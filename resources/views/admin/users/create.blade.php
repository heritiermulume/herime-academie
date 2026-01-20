@extends('layouts.admin')

@section('title', 'Créer un utilisateur - Admin')

@section('admin-content')
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
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-light btn-sm" title="Liste des utilisateurs">
                                <i class="fas fa-th-list"></i>
                            </a>
                            <div>
                                <h4 class="mb-1">
                                    <i class="fas fa-user-plus me-2"></i>Créer un utilisateur
                                </h4>
                                <p class="mb-0 text-description small">Ajouter un nouvel utilisateur à la plateforme</p>
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

            <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Informations personnelles -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations personnelles</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold">
                                    Nom complet <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="Ex: Jean Dupont" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" 
                                       placeholder="Ex: jean@example.com" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-bold">Téléphone</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}"
                                       placeholder="Ex: +243 900 000 000">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="avatar" class="form-label fw-bold">Photo de profil</label>
                                <div class="upload-zone" id="avatarUploadZone">
                                    <input type="file" 
                                           class="form-control d-none @error('avatar') is-invalid @enderror" 
                                           id="avatar" 
                                           name="avatar" 
                                           accept="image/jpeg,image/png,image/jpg,image/webp"
                                           onchange="handleAvatarUpload(this)">
                                    <div class="upload-placeholder text-center p-4" onclick="document.getElementById('avatar').click()">
                                        <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
                                        <p class="mb-2"><strong>Cliquez pour sélectionner une photo</strong></p>
                                        <p class="text-muted small mb-0">Format : JPG, PNG, WEBP | Max : 5MB</p>
                                        <p class="text-muted small">Recommandé : 400x400px (carré)</p>
                                    </div>
                                    <div class="upload-preview d-none">
                                        <img src="" alt="Preview" class="img-fluid rounded-circle mx-auto d-block" style="max-width: 200px; max-height: 200px; border: 3px solid #28a745;">
                                        <div class="upload-info mt-2 text-center">
                                            <span class="badge bg-primary file-name"></span>
                                            <span class="badge bg-info file-size"></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger mt-2 d-block mx-auto" onclick="clearAvatar()">
                                            <i class="fas fa-trash me-1"></i>Supprimer
                                        </button>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" id="avatarError"></div>
                                @error('avatar')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sécurité -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Sécurité & Authentification</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-bold">
                                    Mot de passe <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                <small class="form-text text-muted">Minimum 8 caractères</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-bold">
                                    Confirmer le mot de passe <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rôle & Paramètres -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-info text-white">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Rôle & Paramètres</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="role" class="form-label fw-bold">
                                    Rôle <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('role') is-invalid @enderror" 
                                        id="role" name="role" required>
                                    <option value="">Sélectionner un rôle</option>
                                    <option value="customer" {{ old('role') == 'customer' ? 'selected' : '' }}>Client</option>
                                    <option value="provider" {{ old('role') == 'provider' ? 'selected' : '' }}>Prestataire</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                    <option value="affiliate" {{ old('role') == 'affiliate' ? 'selected' : '' }}>Affilié</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Statuts</label>
                                <div class="d-flex gap-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" 
                                               name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Utilisateur actif
                                        </label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_verified" 
                                               name="is_verified" value="1" {{ old('is_verified', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_verified">
                                            Email vérifié
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Créer l'utilisateur
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Design moderne pour formulaire de création */
.card {
    border-radius: 15px;
    overflow: hidden;
}

.card-header.text-white {
    background: linear-gradient(135deg, #003366 0%, #004080 100%) !important;
    border: none;
    padding: 1.5rem;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #003366 0%, #004080 100%) !important;
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
}

.form-control:focus, .form-select:focus {
    border-color: #003366;
    box-shadow: 0 0 0 0.2rem rgba(0, 51, 102, 0.25);
}

.form-check-input:checked {
    background-color: #003366;
    border-color: #003366;
}

.form-label.fw-bold {
    color: #003366;
}

.card-body {
    padding: 1.5rem;
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-2px);
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
    color: #003366 !important;
}

.upload-placeholder i {
    transition: transform 0.2s ease;
}

.upload-preview {
    padding: 1.5rem;
}

.upload-preview img {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.upload-preview img:hover {
    transform: scale(1.05);
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

/* Mobile Responsive */
@media (max-width: 768px) {
    .card-header.text-white {
        padding: 1rem;
    }
    
    .card-header h4 {
        font-size: 1.1rem;
    }
    
    .card-header .small {
        font-size: 0.8rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn-outline-light.btn-sm {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Constantes de validation
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const VALID_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

// Gestion de l'upload d'avatar
function handleAvatarUpload(input) {
    const zone = document.getElementById('avatarUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const errorDiv = document.getElementById('avatarError');
    
    // Réinitialiser les erreurs
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validation du type
        if (!VALID_IMAGE_TYPES.includes(file.type)) {
            showAvatarError('❌ Format invalide. Utilisez JPG, PNG ou WEBP.');
            input.value = '';
            return;
        }
        
        // Validation de la taille
        if (file.size > MAX_FILE_SIZE) {
            showAvatarError('❌ Le fichier est trop volumineux. Maximum 5MB.');
            input.value = '';
            return;
        }
        
        // Afficher la preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = preview.querySelector('img');
            img.src = e.target.result;
            
            // Afficher les informations du fichier
            preview.querySelector('.file-name').textContent = file.name;
            preview.querySelector('.file-size').textContent = formatFileSize(file.size);
            
            // Basculer l'affichage
            placeholder.classList.add('d-none');
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
}

// Effacer la sélection
function clearAvatar() {
    const zone = document.getElementById('avatarUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const input = document.getElementById('avatar');
    const errorDiv = document.getElementById('avatarError');
    
    // Réinitialiser
    input.value = '';
    preview.querySelector('img').src = '';
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    
    // Basculer l'affichage
    preview.classList.add('d-none');
    placeholder.classList.remove('d-none');
}

// Afficher une erreur
function showAvatarError(message) {
    const errorDiv = document.getElementById('avatarError');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

// Formater la taille du fichier
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}
</script>
@endpush
