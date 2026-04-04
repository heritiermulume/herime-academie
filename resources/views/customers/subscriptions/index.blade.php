@extends('customers.admin.layout')

@section('admin-title', 'Mes abonnements')
@section('admin-subtitle', 'Gérez vos formules, essais gratuits, renouvellements et factures.')

@section('admin-content')
@php
    $planTypeLabels = [
        'recurring' => 'Récurrent',
        'premium' => 'Premium',
        'one_time' => 'Achat unique',
        'freemium' => 'Freemium',
    ];

    $billingPeriodLabels = [
        'monthly' => 'Mensuel',
        'quarterly' => 'Trimestriel',
        'semiannual' => 'Semestriel',
        'yearly' => 'Annuel',
    ];

    $subscriptionStatusLabels = [
        'trialing' => 'Essai en cours',
        'active' => 'Actif',
        'pending_payment' => 'En attente de paiement',
        'past_due' => 'En retard de paiement',
        'cancelled' => 'Annulé',
        'expired' => 'Expiré',
    ];

    $invoiceStatusLabels = [
        'pending' => 'En attente',
        'paid' => 'Payée',
        'failed' => 'Échouée',
        'cancelled' => 'Annulée',
    ];

    $paymentMethodLabels = [
        'moneroo' => 'Moneroo',
    ];
@endphp
@php
    $currentSubscriptionsByPlan = $subscriptions
        ->filter(function ($subscription) {
            return in_array($subscription->status, ['trialing', 'active', 'pending_payment', 'past_due', 'cancelled'], true)
                && (!$subscription->ended_at || $subscription->ended_at->isFuture());
        })
        ->sortByDesc('created_at')
        ->keyBy('subscription_plan_id');
@endphp
<section class="admin-panel">
    <div class="admin-panel__header"><h3>Plans disponibles</h3></div>
    <div class="admin-panel__body">
        <div class="row g-3 subscription-plans-grid">
            @forelse($plans as $plan)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="admin-card h-100 subscription-plan-card">
                        <div class="admin-card__body">
                            @php
                                $includedPackageIds = collect(data_get($plan->metadata, 'included_package_ids', []))
                                    ->map(fn ($id) => (int) $id)
                                    ->filter()
                                    ->values();
                                $includedPackages = $includedPackageIds
                                    ->map(fn ($id) => $includedPackagesById[$id] ?? null)
                                    ->filter();
                            @endphp
                            @php($currentPlanSubscription = $currentSubscriptionsByPlan->get($plan->id))
                            <h5 class="fw-bold mb-1">{{ $plan->name }}</h5>
                            <p class="text-muted small mb-2">{{ $plan->description }}</p>
                            @if($currentPlanSubscription)
                                @if($currentPlanSubscription->status === 'pending_payment')
                                    <span class="subscription-state-label mb-2" style="color:#92400e;background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.35);">
                                        <i class="fas fa-clock me-1"></i>Paiement en attente — finalisez depuis vos factures ci-dessous
                                    </span>
                                @else
                                    <span class="subscription-state-label mb-2">
                                        <i class="fas fa-rotate me-1"></i>Déjà abonné - renouvellement auto
                                    </span>
                                @endif
                            @endif
                            <div class="mb-2 d-flex flex-wrap gap-1">
                                <span class="admin-badge">{{ $planTypeLabels[$plan->plan_type] ?? ucfirst((string) $plan->plan_type) }}</span>
                                @if($plan->billing_period)
                                    <span class="admin-badge">{{ $billingPeriodLabels[$plan->billing_period] ?? ucfirst((string) $plan->billing_period) }}</span>
                                @endif
                            </div>
                            @php($localizedAmount = $plan->effectivePriceForCurrency($preferredCurrency))
                            <p class="h4 mb-2">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($localizedAmount, $preferredCurrency) }}</p>
                            <p class="small text-muted mb-2">Devise du site: {{ $preferredCurrency }}</p>
                            @if($plan->plan_type === 'premium')
                                <p class="small text-muted mb-1">
                                    Abonnement récurrent : accès selon les formations et packs rattachés à ce plan (pas d’ouverture automatique de tout le catalogue).
                                </p>
                            @elseif($plan->contents->isNotEmpty())
                                <p class="small text-muted mb-1">
                                    Formations incluses: {{ $plan->contents->pluck('title')->take(2)->join(', ') }}@if($plan->contents->count() > 2) +{{ $plan->contents->count() - 2 }}@endif
                                </p>
                            @endif
                            @if($includedPackages->isNotEmpty())
                                <p class="small text-muted mb-2">
                                    Packs inclus: {{ $includedPackages->pluck('title')->take(2)->join(', ') }}@if($includedPackages->count() > 2) +{{ $includedPackages->count() - 2 }}@endif
                                </p>
                            @endif
                            @if($plan->trial_days > 0)
                                <p class="small text-success mb-2">{{ $plan->trial_days }} jours d'essai gratuit</p>
                            @endif
                            <form method="POST" action="{{ route('subscriptions.subscribe', $plan) }}">
                                @csrf
                                <button class="btn {{ $currentPlanSubscription ? 'btn-outline-primary' : 'btn-primary' }} w-100">
                                    {{ $currentPlanSubscription ? 'Réabonnement' : 'S\'abonner' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">Aucun plan actif pour le moment.</p>
            @endforelse
        </div>
    </div>
</section>

<section class="admin-panel">
    <div class="admin-panel__header"><h3>Mes abonnements en cours</h3></div>
    <div class="admin-panel__body">
        <div class="table-responsive admin-table">
            <table class="table align-middle mb-0">
                <thead><tr><th>Plan</th><th>Statut</th><th>Renouvellement</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td>{{ $subscription->plan->name ?? 'Plan supprimé' }}</td>
                            <td>{{ $subscriptionStatusLabels[$subscription->status] ?? $subscription->status }}</td>
                            <td>{{ optional($subscription->current_period_ends_at)->format('d/m/Y') ?: '-' }}</td>
                            <td class="d-flex gap-1">
                                @if($subscription->status === 'pending_payment')
                                    @php($pendingSubInvoice = $subscription->invoices->where('status', 'pending')->sortByDesc('id')->first())
                                    @if($pendingSubInvoice)
                                        <form method="POST" action="{{ route('subscriptions.invoices.pay', $pendingSubInvoice) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-primary">Payer</button>
                                        </form>
                                    @endif
                                @elseif(in_array($subscription->status, ['active','trialing']))
                                    <form method="POST" action="{{ route('subscriptions.cancel', $subscription) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">Annuler</button>
                                    </form>
                                @elseif($subscription->status === 'cancelled')
                                    <form method="POST" action="{{ route('subscriptions.resume', $subscription) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success">Réactiver</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Aucun abonnement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="admin-panel">
    <div class="admin-panel__header"><h3>Facturation récurrente</h3></div>
    <div class="admin-panel__body">
        <div class="table-responsive admin-table">
            <table class="table align-middle mb-0">
                <thead><tr><th>Facture</th><th>Montant</th><th>Échéance</th><th>Statut</th><th>Moyen</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ \App\Helpers\CurrencyHelper::formatWithSymbol($invoice->amount, $invoice->currency) }}</td>
                            <td>{{ optional($invoice->due_at)->format('d/m/Y H:i') ?: '-' }}</td>
                            <td>{{ $invoiceStatusLabels[$invoice->status] ?? $invoice->status }}</td>
                            <td>{{ $invoice->payment_method ? ($paymentMethodLabels[$invoice->payment_method] ?? strtoupper((string) $invoice->payment_method)) : '-' }}</td>
                            <td>
                                @if($invoice->status !== 'paid')
                                    <form method="POST" action="{{ route('subscriptions.invoices.pay', $invoice) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">Payer</button>
                                    </form>
                                @else
                                    <span class="text-success small fw-semibold">Reglee</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Aucune facture.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.admin-panel .admin-card {
    border-radius: 14px;
}

