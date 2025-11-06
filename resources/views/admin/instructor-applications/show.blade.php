@extends('layouts.app')

@section('title', 'Détails de la Candidature - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header text-white" style="background-color: #003366;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-sm" title="Tableau de bord">
                                <i class="fas fa-tachometer-alt"></i>
                            </a>
                            <a href="{{ route('admin.instructor-applications') }}" class="btn btn-outline-light btn-sm" title="Liste des candidatures">
                                <i class="fas fa-th-list"></i>
                            </a>
                            <div>
                                <h4 class="mb-1 text-white">
                                    <i class="fas fa-user-graduate me-2"></i>Candidature de {{ $application->user->name }}
                                </h4>
                                <p class="mb-0 text-white-50 small">Détails de la candidature</p>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-{{ $application->getStatusBadgeClass() }} fs-6 px-3 py-2">
                                {{ $application->getStatusLabel() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Left Column - Application Details -->
                <div class="col-12 col-lg-8 mb-4 mb-lg-0">
                    <!-- User Info -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-user me-2"></i>Informations du candidat
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <div style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; flex-shrink: 0; margin-right: 20px;">
                                    <img src="{{ $application->user->avatar_url }}" 
                                         alt="{{ $application->user->name }}" 
                                         style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                </div>
                                <div>
                                    <h5 class="mb-1">{{ $application->user->name }}</h5>
                                    <p class="text-muted mb-1">{{ $application->user->email }}</p>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-phone me-1"></i>{{ $application->phone ?? 'Non renseigné' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Experience -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-briefcase me-2"></i>Expérience Professionnelle
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $application->professional_experience ?? 'Non renseigné' }}</p>
                        </div>
                    </div>

                    <!-- Teaching Experience -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Expérience d'Enseignement
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $application->teaching_experience ?? 'Non renseigné' }}</p>
                        </div>
                    </div>

                    <!-- Specializations -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-star me-2"></i>Domaines de Spécialisation
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $application->specializations ?? 'Non renseigné' }}</p>
                        </div>
                    </div>

                    <!-- Education Background -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-graduation-cap me-2"></i>Parcours Académique
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $application->education_background ?? 'Non renseigné' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Actions & Documents -->
                <div class="col-12 col-lg-4">
                    <!-- Status Update -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-cog me-2"></i>Mettre à jour le statut
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.instructor-applications.update-status', $application) }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="status" class="form-label fw-bold">Statut</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pending" {{ $application->status === 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="under_review" {{ $application->status === 'under_review' ? 'selected' : '' }}>En cours d'examen</option>
                                        <option value="approved" {{ $application->status === 'approved' ? 'selected' : '' }}>Approuvée</option>
                                        <option value="rejected" {{ $application->status === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="admin_notes" class="form-label fw-bold">Notes / Commentaires</label>
                                    <textarea class="form-control" 
                                              id="admin_notes" 
                                              name="admin_notes" 
                                              rows="4" 
                                              placeholder="Ajoutez des notes ou commentaires sur cette candidature...">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                                    <small class="form-text text-muted">Ces notes seront visibles par le candidat si la candidature est rejetée.</small>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-2"></i>Mettre à jour
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-file-alt me-2"></i>Documents
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($application->cv_path)
                                    <a href="{{ route('instructor-application.download-cv', $application) }}" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-file-pdf me-2"></i>Télécharger le CV
                                    </a>
                                @else
                                    <button class="btn btn-outline-secondary" disabled>
                                        <i class="fas fa-file-pdf me-2"></i>CV non disponible
                                    </button>
                                @endif

                                @if($application->motivation_letter_path)
                                    <a href="{{ route('instructor-application.download-motivation-letter', $application) }}" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-file-alt me-2"></i>Télécharger la lettre
                                    </a>
                                @else
                                    <button class="btn btn-outline-secondary" disabled>
                                        <i class="fas fa-file-alt me-2"></i>Lettre non disponible
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Application Info -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-info-circle me-2"></i>Informations
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Date de soumission :</strong>
                                <p class="mb-0">{{ $application->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                            @if($application->reviewed_by)
                                <div class="mb-3">
                                    <strong>Révisé par :</strong>
                                    <p class="mb-0">{{ $application->reviewer->name }}</p>
                                </div>
                            @endif
                            @if($application->reviewed_at)
                                <div class="mb-3">
                                    <strong>Date de révision :</strong>
                                    <p class="mb-0">{{ $application->reviewed_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

