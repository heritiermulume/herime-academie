<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\ContentPackage;
use App\Models\SubscriptionInvoice;
use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionActivated extends Notification
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
        $planName = $this->subscription->plan->name ?? 'Plan';
        $mail = (new MailMessage)
            ->subject($this->isRenewal ? 'Réabonnement confirmé - ' . config('app.name') : 'Abonnement confirmé - ' . config('app.name'))
            ->greeting('Bonjour ' . ($notifiable->name ?? ''))
            ->line($this->isRenewal
                ? "Votre réabonnement au plan \"{$planName}\" est bien enregistré."
                : "Votre abonnement au plan \"{$planName}\" est bien activé.")
            ->line('Vous pouvez suivre votre abonnement depuis votre espace client.')
            ->action('Voir mes abonnements', route('customer.subscriptions'));

        if ($this->invoice) {
            $invoiceStatusLabel = $this->invoice->status === 'paid'
                ? 'Payée'
                : 'En attente de paiement';
            $mail->line('Facture associée : ' . $this->invoice->invoice_number)
                ->line('Montant : ' . CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency))
                ->line('Statut facture : ' . $invoiceStatusLabel);
        } else {
            $mail->line('Aucune facture immédiate n\'est requise pour ce plan.');
        }

        if (!empty($includedContentTitles)) {
            $mail->line('Formations incluses : ' . collect($includedContentTitles)->take(3)->join(', ')
                . (count($includedContentTitles) > 3 ? ' +' . (count($includedContentTitles) - 3) : ''));
        }
        if (!empty($includedPackageTitles)) {
            $mail->line('Packs inclus : ' . collect($includedPackageTitles)->take(3)->join(', ')
                . (count($includedPackageTitles) > 3 ? ' +' . (count($includedPackageTitles) - 3) : ''));
        }

        return $mail->line('Merci de votre confiance.');
    }

    public function toArray(object $notifiable): array
    {
        [$includedContentTitles, $includedPackageTitles] = $this->resolveIncludedItems();

        return [
            'type' => $this->isRenewal ? 'subscription_renewal_scheduled' : 'subscription_activated',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name ?? 'Plan',
            'invoice_id' => $this->invoice?->id,
            'invoice_number' => $this->invoice?->invoice_number,
            'included_contents' => $includedContentTitles,
            'included_packages' => $includedPackageTitles,
            'message' => $this->isRenewal
                ? 'Votre réabonnement est programmé pour la prochaine période.'
                : 'Votre abonnement est confirmé.',
            'action_url' => route('customer.subscriptions'),
            'action_text' => 'Voir mes abonnements',
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

