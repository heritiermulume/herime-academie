<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use App\Support\EmailBranding;
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
        $periodEndText = $periodEnd?->format('d/m/Y');

        return (new MailMessage)
            ->subject('Client : renouvellement automatique réactivé - '.config('app.name').' [Admin]')
            ->view('emails.admin-subscription-event', [
                'logoUrl' => EmailBranding::logoUrl(),
                'title' => 'Auto-renouvellement réactivé',
                'adminName' => $notifiable->name ?? null,
                'intro' => 'Un client a réactivé le renouvellement automatique de son abonnement.',
                'detailsTitle' => 'Détails de l’abonnement',
                'detailLines' => array_filter([
                    ['label' => 'Client', 'value' => ($user->name ?? 'N/A').($user?->email ? ' ('.$user->email.')' : '')],
                    ['label' => 'Plan', 'value' => $planName],
                    $periodEndText ? ['label' => 'Fin de période courante', 'value' => $periodEndText] : null,
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
