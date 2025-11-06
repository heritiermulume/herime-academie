@extends('layouts.app')

@section('title', 'Statut de ma Candidature - Herime Academie')

@section('content')
<!-- Header -->
<section class="page-header-section" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); padding: 2rem 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="h3 h2-md fw-bold mb-2">Statut de ma Candidature</h1>
                <p class="mb-0 small small-md">Suivez l'état de traitement de votre dossier</p>
            </div>
        </div>
    </div>
</section>

<!-- Status Section -->
<section class="page-content-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Status Card -->
                <div class="card border-0 shadow-lg mb-4">
                    <div class="card-body p-3 p-md-5">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                                 style="width: 80px; height: 80px; background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                                <i class="fas fa-file-alt fa-3x text-white"></i>
                            </div>
                            <h3 class="fw-bold mb-2">Votre Candidature</h3>
                            <span class="badge bg-{{ $application->getStatusBadgeClass() }} fs-6 px-3 py-2">
                                {{ $application->getStatusLabel() }}
                            </span>
                        </div>

                        <!-- Status Timeline -->
                        <div class="mt-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold">Candidature soumise</div>
                                    <small class="text-muted">{{ $application->created_at->format('d/m/Y à H:i') }}</small>
                                </div>
                            </div>

                            @if($application->status === 'under_review')
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px; background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white;">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">En cours d'examen</div>
                                        <small class="text-muted">Notre équipe examine votre dossier</small>
                                    </div>
                                </div>
                            @endif

                            @if($application->status === 'approved')
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">Candidature approuvée</div>
                                        <small class="text-muted">Révisée le {{ $application->reviewed_at?->format('d/m/Y à H:i') }}</small>
                                    </div>
                                </div>
                                <div class="alert alert-success mt-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Félicitations !</strong> Votre candidature a été approuvée. Vous pouvez maintenant accéder au tableau de bord formateur.
                                    <div class="mt-3">
                                        <a href="{{ route('instructor.dashboard') }}" class="btn btn-success">
                                            <i class="fas fa-tachometer-alt me-2"></i>Accéder au tableau de bord
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if($application->status === 'rejected')
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
                                        <i class="fas fa-times"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">Candidature rejetée</div>
                                        <small class="text-muted">Révisée le {{ $application->reviewed_at?->format('d/m/Y à H:i') }}</small>
                                    </div>
                                </div>
                                @if($application->admin_notes)
                                    <div class="alert alert-danger mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Commentaire de l'administrateur :</strong>
                                        <p class="mb-0 mt-2">{{ $application->admin_notes }}</p>
                                    </div>
                                @endif
                                @if($application->canBeEdited())
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-redo me-2"></i>
                                        Vous pouvez modifier et soumettre à nouveau votre candidature.
                                        <div class="mt-3">
                                            <a href="{{ route('instructor-application.create') }}" class="btn btn-primary">
                                                <i class="fas fa-edit me-2"></i>Modifier ma candidature
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Documents Card -->
                <div class="card border-0 shadow-lg mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-file-alt me-2"></i>Documents soumis
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">CV</div>
                                        <small class="text-muted">Curriculum Vitae</small>
                                    </div>
                                    @if($application->cv_path)
                                        <a href="{{ route('instructor-application.download-cv', $application) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-1"></i><span class="d-none d-md-inline">Télécharger</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <i class="fas fa-file-alt fa-2x text-primary me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">Lettre de Motivation</div>
                                        <small class="text-muted">Document de motivation</small>
                                    </div>
                                    @if($application->motivation_letter_path)
                                        <a href="{{ route('instructor-application.download-motivation-letter', $application) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-1"></i><span class="d-none d-md-inline">Télécharger</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Card -->
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-info-circle me-2"></i>Informations de candidature
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <strong>Téléphone :</strong>
                                <p class="mb-0">{{ $application->phone ?? 'Non renseigné' }}</p>
                            </div>
                            <div class="col-12 col-md-6">
                                <strong>Date de soumission :</strong>
                                <p class="mb-0">{{ $application->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                            @if($application->reviewed_by)
                                <div class="col-12 col-md-6">
                                    <strong>Révisé par :</strong>
                                    <p class="mb-0">{{ $application->reviewer->name ?? 'Administrateur' }}</p>
                                </div>
                            @endif
                            @if($application->reviewed_at)
                                <div class="col-12 col-md-6">
                                    <strong>Date de révision :</strong>
                                    <p class="mb-0">{{ $application->reviewed_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="text-center mt-4 d-flex flex-column flex-md-row gap-2 justify-content-center">
                    @if($application->canBeEdited())
                        <a href="{{ route('instructor-application.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-2"></i>Modifier ma candidature
                        </a>
                    @endif
                    <a href="{{ route('instructor-application.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
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

