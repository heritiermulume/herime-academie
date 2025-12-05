@extends('layouts.admin')

@section('title', 'Détails Ambassadeur')
@section('admin-title', $ambassador->user->name)

@section('admin-content')
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Statistiques</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Gains totaux:</strong> {{ number_format($ambassador->total_earnings, 2) }} {{ \App\Models\Setting::getBaseCurrency() }}</p>
                            <p><strong>En attente:</strong> {{ number_format($ambassador->pending_earnings, 2) }} {{ \App\Models\Setting::getBaseCurrency() }}</p>
                            <p><strong>Payés:</strong> {{ number_format($ambassador->paid_earnings, 2) }} {{ \App\Models\Setting::getBaseCurrency() }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Références:</strong> {{ $ambassador->total_referrals }}</p>
                            <p><strong>Ventes:</strong> {{ $ambassador->total_sales }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5>Commissions</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Commande</th>
                                    <th>Montant</th>
                                    <th>Commission</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ambassador->commissions->take(10) as $commission)
                                    <tr>
                                        <td>{{ $commission->order->order_number }}</td>
                                        <td>{{ number_format($commission->order_total, 2) }}</td>
                                        <td>{{ number_format($commission->commission_amount, 2) }}</td>
                                        <td><span class="badge bg-{{ $commission->getStatusBadgeClass() }}">{{ $commission->getStatusLabel() }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>Actions</h5>
                    <form method="POST" action="{{ route('admin.ambassadors.toggle-active', $ambassador) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-{{ $ambassador->is_active ? 'warning' : 'success' }} w-100">
                            {{ $ambassador->is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.ambassadors.generate-promo-code', $ambassador) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">Générer code promo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

