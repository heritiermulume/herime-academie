<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionInvoice;
use App\Models\UserSubscription;
use App\Notifications\SubscriptionInvoicePaid;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $subscriptions = UserSubscription::query()
            ->with(['user', 'plan'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $invoices = SubscriptionInvoice::query()
            ->with(['subscription.plan', 'user'])
            ->latest()
            ->limit(30)
            ->get();

        return view('admin.subscriptions.index', compact('subscriptions', 'invoices'));
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

