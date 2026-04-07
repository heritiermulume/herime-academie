<?php

namespace App\Services;

use App\Models\ContentPackage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Setting;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\AdminSubscriptionAccessEnded;
use App\Notifications\AdminSubscriptionActivated;
use App\Notifications\AdminSubscriptionCancelled;
use App\Notifications\AdminSubscriptionInvoiceFailed;
use App\Notifications\AdminSubscriptionInvoiceIssued;
use App\Notifications\SubscriptionAccessEnded;
use App\Notifications\SubscriptionActivated;
use App\Notifications\SubscriptionCancelled;
use App\Notifications\SubscriptionInvoiceCancelled;
use App\Notifications\SubscriptionInvoiceFailed;
use App\Notifications\SubscriptionInvoiceIssued;
use App\Notifications\SubscriptionInvoicePaid;
use Carbon\Carbon;
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
                if (! $existingCurrent->payment_method && $paymentMethod) {
                    $existingCurrent->update(['payment_method' => $paymentMethod]);
                }

                return $existingCurrent->fresh();
            }

            $currency = strtoupper((string) ($user->moneroo_currency ?: (is_array(Setting::getBaseCurrency())
                ? (Setting::getBaseCurrency()['code'] ?? 'USD')
                : (Setting::getBaseCurrency() ?: 'USD'))));
            $amount = $plan->effectivePriceForCurrency($currency);

            if ($existingCurrent->status === 'cancelled' && $amount > 0) {
                $now = now();
                $periodStart = $now->copy();
                $periodEnd = $this->calculatePeriodEnd($plan, $periodStart);

                $pendingInvoiceIds = $existingCurrent->invoices()->where('status', 'pending')->pluck('id')->all();
                $existingCurrent->invoices()->where('status', 'pending')->update(['status' => 'cancelled']);
                if ($pendingInvoiceIds !== []) {
                    app(SubscriptionCheckoutOrderService::class)->cancelPendingOrdersForInvoicesClosed(
                        $pendingInvoiceIds,
                        'Facture annulée (nouveau réabonnement)',
                    );
                }

                $meta = is_array($existingCurrent->metadata) ? $existingCurrent->metadata : [];
                $meta['currency'] = $currency;
                $meta['amount'] = $amount;

                $existingCurrent->update([
                    'status' => 'pending_payment',
                    'cancelled_at' => null,
                    'trial_ends_at' => null,
                    'starts_at' => $now,
                    'current_period_starts_at' => $periodStart,
                    'current_period_ends_at' => $periodEnd,
                    'ended_at' => null,
                    'auto_renew' => $plan->auto_renew_default,
                    'payment_method' => $paymentMethod,
                    'metadata' => $meta,
                ]);

                $subscription = $existingCurrent->fresh();
                $this->createInvoice($subscription, $amount, $currency, $paymentMethod);

                return $subscription->fresh();
            }

            $payload = [
                'auto_renew' => true,
            ];

            $wasCancelled = $existingCurrent->status === 'cancelled';

            if ($wasCancelled) {
                $payload['status'] = 'active';
                $payload['cancelled_at'] = null;
                $payload['ended_at'] = null;
            }

            if (! $existingCurrent->payment_method && $paymentMethod) {
                $payload['payment_method'] = $paymentMethod;
            }

            $existingCurrent->update($payload);

            $subscription = $existingCurrent->fresh();

            if ($wasCancelled && $subscription->status === 'active') {
                $this->grantLinkedContentAccess($subscription);
            }

            return $subscription;
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

    /**
     * Après paiement réussi sur une période du bundle Membre : expire les autres lignes Membre du même utilisateur
     * (changement de cycle traité comme un nouvel abonnement unique actif).
     *
     * @return int Nombre d’abonnements passés en « expired »
     */
    public function expireOtherMemberBundleSubscriptions(UserSubscription $keep): int
    {
        $bundleIds = SubscriptionPlan::memberBundlePlanIds();
        if ($bundleIds === [] || ! in_array((int) $keep->subscription_plan_id, $bundleIds, true)) {
            return 0;
        }

        $others = UserSubscription::query()
            ->where('user_id', $keep->user_id)
            ->whereKeyNot($keep->getKey())
            ->whereIn('subscription_plan_id', $bundleIds)
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>', now());
            })
            ->whereIn('status', ['trialing', 'active', 'past_due', 'pending_payment', 'cancelled'])
            ->get();

        $closed = 0;

        foreach ($others as $sub) {
            $pendingInvoiceIds = $sub->invoices()->where('status', 'pending')->pluck('id')->all();
            $sub->invoices()->where('status', 'pending')->update(['status' => 'cancelled']);
            if ($pendingInvoiceIds !== []) {
                app(SubscriptionCheckoutOrderService::class)->cancelPendingOrdersForInvoicesClosed(
                    $pendingInvoiceIds,
                    'Facture annulée (changement de formule Membre)',
                );
            }

            $meta = is_array($sub->metadata) ? $sub->metadata : [];
            $meta['member_bundle_replaced_by_subscription_id'] = $keep->id;
            $meta['member_bundle_replaced_at'] = now()->toIso8601String();

            $sub->update([
                'status' => 'expired',
                'auto_renew' => false,
                'ended_at' => now(),
                'cancelled_at' => $sub->cancelled_at ?? now(),
                'metadata' => $meta,
            ]);
            $this->revokeEntitlementsGrantedBySubscription($sub);
            $closed++;
        }

        return $closed;
    }

    /**
     * Recalcule current_period_starts_at / current_period_ends_at après paiement d’une facture,
     * sur le même principe que advanceSubscriptionsPastPeriodEnd() (renouvellement = enchaîner depuis la fin de période).
     *
     * @param  \Carbon\Carbon|null  $previousPeriodEnd  Fin de période avant activation (capturée avant update status).
     */
    public function syncSubscriptionPeriodAfterInvoicePaid(
        UserSubscription $subscription,
        bool $wasPendingPayment,
        ?Carbon $previousPeriodEnd = null,
    ): void {
        $subscription->loadMissing('plan');
        $plan = $subscription->plan;
        if (! $plan || ! $plan->usesRecurringBilling()) {
            return;
        }

        if ($wasPendingPayment) {
            if ($subscription->current_period_starts_at === null || $subscription->current_period_ends_at === null) {
                $start = now();
                $subscription->update([
                    'current_period_starts_at' => $start,
                    'current_period_ends_at' => $this->calculatePeriodEnd($plan, $start),
                ]);
            }

            return;
        }

        $start = $previousPeriodEnd ?? $subscription->current_period_ends_at?->copy() ?? now();
        $subscription->update([
            'current_period_starts_at' => $start,
            'current_period_ends_at' => $this->calculatePeriodEnd($plan, $start),
        ]);
    }

    /**
     * Après paiement réussi d’une facture : activation (1er paiement) ou confirmation de renouvellement payé (sinon).
     * Chaque envoi est isolé : un échec ne bloque pas les autres.
     */
    public function dispatchSubscriptionPaidLifecycleNotifications(
        SubscriptionInvoice $invoice,
        UserSubscription $subscription,
        bool $wasPendingPayment,
    ): void {
        $invoice->loadMissing('user');
        if (! $invoice->user) {
            return;
        }

        $subscription = $subscription->fresh(['plan']);
        $invoice->refresh();

        if ($wasPendingPayment) {
            SubscriptionNotificationDispatcher::notifyUser(
                $invoice->user,
                new SubscriptionActivated($subscription, $invoice, false, false),
                'subscription_activated_first_payment',
                ['subscription_id' => $subscription->id, 'invoice_id' => $invoice->id],
            );
            SubscriptionNotificationDispatcher::notifyAdmins(
                new AdminSubscriptionActivated($subscription, $invoice, false, false),
                'subscription_activated_first_payment_admin',
                ['subscription_id' => $subscription->id, 'invoice_id' => $invoice->id],
            );
        } else {
            SubscriptionNotificationDispatcher::notifyUser(
                $invoice->user,
                new SubscriptionActivated($subscription, $invoice, false, true),
                'subscription_paid_cycle_renewal',
                ['subscription_id' => $subscription->id, 'invoice_id' => $invoice->id],
            );
            SubscriptionNotificationDispatcher::notifyAdmins(
                new AdminSubscriptionActivated($subscription, $invoice, false, true),
                'subscription_paid_cycle_renewal_admin',
                ['subscription_id' => $subscription->id, 'invoice_id' => $invoice->id],
            );
        }
    }

    public function notifySubscriptionInvoicePaidUser(SubscriptionInvoice $invoice, string $logLabel = 'subscription_invoice_paid'): void
    {
        SubscriptionNotificationDispatcher::notifyUser(
            $invoice->user,
            new SubscriptionInvoicePaid($invoice),
            $logLabel,
            ['invoice_id' => $invoice->id],
        );
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

            $this->applySubscriptionGrantToEnrollment($enrollment, $subscription);
        }

        $created += $this->grantEnrollmentsForIncludedPackages($subscription, $plan);

        if ($plan->isCommunityPremiumPlan()) {
            $created += $this->grantAllNonDownloadableEnrollmentsForCommunityMember($subscription);
        }

        return $created;
    }

    /**
     * Supprime les inscriptions créées uniquement via cet abonnement (sans commande d’achat liée).
     * L’achat individuel conserve l’inscription car access_granted_by_subscription_id est effacé à la commande payée.
     */
    public function revokeEntitlementsGrantedBySubscription(UserSubscription $subscription): int
    {
        return Enrollment::query()
            ->where('access_granted_by_subscription_id', $subscription->id)
            ->delete();
    }

    private function applySubscriptionGrantToEnrollment(Enrollment $enrollment, UserSubscription $subscription): void
    {
        $enrollment->refresh();
        if ($enrollment->order_id !== null) {
            return;
        }
        if ((int) ($enrollment->access_granted_by_subscription_id ?? 0) === (int) $subscription->id) {
            return;
        }
        $enrollment->update(['access_granted_by_subscription_id' => $subscription->id]);
    }

    /**
     * Abonnements à réaligner quand le plan ou les packs inclus changent.
     * Hors expiré, attente premier paiement et impayé (past_due) : pas de nouveaux droits tant que la facture n’est pas réglée.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, UserSubscription>
     */
    public function subscriptionsEligibleForPlanEntitlementSync(SubscriptionPlan $plan)
    {
        return UserSubscription::query()
            ->where('subscription_plan_id', $plan->id)
            ->whereNotIn('status', ['expired', 'pending_payment', 'past_due'])
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

                $this->applySubscriptionGrantToEnrollment($enrollment, $subscription);
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

            $this->applySubscriptionGrantToEnrollment($enrollment, $subscription);
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

            $this->applySubscriptionGrantToEnrollment($enrollment, $sub);
        }

        return $touched;
    }

    public function createInvoice(
        UserSubscription $subscription,
        float $amount,
        ?string $currency = null,
        ?string $paymentMethod = null
    ): SubscriptionInvoice {
        $currency = $currency ?: (is_array(Setting::getBaseCurrency())
            ? (Setting::getBaseCurrency()['code'] ?? 'USD')
            : (Setting::getBaseCurrency() ?: 'USD'));

        $invoice = SubscriptionInvoice::create([
            'invoice_number' => 'SUB-'.strtoupper(Str::random(10)),
            'user_subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'amount' => $amount,
            'currency' => $currency,
            'payment_method' => $paymentMethod ?? $subscription->payment_method,
            'status' => 'pending',
            'due_at' => now()->addMinutes(max(1, (int) config('subscriptions.invoice_due_minutes', 30))),
        ]);

        if ($subscription->user && $subscription->status !== 'pending_payment') {
            SubscriptionNotificationDispatcher::notifyUser(
                $subscription->user,
                new SubscriptionInvoiceIssued($invoice),
                'subscription_invoice_issued_user',
                ['invoice_id' => $invoice->id],
            );
            SubscriptionNotificationDispatcher::notifyAdmins(
                new AdminSubscriptionInvoiceIssued($invoice),
                'subscription_invoice_issued_admin',
                ['invoice_id' => $invoice->id],
            );
        }

        return $invoice;
    }

    /**
     * Annule l’abonnement (fin de période, pas de renouvellement) puis factures / commandes en attente,
     * et notifie le client + les administrateurs.
     */
    public function cancelSubscriptionWithFullNotifications(UserSubscription $subscription, bool $initiatedByAdmin = false): void
    {
        $subscription->markCancelled();
        $this->cancelPendingInvoicesAfterCustomerCancellation($subscription);
        $subscription->loadMissing(['user', 'plan']);
        $fresh = $subscription->fresh(['plan', 'user']);

        if ($fresh->user) {
            SubscriptionNotificationDispatcher::notifyUser(
                $fresh->user,
                new SubscriptionCancelled($fresh),
                $initiatedByAdmin ? 'subscription_cancelled_by_admin' : 'subscription_cancelled_by_customer',
                ['subscription_id' => $fresh->id],
            );
        }
        SubscriptionNotificationDispatcher::notifyAdmins(
            new AdminSubscriptionCancelled($fresh, $initiatedByAdmin),
            $initiatedByAdmin ? 'subscription_cancelled_by_admin_staff' : 'subscription_cancelled_by_customer_admin',
            ['subscription_id' => $fresh->id],
        );
    }

    /**
     * Résiliation par le client : annule les factures encore en attente et ferme les commandes Moneroo liées,
     * pour éviter des relances de paiement sur un abonnement qu’il a annulé.
     */
    public function cancelPendingInvoicesAfterCustomerCancellation(UserSubscription $subscription): void
    {
        $subscription->loadMissing('user');

        $pending = SubscriptionInvoice::query()
            ->where('user_subscription_id', $subscription->id)
            ->where('status', 'pending')
            ->orderBy('id')
            ->get();

        if ($pending->isEmpty()) {
            return;
        }

        $ids = $pending->pluck('id')->all();

        app(SubscriptionCheckoutOrderService::class)->cancelPendingOrdersForInvoicesClosed(
            $ids,
            'Résiliation de l’abonnement — facture annulée',
        );

        foreach ($pending as $invoice) {
            $invoice->update([
                'status' => 'cancelled',
                'paid_at' => null,
                'metadata' => array_merge($invoice->metadata ?? [], [
                    'cancelled_due_to_customer_subscription_cancellation_at' => now()->toIso8601String(),
                ]),
            ]);
        }

        $user = $subscription->user;
        if (! $user) {
            return;
        }

        foreach ($ids as $invoiceId) {
            $inv = SubscriptionInvoice::query()->find($invoiceId);
            if ($inv) {
                SubscriptionNotificationDispatcher::notifyUser(
                    $user,
                    new SubscriptionInvoiceCancelled($inv),
                    'subscription_invoice_cancelled_customer_resiliation',
                    ['invoice_id' => $inv->id],
                );
            }
        }
    }

    /**
     * Marquage payé par l’admin : même effet métier qu’un webhook Moneroo « success » (abonnement actif,
     * accès, bundle Membre, notifications, commande Order/Payment si présente).
     */
    public function applySubscriptionInvoicePaidFromAdmin(SubscriptionInvoice $invoice, ?int $adminUserId = null): void
    {
        $invoice->loadMissing('subscription.plan', 'user');

        $previousInvoiceStatus = $invoice->status;
        if ($previousInvoiceStatus === 'paid') {
            return;
        }

        $subscription = $invoice->subscription;
        $wasPendingPayment = $subscription && $subscription->status === 'pending_payment';

        $meta = array_merge($invoice->metadata ?? [], [
            'marked_paid_by_admin_at' => now()->toIso8601String(),
        ]);
        if ($adminUserId !== null) {
            $meta['marked_paid_by_admin_user_id'] = $adminUserId;
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'metadata' => $meta,
        ]);

        if ($subscription) {
            $previousPeriodEnd = $subscription->current_period_ends_at?->copy();
            $subscription->update(['status' => 'active']);
            $subscription = $subscription->fresh();
            $this->syncSubscriptionPeriodAfterInvoicePaid($subscription, $wasPendingPayment, $previousPeriodEnd);
            $subscription = $subscription->fresh();
            $this->grantLinkedContentAccess($subscription);
            $this->expireOtherMemberBundleSubscriptions($subscription);

            if ($invoice->user) {
                $invoice->refresh();
                $this->dispatchSubscriptionPaidLifecycleNotifications($invoice, $subscription, $wasPendingPayment);
            }
        }

        $invoice->refresh();
        app(SubscriptionCheckoutOrderService::class)->finalizeLinkedMonerooOrderWhenInvoiceMarkedPaidByAdmin($invoice);

        if ($invoice->user && $previousInvoiceStatus !== 'paid') {
            $this->notifySubscriptionInvoicePaidUser($invoice, 'subscription_invoice_paid_admin_mark');
        }
    }

    /**
     * Quand le webhook Moneroo n’a pas été reçu mais que la commande d’abonnement est confirmée via
     * « Vérifier le paiement », aligner facture + abonnement comme après webhook.
     */
    public function applyPaidStateFromVerifiedSubscriptionOrder(Order $order): void
    {
        $invoiceId = data_get($order->billing_info, 'subscription_invoice_id');
        if (! $invoiceId) {
            return;
        }

        $invoice = SubscriptionInvoice::query()->find($invoiceId);
        if (! $invoice || (int) $invoice->user_id !== (int) $order->user_id) {
            return;
        }

        if ($invoice->status === 'paid') {
            return;
        }

        $subscription = $invoice->subscription;
        $wasPendingPayment = $subscription && $subscription->status === 'pending_payment';

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'metadata' => array_merge($invoice->metadata ?? [], [
                'confirmed_via' => 'order_manual_verify',
                'confirmed_order_id' => $order->id,
                'confirmed_at' => now()->toIso8601String(),
            ]),
        ]);

        if ($subscription) {
            $previousPeriodEnd = $subscription->current_period_ends_at?->copy();
            $subscription->update(['status' => 'active']);
            $subscription = $subscription->fresh();
            $this->syncSubscriptionPeriodAfterInvoicePaid($subscription, $wasPendingPayment, $previousPeriodEnd);
            $subscription = $subscription->fresh();
            $this->grantLinkedContentAccess($subscription);
            $this->expireOtherMemberBundleSubscriptions($subscription);

            if ($invoice->user) {
                $this->dispatchSubscriptionPaidLifecycleNotifications($invoice, $subscription, $wasPendingPayment);
            }
        }

        if ($invoice->user) {
            $this->notifySubscriptionInvoicePaidUser($invoice, 'subscription_invoice_paid_order_verify');
        }
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
                $invoice->loadMissing('subscription');
                $firstPaymentDeadlineExpired = $invoice->subscription
                    && $invoice->subscription->status === 'pending_payment';

                app(SubscriptionCheckoutOrderService::class)->cancelPendingOrdersForInvoicesClosed(
                    [$invoice->id],
                    'Facture en retard : commande de paiement annulée',
                );
                $invoice->update([
                    'status' => 'failed',
                    'metadata' => array_merge($invoice->metadata ?? [], [
                        'failed_reason' => 'overdue',
                        'failed_at' => now()->toIso8601String(),
                    ]),
                ]);
                if ($invoice->subscription && in_array($invoice->subscription->status, ['active', 'trialing'], true)) {
                    $invoice->subscription->update(['status' => 'past_due']);
                    $this->revokeEntitlementsGrantedBySubscription($invoice->subscription->fresh());
                } elseif ($invoice->subscription && $invoice->subscription->status === 'pending_payment') {
                    $invoice->subscription->update([
                        'status' => 'expired',
                        'ended_at' => now(),
                        'auto_renew' => false,
                    ]);
                    $this->revokeEntitlementsGrantedBySubscription($invoice->subscription->fresh());
                }

                $invoice->refresh()->load(['subscription.plan', 'user']);

                if ($invoice->user) {
                    SubscriptionNotificationDispatcher::notifyUser(
                        $invoice->user,
                        new SubscriptionInvoiceFailed($invoice, $firstPaymentDeadlineExpired),
                        'subscription_invoice_failed_overdue',
                        ['invoice_id' => $invoice->id, 'first_payment_expired' => $firstPaymentDeadlineExpired],
                    );
                }
                SubscriptionNotificationDispatcher::notifyAdmins(
                    new AdminSubscriptionInvoiceFailed($invoice),
                    'subscription_invoice_failed_overdue_admin',
                    ['invoice_id' => $invoice->id],
                );
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
                    $this->revokeEntitlementsGrantedBySubscription($subscription);

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
                $subscription->refresh();
                $hasUnpaidInvoice = $subscription->invoices()
                    ->where('status', 'pending')
                    ->where('amount', '>', 0)
                    ->exists();

                $subscription->update([
                    'status' => $hasUnpaidInvoice ? 'past_due' : 'active',
                    'current_period_starts_at' => $start,
                    'current_period_ends_at' => $this->calculatePeriodEnd($plan, $start),
                ]);

                if ($hasUnpaidInvoice) {
                    $this->revokeEntitlementsGrantedBySubscription($subscription->fresh());
                }

                $processed++;
            }
        });

        return $processed;
    }

    private function expireSubscriptionsPastEndDate(?int $userId): void
    {
        $query = UserSubscription::query()
            ->with(['user', 'plan'])
            ->whereIn('status', ['trialing', 'active', 'cancelled'])
            ->whereNotNull('ended_at')
            ->where('ended_at', '<=', now());

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $query->chunkById(100, function ($subscriptions) {
            foreach ($subscriptions as $subscription) {
                $subscription->update(['status' => 'expired']);
                $this->revokeEntitlementsGrantedBySubscription($subscription);
                $fresh = $subscription->fresh(['plan', 'user']);
                if ($fresh->user) {
                    SubscriptionNotificationDispatcher::notifyUser(
                        $fresh->user,
                        new SubscriptionAccessEnded($fresh),
                        'subscription_access_ended',
                        ['subscription_id' => $fresh->id],
                    );
                }
                SubscriptionNotificationDispatcher::notifyAdmins(
                    new AdminSubscriptionAccessEnded($fresh),
                    'subscription_access_ended_admin',
                    ['subscription_id' => $fresh->id],
                );
            }
        });
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
