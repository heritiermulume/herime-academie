@extends('layouts.admin')

@section('title', 'Gestion des avis')
@section('admin-title', 'Avis et évaluations')
@section('admin-subtitle', 'Gérez les avis et notes des étudiants sur les cours')

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <!-- Statistiques -->
            <div class="admin-stats-grid mb-4">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Total</p>
                    <p class="admin-stat-card__value">{{ $stats['total'] }}</p>
                    <p class="admin-stat-card__muted">Avis enregistrés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Approuvés</p>
                    <p class="admin-stat-card__value">{{ $stats['approved'] }}</p>
                    <p class="admin-stat-card__muted">Avis publiés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">En attente</p>
                    <p class="admin-stat-card__value">{{ $stats['pending'] }}</p>
                    <p class="admin-stat-card__muted">En modération</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Note moyenne</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['average_rating'], 1) }}</p>
                    <p class="admin-stat-card__muted">Sur 5 étoiles</p>
                </div>
            </div>

            <!-- Répartition par note -->
            @if($stats['by_rating']->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Répartition par note</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($stats['by_rating'] as $ratingData)
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <div class="text-center">
                                <div class="d-flex justify-content-center align-items-center mb-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star{{ $i <= $ratingData->rating ? ' text-warning' : ' text-muted' }}" style="font-size: 0.8rem;"></i>
                                    @endfor
                                </div>
                                <p class="mb-0 fw-bold">{{ $ratingData->count }}</p>
                                <small class="text-muted">avis</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Filtres -->
            <x-admin.search-panel
                :action="route('admin.reviews')"
                formId="reviewsFilterForm"
                filtersId="reviewsFilters"
                :hasFilters="true"
                :searchValue="request('search')"
                placeholder="Rechercher par étudiant, cours ou commentaire..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvés</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Note</label>
                            <select class="form-select" name="rating">
                                <option value="">Toutes les notes</option>
                                <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 étoiles</option>
                                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 étoiles</option>
                                <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 étoiles</option>
                                <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 étoiles</option>
                                <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 étoile</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Cours</label>
                            <select class="form-select" name="course_id">
                                <option value="">Tous les cours</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ Str::limit($course->title, 50) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Tri</label>
                            <select class="form-select" name="sort">
                                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date de création</option>
                                <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Note</option>
                                <option value="is_approved" {{ request('sort') == 'is_approved' ? 'selected' : '' }}>Statut</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Ajustez les filtres puis appliquez-les.</span>
                        <a href="{{ route('admin.reviews') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            <!-- Tableau des avis -->
            <div class="admin-table">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 200px; max-width: 250px;">Étudiant</th>
                                <th style="min-width: 200px; max-width: 300px;">Cours</th>
                                <th class="text-center" style="width: 120px; white-space: nowrap;">Note</th>
                                <th style="min-width: 200px; max-width: 350px;">Commentaire</th>
                                <th class="text-center" style="width: 120px; white-space: nowrap;">Statut</th>
                                <th class="text-center" style="width: 120px; white-space: nowrap;">Date</th>
                                <th class="text-center d-none d-md-table-cell" style="width: 120px;">Actions</th>
                                <th class="text-center d-md-none" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $review)
                                <tr>
                                    <td style="min-width: 200px; max-width: 250px;">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $review->user->avatar_url }}" 
                                                 alt="{{ $review->user->name }}" 
                                                 class="rounded-circle flex-shrink-0"
                                                 style="width: 48px; height: 48px; object-fit: cover;">
                                            <div style="min-width: 0; flex: 1; overflow: hidden;">
                                                <div class="fw-semibold text-truncate d-block" style="max-width: 100%;" title="{{ $review->user->name }}">{{ $review->user->name }}</div>
                                                <div class="text-muted small text-truncate d-block" style="max-width: 100%;" title="{{ $review->user->email }}">
                                                    {{ $review->user->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="min-width: 200px; max-width: 300px;">
                                        <div style="min-width: 0; overflow: hidden;">
                                            <div class="fw-semibold text-truncate d-block" style="max-width: 100%;" title="{{ $review->course->title }}">{{ $review->course->title }}</div>
                                            <div class="text-muted small text-truncate d-block" style="max-width: 100%;" title="Par {{ $review->course->instructor->name }}">
                                                Par {{ $review->course->instructor->name }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            <span class="admin-chip admin-chip--warning">
                                                {{ $review->rating }}/5
                                            </span>
                                            <div class="text-warning" style="font-size: 0.75rem; line-height: 1;">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                    </td>
                                    <td style="min-width: 200px; max-width: 350px;">
                                        @if($review->comment)
                                            <div class="text-truncate d-block" style="max-width: 100%; word-wrap: break-word; overflow-wrap: break-word;" title="{{ $review->comment }}">
                                                {{ $review->comment }}
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">Aucun commentaire</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <span class="admin-chip admin-chip--{{ $review->is_approved ? 'success' : 'warning' }}">
                                            {{ $review->is_approved ? 'Approuvé' : 'En attente' }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <small class="text-muted">
                                            {{ $review->created_at->format('d/m/Y') }}<br>
                                            {{ $review->created_at->format('H:i') }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            @if(!$review->is_approved)
                                                <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="d-inline m-0">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" title="Approuver">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.reviews.reject', $review) }}" method="POST" class="d-inline m-0">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning btn-sm" title="Rejeter">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm" 
                                                    title="Supprimer"
                                                    onclick="openDeleteModal({{ $review->id }}, '{{ addslashes($review->user->name) }}', '{{ addslashes(Str::limit($review->course->title, 50)) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="admin-table__empty">
                                        <i class="fas fa-comments mb-2 d-block"></i>
                                        Aucun avis trouvé.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <x-admin.pagination :paginator="$reviews" />
        </div>
    </section>

    <!-- Modal de suppression -->
    <div class="modal fade" id="deleteReviewModal" tabindex="-1" aria-labelledby="deleteReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="deleteReviewModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body pt-0">
                    <p class="mb-3">Êtes-vous sûr de vouloir supprimer cet avis ?</p>
                    <div class="alert alert-light border mb-0">
                        <div class="d-flex align-items-center mb-2">
                            <strong class="me-2">Étudiant :</strong>
                            <span id="deleteReviewUserName"></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <strong class="me-2">Cours :</strong>
                            <span id="deleteReviewCourseTitle"></span>
                        </div>
                    </div>
                    <p class="text-danger mt-3 mb-0">
                        <small><i class="fas fa-info-circle me-1"></i>Cette action est irréversible.</small>
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <div class="w-100 d-flex flex-row gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary flex-fill flex-sm-grow-0" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Annuler
                        </button>
                        <form id="deleteReviewForm" method="POST" class="d-inline flex-fill flex-sm-grow-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash-alt me-2"></i>Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function openDeleteModal(reviewId, userName, courseTitle) {
    // Mettre à jour les informations dans le modal
    document.getElementById('deleteReviewUserName').textContent = userName;
    document.getElementById('deleteReviewCourseTitle').textContent = courseTitle;
    
    // Mettre à jour l'action du formulaire
    const form = document.getElementById('deleteReviewForm');
    form.action = `/admin/reviews/${reviewId}`;
    
    // Afficher le modal
    const modal = new bootstrap.Modal(document.getElementById('deleteReviewModal'));
    modal.show();
}
</script>
@endpush

@push('styles')
<style>
/* Gestion des contenus qui dépassent dans les colonnes */
.admin-table table td {
    overflow: hidden;
    text-overflow: ellipsis;
}

.admin-table table td .text-truncate {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

/* Colonne Étudiant */
.admin-table table td:first-child {
    max-width: 250px;
}

/* Colonne Cours */
.admin-table table td:nth-child(2) {
    max-width: 300px;
}

/* Colonne Commentaire */
.admin-table table td:nth-child(4) {
    max-width: 350px;
}

/* Colonnes avec white-space: nowrap */
.admin-table table td.text-center[style*="white-space: nowrap"] {
    white-space: nowrap !important;
}

/* Styles responsives pour les paddings et margins */
@media (max-width: 991.98px) {
    /* Supprimer les scrollbars des conteneurs, garder seulement celle de table-responsive */
    .admin-table {
        overflow: visible !important;
    }
    
    .admin-panel__body {
        overflow: visible !important;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Ajuster les max-width sur tablette */
    .admin-table table td:first-child {
        max-width: 200px;
    }
    
    .admin-table table td:nth-child(2) {
        max-width: 250px;
    }
    
    .admin-table table td:nth-child(4) {
        max-width: 250px;
    }
}

@media (max-width: 767.98px) {
    /* Supprimer les scrollbars des conteneurs, garder seulement celle de table-responsive */
    .admin-table {
        overflow: visible !important;
    }
    
    .admin-panel__body {
        overflow: visible !important;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Ajuster les max-width sur mobile */
    .admin-table table td:first-child {
        max-width: 180px;
    }
    
    .admin-table table td:nth-child(2) {
        max-width: 200px;
    }
    
    .admin-table table td:nth-child(4) {
        max-width: 200px;
    }
}


/* Styles pour le modal de suppression - Responsive */
/* Sur mobile, utiliser un margin réduit */
@media (max-width: 575.98px) {
    #deleteReviewModal .modal-dialog {
        margin: 0.75rem auto;
    }
}

/* Sur desktop, s'assurer que le modal est bien centré */
@media (min-width: 576px) {
    #deleteReviewModal .modal-dialog {
        margin: 1.75rem auto;
    }
    
    /* S'assurer que le centrage vertical fonctionne uniquement quand le modal est ouvert */
    #deleteReviewModal.modal.show {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    /* S'assurer que le modal fermé n'interfère pas avec les clics */
    #deleteReviewModal.modal:not(.show) {
        display: none !important;
        pointer-events: none !important;
        z-index: -1 !important;
    }
    
    /* S'assurer que le backdrop n'interfère pas quand le modal est fermé */
    #deleteReviewModal.modal:not(.show) + .modal-backdrop,
    .modal-backdrop:not(.show) {
        display: none !important;
        pointer-events: none !important;
    }
}

#deleteReviewModal .modal-content {
    border-radius: 0.75rem;
}

#deleteReviewModal .modal-header {
    padding: 1.25rem 1.25rem 0.75rem;
}

#deleteReviewModal .modal-body {
    padding: 0.75rem 1.25rem;
}

#deleteReviewModal .modal-footer {
    padding: 0.75rem 1.25rem 1.25rem;
}

@media (max-width: 575.98px) {
    #deleteReviewModal .modal-dialog {
        margin: 0.75rem;
    }
    
    #deleteReviewModal .modal-header {
        padding: 1rem 1rem 0.5rem;
    }
    
    #deleteReviewModal .modal-header .modal-title {
        font-size: 1.1rem;
    }
    
    #deleteReviewModal .modal-body {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    #deleteReviewModal .modal-footer {
        padding: 0.5rem 1rem 1rem;
    }
    
    #deleteReviewModal .modal-footer .btn {
        font-size: 0.9rem;
        padding: 0.625rem 1rem;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1;
    }
    
    #deleteReviewModal .modal-footer .btn i {
        font-size: 0.85rem;
    }
    
    #deleteReviewModal .modal-footer .d-flex {
        gap: 0.5rem !important;
    }
    
    #deleteReviewModal .modal-footer form {
        flex: 1;
    }
    
    @media (min-width: 576px) {
        #deleteReviewModal .modal-footer .btn {
            flex: 0 0 auto;
        }
        
        #deleteReviewModal .modal-footer form {
            flex: 0 0 auto;
        }
    }
}
</style>
@endpush


