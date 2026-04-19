<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Category;
use App\Models\ContentPackage;
use App\Models\Course;
use App\Models\Partner;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\CommunitySettingsService;
use App\Traits\CourseStatistics;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    use CourseStatistics;

    /**
     * Mélange contenus et packs « en vedette » : tri unique par date de création (plus récent en premier).
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Course>  $featuredCourses
     * @param  \Illuminate\Support\Collection<int, \App\Models\ContentPackage>  $featuredPackages
     * @return Collection<int, array{type: string, course?: \App\Models\Course, package?: \App\Models\ContentPackage}>
     */
    private function featuredHomeFeed(Collection $featuredCourses, Collection $featuredPackages): Collection
    {
        $items = collect();
        foreach ($featuredCourses as $course) {
            $items->push([
                'type' => 'course',
                'course' => $course,
                'sort_at' => $course->created_at?->getTimestamp() ?? 0,
            ]);
        }
        foreach ($featuredPackages as $package) {
            $items->push([
                'type' => 'package',
                'package' => $package,
                'sort_at' => $package->created_at?->getTimestamp() ?? 0,
            ]);
        }

        return $items->sortByDesc('sort_at')->values()->map(fn (array $row) => [
            'type' => $row['type'],
            'course' => $row['course'] ?? null,
            'package' => $row['package'] ?? null,
        ]);
    }

    /**
     * Mélange populaires : score = inscriptions (cours) ou lignes de commande payées (packs).
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Course>  $popularCourses
     * @param  \Illuminate\Support\Collection<int, \App\Models\ContentPackage>  $popularPackages
     * @return Collection<int, array{type: string, course?: \App\Models\Course, package?: \App\Models\ContentPackage}>
     */
    private function popularHomeFeed(Collection $popularCourses, Collection $popularPackages): Collection
    {
        $items = collect();
        foreach ($popularCourses as $course) {
            $items->push([
                'type' => 'course',
                'course' => $course,
                'score' => (int) ($course->enrollments_count ?? 0),
            ]);
        }
        foreach ($popularPackages as $package) {
            $items->push([
                'type' => 'package',
                'package' => $package,
                'score' => (int) ($package->purchases_count ?? 0),
            ]);
        }

        return $items->sortByDesc('score')->values()->take(22)->map(fn (array $row) => [
            'type' => $row['type'],
            'course' => $row['course'] ?? null,
            'package' => $row['package'] ?? null,
        ]);
    }

    /**
     * Mélange tendance : activité sur la dernière semaine (inscriptions / achats pack).
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Course>  $trendingCourses
     * @param  \Illuminate\Support\Collection<int, \App\Models\ContentPackage>  $trendingPackages
     * @return Collection<int, array{type: string, course?: \App\Models\Course, package?: \App\Models\ContentPackage}>
     */
    private function trendingHomeFeed(Collection $trendingCourses, Collection $trendingPackages): Collection
    {
        $items = collect();
        foreach ($trendingCourses as $course) {
            $items->push([
                'type' => 'course',
                'course' => $course,
                'score' => (int) ($course->enrollments_count ?? 0),
            ]);
        }
        foreach ($trendingPackages as $package) {
            $items->push([
                'type' => 'package',
                'package' => $package,
                'score' => (int) ($package->recent_pack_orders_count ?? 0),
            ]);
        }

        return $items->sortByDesc('score')->values()->take(18)->map(fn (array $row) => [
            'type' => $row['type'],
            'course' => $row['course'] ?? null,
            'package' => $row['package'] ?? null,
        ]);
    }

    public function index()
    {
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
            ->limit(12)
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
            ->withCount(['contents' => function ($query) {
                $query->where('is_published', true);
            }])
            ->withCount(['contents as recent_enrollments_count' => function ($query) {
                $query->where('is_published', true)
                    ->whereHas('enrollments', function ($subQuery) {
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
            ->where('type', '!=', Announcement::TYPE_HOME_MODAL)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->latest()
            ->limit(3)
            ->get();

        $homeModalAnnouncement = Announcement::active()
            ->where('type', Announcement::TYPE_HOME_MODAL)
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at')
            ->first();

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
            ->whereHas('enrollments', function ($query) {
                $query->where('created_at', '>=', now()->subWeek());
            })
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(12)
            ->get();

        $featuredPackages = ContentPackage::query()
            ->published()
            ->featured()
            ->withCount('contents')
            ->ordered()
            ->get();

        $paidOrderStatuses = ['paid', 'completed'];

        $popularPackages = ContentPackage::query()
            ->published()
            ->withCount(['orderItems as purchases_count' => function ($query) use ($paidOrderStatuses) {
                $query->whereHas('order', function ($q) use ($paidOrderStatuses) {
                    $q->whereIn('status', $paidOrderStatuses);
                });
            }])
            ->withCount('contents')
            ->orderByDesc('purchases_count')
            ->limit(24)
            ->get()
            ->filter(fn (ContentPackage $package) => $package->purchases_count > 0)
            ->take(12)
            ->values();

        $trendingPackages = ContentPackage::query()
            ->published()
            ->withCount(['orderItems as recent_pack_orders_count' => function ($query) use ($paidOrderStatuses) {
                $query->where('order_items.created_at', '>=', now()->subWeek())
                    ->whereHas('order', function ($q) use ($paidOrderStatuses) {
                        $q->whereIn('status', $paidOrderStatuses);
                    });
            }])
            ->withCount('contents')
            ->orderByDesc('recent_pack_orders_count')
            ->limit(40)
            ->get()
            ->filter(fn (ContentPackage $package) => $package->recent_pack_orders_count > 0)
            ->take(12)
            ->values();

        $communityHomeMedia = CommunitySettingsService::homeMedia();

        // Calculer les statistiques pour chaque cours
        $featuredCourses = $this->addCourseStatistics($featuredCourses);
        $popularCourses = $this->addCourseStatistics($popularCourses);
        $latestCourses = $this->addCourseStatistics($latestCourses);
        $topRatedCourses = $this->addCourseStatistics($topRatedCourses);
        $trendingCourses = $this->addCourseStatistics($trendingCourses);

        $featuredHomeFeed = $this->featuredHomeFeed($featuredCourses, $featuredPackages);
        $popularHomeFeed = $this->popularHomeFeed($popularCourses, $popularPackages);
        $trendingHomeFeed = $this->trendingHomeFeed($trendingCourses, $trendingPackages);

        return view('home', compact(
            'banners',
            'featuredCourses',
            'popularCourses',
            'latestCourses',
            'topRatedCourses',
            'trendingCourses',
            'featuredHomeFeed',
            'popularHomeFeed',
            'trendingHomeFeed',
            'communityHomeMedia',
            'categories',
            'providers',
            'announcements',
            'homeModalAnnouncement',
            'partners',
            'testimonials'
        ));
    }
}
