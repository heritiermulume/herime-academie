@extends('layouts.admin')

@section('admin-title', 'Plans d\'abonnement')
@section('admin-subtitle', 'Créez des offres mensuelles, annuelles, freemium et achat unique.')
@section('admin-actions')
    <a href="{{ route('admin.subscriptions.plans.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouveau plan
    </a>
@endsection

@section('admin-content')
@php
    $planTypeLabels = [
        'recurring' => 'Récurrent',
        'one_time' => 'Achat unique',
        'freemium' => 'Freemium',
    ];

    $billingPeriodLabels = [
        'monthly' => 'Mensuel',
        'yearly' => 'Annuel',
    ];
@endphp
<section class="admin-panel">
    <div class="admin-panel__body">
        <div class="table-responsive admin-table">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Type</th>
                        <th>Période</th>
                        <th>Prix</th>
                        <th>État</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        @php
                            $includedPackageIds = collect(data_get($plan->metadata, 'included_package_ids', []))
                                ->map(fn ($id) => (int) $id)
                                ->filter()
                                ->values();
                            $includedPackages = $includedPackageIds
                                ->map(fn ($id) => $includedPackagesById[$id] ?? null)
                                ->filter();
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $plan->name }}</div>
                                @if($plan->contents->isNotEmpty())
                                    <small class="text-muted">
                                        Formations: {{ $plan->contents->pluck('title')->take(2)->join(', ') }}@if($plan->contents->count() > 2) +{{ $plan->contents->count() - 2 }}@endif
                                    </small>
                                @elseif($plan->content)
                                    <small class="text-muted">Formation: {{ $plan->content->title }}</small>
                                @endif
                                @if($includedPackages->isNotEmpty())
                                    <small class="text-muted d-block">
                                        Packs: {{ $includedPackages->pluck('title')->take(2)->join(', ') }}@if($includedPackages->count() > 2) +{{ $includedPackages->count() - 2 }}@endif
                                    </small>
                                @endif
                            </td>
                            <td>{{ $planTypeLabels[$plan->plan_type] ?? ucfirst((string) $plan->plan_type) }}</td>
                            <td>{{ $plan->billing_period ? ($billingPeriodLabels[$plan->billing_period] ?? ucfirst((string) $plan->billing_period)) : '-' }}</td>
                            <td>{{ \App\Helpers\CurrencyHelper::formatWithSymbol($plan->effective_price) }}</td>
                            <td>
                                <span class="admin-chip {{ $plan->is_active ? 'admin-chip--success' : 'admin-chip--danger' }}">
                                    {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.subscriptions.plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                <form method="POST" action="{{ route('admin.subscriptions.plans.destroy', $plan) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce plan ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucun plan défini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $plans->links() }}</div>
    </div>
</section>
@endsection

