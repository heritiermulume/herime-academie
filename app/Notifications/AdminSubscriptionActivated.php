<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\ContentPackage;
use App\Models\SubscriptionInvoice;
use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminSubscriptionActivated extends Notification
{
    use Queueable;

    public function __construct(
        private readonly UserSubscription $subscription,
        private readonly ?SubscriptionInvoice $invoice = null,
        private readonly bool $isRenewal = false
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        [$includedContentTitles, $includedPackageTitles] = $this->resolveIncludedItems();
        $user = $this->subscription->user;
        $planName = $this->subscription->plan->name ?? 'Plan';

        $mail = (new MailMessage)
            ->subject($this->isRenewal ? 'Réabonnement client - ' . config('app.name') : 'Nouvel abonnement client - ' . config('app.name'))
            ->greeting('Bonjour ' . ($notifiable->name ?? 'Admin'))
            ->line($this->isRenewal
                ? 'Un client a programmé un réabonnement.'
                : 'Un client vient de souscrire un abonnement.')
            ->line('Client : ' . ($user->name ?? 'N/A') . ($user?->email ? ' (' . $user->email . ')' : ''))
            ->line('Plan : ' . $planName)
            ->line('Période en cours : ' . optional($this->subscription->current_period_starts_at)->format('d/m/Y') . ' - ' . optional($this->subscription->current_period_ends_at)->format('d/m/Y'))
            ->action('Voir les abonnements admin', route('admin.subscriptions.index'));

        if ($this->invoice) {
            $mail->line('Facture : ' . $this->invoice->invoice_number)
                ->line('Montant : ' . CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency))
                ->line('Statut : En attente de paiement');
        }
        if (!empty($includedContentTitles)) {
            $mail->line('Formations incluses : ' . collect($includedContentTitles)->take(3)->join(', ')
                . (count($includedContentTitles) > 3 ? ' +' . (count($includedContentTitles) - 3) : ''));
        }
        if (!empty($includedPackageTitles)) {
            $mail->line('Packs inclus : ' . collect($includedPackageTitles)->take(3)->join(', ')
                . (count($includedPackageTitles) > 3 ? ' +' . (count($includedPackageTitles) - 3) : ''));
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        [$includedContentTitles, $includedPackageTitles] = $this->resolveIncludedItems();
        $user = $this->subscription->user;

        return [
            'type' => $this->isRenewal ? 'admin_subscription_renewal_scheduled' : 'admin_subscription_created',
            'subscription_id' => $this->subscription->id,
            'invoice_id' => $this->invoice?->id,
            'invoice_number' => $this->invoice?->invoice_number,
            'customer_name' => $user->name ?? 'N/A',
            'customer_email' => $user->email ?? null,
            'plan_name' => $this->subscription->plan->name ?? 'Plan',
            'included_contents' => $includedContentTitles,
            'included_packages' => $includedPackageTitles,
            'message' => $this->isRenewal
                ? 'Réabonnement programmé par ' . ($user->name ?? 'un client') . '.'
                : 'Nouvel abonnement créé par ' . ($user->name ?? 'un client') . '.',
            'button_text' => 'Voir les abonnements',
            'button_url' => route('admin.subscriptions.index'),
            'url' => route('admin.subscriptions.index'),
        ];
    }

    private function resolveIncludedItems(): array
    {
        $this->subscription->loadMissing('plan.contents');
        $plan = $this->subscription->plan;
        if (!$plan) {
            return [[], []];
        }

        $includedContentTitles = $plan->contents
            ->pluck('title')
            ->filter()
            ->values()
            ->all();

        $includedPackageIds = collect(data_get($plan->metadata, 'included_package_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $includedPackageTitles = empty($includedPackageIds)
            ? []
            : ContentPackage::query()
                ->whereIn('id', $includedPackageIds)
                ->pluck('title')
                ->filter()
                ->values()
                ->all();

        return [$includedContentTitles, $includedPackageTitles];
    }
}

