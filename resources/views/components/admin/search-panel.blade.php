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

        .admin-filter-offcanvas .offcanvas-body {
            padding: 1rem;
        }

        .admin-filter-offcanvas .form-label {
            font-size: 0.85rem;
        }

        .admin-filter-offcanvas .form-select,
        .admin-filter-offcanvas .form-control {
            font-size: 0.85rem;
            padding: 0.45rem 0.6rem;
        }

        .admin-filter-offcanvas .btn {
            font-size: 0.85rem;
            padding: 0.45rem 0.75rem;
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

        .admin-filter-offcanvas .offcanvas-title {
            font-size: 1rem;
        }

        .admin-filter-offcanvas .btn {
            width: 100%;
        }

        .admin-filter-offcanvas .offcanvas-body {
            overflow-y: auto;
            max-height: calc(100vh - 160px);
        }

        .admin-filter-offcanvas .offcanvas-footer {
            flex-direction: column;
            gap: 0.5rem;
        }

        .admin-form-grid.admin-form-grid--two {
            grid-template-columns: 1fr;
            gap: 0.75rem;
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

