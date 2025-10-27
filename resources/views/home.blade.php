@extends('layouts.app')

@section('title', 'Herime Academie - Plateforme d\'apprentissage en ligne')
@section('description', 'Découvrez des milliers de cours en ligne de qualité avec Herime Academie. Formations professionnelles, certifications et expertise garanties.')

@section('content')
<style>
/* Horizontal Scrollable Categories */
.categories-scroll-container {
    position: relative;
    margin: 0 -15px;
    padding: 0 15px;
    overflow: hidden;
}

.categories-scroll-container::before,
.categories-scroll-container::after {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 30px;
    z-index: 5;
    pointer-events: none;
}

.categories-scroll-container::before {
    left: 0;
    background: linear-gradient(to right, rgba(255, 255, 255, 1), rgba(255, 255, 255, 0));
}

.categories-scroll-container::after {
    right: 0;
    background: linear-gradient(to left, rgba(255, 255, 255, 1), rgba(255, 255, 255, 0));
}

.categories-scroll-wrapper {
    display: flex;
    overflow-x: auto;
    gap: 1rem;
    padding: 1rem 0;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
    scroll-snap-type: x mandatory;
    overscroll-behavior-x: contain;
}

.categories-scroll-wrapper::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}

.category-item-scroll {
    flex: 0 0 auto;
    width: 200px;
    min-width: 200px;
    scroll-snap-align: start;
}

.category-item-scroll .category-card .card {
    height: 200px;
    border-radius: 12px;
    transition: all 0.3s ease;
    width: 100%;
}

.category-item-scroll .category-card .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.category-item-scroll .category-card .card-body {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
}

.category-item-scroll .category-card .category-icon {
    margin-bottom: 0.75rem;
}

.category-item-scroll .category-card .category-icon i {
    font-size: 1.75rem;
}

.category-item-scroll .category-card .card-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    line-height: 1.2;
    height: 1.2rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.category-item-scroll .category-card .card-text {
    font-size: 0.75rem;
    line-height: 1.3;
    margin-bottom: 0.75rem;
    height: 2rem;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.category-item-scroll .category-card .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem !important;
    border-radius: 12px;
    align-self: center;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .categories-scroll-container {
        margin: 0 -10px;
        padding: 0 10px;
    }
    
    .categories-scroll-wrapper {
        gap: 0.75rem;
        padding: 0.75rem 0;
    }
    
    .category-item-scroll {
        width: 180px;
        min-width: 180px;
    }
    
    .category-item-scroll .category-card .card {
        height: 180px;
        border-radius: 10px;
    }
    
    .category-item-scroll .category-card .card-body {
        padding: 0.75rem;
    }
    
    .category-item-scroll .category-card .category-icon i {
        font-size: 1.5rem;
    }
    
    .category-item-scroll .category-card .card-title {
        font-size: 0.85rem;
        line-height: 1.1;
    }
    
    .category-item-scroll .category-card .card-text {
        font-size: 0.7rem;
        height: 1.75rem;
        line-height: 1.2;
    }
    
    .category-item-scroll .category-card .badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.4rem !important;
    }
}

@media (max-width: 575.98px) {
    .categories-scroll-container {
        margin: 0 -5px;
        padding: 0 5px;
    }
    
    .categories-scroll-wrapper {
        gap: 0.5rem;
        padding: 0.5rem 0;
    }
    
    .category-item-scroll {
        width: 160px;
        min-width: 160px;
    }
    
    .category-item-scroll .category-card .card {
        height: 160px;
        border-radius: 8px;
    }
    
    .category-item-scroll .category-card .card-body {
        padding: 0.5rem;
    }
    
    .category-item-scroll .category-card .category-icon {
        margin-bottom: 0.5rem;
    }
    
    .category-item-scroll .category-card .category-icon i {
        font-size: 1.25rem;
    }
    
    .category-item-scroll .category-card .card-title {
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
        line-height: 1.1;
    }
    
    .category-item-scroll .category-card .card-text {
        font-size: 0.65rem;
        height: 1.5rem;
        margin-bottom: 0.5rem;
        line-height: 1.1;
    }
    
    .category-item-scroll .category-card .badge {
        font-size: 0.6rem;
        padding: 0.15rem 0.3rem !important;
    }
}

/* Scroll Indicators */
.scroll-indicators {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    display: flex;
    justify-content: space-between;
    pointer-events: none;
    z-index: 10;
}

