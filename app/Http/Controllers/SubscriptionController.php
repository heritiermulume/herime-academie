<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\ContentPackage;
use App\Models\User;
use App\Notifications\AdminSubscriptionActivated;
use App\Notifications\SubscriptionActivated;
use App\Models\UserSubscription;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $preferredCurrency = strtoupper((string) (is_array(\App\Models\Setting::getBaseCurrency())
            ? (\App\Models\Setting::getBaseCurrency()['code'] ?? 'USD')
            : (\App\Models\Setting::getBaseCurrency() ?: 'USD')));

        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->with(['content', 'contents'])
            ->orderBy('price')
            ->get();
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
        $subscriptions = auth()->user()->subscriptions()->with('plan')->latest()->get();
        $invoices = SubscriptionInvoice::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(20)
            ->get();

        return view('customers.subscriptions.index', compact('plans', 'subscriptions', 'invoices', 'preferredCurrency', 'includedPackagesById'));
    }

    public function subscribe(Request $request, SubscriptionPlan $plan, SubscriptionService $service)
    {
        if (!$plan->is_active) {
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

        try {
            auth()->user()->notify(new SubscriptionActivated($subscription, $invoice, (bool) $existingCurrent));
        } catch (\Throwable $e) {
            Log::error('SubscriptionController: echec notification utilisateur abonnement', [
                'user_id' => auth()->id(),
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice?->id,
                'is_renewal' => (bool) $existingCurrent,
                'error' => $e->getMessage(),
            ]);
        }

        $admins = User::admins()
            ->whereNotNull('email')
            ->where('is_active', true)
            ->get();
        foreach ($admins as $admin) {
            try {
                Notification::sendNow($admin, new AdminSubscriptionActivated($subscription, $invoice, (bool) $existingCurrent));
            } catch (\Throwable $e) {
                Log::error('SubscriptionController: echec notification admin abonnement', [
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email,
                    'subscription_id' => $subscription->id,
                    'invoice_id' => $invoice?->id,
                    'is_renewal' => (bool) $existingCurrent,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($existingCurrent) {
            return back()->with('success', 'Réabonnement programmé pour la prochaine période.');
        }

        return back()->with('success', 'Abonnement activé avec succès.');
    }

    public function cancel(UserSubscription $subscription)
    {
        abort_unless($subscription->user_id === auth()->id(), 403);

        $subscription->markCancelled();

        return back()->with('success', 'Votre abonnement a été annulé.');
    }

    public function resume(UserSubscription $subscription)
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

        return back()->with('success', 'Renouvellement automatique réactivé.');
    }

    public function payInvoice(SubscriptionInvoice $invoice)
    {
        abort_unless($invoice->user_id === auth()->id(), 403);

        if ($invoice->status === 'paid') {
            return back()->with('info', 'Cette facture est deja payee.');
        }

        $paymentRef = 'subinv_' . Str::upper(Str::random(12));
        $minorAmount = $this->toMonerooMinor((float) $invoice->amount, (string) $invoice->currency);

        $payload = [
            'amount' => $minorAmount,
            'currency' => $invoice->currency ?: config('services.moneroo.default_currency', 'USD'),
            'description' => 'Paiement facture abonnement ' . $invoice->invoice_number,
            'return_url' => route('subscriptions.invoices.return', ['invoice' => $invoice->id, 'payment_ref' => $paymentRef]),
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
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.moneroo.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post(rtrim((string) config('services.moneroo.base_url', 'https://api.moneroo.io/v1'), '/') . '/payments/initialize', $payload);

        if (!$response->successful()) {
            return back()->with('error', 'Impossible d\'initialiser le paiement de la facture.');
        }

        $data = $response->json();
        $monerooId = data_get($data, 'data.id') ?? data_get($data, 'id');
        $paymentUrl = data_get($data, 'data.payment_url')
            ?? data_get($data, 'data.checkout_url')
            ?? data_get($data, 'payment_url')
            ?? data_get($data, 'checkout_url');

        $invoice->update([
            'payment_method' => 'moneroo',
            'metadata' => array_merge($invoice->metadata ?? [], [
                'moneroo_init' => $data,
                'moneroo_id' => $monerooId,
                'payment_ref' => $paymentRef,
            ]),
        ]);

        if ($paymentUrl) {
            return redirect()->away($paymentUrl);
        }

        return back()->with('success', 'Paiement initialise. Le statut sera mis a jour automatiquement.');
    }

    public function invoiceReturn(SubscriptionInvoice $invoice)
    {
        abort_unless($invoice->user_id === auth()->id(), 403);

        if ($invoice->status === 'paid') {
            return redirect()->route('customer.subscriptions')->with('success', 'Paiement de facture confirme.');
        }

        return redirect()->route('customer.subscriptions')->with('info', 'Paiement en cours de confirmation. Veuillez actualiser dans quelques instants.');
    }

    private function toMonerooMinor(float $amount, string $currency): int
    {
        $currency = strtoupper($currency);
        $noSubunitCurrencies = ['XOF', 'XAF', 'JPY', 'KRW', 'CLP', 'VND'];

        if (in_array($currency, $noSubunitCurrencies, true)) {
            return (int) round($amount);
        }

        return (int) round($amount * 100);
    }
}

