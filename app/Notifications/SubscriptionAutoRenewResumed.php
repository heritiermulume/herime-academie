<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use App\Support\EmailBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionAutoRenewResumed extends Notification
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
        $periodEnd = $this->subscription->current_period_ends_at;
        $periodEndText = $periodEnd?->format('d/m/Y');

        return (new MailMessage)
            ->subject('Renouvellement automatique réactivé — '.config('app.name'))
            ->view('emails.subscription-event', [
                'logoUrl' => EmailBranding::logoUrl(),
                'title' => 'Renouvellement réactivé',
                'subtitle' => 'Votre abonnement continue automatiquement',
                'badgeText' => 'Auto-renouvellement actif',
                'badgeColor' => '#28a745',
                'userName' => $notifiable->name ?? 'Client',
                'intro' => "Le renouvellement automatique pour le plan « {$planName} » est de nouveau activé.",
                'detailsTitle' => 'Détails de l’abonnement',
                'detailLines' => array_filter([
                    ['label' => 'Plan', 'value' => $planName],
                    $periodEndText ? ['label' => 'Prochaine fin de période', 'value' => $periodEndText] : null,
                ]),
                'extraParagraphs' => [
                    'Votre abonnement sera prolongé à chaque échéance, selon les conditions de votre formule.',
                ],
                'actionUrl' => route('customer.subscriptions'),
                'actionLabel' => 'Voir mes abonnements',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $this->subscription->loadMissing('plan');

        return [
            'type' => 'subscription_auto_renew_resumed',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name ?? 'Plan',
            'message' => 'Le renouvellement automatique a été réactivé pour votre abonnement.',
            'icon' => 'fas fa-sync-alt',
            'color' => 'success',
            'action_url' => route('customer.subscriptions'),
            'action_text' => 'Voir mes abonnements',
        ];
    }
}