.scroll-btn {
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.3s ease;
    pointer-events: auto;
    color: #003366;
    font-size: 0.9rem;
}

.scroll-btn:hover {
    background: #003366;
    color: white;
    transform: scale(1.1);
}

.scroll-btn:active {
    transform: scale(0.95);
}

.scroll-left {
    left: 10px;
}

.scroll-right {
    right: 10px;
}

/* Mobile optimizations */
@media (max-width: 767.98px) {
    .scroll-indicators {
        display: none;
    }
    
    /* Improve touch scrolling */
    .categories-scroll-wrapper {
        -webkit-overflow-scrolling: touch;
        scroll-snap-type: x mandatory;
        overscroll-behavior-x: contain;
    }
    
    /* Add touch feedback */
    .category-item-scroll .category-card .card:active {
        transform: scale(0.98);
        transition: transform 0.1s ease;
    }
    
    /* Improve text readability on small screens */
    .category-item-scroll .category-card .card-title {
        font-weight: 700;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    
    .category-item-scroll .category-card .badge {
        font-weight: 600;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
}

/* Mobile Progress Indicator */
.mobile-progress {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
    padding: 0 1rem;
}

.progress-dots {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.progress-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #ddd;
    transition: all 0.3s ease;
    cursor: pointer;
}

.progress-dot.active {
    background-color: #003366;
    transform: scale(1.2);
}

.progress-dot:hover {
    background-color: #004080;
    transform: scale(1.1);
}

</style>

<!-- Hero Section -->
<section class="hero-section-modern text-white">
    <div class="hero-container">
        <div class="hero-image-bg">
            <img src="{{ asset('images/hero/hero-student.jpg') }}" 
                 alt="Apprentissage en ligne" class="hero-bg-image">
        </div>
        
        <div class="hero-content-overlay">
            <div class="container">
                <div class="row align-items-center min-vh-80">
                    <div class="col-lg-7 col-xl-6">
                        <div class="hero-text-content">
                            <h1 class="display-4 fw-bold mb-4">
                                Apprenez sans limites avec 
                                <span class="text-warning">Herime Académie</span>
                            </h1>
                            <p class="lead mb-4">
                                Découvrez des milliers de cours en ligne de qualité, créés par des experts. 
                                Développez vos compétences et boostez votre carrière.
                            </p>
                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <button class="btn btn-warning btn-lg px-4" onclick="goToCourses()" id="start-learning-btn">
                                    <i class="fas fa-play me-2"></i>Commencer à apprendre
                                </button>
                                <button class="btn btn-outline-light btn-lg px-4" onclick="scrollToCategories()" id="explore-btn">
                                    <i class="fas fa-search me-2"></i>Explorer les catégories
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section id="categories" class="categories-section py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-3">Explorez nos catégories</h2>
                <p class="lead text-muted">
                    Trouvez le cours parfait dans nos catégories spécialisées
                </p>
            </div>
        </div>
        <!-- Horizontal Scrollable Categories -->
        <div class="categories-scroll-container">
            <div class="categories-scroll-wrapper" id="categoriesScroll">
                @foreach($categories as $category)
                <div class="category-item-scroll">
                    <div class="category-card h-100">
                        <a href="{{ route('courses.category', $category->slug) }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 hover-lift">
                                <div class="card-body text-center p-3">
                                    @if($category->icon)
                                    <div class="category-icon mb-2">
                                        <i class="{{ $category->icon }} fa-2x" style="color: {{ $category->color }}"></i>
                                    </div>
                                    @endif
                                    <h6 class="card-title fw-bold mb-2">{{ Str::limit($category->name, 15) }}</h6>
                                    <p class="card-text text-muted small mb-2" style="height: 2rem; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ Str::limit($category->description, 40) }}</p>
                                    <span class="badge small" style="background-color: #003366; color: white;">{{ $category->courses_count ?? 0 }} cours</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Scroll Indicators -->
            <div class="scroll-indicators">
                <button class="scroll-btn scroll-left" onclick="scrollCategories('left')" aria-label="Faire défiler vers la gauche">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="scroll-btn scroll-right" onclick="scrollCategories('right')" aria-label="Faire défiler vers la droite">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <!-- Mobile Progress Indicator -->
            <div class="mobile-progress d-md-none">
                <div class="progress-dots" id="progressDots"></div>
            </div>
        </div>
        <div class="text-center mt-5">
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <a href="{{ route('courses.index') }}" class="btn btn-outline-primary btn-lg">
                    Voir tous les cours <i class="fas fa-arrow-right ms-2"></i>
                </a>
                <a href="{{ route('categories.index') }}" class="btn btn-primary btn-lg">
                    Voir toutes les catégories <i class="fas fa-th-large ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Courses Section -->
@if($featuredCourses->count() > 0)
<section class="featured-courses py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-3">Cours en vedette</h2>
                <p class="lead text-muted">
                    Découvrez nos cours les plus populaires et les mieux notés
                </p>
            </div>
        </div>
        <div class="row g-3">
            @foreach($featuredCourses as $course)
            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="course-card">
                    <div class="card">
                        <div class="position-relative">
                            <img src="{{ $course->thumbnail ? $course->thumbnail : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop' }}" 
                                 class="card-img-top" alt="{{ $course->title }}">
                            <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
                                @if($course->is_featured)
                                <span class="badge bg-warning">En vedette</span>
                                @endif
                                @if($course->is_free)
                                <span class="badge bg-success">Gratuit</span>
                                @endif
                                @if($course->sale_price)
                                <span class="badge bg-danger">
                                    -{{ round((($course->price - $course->sale_price) / $course->price) * 100) }}%
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title">
                                <a href="{{ route('courses.show', $course->slug) }}">
                                    {{ Str::limit($course->title, 50) }}
                                </a>
                            </h6>
                            <p class="card-text">{{ Str::limit($course->short_description, 100) }}</p>
                            
                            <div class="instructor-info">
                                <small class="instructor-name">
                                    <i class="fas fa-user me-1"></i>{{ Str::limit($course->instructor->name, 20) }}
                                </small>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <span>{{ number_format($course->stats['average_rating'] ?? 0, 1) }}</span>
                                    <span class="text-muted">({{ $course->stats['total_reviews'] ?? 0 }})</span>
                                </div>
                            </div>
                            
                            <div class="price-duration">
                                <div class="price">
                                    @if($course->is_free)
                                        <span class="text-success fw-bold">Gratuit</span>
                                    @else
                                        @if($course->sale_price)
                                            <span class="text-primary fw-bold">${{ number_format($course->sale_price, 2) }}</span>
                                            <small class="text-muted text-decoration-line-through ms-1">${{ number_format($course->price, 2) }}</small>
                                        @else
                                            <span class="text-primary fw-bold">${{ number_format($course->price, 2) }}</span>
                                        @endif
                                    @endif
                                </div>
                                <small class="duration">
                                    <i class="fas fa-clock me-1"></i>{{ $course->stats['total_duration'] ?? 0 }} min
                                </small>
                            </div>
                            
                            <div class="card-actions">
                                <x-course-button :course="$course" size="small" :show-cart="false" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('courses.index', ['featured' => 1]) }}" class="btn btn-outline-primary btn-lg">
                Voir tous les cours en vedette <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>
