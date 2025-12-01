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
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 200px; max-width: 250px;">Étudiant</th>
                                    <th style="min-width: 200px; max-width: 300px;">Cours</th>
                                    <th style="width: 120px; text-align: center;">Note</th>
                                    <th style="min-width: 200px; max-width: 350px;">Commentaire</th>
                                    <th style="width: 120px; text-align: center;">Statut</th>
                                    <th style="width: 120px; text-align: center;">Date</th>
                                    <th style="width: 150px;" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reviews as $review)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2" style="flex-shrink: 0;">
                                                <img src="{{ $review->user->avatar_url }}" 
                                                     alt="{{ $review->user->name }}" 
                                                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; display: block;">
                                            </div>
                                            <div style="min-width: 0; flex: 1;">
                                                <div class="fw-semibold text-truncate" style="max-width: 200px;" title="{{ $review->user->name }}">{{ $review->user->name }}</div>
                                                <small class="text-muted text-truncate d-block" style="max-width: 200px;" title="{{ $review->user->email }}">{{ $review->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="min-width: 0;">
                                            <div class="fw-semibold text-truncate" style="max-width: 250px;" title="{{ $review->course->title }}">{{ $review->course->title }}</div>
                                            <small class="text-muted text-truncate d-block" style="max-width: 250px;" title="Par {{ $review->course->instructor->name }}">Par {{ $review->course->instructor->name }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.95rem; padding: 0.35rem 0.65rem; min-width: 50px;">
                                                {{ $review->rating }}/5
                                            </span>
                                            <div class="text-warning" style="font-size: 0.75rem; line-height: 1;">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($review->comment)
                                            <div class="comment-cell" style="max-width: 300px;">
                                                <div class="comment-text" title="{{ $review->comment }}">
                                                    {{ $review->comment }}
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">Aucun commentaire</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="white-space: nowrap;">
                                            @if($review->is_approved)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Approuvé
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i>En attente
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $review->created_at->format('d/m/Y') }}<br>
                                            {{ $review->created_at->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end align-items-center gap-2" style="flex-wrap: nowrap;">
                                            @if(!$review->is_approved)
                                                <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="d-inline m-0" style="flex-shrink: 0;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-link text-success p-0 action-btn" title="Approuver" style="border: none; background: none; min-width: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.reviews.reject', $review) }}" method="POST" class="d-inline m-0" style="flex-shrink: 0;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-link text-warning p-0 action-btn" title="Rejeter" style="border: none; background: none; min-width: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-times-circle" style="font-size: 1.2rem;"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <button type="button" 
                                                    class="btn btn-link text-danger p-0 action-btn" 
                                                    title="Supprimer" 
                                                    style="border: none; background: none; min-width: 32px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;"
                                                    onclick="openDeleteModal({{ $review->id }}, '{{ addslashes($review->user->name) }}', '{{ addslashes(Str::limit($review->course->title, 50)) }}')">
                                                <i class="fas fa-trash-alt" style="font-size: 1.2rem;"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="admin-empty-state">
                                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                            <p class="mb-1">Aucun avis trouvé</p>
                                            <p class="text-muted mb-0">Les avis des étudiants apparaîtront ici.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
/* Styles pour éviter les débordements dans les colonnes */
.table td {
    vertical-align: middle;
    overflow: hidden;
    position: relative;
}

/* S'assurer que les éléments cliquables sont accessibles */
.table td a,
.table td button,
.table td .btn-link,
.table td form {
    position: relative;
    z-index: 1;
    pointer-events: auto;
}

/* S'assurer que la colonne Actions n'est jamais coupée */
.table td:last-child {
    overflow: visible !important;
}

/* Colonne Étudiant */
.table td:first-child {
    max-width: 250px;
    min-width: 200px;
}

/* Colonne Cours */
.table td:nth-child(2) {
    max-width: 300px;
    min-width: 200px;
}

/* Colonne Note */
.table td:nth-child(3) {
    text-align: center;
    white-space: nowrap;
}

/* Colonne Commentaire */
.table td:nth-child(4) {
    max-width: 350px;
    min-width: 200px;
}

/* Colonne Statut */
.table td:nth-child(5) {
    white-space: nowrap;
    text-align: center;
}

/* Colonne Date */
.table td:nth-child(6) {
    white-space: nowrap;
    text-align: center;
}

/* Colonne Actions */
.table td:last-child {
    white-space: nowrap;
    text-align: right;
    overflow: visible !important;
    min-width: 120px;
}

.table th:last-child {
    overflow: visible !important;
    min-width: 120px;
}

/* Styles pour les boutons d'action */
.table td .action-btn {
    flex-shrink: 0 !important;
    min-width: 32px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.table td form {
    flex-shrink: 0 !important;
    display: inline-block !important;
    margin: 0 !important;
}

/* Styles pour les icônes d'action */
.table td .btn-link {
    transition: all 0.2s ease;
    cursor: pointer;
}

.table td .btn-link:hover {
    opacity: 0.7;
    transform: scale(1.1);
}

.table td .btn-link:active {
    transform: scale(0.95);
}

/* Responsive - ajustements pour mobile */
@media (max-width: 991.98px) {
    .table td:first-child {
        max-width: 180px;
        min-width: 150px;
    }
    
    .table td:nth-child(2) {
        max-width: 200px;
        min-width: 150px;
    }
    
    .table td:nth-child(4) {
        max-width: 200px;
        min-width: 150px;
    }
}

@media (max-width: 767.98px) {
    .table td:first-child {
        max-width: 150px;
        min-width: 120px;
    }
    
    .table td:nth-child(2) {
        max-width: 150px;
        min-width: 120px;
    }
    
    .table td:nth-child(4) {
        max-width: 150px;
        min-width: 120px;
    }
}

/* Styles pour l'affichage des commentaires */
.comment-cell {
    position: relative;
}

.comment-text {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.5;
    max-height: 4.5em;
    word-wrap: break-word;
    word-break: break-word;
}

.comment-text:hover {
    cursor: help;
}

@media (max-width: 991.98px) {
    .comment-cell {
        max-width: 250px !important;
    }
    
    .comment-text {
        -webkit-line-clamp: 2;
        max-height: 3em;
        font-size: 0.9rem;
    }
}

@media (max-width: 767.98px) {
    .comment-cell {
        max-width: 200px !important;
    }
    
    .comment-text {
        -webkit-line-clamp: 2;
        max-height: 3em;
        font-size: 0.85rem;
    }
}

@media (max-width: 575.98px) {
    .comment-cell {
        max-width: 150px !important;
    }
    
    .comment-text {
        -webkit-line-clamp: 2;
        max-height: 3em;
        font-size: 0.8rem;
    }
}

/* Ajustement de la taille des icônes sur mobile et tablette */
@media (max-width: 991.98px) {
    .table td .btn-link i {
        font-size: 1.1rem !important;
    }
    
    .table td .d-flex.gap-2 {
        gap: 0.5rem !important;
    }
}

@media (max-width: 767.98px) {
    .table td:last-child {
        min-width: 100px !important;
        width: auto !important;
        overflow: visible !important;
    }
    
    .table th:last-child {
        min-width: 100px !important;
        width: auto !important;
    }
    
    .table td .btn-link {
        min-width: 32px !important;
        width: auto !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0.25rem !important;
    }
    
    .table td .btn-link i {
        font-size: 1rem !important;
    }
    
    .table td .d-flex.gap-2 {
        gap: 0.3rem !important;
        flex-wrap: nowrap !important;
    }
    
    .table td form {
        display: inline-block !important;
        margin: 0 !important;
    }
}

@media (max-width: 575.98px) {
    .table td:last-child {
        min-width: 90px !important;
        width: auto !important;
        overflow: visible !important;
    }
    
    .table th:last-child {
        min-width: 90px !important;
        width: auto !important;
    }
    
    .table td .btn-link {
        min-width: 28px !important;
        width: auto !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0.2rem !important;
    }
    
    .table td .btn-link i {
        font-size: 0.9rem !important;
    }
    
    .table td .d-flex.gap-2 {
        gap: 0.25rem !important;
        flex-wrap: nowrap !important;
    }
    
    .table td form {
        display: inline-block !important;
        margin: 0 !important;
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

