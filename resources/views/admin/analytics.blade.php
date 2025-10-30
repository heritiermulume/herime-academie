@extends('layouts.app')

@section('title', 'Analytics - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-sm" title="Tableau de bord">
                                <i class="fas fa-tachometer-alt"></i>
                            </a>
                            <h4 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>Analytics et Statistiques
                            </h4>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-clock me-1"></i>Mis à jour maintenant
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistiques générales -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3 class="card-title">{{ $stats['total_users'] ?? 0 }}</h3>
                                    <p class="card-text mb-0">Utilisateurs</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3 class="card-title">{{ $stats['total_courses'] ?? 0 }}</h3>
                                    <p class="card-text mb-0">Cours</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3 class="card-title">{{ $stats['total_orders'] ?? 0 }}</h3>
                                    <p class="card-text mb-0">Commandes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3 class="card-title">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($stats['total_revenue'] ?? 0) }}</h3>
                                    <p class="card-text mb-0">Revenus</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Graphiques -->
                    <div class="row">
                        <!-- Revenus par mois -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-bar me-2"></i>Revenus par mois
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="revenueChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Croissance des utilisateurs -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-users me-2"></i>Croissance des utilisateurs
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="usersChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques par catégorie et paiements -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-tags me-2"></i>Cours par catégorie
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="categoriesChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Paiements par méthode -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-wallet me-2"></i>Paiements par méthode
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="paymentsMethodChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cours populaires et répartition des statuts de paiements -->
                    <div class="row">
                        <!-- Cours les plus populaires -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-star me-2"></i>Cours les plus populaires
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        @forelse($popularCourses as $course)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $course->title }}</h6>
                                                <small class="text-muted">{{ $course->instructor->name }}</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-primary me-2">{{ $course->enrollments_count }} inscrits</span>
                                            </div>
                                        </div>
                                        @empty
                                        <div class="text-center py-3">
                                            <p class="text-muted">Aucun cours trouvé</p>
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Paiements par statut -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-pie me-2"></i>Paiements par statut
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="paymentsStatusChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des statistiques détaillées -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-table me-2"></i>Statistiques détaillées
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Métrique</th>
                                                    <th>Valeur</th>
                                                    <th>Évolution</th>
                                                    <th>Tendance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <i class="fas fa-users text-primary me-2"></i>
                                                        Nouveaux utilisateurs ce mois
                                                    </td>
                                                    <td><strong>{{ $userGrowth->last()?->count ?? 0 }}</strong></td>
                                                    <td>
                                                        @if($userGrowth->count() > 1)
                                                            @php
                                                                $previous = $userGrowth->slice(-2, 1)->first()?->count ?? 0;
                                                                $current = $userGrowth->last()?->count ?? 0;
                                                                $change = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
                                                            @endphp
                                                            <span class="badge bg-{{ $change >= 0 ? 'success' : 'danger' }}">
                                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 1) }}%
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($userGrowth->count() > 1)
                                                            @php
                                                                $change = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
                                                            @endphp
                                                            <i class="fas fa-arrow-{{ $change >= 0 ? 'up' : 'down' }} text-{{ $change >= 0 ? 'success' : 'danger' }}"></i>
                                                        @else
                                                            <i class="fas fa-minus text-secondary"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <i class="fas fa-graduation-cap text-success me-2"></i>
                                                        Cours publiés
                                                    </td>
                                                    <td><strong>{{ $courseStats->published_courses ?? 0 }}</strong></td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            {{ $courseStats->total_courses ?? 0 }} total
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-check text-success"></i>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <i class="fas fa-user-graduate text-warning me-2"></i>
                                                        Total des étudiants
                                                    </td>
                                                    <td><strong>{{ $courseStats->total_students ?? 0 }}</strong></td>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            {{ $stats['total_enrollments'] ?? 0 }} inscriptions
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-arrow-up text-success"></i>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <i class="fas fa-star text-info me-2"></i>
                                                        Note moyenne des cours
                                                    </td>
                                                    <td><strong>{{ number_format($courseStats->average_rating ?? 0, 1) }}/5</strong></td>
                                                    <td>
                                                        <div class="text-warning">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fas fa-star{{ $i <= ($courseStats->average_rating ?? 0) ? '' : '-o' }}"></i>
                                                            @endfor
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-star text-warning"></i>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Données pour les graphiques
const revenueData = @json($revenueByMonth ?? []);
const userGrowthData = @json($userGrowth ?? []);
const categoryStats = @json($categoryStats ?? []);
const paymentsByMethod = @json($paymentsByMethod ?? []);
const paymentsByStatus = @json($paymentsByStatus ?? []);

// Graphique des revenus
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueData.map(item => item.month),
        datasets: [{
            label: 'Revenus ({{ $baseCurrency ?? "USD" }})',
            data: revenueData.map(item => item.revenue),
            borderColor: '#003366',
            backgroundColor: 'rgba(0, 51, 102, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
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
                        return new Intl.NumberFormat('fr-FR').format(value);
                    }
                }
            }
        }
    }
});

// Graphique de croissance des utilisateurs
const usersCtx = document.getElementById('usersChart').getContext('2d');
new Chart(usersCtx, {
    type: 'bar',
    data: {
        labels: userGrowthData.map(item => item.month),
        datasets: [{
            label: 'Nouveaux utilisateurs',
            data: userGrowthData.map(item => item.count),
            backgroundColor: '#28a745',
            borderColor: '#28a745',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Graphique des catégories
const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
new Chart(categoriesCtx, {
    type: 'doughnut',
    data: {
        labels: categoryStats.map(item => item.name),
        datasets: [{
            data: categoryStats.map(item => item.courses_count),
            backgroundColor: [
                '#003366',
                '#ffcc33',
                '#28a745',
                '#dc3545',
                '#17a2b8',
                '#6f42c1',
                '#fd7e14',
                '#20c997'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Paiements par méthode
const methodCtx = document.getElementById('paymentsMethodChart').getContext('2d');
new Chart(methodCtx, {
    type: 'doughnut',
    data: {
        labels: paymentsByMethod.map(p => (p.payment_method || 'inconnu').toUpperCase()),
        datasets: [{
            data: paymentsByMethod.map(p => p.count),
            backgroundColor: ['#003366','#ffcc33','#28a745','#dc3545','#17a2b8','#6f42c1']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// Paiements par statut
const statusCtx = document.getElementById('paymentsStatusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: paymentsByStatus.map(p => (p.status || 'inconnu').toUpperCase()),
        datasets: [{
            data: paymentsByStatus.map(p => p.count),
            backgroundColor: ['#28a745','#ffc107','#dc3545','#6c757d']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush

@push('styles')
<style>
.card-header {
    background: linear-gradient(135deg, #003366 0%, #004080 100%);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #003366;
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

.badge {
    font-size: 0.75em;
}

.text-warning {
    color: #ffc107 !important;
}
</style>
@endpush
