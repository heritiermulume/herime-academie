@extends('layouts.app')

@section('title', 'Gestion des cours - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-graduation-cap me-2"></i>Gestion des cours
                        </h4>
                        <a href="{{ route('admin.courses.create') }}" class="btn btn-light">
                            <i class="fas fa-plus me-1"></i>Nouveau cours
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['total'] }}</h5>
                                    <p class="card-text small">Total</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['published'] }}</h5>
                                    <p class="card-text small">Publiés</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['draft'] }}</h5>
                                    <p class="card-text small">Brouillons</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['free'] }}</h5>
                                    <p class="card-text small">Gratuits</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['paid'] }}</h5>
                                    <p class="card-text small">Payants</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $courses->total() }}</h5>
                                    <p class="card-text small">Résultats</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres et recherche -->
                    <form method="GET" action="{{ route('admin.courses') }}" id="filterForm">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           value="{{ request('search') }}"
                                           placeholder="Rechercher par titre ou description...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="category">
                                    <option value="">Toutes les catégories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publié</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                    <option value="free" {{ request('status') == 'free' ? 'selected' : '' }}>Gratuit</option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Payant</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="instructor">
                                    <option value="">Tous les formateurs</option>
                                    @foreach($instructors as $instructor)
                                        <option value="{{ $instructor->id }}" {{ request('instructor') == $instructor->id ? 'selected' : '' }}>
                                            {{ $instructor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="btn-group w-100" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter me-1"></i>Filtrer
                                    </button>
                                    <a href="{{ route('admin.courses') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Effacer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Résultats de recherche -->
                    @if(request()->hasAny(['search', 'category', 'status', 'instructor']))
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Filtres appliqués :</strong>
                        @if(request('search'))
                            Recherche: "{{ request('search') }}"
                        @endif
                        @if(request('category'))
                            | Catégorie: {{ $categories->firstWhere('id', request('category'))->name ?? 'Inconnue' }}
                        @endif
                        @if(request('status'))
                            | Statut: {{ ucfirst(request('status')) }}
                        @endif
                        @if(request('instructor'))
                            | Formateur: {{ $instructors->firstWhere('id', request('instructor'))->name ?? 'Inconnu' }}
                        @endif
                        <a href="{{ route('admin.courses') }}" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="fas fa-times me-1"></i>Effacer les filtres
                        </a>
                    </div>
                    @endif

                    <!-- Tableau des cours -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'title', 'direction' => request('sort') == 'title' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Cours
                                            @if(request('sort') == 'title')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @else
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Instructeur</th>
                                    <th>Catégorie</th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'price', 'direction' => request('sort') == 'price' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Prix
                                            @if(request('sort') == 'price')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @else
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Statut</th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Créé
                                            @if(request('sort') == 'created_at')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @else
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courses as $course)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input course-checkbox" value="{{ $course->id }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                @if($course->featured_image)
                                                    <img src="{{ Storage::url($course->featured_image) }}" 
                                                         alt="{{ $course->title }}" 
                                                         class="rounded" 
                                                         width="60" 
                                                         height="40" 
                                                         style="object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 40px;">
                                                        <i class="fas fa-graduation-cap text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-1">{{ $course->title }}</h6>
                                                <small class="text-muted">
                                                    {{ $course->stats['total_lessons'] ?? 0 }} leçons • {{ $course->stats['total_duration'] ?? 0 }} min
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $course->instructor->avatar ? $instructor->avatar : asset('images/default-avatar.svg') }}" 
                                                 alt="{{ $course->instructor->name }}" 
                                                 class="rounded-circle me-2" 
                                                 width="30" 
                                                 height="30">
                                            <span>{{ $course->instructor->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $course->category->color ?? 'primary' }}">
                                            {{ $course->category->name }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($course->is_free)
                                            <span class="badge bg-success">Gratuit</span>
                                        @else
                                            <div>
                                                <strong>{{ number_format($course->current_price) }} FCFA</strong>
                                                @if($course->sale_price && $course->sale_price < $course->price)
                                                    <br><small class="text-muted">
                                                        <s>{{ number_format($course->price) }} FCFA</s>
                                                    </small>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if($course->is_published)
                                                <span class="badge bg-success">Publié</span>
                                            @else
                                                <span class="badge bg-warning">Brouillon</span>
                                            @endif
                                            
                                            @if($course->is_featured)
                                                <span class="badge bg-info">Vedette</span>
                                            @endif
                                            
                                            @if(($course->stats['average_rating'] ?? 0) > 0)
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-star text-warning me-1"></i>
                                                    <span>{{ number_format($course->stats['average_rating'] ?? 0, 1) }}</span>
                                                    <small class="text-muted ms-1">({{ $course->stats['total_reviews'] ?? 0 }})</small>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $course->created_at->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.courses.show', $course) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.courses.edit', $course) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Supprimer"
                                                    onclick="deleteCourse({{ $course->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                                            <p>Aucun cours trouvé</p>
                                            @if(request()->hasAny(['search', 'category', 'status', 'instructor']))
                                                <a href="{{ route('admin.courses') }}" class="btn btn-primary">
                                                    Voir tous les cours
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <span class="text-muted">
                                Affichage de {{ $courses->firstItem() ?? 0 }} à {{ $courses->lastItem() ?? 0 }} sur {{ $courses->total() }} cours
                                @if(request()->hasAny(['search', 'category', 'status', 'instructor']))
                                    ({{ $courses->count() }} résultat{{ $courses->count() > 1 ? 's' : '' }})
                                @endif
                            </span>
                        </div>
                        <div>
                            {{ $courses->appends(request()->query())->links() }}
                        </div>
                    </div>

                    <!-- Actions en lot -->
                    <div class="mt-3" id="bulkActions" style="display: none;">
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-success" onclick="bulkAction('publish')">
                                <i class="fas fa-check me-1"></i>Publier
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="bulkAction('unpublish')">
                                <i class="fas fa-times me-1"></i>Dépublier
                            </button>
                            <button class="btn btn-sm btn-info" onclick="bulkAction('feature')">
                                <i class="fas fa-star me-1"></i>Mettre en vedette
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                                <i class="fas fa-trash me-1"></i>Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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
                <p>Êtes-vous sûr de vouloir supprimer ce cours ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let courseIdToDelete = null;

