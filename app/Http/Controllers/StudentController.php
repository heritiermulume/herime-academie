<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseDownload;
use App\Models\Enrollment;
use App\Models\Order;
use Illuminate\Http\Request;

// Classe pour représenter un cours acheté mais non inscrit
class PurchasedCourseEnrollment
{
    public $id;
    public $course;
    public $order;
    public $status;
    public $progress;
    public $is_purchased_not_enrolled;
    public $updated_at;

    public function __construct($course, $order)
    {
        $this->id = null;
        $this->course = $course;
        $this->order = $order;
        $this->status = 'purchased';
        $this->progress = 0;
        $this->is_purchased_not_enrolled = true;
        $this->updated_at = $order->created_at ?? now();
    }

    public function getKey()
    {
        return 'purchased_' . ($this->course->id ?? '0');
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function getAttribute($key)
    {
        return $this->$key ?? null;
    }
}

class StudentController extends Controller
{
    public function dashboard()
    {
        $student = auth()->user();

        $recentEnrollments = $student->enrollments()
            ->whereHas('course', function($q) {
                $q->where('is_published', true);
            })
            ->with(['course.instructor', 'course.category', 'order', 'course.downloads'])
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $allEnrollments = $student->enrollments()
            ->whereHas('course', function($q) {
                $q->where('is_published', true);
            })
            ->with(['course'])
            ->get();

        $totalCertificates = $student->certificates()
            ->whereHas('course', function($q) {
                $q->where('is_published', true);
            })
            ->count();

        // Charger les compteurs de téléchargements pour chaque cours récent
        foreach ($recentEnrollments as $enrollment) {
            $course = $enrollment->course;
            if ($course && $course->is_downloadable) {
                $course->user_downloads_count = CourseDownload::where('course_id', $course->id)
                    ->where('user_id', $student->id)
                    ->count() ?? 0;
                $course->total_downloads_count = CourseDownload::where('course_id', $course->id)->count() ?? 0;
            }
        }

        $certificates = $student->certificates()
            ->whereHas('course', function($q) {
                $q->where('is_published', true);
            })
            ->with(['course'])
            ->latest()
            ->limit(4)
            ->get();

        $recentOrders = $student->orders()
            ->with(['enrollments' => function($q) {
                $q->whereHas('course', function($q2) {
                    $q2->where('is_published', true);
                });
            }, 'enrollments.course'])
            ->latest()
            ->limit(4)
            ->get();

        $recommendedCourses = Course::published()
            ->with(['instructor', 'category'])
            ->whereNotIn('id', $allEnrollments->pluck('course_id')->filter()->all())
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'total_courses' => $allEnrollments->count(),
            'active_courses' => $allEnrollments->where('status', 'active')->count(),
            'completed_courses' => $allEnrollments->where('status', 'completed')->count(),
            'certificates_earned' => $totalCertificates,
            'average_progress' => $allEnrollments->count() > 0 ? round($allEnrollments->avg('progress'), 1) : 0,
            'learning_minutes' => $allEnrollments->sum(function ($enrollment) {
                return $enrollment->course ? $enrollment->course->duration : 0;
            }),
        ];

        return view('students.dashboard', [
            'user' => $student,
            'enrollments' => $recentEnrollments,
            'certificates' => $certificates,
            'stats' => $stats,
            'orders' => $recentOrders,
            'recommendedCourses' => $recommendedCourses,
            'lastUpdatedEnrollment' => $recentEnrollments->first(),
        ]);
    }

    public function courses(Request $request)
    {
        $student = auth()->user();

        $statusFilter = $request->get('status', 'all');
        $search = $request->get('q');

        // Récupérer les enrollments existants (seulement pour les cours publiés)
        $baseQuery = $student->enrollments()
            ->whereHas('course', function($q) {
                $q->where('is_published', true);
            })
            ->with(['course.instructor', 'course.category', 'order', 'course.downloads'])
            ->orderByDesc('updated_at');

        if ($statusFilter !== 'all') {
            $baseQuery->where('status', $statusFilter);
        }

        if (!empty($search)) {
            $baseQuery->whereHas('course', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            });
        }

        $enrollments = $baseQuery->get();

        // Récupérer les cours achetés mais non inscrits (seulement si le filtre le permet)
        $purchasedButNotEnrolled = collect();
        
