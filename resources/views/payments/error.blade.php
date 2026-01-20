@extends('layouts.app')

@section('title', 'Erreur de paiement - Herime Academie')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center mb-5">
                <div class="mb-4">
                    <img src="{{ asset('images/logo-herime-academie.png') }}" alt="Herime Academie" style="height: 80px; max-width: 300px; object-fit: contain;">
                </div>
                <div class="error-icon mb-4">
                    <i class="fas fa-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
                </div>
                <h1 class="display-5 fw-bold text-danger mb-3">Erreur de paiement</h1>
                <p class="lead text-muted">
                    {{ $message ?? 'Une erreur s\'est produite lors du traitement de votre paiement.' }}
                </p>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-danger text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-info-circle me-2"></i>Que faire maintenant ?
                    </h5>
                </div>
                <div class="card-body p-4">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="fas fa-redo text-primary me-2"></i>
                            <strong>Réessayer le paiement</strong>
                            <p class="text-muted small mb-0 ms-4">
                                Vous pouvez retourner au panier et réessayer avec une autre méthode de paiement.
                            </p>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-credit-card text-info me-2"></i>
                            <strong>Vérifier vos informations de paiement</strong>
                            <p class="text-muted small mb-0 ms-4">
                                Assurez-vous que vos informations bancaires sont correctes et que vous disposez de fonds suffisants.
                            </p>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-phone text-success me-2"></i>
                            <strong>Contacter votre banque</strong>
                            <p class="text-muted small mb-0 ms-4">
                                Si le problème persiste, contactez votre banque pour vérifier qu'il n'y a pas de restriction sur votre compte.
                            </p>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-headset text-warning me-2"></i>
                            <strong>Contacter notre support</strong>
                            <p class="text-muted small mb-0 ms-4">
                                Notre équipe est là pour vous aider. N'hésitez pas à nous contacter si vous avez besoin d'assistance.
                            </p>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm mb-4">
                <div class="d-flex align-items-start">
                    <i class="fas fa-shield-alt me-3 mt-1" style="font-size: 1.5rem;"></i>
                    <div>
                        <h6 class="fw-bold mb-2">Votre sécurité est notre priorité</h6>
                        <p class="mb-0">
                            Aucun montant n'a été débité de votre compte. Vos informations de paiement sont sécurisées et ne sont jamais stockées sur nos serveurs.
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('cart.index') }}" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-shopping-cart me-2"></i>Retour au panier
                </a>
                <a href="{{ route('contents.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-search me-2"></i>Découvrir les cours
                </a>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted small">
                    Besoin d'aide ? 
                    <a href="mailto:support@herime-academie.com" class="text-decoration-none">
                        <i class="fas fa-envelope me-1"></i>Contactez-nous
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.error-icon {
    animation: shake 0.6s ease-in-out;
}

@keyframes shake {
    0%, 100% {
        transform: translateX(0);
    }
    10%, 30%, 50%, 70%, 90% {
        transform: translateX(-5px);
    }
    20%, 40%, 60%, 80% {
        transform: translateX(5px);
    }
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.btn-primary {
    background-color: #003366;
    border-color: #003366;
}

.btn-primary:hover {
    background-color: #004080;
    border-color: #004080;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}
</style>
@endpush

