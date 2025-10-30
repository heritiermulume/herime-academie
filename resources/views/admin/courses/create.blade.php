@extends('layouts.app')

@section('title', 'Créer un cours - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header text-white" style="background-color: #003366;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-sm" title="Tableau de bord">
                                <i class="fas fa-tachometer-alt"></i>
                            </a>
                            <a href="{{ route('admin.courses') }}" class="btn btn-outline-light btn-sm" title="Liste des cours">
                                <i class="fas fa-th-list"></i>
                            </a>
                            <div>
                                <h4 class="mb-1">
                                    <i class="fas fa-plus-circle me-2"></i>Créer un nouveau cours
                                </h4>
                                <p class="mb-0 text-description small">Ajoutez un nouveau cours à votre plateforme</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Affichage des erreurs -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Erreurs de validation</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <form action="{{ route('admin.courses.store') }}" method="POST" id="courseForm" enctype="multipart/form-data">
                @csrf
                
                <!-- Informations de base -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations de base</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-8">
                                <label for="title" class="form-label fw-bold">
                                    Titre du cours <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                       id="title" 
                                       name="title" 
                                       value="{{ old('title') }}" 
                                       placeholder="Ex: Formation complète en développement web"
                                       required>
                                <small class="form-text text-muted">Le titre principal du cours</small>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="instructor_id" class="form-label fw-bold">Instructeur <span class="text-danger">*</span></label>
                                <select class="form-select @error('instructor_id') is-invalid @enderror" 
                                        id="instructor_id" name="instructor_id" required>
                                    <option value="">Sélectionner un instructeur</option>
                                    @foreach($instructors as $instructor)
                                        <option value="{{ $instructor->id }}" {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                            {{ $instructor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('instructor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="category_id" class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                    <option value="">Sélectionner une catégorie</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="level" class="form-label fw-bold">Niveau <span class="text-danger">*</span></label>
                                <select class="form-select @error('level') is-invalid @enderror" id="level" name="level" required>
                                    <option value="">Sélectionner</option>
                                    <option value="beginner" {{ old('level') == 'beginner' ? 'selected' : '' }}>Débutant</option>
                                    <option value="intermediate" {{ old('level') == 'intermediate' ? 'selected' : '' }}>Intermédiaire</option>
                                    <option value="advanced" {{ old('level') == 'advanced' ? 'selected' : '' }}>Avancé</option>
                                </select>
                                @error('level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="language" class="form-label fw-bold">Langue <span class="text-danger">*</span></label>
                                <select class="form-select @error('language') is-invalid @enderror" id="language" name="language" required>
                                    <option value="">Sélectionner</option>
                                    <option value="fr" {{ old('language') == 'fr' ? 'selected' : '' }}>Français</option>
                                    <option value="en" {{ old('language') == 'en' ? 'selected' : '' }}>English</option>
                                </select>
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12">
                                <label for="description" class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="5"
                                          placeholder="Décrivez le contenu et les objectifs du cours..."
                                          required>{{ old('description') }}</textarea>
                                <small class="form-text text-muted">Une description détaillée qui sera visible par les étudiants</small>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Médias -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-success text-white">
                        <h5 class="mb-0"><i class="fas fa-photo-video me-2"></i>Médias du cours</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6 mb-3">
                                <label for="thumbnail" class="form-label fw-bold">Image de couverture</label>
                                <div class="upload-zone" id="thumbnailUploadZone">
                                    <input type="file" 
                                           class="form-control d-none @error('thumbnail') is-invalid @enderror" 
                                           id="thumbnail" 
                                           name="thumbnail" 
                                           accept="image/jpeg,image/png,image/jpg,image/webp"
                                           onchange="handleThumbnailUpload(this)">
                                    <div class="upload-placeholder text-center p-4" onclick="document.getElementById('thumbnail').click()">
                                        <i class="fas fa-image fa-3x text-primary mb-3"></i>
                                        <p class="mb-2"><strong>Cliquez pour sélectionner une image</strong></p>
                                        <p class="text-muted small mb-0">Format : JPG, PNG, WEBP | Max : 5MB</p>
                                        <p class="text-muted small">Recommandé : 1920x1080px (16:9)</p>
                                    </div>
                                    <div class="upload-preview d-none">
                                        <img src="" alt="Preview" class="img-fluid rounded mx-auto d-block" style="max-width: 100%; max-height: 300px; border: 3px solid #28a745;">
                                        <div class="upload-info mt-2 text-center">
                                            <span class="badge bg-primary file-name"></span>
                                            <span class="badge bg-info file-size"></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger mt-2 d-block mx-auto" onclick="clearThumbnail()">
                                            <i class="fas fa-trash me-1"></i>Supprimer
                                        </button>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" id="thumbnailError"></div>
                                @error('thumbnail')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="video_preview" class="form-label fw-bold">Vidéo de prévisualisation</label>
                                
                                <!-- Option 1: Lien vidéo -->
                                <div class="mb-3">
                                    <label class="form-label small">Option 1: Lien vidéo</label>
                                    <input type="url" class="form-control @error('video_preview') is-invalid @enderror" 
                                           id="video_preview" name="video_preview" value="{{ old('video_preview') }}" 
                                           placeholder="https://...">
                                    @error('video_preview')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Option 2: Upload fichier -->
                                <div>
                                    <label class="form-label small">Option 2: Téléverser un fichier</label>
                                    <div class="upload-zone" id="videoUploadZone">
                                        <input type="file" 
                                               class="form-control d-none @error('video_preview_file') is-invalid @enderror" 
                                               id="video_preview_file" 
                                               name="video_preview_file" 
                                               accept="video/mp4,video/webm,video/ogg"
                                               onchange="handleVideoUpload(this)">
                                        <div class="upload-placeholder text-center p-3" onclick="document.getElementById('video_preview_file').click()">
                                            <i class="fas fa-video fa-2x text-success mb-2"></i>
                                            <p class="mb-1 small"><strong>Cliquez pour sélectionner une vidéo</strong></p>
                                            <p class="text-muted small mb-0">Format : MP4, WEBM | Max : 100MB</p>
                                        </div>
                                        <div class="upload-preview d-none">
                                            <video controls class="w-100 rounded" style="max-height: 200px; border: 3px solid #28a745;"></video>
                                            <div class="upload-info mt-2 text-center">
                                                <span class="badge bg-primary file-name"></span>
                                                <span class="badge bg-info file-size"></span>
                                            </div>
                                            <div class="progress mt-2" style="height: 6px; display:none;" id="videoPreviewProgress">
                                                <div class="progress-bar bg-success" role="progressbar" style="width:0%"></div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger mt-2 d-block mx-auto" onclick="clearVideo()">
                                                <i class="fas fa-trash me-1"></i>Supprimer
                                            </button>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback d-block" id="videoError"></div>
                                    @error('video_preview_file')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prix et statut -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Prix et statut</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-3">
                                <label for="price" class="form-label fw-bold">Prix ({{ $baseCurrency ?? 'USD' }}) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" value="{{ old('price') }}" min="0" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="sale_price" class="form-label fw-bold">Prix de vente ({{ $baseCurrency ?? 'USD' }})</label>
                                <input type="number" class="form-control @error('sale_price') is-invalid @enderror" 
                                       id="sale_price" name="sale_price" value="{{ old('sale_price') }}" min="0">
                                @error('sale_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_free" name="is_free" value="1" 
                                           {{ old('is_free') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_free">
                                        Cours gratuit
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" 
                                           {{ old('is_published') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">
                                        Publier immédiatement
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" 
                                           {{ old('is_featured') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">
                                        Cours en vedette
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prérequis et objectifs -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-info text-white">
                        <h5 class="mb-0"><i class="fas fa-target me-2"></i>Prérequis et objectifs</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="requirements" class="form-label fw-bold">Prérequis</label>
                                <div id="requirements-container">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="requirements[]" placeholder="Ajouter un prérequis">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeRequirement(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRequirement()">
                                    <i class="fas fa-plus me-1"></i>Ajouter un prérequis
                                </button>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="what_you_will_learn" class="form-label fw-bold">Ce que vous apprendrez</label>
                                <div id="learnings-container">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="what_you_will_learn[]" placeholder="Ajouter un objectif">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeLearning(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLearning()">
                                    <i class="fas fa-plus me-1"></i>Ajouter un objectif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Optimisation SEO</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="meta_description" class="form-label fw-bold">Description SEO</label>
                                <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                          id="meta_description" name="meta_description" rows="3" maxlength="160">{{ old('meta_description') }}</textarea>
                                <small class="text-muted">Maximum 160 caractères</small>
                                @error('meta_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="meta_keywords" class="form-label fw-bold">Mots-clés SEO</label>
                                <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" 
                                       id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}" 
                                       placeholder="mot-clé1, mot-clé2, mot-clé3">
                                @error('meta_keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12">
                                <label for="tags" class="form-label fw-bold">Tags</label>
                                <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                                       id="tags" name="tags" value="{{ old('tags') }}" 
                                       placeholder="tag1, tag2, tag3">
                                @error('tags')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu du cours (Sections et leçons) -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Contenu du cours</h5>
                    </div>
                    <div class="card-body">
                        <div id="sections-container">
                            <!-- Les sections seront ajoutées dynamiquement -->
                        </div>

                        <button type="button" class="btn btn-primary" onclick="addSection()">
                            <i class="fas fa-plus me-1"></i>Ajouter une section
                        </button>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <a href="{{ route('admin.courses') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annuler
                            </a>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" onclick="saveDraft()">
                                    <i class="fas fa-save me-1"></i>Enregistrer comme brouillon
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-1"></i>Créer le cours
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Constantes de validation
const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5MB
const MAX_VIDEO_SIZE = 100 * 1024 * 1024; // 100MB
const VALID_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
const VALID_VIDEO_TYPES = ['video/mp4', 'video/webm', 'video/ogg'];

let sectionCount = 0;
let lessonCount = 0;

// Gestion de l'upload d'image de couverture
function handleThumbnailUpload(input) {
    const zone = document.getElementById('thumbnailUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const errorDiv = document.getElementById('thumbnailError');
    
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        if (!VALID_IMAGE_TYPES.includes(file.type)) {
            showError(errorDiv, '❌ Format invalide. Utilisez JPG, PNG ou WEBP.');
            input.value = '';
            return;
        }
        
        if (file.size > MAX_IMAGE_SIZE) {
            showError(errorDiv, '❌ Le fichier est trop volumineux. Maximum 5MB.');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = preview.querySelector('img');
            img.src = e.target.result;
            preview.querySelector('.file-name').textContent = file.name;
            preview.querySelector('.file-size').textContent = formatFileSize(file.size);
            placeholder.classList.add('d-none');
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
}

function clearThumbnail() {
    const zone = document.getElementById('thumbnailUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const input = document.getElementById('thumbnail');
    const errorDiv = document.getElementById('thumbnailError');
    
    input.value = '';
    preview.querySelector('img').src = '';
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    preview.classList.add('d-none');
    placeholder.classList.remove('d-none');
}

// Gestion de l'upload de vidéo
function handleVideoUpload(input) {
    const zone = document.getElementById('videoUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const errorDiv = document.getElementById('videoError');
    
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        if (!VALID_VIDEO_TYPES.includes(file.type)) {
            showError(errorDiv, '❌ Format invalide. Utilisez MP4 ou WEBM.');
            input.value = '';
            return;
        }
        
        if (file.size > MAX_VIDEO_SIZE) {
            showError(errorDiv, '❌ Le fichier est trop volumineux. Maximum 100MB.');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const video = preview.querySelector('video');
            video.src = e.target.result;
            preview.querySelector('.file-name').textContent = file.name;
            preview.querySelector('.file-size').textContent = formatFileSize(file.size);
            placeholder.classList.add('d-none');
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
        
        // Upload AJAX optionnel
        uploadVideoPreviewAjax(input);
    }
}

function clearVideo() {
    const zone = document.getElementById('videoUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const input = document.getElementById('video_preview_file');
    const errorDiv = document.getElementById('videoError');
    
    input.value = '';
    preview.querySelector('video').src = '';
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    preview.classList.add('d-none');
    placeholder.classList.remove('d-none');
}

// Fonction utilitaires
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

// Gestion des prérequis
function addRequirement() {
    const container = document.getElementById('requirements-container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" name="requirements[]" placeholder="Ajouter un prérequis">
        <button type="button" class="btn btn-outline-danger" onclick="removeRequirement(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeRequirement(button) {
    button.parentElement.remove();
}

// Gestion des objectifs
function addLearning() {
    const container = document.getElementById('learnings-container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" name="what_you_will_learn[]" placeholder="Ajouter un objectif">
        <button type="button" class="btn btn-outline-danger" onclick="removeLearning(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeLearning(button) {
    button.parentElement.remove();
}

// Gestion des sections
function addSection() {
    sectionCount++;
    const container = document.getElementById('sections-container');
    const sectionDiv = document.createElement('div');
    sectionDiv.className = 'card border-0 shadow-sm mb-3';
    sectionDiv.id = `section-${sectionCount}`;
    sectionDiv.innerHTML = `
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Section ${sectionCount}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSection(${sectionCount})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label">Titre de la section</label>
                    <input type="text" class="form-control" name="sections[${sectionCount}][title]" placeholder="Titre de la section" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="sections[${sectionCount}][description]" placeholder="Description (optionnel)">
                </div>
            </div>
            <div id="lessons-${sectionCount}">
                <!-- Les leçons seront ajoutées ici -->
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLesson(${sectionCount})">
                <i class="fas fa-plus me-1"></i>Ajouter une leçon
            </button>
        </div>
    `;
    container.appendChild(sectionDiv);
}

function removeSection(sectionId) {
    document.getElementById(`section-${sectionId}`).remove();
}

// Gestion des leçons
function addLesson(sectionId) {
    lessonCount++;
    const container = document.getElementById(`lessons-${sectionId}`);
    const lessonDiv = document.createElement('div');
    lessonDiv.className = 'card border-0 shadow-sm mb-2';
    lessonDiv.innerHTML = `
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Titre de la leçon</label>
                    <input type="text" class="form-control" name="sections[${sectionId}][lessons][${lessonCount}][title]" placeholder="Titre de la leçon" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="sections[${sectionId}][lessons][${lessonCount}][type]" required>
                        <option value="">Sélectionner</option>
                        <option value="video">Vidéo</option>
                        <option value="text">Texte</option>
                        <option value="quiz">Quiz</option>
                        <option value="assignment">Devoir</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Durée (min)</label>
                    <input type="number" class="form-control" name="sections[${sectionId}][lessons][${lessonCount}][duration]" min="0" placeholder="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Aperçu</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="sections[${sectionId}][lessons][${lessonCount}][is_preview]" value="1">
                        <label class="form-check-label">Gratuit</label>
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger d-block" onclick="removeLesson(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="sections[${sectionId}][lessons][${lessonCount}][description]" rows="2" placeholder="Description de la leçon"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contenu de la leçon</label>
                    <div class="lesson-content-upload">
                        <input type="url" class="form-control mb-2 lesson-url-input" name="sections[${sectionId}][lessons][${lessonCount}][content_url]" placeholder="Lien (https://...)">
                        <input type="file" class="form-control lesson-file-input" name="sections[${sectionId}][lessons][${lessonCount}][content_file]" accept="video/*,application/pdf" onchange="uploadLessonInline(this)">
                        <small class="text-muted">Vous pouvez fournir un lien OU téléverser un fichier</small>
                        <div class="progress mt-2 d-none" style="height:6px;">
                            <div class="progress-bar" role="progressbar" style="width:0%"></div>
                        </div>
                        <div class="mt-2 d-none lesson-file-preview"></div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <label class="form-label">Contenu texte</label>
                    <textarea class="form-control" name="sections[${sectionId}][lessons][${lessonCount}][content_text]" rows="3" placeholder="Contenu texte de la leçon"></textarea>
                </div>
            </div>
        </div>
    `;
    container.appendChild(lessonDiv);
}

function removeLesson(button) {
    button.closest('.card').remove();
}

// Sauvegarder comme brouillon
function saveDraft() {
    document.getElementById('is_published').checked = false;
    document.getElementById('courseForm').submit();
}

// Gestion du cours gratuit
document.getElementById('is_free').addEventListener('change', function() {
    const priceField = document.getElementById('price');
    const salePriceField = document.getElementById('sale_price');
    
    if (this.checked) {
        priceField.value = '0';
        priceField.disabled = true;
        salePriceField.value = '0';
        salePriceField.disabled = true;
    } else {
        priceField.disabled = false;
        salePriceField.disabled = false;
    }
});

// Upload AJAX de la vidéo de prévisualisation (optionnel)
function uploadVideoPreviewAjax(input) {
    // Upload AJAX désactivé - l'upload se fera via le formulaire normal
    // Pour activer, créez la route 'uploads.video-preview' dans routes/web.php
    return;
    
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) return;

    const urlField = document.getElementById('video_preview');
    const progressWrapper = document.getElementById('videoPreviewProgress');
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
                        urlField.value = resp.path;
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

/* Gradients pour les headers */
.bg-gradient-primary {
    background: linear-gradient(135deg, #003366 0%, #004080 100%) !important;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%) !important;
}

.bg-gradient-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
}

/* Cards */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 12px;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-2px);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border-bottom: none;
}

.card-header h5 {
    font-weight: 600;
}

/* Form controls */
.form-label {
    font-weight: 600;
    color: #333;
}

.form-label.fw-bold {
    color: #003366;
}

.form-control-lg {
    font-size: 1.1rem;
    padding: 0.75rem 1rem;
}

.form-control:focus, .form-select:focus {
    border-color: #003366;
    box-shadow: 0 0 0 0.2rem rgba(0, 51, 102, 0.25);
}

/* Buttons */
.btn {
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-outline-primary {
    border-color: #003366;
    color: #003366;
}

.btn-outline-primary:hover {
    background-color: #003366;
    border-color: #003366;
    color: white;
}

.text-primary {
    color: #003366 !important;
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
    border-color: #003366;
    background-color: #e9ecef;
}

.upload-placeholder {
    cursor: pointer;
    transition: all 0.2s ease;
}

.upload-placeholder:hover {
    background-color: rgba(0, 51, 102, 0.05);
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

.upload-preview img,
.upload-preview video {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.upload-preview img:hover {
    transform: scale(1.02);
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