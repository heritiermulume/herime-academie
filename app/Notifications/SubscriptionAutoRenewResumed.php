<?php

namespace App\Notifications;

use App\Models\UserSubscription;
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

        return (new MailMessage)
            ->subject('Renouvellement automatique réactivé — '.config('app.name'))
            ->greeting('Bonjour '.($notifiable->name ?? ''))
            ->line("Le renouvellement automatique pour le plan « {$planName} » est de nouveau activé.")
            ->line('Votre abonnement sera prolongé à chaque échéance, selon les conditions de votre formule.')
            ->when($periodEnd, fn (MailMessage $m) => $m->line('Prochaine fin de période affichée sur votre espace : '.$periodEnd->format('d/m/Y').'.'))
            ->action('Voir mes abonnements', route('customer.subscriptions'))
            ->line('Merci de votre confiance.');
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
