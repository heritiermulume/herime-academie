@extends('layouts.admin')

@section('title', 'Détails du pack')
@section('admin-title', $package->title)
@section('admin-subtitle')
    Pack · Slug : {{ $package->slug }}
@endsection

@section('admin-actions')
    <div class="admin-actions-grid">
        <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Modifier
        </a>
        @if($package->is_published)
            <a href="{{ route('packs.show', $package) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">
                <i class="fas fa-external-link-alt me-2"></i>Voir sur le site
            </a>
        @endif
        <a href="{{ route('admin.packages.index') }}" class="btn btn-light">
            <i class="fas fa-arrow-left me-2"></i>Retour aux packs
        </a>
    </div>
@endsection

@section('admin-content')
    <div class="row g-4">
        <div class="col-lg-8">
            <section class="admin-panel mb-4">
                <div class="admin-panel__header">
                    <h3><i class="fas fa-info-circle me-2"></i>Informations</h3>
                </div>
                <div class="admin-panel__body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">
                            @if($package->is_published)
                                <span class="badge bg-success">Publié</span>
                            @else
                                <span class="badge bg-secondary">Brouillon</span>
                            @endif
                            @if($package->is_featured)
                                <span class="badge bg-warning text-dark">À la une</span>
                            @endif
                        </dd>
                        <dt class="col-sm-4">Prix</dt>
                        <dd class="col-sm-8">
                            @if($package->is_sale_active)
                                <span class="text-decoration-line-through text-muted me-2">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->price) }}</span>
                                <strong class="text-success">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->effective_price) }}</strong>
                            @else
                                <strong>{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->effective_price) }}</strong>
                            @endif
                        </dd>
                        @if($package->subtitle)
                            <dt class="col-sm-4">Sous-titre</dt>
                            <dd class="col-sm-8">{{ $package->subtitle }}</dd>
                        @endif
                        @if($package->short_description)
                            <dt class="col-sm-4">Accroche</dt>
                            <dd class="col-sm-8">{{ $package->short_description }}</dd>
                        @endif
                    </dl>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3><i class="fas fa-layer-group me-2"></i>Contenus du pack ({{ $package->contents->count() }})</h3>
                </div>
                <div class="admin-panel__body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Contenu</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($package->contents as $course)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $course->title }}</div>
                                            <div class="small text-muted">{{ $course->slug }}</div>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('admin.contents.show', $course) }}" class="btn btn-sm btn-light" title="Voir le contenu">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">Aucun contenu lié.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-lg-4">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3><i class="fas fa-image me-2"></i>Vignette</h3>
                </div>
                <div class="admin-panel__body text-center">
                    @if($package->thumbnail_url)
                        <img src="{{ $package->thumbnail_url }}" alt="" class="img-fluid rounded" style="max-height: 240px; object-fit: cover;">
                    @else
                        <p class="text-muted mb-0">Aucune vignette</p>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
