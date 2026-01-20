@extends('layouts.admin')

@section('title', 'Détails du certificat')
@section('admin-title', 'Détails du certificat')
@section('admin-subtitle', 'Informations complètes sur le certificat')

@section('admin-actions')
    <div class="admin-actions-grid">
        <a href="{{ route('admin.certificates') }}" class="btn btn-light">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
        <a href="{{ route('admin.certificates.download', $certificate) }}" class="btn btn-outline-primary" target="_blank">
            <i class="fas fa-download me-2"></i>Télécharger
        </a>
    </div>
@endsection

@push('modals')
    <!-- Modal de régénération -->
    <div class="modal fade" id="regenerateCertificateModalShow" tabindex="-1" aria-labelledby="regenerateCertificateModalShowLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="regenerateCertificateModalShowLabel">
                        <i class="fas fa-sync-alt me-2"></i>Régénérer le certificat
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Êtes-vous sûr de vouloir régénérer le certificat <span class="fw-semibold">{{ $certificate->certificate_number }}</span> ?</p>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Le nouveau PDF remplacera l'ancien. Le numéro de certificat restera identique.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('admin.certificates.regenerate', $certificate) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-sync-alt me-2"></i>Régénérer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script>
    function openCertificateRegenerateModalShow(button) {
        const modalElement = document.getElementById('regenerateCertificateModalShow');

        if (!modalElement) {
            console.error('Modal de régénération introuvable dans le DOM.');
            return;
        }

        if (!window.bootstrap || !window.bootstrap.Modal) {
            console.error('Bootstrap Modal n\'est pas chargé. Veuillez vérifier l\'inclusion de bootstrap.bundle.min.js.');
            return;
        }

        const modal = new window.bootstrap.Modal(modalElement);
        modal.show();
    }
</script>
@endpush

@push('styles')
<style>
/* Styles identiques à analytics */
/* Grid pour les boutons admin-actions en 2 colonnes sur mobile */
.admin-actions-grid {
    display: grid !important;
    grid-template-columns: repeat(2, auto) !important;
    gap: 0.5rem !important;
    justify-content: center !important;
    width: 100% !important;
}

/* Réduire la taille des boutons admin-actions */
.admin-content__actions .btn,
.admin-actions-grid .btn {
    font-size: 0.9rem !important;
    padding: 0.4rem 0.5rem !important;
    white-space: nowrap !important;
    width: auto !important;
    min-width: fit-content !important;
    text-align: center !important;
}

/* Ajouter une bordure visible sur les boutons outline */
.admin-content__actions .btn-outline-secondary,
.admin-actions-grid .btn-outline-secondary {
    border: 1px solid #6c757d !important;
    border-width: 1px !important;
}

.admin-content__actions .btn-outline-primary,
.admin-actions-grid .btn-outline-primary {
    border: 1px solid #0d6efd !important;
    border-width: 1px !important;
}

/* Taille des icônes dans les boutons */
.admin-content__actions .btn i,
.admin-actions-grid .btn i {
    font-size: 0.8rem !important;
}

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
    
    /* Centrer les boutons admin-actions sur mobile en 2 colonnes */
    .admin-content__header {
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
    }
    
    .admin-content__header > div:first-child {
        width: 100% !important;
        text-align: center !important;
        margin-bottom: 1rem !important;
    }
    
    .admin-content__actions,
    .admin-actions-grid {
        display: grid !important;
        grid-template-columns: repeat(2, auto) !important;
        gap: 0.4rem !important;
        justify-content: center !important;
        width: 100% !important;
    }
    
    .admin-content__actions .btn,
    .admin-actions-grid .btn {
        width: auto !important;
        min-width: fit-content !important;
        white-space: nowrap !important;
        font-size: 0.85rem !important;
        padding: 0.35rem 0.4rem !important;
        text-align: center !important;
    }
    
    .admin-content__actions .btn i,
    .admin-actions-grid .btn i {
        margin-right: 0.3rem !important;
        font-size: 0.75rem !important;
    }
}

/* Ajouter une bordure visible sur le bouton Régénérer */
.admin-panel__body button.btn-outline-warning,
.admin-panel__body .btn-outline-warning {
    border: 1px solid #ffc107 !important;
    border-width: 1px !important;
}
</style>
@endpush

