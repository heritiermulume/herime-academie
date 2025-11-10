@extends('layouts.app')

@section('title', 'Espace d’apprentissage - ' . $course->title)
@section('description', 'Progressez dans le cours ' . $course->title . ' grâce à notre espace d’apprentissage immersif et moderne.')

@push('styles')
<style>
:root {
    --learning-bg: #0f172a;
    --learning-sidebar: rgba(15, 23, 42, 0.92);
    --learning-card: rgba(15, 23, 42, 0.9);
    --learning-highlight: #38bdf8;
    --learning-muted: #94a3b8;
}

.learning-shell {
    background: #0b1120;
    min-height: 100vh;
    color: #e2e8f0;
}

.learning-shell .container-fluid {
    padding: clamp(0.75rem, 0.5rem + 1vw, 1.75rem);
}

.learning-grid {
    display: grid;
    grid-template-columns: minmax(280px, 320px) minmax(0, 1fr) minmax(260px, 320px);
    gap: clamp(1rem, 0.5rem + 1vw, 1.75rem);
}

.learning-column {
    display: flex;
    flex-direction: column;
    gap: clamp(1rem, 0.8rem + 0.5vw, 1.5rem);
}

.learning-card {
    background: var(--learning-card);
    border-radius: 18px;
    border: 1px solid rgba(56, 189, 248, 0.08);
    box-shadow: 0 25px 50px -12px rgba(8, 47, 73, 0.45);
    overflow: hidden;
}

.learning-card .card-body {
    padding: clamp(1.1rem, 1rem + 0.6vw, 1.6rem);
}

.learning-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.85rem;
    padding: clamp(1rem, 0.8rem + 0.6vw, 1.5rem);
    background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.12), transparent);
}

.learning-meta__item {
    background: rgba(15, 23, 42, 0.75);
    border: 1px solid rgba(148, 163, 184, 0.12);
    border-radius: 16px;
    padding: 0.9rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.learning-meta__item span:first-child {
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.7rem;
    color: var(--learning-muted);
}

.learning-progress-bar {
    position: relative;
    height: 12px;
    background: rgba(148, 163, 184, 0.15);
    border-radius: 999px;
    overflow: hidden;
}

.learning-progress-bar__fill {
    position: absolute;
    inset: 0;
    width: 0%;
    background: linear-gradient(90deg, rgba(56, 189, 248, 1) 0%, rgba(14, 116, 144, 1) 100%);
}

.lesson-outline {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.outline-section {
    border: 1px solid rgba(148, 163, 184, 0.1);
    border-radius: 16px;
    overflow: hidden;
    background: rgba(15, 23, 42, 0.75);
}

.outline-section__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.65rem;
    padding: 0.9rem 1.1rem;
    cursor: pointer;
    transition: background 0.25s ease;
}

.outline-section__header:hover {
    background: rgba(56, 189, 248, 0.12);
}

.outline-section__header.active {
    background: rgba(56, 189, 248, 0.18);
}

.outline-section__body {
    padding: 0.75rem 1rem 1rem;
    border-top: 1px solid rgba(148, 163, 184, 0.12);
}

.outline-lesson {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.65rem 0.8rem;
    border-radius: 14px;
    border: 1px solid transparent;
    transition: all 0.2s ease;
    position: relative;
}

.outline-lesson:hover {
    border-color: rgba(56, 189, 248, 0.4);
    background: rgba(56, 189, 248, 0.08);
}

.outline-lesson.active {
    background: rgba(14, 165, 233, 0.18);
    border-color: rgba(14, 165, 233, 0.55);
    box-shadow: inset 0 0 0 1px rgba(14, 165, 233, 0.25);
}

.outline-lesson__icon {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: rgba(56, 189, 248, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(56, 189, 248, 0.8);
}

.outline-lesson.completed .outline-lesson__icon {
    background: rgba(34, 197, 94, 0.15);
    color: rgba(34, 197, 94, 0.95);
}

.outline-lesson__title {
    font-weight: 600;
    color: #f8fafc;
    font-size: 0.93rem;
}

.outline-lesson__meta {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    font-size: 0.75rem;
    color: #94a3b8;
}

.learning-player-card {
    padding: clamp(1rem, 0.75rem + 0.8vw, 1.6rem);
    background: linear-gradient(160deg, rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.82));
    border-radius: 24px;
    border: 1px solid rgba(56, 189, 248, 0.12);
    box-shadow: 0 32px 55px -25px rgba(8, 47, 73, 0.55);
    overflow: hidden;
}

.learning-player-card .lesson-header {
    padding-bottom: clamp(0.75rem, 0.6rem + 0.6vw, 1.25rem);
    margin-bottom: clamp(0.75rem, 0.6rem + 0.6vw, 1.25rem);
    border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.lesson-header__title {
    font-size: clamp(1.3rem, 1.1rem + 0.7vw, 1.75rem);
    font-weight: 700;
    color: #f8fafc;
}

.lesson-header__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    font-size: 0.85rem;
    color: #94a3b8;
}

.player-shell {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background: #040913;
    margin-bottom: clamp(0.75rem, 0.6rem + 0.6vw, 1.2rem);
}

.player-shell .ratio {
    width: 100%;
}

.lesson-cta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.65rem;
}

