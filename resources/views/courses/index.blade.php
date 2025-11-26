@extends('layouts.app')

@section('title', 'Tous les cours - Herime Academie')
@section('description', 'Découvrez notre collection complète de cours en ligne. Formations professionnelles dans tous les domaines.')

@section('content')
<!-- Page Content Section -->
<section class="page-content-section" style="padding: 0.8rem 0 1.4rem;">
    <div class="container">
        @php
            $filtersActive = request()->filled('category')
                || request()->filled('level')
                || request()->filled('price')
                || request()->filled('sort');
            $currentCategoryId = request('category');
        @endphp

        <div class="row align-items-center courses-page-head mb-0 mb-md-3">
            <div class="col-12">
                <h1 class="courses-page-title">Trouver le cours idéal pour progresser</h1>
                <p class="courses-page-description">
                    Parcourez toutes nos formations en ligne, filtrez par catégorie, niveau et prix pour dénicher le programme qui correspond à vos objectifs.
                </p>
            </div>
        </div>

        <div class="row courses-page-layout">
            <aside class="col-lg-3">
                <div class="courses-categories-panel">
                    <div class="courses-categories-panel__header">
                        <i class="fas fa-layer-group me-2"></i>
                        <span>Catégories</span>
                    </div>
                    <nav class="courses-categories-panel__nav">
                        <a
                            href="{{ route('courses.index', request()->except(['page', 'category'])) }}"
                            class="courses-categories-panel__link {{ empty($currentCategoryId) ? 'is-active' : '' }}"
                        >
                            <span>Toutes les catégories</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        @foreach($categories as $category)
                            <a
                                href="{{ route('courses.index', array_merge(request()->except(['page', 'category']), ['category' => $category->id])) }}"
                                class="courses-categories-panel__link {{ (int) $currentCategoryId === (int) $category->id ? 'is-active' : '' }}"
                            >
                                <span>{{ $category->name }}</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            <div class="col-lg-9 mt-0 mt-lg-0">
                <!-- Filters -->
                <div class="courses-search-wrapper mb-4">
                    <form method="GET" action="{{ route('courses.index') }}" class="courses-search">
                        <div class="courses-search__bar">
                            <label for="search" class="visually-hidden">Recherche</label>
                            <div class="courses-search__input-wrapper">
                                <i class="fas fa-search"></i>
                                <input
                                    type="text"
                                    id="search"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Rechercher un cours, un formateur..."
                                >
                                <button type="submit" class="btn btn-primary courses-search__submit-btn" aria-label="Rechercher">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <div class="courses-search__actions">
                            <button
                                class="courses-search__filters-btn toggle-filters-btn {{ $filtersActive ? 'is-active' : '' }}"
                                type="button"
                                id="courses-filter-toggle"
                                aria-expanded="{{ $filtersActive ? 'true' : 'false' }}"
                                aria-controls="courses-filter-panel"
                            >
                                <i class="fas fa-sliders-h me-2"></i>
                                Filtres avancés
                            </button>
                            <a href="{{ route('courses.index') }}" class="courses-search__reset-btn">
                                <i class="fas fa-rotate-left me-2"></i>
                                Réinitialiser
                            </a>
                        </div>

                        <div
                            id="courses-filter-panel"
                            class="courses-search__filters{{ $filtersActive ? ' is-visible' : '' }}"
                        >
                            <div class="row g-3">
                                <div class="col-md-3 col-sm-6">
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
                                <div class="col-md-3 col-sm-6">
                                    <label for="level" class="form-label">Niveau</label>
                                    <select class="form-select" id="level" name="level">
                                        <option value="">Tous les niveaux</option>
                                        <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>Débutant</option>
                                        <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>Intermédiaire</option>
                                        <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>Avancé</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label for="price" class="form-label">Prix</label>
                                    <select class="form-select" id="price" name="price">
                                        <option value="">Tous les prix</option>
                                        <option value="free" {{ request('price') == 'free' ? 'selected' : '' }}>Gratuit</option>
                                        <option value="paid" {{ request('price') == 'paid' ? 'selected' : '' }}>Payant</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label for="sort" class="form-label">Trier par</label>
                                    <select class="form-select" id="sort" name="sort">
                                        <option value="popular" {{ (request('sort') == 'popular' || !request('sort')) ? 'selected' : '' }}>Plus populaires</option>
                                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Plus récents</option>
                                        <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Mieux notés</option>
                                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Prix croissant</option>
                                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Prix décroissant</option>
                                    </select>
                                </div>
                            </div>
                            <div class="courses-search__filters-actions">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">
                                    Appliquer les filtres
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Results -->
                <div class="row">
                    <div class="col-12">
                        @if($courses->count() > 0)
                        <div id="courses-container" class="row g-3">
                            @foreach($courses as $course)
                            <div class="col-lg-4 col-md-6 col-sm-6 course-item">
                                <div class="course-card" data-course-url="{{ route('courses.show', $course->slug) }}" style="cursor: pointer;">
                                    <div class="card course-card-inner" style="position: relative;">
                                        
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
                                                    <i class="fas fa-user me-1"></i>{{ Str::limit($course->instructor->name, 20) }}
                                                </small>
                                                <div class="rating">
                                                    <i class="fas fa-star"></i>
                                                    <span>{{ number_format($course->stats['average_rating'] ?? 0, 1) }}</span>
                                                    <span class="text-muted">({{ $course->stats['total_reviews'] ?? 0 }})</span>
                                                </div>
                                            </div>
                                            
                                            @if($course->show_students_count && isset($course->stats['total_students']))
                                            <div class="students-count mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-users me-1"></i>
                                                    {{ number_format($course->stats['total_students'], 0, ',', ' ') }} 
                                                    {{ $course->stats['total_students'] > 1 ? 'étudiants inscrits' : 'étudiant inscrit' }}
                                                </small>
                                            </div>
                                            @endif
                                            
                                            <div class="price-duration">
                                                <div class="price">
                                                    @if($course->is_free)
                                                        <span class="text-success fw-bold">Gratuit</span>
                                                    @else
                                                        @if($course->is_sale_active && $course->active_sale_price !== null)
                                                            <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->active_sale_price) }}</span>
                                                            <small class="text-muted text-decoration-line-through ms-1">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</small>
                                                        @else
                                                            <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                                @if($course->is_sale_active && $course->sale_end_at)
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
                                                @endif
                                            </div>
                                            
                                            <div class="card-actions mt-2" onclick="event.stopPropagation(); event.preventDefault();">
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
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.courses-page-head {
    row-gap: 0.6rem;
}

