<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\SubscriptionInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionInvoiceFailed extends Notification
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
            ->subject('Échec de paiement — facture '.$this->invoice->invoice_number)
            ->greeting('Bonjour '.($notifiable->name ?? ''))
            ->line("Le paiement de votre facture d’abonnement « {$planName} » n’a pas pu être finalisé.")
            ->line('Facture : '.$this->invoice->invoice_number)
            ->line('Montant : '.CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency))
            ->line('Vous pouvez réessayer depuis votre espace client.')
            ->action('Régulariser mon abonnement', route('customer.subscriptions'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_invoice_failed',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->amount,
            'currency' => $this->invoice->currency,
            'message' => 'Le paiement de la facture '.$this->invoice->invoice_number.' a échoué.',
            'icon' => 'fas fa-times-circle',
            'color' => 'danger',
            'action_url' => route('customer.subscriptions'),
            'action_text' => 'Régulariser',
        ];
    }
}
