<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Traits\DatabaseCompatibility;
use App\Traits\CourseStatistics;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProviderController extends Controller
{
    use DatabaseCompatibility, CourseStatistics;
    public function index()
    {
        // Rediriger vers la page de candidature pour devenir prestataire
        return redirect()->route('provider-application.index');
    }

    public function show(User $provider)
    {
        $provider->loadCount('courses');
        
        $courses = Course::published()
            ->where('provider_id', $provider->id)
            ->with(['category', 'reviews', 'enrollments', 'sections.lessons'])
            ->latest()
            ->paginate(9);

        // Ajouter les statistiques à chaque cours
        $courses->getCollection()->transform(function($course) {
            $course->stats = $course->getCourseStats();
            return $course;
        });

        return view('providers.show', compact('provider', 'courses'));
    }

    public function dashboard()
    {
        $provider = auth()->user();

        $totalCourses = $provider->contents()->count();
        $publishedCourses = $provider->contents()->published()->count();
        $draftCourses = $totalCourses - $publishedCourses;

        $newCoursesCurrent = $provider->contents()
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $newCoursesPrevious = $provider->contents()
            ->whereBetween('created_at', [Carbon::now()->subDays(60), Carbon::now()->subDays(30)])
            ->count();

        $enrollmentsCurrent = $this->enrollmentsCountForPeriod($provider, 30, 0);
        $enrollmentsPrevious = $this->enrollmentsCountForPeriod($provider, 60, 30);

        $revenueCurrent = $this->revenueForPeriod($provider, 30, 0);
        $revenuePrevious = $this->revenueForPeriod($provider, 60, 30);

        $coursesForRatings = $provider->contents()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->get();

        $averageRating = $coursesForRatings->avg('reviews_avg_rating') ?? 0;
        $totalReviews = $coursesForRatings->sum('reviews_count');

        $metrics = [
            [
                'label' => 'Contenus actifs',
                'icon' => 'fas fa-layer-group',
                'value' => $publishedCourses,
                'trend' => $this->percentTrend($newCoursesCurrent, $newCoursesPrevious),
                'accent' => '#0ea5e9',
            ],
            [
                'label' => 'Inscriptions (30 j)',
                'icon' => 'fas fa-user-graduate',
                'value' => number_format($enrollmentsCurrent),
                'trend' => $this->percentTrend($enrollmentsCurrent, $enrollmentsPrevious),
                'accent' => '#22c55e',
            ],
            [
                'label' => 'Satisfaction',
                'icon' => 'fas fa-star',
                'value' => number_format($averageRating, 1) . '/5',
                'trend' => $this->percentTrend($totalReviews, max($totalReviews - 5, 1)),
                'accent' => '#f59e0b',
            ],
            [
                'label' => 'Revenus estimés',
                'icon' => 'fas fa-coins',
                'value' => $this->formatCurrency($revenueCurrent),
                'trend' => $this->percentTrend($revenueCurrent, $revenuePrevious),
                'accent' => '#6366f1',
            ],
        ];

        $recentCourses = $provider->contents()
            ->with('category')
            ->latest()
            ->limit(5)
            ->get();

        $recentEnrollments = Enrollment::whereHas('content', function ($query) use ($provider) {
                $query->where('provider_id', $provider->id);
            })
            ->with(['user', 'content'])
            ->latest()
            ->limit(7)
            ->get();

        $pendingTasks = [];
        if ($draftCourses > 0) {
            $pendingTasks[] = [
                'title' => 'Publier vos brouillons',
                'description' => "{$draftCourses} contenus sont encore en mode brouillon.",
                'type' => 'alert',
            ];
        }
        if ($averageRating < 4 && $totalReviews > 0) {
            $pendingTasks[] = [
                'title' => 'Améliorer la satisfaction',
                'description' => 'Répondez aux derniers avis et ajoutez un module bonus pour fidéliser vos clients.',
                'type' => 'info',
            ];
        }
        if ($enrollmentsCurrent > $enrollmentsPrevious) {
            $pendingTasks[] = [
                'title' => 'Poursuivre votre momentum',
                'description' => 'Vos inscriptions progressent, programmez un live ou un webinaire pour maintenir la dynamique.',
                'type' => 'success',
            ];
        }

        return view('providers.admin.dashboard', [
            'metrics' => $metrics,
            'recentCourses' => $recentCourses,
            'recentEnrollments' => $recentEnrollments,
            'pendingTasks' => $pendingTasks,
        ]);
    }

    public function customers()
    {
        $provider = auth()->user();
        
        $enrollmentsQuery = Enrollment::whereHas('content', function($query) use ($provider) {
            $query->where('provider_id', $provider->id);
        })
        ->with(['user', 'content'])
        ->latest();

        $enrollments = $enrollmentsQuery->paginate(20);

        $averageProgress = (float) (clone $enrollmentsQuery)->avg('progress');
        $activeCustomers = (clone $enrollmentsQuery)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->distinct('user_id')
            ->count('user_id');

        return view('providers.admin.customers', [
            'enrollments' => $enrollments,
            'averageProgress' => $averageProgress,
            'activeCustomers' => $activeCustomers,
        ]);
    }

    public function analytics()
    {
        $provider = auth()->user();
        
        // Statistiques des contenus
        $courseStats = $provider->contents()
            ->selectRaw('
                COUNT(*) as total_courses,
                SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) as published_courses
            ')
            ->first();

        if (!$courseStats) {
            $courseStats = (object) [
                'total_courses' => 0,
                'published_courses' => 0,
            ];
        }
            
        // Calculer les statistiques dynamiquement
        $totalCustomers = $provider->contents()->withCount('enrollments')->get()->sum('enrollments_count');
        $averageRating = $provider->contents()->withAvg('reviews', 'rating')->get()->avg('reviews_avg_rating') ?? 0;
        
        $courseStats->total_customers = $totalCustomers;
        $courseStats->average_rating = $averageRating;

        // Contenus les plus populaires
        $popularCourses = $provider->contents()
            ->published()
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get();

        // Évolution des inscriptions par mois
        $enrollmentsByMonth = Enrollment::whereHas('content', function($query) use ($provider) {
            $query->where('provider_id', $provider->id);
        })
        ->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m', 'month') . ', COUNT(*) as count')                                                       
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $enrollmentsByMonth->transform(function ($row) {
            $row->formatted_month = Carbon::createFromFormat('Y-m', $row->month)->translatedFormat('M Y');
            return $row;
        });

        $totalReviews = $provider->contents()->withCount('reviews')->get()->sum('reviews_count');
        $estimatedRevenue = $this->formatCurrency($this->revenueForPeriod($provider, 30, 0));

        $insights = [];
        if (($courseStats->average_rating ?? 0) < 4 && $totalReviews > 0) {
            $insights[] = [
                'type' => 'alert',
                'title' => 'Votre note moyenne est inférieure à 4/5',
                'description' => 'Analysez les retours clients et mettez en avant un module bonus pour remonter la note.',
            ];
        }
        if (($courseStats->total_courses ?? 0) < 3) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Diversifiez votre catalogue',
                'description' => 'Plusieurs contenus sur des niveaux différents augmentent vos revenus et fidélisent vos clients.',
            ];
        }
        if (($courseStats->total_customers ?? 0) > 0 && $enrollmentsByMonth->isNotEmpty()) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Votre communauté progresse',
                'description' => 'Planifiez un live hebdomadaire pour renforcer l’engagement de vos clients actifs.',
            ];
        }

        return view('providers.admin.analytics', [
            'courseStats' => $courseStats,
            'popularCourses' => $popularCourses,
            'enrollmentsByMonth' => $enrollmentsByMonth,
            'totalReviews' => $totalReviews,
            'estimatedRevenue' => $estimatedRevenue,
            'insights' => $insights,
        ]);
    }

    public function coursesIndex(Request $request)
    {
        $provider = auth()->user();

        $status = $request->get('status');

        $coursesQuery = $provider->contents()
            ->with(['category'])
            ->withCount(['enrollments', 'reviews'])
            ->withAvg('reviews', 'rating');

        if ($status === 'published') {
            $coursesQuery->where('is_published', true);
        } elseif ($status === 'draft') {
            $coursesQuery->where('is_published', false);
        }

        $courses = $coursesQuery
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $baseCurrency = \App\Models\Setting::getBaseCurrency();

        return view('providers.admin.contents', [
            'courses' => $courses,
            'status' => $status,
            'baseCurrency' => $baseCurrency,
        ]);
    }

    public function lessons(Course $course)
    {
        $this->ensureProviderCanManage($course);

        $course->load([
            'category',
            'sections' => function ($query) {
                $query->orderBy('sort_order')
                    ->with(['lessons' => function ($lessonQuery) {
                        $lessonQuery->orderBy('sort_order');
                    }]);
            },
        ]);

        $totalLessons = $course->sections->sum(function ($section) {
            return $section->lessons->count();
        });

        $totalDuration = $course->sections->sum(function ($section) {
            return $section->lessons->sum('duration');
        });

        return view('providers.admin.lessons', [
            'course' => $course,
            'sections' => $course->sections,
            'totalLessons' => $totalLessons,
            'totalDuration' => $totalDuration,
        ]);
    }

    private function percentTrend($current, $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return (($current - $previous) / $previous) * 100;
    }

    private function enrollmentsCountForPeriod($provider, int $daysBack, int $offsetDays): int
    {
        return Enrollment::whereHas('content', function ($query) use ($provider) {
                $query->where('provider_id', $provider->id);
            })
            ->when($offsetDays === 0, function ($query) use ($daysBack) {
                $query->where('created_at', '>=', Carbon::now()->subDays($daysBack));
            })
            ->when($offsetDays > 0, function ($query) use ($daysBack, $offsetDays) {
                $query->whereBetween('created_at', [Carbon::now()->subDays($offsetDays + $daysBack), Carbon::now()->subDays($offsetDays)]);
            })
            ->count();
    }

    private function revenueForPeriod($provider, int $daysBack, int $offsetDays): float
    {
        // TODO: remplacer par la somme réelle des revenus lorsque les paiements prestataires seront disponibles.
        return 0.0;
    }

    private function formatCurrency($amount): string
    {
        if (class_exists('\\App\\Helpers\\CurrencyHelper')) {
            return \App\Helpers\CurrencyHelper::formatWithSymbol($amount);
        }

        return number_format($amount, 2, ',', ' ') . ' €';
    }

    private function ensureProviderCanManage(Course $course): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if ($user->isAdmin() || ($user->isProvider() && (int) $course->provider_id === (int) $user->id)) {
            return;
        }

        abort(403);
    }

    /**
     * Afficher la page de configuration de moyen de règlement
     */
    public function paymentSettings()
    {
        $provider = auth()->user();
        
        // Récupérer les données Moneroo
        $monerooData = $this->getMonerooConfiguration();
        
        return view('providers.admin.payment-settings', [
            'provider' => $provider,
            'monerooData' => $monerooData,
            'pawapayData' => $monerooData, // Compatibilité avec les vues existantes
        ]);
    }

    /**
     * Mettre à jour la configuration de moyen de règlement
     */
    public function updatePaymentSettings(Request $request)
    {
        $provider = auth()->user();
        
        $request->validate([
            'is_external_provider' => 'boolean',
            'pawapay_phone' => 'nullable|string|max:20',
            'pawapay_provider' => 'nullable|string|max:50',
            'pawapay_country' => 'nullable|string|size:3',
            'pawapay_currency' => 'nullable|string|size:3',
        ]);

        // Mettre à jour les champs de paiement (compatibilité avec anciens champs PawaPay)
        $provider->update([
            'is_external_provider' => $request->has('is_external_provider'),
            'pawapay_phone' => $request->pawapay_phone,
            'pawapay_provider' => $request->pawapay_provider,
            'pawapay_country' => $request->pawapay_country,
            'pawapay_currency' => $request->pawapay_currency,
        ]);

        return redirect()->route('provider.payment-settings')
            ->with('success', 'Configuration de paiement mise à jour avec succès.');
    }

    /**
     * Récupérer la configuration Moneroo (pays et providers)
     */
    private function getMonerooConfiguration(): array
    {
        // Utiliser l'API Moneroo pour récupérer les méthodes disponibles
        $baseUrl = rtrim(config('services.moneroo.base_url', 'https://api.moneroo.io/v1'), '/');
        $apiKey = config('services.moneroo.api_key');
        
        if (!$apiKey) {
            Log::error('MONEROO_API_KEY non configurée.');
            return ['countries' => [], 'providers' => []];
        }

        try {
            // Utiliser l'endpoint /payouts/methods selon la documentation Moneroo
            // https://docs.moneroo.io/fr/payouts/methodes-disponibles
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get("{$baseUrl}/payouts/methods");

            if ($response->successful()) {
                $responseData = $response->json();
                // Format Moneroo: { "success": true, "data": {...} }
                $data = $responseData['data'] ?? $responseData;
                
                Log::info('Moneroo configuration retrieved', [
                    'has_data' => !empty($data),
                ]);
                
                // Extraire les pays et providers selon la structure de la réponse Moneroo
                $countries = [];
                $providers = [];
                
                // Moneroo peut retourner les méthodes différemment
                // Adapter selon le format réel de la réponse
                if (isset($data['methods']) && is_array($data['methods'])) {
                    // Si la réponse contient un tableau de méthodes
                    foreach ($data['methods'] as $method) {
                        $countryCode = $method['country'] ?? '';
                        $providerCode = $method['payment_method'] ?? $method['provider'] ?? '';
                        $providerName = $method['name'] ?? $providerCode;
                        $currencies = $method['currencies'] ?? ($method['currency'] ? [$method['currency']] : []);
                        
                        // Ajouter le pays s'il n'existe pas
                        if ($countryCode && !isset($countries[$countryCode])) {
                            $countries[$countryCode] = [
                                'code' => $countryCode,
                                'name' => $countryCode, // Peut être amélioré avec un mapping
                                'prefix' => '',
                                'flag' => '',
                            ];
                        }
                        
                        // Ajouter le provider
                        if ($providerCode) {
                            $providers[] = [
                                'code' => $providerCode,
                                'name' => $providerName,
                                'country' => $countryCode,
                                'currencies' => $currencies,
                                'logo' => $method['logo'] ?? '',
                            ];
                        }
                    }
                } elseif (isset($data['countries']) && is_array($data['countries'])) {
                    // Format similaire à PawaPay (pour compatibilité)
                    foreach ($data['countries'] as $country) {
                        $countryCode = $country['country'] ?? $country['code'] ?? '';
                        $countryName = $country['displayName']['fr'] ?? $country['displayName']['en'] ?? $country['name'] ?? $countryCode;
                        
                        $countries[] = [
                            'code' => $countryCode,
                            'name' => $countryName,
                            'prefix' => $country['prefix'] ?? '',
                            'flag' => $country['flag'] ?? '',
                        ];
                        
                        // Extraire les providers pour ce pays
                        if (isset($country['providers']) && is_array($country['providers'])) {
                            foreach ($country['providers'] as $provider) {
                                $providerCode = $provider['provider'] ?? $provider['payment_method'] ?? '';
                                $providerName = $provider['displayName'] ?? $provider['name'] ?? $providerCode;
                                $currencies = $provider['currencies'] ?? ($provider['currency'] ? [$provider['currency']] : []);
                                
                                $providers[] = [
                                    'code' => $providerCode,
                                    'name' => $providerName,
                                    'country' => $countryCode,
                                    'currencies' => $currencies,
                                    'logo' => $provider['logo'] ?? '',
                                ];
                            }
                        }
                    }
                }
                
                // Convertir le tableau associatif de pays en tableau indexé si nécessaire
                if (!empty($countries) && isset($countries[0]) && is_array($countries[0])) {
                    $countries = array_values($countries);
                } else {
                    $countries = array_values($countries);
                }
                
                // Trier les pays par nom
                usort($countries, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                
                // Trier les providers par nom
                usort($providers, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                
                Log::info('Moneroo configuration processed', [
                    'countries_count' => count($countries),
                    'providers_count' => count($providers),
                ]);
                
                return [
                    'countries' => $countries,
                    'providers' => $providers,
                ];
            } else {
                Log::warning('Échec de la récupération de la configuration Moneroo', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la configuration Moneroo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        
        return ['countries' => [], 'providers' => []];
    }
}
