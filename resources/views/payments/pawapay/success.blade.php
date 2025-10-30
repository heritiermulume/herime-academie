@extends('layouts.app')

@section('title', 'Paiement réussi')

@section('content')
<style>
:root {
    --primary-color: #003366;
    --accent-color: #ffcc33;
    --success-color: #28a745;
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

.success-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--success-color) 0%, #20c997 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.success-icon i {
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

.order-details-card {
    background: var(--bg-light);
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.order-details-title {
    color: var(--primary-color);
    font-weight: 600;
    font-size: 1.25rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--accent-color);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    color: var(--text-muted);
    font-weight: 500;
}

.detail-value {
    color: var(--text-dark);
    font-weight: 600;
}

.status-badge {
    background: linear-gradient(135deg, var(--success-color) 0%, #20c997 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9rem;
}

.info-alert {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border: 2px solid #2196f3;
    border-radius: 8px;
    padding: 1.25rem;
    margin-bottom: 2rem;
    color: var(--text-dark);
}

.info-alert i {
    color: #2196f3;
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
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 class="result-title">Paiement réussi !</h1>
            <p class="result-subtitle">Votre transaction a été effectuée avec succès.</p>

            @if(isset($order))
            <div class="order-details-card">
                <h5 class="order-details-title">
                    <i class="fas fa-receipt me-2"></i>Détails de la commande
                </h5>
                
                <div class="detail-row">
                    <span class="detail-label">Numéro de commande :</span>
                    <span class="detail-value">{{ $order->order_number }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Montant total :</span>
                    <span class="detail-value">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->total) }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Statut :</span>
                    <span class="status-badge">{{ ucfirst($order->status) }}</span>
                </div>
            </div>

            <div class="info-alert">
                <i class="fas fa-info-circle"></i>
                <strong>Accès immédiat :</strong> Vous avez maintenant accès à tous les cours que vous avez achetés. Vous pouvez y accéder depuis votre tableau de bord étudiant.
            </div>
            @elseif(isset($reconciliation_warning) && $reconciliation_warning)
            <div class="order-details-card">
                <h5 class="order-details-title">
                    <i class="fas fa-clock me-2"></i>Paiement en cours de validation
                </h5>
                
                <div style="text-align: center; padding: 1rem 0;">
                    <i class="fas fa-spinner fa-spin fa-2x text-warning mb-3"></i>
                    <p class="text-muted">
                        Votre paiement est en cours de validation par notre système de réconciliation automatique. 
                        Ce processus permet de garantir la cohérence de votre transaction.
                    </p>
                    <p class="text-muted">
                        Vous recevrez une confirmation par email dès que le paiement sera confirmé. 
                        Cela peut prendre quelques minutes.
                    </p>
                </div>
            </div>
            @elseif(isset($processing_warning) && $processing_warning)
            <div class="order-details-card">
                <h5 class="order-details-title">
                    <i class="fas fa-hourglass-half me-2"></i>Paiement en cours de traitement
                </h5>
                
                <div style="text-align: center; padding: 1rem 0;">
                    <i class="fas fa-spinner fa-spin fa-2x text-info mb-3"></i>
                    <p class="text-muted">
                        Votre paiement est en cours de traitement. Veuillez patienter quelques instants.
                    </p>
                    <p class="text-muted">
                        Vous pouvez rafraîchir cette page dans quelques secondes pour vérifier le statut de votre paiement.
                    </p>
                </div>
            </div>
            @endif

            <div class="action-buttons">
                <a href="{{ route('orders.index') }}" class="btn btn-primary-custom">
                    <i class="fas fa-list me-2"></i>Voir mes commandes
                </a>
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-custom">
                    <i class="fas fa-tachometer-alt me-2"></i>Mon tableau de bord
                </a>
                <a href="{{ route('home') }}" class="btn btn-outline-custom">
                    <i class="fas fa-home me-2"></i>Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
