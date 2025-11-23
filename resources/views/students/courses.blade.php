@extends('students.admin.layout')

@section('admin-title', 'Mes cours')
@section('admin-subtitle', 'Retrouvez l’ensemble de vos cours et reprenez-les quand vous le souhaitez.')

@section('admin-actions')
    <a href="{{ route('courses.index') }}" class="admin-btn primary">
        <i class="fas fa-compass me-2"></i>Explorer de nouveaux cours
    </a>
@endsection

@section('admin-content')
<section class="admin-panel admin-panel--main">
    <div class="admin-panel__body">
        <div class="admin-stats-grid">
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Total</p>
                <p class="admin-stat-card__value">{{ number_format($courseSummary['total']) }}</p>
                <p class="admin-stat-card__muted">Tous vos cours</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">En cours</p>
                <p class="admin-stat-card__value">{{ number_format($courseSummary['active']) }}</p>
                <p class="admin-stat-card__muted">Cours actifs actuellement</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Terminés</p>
                <p class="admin-stat-card__value">{{ number_format($courseSummary['completed']) }}</p>
                <p class="admin-stat-card__muted">Félicitations pour vos réussites</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Suspendus/Annulés</p>
                <p class="admin-stat-card__value">
                    {{ number_format(($courseSummary['suspended'] ?? 0) + ($courseSummary['cancelled'] ?? 0)) }}
                </p>
                <p class="admin-stat-card__muted">À réactiver si nécessaire</p>
            </div>
        </div>
    </div>
</section>

