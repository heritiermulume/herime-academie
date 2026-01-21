@extends('layouts.app')

@section('title', 'Herime Académie - Plateforme d\'apprentissage en ligne et espace de ressources professionnelles')
@section('description', 'Herime Académie est votre plateforme complète : formations en ligne de qualité et ressources professionnelles incontournables. Développez vos compétences et accédez à des outils experts pour votre réussite professionnelle.')

@section('content')

<!-- Notification de déconnexion due à un token invalide -->
@if(session('session_expired'))
<div class="container-fluid">
    <div class="alert alert-warning alert-dismissible fade show mt-3 mb-4" role="alert" style="border-left: 4px solid #f59e0b; background-color: #fef3c7; color: #92400e; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-2" style="font-weight: 600;">Session expirée</h5>
                <p class="mb-3">
                    {{ session('warning', 'Votre session a expiré. Veuillez vous reconnecter pour continuer.') }}
                </p>
                <div class="d-flex gap-2 flex-wrap">
                    @php
                        $callback = route('sso.callback', ['redirect' => url()->full()]);
                        $ssoLoginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callback);
                    @endphp
                    <a href="{{ $ssoLoginUrl }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-sign-in-alt me-2"></i>Se reconnecter
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="alert" aria-label="Fermer">
                        <i class="fas fa-times me-2"></i>Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<style>
/* Prévenir le débordement horizontal sur mobile */
html {
    overflow-x: hidden;
    width: 100%;
    max-width: 100vw;
}

body {
    overflow-x: hidden;
    width: 100%;
    max-width: 100vw;
}

/* Modern Categories Design - Horizontal Scroll */
.modern-categories-container {
    position: relative;
    margin: 0 -15px;
    padding: 0 15px;
    overflow: hidden;
}

.modern-categories-wrapper {
    display: flex;
    overflow-x: auto;
    gap: 1rem;
    padding: 1rem 0;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
    scroll-snap-type: x mandatory;
}

.modern-categories-wrapper::-webkit-scrollbar {
    display: none;
}

.modern-category-item {
    flex: 0 0 auto;
    width: 280px;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    scroll-snap-align: start;
}

.modern-category-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 51, 102, 0.1);
    border-color: #003366;
    text-decoration: none;
}

.modern-category-icon {
    width: 50px;
    height: 50px;
    min-width: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.modern-category-content {
    flex: 1;
    min-width: 0;
}

.modern-category-name {
    font-size: 1rem;
    font-weight: 600;
    color: #003366;
    margin: 0 0 0.25rem 0;
}

.modern-category-desc {
    font-size: 0.875rem;
    color: #6c757d;
    margin: 0;
    line-height: 1.4;
}

.modern-category-arrow {
    color: #6c757d;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.modern-category-item:hover .modern-category-arrow {
    color: #003366;
    transform: translateX(4px);
}

/* Mobile optimizations */
@media (max-width: 767.98px) {
    .container {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }
    
    section {
        overflow-x: hidden;
    }
    
    .modern-categories-container {
        margin: 0 -0.75rem;
        padding: 0 0.75rem;
    }
    
    .modern-categories-wrapper {
        gap: 0.75rem;
        padding: 0.75rem 0;
    }
    
    .modern-category-item {
        width: 240px;
        padding: 0.875rem;
        gap: 0.875rem;
    }
    
    .modern-category-icon {
        width: 45px;
        height: 45px;
        min-width: 45px;
        font-size: 1.25rem;
    }
    
    .modern-category-name {
        font-size: 0.95rem;
    }
    
    .modern-category-desc {
        font-size: 0.8rem;
    }
}

@media (max-width: 575.98px) {
    .container {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    
    .modern-categories-container {
        margin: 0 -0.75rem;
        padding: 0 0.75rem;
    }
    
    .modern-categories-wrapper {
        gap: 0.5rem;
        padding: 0.5rem 0;
    }
    
    .modern-category-item {
        width: 200px;
        padding: 0.75rem;
        gap: 0.75rem;
    }
    
    .modern-category-icon {
        width: 40px;
        height: 40px;
        min-width: 40px;
        font-size: 1.1rem;
    }
    
    .modern-category-name {
        font-size: 0.9rem;
    }
    
    .modern-category-desc {
        font-size: 0.75rem;
    }
}

/* Modern Scroll Buttons */
.modern-scroll-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    background: rgba(255, 255, 255, 0.95);
    border: none;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.3s ease;
    color: #003366;
    font-size: 1rem;
}

.modern-scroll-btn:hover {
    background: #003366;
    color: white;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 51, 102, 0.3);
}

.modern-scroll-btn:active {
    transform: translateY(-50%) scale(0.95);
}

.modern-scroll-left {
    left: 10px;
}

.modern-scroll-right {
    right: 10px;
}

@media (max-width: 767.98px) {
    .modern-scroll-btn {
        display: none !important;
    }
}

/* Mobile Progress Indicator */
.modern-mobile-progress {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
    padding: 0 1rem;
}

.modern-progress-dots {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.modern-progress-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgba(0, 51, 102, 0.2);
    transition: all 0.3s ease;
    cursor: pointer;
}

.modern-progress-dot.active {
    background: #003366;
    width: 20px;
    border-radius: 3px;
}

.modern-progress-dot:hover {
    background: #004080;
    transform: scale(1.1);
}

@media (min-width: 768px) {
    .modern-mobile-progress {
        display: none;
    }
}

/* Course sections horizontal scroll */
.course-scroll-container {
    position: relative;
    margin: 0 -15px;
    padding: 0 15px 0.5rem;
    overflow: hidden;
}

.course-scroll-wrapper {
    display: flex;
    gap: 1.5rem;
    overflow-x: auto;
    padding-bottom: 1rem;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    scroll-snap-type: x mandatory;
}

.course-scroll-wrapper::-webkit-scrollbar {
    display: none;
}

.course-scroll-item {
    flex: 0 0 auto;
    width: clamp(260px, 28vw, 320px);
    scroll-snap-align: start;
}

.course-scroll-item .course-card {
    height: 100%;
}

.course-scroll-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 5;
    background: rgba(255, 255, 255, 0.95);
    border: none;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    color: #003366;
    cursor: pointer;
    transition: all 0.3s ease;
}

.course-scroll-btn:hover {
    background: #003366;
    color: #fff;
    transform: translateY(-50%) scale(1.08);
}

.course-scroll-btn:active {
    transform: translateY(-50%) scale(0.95);
}

.course-scroll-btn-left {
    left: 10px;
}

.course-scroll-btn-right {
    right: 10px;
}

