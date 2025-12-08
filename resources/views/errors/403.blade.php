@extends('layouts.app')

@section('title', 'Accès interdit - 403')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 2rem 1rem;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 text-center">
                <div class="error-content" style="background: white; border-radius: 20px; padding: 3rem 2rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);">
                    <!-- Icon -->
                    <div class="mb-4">
                        <div style="width: 120px; height: 120px; margin: 0 auto; background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(245, 158, 11, 0.2);">
                            <i class="fas fa-lock" style="font-size: 3.5rem; color: white;"></i>
                        </div>
                    </div>

                    <!-- Error Title -->
                    <h2 class="h3 fw-bold mb-3" style="color: #1e293b;">
                        Accès interdit
                    </h2>

                    <!-- Error Message -->
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.7;">
                        Vous n'avez pas les permissions nécessaires pour accéder à cette page. 
                        Si vous pensez qu'il s'agit d'une erreur, veuillez contacter l'administrateur.
                    </p>

                    <!-- Actions -->
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                        <a href="{{ route('home') }}" class="btn btn-primary btn-lg px-4" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); border: none; border-radius: 12px; font-weight: 600;">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                        @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-lg px-4" style="border-radius: 12px; font-weight: 600;">
                            <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg px-4" style="border-radius: 12px; font-weight: 600;">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </a>
                        @endauth
                    </div>

                    <!-- Helpful Links -->
                    <div class="mt-5 pt-4 border-top">
                        <p class="text-muted small mb-3">Vous pouvez aussi :</p>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="{{ route('courses.index') }}" class="text-decoration-none" style="color: #003366;">
                                <i class="fas fa-book me-1"></i>Explorer les cours
                            </a>
                            <a href="{{ route('contact') }}" class="text-decoration-none" style="color: #003366;">
                                <i class="fas fa-envelope me-1"></i>Nous contacter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

