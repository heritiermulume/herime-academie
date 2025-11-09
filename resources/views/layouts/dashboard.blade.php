@php
    use Illuminate\Support\Facades\Route;

    $dashboardNavItems = $dashboardNavItems ?? [];
    if (!is_array($dashboardNavItems)) {
        $dashboardNavItems = [];
    }
    $dashboardBrand = $dashboardBrand ?? 'Espace';
    $dashboardReturnRoute = $dashboardReturnRoute ?? route('home');

    $pageTitle = trim($__env->yieldContent('dashboard-title'));
    if ($pageTitle === '') {
        $pageTitle = trim($__env->yieldContent('title')) ?: $dashboardBrand;
    }
    $pageSubtitle = trim($__env->yieldContent('dashboard-subtitle'));
    $pageActions = trim($__env->yieldContent('dashboard-actions'));
@endphp

@extends('layouts.app')

@section('content')
<div class="admin-shell container-fluid py-4 px-0 px-lg-4">
    <div class="admin-shell__container">
        <aside class="admin-shell__sidebar d-none d-md-flex flex-column">
            <div class="admin-shell__brand">
                <i class="fas fa-user-circle me-2"></i>{{ $dashboardBrand }}
            </div>
            <nav class="admin-nav flex-grow-1">
                @foreach($dashboardNavItems as $item)
                    @php
                        $hasRoute = isset($item['route']) && is_string($item['route']);
                        $isAvailable = $item['available'] ?? true;
                        if (!$isAvailable) {
                            continue;
                        }

                        $url = '#';
                        $activeRoutes = [];
                        if ($hasRoute) {
                            $url = Route::has($item['route']) ? route($item['route']) : url($item['route']);
                            $activeRoutes = $item['active'] ?? [$item['route']];
                        } elseif (!empty($item['url'])) {
                            $url = $item['url'];
                        }

                        $isActive = false;
                        if ($hasRoute && !empty($activeRoutes)) {
                            $isActive = collect($activeRoutes)->contains(fn($routeName) => request()->routeIs($routeName));
                        } elseif (!empty($item['activePattern'])) {
                            $isActive = request()->is($item['activePattern']);
                        }
                    @endphp
                    <a href="{{ $url }}" class="admin-nav__item {{ $isActive ? 'is-active' : '' }}">
                        <span class="admin-nav__icon"><i class="{{ $item['icon'] ?? 'fas fa-circle' }}"></i></span>
                        <span class="admin-nav__label">{{ $item['label'] ?? 'Lien' }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="admin-shell__footer mt-auto">
                <a href="{{ $dashboardReturnRoute }}" class="admin-nav__item">
                    <span class="admin-nav__icon"><i class="fas fa-arrow-left"></i></span>
                    <span class="admin-nav__label">Retour</span>
                </a>
            </div>
        </aside>

        <main class="admin-shell__content">
            <nav class="admin-shell__mobile-nav d-md-none" aria-label="Navigation du tableau de bord">
                @foreach($dashboardNavItems as $item)
                    @php
                        $hasRoute = isset($item['route']) && is_string($item['route']);
                        $isAvailable = $item['available'] ?? true;
                        if (!$isAvailable) {
                            continue;
                        }

                        $url = '#';
                        $activeRoutes = [];
                        if ($hasRoute) {
                            $url = Route::has($item['route']) ? route($item['route']) : url($item['route']);
                            $activeRoutes = $item['active'] ?? [$item['route']];
                        } elseif (!empty($item['url'])) {
                            $url = $item['url'];
                        }

                        $isActive = false;
                        if ($hasRoute && !empty($activeRoutes)) {
                            $isActive = collect($activeRoutes)->contains(fn($routeName) => request()->routeIs($routeName));
                        } elseif (!empty($item['activePattern'])) {
                            $isActive = request()->is($item['activePattern']);
                        }
                    @endphp
                    <a href="{{ $url }}" class="admin-shell__mobile-link {{ $isActive ? 'is-active' : '' }}" aria-label="{{ $item['label'] ?? 'Lien' }}">
                        <i class="{{ $item['icon'] ?? 'fas fa-circle' }}"></i>
                        <span>{{ $item['label'] ?? 'Lien' }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="admin-shell__mobile-nav-spacer d-md-none" aria-hidden="true"></div>
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

            @if (trim($__env->yieldContent('dashboard-content')) !== '')
                @yield('dashboard-content')
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
        outline-offset: 3px;
    }
    .admin-nav__icon {
        width: 34px;
        height: 34px;
        border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.08);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .admin-shell__content {
        flex: 1;
        max-width: 100%;
    }
    .admin-content__header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 1rem;
        align-items: center;
    }
    .admin-content__title {
        font-size: clamp(1.5rem, 0.5rem + 2vw, 2rem);
        font-weight: 700;
        color: #0b1f3a;
        margin-bottom: 0.25rem;
    }
    .admin-content__subtitle {
        margin: 0;
        color: #6c7a91;
        max-width: 640px;
    }
    .admin-content__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .admin-shell__mobile-nav {
        position: fixed;
        top: calc(var(--site-navbar-height, 64px) + 1.25rem);
        left: 50%;
        transform: translateX(-50%);
        z-index: 1100;
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
        .admin-shell__container {
            flex-direction: column;
        }
        .admin-shell__sidebar {
            display: none;
        }
        .admin-shell__content {
            width: 100%;
            padding-top: calc(var(--site-navbar-height, 64px) + 4.5rem);
        }
        .admin-shell__mobile-nav-spacer {
            display: block;
            height: calc(var(--site-navbar-height, 64px) + 1.5rem);
        }
    }
</style>
@endpush
