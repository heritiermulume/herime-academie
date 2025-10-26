@extends('layouts.app')

@section('title', 'Créer un cours - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-plus me-2"></i>Créer un nouveau cours
                        </h4>
                        <a href="{{ route('admin.courses') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.courses.store') }}" method="POST" id="courseForm" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Informations de base -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Informations de base
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label">Titre du cours <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="instructor_id" class="form-label">Instructeur <span class="text-danger">*</span></label>
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
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Catégorie <span class="text-danger">*</span></label>
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
                            
                            <div class="col-md-3 mb-3">
                                <label for="level" class="form-label">Niveau <span class="text-danger">*</span></label>
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
                            
                            <div class="col-md-3 mb-3">
                                <label for="language" class="form-label">Langue <span class="text-danger">*</span></label>
                                <select class="form-select @error('language') is-invalid @enderror" id="language" name="language" required>
                                    <option value="">Sélectionner</option>
                                    <option value="fr" {{ old('language') == 'fr' ? 'selected' : '' }}>Français</option>
                                    <option value="en" {{ old('language') == 'en' ? 'selected' : '' }}>English</option>
                                </select>
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Image de couverture -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="thumbnail" class="form-label">Cover Image</label>
                                <input type="file" class="form-control @error('thumbnail') is-invalid @enderror" 
                                       id="thumbnail" name="thumbnail" accept="image/*">
                                <small class="text-muted">Formats acceptés: JPEG, PNG, JPG, GIF, WebP. Taille max: 5MB</small>
                                @error('thumbnail')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="video_preview" class="form-label">Vidéo de prévisualisation</label>
                                <div class="mb-2">
                                    <input type="url" class="form-control @error('video_preview') is-invalid @enderror" 
                                           id="video_preview" name="video_preview" value="{{ old('video_preview') }}" 
                                           placeholder="Lien (https://...)">
                                    @error('video_preview')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <input type="file" class="form-control @error('video_preview_file') is-invalid @enderror" 
                                           id="video_preview_file" name="video_preview_file" accept="video/*" onchange="uploadVideoPreviewAjax(this)">
                                    <small class="text-muted">Optionnel: téléverser un fichier vidéo à la place du lien</small>
                                    <div class="progress mt-2" style="height: 6px; display:none;" id="videoPreviewProgress">
                                        <div class="progress-bar" role="progressbar" style="width:0%"></div>
                                    </div>
                                    <div class="mt-2 d-none" id="videoPreviewThumb"></div>
                                    @error('video_preview_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Prix et statut -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-dollar-sign me-2"></i>Prix et statut
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="price" class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" value="{{ old('price') }}" min="0" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="sale_price" class="form-label">Prix de vente (FCFA)</label>
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


                        <!-- Prérequis et objectifs -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-target me-2"></i>Prérequis et objectifs
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="requirements" class="form-label">Prérequis</label>
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
                            
                            <div class="col-md-6 mb-3">
                                <label for="what_you_will_learn" class="form-label">Ce que vous apprendrez</label>
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

                        <!-- SEO -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-search me-2"></i>Optimisation SEO
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="meta_description" class="form-label">Description SEO</label>
                                <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                          id="meta_description" name="meta_description" rows="3" maxlength="160">{{ old('meta_description') }}</textarea>
                                <small class="text-muted">Maximum 160 caractères</small>
                                @error('meta_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="meta_keywords" class="form-label">Mots-clés SEO</label>
                                <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" 
                                       id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}" 
                                       placeholder="mot-clé1, mot-clé2, mot-clé3">
                                @error('meta_keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                                       id="tags" name="tags" value="{{ old('tags') }}" 
                                       placeholder="tag1, tag2, tag3">
                                @error('tags')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Sections et leçons -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-list me-2"></i>Contenu du cours
                                </h5>
                            </div>
                        </div>

                        <div id="sections-container">
                            <!-- Les sections seront ajoutées dynamiquement -->
                        </div>

                        <button type="button" class="btn btn-primary mb-3" onclick="addSection()">
                            <i class="fas fa-plus me-1"></i>Ajouter une section
                        </button>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.courses') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annuler
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" onclick="saveDraft()">
                                    <i class="fas fa-save me-1"></i>Enregistrer comme brouillon
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-1"></i>Créer le cours
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let sectionCount = 0;
let lessonCount = 0;

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

// Upload AJAX de la vidéo de prévisualisation
function uploadVideoPreviewAjax(input) {
    const file = input.files && input.files[0];
    if (!file) return;

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const urlField = document.getElementById('video_preview');
    const progressWrapper = document.getElementById('videoPreviewProgress');
    const progressBar = progressWrapper.querySelector('.progress-bar');
    const thumb = document.getElementById('videoPreviewThumb');

    progressWrapper.style.display = 'block';
    progressBar.style.width = '0%';
    thumb.classList.add('d-none');
    thumb.innerHTML = '';

    const formData = new FormData();
    formData.append('file', file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '{{ route('uploads.video-preview') }}', true);
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
                        urlField.value = resp.path; // stocker le chemin pour sauvegarde
                        thumb.classList.remove('d-none');
                        thumb.innerHTML = `<video src="${resp.url}" controls style="max-width:100%; height:140px; border-radius:6px;"></video>`;
                    } else {
                        alert('Échec du téléversement');
                    }
                } catch (e) {
                    alert('Réponse invalide du serveur');
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

@push('styles')
<style>
.card-header h6 {
    color: #003366;
}

.form-label {
    font-weight: 600;
    color: #333;
}

.btn-outline-primary {
    border-color: #003366;
    color: #003366;
}

.btn-outline-primary:hover {
    background-color: #003366;
    border-color: #003366;
}

.text-primary {
    color: #003366 !important;
}
</style>
@endpush
@endsection