.courses-page-title {
    margin: 0;
    font-size: clamp(1.55rem, 1.35rem + 0.6vw, 1.95rem);
    font-weight: 700;
    color: #0f172a;
}

.courses-page-description {
    margin-top: 0.06rem;
    margin-bottom: 0;
    color: #475569;
    max-width: 640px;
}

.courses-page-layout {
    row-gap: 2.5rem;
}

.courses-categories-panel {
    position: sticky;
    top: calc(var(--site-navbar-height, 64px) + 1.5rem);
    border-radius: 1.5rem;
    overflow: hidden;
    box-shadow: 0 20px 45px -35px rgba(15, 23, 42, 0.35);
}

.courses-categories-panel__header {
    display: flex;
    align-items: center;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(180deg, #003366 0%, #022447 100%);
    color: #ffffff;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.95rem;
}

.courses-categories-panel__nav {
    display: flex;
    flex-direction: column;
    background: #ffffff;
}

.courses-categories-panel__link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.75rem 1.2rem;
    color: #1f2937;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.82rem;
    transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
    border-bottom: 1px solid #f1f5f9;
}

.courses-categories-panel__link:last-child {
    border-bottom: 0;
}

.courses-categories-panel__link i {
    font-size: 0.85rem;
    color: #94a3b8;
    transition: transform 0.2s ease, color 0.2s ease;
}

.courses-categories-panel__link:hover {
    background: rgba(12, 74, 110, 0.08);
    color: #0c4a6e;
    transform: translateX(4px);
}

.courses-categories-panel__link:hover i {
    transform: translateX(4px);
    color: #0c4a6e;
}

.courses-categories-panel__link.is-active {
    background: rgba(12, 74, 110, 0.12);
    color: #0c4a6e;
}

.courses-categories-panel__link.is-active i {
    color: #0c4a6e;
}

.courses-search-wrapper {
    background: #ffffff;
    border-radius: 1.25rem;
    padding: clamp(0.85rem, 0.8rem + 0.4vw, 1.25rem);
    box-shadow: 0 20px 40px -38px rgba(15, 23, 42, 0.4);
    border: 1px solid rgba(226, 232, 240, 0.7);
}

