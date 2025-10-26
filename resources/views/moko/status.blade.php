@extends('layouts.app')

@section('title', 'Statut du Paiement MOKO')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <!-- En-tête avec couleur bleue du site -->
                <div class="card-header text-white" style="background: #3b82f6;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-mobile-alt me-2"></i>
                        <h5 class="mb-0">Statut du Paiement Mobile Money</h5>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Conteneur de statut -->
                    <div id="status-container">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Vérification...</span>
                            </div>
                            <p class="mt-2 mb-0">Vérification du statut de votre paiement...</p>
                        </div>
                    </div>
                    
                    <!-- Informations de transaction -->
                    <div class="mt-3">
                        <div class="alert alert-light border-start border-primary border-3">
                            <small class="text-muted">
                                <i class="fas fa-receipt me-1"></i>
                                <strong>Référence :</strong> <code id="reference">{{ $reference ?? '' }}</code>
                            </small>
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="mt-3 text-center">
                        <button id="check-status-btn" class="btn btn-primary btn-sm me-2" onclick="checkStatus()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Vérifier
                        </button>
                        <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fas fa-home me-1"></i>
                            Accueil
                        </a>
                        <a href="{{ route('orders.index') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-graduation-cap me-1"></i>
                            Mes cours
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const reference = '{{ $reference ?? '' }}';

function checkStatus() {
    if (!reference) {
        showError('Référence de transaction manquante.');
        return;
    }
    
    const checkBtn = document.getElementById('check-status-btn');
    const statusContainer = document.getElementById('status-container');
    
    // Show loading state
    checkBtn.disabled = true;
    checkBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Vérification...';
    
    statusContainer.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Vérification...</span>
            </div>
            <p class="mt-3">Vérification du statut de votre paiement...</p>
        </div>
    `;
    
    fetch(`/moko/status/${reference}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatusDisplay(data);
        } else {
            showError(data.message || 'Erreur lors de la vérification du statut.');
        }
    })
    .catch(error => {
        console.error('Status check error:', error);
        showError('Erreur de connexion lors de la vérification.');
    })
    .finally(() => {
        checkBtn.disabled = false;
        checkBtn.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Vérifier le statut';
    });
}

function updateStatusDisplay(data) {
    const statusContainer = document.getElementById('status-container');
    const isSuccessful = data.is_successful || data.trans_status === 'Successful';
    const status = data.status || 'pending';
    const transStatus = data.trans_status || 'En attente';
    const comment = data.comment || '';
    
    let statusClass = 'warning';
    let statusIcon = 'fas fa-clock';
    let statusText = 'En attente';
    let statusDescription = 'Votre paiement est en cours de traitement...';
    
    if (isSuccessful) {
        statusClass = 'success';
        statusIcon = 'fas fa-check-circle';
        statusText = 'Paiement réussi !';
        statusDescription = 'Votre paiement a été traité avec succès.';
    } else if (status === 'failed' || transStatus === 'Failed') {
        statusClass = 'danger';
        statusIcon = 'fas fa-times-circle';
        statusText = 'Paiement échoué';
        statusDescription = 'Votre paiement n\'a pas pu être traité.';
    } else if (status === 'cancelled' || transStatus === 'Cancelled') {
        statusClass = 'secondary';
        statusIcon = 'fas fa-ban';
        statusText = 'Paiement annulé';
        statusDescription = 'Votre paiement a été annulé.';
    }
    
    statusContainer.innerHTML = `
        <div class="text-center py-3">
            <div class="mb-3">
                <i class="${statusIcon} fa-2x text-${statusClass}"></i>
            </div>
            <h5 class="text-${statusClass} mb-2">${statusText}</h5>
            <p class="text-muted mb-3">${statusDescription}</p>
            <div class="d-flex justify-content-center align-items-center">
                <span class="badge bg-${statusClass} me-2">${transStatus}</span>
                ${comment ? `<small class="text-muted">${comment}</small>` : ''}
            </div>
        </div>
    `;
    
    // Si le paiement est réussi, rediriger vers la page de succès
    if (isSuccessful) {
        setTimeout(() => {
            window.location.href = '{{ route("moko.success") }}?reference=' + reference;
        }, 2000);
    }
}

function showError(message) {
    const statusContainer = document.getElementById('status-container');
    statusContainer.innerHTML = `
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: #dc3545;"></i>
            </div>
            <h3 class="mb-3 text-danger">Erreur</h3>
            <p class="lead text-muted mb-4">Une erreur s'est produite lors de la vérification de votre paiement.</p>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Détails de l'erreur</h6>
                            <p class="mb-0 text-danger">${message}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Vérifier automatiquement le statut au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    if (reference) {
        checkStatus();
    }
});
</script>
@endsection
