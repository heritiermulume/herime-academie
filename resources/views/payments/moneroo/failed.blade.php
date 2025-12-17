@extends('layouts.app')

@section('title', 'Paiement échoué')

@section('content')
<style>
:root {
    --primary-color: #003366;
    --accent-color: #ffcc33;
    --danger-color: #dc3545;
    --text-dark: #2c3e50;
    --text-muted: #6c757d;
    --bg-light: #f8f9fa;
    --border-color: #e9ecef;
}

.payment-result-page {
    background: var(--bg-light);
    min-height: calc(100vh - 300px);
    padding: 3rem 0;
}

.result-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 3rem;
    max-width: 700px;
    margin: 0 auto;
}

.failed-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--danger-color) 0%, #c82333 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
}

.failed-icon i {
    font-size: 48px;
    color: white;
}

.result-title {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 2rem;
    text-align: center;
    margin-bottom: 1rem;
}

.result-subtitle {
    color: var(--text-muted);
    text-align: center;
    font-size: 1.1rem;
    margin-bottom: 2.5rem;
}

.error-info {
    background: linear-gradient(135deg, #ffe0e0 0%, #ffcccc 100%);
    border: 2px solid var(--danger-color);
    border-radius: 8px;
    padding: 1.25rem;
    margin-bottom: 2rem;
    color: var(--text-dark);
}

.error-info i {
    color: var(--danger-color);
    margin-right: 0.75rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primary-custom {
    background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
    border: none;
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 51, 102, 0.3);
    color: white;
}

.btn-outline-custom {
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    background: white;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-custom:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .result-card {
        padding: 2rem 1.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-primary-custom,
    .btn-outline-custom {
        width: 100%;
    }
}
</style>

<div class="payment-result-page">
    <div class="container">
        <div class="result-card">
            <div class="failed-icon">
                <i class="fas fa-times"></i>
            </div>
            
            <h1 class="result-title">Paiement échoué</h1>
            <p class="result-subtitle">Votre transaction n'a pas pu être effectuée.</p>

            {{-- Afficher le message d'erreur de la session --}}
            @if(session('error'))
            <div class="error-info mb-3">
                <i class="fas fa-times-circle"></i>
                <strong>Erreur :</strong><br>
                {{ session('error') }}
            </div>
            @endif

            @if(session('warning'))
            <div class="error-info mb-3" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); border-color: #ffc107;">
                <i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i>
                <strong>Attention :</strong><br>
                {{ session('warning') }}
            </div>
            @endif

            <div class="error-info">
                <i class="fas fa-info-circle"></i>
                <strong>Que s'est-il passé ?</strong><br>
                Le paiement a été annulé ou a échoué. Voici quelques raisons possibles :
                <ul class="mt-2 mb-0">
                    <li>Solde insuffisant dans votre portefeuille mobile money</li>
                    <li>Transaction refusée par l'opérateur</li>
                    <li>Délai de paiement dépassé</li>
                    <li>Problème de connexion réseau</li>
                </ul>
            </div>

            <div class="alert alert-info mt-3">
                <i class="fas fa-lightbulb me-2"></i>
                <strong>Conseil :</strong> Vérifiez votre solde et réessayez. Si le problème persiste, contactez notre support.
            </div>

            <div class="action-buttons">
                @auth
                <a href="{{ route('cart.index') }}" class="btn btn-primary-custom">
                    <i class="fas fa-shopping-cart me-2"></i>Revenir au panier
                </a>
                @endauth
                
                <a href="{{ route('home') }}" class="btn btn-outline-custom">
                    <i class="fas fa-home me-2"></i>Retour à l'accueil
                </a>
                
                @auth
                <a href="{{ route('orders.index') }}" class="btn btn-outline-custom">
                    <i class="fas fa-list me-2"></i>Mes commandes
                </a>
                @endauth
            </div>

            {{-- Section d'aide supplémentaire --}}
            <div class="mt-4 text-center">
                <p class="text-muted">
                    <i class="fas fa-question-circle me-1"></i>
                    Besoin d'aide ? 
                    <a href="mailto:support@herime-academie.com" class="text-primary">
                        Contactez notre support
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

