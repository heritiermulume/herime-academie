<?php

namespace App\Notifications;

use App\Models\UserSubscription;
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

        return (new MailMessage)
            ->subject('Annulation de votre abonnement — '.config('app.name'))
            ->greeting('Bonjour '.($notifiable->name ?? ''))
            ->line("Votre abonnement au plan « {$planName} » a bien été annulé.")
            ->line('Le renouvellement automatique est désactivé.')
            ->when($end, fn (MailMessage $m) => $m->line('Vous conservez l’accès jusqu’au '.$end->format('d/m/Y').' (fin de période en cours).'))
            ->action('Voir mes abonnements', route('customer.subscriptions'))
            ->line('Merci de votre confiance.');
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
