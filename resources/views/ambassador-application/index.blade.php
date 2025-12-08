@extends('layouts.app')

@section('title', 'Devenir Ambassadeur - Herime Academie')

@section('content')
<!-- Hero Section -->
<section class="page-header-section" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); padding: 2rem 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="h2 h1-md fw-bold mb-3">Devenir Ambassadeur</h1>
                <p class="lead mb-4">Rejoignez notre programme d'ambassadeur et gagnez des commissions en partageant nos formations</p>
                @auth
                    @if($isAmbassador)
                        <button class="btn btn-light btn-lg px-3 px-md-5" disabled>
                            <i class="fas fa-check-circle me-2"></i>Vous êtes déjà ambassadeur
                        </button>
                    @elseif(isset($application) && $application)
                        <a href="{{ route('ambassador-application.status', $application) }}" class="btn btn-light btn-lg px-3 px-md-5">
                            <i class="fas fa-eye me-2"></i>Voir le statut de ma candidature
                        </a>
                    @else
                        <a href="{{ route('ambassador-application.create') }}" class="btn btn-light btn-lg px-3 px-md-5">
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

@auth
    @if(!$isAmbassador && isset($application) && $application)
        <section class="py-4">
            <div class="container">
                <div class="col-lg-10 mx-auto">
                    <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-md-center shadow-sm" role="alert">
                        <div class="mb-3 mb-md-0">
                            <h5 class="fw-bold mb-1">
                                <i class="fas fa-hourglass-half me-2"></i>Candidature en cours
                            </h5>
                            <p class="mb-0">
                                @if($application->canBeEdited())
                                    Vous avez déjà une candidature au programme ambassadeur. Vous pouvez suivre son statut ou la recommencer depuis le début.
                                @else
                                    Votre candidature a été soumise et est en cours de traitement. Vous pouvez suivre son statut ci-dessous.
                                @endif
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('ambassador-application.status', $application) }}" class="btn btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>Voir le statut
                            </a>
                            @if($application->canBeEdited())
                                <form method="POST" action="{{ route('ambassador-application.abandon', $application) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir abandonner votre candidature et recommencer ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-undo me-1"></i>Abandonner et recommencer
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endauth

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
                                <i class="fas fa-handshake fa-3x text-white"></i>
                            </div>
                            <h2 class="fw-bold mb-3" style="color: #003366;">Le Programme Ambassadeur Herime Académie</h2>
                        </div>
                        
                        <div class="row g-4 mt-3">
                            <div class="col-12 col-md-6">
                                <div class="d-flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px; background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                                            <i class="fas fa-gift text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Code Promo Unique</h5>
                                        <p class="text-muted mb-0">Recevez un code promo unique à partager avec votre réseau pour gagner des commissions sur chaque vente.</p>
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
                                        <h5 class="fw-bold mb-2">Commissions Attractives</h5>
                                        <p class="text-muted mb-0">Gagnez un pourcentage sur chaque vente réalisée avec votre code promo. Plus vous partagez, plus vous gagnez !</p>
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
                                        <h5 class="fw-bold mb-2">Suivi en Temps Réel</h5>
                                        <p class="text-muted mb-0">Suivez vos statistiques, vos ventes et vos gains en temps réel depuis votre tableau de bord.</p>
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
                                        <h5 class="fw-bold mb-2">Réseau Illimité</h5>
                                        <p class="text-muted mb-0">Partagez votre code avec autant de personnes que vous le souhaitez. Aucune limite sur le nombre de références.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Benefits Section -->
                <div class="card border-0 shadow-lg mb-5">
                    <div class="card-header border-0 py-4" style="background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                        <h3 class="fw-bold text-center mb-0 text-white">
                            <i class="fas fa-star me-2"></i>Pourquoi Rejoindre le Programme ?
                        </h3>
                    </div>
                    <div class="card-body p-5">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="text-center p-4 h-100" style="background: #f8f9fa; border-radius: 12px;">
                                    <i class="fas fa-money-bill-wave fa-3x mb-3" style="color: #003366;"></i>
                                    <h5 class="fw-bold mb-3">Revenus Passifs</h5>
                                    <p class="text-muted mb-0">Générez des revenus en partageant simplement votre code promo avec votre réseau.</p>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-4">
                                <div class="text-center p-4 h-100" style="background: #f8f9fa; border-radius: 12px;">
                                    <i class="fas fa-trophy fa-3x mb-3" style="color: #003366;"></i>
                                    <h5 class="fw-bold mb-3">Reconnaissance</h5>
                                    <p class="text-muted mb-0">Faites partie d'une communauté d'ambassadeurs qui contribuent à l'éducation en ligne.</p>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-4">
                                <div class="text-center p-4 h-100" style="background: #f8f9fa; border-radius: 12px;">
                                    <i class="fas fa-rocket fa-3x mb-3" style="color: #003366;"></i>
                                    <h5 class="fw-bold mb-3">Croissance</h5>
                                    <p class="text-muted mb-0">Accédez à des outils marketing et des ressources pour maximiser vos ventes.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Process Section -->
                <div class="card border-0 shadow-lg">
                    <div class="card-header border-0 py-4" style="background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                        <h3 class="fw-bold text-center mb-0 text-white">
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
                                    <h5 class="fw-bold text-center mb-3">Informations Personnelles</h5>
                                    <p class="text-muted text-center mb-0">Remplissez vos informations de contact et partagez votre motivation pour devenir ambassadeur.</p>
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
                                    <h5 class="fw-bold text-center mb-3">Expérience et Présence</h5>
                                    <p class="text-muted text-center mb-0">Décrivez votre expérience en marketing et votre présence sur les réseaux sociaux.</p>
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
                                    <h5 class="fw-bold text-center mb-3">Idées Marketing</h5>
                                    <p class="text-muted text-center mb-0">Partagez vos idées pour promouvoir nos formations et soumettez votre candidature.</p>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Après soumission :</strong> Notre équipe examine votre candidature et vous répond dans les plus brefs délais. Une fois approuvé, vous recevrez votre code promo unique et pourrez commencer à gagner des commissions !
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA Section -->
                @auth
                    @if($isAmbassador)
                        <div class="text-center mt-4 mt-md-5">
                            <div class="alert alert-success d-inline-block" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Vous êtes déjà ambassadeur !</strong>
                                <p class="mb-0 mt-2">Accédez à votre tableau de bord depuis le menu de votre profil.</p>
                            </div>
                        </div>
                    @elseif(isset($application) && $application)
                        <div class="text-center mt-4 mt-md-5">
                            <a href="{{ route('ambassador-application.status', $application) }}" class="btn btn-primary btn-lg px-3 px-md-5 py-2 py-md-3">
                                <i class="fas fa-eye me-2"></i>Voir le statut de ma candidature
                            </a>
                        </div>
                    @else
                        <div class="text-center mt-4 mt-md-5">
                            <a href="{{ route('ambassador-application.create') }}" class="btn btn-primary btn-lg px-3 px-md-5 py-2 py-md-3">
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
    @media (min-width: 768px) {
        .h1-md {
            font-size: 2.5rem;
        }
    }
    
    @media (min-width: 992px) {
        .page-header-section {
            padding-top: calc(2rem + 80px) !important;
        }
    }
    
    @media (max-width: 767px) {
        .h1-md {
            font-size: 1.75rem;
        }
        .page-content-section {
            padding-top: 2rem !important;
            padding-bottom: 2rem !important;
        }
        .page-header-section {
            padding-top: calc(1.5rem + 65px) !important;
        }
    }
</style>
@endpush