@media (max-width: 767.98px) {
    .course-scroll-container {
        margin: 0 -0.75rem;
        padding: 0 0.75rem 0.5rem;
    }

    .course-scroll-wrapper {
        gap: 1rem;
    }

    .course-scroll-item {
        width: 260px;
    }

    .course-scroll-btn {
        display: none !important;
    }
}

@media (max-width: 575.98px) {
    .course-scroll-item {
        width: 240px;
    }
}

</style>

<!-- Hero Section - Dynamic Banner Carousel -->
<section class="hero-section-modern text-white">
    <div class="hero-carousel-container" id="heroCarousel">
        @if($banners && $banners->count() > 0)
            @foreach($banners as $index => $banner)
            <div class="hero-slide {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}">
                <div class="hero-container">
                    <div class="hero-image-bg">
                        <picture>
                            @if($banner->mobile_image_url)
                                <source media="(max-width: 768px)" srcset="{{ $banner->mobile_image_url }}">
                            @endif
                            <img src="{{ $banner->image_url ?: 'https://via.placeholder.com/1200x600?text=Banner' }}" 
                                 alt="{{ $banner->title }}" 
                                 class="hero-bg-image"
                                 loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                        </picture>
                    </div>
                    
                    <div class="hero-content-overlay">
                        <div class="container">
                            <div class="row align-items-center min-vh-80">
                                <div class="col-12 col-lg-7 col-xl-6">
                                    <div class="hero-text-content">
                                        <h1 class="display-4 fw-bold mb-4">
                                            {!! $banner->title !!}
                                        </h1>
                                        @if($banner->subtitle)
                                        <p class="lead mb-4">
                                            {{ $banner->subtitle }}
                                        </p>
                                        @endif
                                        <div class="d-flex flex-column flex-sm-row gap-3">
                                            @if($banner->button1_text && $banner->button1_url)
                                            @php
                                                $btn1_url = $banner->button1_url;
                                                // Si l'URL ne commence pas par http:// ou https://, c'est un lien interne
                                                if (!str_starts_with($btn1_url, 'http://') && !str_starts_with($btn1_url, 'https://')) {
                                                    // Si ça commence par /, c'est un chemin absolu, sinon on ajoute /
                                                    $btn1_url = str_starts_with($btn1_url, '/') ? $btn1_url : '/' . $btn1_url;
                                                    $btn1_url = url($btn1_url);
                                                }
                                            @endphp
                                            <a href="{{ $btn1_url }}" 
                                               target="{{ $banner->button1_target ?? '_self' }}"
                                               {{ ($banner->button1_target ?? '_self') == '_blank' ? 'rel="noopener noreferrer"' : '' }}
                                               class="btn btn-{{ $banner->button1_style ?? 'warning' }} btn-lg px-4">
                                                <i class="fas fa-play me-2"></i>{{ $banner->button1_text }}
                                            </a>
                                            @endif
                                            @if($banner->button2_text && $banner->button2_url)
                                            @php
                                                $btn2_url = $banner->button2_url;
                                                // Si l'URL ne commence pas par http:// ou https://, c'est un lien interne
                                                if (!str_starts_with($btn2_url, 'http://') && !str_starts_with($btn2_url, 'https://')) {
                                                    // Si ça commence par /, c'est un chemin absolu, sinon on ajoute /
                                                    $btn2_url = str_starts_with($btn2_url, '/') ? $btn2_url : '/' . $btn2_url;
                                                    $btn2_url = url($btn2_url);
                                                }
                                            @endphp
                                            <a href="{{ $btn2_url }}" 
                                               target="{{ $banner->button2_target ?? '_self' }}"
                                               {{ ($banner->button2_target ?? '_self') == '_blank' ? 'rel="noopener noreferrer"' : '' }}
                                               class="btn btn-{{ $banner->button2_style ?? 'outline-light' }} btn-lg px-4">
                                                <i class="fas fa-search me-2"></i>{{ $banner->button2_text }}
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            
            @if($banners->count() > 1)
            <!-- Navigation Arrows (Desktop only) -->
            <button class="hero-nav hero-nav-prev" id="heroPrev" aria-label="Précédent">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="hero-nav hero-nav-next" id="heroNext" aria-label="Suivant">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <!-- Dots Navigation (Desktop only) -->
            <div class="hero-dots">
                @foreach($banners as $index => $banner)
                <button class="hero-dot {{ $index === 0 ? 'active' : '' }}" 
                        data-slide="{{ $index }}"
                        aria-label="Aller à la bannière {{ $index + 1 }}"></button>
                @endforeach
            </div>
            
            <!-- Modern Mobile Navigation Indicator -->
            <div class="hero-mobile-indicator" aria-hidden="true">
                @foreach($banners as $index => $banner)
                <span class="slide-indicator {{ $index === 0 ? 'active' : '' }}" 
                      data-slide="{{ $index }}"></span>
                @endforeach
            </div>
            @endif
        @else
            <!-- Fallback if no banners -->
            <div class="hero-slide active">
                <div class="hero-container">
                    <div class="hero-image-bg">
                        <img src="{{ asset('images/hero/hero-student.jpg') }}" 
                             alt="Apprentissage en ligne" class="hero-bg-image">
                    </div>
                    
                    <div class="hero-content-overlay">
                        <div class="container">
                            <div class="row align-items-center min-vh-80">
                                <div class="col-12 col-lg-7 col-xl-6">
                                    <div class="hero-text-content">
                                        <h1 class="display-4 fw-bold mb-4">
                                            Votre plateforme complète : 
                                            <span class="text-warning">Apprentissage en ligne · Ressources Professionnelles</span>
                                        </h1>

                                        <div class="d-flex flex-column flex-sm-row gap-3">
                                            <a href="{{ route('contents.index') }}" class="btn btn-warning btn-lg px-4">
                                                <i class="fas fa-play me-2"></i>Commencer à apprendre
                                            </a>
                                            <a href="#categories" class="btn btn-outline-light btn-lg px-4">
                                                <i class="fas fa-search me-2"></i>Explorer les catégories
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

