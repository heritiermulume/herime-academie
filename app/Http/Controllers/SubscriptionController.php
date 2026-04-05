<?php

namespace App\Http\Controllers;

use App\Models\ContentPackage;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Notifications\AdminSubscriptionActivated;
use App\Notifications\AdminSubscriptionAutoRenewResumed;
use App\Notifications\SubscriptionActivated;
use App\Notifications\SubscriptionAutoRenewResumed;
use App\Services\SubscriptionCheckoutOrderService;
use App\Services\SubscriptionNotificationDispatcher;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function index()
    {
        $preferredCurrency = strtoupper((string) (is_array(\App\Models\Setting::getBaseCurrency())
            ? (\App\Models\Setting::getBaseCurrency()['code'] ?? 'USD')
            : (\App\Models\Setting::getBaseCurrency() ?: 'USD')));

        $plans = SubscriptionPlan::activeMemberCommunityPlans();
        $includedPackageIds = $plans
            ->pluck('metadata')
            ->filter(fn ($metadata) => is_array($metadata))
            ->flatMap(fn ($metadata) => collect(data_get($metadata, 'included_package_ids', [])))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $includedPackagesById = ContentPackage::query()
            ->whereIn('id', $includedPackageIds)
            ->get()
            ->keyBy('id');
        $subscriptions = auth()->user()->subscriptions()->with(['plan', 'invoices'])->latest()->get();
        $subscriptions = $subscriptions
            ->sortByDesc(function (UserSubscription $s) {
                $p = $s->plan;

                return ($p && $p->isCommunityPremiumPlan()) ? 1 : 0;
            })
            ->values();
        $invoices = SubscriptionInvoice::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(20)
            ->get();

        return view('customers.subscriptions.index', compact('plans', 'subscriptions', 'invoices', 'preferredCurrency', 'includedPackagesById'));
    }

    public function subscribe(Request $request, SubscriptionPlan $plan, SubscriptionService $service)
    {
        if (! SubscriptionPlan::allowsAdminMemberBundleManagement($plan)) {
            return redirect()
                ->route('community.premium')
                ->with('error', 'Cette offre n\'est plus disponible. Choisissez une formule Membre Herime.');
        }

        if (! $plan->is_active) {
            return back()->with('error', 'Ce plan n\'est plus disponible.');
        }

        $existingCurrent = auth()->user()->subscriptions()
            ->where('subscription_plan_id', $plan->id)
            ->whereIn('status', ['trialing', 'active', 'past_due', 'cancelled'])
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>', now());
            })
            ->latest()
            ->first();

        $method = collect(config('payments.methods', []))
            ->filter(fn ($m) => ($m['enabled'] ?? false) === true)
            ->keys()
            ->first();
        $subscription = $service->subscribe(auth()->user(), $plan, $method);
        $invoice = $subscription->invoices()
            ->where('status', 'pending')
            ->latest()
            ->first();

        $deferActivationNotifications = $subscription->status === 'pending_payment';

        if (! $deferActivationNotifications) {
            $isRenewal = (bool) $existingCurrent;
            SubscriptionNotificationDispatcher::notifyUser(
                auth()->user(),
                new SubscriptionActivated($subscription, $invoice, $isRenewal, false),
                'subscription_subscribe_flow_customer',
                ['subscription_id' => $subscription->id, 'invoice_id' => $invoice?->id, 'is_renewal' => $isRenewal],
            );
            SubscriptionNotificationDispatcher::notifyAdmins(
                new AdminSubscriptionActivated($subscription, $invoice, $isRenewal, false),
                'subscription_subscribe_flow_admin',
                ['subscription_id' => $subscription->id, 'invoice_id' => $invoice?->id, 'is_renewal' => $isRenewal],
            );
        }

        $redirectRoute = $request->input('redirect_after_subscribe');
        $allowedRedirects = ['customer.subscriptions', 'community.premium'];

        if (
            $invoice
            && $invoice->status === 'pending'
            && (float) $invoice->amount > 0
        ) {
            $returnTo = $request->input('redirect_after_subscribe') === 'community.premium' ? 'community' : null;

            return $this->redirectToMonerooCheckout($request, $invoice, $returnTo);
        }

        if ($existingCurrent) {
            if ($redirectRoute && in_array($redirectRoute, $allowedRedirects, true)) {
                return redirect()->route($redirectRoute)
                    ->with('success', 'Réabonnement programmé pour la prochaine période.');
            }

            return back()->with('success', 'Réabonnement programmé pour la prochaine période.');
        }

        if ($redirectRoute && in_array($redirectRoute, $allowedRedirects, true)) {
            return redirect()->route($redirectRoute)
                ->with('success', 'Abonnement activé avec succès.');
        }

        return back()->with('success', 'Abonnement activé avec succès.');
    }

    public function cancel(UserSubscription $subscription, SubscriptionService $subscriptionService)
    {
        abort_unless($subscription->user_id === auth()->id(), 403);

        $subscriptionService->cancelSubscriptionWithFullNotifications($subscription, false);

        return back()->with('success', 'Votre abonnement a été annulé.');
    }

    public function resume(UserSubscription $subscription, SubscriptionService $subscriptionService)
    {
        abort_unless($subscription->user_id === auth()->id(), 403);

        if ($subscription->status !== 'cancelled') {
            return back()->with('error', 'Seuls les abonnements annulés peuvent être réactivés.');
        }

        $subscription->update([
            'status' => 'active',
            'auto_renew' => true,
            'cancelled_at' => null,
            'ended_at' => null,
        ]);

        $subscription->refresh();
        if ($subscription->invoices()->where('status', 'pending')->where('amount', '>', 0)->exists()) {
            $subscription->update(['status' => 'past_due']);
            $subscriptionService->revokeEntitlementsGrantedBySubscription($subscription->fresh());
        }

        $subscription->loadMissing(['user', 'plan']);
        if ($subscription->user) {
            SubscriptionNotificationDispatcher::notifyUser(
                $subscription->user,
                new SubscriptionAutoRenewResumed($subscription->fresh(['plan'])),
                'subscription_auto_renew_resumed',
                ['subscription_id' => $subscription->id],
            );
        }
        SubscriptionNotificationDispatcher::notifyAdmins(
            new AdminSubscriptionAutoRenewResumed($subscription->fresh(['plan', 'user'])),
            'subscription_auto_renew_resumed_admin',
            ['subscription_id' => $subscription->id],
        );

        return back()->with('success', 'Renouvellement automatique réactivé.');
    }

    public function payInvoice(Request $request, SubscriptionInvoice $invoice)
    {
        abort_unless($invoice->user_id === auth()->id(), 403);

        if ($invoice->status === 'paid') {
            return back()->with('info', 'Cette facture est deja payee.');
        }

        $returnTo = $request->input('return_to') === 'community' ? 'community' : null;

        return $this->redirectToMonerooCheckout($request, $invoice, $returnTo);
    }

    /**
     * Initialise un paiement Moneroo pour une facture d’abonnement et redirige vers la page de paiement.
     * Crée une commande Order (pending) et un Payment (pending ou failed), comme le flux panier.
     */
    private function redirectToMonerooCheckout(Request $request, SubscriptionInvoice $invoice, ?string $returnTo = null): RedirectResponse
    {
        $checkoutOrder = app(SubscriptionCheckoutOrderService::class);
        $user = $request->user();

        $checkoutBundle = DB::transaction(function () use ($checkoutOrder, $invoice, $user, $request) {
            $inv = SubscriptionInvoice::query()->whereKey($invoice->id)->lockForUpdate()->first();
            if (! $inv || $inv->status !== 'pending') {
                return null;
            }

            $checkoutOrder->cancelPriorPendingSubscriptionCheckoutsForInvoice($inv, $user);
            $order = $checkoutOrder->createPendingOrderForSubscriptionInvoice($inv, $user, $request);
            $paymentRef = 'subinv_'.Str::upper(Str::random(12));
            $order->update(['payment_reference' => $paymentRef]);

            return [
                'invoice' => $inv,
                'order' => $order->fresh(),
                'paymentRef' => $paymentRef,
            ];
        });

        if ($checkoutBundle === null) {
            return back()->with('error', 'Cette facture n\'est plus en attente de paiement.');
        }

        $invoice = $checkoutBundle['invoice'];
        $order = $checkoutBundle['order'];
        $paymentRef = $checkoutBundle['paymentRef'];
        $monerooAmount = $this->toMonerooInitializeAmount((float) $invoice->amount);

        $returnParams = [
            'invoice' => $invoice->id,
            'payment_ref' => $paymentRef,
        ];
        if ($returnTo === 'community') {
            $returnParams['return_to'] = 'community';
        }

        $payload = [
            'amount' => $monerooAmount,
            'currency' => $invoice->currency ?: config('services.moneroo.default_currency', 'USD'),
            'description' => 'Paiement facture abonnement '.$invoice->invoice_number,
            'return_url' => route('subscriptions.invoices.return', $returnParams),
            'customer' => [
                'email' => auth()->user()->email,
                'first_name' => Str::of(auth()->user()->name)->before(' ')->toString() ?: auth()->user()->name,
                'last_name' => Str::of(auth()->user()->name)->after(' ')->toString() ?: auth()->user()->name,
            ],
            'metadata' => [
                'kind' => 'subscription_invoice',
                'invoice_id' => (string) $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'payment_ref' => $paymentRef,
                'user_id' => (string) auth()->id(),
                'order_id' => (string) $order->id,
                'order_number' => $order->order_number,
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.moneroo.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post(rtrim((string) config('services.moneroo.base_url', 'https://api.moneroo.io/v1'), '/').'/payments/initialize', $payload);

        if (! $response->successful()) {
            $checkoutOrder->recordFailedInitPayment(
                $order,
                $paymentRef,
                $payload,
                $response->json(),
                'Échec de l’initialisation Moneroo (HTTP '.$response->status().').',
            );

            return back()->with('error', 'Impossible d\'initialiser le paiement de la facture.');
        }

        $data = $response->json();
        $monerooId = data_get($data, 'data.id') ?? data_get($data, 'id');
        $paymentUrl = data_get($data, 'data.payment_url')
            ?? data_get($data, 'data.checkout_url')
            ?? data_get($data, 'payment_url')
            ?? data_get($data, 'checkout_url');

        $checkoutOrder->recordPendingPayment($order, $paymentRef, $payload, $data, (string) ($monerooId ?? ''));

        $invoice->update([
            'payment_method' => 'moneroo',
            'metadata' => array_merge($invoice->metadata ?? [], [
                'moneroo_init' => $data,
                'moneroo_id' => $monerooId,
                'payment_ref' => $paymentRef,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]),
        ]);

        if ($paymentUrl) {
            return redirect()->away($paymentUrl);
        }

        return back()->with('success', 'Paiement initialise. Le statut sera mis a jour automatiquement.');
    }

    public function invoiceReturn(Request $request, SubscriptionInvoice $invoice)
    {
        abort_unless($invoice->user_id === auth()->id(), 403);

        $afterPayRoute = $request->query('return_to') === 'community'
            ? 'community.premium'
            : 'customer.subscriptions';

        if ($invoice->status === 'paid') {
            return redirect()->route($afterPayRoute)->with('success', 'Paiement de facture confirme.');
        }

        return redirect()->route($afterPayRoute)->with('info', 'Paiement en cours de confirmation. Veuillez actualiser dans quelques instants.');
    }

    /**
     * Montant pour POST /v1/payments/initialize (Moneroo).
     * Même règle que MonerooController (paiement commande) : entier en unité principale de la devise (ex. 45 pour 45,00 USD),
     * pas en centimes (éviter 4500 affiché / traité comme milliers).
     */
    private function toMonerooInitializeAmount(float $amount): int
    {
        return (int) round($amount);
    }
}
