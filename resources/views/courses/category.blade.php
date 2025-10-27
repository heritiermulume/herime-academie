@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', $category->name . ' - Cours - Herime Academie')
@section('description', $category->description ?: 'Découvrez tous les cours de la catégorie ' . $category->name . ' sur Herime Academie.')

@section('content')
<!-- Category Header -->
<section class="py-5" style="background: linear-gradient(135deg, {{ $category->color }} 0%, {{ $category->color }}CC 100%); color: white;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.index') }}" class="text-white">Cours</a></li>
                        <li class="breadcrumb-item active text-white">{{ $category->name }}</li>
                    </ol>
                </nav>

                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white text-dark rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 4rem; height: 4rem;">
                        <i class="{{ $category->icon ?: 'fas fa-book' }} fa-2x" style="color: {{ $category->color }};"></i>
                    </div>
                    <div>
                        <h1 class="display-5 fw-bold mb-2">{{ $category->name }}</h1>
                        <p class="lead mb-0">{{ $courses->total() }} cours disponibles</p>
                    </div>
                </div>

                @if($category->description)
                <p class="lead">{{ $category->description }}</p>
                @endif
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <div class="bg-white bg-opacity-20 rounded-3 p-4" style="backdrop-filter: blur(10px);">
                    <h5 class="mb-3 text-white fw-bold">Statistiques</h5>
                    <div class="text-center">
                        <div class="h2 fw-bold text-white mb-2">{{ $courses->total() }}</div>
                        <small class="text-white-50 fs-5">Cours disponibles</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Courses Grid -->
