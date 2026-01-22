@extends('layouts.admin')

@section('title', 'Gestion des utilisateurs')
@section('admin-title', 'Gestion des utilisateurs')
@section('admin-subtitle', 'Supervisez les comptes créés via Compte Herime et ajustez leurs rôles')
@section('admin-actions')
    <div class="d-flex align-items-center gap-2 flex-wrap">
    <a href="https://compte.herime.com" class="btn btn-primary" target="_blank" rel="noopener">
        <i class="fas fa-user-plus me-2"></i>Nouvel utilisateur
    </a>
        <div class="alert alert-info mb-0 py-2 px-3">
            <i class="fas fa-info-circle me-2"></i>
            <small>La création se fait via Compte Herime (compte.herime.com)</small>
        </div>
    </div>
@endsection


@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <div class="admin-stats-grid mb-4">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Total</p>
                    <p class="admin-stat-card__value">{{ $stats['total'] }}</p>
                    <p class="admin-stat-card__muted">Utilisateurs enregistrés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Actifs</p>
                    <p class="admin-stat-card__value">{{ $stats['active'] }}</p>
                    <p class="admin-stat-card__muted">Accès autorisés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Clients</p>
                    <p class="admin-stat-card__value">{{ $stats['customers'] }}</p>
                    <p class="admin-stat-card__muted">Apprenants actifs</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Prestataires</p>
                    <p class="admin-stat-card__value">{{ $stats['providers'] }}</p>
                    <p class="admin-stat-card__muted">Experts pédagogiques</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Admins</p>
                    <p class="admin-stat-card__value">{{ $stats['admins'] }}</p>
                    <p class="admin-stat-card__muted">Équipe de pilotage</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Affiliés</p>
                    <p class="admin-stat-card__value">{{ $stats['affiliates'] }}</p>
                    <p class="admin-stat-card__muted">Partenaires commerciaux</p>
                </div>
            </div>

            <x-admin.search-panel
                :action="route('admin.users')"
                formId="usersFilterForm"
                filtersId="usersFilters"
                :hasFilters="true"
                :searchValue="request('search')"
                placeholder="Rechercher par nom ou email..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Rôle</label>
                            <select class="form-select" name="role">
                                <option value="">Tous les rôles</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                <option value="provider" {{ request('role') == 'provider' ? 'selected' : '' }}>Prestataire</option>
                                <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>Client</option>
                                <option value="affiliate" {{ request('role') == 'affiliate' ? 'selected' : '' }}>Affilié</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Vérifié</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Tri</label>
                            <select class="form-select" name="sort">
                                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date d'inscription</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nom</option>
                                <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>Email</option>
                                <option value="role" {{ request('sort') == 'role' ? 'selected' : '' }}>Rôle</option>
                                <option value="last_login_at" {{ request('sort') == 'last_login_at' ? 'selected' : '' }}>Dernière connexion</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Ajustez les filtres puis appliquez-les.</span>
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            <!-- Filtres actifs -->
            @if(request('search') || request('role') || request('status'))
            <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <i class="fas fa-filter me-2"></i><strong>Filtres actifs :</strong>
                    @if(request('search'))
                        <span class="badge bg-primary ms-2">Recherche: "{{ request('search') }}"</span>
                    @endif
                    @if(request('role'))
                        <span class="badge bg-info ms-2">Rôle: {{ ucfirst(request('role')) }}</span>
                    @endif
                    @if(request('status'))
                        <span class="badge bg-warning ms-2">Statut: {{ ucfirst(request('status')) }}</span>
                    @endif
                </div>
                <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-times me-1"></i>Effacer les filtres
                </a>
            </div>
            @endif

            <div id="bulkActionsContainer-usersTable"></div>

            <div class="admin-table">
                <div class="table-responsive">
                    <table class="table align-middle" id="usersTable" data-bulk-select="true" data-export-route="{{ route('admin.users.export') }}">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" data-select-all data-table-id="usersTable" title="Sélectionner tout">
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Utilisateur
                                        @if(request('sort') == 'name')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Inscription
                                        @if(request('sort') == 'created_at')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'last_login_at', 'direction' => request('sort') == 'last_login_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Dernière connexion
                                        @if(request('sort') == 'last_login_at')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th style="width: 130px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>
                                    <input type="checkbox" data-item-id="{{ $user->id }}" class="form-check-input">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="admin-user-avatar">
                                        <div>
                                            <a href="{{ route('admin.users.show', $user) }}" class="fw-semibold text-decoration-none text-dark">{{ $user->name }}</a>
                                            <div class="text-muted small">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="admin-chip admin-chip--info text-uppercase">
                                        <i class="fas fa-user-tag me-1"></i>
                                        @switch($user->role)
                                            @case('admin')
                                                Administrateur
                                                @break
                                            @case('provider')
                                                Prestataire
                                                @break
                                            @case('affiliate')
                                                Affilié
                                                @break
                                            @case('customer')
                                                Client
                                                @break
                                            @default
                                                {{ ucfirst($user->role ?? 'utilisateur') }}
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="admin-chip {{ $user->is_active ? 'admin-chip--success' : 'admin-chip--neutral' }}">
                                            {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                        @if($user->is_verified)
                                            <span class="admin-chip admin-chip--success">
                                                <i class="fas fa-check-circle me-1"></i>Vérifié
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="admin-chip admin-chip--neutral">{{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}</span>
                                </td>
                                <td>
                                    @if($user->last_login_at)
                                        <span class="admin-chip admin-chip--info">{{ $user->last_login_at->diffForHumans() }}</span>
                                    @else
                                        <span class="text-muted small">Jamais connecté</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-light btn-sm" title="Voir l'utilisateur">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm" title="Modifier l'utilisateur">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteUser({{ $user->id }})" title="Supprimer l'utilisateur">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun utilisateur trouvé</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <x-admin.pagination :paginator="$users" :showInfo="true" itemName="utilisateurs" />

            </div>
        </div>
    </section>

<!-- Modal de confirmation de suppression -->
@endsection

@push('scripts')
<script src="{{ asset('js/bulk-actions.js') }}"></script>
<script>
// Initialiser la sélection multiple
document.addEventListener('DOMContentLoaded', function() {
    // Créer et insérer la barre d'actions
    const container = document.getElementById('bulkActionsContainer-usersTable');
    if (container) {
        const bulkActionsBar = document.createElement('div');
        bulkActionsBar.id = 'bulkActionsBar-usersTable';
        bulkActionsBar.className = 'bulk-actions-bar';
        bulkActionsBar.style.display = 'none';
        bulkActionsBar.innerHTML = `
            <div class="bulk-actions-bar__content">
                <div class="bulk-actions-bar__info">
                    <span class="bulk-actions-bar__count" id="selectedCount-usersTable">0</span>
                    <span class="bulk-actions-bar__text">élément(s) sélectionné(s)</span>
                </div>
                <div class="bulk-actions-bar__actions">
                    <button type="button" class="btn btn-sm btn-danger bulk-action-btn" data-action="delete" data-table-id="usersTable" data-confirm="true" data-confirm-message="Êtes-vous sûr de vouloir supprimer les utilisateurs sélectionnés ?" data-route="{{ route('admin.users.bulk-action') }}" data-method="POST">
                        <i class="fas fa-trash me-1"></i>Supprimer
                    </button>
                    <button type="button" class="btn btn-sm btn-success bulk-action-btn" data-action="activate" data-table-id="usersTable" data-confirm="false" data-route="{{ route('admin.users.bulk-action') }}" data-method="POST">
                        <i class="fas fa-check me-1"></i>Activer
                    </button>
                    <button type="button" class="btn btn-sm btn-warning bulk-action-btn" data-action="deactivate" data-table-id="usersTable" data-confirm="false" data-route="{{ route('admin.users.bulk-action') }}" data-method="POST">
                        <i class="fas fa-ban me-1"></i>Désactiver
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown-usersTable" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i>Exporter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown-usersTable">
                            <li><a class="dropdown-item export-link" href="#" data-format="csv" data-table-id="usersTable"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                            <li><a class="dropdown-item export-link" href="#" data-format="excel" data-table-id="usersTable"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkActions.clearSelection('usersTable')">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                </div>
            </div>
        `;
        container.appendChild(bulkActionsBar);
    }
    
    bulkActions.init('usersTable', {
        exportRoute: '{{ route('admin.users.export') }}'
    });
});

async function deleteUser(userId) {
    const message = 'Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.';
    
    const confirmed = await showModernConfirmModal(message, {
        title: 'Supprimer l\'utilisateur',
        confirmButtonText: 'Supprimer',
        confirmButtonClass: 'btn-danger',
        icon: 'fa-exclamation-triangle'
    });

    if (confirmed) {
        // Créer un formulaire pour la suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

const usersFilterForm = document.getElementById('usersFilterForm');
const usersFiltersOffcanvas = document.getElementById('usersFilters');

if (usersFilterForm) {
    usersFilterForm.addEventListener('submit', () => {
        if (usersFiltersOffcanvas) {
            const instance = bootstrap.Offcanvas.getInstance(usersFiltersOffcanvas);
            if (instance) {
                instance.hide();
            }
        }
    });
}

</script>
@endpush

@push('styles')
<style>
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
    
    .admin-card__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-card__body {
        padding: 0.5rem;
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
    
    .admin-card__header {
        padding: 0.5rem 0.625rem;
    }
    
    .admin-card__body {
        padding: 0.375rem;
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
.admin-user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    box-shadow: 0 6px 12px -6px rgba(15, 23, 42, 0.35);
}
</style>
@endpush
