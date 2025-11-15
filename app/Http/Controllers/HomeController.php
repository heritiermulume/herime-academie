<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Partner;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\TemporaryUploadCleaner;
use App\Traits\CourseStatistics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    use CourseStatistics;
    public function index(TemporaryUploadCleaner $temporaryUploadCleaner)
    {
        $cacheKey = 'temporary_uploads_last_cleanup_at';
        $lockKey = 'temporary_uploads_cleanup_lock';
        $interval = max(1, (int) config('uploads.temporary.home_cleanup_interval_minutes', 60));
        $lastCleanup = Cache::get($cacheKey);

        if ((!$lastCleanup || now()->diffInMinutes($lastCleanup) >= $interval)
            && Cache::add($lockKey, true, 60)) {
            try {
                $temporaryUploadCleaner->clean();
                Cache::put($cacheKey, now(), now()->addMinutes($interval * 2));
            } finally {
                Cache::forget($lockKey);
            }
        }

        // Récupérer les bannières actives pour le carousel
        $banners = Banner::active()->ordered()->get();

        // Récupérer les données pour la page d'accueil
        $featuredCourses = Course::published()
            ->featured()
            ->with(['instructor', 'category', 'reviews', 'enrollments'])
            ->latest()
            ->limit(3)
            ->get();

        $popularCourses = Course::published()
            ->with(['instructor', 'category', 'reviews', 'enrollments'])
            ->popular()
            ->limit(8)
            ->get();

        $latestCourses = Course::published()
            ->with(['instructor', 'category', 'reviews', 'enrollments'])
            ->latest()
            ->limit(6)
            ->get();

        $topRatedCourses = Course::published()
            ->with(['instructor', 'category', 'reviews', 'enrollments'])
            ->topRated()
            ->limit(6)
            ->get();

        // Récupérer les catégories les plus populaires basées sur les inscriptions récentes
        $categories = Category::active()
            ->withCount(['courses' => function($query) {
                $query->where('is_published', true);
            }])
            ->withCount(['courses as recent_enrollments_count' => function($query) {
                $query->where('is_published', true)
                      ->whereHas('enrollments', function($subQuery) {
                          $subQuery->where('created_at', '>=', now()->subMonth());
                      });
            }])
            ->orderBy('recent_enrollments_count', 'desc')
            ->orderBy('courses_count', 'desc')
            ->orderBy('sort_order', 'asc')
            ->limit(8)
            ->get();

        $instructors = User::instructors()
            ->where('is_active', true)
            ->withCount('courses')
            ->orderBy('courses_count', 'desc')
            ->limit(6)
            ->get();

        $announcements = Announcement::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('starts_at')
                      ->orWhere('starts_at', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
            })
            ->latest()
            ->limit(3)
            ->get();

        $partners = Partner::where('is_active', true)
            ->ordered()
            ->get();

        $testimonials = Testimonial::where('is_active', true)
            ->ordered()
            ->limit(6)
            ->get();

        // Cours tendance (cours avec le plus d'inscriptions récentes)
        $trendingCourses = Course::published()
            ->with(['instructor', 'category'])
            ->whereHas('enrollments', function($query) {
                $query->where('created_at', '>=', now()->subWeek());
            })
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(4)
            ->get();

        // Calculer les statistiques pour chaque cours
        $featuredCourses = $this->addCourseStatistics($featuredCourses);
        $popularCourses = $this->addCourseStatistics($popularCourses);
        $latestCourses = $this->addCourseStatistics($latestCourses);
        $topRatedCourses = $this->addCourseStatistics($topRatedCourses);
        $trendingCourses = $this->addCourseStatistics($trendingCourses);

        return view('home', compact(
            'banners',
            'featuredCourses',
            'popularCourses',
            'latestCourses',
            'topRatedCourses',
            'trendingCourses',
            'categories',
            'instructors',
            'announcements',
            'partners',
            'testimonials'
        ));
    }

}
