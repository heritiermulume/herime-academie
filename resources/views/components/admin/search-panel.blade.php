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
    <form method="{{ $method }}" action="{{ $action }}" id="{{ $formId }}">
        <div class="admin-search-panel__bar">
            <div class="admin-search-panel__input">
                <i class="fas fa-search"></i>
                <input
                    type="text"
                    name="{{ $searchName }}"
                    value="{{ $searchValue }}"
                    class="form-control"
                    placeholder="{{ $placeholder }}"
                    autocomplete="off"
                >
            </div>
            <div class="admin-search-panel__actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Rechercher
                </button>
                @if($hasFilters && $filtersId)
                    <button
                        type="button"
                        class="btn btn-outline-primary admin-search-panel__filters-toggle"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#{{ $filtersId }}"
                        aria-controls="{{ $filtersId }}"
                    >
                        <i class="fas fa-sliders-h me-2"></i>Filtres
                    </button>
                @endif
            </div>
        </div>

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

