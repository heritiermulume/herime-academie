@extends('layouts.app')

@section('title', 'Paiement annulé - Herime Academie')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center mb-5">
                <div class="mb-4">
                    <img src="{{ asset('images/logo-herime-academie.png') }}" alt="Herime Academie" style="height: 80px; max-width: 300px; object-fit: contain;">
                </div>
                <div class="cancel-icon mb-4">
                    <i class="fas fa-times-circle text-warning" style="font-size: 4rem;"></i>
                </div>
                <h1 class="display-5 fw-bold text-warning mb-3">Paiement annulé</h1>
                <p class="lead text-muted">
                    Votre paiement a été annulé. Aucun montant n'a été débité de votre compte.
                </p>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i>Détails de la commande
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Numéro de commande :</div>
                        <div class="col-sm-8">#{{ $order->order_number }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Date :</div>
                        <div class="col-sm-8">{{ $order->created_at->format('d/m/Y à H:i') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Montant :</div>
                        <div class="col-sm-8">
                            <span class="h5 text-muted">${{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Statut :</div>
                        <div class="col-sm-8">
                            <span class="badge bg-warning">Annulé</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-question-circle text-info me-2"></i>Que s'est-il passé ?
                    </h6>
                    <p class="text-muted mb-3">
                        Votre paiement a été annulé. Cela peut arriver pour plusieurs raisons :
                    </p>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-arrow-right text-primary me-2"></i>
                            Vous avez annulé le paiement avant la finalisation
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-arrow-right text-primary me-2"></i>
                            Une erreur technique s'est produite
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-arrow-right text-primary me-2"></i>
                            Votre carte bancaire a été refusée
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-arrow-right text-primary me-2"></i>
                            Le délai de paiement a expiré
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-lightbulb text-warning me-2"></i>Que pouvez-vous faire ?
                    </h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Réessayer le paiement avec une autre méthode
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Vérifier les informations de votre carte bancaire
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Contacter notre support si le problème persiste
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            Explorer nos cours gratuits en attendant
                        </li>
                    </ul>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('courses.index') }}" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-redo me-2"></i>Réessayer le paiement
                </a>
                <a href="{{ route('courses.index') }}" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-search me-2"></i>Explorer les cours
                </a>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted">
                    Besoin d'aide ? 
                    <a href="mailto:support@herimeacademie.com" class="text-primary">
                        Contactez notre support
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.cancel-icon {
    animation: shake 0.6s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
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

.btn-outline-primary {
    color: #003366;
    border-color: #003366;
}

.btn-outline-primary:hover {
    background-color: #003366;
    border-color: #003366;
}
</style>
@endpush