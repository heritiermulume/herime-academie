@extends('students.admin.layout')

@section('admin-title', 'Mes cours')
@section('admin-subtitle', 'Retrouvez l’ensemble de vos cours et reprenez-les quand vous le souhaitez.')

@section('admin-actions')
    <a href="{{ route('courses.index') }}" class="admin-btn primary">
        <i class="fas fa-compass me-2"></i>Explorer de nouveaux cours
    </a>
@endsection

@section('admin-content')
<div class="student-courses">
    <div class="student-courses__summary">
        <div class="course-summary-card">
            <span class="course-summary-card__label">Total</span>
            <strong class="course-summary-card__value">{{ number_format($courseSummary['total']) }}</strong>
            <small class="course-summary-card__hint">Tous vos cours inscrits</small>
        </div>
        <div class="course-summary-card">
            <span class="course-summary-card__label">En cours</span>
            <strong class="course-summary-card__value">{{ number_format($courseSummary['active']) }}</strong>
            <small class="course-summary-card__hint text-info">Cours actifs actuellement</small>
        </div>
        <div class="course-summary-card">
            <span class="course-summary-card__label">Terminés</span>
            <strong class="course-summary-card__value">{{ number_format($courseSummary['completed']) }}</strong>
            <small class="course-summary-card__hint text-success">Félicitations pour vos réussites</small>
        </div>
        <div class="course-summary-card">
            <span class="course-summary-card__label">Suspendus/Annulés</span>
            <strong class="course-summary-card__value">
                {{ number_format(($courseSummary['suspended'] ?? 0) + ($courseSummary['cancelled'] ?? 0)) }}
            </strong>
            <small class="course-summary-card__hint text-muted">À réactiver si nécessaire</small>
        </div>
    </div>

    <div class="admin-card">
        <form method="GET" class="student-courses__filters">
            <div class="filters-group">
                <div class="filters-field">
                    <label for="search" class="filters-label">Rechercher</label>
                    <div class="filters-input">
                        <i class="fas fa-search"></i>
                        <input type="text" id="search" name="q" placeholder="Titre du cours, formateur..."
                               value="{{ $search }}">
                    </div>
                </div>
                <div class="filters-field">
                    <label for="status" class="filters-label">Statut</label>
                    <select id="status" name="status">
                        @php
                            $statuses = [
                                'all' => 'Tous les statuts',
                                'active' => 'En cours',
                                'completed' => 'Terminé',
                                'suspended' => 'Suspendu',
                                'cancelled' => 'Annulé',
                            ];
                        @endphp
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="filters-actions">
                <button type="submit" class="admin-btn primary sm">
                    <i class="fas fa-filter me-2"></i>Filtrer
                </button>
                <a href="{{ route('student.courses') }}" class="admin-btn ghost sm">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <div class="student-courses__header">
            <div>
                <h3 class="admin-card__title">Tous mes cours</h3>
                <p class="admin-card__subtitle">
                    {{ $enrollments->total() }} cours inscrits
                    @if($statusFilter !== 'all')
                        · Filtre « {{ $statuses[$statusFilter] ?? $statusFilter }} »
                    @endif
                </p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="admin-btn soft">
                <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
            </a>
        </div>

        @if($enrollments->isEmpty())
            <div class="admin-empty-state">
                <i class="fas fa-book"></i>
                <p>Aucun cours trouvé avec ces critères.</p>
                <a href="{{ route('courses.index') }}" class="admin-btn primary sm mt-3">
                    Découvrir des cours
                </a>
            </div>
        @else
            <div class="table-responsive student-courses__table">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Cours</th>
                            <th>Formateur</th>
                            <th>Progression</th>
                            <th>Statut</th>
                            <th>Téléchargements</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollments as $enrollment)
                            @php($course = $enrollment->course)
                            @if(!$course)
                                @continue
                            @endif
                            <tr>
                                <td>
                                    <div class="student-course__info">
                                        <div class="student-course__thumb">
                                            @if($course->thumbnail_url)
                                                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                                            @else
                                                <span>{{ $course->initials }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('courses.show', $course->slug) }}" class="student-course__title">
                                                {{ $course->title }}
                                            </a>
                                            <div class="student-course__meta">
                                                <span><i class="fas fa-layer-group me-1"></i>{{ $course->lessons_count ?? $course->lessons()->count() }} leçons</span>
                                                @if($course->duration ?? false)
                                                    <span><i class="fas fa-clock me-1"></i>{{ $course->duration }} min</span>
                                                @endif
                                                @if($course->category?->name)
                                                    <span><i class="fas fa-tag me-1"></i>{{ $course->category->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="student-course__instructor">
                                        {{ $course->instructor->name ?? 'Formateur' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="student-course__progress">
                                        <div class="student-progress__bar">
                                            <span style="width: {{ $enrollment->progress }}%"></span>
                                        </div>
                                        <span class="student-progress__value">{{ $enrollment->progress }}%</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusMap = [
                                            'active' => ['label' => 'En cours', 'class' => 'info'],
                                            'completed' => ['label' => 'Terminé', 'class' => 'success'],
                                            'suspended' => ['label' => 'Suspendu', 'class' => 'warning'],
                                            'cancelled' => ['label' => 'Annulé', 'class' => 'error'],
                                        ];
                                        $statusData = $statusMap[$enrollment->status] ?? ['label' => ucfirst($enrollment->status), 'class' => 'info'];
                                    @endphp
                                    <span class="admin-badge {{ $statusData['class'] }}">
                                        <i class="fas fa-circle"></i>
                                        {{ $statusData['label'] }}
                                    </span>
                                </td>
                                <td>
                                    @if(($course->is_downloadable ?? false) && isset($course->user_downloads_count))
                                        <span class="student-course__downloads">
                                            <i class="fas fa-download me-1"></i>
                                            {{ $course->user_downloads_count }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="student-course__actions">
                                        <a href="{{ route('student.courses.learn', $course->slug) }}" class="admin-btn primary sm">
                                            <i class="fas fa-play me-1"></i>{{ $enrollment->progress > 0 ? 'Continuer' : 'Commencer' }}
                                        </a>
                                        <a href="{{ route('courses.show', $course->slug) }}" class="admin-btn ghost sm">
                                            Détails
                                        </a>
                                        @if(($course->is_downloadable ?? false) && isset($course->user_downloads_count))
                                            <a href="{{ route('courses.download', $course->slug) }}" class="admin-btn soft sm">
                                                <i class="fas fa-download me-1"></i>Ressources
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="student-courses__pagination">
                {{ $enrollments->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .admin-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        padding: 0.65rem 1.2rem;
        border: 1px solid transparent;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.2s ease;
        color: inherit;
    }

    .admin-btn.primary {
        background: linear-gradient(90deg, #2563eb, #4f46e5);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(37, 99, 235, 0.55);
    }

    .admin-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(37, 99, 235, 0.45);
    }

    .admin-btn.ghost {
        border-color: rgba(37, 99, 235, 0.18);
        color: #2563eb;
        background: transparent;
    }

    .admin-btn.soft {
        border-color: rgba(148, 163, 184, 0.4);
        background: rgba(148, 163, 184, 0.12);
        color: #0f172a;
        padding: 0.55rem 1rem;
        font-size: 0.85rem;
    }

    .admin-btn.sm {
        padding: 0.5rem 0.9rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
    }

    .student-courses {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }

    .student-courses__summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.25rem;
    }

    .course-summary-card {
        padding: 1.25rem;
        border-radius: 1.1rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        background: #ffffff;
        box-shadow: 0 18px 45px -35px rgba(15, 23, 42, 0.18);
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .course-summary-card__label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 600;
    }

    .course-summary-card__value {
        font-size: 1.65rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }

    .course-summary-card__hint {
        font-size: 0.8rem;
        color: #94a3b8;
    }

    .student-courses__filters {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .filters-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.25rem;
    }

    .filters-field {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filters-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 600;
    }

    .filters-input {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid rgba(148, 163, 184, 0.4);
        border-radius: 0.85rem;
        padding: 0.5rem 0.85rem;
        background: rgba(248, 250, 252, 0.7);
    }

    .filters-input input {
        border: none;
        background: transparent;
        width: 100%;
        outline: none;
        font-size: 0.95rem;
        color: #0f172a;
    }

    .filters-input i {
        color: #94a3b8;
    }

    .filters-field select {
        border: 1px solid rgba(148, 163, 184, 0.4);
        border-radius: 0.85rem;
        padding: 0.55rem 0.85rem;
        background: rgba(248, 250, 252, 0.7);
        font-size: 0.95rem;
    }

    .filters-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .student-courses__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .student-courses__table {
        margin-bottom: 1.5rem;
    }

    .student-course__info {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .student-course__thumb {
        width: 64px;
        height: 64px;
        border-radius: 1rem;
        overflow: hidden;
        background: rgba(148, 163, 184, 0.25);
        display: grid;
        place-items: center;
        font-weight: 700;
        color: #2563eb;
    }

    .student-course__thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .student-course__title {
        font-weight: 600;
        color: #0f172a;
        text-decoration: none;
    }

    .student-course__title:hover {
        color: #2563eb;
    }

    .student-course__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        font-size: 0.8rem;
        color: #64748b;
    }

    .student-course__instructor {
        font-weight: 600;
        color: #0f172a;
    }

    .student-course__progress {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .student-progress__bar {
        position: relative;
        width: 140px;
        height: 6px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.3);
        overflow: hidden;
    }

    .student-progress__bar span {
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: linear-gradient(90deg, #22c55e, #0ea5e9);
    }

    .student-progress__value {
        font-size: 0.85rem;
        font-weight: 600;
        color: #2563eb;
    }

    .student-course__downloads {
        font-size: 0.85rem;
        color: #2563eb;
        font-weight: 600;
    }

    .student-course__actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .student-courses__pagination {
        display: flex;
        justify-content: center;
    }

    @media (max-width: 1024px) {
        .student-courses__table table {
            min-width: 760px;
        }
    }

    @media (max-width: 640px) {
        .admin-btn {
            width: 100%;
        }

        .filters-actions {
            flex-direction: column;
        }

        .student-courses__header {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
@endpush

