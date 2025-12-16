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

class InstructorController extends Controller
{
    use DatabaseCompatibility, CourseStatistics;
    public function index()
    {
        // Rediriger vers la page de candidature pour devenir formateur
        return redirect()->route('instructor-application.index');
    }

    public function show(User $instructor)
    {
        $instructor->loadCount('courses');
        
        $courses = Course::published()
            ->where('instructor_id', $instructor->id)
            ->with(['category', 'reviews', 'enrollments', 'sections.lessons'])
            ->latest()
            ->paginate(9);

        // Ajouter les statistiques à chaque cours
        $courses->getCollection()->transform(function($course) {
            $course->stats = $course->getCourseStats();
            return $course;
        });

        return view('instructors.show', compact('instructor', 'courses'));
    }

    public function dashboard()
    {
        $instructor = auth()->user();

        $totalCourses = $instructor->courses()->count();
        $publishedCourses = $instructor->courses()->published()->count();
        $draftCourses = $totalCourses - $publishedCourses;

        $newCoursesCurrent = $instructor->courses()
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $newCoursesPrevious = $instructor->courses()
            ->whereBetween('created_at', [Carbon::now()->subDays(60), Carbon::now()->subDays(30)])
            ->count();

        $enrollmentsCurrent = $this->enrollmentsCountForPeriod($instructor, 30, 0);
        $enrollmentsPrevious = $this->enrollmentsCountForPeriod($instructor, 60, 30);

        $revenueCurrent = $this->revenueForPeriod($instructor, 30, 0);
        $revenuePrevious = $this->revenueForPeriod($instructor, 60, 30);

        $coursesForRatings = $instructor->courses()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->get();

        $averageRating = $coursesForRatings->avg('reviews_avg_rating') ?? 0;
        $totalReviews = $coursesForRatings->sum('reviews_count');

        $metrics = [
            [
                'label' => 'Cours actifs',
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

        $recentCourses = $instructor->courses()
            ->with('category')
            ->latest()
            ->limit(5)
            ->get();

        $recentEnrollments = Enrollment::whereHas('course', function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            })
            ->with(['user', 'course'])
            ->latest()
            ->limit(7)
            ->get();

        $pendingTasks = [];
        if ($draftCourses > 0) {
            $pendingTasks[] = [
                'title' => 'Publier vos brouillons',
                'description' => "{$draftCourses} cours sont encore en mode brouillon.",
                'type' => 'alert',
            ];
        }
        if ($averageRating < 4 && $totalReviews > 0) {
            $pendingTasks[] = [
                'title' => 'Améliorer la satisfaction',
                'description' => 'Répondez aux derniers avis et ajoutez un module bonus pour fidéliser vos étudiants.',
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

        return view('instructors.admin.dashboard', [
            'metrics' => $metrics,
            'recentCourses' => $recentCourses,
            'recentEnrollments' => $recentEnrollments,
            'pendingTasks' => $pendingTasks,
        ]);
    }

    public function students()
    {
        $instructor = auth()->user();
        
        $enrollmentsQuery = Enrollment::whereHas('course', function($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->with(['user', 'course'])
        ->latest();

        $enrollments = $enrollmentsQuery->paginate(20);

        $averageProgress = (float) (clone $enrollmentsQuery)->avg('progress');
        $activeStudents = (clone $enrollmentsQuery)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->distinct('user_id')
            ->count('user_id');

        return view('instructors.admin.students', [
            'enrollments' => $enrollments,
            'averageProgress' => $averageProgress,
            'activeStudents' => $activeStudents,
        ]);
    }

    public function analytics()
    {
        $instructor = auth()->user();
        
        // Statistiques des cours
        $courseStats = $instructor->courses()
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
        $totalStudents = $instructor->courses()->withCount('enrollments')->get()->sum('enrollments_count');
        $averageRating = $instructor->courses()->withAvg('reviews', 'rating')->get()->avg('reviews_avg_rating') ?? 0;
        
        $courseStats->total_students = $totalStudents;
        $courseStats->average_rating = $averageRating;

        // Cours les plus populaires
        $popularCourses = $instructor->courses()
            ->published()
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get();

        // Évolution des inscriptions par mois
        $enrollmentsByMonth = Enrollment::whereHas('course', function($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m', 'month') . ', COUNT(*) as count')                                                       
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $enrollmentsByMonth->transform(function ($row) {
            $row->formatted_month = Carbon::createFromFormat('Y-m', $row->month)->translatedFormat('M Y');
            return $row;
        });

        $totalReviews = $instructor->courses()->withCount('reviews')->get()->sum('reviews_count');
        $estimatedRevenue = $this->formatCurrency($this->revenueForPeriod($instructor, 30, 0));

        $insights = [];
        if (($courseStats->average_rating ?? 0) < 4 && $totalReviews > 0) {
            $insights[] = [
                'type' => 'alert',
                'title' => 'Votre note moyenne est inférieure à 4/5',
                'description' => 'Analysez les retours étudiants et mettez en avant un module bonus pour remonter la note.',
            ];
        }
        if (($courseStats->total_courses ?? 0) < 3) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Diversifiez votre catalogue',
                'description' => 'Plusieurs cours sur des niveaux différents augmentent vos revenus et fidélisent vos apprenants.',
            ];
        }
        if (($courseStats->total_students ?? 0) > 0 && $enrollmentsByMonth->isNotEmpty()) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Votre communauté progresse',
                'description' => 'Planifiez un live hebdomadaire pour renforcer l’engagement de vos étudiants actifs.',
            ];
        }

        return view('instructors.admin.analytics', [
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
        $instructor = auth()->user();

        $status = $request->get('status');

        $coursesQuery = $instructor->courses()
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

        return view('instructors.admin.courses', [
            'courses' => $courses,
            'status' => $status,
            'baseCurrency' => $baseCurrency,
        ]);
    }

    public function lessons(Course $course)
    {
        $this->ensureInstructorCanManage($course);

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

        return view('instructors.admin.lessons', [
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

    private function enrollmentsCountForPeriod($instructor, int $daysBack, int $offsetDays): int
    {
        return Enrollment::whereHas('course', function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            })
            ->when($offsetDays === 0, function ($query) use ($daysBack) {
                $query->where('created_at', '>=', Carbon::now()->subDays($daysBack));
            })
            ->when($offsetDays > 0, function ($query) use ($daysBack, $offsetDays) {
                $query->whereBetween('created_at', [Carbon::now()->subDays($offsetDays + $daysBack), Carbon::now()->subDays($offsetDays)]);
            })
            ->count();
    }

    private function revenueForPeriod($instructor, int $daysBack, int $offsetDays): float
    {
        // TODO: remplacer par la somme réelle des revenus lorsque les paiements formateurs seront disponibles.
        return 0.0;
    }

    private function formatCurrency($amount): string
    {
        if (class_exists('\\App\\Helpers\\CurrencyHelper')) {
            return \App\Helpers\CurrencyHelper::formatWithSymbol($amount);
        }

        return number_format($amount, 2, ',', ' ') . ' €';
    }

    private function ensureInstructorCanManage(Course $course): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if ($user->isAdmin() || ($user->isInstructor() && (int) $course->instructor_id === (int) $user->id)) {
            return;
        }

        abort(403);
    }

    /**
     * Afficher la page de configuration de moyen de règlement
     */
    public function paymentSettings()
    {
        $instructor = auth()->user();
        
        // Récupérer les données Moneroo
        $monerooData = $this->getMonerooConfiguration();
        
        return view('instructors.admin.payment-settings', [
            'instructor' => $instructor,
            'monerooData' => $monerooData,
            'pawapayData' => $monerooData, // Compatibilité avec les vues existantes
        ]);
    }

    /**
     * Mettre à jour la configuration de moyen de règlement
     */
    public function updatePaymentSettings(Request $request)
    {
        $instructor = auth()->user();
        
        $request->validate([
            'is_external_instructor' => 'boolean',
            'pawapay_phone' => 'nullable|string|max:20',
            'pawapay_provider' => 'nullable|string|max:50',
            'pawapay_country' => 'nullable|string|size:3',
            'pawapay_currency' => 'nullable|string|size:3',
        ]);

        // Mettre à jour les champs de paiement (compatibilité avec anciens champs PawaPay)
        $instructor->update([
            'is_external_instructor' => $request->has('is_external_instructor'),
            'pawapay_phone' => $request->pawapay_phone,
            'pawapay_provider' => $request->pawapay_provider,
            'pawapay_country' => $request->pawapay_country,
            'pawapay_currency' => $request->pawapay_currency,
        ]);

        return redirect()->route('instructor.payment-settings')
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
