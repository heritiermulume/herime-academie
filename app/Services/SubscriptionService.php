<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Setting;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\SubscriptionInvoiceFailed;
use App\Notifications\SubscriptionInvoiceIssued;
use Illuminate\Support\Str;

class SubscriptionService
{
    public function subscribe(User $user, SubscriptionPlan $plan, ?string $paymentMethod = null): UserSubscription
    {
        $existingCurrent = UserSubscription::query()
            ->where('user_id', $user->id)
            ->where('subscription_plan_id', $plan->id)
            ->whereIn('status', ['trialing', 'active', 'past_due', 'cancelled'])
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>', now());
            })
            ->latest()
            ->first();

        if ($existingCurrent) {
            $payload = [
                'auto_renew' => true,
            ];

            if ($existingCurrent->status === 'cancelled') {
                $payload['status'] = 'active';
                $payload['cancelled_at'] = null;
                $payload['ended_at'] = null;
            }

            if (!$existingCurrent->payment_method && $paymentMethod) {
                $payload['payment_method'] = $paymentMethod;
            }

            $existingCurrent->update($payload);

            return $existingCurrent->fresh();
        }

        $currency = strtoupper((string) ($user->moneroo_currency ?: (is_array(Setting::getBaseCurrency())
            ? (Setting::getBaseCurrency()['code'] ?? 'USD')
            : (Setting::getBaseCurrency() ?: 'USD'))));
        $amount = $plan->effectivePriceForCurrency($currency);

        $now = now();
        $trialEndsAt = $plan->trial_days > 0 ? $now->copy()->addDays($plan->trial_days) : null;
        // During trial, current period ends at trial end; billing starts after trial.
        $periodStart = $now->copy();
        $periodEnd = $trialEndsAt ? $trialEndsAt->copy() : $this->calculatePeriodEnd($plan, $periodStart);
        $status = $trialEndsAt ? 'trialing' : 'active';

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => $status,
            'starts_at' => $now,
            'trial_ends_at' => $trialEndsAt,
            'current_period_starts_at' => $periodStart,
            'current_period_ends_at' => $periodEnd,
            'auto_renew' => $plan->auto_renew_default,
            'payment_method' => $paymentMethod,
            'metadata' => [
                'currency' => $currency,
                'amount' => $amount,
            ],
        ]);

        if ($status === 'active' && $amount > 0) {
            $this->createInvoice($subscription, $amount, $currency, $paymentMethod);
        }

        $linkedContentIds = $plan->contents()->pluck('contents.id')->all();
        if (empty($linkedContentIds) && $plan->content_id) {
            $linkedContentIds = [(int) $plan->content_id];
        }

        foreach ($linkedContentIds as $contentId) {
            Enrollment::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'content_id' => $contentId,
                ],
                [
                    'status' => 'active',
                    'progress' => 0,
                ]
            );
        }

        return $subscription;
    }

    public function createInvoice(
        UserSubscription $subscription,
        float $amount,
        ?string $currency = null,
        ?string $paymentMethod = null
    ): SubscriptionInvoice
    {
        $currency = $currency ?: (is_array(Setting::getBaseCurrency())
            ? (Setting::getBaseCurrency()['code'] ?? 'USD')
            : (Setting::getBaseCurrency() ?: 'USD'));

        $invoice = SubscriptionInvoice::create([
            'invoice_number' => 'SUB-' . strtoupper(Str::random(10)),
            'user_subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'amount' => $amount,
            'currency' => $currency,
            'payment_method' => $paymentMethod ?? $subscription->payment_method,
            'status' => 'pending',
            'due_at' => now()->addDays(2),
        ]);

        if ($subscription->user) {
            $subscription->user->notify(new SubscriptionInvoiceIssued($invoice));
        }

        return $invoice;
    }

    public function processRenewals(): int
    {
        $processed = 0;

        // Mark overdue pending invoices and put subscriptions in past_due.
        SubscriptionInvoice::query()
            ->where('status', 'pending')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->chunkById(100, function ($invoices) {
                foreach ($invoices as $invoice) {
                    $invoice->update(['status' => 'failed']);
                    if ($invoice->subscription && in_array($invoice->subscription->status, ['active', 'trialing'], true)) {
                        $invoice->subscription->update(['status' => 'past_due']);
                    }
                    if ($invoice->user) {
                        $invoice->user->notify(new SubscriptionInvoiceFailed($invoice));
                    }
                }
            });

        UserSubscription::query()
            ->with('plan')
            ->whereIn('status', ['trialing', 'active', 'past_due'])
            ->where('auto_renew', true)
            ->whereNotNull('current_period_ends_at')
            ->where('current_period_ends_at', '<=', now())
            ->chunkById(100, function ($subscriptions) use (&$processed) {
                foreach ($subscriptions as $subscription) {
                    $plan = $subscription->plan;
                    if (!$plan || !$plan->is_active || $plan->plan_type !== 'recurring') {
                        $subscription->update([
                            'status' => 'expired',
                            'ended_at' => now(),
                            'auto_renew' => false,
                        ]);
                        continue;
                    }

                    $existingPendingInvoice = $subscription->invoices()
                        ->where('status', 'pending')
                        ->where('created_at', '>=', now()->subDays(7))
                        ->exists();

                    if (!$existingPendingInvoice) {
                        $currency = strtoupper((string) data_get($subscription->metadata, 'currency', 'USD'));
                        $amount = $plan->effectivePriceForCurrency($currency);
                        $this->createInvoice($subscription, $amount, $currency, $subscription->payment_method);
                    }

                    $start = $subscription->current_period_ends_at->copy();
                    $subscription->update([
                        'status' => $existingPendingInvoice ? 'past_due' : 'active',
                        'current_period_starts_at' => $start,
                        'current_period_ends_at' => $this->calculatePeriodEnd($plan, $start),
                    ]);

                    $processed++;
                }
            });

        UserSubscription::query()
            ->whereIn('status', ['trialing', 'active', 'cancelled'])
            ->whereNotNull('ended_at')
            ->where('ended_at', '<=', now())
            ->update(['status' => 'expired']);

        return $processed;
    }

    private function calculatePeriodEnd(SubscriptionPlan $plan, $from)
    {
        if ($plan->plan_type !== 'recurring') {
            return $from;
        }

        return $plan->billing_period === 'yearly'
            ? $from->copy()->addYear()
            : $from->copy()->addMonth();
    }
}

