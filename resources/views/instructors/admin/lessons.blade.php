@extends('instructors.admin.layout')

@php
    $sectionsCount = $sections->count();
    $lessonsCount = $totalLessons;
    $hours = intdiv($totalDuration, 60);
    $minutes = $totalDuration % 60;
    $formattedDuration = $totalDuration > 0 ? sprintf('%02dh%02d', $hours, $minutes) : '—';
@endphp

@section('admin-title', 'Structure du cours')
@section('admin-subtitle')
    Organisez les sections et les leçons de votre formation « {{ $course->title }} ».
@endsection

@section('admin-actions')
    <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-outline-primary">
        <i class="fas fa-pen me-2"></i>Modifier les informations du cours
    </a>
    <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour à mes cours
    </a>
@endsection

@section('admin-content')
    <section class="course-lessons-metrics">
        <div class="course-lessons-metric">
            <span class="course-lessons-metric__label">Sections</span>
            <strong class="course-lessons-metric__value">{{ $sectionsCount }}</strong>
            <small class="course-lessons-metric__hint">Regroupez vos contenus par grands chapitres</small>
        </div>
        <div class="course-lessons-metric">
            <span class="course-lessons-metric__label">Leçons</span>
            <strong class="course-lessons-metric__value">{{ $lessonsCount }}</strong>
            <small class="course-lessons-metric__hint">Vidéos, ressources et quiz publiés</small>
        </div>
        <div class="course-lessons-metric">
            <span class="course-lessons-metric__label">Durée estimée</span>
            <strong class="course-lessons-metric__value">{{ $formattedDuration }}</strong>
            <small class="course-lessons-metric__hint">Temps total cumulé des leçons</small>
        </div>
    </section>

    <section class="course-lessons-details">
        @if($sections->isEmpty())
            <div class="course-lessons-empty">
                <i class="fas fa-layer-group"></i>
                <h3>Aucune section définie</h3>
                <p>Ajoutez votre première section depuis la page d’édition du cours pour commencer à structurer votre formation.</p>
                <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Créer une section
                </a>
            </div>
        @else
            @foreach($sections as $index => $section)
                <article class="course-section">
                    <header class="course-section__header">
                        <div>
                            <span class="course-section__index">Section {{ $index + 1 }}</span>
                            <h3 class="course-section__title">{{ $section->title }}</h3>
                            @if($section->description)
                                <p class="course-section__description">{{ $section->description }}</p>
                            @endif
                        </div>
                        <div class="course-section__meta">
                            <span><i class="fas fa-book"></i>{{ $section->lessons->count() }} leçons</span>
                            <span><i class="fas fa-clock"></i>{{ $section->lessons->sum('duration') }} min</span>
                        </div>
                    </header>

                    <div class="course-lessons">
                        @forelse($section->lessons as $lessonIndex => $lesson)
                            <div class="course-lesson">
                                <div class="course-lesson__icon">
                                    <i class="{{ 
                                        match($lesson->type) {
                                            'video' => 'fas fa-play-circle',
                                            'quiz' => 'fas fa-question-circle',
                                            'assignment' => 'fas fa-clipboard-check',
                                            default => 'fas fa-file-alt',
                                        }
                                    }}"></i>
                                </div>
                                <div class="course-lesson__body">
                                    <div class="course-lesson__title">
                                        <span class="course-lesson__index">Leçon {{ $lessonIndex + 1 }}</span>
                                        <h4>{{ $lesson->title }}</h4>
                                    </div>
                                    @if($lesson->description)
                                        <p class="course-lesson__description">{{ $lesson->description }}</p>
                                    @endif
                                    <div class="course-lesson__tags">
                                        <span class="course-lesson__tag type-{{ $lesson->type }}">
                                            {{ [
                                                'video' => 'Vidéo',
                                                'text' => 'Contenu texte',
                                                'quiz' => 'Quiz',
                                                'assignment' => 'Devoir',
                                            ][$lesson->type] ?? 'Leçon' }}
                                        </span>
                                        @if($lesson->duration)
                                            <span class="course-lesson__tag">
                                                <i class="fas fa-clock me-1"></i>{{ $lesson->duration }} min
                                            </span>
                                        @endif
                                        @if($lesson->is_preview)
                                            <span class="course-lesson__tag preview">
                                                <i class="fas fa-eye me-1"></i>Aperçu gratuit
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="course-lesson__actions">
                                    <a href="{{ route('instructor.courses.edit', $course) }}#lesson-{{ $lesson->id }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="course-lesson course-lesson--empty">
                                <p>Aucune leçon dans cette section pour le moment.</p>
                                <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-1"></i>Ajouter une leçon
                                </a>
                            </div>
                        @endforelse
                    </div>
                </article>
            @endforeach
        @endif
    </section>