@endif

<!-- Popular Courses Section -->
@if($popularCourses->count() > 0)
<section class="popular-courses py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-3">Cours populaires</h2>
                <p class="lead text-muted">
                    Les cours les plus suivis par notre communauté
                </p>
            </div>
        </div>
        <div class="row g-3">
            @foreach($popularCourses as $course)
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="course-card">
                    <div class="card">
                        <div class="position-relative">
                            <img src="{{ $course->thumbnail ? $course->thumbnail : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop' }}" 
                                 class="card-img-top" alt="{{ $course->title }}">
                            <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
                                @if($course->is_featured)
                                <span class="badge bg-warning">En vedette</span>
                                @endif
                                @if($course->is_free)
                                <span class="badge bg-success">Gratuit</span>
                                @endif
                                @if($course->sale_price)
                                <span class="badge bg-danger">
                                    -{{ round((($course->price - $course->sale_price) / $course->price) * 100) }}%
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title">
                                <a href="{{ route('courses.show', $course->slug) }}">
                                    {{ Str::limit($course->title, 50) }}
                                </a>
                            </h6>
                            <p class="card-text">{{ Str::limit($course->short_description, 100) }}</p>
                            
                            <div class="instructor-info">
                                <small class="instructor-name">
                                    <i class="fas fa-user me-1"></i>{{ Str::limit($course->instructor->name, 20) }}
                                </small>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <span>{{ number_format($course->stats['average_rating'] ?? 0, 1) }}</span>
                                    <span class="text-muted">({{ $course->stats['total_reviews'] ?? 0 }})</span>
                                </div>
                            </div>
                            
                            <div class="price-duration">
                                <div class="price">
                                    @if($course->is_free)
                                        <span class="text-success fw-bold">Gratuit</span>
                                    @else
                                        @if($course->sale_price)
                                            <span class="text-primary fw-bold">${{ number_format($course->sale_price, 2) }}</span>
                                            <small class="text-muted text-decoration-line-through ms-1">${{ number_format($course->price, 2) }}</small>
                                        @else
                                            <span class="text-primary fw-bold">${{ number_format($course->price, 2) }}</span>
                                        @endif
                                    @endif
                                </div>
                                <small class="duration">
                                    <i class="fas fa-clock me-1"></i>{{ $course->stats['total_duration'] ?? 0 }} min
                                </small>
                            </div>
                            
                            <div class="card-actions">
                                <x-course-button :course="$course" size="small" :show-cart="false" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('courses.index', ['popular' => 1]) }}" class="btn btn-outline-primary btn-lg">
                Voir tous les cours populaires <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>
