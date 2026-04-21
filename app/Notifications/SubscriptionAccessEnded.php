<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use App\Support\EmailBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Abonnement passé en « expired » (fin de période atteinte).
 */
class SubscriptionAccessEnded extends Notification
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
        $endedAtText = optional($this->subscription->ended_at ?? $this->subscription->current_period_ends_at)->format('d/m/Y');

        return (new MailMessage)
            ->subject('Fin de votre abonnement — '.config('app.name'))
            ->view('emails.subscription-event', [
                'logoUrl' => EmailBranding::logoUrl(),
                'title' => 'Fin de votre abonnement',
                'subtitle' => 'Accès abonnement terminé',
                'badgeText' => 'Abonnement expiré',
                'badgeColor' => '#6c757d',
                'userName' => $notifiable->name ?? 'Client',
                'intro' => "Votre abonnement au plan « {$planName} » est terminé.",
                'detailsTitle' => 'Détails',
                'detailLines' => array_filter([
                    ['label' => 'Plan', 'value' => $planName],
                    $endedAtText ? ['label' => 'Date de fin', 'value' => $endedAtText] : null,
                ]),
                'extraParagraphs' => [
                    'L’accès aux contenus réservés aux abonnés n’est plus actif pour ce plan.',
                    'Nous serions ravis de vous revoir sur '.config('app.name').'.',
                ],
                'actionUrl' => route('community.premium'),
                'actionLabel' => 'Voir les offres',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $this->subscription->loadMissing('plan');

        return [
            'type' => 'subscription_access_ended',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name ?? 'Plan',
            'message' => 'Votre abonnement est terminé.',
            'icon' => 'fas fa-hourglass-end',
            'color' => 'secondary',
            'action_url' => route('community.premium'),
            'action_text' => 'Voir les offres',
        ];
    }
}
