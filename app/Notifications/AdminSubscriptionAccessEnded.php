<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminSubscriptionAccessEnded extends Notification
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

        return (new MailMessage)
            ->subject('Abonnement expiré (fin de période) — '.config('app.name'))
            ->greeting('Bonjour '.($notifiable->name ?? 'Admin'))
            ->line('Un abonnement est passé en statut expiré (fin de période atteinte).')
            ->line('Client : '.($user->name ?? 'N/A').($user?->email ? ' ('.$user->email.')' : ''))
            ->line('Plan : '.$planName)
            ->action('Voir les abonnements', route('admin.subscriptions.index'));
    }

    public function toArray(object $notifiable): array
    {
        $this->subscription->loadMissing(['plan', 'user']);
        $user = $this->subscription->user;

        return [
            'type' => 'admin_subscription_access_ended',
            'subscription_id' => $this->subscription->id,
            'customer_name' => $user->name ?? 'N/A',
            'customer_email' => $user->email ?? null,
            'plan_name' => $this->subscription->plan->name ?? 'Plan',
            'message' => 'Abonnement expiré : '.($user->name ?? 'Client').' — '.($this->subscription->plan->name ?? 'Plan'),
            'button_text' => 'Voir les abonnements',
            'button_url' => route('admin.subscriptions.index'),
            'url' => route('admin.subscriptions.index'),
        ];
    }
}
