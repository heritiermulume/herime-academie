@extends('layouts.app')

@php
    $student = auth()->user();
    $currentRouteName = optional(request()->route())->getName();

    $navItems = [
        [
            'label' => 'Vue d’ensemble',
            'icon' => 'fas fa-gauge-high',
            'route' => 'student.dashboard',
            'url' => url('/student/dashboard'),
            'active' => ['student.dashboard'],
        ],
        [
            'label' => 'Mes cours',
            'icon' => 'fas fa-book-open',
            'route' => 'student.courses',
            'url' => url('/student/courses'),
            'active' => ['student.courses', 'student.courses.*'],
        ],
        [
            'label' => 'Certificats',
            'icon' => 'fas fa-certificate',
            'route' => 'student.certificates',
            'url' => url('/student/certificates'),
            'active' => ['student.certificates'],
        ],
        [
            'label' => 'Commandes',
            'icon' => 'fas fa-receipt',
            'route' => 'orders.index',
            'url' => route('orders.index'),
            'active' => ['orders.index', 'orders.show'],
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

    $pageTitle = trim($__env->yieldContent('admin-title')) ?: 'Espace étudiant';
    $pageSubtitle = trim($__env->yieldContent('admin-subtitle'));
    $pageActions = trim($__env->yieldContent('admin-actions'));
@endphp

@section('content')
<div class="student-admin-shell">
    <aside class="admin-sidebar-wrapper">
        <div class="admin-sidebar">
            <div class="admin-sidebar__brand">
                <div class="admin-sidebar__avatar">
                    <img src="{{ $student?->avatar_url ?? asset('images/default-avatar.png') }}" alt="{{ $student?->name }}">
                </div>
                <div class="admin-sidebar__meta">
                    <span class="admin-sidebar__role">Étudiant</span>
                    <strong class="admin-sidebar__name">{{ $student?->name }}</strong>
                    @if($student?->email)
                        <small class="admin-sidebar__email">{{ $student->email }}</small>
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
        --student-primary: #003366;
        --student-primary-dark: #002244;
        --student-secondary: #38bdf8;
        --student-accent: #22c55e;
        --student-bg: #f5f7fb;
        --student-card-bg: #ffffff;
        --student-muted: #64748b;
    }

    .student-admin-shell {
        display: grid;
        grid-template-columns: 280px 1fr;
        column-gap: 2.75rem;
        background: var(--student-bg);
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
        background: var(--student-primary);
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
    }

    .admin-sidebar__avatar {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        overflow: hidden;
        border: 3px solid rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.1);
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
        opacity: 0.65;
    }

    .admin-sidebar__name {
        font-size: 1.1rem;
        font-weight: 700;
    }

    .admin-sidebar__email {
        font-size: 0.8rem;
        opacity: 0.75;
        display: block;
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
        color: var(--student-primary);
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
        color: var(--student-primary);
    }

    .admin-header__subtitle {
        margin: 0.5rem 0 0;
        color: var(--student-muted);
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
        background: var(--student-card-bg);
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
        color: var(--student-muted);
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
        color: var(--student-muted);
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
        color: var(--student-primary);
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
        color: var(--student-muted);
        border: 1px dashed rgba(148, 163, 184, 0.35);
        border-radius: 1.25rem;
        background: rgba(255, 255, 255, 0.65);
    }

    .admin-empty-state i {
        font-size: 2.2rem;
        margin-bottom: 1rem;
        color: rgba(30, 58, 138, 0.35);
    }

    /* Pagination styles */
    .student-pagination {
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
    
    .student-pagination__info {
        font-size: 0.875rem;
        white-space: nowrap;
        color: var(--student-muted);
    }
    
    @media (min-width: 768px) {
        .student-pagination__info {
            padding-left: 1rem;
        }
    }
    
    .student-pagination__links {
        display: flex;
        justify-content: flex-end;
        flex: 1;
    }

    /* Bootstrap 5 pagination styles */
    .pagination {
        margin-bottom: 0;
    }

    .page-link {
        color: var(--student-primary);
        border-color: rgba(30, 58, 138, 0.2);
        padding: 0.5rem 0.75rem;
        transition: all 0.2s ease;
    }

    .page-link:hover {
        color: var(--student-secondary);
        background-color: rgba(30, 58, 138, 0.05);
        border-color: rgba(30, 58, 138, 0.3);
    }

    .page-item.active .page-link {
        background-color: var(--student-primary);
        border-color: var(--student-primary);
        color: #ffffff;
    }

    .page-item.disabled .page-link {
        color: var(--student-muted);
        background-color: #f8f9fa;
        border-color: rgba(30, 58, 138, 0.1);
        cursor: not-allowed;
    }

    @media (max-width: 1024px) {
        .student-admin-shell {
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

        .student-pagination {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            margin-top: 1rem;
        }

        .student-pagination__info {
            text-align: center;
            padding-left: 0;
            font-size: 0.8rem;
        }

        .student-pagination__links {
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
    }
</style>
@endpush