@endif

<!-- Testimonials Section -->
@if($testimonials->count() > 0)
<section class="testimonials py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-3 text-dark">Ce que disent nos étudiants</h2>
                <p class="lead text-muted">
                    Découvrez les témoignages de notre communauté
                </p>
            </div>
        </div>
        
        <!-- Testimonials Grid -->
        <div class="testimonials-grid">
            <div class="testimonials-container">
                @foreach($testimonials as $index => $testimonial)
                <div class="testimonial-item {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}">
                    <div class="testimonial-card">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <!-- Quote Icon -->
                                <div class="quote-icon mb-3">
                                    <i class="fas fa-quote-left text-primary fs-1"></i>
                                </div>
                                
                                <!-- Testimonial Text -->
                                <blockquote class="testimonial-text mb-4">
                                    <p class="mb-0 fst-italic">"{{ $testimonial->testimonial }}"</p>
                                </blockquote>
                                
                                <!-- Rating -->
                                <div class="rating mb-3">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $testimonial->rating ? 'text-warning' : 'text-muted' }}"></i>
                                    @endfor
                                </div>
                                
                                <!-- Author Info -->
                                <div class="author-info d-flex align-items-center">
                                    <div class="author-avatar me-3">
                                        <img src="{{ $testimonial->photo ? (str_starts_with($testimonial->photo, 'http') ? $testimonial->photo : Storage::url($testimonial->photo)) : 'https://ui-avatars.com/api/?name=' . urlencode($testimonial->name) . '&background=003366&color=fff&size=60' }}" 
                                             alt="{{ $testimonial->name }}" class="rounded-circle" width="50" height="50">
                                    </div>
                                    <div class="author-details">
                                        <h6 class="mb-0 fw-bold text-dark">{{ $testimonial->name }}</h6>
                                        <small class="text-muted">{{ $testimonial->title }}</small>
                                        @if($testimonial->company)
                                            <br><small class="text-muted">{{ $testimonial->company }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Navigation -->
            <div class="testimonials-navigation text-center mt-4">
                <button class="btn btn-outline-primary me-2" id="prevBtn">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="dots-container d-inline-block">
                    @foreach($testimonials as $index => $testimonial)
                    <button class="dot {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}"></button>
                    @endforeach
                </div>
                <button class="btn btn-outline-primary ms-2" id="nextBtn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Trending Courses Section -->
@if($trendingCourses->count() > 0)
<section class="trending-courses py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-3">Cours tendance</h2>
                <p class="lead text-muted">
                    Les cours les plus suivis cette semaine
                </p>
            </div>
        </div>
        <div class="row g-4">
            @foreach($trendingCourses as $course)
            <div class="col-lg-3 col-md-6">
                <div class="course-card">
                    <div class="card">
                        <div class="position-relative">
                            <img src="{{ $course->thumbnail ? $course->thumbnail : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop' }}" 
                                 class="card-img-top" alt="{{ $course->title }}">
                            <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
                                <span class="badge bg-danger">Tendance</span>
                                @if($course->is_free)
                                <span class="badge bg-success">Gratuit</span>
                                @endif
                                @if($course->sale_price)
                                <span class="badge bg-warning">
                                    -{{ round((($course->price - $course->sale_price) / $course->price) * 100) }}%
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title">
                                <a href="{{ route('courses.show', $course->slug) }}">
                                    {{ Str::limit($course->title, 50) }}
                                </a>
                            </h6>
                            <p class="card-text">{{ Str::limit($course->short_description, 100) }}</p>
                            
                            <div class="instructor-info">
                                <small class="instructor-name">
                                    <i class="fas fa-user me-1"></i>{{ Str::limit($course->instructor->name, 20) }}
                                </small>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <span>{{ number_format($course->stats['average_rating'] ?? 0, 1) }}</span>
                                    <span class="text-muted">({{ $course->stats['total_reviews'] ?? 0 }})</span>
                                </div>
                            </div>
                            
                            <div class="price-duration">
                                <div class="price">
                                    @if($course->is_free)
                                        <span class="text-success fw-bold">Gratuit</span>
                                    @else
                                        @if($course->sale_price)
                                            <span class="text-primary fw-bold">${{ number_format($course->sale_price, 2) }}</span>
                                            <small class="text-muted text-decoration-line-through ms-1">${{ number_format($course->price, 2) }}</small>
                                        @else
                                            <span class="text-primary fw-bold">${{ number_format($course->price, 2) }}</span>
                                        @endif
                                    @endif
                                </div>
                                <small class="duration">
                                    <i class="fas fa-clock me-1"></i>{{ $course->stats['total_duration'] ?? 0 }} min
                                </small>
                            </div>
                            
                            <div class="card-actions">
                                <x-course-button :course="$course" size="small" :show-cart="false" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('courses.index', ['trending' => 1]) }}" class="btn btn-outline-primary btn-lg">
                Voir tous les cours tendance <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>
