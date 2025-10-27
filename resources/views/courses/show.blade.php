@extends('layouts.app')

@section('title', $course->title . ' - Herime Academie')
@section('description', $course->meta_description ?: Str::limit($course->description, 160))

@section('content')
<style>
:root {
    --primary-color: #003366;
    --accent-color: #ffcc33;
    --secondary-color: #f8f9fa;
    --text-color: #2c3e50;
    --text-muted: #6c757d;
    --border-color: #e9ecef;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --info-color: #17a2b8;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
}

.course-details-page {
    background: #f8f9fa;
}

/* Titres de section harmonisés avec checkout */
.section-title {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 1.5rem; /* text-2xl */
    border-bottom: 3px solid var(--accent-color);
    padding-bottom: 1rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
}

.section-title i {
    margin-right: 0.75rem;
    color: var(--accent-color);
}

/* Breadcrumb amélioré */
.breadcrumb {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.breadcrumb-item a:hover {
    color: var(--accent-color);
    text-decoration: underline;
}

.breadcrumb-item.active {
    color: var(--text-muted);
    font-weight: 600;
}

/* Badges harmonisés */
.badge {
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 8px;
}

.badge.bg-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%) !important;
}

.badge.bg-warning {
    background: linear-gradient(135deg, var(--accent-color) 0%, #e6b800 100%) !important;
    color: var(--primary-color) !important;
}

.badge.bg-success {
    background: linear-gradient(135deg, var(--success-color) 0%, #20c997 100%) !important;
}

/* Titre principal harmonisé */
.course-title {
    color: var(--primary-color);
    font-weight: 700;
    margin-bottom: 1.5rem;
    position: relative;
}

.course-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 0;
    width: 60px;
    height: 4px;
    background: linear-gradient(135deg, var(--accent-color) 0%, #e6b800 100%);
    border-radius: 2px;
}

/* Statistiques du cours harmonisées */
.course-stats {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid rgba(0, 51, 102, 0.1);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0, 51, 102, 0.1);
}

.course-stats .stat-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    color: var(--text-color);
    font-weight: 500;
}

.course-stats .stat-item i {
    color: var(--primary-color);
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
}

.course-stats .stat-item:last-child {
    margin-bottom: 0;
}


/* Média du cours harmonisé */
.course-media {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0, 51, 102, 0.15);
    border: 2px solid rgba(0, 51, 102, 0.1);
}

.course-media video,
.course-media img {
    border-radius: 12px;
}

/* Description du cours harmonisée */
.course-description {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid rgba(0, 51, 102, 0.1);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0, 51, 102, 0.1);
}

.course-description h3 {
    color: var(--primary-color);
    font-weight: 700;
    margin-bottom: 1.5rem;
    position: relative;
}

.course-description h3::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 40px;
    height: 3px;
    background: linear-gradient(135deg, var(--accent-color) 0%, #e6b800 100%);
    border-radius: 2px;
}

/* Contenu harmonisé */
.content {
    color: var(--text-color);
    line-height: 1.7;
    font-size: 1.125rem; /* text-lg */
}

.content p {
    margin-bottom: 1.25rem;
}

.content p:last-child {
    margin-bottom: 0;
}

/* Sections harmonisées avec checkout */
.download-section,
.enroll-section,
.purchase-section,
.what-youll-learn,
.requirements,
.course-curriculum,
.instructor-info,
.reviews,
.recommended-courses {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid rgba(0, 51, 102, 0.1);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0, 51, 102, 0.1);
    transition: all 0.3s ease;
}

.download-section:hover,
.enroll-section:hover,
.purchase-section:hover,
.what-youll-learn:hover,
.requirements:hover,
.course-curriculum:hover,
.instructor-info:hover,
.reviews:hover,
.recommended-courses:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 51, 102, 0.15);
    border-color: var(--accent-color);
}

/* Cartes harmonisées */
.card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid rgba(0, 51, 102, 0.1);
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 51, 102, 0.1);
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 51, 102, 0.15);
    border-color: var(--accent-color);
}

/* Listes harmonisées */
.list-unstyled li {
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(0, 51, 102, 0.1);
    color: var(--text-color);
    font-weight: 500;
}

.list-unstyled li:last-child {
    border-bottom: none;
}

.list-unstyled li::before {
    content: '✓';
    color: var(--success-color);
    font-weight: bold;
    margin-right: 0.75rem;
}

/* Curriculum harmonisé */
.curriculum-stats .badge {
    background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%) !important;
    color: white !important;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 8px;
}

/* Reviews harmonisées */
.reviews-summary {
    background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
    color: white;
    padding: 1rem;
    border-radius: 12px;
    text-align: center;
}

.reviews-summary .rating i {
    color: #ffcc33 !important;
}

.reviews-summary .fw-bold {
    color: white !important;
}

.reviews-summary .text-muted {
    color: rgba(255, 255, 255, 0.8) !important;
}

/* Étoiles dans les avis individuels */
.reviews .rating i {
    color: #ffcc33 !important;
}

.reviews .rating i.text-muted {
    color: #e9ecef !important;
}

.reviews-summary .rating-display {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

/* Instructor card harmonisée */
.instructor-info .card {
    border: 2px solid var(--primary-color);
}

.instructor-info .card:hover {
    border-color: var(--accent-color);
}

/* Related courses harmonisées et compactes */
.recommended-courses .course-card {
    height: 100%;
}

.recommended-courses .card {
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid rgba(0, 51, 102, 0.1);
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 51, 102, 0.1);
    overflow: hidden;
}

.recommended-courses .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0, 51, 102, 0.2);
    border-color: var(--accent-color);
}

