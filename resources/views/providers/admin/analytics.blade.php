@extends('providers.admin.layout')

@section('admin-title', 'Analytics & indicateurs clés')
@section('admin-subtitle', 'Mesurez l’impact de vos contenus, le volume d’inscriptions et la satisfaction de vos clients.')

@section('admin-actions')
    <a href="{{ route('provider.contents.index') }}" class="admin-btn outline">
        <i class="fas fa-chalkboard me-2"></i>Retour aux contenus
    </a>
@endsection

@section('admin-content')
    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Total de contenus</p>
            <p class="admin-stat-card__value">{{ $courseStats->total_courses ?? 0 }}</p>
            <p class="admin-stat-card__muted">{{ $courseStats->published_courses ?? 0 }} publiés</p>
        </div>
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Clients inscrits</p>
            <p class="admin-stat-card__value">{{ number_format($courseStats->total_customers ?? 0) }}</p>
            <p class="admin-stat-card__muted">Sur l'ensemble de vos formations</p>
        </div>
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Note moyenne</p>
            <p class="admin-stat-card__value">{{ number_format($courseStats->average_rating ?? 0, 1) }}/5</p>
            <p class="admin-stat-card__muted">{{ number_format($totalReviews) }} avis reçus</p>
        </div>
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Revenus estimés (30 j)</p>
            <p class="admin-stat-card__value">{{ $estimatedRevenue }}</p>
            <p class="admin-stat-card__muted">Calcul basé sur vos ventes confirmées</p>
        </div>
    </div>

    <article class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-chart-line me-2"></i>Évolution des inscriptions
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="analytics-chart">
                <canvas id="enrollments-chart" height="280"></canvas>
                @if($enrollmentsByMonth->isEmpty())
                    <div class="analytics-empty">Pas encore de données suffisantes pour afficher une courbe.</div>
                @endif
            </div>
        </div>
    </article>

    <article class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-trophy me-2"></i>Contenus les plus performants
            </h3>
        </div>
        <div class="admin-panel__body">
            <ul class="analytics-top">
                @forelse($popularCourses as $course)
                    <li class="analytics-top__item">
                        <div>
                            <strong>{{ $course->title }}</strong>
                            <span>{{ number_format($course->enrollments_count) }} clients</span>
                        </div>
                        <div class="analytics-top__stats">
                            <span><i class="fas fa-star"></i> {{ number_format($course->reviews_avg_rating ?? 0, 1) }}</span>
                            <a href="{{ route('provider.contents.edit', $course->id) }}" class="admin-btn outline sm">Gérer</a>
                        </div>
                    </li>
                @empty
                    <li class="analytics-top__empty">Publiez plusieurs contenus pour voir vos statistiques détaillées.</li>
                @endforelse
            </ul>
        </div>
    </article>

    <article class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-lightbulb me-2"></i>À explorer
            </h3>
        </div>
        <div class="admin-panel__body">
            <ul class="analytics-insights">
                @foreach($insights as $insight)
                    <li class="analytics-insights__item">
                        <span class="analytics-insights__badge {{ $insight['type'] }}">{{ ucfirst($insight['type']) }}</span>
                        <div>
                            <strong>{{ $insight['title'] }}</strong>
                            <p>{{ $insight['description'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </article>
@endsection

@push('styles')
<style>
    .analytics-chart {
        padding: 0;
    }
    .analytics-empty {
        margin-top: 1rem;
        padding: 1.25rem;
        border-radius: 1rem;
        background: rgba(226, 232, 240, 0.6);
        text-align: center;
        color: #64748b;
    }
    .analytics-top {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .analytics-top__item {
        display: flex;
        justify-content: space-between;
        gap: 1.25rem;
        padding: 1rem;
        border-radius: 1rem;
        background: rgba(226, 232, 240, 0.5);
    }
    .analytics-top__item strong {
        color: #0f172a;
        display: block;
        margin-bottom: 0.25rem;
    }
    .analytics-top__item span {
        color: #64748b;
        font-size: 0.85rem;
    }
    .analytics-top__stats {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .analytics-top__stats span {
        color: #f59e0b;
        font-weight: 700;
    }
    .analytics-top__empty {
        text-align: center;
        padding: 1.5rem;
        border-radius: 1rem;
        background: rgba(226, 232, 240, 0.5);
        color: #94a3b8;
    }
    .analytics-insights {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
    }
    .analytics-insights__item {
        display: flex;
        gap: 0.85rem;
        align-items: flex-start;
        padding: 1rem;
        border-radius: 1rem;
        background: rgba(226, 232, 240, 0.4);
    }
    .analytics-insights__badge {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
    }
    .analytics-insights__badge.alert {
        background: rgba(220, 38, 38, 0.12);
        color: #b91c1c;
    }
    .analytics-insights__badge.info {
        background: rgba(14, 165, 233, 0.12);
        color: #0369a1;
    }
    .analytics-insights__badge.success {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }
    .analytics-insights__item strong {
        color: #0f172a;
        display: block;
        margin-bottom: 0.35rem;
    }
    .analytics-insights__item p {
        margin: 0;
        color: #64748b;
        font-size: 0.88rem;
    }

    @media (max-width: 767.98px) {
        .analytics-chart {
            padding: 0;
        }

        .analytics-top__item {
            padding: 0.75rem;
            gap: 0.75rem;
        }

        .analytics-top__stats {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .analytics-top__stats .admin-btn {
            width: 100%;
        }

        .analytics-insights__item {
            padding: 0.75rem;
            gap: 0.65rem;
        }

        .analytics-empty {
            padding: 1rem;
        }

        .analytics-top__empty {
            padding: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('enrollments-chart');
        if (!ctx) return;

        const labels = @json($enrollmentsByMonth->pluck('formatted_month'));
        const data = @json($enrollmentsByMonth->pluck('count'));

        if (!labels.length) {
            ctx.parentElement.classList.add('analytics-chart--empty');
            return;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Inscriptions',
                    data,
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.15)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#0284c7'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    });
</script>
@endpush









