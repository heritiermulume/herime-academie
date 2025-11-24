<?php

namespace App\Models;

use App\Notifications\CourseModerationNotification;
use App\Notifications\CoursePublishedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use Illuminate\Support\Carbon;

class Course extends Model
{
    /**
     * Résoudre le binding de route pour les routes publiques
     * Ne retourner que les cours publiés pour les routes publiques
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $route = request()->route();
        $routeName = $route ? $route->getName() : null;
        $routeUri = $route ? $route->uri() : '';
        
        // Si on est dans une route admin ou instructor, ne pas filtrer et utiliser l'ID
        $isAdminRoute = $routeName && (
            str_starts_with($routeName, 'admin.') || 
            str_contains($routeName, 'admin')
        );
        $isInstructorRoute = $routeName && (
            str_starts_with($routeName, 'instructor.') ||
            str_contains($routeName, 'instructor')
        );
        $isAdminPath = str_contains(request()->path(), '/admin/') || str_contains(request()->path(), 'admin/');
        $isInstructorPath = str_contains(request()->path(), '/instructor/') || str_contains(request()->path(), 'instructor/');
        
        if ($isAdminRoute || $isInstructorRoute || $isAdminPath || $isInstructorPath) {
            // Pour les routes admin/instructor, utiliser l'ID si c'est numérique, sinon le slug
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
        'instructor_id',
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
        'is_featured',
        'show_students_count',
        'is_downloadable',
        'download_file_path',
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
            'is_featured' => 'boolean',
            'show_students_count' => 'boolean',
            'is_downloadable' => 'boolean',
            'video_preview_is_unlisted' => 'boolean',
            'tags' => 'array',
            'requirements' => 'array',
            'what_you_will_learn' => 'array',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class, 'course_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class, 'course_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(CourseDownload::class);
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
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function getLessonsCountAttribute()
    {
        return $this->sections()->with('lessons')->get()->sum(function($section) {
            return $section->lessons->count();
        });
    }

    /**
     * Obtenir le nombre total d'étudiants inscrits
     */
    public function getTotalStudentsAttribute()
    {
        return $this->enrollments()->count();
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
        return $this->sections()->with('lessons')->get()->sum(function($section) {
            return $section->lessons->sum('duration');
        });
    }

