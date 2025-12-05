<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Ambassador extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'application_id',
        'total_earnings',
        'pending_earnings',
        'paid_earnings',
        'total_referrals',
        'total_sales',
        'is_active',
        'activated_at',
    ];

    protected $casts = [
        'total_earnings' => 'decimal:2',
        'pending_earnings' => 'decimal:2',
        'paid_earnings' => 'decimal:2',
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the application
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(AmbassadorApplication::class, 'application_id');
    }

    /**
     * Get promo codes
     */
    public function promoCodes(): HasMany
    {
        return $this->hasMany(AmbassadorPromoCode::class);
    }

    /**
     * Get active promo code
     */
    public function activePromoCode()
    {
        return $this->promoCodes()->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
            })
            ->first();
    }

    /**
     * Get commissions
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(AmbassadorCommission::class);
    }

    /**
     * Get orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Generate a unique promo code for this ambassador
     */
    public function generatePromoCode(): AmbassadorPromoCode
    {
        $code = strtoupper('AMB' . Str::random(6));
        
        // Ensure uniqueness
        while (AmbassadorPromoCode::where('code', $code)->exists()) {
            $code = strtoupper('AMB' . Str::random(6));
        }

        return $this->promoCodes()->create([
            'code' => $code,
            'name' => 'Code promo ' . $this->user->name,
            'description' => 'Code promo ambassadeur',
            'is_active' => true,
        ]);
    }

    /**
     * Add earnings
     */
    public function addEarnings($amount)
    {
        $this->increment('total_earnings', $amount);
        $this->increment('pending_earnings', $amount);
    }

    /**
     * Mark earnings as paid
     */
    public function markAsPaid($amount)
    {
        $this->decrement('pending_earnings', $amount);
        $this->increment('paid_earnings', $amount);
    }

    /**
     * Increment referrals
     */
    public function incrementReferrals()
    {
        $this->increment('total_referrals');
    }

    /**
     * Increment sales
     */
    public function incrementSales()
    {
        $this->increment('total_sales');
    }

    /**
     * Scope for active ambassadors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
