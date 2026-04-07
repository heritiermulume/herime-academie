@extends('customers.admin.layout')

@section('admin-title', 'Mes abonnements')
@section('admin-subtitle', 'Adhésion Membre Herime : formules, renouvellements et factures.')

@section('admin-content')
@php
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
    $paymentMethodLabels = ['moneroo' => 'Moneroo'];

    $currentSubscriptionsByPlan = $subscriptions
        ->filter(fn ($s) => in_array($s->status, ['trialing', 'active', 'pending_payment', 'past_due', 'cancelled'], true)
            && (!$s->ended_at || $s->ended_at->isFuture()))
        ->sortByDesc('created_at')
        ->keyBy('subscription_plan_id');

    $plansByPeriod = $plans->keyBy('billing_period');
    $defaultPremiumPlan = $plansByPeriod->get('yearly') ?? $plans->first();
    $periodUiOrder = [
        'quarterly' => ['label' => 'Trimestre', 'hint' => '3 mois'],
        'semiannual' => ['label' => 'Semestre', 'hint' => '6 mois'],
        'yearly' => ['label' => 'Annuel', 'hint' => '12 mois'],
    ];
    $premiumPlansPayload = ['plans' => [], 'defaultSlug' => $defaultPremiumPlan?->slug];
    foreach ($plans as $plan) {
        $planSub = $currentSubscriptionsByPlan->get($plan->id);
        $premiumPlansPayload['plans'][$plan->slug] = [
            'subscribe_url' => route('subscriptions.subscribe', $plan),
            'price_formatted' => \App\Helpers\CurrencyHelper::formatWithSymbol($plan->effectivePriceForCurrency($preferredCurrency), $preferredCurrency),
            'renewal_caption' => match ($plan->billing_period) {
                'quarterly' => 'Renouvellement tous les 3 mois',
                'semiannual' => 'Renouvellement tous les 6 mois',
                'yearly' => 'Renouvellement annuel',
                default => 'Facturation '.mb_strtolower($billingPeriodLabels[$plan->billing_period] ?? (string) $plan->billing_period),
            },
            'name' => $plan->name,
            'description' => $plan->description,
            'trial_days' => (int) ($plan->trial_days ?? 0),
            'user_subscription' => $planSub?->asCommunityPremiumCardSubscription(),
        ];
    }
@endphp

