@extends('ambassadors.admin.layout')

@section('admin-title', 'Historique des retraits')
@section('admin-subtitle', 'Consultez tous vos retraits effectués')

@section('admin-content')
<div class="payouts-page">
    {{-- Nouveau système de filtres global --}}
    <x-wallet-filters type="payouts" :filters="request()->all()" />

    {{-- Actions --}}
    <div class="actions-bar">
        <a href="{{ route('wallet.index') }}" class="btn btn-outline-custom">
            <i class="fas fa-arrow-left me-2"></i>Retour au wallet
        </a>
        <a href="{{ route('wallet.create-payout') }}" class="btn btn-primary-custom">
            <i class="fas fa-plus me-2"></i>Nouveau retrait
        </a>
    </div>

    {{-- Liste des payouts --}}
    <div class="payouts-card">
        @if($payouts->count() > 0)
        <div class="payouts-list">
            @foreach($payouts as $payout)
            <div class="payout-item">
                <div class="payout-header">
                    <div class="payout-main-info">
                        <div class="payout-amount">{{ number_format($payout->amount, 2) }} {{ $payout->currency }}</div>
                        <div class="payout-method">{{ $payout->provider_name }}</div>
                    </div>
                    <div class="payout-status">
                        {!! $payout->status_badge !!}
                    </div>
                </div>

                <div class="payout-details">
                    <div class="payout-detail-item">
                        <i class="fas fa-calendar text-muted"></i>
                        <span>{{ $payout->created_at->translatedFormat('d M Y à H:i') }}</span>
                    </div>
                    <div class="payout-detail-item">
                        <i class="fas fa-phone text-muted"></i>
                        <span>{{ $payout->phone }}</span>
                    </div>
                    @if($payout->moneroo_id)
                    <div class="payout-detail-item">
                        <i class="fas fa-hashtag text-muted"></i>
                        <code>{{ $payout->moneroo_id }}</code>
                    </div>
                    @endif
                </div>

                @if($payout->description)
                <div class="payout-description">
                    <i class="fas fa-comment text-muted"></i>
                    {{ $payout->description }}
                </div>
                @endif

                @if($payout->failure_reason)
                <div class="payout-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $payout->failure_reason }}
                </div>
                @endif

                <div class="payout-actions">
                    <a href="{{ route('wallet.show-payout', $payout) }}" class="btn-payout-detail">
                        <i class="fas fa-eye me-1"></i>Détails
                    </a>
                    
                    @if($payout->canBeCancelled())
                    <form action="{{ route('wallet.cancel-payout', $payout) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce retrait ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-payout-cancel">
                            <i class="fas fa-times me-1"></i>Annuler
                        </button>
                    </form>
                    @endif

                    @if($payout->moneroo_id && in_array($payout->status, ['pending', 'processing']))
                    <form action="{{ route('wallet.check-payout-status', $payout) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn-payout-check">
                            <i class="fas fa-sync me-1"></i>Vérifier
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrapper">
            {{ $payouts->appends(request()->query())->links() }}
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-inbox fa-3x"></i>
            <p>Aucun retrait trouvé</p>
            @if(request()->hasAny(['status', 'from', 'to']))
            <a href="{{ route('wallet.payouts') }}" class="btn btn-outline-primary mt-3">
                Réinitialiser les filtres
            </a>
            @else
            <a href="{{ route('wallet.create-payout') }}" class="btn btn-primary mt-3">
                <i class="fas fa-plus me-2"></i>Effectuer votre premier retrait
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.payouts-page {
    padding: 0;
}

.actions-bar,
.payouts-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.actions-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
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

.btn-primary-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
}

.payouts-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.payout-item {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    transition: box-shadow 0.2s;
}

.payout-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.payout-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.payout-main-info {
    flex: 1;
}

.payout-amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.25rem;
}

.payout-method {
    color: #6b7280;
    font-size: 0.9375rem;
}

.payout-details {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.payout-detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.payout-detail-item code {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8125rem;
}

.payout-description {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #374151;
    margin-bottom: 1rem;
}

.payout-error {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #fee2e2;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #dc2626;
    margin-bottom: 1rem;
}

.payout-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-payout-detail,
.btn-payout-cancel,
.btn-payout-check {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-payout-detail {
    background: #dbeafe;
    color: #2563eb;
}

.btn-payout-detail:hover {
    background: #bfdbfe;
}

.btn-payout-cancel {
    background: #fee2e2;
    color: #dc2626;
}

.btn-payout-cancel:hover {
    background: #fecaca;
}

.btn-payout-check {
    background: #d1fae5;
    color: #059669;
}

.btn-payout-check:hover {
    background: #a7f3d0;
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
    .actions-bar {
        flex-direction: column;
        gap: 1rem;
    }

    .btn-outline-custom,
    .btn-primary-custom {
        width: 100%;
    }

    .payout-header {
        flex-direction: column;
        gap: 1rem;
    }

    .payout-details {
        flex-direction: column;
        gap: 0.75rem;
    }

    .payout-actions {
        flex-direction: column;
    }

    .btn-payout-detail,
    .btn-payout-cancel,
    .btn-payout-check {
        width: 100%;
    }
}
</style>
@endpush

