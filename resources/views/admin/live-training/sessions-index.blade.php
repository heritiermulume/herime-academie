@extends('layouts.admin')

@section('title', 'Formations live')
@section('admin-title', 'Formations live')
@section('admin-subtitle', 'Historique des sessions, participants et conversations')
@section('admin-actions')
    <div class="live-training-actions">
        <a href="{{ route('admin.live-training.sessions.export', request()->query()) }}" class="btn btn-primary btn-sm live-training-actions__btn">
            <i class="fas fa-file-csv me-1"></i>Exporter CSV
        </a>
        <a href="{{ route('admin.live-training.sessions.export-summary-pdf', request()->query()) }}" class="btn btn-light btn-sm live-training-actions__btn">
            <i class="fas fa-file-pdf me-1"></i>Exporter PDF
        </a>
    </div>
@endsection

@section('admin-content')
    <div class="admin-stats-grid live-stats-grid mb-4">
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Sessions</p>
            <p class="admin-stat-card__value">{{ $stats['total_sessions'] }}</p>
            <p class="admin-stat-card__muted">Total de sessions</p>
        </div>
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Actives</p>
            <p class="admin-stat-card__value">{{ $stats['active_sessions'] }}</p>
            <p class="admin-stat-card__muted">Sessions en cours</p>
        </div>
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Participants</p>
            <p class="admin-stat-card__value">{{ $stats['total_participants'] }}</p>
            <p class="admin-stat-card__muted">Utilisateurs uniques</p>
        </div>
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Entrées</p>
            <p class="admin-stat-card__value">{{ $stats['total_participant_entries'] }}</p>
            <p class="admin-stat-card__muted">Présences enregistrées</p>
        </div>
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Messages</p>
            <p class="admin-stat-card__value">{{ $stats['total_messages'] }}</p>
            <p class="admin-stat-card__muted">Messages de chat traces</p>
        </div>
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Msgs / session</p>
            <p class="admin-stat-card__value">{{ $stats['avg_messages_per_session'] }}</p>
            <p class="admin-stat-card__muted">Moyenne sur le filtre courant</p>
        </div>
    </div>

    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <form method="GET" action="{{ route('admin.live-training.sessions') }}" class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Programme</label>
                    <input type="text" class="form-control" name="course" value="{{ request('course') }}" placeholder="Titre du programme">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Statut</label>
                    <select class="form-select" name="status">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="ended" @selected(request('status') === 'ended')>Terminee</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Intervenant (demarrage)</label>
                    <input type="text" class="form-control" name="started_by" value="{{ request('started_by') }}" placeholder="Nom intervenant">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Date debut</label>
                    <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Date fin</label>
                    <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Participants min.</label>
                    <input type="number" min="0" class="form-control" name="participants_min" value="{{ request('participants_min') }}" placeholder="0">
                </div>
                <div class="col-md-10 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-2"></i>Filtrer</button>
                    <a href="{{ route('admin.live-training.sessions') }}" class="btn btn-outline-secondary">Reinitialiser</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Programme</th>
                            <th>Salle</th>
                            <th>Debut</th>
                            <th>Fin</th>
                            <th>Statut</th>
                            <th>Participants</th>
                            <th>Messages</th>
                            <th class="text-end">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $session->course?->title ?? 'Programme supprime' }}</div>
                                    <small class="text-muted">Demarre par {{ $session->starter?->name ?? 'Inconnu' }}</small>
                                </td>
                                <td><code>{{ $session->room_name }}</code></td>
                                <td>{{ optional($session->started_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ optional($session->ended_at)->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>
                                    <span class="admin-chip {{ $session->status === 'active' ? 'admin-chip--success' : 'admin-chip--neutral' }}">
                                        {{ $session->status === 'active' ? 'Active' : 'Terminee' }}
                                    </span>
                                </td>
                                <td>{{ $session->participants_count }}</td>
                                <td>{{ $session->messages_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.live-training.sessions.show', $session) }}" class="btn btn-sm btn-light">
                                        <i class="fas fa-eye me-1"></i>Voir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Aucune session live enregistree.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-admin.pagination :paginator="$sessions" />
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3><i class="fas fa-chart-line me-2"></i>Évolution des formations live</h3>
        </div>
        <div class="admin-panel__body">
            <div class="d-flex justify-content-end mb-2">
                <select id="liveEvolutionPeriod" class="form-select form-select-sm" style="width: auto; min-width: 160px;">
                    <option value="week">Vue semaine</option>
                    <option value="month" selected>Vue mois</option>
                </select>
            </div>
            <div style="height: 360px;">
                <canvas id="liveEvolutionChart"></canvas>
            </div>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3><i class="fas fa-chart-bar me-2"></i>Statistiques agrégées par programme</h3>
        </div>
        <div class="admin-panel__body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Programme</th>
                            <th>Sessions</th>
                            <th>Participants total</th>
                            <th>Participants uniques</th>
                            <th>Moy. participants/session</th>
                            <th>Messages</th>
                            <th>Durée moy. présence</th>
                            <th>Taux de présence</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessionsByProgram as $program)
                            <tr>
                                <td class="fw-semibold">{{ $program['course_title'] }}</td>
                                <td>{{ $program['sessions_count'] }}</td>
                                <td>{{ $program['participants_total'] }}</td>
                                <td>{{ $program['participants_unique'] }}</td>
                                <td>{{ $program['avg_participants'] }}</td>
                                <td>{{ $program['messages_count'] }}</td>
                                <td>{{ gmdate('H:i:s', (int) $program['avg_duration_seconds']) }}</td>
                                <td>{{ $program['attendance_rate'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Aucune donnée agrégée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const weekly = @json($evolutionWeekly);
        const monthly = @json($evolutionMonthly);
        const ctx = document.getElementById('liveEvolutionChart');
        const periodSelect = document.getElementById('liveEvolutionPeriod');
        if (!ctx || !periodSelect || typeof Chart === 'undefined') return;

        const toDataset = function (series, title) {
            return {
                labels: series.labels && series.labels.length ? series.labels : ['-'],
                datasets: [
                    {
                        label: 'Sessions',
                        data: series.sessions && series.sessions.length ? series.sessions : [0],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.15)',
                        tension: 0.3
                    },
                    {
                        label: 'Participants',
                        data: series.participants && series.participants.length ? series.participants : [0],
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22, 163, 74, 0.15)',
                        tension: 0.3
                    },
                    {
                        label: 'Messages',
                        data: series.messages && series.messages.length ? series.messages : [0],
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.15)',
                        tension: 0.3
                    }
                ],
                title: title
            };
        };

        const weeklyData = toDataset(weekly, 'Évolution hebdomadaire');
        const monthlyData = toDataset(monthly, 'Évolution mensuelle');

        const chart = new Chart(ctx, {
            type: 'line',
            data: monthlyData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: monthlyData.title }
                }
            }
        });

        periodSelect.addEventListener('change', function () {
            const selected = periodSelect.value === 'week' ? weeklyData : monthlyData;
            chart.data.labels = selected.labels;
            chart.data.datasets = selected.datasets;
            chart.options.plugins.title.text = selected.title;
            chart.update();
        });
    });
</script>
@endpush

@push('styles')
<style>
    /* Cette page: stats en 2 colonnes sur desktop */
    .live-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    /* Actions d'export compactes et sur une ligne */
    .live-training-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: nowrap;
    }

    .live-training-actions__btn {
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .live-training-actions {
            display: flex;
            width: 100%;
            justify-content: flex-start;
            gap: 0.4rem;
        }

        .live-training-actions__btn {
            flex: 1 1 0;
            padding-left: 0.6rem;
            padding-right: 0.6rem;
            font-size: 0.78rem;
        }
    }
</style>
@endpush
