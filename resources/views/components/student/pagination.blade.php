@props(['paginator', 'showInfo' => false, 'itemName' => 'éléments'])

@if ($paginator && $paginator->hasPages())
    <div class="student-pagination">
        @if($showInfo)
            <span class="student-pagination__info">
                Affichage de {{ $paginator->firstItem() ?? 0 }} à {{ $paginator->lastItem() ?? 0 }} sur {{ $paginator->total() }} {{ $itemName }}
            </span>
        @endif
        <div class="student-pagination__links">
            {{ $paginator->appends(request()->query())->links('pagination.bootstrap-5') }}
        </div>
    </div>
@endif

