@extends('layouts.app')

@section('title', 'Apprendre - ' . $course->title . ' - Herime Academie')

@section('content')
<div class="container-fluid py-0">
    <!-- Top Bar with Back to Dashboard -->
    <div class="bg-white border-bottom px-3 py-2 d-flex align-items-center">
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm" title="Tableau de bord">
            <i class="fas fa-tachometer-alt"></i>
        </a>
        <span class="ms-3 text-muted small">Retour au tableau de bord</span>
    </div>
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4">
            <div class="course-sidebar bg-light">
                <!-- Course Header -->
                <div class="p-3 border-bottom">
                    <h5 class="fw-bold mb-2">{{ $course->title }}</h5>
                    <div class="d-flex align-items-center">
                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ $enrollment->progress }}%" 
                                 aria-valuenow="{{ $enrollment->progress }}" 
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">{{ $enrollment->progress }}%</small>
                    </div>
                </div>

                <!-- Course Sections -->
                <div class="course-sections">
                    @foreach($course->sections as $section)
                    <div class="section-item">
                        <div class="section-header p-3 border-bottom" data-bs-toggle="collapse" data-bs-target="#section{{ $section->id }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">{{ $section->title }}</h6>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            @if($section->description)
                            <small class="text-muted">{{ $section->description }}</small>
                            @endif
                        </div>
                        <div class="collapse" id="section{{ $section->id }}">
                            <div class="lessons-list">
                                @foreach($section->lessons as $lesson)
                                <div class="lesson-item p-3 border-bottom {{ in_array($lesson->id, $enrollment->completed_lessons ?? []) ? 'completed' : '' }} {{ $lesson->is_preview ? 'preview' : '' }}">
                                    <div class="d-flex align-items-center">
                                        <div class="lesson-icon me-3">
                                            @if(in_array($lesson->id, $enrollment->completed_lessons ?? []))
                                                <i class="fas fa-check-circle text-success"></i>
                                            @else
                                                @switch($lesson->type)
                                                    @case('video')
                                                        <i class="fas fa-play-circle text-primary"></i>
                                                        @break
                                                    @case('text')
                                                        <i class="fas fa-file-text text-info"></i>
                                                        @break
                                                    @case('pdf')
                                                        <i class="fas fa-file-pdf text-danger"></i>
                                                        @break
                                                    @case('quiz')
                                                        <i class="fas fa-question-circle text-warning"></i>
                                                        @break
                                                    @case('assignment')
                                                        <i class="fas fa-tasks text-success"></i>
                                                        @break
                                                @endswitch
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold">{{ $lesson->title }}</h6>
                                            <small class="text-muted">{{ $lesson->duration }} min</small>
                                            @if($lesson->is_preview)
                                            <span class="badge bg-success ms-2">Aperçu</span>
                                            @endif
                                        </div>
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

        <!-- Main Content -->
        <div class="col-lg-9 col-md-8">
            <div class="learning-content">
                <!-- Video Player -->
                <div class="video-container bg-dark">
                    <div class="ratio ratio-16x9">
                        <video id="lessonVideo" controls class="w-100 h-100" style="background: #000;">
                            <source src="" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture vidéo.
                        </video>
                    </div>
                </div>

                <!-- Lesson Content -->
                <div class="lesson-content p-4">
                    <div class="row">
                        <div class="col-lg-8">
                            <h2 class="fw-bold mb-3" id="lessonTitle">Sélectionnez une leçon pour commencer</h2>
                            <div id="lessonDescription" class="text-muted mb-4">
                                Choisissez une leçon dans la sidebar pour commencer votre apprentissage.
                            </div>
                            
                            <!-- Lesson Actions -->
                            <div class="lesson-actions mb-4" id="lessonActions" style="display: none;">
                                <button class="btn btn-primary" id="markCompleteBtn" onclick="markLessonComplete()">
                                    <i class="fas fa-check me-2"></i>Marquer comme terminé
                                </button>
                                <button class="btn btn-outline-secondary" id="prevLessonBtn" onclick="previousLesson()">
                                    <i class="fas fa-arrow-left me-2"></i>Leçon précédente
                                </button>
                                <button class="btn btn-outline-primary" id="nextLessonBtn" onclick="nextLesson()">
                                    Leçon suivante <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>

                            <!-- Lesson Resources -->
                            <div class="lesson-resources" id="lessonResources" style="display: none;">
                                <h5 class="fw-bold mb-3">Ressources de la leçon</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body">
                                                <h6 class="fw-bold">Téléchargements</h6>
                                                <p class="text-muted small">Fichiers complémentaires</p>
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-download me-1"></i>Télécharger
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body">
                                                <h6 class="fw-bold">Quiz</h6>
                                                <p class="text-muted small">Testez vos connaissances</p>
                                                <button class="btn btn-outline-warning btn-sm">
                                                    <i class="fas fa-question-circle me-1"></i>Commencer
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Course Info -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-primary text-white py-3">
                                    <h5 class="mb-0 fw-bold">Informations du cours</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6 class="fw-bold">Formateur</h6>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $course->instructor->avatar ? $course->instructor->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($course->instructor->name) . '&background=003366&color=fff' }}" 
                                                 alt="{{ $course->instructor->name }}" class="rounded-circle me-2" width="30" height="30">
                                            <span>{{ $course->instructor->name }}</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="fw-bold">Progression</h6>
                                        <div class="progress mb-2" style="height: 8px;">
                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                 style="width: {{ $enrollment->progress }}%" 
                                                 aria-valuenow="{{ $enrollment->progress }}" 
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small class="text-muted">{{ $enrollment->progress }}% terminé</small>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="fw-bold">Temps restant</h6>
                                        <small class="text-muted" id="timeRemaining">Calcul en cours...</small>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="fw-bold">Leçons terminées</h6>
                                        <small class="text-muted">
                                            {{ count($enrollment->completed_lessons ?? []) }} / {{ $course->lessons_count }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Notes -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light py-3">
                                    <h5 class="mb-0 fw-bold">Mes notes</h5>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" rows="4" placeholder="Prenez des notes sur cette leçon..."></textarea>
                                    <button class="btn btn-primary btn-sm mt-2">
                                        <i class="fas fa-save me-1"></i>Sauvegarder
                                    </button>
                                </div>
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
let currentLesson = null;
let courseData = @json($course);
let enrollmentData = @json($enrollment);

// Initialize course data
function initializeCourse() {
    // Set up lesson click handlers
    document.querySelectorAll('.lesson-item').forEach(item => {
        item.addEventListener('click', function() {
            const lessonId = this.dataset.lessonId;
            if (lessonId) {
                loadLesson(lessonId);
            }
        });
    });
    
    // Calculate time remaining
    calculateTimeRemaining();
}

// Load lesson content
function loadLesson(lessonId) {
    // Find lesson in course data
    let lesson = null;
    courseData.sections.forEach(section => {
        section.lessons.forEach(l => {
            if (l.id == lessonId) {
                lesson = l;
            }
        });
    });
    
    if (!lesson) return;
    
    currentLesson = lesson;
    
    // Update UI
    document.getElementById('lessonTitle').textContent = lesson.title;
    document.getElementById('lessonDescription').textContent = lesson.description || 'Aucune description disponible.';
    
    // Show lesson actions
    document.getElementById('lessonActions').style.display = 'block';
    document.getElementById('lessonResources').style.display = 'block';
    
    // Load video if it's a video lesson
    if (lesson.type === 'video' && lesson.content_url) {
        const video = document.getElementById('lessonVideo');
        video.src = `/video/${lesson.id}/stream`;
        video.load();
    }
    
    // Update active lesson in sidebar
    document.querySelectorAll('.lesson-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-lesson-id="${lessonId}"]`).classList.add('active');
    
    // Update navigation buttons
    updateNavigationButtons();
}

// Mark lesson as complete
function markLessonComplete() {
    if (!currentLesson) return;
    
    fetch(`/student/courses/${courseData.id}/lessons/${currentLesson.id}/complete`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const lessonItem = document.querySelector(`[data-lesson-id="${currentLesson.id}"]`);
            lessonItem.classList.add('completed');
            lessonItem.querySelector('.lesson-icon i').className = 'fas fa-check-circle text-success';
            
            // Update progress
            updateProgress();
            
            // Show success message
            showNotification('Leçon marquée comme terminée !', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur lors de la mise à jour', 'error');
    });
}

// Update progress
function updateProgress() {
    // This would typically be calculated on the server
    // For now, we'll just update the display
    const progress = Math.round((enrollmentData.completed_lessons.length / courseData.lessons_count) * 100);
    document.querySelectorAll('.progress-bar').forEach(bar => {
        bar.style.width = progress + '%';
        bar.setAttribute('aria-valuenow', progress);
    });
    document.querySelectorAll('.progress-text').forEach(text => {
        text.textContent = progress + '%';
    });
}

// Calculate time remaining
function calculateTimeRemaining() {
    const totalDuration = courseData.duration;
    const completedLessons = enrollmentData.completed_lessons.length;
    const totalLessons = courseData.lessons_count;
    
    const remainingLessons = totalLessons - completedLessons;
    const avgLessonDuration = totalDuration / totalLessons;
    const remainingTime = Math.round(remainingLessons * avgLessonDuration);
    
    const hours = Math.floor(remainingTime / 60);
    const minutes = remainingTime % 60;
    
    let timeText = '';
    if (hours > 0) {
        timeText = `${hours}h ${minutes}min`;
    } else {
        timeText = `${minutes}min`;
    }
    
    document.getElementById('timeRemaining').textContent = timeText;
}

// Update navigation buttons
function updateNavigationButtons() {
    // This would need to be implemented based on the current lesson
    // For now, we'll just show/hide buttons
    document.getElementById('prevLessonBtn').style.display = 'inline-block';
    document.getElementById('nextLessonBtn').style.display = 'inline-block';
}

// Show notification
function showNotification(message, type) {
    // Simple notification - you might want to use a more sophisticated system
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initializeCourse);
</script>
@endpush

@push('styles')
<style>
.course-sidebar {
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

.section-header {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.section-header:hover {
    background-color: #f8f9fa;
}

.lesson-item {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.lesson-item:hover {
    background-color: #f8f9fa;
}

.lesson-item.active {
    background-color: #e3f2fd;
    border-left: 4px solid #003366;
}

.lesson-item.completed {
    background-color: #f8fff8;
}

.lesson-item.preview {
    background-color: #fff8e1;
}

.video-container {
    position: relative;
}

.lesson-content {
    background-color: #fff;
}

.btn-primary {
    background-color: #003366;
    border-color: #003366;
}

.btn-primary:hover {
    background-color: #004080;
    border-color: #004080;
}

.btn-outline-primary {
    color: #003366;
    border-color: #003366;
}

.btn-outline-primary:hover {
    background-color: #003366;
    border-color: #003366;
}

.progress {
    border-radius: 4px;
}

.progress-bar {
    border-radius: 4px;
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
</style>
@endpush
