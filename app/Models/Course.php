<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Course extends Model
{
    protected $fillable = [
        'instructor_id',
        'category_id',
        'title',
        'slug',
        'description',
        'short_description',
        'thumbnail',
        'video_preview',
        'price',
        'sale_price',
        'is_free',
        'use_external_payment',
        'external_payment_url',
        'external_payment_text',
        'is_published',
        'is_featured',
        'is_downloadable',
        'level',
        'language',
        'tags',
        'requirements',
        'what_you_will_learn',
        'meta_description',
        'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_free' => 'boolean',
            'use_external_payment' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'is_downloadable' => 'boolean',
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
        return $this->sale_price ?? $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->sale_price || $this->sale_price >= $this->price) {
            return 0;
        }
        
        return round((($this->price - $this->sale_price) / $this->price) * 100);
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
                return [
                    'type' => 'link',
                    'url' => route('learning.course', $this->slug),
                    'class' => 'btn btn-success',
                    'text' => 'Accéder au cours',
                    'icon' => 'fas fa-play'
                ];
                
            case 'purchased':
                return [
                    'type' => 'form',
                    'action' => route('student.courses.enroll', $this->slug),
                    'class' => 'btn btn-primary',
                    'text' => 'S\'inscrire au cours',
                    'icon' => 'fas fa-user-plus'
                ];
                
            case 'free':
                return [
                    'type' => 'link',
                    'url' => route('courses.show', $this->slug),
                    'class' => 'btn btn-primary',
                    'text' => 'Voir le cours',
                    'icon' => 'fas fa-eye'
                ];
                
            case 'purchase':
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
                            'class' => 'btn btn-success',
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
                    'text' => 'Se connecter pour s\'inscrire',
                    'icon' => 'fas fa-sign-in-alt'
                ];
        }
    }
}
