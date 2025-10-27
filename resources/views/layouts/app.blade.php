<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Herime Academie - Plateforme d\'apprentissage en ligne')</title>
    <meta name="description" content="@yield('description', 'Découvrez des milliers de cours en ligne de qualité avec Herime Academie. Formations professionnelles, certifications et expertise garanties.')">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        /* Global Font Styles */
        * {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 1rem;
            line-height: 1.6;
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
            height: 45px;
            max-width: 180px;
            object-fit: contain;
        }
        
        .footer-logo {
            height: 50px;
            max-width: 250px;
            object-fit: contain;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 576px) {
            .navbar-logo-mobile {
                height: 40px;
                max-width: 160px;
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
        .course-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }

        .course-card .card {
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            height: 100%;
        }

        .course-card .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15), 0 4px 6px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }

        .course-card .card-img-top {
            height: 160px;
            width: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        .course-card .card:hover .card-img-top {
            transform: scale(1.02);
        }

        .course-card .card-body {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            height: calc(100% - 160px);
        }

        .course-card .card-title {
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 0.5rem;
            height: 2.8rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
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
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 0.75rem;
            height: 2.625rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .course-card .instructor-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding: 0.5rem 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
        }

        .course-card .instructor-name {
            color: #475569;
            font-size: 0.8rem;
            font-weight: 500;
            max-width: 60%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .course-card .rating {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .course-card .rating i {
            color: #fbbf24;
            font-size: 0.75rem;
        }

        .course-card .rating span {
            font-size: 0.8rem;
            color: #475569;
            font-weight: 500;
        }

        .course-card .price-duration {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem 0.75rem;
            background: #f1f5f9;
            border-radius: 8px;
        }

        .course-card .price {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1rem;
        }

        .course-card .price .text-muted {
            font-size: 0.75rem;
            text-decoration: line-through;
            color: #94a3b8;
        }

        .course-card .duration {
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .course-card .card-actions {
            margin-top: auto;
        }

        .course-card .btn {
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.625rem 1rem;
            transition: all 0.2s ease;
            text-transform: none;
        }

        .course-card .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .course-card .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
        }

        .course-card .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
            border-color: var(--primary-color);
            color: white;
        }

        .course-card .btn-primary:hover {
            background: linear-gradient(135deg, #002244 0%, var(--primary-color) 100%);
            border-color: #002244;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.4);
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

        /* Mobile responsive */
        @media (max-width: 767.98px) {
            .course-card .card {
                margin-bottom: 1rem;
            }
            
            .course-card .card-body {
                padding: 1rem;
            }
            
            .course-card .card-img-top {
                height: 140px;
                width: 100%;
                object-fit: cover;
                object-position: center;
            }
            
            .course-card .card-body {
                height: calc(100% - 140px);
            }
            
            .course-card .card-title {
                font-size: 0.95rem;
                height: 2.6rem;
                line-height: 1.3;
            }
            
            .course-card .card-text {
                font-size: 0.8rem;
                height: 2.4rem;
                line-height: 1.4;
            }
            
            .course-card .instructor-info {
                padding: 0.5rem 0.75rem;
                margin-bottom: 0.5rem;
            }
            
            .course-card .instructor-name {
                font-size: 0.75rem;
            }
            
            .course-card .rating span {
                font-size: 0.75rem;
            }
            
            .course-card .price-duration {
                padding: 0.5rem 0.75rem;
                margin-bottom: 0.75rem;
            }
            
            .course-card .price {
                font-size: 0.95rem;
            }
            
            .course-card .duration {
                font-size: 0.75rem;
            }
            
            .course-card .btn {
                font-size: 0.8rem;
                padding: 0.5rem 0.75rem;
                margin-bottom: 0.25rem;
            }
            
            .course-card .card-actions .btn:last-child {
                margin-bottom: 0;
            }
        }

        /* Extra small screens */
        @media (max-width: 575.98px) {
            .course-card .card {
                margin-bottom: 0.75rem;
            }
            
            .course-card .card-img-top {
                height: 120px;
                width: 100%;
                object-fit: cover;
                object-position: center;
            }
            
            .course-card .card-body {
                height: calc(100% - 120px);
                padding: 0.75rem;
            }
            
            .course-card .card-title {
                font-size: 0.9rem;
                height: 2.4rem;
                line-height: 1.2;
            }
            
            .course-card .card-text {
                font-size: 0.75rem;
                height: 2.2rem;
                line-height: 1.3;
            }
            
            .course-card .instructor-info {
                padding: 0.375rem 0.5rem;
                margin-bottom: 0.5rem;
            }
            
            .course-card .instructor-name {
                font-size: 0.7rem;
            }
            
            .course-card .rating span {
                font-size: 0.7rem;
            }
            
            .course-card .price-duration {
                padding: 0.375rem 0.5rem;
                margin-bottom: 0.5rem;
            }
            
            .course-card .price {
                font-size: 0.9rem;
            }
            
            .course-card .duration {
                font-size: 0.7rem;
            }
            
            .course-card .btn {
                font-size: 0.75rem;
                padding: 0.4rem 0.6rem;
                margin-bottom: 0.25rem;
            }
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
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
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
            box-shadow: var(--shadow);
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
            .navbar-brand.mx-auto {
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
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
        }

        @media (max-width: 991.98px) {
            .mobile-bottom-nav {
                display: flex;
            }
            
            /* Add padding to main content to prevent overlap with bottom nav */
            main {
                padding-bottom: 60px;
            }

            /* Add margin to footer on mobile to prevent overlap with bottom nav */
            .footer {
                margin-bottom: 60px;
            }
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
            max-height: 70vh;
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
            max-height: 70vh;
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
            max-height: calc(70vh - 60px);
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
    </head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <!-- Mobile Layout -->
            <div class="d-flex d-lg-none w-100 align-items-center position-relative">
                <!-- Left: Nous contacter -->
                <a href="{{ route('contact') }}" class="btn btn-sm btn-link flex-shrink-0" style="z-index: 10; border: none; background: transparent; color: var(--primary-color); text-decoration: none;">
                    <i class="fas fa-envelope fa-lg" style="color: var(--primary-color);"></i>
                    <span class="d-none d-sm-inline ms-1">Contact</span>
                </a>
                
                <!-- Center: Logo (absolute centered) -->
                <a class="navbar-brand position-absolute start-50 translate-middle-x" href="{{ route('home') }}" style="z-index: 1;">
                    <img src="{{ asset('images/logo-herime-academie.png') }}" alt="Herime Academie" class="navbar-logo-mobile">
                </a>
                
                <!-- Right: Notifications and Cart -->
                <div class="d-flex align-items-center ms-auto flex-shrink-0" style="z-index: 10;">
                    @auth
                        <!-- Notifications -->
                        <div class="dropdown me-2">
                            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" title="Notifications">
                                <i class="fas fa-bell fa-lg"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" id="notification-count-mobile" style="display: none; background-color: var(--primary-color); color: white;">
                                    0
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 350px;">
                                <li class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span>Notifications</span>
                                    <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <div id="notifications-list-mobile">
                                    <li class="dropdown-item text-center py-3">
                                        <i class="fas fa-spinner fa-spin"></i> Chargement...
                                    </li>
                                </div>
                            </ul>
                        </div>
                    @endauth
                    
                    <!-- Cart -->
                    <a class="nav-link position-relative" href="{{ route('cart.index') }}" title="Panier">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" id="cart-count-mobile" style="background-color: var(--primary-color); color: white;">
                            0
                        </span>
                    </a>
                </div>
            </div>
            
            <!-- Desktop Layout -->
            <div class="d-none d-lg-flex w-100 align-items-center">
                <!-- Logo -->
                <a class="navbar-brand" href="{{ route('home') }}">
                    <img src="{{ asset('images/logo-herime-academie.png') }}" alt="Herime Academie" class="navbar-logo">
                </a>
                
                <!-- Navigation Menu -->
                <div class="navbar-nav me-auto">
                    <a class="nav-link" href="{{ route('courses.index') }}">Cours</a>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Catégories
                        </a>
                        <ul class="dropdown-menu">
                            @foreach(\App\Models\Category::active()->ordered()->limit(6)->get() as $category)
                                <li><a class="dropdown-item" href="{{ route('courses.category', $category->slug) }}">{{ $category->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <a class="nav-link" href="{{ route('instructors.index') }}">Formateurs</a>
                    <a class="nav-link" href="{{ route('about') }}">À propos</a>
                    <a class="nav-link" href="{{ route('contact') }}">Contact</a>
                </div>
                
                <!-- Right Side Actions -->
                <div class="navbar-nav">
                    <!-- Cart -->
                    <a class="nav-link position-relative me-3" href="{{ route('cart.index') }}" title="Panier">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" id="cart-count" style="background-color: var(--primary-color); color: white;">
                            0
                        </span>
                    </a>
                    
                    @auth
                        <!-- Notifications -->
                        <div class="nav-item dropdown me-3">
                            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" title="Notifications">
                                <i class="fas fa-bell fa-lg"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" id="notification-count" style="display: none; background-color: var(--primary-color); color: white;">
                                    0
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 350px;">
                                <li class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span>Notifications</span>
                                    <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <div id="notifications-list">
                                    <li class="dropdown-item text-center py-3">
                                        <i class="fas fa-spinner fa-spin"></i> Chargement...
                                    </li>
                                </div>
                            </ul>
                        </div>

                        <!-- Messages -->
                        <a class="nav-link position-relative me-3" href="{{ route('messages.index') }}" title="Messages">
                            <i class="fas fa-envelope fa-lg"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" id="message-count" style="display: none; background-color: var(--primary-color); color: white;">
                                0
                            </span>
                        </a>

                        <!-- User Menu -->
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" title="Mon compte">
                                @if(Auth::user()->avatar)
                                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="rounded-circle" width="32" height="32">
                                @else
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">Tableau de bord</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile') }}">Profil</a></li>
                                <li><a class="dropdown-item" href="{{ route('messages.index') }}">Messages</a></li>
                                <li><a class="dropdown-item" href="{{ route('notifications.index') }}">Notifications</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Déconnexion</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a class="nav-link me-3" href="{{ route('login') }}" title="Connexion">
                            <i class="fas fa-sign-in-alt fa-lg"></i>
                            <span class="d-none d-lg-inline ms-1">Connexion</span>
                        </a>
                        <a class="btn btn-primary" href="{{ route('register') }}" title="S'inscrire">
                            <i class="fas fa-user-plus me-1"></i>
                            <span class="d-none d-lg-inline">S'inscrire</span>
                        </a>
                    @endauth
                </div>
            </div>
            
            <!-- Mobile Menu (Collapsed) - Hidden on mobile as we use bottom nav now -->
            <div class="collapse navbar-collapse d-lg-none" id="navbarNav" aria-expanded="false" style="display: none !important;">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="fas fa-home me-2"></i>Accueil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('courses.index') }}">
                            <i class="fas fa-book me-2"></i>Cours
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-th-large me-2"></i>Catégories
                        </a>
                        <ul class="dropdown-menu">
                            @foreach(\App\Models\Category::active()->ordered()->limit(6)->get() as $category)
                                <li><a class="dropdown-item" href="{{ route('courses.category', $category->slug) }}">{{ $category->name }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('instructors.index') }}">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Formateurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('about') }}">
                            <i class="fas fa-info-circle me-2"></i>À propos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('contact') }}">
                            <i class="fas fa-envelope me-2"></i>Contact
                        </a>
                    </li>
                    
                    @auth
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="{{ route('messages.index') }}">
                                <i class="fas fa-envelope me-2"></i>Messages
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" id="message-count-mobile" style="display: none; background-color: var(--primary-color); color: white;">
                                    0
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('profile') }}">
                                <i class="fas fa-user me-2"></i>Profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link text-start w-100">
                                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-2"></i>Connexion
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-2"></i>S'inscrire
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

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
                <div class="modal-header">
                    <h5 class="modal-title" id="moreModalLabel">
                        <i class="fas fa-ellipsis-h me-2"></i>Plus d'options
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="list-group list-group-flush">
                        @auth
                            <div class="list-group-item">
                                <a href="{{ route('dashboard') }}">
                                    <i class="fas fa-tachometer-alt"></i>
                                    Tableau de bord
                                </a>
                            </div>
                            <div class="list-group-item">
                                <a href="{{ route('profile') }}">
                                    <i class="fas fa-user"></i>
                                    Profil
                                </a>
                            </div>
                            <div class="list-group-item">
                                <a href="{{ route('orders.index') }}">
                                    <i class="fas fa-shopping-bag"></i>
                                    Mes commandes
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
                            <div class="list-group-item">
                                <a href="{{ route('messages.index') }}">
                                    <i class="fas fa-envelope"></i>
                                    Messages
                                </a>
                            </div>
                            <div class="list-group-item">
                                <a href="{{ route('notifications.index') }}">
                                    <i class="fas fa-bell"></i>
                                    Notifications
                                </a>
                            </div>
                            <div class="list-group-item">
                                <a href="{{ route('instructors.index') }}">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    Formateurs
                                </a>
                            </div>
                            <div class="list-group-item">
                                <form method="POST" action="{{ route('logout') }}" class="w-100">
                                    @csrf
                                    <button type="submit" class="btn-logout">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Déconnexion</span>
                                    </button>
                                </form>
                            </div>
                        @else
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
                            <div class="list-group-item">
                                <a href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Connexion
                                </a>
                            </div>
                            <div class="list-group-item">
                                <a href="{{ route('register') }}">
                                    <i class="fas fa-user-plus"></i>
                                    S'inscrire
                                </a>
                            </div>
                        @endauth
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
    
    <!-- Custom JS -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Footer visibility test (only in development)
        @if(config('app.debug'))
        console.log('🔍 Test de visibilité du footer - Herime Academie');
        
        function checkFooterVisibility() {
            const footer = document.querySelector('.footer');
            if (!footer) {
                console.error('❌ Footer non trouvé sur la page');
                return false;
            }
            
            const footerRect = footer.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            const isVisible = footerRect.top < windowHeight && footerRect.bottom > 0;
            
            if (isVisible) {
                console.log('✅ Footer visible');
            } else {
                console.error('❌ Footer non visible');
            }
            
            return isVisible;
        }
        
        // Vérifier le footer au chargement
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(checkFooterVisibility, 1000);
        });
        @endif

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

        // Load notifications (simplified)
        function loadNotifications() {
            // For now, we'll just hide the notification badges
            const notificationCount = document.getElementById('notification-count');
            const notificationCountMobile = document.getElementById('notification-count-mobile');
            
            if (notificationCount) {
                notificationCount.style.display = 'none';
            }
            if (notificationCountMobile) {
                notificationCountMobile.style.display = 'none';
            }
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
                    
                    // Vérifier si on est sur la page du panier
                    const isOnCartPage = document.getElementById('cart-main-container') !== null;
                    
                    if (isOnCartPage) {
                        // Si on est sur la page du panier, gérer la transition
                        handleCartPageTransition();
                    }
                    
                    // Afficher la notification de succès
                    showNotification('Cours ajouté au panier !', 'success');
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
    </script>

    @stack('scripts')
    </body>
</html>