@endif

<!-- CTA Section -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 text-center text-lg-start">
                <h2 class="display-5 fw-bold mb-3">Prêt à commencer votre parcours d'apprentissage ?</h2>
                <p class="lead mb-0">
                    Rejoignez des milliers d'étudiants qui transforment leur carrière avec Herime Academie.
                </p>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                @auth
                    <a href="{{ route('courses.index') }}" class="btn btn-warning btn-lg px-4">
                        <i class="fas fa-play me-2"></i>Explorer les cours
                    </a>
                @else
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-end">
                        <a href="{{ route('register') }}" class="btn btn-warning btn-lg px-4">
                            <i class="fas fa-user-plus me-2"></i>S'inscrire gratuitement
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
// Fonctions pour les boutons de la page d'accueil
function goToCourses() {
    console.log('Redirection vers la page des cours...');
    window.location.href = '{{ route("courses.index") }}';
}

function scrollToCategories() {
    console.log('Défilement vers la section catégories...');
    const categoriesSection = document.getElementById('categories');
    if (categoriesSection) {
        categoriesSection.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    } else {
        console.error('Section catégories non trouvée');
    }
}

// La fonction showNotification est maintenant définie globalement dans app.blade.php

// Test des boutons au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page d\'accueil chargée - Test des boutons...');
    
    const startLearningBtn = document.getElementById('start-learning-btn');
    const exploreBtn = document.getElementById('explore-btn');
    
    if (startLearningBtn) {
        console.log('✅ Bouton "Commencer à apprendre" trouvé et prêt');
        // Test de clic programmatique
        startLearningBtn.addEventListener('click', function(e) {
            console.log('🎯 Clic détecté sur "Commencer à apprendre"');
        });
    } else {
        console.error('❌ Bouton "Commencer à apprendre" non trouvé');
    }
    
    if (exploreBtn) {
        console.log('✅ Bouton "Explorer les cours" trouvé et prêt');
        // Test de clic programmatique
        exploreBtn.addEventListener('click', function(e) {
            console.log('🎯 Clic détecté sur "Explorer les cours"');
        });
    } else {
        console.error('❌ Bouton "Explorer les cours" non trouvé');
    }
    
    // Les boutons sont prêts (sans notification)
});

// La fonction addToCart est maintenant définie globalement dans app.blade.php

// Les fonctions showNotification et updateCartCount sont maintenant définies globalement dans app.blade.php

