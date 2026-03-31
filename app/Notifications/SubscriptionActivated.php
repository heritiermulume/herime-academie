<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\SubscriptionInvoice;
use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionActivated extends Notification
{
    use Queueable;

    public function __construct(
        private readonly UserSubscription $subscription,
        private readonly ?SubscriptionInvoice $invoice = null,
        private readonly bool $isRenewal = false
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $planName = $this->subscription->plan->name ?? 'Plan';
        $mail = (new MailMessage)
            ->subject($this->isRenewal ? 'Réabonnement confirmé - ' . config('app.name') : 'Abonnement confirmé - ' . config('app.name'))
            ->greeting('Bonjour ' . ($notifiable->name ?? ''))
            ->line($this->isRenewal
                ? "Votre réabonnement au plan \"{$planName}\" est bien enregistré."
                : "Votre abonnement au plan \"{$planName}\" est bien activé.")
            ->line('Vous pouvez suivre votre abonnement depuis votre espace client.')
            ->action('Voir mes abonnements', route('customer.subscriptions'));

        if ($this->invoice) {
            $mail->line('Facture associée : ' . $this->invoice->invoice_number)
                ->line('Montant : ' . CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency))
                ->line('Statut facture : En attente de paiement');
        } else {
            $mail->line('Aucune facture immédiate n\'est requise pour ce plan.');
        }

        return $mail->line('Merci de votre confiance.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->isRenewal ? 'subscription_renewal_scheduled' : 'subscription_activated',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name ?? 'Plan',
            'invoice_id' => $this->invoice?->id,
            'invoice_number' => $this->invoice?->invoice_number,
            'message' => $this->isRenewal
                ? 'Votre réabonnement est programmé pour la prochaine période.'
                : 'Votre abonnement est confirmé.',
            'action_url' => route('customer.subscriptions'),
            'action_text' => 'Voir mes abonnements',
        ];
    }
}

