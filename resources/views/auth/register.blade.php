@extends('layouts.app')

@section('title', 'Inscription - Herime Academie')

@section('content')
<div class="auth-page">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <div class="mb-3">
                        <img src="{{ asset('images/logo-herime-academie-blanc.png') }}" alt="Herime Academie" style="height: 50px; max-width: 200px; object-fit: contain;">
                    </div>
                    <h3 class="mb-0 fw-bold">
                        Rejoignez Herime Academie
                    </h3>
                    <p class="mb-0 mt-2">Créez votre compte et commencez à apprendre</p>
                </div>
                <div class="card-body p-5">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold">
                                <i class="fas fa-user me-2 text-primary"></i>Nom complet
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
                                <i class="fas fa-envelope me-2 text-primary"></i>Adresse email
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
                                <i class="fas fa-phone me-2 text-primary"></i>Téléphone (optionnel)
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
                                <i class="fas fa-user-tag me-2 text-primary"></i>Type de compte
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
                                <i class="fas fa-lock me-2 text-primary"></i>Mot de passe
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
                                <i class="fas fa-lock me-2 text-primary"></i>Confirmer le mot de passe
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
</div>
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
.auth-page {
    background: linear-gradient(135deg, #f7f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
    padding: 40px 0;
}

.card {
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 15px 50px rgba(0,0,0,0.15) !important;
}

.card-header {
    background: linear-gradient(135deg, #003366 0%, #004080 100%) !important;
    border-radius: 20px 20px 0 0 !important;
    padding: 40px 20px !important;
}

.card-body {
    padding: 40px 30px !important;
}

.btn-primary {
    background: linear-gradient(135deg, #003366 0%, #004080 100%);
    border: none;
    border-radius: 12px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #004080 0%, #0050a0 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,51,102,0.3);
}

.btn-outline-primary {
    color: #003366;
    border: 2px solid #003366;
    border-radius: 12px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-primary:hover {
    background: #003366;
    color: white;
    transform: translateY(-2px);
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #ffcc33;
    box-shadow: 0 0 0 0.2rem rgba(255, 204, 51, 0.25);
}

.form-control-lg {
    font-size: 1rem;
}

.input-group .btn {
    border: 2px solid #e9ecef;
    border-left: none;
}

.form-check-input {
    border: 2px solid #e9ecef;
    width: 1.2em;
    height: 1.2em;
    margin-top: 0.2em;
}

.form-check-input:checked {
    background-color: #003366;
    border-color: #003366;
}

.form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(255, 204, 51, 0.25);
}

.text-primary {
    color: #003366 !important;
}

.alert {
    border-radius: 12px;
    border: none;
}

.border-primary {
    border-color: #003366 !important;
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

.text-muted {
    color: #6c757d !important;
}

/* Responsive */
@media (max-width: 768px) {
    .auth-page {
        padding: 20px 10px;
    }
    
    .card-body {
        padding: 30px 20px !important;
    }
    
    .card-header {
        padding: 30px 15px !important;
    }
    
    h3 {
        font-size: 1.3rem;
    }
}

@media (max-width: 480px) {
    .auth-page {
        padding: 15px 8px;
    }
    
    .card-body {
        padding: 25px 15px !important;
    }
    
    .btn-lg {
        padding: 10px 20px;
        font-size: 0.95rem;
    }
    
    .form-control-lg {
        font-size: 0.95rem;
    }
    
    .card-header img {
        height: 40px !important;
    }
}
</style>
@endpush