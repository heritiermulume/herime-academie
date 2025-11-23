@extends('instructors.admin.layout')

@section('admin-title', 'Tableau de bord formateur')
@section('admin-subtitle', 'Suivez vos performances, vos cours et l’engagement de vos étudiants en temps réel.')

@section('admin-actions')
    <a href="{{ url('/instructor/courses/create') }}" class="admin-btn primary">
        <i class="fas fa-plus me-2"></i>Créer un cours
    </a>
    <a href="{{ url('/instructor/analytics') }}" class="admin-btn outline">
        <i class="fas fa-chart-line me-2"></i>Voir les analytics
    </a>
@endsection

@section('admin-content')
    <section class="dashboard-grid">
        @foreach($metrics as $metric)
            <article class="admin-card dashboard-grid__item">
                <div class="dashboard-metric">
                    <div class="dashboard-metric__icon" style="background: {{ $metric['accent'] }}20; color: {{ $metric['accent'] }};">
                        <i class="{{ $metric['icon'] }}"></i>
                    </div>
                    <div class="dashboard-metric__content">
                        <span class="dashboard-metric__label">{{ $metric['label'] }}</span>
                        <strong class="dashboard-metric__value">{{ $metric['value'] }}</strong>
                        <span class="dashboard-metric__trend {{ $metric['trend'] >= 0 ? 'is-up' : 'is-down' }}">
                            <i class="fas fa-arrow-{{ $metric['trend'] >= 0 ? 'up' : 'down' }}"></i>
                            {{ number_format(abs($metric['trend']), 1) }}% vs. 30 j
                        </span>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <article class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-clock me-2"></i>Activité récente
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="dashboard-activity">
                <div class="dashboard-activity__list">
                    <h3 class="dashboard-activity__title">Nouveaux cours</h3>
                    <ul class="dashboard-activity__items">
                        @forelse($recentCourses as $course)
                            <li class="dashboard-activity__item">
                                <div>
                                    <strong>{{ $course->title }}</strong>
                                    <span>{{ $course->category?->name ?? 'Sans catégorie' }}</span>
                                </div>
                                <span class="dashboard-activity__meta">{{ $course->created_at->diffForHumans() }}</span>
                            </li>
                        @empty
                            <li class="dashboard-activity__empty">Aucun cours récent.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="dashboard-activity__list">
                    <h3 class="dashboard-activity__title">Inscriptions</h3>
                    <ul class="dashboard-activity__items">
                        @forelse($recentEnrollments as $enrollment)
                            <li class="dashboard-activity__item">
                                <div>
                                    <strong>{{ $enrollment->user?->name }}</strong>
                                    <span>{{ $enrollment->course?->title }}</span>
                                </div>
                                <span class="dashboard-activity__meta">{{ $enrollment->created_at->diffForHumans() }}</span>
                            </li>
                        @empty
                            <li class="dashboard-activity__empty">Aucune inscription récente.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </article>

    <article class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-bolt me-2"></i>Actions rapides
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="dashboard-actions">
                <a href="{{ url('/instructor/courses/create') }}" class="dashboard-actions__item">
                    <i class="fas fa-rocket"></i>
                    <span>Lancer un nouveau cours</span>
                </a>
                <a href="{{ url('/instructor/students') }}" class="dashboard-actions__item">
                    <i class="fas fa-user-graduate"></i>
                    <span>Voir mes étudiants</span>
                </a>
                <a href="{{ url('/instructor/analytics') }}" class="dashboard-actions__item">
                    <i class="fas fa-chart-pie"></i>
                    <span>Performance des cours</span>
                </a>
                <a href="{{ url('/notifications') }}" class="dashboard-actions__item">
                    <i class="fas fa-envelope-open-text"></i>
                    <span>Centre de notifications</span>
                </a>
            </div>
        </div>
    </article>

    <article class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-tasks me-2"></i>Tâches à suivre
            </h3>
        </div>
        <div class="admin-panel__body">
            <ul class="dashboard-tasks">
                @forelse($pendingTasks as $task)
                    <li class="dashboard-tasks__item">
                        <div>
                            <strong>{{ $task['title'] }}</strong>
                            <span>{{ $task['description'] }}</span>
                        </div>
                        <span class="dashboard-tasks__badge {{ $task['type'] }}">{{ ucfirst($task['type']) }}</span>
                    </li>
                @empty
                    <li class="dashboard-tasks__empty">Aucune action urgente. Continuez sur cette lancée !</li>
                @endforelse
            </ul>
        </div>
    </article>
