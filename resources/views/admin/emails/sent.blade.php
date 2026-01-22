@extends('layouts.admin')

@section('title', 'Emails envoyés')
@section('admin-title', 'Emails envoyés')
@section('admin-subtitle', 'Consultez l\'historique de tous les emails envoyés')
@section('admin-actions')
    <a href="{{ route('admin.announcements.send-email') }}" class="btn btn-success me-2">
        <i class="fas fa-envelope me-2"></i>Envoyer un email
    </a>
    <a href="{{ route('admin.announcements') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour aux annonces
    </a>
@endsection

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <!-- Statistiques -->
            <div class="admin-stats-grid mb-4">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Total</p>
                    <p class="admin-stat-card__value">{{ $stats['total'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">Emails envoyés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Envoyés</p>
                    <p class="admin-stat-card__value">{{ $stats['sent'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">Avec succès</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Échoués</p>
                    <p class="admin-stat-card__value">{{ $stats['failed'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">En erreur</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">En attente</p>
                    <p class="admin-stat-card__value">{{ $stats['pending'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">Non envoyés</p>
                </div>
            </div>

            <!-- Filtres et recherche -->
            <x-admin.search-panel
                :action="route('admin.emails.sent')"
                formId="sentEmailsFilterForm"
                filtersId="sentEmailsFilters"
                :hasFilters="true"
                :searchValue="request('search')"
                placeholder="Rechercher par sujet, email ou destinataire..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Type</label>
                            <select class="form-select" name="type">
                                <option value="">Tous les types</option>
                                <option value="invoice" {{ request('type') == 'invoice' ? 'selected' : '' }}>Facture</option>
                                <option value="enrollment" {{ request('type') == 'enrollment' ? 'selected' : '' }}>Inscription</option>
                                <option value="announcement" {{ request('type') == 'announcement' ? 'selected' : '' }}>Annonce</option>
                                <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Personnalisé</option>
                                <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>Paiement</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Envoyé</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échoué</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Date de début</label>
                            <input type="date" class="form-select" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Date de fin</label>
                            <input type="date" class="form-select" name="date_to" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Ajustez les filtres puis appliquez-les.</span>
                        <a href="{{ route('admin.emails.sent') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            <div id="bulkActionsContainer-sentEmailsTable"></div>

            <!-- Table des emails envoyés -->
            <div class="admin-table mt-4">
                <div class="table-responsive">
                    <table class="table table-hover" id="sentEmailsTable" data-bulk-select="true">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px; min-width: 50px; max-width: 50px;">
                                    <input type="checkbox" data-select-all data-table-id="sentEmailsTable" title="Sélectionner tout">
                                </th>
                                <th style="width: 50px; min-width: 50px; max-width: 50px;"></th>
                                <th style="min-width: 150px; max-width: 200px;">Destinataire</th>
                                <th style="min-width: 200px; max-width: 300px;">Sujet</th>
                                <th style="width: 100px; min-width: 100px; max-width: 120px;">Type</th>
                                <th style="width: 120px; min-width: 120px; max-width: 120px;">Statut</th>
                                <th style="min-width: 140px; max-width: 150px;">Date d'envoi</th>
                                <th style="width: 120px; min-width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($emails as $email)
                            <tr>
                                <td>
                                    <input type="checkbox" data-item-id="{{ $email->id }}" class="form-check-input">
                                </td>
                                <td class="align-middle" style="width: 50px; min-width: 50px; padding: 0.5rem; vertical-align: middle;">
                                    @php
                                        $recipientUser = $email->recipient_user ?? null;
                                        $avatarUrl = $recipientUser
                                            ? $recipientUser->avatar_url
                                            : 'https://ui-avatars.com/api/?name=' . urlencode($email->recipient_name ?? 'N/A') . '&background=003366&color=fff&size=128';
                                    @endphp
                                    <div class="email-avatar-container" style="width: 40px !important; height: 40px !important; min-width: 40px !important; min-height: 40px !important; max-width: 40px !important; max-height: 40px !important; border-radius: 50% !important; overflow: hidden !important; display: inline-block !important;">
                                        <img src="{{ $avatarUrl }}"
                                             alt="{{ $email->recipient_name ?? 'N/A' }}"
                                             class="email-avatar"
                                             style="border-radius: 50% !important; width: 100% !important; height: 100% !important; object-fit: cover !important; display: block !important;"
                                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($email->recipient_name ?? 'N/A') }}&background=003366&color=fff&size=128'">
                                    </div>
                                </td>
                                <td style="max-width: 200px;">
                                    <strong class="d-block text-truncate" title="{{ $email->recipient_name ?? 'N/A' }}">{{ $email->recipient_name ?? 'N/A' }}</strong>
                                    <small class="text-muted d-block text-truncate" title="{{ $email->recipient_email }}">{{ $email->recipient_email }}</small>
                                </td>
                                <td style="max-width: 300px;">
                                    <strong class="d-block text-truncate" title="{{ $email->subject }}">{{ $email->subject }}</strong>
                                </td>
                                <td style="max-width: 120px;">
                                    <span class="badge bg-info">{{ ucfirst($email->type) }}</span>
                                </td>
                                <td style="max-width: 120px;">
                                    @if($email->status === 'sent')
                                        <span class="badge bg-success">Envoyé</span>
                                    @elseif($email->status === 'failed')
                                        <span class="badge bg-danger" title="{{ $email->error_message }}">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Échoué
                                        </span>
                                    @else
                                        <span class="badge bg-warning">En attente</span>
                                    @endif
                                </td>
                                <td style="max-width: 150px;">
                                    <small class="d-block text-truncate" title="{{ $email->sent_at ? $email->sent_at->format('d/m/Y à H:i') : ($email->created_at->format('d/m/Y à H:i')) }}">
                                        {{ $email->sent_at ? $email->sent_at->format('d/m/Y à H:i') : ($email->created_at->format('d/m/Y à H:i')) }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.emails.sent.show', $email) }}" class="btn btn-sm btn-light" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun email envoyé trouvé</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <x-admin.pagination :paginator="$emails" />
        </div>
    </section>
@endsection

@push('styles')
<style>
/* Gestion du débordement de texte dans les colonnes - masquer avec ellipses */
.admin-table table tbody td {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Colonne Destinataire (3ème colonne après checkbox et avatar) - permet 2 lignes */
.admin-table table tbody td:nth-child(3) {
    white-space: normal;
    line-height: 1.4;
}

.admin-table table tbody td:nth-child(3) strong,
.admin-table table tbody td:nth-child(3) small {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

/* Colonne Sujet (4ème colonne) */
.admin-table table tbody td:nth-child(4) {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Colonnes Type et Statut (5ème et 6ème colonnes) */
.admin-table table tbody td:nth-child(5),
.admin-table table tbody td:nth-child(6) {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Colonne Date d'envoi (7ème colonne) */
.admin-table table tbody td:nth-child(7) {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Colonne Actions (8ème colonne) - ne pas limiter */
.admin-table table tbody td:nth-child(8) {
    white-space: normal;
    overflow: visible;
    text-overflow: clip;
}

/* Colonnes checkbox et avatar (1ère et 2ème) - ne pas limiter */
.admin-table table tbody td:nth-child(1),
.admin-table table tbody td:nth-child(2) {
    white-space: normal;
    overflow: visible;
    text-overflow: clip;
}

/* Utiliser text-truncate de Bootstrap pour les éléments avec cette classe */
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@media (max-width: 991.98px) {
    /* Réduire les paddings et margins sur tablette */
    .admin-panel {
        margin-bottom: 1rem;
    }
    
    /* Padding uniquement pour la première section principale */
    .admin-panel--main .admin-panel__body {
        padding: 1rem !important;
    }
    
    /* Pas de padding pour les autres sections */
    .admin-panel:not(.admin-panel--main) .admin-panel__body {
        padding: 0 !important;
    }
    
    .admin-panel__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-panel__header h3 {
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    
    .admin-stats-grid {
        gap: 0.5rem !important;
    }
    
    .admin-stat-card {
        padding: 0.75rem 0.875rem !important;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.5rem;
        --bs-gutter-y: 0.5rem;
    }
    
    .admin-panel__body .row.g-3 {
        --bs-gutter-x: 0.375rem;
        --bs-gutter-y: 0.375rem;
    }
    
    .admin-panel__body .row.mb-4 {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-panel__body .row.mt-2 {
        margin-top: 0.375rem !important;
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
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }
    
    .table-responsive::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }
}

@media (max-width: 767.98px) {
    /* Réduire encore plus les paddings et margins sur mobile */
    .admin-panel {
        margin-bottom: 0.75rem;
    }
    
    /* Padding uniquement pour la première section principale */
    .admin-panel--main .admin-panel__body {
        padding: 0.75rem !important;
    }
    
    /* Pas de padding pour les autres sections */
    .admin-panel:not(.admin-panel--main) .admin-panel__body {
        padding: 0 !important;
    }
    
    .admin-panel__header {
        padding: 0.375rem 0.5rem;
    }
    
    .admin-panel__header h3 {
        font-size: 0.95rem;
        margin-bottom: 0.125rem;
    }
    
    .admin-stats-grid {
        gap: 0.375rem !important;
    }
    
    .admin-stat-card {
        padding: 0.5rem 0.625rem !important;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.375rem;
        --bs-gutter-y: 0.375rem;
    }
    
    .admin-panel__body .row.g-3 {
        --bs-gutter-x: 0.25rem;
        --bs-gutter-y: 0.25rem;
    }
    
    .admin-panel__body .row.mb-4 {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-panel__body .row.mt-2 {
        margin-top: 0.375rem !important;
    }
    
    /* Supprimer les scrollbars des conteneurs sur mobile */
    .admin-table {
        overflow: visible !important;
    }
    
    .admin-panel__body {
        overflow: visible !important;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }
    
    .table-responsive::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/bulk-actions.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les bulk actions pour la liste des emails envoyés
    const sentEmailsContainer = document.getElementById('bulkActionsContainer-sentEmailsTable');
    if (sentEmailsContainer) {
        const bar = document.createElement('div');
        bar.id = 'bulkActionsBar-sentEmailsTable';
        bar.className = 'bulk-actions-bar';
        bar.style.display = 'none';
        bar.innerHTML = `
            <div class="bulk-actions-bar__content">
                <div class="bulk-actions-bar__info">
                    <span class="bulk-actions-bar__count" id="selectedCount-sentEmailsTable">0</span>
                    <span class="bulk-actions-bar__text">élément(s) sélectionné(s)</span>
                </div>
                <div class="bulk-actions-bar__actions">
                    <button type="button" class="btn btn-sm btn-danger bulk-action-btn" data-action="delete" data-table-id="sentEmailsTable" data-confirm="true" data-confirm-message="Êtes-vous sûr de vouloir supprimer les emails sélectionnés ?" data-route="{{ route('admin.emails.sent.bulk-action') }}" data-method="POST">
                        <i class="fas fa-trash me-1"></i>Supprimer
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown-sentEmailsTable" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i>Exporter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown-sentEmailsTable">
                            <li><a class="dropdown-item export-link" href="#" data-format="csv" data-table-id="sentEmailsTable"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                            <li><a class="dropdown-item export-link" href="#" data-format="excel" data-table-id="sentEmailsTable"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkActions.clearSelection('sentEmailsTable')">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                </div>
            </div>
        `;
        sentEmailsContainer.appendChild(bar);
    }
    bulkActions.init('sentEmailsTable', {
        exportRoute: '{{ route('admin.emails.sent.export') }}'
    });
});

async function openDeleteEmailModal(button) {
    const action = button?.dataset?.action;
    if (!action) {
        console.error('Aucune action de suppression fournie.');
        return;
    }

    const subject = button.dataset.subject || '';
    const message = subject
        ? `Êtes-vous sûr de vouloir supprimer l'email « ${subject} » ? Cette action est irréversible.`
        : `Êtes-vous sûr de vouloir supprimer cet email ? Cette action est irréversible.`;
    
    const confirmed = await showModernConfirmModal(message, {
        title: 'Supprimer l\'email',
        confirmButtonText: 'Supprimer',
        confirmButtonClass: 'btn-danger',
        icon: 'fa-exclamation-triangle'
    });
    
    if (confirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        
        // Ajouter le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
        
        // Ajouter la méthode DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush

<!-- Modal de suppression d'email -->
<div class="modal fade" id="deleteEmailModal" tabindex="-1" aria-labelledby="deleteEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEmailModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p id="deleteEmailMessage">Êtes-vous sûr de vouloir supprimer cet email ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteEmailForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

