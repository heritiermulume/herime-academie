@if($recommendedCourses->count() > 0)
<div class="row g-4">
    @foreach($recommendedCourses as $course)
    <div class="col-lg-3 col-md-6">
        <div class="course-card">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="position-relative">
                    <img src="{{ $course->thumbnail ? $course->thumbnail : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=300&h=200&fit=crop' }}" 
                         class="card-img-top" alt="{{ $course->title }}" style="height: 150px; object-fit: cover;">
                    <span class="badge bg-primary position-absolute top-0 start-0 m-2">{{ $course->category->name }}</span>
                    @if($course->sale_price)
                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">-{{ $course->discount_percentage }}%</span>
                    @endif
                    @if($course->is_featured)
                    <span class="badge bg-warning position-absolute bottom-0 start-0 m-2">En vedette</span>
                    @endif
                </div>
                <div class="card-body p-3">
                    <h6 class="card-title fw-bold mb-2">
                        <a href="{{ route('courses.show', $course->slug) }}" class="text-decoration-none text-dark">
                            {{ Str::limit($course->title, 40) }}
                        </a>
                    </h6>
                    <p class="card-text text-muted small mb-2">
                        {{ Str::limit($course->short_description, 60) }}
                    </p>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">
                            <i class="fas fa-user me-1"></i>{{ $course->instructor->name }}
                        </small>
                        <div class="rating">
                            <i class="fas fa-star text-warning small"></i>
                            <span class="small">{{ number_format($course->stats['average_rating'] ?? 0, 1) }}</span>
                            <span class="text-muted small">({{ $course->stats['total_reviews'] ?? 0 }})</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">
                            <i class="fas fa-users me-1"></i>{{ $course->stats['total_students'] ?? 0 }} étudiants
                        </small>
                        <small class="badge bg-secondary">{{ ucfirst($course->level) }}</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="price">
                            @if($course->sale_price)
                                <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->sale_price) }}</span>
                                <small class="text-muted text-decoration-line-through ms-1">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</small>
                            @else
                                <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</span>
                            @endif
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>{{ $course->stats['total_duration'] ?? 0 }} min
                        </small>
                    </div>
                    <div class="d-grid gap-2">
                        <x-course-button :course="$course" size="small" :show-cart="false" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="text-center py-4">
    <p class="text-muted">Aucune recommandation disponible pour le moment.</p>
</div>
@endif
