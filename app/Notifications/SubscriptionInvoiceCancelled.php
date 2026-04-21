<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\SubscriptionInvoice;
use App\Support\EmailBranding;
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
        $this->invoice->loadMissing('subscription.plan');
        $planName = $this->invoice->subscription?->plan->name ?? 'Programme membre';

        return (new MailMessage)
            ->subject('Facture d’abonnement annulée — '.config('app.name'))
            ->view('emails.subscription-event', [
                'logoUrl' => EmailBranding::logoUrl(),
                'title' => 'Facture annulée',
                'subtitle' => 'Mise à jour de facturation',
                'badgeText' => 'Facture annulée',
                'badgeColor' => '#6c757d',
                'userName' => $notifiable->name ?? 'Client',
                'intro' => 'La facture de votre abonnement a été annulée.',
                'detailsTitle' => 'Détails de la facture',
                'detailLines' => [
                    ['label' => 'Facture', 'value' => $this->invoice->invoice_number],
                    ['label' => 'Plan', 'value' => $planName],
                    ['label' => 'Montant', 'value' => CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency)],
                ],
                'extraParagraphs' => [
                    'Si vous avez une question, contactez le support.',
                ],
                'actionUrl' => route('customer.subscriptions'),
                'actionLabel' => 'Voir mes abonnements',
            ]);
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
