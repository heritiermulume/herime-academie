@extends('layouts.app')

@section('title', 'Modifier l\'utilisateur')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Administration</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">Utilisateurs</a></li>
                        <li class="breadcrumb-item active">Modifier</li>
                    </ol>
                </div>
                <h4 class="page-title">Modifier l'utilisateur</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Section Avatar -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Photo de profil</label>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <img src="{{ $user->avatar ? Storage::url($user->avatar) : asset('images/default-avatar.svg') }}" 
                                             alt="Avatar" class="img-fluid rounded-circle" 
                                             style="width: 80px; height: 80px; object-fit: cover;" id="avatar-preview">
                                    </div>
                                    <div>
                                        <input type="file" class="form-control @error('avatar') is-invalid @enderror" 
                                               id="avatar" name="avatar" accept="image/*" onchange="validateAndPreviewImage(this)">
                                        <small class="text-muted">Formats acceptés: JPG, PNG, GIF. Taille max: 2MB</small>
                                        <div id="file-error" class="text-danger mt-1" style="display: none;"></div>
                                        @error('avatar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
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
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label">Date de naissance</label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}">
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">Genre</label>
                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                    <option value="">Sélectionner</option>
                                    <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Homme</option>
                                    <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Femme</option>
                                    <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Autre</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="bio" class="form-label">Biographie</label>
                                <textarea class="form-control @error('bio') is-invalid @enderror" 
                                          id="bio" name="bio" rows="3">{{ old('bio', $user->bio) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Compte actif
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_verified" name="is_verified" value="1" 
                                           {{ old('is_verified', $user->is_verified) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_verified">
                                        Email vérifié
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="website" class="form-label">Site web</label>
                                <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                       id="website" name="website" value="{{ old('website', $user->website) }}">
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="linkedin" class="form-label">LinkedIn</label>
                                <input type="url" class="form-control @error('linkedin') is-invalid @enderror" 
                                       id="linkedin" name="linkedin" value="{{ old('linkedin', $user->linkedin) }}">
                                @error('linkedin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="twitter" class="form-label">Twitter</label>
                                <input type="url" class="form-control @error('twitter') is-invalid @enderror" 
                                       id="twitter" name="twitter" value="{{ old('twitter', $user->twitter) }}">
                                @error('twitter')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="youtube" class="form-label">YouTube</label>
                                <input type="url" class="form-control @error('youtube') is-invalid @enderror" 
                                       id="youtube" name="youtube" value="{{ old('youtube', $user->youtube) }}">
                                @error('youtube')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validateAndPreviewImage(input) {
    const fileError = document.getElementById('file-error');
    const submitButton = document.querySelector('button[type="submit"]');
    
    // Masquer les erreurs précédentes
    fileError.style.display = 'none';
    input.classList.remove('is-invalid');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB en bytes
        
        // Vérifier la taille du fichier
        if (file.size > maxSize) {
            fileError.textContent = 'Le fichier est trop volumineux. Taille maximum autorisée: 2MB';
            fileError.style.display = 'block';
            input.classList.add('is-invalid');
            submitButton.disabled = true;
            return;
        }
        
        // Vérifier le type de fichier
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            fileError.textContent = 'Format de fichier non supporté. Utilisez JPG, PNG ou GIF.';
            fileError.style.display = 'block';
            input.classList.add('is-invalid');
            submitButton.disabled = true;
            return;
        }
        
        // Si tout est OK, prévisualiser l'image
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
            submitButton.disabled = false;
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
