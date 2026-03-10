@extends('layouts.admin')

@section('title', 'Wallet - Configuration')
@section('admin-title', 'Wallet - Configuration')
@section('admin-subtitle', 'Paiements automatiques et règles d\'automatisation des payouts')

@section('admin-content')
    @include('admin.wallet.partials.tabs')

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3><i class="fas fa-cog me-2"></i>Règles d'automatisation des payouts</h3>
        </div>
        <div class="admin-panel__body">
            <form action="{{ route('admin.wallet.config.update') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="wallet_auto_payout_enabled" value="1" id="wallet_auto_payout_enabled" {{ $walletSettings['auto_payout_enabled'] ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="wallet_auto_payout_enabled">Activer les paiements automatiques des portefeuilles</label>
                            <div class="form-text">Lorsque cette option est activée, les règles ci-dessous peuvent déclencher des payouts automatiques (à configurer selon vos besoins).</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="wallet_auto_payout_min_balance" class="form-label">Solde minimum pour déclencher un payout automatique</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="wallet_auto_payout_min_balance" name="wallet_auto_payout_min_balance" value="{{ $walletSettings['auto_payout_min_balance'] }}">
                        <div class="form-text">Seuil à partir duquel un payout automatique peut être initié (0 = désactivé).</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="wallet_auto_payout_frequency" class="form-label">Fréquence des payouts automatiques</label>
                        <select class="form-select" id="wallet_auto_payout_frequency" name="wallet_auto_payout_frequency">
                            <option value="daily" {{ $walletSettings['auto_payout_frequency'] === 'daily' ? 'selected' : '' }}>Quotidien</option>
                            <option value="weekly" {{ $walletSettings['auto_payout_frequency'] === 'weekly' ? 'selected' : '' }}>Hebdomadaire</option>
                            <option value="monthly" {{ $walletSettings['auto_payout_frequency'] === 'monthly' ? 'selected' : '' }}>Mensuel</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note :</strong> La logique métier des payouts automatiques (cron, sélection des comptes bénéficiaires, etc.) peut être implémentée dans une commande planifiée ou un job. Cette page enregistre uniquement les paramètres.
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Enregistrer la configuration</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="admin-panel mt-4">
        <div class="admin-panel__header">
            <h3><i class="fas fa-info-circle me-2"></i>Référence</h3>
        </div>
        <div class="admin-panel__body">
            <p class="text-muted mb-0">Les paramètres globaux du wallet (période de blocage, montant minimum de retrait, libération automatique des fonds) se trouvent dans <a href="{{ route('admin.settings') }}?tab=wallet">Paramètres du site</a>, onglet « Wallet Ambassadeurs ».</p>
        </div>
    </section>
@endsection
