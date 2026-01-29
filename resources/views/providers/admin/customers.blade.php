@extends('providers.admin.layout')

@section('admin-title', 'Clients & progrès')
@section('admin-subtitle', 'Analysez l'engagement de vos clients, contactez-les et suivez leur progression globale.')

@section('admin-actions')
    <a href="{{ route('provider.contents.index') }}" class="admin-btn outline">
        <i class="fas fa-chalkboard me-2"></i>Gérer mes contenus
    </a>
@endsection


@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <div class="admin-stats-grid mb-4">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Total d'inscriptions</p>
                    <p class="admin-stat-card__value">{{ number_format($enrollments->total()) }}</p>
                    <p class="admin-stat-card__muted">Inscriptions totales</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Progression moyenne</p>
                    <p class="admin-stat-card__value">{{ number_format($averageProgress, 1) }}%</p>
                    <p class="admin-stat-card__muted">Taux de complétion</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Clients actifs</p>
                    <p class="admin-stat-card__value">{{ number_format($activeCustomers) }}</p>
                    <p class="admin-stat-card__muted">Actifs (30 derniers jours)</p>
                </div>
            </div>

            <x-admin.search-panel
                :action="route('provider.customers')"
                formId="customersFilterForm"
                filtersId="customersFilters"
                :hasFilters="true"
                :searchValue="request('search')"
                placeholder="Rechercher par nom, email ou contenu..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Tri</label>
                            <select class="form-select" name="sort">
                                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date d'inscription</option>
                                <option value="progress" {{ request('sort') == 'progress' ? 'selected' : '' }}>Progression</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nom du client</option>
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
                        <a href="{{ route('provider.customers') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            <!-- Filtres actifs -->
            @if(request('search') || request('sort'))
            <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <i class="fas fa-filter me-2"></i><strong>Filtres actifs :</strong>
                    @if(request('search'))
                        <span class="badge bg-primary ms-2">Recherche: "{{ request('search') }}"</span>
                    @endif
                    @if(request('sort'))
                        <span class="badge bg-info ms-2">Tri: {{ ucfirst(request('sort')) }}</span>
                    @endif
                </div>
                <a href="{{ route('provider.customers') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-times me-1"></i>Effacer les filtres
                </a>
            </div>
            @endif

            <div id="bulkActionsContainer-providerCustomersTable"></div>

            <div class="admin-table">
                <div class="table-responsive">
                    <table class="table align-middle" id="providerCustomersTable" data-bulk-select="true" data-export-route="{{ route('provider.customers.export') }}">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" data-select-all data-table-id="providerCustomersTable" title="Sélectionner tout">
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Client
                                        @if(request('sort') == 'name')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Email</th>
                                <th>Contenu</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'progress', 'direction' => request('sort') == 'progress' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Progression
                                        @if(request('sort') == 'progress')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
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
                                <th style="width: 130px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($enrollments as $enrollment)
                            <tr>
                                <td>
                                    <input type="checkbox" data-item-id="{{ $enrollment->id }}" class="form-check-input">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $enrollment->user?->avatar_url ?? asset('images/default-avatar.png') }}" alt="{{ $enrollment->user?->name }}" class="admin-user-avatar">
                                        <div class="flex-grow-1 min-w-0">
                                            <a href="mailto:{{ $enrollment->user?->email }}" class="fw-semibold text-decoration-none text-dark text-truncate d-block">{{ $enrollment->user?->name ?? 'Utilisateur inconnu' }}</a>
                                            <div class="text-muted small text-truncate d-block">ID #{{ $enrollment->user?->id ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:{{ $enrollment->user?->email }}" class="text-decoration-none text-truncate d-block">{{ $enrollment->user?->email }}</a>
                                </td>
                                <td>
                                    <span class="admin-chip admin-chip--info text-truncate d-inline-block" style="max-width: 100%;">
                                        {{ $enrollment->course?->title ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="flex-grow-1" style="min-width: 80px;">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" role="progressbar" style="width: {{ $enrollment->progress }}%" aria-valuenow="{{ $enrollment->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <span class="admin-chip admin-chip--{{ $enrollment->progress >= 100 ? 'success' : ($enrollment->progress >= 50 ? 'info' : 'neutral') }}">
                                            {{ $enrollment->progress }}%
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="admin-chip admin-chip--neutral">{{ $enrollment->created_at ? $enrollment->created_at->format('d/m/Y') : '—' }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('contents.show', $enrollment->content?->slug ?? '#') }}" class="btn btn-light btn-sm" title="Voir le contenu" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun client inscrit pour le moment. Dès qu'un client rejoindra vos contenus, il apparaîtra ici.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <x-admin.pagination :paginator="$enrollments" :showInfo="true" itemName="inscriptions" />

            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script src="{{ asset('js/bulk-actions.js') }}"></script>
<script>
// Initialiser la sélection multiple
document.addEventListener('DOMContentLoaded', function() {
    // Créer et insérer la barre d'actions
    const container = document.getElementById('bulkActionsContainer-providerCustomersTable');
    if (container) {
        const bulkActionsBar = document.createElement('div');
        bulkActionsBar.id = 'bulkActionsBar-providerCustomersTable';
        bulkActionsBar.className = 'bulk-actions-bar';
        bulkActionsBar.style.display = 'none';
        bulkActionsBar.innerHTML = `
            <div class="bulk-actions-bar__content">
                <div class="bulk-actions-bar__info">
                    <span class="bulk-actions-bar__count" id="selectedCount-providerCustomersTable">0</span>
                    <span class="bulk-actions-bar__text">élément(s) sélectionné(s)</span>
                </div>
                <div class="bulk-actions-bar__actions">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown-providerCustomersTable" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i>Exporter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown-providerCustomersTable">
                            <li><a class="dropdown-item export-link" href="#" data-format="csv" data-table-id="providerCustomersTable"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                            <li><a class="dropdown-item export-link" href="#" data-format="excel" data-table-id="providerCustomersTable"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkActions.clearSelection('providerCustomersTable')">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                </div>
            </div>
        `;
        container.appendChild(bulkActionsBar);
        
        // Attacher les événements aux liens d'export après création de la barre
        setTimeout(() => {
            document.querySelectorAll('.export-link[data-table-id="providerCustomersTable"]').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const format = link.dataset.format;
                    const exportRoute = '{{ route('provider.customers.export') }}';
                    const url = new URL(exportRoute, window.location.origin);
                    url.searchParams.set('format', format);
                    
                    // Ajouter les filtres actuels
                    const currentParams = new URLSearchParams(window.location.search);
                    currentParams.forEach((value, key) => {
                        if (key !== 'page' && key !== 'format') {
                            url.searchParams.set(key, value);
                        }
                    });
                    
                    // Télécharger
                    const downloadLink = document.createElement('a');
                    downloadLink.href = url.toString();
                    downloadLink.style.display = 'none';
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);
                    
                    // Effacer la sélection
                    bulkActions.clearSelection('providerCustomersTable');
                });
            });
        }, 50);
    }
    
    bulkActions.init('providerCustomersTable', {
        exportRoute: '{{ route('provider.customers.export') }}'
    });
});

