<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Herime Academie - Plateforme d\'apprentissage en ligne')</title>
    <meta name="description" content="@yield('description', 'Découvrez des milliers de cours en ligne de qualité avec Herime Academie. Formations professionnelles, certifications et expertise garanties.')">

        <!-- Favicon / PWA Icons -->
        <link rel="icon" type="image/png" href="{{ asset('images/icon-herime.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/icon-herime.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/icon-herime.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Plyr Video Player Library CSS -->
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    
    <!-- Custom CSS -->
    <style>
        /* Prévenir le débordement horizontal global */
        html {
            overflow-x: hidden;
            max-width: 100vw;
            width: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 1rem;
            line-height: 1.6;
            overflow-x: hidden;
            max-width: 100vw;
            margin: 0;
            padding: 0;
            width: 100%;
            position: relative;
        }
        
        body.has-global-announcement {
            padding-top: calc(60px + var(--announcement-height, 0px)) !important;
        }
        
        .global-announcement {
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1055;
            box-shadow: 0 15px 30px -20px rgba(15, 23, 42, 0.35);
        }

        @media (max-width: 991.98px) {
            .notification-dropdown.notification-dropdown--mobile {
                position: fixed !important;
                left: 50% !important;
                top: calc(var(--site-navbar-height, 60px) + 0.5rem) !important;
                transform: translateX(-50%) !important;
                width: min(92vw, 320px) !important;
                max-height: 65vh;
                margin: 0 !important;
                border-radius: 16px;
                box-shadow: 0 18px 40px -24px rgba(15, 23, 42, 0.35);
                border: 1px solid rgba(148, 163, 184, 0.25);
                overflow-y: auto;
                z-index: 1080;
            }
            .notification-dropdown.notification-dropdown--mobile .dropdown-header {
                position: sticky;
                top: 0;
                background: #ffffff;
                z-index: 2;
                padding: 0.5rem 0.75rem;
            }
            .notification-dropdown.notification-dropdown--mobile .dropdown-item {
                font-size: 0.9rem;
                padding: 0.75rem 1rem;
                white-space: normal;
                overflow-wrap: anywhere;
            }
            .notification-dropdown.notification-dropdown--mobile .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
                border-radius: 999px;
                line-height: 1.2;
            }
            .notification-dropdown .notification-view-all {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 32px;
                height: 32px;
                padding: 0;
            }
            .notification-dropdown.notification-dropdown--desktop {
                width: min(92vw, 360px) !important;
                max-height: 70vh;
            }
        }
        .global-announcement__inner {
            display: flex;
            align-items: center;
            gap: 1rem;
            justify-content: space-between;
            padding: 0.85rem 1.25rem;
        }
        .global-announcement__content {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            flex: 1;
            min-width: 0;
        }
        .global-announcement__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
            flex-shrink: 0;
        }
        .global-announcement__title {
            font-weight: 600;
            margin: 0;
            color: #ffffff;
        }
        .global-announcement__text {
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
        }
        .global-announcement__actions {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }
        .global-announcement__btn {
            color: #ffffff;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 999px;
            text-decoration: none;
            transition: background-color 0.2s ease, color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }
        .global-announcement__btn:hover {
            color: #0b1f3a;
            background: #ffffff;
        }
        .global-announcement__close {
            border: none;
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .global-announcement__close:hover {
            background: rgba(255, 255, 255, 0.32);
            transform: scale(1.05);
        }
        .global-announcement--info {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        }
        .global-announcement--success {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        }
        .global-announcement--warning {
            background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
        }
        .global-announcement--error {
            background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%);
        }
        .global-announcement--hidden {
            animation: announcement-slide-up 0.3s ease forwards;
        }
        @keyframes announcement-slide-up {
            to {
                transform: translateY(-100%);
                opacity: 0;
                height: 0;
                margin: 0;
                padding: 0;
            }
        }
        @media (max-width: 768px) {
            .global-announcement__inner {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            .global-announcement__actions {
                width: 100%;
                justify-content: space-between;
            }
            .global-announcement__btn {
                flex: 1;
                justify-content: center;
            }
            .global-announcement__close {
                align-self: flex-end;
            }
        }
        
        /* Global Font Styles - Appliquer seulement aux éléments de texte */
        body, button, input, select, textarea, .btn, .form-control {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        /* Box-sizing pour tous les éléments (meilleure pratique) */
        *, *::before, *::after {
            box-sizing: border-box;
        }
        
        /* Surcharger Bootstrap pour utiliser toute la largeur sur desktop */
        .container {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        /* Padding latéral pour les grands écrans */
        @media (min-width: 992px) {
            .container {
                padding-left: 2rem !important;
                padding-right: 2rem !important;
            }
        }
        
        @media (min-width: 1200px) {
            .container {
                padding-left: 3rem !important;
                padding-right: 3rem !important;
            }
        }
        
        @media (min-width: 1400px) {
            .container {
                padding-left: 4rem !important;
                padding-right: 4rem !important;
            }
        }
        
        /* Navbar et Footer utilisent toute la largeur */
        .navbar {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            left: 0 !important;
            right: 0 !important;
        }
        
        .navbar .container {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        .footer {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            left: 0 !important;
            right: 0 !important;
        }
        
        .footer .container {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        /* Container-fluid utilise toute la largeur avec padding adaptatif */
        .container-fluid {
            width: 100% !important;
            max-width: 100% !important;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        @media (min-width: 576px) {
            .container-fluid {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }
        
        @media (min-width: 992px) {
            .container-fluid {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }
        
        @media (min-width: 1200px) {
            .container-fluid {
                padding-left: 3rem;
                padding-right: 3rem;
            }
        }
        
        @media (min-width: 1400px) {
            .container-fluid {
                padding-left: 4rem;
                padding-right: 4rem;
            }
        }
        
        /* Page d'accueil - sections pleine largeur */
        .hero-section-modern,
        section {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        /* Boutons dans text-center ne doivent pas s'étirer - garder leur largeur naturelle */
        .text-center .btn,
        .text-center a.btn {
            width: auto !important;
            max-width: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex: 0 0 auto !important;
        }
        
        /* Boutons dans d-flex avec justify-content-center ne doivent pas s'étirer */
        .d-flex.justify-content-center .btn,
        .d-flex.justify-content-center a.btn,
        .text-center .d-flex.justify-content-center .btn,
        .text-center .d-flex.justify-content-center a.btn {
            width: auto !important;
            max-width: none !important;
            flex: 0 0 auto !important;
        }
        
        /* S'assurer que les boutons dans flex-column/flex-sm-row ne s'étirent pas non plus */
        .d-flex.flex-column .btn,
        .d-flex.flex-column a.btn,
        .d-flex.flex-sm-row .btn,
        .d-flex.flex-sm-row a.btn {
            width: auto !important;
            max-width: none !important;
            flex: 0 0 auto !important;
        }
        
        /* Spécifiquement pour les sections de la page d'accueil */
        .categories-section .text-center .btn,
        .categories-section .text-center a.btn,
        .featured-courses .text-center .btn,
        .featured-courses .text-center a.btn,
        .popular-courses .text-center .btn,
        .popular-courses .text-center a.btn,
        .trending-courses .text-center .btn,
        .trending-courses .text-center a.btn {
            width: auto !important;
            max-width: none !important;
            flex: 0 0 auto !important;
            display: inline-flex !important;
        }
        
        /* S'assurer que les conteneurs flex avec ces boutons ne les forcent pas à s'étirer */
        .text-center .d-flex,
        .text-center .d-flex.flex-column,
        .text-center .d-flex.flex-sm-row {
            justify-content: center !important;
            align-items: center !important;
        }
        
        .text-center .d-flex .btn,
        .text-center .d-flex a.btn {
            flex-shrink: 0 !important;
            flex-grow: 0 !important;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-weight: 600;
        }
        
        /* Logo styles */
        .navbar-logo {
            height: 40px;
            max-width: 200px;
            object-fit: contain;
        }
        
        .navbar-logo-mobile {
            height: 55px;
            max-width: 200px;
            object-fit: contain;
        }
        
        .footer-logo {
            height: 50px;
            max-width: 250px;
            object-fit: contain;
            margin-bottom: 1rem;
        }
        
        /* Centrer le logo du footer sur mobile */
        @media (max-width: 991.98px) {
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
        
        @media (max-width: 576px) {
            .navbar-logo-mobile {
                height: 50px;
                max-width: 180px;
            }
        }
        :root {
            --primary-color: #003366;
            --secondary-color: #ffcc33;
            --accent-color: #0066cc;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --bg-light: #f8f9fa;
            --border-color: #e9ecef;
            --shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        }

        /* Modern Compact Course Card Design */
        /* ========================================
           CARTES DE COURS MODERNISÉES - DESIGN COMPACT
           ======================================== */
        .course-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: auto;
            min-height: auto;
        }

        .course-card .card {
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .course-card .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12), 0 6px 12px rgba(0, 0, 0, 0.08);
            border-color: var(--primary-color);
        }

        .course-card .card-img-top {
            width: 100%;
            aspect-ratio: 21 / 10;
            object-fit: cover;
            object-position: center;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            flex-shrink: 0;
        }

        .course-card .card:hover .card-img-top {
            transform: scale(1.03);
        }

        .course-card .card-body {
            padding: 0.5rem 0.5rem 0.75rem 0.5rem;
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }

        .course-card .card-title {
            font-size: 0.8125rem;
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 0.1875rem;
            min-height: 1.5rem;
            max-height: 1.5rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            flex-shrink: 0;
        }

        .course-card .card-title a {
            color: #1a202c;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .course-card .card-title a:hover {
            color: var(--primary-color);
        }

        .course-card .card-text {
            color: #64748b;
            font-size: 0.65rem;
            line-height: 1.25;
            margin-bottom: 0.25rem;
            min-height: 0.8125rem;
            max-height: 0.8125rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            flex-shrink: 0;
        }

        .course-card .instructor-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.3125rem;
            padding: 0.1875rem 0.375rem;
            background: #f8fafc;
            border-radius: 6px;
            flex-shrink: 0;
        }

        .course-card .instructor-name {
            color: #475569;
            font-size: 0.75rem;
            font-weight: 500;
            max-width: 60%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .course-card .rating {
            display: flex;
            align-items: center;
            gap: 0.2rem;
        }

        .course-card .rating i {
            color: #fbbf24;
            font-size: 0.7rem;
        }

        .course-card .rating span {
            font-size: 0.75rem;
            color: #475569;
            font-weight: 500;
        }

        .course-card .price-duration {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
            padding: 0.25rem 0.375rem;
            background: #f1f5f9;
            border-radius: 6px;
            flex-shrink: 0;
        }

        .course-card .price {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1rem;
        }

        .course-card .price .text-muted {
            font-size: 0.8125rem;
            text-decoration: line-through;
            color: #94a3b8;
        }

        .course-card .duration {
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .course-card .card-actions {
            margin-top: auto;
            margin-bottom: 0;
            padding-bottom: 0.5rem;
            flex-shrink: 0;
        }
        
        
        .course-card .card-actions .btn:last-child,
        .course-card .card-actions .d-grid:last-child .btn:last-child,
        .course-card .card-actions form:last-child button,
        .course-card .card-actions a:last-child,
        .course-card .card-actions .d-grid:last-child {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        
        .course-card .card-actions form,
        .course-card .card-actions a.w-100 {
            margin-bottom: 0 !important;
        }
        
        /* Harmoniser les espacements et positions des boutons */
        .course-card .card-actions {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        /* Réduire les espacements dans les boutons */
        .course-card .card-actions .d-grid {
            gap: 0.25rem !important;
            margin-bottom: 0 !important;
            width: 100%;
        }
        
        .course-card .card-actions .mb-2,
        .course-card .card-actions .w-100.mb-2,
        .course-card .card-actions form,
        .course-card .card-actions a.w-100 {
            margin-bottom: 0 !important;
            width: 100%;
        }
        
        .course-card .card-actions .gap-2 {
            gap: 0.25rem !important;
        }
        
        .course-card .card-actions .gap-1 {
            gap: 0.1875rem !important;
        }
        
        /* Assurer que tous les boutons prennent toute la largeur - Taille augmentée */
        .course-card .card-actions .btn,
        .course-card .card-actions button,
        .course-card .card-actions a.btn,
        .course-card .card-actions form button,
        .course-card .course-button-container .btn,
        .course-card .course-button-container button {
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            font-size: 0.8125rem !important;
            padding: 0.5rem 0.75rem !important;
            line-height: 1.5 !important;
            min-height: 2.25rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }
        
        /* Harmoniser le conteneur de boutons dynamiques */
        .course-card .course-button-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
            margin-top: auto;
            padding-bottom: 0.5rem;
        }
        
        .course-card .course-button-container .btn {
            margin-bottom: 0 !important;
        }
        
        /* Styles pour le compteur à rebours de promotion */
        .promotion-countdown {
            color: #dc2626;
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }
        
        @media (min-width: 992px) {
            .promotion-countdown {
                font-size: 0.65rem;
            }
        }
        
        .promotion-countdown i {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .promotion-countdown .countdown-text {
            display: inline-flex;
            gap: 0.125rem;
        }
        
        .promotion-countdown .countdown-years,
        .promotion-countdown .countdown-months,
        .promotion-countdown .countdown-days,
        .promotion-countdown .countdown-hours,
        .promotion-countdown .countdown-minutes {
            font-weight: 700;
            color: #dc2626;
        }

        /* Harmoniser tous les boutons des cartes de cours - Taille augmentée */
        .course-card .btn,
        .course-card .btn-sm,
        .course-card .btn-lg,
        .course-card .btn-primary,
        .course-card .btn-outline-primary,
        .course-card .btn-success,
        .course-card button.btn,
        .course-card a.btn {
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8125rem !important;
            padding: 0.5rem 0.75rem !important;
            line-height: 1.5 !important;
            min-height: 2.25rem !important;
            transition: all 0.2s ease;
            text-transform: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            flex-wrap: nowrap;
        }
        
        /* Centrer les icônes et le texte dans les boutons */
        .course-card .btn i,
        .course-card .btn-sm i,
        .course-card .btn-lg i,
        .course-card .btn-primary i,
        .course-card .btn-outline-primary i,
        .course-card .btn-success i,
        .course-card button.btn i,
        .course-card a.btn i,
        .course-card .card-actions .btn i,
        .course-card .course-button-container .btn i {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin-right: 0.5rem !important;
            vertical-align: middle !important;
            line-height: 1 !important;
        }
        
        .course-card .btn span,
        .course-card .btn-sm span,
        .course-card .btn-lg span,
        .course-card .btn-primary span,
        .course-card .btn-outline-primary span,
        .course-card .btn-success span {
            display: inline-block;
            vertical-align: middle;
            text-align: center;
            line-height: 1.5;
        }
        
        /* Assurer le centrage du contenu des boutons */
        .course-card .btn > *,
        .course-card .btn-sm > *,
        .course-card .btn-lg > * {
            vertical-align: middle;
        }
        
        /* Surcharger les styles Bootstrap pour les cartes de cours */
        .course-card .btn-sm {
            font-size: 0.8125rem !important;
            padding: 0.5rem 0.75rem !important;
            min-height: 2.25rem !important;
        }
        
        .course-card .btn-lg {
            font-size: 0.8125rem !important;
            padding: 0.5rem 0.75rem !important;
            min-height: 2.25rem !important;
        }
        
        /* Version web - encore plus compact */
        @media (min-width: 992px) {
            .course-card .card-img-top {
                aspect-ratio: 2.2 / 1;
            }
            
            .course-card .card-body {
                padding: 0.4375rem 0.4375rem 0.625rem 0.4375rem;
            }
            
            .course-card .card-title {
                font-size: 0.75rem;
                margin-bottom: 0.1875rem;
                min-height: 1.375rem;
                max-height: 1.375rem;
                line-height: 1.15;
            }
            
            .course-card .card-text {
                font-size: 0.6rem;
                margin-bottom: 0.1875rem;
                min-height: 0.75rem;
                max-height: 0.75rem;
                line-height: 1.2;
                -webkit-line-clamp: 1;
            }
            
            .course-card .instructor-info {
                margin-bottom: 0.25rem;
                padding: 0.125rem 0.3125rem;
            }
            
            .course-card .instructor-name {
                font-size: 0.6rem;
            }
            
            .course-card .rating i {
                font-size: 0.55rem;
            }
            
            .course-card .rating span {
                font-size: 0.6rem;
            }
            
            .course-card .price-duration {
                margin-bottom: 0;
                padding: 0.1875rem 0.3125rem;
            }
            
            .course-card .card-actions {
                margin-top: auto;
                margin-bottom: 0;
                padding-bottom: 0.4375rem;
            }
            
            /* Harmoniser les espacements sur desktop */
            .course-card .card-actions {
                gap: 0.1875rem;
            }
            
            .course-card .card-actions .d-grid {
                gap: 0.1875rem !important;
                margin-bottom: 0 !important;
                width: 100%;
            }
            
            .course-card .card-actions .mb-2,
            .course-card .card-actions .w-100.mb-2,
            .course-card .card-actions form,
            .course-card .card-actions a.w-100 {
                margin-bottom: 0 !important;
                width: 100%;
            }
            
            .course-card .card-actions .gap-2 {
                gap: 0.1875rem !important;
            }
            
            .course-card .card-actions .gap-1 {
                gap: 0.125rem !important;
            }
            
            /* Assurer que tous les boutons prennent toute la largeur sur desktop - Taille augmentée */
            .course-card .card-actions .btn,
            .course-card .card-actions button,
            .course-card .card-actions a.btn,
            .course-card .card-actions form button,
            .course-card .course-button-container .btn,
            .course-card .course-button-container button {
                width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                font-size: 0.875rem !important;
                padding: 0.5625rem 0.875rem !important;
                min-height: 2.5rem !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-align: center !important;
            }
            
            /* Harmoniser le conteneur de boutons dynamiques sur desktop */
            .course-card .course-button-container {
                gap: 0.4375rem;
                margin-top: auto;
                padding-bottom: 0.4375rem;
            }
            
            .course-card .price {
                font-size: 1.0625rem;
            }
            
            .course-card .price .text-muted {
                font-size: 0.875rem;
            }
            
            .course-card .duration {
                font-size: 0.6rem;
            }
            
            /* Harmoniser tous les boutons sur desktop - Taille augmentée */
            .course-card .btn,
            .course-card .btn-sm,
            .course-card .btn-lg,
            .course-card .btn-primary,
            .course-card .btn-outline-primary,
            .course-card .btn-success,
            .course-card button.btn,
            .course-card a.btn {
                font-size: 0.875rem !important;
                padding: 0.5625rem 0.875rem !important;
                line-height: 1.5 !important;
                min-height: 2.5rem !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-align: center !important;
            }
            
            /* Centrer les icônes et le texte sur desktop */
            .course-card .btn i,
            .course-card .btn-sm i,
            .course-card .btn-lg i,
            .course-card .btn-primary i,
            .course-card .btn-outline-primary i,
            .course-card .btn-success i {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                margin-right: 0.5rem;
                vertical-align: middle;
            }
            
            .course-card .btn-sm {
                font-size: 0.875rem !important;
                padding: 0.5625rem 0.875rem !important;
                min-height: 2.5rem !important;
            }
            
            .course-card .btn-lg {
                font-size: 0.875rem !important;
                padding: 0.5625rem 0.875rem !important;
                min-height: 2.5rem !important;
            }
        }

        /* Harmoniser tous les boutons outline-primary - Taille augmentée */
        .course-card .btn-outline-primary,
        .course-card .btn-outline-primary.btn-sm,
        .course-card .btn-outline-primary.btn-lg {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: transparent;
            font-size: 0.8125rem !important;
            padding: 0.5rem 0.75rem !important;
            min-height: 2.25rem !important;
            width: 100% !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }

        .course-card .btn-outline-primary:hover,
        .course-card .btn-outline-primary.btn-sm:hover,
        .course-card .btn-outline-primary.btn-lg:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
        }

        /* Harmoniser tous les boutons primary - Taille augmentée */
        .course-card .btn-primary,
        .course-card .btn-primary.btn-sm,
        .course-card .btn-primary.btn-lg {
            background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
            font-size: 0.8125rem !important;
            padding: 0.5rem 0.75rem !important;
            min-height: 2.25rem !important;
            width: 100% !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }

        .course-card .btn-primary:hover,
        .course-card .btn-primary:focus,
        .course-card .btn-primary:active,
        .course-card .btn-primary.btn-sm:hover,
        .course-card .btn-primary.btn-lg:hover {
            background: linear-gradient(135deg, #002244 0%, var(--primary-color) 100%) !important;
            border-color: #002244 !important;
            color: white !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.4);
        }

        /* Harmoniser tous les boutons success - Taille augmentée */
        .course-card .btn-success,
        .course-card .btn-success.btn-sm,
        .course-card .btn-success.btn-lg {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            border-color: #28a745 !important;
            color: white !important;
            font-size: 0.8125rem !important;
            padding: 0.5rem 0.75rem !important;
            min-height: 2.25rem !important;
            width: 100% !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }

        .course-card .btn-success:hover,
        .course-card .btn-success:focus,
        .course-card .btn-success:active,
        .course-card .btn-success.btn-sm:hover,
        .course-card .btn-success.btn-lg:hover {
            background: linear-gradient(135deg, #1e7e34 0%, #28a745 100%) !important;
            border-color: #1e7e34 !important;
            color: white !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }
        
        /* Styles desktop harmonisés - Taille augmentée */
        @media (min-width: 992px) {
            .course-card .btn-outline-primary,
            .course-card .btn-outline-primary.btn-sm,
            .course-card .btn-outline-primary.btn-lg,
            .course-card .btn-primary,
            .course-card .btn-primary.btn-sm,
            .course-card .btn-primary.btn-lg,
            .course-card .btn-success,
            .course-card .btn-success.btn-sm,
            .course-card .btn-success.btn-lg {
                font-size: 0.875rem !important;
                padding: 0.5625rem 0.875rem !important;
                min-height: 2.5rem !important;
            }
        }

        .course-card .badge {
            font-size: 0.7rem;
            padding: 0.375rem 0.75rem;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .course-card .position-absolute {
            z-index: 2;
        }

        .course-card .badge.bg-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: white;
        }

        .course-card .badge.bg-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: white;
        }

        .course-card .badge.bg-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: white;
        }
        
        /* Lien invisible pour rendre toute la carte cliquable */
        .course-card-link {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
            text-decoration: none;
        }
        
        /* S'assurer que les boutons restent cliquables au-dessus du lien */
        .course-card .card-actions,
        .course-card .btn,
        .course-card .badge,
        .course-card .position-absolute {
            position: relative;
            z-index: 2;
        }
        
        .course-card .card-actions,
        .course-card .card-actions * {
            pointer-events: auto;
        }
        
        .course-card[data-course-url] {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .course-card[data-course-url]:hover {
            transform: translateY(-2px);
        }


        /* Mobile menu separators */
        .navbar-nav .nav-item:nth-child(4) .nav-link {
            border-bottom: 1px solid #e9ecef !important;
            margin-bottom: 0.5rem;
            padding-bottom: 0.75rem;
        }

        .navbar-nav .nav-item:nth-child(6) .nav-link {
            border-bottom: 1px solid #e9ecef !important;
            margin-bottom: 0.5rem;
            padding-bottom: 0.75rem;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
        }

        .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.75rem;
            color: var(--primary-color) !important;
        }
        
        /* Reduce navbar height on desktop */
        @media (min-width: 992px) {
            .navbar {
                padding-top: 0.05rem !important;
                padding-bottom: 0.05rem !important;
                min-height: auto !important;
            }
            
            .navbar .container {
                padding-top: 0.05rem !important;
                padding-bottom: 0.05rem !important;
            }
            
            .navbar .navbar-brand {
                padding-top: 0.05rem !important;
                padding-bottom: 0.05rem !important;
            }
            
            .navbar .navbar-nav .nav-link {
                padding-top: 0.5rem !important;
                padding-bottom: 0.5rem !important;
            }
        }

        /* Badge styles for menu counters */
        .navbar .badge {
            font-size: 0.7rem;
            font-weight: 600;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color) !important;
            color: white !important;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 51, 102, 0.3);
            transition: all 0.3s ease;
        }

        .navbar .badge:hover {
            background-color: #004080 !important;
            transform: scale(1.1);
        }

        /* Specific badge styles */
        #cart-count, #cart-count-mobile,
        #notification-count, #notification-count-mobile,
        #message-count, #message-count-mobile {
            background-color: var(--primary-color) !important;
            color: white !important;
        }

        .btn-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: #004080 !important;
            border-color: #004080 !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Bouton S'inscrire dans la navbar */
        .navbar .btn-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
        }

        .navbar .btn-primary:hover,
        .navbar .btn-primary:focus,
        .navbar .btn-primary:active {
            background-color: #004080 !important;
            border-color: #004080 !important;
            color: white !important;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--text-dark);
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #e6b800;
            border-color: #e6b800;
            color: var(--text-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 6rem 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,0 1000,100 1000,0"/></svg>');
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-section .btn {
            position: relative;
            z-index: 10;
            pointer-events: auto;
        }

        .hero-image {
            position: relative;
            height: 100%;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .hero-image img:hover {
            transform: scale(1.02);
        }

        .course-card {
            border: none;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            height: 100%;
        }

        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
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
            font-size: 0.75rem;
            font-weight: 600;
        }

        .course-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .course-price-old {
            text-decoration: line-through;
            color: var(--text-light);
            font-size: 1rem;
        }

        .section-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 3rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -0.5rem;
            left: 50%;
            transform: translateX(-50%);
            width: 4rem;
            height: 0.25rem;
            background: var(--secondary-color);
            border-radius: 0.125rem;
        }

        .category-card {
            text-align: center;
            padding: 2rem 1rem;
            border-radius: 1rem;
            background: white;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            height: 100%;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .category-icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .testimonial-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow);
            text-align: center;
            height: 100%;
        }

        .testimonial-avatar {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            margin: 0 auto 1rem;
            background-size: cover;
            background-position: center;
        }

        .footer {
            background: var(--primary-color);
            color: white;
            padding: 3rem 0 1rem;
            position: relative;
            z-index: 10;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            left: 0 !important;
            right: 0 !important;
        }

        .footer h5 {
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        .footer a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: var(--secondary-color);
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
        }

        .stats-card {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: var(--text-light);
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 4rem 0;
            }
            
            .hero-section h1 {
                font-size: 2rem;
            }

            .hero-image {
                min-height: 300px;
                margin-top: 2rem;
            }

            .hero-image img {
                border-radius: 0.75rem;
                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.2);
            }
        }

        @media (max-width: 576px) {
            .hero-image {
                min-height: 250px;
                margin-top: 1.5rem;
            }

            .hero-image img {
                border-radius: 0.5rem;
            }
        }

        /* Mobile Navigation Styles */
        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        .navbar-toggler i {
            color: var(--primary-color);
        }

        /* Mobile layout */
        @media (max-width: 991.98px) {
            /* Navbar pleine largeur sur mobile */
            .navbar .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
                padding-top: 0;
                padding-bottom: 0;
                height: 35px;
            }
            
            /* Mobile header layout optimization */
            .navbar .d-flex.d-lg-none {
                height: 35px;
                align-items: center;
            }
            
            /* Mobile menu global - réduire icônes */
            .navbar .d-flex.d-lg-none .nav-link,
            .navbar .d-flex.d-lg-none > div > a {
                padding: 0 !important;
            }
            
            .navbar .d-flex.d-lg-none i.fa-lg {
                font-size: 1rem !important;
            }
            
            .navbar-logo-mobile {
                height: 50px !important;
                max-width: 220px !important;
            }
            
            /* Responsive mobile menu widths - très petits écrans */
            @media (max-width: 575.98px) {
                .navbar-logo-mobile {
                    max-width: 200px !important;
                    height: 48px !important;
                }
            }
            
            /* Très très petits écrans */
            @media (max-width: 360px) {
                .navbar-logo-mobile {
                    max-width: 180px !important;
                    height: 45px !important;
                }
                
                .navbar .d-flex.d-lg-none i.fa-lg {
                    font-size: 0.9rem !important;
                }
                
                .navbar .d-flex.d-lg-none > div:first-child {
                    min-width: 40px;
                }
                
                .navbar .d-flex.d-lg-none > div:last-child {
                    min-width: 85px;
                }
            }
            
            .navbar-toggler {
                z-index: 1050;
            }
            
            /* Ensure mobile menu is closed by default */
            #navbarNav:not(.show) {
                display: none !important;
            }
            
            #navbarNav.show {
                display: block !important;
            }
            
            .navbar-nav .nav-link {
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #f8f9fa;
            }
            
            .navbar-nav .nav-link:last-child {
                border-bottom: none;
            }
            
            .navbar-nav .nav-link i {
                width: 20px;
                text-align: center;
            }

            /* Make contact button more compact on mobile */
            .btn-sm.btn-primary {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }
        }
        
        /* Laisser Bootstrap gérer le responsive normalement */
        
        /* Ensure no duplication */
        .navbar-nav {
            margin: 0;
        }
        
        .navbar-nav .nav-item {
            margin: 0;
        }
        
        .navbar-nav .nav-link {
            padding: 0.5rem 1rem;
        }
        
        /* Desktop specific styles */
        @media (min-width: 992px) {
            .navbar-nav .nav-link {
                padding: 0.5rem 0.75rem;
            }
        }
        
        /* Force mobile menu to be hidden on desktop */
        @media (min-width: 992px) {
            .d-lg-none {
                display: none !important;
            }
            #navbarNav {
                display: none !important;
            }
            .navbar-toggler {
                display: none !important;
            }
        }
        
        /* Force desktop menu to be hidden on mobile */
        @media (max-width: 991.98px) {
            .d-none.d-lg-flex {
                display: none !important;
            }
            #navbarNav {
                display: block !important;
            }
            .navbar-toggler {
                display: block !important;
            }
        }

        /* User avatar styles */
        .navbar .dropdown-toggle img {
            object-fit: cover;
        }

        .navbar .dropdown-toggle .rounded-circle {
            border: 2px solid #e9ecef;
            transition: border-color 0.3s ease;
        }

        .navbar .dropdown-toggle:hover .rounded-circle {
            border-color: var(--primary-color);
        }

        /* Mobile menu items */
        @media (max-width: 991.98px) {
            .navbar-nav .nav-link {
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #f8f9fa;
            }

            .navbar-nav .nav-link:last-child {
                border-bottom: none;
            }

            .navbar-nav .nav-link i {
                width: 20px;
                text-align: center;
            }

            .navbar-nav .dropdown-menu {
                border: none;
                box-shadow: none;
                background-color: #f8f9fa;
                margin-left: 2rem;
            }

            .navbar-nav .dropdown-item {
                padding: 0.5rem 1rem;
                border-bottom: 1px solid #e9ecef;
            }

            .navbar-nav .dropdown-item:last-child {
                border-bottom: none;
            }
        }

        /* Mobile Bottom Navigation */
        .mobile-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, var(--primary-color) 0%, var(--primary-color) 100%);
            border-top: 2px solid var(--secondary-color);
            z-index: 1000;
            display: none;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.15);
            height: 60px;
            width: 100%;
        }

        /* Main content full width */
        main {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        @media (max-width: 991.98px) {
            .mobile-bottom-nav {
                display: flex;
            }
            
            /* Add padding to body to prevent overlap with fixed navbar */
            /* Le logo fait 50px + padding, donc on utilise 60px pour être sûr */
            body {
                padding-top: 60px !important;
            }
            
            body.has-global-announcement {
                padding-top: calc(60px + var(--announcement-height, 0px)) !important;
            }
            
            /* Add padding to main content to prevent overlap with bottom nav */
            main {
                padding-bottom: 60px;
            }

            /* Footer doit commencer exactement là où s'arrête le menu mobile */
            .footer {
                padding-bottom: 60px !important;
                margin-bottom: 0 !important;
                position: relative;
                z-index: 5;
            }
            
            /* Assurer que le contenu du footer n'est pas masqué par le menu */
            .footer::after {
                content: '';
                display: block;
                height: 60px;
                width: 100%;
            }
        }
        
        /* Desktop - padding for fixed navbar */
        @media (min-width: 992px) {
            body {
                padding-top: 60px !important;
            }
            
            body.has-global-announcement {
                padding-top: calc(60px + var(--announcement-height, 0px)) !important;
            }
        }
        
        /* Assurer que tous les éléments principaux occupent toute la largeur */
        @media (max-width: 767.98px) {
            html {
                overflow-x: hidden;
                width: 100%;
            }
            
            body {
                overflow-x: hidden;
                width: 100%;
                position: relative;
                padding-top: 60px !important;
            }
            
            body.has-global-announcement {
                padding-top: calc(60px + var(--announcement-height, 0px)) !important;
            }
            
            .navbar,
            .footer,
            main {
                width: 100%;
            }
            
            /* Supprimer les outlines visibles indésirables sur mobile (mais garder le focus) */
            *:not(:focus):not(:active) {
                outline: none;
            }
            
            /* Laisser Bootstrap gérer les containers normalement - ajuster seulement le padding */
            .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
        }

        /* Styles harmonisés pour toutes les pages - Design cohérent avec l'accueil */
        /* Page header section (première section de chaque page) */
        .page-header-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
            color: white;
            padding: 3rem 0 2rem;
            position: relative;
            overflow: hidden;
        }

        .page-header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,0 1000,100 1000,0"/></svg>');
            background-size: cover;
        }

        .page-header-section .container {
            position: relative;
            z-index: 2;
        }

        .page-header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        }

        .page-header-section .lead {
            font-size: 1.25rem;
            opacity: 0.95;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.3);
        }

        /* Page content section - harmonisé avec les sections de l'accueil */
        .page-content-section {
            padding: 3rem 0;
            background: #ffffff;
        }

        .page-content-section.bg-light {
            background: #f8f9fa;
        }

        /* Section titles harmonisés */
        .section-title-modern {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .section-title-modern::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }

        .section-title-modern.center::after {
            left: 50%;
            transform: translateX(-50%);
        }

        /* Container avec padding harmonisé - Utilise toute la largeur sur web */
        .content-container {
            max-width: 100%;
            margin: 0 auto;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        
        @media (min-width: 992px) {
            .content-container {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }
        
        @media (min-width: 1400px) {
            .content-container {
                padding-left: 3rem;
                padding-right: 3rem;
            }
        }

        /* Desktop responsive pour page header */
        @media (min-width: 992px) {
            .page-header-section {
                margin-top: -40px;
                padding-top: 50px;
            }
        }
        
        /* Mobile responsive pour page header */
        @media (max-width: 991.98px) {
            .page-header-section {
                margin-top: -60px;
                padding-top: 70px;
                padding-bottom: 1.5rem;
            }

            .page-header-section h1 {
                font-size: 2rem;
            }

            .page-header-section .lead {
                font-size: 1.1rem;
            }

            .page-content-section {
                padding: 2rem 0;
            }
        }

        @media (max-width: 767.98px) {
            .page-header-section {
                margin-top: -60px;
                padding-top: 70px;
                padding-bottom: 1.25rem;
            }

            .page-header-section h1 {
                font-size: 1.75rem;
            }

            .page-header-section .lead {
                font-size: 1rem;
            }

            .page-content-section {
                padding: 1.5rem 0;
            }

            .section-title-modern {
                font-size: 1.5rem;
                margin-bottom: 0.75rem;
            }
        }

        /* Harmonisation des espacements pour toutes les pages */
        main > .container:first-child {
            padding-top: 2rem;
        }

        @media (max-width: 767.98px) {
            main > .container:first-child {
                padding-top: 1.5rem;
            }
        }

        /* Styles harmonisés pour toutes les pages légales - Utilisent maintenant page-header-section et page-content-section */
        
        /* Styles pour les cartes dans les pages légales */
        .page-content-section .card {
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .page-content-section .card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .page-content-section .card-body {
            padding: 2rem;
        }

        /* Section titles modernes avec icônes */
        .section-title-modern {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .section-title-modern::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }

        .section-title-modern.center::after {
            left: 50%;
            transform: translateX(-50%);
        }

        .section-title-modern i {
            color: var(--primary-color);
        }

        /* Listes modernes */
        .page-content-section ul:not(.list-unstyled) {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }

        .page-content-section li {
            margin-bottom: 0.5rem;
            line-height: 1.8;
        }

        .page-content-section .list-unstyled li {
            line-height: 1.6;
        }

        /* S'assurer que tous les éléments ne dépassent pas */
        .page-content-section * {
            max-width: 100%;
            box-sizing: border-box;
        }
        
        .page-content-section img,
        .page-content-section video,
        .page-content-section iframe,
        .page-content-section table {
            max-width: 100%;
            height: auto;
        }
        
        .page-content-section table {
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Responsive pour pages légales */
        @media (max-width: 991.98px) {
            .page-content-section .card-body {
                padding: 1.5rem;
            }

            .section-title-modern {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 767.98px) {
            .page-content-section .card-body {
                padding: 1.25rem;
            }

            .section-title-modern {
                font-size: 1.375rem;
            }

            .page-content-section ul:not(.list-unstyled) {
                padding-left: 1.25rem;
            }
        }

        @media (max-width: 480px) {
            .page-content-section .card-body {
                padding: 1rem;
            }

            .section-title-modern {
                font-size: 1.25rem;
            }
        }

        /* ========================================
           NOUVEAU DESIGN MODERNE - CHAMPS DE FORMULAIRE
           ======================================== */
        
        /* Reset et base pour tous les champs */
        .form-control,
        .form-select,
        textarea.form-control,
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        input[type="number"],
        input[type="search"],
        input[type="url"],
        input[type="date"],
        input[type="time"],
        input[type="datetime-local"] {
            width: 100%;
            padding: 0.875rem 1.125rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #1f2937;
            background-color: #ffffff;
            background-clip: padding-box;
            border: 1.5px solid #d1d5db;
            border-radius: 12px;
            transition: all 0.2s ease-in-out;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        /* État par défaut avec ombre subtile */
        .form-control,
        .form-select,
        textarea.form-control {
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        /* État hover - bordure légèrement plus foncée */
        .form-control:hover:not(:disabled):not(:focus),
        .form-select:hover:not(:disabled):not(:focus),
        textarea.form-control:hover:not(:disabled):not(:focus) {
            border-color: #9ca3af;
            box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.08);
        }

        /* État focus - BLEU FONCÉ (#003366) */
        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus,
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="tel"]:focus,
        input[type="number"]:focus,
        input[type="search"]:focus,
        input[type="url"]:focus,
        input[type="date"]:focus,
        input[type="time"]:focus,
        input[type="datetime-local"]:focus {
            border-color: #003366;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1), 0 2px 8px 0 rgba(0, 51, 102, 0.15);
            background-color: #ffffff;
        }

        /* Placeholder moderne */
        .form-control::placeholder,
        textarea.form-control::placeholder,
        input::placeholder {
            color: #9ca3af;
            opacity: 1;
            font-weight: 400;
        }

        /* Champs désactivés */
        .form-control:disabled,
        .form-select:disabled,
        textarea.form-control:disabled {
            background-color: #f9fafb;
            border-color: #e5e7eb;
            color: #9ca3af;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Tailles - Large */
        .form-control-lg,
        .form-select-lg {
            padding: 1rem 1.25rem;
            font-size: 1.0625rem;
            border-radius: 14px;
            line-height: 1.5;
        }

        /* Tailles - Small */
        .form-control-sm,
        .form-select-sm {
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            border-radius: 10px;
            line-height: 1.5;
        }

        /* Textarea */
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        textarea.form-control.form-control-lg {
            min-height: 140px;
        }

        textarea.form-control.form-control-sm {
            min-height: 100px;
        }

        /* Labels modernes */
        .form-label {
            display: inline-block;
            margin-bottom: 0.5rem;
            font-size: 0.9375rem;
            font-weight: 600;
            color: #374151;
            line-height: 1.5;
        }

        .form-label i {
            color: #003366;
            font-size: 0.9375rem;
            margin-right: 0.375rem;
        }

        /* Input groups - Pour les champs avec bouton (ex: mot de passe) */
        .input-group {
            position: relative;
            display: flex;
            flex-wrap: nowrap;
            align-items: stretch;
            width: 100%;
        }

        .input-group > .form-control,
        .input-group > .form-select,
        .input-group > input {
            position: relative;
            flex: 1 1 auto;
            width: 100%;
            min-width: 0;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .input-group > .form-control-lg,
        .input-group > .form-select-lg {
            border-top-right-radius: 14px;
            border-bottom-right-radius: 14px;
        }

        .input-group > .form-control-sm,
        .input-group > .form-select-sm {
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        /* Bouton toggle password dans input-group - Style strict */
        .input-group .btn,
        .input-group button.btn,
        .input-group .btn-outline-secondary {
            position: absolute !important;
            right: 4px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            z-index: 10 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 40px !important;
            height: 40px !important;
            min-width: 40px !important;
            max-width: 40px !important;
            border: none !important;
            background-color: transparent !important;
            color: #6b7280 !important;
            border-radius: 10px !important;
            transition: all 0.2s ease-in-out !important;
            cursor: pointer !important;
            flex-shrink: 0 !important;
            flex-grow: 0 !important;
            box-sizing: border-box !important;
        }

        .input-group .btn:hover,
        .input-group button.btn:hover,
        .input-group .btn-outline-secondary:hover {
            background-color: #f3f4f6 !important;
            color: #003366 !important;
            border: none !important;
        }

        .input-group .btn:active,
        .input-group .btn:focus,
        .input-group button.btn:active,
        .input-group button.btn:focus,
        .input-group .btn-outline-secondary:active,
        .input-group .btn-outline-secondary:focus {
            background-color: #e5e7eb !important;
            color: #003366 !important;
            outline: none !important;
            border: none !important;
            box-shadow: 0 0 0 2px rgba(0, 51, 102, 0.1) !important;
        }

        .input-group .btn i,
        .input-group button.btn i,
        .input-group .btn-outline-secondary i {
            font-size: 1rem !important;
            line-height: 1 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Empêcher Bootstrap d'appliquer ses styles par défaut - SEULEMENT pour les boutons dans input-group */
        .input-group > .btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
        .input-group > button.btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
        .input-group > button:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light) {
            flex: 0 0 0 !important;
            width: 0 !important;
            position: absolute !important;
            pointer-events: auto !important;
        }

        /* S'assurer que le champ input est bien cliquable et utilisable */
        .input-group > .form-control,
        .input-group > input[type="password"],
        .input-group > input[type="text"] {
            pointer-events: auto !important;
            position: relative !important;
            z-index: 1 !important;
        }

        /* Le bouton toggle password doit être au-dessus mais ne pas bloquer l'input */
        .input-group .btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
        .input-group button.btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light) {
            pointer-events: auto !important;
            z-index: 10 !important;
        }

        /* Forcer les styles Bootstrap à ne pas s'appliquer - SEULEMENT pour les boutons toggle */
        .input-group .btn.btn-outline-secondary:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
        .input-group button.btn-outline-secondary:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light) {
            border-width: 0 !important;
            background-color: transparent !important;
            background-image: none !important;
        }

        /* Padding pour éviter chevauchement texte/bouton */
        .input-group .form-control {
            padding-right: 3.5rem !important;
        }

        .input-group .form-control-lg {
            padding-right: 4rem !important;
        }

        .input-group .form-control-sm {
            padding-right: 3rem !important;
        }

        /* Empêcher Bootstrap de modifier la structure */
        .input-group > :not(:first-child):not(.dropdown-menu):not(.valid-tooltip):not(.valid-feedback):not(.invalid-tooltip):not(.invalid-feedback) {
            margin-left: 0;
            border-left: none;
        }

        /* États d'erreur */
        .form-control.is-invalid,
        .form-select.is-invalid,
        .was-validated .form-control:invalid,
        .was-validated .form-select:invalid {
            border-color: #ef4444;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23ef4444'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6 .4.4.4-.4M6 8V6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .form-control.is-invalid:focus,
        .form-select.is-invalid:focus,
        .was-validated .form-control:invalid:focus,
        .was-validated .form-select:invalid:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1), 0 2px 8px 0 rgba(239, 68, 68, 0.15);
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.375rem;
            font-size: 0.875rem;
            color: #ef4444;
            font-weight: 500;
        }

        /* États valides */
        .form-control.is-valid,
        .form-select.is-valid,
        .was-validated .form-control:valid,
        .was-validated .form-select:valid {
            border-color: #10b981;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2310b981' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .form-control.is-valid:focus,
        .form-select.is-valid:focus,
        .was-validated .form-control:valid:focus,
        .was-validated .form-select:valid:focus {
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1), 0 2px 8px 0 rgba(16, 185, 129, 0.15);
        }

        /* Responsive mobile */
        @media (max-width: 767.98px) {
            .form-control,
            .form-select,
            textarea.form-control {
                padding: 0.75rem 1rem;
                font-size: 16px; /* Évite zoom auto sur iOS */
                border-radius: 12px;
            }

            .form-control-lg,
            .form-select-lg {
                padding: 0.875rem 1.125rem;
                font-size: 16px;
                border-radius: 14px;
            }

            .form-control-sm,
            .form-select-sm {
                padding: 0.5625rem 0.75rem;
                font-size: 16px;
                border-radius: 10px;
            }

            .input-group {
                width: 100%;
                position: relative;
                display: flex;
                flex-wrap: nowrap;
                align-items: stretch;
            }

            .input-group > .form-control,
            .input-group > input[type="password"],
            .input-group > input[type="text"] {
                flex: 1 1 auto !important;
                width: 100% !important;
                min-width: 0 !important;
                position: relative !important;
                padding-right: 3rem !important;
                z-index: 1 !important;
            }

            .input-group > .form-control-lg,
            .input-group > input[type="password"].form-control-lg {
                padding-right: 3.5rem !important;
            }

            .input-group > .form-control-sm,
            .input-group > input[type="password"].form-control-sm {
                padding-right: 2.75rem !important;
            }

            /* Le bouton toggle ne doit pas prendre d'espace dans le flex - SEULEMENT les boutons toggle */
            .input-group > .btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
            .input-group > button.btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
            .input-group > button:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light) {
                flex: 0 0 0 !important;
                width: 0 !important;
                min-width: 0 !important;
                max-width: 0 !important;
            }

            .input-group .btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
            .input-group button.btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
            .input-group .btn-outline-secondary:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light) {
                position: absolute !important;
                right: 4px !important;
                top: 50% !important;
                transform: translateY(-50%) !important;
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
                max-width: 36px !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                flex-shrink: 0 !important;
                flex-grow: 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                box-sizing: border-box !important;
                overflow: visible !important;
            }

            .input-group .btn i,
            .input-group button.btn i,
            .input-group .btn-outline-secondary i {
                font-size: 0.9375rem !important;
                margin: 0 !important;
                padding: 0 !important;
                line-height: 1 !important;
            }

            /* Empêcher le bouton toggle de prendre de l'espace dans le flex - SEULEMENT les boutons toggle */
            .input-group > .btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
            .input-group > button.btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
            .input-group > button:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light) {
                flex: 0 0 auto !important;
                width: auto !important;
                position: absolute !important;
            }

            /* S'assurer que le champ est bien utilisable */
            .input-group > .form-control:focus {
                padding-right: 3rem !important;
                z-index: 1;
            }

            .input-group > .form-control-lg:focus {
                padding-right: 3.5rem !important;
                z-index: 1;
            }
        }

        @media (max-width: 480px) {
            .form-control,
            .form-select,
            textarea.form-control {
                padding: 0.6875rem 0.875rem;
            }

            .input-group > .form-control,
            .input-group > input[type="password"],
            .input-group > input[type="text"] {
                width: 100% !important;
                padding-right: 2.75rem !important;
                z-index: 1 !important;
            }

            .input-group > .form-control-lg,
            .input-group > input[type="password"].form-control-lg {
                padding-right: 3.25rem !important;
            }

            .input-group > .form-control-sm,
            .input-group > input[type="password"].form-control-sm {
                padding-right: 2.5rem !important;
            }

            /* Le bouton toggle ne doit pas prendre d'espace dans le flex - SEULEMENT les boutons toggle */
            .input-group > .btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
            .input-group > button.btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
            .input-group > button:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light) {
                flex: 0 0 0 !important;
                width: 0 !important;
                min-width: 0 !important;
                max-width: 0 !important;
            }

            .input-group .btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
            .input-group button.btn:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light),
            .input-group .btn-outline-secondary:not(.btn-primary):not(.btn-secondary):not(.btn-success):not(.btn-danger):not(.btn-warning):not(.btn-info):not(.btn-light) {
                position: absolute !important;
                right: 3px !important;
                top: 50% !important;
                transform: translateY(-50%) !important;
                width: 32px !important;
                height: 32px !important;
                min-width: 32px !important;
                max-width: 32px !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                flex-shrink: 0 !important;
                flex-grow: 0 !important;
                box-sizing: border-box !important;
                overflow: visible !important;
            }

            .input-group .btn i,
            .input-group button.btn i,
            .input-group .btn-outline-secondary i {
                font-size: 0.875rem !important;
                margin: 0 !important;
                padding: 0 !important;
                line-height: 1 !important;
            }

            /* S'assurer que le champ est bien utilisable sur très petits écrans */
            .input-group > .form-control:focus {
                padding-right: 2.75rem !important;
                z-index: 1;
            }

            .input-group > .form-control-lg:focus {
                padding-right: 3.25rem !important;
                z-index: 1;
            }
        }

        /* Checkboxes et radios modernes */
        .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            margin-top: 0.125rem;
            vertical-align: top;
            background-color: #ffffff;
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            border: 1.5px solid #d1d5db;
            border-radius: 6px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .form-check-input:checked {
            background-color: #003366;
            border-color: #003366;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
        }

        .form-check-input:focus {
            border-color: #003366;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }

        .form-check-input:hover:not(:checked):not(:disabled) {
            border-color: #9ca3af;
        }

        .form-check-input:disabled {
            pointer-events: none;
            filter: none;
            opacity: 0.5;
        }

        .form-check-label {
            color: #374151;
            font-weight: 500;
            cursor: pointer;
            margin-left: 0.5rem;
            line-height: 1.5;
        }

        /* Radio buttons modernes */
        input[type="radio"].form-check-input {
            border-radius: 50%;
        }

        input[type="radio"].form-check-input:checked {
            background-color: #003366;
            border-color: #003366;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='2' fill='%23fff'/%3e%3c/svg%3e");
        }

        /* Boutons modernes globaux */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            text-decoration: none;
            line-height: 1.5;
            min-width: auto;
            max-width: none;
            width: auto;
            white-space: nowrap;
            box-sizing: border-box;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Permettre le retour à la ligne pour les boutons dans d-grid */
        .d-grid .btn {
            white-space: normal;
            text-overflow: clip;
            overflow: visible;
        }
        
        /* Empêcher les boutons normaux d'être affectés par les styles des input-groups */
        .btn:not(.input-group .btn) {
            position: relative;
            flex: none;
        }

        .btn:focus {
            box-shadow: 0 0 0 4px rgba(0, 51, 102, 0.2);
            outline: none;
        }

        /* Bouton primaire */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
            color: white;
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.2);
        }

        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #002244 0%, var(--primary-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 51, 102, 0.3);
            border-color: #002244;
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0, 51, 102, 0.2);
        }

        /* Bouton outline primaire */
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            background-color: transparent;
        }

        .btn-outline-primary:hover:not(:disabled) {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.25);
        }

        /* Bouton secondaire */
        .btn-secondary {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #e6b800 100%);
            color: var(--text-dark);
            border-color: var(--secondary-color);
            box-shadow: 0 4px 12px rgba(255, 204, 51, 0.2);
        }

        .btn-secondary:hover:not(:disabled) {
            background: linear-gradient(135deg, #e6b800 0%, var(--secondary-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 204, 51, 0.3);
        }

        /* Bouton success */
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-color: #10b981;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .btn-success:hover:not(:disabled) {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
        }

        /* Bouton danger */
        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-color: #ef4444;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        .btn-danger:hover:not(:disabled) {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }

        /* Bouton warning */
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border-color: #f59e0b;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
        }

        .btn-warning:hover:not(:disabled) {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
        }

        /* Bouton info */
        .btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            border-color: #06b6d4;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.2);
        }

        .btn-info:hover:not(:disabled) {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 182, 212, 0.3);
        }

        /* Bouton light */
        .btn-light {
            background-color: #f8fafc;
            color: var(--text-dark);
            border-color: #e2e8f0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .btn-light:hover:not(:disabled) {
            background-color: #f1f5f9;
            border-color: #cbd5e0;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Bouton disabled */
        .btn:disabled,
        .btn.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Tailles de boutons */
        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.125rem;
            border-radius: 14px;
            min-width: auto;
            max-width: none;
            width: auto;
        }
        
        /* S'assurer que les boutons dans d-grid prennent toute la largeur sans dépasser */
        .d-grid {
            width: 100%;
        }
        
        .d-grid .btn,
        .d-grid button {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            margin: 0;
            white-space: normal;
            word-wrap: break-word;
            word-break: break-word;
            line-height: 1.4;
            min-height: 3rem;
            padding: 0.875rem 1.25rem;
        }
        
        .d-grid .btn-lg,
        .d-grid button.btn-lg {
            padding: 1rem 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border-radius: 10px;
        }

        /* Cards de formulaire modernes */
        .card {
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        
        /* S'assurer que les formulaires dans les cartes ne dépassent pas */
        .card form {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
            color: white;
            border-bottom: none;
            padding: 1.5rem;
            font-weight: 600;
        }

        .card-body {
            padding: 2rem;
            overflow-x: hidden;
        }
        
        .card-body .d-grid,
        .card-body form .d-grid {
            width: 100%;
            max-width: 100%;
        }

        /* Alerts modernes */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .alert-warning {
            background-color: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }

        .alert-info {
            background-color: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }

        /* Responsive pour formulaires */
        @media (max-width: 767.98px) {
            .form-control,
            .form-select,
            textarea.form-control {
                padding: 0.75rem 1rem;
                font-size: 16px; /* Évite le zoom automatique sur iOS */
            }

            .form-control-lg,
            .form-select-lg {
                padding: 0.875rem 1.25rem;
                font-size: 16px; /* Évite le zoom automatique sur iOS */
            }

            .btn {
                padding: 0.75rem 1.25rem;
                font-size: 0.95rem;
            }

            .btn-lg {
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
            }

            .card-body {
                padding: 1.5rem;
            }

            /* Assurer que les input groups sont bien gérés sur mobile */
            .input-group {
                width: 100%;
                display: flex;
                flex-wrap: nowrap;
            }

            .input-group > .form-control,
            .input-group > .form-select {
                flex: 1 1 auto;
                width: 1%;
                min-width: 0;
                position: relative;
            }
        }

        /* Styles pour pages admin - harmonisation */
        .admin-page {
            background: #f8f9fa;
            min-height: 100vh;
        }

        .admin-page .page-header-section,
        .admin-page .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
            color: white;
        }

        .admin-page .card {
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .admin-page .stats-card {
            transition: all 0.3s ease;
        }

        .admin-page .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        /* Tables modernes pour admin */
        .table {
            border-radius: 12px;
            overflow: hidden;
        }

        .table thead {
            background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
            color: white;
        }

        .table thead th {
            border: none;
            font-weight: 600;
            padding: 1rem;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
            transform: scale(1.01);
        }

        .table tbody td {
            padding: 1rem;
            border-color: #e2e8f0;
        }

        /* Badges modernes pour admin */
        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .mobile-bottom-nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.25rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 8px;
        }

        .mobile-bottom-nav-item:hover,
        .mobile-bottom-nav-item.active {
            color: var(--secondary-color);
            background: rgba(255, 255, 255, 0.05);
        }

        .mobile-bottom-nav-item i {
            font-size: 1.1rem;
            margin-bottom: 0.15rem;
        }

        .mobile-bottom-nav-item span {
            font-size: 0.7rem;
            font-weight: 500;
        }

        /* More Modal Styles */
        .more-modal .modal-dialog-bottom {
            position: fixed;
            bottom: 70px;
            left: 16px;
            right: 16px;
            margin: 0;
            max-width: calc(100% - 32px);
            max-height: 75vh;
            transform: translateY(100%);
            transition: transform 0.3s ease-out;
        }

        .more-modal.show .modal-dialog-bottom,
        .more-modal.showing .modal-dialog-bottom {
            transform: translateY(0);
        }

        .more-modal .modal-content {
            border-radius: 16px 16px 0 0;
            border: none;
            margin: 0;
            max-height: 75vh;
            overflow-y: auto;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
        }

        .more-modal .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
            color: white;
            border-bottom: none;
            border-radius: 16px 16px 0 0;
            padding: 16px 20px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .more-modal .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .more-modal .modal-body {
            max-height: calc(75vh - 60px);
            overflow-y: auto;
        }

        /* Backdrop pour le modal mobile */
        .more-modal.modal-backdrop {
            z-index: 1040;
        }

        .more-modal.show {
            display: block;
        }

        /* Assurer que le modal est au-dessus de la navigation mobile */
        .more-modal .modal-dialog-bottom {
            z-index: 1055;
        }

        /* Cacher le modal sur desktop */
        @media (min-width: 992px) {
            .more-modal {
                display: none !important;
            }
        }

        .more-modal .list-group-item {
            border: none;
            border-bottom: 1px solid #f1f5f9;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .more-modal .list-group-item:last-child {
            border-bottom: none;
        }

        .more-modal .list-group-item:hover {
            background-color: #f8f9fa;
            padding-left: 2rem;
        }

        .more-modal .list-group-item a {
            color: var(--text-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .more-modal .list-group-item i {
            width: 24px;
            text-align: center;
            margin-right: 0.75rem;
            color: var(--primary-color);
        }

        .more-modal .list-group-item button.btn-logout {
            padding: 0;
            border: none;
            background: transparent;
            color: var(--text-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            width: 100%;
            text-align: left;
            transition: all 0.3s ease;
        }

        .more-modal .list-group-item button.btn-logout:hover {
            padding-left: 0.5rem;
        }

        .more-modal .list-group-item button.btn-logout i {
            margin-right: 0.75rem;
            width: 24px;
            text-align: center;
            color: var(--primary-color);
        }

        /* Dropdown for categories on mobile bottom nav */
        .mobile-bottom-nav .dropdown {
            flex: 1;
        }

        .mobile-bottom-nav .dropdown > button {
            border: none;
            background: transparent;
            padding: 0;
            width: 100%;
            height: 100%;
        }

        .mobile-bottom-nav .dropdown > button:focus {
            box-shadow: none;
        }

        .mobile-bottom-nav .dropdown-menu {
            bottom: 100%;
            top: auto;
            margin-bottom: 0.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #e9ecef;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
        }

        .mobile-bottom-nav .dropdown-item {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mobile-bottom-nav .dropdown-item:last-child {
            border-bottom: none;
        }

        .mobile-bottom-nav .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .mobile-bottom-nav .dropdown-item i {
            margin-right: 0.5rem;
        }

        /* Cart count synchronization */
        #cart-count-mobile {
            font-size: 0.7rem;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>

    @stack('styles')
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
<body>
    @php
        if (isset($globalAnnouncement) && $globalAnnouncement) {
            $now = \Illuminate\Support\Carbon::now();

            if ($globalAnnouncement->starts_at instanceof \Illuminate\Support\Carbon
                && $globalAnnouncement->starts_at->greaterThan($now)) {
                $globalAnnouncement = null;
            }

            if ($globalAnnouncement && $globalAnnouncement->expires_at instanceof \Illuminate\Support\Carbon
                && $globalAnnouncement->expires_at->lessThanOrEqualTo($now)) {
                $globalAnnouncement = null;
            }
        }
    @endphp
    @if(isset($globalAnnouncement) && $globalAnnouncement)
        @php
            $announcementIcon = match ($globalAnnouncement->type) {
                'success' => 'fas fa-circle-check',
                'warning' => 'fas fa-triangle-exclamation',
                'error' => 'fas fa-circle-exclamation',
                default => 'fas fa-bullhorn',
            };
        @endphp
        <div id="global-announcement"
             class="global-announcement global-announcement--{{ $globalAnnouncement->type }}"
             data-announcement-id="{{ $globalAnnouncement->id }}">
            <div class="container global-announcement__inner">
                <div class="global-announcement__content">
                    <span class="global-announcement__icon">
                        <i class="{{ $announcementIcon }}"></i>
                    </span>
                    <div class="global-announcement__text-wrapper">
                        <p class="global-announcement__title mb-0">
                            {{ $globalAnnouncement->title }}
                        </p>
                        <p class="global-announcement__text mb-0">
                            {{ \Illuminate\Support\Str::limit(strip_tags($globalAnnouncement->content), 160) }}
                        </p>
                    </div>
                </div>
                <div class="global-announcement__actions">
                    @if($globalAnnouncement->button_text && $globalAnnouncement->button_url)
                        <a href="{{ $globalAnnouncement->button_url }}"
                           class="global-announcement__btn"
                           target="_blank"
                           rel="noopener noreferrer">
                            <span>{{ $globalAnnouncement->button_text }}</span>
                            <i class="fas fa-arrow-up-right-from-square"></i>
                        </a>
                    @endif
                    <button type="button"
                            class="global-announcement__close"
                            data-announcement-dismiss
                            aria-label="Fermer l'annonce">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif
    <!-- Navigation -->
    <x-navbar />

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
        <a href="{{ route('home') }}" class="mobile-bottom-nav-item" data-page="home">
            <i class="fas fa-home"></i>
            <span>Accueil</span>
        </a>
        <a href="{{ route('courses.index') }}" class="mobile-bottom-nav-item" data-page="courses">
            <i class="fas fa-book"></i>
            <span>Cours</span>
        </a>
        <div class="dropdown">
            <button class="mobile-bottom-nav-item" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-th-large"></i>
                <span>Catégories</span>
            </button>
            <ul class="dropdown-menu">
                @foreach(\App\Models\Category::active()->ordered()->limit(6)->get() as $category)
                    <li><a class="dropdown-item" href="{{ route('courses.category', $category->slug) }}">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        </div>
        <button class="mobile-bottom-nav-item" type="button" data-bs-toggle="modal" data-bs-target="#moreModal">
            <i class="fas fa-ellipsis-h"></i>
            <span>Plus</span>
        </button>
    </nav>

    <!-- More Modal -->
    <div class="modal fade more-modal" id="moreModal" tabindex="-1" aria-labelledby="moreModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-bottom">
            <div class="modal-content">
                <!-- Default Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="moreModalLabel">
                        <i class="fas fa-ellipsis-h me-2"></i>Plus d'options
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <a href="{{ route('instructors.index') }}">
                                <i class="fas fa-chalkboard-teacher"></i>
                                Formateurs
                            </a>
                        </div>
                        <div class="list-group-item">
                            <a href="{{ route('contact') }}">
                                <i class="fas fa-envelope"></i>
                                Contact
                            </a>
                        </div>
                        <div class="list-group-item">
                            <a href="{{ route('about') }}">
                                <i class="fas fa-info-circle"></i>
                                À propos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <x-footer />

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Day.js pour la gestion des dates relatives -->
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/locale/fr.js"></script>
    
    <!-- Custom JS -->
    <script>
        if (window.dayjs && window.dayjs_plugin_relativeTime) {
            dayjs.extend(window.dayjs_plugin_relativeTime);
            dayjs.locale('fr');
        }

        (function() {
            const banner = document.getElementById('global-announcement');
            if (!banner) {
                return;
            }

            const navbar = document.querySelector('.navbar.fixed-top');

            function applyAnnouncementOffset() {
                const height = Math.ceil(banner.getBoundingClientRect().height);
                document.body.classList.toggle('has-global-announcement', height > 0);
                document.body.style.setProperty('--announcement-height', `${height}px`);
                if (navbar) {
                    navbar.style.top = `${height}px`;
                }
            }

            applyAnnouncementOffset();
            window.addEventListener('resize', applyAnnouncementOffset, { passive: true });

            const dismissButton = banner.querySelector('[data-announcement-dismiss]');
            dismissButton?.addEventListener('click', () => {
                window.removeEventListener('resize', applyAnnouncementOffset);
                if (navbar) {
                    navbar.style.top = '';
                }
                document.body.classList.remove('has-global-announcement');
                document.body.style.removeProperty('--announcement-height');

                banner.classList.add('global-announcement--hidden');
                window.setTimeout(() => {
                    banner.parentNode && banner.parentNode.removeChild(banner);
                }, 320);
            });
        })();

        // Synchronise les hauteurs des navigations (top et bottom) via des variables CSS
        (function() {
            const root = document.documentElement;
            const TOP_VAR = '--site-navbar-height';
            const BOTTOM_VAR = '--site-mobile-bottom-nav-height';
            let rafId = null;

            function readHeight(element) {
                if (!element) {
                    return 0;
                }
                const styles = window.getComputedStyle(element);
                if (styles.display === 'none' || styles.visibility === 'hidden') {
                    return 0;
                }
                return Math.ceil(element.getBoundingClientRect().height);
            }

            function applyNavMetrics() {
                rafId = null;
                const topNav = document.querySelector('.navbar.fixed-top');
                const mobileBottomNav = document.querySelector('.mobile-bottom-nav');

                const topNavHeight = readHeight(topNav) || 64;
                const bottomNavHeight = readHeight(mobileBottomNav);

                root.style.setProperty(TOP_VAR, `${topNavHeight}px`);
                root.style.setProperty(BOTTOM_VAR, `${bottomNavHeight}px`);
            }

            function scheduleMeasure() {
                if (rafId !== null) {
                    return;
                }
                rafId = window.requestAnimationFrame(applyNavMetrics);
            }

            function observeElements() {
                if (!('ResizeObserver' in window)) {
                    return;
                }
                const resizeObserver = new ResizeObserver(scheduleMeasure);
                document.querySelectorAll('.navbar.fixed-top, .mobile-bottom-nav').forEach(el => resizeObserver.observe(el));
            }

            ['load', 'resize', 'orientationchange'].forEach(eventName => {
                window.addEventListener(eventName, scheduleMeasure, { passive: true });
            });

            document.addEventListener('DOMContentLoaded', () => {
                scheduleMeasure();
                observeElements();
            });

            // Dernier filet de sécurité si les éléments apparaissent tardivement
            setTimeout(scheduleMeasure, 500);
        })();

        // Intercepteur global pour fetch afin de gérer les erreurs 401 silencieusement
        // Pour /me et /logout, les erreurs 401 sont normales si l'utilisateur n'est pas authentifié
        (function() {
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                const url = args[0]?.toString() || '';
                const isMeOrLogout = url.includes('/me') || url.includes('/logout');
                
                // Si ce n'est pas /me ou /logout, laisser passer normalement
                if (!isMeOrLogout) {
                    return originalFetch.apply(this, args);
                }
                
                // Pour /me et /logout, intercepter les erreurs 401
                return originalFetch.apply(this, args)
                    .then(response => {
                        // Si c'est une erreur 401, retourner une réponse vide silencieusement
                        if (!response.ok && response.status === 401) {
                            // Ne pas logger pour éviter le bruit dans la console
                            return new Response(JSON.stringify({}), {
                                status: 200,
                                statusText: 'OK',
                                headers: { 'Content-Type': 'application/json' },
                                ok: true
                            });
                        }
                        // Pour les autres statuts, retourner la réponse originale
                        return response;
                    })
                    .catch(error => {
                        // Ignorer silencieusement les erreurs de réseau pour /me et /logout
                        return new Response(JSON.stringify({}), {
                            status: 200,
                            statusText: 'OK',
                            headers: { 'Content-Type': 'application/json' },
                            ok: true
                        });
                    });
            };
        })();

        // Intercepteur pour XMLHttpRequest (pour les anciennes requêtes)
        (function() {
            const originalOpen = XMLHttpRequest.prototype.open;
            const originalSend = XMLHttpRequest.prototype.send;
            
            XMLHttpRequest.prototype.open = function(method, url, ...rest) {
                this._url = url;
                return originalOpen.apply(this, [method, url, ...rest]);
            };
            
            XMLHttpRequest.prototype.send = function(...args) {
                const url = this._url || '';
                const isMeOrLogout = url.includes('/me') || url.includes('/logout');
                
                if (isMeOrLogout) {
                    // Intercepter les erreurs avant qu'elles n'apparaissent dans la console
                    const originalOnError = this.onerror;
                    const originalOnLoad = this.onload;
                    
                    this.onerror = function() {
                        // Ignorer silencieusement les erreurs
                        if (originalOnError) {
                            try {
                                originalOnError.apply(this, arguments);
                            } catch(e) {}
                        }
                    };
                    
                    this.onload = function() {
                        // Si c'est une erreur 401, ne pas la propager
                        if (this.status === 401) {
                            // Ne pas logger pour éviter le bruit dans la console
                            // Appeler le callback original si défini
                            if (originalOnLoad) {
                                try {
                                    // Modifier le statut pour éviter les erreurs
                                    Object.defineProperty(this, 'status', { value: 200, writable: false });
                                    originalOnLoad.apply(this, arguments);
                                } catch(e) {}
                            }
                            return;
                        }
                        if (originalOnLoad) {
                            try {
                                originalOnLoad.apply(this, arguments);
                            } catch(e) {}
                        }
                    };
                }
                
                return originalSend.apply(this, args);
            };
        })();

        // Supprimer les erreurs 401 de la console pour /me et /logout
        (function() {
            const originalError = console.error;
            const originalWarn = console.warn;
            
            console.error = function(...args) {
                const message = args.join(' ');
                // Ignorer les erreurs 401 pour /me et /logout
                if (message.includes('401') && (message.includes('/me') || message.includes('/logout'))) {
                    return;
                }
                // Ignorer les erreurs "Failed to load resource" pour /me et /logout
                if (message.includes('Failed to load resource') && (message.includes('/me') || message.includes('/logout'))) {
                    return;
                }
                originalError.apply(console, args);
            };
            
            console.warn = function(...args) {
                const message = args.join(' ');
                // Ignorer les avertissements 401 pour /me et /logout
                if (message.includes('401') && (message.includes('/me') || message.includes('/logout'))) {
                    return;
                }
                originalWarn.apply(console, args);
            };
        })();

        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        const notificationState = {
            initialized: false,
            templates: {
                empty: `
                    <li class="dropdown-item text-center py-4 text-muted">
                        <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
                        Aucune notification pour le moment
                    </li>
                `,
                error: `
                    <li class="dropdown-item text-center py-4 text-danger">
                        <i class="fas fa-triangle-exclamation fa-2x mb-2 d-block"></i>
                        Impossible de charger les notifications
                    </li>
                `
            }
        };

        function renderNotifications(containerId, notifications) {
            const container = document.getElementById(containerId);
            if (!container) return;

            const unread = (notifications || []).filter(notification => !notification.read_at);

            if (!unread.length) {
                container.innerHTML = notificationState.templates.empty;
                return;
            }

            container.innerHTML = unread.map(notification => {
                const data = notification.data || {};
                const title = data.title || 'Notification';
                const message = data.excerpt || data.message || data.body || '';
                const createdAt = window.dayjs ? dayjs(notification.created_at).fromNow() : (data.created_at_formatted || '');
                const url = data.button_url || data.action_url || null;
                const ctaText = data.button_text || data.action_text || 'Voir les détails';
                const badge = notification.read_at ? '' : '<span class="badge bg-primary me-2">Nouveau</span>';

                return `
                    <li class="dropdown-item px-3 py-3 ${notification.read_at ? '' : 'bg-light'}" style="white-space: normal;">
                        <div class="d-flex flex-column gap-1">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <span class="fw-semibold text-truncate">${badge}${title}</span>
                                <small class="text-muted flex-shrink-0">${createdAt}</small>
                            </div>
                            <p class="mb-0 text-muted" style="overflow-wrap: anywhere;">${message}</p>
                            ${url ? `
                                <div>
                                    <a href="${url}" class="btn btn-sm btn-outline-primary mt-2 text-truncate" target="_blank" rel="noopener" style="max-width: 100%;">
                                        ${ctaText}
                                    </a>
                                </div>` : ''}
                        </div>
                    </li>
                `;
            }).join('');
        }

        function updateNotificationBadge(count) {
            const badgeDesktop = document.getElementById('notification-count');
            const badgeMobile = document.getElementById('notification-count-mobile');
            const displayValue = count > 0 ? 'inline' : 'none';
            const text = count > 99 ? '99+' : count;

            [badgeDesktop, badgeMobile].forEach(badge => {
                if (badge) {
                    badge.textContent = text;
                    badge.style.display = displayValue;
                }
            });
        }

        const NOTIFICATIONS_RECENT_URL = '/notifications/recent';
        const NOTIFICATIONS_COUNT_URL = '/notifications/unread-count';

        async function loadNotifications(force = false) {
            try {
                const [recentResponse, countResponse] = await Promise.all([
                    fetch(NOTIFICATIONS_RECENT_URL, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    }),
                    fetch(NOTIFICATIONS_COUNT_URL, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    })
                ]);

                if (!recentResponse.ok || !countResponse.ok) {
                    throw new Error('Échec du chargement des notifications');
                }

                const notifications = await recentResponse.json();
                const { count } = await countResponse.json();

                renderNotifications('notifications-list', notifications);
                renderNotifications('notifications-list-mobile', notifications);
                updateNotificationBadge(count);

                notificationState.initialized = true;

                if (force && notifications.length === 0) {
                    document.querySelectorAll('.notification-dropdown.show').forEach(dropdown => {
                        const toggle = dropdown.previousElementSibling?.querySelector('[data-bs-toggle="dropdown"]');
                        if (toggle) {
                            const instance = bootstrap.Dropdown.getInstance(toggle);
                            instance?.hide();
                        }
                    });
                }
            } catch (error) {
                console.error('Erreur de chargement des notifications:', error);
                if (!notificationState.initialized) {
                    renderNotifications('notifications-list', []);
                    renderNotifications('notifications-list-mobile', []);
                }
            }
        }

        function initNotificationDropdowns() {
            const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"][title="Notifications"]');
            dropdowns.forEach(dropdownToggle => {
                dropdownToggle.addEventListener('show.bs.dropdown', () => {
                    loadNotifications();
                });

                dropdownToggle.addEventListener('hide.bs.dropdown', () => {
                    loadNotifications(true);
                });
            });
        }

        // Load cart count
        function loadCartCount() {
            fetch('{{ route("cart.count") }}')
                .then(response => response.json())
                .then(data => {
                    const cartCount = document.getElementById('cart-count');
                    const cartCountMobile = document.getElementById('cart-count-mobile');
                    
                    if (cartCount) {
                        cartCount.textContent = data.count;
                    }
                    if (cartCountMobile) {
                        cartCountMobile.textContent = data.count;
                    }
                })
                .catch(error => {
                    console.error('Error loading cart count:', error);
                });
        }


        // Control mobile menu visibility
        function controlMobileMenu() {
            const mobileMenu = document.getElementById('navbarNav');
            const isMobile = window.innerWidth < 992;
            
            if (mobileMenu) {
                if (isMobile) {
                    // On mobile, ensure menu is closed by default
                    mobileMenu.classList.remove('d-none');
                    mobileMenu.classList.remove('show');
                    mobileMenu.setAttribute('aria-expanded', 'false');
                } else {
                    // On desktop, hide the mobile menu
                    mobileMenu.classList.add('d-none');
                }
            }
        }

        // Mobile bottom navigation active state
        function setActiveBottomNav() {
            const currentPath = window.location.pathname;
            const navItems = document.querySelectorAll('.mobile-bottom-nav-item');
            
            navItems.forEach(item => {
                const link = item.closest('a');
                if (link) {
                    const href = link.getAttribute('href');
                    
                    // Check if current path matches
                    if (currentPath === href || currentPath === '/') {
                        if (currentPath === '/' && href.includes('home')) {
                            item.classList.add('active');
                        } else if (href && href !== '/' && currentPath.startsWith(href)) {
                            item.classList.add('active');
                        }
                    }
                }
            });
        }

        // Load notifications on page load
        document.addEventListener('DOMContentLoaded', function() {
            initNotificationDropdowns();
            loadNotifications();
            loadCartCount();
            controlMobileMenu();
            setActiveBottomNav();
            
            // Gestion du modal "Plus" qui s'anime depuis le bas
            const moreModal = document.getElementById('moreModal');
            if (moreModal) {
                // Lorsque le modal s'affiche
                moreModal.addEventListener('show.bs.modal', function() {
                    const modalDialog = this.querySelector('.modal-dialog-bottom');
                    if (modalDialog) {
                        setTimeout(() => {
                            modalDialog.style.transform = 'translateY(0)';
                        }, 10);
                    }
                });
                
                // Lorsque le modal se cache
                moreModal.addEventListener('hide.bs.modal', function() {
                    const modalDialog = this.querySelector('.modal-dialog-bottom');
                    if (modalDialog) {
                        modalDialog.style.transform = 'translateY(100%)';
                    }
                });
            }
            
            // Refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
        });

        // Control mobile menu on window resize
        window.addEventListener('resize', function() {
            controlMobileMenu();
        });

        // Auto-close mobile menu when clicking on a link
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenu = document.getElementById('navbarNav');
            const mobileLinks = mobileMenu.querySelectorAll('.nav-link:not(.dropdown-toggle)');
            
            // Ensure menu is closed on page load
            if (mobileMenu) {
                mobileMenu.classList.remove('show');
                mobileMenu.setAttribute('aria-expanded', 'false');
            }
            
            mobileLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Close the mobile menu only for regular links, not dropdown toggles
                    if (mobileMenu.classList.contains('show') && !link.classList.contains('dropdown-toggle')) {
                        mobileMenu.classList.remove('show');
                        mobileMenu.setAttribute('aria-expanded', 'false');
                    }
                });
            });
            
            // Handle dropdown items (submenu links) - close menu when clicked
            const dropdownItems = mobileMenu.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Close the mobile menu when clicking on dropdown items
                    if (mobileMenu.classList.contains('show')) {
                        mobileMenu.classList.remove('show');
                        mobileMenu.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        });

        // ========================================
        // MÉTHODE GLOBALE UNIFIÉE POUR AJOUT AU PANIER
        // ========================================
        
        // Fonction globale pour ajouter un cours au panier (utilisée sur tout le site)
        function addToCart(courseId) {
            fetch('{{ route("cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    course_id: courseId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour le compteur du panier
                    updateCartCount();
                    
                    // Vérifier si on est sur la page du panier (vérifier plusieurs éléments possibles)
                    const isOnCartPage = document.getElementById('cart-main-container') !== null 
                        || document.getElementById('empty-cart-container') !== null
                        || window.location.pathname.includes('/cart');
                    
                    if (isOnCartPage) {
                        // Si on est sur la page du panier, recharger la page pour afficher le nouveau cours
                        showNotification('Cours ajouté au panier !', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        // Si on n'est pas sur la page du panier, juste afficher la notification
                        showNotification('Cours ajouté au panier !', 'success');
                    }
                } else {
                    showNotification(data.message || 'Erreur lors de l\'ajout au panier', 'error');
                }
            })
            .catch(error => {
                console.error('Error adding to cart:', error);
                showNotification('Erreur lors de l\'ajout au panier', 'error');
            });
        }

        // Fonction globale pour procéder au checkout depuis n'importe quelle page
        // Si un courseId est fourni, on tente d'ajouter d'abord le cours au panier
        function proceedToCheckout(courseId = null) {
            const redirectToCheckout = () => {
                window.location.href = '{{ route("cart.checkout") }}';
            };

            // Si aucun courseId ou bouton depuis page panier, on redirige directement
            if (!courseId) {
                redirectToCheckout();
                return;
            }

            // Ajouter au panier puis rediriger
            fetch('{{ route("cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ course_id: courseId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount();
                    // Laisse une petite marge pour mise à jour UI avant redirection
                    setTimeout(redirectToCheckout, 150);
                } else {
                    // Même en cas d'échec d'ajout, tenter la redirection checkout
                    redirectToCheckout();
                }
            })
            .catch(() => {
                redirectToCheckout();
            });
        }

        // Fonction pour gérer la transition sur la page du panier
        function handleCartPageTransition() {
            const emptyCartContainer = document.getElementById('empty-cart-container');
            const emptyCartRecommendations = document.getElementById('empty-cart-recommendations');
            const mainContainer = document.getElementById('cart-main-container');
            const recommendationsContainer = document.querySelector('.recommendations-container');
            
            // Si le panier était vide, masquer les éléments de panier vide
            if (emptyCartContainer && emptyCartContainer.style.display !== 'none') {
                emptyCartContainer.style.display = 'none';
            }
            
            if (emptyCartRecommendations && emptyCartRecommendations.style.display !== 'none') {
                emptyCartRecommendations.style.display = 'none';
            }
            
            // Afficher le contenu principal du panier
            if (mainContainer) {
                mainContainer.style.display = 'block';
            }
            
            // Afficher les recommandations pour panier plein
            if (recommendationsContainer) {
                recommendationsContainer.style.display = 'block';
            }
            
            // Mettre à jour la liste du panier si la fonction existe
            if (typeof updateCartList === 'function') {
                updateCartList();
            }
        }

        // Fonction pour mettre à jour le compteur du panier
        function updateCartCount() {
            fetch('{{ route("cart.count") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Mettre à jour le compteur desktop
                const cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    cartCount.textContent = data.count;
                }
                
                // Mettre à jour le compteur mobile
                const cartCountMobile = document.getElementById('cart-count-mobile');
                if (cartCountMobile) {
                    cartCountMobile.textContent = data.count;
                }
            })
            .catch(error => {
                console.error('Error updating cart count:', error);
            });
        }

        // Fonction pour mettre à jour seulement les articles du panier (sans toucher au résumé)
        function updateCartItemsOnly() {
            fetch('{{ route("cart.index") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(html => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                // Extraire seulement les articles
                const newCartItems = tempDiv.querySelector('#cart-items-container');
                if (newCartItems) {
                    const currentCartItems = document.getElementById('cart-items-container');
                    if (currentCartItems) {
                        currentCartItems.innerHTML = newCartItems.innerHTML;
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart items:', error);
            });
        }

        // Fonction pour mettre à jour la liste du panier (pour la page du panier)
        function updateCartList() {
            fetch('{{ route("cart.index") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(html => {
                // Créer un élément temporaire pour parser le HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                // Extraire le conteneur principal complet
                const newMainContainer = tempDiv.querySelector('#cart-main-container');
                const newCartItems = tempDiv.querySelector('#cart-items-container');
                const newCartSummary = tempDiv.querySelector('#cart-summary');
                
                if (newMainContainer) {
                    // Préserver la structure Bootstrap en mettant à jour seulement le contenu
                    const currentMainContainer = document.getElementById('cart-main-container');
                    if (currentMainContainer) {
                        // Sauvegarder les classes Bootstrap du conteneur
                        const containerClasses = currentMainContainer.className;
                        
                        // Remplacer le contenu
                        currentMainContainer.innerHTML = newMainContainer.innerHTML;
                        
                        // Restaurer les classes Bootstrap
                        currentMainContainer.className = containerClasses;
                    }
                } else if (newCartItems && newCartSummary) {
                    // Fallback: mettre à jour les éléments individuels si le conteneur principal n'est pas trouvé
                    const currentCartItems = document.getElementById('cart-items-container');
                    if (currentCartItems) {
                        currentCartItems.innerHTML = newCartItems.innerHTML;
                    }
                    
                    const currentCartSummary = document.getElementById('cart-summary');
                    if (currentCartSummary) {
                        currentCartSummary.innerHTML = newCartSummary.innerHTML;
                    }
                }
                
                // Vérifier si le panier est vide
                const cartItems = tempDiv.querySelectorAll('.cart-item-modern');
                if (cartItems.length === 0) {
                    // Afficher le message de panier vide
                    const emptyCartMessage = tempDiv.querySelector('#empty-cart-container');
                    if (emptyCartMessage) {
                        const currentEmptyCart = document.getElementById('empty-cart-container');
                        if (currentEmptyCart) {
                            currentEmptyCart.innerHTML = emptyCartMessage.innerHTML;
                            currentEmptyCart.style.display = 'block';
                        }
                    }
                    
                    // Afficher les recommandations pour panier vide
                    const emptyCartRecommendations = tempDiv.querySelector('#empty-cart-recommendations');
                    if (emptyCartRecommendations) {
                        const currentEmptyRecommendations = document.getElementById('empty-cart-recommendations');
                        if (currentEmptyRecommendations) {
                            currentEmptyRecommendations.innerHTML = emptyCartRecommendations.innerHTML;
                            currentEmptyRecommendations.style.display = 'block';
                        }
                    }
                    
                    // Masquer le contenu principal du panier
                    const mainContainer = document.getElementById('cart-main-container');
                    if (mainContainer) {
                        mainContainer.style.display = 'none';
                    }
                    
                    // Masquer les recommandations pour panier plein
                    const recommendationsContainer = document.querySelector('.recommendations-container');
                    if (recommendationsContainer) {
                        recommendationsContainer.style.display = 'none';
                    }
                } else {
                    // Le panier n'est pas vide, s'assurer que les éléments sont visibles
                    const mainContainer = document.getElementById('cart-main-container');
                    if (mainContainer) {
                        mainContainer.style.display = 'block';
                    }
                    
                    // Masquer le message de panier vide
                    const emptyCartContainer = document.getElementById('empty-cart-container');
                    if (emptyCartContainer) {
                        emptyCartContainer.style.display = 'none';
                    }
                    
                    // Masquer les recommandations pour panier vide
                    const emptyCartRecommendations = document.getElementById('empty-cart-recommendations');
                    if (emptyCartRecommendations) {
                        emptyCartRecommendations.style.display = 'none';
                    }
                    
                    // Afficher les recommandations pour panier plein
                    const recommendationsContainer = document.querySelector('.recommendations-container');
                    if (recommendationsContainer) {
                        recommendationsContainer.style.display = 'block';
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart list:', error);
            });
        }

        // Fonction pour afficher les notifications
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
        
        /* Compteur à rebours pour les promotions */
        function initPromotionCountdowns() {
            const countdowns = document.querySelectorAll('.promotion-countdown[data-sale-end]');
            
            countdowns.forEach((countdown, index) => {
                // Ne pas initialiser plusieurs fois le même compteur
                if (countdown.dataset.initialized === 'true') {
                    return;
                }
                countdown.dataset.initialized = 'true';
                
                const saleEndString = countdown.getAttribute('data-sale-end');
                if (!saleEndString) {
                    return;
                }
                
                const saleEndDate = new Date(saleEndString);
                
                // Vérifier que la date est valide
                if (isNaN(saleEndDate.getTime())) {
                    console.error('Date de fin de promotion invalide:', saleEndString);
                    countdown.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i><span>Date invalide</span>';
                    return;
                }
                
                // Mise à jour immédiate
                updateCountdown(countdown, saleEndDate);
                
                // Mettre à jour toutes les secondes
                const intervalId = setInterval(() => {
                    updateCountdown(countdown, saleEndDate);
                }, 1000);
                
                // Stocker l'ID de l'intervalle pour pouvoir le nettoyer si nécessaire
                countdown.dataset.intervalId = intervalId;
            });
        }
        
        function updateCountdown(element, endDate) {
            const now = new Date();
            const diff = endDate - now;
            
            if (diff <= 0) {
                // La promotion est terminée
                element.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i><span>Promotion terminée</span>';
                return;
            }
            
            // Calculer les années
            let years = endDate.getFullYear() - now.getFullYear();
            let months = endDate.getMonth() - now.getMonth();
            let days = endDate.getDate() - now.getDate();
            let hours = endDate.getHours() - now.getHours();
            let minutes = endDate.getMinutes() - now.getMinutes();
            let seconds = endDate.getSeconds() - now.getSeconds();
            
            // Ajuster les valeurs négatives
            if (seconds < 0) {
                seconds += 60;
                minutes--;
            }
            if (minutes < 0) {
                minutes += 60;
                hours--;
            }
            if (hours < 0) {
                hours += 24;
                days--;
            }
            if (days < 0) {
                const daysInPreviousMonth = new Date(now.getFullYear(), now.getMonth(), 0).getDate();
                days += daysInPreviousMonth;
                months--;
            }
            if (months < 0) {
                months += 12;
                years--;
            }
            
            // Mettre à jour les éléments
            const yearsEl = element.querySelector('.countdown-years');
            const monthsEl = element.querySelector('.countdown-months');
            const daysEl = element.querySelector('.countdown-days');
            const hoursEl = element.querySelector('.countdown-hours');
            const minutesEl = element.querySelector('.countdown-minutes');
            
            if (yearsEl) yearsEl.textContent = years;
            if (monthsEl) monthsEl.textContent = months;
            if (daysEl) daysEl.textContent = days;
            if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
            if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
            
            // Masquer les unités à zéro (sauf minutes)
            if (yearsEl && yearsEl.parentElement) {
                yearsEl.parentElement.style.display = years > 0 ? 'inline' : 'none';
            }
            if (monthsEl && monthsEl.parentElement) {
                monthsEl.parentElement.style.display = months > 0 || years > 0 ? 'inline' : 'none';
            }
            if (daysEl && daysEl.parentElement) {
                daysEl.parentElement.style.display = days > 0 || months > 0 || years > 0 ? 'inline' : 'none';
            }
            if (hoursEl && hoursEl.parentElement) {
                hoursEl.parentElement.style.display = hours > 0 || days > 0 || months > 0 || years > 0 ? 'inline' : 'none';
            }
        }
        
        // Initialiser les compteurs au chargement
        document.addEventListener('DOMContentLoaded', function() {
            initPromotionCountdowns();
        });
        
        // Exposer la fonction globalement pour qu'elle soit accessible depuis d'autres scripts
        window.initPromotionCountdowns = initPromotionCountdowns;
    </script>

    <script>
        // Rendre les cartes de cours entièrement cliquables
        document.addEventListener('DOMContentLoaded', function() {
            const courseCards = document.querySelectorAll('.course-card[data-course-url]');
            
            courseCards.forEach(function(card) {
                card.addEventListener('click', function(e) {
                    // Ne pas rediriger si on clique sur un bouton, un lien ou dans la zone d'actions
                    const clickedElement = e.target;
                    const isButton = clickedElement.closest('.card-actions, .btn, button, a.btn, form');
                    const isBadge = clickedElement.classList.contains('badge') || clickedElement.closest('.badge');
                    
                    // Si on clique sur un badge ou un bouton, ne pas rediriger
                    if (isButton || isBadge) {
                        return;
                    }
                    
                    // Si on clique directement sur un lien (autre que le lien de la carte), ne pas rediriger
                    if (clickedElement.tagName === 'A' && !clickedElement.classList.contains('course-card-link')) {
                        return;
                    }
                    
                    const courseUrl = card.getAttribute('data-course-url');
                    if (courseUrl) {
                        window.location.href = courseUrl;
                    }
                });
            });
        });
    </script>

    @stack('modals')
    
    <!-- Plyr Video Player Library JS -->
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    
    @stack('scripts')
    </body>
</html>