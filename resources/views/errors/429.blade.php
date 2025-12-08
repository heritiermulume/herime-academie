@extends('layouts.app')

@section('title', 'Trop de requêtes - 429')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 2rem 1rem;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 text-center">
                <div class="error-content" style="background: white; border-radius: 20px; padding: 3rem 2rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);">
                    <!-- Icon -->
                    <div class="mb-4">
                        <div style="width: 120px; height: 120px; margin: 0 auto; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(249, 115, 22, 0.2);">
                            <i class="fas fa-tachometer-alt" style="font-size: 3.5rem; color: white;"></i>
                        </div>
                    </div>

                    <!-- Error Title -->
                    <h2 class="h3 fw-bold mb-3" style="color: #1e293b;">
                        Trop de requêtes
                    </h2>

                    <!-- Error Message -->
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.7;">
                        Vous avez effectué trop de requêtes en peu de temps. Veuillez patienter quelques instants 
                        avant de réessayer. Cette limitation protège notre serveur contre les abus.
                    </p>

                    <!-- Actions -->
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                        <button onclick="setTimeout(() => window.location.reload(), 5000)" class="btn btn-primary btn-lg px-4" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); border: none; border-radius: 12px; font-weight: 600;">
                            <i class="fas fa-hourglass-half me-2"></i>Attendre et réessayer
                        </button>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg px-4" style="border-radius: 12px; font-weight: 600;">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                    </div>

                    <!-- Helpful Links -->
                    <div class="mt-5 pt-4 border-top">
                        <p class="text-muted small mb-3">Conseil :</p>
                        <p class="text-muted small">
                            Attendez quelques secondes avant de réessayer. Si vous utilisez un script automatisé, 
                            veuillez réduire la fréquence de vos requêtes.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

