@extends('layouts.app')

@section('title', 'Mot de passe oublié - Herime Academie')

@section('content')
<div class="auth-page">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <div class="mb-3">
                        <img src="{{ asset('images/logo-herime-academie-blanc.png') }}" alt="Herime Academie" style="height: 50px; max-width: 200px; object-fit: contain;">
                    </div>
                    <h3 class="mb-0 fw-bold">
                        Mot de passe oublié
                    </h3>
                    <p class="mb-0 mt-2">Entrez votre email pour recevoir un lien de réinitialisation</p>
                </div>
                <div class="card-body p-5">
                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

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
                                   autofocus 
                                   autocomplete="username"
                                   placeholder="votre@email.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer le lien de réinitialisation
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
</div>
@endsection

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

.btn-outline-info {
    color: #17a2b8;
    border: 2px solid #17a2b8;
    border-radius: 12px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-info:hover {
    background: #17a2b8;
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
}
</style>
@endpush