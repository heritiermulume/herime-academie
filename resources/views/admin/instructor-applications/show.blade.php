@extends('layouts.admin')

@section('title', 'Détails de la Candidature - Admin')
@section('admin-title', 'Candidature de ' . $application->user->name)
@section('admin-subtitle', 'Détails de la candidature')
@section('admin-actions')
    <a href="{{ route('admin.instructor-applications') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@section('admin-content')
    <div class="row g-4">
        <div class="col-md-8">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-user me-2"></i>Informations du candidat
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $application->user->avatar_url }}" 
                             alt="{{ $application->user->name }}" 
                             class="rounded-circle"
                             style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <h5 class="mb-1">{{ $application->user->name }}</h5>
                            <p class="text-muted mb-1">
                                <i class="fas fa-envelope me-2"></i>{{ $application->user->email }}
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-phone me-2"></i>{{ $application->phone ?? 'Non renseigné' }}
                            </p>
                        </div>
                    </div>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $application->getStatusBadgeClass() }} fs-6">
                                {{ $application->getStatusLabel() }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Date de soumission</dt>
                        <dd class="col-sm-8">
                            <i class="fas fa-calendar me-2"></i>
                            {{ $application->created_at->format('d/m/Y à H:i') }}
                        </dd>

                        @if($application->reviewed_by)
                            <dt class="col-sm-4">Révisé par</dt>
                            <dd class="col-sm-8">{{ $application->reviewer->name }}</dd>
                        @endif

                        @if($application->reviewed_at)
                            <dt class="col-sm-4">Date de révision</dt>
                            <dd class="col-sm-8">{{ $application->reviewed_at->format('d/m/Y à H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-briefcase me-2"></i>Expérience Professionnelle
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <p class="mb-0">{{ $application->professional_experience ?? 'Non renseigné' }}</p>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-chalkboard-teacher me-2"></i>Expérience d'Enseignement
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <p class="mb-0">{{ $application->teaching_experience ?? 'Non renseigné' }}</p>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-star me-2"></i>Domaines de Spécialisation
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <p class="mb-0">{{ $application->specializations ?? 'Non renseigné' }}</p>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-graduation-cap me-2"></i>Parcours Académique
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <p class="mb-0">{{ $application->education_background ?? 'Non renseigné' }}</p>
                </div>
            </section>
        </div>

        <div class="col-md-4">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-cog me-2"></i>Mettre à jour le statut
                    </h3>
                </div>
                <div class="admin-panel__body">
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
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-file-alt me-2"></i>Documents
                    </h3>
                </div>
                <div class="admin-panel__body">
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
            </section>
        </div>
    </div>
@endsection

@push('styles')
<style>
/* Styles identiques à analytics */
.admin-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-card__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 16px 16px 0 0;
}

/* Réduire l'espace au-dessus du contenu sur desktop */
@media (min-width: 992px) {
    .admin-card__header .admin-card__title.mb-1 {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-card__header {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }
}

.admin-card__title {
    margin: 0;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
}

.admin-card__body {
    padding: 1.25rem;
}

/* Styles pour admin-panel - identiques à analytics */
.admin-panel {
    margin-bottom: 2rem;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-panel__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-panel__header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.admin-panel__body {
    padding: 1rem;
}

/* Padding légèrement réduit sur desktop */
@media (min-width: 992px) {
    .admin-panel__body {
        padding: 0.875rem 1rem;
    }
}

/* Corriger le chevauchement des boutons dans la carte Informations du certificat */
.admin-panel__body dl.row dd {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.admin-panel__body dl.row dd .badge {
    flex-shrink: 0;
}

.admin-panel__body dl.row dd .btn,
.admin-panel__body dl.row dd button {
    flex-shrink: 0;
    white-space: nowrap;
}

/* Styles responsives pour les paddings et margins - identiques à analytics */
@media (max-width: 991.98px) {
    /* Réduire les paddings et margins sur tablette */
    .admin-panel {
        margin-bottom: 1rem;
    }
    
    .admin-panel__body {
        padding: 0 !important;
    }
    
    .admin-panel__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-panel__header h3 {
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.5rem;
        --bs-gutter-y: 0.5rem;
    }
    
    .admin-card {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-card__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-card__body {
        padding: 0.5rem;
    }
}

@media (max-width: 767.98px) {
    /* Réduire encore plus les paddings et margins sur mobile */
    .admin-panel {
        margin-bottom: 0.75rem;
    }
    
    .admin-panel__body {
        padding: 1.25rem !important;
    }
    
    .admin-panel__header {
        padding: 0.375rem 0.5rem;
    }
    
    .admin-panel__header h3 {
        font-size: 0.95rem;
        margin-bottom: 0.125rem;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.375rem;
        --bs-gutter-y: 0.375rem;
    }
    
    .admin-card {
        margin-bottom: 0.5rem !important;
    }
    
    /* Garder le même design de carte que sur desktop - mêmes tailles */
    .admin-card__header {
        padding: 1rem 1.25rem !important;
    }
    
    .admin-card__body {
        padding: 1.25rem !important;
    }
    
    /* Empiler les boutons sur mobile dans la carte Informations du certificat */
    .admin-panel__body dl.row dd .btn,
    .admin-panel__body dl.row dd button {
        flex: 1 1 auto;
        min-width: 120px;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
}
</style>
@endpush
