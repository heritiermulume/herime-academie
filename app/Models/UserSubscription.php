<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'status',
        'starts_at',
        'trial_ends_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'cancelled_at',
        'ended_at',
        'auto_renew',
        'payment_method',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'current_period_starts_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'ended_at' => 'datetime',
            'auto_renew' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function isCurrentlyActive(): bool
    {
        if (! in_array($this->status, ['trialing', 'active'], true)) {
            return false;
        }

        if ($this->ended_at && $this->ended_at->isPast()) {
            return false;
        }

        if ($this->relationLoaded('invoices')) {
            $blocking = $this->invoices->contains(
                fn ($inv) => $inv->status === 'pending' && (float) $inv->amount > 0
            );
        } else {
            $blocking = $this->invoices()
                ->where('status', 'pending')
                ->where('amount', '>', 0)
                ->exists();
        }

        return ! $blocking;
    }

    public function markCancelled(?Carbon $endAt = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'auto_renew' => false,
            'cancelled_at' => now(),
            'ended_at' => $endAt ?? $this->current_period_ends_at ?? now(),
        ]);
    }

    /**
     * Abonnement encore pertinent pour la page communauté (accès ou période non terminée).
     */
    public function isActiveMembershipPeriod(): bool
    {
        if (! in_array($this->status, ['trialing', 'active', 'pending_payment', 'past_due', 'cancelled'], true)) {
            return false;
        }

        if ($this->ended_at && $this->ended_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Données pour la carte /communaute/membre-premium (JSON + boutons).
     */
    public function asCommunityPremiumCardSubscription(): ?array
    {
        if (! $this->isActiveMembershipPeriod()) {
            return null;
        }

        $this->loadMissing('invoices');

        $pendingInvoice = $this->invoices->where('status', 'pending')->sortByDesc('id')->first();
        $payUrl = $pendingInvoice ? route('subscriptions.invoices.pay', $pendingInvoice) : null;
        $needsPay = $payUrl !== null
            && (float) ($pendingInvoice->amount ?? 0) > 0
            && in_array($this->status, ['pending_payment', 'past_due', 'active', 'trialing'], true);

        return [
            'id' => $this->id,
            'status' => $this->status,
            'cancel_url' => route('subscriptions.cancel', $this),
            'resume_url' => route('subscriptions.resume', $this),
            'pay_url' => $payUrl,
            'show_cancel' => in_array($this->status, ['active', 'trialing', 'past_due', 'pending_payment'], true),
            'show_resume' => $this->status === 'cancelled',
            'show_pay' => $needsPay,
        ];
    }
}
