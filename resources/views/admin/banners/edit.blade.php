@extends('layouts.admin')

@section('title', 'Modifier la bannière')
@section('admin-title', 'Modifier la bannière #' . $banner->id)
@section('admin-subtitle', 'Mettez à jour le visuel et les actions associées pour cette bannière')
@section('admin-actions')
    <a href="{{ route('admin.banners.index') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('admin-content')
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

    <div class="admin-panel">
        <div class="admin-panel__body admin-panel__body--padded">
            <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" id="bannerForm">
                @csrf
                @method('PUT')

                <div class="admin-form-grid">
                    <div class="admin-form-card">
                        <h5><i class="fas fa-info-circle me-2"></i>Informations générales</h5>
                        <div class="row g-3">
                            <!-- Titre -->
                            <div class="col-12">
                                <label for="title" class="form-label fw-bold">
                                    Titre <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                       id="title" 
                                       name="title" 
                                       value="{{ old('title', $banner->title) }}" 
                                       placeholder="Ex: Apprenez sans limites avec Herime Académie"
                                       required>
                                <small class="form-text text-muted">Le titre principal affiché sur la bannière</small>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sous-titre -->
                            <div class="col-12">
                                <label for="subtitle" class="form-label fw-bold">Sous-titre</label>
                                <textarea class="form-control @error('subtitle') is-invalid @enderror" 
                                          id="subtitle" 
                                          name="subtitle" 
                                          rows="2"
                                          placeholder="Ex: Découvrez des milliers de cours en ligne de qualité">{{ old('subtitle', $banner->subtitle) }}</textarea>
                                <small class="form-text text-muted">Texte secondaire sous le titre (optionnel)</small>
                                @error('subtitle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="admin-form-card">
                        <h5><i class="fas fa-images me-2"></i>Images de la bannière</h5>
                        <div class="row g-4">
                            <!-- Image principale -->
                            <div class="col-md-6">
                                <label for="image" class="form-label fw-bold">
                                    Image principale (Desktop)
                                </label>
                                
                                @if($banner->image)
                                <div class="current-image mb-3">
                                    <p class="fw-bold mb-2"><i class="fas fa-check-circle text-success me-1"></i>Image actuelle :</p>
                                    <img src="{{ $banner->image_url ?: 'https://via.placeholder.com/400x200?text=Banner' }}" alt="Image actuelle" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                                @endif
                                
                                <div class="upload-zone" id="imageUploadZone">
                                    <input type="file" 
                                           class="form-control d-none" 
                                           id="image" 
                                           name="image" 
                                           accept="image/jpeg,image/png,image/jpg,image/webp">
                                    <div class="upload-placeholder text-center p-4" onclick="document.getElementById('image').click()">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                        <p class="mb-2"><strong>Cliquez pour changer l'image</strong></p>
                                        <p class="text-muted small mb-0">Format : JPG, PNG, WEBP | Max : 10MB</p>
                                        <p class="text-muted small">Laissez vide pour conserver l'image actuelle</p>
                                    </div>
                                    <div class="upload-preview d-none">
                                        <img src="" alt="Preview" class="img-fluid rounded">
                                        <div class="upload-info mt-2">
                                            <span class="badge bg-primary file-name"></span>
                                            <span class="badge bg-info file-size"></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="clearImage('image')">
                                            <i class="fas fa-trash me-1"></i>Annuler
                                        </button>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" id="imageError"></div>
                            </div>

                            <!-- Image mobile -->
                            <div class="col-md-6">
                                <label for="mobile_image" class="form-label fw-bold">
                                    Image mobile (16:9) <span class="text-muted">(Optionnel)</span>
                                </label>
                                
                                @if($banner->mobile_image)
                                <div class="current-image mb-3">
                                    <p class="fw-bold mb-2"><i class="fas fa-check-circle text-success me-1"></i>Image mobile actuelle :</p>
                                    <img src="{{ $banner->mobile_image_url ?: 'https://via.placeholder.com/400x200?text=Banner+Mobile' }}" alt="Image mobile actuelle" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                                @endif
                                
                                <div class="upload-zone" id="mobileImageUploadZone">
                                    <input type="file" 
                                           class="form-control d-none" 
                                           id="mobile_image" 
                                           name="mobile_image" 
                                           accept="image/jpeg,image/png,image/jpg,image/webp">
                                    <div class="upload-placeholder text-center p-4" onclick="document.getElementById('mobile_image').click()">
                                        <i class="fas fa-mobile-alt fa-3x text-success mb-3"></i>
                                        <p class="mb-2"><strong>Cliquez pour changer l'image mobile</strong></p>
                                        <p class="text-muted small mb-0">Format : JPG, PNG, WEBP | Max : 10MB</p>
                                        <p class="text-muted small">Laissez vide pour conserver l'image actuelle</p>
                                    </div>
                                    <div class="upload-preview d-none">
                                        <img src="" alt="Preview" class="img-fluid rounded">
                                        <div class="upload-info mt-2">
                                            <span class="badge bg-primary file-name"></span>
                                            <span class="badge bg-info file-size"></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="clearImage('mobile_image')">
                                            <i class="fas fa-trash me-1"></i>Annuler
                                        </button>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" id="mobileImageError"></div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-form-card">
                        <h5><i class="fas fa-mouse-pointer me-2"></i>Boutons d'action</h5>
                        <div class="border rounded p-3 mb-3 bg-light">
                            <h6 class="fw-bold mb-3"><i class="fas fa-hand-pointer me-2 text-warning"></i>Bouton principal</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                <label for="button1_text" class="form-label">Texte du bouton</label>
                                    <input type="text" class="form-control" id="button1_text" name="button1_text" value="{{ old('button1_text', $banner->button1_text) }}" placeholder="Ex: Commencer">
                            </div>
                                <div class="col-md-4">
                                <label for="button1_url" class="form-label">URL</label>
                                    <input type="text" class="form-control" id="button1_url" name="button1_url" value="{{ old('button1_url', $banner->button1_url) }}" placeholder="/courses">
                            </div>
                                <div class="col-md-2">
                                <label for="button1_style" class="form-label">Style</label>
                                    <select class="form-select" id="button1_style" name="button1_style">
                                        <option value="warning" {{ old('button1_style', $banner->button1_style) == 'warning' ? 'selected' : '' }}>Warning (Jaune)</option>
                                        <option value="primary" {{ old('button1_style', $banner->button1_style) == 'primary' ? 'selected' : '' }}>Primary (Bleu)</option>
                                        <option value="success" {{ old('button1_style', $banner->button1_style) == 'success' ? 'selected' : '' }}>Success (Vert)</option>
                                        <option value="danger" {{ old('button1_style', $banner->button1_style) == 'danger' ? 'selected' : '' }}>Danger (Rouge)</option>
                                        <option value="info" {{ old('button1_style', $banner->button1_style) == 'info' ? 'selected' : '' }}>Info (Cyan)</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="button1_target" class="form-label">Ouverture</label>
                                    <select class="form-select" id="button1_target" name="button1_target">
                                        <option value="_self" {{ old('button1_target', $banner->button1_target ?? '_self') == '_self' ? 'selected' : '' }}>Même onglet</option>
                                        <option value="_blank" {{ old('button1_target', $banner->button1_target) == '_blank' ? 'selected' : '' }}>Nouvel onglet</option>
                                </select>
                                </div>
                            </div>
                            </div>

                            <!-- Bouton 2 -->
                        <div class="border rounded p-3 bg-light">
                            <h6 class="fw-bold mb-3"><i class="fas fa-hand-pointer me-2 text-info"></i>Bouton secondaire</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                <label for="button2_text" class="form-label">Texte du bouton</label>
                                    <input type="text" class="form-control" id="button2_text" name="button2_text" value="{{ old('button2_text', $banner->button2_text) }}" placeholder="Ex: Explorer">
                            </div>
                                <div class="col-md-4">
                                <label for="button2_url" class="form-label">URL</label>
                                    <input type="text" class="form-control" id="button2_url" name="button2_url" value="{{ old('button2_url', $banner->button2_url) }}" placeholder="#categories">
                            </div>
                                <div class="col-md-2">
                                <label for="button2_style" class="form-label">Style</label>
                                    <select class="form-select" id="button2_style" name="button2_style">
                                    <option value="outline-light" {{ old('button2_style', $banner->button2_style) == 'outline-light' ? 'selected' : '' }}>Outline Light</option>
                                    <option value="outline-primary" {{ old('button2_style', $banner->button2_style) == 'outline-primary' ? 'selected' : '' }}>Outline Primary</option>
                                    <option value="secondary" {{ old('button2_style', $banner->button2_style) == 'secondary' ? 'selected' : '' }}>Secondary</option>
                                    <option value="light" {{ old('button2_style', $banner->button2_style) == 'light' ? 'selected' : '' }}>Light</option>
                                </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="button2_target" class="form-label">Ouverture</label>
                                    <select class="form-select" id="button2_target" name="button2_target">
                                        <option value="_self" {{ old('button2_target', $banner->button2_target ?? '_self') == '_self' ? 'selected' : '' }}>Même onglet</option>
                                        <option value="_blank" {{ old('button2_target', $banner->button2_target) == '_blank' ? 'selected' : '' }}>Nouvel onglet</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-form-card">
                        <h5><i class="fas fa-sliders-h me-2"></i>Paramètres avancés</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label fw-bold">Ordre d'affichage</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="sort_order" 
                                       name="sort_order" 
                                       value="{{ old('sort_order', $banner->sort_order) }}" 
                                       min="0">
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Plus le nombre est petit, plus la bannière apparaît en premier
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold d-block">Statut</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Bannière active</strong>
                                        <small class="d-block text-muted">La bannière sera visible sur le site</small>
                                    </label>
                                </div>
                            </div>
                                </div>
                            </div>
                        </div>

                    <div class="admin-form-card">
                        <h5><i class="fas fa-desktop me-2"></i>Prévisualisation</h5>
                        <div class="row g-4 align-items-center">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Image principale (Desktop)</h6>
                                <div class="preview-image-container">
                                    <img src="{{ $banner->image_url ?: 'https://via.placeholder.com/600x300?text=Banner' }}" alt="Preview Desktop" class="img-fluid rounded">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Image mobile (16:9)</h6>
                                <div class="preview-image-container">
                                    <img src="{{ $banner->mobile_image_url ?: 'https://via.placeholder.com/320x480?text=Banner+Mobile' }}" alt="Preview Mobile" class="img-fluid rounded">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin-panel__footer d-flex justify-content-between flex-wrap gap-2">
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                    <div class="d-flex gap-2 flex-wrap">
                        @if($banner->preview_url ?? false)
                            <a href="{{ $banner->preview_url }}" class="btn btn-outline-primary" target="_blank" rel="noopener">
                                <i class="fas fa-eye me-2"></i>Prévisualiser
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<style>
.card {
    border-radius: 15px;
    overflow: hidden;
}

.card-header .text-description {
    color: rgba(255, 255, 255, 0.85) !important;
}
.bg-gradient-primary {
    background: linear-gradient(135deg, #003366 0%, #004080 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: #000 !important;
}
.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    transition: all 0.3s ease;
    min-height: 200px;
}

.upload-zone:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

.upload-placeholder {
    cursor: pointer;
}

.upload-preview {
    padding: 1rem;
}

.upload-preview img {
    max-height: 300px;
    object-fit: contain;
    width: 100%;
}

.current-image {
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 10px;
}

.current-image img {
    border: 2px solid #28a745;
}

.card {
    border: none;
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
}

/* Mobile responsive */
@media (max-width: 768px) {
    /* Boutons de navigation compacts */
    .btn-outline-secondary.btn-sm,
    .btn-outline-light.btn-sm {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }
    
    .card-header {
        padding: 0.75rem;
    }
    
    .card-header h4 {
        font-size: 1rem;
    }
    
    .upload-zone {
        min-height: 150px;
    }
    
    .upload-placeholder i {
        font-size: 2rem !important;
    }
    
    .card-header h5 {
        font-size: 1rem;
    }
    
    .current-image img {
        max-height: 150px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Configuration
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB en bytes

// Validation et preview pour image principale
document.getElementById('image').addEventListener('change', function(e) {
    handleImageUpload(e.target, 'imageUploadZone', 'imageError');
});

// Validation et preview pour image mobile
document.getElementById('mobile_image').addEventListener('change', function(e) {
    handleImageUpload(e.target, 'mobileImageUploadZone', 'mobileImageError');
});

function handleImageUpload(input, zoneId, errorId) {
    const zone = document.getElementById(zoneId);
    const errorDiv = document.getElementById(errorId);
    const file = input.files[0];
    
    // Reset error
    errorDiv.textContent = '';
    errorDiv.classList.remove('d-block');
    
    if (!file) return;
    
    // Validation du type
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        showError(errorId, '❌ Format invalide. Utilisez JPG, PNG ou WEBP.');
        input.value = '';
        return;
    }
    
    // Validation de la taille
    if (file.size > MAX_FILE_SIZE) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        showError(errorId, `❌ Fichier trop volumineux (${sizeMB}MB). Maximum : 10MB.`);
        input.value = '';
        return;
    }
    
    // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
        const placeholder = zone.querySelector('.upload-placeholder');
        const preview = zone.querySelector('.upload-preview');
        
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
        
        preview.querySelector('img').src = e.target.result;
        preview.querySelector('.file-name').textContent = file.name;
        preview.querySelector('.file-size').textContent = formatFileSize(file.size);
        
        zone.style.borderColor = '#28a745';
    };
        reader.readAsDataURL(file);
    }

function clearImage(inputId) {
    const input = document.getElementById(inputId);
    const zone = input.closest('.upload-zone');
    
    input.value = '';
    
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    
    placeholder.classList.remove('d-none');
    preview.classList.add('d-none');
    preview.querySelector('img').src = '';
    
    zone.style.borderColor = '#dee2e6';
}

function showError(errorId, message) {
    const errorDiv = document.getElementById(errorId);
    errorDiv.textContent = message;
    errorDiv.classList.add('d-block');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Validation du formulaire avant soumission
document.getElementById('bannerForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    
    // Désactiver le bouton pour éviter les double-clics
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement en cours...';
});
</script>
@endpush
