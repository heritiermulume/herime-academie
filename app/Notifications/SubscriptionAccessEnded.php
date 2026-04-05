<?php

namespace App\Notifications;

use App\Models\UserSubscription;
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

        return (new MailMessage)
            ->subject('Fin de votre abonnement — '.config('app.name'))
            ->greeting('Bonjour '.($notifiable->name ?? ''))
            ->line("Votre abonnement au plan « {$planName} » est terminé.")
            ->line('L’accès aux contenus réservés aux abonnés n’est plus actif pour ce plan.')
            ->action('Voir les offres', route('community.premium'))
            ->line('Nous serions ravis de vous revoir sur '.config('app.name').'.');
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
