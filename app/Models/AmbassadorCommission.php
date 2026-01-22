<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AmbassadorCommission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ambassador_id',
        'order_id',
        'promo_code_id',
        'order_total',
        'commission_rate',
        'commission_amount',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'order_total' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the ambassador
     */
    public function ambassador(): BelongsTo
    {
        return $this->belongsTo(Ambassador::class);
    }

    /**
     * Get the order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the promo code
     */
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(AmbassadorPromoCode::class, 'promo_code_id');
    }

    /**
     * Get the status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'paid' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'approved' => 'Approuvée',
            'paid' => 'Payée',
            'cancelled' => 'Annulée',
            default => 'Inconnu',
        };
    }

    /**
     * Mark as paid
     */
    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->ambassador->markAsPaid($this->commission_amount);
    }

    /**
     * Scope for pending commissions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for paid commissions
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
