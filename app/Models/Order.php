<?php

namespace App\Models;

use App\Notifications\OrderStatusUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected static function booted(): void
    {
        static::created(function (Order $order) {
            $order->notifyStatus($order->status ?? 'pending');
        });

        static::updated(function (Order $order) {
            if ($order->wasChanged('status')) {
                $order->notifyStatus($order->status ?? 'pending');
            }
        });
    }

    protected $fillable = [
        'order_number',
        'user_id',
        'affiliate_id',
        'coupon_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'total_amount',
        'currency',
		'payment_currency',
		'payment_amount',
		'exchange_rate',
        'status',
        'payment_method',
        'payment_id',
        'payment_reference',
		'payment_provider',
		'payer_phone',
		'payer_country',
		'customer_ip',
		'user_agent',
        'billing_address',
        'billing_info',
        'order_items',
		'provider_fee',
		'provider_fee_currency',
		'net_total',
        'notes',
        'confirmed_at',
        'paid_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'total_amount' => 'decimal:2',
			'payment_amount' => 'decimal:2',
			'exchange_rate' => 'decimal:8',
			'provider_fee' => 'decimal:2',
			'net_total' => 'decimal:2',
            'billing_address' => 'array',
            'billing_info' => 'array',
            'order_items' => 'array',
            'confirmed_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function notifyStatus(string $status, ?string $message = null): void
    {
        if (!$this->relationLoaded('user')) {
            $this->load('user');
        }

        if ($this->user) {
            $this->user->notify(new OrderStatusUpdated($this, $status, $message));
        }
    }
}
