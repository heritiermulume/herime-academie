@extends('layouts.app')

@section('title', 'Modifier la leçon - ' . $lesson->title)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Modifier la leçon: {{ $lesson->title }}
                        </h4>
                        <a href="{{ route('admin.courses.lessons', $course) }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.lessons.update', $lesson) }}" method="POST" id="lessonForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Informations de base -->
                                <div class="mb-4">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-info-circle me-2"></i>Informations de base
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="section_id" class="form-label">Section <span class="text-danger">*</span></label>
                                            <select class="form-select @error('section_id') is-invalid @enderror" 
                                                    id="section_id" name="section_id" required>
                                                <option value="">Sélectionner une section</option>
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}" {{ old('section_id', $lesson->section_id) == $section->id ? 'selected' : '' }}>
                                                        {{ $section->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('section_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="type" class="form-label">Type de leçon <span class="text-danger">*</span></label>
                                            <select class="form-select @error('type') is-invalid @enderror" 
                                                    id="type" name="type" required>
                                                <option value="">Sélectionner un type</option>
                                                <option value="video" {{ old('type', $lesson->type) == 'video' ? 'selected' : '' }}>Vidéo</option>
                                                <option value="text" {{ old('type', $lesson->type) == 'text' ? 'selected' : '' }}>Texte</option>
                                                <option value="quiz" {{ old('type', $lesson->type) == 'quiz' ? 'selected' : '' }}>Quiz</option>
                                                <option value="assignment" {{ old('type', $lesson->type) == 'assignment' ? 'selected' : '' }}>Devoir</option>
                                            </select>
                                            @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Titre de la leçon <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                               id="title" name="title" value="{{ old('title', $lesson->title) }}" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="3">{{ old('description', $lesson->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Contenu de la leçon -->
                                <div class="mb-4">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-file-alt me-2"></i>Contenu de la leçon
                                    </h5>
                                    
                                    <!-- URL du contenu (pour vidéos, quiz, devoirs) -->
                                    <div class="mb-3" id="content-url-field">
                                        <label for="content_url" class="form-label">Contenu de la leçon</label>
                                        @if($lesson->content_url && !str_starts_with($lesson->content_url, 'http'))
                                            <div class="mb-2">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-file me-2"></i>Fichier actuel: {{ basename($lesson->content_url) }}
                                                </div>
                                            </div>
                                        @endif
                                        <div class="mb-2">
                                            <input type="url" class="form-control @error('content_url') is-invalid @enderror" 
                                                   id="content_url" name="content_url" value="{{ old('content_url', str_starts_with($lesson->content_url ?? '', 'http') ? $lesson->content_url : '') }}" 
                                                   placeholder="Lien (https://youtube.com/watch?v=... ou https://vimeo.com/...)">
                                            <small class="text-muted">Optionnel: lien vers le contenu</small>
                                            @error('content_url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div>
                                            <input type="file" class="form-control @error('content_file') is-invalid @enderror" 
                                                   id="content_file" name="content_file" accept="video/*,application/pdf" onchange="uploadLessonFileEdit(this)">
                                            <small class="text-muted">Optionnel: téléverser un fichier (vidéo ou PDF)</small>
                                            <div class="progress mt-2" style="height: 6px; display:none;" id="uploadProgress">
                                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                            </div>
                                            <div class="mt-2 d-none" id="filePreview"></div>
                                            @error('content_file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <!-- Contenu texte (pour leçons texte) -->
                                    <div class="mb-3" id="content-text-field" style="display: none;">
                                        <label for="content_text" class="form-label">Contenu de la leçon</label>
                                        <textarea class="form-control @error('content_text') is-invalid @enderror" 
                                                  id="content_text" name="content_text" rows="10">{{ old('content_text', $lesson->content_text) }}</textarea>
                                        <small class="text-muted">Utilisez le format Markdown pour le formatage</small>
                                        @error('content_text')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Paramètres avancés -->
                                <div class="mb-4">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-cog me-2"></i>Paramètres
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="duration" class="form-label">Durée (minutes)</label>
                                            <input type="number" class="form-control @error('duration') is-invalid @enderror" 
                                                   id="duration" name="duration" value="{{ old('duration', $lesson->duration) }}" min="0">
                                            @error('duration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" id="is_preview" name="is_preview" value="1" {{ old('is_preview', $lesson->is_preview) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_preview">
                                                    Leçon en aperçu (gratuite)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', $lesson->is_published) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_published">
                                            Publier immédiatement
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- Aperçu de la leçon -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-eye me-2"></i>Aperçu
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="lesson-preview">
                                            <h6 class="text-primary">{{ $lesson->title }}</h6>
                                            <p class="text-muted small">{{ $lesson->description ?: 'Description de la leçon' }}</p>
                                            @if($lesson->type === 'video')
                                                <div class="bg-light p-3 rounded text-center"><i class="fas fa-video fa-2x text-muted"></i><br><small class="text-muted">Vidéo</small></div>
                                            @elseif($lesson->type === 'text')
                                                <div class="bg-light p-3 rounded"><i class="fas fa-file-alt me-2"></i>Contenu texte</div>
                                            @elseif($lesson->type === 'quiz')
                                                <div class="bg-light p-3 rounded text-center"><i class="fas fa-question-circle fa-2x text-muted"></i><br><small class="text-muted">Quiz</small></div>
                                            @elseif($lesson->type === 'assignment')
                                                <div class="bg-light p-3 rounded text-center"><i class="fas fa-tasks fa-2x text-muted"></i><br><small class="text-muted">Devoir</small></div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Statistiques de la leçon -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-chart-bar me-2"></i>Statistiques
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="border-end">
                                                    <h5 class="text-primary mb-0">{{ $lesson->duration }}</h5>
                                                    <small class="text-muted">Minutes</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <h5 class="text-success mb-0">
                                                    @if($lesson->is_published)
                                                        <i class="fas fa-check-circle"></i>
                                                    @else
                                                        <i class="fas fa-times-circle"></i>
                                                    @endif
                                                </h5>
                                                <small class="text-muted">Publié</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.courses.lessons', $course) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const contentUrlField = document.getElementById('content-url-field');
        const contentTextField = document.getElementById('content-text-field');
        const preview = document.getElementById('lesson-preview');
        
        function updateContentFields() {
            const type = typeSelect.value;
            
            if (type === 'text') {
                contentUrlField.style.display = 'none';
                contentTextField.style.display = 'block';
            } else {
                contentUrlField.style.display = 'block';
                contentTextField.style.display = 'none';
            }
            
            updatePreview();
        }
        
        function updatePreview() {
            const type = typeSelect.value;
            const title = document.getElementById('title').value || 'Titre de la leçon';
            const description = document.getElementById('description').value || 'Description de la leçon';
            
            let previewContent = `
                <h6 class="text-primary">${title}</h6>
                <p class="text-muted small">${description}</p>
            `;
            
            if (type === 'video') {
                previewContent += '<div class="bg-light p-3 rounded text-center"><i class="fas fa-video fa-2x text-muted"></i><br><small class="text-muted">Vidéo</small></div>';
            } else if (type === 'text') {
                previewContent += '<div class="bg-light p-3 rounded"><i class="fas fa-file-alt me-2"></i>Contenu texte</div>';
            } else if (type === 'quiz') {
                previewContent += '<div class="bg-light p-3 rounded text-center"><i class="fas fa-question-circle fa-2x text-muted"></i><br><small class="text-muted">Quiz</small></div>';
            } else if (type === 'assignment') {
                previewContent += '<div class="bg-light p-3 rounded text-center"><i class="fas fa-tasks fa-2x text-muted"></i><br><small class="text-muted">Devoir</small></div>';
            }
            
            preview.innerHTML = previewContent;
        }
        
        typeSelect.addEventListener('change', updateContentFields);
        document.getElementById('title').addEventListener('input', updatePreview);
        document.getElementById('description').addEventListener('input', updatePreview);
        
        // Initialiser
        updateContentFields();
    });

    function uploadLessonFileEdit(input) {
        const file = input.files && input.files[0];
        if (!file) return;

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const urlInput = document.getElementById('content_url');
        const progressWrapper = document.getElementById('uploadProgress');
        const progressBar = progressWrapper.querySelector('.progress-bar');
        const preview = document.getElementById('filePreview');

        progressWrapper.style.display = 'block';
        progressBar.style.width = '0%';
        preview.classList.add('d-none');
        preview.innerHTML = '';

        const formData = new FormData();
        formData.append('file', file);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route('uploads.lesson-file') }}', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', token);

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
            }
        };

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        if (resp.success) {
                            urlInput.value = resp.path; // stocker le chemin pour sauvegarde
                            // Aperçu
                            preview.classList.remove('d-none');
                            if (file.type.startsWith('video/')) {
                                preview.innerHTML = `<video src="${resp.url}" controls style=\"max-width:100%; height:120px; border-radius:6px;\"></video>`;
                            } else {
                                preview.innerHTML = `<div class=\"alert alert-success py-2 mb-0\"><i class=\"fas fa-file-pdf me-2\"></i>Fichier téléchargé</div>`;
                            }
                        } else {
                            alert('Échec du téléversement');
                        }
                    } catch (_) {
                        alert('Réponse invalide du serveur.');
                    }
                } else {
                    alert('Erreur lors du téléversement (HTTP ' + xhr.status + ').');
                }
            }
        };

        xhr.send(formData);
    }
</script>
@endpush
@endsection


