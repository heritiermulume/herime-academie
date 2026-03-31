@extends('layouts.admin')

@section('admin-title', 'Abonnements clients')
@section('admin-subtitle', 'Suivi des abonnements, renouvellements et factures récurrentes.')
@section('admin-actions')
    <a href="{{ route('admin.subscriptions.plans.index') }}" class="btn btn-outline-primary">
        <i class="fas fa-layer-group me-1"></i>Gérer les plans
    </a>
@endsection

@section('admin-content')
@php
    $subscriptionStatusLabels = [
        'trialing' => 'Essai en cours',
        'active' => 'Actif',
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
<section class="admin-panel">
    <div class="admin-panel__header"><h3>Abonnements actifs et historiques</h3></div>
    <div class="admin-panel__body">
        <div class="table-responsive admin-table">
            <table class="table align-middle mb-0">
                <thead><tr><th>Client</th><th>Plan</th><th>Statut</th><th>Période</th><th>Méthode</th></tr></thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td>{{ $sub->user->name }}</td>
                            <td>{{ $sub->plan->name ?? 'Plan supprimé' }}</td>
                            <td>{{ $subscriptionStatusLabels[$sub->status] ?? $sub->status }}</td>
                            <td>{{ optional($sub->current_period_starts_at)->format('d/m/Y') }} - {{ optional($sub->current_period_ends_at)->format('d/m/Y') }}</td>
                            <td>{{ $sub->payment_method ? ($paymentMethodLabels[$sub->payment_method] ?? strtoupper((string) $sub->payment_method)) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucun abonnement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $subscriptions->links() }}</div>
    </div>
</section>

<section class="admin-panel">
    <div class="admin-panel__header"><h3>Factures d'abonnement</h3></div>
    <div class="admin-panel__body">
        <div class="table-responsive admin-table">
            <table class="table align-middle mb-0">
                <thead><tr><th>Facture</th><th>Client</th><th>Montant</th><th>Statut</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->user->name ?? '-' }}</td>
                        <td>{{ \App\Helpers\CurrencyHelper::formatWithSymbol($invoice->amount, $invoice->currency) }}</td>
                        <td>{{ $invoiceStatusLabels[$invoice->status] ?? $invoice->status }}</td>
                        <td>
                            @if($invoice->status !== 'paid')
                                <form method="POST" action="{{ route('admin.subscriptions.invoices.mark-paid', $invoice) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-success">Marquer payée</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune facture.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

