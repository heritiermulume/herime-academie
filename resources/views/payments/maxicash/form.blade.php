@extends('layouts.app')

@section('title', 'Redirection vers MaxiCash - Paiement')
@section('description', 'Vous êtes redirigé vers la page de paiement MaxiCash sécurisée.')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="text-center mb-4">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <h3 class="mt-4">Redirection vers MaxiCash</h3>
                <p class="text-muted">Vous allez être redirigé vers la page de paiement sécurisée MaxiCash...</p>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note :</strong> Si vous n'êtes pas redirigé automatiquement, cliquez sur le bouton ci-dessous.
            </div>
        </div>
    </div>
</div>

<!-- Hidden form that will auto-submit to MaxiCash Gateway -->
<form id="maxicashForm" method="POST" action="{{ $gatewayUrl }}" style="display: none;">
    @foreach($params as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach
    
    <button type="submit" id="submitBtn">Continuer</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit the form after 2 seconds
    setTimeout(function() {
        document.getElementById('submitBtn').click();
    }, 2000);
});
</script>
@endsection