const customersFilterForm = document.getElementById('customersFilterForm');
const customersFiltersOffcanvas = document.getElementById('customersFilters');

if (customersFilterForm) {
    customersFilterForm.addEventListener('submit', () => {
        if (customersFiltersOffcanvas) {
            const instance = bootstrap.Offcanvas.getInstance(customersFiltersOffcanvas);
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

/* Gestion des contenus qui dépassent dans les colonnes */
.admin-table table td {
    word-wrap: break-word;
    overflow-wrap: break-word;
    max-width: 0;
}

/* Colonne Checkbox (1ère colonne) - ne pas limiter */
.admin-table table td:first-child {
    max-width: 50px;
    white-space: normal;
    overflow: visible;
}

/* Colonne Client (2ème colonne) */
.admin-table table td:nth-child(2) {
    max-width: 250px;
}

.admin-table table td:nth-child(2) > div {
    min-width: 0;
    flex: 1;
}

.admin-table table td:nth-child(2) > div > div {
    min-width: 0;
}

.admin-table table td:nth-child(2) a {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

.admin-table table td:nth-child(2) .text-muted {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

/* Colonne Email (3ème colonne) */
.admin-table table td:nth-child(3) {
    max-width: 200px;
}

.admin-table table td:nth-child(3) a {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

/* Colonne Contenu (4ème colonne) */
.admin-table table td:nth-child(4) {
    max-width: 300px;
}

.admin-table table td:nth-child(4) .admin-chip {
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Colonne Progression (5ème colonne) - ne pas limiter */
.admin-table table td:nth-child(5) {
    max-width: 180px;
    white-space: normal;
}

/* Colonne Inscription (6ème colonne) */
.admin-table table td:nth-child(6) {
    max-width: 120px;
    white-space: nowrap;
}

/* Colonne Actions (7ème colonne) - ne pas limiter */
.admin-table table td:nth-child(7) {
    max-width: 130px;
    white-space: normal;
    overflow: visible;
}

/* Utiliser text-truncate de Bootstrap pour les éléments avec cette classe */
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@media (max-width: 991.98px) {
    /* Sur tablette, permettre plus de flexibilité */
    .admin-table table td:nth-child(2) {
        max-width: 200px;
    }
    
    .admin-table table td:nth-child(3) {
        max-width: 150px;
    }
    
    .admin-table table td:nth-child(4) {
        max-width: 200px;
    }
}

@media (max-width: 767.98px) {
    /* Sur mobile, les colonnes sont empilées donc pas besoin de max-width */
    .admin-table table td {
        max-width: none;
    }
}
</style>
@endpush
