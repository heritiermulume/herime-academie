@extends('ambassadors.admin.layout')

@section('admin-title', 'Mon Wallet')
@section('admin-subtitle', 'Gérez vos gains et effectuez des retraits')

@section('admin-content')
<div class="wallet-dashboard">
    {{-- Statistiques du Wallet --}}
    <div class="wallet-stats-grid">
        <div class="wallet-stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Disponible au retrait</div>
                <div class="stat-value">{{ number_format($wallet->available_balance, 2) }} {{ $wallet->currency }}</div>
                <small class="stat-help">Vous pouvez retirer ce montant</small>
            </div>
        </div>

        <div class="wallet-stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-lock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">En période de blocage</div>
                <div class="stat-value">{{ number_format($wallet->held_balance, 2) }} {{ $wallet->currency }}</div>
                <small class="stat-help">Sera disponible sous peu</small>
            </div>
        </div>

        <div class="wallet-stat-card success">
            <div class="stat-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Solde total</div>
                <div class="stat-value">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</div>
                <small class="stat-help">Disponible + Bloqué</small>
            </div>
        </div>

        <div class="wallet-stat-card info">
            <div class="stat-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total gagné</div>
                <div class="stat-value">{{ number_format($wallet->total_earned, 2) }} {{ $wallet->currency }}</div>
                <small class="stat-help">Depuis le début</small>
            </div>
        </div>
    </div>

    {{-- Explication du système de blocage --}}
    @if($wallet->held_balance > 0)
    <div class="info-banner">
        <div class="info-icon">
            <i class="fas fa-info-circle"></i>
        </div>
        <div class="info-content">
            <h4>💡 Pourquoi certains fonds sont-ils bloqués ?</h4>
            <p>
                Pour garantir la sécurité des transactions, les nouveaux gains sont bloqués pendant 
                <strong>{{ \App\Models\Setting::get('wallet_holding_period_days', 7) }} jours</strong> avant d'être disponibles au retrait.
                Cette période permet de gérer d'éventuels litiges ou remboursements.
            </p>
        </div>
    </div>
    @endif

    {{-- Actions rapides --}}
    <div class="wallet-actions-section">
        <h3 class="section-title"><i class="fas fa-bolt me-2"></i>Actions rapides</h3>
        <div class="wallet-actions-grid">
            <a href="{{ route('wallet.create-payout') }}" class="wallet-action-btn primary">
                <i class="fas fa-money-bill-wave"></i>
                <span>Effectuer un retrait</span>
            </a>
            <a href="{{ route('wallet.transactions') }}" class="wallet-action-btn secondary">
                <i class="fas fa-list"></i>
                <span>Voir toutes les transactions</span>
            </a>
            <a href="{{ route('wallet.payouts') }}" class="wallet-action-btn secondary">
                <i class="fas fa-history"></i>
                <span>Historique des retraits</span>
            </a>
        </div>
    </div>

    {{-- Fonds en période de blocage --}}
    @if($wallet->activeHolds()->count() > 0)
    <div class="holds-section">
        <h3 class="section-title"><i class="fas fa-lock me-2"></i>Fonds en période de blocage</h3>
        <div class="holds-list">
            @foreach($wallet->activeHolds()->orderBy('held_until')->get() as $hold)
            <div class="hold-item">
                <div class="hold-info">
                    <div class="hold-amount">{{ number_format($hold->amount, 2) }} {{ $hold->currency }}</div>
                    <div class="hold-description">{{ $hold->description }}</div>
                </div>
                <div class="hold-timing">
                    @if($hold->isReleasable())
                        <span class="badge bg-success">
                            <i class="fas fa-unlock me-1"></i>Libérable maintenant
                        </span>
                    @else
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-clock me-1"></i>Disponible dans {{ $hold->formatted_remaining_time }}
                        </span>
                    @endif
                    <small class="hold-date">
                        Libération prévue le {{ $hold->held_until->translatedFormat('d M Y à H:i') }}
                    </small>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Retraits en attente --}}
    @if($pendingPayouts->count() > 0)
    <div class="pending-payouts-section">
        <h3 class="section-title"><i class="fas fa-spinner fa-pulse me-2"></i>Retraits en cours</h3>
        <div class="payouts-list">
            @foreach($pendingPayouts as $payout)
            <div class="payout-item">
                <div class="payout-info">
                    <div class="payout-header">
                        <span class="payout-amount">{{ number_format($payout->amount, 2) }} {{ $payout->currency }}</span>
                        <span class="payout-status">{!! $payout->status_badge !!}</span>
                    </div>
                    <div class="payout-details">
                        <span class="payout-method">{{ $payout->provider_name }}</span>
                        <span class="payout-date">{{ $payout->created_at->translatedFormat('d M Y à H:i') }}</span>
                    </div>
                </div>
                <div class="payout-actions">
                    @if($payout->canBeCancelled())
                    <form action="{{ route('wallet.cancel-payout', $payout) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce retrait ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-payout-cancel">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('wallet.show-payout', $payout) }}" class="btn-payout-view">
                        <i class="fas fa-eye"></i> Détails
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Transactions récentes --}}
    <div class="recent-transactions-section">
        <div class="section-header">
            <h3 class="section-title"><i class="fas fa-exchange-alt me-2"></i>Transactions récentes</h3>
            <a href="{{ route('wallet.transactions') }}" class="view-all-link">Voir tout <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        
        @if($transactions->count() > 0)
        <div class="transactions-table-responsive">
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Solde après</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td>
                            <span class="transaction-type">
                                <i class="{{ $transaction->icon }}"></i>
                                {{ $transaction->type_label }}
                            </span>
                        </td>
                        <td>
                            <span class="transaction-amount {{ $transaction->isCredit() ? 'credit' : 'debit' }}">
                                {{ $transaction->isCredit() ? '+' : '-' }}{{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}
                            </span>
                        </td>
                        <td>{{ number_format($transaction->balance_after, 2) }} {{ $transaction->currency }}</td>
                        <td class="transaction-description">{{ $transaction->description ?? '-' }}</td>
                        <td>{!! $transaction->status_badge !!}</td>
                        <td class="transaction-date">{{ $transaction->created_at->translatedFormat('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="pagination-wrapper">
            {{ $transactions->links() }}
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-inbox fa-3x"></i>
            <p>Aucune transaction pour le moment</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.wallet-dashboard {
    padding: 0;
}

.wallet-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.wallet-stat-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}

.wallet-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.wallet-stat-card .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.wallet-stat-card.primary .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.wallet-stat-card.success .stat-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.wallet-stat-card.warning .stat-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.wallet-stat-card.info .stat-icon {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.wallet-stat-card .stat-content {
    flex: 1;
}

.wallet-stat-card .stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.wallet-stat-card .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.wallet-stat-card .stat-help {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.25rem;
    display: block;
}

.info-banner {
    background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    display: flex;
    gap: 1rem;
    border: 1px solid #bfdbfe;
}

.info-banner .info-icon {
    width: 48px;
    height: 48px;
    background: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #3b82f6;
    flex-shrink: 0;
}

.info-banner .info-content h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #1e40af;
    margin-bottom: 0.5rem;
}

.info-banner .info-content p {
    font-size: 0.9375rem;
    color: #1e3a8a;
    margin: 0;
    line-height: 1.6;
}

.holds-section {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.holds-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.hold-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem;
    background: #fef3c7;
    border-radius: 12px;
    border: 1px solid #fde047;
}

.hold-info {
    flex: 1;
}

.hold-amount {
    font-size: 1.25rem;
    font-weight: 700;
    color: #92400e;
    margin-bottom: 0.25rem;
}

.hold-description {
    color: #78350f;
    font-size: 0.9375rem;
}

.hold-timing {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.5rem;
}

.hold-date {
    color: #a16207;
    font-size: 0.8125rem;
}

.wallet-actions-section,
.pending-payouts-section,
.recent-transactions-section {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.view-all-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    transition: color 0.2s;
}

.view-all-link:hover {
    color: #764ba2;
}

.wallet-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.wallet-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 1.5rem;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: transform 0.2s, box-shadow 0.2s;
}

.wallet-action-btn.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.wallet-action-btn.secondary {
    background: #f3f4f6;
    color: #374151;
}

.wallet-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.wallet-action-btn i {
    font-size: 2rem;
}

.payouts-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.payout-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem;
    background: #f9fafb;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.payout-info {
    flex: 1;
}

.payout-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.payout-amount {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
}

.payout-details {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.payout-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-payout-cancel,
.btn-payout-view {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-payout-cancel {
    background: #fee2e2;
    color: #dc2626;
}

.btn-payout-cancel:hover {
    background: #fecaca;
}

.btn-payout-view {
    background: #dbeafe;
    color: #2563eb;
}

.btn-payout-view:hover {
    background: #bfdbfe;
}

.transactions-table-responsive {
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
}

.transactions-table tbody td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
}

.transaction-type {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.transaction-amount.credit {
    color: #10b981;
    font-weight: 600;
}

.transaction-amount.debit {
    color: #ef4444;
    font-weight: 600;
}

.transaction-description {
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.transaction-date {
    color: #6b7280;
    font-size: 0.875rem;
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
    .wallet-stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .wallet-actions-grid {
        grid-template-columns: 1fr;
    }

    .payout-item {
        flex-direction: column;
        gap: 1rem;
    }

    .payout-actions {
        width: 100%;
        justify-content: stretch;
    }

    .btn-payout-cancel,
    .btn-payout-view {
        flex: 1;
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