/* Image compacte */
.recommended-courses .card-img-top {
    height: 160px !important;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.recommended-courses .card:hover .card-img-top {
    transform: scale(1.05);
}

/* Badges repositionnés */
.recommended-courses .position-absolute {
    z-index: 2;
}

.recommended-courses .badge {
    font-size: 0.75rem;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-weight: 600;
}

/* Contenu de la carte compact */
.recommended-courses .card-body {
    padding: 1.5rem !important;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

/* Titre compact */
.recommended-courses .card-title {
    font-size: 1.125rem; /* text-lg */
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.75rem;
    line-height: 1.3;
    height: 2.6rem;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.recommended-courses .card-title a {
    color: var(--primary-color);
    text-decoration: none;
    transition: color 0.3s ease;
}

.recommended-courses .card-title a:hover {
    color: var(--accent-color);
}

/* Description compacte */
.recommended-courses .card-text {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin-bottom: 1rem;
    height: 2.7rem;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-height: 1.35;
}

/* Métadonnées compactes */
.recommended-courses .course-meta {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.recommended-courses .instructor-info {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

/* Styles pour les cartes de cours recommandés (même design que le panier) */
.recommended-courses .course-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.recommended-courses .course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.recommended-courses .card-body {
    padding: 0.75rem;
}

.recommended-courses .card-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1.2;
    margin-bottom: 0.5rem;
}

.recommended-courses .card-text {
    font-size: 0.8rem;
    color: var(--text-muted);
    line-height: 1.3;
    margin-bottom: 0.5rem;
}

.recommended-courses .btn {
    font-size: 0.75rem;
    padding: 0.4rem 0.6rem;
    border-radius: 6px;
    font-weight: 600;
}

.recommended-courses .btn-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
    border: none;
}

.recommended-courses .btn-primary:hover {
    background: linear-gradient(135deg, #004080 0%, var(--primary-color) 100%);
    transform: translateY(-1px);
}

.recommended-courses .btn-outline-primary {
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
}

.recommended-courses .btn-outline-primary:hover {
    background: var(--primary-color);
    color: white;
}


/* Responsive mobile pour les cartes de cours recommandés */
@media (max-width: 576px) {
    .recommended-courses .card-body {
        padding: 0.5rem;
    }
    
    .recommended-courses .card-title {
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
    }
    
    .recommended-courses .card-text {
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }
    
    .recommended-courses .btn {
        font-size: 0.7rem;
        padding: 0.3rem 0.5rem;
    }
    
    .recommended-courses .card-img-top {
        height: 100px !important;
    }
}

/* Responsive mobile pour la photo de couverture */
@media (max-width: 768px) {
    .course-media .ratio-16x9 {
        aspect-ratio: 16/9 !important;
        max-height: 250px;
    }
    
    .course-media .video-play-overlay {
        transform: scale(0.8);
    }
    
    .course-media .play-button i {
        font-size: 2rem;
    }
    
    .course-media .play-text {
        font-size: 0.8rem;
    }
}

/* Responsive mobile pour le programme du cours */
@media (max-width: 768px) {
    .course-curriculum .accordion-button {
        padding: 0.75rem;
        font-size: 0.9rem;
    }
    
    .course-curriculum .section-header-content {
        display: flex;
        flex-direction: column;
    }
    
    .course-curriculum .section-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .course-curriculum .section-stats {
        display: flex;
        gap: 0.5rem;
        flex-wrap: nowrap;
        align-items: center;
    }
    
    .course-curriculum .section-stats .badge {
        white-space: nowrap;
        flex-shrink: 0;
        margin-bottom: 0;
    }
    
    .course-curriculum .section-stats.d-md-none {
        margin-top: 0.5rem;
    }
    
    .course-curriculum .section-meta {
        margin-top: 0.5rem;
        font-size: 0.8rem;
    }
    
    .course-curriculum .lesson-item-simple {
        padding: 0.75rem;
    }
    
    .course-curriculum .lesson-content {
        width: 100%;
    }
    
    .course-curriculum .lesson-header {
        flex-wrap: nowrap;
        gap: 0.5rem;
    }
    
    .course-curriculum .lesson-title {
        min-width: 0;
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .course-curriculum .lesson-preview {
        flex-shrink: 0;
    }
    
    .course-curriculum .curriculum-stats {
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .course-curriculum .curriculum-stats .badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.6rem;
    }
}

@media (max-width: 576px) {
    .course-curriculum .lesson-item-simple {
        padding: 0.5rem;
    }
    
    .course-curriculum .lesson-header {
        flex-direction: row;
        align-items: center !important;
        gap: 0.25rem;
    }
    
    .course-curriculum .lesson-title {
        min-width: 0;
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 0.85rem;
    }
    
    .course-curriculum .lesson-icon {
        font-size: 0.9rem;
    }
    
    .course-curriculum .lesson-preview {
        flex-shrink: 0;
    }
    
    .course-curriculum .lesson-preview .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
    
    .course-curriculum .section-stats .badge {
        font-size: 0.7rem;
        padding: 0.3rem 0.5rem;
    }
}

/* Responsive mobile pour les avis */
@media (max-width: 768px) {
    .reviews .recent-reviews .row {
        margin: 0;
    }
    
    .reviews .recent-reviews .col-md-6 {
        padding: 0.25rem;
    }
    
    .reviews .card-body {
        padding: 0.75rem !important;
    }
    
    .reviews .d-flex.justify-content-between {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.5rem;
    }
    
    .reviews .btn-outline-primary {
        font-size: 0.8rem;
        padding: 0.4rem 0.6rem;
    }
}


.recommended-courses .d-flex.justify-content-between:last-of-type {
    margin-bottom: 0;
}

/* Espacement final */
.recommended-courses .card-body > *:last-child {
    margin-top: auto;
}

/* Grille de métadonnées compacte */
.course-meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.course-meta-grid .meta-item {
    display: flex;
    align-items: center;
    font-size: 0.75rem;
    color: var(--text-muted);
    font-weight: 500;
}

.course-meta-grid .meta-item i {
    color: var(--primary-color);
    margin-right: 0.5rem;
    width: 14px;
    text-align: center;
    font-size: 0.7rem;
}

.course-meta-grid .meta-item span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Responsive pour la grille */
@media (max-width: 576px) {
    .course-meta-grid {
        grid-template-columns: 1fr;
        gap: 0.25rem;
    }
    
    .course-meta-grid .meta-item {
        font-size: 0.7rem;
    }
}

/* Réduction de l'espacement sur mobile */
@media (max-width: 768px) {
    .container {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }
    
    .course-header {
        margin-bottom: 1.5rem !important;
    }
    
    .course-description,
    .what-youll-learn,
    .course-curriculum,
    .reviews,
    .recommended-courses {
        margin-bottom: 2rem !important;
    }
    
    .section-title {
        margin-bottom: 1rem !important;
        font-size: 1.25rem !important;
    }
    
    .course-title {
        font-size: 1.5rem !important;
        margin-bottom: 1rem !important;
    }
    
    .breadcrumb {
        padding: 0.5rem !important;
        margin-bottom: 1rem !important;
    }
}

@media (max-width: 576px) {
    .container {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }
    
    .course-header {
        margin-bottom: 1rem !important;
    }
    
    .course-description,
    .what-youll-learn,
    .course-curriculum,
    .reviews,
    .recommended-courses {
        margin-bottom: 1.5rem !important;
    }
    
    .section-title {
        margin-bottom: 0.75rem !important;
        font-size: 1.1rem !important;
    }
    
    .course-title {
        font-size: 1.25rem !important;
        margin-bottom: 0.75rem !important;
    }
    
    .breadcrumb {
        padding: 0.25rem !important;
        margin-bottom: 0.75rem !important;
    }
    
    .course-media {
        margin-bottom: 1rem !important;
    }
}
</style>

<div class="course-details-page">
    <div class="container py-5">
    <div class="row">
        <!-- Course Content -->
        <div class="col-lg-8">
            <!-- Course Header -->
            <div class="course-header mb-4">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">Cours</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.category', $course->category->slug) }}">{{ $course->category->name }}</a></li>
                        <li class="breadcrumb-item active">{{ Str::limit($course->title, 30) }}</li>
                    </ol>
                </nav>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if($course->is_featured)
                    <span class="badge bg-warning">En vedette</span>
                    @endif
                    @if($course->is_free)
                    <span class="badge bg-success">Gratuit</span>
                    @endif
                    <span class="badge bg-primary">{{ $course->category->name }}</span>
                    <span class="badge bg-light text-dark">
                        @switch($course->level)
                            @case('beginner') Débutant @break
                            @case('intermediate') Intermédiaire @break
                            @case('advanced') Avancé @break
                        @endswitch
                    </span>
                </div>

                <h1 class="course-title display-5 fw-bold mb-3">{{ $course->title }}</h1>
                
                <!-- Statistiques du cours harmonisées -->
                <div class="course-stats">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="stat-item">
                                <i class="fas fa-star"></i>
                                <span>{{ number_format($course->reviews->avg('rating') ?? 0, 1) }} ({{ $course->reviews->count() }} avis)</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-language"></i>
                                <span>
                                    @php
                                        $languageNames = [
                                            'fr' => 'Français',
                                            'en' => 'Anglais',
                                            'es' => 'Espagnol',
                                            'de' => 'Allemand',
                                            'it' => 'Italien',
                                            'pt' => 'Portugais',
                                            'ar' => 'Arabe',
                                            'zh' => 'Chinois',
                                            'ja' => 'Japonais',
                                            'ko' => 'Coréen',
                                            'ru' => 'Russe',
                                            'nl' => 'Néerlandais',
                                            'sv' => 'Suédois',
                                            'no' => 'Norvégien',
                                            'da' => 'Danois',
                                            'fi' => 'Finnois',
                                            'pl' => 'Polonais',
                                            'tr' => 'Turc',
                                            'he' => 'Hébreu',
                                            'hi' => 'Hindi',
                                            'th' => 'Thaï',
                                            'vi' => 'Vietnamien',
                                            'id' => 'Indonésien',
                                            'ms' => 'Malais',
                                            'tl' => 'Tagalog',
                                            'sw' => 'Swahili',
                                            'af' => 'Afrikaans',
                                            'bg' => 'Bulgare',
                                            'hr' => 'Croate',
                                            'cs' => 'Tchèque',
                                            'et' => 'Estonien',
                                            'el' => 'Grec',
                                            'hu' => 'Hongrois',
                                            'is' => 'Islandais',
                                            'ga' => 'Irlandais',
                                            'lv' => 'Letton',
                                            'lt' => 'Lituanien',
                                            'mt' => 'Maltais',
                                            'ro' => 'Roumain',
                                            'sk' => 'Slovaque',
                                            'sl' => 'Slovène',
                                            'uk' => 'Ukrainien',
                                            'cy' => 'Gallois',
                                        ];
                                        $displayLanguage = $languageNames[$course->language] ?? $course->language ?? 'Non spécifiée';
                                    @endphp
                                    {{ $displayLanguage }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-item">
                                <i class="fas fa-clock"></i>
                                <span>{{ $course->sections->sum(function($section) { return $section->lessons->sum('duration'); }) }} min de vidéo</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-play-circle"></i>
                                <span>{{ $course->sections->sum(function($section) { return $section->lessons->count(); }) }} leçons</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Thumbnail/Video -->
                <div class="course-media mb-4">
                    @php
                        $hasVideoPreview = $course->video_preview;
                        $hasPreviewLessons = $course->sections->flatMap(function($section) {
                            return $section->lessons->where('is_preview', true)->where('type', 'video');
                        })->count() > 0;
                        $hasAnyPreview = $hasVideoPreview || $hasPreviewLessons;
                    @endphp
                    
                    @if($hasAnyPreview)
                    <div class="video-preview-container">
                        <!-- Icône de lecture vidéo au-dessus -->
                        <div class="video-play-icon-above mb-2">
                            <i class="fas fa-play-circle"></i>
                            <span class="ms-2">Aperçu vidéo disponible</span>
                        </div>
                        
                        <!-- Image preview cliquable -->
                        <div class="video-preview-thumbnail" data-bs-toggle="modal" data-bs-target="#coursePreviewModal" style="cursor: pointer;">
                            <div class="ratio ratio-16x9 position-relative">
                                <img src="{{ $course->thumbnail ? $course->thumbnail : 'https://ui-avatars.com/api/?name=' . urlencode($course->title) . '&background=003366&color=fff&size=800' }}" 
                                     alt="{{ $course->title }}" class="img-fluid rounded" style="height: 100%; width: 100%; object-fit: cover;">
                                <div class="video-play-overlay position-absolute top-50 start-50 translate-middle" style="pointer-events: none;">
                                    <div class="play-button">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                    <div class="play-text">Cliquez pour voir l'aperçu</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($course->thumbnail)
                    <img src="{{ $course->thumbnail }}" alt="{{ $course->title }}" 
                         class="img-fluid rounded shadow" style="height: 400px; width: 100%; object-fit: cover;">
                    @else
                    <div class="bg-light rounded shadow d-flex align-items-center justify-content-center" 
                         style="height: 400px;">
                        <div class="text-center text-muted">
                            <i class="fas fa-book fa-4x mb-3"></i>
                            <p class="h5">Aucune image de couverture</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Course Description -->
            <div class="course-description mb-5">
                <h3 class="section-title">
                    <i class="fas fa-book"></i>Description du cours
                </h3>
                <div class="content">
                    {!! nl2br(e($course->description)) !!}
                </div>
            </div>

            <!-- Download Section -->
            @if($course->is_downloadable && auth()->check())
            @endif

            <!-- What You'll Learn -->
            @php $learnings = $course->getWhatYouWillLearnArray(); @endphp
            @if(count($learnings) > 0)
            <div class="what-youll-learn mb-5">
                <h3 class="section-title">
                    <i class="fas fa-graduation-cap"></i>Ce que vous allez apprendre
                </h3>
                <div class="row">
                    @foreach($learnings as $item)
                    <div class="col-md-6 mb-2">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>{{ $item }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Requirements -->
            @php $requirements = $course->getRequirementsArray(); @endphp
            @if(count($requirements) > 0)
            <div class="requirements mb-5">
                <h3 class="section-title">
                    <i class="fas fa-list-check"></i>Prérequis
                </h3>
                <ul class="list-unstyled">
                    @foreach($requirements as $requirement)
                    <li class="mb-2">
                        <i class="fas fa-arrow-right text-primary me-2"></i>
                        {{ $requirement }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Course Curriculum -->
            @if($course->sections->count() > 0)
            <div class="course-curriculum mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="section-title mb-0">
                        <i class="fas fa-list-ol"></i>Programme du cours
                    </h3>
                    <div class="curriculum-stats">
                        <span class="badge bg-primary me-2"><i class="fas fa-layer-group me-1"></i>{{ $course->sections->count() }}</span>
                        <span class="badge bg-success me-2"><i class="fas fa-play-circle me-1"></i>{{ $course->sections->sum(function($section) { return $section->lessons->where('is_published', true)->count(); }) }}</span>
                        <span class="badge bg-info"><i class="fas fa-clock me-1"></i>{{ $course->sections->sum(function($section) { return $section->lessons->where('is_published', true)->sum('duration'); }) }}</span>
                    </div>
                </div>
                
                @if(!$isEnrolled)
                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-info-circle me-3"></i>
                    <div>
                        <strong>Accès au contenu :</strong> Pour accéder aux leçons et commencer votre apprentissage, 
                        inscrivez-vous au cours en utilisant le bouton "S'inscrire" dans la barre latérale droite.
                    </div>
                </div>
                @else
                <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-check-circle me-3"></i>
                    <div>
                        <strong>Vous êtes inscrit !</strong> Utilisez le bouton "Continuer le cours" dans la barre latérale 
                        pour accéder à toutes les leçons et commencer votre apprentissage.
                    </div>
                </div>
                @endif
                
                <div class="accordion" id="curriculumAccordion">
                    @foreach($course->sections as $index => $section)
                    @php
                        $publishedLessons = $section->lessons->where('is_published', true);
                        $sectionDuration = $publishedLessons->sum('duration');
                        $videoLessons = $publishedLessons->where('type', 'video')->count();
                        $textLessons = $publishedLessons->where('type', 'text')->count();
                        $pdfLessons = $publishedLessons->where('type', 'pdf')->count();
                        $quizLessons = $publishedLessons->where('type', 'quiz')->count();
                        $previewLessons = $publishedLessons->where('is_preview', true)->count();
                    @endphp
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $section->id }}">
                            <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#collapse{{ $section->id }}" 
                                    aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                    aria-controls="collapse{{ $section->id }}">
                                <div class="section-header-content w-100 me-3">
                                    <div class="section-title-row d-flex justify-content-between align-items-center mb-1">
                                        <strong>{{ $section->title }}</strong>
                                        <div class="section-stats d-none d-md-flex">
                                            <div class="badge bg-primary me-2"><i class="fas fa-play-circle me-1"></i>{{ $section->lessons->count() }}</div>
                                            <div class="badge bg-secondary"><i class="fas fa-clock me-1"></i>{{ $sectionDuration }}</div>
                                        </div>
                                        </div>
                                        @if($section->description)
                                        <small class="text-muted d-block">{{ $section->description }}</small>
                                        @endif
                                    <div class="section-stats d-md-none">
                                        <div class="badge bg-primary me-2"><i class="fas fa-play-circle me-1"></i>{{ $section->lessons->count() }}</div>
                                        <div class="badge bg-secondary"><i class="fas fa-clock me-1"></i>{{ $sectionDuration }}</div>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse{{ $section->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                             aria-labelledby="heading{{ $section->id }}" data-bs-parent="#curriculumAccordion">
                            <div class="accordion-body p-0">
                                @foreach($section->lessons as $lessonIndex => $lesson)
                                @php
                                    $lessonNumber = $lessonIndex + 1;
                                    $hasVideo = $lesson->type === 'video' && $lesson->content_url;
                                    $hasFile = $lesson->file_path && $lesson->type !== 'video';
                                    $isPreview = $lesson->is_preview;
                                @endphp
                                <div class="lesson-item-simple p-3 border-bottom {{ $isPreview ? 'preview-lesson' : '' }} {{ !$isEnrolled ? 'lesson-locked' : '' }}">
                                    <div class="lesson-content">
                                        <!-- Header avec icône et titre -->
                                        <div class="lesson-header d-flex align-items-center">
                                            <div class="lesson-icon me-2">
                                        @switch($lesson->type)
                                            @case('video')
                                                    <i class="fas fa-play-circle text-danger"></i>
                                                @break
                                            @case('text')
                                                <i class="fas fa-file-text text-info"></i>
                                                @break
                                            @case('pdf')
                                                <i class="fas fa-file-pdf text-danger"></i>
                                                @break
                                            @case('quiz')
                                                <i class="fas fa-question-circle text-warning"></i>
                                                @break
                                            @case('assignment')
                                                <i class="fas fa-tasks text-success"></i>
                                                @break
                                                @default
                                                    <i class="fas fa-file text-secondary"></i>
                                        @endswitch
                                    </div>
                                            <div class="lesson-title flex-grow-1">
                                                <span class="fw-medium">{{ $lesson->title }}</span>
                                            </div>
                                            @if($hasVideo && $isPreview)
                                                <div class="lesson-preview">
                                                    <span class="badge bg-info" data-bs-toggle="modal" data-bs-target="#lessonPreviewModal" data-lesson-url="{{ $lesson->content_url }}" data-lesson-title="{{ $lesson->title }}" style="cursor: pointer;" title="Voir l'aperçu">
                                                        <i class="fas fa-play-circle me-1"></i>Aperçu
                                                </span>
                                        </div>
                                                @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Instructor -->
            <div class="instructor-info mb-5">
                <h3 class="section-title">
                    <i class="fas fa-chalkboard-teacher"></i>À propos du formateur
                </h3>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                <img src="{{ $course->instructor->avatar ? $course->instructor->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($course->instructor->name) . '&background=003366&color=fff' }}" 
                                     alt="{{ $course->instructor->name }}" class="rounded-circle" width="100" height="100">
                            </div>
                            <div class="col-md-9">
                                <h5 class="fw-bold mb-2">{{ $course->instructor->name }}</h5>
                                @if($course->instructor->bio)
                                <p class="text-muted mb-3">{{ $course->instructor->bio }}</p>
                                @endif
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-graduation-cap text-primary me-2"></i>
                                        <span class="text-muted">{{ $course->instructor->courses_count ?? $course->instructor->courses->count() }} cours</span>
                                    </div>
                                    @if($course->instructor->website)
                                    <a href="{{ $course->instructor->website }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-globe me-1"></i>Site web
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Annonces récentes -->
            @if($courseStats['recent_announcements']->count() > 0)
            <div class="announcements mb-5">
                <h3 class="h4 fw-bold mb-3">
                    <i class="fas fa-bullhorn me-2"></i>Annonces récentes
                </h3>
                <div class="row">
                    @foreach($courseStats['recent_announcements'] as $announcement)
                    <div class="col-12 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start">
                                    <div class="announcement-icon me-3">
                                        <i class="fas fa-bullhorn text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1">{{ $announcement->title }}</h6>
                                        <p class="text-muted small mb-2">{{ Str::limit($announcement->content, 150) }}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>{{ $announcement->created_at->format('d/m/Y à H:i') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Reviews -->
            @if($course->reviews->count() > 0)
            <div class="reviews mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="section-title mb-0">
                        <i class="fas fa-star"></i>Avis des étudiants
                    </h3>
                    <div class="reviews-summary">
                        <div class="d-flex align-items-center">
                            <div class="rating me-3">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= floor($course->reviews->avg('rating') ?? 0) ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                            </div>
                            <span class="fw-bold text-primary">{{ number_format($course->reviews->avg('rating') ?? 0, 1) }}</span>
                            <span class="text-muted ms-2">({{ $course->reviews->count() }} avis)</span>
                        </div>
                    </div>
                </div>

                <!-- Distribution des notes -->
                <div class="rating-distribution mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Distribution des notes</h6>
                            @for($i = 5; $i >= 1; $i--)
                            <div class="d-flex align-items-center mb-2">
                                <span class="me-2">{{ $i }} étoiles</span>
                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                    @php
                                        $ratingCount = $course->reviews->where('rating', $i)->count();
                                        $percentage = $course->reviews->count() > 0 ? ($ratingCount / $course->reviews->count()) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-warning" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-muted small">{{ $ratingCount }}</span>
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Avis récents -->
                <div class="recent-reviews">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Avis récents</h6>
                        @if($course->reviews->count() > 4)
                        <button class="btn btn-outline-primary btn-sm" onclick="showAllReviews()">
                            <i class="fas fa-eye me-1"></i>Voir tous les avis ({{ $course->reviews->count() }})
                        </button>
                        @endif
                    </div>
                    <div class="row g-3">
                        @foreach($course->reviews->take(4) as $review)
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-2">
                                    <img src="{{ $review->user->avatar ? $instructor->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($review->user->name) . '&background=003366&color=fff' }}" 
                                             alt="{{ $review->user->name }}" class="rounded-circle me-2" width="32" height="32">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">{{ $review->user->name }}</h6>
                                            <div class="rating" style="font-size: 0.8rem;">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                        <small class="text-muted" style="font-size: 0.75rem;">{{ $review->created_at->format('d/m/Y') }}</small>
                                </div>
                                @if($review->comment)
                                    <p class="mb-0 text-muted" style="font-size: 0.85rem; line-height: 1.4;">
                                        {{ Str::limit($review->comment, 120) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Recommended Courses -->
            @if($relatedCourses->count() > 0)
            <div class="recommended-courses mb-5">
                <h3 class="section-title mb-4">
                    <i class="fas fa-thumbs-up"></i>Cours recommandés pour vous
                </h3>
                <div class="row g-3">
                    @foreach($relatedCourses as $relatedCourse)
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="card border-0 shadow-sm h-100 course-card">
                                <div class="position-relative">
                                <img src="{{ $relatedCourse->thumbnail ? $relatedCourse->thumbnail : 'https://ui-avatars.com/api/?name=' . urlencode($relatedCourse->title) . '&background=003366&color=fff&size=400' }}" 
                                     alt="{{ $relatedCourse->title }}" 
                                     class="card-img-top" 
                                     style="height: 120px; object-fit: cover;">
                                
                                <!-- Course Badges -->
                                <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
                                    @if($relatedCourse->is_featured)
                                    <span class="badge bg-warning">En vedette</span>
                                    @endif
                                    @if($relatedCourse->is_free)
                                    <span class="badge bg-success">Gratuit</span>
                                    @endif
                                    @if($relatedCourse->sale_price)
                                    <span class="badge bg-danger">
                                        -{{ round((($relatedCourse->price - $relatedCourse->sale_price) / $relatedCourse->price) * 100) }}%
                                    </span>
                                    @endif
                                        </div>
                                    </div>
                                    
                            <div class="card-body d-flex flex-column p-3">
                                <h6 class="card-title fw-bold mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.95rem;">
                                    <a href="{{ route('courses.show', $relatedCourse->slug) }}" class="text-decoration-none text-dark">
                                        {{ $relatedCourse->title }}
                                    </a>
                                </h6>
                                
                                <p class="card-text text-muted small mb-2" style="display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.8rem;">
                                    {{ $relatedCourse->short_description }}
                                </p>
                                
                                <div class="mb-2">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $relatedCourse->instructor->avatar ? $relatedCourse->instructor->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($relatedCourse->instructor->name) . '&background=003366&color=fff' }}" 
                                                 alt="{{ $relatedCourse->instructor->name }}" 
                                                 class="rounded-circle me-1" 
                                                 style="width: 20px; height: 20px; object-fit: cover;">
                                            <small class="text-muted" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.75rem;">
                                                {{ Str::limit($relatedCourse->instructor->name, 15) }}
                                            </small>
                                        </div>
                                        <span class="badge bg-light text-dark" style="font-size: 0.7rem;">{{ ucfirst($relatedCourse->level) }}</span>
                                        </div>
                                    
                                    <div class="d-flex align-items-center justify-content-between">
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <i class="fas fa-star text-warning me-1"></i>
                                            {{ number_format($relatedCourse->reviews_avg_rating ?? 0, 1) }} ({{ $relatedCourse->reviews_count ?? 0 }})
                                        </small>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $relatedCourse->sections->sum(function($section) { return $section->lessons->sum('duration'); }) }} min
                                        </small>
                                        </div>
                                    </div>
                                    
                                <div class="mt-auto">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div>
                                            @if($relatedCourse->is_free)
                                            <span class="fw-bold text-success" style="font-size: 0.9rem;">Gratuit</span>
                                            @else
                                            <div>
                                                <span class="fw-bold text-primary" style="font-size: 0.9rem;">${{ number_format($relatedCourse->sale_price ?? $relatedCourse->price, 2) }}</span>
                                                @if($relatedCourse->sale_price)
                                                <small class="text-muted text-decoration-line-through d-block" style="font-size: 0.7rem;">${{ number_format($relatedCourse->price, 2) }}</small>
                                            @endif
                                        </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-1">
                                        <x-course-button :course="$relatedCourse" size="small" :show-cart="false" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="course-sidebar sticky-top">
                <!-- Course Purchase Card -->
                <div class="card border-0 shadow-sm mb-4 course-purchase-card">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            @if($course->is_free)
                                <div class="h2 text-success fw-bold">Gratuit</div>
                            @else
                                @if($course->sale_price)
                                    <div class="h2 text-primary fw-bold">${{ number_format($course->sale_price, 2) }}</div>
                                    <div class="text-muted text-decoration-line-through">${{ number_format($course->price, 2) }}</div>
                                    <div class="badge bg-danger">
                                        -{{ round((($course->price - $course->sale_price) / $course->price) * 100) }}% de réduction
                                    </div>
                                @else
                                    <div class="h2 text-primary fw-bold">${{ number_format($course->price, 2) }}</div>
                                @endif
                            @endif
                        </div>

                        <x-course-button :course="$course" size="normal" :show-cart="true" />


                        <div class="course-features">
                            <h6 class="fw-bold mb-3">Ce cours comprend :</h6>
                            <ul class="list-unstyled">
                                @php
                                    $totalLessons = $course->sections->sum(function($section) { return $section->lessons->where('is_published', true)->count(); });
                                    $totalDuration = $course->sections->sum(function($section) { return $section->lessons->where('is_published', true)->sum('duration'); });
                                    $videoLessons = $course->sections->sum(function($section) { return $section->lessons->where('is_published', true)->where('type', 'video')->count(); });
                                    $textLessons = $course->sections->sum(function($section) { return $section->lessons->where('is_published', true)->where('type', 'text')->count(); });
                                    $pdfLessons = $course->sections->sum(function($section) { return $section->lessons->where('is_published', true)->where('type', 'pdf')->count(); });
                                    $quizLessons = $course->sections->sum(function($section) { return $section->lessons->where('is_published', true)->where('type', 'quiz')->count(); });
                                @endphp
                                
                                @if($totalLessons > 0)
                                <li class="mb-2">
                                    <i class="fas fa-play-circle text-primary me-2"></i>
                                    {{ $totalLessons }} leçons
                                </li>
                                @endif
                                
                                @if($videoLessons > 0)
                                <li class="mb-2">
                                    <i class="fas fa-play-circle text-danger me-2"></i>
                                    {{ $videoLessons }} vidéos
                                </li>
                                @endif
                                
                                @if($textLessons > 0)
                                <li class="mb-2">
                                    <i class="fas fa-file-text text-info me-2"></i>
                                    {{ $textLessons }} textes
                                </li>
                                @endif
                                
                                @if($pdfLessons > 0)
                                <li class="mb-2">
                                    <i class="fas fa-file-pdf text-danger me-2"></i>
                                    {{ $pdfLessons }} PDFs
                                </li>
                                @endif
                                
                                @if($quizLessons > 0)
                                <li class="mb-2">
                                    <i class="fas fa-question-circle text-warning me-2"></i>
                                    {{ $quizLessons }} quiz
                                </li>
                                @endif
                                
                                @if($totalDuration > 0)
                                <li class="mb-2">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    {{ $totalDuration }} minutes de contenu
                                </li>
                                @endif
                                
                                <li class="mb-2">
                                    <i class="fas fa-mobile-alt text-primary me-2"></i>
                                    Accès mobile et desktop
                                </li>
                                
                                @if($course->certificate_enabled ?? true)
                                <li class="mb-2">
                                    <i class="fas fa-certificate text-primary me-2"></i>
                                    Certificat de fin de cours
                                </li>
                                @endif
                                
                                <li class="mb-2">
                                    <i class="fas fa-infinity text-primary me-2"></i>
                                    Accès à vie
                                </li>
                                
                                @if($course->is_downloadable)
                                <li class="mb-2">
                                    <i class="fas fa-download text-primary me-2"></i>
                                    Téléchargement disponible
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Share Course -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Partager ce cours</h6>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" 
                               target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($course->title) }}" 
                               target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}" 
                               target="_blank" class="btn btn-outline-secondary btn-sm">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="copyToClipboard('{{ request()->url() }}')">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Video Preview Modal -->
@if($hasAnyPreview)
<!-- Course Preview Modal -->
<div class="modal fade" id="coursePreviewModal" tabindex="-1" aria-labelledby="coursePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white border-0" style="background-color: #003366;">
                <h5 class="modal-title fw-bold" id="coursePreviewModalLabel">
                    <i class="fas fa-play-circle me-2"></i>Aperçus du cours
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Lecteur vidéo -->
                    <div class="col-lg-8" id="videoColumn">
                        <div class="video-player-container p-4">
                            <div class="ratio ratio-16x9 mb-3" style="height: auto; min-height: 200px; max-height: 400px;">
                                <video id="coursePreviewVideo" controls class="rounded shadow" style="background: #000; width: 100%; height: 100%; object-fit: contain;">
                                    @if($course->video_preview)
                                        <source src="{{ Storage::url($course->video_preview) }}" type="video/mp4">
                                    @else
                                        <source src="" type="video/mp4">
                                    @endif
                                    Votre navigateur ne supporte pas la lecture vidéo.
                                </video>
                            </div>
                            <div class="bg-light rounded p-3" id="previewInfoContainer">
                                <h6 class="fw-bold mb-2" id="previewTitle" style="color: #003366;">Aperçu du cours</h6>
                                <p class="text-muted small mb-0" id="previewDescription">{{ $course->title }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Liste des previews -->
                    <div class="col-lg-4 bg-light" id="previewListContainer">
                        <div class="p-4 h-100">
                            <h6 class="fw-bold mb-3" style="color: #003366;">
                                <i class="fas fa-list me-2"></i>Autres aperçus
                            </h6>
                        <div class="preview-list">
                            @php
                                $previewLessons = $course->sections->flatMap(function($section) {
                                    return $section->lessons->where('is_preview', true)->where('type', 'video');
                                });
                            @endphp
                            
                            @if($course->video_preview)
                                <!-- Aperçu principal du cours -->
                                <div class="preview-item mb-3 p-3 border rounded" onclick="playPreview('{{ Storage::url($course->video_preview) }}', 'Aperçu du cours')" style="cursor: pointer; border-color: #003366 !important; background: rgba(0, 51, 102, 0.05);">
                                    <div class="d-flex align-items-center">
                                        <div class="preview-thumbnail me-3">
                                            <i class="fas fa-star text-warning fa-2x"></i>
                                        </div>
                                        <div class="preview-info flex-grow-1">
                                            <h6 class="mb-1 fw-medium" style="color: #003366;">Aperçu du cours</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-video me-1"></i>Vidéo principale
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($previewLessons->count() > 0)
                                @foreach($previewLessons as $lesson)
                                <div class="preview-item mb-3 p-3 border rounded" onclick="playPreview('{{ Storage::url($lesson->content_url) }}', '{{ $lesson->title }}')" style="cursor: pointer;">
                                    <div class="d-flex align-items-start">
                                        <div class="preview-thumbnail me-3">
                                            <i class="fas fa-play-circle fa-2x" style="color: #fff;"></i>
                                        </div>
                                        <div class="preview-info flex-grow-1">
                                            <h6 class="mb-1 fw-medium" style="color: #003366;">{{ $lesson->title }}</h6>
                                            <div class="d-flex flex-wrap gap-2 mb-1">
                                                <small class="text-muted">
                                                    <i class="fas fa-layer-group me-1"></i>{{ $lesson->section->title }}
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>{{ $lesson->duration }} min
                                                </small>
                                            </div>
                                            <small class="badge bg-light text-dark border">
                                                <i class="fas fa-play me-1"></i>Leçon {{ $lesson->sort_order }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                            
                            @if($previewLessons->count() == 0 && !$course->video_preview)
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-video fa-3x mb-3"></i>
                                    <p>Aucun aperçu disponible</p>
                                </div>
                            @endif
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Lesson Preview Modal -->
<div class="modal fade" id="lessonPreviewModal" tabindex="-1" aria-labelledby="lessonPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lessonPreviewModalLabel">Aperçu de la leçon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <video id="lessonPreviewVideo" controls class="rounded" style="background: #000;">
                        Votre navigateur ne supporte pas la lecture vidéo.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bouton flottant pour mobile -->
<button class="mobile-payment-toggle" id="mobilePaymentToggle">
    <i class="fas fa-shopping-cart"></i>
</button>

<!-- Payment Modal -->
@if(!$course->is_free && !auth()->check() || (!$course->isEnrolledBy(auth()->id() ?? 0)))
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Finaliser l'achat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Résumé de la commande</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ $course->title }}</span>
                            <span>${{ number_format($course->current_price, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span>${{ number_format($course->current_price, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Méthode de paiement</h6>
                        <form id="paymentForm">
                            @csrf
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="stripe" value="stripe" checked>
                                    <label class="form-check-label" for="stripe">
                                        <i class="fab fa-cc-stripe me-2"></i>Carte bancaire (Stripe)
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                    <label class="form-check-label" for="paypal">
                                        <i class="fab fa-paypal me-2"></i>PayPal
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="mobile_money" value="mobile_money">
                                    <label class="form-check-label" for="mobile_money">
                                        <i class="fas fa-mobile-alt me-2"></i>Mobile Money
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-credit-card me-2"></i>Payer ${{ number_format($course->current_price, 2) }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// Variables de contrôle
let sidebarOpen = false;
let isProcessing = false;

// Fonction pour ouvrir la sidebar
function openSidebar() {
    if (isProcessing) return;
    
    isProcessing = true;
    const sidebar = document.querySelector('.course-sidebar');
    const toggle = document.getElementById('mobilePaymentToggle');
    const icon = toggle.querySelector('i');
    
    sidebar.classList.add('show');
    toggle.classList.add('show');
    icon.className = 'fas fa-times';
    sidebarOpen = true;
    
    setTimeout(() => {
        isProcessing = false;
    }, 100);
}

// Fonction pour fermer la sidebar
function closeSidebar() {
    if (isProcessing) return;
    
    isProcessing = true;
    const sidebar = document.querySelector('.course-sidebar');
    const toggle = document.getElementById('mobilePaymentToggle');
    const icon = toggle.querySelector('i');
    
    sidebar.classList.remove('show');
    toggle.classList.remove('show');
    icon.className = 'fas fa-shopping-cart';
    sidebarOpen = false;
    
    setTimeout(() => {
        isProcessing = false;
    }, 100);
}

// Fonction supprimée - gestion directe dans onclick

// SUPPRIMÉ: Fermeture automatique pour éviter les conflits
// La sidebar ne se fermera que manuellement via le bouton

// Gestion du scroll pour masquer/afficher le bouton (désactivée pour garder le bouton toujours visible)
let lastScrollTop = 0;
window.addEventListener('scroll', function() {
    const toggle = document.getElementById('mobilePaymentToggle');
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    // Garder le bouton toujours visible sur mobile
    if (window.innerWidth <= 991.98) {
        toggle.style.transform = 'translateY(0)';
        toggle.style.opacity = '1';
    }
    
    lastScrollTop = scrollTop;
});

// Ajuster l'affichage au redimensionnement de la fenêtre
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.course-sidebar');
    const toggle = document.getElementById('mobilePaymentToggle');
    
    if (window.innerWidth > 991.98) {
        // Desktop - masquer le bouton et réinitialiser la sidebar
        toggle.style.display = 'none';
        toggle.style.opacity = '0';
        toggle.style.visibility = 'hidden';
        sidebar.classList.remove('show', 'slide-up', 'slide-down');
        sidebar.style.position = 'sticky';
        sidebar.style.top = '2rem';
        sidebar.style.bottom = 'auto';
        sidebar.style.left = 'auto';
        sidebar.style.right = 'auto';
        sidebar.style.transform = 'none';
        sidebar.style.zIndex = '10';
        sidebarOpen = false;
        isToggling = false;
    } else {
        // Mobile - afficher le bouton et configurer la sidebar
        toggle.style.display = 'flex';
        toggle.style.opacity = '1';
        toggle.style.visibility = 'visible';
        toggle.style.transform = 'translateY(0)';
        sidebar.style.position = 'fixed';
        sidebar.style.top = 'auto';
        sidebar.style.bottom = '0';
        sidebar.style.left = '0';
        sidebar.style.right = '0';
        sidebar.style.zIndex = '5';
        sidebar.style.transform = 'translateY(100%)';
        sidebarOpen = false;
        isToggling = false;
    }
});

// Initialiser l'affichage au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.course-sidebar');
    const toggle = document.getElementById('mobilePaymentToggle');
    
    // Gestionnaire d'événements ultra-simple
    toggle.addEventListener('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        
        if (sidebarOpen) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });
    
    if (window.innerWidth <= 991.98) {
        // Configuration mobile
        toggle.style.display = 'flex';
        toggle.style.opacity = '1';
        toggle.style.transform = 'translateY(0)';
        toggle.style.visibility = 'visible';
        
        sidebar.style.position = 'fixed';
        sidebar.style.top = 'auto';
        sidebar.style.bottom = '0';
        sidebar.style.left = '0';
        sidebar.style.right = '0';
        sidebar.style.zIndex = '5';
        sidebar.style.transform = 'translateY(100%)';
    } else {
        // Configuration desktop
        toggle.style.display = 'none';
        sidebar.style.position = 'sticky';
        sidebar.style.top = '2rem';
        sidebar.style.bottom = 'auto';
        sidebar.style.left = 'auto';
        sidebar.style.right = 'auto';
        sidebar.style.transform = 'none';
        sidebar.style.zIndex = '10';
    }
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Lien copié dans le presse-papiers !');
    });
}

// La fonction addToCart est maintenant définie globalement dans app.blade.php

function showNotification(message, type) {
    // Créer une notification toast
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Supprimer la notification après 3 secondes
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// La fonction updateCartCount est maintenant définie globalement dans app.blade.php

function proceedToCheckout() {
    // Vérifier si l'utilisateur est connecté
    @if(!auth()->check())
        alert('Vous devez être connecté pour procéder au paiement.');
        window.location.href = '{{ route("login") }}';
        return;
    @else
    // Ajouter le cours au panier d'abord
    addToCart({{ $course->id }});
    
    // Rediriger vers la page de checkout après un court délai
    setTimeout(() => {
        window.location.href = '{{ route("cart.checkout") }}';
    }, 1000);
    @endif
}

// Payment form handling
document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('course_id', {{ $course->id }});
    
    fetch('{{ route("payments.process") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.client_secret) {
                // Handle Stripe payment
                // This would integrate with Stripe.js
                alert('Redirection vers Stripe...');
            } else {
                alert('Paiement en cours de traitement...');
            }
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue lors du paiement.');
    });
});

// Gestion des modals d'aperçu vidéo
document.addEventListener('DOMContentLoaded', function() {
    // Modal pour l'aperçu de leçon
    const lessonPreviewModal = document.getElementById('lessonPreviewModal');
    const lessonPreviewVideo = document.getElementById('lessonPreviewVideo');
    
    if (lessonPreviewModal && lessonPreviewVideo) {
        lessonPreviewModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const lessonUrl = button.getAttribute('data-lesson-url');
            const lessonTitle = button.getAttribute('data-lesson-title');
            
            // Mettre à jour le titre de la modal
            const modalTitle = lessonPreviewModal.querySelector('.modal-title');
            modalTitle.textContent = lessonTitle || 'Aperçu de la leçon';
            
            // Mettre à jour la source vidéo
            lessonPreviewVideo.innerHTML = '';
            const source = document.createElement('source');
            source.src = lessonUrl;
            source.type = 'video/mp4';
            lessonPreviewVideo.appendChild(source);
            
            // Recharger la vidéo
            lessonPreviewVideo.load();
        });
        
        // Arrêter la vidéo quand la modal se ferme
        lessonPreviewModal.addEventListener('hidden.bs.modal', function () {
            lessonPreviewVideo.pause();
            lessonPreviewVideo.currentTime = 0;
        });
    }
    
    // Modal pour l'aperçu du cours
    const videoPreviewModal = document.getElementById('videoPreviewModal');
    if (videoPreviewModal) {
        const videoPreviewVideo = videoPreviewModal.querySelector('video');
        if (videoPreviewVideo) {
            // Arrêter la vidéo quand la modal se ferme
            videoPreviewModal.addEventListener('hidden.bs.modal', function () {
                videoPreviewVideo.pause();
                videoPreviewVideo.currentTime = 0;
            });
        }
    }
});

// Fonction pour jouer un preview
function playPreview(videoUrl, title) {
    const video = document.getElementById('coursePreviewVideo');
    const videoSource = video.querySelector('source');
    
    // Changer la source vidéo
    videoSource.src = videoUrl;
    video.load();
    
    // Mettre à jour le titre
    const titleElement = document.getElementById('previewTitle');
    const descriptionElement = document.getElementById('previewDescription');
    if (titleElement) titleElement.textContent = title;
    if (descriptionElement) descriptionElement.textContent = title;
    
    // Jouer la vidéo
    video.play();
}

// Initialiser la vidéo principale au chargement du modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('coursePreviewModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            const video = document.getElementById('coursePreviewVideo');
            const videoSource = video.querySelector('source');
            
            // Si pas de vidéo principale, charger la première leçon en preview
            @if($course->video_preview)
                videoSource.src = '{{ Storage::url($course->video_preview) }}';
                document.getElementById('previewTitle').textContent = 'Aperçu du cours';
                document.getElementById('previewDescription').textContent = '{{ $course->title }}';
            @else
                @php
                    $firstPreviewLesson = $course->sections->flatMap(function($section) {
                        return $section->lessons->where('is_preview', true)->where('type', 'video');
                    })->first();
                @endphp
                @if($firstPreviewLesson)
                    videoSource.src = '{{ Storage::url($firstPreviewLesson->content_url) }}';
                    document.getElementById('previewTitle').textContent = '{{ $firstPreviewLesson->title }}';
                    document.getElementById('previewDescription').textContent = '{{ $firstPreviewLesson->title }}';
                @endif
            @endif
            
            video.load();
        });
    }
});