<section class="admin-panel">
    <div class="admin-panel__header"><h3>Formules Membre Herime</h3></div>
    <div class="admin-panel__body">
        @if($defaultPremiumPlan)
            <div class="mp-subscribe-zone">
                <article class="mp-card">
                    <div class="mp-card__inner">
                        <span class="mp-card__badge"><i class="fas fa-gem me-1"></i>Adhésion premium</span>
                        <h3 class="mp-card__title">Membre Herime</h3>
                        <p class="mp-card__subtitle">Communauté privée, formations, réseau, lives et ressources premium.</p>

                        <div class="mp-period mt-3">
                            @foreach($periodUiOrder as $periodKey => $meta)
                                @php
                                    $segPlan = $plansByPeriod->get($periodKey);
                                @endphp
                                @if($segPlan)
                                    <button type="button" class="mp-period__btn" data-premium-slug="{{ $segPlan->slug }}" aria-selected="false">
                                        <span class="mp-period__label">{{ $meta['label'] }}</span>
                                        <span class="mp-period__hint">{{ $meta['hint'] }}</span>
                                    </button>
                                @else
                                    <button type="button" class="mp-period__btn" disabled aria-selected="false">
                                        <span class="mp-period__label">{{ $meta['label'] }}</span>
                                        <span class="mp-period__hint">Bientôt</span>
                                    </button>
                                @endif
                            @endforeach
                        </div>

                        <div class="mp-price-block">
                            <div id="customer-premium-price" class="mp-price">
                                {{ \App\Helpers\CurrencyHelper::formatWithSymbol($defaultPremiumPlan->effectivePriceForCurrency($preferredCurrency), $preferredCurrency) }}
                            </div>
                            <p id="customer-premium-billing-line" class="mp-price-suffix mb-0">
                                {{ data_get($premiumPlansPayload, 'plans.'.$defaultPremiumPlan->slug.'.renewal_caption', '') }}
                            </p>
                        </div>

                        <div class="mp-plan-meta text-center">
                            <h4 id="customer-premium-plan-name">{{ $defaultPremiumPlan->name }}</h4>
                            <p id="customer-premium-desc" class="{{ $defaultPremiumPlan->description ? '' : 'd-none' }}">{{ $defaultPremiumPlan->description }}</p>
                            @php
                                $defaultTrialDays = (int) ($defaultPremiumPlan->trial_days ?? 0);
                            @endphp
                            <p id="customer-premium-trial" class="small text-success mb-0 mt-2 fw-semibold {{ $defaultTrialDays > 0 ? '' : 'd-none' }}">
                                @if($defaultTrialDays === 1)
                                    1 jour d'essai gratuit
                                @elseif($defaultTrialDays > 1)
                                    {{ $defaultTrialDays }} jours d'essai gratuit
                                @endif
                            </p>
                        </div>

                        <form id="customer-premium-subscribe-form" method="POST" action="{{ route('subscriptions.subscribe', $defaultPremiumPlan) }}">
                            @csrf
                            <input type="hidden" name="redirect_after_subscribe" value="customer.subscriptions">
                            <button type="submit" class="mp-cta w-100">
                                <i class="fas fa-shield-alt me-2"></i><span id="customer-premium-submit-label">Procéder au paiement</span>
                            </button>
                        </form>
                    </div>
                </article>
            </div>
            <script type="application/json" id="customer-premium-plans-json">{!! json_encode($premiumPlansPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
            <script>
                (function () {
                    var root = document.getElementById('customer-premium-plans-json');
                    if (!root) return;
                    var payload;
                    try { payload = JSON.parse(root.textContent || '{}'); } catch (e) { return; }
                    var plans = payload.plans || {};
                    var buttons = document.querySelectorAll('.mp-period__btn');
                    var form = document.getElementById('customer-premium-subscribe-form');
                    var nameEl = document.getElementById('customer-premium-plan-name');
                    var descEl = document.getElementById('customer-premium-desc');
                    var trialEl = document.getElementById('customer-premium-trial');
                    var priceEl = document.getElementById('customer-premium-price');
                    var billEl = document.getElementById('customer-premium-billing-line');
                    var submitLabel = document.getElementById('customer-premium-submit-label');

                    function trialDaysLabel(n) {
                        var td = parseInt(n, 10);
                        if (!td || td < 1) return '';
                        return td === 1 ? "1 jour d'essai gratuit" : td + " jours d'essai gratuit";
                    }

                    function wantsResubscribePrimaryLabel(us) {
                        if (!us) return false;
                        if (typeof us.use_resubscribe_primary_label === 'boolean') return us.use_resubscribe_primary_label;
                        return us.status !== 'pending_payment' && us.status !== 'cancelled';
                    }

                    function applyPlan(slug) {
                        var data = plans[slug];
                        if (!data) return;
                        if (form && data.subscribe_url) form.action = data.subscribe_url;
                        if (priceEl) priceEl.textContent = data.price_formatted || '';
                        if (billEl) billEl.textContent = data.renewal_caption || '';
                        if (nameEl) nameEl.textContent = data.name || '';
                        if (descEl) {
                            descEl.textContent = data.description || '';
                            descEl.classList.toggle('d-none', !data.description);
                        }
                        var trialLabel = trialDaysLabel(data.trial_days || 0);
                        if (trialEl) {
                            trialEl.textContent = trialLabel;
                            trialEl.classList.toggle('d-none', !trialLabel);
                        }
                        if (submitLabel) {
                            submitLabel.textContent = wantsResubscribePrimaryLabel(data.user_subscription || null)
                                ? 'Réabonnement'
                                : 'Procéder au paiement';
                        }
                        buttons.forEach(function (btn) {
                            var active = btn.getAttribute('data-premium-slug') === slug;
                            btn.classList.toggle('is-active', active);
                            btn.setAttribute('aria-selected', active ? 'true' : 'false');
                        });
                    }

                    buttons.forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var slug = btn.getAttribute('data-premium-slug');
                            if (slug) applyPlan(slug);
                        });
                    });

                    var defaultSlug = payload.defaultSlug || Object.keys(plans)[0];
                    if (defaultSlug) applyPlan(defaultSlug);
                })();
            </script>
        @else
            <p class="text-muted mb-0">Aucun plan actif pour le moment.</p>
        @endif
    </div>
