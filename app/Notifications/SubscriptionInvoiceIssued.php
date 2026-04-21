<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\SubscriptionInvoice;
use App\Support\EmailBranding;
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
        $planName = $this->invoice->subscription?->plan->name ?? 'Programme membre';

        return (new MailMessage)
            ->subject('Nouvelle facture d’abonnement — '.config('app.name'))
            ->view('emails.subscription-event', [
                'logoUrl' => EmailBranding::logoUrl(),
                'title' => 'Facture d’abonnement émise',
                'subtitle' => 'Action requise',
                'badgeText' => 'Paiement en attente',
                'badgeColor' => '#f39c12',
                'userName' => $notifiable->name ?? 'Client',
                'intro' => "Une nouvelle facture a été émise pour « {$planName} » (renouvellement ou relance).",
                'detailsTitle' => 'Détails de la facture',
                'detailLines' => [
                    ['label' => 'Facture', 'value' => $this->invoice->invoice_number],
                    ['label' => 'Plan', 'value' => $planName],
                    ['label' => 'Montant', 'value' => CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency)],
                ],
                'extraParagraphs' => [
                    'Merci de régler cette facture depuis votre espace pour conserver votre accès.',
                ],
                'actionUrl' => route('customer.subscriptions'),
                'actionLabel' => 'Voir mes abonnements et payer',
            ]);
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
