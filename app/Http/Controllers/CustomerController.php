<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseDownload;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ContentPackage;
use App\Services\ContentPackageRecommendationService;
use Illuminate\Support\Collection;
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
        $purchasedPackagesSummaries = $this->purchasedPackagesSummaries($customer);
        $activePackCourseIds = $purchasedPackagesSummaries
            ->flatMap(fn (array $row) => $row['package']->contents->pluck('id'))
            ->filter()
            ->unique()
            ->values();

        // Liste tableau de bord : contenus hors pack + contenus inclus dans les packs (même liste)
        $dashboardRecentEnrollments = $customer->enrollments()
            ->whereHas('content', function($q) {
                $q->where('is_published', true);
            })
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

        $dashboardRecentEnrollments = $dashboardRecentEnrollments->filter(function ($enrollment) use ($downloadedFreeCourseIds) {
            $course = $enrollment->course;
            if (! $course) {
                return false;
            }

            if ($course->is_downloadable && $course->is_free) {
                return $downloadedFreeCourseIds->contains($course->id);
            }

            return true;
        })->values();

        $allEnrollments = $allEnrollments->filter(function ($enrollment) use ($downloadedFreeCourseIds, $activePackCourseIds) {
            $course = $enrollment->course;
            if (! $course) {
                return false;
            }

            if ($activePackCourseIds->contains($course->id)) {
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

        foreach ($dashboardRecentEnrollments as $enrollment) {
            $course = $enrollment->course;
            if ($course && $course->is_downloadable) {
                $course->user_downloads_count = CourseDownload::where('content_id', $course->id)
                    ->where('user_id', $customer->id)
                    ->count() ?? 0;
                $course->total_downloads_count = CourseDownload::where('content_id', $course->id)->count() ?? 0;
            }
        }

        // « Reprendre » : uniquement cours non téléchargeable, hors pack, avec au moins une leçon entamée
        $courseIdsWithLessonActivity = LessonProgress::query()
            ->where('user_id', $customer->id)
            ->where(function ($q) {
                $q->whereNotNull('started_at')
                    ->orWhere('time_watched', '>', 0)
                    ->orWhere('is_completed', true);
            })
            ->pluck('content_id')
            ->unique()
            ->values();

        $resumeHighlightEnrollment = null;
        if ($courseIdsWithLessonActivity->isNotEmpty()) {
            $resumeQuery = $customer->enrollments()
                ->whereHas('content', function ($q) {
                    $q->where('is_published', true)
                        ->where('is_downloadable', false);
                })
                ->where('status', '!=', 'cancelled')
                ->whereIn('content_id', $courseIdsWithLessonActivity)
                ->with(['course.provider', 'course.category'])
                ->orderByDesc('updated_at');

            if ($activePackCourseIds->isNotEmpty()) {
                $resumeQuery->whereNotIn('content_id', $activePackCourseIds->all());
            }

            $resumeHighlightEnrollment = $resumeQuery->first();
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
            ->with(array_merge(
                [
                    'enrollments' => function ($q) {
                        $q->whereHas('content', function ($q2) {
                            $q2->where('is_published', true);
                        });
                    },
                    'enrollments.course',
                ],
                Order::eagerLoadOrderItemsWithPackages()
            ))
            ->latest()
            ->limit(4)
            ->get();

        $excludeRecommendedCourseIds = $customer->getRecommendationExcludedContentIds();

        $recommendedCourses = Course::published()
            ->with(['provider', 'category'])
            ->where('is_free', false)
            ->whereNotIn('id', $excludeRecommendedCourseIds ?: [0])
            ->latest()
            ->limit(5)
            ->get();

        $recommendedPackages = app(ContentPackageRecommendationService::class)
            ->forCustomerDashboard($excludeRecommendedCourseIds);

        // Cours achetés hors pack (lignes de commande standalone, non révoquées)
        $purchasedStandaloneCourseIds = Order::where('user_id', $customer->id)
            ->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', function($q) {
                $q->whereNull('content_package_id')
                  ->whereHas('content', function($q2) {
                    $q2->where('is_published', true);
                });
            })
            ->with(['orderItems.course'])
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems->filter(function($item) use ($order) {
                    if (!($item->course && $item->course->is_published)) {
                        return false;
                    }

                    $revocationMarker = '[COURSE_REVOKED:' . (int) $item->content_id . ']';
                    return !str_contains((string) ($order->notes ?? ''), $revocationMarker);
                })->pluck('content_id');
            })
            ->unique()
            ->values();

        $packsOwnedCount = $purchasedPackagesSummaries->count();
        // Quantité « achats contenus » : cours distincts hors pack + 1 par pack actif
        $purchasedCourses = $purchasedStandaloneCourseIds->count() + $packsOwnedCount;

        // Téléchargeables : hors pack + contenus publiés téléchargeables inclus dans les packs actifs
        $standaloneDownloadableIds = Order::where('user_id', $customer->id)
            ->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', function($q) {
                $q->whereNull('content_package_id')
                  ->whereHas('content', function($q2) {
                    $q2->where('is_published', true)
                       ->where('is_downloadable', true);
                });
            })
            ->with(['orderItems.course'])
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems->filter(function($item) use ($order) {
                    if (!($item->course && $item->course->is_published && $item->course->is_downloadable)) {
                        return false;
                    }

                    $revocationMarker = '[COURSE_REVOKED:' . (int) $item->content_id . ']';
                    return !str_contains((string) ($order->notes ?? ''), $revocationMarker);
                })->pluck('content_id');
            })
            ->unique()
            ->values();

        $packDownloadableIds = $purchasedPackagesSummaries->flatMap(function (array $row) {
            return $row['package']->contents
                ->filter(fn ($c) => (bool) ($c->is_published ?? false) && (bool) ($c->is_downloadable ?? false))
                ->pluck('id');
        })->unique()->values();

        $purchasedDownloadableCourses = $standaloneDownloadableIds->merge($packDownloadableIds)->unique()->count();

        // Inscriptions « hors affichage pack » + packs comme entrées distinctes (quantité bibliothèque)
        $standaloneEnrolledCount = $allEnrollments->count();
        $enrolledTotal = $standaloneEnrolledCount + $packsOwnedCount;

        $standaloneActive = $allEnrollments->where('status', 'active')->count();
        $standaloneCompleted = $allEnrollments->where('status', 'completed')->count();
        $packActive = 0;
        $packCompleted = 0;
        $sumPackProgress = 0.0;
        foreach ($purchasedPackagesSummaries as $row) {
            $agg = $this->packageAggregatesForStats($row['package'], $row['enrollments']);
            $sumPackProgress += (float) $agg['progress'];
            if ($agg['status'] === 'completed') {
                $packCompleted++;
            } elseif ($agg['status'] === 'active') {
                $packActive++;
            }
        }

        // Progression moyenne : moyenne des progressions cours (hors pack dans la liste) + % agrégé par pack
        $sumStandaloneProgress = (float) $allEnrollments->sum('progress');
        $progressDenominator = $standaloneEnrolledCount + $packsOwnedCount;
        $averageProgress = $progressDenominator > 0
            ? round(($sumStandaloneProgress + $sumPackProgress) / $progressDenominator, 1)
            : 0.0;

        // Durée d'apprentissage : tous les contenus suivis (y compris ceux issus d'un pack)
        $allLearningEnrollments = $customer->enrollments()
            ->whereHas('content', function ($q) {
                $q->where('is_published', true);
            })
            ->where('status', '!=', 'cancelled')
            ->with(['course'])
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
            });

        $learningMinutes = $allLearningEnrollments->sum(function ($enrollment) {
            return $enrollment->course ? (int) ($enrollment->course->duration ?? 0) : 0;
        });

        $totalPurchasesAmount = $this->customerPaidOrdersTotalSum($customer->id);

        $stats = [
            'total_courses' => $enrolledTotal,
            'enrolled_courses' => $enrolledTotal,
            'packs_owned' => $packsOwnedCount,
            'purchased_courses' => $purchasedCourses,
            'purchased_downloadable_courses' => $purchasedDownloadableCourses,
            'active_courses' => $standaloneActive + $packActive,
            'completed_courses' => $standaloneCompleted + $packCompleted,
            'certificates_earned' => $totalCertificates,
            'average_progress' => $averageProgress,
            'learning_minutes' => $learningMinutes,
            'total_purchases_amount' => $totalPurchasesAmount,
        ];

        return view('customers.dashboard', [
            'user' => $customer,
            'enrollments' => $dashboardRecentEnrollments,
            'certificates' => $certificates,
            'stats' => $stats,
            'orders' => $recentOrders,
            'recommendedCourses' => $recommendedCourses,
            'recommendedPackages' => $recommendedPackages,
            'resumeHighlightEnrollment' => $resumeHighlightEnrollment,
        ]);
    }

    /**
     * Packs achetés (commande payée) avec inscriptions associées par contenu.
     *
     * @return Collection<int, array{package: ContentPackage, order: ?Order, enrollments: \Illuminate\Support\Collection<int, \App\Models\Enrollment>}>
     */
    private function purchasedPackagesSummaries($customer): Collection
    {
        $packageIds = OrderItem::query()
            ->whereHas('order', function ($q) use ($customer) {
                $q->where('user_id', $customer->id)
                    ->whereIn('status', ['paid', 'completed']);
            })
            ->whereNotNull('content_package_id')
            ->distinct()
            ->pluck('content_package_id');

        if ($packageIds->isEmpty()) {
            return collect();
        }

        return ContentPackage::query()
            ->whereIn('id', $packageIds)
            ->with([
                'contents' => fn ($q) => $q->orderByPivot('sort_order'),
                'contents.provider',
                'contents.category',
            ])
            ->get()
            ->map(function (ContentPackage $package) use ($customer) {
                $revocationMarker = '[PACK_REVOKED:' . (int) $package->id . ']';

                $order = Order::where('user_id', $customer->id)
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereHas('orderItems', fn ($q) => $q->where('content_package_id', $package->id))
                    ->where(function ($q) use ($revocationMarker) {
                        $q->whereNull('notes')
                            ->orWhere('notes', 'not like', '%' . $revocationMarker . '%');
                    })
                    ->orderByDesc('paid_at')
                    ->orderByDesc('created_at')
                    ->first();

                if (!$order) {
                    // Toutes les commandes contenant ce pack ont été révoquées.
                    return null;
                }

                $enrollments = $customer->enrollments()
                    ->whereIn('content_id', $package->contents->pluck('id'))
                    ->where('status', '!=', 'cancelled')
                    ->get()
                    ->keyBy('content_id');

                return [
                    'package' => $package,
                    'order' => $order,
                    'enrollments' => $enrollments,
                ];
            })
            ->filter()
            ->sortByDesc(fn (array $row) => $row['order']?->paid_at ?? $row['order']?->created_at)
            ->values();
    }

    /**
     * Statut et % de complétion d'un pack (même règles que la liste « Mes contenus »).
     *
     * @param  \Illuminate\Support\Collection<int|string, \App\Models\Enrollment>  $enrollmentsKeyedByContentId
     * @return array{status: string, progress: float}
     */
    private function packageAggregatesForStats(ContentPackage $package, Collection $enrollmentsKeyedByContentId): array
    {
        $publishedContents = $package->contents->filter(fn ($c) => (bool) ($c->is_published ?? false));
        $total = $publishedContents->count();
        $publishedIds = $publishedContents->pluck('id');

        $subset = $enrollmentsKeyedByContentId->only($publishedIds->all());
        $completed = $subset->where('status', 'completed')->count();
        $active = $subset->where('status', 'active')->count();
        $suspended = $subset->where('status', 'suspended')->count();
        $progress = $total > 0 ? (float) (($completed / $total) * 100) : 0.0;

        $status = 'active';
        if ($total > 0 && $completed >= $total) {
            $status = 'completed';
        } elseif ($active <= 0 && $suspended > 0) {
            $status = 'suspended';
        }

        return ['status' => $status, 'progress' => $progress];
    }

    private function customerPaidOrdersTotalSum(int $userId): float
    {
        return (float) Order::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['paid', 'completed'])
            ->get()
            ->sum(function (Order $order) {
                $a = $order->total_amount;
                if ($a !== null && $a !== '') {
                    return (float) $a;
                }
                $b = $order->total;
                if ($b !== null && $b !== '') {
                    return (float) $b;
                }

                return 0.0;
            });
    }

    public function showPurchasedPack(ContentPackage $package)
    {
        $customer = auth()->user();
        if (! $customer->hasPurchasedContentPackage($package)) {
            abort(403);
        }

        $package->load([
            'contents' => fn ($q) => $q->orderByPivot('sort_order'),
            'contents.provider',
            'contents.category',
        ]);

        $enrollments = $customer->enrollments()
            ->whereIn('content_id', $package->contents->pluck('id'))
            ->where('status', '!=', 'cancelled')
            ->with(['course.provider', 'course.category'])
            ->get()
            ->keyBy('content_id');

        $revocationMarker = '[PACK_REVOKED:' . (int) $package->id . ']';

        $order = Order::where('user_id', $customer->id)
            ->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', fn ($q) => $q->where('content_package_id', $package->id))
            ->where(function ($q) use ($revocationMarker) {
                $q->whereNull('notes')
                    ->orWhere('notes', 'not like', '%' . $revocationMarker . '%');
            })
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->first();

        if (!$order) {
            // Possibilité rare: le check hasPurchasedContentPackage est passé, mais la commande la plus récente est révoquée.
            // On renvoie 403 pour rester cohérent avec l'accès applicatif.
            abort(403);
        }

        return view('customers.pack-show', [
            'package' => $package,
            'enrollments' => $enrollments,
            'order' => $order,
        ]);
    }

    public function courses(Request $request)
    {
        $customer = auth()->user();

        $statusFilter = $request->get('status', 'all');
        $search = $request->get('q');
        $purchasedPackagesSummaries = $this->purchasedPackagesSummaries($customer);
        $activePackCourseIds = $purchasedPackagesSummaries
            ->flatMap(fn (array $row) => $row['package']->contents->pluck('id'))
            ->filter()
            ->unique()
            ->values();
        $activePackOrderIds = $purchasedPackagesSummaries
            ->pluck('order')
            ->filter()
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        // Récupérer les inscriptions existantes (seulement pour les cours publiés)
        $enrollments = $customer->enrollments()
            ->whereHas('content', function($q) {
                $q->where('is_published', true);
            })
            // Ne jamais remontrer les inscriptions annulées/révoquées dans l'espace client
            ->where('status', '!=', 'cancelled')
            ->with(['course.provider', 'course.category', 'order', 'course.downloads'])
            ->orderByDesc('updated_at')
            ->get();

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
        $enrollments = $enrollments->filter(function ($enrollment) use ($downloadedFreeCourseIds, $activePackCourseIds) {
            $course = $enrollment->course;
            if (! $course) {
                return false;
            }

            // Les contenus inclus dans un pack actif sont consultés depuis la page du pack.
            if ($activePackCourseIds->contains($course->id)) {
                return false;
            }

            if ($course->is_downloadable && $course->is_free) {
                return $downloadedFreeCourseIds->contains($course->id);
            }

            return true;
        })->values();

        // Cours achetés mais non inscrits (hors contenus de packs actifs)
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
            ->whereHas('orderItems', function ($query) use ($allEnrollmentCourseIds, $activePackCourseIds) {
                $query->whereNotIn('content_id', $allEnrollmentCourseIds)
                    ->whereNotIn('content_id', $activePackCourseIds->all() ?: [0])
                    // Les contenus vendus via un pack ne doivent pas réapparaître dans la liste externe.
                    ->whereNull('content_package_id')
                    ->whereHas('content', function($q) {
                        $q->where('is_published', true);
                    });
            })
            ->with(['orderItems.course' => function($q) {
                $q->where('is_published', true);
            }, 'orderItems.course.provider', 'orderItems.course.category', 'orderItems.course.downloads'])
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems->filter(function($item) use ($order) {
                    if (!empty($item->content_package_id)) {
                        return false;
                    }

                    if (!($item->course && $item->course->is_published)) {
                        return false;
                    }

                    $revocationMarker = '[COURSE_REVOKED:' . (int) $item->content_id . ']';
                    return !str_contains((string) ($order->notes ?? ''), $revocationMarker);
                })->map(function ($item) use ($order) {
                    return new PurchasedCourseEnrollment($item->course, $order);
                });
            });

        $courseItems = collect($enrollments->all())->merge($purchasedButNotEnrolled)
            ->map(function ($item) {
                $course = $item->course ?? null;
                if (!$course) {
                    return null;
                }

                $isPurchasedNotEnrolled = isset($item->is_purchased_not_enrolled) && $item->is_purchased_not_enrolled;
                $status = $isPurchasedNotEnrolled ? 'purchased' : ((string) ($item->status ?? 'active'));

                return (object) [
                    'item_type' => 'course',
                    'course' => $course,
                    'package' => null,
                    'order' => $item->order ?? null,
                    'status' => $status,
                    'progress' => $isPurchasedNotEnrolled ? 0 : (float) ($item->progress ?? 0),
                    'is_purchased_not_enrolled' => $isPurchasedNotEnrolled,
                    'created_at' => $item->updated_at ?? ($item->order->created_at ?? now()),
                ];
            })
            ->filter()
            ->values();

        $packageItems = $purchasedPackagesSummaries->map(function (array $row) {
            /** @var ContentPackage $package */
            $package = $row['package'];
            $order = $row['order'];
            $enrollments = $row['enrollments'];
            $publishedContents = $package->contents->filter(fn ($c) => (bool) ($c->is_published ?? false));
            $total = $publishedContents->count();
            $completed = $enrollments->where('status', 'completed')->count();
            $active = $enrollments->where('status', 'active')->count();
            $suspended = $enrollments->where('status', 'suspended')->count();
            $progress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            $status = 'active';
            if ($total > 0 && $completed >= $total) {
                $status = 'completed';
            } elseif ($active <= 0 && $suspended > 0) {
                $status = 'suspended';
            }

            return (object) [
                'item_type' => 'package',
                'course' => null,
                'package' => $package,
                'order' => $order,
                'status' => $status,
                'progress' => $progress,
                'package_total_contents' => $total,
                'package_enrollments_count' => $enrollments->count(),
                'created_at' => $order?->paid_at ?? $order?->created_at ?? now(),
            ];
        })->values();

        // Fusionner contenus + packs dans la même liste et appliquer filtres/recherche communs.
        $allItemsBase = $courseItems->merge($packageItems)->values();
        $allItems = $allItemsBase->filter(function ($item) use ($search, $statusFilter) {
            if (!empty($search)) {
                $term = mb_strtolower((string) $search);
                if ($item->item_type === 'package') {
                    $package = $item->package;
                    $haystack = [
                        (string) ($package->title ?? ''),
                        (string) ($package->subtitle ?? ''),
                        (string) ($package->short_description ?? ''),
                        (string) $package->contents->pluck('title')->join(' '),
                    ];
                    $matched = false;
                    foreach ($haystack as $chunk) {
                        if ($chunk !== '' && str_contains(mb_strtolower($chunk), $term)) {
                            $matched = true;
                            break;
                        }
                    }
                    if (!$matched) {
                        return false;
                    }
                } else {
                    $course = $item->course;
                    $provider = $course?->provider?->name ?? '';
                    $title = $course?->title ?? '';
                    if (!str_contains(mb_strtolower($title . ' ' . $provider), $term)) {
                        return false;
                    }
                }
            }

            if ($statusFilter !== 'all') {
                return (string) $item->status === (string) $statusFilter;
            }

            return true;
        })->values();

        $allItems = $allItems->sortByDesc(fn ($item) => $item->created_at ?? now())->values();

        // Pagination manuelle
        $currentPage = $request->get('page', 1);
        $perPage = 12;
        $total = $allItems->count();
        $items = $allItems->slice(($currentPage - 1) * $perPage, $perPage)->values();

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
            if (($item->item_type ?? 'course') !== 'course') {
                continue;
            }

            $course = $item->course;
            if ($course && $course->is_downloadable) {
                $course->user_downloads_count = CourseDownload::where('content_id', $course->id)
                    ->where('user_id', $customer->id)
                    ->count();
                $course->total_downloads_count = CourseDownload::where('content_id', $course->id)->count();
            }
        }

        $statusCounts = $allItemsBase
            ->groupBy('status')
            ->map(fn ($rows) => $rows->count());
        $courseSummary = [
            'total' => $allItemsBase->count(),
            'active' => $statusCounts->get('active', 0),
            'completed' => $statusCounts->get('completed', 0),
            'suspended' => $statusCounts->get('suspended', 0),
            'cancelled' => $statusCounts->get('cancelled', 0),
            'packs_count' => $packageItems->count(),
            'total_purchases_amount' => $this->customerPaidOrdersTotalSum($customer->id),
        ];

        return view('customers.contents', [
            'user' => $customer,
            'enrollments' => $paginator,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'courseSummary' => $courseSummary,
            'purchasedPackagesSummaries' => $purchasedPackagesSummaries,
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
        
        // Pour les cours téléchargeables ou en présentiel, ne pas rediriger vers learning, mais vers la page du cours
        if (($course->is_downloadable || ($course->is_in_person_program ?? false)) && $redirectTo === 'learn') {
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
            $revocationMarker = '[COURSE_REVOKED:' . (int) $course->id . ']';

            $hasPurchased = Order::where('user_id', $customer->id)
                ->whereIn('status', ['paid', 'completed'])
                ->whereHas('orderItems', function ($query) use ($course) {
                    $query->where('content_id', $course->id);
                })
                ->where(function ($q) use ($revocationMarker) {
                    $q->whereNull('notes')
                        ->orWhere('notes', 'not like', '%' . $revocationMarker . '%');
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
            $revocationMarker = '[COURSE_REVOKED:' . (int) $course->id . ']';

            $order = Order::where('user_id', $customer->id)
                ->whereIn('status', ['paid', 'completed'])
                ->whereHas('orderItems', function ($query) use ($course) {
                    $query->where('content_id', $course->id);
                })
                ->where(function ($q) use ($revocationMarker) {
                    $q->whereNull('notes')
                        ->orWhere('notes', 'not like', '%' . $revocationMarker . '%');
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
            : (($course->is_in_person_program ?? false)
                ? 'Inscription réussie ! Utilisez le bouton « Télécharger » pour obtenir votre reçu.'
                : 'Inscription réussie ! Vous pouvez commencer à apprendre.');

        $packSlug = $request->input('return_to_customer_pack');
        if (is_string($packSlug) && $packSlug !== '') {
            $returnPackage = ContentPackage::where('slug', $packSlug)->first();
            if ($returnPackage && $customer->hasPurchasedContentPackage($returnPackage)) {
                return redirect()
                    ->route('customer.pack', $returnPackage)
                    ->with('success', $successMessage);
            }
        }

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