@endsection

@push('styles')
<style>
    .course-lessons-metrics {
        display: grid;
        gap: 1.2rem;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        margin-bottom: 1.8rem;
    }

    .course-lessons-metric {
        background: #ffffff;
        border-radius: 18px;
        padding: 1.35rem 1.5rem;
        box-shadow: 0 20px 45px -35px rgba(15, 23, 42, 0.3);
        border: 1px solid rgba(226, 232, 240, 0.7);
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .course-lessons-metric__label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 700;
    }

    .course-lessons-metric__value {
        font-size: clamp(1.6rem, 1.4rem + 1vw, 2.2rem);
        font-weight: 700;
        color: #0f172a;
    }

    .course-lessons-metric__hint {
        color: #94a3b8;
        font-size: 0.85rem;
    }

    .course-lessons-details {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .course-lessons-empty {
        border: 1px dashed rgba(148, 163, 184, 0.5);
        border-radius: 18px;
        padding: 2.5rem;
        text-align: center;
        background: rgba(241, 245, 249, 0.6);
        display: grid;
        gap: 1rem;
        place-items: center;
        color: #64748b;
    }

    .course-lessons-empty i {
        font-size: 2rem;
        color: #0ea5e9;
    }

    .course-lessons-empty h3 {
        margin: 0;
        color: #0f172a;
    }

    .course-section {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.7);
        box-shadow: 0 25px 50px -35px rgba(15, 23, 42, 0.35);
        padding: 1.75rem 1.9rem;
        display: flex;
        flex-direction: column;
        gap: 1.1rem;
    }

    .course-section__header {
        display: flex;
        justify-content: space-between;
        gap: 1.25rem;
        flex-wrap: wrap;
    }

    .course-section__index {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: rgba(14, 165, 233, 0.12);
        color: #0369a1;
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .course-section__title {
        margin: 0.35rem 0 0.5rem;
        font-size: clamp(1.2rem, 1.1rem + 0.5vw, 1.5rem);
        color: #0f172a;
    }

    .course-section__description {
        margin: 0;
        color: #64748b;
        max-width: 720px;
    }

    .course-section__meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #475569;
    }

    .course-section__meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-weight: 600;
    }

    .course-section__meta i {
        color: #0ea5e9;
    }

    .course-lessons {
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
    }

    .course-lesson {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 1.1rem;
        padding: 1.05rem 1.2rem;
        border-radius: 16px;
        border: 1px solid rgba(226, 232, 240, 0.7);
        background: rgba(248, 250, 252, 0.85);
        align-items: center;
    }

    .course-lesson__icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(14, 165, 233, 0.1);
        color: #0284c7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }

    .course-lesson__title {
        display: flex;
        align-items: baseline;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .course-lesson__index {
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        color: #94a3b8;
        font-weight: 700;
    }

    .course-lesson__title h4 {
        margin: 0;
        font-size: 1rem;
        color: #0f172a;
    }

    .course-lesson__description {
        margin: 0.4rem 0 0;
        color: #64748b;
    }

    .course-lesson__tags {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 0.5rem;
    }

    .course-lesson__tag {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        background: rgba(226, 232, 240, 0.55);
        color: #475569;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .course-lesson__tag.preview {
        background: rgba(52, 211, 153, 0.18);
        color: #047857;
    }

    .course-lesson__tag.type-video {
        background: rgba(14, 165, 233, 0.18);
        color: #0369a1;
    }

    .course-lesson__tag.type-quiz {
        background: rgba(245, 158, 11, 0.18);
        color: #b45309;
    }

    .course-lesson__tag.type-assignment {
        background: rgba(99, 102, 241, 0.18);
        color: #3730a3;
    }

    .course-lesson__actions .btn {
        min-width: 36px;
    }

    .course-lesson--empty {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    @media (max-width: 1024px) {
        .course-lesson {
            grid-template-columns: auto 1fr;
            grid-template-rows: auto auto;
        }
        .course-lesson__actions {
            grid-column: 2 / -1;
            justify-self: flex-start;
        }
    }

    @media (max-width: 640px) {
        .course-section {
            padding: 1.25rem 1.35rem;
        }
        .course-lesson {
            grid-template-columns: 1fr;
        }
        .course-lesson__icon {
            width: 32px;
            height: 32px;
            font-size: 1rem;
        }
        .course-lesson__actions {
            justify-self: stretch;
        }
    }
</style>
@endpush
