@extends('layouts.app')

@section('title', 'Toutes les catégories - Herime Academie')
@section('description', 'Découvrez toutes nos catégories de cours en ligne. Formations professionnelles dans tous les domaines.')

@section('content')
<style>
/* Variables CSS pour la cohérence avec les cours */
:root {
    --primary-color: #003366;
    --accent-color: #ffcc33;
    --secondary-color: #f8f9fa;
    --text-color: #2c3e50;
    --text-muted: #6c757d;
    --border-color: #e9ecef;
}

/* Styles harmonisés avec les cartes de cours */
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.category-card .card:hover {
    border-color: var(--accent-color) !important;
}

/* Forcer l'affichage en 2 colonnes sur mobile */
@media (max-width: 767.98px) {
    .row.g-3 .category-item {
        flex: 0 0 50% !important;
        max-width: 50% !important;
        width: 50% !important;
    }
    
    /* Styles pour les catégories mobiles compactes */
    .category-card .card {
        height: 180px !important;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .category-card .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .category-card .card-body {
        padding: 1rem !important;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }
    
    .category-card .category-icon {
        margin-bottom: 0.5rem !important;
    }
    
    .category-card .category-icon i {
        font-size: 1.5rem !important;
        color: var(--primary-color) !important;
    }
    
    .category-card .category-icon i.fa-2x {
        color: var(--primary-color) !important;
    }
    
    .category-card .card-title {
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        margin-bottom: 0.5rem !important;
        line-height: 1.2;
        height: 1.2rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .category-card .card-text {
        font-size: 0.75rem !important;
        line-height: 1.3;
        margin-bottom: 0.5rem !important;
        height: 2.5rem !important;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    .category-card .badge {
        font-size: 0.65rem !important;
        padding: 0.25rem 0.5rem !important;
        border-radius: 12px;
        align-self: center;
    }
}

/* Styles pour desktop */
@media (min-width: 768px) {
    .category-card .card {
        height: 220px;
        border-radius: 12px;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }
    
    .category-card .card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        border-color: var(--accent-color) !important;
    }
    
    .category-card .card-body {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }
    
    .category-card .category-icon i {
        font-size: 2.5rem;
        color: var(--primary-color) !important;
    }
    
    .category-card .category-icon i.fa-3x {
        color: var(--primary-color) !important;
    }
    
    .category-card .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: var(--text-color);
    }
    
    .category-card .card-text {
        font-size: 0.85rem;
        line-height: 1.4;
        margin-bottom: 1rem;
        color: var(--text-muted);
    }
    
    .category-card .badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        align-self: center;
        background-color: var(--primary-color) !important;
        color: white !important;
    }
}

/* Bouton de retour harmonisé */
.btn-outline-primary {
    color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
}

.btn-outline-primary:hover {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    color: white !important;
}

/* Titre harmonisé */
.display-4 {
    color: var(--text-color) !important;
}

.lead {
    color: var(--text-muted) !important;
}
</style>

<div class="container py-5">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-3">Toutes les catégories</h1>
            <p class="lead text-muted">
                Explorez nos {{ $categories->count() }} catégories spécialisées
            </p>
        </div>
    </div>

    <!-- Categories Grid -->
    <div class="row g-3 g-md-4">
        @foreach($categories as $category)
        <div class="col-6 col-md-4 col-lg-3 category-item">
            <div class="category-card h-100">
                <a href="{{ route('courses.category', $category->slug) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body text-center p-3 p-md-4">
                            @if($category->icon)
                            <div class="category-icon mb-2 mb-md-3">
                                <i class="{{ $category->icon }} fa-2x fa-md-3x"></i>
                            </div>
                            @endif
                            <h6 class="card-title fw-bold mb-2">{{ Str::limit($category->name, 20) }}</h6>
                            <p class="card-text small mb-2" style="height: 2.5rem; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ Str::limit($category->description, 60) }}</p>
                            <span class="badge small" style="background-color: #003366; color: white;">{{ $category->courses_count ?? 0 }} cours</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Back to Home -->
    <div class="text-center mt-5">
        <a href="{{ route('home') }}" class="btn btn-outline-primary btn-lg">
            <i class="fas fa-arrow-left me-2"></i>Retour à l'accueil
        </a>
    </div>
</div>
@endsection
