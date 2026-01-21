@extends('layouts.app')

@php
    $provider = auth()->user();
    $currentRouteName = optional(request()->route())->getName();

    $navItems = [
        [
            'label' => 'Vue d\'ensemble',
            'icon' => 'fas fa-gauge-high',
            'route' => 'provider.dashboard',
            'url' => url('/provider/dashboard'),
            'active' => ['provider.dashboard'],
        ],
        [
            'label' => 'Contenus',
            'icon' => 'fas fa-chalkboard-teacher',
            'route' => 'provider.contents.index',
            'url' => url('/provider/contents'),
            'active' => ['provider.contents.*', 'contents.*'],
        ],
        [
            'label' => 'Clients',
            'icon' => 'fas fa-users',
            'route' => 'provider.customers',
            'url' => url('/provider/customers'),
            'active' => ['provider.customers'],
        ],
        [
            'label' => 'Analytics',
            'icon' => 'fas fa-chart-line',
            'route' => 'provider.analytics',
            'url' => url('/provider/analytics'),
            'active' => ['provider.analytics'],
        ],
        [
            'label' => 'Configuration de paiement',
            'label_mobile' => 'Configuration',
            'icon' => 'fas fa-money-bill-wave',
            'route' => 'provider.payment-settings',
            'url' => url('/provider/payment-settings'),
            'active' => ['provider.payment-settings'],
        ],
        [
            'label' => 'Notifications',
            'icon' => 'fas fa-bell',
            'route' => 'provider.notifications',
            'url' => url('/provider/notifications'),
            'active' => ['provider.notifications'],
        ],
    ];

    if (\Illuminate\Support\Facades\Route::has('profile.edit')) {
        $navItems[] = [
            'label' => 'Profil',
            'icon' => 'fas fa-user-circle',
            'route' => 'profile.edit',
            'url' => route('profile.edit'),
            'active' => ['profile.edit'],
        ];
    }

    $pageTitle = trim($__env->yieldContent('admin-title')) ?: 'Espace prestataire';
    $pageSubtitle = trim($__env->yieldContent('admin-subtitle'));
    $pageActions = trim($__env->yieldContent('admin-actions'));
@endphp

