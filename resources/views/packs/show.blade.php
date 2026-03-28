@extends('layouts.app')

@section('title', ($package->meta_title ?: $package->title) . ' - Pack - Herime Académie')
@section('description', $package->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($package->short_description ?? $package->description ?? ''), 160))

@push('styles')
<style>
/* Aligné sur le fil d’Ariane de la page détail contenu (contents/show) */
.pack-show-hero {
    --accent-color: #ffcc33;
}

.breadcrumb-modern {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 0.625rem 1rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

.breadcrumb-modern .breadcrumb {
    margin: 0;
    padding: 0;
}

.breadcrumb-modern .breadcrumb-item {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
}

.breadcrumb-modern .breadcrumb-item a {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: all 0.3s ease;
}

.breadcrumb-modern .breadcrumb-item a:hover {
    color: var(--accent-color);
}

.breadcrumb-modern .breadcrumb-item.active {
    color: var(--accent-color);
    font-weight: 600;
}

.breadcrumb-modern .breadcrumb-item + .breadcrumb-item::before {
    color: rgba(255, 255, 255, 0.6);
    content: "›";
    padding: 0 0.5rem;
}

/* .sticky-top (Bootstrap) = z-index 1020 > menu bas mobile (1000) : la carte prix masquait la nav */
@media (max-width: 991.98px) {
    .pack-show-aside-card.sticky-top {
        z-index: 990 !important;
    }
}
</style>
@endpush

@section('content')
@php
    $listPrice = $package->contents_list_price_total;
    $effective = $package->effective_price;
    $savings = $listPrice > $effective ? $listPrice - $effective : 0;
@endphp
<section class="page-content-section" style="padding: 0 0 2rem;">
    <div class="pack-show-hero bg-primary text-white py-4 mb-4" style="--bs-bg-opacity: 1; background: linear-gradient(135deg, #003366 0%, #0a4d8c 100%) !important;">
        <div class="container">
            <nav aria-label="breadcrumb" class="breadcrumb-modern">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('contents.index') }}#content-packs">Contenus</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($package->title, 40) }}</li>
                </ol>
            </nav>
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    @if($package->is_featured)
                        <span class="badge bg-warning text-dark mb-2">Pack à la une</span>
                    @endif
                    <h1 class="display-6 fw-bold mb-2">{{ $package->title }}</h1>
                    @if($package->subtitle)
                        <p class="lead opacity-90 mb-3">{{ $package->subtitle }}</p>
                    @endif
                    @if($package->marketing_headline)
                        <p class="fs-5 mb-0 opacity-90">{{ $package->marketing_headline }}</p>
                    @endif
                </div>
                <div class="col-lg-5">
                    <div class="ratio ratio-16x9 rounded overflow-hidden shadow bg-dark bg-opacity-25">
                        @if($package->isYoutubeCoverVideo())
                            <iframe src="{{ $package->cover_video_url }}" title="Vidéo du pack" allowfullscreen class="border-0"></iframe>
                        @elseif($package->cover_video_url)
                            <video src="{{ $package->cover_video_url }}" controls playsinline preload="{{ in_array($p = config('video.player_preload', 'metadata'), ['none', 'metadata', 'auto'], true) ? $p : 'metadata' }}" class="w-100 h-100 object-fit-cover"></video>
                        @elseif($package->thumbnail_url)
                            <img src="{{ $package->thumbnail_url }}" alt="" class="w-100 h-100 object-fit-cover">
                        @else
                            <div class="d-flex align-items-center justify-content-center text-white-50"><i class="fas fa-box-open fa-4x"></i></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                @if($package->short_description)
                    <p class="lead">{{ $package->short_description }}</p>
                @endif

                @if(!empty($package->marketing_highlights))
                    <h2 class="h5 mt-4">Points clés</h2>
                    <ul class="list-unstyled">
                        @foreach($package->marketing_highlights as $line)
                            @if($line)
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>{{ $line }}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif

                @if(!empty($package->marketing_benefits))
                    <h2 class="h5 mt-4">Ce que vous gagnez</h2>
                    <ul>
                        @foreach($package->marketing_benefits as $line)
                            @if($line)<li>{{ $line }}</li>@endif
                        @endforeach
                    </ul>
                @endif

                @if($package->description)
                    <div class="content-description mt-4">
                        {!! $package->description !!}
                    </div>
                @endif

                <h2 class="h4 mt-5 mb-3">Contenus inclus ({{ $package->contents->count() }})</h2>
                <div class="list-group list-group-flush shadow-sm rounded border">
                    @foreach($package->contents as $course)
                        <a href="{{ route('contents.show', $course->slug) }}" class="list-group-item list-group-item-action d-flex gap-3 py-3">
                            <div class="flex-shrink-0 rounded overflow-hidden" style="width: 96px; height: 64px;">
                                @if($course->thumbnail_url)
                                    <img src="{{ $course->thumbnail_url }}" alt="" class="w-100 h-100 object-fit-cover">
                                @else
                                    <div class="bg-light w-100 h-100 d-flex align-items-center justify-content-center text-muted"><i class="fas fa-book"></i></div>
                                @endif
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold">{{ $course->title }}</div>
                                <div class="small text-muted">{{ $course->category->name ?? '' }} · {{ ucfirst($course->level) }}</div>
                                <div class="small mt-1">
                                    @if($course->is_free)
                                        <span class="badge bg-success">Gratuit</span>
                                    @else
                                        <span>{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->effective_price ?? $course->price) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="align-self-center text-muted"><i class="fas fa-chevron-right"></i></div>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top pack-show-aside-card" style="top: 1rem;">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            @if($package->thumbnail_url && !$package->isYoutubeCoverVideo() && !$package->cover_video)
                                <img src="{{ $package->thumbnail_url }}" alt="" class="img-fluid rounded mb-3" style="max-height: 180px; object-fit: cover;">
                            @endif
                            @if($savings > 0)
                                <div class="badge bg-success mb-2">Économisez {{ \App\Helpers\CurrencyHelper::formatWithSymbol($savings) }}</div>
                                <div class="small text-muted text-decoration-line-through">Valeur séparée : {{ \App\Helpers\CurrencyHelper::formatWithSymbol($listPrice) }}</div>
                            @endif
                            <div class="mt-2">
                                @if($package->is_sale_active)
                                    <span class="text-muted text-decoration-line-through d-block">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->price) }}</span>
                                    <span class="display-6 fw-bold text-success">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($effective) }}</span>
                                @else
                                    <span class="display-6 fw-bold text-primary">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($effective) }}</span>
                                @endif
                            </div>
                        </div>

                        @if($package->is_published && $package->is_sale_enabled)
                            <button type="button"
                                    class="btn btn-success btn-lg w-100"
                                    data-meta-trigger="checkout"
                                    onclick="proceedToCheckoutPackage({{ $package->id }})">
                                <i class="fas fa-credit-card me-2"></i>{{ $package->cta_label ?: 'Procéder au paiement' }}
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary btn-lg w-100" disabled>Indisponible</button>
                        @endif

                        <p class="small text-muted mt-3 mb-0 text-center">
                            Accès à tous les contenus listés après paiement.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
