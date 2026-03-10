@extends('layouts.admin')

@section('title', 'Wallet - Solde')
@section('admin-title', 'Wallet - Solde')
@section('admin-subtitle', 'Solde total, entrées et sorties')
@section('admin-actions')
    <a href="{{ route('admin.wallet.balance.export') }}" class="btn btn-outline-success" download>
        <i class="fas fa-download me-2"></i>Exporter
    </a>
@endsection

@section('admin-content')
    @include('admin.wallet.partials.tabs')

    @php
        $balanceCurrency = \App\Models\Setting::get('base_currency', 'USD');
    @endphp
    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3><i class="fas fa-coins me-2"></i>Solde total du portefeuille</h3>
        </div>
        <div class="admin-panel__body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                        <div class="card-body">
                            <p class="text-muted small mb-1">Solde total</p>
                            <p class="admin-wallet-balance-amount mb-0">{{ number_format($totalBalance, 2) }} {{ $balanceCurrency }}</p>
                            <p class="text-muted small mb-0">(tous portefeuilles)</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                        <div class="card-body">
                            <p class="text-muted small mb-1">Disponible au retrait</p>
                            <p class="admin-wallet-balance-amount mb-0 text-success">{{ number_format($totalAvailable, 2) }} {{ $balanceCurrency }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                        <div class="card-body">
                            <p class="text-muted small mb-1">En période de blocage</p>
                            <p class="admin-wallet-balance-amount mb-0 text-warning">{{ number_format($totalHeld, 2) }} {{ $balanceCurrency }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <style>
    .admin-wallet-balance-amount {
        font-size: 2rem;
        font-weight: 700;
    }
    @media (min-width: 768px) {
        .admin-wallet-balance-amount {
            font-size: 1.5rem;
        }
    }
    </style>

    <section class="admin-panel mt-4">
        <div class="admin-panel__header">
            <h3><i class="fas fa-arrow-down me-2 text-success"></i>Entrées (paiements reçus)</h3>
        </div>
        <div class="admin-panel__body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Portefeuille</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inflows as $tx)
                            <tr>
                                <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $tx->wallet && $tx->wallet->user ? $tx->wallet->user->name : '—' }}</td>
                                <td><span class="badge bg-success">{{ $tx->type }}</span></td>
                                <td class="text-success">+{{ number_format($tx->amount, 2) }} {{ $tx->currency }}</td>
                                <td class="small">{{ Str::limit($tx->description, 50) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted text-center">Aucune entrée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $inflows->links() }}
        </div>
    </section>

    <section class="admin-panel mt-4">
        <div class="admin-panel__header">
            <h3><i class="fas fa-arrow-up me-2 text-danger"></i>Sorties (payouts / retraits)</h3>
        </div>
        <div class="admin-panel__body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Portefeuille</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outflows as $tx)
                            <tr>
                                <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $tx->wallet && $tx->wallet->user ? $tx->wallet->user->name : '—' }}</td>
                                <td><span class="badge bg-danger">{{ $tx->type }}</span></td>
                                <td class="text-danger">-{{ number_format($tx->amount, 2) }} {{ $tx->currency }}</td>
                                <td class="small">{{ Str::limit($tx->description, 50) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted text-center">Aucune sortie.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $outflows->links() }}
        </div>
    </section>
@endsection
