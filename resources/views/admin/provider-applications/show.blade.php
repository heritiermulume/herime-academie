@extends('layouts.admin')

@section('title', 'Détails de la Candidature - Admin')
@section('admin-title', 'Candidature de ' . $application->user->name)
@section('admin-subtitle', 'Détails de la candidature')
@section('admin-actions')
    <a href="{{ route('admin.provider-applications') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

{{-- Les messages seront affichés via toast --}}

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
                    <form method="POST" action="{{ route('admin.provider-applications.update-status', $application) }}" id="updateProviderApplicationStatusForm" onsubmit="return handleUpdateStatusSubmit(event)">
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
                            <a href="{{ route('provider-application.download-cv', $application) }}" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-file-pdf me-2"></i>Télécharger le CV
                            </a>
                        @else
                            <button class="btn btn-outline-secondary" disabled>
                                <i class="fas fa-file-pdf me-2"></i>CV non disponible
                            </button>
                        @endif

                        @if($application->motivation_letter_path)
                            <a href="{{ route('provider-application.download-motivation-letter', $application) }}" 
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

async function handleUpdateStatusSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const statusSelect = form.querySelector('#status');
    const currentStatus = '{{ $application->status }}';
    const newStatus = statusSelect.value;
    
    // Si le statut change vers "rejected", demander confirmation
    if (newStatus === 'rejected' && currentStatus !== 'rejected') {
        const message = 'Êtes-vous sûr de vouloir rejeter cette candidature ? Cette action enverra une notification au candidat.';
        
        const confirmed = await showModernConfirmModal(message, {
            title: 'Rejeter la candidature',
            confirmButtonText: 'Rejeter',
            confirmButtonClass: 'btn-danger',
            icon: 'fa-exclamation-triangle'
        });

        if (!confirmed) {
            return false;
        }
    }
    
    // Si le statut change vers "approved", demander confirmation
    if (newStatus === 'approved' && currentStatus !== 'approved') {
        const message = 'Êtes-vous sûr de vouloir approuver cette candidature ? L\'utilisateur recevra le rôle de prestataire.';
        
        const confirmed = await showModernConfirmModal(message, {
            title: 'Approuver la candidature',
            confirmButtonText: 'Approuver',
            confirmButtonClass: 'btn-success',
            icon: 'fa-check-circle'
        });

        if (!confirmed) {
            return false;
        }
    }
    
    // Soumettre le formulaire
    form.submit();
    return false;
}
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
