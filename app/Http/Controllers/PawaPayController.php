<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Enrollment;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use App\Notifications\PaymentReceived;
use Illuminate\Support\Facades\Validator;

/**
 * Controller pour gérer les paiements pawaPay
 * 
 * Gestion conforme à la documentation pawaPay v2:
 * - https://docs.pawapay.io/v2/docs/deposits
 * 
 * Statuts gérés:
 * - ACCEPTED: Paiement accepté pour traitement
 * - PROCESSING: En cours de traitement
 * - COMPLETED: Paiement réussi
 * - FAILED: Paiement échoué
 * - IN_RECONCILIATION: En réconciliation (géré automatiquement par pawaPay)
 * 
 * Flux nextStep:
 * - FINAL_STATUS: Flux standard (PIN prompt)
 * - GET_AUTH_URL: Attente de l'URL d'autorisation
 * - REDIRECT_TO_AUTH_URL: Redirection vers l'URL d'autorisation (Wave, etc.)
 * 
 * PRINCIPES IMPORTANTS (selon la documentation officielle):
 * 
 * 1. Le webhook est la source de vérité pour le statut final
 *    - Ne PAS poller pour le statut final côté frontend
 *    - S'appuyer uniquement sur le webhook pour les mises à jour
 * 
 * 2. La réconciliation est automatique
 *    - Tous les paiements sont réconciliés automatiquement par pawaPay
 *    - IN_RECONCILIATION ne nécessite aucune action
 *    - Les paiements réussis sont réconciliés plus rapidement
 * 
 * 3. Les redirections (successful/failed URLs) servent uniquement à vérifier le statut
 *    - Utilisées uniquement pour afficher le bon message à l'utilisateur
 *    - Le webhook reste la source de vérité
 * 
 * 4. Stocker le depositId avant l'initiation pour pouvoir réconciliés en cas de problème
 * 
 * 5. Ne JAMAIS annuler automatiquement les paiements en cours de traitement
 *    - Laisser pawaPay gérer les timeouts
 *    - La réconciliation résoudra automatiquement tous les cas
 */
class PawaPayController extends Controller
{
    private function baseUrl(): string
    {
        return rtrim(config('services.pawapay.base_url'), '/');
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('services.pawapay.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Valider la signature d'un webhook pawaPay
     * 
     * Selon la documentation: https://docs.pawapay.io/using_the_api
     * Les webhooks incluent un header X-PawaPay-Signature avec une signature HMAC-SHA256
     */
    private function validateWebhookSignature(string $payload, ?string $signature): bool
    {
        // Si pas de signature dans la config, ne pas valider (sandbox/local dev)
        $webhookSecret = config('services.pawapay.webhook_secret');
        if (!$webhookSecret || !$signature) {
            \Log::warning('pawaPay webhook: No webhook secret or signature configured', [
                'has_secret' => (bool) $webhookSecret,
                'has_signature' => (bool) $signature,
            ]);
            return true; // Autoriser en développement
        }

        // Calculer la signature attendue
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        // Comparaison sécurisée (évite timing attacks)
        return hash_equals($expectedSignature, $signature);
    }

    public function activeConf(Request $request)
    {
        // Annuler côté backend les commandes trop anciennes sans cron/queue
        if (auth()->check()) {
            $this->autoCancelStale(auth()->id());
        }
        $operationType = 'DEPOSIT';

        $query = ['operationType' => $operationType];
        // Si un pays est fourni, on filtre; sinon on récupère toute la configuration active
        if ($request->filled('country')) {
            $query['country'] = $request->query('country');
        }

        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl() . '/active-conf', $query);

        return response()->json($response->json(), $response->status());
    }

    public function initiate(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour procéder au paiement.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string',
            'phoneNumber' => 'required|string',
            'provider' => 'required|string',
            'country' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $user = auth()->user();

        // Récupérer les articles du panier
        $cartItems = $user->cartItems()->with('course')->get();
        // Filtrer les items invalides (cours supprimés)
        $cartItems = $cartItems->filter(function ($item) {
            return $item->course !== null;
        })->values();
        
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Votre panier est vide.'
            ], 400);
        }

        // Récupérer la devise de base du site
        $baseCurrency = \App\Models\Setting::getBaseCurrency();
        
        // Calculer le total réel depuis le panier (dans la devise de base du site)
        $subtotal = $cartItems->sum(function($item) {
            return optional($item->course)->current_price ?? optional($item->course)->sale_price ?? optional($item->course)->price ?? 0;
        });
        
        // IMPORTANT: Utiliser le montant converti et la devise envoyés par le frontend
        $paymentAmount = (float) $data['amount']; // Montant converti dans la devise sélectionnée
        $paymentCurrency = $data['currency'] ?? config('services.pawapay.default_currency'); // Devise sélectionnée

		// Calculer un taux de conversion approximatif (si possible)
		$exchangeRate = $subtotal > 0 ? round($paymentAmount / (float) $subtotal, 8) : null;

		// Créer l'Order (montants dans la devise de base du site) et conserver les métadonnées de paiement
		$order = Order::create([
            'order_number' => 'PP-' . strtoupper(Str::random(8)) . '-' . time(),
            'user_id' => $user->id,
            'subtotal' => $subtotal,
            'discount' => 0,
			'total' => $subtotal, // Total dans la devise de base du site
			'total_amount' => $subtotal, // Assurer l'affichage admin qui s'appuie sur total_amount
            'currency' => $baseCurrency, // Devise de la commande (devise de base du site)
			'payment_currency' => $paymentCurrency,
			'payment_amount' => $paymentAmount,
			'exchange_rate' => $exchangeRate,
            'status' => 'pending',
            'payment_method' => 'pawapay',
            'payment_provider' => $data['provider'] ?? null,
			'payer_phone' => $data['phoneNumber'],
			'payer_country' => $data['country'] ?? config('services.pawapay.default_country'),
			'customer_ip' => $request->ip(),
			'user_agent' => $request->userAgent(),
            'billing_address' => [
                'phone' => $data['phoneNumber'],
                'country' => $data['country'] ?? config('services.pawapay.default_country'),
                'payment_currency' => $paymentCurrency, // Devise utilisée pour le paiement
                'payment_amount' => $paymentAmount, // Montant dans la devise de paiement
            ],
        ]);

        // Créer les OrderItems
        foreach ($cartItems as $cartItem) {
            if (!$cartItem->course) { continue; }
            $coursePrice = $cartItem->course->current_price ?? $cartItem->course->sale_price ?? $cartItem->course->price ?? 0;
            OrderItem::create([
                'order_id' => $order->id,
                'course_id' => $cartItem->course_id,
                'price' => $cartItem->course->price ?? 0,
                'sale_price' => $cartItem->course->sale_price ?? null,
                'total' => $coursePrice,
            ]);
        }

		$depositId = (string) Str::uuid();

		// Sauvegarder la référence fournisseur sur la commande pour suivi centralisé
		$order->update([
			'payment_reference' => $depositId,
		]);
        // CRITIQUE: Utiliser le montant converti et la devise sélectionnée pour pawaPay
		$payload = [
            'depositId' => $depositId,
            'amount' => (string) $paymentAmount, // Montant converti dans la devise sélectionnée
            'currency' => $paymentCurrency, // Devise sélectionnée par l'utilisateur
            'payer' => [
                'type' => 'MMO',
                'accountDetails' => [
                    'phoneNumber' => $data['phoneNumber'],
                    'provider' => $data['provider'],
                ],
            ],
            'successfulUrl' => config('services.pawapay.successful_url') . '?depositId=' . $depositId,
            'failedUrl' => config('services.pawapay.failed_url') . '?depositId=' . $depositId,
        ];

        try {
            // Pas de timeout côté app; on laisse le fournisseur gérer le délai
            $response = Http::withHeaders($this->authHeaders())
                ->post($this->baseUrl() . '/deposits', $payload);

            if (!$response->successful()) {
                // Réponse d'échec: annuler la commande et marquer paiement failed
                $error = $response->json();
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'pawapay',
                    'provider' => $data['provider'] ?? null,
                    'payment_id' => $depositId,
                    'amount' => $paymentAmount,
                    'currency' => $paymentCurrency,
                    'status' => 'failed',
                    'failure_reason' => $error['message'] ?? 'Échec de l\'initialisation fournisseur',
                    'payment_data' => [
                        'request' => $payload,
                        'response' => $error,
                    ],
                ]);
                $order->update(['status' => 'cancelled']);
                return response()->json([
                    'success' => false,
                    'message' => 'Échec de l\'initialisation du paiement.',
                    'error' => $error,
                ], $response->status());
            }

            // Tenter d'obtenir un tableau associatif fiable depuis la réponse fournisseur
            $responseData = $response->json();
            if (!is_array($responseData)) {
                $raw = $response->body();
                $decoded = json_decode($raw, true);
                $responseData = is_array($decoded) ? $decoded : ['raw' => $raw];
            }

            // Extraire des champs attendus par le frontend si présents
            $flatStatus = $responseData['status'] ?? ($responseData['data']['status'] ?? null);
            $flatNextStep = $responseData['nextStep'] ?? ($responseData['data']['nextStep'] ?? null);
            $flatAuthUrl = $responseData['authUrl'] ?? ($responseData['authorizationUrl'] ?? ($responseData['data']['authUrl'] ?? null));
            $flatRedirectUrl = $responseData['redirectUrl'] ?? ($responseData['data']['redirectUrl'] ?? null);

            // Créer un Payment en attente uniquement en cas de succès d'initiation
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'pawapay',
                'provider' => $data['provider'] ?? null,
                'payment_id' => $depositId,
                'amount' => $paymentAmount,
                'currency' => $paymentCurrency,
                'status' => 'pending',
                'payment_data' => [
                    'request' => $payload,
                    'response' => $responseData,
                ],
            ]);

            // Revenir au format exact initial: nos champs + payload fournisseur à la racine
            if (!is_array($responseData)) {
                $responseData = [];
            }

            // Dériver quelques champs attendus par le frontend (compat)
            $flat = is_array($responseData) && isset($responseData['data']) && is_array($responseData['data'])
                ? $responseData['data']
                : (is_array($responseData) ? $responseData : []);
            $derived = [
                'status' => $flat['status'] ?? ($responseData['status'] ?? null),
                'nextStep' => $flat['nextStep'] ?? ($responseData['nextStep'] ?? null),
                'authUrl' => $flat['authUrl'] ?? ($flat['authorizationUrl'] ?? ($responseData['authUrl'] ?? ($responseData['authorizationUrl'] ?? null))),
            ];

            $out = array_merge([
                'success' => true,
                'depositId' => $depositId,
                'order_id' => $order->id,
            ], $derived, (is_array($responseData) ? $responseData : []));

            return response()->json($out, 200, ['Content-Type' => 'application/json; charset=utf-8']);
        } catch (\Throwable $e) {
            // Erreur technique: annuler et marquer failed
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'pawapay',
                'provider' => $data['provider'] ?? null,
                'payment_id' => $depositId,
                'amount' => $paymentAmount,
                'currency' => $paymentCurrency,
                'status' => 'failed',
                'failure_reason' => 'Erreur technique lors de l\'initialisation',
                'payment_data' => [
                    'request' => $payload,
                    'exception' => [
                        'type' => get_class($e),
                        'message' => $e->getMessage(),
                    ],
                ],
            ]);
            $order->update(['status' => 'cancelled']);
            return response()->json([
                'success' => false,
                'message' => 'Erreur de communication avec le fournisseur. La commande a été annulée.',
            ], 502);
        }
    }

    public function status(string $depositId)
    {
        if (auth()->check()) {
            $this->autoCancelStale(auth()->id());
        }
        
        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl() . "/deposits/{$depositId}");

        $statusData = $response->json();
        
        // Selon la documentation pawaPay, la réponse peut avoir deux formats:
        // 1. Format simple: { "status": "COMPLETED", ... }
        // 2. Format wrapper: { "status": "FOUND", "data": { "status": "COMPLETED", ... } }
        
        // Vérifier si c'est le format wrapper
        if (isset($statusData['status']) && isset($statusData['data'])) {
            // Format wrapper: extraire le data
            $actualData = $statusData['data'];
            $metaStatus = $statusData['status']; // "FOUND" ou "NOT_FOUND"
            
            // Si NOT_FOUND, retourner une réponse appropriée
            if ($metaStatus === 'NOT_FOUND') {
                \Log::warning('pawaPay deposit not found', ['depositId' => $depositId]);
                return response()->json([
                    'status' => 'NOT_FOUND',
                    'message' => 'Deposit not found',
                ], $response->status());
            }
            
            \Log::info('pawaPay status check (wrapper format)', [
                'depositId' => $depositId,
                'meta_status' => $metaStatus,
                'deposit_status' => $actualData['status'] ?? null,
                'nextStep' => $actualData['nextStep'] ?? null,
                'full_response' => $statusData,
            ]);
            
            // Retourner le format flat pour compatibilité avec le frontend
            return response()->json($actualData, $response->status());
        } else {
            // Format simple: retourner tel quel
            \Log::info('pawaPay status check (simple format)', [
                'depositId' => $depositId,
                'status' => $statusData['status'] ?? null,
                'nextStep' => $statusData['nextStep'] ?? null,
                'full_response' => $statusData,
            ]);

            return response()->json($statusData, $response->status());
        }
    }

    public function webhook(Request $request)
    {
        // IMPORTANT: Toujours retourner 200 OK si le webhook est reçu avec succès
        // Selon la documentation pawaPay: https://docs.pawapay.io/v2/docs/what_to_know#callbacks
        // "We expect you to return HTTP 200 OK response to consider the callback delivered"
        // Si on retourne un code d'erreur, pawaPay réessaiera pendant 15 minutes
        
        // IMPORTANT: Valider la signature du webhook pour sécurité
        // Selon la documentation pawaPay: https://docs.pawapay.io/using_the_api
        $signature = $request->header('X-PawaPay-Signature');
        $payloadContent = $request->getContent();
        
        if ($signature && !$this->validateWebhookSignature($payloadContent, $signature)) {
            \Log::error('pawaPay webhook: Invalid signature - potential security threat', [
                'depositId' => $request->input('depositId'),
                'ip' => $request->ip(),
            ]);
            // CRITIQUE: Retourner 200 pour éviter les retry, mais logger comme erreur
            return response()->json(['received' => false, 'error' => 'Invalid signature'], 200);
        }

        $payload = $request->all();
        $depositId = $payload['depositId'] ?? null;
        $status = $payload['status'] ?? null;
        $nextStep = $payload['nextStep'] ?? null;

        if (!$depositId) {
            \Log::warning('pawaPay webhook: depositId missing', ['payload' => $payload]);
            // Retourner 200 OK même si depositId manquant (éviter retry)
            return response()->json(['received' => false, 'message' => 'depositId missing'], 200);
        }

        $payment = Payment::where('payment_method', 'pawapay')
            ->where('payment_id', $depositId)
            ->with(['order.orderItems', 'order.user'])
            ->first();

        if (!$payment) {
            \Log::warning('pawaPay webhook: Payment not found', ['depositId' => $depositId]);
            // Retourner 200 OK même si payment non trouvé (éviter retry sur transaction inexistante)
            return response()->json(['received' => false, 'message' => 'Payment not found'], 200);
        }

        // CRITIQUE: Envelopper le traitement dans un try-catch pour toujours retourner 200 OK
        // Selon la documentation, on DOIT retourner 200 OK même en cas d'erreur
        // sinon pawaPay réessaiera pendant 15 minutes
        try {
            // Log de tous les callbacks reçus pour traçabilité
            \Log::info('pawaPay webhook received', [
                'depositId' => $depositId,
                'status' => $status,
                'nextStep' => $nextStep,
                'current_order_status' => $payment->order?->status,
            ]);

            // Mapper le statut pawaPay vers le statut local
            $mapped = match ($status) {
                'COMPLETED' => 'completed',
                'FAILED' => 'failed',
                'ACCEPTED' => 'pending',
                'PROCESSING' => 'pending',
                'IN_RECONCILIATION' => 'pending', // Géré automatiquement par pawaPay, on attend
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
                'processed_at' => ($status === 'COMPLETED') ? now() : null,
            ]);

            // Traiter selon le statut final
			if ($status === 'COMPLETED' && $payment->order) {
				// Mettre à jour la commande avec la référence et les frais si fournis
				$feeAmount = $payload['feeAmount'] ?? ($payload['fees']['amount'] ?? null);
				$feeCurrency = $payload['feeCurrency'] ?? ($payload['fees']['currency'] ?? null);
				$updates = [
					'payment_reference' => $payment->order->payment_reference ?: ($payload['depositId'] ?? null),
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
                \Log::info('pawaPay: Order finalized after successful payment', [
                    'order_id' => $payment->order->id,
                    'depositId' => $depositId,
                ]);
            } elseif ($status === 'FAILED' && $payment->order) {
                // Échec : enregistrer la raison et annuler la commande
                $failureReason = $payload['statusReason'] ?? $payload['message'] ?? ($payload['reason'] ?? 'Paiement échoué');
                $payment->update(['failure_reason' => $failureReason]);
                
                // Annuler la commande seulement si elle n'est pas déjà payée (éviter doublon)
                if (!in_array($payment->order->status, ['paid', 'completed'])) {
                    $payment->order->update(['status' => 'cancelled']);
                }
                
                \Log::info('pawaPay: Order cancelled after failed payment', [
                    'order_id' => $payment->order->id,
                    'depositId' => $depositId,
                    'reason' => $failureReason,
                ]);
            } elseif ($status === 'IN_RECONCILIATION' && $payment->order) {
                // En réconciliation : attendre le statut final
                // Ne rien faire, pawaPay va automatiquement résoudre et renvoyer un callback
                \Log::info('pawaPay: Payment in reconciliation', [
                    'order_id' => $payment->order->id,
                    'depositId' => $depositId,
                ]);
            } elseif ($status === 'ACCEPTED' || $status === 'PROCESSING') {
                // En attente de traitement ou traitement en cours
                \Log::info('pawaPay: Payment accepted/processing', [
                    'order_id' => $payment->order?->id,
                    'depositId' => $depositId,
                    'status' => $status,
                ]);
            }

            return response()->json(['received' => true]);
            
        } catch (\Throwable $e) {
            // CRITIQUE: Logger l'erreur mais retourner 200 OK
            // pawaPay ne réessaiera pas si on retourne 200
            \Log::error('pawaPay webhook: Exception during processing', [
                'depositId' => $depositId,
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
     * Annuler une commande par depositId (annulation manuelle uniquement)
     * 
     * Selon la documentation pawaPay, la réconciliation est automatique.
     * Cette fonction est uniquement pour les annulations explicites par l'utilisateur.
     * Elle ne devrait PAS être appelée automatiquement (pas de timeout cancellation).
     */
    public function cancel(string $depositId)
    {
        $payment = Payment::where('payment_id', $depositId)->with('order')->first();
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Transaction introuvable'], 404);
        }

        // Vérifier que le paiement n'est pas déjà complété
        if ($payment->status === 'completed' || in_array($payment->order?->status ?? null, ['paid', 'completed'])) {
            \Log::warning('pawaPay cancel: Cannot cancel - payment already completed', [
                'depositId' => $depositId,
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
            $payment->update([
                'status' => 'failed',
                'failure_reason' => 'Annulation par l\'utilisateur',
            ]);

            if ($payment->order) {
                $payment->order->update(['status' => 'cancelled']);
            }
            
            \Log::info('pawaPay: Payment cancelled by user', [
                'depositId' => $depositId,
                'payment_id' => $payment->id,
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
            
            \Log::info('pawaPay: Starting finalization', [
                'order_id' => $order->id,
                'current_status' => $order->status,
                'order_total' => $order->total,
                'order_currency' => $order->currency,
                'user_id' => $order->user_id,
            ]);
            
            // Vérifier si déjà finalisée (idempotence)
            if (in_array($order->status, ['paid', 'completed'])) {
                \Log::info('pawaPay: Order already finalized', [
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
                \Log::info('pawaPay: OrderItems loaded directly from DB', [
                    'order_id' => $order->id,
                    'items_count' => $orderItems->count(),
                ]);
            }
            
            \Log::info('pawaPay: OrderItems loaded', [
                'order_id' => $order->id,
                'order_items_count' => $orderItems->count(),
                'items_data' => $orderItems->map(fn($item) => [
                    'id' => $item->id,
                    'course_id' => $item->course_id,
                    'price' => $item->price,
                ])->toArray(),
            ]);
            
            if ($orderItems->isEmpty()) {
                \Log::warning('pawaPay: No order items found for enrollment - proceeding to mark order paid', [
                    'order_id' => $order->id,
                ]);
            }

			// Mettre à jour l'Order : payé après confirmation du paiement
			$updated = $order->update([
				'status' => 'paid',
				'paid_at' => $order->paid_at ?: now(),
			]);

			\Log::info('pawaPay: Order marked as paid', [
                'order_id' => $order->id,
                'update_successful' => $updated,
                'new_status' => $order->fresh()->status,
            ]);

            // Créer les Enrollments pour chaque cours
            $enrollmentsCreated = 0;
            
            foreach ($orderItems as $orderItem) {
                \Log::info('pawaPay: Processing order item', [
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
                    $enrollment = Enrollment::create([
                        'user_id' => $order->user_id,
                        'course_id' => $orderItem->course_id,
                        'order_id' => $order->id,
                        'status' => 'active',
                    ]);
                    $enrollmentsCreated++;
                    
                    \Log::info('pawaPay: Enrollment created', [
                        'enrollment_id' => $enrollment->id,
                        'order_id' => $order->id,
                        'course_id' => $orderItem->course_id,
                        'user_id' => $order->user_id,
                    ]);
                } else {
                    \Log::info('pawaPay: Enrollment already exists', [
                        'order_id' => $order->id,
                        'course_id' => $orderItem->course_id,
                        'existing_enrollment_id' => $existingEnrollment->id,
                    ]);
                }
            }

            \Log::info('pawaPay: Enrollments created', [
                'order_id' => $order->id,
                'enrollments_created' => $enrollmentsCreated,
                'total_order_items' => $orderItems->count(),
            ]);

			// Vider le panier de l'utilisateur (DB + session par sécurité)
			$cartItemsBeforeDelete = CartItem::where('user_id', $order->user_id)->count();
			$cartItemsDeleted = CartItem::where('user_id', $order->user_id)->delete();
			Session::forget('cart');
			
			\Log::info('pawaPay: Cart emptied', [
				'user_id' => $order->user_id,
				'cart_items_before' => $cartItemsBeforeDelete,
				'cart_items_deleted' => $cartItemsDeleted,
				'session_cart_cleared' => true,
			]);
            
            // Envoyer une notification de paiement confirmé à l'utilisateur (éviter doublons)
            try {
                $alreadyNotified = method_exists($order->user, 'notifications')
                    ? $order->user->notifications()
                        ->where('type', PaymentReceived::class)
                        ->where('data->order_id', $order->id)
                        ->exists()
                    : false;
                if (!$alreadyNotified) {
                    $order->user->notify(new PaymentReceived($order));
                    \Log::info('pawaPay: Payment confirmation notification sent', [
                        'user_id' => $order->user_id,
                        'order_id' => $order->id,
                    ]);
                } else {
                    \Log::info('pawaPay: Payment confirmation notification skipped (already sent)', [
                        'user_id' => $order->user_id,
                        'order_id' => $order->id,
                    ]);
                }
            } catch (\Throwable $e) {
                // Logger l'erreur mais ne pas faire échouer la finalisation
                \Log::error('pawaPay: Failed to send payment notification', [
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            
            \Log::info('pawaPay: Finalization completed successfully', [
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
        $depositId = $request->query('depositId');
        
        if ($depositId) {
            $payment = Payment::where('payment_method', 'pawapay')
                ->where('payment_id', $depositId)
                ->with('order')
                ->first();

            if ($payment && $payment->order) {
                // VALIDATION RECOMMANDÉE : Vérifier le statut auprès de pawaPay
                // comme recommandé dans la documentation pour garantir la cohérence
                $statusResponse = Http::withHeaders($this->authHeaders())
                    ->get($this->baseUrl() . "/deposits/{$depositId}");

                if ($statusResponse->successful()) {
                    $statusData = $statusResponse->json();
                    // Gérer le format wrapper: { status: "FOUND", data: { status: "COMPLETED", ... } }
                    if (isset($statusData['status']) && isset($statusData['data']) && is_array($statusData['data'])) {
                        $actualData = $statusData['data'];
                        $status = $actualData['status'] ?? null;
                        $nextStep = $actualData['nextStep'] ?? null;
                        $authUrl = $actualData['authUrl'] ?? ($actualData['authorizationUrl'] ?? null);
                    } else {
                        $status = $statusData['status'] ?? null;
                        $nextStep = $statusData['nextStep'] ?? null;
                        $authUrl = $statusData['authUrl'] ?? ($statusData['authorizationUrl'] ?? null);
                    }

                    \Log::info('pawaPay: Status check on successful redirect', [
                        'depositId' => $depositId,
                        'status' => $status,
                        'nextStep' => $nextStep,
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

                    // Traiter tous les statuts possibles
                    if ($status === 'COMPLETED') {
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
                        $feeSource = isset($actualData) ? $actualData : $statusData;
                        $feeAmount = $feeSource['feeAmount'] ?? ($feeSource['fees']['amount'] ?? null);
                        $feeCurrency = $feeSource['feeCurrency'] ?? ($feeSource['fees']['currency'] ?? null);
						$updates = [
							'payment_reference' => $payment->order->payment_reference ?: ($depositId ?? null),
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
                        if (!in_array($payment->order->status, ['paid', 'completed'])) {
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
                        
                        // Assurer la notification en contexte redirection si non envoyée
                        try {
                            $orderFresh = $payment->order->fresh(['user']);
                            $alreadyNotified = method_exists($orderFresh->user, 'notifications')
                                ? $orderFresh->user->notifications()
                                    ->where('type', PaymentReceived::class)
                                    ->where('data->order_id', $orderFresh->id)
                                    ->exists()
                                : false;
                            if (!$alreadyNotified) {
                                $orderFresh->user->notify(new PaymentReceived($orderFresh));
                                \Log::info('pawaPay: Payment confirmation notification sent on redirect', [
                                    'user_id' => $orderFresh->user_id,
                                    'order_id' => $orderFresh->id,
                                ]);
                            }
                        } catch (\Throwable $e) {
                            \Log::error('pawaPay: Failed to send payment notification on redirect', [
                                'user_id' => $payment->order->user_id,
                                'order_id' => $payment->order->id,
                                'error' => $e->getMessage(),
                            ]);
                        }

                        $order = $payment->order->fresh();
                        return view('payments.pawapay.success', compact('order'));
                        
                    } elseif ($status === 'FAILED') {
                        // Échec : rediriger vers la page d'échec
                        $failureReason = $statusData['statusReason'] ?? $statusData['message'] ?? ($statusData['reason'] ?? 'Paiement échoué');
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
                        
                        \Log::warning('pawaPay: Redirected to failed page', [
                            'depositId' => $depositId,
                            'reason' => $failureReason,
                        ]);
                        
                        return redirect()->route('pawapay.failed');
                        
                    } elseif ($status === 'IN_RECONCILIATION') {
                        // En réconciliation : informer l'utilisateur que le paiement est en cours de validation
                        \Log::info('pawaPay: Payment in reconciliation on redirect', ['depositId' => $depositId]);
                        
                        return view('payments.pawapay.success', [
                            'order' => null,
                            'reconciliation_warning' => true,
                            'depositId' => $depositId,
                        ]);
                        
                    } elseif ($status === 'PROCESSING' || $status === 'ACCEPTED') {
                        // En cours de traitement : informer l'utilisateur
                        \Log::info('pawaPay: Payment still processing on redirect', [
                            'depositId' => $depositId,
                            'status' => $status,
                        ]);
                        
                        return view('payments.pawapay.success', [
                            'order' => null,
                            'processing_warning' => true,
                            'depositId' => $depositId,
                        ]);
                    } else {
                        // Statut inconnu : afficher quand même la page de succès
                        \Log::warning('pawaPay: Unknown status on redirect', [
                            'depositId' => $depositId,
                            'status' => $status,
                        ]);
                        
                        $order = $payment->order->fresh();
                        return view('payments.pawapay.success', compact('order'));
                    }
                } else {
                    // Erreur lors de la vérification : continuer avec le statut local
                    \Log::warning('pawaPay: Failed to check status on redirect', [
                        'depositId' => $depositId,
                        'response_status' => $statusResponse->status(),
                    ]);
                }
            }
        }

        return view('payments.pawapay.success');
    }

    public function failedRedirect(Request $request)
    {
        if (auth()->check()) {
            $this->autoCancelStale(auth()->id());
        }
        return view('payments.pawapay.failed');
    }

    private function autoCancelStale(int $userId): void
    {
        $timeoutMinutes = (int) (env('ORDER_PENDING_TIMEOUT_MIN', 30));
        $threshold = now()->subMinutes($timeoutMinutes);
        $orders = Order::where('user_id', $userId)
            ->where('status', 'pending')
            ->where('created_at', '<', $threshold)
            ->get();
        foreach ($orders as $order) {
            $order->update(['status' => 'cancelled']);
            Payment::where('order_id', $order->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'failed',
                    'failure_reason' => 'Annulation automatique après délai',
                ]);
        }
    }
}


