@extends('layouts.app')

@section('title', 'Candidature Prestataire - Étape 1 - Herime Academie')

@section('content')
<!-- Header -->
<section class="page-header-section" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); padding: 2rem 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="h3 h2-md fw-bold mb-2">Candidature Prestataire</h1>
                <p class="mb-0 small small-md">Étape 1 sur 3 - Informations personnelles et expérience</p>
            </div>
        </div>
    </div>
</section>

<!-- Progress Bar -->
<section class="bg-light py-2 py-md-3">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1 mb-md-2" 
                             style="width: 35px; height: 35px; background: linear-gradient(135deg, #003366 0%, #004080 100%); color: white; font-weight: bold; font-size: 0.9rem;">
                            1
                        </div>
                        <div class="small fw-bold d-none d-md-block">Informations</div>
                        <div class="extra-small fw-bold d-md-none">Info</div>
                    </div>
                    <div class="flex-fill mx-1 mx-md-2">
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar" role="progressbar" style="width: 0%; background: linear-gradient(135deg, #003366 0%, #004080 100%);"></div>
                        </div>
                    </div>
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1 mb-md-2" 
                             style="width: 35px; height: 35px; background: #e9ecef; color: #6c757d; font-weight: bold; font-size: 0.9rem;">
                            2
                        </div>
                        <div class="small text-muted d-none d-md-block">Spécialisations</div>
                        <div class="extra-small text-muted d-md-none">Spéc.</div>
                    </div>
                    <div class="flex-fill mx-1 mx-md-2">
                        <div class="progress" style="height: 3px; background: #e9ecef;">
                        </div>
                    </div>
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1 mb-md-2" 
                             style="width: 35px; height: 35px; background: #e9ecef; color: #6c757d; font-weight: bold; font-size: 0.9rem;">
                            3
                        </div>
                        <div class="small text-muted d-none d-md-block">Documents</div>
                        <div class="extra-small text-muted d-md-none">Docs</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Form Section -->
<section class="page-content-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-3 p-md-5">
                        <form method="POST" action="{{ route('provider-application.store-step1') }}">
                            @csrf

                            <!-- Phone Info -->
                            @auth
                                @if(auth()->user()->phone)
                                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                                        <i class="fas fa-phone me-2"></i>
                                        <div>
                                            <strong>Numéro de téléphone utilisé :</strong> {{ auth()->user()->phone }}
                                            <br>
                                            <small class="text-muted">Ce numéro sera utilisé pour votre candidature. Pour le modifier, veuillez mettre à jour votre profil.</small>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <div>
                                            <strong>Numéro de téléphone manquant</strong>
                                            <br>
                                            <small>Veuillez renseigner votre numéro de téléphone dans votre profil avant de continuer.</small>
                                        </div>
                                    </div>
                                @endif
                            @endauth

                            <!-- Professional Experience -->
                            <div class="mb-4">
                                <label for="professional_experience" class="form-label fw-bold">
                                    Expérience Professionnelle <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('professional_experience') is-invalid @enderror" 
                                          id="professional_experience" 
                                          name="professional_experience" 
                                          rows="6" 
                                          placeholder="Décrivez votre expérience professionnelle dans votre domaine d'expertise. Mentionnez vos réalisations, vos compétences clés et votre parcours professionnel." 
                                          required>{{ old('professional_experience', $application->professional_experience ?? '') }}</textarea>
                                <small class="form-text text-muted">Minimum 50 caractères</small>
                                @error('professional_experience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Teaching / Content Creation Experience -->
                            <div class="mb-4">
                                <label for="teaching_experience" class="form-label fw-bold">
                                    Expérience en création de contenus <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('teaching_experience') is-invalid @enderror" 
                                          id="teaching_experience" 
                                          name="teaching_experience" 
                                          rows="6" 
                                          placeholder="Décrivez votre expérience en création de contenus : formations, enseignement, ressources professionnelles, accompagnement… Dans quel contexte avez-vous déjà créé ou diffusé du contenu ? Quelles méthodes utilisez-vous ?" 
                                          required>{{ old('teaching_experience', $application->teaching_experience ?? '') }}</textarea>
                                <small class="form-text text-muted">Minimum 50 caractères. Indiquez tout type de contenu (formations, ressources, outils, etc.).</small>
                                @error('teaching_experience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Actions -->
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mt-4 mt-md-5">
                                <a href="{{ route('provider-application.index') }}" class="btn btn-outline-secondary order-2 order-md-1">
                                    <i class="fas fa-arrow-left me-2"></i>Retour
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg px-3 px-md-5 order-1 order-md-2">
                                    Continuer <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Navbar offset for fixed navbar */
    @media (max-width: 767px) {
        .page-header-section {
            padding-top: calc(1.5rem + 65px) !important;
        }
    }
    
    @media (min-width: 768px) and (max-width: 991px) {
        .page-header-section {
            padding-top: calc(2rem + 70px) !important;
        }
    }
    
    @media (min-width: 992px) {
        .page-header-section {
            padding-top: calc(2rem + 75px) !important;
        }
    }
</style>
@endpush

