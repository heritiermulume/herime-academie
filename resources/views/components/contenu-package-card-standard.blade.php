@props(['package'])

@php
    $hasAccess = auth()->check() && auth()->user()->hasPurchasedContentPackage($package);
    $courseUrl = $hasAccess ? route('customer.pack', $package) : route('packs.show', $package);
@endphp

<div class="course-card" data-course-url="{{ $courseUrl }}" style="cursor: pointer;">
    <div class="card" style="position: relative;">
        <div class="position-relative">
            <x-package-card-media :package="$package" />
            <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
                <span class="badge bg-primary">Pack</span>
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
                            @if(($package->use_fake_promo_countdown ?? false) || $package->sale_end_at)
                                <div class="course-price-row">
                                    <div class="promotion-countdown"
                                         @if($package->use_fake_promo_countdown ?? false)
                                             data-promo-duration-days="{{ max(1, (int) ($package->fake_promo_duration_days ?? 3)) }}"
                                         @else
                                             data-sale-end="{{ $package->sale_end_at->toIso8601String() }}"
                                         @endif>
                                        <i class="fas fa-fire me-1 text-danger"></i>
                                        <span class="countdown-text">
                                            <span class="countdown-years">0</span><span>a</span>
                                            <span class="countdown-months">0</span><span>m</span>
                                            <span class="countdown-days">0</span>j
                                            <span class="countdown-hours">0</span>h
                                            <span class="countdown-minutes">0</span>min
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->effective_price) }}</span>
                    @endif
                </div>
            </div>
            {{-- Même logique que <x-contenu-button> (état achat) : panier + paiement ; la fiche pack s’ouvre au clic sur la carte (data-course-url). --}}
            <div class="card-actions pt-2">
                <div class="d-grid gap-2">
                    @if($hasAccess)
                        <a href="{{ route('customer.pack', $package) }}"
                           class="btn btn-success btn-sm w-100"
                           onclick="event.stopPropagation();">
                            <i class="fas fa-folder-open me-2"></i>Ouvrir le pack
                        </a>
                    @elseif($package->is_published && $package->is_sale_enabled)
                        <button type="button"
                                class="btn btn-outline-primary btn-sm w-100 add-package-to-cart-btn"
                                data-package-id="{{ $package->id }}"
                                data-meta-trigger="add_to_cart">
                            <i class="fas fa-shopping-cart me-2"></i>Ajouter au panier
                        </button>
                        <button type="button"
                                class="btn btn-success btn-sm w-100"
                                data-meta-trigger="checkout"
                                onclick="proceedToCheckoutPackage({{ $package->id }});">
                            <i class="fas fa-credit-card me-2"></i>{{ $package->cta_label ?: 'Procéder au paiement' }}
                        </button>
                    @elseif($package->is_published)
                        <a href="{{ route('packs.show', $package) }}"
                           class="btn btn-outline-primary btn-sm w-100"
                           onclick="event.stopPropagation();">
                            <i class="fas fa-eye me-2"></i>Voir le pack
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
