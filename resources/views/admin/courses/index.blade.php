@extends('layouts.admin')

@section('title', 'Gestion des cours')
@section('admin-title', 'Gestion des cours')
@section('admin-subtitle', 'Pilotez l’ensemble des formations disponibles sur la plateforme')
@section('admin-actions')
    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-2"></i>Nouveau cours
    </a>
@endsection

@push('modals')
    <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteCourseModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Supprimer le cours
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Êtes-vous sûr de vouloir supprimer le cours <span id="courseDeleteName" class="fw-semibold"></span> ?</p>
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

.mobile-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.mobile-actions .mobile-action {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    cursor: pointer;
}

.mobile-actions .mobile-action i {
    font-size: 1.05rem;
}

.mobile-actions .mobile-action span {
    font-size: 0.85rem;
}

    @media (max-width: 992px) {
        .admin-panel__body {
            padding: 1.25rem;
        }

        .admin-form-grid.admin-form-grid--two {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem 1.25rem;
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

    @media (max-width: 768px) {
        .admin-panel {
            margin-bottom: 1.25rem;
        }

        .admin-panel__body {
            padding: 0;
            overflow: hidden;
        }

        .admin-stats-grid .admin-stat-card__value {
            font-size: 1.55rem;
        }

        .admin-stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .admin-form-grid.admin-form-grid--two {
            grid-template-columns: 1fr;
        }

        .admin-form-grid.admin-form-grid--two .form-label {
            font-size: 0.85rem;
        }

        .admin-form-grid.admin-form-grid--two .form-select,
        .admin-form-grid.admin-form-grid--two .form-control {
            font-size: 0.85rem;
            padding: 0.45rem 0.65rem;
        }

    }

    @media (max-width: 576px) {
        .admin-panel__body {
            padding: 0;
            overflow: hidden;
        }

        .admin-stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }

        .admin-stats-grid .admin-stat-card__value {
            font-size: 1.35rem;
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

        .admin-form-grid.admin-form-grid--two .form-select,
        .admin-form-grid.admin-form-grid--two .form-control {
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
        }

        .admin-table .table-responsive {
            margin: 0;
        }

        .admin-table .admin-actions {
            flex-wrap: nowrap;
            gap: 0.35rem;
        }

        .admin-table .admin-actions .btn {
            flex: 0 0 auto;
        }

        .mobile-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 576px) {
        .admin-panel__body {
            padding: 0.85rem;
        }

        .admin-stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }

        .admin-table .table {
            font-size: 0.9rem;
        }

        .admin-table img {
            width: 44px;
            height: 32px;
        }
    }
</style>
@endpush

@section('admin-content')
    <section class="admin-panel">
        <div class="admin-panel__body">
            <div class="admin-stats-grid mb-4">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Total</p>
                    <p class="admin-stat-card__value">{{ $stats['total'] }}</p>
                    <p class="admin-stat-card__muted">Cours enregistrés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Publiés</p>
                    <p class="admin-stat-card__value">{{ $stats['published'] }}</p>
                    <p class="admin-stat-card__muted">Visibles côté étudiant</p>
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
                    <p class="admin-stat-card__muted">Cours premium</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Résultats</p>
                    <p class="admin-stat-card__value">{{ $courses->total() }}</p>
                    <p class="admin-stat-card__muted">Correspondant à vos filtres</p>
                </div>
            </div>

            <x-admin.search-panel
                :action="route('admin.courses')"
                formId="coursesFilterForm"
                filtersId="coursesFilters"
                :hasFilters="true"
                searchName="search"
                :searchValue="request('search')"
                placeholder="Rechercher un cours..."
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
                            <label class="form-label fw-semibold">Formateur</label>
                            <select class="form-select" name="instructor">
                                <option value="">Tous les formateurs</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}" {{ request('instructor') == $instructor->id ? 'selected' : '' }}>
                                        {{ $instructor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Ajustez les filtres puis appliquez-les.</span>
                        <a href="{{ route('admin.courses') }}" class="btn btn-outline-secondary reset-filters-btn">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            @if(request()->hasAny(['search', 'category', 'status', 'instructor']))
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
                        @if(request('instructor'))
                            | Formateur : <span class="fw-semibold">{{ $instructors->firstWhere('id', request('instructor'))->name ?? 'Inconnu' }}</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.courses') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-times me-1"></i>Effacer
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
                                        Cours
                                        @if(request('sort') == 'title')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Formateur</th>
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
                                <th class="text-center d-none d-md-table-cell" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($courses as $course)
                                <tr>
                                    <td style="min-width: 280px;">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $course->thumbnail_url ?: 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=120&q=80' }}" alt="{{ $course->title }}" class="rounded" style="width: 64px; height: 48px; object-fit: cover;">
                                            <div>
                                                <a href="{{ route('admin.courses.show', $course) }}" class="fw-semibold text-decoration-none text-dark">
                                                    {{ $course->title }}
                                                </a>
                                                <div class="text-muted small">{{ Str::limit($course->subtitle, 60) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="admin-chip">
                                            <i class="fas fa-user"></i>{{ $course->instructor->name ?? 'Non assigné' }}
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
                                    <td class="text-center align-top">
                                        <div class="d-none d-md-inline-block">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-light" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-light" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-light text-danger" title="Supprimer"
                                                        data-course-id="{{ $course->id }}"
                                                        data-course-title="{{ $course->title }}"
                                                        onclick="openCourseDeleteModal(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="d-md-none mobile-actions mt-3">
                                            <div class="mobile-action" onclick="window.location.href='{{ route('admin.courses.edit', $course) }}'">
                                                <i class="fas fa-edit"></i>
                                                <span>Modifier</span>
                                            </div>
                                            <div class="mobile-action" onclick="window.location.href='{{ route('admin.courses.show', $course) }}'">
                                                <i class="fas fa-eye"></i>
                                                <span>Voir</span>
                                            </div>
                                            <div class="mobile-action text-danger" data-course-id="{{ $course->id }}"
                                                 data-course-title="{{ $course->title }}"
                                                 onclick="openCourseDeleteModal(this)">
                                                <i class="fas fa-trash"></i>
                                                <span>Supprimer</span>
                                            </div>
                                        </div>
                                        <form id="course-delete-form-{{ $course->id }}" action="{{ route('admin.courses.destroy', $course) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="admin-table__empty">
                                        <i class="fas fa-inbox mb-2 d-block"></i>
                                        Aucun cours trouvé avec ces critères.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-pagination">
                {{ $courses->withQueryString()->onEachSide(1)->links() }}
            </div>
        </div>
    </section>
@endsection