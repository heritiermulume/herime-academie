@extends('layouts.app')

@section('title', 'Packs de formation - Herime Académie')
@section('description', 'Profitez de packs regroupant plusieurs contenus à prix avantageux.')

@section('content')
<section class="page-content-section" style="padding: 1rem 0 2rem;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8">
                <h1 class="courses-page-title">Packs de contenus</h1>
                <p class="courses-page-description mb-0">
                    Des parcours clés en main : plusieurs formations regroupées avec une offre marketing dédiée.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end align-self-center">
                <a href="{{ route('contents.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-book me-2"></i>Voir tous les contenus
                </a>
            </div>
        </div>

        <div class="row g-4">
            @forelse($packages as $package)
                <div class="col-md-6 col-xl-4">
                    <article class="card h-100 border-0 shadow-sm overflow-hidden course-card">
                        <a href="{{ route('packs.show', $package) }}" class="text-decoration-none text-dark">
                            <div class="ratio ratio-16x9 bg-light">
                                <x-package-card-media :package="$package" variant="nested" />
                            </div>
                            <div class="card-body d-flex flex-column">
                                @if($package->is_featured)
                                    <span class="badge bg-warning text-dark align-self-start mb-2">À la une</span>
                                @endif
                                <h2 class="h5 card-title">{{ $package->title }}</h2>
                                @if($package->subtitle)
                                    <p class="text-muted small mb-2">{{ $package->subtitle }}</p>
                                @endif
                                <p class="small text-muted flex-grow-1">{{ \Illuminate\Support\Str::limit(strip_tags($package->short_description ?? ''), 120) }}</p>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $package->contents_count }} contenus</span>
                                    <div class="text-end">
                                        @if($package->is_sale_active)
                                            <span class="text-decoration-line-through text-muted small me-1">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->price) }}</span>
                                            <strong class="text-success">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->effective_price) }}</strong>
                                        @else
                                            <strong class="text-primary">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->effective_price) }}</strong>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-5">
                    Aucun pack disponible pour le moment.
                </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $packages->links() }}</div>
    </div>
</section>
@endsection