// Testimonials Carousel
document.addEventListener('DOMContentLoaded', function() {
    const testimonialItems = document.querySelectorAll('.testimonial-item');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (testimonialItems.length === 0) return;
    
    let currentIndex = 0;
    let autoSlideInterval;
    
    function showTestimonial(index) {
        // Remove active class from all items and dots
        testimonialItems.forEach(item => item.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Add active class to current item and dot
        testimonialItems[index].classList.add('active');
        dots[index].classList.add('active');
        
        currentIndex = index;
    }
    
    function nextTestimonial() {
        const nextIndex = (currentIndex + 1) % testimonialItems.length;
        showTestimonial(nextIndex);
    }
    
    function prevTestimonial() {
        const prevIndex = currentIndex === 0 ? testimonialItems.length - 1 : currentIndex - 1;
        showTestimonial(prevIndex);
    }
    
    function startAutoSlide() {
        autoSlideInterval = setInterval(nextTestimonial, 4000); // Change every 4 seconds
    }
    
    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }
    
    // Initialize
    showTestimonial(0);
    startAutoSlide();
    
    // Navigation buttons
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            stopAutoSlide();
            nextTestimonial();
            startAutoSlide();
        });
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            stopAutoSlide();
            prevTestimonial();
            startAutoSlide();
        });
    }
    
    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            stopAutoSlide();
            showTestimonial(index);
            startAutoSlide();
        });
    });
    
    // Pause on hover
    const testimonialsGrid = document.querySelector('.testimonials-grid');
    if (testimonialsGrid) {
        testimonialsGrid.addEventListener('mouseenter', stopAutoSlide);
        testimonialsGrid.addEventListener('mouseleave', startAutoSlide);
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            stopAutoSlide();
            prevTestimonial();
            startAutoSlide();
        } else if (e.key === 'ArrowRight') {
            stopAutoSlide();
            nextTestimonial();
            startAutoSlide();
        }
    });
});
</script>
@endpush

@push('styles')
<style>
/* Modern Hero Section with Overlay Text */
.hero-section-modern {
    position: relative;
    min-height: 100vh;
    overflow: hidden;
}

.hero-container {
    position: relative;
    width: 100%;
    height: 100vh;
    min-height: 600px;
}

/* Background Image */
.hero-image-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.hero-bg-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center right;
}

/* Content Overlay */
.hero-content-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 2;
    background: linear-gradient(
        90deg,
        rgba(0, 51, 102, 0.85) 0%,
        rgba(0, 51, 102, 0.7) 30%,
        rgba(0, 51, 102, 0.4) 50%,
        rgba(0, 51, 102, 0.2) 70%,
        transparent 100%
    );
}

.min-vh-80 {
    min-height: 80vh;
}

/* Text Content */
.hero-text-content {
    position: relative;
    z-index: 3;
    max-width: 600px;
    padding: 2rem 0;
}

.hero-text-content h1 {
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
    font-weight: 700;
    line-height: 1.2;
}

.hero-text-content p {
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
    font-size: 1.25rem; /* text-xl */
    line-height: 1.6;
}

