<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use App\Models\CourseSection;
use App\Models\CourseLesson;
use App\Models\Enrollment;
use App\Traits\CourseStatistics;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    use CourseStatistics;
    public function index(Request $request)
    {
        $query = Course::published()->with(['instructor', 'category']);

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

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
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
                $query->orderByRaw('CASE WHEN sale_price IS NOT NULL THEN sale_price ELSE price END ASC');
                break;
            case 'price_high':
                $query->orderByRaw('CASE WHEN sale_price IS NOT NULL THEN sale_price ELSE price END DESC');
                break;
            default:
                $query->popular(); // Par défaut, cours les plus populaires
        }

        // Gestion du scroll infini
        if ($request->ajax() && $request->filled('infinite_scroll')) {
            $page = $request->get('page', 1);
            $perPage = 12;
            
            $courses = $query->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                           ->skip(($page - 1) * $perPage)
                           ->take($perPage)
                           ->get();
            
            // Ajouter les statistiques
            $courses = $this->addCourseStatistics($courses);
            
            $hasMore = $courses->count() === $perPage;
            
            return response()->json([
                'courses' => $courses,
                'hasMore' => $hasMore,
                'nextPage' => $hasMore ? $page + 1 : null
            ]);
        }

        $courses = $query->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])->paginate(12);
        
        // Ajouter les statistiques à chaque cours
        $courses->getCollection()->transform(function($course) {
            $course->stats = [
                'total_lessons' => $course->sections->sum(function($section) {
                    return $section->lessons->count();
                }),
                'total_duration' => $course->sections->sum(function($section) {
                    return $section->lessons->sum('duration');
                }),
                'total_students' => $course->enrollments->count(),
                'average_rating' => $course->reviews->avg('rating') ?? 0,
                'total_reviews' => $course->reviews->count(),
            ];
            return $course;
        });
        
        $categories = Category::active()->ordered()->get();

        return view('courses.index', compact('courses', 'categories'));
    }

    public function show(Course $course)
    {
        // Charger toutes les relations nécessaires
        $course->load([
            'instructor' => function($query) {
                $query->withCount('courses');
            },
            'instructor.courses' => function($query) {
                $query->withCount('enrollments');
            },
            'category',
            'sections' => function($query) {
                $query->orderBy('sort_order');
            },
            'sections.lessons' => function($query) {
                $query->where('is_published', true)->orderBy('sort_order');
            },
            'reviews' => function($query) {
                $query->with('user')->latest()->limit(10);
            },
            'enrollments' => function($query) {
                $query->where('status', 'active');
            },
        ]);
        
        // Vérifier si l'utilisateur est inscrit
        $isEnrolled = auth()->check() ? $course->isEnrolledBy(auth()->id()) : false;
        $enrollment = $isEnrolled ? $course->getEnrollmentFor(auth()->id()) : null;

        // Calculer les statistiques du cours (simplifiées)
        $courseStats = [
            'recent_announcements' => \App\Models\Announcement::where('is_active', true)
                ->latest()
                ->limit(5)
                ->get()
        ];

        // Cours similaires avec algorithme de recommandation amélioré
        $relatedCourses = $this->getRecommendedCourses($course);

        return view('courses.show', compact('course', 'isEnrolled', 'enrollment', 'relatedCourses', 'courseStats'));
    }

    public function byCategory(Category $category)
    {
        $courses = Course::published()
            ->where('category_id', $category->id)
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
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
                'total_students' => $course->enrollments->count(),
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

        return view('courses.category', compact('courses', 'category', 'otherCategories'));
    }

    public function create()
    {
        $categories = Category::active()->ordered()->get();
        return view('courses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'level' => 'required|in:beginner,intermediate,advanced',
            'language' => 'required|string|max:5',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_preview' => 'nullable|file|mimes:mp4,avi,mov|max:10240',
            'requirements' => 'nullable|array',
            'what_you_will_learn' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        $data = $request->all();
        $data['instructor_id'] = auth()->id();
        $data['slug'] = Str::slug($request->title);
        $data['is_published'] = false; // Par défaut, le cours n'est pas publié

        // Gérer l'upload de l'image
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        // Gérer l'upload de la vidéo de prévisualisation
        if ($request->hasFile('video_preview')) {
            $data['video_preview'] = $request->file('video_preview')->store('courses/previews', 'public');
        }

        $course = Course::create($data);

        return redirect()->route('instructor.courses.edit', $course)
            ->with('success', 'Cours créé avec succès. Vous pouvez maintenant ajouter des sections et des leçons.');
    }

    public function edit(Course $course)
    {
        $this->authorize('update', $course);
        
        $categories = Category::active()->ordered()->get();
        $course->load(['sections.lessons']);
        
        return view('courses.edit', compact('course', 'categories'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'level' => 'required|in:beginner,intermediate,advanced',
            'language' => 'required|string|max:5',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_preview' => 'nullable|file|mimes:mp4,avi,mov|max:10240',
            'requirements' => 'nullable|array',
            'what_you_will_learn' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->title);

        // Gérer l'upload de l'image
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        // Gérer l'upload de la vidéo de prévisualisation
        if ($request->hasFile('video_preview')) {
            $data['video_preview'] = $request->file('video_preview')->store('courses/previews', 'public');
        }

        $course->update($data);

        return redirect()->route('instructor.courses.edit', $course)
            ->with('success', 'Cours mis à jour avec succès.');
    }

    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        
        $course->delete();
        
        return redirect()->route('instructor.courses.index')
            ->with('success', 'Cours supprimé avec succès.');
    }

    public function publish(Course $course)
    {
        $this->authorize('update', $course);
        
        $course->update(['is_published' => true]);
        
        return redirect()->back()
            ->with('success', 'Cours publié avec succès.');
    }

    public function unpublish(Course $course)
    {
        $this->authorize('update', $course);
        
        $course->update(['is_published' => false]);
        
        return redirect()->back()
            ->with('success', 'Cours retiré de la publication.');
    }

    public function lesson(Course $course, CourseLesson $lesson)
    {
        // Vérifier que la leçon appartient au cours
        if ($lesson->course_id !== $course->id) {
            abort(404);
        }

        // Vérifier que la leçon est en aperçu ou que l'utilisateur est inscrit
        if (!$lesson->is_preview) {
            // Vérifier si l'utilisateur est inscrit au cours
            if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
                return redirect()->route('courses.show', $course)
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

        return view('courses.lesson', compact('course', 'lesson', 'previousLesson', 'nextLesson'));
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
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
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
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
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
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
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
                    ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
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
                    $query->where('course_id', $course->id);
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
                ->pluck('course_id')
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
                'total_students' => $course->enrollments->count(),
                'average_rating' => $course->reviews->avg('rating') ?? 0,
                'total_reviews' => $course->reviews->count(),
            ];
            return $course;
        });
    }
}
