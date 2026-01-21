@props(['course'])

<div class="course-card" data-course-url="{{ route('contents.show', $course->slug) }}" style="cursor: pointer;">
    <div class="card" style="position: relative;">
        <div class="position-relative">
            <img src="{{ $course->thumbnail_url ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop' }}" 
                 class="card-img-top" alt="{{ $course->title }}">
            <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
                @if($course->is_featured)
                <span class="badge bg-warning">En vedette</span>
                @endif
                @if($course->is_free)
                <span class="badge bg-success">Gratuit</span>
                @endif
                @if($course->sale_discount_percentage)
                <span class="badge bg-danger">
                    -{{ $course->sale_discount_percentage }}%
                </span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <h6 class="card-title">
                {{ Str::limit($course->title, 50) }}
            </h6>
            <p class="card-text">{{ Str::limit($course->short_description, 100) }}</p>
            
            <div class="instructor-info">
                <small class="instructor-name">
                    <i class="fas fa-user me-1"></i>{{ Str::limit($course->provider->name, 20) }}
                </small>
                <div class="rating">
                    <i class="fas fa-star"></i>
                    <span>{{ number_format($course->stats['average_rating'] ?? 0, 1) }}</span>
                    <span class="text-muted">({{ $course->stats['total_reviews'] ?? 0 }})</span>
                </div>
            </div>
            
            @if($course->show_customers_count)
            @php
                $count = 0;
                $label = '';
                $icon = '';
                
                if ($course->is_downloadable) {
                    // Cours téléchargeable
                    if ($course->is_free) {
                        // Téléchargeable gratuit : bénéficiaires uniques
                        $count = (int) ($course->stats['unique_downloads'] ?? $course->unique_downloads_count ?? 0);
                        $label = $count > 1 ? 'bénéficiaires' : 'bénéficiaire';
                        $icon = 'fa-users';
                    } else {
                        // Téléchargeable payant : nombre d'achats
                        $count = (int) ($course->stats['purchases_count'] ?? $course->purchases_count ?? 0);
                        $label = $count > 1 ? 'achats' : 'achat';
                        $icon = 'fa-shopping-cart';
                    }
                } else {
                    // Cours non téléchargeable
                    if ($course->is_free) {
                        // Non téléchargeable gratuit : inscriptions
                        $count = (int) ($course->stats['total_customers'] ?? $course->total_customers ?? 0);
                        $label = $count > 1 ? 'inscriptions' : 'inscription';
                        $icon = 'fa-user-plus';
                    } else {
                        // Non téléchargeable payant : nombre d'achats
                        $count = (int) ($course->stats['purchases_count'] ?? $course->purchases_count ?? 0);
                        $label = $count > 1 ? 'achats' : 'achat';
                        $icon = 'fa-shopping-cart';
                    }
                }
            @endphp
            <div class="customers-count mb-2">
                <small class="text-muted">
                    <i class="fas {{ $icon }} me-1"></i>
                    {{ number_format($count, 0, ',', ' ') }} 
                    {{ $label }}
                </small>
            </div>
            @endif
            
            <div class="price-duration">
                <div class="price">
                    @if($course->is_free)
                        <span class="text-success fw-bold">Gratuit</span>
                    @else
                        @if($course->is_sale_active && $course->active_sale_price !== null)
                            <div class="course-price-container">
                                <div class="course-price-row">
                                    <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->active_sale_price) }}</span>
                                </div>
                                <div class="course-price-row">
                                    <small class="text-muted text-decoration-line-through">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</small>
                                </div>
                                @if($course->is_sale_active && $course->sale_end_at)
                                <div class="course-price-row">
                                    <div class="promotion-countdown" data-sale-end="{{ $course->sale_end_at->toIso8601String() }}">
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
                            <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</span>
                        @endif
                    @endif
                </div>
            </div>
            
            <div class="card-actions" onclick="event.stopPropagation(); event.preventDefault();">
                <x-contenu-button :course="$course" size="small" :show-cart="false" />
            </div>
        </div>
    </div>
</div>

