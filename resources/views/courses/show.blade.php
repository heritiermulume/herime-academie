@extends('layouts.app')

@section('title', $course->title . ' - Herime Academie')
@section('description', $course->meta_description ?: Str::limit($course->description, 160))

@push('styles')
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
    background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
    min-height: 100vh;
    margin-top: 0;
    padding-top: 0;
}

/* Supprimer tout margin-top et padding-top du body sur cette page */
body:has(.course-details-page),
body.has-global-announcement:has(.course-details-page) {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

/* Ajouter un padding-top pour desktop pour compenser la hauteur de la navbar fixe (réduit) */
@media (min-width: 992px) {
    .course-details-page {
        padding-top: calc(var(--site-navbar-height, 64px) - 35px) !important;
    }
    
    /* Si une annonce globale existe, ajouter sa hauteur */
    body.has-global-announcement .course-details-page {
        padding-top: calc(var(--site-navbar-height, 64px) + var(--announcement-height, 0px) - 35px) !important;
    }
    
    /* Le premier élément (hero) doit commencer juste après le padding-top */
    .course-details-page > *:first-child,
    .course-details-page > section:first-child {
        margin-top: 0 !important;
    }
}

@media (max-width: 991.98px) {
    /* Ajouter un padding-top pour compenser la hauteur de la navbar fixe */
    .course-details-page {
        margin-top: 0 !important;
        padding-top: var(--site-navbar-height, 60px) !important;
    }
    
    /* Si une annonce globale existe, ajouter sa hauteur */
    body.has-global-announcement .course-details-page {
        padding-top: calc(var(--site-navbar-height, 60px) + var(--announcement-height, 0px)) !important;
    }
    
    /* Supprimer tout margin-top et padding-top du body sur mobile/tablette */
    body:has(.course-details-page) {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    /* Le premier élément (hero) doit commencer juste après le padding-top */
    .course-details-page > *:first-child,
    .course-details-page > section:first-child {
        margin-top: 0 !important;
    }
    
    /* Ajouter des paddings gauche/droite au conteneur d'en-tête */
    .course-hero .container {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
}

/* Hero Section */
.course-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
    color: white;
    padding: 2rem 0 3rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    margin-top: 0;
}

/* Supprimer tout espace avant le hero sur mobile/tablette */
@media (max-width: 991.98px) {
    .course-details-page > section.course-hero:first-child,
    .course-details-page > .course-hero:first-child,
    .course-details-page section:first-child,
    .course-details-page > *:first-child {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    /* Supprimer aussi le padding-top du container dans le hero */
    .course-hero .container:first-child {
        padding-top: 0 !important;
    }
}

@media (max-width: 991.98px) {
    .course-hero {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
}

.course-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
    opacity: 0.1;
}

.course-hero .container {
    position: relative;
    z-index: 1;
}

.btn-back {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.3);
    display: inline-flex;
    align-items: center;
}

.btn-back:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    transform: translateX(-3px);
    border-color: rgba(255, 255, 255, 0.5);
}

.breadcrumb-modern {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 0.625rem 1rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

.breadcrumb-modern .breadcrumb {
    margin: 0;
    padding: 0;
}

.breadcrumb-modern .breadcrumb-item {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
}

.breadcrumb-modern .breadcrumb-item a {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: all 0.3s ease;
}

.breadcrumb-modern .breadcrumb-item a:hover {
    color: var(--accent-color);
}

.breadcrumb-modern .breadcrumb-item.active {
    color: var(--accent-color);
    font-weight: 600;
}

.breadcrumb-modern .breadcrumb-item + .breadcrumb-item::before {
    color: rgba(255, 255, 255, 0.6);
    content: "›";
    padding: 0 0.5rem;
}

.course-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.course-badge {
    padding: 0.4rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    white-space: nowrap;
}

.course-badge.featured {
    background: linear-gradient(135deg, var(--accent-color) 0%, #e6b800 100%);
    color: var(--primary-color);
}

.course-badge.free {
    background: linear-gradient(135deg, var(--success-color) 0%, #20c997 100%);
    color: white;
}

.course-badge.category {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.course-badge.level {
    background: rgba(255, 255, 255, 0.15);
    color: white;
}

.course-title-hero {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.3;
    margin-bottom: 0.75rem;
    color: white;
    word-wrap: break-word;
}

.course-stats-hero {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 0;
}

.course-stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
    white-space: nowrap;
}

.course-stat-item i {
    color: var(--accent-color);
    font-size: 1rem;
    flex-shrink: 0;
}

.course-stat-item span {
    font-weight: 500;
}

/* Main Content */
.main-content {
    padding-bottom: 3rem;
}


.content-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.content-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.section-title-modern {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--accent-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title-modern i {
    color: var(--accent-color);
}

/* Video Preview */
.video-preview-wrapper {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.3s ease;
}

/* Réduire les paddings des cartes contenant l'image/vidéo sur mobile/tablette */
@media (max-width: 991.98px) {
    .content-card:has(.video-preview-wrapper),
    .content-card:has(img.img-fluid.rounded) {
        padding: 0.125rem !important;
    }
    
    /* Fallback pour les navigateurs qui ne supportent pas :has() */
    .content-card[style*="padding: 0.5rem"] {
        padding: 0.125rem !important;
    }
}

@media (max-width: 767.98px) {
    .content-card:has(.video-preview-wrapper),
    .content-card:has(img.img-fluid.rounded) {
        padding: 0.0625rem !important;
    }
    
    /* Fallback pour les navigateurs qui ne supportent pas :has() */
    .content-card[style*="padding: 0.5rem"] {
        padding: 0.0625rem !important;
    }
    
    /* Réduire aussi les marges de l'image elle-même */
    .video-preview-wrapper {
        margin: 0;
    }
    
    .content-card img.img-fluid.rounded {
        margin: 0;
    }
}

.video-preview-wrapper:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
}

.video-preview-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 51, 102, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
    transition: all 0.3s ease;
}

.video-preview-wrapper:hover .video-preview-overlay {
    background: rgba(0, 51, 102, 0.85);
}

.play-button-large {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    font-size: 1.5rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.video-preview-wrapper:hover .play-button-large {
    transform: scale(1.1);
    background: white;
}

/* Course Description */
.course-description {
    line-height: 1.7;
    color: var(--text-color);
    font-size: 0.9375rem;
}

.course-description p {
    margin-bottom: 1rem;
}

/* What You'll Learn */
.learning-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    margin-bottom: 0.75rem;
    background: var(--light-color);
    border-radius: 12px;
    border-left: 4px solid var(--success-color);
    transition: all 0.3s ease;
}

.learning-item:hover {
    background: #e9ecef;
    transform: translateX(4px);
}

.learning-item i {
    color: var(--success-color);
    font-size: 1rem;
    margin-top: 0.125rem;
    flex-shrink: 0;
}

/* Requirements */
.requirement-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    margin-bottom: 0.75rem;
    background: var(--light-color);
    border-radius: 12px;
    border-left: 4px solid var(--info-color);
}

.requirement-item i {
    color: var(--info-color);
    font-size: 1rem;
    margin-top: 0.125rem;
    flex-shrink: 0;
}

/* Curriculum */
.curriculum-section {
    margin-bottom: 1rem;
}

.curriculum-section-header {
    background: var(--primary-color);
    color: white;
    padding: 1.25rem;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.curriculum-section-header:hover {
    background: #004080;
}

.curriculum-section-header h5 {
    margin: 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.curriculum-section-header .fa-chevron-right {
    display: inline-block;
}

.curriculum-section-header .fa-chevron-down {
    display: none;
}

.curriculum-section-header[aria-expanded="true"] .fa-chevron-right {
    display: none !important;
}

.curriculum-section-header[aria-expanded="true"] .fa-chevron-down {
    display: inline-block !important;
}

.curriculum-section-header[aria-expanded="false"] .fa-chevron-down {
    display: none !important;
}

.curriculum-section-header[aria-expanded="false"] .fa-chevron-right {
    display: inline-block !important;
}

.curriculum-section-stats {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    opacity: 0.9;
}

.curriculum-lessons {
    background: white;
    border: 1px solid var(--border-color);
    border-top: none;
    border-radius: 0 0 12px 12px;
    padding: 0;
}

.curriculum-section-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
    opacity: 0;
    pointer-events: none;
}

.curriculum-section-content.is-open {
    max-height: 5000px;
    opacity: 1;
    transition: max-height 0.4s ease-in, opacity 0.3s ease-in;
    pointer-events: auto;
}

.lesson-item {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.lesson-item:last-child {
    border-bottom: none;
}

.lesson-item:hover {
    background: var(--light-color);
}

.lesson-item.locked {
    opacity: 0.6;
}

.lesson-item.preview-clickable {
    cursor: pointer !important;
    user-select: none;
}

.lesson-item.preview-clickable:hover {
    background: rgba(0, 51, 102, 0.05) !important;
    transform: translateX(2px);
}

.lesson-item.preview-clickable:active {
    background: rgba(0, 51, 102, 0.1) !important;
}

.lesson-item.preview-clickable * {
    pointer-events: none;
}

.preview-item {
    user-select: none;
}

.preview-item.active {
    background: rgba(0, 51, 102, 0.1) !important;
    border-color: #003366 !important;
}

.preview-player-wrapper {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.preview-player-wrapper:not(.active) {
    display: none !important;
}

/* Fixer la taille du conteneur vidéo pour qu'elle ne change pas */
#previewVideoContainer {
    position: relative !important;
    width: 100% !important;
}

#previewVideoContainer.ratio {
    aspect-ratio: 16 / 9 !important;
    height: auto !important;
}

/* Desktop: hauteur fixe minimale */
@media (min-width: 992px) {
    #previewVideoContainer.ratio {
        min-height: 450px !important;
        max-height: 600px !important;
    }
}


.modal-fixed-height .modal-content {
    max-height: 95vh;
    height: 95vh;
    display: flex;
    flex-direction: column;
}

.modal-fixed-height .modal-body {
    overflow: hidden;
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    height: calc(95vh - 73px);
    max-height: calc(95vh - 73px);
}

/* Styles pour le bouton de fermeture du modal - cacher les pseudo-éléments Bootstrap partout */
#coursePreviewModal .modal-header .btn-close::before,
#coursePreviewModal .modal-header .btn-close::after {
    display: none !important;
    content: none !important;
}

/* Forcer la couleur blanche pour le bouton de fermeture */
#coursePreviewModal .modal-header .btn-close,
#coursePreviewModal .modal-header .btn-close.btn-close-white {
    color: #ffffff !important;
    filter: brightness(0) invert(1) !important; /* Force blanc si nécessaire */
}

/* Afficher l'icône Font Awesome partout - FORCER BLANC */
#coursePreviewModal .modal-header .btn-close i,
#coursePreviewModal .modal-header .btn-close.btn-close-white i,
#coursePreviewModal .modal-header .btn-close i.fas,
#coursePreviewModal .modal-header .btn-close i.fa-times {
    display: block !important;
    color: #ffffff !important;
    line-height: 1 !important;
    margin: 0 !important;
    fill: #ffffff !important;
    stroke: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    text-fill-color: #ffffff !important;
}

/* Styles pour le bouton de fermeture du modal sur desktop */
@media (min-width: 992px) {
    #coursePreviewModal .modal-header .btn-close {
        width: 1em !important;
        height: 1em !important;
        padding: 0.25em 0.25em !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: none !important;
        background-image: none !important;
        color: #ffffff !important;
    }
    
    #coursePreviewModal .modal-header .btn-close i,
    #coursePreviewModal .modal-header .btn-close i.fas,
    #coursePreviewModal .modal-header .btn-close i.fa-times {
        font-size: 1.25rem !important;
        color: #ffffff !important;
    }
}

/* Styles pour le bouton de fermeture du modal sur mobile */
@media (max-width: 991.98px) {
    #coursePreviewModal .modal-header .btn-close {
        width: 28px !important;
        height: 28px !important;
        min-width: 28px !important;
        min-height: 28px !important;
        max-width: 28px !important;
        max-height: 28px !important;
        padding: 0 !important;
        margin: 0 !important;
        opacity: 1 !important;
        background: none !important;
        background-image: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 4px !important;
        transition: background-color 0.2s ease !important;
    }
    
    #coursePreviewModal .modal-header .btn-close:hover {
        background-color: rgba(255, 255, 255, 0.1) !important;
    }
    
    #coursePreviewModal .modal-header .btn-close i,
    #coursePreviewModal .modal-header .btn-close i.fas,
    #coursePreviewModal .modal-header .btn-close i.fa-times {
        font-size: 1rem !important;
        color: #ffffff !important;
        fill: #ffffff !important;
        stroke: #ffffff !important;
    }
}

@media (max-width: 575.98px) {
    #coursePreviewModal .modal-header .btn-close {
        width: 24px !important;
        height: 24px !important;
        min-width: 24px !important;
        min-height: 24px !important;
        max-width: 24px !important;
        max-height: 24px !important;
    }
    
    #coursePreviewModal .modal-header .btn-close i,
    #coursePreviewModal .modal-header .btn-close i.fas,
    #coursePreviewModal .modal-header .btn-close i.fa-times {
        font-size: 0.875rem !important;
        color: #ffffff !important;
        fill: #ffffff !important;
        stroke: #ffffff !important;
    }
}

/* Desktop: lecteur fixe, liste scrollable */
@media (min-width: 992px) {
.modal-fixed-height .modal-body {
        height: calc(95vh - 73px) !important;
        max-height: calc(95vh - 73px) !important;
        overflow: hidden !important;
        display: block !important;
    }
    
    .modal-fixed-height .modal-body .row {
        height: 100% !important;
        max-height: 100% !important;
        margin: 0 !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
    }
    
    /* Colonne du lecteur - fixe, sans scroll */
    .modal-fixed-height .modal-body .col-lg-8 {
        height: 100% !important;
        overflow: hidden !important;
        padding: 0 !important;
        flex: 0 0 66.666667% !important;
        max-width: 66.666667% !important;
        position: relative !important;
    }
    
    /* Conteneur du lecteur - fixe, sans scroll */
    .modal-fixed-height .modal-body .col-lg-8 > div {
        height: 100% !important;
        overflow: hidden !important;
        padding: 1rem !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 0 !important; /* Supprimé complètement pour remonter au maximum le texte */
    }
    
    /* Conteneur vidéo - taille fixe pour maintenir la même hauteur */
    .modal-fixed-height .modal-body .col-lg-8 #previewVideoContainer {
        flex: 0 0 auto !important;
        width: 100% !important;
        margin-bottom: 0 !important; /* Supprimé car le gap du parent gère déjà l'espacement */
        aspect-ratio: 16 / 9 !important; /* Forcer le ratio 16:9 - maintiendra la proportion */
        height: auto !important; /* La hauteur sera calculée automatiquement selon le ratio et la largeur */
        min-height: 450px !important; /* Hauteur minimale pour éviter qu'il devienne trop petit */
        max-height: 600px !important; /* Hauteur maximale pour éviter qu'il devienne trop grand */
        overflow: hidden !important; /* Éviter le débordement du contenu vidéo */
    }
    
    /* Forcer le ratio à maintenir la taille fixe - s'assure que le conteneur ne change pas de taille */
    .modal-fixed-height .modal-body .col-lg-8 #previewVideoContainer.ratio {
        aspect-ratio: 16 / 9 !important;
        height: auto !important;
        min-height: 450px !important;
        max-height: 600px !important;
        overflow: hidden !important;
        padding-bottom: 0 !important; /* Désactiver le padding-bottom du ratio Bootstrap qui pourrait créer des variations */
        position: relative !important; /* S'assurer que les enfants absolus sont positionnés correctement */
    }
    
    /* S'assurer que les lecteurs remplissent le conteneur sans changer sa taille */
    .modal-fixed-height .modal-body .col-lg-8 #previewVideoContainer.ratio .plyr-player-container,
    .modal-fixed-height .modal-body .col-lg-8 #previewVideoContainer.ratio .plyr-player-wrapper {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        overflow: hidden !important;
        max-width: 100% !important;
        max-height: 100% !important;
    }
    
    /* S'assurer que les vidéos s'adaptent au conteneur sans changer sa taille */
    .modal-fixed-height .modal-body .col-lg-8 #previewVideoContainer video,
    .modal-fixed-height .modal-body .col-lg-8 #previewVideoContainer .plyr__video-embed {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important; /* S'adapter au conteneur sans le déformer */
        max-width: 100% !important;
        max-height: 100% !important;
    }
    
    /* S'assurer que les wrappers de preview remplissent le conteneur */
    .modal-fixed-height .modal-body .col-lg-8 #previewVideoContainer .preview-player-wrapper {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        overflow: hidden !important;
        max-width: 100% !important;
        max-height: 100% !important;
    }
    
    /* Info de la leçon - hauteur flexible mais limitée, pas d'espace vide en haut */
    .modal-fixed-height .modal-body .col-lg-8 #previewLessonInfo {
        flex: 0 0 auto !important;
        flex-shrink: 0 !important;
        margin-top: 0.25rem !important; /* Espacement minimal pour séparer du lecteur */
        margin-bottom: 0 !important; /* Pas d'espace en bas */
        padding-top: 0.5rem !important; /* Padding supérieur minimal */
        padding-bottom: 0.75rem !important; /* Padding inférieur */
    }
    
    /* Colonne de la liste - flex column avec hauteur fixe */
    .modal-fixed-height .modal-body .col-lg-4 {
        height: 100% !important;
        max-height: 100% !important;
        overflow: hidden !important;
        padding: 0 !important;
        flex: 0 0 33.333333% !important;
        max-width: 33.333333% !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        min-height: 0 !important;
    }
    
    /* En-tête de la liste - hauteur fixe, pas de shrink */
    .modal-fixed-height .modal-body .col-lg-4 > div:first-child {
        flex: none !important;
        flex-shrink: 0 !important;
        flex-grow: 0 !important;
        height: auto !important;
        min-height: fit-content !important;
        max-height: fit-content !important;
    }
    
    /* Conteneur de la liste - scrollable, CRITIQUE: min-height: 0 permet le scroll dans flexbox */
    .modal-fixed-height .modal-body .col-lg-4 > div#previewListContainer,
    .modal-fixed-height .modal-body .col-lg-4 #previewListContainer {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        height: 100% !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        position: relative !important;
        -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 51, 102, 0.3) transparent;
        /* Force le scroll à apparaître si nécessaire */
        overscroll-behavior: contain;
    }
    
    /* Contenu de la liste - pas de contrainte de hauteur */
    .modal-fixed-height .modal-body .col-lg-4 #previewListContainer #previewListContent {
        /* Le contenu peut dépasser, déclenchant le scroll du parent */
        /* Padding géré par les classes Bootstrap (px-4 pb-4) */
    }
    
    /* Styles de la scrollbar de la liste */
    .modal-fixed-height .modal-body #previewListContainer::-webkit-scrollbar {
        width: 8px;
    }
    
    .modal-fixed-height .modal-body #previewListContainer::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .modal-fixed-height .modal-body #previewListContainer::-webkit-scrollbar-thumb {
        background-color: rgba(0, 51, 102, 0.3);
        border-radius: 4px;
    }
    
    .modal-fixed-height .modal-body #previewListContainer::-webkit-scrollbar-thumb:hover {
        background-color: rgba(0, 51, 102, 0.5);
    }
}