<!-- Categories Section - Modern Design -->
<section id="categories" class="categories-section py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 col-lg-8 mx-auto text-center">
                <h2 class="h3 fw-bold mb-2">Explorez nos catégories</h2>
                <p class="text-muted" style="font-size: 0.95rem;">
                    Trouvez le contenu parfait dans nos catégories spécialisées
                </p>
            </div>
        </div>
        
        <!-- Modern Category Pills -->
        <div class="modern-categories-container">
            <div class="modern-categories-wrapper" id="categoriesScroll">
                @foreach($categories as $category)
                <a href="{{ route('contents.category', $category->slug) }}" class="modern-category-item">
                    @if($category->icon)
                    <div class="modern-category-icon" style="background: linear-gradient(135deg, {{ $category->color ?? '#003366' }}, {{ $category->color ?? '#004080' }});">
                        <i class="{{ $category->icon }}"></i>
                    </div>
                    @endif
                    <div class="modern-category-content">
                        <h6 class="modern-category-name">{{ Str::limit($category->name, 20) }}</h6>
                        @if($category->description)
                        <p class="modern-category-desc">{{ Str::limit($category->description, 30) }}</p>
                        @endif
                    </div>
                    <i class="fas fa-chevron-right modern-category-arrow"></i>
                </a>
                @endforeach
            </div>
            
            <!-- Navigation Buttons -->
            <button class="modern-scroll-btn modern-scroll-left" onclick="scrollModernCategories('left')" aria-label="Faire défiler vers la gauche">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="modern-scroll-btn modern-scroll-right" onclick="scrollModernCategories('right')" aria-label="Faire défiler vers la droite">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <!-- Mobile Progress Indicator -->
        <div class="modern-mobile-progress d-md-none">
            <div class="modern-progress-dots" id="modernProgressDots"></div>
        </div>
        
        <div class="text-center mt-5">
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
            <a href="{{ route('categories.index') }}" class="btn btn-primary btn-lg">
                    Voir toutes les catégories <i class="fas fa-th-large ms-2"></i>
                </a>    
            <a href="{{ route('contents.index') }}" class="btn btn-outline-primary btn-lg">
                    Voir tous les contenus <i class="fas fa-arrow-right ms-2"></i>
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
            <div class="col-12 col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-3">Contenus en vedette</h2>
                <p class="lead text-muted">
                    Découvrez nos contenus les plus populaires et les mieux notés
                </p>
            </div>
        </div>
        <div class="course-scroll-container">
            <button class="course-scroll-btn course-scroll-btn-left" type="button" data-scroll-target="featuredCoursesScroll" data-scroll-direction="left" aria-label="Faire défiler les cours en vedette vers la gauche">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="course-scroll-wrapper" id="featuredCoursesScroll" data-scroll-amount="320">
                @foreach($featuredCourses as $course)
                <div class="course-scroll-item">
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
                                
                                <div class="card-actions" onclick="event.stopPropagation(); event.preventDefault();">
                                    <x-contenu-button :course="$course" size="small" :show-cart="false" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <button class="course-scroll-btn course-scroll-btn-right" type="button" data-scroll-target="featuredCoursesScroll" data-scroll-direction="right" aria-label="Faire défiler les cours en vedette vers la droite">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('contents.index', ['featured' => 1]) }}" class="btn btn-outline-primary btn-lg">
                Voir tous les contenus en vedette <i class="fas fa-arrow-right ms-2"></i>
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
            <div class="col-12 col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-3">Contenus populaires</h2>
                <p class="lead text-muted">
                    Les contenus les plus suivis par notre communauté
                </p>
            </div>
        </div>
        <div class="course-scroll-container">
            <button class="course-scroll-btn course-scroll-btn-left" type="button" data-scroll-target="popularCoursesScroll" data-scroll-direction="left" aria-label="Faire défiler les cours populaires vers la gauche">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="course-scroll-wrapper" id="popularCoursesScroll" data-scroll-amount="300">
                @foreach($popularCourses as $course)
                <div class="course-scroll-item">
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
                                
                                <div class="card-actions" onclick="event.stopPropagation(); event.preventDefault();">
                                    <x-contenu-button :course="$course" size="small" :show-cart="false" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <button class="course-scroll-btn course-scroll-btn-right" type="button" data-scroll-target="popularCoursesScroll" data-scroll-direction="right" aria-label="Faire défiler les cours populaires vers la droite">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('contents.index', ['popular' => 1]) }}" class="btn btn-outline-primary btn-lg">
                Voir tous les contenus populaires <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>
@endif

<!-- Testimonials Section - Modern Design -->
@if($testimonials->count() > 0)
<section class="testimonials py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-3 text-dark">Ce que disent nos étudiants</h2>
                <p class="lead text-muted">
                    Découvrez les témoignages de notre communauté
                </p>
            </div>
        </div>
        
        <!-- Modern Testimonials Horizontal Scroll -->
        <div class="modern-testimonials-container">
            <div class="modern-testimonials-wrapper" id="testimonialsScroll">
                @foreach($testimonials as $testimonial)
                <div class="modern-testimonial-item">
                    <div class="modern-testimonial-card">
                        <!-- Rating -->
                        <div class="modern-testimonial-rating">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $testimonial->rating ? 'text-warning' : 'text-muted' }}"></i>
                            @endfor
                        </div>
                        
                        <!-- Testimonial Text -->
                        <div class="modern-testimonial-text">
                            <p>"{{ Str::limit($testimonial->testimonial, 150) }}"</p>
                        </div>
                        
                        <!-- Author Info -->
                        <div class="modern-testimonial-author">
                            <div class="modern-testimonial-avatar">
                                @php
                                    $avatarUrl = '';
                                    if (!empty($testimonial->photo) && trim($testimonial->photo) !== '') {
                                        if (str_starts_with($testimonial->photo, 'http')) {
                                            $avatarUrl = $testimonial->photo;
                                        } else {
                                            $avatarUrl = \App\Helpers\FileHelper::url($testimonial->photo);
                                        }
                                        // Si FileHelper retourne une chaîne vide, utiliser l'avatar par défaut
                                        if (empty($avatarUrl) || trim($avatarUrl) === '') {
                                            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($testimonial->name) . '&background=003366&color=fff&size=128&bold=true';
                                        }
                                    } else {
                                        $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($testimonial->name) . '&background=003366&color=fff&size=128&bold=true';
                                    }
                                @endphp
                                <img src="{{ $avatarUrl }}" 
                                     alt="{{ $testimonial->name }}" 
                                     class="rounded-circle"
                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($testimonial->name) }}&background=003366&color=fff&size=128&bold=true';">
                            </div>
                            <div class="modern-testimonial-info">
                                <h6 class="modern-testimonial-name">{{ $testimonial->name }}</h6>
                                <p class="modern-testimonial-title">{{ $testimonial->title }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Navigation Buttons -->
            <button class="modern-testimonial-scroll-btn modern-testimonial-scroll-left" onclick="scrollModernTestimonials('left')" aria-label="Faire défiler vers la gauche">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="modern-testimonial-scroll-btn modern-testimonial-scroll-right" onclick="scrollModernTestimonials('right')" aria-label="Faire défiler vers la droite">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <!-- Mobile Progress Indicator -->
        <div class="modern-testimonial-mobile-progress d-md-none">
            <div class="modern-testimonial-progress-dots" id="modernTestimonialProgressDots"></div>
        </div>
    </div>
</section>
@endif

<!-- Trending Courses Section -->
@if($trendingCourses->count() > 0)
<section class="trending-courses py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-3">Contenus tendance</h2>
                <p class="lead text-muted">
                    Les contenus les plus suivis cette semaine
                </p>
            </div>
        </div>
        <div class="course-scroll-container">
            <button class="course-scroll-btn course-scroll-btn-left" type="button" data-scroll-target="trendingCoursesScroll" data-scroll-direction="left" aria-label="Faire défiler les cours tendance vers la gauche">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="course-scroll-wrapper" id="trendingCoursesScroll" data-scroll-amount="300">
                @foreach($trendingCourses as $course)
                <div class="course-scroll-item">
                    <div class="course-card" data-course-url="{{ route('contents.show', $course->slug) }}" style="cursor: pointer;">
                        <div class="card" style="position: relative;">
                            <div class="position-relative">
                                <img src="{{ $course->thumbnail_url ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop' }}" 
                                     class="card-img-top" alt="{{ $course->title }}">
                                <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
                                    <span class="badge bg-danger">Tendance</span>
                                    @if($course->is_free)
                                    <span class="badge bg-success">Gratuit</span>
                                    @endif
                                    @if($course->sale_discount_percentage)
                                    <span class="badge bg-warning">
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
                                            $icon = 'fa-download';
                                        } else {
                                            // Téléchargeable payant : nombre d'achats
                                            $count = (int) ($course->stats['purchases_count'] ?? $course->purchases_count ?? 0);
                                            $label = $count > 1 ? 'achats' : 'achat';
                                            $icon = 'fa-shopping-cart';
                                        }
                                    } else {
                                        // Cours non téléchargeable
                                        $icon = 'fa-user-plus';
                                        if ($course->is_free) {
                                            // Non téléchargeable gratuit : inscriptions
                                            $count = (int) ($course->stats['total_customers'] ?? $course->total_customers ?? 0);
                                            $label = $count > 1 ? 'participants' : 'participant';                                            
                                        } else {
                                            // Non téléchargeable payant : nombre d'achats
                                            $count = (int) ($course->stats['purchases_count'] ?? $course->purchases_count ?? 0);
                                            $label = $count > 1 ? 'participants' : 'participant';                                    
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
                </div>
                @endforeach
            </div>
            <button class="course-scroll-btn course-scroll-btn-right" type="button" data-scroll-target="trendingCoursesScroll" data-scroll-direction="right" aria-label="Faire défiler les cours tendance vers la droite">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('contents.index', ['trending' => 1]) }}" class="btn btn-outline-primary btn-lg">
                Voir tous les contenus tendance <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>
