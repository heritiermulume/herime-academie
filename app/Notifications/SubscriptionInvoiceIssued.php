<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\SubscriptionInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionInvoiceIssued extends Notification
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
            ->subject('Nouvelle facture d’abonnement — '.config('app.name'))
            ->greeting('Bonjour '.($notifiable->name ?? ''))
            ->line("Une nouvelle facture a été émise pour « {$planName} » (renouvellement ou relance).")
            ->line('Numéro : '.$this->invoice->invoice_number)
            ->line('Montant : '.CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency))
            ->line('Merci de la régler depuis votre espace pour conserver votre accès.')
            ->action('Voir mes abonnements et payer', route('customer.subscriptions'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_invoice_issued',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->amount,
            'currency' => $this->invoice->currency,
            'message' => 'Nouvelle facture d\'abonnement : '.$this->invoice->invoice_number,
            'icon' => 'fas fa-file-invoice-dollar',
            'color' => 'warning',
            'action_url' => route('customer.subscriptions'),
            'action_text' => 'Voir mes abonnements',
        ];
    }
}