        // Inclure les cours achetés mais non inscrits seulement si le filtre est 'all' ou si on cherche spécifiquement
        if ($statusFilter === 'all' || empty($statusFilter)) {
            $purchasedCourseIds = $enrollments->pluck('course_id')->filter()->all();
            
            $purchasedButNotEnrolled = Order::where('user_id', $student->id)
                ->whereIn('status', ['paid', 'completed'])
                ->whereHas('orderItems', function ($query) use ($purchasedCourseIds) {
                    $query->whereNotIn('course_id', $purchasedCourseIds)
                        ->whereHas('course', function($q) {
                            $q->where('is_published', true);
                        });
                })
                ->with(['orderItems.course' => function($q) {
                    $q->where('is_published', true);
                }, 'orderItems.course.instructor', 'orderItems.course.category', 'orderItems.course.downloads'])
                ->get()
                ->flatMap(function ($order) {
                    return $order->orderItems->filter(function($item) {
                        return $item->course && $item->course->is_published;
                    })->map(function ($item) use ($order) {
                        // Créer un objet PurchasedCourseEnrollment pour la compatibilité avec la vue
                        return new PurchasedCourseEnrollment($item->course, $order);
                    });
                })
                ->filter(function ($fakeEnrollment) use ($search) {
                    // Filtrer par recherche si nécessaire
                    if (!empty($search)) {
                        $course = $fakeEnrollment->course;
                        if (!$course) return false;
                        return stripos($course->title, $search) !== false;
                    }
                    return true;
                });
        }

        // Combiner les enrollments et les cours achetés mais non inscrits
        // Utiliser collect() pour créer une collection normale (pas Eloquent Collection)
        $allCourses = collect($enrollments->all())->merge($purchasedButNotEnrolled);

        // Trier par date de mise à jour (enrollments) ou date de commande (purchased)
        $allCourses = $allCourses->sortByDesc(function ($item) {
            if (isset($item->is_purchased_not_enrolled) && $item->is_purchased_not_enrolled) {
                return isset($item->order) ? $item->order->created_at ?? now() : now();
            }
            return $item->updated_at ?? now();
        })->values();

        // Pagination manuelle
        $currentPage = $request->get('page', 1);
        $perPage = 12;
        $total = $allCourses->count();
        $items = $allCourses->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // Créer une pagination personnalisée avec une collection normale
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->all(), // Convertir en tableau pour éviter les problèmes de collection
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Ajouter les statistiques de téléchargement
        foreach ($paginator as $item) {
            $course = $item->course;
            if ($course && $course->is_downloadable) {
                $course->user_downloads_count = CourseDownload::where('course_id', $course->id)
                    ->where('user_id', $student->id)
                    ->count();
                $course->total_downloads_count = CourseDownload::where('course_id', $course->id)->count();
            }
        }

        // Calculer les statistiques AVANT les filtres pour avoir les totaux réels
        // Compter tous les enrollments par statut (cours inscrits) - sans filtres
        $allEnrollmentsForStats = $student->enrollments()
            ->whereHas('course', function($q) {
                $q->where('is_published', true);
            })
            ->get();
        
        $enrollmentCounts = $allEnrollmentsForStats
            ->groupBy('status')
            ->map(function($group) {
                return $group->count();
            });

