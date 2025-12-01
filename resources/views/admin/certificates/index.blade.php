@extends('layouts.admin')

@section('title', 'Gestion des certificats')
@section('admin-title', 'Gestion des certificats')
@section('admin-subtitle', 'Consultez et gérez tous les certificats délivrés aux étudiants')

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
                placeholder="Rechercher par étudiant, cours ou numéro de certificat..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Cours</label>
                            <select class="form-select" name="course_id">
                                <option value="">Tous les cours</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ Str::limit($course->title, 50) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Étudiant</label>
                            <select class="form-select" name="user_id">
                                <option value="">Tous les étudiants</option>
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
            @if(request()->hasAny(['search', 'course_id', 'user_id']))
                <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                    <div>
                        <i class="fas fa-filter me-2"></i>
                        <strong>Filtres actifs</strong>
                        @if(request('search'))
                            | Recherche : <span class="fw-semibold">{{ request('search') }}</span>
                        @endif
                        @if(request('course_id'))
                            | Cours : <span class="fw-semibold">{{ $courses->firstWhere('id', request('course_id'))->title ?? 'Inconnu' }}</span>
                        @endif
                        @if(request('user_id'))
                            | Étudiant : <span class="fw-semibold">{{ $users->firstWhere('id', request('user_id'))->name ?? 'Inconnu' }}</span>
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
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'certificate_number', 'direction' => request('sort') == 'certificate_number' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Numéro
                                        @if(request('sort') == 'certificate_number')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Étudiant</th>
                                <th>Cours</th>
                                <th>Titre</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'issued_at', 'direction' => request('sort') == 'issued_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Date d'émission
                                        @if(request('sort') == 'issued_at')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-center d-none d-md-table-cell" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($certificates as $certificate)
                                <tr>
                                    <td>
                                        <span class="fw-semibold text-primary">{{ $certificate->certificate_number }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $certificate->user->avatar_url }}" 
                                                 alt="{{ $certificate->user->name }}" 
                                                 class="admin-user-avatar"
                                                 style="width: 40px; height: 40px;">
                                            <div>
                                                <div class="fw-semibold">{{ $certificate->user->name }}</div>
                                                <small class="text-muted">{{ $certificate->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-semibold">{{ Str::limit($certificate->course->title, 40) }}</div>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>
                                                {{ $certificate->course->instructor->name ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ Str::limit($certificate->title, 50) }}</span>
                                    </td>
                                    <td>
                                        <span class="admin-chip admin-chip--info">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $certificate->issued_at ? $certificate->issued_at->format('d/m/Y') : 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center align-top">
                                        @if($loop->first)
                                            <div class="dropdown d-none d-md-block">
                                                <button class="btn btn-sm btn-light course-actions-btn" type="button" id="actionsDropdown{{ $certificate->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown{{ $certificate->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.certificates.show', $certificate) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir les détails
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.certificates.download', $certificate) }}" target="_blank">
                                                            <i class="fas fa-download me-2"></i>Télécharger
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           data-certificate-id="{{ $certificate->id }}"
                                                           data-certificate-number="{{ $certificate->certificate_number }}"
                                                           onclick="openCertificateRegenerateModal(this); return false;">
                                                            <i class="fas fa-sync-alt me-2"></i>Régénérer
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-certificate-id="{{ $certificate->id }}"
                                                           data-certificate-number="{{ $certificate->certificate_number }}"
                                                           onclick="openCertificateDeleteModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @else
                                            <div class="dropup d-none d-md-block">
                                                <button class="btn btn-sm btn-light course-actions-btn" type="button" id="actionsDropdown{{ $certificate->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown{{ $certificate->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.certificates.show', $certificate) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir les détails
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.certificates.download', $certificate) }}" target="_blank">
                                                            <i class="fas fa-download me-2"></i>Télécharger
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           data-certificate-id="{{ $certificate->id }}"
                                                           data-certificate-number="{{ $certificate->certificate_number }}"
                                                           onclick="openCertificateRegenerateModal(this); return false;">
                                                            <i class="fas fa-sync-alt me-2"></i>Régénérer
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-certificate-id="{{ $certificate->id }}"
                                                           data-certificate-number="{{ $certificate->certificate_number }}"
                                                           onclick="openCertificateDeleteModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endif
                                        @if($loop->first)
                                            <div class="dropdown d-md-none">
                                                <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="actionsDropdownMobile{{ $certificate->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdownMobile{{ $certificate->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.certificates.show', $certificate) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.certificates.download', $certificate) }}" target="_blank">
                                                            <i class="fas fa-download me-2"></i>Télécharger
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           data-certificate-id="{{ $certificate->id }}"
                                                           data-certificate-number="{{ $certificate->certificate_number }}"
                                                           onclick="openCertificateRegenerateModal(this); return false;">
                                                            <i class="fas fa-sync-alt me-2"></i>Régénérer
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-certificate-id="{{ $certificate->id }}"
                                                           data-certificate-number="{{ $certificate->certificate_number }}"
                                                           onclick="openCertificateDeleteModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @else
                                            <div class="dropup d-md-none">
                                                <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="actionsDropdownMobile{{ $certificate->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdownMobile{{ $certificate->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.certificates.show', $certificate) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.certificates.download', $certificate) }}" target="_blank">
                                                            <i class="fas fa-download me-2"></i>Télécharger
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           data-certificate-id="{{ $certificate->id }}"
                                                           data-certificate-number="{{ $certificate->certificate_number }}"
                                                           onclick="openCertificateRegenerateModal(this); return false;">
                                                            <i class="fas fa-sync-alt me-2"></i>Régénérer
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-certificate-id="{{ $certificate->id }}"
                                                           data-certificate-number="{{ $certificate->certificate_number }}"
                                                           onclick="openCertificateDeleteModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endif
                                        <form id="certificate-delete-form-{{ $certificate->id }}" action="{{ route('admin.certificates.destroy', $certificate) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <form id="certificate-regenerate-form-{{ $certificate->id }}" action="{{ route('admin.certificates.regenerate', $certificate) }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="admin-table__empty">
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
    }
</style>
@endpush

