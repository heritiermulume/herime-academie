@props([
    'action' => '#',
    'method' => 'GET',
    'searchName' => 'search',
    'searchValue' => '',
    'placeholder' => 'Rechercher…',
    'formId' => 'adminSearchForm',
    'filtersId' => null,
    'hasFilters' => false,
])

<div class="admin-search-panel">
    <form method="{{ $method }}" action="{{ $action }}" id="{{ $formId }}" class="admin-search-panel__form">
        <div class="admin-search-panel__primary">
            <div class="admin-search-panel__search">
                <label class="admin-search-panel__label" for="{{ $formId }}_search">Recherche</label>
                <div class="admin-search-panel__search-box">
                    <span class="admin-search-panel__icon" aria-hidden="true">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        name="{{ $searchName }}"
                        value="{{ $searchValue }}"
                        id="{{ $formId }}_search"
                        class="admin-search-panel__input"
                        placeholder="{{ $placeholder }}"
                        autocomplete="off"
                    >
                </div>
            </div>
            <div class="admin-search-panel__actions">
                <button type="submit" class="btn btn-primary admin-search-panel__submit">
                    <i class="fas fa-search"></i>
                    <span class="admin-search-panel__submit-label">Rechercher</span>
                </button>
                @if($hasFilters && $filtersId)
                    <button
                        type="button"
                        class="btn btn-outline-primary admin-search-panel__filters-toggle"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#{{ $filtersId }}"
                        aria-controls="{{ $filtersId }}"
                    >
                        <i class="fas fa-sliders-h"></i>
                        <span class="admin-search-panel__filters-label">Filtres</span>
                    </button>
                @endif
            </div>
        </div>

        @if(trim($slot))
            <div class="admin-search-panel__meta">
                {{ $slot }}
            </div>
        @endif

        @if($hasFilters && $filtersId)
            <div class="offcanvas offcanvas-end admin-filter-offcanvas" tabindex="-1" id="{{ $filtersId }}">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title"><i class="fas fa-sliders-h me-2"></i>Options de filtrage</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
                </div>
                <div class="offcanvas-body">
                    {{ $filters ?? '' }}
                </div>
                <div class="offcanvas-footer border-top d-flex justify-content-between gap-2 p-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
                        <i class="fas fa-times me-2"></i>Fermer
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Appliquer les filtres
                    </button>
                </div>
            </div>
        @endif
    </form>
</div>

