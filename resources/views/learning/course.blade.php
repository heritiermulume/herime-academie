@extends('layouts.app')

@section('title', 'Apprendre - ' . $course->title . ' - Herime Academie')
@section('description', 'Suivez le cours ' . $course->title . ' sur Herime Academie')

@push('styles')
<style>
.learning-page {
    background: #f8f9fa;
    min-height: 100vh;
}

/* Sidebar Styles */
.learning-sidebar {
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    background: white;
    border-right: 1px solid var(--border-color);
    padding: 1rem;
}

.learning-sidebar::-webkit-scrollbar {
    width: 6px;
}

.learning-sidebar::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.learning-sidebar::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 3px;
}

.course-header {
    border-bottom: 2px solid var(--accent-color);
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}

.progress-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.section-header {
    cursor: pointer;
    padding: 0.75rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    margin-bottom: 0.5rem;
}

.section-header:hover {
    background: #f8f9fa;
}

.section-header.active {
    background: var(--primary-color);
    color: white;
}

.section-header.active .section-title,
.section-header.active .text-muted {
    color: white !important;
}

.section-content {
    display: block;
    transition: all 0.3s ease;
}

.section-content.collapsed {
    display: none;
}

.lesson-item {
    padding: 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 0.5rem;
    border: 1px solid transparent;
}

.lesson-item:hover {
    background: #f8f9fa;
    border-color: var(--border-color);
}

.lesson-item.active {
    background: #e3f2fd;
    border-color: var(--primary-color);
}

.lesson-item.completed .lesson-title {
    color: #28a745;
}

.lesson-item.completed .lesson-icon i {
    color: #28a745;
}

.lesson-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Main Content Styles */
.main-content {
    background: #f8f9fa;
    min-height: 100vh;
}

.player-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin: 1.5rem;
    overflow: hidden;
}

.player-wrapper {
    position: relative;
    width: 100%;
    background: #000;
}

.ratio-16x9 {
    aspect-ratio: 16 / 9;
}

.lesson-info {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.lesson-actions {
    padding: 1.5rem;
}

/* Mobile Header */
.mobile-header {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
    margin-bottom: 1rem;
}

.mobile-course-header {
    padding: 1rem;
}

.mobile-tabs {
    border-bottom: 2px solid var(--border-color);
    padding: 0 1rem;
}

.mobile-tab {
    padding: 0.75rem 1.5rem;
    border: none;
    background: none;
    color: var(--text-muted);
    font-weight: 500;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.mobile-tab.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
}

.mobile-tab-content {
    width: 100%;
}

/* Mobile Sidebar */
.mobile-sidebar {
    background: white;
    border-top: 1px solid var(--border-color);
    max-height: calc(100vh - 300px);
    overflow-y: auto;
}

.mobile-sidebar::-webkit-scrollbar {
    width: 6px;
}

.mobile-sidebar::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.mobile-sidebar::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 3px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 4rem;
    color: var(--border-color);
    margin-bottom: 1rem;
}

