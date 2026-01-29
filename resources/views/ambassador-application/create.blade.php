@extends('layouts.app')

@section('title', 'Candidature Ambassadeur - Étape 1 - Herime Academie')

@section('content')
<!-- Header -->
<section class="page-header-section" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); padding: 2rem 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="h3 h2-md fw-bold mb-2">Candidature Ambassadeur</h1>
                <p class="mb-0 small small-md">Étape 1 sur 3 - Informations personnelles et motivation</p>
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
                        <div class="small text-muted d-none d-md-block">Expérience</div>
                        <div class="extra-small text-muted d-md-none">Exp.</div>
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
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('ambassador-application.store-step1') }}">
                            @csrf

                            @if(auth()->user()->phone)
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-phone me-2"></i>
                                    <strong>Téléphone :</strong> {{ auth()->user()->phone }}
                                    <small class="d-block mt-1 text-muted">Ce numéro sera utilisé pour votre candidature.</small>
                                </div>
                            @else
                                <div class="alert alert-warning mb-4">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Attention :</strong> Votre numéro de téléphone n'est pas renseigné dans votre profil. 
                                    <a href="{{ route('profile.redirect') }}" class="alert-link">Mettez à jour votre profil</a> pour continuer.
                                </div>
                            @endif

                            <!-- Motivation -->
                            <div class="mb-4">
                                <label for="motivation" class="form-label fw-bold">
                                    Motivation <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('motivation') is-invalid @enderror" 
                                          id="motivation" 
                                          name="motivation" 
                                          rows="8" 
                                          placeholder="Pourquoi souhaitez-vous devenir ambassadeur ? Qu'est-ce qui vous motive à promouvoir nos contenus (formations, ressources professionnelles) ? Partagez votre vision et vos objectifs." 
                                          required>{{ old('motivation', $application->motivation ?? '') }}</textarea>
                                <small class="form-text text-muted">Minimum 100 caractères</small>
                                @error('motivation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Actions -->
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mt-4 mt-md-5">
                                <a href="{{ route('ambassador-application.index') }}" class="btn btn-outline-secondary order-2 order-md-1">
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