@endsection

@push('styles')
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
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
    }
    .dashboard-metric__value {
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        display: block;
    }
    .dashboard-metric__trend {
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .dashboard-metric__trend.is-up {
        color: #16a34a;
    }
    .dashboard-metric__trend.is-down {
        color: #dc2626;
    }

    .dashboard-activity {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
        padding: 0;
    }
    .dashboard-activity__list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .dashboard-activity__title {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .dashboard-activity__items {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .dashboard-activity__item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
        border-radius: 1rem;
        background: rgba(226, 232, 240, 0.35);
    }
    .dashboard-activity__item strong {
        display: block;
        color: #0f172a;
    }
    .dashboard-activity__item span {
        color: #64748b;
        font-size: 0.85rem;
    }
    .dashboard-activity__meta {
        font-size: 0.8rem;
        color: #0ea5e9;
        font-weight: 600;
    }
    .dashboard-activity__empty {
        padding: 1.25rem;
        border-radius: 1rem;
        background: rgba(226, 232, 240, 0.5);
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .dashboard-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.75rem;
    }
    .dashboard-actions__item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-radius: 1rem;
        background: rgba(15, 23, 42, 0.04);
        color: #0f172a;
        text-decoration: none;
        font-weight: 600;
        transition: background 0.2s ease, transform 0.2s ease;
    }
    .dashboard-actions__item i {
        font-size: 1.2rem;
        color: var(--instructor-primary);
    }
    .dashboard-actions__item:hover {
        background: rgba(14, 165, 233, 0.15);
        transform: translateY(-3px);
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
    }
    .dashboard-tasks__item strong {
        color: #0f172a;
        display: block;
        margin-bottom: 0.25rem;
    }
    .dashboard-tasks__item span {
        color: #64748b;
        font-size: 0.85rem;
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
    .dashboard-tasks__empty {
        text-align: center;
        padding: 1.25rem;
        border-radius: 1rem;
        background: rgba(226, 232, 240, 0.5);
        color: #94a3b8;
        font-size: 0.9rem;
    }


    @media (max-width: 640px) {
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

        .dashboard-metric__trend {
            font-size: 0.75rem;
        }


        .dashboard-activity {
            grid-template-columns: 1fr;
            gap: 0.75rem;
            padding: 0;
        }

        .dashboard-activity__title {
            font-size: 0.9rem;
        }

        .dashboard-activity__item {
            padding: 0.75rem;
            gap: 0.75rem;
        }

        .dashboard-activity__item strong {
            font-size: 0.85rem;
        }

        .dashboard-activity__item span {
            font-size: 0.75rem;
        }

        .dashboard-activity__meta {
            font-size: 0.7rem;
        }

        .dashboard-activity__empty {
            padding: 1rem;
            font-size: 0.85rem;
        }

        .dashboard-actions {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .dashboard-actions__item {
            padding: 0.65rem 0.85rem;
            font-size: 0.85rem;
        }

        .dashboard-actions__item i {
            font-size: 1rem;
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

        .dashboard-tasks__empty {
            padding: 1rem;
            font-size: 0.85rem;
        }
    }

    @media (max-width: 767.98px) {
        /* Réduire encore plus les paddings et margins sur mobile */
        .admin-panel {
            margin-bottom: 0.75rem;
        }

        .dashboard-columns {
            gap: 0.75rem;
        }


        .admin-panel__header {
            padding: 0.5rem 0.75rem;
        }

        .admin-panel__header h3 {
            font-size: 0.95rem;
        }

        .dashboard-activity {
            gap: 0.5rem;
        }

        .dashboard-activity__list {
            gap: 0.75rem;
        }

        .dashboard-activity__items {
            gap: 0.5rem;
        }

        .dashboard-activity__item {
            padding: 0.5rem;
            gap: 0.5rem;
        }

        .dashboard-activity__empty {
            padding: 0.75rem;
        }

        .dashboard-actions {
            gap: 0.375rem;
        }

        .dashboard-actions__item {
            padding: 0.5rem 0.65rem;
        }

        .dashboard-tasks {
            gap: 0.5rem;
        }

        .dashboard-tasks__item {
            padding: 0.5rem;
            gap: 0.5rem;
        }

        .dashboard-tasks__empty {
            padding: 0.75rem;
        }
    }
</style>
@endpush