/* Mobile: tout scrollable, scroll automatique vers le lecteur */
@media (max-width: 991.98px) {
    .modal-fixed-height .modal-body {
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }
    
    .modal-fixed-height .modal-body #previewVideoContainer {
        scroll-margin-top: 20px;
        aspect-ratio: 16 / 9 !important;
        min-height: 250px !important;
        max-height: 50vh !important;
        height: auto !important;
        overflow: hidden !important;
    }
    
    .modal-fixed-height .modal-body #previewVideoContainer.ratio {
        padding-bottom: 0 !important;
    }
    
    /* Styles pour les contrôles Plyr dans le modal de preview sur mobile - bouton centré */
    .modal-fixed-height .modal-body #previewVideoContainer .plyr__control--overlaid,
    .modal-fixed-height .modal-body #previewVideoContainer .plyr__control.plyr__control--overlaid {
        border-radius: 50% !important;
        width: 70px !important;
        height: 70px !important;
        min-width: 70px !important;
        min-height: 70px !important;
        max-width: 70px !important;
        max-height: 70px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 !important;
        margin: 0 !important;
        aspect-ratio: 1 / 1 !important;
        flex-shrink: 0 !important;
        flex-grow: 0 !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important; /* Centre le bouton */
        z-index: 10 !important;
        scale: 1 !important;
    }
    
    /* Styles pour les contrôles Plyr dans le modal de preview sur mobile (très petit) */
    @media (max-width: 575.98px) {
        .modal-fixed-height .modal-body #previewVideoContainer .plyr__controls {
            padding: 5px 6px !important;
            font-size: 10px !important;
        }
        
        .modal-fixed-height .modal-body #previewVideoContainer .plyr__control {
            padding: 4px !important;
            min-width: 24px !important;
            width: 24px !important;
            height: 24px !important;
        }
        
        .modal-fixed-height .modal-body #previewVideoContainer .plyr__control svg,
        .modal-fixed-height .modal-body #previewVideoContainer .plyr__control .plyr__icon {
            width: 12px !important;
            height: 12px !important;
            font-size: 12px !important;
        }
        
        /* Bouton play central - parfaitement rond et centré dans le modal de preview sur très petit mobile */
        .modal-fixed-height .modal-body #previewVideoContainer .plyr__control--overlaid,
        .modal-fixed-height .modal-body #previewVideoContainer .plyr__control.plyr__control--overlaid {
            width: 50px !important;
            height: 50px !important;
            min-width: 50px !important;
            min-height: 50px !important;
            max-width: 50px !important;
            max-height: 50px !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important; /* Centre le bouton */
            z-index: 10 !important;
        }
        
        .modal-fixed-height .modal-body #previewVideoContainer .plyr__time {
            font-size: 9px !important;
            padding: 0 2px !important;
        }
        
        .modal-fixed-height .modal-body #previewVideoContainer .plyr__tooltip {
            font-size: 8px !important;
            padding: 2px 4px !important;
        }
        
        /* Options de vitesse - taille ultra réduite dans le modal de preview sur très petit mobile */
        .modal-fixed-height .modal-body #previewVideoContainer .plyr__menu__container {
            font-size: 0.45rem !important;
            min-width: 60px !important;
            max-width: 85px !important;
            padding: 0.1rem 0 !important;
        }
        
        .modal-fixed-height .modal-body #previewVideoContainer .plyr__menu__container .plyr__control {
            padding: 0.15rem 0.3rem !important;
            font-size: 0.45rem !important;
            min-height: 20px !important;
        }
        
        .modal-fixed-height .modal-body #previewVideoContainer .plyr__menu__container .plyr__control span {
            font-size: 0.35rem !important;
            transform: scale(0.8) !important;
            letter-spacing: -0.5px !important;
            padding: 0 !important;
            margin: 0 !important;
        }
    }
    
    /* Styles pour les options de vitesse dans le modal de preview sur mobile */
    .modal-fixed-height .modal-body #previewVideoContainer .plyr__menu__container {
        font-size: 0.5rem !important;
        min-width: 70px !important;
        max-width: 95px !important;
        padding: 0.1rem 0 !important;
    }
    
    .modal-fixed-height .modal-body #previewVideoContainer .plyr__menu__container .plyr__control {
        padding: 0.2rem 0.35rem !important;
        font-size: 0.5rem !important;
        min-height: 22px !important;
    }
    
    .modal-fixed-height .modal-body #previewVideoContainer .plyr__menu__container .plyr__control span {
        font-size: 0.4rem !important;
        transform: scale(0.85) !important;
        letter-spacing: -0.3px !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    /* Réduire l'espacement entre le titre et la liste des previews sur mobile */
    .modal-fixed-height .modal-body .col-lg-4 > div:first-child {
        padding: 1rem 1rem 0.25rem 1rem !important;
    }
    
    .modal-fixed-height .modal-body .col-lg-4 > div:first-child h6 {
        margin-bottom: 0.5rem !important;
    }
    
    .modal-fixed-height .modal-body #previewListContent {
        padding-top: 0.5rem !important;
    }
    
    /* Réduire la marge du premier élément de la liste */
    .modal-fixed-height .modal-body #previewListContent > .preview-item:first-child {
        margin-top: 0 !important;
    }
}

.modal-fixed-height .modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-fixed-height .modal-body::-webkit-scrollbar-track {
    background: transparent;
}

.modal-fixed-height .modal-body::-webkit-scrollbar-thumb {
    background-color: rgba(0, 51, 102, 0.3);
    border-radius: 4px;
}

.modal-fixed-height .modal-body::-webkit-scrollbar-thumb:hover {
    background-color: rgba(0, 51, 102, 0.5);
}

.preview-list-scrollable {
    max-height: none;
}

.lesson-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.lesson-icon.video {
    background: rgba(220, 53, 69, 0.1);
    color: var(--danger-color);
}

.lesson-icon.text {
    background: rgba(23, 162, 184, 0.1);
    color: var(--info-color);
}

.lesson-icon.pdf {
    background: rgba(220, 53, 69, 0.1);
    color: var(--danger-color);
}

.lesson-icon.quiz {
    background: rgba(255, 193, 7, 0.1);
    color: var(--warning-color);
}

.lesson-content {
    flex: 1;
}

.lesson-title {
    font-weight: 500;
    color: var(--text-color);
    margin-bottom: 0.25rem;
}

.lesson-meta {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.lesson-preview-badge {
    background: var(--accent-color);
    color: var(--primary-color);
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Instructor Card */
.instructor-card {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}

.instructor-avatar {
    width: 100px !important;
    height: 100px !important;
    border-radius: 50% !important;
    overflow: hidden !important;
    flex-shrink: 0;
    display: block;
    aspect-ratio: 1 / 1 !important;
}

.instructor-avatar img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
    border: none !important;
    box-shadow: none !important;
    transform: none !important;
    border-radius: 0 !important;
}

.instructor-info h5 {
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.instructor-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-top: 0.75rem;
    font-size: 0.875rem;
    color: var(--text-muted);
}

/* Reviews */
.rating-summary {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.rating-score {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
}

.rating-stars {
    color: var(--warning-color);
    font-size: 1.125rem;
    margin-bottom: 0.5rem;
}

.rating-count {
    color: var(--text-muted);
    font-size: 0.875rem;
}

.rating-distribution {
    margin-bottom: 2rem;
}

.rating-bar-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.rating-bar-label {
    min-width: 80px;
    font-size: 0.875rem;
    color: var(--text-color);
}

.rating-bar {
    flex: 1;
    height: 8px;
    background: var(--border-color);
    border-radius: 4px;
    overflow: hidden;
}

.rating-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--warning-color) 0%, #ffb300 100%);
    border-radius: 4px;
    transition: width 0.5s ease;
}

.rating-bar-count {
    min-width: 30px;
    text-align: right;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.review-card {
    background: var(--light-color);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    border: 1px solid var(--border-color);
}

.review-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.review-avatar {
    width: 50px !important;
    height: 50px !important;
    border-radius: 50% !important;
    overflow: hidden !important;
    flex-shrink: 0;
    display: block;
    aspect-ratio: 1 / 1 !important;
}

.review-avatar img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
    border: none !important;
    box-shadow: none !important;
    transform: none !important;
    border-radius: 0 !important;
}

.review-author {
    flex: 1;
}

.review-author-name {
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.25rem;
}

.review-date {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.review-comment {
    color: var(--text-color);
    line-height: 1.6;
}

/* Preview Reviews Horizontal */
.reviews-preview-horizontal {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    padding-bottom: 0.5rem;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
}

.reviews-preview-horizontal::-webkit-scrollbar {
    height: 6px;
}

.reviews-preview-horizontal::-webkit-scrollbar-track {
    background: var(--light-color, #f8f9fa);
    border-radius: 3px;
}

.reviews-preview-horizontal::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}

.reviews-preview-horizontal::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.3);
}

.reviews-preview-container {
    display: flex;
    gap: 1rem;
    padding-bottom: 0.5rem;
}