// Fonction pour afficher tous les avis
function showAllReviews() {
    // Créer un modal pour afficher tous les avis
    const modalHtml = `
        <div class="modal fade" id="allReviewsModal" tabindex="-1" aria-labelledby="allReviewsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="allReviewsModalLabel">
                            <i class="fas fa-star me-2"></i>Tous les avis ({{ $course->reviews->count() }})
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            @foreach($course->reviews as $review)
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <img src="{{ $review->user->avatar ? $review->user->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($review->user->name) . '&background=003366&color=fff' }}" 
                                                 alt="{{ $review->user->name }}" class="rounded-circle me-3" width="40" height="40">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fw-bold">{{ $review->user->name }}</h6>
                                                <div class="rating">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $review->created_at->format('d/m/Y') }}</small>
                                        </div>
                                        @if($review->comment)
                                        <p class="mb-0 text-muted">{{ $review->comment }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Ajouter le modal au DOM s'il n'existe pas déjà
    if (!document.getElementById('allReviewsModal')) {
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    
    // Afficher le modal
    const modal = new bootstrap.Modal(document.getElementById('allReviewsModal'));
    modal.show();
}
</script>
@endpush

@push('styles')
<style>
/* Lecteur vidéo d'aperçu */
.video-preview-container {
    background: #000;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: relative;
    transition: all 0.3s ease;
}

.video-preview-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

/* Conteneur d'aperçu vidéo */
.video-preview-container {
    position: relative;
}

.video-play-icon-above {
    display: flex !important;
    align-items: center;
    font-size: 1.125rem; /* text-lg */
    color: #fff !important;
    margin-bottom: 0.5rem;
    padding: 0.75rem 1rem;
    background: #003366;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 51, 102, 0.3);
    border: none;
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.video-play-icon-above i {
    font-size: 1.5rem; /* text-2xl */
    margin-right: 0.5rem;
    color: #fff !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

/* Aperçu vidéo cliquable */
.video-preview-thumbnail {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0, 51, 102, 0.15);
    transition: all 0.3s ease;
    cursor: pointer !important;
    display: block !important;
}

.video-preview-thumbnail * {
    pointer-events: none;
}

.video-preview-thumbnail:hover {
    cursor: pointer !important;
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(0, 51, 102, 0.25);
}

/* Overlay de lecture */
.video-play-overlay {
    background: rgba(0, 0, 0, 0.6);
    border-radius: 50%;
    width: 80px;
    height: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
}

.video-preview-thumbnail:hover .video-play-overlay,
.video-preview-container:hover .video-play-overlay {
    background: rgba(0, 51, 102, 0.8);
    transform: scale(1.1);
}

.play-button {
    color: white;
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.video-preview-thumbnail:hover .play-button,
.video-preview-container:hover .play-button {
    color: #ffcc33;
    transform: scale(1.1);
}

.play-text {
    color: white;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.video-preview-wrapper {
    position: relative;
    width: 100%;
    height: 0;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
    background: #000;
}

/* Styles pour le modal d'aperçu */
.preview-item {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef !important;
    border-radius: 12px !important;
}

.preview-item:hover {
    background-color: #fff;
    border-color: #003366 !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 51, 102, 0.2);
}

.preview-thumbnail {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background: #003366;
    border-radius: 12px;
    color: white;
    flex-shrink: 0;
}

.preview-info h6 {
    font-size: 0.9rem;
    line-height: 1.3;
    margin-bottom: 0.5rem;
}

.preview-info .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.video-player-container {
    background-color: #fff;
    border-radius: 12px;
    padding: 1rem;
    height: auto;
    min-height: auto;
}

.preview-list {
    max-height: 400px;
    overflow-y: auto;
}

/* Adaptation mobile pour le modal */
@media (max-width: 991.98px) {
    .modal-xl {
        max-width: 95%;
    }
    
    .modal-body .row {
        flex-direction: column;
        margin: 0 !important;
    }
    
    .modal-body .col-lg-8,
    .modal-body .col-lg-4 {
        width: 100%;
        max-width: 100%;
        padding: 0 !important;
    }
    
    #videoColumn {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .video-player-container {
        padding: 1rem !important;
        padding-bottom: 0 !important;
        height: auto !important;
        min-height: auto !important;
        margin: 0 !important;
    }
    
    .video-player-container .ratio {
        height: auto !important;
        min-height: 200px;
        max-height: 300px;
    }
    
    #previewInfoContainer {
        margin-bottom: 0 !important;
        border-radius: 0 !important;
        border-bottom: none !important;
    }
    
    #previewListContainer {
        margin-top: 0 !important;
        padding-top: 0 !important;
        padding: 0 !important;
    }
    
    #previewListContainer .p-4 {
        padding-top: 0 !important;
        padding-bottom: 1rem !important;
        border-radius: 0 !important;
    }
    
    .preview-list {
        max-height: 300px;
    }
    
    .preview-item {
        padding: 0.75rem !important;
        margin-bottom: 0.5rem !important;
    }
    
    .preview-item:last-child {
        margin-bottom: 0 !important;
    }
    
    .preview-item .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .preview-thumbnail {
        margin-bottom: 0.5rem;
        margin-right: 0 !important;
    }
    
    .preview-info {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .modal-xl {
        max-width: 100%;
        margin: 0.5rem;
    }
    
    #videoColumn {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .video-player-container {
        padding: 0.5rem !important;
        padding-bottom: 0 !important;
        height: auto !important;
        min-height: auto !important;
        margin: 0 !important;
    }
    
    .video-player-container .ratio {
        height: auto !important;
        min-height: 150px;
        max-height: 250px;
    }
    
    #previewInfoContainer {
        margin-bottom: 0 !important;
        border-radius: 0 !important;
        border-bottom: none !important;
        padding: 0.75rem !important;
    }
    
    #previewListContainer {
        margin-top: 0 !important;
        padding-top: 0 !important;
        padding: 0 !important;
    }
    
    #previewListContainer .p-4 {
        padding-top: 0 !important;
        padding-bottom: 0.5rem !important;
        border-radius: 0 !important;
    }
    
    .preview-list {
        max-height: 250px;
    }
    
    .preview-item {
        margin-bottom: 0.25rem !important;
        padding: 0.5rem !important;
    }
    
    .preview-item:last-child {
        margin-bottom: 0 !important;
    }
    
    .preview-item h6 {
        font-size: 0.9rem;
    }
    
    .preview-item small {
        font-size: 0.75rem;
    }
}

.video-preview-player {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: contain;
    outline: none;
}

.video-preview-info {
    background: #f8f9fa;
    padding: 10px 15px;
    border-top: 1px solid #dee2e6;
}

.lesson-item-preview {
    transition: background-color 0.2s ease;
}

.lesson-item-preview:hover {
    background-color: #f8f9fa;
}

/* Amélioration des icônes de leçons */
.lesson-item-preview .me-3 i {
    font-size: 18px;
    width: 20px;
    text-align: center;
}

.lesson-item-preview .me-3 i.fa-play-circle {
    color: #dc3545 !important;
}

.lesson-item-preview .me-3 i.fa-file-text {
    color: #17a2b8 !important;
}

.lesson-item-preview .me-3 i.fa-file-pdf {
    color: #dc3545 !important;
}

.lesson-item-preview .me-3 i.fa-question-circle {
    color: #ffc107 !important;
}

.lesson-item-preview .me-3 i.fa-tasks {
    color: #28a745 !important;
}

/* Responsive pour les aperçus vidéo */
@media (max-width: 768px) {
    .video-preview-wrapper {
        padding-bottom: 60%; /* Ajustement pour mobile */
    }
}

/* Styles pour le programme du cours amélioré */
.curriculum-stats {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.section-info {
    flex: 1;
}

.section-meta {
    font-size: 0.85rem;
}

.section-stats {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 80px;
}

.lesson-item-preview {
    transition: all 0.2s ease;
    position: relative;
}

.lesson-item-preview:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
}

.lesson-item-preview.preview-lesson {
    background: linear-gradient(90deg, rgba(13, 110, 253, 0.05) 0%, transparent 100%);
    border-left: 3px solid #0d6efd;
}

.lesson-item-preview.lesson-locked {
    background: linear-gradient(90deg, rgba(108, 117, 125, 0.05) 0%, transparent 100%);
    border-left: 3px solid #6c757d;
    opacity: 0.8;
}

.lesson-item-preview.lesson-locked .lesson-title {
    color: #6c757d;
}

.lesson-item-preview.lesson-locked .lesson-icon i {
    color: #6c757d !important;
}

.lesson-item-preview.lesson-locked .lesson-meta small {
    color: #6c757d;
}

/* Structure simplifiée des leçons */
.lesson-item-simple {
    transition: background-color 0.2s ease;
}

.lesson-item-simple:hover {
    background-color: #f8f9fa;
}

.lesson-item-simple.preview-lesson {
    background: linear-gradient(90deg, rgba(13, 110, 253, 0.05) 0%, transparent 100%);
    border-left: 3px solid #0d6efd;
}

.lesson-item-simple.lesson-locked {
    background: linear-gradient(90deg, rgba(108, 117, 125, 0.05) 0%, transparent 100%);
    border-left: 3px solid #6c757d;
    opacity: 0.8;
}

.lesson-item-simple.lesson-locked .lesson-title {
    color: #6c757d;
}

.lesson-item-simple.lesson-locked .lesson-icon i {
    color: #6c757d !important;
}

/* Icône d'aperçu cliquable */
.preview-icon {
    transition: all 0.3s ease;
    padding: 8px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-icon:hover {
    background-color: rgba(13, 110, 253, 0.1);
    transform: scale(1.2);
}

.preview-icon i {
    font-size: 1.2rem;
    transition: color 0.3s ease;
}

.preview-icon:hover i {
    color: #0056b3 !important;
}

.lesson-number {
    min-width: 30px;
    text-align: center;
}

.lesson-number .badge {
    font-size: 0.75rem;
    font-weight: 600;
}

.lesson-icon {
    min-width: 20px;
    text-align: center;
}

.lesson-icon i {
    font-size: 18px;
}

.lesson-title {
    flex: 1;
}

.lesson-actions {
    gap: 8px;
    flex-wrap: wrap;
}

.lesson-description {
    line-height: 1.4;
}

.lesson-meta {
    margin-top: 8px;
}

.lesson-meta .d-flex {
    flex-wrap: wrap;
    gap: 12px;
}

.lesson-meta small {
    font-size: 0.75rem;
    white-space: nowrap;
}

/* Amélioration des badges */
.badge {
    font-size: 0.7rem;
    font-weight: 500;
    padding: 4px 8px;
}

/* Responsive pour le programme */
@media (max-width: 768px) {
    .curriculum-stats {
        margin-top: 10px;
        justify-content: center;
    }
    
    .section-info .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .section-stats {
        margin-top: 8px;
        flex-direction: row;
        min-width: auto;
    }
    
    .lesson-actions {
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
    }
    
    .lesson-meta .d-flex {
        flex-direction: column;
        gap: 4px;
    }
}
/* Sidebar et conteneur d'action de paiement harmonisés avec checkout */
.course-sidebar {
    position: sticky;
    top: 2rem;
    z-index: 10;
}

.course-purchase-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid var(--primary-color);
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 51, 102, 0.15);
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
}

.course-purchase-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
}

.course-purchase-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(0, 51, 102, 0.25);
    border-color: var(--accent-color);
}

.course-purchase-card .card-body {
    padding: 2rem;
    background: transparent;
}

.course-purchase-card .card-title {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 1.5rem; /* text-2xl */
    margin-bottom: 1.5rem;
    text-align: center;
}

.course-purchase-card .price {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 2.5rem;
    text-align: center;
    margin-bottom: 1.5rem;
    background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.course-features {
    margin-bottom: 2rem;
}

.course-features .feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    color: var(--text-color);
    font-weight: 500;
}

.course-features .feature-item i {
    color: var(--success-color);
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
}

.course-features .feature-item:last-child {
    margin-bottom: 0;
}

/* Boutons d'action harmonisés avec checkout */
.btn-action-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, #001a33 100%);
    border: 2px solid var(--primary-color);
    color: white;
    border-radius: 1rem;
    padding: 1.25rem 2rem;
    font-weight: 700;
    font-size: 16px;
    height: 60px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(0, 51, 102, 0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-action-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-action-primary:hover::before {
    left: 100%;
}

.btn-action-primary:hover {
    background: linear-gradient(135deg, #001a33 0%, var(--primary-color) 100%);
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(0, 51, 102, 0.4);
    border-color: var(--accent-color);
}

.btn-action-secondary {
    background: linear-gradient(135deg, var(--success-color) 0%, #20c997 100%);
    border: 2px solid var(--success-color);
    color: white;
    border-radius: 1rem;
    padding: 1.25rem 2rem;
    font-weight: 700;
    font-size: 16px;
    height: 60px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(40, 167, 69, 0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-action-secondary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-action-secondary:hover::before {
    left: 100%;
}

.btn-action-secondary:hover {
    background: linear-gradient(135deg, #20c997 0%, var(--success-color) 100%);
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(40, 167, 69, 0.4);
    border-color: #1e7e34;
}

/* Responsive mobile - Amélioration de la sidebar */
@media (max-width: 991.98px) {
    .course-sidebar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        top: auto;
        z-index: 5;
        background: linear-gradient(135deg, #ffffff 0%, #f0f4ff 100%);
        border-top: 3px solid #003366;
        box-shadow: 0 -8px 32px rgba(0, 51, 102, 0.15);
        padding: 0;
        transform: translateY(100%);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: block !important;
        border-radius: 20px 20px 0 0;
        backdrop-filter: blur(10px);
    }
    
    .course-sidebar.show {
        transform: translateY(0) !important;
    }
    
    .course-purchase-card {
        margin-bottom: 0;
        border: none;
        box-shadow: none;
        border-radius: 20px 20px 0 0;
        background: transparent;
    }
    
    .course-purchase-card .card-body {
        padding: 1.5rem;
        background: transparent;
    }
    
    /* Header du slider avec indicateur */
    .course-sidebar::before {
        content: '';
        position: absolute;
        top: 8px;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 4px;
        background: #003366;
        border-radius: 2px;
        opacity: 0.8;
    }
    
    /* Masquer les features sur mobile pour économiser l'espace */
    .course-features {
        display: none;
    }
    
    /* Boutons plus grands sur mobile avec design amélioré */
    .btn-action-primary,
    .btn-action-secondary {
        height: 56px;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 0.75rem;
        border-radius: 12px;
        border: none;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .btn-action-primary {
        background: linear-gradient(135deg, #003366 0%, #004080 100%);
        box-shadow: 0 4px 16px rgba(0, 51, 102, 0.3);
    }
    
    .btn-action-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 51, 102, 0.4);
        background: linear-gradient(135deg, #004080 0%, #003366 100%);
    }
    
    .btn-action-secondary {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        box-shadow: 0 4px 16px rgba(40, 167, 69, 0.3);
    }
    
    .btn-action-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(40, 167, 69, 0.4);
        background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
    }
    
    /* Prix avec design amélioré */
    .course-sidebar .h2 {
        background: linear-gradient(135deg, #003366 0%, #004080 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    
    /* Ajuster l'espacement du contenu principal */
    .col-lg-8 {
        margin-bottom: 140px; /* Espace pour la sidebar fixe */
    }
}

/* Bouton flottant pour afficher la sidebar sur mobile */
.mobile-payment-toggle {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 6;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #003366 0%, #004080 100%);
    color: white;
    border: none;
    box-shadow: 0 8px 24px rgba(0, 51, 102, 0.4);
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 1;
    transform: translateY(0);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.mobile-payment-toggle:hover {
    transform: scale(1.15) translateY(-2px);
    box-shadow: 0 12px 32px rgba(0, 51, 102, 0.6);
    background: linear-gradient(135deg, #004080 0%, #003366 100%);
}

.mobile-payment-toggle.show {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    transform: scale(1.1) translateY(-2px);
    box-shadow: 0 8px 24px rgba(220, 53, 69, 0.4);
    border-color: rgba(255, 255, 255, 0.3);
}

.mobile-payment-toggle.show:hover {
    transform: scale(1.2) translateY(-4px);
    box-shadow: 0 16px 40px rgba(220, 53, 69, 0.6);
    background: linear-gradient(135deg, #c82333 0%, #dc3545 100%);
}

/* Animation de pulsation pour attirer l'attention */
@keyframes pulse {
    0% {
        box-shadow: 0 8px 24px rgba(0, 51, 102, 0.4);
    }
    50% {
        box-shadow: 0 8px 24px rgba(0, 51, 102, 0.6), 0 0 0 8px rgba(0, 51, 102, 0.1);
    }
    100% {
        box-shadow: 0 8px 24px rgba(0, 51, 102, 0.4);
    }
}

.mobile-payment-toggle:not(.show) {
    animation: pulse 2s infinite;
}

@media (max-width: 991.98px) {
    .mobile-payment-toggle {
        display: flex !important;
        opacity: 1 !important;
        transform: translateY(0) !important;
    }
}

/* Animation pour l'apparition de la sidebar */
@keyframes slideUp {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}

@keyframes slideDown {
    from {
        transform: translateY(0);
    }
    to {
        transform: translateY(100%);
    }
}

.course-sidebar.slide-up {
    animation: slideUp 0.3s ease;
}

.course-sidebar.slide-down {
    animation: slideDown 0.3s ease;
}

.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
}

.rating i {
    font-size: 0.9em;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(0, 51, 102, 0.25);
}

.course-features ul li {
    padding: 0.25rem 0;
}

.share-buttons .btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush