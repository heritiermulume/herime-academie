<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMail;
use App\Mail\PaymentFailedMail;
use App\Mail\PendingPaymentReminderMail;
use App\Models\AmbassadorCommission;
use App\Models\AmbassadorPromoCode;
use App\Models\CartItem;
use App\Models\CartPackage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\SentEmail;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AdminSubscriptionInvoiceFailed;
use App\Notifications\PaymentReceived;
use App\Notifications\SubscriptionInvoiceFailed;
use App\Services\OrderEnrollmentService;
use App\Services\SubscriptionNotificationDispatcher;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
    private const DEFAULT_PENDING_PAYMENT_REMINDER_DELAY_MINUTES = 10;

    private const DEFAULT_PENDING_ORDER_AUTO_CANCEL_DELAY_MINUTES = 20;

    private const AUTO_CANCELLATION_TIMEOUT_REASON = 'Paiement annulé automatiquement en raison d\'un dépassement du délai d\'attente de confirmation. Vous pouvez relancer le paiement depuis votre commande.';

    private const RETRY_REPLACED_ORDER_REASON = 'Paiement annulé automatiquement : une nouvelle tentative de paiement a été lancée pour cette commande.';

    private const CANCELLED_WITHOUT_AUTORETRY_REASON = 'Paiement annulé automatiquement : la tentative en cours a été interrompue. Vous pouvez relancer le paiement manuellement.';

    private function baseUrl(): string
    {
        return rtrim(config('services.moneroo.base_url', 'https://api.moneroo.io/v1'), '/');
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.config('services.moneroo.api_key'),
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
     * Indique si une commande payée donne encore un accès actif pour chaque ligne du panier
     * (mêmes content_id). Sinon révocation admin : ne pas bloquer un nouveau paiement.
     *
     * @param  array<int|string>  $contentIds
     */
    private function paidOrderStillGrantsActiveAccessForContents(Order $order, array $contentIds): bool
    {
        $contentIdSet = array_fill_keys(array_map('intval', $contentIds), true);
        $notes = (string) ($order->notes ?? '');

        foreach ($order->orderItems as $item) {
            $cid = (int) $item->content_id;
            if (! isset($contentIdSet[$cid])) {
                continue;
            }
            $pkgId = (int) ($item->content_package_id ?? 0);
            if ($pkgId > 0) {
                if (str_contains($notes, '[PACK_REVOKED:'.$pkgId.']')) {
                    return false;
                }
            } elseif (str_contains($notes, '[COURSE_REVOKED:'.$cid.']')) {
                return false;
            }
        }

        return true;
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
        if (! $webhookSecret || ! $signature) {
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
            $this->maybeAutoCancelStaleOrdersForUser((int) auth()->id());
        }

        $query = [];
        // Si un pays est fourni, on filtre
        if ($request->filled('country')) {
            $query['country'] = $request->query('country');
        }

        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl().'/payments/methods', $query);

        $responseData = $response->json();

        // Adapter le format de réponse Moneroo au format attendu par le frontend
        if (isset($responseData['success']) && $responseData['success'] && isset($responseData['data'])) {
            return response()->json($responseData['data'], $response->status());
        }

        return response()->json($responseData, $response->status());
    }

    /**
     * Propriétaire du panier pour Moneroo : utilisateur en session ou intent invité (compte existant).
     */
    private function resolveMonerooCartOwner(): ?User
    {
        if (auth()->check()) {
            return auth()->user();
        }
        if (! Session::get(CartController::GUEST_PAY_READY_KEY)) {
            return null;
        }
        $id = (int) Session::get(CartController::GUEST_PAY_USER_ID_KEY);
        if ($id <= 0) {
            return null;
        }

        return User::find($id);
    }

    public function initiate(Request $request)
    {
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
                            'message' => 'Code promo valide',
                        ]);
                    }
                }
            }

            return response()->json([
                'valid' => false,
                'message' => 'Code promo invalide ou expiré',
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

        $user = $this->resolveMonerooCartOwner();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour procéder au paiement.',
            ], 401);
        }

        // Récupérer les articles du panier (contenus + packs)
        $cartItems = $user->cartItems()->with('course')->get();
        $cartItems = $cartItems->filter(function ($item) {
            return $item->course !== null && $item->course->is_published && $item->course->is_sale_enabled;
        })->values();

        $cartPackages = $user->cartPackages()->with([
            'contentPackage.contents' => fn ($q) => $q->orderByPivot('sort_order'),
        ])->get();
        $cartPackages = $cartPackages->filter(function ($row) {
            $pkg = $row->contentPackage;
            if (! $pkg || ! $pkg->is_published || ! $pkg->is_sale_enabled || $pkg->contents->isEmpty()) {
                return false;
            }
            foreach ($pkg->contents as $c) {
                if (! $c->is_published || ! $c->is_sale_enabled || $c->is_free) {
                    return false;
                }
            }

            return true;
        })->values();

        if ($cartItems->isEmpty() && $cartPackages->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Votre panier est vide ou contient uniquement des articles non disponibles.',
            ], 400);
        }

        // PROTECTION CONTRE LES DOUBLES PAIEMENTS
        // Empreinte = tous les content_id (y compris via packs), triés
        $contentIds = $cartItems->pluck('content_id')
            ->merge($cartPackages->flatMap(fn ($r) => $r->contentPackage->contents->pluck('id')))
            ->unique()
            ->sort()
            ->values()
            ->toArray();
        $recentPaidOrder = Order::where('user_id', $user->id)
            ->whereIn('status', ['paid', 'completed'])
            ->where('created_at', '>=', now()->subHours(24))
            ->whereHas('orderItems', function ($query) use ($contentIds) {
                $query->whereIn('content_id', $contentIds);
            })
            ->with(['orderItems' => function ($query) use ($contentIds) {
                $query->whereIn('content_id', $contentIds);
            }])
            ->get()
            ->filter(function ($order) use ($contentIds) {
                $orderCourseIds = $order->orderItems->pluck('content_id')->sort()->values()->toArray();
                if ($orderCourseIds !== $contentIds) {
                    return false;
                }

                return $this->paidOrderStillGrantsActiveAccessForContents($order, $contentIds);
            })
            ->first();

        if ($recentPaidOrder) {
            \Log::warning('Moneroo: Attempted duplicate payment for already paid order', [
                'user_id' => $user->id,
                'existing_order_id' => $recentPaidOrder->id,
                'existing_order_number' => $recentPaidOrder->order_number,
                'content_ids' => $contentIds,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà une commande payée pour ces cours. Veuillez vérifier vos commandes.',
                'existing_order_id' => $recentPaidOrder->id,
                'existing_order_number' => $recentPaidOrder->order_number,
                'redirect_url' => route('orders.show', $recentPaidOrder->id),
            ], 409); // 409 Conflict
        }

        // PROTECTION CONTRE LES COMMANDES EN ATTENTE MULTIPLES
        // Vérifier s'il y a une commande en attente récente (dernières 5 minutes) avec les mêmes cours
        $recentPendingOrder = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->whereHas('orderItems', function ($query) use ($contentIds) {
                $query->whereIn('content_id', $contentIds);
            })
            ->with(['orderItems' => function ($query) use ($contentIds) {
                $query->whereIn('content_id', $contentIds);
            }, 'payments'])
            ->get()
            ->filter(function ($order) use ($contentIds) {
                $orderCourseIds = $order->orderItems->pluck('content_id')->sort()->values()->toArray();

                return $orderCourseIds === $contentIds;
            })
            ->first();

        if ($recentPendingOrder) {
            // Vérifier si cette commande a déjà un paiement en cours (pending)
            $existingPayment = $recentPendingOrder->payments()
                ->where('status', 'pending')
                ->where('created_at', '>=', now()->subMinutes(10))
                ->first();

            if ($existingPayment) {
                // Marquer le paiement en attente comme échoué pour permettre la création d'une nouvelle commande
                \Log::info('Moneroo: Marking existing pending payment as failed to allow new order', [
                    'user_id' => $user->id,
                    'order_id' => $recentPendingOrder->id,
                    'payment_id' => $existingPayment->payment_id,
                ]);

                $existingPayment->update([
                    'status' => 'failed',
                    'failure_reason' => self::RETRY_REPLACED_ORDER_REASON,
                ]);
            }

            // IMPORTANT: Si la commande existe mais que tous les paiements ont échoué,
            // annuler l'ancienne commande et créer une nouvelle pour permettre une nouvelle tentative
            // Vérifier qu'il n'y a pas de paiement complété (sécurité)
            $hasCompletedPayment = $recentPendingOrder->payments()
                ->where('status', 'completed')
                ->exists();

            // Vérifier si tous les paiements ont échoué (pas de pending, pas de completed)
            $allPaymentsFailed = ! $hasCompletedPayment
                && $recentPendingOrder->payments()
                    ->where('status', 'failed')
                    ->exists()
                && ! $recentPendingOrder->payments()
                    ->where('status', 'pending')
                    ->exists();

            if ($hasCompletedPayment) {
                // Si un paiement est complété, ne pas créer de nouvelle commande
                // Cela ne devrait pas arriver car on vérifie les commandes payées plus haut,
                // mais c'est une sécurité supplémentaire
                \Log::warning('Moneroo: Order has completed payment but status is still pending', [
                    'user_id' => $user->id,
                    'order_id' => $recentPendingOrder->id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Cette commande a déjà un paiement complété. Veuillez vérifier vos commandes.',
                    'existing_order_id' => $recentPendingOrder->id,
                    'redirect_url' => route('orders.show', $recentPendingOrder->id),
                ], 409);
            } elseif ($allPaymentsFailed) {
                // Tous les paiements ont échoué : annuler l'ancienne commande et créer une nouvelle
                \Log::info('Moneroo: Previous payment failed, cancelling old order and creating new one', [
                    'user_id' => $user->id,
                    'old_order_id' => $recentPendingOrder->id,
                    'content_ids' => $contentIds,
                ]);

                // Annuler l'ancienne commande
                Payment::where('order_id', $recentPendingOrder->id)
                    ->whereIn('status', ['pending', 'processing'])
                    ->update([
                        'status' => 'failed',
                        'failure_reason' => self::RETRY_REPLACED_ORDER_REASON,
                    ]);
                $recentPendingOrder->update(['status' => 'cancelled']);

                // Continuer pour créer une nouvelle commande (le code continue après ce bloc)
            } elseif (! $existingPayment) {
                // Si pas de paiement en cours et pas tous échoués, vérifier l'âge de la commande
                // Si elle est trop ancienne (>5 min), l'annuler pour créer une nouvelle
                $orderAge = now()->diffInMinutes($recentPendingOrder->created_at);
                if ($orderAge >= 5) {
                    \Log::info('Moneroo: Old pending order found, cancelling and creating new one', [
                        'user_id' => $user->id,
                        'old_order_id' => $recentPendingOrder->id,
                        'order_age_minutes' => $orderAge,
                    ]);

                    Payment::where('order_id', $recentPendingOrder->id)
                        ->whereIn('status', ['pending', 'processing'])
                        ->update([
                            'status' => 'failed',
                            'failure_reason' => self::RETRY_REPLACED_ORDER_REASON,
                        ]);
                    $recentPendingOrder->update(['status' => 'cancelled']);
                }
            }
        }

        // Récupérer la devise de base du site
        $baseCurrency = \App\Models\Setting::getBaseCurrency();

        // Calculer le total réel depuis le panier (dans la devise de base du site)
        $subtotal = $cartItems->sum(function ($item) {
            return optional($item->course)->current_price ?? optional($item->course)->price ?? 0;
        });
        $subtotal += $cartPackages->sum(function ($row) {
            return (float) ($row->contentPackage->effective_price ?? 0);
        });

        // Valider et appliquer le code promo d'ambassadeur si fourni (requête ou session)
        $ambassadorPromoCode = null;
        $ambassador = null;

        // Vérifier d'abord dans la requête, puis dans la session
        $promoCodeData = null;
        if ($request->filled('ambassador_promo_code')) {
            $promoCodeData = ['code' => $request->ambassador_promo_code];
        } elseif (Session::has('applied_promo_code')) {
            $promoCodeData = Session::get('applied_promo_code');
        }

        if ($promoCodeData) {
            $promoCode = AmbassadorPromoCode::where('code', $promoCodeData['code'] ?? null)
                ->where('is_active', true)
                ->with('ambassador')
                ->first();

            if ($promoCode && $promoCode->isValid()) {
                $ambassadorPromoCode = $promoCode;
                $ambassador = $promoCode->ambassador;

                // Vérifier que l'ambassadeur est actif
                if ($ambassador && $ambassador->is_active) {
                    // Le code promo est valide, on l'associera à la commande
                    // Note: Les codes promo d'ambassadeur ne donnent pas de réduction,
                    // ils servent uniquement à attribuer la commission à l'ambassadeur
                    \Log::info('Moneroo: Ambassador promo code applied to order', [
                        'code' => $promoCode->code,
                        'ambassador_id' => $ambassador->id,
                        'ambassador_name' => $ambassador->user->name ?? 'N/A',
                    ]);
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
            'order_number' => 'MON-'.strtoupper(Str::random(8)).'-'.time(),
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

        // Créer les OrderItems (lignes contenu seul)
        foreach ($cartItems as $cartItem) {
            if (! $cartItem->course) {
                continue;
            }
            $coursePrice = $cartItem->course->current_price ?? $cartItem->course->price ?? 0;
            OrderItem::create([
                'order_id' => $order->id,
                'content_id' => $cartItem->content_id,
                'content_package_id' => null,
                'price' => $cartItem->course->price ?? 0,
                'sale_price' => $cartItem->course->is_sale_active ? $cartItem->course->active_sale_price : null,
                'total' => $coursePrice,
            ]);
        }

        // Packs : une ligne par contenu ; le prix du pack sur la première ligne uniquement
        foreach ($cartPackages as $row) {
            $pkg = $row->contentPackage;
            if (! $pkg) {
                continue;
            }
            $effective = (float) ($pkg->effective_price ?? 0);
            $first = true;
            foreach ($pkg->contents as $course) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'content_id' => $course->id,
                    'content_package_id' => $pkg->id,
                    'price' => $first ? (float) $pkg->price : 0,
                    'sale_price' => $first && $pkg->is_sale_active ? $pkg->sale_price : null,
                    'total' => $first ? $effective : 0,
                ]);
                $first = false;
            }
        }

        $paymentId = 'pay_'.strtoupper(Str::random(16)).'_'.time();

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

        // S'assurer que Moneroo reçoit toujours des chaînes non nulles pour first_name / last_name
        $rawName = $user->name ?? '';
        $firstName = (string) $this->extractFirstName((string) $rawName);
        $lastName = (string) $this->extractLastName((string) $rawName);

        // Si le nom complet est vide, dériver un fallback depuis l'email ou utiliser un défaut
        if ($firstName === '' && ! empty($user->email)) {
            $emailLocal = strstr($user->email, '@', true) ?: $user->email;
            $firstName = ucfirst($emailLocal);
        }

        if ($firstName === '') {
            $firstName = 'Client';
        }

        // Si aucun nom de famille n'est disponible, réutiliser le prénom ou un fallback
        if ($lastName === '') {
            $lastName = $firstName ?: 'Client';
        }

        $payload = [
            'amount' => $amountInSmallestUnit, // Montant en unité de la devise (integer requis par Moneroo)
            'currency' => $paymentCurrency,
            'description' => config('services.moneroo.company_name', 'Herime Académie').' - Paiement commande '.$order->order_number,
            'return_url' => config('services.moneroo.successful_url', route('moneroo.success')).'?payment_id='.$paymentId,
            'customer' => [
                'email' => $user->email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ],
            'metadata' => [
                'order_id' => (string) $order->id,
                'order_number' => $order->order_number,
                'user_id' => (string) $user->id,
            ],
        ];

        // Ajouter customer.phone et country si disponibles (optionnels)
        if (! empty($data['phoneNumber'])) {
            $payload['customer']['phone'] = $data['phoneNumber'];
        }
        if (! empty($data['country'])) {
            $payload['customer']['country'] = $data['country'];
        }

        try {
            // Appel API Moneroo pour initialiser le paiement (intégration standard)
            // Endpoint selon la documentation: POST /v1/payments/initialize
            $response = Http::withHeaders($this->authHeaders())
                ->post($this->baseUrl().'/payments/initialize', $payload);

            $responseData = $response->json();

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

            if (! $isSuccess) {
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
                    $failureReason = 'Aucune méthode de paiement activée pour la devise '.$paymentCurrency.'. Veuillez contacter le support ou activer les méthodes de paiement pour cette devise dans votre compte Moneroo.';
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
            if (! $redirectUrl) {
                \Log::error('Moneroo: Pas d\'URL checkout_url dans la réponse', [
                    'response' => $responseData,
                    'payment_data' => $paymentData,
                    'response_keys' => array_keys($paymentData ?? []),
                ]);
                throw new \Exception('Moneroo n\'a pas retourné d\'URL de checkout pour la page de paiement');
            }

            // Créer un Payment en attente uniquement en cas de succès d'initiation
            // IMPORTANT: Stocker notre référence locale dans payment_id pour la redirection
            // et l'ID Moneroo dans payment_data pour la vérification
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'moneroo',
                'provider' => $data['provider'] ?? null, // Peut être null pour intégration standard
                'payment_id' => $paymentId, // Notre référence locale (utilisée dans return_url)
                'amount' => $paymentAmount,
                'currency' => $paymentCurrency,
                'status' => 'pending',
                'payment_data' => [
                    'request' => $payload,
                    'response' => $responseData,
                    'moneroo_id' => $actualPaymentId, // ID Moneroo réel (pour vérification API)
                    'local_reference' => $paymentId, // Notre référence locale
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
        $cartOwner = $this->resolveMonerooCartOwner();
        if ($cartOwner) {
            $this->maybeAutoCancelStaleOrdersForUser((int) $cartOwner->id);
        }

        // Utiliser l'endpoint /verify selon la documentation Moneroo
        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl()."/payments/{$paymentId}/verify");

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
                        $failureReason = $this->extractFailureReason($paymentData, $responseData, (string) $status);
                        $failureReason = $this->contextualizeFailureReasonForStalePendingOrder($payment->order, (string) $status, $failureReason);
                        $payment->update([
                            'status' => 'failed',
                            'failure_reason' => $failureReason,
                        ]);
                    }
                    if ($payment->order && ! in_array($payment->order->status, ['paid', 'completed'])) {
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

    /**
     * Sync Moneroo + annulation des commandes pending trop anciennes, au plus une fois par fenêtre
     * (défaut 10 min, config payments.visit_processing_cache_seconds). Utilisé par le middleware web GET.
     */
    public function runThrottledVisitPaymentMaintenance(int $userId): void
    {
        $ttl = max(0, (int) config('payments.visit_processing_cache_seconds', 600));
        $key = 'payments:web_visit_maintenance:'.$userId;

        if ($ttl > 0 && Cache::has($key)) {
            return;
        }

        $this->syncPendingPaymentsForUser($userId);
        $this->autoCancelStale($userId);

        if ($ttl > 0) {
            Cache::put($key, true, now()->addSeconds($ttl));
        }
    }

    /**
     * Annulation des commandes pending trop anciennes, avec le même délai minimal qu’en navigation.
     * Évite de répéter le travail si runThrottledVisitPaymentMaintenance vient d’être exécuté (même fenêtre).
     */
    public function maybeAutoCancelStaleOrdersForUser(int $userId): void
    {
        $ttl = max(0, (int) config('payments.visit_processing_cache_seconds', 600));
        $maintenanceKey = 'payments:web_visit_maintenance:'.$userId;

        if ($ttl > 0 && Cache::has($maintenanceKey)) {
            return;
        }

        $key = 'payments:cancel_stale_orders:'.$userId;

        if ($ttl > 0 && Cache::has($key)) {
            return;
        }

        $this->autoCancelStale($userId);

        if ($ttl > 0) {
            Cache::put($key, true, now()->addSeconds($ttl));
        }
    }

    /**
     * Synchroniser automatiquement les paiements Moneroo en attente.
     * Appelé depuis runThrottledVisitPaymentMaintenance (navigation) ou runScheduledPaymentMaintenance (cron).
     * - Pour les admins : synchronise TOUTES les commandes en attente (données à jour).
     * - Pour les clients : synchronise uniquement les paiements de l'utilisateur.
     */
    public function syncPendingPaymentsForUser(int $userId): void
    {
        $user = \App\Models\User::find($userId);
        if ($user && $user->isAdmin()) {
            $this->syncAllPendingPayments();
        } else {
            $this->syncPendingPaymentsForUserId($userId);
        }
    }

    /**
     * Synchroniser TOUS les paiements Moneroo en attente (pour les admins).
     */
    public function syncAllPendingPayments(): void
    {
        $payments = Payment::where('payment_method', 'moneroo')
            ->whereIn('status', ['pending', 'processing'])
            ->where('created_at', '>=', now()->subHours(48))
            ->with(['order.orderItems', 'order.user'])
            ->get();

        foreach ($payments as $payment) {
            try {
                $this->verifyAndProcessPaymentForSync($payment);
            } catch (\Throwable $e) {
                \Log::error('Moneroo syncAllPendingPayments: Error processing payment', [
                    'payment_id' => $payment->payment_id,
                    'order_id' => $payment->order_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Synchroniser les paiements Moneroo en attente pour un utilisateur spécifique.
     */
    private function syncPendingPaymentsForUserId(int $userId): void
    {
        $payments = Payment::where('payment_method', 'moneroo')
            ->whereIn('status', ['pending', 'processing'])
            ->whereHas('order', fn ($q) => $q->where('user_id', $userId))
            ->where('created_at', '>=', now()->subHours(48))
            ->with(['order.orderItems', 'order.user'])
            ->get();

        foreach ($payments as $payment) {
            try {
                $this->verifyAndProcessPaymentForSync($payment);
            } catch (\Throwable $e) {
                \Log::error('Moneroo syncPendingPaymentsForUserId: Error processing payment', [
                    'payment_id' => $payment->payment_id,
                    'order_id' => $payment->order_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Vérifier un paiement auprès de Moneroo et finaliser si completed.
     */
    private function verifyAndProcessPaymentForSync(Payment $payment): void
    {
        $monerooPaymentId = data_get($payment->payment_data, 'moneroo_id')
            ?? data_get($payment->payment_data, 'response.data.id')
            ?? data_get($payment->payment_data, 'data.id')
            ?? $payment->payment_id;

        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl()."/payments/{$monerooPaymentId}/verify");

        if (! $response->successful()) {
            return;
        }

        $responseData = $response->json();
        $statusData = $responseData['data'] ?? $responseData;
        $status = $statusData['status'] ?? null;
        $isProcessed = $statusData['is_processed'] ?? false;
        $processedAt = $statusData['processed_at'] ?? null;

        if (in_array($status, ['success', 'completed'])) {
            $payment->update([
                'status' => 'completed',
                'processed_at' => $processedAt ? \Carbon\Carbon::parse($processedAt) : now(),
                'payment_data' => array_merge($payment->payment_data ?? [], [
                    'auto_sync' => true,
                    'synced_at' => now()->toIso8601String(),
                ]),
            ]);
            $this->finalizeOrderAfterPayment($payment->order);
            \Log::info('Moneroo: Order finalized after auto-sync', [
                'order_id' => $payment->order->id,
                'payment_id' => $payment->payment_id,
            ]);

            return;
        }

        if (in_array($status, ['failed', 'cancelled', 'expired', 'rejected']) && $payment->order) {
            $failureReason = $this->extractFailureReason($statusData, $responseData, $status);
            $failureReason = $this->contextualizeFailureReasonForStalePendingOrder($payment->order, (string) $status, $failureReason);
            $payment->update(['status' => 'failed', 'failure_reason' => $failureReason]);
            if (! in_array($payment->order->status, ['paid', 'completed'])) {
                $payment->order->update(['status' => 'cancelled']);
            }
            $this->sendPaymentFailureNotifications($payment->order, $failureReason);
        }
    }

    /**
     * Vérifier manuellement le statut d'un paiement pour une commande en attente.
     * Utile lorsque la page de redirection n'a pas chargé (problème navigateur/connexion)
     * et que le client a été débité mais la plateforme n'a pas enregistré la transaction.
     */
    public function verifyOrderPayment(Order $order)
    {
        $owner = $this->resolveMonerooCartOwner();
        if (! $owner || (int) $order->user_id !== (int) $owner->id) {
            abort(403, 'Accès non autorisé à cette commande.');
        }

        if (in_array($order->status, ['paid', 'completed'])) {
            return redirect()->route('orders.show', $order)->with('info',
                'Cette commande est déjà enregistrée comme payée.'
            );
        }

        $payment = Payment::where('payment_method', 'moneroo')
            ->where('order_id', $order->id)
            ->whereIn('status', ['pending', 'processing'])
            ->with('order')
            ->latest()
            ->first();

        if (! $payment) {
            return redirect()->route('orders.index')->with('error',
                'Aucun paiement en attente trouvé pour cette commande.'
            );
        }

        $monerooPaymentId = data_get($payment->payment_data, 'moneroo_id')
                         ?? data_get($payment->payment_data, 'response.data.id')
                         ?? data_get($payment->payment_data, 'data.id')
                         ?? $payment->payment_id;

        $statusResponse = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl()."/payments/{$monerooPaymentId}/verify");

        if (! $statusResponse->successful()) {
            \Log::warning('Moneroo verifyOrderPayment: API verification failed', [
                'order_id' => $order->id,
                'payment_id' => $payment->payment_id,
                'response_status' => $statusResponse->status(),
            ]);

            return redirect()->route('orders.show', $order)->with('error',
                'Impossible de vérifier le paiement. Veuillez réessayer ou contacter le support.'
            );
        }

        $responseData = $statusResponse->json();
        $statusData = $responseData['data'] ?? $responseData;
        $status = $statusData['status'] ?? null;
        $isProcessed = $statusData['is_processed'] ?? false;
        $processedAt = $statusData['processed_at'] ?? null;

        if (in_array($status, ['success', 'completed'])) {
            $payment->update([
                'status' => 'completed',
                'processed_at' => $processedAt ? \Carbon\Carbon::parse($processedAt) : now(),
                'payment_data' => array_merge($payment->payment_data ?? [], [
                    'manual_verify' => $statusData,
                    'verified_at' => now()->toIso8601String(),
                ]),
            ]);
            $this->finalizeOrderAfterPayment($payment->order);

            if (data_get($payment->order->billing_info, 'subscription_invoice_id')) {
                app(\App\Services\SubscriptionService::class)
                    ->applyPaidStateFromVerifiedSubscriptionOrder($payment->order->fresh());
            }

            \Log::info('Moneroo: Order finalized after manual verification', [
                'order_id' => $order->id,
                'payment_id' => $payment->payment_id,
            ]);

            return redirect()->route('orders.show', $order)->with('success',
                'Paiement confirmé ! Votre commande a été enregistrée. Vous avez maintenant accès à vos contenus.'
            );
        }

        if (in_array($status, ['failed', 'cancelled', 'expired', 'rejected'])) {
            $failureReason = $this->extractFailureReason($statusData, $responseData, $status);
            $failureReason = $this->contextualizeFailureReasonForStalePendingOrder($order, (string) $status, $failureReason);
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $failureReason,
            ]);
            if (! in_array($order->status, ['paid', 'completed'])) {
                $order->update(['status' => 'cancelled']);
            }
            $this->sendPaymentFailureNotifications($order, $failureReason);

            return redirect()->route('orders.show', $order)->with('error',
                'Le paiement n\'a pas abouti : '.$failureReason
            );
        }

        return redirect()->route('orders.show', $order)->with('info',
            'Votre paiement est encore en cours de traitement. Veuillez patienter quelques minutes puis réessayer.'
        );
    }

    public function webhook(Request $request)
    {
        // IMPORTANT: Toujours retourner 200 OK si le webhook est reçu avec succès
        // Selon la documentation Moneroo, on doit retourner 200 OK pour confirmer la réception

        // IMPORTANT: Valider la signature du webhook pour sécurité
        $signature = $request->header('X-Moneroo-Signature') ?? $request->header('X-Signature');
        $payloadContent = $request->getContent();

        if ($signature && ! $this->validateWebhookSignature($payloadContent, $signature)) {
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

        if (! $paymentId) {
            \Log::warning('Moneroo webhook: payment_id missing', ['payload' => $payload]);

            // Retourner 200 OK même si payment_id manquant (éviter retry)
            return response()->json(['received' => false, 'message' => 'payment_id missing'], 200);
        }

        // Abonnements : toujours traiter en premier pour activer la facture / l’abonnement et synchroniser Order/Payment,
        // même si un enregistrement Payment existe (sinon le flux commande court-circuiterait l’activation).
        $metaKind = data_get($paymentData, 'metadata.kind') ?? data_get($payload, 'metadata.kind');
        if ($metaKind === 'subscription_invoice') {
            $subscriptionHandled = $this->handleSubscriptionInvoiceWebhook($payload, $paymentData, $paymentId, $status);
            if ($subscriptionHandled) {
                return response()->json(['received' => true], 200);
            }
            \Log::warning('Moneroo webhook: subscription_invoice not handled', [
                'payment_id' => $paymentId,
                'payload' => $payload,
            ]);

            return response()->json(['received' => false, 'message' => 'subscription invoice not handled'], 200);
        }

        // Moneroo envoie généralement son ID (py_xxx), pas notre référence locale
        // Chercher par payment_id OU par moneroo_id dans payment_data
        $payment = Payment::where('payment_method', 'moneroo')
            ->where(function ($query) use ($paymentId) {
                $query->where('payment_id', $paymentId)
                    ->orWhereJsonContains('payment_data->moneroo_id', $paymentId)
                    ->orWhereJsonContains('payment_data->response->data->id', $paymentId)
                    ->orWhereJsonContains('payment_data->data->id', $paymentId);
            })
            ->with(['order.orderItems', 'order.user'])
            ->latest()
            ->first();

        if (! $payment) {
            \Log::warning('Moneroo webhook: Payment not found', ['payment_id' => $paymentId, 'searched_by_moneroo_id' => true]);

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
            // Selon la documentation Moneroo: success, pending, processing, completed, failed, cancelled, expired, rejected
            // Note: "success" et "completed" sont équivalents pour un paiement réussi
            $mapped = match ($status) {
                'success', 'completed' => 'completed',
                'failed', 'cancelled', 'expired', 'rejected' => 'failed',
                'pending', 'processing' => 'pending',
                default => 'pending',
            };

            // Vérifier aussi is_processed pour confirmer le traitement
            $isProcessed = $paymentData['is_processed'] ?? false;
            $processedAt = $paymentData['processed_at'] ?? null;

            // Mettre à jour le Payment avec toutes les informations du callback
            $paymentData = array_merge($payment->payment_data ?? [], [
                'callback' => $payload,
                'last_callback_at' => now()->toIso8601String(),
            ]);

            $payment->update([
                'status' => $mapped,
                'payment_data' => $paymentData,
                'processed_at' => in_array($status, ['completed', 'success'])
                    ? ($processedAt ? \Carbon\Carbon::parse($processedAt) : now())
                    : null,
            ]);

            // Traiter selon le statut final
            // Un paiement est réussi si status est "success" ou "completed" ET is_processed est true
            if (in_array($status, ['completed', 'success']) && $payment->order) {
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
                $failureReason = $this->contextualizeFailureReasonForStalePendingOrder($payment->order, (string) $status, $failureReason);

                $payment->update(['failure_reason' => $failureReason]);

                // Annuler la commande seulement si elle n'est pas déjà payée (éviter doublon)
                if (! in_array($payment->order->status, ['paid', 'completed'])) {
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

    private function handleSubscriptionInvoiceWebhook(array $payload, array $paymentData, ?string $paymentId, ?string $status): bool
    {
        $kind = data_get($paymentData, 'metadata.kind')
            ?? data_get($payload, 'metadata.kind');

        if ($kind !== 'subscription_invoice') {
            return false;
        }

        $invoiceId = data_get($paymentData, 'metadata.invoice_id')
            ?? data_get($payload, 'metadata.invoice_id');

        if (! $invoiceId) {
            return false;
        }

        $invoice = \App\Models\SubscriptionInvoice::query()->find($invoiceId);
        if (! $invoice) {
            return false;
        }

        $mappedStatus = match ($status) {
            'success', 'completed' => 'paid',
            'failed', 'cancelled', 'expired', 'rejected' => 'failed',
            default => 'pending',
        };

        $subscription = $invoice->subscription;
        $wasPendingPayment = $subscription && $subscription->status === 'pending_payment';
        $previousInvoiceStatus = $invoice->status;

        $invoice->update([
            'status' => $mappedStatus,
            'paid_at' => $mappedStatus === 'paid' ? now() : null,
            'metadata' => array_merge($invoice->metadata ?? [], [
                'moneroo_callback' => $payload,
                'last_moneroo_payment_id' => $paymentId,
                'last_moneroo_status' => $status,
            ]),
        ]);

        if ($mappedStatus === 'paid' && $subscription) {
            $previousPeriodEnd = $subscription->current_period_ends_at?->copy();
            $subscription->update(['status' => 'active']);
            $subscription = $subscription->fresh();
            $subscriptionService = app(SubscriptionService::class);
            $subscriptionService->syncSubscriptionPeriodAfterInvoicePaid($subscription, $wasPendingPayment, $previousPeriodEnd);
            $subscription = $subscription->fresh();
            $subscriptionService->grantLinkedContentAccess($subscription);
            $subscriptionService->expireOtherMemberBundleSubscriptions($subscription);

            if ($invoice->user) {
                $invoice->refresh();
                $subscriptionService->dispatchSubscriptionPaidLifecycleNotifications(
                    $invoice,
                    $subscription,
                    $wasPendingPayment,
                );
            }
        } elseif ($mappedStatus === 'failed' && $wasPendingPayment && $subscription) {
            $subscription->update([
                'status' => 'expired',
                'ended_at' => now(),
                'auto_renew' => false,
            ]);
        }

        $invoice->refresh();
        $this->syncSubscriptionLinkedOrderAfterInvoiceWebhook($invoice, $payload, $paymentData, $paymentId, $status);

        if ($mappedStatus === 'paid' && $previousInvoiceStatus !== 'paid' && $invoice->user) {
            app(SubscriptionService::class)->notifySubscriptionInvoicePaidUser($invoice, 'moneroo_webhook_subscription_invoice_paid');
        } elseif ($mappedStatus === 'failed' && $previousInvoiceStatus !== 'failed') {
            SubscriptionNotificationDispatcher::notifyUser(
                $invoice->user,
                new SubscriptionInvoiceFailed($invoice),
                'moneroo_webhook_subscription_invoice_failed',
                ['invoice_id' => $invoice->id],
            );
            SubscriptionNotificationDispatcher::notifyAdmins(
                new AdminSubscriptionInvoiceFailed($invoice),
                'moneroo_webhook_subscription_invoice_failed_admin',
                ['invoice_id' => $invoice->id],
            );
        }

        return true;
    }

    /**
     * Met à jour Payment + Order liés à une facture d’abonnement (créés à l’init Moneroo), comme pour le panier.
     */
    private function syncSubscriptionLinkedOrderAfterInvoiceWebhook(
        \App\Models\SubscriptionInvoice $invoice,
        array $payload,
        array $paymentData,
        ?string $paymentId,
        ?string $status,
    ): void {
        // Toujours préférer l’order_id renvoyé par Moneroo (métadonnées du paiement), pas la dernière valeur
        // enregistrée sur la facture — sinon un paiement sur un ancien onglet mettrait à jour la mauvaise commande.
        $orderIdFromCallback = data_get($paymentData, 'metadata.order_id')
            ?? data_get($payload, 'metadata.order_id');
        $orderId = $orderIdFromCallback ?: data_get($invoice->metadata, 'order_id');
        if (! $orderId) {
            return;
        }

        $order = Order::query()->find((int) $orderId);
        if (! $order || (int) $order->user_id !== (int) $invoice->user_id) {
            \Log::warning('Moneroo: subscription order sync skipped', [
                'invoice_id' => $invoice->id,
                'order_id' => $orderId,
                'order_user' => $order?->user_id,
                'invoice_user' => $invoice->user_id,
            ]);

            return;
        }

        if ((int) data_get($order->billing_info, 'subscription_invoice_id') !== (int) $invoice->id) {
            \Log::warning('Moneroo: subscription order sync skipped — billing_info invoice mismatch', [
                'invoice_id' => $invoice->id,
                'order_id' => $order->id,
            ]);

            return;
        }

        $monerooId = $paymentData['id'] ?? $paymentId;
        $paymentRef = data_get($paymentData, 'metadata.payment_ref')
            ?? data_get($payload, 'metadata.payment_ref');

        $payment = null;
        if ($monerooId || $paymentRef) {
            $payment = Payment::query()
                ->where('order_id', $order->id)
                ->where('payment_method', 'moneroo')
                ->where(function ($q) use ($monerooId, $paymentRef) {
                    if ($monerooId) {
                        $q->where('payment_data->moneroo_id', $monerooId)
                            ->orWhere('payment_id', $monerooId);
                    }
                    if ($paymentRef) {
                        $q->orWhere('payment_id', $paymentRef);
                    }
                })
                ->latest('id')
                ->first();
        }

        if (! $payment) {
            $payment = Payment::query()
                ->where('order_id', $order->id)
                ->where('payment_method', 'moneroo')
                ->latest('id')
                ->first();
        }

        if (! $payment) {
            return;
        }

        $rawStatus = is_string($status) ? $status : '';
        $mappedPayment = match ($rawStatus) {
            'success', 'completed' => 'completed',
            'failed', 'cancelled', 'expired', 'rejected' => 'failed',
            'pending', 'processing' => 'pending',
            default => 'pending',
        };

        $mergedData = array_merge($payment->payment_data ?? [], [
            'callback' => $payload,
            'moneroo_id' => $paymentData['id'] ?? $paymentId ?? data_get($payment->payment_data, 'moneroo_id'),
            'last_callback_at' => now()->toIso8601String(),
        ]);

        $processedAt = $paymentData['processed_at'] ?? null;
        $failureReason = null;
        if (in_array($rawStatus, ['failed', 'cancelled', 'expired', 'rejected'], true)) {
            $statusForReason = $rawStatus !== '' ? $rawStatus : 'failed';
            $failureReason = $this->extractFailureReason(
                $paymentData,
                $payload,
                $statusForReason,
            );
            $failureReason = $this->contextualizeFailureReasonForStalePendingOrder($order, $statusForReason, $failureReason);
        }

        $payment->update([
            'status' => $mappedPayment,
            'payment_data' => $mergedData,
            'failure_reason' => $failureReason ?? $payment->failure_reason,
            'processed_at' => in_array($rawStatus, ['completed', 'success'], true)
                ? ($processedAt ? \Carbon\Carbon::parse($processedAt) : now())
                : null,
        ]);

        if (in_array($rawStatus, ['completed', 'success'], true)) {
            $feeAmount = $paymentData['fee'] ?? ($paymentData['fees']['amount'] ?? null);
            $feeCurrency = $paymentData['fee_currency'] ?? ($paymentData['fees']['currency'] ?? null);
            $updates = [
                'payment_reference' => $order->payment_reference ?: ($paymentData['id'] ?? $paymentId),
            ];
            if ($feeAmount !== null) {
                $updates['provider_fee'] = (float) $feeAmount;
                $updates['net_total'] = $order->payment_amount !== null
                    ? (float) $order->payment_amount - (float) $feeAmount
                    : null;
                if ($feeCurrency !== null) {
                    $updates['provider_fee_currency'] = (string) $feeCurrency;
                }
            }
            $order->update(array_merge($updates, [
                'status' => 'paid',
                'paid_at' => $order->paid_at ?: now(),
            ]));
            $this->finalizeOrderAfterPayment($order->fresh());
            if ($invoice->user) {
                app(\App\Services\SubscriptionCheckoutOrderService::class)
                    ->cancelOtherPendingSubscriptionCheckoutsForInvoice($invoice, $invoice->user, $order->id);
            }
        } elseif (in_array($rawStatus, ['failed', 'cancelled', 'expired', 'rejected'], true)) {
            if (! in_array($order->status, ['paid', 'completed'], true)) {
                $order->update(['status' => 'cancelled']);
            }
            $this->sendPaymentFailureNotifications($order->fresh(), $failureReason);
        }
    }

    /**
     * Lorsque la commande est restée non payée au-delà du délai local d'annulation automatique
     * et que Moneroo clôt en annulation ou expiration, on préfère expliquer le dépassement de délai
     * plutôt que le libellé générique du PSP (ex. cron : sync API avant auto-cancel local).
     */
    private function contextualizeFailureReasonForStalePendingOrder(?Order $order, string $monerooStatus, string $extractedReason): string
    {
        if (! $order || in_array($order->status, ['paid', 'completed'], true)) {
            return $extractedReason;
        }

        $timeoutMinutes = $this->pendingOrderAutoCancelDelayMinutes();
        if ($order->created_at->gte(now()->subMinutes($timeoutMinutes))) {
            return $extractedReason;
        }

        $terminal = strtolower($monerooStatus);

        if (! in_array($terminal, ['cancelled', 'expired'], true)) {
            return $extractedReason;
        }

        return self::AUTO_CANCELLATION_TIMEOUT_REASON;
    }

    /**
     * Extraire la raison d'échec du paiement depuis les données Moneroo
     *
     * Cette méthode cherche la raison d'échec dans plusieurs champs possibles
     * pour capturer tous les cas d'erreur (solde insuffisant, transaction rejetée, etc.)
     *
     * @param  array  $paymentData  Les données du paiement
     * @param  array  $payload  Le payload complet du webhook
     * @param  string  $status  Le statut du paiement
     * @return string La raison d'échec formatée
     */
    private function extractFailureReason(array $paymentData, array $payload, string $status): string
    {
        // Chercher la raison d'échec dans plusieurs champs possibles,
        // en priorisant les champs explicitement liés à une erreur.
        $candidates = [
            $paymentData['failure_reason'] ?? null,
            $paymentData['error_message'] ?? null,
            $paymentData['error'] ?? null,
            $paymentData['reason'] ?? null,
            data_get($paymentData, 'error.message'),
            data_get($paymentData, 'error.description'),
            data_get($paymentData, 'errors.0.message'),
            data_get($paymentData, 'errors.0.description'),
            $payload['failure_reason'] ?? null,
            $payload['error_message'] ?? null,
            $payload['error'] ?? null,
            $payload['reason'] ?? null,
            data_get($payload, 'error.message'),
            data_get($payload, 'error.description'),
            data_get($payload, 'errors.0.message'),
            data_get($payload, 'errors.0.description'),
            // Garder message en dernier recours (peut contenir un message de succès technique).
            $paymentData['message'] ?? null,
            $payload['message'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate)) {
                continue;
            }

            $reason = trim($candidate);
            if ($reason === '' || $this->isNonFailureMessage($reason)) {
                continue;
            }
            if ($status === 'cancelled' && $this->isGenericCancelledMessage($reason)) {
                continue;
            }

            return $reason;
        }

        // Sinon, mapper le statut vers un message compréhensible
        return match ($status) {
            'failed' => 'Le paiement a échoué. Veuillez vérifier vos informations de paiement et réessayer.',
            'cancelled' => self::AUTO_CANCELLATION_TIMEOUT_REASON,
            'expired' => self::AUTO_CANCELLATION_TIMEOUT_REASON,
            'rejected' => 'Le paiement a été rejeté. Cela peut être dû à un solde insuffisant ou à une restriction sur votre compte.',
            default => 'Le paiement n\'a pas pu être complété.',
        };
    }

    /**
     * Écarter les messages techniques ou de succès non pertinents pour un échec.
     */
    private function isNonFailureMessage(string $message): bool
    {
        $normalized = strtolower(trim($message));

        return str_contains($normalized, 'fetched successfully')
            || str_contains($normalized, 'fetched succesfully')
            || str_contains($normalized, 'transaction fetched')
            || str_contains($normalized, 'payement transaction fetched successfully')
            || str_contains($normalized, 'payment transaction fetched successfully');
    }

    /**
     * Détecte les messages d'annulation trop génériques qui masquent la vraie cause métier.
     */
    private function isGenericCancelledMessage(string $message): bool
    {
        $normalized = strtolower(trim($message));
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/[.!?]+$/u', '', $normalized) ?? $normalized;
        $normalized = trim((string) $normalized);

        if (str_starts_with($normalized, 'le ')) {
            $normalized = trim(substr($normalized, 3));
        }

        return in_array($normalized, [
            'paiement a été annulé',
            'paiement a ete annule',
            'paiement annulé',
            'paiement annule',
            'payment cancelled',
            'payment canceled',
        ], true);
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
        if (! $payment) {
            return response()->json(['success' => false, 'message' => 'Transaction introuvable'], 404);
        }

        $owner = $this->resolveMonerooCartOwner();
        if (! $owner || ! $payment->order || (int) $payment->order->user_id !== (int) $owner->id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
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
        $cartOwner = $this->resolveMonerooCartOwner();
        if (! $cartOwner) {
            return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }
        $userId = (int) $cartOwner->id;
        // Ne pas toucher aux commandes d’abonnement (SUB-*) : le flux panier ne doit pas annuler un checkout Membre en cours.
        $order = Order::where('user_id', $userId)
            ->where('status', 'pending')
            ->where('order_number', 'not like', 'SUB-%')
            ->latest()
            ->first();
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Aucune commande en attente'], 404);
        }
        // Optionnel: ne pas annuler des commandes trop anciennes (au-delà du délai d'annulation automatique).
        if ($order->created_at->lt(now()->subMinutes($this->pendingOrderAutoCancelDelayMinutes()))) {
            return response()->json(['success' => false, 'message' => 'Commande trop ancienne pour annulation automatique'], 422);
        }
        Payment::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'processing'])
            ->update([
                'status' => 'failed',
                'failure_reason' => self::CANCELLED_WITHOUT_AUTORETRY_REASON,
            ]);
        $order->update(['status' => 'cancelled']);

        return response()->json(['success' => true]);
    }

    /**
     * Finalisation post-paiement (commande panier ou abonnement) — exposée pour le marquage payé admin.
     */
    public function finalizeOrderAfterSuccessfulPayment(Order $order): void
    {
        $this->finalizeOrderAfterPayment($order);
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
            // "paid" peut exister sans finalisation complète (enrollments/panier),
            // on ne court-circuite donc que si la commande est "completed".
            if ($order->status === 'completed') {
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
                'items_data' => $orderItems->map(fn ($item) => [
                    'id' => $item->id,
                    'content_id' => $item->content_id,
                    'content_package_id' => $item->content_package_id,
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

            // Inscriptions : cours seuls → mails/notifs par contenu ; lignes pack → silencieux + un mail/notif par pack.
            $orderItems->loadMissing(['course', 'contentPackage']);
            $enrollmentsCreated = app(OrderEnrollmentService::class)->syncEnrollmentsFromOrderItems($order, $orderItems);

            \Log::info('Moneroo: Order items processed', [
                'order_id' => $order->id,
                'enrollments_created' => $enrollmentsCreated,
                'total_order_items' => $orderItems->count(),
            ]);

            // Vider le panier de l'utilisateur (DB + session par sécurité)
            $cartItemsBeforeDelete = CartItem::where('user_id', $order->user_id)->count()
                + CartPackage::where('user_id', $order->user_id)->count();
            CartPackage::where('user_id', $order->user_id)->delete();
            $cartItemsDeleted = CartItem::where('user_id', $order->user_id)->delete();
            Session::forget('cart');

            if ((int) Session::get(CartController::GUEST_PAY_USER_ID_KEY) === (int) $order->user_id) {
                CartController::clearGuestMonerooPayIntent();
            }

            // Retirer le code promo de la session après utilisation
            Session::forget('applied_promo_code');

            \Log::info('Moneroo: Cart and promo code cleared', [
                'user_id' => $order->user_id,
                'cart_items_before' => $cartItemsBeforeDelete,
                'cart_items_deleted' => $cartItemsDeleted,
                'session_cart_cleared' => true,
                'promo_code_cleared' => true,
            ]);

            $commission = null;
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
                        \Log::error('Error sending ambassador commission email: '.$e->getMessage());
                    }
                }
            }

            // Alimenter les wallets plateforme et ambassadeur à partir des revenus (idempotent)
            try {
                $revenueService = app(\App\Services\WalletRevenueService::class);
                $revenueService->creditPlatformFromOrder($order);
                if ($commission) {
                    $revenueService->creditAmbassadorFromCommission($order, $commission);
                }
            } catch (\Throwable $e) {
                \Log::error('Moneroo: erreur alimentation wallets depuis commande', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            \Log::info('Moneroo: Finalization completed successfully', [
                'order_id' => $order->id,
                'final_status' => $order->fresh()->status,
            ]);
        });

        // Envoyer les emails de paiement APRÈS le commit de la transaction pour éviter qu'une exception
        // (envoi SMTP, etc.) ne fasse rollback et empêche la commande d'être marquée payée.
        // Inclut : confirmation à l'utilisateur, facture, notification aux admins.
        $orderFinal = $order->fresh();
        if ($orderFinal && in_array($orderFinal->status, ['paid', 'completed'])) {
            try {
                $this->sendPaymentEmails($orderFinal);
            } catch (\Throwable $e) {
                \Log::error('Moneroo: Erreur envoi emails après finalisation (commande déjà payée)', [
                    'order_id' => $orderFinal->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    public function successfulRedirect(Request $request)
    {
        if (auth()->check()) {
            $this->maybeAutoCancelStaleOrdersForUser((int) auth()->id());
        }

        // Moneroo peut envoyer payment_id (notre référence) ou paymentId (ID Moneroo) dans les paramètres
        // Selon la documentation: https://docs.moneroo.io/payments/standard-integration
        // Moneroo redirige avec paymentId et paymentStatus dans les query parameters
        $paymentId = $request->query('payment_id')
                  ?? $request->query('paymentId')
                  ?? $request->input('payment_id')
                  ?? $request->input('paymentId');

        // Récupérer le paymentStatus depuis les paramètres de redirection Moneroo
        $paymentStatus = $request->query('paymentStatus')
                      ?? $request->input('paymentStatus');

        \Log::info('Moneroo: successfulRedirect appelé', [
            'payment_id' => $request->query('payment_id'),
            'paymentId' => $request->query('paymentId'),
            'paymentStatus' => $paymentStatus,
            'all_params' => $request->all(),
        ]);

        // Si paymentStatus indique un échec, rediriger vers la page failed
        if ($paymentStatus && in_array(strtolower($paymentStatus), ['failed', 'cancelled', 'expired', 'rejected'])) {
            \Log::warning('Moneroo: paymentStatus indicates failure, redirecting to failed page', [
                'payment_id' => $paymentId,
                'paymentStatus' => $paymentStatus,
            ]);

            if ($paymentId) {
                return redirect()->route('moneroo.failed', ['payment_id' => $paymentId, 'paymentStatus' => $paymentStatus]);
            }

            return redirect()->route('moneroo.failed');
        }

        if ($paymentId) {
            // Chercher le paiement par payment_id (notre référence) ou par l'ID Moneroo
            // Moneroo peut rediriger avec notre référence locale OU avec son propre ID
            $payment = Payment::where('payment_method', 'moneroo')
                ->where(function ($query) use ($paymentId) {
                    $query->where('payment_id', $paymentId) // Notre référence locale
                        ->orWhereJsonContains('payment_data->moneroo_id', $paymentId) // ID Moneroo stocké
                        ->orWhereJsonContains('payment_data->response->data->id', $paymentId) // Format alternatif
                        ->orWhereJsonContains('payment_data->data->id', $paymentId); // Format alternatif
                })
                ->with('order')
                ->first();

            // Si pas trouvé, essayer de chercher directement par l'ID Moneroo dans tous les paiements récents
            if (! $payment) {
                \Log::warning('Moneroo: Payment not found with initial search in successfulRedirect, trying alternative methods', [
                    'payment_id' => $paymentId,
                ]);

                // Chercher dans les paiements récents (dernières 24h) par l'ID Moneroo
                $payment = Payment::where('payment_method', 'moneroo')
                    ->where('created_at', '>=', now()->subHours(24))
                    ->where(function ($query) use ($paymentId) {
                        $query->whereJsonContains('payment_data->moneroo_id', $paymentId)
                            ->orWhereJsonContains('payment_data->response->data->id', $paymentId)
                            ->orWhereJsonContains('payment_data->data->id', $paymentId)
                            ->orWhere('payment_id', $paymentId);
                    })
                    ->with('order')
                    ->latest()
                    ->first();
            }

            if ($payment && $payment->order) {
                // SÉCURITÉ : Vérifier que la commande appartient à l'utilisateur connecté
                // pour éviter qu'un utilisateur puisse accéder à la commande d'un autre
                if (auth()->check() && $payment->order->user_id !== auth()->id()) {
                    \Log::warning('Moneroo: Attempted access to another user order', [
                        'payment_id' => $paymentId,
                        'order_id' => $payment->order->id,
                        'order_user_id' => $payment->order->user_id,
                        'current_user_id' => auth()->id(),
                        'ip' => $request->ip(),
                    ]);

                    return redirect()->route('moneroo.failed')->with('error',
                        'Accès non autorisé. Veuillez vérifier votre paiement.'
                    );
                }

                // VALIDATION RECOMMANDÉE : Vérifier le statut auprès de Moneroo
                // comme recommandé dans la documentation pour garantir la cohérence
                // Utiliser l'ID Moneroo (py_xxx) si disponible, sinon notre payment_id
                // IMPORTANT: Utiliser l'endpoint /verify selon la documentation Moneroo
                // https://docs.moneroo.io/payments/transaction-verification
                // Récupérer l'ID Moneroo depuis payment_data (stocké lors de la création)
                $monerooPaymentId = $payment->payment_data['moneroo_id']
                                 ?? $payment->payment_data['response']['data']['id']
                                 ?? $payment->payment_data['data']['id']
                                 ?? $paymentId; // Fallback: utiliser le paymentId de la redirection

                $statusResponse = Http::withHeaders($this->authHeaders())
                    ->get($this->baseUrl()."/payments/{$monerooPaymentId}/verify");

                if ($statusResponse->successful()) {
                    $responseData = $statusResponse->json();
                    // Format Moneroo: { "success": true, "message": "...", "data": { "id": "...", "status": "..." } }
                    $statusData = $responseData['data'] ?? $responseData;
                    $status = $statusData['status'] ?? null;
                    // Vérifier aussi is_processed et processed_at selon la documentation
                    $isProcessed = $statusData['is_processed'] ?? false;
                    $processedAt = $statusData['processed_at'] ?? null;

                    \Log::info('Moneroo: Status check on successful redirect', [
                        'payment_id' => $paymentId,
                        'moneroo_payment_id' => $monerooPaymentId,
                        'status' => $status,
                        'is_processed' => $isProcessed,
                        'processed_at' => $processedAt,
                        'local_payment_status' => $payment->status,
                        'order_status' => $payment->order->status,
                        'full_status_data' => $statusData,
                    ]);

                    // Si localement le paiement est déjà complété, forcer la mise à jour de la commande
                    if ($payment->status === 'completed' && ! in_array($payment->order->status, ['paid', 'completed'])) {
                        $payment->order->update([
                            'status' => 'paid',
                            'paid_at' => $payment->order->paid_at ?: now(),
                        ]);
                        $this->finalizeOrderAfterPayment($payment->order);
                    }

                    // Traiter tous les statuts possibles selon Moneroo
                    // Selon la documentation: status peut être "success", "pending", "failed"
                    // Un paiement réussi a status="success" et is_processed=true
                    // Vérifier aussi le paymentStatus de la redirection si disponible
                    $redirectStatus = strtolower($paymentStatus ?? '');
                    $isSuccessFromRedirect = in_array($redirectStatus, ['success', 'completed']);

                    if (in_array($status, ['success', 'completed']) || $isSuccessFromRedirect) {
                        $orderWasAlreadyPaid = in_array($payment->order->status, ['paid', 'completed']);

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
                        if (! in_array($payment->order->status, ['paid', 'completed'])) {
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
                        if (! $orderWasAlreadyPaid) {
                            $this->finalizeOrderAfterPayment($payment->order);
                        }

                        // Sécuriser le vidage du panier (contexte redirection ; invité paiement compte existant inclus)
                        try {
                            $ownerId = (int) ($payment->order->user_id ?? 0);
                            if ($ownerId > 0) {
                                $owner = User::query()->find($ownerId);
                                if ($owner) {
                                    $owner->cartItems()->delete();
                                    $owner->cartPackages()->delete();
                                }
                                if ((int) Session::get(CartController::GUEST_PAY_USER_ID_KEY) === $ownerId) {
                                    CartController::clearGuestMonerooPayIntent();
                                }
                            }
                            Session::forget('cart');
                        } catch (\Throwable $e) {
                        }

                        // TOUJOURS assurer l'envoi des emails en contexte redirection (même si commande déjà payée)
                        // Car le webhook peut ne pas avoir envoyé les emails ou avoir échoué
                        // Utiliser la même logique que Enrollment::sendEnrollmentNotifications
                        $orderFresh = $payment->order->fresh();
                        $this->sendPaymentEmails($orderFresh);

                        $order = $payment->order->fresh();

                        // PROTECTION CONTRE LES ACTUALISATIONS : Utiliser un flag pour éviter les traitements multiples
                        // Si la commande est déjà payée et finalisée, on peut afficher directement
                        if (in_array($order->status, ['paid', 'completed'])) {
                            \Log::info('Moneroo: Order already paid, displaying success page', [
                                'order_id' => $order->id,
                                'order_status' => $order->status,
                                'payment_id' => $paymentId,
                            ]);
                        }

                        return view('payments.moneroo.success', compact('order'));

                    } elseif (in_array($status, ['failed', 'cancelled', 'expired', 'rejected'])) {
                        // Échec : extraire la raison détaillée et rediriger vers la page d'échec
                        $failureReason = $this->extractFailureReason($statusData, $responseData, $status);
                        $failureReason = $this->contextualizeFailureReasonForStalePendingOrder($payment->order, (string) $status, $failureReason);

                        $payment->update([
                            'status' => 'failed',
                            'failure_reason' => $failureReason,
                            'payment_data' => array_merge($payment->payment_data ?? [], [
                                'redirect_check' => $statusData,
                            ]),
                        ]);

                        if (! in_array($payment->order->status, ['paid', 'completed'])) {
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

                    // Si le paiement local est déjà complété, afficher la page de succès
                    if ($payment->status === 'completed' && in_array($payment->order->status, ['paid', 'completed'])) {
                        $order = $payment->order->fresh();

                        return view('payments.moneroo.success', compact('order'));
                    }
                }
            } else {
                // Paiement trouvé mais pas de commande associée (cas rare)
                \Log::warning('Moneroo: Payment found but no order associated', [
                    'payment_id' => $paymentId,
                ]);
            }
        } else {
            // PaymentId fourni mais paiement non trouvé localement
            // Essayer de vérifier directement via l'API Moneroo
            \Log::warning('Moneroo: Payment not found locally in successfulRedirect, trying direct API verification', [
                'payment_id' => $paymentId,
                'paymentStatus' => $paymentStatus,
            ]);

            // Vérifier le statut directement via l'API même si le paiement n'est pas trouvé localement
            if ($paymentId) {
                try {
                    $statusResponse = Http::withHeaders($this->authHeaders())
                        ->get($this->baseUrl()."/payments/{$paymentId}/verify");

                    if ($statusResponse->successful()) {
                        $responseData = $statusResponse->json();
                        $statusData = $responseData['data'] ?? $responseData;
                        $status = $statusData['status'] ?? null;
                        $isProcessed = $statusData['is_processed'] ?? false;

                        \Log::info('Moneroo: Direct API verification result', [
                            'payment_id' => $paymentId,
                            'status' => $status,
                            'is_processed' => $isProcessed,
                            'paymentStatus' => $paymentStatus,
                        ]);

                        // Si le paiement est réussi selon l'API, chercher la commande par order_id dans metadata
                        if (($status === 'success' && $isProcessed) || ($paymentStatus && in_array(strtolower($paymentStatus), ['success', 'completed']))) {
                            // Essayer de trouver la commande via les metadata
                            $orderId = $statusData['metadata']['order_id'] ?? null;

                            if ($orderId) {
                                $order = Order::find($orderId);
                                if ($order && (! $order->user_id || ! auth()->check() || $order->user_id === auth()->id())) {
                                    \Log::info('Moneroo: Order found via metadata, finalizing payment', [
                                        'order_id' => $order->id,
                                        'payment_id' => $paymentId,
                                    ]);

                                    // Créer ou mettre à jour le paiement
                                    $payment = Payment::firstOrCreate(
                                        [
                                            'payment_method' => 'moneroo',
                                            'payment_id' => $paymentId,
                                        ],
                                        [
                                            'order_id' => $order->id,
                                            'amount' => $statusData['amount'] ?? $order->total,
                                            'currency' => $statusData['currency']['code'] ?? $order->currency,
                                            'status' => 'completed',
                                            'processed_at' => $statusData['processed_at'] ? \Carbon\Carbon::parse($statusData['processed_at']) : now(),
                                            'payment_data' => [
                                                'moneroo_id' => $paymentId,
                                                'direct_verification' => true,
                                                'status_data' => $statusData,
                                            ],
                                        ]
                                    );

                                    if ($payment->status !== 'completed') {
                                        $payment->update([
                                            'status' => 'completed',
                                            'processed_at' => $statusData['processed_at'] ? \Carbon\Carbon::parse($statusData['processed_at']) : now(),
                                        ]);
                                    }

                                    if (! in_array($order->status, ['paid', 'completed'])) {
                                        $order->update([
                                            'status' => 'paid',
                                            'paid_at' => $order->paid_at ?: now(),
                                        ]);
                                        $this->finalizeOrderAfterPayment($order);
                                    }

                                    return view('payments.moneroo.success', compact('order'));
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Moneroo: Exception while verifying unknown payment via API in successfulRedirect', [
                        'payment_id' => $paymentId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // CRITIQUE: Si on arrive ici, c'est qu'aucun payment_id valide n'est fourni ou paiement non trouvé
        // NE JAMAIS afficher la page de succès sans commande vérifiée
        \Log::warning('Moneroo: successfulRedirect called without valid payment_id or payment not found', [
            'url' => $request->fullUrl(),
            'query_params' => $request->query(),
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Rediriger vers la page d'échec pour que l'utilisateur puisse réessayer
        return redirect()->route('moneroo.failed')->with('error',
            'Impossible de retrouver les détails de votre paiement. Veuillez réessayer.'
        );
    }

    public function failedRedirect(Request $request)
    {
        if (auth()->check()) {
            $this->maybeAutoCancelStaleOrdersForUser((int) auth()->id());
        }

        // Si Moneroo redirige avec un payment_id, synchroniser l'état local
        // Selon la documentation: https://docs.moneroo.io/payments/standard-integration
        // Moneroo redirige avec paymentId et paymentStatus dans les query parameters
        $paymentId = $request->query('payment_id')
                  ?? $request->query('paymentId')
                  ?? $request->input('payment_id')
                  ?? $request->input('paymentId');

        // Récupérer le paymentStatus depuis les paramètres de redirection Moneroo
        $paymentStatus = $request->query('paymentStatus')
                      ?? $request->input('paymentStatus');

        \Log::info('Moneroo: failedRedirect appelé', [
            'payment_id' => $paymentId,
            'paymentStatus' => $paymentStatus,
            'all_params' => $request->all(),
        ]);

        // Si paymentStatus indique un succès, rediriger vers la page success
        if ($paymentStatus && in_array(strtolower($paymentStatus), ['success', 'completed'])) {
            \Log::warning('Moneroo: paymentStatus indicates success but redirected to failed page', [
                'payment_id' => $paymentId,
                'paymentStatus' => $paymentStatus,
            ]);

            if ($paymentId) {
                return redirect()->route('moneroo.success', ['payment_id' => $paymentId, 'paymentStatus' => $paymentStatus]);
            }

            return redirect()->route('moneroo.success');
        }

        if ($paymentId) {
            // Chercher le paiement par payment_id (notre référence) ou par le payment_id de Moneroo
            $payment = Payment::where('payment_method', 'moneroo')
                ->where(function ($query) use ($paymentId) {
                    $query->where('payment_id', $paymentId)
                        ->orWhereJsonContains('payment_data->response->data->id', $paymentId)
                        ->orWhereJsonContains('payment_data->data->id', $paymentId);
                })
                ->with('order')
                ->first();

            if ($payment && $payment->order) {
                // IMPORTANT: Vérifier d'abord le statut réel auprès de Moneroo
                // pour obtenir la raison d'échec exacte (solde insuffisant, carte rejetée, etc.)
                // IMPORTANT: Utiliser l'endpoint /verify selon la documentation Moneroo
                // https://docs.moneroo.io/payments/transaction-verification
                // Récupérer l'ID Moneroo depuis payment_data (stocké lors de la création)
                $monerooPaymentId = $payment->payment_data['moneroo_id']
                                 ?? $payment->payment_data['response']['data']['id']
                                 ?? $payment->payment_data['data']['id']
                                 ?? $paymentId; // Fallback: utiliser le paymentId de la redirection

                try {
                    $statusResponse = Http::withHeaders($this->authHeaders())
                        ->get($this->baseUrl()."/payments/{$monerooPaymentId}/verify");

                    if ($statusResponse->successful()) {
                        $responseData = $statusResponse->json();
                        $statusData = $responseData['data'] ?? $responseData;
                        $status = $statusData['status'] ?? 'failed';
                        $isProcessed = $statusData['is_processed'] ?? false;
                        $processedAt = $statusData['processed_at'] ?? null;

                        // CRITIQUE: Si le statut est "success" et is_processed=true,
                        // le paiement a réussi même si on est sur la page failed
                        // Cela peut arriver si Moneroo redirige vers failed par erreur
                        // Vérifier aussi le paymentStatus de la redirection
                        $redirectStatus = strtolower($paymentStatus ?? '');
                        $isSuccessFromRedirect = in_array($redirectStatus, ['success', 'completed']);
                        $isSuccessFromApi = ($status === 'success' || ($status === 'completed' && $isProcessed));

                        if ($isSuccessFromApi || ($isSuccessFromRedirect && $isProcessed)) {
                            \Log::warning('Moneroo: Payment actually succeeded but redirected to failed page', [
                                'payment_id' => $paymentId,
                                'moneroo_payment_id' => $monerooPaymentId,
                                'status' => $status,
                                'paymentStatus' => $paymentStatus,
                                'is_processed' => $isProcessed,
                                'processed_at' => $processedAt,
                                'is_success_from_api' => $isSuccessFromApi,
                                'is_success_from_redirect' => $isSuccessFromRedirect,
                            ]);

                            // Traiter comme un succès
                            if ($payment->status !== 'completed') {
                                $payment->update([
                                    'status' => 'completed',
                                    'processed_at' => $processedAt ? \Carbon\Carbon::parse($processedAt) : now(),
                                    'payment_data' => array_merge($payment->payment_data ?? [], [
                                        'redirect_check' => $statusData,
                                        'corrected_from_failed' => true,
                                        'paymentStatus_from_redirect' => $paymentStatus,
                                    ]),
                                ]);
                            }

                            if (! in_array($payment->order->status, ['paid', 'completed'])) {
                                $payment->order->update([
                                    'status' => 'paid',
                                    'paid_at' => $payment->order->paid_at ?: now(),
                                ]);
                                $this->finalizeOrderAfterPayment($payment->order);
                            }

                            // Rediriger vers la page de succès
                            return redirect()->route('moneroo.success', ['payment_id' => $paymentId, 'paymentStatus' => 'success']);
                        }

                        // Extraire la raison d'échec détaillée depuis l'API
                        $failureReason = $this->extractFailureReason($statusData, $responseData, $status);
                        $failureReason = $this->contextualizeFailureReasonForStalePendingOrder($payment->order, (string) $status, $failureReason);

                        \Log::info('Moneroo: Status check on failed redirect', [
                            'payment_id' => $paymentId,
                            'moneroo_payment_id' => $monerooPaymentId,
                            'status' => $status,
                            'is_processed' => $isProcessed,
                            'processed_at' => $processedAt,
                            'failure_reason' => $failureReason,
                            'full_status_data' => $statusData,
                        ]);
                    } else {
                        // Si la vérification échoue, utiliser une raison générique
                        $failureReason = 'Le paiement n\'a pas pu être complété';
                        \Log::warning('Moneroo: Failed to verify status on failed redirect', [
                            'payment_id' => $paymentId,
                            'response_status' => $statusResponse->status(),
                        ]);
                    }
                } catch (\Exception $e) {
                    $failureReason = 'Le paiement n\'a pas pu être complété';
                    \Log::error('Moneroo: Exception while verifying status on failed redirect', [
                        'payment_id' => $paymentId,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Marquer le paiement comme échoué si encore en attente
                if ($payment->status === 'pending') {
                    $payment->update([
                        'status' => 'failed',
                        'failure_reason' => $failureReason,
                    ]);
                }

                // Annuler la commande si elle n'est pas déjà payée/terminée
                if (! in_array($payment->order->status, ['paid', 'completed'])) {
                    $payment->order->update(['status' => 'cancelled']);
                }

                // CRITIQUE: Envoyer email ET notification d'échec
                // Même si le webhook sera appelé plus tard, on envoie maintenant pour informer l'utilisateur immédiatement
                $this->sendPaymentFailureNotifications($payment->order, $failureReason);

                \Log::info('Moneroo: Order/payment marked cancelled/failed on failed redirect', [
                    'payment_id' => $paymentId,
                    'payment_db_id' => $payment->id,
                    'order_id' => $payment->order->id,
                    'failure_reason' => $failureReason,
                ]);
            } else {
                // Paiement non trouvé localement mais paymentId fourni
                // Essayer de vérifier directement via l'API Moneroo
                \Log::warning('Moneroo: Payment not found locally, trying direct API verification', [
                    'payment_id' => $paymentId,
                    'paymentStatus' => $paymentStatus,
                ]);

                // Si paymentStatus indique un succès, vérifier via l'API
                if ($paymentStatus && in_array(strtolower($paymentStatus), ['success', 'completed'])) {
                    try {
                        $statusResponse = Http::withHeaders($this->authHeaders())
                            ->get($this->baseUrl()."/payments/{$paymentId}/verify");

                        if ($statusResponse->successful()) {
                            $responseData = $statusResponse->json();
                            $statusData = $responseData['data'] ?? $responseData;
                            $status = $statusData['status'] ?? null;
                            $isProcessed = $statusData['is_processed'] ?? false;

                            if ($status === 'success' && $isProcessed) {
                                \Log::warning('Moneroo: Payment verified as success via API but not found locally', [
                                    'payment_id' => $paymentId,
                                    'status' => $status,
                                    'is_processed' => $isProcessed,
                                ]);

                                // Rediriger vers success même si le paiement n'est pas trouvé localement
                                return redirect()->route('moneroo.success', ['payment_id' => $paymentId, 'paymentStatus' => 'success']);
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Moneroo: Exception while verifying unknown payment via API', [
                            'payment_id' => $paymentId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                \Log::warning('Moneroo: Failed redirect with unknown payment_id', [
                    'payment_id' => $paymentId,
                    'paymentStatus' => $paymentStatus,
                ]);
            }
        } else {
            // Aucun payment_id fourni : logger et afficher message générique
            \Log::warning('Moneroo: failedRedirect called without payment_id', [
                'url' => $request->fullUrl(),
                'query_params' => $request->query(),
                'user_id' => auth()->id(),
            ]);
        }

        // La page failed est moins critique, on peut l'afficher même sans payment_id
        // car elle informe juste l'utilisateur d'un échec
        return view('payments.moneroo.failed');
    }

    /**
     * Endpoint pour signaler un échec de paiement détecté côté client
     *
     * Utilisé quand Moneroo affiche un message d'erreur (ex: solde insuffisant)
     * avant même que l'utilisateur ne soit redirigé
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reportClientSideFailure(Request $request)
    {
        try {
            $paymentId = $request->input('payment_id');
            $failureMessage = $request->input('failure_message');
            $failureType = $request->input('failure_type', 'unknown');

            if (! $paymentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'payment_id requis',
                ], 400);
            }

            \Log::info('Moneroo: Client-side failure reported', [
                'payment_id' => $paymentId,
                'failure_message' => $failureMessage,
                'failure_type' => $failureType,
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ]);

            // Chercher le paiement
            $payment = Payment::where('payment_method', 'moneroo')
                ->where('payment_id', $paymentId)
                ->with('order')
                ->first();

            if (! $payment || ! $payment->order) {
                \Log::warning('Moneroo: Payment not found for client-side failure', [
                    'payment_id' => $paymentId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Paiement introuvable',
                ], 404);
            }

            $actor = $this->resolveMonerooCartOwner();
            if (! $actor || (int) $payment->order->user_id !== (int) $actor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                ], 403);
            }

            // Mapper le type d'échec vers une raison compréhensible
            $failureReason = $this->mapClientFailureToReason($failureType, $failureMessage);

            // Marquer comme échoué si encore en attente
            if ($payment->status === 'pending') {
                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => $failureReason,
                    'payment_data' => array_merge($payment->payment_data ?? [], [
                        'client_side_failure' => [
                            'message' => $failureMessage,
                            'type' => $failureType,
                            'reported_at' => now()->toIso8601String(),
                        ],
                    ]),
                ]);
            }

            // Annuler la commande si pas déjà payée
            if (! in_array($payment->order->status, ['paid', 'completed'])) {
                $payment->order->update(['status' => 'cancelled']);
            }

            // Envoyer les notifications immédiatement
            $this->sendPaymentFailureNotifications($payment->order, $failureReason);

            \Log::info('Moneroo: Client-side failure processed and notifications sent', [
                'payment_id' => $paymentId,
                'order_id' => $payment->order->id,
                'failure_reason' => $failureReason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Échec signalé et notifications envoyées',
            ]);

        } catch (\Exception $e) {
            \Log::error('Moneroo: Error processing client-side failure', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement',
            ], 500);
        }
    }

    /**
     * Mapper le type d'échec client vers une raison compréhensible
     */
    private function mapClientFailureToReason(string $type, ?string $message): string
    {
        // Si un message spécifique est fourni, l'utiliser
        if ($message && strlen($message) > 10) {
            return $message;
        }

        // Sinon, mapper selon le type
        return match ($type) {
            'insufficient_funds' => 'Solde insuffisant. Veuillez recharger votre compte et réessayer.',
            'invalid_card' => 'Carte invalide ou expirée. Veuillez vérifier vos informations.',
            'transaction_declined' => 'Transaction refusée par votre banque. Veuillez contacter votre banque.',
            'network_error' => 'Erreur de connexion. Veuillez vérifier votre connexion internet.',
            'timeout' => 'Délai d\'attente dépassé. Veuillez réessayer.',
            'user_cancelled' => 'Paiement annulé par l\'utilisateur.',
            default => 'Le paiement n\'a pas pu être complété. Veuillez réessayer.',
        };
    }

    /**
     * Envoyer les emails de paiement (même logique que Enrollment::sendEnrollmentNotifications)
     * Cette méthode envoie directement les emails de manière synchrone
     */
    private function sendPaymentEmails(Order $order): void
    {
        try {
            // Charger les relations nécessaires
            if (! $order->relationLoaded('user')) {
                $order->load('user');
            }
            if (! $order->relationLoaded('orderItems')) {
                $order->load(Order::eagerLoadOrderItemsWithPackages());
            }
            if (! $order->relationLoaded('coupon')) {
                $order->load('coupon');
            }
            if (! $order->relationLoaded('affiliate')) {
                $order->load('affiliate');
            }
            if (! $order->relationLoaded('payments')) {
                $order->load('payments');
            }

            $user = $order->user;

            if (! $user || ! $user->email) {
                \Log::warning("Impossible d'envoyer les emails de paiement: utilisateur ou email manquant", [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                ]);

                return;
            }

            // Déduplication fiable (évite double envoi webhook + redirection):
            // si le mail PaymentReceivedMail a déjà été envoyé et enregistré pour cette commande, on ne le renvoie pas.
            $paymentAlreadySent = SentEmail::query()
                ->where('recipient_email', $user->email)
                ->where('metadata->mail_class', \App\Mail\PaymentReceivedMail::class)
                ->where('metadata->order_id', $order->id)
                ->where('status', 'sent')
                ->exists();

            // Envoyer l'email et WhatsApp en parallèle
            try {
                if (! $paymentAlreadySent) {
                    $mailable = new \App\Mail\PaymentReceivedMail($order);
                    $communicationService = app(\App\Services\CommunicationService::class);
                    $communicationService->sendEmailAndWhatsApp($user, $mailable);
                    \Log::info("Email et WhatsApp PaymentReceivedMail envoyés pour la commande {$order->order_number}", [
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                    ]);
                } else {
                    \Log::info("PaymentReceivedMail déjà envoyé (déduplication) pour la commande {$order->order_number}", [
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                    ]);
                }
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

            // Notifier les admins / super_user (email + notification in-app)
            try {
                app(\App\Services\AdminPaymentNotifier::class)->notify($order);
            } catch (\Throwable $e) {
                \Log::error('Erreur lors de la notification admin (paiement confirmé)', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Envoyer la facture par email et WhatsApp
            try {
                $invoiceAlreadySent = SentEmail::query()
                    ->where('recipient_email', $user->email)
                    ->where('metadata->mail_class', \App\Mail\InvoiceMail::class)
                    ->where('metadata->order_id', $order->id)
                    ->where('status', 'sent')
                    ->exists();

                if (! $invoiceAlreadySent) {
                    $mailable = new InvoiceMail($order);
                    $communicationService = app(\App\Services\CommunicationService::class);
                    $communicationService->sendEmailAndWhatsApp($user, $mailable);
                    \Log::info("Email et WhatsApp InvoiceMail envoyés pour la commande {$order->order_number}", [
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                    ]);
                } else {
                    \Log::info("InvoiceMail déjà envoyé (déduplication) pour la commande {$order->order_number}", [
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                    ]);
                }
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
     * @param  Order  $order  La commande concernée
     * @param  string|null  $failureReason  La raison de l'échec
     */
    private function sendPaymentFailureNotifications(Order $order, ?string $failureReason = null): void
    {
        try {
            // Charger les relations nécessaires si pas déjà chargées
            if (! $order->relationLoaded('user')) {
                $order->load('user');
            }
            if (! $order->relationLoaded('orderItems')) {
                $order->load(Order::eagerLoadOrderItemsWithPackages());
            }
            if (! $order->relationLoaded('payments')) {
                $order->load('payments');
            }

            $user = $order->user;

            if (! $user || ! $user->email) {
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
        $this->sendPendingPaymentReminders($userId);
        $this->eachStalePendingOrderQuery($userId)->chunkById(50, function ($orders): void {
            foreach ($orders as $order) {
                $this->cancelSingleStalePendingOrder($order);
            }
        });
    }

    /**
     * Annule les commandes pending expirées pour tous les utilisateurs (cron / file).
     */
    public function autoCancelAllStalePendingOrders(): void
    {
        $this->sendPendingPaymentReminders(null);
        $this->eachStalePendingOrderQuery(null)->chunkById(50, function ($orders): void {
            foreach ($orders as $order) {
                $this->cancelSingleStalePendingOrder($order);
            }
        });
    }

    /**
     * Synchronise les paiements Moneroo en attente et annule les commandes pending trop anciennes.
     * Appelé depuis le planificateur (file d'attente), plus depuis le middleware web.
     */
    public function runScheduledPaymentMaintenance(): void
    {
        $this->syncAllPendingPayments();
        $this->autoCancelAllStalePendingOrders();
    }

    private function eachStalePendingOrderQuery(?int $userId)
    {
        $timeoutMinutes = $this->pendingOrderAutoCancelDelayMinutes();
        $threshold = now()->subMinutes($timeoutMinutes);
        $query = Order::query()
            ->where('status', 'pending')
            ->where('created_at', '<', $threshold)
            ->with(array_merge(['user', 'payments'], Order::eagerLoadOrderItemsWithPackages()));
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query;
    }

    private function cancelSingleStalePendingOrder(Order $order): void
    {
        $timeoutReason = self::AUTO_CANCELLATION_TIMEOUT_REASON;

        $order->update(['status' => 'cancelled']);
        Payment::where('order_id', $order->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'failed',
                'failure_reason' => $timeoutReason,
            ]);

        $this->sendPaymentFailureNotifications($order, $timeoutReason);
    }

    private function pendingPaymentReminderDelayMinutes(): int
    {
        return max(1, (int) env('ORDER_PENDING_REMINDER_DELAY_MIN', self::DEFAULT_PENDING_PAYMENT_REMINDER_DELAY_MINUTES));
    }

    private function pendingOrderAutoCancelDelayMinutes(): int
    {
        return max(1, (int) env('ORDER_PENDING_AUTO_CANCEL_DELAY_MIN', self::DEFAULT_PENDING_ORDER_AUTO_CANCEL_DELAY_MINUTES));
    }

    private function sendPendingPaymentReminders(?int $userId): void
    {
        $reminderDelay = $this->pendingPaymentReminderDelayMinutes();
        $cancelDelay = $this->pendingOrderAutoCancelDelayMinutes();

        if ($reminderDelay >= $cancelDelay) {
            return;
        }

        $reminderFrom = now()->subMinutes($cancelDelay);
        $reminderUntil = now()->subMinutes($reminderDelay);

        $query = Order::query()
            ->where('status', 'pending')
            ->whereBetween('created_at', [$reminderFrom, $reminderUntil])
            ->where(function ($q) {
                $q->whereNull('billing_info')
                    ->orWhereRaw("JSON_EXTRACT(billing_info, '$.payment_reminder_sent_at') IS NULL");
            })
            ->with(['user', 'payments']);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $query->chunkById(50, function ($orders): void {
            foreach ($orders as $order) {
                $this->sendSinglePendingPaymentReminder($order);
            }
        });
    }

    private function sendSinglePendingPaymentReminder(Order $order): void
    {
        if ($order->status !== 'pending') {
            return;
        }

        $pendingMonerooPayment = $order->payments
            ->where('payment_method', 'moneroo')
            ->whereIn('status', ['pending', 'processing'])
            ->isNotEmpty();

        if (! $pendingMonerooPayment || ! $order->user || ! $order->user->email) {
            return;
        }

        try {
            Mail::to($order->user->email)->send(new PendingPaymentReminderMail($order));

            $billingInfo = $order->billing_info ?? [];
            $billingInfo['payment_reminder_sent_at'] = now()->toIso8601String();
            $order->update(['billing_info' => $billingInfo]);
        } catch (\Throwable $e) {
            \Log::error('Moneroo: impossible d\'envoyer la relance de paiement', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'error' => $e->getMessage(),
            ]);
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

            if (! $order->ambassador || ! $order->ambassador->is_active) {
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