// Gestion de la sélection multiple
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.course-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkActions();
});

document.querySelectorAll('.course-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkActions);
});

function toggleBulkActions() {
    const checkedBoxes = document.querySelectorAll('.course-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    
    if (checkedBoxes.length > 0) {
        bulkActions.style.display = 'block';
    } else {
        bulkActions.style.display = 'none';
    }
}

function deleteCourse(courseId) {
    courseIdToDelete = courseId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (courseIdToDelete) {
        // Créer un formulaire pour la suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/courses/${courseIdToDelete}`;
        
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

// Recherche en temps réel avec debounce
let searchTimeout;
document.querySelector('input[name="search"]').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
});

// Soumission automatique du formulaire lors du changement des sélecteurs
document.querySelectorAll('select[name="category"], select[name="status"], select[name="instructor"]').forEach(select => {
    select.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});

// Fonction pour les actions en lot
function bulkAction(action) {
    const checkedBoxes = document.querySelectorAll('.course-checkbox:checked');
    const courseIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (courseIds.length === 0) {
        alert('Veuillez sélectionner au moins un cours.');
        return;
    }
    
    if (action === 'delete' && !confirm('Êtes-vous sûr de vouloir supprimer les cours sélectionnés ?')) {
        return;
    }
    
    // Ici vous pouvez implémenter les actions en lot
    console.log(`Action: ${action}, Courses: ${courseIds.join(',')}`);
    alert(`Action "${action}" appliquée à ${courseIds.length} cours.`);
}
</script>
@endpush

@push('styles')
<style>
.table th {
    border-top: none;
    font-weight: 600;
    color: #003366;
}

.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    border-radius: 0.375rem;
}

.card-body h6 {
    color: #003366;
}

.text-primary {
    color: #003366 !important;
}
</style>
@endpush
@endsection