@extends('layouts.app')

@section('title', 'Profil - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary" style="background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                    <h4 class="mb-0 text-white">
                        <i class="fas fa-user me-2 text-white"></i>Mon Profil
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Informations personnelles -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header" style="background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                                    <h5 class="mb-0 text-white">
                                        <i class="fas fa-user-edit me-2 text-white"></i>Informations personnelles
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="name" class="form-label">Nom complet *</label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                       id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">Email *</label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                       id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="phone" class="form-label">Téléphone</label>
                                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                                       id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="date_of_birth" class="form-label">Date de naissance</label>
                                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                                       id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', auth()->user()->date_of_birth ? auth()->user()->date_of_birth->format('Y-m-d') : '') }}">
                                                @error('date_of_birth')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="gender" class="form-label">Genre</label>
                                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                                    <option value="">Sélectionner</option>
                                                    <option value="male" {{ old('gender', auth()->user()->gender) == 'male' ? 'selected' : '' }}>Homme</option>
                                                    <option value="female" {{ old('gender', auth()->user()->gender) == 'female' ? 'selected' : '' }}>Femme</option>
                                                    <option value="other" {{ old('gender', auth()->user()->gender) == 'other' ? 'selected' : '' }}>Autre</option>
                                                </select>
                                                @error('gender')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="role" class="form-label">Rôle</label>
                                                <input type="text" class="form-control" value="{{ ucfirst(auth()->user()->role) }}" readonly>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="bio" class="form-label">Biographie</label>
                                            <textarea class="form-control @error('bio') is-invalid @enderror" 
                                                      id="bio" name="bio" rows="4" 
                                                      placeholder="Parlez-nous de vous...">{{ old('bio', auth()->user()->bio) }}</textarea>
                                            @error('bio')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="website" class="form-label">Site web</label>
                                                <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                                       id="website" name="website" value="{{ old('website', auth()->user()->website) }}" 
                                                       placeholder="https://votre-site.com">
                                                @error('website')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="linkedin" class="form-label">LinkedIn</label>
                                                <input type="url" class="form-control @error('linkedin') is-invalid @enderror" 
                                                       id="linkedin" name="linkedin" value="{{ old('linkedin', auth()->user()->linkedin) }}" 
                                                       placeholder="https://linkedin.com/in/votre-profil">
                                                @error('linkedin')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="twitter" class="form-label">Twitter</label>
                                                <input type="url" class="form-control @error('twitter') is-invalid @enderror" 
                                                       id="twitter" name="twitter" value="{{ old('twitter', auth()->user()->twitter) }}" 
                                                       placeholder="https://twitter.com/votre-compte">
                                                @error('twitter')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="youtube" class="form-label">YouTube</label>
                                                <input type="url" class="form-control @error('youtube') is-invalid @enderror" 
                                                       id="youtube" name="youtube" value="{{ old('youtube', auth()->user()->youtube) }}" 
                                                       placeholder="https://youtube.com/@votre-chaine">
                                                @error('youtube')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Mettre à jour
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Photo de profil et informations -->
                        <div class="col-md-4">
                            <!-- Photo de profil -->
                            <div class="card mb-4">
                                <div class="card-header" style="background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                                    <h5 class="mb-0 text-white">
                                        <i class="fas fa-camera me-2 text-white"></i>Photo de profil
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        @if(auth()->user()->avatar)
                                            <img src="{{ Storage::url(auth()->user()->avatar) }}" 
                                                 alt="Photo de profil" class="rounded-circle" 
                                                 style="width: 150px; height: 150px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto" 
                                                 style="width: 150px; height: 150px;">
                                                <i class="fas fa-user text-white" style="font-size: 4rem;"></i>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <input type="file" class="form-control @error('avatar') is-invalid @enderror" 
                                                   id="avatar" name="avatar" accept="image/*">
                                            @error('avatar')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-upload me-1"></i>Changer
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Informations du compte -->
                            <div class="card">
                                <div class="card-header" style="background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                                    <h5 class="mb-0 text-white">
                                        <i class="fas fa-info-circle me-2 text-white"></i>Informations du compte
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Membre depuis :</strong><br>
                                        <span class="text-muted">{{ auth()->user()->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Dernière connexion :</strong><br>
                                        <span class="text-muted">
                                            @if(auth()->user()->last_login_at)
                                                {{ auth()->user()->last_login_at->format('d/m/Y à H:i') }}
                                            @else
                                                Jamais
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Statut :</strong><br>
                                        @if(auth()->user()->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Vérification :</strong><br>
                                        @if(auth()->user()->is_verified)
                                            <span class="badge bg-success">Vérifié</span>
                                        @else
                                            <span class="badge bg-warning">Non vérifié</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Changement de mot de passe -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header" style="background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                                    <h5 class="mb-0 text-white">
                                        <i class="fas fa-lock me-2 text-white"></i>Changer le mot de passe
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('profile.password') }}">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="current_password" class="form-label">Mot de passe actuel *</label>
                                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                                       id="current_password" name="current_password" required>
                                                @error('current_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label for="password" class="form-label">Nouveau mot de passe *</label>
                                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                                       id="password" name="password" required>
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label for="password_confirmation" class="form-label">Confirmer le mot de passe *</label>
                                                <input type="password" class="form-control" 
                                                       id="password_confirmation" name="password_confirmation" required>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-key me-2"></i>Changer le mot de passe
                                            </button>
                                        </div>
                                    </form>
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

@push('styles')
<style>
.card-header {
    background: linear-gradient(135deg, #003366 0%, #004080 100%) !important;
    border-radius: 12px 12px 0 0;
}

.card-header h5,
.card-header h4 {
    color: white !important;
}

.card-header i {
    color: white !important;
}

.form-label {
    font-weight: 600;
    color: #003366;
}

.btn-primary {
    background: linear-gradient(135deg, #003366 0%, #004080 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #004080 0%, #0052a3 100%);
}

.btn-warning {
    background: linear-gradient(135deg, #ffcc33 0%, #ffd633 100%);
    border: none;
    color: #003366;
}

.btn-warning:hover {
    background: linear-gradient(135deg, #ffd633 0%, #ffe066 100%);
    color: #003366;
}

.badge {
    font-size: 0.8em;
}

.rounded-circle {
    border: 3px solid #e9ecef;
}

.text-muted {
    font-size: 0.9em;
}

/* Responsive pour mobile */
@media (max-width: 768px) {
    .card {
        margin-bottom: 20px !important;
    }
    
    .card-body {
        padding: 20px !important;
    }
    
    .row {
        margin-left: 0;
        margin-right: 0;
    }
    
    .col-md-8, .col-md-4 {
        margin-bottom: 20px;
    }
    
    .mb-4 {
        margin-bottom: 24px !important;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-left: 10px;
        padding-right: 10px;
    }
    
    .card-body {
        padding: 15px !important;
    }
    
    .card-header h5 {
        font-size: 1rem !important;
    }
    
    .card-header h4 {
        font-size: 1.1rem !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Aperçu de l'image avant upload
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.querySelector('.rounded-circle img');
            if (img) {
                img.src = e.target.result;
            } else {
                // Créer une nouvelle image si elle n'existe pas
                const container = document.querySelector('.rounded-circle');
                container.innerHTML = `<img src="${e.target.result}" alt="Photo de profil" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">`;
            }
        };
        reader.readAsDataURL(file);
    }
});

// Validation du mot de passe
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const confirmPassword = document.getElementById('password_confirmation');
    
    if (password !== confirmPassword.value) {
        confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas');
    } else {
        confirmPassword.setCustomValidity('');
    }
});

document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Les mots de passe ne correspondent pas');
    } else {
        this.setCustomValidity('');
    }
});
</script>
@endpush
