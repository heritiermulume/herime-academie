<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminSubscriptionAutoRenewResumed extends Notification
{
    use Queueable;

    public function __construct(private readonly UserSubscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->subscription->loadMissing(['plan', 'user']);
        $user = $this->subscription->user;
        $planName = $this->subscription->plan->name ?? 'Plan';
        $periodEnd = $this->subscription->current_period_ends_at;

        return (new MailMessage)
            ->subject('Client : renouvellement automatique réactivé — '.config('app.name'))
            ->greeting('Bonjour '.($notifiable->name ?? 'Admin'))
            ->line('Un client a réactivé le renouvellement automatique de son abonnement.')
            ->line('Client : '.($user->name ?? 'N/A').($user?->email ? ' ('.$user->email.')' : ''))
            ->line('Plan : '.$planName)
            ->when($periodEnd, fn (MailMessage $m) => $m->line('Fin de période courante (indicative) : '.$periodEnd->format('d/m/Y')))
            ->action('Voir les abonnements', route('admin.subscriptions.index'));
    }

    public function toArray(object $notifiable): array
    {
        $this->subscription->loadMissing(['plan', 'user']);
        $user = $this->subscription->user;

        return [
            'type' => 'admin_subscription_auto_renew_resumed',
            'subscription_id' => $this->subscription->id,
            'customer_name' => $user->name ?? 'N/A',
            'customer_email' => $user->email ?? null,
            'plan_name' => $this->subscription->plan->name ?? 'Plan',
            'message' => ($user->name ?? 'Un client').' a réactivé le renouvellement automatique.',
            'button_text' => 'Voir les abonnements',
            'button_url' => route('admin.subscriptions.index'),
            'url' => route('admin.subscriptions.index'),
        ];
    }
}
