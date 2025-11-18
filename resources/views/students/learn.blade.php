@extends('layouts.app')

@section('title', 'Apprendre - ' . $course->title . ' - Herime Academie')

@section('content')
<div class="container-fluid py-0">
    <!-- Top Bar with Back to Dashboard -->
    <div class="text-white px-3 py-2 d-flex align-items-center justify-content-between justify-content-sm-start top-bar-return rounded-3 mb-2" style="background-color:#003366;">
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-light btn-sm" title="Tableau de bord">
            <i class="fas fa-tachometer-alt"></i>
        </a>
        <span class="ms-3 small text-white-50 flex-grow-1 flex-sm-grow-0">Retour au tableau de bord</span>
    </div>
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4 order-2 order-lg-1">
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
                                <div class="lesson-item p-3 border-bottom {{ in_array($lesson->id, $enrollment->completed_lessons ?? []) ? 'completed' : '' }} {{ $lesson->is_preview ? 'preview' : '' }}" data-lesson-id="{{ $lesson->id }}">
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

                <div class="d-block d-lg-none mt-4">
                    @include('students.partials.course-info-section', ['course' => $course, 'enrollment' => $enrollment])
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9 col-md-8 order-1 order-lg-2">
            <div class="learning-content">
                <!-- Video Player -->
                <div class="video-container bg-dark" id="videoContainer" style="margin: 0; padding: 0;">
                    <div class="ratio ratio-16x9" style="margin: 0; padding: 0; position: relative;">
                        <!-- Container pour vidéo YouTube -->
                        <div id="youtubePlayerContainer" style="display: none; width: 100%; height: 100%; position: absolute; top: 0; left: 0; margin: 0; padding: 0;"></div>
                        <!-- Container pour vidéo locale -->
                        <video id="lessonVideo" controls class="w-100 h-100" style="background: #000; display: none; position: absolute; top: 0; left: 0; margin: 0; padding: 0; object-fit: contain;" controlsList="nodownload" disablePictureInPicture>
                            <source src="" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture vidéo.
                        </video>
                        <!-- Message par défaut -->
                        <div id="noVideoMessage" class="d-flex align-items-center justify-content-center text-white" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; margin: 0; padding: 0;">
                            <div class="text-center">
                                <i class="fas fa-video fa-3x mb-3 text-muted"></i>
                                <p class="text-muted">Sélectionnez une leçon pour commencer</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Text/PDF Viewer -->
                <div id="textPdfViewerContainer" style="display: none; min-height: 600px; margin-bottom: 1rem;"></div>

                <!-- Lesson Content -->
                <div class="lesson-content p-4">
                    <div class="row">
                        <div class="col-lg-8">
                            <h2 class="lesson-title fw-bold mb-3" id="lessonTitle">Sélectionnez une leçon pour commencer</h2>
                            <div id="lessonDescription" class="lesson-description text-muted mb-4">
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
                        <div class="col-lg-4 d-none d-lg-block">
                            @include('students.partials.course-info-section', ['course' => $course, 'enrollment' => $enrollment])
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

// S'assurer que courseData a un slug
if (!courseData.slug) {
    courseData.slug = '{{ $course->slug }}';
}

