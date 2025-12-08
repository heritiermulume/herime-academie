@extends('layouts.app')

@section('title', 'Service indisponible - 503')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 2rem 1rem;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 text-center">
                <div class="error-content" style="background: white; border-radius: 20px; padding: 3rem 2rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);">
                    <!-- Icon -->
                    <div class="mb-4">
                        <div style="width: 120px; height: 120px; margin: 0 auto; background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(99, 102, 241, 0.2);">
                            <i class="fas fa-tools" style="font-size: 3.5rem; color: white;"></i>
                        </div>
                    </div>

                    <!-- Error Title -->
                    <h2 class="h3 fw-bold mb-3" style="color: #1e293b;">
                        Service indisponible
                    </h2>

                    <!-- Error Message -->
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.7;">
                        Le service est temporairement indisponible pour maintenance. 
                        Nous travaillons à rétablir le service au plus vite. Merci de votre patience.
                    </p>

                    <!-- Actions -->
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                        <button onclick="window.location.reload()" class="btn btn-primary btn-lg px-4" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); border: none; border-radius: 12px; font-weight: 600;">
                            <i class="fas fa-redo me-2"></i>Actualiser
                        </button>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg px-4" style="border-radius: 12px; font-weight: 600;">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                    </div>

                    <!-- Helpful Links -->
                    <div class="mt-5 pt-4 border-top">
                        <p class="text-muted small mb-3">En attendant :</p>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="{{ route('blog.index') }}" class="text-decoration-none" style="color: #003366;">
                                <i class="fas fa-newspaper me-1"></i>Lire le blog
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

