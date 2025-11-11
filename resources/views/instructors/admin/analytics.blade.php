@extends('instructors.admin.layout')

@section('admin-title', 'Analytics & indicateurs clés')
@section('admin-subtitle', 'Mesurez l’impact de vos cours, le volume d’inscriptions et la satisfaction de vos étudiants.')

@section('admin-actions')
    <a href="{{ url('/instructor/courses') }}" class="btn btn-outline-primary">
        <i class="fas fa-chalkboard me-2"></i>Retour aux cours
    </a>
@endsection

@section('admin-content')
    <section class="dashboard-grid">
        <article class="admin-card">
            <div class="analytics-metric">
                <span>Total de cours</span>
                <strong>{{ $courseStats->total_courses ?? 0 }}</strong>
                <small>{{ $courseStats->published_courses ?? 0 }} publiés</small>
            </div>
        </article>
        <article class="admin-card">
            <div class="analytics-metric">
                <span>Étudiants inscrits</span>
                <strong>{{ number_format($courseStats->total_students ?? 0) }}</strong>
                <small>Sur l’ensemble de vos formations</small>
            </div>
        </article>
        <article class="admin-card">
            <div class="analytics-metric">
                <span>Note moyenne</span>
                <strong>{{ number_format($courseStats->average_rating ?? 0, 1) }}/5</strong>
                <small>{{ number_format($totalReviews) }} avis reçus</small>
            </div>
        </article>
        <article class="admin-card">
            <div class="analytics-metric">
                <span>Revenus estimés (30 j)</span>
                <strong>{{ $estimatedRevenue }}</strong>
                <small>Calcul basé sur vos ventes confirmées</small>
            </div>
        </article>
    </section>

    <section class="dashboard-columns">
        <article class="admin-card dashboard-columns__main">
            <div class="admin-card__header">
                <div>
                    <h2 class="admin-card__title">Évolution des inscriptions</h2>
                    <p class="admin-card__subtitle">Nombre d’étudiants inscrits par mois.</p>
                </div>
            </div>
            <div class="analytics-chart">
                <canvas id="enrollments-chart" height="280"></canvas>
                @if($enrollmentsByMonth->isEmpty())
                    <div class="analytics-empty">Pas encore de données suffisantes pour afficher une courbe.</div>
                @endif
            </div>
        </article>
        <aside class="dashboard-columns__side">
            <article class="admin-card">
                <div class="admin-card__header">
                    <div>
                        <h2 class="admin-card__title">Cours les plus performants</h2>
                        <p class="admin-card__subtitle">Top 5 selon le nombre d’inscriptions.</p>
                    </div>
                </div>
                <ul class="analytics-top">
                    @forelse($popularCourses as $course)
                        <li class="analytics-top__item">
                            <div>
                                <strong>{{ $course->title }}</strong>
                                <span>{{ number_format($course->enrollments_count) }} étudiants</span>
                            </div>
                            <div class="analytics-top__stats">
                                <span><i class="fas fa-star"></i> {{ number_format($course->reviews_avg_rating ?? 0, 1) }}</span>
                                <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-outline-primary btn-sm">Gérer</a>
                            </div>
                        </li>
                    @empty
                        <li class="analytics-top__empty">Publiez plusieurs cours pour voir vos statistiques détaillées.</li>
                    @endforelse
                </ul>
            </article>

            <article class="admin-card">
                <div class="admin-card__header">
                    <div>
                        <h2 class="admin-card__title">À explorer</h2>
                        <p class="admin-card__subtitle">Conseils personnalisés pour améliorer vos performances.</p>
                    </div>
                </div>
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
            </article>
        </aside>
    </section>
@endsection

@push('styles')
<style>
    .analytics-metric {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .analytics-metric span {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 700;
    }
    .analytics-metric strong {
        font-size: 1.9rem;
        color: #0f172a;
    }
    .analytics-metric small {
        color: #94a3b8;
    }
    .analytics-chart {
        padding: 0 1.5rem 1.5rem;
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
        padding: 0 1.5rem 1.5rem;
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
        padding: 0 1.5rem 1.5rem;
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


