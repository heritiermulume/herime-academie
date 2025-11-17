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

@media (max-width: 991.98px) {
    .course-details-page {
        margin-top: 0 !important;
        padding-top: 0 !important;
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
}

.preview-player-wrapper:not(.active) {
    display: none !important;
}

/* Styles personnalisés pour le lecteur Plyr dans le modal */
:root {
    --plyr-color-main: #ffcc33;
    --plyr-video-background: #000;
    --plyr-video-controls-background: linear-gradient(to top, rgba(0, 51, 102, 0.95) 0%, rgba(0, 51, 102, 0.7) 50%, transparent 100%);
    --plyr-video-control-color: #ffffff;
    --plyr-video-control-color-hover: #ffcc33;
    --plyr-audio-controls-background: rgba(0, 51, 102, 0.95);
    --plyr-audio-control-color: #ffffff;
    --plyr-menu-background: rgba(0, 51, 102, 0.95);
    --plyr-menu-color: #ffffff;
    --plyr-menu-shadow: 0 4px 16px rgba(0, 0, 0, 0.5);
    --plyr-tooltip-background: rgba(0, 51, 102, 0.95);
    --plyr-tooltip-color: #ffffff;
    --plyr-progress-loading-background: rgba(255, 255, 255, 0.25);
    --plyr-progress-buffered-background: rgba(255, 255, 255, 0.4);
    --plyr-range-thumb-background: #ffcc33;
    --plyr-range-thumb-active-shadow-width: 0 0 12px rgba(255, 204, 51, 0.6);
    --plyr-range-track-height: 6px;
    --plyr-range-thumb-height: 18px;
    --plyr-range-thumb-width: 18px;
    --plyr-control-icon-size: 20px;
    --plyr-control-spacing: 10px;
    --plyr-control-radius: 50%;
    --plyr-control-padding: 8px;
    --plyr-video-controls-padding: 15px 10px;
    
    /* Variables pour compatibilité */
    --plyr-primary-color: #003366;
    --plyr-accent-color: #ffcc33;
    --plyr-bg-dark: rgba(0, 51, 102, 0.95);
    --plyr-bg-light: rgba(0, 51, 102, 0.7);
}

/* Styles Plyr personnalisés */
.plyr {
    width: 100%;
    height: 100%;
    border-radius: 8px;
    overflow: hidden;
}

.plyr__video-wrapper {
    background: #000;
}

/* Contrôles Plyr personnalisés */
.plyr__controls {
    background: var(--plyr-video-controls-background) !important;
    backdrop-filter: blur(10px);
    padding: var(--plyr-video-controls-padding) !important;
}

.plyr__control {
    background: rgba(0, 51, 102, 0.95) !important;
    border: 2px solid rgba(255, 255, 255, 0.3) !important;
    color: white !important;
    border-radius: 50% !important;
    width: 40px !important;
    height: 40px !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
}

.plyr__control:hover {
    background: var(--plyr-color-main) !important;
    color: #003366 !important;
    border-color: var(--plyr-color-main) !important;
    transform: scale(1.15) !important;
    box-shadow: 0 4px 12px rgba(255, 204, 51, 0.5) !important;
}

.plyr__control:focus {
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(255, 204, 51, 0.3) !important;
}

.plyr__control[aria-pressed="true"] {
    background: var(--plyr-color-main) !important;
    color: #003366 !important;
}

/* Barre de progression */
.plyr__progress__container {
    height: 6px !important;
    cursor: pointer;
}

.plyr__progress__container:hover {
    height: 8px !important;
}

.plyr__progress__buffer {
    background: rgba(255, 255, 255, 0.4) !important;
}

.plyr__progress__played {
    background: linear-gradient(90deg, var(--plyr-color-main) 0%, #ff9933 100%) !important;
    box-shadow: 0 0 8px rgba(255, 204, 51, 0.5) !important;
}

.plyr__progress__container:hover .plyr__progress__played {
    box-shadow: 0 0 12px rgba(255, 204, 51, 0.7) !important;
}

/* Volume */
.plyr__volume {
    max-width: 90px;
}

.plyr__volume input[type="range"] {
    color: var(--plyr-color-main) !important;
}

.plyr__volume input[type="range"]::-webkit-slider-thumb {
    background: var(--plyr-color-main) !important;
    border: 2px solid white !important;
    box-shadow: 0 2px 6px rgba(0, 51, 102, 0.4) !important;
}

.plyr__volume input[type="range"]::-moz-range-thumb {
    background: var(--plyr-color-main) !important;
    border: 2px solid white !important;
    box-shadow: 0 2px 6px rgba(0, 51, 102, 0.4) !important;
}

/* Temps */
.plyr__time {
    color: white !important;
    font-weight: 600 !important;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8) !important;
    letter-spacing: 0.5px !important;
}

/* Menu (qualité, etc.) */
.plyr__menu {
    background: var(--plyr-menu-background) !important;
    border: 2px solid var(--plyr-color-main) !important;
    border-radius: 8px !important;
    box-shadow: var(--plyr-menu-shadow) !important;
    backdrop-filter: blur(10px) !important;
}

.plyr__menu__container button {
    color: white !important;
    transition: all 0.2s ease !important;
}

.plyr__menu__container button:hover,
.plyr__menu__container button[aria-checked="true"] {
    background: rgba(255, 204, 51, 0.15) !important;
    color: var(--plyr-color-main) !important;
}

.plyr__menu__container button[aria-checked="true"] {
    background: rgba(255, 204, 51, 0.25) !important;
    font-weight: 700 !important;
    border-left: 3px solid var(--plyr-color-main) !important;
}

/* Tooltip */
.plyr__tooltip {
    background: var(--plyr-tooltip-background) !important;
    color: var(--plyr-tooltip-color) !important;
    border: 1px solid var(--plyr-color-main) !important;
}

/* Bouton plein écran */
.plyr__control[data-plyr="fullscreen"] {
    background: rgba(0, 51, 102, 0.95) !important;
}

.plyr__control[data-plyr="fullscreen"]:hover {
    background: var(--plyr-color-main) !important;
}

/* Styles responsives pour mobile et tablette */
@media (max-width: 991.98px) {
    /* Réduire la taille des contrôles */
    .plyr__control {
        width: 32px !important;
        height: 32px !important;
        padding: 6px !important;
    }
    
    .plyr__control svg {
        width: 16px !important;
        height: 16px !important;
    }
    
    /* Réduire le padding des contrôles */
    .plyr__controls {
        padding: 10px 8px !important;
        gap: 6px !important;
    }
    
    /* Réduire la taille de la barre de progression */
    .plyr__progress__container {
        height: 4px !important;
    }
    
    .plyr__progress__container:hover {
        height: 5px !important;
    }
    
    /* Réduire la taille du volume */
    .plyr__volume {
        max-width: 70px !important;
    }
    
    /* Réduire la taille du texte */
    .plyr__time {
        font-size: 0.75rem !important;
        padding: 0 4px !important;
    }
    
    /* Ajuster le menu des paramètres pour mobile */
    .plyr__menu {
        min-width: 140px !important;
        max-width: 90vw !important;
        font-size: 0.875rem !important;
    }
    
    .plyr__menu__container {
        padding: 0.25rem 0 !important;
    }
    
    .plyr__menu__container button {
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
    }
    
    /* S'assurer que le menu s'affiche correctement */
    .plyr__menu[data-plyr="settings"],
    .plyr__menu[data-plyr="captions"],
    .plyr__menu[data-plyr="quality"],
    .plyr__menu[data-plyr="speed"] {
        position: absolute !important;
        bottom: 100% !important;
        left: auto !important;
        right: 0 !important;
        margin-bottom: 8px !important;
        z-index: 1000 !important;
        max-height: 50vh !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }
    
    /* S'assurer que le conteneur du menu ne coupe pas le contenu */
    .plyr__menu__container {
        max-height: 100% !important;
        overflow: visible !important;
    }
    
    /* Ajuster le wrapper du menu pour éviter les coupures */
    .plyr__control[aria-haspopup="true"] {
        position: relative !important;
    }
    
    /* S'assurer que le conteneur du lecteur permet au menu de s'afficher */
    .plyr-player-container,
    .plyr-player-wrapper {
        overflow: visible !important;
    }
    
    .plyr-player-container .plyr,
    .plyr-player-wrapper .plyr {
        overflow: visible !important;
    }
    
    .plyr-player-container .plyr__video-wrapper,
    .plyr-player-wrapper .plyr__video-wrapper {
        overflow: hidden !important;
    }
    
    /* S'assurer que les contrôles ont assez d'espace */
    .plyr__controls {
        position: relative !important;
        z-index: 10 !important;
    }
    
    /* S'assurer que le conteneur du modal permet au menu de s'afficher */
    .modal-fixed-height .modal-body .col-lg-8 #previewVideoContainer {
        overflow: visible !important;
    }
    
    .modal-fixed-height .modal-body .col-lg-8 #previewVideoContainer .ratio {
        overflow: visible !important;
    }
    
    /* Bouton play large */
    .plyr__control--overlaid {
        width: 60px !important;
        height: 60px !important;
    }
    
    .plyr__control--overlaid svg {
        width: 24px !important;
        height: 24px !important;
    }
}