.review-card-preview {
    min-width: 280px;
    max-width: 320px;
    background: var(--light-color, #f8f9fa);
    border-radius: 10px;
    padding: 1.25rem;
    border: 1px solid var(--border-color, #e0e0e0);
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.review-card-preview:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.review-card-preview .review-header {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.review-card-preview .review-avatar {
    width: 45px !important;
    height: 45px !important;
    border-radius: 50% !important;
    overflow: hidden !important;
    flex-shrink: 0;
    display: block;
    aspect-ratio: 1 / 1 !important;
}

.review-card-preview .review-avatar img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
}

.review-card-preview .review-author {
    flex: 1;
    min-width: 0;
}

.review-card-preview .review-author-name {
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.review-card-preview .review-date {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.review-card-preview .review-comment-preview {
    color: var(--text-color);
    line-height: 1.5;
    font-size: 0.85rem;
    flex: 1;
}

/* Horizontal Reviews Scroll */
.reviews-horizontal-scroll {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    padding-bottom: 1rem;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
}

.reviews-horizontal-scroll::-webkit-scrollbar {
    height: 8px;
}

.reviews-horizontal-scroll::-webkit-scrollbar-track {
    background: var(--light-color, #f8f9fa);
    border-radius: 4px;
}

.reviews-horizontal-scroll::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 4px;
}

.reviews-horizontal-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.3);
}

.reviews-container {
    display: flex;
    gap: 1.5rem;
    padding-bottom: 0.5rem;
}

.review-card-horizontal {
    min-width: 320px;
    max-width: 380px;
    background: var(--light-color, #f8f9fa);
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid var(--border-color, #e0e0e0);
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
}

.review-card-horizontal .review-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
}

.review-card-horizontal .review-avatar {
    width: 50px !important;
    height: 50px !important;
    border-radius: 50% !important;
    overflow: hidden !important;
    flex-shrink: 0;
    display: block;
    aspect-ratio: 1 / 1 !important;
}

.review-card-horizontal .review-avatar img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
}

.review-card-horizontal .review-author {
    flex: 1;
    min-width: 0;
}

.review-card-horizontal .review-author-name {
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.25rem;
    font-size: 0.95rem;
}

.review-card-horizontal .review-date {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.review-card-horizontal .review-comment {
    color: var(--text-color);
    line-height: 1.6;
    font-size: 0.9rem;
    flex: 1;
}

/* Rating Input Styles */
.rating-input-wrapper {
    margin-bottom: 1rem;
}

.rating-stars-input {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.rating-star {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-right: 0.5rem;
}

.rating-star:hover,
.rating-star.active {
    color: var(--warning-color, #ffc107);
    transform: scale(1.1);
}

.rating-star.active {
    color: var(--warning-color, #ffc107);
}

.rating-value-text {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-top: 0.5rem;
}

/* Sidebar */
.course-sidebar {
    position: sticky;
    top: 2rem;
}

.sidebar-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    border: 1px solid var(--border-color);
}

.price-display {
    text-align: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.price-free {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--success-color);
}

.price-current {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1.2;
    margin-bottom: 0.375rem;
}

.price-original {
    font-size: 1rem;
    color: var(--text-muted);
    text-decoration: line-through;
    margin-bottom: 0.5rem;
}

.price-discount {
    background: var(--danger-color);
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

/* Promotion Countdown Styles */
.promotion-countdown {
    background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 0.625rem 0.75rem;
    text-align: center;
    width: 100%;
}

.countdown-label {
    font-size: 0.6875rem;
    margin-bottom: 0.375rem;
    display: block;
}

.countdown-text {
    font-size: 0.8125rem;
    letter-spacing: 0.3px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.countdown-text span {
    display: inline-block;
    min-width: fit-content;
    white-space: nowrap;
}


.course-features-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.course-features-list li {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.625rem 0;
    border-bottom: 1px solid var(--border-color);
    font-size: 0.875rem;
}

.course-features-list li:last-child {
    border-bottom: none;
}

.course-features-list i {
    color: var(--primary-color);
    font-size: 0.9375rem;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
}

.share-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: center;
}

.share-btn {
    flex: 1;
    min-width: 50px;
    max-width: 60px;
    padding: 0.625rem;
    border-radius: 10px;
    border: 1px solid var(--border-color);
    background: white;
    color: var(--primary-color);
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.share-btn:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    transform: translateY(-2px);
}

.share-btn i {
    font-size: 1rem;
}

/* Related Courses */
.related-course-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid var(--border-color);
    height: 100%;
}

.related-course-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.related-course-card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.related-course-card-body {
    padding: 1.25rem;
}

.related-course-title {
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.75rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 3rem;
}

.related-course-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-color);
}

/* Mobile Payment Button */
/* Modern Mobile Price Slider */
.mobile-price-slider {
    position: fixed;
    bottom: 60px;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    padding: 0.5rem 0.75rem;
    z-index: 999;
    box-shadow: 0 -2px 20px rgba(0, 0, 0, 0.1);
    display: none;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.mobile-price-slider__content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    max-width: 100%;
}

.mobile-price-slider__price {
    flex: 0 0 auto;
    min-width: 0;
    margin-right: auto;
}

.mobile-price-slider__label {
    font-size: 0.7rem;
    color: #6c757d;
    margin-bottom: 0.2rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.mobile-price-slider__value {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-color, #003366);
    line-height: 1.2;
}

.mobile-price-slider__prices {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.mobile-price-slider__current {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-color, #003366);
    line-height: 1.2;
}

.mobile-price-slider__original {
    font-size: 0.85rem;
    color: #6c757d;
    text-decoration: line-through;
    line-height: 1.2;
}

.mobile-price-slider__badge {
    display: inline-block;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.2rem 0.45rem;
    border-radius: 12px;
    line-height: 1.2;
}

.mobile-price-slider__countdown {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 0.3rem;
    font-size: 0.7rem;
    color: #dc3545;
    font-weight: 600;
}

.mobile-price-slider__countdown i {
    font-size: 0.75rem;
}

.mobile-price-slider__countdown .countdown-text {
    font-size: 0.7rem;
}

.mobile-price-slider__actions {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-left: auto;
}

.mobile-price-slider__form {
    display: inline-flex;
    flex: 0 0 auto;
}

.mobile-price-slider__btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.2rem !important;
    width: 85px !important;
    height: 42px !important;
    padding: 0 !important;
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    border: none;
    border-radius: 10px !important;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap !important;
    text-decoration: none !important;
    flex: 0 0 auto !important;
    box-sizing: border-box !important;
}

.mobile-price-slider__btn i {
    font-size: 0.8rem !important;
    flex-shrink: 0 !important;
}

/* Boutons avec texte long (Se connecter, Télécharger) - Priorité maximale */
.mobile-price-slider__actions .mobile-price-slider__btn--download,
.mobile-price-slider__actions a.mobile-price-slider__btn--download,
.mobile-price-slider__actions button.mobile-price-slider__btn--download,
.mobile-price-slider__form .mobile-price-slider__btn--download,
.mobile-price-slider__btn--login,
.mobile-price-slider__btn--download {
    width: 110px !important;
    min-width: 110px !important;
    max-width: 110px !important;
    height: 42px !important;
    font-size: 0.8rem !important;
}

.mobile-price-slider__actions .mobile-price-slider__btn--download i,
.mobile-price-slider__actions a.mobile-price-slider__btn--download i,
.mobile-price-slider__actions button.mobile-price-slider__btn--download i,
.mobile-price-slider__form .mobile-price-slider__btn--download i,
.mobile-price-slider__btn--login i,
.mobile-price-slider__btn--download i {
    font-size: 0.8rem !important;
}

/* Boutons avec texte moyen (S'inscrire, Commencer, Continuer) */
.mobile-price-slider__btn--medium {
    width: 100px !important;
    min-width: 100px !important;
    height: 42px !important;
    font-size: 0.8rem !important;
}

.mobile-price-slider__btn--medium i {
    font-size: 0.8rem !important;
}

.mobile-price-slider__btn--primary {
    background: linear-gradient(135deg, var(--primary-color, #003366) 0%, #004080 100%);
    color: white !important;
    box-shadow: 0 2px 8px rgba(0, 51, 102, 0.3);
    height: 42px !important;
    font-size: 0.8rem !important;
}

.mobile-price-slider__btn--primary:hover,
.mobile-price-slider__btn--primary:active {
    background: linear-gradient(135deg, #004080 0%, #003366 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 51, 102, 0.4);
    color: white !important;
}

.mobile-price-slider__btn--primary i {
    font-size: 0.8rem !important;
}

/* Force spécifique pour le bouton Télécharger */
a.mobile-price-slider__btn--download,
button.mobile-price-slider__btn--download,
.mobile-price-slider__btn.mobile-price-slider__btn--download,
.mobile-price-slider__btn--primary.mobile-price-slider__btn--download {
    width: 110px !important;
    min-width: 110px !important;
    height: 42px !important;
    font-size: 0.8rem !important;
}

a.mobile-price-slider__btn--download i,
button.mobile-price-slider__btn--download i,
.mobile-price-slider__btn.mobile-price-slider__btn--download i,
.mobile-price-slider__btn--primary.mobile-price-slider__btn--download i {
    font-size: 0.8rem !important;
}

.mobile-price-slider__btn--success {
    background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    color: white !important;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    height: 42px !important;
    font-size: 0.8rem !important;
}

.mobile-price-slider__btn--success:hover,
.mobile-price-slider__btn--success:active {
    background: linear-gradient(135deg, #218838 0%, #28a745 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
    color: white !important;
}

.mobile-price-slider__btn--success i {
    font-size: 0.8rem !important;
}

.mobile-price-slider__btn--outline {
    background: white;
    color: var(--primary-color, #003366) !important;
    border: 1.5px solid var(--primary-color, #003366);
    height: 42px !important;
    font-size: 0.8rem !important;
}

.mobile-price-slider__btn--outline:hover,
.mobile-price-slider__btn--outline:active {
    background: var(--primary-color, #003366);
    color: white !important;
    transform: translateY(-1px);
}

.mobile-price-slider__btn--outline i {
    font-size: 0.8rem !important;
}

.mobile-price-slider__btn-group {
    display: flex;
    flex-direction: row;
    gap: 0.2rem;
    align-items: center;
    justify-content: flex-end;
}

.mobile-price-slider__btn-group .mobile-price-slider__btn {
    flex: 0 0 auto !important;
    width: 85px !important;
    height: 42px !important;
    padding: 0 !important;
    font-size: 0.8rem !important;
    white-space: nowrap !important;
    gap: 0.2rem !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.mobile-price-slider__btn-group .mobile-price-slider__btn i {
    font-size: 0.8rem !important;
}

/* Responsive Design */
@media (max-width: 991.98px) {
    .mobile-price-slider {
        display: flex !important;
        flex-direction: column !important;
        padding: 0.5rem 0.75rem !important;
    }
    
    /* Forcer l'alignement des boutons à droite - RÈGLES ULTRA PRIORITAIRES */
    .mobile-price-slider__content {
        display: flex !important;
        flex-direction: row !important;
        justify-content: flex-start !important;
        align-items: center !important;
        width: 100% !important;
        gap: 0.5rem !important;
    }
    
    .mobile-price-slider__price {
        flex: 0 0 auto !important;
        margin-right: 0 !important;
        margin-left: 0 !important;
        order: 1 !important;
    }
    
    .mobile-price-slider__actions {
        flex: 1 1 auto !important;
        margin-left: auto !important;
        margin-right: 0 !important;
        order: 2 !important;
        display: flex !important;
        justify-content: flex-end !important;
        align-items: center !important;
    }
    
    .mobile-price-slider__form {
        margin-left: auto !important;
        display: flex !important;
        justify-content: flex-end !important;
    }
    
    .mobile-price-slider__btn-group {
        display: flex !important;
        flex-direction: row !important;
        justify-content: flex-end !important;
        margin-left: auto !important;
        gap: 0.2rem !important;
    }
    
    /* FORCER LES TAILLES DE TEXTE DES PRIX */
    .mobile-price-slider__label {
        font-size: 0.7rem !important;
        margin-bottom: 0.2rem !important;
    }
    
    .mobile-price-slider__value {
        font-size: 1.3rem !important;
        font-weight: 700 !important;
    }
    
    .mobile-price-slider__current {
        font-size: 1.3rem !important;
        font-weight: 700 !important;
    }
    
    .mobile-price-slider__original {
        font-size: 0.85rem !important;
    }
    
    .mobile-price-slider__badge {
        font-size: 0.75rem !important;
        padding: 0.2rem 0.45rem !important;
    }
    
    .mobile-price-slider__countdown {
        font-size: 0.7rem !important;
        gap: 0.35rem !important;
        margin-top: 0.3rem !important;
    }
    
    .mobile-price-slider__countdown i {
        font-size: 0.75rem !important;
    }
    
    .mobile-price-slider__countdown .countdown-text {
        font-size: 0.7rem !important;
    }
    
    .main-content {
        padding-bottom: 140px; /* Espace pour le bouton de paiement (80px) + navigation mobile (60px) */
    }
    
    /* Réduire les marges latérales du container */
    .main-content.container {
        padding-left: 0.25rem;
        padding-right: 0.25rem;
    }
    
    /* Réduire les espacements des colonnes */
    .main-content .row {
        margin-left: -0.125rem;
        margin-right: -0.125rem;
    }
    
    .main-content .row > [class*="col-"] {
        padding-left: 0.125rem;
        padding-right: 0.125rem;
    }
    
    /* Réduire les paddings et margins sur tablette */
    .course-hero {
        padding: 0.125rem 0 0.75rem;
        margin-bottom: 0.375rem;
        margin-top: 0 !important;
    }
    
    /* Ajouter des paddings gauche/droite au conteneur d'en-tête sur tablette */
    .course-hero .container {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
        padding-top: 0 !important;
    }
    
    .breadcrumb-modern {
        margin-top: 0 !important;
        padding-top: 0.25rem;
        padding-bottom: 0.25rem;
    }
    
    .course-title-hero {
        font-size: 1.25rem;
        line-height: 1.3;
        margin-bottom: 0.3125rem;
    }
    
    .course-stats-hero {
        gap: 0.375rem;
    }
    
    .course-stat-item {
        font-size: 0.8125rem;
    }
    
    .course-stat-item i {
        font-size: 0.875rem;
    }
    
    .breadcrumb-modern {
        padding: 0.25rem 0.375rem;
        margin-bottom: 0.375rem;
        margin-top: 0.25rem;
        font-size: 0.75rem;
    }
    
    .course-badges {
        gap: 0.2rem;
        margin-bottom: 0.4375rem;
    }
    
    .course-badge {
        padding: 0.175rem 0.375rem;
        font-size: 0.7rem;
    }
    
    .content-card {
        padding: 0.375rem;
        margin-bottom: 0.1875rem;
    }
    
    /* Réduire fortement les paddings des cartes contenant l'image/vidéo */
    .content-card:has(.video-preview-wrapper),
    .content-card:has(img.img-fluid.rounded) {
        padding: 0.125rem !important;
    }
    
    /* Fallback pour les navigateurs qui ne supportent pas :has() */
    .content-card[style*="padding: 0.5rem"] {
        padding: 0.125rem !important;
    }
    
    .section-title-modern {
        font-size: 1rem;
        margin-bottom: 0.375rem;
        padding-bottom: 0.25rem;
    }
    
    .mobile-price-slider {
        display: flex !important;
    }
    
    .course-sidebar {
        position: relative;
        top: 0;
    }
    
    .sidebar-card {
        margin-bottom: 0.1875rem;
        padding: 0.3125rem;
    }
    
    .sidebar-card .btn,
    .sidebar-card .btn-lg,
    .sidebar-card .btn-sm {
        padding: 0.45rem 0.75rem;
        font-size: 0.8rem;
        width: 100%;
    }
    
    .sidebar-card .d-grid {
        gap: 0.25rem !important;
    }
    
    .sidebar-card .d-grid .btn {
        padding: 0.45rem 0.65rem;
        font-size: 0.8rem;
    }
    
    .price-display {
        padding-bottom: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .price-current {
        font-size: 1.75rem;
    }
    
    .price-original {
        font-size: 0.9rem;
    }
    
    .price-discount {
        padding: 0.35rem 0.65rem;
        font-size: 0.7rem;
    }
    
    .instructor-card {
        flex-direction: column;
        text-align: center;
    }
    
    .rating-summary {
        flex-direction: column;
        text-align: center;
    }
    
    /* Réduire les espacements des grilles */
    .row.g-4 {
        --bs-gutter-x: 0.1875rem;
        --bs-gutter-y: 0.1875rem;
    }
    
    .row.g-3 {
        --bs-gutter-x: 0.125rem;
        --bs-gutter-y: 0.125rem;
    }
    
    /* Réduire les paddings des éléments de liste */
    .learning-item,
    .requirement-item {
        padding: 0.3125rem;
        margin-bottom: 0.09375rem;
    }
    
    .review-card {
        padding: 0.4375rem;
        margin-bottom: 0.1875rem;
    }
    
    .curriculum-section-header {
        padding: 0.4375rem;
    }
    
    .lesson-item {
        padding: 0.3125rem 0.4375rem;
    }
    
    /* Réduire les paddings des cartes de cours liés */
    .related-course-card-body {
        padding: 0.5rem;
    }
    
    .related-course-title {
        font-size: 0.95rem;
        margin-bottom: 0.3125rem;
    }
    
    .related-course-price {
        font-size: 1.125rem;
    }
    
    /* Réduire les paddings des listes de fonctionnalités */
    .course-features-list li {
        padding: 0.25rem 0;
        font-size: 0.8125rem;
    }
    
    .course-features-list i {
        font-size: 0.875rem;
    }
}

@media (max-width: 767.98px) {
    /* Ajouter un padding-top pour compenser la hauteur de la navbar fixe */
    .course-details-page {
        padding-top: var(--site-navbar-height, 60px) !important;
    }
    
    body.has-global-announcement .course-details-page {
        padding-top: calc(var(--site-navbar-height, 60px) + var(--announcement-height, 0px)) !important;
    }
    
    /* Réduire encore plus les marges latérales du container */
    .main-content.container {
        padding-left: 0.1875rem;
        padding-right: 0.1875rem;
    }
    
    /* Réduire encore plus les espacements des colonnes */
    .main-content .row {
        margin-left: -0.09375rem;
        margin-right: -0.09375rem;
    }
    
    .main-content .row > [class*="col-"] {
        padding-left: 0.09375rem;
        padding-right: 0.09375rem;
    }
    
    /* Réduire encore plus les paddings et margins sur mobile */
    .course-hero {
        padding: 0.0625rem 0 0.625rem;
        margin-bottom: 0.3125rem;
        margin-top: 0 !important;
    }
    
    /* Ajouter des paddings gauche/droite au conteneur d'en-tête sur mobile */
    .course-hero .container {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
        padding-top: 0 !important;
    }
    
    .breadcrumb-modern {
        margin-top: 0 !important;
        padding-top: 0.1875rem;
        padding-bottom: 0.1875rem;
    }
    
    .course-title-hero {
        font-size: 1.25rem;
        margin-bottom: 0.25rem;
    }
    
    .content-card {
        padding: 0.3125rem;
        margin-bottom: 0.125rem;
    }
    
    /* Réduire fortement les paddings des cartes contenant l'image/vidéo */
    .content-card:has(.video-preview-wrapper),
    .content-card:has(img.img-fluid.rounded) {
        padding: 0.0625rem !important;
    }
    
    /* Fallback pour les navigateurs qui ne supportent pas :has() */
    .content-card[style*="padding: 0.5rem"] {
        padding: 0.0625rem !important;
    }
    
    .section-title-modern {
        font-size: 0.95rem;
        margin-bottom: 0.3125rem;
        padding-bottom: 0.1875rem;
    }
    
    .section-title-modern i {
        font-size: 0.9rem;
    }
    
    .video-preview-wrapper {
        margin-bottom: 0;
        margin: 0;
    }
    
    .content-card img.img-fluid.rounded {
        margin: 0;
    }
    
    .course-stats-hero {
        gap: 0.1875rem;
        flex-direction: row;
        justify-content: flex-start;
    }
    
    .course-stat-item {
        font-size: 0.75rem;
    }
    
    .course-stat-item i {
        font-size: 0.8rem;
    }
    
    .breadcrumb-modern {
        padding: 0.1875rem 0.25rem;
        margin-bottom: 0.25rem;
        margin-top: 0.1875rem;
        font-size: 0.75rem;
    }
    
    .breadcrumb-modern .breadcrumb-item {
        font-size: 0.75rem;
    }
    
    .course-badge {
        padding: 0.125rem 0.25rem;
        font-size: 0.65rem;
    }
    
    .share-buttons {
        justify-content: center;
    }
    
    .share-btn {
        flex: 0 0 auto;
    }
    
    
    
    .promotion-countdown {
        padding: 0.5rem 0.625rem;
    }
    
    .countdown-label {
        font-size: 0.7rem;
    }
    
    .countdown-text {
        font-size: 0.85rem;
        flex-wrap: wrap;
    }
    
    .sidebar-card {
        padding: 0.3125rem;
        margin-bottom: 0.125rem;
    }
    
    .sidebar-card .btn,
    .sidebar-card .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        width: 100%;
    }
    
    .sidebar-card .btn-sm {
        padding: 0.4rem 0.75rem;
        font-size: 0.8rem;
    }
    
    .sidebar-card .d-grid {
        gap: 0.1875rem !important;
    }
    
    .sidebar-card .d-grid .btn {
        padding: 0.4rem 0.6rem;
        font-size: 0.8rem;
    }
    
    .price-display {
        padding-bottom: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .price-current {
        font-size: 1.75rem;
    }
    
    .price-original {
        font-size: 0.9rem;
    }
    
    .price-discount {
        padding: 0.35rem 0.65rem;
        font-size: 0.7rem;
    }
    
    /* Réduire encore plus les espacements des grilles */
    .row.g-4 {
        --bs-gutter-x: 0.125rem;
        --bs-gutter-y: 0.125rem;
    }
    
    .row.g-3 {
        --bs-gutter-x: 0.09375rem;
        --bs-gutter-y: 0.09375rem;
    }
    
    /* Réduire les paddings des éléments de liste */
    .learning-item,
    .requirement-item {
        padding: 0.25rem;
        margin-bottom: 0.09375rem;
    }
    
    .learning-item i,
    .requirement-item i {
        font-size: 0.9rem;
    }
    
    .review-card {
        padding: 0.375rem;
        margin-bottom: 0.1875rem;
    }
    
    .review-header {
        margin-bottom: 0.1875rem;
    }
    
    .curriculum-section-header {
        padding: 0.375rem;
    }
    
    .curriculum-section-header h5 {
        font-size: 0.9rem;
    }
    
    .lesson-item {
        padding: 0.25rem 0.375rem;
    }
    
    .lesson-icon {
        width: 32px;
        height: 32px;
        font-size: 0.875rem;
    }
    
    .instructor-card {
        gap: 1rem;
    }
    
    .instructor-avatar {
        width: 80px !important;
        height: 80px !important;
    }
    
    .instructor-stats {
        gap: 1rem;
        font-size: 0.8125rem;
    }
    
    .rating-summary {
        gap: 0.75rem;
    }
    
    .rating-score {
        font-size: 1.75rem;
    }
    
    .rating-stars {
        font-size: 1rem;
    }
    
    .rating-bar-item {
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }
    
    .rating-bar-label {
        min-width: 70px;
        font-size: 0.8125rem;
    }
    
    .rating-bar-count {
        min-width: 25px;
        font-size: 0.8125rem;
    }
    
    /* Réduire les paddings des cartes de cours liés */
    .related-course-card-body {
        padding: 0.5rem;
    }
    
    .related-course-title {
        font-size: 0.9rem;
        margin-bottom: 0.3125rem;
    }
    
    .related-course-price {
        font-size: 1.125rem;
    }
    
    /* Réduire les paddings des listes de fonctionnalités */
    .course-features-list li {
        padding: 0.25rem 0;
        font-size: 0.8125rem;
    }
    
    .course-features-list i {
        font-size: 0.875rem;
        width: 18px;
    }
    
    /* Styles responsives pour les cartes d'avis sur tablettes */
    .content-card .d-flex.justify-content-between.align-items-center {
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    
    .content-card .d-flex.justify-content-between.align-items-center h3 {
        font-size: 1.125rem !important;
    }
    
    /* Styles responsives pour la section "Tous les avis des étudiants" sur tablettes */
    #all-reviews .d-flex.justify-content-between.align-items-center {
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    /* Styles pour les boutons d'avis sur tablettes - masquer le texte, garder les icônes */
    #courseReviewForm .d-flex.gap-2 .btn {
        min-width: auto;
        padding: 0.5rem 0.75rem;
        font-size: 0;
        line-height: 1;
        position: relative;
        text-indent: -9999px;
        overflow: hidden;
        white-space: nowrap;
    }
    
    #courseReviewForm .d-flex.gap-2 .btn i {
        margin-right: 0 !important;
        margin-left: 0 !important;
        font-size: 1rem;
        display: inline-block;
        line-height: 1;
        text-indent: 0;
    }
    
    #courseReviewForm .d-flex.gap-2 .btn:not(:only-child) {
        flex: 0 0 auto;
    }
    
    .review-card-preview {
        min-width: 250px !important;
        max-width: 100% !important;
        padding: 1.125rem !important;
    }
    
    .review-card-horizontal {
        min-width: 280px !important;
        max-width: 100% !important;
        padding: 1.25rem !important;
    }
    
    .reviews-preview-container {
        gap: 0.875rem;
    }
    
    .reviews-container {
        gap: 1rem;
    }
}

@media (max-width: 575.98px) {
    .course-hero {
        padding: 0.5rem 0 1.25rem;
        margin-bottom: 1rem;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    .course-title-hero {
        font-size: 1.125rem;
        line-height: 1.3;
        margin-bottom: 0.5rem;
    }
    
    .content-card {
        padding: 0.875rem;
        border-radius: 12px;
        margin-bottom: 1rem;
    }
    
    .section-title-modern {
        font-size: 0.9375rem;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
    }
    
    .course-description {
        font-size: 0.875rem;
    }
    
    .video-preview-wrapper {
        margin-bottom: 0;
    }
    
    .play-button-large {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .section-title-modern i {
        font-size: 1rem;
    }
    
    .course-stats-hero {
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .course-stat-item {
        font-size: 0.7rem;
        gap: 0.375rem;
    }
    
    .course-stat-item i {
        font-size: 0.75rem;
    }
    
    .breadcrumb-modern {
        padding: 0.45rem 0.5rem;
        margin-bottom: 0.625rem;
        margin-top: 0.5rem;
        font-size: 0.65rem;
    }
    
    .breadcrumb-modern .breadcrumb-item {
        font-size: 0.7rem;
    }
    
    .breadcrumb-modern .breadcrumb-item + .breadcrumb-item::before {
        padding: 0 0.375rem;
    }
    
    .course-badges {
        gap: 0.35rem;
        margin-bottom: 0.625rem;
    }
    
    .course-badge {
        padding: 0.25rem 0.5rem;
        font-size: 0.625rem;
    }
    
    .play-button-large {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .instructor-avatar {
        width: 80px;
        height: 80px;
    }
    
    .mobile-price-slider {
        padding: 0.4rem 0.625rem;
        bottom: 60px;
    }
    
    .mobile-price-slider__content {
        gap: 0.5rem;
    }
    
    .mobile-price-slider__label {
        font-size: 0.55rem;
        margin-bottom: 0.1rem;
    }
    
    .mobile-price-slider__value,
    .mobile-price-slider__current {
        font-size: 0.95rem;
    }
    
    .mobile-price-slider__original {
        font-size: 0.7rem;
    }
    
    .mobile-price-slider__badge {
        font-size: 0.6rem;
        padding: 0.1rem 0.35rem;
    }
    
    .mobile-price-slider__countdown {
        font-size: 0.55rem;
        margin-top: 0.2rem;
        gap: 0.25rem;
    }
    
    .mobile-price-slider__countdown i {
        font-size: 0.6rem;
    }
    
    .mobile-price-slider__countdown .countdown-text {
        font-size: 0.55rem;
    }
    
    .mobile-price-slider__btn {
        width: 85px !important;
        height: 42px !important;
        padding: 0 !important;
        font-size: 0.8rem !important;
        gap: 0.2rem !important;
    }
    
    .mobile-price-slider__btn i {
        font-size: 0.8rem !important;
    }
    
    /* Boutons avec texte long (Se connecter, Télécharger) - Priorité maximale en mobile */
    .mobile-price-slider__actions .mobile-price-slider__btn--download,
    .mobile-price-slider__actions a.mobile-price-slider__btn--download,
    .mobile-price-slider__actions button.mobile-price-slider__btn--download,
    .mobile-price-slider__form .mobile-price-slider__btn--download,
    .mobile-price-slider__btn--login,
    .mobile-price-slider__btn--download {
        width: 110px !important;
        min-width: 110px !important;
        max-width: 110px !important;
        height: 42px !important;
        font-size: 0.8rem !important;
    }
    
    .mobile-price-slider__actions .mobile-price-slider__btn--download i,
    .mobile-price-slider__actions a.mobile-price-slider__btn--download i,
    .mobile-price-slider__actions button.mobile-price-slider__btn--download i,
    .mobile-price-slider__form .mobile-price-slider__btn--download i,
    .mobile-price-slider__btn--login i,
    .mobile-price-slider__btn--download i {
        font-size: 0.8rem !important;
    }
    
    /* Boutons avec texte moyen (S'inscrire, Commencer, Continuer) */
    .mobile-price-slider__btn--medium {
        width: 100px !important;
        min-width: 100px !important;
        height: 42px !important;
        font-size: 0.8rem !important;
    }
    
    .mobile-price-slider__btn--medium i {
        font-size: 0.8rem !important;
    }
    
    /* Force spécifique pour le bouton Télécharger en mobile */
    a.mobile-price-slider__btn--download,
    button.mobile-price-slider__btn--download,
    .mobile-price-slider__btn.mobile-price-slider__btn--download,
    .mobile-price-slider__btn--primary.mobile-price-slider__btn--download {
        width: 110px !important;
        min-width: 110px !important;
        height: 42px !important;
        font-size: 0.8rem !important;
    }
    
    a.mobile-price-slider__btn--download i,
    button.mobile-price-slider__btn--download i,
    .mobile-price-slider__btn.mobile-price-slider__btn--download i,
    .mobile-price-slider__btn--primary.mobile-price-slider__btn--download i {
        font-size: 0.8rem !important;
    }
    
    .mobile-price-slider__btn-group {
        gap: 0.2rem !important;
    }
    
    .mobile-price-slider__btn-group .mobile-price-slider__btn {
        width: 85px !important;
        height: 42px !important;
        padding: 0 !important;
        font-size: 0.8rem !important;
    }
    
    .mobile-price-slider__btn-group .mobile-price-slider__btn i {
        font-size: 0.8rem !important;
    }
        gap: 0.125rem;
    }
    
    .countdown-text span {
        font-size: inherit;
    }
    
    /* Styles responsives pour les cartes d'avis */
    .content-card .d-flex.justify-content-between.align-items-center {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.75rem;
    }
    
    .content-card .d-flex.justify-content-between.align-items-center h3 {
        font-size: 1rem !important;
        margin-bottom: 0 !important;
    }
    
    .content-card .d-flex.justify-content-between.align-items-center .btn {
        width: 100%;
        justify-content: center;
    }
    
    /* Styles responsives pour la section "Tous les avis des étudiants" */
    #all-reviews .d-flex.justify-content-between.align-items-center {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }
    
    #all-reviews .rating-summary {
        width: 100%;
        justify-content: flex-start;
    }
    
    /* Styles pour les boutons d'avis sur mobile - masquer le texte, garder les icônes */
    #courseReviewForm .d-flex.gap-2 .btn {
        min-width: auto;
        padding: 0.5rem 0.75rem;
        font-size: 0;
        line-height: 1;
        position: relative;
        text-indent: -9999px;
        overflow: hidden;
        white-space: nowrap;
    }
    
    #courseReviewForm .d-flex.gap-2 .btn i {
        margin-right: 0 !important;
        margin-left: 0 !important;
        font-size: 1rem;
        display: inline-block;
        line-height: 1;
        text-indent: 0;
    }
    
    #courseReviewForm .d-flex.gap-2 .btn:not(:only-child) {
        flex: 0 0 auto;
    }
    
    .review-card-preview {
        min-width: calc(100vw - 3rem) !important;
        max-width: 100% !important;
        padding: 1rem !important;
    }
    
    .review-card-horizontal {
        min-width: calc(100vw - 3rem) !important;
        max-width: 100% !important;
        padding: 1rem !important;
    }
    
    .reviews-preview-container {
        gap: 0.75rem;
    }
    
    .reviews-container {
        gap: 0.75rem;
    }
    
    .review-card-preview .review-header {
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .review-card-horizontal .review-header {
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .review-card-preview .review-avatar {
        width: 40px !important;
        height: 40px !important;
    }
    
    .review-card-horizontal .review-avatar {
        width: 40px !important;
        height: 40px !important;
    }
    
    .review-card-preview .review-author-name {
        font-size: 0.85rem !important;
    }
    
    .review-card-horizontal .review-author-name {
        font-size: 0.85rem !important;
    }
    
    .review-card-preview .review-comment-preview {
        font-size: 0.8rem !important;
    }
    
    .review-card-horizontal .review-comment {
        font-size: 0.85rem !important;
    }
}
</style>
@endpush

@section('content')
@php
    // Toutes les sections sont déjà filtrées par is_published dans le contrôleur
    $publishedSections = $course->sections->where('is_published', true);
    
    $hasVideoPreview = $course->video_preview_url || $course->video_preview_youtube_id;
    $hasPreviewLessons = $publishedSections->flatMap(function($section) {
        return $section->lessons->where('is_preview', true)->where('type', 'video');
    })->count() > 0;
    $hasAnyPreview = $hasVideoPreview || $hasPreviewLessons;
    $learnings = $course->getWhatYouWillLearnArray();
    $requirements = $course->getRequirementsArray();
    $user = auth()->user();
    
    // Calculer les statistiques depuis les données de la base de données
    $totalLessons = $publishedSections->sum(function($section) { 
        return $section->lessons->where('is_published', true)->count(); 
    });
    $totalDuration = $publishedSections->sum(function($section) { 
        return $section->lessons->where('is_published', true)->sum('duration'); 
    });
    
    $languageNames = [
        'fr' => 'Français', 'en' => 'Anglais', 'es' => 'Espagnol', 'de' => 'Allemand',
        'it' => 'Italien', 'pt' => 'Portugais', 'ar' => 'Arabe', 'zh' => 'Chinois',
        'ja' => 'Japonais', 'ko' => 'Coréen', 'ru' => 'Russe', 'nl' => 'Néerlandais',
    ];
    $displayLanguage = $languageNames[$course->language] ?? $course->language ?? 'Non spécifiée';
    
    // Charger les reviews approuvées pour les statistiques et l'affichage
    // Utiliser directement le modèle Review pour garantir les bonnes données depuis la base de données
    
    // Calculer les statistiques directement depuis la base de données (requêtes séparées)
    $averageRatingValue = \App\Models\Review::where('course_id', $course->id)
        ->where('is_approved', true)
        ->avg('rating');
    $averageRatingApproved = $averageRatingValue !== null ? round((float)$averageRatingValue, 1) : 0;
    
    $reviewsCountApproved = \App\Models\Review::where('course_id', $course->id)
        ->where('is_approved', true)
        ->count();
    
    // Charger les reviews pour l'affichage avec les relations
    $approvedReviews = \App\Models\Review::where('course_id', $course->id)
        ->where('is_approved', true)
        ->with('user')
        ->latest()
        ->get();
@endphp

<div class="course-details-page">
    <!-- Hero Section -->
    <section class="course-hero">
        <div class="container">
            <nav aria-label="breadcrumb" class="breadcrumb-modern">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">Cours</a></li>
                    @if($course->category)
                    <li class="breadcrumb-item"><a href="{{ route('courses.category', $course->category->slug) }}">{{ $course->category->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active">{{ Str::limit($course->title, 40) }}</li>
                </ol>
            </nav>

            <h1 class="course-title-hero">{{ $course->title }}</h1>

            <div class="course-stats-hero">
                <div class="course-stat-item">
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <div class="d-flex align-items-center gap-1" style="margin-right: 0.25rem;">
                            @php
                                // Calculer le nombre d'étoiles pleines basé sur la note moyenne
                                $ratingValue = (float)$averageRatingApproved;
                                $fullStars = floor($ratingValue);
                                $hasHalfStar = ($ratingValue - $fullStars) >= 0.5;
                            @endphp
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $fullStars)
                                    <i class="fas fa-star" style="color: #ffc107; font-size: 0.85rem;"></i>
                                @elseif($i == ($fullStars + 1) && $hasHalfStar)
                                    <i class="fas fa-star-half-alt" style="color: #ffc107; font-size: 0.85rem;"></i>
                                @else
                                    <i class="far fa-star" style="color: rgba(255, 255, 255, 0.5); font-size: 0.85rem;"></i>
                                @endif
                            @endfor
                        </div>
                        <span>{{ $averageRatingApproved > 0 ? number_format($averageRatingApproved, 1) : '0.0' }} ({{ $reviewsCountApproved }} avis)</span>
                    </div>
                </div>
                <div class="course-stat-item">
                    <i class="fas fa-clock"></i>
                    <span>{{ $totalDuration }} min</span>
                </div>
                <div class="course-stat-item">
                    <i class="fas fa-play-circle"></i>
                    <span>{{ $totalLessons }} leçons</span>
                </div>
                @if($course->show_students_count)
                @php
                    $totalStudents = $course->enrollments()->count();
                @endphp
                <div class="course-stat-item">
                    <i class="fas fa-users"></i>
                    <span>{{ number_format($totalStudents, 0, ',', ' ') }} 
                        {{ $totalStudents > 1 ? 'étudiants inscrits' : 'étudiant inscrit' }}</span>
                </div>
                @endif
                <div class="course-stat-item">
                    <i class="fas fa-language"></i>
                    <span>{{ $displayLanguage }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container main-content">
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Video Preview -->
                @if($hasAnyPreview)
                <div class="content-card" style="padding: 0.5rem;">
                    <div class="video-preview-wrapper" data-bs-toggle="modal" data-bs-target="#coursePreviewModal">
                        <div class="ratio ratio-16x9">
                            @if($course->thumbnail_url)
                                <img src="{{ $course->thumbnail_url }}" 
                                 alt="{{ $course->title }}" 
                                 class="img-fluid" 
                                 style="object-fit: cover;">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-primary text-white" style="font-size: 2rem; font-weight: bold;">
                                    {{ strtoupper(substr($course->title, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="video-preview-overlay">
                            <div class="play-button-large">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @elseif($course->thumbnail_url)
                <div class="content-card" style="padding: 0.5rem;">
                    <img src="{{ $course->thumbnail_url }}" 
                         alt="{{ $course->title }}" 
                         class="img-fluid rounded" 
                         style="width: 100%; height: auto; border-radius: 12px;">
                </div>
                @endif

                <!-- Course Description -->
                <div class="content-card">
                    <h2 class="section-title-modern">
                        <i class="fas fa-book-open"></i>
                        Description du cours
                    </h2>
                    <div class="course-description">
                        {!! nl2br(e($course->description)) !!}
                    </div>
                </div>

                <!-- What You'll Learn -->
                @if(count($learnings) > 0)
                <div class="content-card">
                    <h2 class="section-title-modern">
                        <i class="fas fa-graduation-cap"></i>
                        Ce que vous allez apprendre
                    </h2>
                    <div class="row">
                        @foreach($learnings as $item)
                        <div class="col-md-6">
                            <div class="learning-item">
                                <i class="fas fa-check-circle"></i>
                                <span>{{ $item }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Requirements -->
                @if(count($requirements) > 0)
                <div class="content-card">
                    <h2 class="section-title-modern">
                        <i class="fas fa-list-check"></i>
                        Prérequis
                    </h2>
                    <div class="row">
                        @foreach($requirements as $requirement)
                        <div class="col-md-6">
                            <div class="requirement-item">
                                <i class="fas fa-circle-check"></i>
                                <span>{{ $requirement }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Course Curriculum -->
                @php
                    // Utiliser uniquement les sections publiées depuis la base de données
                    $publishedSectionsList = $course->sections->where('is_published', true)->sortBy('sort_order');
                @endphp
                @if($publishedSectionsList->count() > 0)
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title-modern mb-0">
                            <i class="fas fa-list-ul"></i>
                            Programme du cours
                        </h2>
                        <div class="text-muted">
                            <i class="fas fa-play-circle me-1"></i>{{ $totalLessons }} leçon{{ $totalLessons > 1 ? 's' : '' }}
                            <i class="fas fa-clock ms-3 me-1"></i>{{ $totalDuration }} min
                        </div>
                    </div>

                    @foreach($publishedSectionsList as $index => $section)
                    <div class="curriculum-section mb-3">
                        <div class="curriculum-section-header" 
                             data-section-id="section{{ $section->id }}"
                             aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                             aria-controls="section{{ $section->id }}"
                             role="button"
                             tabindex="0">
                            <h5>
                                <i class="fas fa-chevron-down"></i>
                                <i class="fas fa-chevron-right"></i>
                                {{ $section->title }}
                            </h5>
                            @php
                                // Calculer les statistiques de la section depuis les données de la base de données
                                $sectionPublishedLessons = $section->lessons->where('is_published', true);
                                $sectionLessonsCount = $sectionPublishedLessons->count();
                                $sectionDuration = $sectionPublishedLessons->sum('duration');
                            @endphp
                            <div class="curriculum-section-stats">
                                <span><i class="fas fa-play-circle me-1"></i>{{ $sectionLessonsCount }}</span>
                                <span><i class="fas fa-clock me-1"></i>{{ $sectionDuration }} min</span>
                            </div>
                        </div>
                        <div class="curriculum-section-content {{ $index === 0 ? 'is-open' : '' }}" id="section{{ $section->id }}">
                            <div class="curriculum-lessons">
                                @php
                                    // Filtrer et trier les leçons publiées depuis la base de données
                                    $publishedLessons = $section->lessons->where('is_published', true)->sortBy('sort_order');
                                @endphp
                                @foreach($publishedLessons as $lesson)
                                @php
                                    $isPreview = $lesson->is_preview;
                                    $isLocked = !$isEnrolled && !$isPreview;
                                    $lessonTypeClass = match($lesson->type) {
                                        'video' => 'video',
                                        'text' => 'text',
                                        'pdf' => 'pdf',
                                        'quiz' => 'quiz',
                                        default => 'video'
                                    };
                                @endphp
                                <div class="lesson-item {{ $isLocked ? 'locked' : '' }} {{ $isPreview && $lesson->type === 'video' ? 'preview-clickable' : '' }}" 
                                     @if($isPreview && $lesson->type === 'video' && ($lesson->youtube_video_id || $lesson->file_path || $lesson->content_url))
                                     data-preview-lesson="{{ $lesson->id }}"
                                     data-preview-title="{{ htmlspecialchars($lesson->title, ENT_QUOTES, 'UTF-8') }}"
                                     data-preview-youtube-id="{{ $lesson->youtube_video_id ?? '' }}"
                                     data-preview-is-unlisted="{{ $lesson->is_unlisted ? '1' : '0' }}"
                                     data-preview-video-url="{{ ($lesson->file_path ? $lesson->file_url : ($lesson->content_url && !filter_var($lesson->content_url, FILTER_VALIDATE_URL) ? $lesson->content_file_url : ($lesson->content_url ?? ''))) }}"
                                     data-preview-section="{{ htmlspecialchars($section->title, ENT_QUOTES, 'UTF-8') }}"
                                     style="cursor: pointer;"
                                     onclick="event.stopPropagation(); openPreviewLesson({{ $lesson->id }}, this); return false;"
                                     @endif>
                                    <div class="lesson-icon {{ $lessonTypeClass }}">
                                        <i class="fas fa-{{ $lesson->type === 'video' ? 'play' : ($lesson->type === 'text' ? 'file-text' : ($lesson->type === 'pdf' ? 'file-pdf' : 'question-circle')) }}"></i>
                                    </div>
                                    <div class="lesson-content">
                                        <div class="lesson-title">{{ $lesson->title }}</div>
                                        <div class="lesson-meta">
                                            @if($lesson->duration)
                                            <i class="fas fa-clock me-1"></i>{{ $lesson->duration }} min
                                            @endif
                                            @if($isPreview)
                                            <span class="lesson-preview-badge ms-2">
                                                <i class="fas fa-eye me-1"></i>Aperçu
                                            </span>
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
                @endif

                <!-- Instructor -->
                <div class="content-card">
                    <h2 class="section-title-modern">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Votre instructeur
                    </h2>
                    <div class="instructor-card">
                        @if($course->instructor)
                            <div class="instructor-avatar">
                                <img src="{{ $course->instructor->avatar_url }}" 
                                     alt="{{ $course->instructor->name }}">
                            </div>
                        @else
                            <div class="instructor-avatar d-flex align-items-center justify-content-center bg-primary text-white" style="font-size: 2rem; font-weight: bold; border-radius: 50%;">
                                {{ strtoupper(substr($course->instructor->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <h5>{{ $course->instructor->name }}</h5>
                            @if($course->instructor->bio)
                            <p class="text-muted mb-0">{{ Str::limit($course->instructor->bio, 200) }}</p>
                            @endif
                            <div class="instructor-stats">
                                <span><i class="fas fa-book me-1"></i>{{ $course->instructor->courses_count ?? $course->instructor->courses->count() }} cours</span>
                                <span><i class="fas fa-users me-1"></i>{{ $course->instructor->courses->sum(function($c) { return $c->enrollments_count ?? $c->enrollments->count(); }) }} étudiants</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rating and Review Section -->
                @php
                    $userReview = null;
                    if ($user) {
                        // Charger l'avis de l'utilisateur directement depuis la base de données
                        $userReview = \App\Models\Review::where('user_id', $user->id)
                            ->where('course_id', $course->id)
                            ->first();
                    }
                    $hasUserReview = $userReview !== null;
                @endphp
                @if($user && ($isEnrolled || $course->is_free))
                <div class="content-card">
                    <h2 class="section-title-modern">
                        <i class="fas fa-star"></i>
                        {{ $hasUserReview ? 'Modifier votre avis' : 'Noter ce cours' }}
                    </h2>
                    <form id="courseReviewForm" action="{{ route('courses.review.store', $course->slug) }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-3">Votre note</label>
                            <div class="rating-input-wrapper">
                                <div class="rating-stars-input" data-rating="{{ $hasUserReview ? $userReview->rating : 0 }}">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star rating-star {{ $hasUserReview && $i <= $userReview->rating ? 'active' : '' }}" 
                                       data-value="{{ $i }}"
                                       style="font-size: 2rem; color: #ddd; cursor: pointer; transition: all 0.2s; margin-right: 0.5rem;"></i>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" value="{{ $hasUserReview ? $userReview->rating : 0 }}" required>
                                <div class="rating-value-text mt-2 text-muted">
                                    <span id="ratingText">{{ $hasUserReview ? $userReview->rating . ' étoile' . ($userReview->rating > 1 ? 's' : '') : 'Sélectionnez une note' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="reviewComment" class="form-label fw-semibold mb-2">Votre avis</label>
                            <textarea class="form-control" 
                                      id="reviewComment" 
                                      name="comment" 
                                      rows="5" 
                                      placeholder="Partagez votre expérience avec ce cours...">{{ $hasUserReview ? $userReview->comment : '' }}</textarea>
                            <div class="form-text">Votre avis aidera d'autres étudiants à prendre une décision.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>
                                {{ $hasUserReview ? 'Mettre à jour mon avis' : 'Publier mon avis' }}
                            </button>
                            @if($hasUserReview)
                            <button type="button" class="btn btn-outline-danger" id="deleteReviewBtn">
                                <i class="fas fa-trash me-2"></i>
                                Supprimer mon avis
                            </button>
                            @endif
                        </div>
                    </form>
                </div>
                @elseif(!$user)
                <div class="content-card">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Connectez-vous</strong> pour noter ce cours et donner votre avis.
                        <a href="{{ route('login') }}" class="alert-link ms-2">Se connecter</a>
                    </div>
                </div>
                @elseif(!$isEnrolled && !$course->is_free)
                <div class="content-card">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Vous devez être <strong>inscrit à ce cours</strong> pour pouvoir le noter et donner votre avis.
                    </div>
                </div>
                @endif

                <!-- Preview Reviews Section -->
                @php
                    // Utiliser $approvedReviews déjà définie en haut du fichier
                    $previewReviews = $approvedReviews && $approvedReviews->count() > 0 ? $approvedReviews->take(3) : collect(); // Afficher seulement 3 avis
                @endphp
                @if($approvedReviews->count() > 0)
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0" style="font-size: 1.25rem; font-weight: 600;">
                            <i class="fas fa-comments me-2"></i>
                            Avis récents
                        </h3>
                        <a href="{{ route('courses.reviews', $course->slug) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>
                            Voir tous les avis ({{ $approvedReviews->count() }})
                        </a>
                    </div>
                    <div class="reviews-preview-horizontal">
                        <div class="reviews-preview-container">
                            @foreach($previewReviews as $review)
                            <div class="review-card-preview">
                                <div class="review-header">
                                    @if($review->user && $review->user->avatar_url)
                                        <div class="review-avatar">
                                            <img src="{{ $review->user->avatar_url }}" 
                                                 alt="{{ $review->user->name }}">
                                        </div>
                                    @else
                                        <div class="review-avatar d-flex align-items-center justify-content-center bg-primary text-white" style="font-size: 0.9rem; font-weight: bold; border-radius: 50%; min-width: 45px; min-height: 45px;">
                                            {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="review-author">
                                        <div class="review-author-name">{{ $review->user->name ?? 'Utilisateur' }}</div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <div class="rating-stars" style="font-size: 0.8rem;">
                                                @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $review->rating ? '' : 'far' }}" style="color: {{ $i <= $review->rating ? '#ffc107' : '#ddd' }};"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <div class="review-date" style="font-size: 0.75rem;">{{ $review->created_at->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                                @if($review->comment)
                                <div class="review-comment-preview">{{ Str::limit($review->comment, 150) }}</div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- All Reviews Section -->
                @if($approvedReviews->count() > 0)
                <div class="content-card" id="all-reviews">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title-modern mb-0">
                            <i class="fas fa-star"></i>
                            Tous les avis des étudiants
                        </h2>
                        <div class="rating-summary">
                            <div class="rating-score">{{ number_format($averageRatingApproved, 1) }}</div>
                            <div>
                                <div class="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                    @php
                                        $filledStar = $i <= round($averageRatingApproved, 0);
                                    @endphp
                                    <i class="fas fa-star {{ $filledStar ? '' : 'far' }}"></i>
                                    @endfor
                                </div>
                                <div class="rating-count">({{ $approvedReviews->count() }} avis)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Horizontal Reviews Scroll -->
                    <div class="reviews-horizontal-scroll">
                        <div class="reviews-container">
                            @foreach($approvedReviews as $review)
                            <div class="review-card-horizontal">
                                <div class="review-header">
                                    @if($review->user && $review->user->avatar_url)
                                        <div class="review-avatar">
                                            <img src="{{ $review->user->avatar_url }}" 
                                                 alt="{{ $review->user->name }}">
                                        </div>
                                    @else
                                        <div class="review-avatar d-flex align-items-center justify-content-center bg-primary text-white" style="font-size: 1rem; font-weight: bold; border-radius: 50%; min-width: 50px; min-height: 50px;">
                                            {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="review-author">
                                        <div class="review-author-name">{{ $review->user->name ?? 'Utilisateur' }}</div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <div class="rating-stars" style="font-size: 0.875rem;">
                                                @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $review->rating ? '' : 'far' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <div class="review-date">{{ $review->created_at->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                                @if($review->comment)
                                <div class="review-comment">{{ Str::limit($review->comment, 200) }}</div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Related Courses -->
                @if($relatedCourses->count() > 0)
                <div class="content-card">
                    <h2 class="section-title-modern">
                        <i class="fas fa-thumbs-up"></i>
                        Cours recommandés
                    </h2>
                    <div class="row g-3">
                        @foreach($relatedCourses as $relatedCourse)
                        @php
                            $relatedCourseStats = $relatedCourse->getCourseStats();
                        @endphp
                        <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                            <div class="course-card" data-course-url="{{ route('courses.show', $relatedCourse->slug) }}" style="cursor: pointer;">
                                <div class="card" style="position: relative;">
                                    <div class="position-relative">
                                        @if($relatedCourse->thumbnail)
                                            <img src="{{ $relatedCourse->thumbnail }}" 
                                             class="card-img-top" alt="{{ $relatedCourse->title }}">
                                        @else
                                            <div class="card-img-top d-flex align-items-center justify-content-center bg-primary text-white" style="height: 180px; font-size: 2rem; font-weight: bold;">
                                                {{ strtoupper(substr($relatedCourse->title, 0, 2)) }}
                                            </div>
                                        @endif
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
                                                <i class="fas fa-user me-1"></i>{{ Str::limit($relatedCourse->instructor->name ?? 'Instructeur', 20) }}
                                            </small>
                                            <div class="rating">
                                                <i class="fas fa-star"></i>
                                                <span>{{ number_format($relatedCourseStats['average_rating'] ?? 0, 1) }}</span>
                                                <span class="text-muted">({{ $relatedCourseStats['total_reviews'] ?? 0 }})</span>
                                            </div>
                                        </div>
                                        
                                        @if($relatedCourse->show_students_count && isset($relatedCourseStats['total_students']))
                                        <div class="students-count mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-users me-1"></i>
                                                {{ number_format($relatedCourseStats['total_students'], 0, ',', ' ') }} 
                                                {{ $relatedCourseStats['total_students'] > 1 ? 'étudiants inscrits' : 'étudiant inscrit' }}
                                            </small>
                                        </div>
                                        @endif
                                        
                                        <div class="price-duration">
                                            <div class="price">
                                                @if($relatedCourse->is_free)
                                                    <span class="text-success fw-bold">Gratuit</span>
                                                @else
                                                    @if($relatedCourse->is_sale_active && $relatedCourse->active_sale_price !== null)
                                                        <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($relatedCourse->active_sale_price) }}</span>
                                                        <small class="text-muted text-decoration-line-through ms-1">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($relatedCourse->price) }}</small>
                                                    @else
                                                        <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($relatedCourse->price) }}</span>
                                                    @endif
                                                @endif
                                            </div>
                                            @if($relatedCourse->is_sale_active && $relatedCourse->sale_end_at)
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
                                            @endif
                                        </div>
                                        
                                        <div class="card-actions" onclick="event.stopPropagation(); event.preventDefault();">
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

            <!-- Right Sidebar -->
            <div class="col-lg-4">
                <div class="course-sidebar">
                    <!-- Purchase Card -->
                    <div class="sidebar-card">
                        <div class="price-display">
                            @if($course->is_free)
                                <div class="price-free">Gratuit</div>
                            @else
                                @if($course->is_sale_active && $course->active_sale_price !== null)
                                    <div class="price-current">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->active_sale_price) }}</div>
                                    <div class="price-original">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</div>
                                    @if($course->sale_discount_percentage)
                                    <div class="price-discount">
                                        -{{ $course->sale_discount_percentage }}% de réduction
                                    </div>
                                    @endif
                                    @if($course->is_sale_active && $course->sale_end_at)
                                    <div class="promotion-countdown mt-3" data-sale-end="{{ $course->sale_end_at->toIso8601String() }}">
                                        <div class="countdown-label text-muted small mb-1">
                                            <i class="fas fa-clock me-1"></i>Promotion se termine dans :
                                        </div>
                                        <div class="countdown-text text-danger fw-bold">
                                            <span class="countdown-days">0</span>j 
                                            <span class="countdown-hours">00</span>h 
                                            <span class="countdown-minutes">00</span>min
                                        </div>
                                    </div>
                                    @endif
                                @else
                                    <div class="price-current">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</div>
                                @endif
                            @endif
                        </div>

                        @if(!$user)
                            <div class="d-grid gap-2">
                                @php
                                    $finalLoginCourse = url()->full();
                                    $callbackLoginCourse = route('sso.callback', ['redirect' => $finalLoginCourse]);
                                    $ssoLoginUrlCourse = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callbackLoginCourse);
                                @endphp
                                <a href="{{ $ssoLoginUrlCourse }}" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter pour accéder au cours
                                </a>
                                @php
                                    $finalRegisterCourse = url()->full();
                                    $callbackRegisterCourse = route('sso.callback', ['redirect' => $finalRegisterCourse]);
                                    $ssoRegisterUrlCourse = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callbackRegisterCourse);
                                @endphp
                                <a href="{{ $ssoRegisterUrlCourse }}" class="btn btn-outline-primary btn-lg w-100">
                                    <i class="fas fa-user-plus me-2"></i>Créer un compte
                                </a>
                            </div>
                        @else
                            <div class="d-grid gap-2">
                                @if($course->is_free)
                                    {{-- Cours gratuit --}}
                                    @if($isEnrolled)
                                        {{-- Utilisateur inscrit au cours gratuit --}}
                                        @if($course->is_downloadable && $canDownloadCourse)
                                            <a href="{{ route('courses.download', $course->slug) }}" class="btn btn-primary btn-lg w-100">
                                                <i class="fas fa-download me-2"></i>Télécharger
                                            </a>
                                        @else
                                            @php
                                                $progress = $enrollment->progress ?? 0;
                                            @endphp
                                            <a href="{{ route('learning.course', $course->slug) }}" class="btn btn-success btn-lg w-100">
                                                <i class="fas fa-play me-2"></i>{{ $progress > 0 ? 'Continuer' : 'Commencer' }}
                                            </a>
                                        @endif
                                    @else
                                        {{-- Utilisateur pas encore inscrit au cours gratuit --}}
                                        <form action="{{ route('student.courses.enroll', $course->slug) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="redirect_to" value="{{ $course->is_downloadable ? 'course' : 'learn' }}">
                                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                                <i class="fas fa-user-plus me-2"></i>S'inscrire au cours
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    {{-- Cours payant --}}
                                    @if($isEnrolled)
                                        {{-- Utilisateur inscrit au cours payant --}}
                                        @if($course->is_downloadable && $canDownloadCourse)
                                            <a href="{{ route('courses.download', $course->slug) }}" class="btn btn-primary btn-lg w-100">
                                                <i class="fas fa-download me-2"></i>Télécharger
                                            </a>
                                        @else
                                            @php
                                                $progress = $enrollment->progress ?? 0;
                                            @endphp
                                            <a href="{{ route('learning.course', $course->slug) }}" class="btn btn-success btn-lg w-100">
                                                <i class="fas fa-play me-2"></i>{{ $progress > 0 ? 'Continuer' : 'Commencer' }}
                                            </a>
                                        @endif
                                    @elseif($hasPurchased)
                                        {{-- Utilisateur a acheté mais pas encore inscrit --}}
                                        <form action="{{ route('student.courses.enroll', $course->slug) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="redirect_to" value="{{ $course->is_downloadable ? 'course' : 'learn' }}">
                                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                                <i class="fas fa-user-plus me-2"></i>S'inscrire au cours
                                            </button>
                                        </form>
                                    @else
                                        {{-- Utilisateur n'a pas encore acheté --}}
                                        <button type="button" class="btn btn-outline-primary btn-lg w-100" onclick="addToCart({{ $course->id }})">
                                            <i class="fas fa-shopping-cart me-2"></i>Ajouter au panier
                                        </button>
                                        <button type="button" class="btn btn-success btn-lg w-100" onclick="proceedToCheckout({{ $course->id }})">
                                            <i class="fas fa-credit-card me-2"></i>Procéder au paiement
                                        </button>
                                    @endif
                                @endif
                            </div>
                        @endif

                        <hr class="my-3">

                        <h6 class="fw-bold mb-2" style="font-size: 0.9375rem;">Ce cours comprend :</h6>
                        <ul class="course-features-list">
                            @foreach($course->getCourseFeatures() as $feature)
                            <li>
                                <i class="fas {{ $feature['icon'] }}"></i>
                                <span>{{ $feature['text'] }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Share Card -->
                    <div class="sidebar-card">
                        <h6 class="fw-bold mb-2" style="font-size: 0.9375rem;">Partager ce cours</h6>
                        <div class="share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" 
                               target="_blank" 
                               class="share-btn" 
                               title="Partager sur Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($course->title) }}" 
                               target="_blank" 
                               class="share-btn" 
                               title="Partager sur Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}" 
                               target="_blank" 
                               class="share-btn" 
                               title="Partager sur LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <button type="button" 
                                    class="share-btn" 
                                    onclick="copyToClipboard('{{ request()->url() }}')" 
                                    title="Copier le lien">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Payment Slider -->
<div class="mobile-price-slider">
    <div class="mobile-price-slider__content">
        <div class="mobile-price-slider__price">
            @if($course->is_free)
                <div class="mobile-price-slider__label">Prix</div>
                <div class="mobile-price-slider__value">Gratuit</div>
            @else
                @if($course->is_sale_active && $course->active_sale_price !== null)
                    <div class="mobile-price-slider__label">Prix promotionnel</div>
                    <div class="mobile-price-slider__prices">
                        <span class="mobile-price-slider__current">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->active_sale_price) }}</span>
                        <span class="mobile-price-slider__original">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</span>
                        @if($course->sale_discount_percentage)
                            <span class="mobile-price-slider__badge">-{{ $course->sale_discount_percentage }}%</span>
                        @endif
                    </div>
                    @if($course->is_sale_active && $course->sale_end_at)
                        <div class="mobile-price-slider__countdown" data-sale-end="{{ $course->sale_end_at->toIso8601String() }}">
                            <i class="fas fa-fire"></i>
                            <span class="countdown-text">
                                <span class="countdown-days">0</span>j 
                                <span class="countdown-hours">00</span>h 
                                <span class="countdown-minutes">00</span>min
                            </span>
                        </div>
                    @endif
                @else
                    <div class="mobile-price-slider__label">Prix</div>
                    <div class="mobile-price-slider__value">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</div>
                @endif
            @endif
        </div>
        <div class="mobile-price-slider__actions">
            @if(!$user)
                @php
                    $finalLoginCourse2 = url()->full();
                    $callbackLoginCourse2 = route('sso.callback', ['redirect' => $finalLoginCourse2]);
                    $ssoLoginUrlCourse2 = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callbackLoginCourse2);
                @endphp
                <a href="{{ $ssoLoginUrlCourse2 }}" class="mobile-price-slider__btn mobile-price-slider__btn--primary mobile-price-slider__btn--login">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Se connecter</span>
                </a>
            @else
                @if($course->is_free)
                    {{-- Cours gratuit --}}
                    @if($isEnrolled)
                        {{-- Utilisateur inscrit au cours gratuit --}}
                        @if($course->is_downloadable && $canDownloadCourse)
                            <a href="{{ route('courses.download', $course->slug) }}" class="mobile-price-slider__btn mobile-price-slider__btn--primary mobile-price-slider__btn--download">
                                <i class="fas fa-download"></i>
                                <span>Télécharger</span>
                            </a>
                        @else
                            @php
                                $progress = $enrollment->progress ?? 0;
                            @endphp
                            <a href="{{ route('learning.course', $course->slug) }}" class="mobile-price-slider__btn mobile-price-slider__btn--success mobile-price-slider__btn--medium">
                                <i class="fas fa-play"></i>
                                <span>{{ $progress > 0 ? 'Continuer' : 'Commencer' }}</span>
                            </a>
                        @endif
                    @else
                        {{-- Utilisateur pas encore inscrit au cours gratuit --}}
                        <form action="{{ route('student.courses.enroll', $course->slug) }}" method="POST" class="mobile-price-slider__form">
                            @csrf
                            <input type="hidden" name="redirect_to" value="{{ $course->is_downloadable ? 'course' : 'learn' }}">
                            <button type="submit" class="mobile-price-slider__btn mobile-price-slider__btn--primary mobile-price-slider__btn--medium">
                                <i class="fas fa-user-plus"></i>
                                <span>S'inscrire</span>
                            </button>
                        </form>
                    @endif
                @else
                    {{-- Cours payant --}}
                    @if($isEnrolled)
                        {{-- Utilisateur inscrit au cours payant --}}
                        @if($course->is_downloadable && $canDownloadCourse)
                            <a href="{{ route('courses.download', $course->slug) }}" class="mobile-price-slider__btn mobile-price-slider__btn--primary mobile-price-slider__btn--download">
                                <i class="fas fa-download"></i>
                                <span>Télécharger</span>
                            </a>
                        @else
                            @php
                                $progress = $enrollment->progress ?? 0;
                            @endphp
                            <a href="{{ route('learning.course', $course->slug) }}" class="mobile-price-slider__btn mobile-price-slider__btn--success mobile-price-slider__btn--medium">
                                <i class="fas fa-play"></i>
                                <span>{{ $progress > 0 ? 'Continuer' : 'Commencer' }}</span>
                            </a>
                        @endif
                    @elseif($hasPurchased)
                        {{-- Utilisateur a acheté mais pas encore inscrit --}}
                        <form action="{{ route('student.courses.enroll', $course->slug) }}" method="POST" class="mobile-price-slider__form">
                            @csrf
                            <input type="hidden" name="redirect_to" value="{{ $course->is_downloadable ? 'course' : 'learn' }}">
                            <button type="submit" class="mobile-price-slider__btn mobile-price-slider__btn--primary mobile-price-slider__btn--medium">
                                <i class="fas fa-user-plus"></i>
                                <span>S'inscrire</span>
                            </button>
                        </form>
                    @else
                        {{-- Utilisateur n'a pas encore acheté --}}
                        <div class="mobile-price-slider__btn-group">
                            <button type="button" class="mobile-price-slider__btn mobile-price-slider__btn--outline" onclick="addToCart({{ $course->id }})">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Panier</span>
                            </button>
                            <button type="button" class="mobile-price-slider__btn mobile-price-slider__btn--success" onclick="proceedToCheckout({{ $course->id }})">
                                <i class="fas fa-credit-card"></i>
                                <span>Payer</span>
                            </button>
                        </div>
                    @endif
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Video Preview Modal -->
@if($hasAnyPreview)
<div class="modal fade" id="coursePreviewModal" tabindex="-1" aria-labelledby="coursePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fixed-height">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white border-0 flex-shrink-0" style="background-color: #003366;">
                <h5 class="modal-title fw-bold" id="coursePreviewModalLabel">
                    <i class="fas fa-play-circle me-2"></i>Aperçus du cours
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer">
                    <i class="fas fa-times" style="color: #ffffff !important;"></i>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0 h-100">
                    <div class="col-lg-8" style="border-right: 1px solid #dee2e6;">
                        <div class="p-4">
                            <div class="ratio ratio-16x9" id="previewVideoContainer">
                                @if($course->video_preview_youtube_id)
                                    @php
                                        $previewLesson = new \App\Models\CourseLesson();
                                        $previewLesson->id = 0;
                                        $previewLesson->youtube_video_id = $course->video_preview_youtube_id;
                                        $previewLesson->is_unlisted = $course->video_preview_is_unlisted;
                                        $previewLesson->is_preview = true;
                                    @endphp
                                    <div class="preview-player-wrapper active" data-preview-id="0">
                                    <x-plyr-player :lesson="$previewLesson" :course="$course" :isMobile="false" />
                                    </div>
                                @elseif($course->video_preview_url)
                                    <div class="preview-player-wrapper active" data-preview-id="0">
                                        <div class="plyr-player-wrapper position-absolute top-0 start-0 w-100 h-100" id="wrapper-plyr-player-0">
                                            <video id="plyr-player-0" class="plyr-player-video" playsinline>
                                                <source src="{{ $course->video_preview_url }}" type="video/mp4">
                                            </video>
                                        </div>
                                    </div>
                                @endif
                                
                                {{-- Les wrappers pour les leçons d'aperçu seront créés dynamiquement via JavaScript --}}
                            </div>
                            <div class="bg-light rounded p-3" id="previewLessonInfo" style="margin-top: 0.25rem !important; padding-top: 0.5rem !important; padding-bottom: 0.75rem !important;">
                                <h6 class="fw-bold mb-2" style="color: #003366; margin-top: 0;">Aperçu du cours</h6>
                                <p class="text-muted small mb-0" id="previewLessonTitle">{{ $course->title }}</p>
                                <p class="text-muted small mb-0" id="previewLessonSection" style="display: none;"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 bg-light" style="display: flex; flex-direction: column; height: 100%; overflow: hidden;">
                        <div class="p-4" style="flex-shrink: 0;">
                            <h6 class="fw-bold mb-3" style="color: #003366;">
                                <i class="fas fa-list me-2"></i>Autres aperçus
                            </h6>
                        </div>
                            <div id="previewListContainer" style="flex: 1 1 auto; min-height: 0; height: 100%; overflow-y: auto;">
                            <div class="px-4 pb-4" id="previewListContent">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>

<!-- Données pour le JavaScript -->
<script>
    window.coursePreviewData = {
        courseId: {{ $course->id }},
        previewUrl: '{{ route("courses.preview-data", $course->slug) }}'
    };
</script>
@endif

@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Créer une notification toast moderne
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 start-50 translate-middle-x mt-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="alert alert-success alert-dismissible fade show shadow" role="alert" style="min-width: 300px;">
                <i class="fas fa-check-circle me-2"></i>Lien copié dans le presse-papiers !
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    });
}

// Flag pour éviter les chargements multiples
let isLoadingPreviewList = false;
let previewListLoaded = false;

// Fonction pour charger la liste des aperçus dynamiquement
function loadPreviewList() {
    // Éviter les chargements multiples
    if (isLoadingPreviewList) {
        return;
    }
    
    const container = document.getElementById('previewListContainer');
    if (!container) {
        console.error('❌ Container previewListContainer manquant');
        // Réessayer après un délai
        setTimeout(function() {
            const retryContainer = document.getElementById('previewListContainer');
            if (retryContainer) {
                loadPreviewList();
            } else {
                console.error('❌ Container toujours manquant après retry');
            }
        }, 500);
        return;
    }
    
    const computedStyle = window.getComputedStyle(container);
    
    if (!window.coursePreviewData || !window.coursePreviewData.previewUrl) {
        console.error('❌ coursePreviewData ou previewUrl manquant', window.coursePreviewData);
        let contentWrapper = document.getElementById('previewListContent');
        if (!contentWrapper && container) {
            contentWrapper = document.createElement('div');
            contentWrapper.id = 'previewListContent';
            contentWrapper.className = 'px-4 pb-4';
            container.appendChild(contentWrapper);
        }
        if (contentWrapper) {
            contentWrapper.innerHTML = '<p class="text-danger text-center py-4">Erreur: Données de cours manquantes</p>';
        }
        return;
    }
    
    // Afficher le spinner
    let contentWrapper = document.getElementById('previewListContent');
    if (!contentWrapper) {
        contentWrapper = document.createElement('div');
        contentWrapper.id = 'previewListContent';
        contentWrapper.className = 'px-4 pb-4';
        container.innerHTML = '';
        container.appendChild(contentWrapper);
    }
    
    if (contentWrapper) {
        contentWrapper.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
        `;
    }
    
    isLoadingPreviewList = true;
    fetch(window.coursePreviewData.previewUrl, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || err.error || 'Erreur HTTP: ' + response.status);
                }).catch(() => {
                    throw new Error('Erreur HTTP: ' + response.status);
                });
            }
            return response.json();
        })
        .then(data => {
            isLoadingPreviewList = false;
            previewListLoaded = true;
            
            // Vérifier si c'est une réponse d'erreur
            if (data.error) {
                throw new Error(data.message || data.error);
            }
            
            // Vider le conteneur et créer un wrapper pour le contenu
            contentWrapper = document.getElementById('previewListContent');
            if (!contentWrapper) {
                contentWrapper = document.createElement('div');
                contentWrapper.id = 'previewListContent';
                contentWrapper.className = 'px-4 pb-4';
                container.innerHTML = '';
                container.appendChild(contentWrapper);
            } else {
                contentWrapper.innerHTML = '';
            }
            
            if (!data.preview || data.preview.length === 0) {
                contentWrapper.innerHTML = '<p class="text-muted text-center py-4">Aucun aperçu disponible</p>';
                return;
            }
            
            // Créer les wrappers de players pour les leçons d'aperçu qui n'existent pas encore
            const previewVideoContainer = document.getElementById('previewVideoContainer');
            if (previewVideoContainer) {
                data.preview.forEach(preview => {
                    // Vérifier si le wrapper existe déjà
                    const existingWrapper = previewVideoContainer.querySelector(`[data-preview-id="${preview.id}"]`);
                    if (!existingWrapper && preview.id !== 0) {
                        // Créer le wrapper pour cette leçon
                        const wrapper = document.createElement('div');
                        wrapper.className = 'preview-player-wrapper';
                        wrapper.setAttribute('data-preview-id', preview.id);
                        wrapper.style.display = 'none';
                        
                        // Créer un player Plyr pour cette leçon
                        if (preview.youtube_id) {
                            // Pour YouTube, créer un conteneur Plyr
                            const playerId = 'plyr-player-' + preview.id;
                            wrapper.innerHTML = `
                                <div class="plyr-player-wrapper plyr-external-video position-absolute top-0 start-0 w-100 h-100" id="wrapper-${playerId}">
                                    <div class="plyr__video-embed" id="${playerId}" data-plyr-provider="youtube" data-plyr-embed-id="${preview.youtube_id}"></div>
                                </div>
                            `;
                            
                            previewVideoContainer.appendChild(wrapper);
                            
                            // Initialiser Plyr quand le wrapper sera affiché
                        } else if (preview.video_url) {
                            // Pour les vidéos directes, créer un player Plyr
                            const playerId = 'plyr-player-' + preview.id;
                            wrapper.innerHTML = `
                                <div class="plyr-player-wrapper position-absolute top-0 start-0 w-100 h-100" id="wrapper-${playerId}">
                                    <video id="${playerId}" class="plyr-player-video" playsinline>
                                        <source src="${preview.video_url}" type="video/mp4">
                                    </video>
                                </div>
                            `;
                            previewVideoContainer.appendChild(wrapper);
                            
                            // Initialiser Plyr pour cette vidéo
                            setTimeout(function() {
                                const videoElement = document.getElementById(playerId);
                                if (videoElement && window.Plyr) {
                                    try {
                                        const player = new Plyr(videoElement, {
                                            controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
                                            settings: ['quality', 'speed'],
                                            keyboard: { focused: true, global: false },
                                            tooltips: { controls: true, seek: true },
                                            clickToPlay: true,
                                            hideControls: true,
                                            resetOnEnd: false,
                                            disableContextMenu: true
                                        });
                                        window['plyr_' + playerId] = player;
                                    } catch (error) {
                                        console.error('❌ Error initializing Plyr for video:', error);
                                    }
                                }
                            }, 100);
                        }
                    }
                });
            }
            
            data.preview.forEach(preview => {
                const item = document.createElement('div');
                item.className = 'preview-item mb-3 p-3 border rounded';
                item.setAttribute('data-preview-lesson', preview.id);
                item.setAttribute('data-preview-title', preview.title);
                item.setAttribute('data-preview-youtube-id', preview.youtube_id || '');
                item.setAttribute('data-preview-is-unlisted', preview.is_unlisted ? '1' : '0');
                item.setAttribute('data-preview-video-url', preview.video_url || '');
                item.setAttribute('data-preview-section', preview.section || '');
                item.style.cssText = preview.is_main 
                    ? 'border-color: #003366 !important; background: rgba(0, 51, 102, 0.05); cursor: pointer; transition: all 0.2s ease;'
                    : 'cursor: pointer; transition: all 0.2s ease;';
                
                item.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = preview.is_main 
                        ? 'rgba(0, 51, 102, 0.15)' 
                        : 'rgba(0, 51, 102, 0.1)';
                    this.style.borderColor = '#003366';
                });
                
                item.addEventListener('mouseleave', function() {
                    if (preview.is_main) {
                        this.style.backgroundColor = 'rgba(0, 51, 102, 0.05)';
                    } else {
                        this.style.backgroundColor = '';
                        this.style.borderColor = '';
                    }
                });
                
                item.addEventListener('click', function() {
                    openPreviewLesson(preview.id);
                });
                
                const iconClass = 'fas fa-play-circle fa-2x';
                const iconStyle = 'color: #003366;';
                const isPreview = preview.is_preview || false;
                const previewBadge = isPreview ? '<span class="badge bg-warning text-dark ms-2" style="font-size: 0.65rem;"><i class="fas fa-eye me-1"></i>Aperçu</span>' : '';
                
                // Fonction pour échapper le HTML et afficher correctement les caractères spéciaux
                const escapeHtml = (text) => {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                };
                
                const titleText = escapeHtml(preview.title || '');
                const sectionText = preview.section ? escapeHtml(preview.section) : '';
                
                item.innerHTML = `
                    <div class="d-flex ${preview.is_main ? 'align-items-center' : 'align-items-start'}">
                        <div class="me-3">
                            <i class="${iconClass}" style="${iconStyle}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-1">
                                <h6 class="mb-0 fw-medium" style="color: #003366; flex: 1;">${titleText}</h6>
                                ${previewBadge}
                            </div>
                            ${preview.is_main 
                                ? '<small class="text-muted"><i class="fas fa-video me-1"></i>Vidéo principale</small>'
                                : `<div class="d-flex flex-wrap gap-2 mb-1">
                                    ${sectionText ? `<small class="text-muted"><i class="fas fa-layer-group me-1"></i>${sectionText}</small>` : ''}
                                    ${preview.duration ? `<small class="text-muted"><i class="fas fa-clock me-1"></i>${preview.duration} min</small>` : ''}
                                </div>`
                            }
                        </div>
                    </div>
                `;
                
                contentWrapper.appendChild(item);
            });
            
            // Activer l'aperçu principal par défaut
            const mainItem = contentWrapper.querySelector('[data-preview-lesson="0"]');
            if (mainItem) {
                mainItem.classList.add('active');
            }
            
            // Recalculer la hauteur du conteneur après le chargement du contenu
            setTimeout(function() {
                const container = document.getElementById('previewListContainer');
                const colLg4 = container ? container.closest('.col-lg-4') : null;
                
                if (container && colLg4) {
                    const headerHeight = colLg4.querySelector('div:first-child')?.offsetHeight || 0;
                    const colHeight = colLg4.offsetHeight;
                    const availableHeight = colHeight - headerHeight;
                    
                    if (availableHeight > 0) {
                        container.style.maxHeight = availableHeight + 'px';
                        container.style.height = availableHeight + 'px';
                    }
                }
            }, 100);
        })
        .catch(error => {
            isLoadingPreviewList = false;
            console.error('❌ Erreur lors du chargement des aperçus:', error);
            console.error('URL:', window.coursePreviewData?.previewUrl);
            console.error('Container:', container);
            let contentWrapper = document.getElementById('previewListContent');
            if (!contentWrapper) {
                if (container) {
                    contentWrapper = document.createElement('div');
                    contentWrapper.id = 'previewListContent';
                    contentWrapper.className = 'px-4 pb-4';
                    container.innerHTML = '';
                    container.appendChild(contentWrapper);
                } else {
                    console.error('❌ Impossible de créer le conteneur de contenu');
                    return;
                }
            }
            const errorMessage = error.message || 'Erreur inconnue';
            contentWrapper.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-danger mb-2">Erreur lors du chargement des aperçus</p>
                    <small class="text-muted">${errorMessage}</small>
                    <br>
                    <button class="btn btn-sm btn-primary mt-3" onclick="previewListLoaded = false; loadPreviewList();">
                        <i class="fas fa-redo me-1"></i>Réessayer
                    </button>
                </div>
            `;
        });
}


// Fonction pour initialiser un player Plyr pour une leçon d'aperçu
function initializePreviewPlayer(lessonId, youtubeId, isUnlisted) {
    if (!youtubeId || !window.Plyr) {
        console.warn('Plyr not available or no YouTube ID for player:', lessonId);
        return;
    }
    
    const playerId = 'plyr-player-' + lessonId;
        const playerElement = document.getElementById(playerId);
    const wrapper = document.getElementById('wrapper-' + playerId);
    
    if (!playerElement || !wrapper) {
        console.warn('Player element or wrapper not found for:', playerId);
            return;
        }
        
    // Initialiser Plyr
    try {
        const player = new Plyr(playerElement, {
            youtube: {
                noCookie: false,
                rel: 0,
                showinfo: 0,
                iv_load_policy: 3,
                modestbranding: 1,
                controls: 0,
                disablekb: 1,
                fs: 0,
                cc_load_policy: 0
            },
            controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
            settings: ['quality', 'speed'],
            keyboard: { focused: true, global: false },
            tooltips: { controls: true, seek: true },
            clickToPlay: true,
            hideControls: true,
            resetOnEnd: false,
            disableContextMenu: true
        });
        
        // Sauvegarder la référence
        window['plyr_' + playerId] = player;
        
        // Désactiver le menu contextuel
        wrapper.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });
        
        wrapper.addEventListener('dragstart', function(e) {
            e.preventDefault();
            return false;
        });
        
    } catch (error) {
        console.error('❌ Error initializing Plyr player:', error);
    }
}

// Fonction pour ouvrir un aperçu de leçon dans le modal
function openPreviewLesson(lessonId, clickedElement = null) {
    // Si un élément a été cliqué, utiliser ses données directement
    let lessonElement = clickedElement;
    let title = '';
    let section = '';
    
    if (!lessonElement) {
        // Chercher l'élément dans la liste des aperçus chargés
        if (lessonId === 0) {
            lessonElement = document.querySelector('[data-preview-lesson="0"]');
        } else {
            lessonElement = document.querySelector('[data-preview-lesson="' + lessonId + '"]');
        }
    }
    
    // Si on a trouvé un élément, récupérer ses données
    if (lessonElement) {
        title = lessonElement.getAttribute('data-preview-title') || '';
        section = lessonElement.getAttribute('data-preview-section') || '';
    }
    
    // Fonction pour décoder les entités HTML
    function decodeHtmlEntities(text) {
        if (!text) return '';
        const textarea = document.createElement('textarea');
        textarea.innerHTML = text;
        return textarea.value;
    }
    
    // Mettre à jour le titre dans le modal
    const titleElement = document.getElementById('previewLessonTitle');
    const sectionElement = document.getElementById('previewLessonSection');
    if (titleElement && title) {
        // Décoder les entités HTML avant d'afficher
        const decodedTitle = decodeHtmlEntities(title);
        titleElement.textContent = decodedTitle;
    }
    if (sectionElement) {
        if (section) {
            // Décoder les entités HTML avant d'afficher
            const decodedSection = decodeHtmlEntities(section);
            sectionElement.textContent = decodedSection;
            sectionElement.style.display = 'block';
        } else {
            sectionElement.style.display = 'none';
        }
    }
    
    // Mettre à jour l'état actif dans la liste des aperçus (si elle existe)
    document.querySelectorAll('.preview-item').forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('data-preview-lesson') == lessonId) {
            item.classList.add('active');
        }
    });
    
    // Cacher tous les players
    const allWrappers = document.querySelectorAll('.preview-player-wrapper');
    allWrappers.forEach(wrapper => {
        wrapper.style.display = 'none';
        wrapper.classList.remove('active');
    });
    
    // Afficher le player correspondant
    let targetWrapper = document.querySelector('.preview-player-wrapper[data-preview-id="' + lessonId + '"]');
    
    // Vérifier si le wrapper existe et s'il contient un player fonctionnel
    if (targetWrapper) {
        // Vérifier si le wrapper contient le message "Aucune vidéo YouTube disponible"
        const noVideoMessage = targetWrapper.querySelector('.text-center p.text-muted');
        if (noVideoMessage && noVideoMessage.textContent.includes('Aucune vidéo YouTube disponible')) {
            // Le wrapper existe mais n'a pas de player, le remplacer
            const previewVideoContainer = document.getElementById('previewVideoContainer');
            if (previewVideoContainer && lessonElement) {
                const youtubeId = lessonElement.getAttribute('data-preview-youtube-id') || '';
                const videoUrl = lessonElement.getAttribute('data-preview-video-url') || '';
                const isUnlisted = lessonElement.getAttribute('data-preview-is-unlisted') === '1';
                
                // Supprimer l'ancien wrapper
                targetWrapper.remove();
                targetWrapper = null;
                
                // Créer un nouveau wrapper avec un player fonctionnel
                if (youtubeId || videoUrl) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'preview-player-wrapper';
                    wrapper.setAttribute('data-preview-id', lessonId);
                    wrapper.style.display = 'none';
                    
                    if (youtubeId) {
                        const playerId = 'plyr-player-' + lessonId;
                        wrapper.innerHTML = `
                            <div class="plyr-player-wrapper plyr-external-video position-absolute top-0 start-0 w-100 h-100" id="wrapper-${playerId}">
                                <div class="plyr__video-embed" id="${playerId}" data-plyr-provider="youtube" data-plyr-embed-id="${youtubeId}"></div>
                            </div>
                        `;
                        previewVideoContainer.appendChild(wrapper);
                        initializePreviewPlayer(lessonId, youtubeId, isUnlisted);
                        targetWrapper = wrapper;
                    } else if (videoUrl) {
                        const playerId = 'plyr-player-' + lessonId;
                        wrapper.innerHTML = `
                            <div class="plyr-player-wrapper position-absolute top-0 start-0 w-100 h-100" id="wrapper-${playerId}">
                                <video id="${playerId}" class="plyr-player-video" playsinline>
                                    <source src="${videoUrl}" type="video/mp4">
                                </video>
                            </div>
                        `;
                        previewVideoContainer.appendChild(wrapper);
                        targetWrapper = wrapper;
                        
                        // Initialiser Plyr pour cette vidéo
                        setTimeout(function() {
                            const videoElement = document.getElementById(playerId);
                            if (videoElement && window.Plyr) {
                                try {
                                    const player = new Plyr(videoElement, {
                                        controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
                                        settings: ['quality', 'speed'],
                                        keyboard: { focused: true, global: false },
                                        tooltips: { controls: true, seek: true },
                                        clickToPlay: true,
                                        hideControls: true,
                                        resetOnEnd: false,
                                        disableContextMenu: true
                                    });
                                    window['plyr_' + playerId] = player;
                                    
                                    // Désactiver le menu contextuel
                                    const wrapperEl = document.getElementById('wrapper-' + playerId);
                                    if (wrapperEl) {
                                        wrapperEl.addEventListener('contextmenu', function(e) {
                                            e.preventDefault();
                                            return false;
                                        });
                                        wrapperEl.addEventListener('dragstart', function(e) {
                                            e.preventDefault();
                                            return false;
                                        });
                                    }
                                    
                                } catch (error) {
                                    console.error('❌ Error initializing Plyr for video:', error);
                                }
                            }
                        }, 200);
                    }
                }
            }
        }
    }
    
    // Si le wrapper n'existe toujours pas, le créer
    if (!targetWrapper && lessonElement && lessonId !== 0) {
        const previewVideoContainer = document.getElementById('previewVideoContainer');
        if (previewVideoContainer) {
            const youtubeId = lessonElement.getAttribute('data-preview-youtube-id') || '';
            const videoUrl = lessonElement.getAttribute('data-preview-video-url') || '';
            const isUnlisted = lessonElement.getAttribute('data-preview-is-unlisted') === '1';
            
            if (youtubeId || videoUrl) {
                const wrapper = document.createElement('div');
                wrapper.className = 'preview-player-wrapper';
                wrapper.setAttribute('data-preview-id', lessonId);
                wrapper.style.display = 'none';
                
                if (youtubeId) {
                    const playerId = 'plyr-player-' + lessonId;
                    wrapper.innerHTML = `
                        <div class="plyr-player-wrapper plyr-external-video position-absolute top-0 start-0 w-100 h-100" id="wrapper-${playerId}">
                            <div class="plyr__video-embed" id="${playerId}" data-plyr-provider="youtube" data-plyr-embed-id="${youtubeId}"></div>
                        </div>
                    `;
                    previewVideoContainer.appendChild(wrapper);
                    initializePreviewPlayer(lessonId, youtubeId, isUnlisted);
                    targetWrapper = wrapper;
                } else if (videoUrl) {
                    const playerId = 'plyr-player-' + lessonId;
                    wrapper.innerHTML = `
                        <div class="plyr-player-wrapper position-absolute top-0 start-0 w-100 h-100" id="wrapper-${playerId}">
                            <video id="${playerId}" class="plyr-player-video" playsinline>
                                <source src="${videoUrl}" type="video/mp4">
                            </video>
                        </div>
                    `;
                    previewVideoContainer.appendChild(wrapper);
                    targetWrapper = wrapper;
                    
                    // Initialiser Plyr pour cette vidéo
                    setTimeout(function() {
                        const videoElement = document.getElementById(playerId);
                        if (videoElement && window.Plyr) {
                            try {
                                const player = new Plyr(videoElement, {
                                    controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
                                    settings: ['quality', 'speed'],
                                    keyboard: { focused: true, global: false },
                                    tooltips: { controls: true, seek: true },
                                    clickToPlay: true,
                                    hideControls: true,
                                    resetOnEnd: false,
                                    disableContextMenu: true
                                });
                                window['plyr_' + playerId] = player;
                                
                                // Désactiver le menu contextuel
                                const wrapperEl = document.getElementById('wrapper-' + playerId);
                                if (wrapperEl) {
                                    wrapperEl.addEventListener('contextmenu', function(e) {
                                        e.preventDefault();
                                        return false;
                                    });
                                    wrapperEl.addEventListener('dragstart', function(e) {
                                        e.preventDefault();
                                        return false;
                                    });
                                }
                                
                            } catch (error) {
                                console.error('❌ Error initializing Plyr for video:', error);
                            }
                        }
                    }, 200);
                }
            }
        }
    }
    
    if (targetWrapper) {
        targetWrapper.style.display = 'block';
        targetWrapper.classList.add('active');
        
        // Initialiser Plyr si nécessaire
        setTimeout(function() {
            const playerElement = targetWrapper.querySelector('[id^="plyr-player-"]');
            if (playerElement && window.Plyr) {
                const playerId = playerElement.id;
                const playerKey = 'plyr_' + playerId;
                if (!window[playerKey]) {
                    const youtubeId = playerElement.getAttribute('data-plyr-embed-id');
                    if (youtubeId) {
                        // C'est une vidéo YouTube
                        const lessonId = targetWrapper.getAttribute('data-preview-id');
                        const isUnlisted = lessonElement?.getAttribute('data-preview-is-unlisted') === '1';
                        initializePreviewPlayer(lessonId, youtubeId, isUnlisted);
                    } else if (playerElement.tagName === 'VIDEO') {
                        // C'est une vidéo directe
                        try {
                            const player = new Plyr(playerElement, {
                                controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
                                settings: ['quality', 'speed'],
                                keyboard: { focused: true, global: false },
                                tooltips: { controls: true, seek: true },
                                clickToPlay: true,
                                hideControls: true,
                                resetOnEnd: false,
                                disableContextMenu: true
                            });
                            window[playerKey] = player;
                            
                            // Désactiver le menu contextuel
                            const wrapperEl = document.getElementById('wrapper-' + playerId);
                            if (wrapperEl) {
                                wrapperEl.addEventListener('contextmenu', function(e) {
                                    e.preventDefault();
                                    return false;
                                });
                                wrapperEl.addEventListener('dragstart', function(e) {
                                    e.preventDefault();
                                    return false;
                                });
                            }
                            
                        } catch (error) {
                            console.error('❌ Error initializing Plyr for video:', error);
                        }
                    }
                }
            }
        }, 200);
        
        // Sur mobile, scroller automatiquement vers le lecteur
        if (window.innerWidth < 992) {
            const modal = document.getElementById('coursePreviewModal');
            if (modal) {
                const modalBody = modal.querySelector('.modal-body');
                const previewVideoContainer = document.getElementById('previewVideoContainer');
                if (modalBody && previewVideoContainer) {
                    setTimeout(function() {
                        // Scroller dans le modal-body vers le conteneur vidéo
                        const containerTop = previewVideoContainer.offsetTop;
                        modalBody.scrollTo({
                            top: containerTop - 20,
                            behavior: 'smooth'
                        });
                    }, 150);
                }
            }
        }
        
        // Vérifier si le player Plyr existe et s'initialiser si nécessaire
        if (lessonId !== 0) {
            // Attendre un peu pour s'assurer que le DOM est prêt
            setTimeout(function() {
                const playerElement = targetWrapper.querySelector('[id^="plyr-player-"]');
                if (playerElement) {
                    const playerId = playerElement.id;
                    const playerKey = 'plyr_' + playerId;
                    if (!window[playerKey]) {
                        // Le player n'existe pas encore, l'initialiser
                        const lessonElementData = lessonElement || document.querySelector('[data-preview-lesson="' + lessonId + '"]');
                        if (lessonElementData) {
                            const youtubeId = lessonElementData.getAttribute('data-preview-youtube-id') || '';
                            const isUnlisted = lessonElementData.getAttribute('data-preview-is-unlisted') === '1';
                            if (youtubeId) {
                                initializePreviewPlayer(lessonId, youtubeId, isUnlisted);
                            } else {
                                console.warn('No YouTube ID found for lesson', lessonId);
                            }
                        }
                    }
                }
            }, 100);
        }
        
        // Arrêter les autres players s'ils sont en cours de lecture
        allWrappers.forEach(wrapper => {
            if (wrapper !== targetWrapper) {
                const playerElement = wrapper.querySelector('[id^="plyr-player-"], [id^="plyr-mobile-"]');
                if (playerElement) {
                    const playerKey = 'plyr_' + playerElement.id;
                    const existingPlayer = window[playerKey];
                    if (existingPlayer && typeof existingPlayer.pause === 'function') {
                        try {
                            existingPlayer.pause();
                        } catch (e) {
                            console.error('Could not pause player:', e);
                        }
                    }
                }
                // Arrêter aussi les vidéos HTML5
                const video = wrapper.querySelector('video');
                if (video) {
                    video.pause();
                }
            }
        });
    } else {
        console.warn('Player wrapper not found for lesson ID:', lessonId);
    }
    
    // Ouvrir le modal
    const modal = document.getElementById('coursePreviewModal');
    if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
        
        // Si le modal n'est pas encore ouvert, attendre qu'il s'ouvre avant de changer le player
        if (!modal.classList.contains('show')) {
            // Écouter l'événement d'ouverture du modal
            const handleModalShown = () => {
                // Une fois le modal ouvert, afficher le bon player
                setTimeout(() => {
                    const targetWrapper = document.querySelector('.preview-player-wrapper[data-preview-id="' + lessonId + '"]');
                    if (targetWrapper) {
                        // Cacher tous les players
                        document.querySelectorAll('.preview-player-wrapper').forEach(wrapper => {
                            wrapper.style.display = 'none';
                            wrapper.classList.remove('active');
                        });
                        // Afficher le player correspondant
                        targetWrapper.style.display = 'block';
                        targetWrapper.classList.add('active');
                        
                        // Initialiser Plyr si nécessaire
                        setTimeout(function() {
                            const playerElement = targetWrapper.querySelector('[id^="plyr-player-"]');
                            if (playerElement && window.Plyr) {
                                const playerId = playerElement.id;
                                const playerKey = 'plyr_' + playerId;
                                if (!window[playerKey]) {
                                    const youtubeId = playerElement.getAttribute('data-plyr-embed-id');
                                    if (youtubeId) {
                                        // C'est une vidéo YouTube
                                        const lessonId = targetWrapper.getAttribute('data-preview-id');
                                        const isUnlisted = document.querySelector('[data-preview-lesson="' + lessonId + '"]')?.getAttribute('data-preview-is-unlisted') === '1';
                                        initializePreviewPlayer(lessonId, youtubeId, isUnlisted);
                                    } else if (playerElement.tagName === 'VIDEO') {
                                        // C'est une vidéo directe
                                        try {
                                            const player = new Plyr(playerElement, {
                                                controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
                                                settings: ['quality', 'speed'],
                                                keyboard: { focused: true, global: false },
                                                tooltips: { controls: true, seek: true },
                                                clickToPlay: true,
                                                hideControls: true,
                                                resetOnEnd: false,
                                                disableContextMenu: true
                                            });
                                            window[playerKey] = player;
                                            
                                            // Désactiver le menu contextuel
                                            const wrapperEl = document.getElementById('wrapper-' + playerId);
                                            if (wrapperEl) {
                                                wrapperEl.addEventListener('contextmenu', function(e) {
                                                    e.preventDefault();
                                                    return false;
                                                });
                                                wrapperEl.addEventListener('dragstart', function(e) {
                                                    e.preventDefault();
                                                    return false;
                                                });
                                            }
                                            
                                        } catch (error) {
                                            console.error('❌ Error initializing Plyr for video:', error);
                                        }
                                    }
                                }
                            }
                        }, 200);
                        
                        // Fonction pour décoder les entités HTML
                        function decodeHtmlEntities(text) {
                            if (!text) return '';
                            const textarea = document.createElement('textarea');
                            textarea.innerHTML = text;
                            return textarea.value;
                        }
                        
                        // Mettre à jour le titre et la section
                        if (titleElement && title) {
                            const decodedTitle = decodeHtmlEntities(title);
                            titleElement.textContent = decodedTitle;
                        }
                        if (sectionElement) {
                            if (section) {
                                const decodedSection = decodeHtmlEntities(section);
                                sectionElement.textContent = decodedSection;
                                sectionElement.style.display = 'block';
                            } else {
                                sectionElement.style.display = 'none';
                            }
                        }
                        
                        // Sur mobile, scroller automatiquement vers le lecteur
                        if (window.innerWidth < 992) {
                            const modalBody = modal.querySelector('.modal-body');
                            const previewVideoContainer = document.getElementById('previewVideoContainer');
                            if (modalBody && previewVideoContainer) {
                                setTimeout(function() {
                                    // Scroller dans le modal-body vers le conteneur vidéo
                                    const containerTop = previewVideoContainer.offsetTop;
                                    modalBody.scrollTo({
                                        top: containerTop - 20,
                                        behavior: 'smooth'
                                    });
                                }, 200);
                            }
                        }
                    }
                    // Retirer l'écouteur après utilisation
                    modal.removeEventListener('shown.bs.modal', handleModalShown);
                }, 100);
            };
            
            modal.addEventListener('shown.bs.modal', handleModalShown);
        }
        
        // Ouvrir le modal
        bsModal.show();
        
        // S'assurer que la liste des aperçus est chargée
        const previewListContainer = document.getElementById('previewListContainer');
        if (previewListContainer && typeof loadPreviewList === 'function') {
            // Charger la liste si elle n'a pas encore été chargée
            const spinner = previewListContainer.querySelector('.spinner-border');
            if (spinner) {
                loadPreviewList();
            }
        }
    }
}

// Initialiser la vidéo principale dans le modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('coursePreviewModal');
    if (modal) {
        // Écouter l'événement 'shown.bs.modal' (après que le modal soit complètement affiché)
        modal.addEventListener('shown.bs.modal', function() {
            
            // Initialiser Plyr pour le lecteur principal (ID 0) s'il existe
            setTimeout(function() {
                const mainPlayerElement = document.querySelector('#plyr-player-0, [id^="plyr-player-0"]');
                if (mainPlayerElement && window.Plyr) {
                    const playerId = mainPlayerElement.id || 'plyr-player-0';
                    const playerKey = 'plyr_' + playerId;
                    if (!window[playerKey]) {
                        try {
                            // Vérifier si c'est une vidéo YouTube ou une vidéo directe
                            const isYouTube = mainPlayerElement.classList.contains('plyr__video-embed') || 
                                            mainPlayerElement.hasAttribute('data-plyr-provider');
                            
                            let playerConfig = {
                                controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
                                settings: ['quality', 'speed'],
                                keyboard: { focused: true, global: false },
                                tooltips: { controls: true, seek: true },
                                clickToPlay: true,
                                hideControls: true,
                                resetOnEnd: false,
                                disableContextMenu: true
                            };
                            
                            // Si c'est YouTube, ajouter la config YouTube
                            if (isYouTube) {
                                playerConfig.youtube = {
                                    noCookie: false,
                                    rel: 0,
                                    showinfo: 0,
                                    iv_load_policy: 3,
                                    modestbranding: 1,
                                    controls: 0,
                                    disablekb: 1,
                                    fs: 0,
                                    cc_load_policy: 0
                                };
                            }
                            
                            const player = new Plyr(mainPlayerElement, playerConfig);
                            window[playerKey] = player;
                            
                            // Désactiver le menu contextuel
                            const wrapper = document.getElementById('wrapper-' + playerId) || mainPlayerElement.closest('.plyr-player-wrapper');
                            if (wrapper) {
                                wrapper.addEventListener('contextmenu', function(e) {
                                    e.preventDefault();
                                    return false;
                                });
                                wrapper.addEventListener('dragstart', function(e) {
                                    e.preventDefault();
                                    return false;
                                });
                            }
                            
                        } catch (error) {
                            console.error('❌ Error initializing Plyr for main player:', error);
                        }
                    }
                }
            }, 300);
            
            const container = document.getElementById('previewListContainer');
            const contentWrapper = document.getElementById('previewListContent');
            const colLg4 = container ? container.closest('.col-lg-4') : null;
            
            if (container && colLg4) {
                // Calculer la hauteur disponible pour le conteneur
                const headerHeight = colLg4.querySelector('div:first-child')?.offsetHeight || 0;
                const colHeight = colLg4.offsetHeight;
                const availableHeight = colHeight - headerHeight;
                
                // Forcer une hauteur maximale pour permettre le scroll
                if (availableHeight > 0) {
                    container.style.maxHeight = availableHeight + 'px';
                    container.style.height = availableHeight + 'px';
                }
            }
            
            // Charger la liste des aperçus seulement si elle n'a pas déjà été chargée
            if (!previewListLoaded) {
                setTimeout(function() {
                    loadPreviewList();
                }, 300);
            }
        });
        
        // Écouter 'show.bs.modal' pour certaines actions
        modal.addEventListener('show.bs.modal', function() {
            @if($course->video_preview_url && !$course->video_preview_youtube_id)
                const video = document.getElementById('coursePreviewVideo');
                if (video) {
                    video.load();
                }
            @endif
            
            // Arrêter toutes les vidéos quand le modal s'ouvre (sauf celle active)
            document.querySelectorAll('.preview-player-wrapper').forEach(wrapper => {
                if (!wrapper.classList.contains('active')) {
                    const playerElement = wrapper.querySelector('[id^="plyr-player-"], [id^="plyr-mobile-"]');
                    if (playerElement) {
                        const playerKey = 'plyr_' + playerElement.id;
                        const existingPlayer = window[playerKey];
                        if (existingPlayer && typeof existingPlayer.pause === 'function') {
                            try {
                                existingPlayer.pause();
                            } catch (e) {
                                console.error('Erreur lors de l\'arrêt de la vidéo:', e);
                            }
                        }
                    }
                    const video = wrapper.querySelector('video');
                    if (video) {
                        video.pause();
                    }
                }
            });
        });
        
        // Réinitialiser le flag quand le modal se ferme
        modal.addEventListener('hidden.bs.modal', function() {
            previewListLoaded = false;
            isLoadingPreviewList = false;
        });
        
        // Réinitialiser à l'aperçu principal quand le modal se ferme
        modal.addEventListener('hidden.bs.modal', function() {
            // Arrêter toutes les vidéos
            document.querySelectorAll('.preview-player-wrapper').forEach(wrapper => {
                const playerId = wrapper.querySelector('[id^="plyr-player-"], [id^="plyr-mobile-"]');
                if (playerId) {
                    const playerKey = 'plyr_' + playerId.id;
                    const existingPlayer = window[playerKey];
                    if (existingPlayer && typeof existingPlayer.pauseVideo === 'function') {
                        try {
                            existingPlayer.pauseVideo();
                        } catch (e) {
                            // Ignorer les erreurs
                        }
                    }
                }
                const video = wrapper.querySelector('video');
                if (video) {
                    video.pause();
                }
            });
            
            @if($course->video_preview_youtube_id || $course->video_preview_url)
                // Réinitialiser à l'aperçu principal
                const allWrappers = document.querySelectorAll('.preview-player-wrapper');
                allWrappers.forEach(wrapper => {
                    wrapper.style.display = 'none';
                    wrapper.classList.remove('active');
                });
                const mainWrapper = document.querySelector('.preview-player-wrapper[data-preview-id="0"]');
                if (mainWrapper) {
                    mainWrapper.style.display = 'block';
                    mainWrapper.classList.add('active');
                }
                
                // Réinitialiser le titre
                const titleElement = document.getElementById('previewLessonTitle');
                const sectionElement = document.getElementById('previewLessonSection');
                if (titleElement) {
                    // Décoder les entités HTML pour afficher correctement les caractères spéciaux
                    const courseTitle = @json($course->title);
                    titleElement.textContent = courseTitle;
                }
                if (sectionElement) {
                    sectionElement.style.display = 'none';
                }
                
                // Réinitialiser l'état actif dans la liste
                document.querySelectorAll('.preview-item').forEach(item => {
                    item.classList.remove('active');
                });
                const mainPreviewItem = document.querySelector('[data-preview-lesson="0"]');
                if (mainPreviewItem) {
                    mainPreviewItem.classList.add('active');
                }
            @endif
        });
    }
    
    // Initialiser les compteurs à rebours pour les promotions
    if (typeof window.initPromotionCountdowns === 'function') {
        window.initPromotionCountdowns();
    }
    
    // Gérer le collapse des sections du curriculum avec JavaScript vanilla
    const curriculumHeaders = document.querySelectorAll('.curriculum-section-header');
    curriculumHeaders.forEach(function(header) {
        const sectionId = header.getAttribute('data-section-id');
        // Vérifier que sectionId existe et n'est pas vide avant d'utiliser querySelector
        if (!sectionId || sectionId.trim() === '') return;
        
        const targetElement = document.querySelector('#' + sectionId);
        
        if (!targetElement) return;
        
        // Initialiser l'état au chargement
        const isExpanded = header.getAttribute('aria-expanded') === 'true';
        if (isExpanded && !targetElement.classList.contains('is-open')) {
            targetElement.classList.add('is-open');
        } else if (!isExpanded && targetElement.classList.contains('is-open')) {
            targetElement.classList.remove('is-open');
        }
        
        // Fonction pour toggle
        function toggleSection() {
            const isCurrentlyExpanded = targetElement.classList.contains('is-open');
            
            if (isCurrentlyExpanded) {
                targetElement.classList.remove('is-open');
                header.setAttribute('aria-expanded', 'false');
            } else {
                targetElement.classList.add('is-open');
                header.setAttribute('aria-expanded', 'true');
            }
        }
        
        // Gérer le clic sur le header
        header.addEventListener('click', function(e) {
            // Empêcher si le clic est sur les stats
            if (e.target.closest('.curriculum-section-stats')) {
                return;
            }
            
            e.preventDefault();
            e.stopPropagation();
            toggleSection();
        });
        
        // Support clavier
        header.addEventListener('keydown', function(e) {
            if (e.target.closest('.curriculum-section-stats')) {
                return;
            }
            
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                e.stopPropagation();
                toggleSection();
            }
        });
    });
});

// Désactiver le menu contextuel et empêcher le téléchargement sur tous les lecteurs du modal
document.addEventListener('DOMContentLoaded', function() {
    function disableContextMenuOnPlayers() {
        const players = document.querySelectorAll('.plyr-player-container');
        players.forEach(function(container) {
            // Désactiver le clic droit
            container.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }, false);
            
            // Empêcher le drag and drop
            container.addEventListener('dragstart', function(e) {
                e.preventDefault();
                return false;
            }, false);
            
            // Empêcher la sélection
            container.addEventListener('selectstart', function(e) {
                e.preventDefault();
                return false;
            }, false);
            
            // Empêcher le copier-coller
            container.addEventListener('copy', function(e) {
                e.preventDefault();
                return false;
            }, false);
            
            container.addEventListener('cut', function(e) {
                e.preventDefault();
                return false;
            }, false);
        });
    }
    
    // Appliquer immédiatement
    disableContextMenuOnPlayers();
    
    // Observer les nouveaux lecteurs ajoutés dynamiquement
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    if (node.classList && node.classList.contains('plyr-player-container')) {
                        disableContextMenuOnPlayers();
                    } else if (node.querySelectorAll) {
                        const newPlayers = node.querySelectorAll('.plyr-player-container');
                        if (newPlayers.length > 0) {
                            disableContextMenuOnPlayers();
                        }
                    }
                }
            });
        });
    });
    
    // Observer le modal pour les nouveaux lecteurs
    const modal = document.getElementById('coursePreviewModal');
    if (modal) {
        observer.observe(modal, {
            childList: true,
            subtree: true
        });
        
        // Réappliquer quand le modal s'ouvre
        modal.addEventListener('shown.bs.modal', function() {
            setTimeout(disableContextMenuOnPlayers, 100);
        });
    }
    
    // Script global pour forcer les tooltips Plyr en français dans le modal de preview
    let isUpdatingTooltips = false; // Flag pour éviter les boucles infinies
    function updateAllPlyrTooltips() {
        if (isUpdatingTooltips) return; // Éviter les appels simultanés
        isUpdatingTooltips = true;
        
        try {
            const tooltipMap = {
                'Play': 'Lire',
                'Pause': 'Pause',
                'Restart': 'Redémarrer',
                'Rewind': 'Rembobiner',
                'Fast forward': 'Avance rapide',
                'Mute': 'Couper le son',
                'Unmute': 'Activer le son',
                'Volume': 'Volume',
                'Enter fullscreen': 'Plein écran',
                'Exit fullscreen': 'Quitter le plein écran',
                'Settings': 'Paramètres',
                'Picture in picture': 'Image dans l\'image',
                'Download': 'Télécharger',
                'Captions': 'Sous-titres',
                'Enable captions': 'Activer les sous-titres',
                'Disable captions': 'Désactiver les sous-titres',
                'Quality': 'Qualité',
                'Speed': 'Vitesse',
                'Normal': 'Normal'
            };
            
            // Mettre à jour tous les boutons Plyr (seulement si pas déjà en français)
            document.querySelectorAll('.plyr__control[data-plyr]').forEach(function(control) {
                const ariaLabel = control.getAttribute('aria-label');
                if (ariaLabel && tooltipMap[ariaLabel] && ariaLabel !== tooltipMap[ariaLabel]) {
                    control.setAttribute('aria-label', tooltipMap[ariaLabel]);
                    control.setAttribute('title', tooltipMap[ariaLabel]);
                }
            });
            
            // Mettre à jour tous les tooltips générés par Plyr (seulement si pas déjà en français)
            document.querySelectorAll('.plyr__tooltip').forEach(function(tooltip) {
                const text = tooltip.textContent.trim();
                for (const [key, value] of Object.entries(tooltipMap)) {
                    if (text === key && text !== value) {
                        tooltip.textContent = value;
                        break;
                    }
                }
            });
        } catch (e) {
            console.error('Erreur lors de la mise à jour des tooltips:', e);
        } finally {
            isUpdatingTooltips = false;
        }
    }
    
    // Exécuter quand le modal est ouvert
    const previewModal = document.getElementById('coursePreviewModal');
    if (previewModal) {
        previewModal.addEventListener('shown.bs.modal', function() {
            setTimeout(updateAllPlyrTooltips, 200);
            setTimeout(updateAllPlyrTooltips, 500);
        });
    }
    
    // Observer les changements du DOM pour mettre à jour les tooltips (avec debounce)
    let tooltipUpdateTimeout;
    const tooltipObserver = new MutationObserver(function() {
        clearTimeout(tooltipUpdateTimeout);
        tooltipUpdateTimeout = setTimeout(updateAllPlyrTooltips, 100);
    });
    
    tooltipObserver.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: false // Ne pas observer les attributs pour éviter les boucles
    });
    
    // Exécuter aussi au chargement initial
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(updateAllPlyrTooltips, 500);
        });
    } else {
        setTimeout(updateAllPlyrTooltips, 500);
    }
});

