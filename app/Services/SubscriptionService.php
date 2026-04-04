<?php

namespace App\Services;

use App\Models\ContentPackage;
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
            ->whereIn('status', ['trialing', 'active', 'past_due', 'cancelled', 'pending_payment'])
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>', now());
            })
            ->latest()
            ->first();

        if ($existingCurrent) {
            if ($existingCurrent->status === 'pending_payment') {
                if (!$existingCurrent->payment_method && $paymentMethod) {
                    $existingCurrent->update(['payment_method' => $paymentMethod]);
                }

                return $existingCurrent->fresh();
            }

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
        $awaitsFirstPayment = ! $trialEndsAt && $amount > 0;
        $status = $trialEndsAt ? 'trialing' : ($awaitsFirstPayment ? 'pending_payment' : 'active');

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

        if (in_array($status, ['active', 'pending_payment'], true) && $amount > 0) {
            $this->createInvoice($subscription, $amount, $currency, $paymentMethod);
        }

        if ($status !== 'pending_payment') {
            $this->grantLinkedContentAccess($subscription);
        }

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

        $created += $this->grantEnrollmentsForIncludedPackages($subscription, $plan);

        if ($plan->isCommunityPremiumPlan()) {
            $created += $this->grantAllNonDownloadableEnrollmentsForCommunityMember($subscription);
        }

        return $created;
    }

    /**
     * Abonnements à réaligner quand le plan ou les packs inclus changent (hors expiré / attente paiement).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, UserSubscription>
     */
    public function subscriptionsEligibleForPlanEntitlementSync(SubscriptionPlan $plan)
    {
        return UserSubscription::query()
            ->where('subscription_plan_id', $plan->id)
            ->whereNotIn('status', ['expired', 'pending_payment'])
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>', now());
            })
            ->get();
    }

    /**
     * Ré-applique les droits du plan (contenus liés, packs metadata, règle communauté « tout le catalogue non téléchargeable ») pour tous les abonnés éligibles.
     */
    public function syncEntitlementsForAllPlanSubscribers(SubscriptionPlan $plan): int
    {
        $plan->refresh();
        $plan->loadMissing('contents');

        $synced = 0;
        foreach ($this->subscriptionsEligibleForPlanEntitlementSync($plan) as $subscription) {
            $this->grantLinkedContentAccess($subscription);
            $synced++;
        }

        return $synced;
    }

    /**
     * Plans dont les metadata référencent ce pack (included_package_ids).
     *
     * @return array<int>
     */
    public function planIdsReferencingContentPackage(int $contentPackageId): array
    {
        $ids = [];
        foreach (SubscriptionPlan::query()->whereNotNull('metadata')->cursor() as $pl) {
            if (in_array($contentPackageId, data_get($pl->metadata, 'included_package_ids', []), true)) {
                $ids[] = (int) $pl->id;
            }
        }

        return array_values(array_unique($ids));
    }

    private static array $deferredPlanIdsForEntitlementSync = [];

    private static array $deferredPackageIdsForEntitlementSync = [];

    private static bool $entitlementSyncTerminatingRegistered = false;

    public static function deferEntitlementSyncForPlan(int $planId): void
    {
        if ($planId <= 0) {
            return;
        }
        self::$deferredPlanIdsForEntitlementSync[$planId] = true;
        self::registerDeferredEntitlementSyncTerminating();
    }

    public static function deferEntitlementSyncForContentPackage(int $contentPackageId): void
    {
        if ($contentPackageId <= 0) {
            return;
        }
        self::$deferredPackageIdsForEntitlementSync[$contentPackageId] = true;
        self::registerDeferredEntitlementSyncTerminating();
    }

    /**
     * Exécute immédiatement les synchronisations différées (tests, console).
     */
    public static function flushDeferredEntitlementSyncs(): void
    {
        self::runDeferredEntitlementSyncs();
    }

    private static function registerDeferredEntitlementSyncTerminating(): void
    {
        if (self::$entitlementSyncTerminatingRegistered) {
            return;
        }
        self::$entitlementSyncTerminatingRegistered = true;
        app()->terminating(function () {
            self::runDeferredEntitlementSyncs();
        });
    }

    private static function runDeferredEntitlementSyncs(): void
    {
        self::$entitlementSyncTerminatingRegistered = false;

        $mergedPlanIds = [];
        foreach (array_keys(self::$deferredPlanIdsForEntitlementSync) as $id) {
            $mergedPlanIds[(int) $id] = true;
        }
        self::$deferredPlanIdsForEntitlementSync = [];

        $service = app(self::class);
        foreach (array_keys(self::$deferredPackageIdsForEntitlementSync) as $pkgId) {
            foreach ($service->planIdsReferencingContentPackage((int) $pkgId) as $planId) {
                $mergedPlanIds[$planId] = true;
            }
        }
        self::$deferredPackageIdsForEntitlementSync = [];

        foreach (array_keys($mergedPlanIds) as $planId) {
            $plan = SubscriptionPlan::query()->find($planId);
            if ($plan) {
                $service->syncEntitlementsForAllPlanSubscribers($plan);
            }
        }
    }

    /**
     * Inscrit l’utilisateur à tous les contenus des packs listés dans metadata.included_package_ids.
     */
    private function grantEnrollmentsForIncludedPackages(UserSubscription $subscription, SubscriptionPlan $plan): int
    {
        $packageIds = collect(data_get($plan->metadata, 'included_package_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($packageIds === []) {
            return 0;
        }

        $packages = ContentPackage::query()
            ->whereIn('id', $packageIds)
            ->with('contents')
            ->get();

        $created = 0;
        foreach ($packages as $package) {
            foreach ($package->contents as $course) {
                if (! $course->is_published) {
                    continue;
                }

                $enrollment = Enrollment::firstOrCreate(
                    [
                        'user_id' => $subscription->user_id,
                        'content_id' => (int) $course->id,
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

        if ($subscription->user && $subscription->status !== 'pending_payment') {
            $subscription->user->notify(new SubscriptionInvoiceIssued($invoice));
        }

        return $invoice;
    }

    /**
     * Traite tous les abonnements (cron / commande artisan).
     * Pour éviter de dépendre du scheduler, voir aussi processRenewalsForUser() + middleware web.
     */
    public function processRenewals(): int
    {
        $this->failOverduePendingInvoices(null);

        $processed = $this->advanceSubscriptionsPastPeriodEnd(
            UserSubscription::query()
                ->with('plan')
                ->whereIn('status', ['trialing', 'active', 'past_due'])
                ->where('auto_renew', true)
                ->whereNotNull('current_period_ends_at')
                ->where('current_period_ends_at', '<=', now())
        );

        $this->expireSubscriptionsPastEndDate(null);

        return $processed;
    }

    /**
     * Même logique que processRenewals(), limitée aux factures et abonnements de cet utilisateur.
     * Appelé à la visite (GET) pour faire avancer renouvellements / échéances sans cron.
     */
    public function processRenewalsForUser(int $userId): int
    {
        $this->failOverduePendingInvoices($userId);

        $processed = $this->advanceSubscriptionsPastPeriodEnd(
            UserSubscription::query()
                ->with('plan')
                ->where('user_id', $userId)
                ->whereIn('status', ['trialing', 'active', 'past_due'])
                ->where('auto_renew', true)
                ->whereNotNull('current_period_ends_at')
                ->where('current_period_ends_at', '<=', now())
        );

        $this->expireSubscriptionsPastEndDate($userId);

        return $processed;
    }

    private function failOverduePendingInvoices(?int $userId): void
    {
        $query = SubscriptionInvoice::query()
            ->where('status', 'pending')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $query->chunkById(100, function ($invoices) {
            foreach ($invoices as $invoice) {
                $invoice->update(['status' => 'failed']);
                if ($invoice->subscription && in_array($invoice->subscription->status, ['active', 'trialing'], true)) {
                    $invoice->subscription->update(['status' => 'past_due']);
                } elseif ($invoice->subscription && $invoice->subscription->status === 'pending_payment') {
                    $invoice->subscription->update([
                        'status' => 'expired',
                        'ended_at' => now(),
                        'auto_renew' => false,
                    ]);
                }
                if ($invoice->user) {
                    $invoice->user->notify(new SubscriptionInvoiceFailed($invoice));
                }
            }
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<UserSubscription>  $baseQuery
     */
    private function advanceSubscriptionsPastPeriodEnd($baseQuery): int
    {
        $processed = 0;

        (clone $baseQuery)->chunkById(100, function ($subscriptions) use (&$processed) {
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

                if (! $existingPendingInvoice) {
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

        return $processed;
    }

    private function expireSubscriptionsPastEndDate(?int $userId): void
    {
        $query = UserSubscription::query()
            ->whereIn('status', ['trialing', 'active', 'cancelled'])
            ->whereNotNull('ended_at')
            ->where('ended_at', '<=', now());

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $query->update(['status' => 'expired']);
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

