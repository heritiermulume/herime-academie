@extends('layouts.admin')

@section('title', 'Gestion des catégories')
@section('admin-title', 'Gestion des catégories')
@section('admin-subtitle', 'Structurez vos contenus par thématique et contrôlez leur visibilité')
@section('admin-actions')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal" onclick="resetCategoryForm()">
        <i class="fas fa-plus-circle me-2"></i>Nouvelle catégorie
    </button>
@endsection

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
                    <!-- Filtres -->
                    <x-admin.search-panel
                        action="{{ route('admin.categories') }}"
                        formId="categoriesFilterForm"
                        filtersId="categoriesFilters"
                        :hasFilters="true"
                        :searchValue="request('search')"
                        placeholder="Rechercher une catégorie..."
                    >
                        <x-slot:filters>
                            <div class="admin-form-grid admin-form-grid--two mb-3">
                                <div>
                                    <label class="form-label fw-semibold">Statut</label>
                                    <select class="form-select" name="status">
                                        <option value="">Tous les statuts</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actives</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactives</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <span class="text-muted small">Personnalisez l’affichage selon vos besoins.</span>
                                <a href="{{ route('admin.categories') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo me-2"></i>Réinitialiser
                                </a>
                            </div>
                        </x-slot:filters>
                    </x-admin.search-panel>

                    <!-- Filtres actifs -->
                    @if(request('search') || request('status'))
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-filter me-2"></i>
                                    <strong>Filtres actifs :</strong>
                                    @if(request('search'))
                                        <span class="badge bg-primary me-2">Recherche: "{{ request('search') }}"</span>
                                    @endif
                                    @if(request('status'))
                                        <span class="badge bg-info me-2">Statut: {{ request('status') === 'active' ? 'Actives' : 'Inactives' }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('admin.categories') }}" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-times me-1"></i>Effacer tous les filtres
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Statistiques rapides -->
                    <div class="admin-stats-grid mb-4">
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Total</p>
                            <p class="admin-stat-card__value">{{ $categories->total() }}</p>
                            <p class="admin-stat-card__muted">Catégories enregistrées</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Actives</p>
                            <p class="admin-stat-card__value">{{ $categories->where('is_active', true)->count() }}</p>
                            <p class="admin-stat-card__muted">Visibles côté plateforme</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Inactives</p>
                            <p class="admin-stat-card__value">{{ $categories->where('is_active', false)->count() }}</p>
                            <p class="admin-stat-card__muted">En attente d’activation</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Contenus associés</p>
                            <p class="admin-stat-card__value">{{ $categories->sum('courses_count') }}</p>
                            <p class="admin-stat-card__muted">Nombre total de contenus liés</p>
                        </div>
                    </div>

                    <!-- Tableau des catégories -->
                    <div class="admin-table mt-4">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Catégorie</th>
                                        <th>Couleur / Icône</th>
                                        <th>Contenus associés</th>
                                        <th>Statut</th>
                                        <th>Date de création</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categories as $category)
                                        <tr>
                                            <td style="min-width: 250px;">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="category-icon-small" style="background: {{ $category->color ?? '#003366' }};">
                                                        @if($category->icon)
                                                            <i class="{{ $category->icon }}"></i>
                                                        @else
                                                            <i class="fas fa-tag"></i>
                                                        @endif
                                                    </div>
                                                    <div style="min-width: 0; flex: 1;">
                                                        <div class="fw-semibold text-truncate d-block" title="{{ $category->name }}">{{ $category->name }}</div>
                                                        <div class="text-muted small text-truncate d-block" title="{{ $category->description ?? 'Aucune description' }}">
                                                            {{ $category->description ? Str::limit($category->description, 50) : 'Aucune description' }}
                                                        </div>
                                                        <div class="text-muted small mt-1">
                                                            <span class="badge bg-light text-dark">#{{ $category->slug }}</span>
                                                            @if($category->sort_order)
                                                                <span class="badge bg-light text-dark">Ordre: {{ $category->sort_order }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="color-preview" style="background-color: {{ $category->color ?? '#003366' }}; width: 24px; height: 24px; border-radius: 4px; border: 1px solid #dee2e6;"></div>
                                                    @if($category->icon)
                                                        <span class="text-muted small">{{ Str::limit($category->icon, 20) }}</span>
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="admin-chip admin-chip--info">{{ $category->courses_count ?? 0 }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted small">{{ $category->created_at->format('d/m/Y') }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="editCategory({{ $category->id }})" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteCategory({{ $category->id }})" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Aucune catégorie trouvée</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <x-admin.pagination :paginator="$categories" />
        </div>
    </section>

<!-- Modal de création/édition de catégorie -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm" method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom de la catégorie *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="slug" class="form-label">Slug</label>
                                <input type="text" class="form-control" id="slug" name="slug" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="color" class="form-label">Couleur</label>
                                <input type="color" class="form-control form-control-color" id="color" name="color" value="#003366">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="icon" class="form-label">Icône Font Awesome</label>
                                <input type="text" class="form-control" id="icon" name="icon" placeholder="fas fa-code">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Ordre d'affichage</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">
                                        Catégorie active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="categoryFormSubmit">
                        <span class="submit-label-create"><i class="fas fa-plus me-2"></i>Créer la catégorie</span>
                        <span class="submit-label-update d-none"><i class="fas fa-save me-2"></i>Enregistrer les modifications</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cette catégorie ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let categoryIdToDelete = null;

// Génération automatique du slug
document.getElementById('name').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
    document.getElementById('slug').value = slug;
});

