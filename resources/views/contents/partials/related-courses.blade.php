@if(isset($relatedCourses) && $relatedCourses->count() > 0)
    <div class="content-card">
        <h2 class="section-title-modern">
            <i class="fas fa-thumbs-up"></i>
            Recommandés
        </h2>
        <div class="row g-3">
            @foreach($relatedCourses as $relatedCourse)
                @php
                    $relatedCourseStats = $relatedCourse->getCourseStats();
                @endphp
                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                    <div class="course-card" data-course-url="{{ route('contents.show', $relatedCourse->slug) }}" style="cursor: pointer;">
                        <div class="card course-card-inner" style="position: relative;">
                            <div class="position-relative">
                                <img src="{{ $relatedCourse->thumbnail_url ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop' }}"
                                     class="card-img-top" alt="{{ $relatedCourse->title }}">
                                <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
                                    @if($relatedCourse->is_featured)
                                        <span class="badge bg-warning">En vedette</span>
                                    @endif
                                    @if($relatedCourse->is_free)
                                        <span class="badge bg-success">Gratuit</span>
                                    @endif
                                    @if($relatedCourse->sale_discount_percentage)
                                        <span class="badge bg-danger">
                                            -{{ $relatedCourse->sale_discount_percentage }}%
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title">
                                    {{ Str::limit($relatedCourse->title, 50) }}
                                </h6>
                                <p class="card-text">{{ Str::limit($relatedCourse->short_description ?? $relatedCourse->description, 100) }}</p>

                                <div class="instructor-info">
                                    <small class="instructor-name">
                                        <i class="fas fa-user me-1"></i>{{ Str::limit($relatedCourse->provider->name ?? 'Prestataire', 20) }}
                                    </small>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <span>{{ number_format($relatedCourseStats['average_rating'] ?? 0, 1) }}</span>
                                        <span class="text-muted">({{ $relatedCourseStats['total_reviews'] ?? 0 }})</span>
                                    </div>
                                </div>

                                @if($relatedCourse->show_customers_count)
                                    @php
                                        $relatedCount = 0;
                                        $relatedLabel = '';
                                        $relatedIcon = '';

                                        if ($relatedCourse->is_downloadable) {
                                            // Cours téléchargeable
                                            if ($relatedCourse->is_free) {
                                                // Téléchargeable gratuit : total des inscrits (car l'utilisateur s'inscrit d'abord avec "Intéresser")
                                                $relatedCount = (int) ($relatedCourseStats['total_customers'] ?? $relatedCourse->total_customers ?? 0);
                                                $relatedLabel = $relatedCount > 1 ? 'bénéficiaires' : 'bénéficiaire';
                                                $relatedIcon = 'fa-users';
                                            } else {
                                                // Téléchargeable payant : nombre d'achats
                                                $relatedCount = (int) ($relatedCourseStats['purchases_count'] ?? $relatedCourse->purchases_count ?? 0);
                                                $relatedLabel = $relatedCount > 1 ? 'achats' : 'achat';
                                                $relatedIcon = 'fa-shopping-cart';
                                            }
                                        } else {
                                            // Cours non téléchargeable
                                            if ($relatedCourse->is_free) {
                                                // Non téléchargeable gratuit : inscriptions
                                                $relatedCount = (int) ($relatedCourseStats['total_customers'] ?? $relatedCourse->total_customers ?? 0);
                                                $relatedLabel = $relatedCount > 1 ? 'inscriptions' : 'inscription';
                                                $relatedIcon = 'fa-user-plus';
                                            } else {
                                                // Non téléchargeable payant : nombre d'achats
                                                $relatedCount = (int) ($relatedCourseStats['purchases_count'] ?? $relatedCourse->purchases_count ?? 0);
                                                $relatedLabel = $relatedCount > 1 ? 'achats' : 'achat';
                                                $relatedIcon = 'fa-shopping-cart';
                                            }
                                        }
                                    @endphp
                                    <div class="customers-count mb-2">
                                        <small class="text-muted">
                                            <i class="fas {{ $relatedIcon }} me-1"></i>
                                            {{ number_format($relatedCount, 0, ',', ' ') }}
                                            {{ $relatedLabel }}
                                        </small>
                                    </div>
                                @endif

                                <div class="price-duration">
                                    <div class="price">
                                        @if($relatedCourse->is_free)
                                            <span class="text-success fw-bold">Gratuit</span>
                                        @else
                                            @if($relatedCourse->is_sale_active && $relatedCourse->active_sale_price !== null)
                                                <div class="course-price-container">
                                                    <div class="course-price-row">
                                                        <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($relatedCourse->active_sale_price) }}</span>
                                                    </div>
                                                    <div class="course-price-row">
                                                        <small class="text-muted text-decoration-line-through">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($relatedCourse->price) }}</small>
                                                    </div>
                                                    @if($relatedCourse->is_sale_active && $relatedCourse->sale_end_at)
                                                        <div class="course-price-row">
                                                            <div class="promotion-countdown" data-sale-end="{{ $relatedCourse->sale_end_at->toIso8601String() }}">
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
                                                <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($relatedCourse->price) }}</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="card-actions mt-2" onclick="event.stopPropagation(); event.preventDefault();">
                                    <x-contenu-button :course="$relatedCourse" size="small" :show-cart="false" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