@section('content')
<div class="instructor-admin-shell">
    <aside class="admin-sidebar-wrapper">
        <div class="admin-sidebar">
            <div class="admin-sidebar__brand">
                <div class="admin-sidebar__avatar">
                    <img src="{{ $provider?->avatar_url ?? asset('images/default-avatar.png') }}" alt="{{ $provider?->name }}">
                </div>
                <div class="admin-sidebar__meta">
                    <span class="admin-sidebar__role">Prestataire</span>
                    <strong class="admin-sidebar__name">{{ $provider?->name }}</strong>
                    @if($provider?->email)
                        <small class="admin-sidebar__email">{{ $provider->email }}</small>
                    @endif
                </div>
            </div>
            <nav class="admin-sidebar__nav">
                @foreach($navItems as $item)
                    @php
                        $isActive = $currentRouteName === $item['route'] || request()->routeIs($item['route']);
                        if (!empty($item['active'])) {
                            foreach ((array) $item['active'] as $pattern) {
                                if (request()->routeIs($pattern)) {
                                    $isActive = true;
                                    break;
                                }
                            }
                        }
                    @endphp
                    <a href="{{ $item['url'] }}" class="admin-sidebar__link {{ $isActive ? 'is-active' : '' }}" 
                       @if(isset($item['label_mobile'])) data-label-desktop="{{ $item['label'] }}" data-label-mobile="{{ $item['label_mobile'] }}" @endif>
                        <i class="{{ $item['icon'] }}"></i>
                        <span class="admin-sidebar__link-text">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <div>
                <h1 class="admin-header__title">{{ html_entity_decode($pageTitle, ENT_QUOTES, 'UTF-8') }}</h1>
                @if($pageSubtitle !== '')
                    <p class="admin-header__subtitle">{{ html_entity_decode($pageSubtitle, ENT_QUOTES, 'UTF-8') }}</p>
                @endif
            </div>
            @if($pageActions !== '')
                <div class="admin-header__actions">
                    {!! $pageActions !!}
                </div>
            @endif
        </header>

        <section class="admin-content">
            @if(session('success') || session('status'))
                <div class="admin-alert success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Succès</strong>
                        <p>{{ session('success') ?? session('status') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="admin-alert error">
                    <i class="fas fa-triangle-exclamation"></i>
                    <div>
                        <strong>Erreur</strong>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @yield('admin-content')
        </section>
    </main>
</div>
@endsection

@push('styles')
<style>
    :root {
        --instructor-primary: #003366;
        --instructor-primary-dark: #002244;
        --instructor-secondary: #38bdf8;
        --instructor-accent: #22c55e;
        --instructor-bg: #f5f7fb;
        --instructor-card-bg: #ffffff;
        --instructor-muted: #64748b;
    }

    .instructor-admin-shell {
        display: grid;
        grid-template-columns: 280px 1fr;
        column-gap: 2.75rem;
        background: var(--instructor-bg);
        margin-top: 0;
        min-height: 100vh;
        padding: 0.5rem 3.1rem 3.2rem 0;
    }

    .admin-sidebar-wrapper {
        position: relative;
        width: 280px;
        min-height: calc(100vh - var(--site-navbar-height, 64px));
        flex-shrink: 0;
    }

    .admin-sidebar {
        background: var(--instructor-primary);
        color: #ffffff;
        padding: 2.2rem 1.75rem 2rem;
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
        position: fixed;
        top: var(--site-navbar-height, 64px);
        left: 0;
        width: 280px;
        height: calc(100vh - var(--site-navbar-height, 64px));
        border-radius: 0;
        box-shadow: 0 24px 55px -40px rgba(30, 58, 138, 0.45);
    }

    .admin-sidebar__brand {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: nowrap;
    }

    .admin-sidebar__avatar {
        width: 64px;
        height: 64px;
        min-width: 64px;
        min-height: 64px;
        max-width: 64px;
        max-height: 64px;
        border-radius: 16px;
        overflow: hidden;
        border: 3px solid rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.1);
        flex-shrink: 0;
    }

    .admin-sidebar__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .admin-sidebar__meta {
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }

    .admin-sidebar__role {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        opacity: 0.65;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .admin-sidebar__name {
        font-size: 1.1rem;
        font-weight: 700;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        word-break: break-word;
    }

    .admin-sidebar__email {
        font-size: 0.8rem;
        opacity: 0.75;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        word-break: break-all;
    }

    .admin-sidebar__nav {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .admin-sidebar__link {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.85rem 1rem;
        border-radius: 0.85rem;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        font-weight: 600;
        transition: background 0.2s ease, transform 0.2s ease, color 0.2s ease;
    }
    

    .admin-sidebar__link i {
        width: 20px;
        text-align: center;
    }

    .admin-sidebar__link:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.12);
        transform: translateX(4px);
    }

    .admin-sidebar__link.is-active {
        background: #ffffff;
        color: var(--instructor-primary);
        box-shadow: 0 18px 35px -25px rgba(15, 23, 42, 0.45);
    }

    .admin-main {
        padding: 1.7rem 2.85rem 3rem;
        display: flex;
        flex-direction: column;
        gap: 2rem;
        min-width: 0;
        overflow-x: hidden;
    }

    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .admin-header__title {
        margin: 0;
        font-size: clamp(1.8rem, 1.5rem + 1vw, 2.4rem);
        font-weight: 700;
        color: var(--instructor-primary);
    }

    .admin-header__subtitle {
        margin: 0.5rem 0 0;
        color: var(--instructor-muted);
        max-width: 640px;
        font-size: 0.98rem;
    }

    .admin-header__actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .admin-content {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        min-width: 0;
        overflow-x: hidden;
    }

    .admin-alert {
        display: flex;
        gap: 1rem;
        align-items: center;
        padding: 1rem 1.25rem;
        border-radius: 1rem;
        border: 1px solid transparent;
        font-size: 0.95rem;
        box-shadow: 0 12px 24px -20px rgba(15, 23, 42, 0.35);
    }

    .admin-alert i {
        font-size: 1.4rem;
    }

    .admin-alert.success {
        background: rgba(34, 197, 94, 0.12);
        border-color: rgba(34, 197, 94, 0.25);
        color: #15803d;
    }

    .admin-alert.error {
        background: rgba(239, 68, 68, 0.12);
        border-color: rgba(239, 68, 68, 0.25);
        color: #b91c1c;
    }

    .admin-card {
        background: var(--instructor-card-bg);
        border-radius: 1.25rem;
        padding: 1.75rem;
        box-shadow: 0 22px 45px -35px rgba(15, 23, 42, 0.25);
        border: 1px solid rgba(226, 232, 240, 0.7);
    }

    .admin-card__title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        color: #0f172a;
    }

    .admin-card__subtitle {
        margin: 0;
        color: var(--instructor-muted);
        font-size: 0.9rem;
    }

    .admin-card__actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }

    .admin-table thead th {
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.08em;
        color: var(--instructor-muted);
        font-weight: 700;
        padding-bottom: 0.75rem;
    }

    .admin-table tbody tr {
        border-top: 1px solid rgba(226, 232, 240, 0.7);
    }

    .admin-table tbody tr:first-child {
        border-top: none;
    }

    .admin-table tbody td {
        padding: 0.95rem 0 0.95rem 0;
        vertical-align: middle;
        font-size: 0.95rem;
        color: #0f172a;
    }

    .admin-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: rgba(30, 58, 138, 0.1);
        color: var(--instructor-primary);
    }

    .admin-badge.success {
        background: rgba(34, 197, 94, 0.15);
        color: #15803d;
    }

    .admin-badge.warning {
        background: rgba(234, 179, 8, 0.18);
        color: #b45309;
    }

    .admin-badge.info {
        background: rgba(14, 165, 233, 0.15);
        color: #0f172a;
    }

    .admin-empty-state {
        padding: 3rem 1.5rem;
        text-align: center;
        color: var(--instructor-muted);
        border: 1px dashed rgba(148, 163, 184, 0.35);
        border-radius: 1.25rem;
        background: rgba(255, 255, 255, 0.65);
    }

    .admin-empty-state i {
        font-size: 2.2rem;
        margin-bottom: 1rem;
        color: rgba(30, 58, 138, 0.35);
    }

    .admin-table__empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--instructor-muted);
    }

    .admin-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        background: rgba(15, 23, 42, 0.05);
        color: #0b1f3a;
    }

    .admin-chip--success {
        background: rgba(22, 163, 74, 0.12);
        color: #15803d;
    }

    .admin-chip--warning {
        background: rgba(234, 179, 8, 0.12);
        color: #b45309;
    }

    .admin-chip--danger {
        background: rgba(239, 68, 68, 0.12);
        color: #b91c1c;
    }

    .admin-chip--info {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }

    .admin-chip--neutral {
        background: rgba(15, 23, 42, 0.08);
        color: #0f172a;
    }

    .admin-chip--secondary {
        background: rgba(148, 163, 184, 0.15);
        color: #475569;
    }

    /* Button styles */
    .admin-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        padding: 0.65rem 1.2rem;
        border: 1px solid transparent;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.2s ease;
        color: inherit;
    }

    .admin-btn.primary {
        background: linear-gradient(90deg, var(--instructor-primary), #004080);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(0, 51, 102, 0.55);
    }

    .admin-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(0, 51, 102, 0.45);
    }

    .admin-btn.outline {
        border-color: rgba(0, 51, 102, 0.32);
        color: var(--instructor-primary);
        background: rgba(0, 51, 102, 0.08);
    }

    .admin-btn.soft {
        border-color: rgba(148, 163, 184, 0.4);
        background: rgba(148, 163, 184, 0.12);
        color: var(--instructor-primary-dark);
        padding: 0.55rem 1rem;
        font-size: 0.85rem;
    }

    .admin-btn.ghost {
        border-color: rgba(0, 51, 102, 0.3);
        color: #ffffff;
        background: var(--instructor-primary);
    }

    .admin-btn.ghost:hover {
        background: rgba(0, 51, 102, 0.9);
        border-color: var(--instructor-primary);
    }

    .admin-btn.sm {
        padding: 0.5rem 0.9rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
    }

    .admin-btn.lg {
        padding: 0.82rem 1.65rem;
        font-size: 1.02rem;
        border-radius: 1rem;
    }

    /* Panel styles */
    .admin-panel {
        margin-bottom: 1.5rem;
        background: var(--instructor-card-bg);
        border-radius: 1.25rem;
        box-shadow: 0 22px 45px -35px rgba(15, 23, 42, 0.25);
        border: 1px solid rgba(226, 232, 240, 0.7);
    }

    .admin-panel__header {
        padding: 1.25rem 1.75rem;
        background: linear-gradient(135deg, var(--instructor-primary) 0%, #004080 100%);
        color: #ffffff;
        border-radius: 1.25rem 1.25rem 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .admin-panel__header h3 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .admin-panel__header i {
        font-size: 1.1rem;
    }

    .admin-panel__actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .admin-panel__actions .admin-btn.soft {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .admin-panel__actions .admin-btn.soft:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.5);
    }
    
    .admin-panel__body {
        padding: 1.75rem;
    }

    .admin-panel--main {
        margin-bottom: 2rem;
    }

    /* Stats grid */
    .admin-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .admin-stat-card {
        background: var(--instructor-card-bg);
        border-radius: 1.25rem;
        padding: 1.5rem;
        box-shadow: 0 22px 45px -35px rgba(15, 23, 42, 0.25);
        border: 1px solid rgba(226, 232, 240, 0.7);
        display: flex;
        flex-direction: column;
        min-height: 100%;
        overflow: hidden;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .admin-stat-card__label {
        font-size: 0.85rem;
        color: var(--instructor-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
        order: 1;
    }

    .admin-stat-card__value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--instructor-primary);
        margin-bottom: 0.25rem;
        line-height: 1.2;
        order: 2;
    }

    .admin-stat-card__muted {
        font-size: 0.8rem;
        color: var(--instructor-muted);
        margin-top: auto;
        order: 3;
        line-height: 1.4;
    }

    /* Form grid styles */
    .admin-form-grid {
        display: grid;
        gap: 1.25rem;
    }

    .admin-form-grid--two {
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    }

    /* Pagination styles */
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
        color: var(--instructor-muted);
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

    .provider-pagination {
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
    
    .provider-pagination__info {
        font-size: 0.875rem;
        white-space: nowrap;
        color: var(--instructor-muted);
    }
    
    @media (min-width: 768px) {
        .provider-pagination__info {
            padding-left: 1rem;
        }
    }
    
    .provider-pagination__links {
        display: flex;
        justify-content: flex-end;
        flex: 1;
    }

    /* Bootstrap 5 pagination styles */
    .pagination {
        margin-bottom: 0;
    }

    .page-link {
        color: var(--instructor-primary);
        border-color: rgba(30, 58, 138, 0.2);
        padding: 0.5rem 0.75rem;
        transition: all 0.2s ease;
    }

    .page-link:hover {
        color: var(--instructor-secondary);
        background-color: rgba(30, 58, 138, 0.05);
        border-color: rgba(30, 58, 138, 0.3);
    }

    .page-item.active .page-link {
        background-color: var(--instructor-primary);
        border-color: var(--instructor-primary);
        color: #ffffff;
    }

    .page-item.disabled .page-link {
        color: var(--instructor-muted);
        background-color: #f8f9fa;
        border-color: rgba(30, 58, 138, 0.1);
        cursor: not-allowed;
    }

    @media (max-width: 1024px) {
        .instructor-admin-shell {
            grid-template-columns: 1fr;
            padding: 0 1.25rem 2rem;
            padding-top: calc(var(--site-navbar-height, 64px) + 2.6rem);
            margin-top: 0;
        }

        .admin-sidebar-wrapper {
            position: static;
            width: auto;
            height: auto;
            min-height: 0;
        }

        .admin-sidebar {
            position: fixed;
            top: var(--site-navbar-height, 64px);
            left: 0;
            right: 0;
            height: 3.5rem;
            padding: 0 1rem;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            border-radius: 0;
            box-shadow: 0 12px 25px -20px rgba(30, 64, 175, 0.45);
            z-index: 1020;
            margin-top: 0;
            width: auto;
        }

        .admin-sidebar__brand {
            display: none;
        }

        .admin-sidebar__nav {
            flex-direction: row;
            gap: 0.2rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.35rem;
            border-radius: 0;
            justify-content: center;
        }

        .admin-sidebar__link {
            padding: 0.4rem 0.6rem;
            gap: 0.4rem;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        .admin-sidebar__link i {
            font-size: 0.85rem;
        }

        .admin-main {
            padding: 0.75rem 0.85rem 1.6rem;
        }

        .admin-stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
    }

    @media (max-width: 640px) {
        .admin-sidebar {
            height: auto;
            flex-direction: column;
            align-items: stretch;
            gap: 0.6rem;
            padding: 0.75rem 1rem 1rem;
            margin-top: 0;
            border-radius: 0;
            width: auto;
        }

        .admin-sidebar__nav {
            width: 100%;
            display: flex;
            gap: 0.25rem;
            background: transparent;
            padding: 0.3rem 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            justify-content: center;
        }

        .admin-sidebar__link {
            border-radius: 10px;
            justify-content: center;
            font-size: 0.72rem;
            padding: 0.45rem 0.5rem;
            flex: 0 0 auto;
        }

        .admin-sidebar__link i {
            display: none;
        }

        .admin-header {
            flex-direction: column;
            align-items: stretch;
        }

        .admin-main {
            padding: 0.18rem 0.6rem 1.1rem;
        }

        .admin-panel {
            margin-bottom: 0.75rem;
        }

        .admin-panel--main .admin-panel__body {
            padding: 0.75rem 0.25rem !important;
        }

        .admin-panel__body {
            padding: 0.5rem 0.25rem !important;
        }

        .admin-panel__header {
            padding: 0.5rem 0.75rem;
        }

        .admin-panel__header h3 {
            font-size: 0.95rem;
        }

        .admin-card {
            margin-bottom: 0.75rem;
        }

        .admin-card__header {
            padding: 0.5rem 0.75rem;
        }

        .admin-card__header .admin-card__title {
            font-size: 0.95rem;
        }

        .admin-card__body {
            padding: 0.5rem;
        }

        .provider-pagination {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            margin-top: 1rem;
        }

        .provider-pagination__info {
            text-align: center;
            padding-left: 0;
            font-size: 0.8rem;
        }

        .provider-pagination__links {
            justify-content: center;
        }

        .pagination {
            flex-wrap: wrap;
            justify-content: center;
        }

        .page-link {
            padding: 0.4rem 0.6rem;
            font-size: 0.875rem;
        }

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

        .admin-stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.375rem !important;
        }

        .admin-stat-card {
            padding: 0.5rem 0.625rem !important;
        }

        .admin-stat-card__label {
            font-size: 0.75rem;
        }

        .admin-stat-card__value {
            font-size: 1.5rem;
        }

        .admin-stat-card__muted {
            font-size: 0.7rem;
        }

        .admin-empty-state {
            padding: 1.5rem 0.75rem;
        }

        .admin-empty-state i {
            font-size: 1.5rem;
        }

        .admin-empty-state p {
            font-size: 0.85rem;
        }

        .admin-btn {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }

        .admin-btn.sm {
            padding: 0.4rem 0.7rem;
            font-size: 0.75rem;
        }

        .admin-btn.lg {
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
        }

        .admin-panel__actions .admin-btn {
            width: auto;
            font-size: 0.75rem;
            padding: 0.4rem 0.7rem;
        }

        .admin-header .admin-btn {
            width: 100%;
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
        }

        .admin-header .admin-btn i {
            font-size: 0.75rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateMenuLabels() {
        const links = document.querySelectorAll('.admin-sidebar__link[data-label-mobile]');
        const isMobile = window.innerWidth <= 1024;
        
        links.forEach(link => {
            const textSpan = link.querySelector('.admin-sidebar__link-text');
            if (textSpan) {
                if (isMobile) {
                    textSpan.textContent = link.getAttribute('data-label-mobile');
                } else {
                    textSpan.textContent = link.getAttribute('data-label-desktop');
                }
            }
        });
    }
    
    // Mettre à jour au chargement
    updateMenuLabels();
    
    // Mettre à jour lors du redimensionnement
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(updateMenuLabels, 100);
    });
});
</script>
@endpush
