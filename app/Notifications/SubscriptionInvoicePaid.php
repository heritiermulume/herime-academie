<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\SubscriptionInvoice;
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
        $planName = $this->invoice->subscription?->plan->name ?? 'Votre plan';

        return (new MailMessage)
            ->subject('Paiement reçu — facture '.$this->invoice->invoice_number)
            ->greeting('Bonjour '.($notifiable->name ?? ''))
            ->line("Nous avons bien enregistré le paiement de votre facture d’abonnement pour « {$planName} ».")
            ->line('Facture : '.$this->invoice->invoice_number)
            ->line('Montant : '.CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency))
            ->action('Voir mes abonnements', route('customer.subscriptions'))
            ->line('Merci de votre confiance.');
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
