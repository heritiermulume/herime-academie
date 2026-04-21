<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\SubscriptionInvoice;
use App\Support\EmailBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionInvoicePaid extends Notification
{
    use Queueable;

    public function __construct(private readonly SubscriptionInvoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->invoice->loadMissing('subscription.plan');
        $planName = $this->invoice->subscription?->plan->name ?? 'Programme membre';
        $paidAtText = optional($this->invoice->paid_at)
            ->timezone(config('app.timezone'))
            ->format('d/m/Y à H:i');

        return (new MailMessage)
            ->subject('Paiement confirmé - '.config('app.name'))
            ->view('emails.subscription-payment-received', [
                'logoUrl' => EmailBranding::logoUrl(),
                'userName' => $notifiable->name ?? 'Client',
                'invoiceNumber' => $this->invoice->invoice_number,
                'planName' => $planName,
                'amountFormatted' => CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency),
                'paidAtText' => $paidAtText,
                'subscriptionsUrl' => route('customer.subscriptions'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_invoice_paid',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->amount,
            'currency' => $this->invoice->currency,
            'message' => 'Paiement confirmé pour la facture '.$this->invoice->invoice_number,
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
            'action_url' => route('customer.subscriptions'),
            'action_text' => 'Voir mes abonnements',
        ];
    }
}
