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
        
        // Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

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
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        // 🔓 LIBÉRATION AUTOMATIQUE : Libérer les fonds expirés lors de l'accès aux transactions
        $releasedCount = $this->autoReleaseService->releaseExpiredHoldsForWallet($wallet);
        
        if ($releasedCount > 0) {
            $wallet->refresh();
        }

        $query = $wallet->transactions()->orderBy('created_at', 'desc');

        // Filtrer par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtrer par période
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        $transactions = $query->paginate(30);

        return view('wallet.transactions', compact('wallet', 'transactions'));
    }

    /**
     * Afficher les payouts du wallet
     */
    public function payouts(Request $request)
    {
        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        // 🔓 LIBÉRATION AUTOMATIQUE : Libérer les fonds expirés lors de l'accès aux payouts
        $releasedCount = $this->autoReleaseService->releaseExpiredHoldsForWallet($wallet);
        
        if ($releasedCount > 0) {
            $wallet->refresh();
        }

        $query = $wallet->payouts()->orderBy('created_at', 'desc');

        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtrer par période
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        $payouts = $query->paginate(20);

        return view('wallet.payouts', compact('wallet', 'payouts'));
    }

    /**
     * Afficher le formulaire de retrait
     */
    public function createPayout()
    {
        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        // 🔓 LIBÉRATION AUTOMATIQUE : Libérer les fonds expirés avant de créer un payout
        $releasedCount = $this->autoReleaseService->releaseExpiredHoldsForWallet($wallet);
        
        if ($releasedCount > 0) {
            $wallet->refresh();
            session()->flash('success', "{$releasedCount} fond(s) ont été automatiquement libérés et sont maintenant disponibles au retrait !");
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
        $request->validate([
            'amount' => 'required|numeric|min:5',
            'method' => 'required|string',
            'phone' => 'required|string',
            'country' => 'required|string|size:2',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string|max:255',
        ], [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.min' => 'Le montant minimum est de 5.',
            'method.required' => 'La méthode de paiement est obligatoire.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'country.required' => 'Le pays est obligatoire.',
            'currency.required' => 'La devise est obligatoire.',
        ]);

        $user = Auth::user();
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

        // Initier le payout via Moneroo
        $result = $this->monerooPayoutService->initiateWalletPayout(
            $wallet,
            $request->amount,
            $request->currency,
            $request->phone,
            $request->method,
            $request->country,
            $request->description
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

        // Vérifier que le payout appartient bien à l'utilisateur
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

        // Vérifier que le payout appartient bien à l'utilisateur
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

        // Vérifier que le payout appartient bien à l'utilisateur
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
        $baseUrl = rtrim(config('services.moneroo.base_url', 'https://api.moneroo.io/v1'), '/');
        $apiKey = config('services.moneroo.api_key');
        
        if (!$apiKey) {
            Log::error('MONEROO_API_KEY non configurée.');
            return ['countries' => [], 'providers' => [], 'error' => 'API Key non configurée'];
        }

        try {
            // Utiliser l'endpoint /payouts/available-methods selon la documentation Moneroo
            $url = "{$baseUrl}/payouts/available-methods";
            
            Log::info('Tentative de récupération des méthodes Moneroo', [
                'url' => $url,
                'api_key_present' => !empty($apiKey),
            ]);
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->get($url);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('Réponse Moneroo reçue', [
                    'status' => $response->status(),
                    'has_data' => isset($responseData['data']),
                    'response_keys' => array_keys($responseData),
                ]);
                
                $data = $responseData['data'] ?? $responseData;
                
                $countries = [];
                $providers = [];
                
                if (isset($data['methods']) && is_array($data['methods'])) {
                    foreach ($data['methods'] as $method) {
                        $countryCode = $method['country'] ?? '';
                        $providerCode = $method['payment_method'] ?? $method['provider'] ?? '';
                        $providerName = $method['name'] ?? $providerCode;
                        $currencies = $method['currencies'] ?? ($method['currency'] ? [$method['currency']] : []);
                        
                        if ($countryCode && !isset($countries[$countryCode])) {
                            $countries[$countryCode] = [
                                'code' => $countryCode,
                                'name' => $countryCode,
                                'prefix' => '',
                                'flag' => '',
                                'currency' => !empty($currencies) ? $currencies[0] : '',
                            ];
                        }
                        
                        if ($providerCode) {
                            $providers[] = [
                                'code' => $providerCode,
                                'name' => $providerName,
                                'country' => $countryCode,
                                'currencies' => $currencies,
                                'currency' => !empty($currencies) ? $currencies[0] : '',
                                'logo' => $method['logo'] ?? '',
                            ];
                        }
                    }
                    $countries = array_values($countries);
                } elseif (isset($data['countries']) && is_array($data['countries'])) {
                    foreach ($data['countries'] as $country) {
                        $countryCode = $country['country'] ?? '';
                        $countryName = $country['displayName']['fr'] ?? $country['displayName']['en'] ?? $countryCode;
                        $countryCurrency = $country['currency'] ?? '';
                        
                        $countries[] = [
                            'code' => $countryCode,
                            'name' => $countryName,
                            'prefix' => $country['prefix'] ?? '',
                            'flag' => $country['flag'] ?? '',
                            'currency' => $countryCurrency,
                        ];
                        
                        if (isset($country['providers']) && is_array($country['providers'])) {
                            foreach ($country['providers'] as $provider) {
                                try {
                                    $providerCode = $provider['provider'] ?? '';
                                    $providerName = $provider['displayName'] ?? $provider['name'] ?? $providerCode;
                                    
                                    $currencies = [];
                                    if (isset($provider['currencies']) && is_array($provider['currencies'])) {
                                        $currencies = array_values(array_filter(
                                            array_map(function($c) {
                                                if (is_array($c) && isset($c['currency'])) {
                                                    return $c['currency'];
                                                }
                                                if (is_string($c)) {
                                                    return $c;
                                                }
                                                return null;
                                            }, $provider['currencies']),
                                            function($currency) {
                                                return !empty($currency) && is_string($currency);
                                            }
                                        ));
                                    } elseif (isset($provider['currency']) && !empty($provider['currency'])) {
                                        if (is_array($provider['currency'])) {
                                            $currencies = array_values(array_filter($provider['currency'], function($c) {
                                                return !empty($c) && is_string($c);
                                            }));
                                        } else {
                                            $currencies = [$provider['currency']];
                                        }
                                    }
                                    
                                    if (empty($currencies) && !empty($countryCurrency)) {
                                        $currencies = [$countryCurrency];
                                    }
                                    
                                    $providers[] = [
                                        'code' => $providerCode,
                                        'name' => $providerName,
                                        'country' => $countryCode,
                                        'logo' => $provider['logo'] ?? '',
                                        'currencies' => $currencies,
                                        'currency' => !empty($currencies) ? $currencies[0] : '',
                                    ];
                                } catch (\Exception $e) {
                                    Log::warning('Error processing provider', [
                                        'provider' => $provider['provider'] ?? 'unknown',
                                        'error' => $e->getMessage(),
                                    ]);
                                    continue;
                                }
                            }
                        }
                    }
                }
                
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
                Log::error('Échec de la récupération de la configuration Moneroo', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'url' => $url,
                ]);
                
                return [
                    'countries' => [],
                    'providers' => [],
                    'error' => 'Erreur API Moneroo: ' . $response->status(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la configuration Moneroo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'url' => $url ?? 'URL non définie',
            ]);
            
            return [
                'countries' => [],
                'providers' => [],
                'error' => $e->getMessage(),
            ];
        }
        
        return ['countries' => [], 'providers' => [], 'error' => 'Aucune donnée retournée'];
    }
}