@endif

<!-- CTA Section -->
<section class="cta-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-lg-8 text-center text-lg-start mb-4 mb-lg-0">
                <h2 class="display-5 fw-bold mb-3">Prêt à transformer votre carrière et développer vos compétences ?</h2>
                <p class="lead mb-0">
                    Rejoignez des milliers de professionnels qui transforment leur carrière et développent leurs compétences avec Herime Académie. Plateforme d'apprentissage en ligne et espace de ressources professionnelles.
                </p>
            </div>
            <div class="col-12 col-lg-4 text-center text-lg-end">
                @auth
                    <a href="{{ route('contents.index') }}" class="btn btn-warning btn-lg px-4">
                        <i class="fas fa-play me-2"></i>Explorer les contenus
                    </a>
                @else
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-end">
                    @php
                        $final = url()->full();
                        $callback = route('sso.callback', ['redirect' => $final]);
                        $ssoRegisterUrl = 'https://compte.herime.com/login?force_token=1&action=register&redirect=' . urlencode($callback);
                    @endphp
                    <a href="{{ $ssoRegisterUrl }}" class="btn btn-warning btn-lg px-4 w-100 w-sm-auto">
                            <i class="fas fa-user-plus me-2"></i>S'inscrire gratuitement
                        </a>
                    @php
                        $finalLogin = url()->full();
                        $callbackLogin = route('sso.callback', ['redirect' => $finalLogin]);
                        $ssoLoginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callbackLogin);
                    @endphp
                    <a href="{{ $ssoLoginUrl }}" class="btn btn-outline-light btn-lg px-4 w-100 w-sm-auto">
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
    window.location.href = '{{ route('contents.index') }}';
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

// Hero Banner Carousel
document.addEventListener('DOMContentLoaded', function() {
    const heroSlides = document.querySelectorAll('.hero-slide');
    const heroDots = document.querySelectorAll('.hero-dot');
    const mobileIndicators = document.querySelectorAll('.slide-indicator');
    const prevBtn = document.getElementById('heroPrev');
    const nextBtn = document.getElementById('heroNext');
    
    if (heroSlides.length <= 1) return; // No carousel needed for single slide
    
    let currentSlide = 0;
    let autoSlideInterval;
    const autoSlideDelay = 4500; // 4.5 seconds
    
    function showSlide(index) {
        // Remove active class from all slides, dots and mobile indicators
        heroSlides.forEach(slide => slide.classList.remove('active'));
        heroDots.forEach(dot => dot.classList.remove('active'));
        mobileIndicators.forEach(indicator => indicator.classList.remove('active'));
        
        // Add active class to current slide, dot and mobile indicator
        heroSlides[index].classList.add('active');
        heroDots[index].classList.add('active');
        if (mobileIndicators[index]) {
            mobileIndicators[index].classList.add('active');
        }
        
        currentSlide = index;
    }
    
    function nextSlide() {
        const next = (currentSlide + 1) % heroSlides.length;
        showSlide(next);
    }
    
    function prevSlide() {
        const prev = currentSlide === 0 ? heroSlides.length - 1 : currentSlide - 1;
        showSlide(prev);
    }
    
    function startAutoSlide() {
        stopAutoSlide();
        autoSlideInterval = setInterval(nextSlide, autoSlideDelay);
    }
    
    function stopAutoSlide() {
        if (autoSlideInterval) {
            clearInterval(autoSlideInterval);
        }
    }
    
    // Navigation buttons
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            stopAutoSlide();
            prevSlide();
            startAutoSlide();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            stopAutoSlide();
            nextSlide();
            startAutoSlide();
        });
    }
    
    // Dot navigation
    heroDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            stopAutoSlide();
            showSlide(index);
            startAutoSlide();
        });
    });
    
    // Pause on hover
    const heroCarousel = document.getElementById('heroCarousel');
    if (heroCarousel) {
        heroCarousel.addEventListener('mouseenter', stopAutoSlide);
        heroCarousel.addEventListener('mouseleave', startAutoSlide);
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            stopAutoSlide();
            prevSlide();
            startAutoSlide();
        } else if (e.key === 'ArrowRight') {
            stopAutoSlide();
            nextSlide();
            startAutoSlide();
        }
    });
    
    // Touch/swipe support
    let touchStartX = 0;
    let touchEndX = 0;
    
    if (heroCarousel) {
        heroCarousel.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        heroCarousel.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
    }
    
    function handleSwipe() {
        if (touchEndX < touchStartX - 50) { // Swipe left
            stopAutoSlide();
            nextSlide();
            startAutoSlide();
        }
        if (touchEndX > touchStartX + 50) { // Swipe right
            stopAutoSlide();
            prevSlide();
            startAutoSlide();
        }
    }
    
    // Start auto-sliding
    startAutoSlide();
});

