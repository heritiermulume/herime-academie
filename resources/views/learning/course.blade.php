@extends('layouts.app')

@section('title', 'Apprendre - ' . $course->title . ' - Herime Academie')
@section('description', 'Suivez le cours ' . $course->title . ' sur Herime Academie')

@section('content')
<div class="learning-page-wrapper">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar - Programme du cours (Web uniquement) -->
            <div class="col-lg-3 col-md-4 sidebar-container d-none d-lg-block">
                <div class="learning-sidebar">
                    <!-- En-tête du cours -->
                    <div class="course-header">
                        <div class="d-flex align-items-center mb-3">
                            <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-outline-primary btn-sm me-3" title="Retour au cours" style="background-color: #003366; border-color: #003366; color: white;">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <h6 class="mb-0 fw-bold">{{ $course->title }}</h6>
                        </div>
                    </div>

                    <!-- Progression du cours -->
                    <div class="progress-section mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Progression du cours</span>
                            <span class="text-primary fw-bold small">{{ $progress['overall_progress'] ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $progress['overall_progress'] ?? 0 }}%; background-color: #003366;"></div>
                        </div>
                        <small class="text-muted">{{ $progress['completed_lessons'] ?? 0 }} / {{ $progress['total_lessons'] ?? 0 }} leçons terminées</small>
                    </div>

                    <!-- Programme du cours -->
                    <div class="course-program">
                        <div class="program-header">
                            <h6 class="mb-0 fw-bold">Contenu du cours</h6>
                        </div>
                        
                        <div class="sections-list">
                            @foreach($course->sections as $section)
                            <div class="section-item" data-section-order="{{ $section->sort_order }}">
                                <div class="section-header" data-bs-toggle="collapse" data-bs-target="#section-{{ $section->id }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="section-info">
                                            <h6 class="section-title mb-1">{{ $section->title }}</h6>
                                            <small class="text-muted">{{ $section->lessons ? $section->lessons->count() : 0 }} leçons</small>
                                        </div>
                                        <i class="fas fa-chevron-down section-arrow"></i>
                                    </div>
                                </div>
                                
                                <div class="collapse" id="section-{{ $section->id }}">
                                    <div class="lessons-list">
                                        @foreach($section->lessons ?? [] as $sectionLesson)
                                        @php
                                            $isActive = isset($activeLesson) && $sectionLesson->id === $activeLesson->id;
                                            $isCompleted = isset($progress['completed_lessons_ids']) ? $progress['completed_lessons_ids']->contains($sectionLesson->id) : false;
                                            $isStarted = isset($progress['started_lessons_ids']) ? $progress['started_lessons_ids']->contains($sectionLesson->id) : false;
                                        @endphp
                                        <div class="lesson-item {{ $isCompleted ? 'completed' : '' }} {{ $isStarted ? 'started' : '' }} {{ $isActive ? 'active' : '' }}"
                                             data-lesson-id="{{ $sectionLesson->id }}"
                                             data-lesson-type="{{ $sectionLesson->type }}"
                                             data-lesson-title="{{ $sectionLesson->title }}"
                                             data-lesson-description="{{ $sectionLesson->description }}"
                                             data-lesson-content="{{ $sectionLesson->content_url ?? $sectionLesson->content ?? '' }}"
                                             data-lesson-order="{{ $sectionLesson->sort_order }}">
                                            <a href="#" class="lesson-link" onclick="loadLesson({{ $sectionLesson->id }})">
                                                <div class="lesson-content">
                                                    <div class="lesson-icon">
                                                        @if($sectionLesson->type === 'video')
                                                            <i class="fas fa-play-circle"></i>
                                                        @elseif($sectionLesson->type === 'text')
                                                            <i class="fas fa-file-alt"></i>
                                                        @elseif($sectionLesson->type === 'quiz')
                                                            <i class="fas fa-question-circle"></i>
                                                        @else
                                                            <i class="fas fa-file"></i>
                                                        @endif
                                                    </div>
                                                    <div class="lesson-info">
                                                        <h6 class="lesson-title">{{ $sectionLesson->title }}</h6>
                                                        <small class="lesson-duration">{{ $sectionLesson->duration ?? 0 }} min</small>
                                                    </div>
                                                </div>
                                                <div class="lesson-status">
                                                    @if($isCompleted)
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @elseif($isStarted)
                                                        <i class="fas fa-play-circle text-warning"></i>
                                                    @elseif($isActive)
                                                        <i class="fas fa-play-circle text-primary"></i>
                                                    @endif
                                                </div>
                                            </a>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="col-lg-9 col-md-8 main-content">
                <!-- Section Mobile - Header et lecteur -->
                <div class="mobile-header d-block d-lg-none">
                    <div class="mobile-course-header">
                        <div class="d-flex align-items-center justify-content-center position-relative mb-3">
                            <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-outline-primary btn-sm position-absolute start-0" title="Retour au cours" style="background-color: #003366; border-color: #003366; color: white;">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <h6 class="mb-0 fw-bold text-center">{{ $course->title }}</h6>
                        </div>
                    </div>
                    
                    <!-- Lecteur mobile -->
                    <div class="mobile-player-section">
                        <div class="mobile-player-header">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="lesson-info">
                                    <h1 class="lesson-title" id="mobile-lesson-title">
                                        @if(isset($activeLesson))
                                            {{ $activeLesson->title }}
                                        @else
                                            {{ $course->title }}
                                        @endif
                                    </h1>
                                    <p class="lesson-description text-muted" id="mobile-lesson-description">
                                        @if(isset($activeLesson))
                                            {{ $activeLesson->description }}
                                        @else
                                            {{ Str::limit($course->description, 150) }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Lecteur moderne mobile -->
                        <div class="modern-player-container mobile-player">
                            <div class="player-wrapper" id="mobile-player-wrapper">
                                @if(isset($activeLesson))
                                    @switch($activeLesson->type)
                                        @case('video')
                                            @if($activeLesson->content_url)
                                            <div class="modern-video-player">
                                                <div class="video-container">
                                                    <video id="mobile-video-player" class="video-player" preload="metadata">
                                                        <source src="{{ str_starts_with($activeLesson->content_url, 'http') ? $activeLesson->content_url : Storage::url($activeLesson->content_url) }}" type="video/mp4">
                                                        Votre navigateur ne supporte pas la lecture vidéo.
                                                    </video>
                                                    <div class="play-overlay" id="mobile-play-overlay">
                                                        <button class="play-btn" id="mobile-play-btn">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    </div>
                                                    <div class="loading-overlay" id="mobile-loading-overlay">
                                                        <div class="spinner-border text-primary" role="status">
                                                            <span class="visually-hidden">Chargement...</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="video-controls" id="mobile-video-controls">
                                                    <div class="left-controls">
                                                        <button class="control-btn play-pause-btn" id="mobile-play-pause">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                        <div class="time-display">
                                                            <span id="mobile-current-time">0:00</span> / <span id="mobile-duration">0:00</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="center-controls">
                                                        <button class="control-btn prev-lesson-btn" id="mobile-prev-lesson">
                                                            <i class="fas fa-step-backward"></i>
                                                        </button>
                                                        <button class="control-btn next-lesson-btn" id="mobile-next-lesson">
                                                            <i class="fas fa-step-forward"></i>
                                                        </button>
                                                    </div>
                                                    
                                                    <div class="right-controls">
                                                        <button class="control-btn speed-btn" id="mobile-speed-btn">
                                                            <span class="speed-text">1x</span>
                                                        </button>
                                                        <button class="control-btn fullscreen-btn" id="mobile-fullscreen-btn">
                                                            <i class="fas fa-expand"></i>
                                                        </button>
                                                    </div>
                                                    
                                                    <div class="progress-container">
                                                        <div class="progress-bar" id="mobile-progress-bar">
                                                            <div class="progress-fill" id="mobile-progress-fill"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                            <div class="no-content">
                                                <i class="fas fa-video fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Aucune vidéo disponible</p>
                                            </div>
                                            @endif
                                            @break
                                        @default
                                            <div class="no-content">
                                                <i class="fas fa-play-circle fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Sélectionnez une leçon pour commencer</p>
                                            </div>
                                    @endswitch
                                @else
                                    <div class="no-content">
                                        <i class="fas fa-play-circle fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Sélectionnez une leçon pour commencer</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions mobile -->
                        <div class="mobile-actions">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="lesson-navigation d-flex gap-2">
                                    <button class="btn btn-outline-secondary" id="mobile-prev-btn" disabled>
                                        <i class="fas fa-arrow-left me-1"></i>Précédent
                                    </button>
                                    <button class="btn btn-primary" id="mobile-next-btn" disabled>
                                        Suivant<i class="fas fa-arrow-right ms-1"></i>
                                    </button>
                                </div>
                                
                                <div class="lesson-progress">
                                    @if(isset($activeLesson))
                                        @if(!$lessonProgress || !$lessonProgress->is_completed)
                                        <button id="mobile-complete-lesson-btn" class="btn btn-success" 
                                                data-lesson-id="{{ $activeLesson->id }}" data-course-id="{{ $course->id }}">
                                            <i class="fas fa-check me-2"></i>Marquer comme terminée
                                        </button>
                                        @else
                                        <div class="alert alert-success d-inline-block mb-0">
                                            <i class="fas fa-check-circle me-2"></i>Leçon terminée
                                        </div>
                                        @endif
                                    @else
                                        <div class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>Sélectionnez une leçon pour commencer
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lesson-container">
                    <!-- Section 1: Lecteur moderne (Desktop) -->
                    <div class="player-section d-none d-lg-block">
                        <div class="player-header">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="lesson-info">
                                    <h1 class="lesson-title" id="current-lesson-title">
                                        @if(isset($activeLesson))
                                            {{ $activeLesson->title }}
                                        @else
                                            {{ $course->title }}
                                        @endif
                                    </h1>
                                    <p class="lesson-description text-muted" id="current-lesson-description">
                                        @if(isset($activeLesson))
                                            {{ $activeLesson->description }}
                                        @else
                                            {{ Str::limit($course->description, 150) }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Lecteur moderne -->
                        <div class="modern-player-container sticky-player">
                            <div class="player-wrapper" id="player-wrapper">
                                @if(isset($activeLesson))
                                    @switch($activeLesson->type)
                                        @case('video')
                                            @if($activeLesson->content_url)
                                            <div class="modern-video-player" id="modern-video-player">
                                                <div class="video-container">
                                                    <video id="lesson-video" class="video-element" preload="metadata" 
                                                           data-lesson-id="{{ $activeLesson->id }}" data-course-id="{{ $course->id }}"
                                                           poster="{{ $course->cover_image ? Storage::url($course->cover_image) : '' }}">
                                                        <source src="{{ str_starts_with($activeLesson->content_url, 'http') ? $activeLesson->content_url : Storage::url($activeLesson->content_url) }}" type="video/mp4">
                                                        <source src="{{ str_starts_with($activeLesson->content_url, 'http') ? $activeLesson->content_url : Storage::url($activeLesson->content_url) }}" type="video/webm">
                                                        <source src="{{ str_starts_with($activeLesson->content_url, 'http') ? $activeLesson->content_url : Storage::url($activeLesson->content_url) }}" type="video/ogg">
                                                        Votre navigateur ne supporte pas la lecture vidéo.
                                                    </video>
                                                    
                                                    <!-- Contrôles personnalisés -->
                                                    <div class="video-controls">
                                                        <!-- Barre de progression -->
                                                        <div class="progress-container">
                                                            <div class="progress-bar">
                                                                <div class="progress-filled"></div>
                                                                <div class="progress-handle"></div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Contrôles principaux -->
                                                        <div class="controls-row">
                                                            <div class="left-controls">
                                                                <button class="control-btn play-pause-btn" id="play-pause">
                                                                    <i class="fas fa-play"></i>
                                                                </button>
                                                                <div class="time-display">
                                                                    <span class="current-time">0:00</span>
                                                                    <span class="time-separator">/</span>
                                                                    <span class="duration">0:00</span>
                                                                </div>
                                                                <button class="control-btn volume-btn" id="volume-btn">
                                                                    <i class="fas fa-volume-up"></i>
                                                                </button>
                                                                <div class="volume-slider">
                                                                    <input type="range" class="volume-range" min="0" max="100" value="100">
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="center-controls">
                                                                <button class="control-btn prev-lesson-btn" id="prev-lesson" 
                                                                        @if(!$previousLesson) disabled @endif>
                                                                    <i class="fas fa-step-backward"></i>
                                                                </button>
                                                                <button class="control-btn next-lesson-btn" id="next-lesson"
                                                                        @if(!$nextLesson) disabled @endif>
                                                                    <i class="fas fa-step-forward"></i>
                                                                </button>
                                                            </div>
                                                            
                                                            <div class="right-controls">
                                                                <button class="control-btn speed-btn" id="speed-btn">
                                                                    <span class="speed-text">1x</span>
                                                                </button>
                                                                <button class="control-btn fullscreen-btn" id="fullscreen">
                                                                    <i class="fas fa-expand"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Overlay de chargement -->
                                                    <div class="loading-overlay">
                                                        <div class="spinner"></div>
                                                    </div>
                                                    
                                                    <!-- Overlay de lecture -->
                                                    <div class="play-overlay">
                                                        <button class="play-button">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                            <div class="no-content">
                                                <i class="fas fa-video fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Aucune vidéo disponible</p>
                                            </div>
                                            @endif
                                            @break

                                        @case('text')
                                            @if($activeLesson->content)
                                            <div class="modern-text-reader">
                                                <div class="text-reader-header">
                                                    <div class="reader-controls">
                                                        <button class="control-btn font-size-btn" id="font-decrease">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <span class="font-size-display">100%</span>
                                                        <button class="control-btn font-size-btn" id="font-increase">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                        <button class="control-btn theme-btn" id="theme-toggle">
                                                            <i class="fas fa-moon"></i>
                                                        </button>
                                                    </div>
                                                    <div class="lesson-navigation">
                                                        <button class="control-btn prev-lesson-btn" id="prev-lesson-text">
                                                            <i class="fas fa-arrow-left"></i> Précédent
                                                        </button>
                                                        <button class="control-btn next-lesson-btn" id="next-lesson-text">
                                                            Suivant <i class="fas fa-arrow-right"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="text-content" id="text-content">
                                                    {!! $activeLesson->content !!}
                                                </div>
                                            </div>
                                            @else
                                            <div class="no-content">
                                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Aucun contenu disponible</p>
                                            </div>
                                            @endif
                                            @break

                                        @case('quiz')
                                            <div class="modern-quiz-player">
                                                <div class="quiz-header">
                                                    <h4><i class="fas fa-question-circle me-2"></i>Quiz</h4>
                                                    <div class="lesson-navigation">
                                                        <button class="control-btn prev-lesson-btn" id="prev-lesson-quiz">
                                                            <i class="fas fa-arrow-left"></i> Précédent
                                                        </button>
                                                        <button class="control-btn next-lesson-btn" id="next-lesson-quiz">
                                                            Suivant <i class="fas fa-arrow-right"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="quiz-content">
                                                    <div class="quiz-placeholder">
                                                        <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">Système de quiz en développement</p>
                                                        <button class="btn btn-primary">Commencer le quiz</button>
                                                    </div>
                                                </div>
                                            </div>
                                            @break

                                        @case('pdf')
                                            <div class="modern-pdf-viewer">
                                                <div class="pdf-viewer-header">
                                                    <div class="pdf-controls">
                                                        <button class="control-btn" id="pdf-zoom-out">
                                                            <i class="fas fa-search-minus"></i>
                                                        </button>
                                                        <span class="zoom-level">100%</span>
                                                        <button class="control-btn" id="pdf-zoom-in">
                                                            <i class="fas fa-search-plus"></i>
                                                        </button>
                                                        <button class="control-btn" id="pdf-fullscreen">
                                                            <i class="fas fa-expand"></i>
                                                        </button>
                                                    </div>
                                                    <div class="lesson-navigation">
                                                        <button class="control-btn prev-lesson-btn" id="prev-lesson-pdf">
                                                            <i class="fas fa-arrow-left"></i> Précédent
                                                        </button>
                                                        <button class="control-btn next-lesson-btn" id="next-lesson-pdf">
                                                            Suivant <i class="fas fa-arrow-right"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="pdf-container">
                                                    <iframe src="{{ Storage::url($activeLesson->file_url) }}" 
                                                            class="pdf-iframe" 
                                                            frameborder="0">
                                                    </iframe>
                                                </div>
                                            </div>
                                            @break

                                        @case('audio')
                                            <div class="modern-audio-player">
                                                <div class="audio-container">
                                                    <div class="audio-visualizer">
                                                        <div class="visualizer-bars">
                                                            <div class="bar"></div>
                                                            <div class="bar"></div>
                                                            <div class="bar"></div>
                                                            <div class="bar"></div>
                                                            <div class="bar"></div>
                                                        </div>
                                                    </div>
                                                    <audio id="lesson-audio" class="audio-element" preload="metadata">
                                                        <source src="{{ Storage::url($activeLesson->file_url) }}" type="audio/mpeg">
                                                        <source src="{{ Storage::url($activeLesson->file_url) }}" type="audio/wav">
                                                        <source src="{{ Storage::url($activeLesson->file_url) }}" type="audio/ogg">
                                                        Votre navigateur ne supporte pas la lecture audio.
                                                    </audio>
                                                    
                                                    <div class="audio-controls">
                                                        <button class="control-btn play-pause-btn" id="audio-play-pause">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                        <div class="audio-progress">
                                                            <div class="progress-bar">
                                                                <div class="progress-filled"></div>
                                                                <div class="progress-handle"></div>
                                                            </div>
                                                        </div>
                                                        <div class="time-display">
                                                            <span class="current-time">0:00</span>
                                                            <span class="time-separator">/</span>
                                                            <span class="duration">0:00</span>
                                                        </div>
                                                        <button class="control-btn volume-btn" id="audio-volume-btn">
                                                            <i class="fas fa-volume-up"></i>
                                                        </button>
                                                        <div class="lesson-navigation">
                                                            <button class="control-btn prev-lesson-btn" id="prev-lesson-audio" 
                                                                    @if(!$previousLesson) disabled @endif>
                                                                <i class="fas fa-step-backward"></i>
                                                            </button>
                                                            <button class="control-btn next-lesson-btn" id="next-lesson-audio"
                                                                    @if(!$nextLesson) disabled @endif>
                                                                <i class="fas fa-step-forward"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @break

                                        @default
                                            <div class="no-content">
                                                <i class="fas fa-file fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Type de contenu non supporté</p>
                                            </div>
                                    @endswitch
                                @else
                                    <!-- Aperçu du cours par défaut -->
                                    <div class="course-preview">
                                        @if($course->video_preview || ($course->previewLessons && $course->previewLessons->count() > 0))
                                            <div class="position-relative">
                                                <img src="{{ $course->cover_image ? Storage::url($course->cover_image) : asset('images/default-course.jpg') }}" 
                                                     alt="{{ $course->title }}" 
                                                     class="img-fluid rounded w-100" 
                                                     style="height: 400px; object-fit: cover;">
                                                <div class="video-play-overlay position-absolute top-50 start-50 translate-middle">
                                                    <button type="button" class="btn btn-primary btn-lg rounded-circle" 
                                                            data-bs-toggle="modal" data-bs-target="#previewModal">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <div class="no-content">
                                                <i class="fas fa-play-circle fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Sélectionnez une leçon pour commencer</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Navigation des leçons -->
                        <div class="lesson-navigation-section mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-outline-secondary" id="prev-lesson-main" @if(!isset($previousLesson)) disabled @endif>
                                    <i class="fas fa-arrow-left me-2"></i>Leçon précédente
                                </button>
                                <button class="btn btn-primary" id="next-lesson-main" @if(!isset($nextLesson)) disabled @endif>
                                    Leçon suivante<i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Section 2: Onglets Desktop -->
                    <div class="tabs-section d-none d-lg-block">
                        <ul class="nav nav-tabs" id="courseTabs" role="tablist">
                            <!-- Onglet Présentation - Desktop -->
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ !isset($activeLesson) ? 'active' : '' }}" id="presentation-tab" data-bs-toggle="tab" data-bs-target="#presentation" type="button" role="tab">
                                    <i class="fas fa-info-circle me-2"></i>Présentation
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="qa-tab" data-bs-toggle="tab" data-bs-target="#qa" type="button" role="tab">
                                    <i class="fas fa-question-circle me-2"></i>Q&R
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="announcements-tab" data-bs-toggle="tab" data-bs-target="#announcements" type="button" role="tab">
                                    <i class="fas fa-bullhorn me-2"></i>Annonces
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                                    <i class="fas fa-star me-2"></i>Évaluations
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Section Mobile - Navigation simple -->
                    <div class="mobile-tabs-section d-block d-lg-none">
                        <div class="mobile-tabs-nav">
                            <button class="mobile-tab-btn active" data-tab="content">
                                <i class="fas fa-list"></i>
                                <span>Contenu</span>
                            </button>
                            <button class="mobile-tab-btn" data-tab="presentation">
                                <i class="fas fa-info-circle"></i>
                                <span>Présentation</span>
                            </button>
                            <button class="mobile-tab-btn" data-tab="qa">
                                <i class="fas fa-question-circle"></i>
                                <span>Q&R</span>
                            </button>
                            <button class="mobile-tab-btn" data-tab="announcements">
                                <i class="fas fa-bullhorn"></i>
                                <span>Annonces</span>
                            </button>
                            <button class="mobile-tab-btn" data-tab="reviews">
                                <i class="fas fa-star"></i>
                                <span>Évaluations</span>
                            </button>
                        </div>
                    </div>

                        <!-- Contenu Desktop -->
                        <div class="tab-content d-none d-lg-block" id="courseTabContent">
                            <!-- Onglet Présentation Desktop -->
                            <div class="tab-pane fade {{ !isset($activeLesson) ? 'show active' : '' }}" id="presentation" role="tabpanel">
                                <div class="tab-content-wrapper">
                                    <!-- Description du cours -->
                                    <div class="course-description mb-4">
                                        <h5 class="fw-bold mb-3">À propos de ce cours</h5>
                                        <p class="text-muted">{{ $course->description }}</p>
                                    </div>

                                    <!-- Statistiques du cours -->
                                    <div class="course-stats mb-4">
                                        <h5 class="fw-bold mb-3">Statistiques du cours</h5>
                                        <div class="row g-3">
                                            <div class="col-md-3 col-6">
                                                <div class="stat-item text-center">
                                                    <div class="stat-icon mb-2">
                                                        <i class="fas fa-play-circle text-primary"></i>
                                                    </div>
                                                    <div class="stat-value fw-bold">{{ $course->lessons ? $course->lessons->count() : 0 }}</div>
                                                    <div class="stat-label text-muted">Leçons</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-item text-center">
                                                    <div class="stat-icon mb-2">
                                                        <i class="fas fa-clock text-info"></i>
                                                    </div>
                                                    <div class="stat-value fw-bold">{{ $course->lessons ? $course->lessons->sum('duration') : 0 }}</div>
                                                    <div class="stat-label text-muted">Minutes</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-item text-center">
                                                    <div class="stat-icon mb-2">
                                                        <i class="fas fa-users text-success"></i>
                                                    </div>
                                                    <div class="stat-value fw-bold">{{ $course->enrollments ? $course->enrollments->count() : 0 }}</div>
                                                    <div class="stat-label text-muted">Étudiants</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-item text-center">
                                                    <div class="stat-icon mb-2">
                                                        <i class="fas fa-star text-warning"></i>
                                                    </div>
                                                    <div class="stat-value fw-bold">{{ $course->reviews ? number_format($course->reviews->avg('rating') ?? 0, 1) : '0.0' }}</div>
                                                    <div class="stat-label text-muted">Note</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Ce que vous apprendrez -->
                                    @if($course->what_you_will_learn)
                                    <div class="what-you-learn mb-4">
                                        <h5 class="fw-bold mb-3">Ce que vous apprendrez</h5>
                                        <ul class="list-unstyled">
                                            @foreach($course->getWhatYouWillLearnArray() as $item)
                                            <li class="mb-2">
                                                <i class="fas fa-check text-success me-2"></i>{{ $item }}
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif

                                    <!-- Prérequis -->
                                    @if($course->requirements)
                                    <div class="requirements mb-4">
                                        <h5 class="fw-bold mb-3">Prérequis</h5>
                                        <ul class="list-unstyled">
                                            @foreach($course->getRequirementsArray() as $requirement)
                                            <li class="mb-2">
                                                <i class="fas fa-arrow-right text-primary me-2"></i>{{ $requirement }}
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Onglet Présentation -->
                            <div class="tab-pane fade {{ !isset($activeLesson) ? 'show active d-none d-lg-block' : 'd-none d-lg-block' }}" id="presentation" role="tabpanel">
                                <div class="tab-content-wrapper">
                                    <!-- Description du cours -->
                                    <div class="course-description mb-4">
                                        <h5 class="fw-bold mb-3">À propos de ce cours</h5>
                                        <p class="text-muted">{{ $course->description }}</p>
                                    </div>

                                    <!-- Statistiques du cours -->
                                    <div class="course-stats mb-4">
                                        <h5 class="fw-bold mb-3">Statistiques du cours</h5>
                                        <div class="row g-3">
                                            <div class="col-md-3 col-6">
                                                <div class="stat-item text-center">
                                                    <div class="stat-icon mb-2">
                                                        <i class="fas fa-play-circle text-primary"></i>
                                                    </div>
                                                    <div class="stat-value fw-bold">{{ $course->lessons ? $course->lessons->count() : 0 }}</div>
                                                    <div class="stat-label text-muted">Leçons</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-item text-center">
                                                    <div class="stat-icon mb-2">
                                                        <i class="fas fa-clock text-info"></i>
                                                    </div>
                                                    <div class="stat-value fw-bold">{{ $course->lessons ? $course->lessons->sum('duration') : 0 }}</div>
                                                    <div class="stat-label text-muted">Minutes</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-item text-center">
                                                    <div class="stat-icon mb-2">
                                                        <i class="fas fa-users text-success"></i>
                                                    </div>
                                                    <div class="stat-value fw-bold">{{ $course->enrollments ? $course->enrollments->count() : 0 }}</div>
                                                    <div class="stat-label text-muted">Étudiants</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-item text-center">
                                                    <div class="stat-icon mb-2">
                                                        <i class="fas fa-star text-warning"></i>
                                                    </div>
                                                    <div class="stat-value fw-bold">{{ $course->reviews ? number_format($course->reviews->avg('rating') ?? 0, 1) : '0.0' }}</div>
                                                    <div class="stat-label text-muted">Note</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Ce que vous apprendrez -->
                                    @if($course->what_you_will_learn)
                                    <div class="what-you-learn mb-4">
                                        <h5 class="fw-bold mb-3">Ce que vous apprendrez</h5>
                                        <ul class="list-unstyled">
                                            @foreach($course->getWhatYouWillLearnArray() as $item)
                                            <li class="mb-2">
                                                <i class="fas fa-check text-success me-2"></i>{{ $item }}
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif

                                    <!-- Prérequis -->
                                    @if($course->requirements)
                                    <div class="requirements mb-4">
                                        <h5 class="fw-bold mb-3">Prérequis</h5>
                                        <ul class="list-unstyled">
                                            @foreach($course->getRequirementsArray() as $requirement)
                                            <li class="mb-2">
                                                <i class="fas fa-arrow-right text-primary me-2"></i>{{ $requirement }}
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif

                                    <!-- Formateur -->
                                    @if($course->instructor)
                                    <div class="instructor-info mb-4">
                                        <h5 class="fw-bold mb-3">Formateur</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="instructor-avatar me-3">
                                                @if($course->instructor->avatar)
                                                <img src="{{ $instructor->avatar }}" 
                                                     alt="{{ $course->instructor->name }}" 
                                                     class="rounded-circle" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                                @else
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="fas fa-user fa-2x"></i>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="instructor-details">
                                                <h6 class="mb-1">{{ $course->instructor->name }}</h6>
                                                @if($course->instructor->specialization)
                                                <p class="text-muted mb-1">{{ $course->instructor->specialization }}</p>
                                                @endif
                                                @if($course->instructor->bio)
                                                <p class="text-muted mb-0">{{ Str::limit($course->instructor->bio, 200) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Cours recommandés -->
                                    @if($recommendedCourses && $recommendedCourses->count() > 0)
                                    <div class="recommended-courses mb-4">
                                        <h5 class="fw-bold mb-3">Cours recommandés pour vous</h5>
                                        <div class="row g-4">
                                            @foreach($recommendedCourses->take(4) as $recommendedCourse)
                                            <div class="col-md-6">
                                                <div class="card h-100 course-card">
                                                    <div class="card-body p-0">
                                                        <div class="d-flex">
                                                            <div class="course-thumbnail">
                                                                @if($recommendedCourse->thumbnail)
                                                                    <img src="{{ Storage::url($recommendedCourse->thumbnail) }}" alt="{{ $recommendedCourse->title }}" class="img-fluid">
                                                                @else
                                                                    <div class="bg-primary d-flex align-items-center justify-content-center">
                                                                        <i class="fas fa-play text-white"></i>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="course-info flex-grow-1 p-3">
                                                                <h6 class="card-title mb-2 fw-bold">{{ $recommendedCourse->title }}</h6>
                                                                <p class="text-muted small mb-2">{{ $recommendedCourse->instructor->name }}</p>
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <div class="rating me-2">
                                                                        @for($i = 1; $i <= 5; $i++)
                                                                            @if($i <= ($recommendedCourse->reviews->avg('rating') ?? 0))
                                                                                <i class="fas fa-star text-warning"></i>
                                                                            @else
                                                                                <i class="far fa-star text-warning"></i>
                                                                            @endif
                                                                        @endfor
                                                                    </div>
                                                                    <span class="text-muted small">{{ number_format($recommendedCourse->reviews->avg('rating') ?? 0, 1) }}</span>
                                                                    <span class="text-muted small ms-2">({{ $recommendedCourse->reviews->count() }})</span>
                                                                </div>
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <span class="text-primary fw-bold h6 mb-0">{{ number_format($recommendedCourse->price, 0) }} FCFA</span>
                                                                    <a href="{{ route('courses.show', $recommendedCourse->slug) }}" class="btn btn-sm btn-primary">Voir le cours</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Onglet Q&R -->
                            <div class="tab-pane fade" id="qa" role="tabpanel">
                                <div class="qa-section">
                                    <h5 class="fw-bold mb-3">Questions et Réponses</h5>
                                    <div class="text-center py-5">
                                        <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucune question pour le moment</p>
                                        <button class="btn btn-primary">Poser une question</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglet Annonces -->
                            <div class="tab-pane fade" id="announcements" role="tabpanel">
                                <div class="announcements-section">
                                    <h5 class="fw-bold mb-3">Annonces du formateur</h5>
                                    <div class="text-center py-5">
                                        <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucune annonce pour le moment</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglet Évaluations -->
                            <div class="tab-pane fade" id="reviews" role="tabpanel">
                                <div class="reviews-section">
                                        <h5 class="fw-bold mb-3">Évaluations des étudiants</h5>
                                        @if($course->reviews && $course->reviews->count() > 0)
                                            <div class="reviews-summary mb-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="rating me-3">
                                                        @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= ($course->reviews->avg('rating') ?? 0) ? 'text-warning' : 'text-muted' }}"></i>
                                                        @endfor
                                                    </div>
                                                    <span class="fw-bold">{{ number_format($course->reviews->avg('rating') ?? 0, 1) }}</span>
                                                    <span class="text-muted ms-2">({{ $course->reviews->count() }} avis)</span>
                                                </div>
                                            </div>
                                            
                                            <div class="reviews-list">
                                                @foreach($course->reviews->take(5) as $review)
                                                <div class="review-item mb-3">
                                                    <div class="d-flex align-items-start">
                                                        <div class="reviewer-avatar me-3">
                                                            @if($review->user->avatar)
                                                            <img src="{{ $instructor->avatar }}" 
                                                                 alt="{{ $review->user->name }}" 
                                                                 class="rounded-circle" 
                                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                                            @else
                                                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                                 style="width: 40px; height: 40px;">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                            @endif
                                                        </div>
                                                        <div class="review-content">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <h6 class="mb-0 me-2">{{ $review->user->name }}</h6>
                                                                <div class="rating">
                                                                    @for($i = 1; $i <= 5; $i++)
                                                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                                                    @endfor
                                                                </div>
                                                            </div>
                                                            <p class="text-muted mb-0">{{ $review->comment }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-5">
                                                <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Aucune évaluation pour le moment</p>
                                            </div>
                                        @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Mobile - Contenu dédié -->
                    <div class="mobile-content d-block d-lg-none">
                        <!-- Onglet Contenu Mobile -->
                        <div class="mobile-tab-content active" id="mobile-content">
                            <div class="course-program-mobile">
                                <div class="program-header">
                                    <h5 class="fw-bold mb-3">Contenu du cours</h5>
                                </div>
                                
                                <div class="sections-list">
                                    @foreach($course->sections as $section)
                                    <div class="section-item" data-section-order="{{ $section->sort_order }}">
                                        <div class="section-header" data-bs-toggle="collapse" data-bs-target="#mobile-section-{{ $section->id }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="section-info">
                                                    <h6 class="section-title mb-1">{{ $section->title }}</h6>
                                                    <small class="text-muted">{{ $section->lessons ? $section->lessons->count() : 0 }} leçons</small>
                                                </div>
                                                <i class="fas fa-chevron-down section-arrow"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="collapse" id="mobile-section-{{ $section->id }}">
                                            <div class="lessons-list">
                                                @foreach($section->lessons ?? [] as $sectionLesson)
                                                @php
                                                    $isActive = isset($activeLesson) && $sectionLesson->id === $activeLesson->id;
                                                    $isCompleted = isset($progress['completed_lessons_ids']) ? $progress['completed_lessons_ids']->contains($sectionLesson->id) : false;
                                                    $isStarted = isset($progress['started_lessons_ids']) ? $progress['started_lessons_ids']->contains($sectionLesson->id) : false;
                                                @endphp
                                                <div class="lesson-item {{ $isCompleted ? 'completed' : '' }} {{ $isStarted ? 'started' : '' }} {{ $isActive ? 'active' : '' }}"
                                                     data-lesson-id="{{ $sectionLesson->id }}"
                                                     data-lesson-type="{{ $sectionLesson->type }}"
                                                     data-lesson-title="{{ $sectionLesson->title }}"
                                                     data-lesson-description="{{ $sectionLesson->description }}"
                                                     data-lesson-content="{{ $sectionLesson->content_url ?? $sectionLesson->content ?? '' }}"
                                                     data-lesson-order="{{ $sectionLesson->sort_order }}">
                                                    <a href="#" class="lesson-link" onclick="loadLesson({{ $sectionLesson->id }})">
                                                        <div class="lesson-content">
                                                            <div class="lesson-icon">
                                                                @if($sectionLesson->type === 'video')
                                                                    <i class="fas fa-play-circle"></i>
                                                                @elseif($sectionLesson->type === 'text')
                                                                    <i class="fas fa-file-alt"></i>
                                                                @elseif($sectionLesson->type === 'quiz')
                                                                    <i class="fas fa-question-circle"></i>
                                                                @else
                                                                    <i class="fas fa-file"></i>
                                                                @endif
                                                            </div>
                                                            <div class="lesson-info">
                                                                <h6 class="lesson-title">{{ $sectionLesson->title }}</h6>
                                                                <small class="lesson-duration">{{ $sectionLesson->duration ?? 0 }} min</small>
                                                            </div>
                                                        </div>
                                                        <div class="lesson-status">
                                                            @if($isCompleted)
                                                                <i class="fas fa-check-circle text-success"></i>
                                                            @elseif($isStarted)
                                                                <i class="fas fa-play-circle text-warning"></i>
                                                            @elseif($isActive)
                                                                <i class="fas fa-play-circle text-primary"></i>
                                                            @endif
                                                        </div>
                                                    </a>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Onglet Présentation Mobile -->
                        <div class="mobile-tab-content" id="mobile-presentation">
                            <div class="course-description mb-4">
                                <h5 class="fw-bold mb-3">À propos de ce cours</h5>
                                <p class="text-muted">{{ $course->description }}</p>
                            </div>

                            <div class="course-stats mb-4">
                                <h5 class="fw-bold mb-3">Statistiques du cours</h5>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="stat-item text-center">
                                            <div class="stat-icon mb-2">
                                                <i class="fas fa-play-circle text-primary"></i>
                                            </div>
                                            <div class="stat-value fw-bold">{{ $course->lessons ? $course->lessons->count() : 0 }}</div>
                                            <div class="stat-label text-muted">Leçons</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-item text-center">
                                            <div class="stat-icon mb-2">
                                                <i class="fas fa-clock text-info"></i>
                                            </div>
                                            <div class="stat-value fw-bold">{{ $course->lessons ? $course->lessons->sum('duration') : 0 }}</div>
                                            <div class="stat-label text-muted">Minutes</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-item text-center">
                                            <div class="stat-icon mb-2">
                                                <i class="fas fa-users text-success"></i>
                                            </div>
                                            <div class="stat-value fw-bold">{{ $course->enrollments ? $course->enrollments->count() : 0 }}</div>
                                            <div class="stat-label text-muted">Étudiants</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-item text-center">
                                            <div class="stat-icon mb-2">
                                                <i class="fas fa-star text-warning"></i>
                                            </div>
                                            <div class="stat-value fw-bold">{{ $course->reviews ? number_format($course->reviews->avg('rating') ?? 0, 1) : '0.0' }}</div>
                                            <div class="stat-label text-muted">Note</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($course->what_you_will_learn)
                            <div class="what-you-learn mb-4">
                                <h5 class="fw-bold mb-3">Ce que vous apprendrez</h5>
                                <ul class="list-unstyled">
                                    @foreach($course->getWhatYouWillLearnArray() as $item)
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>{{ $item }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            @if($course->requirements)
                            <div class="requirements mb-4">
                                <h5 class="fw-bold mb-3">Prérequis</h5>
                                <ul class="list-unstyled">
                                    @foreach($course->getRequirementsArray() as $requirement)
                                    <li class="mb-2">
                                        <i class="fas fa-arrow-right text-primary me-2"></i>{{ $requirement }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <!-- Cours recommandés Mobile -->
                            @if($recommendedCourses && $recommendedCourses->count() > 0)
                            <div class="recommended-courses mb-4">
                                <h5 class="fw-bold mb-3">Cours recommandés pour vous</h5>
                                <div class="row g-3">
                                    @foreach($recommendedCourses->take(4) as $recommendedCourse)
                                    <div class="col-12">
                                        <div class="card course-card">
                                            <div class="card-body p-0">
                                                <div class="d-flex">
                                                    <div class="course-thumbnail">
                                                        @if($recommendedCourse->thumbnail)
                                                            <img src="{{ Storage::url($recommendedCourse->thumbnail) }}" alt="{{ $recommendedCourse->title }}" class="img-fluid">
                                                        @else
                                                            <div class="bg-primary d-flex align-items-center justify-content-center">
                                                                <i class="fas fa-play text-white"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="course-info flex-grow-1 p-3">
                                                        <h6 class="card-title mb-2 fw-bold">{{ $recommendedCourse->title }}</h6>
                                                        <p class="text-muted small mb-2">{{ $recommendedCourse->instructor->name }}</p>
                                                        <div class="d-flex align-items-center mb-2">
                                                            <div class="rating me-2">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    @if($i <= ($recommendedCourse->reviews->avg('rating') ?? 0))
                                                                        <i class="fas fa-star text-warning"></i>
                                                                    @else
                                                                        <i class="far fa-star text-warning"></i>
                                                                    @endif
                                                                @endfor
                                                            </div>
                                                            <span class="text-muted small">{{ number_format($recommendedCourse->reviews->avg('rating') ?? 0, 1) }}</span>
                                                            <span class="text-muted small ms-2">({{ $recommendedCourse->reviews->count() }})</span>
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <span class="text-primary fw-bold h6 mb-0">{{ number_format($recommendedCourse->price, 0) }} FCFA</span>
                                                            <a href="{{ route('courses.show', $recommendedCourse->slug) }}" class="btn btn-sm btn-primary">Voir le cours</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Onglet Q&R Mobile -->
                        <div class="mobile-tab-content" id="mobile-qa">
                            <h5 class="fw-bold mb-3">Questions et Réponses</h5>
                            <div class="text-center py-5">
                                <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucune question pour le moment</p>
                                <button class="btn btn-primary">Poser une question</button>
                            </div>
                        </div>

                        <!-- Onglet Annonces Mobile -->
                        <div class="mobile-tab-content" id="mobile-announcements">
                            <h5 class="fw-bold mb-3">Annonces du formateur</h5>
                            <div class="text-center py-5">
                                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucune annonce pour le moment</p>
                            </div>
                        </div>

                        <!-- Onglet Évaluations Mobile -->
                        <div class="mobile-tab-content" id="mobile-reviews">
                            <h5 class="fw-bold mb-3">Évaluations des étudiants</h5>
                            @if($course->reviews && $course->reviews->count() > 0)
                                <div class="reviews-summary mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rating me-3">
                                            @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= ($course->reviews->avg('rating') ?? 0) ? 'text-warning' : 'text-muted' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="fw-bold">{{ number_format($course->reviews->avg('rating') ?? 0, 1) }}</span>
                                        <span class="text-muted ms-2">({{ $course->reviews->count() }} avis)</span>
                                    </div>
                                </div>
                                
                                <div class="reviews-list">
                                    @foreach($course->reviews->take(5) as $review)
                                    <div class="review-item mb-3">
                                        <div class="d-flex align-items-start">
                                            <div class="reviewer-avatar me-3">
                                                @if($review->user->avatar)
                                                <img src="{{ $instructor->avatar }}" 
                                                     alt="{{ $review->user->name }}" 
                                                     class="rounded-circle" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="review-content">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h6 class="mb-0 me-2">{{ $review->user->name }}</h6>
                                                    <div class="rating">
                                                        @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                                        @endfor
                                                    </div>
                                                </div>
                                                <p class="text-muted mb-0">{{ $review->comment }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucune évaluation pour le moment</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'aperçu vidéo -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #003366; color: white;">
                <h5 class="modal-title">Aperçu du cours</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="video-player-container">
                    <div class="video-wrapper">
                        <video id="preview-video" class="video-player" controls>
                            <source src="{{ $course->video_preview ? Storage::url($course->video_preview) : '' }}" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture vidéo.
                        </video>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles pour la page d'apprentissage */
.learning-page-wrapper {
    background-color: #f8f9fa;
    min-height: 100vh;
}

/* Sidebar */
.sidebar-container {
    background-color: #fff;
    border-right: 1px solid #e9ecef;
    height: 100vh;
    overflow-y: auto;
    position: sticky;
    top: 0;
}

.learning-sidebar {
    padding: 1.5rem;
}

.course-header h6 {
    color: #003366;
    font-size: 0.875rem; /* text-sm */
    line-height: 1.3;
}

/* Progression */
.progress-section {
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

/* Programme du cours */
.program-header {
    padding: 0.75rem 0;
    border-bottom: 2px solid #003366;
    margin-bottom: 1rem;
}

.program-header h6 {
    color: #003366;
    font-size: 1rem; /* text-base */
}

/* Sections */
.section-item {
    margin-bottom: 0.5rem;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    overflow: hidden;
}

.section-header {
    background-color: #f8f9fa;
    padding: 1rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.section-header:hover {
    background-color: #e9ecef;
}

.section-title {
    color: #003366;
    font-size: 0.875rem; /* text-sm */
    font-weight: 600;
}

.section-arrow {
    color: #6c757d;
    transition: transform 0.2s;
}

.section-header[aria-expanded="true"] .section-arrow {
    transform: rotate(180deg);
}

/* Leçons */
.lessons-list {
    background-color: #fff;
}

.lesson-item {
    border-bottom: 1px solid #f1f3f4;
}

.lesson-item:last-child {
    border-bottom: none;
}

.lesson-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: inherit;
    transition: background-color 0.2s;
    cursor: pointer;
}

.lesson-link:hover {
    background-color: #f8f9fa;
    text-decoration: none;
    color: inherit;
}

.lesson-item.active .lesson-link {
    background-color: #003366;
    color: white;
}

.lesson-item.completed .lesson-link {
    background-color: #d4edda;
}

.lesson-item.started .lesson-link {
    background-color: #fff3cd;
}

.lesson-content {
    display: flex;
    align-items: center;
    flex: 1;
}

.lesson-icon {
    margin-right: 0.75rem;
    font-size: 1.125rem; /* text-lg */
    width: 20px;
    text-align: center;
}

.lesson-item.active .lesson-icon {
    color: white;
}

.lesson-info {
    flex: 1;
}

.lesson-title {
    font-size: 0.875rem; /* text-sm */
    font-weight: 500;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

.lesson-duration {
    color: #6c757d;
    font-size: 0.75rem; /* text-xs */
}

.lesson-item.active .lesson-duration {
    color: rgba(255, 255, 255, 0.8);
}

.lesson-status {
    font-size: 1rem; /* text-base */
}

/* Contenu principal */
.main-content {
    background-color: #fff;
    min-height: 100vh;
}

.lesson-container {
    padding: 2rem;
    max-width: 1000px;
    margin: 0 auto;
}

/* Section lecteur */
.player-section {
    margin-bottom: 2rem;
}

.player-header h1 {
    color: #003366;
    font-size: 1.875rem; /* text-3xl */
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.lesson-description {
    font-size: 1rem; /* text-base */
    line-height: 1.5;
}

/* Lecteur moderne - Position sticky */
.modern-player-container {
    background-color: #000;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 2rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    position: sticky;
    top: 20px;
    z-index: 100;
}

.sticky-player {
    position: sticky;
    top: 20px;
    z-index: 100;
}

/* Lecteur vidéo moderne */
.modern-video-player {
    position: relative;
    width: 100%;
    background: #000;
    border-radius: 12px;
    overflow: hidden;
}

.video-container {
    position: relative;
    width: 100%;
    height: 0;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
}

/* Taille du lecteur sur desktop */
@media (min-width: 992px) {
    .video-container {
        padding-bottom: 50%; /* Taille optimale pour desktop */
    }
    
    .modern-player-container {
        min-height: 400px;
        max-height: 600px;
    }
}

.video-element {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
}

/* Contrôles vidéo personnalisés */
.video-controls {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
    padding: 20px;
    transform: translateY(100%);
    transition: transform 0.3s ease;
    z-index: 10;
}

.modern-video-player:hover .video-controls {
    transform: translateY(0);
}

.progress-container {
    margin-bottom: 15px;
}

.progress-bar {
    position: relative;
    height: 6px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
    cursor: pointer;
}

.progress-filled {
    height: 100%;
    background: #003366;
    border-radius: 3px;
    width: 0%;
    transition: width 0.1s ease;
}

.progress-handle {
    position: absolute;
    top: 50%;
    left: 0%;
    width: 16px;
    height: 16px;
    background: #003366;
    border-radius: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
    transition: opacity 0.2s ease;
}

.progress-bar:hover .progress-handle {
    opacity: 1;
}

.controls-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.left-controls, .center-controls, .right-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.control-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1rem; /* text-base */
    padding: 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
}

.control-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
}

.control-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.play-pause-btn {
    background: #003366;
    font-size: 1.125rem; /* text-lg */
}

.play-pause-btn:hover {
    background: #004080;
}

.time-display {
    color: white;
    font-size: 0.875rem; /* text-sm */
    font-weight: 500;
    white-space: nowrap;
}

.volume-slider {
    width: 80px;
}

.volume-range {
    width: 100%;
    height: 4px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
    outline: none;
    cursor: pointer;
}

.volume-range::-webkit-slider-thumb {
    appearance: none;
    width: 16px;
    height: 16px;
    background: #003366;
    border-radius: 50%;
    cursor: pointer;
}

.prev-lesson-btn, .next-lesson-btn {
    background: rgba(255, 255, 255, 0.1);
    font-size: 0.875rem; /* text-sm */
    padding: 8px 12px;
    border-radius: 20px;
}

.prev-lesson-btn:hover, .next-lesson-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.speed-btn {
    background: rgba(255, 255, 255, 0.1);
    font-size: 0.75rem; /* text-xs */
    font-weight: 600;
}

.fullscreen-btn {
    background: rgba(255, 255, 255, 0.1);
}

/* Overlays */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 20;
}

.loading-overlay.show {
    display: flex;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid #003366;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.play-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 15;
    transition: opacity 0.3s ease;
}

.play-overlay.hidden {
    opacity: 0;
    pointer-events: none;
}

.play-button {
    width: 80px;
    height: 80px;
    background: rgba(0, 51, 102, 0.9);
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 2rem; /* text-2xl */
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.play-button:hover {
    background: #003366;
    transform: scale(1.1);
}

/* Lecteur de texte moderne */
.modern-text-reader {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.text-reader-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.reader-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.font-size-btn {
    background: #003366;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.font-size-btn:hover {
    background: #004080;
}

.font-size-display {
    font-weight: 600;
    color: #003366;
    min-width: 50px;
    text-align: center;
}

.theme-btn {
    background: #6c757d;
    color: white;
}

.theme-btn:hover {
    background: #5a6268;
}

.lesson-navigation {
    display: flex;
    gap: 10px;
}

#text-content {
    padding: 30px;
    line-height: 1.8;
    font-size: 1rem; /* text-base */
    color: #333;
    max-height: 600px;
    overflow-y: auto;
}

/* Taille des lecteurs sur desktop */
@media (min-width: 992px) {
    #text-content {
        max-height: 600px;
        padding: 30px;
        font-size: 1rem; /* text-base */
    }
    
    .pdf-container {
        height: 600px;
    }
    
    .quiz-content, .quiz-placeholder {
        padding: 40px;
    }
    
    .audio-container {
        padding: 30px;
    }
}

/* Lecteur de quiz moderne */
.modern-quiz-player {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.quiz-header {
    background: linear-gradient(135deg, #003366, #004080);
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.quiz-header h4 {
    margin: 0;
    font-size: 1.5rem; /* text-2xl */
}

.quiz-content {
    padding: 40px;
    text-align: center;
}

.quiz-placeholder {
    padding: 40px;
}

/* Lecteur PDF moderne */
.modern-pdf-viewer {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.pdf-viewer-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.pdf-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.zoom-level {
    font-weight: 600;
    color: #003366;
    min-width: 50px;
    text-align: center;
}

.pdf-container {
    height: 600px;
    position: relative;
}

.pdf-iframe {
    width: 100%;
    height: 100%;
    border: none;
}

/* Lecteur audio moderne */
.modern-audio-player {
    background: linear-gradient(135deg, #003366, #004080);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.audio-container {
    padding: 30px;
    text-align: center;
}

.audio-visualizer {
    margin-bottom: 30px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.visualizer-bars {
    display: flex;
    align-items: end;
    gap: 4px;
    height: 60px;
}

.bar {
    width: 6px;
    background: rgba(255, 255, 255, 0.6);
    border-radius: 3px;
    animation: audioWave 1.5s ease-in-out infinite;
}

.bar:nth-child(1) { animation-delay: 0s; }
.bar:nth-child(2) { animation-delay: 0.1s; }
.bar:nth-child(3) { animation-delay: 0.2s; }
.bar:nth-child(4) { animation-delay: 0.3s; }
.bar:nth-child(5) { animation-delay: 0.4s; }

@keyframes audioWave {
    0%, 100% { height: 20px; }
    50% { height: 60px; }
}

.audio-element {
    display: none;
}

.audio-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.audio-progress {
    flex: 1;
    max-width: 300px;
}

.audio-progress .progress-bar {
    height: 8px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
}

.audio-progress .progress-filled {
    background: white;
}

.audio-progress .progress-handle {
    background: white;
}

.audio-controls .time-display {
    color: white;
    font-weight: 600;
}

.audio-controls .control-btn {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.audio-controls .control-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.audio-controls .play-pause-btn {
    background: white;
    color: #003366;
    font-size: 1.25rem; /* text-xl */ /* text-xl */
    width: 50px;
    height: 50px;
    border-radius: 50%;
}

.audio-controls .play-pause-btn:hover {
    background: #f8f9fa;
}

/* Programme du cours mobile */
.course-program-mobile {
    padding: 0;
}

.course-program-mobile .program-header {
    padding: 0.75rem 0;
    border-bottom: 2px solid #003366;
    margin-bottom: 1rem;
}

.course-program-mobile .program-header h5 {
    color: #003366;
    font-size: 1.125rem; /* text-lg */
}

/* Responsive pour les lecteurs */
@media (max-width: 768px) {
    .modern-player-container {
        position: relative;
        top: auto;
        margin-bottom: 1rem;
    }
    
    .sticky-player {
        position: relative;
        top: auto;
    }
    
    .controls-row {
        flex-direction: column;
        gap: 10px;
    }
    
    .left-controls, .center-controls, .right-controls {
        justify-content: center;
        width: 100%;
    }
    
    .volume-slider {
        width: 120px;
    }
    
    .text-reader-header, .quiz-header, .pdf-viewer-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .reader-controls, .pdf-controls {
        justify-content: center;
    }
    
    .lesson-navigation {
        justify-content: center;
    }
    
    #text-content {
        padding: 20px;
        font-size: 0.875rem; /* text-sm */
    }
    
    .quiz-content, .quiz-placeholder {
        padding: 20px;
    }
    
    .audio-container {
        padding: 20px;
    }
    
    .audio-controls {
        flex-direction: column;
        gap: 15px;
    }
    
    .audio-progress {
        max-width: 100%;
    }
    
    /* Ordre des onglets sur mobile */
    .nav-tabs .nav-item:nth-child(1) {
        order: 1; /* Contenu en premier */
    }
    
    .nav-tabs .nav-item:nth-child(2) {
        order: 2; /* Présentation en second */
    }
    
    .nav-tabs .nav-item:nth-child(3) {
        order: 3; /* Q&R en troisième */
    }
    
    .nav-tabs .nav-item:nth-child(4) {
        order: 4; /* Annonces en quatrième */
    }
    
    .nav-tabs .nav-item:nth-child(5) {
        order: 5; /* Évaluations en dernier */
    }
}

/* Contenu texte */
.text-content {
    background-color: #f8f9fa;
    padding: 2rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    margin-bottom: 2rem;
    line-height: 1.6;
}

/* Contenu vide */
.no-content, .quiz-content {
    text-align: center;
    padding: 3rem 2rem;
    background-color: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    margin-bottom: 2rem;
}

/* Aperçu du cours */
.course-preview {
    background-color: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
}

.video-play-overlay {
    z-index: 10;
}

/* Actions de la leçon */
.lesson-actions-footer {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-top: 2rem;
    border: 1px solid #e9ecef;
}

/* Actions plus compactes sur desktop */
@media (min-width: 992px) {
    .lesson-actions-footer {
        padding: 1rem 1.5rem;
        margin-top: 1.5rem;
    }
    
    .lesson-actions-footer .d-flex {
        justify-content: center;
        gap: 3rem;
    }
    
    .lesson-actions-footer .lesson-navigation {
        order: 1;
    }
    
    .lesson-actions-footer .lesson-progress {
        order: 2;
    }
}

.lesson-actions-footer .lesson-navigation {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.lesson-actions-footer .lesson-progress {
    display: flex;
    align-items: center;
}

.lesson-actions-footer .lesson-actions {
    display: flex;
    align-items: center;
}

/* Responsive pour les actions */
@media (max-width: 768px) {
    .lesson-actions-footer {
        padding: 1rem;
    }
    
    .lesson-actions-footer .d-flex {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .lesson-actions-footer .lesson-navigation,
    .lesson-actions-footer .lesson-progress,
    .lesson-actions-footer .lesson-actions {
        justify-content: center;
        width: 100%;
    }
    
    .lesson-actions-footer .lesson-navigation {
        order: 1;
    }
    
    .lesson-actions-footer .lesson-progress {
        order: 2;
    }
    
    .lesson-actions-footer .lesson-actions {
        order: 3;
    }
}

/* Section onglets */
.tabs-section {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.nav-tabs {
    border-bottom: 2px solid #003366;
    padding: 0 1rem;
}

.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 1rem 1.5rem;
    font-weight: 500;
}

.nav-tabs .nav-link:hover {
    color: #003366;
    border-bottom-color: #003366;
}

.nav-tabs .nav-link.active {
    color: #003366;
    background-color: transparent;
    border-bottom-color: #003366;
}

.tab-content-wrapper {
    padding: 2rem;
}

/* NOUVELLE STRUCTURE MOBILE - CSS dédié */
.mobile-tabs-section {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 0.5rem;
    margin-bottom: 1rem;
}

.mobile-tabs-nav {
    display: flex;
    overflow-x: auto;
    gap: 0.5rem;
    padding: 0.25rem 0;
}

.mobile-tab-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0.75rem 1rem;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    color: #6c757d;
    text-decoration: none;
    transition: all 0.3s ease;
    min-width: 80px;
    white-space: nowrap;
}

.mobile-tab-btn:hover {
    background: #f8f9fa;
    color: #003366;
    text-decoration: none;
}

.mobile-tab-btn.active {
    background: #003366;
    color: white;
    border-color: #003366;
}

.mobile-tab-btn i {
    font-size: 1.2rem;
    margin-bottom: 0.25rem;
}

.mobile-tab-btn span {
    font-size: 0.75rem;
    font-weight: 500;
}

/* Contenu mobile */
.mobile-content {
    padding: 0 1rem;
}

.mobile-tab-content {
    display: none;
    padding: 0;
    margin: 0;
}

.mobile-tab-content.active {
    display: block;
}

/* Éliminer tous les espaces vides dans le contenu mobile */
.mobile-tab-content > *:first-child {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

.mobile-tab-content h5:first-child {
    margin-top: 0 !important;
    padding-top: 0 !important;
    line-height: 1.2 !important;
}
    
    .course-description h5,
    .course-stats h5,
    .what-youll-learn h5,
    .course-requirements h5,
    .instructor-info h5,
    .recommended-courses h5 {
        margin-bottom: 1rem !important;
        font-size: 1.125rem; /* text-lg */
    }
    
    /* Styles pour les cartes de cours recommandés */
    .recommended-courses .course-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        background: white;
    }
    
    .recommended-courses .course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,51,102,0.15);
        border-color: #003366;
    }
    
    .recommended-courses .course-card .card-body {
        padding: 0;
    }
    
    .recommended-courses .course-thumbnail {
        width: 120px;
        height: 80px;
        flex-shrink: 0;
        overflow: hidden;
        position: relative;
    }
    
    .recommended-courses .course-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .recommended-courses .course-card:hover .course-thumbnail img {
        transform: scale(1.05);
    }
    
    .recommended-courses .course-thumbnail .bg-primary {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #003366, #004d99) !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .recommended-courses .course-info {
        min-height: 80px;
    }
    
    .recommended-courses .card-title {
        font-size: 1rem;
        line-height: 1.3;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .recommended-courses .rating i {
        font-size: 0.8rem;
        margin-right: 1px;
    }
    
    .recommended-courses .btn-primary {
        background: linear-gradient(135deg, #003366, #004d99);
        border: none;
        border-radius: 6px;
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .recommended-courses .btn-primary:hover {
        background: linear-gradient(135deg, #002244, #003366);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,51,102,0.3);
    }
    
    .recommended-courses .text-primary {
        color: #003366 !important;
        font-weight: 600;
    }
    
    .recommended-courses h5 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 1.5rem;
        position: relative;
    }
    
    .recommended-courses h5::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(135deg, #003366, #004d99);
        border-radius: 2px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .recommended-courses .course-thumbnail {
            width: 100px;
            height: 70px;
        }
        
        .recommended-courses .course-info {
            min-height: 70px;
        }
        
        .recommended-courses .card-title {
            font-size: 0.9rem;
        }
    }
    
    /* Réduire l'espace dans les statistiques */
    .stat-item {
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }
    
    .stat-item h6 {
        font-size: 0.875rem; /* text-sm */
        margin-bottom: 0.25rem;
    }
    
    .stat-item .stat-value {
        font-size: 1.125rem; /* text-lg */
    }
    
    /* Réduire l'espace dans les sections Q&R, Annonces, Évaluations */
    .qa-section,
    .announcements-section,
    .reviews-section {
        padding: 0.5rem 0;
    }
    
    .qa-section h5,
    .announcements-section h5,
    .reviews-section h5 {
        margin-bottom: 1rem !important;
        font-size: 1.125rem; /* text-lg */
    }
    
    .qa-section .text-center,
    .announcements-section .text-center {
        padding: 2rem 1rem !important;
    }
    
    /* Réduire l'espace dans les avis */
    .review-item {
        margin-bottom: 1rem !important;
        padding: 0.75rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
    }
    
    .reviews-summary {
        margin-bottom: 1rem !important;
    }
}

/* Statistiques */
.stat-item {
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.stat-icon {
    font-size: 1.5rem; /* text-2xl */
}

.stat-value {
    font-size: 1.25rem; /* text-xl */
    color: #003366;
}

/* Avis */
.review-item {
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.rating {
    color: #ffc107;
}

/* Styles pour la section mobile */
.mobile-header {
    background-color: #fff;
    border-bottom: 1px solid #e9ecef;
    padding: 1rem;
}

.mobile-course-header {
    margin-bottom: 1rem;
}

.mobile-player-section {
    margin-bottom: 1rem;
}

.mobile-player {
    position: relative;
    margin-bottom: 1rem;
}

.mobile-actions {
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin-top: 1rem;
}

/* Responsive */
@media (max-width: 991.98px) {
    .sidebar-container {
        display: none !important;
    }
    
    .main-content {
        padding: 0;
    }
    
    .lesson-container {
        padding: 0;
    }
    
    .main-content {
        width: 100%;
    }
    
    /* S'assurer que l'onglet Présentation ne s'affiche pas sur mobile sauf quand activé */
    .tab-pane#presentation {
        display: none !important;
    }
    
    .tab-pane#presentation.show {
        display: block !important;
    }
}
    
    .player-header h1 {
        font-size: 1.5rem; /* text-2xl */ /* text-2xl */
    }
    
    .lesson-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .lesson-actions .btn {
        width: 100%;
    }
    
    .nav-tabs {
        flex-wrap: wrap;
    }
    
    .nav-tabs .nav-link {
        padding: 0.75rem 1rem;
        font-size: 0.875rem; /* text-sm */
    }
}

@media (max-width: 767.98px) {
    .lesson-container {
        padding: 0.75rem;
    }
    
    .player-header h1 {
        font-size: 1.25rem; /* text-xl */ /* text-xl */
    }
    
    .tab-content-wrapper {
        padding: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser la liste des leçons
    initializeLessonsList();
    
    // Gestion des onglets mobiles
    const mobileTabBtns = document.querySelectorAll('.mobile-tab-btn');
    const mobileTabContents = document.querySelectorAll('.mobile-tab-content');
    
    mobileTabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Désactiver tous les boutons
            mobileTabBtns.forEach(b => b.classList.remove('active'));
            
            // Désactiver tout le contenu
            mobileTabContents.forEach(content => content.classList.remove('active'));
            
            // Activer le bouton cliqué
            this.classList.add('active');
            
            // Activer le contenu correspondant
            const targetContent = document.getElementById('mobile-' + targetTab);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
    
    // Gestion des sections collapsibles
    const sectionHeaders = document.querySelectorAll('.section-header');
    sectionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-bs-target'));
            const arrow = this.querySelector('.section-arrow');
            
            if (target.classList.contains('show')) {
                arrow.style.transform = 'rotate(0deg)';
            } else {
                arrow.style.transform = 'rotate(180deg)';
            }
        });
    });

    // Gestion des onglets mobile
    const contentTab = document.getElementById('content-tab');
    const presentationTabMobile = document.getElementById('presentation-tab-mobile');
    
    // Fonction pour gérer l'activation des onglets
    function activateTab(tabId) {
        // Désactiver tous les onglets
        document.querySelectorAll('.nav-link').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Désactiver tous les contenus d'onglets
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
        });
        
        // Activer l'onglet sélectionné
        const selectedTab = document.getElementById(tabId);
        if (selectedTab) {
            selectedTab.classList.add('active');
        }
        
        // Activer le contenu correspondant
        const targetId = tabId.replace('-tab', '').replace('-mobile', '');
        const selectedPane = document.getElementById(targetId);
        if (selectedPane) {
            selectedPane.classList.add('show', 'active');
            
            // Sur mobile, s'assurer que l'onglet Présentation s'affiche correctement
            if (targetId === 'presentation' && window.innerWidth < 992) {
                selectedPane.style.display = 'block';
            }
        }
    }
    
    if (contentTab) {
        contentTab.addEventListener('click', function() {
            activateTab('content-tab');
        });
    }
    
    if (presentationTabMobile) {
        presentationTabMobile.addEventListener('click', function() {
            activateTab('presentation-tab-mobile');
        });
    }
    
    // Initialisation : s'assurer que l'onglet correct est actif au chargement
    if (window.innerWidth < 992) {
        // Sur mobile, activer l'onglet Contenu par défaut
        activateTab('content-tab');
    }
    
    // Gestion des autres onglets
    const qaTab = document.getElementById('qa-tab');
    const announcementsTab = document.getElementById('announcements-tab');
    const reviewsTab = document.getElementById('reviews-tab');
    
    if (qaTab) {
        qaTab.addEventListener('click', function() {
            activateTab('qa-tab');
        });
    }
    
    if (announcementsTab) {
        announcementsTab.addEventListener('click', function() {
            activateTab('announcements-tab');
        });
    }
    
    if (reviewsTab) {
        reviewsTab.addEventListener('click', function() {
            activateTab('reviews-tab');
        });
    }

    // Gestion du bouton de completion
    const completeBtn = document.getElementById('complete-lesson-btn');
    if (completeBtn) {
        completeBtn.addEventListener('click', function() {
            const lessonId = this.dataset.lessonId;
            const courseId = this.dataset.courseId;
            
            // Ici vous pouvez ajouter la logique AJAX pour marquer la leçon comme terminée
            console.log('Marquer la leçon comme terminée:', lessonId, courseId);
            
            // Mise à jour de l'interface
            this.innerHTML = '<i class="fas fa-check-circle me-2"></i>Leçon terminée';
            this.classList.remove('btn-success');
            this.classList.add('alert', 'alert-success');
            this.style.display = 'inline-block';
        });
    }

    // Initialiser les lecteurs
    initializePlayers();
    
    // Initialiser la liste des leçons
    initializeLessonsList();
    
    // Sélectionner automatiquement la première leçon si aucune leçon n'est active
    @if(!isset($activeLesson))
    if (allLessons.length > 0) {
        const firstLesson = allLessons[0];
        console.log('Sélection automatique de la première leçon:', firstLesson.id);
        loadLesson(firstLesson.id);
    } else {
        // Si aucune leçon n'est disponible, activer l'onglet approprié selon l'écran
        if (window.innerWidth >= 992) {
            activateTab('presentation-tab');
        } else {
            activateTab('content-tab');
        }
    }
    @else
    // Si une leçon est déjà active, s'assurer que l'onglet correct est actif
    if (window.innerWidth < 992) {
        activateTab('content-tab');
    }
    @endif
    
    // Gestion des boutons de navigation globaux
    setupGlobalNavigation();
});

// Fonction pour configurer la navigation globale
function setupGlobalNavigation() {
    // Gestion des boutons Précédent/Suivant globaux
    document.addEventListener('click', function(e) {
        if (e.target.closest('.lesson-navigation button')) {
            const button = e.target.closest('.lesson-navigation button');
            if (button.onclick) {
                button.onclick();
            }
        }
    });
}

// Variables globales pour la navigation
let currentLessonId = null;
let allLessons = [];

// Fonction pour initialiser la liste des leçons
function initializeLessonsList() {
    console.log('=== INITIALISATION DE LA LISTE DES LECONS ===');
    allLessons = [];
    
    // Récupérer toutes les leçons dans l'ordre correct
    const allLessonElements = Array.from(document.querySelectorAll('.lesson-item'));
    console.log('Total leçons trouvées:', allLessonElements.length);
    
    // Créer un tableau avec les informations de tri
    const lessonsWithOrder = allLessonElements.map(item => {
        const section = item.closest('.section-item');
        const sectionOrder = parseInt(section?.dataset.sectionOrder || 0);
        const lessonOrder = parseInt(item.dataset.lessonOrder || 0);
        
        console.log(`Leçon trouvée: ID=${item.dataset.lessonId}, Section=${sectionOrder}, Ordre=${lessonOrder}, Titre="${item.dataset.lessonTitle}"`);
        
        return {
            element: item,
            sectionOrder: sectionOrder,
            lessonOrder: lessonOrder,
            id: parseInt(item.dataset.lessonId),
            title: item.dataset.lessonTitle,
            type: item.dataset.lessonType,
            content: item.dataset.lessonContent
        };
    });
    
    // Trier par section puis par leçon pour respecter l'ordre séquentiel
    console.log('=== AVANT TRI ===');
    console.log('Leçons à trier:', lessonsWithOrder.map(l => ({ id: l.id, section: l.sectionOrder, order: l.lessonOrder, title: l.title })));
    
    lessonsWithOrder.sort((a, b) => {
        // D'abord trier par section
        if (a.sectionOrder !== b.sectionOrder) {
            return a.sectionOrder - b.sectionOrder;
        }
        // Puis par ordre de leçon dans la section
        return a.lessonOrder - b.lessonOrder;
    });
    
    console.log('=== APRÈS TRI ===');
    console.log('Leçons triées:', lessonsWithOrder.map(l => ({ id: l.id, section: l.sectionOrder, order: l.lessonOrder, title: l.title })));
    
    // Créer la liste finale des leçons
    lessonsWithOrder.forEach((lessonData, globalIndex) => {
        const lesson = {
            id: lessonData.id,
            element: lessonData.element,
            title: lessonData.title,
            type: lessonData.type,
            content: lessonData.content,
            globalIndex: globalIndex
        };
        allLessons.push(lesson);
        console.log(`  Leçon ${globalIndex}: ID=${lesson.id}, Titre="${lesson.title}"`);
    });
    
    console.log('=== RÉSULTAT FINAL ===');
    console.log('Total leçons:', allLessons.length);
    console.log('Ordre des leçons:', allLessons.map((l, i) => ({ 
        index: i,
        id: l.id, 
        title: l.title,
        globalIndex: l.globalIndex
    })));
}

// Fonction pour naviguer vers la leçon précédente
function navigateToPrevious() {
    console.log('=== NAVIGATION PRÉCÉDENTE ===');
    
    if (!currentLessonId) {
        console.log('Aucune leçon actuelle');
        return;
    }
    
    // S'assurer que la liste des leçons est initialisée
    if (!allLessons || allLessons.length === 0) {
        console.log('Initialisation de la liste des leçons...');
        initializeLessonsList();
    }
    
    const currentIndex = allLessons.findIndex(lesson => lesson.id === currentLessonId);
    console.log('Leçon actuelle:', currentLessonId, 'Index:', currentIndex);
    
    if (currentIndex > 0) {
        const previousLesson = allLessons[currentIndex - 1];
        console.log('Navigation vers:', previousLesson.id, previousLesson.title);
        loadLesson(previousLesson.id);
    } else {
        console.log('Aucune leçon précédente disponible');
    }
}

// Fonction pour naviguer vers la leçon suivante
function navigateToNext() {
    console.log('=== NAVIGATION SUIVANTE ===');
    
    if (!currentLessonId) {
        console.log('Aucune leçon actuelle');
        return;
    }
    
    // S'assurer que la liste des leçons est initialisée
    if (!allLessons || allLessons.length === 0) {
        console.log('Initialisation de la liste des leçons...');
        initializeLessonsList();
    }
    
    const currentIndex = allLessons.findIndex(lesson => lesson.id === currentLessonId);
    console.log('Leçon actuelle:', currentLessonId, 'Index:', currentIndex);
    
    if (currentIndex < allLessons.length - 1) {
        const nextLesson = allLessons[currentIndex + 1];
        console.log('Navigation vers:', nextLesson.id, nextLesson.title);
        loadLesson(nextLesson.id);
    } else {
        console.log('Aucune leçon suivante disponible');
    }
}


// Classe pour le lecteur vidéo moderne
class ModernVideoPlayer {
    constructor(container) {
        this.container = container;
        this.video = container.querySelector('.video-element');
        this.controls = container.querySelector('.video-controls');
        this.playPauseBtn = container.querySelector('#play-pause');
        this.progressBar = container.querySelector('.progress-bar');
        this.progressFilled = container.querySelector('.progress-filled');
        this.progressHandle = container.querySelector('.progress-handle');
        this.currentTimeEl = container.querySelector('.current-time');
        this.durationEl = container.querySelector('.duration');
        this.volumeBtn = container.querySelector('#volume-btn');
        this.volumeRange = container.querySelector('.volume-range');
        this.speedBtn = container.querySelector('#speed-btn');
        this.fullscreenBtn = container.querySelector('#fullscreen');
        this.playOverlay = container.querySelector('.play-overlay');
        this.loadingOverlay = container.querySelector('.loading-overlay');
        
        this.isPlaying = false;
        this.isDragging = false;
        this.currentSpeed = 1;
        this.speeds = [0.5, 0.75, 1, 1.25, 1.5, 2];
        this.speedIndex = 2; // Index pour 1x
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.updateDuration();
    }
    
    setupEventListeners() {
        // Play/Pause
        this.playPauseBtn.addEventListener('click', () => this.togglePlayPause());
        this.video.addEventListener('click', () => this.togglePlayPause());
        this.playOverlay.addEventListener('click', () => this.togglePlayPause());
        
        // Progression
        this.progressBar.addEventListener('click', (e) => this.seekTo(e));
        this.progressBar.addEventListener('mousedown', (e) => this.startDragging(e));
        document.addEventListener('mousemove', (e) => this.drag(e));
        document.addEventListener('mouseup', () => this.stopDragging());
        
        // Volume
        this.volumeBtn.addEventListener('click', () => this.toggleMute());
        this.volumeRange.addEventListener('input', (e) => this.setVolume(e.target.value));
        
        // Vitesse
        this.speedBtn.addEventListener('click', () => this.changeSpeed());
        
        // Plein écran
        this.fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());
        
        // Événements vidéo
        this.video.addEventListener('loadedmetadata', () => this.updateDuration());
        this.video.addEventListener('timeupdate', () => this.updateProgress());
        this.video.addEventListener('play', () => this.onPlay());
        this.video.addEventListener('pause', () => this.onPause());
        this.video.addEventListener('loadstart', () => this.showLoading());
        this.video.addEventListener('canplay', () => this.hideLoading());
        
        // Contrôles au survol
        this.container.addEventListener('mouseenter', () => this.showControls());
        this.container.addEventListener('mouseleave', () => this.hideControls());
    }
    
    togglePlayPause() {
        if (this.video.paused) {
            this.video.play();
        } else {
            this.video.pause();
        }
    }
    
    onPlay() {
        this.isPlaying = true;
        this.playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
        this.playOverlay.classList.add('hidden');
    }
    
    onPause() {
        this.isPlaying = false;
        this.playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
        this.playOverlay.classList.remove('hidden');
    }
    
    updateProgress() {
        if (this.isDragging) return;
        
        const progress = (this.video.currentTime / this.video.duration) * 100;
        this.progressFilled.style.width = `${progress}%`;
        this.progressHandle.style.left = `${progress}%`;
        this.currentTimeEl.textContent = this.formatTime(this.video.currentTime);
    }
    
    updateDuration() {
        if (this.video.duration) {
            this.durationEl.textContent = this.formatTime(this.video.duration);
        }
    }
    
    seekTo(e) {
        const rect = this.progressBar.getBoundingClientRect();
        const pos = (e.clientX - rect.left) / rect.width;
        this.video.currentTime = pos * this.video.duration;
    }
    
    startDragging(e) {
        this.isDragging = true;
        this.seekTo(e);
    }
    
    drag(e) {
        if (!this.isDragging) return;
        this.seekTo(e);
    }
    
    stopDragging() {
        this.isDragging = false;
    }
    
    toggleMute() {
        this.video.muted = !this.video.muted;
        this.volumeBtn.innerHTML = this.video.muted ? '<i class="fas fa-volume-mute"></i>' : '<i class="fas fa-volume-up"></i>';
        this.volumeRange.value = this.video.muted ? 0 : this.video.volume * 100;
    }
    
    setVolume(value) {
        this.video.volume = value / 100;
        this.video.muted = value == 0;
        this.volumeBtn.innerHTML = value == 0 ? '<i class="fas fa-volume-mute"></i>' : '<i class="fas fa-volume-up"></i>';
    }
    
    changeSpeed() {
        this.speedIndex = (this.speedIndex + 1) % this.speeds.length;
        this.currentSpeed = this.speeds[this.speedIndex];
        this.video.playbackRate = this.currentSpeed;
        this.speedBtn.querySelector('.speed-text').textContent = `${this.currentSpeed}x`;
    }
    
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            this.container.requestFullscreen();
            this.fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
        } else {
            document.exitFullscreen();
            this.fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
        }
    }
    
    showControls() {
        this.controls.style.transform = 'translateY(0)';
    }
    
    hideControls() {
        if (this.isPlaying) {
            this.controls.style.transform = 'translateY(100%)';
        }
    }
    
    showLoading() {
        this.loadingOverlay.classList.add('show');
    }
    
    hideLoading() {
        this.loadingOverlay.classList.remove('show');
    }
    
    formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
}

// Classe pour le lecteur de texte moderne
class ModernTextReader {
    constructor(container) {
        this.container = container;
        this.textContent = container.querySelector('#text-content');
        this.fontDecreaseBtn = container.querySelector('#font-decrease');
        this.fontIncreaseBtn = container.querySelector('#font-increase');
        this.fontSizeDisplay = container.querySelector('.font-size-display');
        this.themeBtn = container.querySelector('#theme-toggle');
        
        this.currentFontSize = 100;
        this.isDarkTheme = false;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        this.fontDecreaseBtn.addEventListener('click', () => this.decreaseFontSize());
        this.fontIncreaseBtn.addEventListener('click', () => this.increaseFontSize());
        this.themeBtn.addEventListener('click', () => this.toggleTheme());
    }
    
    decreaseFontSize() {
        if (this.currentFontSize > 50) {
            this.currentFontSize -= 10;
            this.updateFontSize();
        }
    }
    
    increaseFontSize() {
        if (this.currentFontSize < 200) {
            this.currentFontSize += 10;
            this.updateFontSize();
        }
    }
    
    updateFontSize() {
        this.textContent.style.fontSize = `${this.currentFontSize}%`;
        this.fontSizeDisplay.textContent = `${this.currentFontSize}%`;
    }
    
    toggleTheme() {
        this.isDarkTheme = !this.isDarkTheme;
        
        if (this.isDarkTheme) {
            this.textContent.style.backgroundColor = '#1a1a1a';
            this.textContent.style.color = '#ffffff';
            this.themeBtn.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            this.textContent.style.backgroundColor = '#ffffff';
            this.textContent.style.color = '#333333';
            this.themeBtn.innerHTML = '<i class="fas fa-moon"></i>';
        }
    }
}

// Classe pour le lecteur audio moderne
class ModernAudioPlayer {
    constructor(container) {
        this.container = container;
        this.audio = container.querySelector('.audio-element');
        this.playPauseBtn = container.querySelector('#audio-play-pause');
        this.progressBar = container.querySelector('.audio-progress .progress-bar');
        this.progressFilled = container.querySelector('.audio-progress .progress-filled');
        this.progressHandle = container.querySelector('.audio-progress .progress-handle');
        this.currentTimeEl = container.querySelector('.audio-controls .current-time');
        this.durationEl = container.querySelector('.audio-controls .duration');
        this.volumeBtn = container.querySelector('#audio-volume-btn');
        this.visualizerBars = container.querySelectorAll('.bar');
        
        this.isPlaying = false;
        this.isDragging = false;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.updateDuration();
        this.startVisualizer();
    }
    
    setupEventListeners() {
        this.playPauseBtn.addEventListener('click', () => this.togglePlayPause());
        this.progressBar.addEventListener('click', (e) => this.seekTo(e));
        this.progressBar.addEventListener('mousedown', (e) => this.startDragging(e));
        document.addEventListener('mousemove', (e) => this.drag(e));
        document.addEventListener('mouseup', () => this.stopDragging());
        
        this.audio.addEventListener('loadedmetadata', () => this.updateDuration());
        this.audio.addEventListener('timeupdate', () => this.updateProgress());
        this.audio.addEventListener('play', () => this.onPlay());
        this.audio.addEventListener('pause', () => this.onPause());
    }
    
    togglePlayPause() {
        if (this.audio.paused) {
            this.audio.play();
        } else {
            this.audio.pause();
        }
    }
    
    onPlay() {
        this.isPlaying = true;
        this.playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
    }
    
    onPause() {
        this.isPlaying = false;
        this.playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
    }
    
    updateProgress() {
        if (this.isDragging) return;
        
        const progress = (this.audio.currentTime / this.audio.duration) * 100;
        this.progressFilled.style.width = `${progress}%`;
        this.progressHandle.style.left = `${progress}%`;
        this.currentTimeEl.textContent = this.formatTime(this.audio.currentTime);
    }
    
    updateDuration() {
        if (this.audio.duration) {
            this.durationEl.textContent = this.formatTime(this.audio.duration);
        }
    }
    
    seekTo(e) {
        const rect = this.progressBar.getBoundingClientRect();
        const pos = (e.clientX - rect.left) / rect.width;
        this.audio.currentTime = pos * this.audio.duration;
    }
    
    startDragging(e) {
        this.isDragging = true;
        this.seekTo(e);
    }
    
    drag(e) {
        if (!this.isDragging) return;
        this.seekTo(e);
    }
    
    stopDragging() {
        this.isDragging = false;
    }
    
    startVisualizer() {
        setInterval(() => {
            if (this.isPlaying) {
                this.visualizerBars.forEach((bar, index) => {
                    const height = Math.random() * 60 + 20;
                    bar.style.height = `${height}px`;
                });
            }
        }, 150);
    }
    
    formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
}

// Fonction pour initialiser tous les lecteurs
function initializePlayers() {
    // Lecteur vidéo
    const videoPlayer = document.querySelector('.modern-video-player');
    if (videoPlayer) {
        new ModernVideoPlayer(videoPlayer);
    }
    
    // Lecteur de texte
    const textReader = document.querySelector('.modern-text-reader');
    if (textReader) {
        new ModernTextReader(textReader);
    }
    
    // Lecteur audio
    const audioPlayer = document.querySelector('.modern-audio-player');
    if (audioPlayer) {
        new ModernAudioPlayer(audioPlayer);
    }
}

// Fonction pour charger une leçon
function loadLesson(lessonId) {
    console.log('=== CHARGEMENT DE LA LECON ===');
    console.log('ID de la leçon à charger:', lessonId);
    console.log('Type de lessonId:', typeof lessonId);
    console.log('Stack trace:', new Error().stack);
    
    // Initialiser la liste des leçons si nécessaire
    if (allLessons.length === 0) {
        console.log('Initialisation de la liste des leçons...');
        initializeLessonsList();
    }
    
    // Mettre à jour la leçon actuelle
    currentLessonId = lessonId;
    console.log('Leçon actuelle mise à jour:', currentLessonId);
    
    // Récupérer les données de la leçon
    const lessonItem = document.querySelector(`[data-lesson-id="${lessonId}"]`);
    if (!lessonItem) {
        console.error('Leçon non trouvée:', lessonId);
        return;
    }
    
    const lessonType = lessonItem.dataset.lessonType;
    const lessonTitle = lessonItem.dataset.lessonTitle;
    const lessonDescription = lessonItem.dataset.lessonDescription;
    const lessonContent = lessonItem.dataset.lessonContent;
    
    console.log('Données de la leçon:', {
        id: lessonId,
        type: lessonType,
        title: lessonTitle,
        description: lessonDescription,
        hasContent: !!lessonContent
    });
    
    // Mettre à jour le titre et la description (desktop)
    const titleElement = document.getElementById('current-lesson-title');
    const descriptionElement = document.getElementById('current-lesson-description');
    
    if (titleElement) titleElement.textContent = lessonTitle;
    if (descriptionElement) descriptionElement.textContent = lessonDescription;
    
    // Mettre à jour le titre et la description (mobile)
    const mobileTitleElement = document.getElementById('mobile-lesson-title');
    const mobileDescriptionElement = document.getElementById('mobile-lesson-description');
    
    if (mobileTitleElement) mobileTitleElement.textContent = lessonTitle;
    if (mobileDescriptionElement) mobileDescriptionElement.textContent = lessonDescription;
    
    // Mettre à jour le contenu du lecteur
    const playerWrapper = document.getElementById('player-wrapper');
    
    // Supprimer la classe active de toutes les leçons (desktop et mobile)
    document.querySelectorAll('.lesson-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Ajouter la classe active à toutes les instances de la leçon sélectionnée
    document.querySelectorAll(`[data-lesson-id="${lessonId}"]`).forEach(item => {
        item.classList.add('active');
    });
    
    // Générer le contenu selon le type
    let content = '';
    switch(lessonType) {
        case 'video':
            if (lessonContent) {
                content = `
                    <div class="modern-video-player" id="modern-video-player">
                        <div class="video-container">
                            <video id="lesson-video" class="video-element" preload="metadata">
                                <source src="${lessonContent}" type="video/mp4">
                                <source src="${lessonContent}" type="video/webm">
                                <source src="${lessonContent}" type="video/ogg">
                                Votre navigateur ne supporte pas la lecture vidéo.
                            </video>
                            
                            <div class="video-controls">
                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <div class="progress-filled"></div>
                                        <div class="progress-handle"></div>
                                    </div>
                                </div>
                                
                                <div class="controls-row">
                                    <div class="left-controls">
                                        <button class="control-btn play-pause-btn" id="play-pause">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        <div class="time-display">
                                            <span class="current-time">0:00</span>
                                            <span class="time-separator">/</span>
                                            <span class="duration">0:00</span>
                                        </div>
                                        <button class="control-btn volume-btn" id="volume-btn">
                                            <i class="fas fa-volume-up"></i>
                                        </button>
                                        <div class="volume-slider">
                                            <input type="range" class="volume-range" min="0" max="100" value="100">
                                        </div>
                                    </div>
                                    
                                    <div class="center-controls">
                                        <button class="control-btn prev-lesson-btn" id="prev-lesson">
                                            <i class="fas fa-step-backward"></i>
                                        </button>
                                        <button class="control-btn next-lesson-btn" id="next-lesson">
                                            <i class="fas fa-step-forward"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="right-controls">
                                        <button class="control-btn speed-btn" id="speed-btn">
                                            <span class="speed-text">1x</span>
                                        </button>
                                        <button class="control-btn fullscreen-btn" id="fullscreen">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="loading-overlay">
                                <div class="spinner"></div>
                            </div>
                            
                            <div class="play-overlay">
                                <button class="play-button">
                                    <i class="fas fa-play"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                content = `
                    <div class="no-content">
                        <i class="fas fa-video fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune vidéo disponible</p>
                    </div>
                `;
            }
            break;
        case 'text':
            if (lessonContent) {
                content = `
                    <div class="modern-text-reader">
                        <div class="text-reader-header">
                            <div class="reader-controls">
                                <button class="control-btn font-size-btn" id="font-decrease">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="font-size-display">100%</span>
                                <button class="control-btn font-size-btn" id="font-increase">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button class="control-btn theme-btn" id="theme-toggle">
                                    <i class="fas fa-moon"></i>
                                </button>
                            </div>
                            <div class="lesson-navigation">
                                <button class="control-btn prev-lesson-btn" id="prev-lesson-text">
                                    <i class="fas fa-arrow-left"></i> Précédent
                                </button>
                                <button class="control-btn next-lesson-btn" id="next-lesson-text">
                                    Suivant <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="text-content" id="text-content">
                            ${lessonContent}
                        </div>
                    </div>
                `;
            } else {
                content = `
                    <div class="no-content">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun contenu disponible</p>
                    </div>
                `;
            }
            break;
        case 'audio':
            if (lessonContent) {
                content = `
                    <div class="modern-audio-player">
                        <div class="audio-container">
                            <div class="audio-visualizer">
                                <div class="visualizer-bars">
                                    <div class="bar"></div>
                                    <div class="bar"></div>
                                    <div class="bar"></div>
                                    <div class="bar"></div>
                                    <div class="bar"></div>
                                </div>
                            </div>
                            <audio id="lesson-audio" class="audio-element" preload="metadata">
                                <source src="${lessonContent}" type="audio/mpeg">
                                <source src="${lessonContent}" type="audio/wav">
                                <source src="${lessonContent}" type="audio/ogg">
                                Votre navigateur ne supporte pas la lecture audio.
                            </audio>
                            
                            <div class="audio-controls">
                                <button class="control-btn play-pause-btn" id="audio-play-pause">
                                    <i class="fas fa-play"></i>
                                </button>
                                <div class="audio-progress">
                                    <div class="progress-bar">
                                        <div class="progress-filled"></div>
                                        <div class="progress-handle"></div>
                                    </div>
                                </div>
                                <div class="time-display">
                                    <span class="current-time">0:00</span>
                                    <span class="time-separator">/</span>
                                    <span class="duration">0:00</span>
                                </div>
                                <button class="control-btn volume-btn" id="audio-volume-btn">
                                    <i class="fas fa-volume-up"></i>
                                </button>
                                <div class="lesson-navigation">
                                    <button class="control-btn prev-lesson-btn" id="prev-lesson-audio">
                                        <i class="fas fa-step-backward"></i>
                                    </button>
                                    <button class="control-btn next-lesson-btn" id="next-lesson-audio">
                                        <i class="fas fa-step-forward"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                content = `
                    <div class="no-content">
                        <i class="fas fa-volume-up fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun fichier audio disponible</p>
                    </div>
                `;
            }
            break;
        case 'pdf':
            if (lessonContent) {
                content = `
                    <div class="modern-pdf-viewer">
                        <div class="pdf-viewer-header">
                            <div class="pdf-controls">
                                <button class="control-btn" id="pdf-zoom-out">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <span class="zoom-level">100%</span>
                                <button class="control-btn" id="pdf-zoom-in">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button class="control-btn" id="pdf-fullscreen">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                            <div class="lesson-navigation">
                                <button class="control-btn prev-lesson-btn" id="prev-lesson-pdf">
                                    <i class="fas fa-arrow-left"></i> Précédent
                                </button>
                                <button class="control-btn next-lesson-btn" id="next-lesson-pdf">
                                    Suivant <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="pdf-container">
                            <iframe src="${lessonContent}" class="pdf-iframe" frameborder="0"></iframe>
                        </div>
                    </div>
                `;
            } else {
                content = `
                    <div class="no-content">
                        <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun fichier PDF disponible</p>
                    </div>
                `;
            }
            break;
        case 'quiz':
            content = `
                <div class="modern-quiz-player">
                    <div class="quiz-header">
                        <h4><i class="fas fa-question-circle me-2"></i>Quiz</h4>
                        <div class="lesson-navigation">
                            <button class="control-btn prev-lesson-btn" id="prev-lesson-quiz">
                                <i class="fas fa-arrow-left"></i> Précédent
                            </button>
                            <button class="control-btn next-lesson-btn" id="next-lesson-quiz">
                                Suivant <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="quiz-content">
                        <div class="quiz-placeholder">
                            <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Système de quiz en développement</p>
                            <button class="btn btn-primary">Commencer le quiz</button>
                        </div>
                    </div>
                </div>
            `;
            break;
        default:
            content = `
                <div class="no-content">
                    <i class="fas fa-file fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Type de contenu non supporté</p>
                </div>
            `;
    }
    
    playerWrapper.innerHTML = content;
    
    // Réinitialiser les lecteurs
    setTimeout(() => {
        initializePlayers();
    }, 100);
    
    // Mettre à jour tous les boutons de navigation (web et mobile)
    setTimeout(() => {
        updateNavigationButtons(parseInt(lessonId));
    }, 100);
    
    console.log('=== FIN CHARGEMENT LECON ===');
    console.log('Leçon chargée:', lessonId);
    console.log('Liste finale des leçons:', allLessons.map((l, i) => ({ index: i, id: l.id, title: l.title })));
    
    // Activer l'onglet "Contenu" sur mobile
    if (window.innerWidth < 992) {
        // Désactiver tous les boutons mobiles
        document.querySelectorAll('.mobile-tab-btn').forEach(btn => btn.classList.remove('active'));
        
        // Désactiver tout le contenu mobile
        document.querySelectorAll('.mobile-tab-content').forEach(content => content.classList.remove('active'));
        
        // Activer le bouton Contenu
        const contentBtn = document.querySelector('.mobile-tab-btn[data-tab="content"]');
        if (contentBtn) {
            contentBtn.classList.add('active');
        }
        
        // Activer le contenu Contenu
        const contentDiv = document.getElementById('mobile-content');
        if (contentDiv) {
            contentDiv.classList.add('active');
        }
        
        playerWrapper.scrollIntoView({ behavior: 'smooth' });
    }
}

// Fonction pour mettre à jour les boutons de navigation
function updateNavigationButtons(currentLessonId) {
    console.log('=== FONCTION updateNavigationButtons APPELÉE ===');
    console.log('currentLessonId reçu:', currentLessonId);
    console.log('allLessons.length:', allLessons ? allLessons.length : 'undefined');
    
    // Utiliser la liste des leçons déjà initialisée
    if (!allLessons || allLessons.length === 0) {
        console.warn('Liste des leçons non initialisée, réinitialisation...');
        initializeLessonsList();
    }
    
    // Trouver l'index de la leçon actuelle
    const currentIndex = allLessons.findIndex(lesson => lesson.id === currentLessonId);
    
    console.log('=== DEBUG NAVIGATION ===');
    console.log('Total leçons:', allLessons.length);
    console.log('Leçon actuelle ID:', currentLessonId);
    console.log('Index actuel:', currentIndex);
    console.log('Toutes les leçons:', allLessons.map((l, index) => ({ 
        index: index, 
        id: l.id, 
        title: l.title, 
        globalIndex: l.globalIndex 
    })));
    
    if (currentIndex === -1) {
        console.error('Leçon actuelle non trouvée:', currentLessonId);
        console.log('Leçons disponibles:', allLessons.map(l => ({ id: l.id, title: l.title })));
        return;
    }
    
    const previousLesson = currentIndex > 0 ? allLessons[currentIndex - 1] : null;
    const nextLesson = currentIndex < allLessons.length - 1 ? allLessons[currentIndex + 1] : null;
    
    console.log('=== RÉSULTAT NAVIGATION ===');
    console.log('Leçon actuelle:', currentLessonId);
    console.log('Leçon précédente:', previousLesson ? { id: previousLesson.id, title: previousLesson.title, globalIndex: previousLesson.globalIndex } : 'Aucune');
    console.log('Leçon suivante:', nextLesson ? { id: nextLesson.id, title: nextLesson.title, globalIndex: nextLesson.globalIndex } : 'Aucune');
    console.log('Total leçons:', allLessons.length);
    
    // Mettre à jour tous les boutons Précédent - Approche simplifiée
    const prevButtonIds = ['prev-lesson-main', 'mobile-prev-btn', 'mobile-prev-lesson', 'prev-lesson', 'prev-lesson-text', 'prev-lesson-quiz', 'prev-lesson-pdf', 'prev-lesson-audio'];
    
    prevButtonIds.forEach(buttonId => {
        const button = document.getElementById(buttonId);
        if (button) {
            console.log(`Configuration bouton ${buttonId}:`, previousLesson ? 'ACTIVÉ' : 'DÉSACTIVÉ');
            
            // Supprimer tous les anciens gestionnaires d'événements
            button.onclick = null;
            button.removeEventListener('click', button._prevLessonHandler);
            
            if (previousLesson) {
                button.disabled = false;
                button.classList.remove('btn-outline-secondary');
                button.classList.add('btn-outline-primary');
                
                // Créer un nouveau gestionnaire d'événement
                button._prevLessonHandler = function() {
                    console.log(`CLIC BOUTON ${buttonId} - Navigation vers:`, previousLesson.id);
                    loadLesson(previousLesson.id);
                };
                
                // Assigner le gestionnaire
                button.onclick = button._prevLessonHandler;
                
            } else {
                button.disabled = true;
                button.classList.remove('btn-outline-primary');
                button.classList.add('btn-outline-secondary');
            }
        } else {
            console.log(`Bouton ${buttonId} non trouvé`);
        }
    });
    
    // Mettre à jour tous les boutons Suivant - Approche simplifiée
    const nextButtonIds = ['next-lesson-main', 'mobile-next-btn', 'mobile-next-lesson', 'next-lesson', 'next-lesson-text', 'next-lesson-quiz', 'next-lesson-pdf', 'next-lesson-audio'];
    
    nextButtonIds.forEach(buttonId => {
        const button = document.getElementById(buttonId);
        if (button) {
            console.log(`Configuration bouton ${buttonId}:`, nextLesson ? 'ACTIVÉ' : 'DÉSACTIVÉ');
            
            // Supprimer tous les anciens gestionnaires d'événements
            button.onclick = null;
            button.removeEventListener('click', button._nextLessonHandler);
            
            if (nextLesson) {
                button.disabled = false;
                button.classList.remove('btn-outline-secondary');
                button.classList.add('btn-primary');
                
                // Créer un nouveau gestionnaire d'événement
                button._nextLessonHandler = function() {
                    console.log(`CLIC BOUTON ${buttonId} - Navigation vers:`, nextLesson.id);
                    loadLesson(nextLesson.id);
                };
                
                // Assigner le gestionnaire
                button.onclick = button._nextLessonHandler;
                
                // Test immédiat pour vérifier l'assignation
                console.log(`Bouton ${buttonId} onclick assigné:`, typeof button.onclick);
                console.log(`Bouton ${buttonId} disabled:`, button.disabled);
                
                // Ajouter aussi un addEventListener pour être sûr
                button.addEventListener('click', function(e) {
                    console.log(`EVENT LISTENER CLIC - ${buttonId}`);
                    e.preventDefault();
                    e.stopPropagation();
                    console.log(`Navigation via addEventListener vers:`, nextLesson.id);
                    loadLesson(nextLesson.id);
                });
                
            } else {
                button.disabled = true;
                button.classList.remove('btn-primary');
                button.classList.add('btn-outline-secondary');
            }
        } else {
            console.log(`Bouton ${buttonId} non trouvé`);
        }
    });
}

// Fonction de test pour vérifier la navigation
function testNavigation() {
    console.log('=== TEST NAVIGATION ===');
    console.log('currentLessonId:', currentLessonId);
    console.log('allLessons.length:', allLessons ? allLessons.length : 'undefined');
    
    if (allLessons && allLessons.length > 0) {
        const currentIndex = allLessons.findIndex(lesson => lesson.id == currentLessonId);
        console.log('Index actuel:', currentIndex);
        
        if (currentIndex >= 0) {
            const nextLesson = currentIndex < allLessons.length - 1 ? allLessons[currentIndex + 1] : null;
            console.log('Leçon suivante:', nextLesson ? nextLesson.id : 'Aucune');
            
            if (nextLesson) {
                console.log('Test: Navigation vers leçon suivante...');
                loadLesson(nextLesson.id);
            }
        }
    }
}

// Fonction de test pour vérifier les boutons
function testButtons() {
    console.log('=== TEST BOUTONS ===');
    const nextButtonIds = ['next-lesson-main', 'mobile-next-btn', 'mobile-next-lesson', 'next-lesson', 'next-lesson-text', 'next-lesson-quiz', 'next-lesson-pdf', 'next-lesson-audio'];
    
    nextButtonIds.forEach(buttonId => {
        const button = document.getElementById(buttonId);
        if (button) {
            console.log(`Bouton ${buttonId}:`);
            console.log(`  - Existe: OUI`);
            console.log(`  - Disabled: ${button.disabled}`);
            console.log(`  - onclick: ${typeof button.onclick}`);
            console.log(`  - Visible: ${button.offsetParent !== null}`);
            console.log(`  - Classes: ${button.className}`);
            
            // Test de clic programmatique
            if (!button.disabled) {
                console.log(`  - Test clic programmatique...`);
                button.click();
            }
        } else {
            console.log(`Bouton ${buttonId}: NON TROUVÉ`);
        }
    });
}

// Fonction pour forcer la navigation suivante
function forceNext() {
    console.log('=== FORCE NEXT ===');
    if (allLessons && allLessons.length > 0) {
        const currentIndex = allLessons.findIndex(lesson => lesson.id == currentLessonId);
        if (currentIndex >= 0 && currentIndex < allLessons.length - 1) {
            const nextLesson = allLessons[currentIndex + 1];
            console.log('Force navigation vers:', nextLesson.id);
            loadLesson(nextLesson.id);
        } else {
            console.log('Aucune leçon suivante disponible');
        }
    }
}

// Exposer les fonctions de test globalement
window.testNavigation = testNavigation;
window.testButtons = testButtons;
window.forceNext = forceNext;
</script>
@endsection