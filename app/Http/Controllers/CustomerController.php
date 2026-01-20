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

class CustomerController extends Controller
{
    public function dashboard()
    {
        $customer = auth()->user();

        $recentEnrollments = $customer->enrollments()
            ->whereHas('content', function($q) {
                $q->where('is_published', true);
            })
            // Ne jamais afficher les inscriptions annulées/révoquées dans l'espace client
            ->where('status', '!=', 'cancelled')
            ->with(['course.provider', 'course.category', 'order', 'course.downloads'])
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $allEnrollments = $customer->enrollments()
            ->whereHas('content', function($q) {
                $q->where('is_published', true);
            })
            ->where('status', '!=', 'cancelled')
            ->with(['course'])
            ->get();

        // Pour les contenus téléchargeables gratuits, ne les afficher dans l'espace client
        // que s'ils ont été téléchargés au moins une fois par l'utilisateur.
        $downloadedFreeCourseIds = CourseDownload::where('user_id', $customer->id)
            ->whereHas('content', function($q) {
                $q->where('is_downloadable', true)
                  ->where('is_free', true)
                  ->where('is_published', true);
            })
            ->pluck('content_id')
            ->unique()
            ->values();

        $recentEnrollments = $recentEnrollments->filter(function ($enrollment) use ($downloadedFreeCourseIds) {
            $course = $enrollment->course;
            if (! $course) {
                return false;
            }

            if ($course->is_downloadable && $course->is_free) {
                // Garder uniquement si le cours a été téléchargé au moins une fois
                return $downloadedFreeCourseIds->contains($course->id);
            }

            return true;
        })->values();

        $allEnrollments = $allEnrollments->filter(function ($enrollment) use ($downloadedFreeCourseIds) {
            $course = $enrollment->course;
            if (! $course) {
                return false;
            }

            if ($course->is_downloadable && $course->is_free) {
                return $downloadedFreeCourseIds->contains($course->id);
            }

            return true;
        })->values();

        $totalCertificates = $customer->certificates()
            ->whereHas('content', function($q) {
                $q->where('is_published', true);
            })
            ->count();

        // Charger les compteurs de téléchargements pour chaque cours récent
        foreach ($recentEnrollments as $enrollment) {
            $course = $enrollment->course;
            if ($course && $course->is_downloadable) {
                $course->user_downloads_count = CourseDownload::where('content_id', $course->id)
                    ->where('user_id', $customer->id)
                    ->count() ?? 0;
                $course->total_downloads_count = CourseDownload::where('content_id', $course->id)->count() ?? 0;
            }
        }

        $certificates = $customer->certificates()
            ->whereHas('content', function($q) {
                $q->where('is_published', true);
            })
            ->with(['course'])
            ->latest()
            ->limit(4)
            ->get();

        $recentOrders = $customer->orders()
            ->with(['enrollments' => function($q) {
                $q->whereHas('content', function($q2) {
                    $q2->where('is_published', true);
                });
            }, 'enrollments.course'])
            ->latest()
            ->limit(4)
            ->get();

        $recommendedCourses = Course::published()
            ->with(['provider', 'category'])
            ->whereNotIn('id', $allEnrollments->pluck('content_id')->filter()->all())
            ->latest()
            ->limit(5)
            ->get();

        // Calculer les cours achetés (via les commandes payées)
        $purchasedCourses = Order::where('user_id', $customer->id)
            ->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', function($q) {
                $q->whereHas('content', function($q2) {
                    $q2->where('is_published', true);
                });
            })
            ->with(['orderItems.course'])
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems->filter(function($item) {
                    return $item->course && $item->course->is_published;
                })->pluck('content_id');
            })
            ->unique()
            ->count();

        // Calculer les contenus téléchargeables achetés
        $purchasedDownloadableCourses = Order::where('user_id', $customer->id)
            ->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', function($q) {
                $q->whereHas('content', function($q2) {
                    $q2->where('is_published', true)
                       ->where('is_downloadable', true);
                });
            })
            ->with(['orderItems.course'])
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems->filter(function($item) {
                    return $item->course && $item->course->is_published && $item->course->is_downloadable;
                })->pluck('content_id');
            })
            ->unique()
            ->count();

        $stats = [
            'total_courses' => $allEnrollments->count(),
            'enrolled_courses' => $allEnrollments->count(), // Cours inscrits
            'purchased_courses' => $purchasedCourses, // Cours achetés
            'purchased_downloadable_courses' => $purchasedDownloadableCourses, // Contenus téléchargeables achetés
            'active_courses' => $allEnrollments->where('status', 'active')->count(),
            'completed_courses' => $allEnrollments->where('status', 'completed')->count(),
            'certificates_earned' => $totalCertificates,
            'average_progress' => $allEnrollments->count() > 0 ? round($allEnrollments->avg('progress'), 1) : 0,
            'learning_minutes' => $allEnrollments->sum(function ($enrollment) {
                return $enrollment->course ? $enrollment->course->duration : 0;
            }),
        ];

        return view('customers.dashboard', [
            'user' => $customer,
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
        $customer = auth()->user();

        $statusFilter = $request->get('status', 'all');
        $search = $request->get('q');

        // Récupérer les enrollments existants (seulement pour les cours publiés)
        $baseQuery = $customer->enrollments()
            ->whereHas('content', function($q) {
                $q->where('is_published', true);
            })
            // Ne jamais remontrer les inscriptions annulées/révoquées dans l'espace client,
            // même si le filtre de statut est sur "Annulé".
            ->where('status', '!=', 'cancelled')
            ->with(['course.provider', 'course.category', 'order', 'course.downloads'])
            ->orderByDesc('updated_at');

        if ($statusFilter !== 'all') {
            $baseQuery->where('status', $statusFilter);
        }

        if (!empty($search)) {
            $baseQuery->whereHas('content', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            });
        }

        $enrollments = $baseQuery->get();

        // Identifier les cours téléchargeables gratuits déjà téléchargés par l'utilisateur
        $downloadedFreeCourseIds = CourseDownload::where('user_id', $customer->id)
            ->whereHas('content', function($q) {
                $q->where('is_downloadable', true)
                  ->where('is_free', true)
                  ->where('is_published', true);
            })
            ->pluck('content_id')
            ->unique()
            ->values();

        // Filtrer les inscriptions : un cours téléchargeable gratuit n'apparaît
        // que s'il a été téléchargé au moins une fois
        $enrollments = $enrollments->filter(function ($enrollment) use ($downloadedFreeCourseIds) {
            $course = $enrollment->course;
            if (! $course) {
                return false;
            }

            if ($course->is_downloadable && $course->is_free) {
                return $downloadedFreeCourseIds->contains($course->id);
            }

            return true;
        })->values();

        // Récupérer les cours achetés mais non inscrits (seulement si le filtre le permet)
        $purchasedButNotEnrolled = collect();
        
        // Inclure les cours achetés mais non inscrits seulement si le filtre est 'all' ou si on cherche spécifiquement
        if ($statusFilter === 'all' || empty($statusFilter)) {
            // Tenir compte de toutes les inscriptions (y compris annulées) pour éviter
            // de ré-afficher un cours dont l'accès a été explicitement révoqué.
            $allEnrollmentCourseIds = $customer->enrollments()
                ->whereHas('content', function($q) {
                    $q->where('is_published', true);
                })
                ->pluck('content_id')
                ->filter()
                ->all();
            
            $purchasedButNotEnrolled = Order::where('user_id', $customer->id)
                ->whereIn('status', ['paid', 'completed'])
                ->whereHas('orderItems', function ($query) use ($allEnrollmentCourseIds) {
                    $query->whereNotIn('content_id', $allEnrollmentCourseIds)
                        ->whereHas('content', function($q) {
                            $q->where('is_published', true);
                        });
                })
                ->with(['orderItems.course' => function($q) {
                    $q->where('is_published', true);
                }, 'orderItems.course.provider', 'orderItems.course.category', 'orderItems.course.downloads'])
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
                $course->user_downloads_count = CourseDownload::where('content_id', $course->id)
                    ->where('user_id', $customer->id)
                    ->count();
                $course->total_downloads_count = CourseDownload::where('content_id', $course->id)->count();
            }
        }

        // Calculer les statistiques AVANT les filtres pour avoir les totaux réels
        // Compter tous les enrollments par statut (cours inscrits) - sans filtres,
        // en appliquant aussi la règle sur les cours téléchargeables gratuits.
        $allEnrollmentsForStats = $customer->enrollments()
            ->whereHas('content', function($q) {
                $q->where('is_published', true);
            })
            // Ne pas compter les inscriptions annulées/révoquées dans les statistiques
            ->where('status', '!=', 'cancelled')
            ->with('course')
            ->get()
            ->filter(function ($enrollment) use ($downloadedFreeCourseIds) {
                $course = $enrollment->course;
                if (! $course) {
                    return false;
                }

                if ($course->is_downloadable && $course->is_free) {
                    return $downloadedFreeCourseIds->contains($course->id);
                }

                return true;
            })
            ->values();
        
        $enrollmentCounts = $allEnrollmentsForStats
            ->groupBy('status')
            ->map(function($group) {
                return $group->count();
            });

        // Compter tous les cours achetés mais non inscrits - sans filtres
        $allEnrolledCourseIds = $allEnrollmentsForStats->pluck('content_id')->filter()->all();
        $purchasedButNotEnrolledCount = Order::where('user_id', $customer->id)
            ->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', function ($query) use ($allEnrolledCourseIds) {
                $query->whereNotIn('content_id', $allEnrolledCourseIds)
                    ->whereHas('content', function($q) {
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
            ->unique('content_id')
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

        return view('customers.contents', [
            'user' => $customer,
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
            return redirect()->route('contents.index')
                ->with('error', 'Ce cours n\'est pas disponible.');
        }

        $customer = auth()->user();
        $redirectTo = $request->input('redirect_to', 'learn');
        
        // Pour les cours téléchargeables, ne pas rediriger vers learning, mais vers la page du cours
        if ($course->is_downloadable && $redirectTo === 'learn') {
            $redirectTo = 'course';
        }
        
        // Vérifier si l'étudiant n'est pas déjà inscrit
        if ($course->isEnrolledBy($customer->id)) {
            return $this->redirectAfterEnrollment(
                $course,
                $redirectTo,
                'Vous êtes déjà inscrit à ce cours.',
                'info'
            );
        }

        // Vérifier si la vente/inscription est activée
        if (!$course->is_sale_enabled) {
            return redirect()->route('contents.show', $course->slug)
                ->with('error', 'Ce cours n\'est pas actuellement disponible à l\'inscription.');
        }

        // Pour les cours payants, vérifier que l'utilisateur a acheté le cours
        if (!$course->is_free) {
            $hasPurchased = Order::where('user_id', $customer->id)
                ->where('status', 'paid')
                ->whereHas('orderItems', function ($query) use ($course) {
                    $query->where('content_id', $course->id);
                })
                ->exists();

            if (!$hasPurchased) {
                return redirect()->route('contents.show', $course->slug)
                    ->with('error', 'Veuillez acheter ce cours pour y accéder.');
            }
        }

        // Récupérer l'order_id si le cours a été acheté
        $orderId = null;
        if (!$course->is_free) {
            $order = Order::where('user_id', $customer->id)
                ->whereIn('status', ['paid', 'completed'])
                ->whereHas('orderItems', function ($query) use ($course) {
                    $query->where('content_id', $course->id);
                })
                ->first();
            
            if ($order) {
                $orderId = $order->id;
            }
        }

        // Créer l'inscription si le cours est gratuit ou si l'achat est confirmé
        // La méthode createAndNotify envoie automatiquement les notifications et emails
        $enrollment = Enrollment::createAndNotify([
            'user_id' => $customer->id,
            'content_id' => $course->id,
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
            'download' => ['name' => 'contents.download', 'params' => ['course' => $course->slug]],
            'dashboard' => ['name' => 'customer.contents', 'params' => []],
            'course' => ['name' => 'contents.show', 'params' => ['course' => $course->slug]],
            default => ['name' => 'learning.course', 'params' => ['course' => $course->slug]],
        };

        return redirect()->route($route['name'], $route['params'])->with($flashType, $message);
    }
    public function certificates()
    {
        $customer = auth()->user();

        $certificateBase = $customer->certificates()
            ->whereHas('content', function($q) {
                $q->where('is_published', true);
            });
        $certificatesQuery = (clone $certificateBase)
            ->with(['course.provider'])
            ->latest('issued_at');

        $certificates = $certificatesQuery->paginate(12);

        return view('customers.certificates', [
            'user' => $customer,
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
