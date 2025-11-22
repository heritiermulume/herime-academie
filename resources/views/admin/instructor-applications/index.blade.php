@extends('layouts.admin')

@section('title', 'Candidatures formateur')
@section('admin-title', 'Candidatures formateur')
@section('admin-subtitle', 'Suivez, examinez et validez les demandes d’accès au statut de formateur')

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <div class="admin-stats-grid mb-4">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Total</p>
                    <p class="admin-stat-card__value">{{ $stats['total'] }}</p>
                    <p class="admin-stat-card__muted">Candidatures reçues</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">En attente</p>
                    <p class="admin-stat-card__value">{{ $stats['pending'] }}</p>
                    <p class="admin-stat-card__muted">En file de traitement</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">En examen</p>
                    <p class="admin-stat-card__value">{{ $stats['under_review'] }}</p>
                    <p class="admin-stat-card__muted">Analyse en cours</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Approuvées</p>
                    <p class="admin-stat-card__value">{{ $stats['approved'] }}</p>
                    <p class="admin-stat-card__muted">Prêtes à être activées</p>
                </div>
            </div>

            <x-admin.search-panel
                :action="route('admin.instructor-applications')"
                formId="applicationsFilterForm"
                filtersId="applicationsFilters"
                :hasFilters="true"
                :searchValue="request('search')"
                placeholder="Rechercher par nom ou email..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>En examen</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvée</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Filtrez rapidement les candidatures selon leur statut.</span>
                        <a href="{{ route('admin.instructor-applications') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            <div class="admin-table mt-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Date de soumission</th>
                                <th>Statut</th>
                                <th>Révisé par</th>
                                <th class="text-center" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $application)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $application->user->avatar_url }}"
                                                 alt="{{ $application->user->name }}"
                                                 class="admin-user-avatar">
                                            <div>
                                                <a href="{{ route('admin.users.show', $application->user) }}"
                                                   class="fw-semibold text-decoration-none text-dark">
                                                    {{ $application->user->name }}
                                                </a>
                                                <div class="text-muted small">{{ $application->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="admin-chip admin-chip--neutral">
                                            {{ $application->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match ($application->status) {
                                                'pending' => 'admin-chip--warning',
                                                'under_review' => 'admin-chip--info',
                                                'approved' => 'admin-chip--success',
                                                'rejected' => 'admin-chip--danger',
                                                default => 'admin-chip--neutral',
                                            };
                                        @endphp
                                        <span class="admin-chip {{ $statusClass }}">
                                            {{ $application->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($application->reviewer)
                                            <span class="admin-chip admin-chip--info">
                                                <i class="fas fa-user-check me-1"></i>{{ $application->reviewer->name }}
                                            </span>
                                        @else
                                            <span class="text-muted small">Non attribué</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.instructor-applications.show', $application) }}"
                                           class="btn btn-light btn-sm" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="admin-table__empty">
                                        <i class="fas fa-inbox mb-2 d-block"></i>
                                        Aucune candidature trouvée
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-admin.pagination :paginator="$applications" />
        </div>
    </section>
@endsection

@push('styles')
<style>
.admin-user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    box-shadow: 0 6px 12px -6px rgba(15, 23, 42, 0.35);
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
</style>
@endpush

@push('scripts')
<script>
const applicationsFilterForm = document.getElementById('applicationsFilterForm');
const applicationsFiltersOffcanvas = document.getElementById('applicationsFilters');

if (applicationsFilterForm) {
    applicationsFilterForm.addEventListener('submit', () => {
        if (applicationsFiltersOffcanvas) {
            const instance = bootstrap.Offcanvas.getInstance(applicationsFiltersOffcanvas);
            if (instance) {
                instance.hide();
            }
        }
    });
}

const applicationsSearchInput = document.querySelector('#applicationsFilterForm input[name="search"]');
if (applicationsSearchInput) {
    let searchTimeout;
    applicationsSearchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applicationsFilterForm?.submit();
        }, 500);
    });
}
</script>
@endpush

