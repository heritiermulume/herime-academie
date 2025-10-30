@extends('layouts.app')

@section('title', 'Transactions - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow mb-4">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #003366 0%, #004080 100%);">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-sm" title="Tableau de bord">
                        <i class="fas fa-tachometer-alt"></i>
                    </a>
                    <div>
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Transactions</h5>
                        <small class="opacity-75">Liste des paiements réussis/échoués</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form class="row g-2 mb-3" method="GET">
                <div class="col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Rechercher (nom/email)">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tous statuts</option>
                        <option value="pending" {{ request('status')==='pending' ? 'selected' : '' }}>En attente</option>
                        <option value="completed" {{ request('status')==='completed' ? 'selected' : '' }}>Réussi</option>
                        <option value="failed" {{ request('status')==='failed' ? 'selected' : '' }}>Échoué</option>
                        <option value="cancelled" {{ request('status')==='cancelled' ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="method" class="form-select">
                        <option value="">Tous moyens</option>
                        <option value="pawapay" {{ request('method')==='pawapay' ? 'selected' : '' }}>pawaPay</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" placeholder="Du">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" placeholder="Au">
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-outline-primary"><i class="fas fa-filter me-1"></i>Filtrer</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Référence</th>
                            <th>Utilisateur</th>
                            <th>Méthode</th>
                            <th>Fournisseur</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Raison (échec)</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td><code>{{ $payment->payment_id }}</code></td>
                            <td>
                                @if($payment->order && $payment->order->user)
                                    <div class="d-flex flex-column">
                                        <span>{{ $payment->order->user->name }}</span>
                                        <small class="text-muted">{{ $payment->order->user->email }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">Inconnu</span>
                                @endif
                            </td>
                            <td class="text-uppercase">{{ $payment->payment_method }}</td>
                            <td class="text-uppercase">{{ $payment->provider ?? '-' }}</td>
                            <td>{{ \App\Helpers\CurrencyHelper::formatWithSymbol($payment->amount, $payment->currency ?? $baseCurrency) }}</td>
                            <td>
                                @php($status = strtolower($payment->status))
                                <span class="badge bg-{{ $status === 'completed' ? 'success' : ($status === 'pending' ? 'warning' : 'danger') }} text-uppercase">{{ $payment->status }}</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $payment->failure_reason ?? '-' }}</small>
                            </td>
                            <td><small class="text-muted">{{ $payment->created_at->format('Y-m-d H:i') }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucune transaction trouvée.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection


