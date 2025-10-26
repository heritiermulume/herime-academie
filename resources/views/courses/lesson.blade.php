@extends('layouts.app')

@section('title', $lesson->title . ' - ' . $course->title)

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Sidebar avec le programme du cours -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>Programme du cours
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="accordion accordion-flush" id="courseProgram">
                        @foreach($course->sections as $section)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $section->id }}">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#collapse{{ $section->id }}" 
                                            aria-expanded="false" aria-controls="collapse{{ $section->id }}">
                                        <div class="d-flex justify-content-between w-100 me-3">
                                            <span class="small">{{ $section->title }}</span>
                                            <span class="badge bg-primary">{{ $section->lessons->count() }}</span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $section->id }}" class="accordion-collapse collapse" 
                                     aria-labelledby="heading{{ $section->id }}" data-bs-parent="#courseProgram">
                                    <div class="accordion-body p-2">
                                        @foreach($section->lessons as $sectionLesson)
                                            <a href="{{ route('courses.lesson', ['course' => $course->slug, 'lesson' => $sectionLesson->id]) }}" 
                                               class="d-block p-2 text-decoration-none {{ $sectionLesson->id === $lesson->id ? 'bg-primary text-white rounded' : 'text-dark' }} 
                                                      {{ $sectionLesson->is_preview ? 'border-start border-info border-3' : '' }}">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-{{ $sectionLesson->type === 'video' ? 'video' : ($sectionLesson->type === 'text' ? 'file-alt' : ($sectionLesson->type === 'quiz' ? 'question' : 'tasks')) }} me-2"></i>
                                                    <div class="flex-grow-1">
                                                        <div class="small fw-bold">{{ $sectionLesson->title }}</div>
                                                        <div class="small text-muted">{{ $sectionLesson->duration }} min</div>
                                                    </div>
                                                    @if($sectionLesson->is_preview)
                                                        <span class="badge bg-info">Gratuit</span>
                                                    @endif
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu principal de la leçon -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">Cours</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->slug) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item active">{{ Str::limit($lesson->title, 30) }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="card-body">
                    <!-- En-tête de la leçon -->
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h1 class="h3 mb-2">{{ $lesson->title }}</h1>
                            @if($lesson->description)
                                <p class="text-muted">{{ $lesson->description }}</p>
                            @endif
                        </div>
                        <div class="text-end">
                            @if($lesson->is_preview)
                                <span class="badge bg-info fs-6">Leçon gratuite</span>
                            @endif
                            <div class="mt-2">
                                <span class="badge bg-{{ $lesson->type === 'video' ? 'danger' : ($lesson->type === 'text' ? 'info' : ($lesson->type === 'quiz' ? 'warning' : 'success')) }}">
                                    {{ ucfirst($lesson->type) }}
                                </span>
                                <span class="text-muted ms-2">{{ $lesson->duration }} min</span>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu de la leçon -->
                    <div class="lesson-content">
                        @if($lesson->type === 'video')
                            <div class="ratio ratio-16x9 mb-4">
                                @if($lesson->content_url)
                                    @if(str_contains($lesson->content_url, 'youtube.com') || str_contains($lesson->content_url, 'youtu.be'))
                                        @php
                                            $videoId = '';
                                            if (str_contains($lesson->content_url, 'youtube.com/watch')) {
                                                parse_str(parse_url($lesson->content_url, PHP_URL_QUERY), $query);
                                                $videoId = $query['v'] ?? '';
                                            } elseif (str_contains($lesson->content_url, 'youtu.be/')) {
                                                $videoId = basename(parse_url($lesson->content_url, PHP_URL_PATH));
                                            }
                                        @endphp
                                        @if($videoId)
                                            <iframe src="https://www.youtube.com/embed/{{ $videoId }}" 
                                                    title="{{ $lesson->title }}" 
                                                    allowfullscreen></iframe>
                                        @else
                                            <div class="d-flex align-items-center justify-content-center bg-dark text-white">
                                                <div class="text-center">
                                                    <i class="fas fa-video fa-3x mb-3"></i>
                                                    <p>URL vidéo invalide</p>
                                                </div>
                                            </div>
                                        @endif
                                    @elseif(str_contains($lesson->content_url, 'vimeo.com'))
                                        @php
                                            $videoId = basename(parse_url($lesson->content_url, PHP_URL_PATH));
                                        @endphp
                                        @if($videoId)
                                            <iframe src="https://player.vimeo.com/video/{{ $videoId }}" 
                                                    title="{{ $lesson->title }}" 
                                                    allowfullscreen></iframe>
                                        @else
                                            <div class="d-flex align-items-center justify-content-center bg-dark text-white">
                                                <div class="text-center">
                                                    <i class="fas fa-video fa-3x mb-3"></i>
                                                    <p>URL vidéo invalide</p>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <video controls class="w-100 h-100" style="object-fit: cover;">
                                            <source src="{{ $lesson->content_url }}" type="video/mp4">
                                            Votre navigateur ne supporte pas la lecture vidéo.
                                        </video>
                                    @endif
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-dark text-white">
                                        <div class="text-center">
                                            <i class="fas fa-video fa-3x mb-3"></i>
                                            <p>Aucune vidéo disponible</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @elseif($lesson->type === 'text')
                            <div class="content-text">
                                @if($lesson->content_text)
                                    {!! nl2br(e($lesson->content_text)) !!}
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucun contenu texte disponible</p>
                                    </div>
                                @endif
                            </div>
                        @elseif($lesson->type === 'quiz')
                            <div class="quiz-content">
                                @if($lesson->content_url)
                                    <div class="text-center py-5">
                                        <i class="fas fa-question-circle fa-3x text-warning mb-3"></i>
                                        <h5>Quiz disponible</h5>
                                        <p class="text-muted">Cliquez sur le lien ci-dessous pour accéder au quiz</p>
                                        <a href="{{ $lesson->content_url }}" target="_blank" class="btn btn-warning">
                                            <i class="fas fa-external-link-alt me-2"></i>Ouvrir le quiz
                                        </a>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucun quiz disponible</p>
                                    </div>
                                @endif
                            </div>
                        @elseif($lesson->type === 'assignment')
                            <div class="assignment-content">
                                @if($lesson->content_url)
                                    <div class="text-center py-5">
                                        <i class="fas fa-tasks fa-3x text-success mb-3"></i>
                                        <h5>Devoir disponible</h5>
                                        <p class="text-muted">Cliquez sur le lien ci-dessous pour accéder au devoir</p>
                                        <a href="{{ $lesson->content_url }}" target="_blank" class="btn btn-success">
                                            <i class="fas fa-external-link-alt me-2"></i>Ouvrir le devoir
                                        </a>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucun devoir disponible</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Navigation entre les leçons -->
                    <div class="d-flex justify-content-between mt-5 pt-4 border-top">
                        <div>
                            @if($previousLesson)
                                <a href="{{ route('courses.lesson', ['course' => $course->slug, 'lesson' => $previousLesson->id]) }}" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i>Leçon précédente
                                </a>
                            @endif
                        </div>
                        <div>
                            @if($nextLesson)
                                <a href="{{ route('courses.lesson', ['course' => $course->slug, 'lesson' => $nextLesson->id]) }}" 
                                   class="btn btn-primary">
                                    Leçon suivante<i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