    /**
     * Obtenir le nombre total de leçons
     */
    public function getTotalLessonsAttribute()
    {
        return $this->sections()->with('lessons')->get()->sum(function($section) {
            return $section->lessons->count();
        });
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
        return $this->sections()->with('lessons')->get()->sum(function($section) {
            return $section->lessons->sum('duration');
        });
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
            'total_students' => $this->enrollments ? $this->enrollments->count() : 0,
            'average_rating' => $this->reviews ? $this->reviews->avg('rating') ?? 0 : 0,
            'total_reviews' => $this->reviews ? $this->reviews->count() : 0,
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
     * Returns: 'enrolled', 'purchased', 'free', 'purchase', 'login'
     */
    public function getButtonStateForUser($userId = null): string
    {
        if (!$userId) {
            return 'login';
        }

        // Check if user is enrolled (for both free and paid courses)
        if ($this->isEnrolledBy($userId)) {
            return 'enrolled';
        }

        // Check if user has purchased the course (for paid courses only)
        if (!$this->is_free) {
            $hasPurchased = \App\Models\Order::where('user_id', $userId)
                ->where('status', 'paid')
                ->whereHas('orderItems', function($query) {
                    $query->where('course_id', $this->id);
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
        $features = [];
        
        // Nombre de leçons (toujours affiché si > 0)
        $totalLessons = $this->getTotalLessonsAttribute();
        if ($totalLessons > 0) {
            $features[] = [
                'icon' => 'fa-play-circle',
                'text' => $totalLessons . ' leçon' . ($totalLessons > 1 ? 's' : '')
            ];
        }
        
        // Durée totale (toujours affichée si > 0)
        $totalDuration = $this->getTotalDurationAttribute();
        if ($totalDuration > 0) {
            $features[] = [
                'icon' => 'fa-clock',
                'text' => $totalDuration . ' minute' . ($totalDuration > 1 ? 's' : '') . ' de contenu'
            ];
        }
        
        // Accès mobile et desktop (toujours disponible pour les cours en ligne)
        $features[] = [
            'icon' => 'fa-mobile-alt',
            'text' => 'Accès mobile et desktop'
        ];
        
        // Certificat de fin de cours (vérifier si des certificats sont configurés pour ce cours)
        // On considère qu'un certificat est disponible si le cours a au moins une section avec des leçons
        if ($this->sections->count() > 0) {
            $features[] = [
                'icon' => 'fa-certificate',
                'text' => 'Certificat de fin de cours'
            ];
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
    }

    /**
     * Obtenir l'URL de la miniature (thumbnail)
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail) {
            return '';
        }

        if (filter_var($this->thumbnail, FILTER_VALIDATE_URL)) {
            return $this->thumbnail;
        }

        $service = app(\App\Services\FileUploadService::class);
        return $service->getUrl($this->thumbnail, 'courses/thumbnails');
    }

    /**
     * Obtenir l'URL de la vidéo de prévisualisation
     */
    public function getVideoPreviewUrlAttribute(): ?string
    {
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
        return !empty($this->video_preview_youtube_id);
    }

    /**
     * Obtenir l'URL d'embed YouTube sécurisée pour la prévisualisation
     */
    public function getSecureYouTubePreviewEmbedUrl(): ?string
    {
        if (!$this->isYoutubePreviewVideo()) {
            return null;
        }

        $videoId = $this->video_preview_youtube_id;
        $params = [
            'rel' => 0,
            'modestbranding' => 1,
            'iv_load_policy' => 3,
            'origin' => config('video.youtube.embed_domain', request()->getHost()),
        ];

        return "https://www.youtube.com/embed/{$videoId}?" . http_build_query($params);
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

        // Si paiement externe: autoriser le lien direct même pour les invités (pas d'auth requise)
        if ($this->use_external_payment && $this->external_payment_url && in_array($state, ['purchase', 'login'])) {
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
                        'url' => route('courses.download', $this->slug),
                        'class' => 'btn btn-primary',
                        'text' => 'Télécharger',
                        'icon' => 'fas fa-download'
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
                    'icon' => 'fas fa-play'
                ];
                
            case 'purchased':
                // Si le cours est téléchargeable, proposer l'inscription pour accéder au tableau de bord
                if ($this->is_downloadable) {
                    return [
                        'type' => 'form',
                        'action' => route('student.courses.enroll', $this->slug),
                        'class' => 'btn btn-primary',
                        'text' => 'S\'inscrire',
                        'icon' => 'fas fa-user-plus'
                    ];
                }
                
                // Pour les cours non téléchargeables, proposer de s'inscrire pour commencer l'apprentissage
                return [
                    'type' => 'form',
                    'action' => route('student.courses.enroll', $this->slug),
                    'class' => 'btn btn-primary',
                    'text' => 'S\'inscrire',
                    'icon' => 'fas fa-user-plus'
                ];
                
            case 'free':
                // Pour les cours gratuits non inscrits, afficher le bouton "S'inscrire" (bleu foncé)
                return [
                    'type' => 'form',
                    'action' => route('student.courses.enroll', $this->slug),
                    'class' => 'btn btn-primary',
                    'text' => 'S\'inscrire',
                    'icon' => 'fas fa-user-plus'
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
                            'onclick' => "addToCart({$this->id})"
                        ],
                        [
                            'type' => 'button',
                            'class' => 'btn btn-primary',
                            'text' => 'Procéder au paiement',
                            'icon' => 'fas fa-credit-card',
                            'onclick' => 'proceedToCheckout(' . $this->id . ')'
                        ]
                    ]
                ];
                
            case 'login':
            default:
                return [
                    'type' => 'link',
                    'url' => route('login'),
                    'class' => 'btn btn-primary',
                    'text' => 'Se connecter',
                    'icon' => 'fas fa-sign-in-alt'
                ];
        }
    }

    public function notifyModeratorStatus(?string $status = null): void
    {
        $status = $status ?? ($this->is_published ? 'approved' : 'pending');

        if (!$this->relationLoaded('instructor')) {
            $this->load('instructor');
        }

        if ($this->instructor) {
            // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
            Notification::sendNow($this->instructor, new CourseModerationNotification($this, $status));
        }
    }

    public function notifyStudentsOfNewCourse(): void
    {
        $freshCourse = $this->fresh(['instructor', 'category']);

        // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
        User::where('is_active', true)
            ->chunk(200, function ($users) use ($freshCourse) {
                Notification::sendNow($users, new CoursePublishedNotification($freshCourse));
            });
    }
}
