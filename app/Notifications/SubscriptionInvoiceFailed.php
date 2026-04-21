<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\SubscriptionInvoice;
use App\Support\EmailBranding;
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
                ->view('emails.subscription-event', [
                    'logoUrl' => EmailBranding::logoUrl(),
                    'title' => 'Souscription non finalisée',
                    'subtitle' => 'Délai dépassé',
                    'badgeText' => 'Action requise',
                    'badgeColor' => '#dc3545',
                    'userName' => $notifiable->name ?? 'Client',
                    'intro' => "La facture « {$planName} » n’a pas été réglée dans le délai prévu.",
                    'detailsTitle' => 'Détails de la facture',
                    'detailLines' => [
                        ['label' => 'Facture', 'value' => $this->invoice->invoice_number],
                        ['label' => 'Plan', 'value' => $planName],
                        ['label' => 'Montant', 'value' => CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency)],
                    ],
                    'extraParagraphs' => [
                        'Votre demande d’adhésion a expiré. Vous pouvez relancer une souscription à tout moment depuis votre espace client.',
                    ],
                    'actionUrl' => route('customer.subscriptions'),
                    'actionLabel' => 'Voir les formules',
                ]);
        }

        return (new MailMessage)
            ->subject('Échec de paiement — facture '.$this->invoice->invoice_number)
            ->view('emails.subscription-event', [
                'logoUrl' => EmailBranding::logoUrl(),
                'title' => 'Échec de paiement',
                'subtitle' => 'Paiement non finalisé',
                'badgeText' => 'Paiement échoué',
                'badgeColor' => '#dc3545',
                'userName' => $notifiable->name ?? 'Client',
                'intro' => "Le paiement de votre facture d’abonnement « {$planName} » n’a pas pu être finalisé.",
                'detailsTitle' => 'Détails de la facture',
                'detailLines' => [
                    ['label' => 'Facture', 'value' => $this->invoice->invoice_number],
                    ['label' => 'Plan', 'value' => $planName],
                    ['label' => 'Montant', 'value' => CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency)],
                ],
                'extraParagraphs' => [
                    'Vous pouvez réessayer depuis votre espace client.',
                ],
                'actionUrl' => route('customer.subscriptions'),
                'actionLabel' => 'Régulariser mon abonnement',
            ]);
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
