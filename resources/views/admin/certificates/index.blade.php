@extends('layouts.admin')

@section('title', 'Gestion des certificats')
@section('admin-title', 'Gestion des certificats')
@section('admin-subtitle', 'Consultez et gérez tous les certificats délivrés aux clients')

@push('modals')
    <!-- Modal de suppression -->
    <div class="modal fade" id="deleteCertificateModal" tabindex="-1" aria-labelledby="deleteCertificateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteCertificateModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Supprimer le certificat
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Êtes-vous sûr de vouloir supprimer le certificat <span id="certificateDeleteNumber" class="fw-semibold"></span> ?</p>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Cette action est irréversible et supprimera le certificat ainsi que le fichier PDF associé.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmCertificateDelete">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de régénération -->
    <div class="modal fade" id="regenerateCertificateModal" tabindex="-1" aria-labelledby="regenerateCertificateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="regenerateCertificateModalLabel">
                        <i class="fas fa-sync-alt me-2"></i>Régénérer le certificat
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Êtes-vous sûr de vouloir régénérer le certificat <span id="certificateRegenerateNumber" class="fw-semibold"></span> ?</p>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Le nouveau PDF remplacera l'ancien. Le numéro de certificat restera identique.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-warning" id="confirmCertificateRegenerate">
                        <i class="fas fa-sync-alt me-2"></i>Régénérer
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script>
    let certificateDeleteModal = null;
    let certificateRegenerateModal = null;
    let certificateFormToSubmit = null;
    let certificateNumberToDelete = '';
    let certificateNumberToRegenerate = '';

    function openCertificateDeleteModal(button) {
        const certificateId = button.getAttribute('data-certificate-id');
        const certificateNumber = button.getAttribute('data-certificate-number');
        const numberSpan = document.getElementById('certificateDeleteNumber');
        const form = document.getElementById(`certificate-delete-form-${certificateId}`);

        if (!certificateId || !form) return;

        certificateFormToSubmit = form;
        certificateNumberToDelete = certificateNumber ?? '';

        if (numberSpan) {
            numberSpan.textContent = certificateNumberToDelete;
        }

        const modalElement = document.getElementById('deleteCertificateModal');

        if (!modalElement) {
            console.error('Modal de suppression introuvable dans le DOM.');
            return;
        }

        if (!window.bootstrap || !window.bootstrap.Modal) {
            console.error('Bootstrap Modal n\'est pas chargé. Veuillez vérifier l\'inclusion de bootstrap.bundle.min.js.');
            return;
        }

        if (!certificateDeleteModal) {
            certificateDeleteModal = new window.bootstrap.Modal(modalElement);

            const confirmBtn = document.getElementById('confirmCertificateDelete');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function () {
                    if (certificateFormToSubmit) {
                        certificateDeleteModal.hide();
                        certificateFormToSubmit.submit();
                    }
                });
            }
        }

        certificateDeleteModal.show();
    }

    function openCertificateRegenerateModal(button) {
        const certificateId = button.getAttribute('data-certificate-id');
        const certificateNumber = button.getAttribute('data-certificate-number');
        const numberSpan = document.getElementById('certificateRegenerateNumber');
        const form = document.getElementById(`certificate-regenerate-form-${certificateId}`);

        if (!certificateId || !form) return;

        certificateFormToSubmit = form;
        certificateNumberToRegenerate = certificateNumber ?? '';

        if (numberSpan) {
            numberSpan.textContent = certificateNumberToRegenerate;
        }

        const modalElement = document.getElementById('regenerateCertificateModal');

        if (!modalElement) {
            console.error('Modal de régénération introuvable dans le DOM.');
            return;
        }

        if (!window.bootstrap || !window.bootstrap.Modal) {
            console.error('Bootstrap Modal n\'est pas chargé. Veuillez vérifier l\'inclusion de bootstrap.bundle.min.js.');
            return;
        }

        if (!certificateRegenerateModal) {
            certificateRegenerateModal = new window.bootstrap.Modal(modalElement);

            const confirmBtn = document.getElementById('confirmCertificateRegenerate');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function () {
                    if (certificateFormToSubmit) {
                        certificateRegenerateModal.hide();
                        certificateFormToSubmit.submit();
                    }
                });
            }
        }

        certificateRegenerateModal.show();
    }
