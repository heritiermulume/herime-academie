@extends('layouts.app')

@section('title', 'Inscription - Herime Academie')

@section('content')
<!-- Page Header Section -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1>Inscription</h1>
                <p class="lead">Créez votre compte et commencez à apprendre</p>
            </div>
        </div>
    </div>
</section>

<!-- Page Content Section -->
<section class="page-content-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold">
                                <i class="fas fa-user me-2"></i>Nom complet
                            </label>
                            <input id="name" 
                                   type="text" 
                                   class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required 
                                   autofocus 
                                   autocomplete="name"
                                   placeholder="Votre nom complet">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">
                                <i class="fas fa-envelope me-2"></i>Adresse email
                            </label>
                            <input id="email" 
                                   type="email" 
                                   class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autocomplete="username"
                                   placeholder="votre@email.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="mb-4">
                            <label for="phone" class="form-label fw-bold">
                                <i class="fas fa-phone me-2"></i>Téléphone (optionnel)
                            </label>
                            <input id="phone" 
                                   type="tel" 
                                   class="form-control form-control-lg @error('phone') is-invalid @enderror" 
                                   name="phone" 
                                   value="{{ old('phone') }}" 
                                   autocomplete="tel"
                                   placeholder="+243 XXX XXX XXX">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Role Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user-tag me-2"></i>Type de compte
                            </label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card h-100 border-2 @if(old('role') == 'student' || !old('role')) border-primary @else border-light @endif" 
                                         onclick="selectRole('student')" style="cursor: pointer;">
                                        <div class="card-body text-center p-3">
                                            <i class="fas fa-user-graduate fa-2x text-primary mb-2"></i>
                                            <h6 class="fw-bold mb-1">Étudiant</h6>
                                            <small class="text-muted">Apprenez et suivez des cours</small>
                                            <input type="radio" name="role" value="student" 
                                                   @if(old('role') == 'student' || !old('role')) checked @endif 
                                                   style="display: none;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border-2 @if(old('role') == 'instructor') border-primary @else border-light @endif" 
                                         onclick="selectRole('instructor')" style="cursor: pointer;">
                                        <div class="card-body text-center p-3">
                                            <i class="fas fa-chalkboard-teacher fa-2x text-warning mb-2"></i>
                                            <h6 class="fw-bold mb-1">Formateur</h6>
                                            <small class="text-muted">Créez et enseignez des cours</small>
                                            <input type="radio" name="role" value="instructor" 
                                                   @if(old('role') == 'instructor') checked @endif 
                                                   style="display: none;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @error('role')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">
                                <i class="fas fa-lock me-2"></i>Mot de passe
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
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-bold">
                                <i class="fas fa-lock me-2"></i>Confirmer le mot de passe
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

                        <!-- Terms and Conditions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input @error('terms') is-invalid @enderror" 
                                       type="checkbox" 
                                       name="terms" 
                                       id="terms" 
                                       required>
                                <label class="form-check-label" for="terms">
                                    J'accepte les <a href="{{ route('legal.terms') }}" target="_blank" class="text-primary">conditions générales de vente</a> 
                                    et la <a href="{{ route('legal.privacy') }}" target="_blank" class="text-primary">politique de confidentialité</a>
                                </label>
                                @error('terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Newsletter -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="newsletter" 
                                       id="newsletter" 
                                       value="1">
                                <label class="form-check-label" for="newsletter">
                                    Je souhaite recevoir la newsletter avec les nouveaux cours et actualités
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Créer mon compte
                            </button>
                        </div>

                        <!-- Login Link -->
                        <div class="text-center">
                            <p class="text-muted mb-0">Déjà un compte ?</p>
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg mt-2">
                                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                            </a>
                        </div>
                    </form>
                </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function selectRole(role) {
    // Remove border-primary from all cards
    document.querySelectorAll('.card').forEach(card => {
        card.classList.remove('border-primary');
        card.classList.add('border-light');
    });
    
    // Add border-primary to selected card
    event.currentTarget.classList.remove('border-light');
    event.currentTarget.classList.add('border-primary');
    
    // Check the radio button
    const radio = event.currentTarget.querySelector('input[type="radio"]');
    radio.checked = true;
}

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
    
    // You can add a visual indicator here if needed
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
</script>
@endpush

@push('styles')
<style>
/* Styles spécifiques pour la page d'inscription si nécessaire */
/* Les styles globaux sont déjà appliqués via app.blade.php */

.border-primary {
    border-color: var(--primary-color) !important;
    border-width: 2px !important;
}

.border-light {
    border-color: #e9ecef !important;
}

.role-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.role-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,51,102,0.1) !important;
}
</style>
@endpush