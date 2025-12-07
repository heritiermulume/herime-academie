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
                    <strong class="dashboard-metric__value">{{ number_format($totalEarnings, 2) }} {{ $currencyCode }}</strong>
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
                    <strong class="dashboard-metric__value">{{ number_format($pendingEarnings, 2) }} {{ $currencyCode }}</strong>
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
                    <strong class="dashboard-metric__value">{{ number_format($totalReferrals) }}</strong>
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
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        padding: 10,
                        font: {
                            size: window.innerWidth < 768 ? 10 : 12
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    ticks: {
                        font: {
                            size: window.innerWidth < 768 ? 10 : 12
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        font: {
                            size: window.innerWidth < 768 ? 10 : 12
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: window.innerWidth < 768 ? 10 : 12
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
    /* Empêcher le débordement horizontal */
    section.admin-content,
    .admin-content {
        overflow-x: hidden !important;
        max-width: 100% !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .instructor-admin-shell {
        overflow-x: hidden;
        max-width: 100vw;
    }

    .admin-main {
        overflow-x: hidden;
        max-width: 100%;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        width: 100%;
        max-width: 100%;
    }
    .dashboard-grid__item {
        padding: 0;
        min-width: 0;
        max-width: 100%;
        overflow: hidden;
    }
    .dashboard-metric {
        display: flex;
        gap: 1.25rem;
        align-items: center;
        min-width: 0;
        width: 100%;
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
    }
    .dashboard-metric__value {
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        display: block;
        word-break: break-word;
    }
    .dashboard-metric__muted {
        font-size: 0.85rem;
        color: #64748b;
        word-break: break-word;
    }

    .dashboard-metric__content {
        min-width: 0;
        flex: 1;
        overflow: hidden;
    }
    .analytics-chart {
        position: relative;
        height: 280px;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }

    .analytics-chart canvas {
        max-width: 100% !important;
        height: auto !important;
    }
    .analytics-empty {
        text-align: center;
        padding: 2rem;
        color: #64748b;
        font-size: 0.9rem;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0;
        width: 100%;
        max-width: 100%;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }

    .admin-table th {
        background: rgba(226, 232, 240, 0.5);
        padding: 0.85rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.85rem;
        color: #0f172a;
        border-bottom: 2px solid rgba(226, 232, 240, 0.8);
    }

    .admin-table td {
        padding: 0.85rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.5);
        font-size: 0.9rem;
        color: #0f172a;
    }

    .admin-table tbody tr:hover {
        background: rgba(226, 232, 240, 0.2);
    }

    .admin-badge {
        display: inline-block;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .admin-badge.warning {
        background: rgba(245, 158, 11, 0.15);
        color: #b45309;
    }

    .admin-badge.info {
        background: rgba(14, 165, 233, 0.15);
        color: #0369a1;
    }

    .admin-badge.success {
        background: rgba(34, 197, 94, 0.15);
        color: #15803d;
    }

    .admin-badge.danger {
        background: rgba(220, 38, 38, 0.15);
        color: #b91c1c;
    }

    .admin-badge.secondary {
        background: rgba(100, 116, 139, 0.15);
        color: #475569;
    }

    .admin-card {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        box-sizing: border-box;
    }

    .admin-panel {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        box-sizing: border-box;
    }

    .admin-panel__body {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        box-sizing: border-box;
    }
    .dashboard-tasks {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .dashboard-tasks__item {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-radius: 1rem;
        background: rgba(226, 232, 240, 0.35);
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .dashboard-tasks__item strong {
        color: #0f172a;
        display: block;
        margin-bottom: 0.25rem;
        word-break: break-word;
    }
    .dashboard-tasks__item span {
        color: #64748b;
        font-size: 0.85rem;
        word-break: break-word;
    }
    .dashboard-tasks__badge {
        align-self: flex-start;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .dashboard-tasks__badge.alert {
        background: rgba(220, 38, 38, 0.15);
        color: #b91c1c;
    }
    .dashboard-tasks__badge.info {
        background: rgba(14, 165, 233, 0.15);
        color: #0369a1;
    }
    .dashboard-tasks__badge.success {
        background: rgba(34, 197, 94, 0.15);
        color: #15803d;
    }

    @media (max-width: 1024px) {
        .admin-content {
            padding: 0;
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
            width: 100%;
            max-width: 100%;
        }

        .dashboard-metric {
            gap: 0.85rem;
            width: 100%;
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

        .analytics-chart {
            height: 250px;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .admin-table {
            min-width: 600px;
        }

        .admin-table th,
        .admin-table td {
            padding: 0.75rem;
            font-size: 0.85rem;
        }

        .dashboard-tasks {
            padding: 0 1rem 1rem;
            gap: 0.65rem;
        }

        .dashboard-tasks__item {
            padding: 0.75rem;
            gap: 0.75rem;
        }

        .dashboard-tasks__item strong {
            font-size: 0.85rem;
        }

        .dashboard-tasks__item span {
            font-size: 0.75rem;
        }

        .dashboard-tasks__badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
    }

    @media (max-width: 768px) {
        .admin-content {
            padding: 0;
        }

        .admin-card {
            padding: 1rem !important;
            max-width: 100%;
            overflow: hidden;
        }

        .admin-panel__body {
            padding: 1rem !important;
        }

        .dashboard-grid {
            gap: 0.75rem;
            width: 100%;
            max-width: 100%;
        }

        .dashboard-metric {
            gap: 0.75rem;
            flex-wrap: wrap;
            width: 100%;
        }

        .dashboard-metric__icon {
            width: 44px;
            height: 44px;
            font-size: 1.1rem;
        }

        .dashboard-metric__content {
            flex: 1;
            min-width: 0;
        }

        .dashboard-metric__label {
            font-size: 0.65rem;
        }

        .dashboard-metric__value {
            font-size: 1.25rem;
            word-break: break-word;
        }

        .dashboard-metric__muted {
            font-size: 0.7rem;
        }

        .analytics-chart {
            height: 220px;
        }

        .admin-table {
            min-width: 500px;
            width: 100%;
        }

        .admin-table th,
        .admin-table td {
            padding: 0.65rem;
            font-size: 0.8rem;
        }

        .dashboard-tasks__item {
            padding: 0.65rem;
            gap: 0.5rem;
            flex-direction: column;
            align-items: flex-start;
        }

        .dashboard-tasks__item > div {
            width: 100%;
        }

        .dashboard-tasks__item strong {
            font-size: 0.8rem;
        }

        .dashboard-tasks__item span {
            font-size: 0.7rem;
        }

        .dashboard-tasks__badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.45rem;
            align-self: flex-end;
        }
    }

    @media (max-width: 480px) {
        .admin-content {
            padding: 0;
        }

        .admin-card {
            padding: 0.85rem !important;
            max-width: 100%;
            overflow: hidden;
        }

        .admin-panel__body {
            padding: 0.85rem !important;
        }

        .dashboard-grid {
            gap: 0.5rem;
            width: 100%;
            max-width: 100%;
        }

        .dashboard-metric {
            gap: 0.5rem;
        }

        .dashboard-metric__icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .dashboard-metric__value {
            font-size: 1.1rem;
        }

        .analytics-chart {
            height: 200px;
        }

        .admin-table {
            min-width: 450px;
            width: 100%;
        }

        .admin-table th,
        .admin-table td {
            padding: 0.5rem;
            font-size: 0.75rem;
        }

        .admin-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }

        .dashboard-tasks__item {
            padding: 0.5rem;
        }

        .dashboard-tasks__item strong {
            font-size: 0.75rem;
        }

        .dashboard-tasks__item span {
            font-size: 0.65rem;
        }
    }
</style>
@endpush