.courses-search {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.courses-search__bar {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.courses-search__input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.3rem 0.3rem 0.3rem 0.75rem;
    gap: 0.55rem;
}

.courses-search__input-wrapper i {
    color: #0ea5e9;
    font-size: 0.9rem;
}

.courses-search__input-wrapper input {
    flex: 1 1 auto;
    border: 0;
    background: transparent;
    padding: 0.28rem 0.55rem;
    font-size: 0.9rem;
    min-width: 0;
}

.courses-search__input-wrapper input:focus {
    outline: none;
}

.courses-search__input-wrapper button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    padding: 0.4rem 1.15rem;
    font-weight: 600;
    font-size: 0.9rem;
    flex: 0 0 auto;
}

.courses-search__submit-btn i {
    font-size: 0.95rem;
}

.courses-search__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
}

.courses-search__filters-btn,
.courses-search__reset-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    border: 1px solid #e1e7ef;
    background: #ffffff;
    color: #0f172a;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.88rem;
    transition: background 0.2s ease, border-color 0.2s ease;
}

.courses-search__filters-btn:hover,
.courses-search__filters-btn:focus,
.courses-search__reset-btn:hover,
.courses-search__reset-btn:focus {
    background: rgba(14, 165, 233, 0.12);
    border-color: #0ea5e9;
    color: #0f172a;
}

.courses-search__filters-btn.is-active {
    background: #0ea5e9;
    border-color: #0ea5e9;
    color: #ffffff;
}

.courses-search__filters-btn.is-active:hover {
    background: #0284c7;
}

.courses-search__filters {
    border-top: 1px solid rgba(226, 232, 240, 0.6);
    padding-top: 0.85rem;
    display: none;
}

.courses-search__filters.is-visible {
    display: block;
}

.courses-search__filters-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 0.65rem;
}

.courses-search__filters .form-label {
    font-weight: 600;
    color: #0f172a;
}

.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.course-card-inner {
    padding: 0.6rem;
    border: 1px solid rgba(226, 232, 240, 0.7);
    border-radius: 1.1rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
}

.course-card-inner:hover {
    border-color: #ffcc33 !important;
    box-shadow: 0 12px 30px -18px rgba(15, 23, 42, 0.3);
}

.course-card-inner .card-body {
    padding: 0.6rem 0 0;
}

.course-card-inner .card-body .card-text {
    font-weight: 400;
    color: #475569;
    font-size: 0.92rem;
    line-height: 1.45;
}

.course-card-inner .card-body .price {
    font-size: 1rem;
    font-weight: 600;
}

.price-duration {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.35rem;
}

.price-duration .promotion-countdown {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.8rem;
    font-weight: 400;
    color: #ef4444;
}

.price-duration .promotion-countdown i {
    font-size: 0.8rem;
}