// La fonction showNotification est maintenant définie globalement dans app.blade.php

// Test des boutons au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const startLearningBtn = document.getElementById('start-learning-btn');
    const exploreBtn = document.getElementById('explore-btn');

    if (startLearningBtn) {
        startLearningBtn.addEventListener('click', function(e) {
            console.log('🎯 Clic détecté sur "Commencer à apprendre"');
        });
    }

    if (exploreBtn) {
        exploreBtn.addEventListener('click', function(e) {
            console.log('🎯 Clic détecté sur "Explorer les cours"');
        });
    }
});

// La fonction addToCart est maintenant définie globalement dans app.blade.php

// Les fonctions showNotification et updateCartCount sont maintenant définies globalement dans app.blade.php

// Modern Testimonials Horizontal Scroll
function scrollModernTestimonials(direction) {
    const scrollContainer = document.getElementById('testimonialsScroll');
    const scrollAmount = 300;
    
    if (scrollContainer) {
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
}

// Auto-hide testimonial scroll buttons and mobile progress with auto-scroll
document.addEventListener('DOMContentLoaded', function() {
    const scrollContainer = document.getElementById('testimonialsScroll');
    const leftBtn = document.querySelector('.modern-testimonial-scroll-left');
    const rightBtn = document.querySelector('.modern-testimonial-scroll-right');
    const progressDots = document.getElementById('modernTestimonialProgressDots');
    const testimonialsContainer = document.querySelector('.modern-testimonials-container');
    
    if (!scrollContainer) return;
    
    let autoScrollInterval;
    let currentPage = 0;
    let totalPages = 0;
    let isUserInteracting = false;
    
    function createProgressDots() {
        if (!progressDots) return;
        
        const items = document.querySelectorAll('.modern-testimonial-item');
        const totalItems = items.length;
        const visibleItems = Math.ceil(scrollContainer.clientWidth / 320);
        totalPages = Math.max(1, Math.ceil(totalItems / visibleItems));
        
        progressDots.innerHTML = '';
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('div');
            dot.className = 'modern-testimonial-progress-dot';
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => {
                scrollToPage(i);
                restartAutoScroll();
            });
            progressDots.appendChild(dot);
        }
    }
    
    function scrollToPage(pageIndex) {
        const items = document.querySelectorAll('.modern-testimonial-item');
        const visibleItems = Math.ceil(scrollContainer.clientWidth / 320);
        const targetIndex = pageIndex * visibleItems;
        const targetItem = items[targetIndex];
        
        if (targetItem) {
            // Use scrollBy instead of scrollIntoView to avoid page scrolling
            const scrollAmount = targetItem.offsetLeft - scrollContainer.scrollLeft;
            scrollContainer.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
            currentPage = pageIndex;
        }
    }
    
    function nextPage() {
        if (totalPages <= 1) return;
        
        const nextPageIndex = (currentPage + 1) % totalPages;
        
        // Use scrollBy instead of scrollIntoView to avoid page scrolling
        const items = document.querySelectorAll('.modern-testimonial-item');
        const visibleItems = Math.ceil(scrollContainer.clientWidth / 320);
        const targetIndex = nextPageIndex * visibleItems;
        const targetItem = items[targetIndex];
        
        if (targetItem) {
            const scrollAmount = targetItem.offsetLeft - scrollContainer.scrollLeft;
            scrollContainer.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
            currentPage = nextPageIndex;
        }
    }
    
    function startAutoScroll() {
        stopAutoScroll();
        autoScrollInterval = setInterval(() => {
            if (!isUserInteracting && totalPages > 1) {
                nextPage();
            }
        }, 4500); // Change every 4.5 seconds
    }
    
    function stopAutoScroll() {
        if (autoScrollInterval) {
            clearInterval(autoScrollInterval);
        }
    }
    
    function restartAutoScroll() {
        stopAutoScroll();
        startAutoScroll();
    }
    
    function updateScrollButtons() {
        const scrollLeft = scrollContainer.scrollLeft;
        const maxScroll = scrollContainer.scrollWidth - scrollContainer.clientWidth;
        
        if (leftBtn) {
            if (scrollLeft <= 0) {
                leftBtn.style.opacity = '0.5';
                leftBtn.style.pointerEvents = 'none';
            } else {
                leftBtn.style.opacity = '1';
                leftBtn.style.pointerEvents = 'auto';
            }
        }
        
        if (rightBtn) {
            if (scrollLeft >= maxScroll - 10) {
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
        
        const dots = progressDots.querySelectorAll('.modern-testimonial-progress-dot');
        const activeIndex = Math.round(progress * (dots.length - 1));
        
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === activeIndex);
        });
        
        currentPage = activeIndex;
    }
    
    // Initialize
    createProgressDots();
    updateScrollButtons();
    updateProgressDots();
    startAutoScroll();
    
    // Pause on hover/interaction
    if (testimonialsContainer) {
        testimonialsContainer.addEventListener('mouseenter', () => {
            isUserInteracting = true;
            stopAutoScroll();
        });
        
        testimonialsContainer.addEventListener('mouseleave', () => {
            isUserInteracting = false;
            startAutoScroll();
        });
    }
    
    // User scroll detection
    let scrollTimeout;
    scrollContainer.addEventListener('scroll', () => {
        updateScrollButtons();
        updateProgressDots();
        
        isUserInteracting = true;
        clearTimeout(scrollTimeout);
        
        scrollTimeout = setTimeout(() => {
            isUserInteracting = false;
        }, 3000);
    });
    
    // Window resize
    window.addEventListener('resize', () => {
        createProgressDots();
        updateScrollButtons();
        updateProgressDots();
        restartAutoScroll();
    });
});

