@extends('layouts.admin')

@section('title', 'Profil de ' . $user->name)
@section('admin-title', 'Profil utilisateur')
@section('admin-subtitle', 'Consultez les informations synchronisées et l\'activité de ' . ($user->name ?? 'l\'utilisateur'))
@section('admin-actions')
    <a href="{{ route('admin.users') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
        <i class="fas fa-edit me-2"></i>Modifier
    </a>
@endsection

@section('admin-content')
    <div class="row g-4">
        <div class="col-md-8">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-user me-2"></i>Informations de l'utilisateur
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="{{ $user->avatar_url }}" 
                             alt="{{ $user->name }}" 
                             class="rounded-circle"
                             style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <h5 class="mb-1">{{ $user->name }}</h5>
                            <p class="text-muted mb-1">
                                <i class="fas fa-envelope me-2"></i>{{ $user->email }}
                            </p>
                        </div>
                    </div>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Rôle</dt>
                        <dd class="col-sm-8">
                            @switch($user->role)
                                @case('admin')
                                    <span class="badge bg-danger">Administrateur</span>
                                    @break
                                @case('instructor')
                                    <span class="badge bg-success">Formateur</span>
                                    @break
                                @case('affiliate')
                                    <span class="badge bg-info">Affilié</span>
                                    @break
                                @default
                                    <span class="badge bg-primary">Étudiant</span>
                            @endswitch
                        </dd>

                        <dt class="col-sm-4">Statut du compte</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $user->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                            <span class="badge {{ $user->is_verified ? 'bg-info' : 'bg-warning text-dark' }} ms-2">
                                {{ $user->is_verified ? 'Email vérifié' : 'Non vérifié' }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Téléphone</dt>
                        <dd class="col-sm-8">{{ $user->phone ?? 'Non renseigné' }}</dd>

                        <dt class="col-sm-4">Date de naissance</dt>
                        <dd class="col-sm-8">
                            {{ $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') . ' (' . $user->date_of_birth->age . ' ans)' : 'Non renseignée' }}
                        </dd>

                        <dt class="col-sm-4">Genre</dt>
                        <dd class="col-sm-8">
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
                        </dd>

                        @if($user->bio)
                            <dt class="col-sm-4">Biographie</dt>
                            <dd class="col-sm-8">{{ $user->bio }}</dd>
                        @endif

                        <dt class="col-sm-4">Membre depuis</dt>
                        <dd class="col-sm-8">
                            <i class="fas fa-calendar me-2"></i>
                            {{ $user->created_at->format('d/m/Y à H:i') }}
                        </dd>

                        <dt class="col-sm-4">Dernière connexion</dt>
                        <dd class="col-sm-8">
                            {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y à H:i') . ' (' . $user->last_login_at->diffForHumans() . ')' : 'Jamais' }}
                        </dd>
                    </dl>
                </div>
            </section>

            @if($user->website || $user->linkedin || $user->twitter || $user->youtube)
                <section class="admin-panel">
                    <div class="admin-panel__header">
                        <h3>
                            <i class="fas fa-share-alt me-2"></i>Réseaux sociaux
                        </h3>
                    </div>
                    <div class="admin-panel__body">
                        <dl class="row mb-0">
                            @if($user->website)
                                <dt class="col-sm-4">Site web</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ $user->website }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fas fa-globe me-2"></i>{{ $user->website }}
                                    </a>
                                </dd>
                            @endif
                            
                            @if($user->linkedin)
                                <dt class="col-sm-4">LinkedIn</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ $user->linkedin }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fab fa-linkedin me-2"></i>Voir le profil
                                    </a>
                                </dd>
                            @endif
                            
                            @if($user->twitter)
                                <dt class="col-sm-4">Twitter</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ $user->twitter }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fab fa-twitter me-2"></i>Voir le profil
                                    </a>
                                </dd>
                            @endif
                            
                            @if($user->youtube)
                                <dt class="col-sm-4">YouTube</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ $user->youtube }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fab fa-youtube me-2"></i>Voir la chaîne
                                    </a>
                                </dd>
                            @endif
                        </dl>
                    </div>
                </section>
            @endif

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-book me-2"></i>Gestion des cours
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <span class="text-muted">Cours inscrits: {{ $enrollments->count() }}</span>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#grantAccessModal">
                            <i class="fas fa-gift me-2"></i>Donner accès gratuit
                        </button>
                    </div>
                    
                    @if($enrollments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">Avatar</th>
                                        <th style="min-width: 250px;">Cours</th>
                                        <th>Statut</th>
                                        <th>Progression</th>
                                        <th>Type d'accès</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollments as $enrollment)
                                        <tr>
                                            <td>
                                                @if($enrollment->course && $enrollment->course->thumbnail_url)
                                                    <img src="{{ $enrollment->course->thumbnail_url }}" 
                                                         alt="{{ $enrollment->course->title }}" 
                                                         class="rounded"
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-book text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $enrollment->course ? $enrollment->course->title : 'Cours supprimé' }}</strong>
                                                    @if($enrollment->course && $enrollment->course->instructor)
                                                        <br><small class="text-muted">par {{ $enrollment->course->instructor->name }}</small>
                                                    @endif
                                                </div>
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
                                                    <button type="button" class="btn btn-sm btn-danger btn-action-small" 
                                                            onclick="confirmRevokeAccess({{ $user->id }}, {{ $enrollment->course->id }}, '{{ $enrollment->course->title }}')"
                                                            title="Enlever l'accès">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
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
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-history me-2"></i>Historique d'activité
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-calendar text-primary"></i>
                                <div>
                                    <strong>Inscription</strong>
                                    <p class="text-muted mb-0">{{ $user->created_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                        </li>
                        
                        @if($user->email_verified_at)
                            <li class="mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <div>
                                        <strong>Email vérifié</strong>
                                        <p class="text-muted mb-0">{{ $user->email_verified_at->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>
                            </li>
                        @endif
                        
                        @if($user->last_login_at)
                            <li class="mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-sign-in-alt text-info"></i>
                                    <div>
                                        <strong>Dernière connexion</strong>
                                        <p class="text-muted mb-0">{{ $user->last_login_at->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>
                            </li>
                        @endif
                        
                        <li>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-edit text-warning"></i>
                                <div>
                                    <strong>Dernière modification</strong>
                                    <p class="text-muted mb-0">{{ $user->updated_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </section>
        </div>

        <div class="col-md-4">
            @if($user->role == 'instructor')
                <section class="admin-panel">
                    <div class="admin-panel__header">
                        <h3>
                            <i class="fas fa-chart-bar me-2"></i>Statistiques formateur
                        </h3>
                    </div>
                    <div class="admin-panel__body">
                        <dl class="row mb-0">
                            <dt class="col-sm-6">Cours créés</dt>
                            <dd class="col-sm-6">{{ $user->courses_count ?? 0 }}</dd>

                            <dt class="col-sm-6">Étudiants</dt>
                            <dd class="col-sm-6">{{ $user->enrollments_count ?? 0 }}</dd>

                            <dt class="col-sm-6">Note moyenne</dt>
                            <dd class="col-sm-6">
                                {{ number_format($user->courses->avg('stats.average_rating') ?? 0, 1) }}
                                <i class="fas fa-star text-warning"></i>
                            </dd>
                        </dl>
                    </div>
                </section>
            @endif

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-exclamation-triangle me-2"></i>Zone de danger
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <p class="text-muted small mb-3">
                        La suppression de l'utilisateur est irréversible et supprimera toutes les données associées.
                    </p>
                    <button type="button" class="btn btn-danger w-100" onclick="openDeleteModal({{ $user->id }})">
                        <i class="fas fa-trash me-2"></i>Supprimer l'utilisateur
                    </button>
                </div>
            </section>
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
                <div class="modal-footer delete-user-modal-footer">
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
/* Styles identiques à analytics */
/* Réduire la taille des boutons admin-actions et ajouter bordure */
.admin-content__actions .btn {
    font-size: 0.85rem !important;
    padding: 0.45rem 0.7rem !important;
}

/* Réduire la taille du bouton d'action "Enlever l'accès" */
.btn-action-small {
    padding: 0.25rem 0.4rem !important;
    font-size: 0.75rem !important;
    line-height: 1.2 !important;
    min-width: 32px !important;
    height: 28px !important;
}

.btn-action-small i {
    font-size: 0.7rem !important;
}

/* Forcer les boutons du modal à rester sur la même ligne */
#revokeAccessModal .modal-footer {
    display: flex !important;
    flex-wrap: nowrap !important;
    justify-content: flex-end !important;
    gap: 0.5rem !important;
}

#revokeAccessModal .modal-footer .btn,
#revokeAccessModal .modal-footer form {
    flex: 0 0 auto !important;
    margin: 0 !important;
}

#revokeAccessModal .modal-footer form {
    display: inline-block !important;
}

/* Réduire la taille des boutons du modal */
#revokeAccessModal .modal-footer .btn {
    font-size: 0.8rem !important;
    padding: 0.35rem 0.6rem !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
}

/* Réduire spécifiquement la largeur du bouton Annuler */
#revokeAccessModal .modal-footer .btn-secondary {
    min-width: auto !important;
    max-width: fit-content !important;
    width: auto !important;
    flex: 0 0 auto !important;
    padding: 0.35rem 0.8rem !important;
}

#revokeAccessModal .modal-footer .btn i {
    font-size: 0.75rem !important;
    margin-right: 0.4rem !important;
}

/* Ajouter une bordure visible sur le bouton "Retour à la liste" */
.admin-content__actions .btn-outline-secondary {
    border: 1px solid #6c757d !important;
    border-width: 1px !important;
}

.admin-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-card__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 16px 16px 0 0;
}

/* Réduire l'espace au-dessus du contenu sur desktop */
@media (min-width: 992px) {
    .admin-card__header .admin-card__title.mb-1 {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-card__header {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }
}

.admin-card__title {
    margin: 0;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
}

.admin-card__body {
    padding: 1.25rem;
}

/* Styles pour admin-panel - identiques à analytics */
.admin-panel {
    margin-bottom: 2rem;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-panel__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-panel__header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.admin-panel__body {
    padding: 1rem;
}

/* Padding légèrement réduit sur desktop */
@media (min-width: 992px) {
    .admin-panel__body {
        padding: 0.875rem 1rem;
    }
}

/* Corriger le chevauchement des boutons dans la carte Informations du certificat */
.admin-panel__body dl.row dd {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.admin-panel__body dl.row dd .badge {
    flex-shrink: 0;
}

.admin-panel__body dl.row dd .btn,
.admin-panel__body dl.row dd button {
    flex-shrink: 0;
    white-space: nowrap;
}

/* Styles responsives pour les paddings et margins - identiques à analytics */
@media (max-width: 991.98px) {
    /* Réduire les paddings et margins sur tablette */
    .admin-panel {
        margin-bottom: 1rem;
    }
    
    .admin-panel__body {
        padding: 0 !important;
    }
    
    .admin-panel__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-panel__header h3 {
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.5rem;
        --bs-gutter-y: 0.5rem;
    }
    
    .admin-card {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-card__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-card__body {
        padding: 0.5rem;
    }
}

@media (max-width: 767.98px) {
    /* Réduire encore plus les paddings et margins sur mobile */
    .admin-panel {
        margin-bottom: 0.75rem;
    }
    
    .admin-panel__body {
        padding: 1.25rem !important;
    }
    
    .admin-panel__header {
        padding: 0.375rem 0.5rem;
    }
    
    .admin-panel__header h3 {
        font-size: 0.95rem;
        margin-bottom: 0.125rem;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.375rem;
        --bs-gutter-y: 0.375rem;
    }
    
    .admin-card {
        margin-bottom: 0.5rem !important;
    }
    
    /* Garder le même design de carte que sur desktop - mêmes tailles */
    .admin-card__header {
        padding: 1rem 1.25rem !important;
    }
    
    .admin-card__body {
        padding: 1.25rem !important;
    }
    
    /* Empiler les boutons sur mobile dans la carte Informations du certificat */
    .admin-panel__body dl.row dd .btn,
    .admin-panel__body dl.row dd button {
        flex: 1 1 auto;
        min-width: 120px;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    
    /* Centrer les boutons admin-actions sur mobile */
    .admin-content__header {
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
    }
    
    .admin-content__header > div:first-child {
        width: 100% !important;
        text-align: center !important;
        margin-bottom: 1rem !important;
    }
    
    .admin-content__actions {
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: 0.5rem !important;
        justify-content: center !important;
        width: 100% !important;
    }
    
    .admin-content__actions .btn {
        flex: 0 1 auto !important;
        min-width: 0 !important;
        white-space: nowrap !important;
        font-size: 0.8rem !important;
        padding: 0.4rem 0.6rem !important;
    }
    
    /* Ajouter une bordure visible sur le bouton "Retour à la liste" */
    .admin-content__actions .btn-outline-secondary {
        border: 1px solid #6c757d !important;
        border-width: 1px !important;
    }
    
    .admin-content__actions .btn i {
        margin-right: 0.4rem !important;
        font-size: 0.75rem !important;
    }
    
    /* Styles pour le modal de suppression d'utilisateur */
    #deleteUserModal .modal-footer {
        display: flex !important;
        flex-wrap: nowrap !important;
        justify-content: space-between !important;
        gap: 0.5rem !important;
        width: 100% !important;
    }
    
    #deleteUserModal .modal-footer .btn,
    #deleteUserModal .modal-footer form {
        flex: 1 1 50% !important;
        margin: 0 !important;
        width: calc(50% - 0.25rem) !important;
        max-width: calc(50% - 0.25rem) !important;
    }
    
    #deleteUserModal .modal-footer form {
        display: flex !important;
    }
    
    #deleteUserModal .modal-footer form .btn {
        width: 100% !important;
        flex: 1 1 100% !important;
    }
    
    #deleteUserModal .modal-footer .btn {
        font-size: 0.9rem !important;
        padding: 0.5rem 1rem !important;
        white-space: nowrap !important;
    }
    
    /* Forcer la réduction de la taille du bouton d'action sur mobile */
    .admin-panel__body .btn-action-small,
    .table .btn-action-small,
    .table td .btn-action-small,
    .btn-action-small.btn-sm,
    .btn-action-small.btn-danger,
    button.btn-action-small {
        padding: 0.1rem 0.2rem !important;
        font-size: 0.65rem !important;
        line-height: 1 !important;
        min-width: 24px !important;
        max-width: 24px !important;
        width: 24px !important;
        min-height: 24px !important;
        max-height: 24px !important;
        height: 24px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-sizing: border-box !important;
    }
    
    .admin-panel__body .btn-action-small i,
    .table .btn-action-small i,
    .btn-action-small.btn-sm i,
    .btn-action-small.btn-danger i {
        font-size: 0.6rem !important;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1 !important;
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
