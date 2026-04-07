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

    public function __construct(
        private readonly SubscriptionInvoice $invoice,
        private readonly bool $firstPaymentDeadlineExpired = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->invoice->loadMissing('subscription.plan');
        $planName = $this->invoice->subscription?->plan->name ?? 'Votre plan';

        if ($this->firstPaymentDeadlineExpired) {
            return (new MailMessage)
                ->subject('Souscription non finalisée — délai dépassé — '.config('app.name'))
                ->greeting('Bonjour '.($notifiable->name ?? ''))
                ->line("La facture « {$planName} » ({$this->invoice->invoice_number}) n’a pas été réglée dans le délai prévu.")
                ->line('Montant : '.CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency))
                ->line('Votre demande d’adhésion a expiré. Vous pouvez relancer une souscription quand vous le souhaitez depuis votre espace client.')
                ->action('Voir les formules', route('customer.subscriptions'));
        }

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
        $message = $this->firstPaymentDeadlineExpired
            ? 'Délai de paiement dépassé — souscription non finalisée ('.$this->invoice->invoice_number.').'
            : 'Le paiement de la facture '.$this->invoice->invoice_number.' a échoué.';

        return [
            'type' => 'subscription_invoice_failed',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->amount,
            'currency' => $this->invoice->currency,
            'message' => $message,
            'first_payment_deadline_expired' => $this->firstPaymentDeadlineExpired,
            'icon' => 'fas fa-times-circle',
            'color' => 'danger',
            'action_url' => route('customer.subscriptions'),
            'action_text' => $this->firstPaymentDeadlineExpired ? 'Voir les formules' : 'Régulariser',
        ];
    }
}
