@props(['paginator', 'showInfo' => false, 'itemName' => 'éléments'])

@if ($paginator && $paginator->hasPages())
    <div class="admin-pagination">
        @if($showInfo)
            <span class="admin-pagination__info text-muted">
                Affichage de {{ $paginator->firstItem() ?? 0 }} à {{ $paginator->lastItem() ?? 0 }} sur {{ $paginator->total() }} {{ $itemName }}
            </span>
        @endif
        <div class="admin-pagination__links">
            {{ $paginator->appends(request()->query())->links('pagination.bootstrap-5') }}
        </div>
    </div>
@endif