</section>

<section class="admin-panel">
    <div class="admin-panel__header"><h3>Mes abonnements en cours</h3></div>
    <div class="admin-panel__body">
        <div class="table-responsive admin-table">
            <table class="table align-middle mb-0 subscriptions-current-table">
                <thead><tr><th class="plan-col">Plan</th><th>Statut</th><th>Date d'abonnement</th><th>Date d'expiration</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        @php
                            $pendingSubInvoice = $subscription->invoices->where('status', 'pending')->sortByDesc('id')->first();
                            $canPayCurrentSubscriptionInvoice = in_array($subscription->status, ['pending_payment', 'past_due'], true)
                                && $pendingSubInvoice
                                && (float) $pendingSubInvoice->amount > 0;
                            $canCancelSubscription = in_array($subscription->status, ['active', 'trialing', 'past_due', 'pending_payment'], true);
                            $canResumeSubscription = $subscription->status === 'cancelled';
                        @endphp
                        <tr>
                            <td class="plan-col" data-label="Plan">{{ $subscription->plan->name ?? 'Plan supprimé' }}</td>
                            <td data-label="Statut">{{ $subscriptionStatusLabels[$subscription->status] ?? $subscription->status }}</td>
                            <td data-label="Date d'abonnement">{{ optional($subscription->starts_at)->format('d/m/Y') ?: optional($subscription->created_at)->format('d/m/Y') ?: '-' }}</td>
                            <td data-label="Date d'expiration">{{ optional($subscription->ended_at)->format('d/m/Y') ?: optional($subscription->current_period_ends_at)->format('d/m/Y') ?: '-' }}</td>
                            <td class="d-flex flex-wrap gap-1" data-label="Actions">
                                @if($canPayCurrentSubscriptionInvoice)
                                    <form method="POST" action="{{ route('subscriptions.invoices.pay', $pendingSubInvoice) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">Procéder au paiement</button>
                                    </form>
                                @endif
                                @if($canCancelSubscription)
                                    <form method="POST" action="{{ route('subscriptions.cancel', $subscription) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">Résilier l'abonnement</button>
                                    </form>
                                @elseif($canResumeSubscription)
                                    <form method="POST" action="{{ route('subscriptions.resume', $subscription) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success">Réactiver le renouvellement auto</button>
                                    </form>
                                @endif
                                @if(! $canPayCurrentSubscriptionInvoice && ! $canCancelSubscription && ! $canResumeSubscription)
                                    <span class="text-muted small fw-semibold">Aucune action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucun abonnement.</td></tr>
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
                        @php
                            $canPayInvoice = $invoice->status === 'pending' && (float) $invoice->amount > 0;
                        @endphp
                        <tr>
                            <td data-label="Facture">{{ $invoice->invoice_number }}</td>
                            <td data-label="Montant">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($invoice->amount, $invoice->currency) }}</td>
                            <td data-label="Échéance">{{ optional($invoice->due_at)->format('d/m/Y H:i') ?: '-' }}</td>
                            <td data-label="Statut">{{ $invoiceStatusLabels[$invoice->status] ?? $invoice->status }}</td>
                            <td data-label="Moyen">{{ $invoice->payment_method ? ($paymentMethodLabels[$invoice->payment_method] ?? strtoupper((string) $invoice->payment_method)) : '-' }}</td>
                            <td data-label="Action">
                                @if($canPayInvoice)
                                    <form method="POST" action="{{ route('subscriptions.invoices.pay', $invoice) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">Payer</button>
                                    </form>
                                @else
                                    <span class="text-muted small fw-semibold">Aucune action</span>
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
.mp-subscribe-zone { max-width: 560px; margin: 0 auto; }
.mp-card { border-radius: 1.25rem; background: linear-gradient(145deg, #fff, #f8fafc); box-shadow: 0 0 0 1px rgba(15,23,42,.06), 0 20px 45px -14px rgba(0,51,102,.2); }
.mp-card__inner { padding: 1.5rem; }
.mp-card__badge { display: inline-flex; align-items: center; font-size: .72rem; font-weight: 700; color: #003366; background: rgba(0,51,102,.08); border: 1px solid rgba(0,51,102,.15); border-radius: 999px; padding: .3rem .6rem; margin-bottom: .7rem; }
.mp-card__title { font-size: 1.4rem; font-weight: 800; margin: 0 0 .25rem; }
.mp-card__subtitle { color: #64748b; margin-bottom: 1rem; }
.mp-period { display: grid; grid-template-columns: repeat(3,1fr); gap: .35rem; background: #0f172a; border-radius: .8rem; padding: .35rem; margin-bottom: 1rem; }
.mp-period__btn { border: none; border-radius: .6rem; padding: .55rem .3rem; background: transparent; color: rgba(255,255,255,.65); display: flex; flex-direction: column; align-items: center; gap: .08rem; }
.mp-period__btn.is-active { background: #fff; color: #0f172a; }
.mp-period__label { font-weight: 800; }
.mp-period__hint { font-size: .7rem; opacity: .85; }
.mp-price-block { text-align: center; margin-bottom: .8rem; }
.mp-price { font-size: 1.9rem; font-weight: 800; color: #003366; line-height: 1.1; }
.mp-price-suffix { color: #64748b; font-size: .85rem; }
.mp-plan-meta h4 { margin-bottom: .2rem; }
.mp-plan-meta p { color: #64748b; }
.mp-cta { border: none; border-radius: .75rem; background: linear-gradient(90deg,#003366,#0b5ed7); color: #fff; font-weight: 700; padding: .78rem 1rem; display: inline-flex; justify-content: center; align-items: center; }
.admin-table .table td, .admin-table .table th { vertical-align: middle; }
.admin-table .table th,
.admin-table .table td { padding-left: .9rem; padding-right: .9rem; }
.subscriptions-current-table .plan-col { min-width: 260px; width: 34%; }

@media (max-width: 576px) {
    .mp-subscribe-zone { max-width: 100%; }
    .mp-card__inner { padding: 1rem; }
    .mp-card__title { font-size: 1.2rem; }
    .mp-card__subtitle { font-size: .92rem; margin-bottom: .8rem; }
    .mp-period { gap: .25rem; padding: .25rem; }
    .mp-period__btn { padding: .5rem .2rem; }
    .mp-period__label { font-size: .84rem; line-height: 1.1; }
    .mp-period__hint { font-size: .68rem; line-height: 1.1; }
    .mp-price { font-size: 1.55rem; }
    .mp-price-suffix { font-size: .8rem; }
    .mp-plan-meta h4 { font-size: 1rem; }
    .mp-plan-meta p { font-size: .88rem; }
    .mp-cta {
        white-space: normal;
        text-align: center;
        line-height: 1.25;
        min-height: 46px;
    }

    .admin-table .table {
        min-width: 0 !important;
    }

    .admin-table .table thead {
        display: none;
    }

    .admin-table .table tbody tr {
        display: block;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: .8rem;
        padding: .55rem .7rem;
        margin-bottom: .6rem;
        background: #fff;
    }

    .admin-table .table tbody td {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: .75rem;
        border: 0;
        padding: .35rem 0;
        white-space: normal;
        text-align: right;
    }

    .admin-table .table tbody td::before {
        content: attr(data-label);
        font-weight: 700;
        color: #334155;
        text-align: left;
        flex: 0 0 48%;
    }

    .admin-table .table tbody td[data-label="Actions"],
    .admin-table .table tbody td[data-label="Action"] {
        display: block;
        text-align: left;
        padding-top: .55rem;
    }

    .admin-table .table tbody td[data-label="Actions"]::before,
    .admin-table .table tbody td[data-label="Action"]::before {
        content: attr(data-label);
        display: block;
        margin-bottom: .4rem;
    }

    .admin-table .table tbody td[data-label="Actions"] form,
    .admin-table .table tbody td[data-label="Action"] form {
        width: 100%;
    }

    .admin-table .table tbody td[data-label="Actions"] .btn,
    .admin-table .table tbody td[data-label="Action"] .btn {
        width: 100%;
    }

    .admin-table .table tbody tr td[colspan] {
        display: block;
        text-align: center;
        padding: .35rem 0;
    }

    .admin-table .table tbody tr td[colspan]::before {
        content: none;
    }
}
</style>
@endpush

