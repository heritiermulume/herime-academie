@props(['paginator', 'showInfo' => false, 'itemName' => 'éléments', 'pageName' => null])

@if ($paginator && $paginator->hasPages())
    <div class="admin-pagination">
        @if($showInfo)
            <span class="admin-pagination__info text-muted">
                Affichage de {{ $paginator->firstItem() ?? 0 }} à {{ $paginator->lastItem() ?? 0 }} sur {{ $paginator->total() }} {{ $itemName }}
            </span>
        @endif
        <div class="admin-pagination__links">
            @php
                $queryParams = request()->query();
                // Si un nom de page personnalisé est fourni, exclure le paramètre 'page' par défaut
                if ($pageName && isset($queryParams['page'])) {
                    unset($queryParams['page']);
                }
            @endphp
            {{ $paginator->appends($queryParams)->links('pagination.bootstrap-5') }}
        </div>
    </div>
@endif
