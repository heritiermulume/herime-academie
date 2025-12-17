<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Enrollment;
use App\Models\CartItem;
use App\Models\AmbassadorPromoCode;
use App\Models\Ambassador;
use App\Models\AmbassadorCommission;
use App\Models\Setting;
use App\Mail\InvoiceMail;
use App\Mail\PaymentFailedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Notifications\PaymentReceived;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

/**
 * Controller pour gérer les paiements Moneroo
 * 
 * Gestion conforme à la documentation Moneroo:
 * - https://docs.moneroo.io/fr/payments/initialiser-un-paiement
 * 
 * Statuts gérés:
 * - pending: Paiement en attente
 * - processing: En cours de traitement
 * - completed: Paiement réussi
 * - failed: Paiement échoué
 * 
 * PRINCIPES IMPORTANTS (selon la documentation officielle):
 * 
 * 1. Le webhook est la source de vérité pour le statut final
 *    - Ne PAS poller pour le statut final côté frontend
 *    - S'appuyer uniquement sur le webhook pour les mises à jour
 * 
 * 2. Les redirections (successful/failed URLs) servent uniquement à vérifier le statut
 *    - Utilisées uniquement pour afficher le bon message à l'utilisateur
 *    - Le webhook reste la source de vérité
 * 
 * 3. Format de réponse Moneroo: { "success": true, "message": "...", "data": {} }
 */
class MonerooController extends Controller
{
    private function baseUrl(): string
    {
        return rtrim(config('services.moneroo.base_url', 'https://api.moneroo.io/v1'), '/');
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('services.moneroo.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Extraire le prénom du nom complet
     */
    private function extractFirstName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        return $parts[0] ?? $fullName;
    }

    /**
     * Extraire le nom de famille du nom complet
     */
    private function extractLastName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        if (count($parts) > 1) {
            return implode(' ', array_slice($parts, 1));
        }
        return ''; // Si un seul mot, retourner une chaîne vide
    }

    /**
     * Convertir le montant dans la plus petite unité de la devise
     * Pour XOF (Franc CFA), il n'y a pas de sous-unité, donc on arrondit à l'entier
     * Pour les devises avec centimes (USD, EUR, etc.), multiplier par 100
     */
    private function convertAmountToSmallestUnit(float $amount, string $currency): int
    {
        // Devises sans sous-unité (comme XOF, JPY, etc.)
        $noSubunitCurrencies = ['XOF', 'XAF', 'JPY', 'KRW', 'CLP', 'VND'];
        
        if (in_array(strtoupper($currency), $noSubunitCurrencies)) {
            // Arrondir à l'entier le plus proche
            return (int) round($amount);
        }
        
        // Pour les autres devises (USD, EUR, etc.), multiplier par 100 pour obtenir les centimes
        return (int) round($amount * 100);
    }

    /**
     * Valider la signature d'un webhook Moneroo
     * 
     * Selon la documentation Moneroo, les webhooks peuvent inclure une signature
     * pour validation de sécurité
     */
    private function validateWebhookSignature(string $payload, ?string $signature): bool
    {
        // Si pas de signature dans la config, ne pas valider (sandbox/local dev)
        $webhookSecret = config('services.moneroo.webhook_secret');
        if (!$webhookSecret || !$signature) {
            \Log::warning('Moneroo webhook: No webhook secret or signature configured', [
                'has_secret' => (bool) $webhookSecret,
                'has_signature' => (bool) $signature,
            ]);
            return true; // Autoriser en développement
        }

        // Calculer la signature attendue (HMAC-SHA256 selon la documentation)
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        // Comparaison sécurisée (évite timing attacks)
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Récupérer les méthodes de paiement disponibles
     * Moneroo fournit les méthodes disponibles via l'endpoint /payments/methods
     */
    public function availableMethods(Request $request)
    {
        // Annuler côté backend les commandes trop anciennes sans cron/queue (si connecté)
        if (auth()->check()) {
            $this->autoCancelStale(auth()->id());
        }

        $query = [];
        // Si un pays est fourni, on filtre
        if ($request->filled('country')) {
            $query['country'] = $request->query('country');
        }

        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl() . '/payments/methods', $query);

        $responseData = $response->json();
        
        // Adapter le format de réponse Moneroo au format attendu par le frontend
        if (isset($responseData['success']) && $responseData['success'] && isset($responseData['data'])) {
            return response()->json($responseData['data'], $response->status());
        }

        return response()->json($responseData, $response->status());
    }