.admin-panel,
.admin-panel__body {
    max-width: 100%;
}

.subscription-plans-grid .col-12 {
    display: flex;
}

.subscription-plans-grid {
    width: 100%;
    margin-left: 0;
    margin-right: 0;
}

.admin-panel .admin-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    width: 100%;
}

.admin-panel .admin-card__body form {
    margin-top: auto;
}

.subscription-state-label {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    max-width: 100%;
    font-size: 0.76rem;
    font-weight: 600;
    color: #1d4ed8;
    background: rgba(59, 130, 246, 0.12);
    border: 1px solid rgba(59, 130, 246, 0.25);
    border-radius: 999px;
    padding: 0.28rem 0.62rem;
    line-height: 1.2;
    white-space: normal;
}

.subscription-plan-card .admin-card__body h5,
.subscription-plan-card .admin-card__body p {
    word-break: break-word;
    overflow-wrap: anywhere;
}

.admin-table .table td,
.admin-table .table th {
    vertical-align: middle;
}

@media (max-width: 991.98px) {
    .admin-panel .admin-panel__header h3 {
        font-size: 1rem;
    }

    .admin-panel .admin-card__body h5 {
        font-size: 1rem;
    }

    .admin-panel .admin-card__body .h4 {
        font-size: 1.15rem;
    }
}

@media (max-width: 767.98px) {
    .admin-panel__body {
        overflow-x: hidden;
    }

    .subscription-plans-grid {
        --bs-gutter-x: 0;
        margin-left: 0;
        margin-right: 0;
    }

    .subscription-plans-grid > [class*="col-"] {
        padding-left: 0;
        padding-right: 0;
    }

    .admin-panel {
        margin-bottom: 0.85rem;
    }

    .subscription-plans-grid .col-12 {
        padding-left: 0;
        padding-right: 0;
    }

    .subscription-plan-card {
        border-radius: 12px;
        width: 100%;
    }

    .subscription-plan-card .admin-card__body {
        padding: 0.8rem;
        gap: 0.3rem;
    }

    .subscription-plan-card .admin-card__body h5 {
        font-size: 0.98rem;
        line-height: 1.35;
    }

    .subscription-plan-card .admin-card__body .h4 {
        font-size: 1.05rem !important;
        margin-bottom: 0.35rem !important;
    }

    .subscription-plan-card .admin-badge {
        font-size: 0.68rem;
        padding: 0.28rem 0.5rem;
        border-radius: 999px;
    }

    .subscription-state-label {
        font-size: 0.7rem;
        padding: 0.24rem 0.5rem;
    }

    .subscription-plan-card .btn {
        min-height: 40px;
        font-size: 0.9rem;
    }

    .admin-panel .row.g-3 {
        --bs-gutter-x: 0.75rem;
        --bs-gutter-y: 0.75rem;
    }

    .admin-table table {
        min-width: 640px;
    }

    .admin-table .btn {
        white-space: nowrap;
    }
}
</style>
@endpush

