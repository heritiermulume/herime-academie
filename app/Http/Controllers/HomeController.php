<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Partner;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\ContentPackage;
use App\Models\SubscriptionPlan;
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
            ->with(['provider', 'category', 'reviews', 'enrollments'])
            ->latest()
            ->limit(10)
            ->get();

        $popularCourses = Course::published()
            ->with(['provider', 'category', 'reviews', 'enrollments'])
            ->popular()
            ->limit(10)
            ->get();

        $latestCourses = Course::published()
            ->with(['provider', 'category', 'reviews', 'enrollments'])
            ->latest()
            ->limit(10)
            ->get();

        $topRatedCourses = Course::published()
            ->with(['provider', 'category', 'reviews', 'enrollments'])
            ->topRated()
            ->limit(10)
            ->get();

        // Récupérer les catégories les plus populaires basées sur les inscriptions récentes
        $categories = Category::active()
            ->withCount(['contents' => function($query) {
                $query->where('is_published', true);
            }])
            ->withCount(['contents as recent_enrollments_count' => function($query) {
                $query->where('is_published', true)
                      ->whereHas('enrollments', function($subQuery) {
                          $subQuery->where('created_at', '>=', now()->subMonth());
                      });
            }])
            ->orderBy('recent_enrollments_count', 'desc')
            ->orderBy('contents_count', 'desc')
            ->orderBy('sort_order', 'asc')
            ->limit(8)
            ->get();

        $providers = User::providers()
            ->where('is_active', true)
            ->withCount('contents')
            ->orderBy('contents_count', 'desc')
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
            ->with(['provider', 'category'])
            ->whereHas('enrollments', function($query) {
                $query->where('created_at', '>=', now()->subWeek());
            })
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(10)
            ->get();

        $featuredPackages = ContentPackage::query()
            ->published()
            ->featured()
            ->withCount('contents')
            ->ordered()
            ->get();

        $homeSubscriptionPlans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('price')
            ->limit(3)
            ->get();

        $homePackagesAsideFeatured = ContentPackage::query()
            ->published()
            ->where('is_featured', false)
            ->withCount('contents')
            ->ordered()
            ->limit(12)
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
            'featuredPackages',
            'homePackagesAsideFeatured',
            'homeSubscriptionPlans',
            'categories',
            'providers',
            'announcements',
            'partners',
            'testimonials'
        ));
    }

}
