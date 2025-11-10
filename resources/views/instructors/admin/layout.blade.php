@extends('layouts.app')

@php
    $instructor = auth()->user();
    $currentRouteName = optional(request()->route())->getName();
    $navItems = [
        [
            'label' => 'Vue d’ensemble',
            'icon' => 'fas fa-gauge-high',
            'route' => 'instructor.dashboard',
            'url' => url('/instructor/dashboard'),
            'active' => ['instructor.dashboard'],
        ],
        [
            'label' => 'Cours',
            'icon' => 'fas fa-chalkboard-teacher',
            'route' => 'instructor.courses.index',
            'url' => url('/instructor/courses'),
            'active' => ['instructor.courses.*', 'courses.*'],
        ],
        [
            'label' => 'Étudiants',
            'icon' => 'fas fa-users',
            'route' => 'instructor.students',
            'url' => url('/instructor/students'),
            'active' => ['instructor.students'],
        ],
        [
            'label' => 'Analytics',
            'icon' => 'fas fa-chart-line',
            'route' => 'instructor.analytics',
            'url' => url('/instructor/analytics'),
            'active' => ['instructor.analytics'],
        ],
        [
            'label' => 'Notifications',
            'icon' => 'fas fa-bell',
            'route' => 'instructor.notifications',
            'url' => url('/instructor/notifications'),
            'active' => ['instructor.notifications'],
        ],
    ];

    $pageTitle = trim($__env->yieldContent('admin-title')) ?: 'Espace formateur';
    $pageSubtitle = trim($__env->yieldContent('admin-subtitle'));
    $pageActions = trim($__env->yieldContent('admin-actions'));
@endphp

@section('content')
<div class="instructor-admin-shell">
    <aside class="admin-sidebar-wrapper">
        <div class="admin-sidebar">
        <div class="admin-sidebar__brand">
            <div class="admin-sidebar__avatar">
                <img src="{{ $instructor?->avatar_url ?? asset('images/default-avatar.png') }}" alt="{{ $instructor?->name }}">
            </div>
            <div class="admin-sidebar__meta">
                <span class="admin-sidebar__role">Formateur</span>
                <strong class="admin-sidebar__name">{{ $instructor?->name }}</strong>
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
                <a href="{{ $item['url'] }}" class="admin-sidebar__link {{ $isActive ? 'is-active' : '' }}">
                    <i class="{{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
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
            @yield('admin-content')
        </section>
    </main>
</div>
@endsection

@push('styles')
<style>
    :root {
        --instructor-primary: #003366;
        --instructor-primary-dark: #022447;
        --instructor-secondary: #0ea5e9;
        --instructor-bg: #f4f7fb;
        --instructor-card-bg: #ffffff;
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
        background: linear-gradient(180deg, var(--instructor-primary) 0%, var(--instructor-primary-dark) 100%);
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
        border-radius: 0 24px 24px 0;
        box-shadow: 0 24px 55px -40px rgba(0, 51, 102, 0.45);
    }

    .admin-sidebar__brand {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .admin-sidebar__avatar {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        overflow: hidden;
        border: 3px solid rgba(255, 255, 255, 0.35);
    }

    .admin-sidebar__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .admin-sidebar__role {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        opacity: 0.75;
    }

    .admin-sidebar__name {
        font-size: 1.1rem;
        font-weight: 700;
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
        color: #64748b;
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
        color: #64748b;
        font-size: 0.9rem;
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
            border-radius: 0 0 18px 18px;
            box-shadow: 0 12px 25px -20px rgba(14, 165, 233, 0.45);
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
            border-radius: 999px;
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
            padding: 0.15rem 1.2rem 1.4rem;
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
            border-radius: 18px;
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
            padding: 0.18rem 0.9rem 1.1rem;
        }
    }
</style>
@endpush
