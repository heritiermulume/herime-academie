<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletPayout;
use App\Models\Ambassador;
use App\Services\MonerooPayoutService;
use App\Services\WalletAutoReleaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WalletController extends Controller
{
    protected $monerooPayoutService;
    protected $autoReleaseService;

    public function __construct(
        MonerooPayoutService $monerooPayoutService,
        WalletAutoReleaseService $autoReleaseService
    ) {
        $this->middleware('auth');
        $this->monerooPayoutService = $monerooPayoutService;
        $this->autoReleaseService = $autoReleaseService;
    }

    /**
     * Afficher le dashboard du wallet
     */
    public function index()
    {
        $user = Auth::user();
        
        // Les administrateurs (admin) et super-utilisateurs (super_user) ont accès à toutes les sections
        // La méthode isAdmin() vérifie à la fois 'admin' et 'super_user'
        $isAdmin = $user->isAdmin();
        
        // Vérifier que l'utilisateur est un ambassadeur actif, un provider, ou un administrateur/super admin
        $ambassador = null;
        if ($isAdmin) {
            // Les admins et super admins peuvent accéder, pas besoin de vérifier le rôle ambassador
            // Mais on peut essayer de récupérer l'ambassadeur s'il existe
            $ambassador = Ambassador::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
        } elseif ($user->hasRole('ambassador')) {
            $ambassador = Ambassador::where('user_id', $user->id)
                ->where('is_active', true)
                ->firstOrFail();
        } elseif (!$user->hasRole('provider')) {
            abort(403, 'Accès réservé aux ambassadeurs, providers, administrateurs et super administrateurs');
        }

        // Créer un wallet si l'utilisateur n'en a pas
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'currency' => config('services.moneroo.default_currency', 'USD'),
                'balance' => 0,
                'pending_balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'is_active' => true,
            ]
        );

        // 🔓 LIBÉRATION AUTOMATIQUE : Libérer les fonds expirés lors de l'accès au wallet
        $releasedCount = $this->autoReleaseService->releaseExpiredHoldsForWallet($wallet);
        
        // Recharger le wallet si des fonds ont été libérés
        if ($releasedCount > 0) {
            $wallet->refresh();
            session()->flash('success', "{$releasedCount} fond(s) ont été automatiquement libérés et sont maintenant disponibles au retrait !");
        }

        // Récupérer les statistiques du wallet
        $stats = $wallet->getStats();

        // Récupérer les transactions récentes (20 dernières)
        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Récupérer les payouts récents (10 derniers)
        $payouts = $wallet->payouts()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Récupérer les payouts en attente
        $pendingPayouts = $wallet->pendingPayouts();

        // Récupérer la configuration Moneroo (pays et providers)
        $monerooData = $this->getMonerooConfiguration();

        return view('wallet.index', compact(
            'wallet',
            'ambassador',
            'stats',
            'transactions',
            'payouts',
            'pendingPayouts',
            'monerooData'
        ));
    }

    /**
     * Afficher les transactions du wallet
     */
    public function transactions(Request $request)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();
            
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        // 🔓 LIBÉRATION AUTOMATIQUE : Libérer les fonds expirés lors de l'accès aux transactions
        $releasedCount = $this->autoReleaseService->releaseExpiredHoldsForWallet($wallet);
        
        if ($releasedCount > 0) {
            $wallet->refresh();
        }

        // Validation des entrées
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:credit,debit,commission,payout,refund,bonus',
            'status' => 'nullable|string|in:completed,pending,failed,cancelled',
            'from' => 'nullable|date|before_or_equal:today',
            'to' => 'nullable|date|after_or_equal:from|before_or_equal:today',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0|gte:min_amount',
            'sort_by' => 'nullable|string|in:created_at,amount,balance_after',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|in:10,20,30,50,100',
        ]);

        $query = $wallet->transactions();

        // 🔒 PROTECTION : S'assurer que seules les transactions de l'utilisateur sont accessibles
        $query->whereHas('wallet', function($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        // Recherche globale
        if ($request->filled('search')) {
            $searchTerm = $validated['search'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('reference', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filtrer par type
        if ($request->filled('type')) {
            $query->where('type', $validated['type']);
        }

        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $validated['status']);
        }

        // Filtrer par période
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        // Filtrer par montant
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $validated['min_amount']);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $validated['max_amount']);
        }

        // Tri
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $validated['per_page'] ?? 20;
        $transactions = $query->paginate($perPage)->withQueryString();

        return view('wallet.transactions', compact('wallet', 'transactions'));
    }

    /**
     * Afficher les payouts du wallet
     */
    public function payouts(Request $request)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();
            
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        // 🔓 LIBÉRATION AUTOMATIQUE : Libérer les fonds expirés lors de l'accès aux payouts
        $releasedCount = $this->autoReleaseService->releaseExpiredHoldsForWallet($wallet);
        
        if ($releasedCount > 0) {
            $wallet->refresh();
        }

        // Validation des entrées
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,processing,completed,failed,cancelled',
            'from' => 'nullable|date|before_or_equal:today',
            'to' => 'nullable|date|after_or_equal:from|before_or_equal:today',
            'sort_by' => 'nullable|string|in:created_at,amount',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|in:10,20,30,50,100',
        ]);

        $query = $wallet->payouts();

        // 🔒 PROTECTION : S'assurer que seuls les payouts de l'utilisateur sont accessibles
        $query->whereHas('wallet', function($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        // Recherche globale
        if ($request->filled('search')) {
            $searchTerm = $validated['search'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('moneroo_id', 'like', '%' . $searchTerm . '%')
                  ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $validated['status']);
        }

        // Filtrer par période
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        // Tri
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $validated['per_page'] ?? 20;
        $payouts = $query->paginate($perPage)->withQueryString();

        return view('wallet.payouts', compact('wallet', 'payouts'));
    }

    /**
     * Afficher le formulaire de retrait
     */
    public function createPayout()
    {
        $user = Auth::user();
        
        // 🔒 PROTECTION : Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();
            
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        // 🔓 LIBÉRATION AUTOMATIQUE : Libérer les fonds expirés avant de créer un payout
        $releasedCount = $this->autoReleaseService->releaseExpiredHoldsForWallet($wallet);
        
        if ($releasedCount > 0) {
            $wallet->refresh();
            session()->flash('success', "{$releasedCount} fond(s) ont été automatiquement libérés et sont maintenant disponibles au retrait !");
        }

        $minPayout = (float) \App\Models\Setting::get('wallet_minimum_payout_amount', 5);
        if ($wallet->available_balance < $minPayout) {
            return redirect()->route('wallet.index')
                ->with('warning', "Le montant minimum de retrait est de {$minPayout} {$wallet->currency}. Votre solde disponible ({$wallet->available_balance} {$wallet->currency}) ne permet pas encore de demander un retrait.");
        }

        // Récupérer la configuration Moneroo (pays et providers)
        $monerooData = $this->getMonerooConfiguration();

        return view('wallet.create-payout', compact('wallet', 'monerooData'));
    }

    /**
     * Initier un retrait
     */
    public function storePayout(Request $request)
    {
        $minPayout = (float) \App\Models\Setting::get('wallet_minimum_payout_amount', 5);

        // 🔒 PROTECTION : Validation stricte des entrées
        $validated = $request->validate([
            'amount' => "required|numeric|min:{$minPayout}|max:100000",
            'method' => 'required|string|max:64|regex:/^[a-zA-Z0-9_-]+$/',
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/'],
            'country' => 'required|string|size:2|alpha',
            'currency' => 'required|string|size:3|alpha',
            'description' => 'nullable|string|max:255',
        ], [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.min' => "Le montant minimum est de {$minPayout}.",
            'amount.max' => 'Le montant maximum est de 100,000.',
            'method.required' => 'La méthode de paiement est obligatoire.',
            'method.regex' => 'La méthode de paiement sélectionnée n\'est pas valide.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.regex' => 'Le format du numéro de téléphone n\'est pas valide.',
            'country.required' => 'Le pays est obligatoire.',
            'country.alpha' => 'Le pays sélectionné n\'est pas valide.',
            'currency.required' => 'La devise est obligatoire.',
            'currency.alpha' => 'La devise sélectionnée n\'est pas valide.',
        ]);

        $user = Auth::user();
        
        // 🔒 PROTECTION : Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();
            
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        // 🔓 LIBÉRATION AUTOMATIQUE : Libérer les fonds expirés avant de vérifier le solde
        $releasedCount = $this->autoReleaseService->releaseExpiredHoldsForWallet($wallet);
        
        if ($releasedCount > 0) {
            $wallet->refresh();
            Log::info('Fonds automatiquement libérés avant retrait', [
                'wallet_id' => $wallet->id,
                'released_count' => $releasedCount,
            ]);
        }

        // Vérifier que le solde atteint le montant minimum de retrait
        if ($wallet->available_balance < $minPayout) {
            return redirect()->back()
                ->with('error', "Le montant minimum de retrait est de {$minPayout} {$wallet->currency}. Votre solde disponible ({$wallet->available_balance} {$wallet->currency}) ne permet pas encore de demander un retrait.")
                ->withInput();
        }

        // Vérifier que le wallet a suffisamment de solde DISPONIBLE
        if (!$wallet->hasBalance($request->amount)) {
            $heldInfo = '';
            if ($wallet->held_balance > 0) {
                $heldInfo = " Vous avez {$wallet->held_balance} {$wallet->currency} en période de blocage qui seront bientôt disponibles.";
            }
            
            return redirect()->back()
                ->with('error', "Solde disponible insuffisant. Vous avez {$wallet->available_balance} {$wallet->currency} disponibles, mais vous essayez de retirer {$request->amount} {$request->currency}.{$heldInfo}")
                ->withInput();
        }

        // Initier le payout via Moneroo avec les données validées
        $result = $this->monerooPayoutService->initiateWalletPayout(
            $wallet,
            $validated['amount'],
            $validated['currency'],
            $validated['phone'],
            $validated['method'],
            $validated['country'],
            $validated['description'] ?? null
        );

        if ($result['success']) {
            return redirect()->route('wallet.index')
                ->with('success', 'Votre demande de retrait a été initiée avec succès ! Elle sera traitée dans les prochaines minutes.');
        } else {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'initiation du retrait : ' . ($result['error'] ?? 'Erreur inconnue'))
                ->withInput();
        }
    }

    /**
     * Afficher les détails d'un payout
     */
    public function showPayout(WalletPayout $payout)
    {
        $user = Auth::user();
        
        // 🔒 PROTECTION : Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // 🔒 PROTECTION : Vérifier que le payout appartient bien à l'utilisateur
        if ($payout->wallet->user_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à ce retrait.');
        }

        return view('wallet.show-payout', compact('payout'));
    }

    /**
     * Annuler un payout en attente
     */
    public function cancelPayout(WalletPayout $payout)
    {
        $user = Auth::user();
        
        // 🔒 PROTECTION : Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // 🔒 PROTECTION : Vérifier que le payout appartient bien à l'utilisateur
        if ($payout->wallet->user_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à ce retrait.');
        }

        // Vérifier que le payout peut être annulé
        if (!$payout->canBeCancelled()) {
            return redirect()->back()
                ->with('error', 'Ce retrait ne peut pas être annulé car il est déjà en cours de traitement ou terminé.');
        }

        // Annuler le payout
        if ($payout->cancel('Annulé par l\'utilisateur')) {
            return redirect()->route('wallet.index')
                ->with('success', 'Le retrait a été annulé avec succès. Le montant a été remboursé dans votre wallet.');
        } else {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'annulation du retrait. Veuillez réessayer ou contacter le support.');
        }
    }

    /**
     * Vérifier le statut d'un payout auprès de Moneroo
     */
    public function checkPayoutStatus(WalletPayout $payout)
    {
        $user = Auth::user();
        
        // 🔒 PROTECTION : Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // 🔒 PROTECTION : Vérifier que le payout appartient bien à l'utilisateur
        if ($payout->wallet->user_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à ce retrait.');
        }

        if (!$payout->moneroo_id) {
            return redirect()->back()
                ->with('error', 'Ce retrait n\'a pas encore été envoyé à Moneroo.');
        }

        // Vérifier le statut auprès de Moneroo
        $result = $this->monerooPayoutService->checkWalletPayoutStatus($payout->moneroo_id);

        if ($result['success']) {
            return redirect()->back()
                ->with('success', 'Le statut du retrait a été mis à jour : ' . ($result['status'] ?? 'Inconnu'));
        } else {
            return redirect()->back()
                ->with('error', 'Erreur lors de la vérification du statut : ' . ($result['error'] ?? 'Erreur inconnue'));
        }
    }

    /**
     * Webhook Moneroo pour les payouts wallet
     */
    public function webhookPayout(Request $request)
    {
        // Vérifier la signature du webhook si nécessaire
        // TODO: Implémenter la vérification de la signature Moneroo

        Log::info('Moneroo webhook payout reçu', [
            'data' => $request->all(),
        ]);

        // Traiter le callback
        $success = $this->monerooPayoutService->handleWalletPayoutCallback($request->all());

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Webhook traité avec succès',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement du webhook',
            ], 400);
        }
    }

    /**
     * Récupérer la configuration Moneroo (pays et providers)
     * (Reprise de la méthode dans AmbassadorApplicationController)
     */
    private function getMonerooConfiguration(): array
    {
        $utilsBaseUrl = rtrim(config('services.moneroo.utils_base_url', 'https://api.moneroo.io'), '/');
        $apiKey = config('services.moneroo.api_key');

        $url = "{$utilsBaseUrl}/utils/payout/methods";

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        if ($apiKey) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        try {
            Log::info('Tentative de récupération des méthodes Moneroo', [
                'url' => $url,
                'api_key_present' => !empty($apiKey),
            ]);

            $response = Http::timeout(15)
                ->retry(2, 100)
                ->withHeaders($headers)
                ->get($url);

            if ($response->successful()) {
                $responseData = $response->json();
                $methodsList = $responseData['data'] ?? [];

                if (!is_array($methodsList)) {
                    $methodsList = [];
                }

                $countries = [];
                $providers = [];

                foreach ($methodsList as $method) {
                    if (empty($method['short_code']) || empty($method['is_enabled'])) {
                        continue;
                    }

                    $methodCode = $method['short_code'];
                    $methodName = $method['name'] ?? $methodCode;
                    $currencyCode = $method['currency']['code'] ?? $method['currency'] ?? '';
                    $countryList = $method['countries'] ?? [];
                    $countryCode = $countryList[0]['code'] ?? $method['country'] ?? '';
                    $countryName = $countryList[0]['name'] ?? $countryCode;

                    if ($countryCode && !isset($countries[$countryCode])) {
                        $countries[$countryCode] = [
                            'code' => $countryCode,
                            'name' => $countryName,
                            'prefix' => '',
                            'flag' => '',
                            'currency' => $currencyCode,
                        ];
                    }

                    $providers[] = [
                        'code' => $methodCode,
                        'name' => $methodName,
                        'country' => $countryCode,
                        'currencies' => $currencyCode ? [$currencyCode] : [],
                        'currency' => $currencyCode,
                        'logo' => $method['icon_url'] ?? '',
                    ];
                }

                $countries = array_values($countries);
                usort($countries, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                usort($providers, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });

                return [
                    'countries' => $countries,
                    'providers' => $providers,
                ];
            } else {
                Log::warning('Échec de la récupération de la configuration Moneroo - Utilisation du fallback', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'url' => $url,
                ]);
                
                // TEMPORAIRE: Utiliser les données statiques en attendant la confirmation de Moneroo
                return $this->getStaticMonerooMethods();
            }
        } catch (\Exception $e) {
            Log::warning('Erreur lors de la récupération de la configuration Moneroo - Utilisation du fallback', [
                'error' => $e->getMessage(),
                'url' => $url ?? 'URL non définie',
            ]);
            
            // TEMPORAIRE: Utiliser les données statiques en attendant la confirmation de Moneroo
            return $this->getStaticMonerooMethods();
        }
        
        // TEMPORAIRE: Si aucune donnée de l'API, utiliser les données statiques
        return $this->getStaticMonerooMethods();
    }

    /**
     * TEMPORAIRE: Données statiques des méthodes Moneroo
     * 
     * En attendant la confirmation de Moneroo sur l'endpoint correct pour récupérer
     * la liste des méthodes de payout disponibles via l'API.
     * 
     * À remplacer par l'appel API une fois que Moneroo fournira l'endpoint correct.
     */
    private function getStaticMonerooMethods(): array
    {
        return [
            'countries' => [
                [
                    'code' => 'CD',
                    'name' => 'République Démocratique du Congo',
                    'prefix' => '+243',
                    'flag' => '🇨🇩',
                    'currency' => 'CDF',
                ],
                [
                    'code' => 'CM',
                    'name' => 'Cameroun',
                    'prefix' => '+237',
                    'flag' => '🇨🇲',
                    'currency' => 'XAF',
                ],
                [
                    'code' => 'CI',
                    'name' => 'Côte d\'Ivoire',
                    'prefix' => '+225',
                    'flag' => '🇨🇮',
                    'currency' => 'XOF',
                ],
                [
                    'code' => 'SN',
                    'name' => 'Sénégal',
                    'prefix' => '+221',
                    'flag' => '🇸🇳',
                    'currency' => 'XOF',
                ],
                [
                    'code' => 'BJ',
                    'name' => 'Bénin',
                    'prefix' => '+229',
                    'flag' => '🇧🇯',
                    'currency' => 'XOF',
                ],
                [
                    'code' => 'BF',
                    'name' => 'Burkina Faso',
                    'prefix' => '+226',
                    'flag' => '🇧🇫',
                    'currency' => 'XOF',
                ],
                [
                    'code' => 'ML',
                    'name' => 'Mali',
                    'prefix' => '+223',
                    'flag' => '🇲🇱',
                    'currency' => 'XOF',
                ],
                [
                    'code' => 'NE',
                    'name' => 'Niger',
                    'prefix' => '+227',
                    'flag' => '🇳🇪',
                    'currency' => 'XOF',
                ],
                [
                    'code' => 'TG',
                    'name' => 'Togo',
                    'prefix' => '+228',
                    'flag' => '🇹🇬',
                    'currency' => 'XOF',
                ],
                [
                    'code' => 'GH',
                    'name' => 'Ghana',
                    'prefix' => '+233',
                    'flag' => '🇬🇭',
                    'currency' => 'GHS',
                ],
                [
                    'code' => 'NG',
                    'name' => 'Nigeria',
                    'prefix' => '+234',
                    'flag' => '🇳🇬',
                    'currency' => 'NGN',
                ],
                [
                    'code' => 'KE',
                    'name' => 'Kenya',
                    'prefix' => '+254',
                    'flag' => '🇰🇪',
                    'currency' => 'KES',
                ],
                [
                    'code' => 'RW',
                    'name' => 'Rwanda',
                    'prefix' => '+250',
                    'flag' => '🇷🇼',
                    'currency' => 'RWF',
                ],
                [
                    'code' => 'UG',
                    'name' => 'Ouganda',
                    'prefix' => '+256',
                    'flag' => '🇺🇬',
                    'currency' => 'UGX',
                ],
                [
                    'code' => 'TZ',
                    'name' => 'Tanzanie',
                    'prefix' => '+255',
                    'flag' => '🇹🇿',
                    'currency' => 'TZS',
                ],
            ],
            'providers' => [
                // RDC
                ['code' => 'vodacom_mpesa', 'name' => 'Vodacom M-Pesa', 'country' => 'CD', 'currencies' => ['USD', 'CDF'], 'currency' => 'USD', 'logo' => ''],
                ['code' => 'airtel_money', 'name' => 'Airtel Money', 'country' => 'CD', 'currencies' => ['USD', 'CDF'], 'currency' => 'USD', 'logo' => ''],
                ['code' => 'orange_money', 'name' => 'Orange Money', 'country' => 'CD', 'currencies' => ['USD', 'CDF'], 'currency' => 'USD', 'logo' => ''],
                ['code' => 'africell_money', 'name' => 'Africell Money', 'country' => 'CD', 'currencies' => ['USD', 'CDF'], 'currency' => 'USD', 'logo' => ''],
                
                // Cameroun
                ['code' => 'mtn_momo', 'name' => 'MTN Mobile Money', 'country' => 'CM', 'currencies' => ['XAF'], 'currency' => 'XAF', 'logo' => ''],
                ['code' => 'orange_money', 'name' => 'Orange Money', 'country' => 'CM', 'currencies' => ['XAF'], 'currency' => 'XAF', 'logo' => ''],
                
                // Côte d'Ivoire
                ['code' => 'mtn_momo', 'name' => 'MTN Mobile Money', 'country' => 'CI', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                ['code' => 'orange_money', 'name' => 'Orange Money', 'country' => 'CI', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                ['code' => 'moov_money', 'name' => 'Moov Money', 'country' => 'CI', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                ['code' => 'wave', 'name' => 'Wave', 'country' => 'CI', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                
                // Sénégal
                ['code' => 'orange_money', 'name' => 'Orange Money', 'country' => 'SN', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                ['code' => 'free_money', 'name' => 'Free Money', 'country' => 'SN', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                ['code' => 'wave', 'name' => 'Wave', 'country' => 'SN', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                
                // Bénin, Burkina Faso, Mali, Niger, Togo (Zone XOF)
                ['code' => 'mtn_momo', 'name' => 'MTN Mobile Money', 'country' => 'BJ', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                ['code' => 'moov_money', 'name' => 'Moov Money', 'country' => 'BJ', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                ['code' => 'mtn_momo', 'name' => 'MTN Mobile Money', 'country' => 'BF', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                ['code' => 'orange_money', 'name' => 'Orange Money', 'country' => 'BF', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                ['code' => 'orange_money', 'name' => 'Orange Money', 'country' => 'ML', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                ['code' => 'orange_money', 'name' => 'Orange Money', 'country' => 'NE', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                ['code' => 'moov_money', 'name' => 'Moov Money', 'country' => 'TG', 'currencies' => ['XOF'], 'currency' => 'XOF', 'logo' => ''],
                
                // Ghana
                ['code' => 'mtn_momo', 'name' => 'MTN Mobile Money', 'country' => 'GH', 'currencies' => ['GHS'], 'currency' => 'GHS', 'logo' => ''],
                ['code' => 'vodafone_cash', 'name' => 'Vodafone Cash', 'country' => 'GH', 'currencies' => ['GHS'], 'currency' => 'GHS', 'logo' => ''],
                ['code' => 'airteltigo', 'name' => 'AirtelTigo Money', 'country' => 'GH', 'currencies' => ['GHS'], 'currency' => 'GHS', 'logo' => ''],
                
                // Nigeria
                ['code' => 'mtn_momo', 'name' => 'MTN Mobile Money', 'country' => 'NG', 'currencies' => ['NGN'], 'currency' => 'NGN', 'logo' => ''],
                
                // Kenya
                ['code' => 'mpesa', 'name' => 'M-Pesa', 'country' => 'KE', 'currencies' => ['KES'], 'currency' => 'KES', 'logo' => ''],
                ['code' => 'airtel_money', 'name' => 'Airtel Money', 'country' => 'KE', 'currencies' => ['KES'], 'currency' => 'KES', 'logo' => ''],
                
                // Rwanda
                ['code' => 'mtn_momo', 'name' => 'MTN Mobile Money', 'country' => 'RW', 'currencies' => ['RWF'], 'currency' => 'RWF', 'logo' => ''],
                ['code' => 'airtel_money', 'name' => 'Airtel Money', 'country' => 'RW', 'currencies' => ['RWF'], 'currency' => 'RWF', 'logo' => ''],
                
                // Ouganda
                ['code' => 'mtn_momo', 'name' => 'MTN Mobile Money', 'country' => 'UG', 'currencies' => ['UGX'], 'currency' => 'UGX', 'logo' => ''],
                ['code' => 'airtel_money', 'name' => 'Airtel Money', 'country' => 'UG', 'currencies' => ['UGX'], 'currency' => 'UGX', 'logo' => ''],
                
                // Tanzanie
                ['code' => 'mpesa', 'name' => 'M-Pesa', 'country' => 'TZ', 'currencies' => ['TZS'], 'currency' => 'TZS', 'logo' => ''],
                ['code' => 'tigo_pesa', 'name' => 'Tigo Pesa', 'country' => 'TZ', 'currencies' => ['TZS'], 'currency' => 'TZS', 'logo' => ''],
                ['code' => 'airtel_money', 'name' => 'Airtel Money', 'country' => 'TZ', 'currencies' => ['TZS'], 'currency' => 'TZS', 'logo' => ''],
            ],
        ];
    }
}
