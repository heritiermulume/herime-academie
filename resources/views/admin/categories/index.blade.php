@extends('layouts.app')

@section('title', 'Gestion des catégories - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-tags me-2"></i>Gestion des catégories
                        </h4>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createCategoryModal" onclick="resetCategoryForm()">
                            <i class="fas fa-plus me-1"></i>Nouvelle catégorie
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <form method="GET" id="filterForm">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <input type="text" class="form-control" placeholder="Rechercher une catégorie..." 
                                       id="searchInput" name="search" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="statusFilter" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actives</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactives</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-filter me-1"></i>Filtrer
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('admin.categories') }}" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times"></i>Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>

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
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $categories->total() }}</h5>
                                    <p class="card-text mb-0">Total des catégories</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $categories->where('is_active', true)->count() }}</h5>
                                    <p class="card-text mb-0">Catégories actives</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $categories->where('is_active', false)->count() }}</h5>
                                    <p class="card-text mb-0">Catégories inactives</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $categories->sum('courses_count') }}</h5>
                                    <p class="card-text mb-0">Total des cours</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grille des catégories -->
                    <div class="row">
                        @forelse($categories as $category)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm category-card">
                                <div class="card-header d-flex justify-content-between align-items-center" 
                                     style="background-color: {{ $category->color ?? '#003366' }}; color: white;">
                                    <div class="d-flex align-items-center">
                                        @if($category->icon)
                                            <i class="{{ $category->icon }} me-2"></i>
                                        @endif
                                        <h6 class="mb-0">{{ $category->name }}</h6>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="editCategory({{ $category->id }})">
                                                <i class="fas fa-edit me-2"></i>Modifier
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="deleteCategory({{ $category->id }})">
                                                <i class="fas fa-trash me-2"></i>Supprimer
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text text-muted">{{ $category->description ?? 'Aucune description' }}</p>
                                    
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h5 class="text-primary">{{ $category->courses_count ?? 0 }}</h5>
                                            <small class="text-muted">Cours</small>
                                        </div>
                                        <div class="col-6">
                                            <h5 class="text-success">
                                                @if($category->is_active)
                                                    <i class="fas fa-check-circle text-success"></i>
                                                @else
                                                    <i class="fas fa-times-circle text-danger"></i>
                                                @endif
                                            </h5>
                                            <small class="text-muted">Statut</small>
                                        </div>
                                    </div>
                                    
                                    @if($category->image)
                                    <div class="mt-3">
                                        <img src="{{ Storage::url($category->image) }}" 
                                             alt="{{ $category->name }}" 
                                             class="img-fluid rounded" 
                                             style="max-height: 100px; width: 100%; object-fit: cover;">
                                    </div>
                                    @endif
                                </div>
                                <div class="card-footer bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            Créée le {{ $category->created_at->format('d/m/Y') }}
                                        </small>
                                        <div>
                                            <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
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
                    @if($categories->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $categories->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

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
                    <button type="submit" class="btn btn-primary">Créer la catégorie</button>
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

// Recherche en temps réel avec debounce
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500); // Attendre 500ms après la dernière frappe
});

// Soumission automatique du formulaire lors du changement de statut
document.getElementById('statusFilter').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

function resetCategoryForm() {
    // Réinitialiser le formulaire
    document.getElementById('categoryForm').reset();
    document.getElementById('color').value = '#003366';
    document.getElementById('is_active').checked = true;
    
    // Changer l'action du formulaire pour la création
    const form = document.getElementById('categoryForm');
    form.action = '{{ route("admin.categories.store") }}';
    
    // Changer le titre du modal
    document.querySelector('#createCategoryModal .modal-title').textContent = 'Nouvelle catégorie';
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
            
            // Changer le titre du modal
            document.querySelector('#createCategoryModal .modal-title').textContent = 'Modifier la catégorie';
            
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

// Les fonctions applyFilters et resetFilters ne sont plus nécessaires
// car nous utilisons maintenant un formulaire avec soumission automatique
</script>
@endpush

@push('styles')
<style>
.card-header {
    background: linear-gradient(135deg, #003366 0%, #004080 100%);
}

.category-card {
    transition: transform 0.2s ease-in-out;
}

.category-card:hover {
    transform: translateY(-2px);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #003366;
}

.form-control-color {
    width: 100%;
    height: 38px;
}

.badge {
    font-size: 0.75em;
}
</style>
@endpush
