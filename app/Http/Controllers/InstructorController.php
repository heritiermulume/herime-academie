<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Traits\DatabaseCompatibility;
use App\Traits\CourseStatistics;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
}
