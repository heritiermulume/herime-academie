<?php

namespace App\Http\Controllers;

use App\Models\ContentPackage;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

class ContentPackageController extends Controller
{
    public function index()
    {
        return redirect()->to(route('contents.index').'#content-packs');
    }

    public function show(ContentPackage $package)
    {
        $package->load([
            'contents' => fn ($q) => $q->with(['category', 'provider', 'sections.lessons', 'reviews'])
                ->orderByPivot('sort_order'),
        ]);

        $recommendedPackages = ContentPackage::query()
            ->published()
            ->where('is_sale_enabled', true)
            ->where('price', '>', 0)
            ->where(function ($q) {
                $q->whereNull('sale_price')
                    ->orWhere('sale_price', '>', 0);
            })
            ->whereKeyNot($package->id)
            ->withCount('contents')
            ->ordered()
            ->limit(8)
            ->get();

        if (auth()->check()) {
            $recommendedPackages = $recommendedPackages
                ->filter(fn (ContentPackage $p) => ! auth()->user()->hasPurchasedContentPackage($p))
                ->values();
        }

        $recommendedPackages = $recommendedPackages->take(3)->values();

        $packageContentIds = $package->contents->pluck('id')->map(fn ($id) => (int) $id)->all();
        $packageCategoryIds = $package->contents
            ->pluck('category_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $excludeCourseIds = array_values(array_unique(array_merge(
            $packageContentIds,
            Auth::check() ? Auth::user()->getRecommendationExcludedContentIds() : []
        )));

        $recommendedCourses = Course::query()
            ->published()
            ->saleEnabled()
            ->where('is_free', false)
            ->whereNotIn('id', $excludeCourseIds ?: [0])
            ->when(! empty($packageCategoryIds), fn ($q) => $q->whereIn('category_id', $packageCategoryIds))
            ->with(['provider', 'category'])
            ->withCount(['enrollments', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->orderByDesc('is_featured')
            ->orderByDesc('enrollments_count')
            ->limit(3)
            ->get();

        if ($recommendedCourses->count() < 3) {
            $missing = 3 - $recommendedCourses->count();
            $fallbackCourses = Course::query()
                ->published()
                ->saleEnabled()
                ->where('is_free', false)
                ->whereNotIn('id', array_merge(
                    $excludeCourseIds,
                    $recommendedCourses->pluck('id')->map(fn ($id) => (int) $id)->all()
                ) ?: [0])
                ->with(['provider', 'category'])
                ->withCount(['enrollments', 'reviews'])
                ->withAvg('reviews', 'rating')
                ->latest()
                ->limit($missing)
                ->get();

            $recommendedCourses = $recommendedCourses->concat($fallbackCourses);
        }

        $recommendedCourses->transform(function (Course $course) {
            $course->stats = [
                'average_rating' => (float) ($course->reviews_avg_rating ?? 0),
                'total_reviews' => (int) ($course->reviews_count ?? 0),
                'total_customers' => (int) ($course->enrollments_count ?? 0),
                'purchases_count' => (int) ($course->purchases_count ?? 0),
            ];

            return $course;
        });

        return view('packs.show', compact('package', 'recommendedPackages', 'recommendedCourses'));
    }
}
