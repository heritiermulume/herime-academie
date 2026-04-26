@extends('layouts.app')

@section('title', 'Lien de reprise expire - Herime Academie')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="mb-3">
                        <i class="fas fa-link-slash text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h1 class="h3 fw-bold mb-3">Lien de reprise indisponible</h1>
                    <p class="text-muted mb-4">
                        Ce lien de reprise de paiement est invalide ou a expire pour des raisons de securite.
                    </p>

                    <div class="d-flex flex-column flex-md-row justify-content-center gap-2">
                        <a href="{{ route('orders.index') }}" class="btn btn-primary">
                            <i class="fas fa-receipt me-2"></i>Mes commandes
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </a>
                    </div>

                    <p class="small text-muted mt-4 mb-0">
                        Si besoin, relancez le paiement depuis votre espace client ou contactez le support.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