/* Styles pour très petits écrans */
@media (max-width: 575.98px) {
    /* Encore plus petit pour les très petits écrans */
    .plyr__control {
        width: 28px !important;
        height: 28px !important;
        padding: 5px !important;
    }
    
    .plyr__control svg {
        width: 14px !important;
        height: 14px !important;
    }
    
    .plyr__controls {
        padding: 8px 6px !important;
        gap: 4px !important;
    }
    
    .plyr__time {
        font-size: 0.7rem !important;
        padding: 0 3px !important;
    }
    
    .plyr__volume {
        max-width: 60px !important;
    }
    
    .plyr__menu {
        min-width: 120px !important;
        max-width: 85vw !important;
        font-size: 0.8rem !important;
    }
    
    .plyr__menu__container button {
        padding: 0.4rem 0.6rem !important;
        font-size: 0.8rem !important;
    }
    
    /* Menu des paramètres pour très petits écrans */
    .plyr__menu[data-plyr="settings"],
    .plyr__menu[data-plyr="captions"],
    .plyr__menu[data-plyr="quality"],
    .plyr__menu[data-plyr="speed"] {
        max-height: 40vh !important;
    }
    
    .plyr__control--overlaid {
        width: 50px !important;
        height: 50px !important;
    }
    
    .plyr__control--overlaid svg {
        width: 20px !important;
        height: 20px !important;
    }
    
    /* Barre de progression encore plus fine */
    .plyr__progress__container {
        height: 3px !important;
    }
    
    .plyr__progress__container:hover {
        height: 4px !important;
    }
}

/* Video Player Container - Style charte graphique */
.plyr-player-container {
    width: 100% !important;
    height: 100% !important;
    display: block !important;
    visibility: visible !important;
    position: relative;
    background: #000;
    border-radius: 8px;
    overflow: visible !important; /* Permettre au menu de s'afficher */
    box-shadow: 0 4px 20px rgba(0, 51, 102, 0.3);
    border: 2px solid rgba(0, 51, 102, 0.2);
    transition: box-shadow 0.3s ease, border-color 0.3s ease;
}