</script>
@endpush

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <!-- Statistiques -->
            <div class="admin-stats-grid mb-4">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Total</p>
                    <p class="admin-stat-card__value">{{ $stats['total'] }}</p>
                    <p class="admin-stat-card__muted">Certificats délivrés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Ce mois</p>
                    <p class="admin-stat-card__value">{{ $stats['this_month'] }}</p>
                    <p class="admin-stat-card__muted">Certificats délivrés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Cette année</p>
                    <p class="admin-stat-card__value">{{ $stats['this_year'] }}</p>
                    <p class="admin-stat-card__muted">Certificats délivrés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Résultats</p>
                    <p class="admin-stat-card__value">{{ $certificates->total() }}</p>
                    <p class="admin-stat-card__muted">Correspondant à vos filtres</p>
                </div>
            </div>

            <!-- Filtres -->
            <x-admin.search-panel
                :action="route('admin.certificates')"
                formId="certificatesFilterForm"
                filtersId="certificatesFilters"
                :hasFilters="true"
                :searchValue="request('search')"
                placeholder="Rechercher par client, contenu ou numéro de certificat..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Cours</label>
                            <select class="form-select" name="content_id">
                                <option value="">Tous les cours</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ request('content_id') == $course->id ? 'selected' : '' }}>
                                        {{ Str::limit($course->title, 50) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Client</label>
                            <select class="form-select" name="user_id">
                                <option value="">Tous les clients</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Tri</label>
                            <select class="form-select" name="sort">
                                <option value="issued_at" {{ request('sort') == 'issued_at' ? 'selected' : '' }}>Date d'émission</option>
                                <option value="certificate_number" {{ request('sort') == 'certificate_number' ? 'selected' : '' }}>Numéro de certificat</option>
                                <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Titre</option>
                                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date de création</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Direction</label>
                            <select class="form-select" name="direction">
                                <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Décroissant</option>
                                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Croissant</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Ajustez les filtres puis appliquez-les.</span>
                        <a href="{{ route('admin.certificates') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            <!-- Filtres actifs -->
            @if(request()->hasAny(['search', 'content_id', 'user_id']))
                <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                    <div>
                        <i class="fas fa-filter me-2"></i>
                        <strong>Filtres actifs</strong>
                        @if(request('search'))
                            | Recherche : <span class="fw-semibold">{{ request('search') }}</span>
                        @endif
                        @if(request('content_id'))
                            | Contenu : <span class="fw-semibold">{{ $courses->firstWhere('id', request('content_id'))->title ?? 'Inconnu' }}</span>
                        @endif
                        @if(request('user_id'))
                            | Client : <span class="fw-semibold">{{ $users->firstWhere('id', request('user_id'))->name ?? 'Inconnu' }}</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.certificates') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-times me-1"></i>Effacer tous les filtres
                    </a>
                </div>
            @endif
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__body">
            <div class="admin-table">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 150px; max-width: 180px;">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'certificate_number', 'direction' => request('sort') == 'certificate_number' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Numéro
                                        @if(request('sort') == 'certificate_number')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th style="min-width: 200px; max-width: 250px;">Client</th>
                                <th style="min-width: 200px; max-width: 300px;">Contenu</th>
                                <th style="min-width: 180px; max-width: 250px;">Titre</th>
                                <th style="min-width: 140px; max-width: 160px; white-space: nowrap;">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'issued_at', 'direction' => request('sort') == 'issued_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Date d'émission
                                        @if(request('sort') == 'issued_at')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-center d-none d-md-table-cell" style="width: 150px; white-space: nowrap;">Actions</th>
                                <th class="text-center d-md-none" style="width: 150px; white-space: nowrap;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($certificates as $certificate)
                                <tr>
                                    <td style="max-width: 180px;">
                                        <span class="fw-semibold text-primary text-truncate d-block" style="max-width: 100%;" title="{{ $certificate->certificate_number }}">{{ $certificate->certificate_number }}</span>
                                    </td>
                                    <td style="max-width: 250px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $certificate->user->avatar_url }}" 
                                                 alt="{{ $certificate->user->name }}" 
                                                 class="admin-user-avatar flex-shrink-0"
                                                 style="width: 40px; height: 40px;">
                                            <div style="min-width: 0; flex: 1; overflow: hidden;">
                                                <div class="fw-semibold text-truncate d-block" style="max-width: 100%;" title="{{ $certificate->user->name }}">{{ $certificate->user->name }}</div>
                                                <small class="text-muted text-truncate d-block" style="max-width: 100%;" title="{{ $certificate->user->email }}">{{ $certificate->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="max-width: 300px;">
                                        <div style="min-width: 0; overflow: hidden;">
                                            <div class="fw-semibold text-truncate d-block" style="max-width: 100%;" title="{{ $certificate->course->title }}">{{ $certificate->course->title }}</div>
                                            <small class="text-muted text-truncate d-block" style="max-width: 100%;" title="{{ $certificate->course->provider->name ?? 'N/A' }}">
                                                <i class="fas fa-user me-1"></i>
                                                {{ $certificate->course->provider->name ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td style="max-width: 250px;">
                                        <span class="text-muted text-truncate d-block" style="max-width: 100%;" title="{{ $certificate->title }}">{{ $certificate->title }}</span>
                                    </td>
                                    <td style="max-width: 160px; white-space: nowrap;">
                                        <span class="admin-chip admin-chip--info">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $certificate->issued_at ? $certificate->issued_at->format('d/m/Y') : 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center d-none d-md-table-cell">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('admin.certificates.show', $certificate) }}" class="btn btn-light btn-sm" title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.certificates.download', $certificate) }}" target="_blank" class="btn btn-info btn-sm" title="Télécharger">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-warning btn-sm" 
                                                    data-certificate-id="{{ $certificate->id }}"
                                                    data-certificate-number="{{ $certificate->certificate_number }}"
                                                    onclick="openCertificateRegenerateModal(this)" 
                                                    title="Régénérer">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    data-certificate-id="{{ $certificate->id }}"
                                                    data-certificate-number="{{ $certificate->certificate_number }}"
                                                    onclick="openCertificateDeleteModal(this)" 
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <form id="certificate-delete-form-{{ $certificate->id }}" action="{{ route('admin.certificates.destroy', $certificate) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <form id="certificate-regenerate-form-{{ $certificate->id }}" action="{{ route('admin.certificates.regenerate', $certificate) }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </td>
                                    <td class="text-center d-md-none">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('admin.certificates.show', $certificate) }}" class="btn btn-light btn-sm" title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.certificates.download', $certificate) }}" target="_blank" class="btn btn-info btn-sm" title="Télécharger">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-warning btn-sm" 
                                                    data-certificate-id="{{ $certificate->id }}"
                                                    data-certificate-number="{{ $certificate->certificate_number }}"
                                                    onclick="openCertificateRegenerateModal(this)" 
                                                    title="Régénérer">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    data-certificate-id="{{ $certificate->id }}"
                                                    data-certificate-number="{{ $certificate->certificate_number }}"
                                                    onclick="openCertificateDeleteModal(this)" 
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="admin-table__empty">
                                        <i class="fas fa-certificate fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted">Aucun certificat trouvé avec ces critères.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-admin.pagination :paginator="$certificates" />
        </div>
    </section>
@endsection

@push('styles')
<style>
    .admin-user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Gestion des contenus qui dépassent dans les colonnes */
    .admin-table table td {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .admin-table table td .text-truncate {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
    }

    /* Colonne Numéro */
    .admin-table table td:first-child {
        max-width: 180px;
    }

    /* Colonne Client */
    .admin-table table td:nth-child(2) {
        max-width: 250px;
    }

    /* Colonne Contenu */
    .admin-table table td:nth-child(3) {
        max-width: 300px;
    }

    /* Colonne Titre */
    .admin-table table td:nth-child(4) {
        max-width: 250px;
    }

    /* Colonne Date d'émission */
    .admin-table table td:nth-child(5) {
        max-width: 160px;
    }

    /* Colonnes avec white-space: nowrap */
    .admin-table table td[style*="white-space: nowrap"] {
        white-space: nowrap !important;
    }

    @media (max-width: 991.98px) {
        .admin-panel {
            margin-bottom: 1rem;
        }
        
        .admin-panel--main .admin-panel__body {
            padding: 1rem !important;
        }
        
        .admin-panel:not(.admin-panel--main) .admin-panel__body {
            padding: 0 !important;
        }
        
        /* Ajuster les max-width sur tablette */
        .admin-table table td:first-child {
            max-width: 150px;
        }
        
        .admin-table table td:nth-child(2) {
            max-width: 200px;
        }
        
        .admin-table table td:nth-child(3) {
            max-width: 250px;
        }
        
        .admin-table table td:nth-child(4) {
            max-width: 200px;
        }
        
        .admin-table table td:nth-child(5) {
            max-width: 140px;
        }
    }

    @media (max-width: 767.98px) {
        .admin-panel {
            margin-bottom: 0.75rem;
        }
        
        .admin-panel--main .admin-panel__body {
            padding: 0.75rem !important;
        }
        
        .admin-panel:not(.admin-panel--main) .admin-panel__body {
            padding: 0 !important;
        }

        /* Supprimer les scrollbars des conteneurs, garder seulement celle de table-responsive */
        .admin-table {
            overflow: visible !important;
        }
        
        .admin-panel__body {
            overflow: visible !important;
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Ajuster les max-width sur mobile */
        .admin-table table td:first-child {
            max-width: 130px;
        }
        
        .admin-table table td:nth-child(2) {
            max-width: 180px;
        }
        
        .admin-table table td:nth-child(3) {
            max-width: 200px;
        }
        
        .admin-table table td:nth-child(4) {
            max-width: 180px;
        }
        
        .admin-table table td:nth-child(5) {
            max-width: 120px;
        }
    }
</style>
@endpush

