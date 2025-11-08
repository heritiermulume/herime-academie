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

