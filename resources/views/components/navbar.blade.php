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
                        <ul class="dropdown-menu dropdown-menu-start" style="width: 280px; padding: 0; border: none; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); margin-top: 0.5rem;">
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
                                <a class="dropdown-item" href="{{ route('dashboard') }}" style="padding: 0.75rem 1.25rem;">
                                    <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                                </a>
                            </li>
                            <li style="padding: 0;">
                                <a class="dropdown-item" href="{{ app(\App\Services\SSOService::class)->getProfileUrl() }}" target="_blank" rel="noopener noreferrer" style="padding: 0.75rem 1.25rem;">
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
                                <a class="dropdown-item" href="{{ route('contact') }}" style="padding: 0.75rem 1.25rem;">
                                    <i class="fas fa-envelope me-2"></i>Nous contacter
                                </a>
                            </li>
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
                    <div class="d-flex align-items-center" style="gap: 0.75rem;">
                        <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center" style="text-decoration: none; color: var(--primary-color); padding: 0.5rem;" title="Connexion">
                            <i class="fas fa-sign-in-alt" style="font-size: 1.25rem;"></i>
                        </a>
                        <a href="{{ route('register') }}" class="d-flex align-items-center justify-content-center" style="text-decoration: none; color: var(--primary-color); padding: 0.5rem;" title="Inscription">
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
                @auth
                    @if(auth()->user()->role !== 'instructor')
                        <a class="nav-link" href="{{ route('instructor-application.index') }}">Devenir Formateur</a>
                    @endif
                @endauth
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
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <div style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; flex-shrink: 0; display: inline-block;" title="{{ Auth::user()->name }}">
                                <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" style="width: 100%; height: 100%; object-fit: cover; display: block; border: none; box-shadow: none; transform: none;">
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="width: 280px; padding: 0; border: none; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">
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
                                <a class="dropdown-item" href="{{ route('dashboard') }}" style="padding: 0.75rem 1.25rem;">
                                    <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                                </a>
                            </li>
                            <li style="padding: 0;">
                                <a class="dropdown-item" href="{{ app(\App\Services\SSOService::class)->getProfileUrl() }}" target="_blank" rel="noopener noreferrer" style="padding: 0.75rem 1.25rem;">
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
                    <a class="nav-link" href="{{ route('instructor-application.index') }}">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Formateurs
                    </a>
                </li>
                @auth
                    @if(auth()->user()->role !== 'instructor')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('instructor-application.index') }}">
                                <i class="fas fa-rocket me-2"></i>Devenir Formateur
                            </a>
                        </li>
                    @endif
                @endauth
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
                        <a class="nav-link" href="{{ app(\App\Services\SSOService::class)->getProfileUrl() }}" target="_blank" rel="noopener noreferrer">
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

