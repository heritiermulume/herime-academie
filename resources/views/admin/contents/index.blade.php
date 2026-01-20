@extends('layouts.admin')

@section('title', 'Gestion des contenus')
@section('admin-title', 'Gestion des contenus')
@section('admin-subtitle', 'Pilotez l’ensemble des formations disponibles sur la plateforme')
@section('admin-actions')
    <a href="{{ route('admin.contents.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-2"></i>Nouveau contenu
    </a>
@endsection

@push('modals')
    <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteCourseModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Supprimer le contenu
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Êtes-vous sûr de vouloir supprimer le contenu <span id="courseDeleteName" class="fw-semibold"></span> ?</p>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Cette action est irréversible et supprimera toutes les informations associées.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmCourseDelete">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script>
    let courseDeleteModal = null;
    let courseFormToSubmit = null;
    let courseTitleToDelete = '';

    function openCourseDeleteModal(button) {
        const courseId = button.getAttribute('data-course-id');
        const courseTitle = button.getAttribute('data-course-title');
        const nameSpan = document.getElementById('courseDeleteName');
        const form = document.getElementById(`course-delete-form-${courseId}`);

        if (!courseId || !form) return;

        courseFormToSubmit = form;
        courseTitleToDelete = courseTitle ?? '';

        if (nameSpan) {
            nameSpan.textContent = courseTitleToDelete;
        }

        const modalElement = document.getElementById('deleteCourseModal');

        if (!modalElement) {
            console.error('Modal de suppression introuvable dans le DOM.');
            return;
        }

        if (!window.bootstrap || !window.bootstrap.Modal) {
            console.error('Bootstrap Modal n\'est pas chargé. Veuillez vérifier l\'inclusion de bootstrap.bundle.min.js.');
            return;
        }

        if (!courseDeleteModal) {
            courseDeleteModal = new window.bootstrap.Modal(modalElement);

            const confirmBtn = document.getElementById('confirmCourseDelete');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function () {
                    if (courseFormToSubmit) {
                        courseDeleteModal.hide();
                        courseFormToSubmit.submit();
                    }
                });
            }
        }

        courseDeleteModal.show();
    }


</script>
@endpush

