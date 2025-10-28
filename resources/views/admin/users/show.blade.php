@extends('layouts.app')

@section('title', 'Profil de ' . $user->name . ' - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header text-white" style="background-color: #003366;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-sm" title="Tableau de bord">
                                <i class="fas fa-tachometer-alt"></i>
                            </a>
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-light btn-sm" title="Liste des utilisateurs">
                                <i class="fas fa-th-list"></i>
                            </a>
                            <div>
                                <h4 class="mb-1">
                                    <i class="fas fa-user me-2"></i>Profil utilisateur
                                </h4>
                                <p class="mb-0 text-description small">Détails complets de {{ $user->name }}</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-1"></i>Modifier
                            </a>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" 
                                  onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash me-1"></i>Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Colonne gauche : Avatar & Info principale -->
                <div class="col-lg-4">
                    <!-- Card Avatar -->
                    <div class="card border-0 shadow-sm mb-4 profile-card">
                        <div class="card-body text-center p-4">
                            <div class="avatar-container mb-3">
                                <img src="{{ $user->avatar ? $user->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=200&background=003366&color=fff' }}" 
                                     alt="Avatar de {{ $user->name }}" 
                                     class="rounded-circle img-thumbnail avatar-image" 
                                     style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #003366;">
                            </div>
                            
                            <h4 class="fw-bold text-dark mb-1">{{ $user->name }}</h4>
                            <p class="text-muted mb-3">
                                <i class="fas fa-envelope me-1"></i>{{ $user->email }}
                            </p>
                            
                            <!-- Badge rôle -->
                            <div class="mb-3">
                                @switch($user->role)
                                    @case('admin')
                                        <span class="badge bg-danger fs-6 px-3 py-2">
                                            <i class="fas fa-crown me-1"></i>Administrateur
                                        </span>
                                        @break
                                    @case('instructor')
                                        <span class="badge bg-success fs-6 px-3 py-2">
                                            <i class="fas fa-chalkboard-teacher me-1"></i>Formateur
                                        </span>
                                        @break
                                    @case('affiliate')
                                        <span class="badge bg-info fs-6 px-3 py-2">
                                            <i class="fas fa-handshake me-1"></i>Affilié
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-primary fs-6 px-3 py-2">
                                            <i class="fas fa-user-graduate me-1"></i>Étudiant
                                        </span>
                                @endswitch
                            </div>
                            
                            <!-- Statuts -->
                            <div class="mb-3">
                                @if($user->is_active)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Compte actif
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-times-circle me-1"></i>Compte inactif
                                    </span>
                                @endif
                                
                                @if($user->is_verified)
                                    <span class="badge bg-info ms-1">
                                        <i class="fas fa-certificate me-1"></i>Email vérifié
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark ms-1">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Non vérifié
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Dates importantes -->
                            <div class="border-top pt-3 mt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted"><i class="fas fa-calendar-plus me-1"></i>Membre depuis</span>
                                    <strong>{{ $user->created_at->format('d/m/Y') }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted"><i class="fas fa-clock me-1"></i>Dernière connexion</span>
                                    <strong>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($user->role == 'instructor')
                    <!-- Statistiques formateur -->
                    <div class="card border-0 shadow-sm stats-card">
                        <div class="card-header bg-gradient-success text-white">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistiques</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3 pb-3 border-bottom">
                                <h2 class="text-primary mb-0">{{ $user->courses_count ?? 0 }}</h2>
                                <p class="text-muted mb-0"><i class="fas fa-book me-1"></i>Cours créés</p>
                            </div>
                            <div class="text-center mb-3 pb-3 border-bottom">
                                <h2 class="text-success mb-0">{{ $user->enrollments_count ?? 0 }}</h2>
                                <p class="text-muted mb-0"><i class="fas fa-users me-1"></i>Étudiants</p>
                            </div>
                            <div class="text-center">
                                <h2 class="text-warning mb-0">
                                    {{ number_format($user->courses->avg('stats.average_rating') ?? 0, 1) }}
                                    <i class="fas fa-star"></i>
                                </h2>
                                <p class="text-muted mb-0">Note moyenne</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Colonne droite : Détails -->
                <div class="col-lg-8">
                    <!-- Informations personnelles -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-gradient-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Informations personnelles</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="text-muted mb-1"><i class="fas fa-phone me-1"></i>Téléphone</label>
                                        <p class="fw-bold mb-0">{{ $user->phone ?? 'Non renseigné' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="text-muted mb-1"><i class="fas fa-birthday-cake me-1"></i>Date de naissance</label>
                                        <p class="fw-bold mb-0">
                                            {{ $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') . ' (' . $user->date_of_birth->age . ' ans)' : 'Non renseignée' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="text-muted mb-1"><i class="fas fa-venus-mars me-1"></i>Genre</label>
                                        <p class="fw-bold mb-0">
                                            @switch($user->gender)
                                                @case('male')
                                                    <i class="fas fa-mars text-primary"></i> Homme
                                                    @break
                                                @case('female')
                                                    <i class="fas fa-venus text-danger"></i> Femme
                                                    @break
                                                @case('other')
                                                    <i class="fas fa-genderless text-info"></i> Autre
                                                    @break
                                                @default
                                                    Non renseigné
                                            @endswitch
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="text-muted mb-1"><i class="fas fa-user-tag me-1"></i>Rôle</label>
                                        <p class="fw-bold mb-0">{{ ucfirst($user->role) }}</p>
                                    </div>
                                </div>
                                
                                @if($user->bio)
                                <div class="col-12">
                                    <div class="info-item">
                                        <label class="text-muted mb-1"><i class="fas fa-quote-left me-1"></i>Biographie</label>
                                        <p class="mb-0 p-3 bg-light rounded">{{ $user->bio }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($user->website || $user->linkedin || $user->twitter || $user->youtube)
                    <!-- Réseaux sociaux -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);">
                            <h5 class="mb-0 text-white"><i class="fas fa-share-alt me-2"></i>Réseaux sociaux</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @if($user->website)
                                <div class="col-md-6">
                                    <div class="social-link">
                                        <i class="fas fa-globe fa-2x text-primary mb-2"></i>
                                        <label class="text-muted d-block mb-1">Site web</label>
                                        <a href="{{ $user->website }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                                            {{ $user->website }}
                                        </a>
                                    </div>
                                </div>
                                @endif
                                
                                @if($user->linkedin)
                                <div class="col-md-6">
                                    <div class="social-link">
                                        <i class="fab fa-linkedin fa-2x text-info mb-2"></i>
                                        <label class="text-muted d-block mb-1">LinkedIn</label>
                                        <a href="{{ $user->linkedin }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                                            Voir le profil
                                        </a>
                                    </div>
                                </div>
                                @endif
                                
                                @if($user->twitter)
                                <div class="col-md-6">
                                    <div class="social-link">
                                        <i class="fab fa-twitter fa-2x text-primary mb-2"></i>
                                        <label class="text-muted d-block mb-1">Twitter</label>
                                        <a href="{{ $user->twitter }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                                            Voir le profil
                                        </a>
                                    </div>
                                </div>
                                @endif
                                
                                @if($user->youtube)
                                <div class="col-md-6">
                                    <div class="social-link">
                                        <i class="fab fa-youtube fa-2x text-danger mb-2"></i>
                                        <label class="text-muted d-block mb-1">YouTube</label>
                                        <a href="{{ $user->youtube }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                                            Voir la chaîne
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Activité récente -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-gradient-info text-white">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Activité & Dates</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Inscription</h6>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $user->created_at->format('d/m/Y à H:i') }}
                                            <span class="badge bg-light text-dark ms-2">
                                                Il y a {{ $user->created_at->diffForHumans() }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                @if($user->email_verified_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Email vérifié</h6>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-check-circle me-1"></i>
                                            {{ $user->email_verified_at->format('d/m/Y à H:i') }}
                                        </p>
                                    </div>
                                </div>
                                @endif
                                
                                @if($user->last_login_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Dernière connexion</h6>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-sign-in-alt me-1"></i>
                                            {{ $user->last_login_at->format('d/m/Y à H:i') }}
                                            <span class="badge bg-light text-dark ms-2">
                                                {{ $user->last_login_at->diffForHumans() }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Dernière modification</h6>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-edit me-1"></i>
                                            {{ $user->updated_at->format('d/m/Y à H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Design moderne pour page de profil */
.card {
    border-radius: 15px;
    overflow: hidden;
}

.card-header.text-white {
    background: linear-gradient(135deg, #003366 0%, #004080 100%) !important;
    border: none;
    padding: 1.5rem;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #003366 0%, #004080 100%) !important;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
}

/* Avatar */
.avatar-container {
    position: relative;
    display: inline-block;
}

.avatar-image {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.avatar-image:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 20px rgba(0, 51, 102, 0.3);
}

/* Profile card */
.profile-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.profile-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

/* Stats card */
.stats-card {
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-3px);
}

/* Info items */
.info-item {
    padding: 1rem;
    border-radius: 8px;
    background: #f8f9fa;
    transition: background 0.2s ease;
}

.info-item:hover {
    background: #e9ecef;
}

/* Social links */
.social-link {
    padding: 1.5rem;
    border-radius: 10px;
    background: #f8f9fa;
    text-align: center;
    transition: all 0.2s ease;
}

.social-link:hover {
    background: #e9ecef;
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.social-link i {
    transition: transform 0.2s ease;
}

.social-link:hover i {
    transform: scale(1.2);
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -24px;
    top: 12px;
    width: 2px;
    height: calc(100% + 8px);
    background: #dee2e6;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-content {
    padding: 8px 0;
}

/* Badges */
.badge {
    font-weight: 500;
    padding: 0.5em 1em;
}

/* Buttons */
.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .card-header.text-white {
        padding: 1rem;
    }
    
    .card-header h4 {
        font-size: 1.1rem;
    }
    
    .card-header .small {
        font-size: 0.8rem;
    }
    
    .avatar-image {
        width: 120px !important;
        height: 120px !important;
    }
    
    .btn-outline-light.btn-sm {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .card-header .d-flex.gap-2 {
        width: 100%;
        margin-top: 0.5rem;
    }
    
    .card-header .d-flex.gap-2 .btn,
    .card-header .d-flex.gap-2 form {
        flex: 1;
    }
    
    .card-header .d-flex.gap-2 .btn {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .stats-card .card-body > div {
        padding: 0.75rem 0;
    }
    
    .stats-card h2 {
        font-size: 1.5rem;
    }
    
    .social-link {
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -20px;
    }
    
    .timeline-item:before {
        left: -14px;
    }
}
</style>
@endpush
