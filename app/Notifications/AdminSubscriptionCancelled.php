<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use App\Support\EmailBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminSubscriptionCancelled extends Notification
{
    use Queueable;

    public function __construct(
        private readonly UserSubscription $subscription,
        private readonly bool $cancelledByAdmin = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->subscription->loadMissing(['plan', 'user']);
        $user = $this->subscription->user;
        $planName = $this->subscription->plan->name ?? 'Plan';
        $end = $this->subscription->ended_at ?? $this->subscription->current_period_ends_at;

        $intro = $this->cancelledByAdmin
            ? 'Un administrateur a annulé l’abonnement d’un client.'
            : 'Un client a annulé son abonnement.';

        return (new MailMessage)
            ->subject('Annulation d’abonnement client - '.config('app.name').' [Admin]')
            ->view('emails.admin-subscription-event', [
                'logoUrl' => EmailBranding::logoUrl(),
                'title' => 'Annulation d’abonnement',
                'adminName' => $notifiable->name ?? null,
                'intro' => $intro,
                'detailsTitle' => 'Détails de l’annulation',
                'detailLines' => array_filter([
                    ['label' => 'Client', 'value' => ($user->name ?? 'N/A').($user?->email ? ' ('.$user->email.')' : '')],
                    ['label' => 'Plan', 'value' => $planName],
                    $end ? ['label' => 'Fin d’accès prévue', 'value' => $end->format('d/m/Y')] : null,
                ]),
                'actionUrl' => route('admin.subscriptions.index'),
                'actionLabel' => 'Voir les abonnements admin',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $this->subscription->loadMissing(['plan', 'user']);
        $user = $this->subscription->user;

        $message = $this->cancelledByAdmin
            ? 'Annulation d’abonnement effectuée depuis l’administration pour '.($user->name ?? 'un client').'.'
            : ($user->name ?? 'Un client').' a annulé son abonnement.';

        return [
            'type' => 'admin_subscription_cancelled',
            'subscription_id' => $this->subscription->id,
            'customer_name' => $user->name ?? 'N/A',
            'customer_email' => $user->email ?? null,
            'plan_name' => $this->subscription->plan->name ?? 'Plan',
            'message' => $message,
            'button_text' => 'Voir les abonnements',
            'button_url' => route('admin.subscriptions.index'),
            'url' => route('admin.subscriptions.index'),
        ];
    }
}
