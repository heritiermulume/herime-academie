<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\ContentPackage;
use App\Models\SubscriptionInvoice;
use App\Models\UserSubscription;
use App\Support\EmailBranding;
use App\Support\RecipientDisplayName;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminSubscriptionActivated extends Notification
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
        $user = $this->subscription->user;
        $planName = $this->subscription->plan->name ?? 'Plan';

        $subject = $this->isPaidCycleRenewal
            ? 'Renouvellement payé (client) — '.config('app.name')
            : ($this->isRenewal ? 'Réabonnement client — '.config('app.name') : 'Nouvel abonnement client — '.config('app.name'));
        $lead = $this->isPaidCycleRenewal
            ? 'Un client a payé une facture de renouvellement ; la période d’abonnement a été prolongée.'
            : ($this->isRenewal
                ? 'Un client a programmé un réabonnement.'
                : 'Un client vient de souscrire un abonnement.');

        $detailLines = [
            ['label' => 'Client', 'value' => ($user->name ?? 'N/A').($user?->email ? ' ('.$user->email.')' : '')],
            ['label' => 'Plan', 'value' => $planName],
            ['label' => 'Période en cours', 'value' => optional($this->subscription->current_period_starts_at)->format('d/m/Y').' - '.optional($this->subscription->current_period_ends_at)->format('d/m/Y')],
        ];

        if ($this->invoice) {
            $invoiceStatusLabel = $this->invoice->status === 'paid' ? 'Payée' : 'En attente de paiement';
            $detailLines[] = ['label' => 'Facture', 'value' => $this->invoice->invoice_number];
            $detailLines[] = ['label' => 'Montant', 'value' => CurrencyHelper::formatWithSymbol($this->invoice->amount, $this->invoice->currency)];
            $detailLines[] = ['label' => 'Statut', 'value' => $invoiceStatusLabel];
        }

        $extraParagraphs = [];
        if (! empty($includedContentTitles)) {
            $extraParagraphs[] = '<strong>Formations incluses :</strong> '.collect($includedContentTitles)->take(3)->join(', ')
                .(count($includedContentTitles) > 3 ? ' +'.(count($includedContentTitles) - 3) : '');
        }
        if (! empty($includedPackageTitles)) {
            $extraParagraphs[] = '<strong>Packs inclus :</strong> '.collect($includedPackageTitles)->take(3)->join(', ')
                .(count($includedPackageTitles) > 3 ? ' +'.(count($includedPackageTitles) - 3) : '');
        }

        return (new MailMessage)
            ->subject($subject.' [Admin]')
            ->view('emails.admin-subscription-event', [
                'logoUrl' => EmailBranding::logoUrl(),
                'title' => $this->isPaidCycleRenewal ? 'Paiement d\'abonnement reçu' : ($this->isRenewal ? 'Réabonnement client' : 'Nouvel abonnement client'),
                'adminName' => RecipientDisplayName::resolve($notifiable->name ?? null, $notifiable->email ?? null),
                'intro' => $lead,
                'detailsTitle' => 'Détails de l’abonnement',
                'detailLines' => $detailLines,
                'extraParagraphs' => $extraParagraphs,
                'actionUrl' => route('admin.subscriptions.index'),
                'actionLabel' => 'Voir les abonnements admin',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        [$includedContentTitles, $includedPackageTitles] = $this->resolveIncludedItems();
        $user = $this->subscription->user;

        return [
            'type' => $this->isPaidCycleRenewal
                ? 'admin_subscription_paid_renewal'
                : ($this->isRenewal ? 'admin_subscription_renewal_scheduled' : 'admin_subscription_created'),
            'subscription_id' => $this->subscription->id,
            'invoice_id' => $this->invoice?->id,
            'invoice_number' => $this->invoice?->invoice_number,
            'customer_name' => $user->name ?? 'N/A',
            'customer_email' => $user->email ?? null,
            'plan_name' => $this->subscription->plan->name ?? 'Plan',
            'included_contents' => $includedContentTitles,
            'included_packages' => $includedPackageTitles,
            'message' => $this->isPaidCycleRenewal
                ? 'Renouvellement payé : '.($user->name ?? 'un client').'.'
                : ($this->isRenewal
                    ? 'Réabonnement programmé par '.($user->name ?? 'un client').'.'
                    : 'Nouvel abonnement créé par '.($user->name ?? 'un client').'.'),
            'button_text' => 'Voir les abonnements',
            'button_url' => route('admin.subscriptions.index'),
            'url' => route('admin.subscriptions.index'),
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
