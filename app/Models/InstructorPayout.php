<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorPayout extends Model
{
    protected $fillable = [
        'instructor_id',
        'order_id',
        'course_id',
        'payout_id',
        'amount',
        'commission_percentage',
        'commission_amount',
        'currency',
        'status',
        'pawapay_status',
        'provider_transaction_id',
        'failure_reason',
        'pawapay_response',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'pawapay_response' => 'array',
        'processed_at' => 'datetime',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
