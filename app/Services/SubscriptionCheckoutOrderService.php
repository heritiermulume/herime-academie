<?php

namespace App\Services;

use App\Http\Controllers\MonerooController;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Crée une commande Order + enregistrement Payment pour un paiement d’abonnement Moneroo,
 * sur le même modèle que le panier (suivi admin, finalisation après webhook).
 */
class SubscriptionCheckoutOrderService
{
    /**
     * Avant une nouvelle init Moneroo, annule les commandes d’abonnement encore en attente pour cette facture
     * et marque leurs paiements comme échoués, pour éviter plusieurs paiements actifs sur une même commande.
     */
    public function cancelPriorPendingSubscriptionCheckoutsForInvoice(SubscriptionInvoice $invoice, User $user): void
    {
        DB::transaction(function () use ($invoice, $user) {
            $orders = Order::query()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('payment_method', 'moneroo')
                ->where('billing_info->subscription_invoice_id', $invoice->id)
                ->lockForUpdate()
                ->get();

            foreach ($orders as $order) {
                Payment::query()
                    ->where('order_id', $order->id)
                    ->where('payment_method', 'moneroo')
                    ->whereIn('status', ['pending', 'processing'])
                    ->update([
                        'status' => 'failed',
                        'failure_reason' => 'Nouvelle tentative de paiement (commande remplacée)',
                    ]);

                $order->update(['status' => 'cancelled']);
            }
        });
    }

    /**
     * Après un paiement réussi sur une commande liée à une facture, annule les autres commandes encore
     * en attente pour la même facture (ex. onglet Moneroo obsolète après une nouvelle init).
     */
    /**
     * Lorsqu’une facture passe en annulée / échouée côté métier, fermer les commandes Moneroo encore en attente
     * qui pointaient sur cette facture (évite des commandes « pending » orphelines).
     *
     * @param  array<int>  $invoiceIds
     */
    public function cancelPendingOrdersForInvoicesClosed(array $invoiceIds, string $failureReason = 'Facture non payable (annulée ou expirée)'): void
    {
        $invoiceIds = array_values(array_unique(array_map('intval', $invoiceIds)));
        if ($invoiceIds === []) {
            return;
        }

        DB::transaction(function () use ($invoiceIds, $failureReason) {
            $orders = Order::query()
                ->where('status', 'pending')
                ->where('payment_method', 'moneroo')
                ->where(function ($q) use ($invoiceIds) {
                    foreach ($invoiceIds as $id) {
                        $q->orWhere('billing_info->subscription_invoice_id', $id);
                    }
                })
                ->lockForUpdate()
                ->get();

            foreach ($orders as $order) {
                Payment::query()
                    ->where('order_id', $order->id)
                    ->where('payment_method', 'moneroo')
                    ->whereIn('status', ['pending', 'processing'])
                    ->update([
                        'status' => 'failed',
                        'failure_reason' => $failureReason,
                    ]);

                $order->update(['status' => 'cancelled']);
            }
        });
    }

    public function cancelOtherPendingSubscriptionCheckoutsForInvoice(
        SubscriptionInvoice $invoice,
        User $user,
        int $exceptOrderId,
    ): void {
        DB::transaction(function () use ($invoice, $user, $exceptOrderId) {
            $orders = Order::query()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('payment_method', 'moneroo')
                ->where('billing_info->subscription_invoice_id', $invoice->id)
                ->whereKeyNot($exceptOrderId)
                ->lockForUpdate()
                ->get();

            foreach ($orders as $order) {
                Payment::query()
                    ->where('order_id', $order->id)
                    ->where('payment_method', 'moneroo')
                    ->whereIn('status', ['pending', 'processing'])
                    ->update([
                        'status' => 'failed',
                        'failure_reason' => 'Paiement enregistré sur une autre commande pour la même facture',
                    ]);

                $order->update(['status' => 'cancelled']);
            }
        });
    }