// Modern Categories Horizontal Scroll
function scrollModernCategories(direction) {
    const scrollContainer = document.getElementById('categoriesScroll');
    const scrollAmount = 300; // Distance de scroll en pixels
    
    if (scrollContainer) {
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
}

// Auto-hide scroll buttons and mobile progress
document.addEventListener('DOMContentLoaded', function() {
    const scrollContainer = document.getElementById('categoriesScroll');
    const leftBtn = document.querySelector('.modern-scroll-left');
    const rightBtn = document.querySelector('.modern-scroll-right');
    const progressDots = document.getElementById('modernProgressDots');
    
    if (!scrollContainer) return;
    
    // Create mobile progress dots
    function createProgressDots() {
        if (!progressDots) return;
        
        const items = document.querySelectorAll('.modern-category-item');
        const totalItems = items.length;
        const visibleItems = Math.ceil(scrollContainer.clientWidth / 280); // Approximate visible count
        const totalPages = Math.ceil(totalItems / visibleItems);
        
        progressDots.innerHTML = '';
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('div');
            dot.className = 'modern-progress-dot';
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => scrollToPage(i));
            progressDots.appendChild(dot);
        }
    }
    
    // Scroll to specific page
    function scrollToPage(pageIndex) {
        const items = document.querySelectorAll('.modern-category-item');
        const visibleItems = Math.ceil(scrollContainer.clientWidth / 280);
        const targetIndex = pageIndex * visibleItems;
        const targetItem = items[targetIndex];
        
        if (targetItem) {
            targetItem.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'start'
            });
        }
    }
    
    function updateScrollButtons() {
        const scrollLeft = scrollContainer.scrollLeft;
        const maxScroll = scrollContainer.scrollWidth - scrollContainer.clientWidth;
        
        if (leftBtn) {
            if (scrollLeft <= 0) {
                leftBtn.style.opacity = '0.5';
                leftBtn.style.pointerEvents = 'none';
            } else {
                leftBtn.style.opacity = '1';
                leftBtn.style.pointerEvents = 'auto';
            }
        }
        
        if (rightBtn) {
            if (scrollLeft >= maxScroll - 10) {
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
        
        const dots = progressDots.querySelectorAll('.modern-progress-dot');
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

function scrollCourseSection(targetId, direction) {
    const scrollContainer = document.getElementById(targetId);
    if (!scrollContainer) {
        return;
    }

    const amount = parseInt(scrollContainer.dataset.scrollAmount || '320', 10);
    const delta = direction === 'left' ? -amount : amount;

    scrollContainer.scrollBy({
        left: delta,
        behavior: 'smooth',
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const courseScrollButtons = document.querySelectorAll('[data-scroll-target][data-scroll-direction]');

    courseScrollButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-scroll-target');
            const direction = button.getAttribute('data-scroll-direction') || 'right';
            scrollCourseSection(targetId, direction);
        });
    });
});
</script>
@endpush

@push('styles')
<style>
/* CTA Section - No gap with footer */
.cta-section {
    margin-bottom: 0 !important;
    padding-top: 5rem !important;
    padding-bottom: 4rem !important;
}

/* Version web/desktop - Plus d'espace pour la section CTA */
@media (min-width: 992px) {
    .cta-section {
        padding-top: 6rem !important;
        padding-bottom: 5rem !important;
    }
    
    .cta-section .container {
        padding-left: 2rem;
        padding-right: 2rem;
    }
}

@media (min-width: 1200px) {
    .cta-section {
        padding-top: 7rem !important;
        padding-bottom: 6rem !important;
    }
    
    .cta-section .container {
        padding-left: 3rem;
        padding-right: 3rem;
    }
}

@media (max-width: 767.98px) {
    .cta-section {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    
    /* Centrer le logo du footer sur mobile */
    .footer .col-lg-4:first-child {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center !important;
    }
    
    .footer-logo {
        margin-left: auto;
        margin-right: auto;
    }
}

@media (max-width: 575.98px) {
    .cta-section {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    
    /* Centrer le logo du footer sur mobile */
    .footer .col-lg-4:first-child {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center !important;
    }
    
    .footer-logo {
        margin-left: auto;
        margin-right: auto;
    }
}

/* Modern Hero Section with Carousel */
.hero-section-modern {
    position: relative;
    min-height: 100vh;
    overflow: hidden;
    margin-top: -70px;
    padding-top: 80px;
}

.hero-carousel-container {
    position: relative;
    width: 100%;
    height: 100vh;
    min-height: 600px;
}

.hero-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.8s ease-in-out, visibility 0.8s ease-in-out;
}

.hero-slide.active {
    opacity: 1;
    visibility: visible;
    z-index: 1;
}

.hero-container {
    position: relative;
    width: 100%;
    height: 100%;
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
    font-size: 1.25rem;
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

/* Navigation Arrows */
.hero-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1.2rem;
}

.hero-nav:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-50%) scale(1.1);
}

.hero-nav-prev {
    left: 20px;
}

.hero-nav-next {
    right: 20px;
}

/* Dots Navigation */
.hero-dots {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.hero-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    border: 2px solid rgba(255, 255, 255, 0.8);
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 0;
}

.hero-dot:hover {
    background: rgba(255, 255, 255, 0.7);
    transform: scale(1.2);
}

