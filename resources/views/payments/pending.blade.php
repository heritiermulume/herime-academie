@extends('layouts.app')

@section('title', 'Paiement en cours - Herime Academie')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center mb-5">
                <div class="mb-4">
                    <img src="{{ asset('images/logo-herime-academie.png') }}" alt="Herime Academie" style="height: 80px; max-width: 300px; object-fit: contain;">
                </div>
                <div class="pending-icon mb-4">
                    <i class="fas fa-clock text-warning" style="font-size: 4rem;"></i>
                </div>
                <h1 class="display-5 fw-bold text-warning mb-3">Paiement en cours de traitement</h1>
                <p class="lead text-muted">
                    {{ $message ?? 'Votre paiement est en cours de traitement. Veuillez patienter...' }}
                </p>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-info-circle me-2"></i>Informations importantes
                    </h5>
                </div>
                <div class="card-body p-4">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Votre paiement est en cours de vérification</strong>
                            <p class="text-muted small mb-0 ms-4">
                                Cela peut prendre quelques minutes selon votre méthode de paiement.
                            </p>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-envelope text-info me-2"></i>
                            <strong>Vous recevrez une confirmation par email</strong>
                            <p class="text-muted small mb-0 ms-4">
                                Dès que votre paiement sera validé, nous vous enverrons un email de confirmation avec les détails de votre commande.
                            </p>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-shield-alt text-primary me-2"></i>
                            <strong>Votre commande est sécurisée</strong>
                            <p class="text-muted small mb-0 ms-4">
                                Votre commande a été enregistrée et sera traitée dès validation du paiement.
                            </p>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-question-circle text-secondary me-2"></i>
                            <strong>Besoin d'aide ?</strong>
                            <p class="text-muted small mb-0 ms-4">
                                Si vous ne recevez pas de confirmation dans les 30 minutes, contactez notre support.
                            </p>
                        </li>
                    </ul>
                </div>
            </div>

            @if(isset($order))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-receipt me-2"></i>Détails de la commande
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
                        <div class="col-sm-4 fw-bold">Montant total :</div>
                        <div class="col-sm-8">
                            <span class="h5 fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->total) }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Statut :</div>
                        <div class="col-sm-8">
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-spinner fa-spin me-1"></i>En cours de traitement
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($payment))
            <div class="alert alert-info border-0 shadow-sm mb-4">
                <div class="d-flex align-items-start">
                    <i class="fas fa-info-circle me-3 mt-1" style="font-size: 1.5rem;"></i>
                    <div>
                        <h6 class="fw-bold mb-2">Référence de paiement</h6>
                        <p class="mb-0 small">
                            <code>{{ $payment->payment_id }}</code>
                        </p>
                        <p class="text-muted small mb-0 mt-2">
                            Conservez cette référence pour toute demande d'assistance.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <div class="card border-0 shadow-sm mb-4 bg-light">
                <div class="card-body p-4 text-center">
                    <div class="spinner-border text-warning mb-3" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="text-muted mb-0">
                        <i class="fas fa-sync-alt me-2"></i>
                        Vérification du statut en cours...
                    </p>
                    <p class="text-muted small mb-0 mt-2">
                        Cette page se rafraîchira automatiquement toutes les 10 secondes.
                    </p>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-primary btn-lg me-3">
                    <i class="fas fa-tachometer-alt me-2"></i>Aller au tableau de bord
                </a>
                <a href="{{ route('contents.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-search me-2"></i>Découvrir d'autres cours
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.pending-icon {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
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

.btn-outline-primary {
    color: #003366;
    border-color: #003366;
}

.btn-outline-primary:hover {
    background-color: #003366;
    border-color: #003366;
    color: white;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.3em;
}
</style>
@endpush

@push('scripts')
<script>
    // Rafraîchir la page toutes les 10 secondes pour vérifier le statut
    let refreshCount = 0;
    const maxRefreshes = 30; // Arrêter après 5 minutes (30 * 10 secondes)
    
    const refreshInterval = setInterval(function() {
        refreshCount++;
        
        if (refreshCount >= maxRefreshes) {
            clearInterval(refreshInterval);
            // Afficher un message si le paiement prend trop de temps
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning border-0 shadow-sm mt-4';
            alertDiv.innerHTML = `
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle me-3 mt-1" style="font-size: 1.5rem;"></i>
                    <div>
                        <h6 class="fw-bold mb-2">Le traitement prend plus de temps que prévu</h6>
                        <p class="mb-0">
                            Si vous n'avez pas reçu de confirmation par email dans les 30 minutes, 
                            veuillez contacter notre support avec votre référence de paiement.
                        </p>
                    </div>
                </div>
            `;
            document.querySelector('.container').appendChild(alertDiv);
            return;
        }
        
        // Rafraîchir la page
        window.location.reload();
    }, 10000); // 10 secondes
    
    // Nettoyer l'intervalle si l'utilisateur quitte la page
    window.addEventListener('beforeunload', function() {
        clearInterval(refreshInterval);
    });
</script>
@endpush