@push('styles')
<style>
    .admin-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.25rem;
    }

    .course-actions-btn--mobile {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.75rem !important;
        line-height: 1.2;
    }

    .course-actions-btn--mobile i {
        font-size: 0.7rem !important;
    }

    /* Styles de base pour tous les dropdowns */
    .dropdown,
    .dropup {
        position: relative;
    }

    /* Menu desktop - dropdown pour première ligne (vers le bas) */
    .dropdown.d-none.d-md-block .dropdown-menu {
        margin-top: 0.25rem;
        top: 100%;
        z-index: 1050 !important;
    }

    /* Flèche pour dropdown desktop première ligne (menu vers le bas) */
    .dropdown.d-none.d-md-block .dropdown-menu::before {
        content: '';
        position: absolute;
        top: -5px;
        right: 12px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-bottom: 5px solid #fff;
        z-index: 1001;
    }

    .dropdown.d-none.d-md-block .dropdown-menu::after {
        content: '';
        position: absolute;
        top: -6px;
        right: 12px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-bottom: 6px solid rgba(0, 0, 0, 0.175);
        z-index: 1000;
    }

    /* Menu desktop - dropup pour autres lignes (vers le haut) */
    .dropup.d-none.d-md-block .dropdown-menu {
        margin-bottom: 0.25rem;
        bottom: 100%;
        top: auto;
        z-index: 1050 !important;
    }

    /* Flèche pour dropup desktop (menu vers le haut) */
    .dropup.d-none.d-md-block .dropdown-menu::before {
        content: '';
        position: absolute;
        bottom: -5px;
        right: 12px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #fff;
        z-index: 1001;
    }

    .dropup.d-none.d-md-block .dropdown-menu::after {
        content: '';
        position: absolute;
        bottom: -6px;
        right: 12px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid rgba(0, 0, 0, 0.175);
        z-index: 1000;
    }

    /* Styles pour mobile */
    @media (max-width: 768px) {
        /* Réduire la taille du bouton sur mobile */
        .course-actions-btn--mobile {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.75rem !important;
        }

        .course-actions-btn--mobile i {
            font-size: 0.7rem !important;
        }

        /* Menu avec z-index élevé pour s'afficher au-dessus */
        .dropdown-menu,
        .dropup .dropdown-menu {
            z-index: 1050 !important;
        }

        /* Menu vers le haut pour dropup */
        .dropup .dropdown-menu {
            bottom: 100%;
            top: auto;
            margin-bottom: 0.25rem;
        }

        /* Flèche pour dropup (mobile - menu vers le haut) */
        .dropup .dropdown-menu::before {
            content: '';
            position: absolute;
            bottom: -5px;
            right: 12px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #fff;
            z-index: 1001;
        }

        .dropup .dropdown-menu::after {
            content: '';
            position: absolute;
            bottom: -6px;
            right: 12px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 6px solid rgba(0, 0, 0, 0.175);
            z-index: 1000;
        }

        /* Menu vers le bas pour dropdown (premier élément) */
        .dropdown.d-md-none .dropdown-menu {
            top: 100%;
            bottom: auto;
            margin-top: 0.25rem;
        }

        /* Flèche pour dropdown mobile (premier élément) */
        .dropdown.d-md-none .dropdown-menu::before {
            content: '';
            position: absolute;
            top: -5px;
            right: 12px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 5px solid #fff;
            z-index: 1001;
        }

        .dropdown.d-md-none .dropdown-menu::after {
            content: '';
            position: absolute;
            top: -6px;
            right: 12px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 6px solid rgba(0, 0, 0, 0.175);
            z-index: 1000;
        }

        /* Réduire la taille des textes et icônes sur mobile */
        .dropdown.d-md-none .dropdown-item,
        .dropup .dropdown-item {
            font-size: 0.8rem !important;
            padding: 0.4rem 0.75rem !important;
        }

        .dropdown.d-md-none .dropdown-item i,
        .dropup .dropdown-item i {
            font-size: 0.75rem !important;
        }

        .dropdown.d-md-none .dropdown-divider,
        .dropup .dropdown-divider {
            margin: 0.3rem 0 !important;
        }
    }


    /* Styles responsives pour les paddings et margins */
    @media (max-width: 991.98px) {
        /* Réduire les paddings et margins sur tablette */
        .admin-panel {
            margin-bottom: 1rem;
        }
        
        /* Padding uniquement pour la première section principale */
        .admin-panel--main .admin-panel__body {
            padding: 1rem !important;
        }
        
        /* Pas de padding pour les autres sections */
        .admin-panel:not(.admin-panel--main) .admin-panel__body {
            padding: 0 !important;
        }
        
        .admin-panel__header {
            padding: 0.5rem 0.75rem;
        }
        
        .admin-panel__header h3 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }
        
        .admin-stats-grid {
            gap: 0.5rem !important;
        }
        
        .admin-stat-card {
            padding: 0.75rem 0.875rem !important;
        }
        
        .admin-panel__body .row.g-4 {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }
        
        .admin-panel__body .row.g-3 {
            --bs-gutter-x: 0.375rem;
            --bs-gutter-y: 0.375rem;
        }
        
        .admin-panel__body .row.mb-4 {
            margin-bottom: 0.5rem !important;
        }
        
        .admin-panel__body .row.mt-2 {
            margin-top: 0.375rem !important;
        }

        .admin-form-grid.admin-form-grid--two {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.75rem 1rem;
        }

        .admin-table .table thead th,
        .admin-table .table tbody td {
            white-space: nowrap;
        }

        .admin-table img {
            width: 56px;
            height: 42px;
        }
    }

    @media (max-width: 767.98px) {
        /* Réduire encore plus les paddings et margins sur mobile */
        .admin-panel {
            margin-bottom: 0.75rem;
        }
        
        /* Padding uniquement pour la première section principale */
        .admin-panel--main .admin-panel__body {
            padding: 0.75rem !important;
        }
        
        /* Pas de padding pour les autres sections */
        .admin-panel:not(.admin-panel--main) .admin-panel__body {
            padding: 0 !important;
        }
        
        .admin-panel__header {
            padding: 0.375rem 0.5rem;
        }
        
        .admin-panel__header h3 {
            font-size: 0.95rem;
            margin-bottom: 0.125rem;
        }
        
        .admin-stats-grid {
            gap: 0.375rem !important;
        }
        
        .admin-stat-card {
            padding: 0.5rem 0.625rem !important;
        }
        
        .admin-panel__body .row.g-4 {
            --bs-gutter-x: 0.375rem;
            --bs-gutter-y: 0.375rem;
        }
        
        .admin-panel__body .row.g-3 {
            --bs-gutter-x: 0.25rem;
            --bs-gutter-y: 0.25rem;
        }
        
        .admin-panel__body .row.mb-4 {
            margin-bottom: 0.5rem !important;
        }
        
        .admin-panel__body .row.mt-2 {
            margin-top: 0.375rem !important;
        }

        .admin-stats-grid .admin-stat-card__value {
            font-size: 1.35rem;
        }

        .admin-stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }

        .admin-form-grid.admin-form-grid--two {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .admin-form-grid.admin-form-grid--two .form-label {
            font-size: 0.85rem;
        }

        .admin-form-grid.admin-form-grid--two .form-select,
        .admin-form-grid.admin-form-grid--two .form-control {
            font-size: 0.85rem;
            padding: 0.45rem 0.65rem;
        }

        .admin-table .table {
            font-size: 0.85rem;
        }

        .admin-table img {
            width: 44px;
            height: 32px;
        }

        .admin-chip {
            font-size: 0.7rem;
        }

        .admin-table .table-responsive {
            margin: 0;
        }
    }
