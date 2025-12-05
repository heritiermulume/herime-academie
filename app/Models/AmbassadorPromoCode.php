<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AmbassadorPromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'ambassador_id',
        'code',
        'name',
        'description',
        'usage_count',
        'max_usage',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the ambassador
     */
    public function ambassador(): BelongsTo
    {
        return $this->belongsTo(Ambassador::class);
    }

    /**
     * Get commissions
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(AmbassadorCommission::class, 'promo_code_id');
    }

    /**
     * Get orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'ambassador_promo_code_id');
    }

    /**
     * Check if code is valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_usage && $this->usage_count >= $this->max_usage) {
            return false;
        }

        return true;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    /**
     * Scope for active codes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Scope for valid codes
     */
    public function scopeValid($query)
    {
        return $query->active()
            ->where(function($q) {
                $q->whereNull('max_usage')
                  ->orWhereRaw('usage_count < max_usage');
            });
    }
}
