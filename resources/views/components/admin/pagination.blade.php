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
                // pour éviter les conflits avec le nom de page personnalisé
                if ($pageName && isset($queryParams['page'])) {
                    unset($queryParams['page']);
                }
                // Exclure également le nom de page personnalisé s'il existe dans les query params
                // car Laravel le gère automatiquement via le paginator
                if ($pageName && isset($queryParams[$pageName])) {
                    unset($queryParams[$pageName]);
                }
            @endphp
            {{ $paginator->appends($queryParams)->links('pagination.bootstrap-5') }}
        </div>
    </div>
@endif