.hero-text-content .btn {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.hero-text-content .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

/* Mobile Responsive */
@media (max-width: 1199.98px) {
    .hero-content-overlay {
        background: linear-gradient(
            90deg,
            rgba(0, 51, 102, 0.9) 0%,
            rgba(0, 51, 102, 0.8) 40%,
            rgba(0, 51, 102, 0.6) 60%,
            rgba(0, 51, 102, 0.3) 80%,
            transparent 100%
        );
    }
}

@media (max-width: 991.98px) {
    .hero-container {
        height: 80vh;
        min-height: 500px;
    }
    
    .hero-content-overlay {
        background: linear-gradient(
            90deg,
            rgba(0, 51, 102, 0.95) 0%,
            rgba(0, 51, 102, 0.85) 50%,
            rgba(0, 51, 102, 0.7) 70%,
            rgba(0, 51, 102, 0.4) 85%,
            transparent 100%
        );
    }
    
    .hero-text-content {
        max-width: 100%;
        padding: 1.5rem 0;
    }
    
    .hero-text-content h1 {
        font-size: 2.5rem; /* text-4xl */
    }
    
    .hero-text-content p {
        font-size: 1.125rem; /* text-lg */
    }
}

@media (max-width: 767.98px) {
    .hero-section-modern {
        min-height: 100vh;
        position: relative;
    }
    
    .hero-container {
        height: 100vh;
        min-height: 100vh;
    }
    
    .hero-content-overlay {
        background: linear-gradient(
            180deg,
            rgba(0, 51, 102, 0.3) 0%,
            rgba(0, 51, 102, 0.2) 20%,
            rgba(0, 51, 102, 0.1) 40%,
            rgba(0, 51, 102, 0.05) 60%,
            rgba(0, 51, 102, 0.02) 80%,
            transparent 100%
        );
    }
    
    .hero-bg-image {
        object-position: center center;
    }
    
    .min-vh-80 {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .hero-text-content {
        padding: 2rem 1rem;
        text-align: center;
        background: rgba(0, 51, 102, 0.7);
        border-radius: 15px;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        max-width: 90%;
        margin: 0 auto;
    }
    
    .hero-text-content h1 {
        font-size: 2.25rem; /* text-4xl */
        text-align: center;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8);
    }
    
    .hero-text-content p {
        font-size: 1.125rem; /* text-lg */
        text-align: center;
        margin-bottom: 1.5rem;
        text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.8);
    }
    
    .hero-text-content .d-flex {
        justify-content: center;
    }
    
    /* Gradient fade to next section - Enhanced */
    .hero-section-modern::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 150px;
        background: linear-gradient(
            180deg,
            transparent 0%,
            rgba(248, 249, 250, 0.05) 10%,
            rgba(248, 249, 250, 0.15) 25%,
            rgba(248, 249, 250, 0.3) 40%,
            rgba(248, 249, 250, 0.5) 55%,
            rgba(248, 249, 250, 0.7) 70%,
            rgba(248, 249, 250, 0.85) 85%,
            rgba(248, 249, 250, 0.95) 95%,
            rgba(248, 249, 250, 1) 100%
        );
        z-index: 4;
    }
    
    /* Additional smooth transition */
    .hero-section-modern::before {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 200px;
        background: linear-gradient(
            180deg,
            rgba(248, 249, 250, 0.02) 0%,
            rgba(248, 249, 250, 0.08) 20%,
            rgba(248, 249, 250, 0.2) 40%,
            rgba(248, 249, 250, 0.4) 60%,
            rgba(248, 249, 250, 0.6) 80%,
            rgba(248, 249, 250, 0.8) 90%,
            rgba(248, 249, 250, 0.95) 95%,
            rgba(248, 249, 250, 1) 100%
        );
        z-index: 3;
    }
}

@media (max-width: 575.98px) {
    .hero-container {
        height: 100vh;
        min-height: 100vh;
    }
    
    .min-vh-80 {
        min-height: 100vh;
    }
    
    .hero-text-content {
        padding: 1.5rem 1rem;
        max-width: 95%;
    }
    
    .hero-text-content h1 {
        font-size: 1.875rem; /* text-3xl */
        margin-bottom: 0.75rem;
    }
    
    .hero-text-content p {
        font-size: 1rem; /* text-base */
        margin-bottom: 1rem;
    }
    
    .hero-text-content .btn {
        font-size: 0.875rem; /* text-sm */
        padding: 0.75rem 1.5rem;
    }
}

.hero-section {
    background: linear-gradient(135deg, #003366 0%, #004080 100%);
    min-height: 70vh;
}

.min-vh-50 {
    min-height: 50vh;
}

.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.category-card .card:hover {
    border-color: #003366 !important;
}

.course-card .card:hover {
    border-color: #ffcc33 !important;
}

.rating i {
    font-size: 0.9em; /* relative to parent */
}

.testimonial-card .card {
    border-left: 4px solid #ffcc33 !important;
}

/* Testimonials Grid Styles */
.testimonials-grid {
    position: relative;
}

.testimonials-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.testimonial-item {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease-in-out;
    display: none;
}

.testimonial-item.active {
    opacity: 1;
    transform: translateY(0);
    display: block;
}

.testimonial-card {
    height: 100%;
    transition: all 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}

.quote-icon {
    color: #003366;
    opacity: 0.1;
}

.testimonial-text p {
    font-size: 1.125rem; /* text-lg */
    line-height: 1.6;
    color: #555;
}

.author-avatar img {
    border: 3px solid #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.author-avatar img:hover {
    transform: scale(1.1);
}

.author-details h6 {
    color: #003366;
}

.rating i {
    font-size: 1.125rem; /* text-lg */
    margin-right: 2px;
}

/* Navigation Styles */
.testimonials-navigation {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.dots-container {
    display: flex;
    gap: 8px;
}

.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: none;
    background-color: #ddd;
    cursor: pointer;
    transition: all 0.3s ease;
}

.dot.active {
    background-color: #003366;
    transform: scale(1.3);
}

.dot:hover {
    background-color: #004080;
    transform: scale(1.1);
}

#prevBtn, #nextBtn {
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

#prevBtn:hover, #nextBtn:hover {
    background-color: #003366;
    color: white;
    transform: scale(1.1);
}

