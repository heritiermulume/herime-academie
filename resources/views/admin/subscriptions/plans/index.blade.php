@extends('layouts.admin')

@section('admin-title', 'Abonnement Membre Herime')
@section('admin-subtitle', 'Les trois formules (trimestre, semestre, année) affichées sur la page communauté et dans l’espace client.')
@section('admin-actions')
    @if($hasAnyMemberPlan ?? false)
        <span class="btn btn-primary disabled opacity-75" role="button" tabindex="-1"
              title="L’offre existe déjà. Utilisez « Modifier » sur une ligne pour éditer les trois périodes.">
            <i class="fas fa-plus me-1"></i>Créer l’offre Membre Herime
        </span>
    @else
        <a href="{{ route('admin.subscriptions.plans.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Créer l’offre Membre Herime
        </a>
    @endif
@endsection

@section('admin-content')
@if(!($hasAnyMemberPlan ?? false))
    <section class="admin-panel">
        <div class="admin-panel__body text-center text-muted py-5">
            <p class="mb-0">Aucune offre Membre Herime n’est configurée.</p>
        </div>
    </section>
@else
    @php
        $bundleRows = $plans->getCollection();
        $anchorPlan = $bundleRows->first(fn ($r) => $r['plan'] !== null)['plan'] ?? null;
        $destroyPlan = $anchorPlan;
        $includedPackageIds = $anchorPlan
            ? collect(data_get($anchorPlan->metadata, 'included_package_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values()
            : collect();
        $includedPackages = $includedPackageIds
            ->map(fn ($id) => $includedPackagesById[$id] ?? null)
            ->filter();
    @endphp
    <section class="admin-panel admin-member-bundle">
        <div class="admin-panel__body p-0">
        <div class="admin-member-bundle__shell border rounded-3 overflow-hidden bg-white shadow-sm">
            <div class="admin-member-bundle__header px-3 px-md-4 py-3 py-md-4 border-bottom"
                 style="background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%); border-left: 4px solid #003366 !important;">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h2 class="h5 mb-0 fw-bold">{{ $anchorPlan ? $anchorPlan->memberOfferBaseName() : 'Membre Herime' }}</h2>
                            <span class="badge rounded-pill" style="background:#003366;">Offre unique · 3 périodes</span>
                        </div>
                        <p class="text-muted small mb-2 mb-md-3">Les lignes ci-dessous sont les options de facturation d’une même adhésion (page communauté et espace client).</p>
                        @if($anchorPlan)
                            @if($anchorPlan->contents->isNotEmpty())
                                <p class="small text-muted mb-1">
                                    <span class="text-secondary fw-semibold">Formations liées :</span>
                                    {{ $anchorPlan->contents->pluck('title')->take(3)->join(', ') }}@if($anchorPlan->contents->count() > 3) +{{ $anchorPlan->contents->count() - 3 }}@endif
                                </p>
                            @elseif($anchorPlan->content)
                                <p class="small text-muted mb-1">
                                    <span class="text-secondary fw-semibold">Formation :</span> {{ $anchorPlan->content->title }}
                                </p>
                            @endif
                            @if($includedPackages->isNotEmpty())
                                <p class="small text-muted mb-0">
                                    <span class="text-secondary fw-semibold">Packs :</span>
                                    {{ $includedPackages->pluck('title')->take(3)->join(', ') }}@if($includedPackages->count() > 3) +{{ $includedPackages->count() - 3 }}@endif
                                </p>
                            @endif
                        @endif
                    </div>
                    <div class="d-flex flex-nowrap gap-2 align-items-center flex-shrink-0 admin-member-bundle__actions">
                        <a href="{{ route('admin.subscriptions.plans.membre.edit') }}" class="btn btn-primary btn-sm text-nowrap flex-shrink-0">
                            <i class="fas fa-edit me-1"></i>Modifier l’offre
                        </a>
                        @if($destroyPlan)
                            <form method="POST" action="{{ route('admin.subscriptions.plans.destroy', $destroyPlan) }}" class="d-inline-flex align-items-center flex-shrink-0 m-0 js-subscription-plan-delete-form"
                                  data-confirm-title="Supprimer l’offre Membre Herime"
                                  data-confirm-message="Les trois formules (trimestre, semestre et année) seront supprimées. Cette action est irréversible.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm text-nowrap" title="Supprimer toute l’offre">
                                    <i class="fas fa-trash me-1"></i>Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            <div class="admin-member-bundle__periods px-2 px-md-3 py-2">
                <div class="table-responsive admin-table rounded-2 border">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Période de facturation</th>
                                <th>Prix</th>
                                <th>État</th>
                                <th class="d-none d-md-table-cell text-nowrap">Essai (j.)</th>
                                <th class="d-none d-md-table-cell">Affichage carte</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bundleRows as $row)
                                @php $plan = $row['plan']; @endphp
                                <tr class="admin-member-bundle__period-row">
                                    <td class="ps-3">
                                        <span class="fw-semibold">{{ $row['period_label'] }}</span>
                                        @if($plan && $plan->isCommunityCardPopular())
                                            <span class="badge ms-1" style="background:#f59e0b; color:#1a1a1a;">Populaire</span>
                                        @endif
                                        @if($plan && (int) $plan->trial_days > 0)
                                            <small class="text-muted d-md-none d-block mt-1">{{ (int) $plan->trial_days }} j. d’essai</small>
                                        @endif
                                        @if($plan && filled(data_get($plan->metadata, 'community_card_highlight')))
                                            <small class="text-muted d-md-none d-block mt-1">{{ Str::limit((string) data_get($plan->metadata, 'community_card_highlight'), 100) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($plan)
                                            <span class="tabular-nums">{{ \App\Helpers\CurrencyHelper::formatWithSymbol((float) $plan->price) }}</span>
                                            @if($plan->billing_period === 'yearly' && (float) $plan->annual_discount_percent > 0)
                                                <br><small class="text-muted">Facturé (remise {{ (float) $plan->annual_discount_percent }}%) : {{ \App\Helpers\CurrencyHelper::formatWithSymbol($plan->effective_price) }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">Non configuré</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($plan)
                                            <span class="admin-chip {{ $plan->is_active ? 'admin-chip--success' : 'admin-chip--danger' }}">
                                                {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell tabular-nums">
                                        @if($plan)
                                            @if((int) $plan->trial_days > 0)
                                                {{ (int) $plan->trial_days }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        @if($plan && filled(data_get($plan->metadata, 'community_card_highlight')))
                                            <small class="text-muted">{{ Str::limit((string) data_get($plan->metadata, 'community_card_highlight'), 120) }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>
        <div class="mt-3">{{ $plans->links() }}</div>
    </section>
@endif
@endsection

@push('styles')
<style>
    @media (max-width: 767.98px) {
        .admin-member-bundle__actions {
            display: flex !important;
            flex-flow: row nowrap !important;
            align-items: center !important;
            gap: 0.5rem !important;
            width: auto !important;
            max-width: 100%;
        }
        .admin-member-bundle__actions > a.btn,
        .admin-member-bundle__actions > form {
            flex: 0 0 auto !important;
            width: auto !important;
        }
        .admin-member-bundle__actions .btn {
            width: auto !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-subscription-plan-delete-form').forEach(function (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            var message = form.getAttribute('data-confirm-message') || 'Confirmer la suppression ?';
            var title = form.getAttribute('data-confirm-title') || 'Confirmation';
            if (typeof showModernConfirmModal !== 'function') {
                if (confirm(message)) {
                    form.submit();
                }
                return;
            }
            var confirmed = await showModernConfirmModal(message, {
                title: title,
                confirmButtonText: 'Supprimer',
                confirmButtonClass: 'btn-danger',
                icon: 'fa-trash-alt'
            });
            if (confirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
