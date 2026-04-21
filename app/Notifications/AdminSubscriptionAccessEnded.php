<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use App\Support\EmailBranding;
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
        $endedAtText = optional($this->subscription->ended_at ?? $this->subscription->current_period_ends_at)->format('d/m/Y');

        return (new MailMessage)
            ->subject('Abonnement expiré (fin de période) - '.config('app.name').' [Admin]')
            ->view('emails.admin-subscription-event', [
                'logoUrl' => EmailBranding::logoUrl(),
                'title' => 'Abonnement expiré',
                'adminName' => $notifiable->name ?? null,
                'intro' => 'Un abonnement est passé en statut expiré (fin de période atteinte).',
                'detailsTitle' => 'Détails de l’expiration',
                'detailLines' => array_filter([
                    ['label' => 'Client', 'value' => ($user->name ?? 'N/A').($user?->email ? ' ('.$user->email.')' : '')],
                    ['label' => 'Plan', 'value' => $planName],
                    $endedAtText ? ['label' => 'Date de fin', 'value' => $endedAtText] : null,
                ]),
                'actionUrl' => route('admin.subscriptions.index'),
                'actionLabel' => 'Voir les abonnements admin',
            ]);
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
