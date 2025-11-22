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
                            <p class="admin-stat-card__label">Cours associés</p>
                            <p class="admin-stat-card__value">{{ $categories->sum('courses_count') }}</p>
                            <p class="admin-stat-card__muted">Nombre total de cours liés</p>
                        </div>
                    </div>

                    <!-- Grille des catégories -->
                    <div class="row g-3">
                        @forelse($categories as $category)
                        <div class="col-md-6 col-lg-4">
                            <div class="category-card-modern h-100">
                                <div class="category-card-modern__badge" style="--category-color: {{ $category->color ?? '#003366' }};">
                                    <span>{{ strtoupper(Str::substr($category->name, 0, 1)) }}</span>
                                </div>
                                <div class="category-card-modern__header">
                                    <div class="category-card-modern__icon" style="background: {{ $category->color ?? '#003366' }};">
                                        @if($category->icon)
                                            <i class="{{ $category->icon }}"></i>
                                        @else
                                            <i class="fas fa-tag"></i>
                                        @endif
                                    </div>
                                    <div class="category-card-modern__title">
                                        <h5>{{ $category->name }}</h5>
                                        <p>{{ $category->description ?? 'Aucune description pour cette catégorie.' }}</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="category-card-modern__menu" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="editCategory({{ $category->id }})">
                                                    <i class="fas fa-edit me-2"></i>Modifier
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="deleteCategory({{ $category->id }})">
                                                    <i class="fas fa-trash me-2"></i>Supprimer
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="category-card-modern__metrics">
                                    <div>
                                        <span class="category-card-modern__metric-label">Cours associés</span>
                                        <span class="category-card-modern__metric-value">{{ $category->courses_count ?? 0 }}</span>
                                    </div>
                                    <div>
                                        <span class="category-card-modern__metric-label">Statut</span>
                                        <span class="category-card-modern__metric-badge {{ $category->is_active ? 'is-active' : 'is-inactive' }}">
                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="category-card-modern__metric-label">Visibilité</span>
                                        <span class="category-card-modern__metric-value">
                                            @if($category->is_active)
                                                <i class="fas fa-eye text-success"></i>
                                            @else
                                                <i class="fas fa-eye-slash text-muted"></i>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                @if($category->image)
                                <div class="category-card-modern__media">
                                    <img src="{{ \App\Helpers\FileHelper::url($category->image, 'categories') }}" alt="{{ $category->name }}">
                                </div>
                                @endif
                                <div class="category-card-modern__footer">
                                    <div>
                                        <span class="category-card-modern__footer-label">Créée le</span>
                                        <strong>{{ $category->created_at->format('d/m/Y') }}</strong>
                                    </div>
                                    <div class="category-card-modern__tags">
                                        <span class="badge rounded-pill text-bg-light" title="#{{ $category->slug }}">
                                            #{{ Str::limit($category->slug, 15) }}
                                        </span>
                                        <span class="badge rounded-pill text-bg-light">
                                            Ordre : {{ $category->sort_order ?? 0 }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucune catégorie trouvée</p>
                            </div>
                        </div>
                        @endforelse
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
.category-card-modern {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
    background: #ffffff;
    border-radius: 1.25rem;
    padding: 1.125rem;
    border: 1px solid rgba(15, 23, 42, 0.08);
    box-shadow: 0 22px 45px -30px rgba(15, 23, 42, 0.35);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    overflow: hidden;
    height: 100%;
}
.category-card-modern::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(15, 23, 42, 0) 0%, rgba(15, 23, 42, 0.06) 100%);
    pointer-events: none;
}
.category-card-modern:hover {
    transform: translateY(-6px);
    box-shadow: 0 30px 60px -30px rgba(15, 23, 42, 0.45);
}
.category-card-modern__badge {
    position: absolute;
    top: -28px;
    right: -28px;
    width: 100px;
    height: 100px;
    background: radial-gradient(circle at center, var(--category-color) 0%, rgba(15, 23, 42, 0) 70%);
    opacity: 0.22;
    pointer-events: none;
}
.category-card-modern__badge span {
    position: absolute;
    bottom: 28px;
    right: 35px;
    font-size: 2.4rem;
    font-weight: 800;
    color: rgba(15, 23, 42, 0.08);
    letter-spacing: -0.04em;
}
.category-card-modern__header {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    flex-wrap: nowrap;
}
.category-card-modern__icon {
    flex: 0 0 44px;
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1.15rem;
    box-shadow: 0 12px 25px -18px rgba(15, 23, 42, 0.65);
}
.category-card-modern__title {
    flex: 1 1 auto;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}