.lesson-cta-row .btn {
    border-radius: 12px;
    font-weight: 600;
    min-height: 44px;
}

.lesson-cta-row .btn i {
    font-size: 0.9rem;
}

.lesson-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-bottom: 1rem;
}

.lesson-tab {
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 999px;
    padding: 0.4rem 0.9rem;
    font-size: 0.82rem;
    color: #cbd5f5;
    background: transparent;
    transition: all 0.2s ease;
}

.lesson-tab.active {
    background: rgba(56, 189, 248, 0.15);
    border-color: rgba(56, 189, 248, 0.4);
    color: #bae6fd;
}

.lesson-tab-content {
    display: none;
}

.lesson-tab-content.active {
    display: block;
}

.learning-insights {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.insight-card {
    background: rgba(15, 23, 42, 0.78);
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, 0.12);
    padding: clamp(1rem, 0.8rem + 0.6vw, 1.4rem);
}

.insight-card h6 {
    font-weight: 700;
    color: #e0f2fe;
}

.insight-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-top: 0.75rem;
}

.insight-list__item {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #cbd5f5;
}

.recommended-courses {
    display: grid;
    gap: 0.75rem;
}

.recommended-courses .recommended-item {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, 0.12);
    background: rgba(15, 23, 42, 0.65);
    transition: border 0.2s ease, transform 0.2s ease;
}

.recommended-courses .recommended-item:hover {
    border-color: rgba(56, 189, 248, 0.3);
    transform: translateY(-2px);
}

.recommended-thumb {
    width: 72px;
    height: 72px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
}

.recommended-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.recommended-content h6 {
    margin-bottom: 0.15rem;
    font-weight: 600;
    color: #f8fafc;
}

.recommended-meta {
    font-size: 0.75rem;
    color: #94a3b8;
    display: flex;
    gap: 0.65rem;
}

.recommended-actions {
    margin-top: 0.65rem;
    display: flex;
    gap: 0.4rem;
}

.recommended-actions .btn {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
    border-radius: 10px;
}

.mobile-outline-drawer {
    position: fixed;
    inset: 0;
    background: rgba(3, 7, 18, 0.7);
    backdrop-filter: blur(6px);
    display: none;
    align-items: flex-end;
    justify-content: center;
    z-index: 1060;
    padding: 0 0.75rem 0.75rem;
}

.mobile-outline-drawer.is-open {
    display: flex;
}

