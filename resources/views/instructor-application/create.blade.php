@extends('layouts.app')

@section('title', 'Candidature Formateur - Étape 1 - Herime Academie')

@section('content')
<!-- Header -->
<section class="page-header-section" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); padding: 3rem 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="h2 fw-bold mb-2">Candidature Formateur</h1>
                <p class="mb-0">Étape 1 sur 3 - Informations personnelles et expérience</p>
            </div>
        </div>
    </div>
</section>

<!-- Progress Bar -->
<section class="bg-light py-3">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                             style="width: 40px; height: 40px; background: linear-gradient(135deg, #003366 0%, #004080 100%); color: white; font-weight: bold;">
                            1
                        </div>
                        <div class="small fw-bold">Informations</div>
                    </div>
                    <div class="flex-fill mx-2">
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar" role="progressbar" style="width: 0%; background: linear-gradient(135deg, #003366 0%, #004080 100%);"></div>
                        </div>
                    </div>
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                             style="width: 40px; height: 40px; background: #e9ecef; color: #6c757d; font-weight: bold;">
                            2
                        </div>
                        <div class="small text-muted">Spécialisations</div>
                    </div>
                    <div class="flex-fill mx-2">
                        <div class="progress" style="height: 3px; background: #e9ecef;">
                        </div>
                    </div>
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                             style="width: 40px; height: 40px; background: #e9ecef; color: #6c757d; font-weight: bold;">
                            3
                        </div>
                        <div class="small text-muted">Documents</div>
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
                    <div class="card-body p-5">
                        <form method="POST" action="{{ route('instructor-application.store-step1') }}">
                            @csrf

                            <!-- Phone -->
                            <div class="mb-4">
                                <label for="phone" class="form-label fw-bold">
                                    Téléphone <span class="text-danger">*</span>
                                </label>
                                <input type="tel" 
                                       class="form-control form-control-lg @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $application->phone ?? '') }}" 
                                       placeholder="Ex: +243 900 000 000" 
                                       required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

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

                            <!-- Teaching Experience -->
                            <div class="mb-4">
                                <label for="teaching_experience" class="form-label fw-bold">
                                    Expérience d'Enseignement <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('teaching_experience') is-invalid @enderror" 
                                          id="teaching_experience" 
                                          name="teaching_experience" 
                                          rows="6" 
                                          placeholder="Décrivez votre expérience en matière d'enseignement ou de formation. Avez-vous déjà enseigné ? Dans quel contexte ? Quelles méthodes pédagogiques utilisez-vous ?" 
                                          required>{{ old('teaching_experience', $application->teaching_experience ?? '') }}</textarea>
                                <small class="form-text text-muted">Minimum 50 caractères</small>
                                @error('teaching_experience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-between mt-5">
                                <a href="{{ route('instructor-application.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Retour
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg px-5">
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

