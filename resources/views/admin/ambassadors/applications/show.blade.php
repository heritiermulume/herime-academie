@extends('layouts.admin')

@section('title', 'Détails Candidature Ambassadeur')
@section('admin-title', 'Candidature Ambassadeur')
@section('admin-subtitle', 'Détails complets de la candidature')

@section('admin-actions')
    <a href="{{ route('admin.ambassadors.index', ['tab' => 'applications']) }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('admin-content')
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

    <div class="row g-4">
        <!-- Informations du candidat -->
        <div class="col-12">
            <div class="admin-card">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h3 class="admin-card__title mb-2">
                            <i class="fas fa-user-circle me-2 text-primary"></i>Informations du candidat
                        </h3>
                    </div>
                    <div>
                        <span class="admin-badge {{ $application->getStatusBadgeClass() }}">
                            {{ $application->getStatusLabel() }}
                        </span>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $application->user->avatar_url }}" alt="{{ $application->user->name }}" class="admin-user-avatar-large">
                        <div>
                            <h4 class="mb-1">{{ $application->user->name }}</h4>
                            <p class="text-muted mb-0">{{ $application->user->email }}</p>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-user me-2 text-muted"></i>Nom complet
                            </label>
                            <div class="info-value text-start">{{ $application->user->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-envelope me-2 text-muted"></i>Email
                            </label>
                            <div class="info-value text-start">
                                <a href="mailto:{{ $application->user->email }}">{{ $application->user->email }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-phone me-2 text-muted"></i>Téléphone
                            </label>
                            <div class="info-value">{{ $application->phone ?? 'Non renseigné' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-calendar me-2 text-muted"></i>Date de candidature
                            </label>
                            <div class="info-value text-start">{{ $application->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>
                    @if($application->user->created_at)
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-user-plus me-2 text-muted"></i>Membre depuis
                            </label>
                            <div class="info-value text-start">{{ $application->user->created_at->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    @endif
                    @if($application->reviewer)
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-user-check me-2 text-muted"></i>Révisé par
                            </label>
                            <div class="info-value text-start">{{ $application->reviewer->name }}</div>
                        </div>
                    </div>
                    @endif
                    @if($application->reviewed_at)
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-clock me-2 text-muted"></i>Date de révision
                            </label>
                            <div class="info-value text-start">{{ $application->reviewed_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Étape 1: Informations & Motivation -->
        <div class="col-12">
            <div class="admin-card">
                <h3 class="admin-card__title mb-4">
                    <span class="step-badge">1</span>
                    <i class="fas fa-info-circle me-2 text-primary"></i>Étape 1 : Informations & Motivation
                </h3>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-phone me-2 text-muted"></i>Téléphone
                            </label>
                            <div class="info-value text-start">{{ $application->phone ?? 'Non renseigné' }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-lightbulb me-2 text-muted"></i>Motivation
                            </label>
                            <div class="info-value info-text text-start">
                                {{ $application->motivation ?? 'Non renseigné' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Étape 2: Expérience & Présence -->
        <div class="col-12">
            <div class="admin-card">
                <h3 class="admin-card__title mb-4">
                    <span class="step-badge">2</span>
                    <i class="fas fa-briefcase me-2 text-primary"></i>Étape 2 : Expérience & Présence
                </h3>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-chart-line me-2 text-muted"></i>Expérience
                            </label>
                            <div class="info-value info-text text-start">
                                {{ $application->experience ?? 'Non renseigné' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-share-alt me-2 text-muted"></i>Présence sur les réseaux sociaux
                            </label>
                            <div class="info-value info-text text-start">
                                {{ $application->social_media_presence ?? 'Non renseigné' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-users me-2 text-muted"></i>Audience cible
                            </label>
                            <div class="info-value info-text text-start">
                                {{ $application->target_audience ?? 'Non renseigné' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Étape 3: Idées Marketing -->
        <div class="col-12">
            <div class="admin-card">
                <h3 class="admin-card__title mb-4">
                    <span class="step-badge">3</span>
                    <i class="fas fa-bullhorn me-2 text-primary"></i>Étape 3 : Idées Marketing
                </h3>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-lightbulb me-2 text-muted"></i>Idées marketing
                            </label>
                            <div class="info-value info-text text-start">
                                {{ $application->marketing_ideas ?? 'Non renseigné' }}
                            </div>
                        </div>
                    </div>
                    @if($application->document_path)
                    <div class="col-12">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-file-pdf me-2 text-muted"></i>Document complémentaire
                            </label>
                            <div class="info-value text-start">
                                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded border">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold mb-1">{{ basename($application->document_path) }}</div>
                                        <small class="text-muted d-block mb-2">Document PDF fourni par le candidat</small>
                                        <a href="{{ route('ambassador-application.download-document', $application) }}" 
                                           class="btn btn-outline-primary btn-sm" 
                                           target="_blank"
                                           onclick="event.preventDefault(); window.open(this.href, '_blank');">
                                            <i class="fas fa-download me-1"></i>Télécharger le document
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="col-12">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-file-pdf me-2 text-muted"></i>Document complémentaire
                            </label>
                            <div class="info-value text-start">
                                <span class="text-muted">Aucun document fourni</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Gestion de la candidature -->
        <div class="col-12">
            <div class="admin-card">
                <h3 class="admin-card__title mb-4">
                    <i class="fas fa-cog me-2 text-primary"></i>Gestion de la candidature
                </h3>

                <form method="POST" action="{{ route('admin.ambassadors.applications.update-status', $application) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-tag me-2"></i>Statut de la candidature
                                </label>
                                <select name="status" class="form-select form-select-lg" required>
                                    <option value="pending" {{ $application->status === 'pending' ? 'selected' : '' }}>
                                        ⏳ En attente
                                    </option>
                                    <option value="under_review" {{ $application->status === 'under_review' ? 'selected' : '' }}>
                                        🔍 En cours d'examen
                                    </option>
                                    <option value="approved" {{ $application->status === 'approved' ? 'selected' : '' }}>
                                        ✅ Approuvée
                                    </option>
                                    <option value="rejected" {{ $application->status === 'rejected' ? 'selected' : '' }}>
                                        ❌ Rejetée
                                    </option>
                                </select>
                                <small class="form-text text-muted">
                                    Le candidat sera notifié automatiquement du changement de statut.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-sticky-note me-2"></i>Notes administratives
                                </label>
                                <textarea name="admin_notes" 
                                          class="form-control" 
                                          rows="4" 
                                          placeholder="Ajoutez des notes internes sur cette candidature...">{{ $application->admin_notes }}</textarea>
                                <small class="form-text text-muted">
                                    Ces notes sont visibles uniquement par les administrateurs.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mt-4">
                        <div class="col-6">
                            <button type="submit" class="btn btn-light btn-primary-light w-100">
                                <i class="fas fa-save me-2"></i>Mettre à jour
                            </button>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.ambassadors.index', ['tab' => 'applications']) }}" class="btn btn-light btn-secondary-light w-100">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
            margin-right: 0.75rem;
        }

        .info-item {
            margin-bottom: 1.5rem;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1rem;
            color: #0f172a;
            font-weight: 500;
            word-break: break-word;
            text-align: left;
        }

        .admin-user-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            box-shadow: 0 6px 12px -6px rgba(15, 23, 42, 0.35);
        }

        .info-value a {
            color: #003366;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .info-value a:hover {
            color: #004080;
            text-decoration: underline;
        }

        .info-text {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            line-height: 1.7;
            white-space: pre-wrap;
            min-height: 60px;
            text-align: left;
        }

        .admin-card {
            margin-bottom: 1.5rem;
        }

        .admin-card__title {
            display: flex;
            align-items: center;
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .form-select-lg {
            font-size: 1rem;
            padding: 0.75rem 1rem;
        }

        /* Boutons avec style btn-light mais couleurs différentes */
        .btn-primary-light {
            background-color: #003366;
            border-color: #003366;
            color: #ffffff;
        }

        .btn-primary-light:hover {
            background-color: #004080;
            border-color: #004080;
            color: #ffffff;
        }

        .btn-secondary-light {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #6c757d;
        }

        .btn-secondary-light:hover {
            background-color: #e9ecef;
            border-color: #ced4da;
            color: #495057;
        }

        @media (max-width: 768px) {
            .info-item {
                margin-bottom: 1rem;
            }

            .admin-card__title {
                font-size: 1.1rem;
            }

            .step-badge {
                width: 28px;
                height: 28px;
                font-size: 0.8rem;
                margin-right: 0.5rem;
            }
        }
    </style>
    @endpush
@endsection
