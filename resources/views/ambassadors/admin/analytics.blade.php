@extends('ambassadors.admin.layout')

@section('admin-title', 'Analytics & indicateurs clés')
@section('admin-subtitle', 'Suivez vos commissions, vos références et vos gains en temps réel.')

@section('admin-content')
    <section class="dashboard-grid">
        <article class="admin-card dashboard-grid__item">
            <div class="dashboard-metric">
                <div class="dashboard-metric__icon" style="background: #0ea5e920; color: #0ea5e9;">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="dashboard-metric__content">
                    <span class="dashboard-metric__label">Total de commissions</span>
                    <strong class="dashboard-metric__value">{{ number_format($totalCommissions) }}</strong>
                    <span class="dashboard-metric__muted">{{ $paidCommissions }} payées</span>
                </div>
            </div>
        </article>
        <article class="admin-card dashboard-grid__item">
            <div class="dashboard-metric">
                <div class="dashboard-metric__icon" style="background: #6366f120; color: #6366f1;">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="dashboard-metric__content">
                    <span class="dashboard-metric__label">Gains totaux</span>
                    <strong class="dashboard-metric__value">{{ number_format($ambassador->total_earnings ?? 0, 2) }} {{ $currencyCode }}</strong>
                    <span class="dashboard-metric__muted">Depuis le début</span>
                </div>
            </div>
        </article>
        <article class="admin-card dashboard-grid__item">
            <div class="dashboard-metric">
                <div class="dashboard-metric__icon" style="background: #f59e0b20; color: #f59e0b;">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="dashboard-metric__content">
                    <span class="dashboard-metric__label">En attente</span>
                    <strong class="dashboard-metric__value">{{ number_format($ambassador->pending_earnings ?? 0, 2) }} {{ $currencyCode }}</strong>
                    <span class="dashboard-metric__muted">{{ $pendingCommissions }} commission(s) en attente</span>
                </div>
            </div>
        </article>
        <article class="admin-card dashboard-grid__item">
            <div class="dashboard-metric">
                <div class="dashboard-metric__icon" style="background: #22c55e20; color: #22c55e;">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="dashboard-metric__content">
                    <span class="dashboard-metric__label">Références</span>
                    <strong class="dashboard-metric__value">{{ number_format($ambassador->total_referrals ?? 0) }}</strong>
                    <span class="dashboard-metric__muted">Total de références</span>
                </div>
            </div>
        </article>
    </section>

    <article class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-chart-line me-2"></i>Évolution des commissions
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="analytics-chart">
                <canvas id="commissions-chart" height="280"></canvas>
                @if($commissionsByMonth->isEmpty())
                    <div class="analytics-empty">Pas encore de données suffisantes pour afficher une courbe.</div>
                @endif
            </div>
        </div>
    </article>

    <article class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-trophy me-2"></i>Top commandes
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Commande</th>
                            <th>Montant</th>
                            <th>Commission</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topOrders as $commission)
                            <tr>
                                <td>{{ $commission->created_at->format('d/m/Y') }}</td>
                                <td>{{ $commission->order?->order_number ?? 'N/A' }}</td>
                                <td>{{ number_format($commission->order_total, 2) }} {{ $currencyCode }}</td>
                                <td>{{ number_format($commission->commission_amount, 2) }} {{ $currencyCode }}</td>
                                <td>
                                    <span class="admin-badge {{ $commission->getStatusBadgeClass() }}">
                                        {{ $commission->getStatusLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">Aucune commission pour le moment</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </article>

    @if(!empty($insights))
        <article class="admin-panel">
            <div class="admin-panel__header">
                <h3>
                    <i class="fas fa-lightbulb me-2"></i>Insights
                </h3>
            </div>
            <div class="admin-panel__body">
                <ul class="dashboard-tasks">
                    @foreach($insights as $insight)
                        <li class="dashboard-tasks__item">
                            <div>
                                <strong>{{ $insight['title'] }}</strong>
                                <span>{{ $insight['description'] }}</span>
                            </div>
                            <span class="dashboard-tasks__badge {{ $insight['type'] }}">{{ ucfirst($insight['type']) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </article>
    @endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('commissions-chart');
    if (!ctx) return;

    const data = @json($commissionsByMonth);
    
    if (data.length === 0) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.formatted_month),
            datasets: [{
                label: 'Nombre de commissions',
                data: data.map(item => item.count),
                borderColor: '#003366',
                backgroundColor: 'rgba(0, 51, 102, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Montant total ({{ $currencyCode }})',
                data: data.map(item => parseFloat(item.total || 0)),
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .dashboard-grid__item {
        padding: 0;
    }
    .dashboard-metric {
        display: flex;
        gap: 1.25rem;
        align-items: center;
    }
    .dashboard-metric__icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 1.4rem;
    }
    .dashboard-metric__label {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        display: block;
    }
    .dashboard-metric__value {
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        display: block;
        margin: 0.25rem 0;
    }
    .dashboard-metric__muted {
        font-size: 0.85rem;
        color: #64748b;
        display: block;
    }
    .analytics-chart {
        position: relative;
        height: 280px;
    }
    .analytics-empty {
        text-align: center;
        padding: 2rem;
        color: #64748b;
    }
    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .dashboard-metric {
            gap: 0.85rem;
        }
        .dashboard-metric__icon {
            width: 48px;
            height: 48px;
            font-size: 1.2rem;
        }
        .dashboard-metric__label {
            font-size: 0.7rem;
        }
        .dashboard-metric__value {
            font-size: 1.5rem;
        }
        .dashboard-metric__muted {
            font-size: 0.75rem;
        }
    }
</style>
@endpush

