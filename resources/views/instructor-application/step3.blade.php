@extends('layouts.app')

@section('title', 'Candidature Formateur - Étape 3 - Herime Academie')

@section('content')
<!-- Header -->
<section class="page-header-section" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); padding: 2rem 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="h3 h2-md fw-bold mb-2">Candidature Formateur</h1>
                <p class="mb-0 small small-md">Étape 3 sur 3 - Documents à télécharger</p>
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
                            <div class="progress-bar" role="progressbar" style="width: 100%; background: linear-gradient(135deg, #003366 0%, #004080 100%);"></div>
                        </div>
                    </div>
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1 mb-md-2" 
                             style="width: 35px; height: 35px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; font-size: 0.9rem;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="small fw-bold d-none d-md-block">Spécialisations</div>
                        <div class="extra-small fw-bold d-md-none">Spéc.</div>
                    </div>
                    <div class="flex-fill mx-1 mx-md-2">
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar" role="progressbar" style="width: 50%; background: linear-gradient(135deg, #003366 0%, #004080 100%);"></div>
                        </div>
                    </div>
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1 mb-md-2" 
                             style="width: 35px; height: 35px; background: linear-gradient(135deg, #003366 0%, #004080 100%); color: white; font-weight: bold; font-size: 0.9rem;">
                            3
                        </div>
                        <div class="small fw-bold d-none d-md-block">Documents</div>
                        <div class="extra-small fw-bold d-md-none">Docs</div>
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
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Format accepté :</strong> PDF, DOC, DOCX (Maximum 5MB par fichier)
                        </div>

                        <form method="POST" action="{{ route('instructor-application.store-step3', $application) }}" enctype="multipart/form-data">
                            @csrf

                            <!-- CV Upload -->
                            <div class="mb-5">
                                <label for="cv" class="form-label fw-bold">
                                    CV / Curriculum Vitae <span class="text-danger">*</span>
                                </label>
                                <input type="file" 
                                       class="form-control form-control-lg @error('cv') is-invalid @enderror" 
                                       id="cv" 
                                       name="cv" 
                                       accept=".pdf,.doc,.docx"
                                       required>
                                <small class="form-text text-muted d-block mt-2">
                                    <i class="fas fa-file-pdf me-1"></i>
                                    Téléchargez votre CV à jour (PDF, DOC ou DOCX)
                                </small>
                                @error('cv')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Motivation Letter Upload -->
                            <div class="mb-5">
                                <label for="motivation_letter" class="form-label fw-bold">
                                    Lettre de Motivation <span class="text-danger">*</span>
                                </label>
                                <input type="file" 
                                       class="form-control form-control-lg @error('motivation_letter') is-invalid @enderror" 
                                       id="motivation_letter" 
                                       name="motivation_letter" 
                                       accept=".pdf,.doc,.docx"
                                       required>
                                <small class="form-text text-muted d-block mt-2">
                                    <i class="fas fa-file-alt me-1"></i>
                                    Expliquez pourquoi vous souhaitez devenir formateur sur Herime Académie (PDF, DOC ou DOCX)
                                </small>
                                @error('motivation_letter')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Actions -->
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mt-4 mt-md-5">
                                <a href="{{ route('instructor-application.step2', $application) }}" class="btn btn-outline-secondary order-2 order-md-1">
                                    <i class="fas fa-arrow-left me-2"></i>Précédent
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg px-3 px-md-5 order-1 order-md-2">
                                    <i class="fas fa-paper-plane me-2"></i>Soumettre ma candidature
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

