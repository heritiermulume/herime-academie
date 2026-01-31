<?php

namespace App\Models;

use App\Notifications\CourseModerationNotification;
use App\Notifications\CoursePublishedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use Illuminate\Support\Carbon;

class Course extends Model
{
    use SoftDeletes;
    
    protected $table = 'contents';
    /**
     * Résoudre le binding de route pour les routes publiques
     * Ne retourner que les cours publiés pour les routes publiques
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $route = request()->route();
        $routeName = $route ? $route->getName() : null;
        $routeUri = $route ? $route->uri() : '';
        
        // Si on est dans une route admin ou provider, ne pas filtrer et utiliser l'ID
        $isAdminRoute = $routeName && (
            str_starts_with($routeName, 'admin.') || 
            str_contains($routeName, 'admin')
        );
        $isProviderRoute = $routeName && (
            str_starts_with($routeName, 'provider.') ||
            str_contains($routeName, 'provider')
        );
        $isAdminPath = str_contains(request()->path(), '/admin/') || str_contains(request()->path(), 'admin/');
        $isProviderPath = str_contains(request()->path(), '/provider/') || str_contains(request()->path(), 'provider/');
        
        if ($isAdminRoute || $isProviderRoute || $isAdminPath || $isProviderPath) {
            // Pour les routes admin/provider, utiliser l'ID si c'est numérique, sinon le slug
            if (is_numeric($value)) {
                return static::where('id', $value)->firstOrFail();
            }
            return static::where('slug', $value)->firstOrFail();
        }
        
        // Pour les routes publiques, utiliser le slug et ne retourner que les cours publiés
        $field = $field ?? $this->getRouteKeyName();
        return static::where($field, $value)
            ->where('is_published', true)
            ->firstOrFail();
    }

    /**
     * Obtenir le nom de la clé de route (slug)
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
    protected $fillable = [
        'provider_id',
        'category_id',
        'title',
        'slug',
        'description',
        'short_description',
        'thumbnail',
        'video_preview',
        'video_preview_youtube_id',
        'video_preview_is_unlisted',
        'price',
        'sale_price',
        'sale_start_at',
        'sale_end_at',
        'is_free',
        'use_external_payment',
        'external_payment_url',
        'external_payment_text',
        'is_published',
        'is_sale_enabled',
        'is_featured',
        'show_customers_count',
        'is_downloadable',
        'download_file_path',
        'is_in_person_program',
        'whatsapp_number',
        'level',
        'language',
        'tags',
        'requirements',
        'what_you_will_learn',
        'meta_description',
        'meta_keywords',
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
            'is_free' => 'boolean',
            'use_external_payment' => 'boolean',
            'is_published' => 'boolean',
            'is_sale_enabled' => 'boolean',
            'is_featured' => 'boolean',
            'show_customers_count' => 'boolean',
            'is_downloadable' => 'boolean',
            'is_in_person_program' => 'boolean',
            'video_preview_is_unlisted' => 'boolean',
            'tags' => 'array',
            'requirements' => 'array',
            'what_you_will_learn' => 'array',
        ];
    }

    public function getWhatsappChatUrlAttribute(): ?string
    {
        try {
            if (!($this->is_in_person_program ?? false)) {
                return null;
            }

            $raw = (string) ($this->whatsapp_number ?? '');
            $digits = preg_replace('/\D+/', '', $raw) ?? '';

            if ($digits === '') {
                return null;
            }

            return 'https://wa.me/' . $digits;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Alias pour compatibilité avec le code existant
     */
    public function instructor(): BelongsTo
    {
        return $this->provider();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class, 'content_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class, 'content_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'content_id');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(CourseDownload::class, 'content_id');
    }

    /**
     * Obtenir le nombre total de téléchargements
     */
    public function getDownloadsCountAttribute()
    {
        return $this->downloads()->count();
    }

    /**
     * Obtenir le nombre de téléchargements uniques (utilisateurs uniques)
     */
    public function getUniqueDownloadsCountAttribute()
    {
        return $this->downloads()->distinct('user_id')->count('user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'content_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'content_id');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'content_id');
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class, 'content_id');
    }

    public function getLessonsCountAttribute()
    {
        try {
            if (!$this->relationLoaded('sections')) {
                $this->load('sections.lessons');
            }
            
            if (!$this->sections) {
                return 0;
            }
            
            return $this->sections->sum(function($section) {
                if (!$section || !$section->lessons) {
                    return 0;
                }
                return $section->lessons->count();
            });
        } catch (\Throwable $e) {
            \Log::warning('Erreur lors de getLessonsCountAttribute', [
                'content_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Obtenir le nombre total de clients inscrits
     * Retourne toujours les inscriptions (pas les achats)
     */
    public function getTotalCustomersAttribute()
    {
        return $this->enrollments()->count();
    }

    /**
     * Alias pour compatibilité avec le code existant (ancien nom: getTotalStudentsAttribute)
     */
    public function getTotalStudentsAttribute()
    {
        return $this->getTotalCustomersAttribute();
    }

    /**
     * Obtenir le nombre d'achats (commandes payées ou complétées)
     */
    public function getPurchasesCountAttribute()
    {
        try {
            // Vérifier si la relation orderItems existe
            if (!method_exists($this, 'orderItems')) {
                return 0;
            }
            
            return $this->orderItems()
                ->whereHas('order', function($query) {
                    $query->whereIn('status', ['paid', 'completed']);
                })
                ->count();
        } catch (\Throwable $e) {
            // En cas d'erreur (table manquante, relation non définie, etc.), retourner 0
            \Log::warning('Erreur lors du calcul de purchases_count pour le cours ' . $this->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * Obtenir le nombre d'acheteurs uniques
     */
    public function getUniquePurchasersCountAttribute()
    {
        return $this->orderItems()
            ->whereHas('order', function($query) {
                $query->whereIn('status', ['paid', 'completed']);
            })
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->distinct('orders.user_id')
            ->count('orders.user_id');
    }

    /**
     * Obtenir la somme totale des achats (revenus)
     */
    public function getTotalPurchasesRevenueAttribute()
    {
        return $this->orderItems()
            ->whereHas('order', function($query) {
                $query->whereIn('status', ['paid', 'completed']);
            })
            ->sum('total');
    }

    /**
     * Obtenir le nombre total de téléchargements
     */
    public function getTotalDownloadsCountAttribute()
    {
        return $this->downloads()->count();
    }

    /**
     * Obtenir la note moyenne des avis
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Déterminer si une promotion est active.
     */
    public function getIsSaleActiveAttribute(): bool
    {
        $salePrice = $this->attributes['sale_price'] ?? null;

        if (is_null($salePrice)) {
            return false;
        }

        $now = Carbon::now();
        $saleStart = $this->sale_start_at;
        $saleEnd = $this->sale_end_at;

        if ($saleStart instanceof Carbon && $now->lt($saleStart)) {
            return false;
        }

        if ($saleEnd instanceof Carbon && $now->greaterThan($saleEnd)) {
            return false;
        }

        return true;
    }

    /**
     * Prix promotionnel actif (ou null si la promotion n'est pas active).
     */
    protected function activeSalePrice(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->is_sale_active) {
                return null;
            }

            $salePrice = $this->attributes['sale_price'] ?? null;

            return is_null($salePrice) ? null : (float) $salePrice;
        });
    }

    /**
     * Prix effectif à afficher (prix normal ou promotionnel actif).
     */
    protected function effectivePrice(): Attribute
    {
        return Attribute::get(function () {
            if ($this->is_free) {
                return 0.0;
            }

            if ($this->active_sale_price !== null) {
                return $this->active_sale_price;
            }

            $price = $this->attributes['price'] ?? null;

            return is_null($price) ? null : (float) $price;
        });
    }

    /**
     * Pourcentage de réduction appliqué lorsque la promotion est active.
     */
    public function getSaleDiscountPercentageAttribute(): ?int
    {
        if (! $this->is_sale_active) {
            return null;
        }

        $price = $this->attributes['price'] ?? null;
        $salePrice = $this->active_sale_price;

        if (is_null($price) || $price <= 0 || is_null($salePrice)) {
            return null;
        }

        return (int) round((($price - $salePrice) / $price) * 100);
    }

    /**
     * Obtenir le nombre total d'avis
     */
    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Obtenir la durée totale du cours
     */
    public function getTotalDurationAttribute()
    {
        try {
            if (!$this->relationLoaded('sections')) {
                $this->load('sections.lessons');
            }
            
            if (!$this->sections) {
                return 0;
            }
            
            return $this->sections->sum(function($section) {
                if (!$section || !$section->lessons) {
                    return 0;
                }
                return $section->lessons->sum('duration') ?? 0;
            });
        } catch (\Throwable $e) {
            \Log::warning('Erreur lors de getTotalDurationAttribute', [
                'content_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Obtenir le nombre total de leçons
     */
    public function getTotalLessonsAttribute()
    {
        try {
            if (!$this->relationLoaded('sections')) {
                $this->load('sections.lessons');
            }
            
            if (!$this->sections) {
                return 0;
            }
            
            return $this->sections->sum(function($section) {
                if (!$section || !$section->lessons) {
                    return 0;
                }
                return $section->lessons->count();
            });
        } catch (\Throwable $e) {
            \Log::warning('Erreur lors de getTotalLessonsAttribute', [
                'content_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Obtenir des initiales pour le titre du cours (fallback visuel)
     */
    public function getInitialsAttribute(): string
    {
        $title = trim((string) $this->title);

        if ($title === '') {
            return 'HC';
        }

        $parts = preg_split('/\s+/u', $title) ?: [];
        $first = mb_substr($parts[0] ?? '', 0, 1, 'UTF-8');
        $second = mb_substr($parts[1] ?? '', 0, 1, 'UTF-8');

        $initials = mb_strtoupper($first . $second, 'UTF-8');

        return $initials !== '' ? $initials : 'HC';
    }

    /**
     * Obtenir la durée totale du cours (en minutes)
     */
    public function getDurationAttribute()
    {
        try {
            if (!$this->relationLoaded('sections')) {
                $this->load('sections.lessons');
            }
            
            if (!$this->sections) {
                return 0;
            }
            
            return $this->sections->sum(function($section) {
                if (!$section || !$section->lessons) {
                    return 0;
                }
                return $section->lessons->sum('duration') ?? 0;
            });
        } catch (\Throwable $e) {
            \Log::warning('Erreur lors de getDurationAttribute', [
                'content_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }


    /**
     * Obtenir toutes les statistiques du cours
     */
    public function getCourseStats()
    {
        // Charger les relations si elles ne sont pas déjà chargées
        if (!$this->relationLoaded('sections')) {
            $this->load(['sections.lessons', 'reviews', 'enrollments']);
        }

        return [
            'total_lessons' => $this->sections ? $this->sections->sum(function($section) {
                return $section->lessons ? $section->lessons->count() : 0;
            }) : 0,
            'total_duration' => $this->sections ? $this->sections->sum(function($section) {
                return $section->lessons ? $section->lessons->sum('duration') : 0;
            }) : 0,
            'total_customers' => $this->total_customers, // Nombre d'inscriptions
            'total_students' => $this->total_customers, // Alias pour compatibilité
            'purchases_count' => $this->purchases_count, // Nombre d'achats (pour tous les cours)
            'average_rating' => $this->reviews ? $this->reviews->avg('rating') ?? 0 : 0,
            'total_reviews' => $this->reviews ? $this->reviews->count() : 0,
            // Statistiques supplémentaires pour les produits téléchargeables
            'total_downloads' => $this->is_downloadable ? $this->total_downloads_count : null,
            'unique_downloads' => $this->is_downloadable ? $this->unique_downloads_count : null,
            'total_revenue' => $this->is_downloadable ? $this->total_purchases_revenue : null,
        ];
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_free', false);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopePopular($query)
    {
        return $query->withCount('enrollments')->orderBy('enrollments_count', 'desc');
    }

    public function scopeTopRated($query)
    {
        return $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Helper methods
    public function getCurrentPriceAttribute()
    {
        return $this->effective_price ?? $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        return $this->sale_discount_percentage ?? 0;
    }

    public function isEnrolledBy($userId)
    {
        return $this->enrollments()->where('user_id', $userId)->exists();
    }

    public function getEnrollmentFor($userId)
    {
        return $this->enrollments()->where('user_id', $userId)->first();
    }

    /**
     * Get requirements as array, ensuring it's always an array
     */
    public function getRequirementsArray(): array
    {
        if (is_array($this->requirements)) {
            return $this->requirements;
        }
        
        if (is_string($this->requirements)) {
            return json_decode($this->requirements, true) ?? [];
        }
        
        return [];
    }

    /**
     * Get what you will learn as array, ensuring it's always an array
     */
    public function getWhatYouWillLearnArray(): array
    {
        if (is_array($this->what_you_will_learn)) {
            return $this->what_you_will_learn;
        }
        
        if (is_string($this->what_you_will_learn)) {
            return json_decode($this->what_you_will_learn, true) ?? [];
        }
        
        return [];
    }

    /**
     * Get tags as array, ensuring it's always an array
     */
    public function getTagsArray(): array
    {
        if (is_array($this->tags)) {
            return $this->tags;
        }
        
        if (is_string($this->tags)) {
            return json_decode($this->tags, true) ?? [];
        }
        
        return [];
    }

    /**
     * Get the course button state for a user
     * Returns: 'enrolled', 'purchased', 'free', 'purchase', 'login', 'sale_disabled'
     */
    public function getButtonStateForUser($userId = null): string
    {
        if (!$userId) {
            // Si la vente est désactivée, on affiche quand même le bouton de connexion
            // mais on vérifiera après la connexion
            return 'login';
        }

        // Check if user is enrolled (for both free and paid courses)
        // Les utilisateurs déjà inscrits peuvent toujours accéder au cours
        if ($this->isEnrolledBy($userId)) {
            return 'enrolled';
        }

        // Si la vente/inscription est désactivée, retourner l'état spécial
        if (!$this->is_sale_enabled) {
            return 'sale_disabled';
        }

        // Check if user has purchased the course (for paid courses only)
        if (!$this->is_free) {
            $hasPurchased = \App\Models\Order::where('user_id', $userId)
                ->where('status', 'paid')
                ->whereHas('orderItems', function($query) {
                    $query->where('content_id', $this->id);
                })
                ->exists();
            
            if ($hasPurchased) {
                return 'purchased';
            }
            
            return 'purchase';
        }

        // Free course - not enrolled yet
        return 'free';
    }

    /**
     * Get course features as array dynamically
     * Returns an array of features based on course properties
     */
    public function getCourseFeatures(): array
    {
        try {
            $features = [];
            
            // Nombre de leçons (toujours affiché si > 0)
            try {
                $totalLessons = $this->getTotalLessonsAttribute();
                if ($totalLessons > 0) {
                    $features[] = [
                        'icon' => 'fa-play-circle',
                        'text' => $totalLessons . ' leçon' . ($totalLessons > 1 ? 's' : '')
                    ];
                }
            } catch (\Throwable $e) {
                \Log::warning('Erreur lors du calcul de totalLessons dans getCourseFeatures', ['content_id' => $this->id, 'error' => $e->getMessage()]);
            }
            
            // Durée totale (toujours affichée si > 0)
            try {
                $totalDuration = $this->getTotalDurationAttribute();
                if ($totalDuration > 0) {
                    $features[] = [
                        'icon' => 'fa-clock',
                        'text' => $totalDuration . ' minute' . ($totalDuration > 1 ? 's' : '') . ' de contenu'
                    ];
                }
            } catch (\Throwable $e) {
                \Log::warning('Erreur lors du calcul de totalDuration dans getCourseFeatures', ['content_id' => $this->id, 'error' => $e->getMessage()]);
            }
            
            // Accès mobile et desktop (toujours disponible pour les cours en ligne)
            $features[] = [
                'icon' => 'fa-mobile-alt',
                'text' => 'Accès mobile et desktop'
            ];
            
            // Certificat de fin de cours (uniquement pour les cours non téléchargeables)
            // On considère qu'un certificat est disponible si le cours a au moins une section avec des leçons
            try {
                if (!$this->is_downloadable && $this->sections && $this->sections->count() > 0) {
                    $features[] = [
                        'icon' => 'fa-certificate',
                        'text' => 'Certificat de fin de cours'
                    ];
                }
            } catch (\Throwable $e) {
                \Log::warning('Erreur lors de la vérification des sections dans getCourseFeatures', ['content_id' => $this->id, 'error' => $e->getMessage()]);
            }
            
            // Accès à vie (par défaut, les cours n'ont pas d'expiration)
            $features[] = [
                'icon' => 'fa-infinity',
                'text' => 'Accès à vie'
            ];
            
            // Téléchargement disponible (si is_downloadable est activé)
            if ($this->is_downloadable) {
                $features[] = [
                    'icon' => 'fa-download',
                    'text' => 'Téléchargement disponible'
                ];
            }
            
            return $features;
        } catch (\Throwable $e) {
            \Log::error('Erreur fatale dans getCourseFeatures', [
                'content_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Retourner des features par défaut en cas d'erreur
            return [
                ['icon' => 'fa-play-circle', 'text' => 'Contenu vidéo'],
                ['icon' => 'fa-mobile-alt', 'text' => 'Accès mobile et desktop'],
                ['icon' => 'fa-infinity', 'text' => 'Accès à vie']
            ];
        }
    }

    /**
     * Obtenir l'URL de la miniature (thumbnail)
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        try {
            if (!$this->thumbnail) {
                return '';
            }

            if (filter_var($this->thumbnail, FILTER_VALIDATE_URL)) {
                return $this->thumbnail;
            }

            $service = app(\App\Services\FileUploadService::class);
            return $service->getUrl($this->thumbnail, 'courses/thumbnails');
        } catch (\Throwable $e) {
            \Log::warning('Erreur lors de getThumbnailUrlAttribute', [
                'content_id' => $this->id,
                'thumbnail' => $this->thumbnail ?? null,
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }

    /**
     * Obtenir l'URL de la vidéo de prévisualisation
     */
    public function getVideoPreviewUrlAttribute(): ?string
    {
        try {
            // Si YouTube est utilisé, retourner l'URL d'embed
            if ($this->isYoutubePreviewVideo()) {
                return $this->getSecureYouTubePreviewEmbedUrl();
            }
            
            if (!$this->video_preview) {
                return '';
            }

            if (filter_var($this->video_preview, FILTER_VALIDATE_URL)) {
                return $this->video_preview;
            }

            $service = app(\App\Services\FileUploadService::class);
            return $service->getUrl($this->video_preview, 'courses/previews');
        } catch (\Throwable $e) {
            \Log::warning('Erreur lors de getVideoPreviewUrlAttribute', [
                'content_id' => $this->id,
                'video_preview' => $this->video_preview ?? null,
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }

    /**
     * Obtenir l'URL du fichier de téléchargement
     */
    public function getDownloadFileUrlAttribute(): ?string
    {
        if (!$this->download_file_path) {
            return '';
        }

        if (filter_var($this->download_file_path, FILTER_VALIDATE_URL)) {
            return $this->download_file_path;
        }

        $service = app(\App\Services\FileUploadService::class);
        return $service->getUrl($this->download_file_path, 'courses/downloads');
    }

    /**
     * Vérifier si la prévisualisation utilise YouTube
     */
    public function isYoutubePreviewVideo(): bool
    {
        try {
            return !empty($this->video_preview_youtube_id);
        } catch (\Throwable $e) {
            \Log::warning('Erreur lors de isYoutubePreviewVideo', [
                'content_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtenir l'URL d'embed YouTube sécurisée pour la prévisualisation
     */
    public function getSecureYouTubePreviewEmbedUrl(): ?string
    {
        try {
            if (!$this->isYoutubePreviewVideo()) {
                return null;
            }

            $videoId = $this->video_preview_youtube_id;
            if (empty($videoId)) {
                return null;
            }
            
            $params = [
                'rel' => 0,
                'modestbranding' => 1,
                'iv_load_policy' => 3,
                'origin' => config('video.youtube.embed_domain', request()->getHost()),
            ];

            return "https://www.youtube.com/embed/{$videoId}?" . http_build_query($params);
        } catch (\Throwable $e) {
            \Log::warning('Erreur lors de getSecureYouTubePreviewEmbedUrl', [
                'content_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtenir l'URL YouTube de la prévisualisation
     */
    public function getYouTubePreviewWatchUrl(): ?string
    {
        if (!$this->isYoutubePreviewVideo()) {
            return null;
        }

        return "https://www.youtube.com/watch?v={$this->video_preview_youtube_id}";
    }

    /**
     * Get button configuration for a user
     */
    public function getButtonConfigForUser($userId = null): array
    {
        $state = $this->getButtonStateForUser($userId);

        // Si la vente est désactivée et l'utilisateur n'est pas inscrit
        if ($state === 'sale_disabled') {
            return [
                'type' => 'disabled',
                'class' => 'btn btn-secondary disabled',
                'text' => 'Indisponible',
                'icon' => 'fas fa-ban',
                'tooltip' => 'Ce cours n\'est pas actuellement disponible à l\'achat ou à l\'inscription'
            ];
        }

        // Si paiement externe: autoriser le lien direct même pour les invités (pas d'auth requise)
        // Mais seulement si la vente est activée
        if ($this->is_sale_enabled && $this->use_external_payment && $this->external_payment_url && in_array($state, ['purchase', 'login'])) {
            return [
                'type' => 'link',
                'url' => $this->external_payment_url,
                'class' => 'btn btn-primary',
                'text' => $this->external_payment_text ?: 'Acheter maintenant',
                'icon' => 'fas fa-external-link-alt',
                'target' => '_blank'
            ];
        }
        
        switch ($state) {
            case 'enrolled':
                // Si le cours est téléchargeable, afficher le bouton "Télécharger"
                if ($this->is_downloadable) {
                    return [
                        'type' => 'link',
                        'url' => route('contents.download', $this->slug),
                        'class' => 'btn btn-primary',
                        'text' => 'Télécharger',
                        'icon' => 'fas fa-download',
                        'meta_trigger' => 'download',
                    ];
                }
                
                // Pour les cours non téléchargeables, afficher "Commencer" ou "Continuer" selon la progression
                $enrollment = $this->getEnrollmentFor($userId);
                $progress = $enrollment ? ($enrollment->progress ?? 0) : 0;
                $buttonText = $progress > 0 ? 'Continuer' : 'Commencer';
                
                return [
                    'type' => 'link',
                    'url' => route('learning.course', $this->slug),
                    'class' => 'btn btn-success',
                    'text' => $buttonText,
                    'icon' => 'fas fa-play',
                    'meta_trigger' => 'learn',
                ];
                
            case 'purchased':
                // Si le cours est téléchargeable, proposer le téléchargement direct (pas d'inscription nécessaire)
                if ($this->is_downloadable) {
                    return [
                        'type' => 'link',
                        'url' => route('contents.download', $this->slug),
                        'class' => 'btn btn-primary',
                        'text' => 'Télécharger',
                        'icon' => 'fas fa-download',
                        'meta_trigger' => 'download',
                    ];
                }
                
                // Pour les cours non téléchargeables, proposer de s'inscrire pour commencer l'apprentissage
                return [
                    'type' => 'form',
                    'action' => route('customer.contents.enroll', $this->slug),
                    'class' => 'btn btn-primary',
                    'text' => 'S\'inscrire',
                    'icon' => 'fas fa-user-plus',
                    'meta_trigger' => 'enroll',
                ];
                
            case 'free':
                // Pour les cours gratuits non inscrits
                // Si téléchargeable : "Intéresser", sinon : "S'inscrire"
                $buttonText = $this->is_downloadable ? 'Intéresser' : 'S\'inscrire';
                return [
                    'type' => 'form',
                    'action' => route('customer.contents.enroll', $this->slug),
                    'class' => 'btn btn-primary',
                    'text' => $buttonText,
                    'icon' => 'fas fa-user-plus',
                    'meta_trigger' => 'enroll',
                ];
                
            case 'purchase':
                // Pour les cours payants non inscrits, afficher 2 boutons : "Ajouter au panier" et "Procéder au paiement"
                return [
                    'type' => 'buttons',
                    'buttons' => [
                        [
                            'type' => 'button',
                            'class' => 'btn btn-outline-primary',
                            'text' => 'Ajouter au panier',
                            'icon' => 'fas fa-shopping-cart',
                            'onclick' => "addToCart({$this->id})",
                            'meta_trigger' => 'add_to_cart',
                        ],
                        [
                            'type' => 'button',
                            'class' => 'btn btn-success',
                            'text' => 'Procéder au paiement',
                            'icon' => 'fas fa-credit-card',
                            'onclick' => 'proceedToCheckout(' . $this->id . ')',
                            'meta_trigger' => 'checkout',
                        ]
                    ]
                ];
                
            case 'login':
            default:
                // Générer l'URL SSO pour la connexion
                $currentUrl = url()->current();
                $callbackUrl = route('sso.callback', ['redirect' => $currentUrl]);
                $ssoLoginUrl = config('services.sso.base_url', 'https://compte.herime.com') . '/login?force_token=1&redirect=' . urlencode($callbackUrl);
                
                return [
                    'type' => 'link',
                    'url' => $ssoLoginUrl,
                    'class' => 'btn btn-primary',
                    'text' => 'Se connecter',
                    'icon' => 'fas fa-sign-in-alt'
                ];
        }
    }

    public function notifyModeratorStatus(?string $status = null): void
    {
        $status = $status ?? ($this->is_published ? 'approved' : 'pending');

        if (!$this->relationLoaded('provider')) {
            $this->load('provider');
        }

        if ($this->provider) {
            // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
            Notification::sendNow($this->provider, new CourseModerationNotification($this, $status));
        }
    }

    public function notifyCustomersOfNewCourse(): void
    {
        $freshCourse = $this->fresh(['provider', 'category']);

        // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
        User::where('is_active', true)
            ->chunk(200, function ($users) use ($freshCourse) {
                Notification::sendNow($users, new CoursePublishedNotification($freshCourse));
            });
    }
}
