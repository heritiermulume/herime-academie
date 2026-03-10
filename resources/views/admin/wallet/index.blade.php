@extends('layouts.admin')

@section('title', 'Wallet - Tableau de bord')
@section('admin-title', 'Wallet')
@section('admin-subtitle', 'Vue d\'ensemble des portefeuilles et gains récents')

@section('admin-content')
    @include('admin.wallet.partials.tabs')

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3><i class="fas fa-wallet me-2"></i>Portefeuilles</h3>
        </div>
        <div class="admin-panel__body">
            <p class="text-muted small mb-3">Montants regroupés par devise configurée sur le site (devise de base et devises des comptes).</p>
            <div class="row g-3">
                @forelse($walletsByCurrency as $item)
                    <div class="col-12 col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title mb-1">Portefeuille {{ $item['currency'] }}</h5>
                                    <span class="badge bg-primary rounded-pill">{{ $item['currency'] }}</span>
                                </div>
                                @if($item['wallets_count'] > 0)
                                    <p class="text-muted small mb-2">{{ $item['wallets_count'] }} compte(s) dans cette devise</p>
                                @endif
                                <hr class="my-3">
                                <div class="d-flex flex-wrap gap-3">
                                    <div>
                                        <span class="text-muted small d-block">Solde total</span>
                                        <strong>{{ number_format($item['balance'], 2) }} {{ $item['currency'] }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted small d-block">Disponible</span>
                                        <strong class="text-success">{{ number_format($item['available_balance'] ?? 0, 2) }} {{ $item['currency'] }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted small d-block">Bloqué</span>
                                        <strong class="text-warning">{{ number_format($item['held_balance'] ?? 0, 2) }} {{ $item['currency'] }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted mb-0">Aucune devise configurée. La devise de base du site est utilisée par défaut.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="admin-panel mt-4">
        <div class="admin-panel__header">
            <h3><i class="fas fa-chart-line me-2"></i>Gains récents (10 derniers)</h3>
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
                        @forelse($recentGains as $tx)
                            <tr>
                                <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($tx->wallet && $tx->wallet->user)
                                        <a href="{{ route('admin.users.show', $tx->wallet->user) }}">{{ $tx->wallet->user->name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td><span class="badge bg-success">{{ $tx->type }}</span></td>
                                <td class="text-success">+{{ number_format($tx->amount, 2) }} {{ $tx->currency }}</td>
                                <td class="text-muted small">{{ Str::limit($tx->description ?? '', 40) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted text-center">Aucun gain récent.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