        // Compter tous les cours achetés mais non inscrits - sans filtres
        $allEnrolledCourseIds = $allEnrollmentsForStats->pluck('course_id')->filter()->all();
        $purchasedButNotEnrolledCount = Order::where('user_id', $student->id)
            ->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', function ($query) use ($allEnrolledCourseIds) {
                $query->whereNotIn('course_id', $allEnrolledCourseIds)
                    ->whereHas('course', function($q) {
                        $q->where('is_published', true);
                    });
            })
            ->with(['orderItems.course'])
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems->filter(function($item) {
                    return $item->course && $item->course->is_published;
                });
            })
            ->unique('course_id')
            ->count();

        // Total de tous les cours (inscrits + achetés mais non inscrits)
        $totalEnrolled = $allEnrollmentsForStats->count();
        $totalAll = $totalEnrolled + $purchasedButNotEnrolledCount;

        $courseSummary = [
            'total' => $totalAll, // Total de tous les cours
            'total_enrolled' => $totalEnrolled, // Total des cours inscrits
            'total_purchased_not_enrolled' => $purchasedButNotEnrolledCount, // Total des cours achetés mais non inscrits
            'active' => $enrollmentCounts->get('active', 0),
            'completed' => $enrollmentCounts->get('completed', 0),
            'suspended' => $enrollmentCounts->get('suspended', 0),
            'cancelled' => $enrollmentCounts->get('cancelled', 0),
        ];

        return view('students.courses', [
            'user' => $student,
            'enrollments' => $paginator,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'courseSummary' => $courseSummary,
        ]);
    }

    public function enroll(Request $request, Course $course)
    {
        // Vérifier que le cours est publié
        if (!$course->is_published) {
            return redirect()->route('courses.index')
                ->with('error', 'Ce cours n\'est pas disponible.');
        }

        $student = auth()->user();
        $redirectTo = $request->input('redirect_to', 'learn');
        
        // Pour les cours téléchargeables, ne pas rediriger vers learning, mais vers la page du cours
        if ($course->is_downloadable && $redirectTo === 'learn') {
            $redirectTo = 'course';
        }
        
        // Vérifier si l'étudiant n'est pas déjà inscrit
        if ($course->isEnrolledBy($student->id)) {
            return $this->redirectAfterEnrollment(
                $course,
                $redirectTo,
                'Vous êtes déjà inscrit à ce cours.',
                'info'
            );
        }

        // Vérifier si la vente/inscription est activée
        if (!$course->is_sale_enabled) {
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'Ce cours n\'est pas actuellement disponible à l\'inscription.');
        }

        // Pour les cours payants, vérifier que l'utilisateur a acheté le cours
        if (!$course->is_free) {
            $hasPurchased = Order::where('user_id', $student->id)
                ->where('status', 'paid')
                ->whereHas('orderItems', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->exists();

            if (!$hasPurchased) {
                return redirect()->route('courses.show', $course->slug)
                    ->with('error', 'Veuillez acheter ce cours pour y accéder.');
            }
        }

        // Récupérer l'order_id si le cours a été acheté
        $orderId = null;
        if (!$course->is_free) {
            $order = Order::where('user_id', $student->id)
                ->whereIn('status', ['paid', 'completed'])
                ->whereHas('orderItems', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->first();
            
            if ($order) {
                $orderId = $order->id;
            }
        }

        // Créer l'inscription si le cours est gratuit ou si l'achat est confirmé
        // La méthode createAndNotify envoie automatiquement les notifications et emails
        $enrollment = Enrollment::createAndNotify([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'order_id' => $orderId,
            'status' => 'active',
        ]);

        $successMessage = $course->is_downloadable 
            ? 'Inscription réussie ! Vous pouvez maintenant télécharger le cours.'
            : 'Inscription réussie ! Vous pouvez commencer à apprendre.';

        return $this->redirectAfterEnrollment($course, $redirectTo, $successMessage);
    }

    protected function redirectAfterEnrollment(Course $course, string $redirectTo, string $message, string $flashType = 'success')
    {
        $route = match ($redirectTo) {
            'download' => ['name' => 'courses.download', 'params' => ['course' => $course->slug]],
            'dashboard' => ['name' => 'student.courses', 'params' => []],
            'course' => ['name' => 'courses.show', 'params' => ['course' => $course->slug]],
            default => ['name' => 'learning.course', 'params' => ['course' => $course->slug]],
        };

        return redirect()->route($route['name'], $route['params'])->with($flashType, $message);
    }
    public function certificates()
    {
        $student = auth()->user();

        $certificateBase = $student->certificates()
            ->whereHas('course', function($q) {
                $q->where('is_published', true);
            });
        $certificatesQuery = (clone $certificateBase)
            ->with(['course.instructor'])
            ->latest('issued_at');

        $certificates = $certificatesQuery->paginate(12);

        return view('students.certificates', [
            'user' => $student,
            'certificates' => $certificates,
            'certificateSummary' => [
                'total' => (clone $certificateBase)->count(),
                'recent' => (clone $certificateBase)->with('course')->latest('issued_at')->first(),
                'issued_this_year' => (clone $certificateBase)->whereYear('issued_at', now()->year)->count(),
            ],
        ]);
    }

    /**
     * Télécharger un certificat
     */
    public function downloadCertificate(Certificate $certificate)
    {
        // Vérifier que l'utilisateur est propriétaire du certificat
        if ($certificate->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à télécharger ce certificat.');
        }

        if (!$certificate->file_path) {
            abort(404, 'Le fichier du certificat n\'existe pas.');
        }

        $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($certificate->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'Le fichier du certificat n\'existe pas sur le serveur.');
        }

        return response()->download($filePath, 'certificat-' . $certificate->certificate_number . '.pdf');
    }
}