// Initialize course data
function initializeCourse() {
    // Set up lesson click handlers
    document.querySelectorAll('.lesson-item').forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
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
    
    // Masquer tous les conteneurs d'abord
    const videoContainer = document.getElementById('videoContainer');
    const textPdfViewerContainer = document.getElementById('textPdfViewerContainer');
    const video = document.getElementById('lessonVideo');
    const youtubeContainer = document.getElementById('youtubePlayerContainer');
    const noVideoMessage = document.getElementById('noVideoMessage');
    
    videoContainer.style.display = 'none';
    textPdfViewerContainer.style.display = 'none';
    
    // Load content based on lesson type
    if (lesson.type === 'video') {
        // Afficher le conteneur vidéo
        videoContainer.style.display = 'block';
        
        // Masquer tous les conteneurs vidéo
        video.style.display = 'none';
        youtubeContainer.style.display = 'none';
        noVideoMessage.style.display = 'none';
        
        // Vérifier si c'est une vidéo YouTube
        if (lesson.youtube_video_id) {
            // Vidéo YouTube via youtube_video_id
            youtubeContainer.style.display = 'block';
            youtubeContainer.innerHTML = `<iframe src="https://www.youtube.com/embed/${lesson.youtube_video_id}?rel=0&modestbranding=1&iv_load_policy=3&controls=1&disablekb=1&fs=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width: 100%; height: 100%;"></iframe>`;
            
            // Initialiser le suivi de progression pour YouTube
            initializeYouTubeProgressTracking(lesson.id, lesson.youtube_video_id);
        } else if (lesson.content_url && (lesson.content_url.includes('youtube.com') || lesson.content_url.includes('youtu.be'))) {
            // URL YouTube dans content_url
            let videoId = '';
            try {
                if (lesson.content_url.includes('youtube.com/watch')) {
                    const url = new URL(lesson.content_url);
                    videoId = url.searchParams.get('v');
                } else if (lesson.content_url.includes('youtu.be/')) {
                    videoId = lesson.content_url.split('youtu.be/')[1].split('?')[0];
                }
            } catch (e) {
                console.error('Error parsing YouTube URL:', e);
            }
            
            if (videoId) {
                youtubeContainer.style.display = 'block';
                youtubeContainer.innerHTML = `<iframe src="https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1&iv_load_policy=3&controls=1&disablekb=1&fs=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width: 100%; height: 100%;"></iframe>`;
                initializeYouTubeProgressTracking(lesson.id, videoId);
            } else {
                noVideoMessage.style.display = 'flex';
            }
        } else if (lesson.content_file_url || lesson.file_url || (lesson.content_url && !lesson.content_url.includes('http'))) {
            // Vidéo locale du site (fichier uploadé)
            const videoUrl = lesson.content_file_url || lesson.file_url || lesson.content_url || `/video/${lesson.id}/stream`;
            video.style.display = 'block';
            video.src = videoUrl;
            video.load();
            
            // Initialiser le suivi de progression pour vidéo locale
            initializeVideoProgressTracking(lesson.id, video);
        } else if (lesson.content_url && lesson.content_url.includes('http') && !lesson.content_url.includes('youtube') && !lesson.content_url.includes('youtu.be')) {
            // URL externe (Vimeo, etc.) - traiter comme vidéo locale si possible
            video.style.display = 'block';
            video.src = lesson.content_url;
            video.load();
            initializeVideoProgressTracking(lesson.id, video);
        } else {
            // Aucune vidéo disponible
            noVideoMessage.style.display = 'flex';
        }
    } else if (lesson.type === 'text' || lesson.type === 'pdf') {
        // Afficher le viewer texte/PDF
        textPdfViewerContainer.style.display = 'block';
        
        // Créer un objet lesson pour le composant
        const lessonData = {
            id: lesson.id,
            title: lesson.title,
            description: lesson.description,
            type: lesson.type,
            duration: lesson.duration,
            content_text: lesson.content_text,
            content_file_url: lesson.content_file_url || lesson.file_url || lesson.content_url
        };
        
        // Charger le viewer via fetch ou créer directement le HTML
        loadTextViewer(lessonData);
    } else {
        // Autres types de leçons (quiz, assignment, etc.)
        videoContainer.style.display = 'block';
        video.style.display = 'none';
        youtubeContainer.style.display = 'none';
        noVideoMessage.style.display = 'flex';
        noVideoMessage.innerHTML = `
            <div class="text-center">
                <i class="fas fa-${lesson.type === 'quiz' ? 'question-circle' : 'tasks'} fa-3x mb-3 text-muted"></i>
                <p class="text-muted">Type de leçon: ${lesson.type}</p>
            </div>
        `;
    }
    
    // Update active lesson in sidebar
    document.querySelectorAll('.lesson-item').forEach(item => {
        item.classList.remove('active');
    });
    const selectedLessonElement = document.querySelector(`[data-lesson-id="${lessonId}"]`);
    if (selectedLessonElement) {
        selectedLessonElement.classList.add('active');

        const parentCollapse = selectedLessonElement.closest('.collapse');
        if (parentCollapse && !parentCollapse.classList.contains('show')) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                bootstrap.Collapse.getOrCreateInstance(parentCollapse, { toggle: false }).show();
            } else {
                parentCollapse.classList.add('show');
            }
        }
    }
    
    // Update navigation buttons
    updateNavigationButtons();

    // Scroll to the top (useful for mobile to reveal the player)
    if (window.matchMedia('(max-width: 991.98px)').matches) {
        const learningContent = document.querySelector('.learning-content');
        if (learningContent) {
            learningContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
}

// Mark lesson as complete
function markLessonComplete() {
    if (!currentLesson) return;
    
    fetch(`/learning/courses/${courseData.slug}/lessons/${currentLesson.id}/complete`, {
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
    
    document.querySelectorAll('.time-remaining-placeholder').forEach(element => {
        element.textContent = timeText;
    });
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

// Suivi de progression pour vidéo locale
function initializeVideoProgressTracking(lessonId, videoElement) {
    if (!videoElement) return;
    
    // Utiliser directement l'élément vidéo
    const video = videoElement;
    
    let lastSavedTime = 0;
    let isTracking = false;
    
    // Désactiver le menu contextuel et le téléchargement
    video.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });
    
    video.addEventListener('dragstart', function(e) {
        e.preventDefault();
        return false;
    });
    
    // Empêcher le glisser-déposer
    video.addEventListener('drop', function(e) {
        e.preventDefault();
        return false;
    });
    
    // Marquer la leçon comme commencée au premier play
    video.addEventListener('play', function() {
        if (!isTracking) {
            isTracking = true;
            // Marquer comme commencée
            fetch(`/learning/courses/${courseData.slug}/lessons/${lessonId}/start`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ time_watched: Math.floor(video.currentTime) })
            }).catch(err => console.error('Error starting lesson:', err));
        }
    });
    
    // Sauvegarder la progression toutes les 10 secondes
    video.addEventListener('timeupdate', function() {
        const currentTime = Math.floor(video.currentTime);
        const duration = Math.floor(video.duration);
        
        // Sauvegarder toutes les 10 secondes ou si la différence est significative
        if (currentTime - lastSavedTime >= 10 || (duration > 0 && currentTime >= duration * 0.9)) {
            lastSavedTime = currentTime;
            
            const isCompleted = duration > 0 && currentTime >= duration * 0.95; // 95% = terminé
            
            fetch(`/learning/courses/${courseData.slug}/lessons/${lessonId}/progress`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    time_watched: currentTime,
                    is_completed: isCompleted
                })
            }).catch(err => console.error('Error updating progress:', err));
        }
    });
    
    // Sauvegarder à la fin de la vidéo
    video.addEventListener('ended', function() {
        const duration = Math.floor(video.duration);
        fetch(`/learning/courses/${courseData.slug}/lessons/${lessonId}/progress`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                time_watched: duration,
                is_completed: true
            })
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                // Marquer visuellement comme terminée
                const lessonItem = document.querySelector(`[data-lesson-id="${lessonId}"]`);
                if (lessonItem) {
                    lessonItem.classList.add('completed');
                    const icon = lessonItem.querySelector('.lesson-icon i');
                    if (icon) {
                        icon.className = 'fas fa-check-circle text-success';
                    }
                }
                updateProgress();
            }
        }).catch(err => console.error('Error completing lesson:', err));
    });
}