@section('admin-content')
    <div class="row g-4">
        <div class="col-md-8">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-certificate me-2"></i>Informations du certificat
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Numéro de certificat</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-primary fs-6">{{ $certificate->certificate_number }}</span>
                        </dd>

                        <dt class="col-sm-4">Titre</dt>
                        <dd class="col-sm-8">{{ $certificate->title }}</dd>

                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $certificate->description ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Date d'émission</dt>
                        <dd class="col-sm-8">
                            <i class="fas fa-calendar me-2"></i>
                            {{ $certificate->issued_at ? $certificate->issued_at->format('d/m/Y à H:i') : 'N/A' }}
                        </dd>

                        <dt class="col-sm-4">Date de création</dt>
                        <dd class="col-sm-8">
                            {{ $certificate->created_at->format('d/m/Y à H:i') }}
                        </dd>

                        <dt class="col-sm-4">Fichier PDF</dt>
                        <dd class="col-sm-8">
                            @if($certificate->file_path)
                                <span class="badge bg-success">
                                    <i class="fas fa-file-pdf me-1"></i>Disponible
                                </span>
                                <a href="{{ route('admin.certificates.download', $certificate) }}" class="btn btn-sm btn-outline-primary ms-2" target="_blank">
                                    <i class="fas fa-download me-1"></i>Télécharger
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-warning ms-2" onclick="openCertificateRegenerateModalShow(this);">
                                    <i class="fas fa-sync-alt me-1"></i>Régénérer
                                </button>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Non disponible
                                </span>
                                <button type="button" class="btn btn-sm btn-primary ms-2" onclick="openCertificateRegenerateModalShow(this);">
                                    <i class="fas fa-sync-alt me-1"></i>Générer le certificat
                                </button>
                            @endif
                        </dd>
                    </dl>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-user-graduate me-2"></i>Client
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $certificate->user->avatar_url }}" 
                             alt="{{ $certificate->user->name }}" 
                             class="rounded-circle"
                             style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <h5 class="mb-1">{{ $certificate->user->name }}</h5>
                            <p class="text-muted mb-1">
                                <i class="fas fa-envelope me-2"></i>{{ $certificate->user->email }}
                            </p>
                            <a href="{{ route('admin.users.show', $certificate->user) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>Voir le profil
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-book me-2"></i>Contenu
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <h5 class="mb-2">{{ $certificate->course->title }}</h5>
                    @if($certificate->course->subtitle)
                        <p class="text-muted mb-3">{{ $certificate->course->subtitle }}</p>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <strong>Prestataire:</strong> 
                                {{ $certificate->course->provider->name ?? 'N/A' }}
                            </p>
                            <p class="mb-1">
                                <strong>Catégorie:</strong> 
                                {{ $certificate->course->category->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1">
                                <strong>Niveau:</strong> 
                                <span class="badge bg-info">{{ ucfirst($certificate->course->level ?? 'N/A') }}</span>
                            </p>
                            <p class="mb-1">
                                <strong>Langue:</strong> 
                                {{ strtoupper($certificate->course->language ?? 'N/A') }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.contents.show', $certificate->course) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>Voir le cours
                        </a>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-md-4">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-sync-alt me-2"></i>Actions sur le certificat
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <p class="text-muted small mb-3">
                        Régénérer le certificat recréera le fichier PDF avec les dernières informations du contenu et du client.
                    </p>
                    <button type="button" class="btn btn-warning w-100" onclick="openCertificateRegenerateModalShow(this);">
                        <i class="fas fa-sync-alt me-2"></i>Régénérer le certificat
                    </button>
                </div>
            </section>
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-exclamation-triangle me-2"></i>Zone de danger
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <p class="text-muted small mb-3">
                        La suppression du certificat est irréversible et supprimera également le fichier PDF associé.
                    </p>
                    <form action="{{ route('admin.certificates.destroy', $certificate) }}" method="POST" id="deleteForm">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce certificat ? Cette action est irréversible.');">
                            <i class="fas fa-trash me-2"></i>Supprimer le certificat
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection

