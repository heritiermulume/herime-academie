@extends('layouts.app')

@section('title', 'Inscription - Herime Academie')

@section('content')
<!-- Page Header Section -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1>Inscription</h1>
                <p class="lead">Création de votre compte</p>
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
                    <div class="card-body p-5 text-center">
                        <!-- Icon -->
                        <div class="mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                                 style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);">
                                <i class="fas fa-user-plus fa-3x text-white"></i>
                            </div>
                        </div>

                        <!-- Title -->
                        <h2 class="mb-3" style="color: var(--primary-color);">Redirection en cours</h2>
                        
                        <!-- Message -->
                        <p class="text-muted mb-4">
                            Vous allez être redirigé vers notre page d'inscription dans un nouvel onglet.
                        </p>

                        <!-- Spinner -->
                        <div class="mb-4">
                            <div class="spinner-border" 
                                 style="width: 3rem; height: 3rem; border-width: 0.3rem; color: var(--primary-color);" 
                                 role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>

                        <!-- Help Text -->
                        <p class="text-muted small mb-4">
                            Si la nouvelle fenêtre ne s'ouvre pas automatiquement, cliquez sur le bouton ci-dessous.
                        </p>

                        <!-- Button -->
                        <div class="d-grid mb-4">
                            <button onclick="openRegisterWindow()" 
                                    class="btn btn-primary btn-lg"
                                    style="background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%); border: none;">
                                <i class="fas fa-external-link-alt me-2"></i>
                                Ouvrir la page d'inscription
                            </button>
                        </div>

                        <!-- Back Link -->
                        <div class="mt-4">
                            <a href="{{ route('home') }}" 
                               class="text-decoration-none" 
                               style="color: var(--primary-color);">
                                <i class="fas fa-arrow-left me-1"></i>
                                Retour à l'accueil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function openRegisterWindow() {
    const ssoRegisterUrl = @json($ssoRegisterUrl);
    window.open(ssoRegisterUrl, '_blank', 'noopener,noreferrer');
}

// Ouvrir automatiquement dans un nouvel onglet au chargement de la page
window.addEventListener('load', function() {
    openRegisterWindow();
});
</script>
@endpush

