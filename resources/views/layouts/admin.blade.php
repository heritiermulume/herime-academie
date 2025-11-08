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
<div class="admin-shell container-fluid py-4 px-0 px-lg-4">
    <div class="admin-shell__container">
        <aside class="admin-shell__sidebar d-none d-md-flex flex-column">
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
            @if($pageTitle !== '' || $pageActions !== '')
                <header class="admin-content__header mb-4">
                    <div>
                        <h1 class="admin-content__title">{{ $pageTitle }}</h1>
                        @if($pageSubtitle !== '')
                            <p class="admin-content__subtitle">{{ $pageSubtitle }}</p>
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

<nav class="admin-shell__mobile-nav d-md-none">
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
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1.5rem;
        padding: 1.25rem;
        margin-bottom: 1.75rem;
        box-shadow: 0 24px 60px -40px rgba(15, 23, 42, 0.35);
    }
    .admin-search-panel__bar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        justify-content: space-between;
    }
    .admin-search-panel__input {
        flex: 1 1 280px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 0.9rem;
        border: 1px solid #dbe3f0;
        border-radius: 999px;
        background: #f9fbff;
        transition: border-color 0.2s ease, background 0.2s ease;
    }
    .admin-search-panel__input:focus-within {
        border-color: #2563eb;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }
    .admin-search-panel__input i {
        color: #2563eb;
        font-size: 1rem;
    }
    .admin-search-panel__input input {
        border: none;
        background: transparent;
        outline: none;
        width: 100%;
        font-size: 0.95rem;
    }
    .admin-search-panel__actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .admin-search-panel__filters-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
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
        bottom: 0;
        left: 0;
        right: 0;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
        box-shadow: 0 -10px 30px -25px rgba(0, 0, 0, 0.35);
        display: flex;
        justify-content: space-between;
        padding: 0.5rem env(safe-area-inset-right, 1.25rem) 0.75rem env(safe-area-inset-left, 1.25rem);
        z-index: 50;
    }
    .admin-shell__mobile-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        flex: 1;
        color: #64748b;
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.5rem;
        border-radius: 0.75rem;
    }
    .admin-shell__mobile-link i {
        font-size: 1.05rem;
    }
    .admin-shell__mobile-link.is-active {
        color: #003366;
        background: rgba(0, 51, 102, 0.08);
    }
    .admin-shell__mobile-link:focus-visible {
        outline: 2px solid #fbbf24;
        outline-offset: 4px;
    }
    @media (max-width: 991px) {
        .admin-shell__container {
            flex-direction: column;
        }
        .admin-shell__sidebar {
            display: none;
        }
        .admin-shell__content {
            width: 100%;
        }
    }
    @media (max-width: 767px) {
        .admin-shell {
            padding-bottom: 5rem;
        }
        .admin-search-panel__bar {
            flex-direction: row;
            align-items: center;
            gap: 0.5rem;
        }
        .admin-search-panel__actions {
            width: auto;
            margin-top: 0;
            flex-wrap: nowrap;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .admin-search-panel__actions .btn {
            flex: none;
            height: 42px;
        }
        .admin-search-panel__input {
            width: auto;
        }
        .admin-content__header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        .admin-content__actions {
            display: flex;
            width: 100%;
            gap: 0.75rem;
        }
        .admin-content__actions .btn {
            flex: 1;
        }
    }
    @media (max-width: 575px) {
        .quick-actions-grid {
            grid-template-columns: 1fr;
        }
        .admin-search-panel__input {
            padding: 0.55rem 0.75rem;
            width: 100%;
        }
        .admin-search-panel__bar {
            flex-direction: column;
            align-items: stretch;
        }
        .admin-search-panel__actions {
            width: 100%;
            margin-top: 0.5rem;
            justify-content: stretch;
            gap: 0.5rem;
        }
        .admin-search-panel__actions .btn {
            flex: 1;
            height: 44px;
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
        justify-content: flex-end;
        margin-top: 1.5rem;
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
</style>
@endpush