    public function initiate(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour procéder au paiement.'
            ], 401);
        }

        // Validation rapide du code promo (sans créer de commande)
        if ($request->has('validate_promo_code') && $request->validate_promo_code === true) {
            $code = $request->ambassador_promo_code;
            if ($code) {
                $promoCode = AmbassadorPromoCode::where('code', strtoupper($code))
                    ->where('is_active', true)
                    ->first();

                if ($promoCode && $promoCode->isValid()) {
                    $ambassador = $promoCode->ambassador;
                    if ($ambassador && $ambassador->is_active) {
                        return response()->json([
                            'valid' => true,
                            'message' => 'Code promo valide'
                        ]);
                    }
                }
            }
            return response()->json([
                'valid' => false,
                'message' => 'Code promo invalide ou expiré'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string',
            'ambassador_promo_code' => 'nullable|string',
            // Note: phoneNumber, provider, country ne sont plus requis car Moneroo les collectera sur leur page
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        
        // Pour l'intégration standard Moneroo, ces champs sont optionnels
        // Initialiser avec des valeurs par défaut si elles ne sont pas présentes
        $data['phoneNumber'] = $data['phoneNumber'] ?? null;
        $data['country'] = $data['country'] ?? null;
        $data['provider'] = $data['provider'] ?? null;

        $user = auth()->user();

        // Récupérer les articles du panier
        $cartItems = $user->cartItems()->with('course')->get();
        // Filtrer les items invalides (cours supprimés ou non publiés)
        $cartItems = $cartItems->filter(function ($item) {
            return $item->course !== null && $item->course->is_published;
        })->values();
        
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Votre panier est vide ou contient uniquement des cours non disponibles.'
            ], 400);
        }

        // Récupérer la devise de base du site
        $baseCurrency = \App\Models\Setting::getBaseCurrency();
        
        // Calculer le total réel depuis le panier (dans la devise de base du site)
        $subtotal = $cartItems->sum(function($item) {
            return optional($item->course)->current_price ?? optional($item->course)->price ?? 0;
        });
        
        // Valider et appliquer le code promo d'ambassadeur si fourni
        $ambassadorPromoCode = null;
        $ambassador = null;
        if ($request->filled('ambassador_promo_code')) {
            $promoCode = AmbassadorPromoCode::where('code', $request->ambassador_promo_code)
                ->where('is_active', true)
                ->first();

            if ($promoCode && $promoCode->isValid()) {
                $ambassadorPromoCode = $promoCode;
                $ambassador = $promoCode->ambassador;
                
                // Vérifier que l'ambassadeur est actif
                if ($ambassador && $ambassador->is_active) {
                    // Le code promo est valide, on l'associera à la commande
                    // Note: Les codes promo d'ambassadeur ne donnent pas de réduction,
                    // ils servent uniquement à attribuer la commission à l'ambassadeur
                } else {
                    $ambassadorPromoCode = null;
                    $ambassador = null;
                }
            }
        }
        
        // IMPORTANT: Utiliser le montant converti et la devise envoyés par le frontend
        $paymentAmount = (float) $data['amount']; // Montant converti dans la devise sélectionnée
        $paymentCurrency = $data['currency'] ?? config('services.moneroo.default_currency', 'USD'); // Devise sélectionnée

		// Calculer un taux de conversion approximatif (si possible)
		$exchangeRate = $subtotal > 0 ? round($paymentAmount / (float) $subtotal, 8) : null;

		// Créer l'Order (montants dans la devise de base du site) et conserver les métadonnées de paiement
		$order = Order::create([
            'order_number' => 'MON-' . strtoupper(Str::random(8)) . '-' . time(),
            'user_id' => $user->id,
            'ambassador_id' => $ambassador?->id,
            'ambassador_promo_code_id' => $ambassadorPromoCode?->id,
            'subtotal' => $subtotal,
            'discount' => 0,
			'total' => $subtotal, // Total dans la devise de base du site
			'total_amount' => $subtotal, // Assurer l'affichage admin qui s'appuie sur total_amount
            'currency' => $baseCurrency, // Devise de la commande (devise de base du site)
			'payment_currency' => $paymentCurrency,
			'payment_amount' => $paymentAmount,
			'exchange_rate' => $exchangeRate,
            'status' => 'pending',
            'payment_method' => 'moneroo',
            'payment_provider' => $data['provider'], // Peut être null pour intégration standard
			'payer_phone' => $data['phoneNumber'], // Optionnel - Moneroo collectera sur leur page
			'payer_country' => $data['country'] ?? config('services.moneroo.default_country', 'SN'),
			'customer_ip' => $request->ip(),
			'user_agent' => $request->userAgent(),
            'billing_address' => [
                'phone' => $data['phoneNumber'], // Optionnel - Moneroo collectera sur leur page
                'country' => $data['country'] ?? config('services.moneroo.default_country', 'SN'),
                'payment_currency' => $paymentCurrency, // Devise utilisée pour le paiement
                'payment_amount' => $paymentAmount, // Montant dans la devise de paiement
            ],
        ]);

        // Créer les OrderItems
        foreach ($cartItems as $cartItem) {
            if (!$cartItem->course) { continue; }
            $coursePrice = $cartItem->course->current_price ?? $cartItem->course->price ?? 0;
            OrderItem::create([
                'order_id' => $order->id,
                'course_id' => $cartItem->course_id,
                'price' => $cartItem->course->price ?? 0,
                'sale_price' => $cartItem->course->is_sale_active ? $cartItem->course->active_sale_price : null,
                'total' => $coursePrice,
            ]);
        }

		$paymentId = 'pay_' . strtoupper(Str::random(16)) . '_' . time();

		// Sauvegarder la référence fournisseur sur la commande pour suivi centralisé
		$order->update([
			'payment_reference' => $paymentId,
		]);
        
        // Intégration standard Moneroo selon la documentation: https://docs.moneroo.io/fr/payments/integration-standard
        // Endpoint: POST /v1/payments/initialize
        // Format requis: amount (integer), currency, description, return_url, customer.email, customer.first_name, customer.last_name
        // IMPORTANT: Selon la documentation Moneroo, le montant doit être un entier
        // D'après les tests, Moneroo semble attendre le montant en unité de base (pas en centimes)
        // Exemple: 199.99 USD doit être envoyé comme 200 (dollars arrondis), pas 19999 (centimes)
        // Cela évite que Moneroo affiche 19999 au lieu de 199.99 dans leur interface
        $amountInSmallestUnit = (int) round($paymentAmount); // Montant en unité de base arrondi à l'entier
        
		$payload = [
            'amount' => $amountInSmallestUnit, // Montant en unité de la devise (integer requis par Moneroo)
            'currency' => $paymentCurrency,
            'description' => config('services.moneroo.company_name', 'Herime Académie') . ' - Paiement commande ' . $order->order_number,
            'return_url' => config('services.moneroo.successful_url', route('moneroo.success')) . '?payment_id=' . $paymentId,
            'customer' => [
                'email' => $user->email,
                'first_name' => $this->extractFirstName($user->name),
                'last_name' => $this->extractLastName($user->name),
            ],
            'metadata' => [
                'order_id' => (string) $order->id,
                'order_number' => $order->order_number,
                'user_id' => (string) $user->id,
            ],
        ];
        
        // Ajouter customer.phone et country si disponibles (optionnels)
        if (!empty($data['phoneNumber'])) {
            $payload['customer']['phone'] = $data['phoneNumber'];
        }
        if (!empty($data['country'])) {
            $payload['customer']['country'] = $data['country'];
        }

        try {
            // Appel API Moneroo pour initialiser le paiement (intégration standard)
            // Endpoint selon la documentation: POST /v1/payments/initialize
            \Log::info('Moneroo: Envoi de la requête d\'initialisation', [
                'url' => $this->baseUrl() . '/payments/initialize',
                'payload' => $payload,
                'amount_converted' => $amountInSmallestUnit,
                'original_amount' => $paymentAmount,
                'currency' => $paymentCurrency,
            ]);
            
            $response = Http::withHeaders($this->authHeaders())
                ->post($this->baseUrl() . '/payments/initialize', $payload);

            $responseData = $response->json();
            
            \Log::info('Moneroo: Réponse brute de l\'API', [
                'status' => $response->status(),
                'response_data' => $responseData,
                'response_body' => $response->body(),
            ]);
            
            // Format de réponse Moneroo: 
            // - Succès standard: HTTP 201 avec { "success": true, "data": { "id": "...", "checkout_url": "..." } }
            // - Succès alternatif: HTTP 201 avec { "success": false, "error": { "data": { "id": "...", "checkout_url": "..." } } }
            // - Échec: HTTP 400+ avec message d'erreur
            // Vérifier d'abord le statut HTTP, puis la présence des données nécessaires
            $hasCheckoutUrl = isset($responseData['data']['checkout_url']) || 
                             isset($responseData['error']['data']['checkout_url']);
            
            $isSuccess = $response->successful() && (
                // Cas 1: Réponse standard avec success: true
                (isset($responseData['success']) && $responseData['success'] === true) ||
                // Cas 2: Statut 201 avec checkout_url présent (même si success: false dans la structure)
                ($response->status() === 201 && $hasCheckoutUrl)
            );
            
            if (!$isSuccess) {
                // Réponse d'échec: annuler la commande et marquer paiement failed
                $error = $responseData;
                \Log::error('Moneroo: Échec de l\'initialisation du paiement', [
                    'error' => $error,
                    'payload' => $payload,
                    'amount_converted' => $amountInSmallestUnit,
                    'original_amount' => $paymentAmount,
                    'currency' => $paymentCurrency,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
                
                // Traduire les erreurs courantes en messages plus compréhensibles
                $errorMessage = $error['message'] ?? 'Échec de l\'initialisation du paiement';
                if (str_contains($errorMessage, 'No payment methods enabled for this currency')) {
                    $failureReason = 'Aucune méthode de paiement activée pour la devise ' . $paymentCurrency . '. Veuillez contacter le support ou activer les méthodes de paiement pour cette devise dans votre compte Moneroo.';
                } else {
                    $failureReason = $errorMessage;
                }
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'moneroo',
                    'provider' => $data['provider'] ?? null, // Peut être null pour intégration standard
                    'payment_id' => $paymentId,
                    'amount' => $paymentAmount,
                    'currency' => $paymentCurrency,
                    'status' => 'failed',
                    'failure_reason' => $failureReason,
                    'payment_data' => [
                        'request' => $payload,
                        'response' => $error,
                    ],
                ]);
                $order->update(['status' => 'cancelled']);
                
                // Envoyer email ET notification d'échec
                $this->sendPaymentFailureNotifications($order, $failureReason);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Échec de l\'initialisation du paiement.',
                    'error' => $error,
                ], $response->status());
            }

            // Extraire les données de la réponse Moneroo (intégration standard)
            // Format de réponse selon la documentation: { "success": true, "data": { "id": "...", "checkout_url": "..." } }
            // Mais parfois Moneroo retourne: { "success": false, "error": { "data": { "id": "...", "checkout_url": "..." } } }
            $paymentData = $responseData['data'] ?? $responseData['error']['data'] ?? $responseData;
            $actualPaymentId = $paymentData['id'] ?? $paymentId;
            $status = $paymentData['status'] ?? 'pending';
            
            // Pour l'intégration standard, Moneroo retourne checkout_url dans data
            // Selon la documentation: data.checkout_url
            // Mais parfois dans error.data.checkout_url (format alternatif)
            $redirectUrl = $paymentData['checkout_url'] 
                        ?? $paymentData['checkoutUrl']
                        ?? ($responseData['error']['data']['checkout_url'] ?? null)
                        ?? $paymentData['redirect_url'] 
                        ?? $paymentData['authorizationUrl'] 
                        ?? $paymentData['authorization_url']
                        ?? $paymentData['url']
                        ?? null;
            
            \Log::info('Moneroo: Réponse de l\'API', [
                'response_data' => $responseData,
                'payment_data' => $paymentData,
                'redirect_url' => $redirectUrl,
                'actual_payment_id' => $actualPaymentId,
            ]);
            
            // Si pas d'URL de redirection, c'est une erreur pour l'intégration standard
            if (!$redirectUrl) {
                \Log::error('Moneroo: Pas d\'URL checkout_url dans la réponse', [
                    'response' => $responseData,
                    'payment_data' => $paymentData,
                    'response_keys' => array_keys($paymentData ?? []),
                ]);
                throw new \Exception('Moneroo n\'a pas retourné d\'URL de checkout pour la page de paiement');
            }

            // Créer un Payment en attente uniquement en cas de succès d'initiation
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'moneroo',
                'provider' => $data['provider'] ?? null, // Peut être null pour intégration standard
                'payment_id' => $actualPaymentId,
                'amount' => $paymentAmount,
                'currency' => $paymentCurrency,
                'status' => 'pending',
                'payment_data' => [
                    'request' => $payload,
                    'response' => $responseData,
                ],
            ]);

            // Mettre à jour la référence de paiement avec l'ID réel de Moneroo
            $order->update([
                'payment_reference' => $actualPaymentId,
            ]);

            // Retourner la réponse au format attendu par le frontend
            // Format selon la documentation Moneroo: data.checkout_url
            $out = [
                'success' => true,
                'payment_id' => $actualPaymentId,
                'order_id' => $order->id,
                'status' => $status,
                'checkout_url' => $redirectUrl, // Format Moneroo standard (priorité)
                'redirect_url' => $redirectUrl, // Pour compatibilité
                'data' => array_merge($paymentData, [
                    'checkout_url' => $redirectUrl, // Format Moneroo standard dans data aussi
                ]),
            ];

            return response()->json($out, 200, ['Content-Type' => 'application/json; charset=utf-8']);
        } catch (\Throwable $e) {
            // Erreur technique: annuler et marquer failed
            $failureReason = 'Erreur technique lors de l\'initialisation';
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'moneroo',
                'provider' => $data['provider'] ?? null, // Peut être null pour intégration standard
                'payment_id' => $paymentId,
                'amount' => $paymentAmount,
                'currency' => $paymentCurrency,
                'status' => 'failed',
                'failure_reason' => $failureReason,
                'payment_data' => [
                    'request' => $payload,
                    'exception' => [
                        'type' => get_class($e),
                        'message' => $e->getMessage(),
                    ],
                ],
            ]);
            $order->update(['status' => 'cancelled']);
            
            // Envoyer email ET notification d'échec
            $this->sendPaymentFailureNotifications($order, $failureReason);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur de communication avec le fournisseur. La commande a été annulée.',
            ], 502);
        }
    }

    public function status(string $paymentId)
    {
        if (auth()->check()) {
            $this->autoCancelStale(auth()->id());
        }
        
        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl() . "/payments/{$paymentId}");

        $responseData = $response->json();
        
        // Format de réponse Moneroo: { "success": true, "message": "...", "data": {} }
        $paymentData = $responseData['data'] ?? $responseData;
        $status = $paymentData['status'] ?? null;
        
        \Log::info('Moneroo status check', [
            'payment_id' => $paymentId,
            'status' => $status,
            'full_response' => $responseData,
        ]);
        
        // Synchroniser l'état local si le statut est terminal (échec/annulation)
        try {
            if (in_array($status, ['failed', 'cancelled', 'expired', 'rejected'])) {
                $payment = Payment::where('payment_method', 'moneroo')
                    ->where('payment_id', $paymentId)
                    ->with('order')
                    ->first();
                if ($payment) {
                    if ($payment->status === 'pending') {
                        $payment->update([
                            'status' => 'failed',
                            'failure_reason' => $paymentData['failure_reason'] ?? ($paymentData['message'] ?? 'Paiement échoué'),
                        ]);
                    }
                    if ($payment->order && !in_array($payment->order->status, ['paid', 'completed'])) {
                        $payment->order->update(['status' => 'cancelled']);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Moneroo status sync failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
        }
        
        return response()->json($paymentData, $response->status());
    }

    public function webhook(Request $request)
    {
        // IMPORTANT: Toujours retourner 200 OK si le webhook est reçu avec succès
        // Selon la documentation Moneroo, on doit retourner 200 OK pour confirmer la réception
        
        // IMPORTANT: Valider la signature du webhook pour sécurité
        $signature = $request->header('X-Moneroo-Signature') ?? $request->header('X-Signature');
        $payloadContent = $request->getContent();
        
        if ($signature && !$this->validateWebhookSignature($payloadContent, $signature)) {
            \Log::error('Moneroo webhook: Invalid signature - potential security threat', [
                'payment_id' => $request->input('data.id') ?? $request->input('id'),
                'ip' => $request->ip(),
            ]);
            // CRITIQUE: Retourner 200 pour éviter les retry, mais logger comme erreur
            return response()->json(['received' => false, 'error' => 'Invalid signature'], 200);
        }

        $payload = $request->all();
        // Format Moneroo: { "success": true, "message": "...", "data": { "id": "...", "status": "..." } }
        $paymentData = $payload['data'] ?? $payload;
        $paymentId = $paymentData['id'] ?? null;
        $status = $paymentData['status'] ?? null;

        if (!$paymentId) {
            \Log::warning('Moneroo webhook: payment_id missing', ['payload' => $payload]);
            // Retourner 200 OK même si payment_id manquant (éviter retry)
            return response()->json(['received' => false, 'message' => 'payment_id missing'], 200);
        }

        $payment = Payment::where('payment_method', 'moneroo')
            ->where('payment_id', $paymentId)
            ->with(['order.orderItems', 'order.user'])
            ->first();

        if (!$payment) {
            \Log::warning('Moneroo webhook: Payment not found', ['payment_id' => $paymentId]);
            // Retourner 200 OK même si payment non trouvé (éviter retry sur transaction inexistante)
            return response()->json(['received' => false, 'message' => 'Payment not found'], 200);
        }

        // CRITIQUE: Envelopper le traitement dans un try-catch pour toujours retourner 200 OK
        // Selon la documentation Moneroo, on DOIT retourner 200 OK même en cas d'erreur
        try {
            // Log de tous les callbacks reçus pour traçabilité
            \Log::info('Moneroo webhook received', [
                'payment_id' => $paymentId,
                'status' => $status,
                'current_order_status' => $payment->order?->status,
            ]);

            // Mapper le statut Moneroo vers le statut local
            // Selon la documentation Moneroo: pending, processing, completed, failed, cancelled, expired, rejected
            $mapped = match ($status) {
                'completed' => 'completed',
                'failed', 'cancelled', 'expired', 'rejected' => 'failed',
                'pending', 'processing' => 'pending',
                default => 'pending',
            };

            // Mettre à jour le Payment avec toutes les informations du callback
            $paymentData = array_merge($payment->payment_data ?? [], [
                'callback' => $payload,
                'last_callback_at' => now()->toIso8601String(),
            ]);

            $payment->update([
                'status' => $mapped,
                'payment_data' => $paymentData,
                'processed_at' => ($status === 'completed') ? now() : null,
            ]);

            // Traiter selon le statut final
			if ($status === 'completed' && $payment->order) {
				// Mettre à jour la commande avec la référence et les frais si fournis
				$feeAmount = $paymentData['fee'] ?? ($paymentData['fees']['amount'] ?? null);
				$feeCurrency = $paymentData['fee_currency'] ?? ($paymentData['fees']['currency'] ?? null);
				$updates = [
					'payment_reference' => $payment->order->payment_reference ?: ($paymentData['id'] ?? $paymentId),
				];
				if ($feeAmount !== null) {
					$updates['provider_fee'] = (float) $feeAmount; // interprété dans la devise de paiement
					$updates['net_total'] = $payment->order->payment_amount !== null
						? (float) $payment->order->payment_amount - (float) $feeAmount
						: null; // même unité que payment_amount
					if ($feeCurrency !== null) {
						$updates['provider_fee_currency'] = (string) $feeCurrency;
					}
				}
				$payment->order->update(array_merge($updates, [
					'status' => 'paid',
					'paid_at' => $payment->order->paid_at ?: now(),
				]));
                // Paiement réussi : finaliser la commande et créer les inscriptions
                $this->finalizeOrderAfterPayment($payment->order);
                \Log::info('Moneroo: Order finalized after successful payment', [
                    'order_id' => $payment->order->id,
                    'payment_id' => $paymentId,
                ]);
            } elseif (in_array($status, ['failed', 'cancelled', 'expired', 'rejected']) && $payment->order) {
                // Échec : enregistrer la raison détaillée et annuler la commande
                // Extraire la raison d'échec de plusieurs sources possibles selon la structure Moneroo
                $failureReason = $this->extractFailureReason($paymentData, $payload, $status);
                
                $payment->update(['failure_reason' => $failureReason]);
                
                // Annuler la commande seulement si elle n'est pas déjà payée (éviter doublon)
                if (!in_array($payment->order->status, ['paid', 'completed'])) {
                    $payment->order->update(['status' => 'cancelled']);
                }
                
                // Envoyer email ET notification d'échec
                $this->sendPaymentFailureNotifications($payment->order, $failureReason);
                
                \Log::info('Moneroo: Order cancelled after failed payment', [
                    'order_id' => $payment->order->id,
                    'payment_id' => $paymentId,
                    'status' => $status,
                    'reason' => $failureReason,
                    'full_payload' => $payload, // Logger le payload complet pour analyse
                ]);
            } elseif ($status === 'pending' || $status === 'processing') {
                // En attente de traitement ou traitement en cours
                \Log::info('Moneroo: Payment pending/processing', [
                    'order_id' => $payment->order?->id,
                    'payment_id' => $paymentId,
                    'status' => $status,
                ]);
            }

            return response()->json(['received' => true]);
            
        } catch (\Throwable $e) {
            // CRITIQUE: Logger l'erreur mais retourner 200 OK
            // Moneroo ne réessaiera pas si on retourne 200
            \Log::error('Moneroo webhook: Exception during processing', [
                'payment_id' => $paymentId,
                'status' => $status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Retourner 200 OK avec indication d'erreur dans la réponse
            return response()->json([
                'received' => true, 
                'error' => 'Error processing callback (logged)',
            ], 200);
        }
    }

    /**
     * Extraire la raison d'échec du paiement depuis les données Moneroo
     * 
     * Cette méthode cherche la raison d'échec dans plusieurs champs possibles
     * pour capturer tous les cas d'erreur (solde insuffisant, transaction rejetée, etc.)
     * 
     * @param array $paymentData Les données du paiement
     * @param array $payload Le payload complet du webhook
     * @param string $status Le statut du paiement
     * @return string La raison d'échec formatée
     */
    private function extractFailureReason(array $paymentData, array $payload, string $status): string
    {
        // Chercher la raison d'échec dans plusieurs champs possibles
        $reason = $paymentData['failure_reason'] 
               ?? $paymentData['error_message'] 
               ?? $paymentData['error'] 
               ?? $paymentData['message'] 
               ?? $payload['message'] 
               ?? $payload['error_message']
               ?? null;
        
        // Si une raison spécifique est trouvée, la retourner
        if ($reason && is_string($reason)) {
            return $reason;
        }
        
        // Sinon, mapper le statut vers un message compréhensible
        return match ($status) {
            'failed' => 'Le paiement a échoué. Veuillez vérifier vos informations de paiement et réessayer.',
            'cancelled' => 'Le paiement a été annulé.',
            'expired' => 'Le délai de paiement a expiré.',
            'rejected' => 'Le paiement a été rejeté. Cela peut être dû à un solde insuffisant ou à une restriction sur votre compte.',
            default => 'Le paiement n\'a pas pu être complété.',
        };
    }

    /**
     * Annuler une commande par payment_id (annulation manuelle uniquement)
     * 
     * Selon la documentation Moneroo, cette fonction est uniquement pour les annulations explicites par l'utilisateur.
     */
    public function cancel(string $paymentId)
    {
        $payment = Payment::where('payment_method', 'moneroo')
            ->where('payment_id', $paymentId)
            ->with('order')
            ->first();
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Transaction introuvable'], 404);
        }

        // Vérifier que le paiement n'est pas déjà complété
        if ($payment->status === 'completed' || in_array($payment->order?->status ?? null, ['paid', 'completed'])) {
            \Log::warning('Moneroo cancel: Cannot cancel - payment already completed', [
                'payment_id' => $paymentId,
                'payment_status' => $payment->status,
                'order_status' => $payment->order?->status,
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Impossible d\'annuler : le paiement est déjà complété',
            ], 422);
        }

        // Annuler uniquement si le statut est encore pending
        if ($payment->status === 'pending') {
            $failureReason = 'Annulation par l\'utilisateur';
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $failureReason,
            ]);

            if ($payment->order) {
                $payment->order->update(['status' => 'cancelled']);
                
                // Envoyer email ET notification d'échec
                $this->sendPaymentFailureNotifications($payment->order, $failureReason);
            }
            
            \Log::info('Moneroo: Payment cancelled by user', [
                'payment_id' => $paymentId,
                'payment_db_id' => $payment->id,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Annuler la dernière commande en attente de l'utilisateur (si l'init échoue côté client)
     */
    public function cancelLatestPending(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }
        $userId = auth()->id();
        $order = Order::where('user_id', $userId)
            ->where('status', 'pending')
            ->latest()
            ->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Aucune commande en attente'], 404);
        }
        // Optionnel: ne pas annuler des commandes trop anciennes (>10 min)
        if ($order->created_at->lt(now()->subMinutes(10))) {
            return response()->json(['success' => false, 'message' => 'Commande trop ancienne pour annulation automatique'], 422);
        }
        $order->update(['status' => 'cancelled']);
        return response()->json(['success' => true]);
    }

    /**
     * Finaliser la commande après paiement réussi
     * 
     * Cette méthode est idempotente : elle peut être appelée plusieurs fois sans problème
     */
    private function finalizeOrderAfterPayment(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Rafraîchir l'order pour avoir les dernières données + charger les relations
            $order->refresh();
            $order->load('orderItems', 'user');
            
            \Log::info('Moneroo: Starting finalization', [
                'order_id' => $order->id,
                'current_status' => $order->status,
                'order_total' => $order->total,
                'order_currency' => $order->currency,
                'user_id' => $order->user_id,
            ]);
            
            // Vérifier si déjà finalisée (idempotence)
            if (in_array($order->status, ['paid', 'completed'])) {
                \Log::info('Moneroo: Order already finalized', [
                    'order_id' => $order->id,
                    'status' => $order->status,
                ]);
                return;
            }

            // CRITIQUE: Charger les OrderItems directement depuis la DB si la relation est vide
            $orderItems = $order->orderItems;
            
            // Si la collection est vide, charger directement depuis la DB
            if ($orderItems->isEmpty()) {
                $orderItems = OrderItem::where('order_id', $order->id)->get();
                \Log::info('Moneroo: OrderItems loaded directly from DB', [
                    'order_id' => $order->id,
                    'items_count' => $orderItems->count(),
                ]);
            }
            
            \Log::info('Moneroo: OrderItems loaded', [
                'order_id' => $order->id,
                'order_items_count' => $orderItems->count(),
                'items_data' => $orderItems->map(fn($item) => [
                    'id' => $item->id,
                    'course_id' => $item->course_id,
                    'price' => $item->price,
                ])->toArray(),
            ]);
            
            if ($orderItems->isEmpty()) {
                \Log::warning('Moneroo: No order items found for enrollment - proceeding to mark order paid', [
                    'order_id' => $order->id,
                ]);
            }

			// Mettre à jour l'Order : payé après confirmation du paiement
			$updated = $order->update([
				'status' => 'paid',
				'paid_at' => $order->paid_at ?: now(),
			]);

			\Log::info('Moneroo: Order marked as paid', [
                'order_id' => $order->id,
                'update_successful' => $updated,
                'new_status' => $order->fresh()->status,
            ]);

            // Créer les Enrollments pour chaque cours
            $enrollmentsCreated = 0;
            
            foreach ($orderItems as $orderItem) {
                \Log::info('Moneroo: Processing order item', [
                    'order_id' => $order->id,
                    'order_item_id' => $orderItem->id,
                    'course_id' => $orderItem->course_id,
                    'user_id' => $order->user_id,
                ]);
                
                // Vérifier si l'utilisateur n'est pas déjà inscrit
                $existingEnrollment = Enrollment::where('user_id', $order->user_id)
                    ->where('course_id', $orderItem->course_id)
                    ->first();

                if (!$existingEnrollment) {
                    // La méthode createAndNotify envoie automatiquement les notifications et emails
                    $enrollment = Enrollment::createAndNotify([
                        'user_id' => $order->user_id,
                        'course_id' => $orderItem->course_id,
                        'order_id' => $order->id,
                        'status' => 'active',
                    ]);
                    $enrollmentsCreated++;
                    
                    \Log::info('Moneroo: Enrollment created', [
                        'enrollment_id' => $enrollment->id,
                        'order_id' => $order->id,
                        'course_id' => $orderItem->course_id,
                        'user_id' => $order->user_id,
                    ]);
                } else {
                    \Log::info('Moneroo: Enrollment already exists', [
                        'order_id' => $order->id,
                        'course_id' => $orderItem->course_id,
                        'existing_enrollment_id' => $existingEnrollment->id,
                    ]);
                }
            }

            \Log::info('Moneroo: Enrollments created', [
                'order_id' => $order->id,
                'enrollments_created' => $enrollmentsCreated,
                'total_order_items' => $orderItems->count(),
            ]);

			// Vider le panier de l'utilisateur (DB + session par sécurité)
			$cartItemsBeforeDelete = CartItem::where('user_id', $order->user_id)->count();
			$cartItemsDeleted = CartItem::where('user_id', $order->user_id)->delete();
			Session::forget('cart');
			
			\Log::info('Moneroo: Cart emptied', [
				'user_id' => $order->user_id,
				'cart_items_before' => $cartItemsBeforeDelete,
				'cart_items_deleted' => $cartItemsDeleted,
				'session_cart_cleared' => true,
			]);
            
            // Créer la commission d'ambassadeur si un code promo a été utilisé
            if ($order->ambassador_id && $order->ambassador_promo_code_id) {
                $commission = $this->createAmbassadorCommission($order);
                
                // Envoyer un email à l'ambassadeur
                if ($commission) {
                    try {
                        $order->load(['ambassador.user']);
                        if ($order->ambassador && $order->ambassador->user) {
                            $mailable = new \App\Mail\AmbassadorCommissionEarned($commission);
                            $communicationService = app(\App\Services\CommunicationService::class);
                            $communicationService->sendEmailAndWhatsApp($order->ambassador->user, $mailable);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error sending ambassador commission email: ' . $e->getMessage());
                    }
                }
            }
            
            // Envoyer les emails de paiement (même logique que Enrollment::sendEnrollmentNotifications)
            $this->sendPaymentEmails($order);
            
            \Log::info('Moneroo: Finalization completed successfully', [
                'order_id' => $order->id,
                'final_status' => $order->fresh()->status,
            ]);
        });
    }

    public function successfulRedirect(Request $request)
    {
        if (auth()->check()) {
            $this->autoCancelStale(auth()->id());
        }
        
        // Moneroo peut envoyer payment_id (notre référence) ou paymentId (ID Moneroo) dans les paramètres
        $paymentId = $request->query('payment_id') 
                  ?? $request->query('paymentId') 
                  ?? $request->input('payment_id')
                  ?? $request->input('paymentId');
        
        \Log::info('Moneroo: successfulRedirect appelé', [
            'payment_id' => $request->query('payment_id'),
            'paymentId' => $request->query('paymentId'),
            'paymentStatus' => $request->query('paymentStatus'),
            'all_params' => $request->all(),
        ]);
        
        if ($paymentId) {
            // Chercher le paiement par payment_id (notre référence) ou par le payment_id de Moneroo
            $payment = Payment::where('payment_method', 'moneroo')
                ->where(function($query) use ($paymentId) {
                    $query->where('payment_id', $paymentId)
                          ->orWhereJsonContains('payment_data->response->data->id', $paymentId)
                          ->orWhereJsonContains('payment_data->data->id', $paymentId);
                })
                ->with('order')
                ->first();

            if ($payment && $payment->order) {
                // VALIDATION RECOMMANDÉE : Vérifier le statut auprès de Moneroo
                // comme recommandé dans la documentation pour garantir la cohérence
                // Utiliser l'ID Moneroo (py_xxx) si disponible, sinon notre payment_id
                $monerooPaymentId = $payment->payment_data['response']['data']['id'] 
                                 ?? $payment->payment_data['data']['id'] 
                                 ?? $paymentId;
                
                $statusResponse = Http::withHeaders($this->authHeaders())
                    ->get($this->baseUrl() . "/payments/{$monerooPaymentId}");

                if ($statusResponse->successful()) {
                    $responseData = $statusResponse->json();
                    // Format Moneroo: { "success": true, "message": "...", "data": { "id": "...", "status": "..." } }
                    $statusData = $responseData['data'] ?? $responseData;
                    $status = $statusData['status'] ?? null;

                    \Log::info('Moneroo: Status check on successful redirect', [
                        'payment_id' => $paymentId,
                        'status' => $status,
                        'local_payment_status' => $payment->status,
                        'order_status' => $payment->order->status,
                    ]);

                    // Si localement le paiement est déjà complété, forcer la mise à jour de la commande
                    if ($payment->status === 'completed' && !in_array($payment->order->status, ['paid', 'completed'])) {
                        $payment->order->update([
                            'status' => 'paid',
                            'paid_at' => $payment->order->paid_at ?: now(),
                        ]);
                        $this->finalizeOrderAfterPayment($payment->order);
                    }

                    // Traiter tous les statuts possibles selon Moneroo
                    if ($status === 'completed') {
                        // Paiement complété : s'assurer que tout est finalisé
                        if ($payment->status !== 'completed') {
                            $payment->update([
                                'status' => 'completed',
                                'processed_at' => now(),
                                'payment_data' => array_merge($payment->payment_data ?? [], [
                                    'redirect_check' => $statusData,
                                ]),
                            ]);
                        }

                        // Marquer immédiatement la commande comme payée si besoin
                        if (!in_array($payment->order->status, ['paid', 'completed'])) {
                            $payment->order->update([
                                'status' => 'paid',
                                'paid_at' => $payment->order->paid_at ?: now(),
                            ]);
                        }

                        // Sauvegarder référence et frais si fournis
                        $feeAmount = $statusData['fee'] ?? ($statusData['fees']['amount'] ?? null);
                        $feeCurrency = $statusData['fee_currency'] ?? ($statusData['fees']['currency'] ?? null);
						$updates = [
							'payment_reference' => $payment->order->payment_reference ?: ($paymentId ?? null),
						];
						if ($feeAmount !== null) {
							$updates['provider_fee'] = (float) $feeAmount;
							$updates['net_total'] = $payment->order->payment_amount !== null
								? (float) $payment->order->payment_amount - (float) $feeAmount
								: null;
							if ($feeCurrency !== null) {
								$updates['provider_fee_currency'] = (string) $feeCurrency;
							}
						}
						$payment->order->update($updates);
                        
                        // Finaliser la commande si pas déjà fait
                        $orderWasAlreadyPaid = in_array($payment->order->status, ['paid', 'completed']);
                        if (!$orderWasAlreadyPaid) {
                            $this->finalizeOrderAfterPayment($payment->order);
                        }

                        // Sécuriser le vidage du panier côté session utilisateur (contexte redirection)
                        if (auth()->check()) {
                            try {
                                // Supprimer éventuels reliquats (même si déjà vidé par webhook)
                                auth()->user()->cartItems()->delete();
                            } catch (\Throwable $e) {}
                            \Session::forget('cart');
                        }
                        
                        // TOUJOURS assurer l'envoi des emails en contexte redirection (même si commande déjà payée)
                        // Car le webhook peut ne pas avoir envoyé les emails ou avoir échoué
                        // Utiliser la même logique que Enrollment::sendEnrollmentNotifications
                        $orderFresh = $payment->order->fresh();
                        $this->sendPaymentEmails($orderFresh);

                        $order = $payment->order->fresh();
                        return view('payments.moneroo.success', compact('order'));
                        
                    } elseif (in_array($status, ['failed', 'cancelled', 'expired', 'rejected'])) {
                        // Échec : extraire la raison détaillée et rediriger vers la page d'échec
                        $failureReason = $this->extractFailureReason($statusData, $responseData, $status);
                        
                        $payment->update([
                            'status' => 'failed',
                            'failure_reason' => $failureReason,
                            'payment_data' => array_merge($payment->payment_data ?? [], [
                                'redirect_check' => $statusData,
                            ]),
                        ]);
                        
                        if (!in_array($payment->order->status, ['paid', 'completed'])) {
                            $payment->order->update(['status' => 'cancelled']);
                        }
                        
                        // Envoyer email ET notification d'échec
                        $this->sendPaymentFailureNotifications($payment->order, $failureReason);
                        
                        \Log::warning('Moneroo: Redirected to failed page', [
                            'payment_id' => $paymentId,
                            'status' => $status,
                            'reason' => $failureReason,
                            'full_status_data' => $statusData, // Logger les données complètes pour analyse
                        ]);
                        
                        return redirect()->route('moneroo.failed');
                        
                    } elseif ($status === 'pending' || $status === 'processing') {
                        // En cours de traitement : informer l'utilisateur
                        \Log::info('Moneroo: Payment still processing on redirect', [
                            'payment_id' => $paymentId,
                            'status' => $status,
                        ]);
                        
                        return view('payments.moneroo.success', [
                            'order' => null,
                            'processing_warning' => true,
                            'payment_id' => $paymentId,
                        ]);
                    } else {
                        // Statut inconnu : afficher quand même la page de succès
                        \Log::warning('Moneroo: Unknown status on redirect', [
                            'payment_id' => $paymentId,
                            'status' => $status,
                        ]);
                        
                        $order = $payment->order->fresh();
                        return view('payments.moneroo.success', compact('order'));
                    }
                } else {
                    // Erreur lors de la vérification : continuer avec le statut local
                    \Log::warning('Moneroo: Failed to check status on redirect', [
                        'payment_id' => $paymentId,
                        'response_status' => $statusResponse->status(),
                    ]);
                }
            }
        }

        return view('payments.moneroo.success');
    }

    public function failedRedirect(Request $request)
    {
        if (auth()->check()) {
            $this->autoCancelStale(auth()->id());
        }
        // Si Moneroo redirige avec un payment_id, synchroniser l'état local
        $paymentId = $request->query('payment_id') ?? $request->query('paymentId');
        if ($paymentId) {
            $payment = Payment::where('payment_method', 'moneroo')
                ->where('payment_id', $paymentId)
                ->with('order')
                ->first();

            if ($payment && $payment->order) {
                // Marquer le paiement comme échoué si encore en attente
                $failureReason = 'Annulation par l\'utilisateur (redirect)';
                if ($payment->status === 'pending') {
                    $payment->update(['status' => 'failed', 'failure_reason' => $failureReason]);
                }

                // Annuler la commande si elle n'est pas déjà payée/terminée
                if (!in_array($payment->order->status, ['paid', 'completed'])) {
                    $payment->order->update(['status' => 'cancelled']);
                }

                // Envoyer email ET notification d'échec
                $this->sendPaymentFailureNotifications($payment->order, $failureReason);

                \Log::info('Moneroo: Order/payment marked cancelled/failed on failed redirect', [
                    'payment_id' => $paymentId,
                    'payment_db_id' => $payment->id,
                    'order_id' => $payment->order->id,
                ]);
            } else {
                \Log::warning('Moneroo: Failed redirect with unknown payment_id', [
                    'payment_id' => $paymentId,
                ]);
            }
        }

        return view('payments.moneroo.failed');
    }

    /**
     * Envoyer les emails de paiement (même logique que Enrollment::sendEnrollmentNotifications)
     * Cette méthode envoie directement les emails de manière synchrone
     */
    private function sendPaymentEmails(Order $order): void
    {
        try {
            // Charger les relations nécessaires
            if (!$order->relationLoaded('user')) {
                $order->load('user');
            }
            if (!$order->relationLoaded('orderItems')) {
                $order->load('orderItems.course');
            }
            if (!$order->relationLoaded('coupon')) {
                $order->load('coupon');
            }
            if (!$order->relationLoaded('affiliate')) {
                $order->load('affiliate');
            }
            if (!$order->relationLoaded('payments')) {
                $order->load('payments');
            }

            $user = $order->user;

            if (!$user || !$user->email) {
                \Log::warning("Impossible d'envoyer les emails de paiement: utilisateur ou email manquant", [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                ]);
                return;
            }

            // Envoyer l'email et WhatsApp en parallèle
            try {
                $mailable = new \App\Mail\PaymentReceivedMail($order);
                $communicationService = app(\App\Services\CommunicationService::class);
                $communicationService->sendEmailAndWhatsApp($user, $mailable);
                \Log::info("Email et WhatsApp PaymentReceivedMail envoyés pour la commande {$order->order_number}", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);
            } catch (\Exception $emailException) {
                \Log::error("Erreur lors de l'envoi de l'email PaymentReceivedMail", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'error' => $emailException->getMessage(),
                    'trace' => $emailException->getTraceAsString(),
                ]);
                // Ne pas relancer l'exception pour ne pas bloquer le processus
            }
            
            // Envoyer la notification (pour la base de données et l'affichage dans la navbar)
            // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
            try {
                Notification::sendNow($user, new PaymentReceived($order));
                
                \Log::info("Notification PaymentReceived envoyée à l'utilisateur {$user->id} pour la commande {$order->id}", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);
            } catch (\Exception $notifException) {
                \Log::error("Erreur lors de l'envoi de la notification PaymentReceived", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'error' => $notifException->getMessage(),
                    'trace' => $notifException->getTraceAsString(),
                ]);
                // Ne pas relancer l'exception pour ne pas bloquer le processus
            }

            // Envoyer la facture par email et WhatsApp
            try {
                $mailable = new InvoiceMail($order);
                $communicationService = app(\App\Services\CommunicationService::class);
                $communicationService->sendEmailAndWhatsApp($user, $mailable);
                \Log::info("Email et WhatsApp InvoiceMail envoyés pour la commande {$order->order_number}", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);
            } catch (\Exception $invoiceException) {
                \Log::error("Erreur lors de l'envoi de l'email InvoiceMail", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'error' => $invoiceException->getMessage(),
                    'trace' => $invoiceException->getTraceAsString(),
                ]);
                // Ne pas relancer l'exception pour ne pas bloquer le processus
            }
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'envoi des emails de paiement", [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Envoyer les notifications d'échec de paiement (Email + Notification in-app)
     * 
     * Cette méthode centralise l'envoi des emails et notifications pour tous les cas d'échec:
     * - Échec d'initialisation
     * - Solde insuffisant
     * - Carte rejetée
     * - Paiement annulé
     * - Délai expiré
     * - Erreur technique
     * - Annulation automatique
     * 
     * @param Order $order La commande concernée
     * @param string|null $failureReason La raison de l'échec
     * @return void
     */
    private function sendPaymentFailureNotifications(Order $order, ?string $failureReason = null): void
    {
        try {
            // Charger les relations nécessaires si pas déjà chargées
            if (!$order->relationLoaded('user')) {
                $order->load('user');
            }
            if (!$order->relationLoaded('orderItems')) {
                $order->load('orderItems.course');
            }
            if (!$order->relationLoaded('payments')) {
                $order->load('payments');
            }

            $user = $order->user;

            if (!$user || !$user->email) {
                \Log::warning("Impossible d'envoyer les notifications d'échec: utilisateur ou email manquant", [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                ]);
                return;
            }

            // Raison d'échec par défaut si non fournie
            $failureReason = $failureReason ?? 'Le paiement n\'a pas pu être complété';

            // 1. Envoyer l'email ET WhatsApp d'échec
            try {
                $mailable = new PaymentFailedMail($order, $failureReason);
                $communicationService = app(\App\Services\CommunicationService::class);
                $communicationService->sendEmailAndWhatsApp($user, $mailable);
                \Log::info("Email et WhatsApp d'échec envoyés pour la commande {$order->order_number}", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'failure_reason' => $failureReason,
                ]);
            } catch (\Exception $emailException) {
                \Log::error("Erreur lors de l'envoi de l'email d'échec", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'error' => $emailException->getMessage(),
                    'trace' => $emailException->getTraceAsString(),
                ]);
                // Ne pas relancer l'exception pour ne pas bloquer le processus
            }

            // 2. Envoyer la notification in-app (pour la navbar)
            // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
            try {
                Notification::sendNow($user, new \App\Notifications\PaymentFailed($order, $failureReason));
                
                \Log::info("Notification PaymentFailed envoyée à l'utilisateur {$user->id} pour la commande {$order->id}", [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'failure_reason' => $failureReason,
                ]);
            } catch (\Exception $notifException) {
                \Log::error("Erreur lors de l'envoi de la notification PaymentFailed", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'error' => $notifException->getMessage(),
                    'trace' => $notifException->getTraceAsString(),
                ]);
                // Ne pas relancer l'exception pour ne pas bloquer le processus
            }
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'envoi des notifications d'échec de paiement", [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function autoCancelStale(int $userId): void
    {
        $timeoutMinutes = (int) (env('ORDER_PENDING_TIMEOUT_MIN', 30));
        $threshold = now()->subMinutes($timeoutMinutes);
        $orders = Order::where('user_id', $userId)
            ->where('status', 'pending')
            ->where('created_at', '<', $threshold)
            ->with(['user', 'orderItems.course', 'payments'])
            ->get();
        foreach ($orders as $order) {
            $order->update(['status' => 'cancelled']);
            Payment::where('order_id', $order->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'failed',
                    'failure_reason' => 'Annulation automatique après délai',
                ]);
            
            // Envoyer email ET notification d'annulation automatique
            $failureReason = 'Annulation automatique après délai d\'attente';
            $this->sendPaymentFailureNotifications($order, $failureReason);
        }
    }

    /**
     * Créer une commission d'ambassadeur pour une commande
     */
    private function createAmbassadorCommission(Order $order)
    {
        try {
            // Charger les relations nécessaires
            $order->load(['ambassador', 'ambassadorPromoCode']);
            
            if (!$order->ambassador || !$order->ambassador->is_active) {
                \Log::warning('Moneroo: Cannot create ambassador commission - ambassador not found or inactive', [
                    'order_id' => $order->id,
                    'ambassador_id' => $order->ambassador_id,
                ]);
                return;
            }

            // Récupérer le pourcentage de commission depuis les settings
            $commissionRate = Setting::get('ambassador_commission_rate', 10.0); // 10% par défaut
            
            // Calculer le montant de la commission
            $orderTotal = $order->total ?? $order->total_amount ?? 0;
            $commissionAmount = ($orderTotal * $commissionRate) / 100;

            // Vérifier si une commission existe déjà pour cette commande
            $existingCommission = AmbassadorCommission::where('order_id', $order->id)->first();
            if ($existingCommission) {
                \Log::info('Moneroo: Ambassador commission already exists', [
                    'order_id' => $order->id,
                    'commission_id' => $existingCommission->id,
                ]);
                return;
            }

            // Créer la commission
            $commission = AmbassadorCommission::create([
                'ambassador_id' => $order->ambassador_id,
                'order_id' => $order->id,
                'promo_code_id' => $order->ambassador_promo_code_id,
                'order_total' => $orderTotal,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'status' => 'pending',
            ]);

            // Ajouter les gains à l'ambassadeur
            $order->ambassador->addEarnings($commissionAmount);
            $order->ambassador->incrementReferrals();
            $order->ambassador->incrementSales();

            // Incrémenter l'utilisation du code promo
            if ($order->ambassadorPromoCode) {
                $order->ambassadorPromoCode->incrementUsage();
            }

            \Log::info('Moneroo: Ambassador commission created', [
                'order_id' => $order->id,
                'commission_id' => $commission->id,
                'ambassador_id' => $order->ambassador_id,
                'commission_amount' => $commissionAmount,
                'commission_rate' => $commissionRate,
            ]);

            return $commission;
        } catch (\Exception $e) {
            \Log::error('Moneroo: Error creating ambassador commission', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}