// Rating System JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const ratingStars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('ratingInput');
    const ratingText = document.getElementById('ratingText');
    
    if (ratingStars.length > 0 && ratingInput && ratingText) {
        let currentRating = parseInt(ratingInput.value) || 0;
        
        // Fonction pour mettre à jour l'affichage des étoiles
        function updateStars(rating) {
            ratingStars.forEach((star, index) => {
                const starValue = index + 1;
                if (starValue <= rating) {
                    star.classList.add('active');
                    star.style.color = 'var(--warning-color, #ffc107)';
                } else {
                    star.classList.remove('active');
                    star.style.color = '#ddd';
                }
            });
        }
        
        // Fonction pour mettre à jour le texte
        function updateRatingText(rating) {
            if (rating === 0) {
                ratingText.textContent = 'Sélectionnez une note';
            } else {
                ratingText.textContent = rating + ' étoile' + (rating > 1 ? 's' : '');
            }
        }
        
        // Initialiser l'affichage
        updateStars(currentRating);
        updateRatingText(currentRating);
        
        // Ajouter les événements sur chaque étoile
        ratingStars.forEach((star, index) => {
            const starValue = index + 1;
            
            star.addEventListener('click', function() {
                currentRating = starValue;
                ratingInput.value = currentRating;
                updateStars(currentRating);
                updateRatingText(currentRating);
            });
            
            star.addEventListener('mouseenter', function() {
                updateStars(starValue);
            });
        });
        
        // Réinitialiser au survol de la zone
        const ratingWrapper = document.querySelector('.rating-stars-input');
        if (ratingWrapper) {
            ratingWrapper.addEventListener('mouseleave', function() {
                updateStars(currentRating);
            });
        }
    }
    
    // Gestion de la suppression d'avis
    const deleteReviewBtn = document.getElementById('deleteReviewBtn');
    if (deleteReviewBtn) {
        deleteReviewBtn.addEventListener('click', function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer votre avis ? Cette action est irréversible.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("courses.review.destroy", $course->slug) }}';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                
                form.appendChild(csrfInput);
                form.appendChild(methodInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
});
</script>
@endpush


