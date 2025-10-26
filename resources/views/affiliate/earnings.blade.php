@extends('layouts.app')

@section('title', 'Gains d\'affiliation - Herime Academie')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('affiliate.dashboard') }}">Affiliation</a></li>
                    <li class="breadcrumb-item active">Gains</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 fw-bold mb-1">Gains d'affiliation</h1>
                    <p class="text-muted mb-0">Suivez vos gains et l'historique de vos commissions</p>
                </div>
                <div>
                    <a href="{{ route('affiliate.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-dollar-sign text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Gains totaux</h6>
                            <h3 class="mb-0 fw-bold">${{ number_format($affiliate->total_earnings, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">En attente</h6>
                            <h3 class="mb-0 fw-bold">${{ number_format($affiliate->pending_earnings, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-check-circle text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Payés</h6>
                            <h3 class="mb-0 fw-bold">${{ number_format($affiliate->paid_earnings, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-percentage text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Taux de commission</h6>
                            <h3 class="mb-0 fw-bold">{{ $affiliate->commission_rate }}%</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Earnings Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Évolution des gains (12 derniers mois)</h5>
                </div>
                <div class="card-body">
                    @if($monthlyEarnings->count() > 0)
                        <canvas id="earningsChart" height="100"></canvas>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune donnée de gains disponible</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings History -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Historique des gains</h5>
                </div>
                <div class="card-body p-0">
                    @if($earnings->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Commande</th>
                                        <th>Client</th>
                                        <th>Cours</th>
                                        <th>Montant de la commande</th>
                                        <th>Commission</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($earnings as $order)
                                    <tr>
                                        <td>
                                            <small class="text-muted">{{ $order->created_at->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold">#{{ $order->order_number }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $order->user->avatar ? Storage::url($order->user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($order->user->name) . '&background=003366&color=fff' }}" 
                                                     alt="{{ $order->user->name }}" class="rounded-circle me-2" width="30" height="30">
                                                <span>{{ $order->user->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @foreach($order->orderItems as $item)
                                                <div class="small">{{ Str::limit($item->course->title, 30) }}</div>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="fw-bold">${{ number_format($order->total, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">${{ number_format(($order->total * $affiliate->commission_rate) / 100, 2) }}</span>
                                        </td>
                                        <td>
                                            @switch($order->status)
                                                @case('paid')
                                                    <span class="badge bg-success">Payé</span>
                                                    @break
                                                @case('pending')
                                                    <span class="badge bg-warning">En attente</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Annulé</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="p-3">
                            {{ $earnings->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun gain enregistré</h5>
                            <p class="text-muted">Commencez à promouvoir des cours pour générer des commissions</p>
                            <a href="{{ route('affiliate.links') }}" class="btn btn-primary">
                                <i class="fas fa-link me-2"></i>Générer des liens
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Earnings Chart
@if($monthlyEarnings->count() > 0)
const ctx = document.getElementById('earningsChart').getContext('2d');
const earningsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            @foreach($monthlyEarnings as $month)
                '{{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('M Y') }}',
            @endforeach
        ],
        datasets: [{
            label: 'Gains ($)',
            data: [
                @foreach($monthlyEarnings as $month)
                    {{ $month->total }},
                @endforeach
            ],
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
@endif
</script>
@endpush

@push('styles')
<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.breadcrumb {
    background-color: transparent;
    padding: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: ">";
    color: #6c757d;
}

.breadcrumb-item a {
    color: #003366;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #ffcc33;
}

.breadcrumb-item.active {
    color: #6c757d;
}

.btn-primary {
    background-color: #003366;
    border-color: #003366;
}

.btn-primary:hover {
    background-color: #004080;
    border-color: #004080;
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
}

.pagination {
    justify-content: center;
}

.page-link {
    color: #003366;
    border-color: #dee2e6;
}

.page-link:hover {
    color: #ffcc33;
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.page-item.active .page-link {
    background-color: #003366;
    border-color: #003366;
}

#earningsChart {
    max-height: 300px;
}
</style>
@endpush