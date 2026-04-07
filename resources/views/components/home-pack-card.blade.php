@props(['package', 'showTrendingBadge' => false])

@php
    $hasAccess = auth()->check() && auth()->user()->hasPurchasedContentPackage($package);
    $packShowUrl = route('packs.show', $package);
@endphp

<div class="course-scroll-item">
    <div class="course-card" data-course-url="{{ $packShowUrl }}" style="cursor: pointer;">
        <div class="card" style="position: relative;">
            <div class="position-relative">
                <x-package-card-media :package="$package" />
                <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
                    <span class="badge bg-primary">Pack</span>
                    @if($showTrendingBadge)
                        <span class="badge bg-danger">Tendance</span>
                    @endif
                    @if($package->is_featured)
                        <span class="badge bg-warning text-dark">À la une</span>
                    @endif
                    @if($package->sale_discount_percentage)
                        <span class="badge bg-danger">-{{ $package->sale_discount_percentage }}%</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <h6 class="card-title">{{ Str::limit($package->title, 50) }}</h6>
                <p class="card-text">{{ Str::limit(strip_tags($package->short_description ?? $package->subtitle ?? ''), 100) }}</p>
                <div class="instructor-info">
                    <small class="instructor-name text-muted">
                        <i class="fas fa-box-open me-1"></i>{{ $package->contents_count }} contenus
                    </small>
                </div>
                <div class="price-duration mt-2">
                    <div class="price">
                        @if($package->is_sale_active)
                            <div class="course-price-container">
                                <div class="course-price-row">
                                    <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->effective_price) }}</span>
                                </div>
                                <div class="course-price-row">
                                    <small class="text-muted text-decoration-line-through">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->price) }}</small>
                                </div>
                            </div>
                        @else
                            <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->effective_price) }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-actions mt-2">
                    <div class="d-grid gap-2" onclick="event.stopPropagation();">
                        @if($hasAccess)
                            <a href="{{ $packShowUrl }}"
                               class="btn btn-outline-primary btn-sm w-100"
                               onclick="event.stopPropagation();">
                                <i class="fas fa-info-circle me-2"></i>Voir les détails
                            </a>
                            <a href="{{ route('customer.pack', $package) }}"
                               class="btn btn-success btn-sm w-100"
                               onclick="event.stopPropagation();">
                                <i class="fas fa-folder-open me-2"></i>Ouvrir le pack
                            </a>
                        @else
                            <a href="{{ route('packs.show', $package) }}"
                               class="btn btn-outline-primary btn-sm w-100"
                               onclick="event.stopPropagation();">
                                <i class="fas fa-eye me-2"></i>Voir le pack
                            </a>
                            @if($package->is_published && $package->is_sale_enabled)
                                <button type="button"
                                        class="btn btn-success btn-sm w-100"
                                        data-meta-trigger="checkout"
                                        onclick="event.stopPropagation(); proceedToCheckoutPackage({{ $package->id }});">
                                    <i class="fas fa-credit-card me-2"></i>{{ $package->cta_label ?: 'Procéder au paiement' }}
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
