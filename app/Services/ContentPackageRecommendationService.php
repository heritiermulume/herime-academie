<?php

namespace App\Services;

use App\Models\ContentPackage;
use App\Models\Course;
use Illuminate\Support\Collection;

class ContentPackageRecommendationService
{
    /**
     * Qualifie une colonne sur la table des contenus (évite « id » ambigu avec content_package_content.id en whereHas).
     */
    private static function contentColumn(string $name): string
    {
        return (new Course)->getTable().'.'.$name;
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int>
     */
    public function excludedPackageIdsFromCartLines(array $lines): array
    {
        return collect($lines)
            ->filter(fn ($i) => ($i['type'] ?? 'content') === 'package')
            ->map(function ($i) {
                $p = $i['package'] ?? null;
                if (is_object($p)) {
                    return (int) $p->id;
                }

                return isset($i['package_id']) ? (int) $i['package_id'] : null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int>
     */
    public function excludedPackageIdsFromSessionAndUser(): array
    {
        $ids = collect();
        $cart = session('cart', []);
        if (is_array($cart) && ! empty($cart['packages'])) {
            $ids = $ids->merge($cart['packages']);
        }
        if (auth()->check()) {
            $ids = $ids->merge(auth()->user()->cartPackages()->pluck('content_package_id'));
        }

        return $ids->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    public function isPurchased(ContentPackage $package): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return auth()->user()->hasPurchasedContentPackage($package);
    }

    /**
     * @param  array<int>  $excludeIds
     */
    protected function baseQuery(array $excludeIds): \Illuminate\Database\Eloquent\Builder
    {
        $q = ContentPackage::query()
            ->published()
            ->where('is_sale_enabled', true)
            ->where('price', '>', 0)
            ->where(function ($q2) {
                $q2->whereNull('sale_price')
                    ->orWhere('sale_price', '>', 0);
            })
            ->withCount('contents')
            ->ordered();

        if ($excludeIds !== []) {
            $q->whereNotIn('id', $excludeIds);
        }

        return $q;
    }

    /**
     * @return Collection<int, ContentPackage>
     */
    protected function filterPurchased(Collection $packages): Collection
    {
        return $packages->filter(fn (ContentPackage $p) => ! $this->isPurchased($p))->values();
    }

    /**
     * Recommandations de packs selon les lignes du panier (contenus / packs).
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @return Collection<int, ContentPackage>
     */
    public function forCartLines(array $lines): Collection
    {
        $excludeIds = $this->excludedPackageIdsFromCartLines($lines);
        $packages = collect();
        $pickedIds = collect();

        if ($lines === []) {
            $c = $this->filterPurchased($this->baseQuery($excludeIds)->limit(8)->get());

            return $c->shuffle()->take(3)->values();
        }

        $contentLines = collect($lines)->filter(fn ($i) => ($i['type'] ?? 'content') === 'content');
        $cartContentIds = $contentLines->pluck('course.id')->filter()->unique()->values()->toArray();
        $cartCategories = $contentLines->pluck('course.category_id')->filter()->unique()->values()->toArray();

        $mergeUnique = function (Collection $chunk) use (&$packages, &$pickedIds) {
            foreach ($chunk as $p) {
                if ($pickedIds->contains($p->id)) {
                    continue;
                }
                $pickedIds->push($p->id);
                $packages->push($p);
            }
        };

        if ($cartContentIds !== []) {
            $overlap = $this->filterPurchased(
                $this->baseQuery($excludeIds)
                    ->whereHas('contents', fn ($q) => $q->whereIn(self::contentColumn('id'), $cartContentIds))
                    ->whereNotIn('id', $pickedIds->all())
                    ->limit(2)
                    ->get()
            );
            $mergeUnique($overlap);
        }

        if ($packages->count() < 3 && $cartCategories !== []) {
            $cat = $this->filterPurchased(
                $this->baseQuery($excludeIds)
                    ->whereHas('contents', fn ($q) => $q->whereIn(self::contentColumn('category_id'), $cartCategories))
                    ->whereNotIn('id', $pickedIds->all())
                    ->limit(3)
                    ->get()
            );
            $mergeUnique($cat);
        }

        if ($packages->count() < 3) {
            $fill = $this->filterPurchased(
                $this->baseQuery($excludeIds)
                    ->whereNotIn('id', $pickedIds->all())
                    ->limit(3 - $packages->count())
                    ->get()
            );
            $mergeUnique($fill);
        }

        return $packages->take(3)->values();
    }

    /**
     * Packs pour la section « panier vide » (populaires, hors panier / déjà achetés).
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @return Collection<int, ContentPackage>
     */
    public function popularForCartContext(array $lines): Collection
    {
        $excludeIds = $this->excludedPackageIdsFromCartLines($lines);

        return $this->filterPurchased($this->baseQuery($excludeIds)->limit(8)->get())
            ->take(4)
            ->values();
    }

    /**
     * Packs sur la fiche contenu ou l’écran d’apprentissage.
     *
     * @return Collection<int, ContentPackage>
     */
    public function forCourse(Course $course): Collection
    {
        $excludeIds = $this->excludedPackageIdsFromSessionAndUser();
        $packages = collect();
        $pickedIds = collect();

        $mergeUnique = function (Collection $chunk) use (&$packages, &$pickedIds) {
            foreach ($chunk as $p) {
                if ($pickedIds->contains($p->id)) {
                    continue;
                }
                $pickedIds->push($p->id);
                $packages->push($p);
            }
        };

        $overlap = $this->filterPurchased(
            $this->baseQuery($excludeIds)
                ->whereHas('contents', fn ($q) => $q->where(self::contentColumn('id'), $course->id))
                ->limit(2)
                ->get()
        );
        $mergeUnique($overlap);

        if ($packages->count() < 3 && $course->category_id) {
            $cat = $this->filterPurchased(
                $this->baseQuery($excludeIds)
                    ->whereNotIn('id', $pickedIds->all())
                    ->whereHas('contents', fn ($q) => $q->where(self::contentColumn('category_id'), $course->category_id))
                    ->limit(2)
                    ->get()
            );
            $mergeUnique($cat);
        }

        if ($packages->count() < 3) {
            $fill = $this->filterPurchased(
                $this->baseQuery($excludeIds)
                    ->whereNotIn('id', $pickedIds->all())
                    ->limit(3 - $packages->count())
                    ->get()
            );
            $mergeUnique($fill);
        }

        return $packages->take(3)->values();
    }

    /**
     * Packs suggérés sur le tableau de bord client (au moins un contenu non suivi).
     *
     * @param  array<int>  $enrolledContentIds
     * @return Collection<int, ContentPackage>
     */
    public function forCustomerDashboard(array $enrolledContentIds): Collection
    {
        $enrolledContentIds = array_values(array_unique(array_filter($enrolledContentIds)));
        $excludeCart = $this->excludedPackageIdsFromSessionAndUser();

        $q = $this->baseQuery($excludeCart)
            ->whereHas('contents', function ($q2) use ($enrolledContentIds) {
                if ($enrolledContentIds !== []) {
                    $q2->whereNotIn(self::contentColumn('id'), $enrolledContentIds);
                }
            });

        $packages = $this->filterPurchased($q->limit(8)->get());

        if ($packages->isEmpty()) {
            $packages = $this->filterPurchased($this->baseQuery($excludeCart)->limit(4)->get());
        }

        return $packages->take(3)->values();
    }
}
