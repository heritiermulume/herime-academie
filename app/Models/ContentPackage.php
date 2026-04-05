<?php

namespace App\Models;

use App\Models\Pivots\ContentPackageContentPivot;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Pack commercial : le prix de vente forfait est l’attribut calculé `effective_price`.
 * Ne pas utiliser `contents_list_price_total` comme prix de vente (c’est une valeur indicative si les contenus sont achetés séparément).
 */
class ContentPackage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'short_description',
        'description',
        'thumbnail',
        'cover_video',
        'cover_video_hls_manifest_path',
        'cover_video_hls_status',
        'cover_video_youtube_id',
        'cover_video_is_unlisted',
        'price',
        'sale_price',
        'sale_start_at',
        'sale_end_at',
        'is_sale_enabled',
        'is_published',
        'is_featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'marketing_headline',
        'marketing_highlights',
        'marketing_benefits',
        'cta_label',
        'sort_order',
    ];

    protected $appends = [
        'is_sale_active',
        'active_sale_price',
        'effective_price',
        'sale_discount_percentage',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'sale_start_at' => 'datetime',
            'sale_end_at' => 'datetime',
            'is_sale_enabled' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'cover_video_is_unlisted' => 'boolean',
            'marketing_highlights' => 'array',
            'marketing_benefits' => 'array',
        ];
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $route = request()->route();
        $routeName = $route ? (string) $route->getName() : '';
        $isAdmin = str_starts_with($routeName, 'admin.')
            || str_contains(request()->path(), '/admin/');

        if ($routeName === 'customer.pack') {
            $field = $field ?? $this->getRouteKeyName();
            $package = static::where($field, $value)->firstOrFail();
            if (! auth()->check() || ! auth()->user()->hasPurchasedContentPackage($package)) {
                abort(403);
            }

            return $package;
        }

        if ($isAdmin) {
            if (is_numeric($value)) {
                return static::where('id', $value)->firstOrFail();
            }

            return static::where('slug', $value)->firstOrFail();
        }

        $field = $field ?? $this->getRouteKeyName();

        return static::where($field, $value)
            ->where('is_published', true)
            ->firstOrFail();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'content_package_content', 'content_package_id', 'content_id')
            ->using(ContentPackageContentPivot::class)
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function cartPackages(): HasMany
    {
        return $this->hasMany(CartPackage::class, 'content_package_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'content_package_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('created_at');
    }

    public function getIsSaleActiveAttribute(): bool
    {
        if (! $this->is_sale_enabled) {
            return false;
        }

        $salePrice = $this->attributes['sale_price'] ?? null;
        if ($salePrice === null || $salePrice === '') {
            return false;
        }

        $now = Carbon::now();
        if ($this->sale_start_at instanceof Carbon && $now->lt($this->sale_start_at)) {
            return false;
        }
        if ($this->sale_end_at instanceof Carbon && $now->greaterThan($this->sale_end_at)) {
            return false;
        }

        return true;
    }

    protected function activeSalePrice(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->is_sale_active) {
                return null;
            }
            $salePrice = $this->attributes['sale_price'] ?? null;

            return $salePrice === null || $salePrice === '' ? null : (float) $salePrice;
        });
    }

    protected function effectivePrice(): Attribute
    {
        return Attribute::get(function () {
            if ($this->active_sale_price !== null) {
                return $this->active_sale_price;
            }
            $price = $this->attributes['price'] ?? null;

            return $price === null || $price === '' ? null : (float) $price;
        });
    }

    public function getSaleDiscountPercentageAttribute(): ?int
    {
        if (! $this->is_sale_active || $this->active_sale_price === null) {
            return null;
        }
        $base = (float) ($this->attributes['price'] ?? 0);
        if ($base <= 0) {
            return null;
        }

        return (int) round((1 - ($this->active_sale_price / $base)) * 100);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        try {
            if (! $this->thumbnail) {
                return '';
            }
            if (filter_var($this->thumbnail, FILTER_VALIDATE_URL)) {
                return $this->thumbnail;
            }
            $service = app(\App\Services\FileUploadService::class);

            return $service->getUrl($this->thumbnail, 'packages/thumbnails');
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function isYoutubeCoverVideo(): bool
    {
        return ! empty($this->cover_video_youtube_id);
    }

    public function hasCoverVideoHlsStreamReady(): bool
    {
        return ($this->cover_video_hls_status ?? null) === 'ready'
            && ! empty($this->cover_video_hls_manifest_path);
    }

    public function getCoverVideoHlsManifestUrlAttribute(): string
    {
        if (! $this->hasCoverVideoHlsStreamReady()) {
            return '';
        }

        $p = ltrim((string) $this->cover_video_hls_manifest_path, '/');

        return route('files.serve', ['type' => 'package-covers', 'path' => $p]);
    }

    public function getCoverVideoUrlAttribute(): ?string
    {
        try {
            if ($this->isYoutubeCoverVideo()) {
                $videoId = $this->cover_video_youtube_id;
                $params = [
                    'rel' => 0,
                    'modestbranding' => 1,
                    'iv_load_policy' => 3,
                    'origin' => config('video.youtube.embed_domain', request()->getHost()),
                ];

                return "https://www.youtube.com/embed/{$videoId}?".http_build_query($params);
            }
            if (! $this->cover_video) {
                return '';
            }
            if (filter_var($this->cover_video, FILTER_VALIDATE_URL)) {
                return $this->cover_video;
            }
            $service = app(\App\Services\FileUploadService::class);

            return $service->getUrl($this->cover_video, 'packages/covers');
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Somme des prix publics des contenus si achetés séparément (comparaison marketing uniquement).
     * Le prix de vente du pack est `effective_price` — ne pas utiliser cette somme comme montant facturé.
     */
    public function getContentsListPriceTotalAttribute(): float
    {
        if (! $this->relationLoaded('contents')) {
            $this->load('contents');
        }

        return (float) $this->contents->sum(function (Course $c) {
            return (float) ($c->effective_price ?? $c->price ?? 0);
        });
    }
}
