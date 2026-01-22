<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderPayout extends Model
{
    use SoftDeletes;
    
    protected $table = 'provider_payouts';
    
    protected $fillable = [
        'provider_id',
        'order_id',
        'content_id',
        'payout_id',
        'amount',
        'commission_percentage',
        'commission_amount',
        'currency',
        'status',
        'pawapay_status', // Compatibilité avec anciennes données
        'moneroo_status',
        'provider_transaction_id',
        'failure_reason',
        'pawapay_response', // Compatibilité avec anciennes données
        'moneroo_response',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'pawapay_response' => 'array', // Compatibilité avec anciennes données
        'moneroo_response' => 'array',
        'processed_at' => 'datetime',
    ];

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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'content_id');
    }

    /**
     * Alias pour compatibilité avec le nouveau nom
     */
    public function content(): BelongsTo
    {
        return $this->course();
    }
}