@media (max-width: 991.98px) {
    .page-content-section .container {
        padding-top: calc(var(--site-navbar-height, 64px) + 0.1rem);
    }

    .courses-page-head {
        text-align: center;
        margin-top: 0;
    }

    .courses-page-description {
        margin-inline: auto;
    }

    .courses-search__filters-actions {
        flex-direction: column;
        gap: 0.45rem;
        align-items: stretch;
    }

    .courses-search__filters-actions .btn {
        width: 100%;
    }

    .courses-search__bar {
        gap: 0.5rem;
    }

    .courses-search__input-wrapper {
        padding: 0.3rem 0.35rem;
        border-radius: 9px;
        gap: 0.4rem;
    }

    .courses-search__input-wrapper input {
        font-size: 0.78rem;
    }

    .courses-search__input-wrapper button {
        padding: 0.32rem 0.75rem;
        font-size: 0.78rem;
        min-height: 36px;
        width: auto;
    }
    
    .courses-search__submit-btn {
        padding: 0.3rem 0.6rem;
        min-height: 34px;
    }

    .courses-search__actions {
        width: 100%;
        gap: 0.25rem;
    }

    .courses-search__filters-btn,
    .courses-search__reset-btn {
        flex: 1 1 0;
        justify-content: center;
        font-size: 0.7rem;
        padding: 0.32rem 0.65rem;
        min-height: 34px;
    }

    .courses-categories-panel {
        position: fixed;
        top: calc(var(--site-navbar-height, 64px));
        left: 0;
        right: 0;
        z-index: 1030;
        border-radius: 0;
        box-shadow: 0 20px 40px -25px rgba(2, 36, 71, 0.55);
    }

    .courses-categories-panel__header {
        display: none;
    }

    .courses-categories-panel__nav {
        display: flex;
        flex-direction: row;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        gap: 0.25rem;
        padding: 0.65rem 0.75rem;
    }

    .courses-categories-panel__nav::-webkit-scrollbar {
        display: none;
    }

    .courses-categories-panel__link {
        flex: 0 0 auto;
        border: 0;
        border-radius: 999px;
        padding: 0.65rem 1.1rem;
        background: rgba(15, 23, 42, 0.05);
        color: #0f172a;
        font-size: 0.7rem;
    }

    .courses-categories-panel__link i {
        display: none;
    }

    .courses-categories-panel__link.is-active {
        background: #ffffff;
        color: #0c4a6e;
        box-shadow: 0 14px 30px -20px rgba(15, 23, 42, 0.55);
    }

    .course-card-inner {
        padding: 0.5rem;
    }

    .course-card-inner .card-body {
        padding-top: 0.45rem;
        padding-bottom: 0.45rem;
    }

    .price-duration {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .price-duration .promotion-countdown {
        margin-left: auto;
        font-size: 0.82rem;
    }

    .courses-page-title {
        font-size: 1.15rem;
    }

    .courses-page-description {
        font-size: 0.85rem;
        margin-top: 0.1rem;
    }

    .courses-page-layout {
        padding-top: 0.1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
let currentPage = 1;
let isLoading = false;
let hasMore = true;
let filterToggle;
let filterPanel;

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
            
            // Réinitialiser les compteurs à rebours pour les nouveaux éléments
            setTimeout(() => {
                if (typeof window.initPromotionCountdowns === 'function') {
                    window.initPromotionCountdowns();
                }
            }, 150);
            
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
    
    const hasActiveSale = Boolean(course.is_sale_active) && course.active_sale_price !== null;
    const priceHtml = course.is_free ? 
        '<span class="text-success fw-bold">Gratuit</span>' :
        hasActiveSale ?
            `<span class="text-primary fw-bold">$${parseFloat(course.active_sale_price).toFixed(2)}</span>
             <small class="text-muted text-decoration-line-through ms-1">$${parseFloat(course.price).toFixed(2)}</small>` :
            `<span class="text-primary fw-bold">$${parseFloat(course.price).toFixed(2)}</span>`;
    
    const badgesHtml = `
        <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
            ${course.is_featured ? '<span class="badge bg-warning">En vedette</span>' : ''}
            ${course.is_free ? '<span class="badge bg-success">Gratuit</span>' : ''}
            ${course.sale_discount_percentage ? `<span class="badge bg-danger">-${course.sale_discount_percentage}%</span>` : ''}
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
                    ${course.show_students_count && course.stats?.total_students ? `
                    <div class="students-count mb-2">
                        <small class="text-muted">
                            <i class="fas fa-users me-1"></i>
                            ${parseInt(course.stats.total_students).toLocaleString('fr-FR')} 
                            ${parseInt(course.stats.total_students) > 1 ? 'étudiants inscrits' : 'étudiant inscrit'}
                        </small>
                    </div>
                    ` : ''}
                    
                    <div class="price-duration">
                        <div class="price">
                            ${priceHtml}
                        </div>
                        ${hasActiveSale && course.sale_end_at ? 
                            `<div class="promotion-countdown" data-sale-end="${course.sale_end_at}">
                                <i class="fas fa-fire me-1 text-danger"></i>
                                <span class="countdown-text">
                                    <span class="countdown-years">0</span><span>a</span> 
                                    <span class="countdown-months">0</span><span>m</span> 
                                    <span class="countdown-days">0</span>j 
                                    <span class="countdown-hours">0</span>h 
                                    <span class="countdown-minutes">0</span>min
                                </span>
                            </div>` : ''
                        }
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
    
    filterPanel = document.getElementById('courses-filter-panel');
    filterToggle = document.getElementById('courses-filter-toggle');

    if (filterPanel && filterToggle) {
        const updateToggleState = (isShown) => {
            if (isShown) {
                filterToggle.classList.add('is-active');
                filterToggle.setAttribute('aria-expanded', 'true');
                filterToggle.innerHTML = '<i class="fas fa-sliders-h me-2"></i>Masquer les filtres';
            } else {
                filterToggle.classList.remove('is-active');
                filterToggle.setAttribute('aria-expanded', 'false');
                filterToggle.innerHTML = '<i class="fas fa-sliders-h me-2"></i>Filtres avancés';
            }
        };

        updateToggleState(filterPanel.classList.contains('is-visible'));

        filterToggle.addEventListener('click', (event) => {
            event.preventDefault();
            const willShow = !filterPanel.classList.contains('is-visible');
            filterPanel.classList.toggle('is-visible', willShow);
            updateToggleState(willShow);
        });
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