/* Mobile Styles */
@media (max-width: 991px) {
    .learning-page {
        padding: 0;
    }
    
    .learning-page .container-fluid {
        padding: 0 !important;
    }
    
    .player-container {
        margin: 0;
        border-radius: 0;
        box-shadow: none;
    }
    
    .player-wrapper {
        border-radius: 0;
    }
    
    .lesson-info {
        padding: 1rem !important;
    }
    
    .lesson-info h3 {
        font-size: 1.25rem;
    }
    
    .lesson-actions {
        padding: 1rem;
    }
    
    .lesson-actions .btn {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    
    .lesson-actions .d-flex {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .lesson-actions .btn,
    .lesson-actions .badge {
        flex: 1;
        min-width: auto;
    }
    
    .mobile-tabs {
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .mobile-tab {
        white-space: nowrap;
        font-size: 0.875rem;
    }
    
    .mobile-sidebar {
        max-height: 70vh !important;
    }
    
    .empty-state {
        padding: 2rem 1rem;
    }
    
    .empty-state i {
        font-size: 3rem;
    }
    
    .empty-state h4 {
        font-size: 1.25rem;
    }
    
    .empty-state .btn {
        width: 100%;
        margin-top: 1rem;
    }
    
    /* PDF Viewer adjustments */
    .pdf-viewer-container {
        height: 100%;
    }
    
    .pdf-toolbar {
        padding: 0.75rem !important;
    }
    
    .pdf-toolbar h5 {
        font-size: 1rem;
    }
    
    .pdf-toolbar .btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .pdf-content {
        height: calc(100vh - 200px) !important;
    }
    
    /* Text Viewer adjustments */
    .text-viewer-container {
        height: 100%;
        overflow: auto;
    }
    
    .text-viewer-container .text-content {
        padding: 1rem !important;
        max-height: calc(100vh - 200px) !important;
    }
    
    .text-viewer-header h2 {
        font-size: 1.5rem;
    }
    
    /* Quiz Viewer adjustments */
    .quiz-viewer-container {
        height: 100%;
        overflow: auto;
    }
    
    .quiz-content {
        padding: 1rem !important;
    }
    
    .quiz-header h2 {
        font-size: 1.5rem;
    }
    
    .quiz-question {
        padding: 1rem !important;
    }
    
    .quiz-actions .btn {
        width: 100%;
    }
    
    /* Plyr player adjustments */
    .plyr-player-container {
        min-height: 250px !important;
    }
}
</style>
@endpush

@section('content')
<div class="learning-page">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Desktop Sidebar -->
            <div class="col-lg-3 sidebar-container d-none d-lg-block">
                <div class="learning-sidebar">
                    <!-- Course Header -->
                    <div class="course-header">
                        <div class="d-flex align-items-center mb-3">
                            <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-sm" style="background-color: var(--primary-color); color: white; margin-right: 1rem;">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <h6 class="mb-0 fw-bold">{{ Str::limit($course->title, 40) }}</h6>
                        </div>
                    </div>

                    <!-- Progress Section -->
                    <div class="progress-section">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold">Progression</span>
                            <span class="fw-bold" style="color: var(--primary-color);">{{ $progress['overall_progress'] ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $progress['overall_progress'] ?? 0 }}%; background: linear-gradient(90deg, var(--primary-color) 0%, var(--accent-color) 100%);"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">{{ $progress['completed_lessons'] ?? 0 }} / {{ $progress['total_lessons'] ?? 0 }} leçons terminées</small>
                    </div>

                    <!-- Course Content -->
                    <div class="course-content">
                        <h6 class="fw-bold mb-3">Contenu du cours</h6>
                        <div class="sections-list">
                            @foreach($course->sections as $section)
                            <div class="section-item">
                                <div class="section-header" onclick="toggleSection({{ $section->id }})">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 section-title">{{ $section->title }}</h6>
                                            <small class="text-muted">{{ $section->lessons ? $section->lessons->count() : 0 }} leçons</small>
                                        </div>
                                        <i class="fas fa-chevron-down section-toggle-{{ $section->id }}"></i>
                                    </div>
                                </div>
                                
                                <div class="section-content" id="section-{{ $section->id }}">
                                    <div class="lessons-list ps-3">
                                        @foreach($section->lessons ?? [] as $sectionLesson)
                                        @php
                                            $isActive = isset($activeLesson) && $sectionLesson->id === $activeLesson->id;
                                            $isCompleted = isset($progress['completed_lessons_ids']) ? $progress['completed_lessons_ids']->contains($sectionLesson->id) : false;
                                        @endphp
                                        <div class="lesson-item {{ $isCompleted ? 'completed' : '' }} {{ $isActive ? 'active' : '' }}"
                                             data-lesson-id="{{ $sectionLesson->id }}"
                                             onclick="loadLesson({{ $sectionLesson->id }})">
                                            <div class="d-flex align-items-center">
                                                <div class="lesson-icon me-3">
                                                    @if($sectionLesson->type === 'video')
                                                        <i class="fas fa-play-circle"></i>
                                                    @elseif($sectionLesson->type === 'text')
                                                        <i class="fas fa-file-alt"></i>
                                                    @elseif($sectionLesson->type === 'pdf')
                                                        <i class="fas fa-file-pdf"></i>
                                                    @elseif($sectionLesson->type === 'quiz')
                                                        <i class="fas fa-question-circle"></i>
                                                    @else
                                                        <i class="fas fa-file"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0 lesson-title">{{ $sectionLesson->title }}</h6>
                                                    <small class="text-muted">{{ $sectionLesson->duration ?? 0 }} min</small>
                                                </div>
                                                @if($isCompleted)
                                                    <i class="fas fa-check-circle text-success"></i>
                                                @endif
                                            </div>
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

            <!-- Main Content -->
            <div class="col-lg-9 col-12 main-content">
                <!-- Mobile Header -->
                <div class="mobile-header d-block d-lg-none">
                    <div class="mobile-course-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold">{{ Str::limit($course->title, 30) }}</h6>
                            <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-sm" style="background-color: var(--primary-color); color: white;">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="mobile-tabs d-flex">
                        <button class="mobile-tab active" onclick="showMobileTab('content')" id="tab-content">
                            Contenu
                        </button>
                        <button class="mobile-tab" onclick="showMobileTab('sidebar')" id="tab-sidebar">
                            Cours
                        </button>
                    </div>
                </div>

                <!-- Content Tab -->
                <div id="mobile-content-tab" class="mobile-tab-content">
                    <div class="player-container">
                        @if(isset($activeLesson))
                            <!-- Lesson Player -->
                            <div class="lesson-info">
                                <h3 class="mb-2">{{ $activeLesson->title }}</h3>
                                @if($activeLesson->description)
                                    <p class="text-muted mb-0">{{ $activeLesson->description }}</p>
                                @endif
                            </div>
                            
                            <div class="player-wrapper">
                                <div class="ratio ratio-16x9">
                                    @switch($activeLesson->type)
                                        @case('video')
                                            @if($activeLesson->isYoutubeVideo())
                                                <x-plyr-player :lesson="$activeLesson" :course="$course" :isMobile="false" />
                                            @else
                                                <div class="d-flex align-items-center justify-content-center bg-dark" style="height: 100%;">
                                                    <p class="text-white">Format de vidéo non supporté</p>
                                                </div>
                                            @endif
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
                                            <div class="d-flex align-items-center justify-content-center bg-dark" style="height: 100%;">
                                                <p class="text-white">Type de leçon non supporté</p>
                                            </div>
                                    @endswitch
                                </div>
                            </div>

                            <!-- Lesson Actions -->
                            <div class="lesson-actions">
                                <div class="d-flex justify-content-between align-items-center">
                                    @if(isset($previousLesson))
                                        <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $previousLesson->id]) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-arrow-left me-2"></i>Précédent
                                        </a>
                                    @else
                                        <span></span>
                                    @endif
                                    
                                    @if(!isset($progress['completed_lessons_ids']) || !$progress['completed_lessons_ids']->contains($activeLesson->id))
                                        <button class="btn btn-primary" onclick="markAsComplete({{ $activeLesson->id }})">
                                            <i class="fas fa-check me-2"></i>Marquer comme terminé
                                        </button>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Terminé
                                        </span>
                                    @endif
                                    
                                    @if(isset($nextLesson))
                                        <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $nextLesson->id]) }}" class="btn btn-primary">
                                            Suivant<i class="fas fa-arrow-right ms-2"></i>
                                        </a>
                                    @else
                                        <span></span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- Empty State -->
                            <div class="empty-state">
                                <i class="fas fa-graduation-cap"></i>
                                <h4 class="mb-3">Bienvenue dans votre cours</h4>
                                <p class="text-muted">Sélectionnez une leçon pour commencer</p>
                                @if($course->sections->first() && $course->sections->first()->lessons->first())
                                    <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $course->sections->first()->lessons->first()->id]) }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-play me-2"></i>Commencer la première leçon
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar Tab -->
                <div id="mobile-sidebar-tab" class="mobile-tab-content d-none">
                    <div class="mobile-sidebar p-3">
                        <!-- Progress Section -->
                        <div class="progress-section mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold">Progression</span>
                                <span class="fw-bold" style="color: var(--primary-color);">{{ $progress['overall_progress'] ?? 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $progress['overall_progress'] ?? 0 }}%; background: linear-gradient(90deg, var(--primary-color) 0%, var(--accent-color) 100%);"></div>
                            </div>
                            <small class="text-muted mt-2 d-block">{{ $progress['completed_lessons'] ?? 0 }} / {{ $progress['total_lessons'] ?? 0 }} leçons terminées</small>
                        </div>

                        <!-- Course Content -->
                        <div class="course-content">
                            <h6 class="fw-bold mb-3">Contenu du cours</h6>
                            <div class="sections-list">
                                @foreach($course->sections as $section)
                                <div class="section-item">
                                    <div class="section-header" onclick="toggleMobileSection({{ $section->id }})">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1 section-title">{{ $section->title }}</h6>
                                                <small class="text-muted">{{ $section->lessons ? $section->lessons->count() : 0 }} leçons</small>
                                            </div>
                                            <i class="fas fa-chevron-down mobile-section-toggle-{{ $section->id }}"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="section-content" id="section-mobile-{{ $section->id }}">
                                        <div class="lessons-list ps-3">
                                            @foreach($section->lessons ?? [] as $sectionLesson)
                                            @php
                                                $isActive = isset($activeLesson) && $sectionLesson->id === $activeLesson->id;
                                                $isCompleted = isset($progress['completed_lessons_ids']) ? $progress['completed_lessons_ids']->contains($sectionLesson->id) : false;
                                            @endphp
                                            <div class="lesson-item {{ $isCompleted ? 'completed' : '' }} {{ $isActive ? 'active' : '' }}"
                                                 data-lesson-id="{{ $sectionLesson->id }}"
                                                 onclick="loadLesson({{ $sectionLesson->id }})">
                                                <div class="d-flex align-items-center">
                                                    <div class="lesson-icon me-3">
                                                        @if($sectionLesson->type === 'video')
                                                            <i class="fas fa-play-circle"></i>
                                                        @elseif($sectionLesson->type === 'text')
                                                            <i class="fas fa-file-alt"></i>
                                                        @elseif($sectionLesson->type === 'pdf')
                                                            <i class="fas fa-file-pdf"></i>
                                                        @elseif($sectionLesson->type === 'quiz')
                                                            <i class="fas fa-question-circle"></i>
                                                        @else
                                                            <i class="fas fa-file"></i>
                                                        @endif
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0 lesson-title">{{ $sectionLesson->title }}</h6>
                                                        <small class="text-muted">{{ $sectionLesson->duration ?? 0 }} min</small>
                                                    </div>
                                                    @if($isCompleted)
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @endif
                                                </div>
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
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadLesson(lessonId) {
    // Charger la leçon via AJAX ou redirection
    window.location.href = `/learning/courses/{{ $course->slug }}/lessons/${lessonId}`;
}

