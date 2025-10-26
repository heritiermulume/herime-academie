@extends('layouts.app')

@section('title', 'Paiement Échoué - Herime Academie')
@section('description', 'Votre paiement n\'a pas pu être traité.')

@section('content')
<div class="payment-failure-page">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="failure-card">
                    <div class="failure-header">
                        <div class="failure-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h1 class="failure-title">Paiement Échoué</h1>
                        <p class="failure-message">
                            Votre paiement Mobile Money n'a pas pu être traité. 
                            Veuillez réessayer ou choisir un autre mode de paiement.
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
                                <span class="detail-value status-failed">
                                    <i class="fas fa-times-circle me-1"></i>Échoué
                                </span>
                            </div>
                            @if($transaction->comment)
                            <div class="detail-item">
                                <span class="detail-label">Raison :</span>
                                <span class="detail-value">{{ $transaction->comment }}</span>
                            </div>
                            @endif
                            <div class="detail-item">
                                <span class="detail-label">Date :</span>
                                <span class="detail-value">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="failure-actions">
                        <a href="{{ route('cart.checkout') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-redo me-2"></i>
                            Réessayer le paiement
                        </a>
                        <a href="{{ route('cart.index') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Retour au panier
                        </a>
                    </div>

                    <div class="failure-info">
                        <div class="info-item">
                            <i class="fas fa-info-circle text-info"></i>
                            <span>Vérifiez que votre solde Mobile Money est suffisant</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone text-warning"></i>
                            <span>Assurez-vous que votre numéro est correct</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-headset text-success"></i>
                            <span>Contactez le support si le problème persiste</span>
                        </div>
                    </div>

                    <div class="support-section">
                        <h4 class="support-title">
                            <i class="fas fa-life-ring me-2"></i>Besoin d'aide ?
                        </h4>
                        <p class="support-text">
                            Si vous rencontrez des difficultés, notre équipe support est là pour vous aider.
                        </p>
                        <div class="support-contacts">
                            <a href="mailto:support@herimeacademie.com" class="support-link">
                                <i class="fas fa-envelope"></i>
                                support@herimeacademie.com
                            </a>
                            <a href="tel:+243824449218" class="support-link">
                                <i class="fas fa-phone"></i>
                                +243 824 449 218
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-failure-page {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
}

.failure-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    padding: 3rem;
    text-align: center;
}

.failure-header {
    margin-bottom: 2rem;
}

.failure-icon {
    font-size: 4rem;
    color: #dc3545;
    margin-bottom: 1rem;
    animation: shake 0.6s ease-in-out;
}

.failure-title {
    color: #dc3545;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.failure-message {
    color: #6c757d;
    font-size: 1.2rem;
    margin-bottom: 0;
}

.transaction-details {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 2rem;
    margin: 2rem 0;
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

.status-failed {
    color: #dc3545 !important;
    font-weight: 600;
}

.failure-actions {
    margin: 2rem 0;
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

.failure-info {
    margin: 2rem 0;
    padding: 1.5rem;
    background: #fff3cd;
    border-radius: 10px;
    border-left: 4px solid #ffc107;
}

.info-item {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
    color: #856404;
    font-size: 0.95rem;
}

.info-item i {
    margin-right: 0.5rem;
    font-size: 1.1rem;
}

.support-section {
    margin-top: 2rem;
    padding: 2rem;
    background: #e7f3ff;
    border-radius: 15px;
    border-left: 4px solid #007bff;
}

.support-title {
    color: #004085;
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.support-text {
    color: #004085;
    margin-bottom: 1.5rem;
}

.support-contacts {
    display: flex;
    gap: 2rem;
    justify-content: center;
    flex-wrap: wrap;
}

.support-link {
    display: flex;
    align-items: center;
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    background: white;
    transition: all 0.3s ease;
}

.support-link:hover {
    background: #007bff;
    color: white;
    transform: translateY(-2px);
}

.support-link i {
    margin-right: 0.5rem;
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

@media (max-width: 768px) {
    .failure-card {
        padding: 2rem 1.5rem;
    }
    
    .failure-title {
        font-size: 2rem;
    }
    
    .failure-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-lg {
        width: 100%;
        max-width: 300px;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
    }
    
    .support-contacts {
        flex-direction: column;
        align-items: center;
    }
}
</style>
@endsection
