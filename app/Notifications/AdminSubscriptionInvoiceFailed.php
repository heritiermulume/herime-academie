<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\SubscriptionInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminSubscriptionInvoiceFailed extends Notification
{
    use Queueable;

    public function __construct(private readonly SubscriptionInvoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->invoice->loadMissing(['subscription.plan', 'user']);
        $user = $this->invoice->user;
        $planName = $this->invoice->subscription?->plan->name ?? 'Plan';

        return (new MailMessage)
            ->subject('Échec paiement facture abonnement — '.config('app.name'))
            ->greeting('Bonjour '.($notifiable->name ?? 'Admin'))
            ->line('Une facture d’abonnement est en échec ou a échoué côté paiement.')
            ->line('Client : '.($user->name ?? 'N/A').($user?->email ? ' ('.$user->email.')' : ''))
            ->line('Plan : '.$planName)
            ->line('Facture : '.$this->invoice->invoice_number)
            ->line('Montant : '.CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency))
            ->action('Voir les abonnements', route('admin.subscriptions.index'));
    }

    public function toArray(object $notifiable): array
    {
        $this->invoice->loadMissing(['subscription.plan', 'user']);
        $user = $this->invoice->user;

        return [
            'type' => 'admin_subscription_invoice_failed',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'customer_name' => $user->name ?? 'N/A',
            'customer_email' => $user->email ?? null,
            'plan_name' => $this->invoice->subscription?->plan->name ?? 'Plan',
            'message' => 'Échec de paiement — facture '.$this->invoice->invoice_number,
            'button_text' => 'Voir les abonnements',
            'button_url' => route('admin.subscriptions.index'),
            'url' => route('admin.subscriptions.index'),
        ];
    }
}
