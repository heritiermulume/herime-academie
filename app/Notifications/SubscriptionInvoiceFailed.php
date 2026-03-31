<?php

namespace App\Notifications;

use App\Models\SubscriptionInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionInvoiceFailed extends Notification
{
    use Queueable;

    public function __construct(private readonly SubscriptionInvoice $invoice)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_invoice_failed',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->amount,
            'currency' => $this->invoice->currency,
            'message' => 'Le paiement de la facture ' . $this->invoice->invoice_number . ' a echoue.',
            'icon' => 'fas fa-times-circle',
            'color' => 'danger',
            'action_url' => route('customer.subscriptions'),
            'action_text' => 'Regulariser',
        ];
    }
}

