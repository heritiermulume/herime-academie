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
        private readonly bool $isRenewal = false,
        private readonly bool $isPaidCycleRenewal = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        [$includedContentTitles, $includedPackageTitles] = $this->resolveIncludedItems();
        $planName = $this->subscription->plan->name ?? 'Plan';
        $periodEnd = $this->subscription->current_period_ends_at;

        if ($this->isPaidCycleRenewal) {
            $subject = 'Renouvellement payé — '.config('app.name');
            $intro = "Nous avons bien reçu votre paiement pour le plan « {$planName} ». Votre période d’abonnement est prolongée.";
            $periodLine = $periodEnd
                ? 'Prochaine échéance de période : '.$periodEnd->format('d/m/Y').'.'
                : null;
        } elseif ($this->isRenewal) {
            $subject = 'Réabonnement confirmé — '.config('app.name');
            $intro = "Votre réabonnement au plan « {$planName} » est bien enregistré.";
            $periodLine = null;
        } else {
            $subject = 'Abonnement confirmé — '.config('app.name');
            $intro = "Votre abonnement au plan « {$planName} » est bien activé.";
            $periodLine = null;
        }

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Bonjour '.($notifiable->name ?? ''))
            ->line($intro)
            ->when($periodLine, fn (MailMessage $m) => $m->line($periodLine))
            ->line('Vous pouvez suivre votre abonnement depuis votre espace client.')
            ->action('Voir mes abonnements', route('customer.subscriptions'));

        if ($this->invoice) {
            $invoiceStatusLabel = $this->invoice->status === 'paid'
                ? 'Payée'
                : 'En attente de paiement';
            $mail->line('Facture associée : '.$this->invoice->invoice_number)
                ->line('Montant : '.CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency))
                ->line('Statut facture : '.$invoiceStatusLabel);
        } else {
            $mail->line('Aucune facture immédiate n\'est requise pour ce plan.');
        }

        if (! empty($includedContentTitles)) {
            $mail->line('Formations incluses : '.collect($includedContentTitles)->take(3)->join(', ')
                .(count($includedContentTitles) > 3 ? ' +'.(count($includedContentTitles) - 3) : ''));
        }
        if (! empty($includedPackageTitles)) {
            $mail->line('Packs inclus : '.collect($includedPackageTitles)->take(3)->join(', ')
                .(count($includedPackageTitles) > 3 ? ' +'.(count($includedPackageTitles) - 3) : ''));
        }

        return $mail->line('Merci de votre confiance.');
    }

    public function toArray(object $notifiable): array
    {
        [$includedContentTitles, $includedPackageTitles] = $this->resolveIncludedItems();

        return [
            'type' => $this->isPaidCycleRenewal
                ? 'subscription_paid_renewal'
                : ($this->isRenewal ? 'subscription_renewal_scheduled' : 'subscription_activated'),
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name ?? 'Plan',
            'invoice_id' => $this->invoice?->id,
            'invoice_number' => $this->invoice?->invoice_number,
            'included_contents' => $includedContentTitles,
            'included_packages' => $includedPackageTitles,
            'message' => $this->isPaidCycleRenewal
                ? 'Paiement de renouvellement confirmé — période prolongée.'
                : ($this->isRenewal
                    ? 'Votre réabonnement est programmé pour la prochaine période.'
                    : 'Votre abonnement est confirmé.'),
            'action_url' => route('customer.subscriptions'),
            'action_text' => 'Voir mes abonnements',
        ];
    }

    private function resolveIncludedItems(): array
    {
        $this->subscription->loadMissing('plan.contents');
        $plan = $this->subscription->plan;
        if (! $plan) {
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
