@php
    use Illuminate\Support\Facades\Route;

    $navItems = [
        [
            'label' => 'Tableau de bord',
            'icon' => 'fas fa-chart-pie',
            'route' => 'admin.dashboard',
            'active' => ['admin.dashboard']
        ],
        [
            'label' => 'Analytics',
            'icon' => 'fas fa-chart-line',
            'route' => 'admin.analytics',
            'active' => ['admin.analytics']
        ],
        [
            'label' => 'Cours',
            'icon' => 'fas fa-book',
            'route' => 'admin.courses',
            'active' => ['admin.courses', 'admin.courses.*']
        ],
        [
            'label' => 'Catégories',
            'icon' => 'fas fa-tags',
            'route' => 'admin.categories',
            'active' => ['admin.categories']
        ],
        [
            'label' => 'Commandes',
            'icon' => 'fas fa-shopping-bag',
            'route' => 'admin.orders.index',
            'active' => ['admin.orders.*']
        ],
        [
            'label' => 'Utilisateurs',
            'icon' => 'fas fa-users-cog',
            'route' => 'admin.users',
            'active' => ['admin.users', 'admin.users.*']
        ],
        [
            'label' => 'Formateurs',
            'icon' => 'fas fa-chalkboard-teacher',
            'route' => 'admin.instructor-applications',
            'active' => ['admin.instructor-applications', 'admin.instructor-applications.*'],
            'available' => Route::has('admin.instructor-applications')
        ],
        [
            'label' => 'Bannières',
            'icon' => 'fas fa-image',
            'route' => 'admin.banners.index',
            'active' => ['admin.banners.*'],
            'available' => Route::has('admin.banners.index')
        ],
        [
            'label' => 'Témoignages',
            'icon' => 'fas fa-quote-left',
            'route' => 'admin.testimonials',
            'active' => ['admin.testimonials', 'admin.testimonials.*'],
            'available' => Route::has('admin.testimonials')
        ],
        [
            'label' => 'Annonces',
            'icon' => 'fas fa-bullhorn',
            'route' => 'admin.announcements',
            'active' => ['admin.announcements']
        ],
        [
            'label' => 'Paramètres',
            'icon' => 'fas fa-sliders-h',
            'route' => 'admin.settings',
            'active' => ['admin.settings']
        ],
    ];

    $pageTitle = trim($__env->yieldContent('admin-title'));
    if ($pageTitle === '') {
        $pageTitle = trim($__env->yieldContent('title')) ?: 'Administration';
    }
    $pageSubtitle = trim($__env->yieldContent('admin-subtitle'));
    $pageActions = trim($__env->yieldContent('admin-actions'));
@endphp

@extends('layouts.app')