.category-card-modern__title h5 {
    margin: 0;
    font-weight: 700;
    color: #0f172a;
    font-size: 1rem;
    line-height: 1.3;
    word-wrap: break-word;
}
.category-card-modern__title p {
    margin: 0;
    color: #64748b;
    font-size: 0.8rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}
.category-card-modern__menu {
    border: none;
    background: rgba(148, 163, 184, 0.18);
    color: #475569;
    width: 32px;
    height: 32px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease, color 0.2s ease;
    margin-left: auto;
    flex-shrink: 0;
    font-size: 0.875rem;
}
.category-card-modern__menu:hover {
    background: rgba(15, 23, 42, 0.1);
    color: #1f2937;
}
.category-card-modern__metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 0.65rem;
    padding: 0.65rem;
    border-radius: 1rem;
    background: rgba(241, 245, 249, 0.6);
}
.category-card-modern__metrics > div {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    padding: 0.65rem 0.6rem;
    background: rgba(148, 163, 184, 0.08);
    border-radius: 0.9rem;
    border: 1px solid rgba(148, 163, 184, 0.12);
    min-height: 100%;
}
.category-card-modern__metrics > div:nth-child(odd) {
    background: rgba(148, 163, 184, 0.12);
}
.category-card-modern__metric-label {
    display: block;
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #94a3b8;
    letter-spacing: 0.08em;
    margin-bottom: 0.25rem;
}
.category-card-modern__metric-value {
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
}
.category-card-modern__metric-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.78rem;
    font-weight: 600;
    border-radius: 999px;
    padding: 0.35rem 0.85rem;
    background: rgba(15, 23, 42, 0.06);
    color: #0f172a;
}
.category-card-modern__metric-badge.is-active {
    background: rgba(34, 197, 94, 0.14);
    color: #15803d;
}
.category-card-modern__metric-badge.is-inactive {
    background: rgba(148, 163, 184, 0.18);
    color: #475569;
}
.category-card-modern__media {
    border-radius: 1.2rem;
    overflow: hidden;
    border: 1px solid rgba(148, 163, 184, 0.22);
    max-height: 120px;
}
.category-card-modern__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.category-card-modern__footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px dashed rgba(148, 163, 184, 0.4);
    flex-wrap: wrap;
}
.category-card-modern__footer > div:first-child {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    min-width: 0;
    flex-shrink: 0;
}
.category-card-modern__footer-label {
    font-size: 0.6rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    line-height: 1.1;
    display: block;
}
.category-card-modern__footer strong {
    font-size: 0.65rem;
    color: #334155;
    font-weight: 600;
    line-height: 1.2;
    display: block;
}
.category-card-modern__tags {
    display: flex;
    gap: 0.3rem;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    flex: 1 1 auto;
    min-width: 0;
}
.category-card-modern__tags .badge {
    background: rgba(15, 23, 42, 0.05);
    color: #334155;
    border: 1px solid rgba(15, 23, 42, 0.08);
    font-weight: 500;
    font-size: 0.6rem;
    padding: 0.15rem 0.4rem;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100px;
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
    
    .admin-panel__body .row.mb-3 {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-panel__body .row.mt-2 {
        margin-top: 0.375rem !important;
    }
    
    .category-card-modern {
        padding: 1.25rem;
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
    
    .admin-panel__body .row.mb-3 {
        margin-bottom: 0.375rem !important;
    }
    
    .admin-panel__body .row.mt-2 {
        margin-top: 0.375rem !important;
    }
    
    .category-card-modern {
        padding: 1rem;
    }
    
    .category-card-modern__metrics {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .category-card-modern__badge {
        width: 90px;
        height: 90px;
    }
    
    .category-card-modern__badge span {
        font-size: 2.2rem;
    }
    
    .category-card-modern__footer {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
@endpush
