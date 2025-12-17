@extends('ambassadors.admin.layout')

@section('admin-title', 'Historique des transactions')
@section('admin-subtitle', 'Consultez toutes vos transactions wallet')

@section('admin-content')
<div class="transactions-page">
    {{-- Filtres --}}
    <div class="filters-card">
        <form method="GET" action="{{ route('wallet.transactions') }}" class="filters-form">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="type" class="form-label">Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Crédit</option>
                        <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Débit</option>
                        <option value="commission" {{ request('type') == 'commission' ? 'selected' : '' }}>Commission</option>
                        <option value="payout" {{ request('type') == 'payout' ? 'selected' : '' }}>Retrait</option>
                        <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>Remboursement</option>
                        <option value="bonus" {{ request('type') == 'bonus' ? 'selected' : '' }}>Bonus</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">Statut</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Complété</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échoué</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="from" class="form-label">Du</label>
                    <input type="date" name="from" id="from" class="form-control" value="{{ request('from') }}">
                </div>

                <div class="col-md-2">
                    <label for="to" class="form-label">Au</label>
                    <input type="date" name="to" id="to" class="form-control" value="{{ request('to') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
            </div>

            @if(request()->hasAny(['type', 'status', 'from', 'to']))
            <div class="mt-3">
                <a href="{{ route('wallet.transactions') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-2"></i>Réinitialiser les filtres
                </a>
            </div>
            @endif
        </form>
    </div>

    {{-- Solde actuel --}}
    <div class="balance-summary">
        <div class="balance-info">
            <span class="balance-label">Solde actuel</span>
            <span class="balance-value">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</span>
        </div>
        <a href="{{ route('wallet.index') }}" class="btn btn-outline-custom">
            <i class="fas fa-arrow-left me-2"></i>Retour au wallet
        </a>
    </div>

    {{-- Liste des transactions --}}
    <div class="transactions-card">
        @if($transactions->count() > 0)
        <div class="table-responsive">
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Référence</th>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>Solde avant</th>
                        <th>Solde après</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td class="transaction-date">
                            <div>{{ $transaction->created_at->translatedFormat('d M Y') }}</div>
                            <small class="text-muted">{{ $transaction->created_at->translatedFormat('H:i') }}</small>
                        </td>
                        <td>
                            <span class="transaction-type-badge">
                                <i class="{{ $transaction->icon }}"></i>
                                {{ $transaction->type_label }}
                            </span>
                        </td>
                        <td class="transaction-reference">
                            <code>{{ $transaction->reference ?? '-' }}</code>
                        </td>
                        <td class="transaction-description">
                            {{ $transaction->description ?? '-' }}
                        </td>
                        <td>
                            <span class="transaction-amount {{ $transaction->isCredit() ? 'credit' : 'debit' }}">
                                {{ $transaction->isCredit() ? '+' : '-' }}{{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}
                            </span>
                        </td>
                        <td>{{ number_format($transaction->balance_before, 2) }} {{ $transaction->currency }}</td>
                        <td>{{ number_format($transaction->balance_after, 2) }} {{ $transaction->currency }}</td>
                        <td>{!! $transaction->status_badge !!}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrapper">
            {{ $transactions->appends(request()->query())->links() }}
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-inbox fa-3x"></i>
            <p>Aucune transaction trouvée</p>
            @if(request()->hasAny(['type', 'status', 'from', 'to']))
            <a href="{{ route('wallet.transactions') }}" class="btn btn-outline-primary mt-3">
                Réinitialiser les filtres
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.transactions-page {
    padding: 0;
}

.filters-card,
.balance-summary,
.transactions-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.filters-form .form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.filters-form .form-select,
.filters-form .form-control {
    border-radius: 8px;
    border: 1px solid #d1d5db;
}

.balance-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.balance-info {
    display: flex;
    flex-direction: column;
}

.balance-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.balance-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
}

.btn-outline-custom {
    background: white;
    color: #374151;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    border: 2px solid #d1d5db;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-outline-custom:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.table-responsive {
    overflow-x: auto;
}

.transactions-table {
    width: 100%;
    border-collapse: collapse;
}

.transactions-table thead th {
    background: #f9fafb;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
    white-space: nowrap;
}

.transactions-table tbody td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.transaction-date {
    white-space: nowrap;
}

.transaction-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    background: #f3f4f6;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    white-space: nowrap;
}

.transaction-reference code {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8125rem;
    color: #6b7280;
}

.transaction-description {
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.transaction-amount {
    font-weight: 700;
    font-size: 1rem;
    white-space: nowrap;
}

.transaction-amount.credit {
    color: #10b981;
}

.transaction-amount.debit {
    color: #ef4444;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #9ca3af;
}

.empty-state i {
    margin-bottom: 1rem;
}

.pagination-wrapper {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

@media (max-width: 768px) {
    .balance-summary {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .transactions-table {
        font-size: 0.875rem;
    }

    .transactions-table thead th,
    .transactions-table tbody td {
        padding: 0.75rem 0.5rem;
    }

    .transaction-description {
        max-width: 150px;
    }
}
</style>
@endpush