.hero-dot.active {
    background: white;
    width: 40px;
    height: 4px;
    border-radius: 2px;
    border: none;
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

/* Desktop - Hero starts right after navbar */
@media (min-width: 992px) {
    .hero-section-modern {
        margin-top: -70px;
        padding-top: 80px;
    }
    
    .hero-content-overlay .container {
        padding-top: 20px;
    }
}

@media (max-width: 991.98px) {
    .hero-section-modern {
        margin-top: -60px;
        padding-top: 70px;
    }
    
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
    
    .hero-content-overlay .container {
        padding-top: 10px;
    }
    
    .hero-text-content {
        max-width: 100%;
        padding: 1.5rem 0;
    }
}

@media (max-width: 767.98px) {
    /* Hero section pleine largeur - sans !important pour ne pas casser Bootstrap */
    .hero-section-modern {
        position: relative;
        min-height: auto;
        height: auto;
        width: 100%;
        margin-top: -60px;
        padding-top: 70px;
    }
    
    .hero-carousel-container {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 56.25%; /* 16:9 ratio pour le conteneur */
        min-height: 0;
        overflow: hidden;
        margin: 0;
    }
    
    .hero-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
        margin: 0;
        padding: 0;
    }
    
    .hero-slide.active {
        z-index: 1;
    }
    
    .hero-container {
        position: relative;
        width: 100%;
        height: 100%;
        padding-bottom: 0;
        min-height: 0;
        margin: 0;
    }
    
    .hero-image-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
    }
    
    .hero-bg-image {
        object-fit: cover;
        object-position: center center;
        width: 100%;
        height: 100%;
    }
    
    .hero-content-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        background: linear-gradient(
            90deg,
            rgba(0, 51, 102, 0.85) 0%,
            rgba(0, 51, 102, 0.75) 30%,
            rgba(0, 51, 102, 0.6) 50%,
            rgba(0, 51, 102, 0.4) 70%,
            rgba(0, 51, 102, 0.2) 85%,
            transparent 100%
        );
    }
    
    /* Container Bootstrap dans hero - ajuster seulement le padding */
    .hero-content-overlay .container {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
        padding-top: 10px;
    }
    
    .min-vh-80 {
        min-height: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: 2rem 1rem 1rem 1rem;
    }
    
    .hero-text-content {
        padding: 0;
        text-align: left;
        background: transparent;
        border-radius: 0;
        backdrop-filter: none;
        box-shadow: none;
        max-width: 65vw;
        margin: 0;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .hero-text-content h1 {
        font-size: 1.6rem;
        text-align: left;
        margin-bottom: 0.75rem;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.9);
        line-height: 1.25;
        font-weight: 700;
        max-width: 65vw;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .hero-text-content p {
        font-size: 0.875rem;
        text-align: left;
        margin-bottom: 0.875rem;
        text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.9);
        line-height: 1.4;
        max-width: 65vw;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .hero-text-content .d-flex {
        justify-content: flex-start;
        gap: 0.25rem !important;
        flex-wrap: wrap;
    }
    
    .hero-text-content .d-flex.flex-column .btn {
        width: auto !important;
        align-self: flex-start;
    }
    
    .hero-text-content .btn,
    .hero-text-content .btn.btn-lg {
        font-size: 0.875rem !important;
        padding: 0.35rem 0.65rem !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        white-space: nowrap;
        width: auto;
        max-width: none;
        line-height: 1.4 !important;
        min-height: auto !important;
        height: auto !important;
    }
    
    .hero-text-content .btn.px-4 {
        padding-left: 0.65rem !important;
        padding-right: 0.65rem !important;
    }
    
    .hero-text-content .btn i,
    .hero-text-content .btn.btn-lg i {
        font-size: 0.875rem !important;
        margin-right: 0.25rem !important;
    }
    
    /* Modern Mobile Navigation - Hide old navigation */
    .hero-nav {
        display: none !important;
    }
    
    .hero-dots {
        display: none !important;
    }
    
    /* Nouveau système de navigation moderne pour mobile */
    .hero-mobile-indicator {
        position: absolute;
        bottom: 15px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(10px);
        padding: 8px 12px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .hero-mobile-indicator .slide-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.4);
        transition: all 0.3s ease;
    }
    
    .hero-mobile-indicator .slide-indicator.active {
        width: 20px;
        background: white;
        border-radius: 3px;
    }
    
    /* Pas de dégradé en bas sur mobile */
    .hero-section-modern::after,
    .hero-section-modern::before {
        display: none;
    }
}

