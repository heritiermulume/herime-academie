<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use App\Models\CourseSection;
use App\Models\CourseLesson;
use App\Models\Enrollment;
use App\Traits\CourseStatistics;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\CourseDownload;
use Illuminate\Support\Carbon;

class CourseController extends Controller
{
    use CourseStatistics;

    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index(Request $request)
    {
        $query = Course::published()->with(['provider', 'category']);

        // Filtres spéciaux pour les sections de la page d'accueil
        if ($request->filled('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->filled('popular')) {
            $query->popular();
        }

        if ($request->filled('trending')) {
            $query->whereHas('enrollments', function($q) {
                $q->where('created_at', '>=', now()->subWeek());
            })->withCount('enrollments')->orderBy('enrollments_count', 'desc');
        }

        // Filtres normaux
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('price')) {
            if ($request->price === 'free') {
                $query->where('is_free', true);
            } elseif ($request->price === 'paid') {
                $query->where('is_free', false);
            }
        }

        // Recherche globale (comme dans l'admin)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('short_description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('provider', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('category', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Tri - Par défaut, afficher les cours les plus populaires en premier
        $sort = $request->get('sort', 'popular');
        switch ($sort) {
            case 'popular':
                $query->popular();
                break;
            case 'latest':
                $query->latest();
                break;
            case 'rating':
                $query->topRated();
                break;
            case 'price_low':
                $now = Carbon::now();
                $query->orderByRaw(
                    'CASE WHEN sale_price IS NOT NULL 
                        AND (sale_start_at IS NULL OR sale_start_at <= ?) 
                        AND (sale_end_at IS NULL OR sale_end_at >= ?) 
                        THEN sale_price ELSE price END ASC',
                    [$now, $now]
                );
                break;
            case 'price_high':
                $now = Carbon::now();
                $query->orderByRaw(
                    'CASE WHEN sale_price IS NOT NULL 
                        AND (sale_start_at IS NULL OR sale_start_at <= ?) 
                        AND (sale_end_at IS NULL OR sale_end_at >= ?) 
                        THEN sale_price ELSE price END DESC',
                    [$now, $now]
                );
                break;
            default:
                $query->popular(); // Par défaut, cours les plus populaires
        }

        // Gestion du scroll infini
        if ($request->ajax() && $request->filled('infinite_scroll')) {
            $page = $request->get('page', 1);
            $perPage = 12;
            
            $courses = $query->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                           ->skip(($page - 1) * $perPage)
                           ->take($perPage)
                           ->get();
            
            // Ajouter les statistiques
            $courses = $this->addCourseStatistics($courses);
            
            $hasMore = $courses->count() === $perPage;
            
            // Formater les cours pour JSON avec la date de fin de promotion
            $coursesArray = $courses->map(function($course) {
                $courseArray = $course->toArray();
                // S'assurer que sale_end_at est au format ISO 8601
                if ($course->sale_end_at) {
                    $courseArray['sale_end_at'] = $course->sale_end_at->toIso8601String();
                }
                if ($course->sale_start_at) {
                    $courseArray['sale_start_at'] = $course->sale_start_at->toIso8601String();
                }
                $courseArray['is_sale_active'] = $course->is_sale_active;
                $courseArray['active_sale_price'] = $course->active_sale_price;
                $courseArray['sale_discount_percentage'] = $course->sale_discount_percentage;
                if (! $course->is_sale_active) {
                    $courseArray['sale_price'] = null;
                } else {
                    $courseArray['sale_price'] = $course->active_sale_price;
                }
                return $courseArray;
            });
            
            return response()->json([
                'courses' => $coursesArray,
                'hasMore' => $hasMore,
                'nextPage' => $hasMore ? $page + 1 : null
            ]);
        }

        $courses = $query->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                          ->paginate(12)
                          ->withQueryString();
        
        // Ajouter les statistiques à chaque cours
        $courses->getCollection()->transform(function($course) {
            $course->stats = [
                'total_lessons' => $course->sections->sum(function($section) {
                    return $section->lessons->count();
                }),
                'total_duration' => $course->sections->sum(function($section) {
                    return $section->lessons->sum('duration');
                }),
                'total_customers' => $course->total_customers, // Nombre d'inscriptions
                'purchases_count' => $course->purchases_count, // Nombre d'achats (pour tous les cours)
                // Statistiques supplémentaires pour les produits téléchargeables
                'total_downloads' => $course->is_downloadable ? $course->total_downloads_count : null,
                'unique_downloads' => $course->is_downloadable ? $course->unique_downloads_count : null,
                'total_revenue' => $course->is_downloadable ? $course->total_purchases_revenue : null,
                'average_rating' => $course->reviews->avg('rating') ?? 0,
                'total_reviews' => $course->reviews->count(),
            ];
            return $course;
        });
        
        $categories = Category::active()->ordered()->get();

        return view('contents.index', compact('courses', 'categories'));
    }

