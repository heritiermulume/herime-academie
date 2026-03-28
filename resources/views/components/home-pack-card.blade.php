@props(['package'])

<div class="course-scroll-item">
    <div class="course-card" data-course-url="{{ route('packs.show', $package) }}" style="cursor: pointer;">
        <div class="card" style="position: relative;">
            <div class="position-relative">
                <img src="{{ $package->thumbnail_url ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop' }}"
                     class="card-img-top" alt="{{ $package->title }}">
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
                            </div>
                        @else
                            <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->effective_price) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