function showMobileTab(tab) {
    if (tab === 'content') {
        document.getElementById('mobile-content-tab').classList.remove('d-none');
        document.getElementById('mobile-sidebar-tab').classList.add('d-none');
        document.getElementById('tab-content').classList.add('active');
        document.getElementById('tab-sidebar').classList.remove('active');
    } else {
        document.getElementById('mobile-content-tab').classList.add('d-none');
        document.getElementById('mobile-sidebar-tab').classList.remove('d-none');
        document.getElementById('tab-content').classList.remove('active');
        document.getElementById('tab-sidebar').classList.add('active');
    }
}

function toggleSection(sectionId) {
    const section = document.getElementById(`section-${sectionId}`);
    if (section) {
        section.classList.toggle('collapsed');
        const toggle = document.querySelector(`.section-toggle-${sectionId}`);
        if (toggle) {
            if (section.classList.contains('collapsed')) {
                toggle.style.transform = 'rotate(-90deg)';
            } else {
                toggle.style.transform = 'rotate(0deg)';
            }
        }
    }
}

function toggleMobileSection(sectionId) {
    const section = document.getElementById(`section-mobile-${sectionId}`);
    if (section) {
        section.classList.toggle('collapsed');
        const toggle = document.querySelector(`.mobile-section-toggle-${sectionId}`);
        if (toggle) {
            if (section.classList.contains('collapsed')) {
                toggle.style.transform = 'rotate(-90deg)';
            } else {
                toggle.style.transform = 'rotate(0deg)';
            }
        }
    }
}

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
            // Mettre à jour l'UI
            const lessonItem = document.querySelector(`[data-lesson-id="${lessonId}"]`);
            if (lessonItem) {
                lessonItem.classList.add('completed');
            }
            // Reload pour mettre à jour la progression
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de la mise à jour');
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('Learning page loaded');
});
</script>
@endpush

