<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\SubscriptionInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionInvoiceCancelled extends Notification
{
    use Queueable;

    public function __construct(private readonly SubscriptionInvoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Facture d’abonnement annulée — '.config('app.name'))
            ->greeting('Bonjour '.($notifiable->name ?? ''))
            ->line('La facture '.$this->invoice->invoice_number.' a été annulée.')
            ->line('Montant : '.CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency))
            ->line('Si vous avez une question, contactez le support.')
            ->action('Voir mes abonnements', route('customer.subscriptions'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_invoice_cancelled',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->amount,
            'currency' => $this->invoice->currency,
            'message' => 'La facture '.$this->invoice->invoice_number.' a été annulée.',
            'icon' => 'fas fa-ban',
            'color' => 'secondary',
            'action_url' => route('customer.subscriptions'),
            'action_text' => 'Voir mes abonnements',
        ];
    }
}
