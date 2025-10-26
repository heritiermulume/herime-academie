<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'name',
        'commission_rate',
        'total_earnings',
        'pending_earnings',
        'paid_earnings',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'pending_earnings' => 'decimal:2',
            'paid_earnings' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(AffiliatePayout::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function calculateCommission($orderTotal)
    {
        return ($orderTotal * $this->commission_rate) / 100;
    }

    public function addEarnings($amount)
    {
        $this->increment('total_earnings', $amount);
        $this->increment('pending_earnings', $amount);
    }

    public function markAsPaid($amount)
    {
        $this->decrement('pending_earnings', $amount);
        $this->increment('paid_earnings', $amount);
    }
}