    public function show(Course $course)
    {
        try {
            // Vérifier que le contenu est publié, sauf si l'utilisateur est le prestataire du contenu
            $isProvider = auth()->check() && auth()->user()->hasRole('provider') && $course->provider_id === auth()->id();
            if (!$course->is_published && !$isProvider) {
                abort(404, 'Ce contenu n\'est pas disponible.');
            }

            // Charger toutes les relations nécessaires
            // Pour les prestataires, charger toutes les sections et leçons même non publiées
            $course->load([
                'provider' => function($query) {
                    $query->withCount('courses');
                },
                'provider.contents' => function($query) {
                    $query->withCount('enrollments');
                },
                'category',
                'sections' => function($query) use ($isProvider) {
                    if (!$isProvider) {
                        $query->where('is_published', true);
                    }
                    $query->orderBy('sort_order');
                },
                'sections.lessons' => function($query) use ($isProvider) {
                    if (!$isProvider) {
                        $query->where('is_published', true);
                    }
                    $query->orderBy('sort_order');
                },
                'reviews' => function($query) {
                    $query->where('is_approved', true)->with('user')->latest();
                },
                'enrollments' => function($query) {
                    $query->where('status', 'active');
                },
            ]);
            
            $userId = auth()->id();

            // Vérifier si l'utilisateur est inscrit
            try {
                $isEnrolled = $userId ? $course->isEnrolledBy($userId) : false;
                $enrollment = $isEnrolled ? $course->getEnrollmentFor($userId) : null;
            } catch (\Throwable $e) {
                \Log::error('Erreur lors de la vérification de l\'inscription', [
                    'content_id' => $course->id,
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
                $isEnrolled = false;
                $enrollment = null;
            }

            $hasPurchased = false;
            if ($userId) {
                try {
                    if ($isEnrolled || $course->is_free) {
                        $hasPurchased = $isEnrolled;
                    } elseif (!$course->is_free) {
                        // Vérifier les commandes payées ou complétées
                        $hasPurchased = Order::where('user_id', $userId)
                            ->whereIn('status', ['paid', 'completed'])
                            ->whereHas('orderItems', function ($query) use ($course) {
                                $query->where('content_id', $course->id);
                            })
                            ->exists();
                    }
                } catch (\Throwable $e) {
                    \Log::error('Erreur lors de la vérification de l\'achat', [
                        'content_id' => $course->id,
                        'user_id' => $userId,
                        'error' => $e->getMessage()
                    ]);
                    $hasPurchased = false;
                }
            }

            // Obtenir l'état du bouton avec gestion d'erreur
            try {
                $buttonState = $course->getButtonStateForUser($userId);
            } catch (\Throwable $e) {
                \Log::error('Erreur lors de l\'obtention de buttonState', [
                    'content_id' => $course->id,
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
                $buttonState = 'enroll'; // Valeur par défaut
            }
            
            $canAccessCourse = $isEnrolled || $hasPurchased;
            $canDownloadCourse = $course->is_downloadable && $canAccessCourse;

            // Calculer si l'utilisateur a téléchargé (pour les contenus téléchargeables gratuits)
            $hasDownloaded = false;
            if ($userId && $course->is_downloadable) {
                try {
                    $hasDownloaded = \App\Models\CourseDownload::where('content_id', $course->id)
                        ->where('user_id', $userId)
                        ->exists();
                } catch (\Throwable $e) {
                    \Log::error('Erreur lors de la vérification du téléchargement', [
                        'content_id' => $course->id,
                        'user_id' => $userId,
                        'error' => $e->getMessage()
                    ]);
                    $hasDownloaded = false;
                }
            }

            // Calculer si l'utilisateur peut noter selon les nouvelles règles
            $canReview = false;
            if ($userId) {
                try {
                    if ($course->is_downloadable) {
                        // Contenu téléchargeable
                        if ($course->is_free) {
                            // Téléchargeable gratuit : avoir téléchargé au moins une fois
                            $canReview = $hasDownloaded;
                        } else {
                            // Téléchargeable payant : avoir payé
                            $canReview = $hasPurchased;
                        }
                    } else {
                        // Contenu non téléchargeable
                        if ($course->is_free) {
                            // Non téléchargeable gratuit : être inscrit
                            $canReview = $isEnrolled;
                        } else {
                            // Non téléchargeable payant : avoir payé
                            $canReview = $hasPurchased;
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::error('Erreur lors de la vérification de canReview', [
                        'content_id' => $course->id,
                        'user_id' => $userId,
                        'error' => $e->getMessage()
                    ]);
                    $canReview = false;
                }
            }

            // Calculer les statistiques du cours (simplifiées)
            $courseStats = [
                'recent_announcements' => \App\Models\Announcement::where('is_active', true)
                    ->latest()
                    ->limit(5)
                    ->get()
            ];

            // Cours similaires avec algorithme de recommandation amélioré
            try {
                $relatedCourses = $this->getRecommendedCourses($course);
            } catch (\Throwable $e) {
                \Log::error('Erreur lors de l\'obtention des cours recommandés', [
                    'content_id' => $course->id,
                    'error' => $e->getMessage()
                ]);
                $relatedCourses = collect([]); // Collection vide en cas d'erreur
            }

            return view('contents.show', compact(
                'course',
                'isEnrolled',
                'enrollment',
                'relatedCourses',
                'courseStats',
                'buttonState',
                'hasPurchased',
                'canAccessCourse',
                'canDownloadCourse',
                'hasDownloaded',
                'canReview'
            ));
        } catch (\Throwable $e) {
            \Log::error('Erreur fatale dans CourseController@show', [
                'content_id' => $course->id ?? null,
                'course_slug' => $course->slug ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // En production, afficher une erreur générique
            if (!config('app.debug')) {
                abort(500, 'Une erreur est survenue lors du chargement de la page. Veuillez réessayer plus tard.');
            }
            
            // En développement, laisser passer l'erreur pour le debug
            throw $e;
        }
    }

    public function byCategory(Category $category)
    {
        $courses = Course::published()
            ->where('category_id', $category->id)
            ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->latest()
            ->paginate(12);
        
        // Ajouter les statistiques à chaque cours
        $courses->getCollection()->transform(function($course) {
            $course->stats = [
                'total_lessons' => $course->sections->sum(function($section) {
                    return $section->lessons->count();
                }),
                'total_duration' => $course->sections->sum(function($section) {
                    return $section->lessons->sum('duration');
                }),
                'total_customers' => $course->total_customers, // Nombre d'inscriptions
                'purchases_count' => $course->purchases_count, // Nombre d'achats (pour tous les cours)
                // Statistiques supplémentaires pour les produits téléchargeables
                'total_downloads' => $course->is_downloadable ? $course->total_downloads_count : null,
                'unique_downloads' => $course->is_downloadable ? $course->unique_downloads_count : null,
                'total_revenue' => $course->is_downloadable ? $course->total_purchases_revenue : null,
                'average_rating' => $course->reviews->avg('rating') ?? 0,
                'total_reviews' => $course->reviews->count(),
            ];
            return $course;
        });

        // Récupérer les autres catégories avec le nombre de cours
        $otherCategories = Category::active()
            ->where('id', '!=', $category->id)
            ->withCount('courses')
            ->ordered()
            ->limit(6)
            ->get();

        return view('contents.category', compact('courses', 'category', 'otherCategories'));
    }

    /**
     * Afficher tous les avis d'un cours avec pagination
     */
    public function reviews(Course $course, Request $request)
    {
        // Vérifier que le cours est publié
        if (!$course->is_published) {
            abort(404, 'Ce cours n\'est pas disponible.');
        }

        // Charger les informations de base du cours
        $course->load(['provider', 'category']);

        // Charger les avis approuvés avec pagination
        $reviews = \App\Models\Review::where('content_id', $course->id)
            ->where('is_approved', true)
            ->with('user')
            ->latest()
            ->paginate(10);

        // Calculer les statistiques des avis
        $averageRating = round((float)(\App\Models\Review::where('content_id', $course->id)
            ->where('is_approved', true)
            ->avg('rating') ?? 0), 1);
        
        $totalReviews = \App\Models\Review::where('content_id', $course->id)
            ->where('is_approved', true)
            ->count();

        return view('contents.reviews', compact('course', 'reviews', 'averageRating', 'totalReviews'));
    }

    public function create()
    {
        $categories = Category::active()->ordered()->get();
        return view('contents.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Log pour debug
        Log::info('Tentative de création de cours', [
            'user_id' => auth()->id(),
            'has_title' => $request->has('title'),
            'has_category' => $request->has('category_id'),
            'has_price' => $request->has('price'),
        ]);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'sale_start_at' => 'nullable|date',
            'sale_end_at' => 'nullable|date|after_or_equal:sale_start_at',
            'level' => 'required|in:beginner,intermediate,advanced',
            'language' => 'required|string|max:5',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'thumbnail_chunk_path' => 'nullable|string|max:2048',
            'thumbnail_chunk_name' => 'nullable|string|max:255',
            'thumbnail_chunk_size' => 'nullable|integer|min:0',
            'video_preview' => 'nullable|file|mimes:mp4,avi,mov|max:10240',
            'video_preview_file' => 'nullable|file|mimes:mp4,avi,mov,webm,ogg|max:512000',
            'video_preview_path' => 'nullable|string|max:2048',
            'video_preview_name' => 'nullable|string|max:255',
            'video_preview_size' => 'nullable|integer|min:0',
            'requirements' => 'nullable|array',
            'what_you_will_learn' => 'nullable|array',
            'tags' => 'nullable|string',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'is_downloadable' => 'nullable|boolean',
            'download_file_path' => 'nullable|file|mimes:zip,pdf,doc,docx,rar,7z,tar,gz|max:1048576',
            'download_file_chunk_path' => 'nullable|string|max:2048',
            'download_file_chunk_name' => 'nullable|string|max:255',
            'download_file_chunk_size' => 'nullable|integer|min:0',
            'download_file_url' => 'nullable|url|max:1000',
            'sections' => 'nullable|array',
            'sections.*.title' => 'required_with:sections|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.lessons' => 'nullable|array',
            'sections.*.lessons.*.title' => 'required_with:sections.*.lessons|string|max:255',
            'sections.*.lessons.*.description' => 'nullable|string',
            'sections.*.lessons.*.type' => 'required_with:sections.*.lessons|in:video,text,quiz,assignment',
            'sections.*.lessons.*.content_url' => 'nullable|string',
            'sections.*.lessons.*.content_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg,application/pdf,application/zip,application/x-zip-compressed,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,application/x-rar-compressed,application/x-7z-compressed,application/x-tar,application/gzip|max:524288000', // 500MB max
            'sections.*.lessons.*.content_file_path' => 'nullable|string|max:2048',
            'sections.*.lessons.*.content_file_name' => 'nullable|string|max:255',
            'sections.*.lessons.*.content_file_size' => 'nullable|integer|min:0',
            'sections.*.lessons.*.content_text' => 'nullable|string',
            'sections.*.lessons.*.duration' => 'nullable|integer|min:0',
            'sections.*.lessons.*.is_preview' => 'boolean',
        ]);

        DB::beginTransaction();

        try {
            $providerId = auth()->id();

            if (!$providerId) {
                abort(403, 'Vous devez être connecté en tant que prestataire pour créer un contenu.');
            }

            $data = $request->only([
                'title',
                'description',
                'short_description',
                'category_id',
                'price',
                'sale_price',
                'sale_start_at',
                'sale_end_at',
                'level',
                'language',
                'requirements',
                'what_you_will_learn',
                'tags',
                'meta_description',
                'meta_keywords',
            ]);

            $data['provider_id'] = $providerId;
            $data['slug'] = $this->generateUniqueSlug($request->title);
            $data['is_published'] = false;
            $data['is_free'] = false;
            $data['use_external_payment'] = false;
            $data['is_downloadable'] = $request->boolean('is_downloadable', false);
            $data['meta_description'] = $request->input('meta_description');
            $data['meta_keywords'] = $request->input('meta_keywords');

            $data['requirements'] = collect($request->input('requirements', []))
                ->filter(fn($value) => filled($value))
                ->values()
                ->all();

            $data['what_you_will_learn'] = collect($request->input('what_you_will_learn', []))
                ->filter(fn($value) => filled($value))
                ->values()
                ->all();

            // Traiter les tags comme une string séparée par des virgules
            $tagsString = $request->input('tags', '');
            if (filled($tagsString)) {
                $data['tags'] = collect(explode(',', $tagsString))
                    ->map(fn($tag) => trim($tag))
                    ->filter(fn($tag) => filled($tag))
                    ->values()
                    ->all();
            } else {
                $data['tags'] = [];
            }

            $data['sale_start_at'] = $request->filled('sale_start_at') ? Carbon::parse($request->input('sale_start_at')) : null;
            $data['sale_end_at'] = $request->filled('sale_end_at') ? Carbon::parse($request->input('sale_end_at')) : null;

            if (! $request->filled('sale_price')) {
                $data['sale_price'] = null;
                $data['sale_start_at'] = null;
                $data['sale_end_at'] = null;
            }

            if ($request->hasFile('thumbnail')) {
                $result = $this->fileUploadService->uploadImage(
                    $request->file('thumbnail'),
                    'courses/thumbnails',
                    null,
                    1920
                );
                $data['thumbnail'] = $result['path'];
            } elseif ($request->filled('thumbnail_chunk_path')) {
                $chunkPath = $this->sanitizeUploadedPath($request->input('thumbnail_chunk_path'));
                if ($chunkPath) {
                    $data['thumbnail'] = $this->fileUploadService->promoteTemporaryFile(
                        $chunkPath,
                        'courses/thumbnails'
                    );
                }
            }

        if ($request->hasFile('video_preview_file')) {
            $result = $this->fileUploadService->uploadVideo(
                $request->file('video_preview_file'),
                'courses/previews',
                null
            );
            $data['video_preview'] = $result['path'];
        } elseif ($request->hasFile('video_preview')) {
            // Fallback pour compatibilité
            $result = $this->fileUploadService->uploadVideo(
                $request->file('video_preview'),
                'courses/previews',
                null
            );
            $data['video_preview'] = $result['path'];
        } elseif ($request->filled('video_preview_path')) {
            $sanitizedPath = $this->sanitizeUploadedPath($request->string('video_preview_path')->toString());
            if ($sanitizedPath) {
                $data['video_preview'] = $this->fileUploadService->promoteTemporaryFile(
                    $sanitizedPath,
                    'courses/previews'
                );
            }
        }

            // Gérer le fichier de téléchargement spécifique
            if ($request->hasFile('download_file_path')) {
                try {
                    $result = $this->fileUploadService->uploadDocument(
                        $request->file('download_file_path'),
                        'courses/downloads',
                        null
                    );
                    $data['download_file_path'] = $result['path'];
                } catch (\Exception $e) {
                    Log::error('Erreur upload download_file_path: ' . $e->getMessage());
                    throw $e;
                }
            } elseif ($request->filled('download_file_chunk_path')) {
                $chunkPath = $this->sanitizeUploadedPath($request->input('download_file_chunk_path'));
                if ($chunkPath) {
                    $data['download_file_path'] = $this->fileUploadService->promoteTemporaryFile(
                        $chunkPath,
                        'courses/downloads'
                    );
                }
            } elseif ($request->filled('download_file_url')) {
                // Si une URL externe est fournie, l'utiliser
                $data['download_file_path'] = $request->download_file_url;
            }

            $course = Course::create($data);

            $sections = $request->input('sections', []);
            foreach ($sections as $sectionIndex => $sectionData) {
                $sectionTitle = $sectionData['title'] ?? null;
                if (!filled($sectionTitle)) {
                    continue;
                }

                $section = $course->sections()->create([
                    'title' => $sectionTitle,
                    'description' => $sectionData['description'] ?? '',
                    'sort_order' => $sectionIndex + 1,
                    'is_published' => true,
                ]);

                $lessons = $sectionData['lessons'] ?? [];
                if (!is_array($lessons) || empty($lessons)) {
                    continue;
                }

                foreach ($lessons as $lessonIndex => $lessonData) {
                    $lessonTitle = $lessonData['title'] ?? null;
                    $lessonType = $lessonData['type'] ?? null;

                    if (!filled($lessonTitle) || !filled($lessonType)) {
                        continue;
                    }

                    $filePath = null;
                    $chunkPath = $this->sanitizeUploadedPath($lessonData['content_file_path'] ?? null);
                    if ($chunkPath) {
                        $filePath = $this->fileUploadService->promoteTemporaryFile(
                            $chunkPath,
                            'courses/lessons'
                        );
                    }
                    if ($request->hasFile("sections.$sectionIndex.lessons.$lessonIndex.content_file")) {
                        $uploadedFile = $request->file("sections.$sectionIndex.lessons.$lessonIndex.content_file");

                        try {
                            $mimeType = $uploadedFile->getMimeType();
                            if ($mimeType && str_starts_with($mimeType, 'video/')) {
                                $result = $this->fileUploadService->uploadVideo($uploadedFile, 'courses/lessons', null);
                            } elseif ($mimeType && str_starts_with($mimeType, 'application/')) {
                                $result = $this->fileUploadService->uploadDocument($uploadedFile, 'courses/lessons', null);
                            } else {
                                $result = $this->fileUploadService->upload($uploadedFile, 'courses/lessons', null);
                            }
                            $filePath = $result['path'];
                        } catch (\Throwable $e) {
                            Log::error('Erreur lors du téléversement du fichier de leçon', [
                                'provider_id' => auth()->id(),
                                'content_id' => $course->id,
                                'section_index' => $sectionIndex,
                                'lesson_index' => $lessonIndex,
                                'message' => $e->getMessage(),
                            ]);
                            throw $e;
                        }
                    }

                    $section->lessons()->create([
                        'content_id' => $course->id,
                        'title' => $lessonTitle,
                        'description' => $lessonData['description'] ?? null,
                        'type' => $lessonType,
                        'content_url' => $filePath ?: ($lessonData['content_url'] ?? null),
                        'file_path' => $filePath,
                        'content_text' => $lessonData['content_text'] ?? null,
                        'duration' => isset($lessonData['duration']) ? (int) $lessonData['duration'] : 0,
                        'sort_order' => $lessonIndex + 1,
                        'is_published' => true,
                        'is_preview' => !empty($lessonData['is_preview']),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('provider.contents.index')
                ->with('success', 'Cours créé avec succès.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            Log::error('Erreur de validation lors de la création du cours', [
                'provider_id' => auth()->id(),
                'errors' => $e->errors(),
                'request_data' => $request->except(['thumbnail', 'video_preview_file', 'sections']),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Erreur lors de la création du cours', [
                'provider_id' => auth()->id(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Impossible de créer le cours pour le moment. Erreur: ' . $e->getMessage()]);
        }
    }

    public function edit(Course $course)
    {
        $this->ensureCanManageCourse($course);
        
        $categories = Category::active()->ordered()->get();
        $course->load(['sections' => function($query) {
            $query->orderBy('sort_order');
        }, 'sections.lessons' => function($query) {
            $query->orderBy('sort_order');
        }]);
        
        return view('contents.edit', compact('course', 'categories'));
    }

    public function update(Request $request, Course $course)
    {
        $this->ensureCanManageCourse($course);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            // Prix obligatoire sauf si le cours est marqué comme gratuit
            'price' => 'required_unless:is_free,1|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'sale_start_at' => 'nullable|date',
            'sale_end_at' => 'nullable|date|after_or_equal:sale_start_at',
            'level' => 'required|in:beginner,intermediate,advanced',
            'language' => 'required|string|max:5',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'thumbnail_chunk_path' => 'nullable|string|max:2048',
            'thumbnail_chunk_name' => 'nullable|string|max:255',
            'thumbnail_chunk_size' => 'nullable|integer|min:0',
            'video_preview' => 'nullable|string|max:2048',
            'video_preview_file' => 'nullable|file|mimes:mp4,avi,mov,webm,ogg|max:512000',
            'video_preview_path' => 'nullable|string|max:2048',
            'video_preview_name' => 'nullable|string|max:255',
            'video_preview_size' => 'nullable|integer|min:0',
            'video_preview_youtube_id' => 'nullable|string|max:255',
            'video_preview_is_unlisted' => 'nullable|boolean',
            'requirements' => 'nullable|array',
            'what_you_will_learn' => 'nullable|array',
            'tags' => 'nullable|string',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'is_downloadable' => 'nullable|boolean',
            'download_file_path' => 'nullable|file|mimes:zip,pdf,doc,docx,rar,7z,tar,gz|max:1048576',
            'download_file_chunk_path' => 'nullable|string|max:2048',
            'download_file_chunk_name' => 'nullable|string|max:255',
            'download_file_chunk_size' => 'nullable|integer|min:0',
            'download_file_url' => 'nullable|url|max:1000',
            'sections' => 'nullable|array',
            'sections.*.title' => 'required_with:sections|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.lessons' => 'nullable|array',
            'sections.*.lessons.*.title' => 'required_with:sections.*.lessons|string|max:255',
            'sections.*.lessons.*.description' => 'nullable|string',
            'sections.*.lessons.*.type' => 'required_with:sections.*.lessons|in:video,text,quiz,assignment',
            'sections.*.lessons.*.content_url' => 'nullable|string',
            'sections.*.lessons.*.content_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg,application/pdf,application/zip,application/x-zip-compressed,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,application/x-rar-compressed,application/x-7z-compressed,application/x-tar,application/gzip|max:524288000',
            'sections.*.lessons.*.content_file_path' => 'nullable|string|max:2048',
            'sections.*.lessons.*.content_file_name' => 'nullable|string|max:255',
            'sections.*.lessons.*.content_file_size' => 'nullable|integer|min:0',
            'sections.*.lessons.*.content_text' => 'nullable|string',
            'sections.*.lessons.*.duration' => 'nullable|integer|min:0',
            'sections.*.lessons.*.is_preview' => 'boolean',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->only([
                'title',
                'description',
                'short_description',
                'category_id',
                'price',
                'sale_price',
                'sale_start_at',
                'sale_end_at',
                'level',
                'language',
                'requirements',
                'what_you_will_learn',
                'meta_description',
                'meta_keywords',
            ]);

            if (auth()->check() && auth()->user()->isProvider()) {
                $data['provider_id'] = $course->provider_id ?? auth()->id();
            }
            $data['slug'] = $this->generateUniqueSlug($request->title, $course->id);

            // Normaliser les tableaux (requirements / what_you_will_learn) en filtrant les valeurs vides
            $data['requirements'] = collect($request->input('requirements', []))
                ->filter(fn($value) => filled($value))
                ->values()
                ->all();

            $data['what_you_will_learn'] = collect($request->input('what_you_will_learn', []))
                ->filter(fn($value) => filled($value))
                ->values()
                ->all();

            // Traiter les tags comme une string séparée par des virgules, comme dans store()
            $tagsString = $request->input('tags', '');
            if (filled($tagsString)) {
                $data['tags'] = collect(explode(',', $tagsString))
                    ->map(fn($tag) => trim($tag))
                    ->filter(fn($tag) => filled($tag))
                    ->values()
                    ->all();
            } else {
                $data['tags'] = [];
            }

            $data['sale_start_at'] = $request->filled('sale_start_at') ? Carbon::parse($request->input('sale_start_at')) : null;
            $data['sale_end_at'] = $request->filled('sale_end_at') ? Carbon::parse($request->input('sale_end_at')) : null;

            if (! $request->filled('sale_price')) {
                $data['sale_price'] = null;
                $data['sale_start_at'] = null;
                $data['sale_end_at'] = null;
            }

            // Si le cours est gratuit, forcer le prix à 0 pour éviter les incohérences
            if ($request->boolean('is_free')) {
                $data['price'] = 0;
                $data['is_free'] = true;
            } else {
                $data['is_free'] = false;
            }

            // Gérer is_downloadable
            $data['is_downloadable'] = $request->boolean('is_downloadable', false);

            // Gérer les champs YouTube pour la vidéo de prévisualisation
            // Note: Ces champs ne sont peut-être pas dans le formulaire instructeur, mais on les gère pour la cohérence
            if ($request->has('video_preview_youtube_id')) {
                if ($request->filled('video_preview_youtube_id')) {
                    $youtubeId = $request->input('video_preview_youtube_id');
                    // Extraire l'ID YouTube de l'URL si nécessaire
                    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtubeId, $matches)) {
                        $data['video_preview_youtube_id'] = $matches[1];
                    } else {
                        $data['video_preview_youtube_id'] = $youtubeId;
                    }
                    $data['video_preview_is_unlisted'] = $request->boolean('video_preview_is_unlisted', false);
                    // Si YouTube est utilisé, vider le champ video_preview (sauf si c'est une URL externe)
                    if (!$request->filled('video_preview') || !filter_var($request->input('video_preview'), FILTER_VALIDATE_URL)) {
                        $data['video_preview'] = null;
                    }
                } else {
                    // Si le champ est présent mais vide, on le vide
                    $data['video_preview_youtube_id'] = null;
                    $data['video_preview_is_unlisted'] = false;
                }
            }

            // Gérer l'upload de l'image (thumbnail)
            if ($request->hasFile('thumbnail')) {
                $result = $this->fileUploadService->uploadImage(
                    $request->file('thumbnail'),
                    'courses/thumbnails',
                    $course->thumbnail,
                    1920
                );
                $data['thumbnail'] = $result['path'];
            } elseif ($request->filled('thumbnail_chunk_path')) {
                $chunkPath = $this->sanitizeUploadedPath($request->input('thumbnail_chunk_path'));
                if ($chunkPath) {
                    $newThumbnail = $this->fileUploadService->promoteTemporaryFile(
                        $chunkPath,
                        'courses/thumbnails'
                    );
                    if ($course->thumbnail && $course->thumbnail !== $newThumbnail) {
                        $this->fileUploadService->deleteFile($course->thumbnail);
                    }
                    $data['thumbnail'] = $newThumbnail;
                }
            }

            // Gérer l'upload de la vidéo de prévisualisation
            if ($request->hasFile('video_preview_file')) {
                $result = $this->fileUploadService->uploadVideo(
                    $request->file('video_preview_file'),
                    'courses/previews',
                    $course->video_preview && !filter_var($course->video_preview, FILTER_VALIDATE_URL) ? $course->video_preview : null
                );
                $data['video_preview'] = $result['path'];
            } elseif ($request->hasFile('video_preview')) {
                // Fallback pour compatibilité
                $result = $this->fileUploadService->uploadVideo(
                    $request->file('video_preview'),
                    'courses/previews',
                    $course->video_preview && !filter_var($course->video_preview, FILTER_VALIDATE_URL) ? $course->video_preview : null
                );
                $data['video_preview'] = $result['path'];
            } elseif ($request->filled('video_preview_path')) {
                $sanitizedPath = $this->sanitizeUploadedPath($request->string('video_preview_path')->toString());
                if ($sanitizedPath) {
                    $finalPath = $this->fileUploadService->promoteTemporaryFile(
                        $sanitizedPath,
                        'courses/previews'
                    );
                    if ($course->video_preview && !filter_var($course->video_preview, FILTER_VALIDATE_URL)
                        && $course->video_preview !== $finalPath) {
                        $this->fileUploadService->deleteFile($course->video_preview);
                    }
                    $data['video_preview'] = $finalPath;
                }
            } elseif ($request->filled('video_preview') && filter_var($request->input('video_preview'), FILTER_VALIDATE_URL)) {
                // Si c'est une URL externe, l'utiliser directement
                $data['video_preview'] = $request->input('video_preview');
            }

            // Gérer le fichier de téléchargement spécifique
            if ($request->hasFile('download_file_path')) {
                try {
                    $oldPath = $course->download_file_path && !filter_var($course->download_file_path, FILTER_VALIDATE_URL) 
                        ? $course->download_file_path 
                        : null;
                    $result = $this->fileUploadService->uploadDocument(
                        $request->file('download_file_path'),
                        'courses/downloads',
                        $oldPath
                    );
                    $data['download_file_path'] = $result['path'];
                } catch (\Exception $e) {
                    Log::error('Erreur upload download_file_path: ' . $e->getMessage());
                    throw $e;
                }
            } elseif ($request->filled('download_file_chunk_path')) {
                $chunkPath = $this->sanitizeUploadedPath($request->input('download_file_chunk_path'));
                if ($chunkPath) {
                    $oldPath = $course->download_file_path && !filter_var($course->download_file_path, FILTER_VALIDATE_URL) 
                        ? $course->download_file_path 
                        : null;
                    $newPath = $this->fileUploadService->promoteTemporaryFile(
                        $chunkPath,
                        'courses/downloads'
                    );
                    if ($oldPath && $oldPath !== $newPath) {
                        $this->fileUploadService->deleteFile($oldPath);
                    }
                    $data['download_file_path'] = $newPath;
                }
            } elseif ($request->filled('download_file_url')) {
                // Si une URL externe est fournie, l'utiliser
                $data['download_file_path'] = $request->download_file_url;
            }

            $course->update($data);

            // Gérer les sections et leçons si fournies
            if ($request->filled('sections')) {
                // Supprimer les sections existantes qui ne sont plus dans la requête
                $existingSectionIds = collect($request->input('sections', []))
                    ->filter(fn($section) => isset($section['id']))
                    ->pluck('id')
                    ->toArray();
                
                $course->sections()->whereNotIn('id', $existingSectionIds)->delete();

                $sections = $request->input('sections', []);
                foreach ($sections as $sectionIndex => $sectionData) {
                    $sectionTitle = $sectionData['title'] ?? null;
                    if (!filled($sectionTitle)) {
                        continue;
                    }

                    // Mettre à jour ou créer la section
                    if (isset($sectionData['id']) && $sectionData['id']) {
                        $section = $course->sections()->find($sectionData['id']);
                        if ($section) {
                            $section->update([
                                'title' => $sectionTitle,
                                'description' => $sectionData['description'] ?? '',
                                'sort_order' => $sectionIndex + 1,
                            ]);
                        } else {
                            $section = $course->sections()->create([
                                'title' => $sectionTitle,
                                'description' => $sectionData['description'] ?? '',
                                'sort_order' => $sectionIndex + 1,
                                'is_published' => true,
                            ]);
                        }
                    } else {
                        $section = $course->sections()->create([
                            'title' => $sectionTitle,
                            'description' => $sectionData['description'] ?? '',
                            'sort_order' => $sectionIndex + 1,
                            'is_published' => true,
                        ]);
                    }

                    // Gérer les leçons de cette section
                    $lessons = $sectionData['lessons'] ?? [];
                    if (is_array($lessons) && !empty($lessons)) {
                        // Supprimer les leçons existantes qui ne sont plus dans la requête
                        $existingLessonIds = collect($lessons)
                            ->filter(fn($lesson) => isset($lesson['id']))
                            ->pluck('id')
                            ->toArray();
                        
                        $section->lessons()->whereNotIn('id', $existingLessonIds)->delete();

                        foreach ($lessons as $lessonIndex => $lessonData) {
                            $lessonTitle = $lessonData['title'] ?? null;
                            $lessonType = $lessonData['type'] ?? null;

                            if (!filled($lessonTitle) || !filled($lessonType)) {
                                continue;
                            }

                            $filePath = null;
                            $chunkPath = $this->sanitizeUploadedPath($lessonData['content_file_path'] ?? null);
                            if ($chunkPath) {
                                $filePath = $this->fileUploadService->promoteTemporaryFile(
                                    $chunkPath,
                                    'courses/lessons'
                                );
                            }
                            if ($request->hasFile("sections.$sectionIndex.lessons.$lessonIndex.content_file")) {
                                $uploadedFile = $request->file("sections.$sectionIndex.lessons.$lessonIndex.content_file");

                                try {
                                    $mimeType = $uploadedFile->getMimeType();
                                    if ($mimeType && str_starts_with($mimeType, 'video/')) {
                                        $result = $this->fileUploadService->uploadVideo($uploadedFile, 'courses/lessons', null);
                                    } elseif ($mimeType && str_starts_with($mimeType, 'application/')) {
                                        $result = $this->fileUploadService->uploadDocument($uploadedFile, 'courses/lessons', null);
                                    } else {
                                        $result = $this->fileUploadService->upload($uploadedFile, 'courses/lessons', null);
                                    }
                                    $filePath = $result['path'];
                                } catch (\Throwable $e) {
                                    Log::error('Erreur lors du téléversement du fichier de leçon', [
                                        'provider_id' => auth()->id(),
                                        'content_id' => $course->id,
                                        'section_index' => $sectionIndex,
                                        'lesson_index' => $lessonIndex,
                                        'message' => $e->getMessage(),
                                    ]);
                                    throw $e;
                                }
                            }

                            $lessonDataToSave = [
                                'content_id' => $course->id,
                                'title' => $lessonTitle,
                                'description' => $lessonData['description'] ?? null,
                                'type' => $lessonType,
                                'content_url' => $filePath ?: ($lessonData['content_url'] ?? null),
                                'file_path' => $filePath,
                                'content_text' => $lessonData['content_text'] ?? null,
                                'duration' => isset($lessonData['duration']) ? (int) $lessonData['duration'] : 0,
                                'sort_order' => $lessonIndex + 1,
                                'is_published' => true,
                                'is_preview' => !empty($lessonData['is_preview']),
                            ];

                            // Mettre à jour ou créer la leçon
                            if (isset($lessonData['id']) && $lessonData['id']) {
                                $lesson = $section->lessons()->find($lessonData['id']);
                                if ($lesson) {
                                    $lesson->update($lessonDataToSave);
                                } else {
                                    $section->lessons()->create($lessonDataToSave);
                                }
                            } else {
                                $section->lessons()->create($lessonDataToSave);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('provider.contents.edit', $course)
                ->with('success', 'Cours mis à jour avec succès.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            Log::error('Erreur de validation lors de la mise à jour du cours', [
                'provider_id' => auth()->id(),
                'content_id' => $course->id,
                'errors' => $e->errors(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Erreur lors de la mise à jour du cours', [
                'provider_id' => auth()->id(),
                'content_id' => $course->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Impossible de mettre à jour le cours pour le moment. Erreur: ' . $e->getMessage()]);
        }
    }

    public function destroy(Course $course)
    {
        $this->ensureCanManageCourse($course);
        
        $course->delete();
        
        if (Route::has('provider.contents.index')) {
            return redirect()->route('provider.contents.index')
                ->with('success', 'Cours supprimé avec succès.');
        }

        return redirect('/provider/contents')
            ->with('success', 'Cours supprimé avec succès.');
    }

    public function publish(Course $course)
    {
        $this->ensureCanManageCourse($course);
        
        $course->update(['is_published' => true]);
        
        return redirect()->back()
            ->with('success', 'Cours publié avec succès.');
    }

    public function unpublish(Course $course)
    {
        $this->ensureCanManageCourse($course);
        
        $course->update(['is_published' => false]);
        
        return redirect()->back()
            ->with('success', 'Cours retiré de la publication.');
    }

    public function lesson(Course $course, CourseLesson $lesson)
    {
        // Vérifier que le cours est publié
        if (!$course->is_published) {
            abort(404, 'Ce cours n\'est pas disponible.');
        }

        // Vérifier que la leçon appartient au cours
        if ($lesson->content_id !== $course->id) {
            abort(404);
        }

        // Vérifier que la leçon est en aperçu ou que l'utilisateur est inscrit
        if (!$lesson->is_preview) {
            // Vérifier si l'utilisateur est inscrit au cours
            if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
                return redirect()->route('contents.show', $course)
                    ->with('error', 'Vous devez être inscrit à ce cours pour accéder à cette leçon.');
            }
        }

        // Charger les données nécessaires
        $course->load(['sections.lessons' => function($query) {
            $query->orderBy('sort_order');
        }]);

        // Trouver la leçon précédente et suivante
        $allLessons = $course->lessons()->orderBy('sort_order')->get();
        $currentIndex = $allLessons->search(function($item) use ($lesson) {
            return $item->id === $lesson->id;
        });

        $previousLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1] : null;

        return view('contents.lesson', compact('course', 'lesson', 'previousLesson', 'nextLesson'));
    }

    /**
     * Obtenir des cours recommandés basés sur plusieurs critères
     * Utilise les mêmes filtres que le panier (exclut les cours gratuits, déjà achetés et dans le panier)
     */
    private function getRecommendedCourses(Course $course)
    {
        // Obtenir les IDs des cours à exclure (même logique que le panier)
        $excludedCourseIds = $this->getExcludedCourseIds();
        
        $recommendations = collect();

        // 1. Cours de la même catégorie avec un bon rating
        $categoryCourses = Course::published()
            ->where('category_id', $course->category_id)
            ->where('id', '!=', $course->id)
            ->where('is_free', false) // Exclure les cours gratuits
            ->whereNotIn('id', $excludedCourseIds) // Exclure les cours déjà achetés et dans le panier
            ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->withCount(['reviews', 'enrollments'])
            ->withAvg('reviews', 'rating')
            ->orderBy('reviews_avg_rating', 'desc')
            ->orderBy('enrollments_count', 'desc')
            ->limit(2)
            ->get();

        $recommendations = $recommendations->merge($categoryCourses);

        // 2. Cours du même niveau de difficulté
        $levelCourses = Course::published()
            ->where('level', $course->level)
            ->where('id', '!=', $course->id)
            ->where('is_free', false) // Exclure les cours gratuits
            ->whereNotIn('id', $excludedCourseIds) // Exclure les cours déjà achetés et dans le panier
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->withCount(['reviews', 'enrollments'])
            ->withAvg('reviews', 'rating')
            ->orderBy('enrollments_count', 'desc')
            ->orderBy('reviews_avg_rating', 'desc')
            ->limit(1)
            ->get();

        $recommendations = $recommendations->merge($levelCourses);

        // 3. Cours populaires récents
        $popularCourses = Course::published()
            ->where('id', '!=', $course->id)
            ->where('is_free', false) // Exclure les cours gratuits
            ->whereNotIn('id', $excludedCourseIds) // Exclure les cours déjà achetés et dans le panier
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->withCount(['reviews', 'enrollments'])
            ->withAvg('reviews', 'rating')
            ->orderBy('enrollments_count', 'desc')
            ->orderBy('reviews_avg_rating', 'desc')
            ->limit(1)
            ->get();

        $recommendations = $recommendations->merge($popularCourses);

        // 4. Si l'utilisateur est connecté, recommander basé sur ses préférences
        if (auth()->check()) {
            $userEnrollments = auth()->user()->enrollments()
                ->with('course.category')
                ->get()
                ->pluck('course.category_id')
                ->unique()
                ->toArray();

            if (!empty($userEnrollments)) {
                $userPreferenceCourses = Course::published()
                    ->whereIn('category_id', $userEnrollments)
                    ->where('id', '!=', $course->id)
                    ->where('is_free', false) // Exclure les cours gratuits
                    ->whereNotIn('id', $excludedCourseIds) // Exclure les cours déjà achetés et dans le panier
                    ->whereNotIn('id', $recommendations->pluck('id'))
                    ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                    ->withCount(['reviews', 'enrollments'])
                    ->withAvg('reviews', 'rating')
                    ->orderBy('enrollments_count', 'desc')
                    ->orderBy('reviews_avg_rating', 'desc')
                    ->limit(1)
                    ->get();

                $recommendations = $recommendations->merge($userPreferenceCourses);
            }
        }

        // Mélanger et limiter à 4 cours, puis ajouter les statistiques
        $finalRecommendations = $recommendations->shuffle()->take(4);
        
        return $finalRecommendations->map(function($course) {
            $course->stats = $course->getCourseStats();
            return $course;
        });
    }

    /**
     * Vérifier si un cours a été acheté ou si l'utilisateur y est inscrit
     */
    private function isCoursePurchased($course)
    {
        if (!auth()->check()) {
            return false;
        }
        
        $userId = auth()->id();
        
        // Vérifier si l'utilisateur est inscrit au cours
        $isEnrolled = $course->isEnrolledBy($userId);
        if ($isEnrolled) {
            return true;
        }
        
        // Vérifier si l'utilisateur a acheté le cours (pour les cours payants)
        if (!$course->is_free) {
            $hasPurchased = \App\Models\Order::where('user_id', $userId)
                ->where('status', 'paid')
                ->whereHas('orderItems', function($query) use ($course) {
                    $query->where('content_id', $course->id);
                })
                ->exists();
            
            if ($hasPurchased) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtenir les IDs des cours à exclure des recommandations
     * Utilise la même logique que le panier
     */
    private function getExcludedCourseIds()
    {
        $excludedIds = collect();
        
        // 1. Exclure les cours déjà dans le panier
        $cartItems = session('cart', []);
        if (!empty($cartItems) && is_array($cartItems)) {
            $cartCourseIds = collect($cartItems)->map(function($item) {
                // Gérer les deux structures possibles : avec 'course.id' ou directement 'id'
                if (isset($item['course']['id'])) {
                    return $item['course']['id'];
                } elseif (isset($item['id'])) {
                    return $item['id'];
                } elseif (is_object($item) && isset($item->course->id)) {
                    return $item->course->id;
                } elseif (is_object($item) && isset($item->id)) {
                    return $item->id;
                }
                return null;
            })->filter()->toArray();
            
            $excludedIds = $excludedIds->merge($cartCourseIds);
        }
        
        // 2. Exclure les cours gratuits (toujours)
        $freeCourseIds = Course::published()
            ->where('is_free', true)
            ->pluck('id')
            ->toArray();
        $excludedIds = $excludedIds->merge($freeCourseIds);
        
        // 3. Si l'utilisateur est connecté, exclure les cours déjà achetés ou auxquels il est inscrit
        if (auth()->check()) {
            $purchasedCourseIds = auth()->user()
                ->enrollments()
                ->whereIn('status', ['active', 'completed']) // Inclure les cours actifs ET complétés
                ->pluck('content_id')
                ->toArray();
            $excludedIds = $excludedIds->merge($purchasedCourseIds);
        }
        
        return $excludedIds->unique()->values()->toArray();
    }

    /**
     * Ajouter les statistiques calculées à chaque cours
     */
    private function addCourseStatistics($courses)
    {
        return $courses->map(function($course) {
            $course->stats = [
                'total_lessons' => $course->sections->sum(function($section) {
                    return $section->lessons->count();
                }),
                'total_duration' => $course->sections->sum(function($section) {
                    return $section->lessons->sum('duration');
                }),
                'total_customers' => $course->total_customers, // Nombre d'inscriptions
                'purchases_count' => $course->purchases_count, // Nombre d'achats (pour tous les cours)
                // Statistiques supplémentaires pour les produits téléchargeables
                'total_downloads' => $course->is_downloadable ? $course->total_downloads_count : null,
                'unique_downloads' => $course->is_downloadable ? $course->unique_downloads_count : null,
                'total_revenue' => $course->is_downloadable ? $course->total_purchases_revenue : null,
                'average_rating' => $course->reviews->avg('rating') ?? 0,
                'total_reviews' => $course->reviews->count(),
            ];
            return $course;
        });
    }

    private function ensureCanManageCourse(Course $course): void
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

    private function sanitizeUploadedPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $clean = trim($path);

        if ($clean === '') {
            return null;
        }

        $clean = str_replace('..', '', $clean);
        $clean = ltrim($clean, '/');

        if (str_starts_with($clean, 'storage/')) {
            $clean = ltrim(substr($clean, strlen('storage/')), '/');
        }

        $allowedPrefixes = [
            FileUploadService::TEMPORARY_BASE_PATH,
            'courses/thumbnails',
            'courses/previews',
            'courses/lessons',
            'courses/downloads',
        ];

        foreach ($allowedPrefixes as $prefix) {
            $normalized = rtrim($prefix, '/');
            if ($clean === $normalized || str_starts_with($clean, $normalized . '/')) {
                return $clean;
            }
        }

        return null;
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = Str::random(8);
        }

        $slug = $base;
        $counter = 1;

        while (Course::where('slug', $slug)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    public function previewData(Course $course)
    {
        // Vérifier que le cours est publié
        if (!$course->is_published) {
            abort(404, 'Ce cours n\'est pas disponible.');
        }

        try {
            $fileHelper = app(\App\Helpers\FileHelper::class);
            // Récupérer uniquement les leçons vidéo d'aperçu publiées qui ont du contenu vidéo
            $previewVideoLessons = $course->sections()
                ->with(['lessons' => function($query) {
                    $query->where('type', 'video')
                          ->where('is_published', true)
                          ->where('is_preview', true)
                          ->where(function($q) {
                              $q->whereNotNull('youtube_video_id')
                                ->orWhereNotNull('file_path')
                                ->orWhereNotNull('content_url');
                          })
                          ->orderBy('sort_order');
                }])
                ->get()
                ->flatMap(function($section) {
                    return $section->lessons->map(function($lesson) use ($section) {
                        // Déterminer l'URL de la vidéo
                        $videoUrl = null;
                        if ($lesson->file_path && !filter_var($lesson->file_path, FILTER_VALIDATE_URL)) {
                            $videoUrl = $lesson->file_url;
                        } elseif ($lesson->content_url) {
                            $videoUrl = filter_var($lesson->content_url, FILTER_VALIDATE_URL)
                                ? $lesson->content_url
                                : $lesson->content_file_url;
                        }
                        
                        return [
                            'id' => $lesson->id,
                            'title' => $lesson->title ?? 'Sans titre',
                            'section' => $section->title ?? 'Sans section',
                            'duration' => $lesson->duration ?? 0,
                            'youtube_id' => $lesson->youtube_video_id ?? null,
                            'is_unlisted' => $lesson->is_unlisted ?? false,
                            'video_url' => $videoUrl,
                            'is_preview' => true,
                        ];
                    });
                });

            $previews = [];
            
            // Ajouter l'aperçu principal du cours
            if ($course->video_preview_youtube_id || $course->video_preview) {
                $previews[] = [
                    'id' => 0,
                    'title' => 'Aperçu du cours',
                    'section' => '',
                    'duration' => null,
                    'youtube_id' => $course->video_preview_youtube_id,
                    'is_unlisted' => $course->video_preview_is_unlisted,
                    'video_url' => $course->video_preview
                        ? $course->video_preview_url
                        : null,
                    'is_main' => true,
                ];
            }

            // Ajouter toutes les leçons vidéo d'aperçu
            foreach ($previewVideoLessons as $lesson) {
                $previews[] = array_merge($lesson, [
                    'is_main' => false,
                    'is_preview' => true
                ]);
            }

            return response()->json([
                'preview' => $previews
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans previewData: ' . $e->getMessage(), [
                'content_id' => $course->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors du chargement des aperçus',
                'message' => $e->getMessage(),
                'preview' => []
            ], 500);
        }
    }
}
