@extends('layouts.app')

@section('title', 'Vérification d\'email - Herime Academie')

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
                        Vérification d'email
                    </h3>
                    <p class="mb-0 mt-2">Veuillez vérifier votre adresse email</p>
                </div>
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                        <h5 class="fw-bold">Email de vérification envoyé !</h5>
                        <p class="text-muted">
                            Nous avons envoyé un lien de vérification à <strong>{{ auth()->user()->email }}</strong>
                        </p>
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            Un nouveau lien de vérification a été envoyé à votre adresse email.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="mb-4">
                        <p class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Avant de continuer, veuillez vérifier votre email et cliquer sur le lien de vérification.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Renvoyer l'email de vérification
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <p class="text-muted small mb-2">Vous n'avez pas reçu l'email ?</p>
                        <ul class="list-unstyled small text-muted">
                            <li><i class="fas fa-check me-1"></i>Vérifiez vos spams</li>
                            <li><i class="fas fa-check me-1"></i>Attendez quelques minutes</li>
                            <li><i class="fas fa-check me-1"></i>Contactez le support</li>
                        </ul>
                    </div>

                    <hr class="my-4">

                    <div class="d-grid gap-2">
                        <a href="{{ route('logout') }}" 
                           class="btn btn-outline-secondary"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i>Se déconnecter
                        </a>
                        
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-center">
                        <i class="fas fa-question-circle me-2 text-info"></i>Besoin d'aide ?
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="text-center">
                        <p class="text-muted small mb-2">Problème avec la vérification ?</p>
                        <a href="mailto:support@herimeacademie.com" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-envelope me-1"></i>Contacter le support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
    border-radius: 10px;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-outline-info {
    color: #17a2b8;
    border-color: #17a2b8;
    border-radius: 10px;
}

.btn-outline-info:hover {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.alert {
    border-radius: 10px;
}
</style>
@endpush