// Suivi de progression pour vidéo YouTube (basique - nécessiterait YouTube API pour un suivi précis)
function initializeYouTubeProgressTracking(lessonId, videoId) {
    // Pour YouTube, on ne peut pas tracker précisément sans YouTube API
    // On marque juste comme commencée quand l'iframe est chargée
    setTimeout(function() {
        const iframe = document.querySelector('#youtubePlayerContainer iframe');
        if (iframe) {
            iframe.addEventListener('load', function() {
                fetch(`/learning/courses/${courseData.slug}/lessons/${lessonId}/start`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ time_watched: 0 })
                }).catch(err => console.error('Error starting lesson:', err));
            });
            
            // Si l'iframe est déjà chargée
            if (iframe.complete) {
                fetch(`/learning/courses/${courseData.slug}/lessons/${lessonId}/start`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ time_watched: 0 })
                }).catch(err => console.error('Error starting lesson:', err));
            }
        }
    }, 100);
}

// Charger le viewer texte/PDF
function loadTextViewer(lesson) {
    const container = document.getElementById('textPdfViewerContainer');
    if (!container) return;
    
    const viewerId = 'text-viewer-' + lesson.id;
    const isPdf = lesson.type === 'pdf';
    
    // Créer le HTML du viewer
    let html = `
        <div class="text-viewer-container" id="${viewerId}">
            <div class="text-viewer-toolbar d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
                <div class="d-flex align-items-center gap-3">
                    ${lesson.duration ? `<span class="badge bg-primary"><i class="fas fa-clock"></i> ${lesson.duration} min</span>` : ''}
                    <span class="badge bg-secondary">
                        <i class="fas fa-${isPdf ? 'file-pdf' : 'file-alt'}"></i> ${isPdf ? 'PDF' : 'Texte'}
                    </span>
                </div>
                <div class="text-viewer-actions">
                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleFullscreen('${viewerId}')" title="Plein écran">
                        <i class="fas fa-expand" id="${viewerId}-expand-icon"></i>
                        <i class="fas fa-compress d-none" id="${viewerId}-compress-icon"></i>
                    </button>
                </div>
            </div>
            <div class="text-content p-4" id="${viewerId}-content">
    `;
    
    if (isPdf && lesson.content_file_url) {
        html += `
                <div class="pdf-viewer-wrapper">
                    <iframe src="${lesson.content_file_url}#toolbar=1" class="pdf-iframe" frameborder="0"></iframe>
                </div>
        `;
    } else {
        const content = lesson.content_text || 'Aucun contenu disponible.';
        // Échapper le HTML et convertir les retours à la ligne
        const escapedContent = content.replace(/\n/g, '<br>').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        html += `
                <div class="text-body">
                    ${escapedContent}
                </div>
        `;
    }
    
    html += `
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Ajouter les styles si pas déjà présents
    if (!document.getElementById('text-viewer-styles')) {
        const style = document.createElement('style');
        style.id = 'text-viewer-styles';
        style.textContent = `
            .text-viewer-container {
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                display: flex;
                flex-direction: column;
                height: 100%;
                max-height: calc(100vh - 200px);
                transition: all 0.3s ease;
                position: relative;
            }
            .text-viewer-container.fullscreen {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 9999;
                max-height: 100vh;
                border-radius: 0;
                margin: 0;
            }
            .text-viewer-toolbar {
                flex-shrink: 0;
                border-bottom: 1px solid #dee2e6;
            }
            .text-content {
                flex: 1;
                overflow-y: auto;
                overflow-x: hidden;
                min-height: 0;
                display: flex;
                justify-content: center;
                align-items: flex-start;
            }
            .text-body {
                font-size: 1.1rem;
                line-height: 1.8;
                color: #333;
                word-wrap: break-word;
                max-width: 900px;
                width: 100%;
                margin: 0 auto;
                padding: 0 1rem;
            }
            .text-body p { margin-bottom: 1.5rem; }
            .text-body h1, .text-body h2, .text-body h3, .text-body h4, .text-body h5, .text-body h6 {
                color: #003366;
                margin-top: 2rem;
                margin-bottom: 1rem;
                font-weight: 600;
            }
            .text-body h1 { font-size: 2rem; }
            .text-body h2 { font-size: 1.75rem; }
            .text-body h3 { font-size: 1.5rem; }
            .text-body h4 { font-size: 1.25rem; }
            .text-body ul, .text-body ol {
                margin-left: 2rem;
                margin-bottom: 1.5rem;
                padding-left: 1rem;
            }
            .text-body li { margin-bottom: 0.5rem; }
            .text-body blockquote {
                border-left: 4px solid #ffcc33;
                padding-left: 1.5rem;
                margin: 1.5rem 0;
                font-style: italic;
                color: #666;
                background-color: #f8f9fa;
                padding: 1rem 1.5rem;
                border-radius: 4px;
            }
            .text-body code {
                background-color: #f4f4f4;
                padding: 0.2rem 0.4rem;
                border-radius: 3px;
                font-family: 'Courier New', monospace;
                font-size: 0.9em;
            }
            .text-body pre {
                background-color: #f4f4f4;
                padding: 1rem;
                border-radius: 4px;
                overflow-x: auto;
                margin: 1.5rem 0;
            }
            .text-body pre code {
                background-color: transparent;
                padding: 0;
            }
            .text-body img {
                max-width: 100%;
                height: auto;
                border-radius: 4px;
                margin: 1.5rem 0;
            }
            .text-body table {
                width: 100%;
                border-collapse: collapse;
                margin: 1.5rem 0;
            }
            .text-body table th, .text-body table td {
                border: 1px solid #dee2e6;
                padding: 0.75rem;
                text-align: left;
            }
            .text-body table th {
                background-color: #f8f9fa;
                font-weight: 600;
                color: #003366;
            }
            .text-body a {
                color: #003366;
                text-decoration: underline;
            }
            .text-body a:hover { color: #004080; }
            .pdf-viewer-wrapper {
                width: 100%;
                height: calc(100vh - 300px);
                min-height: 600px;
                position: relative;
            }
            .pdf-iframe {
                width: 100%;
                height: 100%;
                border: none;
            }
            .text-viewer-container.fullscreen .pdf-viewer-wrapper {
                height: calc(100vh - 80px);
            }
            @media (max-width: 767.98px) {
                .text-viewer-container {
                    max-height: calc(100vh - 150px);
                }
                .text-body {
                    font-size: 1rem;
                    line-height: 1.6;
                }
                .pdf-viewer-wrapper {
                    height: calc(100vh - 250px);
                    min-height: 400px;
                }
                .text-viewer-container.fullscreen .pdf-viewer-wrapper {
                    height: calc(100vh - 60px);
                }
            }
            .text-content::-webkit-scrollbar {
                width: 8px;
            }
            .text-content::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 4px;
            }
            .text-content::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 4px;
            }
            .text-content::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
        `;
        document.head.appendChild(style);
    }
}

// Fonction globale pour le plein écran
window.toggleFullscreen = function(viewerId) {
    const container = document.getElementById(viewerId);
    const expandIcon = document.getElementById(viewerId + '-expand-icon');
    const compressIcon = document.getElementById(viewerId + '-compress-icon');
    
    if (!container) return;
    
    if (container.classList.contains('fullscreen')) {
        // Exit fullscreen
        container.classList.remove('fullscreen');
        if (expandIcon) expandIcon.classList.remove('d-none');
        if (compressIcon) compressIcon.classList.add('d-none');
        document.body.style.overflow = '';
    } else {
        // Enter fullscreen
        container.classList.add('fullscreen');
        if (expandIcon) expandIcon.classList.add('d-none');
        if (compressIcon) compressIcon.classList.remove('d-none');
        document.body.style.overflow = 'hidden';
    }
};

// Exit fullscreen on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const fullscreenViewers = document.querySelectorAll('.text-viewer-container.fullscreen');
        fullscreenViewers.forEach(viewer => {
            const viewerId = viewer.id;
            const expandIcon = document.getElementById(viewerId + '-expand-icon');
            const compressIcon = document.getElementById(viewerId + '-compress-icon');
            viewer.classList.remove('fullscreen');
            if (expandIcon) expandIcon.classList.remove('d-none');
            if (compressIcon) compressIcon.classList.add('d-none');
            document.body.style.overflow = '';
        });
    }
});

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

.top-bar-return {
    gap: 0.75rem;
}

.top-bar-return .btn {
    flex: 0 0 auto;
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
    background-color: #fff9e6;
    border-left: 4px solid #ffcc33;
}

.lesson-item.completed {
    background-color: #f8fff8;
}

.lesson-item.preview {
    background-color: #fff8e1;
}

.video-container {
    position: relative;
    margin: 0;
    padding: 0;
}

.video-container .ratio {
    margin: 0;
    padding: 0;
}

.video-container .ratio > * {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* Désactiver le menu contextuel et le téléchargement sur les vidéos */
#lessonVideo {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* Empêcher le téléchargement via les contrôles natifs */
#lessonVideo::-webkit-media-controls-enclosure {
    overflow: hidden;
}

#lessonVideo::-webkit-media-controls-panel {
    width: calc(100% + 30px);
}

/* Désactiver le menu contextuel sur le conteneur vidéo */
.video-container {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.video-container * {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* Empêcher le téléchargement via le menu contextuel */
#youtubePlayerContainer iframe {
    pointer-events: auto;
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

/* Desktop: Réduire la taille du titre de la leçon */
.lesson-title {
    font-size: 1.5rem;
}

.lesson-description {
    font-size: 1rem;
}

/* Responsive: Mobile et Tablette */
@media (max-width: 991.98px) {
    .course-sidebar {
        position: static;
        height: auto;
        margin-top: 1rem;
    }
    
    .learning-content {
        margin-bottom: 1rem;
    }
    
    .lesson-content {
        padding: 1rem !important;
    }
    
    .lesson-title {
        font-size: 1.25rem;
        margin-bottom: 0.75rem !important;
    }
    
    .lesson-description {
        font-size: 0.9rem;
        margin-bottom: 1rem !important;
    }
    
    .section-header h6 {
        font-size: 0.95rem;
    }
    
    .lesson-item h6 {
        font-size: 0.9rem;
    }
    
    .course-sidebar h5 {
        font-size: 1rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    
    .card-body h6 {
        font-size: 0.9rem;
    }
    
    .card-body p {
        font-size: 0.85rem;
    }
}

@media (max-width: 767.98px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .lesson-title {
        font-size: 1.1rem;
    }
    
    .lesson-description {
        font-size: 0.85rem;
    }
    
    .lesson-content {
        padding: 0.75rem !important;
    }
    
    .video-container {
        margin-bottom: 0;
    }
    
    /* S'assurer que la sidebar apparaît bien en dessous du lecteur */
    .row > .col-lg-3.order-2 {
        margin-top: 1rem;
    }
    
    .lesson-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .lesson-actions .btn {
        width: 100%;
        font-size: 0.8rem;
    }
    
    .section-header {
        padding: 0.75rem !important;
    }
    
    .section-header h6 {
        font-size: 0.9rem;
    }
    
    .lesson-item {
        padding: 0.75rem !important;
    }
    
    .lesson-item h6 {
        font-size: 0.85rem;
    }
    
    .course-sidebar {
        padding: 0.5rem;
    }
    
    .course-sidebar .p-3 {
        padding: 0.75rem !important;
    }
    
    .course-sidebar h5 {
        font-size: 0.95rem;
    }
    
    .top-bar-return {
        gap: 0.5rem;
        padding: 0.5rem !important;
    }

    .top-bar-return span {
        font-size: 0.75rem;
        flex-grow: 0;
    }
    
    .lesson-resources .card-body {
        padding: 0.75rem;
    }
    
    .lesson-resources h5 {
        font-size: 1rem;
        margin-bottom: 0.75rem !important;
    }
}

@media (max-width: 575.98px) {
    .container-fluid {
        padding-left: 0.25rem;
        padding-right: 0.25rem;
    }
    
    .lesson-title {
        font-size: 1rem;
    }
    
    .lesson-description {
        font-size: 0.8rem;
    }
    
    .lesson-content {
        padding: 0.5rem !important;
    }
    
    .section-header {
        padding: 0.5rem !important;
    }
    
    .section-header h6 {
        font-size: 0.85rem;
    }
    
    .lesson-item {
        padding: 0.5rem !important;
    }
    
    .lesson-item h6 {
        font-size: 0.8rem;
    }
    
    .course-sidebar .p-3 {
        padding: 0.5rem !important;
    }
    
    .course-sidebar h5 {
        font-size: 0.9rem;
    }
    
    .btn {
        font-size: 0.75rem;
        padding: 0.4rem 0.6rem;
    }
    
    .top-bar-return {
        gap: 0.5rem;
    }

    .top-bar-return span {
        font-size: 0.7rem;
        flex-grow: 0;
    }
}
</style>
@endpush
