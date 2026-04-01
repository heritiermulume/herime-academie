<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentPackage;
use App\Models\SubscriptionInvoice;
use App\Models\UserSubscription;
use App\Notifications\SubscriptionInvoicePaid;
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
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $subscription = $invoice->subscription;
        if ($subscription && in_array($subscription->status, ['past_due', 'cancelled'], true)) {
            $subscription->update(['status' => 'active']);
        }
        if ($invoice->user) {
            $invoice->user->notify(new SubscriptionInvoicePaid($invoice));
        }

        return back()->with('success', 'Facture marquée comme payée.');
    }
}

