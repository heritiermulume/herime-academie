@extends('layouts.app')

@section('title', 'Modifier l\'utilisateur - Admin')

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
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-light btn-sm" title="Liste des utilisateurs">
                                <i class="fas fa-th-list"></i>
                            </a>
                            <div>
                                <h4 class="mb-1">
                                    <i class="fas fa-user-edit me-2"></i>Modifier l'utilisateur
                                </h4>
                                <p class="mb-0 text-description small">Mettre à jour les informations de {{ $user->name }}</p>
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

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Informations personnelles -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations personnelles</h5>
                    </div>
                    <div class="card-body">
                        <!-- Avertissement Compte Herime -->
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Gestion via Compte Herime :</strong> Les informations personnelles (nom, email, photo) sont gérées via Compte Herime (compte.herime.com) et seront synchronisées automatiquement lors de la prochaine connexion de l'utilisateur. Seuls le rôle et le statut actif peuvent être modifiés ici.
                        </div>
                        <div class="row g-3">
                            <!-- Avatar actuel (lecture seule) -->
                            <div class="col-12">
                                <label class="form-label fw-bold">
                                    Photo de profil 
                                    <span class="badge bg-info ms-2">Géré par Compte Herime</span>
                                </label>
                                
                                <!-- Avatar actuel -->
                                <div class="current-avatar mb-3 text-center">
                                    <p class="fw-bold mb-2 text-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Avatar actuel (synchronisé depuis Compte Herime) :
                                    </p>
                                    <div style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; margin: 0 auto; aspect-ratio: 1 / 1;">
                                        <img src="{{ $user->avatar_url }}" 
                                             alt="Avatar actuel" 
                                             id="currentAvatar"
                                             style="width: 100%; height: 100%; object-fit: cover; display: block; border: none; box-shadow: none; transform: none;">
                                    </div>
                                    <p class="text-muted small mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        La photo est gérée via <a href="{{ config('services.sso.base_url') }}" target="_blank">compte.herime.com</a>
                                    </p>
                                </div>
                            </div>

                            <!-- Nom (lecture seule) -->
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold">
                                    Nom complet 
                                    <span class="badge bg-info ms-2">Géré par Compte Herime</span>
                                </label>
                                <input type="text" class="form-control bg-light" 
                                       id="name" name="name" value="{{ $user->name }}" 
                                       readonly disabled>
                                <small class="form-text text-muted">
                                    <i class="fas fa-lock me-1"></i>
                                    Modifiable uniquement via Compte Herime
                                </small>
                            </div>
                            
                            <!-- Email (lecture seule) -->
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold">
                                    Email 
                                    <span class="badge bg-info ms-2">Géré par Compte Herime</span>
                                </label>
                                <input type="email" class="form-control bg-light" 
                                       id="email" name="email" value="{{ $user->email }}" 
                                       readonly disabled>
                                <small class="form-text text-muted">
                                    <i class="fas fa-lock me-1"></i>
                                    Modifiable uniquement via Compte Herime
                                </small>
                            </div>
                            
                            <!-- Informations supplémentaires (lecture seule) -->
                            <div class="col-12">
                                <div class="alert alert-secondary">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note :</strong> Les autres informations (téléphone, date de naissance, genre, biographie) sont également gérées via Compte Herime et seront synchronisées lors de la prochaine connexion.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mot de passe (géré par Compte Herime) -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Mot de passe</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Gestion via Compte Herime :</strong> Le mot de passe est géré exclusivement via Compte Herime (compte.herime.com). Les utilisateurs doivent modifier leur mot de passe sur Compte Herime.
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
                                    <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Étudiant</option>
                                    <option value="instructor" {{ old('role', $user->role) == 'instructor' ? 'selected' : '' }}>Formateur</option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                    <option value="affiliate" {{ old('role', $user->role) == 'affiliate' ? 'selected' : '' }}>Affilié</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Statut</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" 
                                           name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Utilisateur actif
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Désactiver pour empêcher l'accès à la plateforme
                                    </small>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-{{ $user->is_verified ? 'success' : 'warning' }} me-2">
                                            <i class="fas fa-{{ $user->is_verified ? 'check' : 'clock' }} me-1"></i>
                                            Email {{ $user->is_verified ? 'vérifié' : 'non vérifié' }}
                                        </span>
                                        <small class="text-muted">
                                            (Géré par Compte Herime)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Réseaux sociaux -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);">
                        <h5 class="mb-0 text-white"><i class="fas fa-share-alt me-2"></i>Réseaux sociaux</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="website" class="form-label fw-bold">
                                    <i class="fas fa-globe me-1"></i>Site web
                                </label>
                                <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                       id="website" name="website" value="{{ old('website', $user->website) }}"
                                       placeholder="https://example.com">
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="linkedin" class="form-label fw-bold">
                                    <i class="fab fa-linkedin me-1"></i>LinkedIn
                                </label>
                                <input type="url" class="form-control @error('linkedin') is-invalid @enderror" 
                                       id="linkedin" name="linkedin" value="{{ old('linkedin', $user->linkedin) }}"
                                       placeholder="https://linkedin.com/in/username">
                                @error('linkedin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="twitter" class="form-label fw-bold">
                                    <i class="fab fa-twitter me-1"></i>Twitter
                                </label>
                                <input type="url" class="form-control @error('twitter') is-invalid @enderror" 
                                       id="twitter" name="twitter" value="{{ old('twitter', $user->twitter) }}"
                                       placeholder="https://twitter.com/username">
                                @error('twitter')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="youtube" class="form-label fw-bold">
                                    <i class="fab fa-youtube me-1"></i>YouTube
                                </label>
                                <input type="url" class="form-control @error('youtube') is-invalid @enderror" 
                                       id="youtube" name="youtube" value="{{ old('youtube', $user->youtube) }}"
                                       placeholder="https://youtube.com/@username">
                                @error('youtube')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Mettre à jour
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
/* Design moderne pour formulaire d'édition */
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

/* Preview d'images */
.current-avatar img {
    transition: transform 0.2s ease;
}

.current-avatar img:hover {
    transform: scale(1.05);
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
    border-color: #17a2b8;
    background-color: #e9ecef;
}

.upload-placeholder {
    cursor: pointer;
    transition: all 0.2s ease;
}

.upload-placeholder:hover {
    background-color: rgba(23, 162, 184, 0.05);
}

.upload-placeholder:hover i {
    transform: scale(1.1);
    color: #138496 !important;
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
