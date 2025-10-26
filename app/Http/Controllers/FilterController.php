<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use App\Models\User;
use App\Traits\CourseStatistics;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    use CourseStatistics;

    /**
     * Filtrer les cours avec des critères avancés
     * Utilise uniquement des données dynamiques
     */
    public function filterCourses(Request $request)
    {
        $query = Course::published()
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons']);

        // Filtres de base
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('is_free')) {
            $query->where('is_free', $request->boolean('is_free'));
        }

        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        // Filtres basés sur des données dynamiques
        if ($request->filled('min_rating')) {
            $query->withAvg('reviews', 'rating')
                  ->having('reviews_avg_rating', '>=', $request->min_rating);
        }

        if ($request->filled('min_students')) {
            $query->withCount('enrollments')
                  ->having('enrollments_count', '>=', $request->min_students);
        }

        if ($request->filled('min_duration')) {
            // Filtre par durée minimale (calculée dynamiquement)
            $query->whereHas('sections.lessons', function($q) use ($request) {
                $q->selectRaw('SUM(duration) as total_duration')
                  ->groupBy('course_id')
                  ->having('total_duration', '>=', $request->min_duration);
            });
        }

        if ($request->filled('max_duration')) {
            $query->whereHas('sections.lessons', function($q) use ($request) {
                $q->selectRaw('SUM(duration) as total_duration')
                  ->groupBy('course_id')
                  ->having('total_duration', '<=', $request->max_duration);
            });
        }

        if ($request->filled('min_lessons')) {
            $query->whereHas('sections.lessons', function($q) use ($request) {
                $q->selectRaw('COUNT(*) as total_lessons')
                  ->groupBy('course_id')
                  ->having('total_lessons', '>=', $request->min_lessons);
            });
        }

        if ($request->filled('instructor_id')) {
            $query->where('instructor_id', $request->instructor_id);
        }

        // Tri dynamique
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'popularity':
                $query->withCount('enrollments')->orderBy('enrollments_count', $sortOrder);
                break;
            case 'rating':
                $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', $sortOrder);
                break;
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
            case 'duration':
                // Tri par durée (nécessite un calcul plus complexe)
                $query->withCount(['sections as total_duration' => function($q) {
                    $q->join('course_lessons', 'course_sections.id', '=', 'course_lessons.section_id')
                      ->selectRaw('SUM(course_lessons.duration)');
                }])->orderBy('total_duration', $sortOrder);
                break;
            case 'lessons':
                $query->withCount(['sections as total_lessons' => function($q) {
                    $q->join('course_lessons', 'course_sections.id', '=', 'course_lessons.section_id')
                      ->selectRaw('COUNT(course_lessons.id)');
                }])->orderBy('total_lessons', $sortOrder);
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 12);
        $courses = $query->paginate($perPage);

        // Ajouter les statistiques dynamiques
        $courses = $this->addCourseStatistics($courses);

        return response()->json([
            'success' => true,
            'courses' => $courses,
            'filters_applied' => $request->only([
                'category_id', 'level', 'language', 'is_free', 'is_featured',
                'min_rating', 'min_students', 'min_duration', 'max_duration',
                'min_lessons', 'instructor_id', 'sort_by', 'sort_order'
            ])
        ]);
    }

    /**
     * Obtenir les options de filtres disponibles
     */
    public function getFilterOptions()
    {
        $categories = Category::active()
            ->withCount(['courses' => function($query) {
                $query->where('is_published', true);
            }])
            ->having('courses_count', '>', 0)
            ->orderBy('name')
            ->get();

        $instructors = User::instructors()
            ->whereHas('courses', function($query) {
                $query->where('is_published', true);
            })
            ->withCount(['courses' => function($query) {
                $query->where('is_published', true);
            }])
            ->orderBy('name')
            ->get();

        $levels = Course::published()
            ->select('level')
            ->distinct()
            ->orderBy('level')
            ->pluck('level');

        $languages = Course::published()
            ->select('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language');

        // Statistiques pour les filtres de plage
        $stats = [
            'min_rating' => Course::published()->withAvg('reviews', 'rating')->min('reviews_avg_rating') ?? 0,
            'max_rating' => Course::published()->withAvg('reviews', 'rating')->max('reviews_avg_rating') ?? 5,
            'min_students' => Course::published()->withCount('enrollments')->min('enrollments_count') ?? 0,
            'max_students' => Course::published()->withCount('enrollments')->max('enrollments_count') ?? 0,
            'min_duration' => Course::published()->withCount(['sections as total_duration' => function($q) {
                $q->join('course_lessons', 'course_sections.id', '=', 'course_lessons.section_id')
                  ->selectRaw('SUM(course_lessons.duration)');
            }])->min('total_duration') ?? 0,
            'max_duration' => Course::published()->withCount(['sections as total_duration' => function($q) {
                $q->join('course_lessons', 'course_sections.id', '=', 'course_lessons.section_id')
                  ->selectRaw('SUM(course_lessons.duration)');
            }])->max('total_duration') ?? 0,
        ];

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'instructors' => $instructors,
            'levels' => $levels,
            'languages' => $languages,
            'stats' => $stats
        ]);
    }

    /**
     * Recherche avancée de cours
     */
    public function searchCourses(Request $request)
    {
        $query = Course::published()
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons']);

        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('short_description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('instructor', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('category', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Appliquer les filtres de base
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Tri par pertinence (pour la recherche)
        if ($request->filled('q')) {
            $query->withCount('enrollments')
                  ->withAvg('reviews', 'rating')
                  ->orderBy('enrollments_count', 'desc')
                  ->orderBy('reviews_avg_rating', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->get('per_page', 12);
        $courses = $query->paginate($perPage);

        // Ajouter les statistiques dynamiques
        $courses = $this->addCourseStatistics($courses);

        return response()->json([
            'success' => true,
            'courses' => $courses,
            'search_term' => $request->q,
            'total_results' => $courses->total()
        ]);
    }
}