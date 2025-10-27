@props(['title', 'subtitle' => null, 'actions' => []])

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 admin-header">
            <div>
                <h1 class="h3 fw-bold mb-1">{{ $title }}</h1>
                @if($subtitle)
                    <p class="text-muted mb-0">{{ $subtitle }}</p>
                @endif
            </div>
            @if(count($actions) > 0)
                <div class="d-flex flex-column flex-md-row gap-2">
                    @foreach($actions as $action)
                        <a href="{{ $action['url'] }}" 
                           class="btn {{ $action['class'] ?? 'btn-primary' }} admin-btn-primary">
                            @if(isset($action['icon']))
                                <i class="{{ $action['icon'] }} me-2"></i>
                            @endif
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<style>
@media (max-width: 767.98px) {
    .admin-header {
        margin-bottom: 1rem;
    }
    
    .admin-header h1 {
        font-size: 1.5rem;
    }
    
    .admin-header .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>

