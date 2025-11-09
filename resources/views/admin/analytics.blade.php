@extends('layouts.admin')

@section('title', 'Analytics')
@section('admin-title', 'Analytics & Statistiques')
@section('admin-subtitle', 'Visualisez les indicateurs clés de performance de la plateforme')
@section('admin-actions')
    <span class="btn btn-light">
        <i class="fas fa-clock me-2"></i>Mis à jour maintenant
    </span>
@endsection

@section('admin-content')
    <section class="admin-panel">
        <div class="admin-panel__body">
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Utilisateurs</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['total_users'] ?? 0) }}</p>
                    <p class="admin-stat-card__muted">Total inscrits</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Cours</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['total_courses'] ?? 0) }}</p>
                    <p class="admin-stat-card__muted">Catalogue disponible</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Commandes</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['total_orders'] ?? 0) }}</p>
                    <p class="admin-stat-card__muted">Transactions enregistrées</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Revenus</p>
                    <p class="admin-stat-card__value">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($stats['total_revenue'] ?? 0) }}</p>
                    <p class="admin-stat-card__muted">Cumul sur la période</p>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-chart-bar me-2"></i>Revenus par mois
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <canvas id="revenueChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-users me-2"></i>Croissance des utilisateurs
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <canvas id="usersChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-tags me-2"></i>Cours par catégorie
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <canvas id="categoriesChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-wallet me-2"></i>Paiements par méthode
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <canvas id="paymentsMethodChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-star me-2"></i>Cours les plus populaires
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <ul class="admin-list">
                                @forelse($popularCourses as $course)
                                    <li class="admin-list__item d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold">{{ $course->title }}</div>
                                            <div class="text-muted small">{{ $course->instructor->name }}</div>
                                        </div>
                                        <span class="admin-chip admin-chip--primary">{{ $course->enrollments_count }} inscrits</span>
                                    </li>
                                @empty
                                    <li class="admin-list__item text-center text-muted">Aucun cours trouvé</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-chart-pie me-2"></i>Paiements par statut
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <canvas id="paymentsStatusChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__body">
            <div class="admin-card shadow-sm">
                <div class="admin-card__header">
                    <h5 class="admin-card__title">
                        <i class="fas fa-table me-2"></i>Statistiques détaillées
                    </h5>
                </div>
                <div class="admin-card__body">
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
    </section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
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
.admin-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-card__header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-card__title {
    margin: 0;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
}

.admin-card__body {
    padding: 1.5rem;
}

.admin-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 1rem;
}

.admin-list__item {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-list__item:hover {
    background: #eef2ff;
}

.admin-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.82rem;
}

.admin-chip--info {
    background: rgba(59, 130, 246, 0.12);
    color: #1d4ed8;
}

.admin-chip--primary {
    background: rgba(14, 165, 233, 0.12);
    color: #0369a1;
}

.table thead th {
    background: #f1f5f9;
    font-weight: 600;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
}
</style>
@endpush