@pushOnce('styles', 'admin-search-panel-styles')
<style>
    /* Desktop styles - Réduction de la taille du contenu */
    .admin-filter-offcanvas .offcanvas-header {
        padding: 0.875rem 1.125rem;
    }

    .admin-filter-offcanvas .offcanvas-title {
        font-size: 1rem;
    }

    .admin-filter-offcanvas .offcanvas-body {
        padding: 0.875rem;
        overflow-y: auto;
        max-height: calc(100vh - 150px);
    }

    .admin-filter-offcanvas .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.4rem;
    }

    .admin-filter-offcanvas .form-select,
    .admin-filter-offcanvas .form-control {
        font-size: 0.85rem;
        padding: 0.45rem 0.65rem;
    }

    .admin-filter-offcanvas .btn {
        font-size: 0.85rem;
        padding: 0.45rem 0.75rem;
    }

    .admin-filter-offcanvas .offcanvas-footer {
        padding: 0.75rem 1.125rem;
    }

    .admin-filter-offcanvas .mb-3 {
        margin-bottom: 0.875rem !important;
    }

    .admin-form-grid.admin-form-grid--two {
        gap: 0.75rem;
    }

    .admin-filter-offcanvas .row {
        --bs-gutter-y: 0.75rem;
        --bs-gutter-x: 0.75rem;
    }

    .admin-filter-offcanvas .gap-2 {
        gap: 0.5rem !important;
    }

    .admin-filter-offcanvas .d-flex.justify-content-between {
        gap: 0.75rem;
    }

    .admin-filter-offcanvas .text-muted.small {
        font-size: 0.8rem;
        line-height: 1.4;
    }

    .admin-filter-offcanvas .alert {
        padding: 0.6rem 0.875rem;
        font-size: 0.8rem;
        margin-bottom: 0.75rem;
    }

    .admin-filter-offcanvas .btn-outline-secondary {
        font-size: 0.85rem;
        padding: 0.45rem 0.75rem;
    }

    /* Sur toutes les tailles sauf desktop (>= 1200px), utiliser le layout mobile */
    @media (max-width: 1199.98px) {
        .admin-search-panel__primary {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.75rem !important;
        }

        .admin-search-panel__search {
            width: 100% !important;
            flex: none !important;
        }

        .admin-search-panel__actions {
            width: 100% !important;
            display: flex !important;
            gap: 0.5rem !important;
            flex-wrap: nowrap !important;
        }

        .admin-search-panel__actions .btn {
            flex: 1 1 50% !important;
            white-space: nowrap !important;
        }
    }
    
    /* Styles spécifiques pour tablette */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .admin-search-panel__actions .btn {
            font-size: 0.85rem;
            padding: 0.45rem 0.75rem;
        }
    }
    
    /* Styles pour la plage où le slider remonte */
    @media (min-width: 992px) and (max-width: 1199.98px) {
        .admin-search-panel__actions .btn {
            font-size: 0.85rem;
            padding: 0.5rem 0.8rem;
        }
    }

    @media (max-width: 768px) {
        .admin-search-panel__primary {
            flex-direction: column;
            gap: 0.75rem;
        }

        .admin-search-panel__search {
            width: 100%;
        }

        .admin-search-panel__actions {
            width: 100%;
            display: flex;
            gap: 0.5rem;
        }

        .admin-search-panel__actions .btn {
            flex: 1 1 50%;
            font-size: 0.85rem;
            padding: 0.45rem 0.75rem;
        }

        .reset-filters-btn {
            max-width: 180px;
            align-self: flex-end;
        }

        .admin-search-panel__submit-label,
        .admin-search-panel__filters-label {
            display: inline;
        }

        .admin-filter-offcanvas .offcanvas-header {
            padding: 0.75rem 1rem;
        }

        .admin-filter-offcanvas .offcanvas-title {
            font-size: 0.95rem;
        }

        .admin-filter-offcanvas .offcanvas-body {
            padding: 0.75rem;
            overflow-y: auto;
            max-height: calc(100vh - 140px);
        }

        .admin-filter-offcanvas .form-label {
            font-size: 0.8rem;
            margin-bottom: 0.35rem;
        }

        .admin-filter-offcanvas .form-select,
        .admin-filter-offcanvas .form-control {
            font-size: 0.8rem;
            padding: 0.4rem 0.55rem;
        }

        .admin-filter-offcanvas .btn {
            font-size: 0.8rem;
            padding: 0.4rem 0.65rem;
        }

        .admin-filter-offcanvas .mb-3 {
            margin-bottom: 0.75rem !important;
        }

        .admin-form-grid.admin-form-grid--two {
            gap: 0.5rem;
        }

        .admin-filter-offcanvas .row {
            --bs-gutter-y: 0.5rem;
            --bs-gutter-x: 0.5rem;
        }

        .admin-filter-offcanvas .gap-2 {
            gap: 0.4rem !important;
        }

        .admin-filter-offcanvas .d-flex.justify-content-between {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
    }

    @media (max-width: 576px) {
        .admin-search-panel__actions .btn {
            flex: 1 1 auto;
            font-size: 0.8rem;
        }

        .reset-filters-btn {
            max-width: 140px;
        }

        .admin-filter-offcanvas .offcanvas-header {
            padding: 0.5rem 0.75rem;
        }

        .admin-filter-offcanvas .offcanvas-title {
            font-size: 0.9rem;
        }

        .admin-filter-offcanvas .offcanvas-body {
            padding: 0.5rem;
            max-height: calc(100vh - 120px);
        }

        .admin-filter-offcanvas .form-label {
            font-size: 0.75rem;
            margin-bottom: 0.3rem;
        }

        .admin-filter-offcanvas .form-select,
        .admin-filter-offcanvas .form-control {
            font-size: 0.75rem;
            padding: 0.35rem 0.5rem;
        }

        .admin-filter-offcanvas .btn {
            width: 100%;
            font-size: 0.75rem;
            padding: 0.35rem 0.6rem;
        }

        .admin-filter-offcanvas .offcanvas-footer {
            flex-direction: column;
            gap: 0.4rem;
            padding: 0.5rem 0.75rem;
        }

        .admin-filter-offcanvas .mb-3 {
            margin-bottom: 0.5rem !important;
        }

        .admin-form-grid.admin-form-grid--two {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .admin-filter-offcanvas .d-flex.justify-content-between {
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .admin-filter-offcanvas .text-muted.small {
            font-size: 0.7rem;
            line-height: 1.3;
        }

        .admin-filter-offcanvas .alert {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .admin-filter-offcanvas .row {
            --bs-gutter-y: 0.4rem;
            --bs-gutter-x: 0.4rem;
        }

        .admin-filter-offcanvas .gap-2 {
            gap: 0.3rem !important;
        }

        .admin-filter-offcanvas .btn-outline-secondary {
            font-size: 0.75rem;
            padding: 0.35rem 0.6rem;
        }
    }

    @media (max-width: 480px) {
        .admin-filter-offcanvas .offcanvas-header {
            padding: 0.4rem 0.6rem;
        }

        .admin-filter-offcanvas .offcanvas-title {
            font-size: 0.85rem;
        }

        .admin-filter-offcanvas .offcanvas-body {
            padding: 0.4rem;
            max-height: calc(100vh - 110px);
        }

        .admin-filter-offcanvas .offcanvas-footer {
            padding: 0.4rem 0.6rem;
        }

        .admin-filter-offcanvas .form-label {
            font-size: 0.7rem;
            margin-bottom: 0.25rem;
        }

        .admin-filter-offcanvas .form-select,
        .admin-filter-offcanvas .form-control {
            font-size: 0.7rem;
            padding: 0.3rem 0.45rem;
        }

        .admin-filter-offcanvas .btn {
            font-size: 0.7rem;
            padding: 0.3rem 0.5rem;
        }

        .admin-filter-offcanvas .mb-3 {
            margin-bottom: 0.4rem !important;
        }

        .admin-form-grid.admin-form-grid--two {
            gap: 0.4rem;
        }

        .admin-filter-offcanvas .row {
            --bs-gutter-y: 0.3rem;
            --bs-gutter-x: 0.3rem;
        }

        .admin-filter-offcanvas .gap-2 {
            gap: 0.25rem !important;
        }

        .admin-filter-offcanvas .d-flex.justify-content-between {
            gap: 0.3rem;
        }

        .admin-filter-offcanvas .text-muted.small {
            font-size: 0.65rem;
        }

        .admin-filter-offcanvas .btn-outline-secondary {
            font-size: 0.7rem;
            padding: 0.3rem 0.5rem;
        }
    }

    @media (max-width: 480px) {
        .mobile-actions .mobile-action {
            font-size: 0.7rem;
        }

        .mobile-actions .mobile-action i {
            font-size: 1rem;
        }

        .reset-filters-btn {
            max-width: 120px;
        }
    }
</style>
@endPushOnce