const categoriesFilterForm = document.getElementById('categoriesFilterForm');
const categoriesFiltersOffcanvas = document.getElementById('categoriesFilters');

if (categoriesFilterForm) {
    categoriesFilterForm.addEventListener('submit', () => {
        if (categoriesFiltersOffcanvas) {
            const instance = bootstrap.Offcanvas.getInstance(categoriesFiltersOffcanvas);
            if (instance) {
                instance.hide();
            }
        }
    });
}

const categoriesSearchInput = document.querySelector('#categoriesFilterForm input[name=\"search\"]');
if (categoriesSearchInput) {
    let searchTimeout;
    categoriesSearchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            categoriesFilterForm?.submit();
        }, 500);
    });
}

function resetCategoryForm() {
    // Réinitialiser le formulaire
    document.getElementById('categoryForm').reset();
    document.getElementById('color').value = '#003366';
    document.getElementById('is_active').checked = true;
    
    // Changer l'action du formulaire pour la création
    const form = document.getElementById('categoryForm');
    form.action = '{{ route("admin.categories.store") }}';
    
    // Retirer le champ _method si présent
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) {
        methodInput.remove();
    }
    
    // Changer le titre du modal
    document.querySelector('#createCategoryModal .modal-title').textContent = 'Nouvelle catégorie';
    
    // Mettre à jour le libellé du bouton
    toggleCategorySubmitLabels('create');
}

function editCategory(categoryId) {
    // Charger les données de la catégorie
    fetch(`/admin/categories/${categoryId}/edit`)
        .then(response => response.json())
        .then(category => {
            // Remplir le formulaire avec les données de la catégorie
            document.getElementById('name').value = category.name;
            document.getElementById('slug').value = category.slug;
            document.getElementById('description').value = category.description || '';
            document.getElementById('color').value = category.color || '#003366';
            document.getElementById('icon').value = category.icon || '';
            document.getElementById('is_active').checked = category.is_active;
            
            // Changer l'action du formulaire pour la mise à jour
            const form = document.getElementById('categoryForm');
            form.action = `/admin/categories/${categoryId}`;
            
            // Ajouter ou mettre à jour le champ _method pour l'update
            let methodInput = form.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                form.prepend(methodInput);
            }
            methodInput.value = 'PUT';
            
            // Changer le titre du modal
            document.querySelector('#createCategoryModal .modal-title').textContent = 'Modifier la catégorie';
            toggleCategorySubmitLabels('edit');
            
            // Ouvrir le modal
            new bootstrap.Modal(document.getElementById('createCategoryModal')).show();
        })
        .catch(error => {
            console.error('Erreur lors du chargement de la catégorie:', error);
            alert('Erreur lors du chargement de la catégorie');
        });
}

function deleteCategory(categoryId) {
    categoryIdToDelete = categoryId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (categoryIdToDelete) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/categories/${categoryIdToDelete}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
});

function toggleCategorySubmitLabels(mode) {
    const submitButton = document.getElementById('categoryFormSubmit');
    if (!submitButton) return;
    const createLabel = submitButton.querySelector('.submit-label-create');
    const updateLabel = submitButton.querySelector('.submit-label-update');
    
    if (mode === 'edit') {
        createLabel?.classList.add('d-none');
        updateLabel?.classList.remove('d-none');
    } else {
        createLabel?.classList.remove('d-none');
        updateLabel?.classList.add('d-none');
    }
}

</script>
@endpush

@push('styles')
<style>
.category-icon-small {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1rem;
    flex-shrink: 0;
}

.color-preview {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

    .admin-panel {
        margin-bottom: 1rem;
    }
    
    .admin-panel--main .admin-panel__body {
        padding: 1rem !important;
    }
    
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

    .admin-panel {
        margin-bottom: 0.75rem;
    }
    
    .admin-panel--main .admin-panel__body {
        padding: 0.75rem !important;
    }
    
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
}
</style>
@endpush
