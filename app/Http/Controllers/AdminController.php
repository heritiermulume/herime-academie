<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Category;
use App\Models\Order;
use App\Models\Enrollment;
use App\Models\Announcement;
use App\Models\Partner;
use App\Models\Testimonial;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Setting;
use App\Models\CourseDownload;
use App\Models\InstructorApplication;
use App\Traits\DatabaseCompatibility;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    use DatabaseCompatibility;

    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function dashboard()
    {
        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'total_students' => User::students()->count(),
            'total_instructors' => User::instructors()->count(),
            'total_courses' => Course::count(),
            'published_courses' => Course::published()->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'paid_orders' => Order::where('status', 'paid')->count(),
            'total_revenue' => Order::where('status', 'paid')->sum('total'),
            'total_enrollments' => Enrollment::count(),
        ];

        // Revenus par mois (6 derniers mois)
        $revenueByMonth = Order::where('status', 'paid')
            ->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m', 'month') . ', SUM(total) as revenue')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Cours les plus populaires
        $popularCourses = Course::published()
            ->with(['instructor', 'category'])
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get();

        // Inscriptions récentes
        $recentEnrollments = Enrollment::with(['user', 'course.instructor'])
            ->latest()
            ->limit(10)
            ->get();

        // Commandes récentes
        $recentOrders = Order::with(['user', 'orderItems.course'])
            ->latest()
            ->limit(10)
            ->get();

        $baseCurrency = Setting::getBaseCurrency();
        return view('admin.dashboard', compact(
            'stats', 
            'revenueByMonth', 
            'popularCourses', 
            'recentEnrollments', 
            'recentOrders',
            'baseCurrency'
        ));
    }

    public function analytics()
    {
        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'total_courses' => Course::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', 'paid')->sum('total'),
            'total_enrollments' => Enrollment::count(),
        ];

        // Revenus par mois (6 derniers mois)
        $revenueByMonth = Order::where('status', 'paid')
            ->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m', 'month') . ', SUM(total) as revenue')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Analytics détaillées
        $courseStats = Course::selectRaw('
            COUNT(*) as total_courses,
            SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) as published_courses
        ')->first();
        
        // Calculer les statistiques dynamiquement
        $totalStudents = Enrollment::count();
        $averageRating = Review::avg('rating') ?? 0;
        
        $courseStats->total_students = $totalStudents;
        $courseStats->average_rating = $averageRating;

        $userGrowth = User::selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m', 'month') . ', COUNT(*) as count')
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $categoryStats = Category::withCount('courses')
            ->orderBy('courses_count', 'desc')
            ->get();

        $instructorStats = User::instructors()
            ->withCount('courses')
            ->withCount(['courses as total_students' => function($query) {
                $query->withCount('enrollments');
            }])
            ->orderBy('courses_count', 'desc')
            ->limit(10)
            ->get();

        // Cours les plus populaires
        $popularCourses = Course::published()
            ->with(['instructor', 'category'])
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get();

        // Paiements: répartition par statut et par méthode
        $paymentsByStatus = Payment::select('status')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get();

        $paymentsByMethod = Payment::select('payment_method')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        $baseCurrency = Setting::getBaseCurrency();
        return view('admin.analytics', compact(
            'stats',
            'revenueByMonth',
            'courseStats',
            'userGrowth',
            'categoryStats',
            'instructorStats',
            'popularCourses',
            'paymentsByStatus',
            'paymentsByMethod',
            'baseCurrency'
        ));
    }

    // Gestion des utilisateurs
    public function users(Request $request)
    {
        $query = User::withCount(['courses', 'enrollments']);

        // Recherche par nom ou email
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par rôle
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        // Filtre par statut
        if ($request->filled('status')) {
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->get('status') === 'verified') {
                $query->where('is_verified', true);
            }
        }

        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['name', 'email', 'role', 'created_at', 'last_login_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest();
        }

        $users = $query->paginate(20)->withQueryString();

        // Statistiques pour les filtres
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'verified' => User::where('is_verified', true)->count(),
            'students' => User::where('role', 'student')->count(),
            'instructors' => User::where('role', 'instructor')->count(),
            'admins' => User::whereIn('role', ['admin', 'super_user'])->count(),
            'affiliates' => User::where('role', 'affiliate')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Mettre à jour un utilisateur
     * Seuls certains champs peuvent être modifiés localement (rôle, is_active)
     * Les autres données (nom, email, photo) viennent du SSO et seront synchronisées
     */
    public function updateUser(Request $request, User $user)
    {
        // Avec le SSO, on limite les modifications locales
        // Seuls le rôle et le statut actif peuvent être modifiés localement
        // Les autres données (nom, email, photo) viennent du SSO
        
        $request->validate([
            'role' => 'required|in:student,instructor,admin,affiliate,super_user',
            'is_active' => 'boolean',
            // Note: Les autres champs (name, email, avatar) sont gérés par le SSO
        ]);

        // Mettre à jour uniquement les champs modifiables localement
        $user->update([
            'role' => $request->role,
            'is_active' => $request->has('is_active'),
        ]);

            return redirect()->route('admin.users')
                ->with('success', 'Utilisateur mis à jour avec succès. Les données personnelles (nom, email, photo) sont gérées via Compte Herime et seront synchronisées lors de la prochaine connexion.');
    }
    
    /**
     * Synchroniser un utilisateur avec le SSO
     * Récupère les dernières données depuis le SSO
     */
    public function syncUserFromSSO(User $user)
    {
        try {
            // Si l'utilisateur a un ID SSO, on pourrait faire une requête au SSO
            // Pour l'instant, on synchronise lors de la prochaine connexion
            // Cette méthode peut être utilisée pour forcer une synchronisation
            return redirect()->route('admin.users')
                ->with('info', 'La synchronisation se fait automatiquement lors de la connexion via Compte Herime. Les données seront mises à jour lors de la prochaine connexion de l\'utilisateur.');
        } catch (\Exception $e) {
            Log::error('SSO User Sync Error', [
                'user_id' => $user->id,
                'message' => $e->getMessage()
            ]);
            
            return redirect()->route('admin.users')
                ->with('error', 'Erreur lors de la synchronisation. Les données seront mises à jour lors de la prochaine connexion.');
        }
    }

    /**
     * Afficher la page de création d'utilisateur
     * Redirige vers le SSO car la création se fait via SSO
     */
    public function createUser()
    {
        // La création d'utilisateurs se fait via le SSO
        // Rediriger vers le SSO pour créer un utilisateur
        if (config('services.sso.enabled', true)) {
            $ssoService = app(\App\Services\SSOService::class);
            $callbackUrl = route('sso.callback', [
                'redirect' => route('admin.users')
            ]);
            $ssoRegisterUrl = $ssoService->getRegisterUrl($callbackUrl);
            
            return redirect($ssoRegisterUrl)
                ->with('info', 'La création d\'utilisateurs se fait via Compte Herime. Vous allez être redirigé vers la page d\'inscription.');
        }
        
        // Fallback si SSO désactivé (ne devrait pas arriver)
        return redirect()->route('admin.users')
            ->with('error', 'Compte Herime est requis pour créer des utilisateurs.');
    }

    /**
     * Stocker un utilisateur
     * Cette méthode ne devrait jamais être appelée car la création se fait via SSO
     */
    public function storeUser(Request $request)
    {
        // La création d'utilisateurs se fait uniquement via SSO
        return redirect()->route('admin.users')
            ->with('error', 'La création d\'utilisateurs se fait uniquement via Compte Herime. Veuillez rediriger l\'utilisateur vers Compte Herime pour créer un compte.');
    }

    public function showUser(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    // Gestion des cours
    public function courses(Request $request)
    {
        $query = Course::with(['instructor', 'category', 'sections', 'lessons']);

        // Recherche par titre ou description
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtre par catégorie
        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }

        // Filtre par statut
        if ($request->filled('status')) {
            if ($request->get('status') === 'published') {
                $query->where('is_published', true);
            } elseif ($request->get('status') === 'draft') {
                $query->where('is_published', false);
            } elseif ($request->get('status') === 'free') {
                $query->where('is_free', true);
            } elseif ($request->get('status') === 'paid') {
                $query->where('is_free', false);
            }
        }

        // Filtre par instructeur
        if ($request->filled('instructor')) {
            $query->where('instructor_id', $request->get('instructor'));
        }

        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['title', 'price', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest();
        }

        $courses = $query->paginate(20)->withQueryString();

        // Données pour les filtres
        $categories = Category::active()->ordered()->get();
        $instructors = User::instructors()->get();

        // Statistiques
        $stats = [
            'total' => Course::count(),
            'published' => Course::where('is_published', true)->count(),
            'draft' => Course::where('is_published', false)->count(),
            'free' => Course::where('is_free', true)->count(),
            'paid' => Course::where('is_free', false)->count(),
        ];
        
        $baseCurrency = Setting::getBaseCurrency();
        return view('admin.courses.index', compact('courses', 'categories', 'instructors', 'stats', 'baseCurrency'));
    }

    public function createCourse()
    {
        $categories = Category::active()->ordered()->get();
        $instructors = User::instructors()->get();
        $baseCurrency = Setting::getBaseCurrency();
        return view('admin.courses.create', compact('categories', 'instructors', 'baseCurrency'));
    }

    public function storeCourse(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'instructor_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'is_downloadable' => 'boolean',
            'download_file_path' => 'nullable|file|mimes:zip,pdf,doc,docx,rar,7z,tar,gz|max:2048', // 2MB max (limite PHP)
            'download_file_url' => 'nullable|url|max:1000',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'level' => 'required|in:beginner,intermediate,advanced',
            'language' => 'required|string|max:10',
            'use_external_payment' => 'boolean',
            'external_payment_url' => 'nullable|url|max:500|required_if:use_external_payment,1',
            'external_payment_text' => 'nullable|string|max:100',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'video_preview' => 'nullable|string|max:255',
            'video_preview_file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/webm|max:1048576',
            'video_preview_youtube_id' => 'nullable|string|max:100',
            'video_preview_is_unlisted' => 'boolean',
            'requirements' => 'nullable|array',
            'what_you_will_learn' => 'nullable|array',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'sections' => 'nullable|array',
            'sections.*.title' => 'required_with:sections|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.lessons' => 'nullable|array',
            'sections.*.lessons.*.title' => 'required_with:sections.*.lessons|string|max:255',
            'sections.*.lessons.*.description' => 'nullable|string',
            'sections.*.lessons.*.type' => 'required_with:sections.*.lessons|in:video,text,quiz,assignment',
            'sections.*.lessons.*.content_url' => 'nullable|string',
            'sections.*.lessons.*.content_file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/webm,application/pdf|max:1048576',
            'sections.*.lessons.*.content_text' => 'nullable|string',
            'sections.*.lessons.*.duration' => 'nullable|integer|min:0',
            'sections.*.lessons.*.is_preview' => 'boolean',
        ], [
            'thumbnail.image' => 'Le fichier doit être une image.',
            'thumbnail.mimes' => 'Le fichier doit être de type: jpeg, png, jpg, gif, webp.',
            'thumbnail.max' => 'Le fichier ne doit pas dépasser 5MB.',
            'download_file_path.file' => 'Le fichier de téléchargement doit être un fichier valide.',
            'download_file_path.mimes' => 'Le fichier de téléchargement doit être de type: zip, pdf, doc, docx, rar, 7z, tar, gz.',
            'download_file_path.max' => 'Le fichier de téléchargement ne doit pas dépasser 2MB. Pour les fichiers plus volumineux, utilisez une URL externe.',
            'external_payment_url.required_if' => 'L\'URL de paiement externe est requise quand le paiement externe est activé.',
        ]);

        DB::beginTransaction();
        try {
            // Créer le cours
            $courseData = $request->only([
                'title', 'description', 'instructor_id', 'category_id', 'price', 'sale_price',
                'is_free', 'is_downloadable', 'is_published', 'is_featured', 'level', 'language',
                'video_preview', 'meta_description', 'meta_keywords', 'tags',
                'video_preview_youtube_id', 'video_preview_is_unlisted', 'use_external_payment',
                'external_payment_url', 'external_payment_text'
            ]);

            // Gérer l'upload de l'image de couverture
            if ($request->hasFile('thumbnail')) {
                $result = $this->fileUploadService->uploadImage(
                    $request->file('thumbnail'),
                    'courses/thumbnails',
                    null,
                    1920 // Max 1920px width
                );
                $courseData['thumbnail'] = $result['path'];
            }

            // Gérer YouTube ou upload de la vidéo de prévisualisation
            $videoPreviewYoutubeId = $request->video_preview_youtube_id;
            $isUnlisted = $request->boolean('video_preview_is_unlisted', false);
            
            // Si YouTube vidéo ID fourni, extraire et valider
            if ($videoPreviewYoutubeId) {
                $courseData['video_preview_youtube_id'] = $this->extractYouTubeVideoId($videoPreviewYoutubeId);
                $courseData['video_preview_is_unlisted'] = $isUnlisted;
            }
            
            // Gérer upload fichier si fourni
            if ($request->hasFile('video_preview_file')) {
                $result = $this->fileUploadService->uploadVideo(
                    $request->file('video_preview_file'),
                    'courses/previews',
                    null
                );
                $courseData['video_preview'] = $result['path'];
            }

            // Gérer le fichier de téléchargement spécifique
            if ($request->hasFile('download_file_path')) {
                try {
                    $result = $this->fileUploadService->uploadDocument(
                        $request->file('download_file_path'),
                        'courses/downloads',
                        null
                    );
                    $courseData['download_file_path'] = $result['path'];
                } catch (\Exception $e) {
                    DB::rollback();
                    \Log::error('Erreur upload download_file_path: ' . $e->getMessage(), [
                        'file' => $request->file('download_file_path')->getClientOriginalName(),
                        'size' => $request->file('download_file_path')->getSize(),
                        'error' => $e->getTraceAsString()
                    ]);
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['download_file_path' => 'Erreur lors de l\'upload du fichier : ' . $e->getMessage()]);
                }
            } elseif ($request->filled('download_file_url')) {
                // Si une URL externe est fournie, l'utiliser
                $courseData['download_file_path'] = $request->download_file_url;
            }

            // Traiter les tableaux
            $courseData['requirements'] = $request->input('requirements', []);
            $courseData['what_you_will_learn'] = $request->input('what_you_will_learn', []);
            $courseData['slug'] = \Str::slug($request->title);

            $course = Course::create($courseData);

            // Créer les sections et leçons
            if ($request->has('sections')) {
                foreach ($request->sections as $sectionIndex => $sectionData) {
                    $section = $course->sections()->create([
                        'title' => $sectionData['title'],
                        'description' => $sectionData['description'] ?? '',
                        'sort_order' => $sectionIndex + 1,
                        'is_published' => true,
                    ]);

                    // Créer les leçons de cette section
                    if (isset($sectionData['lessons'])) {
                        foreach ($sectionData['lessons'] as $lessonIndex => $lessonData) {
                            $filePath = null;
                            // Récupérer le fichier uploadé via l'indexation de la requête
                            $uploaded = $request->file("sections.$sectionIndex.lessons.$lessonIndex.content_file");
                            if ($uploaded) {
                                // Déterminer le type de fichier
                                $mimeType = $uploaded->getMimeType();
                                if (strpos($mimeType, 'video/') === 0) {
                                    $result = $this->fileUploadService->uploadVideo($uploaded, 'courses/lessons', null);
                                } elseif (strpos($mimeType, 'application/') === 0) {
                                    $result = $this->fileUploadService->uploadDocument($uploaded, 'courses/lessons', null);
                                } else {
                                    $result = $this->fileUploadService->upload($uploaded, 'courses/lessons', null);
                                }
                                $filePath = $result['path'];
                            }

                            $section->lessons()->create([
                                'course_id' => $course->id,
                                'title' => $lessonData['title'],
                                'description' => $lessonData['description'] ?? '',
                                'type' => $lessonData['type'],
                                'content_url' => $filePath ? $filePath : ($lessonData['content_url'] ?? null),
                                'content_text' => $lessonData['content_text'] ?? null,
                                'duration' => $lessonData['duration'] ?? 0,
                                'sort_order' => $lessonIndex + 1,
                                'is_published' => true,
                                'is_preview' => $lessonData['is_preview'] ?? false,
                            ]);
                        }
                    }
                }
            }

            // Les statistiques (duration, lessons_count, etc.) sont maintenant calculées dynamiquement
            // via les accesseurs du modèle Course

            DB::commit();

            $lessonsCount = $course->lessons()->count();
            return redirect()->route('admin.courses')
                ->with('success', 'Cours créé avec succès avec ' . $lessonsCount . ' leçons.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erreur lors de la création du cours: ' . $e->getMessage()]);
        }
    }

    public function editCourse(Course $course)
    {
        $categories = Category::active()->ordered()->get();
        $instructors = User::instructors()->get();
        $baseCurrency = Setting::getBaseCurrency();
        return view('admin.courses.edit', compact('course', 'categories', 'instructors', 'baseCurrency'));
    }

    public function updateCourse(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'instructor_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'is_downloadable' => 'boolean',
            'download_file_path' => 'nullable|file|mimes:zip,pdf,doc,docx,rar,7z,tar,gz|max:2048', // 2MB max (limite PHP)
            'download_file_url' => 'nullable|url|max:1000',
            'use_external_payment' => 'boolean',
            'external_payment_url' => 'nullable|url|max:500|required_if:use_external_payment,1',
            'external_payment_text' => 'nullable|string|max:100',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'level' => 'required|in:beginner,intermediate,advanced',
            'language' => 'required|string|max:10',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'video_preview' => 'nullable|string|max:255',
            'video_preview_file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/webm|max:1048576',
            'video_preview_youtube_id' => 'nullable|string|max:100',
            'video_preview_is_unlisted' => 'boolean',
            'requirements' => 'nullable|array',
            'what_you_will_learn' => 'nullable|array',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
        ], [
            'thumbnail.image' => 'Le fichier doit être une image.',
            'thumbnail.mimes' => 'Le fichier doit être de type: jpeg, png, jpg, gif, webp.',
            'thumbnail.max' => 'Le fichier ne doit pas dépasser 5MB.',
            'download_file_path.mimes' => 'Le fichier de téléchargement doit être de type: zip, pdf, doc, docx, rar, 7z, tar, gz.',
            'download_file_path.max' => 'Le fichier de téléchargement ne doit pas dépasser 2MB. Pour les fichiers plus volumineux, utilisez une URL externe.',
            'external_payment_url.required_if' => 'L\'URL de paiement externe est requise quand le paiement externe est activé.',
        ]);

        $data = $request->only([
            'title', 'description', 'instructor_id', 'category_id', 'price', 'sale_price',
            'is_free', 'is_downloadable', 'use_external_payment', 'external_payment_url', 'external_payment_text',
            'is_published', 'is_featured', 'level', 'language',
            'video_preview', 'meta_description', 'meta_keywords', 'tags',
            'video_preview_youtube_id', 'video_preview_is_unlisted'
        ]);

        // Gérer l'upload de l'image de couverture
        if ($request->hasFile('thumbnail')) {
            $result = $this->fileUploadService->uploadImage(
                $request->file('thumbnail'),
                'courses/thumbnails',
                $course->thumbnail,
                1920 // Max 1920px width
            );
            $data['thumbnail'] = $result['path'];
        }

        // Gérer YouTube ou upload de la vidéo de prévisualisation
        $videoPreviewYoutubeId = $request->video_preview_youtube_id;
        $isUnlisted = $request->boolean('video_preview_is_unlisted', false);
        
        // Si YouTube vidéo ID fourni, extraire et valider
        if ($videoPreviewYoutubeId) {
            $data['video_preview_youtube_id'] = $this->extractYouTubeVideoId($videoPreviewYoutubeId);
            $data['video_preview_is_unlisted'] = $isUnlisted;
        }
        
        // Gérer upload fichier si fourni
        if ($request->hasFile('video_preview_file')) {
            $result = $this->fileUploadService->uploadVideo(
                $request->file('video_preview_file'),
                'courses/previews',
                $course->video_preview && !filter_var($course->video_preview, FILTER_VALIDATE_URL) ? $course->video_preview : null
            );
            $data['video_preview'] = $result['path'];
        }

        // Gérer le fichier de téléchargement spécifique
        if ($request->has('remove_download_file') && $request->remove_download_file) {
            // Supprimer l'ancien fichier s'il existe
            if ($course->download_file_path && !filter_var($course->download_file_path, FILTER_VALIDATE_URL)) {
                $this->fileUploadService->deleteFile($course->download_file_path);
            }
            $data['download_file_path'] = null;
        } elseif ($request->hasFile('download_file_path')) {
            // Déterminer l'ancien chemin
            $oldPath = null;
            if ($course->download_file_path && !filter_var($course->download_file_path, FILTER_VALIDATE_URL)) {
                $oldPath = $course->download_file_path;
            }
            // Stocker le nouveau fichier
            try {
                $result = $this->fileUploadService->uploadDocument(
                    $request->file('download_file_path'),
                    'courses/downloads',
                    $oldPath
                );
                $data['download_file_path'] = $result['path'];
            } catch (\Exception $e) {
                \Log::error('Erreur upload download_file_path (update): ' . $e->getMessage(), [
                    'file' => $request->file('download_file_path')->getClientOriginalName(),
                    'size' => $request->file('download_file_path')->getSize(),
                    'error' => $e->getTraceAsString()
                ]);
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['download_file_path' => 'Erreur lors de l\'upload du fichier : ' . $e->getMessage()]);
            }
        } elseif ($request->filled('download_file_url')) {
            // Si une URL externe est fournie, l'utiliser
            $data['download_file_path'] = $request->download_file_url;
        }

        // Traiter les tableaux
        $data['requirements'] = $request->input('requirements', []);
        $data['what_you_will_learn'] = $request->input('what_you_will_learn', []);
        $data['slug'] = \Str::slug($request->title);

        $course->update($data);

        return redirect()->route('admin.courses')
            ->with('success', 'Cours mis à jour avec succès.');
    }

    public function showCourse(Course $course)
    {
        $course->load(['instructor', 'category', 'sections.lessons']);
        $baseCurrency = Setting::getBaseCurrency();
        return view('admin.courses.show', compact('course', 'baseCurrency'));
    }

    public function destroyCourse(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses')
            ->with('success', 'Cours supprimé avec succès.');
    }

    // Gestion des catégories
    public function categories(Request $request)
    {
        $query = Category::withCount('courses');

        // Filtre par recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Tri
        $sortBy = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        
        if (in_array($sortBy, ['name', 'created_at', 'courses_count'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->ordered();
        }

        $categories = $query->paginate(20)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function createCategory()
    {
        return view('admin.categories.create');
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:categories,slug',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string',
        ]);

        Category::create($request->all());

        return redirect()->route('admin.categories')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function editCategory(Category $category)
    {
        return response()->json($category);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category->update($request->all());

        return redirect()->route('admin.categories')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories')
            ->with('success', 'Catégorie supprimée avec succès.');
    }

    // Gestion des annonces
    public function announcements()
    {
        $announcements = Announcement::latest()->paginate(20);
        return view('admin.announcements.index', compact('announcements'));
    }

    public function createAnnouncement()
    {
        return view('admin.announcements.create');
    }

    public function storeAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'button_text' => 'nullable|string|max:255',
            'button_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        Announcement::create($request->all());

        return redirect()->route('admin.announcements')
            ->with('success', 'Annonce créée avec succès.');
    }

    public function editAnnouncement(Announcement $announcement)
    {
        return response()->json($announcement);
    }

    public function updateAnnouncement(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'button_text' => 'nullable|string|max:255',
            'button_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        $announcement->update($request->all());

        return redirect()->route('admin.announcements')
            ->with('success', 'Annonce mise à jour avec succès.');
    }

    public function destroyAnnouncement(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('admin.announcements')
            ->with('success', 'Annonce supprimée avec succès.');
    }

    // Gestion des partenaires
    public function partners()
    {
        $partners = Partner::ordered()->paginate(20);
        return view('admin.partners.index', compact('partners'));
    }

    public function createPartner()
    {
        return view('admin.partners.create');
    }

    public function storePartner(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|url',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Partner::create($request->all());

        return redirect()->route('admin.partners')
            ->with('success', 'Partenaire ajouté avec succès.');
    }

    public function editPartner(Partner $partner)
    {
        return response()->json($partner);
    }

    public function updatePartner(Request $request, Partner $partner)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|url',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $partner->update($request->all());

        return redirect()->route('admin.partners')
            ->with('success', 'Partenaire mis à jour avec succès.');
    }

    public function destroyPartner(Partner $partner)
    {
        $partner->delete();
        return redirect()->route('admin.partners')
            ->with('success', 'Partenaire supprimé avec succès.');
    }

    /**
     * AJAX: Upload d'un fichier de leçon (vidéo/PDF) avec réponse JSON
     */
    public function uploadLessonFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:video/mp4,video/quicktime,video/webm,application/pdf|max:1048576',
        ]);

        // Déterminer le type de fichier
        $mimeType = $request->file('file')->getMimeType();
        if (strpos($mimeType, 'video/') === 0) {
            $result = $this->fileUploadService->uploadVideo($request->file('file'), 'courses/lessons', null);
        } else {
            $result = $this->fileUploadService->uploadDocument($request->file('file'), 'courses/lessons', null);
        }

        return response()->json([
            'success' => true,
            'path' => $result['path'],
            'url' => $result['url'],
        ]);
    }

    /**
     * AJAX: Upload de la vidéo de prévisualisation du cours
     */
    public function uploadVideoPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:video/mp4,video/quicktime,video/webm|max:1048576',
        ]);

        $result = $this->fileUploadService->uploadVideo($request->file('file'), 'courses/previews', null);

        return response()->json([
            'success' => true,
            'path' => $result['path'],
            'url' => $result['url'],
        ]);
    }

    // Gestion des témoignages
    public function testimonials()
    {
        $testimonials = Testimonial::ordered()->paginate(20);
        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function createTestimonial()
    {
        return view('admin.testimonials.create');
    }

    public function storeTestimonial(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'photo' => 'nullable|url',
            'testimonial' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'is_active' => 'boolean',
        ]);

        Testimonial::create($request->all());

        return redirect()->route('admin.testimonials')
            ->with('success', 'Témoignage ajouté avec succès.');
    }

    public function editTestimonial(Testimonial $testimonial)
    {
        return response()->json($testimonial);
    }

    public function updateTestimonial(Request $request, Testimonial $testimonial)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'photo' => 'nullable|url',
            'testimonial' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'is_active' => 'boolean',
        ]);

        $testimonial->update($request->all());

        return redirect()->route('admin.testimonials')
            ->with('success', 'Témoignage mis à jour avec succès.');
    }

    public function destroyTestimonial(Testimonial $testimonial)
    {
        $testimonial->delete();
        return redirect()->route('admin.testimonials')
            ->with('success', 'Témoignage supprimé avec succès.');
    }

    // Course Lessons Management
    public function courseLessons(Course $course)
    {
        $course->load(['sections.lessons' => function($query) {
            $query->orderBy('sort_order');
        }]);
        
        return view('admin.courses.lessons.index', compact('course'));
    }

    public function createLesson(Course $course)
    {
        $sections = $course->sections()->orderBy('sort_order')->get();
        return view('admin.courses.lessons.create', compact('course', 'sections'));
    }

    public function storeLesson(Request $request, Course $course)
    {
        $request->validate([
            'section_id' => 'required|exists:course_sections,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:video,text,quiz,assignment',
            'content_url' => 'nullable|string|max:500',
            'content_file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/webm,application/pdf|max:1048576',
            'content_text' => 'nullable|string',
            'duration' => 'nullable|integer|min:0',
            'is_preview' => 'boolean',
            'is_published' => 'boolean',
            'youtube_video_id' => 'nullable|string|max:100',
            'is_unlisted' => 'boolean',
        ]);

        // Get the next sort order for this section
        $nextOrder = $course->lessons()->where('section_id', $request->section_id)->max('sort_order') + 1;

        // Gérer YouTube ou upload de fichier
        $contentUrl = $request->content_url;
        $youtubeVideoId = $request->youtube_video_id;
        $isUnlisted = $request->boolean('is_unlisted', false);
        
        // Si YouTube vidéo ID fourni, extraire et valider
        if ($youtubeVideoId) {
            $youtubeVideoId = $this->extractYouTubeVideoId($youtubeVideoId);
        }
        
        if ($request->hasFile('content_file')) {
            // Déterminer le type de fichier
            $mimeType = $request->file('content_file')->getMimeType();
            if (strpos($mimeType, 'video/') === 0) {
                $result = $this->fileUploadService->uploadVideo($request->file('content_file'), 'courses/lessons', null);
            } else {
                $result = $this->fileUploadService->uploadDocument($request->file('content_file'), 'courses/lessons', null);
            }
            $contentUrl = $result['path'];
        }

        $lesson = $course->lessons()->create([
            'section_id' => $request->section_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'content_url' => $contentUrl,
            'content_text' => $request->content_text,
            'duration' => $request->duration ?? 0,
            'sort_order' => $nextOrder,
            'is_published' => $request->boolean('is_published', true),
            'is_preview' => $request->boolean('is_preview', false),
            'youtube_video_id' => $youtubeVideoId,
            'is_unlisted' => $isUnlisted,
        ]);

        // Les statistiques (duration, lessons_count, etc.) sont maintenant calculées dynamiquement
        // via les accesseurs du modèle Course - pas besoin de les mettre à jour manuellement

        return redirect()->route('admin.courses.lessons', $course)
            ->with('success', 'Leçon créée avec succès.');
    }

    public function editLesson(CourseLesson $lesson)
    {
        $course = $lesson->course;
        $sections = $course->sections()->orderBy('sort_order')->get();
        return view('admin.courses.lessons.edit', compact('lesson', 'course', 'sections'));
    }

    public function updateLesson(Request $request, CourseLesson $lesson)
    {
        $request->validate([
            'section_id' => 'required|exists:course_sections,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:video,text,quiz,assignment',
            'content_url' => 'nullable|string|max:500',
            'content_file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/webm,application/pdf|max:1048576',
            'content_text' => 'nullable|string',
            'duration' => 'nullable|integer|min:0',
            'is_preview' => 'boolean',
            'is_published' => 'boolean',
            'youtube_video_id' => 'nullable|string|max:100',
            'is_unlisted' => 'boolean',
        ]);

        // Gérer YouTube ou upload de fichier
        $contentUrl = $request->content_url;
        $youtubeVideoId = $request->youtube_video_id;
        $isUnlisted = $request->boolean('is_unlisted', false);
        
        // Si YouTube vidéo ID fourni, extraire et valider
        if ($youtubeVideoId) {
            $youtubeVideoId = $this->extractYouTubeVideoId($youtubeVideoId);
        }
        
        if ($request->hasFile('content_file')) {
            // Déterminer l'ancien chemin (si ce n'est pas une URL externe)
            $oldPath = null;
            if ($lesson->content_url && !filter_var($lesson->content_url, FILTER_VALIDATE_URL)) {
                $oldPath = $lesson->content_url;
            }
            
            // Déterminer le type de fichier
            $mimeType = $request->file('content_file')->getMimeType();
            if (strpos($mimeType, 'video/') === 0) {
                $result = $this->fileUploadService->uploadVideo($request->file('content_file'), 'courses/lessons', $oldPath);
            } else {
                $result = $this->fileUploadService->uploadDocument($request->file('content_file'), 'courses/lessons', $oldPath);
            }
            $contentUrl = $result['path'];
        }

        $lesson->update([
            'section_id' => $request->section_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'content_url' => $contentUrl,
            'content_text' => $request->content_text,
            'duration' => $request->duration ?? 0,
            'is_published' => $request->boolean('is_published', true),
            'is_preview' => $request->boolean('is_preview', false),
            'youtube_video_id' => $youtubeVideoId,
            'is_unlisted' => $isUnlisted,
        ]);

        // Les statistiques (duration, lessons_count, etc.) sont maintenant calculées dynamiquement
        // via les accesseurs du modèle Course - pas besoin de les mettre à jour manuellement

        return redirect()->route('admin.courses.lessons', $course)
            ->with('success', 'Leçon mise à jour avec succès.');
    }

    public function destroyLesson(CourseLesson $lesson)
    {
        $course = $lesson->course;
        $lesson->delete();

        // Update course duration and lessons count
        $totalDuration = $course->lessons()->sum('duration');
        $lessonsCount = $course->lessons()->count();
        
        $course->update([
            'duration' => $totalDuration,
            'lessons_count' => $lessonsCount,
        ]);

        return redirect()->route('admin.courses.lessons', $course)
            ->with('success', 'Leçon supprimée avec succès.');
    }

    /**
     * Afficher la page de gestion des statistiques
     */
    public function statistics()
    {
        // Statistiques générales
        $stats = [
            'total_courses' => Course::count(),
            'published_courses' => Course::published()->count(),
            'total_enrollments' => Enrollment::count(),
            'total_reviews' => \App\Models\Review::count(),
            'total_downloads' => CourseDownload::count(),
            'unique_downloaders' => CourseDownload::distinct('user_id')->count('user_id'),
        ];

        // Statistiques de téléchargements
        $downloadStats = [
            // Par cours
            'by_course' => Course::where('is_downloadable', true)
                ->withCount('downloads')
                ->orderBy('downloads_count', 'desc')
                ->limit(20)
                ->get(),
            
            // Par utilisateur
            'by_user' => User::withCount('downloads')
                ->having('downloads_count', '>', 0)
                ->orderBy('downloads_count', 'desc')
                ->limit(20)
                ->get(),
            
            // Par catégorie
            'by_category' => Category::withCount(['courses' => function($query) {
                    $query->where('is_downloadable', true);
                }])
                ->with(['courses' => function($query) {
                    $query->where('is_downloadable', true)->withCount('downloads');
                }])
                ->get()
                ->map(function($category) {
                    $category->total_downloads = $category->courses->sum('downloads_count');
                    return $category;
                })
                ->sortByDesc('total_downloads')
                ->take(10),
            
            // Par pays
            'by_country' => CourseDownload::select('country', 'country_name')
                ->selectRaw('COUNT(*) as downloads_count')
                ->whereNotNull('country')
                ->groupBy('country', 'country_name')
                ->orderBy('downloads_count', 'desc')
                ->limit(20)
                ->get(),
            
            // Par ville
            'by_city' => CourseDownload::select('city', 'country_name')
                ->selectRaw('COUNT(*) as downloads_count')
                ->whereNotNull('city')
                ->groupBy('city', 'country_name')
                ->orderBy('downloads_count', 'desc')
                ->limit(20)
                ->get(),
            
            // Téléchargements par jour (30 derniers jours)
            'daily' => CourseDownload::selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m-%d', 'date') . ', COUNT(*) as downloads_count')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        // Cours avec le plus d'étudiants
        $topCourses = Course::published()
            ->with(['instructor', 'category'])
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(10)
            ->get();

        // Cours les mieux notés
        $topRatedCourses = Course::published()
            ->with(['instructor', 'category'])
            ->withAvg('reviews', 'rating')
            ->having('reviews_avg_rating', '>', 0)
            ->orderBy('reviews_avg_rating', 'desc')
            ->limit(10)
            ->get();

        return view('admin.statistics', compact('stats', 'topCourses', 'topRatedCourses', 'downloadStats'));
    }

    /**
     * Recalculer les statistiques d'un cours
     */
    public function recalculateCourseStats(Course $course)
    {
        try {
            // Forcer le recalcul des statistiques
            $stats = $course->getCourseStats();
            
            return response()->json([
                'success' => true,
                'message' => 'Statistiques recalculées avec succès',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du recalcul: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculer toutes les statistiques
     */
    public function recalculateAllStats()
    {
        try {
            $courses = Course::with(['enrollments', 'reviews', 'sections.lessons'])->get();
            $processed = 0;
            
            foreach ($courses as $course) {
                // Forcer le recalcul des statistiques
                $course->getCourseStats();
                $processed++;
            }
            
            return response()->json([
                'success' => true,
                'message' => "Statistiques recalculées pour {$processed} cours",
                'processed' => $processed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du recalcul: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste des paiements/transactions (réussis/échoués)
     */
    public function payments(Request $request)
    {
        $query = Payment::with(['order.user'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('method')) {
            $query->where('payment_method', $request->string('method')->toString());
        }
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('order.user', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        $payments = $query->paginate(20)->withQueryString();
        $baseCurrency = Setting::getBaseCurrency();

        return view('admin.payments.index', compact('payments', 'baseCurrency'));
    }

    /**
     * Afficher la page de paramètres
     */
    public function settings()
    {
        $settings = Setting::all()->keyBy('key');
        $baseCurrency = Setting::getBaseCurrency();
        
        // Liste des devises courantes
        $currencies = [
            'USD' => 'USD - Dollar américain',
            'EUR' => 'EUR - Euro',
            'CDF' => 'CDF - Franc congolais',
            'XOF' => 'XOF - Franc CFA (BCEAO)',
            'XAF' => 'XAF - Franc CFA (BEAC)',
            'RWF' => 'RWF - Franc rwandais',
            'KES' => 'KES - Shilling kenyan',
            'UGX' => 'UGX - Shilling ougandais',
            'TZS' => 'TZS - Shilling tanzanien',
            'GHS' => 'GHS - Cedi ghanéen',
            'NGN' => 'NGN - Naira nigérian',
            'ZAR' => 'ZAR - Rand sud-africain',
        ];
        
        return view('admin.settings.index', compact('baseCurrency', 'currencies', 'settings'));
    }

    /**
     * Mettre à jour les paramètres
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'base_currency' => 'required|string|size:3|uppercase',
        ]);

        Setting::set('base_currency', strtoupper($request->base_currency), 'string', 'Devise de base du site');

        return redirect()->route('admin.settings')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Extraire l'ID vidéo YouTube depuis une URL ou un ID
     */
    private function extractYouTubeVideoId($url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Si c'est déjà un ID simple (11 caractères alphanumériques)
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        // Extraire depuis différentes formes d'URL YouTube
        $patterns = [
            // https://www.youtube.com/watch?v=dQw4w9WgXcQ
            '/[?&]v=([a-zA-Z0-9_-]{11})/',
            // https://youtu.be/dQw4w9WgXcQ
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            // https://www.youtube.com/embed/dQw4w9WgXcQ
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
            // https://www.youtube.com/v/dQw4w9WgXcQ
            '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Gérer les candidatures formateur
     */
    public function instructorApplications(Request $request)
    {
        $query = InstructorApplication::with(['user', 'reviewer']);

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Recherche par nom ou email
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['created_at', 'status', 'reviewed_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest();
        }

        $applications = $query->paginate(20)->withQueryString();

        // Statistiques
        $stats = [
            'total' => InstructorApplication::count(),
            'pending' => InstructorApplication::where('status', 'pending')->count(),
            'under_review' => InstructorApplication::where('status', 'under_review')->count(),
            'approved' => InstructorApplication::where('status', 'approved')->count(),
            'rejected' => InstructorApplication::where('status', 'rejected')->count(),
        ];

        return view('admin.instructor-applications.index', compact('applications', 'stats'));
    }

    /**
     * Afficher une candidature
     */
    public function showInstructorApplication(InstructorApplication $application)
    {
        $application->load(['user', 'reviewer']);
        return view('admin.instructor-applications.show', compact('application'));
    }

    /**
     * Mettre à jour le statut d'une candidature
     */
    public function updateInstructorApplicationStatus(Request $request, InstructorApplication $application)
    {
        $request->validate([
            'status' => 'required|in:pending,under_review,approved,rejected',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $application->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Si approuvée, changer le rôle de l'utilisateur
        if ($request->status === 'approved') {
            $application->user->update([
                'role' => 'instructor'
            ]);
        }

        return redirect()->route('admin.instructor-applications.show', $application)
            ->with('success', 'Statut de la candidature mis à jour avec succès.');
    }
}