.mobile-outline-drawer__panel {
    width: min(100%, 540px);
    max-height: 88vh;
    background: rgba(15, 23, 42, 0.95);
    border-radius: 24px;
    border: 1px solid rgba(56, 189, 248, 0.2);
    box-shadow: 0 -22px 45px -30px rgba(8, 47, 73, 0.65);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.mobile-outline-drawer__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(56, 189, 248, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.mobile-outline-drawer__body {
    padding: 1rem 1.25rem 1.5rem;
    overflow-y: auto;
}

.mobile-outline-progress {
    background: rgba(8, 47, 73, 0.55);
    border-radius: 16px;
    border: 1px solid rgba(56, 189, 248, 0.12);
    padding: 0.85rem 1rem;
}

/* Responsive */
@media (max-width: 1199px) {
    .learning-grid {
        grid-template-columns: minmax(0, 1fr) minmax(260px, 320px);
    }

    .learning-grid .learning-column:nth-child(1) {
        display: none;
    }
}

@media (max-width: 991.98px) {
    .learning-shell .container-fluid {
        padding: 0;
    }

    .learning-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding-bottom: 1.5rem;
    }

    .learning-column {
        gap: 1rem;
    }

    .learning-player-card {
        border-radius: 0;
        box-shadow: none;
        border-left: 0;
        border-right: 0;
        padding: 0.85rem;
    }

    .player-shell {
        border-radius: 12px;
    }

    .lesson-header__title {
        font-size: 1.15rem;
    }

    .lesson-cta-row {
        gap: 0.5rem;
    }

    .lesson-cta-row .btn {
        flex: 1;
        min-width: 0;
        padding: 0.55rem;
    }

    .learning-meta {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .learning-column.secondary,
    .learning-column.sidebar {
        order: 3;
    }

    .outline-section__header {
        padding: 0.75rem 0.8rem;
    }

    .outline-lesson {
        padding: 0.55rem 0.65rem;
    }

    .outline-lesson__title {
        font-size: 0.85rem;
    }

    .lesson-tab {
        font-size: 0.78rem;
        padding: 0.35rem 0.75rem;
    }

    .learning-insights {
        gap: 0.75rem;
    }
}
</style>
@endpush

@section('content')
<div class="learning-shell">
    <div class="container-fluid">
        <div class="learning-topbar d-lg-none mb-3">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                    <div>
                        <p class="text-uppercase text-muted fw-semibold mb-0" style="font-size: 0.65rem;">Mon apprentissage</p>
                        <h6 class="mb-0 fw-bold text-white">{{ Str::limit($course->title, 40) }}</h6>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-light" id="mobile-outline-toggle">
                    <i class="fas fa-list-ul me-2"></i>Sommaire
                </button>
            </div>
            <div class="learning-progress-bar mt-3">
                <div class="learning-progress-bar__fill" style="width: {{ $progress['overall_progress'] ?? 0 }}%;"></div>
                        </div>
                    </div>

        <div class="learning-grid">
            {{-- Outline Column --}}
            <div class="learning-column sidebar d-none d-xl-flex">
                <div class="learning-card">
                    <div class="card-body pb-2">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <p class="text-uppercase text-muted fw-semibold mb-1" style="letter-spacing: 0.08em;">Progression</p>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="fs-4 fw-bold text-white">{{ $progress['overall_progress'] ?? 0 }}%</span>
                                    <span class="text-muted small">{{ $progress['completed_lessons'] ?? 0 }}/{{ $progress['total_lessons'] ?? 0 }} leçons</span>
                        </div>
                        </div>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill">
                                Niveau {{ ucfirst($course->level) }}
                            </span>
                        </div>
                        <div class="learning-progress-bar mb-2">
                            <div class="learning-progress-bar__fill" style="width: {{ $progress['overall_progress'] ?? 0 }}%;"></div>
                        </div>
                        <p class="text-muted small mb-0">Dernière mise à jour : {{ optional($course->updated_at)->diffForHumans() ?? 'non disponible' }}</p>
                    </div>
                    </div>

                <div class="learning-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0 fw-bold text-white">Plan du cours</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2">
                                {{ $course->sections?->count() ?? 0 }} sections
                            </span>
                        </div>

                        <div class="lesson-outline">
                            @foreach($course->sections as $section)
                                @php
                                    $sectionLessons = $section->lessons ?? collect();
                                    $isSectionOpen = $sectionLessons->contains(fn($lesson) => isset($activeLessonId) && $lesson->id === $activeLessonId);
                                @endphp
                                <div class="outline-section" data-section-id="{{ $section->id }}">
                                    <button class="outline-section__header {{ $isSectionOpen ? 'active' : '' }}" type="button">
                                        <div>
                                            <p class="text-uppercase small mb-1 text-muted fw-semibold">Section {{ $loop->iteration }}</p>
                                            <h6 class="mb-0 fw-semibold text-white">{{ $section->title }}</h6>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1">
                                                {{ $sectionLessons->count() }} leçons
                                            </span>
                                            <i class="fas fa-chevron-down ms-2 text-muted section-toggle-icon"></i>
                                    </div>
                                    </button>
                                
                                    <div class="outline-section__body {{ $isSectionOpen ? '' : 'd-none' }}">
                                        @foreach($sectionLessons as $sectionLesson)
                                        @php
                                                $isActive = isset($activeLessonId) && $sectionLesson->id === $activeLessonId;
                                                $isCompleted = $progress['completed_lessons_ids']->contains($sectionLesson->id ?? 0);
                                                $progressEntry = $progress['lesson_progress'][$sectionLesson->id] ?? null;
                                        @endphp
                                            <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $sectionLesson->id]) }}"
                                               class="outline-lesson {{ $isActive ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }}">
                                                <div class="outline-lesson__icon">
                                                    @switch($sectionLesson->type)
                                                        @case('video')
                                                            <i class="fas fa-play"></i>
                                                            @break
                                                        @case('pdf')
                                                        <i class="fas fa-file-pdf"></i>
                                                            @break
                                                        @case('quiz')
                                                            <i class="fas fa-star"></i>
                                                            @break
                                                        @case('text')
                                                            <i class="fas fa-align-left"></i>
                                                            @break
                                                        @default
                                                        <i class="fas fa-file"></i>
                                                    @endswitch
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="outline-lesson__title mb-1">{{ $sectionLesson->title }}</p>
                                                    <div class="outline-lesson__meta">
                                                        @if($sectionLesson->duration)
                                                            <span><i class="far fa-clock me-1"></i>{{ $sectionLesson->duration }} min</span>
                                                        @endif
                                                        @if($progressEntry)
                                                            <span><i class="fas fa-chart-line me-1 text-success"></i>{{ round($progressEntry->progress_percentage) }}%</span>
                                                        @endif
                                                @if($isCompleted)
                                                            <span class="text-success"><i class="fas fa-check-circle me-1"></i>Terminé</span>
                                                @endif
                                            </div>
                                        </div>
                                                <i class="fas fa-chevron-right text-muted small"></i>
                                            </a>
                                        @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Column --}}
            <div class="learning-column main">
                <div class="learning-player-card">
                    <div class="lesson-header">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                            <div>
                                <p class="text-uppercase small text-muted fw-semibold mb-1" style="letter-spacing: 0.08em;">
                                    @if(isset($activeLesson))
                                        Leçon {{ $progress['lesson_progress'][$activeLesson->id]->watched_seconds ?? 0 > 0 ? 'en cours' : 'nouvelle' }}
                                    @else
                                        Aperçu du cours
                                    @endif
                                </p>
                                <h1 class="lesson-header__title mb-2">
                                    {{ $activeLesson->title ?? 'Commencez votre apprentissage' }}
                                </h1>
                                <div class="lesson-header__meta">
                                    <span><i class="fas fa-layer-group me-1 text-info"></i>{{ $course->sections?->count() ?? 0 }} sections</span>
                                    <span><i class="fas fa-play-circle me-1 text-info"></i>{{ $progress['total_lessons'] ?? 0 }} leçons</span>
                                    @if(isset($activeLesson) && $activeLesson->duration)
                                        <span><i class="far fa-clock me-1 text-info"></i>{{ $activeLesson->duration }} min</span>
                                    @endif
                                    <span><i class="fas fa-signal me-1 text-info"></i>{{ ucfirst($course->level) }}</span>
                        </div>
                    </div>
                            <button class="btn btn-outline-light d-flex align-items-center gap-2">
                                <i class="fas fa-share-nodes"></i>
                                Partager
                        </button>
                    </div>
                </div>

                    <div class="player-shell mb-4">
                                <div class="ratio ratio-16x9">
                            @if(isset($activeLesson))
                                    @switch($activeLesson->type)
                                        @case('video')
                                        <x-plyr-player :lesson="$activeLesson" :course="$course" :is-mobile="false" />
                                            @break
                                        @case('pdf')
                                            <x-pdf-viewer :lesson="$activeLesson" />
                                            @break
                                        @case('text')
                                            <x-text-viewer :lesson="$activeLesson" />
                                            @break
                                        @case('quiz')
                                            <x-quiz-viewer :lesson="$activeLesson" :course="$course" />
                                            @break
                                        @default
                                        <div class="d-flex align-items-center justify-content-center bg-dark">
                                            <p class="text-white">Type de contenu non supporté</p>
                                            </div>
                                    @endswitch
                            @else
                                <div class="d-flex flex-column align-items-center justify-content-center bg-dark text-white p-5">
                                    <i class="fas fa-graduation-cap fa-3x mb-3 text-info"></i>
                                    <h4 class="mb-2">Sélectionnez une leçon pour commencer</h4>
                                    <p class="text-muted mb-4 text-center">
                                        Explorez le contenu du cours et lancez-vous dans une expérience immersive.
                                    </p>
                                    @if($course->sections->first()?->lessons->first())
                                        <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $course->sections->first()->lessons->first()->id]) }}"
                                           class="btn btn-info btn-lg px-4">
                                            <i class="fas fa-play me-2"></i>Commencer maintenant
                                        </a>
                                    @endif
                                </div>
                            @endif
                                </div>
                            </div>

                    <div class="lesson-cta-row mb-4">
                        <div class="d-flex gap-2 flex-wrap">
                                    @if(isset($previousLesson))
                                <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $previousLesson->id]) }}"
                                   class="btn btn-outline-light d-flex align-items-center gap-2">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>Précédent</span>
                                </a>
                            @endif

                            @if(isset($nextLesson))
                                <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $nextLesson->id]) }}"
                                   class="btn btn-info d-flex align-items-center gap-2">
                                    <span>Suivant</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            @endif

                            @if(isset($activeLesson))
                                <button class="btn btn-success d-flex align-items-center gap-2"
                                        @if($progress['completed_lessons_ids']->contains($activeLesson->id))
                                            disabled
                                    @else
                                            onclick="markAsComplete({{ $activeLesson->id }})"
                                    @endif
                                >
                                    <i class="fas fa-check"></i>
                                    {{ $progress['completed_lessons_ids']->contains($activeLesson->id) ? 'Leçon terminée' : 'Marquer comme terminé' }}
                                </button>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2 ms-auto">
                            <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-info-circle me-2"></i>Détails du cours
                            </a>
                            <button class="btn btn-outline-secondary">
                                <i class="fas fa-download me-2"></i>Ressources
                            </button>
                        </div>
                    </div>

                    <div class="lesson-tabs">
                        <button class="lesson-tab active" data-tab="overview">
                            <i class="fas fa-book-open me-2"></i>Aperçu
                                        </button>
                        @isset($activeLesson)
                            <button class="lesson-tab" data-tab="notes">
                                <i class="fas fa-pen-to-square me-2"></i>Notes
                            </button>
                            <button class="lesson-tab" data-tab="resources">
                                <i class="fas fa-folder-open me-2"></i>Ressources
                            </button>
                            <button class="lesson-tab" data-tab="discussion">
                                <i class="fas fa-comments me-2"></i>Discussion
                            </button>
                        @endisset
                    </div>

                    <div class="lesson-tab-panels">
                        <div class="lesson-tab-content active" id="tab-overview">
                            @isset($activeLesson)
                                <div class="mb-4">
                                    <h5 class="text-white fw-semibold mb-3">À propos de cette leçon</h5>
                                    <div class="text-muted">
                                        {!! nl2br(e($activeLesson->description ?? 'Aucune description fournie pour cette leçon.')) !!}
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-sm-4">
                                        <div class="insight-card h-100">
                                            <span class="text-uppercase text-muted small fw-semibold">Type de contenu</span>
                                            <h6 class="mt-2 mb-0 text-info">
                                                <i class="fas fa-layer-group me-2"></i>{{ ucfirst($activeLesson->type) }}
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="insight-card h-100">
                                            <span class="text-uppercase text-muted small fw-semibold">Durée prévue</span>
                                            <h6 class="mt-2 mb-0 text-warning">
                                                <i class="far fa-clock me-2"></i>{{ $activeLesson->duration ?? '–' }} min
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="insight-card h-100">
                                            <span class="text-uppercase text-muted small fw-semibold">Progression</span>
                                            <h6 class="mt-2 mb-0 text-success">
                                                <i class="fas fa-chart-line me-2"></i>
                                                {{ isset($progress['lesson_progress'][$activeLesson->id]) ? round($progress['lesson_progress'][$activeLesson->id]->progress_percentage) : 0 }}%
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                                    @else
                                <p class="text-muted mb-0">Choisissez une leçon pour afficher son contenu et ses ressources.</p>
                            @endisset
                        </div>

                        @isset($activeLesson)
                            <div class="lesson-tab-content" id="tab-notes">
                                <p class="text-muted mb-3">Prenez des notes personnelles pour cette leçon (fonctionnalité à venir).</p>
                                <button class="btn btn-outline-light btn-sm disabled">Ajouter une note</button>
                            </div>
                            <div class="lesson-tab-content" id="tab-resources">
                                <p class="text-muted mb-0">Les ressources téléchargeables et documents complémentaires apparaîtront ici dès qu’elles seront disponibles.</p>
                            </div>
                            <div class="lesson-tab-content" id="tab-discussion">
                                <p class="text-muted">Rejoignez la discussion et échangez avec les autres apprenants (module communauté à venir).</p>
                                <button class="btn btn-outline-info btn-sm disabled">Ouvrir la discussion</button>
                            </div>
                        @endisset
                    </div>
                </div>
            </div>

            {{-- Insights Column --}}
            <div class="learning-column secondary">
                <div class="learning-card">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="letter-spacing: 0.12em;">
                            Statistiques du cours
                        </h6>
                        <div class="learning-meta">
                            <div class="learning-meta__item">
                                <span>Étudiants</span>
                                <strong>{{ $courseStats['total_students'] ?? 0 }}</strong>
                            </div>
                            <div class="learning-meta__item">
                                <span>Durée totale</span>
                                <strong>{{ $courseStats['total_duration'] ?? 0 }} min</strong>
                            </div>
                            <div class="learning-meta__item">
                                <span>Leçons vidéos</span>
                                <strong>{{ $courseStats['video_lessons'] ?? 0 }}</strong>
                            </div>
                            <div class="learning-meta__item">
                                <span>Quiz</span>
                                <strong>{{ $courseStats['quiz_lessons'] ?? 0 }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="learning-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0 text-white fw-bold">Prochaines étapes</h6>
                            <a href="{{ route('courses.show', $course->slug) }}#curriculum" class="text-info small">Voir le plan complet</a>
                        </div>
                        <ul class="insight-list mb-0">
                                    @if(isset($nextLesson))
                                <li class="insight-list__item">
                                    <span>Leçon suivante</span>
                                    <strong>{{ Str::limit($nextLesson->title, 45) }}</strong>
                                </li>
                                    @endif
                            <li class="insight-list__item">
                                <span>Cours complétés</span>
                                <strong>{{ $progress['completed_lessons'] ?? 0 }}/{{ $progress['total_lessons'] ?? 0 }}</strong>
                            </li>
                            <li class="insight-list__item">
                                <span>Progression moyenne des étudiants</span>
                                <strong>{{ $courseStats['average_progress'] ?? 0 }}%</strong>
                            </li>
                            <li class="insight-list__item">
                                <span>Note moyenne</span>
                                <strong>{{ $courseStats['average_rating'] ?? 0 }}/5</strong>
                            </li>
                        </ul>
                                </div>
                            </div>

                @if(!empty($recommendedCourses) && $recommendedCourses->count())
                    <div class="learning-card">
                        <div class="card-body">
                            <h6 class="text-white fw-bold mb-3">Cours à explorer ensuite</h6>
                            <div class="recommended-courses">
                                @foreach($recommendedCourses as $recommended)
                                    <a href="{{ route('courses.show', $recommended->slug) }}" class="recommended-item">
                                        <div class="recommended-thumb">
                                            <img src="{{ $recommended->thumbnail_url ?? 'https://source.unsplash.com/300x200/?learning' }}" alt="{{ $recommended->title }}">
                                        </div>
                                        <div class="recommended-content flex-grow-1">
                                            <h6>{{ $recommended->title }}</h6>
                                            <div class="recommended-meta">
                                                <span><i class="fas fa-user me-1"></i>{{ $recommended->instructor?->name }}</span>
                                                <span><i class="fas fa-signal me-1"></i>{{ ucfirst($recommended->level) }}</span>
                                            </div>
                                            <div class="recommended-actions">
                                                <span class="badge bg-primary bg-opacity-10 text-info border-0">
                                                    {{ $recommended->stats['total_lessons'] ?? 0 }} leçons
                                                </span>
                                                <span class="badge bg-primary bg-opacity-10 text-warning border-0">
                                                    {{ $recommended->stats['average_rating'] ?? 0 }}/5
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                            </div>
                        @endif
            </div>
                    </div>
                </div>

    {{-- Mobile Outline Drawer --}}
    <div class="mobile-outline-drawer d-lg-none" id="mobile-outline-drawer" aria-hidden="true">
        <div class="mobile-outline-drawer__panel">
            <div class="mobile-outline-drawer__header">
                <h5 class="mb-0 fw-semibold text-white">Contenu du cours</h5>
                <button type="button" class="btn btn-sm btn-outline-light" id="mobile-outline-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mobile-outline-drawer__body">
                <div class="mobile-outline-progress mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-uppercase text-muted fw-semibold">Progression</span>
                        <span class="fw-bold text-white">{{ $progress['overall_progress'] ?? 0 }}%</span>
                            </div>
                    <div class="learning-progress-bar">
                        <div class="learning-progress-bar__fill" style="width: {{ $progress['overall_progress'] ?? 0 }}%;"></div>
                            </div>
                    <span class="small text-muted d-block mt-1">{{ $progress['completed_lessons'] ?? 0 }} / {{ $progress['total_lessons'] ?? 0 }} leçons terminées</span>
                        </div>

                <div class="lesson-outline">
                                @foreach($course->sections as $section)
                        @php
                            $sectionLessons = $section->lessons ?? collect();
                            $isSectionOpen = $sectionLessons->contains(fn($lesson) => isset($activeLessonId) && $lesson->id === $activeLessonId);
                        @endphp
                        <div class="outline-section {{ $isSectionOpen ? 'open' : '' }}" data-section-id="mobile-{{ $section->id }}">
                            <button class="outline-section__header {{ $isSectionOpen ? 'active' : '' }}" type="button">
                                            <div>
                                    <p class="text-uppercase small mb-1 text-muted fw-semibold">Section {{ $loop->iteration }}</p>
                                    <h6 class="mb-0 fw-semibold text-white">{{ $section->title }}</h6>
                                            </div>
                                <div class="text-end">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1">
                                        {{ $sectionLessons->count() }} leçons
                                    </span>
                                    <i class="fas fa-chevron-down ms-2 text-muted section-toggle-icon"></i>
                                        </div>
                            </button>
                                    
                            <div class="outline-section__body {{ $isSectionOpen ? '' : 'd-none' }}">
                                @foreach($sectionLessons as $sectionLesson)
                                            @php
                                        $isActive = isset($activeLessonId) && $sectionLesson->id === $activeLessonId;
                                        $isCompleted = $progress['completed_lessons_ids']->contains($sectionLesson->id ?? 0);
                                        $progressEntry = $progress['lesson_progress'][$sectionLesson->id] ?? null;
                                            @endphp
                                    <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $sectionLesson->id]) }}"
                                       class="outline-lesson {{ $isActive ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }}">
                                        <div class="outline-lesson__icon">
                                            @switch($sectionLesson->type)
                                                @case('video')
                                                    <i class="fas fa-play"></i>
                                                    @break
                                                @case('pdf')
                                                            <i class="fas fa-file-pdf"></i>
                                                    @break
                                                @case('quiz')
                                                    <i class="fas fa-star"></i>
                                                    @break
                                                @case('text')
                                                    <i class="fas fa-align-left"></i>
                                                    @break
                                                @default
                                                            <i class="fas fa-file"></i>
                                            @endswitch
                                                    </div>
                                                    <div class="flex-grow-1">
                                            <p class="outline-lesson__title mb-1">{{ $sectionLesson->title }}</p>
                                            <div class="outline-lesson__meta">
                                                @if($sectionLesson->duration)
                                                    <span><i class="far fa-clock me-1"></i>{{ $sectionLesson->duration }} min</span>
                                                @endif
                                                @if($progressEntry)
                                                    <span><i class="fas fa-chart-line me-1 text-success"></i>{{ round($progressEntry->progress_percentage) }}%</span>
                                                    @endif
                                                </div>
                                            </div>
                                        <i class="fas fa-chevron-right text-muted small"></i>
                                    </a>
                                            @endforeach
                                    </div>
                                </div>
                                @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const sectionHeaders = document.querySelectorAll('.outline-section__header');
    sectionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const section = header.closest('.outline-section');
            const body = section.querySelector('.outline-section__body');
            const icon = header.querySelector('.section-toggle-icon');
            const isOpen = !body.classList.contains('d-none');

            header.classList.toggle('active', !isOpen);
            body.classList.toggle('d-none', isOpen);
            icon?.classList.toggle('rotate-180', !isOpen);
        });
    });

    document.querySelectorAll('.lesson-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            document.querySelectorAll('.lesson-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.lesson-tab-content').forEach(panel => panel.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(`tab-${target}`).classList.add('active');
        });
    });

    const mobileDrawer = document.getElementById('mobile-outline-drawer');
    const mobileToggle = document.getElementById('mobile-outline-toggle');
    const mobileClose = document.getElementById('mobile-outline-close');

    const setDrawerState = (isOpen) => {
        mobileDrawer?.classList.toggle('is-open', isOpen);
        document.body.classList.toggle('overflow-hidden', isOpen);
    };

    mobileToggle?.addEventListener('click', () => setDrawerState(true));
    mobileClose?.addEventListener('click', () => setDrawerState(false));

    mobileDrawer?.addEventListener('click', (event) => {
        if (event.target === mobileDrawer) {
            setDrawerState(false);
        }
    });

    const progressFill = document.querySelector('.learning-progress-bar__fill');
    if (progressFill) {
        requestAnimationFrame(() => {
            const width = progressFill.style.width;
            progressFill.style.width = '0%';
            requestAnimationFrame(() => progressFill.style.width = width);
        });
    }
});

function markAsComplete(lessonId) {
    fetch(`/learning/courses/{{ $course->slug }}/lessons/${lessonId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(() => alert('Erreur lors de la mise à jour de la leçon.'));
}
</script>
@endpush