    /**
     * Après marquage payé par l’admin : aligner Payment + Order + finalisation comme un webhook Moneroo « success ».
     */
    public function finalizeLinkedMonerooOrderWhenInvoiceMarkedPaidByAdmin(SubscriptionInvoice $invoice): void
    {
        $orderId = data_get($invoice->metadata, 'order_id');
        if (! $orderId) {
            return;
        }

        $order = Order::query()->find((int) $orderId);
        if (! $order || (int) $order->user_id !== (int) $invoice->user_id) {
            return;
        }

        if ((int) data_get($order->billing_info, 'subscription_invoice_id') !== (int) $invoice->id) {
            return;
        }

        if (in_array($order->status, ['paid', 'completed'], true)) {
            return;
        }

        if ($order->status === 'cancelled') {
            return;
        }

        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->where('payment_method', 'moneroo')
            ->whereIn('status', ['pending', 'processing'])
            ->latest('id')
            ->first();

        if ($payment) {
            $payment->update([
                'status' => 'completed',
                'processed_at' => now(),
                'payment_data' => array_merge($payment->payment_data ?? [], [
                    'admin_marked_invoice_paid_at' => now()->toIso8601String(),
                    'admin_marked_invoice_id' => $invoice->id,
                ]),
            ]);
        }

        $order->update([
            'status' => 'paid',
            'paid_at' => $order->paid_at ?: now(),
            'payment_reference' => $order->payment_reference ?: ('admin_inv_'.$invoice->id),
        ]);

        app(MonerooController::class)->finalizeOrderAfterSuccessfulPayment($order->fresh());

        if ($invoice->user) {
            $this->cancelOtherPendingSubscriptionCheckoutsForInvoice($invoice, $invoice->user, $order->id);
        }
    }

    public function createPendingOrderForSubscriptionInvoice(SubscriptionInvoice $invoice, User $user, Request $request): Order
    {
        $invoice->loadMissing('subscription.plan');

        $amount = (float) $invoice->amount;
        $baseCurrency = strtoupper((string) (Setting::getBaseCurrency() ?: 'USD'));
        $payCurrency = strtoupper((string) ($invoice->currency ?: $baseCurrency));
        // Même logique que le panier : devise « compta » = base du site, montant encaissé = devise Moneroo / facture.
        // Si la facture est dans une autre devise que la base, on ne invente pas de taux : les totaux restent le montant
        // facturé et la devise de paiement (cohérent avec WalletRevenue / Moneroo).
        $exchangeRate = $payCurrency === $baseCurrency ? 1.0 : null;
        $orderCurrency = $payCurrency === $baseCurrency ? $baseCurrency : $payCurrency;

        return Order::query()->create([
            'order_number' => 'SUB-'.strtoupper(Str::random(8)).'-'.time(),
            'user_id' => $user->id,
            'subtotal' => $amount,
            'discount' => 0,
            'total' => $amount,
            'total_amount' => $amount,
            'currency' => $orderCurrency,
            'payment_currency' => $payCurrency,
            'payment_amount' => $amount,
            'exchange_rate' => $exchangeRate,
            'status' => 'pending',
            'payment_method' => 'moneroo',
            'payment_provider' => null,
            'payer_country' => config('services.moneroo.default_country', 'SN'),
            'customer_ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'billing_info' => [
                'subscription_invoice_id' => $invoice->id,
                'subscription_invoice_number' => $invoice->invoice_number,
                'user_subscription_id' => $invoice->user_subscription_id,
                'subscription_plan_id' => $invoice->subscription?->subscription_plan_id,
                'site_base_currency' => $baseCurrency,
            ],
            'notes' => 'Abonnement — facture '.$invoice->invoice_number,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>|null  $errorResponse
     */
    public function recordFailedInitPayment(
        Order $order,
        string $localPaymentRef,
        array $payload,
        ?array $errorResponse,
        string $failureReason,
    ): void {
        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'moneroo',
            'provider' => null,
            'payment_id' => $localPaymentRef,
            'amount' => $order->payment_amount ?? $order->total,
            'currency' => $order->payment_currency ?? $order->currency,
            'status' => 'failed',
            'failure_reason' => $failureReason,
            'payment_data' => [
                'request' => $payload,
                'response' => $errorResponse ?? [],
            ],
        ]);
        $order->update(['status' => 'cancelled']);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $responseData
     */
    public function recordPendingPayment(
        Order $order,
        string $localPaymentRef,
        array $payload,
        array $responseData,
        string $monerooPaymentId,
    ): Payment {
        return Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'moneroo',
            'provider' => null,
            'payment_id' => $localPaymentRef,
            'amount' => $order->payment_amount ?? $order->total,
            'currency' => $order->payment_currency ?? $order->currency,
            'status' => 'pending',
            'payment_data' => [
                'request' => $payload,
                'response' => $responseData,
                'moneroo_id' => $monerooPaymentId,
                'local_reference' => $localPaymentRef,
            ],
        ]);
    }
}
