@extends('layouts.app')

@section('title', 'Candidature Ambassadeur - Étape 2 - Herime Academie')

@section('content')
<!-- Header -->
<section class="page-header-section" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); padding: 2rem 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="h3 h2-md fw-bold mb-2">Candidature Ambassadeur</h1>
                <p class="mb-0 small small-md">Étape 2 sur 3 - Expérience et présence en ligne</p>
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
                             style="width: 35px; height: 35px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; font-size: 0.9rem;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="small fw-bold d-none d-md-block">Informations</div>
                        <div class="extra-small fw-bold d-md-none">Info</div>
                    </div>
                    <div class="flex-fill mx-1 mx-md-2">
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar" role="progressbar" style="width: 50%; background: linear-gradient(135deg, #003366 0%, #004080 100%);"></div>
                        </div>
                    </div>
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1 mb-md-2" 
                             style="width: 35px; height: 35px; background: linear-gradient(135deg, #003366 0%, #004080 100%); color: white; font-weight: bold; font-size: 0.9rem;">
                            2
                        </div>
                        <div class="small fw-bold d-none d-md-block">Expérience</div>
                        <div class="extra-small fw-bold d-md-none">Exp.</div>
                    </div>
                    <div class="flex-fill mx-1 mx-md-2">
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar" role="progressbar" style="width: 0%; background: linear-gradient(135deg, #003366 0%, #004080 100%);"></div>
                        </div>
                    </div>
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1 mb-md-2" 
                             style="width: 35px; height: 35px; background: #e9ecef; color: #6c757d; font-weight: bold; font-size: 0.9rem;">
                            3
                        </div>
                        <div class="small text-muted d-none d-md-block">Marketing</div>
                        <div class="extra-small text-muted d-md-none">Marketing</div>
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
                        <form method="POST" action="{{ route('ambassador-application.store-step2', $application) }}">
                            @csrf

                            <!-- Experience -->
                            <div class="mb-4">
                                <label for="experience" class="form-label fw-bold">
                                    Expérience <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('experience') is-invalid @enderror" 
                                          id="experience" 
                                          name="experience" 
                                          rows="6" 
                                          placeholder="Décrivez votre expérience en marketing, réseaux sociaux, ou promotion de produits/services. Avez-vous déjà promu des produits ou services ? Dans quel contexte ?" 
                                          required>{{ old('experience', $application->experience ?? '') }}</textarea>
                                <small class="form-text text-muted">Minimum 50 caractères</small>
                                @error('experience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Social Media Presence -->
                            <div class="mb-4">
                                <label for="social_media_presence" class="form-label fw-bold">
                                    Présence sur les réseaux sociaux
                                </label>
                                <textarea class="form-control @error('social_media_presence') is-invalid @enderror" 
                                          id="social_media_presence" 
                                          name="social_media_presence" 
                                          rows="4" 
                                          placeholder="Mentionnez vos comptes sur les réseaux sociaux (Facebook, Instagram, LinkedIn, Twitter, etc.) et votre nombre d'abonnés approximatif.">{{ old('social_media_presence', $application->social_media_presence ?? '') }}</textarea>
                                <small class="form-text text-muted">Optionnel mais recommandé</small>
                                @error('social_media_presence')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Target Audience -->
                            <div class="mb-4">
                                <label for="target_audience" class="form-label fw-bold">
                                    Audience cible
                                </label>
                                <textarea class="form-control @error('target_audience') is-invalid @enderror" 
                                          id="target_audience" 
                                          name="target_audience" 
                                          rows="4" 
                                          placeholder="Décrivez votre audience cible. Qui sont les personnes que vous pouvez toucher avec nos formations ? Quel est leur profil ?">{{ old('target_audience', $application->target_audience ?? '') }}</textarea>
                                <small class="form-text text-muted">Optionnel mais recommandé</small>
                                @error('target_audience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Actions -->
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mt-4 mt-md-5">
                                <a href="{{ route('ambassador-application.create') }}" class="btn btn-outline-secondary order-2 order-md-1">
                                    <i class="fas fa-arrow-left me-2"></i>Précédent
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
    /* Responsive typography */
    @media (min-width: 768px) {
        .h2-md {
            font-size: 2rem;
        }
        .small-md {
            font-size: 1rem;
        }
    }
    
    @media (max-width: 767px) {
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









