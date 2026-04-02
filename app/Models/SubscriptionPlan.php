<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'plan_type',
        'billing_period',
        'price',
        'annual_discount_percent',
        'trial_days',
        'is_active',
        'auto_renew_default',
        'content_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'annual_discount_percent' => 'decimal:2',
            'trial_days' => 'integer',
            'is_active' => 'boolean',
            'auto_renew_default' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SubscriptionPlan $plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'content_id');
    }

    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'subscription_plan_content', 'subscription_plan_id', 'content_id')
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->billing_period === 'yearly' && $this->annual_discount_percent > 0) {
            return (float) $this->price * (1 - ((float) $this->annual_discount_percent / 100));
        }

        return (float) $this->price;
    }

    public function effectivePriceForCurrency(?string $currency): float
    {
        $currency = strtoupper((string) $currency);
        $localized = data_get($this->metadata, "localized_pricing.{$currency}.amount");

        if ($localized !== null) {
            return (float) $localized;
        }

        return $this->effective_price;
    }

    /**
     * Abonnement « Membre Herime » / communauté : exclu des listes publiques d’abonnements.
     */
    public function isCommunityPremiumPlan(): bool
    {
        return filter_var(data_get($this->metadata, 'community_premium'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Plan « Premium » : accès automatique à toutes les formations publiées, non téléchargeables, avec au moins une leçon.
     */
    public function isPremiumCatalogPlan(): bool
    {
        return $this->plan_type === 'premium';
    }

    /**
     * Facturation par période (mensuel / semestriel / annuel), essais et renouvellements automatiques.
     */
    public function usesRecurringBilling(): bool
    {
        return in_array($this->plan_type, ['recurring', 'premium'], true);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\SubscriptionPlan>  $plans
     * @return \Illuminate\Support\Collection<int, \App\Models\SubscriptionPlan>
     */
    public static function filterOutCommunityPremium($plans)
    {
        return $plans->filter(fn (self $plan) => ! $plan->isCommunityPremiumPlan())->values();
    }
}


