<?php

namespace App\Notifications;

use App\Models\SubscriptionInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionInvoiceIssued extends Notification
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
            'type' => 'subscription_invoice_issued',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->amount,
            'currency' => $this->invoice->currency,
            'message' => 'Nouvelle facture d\'abonnement emise: ' . $this->invoice->invoice_number,
            'icon' => 'fas fa-file-invoice-dollar',
            'color' => 'warning',
            'action_url' => route('customer.subscriptions'),
            'action_text' => 'Voir mes abonnements',
        ];
    }
}

