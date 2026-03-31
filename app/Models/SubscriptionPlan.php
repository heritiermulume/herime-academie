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
}

