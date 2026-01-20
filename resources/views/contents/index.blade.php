@extends('layouts.app')

@section('title', 'Tous les contenus - Herime Academie')
@section('description', 'Découvrez notre collection complète de contenus en ligne. Formations professionnelles dans tous les domaines.')

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
                <h1 class="courses-page-title">Trouver le contenu idéal pour progresser</h1>
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
                            href="{{ route('contents.index', request()->except(['page', 'category'])) }}"
                            class="courses-categories-panel__link {{ empty($currentCategoryId) ? 'is-active' : '' }}"
                        >
                            <span>Toutes les catégories</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        @foreach($categories as $category)
                            <a
                                href="{{ route('contents.index', array_merge(request()->except(['page', 'category']), ['category' => $category->id])) }}"
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
                <!-- Search Panel (système global comme dans l'admin) -->
                <div class="mb-4">
                    <x-admin.search-panel
                        :action="route('contents.index')"
                        formId="coursesFilterForm"
                        filtersId="coursesFilters"
                        :hasFilters="true"
                        searchName="search"
                        :searchValue="request('search')"
                        placeholder="Rechercher un contenu, un prestataire, une catégorie..."
                    >
                        <x-slot:filters>
                            <div class="admin-form-grid admin-form-grid--two mb-3">
                                <div>
                                    <label class="form-label fw-semibold">Catégorie</label>
                                    <select class="form-select" name="category">
                                        <option value="">Toutes les catégories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold">Niveau</label>
                                    <select class="form-select" name="level">
                                        <option value="">Tous les niveaux</option>
                                        <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>Débutant</option>
                                        <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>Intermédiaire</option>
                                        <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>Avancé</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold">Prix</label>
                                    <select class="form-select" name="price">
                                        <option value="">Tous les prix</option>
                                        <option value="free" {{ request('price') == 'free' ? 'selected' : '' }}>Gratuit</option>
                                        <option value="paid" {{ request('price') == 'paid' ? 'selected' : '' }}>Payant</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold">Trier par</label>
                                    <select class="form-select" name="sort">
                                        <option value="popular" {{ (request('sort') == 'popular' || !request('sort')) ? 'selected' : '' }}>Plus populaires</option>
                                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Plus récents</option>
                                        <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Mieux notés</option>
                                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Prix croissant</option>
                                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Prix décroissant</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <span class="text-muted small">Ajustez les filtres puis appliquez-les.</span>
                                <a href="{{ route('contents.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo me-2"></i>Réinitialiser
                                </a>
                            </div>
                        </x-slot:filters>
                    </x-admin.search-panel>

                    <!-- Filtres actifs -->
                    @if(request()->hasAny(['search', 'category', 'level', 'price', 'sort']))
                        <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                            <div>
                                <i class="fas fa-filter me-2"></i><strong>Filtres actifs :</strong>
                                @if(request('search'))
                                    <span class="badge bg-primary ms-2">Recherche: "{{ request('search') }}"</span>
                                @endif
                                @if(request('category'))
                                    <span class="badge bg-info ms-2">Catégorie: {{ $categories->firstWhere('id', request('category'))->name ?? 'N/A' }}</span>
                                @endif
                                @if(request('level'))
                                    <span class="badge bg-warning ms-2">Niveau: {{ ucfirst(request('level')) }}</span>
                                @endif
                                @if(request('price'))
                                    <span class="badge bg-success ms-2">Prix: {{ request('price') == 'free' ? 'Gratuit' : 'Payant' }}</span>
                                @endif
                                @if(request('sort'))
                                    <span class="badge bg-secondary ms-2">Tri: {{ ucfirst(str_replace('_', ' ', request('sort'))) }}</span>
                                @endif
                            </div>
                            <a href="{{ route('contents.index') }}" class="btn btn-sm btn-outline-danger clear-filters-btn">
                                <i class="fas fa-times me-1"></i>Effacer les filtres
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Results -->
                <div class="row">
                    <div class="col-12">
                        @if($courses->count() > 0)
                        <div id="courses-container" class="row g-3">
                            @foreach($courses as $course)
                            <div class="col-lg-4 col-md-6 col-sm-6 course-item">
                                <div class="course-card" data-course-url="{{ route('contents.show', $course->slug) }}" style="cursor: pointer;">
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
                                                        // Téléchargeable gratuit : téléchargements uniques
                                                        $count = (int) ($course->stats['unique_downloads'] ?? $course->unique_downloads_count ?? 0);
                                                        $label = $count > 1 ? 'téléchargements' : 'téléchargement';
                                                        $icon = 'fa-download';
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
                                            
                                            <div class="card-actions mt-2" onclick="event.stopPropagation(); event.preventDefault();">
                                                <x-contenu-button :course="$course" size="small" :show-cart="false" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($courses->hasPages())
                            <div class="mt-5 d-flex justify-content-center">
                                {{ $courses->appends(request()->query())->links('pagination.bootstrap-5') }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-search fa-4x text-muted"></i>
                            </div>
                            <h3 class="text-muted">Aucun contenu trouvé</h3>
                            <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                            <a href="{{ route('contents.index') }}" class="btn btn-primary">
                                Voir tous les contenus
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

/* Styles pour le panneau de recherche admin adapté au front-end */
.admin-search-panel {
    position: relative;
    background: linear-gradient(135deg, rgba(248, 250, 255, 0.95) 0%, #ffffff 100%);
    border: 1px solid #dbe3f0;
    border-radius: 1.75rem;
    padding: 1.35rem 1.5rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 32px 70px -45px rgba(15, 23, 42, 0.45);
}

.admin-search-panel__form {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
}

.admin-search-panel__primary {
    display: flex;
    align-items: flex-end;
    gap: 1rem;
    flex-wrap: nowrap;
}

.admin-search-panel__search {
    flex: 1 1 auto;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.admin-search-panel__label {
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-weight: 600;
    color: #475569;
    margin: 0;
}

.admin-search-panel__search-box {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.65rem 0.95rem;
    border-radius: 1rem;
    border: 1px solid #dbe3f0;
    background: rgba(255, 255, 255, 0.95);
    transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
}

.admin-search-panel__search-box:focus-within {
    border-color: #2563eb;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.14);
}

.admin-search-panel__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #2563eb;
    font-size: 1rem;
}

.admin-search-panel__input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 0.98rem;
    color: #0f172a;
    min-width: 0;
}

.admin-search-panel__input::placeholder {
    color: #94a3b8;
}

.admin-search-panel__actions {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: nowrap;
    flex-shrink: 0;
}

.admin-search-panel__actions .btn {
    border-radius: 999px;
    min-height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0.6rem 1.3rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    white-space: nowrap;
    flex-shrink: 0;
}

.admin-search-panel__actions .btn:active {
    transform: translateY(1px);
}

.admin-search-panel__submit i,
.admin-search-panel__filters-toggle i {
    font-size: 1rem;
}

.admin-search-panel__submit-label,
.admin-search-panel__filters-label {
    display: inline;
}

/* Responsive pour le panneau de recherche */
@media (max-width: 1199.98px) {
    .admin-search-panel__primary {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem !important;
    }
    
    .admin-search-panel__search {
        width: 100% !important;
        flex: none !important;
    }
    
    .admin-search-panel__actions {
        width: 100% !important;
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.5rem !important;
    }
    
    .admin-search-panel__actions .btn {
        width: 100% !important;
        white-space: nowrap !important;
    }
}

@media (max-width: 767.98px) {
    .admin-search-panel {
        padding: 1.05rem 1.1rem;
    }
    
    .admin-search-panel__primary {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }
    
    .admin-search-panel__search {
        width: 100%;
    }
    
    .admin-search-panel__search-box {
        padding: 0.5rem 0.8rem;
    }
    
    .admin-search-panel__actions {
        width: 100%;
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.5rem;
    }
    
    .admin-search-panel__actions .btn {
        width: 100% !important;
        white-space: nowrap;
        height: 42px;
        font-size: 0.85rem;
        padding: 0.45rem 0.75rem;
    }
    
    .admin-search-panel__submit-label,
    .admin-search-panel__filters-label {
        display: none;
    }
}

@media (max-width: 575.98px) {
    .admin-search-panel {
        padding: 0.95rem 1rem;
    }
    
    .admin-search-panel__search-box {
        padding: 0.48rem 0.75rem;
    }
    
    .admin-search-panel__actions {
        gap: 0.45rem;
    }
    
    .admin-search-panel__actions .btn {
        min-width: 0;
        height: 42px;
        font-size: 0.8rem;
    }
}

/* Styles pour la grille de formulaire admin */
.admin-form-grid {
    display: grid;
    gap: 1rem;
}

.admin-form-grid--two {
    grid-template-columns: repeat(2, 1fr);
}

@media (max-width: 767.98px) {
    .admin-form-grid--two {
        grid-template-columns: 1fr;
    }
}

/* Style pour le bouton "Effacer les filtres" */
.clear-filters-btn {
    border: 1px solid #dc3545 !important;
    padding: 0.375rem 0.75rem !important;
    font-size: 0.875rem !important;
    line-height: 1.5 !important;
    white-space: nowrap;
    min-height: auto !important;
    height: auto !important;
    border-radius: 0.375rem !important;
}

.clear-filters-btn:hover {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: #ffffff !important;
}

.clear-filters-btn:focus {
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

/* Styles pour le conteneur des filtres actifs */
.alert.alert-info {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
}

.alert.alert-info strong {
    font-size: 0.875rem;
}

.alert.alert-info .badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

@media (max-width: 991.98px) {
    .alert.alert-info {
        padding: 0.6rem 0.75rem;
        font-size: 0.8rem;
    }
    
    .alert.alert-info strong {
        font-size: 0.8rem;
    }
    
    .alert.alert-info .badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
    
    .clear-filters-btn {
        font-size: 0.75rem !important;
        padding: 0.25rem 0.5rem !important;
        min-height: 32px !important;
        height: 32px !important;
    }
    
    .clear-filters-btn i {
        font-size: 0.7rem;
    }
}

@media (max-width: 767.98px) {
    .alert.alert-info {
        padding: 0.5rem 0.65rem;
        font-size: 0.75rem;
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.5rem !important;
    }
    
    .alert.alert-info > div {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        align-items: center;
    }
    
    .alert.alert-info strong {
        font-size: 0.75rem;
        margin-right: 0.25rem;
    }
    
    .alert.alert-info .badge {
        font-size: 0.65rem;
        padding: 0.15rem 0.35rem;
        margin: 0 !important;
    }
    
    .clear-filters-btn {
        font-size: 0.7rem !important;
        padding: 0.2rem 0.45rem !important;
        min-height: 28px !important;
        height: 28px !important;
        width: 100%;
        justify-content: center;
    }
    
    .clear-filters-btn i {
        font-size: 0.65rem;
        margin-right: 0.25rem !important;
    }
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

/* Pagination compacte */
.pagination {
    --bs-pagination-padding-x: 0.5rem;
    --bs-pagination-padding-y: 0.25rem;
    --bs-pagination-font-size: 0.875rem;
    --bs-pagination-border-radius: 0.375rem;
    gap: 0.25rem;
}

.pagination .page-link {
    padding: 0.375rem 0.65rem;
    font-size: 0.875rem;
    line-height: 1.5;
    min-width: 2.25rem;
    height: 2.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #003366;
    border-color: #e2e8f0;
    background-color: #ffffff;
    transition: all 0.2s ease;
}

.pagination .page-link:hover {
    color: #ffffff;
    background-color: #003366;
    border-color: #003366;
}

.pagination .page-link:focus {
    color: #003366;
    background-color: #ffffff;
    border-color: #003366;
    box-shadow: 0 0 0 0.2rem rgba(0, 51, 102, 0.25);
}

.pagination .page-item.active .page-link {
    padding: 0.375rem 0.65rem;
    font-size: 0.875rem;
    min-width: 2.25rem;
    height: 2.25rem;
    color: #ffffff;
    background-color: #003366;
    border-color: #003366;
}

.pagination .page-item.disabled .page-link {
    padding: 0.375rem 0.65rem;
    font-size: 0.875rem;
    min-width: 2.25rem;
    height: 2.25rem;
    color: #94a3b8;
    background-color: #f8fafc;
    border-color: #e2e8f0;
    cursor: not-allowed;
}

.pagination .page-link i {
    font-size: 0.75rem;
}

@media (max-width: 575.98px) {
    .pagination {
        --bs-pagination-padding-x: 0.375rem;
        --bs-pagination-padding-y: 0.2rem;
        --bs-pagination-font-size: 0.8rem;
        gap: 0.15rem;
    }
    
    .pagination .page-link {
        padding: 0.3rem 0.5rem;
        font-size: 0.8rem;
        min-width: 2rem;
        height: 2rem;
    }
    
    .pagination .page-item.disabled .page-link,
    .pagination .page-item.active .page-link {
        padding: 0.3rem 0.5rem;
        font-size: 0.8rem;
        min-width: 2rem;
        height: 2rem;
    }
    
    .pagination .page-link i {
        font-size: 0.7rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
let filterToggle;
let filterPanel;

// Fonction pour créer un élément de cours (utilisée pour les recommandations dans le panier)
function createCourseElement(course) {
    const div = document.createElement('div');
    div.className = 'col-lg-4 col-md-6 course-item';
    
    const hasActiveSale = Boolean(course.is_sale_active) && course.active_sale_price !== null;
    const hasCountdown = hasActiveSale && course.sale_end_at;
    const priceHtml = course.is_free ? 
        '<span class="text-success fw-bold">Gratuit</span>' :
        hasActiveSale ?
            `<div class="course-price-container">
                <div class="course-price-row">
                    <span class="text-primary fw-bold">$${parseFloat(course.active_sale_price).toFixed(2)}</span>
                </div>
                <div class="course-price-row">
                    <small class="text-muted text-decoration-line-through">$${parseFloat(course.price).toFixed(2)}</small>
                </div>
                ${hasCountdown ? `
                <div class="course-price-row">
                    <div class="promotion-countdown" data-sale-end="${course.sale_end_at}">
                        <i class="fas fa-fire me-1 text-danger"></i>
                        <span class="countdown-text">
                            <span class="countdown-years">0</span><span>a</span> 
                            <span class="countdown-months">0</span><span>m</span> 
                            <span class="countdown-days">0</span>j 
                            <span class="countdown-hours">0</span>h 
                            <span class="countdown-minutes">0</span>min
                        </span>
                    </div>
                </div>` : ''}
            </div>` :
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
                            <i class="fas fa-user me-1"></i>${course.provider.name.length > 20 ? course.provider.name.substring(0, 20) + '...' : course.provider.name}
                        </small>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <span>${(course.stats?.average_rating || 0).toFixed(1)}</span>
                            <span class="text-muted">(${course.stats?.total_reviews || 0})</span>
                        </div>
                    </div>
                    ${course.show_customers_count ? `
                    <div class="customers-count mb-2">
                        <small class="text-muted">
                            <i class="fas fa-shopping-cart me-1"></i>
                            ${parseInt(course.stats?.purchases_count || 0).toLocaleString('fr-FR')} 
                            ${parseInt(course.stats?.purchases_count || 0) > 1 ? 'achats' : 'achat'}
                        </small>
                    </div>
                    ` : ''}
                    
                    <div class="price-duration">
                        <div class="price">
                            ${priceHtml}
                        </div>
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

// Gestion du formulaire de recherche (système global comme dans l'admin)
document.addEventListener('DOMContentLoaded', function() {
    const coursesFilterForm = document.getElementById('coursesFilterForm');
    const coursesFiltersOffcanvas = document.getElementById('coursesFilters');

    if (coursesFilterForm) {
        coursesFilterForm.addEventListener('submit', () => {
            if (coursesFiltersOffcanvas) {
                const instance = bootstrap.Offcanvas.getInstance(coursesFiltersOffcanvas);
                if (instance) {
                    instance.hide();
                }
            }
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