</style>
@endpush

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <div class="admin-stats-grid mb-4">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Total</p>
                    <p class="admin-stat-card__value">{{ $stats['total'] }}</p>
                    <p class="admin-stat-card__muted">Contenus enregistrés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Publiés</p>
                    <p class="admin-stat-card__value">{{ $stats['published'] }}</p>
                    <p class="admin-stat-card__muted">Visibles côté client</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Brouillons</p>
                    <p class="admin-stat-card__value">{{ $stats['draft'] }}</p>
                    <p class="admin-stat-card__muted">À finaliser</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Gratuits</p>
                    <p class="admin-stat-card__value">{{ $stats['free'] }}</p>
                    <p class="admin-stat-card__muted">Accès libre</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Payants</p>
                    <p class="admin-stat-card__value">{{ $stats['paid'] }}</p>
                    <p class="admin-stat-card__muted">Contenus premium</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Résultats</p>
                    <p class="admin-stat-card__value">{{ $courses->total() }}</p>
                    <p class="admin-stat-card__muted">Correspondant à vos filtres</p>
                </div>
            </div>

            <x-admin.search-panel
                :action="route('admin.contents')"
                formId="coursesFilterForm"
                filtersId="coursesFilters"
                :hasFilters="true"
                searchName="search"
                :searchValue="request('search')"
                placeholder="Rechercher un contenu..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Catégorie</label>
                            <select class="form-select" name="category">
                                <option value="">Toutes les catégories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publié</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="free" {{ request('status') == 'free' ? 'selected' : '' }}>Gratuit</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Payant</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Prestataire</label>
                            <select class="form-select" name="provider">
                                <option value="">Tous les prestataires</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}" {{ request('provider') == $provider->id ? 'selected' : '' }}>
                                        {{ $provider->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Ajustez les filtres puis appliquez-les.</span>
                        <a href="{{ route('admin.contents') }}" class="btn btn-outline-secondary reset-filters-btn">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            @if(request()->hasAny(['search', 'category', 'status', 'provider']))
                <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <i class="fas fa-filter me-2"></i>
                        <strong>Filtres actifs</strong>
                        @if(request('search'))
                            | Recherche : <span class="fw-semibold">{{ request('search') }}</span>
                        @endif
                        @if(request('category'))
                            | Catégorie : <span class="fw-semibold">{{ $categories->firstWhere('id', request('category'))->name ?? 'Inconnue' }}</span>
                        @endif
                        @if(request('status'))
                            | Statut : <span class="fw-semibold">{{ ucfirst(request('status')) }}</span>
                        @endif
                        @if(request('provider'))
                            | Prestataire : <span class="fw-semibold">{{ $providers->firstWhere('id', request('provider'))->name ?? 'Inconnu' }}</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.contents') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-times me-1"></i>Effacer tous les filtres
                    </a>
                </div>
            @endif
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__body">
            <div class="admin-table">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 280px;">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'title', 'direction' => request('sort') == 'title' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Contenu
                                        @if(request('sort') == 'title')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Prestataire</th>
                                <th>Catégorie</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'price', 'direction' => request('sort') == 'price' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Prix
                                        @if(request('sort') == 'price')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Statut</th>
                                <th>Vente</th>
                                <th class="text-center d-none d-md-table-cell" style="width: 120px;">Actions</th>
                                <th class="text-center d-md-none" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($courses as $course)
                                <tr>
                                    <td style="min-width: 280px;">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $course->thumbnail_url ?: 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=120&q=80' }}" alt="{{ $course->title }}" class="rounded" style="width: 64px; height: 48px; object-fit: cover;">
                                            <div>
                                                <a href="{{ route('admin.contents.show', $course) }}" class="fw-semibold text-decoration-none text-dark">
                                                    {{ $course->title }}
                                                </a>
                                                <div class="text-muted small">{{ Str::limit($course->subtitle, 60) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="admin-chip">
                                            <i class="fas fa-user"></i>{{ $course->provider->name ?? 'Non assigné' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-chip admin-chip--info">
                                            {{ $course->category->name ?? 'Aucune' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($course->is_free)
                                            <span class="admin-chip admin-chip--success">Gratuit</span>
                                        @else
                                            {{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price ?? 0, $course->currency ?? 'USD') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($course->is_published)
                                            <span class="admin-chip admin-chip--success">Publié</span>
                                        @else
                                            <span class="admin-chip admin-chip--warning">Brouillon</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($course->is_sale_enabled ?? true)
                                            <span class="admin-chip admin-chip--success" title="La vente et l'inscription sont activées">
                                                <i class="fas fa-check-circle me-1"></i>Activée
                                            </span>
                                        @else
                                            <span class="admin-chip admin-chip--secondary" title="La vente et l'inscription sont désactivées">
                                                <i class="fas fa-ban me-1"></i>Désactivée
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('admin.contents.show', $course) }}" class="btn btn-light btn-sm" title="Voir le contenu">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.contents.edit', $course) }}" class="btn btn-primary btn-sm" title="Modifier le contenu">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    data-course-id="{{ $course->id }}"
                                                    data-course-title="{{ $course->title }}"
                                                    onclick="openCourseDeleteModal(this)" 
                                                    title="Supprimer le contenu">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <form id="course-delete-form-{{ $course->id }}" action="{{ route('admin.contents.destroy', $course) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="admin-table__empty">
                                        <i class="fas fa-inbox mb-2 d-block"></i>
                                        Aucun contenu trouvé avec ces critères.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-admin.pagination :paginator="$courses" />
        </div>
    </section>
@endsection