<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\SubscriptionInvoice;
use App\Support\EmailBranding;
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
            ->subject('Échec paiement facture abonnement - '.config('app.name').' [Admin]')
            ->view('emails.admin-subscription-event', [
                'logoUrl' => EmailBranding::logoUrl(),
                'title' => 'Échec de paiement abonnement',
                'adminName' => $notifiable->name ?? null,
                'intro' => 'Une facture d’abonnement est en échec ou a échoué côté paiement.',
                'detailsTitle' => 'Détails de la facture',
                'detailLines' => [
                    ['label' => 'Client', 'value' => ($user->name ?? 'N/A').($user?->email ? ' ('.$user->email.')' : '')],
                    ['label' => 'Plan', 'value' => $planName],
                    ['label' => 'Facture', 'value' => $this->invoice->invoice_number],
                    ['label' => 'Montant', 'value' => CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency)],
                ],
                'actionUrl' => route('admin.subscriptions.index'),
                'actionLabel' => 'Voir les abonnements admin',
            ]);
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
