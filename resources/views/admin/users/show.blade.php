@extends('layouts.admin')

@section('title', 'Profil de ' . $user->name)
@section('admin-title', 'Profil utilisateur')
@section('admin-subtitle', 'Consultez les informations synchronisées et l’activité de ' . ($user->name ?? 'l’utilisateur'))
@section('admin-actions')
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
        <i class="fas fa-edit me-2"></i>Modifier
    </a>
@endsection

@section('admin-content')
    <div class="admin-panel">
        <div class="admin-panel__body admin-panel__body--padded">
            <div class="admin-form-grid admin-form-grid--two">
                <div class="admin-form-card text-center">
                    <div class="avatar-container mb-3">
                        <img src="{{ $user->avatar_url }}" 
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
                    
                    <div class="mt-3">
                        <span class="admin-chip {{ $user->is_active ? 'admin-chip--success' : 'admin-chip--neutral' }}">
                            <i class="fas fa-{{ $user->is_active ? 'check-circle' : 'times-circle' }} me-1"></i>{{ $user->is_active ? 'Compte actif' : 'Compte inactif' }}
                        </span>
                        <span class="admin-chip {{ $user->is_verified ? 'admin-chip--info' : 'admin-chip--warning' }}">
                            <i class="fas fa-{{ $user->is_verified ? 'certificate' : 'exclamation-triangle' }} me-1"></i>{{ $user->is_verified ? 'Email vérifié' : 'Non vérifié' }}
                        </span>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted d-block"><i class="fas fa-calendar-plus me-1"></i>Membre depuis {{ $user->created_at->format('d/m/Y') }}</span>
                        <span class="text-muted d-block"><i class="fas fa-clock me-1"></i>Dernière connexion {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}</span>
                    </div>
                </div>
                @if($user->role == 'instructor')
                <div class="admin-form-card">
                    <h5><i class="fas fa-chart-bar me-2"></i>Statistiques formateur</h5>
                    <div class="admin-stats-grid">
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Cours créés</p>
                            <p class="admin-stat-card__value">{{ $user->courses_count ?? 0 }}</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Étudiants</p>
                            <p class="admin-stat-card__value">{{ $user->enrollments_count ?? 0 }}</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Note moyenne</p>
                            <p class="admin-stat-card__value">{{ number_format($user->courses->avg('stats.average_rating') ?? 0, 1) }}<i class="fas fa-star ms-1"></i></p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="admin-form-card">
                <h5><i class="fas fa-user-circle me-2"></i>Informations personnelles</h5>
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

            @if($user->website || $user->linkedin || $user->twitter || $user->youtube)
            <div class="admin-form-card">
                <h5><i class="fas fa-share-alt me-2"></i>Réseaux sociaux</h5>
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
            @endif

            <!-- Section Gestion des cours -->
            <div class="admin-form-card mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>Gestion des cours</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#grantAccessModal">
                        <i class="fas fa-gift me-2"></i>Donner accès gratuit
                    </button>
                </div>
                
                @if($enrollments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cours</th>
                                    <th>Catégorie</th>
                                    <th>Statut</th>
                                    <th>Date d'inscription</th>
                                    <th>Progression</th>
                                    <th>Type d'accès</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrollments as $enrollment)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if($enrollment->course)
                                                    <strong>{{ $enrollment->course->title }}</strong>
                                                    @if($enrollment->course->instructor)
                                                        <small class="text-muted">par {{ $enrollment->course->instructor->name }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Cours supprimé</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($enrollment->course && $enrollment->course->category)
                                                <span class="badge bg-info">{{ $enrollment->course->category->name }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($enrollment->status)
                                                @case('active')
                                                    <span class="badge bg-success">Actif</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-primary">Terminé</span>
                                                    @break
                                                @case('suspended')
                                                    <span class="badge bg-warning">Suspendu</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Annulé</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $enrollment->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <small>{{ $enrollment->created_at->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px; width: 100px;">
                                                <div class="progress-bar" role="progressbar" style="width: {{ $enrollment->progress }}%">
                                                    {{ number_format($enrollment->progress, 0) }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($enrollment->order_id)
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-shopping-cart me-1"></i>Payé
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="fas fa-gift me-1"></i>Gratuit
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($enrollment->course)
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="confirmRevokeAccess({{ $user->id }}, {{ $enrollment->course->id }}, '{{ $enrollment->course->title }}')"
                                                            title="Enlever l'accès">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Cet utilisateur n'est inscrit à aucun cours.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#grantAccessModal">
                            <i class="fas fa-gift me-2"></i>Donner accès à un cours
                        </button>
                    </div>
                @endif
            </div>

            <div class="admin-form-card mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique d'activité</h5>
                    <span class="admin-chip admin-chip--neutral"><i class="fas fa-clock me-1"></i>Dernière mise à jour {{ $user->updated_at->diffForHumans() }}</span>
                </div>
                <ul class="timeline-simple">
                    <li class="timeline-item">
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
                    </li>
                    
                    @if($user->email_verified_at)
                    <li class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Email vérifié</h6>
                            <p class="text-muted mb-0">
                                <i class="fas fa-check-circle me-1"></i>
                                {{ $user->email_verified_at->format('d/m/Y à H:i') }}
                            </p>
                        </div>
                    </li>
                    @endif
                    
                    @if($user->last_login_at)
                    <li class="timeline-item">
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
                    </li>
                    @endif
                    
                    <li class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Dernière modification</h6>
                            <p class="text-muted mb-0">
                                <i class="fas fa-edit me-1"></i>
                                {{ $user->updated_at->format('d/m/Y à H:i') }}
                            </p>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="admin-panel__footer d-flex justify-content-between flex-wrap gap-2">
                <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </a>
                    <button type="button" class="btn btn-danger" onclick="openDeleteModal({{ $user->id }})">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="deleteUserForm" method="POST" action="{{ route('admin.users.destroy', $user) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour donner accès gratuit à un cours -->
    <div class="modal fade" id="grantAccessModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-gift me-2"></i>Donner accès gratuit à un cours
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.users.grant-course-access', $user) }}">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            Sélectionnez un cours pour donner un accès gratuit à <strong>{{ $user->name }}</strong>.
                        </p>
                        
                        <div class="mb-3">
                            <label for="course_id" class="form-label fw-semibold">Cours <span class="text-danger">*</span></label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="">Sélectionner un cours...</option>
                                @foreach($allCourses as $course)
                                    @php
                                        $isEnrolled = $enrollments->contains(function($enrollment) use ($course) {
                                            return $enrollment->course_id == $course->id;
                                        });
                                    @endphp
                                    @if(!$isEnrolled)
                                        <option value="{{ $course->id }}">
                                            {{ $course->title }}
                                            @if($course->category)
                                                - {{ $course->category->name }}
                                            @endif
                                            @if($course->instructor)
                                                ({{ $course->instructor->name }})
                                            @endif
                                            @if($course->is_free)
                                                <span class="text-success">[Gratuit]</span>
                                            @else
                                                <span class="text-primary">[Payant - {{ number_format($course->price, 0, ',', ' ') }} FCFA]</span>
                                            @endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @if($allCourses->filter(function($course) use ($enrollments) {
                                return !$enrollments->contains(function($enrollment) use ($course) {
                                    return $enrollment->course_id == $course->id;
                                });
                            })->count() == 0)
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Tous les cours disponibles sont déjà accessibles à cet utilisateur.
                                </small>
                            @endif
                        </div>
                        
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note :</strong> L'utilisateur recevra une notification par email lorsqu'un accès gratuit lui sera accordé.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" 
                                @if($allCourses->filter(function($course) use ($enrollments) {
                                    return !$enrollments->contains(function($enrollment) use ($course) {
                                        return $enrollment->course_id == $course->id;
                                    });
                                })->count() == 0) disabled @endif>
                            <i class="fas fa-gift me-2"></i>Donner l'accès
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation pour enlever l'accès -->
    <div class="modal fade" id="revokeAccessModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression d'accès</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir enlever l'accès au cours <strong id="revokeCourseTitle"></strong> ?</p>
                    <p class="text-danger mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Cette action est irréversible. L'utilisateur perdra immédiatement l'accès à ce cours.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="revokeAccessForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-ban me-2"></i>Enlever l'accès
                        </button>
                    </form>
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

@push('scripts')
<script>
function openDeleteModal(userId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    modal.show();
}

function confirmRevokeAccess(userId, courseId, courseTitle) {
    document.getElementById('revokeCourseTitle').textContent = courseTitle;
    document.getElementById('revokeAccessForm').action = `/admin/users/${userId}/courses/${courseId}/revoke-access`;
    const modal = new bootstrap.Modal(document.getElementById('revokeAccessModal'));
    modal.show();
}
</script>
@endpush
