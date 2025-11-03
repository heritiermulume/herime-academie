@extends('layouts.app')

@section('title', 'Créer une leçon - ' . $course->title)

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header text-white" style="background-color: #003366;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-sm" title="Tableau de bord">
                                <i class="fas fa-tachometer-alt"></i>
                            </a>
                            <a href="{{ route('admin.courses.lessons', $course) }}" class="btn btn-outline-light btn-sm" title="Liste des leçons">
                                <i class="fas fa-th-list"></i>
                            </a>
                            <div>
                                <h4 class="mb-1">
                                    <i class="fas fa-plus-circle me-2"></i>Créer une nouvelle leçon
                                </h4>
                                <p class="mb-0 text-description small">Cours: {{ $course->title }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow">
                <div class="card-body">
                    <form action="{{ route('admin.courses.lessons.store', $course) }}" method="POST" id="lessonForm" enctype="multipart/form-data">
                        @csrf
                        
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
                                                    <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
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
                                                <option value="video" {{ old('type') == 'video' ? 'selected' : '' }}>Vidéo</option>
                                                <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>Texte</option>
                                                <option value="quiz" {{ old('type') == 'quiz' ? 'selected' : '' }}>Quiz</option>
                                                <option value="assignment" {{ old('type') == 'assignment' ? 'selected' : '' }}>Devoir</option>
                                            </select>
                                            @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Titre de la leçon <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                               id="title" name="title" value="{{ old('title') }}" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
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
                                        
                                        {{-- Champ YouTube (recommandé pour vidéos sécurisées) --}}
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-info-circle"></i> <strong>Recommandé:</strong> Utilisez YouTube pour un hébergement sécurisé.
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="youtube_video_id" class="form-label">
                                                <i class="fab fa-youtube text-danger"></i> URL YouTube (Mode Non Répertorié)
                                            </label>
                                            <input type="text" class="form-control @error('youtube_video_id') is-invalid @enderror" 
                                                   id="youtube_video_id" name="youtube_video_id" value="{{ old('youtube_video_id') }}" 
                                                   placeholder="https://www.youtube.com/watch?v=xxx ou youtu.be/xxx">
                                            <small class="text-muted">Collez l'URL complète ou juste l'ID de la vidéo YouTube</small>
                                            @error('youtube_video_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_unlisted" name="is_unlisted" value="1" {{ old('is_unlisted') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_unlisted">
                                                    Vidéo en mode "Non répertorié" sur YouTube
                                                </label>
                                            </div>
                                            <small class="text-muted">Cochez cette case si votre vidéo YouTube est en mode non répertorié (recommandé pour plus de sécurité)</small>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <label for="content_url" class="form-label">Ou URL directe (Vimeo, autres)</label>
                                            <input type="url" class="form-control @error('content_url') is-invalid @enderror" 
                                                   id="content_url" name="content_url" value="{{ old('content_url') }}" 
                                                   placeholder="Lien (https://vimeo.com/...)">
                                            <small class="text-muted">Pour Vimeo ou autres plateformes</small>
                                            @error('content_url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="form-label small">Ou téléverser un fichier</label>
                                            <div class="upload-zone" id="contentUploadZone">
                                                <input type="file" 
                                                       class="form-control d-none @error('content_file') is-invalid @enderror" 
                                                       id="content_file" 
                                                       name="content_file" 
                                                       accept="video/mp4,video/webm,application/pdf"
                                                       onchange="handleContentUpload(this)">
                                                <div class="upload-placeholder text-center p-3" onclick="document.getElementById('content_file').click()">
                                                    <i class="fas fa-file-upload fa-2x text-success mb-2"></i>
                                                    <p class="mb-1 small"><strong>Cliquez pour sélectionner un fichier</strong></p>
                                                    <p class="text-muted small mb-0">Vidéo : MP4, WEBM (Max 100MB) | Document : PDF (Max 10MB)</p>
                                                </div>
                                                <div class="upload-preview d-none">
                                                    <div class="preview-content"></div>
                                                    <div class="upload-info mt-2 text-center">
                                                        <span class="badge bg-primary file-name"></span>
                                                        <span class="badge bg-info file-size"></span>
                                                    </div>
                                                    <div class="progress mt-2" style="height: 6px; display:none;" id="uploadProgress">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-danger mt-2 d-block mx-auto" onclick="clearContent()">
                                                        <i class="fas fa-trash me-1"></i>Supprimer
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="invalid-feedback d-block" id="contentError"></div>
                                            @error('content_file')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <!-- Contenu texte (pour leçons texte) -->
                                    <div class="mb-3" id="content-text-field" style="display: none;">
                                        <label for="content_text" class="form-label">Contenu de la leçon</label>
                                        <textarea class="form-control @error('content_text') is-invalid @enderror" 
                                                  id="content_text" name="content_text" rows="10">{{ old('content_text') }}</textarea>
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
                                                   id="duration" name="duration" value="{{ old('duration', 0) }}" min="0">
                                            @error('duration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" id="is_preview" name="is_preview" value="1" {{ old('is_preview') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_preview">
                                                    Leçon en aperçu (gratuite)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }}>
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
                                            <p class="text-muted">Sélectionnez un type de leçon pour voir l'aperçu</p>
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
                                <i class="fas fa-save me-1"></i>Créer la leçon
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
    // Constantes de validation
    const MAX_VIDEO_SIZE = 100 * 1024 * 1024; // 100MB
    const MAX_PDF_SIZE = 10 * 1024 * 1024; // 10MB
    const VALID_VIDEO_TYPES = ['video/mp4', 'video/webm', 'video/ogg'];
    const VALID_PDF_TYPES = ['application/pdf'];

    // Gestion de l'upload de contenu (vidéo ou PDF)
    function handleContentUpload(input) {
        const zone = document.getElementById('contentUploadZone');
        const placeholder = zone.querySelector('.upload-placeholder');
        const preview = zone.querySelector('.upload-preview');
        const errorDiv = document.getElementById('contentError');
        const previewContent = preview.querySelector('.preview-content');
        
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validation du type et de la taille
            if (VALID_VIDEO_TYPES.includes(file.type)) {
                if (file.size > MAX_VIDEO_SIZE) {
                    showError(errorDiv, '❌ La vidéo est trop volumineuse. Maximum 100MB.');
                    input.value = '';
                    return;
                }
            } else if (VALID_PDF_TYPES.includes(file.type)) {
                if (file.size > MAX_PDF_SIZE) {
                    showError(errorDiv, '❌ Le PDF est trop volumineux. Maximum 10MB.');
                    input.value = '';
                    return;
                }
            } else {
                showError(errorDiv, '❌ Format invalide. Utilisez MP4, WEBM ou PDF.');
                input.value = '';
                return;
            }
            
            // Afficher les infos
            preview.querySelector('.file-name').textContent = file.name;
            preview.querySelector('.file-size').textContent = formatFileSize(file.size);
            
            // Preview selon le type
            if (VALID_VIDEO_TYPES.includes(file.type)) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContent.innerHTML = `<video controls class="w-100 rounded" style="max-height: 200px; border: 3px solid #28a745;"><source src="${e.target.result}"></video>`;
                    placeholder.classList.add('d-none');
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else if (VALID_PDF_TYPES.includes(file.type)) {
                previewContent.innerHTML = `<div class="alert alert-success text-center mb-0"><i class="fas fa-file-pdf fa-3x mb-2"></i><p class="mb-0">PDF sélectionné</p></div>`;
                placeholder.classList.add('d-none');
                preview.classList.remove('d-none');
            }
            
            // Upload AJAX optionnel
            uploadLessonFile(input);
        }
    }

    function clearContent() {
        const zone = document.getElementById('contentUploadZone');
        const placeholder = zone.querySelector('.upload-placeholder');
        const preview = zone.querySelector('.upload-preview');
        const input = document.getElementById('content_file');
        const errorDiv = document.getElementById('contentError');
        
        input.value = '';
        preview.querySelector('.preview-content').innerHTML = '';
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';
        preview.classList.add('d-none');
        placeholder.classList.remove('d-none');
    }

    function showError(errorDiv, message) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

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

    // Upload AJAX optionnel
    function uploadLessonFile(input) {
        // Upload AJAX désactivé - l'upload se fera via le formulaire normal
        // Pour activer, créez la route 'uploads.lesson-file' dans routes/web.php
        return;
        
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!token) return;

        const urlInput = document.getElementById('content_url');
        const progressWrapper = document.getElementById('uploadProgress');
        const progressBar = progressWrapper.querySelector('.progress-bar');

        progressWrapper.style.display = 'block';
        progressBar.style.width = '0%';

        const formData = new FormData();
        formData.append('file', file);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadRoute, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', token);

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
            }
        };

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                progressWrapper.style.display = 'none';
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        if (resp.success && resp.path) {
                            urlInput.value = resp.path;
                        }
                    } catch (e) {
                        console.error('Erreur de parsing de la réponse', e);
                    }
                }
            }
        };

        xhr.send(formData);
    }
</script>
@endpush

@push('styles')
<style>
/* En-tête */
.text-description {
    opacity: 0.9;
}

/* Zone d'upload moderne */
.upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    overflow: hidden;
}

.upload-zone:hover {
    border-color: #28a745;
    background-color: #e9ecef;
}

.upload-placeholder {
    cursor: pointer;
    transition: all 0.2s ease;
}

.upload-placeholder:hover {
    background-color: rgba(40, 167, 69, 0.05);
}

.upload-placeholder:hover i {
    transform: scale(1.1);
}

.upload-placeholder i {
    transition: transform 0.2s ease;
}

.upload-preview {
    padding: 1.5rem;
}

.upload-preview video {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.upload-info {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.upload-info .badge {
    font-size: 0.85rem;
    padding: 0.4em 0.8em;
}
</style>
@endpush
@endsection