<section class="py-5">
    <div class="container">
        @if($courses->count() > 0)
            <div class="row g-3">
                @foreach($courses as $course)
                <div class="col-lg-4 col-md-6 col-sm-6 course-item" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
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

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-5">
                {{ $courses->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="{{ $category->icon ?: 'fas fa-book' }} fa-5x" style="color: {{ $category->color }};"></i>
                </div>
                <h3>Aucun cours dans cette catégorie</h3>
                <p class="text-muted">Il n'y a pas encore de cours disponibles dans cette catégorie.</p>
                <a href="{{ route('courses.index') }}" class="btn btn-primary">
                    Voir tous les cours
                </a>
            </div>
        @endif
    </div>
</section>

<!-- Other Categories -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center" data-aos="fade-up">Autres catégories</h2>
        <div class="row g-3">
            @foreach($otherCategories as $otherCategory)
            <div class="col-lg-2 col-md-4 col-6" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <a href="{{ route('courses.category', $otherCategory->slug) }}" class="text-decoration-none">
                    <div class="category-card text-center h-100">
                        <div class="category-icon" style="background-color: {{ $otherCategory->color }}">
                            <i class="{{ $otherCategory->icon ?: 'fas fa-book' }}"></i>
                        </div>
                        <h6 class="mb-1">{{ $otherCategory->name }}</h6>
                        <small class="text-muted">{{ $otherCategory->courses_count }} cours</small>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.course-card .card:hover {
    border-color: #ffcc33 !important;
}

.rating i {
    font-size: 0.9em; /* relative to parent */
}

/* Forcer la couleur bleue du site pour les boutons */
.course-card .btn-primary,
.course-card .btn-primary:focus,
.course-card .btn-primary:active,
.course-card .btn-primary:visited {
    background-color: #003366 !important;
    border-color: #003366 !important;
    color: white !important;
    box-shadow: none !important;
}

.course-card .btn-primary:hover {
    background-color: #ffcc33 !important;
    border-color: #ffcc33 !important;
    color: #003366 !important;
    box-shadow: 0 4px 8px rgba(0, 51, 102, 0.3) !important;
}

.course-card .btn-outline-primary,
.course-card .btn-outline-primary:focus,
.course-card .btn-outline-primary:active,
.course-card .btn-outline-primary:visited {
    color: #003366 !important;
    border-color: #003366 !important;
    background-color: transparent !important;
    box-shadow: none !important;
}

.course-card .btn-outline-primary:hover {
    background-color: #003366 !important;
    border-color: #003366 !important;
    color: white !important;
    box-shadow: 0 4px 8px rgba(0, 51, 102, 0.3) !important;
}
    
    .course-thumbnail {
        height: 200px;
        background-size: cover;
        background-position: center;
        position: relative;
    }
    
    .course-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: var(--secondary-color);
        color: var(--text-dark);
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem; /* text-xs */
        font-weight: 600;
        z-index: 2;
    }
    
    .course-price {
        font-size: 1.25rem; /* text-xl */
        font-weight: 700;
        color: var(--primary-color);
    }
    
    .course-price-old {
        text-decoration: line-through;
        color: var(--text-light);
        font-size: 1rem; /* text-base */
    }
    
    .category-card {
        text-align: center;
        padding: 1.5rem 1rem;
        border-radius: 1rem;
        background: white;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .category-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: #667eea;
    }
    
    .category-icon {
        width: 3.5rem;
        height: 3.5rem;
        margin: 0 auto 1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem; /* text-2xl */
        color: white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .category-card:hover .category-icon {
        transform: scale(1.1);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    
    .category-card h6 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 0.5rem;
        transition: color 0.3s ease;
    }
    
    .category-card:hover h6 {
        color: #667eea;
    }
    
    .category-card small {
        color: #6c757d;
        font-size: 0.875rem; /* text-sm */
    }
    
    /* New enhanced styles for course cards */
    .course-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.3s ease;
    }
    
    .course-card:hover .course-overlay {
        opacity: 1;
    }
    
    .sale-badge {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        top: 3.5rem;
    }
    
    .level-badge {
        font-size: 0.75rem; /* text-xs */
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
    }
    
    .rating {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .rating i {
        color: #ffc107;
        font-size: 0.875rem; /* text-sm */
    }
    
    .rating-text {
        font-weight: 600;
        color: #333;
        margin-left: 0.5rem;
    }
    
    .card-title a {
        color: #2c3e50;
        font-weight: 600;
        line-height: 1.4;
        transition: color 0.3s ease;
    }
    
    .card-title a:hover {
        color: #3498db;
    }
    
    .course-meta {
        background: #f8f9fa;
        border-radius: 0.75rem;
        padding: 1rem;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        font-size: 0.875rem; /* text-sm */
        color: #6c757d;
        margin-bottom: 0.5rem;
    }
    
    .meta-item:last-child {
        margin-bottom: 0;
    }
    
    .meta-item i {
        width: 16px;
        text-align: center;
    }
    
    .course-footer {
        margin-top: auto;
    }
    
    .price-section {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .btn-light {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        font-weight: 600;
        color: #2c3e50;
        transition: all 0.3s ease;
    }
    
    .btn-light:hover {
        background: white;
        transform: scale(1.05);
    }
    
    /* Page spacing and layout */
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .row.g-4 {
        margin-left: -0.5rem;
        margin-right: -0.5rem;
    }
    
    .row.g-4 > [class*="col-"] {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    /* Card content spacing */
    .card-body {
        padding: 1.5rem;
    }
    
    .course-meta {
        margin: 1rem 0;
    }
    
    .course-footer {
        padding-top: 1rem;
        border-top: 1px solid #f1f3f4;
        margin-top: 1rem;
    }
    
    /* Statistics section improvements */
    .bg-white.bg-opacity-20 {
        background: rgba(255, 255, 255, 0.15) !important;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    .text-white-50 {
        color: rgba(255, 255, 255, 0.8) !important;
        font-weight: 500;
    }
    
    .text-white {
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .container {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        .course-card {
            margin: 0.25rem;
        }
        
        .course-thumbnail {
            height: 180px;
        }
        
        .course-meta {
            padding: 0.75rem;
        }
        
        .meta-item {
            font-size: 0.75rem; /* text-xs */
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .bg-white.bg-opacity-20 {
            margin-top: 2rem;
        }
    }
    
    @media (max-width: 576px) {
        .container {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        
        .course-card {
            margin: 0.125rem;
        }
        
        .bg-white.bg-opacity-20 {
            margin-top: 1.5rem;
        }
    }
    
    /* Harmonisation des cartes de cours avec les couleurs du site */
    
    /* Styles très spécifiques pour forcer l'application des couleurs */
    .course-card .card .card-body .card-actions .btn-primary {
        background: #003366 !important;
        background-color: #003366 !important;
        background-image: none !important;
        border-color: #003366 !important;
        color: white !important;
    }
    
    .course-card .card .card-body .card-actions .btn-primary:hover,
    .course-card .card .card-body .card-actions .btn-primary:focus,
    .course-card .card .card-body .card-actions .btn-primary:active {
        background: #004080 !important;
        background-color: #004080 !important;
        background-image: none !important;
        border-color: #004080 !important;
        color: white !important;
    }
    
    .course-card .card .card-body .card-actions .btn-outline-primary {
        color: #003366 !important;
        border-color: #003366 !important;
        background-color: transparent !important;
    }
    
    .course-card .card .card-body .card-actions .btn-outline-primary:hover,
    .course-card .card .card-body .card-actions .btn-outline-primary:focus,
    .course-card .card .card-body .card-actions .btn-outline-primary:active {
        background-color: #003366 !important;
        border-color: #003366 !important;
        color: white !important;
    }
    .course-card .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }
    
    .course-card .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
        border-color: #ffcc33 !important;
    }
    
    .course-card .card-title a {
        color: #003366;
        text-decoration: none;
    }
    
    .course-card .card-title a:hover {
        color: #004080;
        text-decoration: underline;
    }
    
    .instructor-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .rating i {
        color: #ffc107;
        font-size: 0.9em; /* relative to parent */
    }
    
    .price-duration {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .card-actions .btn {
        margin-bottom: 5px;
    }
    
    .course-card .btn-outline-primary {
        color: #003366 !important;
        border-color: #003366 !important;
        background-color: transparent !important;
    }
    
    .course-card .btn-outline-primary:hover {
        background-color: #003366 !important;
        border-color: #003366 !important;
        color: white !important;
    }
    
    .course-card .btn-primary {
        background: #003366 !important;
        background-color: #003366 !important;
        background-image: none !important;
        border-color: #003366 !important;
        color: white !important;
    }
    
    .course-card .btn-primary:hover {
        background: #004080 !important;
        background-color: #004080 !important;
        background-image: none !important;
        border-color: #004080 !important;
        color: white !important;
    }
    
    /* Styles plus spécifiques pour forcer l'application */
    .course-card .card-actions .btn-primary {
        background: #003366 !important;
        background-color: #003366 !important;
        background-image: none !important;
        border-color: #003366 !important;
        color: white !important;
    }
    
    .course-card .card-actions .btn-primary:hover {
        background: #004080 !important;
        background-color: #004080 !important;
        background-image: none !important;
        border-color: #004080 !important;
        color: white !important;
    }
    
    .course-card .card-actions .btn-outline-primary {
        color: #003366 !important;
        border-color: #003366 !important;
        background-color: transparent !important;
    }
    
    .course-card .card-actions .btn-outline-primary:hover {
        background-color: #003366 !important;
        border-color: #003366 !important;
        color: white !important;
    }
    
    .text-primary {
        color: #003366 !important;
    }
</style>
@endpush