<section class="admin-panel">
    <div class="admin-panel__header">
        <h3>
            <i class="fas fa-book-open me-2"></i>Tous mes cours
        </h3>
        <div class="admin-panel__actions">
            <a href="{{ route('student.dashboard') }}" class="admin-btn soft">
                <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
            </a>
        </div>
    </div>
    <div class="admin-panel__body">
        <form method="GET" class="modern-filters">
            <div class="modern-filters__row">
                <div class="modern-filters__search">
                    <div class="modern-filters__input-wrapper">
                        <i class="fas fa-search modern-filters__icon"></i>
                        <input 
                            type="text" 
                            id="search" 
                            name="q" 
                            class="modern-filters__input"
                            placeholder="Rechercher un cours, formateur..."
                            value="{{ $search }}"
                        >
                        @if($search)
                            <button type="button" class="modern-filters__clear" onclick="document.getElementById('search').value=''; this.closest('form').submit();">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="modern-filters__select-wrapper">
                    <select id="status" name="status" class="modern-filters__select">
                        @php
                            $statuses = [
                                'all' => 'Tous les statuts',
                                'active' => 'En cours',
                                'completed' => 'Terminé',
                                'suspended' => 'Suspendu',
                                'cancelled' => 'Annulé',
                            ];
                            $currentStatusFilter = $statusFilter ?? 'all';
                        @endphp
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($currentStatusFilter === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modern-filters__actions">
                    <button type="submit" class="modern-filters__btn modern-filters__btn--primary">
                        <i class="fas fa-filter"></i>
                        <span>Filtrer</span>
                    </button>
                    @if($search || (isset($statusFilter) && $statusFilter !== 'all'))
                        <a href="{{ route('student.courses') }}" class="modern-filters__btn modern-filters__btn--reset">
                            <i class="fas fa-times"></i>
                            <span>Réinitialiser</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>
        @php
            $statusesLabels = [
                'all' => 'Tous les statuts',
                'active' => 'En cours',
                'completed' => 'Terminé',
                'suspended' => 'Suspendu',
                'cancelled' => 'Annulé',
            ];
        @endphp
        <div class="courses-summary">
            <p class="courses-summary__text">
                <strong>{{ $enrollments->total() }}</strong> cours
                @if(isset($statusFilter) && $statusFilter !== 'all')
                    <span class="courses-summary__filter">
                        · Filtre actif : <strong>{{ $statusesLabels[$statusFilter] ?? $statusFilter }}</strong>
                    </span>
                @endif
            </p>
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
            <div class="student-course-list">
                @foreach($enrollments as $enrollment)
                    @php
                        $course = $enrollment->course ?? null;
                    @endphp
                    @if(!$course)
                        @continue
                    @endif
                    @php
                        $isPurchasedNotEnrolled = isset($enrollment->is_purchased_not_enrolled) && $enrollment->is_purchased_not_enrolled;
                        $statusKey = $enrollment->status ?? ($isPurchasedNotEnrolled ? 'purchased' : 'active');
                        $progress = $isPurchasedNotEnrolled ? 0 : ($enrollment->progress ?? 0);
                    @endphp
                    <div class="student-course-item">
                        <div class="student-course-item__meta">
                            <div class="student-course-item__thumbnail">
                                @if($course->thumbnail_url)
                                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                                @else
                                    <span>{{ $course->initials }}</span>
                                @endif
                            </div>
                            <div>
                                <h4>{{ $course->title }}</h4>
                                <p>
                                    {{ $course->instructor->name ?? 'Formateur' }}
                                    @if($course->category?->name)
                                        · {{ $course->category->name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="student-course-item__progress">
                            @if(!$isPurchasedNotEnrolled)
                                <div class="student-progress">
                                    <div class="student-progress__meta">
                                        <span>Progression</span>
                                        <span>{{ $progress }}%</span>
                                    </div>
                                    <div class="student-progress__bar">
                                        <span style="width: {{ $progress }}%"></span>
                                    </div>
                                </div>
                            @else
                                <div class="student-progress">
                                    <div class="student-progress__meta">
                                        <span>Statut</span>
                                        <span>Acheté</span>
                                    </div>
                                    <div class="student-progress__bar" style="background-color: rgba(30, 58, 138, 0.1);">
                                        <span style="width: 0%"></span>
                                    </div>
                                </div>
                            @endif
                            <div class="student-course-item__stats">
                                <span>
                                    <i class="fas fa-layer-group me-1"></i>
                                    {{ $course->lessons_count ?? $course->lessons()->count() }} leçons
                                </span>
                                @if($course->duration ?? false)
                                    <span>
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $course->duration }} min
                                    </span>
                                @endif
                                @if(($course->is_downloadable ?? false) && isset($course->user_downloads_count))
                                    <span>
                                        <i class="fas fa-download me-1"></i>
                                        {{ $course->user_downloads_count }} téléchargements
                                    </span>
                                @endif
                            </div>
                            <div class="student-course-item__status">
                                @if($isPurchasedNotEnrolled)
                                    <span class="admin-badge warning">
                                        <i class="fas fa-shopping-cart"></i>
                                        Acheté - Non inscrit
                                    </span>
                                @elseif($statusKey === 'active')
                                    <span class="admin-badge info">
                                        <i class="fas fa-circle"></i>
                                        En cours
                                    </span>
                                @elseif($statusKey === 'completed')
                                    <span class="admin-badge success">
                                        <i class="fas fa-circle"></i>
                                        Terminé
                                    </span>
                                @elseif($statusKey === 'suspended')
                                    <span class="admin-badge warning">
                                        <i class="fas fa-circle"></i>
                                        Suspendu
                                    </span>
                                @elseif($statusKey === 'cancelled')
                                    <span class="admin-badge error">
                                        <i class="fas fa-circle"></i>
                                        Annulé
                                    </span>
                                @else
                                    <span class="admin-badge info">
                                        <i class="fas fa-circle"></i>
                                        {{ ucfirst((string) $statusKey) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="student-course-item__actions">
                            @if($isPurchasedNotEnrolled)
                                <form action="{{ route('student.courses.enroll', $course->slug) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="dashboard">
                                    <button type="submit" class="admin-btn primary sm">
                                        <i class="fas fa-user-plus me-1"></i>S'inscrire
                                    </button>
                                </form>
                            @else
                                @if($course->is_downloadable ?? false)
                                    <a href="{{ route('courses.download', $course->slug) }}" class="admin-btn primary sm">
                                        <i class="fas fa-download me-1"></i>Télécharger
                                    </a>
                                @else
                                    <a href="{{ route('learning.course', $course->slug) }}" class="admin-btn success sm">
                                        <i class="fas fa-play me-1"></i>{{ $progress > 0 ? 'Continuer' : 'Commencer' }}
                                    </a>
                                @endif
                            @endif
                            <a href="{{ route('courses.show', $course->slug) }}" class="admin-btn ghost sm">
                                Détails
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <x-student.pagination :paginator="$enrollments" :showInfo="true" itemName="cours" />
        @endif
    </div>
</section>
@endsection

@push('styles')
<style>
    * {
        box-sizing: border-box;
    }

    .admin-main {
        max-width: 100%;
        overflow-x: hidden;
    }

    .admin-panel {
        max-width: 100%;
        overflow-x: hidden;
    }

    .admin-panel__body {
        max-width: 100%;
        overflow-x: hidden;
    }

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
        background: linear-gradient(90deg, var(--student-primary), #0b4f99);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(30, 58, 138, 0.55);
    }

    .admin-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(30, 58, 138, 0.45);
    }

    .admin-btn.success {
        background: linear-gradient(90deg, #22c55e, #16a34a);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(34, 197, 94, 0.55);
    }

    .admin-btn.success:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(34, 197, 94, 0.45);
        background: linear-gradient(90deg, #16a34a, #15803d);
    }

    .admin-btn.ghost {
        border-color: rgba(30, 58, 138, 0.3);
        color: #ffffff;
        background: var(--student-primary);
    }

    .admin-btn.ghost:hover {
        background: rgba(30, 58, 138, 0.9);
        border-color: var(--student-primary);
    }

    .admin-btn.soft {
        border-color: rgba(148, 163, 184, 0.4);
        background: rgba(148, 163, 184, 0.12);
        color: var(--student-primary-dark);
        padding: 0.55rem 1rem;
        font-size: 0.85rem;
    }

    .admin-btn.sm {
        padding: 0.5rem 0.9rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
    }

    .admin-btn.lg {
        padding: 0.82rem 1.65rem;
        font-size: 1.02rem;
        border-radius: 1rem;
    }

    .admin-btn.outline {
        border-color: rgba(30, 58, 138, 0.32);
        color: var(--student-primary);
        background: rgba(30, 58, 138, 0.08);
    }

    .admin-panel {
        margin-bottom: 1.5rem;
        background: var(--student-card-bg);
        border-radius: 1.25rem;
        box-shadow: 0 22px 45px -35px rgba(15, 23, 42, 0.25);
        border: 1px solid rgba(226, 232, 240, 0.7);
    }

    .admin-panel__header {
        padding: 1.25rem 1.75rem;
        background: linear-gradient(120deg, var(--student-primary) 0%, var(--student-primary-dark) 100%);
        color: #ffffff;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        align-items: center;
        border-radius: 1.25rem 1.25rem 0 0;
    }

    .admin-panel__header h2,
    .admin-panel__header h3,
    .admin-panel__header h4 {
        margin: 0;
        font-weight: 600;
        color: #ffffff;
    }

    .admin-panel__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .admin-panel__actions .admin-btn.soft {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .admin-panel__actions .admin-btn.soft:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.5);
    }
    
    .admin-panel__body {
        padding: 1.75rem;
    }

    .admin-panel--main .admin-panel__body {
        padding-left: 1.75rem;
        padding-right: 1.75rem;
    }

    .admin-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        align-items: stretch;
    }

    .admin-stat-card {
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.07) 0%, rgba(30, 58, 138, 0.15) 100%);
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        color: var(--student-primary-dark);
        border: 1px solid rgba(30, 58, 138, 0.1);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
        display: flex;
        flex-direction: column;
        min-height: 100%;
        overflow: hidden;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .admin-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 28px 60px -45px rgba(30, 58, 138, 0.35);
    }

    .admin-stat-card__label {
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.75rem;
        margin-bottom: 0.4rem;
        color: var(--student-primary);
        font-weight: 600;
        order: 1;
        word-wrap: break-word;
        overflow-wrap: break-word;
        line-height: 1.3;
    }

    .admin-stat-card__value {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 0.25rem;
        color: var(--student-primary-dark);
        line-height: 1.2;
        order: 2;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .admin-stat-card__muted {
        margin-top: auto;
        color: var(--student-muted);
        font-size: 0.8rem;
        order: 3;
        word-wrap: break-word;
        overflow-wrap: break-word;
        line-height: 1.4;
    }

    .modern-filters {
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
    }

    .modern-filters__row {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .modern-filters__search {
        flex: 1;
        min-width: 200px;
    }

    .modern-filters__input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        background: rgba(248, 250, 252, 0.8);
        border: 1px solid rgba(148, 163, 184, 0.3);
        border-radius: 0.75rem;
        padding: 0.6rem 0.9rem;
        transition: all 0.2s ease;
    }

    .modern-filters__input-wrapper:focus-within {
        border-color: var(--student-primary);
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
    }

    .modern-filters__icon {
        color: var(--student-muted);
        font-size: 0.9rem;
        margin-right: 0.5rem;
        flex-shrink: 0;
    }

    .modern-filters__input {
        flex: 1;
        border: none;
        background: transparent;
        outline: none;
        font-size: 0.9rem;
        color: var(--student-primary-dark);
        min-width: 0;
    }

    .modern-filters__input::placeholder {
        color: var(--student-muted);
    }

    .modern-filters__clear {
        background: none;
        border: none;
        color: var(--student-muted);
        cursor: pointer;
        padding: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
        margin-left: 0.5rem;
    }

    .modern-filters__clear:hover {
        background: rgba(148, 163, 184, 0.2);
        color: var(--student-primary-dark);
    }

    .modern-filters__select-wrapper {
        position: relative;
        min-width: 180px;
    }

    .modern-filters__select {
        width: 100%;
        padding: 0.6rem 2.5rem 0.6rem 0.9rem;
        background: rgba(248, 250, 252, 0.8);
        border: 1px solid rgba(148, 163, 184, 0.3);
        border-radius: 0.75rem;
        font-size: 0.9rem;
        color: var(--student-primary-dark);
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.9rem center;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .modern-filters__select:hover {
        border-color: var(--student-primary);
        background-color: #ffffff;
    }

    .modern-filters__select:focus {
        outline: none;
        border-color: var(--student-primary);
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
    }

    .modern-filters__actions {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .modern-filters__btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid transparent;
        transition: all 0.2s ease;
        cursor: pointer;
        white-space: nowrap;
    }

    .modern-filters__btn--primary {
        background: linear-gradient(90deg, var(--student-primary), #0b4f99);
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(30, 58, 138, 0.2);
    }

    .modern-filters__btn--primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
    }

    .modern-filters__btn--reset {
        background: rgba(148, 163, 184, 0.1);
        color: var(--student-primary-dark);
        border-color: rgba(148, 163, 184, 0.3);
    }

    .modern-filters__btn--reset:hover {
        background: rgba(148, 163, 184, 0.2);
        border-color: rgba(148, 163, 184, 0.4);
    }

    .courses-summary {
        margin-bottom: 1.25rem;
        padding: 0.75rem 1rem;
        background: rgba(30, 58, 138, 0.05);
        border-radius: 0.75rem;
        border: 1px solid rgba(30, 58, 138, 0.1);
    }

    .courses-summary__text {
        margin: 0;
        font-size: 0.9rem;
        color: var(--student-primary-dark);
    }

    .courses-summary__text strong {
        color: var(--student-primary);
        font-weight: 700;
    }

    .courses-summary__filter {
        color: var(--student-muted);
    }

    .student-course-list {
        display: flex;
        flex-direction: column;
        gap: 1.1rem;
    }

    .student-course-item {
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(0, 1.2fr) auto;
        gap: 1rem;
        padding: 1.15rem 1.35rem;
        border-radius: 1.1rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        background: rgba(248, 250, 252, 0.6);
        transition: box-shadow 0.18s ease, transform 0.18s ease;
        align-items: center;
    }

    .student-course-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 45px -35px rgba(30, 58, 138, 0.28);
    }

    .student-course-item__meta {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .student-course-item__thumbnail {
        width: 56px;
        height: 56px;
        border-radius: 1rem;
        overflow: hidden;
        background: rgba(30, 58, 138, 0.12);
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--student-primary);
        flex-shrink: 0;
    }

    .student-course-item__thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .student-course-item__meta h4 {
        font-size: 1rem;
        margin: 0 0 0.25rem;
        font-weight: 700;
        color: var(--student-primary-dark);
    }

    .student-course-item__meta p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--student-muted);
    }

    .student-course-item__progress {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .student-progress {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .student-progress__meta {
        display: flex;
        justify-content: space-between;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--student-muted);
        font-weight: 600;
    }

    .student-progress__bar {
        position: relative;
        width: 100%;
        height: 8px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.3);
        overflow: hidden;
    }

    .student-progress__bar span {
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--student-accent), var(--student-secondary));
    }

    .student-progress__value {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--student-primary);
        margin-top: 0.5rem;
        display: inline-block;
    }

    .student-course-item__stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        font-size: 0.8rem;
        color: #475569;
    }

    .student-course-item__status {
        display: flex;
        align-items: center;
    }

    .student-course-item__actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-end;
    }


    .admin-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .admin-badge i {
        font-size: 0.5rem;
    }

    .admin-badge.info {
        background: rgba(56, 189, 248, 0.15);
        color: #0284c7;
    }

    .admin-badge.success {
        background: rgba(34, 197, 94, 0.15);
        color: #16a34a;
    }

    .admin-badge.warning {
        background: rgba(250, 204, 21, 0.15);
        color: #ca8a04;
    }

    .admin-badge.error {
        background: rgba(239, 68, 68, 0.15);
        color: #dc2626;
    }

    @media (max-width: 991.98px) {
        .admin-panel {
            margin-bottom: 1rem;
        }

        .admin-panel--main .admin-panel__body {
            padding: 1rem 1rem !important;
        }

        .admin-panel__header {
            padding: 0.75rem 1rem;
        }

        .admin-panel__header h3 {
            font-size: 1rem;
        }

        .admin-panel__body {
            padding: 1rem;
            max-width: 100%;
            overflow-x: hidden;
        }

        .admin-stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem !important;
        }

        .admin-stat-card {
            padding: 0.65rem 0.75rem !important;
            min-width: 0;
        }

        .admin-stat-card__label {
            font-size: 0.7rem;
            line-height: 1.2;
        }

        .admin-stat-card__value {
            font-size: 1.5rem;
            line-height: 1.1;
        }

        .admin-stat-card__muted {
            font-size: 0.75rem;
            line-height: 1.3;
        }

        .student-course-list {
            gap: 0.875rem;
        }

        .student-course-item {
            padding: 1rem 1.15rem;
        }

        .student-course-item__thumbnail {
            width: 52px;
            height: 52px;
        }

        .modern-filters {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
        }

        .modern-filters__row {
            gap: 0.65rem;
        }

        .modern-filters__search {
            min-width: 180px;
        }

        .modern-filters__select-wrapper {
            min-width: 160px;
        }

        .modern-filters__btn {
            padding: 0.55rem 0.85rem;
            font-size: 0.8rem;
        }

        .courses-summary {
            padding: 0.65rem 0.85rem;
        }

        .courses-summary__text {
            font-size: 0.85rem;
        }
    }

    @media (max-width: 767.98px) {
        .admin-panel {
            margin-bottom: 0.75rem;
        }

        .admin-panel--main .admin-panel__body {
            padding: 0.5rem 0.75rem !important;
        }

        .admin-panel__header {
            padding: 0.5rem 0.75rem;
        }

        .admin-panel__header h3 {
            font-size: 0.95rem;
        }

        .admin-panel__body {
            padding: 0.5rem 0.25rem;
        }

        .admin-stats-grid {
            gap: 0.375rem !important;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }

        .admin-stat-card {
            padding: 0.5rem 0.5rem !important;
            min-width: 0;
        }

        .admin-stat-card__label {
            font-size: 0.65rem;
            line-height: 1.2;
            margin-bottom: 0.3rem;
            letter-spacing: 0.06em;
        }

        .admin-stat-card__value {
            font-size: 1.35rem;
            line-height: 1.1;
            margin-bottom: 0.2rem;
        }

        .admin-stat-card__muted {
            font-size: 0.7rem;
            line-height: 1.3;
        }

        .admin-btn {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }

        .admin-btn.sm {
            padding: 0.4rem 0.7rem;
            font-size: 0.75rem;
        }

        .admin-panel__actions .admin-btn {
            width: auto;
            font-size: 0.75rem;
            padding: 0.4rem 0.7rem;
        }

        .modern-filters {
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .modern-filters__row {
            flex-direction: column;
            gap: 0.75rem;
            align-items: stretch;
        }

        .modern-filters__search {
            min-width: 0;
            width: 100%;
        }

        .modern-filters__input-wrapper {
            padding: 0.55rem 0.8rem;
        }

        .modern-filters__input {
            font-size: 0.85rem;
        }

        .modern-filters__select-wrapper {
            min-width: 0;
            width: 100%;
        }

        .modern-filters__select {
            padding: 0.55rem 2.25rem 0.55rem 0.8rem;
            font-size: 0.85rem;
        }

        .modern-filters__actions {
            width: 100%;
            flex-direction: column;
            gap: 0.5rem;
        }

        .modern-filters__btn {
            width: 100%;
            justify-content: center;
            padding: 0.55rem 0.85rem;
            font-size: 0.8rem;
        }

        .courses-summary {
            padding: 0.5rem 0.75rem;
            margin-bottom: 1rem;
        }

        .courses-summary__text {
            font-size: 0.8rem;
        }

        .student-course-list {
            gap: 0.75rem;
        }

        .student-course-item {
            grid-template-columns: 1fr;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            align-items: flex-start;
        }

        .student-course-item__meta {
            gap: 0.75rem;
        }

        .student-course-item__thumbnail {
            width: 48px;
            height: 48px;
            font-size: 0.9rem;
        }

        .student-course-item__meta h4 {
            font-size: 0.9rem;
        }

        .student-course-item__meta p {
            font-size: 0.75rem;
        }

        .student-course-item__progress {
            gap: 0.5rem;
        }

        .student-progress__meta {
            font-size: 0.75rem;
        }

        .student-progress__bar {
            height: 6px;
        }

        .student-course-item__stats {
            font-size: 0.75rem;
            gap: 0.5rem;
        }

        .student-course-item__status {
            margin-top: 0.25rem;
        }

        .student-course-item__actions {
            width: 100%;
            align-items: stretch;
        }

        .student-course-item__actions .admin-btn {
            width: 100%;
        }

        .admin-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }


        .admin-empty-state {
            padding: 2rem 1rem;
        }

        .admin-empty-state i {
            font-size: 2rem;
        }

        .admin-empty-state p {
            font-size: 0.9rem;
        }
    }
</style>
@endpush

