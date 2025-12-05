@extends('layouts.app')

@php
    $ambassador = \App\Models\Ambassador::where('user_id', auth()->id())
        ->where('is_active', true)
        ->first();
    $user = auth()->user();
    $currentRouteName = optional(request()->route())->getName();

    $navItems = [
        [
            'label' => 'Vue d\'ensemble',
            'icon' => 'fas fa-gauge-high',
            'route' => 'ambassador.dashboard',
            'url' => route('ambassador.dashboard'),
            'active' => ['ambassador.dashboard'],
        ],
        [
            'label' => 'Analytics',
            'icon' => 'fas fa-chart-line',
            'route' => 'ambassador.analytics',
            'url' => route('ambassador.analytics'),
            'active' => ['ambassador.analytics'],
        ],
        [
            'label' => 'Configuration de paiement',
            'label_mobile' => 'Configuration',
            'icon' => 'fas fa-money-bill-wave',
            'route' => 'ambassador.payment-settings',
            'url' => route('ambassador.payment-settings'),
            'active' => ['ambassador.payment-settings'],
        ],
    ];

    $pageTitle = trim($__env->yieldContent('admin-title')) ?: 'Espace ambassadeur';
    $pageSubtitle = trim($__env->yieldContent('admin-subtitle'));
    $pageActions = trim($__env->yieldContent('admin-actions'));
@endphp

@section('content')
<div class="instructor-admin-shell">
    <aside class="admin-sidebar-wrapper">
        <div class="admin-sidebar">
            <div class="admin-sidebar__brand">
                <div class="admin-sidebar__avatar">
                    <img src="{{ $user?->avatar_url ?? asset('images/default-avatar.png') }}" alt="{{ $user?->name }}">
                </div>
                <div class="admin-sidebar__meta">
                    <span class="admin-sidebar__role">Ambassadeur</span>
                    <strong class="admin-sidebar__name">{{ $user?->name }}</strong>
                    @if($user?->email)
                        <small class="admin-sidebar__email">{{ $user->email }}</small>
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

    .admin-panel__body {
        padding: 1.75rem;
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

        .admin-main {
            padding: 0.75rem 0.85rem 1.6rem;
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

        .admin-panel__body {
            padding: 0.5rem 0.25rem !important;
        }

        .admin-panel__header {
            padding: 0.5rem 0.75rem;
        }

        .admin-panel__header h3 {
            font-size: 0.95rem;
        }

        .admin-btn {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }
    }
</style>
@endpush

