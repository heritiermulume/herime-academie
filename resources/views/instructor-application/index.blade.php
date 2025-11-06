@extends('layouts.app')

@section('title', 'Devenir Formateur - Herime Academie')

@section('content')
<!-- Hero Section -->
<section class="page-header-section" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); padding: 2rem 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="h2 h1-md fw-bold mb-3">Devenir Formateur</h1>
                <p class="lead mb-4">Partagez votre expertise et transformez des vies grâce à l'éducation en ligne</p>
                @auth
                    @if(auth()->user()->role !== 'instructor')
                        <a href="{{ route('instructor-application.create') }}" class="btn btn-light btn-lg px-3 px-md-5">
                            <i class="fas fa-rocket me-2"></i>Postuler maintenant
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-light btn-lg px-3 px-md-5">
                        <i class="fas fa-sign-in-alt me-2"></i>Se connecter pour postuler
                    </a>
                @endauth
            </div>
        </div>
    </div>
</section>

<!-- Role Explanation Section -->
<section class="page-content-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Introduction -->
                <div class="card border-0 shadow-lg mb-5">
                    <div class="card-body p-3 p-md-5">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                                 style="width: 100px; height: 100px; background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                                <i class="fas fa-chalkboard-teacher fa-3x text-white"></i>
                            </div>
                            <h2 class="fw-bold mb-3" style="color: #003366;">Le Rôle du Formateur chez Herime Académie</h2>
                        </div>
                        
                        <div class="row g-4 mt-3">
                            <div class="col-12 col-md-6">
                                <div class="d-flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px; background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                                            <i class="fas fa-graduation-cap text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Créer des Cours de Qualité</h5>
                                        <p class="text-muted mb-0">Développez et structurez des contenus pédagogiques engageants qui aideront les étudiants à atteindre leurs objectifs d'apprentissage.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-6">
                                <div class="d-flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px; background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                                            <i class="fas fa-users text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Guider les Étudiants</h5>
                                        <p class="text-muted mb-0">Interagissez avec vos étudiants, répondez à leurs questions et les accompagnez dans leur parcours d'apprentissage.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-6">
                                <div class="d-flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px; background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                                            <i class="fas fa-chart-line text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Développer votre Expertise</h5>
                                        <p class="text-muted mb-0">Construisez votre réputation en tant qu'expert dans votre domaine et développez votre audience d'apprenants.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-6">
                                <div class="d-flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px; background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                                            <i class="fas fa-dollar-sign text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Générer des Revenus</h5>
                                        <p class="text-muted mb-0">Monétisez vos connaissances en créant des cours payants et bénéficiez d'une commission sur chaque vente.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Benefits Section -->
                <div class="card border-0 shadow-lg mb-5">
                    <div class="card-header bg-white border-0 py-4">
                        <h3 class="fw-bold text-center mb-0" style="color: #003366;">
                            <i class="fas fa-star me-2"></i>Pourquoi Rejoindre Herime Académie ?
                        </h3>
                    </div>
                    <div class="card-body p-5">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="text-center p-4 h-100" style="background: #f8f9fa; border-radius: 12px;">
                                    <i class="fas fa-tools fa-3x mb-3" style="color: #003366;"></i>
                                    <h5 class="fw-bold mb-3">Outils Professionnels</h5>
                                    <p class="text-muted mb-0">Accédez à une plateforme complète avec tous les outils nécessaires pour créer et gérer vos cours efficacement.</p>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-4">
                                <div class="text-center p-4 h-100" style="background: #f8f9fa; border-radius: 12px;">
                                    <i class="fas fa-headset fa-3x mb-3" style="color: #003366;"></i>
                                    <h5 class="fw-bold mb-3">Support Dédié</h5>
                                    <p class="text-muted mb-0">Bénéficiez d'un accompagnement personnalisé pour vous aider à réussir en tant que formateur.</p>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-4">
                                <div class="text-center p-4 h-100" style="background: #f8f9fa; border-radius: 12px;">
                                    <i class="fas fa-globe fa-3x mb-3" style="color: #003366;"></i>
                                    <h5 class="fw-bold mb-3">Audience Mondiale</h5>
                                    <p class="text-muted mb-0">Touchez des milliers d'étudiants à travers le monde et partagez votre expertise à grande échelle.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Process Section -->
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-white border-0 py-4">
                        <h3 class="fw-bold text-center mb-0" style="color: #003366;">
                            <i class="fas fa-list-ol me-2"></i>Le Processus de Candidature
                        </h3>
                    </div>
                    <div class="card-body p-3 p-md-5">
                        <div class="row g-4">
                            <div class="col-12 col-md-4">
                                <div class="position-relative">
                                    <div class="text-center mb-3">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                                             style="width: 60px; height: 60px; background: linear-gradient(135deg, #003366 0%, #004080 100%); color: white; font-size: 1.5rem; font-weight: bold;">
                                            1
                                        </div>
                                    </div>
                                    <h5 class="fw-bold text-center mb-3">Remplissez le Formulaire</h5>
                                    <p class="text-muted text-center mb-0">Partagez vos informations personnelles, votre expérience professionnelle et votre parcours académique.</p>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-4">
                                <div class="position-relative">
                                    <div class="text-center mb-3">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                                             style="width: 60px; height: 60px; background: linear-gradient(135deg, #003366 0%, #004080 100%); color: white; font-size: 1.5rem; font-weight: bold;">
                                            2
                                        </div>
                                    </div>
                                    <h5 class="fw-bold text-center mb-3">Téléchargez vos Documents</h5>
                                    <p class="text-muted text-center mb-0">Joignez votre CV et votre lettre de motivation pour compléter votre candidature.</p>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-4">
                                <div class="position-relative">
                                    <div class="text-center mb-3">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                                             style="width: 60px; height: 60px; background: linear-gradient(135deg, #003366 0%, #004080 100%); color: white; font-size: 1.5rem; font-weight: bold;">
                                            3
                                        </div>
                                    </div>
                                    <h5 class="fw-bold text-center mb-3">Suivez votre Dossier</h5>
                                    <p class="text-muted text-center mb-0">Consultez l'état de traitement de votre candidature et recevez une réponse de notre équipe.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA Section -->
                @auth
                    @if(auth()->user()->role !== 'instructor')
                        <div class="text-center mt-4 mt-md-5">
                            <a href="{{ route('instructor-application.create') }}" class="btn btn-primary btn-lg px-3 px-md-5 py-2 py-md-3">
                                <i class="fas fa-rocket me-2"></i>Commencer ma candidature
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center mt-4 mt-md-5">
                        <p class="text-muted mb-3">Vous devez être connecté pour postuler</p>
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-3 px-md-5 py-2 py-md-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Responsive typography */
    @media (min-width: 768px) {
        .h1-md {
            font-size: 2.5rem;
        }
        .h2-md {
            font-size: 2rem;
        }
        .small-md {
            font-size: 1rem;
        }
    }
    
    @media (max-width: 767px) {
        .h1-md {
            font-size: 1.75rem;
        }
        .h2-md {
            font-size: 1.5rem;
        }
        .small-md {
            font-size: 0.875rem;
        }
        .extra-small {
            font-size: 0.7rem;
        }
    }
    
    /* Responsive padding adjustments */
    @media (max-width: 767px) {
        .page-content-section {
            padding-top: 2rem !important;
            padding-bottom: 2rem !important;
        }
        
        .page-header-section {
            padding-top: calc(1.5rem + 65px) !important;
        }
    }
    
    /* Tablet navbar offset */
    @media (min-width: 768px) and (max-width: 991px) {
        .page-header-section {
            padding-top: calc(2rem + 70px) !important;
        }
    }
    
    /* Desktop navbar offset */
    @media (min-width: 992px) {
        .page-header-section {
            padding-top: calc(2rem + 75px) !important;
        }
    }
</style>
@endpush