/* Responsive Design */
@media (max-width: 768px) {
    .testimonials-container {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .testimonials-navigation {
        flex-direction: column;
        gap: 1rem;
    }
    
    .testimonial-text p {
        font-size: 1rem; /* text-base */
    }
}

/* Animation pour l'apparition des témoignages */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.testimonial-item.active {
    animation: slideInUp 0.8s ease-out;
}
</style>

<script>
// Categories horizontal scroll functionality
function scrollCategories(direction) {
    const scrollContainer = document.getElementById('categoriesScroll');
    const scrollAmount = 250; // Distance de scroll en pixels
    
    if (direction === 'left') {
        scrollContainer.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    } else {
        scrollContainer.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    }
}

// Auto-hide scroll buttons and update mobile progress
document.addEventListener('DOMContentLoaded', function() {
    const scrollContainer = document.getElementById('categoriesScroll');
    const leftBtn = document.querySelector('.scroll-left');
    const rightBtn = document.querySelector('.scroll-right');
    const progressDots = document.getElementById('progressDots');
    
    // Create mobile progress dots
    function createProgressDots() {
        if (!progressDots) return;
        
        const categories = document.querySelectorAll('.category-item-scroll');
        const totalCategories = categories.length;
        const visibleCategories = Math.ceil(scrollContainer.clientWidth / 200); // Approximate visible count
        const totalPages = Math.ceil(totalCategories / visibleCategories);
        
        progressDots.innerHTML = '';
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('div');
            dot.className = 'progress-dot';
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => scrollToPage(i));
            progressDots.appendChild(dot);
        }
    }
    
    // Scroll to specific page
    function scrollToPage(pageIndex) {
        const categories = document.querySelectorAll('.category-item-scroll');
        const visibleCategories = Math.ceil(scrollContainer.clientWidth / 200);
        const targetIndex = pageIndex * visibleCategories;
        const targetCategory = categories[targetIndex];
        
        if (targetCategory) {
            targetCategory.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'start'
            });
        }
    }
    
    function updateScrollButtons() {
        const scrollLeft = scrollContainer.scrollLeft;
        const maxScroll = scrollContainer.scrollWidth - scrollContainer.clientWidth;
        
        // Show/hide left button
        if (leftBtn) {
            if (scrollLeft <= 0) {
                leftBtn.style.opacity = '0.5';
                leftBtn.style.pointerEvents = 'none';
            } else {
                leftBtn.style.opacity = '1';
                leftBtn.style.pointerEvents = 'auto';
            }
        }
        
        // Show/hide right button
        if (rightBtn) {
            if (scrollLeft >= maxScroll - 10) { // 10px tolerance
                rightBtn.style.opacity = '0.5';
                rightBtn.style.pointerEvents = 'none';
            } else {
                rightBtn.style.opacity = '1';
                rightBtn.style.pointerEvents = 'auto';
            }
        }
    }
    
    function updateProgressDots() {
        if (!progressDots) return;
        
        const scrollLeft = scrollContainer.scrollLeft;
        const maxScroll = scrollContainer.scrollWidth - scrollContainer.clientWidth;
        const progress = Math.min(scrollLeft / maxScroll, 1);
        
        const dots = progressDots.querySelectorAll('.progress-dot');
        const activeIndex = Math.round(progress * (dots.length - 1));
        
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === activeIndex);
        });
    }
    
    // Initial setup
    createProgressDots();
    updateScrollButtons();
    updateProgressDots();
    
    // Update on scroll
    scrollContainer.addEventListener('scroll', () => {
        updateScrollButtons();
        updateProgressDots();
    });
    
    // Update on resize
    window.addEventListener('resize', () => {
        createProgressDots();
        updateScrollButtons();
        updateProgressDots();
    });
});
</script>
@endpush