<?php

namespace App\Http\Controllers;

use App\Models\AmbassadorApplication;
use App\Models\Ambassador;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AmbassadorApplicationController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    /**
     * Afficher la page d'explication du programme ambassadeur
     */
    public function index()
    {
        $application = null;
        $isAmbassador = false;

        if (Auth::check()) {
            $isAmbassador = Ambassador::where('user_id', Auth::id())
                ->where('is_active', true)
                ->exists();

            if ($isAmbassador) {
                return redirect()->route('ambassador.dashboard');
            }

            $application = AmbassadorApplication::where('user_id', Auth::id())->first();
        }

        return view('ambassador-application.index', compact('application', 'isAmbassador'));
    }

    /**
     * Dashboard ambassadeur
     */
    public function dashboard()
    {
        $ambassador = Ambassador::where('user_id', Auth::id())
            ->where('is_active', true)
            ->with(['commissions.order', 'promoCodes'])
            ->firstOrFail();

        $promoCode = $ambassador->activePromoCode();

        // Calculer les métriques pour les 30 derniers jours
        $commissionsCurrent = $ambassador->commissions()
            ->where('created_at', '>=', now()->subDays(30))
            ->get();
        $commissionsPrevious = $ambassador->commissions()
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->get();

        $earningsCurrent = $commissionsCurrent->where('status', 'paid')->sum('commission_amount');
        $earningsPrevious = $commissionsPrevious->where('status', 'paid')->sum('commission_amount');

        $referralsCurrent = $ambassador->commissions()
            ->where('created_at', '>=', now()->subDays(30))
            ->distinct('order_id')
            ->count('order_id');
        $referralsPrevious = $ambassador->commissions()
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->distinct('order_id')
            ->count('order_id');

        $salesCurrent = $ambassador->commissions()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        $salesPrevious = $ambassador->commissions()
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->count();

        // Fonction pour calculer le pourcentage de tendance
        $percentTrend = function($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }
            return (($current - $previous) / $previous) * 100;
        };

        // Fonction pour formater la devise
        $formatCurrency = function($amount) {
            $currency = \App\Models\Setting::getBaseCurrency();
            $currencyCode = is_array($currency) ? ($currency['code'] ?? 'USD') : ($currency ?? 'USD');
            return number_format($amount, 2) . ' ' . $currencyCode;
        };

        // Calculer les gains totaux et en attente depuis les commissions réelles en base de données
        $totalEarningsFromDB = $ambassador->commissions()->sum('commission_amount');
        $pendingEarningsFromDB = $ambassador->commissions()
            ->where('status', '!=', 'paid')
            ->sum('commission_amount');
        
        // Utiliser les valeurs calculées depuis la DB, avec fallback sur les champs stockés
        $totalEarnings = $totalEarningsFromDB > 0 ? $totalEarningsFromDB : ($ambassador->total_earnings ?? 0);
        $pendingEarnings = $pendingEarningsFromDB > 0 ? $pendingEarningsFromDB : ($ambassador->pending_earnings ?? 0);

        $metrics = [
            [
                'label' => 'Gains totaux',
                'icon' => 'fas fa-coins',
                'value' => $formatCurrency($totalEarnings),
                'trend' => $percentTrend($earningsCurrent, $earningsPrevious),
                'accent' => '#6366f1',
            ],
            [
                'label' => 'En attente',
                'icon' => 'fas fa-hourglass-half',
                'value' => $formatCurrency($pendingEarnings),
                'trend' => 0,
                'accent' => '#f59e0b',
            ],
            [
                'label' => 'Références (30 j)',
                'icon' => 'fas fa-user-plus',
                'value' => number_format($referralsCurrent),
                'trend' => $percentTrend($referralsCurrent, $referralsPrevious),
                'accent' => '#22c55e',
            ],
            [
                'label' => 'Ventes (30 j)',
                'icon' => 'fas fa-shopping-cart',
                'value' => number_format($salesCurrent),
                'trend' => $percentTrend($salesCurrent, $salesPrevious),
                'accent' => '#0ea5e9',
            ],
        ];

        $recentCommissions = $ambassador->commissions()
            ->with('order')
            ->latest()
            ->limit(7)
            ->get();

        $recentOrders = \App\Models\Order::where('ambassador_id', $ambassador->id)
            ->with(['user', 'orderItems.course'])
            ->latest()
            ->limit(5)
            ->get();

        $pendingTasks = [];
        if (!$promoCode) {
            $pendingTasks[] = [
                'title' => 'Code promo manquant',
                'description' => 'Vous n\'avez pas encore de code promo actif. Contactez l\'administration pour en obtenir un.',
                'type' => 'alert',
            ];
        }
        if ($pendingEarnings > 0 && $pendingEarnings < 50) {
            $pendingTasks[] = [
                'title' => 'Gains en attente',
                'description' => 'Vous avez des gains en attente de paiement. Continuez à partager votre code promo pour augmenter vos revenus.',
                'type' => 'info',
            ];
        }
        if ($referralsCurrent > $referralsPrevious) {
            $pendingTasks[] = [
                'title' => 'Momentum positif',
                'description' => 'Vos références progressent ! Continuez à partager votre code promo pour maximiser vos gains.',
                'type' => 'success',
            ];
        }

        return view('ambassadors.admin.dashboard', [
            'ambassador' => $ambassador,
            'promoCode' => $promoCode,
            'metrics' => $metrics,
            'recentCommissions' => $recentCommissions,
            'recentOrders' => $recentOrders,
            'pendingTasks' => $pendingTasks,
        ]);
    }

    /**
     * Afficher le formulaire de candidature (étape 1)
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour postuler.');
        }

        // Vérifier si l'utilisateur est déjà ambassadeur
        $isAmbassador = Ambassador::where('user_id', Auth::id())
            ->where('is_active', true)
            ->exists();

        if ($isAmbassador) {
            return redirect()->route('ambassador.dashboard');
        }

        // Vérifier si une candidature existe déjà - si oui, rediriger vers le statut
        $application = AmbassadorApplication::where('user_id', Auth::id())->first();
        if ($application) {
            // Si la candidature ne peut pas être modifiée, rediriger vers le statut
            if (!$application->canBeEdited()) {
                return redirect()->route('ambassador-application.status', $application)
                    ->with('info', 'Vous avez déjà une candidature en cours. Vous pouvez suivre son statut ci-dessous.');
            }
            // Si la candidature peut être modifiée, permettre de continuer l'édition
            // (c'est le cas pour les candidatures rejetées qui peuvent être modifiées)
        }

        return view('ambassador-application.create', [
            'application' => $application
        ]);
    }

    /**
     * Sauvegarder les informations de base (étape 1)
     */
    public function storeStep1(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Vérifier si l'utilisateur est déjà ambassadeur
        $isAmbassador = Ambassador::where('user_id', Auth::id())
            ->where('is_active', true)
            ->exists();

        if ($isAmbassador) {
            return redirect()->route('ambassador.dashboard')
                ->with('info', 'Vous êtes déjà ambassadeur.');
        }

        // Vérifier si une candidature existe déjà
        $existingApplication = AmbassadorApplication::where('user_id', Auth::id())->first();
        if ($existingApplication) {
            // Si la candidature ne peut pas être modifiée, rediriger vers le statut
            if (!$existingApplication->canBeEdited()) {
                return redirect()->route('ambassador-application.status', $existingApplication)
                    ->with('error', 'Vous avez déjà une candidature en cours. Vous ne pouvez pas en créer une nouvelle.');
            }
            // Si la candidature peut être modifiée, continuer avec celle-ci
            $application = $existingApplication;
        }

        $user = Auth::user();
        
        // Vérifier que l'utilisateur a un numéro de téléphone
        if (!$user->phone) {
            return redirect()->back()
                ->with('error', 'Veuillez renseigner votre numéro de téléphone dans votre profil avant de continuer.')
                ->withInput();
        }

        $request->validate([
            'motivation' => 'required|string|min:100|max:2000',
        ]);

        // Si aucune candidature n'existe, en créer une nouvelle
        if (!isset($application)) {
            $application = AmbassadorApplication::create([
                'user_id' => Auth::id(),
                'phone' => $user->phone, // Récupérer depuis le profil utilisateur
                'motivation' => $request->motivation,
                'status' => 'pending',
            ]);
        } else {
            // Mettre à jour la candidature existante
            $application->update([
                'phone' => $user->phone,
                'motivation' => $request->motivation,
                'status' => 'pending', // Réinitialiser le statut si c'était rejeté
            ]);
        }

        return redirect()->route('ambassador-application.step2', $application);
    }

    /**
     * Afficher l'étape 2 (expérience et présence en ligne)
     */
    public function step2(AmbassadorApplication $application)
    {
        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$application->canBeEdited()) {
            return redirect()->route('ambassador-application.status', $application);
        }

        return view('ambassador-application.step2', compact('application'));
    }

    /**
     * Sauvegarder l'étape 2
     */
    public function storeStep2(Request $request, AmbassadorApplication $application)
    {
        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier que la candidature peut être modifiée
        if (!$application->canBeEdited()) {
            return redirect()->route('ambassador-application.status', $application)
                ->with('error', 'Cette candidature ne peut plus être modifiée car elle a déjà été soumise.');
        }

        $request->validate([
            'experience' => 'required|string|min:50|max:1000',
            'social_media_presence' => 'nullable|string|max:500',
            'target_audience' => 'nullable|string|max:500',
        ]);

        $application->update([
            'experience' => $request->experience,
            'social_media_presence' => $request->social_media_presence,
            'target_audience' => $request->target_audience,
        ]);

        return redirect()->route('ambassador-application.step3', $application);
    }

    /**
     * Afficher l'étape 3 (idées marketing)
     */
    public function step3(AmbassadorApplication $application)
    {
        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$application->canBeEdited()) {
            return redirect()->route('ambassador-application.status', $application);
        }

        return view('ambassador-application.step3', compact('application'));
    }

    /**
     * Sauvegarder l'étape 3 (idées marketing et document PDF)
     */
    public function storeStep3(Request $request, AmbassadorApplication $application)
    {
        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier que la candidature peut être modifiée
        if (!$application->canBeEdited()) {
            return redirect()->route('ambassador-application.status', $application)
                ->with('error', 'Cette candidature ne peut plus être modifiée car elle a déjà été soumise.');
        }

        $request->validate([
            'marketing_ideas' => 'nullable|string|max:1000',
            'document_path' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf|max:5120', // Fallback si upload direct
        ]);

        try {
            // Si un document_path est fourni (upload par chunks), l'utiliser
            if ($request->filled('document_path')) {
                $documentPath = $request->input('document_path');
                
                // Vérifier que le chemin est valide et dans le bon répertoire
                if (str_starts_with($documentPath, \App\Services\FileUploadService::TEMPORARY_BASE_PATH . '/')) {
                    // Le fichier est déjà uploadé via chunks, on peut l'utiliser directement
                    // Optionnellement, on peut le déplacer vers le répertoire final
                    $application->document_path = $documentPath;
                }
            } 
            // Fallback : upload direct si document_path n'est pas fourni mais qu'un fichier est présent
            elseif ($request->hasFile('document')) {
                $documentPath = $this->fileUploadService->upload(
                    $request->file('document'),
                    'ambassador-applications/documents',
                    $application->document_path
                );
                $application->document_path = $documentPath['path'];
            }
            // Si aucun document n'est fourni, on garde le document existant s'il y en a un, sinon on laisse null

            $updateData = [
                'marketing_ideas' => $request->marketing_ideas,
            ];
            
            // Ne mettre à jour document_path que si un nouveau document est fourni
            if ($request->filled('document_path') || $request->hasFile('document')) {
                $updateData['document_path'] = $application->document_path;
            }

            // Si la candidature est en statut 'pending', la passer en 'under_review' lors de la soumission finale
            // Cela empêchera toute modification ultérieure
            if ($application->status === 'pending') {
                $updateData['status'] = 'under_review';
            }

            $application->update($updateData);

            return redirect()->route('ambassador-application.status', $application)
                ->with('success', 'Votre candidature a été soumise avec succès ! Elle sera examinée par notre équipe.');

        } catch (\Exception $e) {
            Log::error('Error saving ambassador application step 3', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erreur lors de la sauvegarde. Veuillez réessayer.')
                ->withInput();
        }
    }

    /**
     * Afficher le statut de la candidature
     */
    public function status(AmbassadorApplication $application)
    {
        if ($application->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $application->load(['user', 'reviewer']);

        return view('ambassador-application.status', compact('application'));
    }

    /**
     * Abandonner la candidature
     */
    public function abandon(Request $request, AmbassadorApplication $application)
    {
        if (!Auth::check() || $application->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$application->canBeEdited()) {
            return redirect()->route('ambassador-application.status', $application)
                ->with('error', 'Cette candidature ne peut plus être abandonnée.');
        }

        // Supprimer le document PDF si présent
        if ($application->document_path) {
            Storage::delete($application->document_path);
        }

        $application->delete();

        return redirect()->route('ambassador-application.create')
            ->with('success', 'Votre candidature a été réinitialisée. Vous pouvez recommencer depuis le début.');
    }

    /**
     * Télécharger le document PDF
     */
    public function downloadDocument(AmbassadorApplication $application)
    {
        if ($application->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if (!$application->document_path) {
            abort(404);
        }

        return Storage::download($application->document_path);
    }

    /**
     * Analytics ambassadeur
     */
    public function analytics()
    {
        $ambassador = Ambassador::where('user_id', Auth::id())
            ->where('is_active', true)
            ->with(['commissions.order'])
            ->firstOrFail();

        $currency = \App\Models\Setting::getBaseCurrency();
        $currencyCode = is_array($currency) ? ($currency['code'] ?? 'USD') : ($currency ?? 'USD');

        // Statistiques des commissions (calculées depuis la DB)
        $totalCommissions = $ambassador->commissions()->count();
        $paidCommissions = $ambassador->commissions()->where('status', 'paid')->count();
        $pendingCommissions = $ambassador->commissions()->where('status', 'pending')->count();
        
        // Calculer les gains totaux et en attente depuis les commissions réelles en base de données
        $totalEarningsFromDB = $ambassador->commissions()->sum('commission_amount');
        $pendingEarningsFromDB = $ambassador->commissions()
            ->where('status', '!=', 'paid')
            ->sum('commission_amount');
        
        // Utiliser les valeurs calculées depuis la DB, avec fallback sur les champs stockés
        $totalEarnings = $totalEarningsFromDB > 0 ? $totalEarningsFromDB : ($ambassador->total_earnings ?? 0);
        $pendingEarnings = $pendingEarningsFromDB > 0 ? $pendingEarningsFromDB : ($ambassador->pending_earnings ?? 0);

        // Calculer le nombre total de références depuis les commandes réelles en base de données
        // Une référence = une commande qui utilise le code promo de l'ambassadeur
        // Récupérer les IDs des codes promo de l'ambassadeur
        $promoCodeIds = $ambassador->promoCodes()->pluck('id');
        
        $totalReferralsFromDB = \App\Models\Order::where(function($query) use ($ambassador, $promoCodeIds) {
            $query->where('ambassador_id', $ambassador->id)
                  ->orWhereIn('ambassador_promo_code_id', $promoCodeIds);
        })->count();
        
        // Utiliser la valeur calculée depuis la DB, avec fallback sur le champ stocké
        $totalReferrals = $totalReferralsFromDB > 0 ? $totalReferralsFromDB : ($ambassador->total_referrals ?? 0);

        // Évolution des commissions par mois
        $commissionsByMonth = $ambassador->commissions()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count, SUM(commission_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $commissionsByMonth->transform(function ($row) {
            $row->formatted_month = \Carbon\Carbon::createFromFormat('Y-m', $row->month)->translatedFormat('M Y');
            return $row;
        });

        // Top commandes
        $topOrders = $ambassador->commissions()
            ->with('order')
            ->orderBy('commission_amount', 'desc')
            ->limit(10)
            ->get();

        $insights = [];
        if ($pendingCommissions > 0) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Commissions en attente',
                'description' => "Vous avez {$pendingCommissions} commission(s) en attente d'approbation.",
            ];
        }
        if ($totalCommissions > 0 && $paidCommissions / $totalCommissions < 0.5) {
            $insights[] = [
                'type' => 'alert',
                'title' => 'Taux de paiement faible',
                'description' => 'Moins de 50% de vos commissions ont été payées. Contactez l\'administration pour plus d\'informations.',
            ];
        }
        if ($totalCommissions > 10) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Excellent travail !',
                'description' => 'Vous avez généré plus de 10 commissions. Continuez à partager votre code promo !',
            ];
        }

        return view('ambassadors.admin.analytics', [
            'ambassador' => $ambassador,
            'totalCommissions' => $totalCommissions,
            'paidCommissions' => $paidCommissions,
            'pendingCommissions' => $pendingCommissions,
            'totalEarnings' => $totalEarnings,
            'pendingEarnings' => $pendingEarnings,
            'totalReferrals' => $totalReferrals,
            'commissionsByMonth' => $commissionsByMonth,
            'topOrders' => $topOrders,
            'insights' => $insights,
            'currencyCode' => $currencyCode,
        ]);
    }

    /**
     * Configuration de moyen de règlement
     */
    public function paymentSettings()
    {
        $ambassador = Ambassador::where('user_id', Auth::id())
            ->where('is_active', true)
            ->firstOrFail();

        $user = Auth::user();

        // Récupérer les données pawaPay
        $pawapayData = $this->getPawaPayConfiguration();

        return view('ambassadors.admin.payment-settings', [
            'ambassador' => $ambassador,
            'user' => $user,
            'pawapayData' => $pawapayData,
        ]);
    }

    /**
     * Récupérer la configuration pawaPay (pays et providers)
     */
    private function getPawaPayConfiguration(): array
    {
        $apiUrl = config('services.pawapay.api_url', config('services.pawapay.base_url', 'https://api.sandbox.pawapay.io/v2'));
        $apiKey = config('services.pawapay.api_key');
        
        if (!$apiKey) {
            Log::error('PAWAPAY_API_KEY non configurée.');
            return ['countries' => [], 'providers' => []];
        }

        try {
            // Utiliser l'endpoint active-conf selon la documentation pawaPay
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get("{$apiUrl}/active-conf", [
                'operationType' => 'PAYOUT',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('pawaPay configuration retrieved', [
                    'has_countries' => isset($data['countries']),
                    'countries_count' => isset($data['countries']) ? count($data['countries']) : 0,
                ]);
                
                // Extraire les pays et providers selon la structure de la réponse
                $countries = [];
                $providers = [];
                
                if (isset($data['countries']) && is_array($data['countries'])) {
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
                        
                        // Extraire les providers pour ce pays
                        if (isset($country['providers']) && is_array($country['providers'])) {
                            foreach ($country['providers'] as $provider) {
                                try {
                                    $providerCode = $provider['provider'] ?? '';
                                    $providerName = $provider['displayName'] ?? $provider['name'] ?? $providerCode;
                                    
                                    // Extraire les devises disponibles pour ce provider
                                    // Structure selon checkout: provider.currencies est un tableau d'objets avec une propriété 'currency'
                                    $currencies = [];
                                    if (isset($provider['currencies']) && is_array($provider['currencies'])) {
                                        // Filtrer et extraire les codes de devises
                                        $currencies = array_values(array_filter(
                                            array_map(function($c) {
                                                // Si c'est un objet avec une propriété 'currency'
                                                if (is_array($c) && isset($c['currency'])) {
                                                    return $c['currency'];
                                                }
                                                // Si c'est directement une string
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
                                        // Si currency est une seule devise (string)
                                        if (is_array($provider['currency'])) {
                                            $currencies = array_values(array_filter($provider['currency'], function($c) {
                                                return !empty($c) && is_string($c);
                                            }));
                                        } else {
                                            $currencies = [$provider['currency']];
                                        }
                                    }
                                    
                                    // Fallback sur la devise du pays si aucune devise trouvée
                                    if (empty($currencies) && !empty($countryCurrency)) {
                                        $currencies = [$countryCurrency];
                                    }
                                    
                                    $providers[] = [
                                        'code' => $providerCode,
                                        'name' => $providerName,
                                        'country' => $countryCode,
                                        'logo' => $provider['logo'] ?? '',
                                        'currencies' => $currencies, // Liste des codes de devises disponibles
                                        'currency' => !empty($currencies) ? $currencies[0] : '', // Devise par défaut
                                    ];
                                } catch (\Exception $e) {
                                    Log::warning('Error processing provider', [
                                        'provider' => $provider['provider'] ?? 'unknown',
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString(),
                                    ]);
                                    // Continuer avec le provider suivant même en cas d'erreur
                                    continue;
                                }
                            }
                        }
                    }
                }
                
                // Trier les pays par nom
                usort($countries, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                
                // Trier les providers par nom
                usort($providers, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                
                Log::info('pawaPay configuration processed', [
                    'countries_count' => count($countries),
                    'providers_count' => count($providers),
                ]);
                
                return [
                    'countries' => $countries,
                    'providers' => $providers,
                ];
            } else {
                Log::warning('Échec de la récupération de la configuration pawaPay', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la configuration pawaPay', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        
        return ['countries' => [], 'providers' => []];
    }

    /**
     * Mettre à jour la configuration de moyen de règlement
     */
    public function updatePaymentSettings(Request $request)
    {
        $ambassador = Ambassador::where('user_id', Auth::id())
            ->where('is_active', true)
            ->firstOrFail();

        $user = Auth::user();

        $request->validate([
            'pawapay_phone' => 'required|string|max:20',
            'pawapay_provider' => 'required|string|max:50',
            'pawapay_country' => 'required|string|size:3',
            'pawapay_currency' => 'required|string|size:3',
        ], [
            'pawapay_phone.required' => 'Le numéro de téléphone mobile money est obligatoire.',
            'pawapay_provider.required' => 'Le fournisseur mobile money est obligatoire.',
            'pawapay_country.required' => 'Le pays est obligatoire.',
            'pawapay_currency.required' => 'La devise est obligatoire.',
        ]);

        // Mettre à jour les champs pawaPay
        $user->update([
            'pawapay_phone' => $request->pawapay_phone,
            'pawapay_provider' => $request->pawapay_provider,
            'pawapay_country' => $request->pawapay_country,
            'pawapay_currency' => $request->pawapay_currency,
        ]);

        return redirect()->route('ambassador.payment-settings')
            ->with('success', 'Vos paramètres de paiement ont été mis à jour avec succès.');
    }
}