/* Le wrapper vidéo peut avoir overflow hidden, mais pas le conteneur */
.plyr-player-container .plyr {
    overflow: hidden;
}

.plyr-player-container .plyr__video-wrapper {
    overflow: hidden;
}

.plyr-player-container:hover {
    box-shadow: 0 6px 30px rgba(0, 51, 102, 0.4);
    border-color: rgba(255, 204, 51, 0.3);
}

/* Désactiver le menu contextuel pour empêcher le téléchargement */
.plyr-player-container,
.plyr-player-container * {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.youtube-iframe-container {
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.youtube-iframe-container iframe {
    pointer-events: none;
}

/* Vidéos directes avec Plyr */
.plyr-player-video {
    width: 100%;
    height: 100%;
    border-radius: 8px;
}

/* Contrôles personnalisés */
.custom-video-controls {
    top: 0;
    left: 0;
    z-index: 100;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.plyr-player-container:hover .custom-video-controls {
    opacity: 1;
    pointer-events: auto;
}

.video-controls-bottom {
    background: linear-gradient(to top, var(--plyr-bg-dark) 0%, var(--plyr-bg-light) 50%, transparent 100%);
    padding: 15px 10px;
    z-index: 101;
    backdrop-filter: blur(10px);
}

/* Progress bar - Style charte graphique */
.video-progress-bar {
    position: relative;
    height: 6px;
    cursor: pointer;
    transition: height 0.2s ease;
    border-radius: 3px;
    background: rgba(255, 255, 255, 0.2);
}

.video-progress-bar:hover {
    height: 8px;
}

.video-progress-track {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 3px;
    pointer-events: none;
}

.video-progress-buffered {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 3px;
    width: 0%;
    pointer-events: none;
}

.video-progress-filled {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: linear-gradient(90deg, var(--plyr-accent-color) 0%, #ff9933 100%);
    border-radius: 3px;
    width: 0%;
    transition: width 0.1s linear;
    pointer-events: none;
    box-shadow: 0 0 8px rgba(255, 204, 51, 0.5);
}

.video-progress-handle {
    position: absolute;
    top: 50%;
    left: 0%;
    transform: translate(-50%, -50%);
    width: 18px;
    height: 18px;
    background: var(--plyr-accent-color);
    border: 3px solid white;
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.2s ease, transform 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 51, 102, 0.5), 0 0 12px rgba(255, 204, 51, 0.6);
    pointer-events: auto;
}

.video-progress-bar:hover .video-progress-handle {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1.3);
}

.control-btn {
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.3);
    background: var(--plyr-bg-dark);
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.control-btn:hover {
    background: var(--plyr-accent-color);
    color: var(--plyr-primary-color);
    border-color: var(--plyr-accent-color);
    transform: scale(1.15);
    box-shadow: 0 4px 12px rgba(255, 204, 51, 0.5);
}

.control-btn:active {
    transform: scale(1.05);
}

.control-btn i {
    font-size: 0.9rem;
}

.video-time {
    font-size: 0.875rem;
    font-weight: 600;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
    color: white;
    letter-spacing: 0.5px;
}

/* Volume control - Style charte graphique */
.volume-control {
    position: relative;
}

.volume-slider-container {
    width: 0;
    overflow: hidden;
    transition: width 0.3s ease;
}

.volume-control:hover .volume-slider-container {
    width: 90px;
}

.volume-slider {
    position: relative;
    width: 90px;
    height: 5px;
    cursor: pointer;
    margin: 0 12px;
}

.volume-slider-track {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 3px;
}

.volume-slider-fill {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: linear-gradient(90deg, var(--plyr-accent-color) 0%, #ff9933 100%);
    border-radius: 3px;
    width: 100%;
    transition: width 0.1s linear;
    box-shadow: 0 0 6px rgba(255, 204, 51, 0.4);
}

.volume-slider-handle {
    position: absolute;
    top: 50%;
    left: 0;
    transform: translate(-50%, -50%);
    width: 14px;
    height: 14px;
    background: var(--plyr-accent-color);
    border: 2px solid white;
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.2s ease;
    box-shadow: 0 2px 6px rgba(0, 51, 102, 0.4);
}

.volume-slider:hover .volume-slider-handle {
    opacity: 1;
}

/* Quality dropdown - Style charte graphique */
.quality-dropdown .dropdown-toggle {
    border: none;
}

.quality-dropdown .dropdown-toggle::after {
    display: none;
}

.quality-dropdown .dropdown-menu {
    background: var(--plyr-bg-dark);
    border: 2px solid var(--plyr-accent-color);
    min-width: 120px;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(10px);
    padding: 0.5rem 0;
}

.quality-dropdown .dropdown-item {
    color: white;
    padding: 0.65rem 1.25rem;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.quality-dropdown .dropdown-item:hover {
    background: rgba(255, 204, 51, 0.15);
    color: var(--plyr-accent-color);
    padding-left: 1.5rem;
}

.quality-dropdown .dropdown-item.active {
    background: rgba(255, 204, 51, 0.25);
    color: var(--plyr-accent-color);
    font-weight: 700;
    border-left: 3px solid var(--plyr-accent-color);
}

.plyr-loading .spinner-border {
    color: var(--plyr-accent-color) !important;
    border-width: 3px;
}

.video-watermark {
    background: var(--plyr-bg-dark) !important;
    border: 2px solid var(--plyr-accent-color);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
}

@media (max-width: 768px) {
    .control-btn {
        width: 36px;
        height: 36px;
    }
    
    .control-btn i {
        font-size: 0.8rem;
    }
    
    .video-time {
        font-size: 0.75rem;
    }
    
    .video-controls-bottom {
        padding: 12px 8px;
    }
    
    .video-progress-bar {
        height: 5px;
    }
    
    .video-progress-bar:hover {
        height: 6px;
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
        gap: 0.75rem !important;
    }
    
    /* Conteneur vidéo - prend l'espace nécessaire */
    .modal-fixed-height .modal-body .col-lg-8 #previewVideoContainer {
        flex: 0 0 auto !important;
        width: 100% !important;
        margin-bottom: 0.75rem !important;
    }
    
    /* Forcer le ratio à ne pas dépasser une certaine hauteur */
    .modal-fixed-height .modal-body .col-lg-8 #previewVideoContainer.ratio {
        max-height: 70vh !important;
        overflow: visible !important; /* Permettre au menu de s'afficher */
    }
    
    /* S'assurer que le ratio ne coupe pas le menu */
    .modal-fixed-height .modal-body .col-lg-8 #previewVideoContainer.ratio .plyr-player-container,
    .modal-fixed-height .modal-body .col-lg-8 #previewVideoContainer.ratio .plyr-player-wrapper {
        overflow: visible !important;
    }
    
    /* Info de la leçon - hauteur flexible mais limitée */
    .modal-fixed-height .modal-body .col-lg-8 #previewLessonInfo {
        flex: 0 0 auto !important;
        flex-shrink: 0 !important;
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

.mobile-countdown {
    font-size: 0.7rem;
    background: transparent;
    border: none;
    padding: 0.25rem 0;
    margin-top: 0.25rem;
}

.mobile-countdown .countdown-text {
    font-size: 0.7rem;
    gap: 0.125rem;
}

.mobile-countdown .countdown-text span {
    min-width: auto;
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
.mobile-payment-btn {
    position: fixed;
    bottom: 60px; /* Au-dessus de la navigation mobile en bas */
    left: 0;
    right: 0;
    background: white;
    border-top: 2px solid var(--border-color);
    padding: 0.625rem 1rem;
    z-index: 999; /* Sous la navigation mobile mais au-dessus du contenu */
    box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.15);
    display: none;
    box-sizing: border-box;
}

@media (max-width: 991.98px) {
    .mobile-payment-btn {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
}

@media (max-width: 575.98px) {
    .mobile-payment-btn {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }
}

.mobile-payment-btn .container-fluid {
    padding-left: 0 !important;
    padding-right: 0 !important;
    width: 100%;
    box-sizing: border-box;
}

.mobile-payment-btn .row {
    margin-left: 0;
    margin-right: 0;
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.mobile-price-col {
    flex: 1;
    min-width: 0;
    padding-left: 0;
    padding-right: 0.75rem;
}

.mobile-btn-col {
    flex-shrink: 0;
    padding-left: 0;
    padding-right: 0;
    margin-left: auto;
}

.mobile-price {
    width: 100%;
}

.mobile-price-label {
    font-size: 0.65rem;
    color: var(--text-muted);
    margin-bottom: 0.125rem;
    display: block;
    line-height: 1.2;
}

.mobile-price-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0;
    line-height: 1.3;
}

.mobile-price-value {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mobile-price-original {
    font-size: 0.85rem;
    color: var(--text-muted);
    text-decoration: line-through;
    line-height: 1.2;
}

.mobile-price-discount {
    font-size: 0.7rem;
    color: var(--danger-color);
    font-weight: 600;
    margin-top: 0.125rem;
    line-height: 1.2;
}

.mobile-payment-btn .btn {
    flex-shrink: 0;
    white-space: nowrap;
    padding: 0.5rem 0.75rem;
    font-size: 0.7rem;
    min-width: 100px;
    max-width: 130px;
    width: 100%;
    text-align: center;
    line-height: 1.2;
    overflow: visible;
    text-overflow: clip;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
}

.mobile-payment-btn .btn i {
    font-size: 0.7rem;
    flex-shrink: 0;
    margin-right: 0.3rem;
}

.mobile-payment-btn .btn,
.mobile-payment-btn .btn-group,
.mobile-payment-btn .d-grid {
    max-width: 130px;
    min-width: 100px;
    width: 100%;
    box-sizing: border-box;
}

.mobile-payment-btn .btn-group .btn,
.mobile-payment-btn .d-grid .btn {
    font-size: 0.65rem;
    padding: 0.4rem 0.6rem;
    min-width: auto;
    max-width: 100%;
    white-space: nowrap;
    line-height: 1.2;
    height: 36px;
    overflow: visible;
    text-overflow: clip;
    box-sizing: border-box;
}

.mobile-payment-btn .d-grid {
    gap: 0.3rem !important;
}

/* S'assurer que les boutons ne dépassent pas */
.mobile-payment-btn > .container > div:last-child,
.mobile-payment-btn .mobile-btn-col {
    max-width: 130px;
    min-width: 100px;
    flex-shrink: 0;
    box-sizing: border-box;
    margin-left: auto;
}

/* Forcer le respect des marges */
.mobile-payment-btn * {
    box-sizing: border-box;
}

/* Responsive Design */
@media (max-width: 991.98px) {
    .main-content {
        padding-bottom: 140px; /* Espace pour le bouton de paiement (80px) + navigation mobile (60px) */
    }
    
    .course-hero {
        padding: 0.75rem 0 1.5rem;
        margin-bottom: 1.5rem;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    .course-title-hero {
        font-size: 1.25rem;
        line-height: 1.3;
        margin-bottom: 0.625rem;
    }
    
    .course-stats-hero {
        gap: 0.75rem;
    }
    
    .course-stat-item {
        font-size: 0.8125rem;
    }
    
    .course-stat-item i {
        font-size: 0.875rem;
    }
    
    .breadcrumb-modern {
        padding: 0.5rem 0.75rem;
        margin-bottom: 0.75rem;
        margin-top: 0.5rem;
        font-size: 0.75rem;
    }
    
    .course-badges {
        gap: 0.4rem;
        margin-bottom: 0.875rem;
    }
    
    .course-badge {
        padding: 0.35rem 0.75rem;
        font-size: 0.7rem;
    }
    
    .mobile-payment-btn {
        display: flex;
    }
    
    .course-sidebar {
        position: relative;
        top: 0;
    }
    
    .sidebar-card {
        margin-bottom: 1rem;
        padding: 1rem;
    }
    
    .sidebar-card .btn,
    .sidebar-card .btn-lg,
    .sidebar-card .btn-sm {
        padding: 0.45rem 0.75rem;
        font-size: 0.8rem;
        width: 100%;
    }
    
    .sidebar-card .d-grid {
        gap: 0.5rem !important;
    }
    
    .sidebar-card .d-grid .btn {
        padding: 0.45rem 0.65rem;
        font-size: 0.8rem;
    }
    
    .price-display {
        padding-bottom: 1rem;
        margin-bottom: 1rem;
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
}

@media (max-width: 767.98px) {
    .course-hero {
        padding: 1.25rem 0 1.75rem;
    }
    
    .course-title-hero {
        font-size: 1.5rem;
        margin-bottom: 0.75rem;
    }
    
    .content-card {
        padding: 1rem;
        margin-bottom: 1.25rem;
    }
    
    .section-title-modern {
        font-size: 1rem;
        margin-bottom: 0.875rem;
        padding-bottom: 0.5rem;
    }
    
    .video-preview-wrapper {
        margin-bottom: 0;
    }
    
    .course-stats-hero {
        gap: 0.625rem;
        flex-direction: row;
        justify-content: flex-start;
    }
    
    .course-stat-item {
        font-size: 0.75rem;
    }
    
    .breadcrumb-modern {
        padding: 0.5rem 0.625rem;
        font-size: 0.75rem;
    }
    
    .breadcrumb-modern .breadcrumb-item {
        font-size: 0.75rem;
    }
    
    .course-badge {
        padding: 0.3rem 0.625rem;
        font-size: 0.65rem;
    }
    
    .share-buttons {
        justify-content: center;
    }
    
    .share-btn {
        flex: 0 0 auto;
    }
    
    .mobile-payment-btn {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
        padding-top: 0.625rem;
        padding-bottom: 0.625rem;
    }
    
    .mobile-payment-btn .container-fluid {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    
    .mobile-payment-btn .row {
        justify-content: space-between;
    }
    
    .mobile-price-col {
        padding-right: 0.75rem;
        flex: 1;
        min-width: 0;
        max-width: calc(100% - 140px);
    }
    
    .mobile-btn-col {
        flex-shrink: 0;
        margin-left: auto;
        min-width: 100px;
        max-width: 130px;
    }
    
    .mobile-payment-btn .btn,
    .mobile-payment-btn .btn-group,
    .mobile-payment-btn .d-grid {
        max-width: 130px;
        min-width: 100px;
        width: 100%;
    }
    
    .mobile-price {
        width: 100%;
    }
    
    .mobile-price-value {
        font-size: 1.25rem;
    }
    
    .mobile-price-original {
        font-size: 0.8rem;
    }
    
    .mobile-price-discount {
        font-size: 0.65rem;
    }
    
    .mobile-payment-btn .btn {
        padding: 0.45rem 0.65rem;
        font-size: 0.68rem;
        min-width: 115px;
        max-width: 135px;
        white-space: nowrap;
        line-height: 1.2;
        height: 36px;
        overflow: visible;
        text-overflow: clip;
    }
    
    .mobile-payment-btn .btn i {
        font-size: 0.68rem;
        margin-right: 0.25rem;
    }
    
    .mobile-payment-btn .btn-group,
    .mobile-payment-btn .d-grid {
        max-width: 135px;
        min-width: 115px;
    }
    
    .mobile-payment-btn .btn-group .btn,
    .mobile-payment-btn .d-grid .btn {
        font-size: 0.62rem;
        padding: 0.35rem 0.55rem;
        white-space: nowrap;
        line-height: 1.2;
        height: 34px;
        overflow: visible;
        text-overflow: clip;
    }
    
    .promotion-countdown {
        padding: 0.625rem 0.75rem;
    }
    
    .countdown-label {
        font-size: 0.7rem;
    }
    
    .countdown-text {
        font-size: 0.85rem;
        flex-wrap: wrap;
    }
    
    .sidebar-card {
        padding: 1rem;
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
        gap: 0.75rem !important;
    }
    
    .sidebar-card .d-grid .btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
    }
    
    .price-display {
        padding-bottom: 1.25rem;
        margin-bottom: 1.25rem;
    }
    
    .price-current {
        font-size: 2rem;
    }
    
    .price-original {
        font-size: 1rem;
    }
    
    .price-discount {
        padding: 0.4rem 0.75rem;
        font-size: 0.75rem;
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
    
    .mobile-payment-btn {
        padding: 0.5rem 0.75rem !important;
        bottom: 60px;
    }
    
    .mobile-payment-btn .container-fluid {
        padding-left: 0px !important;
        padding-right: 0px !important;
    }
    
    .mobile-payment-btn .row {
        justify-content: space-between;
        align-items: center;
    }
    
    .mobile-price-col {
        padding-right: 0.5rem;
        flex: 1;
        min-width: 0;
        max-width: calc(100% - 140px);
    }
    
    .mobile-btn-col {
        flex-shrink: 0;
        margin-left: auto;
        min-width: 100px;
        max-width: 130px;
    }
    
    .mobile-price {
        width: 100%;
    }
    
    .mobile-price-label {
        font-size: 0.65rem;
        margin-bottom: 0.1rem;
    }
    
    .mobile-price-value {
        font-size: 1.25rem;
    }
    
    .mobile-price-original {
        font-size: 0.8rem;
    }
    
    .mobile-price-discount {
        font-size: 0.65rem;
        margin-top: 0.1rem;
    }
    
    .mobile-countdown {
        font-size: 0.65rem;
        margin-top: 0.15rem;
    }
    
    .mobile-countdown .countdown-text {
        font-size: 0.65rem;
    }
    
    .mobile-payment-btn .btn {
        padding: 0.4rem 0.6rem;
        font-size: 0.65rem;
        width: 100%;
        min-width: 100px;
        max-width: 130px;
        white-space: nowrap;
        line-height: 1.2;
        height: 36px;
        overflow: visible;
        text-overflow: clip;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }
    
    .mobile-payment-btn .btn i {
        font-size: 0.65rem;
        margin-right: 0.2rem;
        flex-shrink: 0;
    }
    
    .mobile-payment-btn .btn-group,
    .mobile-payment-btn .d-grid {
        max-width: 130px;
        min-width: 100px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .mobile-payment-btn .btn-group .btn,
    .mobile-payment-btn .d-grid .btn {
        font-size: 0.6rem;
        padding: 0.3rem 0.5rem;
        min-width: auto;
        max-width: 100%;
        white-space: nowrap;
        line-height: 1.2;
        height: 30px;
        overflow: visible;
        text-overflow: clip;
        box-sizing: border-box;
    }
    
    .mobile-payment-btn .d-grid {
        gap: 0.25rem !important;
    }
    
    .mobile-payment-btn .d-grid .btn i {
        font-size: 0.6rem;
        margin-right: 0.2rem;
    }
    
    .promotion-countdown {
        padding: 0.5rem 0.625rem;
    }
    
    .countdown-label {
        font-size: 0.65rem;
        margin-bottom: 0.375rem;
    }
    
    .countdown-text {
        font-size: 0.8rem;
        gap: 0.125rem;
    }
    
    .countdown-text span {
        font-size: inherit;
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
                    <i class="fas fa-star"></i>
                    <span>{{ number_format($course->reviews->avg('rating') ?? 0, 1) }} ({{ $course->reviews->count() }} avis)</span>
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

                <!-- Reviews -->
                @if($course->reviews->count() > 0)
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title-modern mb-0">
                            <i class="fas fa-star"></i>
                            Avis des étudiants
                        </h2>
                        <div class="rating-summary">
                            <div class="rating-score">{{ number_format($course->reviews->avg('rating') ?? 0, 1) }}</div>
                            <div>
                                <div class="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= floor($course->reviews->avg('rating') ?? 0) ? '' : 'far' }}"></i>
                                    @endfor
                                </div>
                                <div class="rating-count">({{ $course->reviews->count() }} avis)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Rating Distribution -->
                    <div class="rating-distribution mb-4">
                        @for($i = 5; $i >= 1; $i--)
                        @php
                            $ratingCount = $course->reviews->where('rating', $i)->count();
                            $percentage = $course->reviews->count() > 0 ? ($ratingCount / $course->reviews->count()) * 100 : 0;
                        @endphp
                        <div class="rating-bar-item">
                            <div class="rating-bar-label">{{ $i }} étoiles</div>
                            <div class="rating-bar">
                                <div class="rating-bar-fill" style="width: {{ $percentage }}%"></div>
                            </div>
                            <div class="rating-bar-count">{{ $ratingCount }}</div>
                        </div>
                        @endfor
                    </div>

                    <!-- Recent Reviews -->
                    <div class="recent-reviews">
                        @foreach($course->reviews->take(5) as $review)
                        <div class="review-card">
                            <div class="review-header">
                                @if($review->user)
                                    <div class="review-avatar">
                                        <img src="{{ $review->user->avatar_url }}" 
                                             alt="{{ $review->user->name }}">
                                    </div>
                                @else
                                    <div class="review-avatar d-flex align-items-center justify-content-center bg-primary text-white" style="font-size: 1.25rem; font-weight: bold; border-radius: 50%; min-width: 50px; min-height: 50px;">
                                        {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="review-author">
                                    <div class="review-author-name">{{ $review->user->name }}</div>
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
                            <div class="review-comment">{{ $review->comment }}</div>
                            @endif
                        </div>
                        @endforeach
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
                                                        <span class="countdown-years">0</span>a 
                                                        <span class="countdown-months">0</span>m 
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
                                    @if($course->is_downloadable)
                                        @if($canDownloadCourse)
                                            <a href="{{ route('courses.download', $course->slug) }}" class="btn btn-primary btn-lg w-100">
                                                <i class="fas fa-download me-2"></i>Télécharger le cours
                                            </a>
                                        @else
                                            <form action="{{ route('student.courses.enroll', $course->slug) }}" method="POST" class="d-grid gap-2">
                                                @csrf
                                                <input type="hidden" name="redirect_to" value="download">
                                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                                    <i class="fas fa-download me-2"></i>Télécharger le cours
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        @if($canAccessCourse)
                                            <a href="{{ route('learning.course', $course->slug) }}" class="btn btn-success btn-lg w-100">
                                                <i class="fas fa-play me-2"></i>Apprendre
                                            </a>
                                        @else
                                            <form action="{{ route('student.courses.enroll', $course->slug) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="redirect_to" value="learn">
                                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                                    <i class="fas fa-user-plus me-2"></i>S'inscrire au cours
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                @else
                                    @if($isEnrolled)
                                        @if($course->is_downloadable && $canDownloadCourse)
                                            <a href="{{ route('courses.download', $course->slug) }}" class="btn btn-primary btn-lg w-100">
                                                <i class="fas fa-download me-2"></i>Télécharger le cours
                                            </a>
                                        @else
                                        <a href="{{ route('learning.course', $course->slug) }}" class="btn btn-success btn-lg w-100">
                                            <i class="fas fa-play me-2"></i>Apprendre
                                            </a>
                                        @endif
                                    @elseif($hasPurchased)
                                        <form action="{{ route('student.courses.enroll', $course->slug) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="redirect_to" value="{{ $course->is_downloadable ? 'dashboard' : 'learn' }}">
                                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                                <i class="fas fa-user-plus me-2"></i>S'inscrire au cours
                                            </button>
                                        </form>
                                    @else
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

<!-- Mobile Payment Button -->
<div class="mobile-payment-btn">
    <div class="container-fluid">
        <div class="row align-items-center g-2" style="justify-content: space-between;">
            <div class="col mobile-price-col" style="flex: 1; min-width: 0;">
                <div class="mobile-price">
            @if($course->is_free)
                <div class="mobile-price-label">Prix</div>
                <div class="mobile-price-value">Gratuit</div>
            @else
                @if($course->is_sale_active && $course->active_sale_price !== null)
                    <div class="mobile-price-label">Prix promotionnel</div>
                    <div class="mobile-price-wrapper">
                        <div class="mobile-price-value">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->active_sale_price) }}</div>
                        <div class="mobile-price-original">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</div>
                        @if($course->sale_discount_percentage)
                        <div class="mobile-price-discount">
                            -{{ $course->sale_discount_percentage }}% de réduction
                        </div>
                        @endif
                    </div>
                    @if($course->is_sale_active && $course->sale_end_at)
                    <div class="promotion-countdown mobile-countdown text-danger mt-1" data-sale-end="{{ $course->sale_end_at->toIso8601String() }}">
                        <i class="fas fa-clock me-1"></i>
                        <span class="countdown-text">
                            <span class="countdown-days">0</span>j 
                            <span class="countdown-hours">00</span>h 
                            <span class="countdown-minutes">00</span>min
                        </span>
                    </div>
                    @endif
                @else
                    <div class="mobile-price-label">Prix</div>
                    <div class="mobile-price-value">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</div>
                @endif
            @endif
                </div>
            </div>
            <div class="col-auto mobile-btn-col" style="flex-shrink: 0;">
                @if(!$user)
                    @php
                        $finalLoginCourse2 = url()->full();
                        $callbackLoginCourse2 = route('sso.callback', ['redirect' => $finalLoginCourse2]);
                        $ssoLoginUrlCourse2 = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callbackLoginCourse2);
                    @endphp
                    <a href="{{ $ssoLoginUrlCourse2 }}" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                    </a>
                @else
                    @if($course->is_free)
                        @if($course->is_downloadable)
                            @if($canDownloadCourse)
                                <a href="{{ route('courses.download', $course->slug) }}" class="btn btn-primary w-100">
                                    <i class="fas fa-download me-2"></i>Télécharger
                                </a>
                            @else
                                <form action="{{ route('student.courses.enroll', $course->slug) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="download">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-download me-2"></i>Télécharger
                                    </button>
                                </form>
                            @endif
                        @else
                            @if($canAccessCourse)
                                <a href="{{ route('learning.course', $course->slug) }}" class="btn btn-success w-100">
                                    <i class="fas fa-play me-2"></i>Apprendre
                                </a>
                            @else
                                <form action="{{ route('student.courses.enroll', $course->slug) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="learn">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-user-plus me-2"></i>S'inscrire
                                    </button>
                                </form>
                            @endif
                        @endif
                    @else
                        @if($isEnrolled)
                            @if($course->is_downloadable && $canDownloadCourse)
                                <a href="{{ route('courses.download', $course->slug) }}" class="btn btn-primary w-100">
                                    <i class="fas fa-download me-2"></i>Télécharger
                                </a>
                            @else
                            <a href="{{ route('learning.course', $course->slug) }}" class="btn btn-success w-100">
                                <i class="fas fa-play me-2"></i>Apprendre
                            </a>
                            @endif
                        @elseif($hasPurchased)
                            <form action="{{ route('student.courses.enroll', $course->slug) }}" method="POST">
                                @csrf
                                <input type="hidden" name="redirect_to" value="{{ $course->is_downloadable ? 'dashboard' : 'learn' }}">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-user-plus me-2"></i>S'inscrire
                                </button>
                            </form>
                        @else
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary w-100" onclick="addToCart({{ $course->id }})">
                                    <i class="fas fa-shopping-cart me-2"></i>Ajouter
                                </button>
                                <button type="button" class="btn btn-success w-100" onclick="proceedToCheckout({{ $course->id }})">
                                    <i class="fas fa-credit-card me-2"></i>Payer
                                </button>
                            </div>
                        @endif
                    @endif
                @endif
            </div>
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <div class="bg-light rounded p-3" id="previewLessonInfo">
                                <h6 class="fw-bold mb-2" style="color: #003366;">Aperçu du cours</h6>
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
        previewUrl: '{{ route("courses.preview-data", $course->id) }}'
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
        console.log('⏳ Chargement déjà en cours, ignore...');
        return;
    }
    
    console.log('=== loadPreviewList() appelée ===');
    const container = document.getElementById('previewListContainer');
    if (!container) {
        console.error('❌ Container previewListContainer manquant');
        // Réessayer après un délai
        setTimeout(function() {
            const retryContainer = document.getElementById('previewListContainer');
            if (retryContainer) {
                console.log('✅ Container trouvé après retry');
                loadPreviewList();
            } else {
                console.error('❌ Container toujours manquant après retry');
            }
        }, 500);
        return;
    }
    
    console.log('✅ Container trouvé:', container);
    console.log('Container parent:', container.parentElement);
    console.log('Container visible:', container.offsetHeight > 0, 'height:', container.offsetHeight);
    const computedStyle = window.getComputedStyle(container);
    console.log('Container computed style:', {
        display: computedStyle.display,
        visibility: computedStyle.visibility,
        opacity: computedStyle.opacity,
        height: computedStyle.height,
        flex: computedStyle.flex
    });
    
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
        console.log('Création du contentWrapper...');
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
        console.log('✅ Spinner affiché');
    }
    
    isLoadingPreviewList = true;
    console.log('📡 Chargement des aperçus depuis:', window.coursePreviewData.previewUrl);
    fetch(window.coursePreviewData.previewUrl, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            console.log('📥 Réponse reçue:', response.status, response.statusText);
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
            console.log('✅ Données reçues:', data);
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
                console.log('Aucun aperçu disponible');
                contentWrapper.innerHTML = '<p class="text-muted text-center py-4">Aucun aperçu disponible</p>';
                return;
            }
            
            console.log('Chargement de', data.preview.length, 'aperçus');
            
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
                                <div class="plyr-player-wrapper position-absolute top-0 start-0 w-100 h-100" id="wrapper-${playerId}">
                                    <div class="plyr__video-embed" id="${playerId}" data-plyr-provider="youtube" data-plyr-embed-id="${preview.youtube_id}"></div>
                                </div>
                            `;
                            
                            previewVideoContainer.appendChild(wrapper);
                            
                            // Initialiser Plyr quand le wrapper sera affiché
                            console.log('Plyr wrapper created for lesson', preview.id, 'with YouTube ID:', preview.youtube_id);
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
                                        console.log('✅ Plyr initialized for video:', playerId);
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
            
            console.log('✅ Liste des aperçus chargée avec succès:', data.preview.length, 'aperçus');
            
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
                        console.log('✅ Hauteur recalculée après chargement:', availableHeight + 'px');
                        console.log('Container scrollHeight:', container.scrollHeight);
                        console.log('Container clientHeight:', container.clientHeight);
                        console.log('Scroll nécessaire:', container.scrollHeight > container.clientHeight);
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
        
        console.log('✅ Plyr player initialized for:', playerId);
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
                            <div class="plyr-player-wrapper position-absolute top-0 start-0 w-100 h-100" id="wrapper-${playerId}">
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
                                    
                                    console.log('✅ Plyr initialized for video:', playerId);
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
                        <div class="plyr-player-wrapper position-absolute top-0 start-0 w-100 h-100" id="wrapper-${playerId}">
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
                                
                                console.log('✅ Plyr initialized for video:', playerId);
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
                            
                            console.log('✅ Plyr initialized for video:', playerId);
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
                                console.log('Initializing Plyr player for lesson', lessonId, 'with YouTube ID:', youtubeId);
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
                            console.log('Could not pause player:', e);
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
                                            
                                            console.log('✅ Plyr initialized for video:', playerId);
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
            console.log('🎬 Modal complètement ouvert, chargement de la liste des aperçus...');
            
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
                            
                            console.log('✅ Plyr initialized for main player');
                        } catch (error) {
                            console.error('❌ Error initializing Plyr for main player:', error);
                        }
                    }
                }
            }, 300);
            
            const container = document.getElementById('previewListContainer');
            const contentWrapper = document.getElementById('previewListContent');
            const colLg4 = container ? container.closest('.col-lg-4') : null;
            
            console.log('Container trouvé:', !!container);
            console.log('ContentWrapper trouvé:', !!contentWrapper);
            console.log('Col-lg-4 trouvé:', !!colLg4);
            
            if (container && colLg4) {
                // Calculer la hauteur disponible pour le conteneur
                const headerHeight = colLg4.querySelector('div:first-child')?.offsetHeight || 0;
                const colHeight = colLg4.offsetHeight;
                const availableHeight = colHeight - headerHeight;
                
                console.log('Header height:', headerHeight);
                console.log('Column height:', colHeight);
                console.log('Available height:', availableHeight);
                console.log('Container height avant:', container.offsetHeight);
                console.log('Container computed height:', window.getComputedStyle(container).height);
                console.log('Container scrollHeight:', container.scrollHeight);
                console.log('Container clientHeight:', container.clientHeight);
                
                // Forcer une hauteur maximale pour permettre le scroll
                if (availableHeight > 0) {
                    container.style.maxHeight = availableHeight + 'px';
                    container.style.height = availableHeight + 'px';
                    console.log('✅ Hauteur définie sur le conteneur:', availableHeight + 'px');
                }
            }
            
            // Charger la liste des aperçus seulement si elle n'a pas déjà été chargée
            if (!previewListLoaded) {
                setTimeout(function() {
            loadPreviewList();
                }, 300);
            } else {
                console.log('✅ Liste déjà chargée, pas de rechargement');
            }
        });
        
        // Écouter 'show.bs.modal' pour certaines actions
        modal.addEventListener('show.bs.modal', function() {
            console.log('🎭 Modal en cours d\'ouverture...');
            
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
});
</script>
@endpush

