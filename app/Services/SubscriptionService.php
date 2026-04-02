<?php

namespace App\Services;

use App\Models\Course;
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

        $this->grantLinkedContentAccess($subscription);

        return $subscription;
    }

    public function grantLinkedContentAccess(UserSubscription $subscription): int
    {
        $subscription->loadMissing('plan');
        $plan = $subscription->plan;

        if (! $plan) {
            return 0;
        }

        $linkedContentIds = $plan->contents()->pluck('contents.id')->all();
        if (empty($linkedContentIds) && $plan->content_id) {
            $linkedContentIds = [(int) $plan->content_id];
        }

        $created = 0;
        foreach ($linkedContentIds as $contentId) {
            $enrollment = Enrollment::firstOrCreate(
                [
                    'user_id' => $subscription->user_id,
                    'content_id' => (int) $contentId,
                ],
                [
                    'status' => 'active',
                    'progress' => 0,
                ]
            );

            if ($enrollment->wasRecentlyCreated) {
                $created++;
            } elseif ($enrollment->status !== 'active') {
                $enrollment->update(['status' => 'active']);
            }
        }

        if ($plan->isCommunityPremiumPlan()) {
            $created += $this->grantAllNonDownloadableEnrollmentsForCommunityMember($subscription);
        }

        if ($plan->isPremiumCatalogPlan()) {
            $created += $this->grantAllPremiumCatalogEnrollments($subscription);
        }

        return $created;
    }

    /**
     * Inscrit l’utilisateur à tous les contenus publiés non téléchargeables (abonnement Membre Herime).
     */
    public function grantAllNonDownloadableEnrollmentsForCommunityMember(UserSubscription $subscription): int
    {
        $subscription->loadMissing('plan');
        if (! $subscription->plan?->isCommunityPremiumPlan()) {
            return 0;
        }

        $contentIds = Course::query()
            ->where('is_published', true)
            ->where('is_downloadable', false)
            ->pluck('id');

        $created = 0;
        foreach ($contentIds as $contentId) {
            $enrollment = Enrollment::firstOrCreate(
                [
                    'user_id' => $subscription->user_id,
                    'content_id' => (int) $contentId,
                ],
                [
                    'status' => 'active',
                    'progress' => 0,
                ]
            );

            if ($enrollment->wasRecentlyCreated) {
                $created++;
            } elseif ($enrollment->status !== 'active') {
                $enrollment->update(['status' => 'active']);
            }
        }

        return $created;
    }

    /**
     * Inscrit l’utilisateur à toutes les formations publiées, non téléchargeables, avec au moins une leçon (plan Premium).
     */
    public function grantAllPremiumCatalogEnrollments(UserSubscription $subscription): int
    {
        $subscription->loadMissing('plan');
        if (! $subscription->plan?->isPremiumCatalogPlan()) {
            return 0;
        }

        $contentIds = Course::query()
            ->where('is_published', true)
            ->where('is_downloadable', false)
            ->whereHas('lessons')
            ->pluck('id');

        $created = 0;
        foreach ($contentIds as $contentId) {
            $enrollment = Enrollment::firstOrCreate(
                [
                    'user_id' => $subscription->user_id,
                    'content_id' => (int) $contentId,
                ],
                [
                    'status' => 'active',
                    'progress' => 0,
                ]
            );

            if ($enrollment->wasRecentlyCreated) {
                $created++;
            } elseif ($enrollment->status !== 'active') {
                $enrollment->update(['status' => 'active']);
            }
        }

        return $created;
    }

    /**
     * Lorsqu’une formation éligible est publiée ou reçoit une leçon, ouvrir l’accès aux abonnés « Premium » actifs.
     */
    public function grantPremiumSubscribersAccessToCourse(Course $course): int
    {
        if (! $course->is_published || $course->is_downloadable || ! $course->lessons()->exists()) {
            return 0;
        }

        $planIds = SubscriptionPlan::query()
            ->where('plan_type', 'premium')
            ->where('is_active', true)
            ->pluck('id');

        if ($planIds->isEmpty()) {
            return 0;
        }

        $subscriptions = UserSubscription::query()
            ->whereIn('subscription_plan_id', $planIds->all())
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>', now());
            })
            ->get();

        $touched = 0;
        foreach ($subscriptions as $sub) {
            $enrollment = Enrollment::firstOrCreate(
                [
                    'user_id' => $sub->user_id,
                    'content_id' => (int) $course->id,
                ],
                [
                    'status' => 'active',
                    'progress' => 0,
                ]
            );

            if ($enrollment->wasRecentlyCreated) {
                $touched++;
            } elseif ($enrollment->status !== 'active') {
                $enrollment->update(['status' => 'active']);
                $touched++;
            }
        }

        return $touched;
    }

    /**
     * Lorsqu’un contenu non téléchargeable est publié (ou modifié en ce sens), ouvrir l’accès aux membres communauté actifs.
     */
    public function grantCommunityMembersAccessToCourse(Course $course): int
    {
        if (! $course->is_published || $course->is_downloadable) {
            return 0;
        }

        $planIds = SubscriptionPlan::query()
            ->get()
            ->filter(fn (SubscriptionPlan $p) => $p->isCommunityPremiumPlan())
            ->pluck('id');

        if ($planIds->isEmpty()) {
            return 0;
        }

        $subscriptions = UserSubscription::query()
            ->whereIn('subscription_plan_id', $planIds->all())
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>', now());
            })
            ->get();

        $touched = 0;
        foreach ($subscriptions as $sub) {
            $enrollment = Enrollment::firstOrCreate(
                [
                    'user_id' => $sub->user_id,
                    'content_id' => (int) $course->id,
                ],
                [
                    'status' => 'active',
                    'progress' => 0,
                ]
            );

            if ($enrollment->wasRecentlyCreated) {
                $touched++;
            } elseif ($enrollment->status !== 'active') {
                $enrollment->update(['status' => 'active']);
                $touched++;
            }
        }

        return $touched;
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
                    if (! $plan || ! $plan->is_active || ! $plan->usesRecurringBilling()) {
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
        if (! $plan->usesRecurringBilling()) {
            return $from;
        }

        return match ($plan->billing_period) {
            'yearly' => $from->copy()->addYear(),
            'semiannual' => $from->copy()->addMonths(6),
            'quarterly' => $from->copy()->addMonths(3),
            default => $from->copy()->addMonth(),
        };
    }
}

