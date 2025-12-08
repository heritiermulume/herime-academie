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
    {{-- Les messages seront affichés via toast --}}

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
                                           download>
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

@push('scripts')
<script>
// Système de toast moderne
function showToast(message, type = 'success') {
    // Créer le conteneur de toast s'il n'existe pas
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }

    // Créer le toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    const icon = icons[type] || icons['info'];
    
    toast.innerHTML = `
        <div class="toast__icon">
            <i class="fas ${icon}"></i>
        </div>
        <div class="toast__content">
            <div class="toast__message">${message}</div>
        </div>
        <button class="toast__close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Ajouter le toast au conteneur
    toastContainer.appendChild(toast);

    // Animation d'entrée
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Suppression automatique après 4 secondes
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }, 4000);
}

// Afficher les messages de session Laravel avec des toasts
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif
    
    @if(session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif
    
    @if(session('info'))
        showToast('{{ session('info') }}', 'info');
    @endif
    
    @if($errors->any())
        @foreach($errors->all() as $error)
            showToast('{{ $error }}', 'error');
        @endforeach
    @endif
});
</script>

<style>
.toast-container {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-width: 400px;
    pointer-events: none;
}

.toast {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 40px -20px rgba(0, 0, 0, 0.3);
    border-left: 4px solid #22c55e;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    pointer-events: auto;
}

.toast.show {
    opacity: 1;
    transform: translateX(0);
}

.toast-success {
    border-left-color: #22c55e;
}

.toast-info {
    border-left-color: #3b82f6;
}

.toast-warning {
    border-left-color: #f59e0b;
}

.toast-error {
    border-left-color: #ef4444;
}

.toast__icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 0.875rem;
}

.toast-success .toast__icon {
    background: #dcfce7;
    color: #22c55e;
}

.toast-info .toast__icon {
    background: #dbeafe;
    color: #3b82f6;
}

.toast-warning .toast__icon {
    background: #fef3c7;
    color: #f59e0b;
}

.toast-error .toast__icon {
    background: #fee2e2;
    color: #ef4444;
}

.toast__content {
    flex: 1;
}

.toast__message {
    font-size: 0.875rem;
    font-weight: 500;
    color: #1f2937;
    line-height: 1.5;
}

.toast__close {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.2s;
    font-size: 0.75rem;
}

.toast__close:hover {
    background: #f3f4f6;
    color: #6b7280;
}

@media (max-width: 768px) {
    .toast-container {
        top: 70px;
        right: 10px;
        left: 10px;
        max-width: none;
    }

    .toast {
        padding: 0.875rem 1rem;
    }
}
</style>
@endpush
@endsection
