<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Traits\DatabaseCompatibility;
use App\Traits\CourseStatistics;
use Illuminate\Http\Request;

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
        
        $stats = [
            'total_courses' => $instructor->courses()->count(),
            'published_courses' => $instructor->courses()->published()->count(),
            'total_students' => Enrollment::whereHas('course', function($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            })->count(),
            'total_earnings' => 0, // À implémenter avec le système de paiement
        ];

        $stats['active_students'] = Enrollment::whereHas('course', function($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })->where('created_at', '>=', now()->subDays(30))->count();

        $coursesForRatings = $instructor->courses()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->get();

        $stats['average_rating'] = $coursesForRatings->avg('reviews_avg_rating') ?? 0;
        $stats['total_reviews'] = $coursesForRatings->sum('reviews_count');
 
        $recent_courses = $instructor->courses()
            ->with(['category'])
            ->latest()
            ->limit(5)
            ->get();

        $recent_enrollments = Enrollment::whereHas('course', function($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->with(['user', 'course'])
        ->latest()
        ->limit(10)
        ->get();

        $stats['top_courses'] = $instructor->courses()
            ->withCount(['enrollments', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->orderByDesc('enrollments_count')
            ->limit(3)
            ->get();

        return view('instructors.dashboard', [
            'instructor' => $instructor,
            'stats' => $stats,
            'recent_courses' => $recent_courses,
            'recent_enrollments' => $recent_enrollments,
        ]);
    }

    public function students()
    {
        $instructor = auth()->user();
        
        $enrollments = Enrollment::whereHas('course', function($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->with(['user', 'course'])
        ->latest()
        ->paginate(20);

        return view('instructors.students', [
            'instructor' => $instructor,
            'enrollments' => $enrollments,
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

        return view('instructors.analytics', [
            'instructor' => $instructor,
            'courseStats' => $courseStats,
            'popularCourses' => $popularCourses,
            'enrollmentsByMonth' => $enrollmentsByMonth,
        ]);
    }

    public function coursesIndex(Request $request)
    {
        $instructor = auth()->user();

        $status = $request->get('status');

        $coursesQuery = $instructor->courses()
            ->with(['category'])
            ->withCount(['enrollments', 'reviews']);

        if ($status === 'published') {
            $coursesQuery->where('is_published', true);
        } elseif ($status === 'draft') {
            $coursesQuery->where('is_published', false);
        }

        $courses = $coursesQuery
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(function ($course) {
                $course->stats = $course->getCourseStats();
                return $course;
            })
            ->withQueryString();

        return view('instructors.courses.index', [
            'instructor' => $instructor,
            'courses' => $courses,
            'status' => $status,
        ]);
    }
}
