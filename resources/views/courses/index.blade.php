@extends('layouts.app')

@section('title', 'Tous les cours - Herime Academie')
@section('description', 'Découvrez notre collection complète de cours en ligne. Formations professionnelles dans tous les domaines.')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-3">Tous les cours</h1>
            <p class="lead text-muted">
                Explorez notre collection de cours en ligne
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('courses.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Rechercher</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Nom du cours, formateur...">
                        </div>
                        <div class="col-md-2">
                            <label for="category" class="form-label">Catégorie</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Toutes les catégories</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="level" class="form-label">Niveau</label>
                            <select class="form-select" id="level" name="level">
                                <option value="">Tous les niveaux</option>
                                <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>Débutant</option>
                                <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>Intermédiaire</option>
                                <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>Avancé</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="price" class="form-label">Prix</label>
                            <select class="form-select" id="price" name="price">
                                <option value="">Tous les prix</option>
                                <option value="free" {{ request('price') == 'free' ? 'selected' : '' }}>Gratuit</option>
                                <option value="paid" {{ request('price') == 'paid' ? 'selected' : '' }}>Payant</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort" class="form-label">Trier par</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="popular" {{ (request('sort') == 'popular' || !request('sort')) ? 'selected' : '' }}>Plus populaires</option>
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Plus récents</option>
                                <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Mieux notés</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Prix croissant</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Prix décroissant</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="row">
        <div class="col-12">
            @if($courses->count() > 0)
                <div id="courses-container" class="row g-3">
                    @foreach($courses as $course)
                    <div class="col-lg-4 col-md-6 col-sm-6 course-item">
                        <div class="course-card">
                            <div class="card">
                                <div class="position-relative">
                                    <img src="{{ $course->thumbnail ? Storage::url($course->thumbnail) : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop' }}" 
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

                <!-- Loading Indicator -->
                <div id="loading-indicator" class="text-center mt-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement de plus de cours...</p>
                </div>

                <!-- End Indicator -->
                <div id="end-indicator" class="text-center mt-5" style="display: none;">
                    <p class="text-muted">Vous avez vu tous les cours disponibles.</p>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-muted"></i>
                    </div>
                    <h3 class="text-muted">Aucun cours trouvé</h3>
                    <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                    <a href="{{ route('courses.index') }}" class="btn btn-primary">
                        Voir tous les cours
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
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

.pagination {
    justify-content: center;
}

.page-link {
    color: #003366;
    border-color: #dee2e6;
}

.page-link:hover {
    color: #ffcc33;
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.page-item.active .page-link {
    background-color: #003366;
    border-color: #003366;
}
</style>
@endpush

@push('scripts')
<script>
let currentPage = 1;
let isLoading = false;
let hasMore = true;

// Fonction pour charger plus de cours
function loadMoreCourses() {
    if (isLoading || !hasMore) return;
    
    isLoading = true;
    currentPage++;
    
    document.getElementById('loading-indicator').style.display = 'block';
    
    const params = new URLSearchParams(window.location.search);
    params.set('infinite_scroll', '1');
    params.set('page', currentPage);
    
    fetch(`{{ route('courses.index') }}?${params.toString()}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.courses && data.courses.length > 0) {
            const container = document.getElementById('courses-container');
            data.courses.forEach(course => {
                const courseElement = createCourseElement(course);
                container.appendChild(courseElement);
            });
            
            hasMore = data.hasMore;
            if (!hasMore) {
                document.getElementById('end-indicator').style.display = 'block';
            }
        } else {
            hasMore = false;
            document.getElementById('end-indicator').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Erreur lors du chargement des cours:', error);
        hasMore = false;
    })
    .finally(() => {
        isLoading = false;
        document.getElementById('loading-indicator').style.display = 'none';
    });
}

// Fonction pour créer un élément de cours
function createCourseElement(course) {
    const div = document.createElement('div');
    div.className = 'col-lg-4 col-md-6 course-item';
    
    const priceHtml = course.is_free ? 
        '<span class="text-success fw-bold">Gratuit</span>' :
        course.sale_price ?
            `<span class="text-primary fw-bold">$${parseFloat(course.sale_price).toFixed(2)}</span>
             <small class="text-muted text-decoration-line-through ms-1">$${parseFloat(course.price).toFixed(2)}</small>` :
            `<span class="text-primary fw-bold">$${parseFloat(course.price).toFixed(2)}</span>`;
    
    const badgesHtml = `
        <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
            ${course.is_featured ? '<span class="badge bg-warning">En vedette</span>' : ''}
            ${course.is_free ? '<span class="badge bg-success">Gratuit</span>' : ''}
            ${course.sale_price ? `<span class="badge bg-danger">-${Math.round(((course.price - course.sale_price) / course.price) * 100)}%</span>` : ''}
        </div>
    `;
    
    div.innerHTML = `
        <div class="course-card">
            <div class="card">
                <div class="position-relative">
                    <img src="${course.thumbnail ? '/storage/' + course.thumbnail : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop'}" 
                         class="card-img-top" alt="${course.title}">
                    ${badgesHtml}
                </div>
                <div class="card-body">
                    <h6 class="card-title">
                        <a href="/courses/${course.slug}">
                            ${course.title.length > 50 ? course.title.substring(0, 50) + '...' : course.title}
                        </a>
                    </h6>
                    <p class="card-text">${course.short_description ? (course.short_description.length > 100 ? course.short_description.substring(0, 100) + '...' : course.short_description) : ''}</p>
                    
                    <div class="instructor-info">
                        <small class="instructor-name">
                            <i class="fas fa-user me-1"></i>${course.instructor.name.length > 20 ? course.instructor.name.substring(0, 20) + '...' : course.instructor.name}
                        </small>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <span>${(course.stats?.average_rating || 0).toFixed(1)}</span>
                            <span class="text-muted">(${course.stats?.total_reviews || 0})</span>
                        </div>
                    </div>
                    
                    <div class="price-duration">
                        <div class="price">
                            ${priceHtml}
                        </div>
                        <small class="duration">
                            <i class="fas fa-clock me-1"></i>${course.stats?.total_duration || 0} min
                        </small>
                    </div>
                    
                    <div class="card-actions">
                        <div class="course-button-container" data-course-id="${course.id}" data-course-free="${course.is_free}">
                            ${!course.is_free ? `
                                <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="addToCart(${course.id})">
                                    <i class="fas fa-shopping-cart me-1"></i>Ajouter au panier
                                </button>
                                <button type="button" class="btn btn-success btn-sm w-100" onclick="proceedToCheckout(${course.id})">
                                    <i class="fas fa-credit-card me-1"></i>Procéder au paiement
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    return div;
}

// Observer pour détecter quand l'utilisateur arrive en bas de page
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && hasMore && !isLoading) {
            loadMoreCourses();
        }
    });
}, {
    threshold: 0.1
});

// Initialiser le scroll infini
document.addEventListener('DOMContentLoaded', function() {
    const loadingIndicator = document.getElementById('loading-indicator');
    if (loadingIndicator) {
        observer.observe(loadingIndicator);
    }
    
    // Mettre à jour le compteur du panier (attendre que la fonction globale soit chargée)
    setTimeout(() => {
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        }
    }, 100);
});

// La fonction addToCart est maintenant définie globalement dans app.blade.php

// La fonction updateCartCount est maintenant définie globalement dans app.blade.php
</script>
@endpush