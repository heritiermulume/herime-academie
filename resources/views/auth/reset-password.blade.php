@extends('layouts.app')

@section('title', 'Réinitialiser le mot de passe - Herime Academie')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <div class="mb-3">
                        <img src="{{ asset('images/logo-herime-academie-blanc.png') }}" alt="Herime Academie" style="height: 50px; max-width: 200px; object-fit: contain;">
                    </div>
                    <h3 class="mb-0 fw-bold">
                        Nouveau mot de passe
                    </h3>
                    <p class="mb-0 mt-2">Définissez votre nouveau mot de passe</p>
                </div>
                <div class="card-body p-5">
                    <form method="POST" action="{{ route('password.store') }}">
                        @csrf

                        <!-- Password Reset Token -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <!-- Email Address -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">
                                <i class="fas fa-envelope me-2 text-primary"></i>Adresse email
                            </label>
                            <input id="email" 
                                   type="email" 
                                   class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                   name="email" 
                                   value="{{ old('email', $request->email) }}" 
                                   required 
                                   autofocus 
                                   autocomplete="username"
                                   readonly>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">
                                <i class="fas fa-lock me-2 text-primary"></i>Nouveau mot de passe
                            </label>
                            <div class="input-group">
                                <input id="password" 
                                       type="password" 
                                       class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                       name="password" 
                                       required 
                                       autocomplete="new-password"
                                       placeholder="Minimum 8 caractères">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="toggleIcon1"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Le mot de passe doit contenir au moins 8 caractères
                                </small>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-bold">
                                <i class="fas fa-lock me-2 text-primary"></i>Confirmer le nouveau mot de passe
                            </label>
                            <div class="input-group">
                                <input id="password_confirmation" 
                                       type="password" 
                                       class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror" 
                                       name="password_confirmation" 
                                       required 
                                       autocomplete="new-password"
                                       placeholder="Répétez votre mot de passe">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="fas fa-eye" id="toggleIcon2"></i>
                                </button>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password Strength Indicator -->
                        <div class="mb-4">
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <small class="text-muted" id="strengthText">Force du mot de passe</small>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Réinitialiser le mot de passe
                            </button>
                        </div>

                        <!-- Back to Login -->
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Retour à la connexion
                            </a>
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
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(fieldId === 'password' ? 'toggleIcon1' : 'toggleIcon2');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strength = getPasswordStrength(password);
    updateStrengthIndicator(strength);
});

function getPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    return strength;
}

function updateStrengthIndicator(strength) {
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    
    const percentage = (strength / 5) * 100;
    strengthFill.style.width = percentage + '%';
    
    if (strength <= 1) {
        strengthFill.className = 'strength-fill weak';
        strengthText.textContent = 'Très faible';
        strengthText.className = 'text-danger';
    } else if (strength <= 2) {
        strengthFill.className = 'strength-fill fair';
        strengthText.textContent = 'Faible';
        strengthText.className = 'text-warning';
    } else if (strength <= 3) {
        strengthFill.className = 'strength-fill good';
        strengthText.textContent = 'Moyen';
        strengthText.className = 'text-info';
    } else if (strength <= 4) {
        strengthFill.className = 'strength-fill strong';
        strengthText.textContent = 'Fort';
        strengthText.className = 'text-success';
    } else {
        strengthFill.className = 'strength-fill very-strong';
        strengthText.textContent = 'Très fort';
        strengthText.className = 'text-success';
    }
}
</script>
@endpush

@push('styles')
<style>
.card {
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
}

.btn-primary {
    background-color: #003366;
    border-color: #003366;
    border-radius: 10px;
}

.btn-primary:hover {
    background-color: #004080;
    border-color: #004080;
}

.form-control:focus {
    border-color: #003366;
    box-shadow: 0 0 0 0.2rem rgba(0, 51, 102, 0.25);
}

.input-group .btn {
    border-radius: 0 0.375rem 0.375rem 0;
}

.password-strength {
    margin-top: 10px;
}

.strength-bar {
    height: 4px;
    background-color: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 5px;
}

.strength-fill {
    height: 100%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-fill.weak {
    background-color: #dc3545;
    width: 20%;
}

.strength-fill.fair {
    background-color: #fd7e14;
    width: 40%;
}

.strength-fill.good {
    background-color: #ffc107;
    width: 60%;
}

.strength-fill.strong {
    background-color: #20c997;
    width: 80%;
}

.strength-fill.very-strong {
    background-color: #198754;
    width: 100%;
}
</style>
@endpush