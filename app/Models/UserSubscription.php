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
     * Au moins une facture payée sur cet abonnement, ou sur un autre abonnement du même plan pour ce client.
     * Permet de distinguer une simple tentative de paiement (jamais payé) d’un vrai réabonnement.
     */
    public function hasPaidOrPriorPaidSamePlan(): bool
    {
        if ($this->relationLoaded('invoices')) {
            if ($this->invoices->contains(fn ($inv) => $inv->status === 'paid')) {
                return true;
            }
        } elseif ($this->invoices()->where('status', 'paid')->exists()) {
            return true;
        }

        return static::query()
            ->where('user_id', $this->user_id)
            ->where('subscription_plan_id', $this->subscription_plan_id)
            ->whereKeyNot($this->getKey())
            ->whereHas('invoices', fn ($q) => $q->where('status', 'paid'))
            ->exists();
    }

    /**
     * Libellé type « Réabonnement » sur le bouton principal (vs première souscription / finaliser paiement).
     */
    public function shouldUseResubscribePrimaryLabel(): bool
    {
        return match ($this->status) {
            'active', 'trialing', 'past_due' => true,
            'pending_payment', 'cancelled' => $this->hasPaidOrPriorPaidSamePlan(),
            default => true,
        };
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
        $periodEndAt = $this->ended_at ?? $this->current_period_ends_at;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'period_end_at' => $periodEndAt?->toIso8601String(),
            'period_end_label' => $periodEndAt?->format('d/m/Y'),
            'cancel_url' => route('subscriptions.cancel', $this),
            'resume_url' => route('subscriptions.resume', $this),
            'pay_url' => $payUrl,
            'show_cancel' => in_array($this->status, ['active', 'trialing', 'past_due', 'pending_payment'], true),
            'show_resume' => $this->status === 'cancelled',
            'show_pay' => $needsPay,
            'use_resubscribe_primary_label' => $this->shouldUseResubscribePrimaryLabel(),
        ];
    }
}
