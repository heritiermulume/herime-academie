<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use App\Support\EmailBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCancelled extends Notification
{
    use Queueable;

    public function __construct(private readonly UserSubscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->subscription->loadMissing('plan');
        $planName = $this->subscription->plan->name ?? 'Plan';
        $end = $this->subscription->ended_at ?? $this->subscription->current_period_ends_at;
        $endText = $end ? $end->format('d/m/Y') : null;

        return (new MailMessage)
            ->subject('Annulation de votre abonnement — '.config('app.name'))
            ->view('emails.subscription-event', [
                'logoUrl' => EmailBranding::logoUrl(),
                'title' => 'Abonnement annulé',
                'subtitle' => 'Mise à jour de votre abonnement',
                'badgeText' => 'Renouvellement désactivé',
                'badgeColor' => '#6c757d',
                'userName' => $notifiable->name ?? 'Client',
                'intro' => "Votre abonnement au plan « {$planName} » a bien été annulé.",
                'detailsTitle' => 'Détails de l’annulation',
                'detailLines' => array_filter([
                    ['label' => 'Plan', 'value' => $planName],
                    $endText ? ['label' => 'Accès conservé jusqu’au', 'value' => $endText] : null,
                ]),
                'extraParagraphs' => [
                    'Le renouvellement automatique est désactivé.',
                ],
                'actionUrl' => route('customer.subscriptions'),
                'actionLabel' => 'Voir mes abonnements',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $this->subscription->loadMissing('plan');

        return [
            'type' => 'subscription_cancelled',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name ?? 'Plan',
            'message' => 'Votre abonnement a été annulé. L’accès reste actif jusqu’à la fin de la période en cours.',
            'icon' => 'fas fa-user-slash',
            'color' => 'warning',
            'action_url' => route('customer.subscriptions'),
            'action_text' => 'Voir mes abonnements',
        ];
    }
}
