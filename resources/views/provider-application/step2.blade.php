@extends('layouts.app')

@section('title', 'Candidature Prestataire - Étape 2 - Herime Academie')

@section('content')
<!-- Header -->
<section class="page-header-section" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); padding: 2rem 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="h3 h2-md fw-bold mb-2">Candidature Prestataire</h1>
                <p class="mb-0 small small-md">Étape 2 sur 3 - Spécialisations et parcours</p>
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
                        <div class="small fw-bold d-none d-md-block">Spécialisations</div>
                        <div class="extra-small fw-bold d-md-none">Spéc.</div>
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
                        <form method="POST" action="{{ route('provider-application.store-step2', $application) }}">
                            @csrf

                            <!-- Specializations -->
                            <div class="mb-4">
                                <label for="specializations" class="form-label fw-bold">
                                    Domaines de Spécialisation <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('specializations') is-invalid @enderror" 
                                          id="specializations" 
                                          name="specializations" 
                                          rows="6" 
                                          placeholder="Listez vos domaines d'expertise et de spécialisation. Quels sujets maîtrisez-vous le mieux ? Dans quels domaines pouvez-vous créer des contenus (formations, ressources professionnelles, outils) ?" 
                                          required>{{ old('specializations', $application->specializations ?? '') }}</textarea>
                                <small class="form-text text-muted">Minimum 20 caractères. Précisez les types de contenus que vous souhaitez proposer.</small>
                                @error('specializations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Education Background -->
                            <div class="mb-4">
                                <label for="education_background" class="form-label fw-bold">
                                    Parcours Académique <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('education_background') is-invalid @enderror" 
                                          id="education_background" 
                                          name="education_background" 
                                          rows="6" 
                                          placeholder="Décrivez votre parcours académique et professionnel : diplômes, certifications, formations, expériences clés qui justifient votre capacité à créer des contenus de qualité." 
                                          required>{{ old('education_background', $application->education_background ?? '') }}</textarea>
                                <small class="form-text text-muted">Minimum 20 caractères.</small>
                                @error('education_background')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Actions -->
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mt-4 mt-md-5">
                                <a href="{{ route('provider-application.create') }}" class="btn btn-outline-secondary order-2 order-md-1">
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