@media (max-width: 575.98px) {
    .hero-carousel-container {
        padding-bottom: 56.25%; /* Maintenir le format 16:9 */
    }
    
    .min-vh-80 {
        min-height: 100%;
        height: 100%;
        padding: 1.5rem 0.75rem 1rem 0.75rem;
    }
    
    .hero-text-content {
        padding: 0;
        max-width: 65vw;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .hero-text-content h1 {
        font-size: 1.4rem;
        margin-bottom: 0.625rem;
        line-height: 1.2;
        max-width: 65vw;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .hero-text-content p {
        font-size: 0.8rem;
        margin-bottom: 0.75rem;
        line-height: 1.35;
        max-width: 65vw;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .hero-text-content .d-flex.flex-column .btn {
        width: auto !important;
        align-self: flex-start;
    }
    
    .hero-text-content .btn,
    .hero-text-content .btn.btn-lg {
        font-size: 0.8rem !important;
        padding: 0.3rem 0.55rem !important;
        width: auto;
        max-width: none;
        line-height: 1.35 !important;
        min-height: auto !important;
        height: auto !important;
    }
    
    .hero-text-content .btn.px-4 {
        padding-left: 0.55rem !important;
        padding-right: 0.55rem !important;
    }
    
    .hero-text-content .btn i,
    .hero-text-content .btn.btn-lg i {
        font-size: 0.8rem !important;
        margin-right: 0.2rem !important;
    }
    
    /* Les styles pour très petits écrans sont déjà dans le media query mobile */
    .hero-nav {
        display: none !important;
    }
    
    .hero-dots {
        display: none !important;
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

/* Styles harmonisés - utilisent les styles globaux de app.blade.php */

.rating i {
    font-size: 0.9em; /* relative to parent */
}

/* Modern Testimonials Horizontal Scroll */
.modern-testimonials-container {
    position: relative;
    margin: 0 -15px;
    padding: 0 15px;
    overflow: hidden;
}

.modern-testimonials-wrapper {
    display: flex;
    overflow-x: auto;
    gap: 1rem;
    padding: 1rem 0;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
    scroll-snap-type: x mandatory;
}

.modern-testimonials-wrapper::-webkit-scrollbar {
    display: none;
}

.modern-testimonial-item {
    flex: 0 0 auto;
    width: 320px;
    scroll-snap-align: start;
}

.modern-testimonial-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.modern-testimonial-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 20px rgba(0, 51, 102, 0.15);
}

.modern-testimonial-rating {
    margin-bottom: 1rem;
}

.modern-testimonial-rating i {
    font-size: 0.9rem;
    margin-right: 2px;
}

.modern-testimonial-text {
    flex: 1;
    margin-bottom: 1.5rem;
}

.modern-testimonial-text p {
    font-size: 0.95rem;
    line-height: 1.6;
    color: #555;
    font-style: italic;
    margin: 0;
}

.modern-testimonial-author {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modern-testimonial-avatar img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.modern-testimonial-info {
    flex: 1;
}

.modern-testimonial-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #003366;
    margin: 0 0 0.25rem 0;
}

.modern-testimonial-title {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
}

/* Modern Testimonial Scroll Buttons */
.modern-testimonial-scroll-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    background: rgba(255, 255, 255, 0.95);
    border: none;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.3s ease;
    color: #003366;
    font-size: 1rem;
}

.modern-testimonial-scroll-btn:hover {
    background: #003366;
    color: white;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 51, 102, 0.3);
}

.modern-testimonial-scroll-btn:active {
    transform: translateY(-50%) scale(0.95);
}

.modern-testimonial-scroll-left {
    left: 10px;
}

.modern-testimonial-scroll-right {
    right: 10px;
}

/* Testimonial Mobile Progress Indicator */
.modern-testimonial-mobile-progress {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
    padding: 0 1rem;
}

.modern-testimonial-progress-dots {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.modern-testimonial-progress-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgba(0, 51, 102, 0.2);
    transition: all 0.3s ease;
    cursor: pointer;
}

.modern-testimonial-progress-dot.active {
    background: #003366;
    width: 20px;
    border-radius: 3px;
}

.modern-testimonial-progress-dot:hover {
    background: #004080;
    transform: scale(1.1);
}

@media (min-width: 768px) {
    .modern-testimonial-mobile-progress {
        display: none;
    }
}

@media (max-width: 767.98px) {
    .modern-testimonials-container {
        margin: 0 -0.75rem;
        padding: 0 0.75rem;
    }
    
    .modern-testimonials-wrapper {
        gap: 0.75rem;
        padding: 0.75rem 0;
    }
    
    .modern-testimonial-item {
        width: 280px;
    }
    
    .modern-testimonial-card {
        padding: 1.25rem;
    }
    
    .modern-testimonial-rating i {
        font-size: 0.85rem;
    }
    
    .modern-testimonial-text p {
        font-size: 0.875rem;
    }
    
    .modern-testimonial-avatar img {
        width: 40px;
        height: 40px;
    }
    
    .modern-testimonial-name {
        font-size: 0.9rem;
    }
    
    .modern-testimonial-title {
        font-size: 0.8rem;
    }
    
    .modern-testimonial-scroll-btn {
        display: none !important;
    }
}

@media (max-width: 575.98px) {
    .modern-testimonials-container {
        margin: 0 -0.75rem;
        padding: 0 0.75rem;
    }
    
    .modern-testimonials-wrapper {
        gap: 0.5rem;
        padding: 0.5rem 0;
    }
    
    .modern-testimonial-item {
        width: 260px;
    }
    
    .modern-testimonial-card {
        padding: 1rem;
    }
    
    .modern-testimonial-rating i {
        font-size: 0.8rem;
    }
    
    .modern-testimonial-text p {
        font-size: 0.85rem;
    }
    
    .modern-testimonial-avatar img {
        width: 35px;
        height: 35px;
    }
    
    .modern-testimonial-name {
        font-size: 0.875rem;
    }
    
    .modern-testimonial-title {
        font-size: 0.75rem;
    }
}
    
    /* Titres de sections plus petits sur mobile */
    section h2.display-5,
    section h2.h3 {
        font-size: 1.25rem !important;
        margin-bottom: 0.5rem !important;
    }
    
    section .lead,
    section p.lead {
        font-size: 0.875rem !important;
    }
    
    .categories-section h2 {
        font-size: 1.15rem !important;
    }
    
    .categories-section p {
        font-size: 0.85rem !important;
    }
    
    /* Section catégories visible en bas de la bannière */
    .categories-section {
        padding-top: 2rem !important;
        padding-bottom: 2rem !important;
    }
    
    .categories-section .row.mb-4 {
        margin-bottom: 1rem !important;
    }
    
    .categories-section h2 {
        font-size: 1.1rem !important;
        margin-bottom: 0.5rem !important;
    }
    
    .categories-section p {
        font-size: 0.8rem !important;
        margin-bottom: 0.5rem !important;
    }
    
    /* Amélioration de l'espacement des sections sur mobile */
    section {
        padding-top: 2rem !important;
        padding-bottom: 2rem !important;
    }
    
    /* CTA section responsive */
    .cta-section {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    
    .cta-section h2 {
        font-size: 1.5rem !important;
        margin-bottom: 1rem !important;
    }
    
    .cta-section .lead {
        font-size: 0.95rem !important;
        margin-bottom: 1.5rem !important;
    }
    
    .cta-section .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .cta-section .btn:last-child {
        margin-bottom: 0;
    }
    
    /* Boutons de navigation des sections */
    .text-center .btn-lg {
        font-size: 0.9rem !important;
        padding: 0.6rem 1.2rem !important;
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    /* Espacement des cartes de cours - Bootstrap gère les largeurs */
    .course-card {
        margin-bottom: 1.5rem;
    }
    
    /* S'assurer que les cartes s'adaptent dans leurs colonnes Bootstrap */
    .course-card .card {
        width: 100%;
        height: 100%;
    }
    
    /* Sections pleine largeur */
    section {
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }
    
    /* Laisser Bootstrap gérer les containers normalement */
    section .container {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    
    /* Amélioration des titres */
    section h2.display-5,
    section h2.h3 {
        font-size: 1.5rem !important;
        margin-bottom: 0.75rem !important;
        line-height: 1.3 !important;
    }
    
    section .lead,
    section p.lead {
        font-size: 0.95rem !important;
        line-height: 1.5 !important;
    }
}

/* Responsive pour très petits écrans */
@media (max-width: 575.98px) {
    /* Container Bootstrap avec padding réduit - ne pas forcer avec !important */
    .container {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
        max-width: 100%;
    }
    
    /* Sections avec moins d'espacement */
    section {
        padding-top: 1.5rem !important;
        padding-bottom: 1.5rem !important;
    }
    
    /* Exclure CTA section de l'espacement */
    .cta-section {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    
    /* Titres encore plus petits */
    section h2.display-5,
    section h2.h3 {
        font-size: 1.25rem !important;
        margin-bottom: 0.5rem !important;
    }
    
    section .lead,
    section p.lead {
        font-size: 0.875rem !important;
    }
    
    /* Boutons plus compacts */
    .btn-lg {
        font-size: 0.85rem !important;
        padding: 0.5rem 1rem !important;
    }
    
    /* CTA section */
    .cta-section {
        padding: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    
    .cta-section h2 {
        font-size: 1.25rem !important;
        margin-bottom: 0.75rem !important;
    }
    
    .cta-section .lead {
        font-size: 0.875rem !important;
        margin-bottom: 1rem !important;
    }
    
    /* Testimonials navigation sur très petits écrans */
    .testimonials-navigation {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    #prevBtn, #nextBtn {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    
    .dots-container {
        order: 1;
        width: 100%;
        justify-content: center;
        margin-top: 0.5rem;
    }
    
    /* Espacement entre les cartes */
    .row.g-3,
    .row.g-4 {
        --bs-gutter-y: 1rem;
        --bs-gutter-x: 0.75rem;
    }
    
    /* Catégories scrollables plus compactes */
    .categories-scroll-container {
        margin: 0 -0.75rem;
        padding: 0 0.75rem;
    }
    
    /* Optimiser catégories sur très petits écrans */
    .category-item-scroll {
        width: 150px;
        min-width: 150px;
    }
    
    .category-item-scroll .category-card .card {
        height: 150px;
    }
    
    .category-item-scroll .category-card .card-body {
        padding: 0.5rem;
    }
    
    .category-item-scroll .category-card .category-icon i {
        font-size: 1.1rem;
    }
    
    .category-item-scroll .category-card .card-title {
        font-size: 0.8rem;
    }
    
    .category-item-scroll .category-card .card-text {
        font-size: 0.7rem;
        height: 1.5rem;
    }
    
    /* S'assurer que les médias ne débordent pas */
    img, video, iframe, object, embed {
        max-width: 100%;
        height: auto;
    }
}

</style>


@endpush