@section('content')
<div class="admin-shell container-fluid py-4 px-0 px-md-4">
    <div class="admin-shell__container">
        <aside class="admin-shell__sidebar d-none d-lg-flex flex-column">
            <div class="admin-shell__brand">
                <i class="fas fa-shield-alt me-2"></i>Administration
            </div>
            <nav class="admin-nav flex-grow-1">
                @foreach($navItems as $item)
                    @continue(isset($item['available']) && !$item['available'])
                    @php
                        $activeRoutes = $item['active'] ?? [$item['route']];
                        $isActive = collect($activeRoutes)->contains(fn($routeName) => request()->routeIs($routeName));
                    @endphp
                    <a href="{{ route($item['route']) }}" class="admin-nav__item {{ $isActive ? 'is-active' : '' }}">
                        <span class="admin-nav__icon"><i class="{{ $item['icon'] }}"></i></span>
                        <span class="admin-nav__label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="admin-shell__footer mt-auto">
                <a href="{{ route('home') }}" class="admin-nav__item">
                    <span class="admin-nav__icon"><i class="fas fa-globe"></i></span>
                    <span class="admin-nav__label">Retour au site</span>
                </a>
            </div>
        </aside>

        <main class="admin-shell__content">
            <nav class="admin-shell__mobile-nav d-lg-none" aria-label="Navigation d'administration">
                @foreach($navItems as $item)
                    @continue(isset($item['available']) && !$item['available'])
                    @php
                        $activeRoutes = $item['active'] ?? [$item['route']];
                        $isActive = collect($activeRoutes)->contains(fn($routeName) => request()->routeIs($routeName));
                    @endphp
                    <a href="{{ route($item['route']) }}" class="admin-shell__mobile-link {{ $isActive ? 'is-active' : '' }}" aria-label="{{ $item['label'] }}">
                        <i class="{{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="admin-shell__mobile-nav-spacer d-lg-none" aria-hidden="true"></div>
            @if($pageTitle !== '' || $pageActions !== '')
                <header class="admin-content__header mb-4">
                    <div>
                        <h1 class="admin-content__title">{!! html_entity_decode($pageTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8') !!}</h1>
                        @if($pageSubtitle !== '')
                            <p class="admin-content__subtitle">{!! html_entity_decode($pageSubtitle, ENT_QUOTES | ENT_HTML5, 'UTF-8') !!}</p>
                        @endif
                    </div>
                    @if($pageActions !== '')
                        <div class="admin-content__actions">
                            {!! $pageActions !!}
                        </div>
                    @endif
                </header>
            @endif

            @if (trim($__env->yieldContent('admin-content')) !== '')
                @yield('admin-content')
            @else
                @yield('content')
            @endif
        </main>
    </div>
</div>

@endsection

@push('styles')
<style>
    .admin-shell {
        min-height: calc(100vh - 80px);
        padding-bottom: 4.5rem;
    }
    .admin-shell__container {
        display: flex;
        gap: 1.5rem;
        align-items: flex-start;
    }
    .admin-shell__sidebar {
        width: 250px;
        min-height: calc(100vh - 100px);
        background: #0b1f3a;
        border-radius: 1.5rem;
        padding: 1.5rem 1rem;
        color: #ffffff;
        box-shadow: 0 20px 45px -25px rgba(11, 31, 58, 0.6);
    }
    .admin-shell__brand {
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.75rem;
        background: rgba(255, 255, 255, 0.12);
        border-radius: 999px;
        padding: 0.5rem 1rem;
        margin-bottom: 1.5rem;
    }
    .admin-nav__item {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.75rem 1rem;
        border-radius: 0.85rem;
        color: rgba(255, 255, 255, 0.75);
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .admin-nav__item:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.12);
    }
    .admin-nav__item.is-active {
        background: #ffffff;
        color: #0b1f3a;
        box-shadow: 0 15px 30px -20px rgba(255, 255, 255, 0.55);
    }
    .admin-nav__item:focus-visible {
        outline: 2px solid #fbbf24;
        outline-offset: 4px;
    }
    .admin-shell a:focus-visible,
    .admin-shell button:focus-visible,
    .admin-shell select:focus-visible,
    .admin-shell input:focus-visible,
    .admin-shell textarea:focus-visible {
        outline: 2px solid #2563eb;
        outline-offset: 3px;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
    }
    .admin-shell a:hover,
    .admin-shell button:hover {
        filter: brightness(0.95);
    }
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
    
    /* Sur toutes les tailles sauf desktop (>= 1200px), utiliser le layout mobile */
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
            display: flex !important;
            gap: 0.5rem !important;
            flex-wrap: nowrap !important;
        }
        
        .admin-search-panel__actions .btn {
            flex: 1 1 50% !important;
            white-space: nowrap !important;
        }
    }
    .admin-search-panel__submit-label,
    .admin-search-panel__filters-label {
        display: inline;
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
    .admin-search-panel__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        align-items: center;
    }
    .admin-search-panel__meta > * {
        margin: 0;
    }
    .admin-filter-offcanvas {
        width: min(420px, 100vw);
    }
    .admin-filter-offcanvas .offcanvas-footer {
        background: #f8fafc;
    }
    .admin-nav__icon {
        width: 36px;
        height: 36px;
        border-radius: 0.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.1);
    }
    .admin-nav__item.is-active .admin-nav__icon {
        background: rgba(11, 31, 58, 0.08);
    }
    .admin-shell__content {
        flex: 1;
        min-width: 0;
    }
    .admin-content__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .admin-content__title {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }
    .admin-content__subtitle {
        color: #64748b;
        margin: 0;
    }
    .admin-content__actions .btn {
        border-radius: 999px;
    }
    .admin-shell__mobile-nav {
        position: fixed;
        top: calc(var(--site-navbar-height, 64px) + 0.65rem);
        left: 50%;
        transform: translateX(-50%);
        z-index: 70;
        display: flex;
        align-items: center;
        gap: 0.45rem;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 999px;
        padding: 0.45rem 0.75rem;
        margin: 0 0 1.25rem 0;
        box-shadow: 0 22px 35px -28px rgba(15, 23, 42, 0.5);
        overflow-x: auto;
        max-width: min(92vw, 520px);
        -webkit-overflow-scrolling: touch;
    }
    .admin-shell__mobile-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        flex: 0 0 auto;
        color: #64748b;
        text-decoration: none;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
        white-space: nowrap;
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    .admin-shell__mobile-link i {
        font-size: 0.95rem;
    }
    .admin-shell__mobile-link.is-active {
        color: #003366;
        background: rgba(0, 51, 102, 0.12);
    }
    .admin-shell__mobile-link:focus-visible {
        outline: 2px solid #fbbf24;
        outline-offset: 4px;
    }
    .admin-shell__mobile-nav-spacer {
        display: none;
        height: 0;
    }
    @media (max-width: 991.98px) {
        .admin-shell__content {
            padding: 0 1.5rem 0.5rem;
        }
        .admin-shell__container {
            flex-direction: column;
        }
        .admin-shell__sidebar {
            display: none !important;
        }
        .admin-shell__content {
            width: 100%;
        }
        .admin-search-panel {
            padding: 1.05rem 1.1rem;
        }
        .admin-search-panel__primary {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
        .admin-search-panel__actions {
            width: 100%;
            display: flex;
            gap: 0.5rem;
        }
        .admin-search-panel__actions .btn {
            flex: 1 1 50%;
            white-space: nowrap;
        }
        .admin-shell__mobile-nav-spacer {
            display: block;
            height: calc(var(--site-navbar-height, 64px) + 0.75rem);
        }
    }
    /* Styles spécifiques pour tablette - utiliser le layout mobile */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .admin-search-panel {
            padding: 1.05rem 1.1rem;
        }
        
        .admin-search-panel__search-box {
            padding: 0.6rem 0.9rem;
        }
        
        .admin-search-panel__actions .btn {
            font-size: 0.85rem;
            padding: 0.45rem 0.75rem;
        }
    }
    
    /* Point de rupture où le slider remonte - utiliser le layout mobile */
    @media (min-width: 992px) and (max-width: 1199.98px) {
        .admin-search-panel {
            padding: 1.05rem 1.1rem;
        }
        
        .admin-search-panel__search-box {
            padding: 0.65rem 0.95rem;
        }
        
        .admin-search-panel__actions .btn {
            font-size: 0.85rem;
            padding: 0.5rem 0.8rem;
        }
    }

    @media (max-width: 767px) {
        .admin-shell {
            padding-bottom: 5rem;
        }
        .admin-search-panel {
            padding: 0.95rem 1rem;
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
            display: flex;
            gap: 0.5rem;
        }
        .admin-search-panel__actions .btn {
            flex: 1 1 50%;
            white-space: nowrap;
            height: 42px;
        }
        .admin-dashboard-table {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .admin-dashboard-table table {
            min-width: 640px;
        }
    }
    @media (max-width: 575px) {
        .admin-shell {
            padding-bottom: 4.25rem;
        }
        .quick-actions-grid {
            grid-template-columns: 1fr;
        }
        .admin-search-panel {
            padding: 0.85rem 0.9rem;
        }
        .admin-search-panel__primary {
            gap: 0.55rem;
        }
        .admin-search-panel__search-box {
            padding: 0.48rem 0.75rem;
        }
        .admin-search-panel__actions {
            flex-direction: column;
            align-items: stretch;
            gap: 0.45rem;
        }
        .admin-search-panel__actions .btn {
            width: 100%;
            min-width: 0;
            height: 42px;
        }
        .admin-search-panel__submit-label,
        .admin-search-panel__filters-label {
            display: none;
        }
        .admin-dashboard-table tr {
            padding: 0.85rem 0.95rem;
        }
    }

    .admin-panel {
        background: #ffffff;
        border-radius: 1.5rem;
        box-shadow: 0 24px 60px -35px rgba(15, 23, 42, 0.35);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 1.75rem;
    }
    .admin-panel__header {
        padding: 1.25rem 1.75rem;
        background: linear-gradient(120deg, #003366 0%, #0b4f99 100%);
        color: #ffffff;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        align-items: center;
    }
    .admin-panel__header h2,
    .admin-panel__header h3,
    .admin-panel__header h4 {
        margin: 0;
        font-weight: 600;
    }
    .admin-panel__subtitle {
        margin-top: 0.25rem;
        color: rgba(255, 255, 255, 0.75);
        font-size: 0.85rem;
    }
    .admin-panel__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }
    .admin-panel__body {
        padding: 1.75rem;
    }
    .admin-panel__body--padded {
        padding: 2.5rem;
    }
    .admin-panel__footer {
        padding: 1.25rem 1.75rem;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .admin-stats-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    }
    .admin-stat-card {
        background: linear-gradient(135deg, rgba(0, 51, 102, 0.07) 0%, rgba(0, 51, 102, 0.15) 100%);
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        color: #0b1f3a;
    }
    .admin-stat-card__label {
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.65rem;
        margin-bottom: 0.4rem;
        color: #1d4ed8;
    }
    .admin-stat-card__value {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }
    .admin-stat-card__muted {
        margin-top: 0.25rem;
        color: #475569;
        font-size: 0.8rem;
    }
    .admin-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        margin-bottom: 1.25rem;
    }
    .admin-toolbar__filters {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        align-items: center;
    }
    .admin-toolbar__actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.65rem;
    }
    .admin-form-grid {
        display: grid;
        gap: 1.25rem;
    }
    .admin-form-grid--two {
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    }
    .admin-form-card {
        background: #f9fbff;
        border-radius: 1.25rem;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
    }
    .admin-form-card h5 {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 1rem;
    }
    .admin-table {
        width: 100%;
        border-radius: 1rem;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .admin-table table {
        margin-bottom: 0;
    }
    .admin-table thead {
        background: #f1f5f9;
    }
    .admin-table thead th {
        border-bottom: none;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #475569;
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
    }
    .admin-table tbody td {
        vertical-align: middle;
        border-color: #e2e8f0 !important;
        padding-top: 0.9rem;
        padding-bottom: 0.9rem;
    }
    .admin-table__empty {
        padding: 3rem 1rem;
        text-align: center;
        color: #64748b;
    }
    .admin-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(15, 23, 42, 0.05);
        color: #0b1f3a;
    }
    .admin-chip--success { background: rgba(22, 163, 74, 0.12); color: #15803d; }
    .admin-chip--warning { background: rgba(234, 179, 8, 0.12); color: #b45309; }
    .admin-chip--danger { background: rgba(239, 68, 68, 0.12); color: #b91c1c; }
    .admin-chip--info { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }
    .admin-chip--neutral { background: rgba(15, 23, 42, 0.08); color: #0f172a; }
    .admin-badge-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
    }
    .admin-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 1.5rem;
        padding: 1rem 1.25rem;
        background-color: #ffffff;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }
    
    .admin-pagination__info {
        font-size: 0.875rem;
        white-space: nowrap;
    }
    
    @media (min-width: 768px) {
        .admin-pagination__info {
            padding-left: 1rem;
        }
    }
    
    .admin-pagination__links {
        display: flex;
        justify-content: flex-end;
        flex: 1;
    }
    
    .admin-pagination .pagination {
        margin-bottom: 0;
    }
    
    .admin-pagination nav {
        padding: 0.5rem 0;
    }
    
    @media (max-width: 767.98px) {
        .admin-pagination {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
        }
        
        .admin-pagination__info {
            text-align: center;
            font-size: 0.8rem;
            padding-left: 0;
        }
        
        .admin-pagination__links {
            justify-content: center;
        }
        
        .admin-pagination .pagination {
            justify-content: center;
        }
        
        .admin-pagination nav {
            padding: 0.375rem 0;
        }
    }
    .admin-card-grid {
        display: grid;
        gap: 1.5rem;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    }
    .admin-empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #64748b;
    }
    .admin-empty-state i {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: #003366;
        opacity: 0.65;
    }

    /* Styles globaux pour les dropdowns d'actions dans les listes admin */
    .course-actions-btn--mobile {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.75rem !important;
        line-height: 1.2;
    }

    .course-actions-btn--mobile i {
        font-size: 0.7rem !important;
    }

    /* Styles de base pour tous les dropdowns */
    .dropdown,
    .dropup {
        position: relative;
    }

    /* Menu desktop - dropdown pour première ligne (vers le bas) */
    .dropdown.d-none.d-md-block .dropdown-menu {
        margin-top: 0.25rem;
        top: 100%;
        z-index: 1050 !important;
    }

    /* Flèche pour dropdown desktop première ligne (menu vers le bas) */
    .dropdown.d-none.d-md-block .dropdown-menu::before {
        content: '';
        position: absolute;
        top: -5px;
        right: 12px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-bottom: 5px solid #fff;
        z-index: 1001;
    }

    .dropdown.d-none.d-md-block .dropdown-menu::after {
        content: '';
        position: absolute;
        top: -6px;
        right: 12px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-bottom: 6px solid rgba(0, 0, 0, 0.175);
        z-index: 1000;
    }

    /* Menu desktop - dropup pour autres lignes (vers le haut) */
    .dropup.d-none.d-md-block .dropdown-menu {
        margin-bottom: 0.25rem;
        bottom: 100%;
        top: auto;
        z-index: 1050 !important;
    }

    /* Flèche pour dropup desktop (menu vers le haut) */
    .dropup.d-none.d-md-block .dropdown-menu::before {
        content: '';
        position: absolute;
        bottom: -5px;
        right: 12px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #fff;
        z-index: 1001;
    }

    .dropup.d-none.d-md-block .dropdown-menu::after {
        content: '';
        position: absolute;
        bottom: -6px;
        right: 12px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid rgba(0, 0, 0, 0.175);
        z-index: 1000;
    }

    /* Styles pour mobile */
    @media (max-width: 768px) {
        /* Menu avec z-index élevé pour s'afficher au-dessus */
        .dropdown-menu,
        .dropup .dropdown-menu {
            z-index: 1050 !important;
        }

        /* Menu vers le haut pour dropup */
        .dropup .dropdown-menu {
            bottom: 100%;
            top: auto;
            margin-bottom: 0.25rem;
        }

        /* Flèche pour dropup (mobile - menu vers le haut) */
        .dropup .dropdown-menu::before {
            content: '';
            position: absolute;
            bottom: -5px;
            right: 12px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #fff;
            z-index: 1001;
        }

        .dropup .dropdown-menu::after {
            content: '';
            position: absolute;
            bottom: -6px;
            right: 12px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 6px solid rgba(0, 0, 0, 0.175);
            z-index: 1000;
        }

        /* Menu vers le bas pour dropdown (premier élément) */
        .dropdown.d-md-none .dropdown-menu {
            top: 100%;
            bottom: auto;
            margin-top: 0.25rem;
        }

        /* Flèche pour dropdown mobile (premier élément) */
        .dropdown.d-md-none .dropdown-menu::before {
            content: '';
            position: absolute;
            top: -5px;
            right: 12px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 5px solid #fff;
            z-index: 1001;
        }

        .dropdown.d-md-none .dropdown-menu::after {
            content: '';
            position: absolute;
            top: -6px;
            right: 12px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 6px solid rgba(0, 0, 0, 0.175);
            z-index: 1000;
        }

        /* Réduire la taille des textes et icônes sur mobile */
        .dropdown.d-md-none .dropdown-item,
        .dropup .dropdown-item {
            font-size: 0.8rem !important;
            padding: 0.4rem 0.75rem !important;
        }

        .dropdown.d-md-none .dropdown-item i,
        .dropup .dropdown-item i {
            font-size: 0.75rem !important;
        }

        .dropdown.d-md-none .dropdown-divider,
        .dropup .dropdown-divider {
            margin: 0.3rem 0 !important;
        }
    }

    /* Styles pour les boutons "Effacer tous les filtres" dans les alertes */
    @media (max-width: 991.98px) {
        .alert-info .btn-sm.btn-outline-danger,
        .alert-info .btn-sm.btn-outline-primary {
            font-size: 0.7rem;
            padding: 0.4rem 0.65rem;
            white-space: nowrap;
            border-width: 1.5px;
            border-style: solid;
            font-weight: 500;
        }

        .alert-info .btn-sm.btn-outline-danger {
            border-color: #dc3545;
            color: #dc3545;
        }

        .alert-info .btn-sm.btn-outline-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #fff;
        }

        .alert-info .btn-sm.btn-outline-primary {
            border-color: #0d6efd;
            color: #0d6efd;
        }

        .alert-info .btn-sm.btn-outline-primary:hover {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        .alert-info .btn-sm.btn-outline-danger i,
        .alert-info .btn-sm.btn-outline-primary i {
            font-size: 0.65rem;
            margin-right: 0.3rem;
        }
    }

    @media (max-width: 767px) {
        .alert-info .btn-sm.btn-outline-danger,
        .alert-info .btn-sm.btn-outline-primary {
            font-size: 0.65rem;
            padding: 0.35rem 0.55rem;
            border-width: 1.5px;
        }

        .alert-info .btn-sm.btn-outline-danger i,
        .alert-info .btn-sm.btn-outline-primary i {
            font-size: 0.6rem;
            margin-right: 0.25rem;
        }

        .alert-info.d-flex.justify-content-between {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .alert-info.d-flex.justify-content-between > div {
            flex: 1 1 100%;
            min-width: 0;
        }

        .alert-info.d-flex.justify-content-between .btn {
            flex: 0 0 auto;
            align-self: flex-start;
            width: 100%;
            text-align: center;
        }
    }

    @media (max-width: 575px) {
        .alert-info .btn-sm.btn-outline-danger,
        .alert-info .btn-sm.btn-outline-primary {
            font-size: 0.6rem;
            padding: 0.3rem 0.5rem;
            border-width: 1.5px;
        }

        .alert-info .btn-sm.btn-outline-danger i,
        .alert-info .btn-sm.btn-outline-primary i {
            font-size: 0.55rem;
            margin-right: 0.2rem;
        }

        .alert-info.d-flex.justify-content-between {
            gap: 0.4rem;
        }
    }
</style>
@endpush
