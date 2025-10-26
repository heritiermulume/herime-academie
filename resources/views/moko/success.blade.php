@extends('layouts.app')

@section('title', 'Paiement Réussi - Herime Academie')
@section('description', 'Votre paiement a été traité avec succès.')

@section('content')
<div class="payment-success-page">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="success-card">
                    <div class="success-header">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h1 class="success-title">Paiement Réussi !</h1>
                        <p class="success-message">
                            Votre paiement Mobile Money a été traité avec succès. 
                            Vous allez recevoir une confirmation par email.
                        </p>
                    </div>

                    @if($transaction)
                    <div class="transaction-details">
                        <h3 class="details-title">
                            <i class="fas fa-receipt me-2"></i>Détails de la transaction
                        </h3>
                        <div class="details-grid">
                            <div class="detail-item">
                                <span class="detail-label">Référence :</span>
                                <span class="detail-value">{{ $transaction->reference }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Montant :</span>
                                <span class="detail-value">{{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Méthode :</span>
                                <span class="detail-value">{{ $transaction->payment_method_name }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Statut :</span>
                                <span class="detail-value status-success">
                                    <i class="fas fa-check-circle me-1"></i>Réussi
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Date :</span>
                                <span class="detail-value">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="success-actions">
                        <a href="{{ route('student.dashboard') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-graduation-cap me-2"></i>
                            Accéder à mes cours
                        </a>
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-list me-2"></i>
                            Voir mes commandes
                        </a>
                    </div>

                    <div class="success-info">
                        <div class="info-item">
                            <i class="fas fa-envelope text-primary"></i>
                            <span>Un email de confirmation a été envoyé à votre adresse</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-mobile-alt text-success"></i>
                            <span>Vous avez reçu une confirmation SMS</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-shield-alt text-warning"></i>
                            <span>Votre paiement est 100% sécurisé</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-success-page {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
}

.success-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    text-align: center;
}

.success-header {
    margin-bottom: 1.5rem;
}

.success-icon {
    font-size: 4rem;
    color: #28a745;
    margin-bottom: 1rem;
    animation: bounceIn 0.6s ease-out;
}

.success-title {
    color: #28a745;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.success-message {
    color: #6c757d;
    font-size: 1.2rem;
    margin-bottom: 0;
}

.transaction-details {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 1.5rem;
    margin: 1.5rem 0;
    text-align: left;
}

.details-title {
    color: #495057;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    text-align: center;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #dee2e6;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #495057;
}

.detail-value {
    color: #6c757d;
    font-weight: 500;
}

.status-success {
    color: #28a745 !important;
    font-weight: 600;
}

.success-actions {
    margin: 1.5rem 0;
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.1rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.success-info {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #dee2e6;
}

.info-item {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
    color: #6c757d;
    font-size: 0.95rem;
}

.info-item i {
    margin-right: 0.5rem;
    font-size: 1.1rem;
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

@media (max-width: 768px) {
    .success-card {
        padding: 1.5rem 1rem;
        margin: 0.5rem;
    }
    
    .success-title {
        font-size: 1.8rem;
    }
    
    .success-message {
        font-size: 1rem;
    }
    
    .success-actions {
        flex-direction: column;
        align-items: center;
        margin: 1rem 0;
    }
    
    .btn-lg {
        width: 100%;
        max-width: 280px;
        padding: 0.8rem 1.5rem;
        font-size: 1rem;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
    }
    
    .transaction-details {
        padding: 1rem;
        margin: 1rem 0;
    }
    
    .details-title {
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }
    
    .success-icon {
        font-size: 3rem;
    }
    
    .success-header {
        margin-bottom: 1rem;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0.5rem;
    }
    
    .success-card {
        padding: 1rem 0.8rem;
        margin: 0.25rem;
        border-radius: 15px;
    }
    
    .success-title {
        font-size: 1.5rem;
    }
    
    .success-message {
        font-size: 0.9rem;
    }
    
    .success-icon {
        font-size: 2.5rem;
    }
    
    .btn-lg {
        padding: 0.7rem 1.2rem;
        font-size: 0.9rem;
        max-width: 250px;
    }
    
    .transaction-details {
        padding: 0.8rem;
    }
    
    .details-title {
        font-size: 1.1rem;
    }
    
    .detail-item {
        padding: 0.5rem 0;
        font-size: 0.9rem;
    }
    
    .info-item {
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }
}
</style>
@endsection
