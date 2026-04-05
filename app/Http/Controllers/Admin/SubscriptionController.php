<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentPackage;
use App\Models\SubscriptionInvoice;
use App\Models\UserSubscription;
use App\Notifications\SubscriptionInvoiceCancelled;
use App\Services\SubscriptionCheckoutOrderService;
use App\Services\SubscriptionNotificationDispatcher;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $subscriptions = UserSubscription::query()
            ->with(['user', 'plan', 'plan.contents'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $includedPackageIds = $subscriptions->getCollection()
            ->pluck('plan.metadata')
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

        $invoices = SubscriptionInvoice::query()
            ->with(['subscription.plan', 'user'])
            ->latest()
            ->limit(30)
            ->get();

        return view('admin.subscriptions.index', compact('subscriptions', 'invoices', 'includedPackagesById'));
    }

    public function markInvoicePaid(SubscriptionInvoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('info', 'Cette facture est déjà marquée comme payée.');
        }

        app(SubscriptionService::class)->applySubscriptionInvoicePaidFromAdmin($invoice, auth()->id());

        return back()->with('success', 'Facture marquée comme payée. L’abonnement et les accès ont été alignés comme après un paiement réussi.');
    }

    public function cancelInvoice(SubscriptionInvoice $invoice)
    {
        if ($invoice->status !== 'pending') {
            return back()->with('error', 'Seules les factures en attente de paiement peuvent être annulées.');
        }

        app(SubscriptionCheckoutOrderService::class)->cancelPendingOrdersForInvoicesClosed(
            [$invoice->id],
            'Facture annulée par l’administration',
        );

        $subscription = $invoice->subscription;
        $wasPendingPayment = $subscription && $subscription->status === 'pending_payment';

        $invoice->update([
            'status' => 'cancelled',
            'paid_at' => null,
            'metadata' => array_merge($invoice->metadata ?? [], [
                'cancelled_by_admin_at' => now()->toIso8601String(),
                'cancelled_by_admin_user_id' => auth()->id(),
            ]),
        ]);

        if ($wasPendingPayment && $subscription) {
            $subscription->update([
                'status' => 'expired',
                'ended_at' => now(),
                'auto_renew' => false,
            ]);
            app(SubscriptionService::class)->revokeEntitlementsGrantedBySubscription($subscription->fresh());
        }

        $invoice->refresh();
        if ($invoice->user) {
            SubscriptionNotificationDispatcher::notifyUser(
                $invoice->user,
                new SubscriptionInvoiceCancelled($invoice),
                'subscription_invoice_cancelled_by_admin',
                ['invoice_id' => $invoice->id],
            );
        }

        return back()->with('success', 'Facture annulée. Les commandes de paiement en attente associées ont été fermées.');
    }

    /**
     * Résiliation depuis l’administration (même effet métier qu’une annulation client : fin de période, factures en attente fermées).
     */
    public function cancelUserSubscription(UserSubscription $userSubscription, SubscriptionService $subscriptionService)
    {
        if (! in_array($userSubscription->status, ['active', 'trialing', 'past_due', 'pending_payment'], true)) {
            return back()->with('error', 'Cet abonnement ne peut pas être annulé avec ce statut.');
        }

        $subscriptionService->cancelSubscriptionWithFullNotifications($userSubscription, true);

        return back()->with('success', 'L’abonnement de ce client a été annulé. Les factures en attente ont été fermées le cas échéant.');
    }
}
