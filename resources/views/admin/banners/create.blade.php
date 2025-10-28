@extends('layouts.app')

@section('title', 'Créer une bannière - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-plus me-2"></i>Créer une bannière
                        </h4>
                        <a href="{{ route('admin.banners.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- Titre -->
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label">Titre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sous-titre -->
                            <div class="col-md-12 mb-3">
                                <label for="subtitle" class="form-label">Sous-titre</label>
                                <textarea class="form-control @error('subtitle') is-invalid @enderror" 
                                          id="subtitle" name="subtitle" rows="2">{{ old('subtitle') }}</textarea>
                                @error('subtitle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Image principale -->
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">Image principale <span class="text-danger">*</span></label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                       id="image" name="image" accept="image/*" required>
                                <small class="form-text text-muted">Format recommandé: 1920x1080px (Full HD)</small>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="imagePreview" class="mt-2"></div>
                            </div>

                            <!-- Image mobile -->
                            <div class="col-md-6 mb-3">
                                <label for="mobile_image" class="form-label">Image mobile (16:9)</label>
                                <input type="file" class="form-control @error('mobile_image') is-invalid @enderror" 
                                       id="mobile_image" name="mobile_image" accept="image/*">
                                <small class="form-text text-muted">Format recommandé: 1920x1080px (16:9)</small>
                                @error('mobile_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="mobileImagePreview" class="mt-2"></div>
                            </div>

                            <!-- Bouton 1 -->
                            <div class="col-md-12">
                                <h5 class="mt-3 mb-3">Bouton d'action 1</h5>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="button1_text" class="form-label">Texte du bouton</label>
                                <input type="text" class="form-control @error('button1_text') is-invalid @enderror" 
                                       id="button1_text" name="button1_text" value="{{ old('button1_text') }}">
                                @error('button1_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="button1_url" class="form-label">URL</label>
                                <input type="text" class="form-control @error('button1_url') is-invalid @enderror" 
                                       id="button1_url" name="button1_url" value="{{ old('button1_url') }}">
                                @error('button1_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="button1_style" class="form-label">Style</label>
                                <select class="form-select @error('button1_style') is-invalid @enderror" 
                                        id="button1_style" name="button1_style">
                                    <option value="primary" {{ old('button1_style') == 'primary' ? 'selected' : '' }}>Primary</option>
                                    <option value="warning" {{ old('button1_style') == 'warning' ? 'selected' : '' }}>Warning</option>
                                    <option value="success" {{ old('button1_style') == 'success' ? 'selected' : '' }}>Success</option>
                                    <option value="danger" {{ old('button1_style') == 'danger' ? 'selected' : '' }}>Danger</option>
                                    <option value="info" {{ old('button1_style') == 'info' ? 'selected' : '' }}>Info</option>
                                </select>
                                @error('button1_style')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Bouton 2 -->
                            <div class="col-md-12">
                                <h5 class="mt-3 mb-3">Bouton d'action 2</h5>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="button2_text" class="form-label">Texte du bouton</label>
                                <input type="text" class="form-control @error('button2_text') is-invalid @enderror" 
                                       id="button2_text" name="button2_text" value="{{ old('button2_text') }}">
                                @error('button2_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="button2_url" class="form-label">URL</label>
                                <input type="text" class="form-control @error('button2_url') is-invalid @enderror" 
                                       id="button2_url" name="button2_url" value="{{ old('button2_url') }}">
                                @error('button2_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="button2_style" class="form-label">Style</label>
                                <select class="form-select @error('button2_style') is-invalid @enderror" 
                                        id="button2_style" name="button2_style">
                                    <option value="outline-light" {{ old('button2_style') == 'outline-light' ? 'selected' : '' }}>Outline Light</option>
                                    <option value="outline-primary" {{ old('button2_style') == 'outline-primary' ? 'selected' : '' }}>Outline Primary</option>
                                    <option value="secondary" {{ old('button2_style') == 'secondary' ? 'selected' : '' }}>Secondary</option>
                                    <option value="light" {{ old('button2_style') == 'light' ? 'selected' : '' }}>Light</option>
                                </select>
                                @error('button2_style')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Ordre et statut -->
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Ordre d'affichage</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                <small class="form-text text-muted">Plus le nombre est petit, plus la bannière sera affichée en premier</small>
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="is_active" class="form-label d-block">Statut</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" 
                                           name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Bannière active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Créer la bannière
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Preview image principale
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').innerHTML = 
                `<img src="${e.target.result}" class="img-thumbnail" style="max-width: 300px;">`;
        }
        reader.readAsDataURL(file);
    }
});

// Preview image mobile
document.getElementById('mobile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('mobileImagePreview').innerHTML = 
                `<img src="${e.target.result}" class="img-thumbnail" style="max-width: 300px;">`;
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush

