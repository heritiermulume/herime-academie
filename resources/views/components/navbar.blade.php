<!-- Navbar Component -->
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
    <div class="container">
        <!-- Mobile Layout -->
        <div class="d-flex d-lg-none w-100 align-items-center justify-content-between position-relative" style="padding: 0;">
            <!-- Left: User Avatar (if authenticated) or Contact (if not) -->
            <div class="flex-shrink-0">
                @auth
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center justify-content-center" href="#" role="button" data-bs-toggle="dropdown" style="text-decoration: none; padding: 0.25rem;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; overflow: hidden; flex-shrink: 0; display: inline-block; border: 2px solid var(--primary-color);" title="{{ Auth::user()->name }}">
                                <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" style="width: 100%; height: 100%; object-fit: cover; display: block; border: none; box-shadow: none; transform: none;">
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-start user-profile-dropdown" style="width: 320px; padding: 0; border: none; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); margin-top: 0.5rem;">
                            <!-- User Card -->
                            <li style="padding: 1.25rem; background: linear-gradient(135deg, #003366 0%, #004080 100%); border-radius: 0.375rem 0.375rem 0 0; margin: 0;">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 2px solid rgba(255, 255, 255, 0.3);">
                                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                    </div>
                                    <div class="text-white" style="flex: 1; min-width: 0;">
                                        <div class="fw-bold mb-1" style="font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Auth::user()->name }}</div>
                                        <div class="text-white-50" style="font-size: 0.875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Auth::user()->email }}</div>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider my-0" style="margin: 0;"></li>
                            <!-- Menu Items -->
                            <li style="padding: 0;">
                                <a class="dropdown-item" href="{{ route('profile.redirect') }}" 
                                   @if(session('sso_token'))target="_blank" rel="noopener noreferrer"@endif 
                                   style="padding: 0.75rem 1.25rem;">
                                    <i class="fas fa-user me-2"></i>Profil
                                </a>
                            </li>
                            @php
                                $user = Auth::user();
                                $dashboardLinks = [];
                                $isAmbassador = \App\Models\Ambassador::where('user_id', $user->id)
                                    ->where('is_active', true)
                                    ->exists();

                                if ($user->isAdmin()) {
                                    $dashboardLinks[] = [
                                        'label' => 'Administrateur',
                                        'route' => route('admin.dashboard'),
                                        'icon' => 'fas fa-tools',
                                    ];
                                    $dashboardLinks[] = [
                                        'label' => 'Tableau de bord étudiant',
                                        'route' => route('student.dashboard'),
                                        'icon' => 'fas fa-user-graduate',
                                    ];
                                    $dashboardLinks[] = [
                                        'label' => 'Tableau de bord formateur',
                                        'route' => route('instructor.dashboard'),
                                        'icon' => 'fas fa-chalkboard-teacher',
                                    ];
                                    if ($isAmbassador) {
                                        $dashboardLinks[] = [
                                            'label' => 'Tableau de bord ambassadeur',
                                            'route' => route('ambassador.dashboard'),
                                            'icon' => 'fas fa-handshake',
                                        ];
                                    }
                                } elseif ($user->isInstructor()) {
                                    $dashboardLinks[] = [
                                        'label' => 'Tableau de bord formateur',
                                        'route' => route('instructor.dashboard'),
                                        'icon' => 'fas fa-chalkboard-teacher',
                                    ];
                                    $dashboardLinks[] = [
                                        'label' => 'Tableau de bord étudiant',
                                        'route' => route('student.dashboard'),
                                        'icon' => 'fas fa-user-graduate',
                                    ];
                                    if ($isAmbassador) {
                                        $dashboardLinks[] = [
                                            'label' => 'Tableau de bord ambassadeur',
                                            'route' => route('ambassador.dashboard'),
                                            'icon' => 'fas fa-handshake',
                                        ];
                                    }
                                } else {
                                    $dashboardLinks[] = [
                                        'label' => 'Tableau de bord étudiant',
                                        'route' => route('student.dashboard'),
                                        'icon' => 'fas fa-user-graduate',
                                    ];
                                    if ($isAmbassador) {
                                        $dashboardLinks[] = [
                                            'label' => 'Tableau de bord ambassadeur',
                                            'route' => route('ambassador.dashboard'),
                                            'icon' => 'fas fa-handshake',
                                        ];
                                    }
                                }
                            @endphp

                            @foreach($dashboardLinks as $dashboardLink)
                                <li style="padding: 0;">
                                    <a class="dropdown-item" href="{{ $dashboardLink['route'] }}" style="padding: 0.75rem 1.25rem;">
                                        <i class="{{ $dashboardLink['icon'] }} me-2"></i>{{ $dashboardLink['label'] }}
                                    </a>
                                </li>
                            @endforeach
                            <li style="padding: 0;">
                                <a class="dropdown-item" href="{{ route('messages.index') }}" style="padding: 0.75rem 1.25rem;">
                                    <i class="fas fa-envelope me-2"></i>Messages
                                </a>
                            </li>
                            <li style="padding: 0;">
                                <a class="dropdown-item" href="{{ route('notifications.index') }}" style="padding: 0.75rem 1.25rem;">
                                    <i class="fas fa-bell me-2"></i>Notifications
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-0" style="margin: 0;"></li>
                            
                            <li style="padding: 0;">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item w-100 text-start border-0 bg-transparent" style="padding: 0.75rem 1.25rem;">
                                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <div class="d-flex align-items-center" style="gap: 0.375rem;">
                        @php
                            $finalLoginMobile = url()->full();
                            $callbackLoginMobile = route('sso.callback', ['redirect' => $finalLoginMobile]);
                            $ssoLoginUrlMobile = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callbackLoginMobile);
                        @endphp
                        <a href="{{ $ssoLoginUrlMobile }}" class="d-flex align-items-center justify-content-center" style="text-decoration: none; color: var(--primary-color); padding: 0.25rem;" title="Connexion">
                            <i class="fas fa-sign-in-alt" style="font-size: 1.25rem;"></i>
                        </a>
                        @php
                            $finalRegisterMobile = url()->full();
                            $callbackRegisterMobile = route('sso.callback', ['redirect' => $finalRegisterMobile]);
                            $ssoRegisterUrlMobile = 'https://compte.herime.com/login?force_token=1&action=register&redirect=' . urlencode($callbackRegisterMobile);
                        @endphp
                        <a href="{{ $ssoRegisterUrlMobile }}" class="d-flex align-items-center justify-content-center" style="text-decoration: none; color: var(--primary-color); padding: 0.25rem;" title="Inscription">
                            <i class="fas fa-user-plus" style="font-size: 1.25rem;"></i>
                        </a>
                    </div>
                @endauth
            </div>
            
            <!-- Center: Logo -->
            <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                <a class="navbar-brand" href="{{ route('home') }}" style="margin: 0;">
                    <img src="{{ asset('images/logo-herime-academie.png') }}" alt="Herime Academie" class="navbar-logo-mobile">
                </a>
            </div>
            
            <!-- Right: Notifications and Cart -->
            <div class="flex-shrink-0 d-flex align-items-center" style="justify-content: flex-end; gap: 0.5rem;">
                @auth
                    <!-- Notifications -->
                    <div class="dropdown">
                        <a class="nav-link position-relative notification-toggle" href="#" role="button" data-bs-toggle="dropdown" title="Notifications">
                            <i class="fas fa-bell fa-lg"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" id="notification-count-mobile" style="display: none; background-color: var(--primary-color); color: white;">
                                0
                            </span>
                        </a>
                        <div class="dropdown-menu notification-dropdown notification-dropdown--mobile">
                            <div class="dropdown-header d-flex justify-content-between align-items-center px-3 py-2">
                                <span>Notifications</span>
                                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary notification-view-all" aria-label="Voir toutes les notifications">
                                    <i class="fas fa-list"></i>
                                </a>
                            </div>
                            <div class="dropdown-divider"></div>
                            <ul id="notifications-list-mobile" class="list-unstyled mb-0">
                                <li class="dropdown-item text-center py-3 text-muted">
                                    <i class="fas fa-spinner fa-spin"></i> Chargement...
                                </li>
                            </ul>
                        </div>
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
                <a class="nav-link" href="{{ route('categories.index') }}">Catégories</a>
                <a class="nav-link" href="{{ route('instructors.index') }}">Formateurs</a>
                <a class="nav-link" href="{{ route('ambassador-application.index') }}">Ambassadeurs</a>
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
                        <a class="nav-link position-relative notification-toggle" href="#" role="button" data-bs-toggle="dropdown" title="Notifications">
                            <i class="fas fa-bell fa-lg"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" id="notification-count" style="display: none; background-color: var(--primary-color); color: white;">
                                0
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown notification-dropdown--desktop">
                            <div class="dropdown-header d-flex justify-content-between align-items-center px-3 py-2">
                                <span>Notifications</span>
                                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary notification-view-all" aria-label="Voir toutes les notifications">
                                    <i class="fas fa-list"></i>
                                </a>
                            </div>
                            <div class="dropdown-divider"></div>
                            <ul id="notifications-list" class="list-unstyled mb-0">
                                <li class="dropdown-item text-center py-3 text-muted">
                                    <i class="fas fa-spinner fa-spin"></i> Chargement...
                                </li>
                            </ul>
                        </div>
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
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <div style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; flex-shrink: 0; display: inline-block;" title="{{ Auth::user()->name }}">
                                <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" style="width: 100%; height: 100%; object-fit: cover; display: block; border: none; box-shadow: none; transform: none;">
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end user-profile-dropdown" style="width: 320px; padding: 0; border: none; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">
                            <!-- User Card -->
                            <li style="padding: 1.25rem; background: linear-gradient(135deg, #003366 0%, #004080 100%); border-radius: 0.375rem 0.375rem 0 0; margin: 0;">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 2px solid rgba(255, 255, 255, 0.3);">
                                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                    </div>
                                    <div class="text-white" style="flex: 1; min-width: 0;">
                                        <div class="fw-bold mb-1" style="font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Auth::user()->name }}</div>
                                        <div class="text-white-50" style="font-size: 0.875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Auth::user()->email }}</div>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider my-0" style="margin: 0;"></li>
                            <!-- Menu Items -->
                            @php
                                $user = Auth::user();
                                $dashboardLinks = [];
                                $isAmbassador = \App\Models\Ambassador::where('user_id', $user->id)
                                    ->where('is_active', true)
                                    ->exists();

                                if ($user->isAdmin()) {
                                    $dashboardLinks[] = [
                                        'label' => 'Administrateur',
                                        'route' => route('admin.dashboard'),
                                        'icon' => 'fas fa-tools',
                                    ];
                                    $dashboardLinks[] = [
                                        'label' => 'Tableau de bord étudiant',
                                        'route' => route('student.dashboard'),
                                        'icon' => 'fas fa-user-graduate',
                                    ];
                                    $dashboardLinks[] = [
                                        'label' => 'Tableau de bord formateur',
                                        'route' => route('instructor.dashboard'),
                                        'icon' => 'fas fa-chalkboard-teacher',
                                    ];
                                    if ($isAmbassador) {
                                        $dashboardLinks[] = [
                                            'label' => 'Tableau de bord ambassadeur',
                                            'route' => route('ambassador.dashboard'),
                                            'icon' => 'fas fa-handshake',
                                        ];
                                    }
                                } elseif ($user->isInstructor()) {
                                    $dashboardLinks[] = [
                                        'label' => 'Tableau de bord formateur',
                                        'route' => route('instructor.dashboard'),
                                        'icon' => 'fas fa-chalkboard-teacher',
                                    ];
                                    $dashboardLinks[] = [
                                        'label' => 'Tableau de bord étudiant',
                                        'route' => route('student.dashboard'),
                                        'icon' => 'fas fa-user-graduate',
                                    ];
                                    if ($isAmbassador) {
                                        $dashboardLinks[] = [
                                            'label' => 'Tableau de bord ambassadeur',
                                            'route' => route('ambassador.dashboard'),
                                            'icon' => 'fas fa-handshake',
                                        ];
                                    }
                                } else {
                                    $dashboardLinks[] = [
                                        'label' => 'Tableau de bord étudiant',
                                        'route' => route('student.dashboard'),
                                        'icon' => 'fas fa-user-graduate',
                                    ];
                                    if ($isAmbassador) {
                                        $dashboardLinks[] = [
                                            'label' => 'Tableau de bord ambassadeur',
                                            'route' => route('ambassador.dashboard'),
                                            'icon' => 'fas fa-handshake',
                                        ];
                                    }
                                }
                            @endphp

                            @foreach($dashboardLinks as $dashboardLink)
                                <li style="padding: 0;">
                                    <a class="dropdown-item" href="{{ $dashboardLink['route'] }}" style="padding: 0.75rem 1.25rem;">
                                        <i class="{{ $dashboardLink['icon'] }} me-2"></i>{{ $dashboardLink['label'] }}
                                    </a>
                                </li>
                            @endforeach
                            <li style="padding: 0;">
                                <a class="dropdown-item" href="{{ route('profile.redirect') }}" 
                                   @if(session('sso_token'))target="_blank" rel="noopener noreferrer"@endif 
                                   style="padding: 0.75rem 1.25rem;">
                                    <i class="fas fa-user me-2"></i>Profil
                                </a>
                            </li>
                            <li style="padding: 0;">
                                <a class="dropdown-item" href="{{ route('messages.index') }}" style="padding: 0.75rem 1.25rem;">
                                    <i class="fas fa-envelope me-2"></i>Messages
                                </a>
                            </li>
                            <li style="padding: 0;">
                                <a class="dropdown-item" href="{{ route('notifications.index') }}" style="padding: 0.75rem 1.25rem;">
                                    <i class="fas fa-bell me-2"></i>Notifications
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-0" style="margin: 0;"></li>
                            <li style="padding: 0;">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item w-100 text-start border-0 bg-transparent" style="padding: 0.75rem 1.25rem;">
                                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    @php
                        $finalLogin = url()->full();
                        $callbackLogin = route('sso.callback', ['redirect' => $finalLogin]);
                        $ssoLoginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callbackLogin);
                    @endphp
                    <a class="nav-link me-3" href="{{ $ssoLoginUrl }}" title="Connexion">
                        <i class="fas fa-sign-in-alt fa-lg"></i>
                        <span class="d-none d-lg-inline ms-1">Connexion</span>
                    </a>
                    @php
                        $finalRegister = url()->full();
                        $callbackRegister = route('sso.callback', ['redirect' => $finalRegister]);
                        $ssoRegisterUrl = 'https://compte.herime.com/login?force_token=1&action=register&redirect=' . urlencode($callbackRegister);
                    @endphp
                    <a class="btn btn-primary" href="{{ $ssoRegisterUrl }}" title="S'inscrire">
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
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('categories.index') }}">
                        <i class="fas fa-th-large me-2"></i>Catégories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('instructors.index') }}">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Formateurs
                    </a>
                </li>
                @auth
                    @php
                        $hasApplication = auth()->user()->role !== 'instructor' && \App\Models\InstructorApplication::where('user_id', auth()->id())->exists();
                    @endphp
                    @if(auth()->user()->role !== 'instructor' && !$hasApplication)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('instructor-application.index') }}">
                                <i class="fas fa-rocket me-2"></i>Devenir Formateur
                            </a>
                        </li>
                    @elseif($hasApplication)
                        @php
                            $application = \App\Models\InstructorApplication::where('user_id', auth()->id())->first();
                        @endphp
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('instructor-application.status', $application) }}">
                                <i class="fas fa-file-alt me-2"></i>Ma candidature
                            </a>
                        </li>
                    @endif
                @endauth
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('ambassador-application.index') }}">
                        <i class="fas fa-handshake me-2"></i>Ambassadeurs
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
                    @php
                        $user = Auth::user();
                        $dashboardLinks = [];
                        $isAmbassador = \App\Models\Ambassador::where('user_id', $user->id)
                            ->where('is_active', true)
                            ->exists();

                        if ($user->isAdmin()) {
                            $dashboardLinks[] = [
                                'label' => 'Administrateur',
                                'route' => route('admin.dashboard'),
                                'icon' => 'fas fa-tools',
                            ];
                            $dashboardLinks[] = [
                                'label' => 'Tableau de bord étudiant',
                                'route' => route('student.dashboard'),
                                'icon' => 'fas fa-user-graduate',
                            ];
                            $dashboardLinks[] = [
                                'label' => 'Tableau de bord formateur',
                                'route' => route('instructor.dashboard'),
                                'icon' => 'fas fa-chalkboard-teacher',
                            ];
                            if ($isAmbassador) {
                                $dashboardLinks[] = [
                                    'label' => 'Tableau de bord ambassadeur',
                                    'route' => route('ambassador.dashboard'),
                                    'icon' => 'fas fa-handshake',
                                ];
                            }
                        } elseif ($user->isInstructor()) {
                            $dashboardLinks[] = [
                                'label' => 'Tableau de bord formateur',
                                'route' => route('instructor.dashboard'),
                                'icon' => 'fas fa-chalkboard-teacher',
                            ];
                            $dashboardLinks[] = [
                                'label' => 'Tableau de bord étudiant',
                                'route' => route('student.dashboard'),
                                'icon' => 'fas fa-user-graduate',
                            ];
                            if ($isAmbassador) {
                                $dashboardLinks[] = [
                                    'label' => 'Tableau de bord ambassadeur',
                                    'route' => route('ambassador.dashboard'),
                                    'icon' => 'fas fa-handshake',
                                ];
                            }
                        } else {
                            $dashboardLinks[] = [
                                'label' => 'Tableau de bord étudiant',
                                'route' => route('student.dashboard'),
                                'icon' => 'fas fa-user-graduate',
                            ];
                            if ($isAmbassador) {
                                $dashboardLinks[] = [
                                    'label' => 'Tableau de bord ambassadeur',
                                    'route' => route('ambassador.dashboard'),
                                    'icon' => 'fas fa-handshake',
                                ];
                            }
                        }
                    @endphp

                    @foreach($dashboardLinks as $dashboardLink)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ $dashboardLink['route'] }}">
                                <i class="{{ $dashboardLink['icon'] }} me-2"></i>{{ $dashboardLink['label'] }}
                            </a>
                        </li>
                    @endforeach

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
                        <a class="nav-link" href="{{ route('profile.redirect') }}" 
                           @if(session('sso_token'))target="_blank" rel="noopener noreferrer"@endif>
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
                        @php
                            $finalLogin2 = url()->full();
                            $callbackLogin2 = route('sso.callback', ['redirect' => $finalLogin2]);
                            $ssoLoginUrl2 = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callbackLogin2);
                        @endphp
                        <a class="nav-link" href="{{ $ssoLoginUrl2 }}">
                            <i class="fas fa-sign-in-alt me-2"></i>Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        @php
                            $finalRegister2 = url()->full();
                            $callbackRegister2 = route('sso.callback', ['redirect' => $finalRegister2]);
                            $ssoRegisterUrl2 = 'https://compte.herime.com/login?force_token=1&action=register&redirect=' . urlencode($callbackRegister2);
                        @endphp
                        <a class="nav-link" href="{{ $ssoRegisterUrl2 }}">
                            <i class="fas fa-user-plus me-2"></i>S'inscrire
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

@push('styles')
<style>
    /* Correction du débordement du texte dans le menu dropdown de l'avatar */
    .navbar .dropdown-menu.user-profile-dropdown {
        width: 320px !important;
        max-width: calc(100vw - 2rem) !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }
    
    /* S'assurer que tous les éléments du menu respectent la largeur */
    .navbar .dropdown-menu.user-profile-dropdown > li {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
    }
    
    /* S'assurer que les éléments du menu ne débordent pas - approche avec span pour le texte */
    .navbar .dropdown-menu.user-profile-dropdown .dropdown-item {
        display: flex !important;
        align-items: flex-start !important;
        gap: 0.5rem !important;
        white-space: normal !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        max-width: 100% !important;
        width: 100% !important;
        padding: 0.75rem 1.25rem !important;
        line-height: 1.4 !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
        overflow: visible !important;
        position: relative !important;
    }
    
    /* S'assurer que les icônes ne se rétrécissent pas et restent alignées en haut */
    .navbar .dropdown-menu.user-profile-dropdown .dropdown-item i {
        flex-shrink: 0 !important;
        width: 1.25rem !important;
        min-width: 1.25rem !important;
        max-width: 1.25rem !important;
        text-align: center !important;
        align-self: flex-start !important;
        margin-top: 0.15rem !important;
        line-height: 1.4 !important;
        display: inline-block !important;
    }
    
    /* Le texte après l'icône doit pouvoir se retourner */
    .navbar .dropdown-menu.user-profile-dropdown .dropdown-item {
        flex-wrap: wrap !important;
    }
    
    /* Forcer le texte à se retourner - cibler le contenu textuel directement */
    .navbar .dropdown-menu.user-profile-dropdown .dropdown-item:not(button):not([type="submit"]) {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
        hyphens: auto !important;
    }
    
    /* S'assurer que le texte dans les liens ne dépasse pas */
    .navbar .dropdown-menu.user-profile-dropdown .dropdown-item:not(button):not([type="submit"]) {
        text-overflow: clip !important;
    }
    
    /* Pour les boutons (comme déconnexion), garder le comportement normal */
    .navbar .dropdown-menu.user-profile-dropdown .dropdown-item button,
    .navbar .dropdown-menu.user-profile-dropdown form .dropdown-item {
        display: flex !important;
        align-items: center !important;
        white-space: nowrap !important;
    }
    
    /* S'assurer que le texte dans la carte utilisateur ne déborde pas */
    .navbar .dropdown-menu.user-profile-dropdown li[style*="padding: 1.25rem"] {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
    }
    
    .navbar .dropdown-menu.user-profile-dropdown li[style*="padding: 1.25rem"] .text-white {
        overflow: hidden !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    
    .navbar .dropdown-menu.user-profile-dropdown li[style*="padding: 1.25rem"] .fw-bold {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 100% !important;
        display: block !important;
    }
    
    .navbar .dropdown-menu.user-profile-dropdown li[style*="padding: 1.25rem"] .text-white-50 {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 100% !important;
        display: block !important;
    }
    
    /* Sur mobile/tablette, l'en-tête doit occuper toute la largeur */
    @media (max-width: 991.98px) {
        .navbar .dropdown-menu.user-profile-dropdown {
            width: calc(100vw - 1rem) !important;
            max-width: 320px !important;
            padding: 0 !important;
            border-radius: 0.375rem !important;
            overflow: hidden !important;
        }
        
        /* L'en-tête doit occuper toute la largeur sans marges horizontales */
        .navbar .dropdown-menu.user-profile-dropdown > li:first-child {
            padding-top: 1.25rem !important;
            padding-bottom: 1.25rem !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-radius: 0.375rem 0.375rem 0 0 !important;
        }
        
        /* Le contenu interne de l'en-tête garde son padding horizontal */
        .navbar .dropdown-menu.user-profile-dropdown > li:first-child > div {
            padding-left: 1.25rem !important;
            padding-right: 1.25rem !important;
        }
    }
    
    /* Sur mobile, réduire la largeur si nécessaire */
    @media (max-width: 575px) {
        .navbar .dropdown-menu.user-profile-dropdown {
            width: calc(100vw - 1rem) !important;
            max-width: 320px !important;
        }
    }
    
    /* S'assurer que les séparateurs ne débordent pas */
    .navbar .dropdown-menu.user-profile-dropdown .dropdown-divider {
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    
    /* Forcer Bootstrap à ne pas appliquer nowrap sur les dropdown-item */
    .navbar .dropdown-menu.user-profile-dropdown .dropdown-item {
        white-space: normal !important;
    }
    
    /* Surcharger tous les styles Bootstrap qui pourraient forcer nowrap */
    .navbar .dropdown-menu.user-profile-dropdown a.dropdown-item,
    .navbar .dropdown-menu.user-profile-dropdown button.dropdown-item {
        white-space: normal !important;
        overflow-wrap: break-word !important;
        word-break: break-word !important;
    }
</style>
@endpush

