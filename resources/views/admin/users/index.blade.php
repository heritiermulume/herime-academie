@extends('layouts.app')

@section('title', 'Gestion des utilisateurs - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-sm" title="Tableau de bord">
                                <i class="fas fa-tachometer-alt"></i>
                            </a>
                            <h4 class="mb-0">
                                <i class="fas fa-users me-2"></i>Gestion des utilisateurs
                            </h4>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-light">
                                <i class="fas fa-plus me-1"></i>Nouvel utilisateur
                            </a>
                            <div class="alert alert-info mb-0 py-2 px-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <small>La création se fait via le SSO (compte.herime.com)</small>
                            </div>
                        </div>
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
                                    <h5 class="card-title">{{ $stats['active'] }}</h5>
                                    <p class="card-text small">Actifs</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['students'] }}</h5>
                                    <p class="card-text small">Étudiants</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['instructors'] }}</h5>
                                    <p class="card-text small">Formateurs</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['admins'] }}</h5>
                                    <p class="card-text small">Admins</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['affiliates'] }}</h5>
                                    <p class="card-text small">Affiliés</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres et recherche -->
                    <form method="GET" action="{{ route('admin.users') }}" id="filterForm">
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
                                           placeholder="Rechercher par nom ou email...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="role">
                                    <option value="">Tous les rôles</option>
                                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                    <option value="instructor" {{ request('role') == 'instructor' ? 'selected' : '' }}>Formateur</option>
                                    <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Étudiant</option>
                                    <option value="affiliate" {{ request('role') == 'affiliate' ? 'selected' : '' }}>Affilié</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Vérifié</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="sort">
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date d'inscription</option>
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nom</option>
                                    <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="role" {{ request('sort') == 'role' ? 'selected' : '' }}>Rôle</option>
                                    <option value="last_login_at" {{ request('sort') == 'last_login_at' ? 'selected' : '' }}>Dernière connexion</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="btn-group w-100" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter me-1"></i>Filtrer
                                    </button>
                                    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Effacer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Tableau des utilisateurs -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Utilisateur
                                            @if(request('sort') == 'name')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @else
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'role', 'direction' => request('sort') == 'role' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Rôle
                                            @if(request('sort') == 'role')
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
                                            Inscription
                                            @if(request('sort') == 'created_at')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @else
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'last_login_at', 'direction' => request('sort') == 'last_login_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Dernière connexion
                                            @if(request('sort') == 'last_login_at')
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
                                @forelse($users as $user)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $user->avatar_url }}" 
                                                 alt="{{ $user->name }}" 
                                                 class="rounded-circle me-3" 
                                                 width="40" 
                                                 height="40">
                                            <div>
                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ ($user->role === 'admin' || $user->role === 'super_user') ? 'danger' : ($user->role === 'instructor' ? 'warning' : ($user->role === 'affiliate' ? 'info' : 'primary')) }}">
                                            {{ $user->role === 'super_user' ? 'Super User' : ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            @if($user->is_active)
                                                <span class="badge bg-success mb-1">Actif</span>
                                            @else
                                                <span class="badge bg-secondary mb-1">Inactif</span>
                                            @endif
                                            @if($user->is_verified)
                                                <span class="badge bg-info">Vérifié</span>
                                            @else
                                                <span class="badge bg-warning">Non vérifié</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <small>{{ $user->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais' }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="deleteUser({{ $user->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucun utilisateur trouvé</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Résultats de recherche -->
                    @if(request()->hasAny(['search', 'role', 'status']))
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Filtres appliqués :</strong>
                        @if(request('search'))
                            Recherche: "{{ request('search') }}"
                        @endif
                        @if(request('role'))
                            | Rôle: {{ ucfirst(request('role')) }}
                        @endif
                        @if(request('status'))
                            | Statut: {{ ucfirst(request('status')) }}
                        @endif
                        <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="fas fa-times me-1"></i>Effacer les filtres
                        </a>
                    </div>
                    @endif

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <span class="text-muted">
                                Affichage de {{ $users->firstItem() ?? 0 }} à {{ $users->lastItem() ?? 0 }} sur {{ $users->total() }} utilisateurs
                                @if(request()->hasAny(['search', 'role', 'status']))
                                    ({{ $users->count() }} résultat{{ $users->count() > 1 ? 's' : '' }})
                                @endif
                            </span>
                        </div>
                        <div>
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    </div>

                    <!-- Actions en lot -->
                    <div class="mt-3" id="bulkActions" style="display: none;">
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-success" onclick="bulkAction('activate')">
                                <i class="fas fa-check me-1"></i>Activer
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="bulkAction('deactivate')">
                                <i class="fas fa-times me-1"></i>Désactiver
                            </button>
                            <button class="btn btn-sm btn-info" onclick="bulkAction('verify')">
                                <i class="fas fa-check-circle me-1"></i>Vérifier
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
                <p>Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.</p>
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
let userIdToDelete = null;

// Sélection multiple
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkActions();
});

document.querySelectorAll('.user-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkActions);
});

function toggleBulkActions() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    
    if (checkedBoxes.length > 0) {
        bulkActions.style.display = 'block';
    } else {
        bulkActions.style.display = 'none';
    }
}

function deleteUser(userId) {
    userIdToDelete = userId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (userIdToDelete) {
        // Créer un formulaire pour la suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userIdToDelete}`;
        
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
document.querySelectorAll('select[name="role"], select[name="status"], select[name="sort"]').forEach(select => {
    select.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});

// Fonction pour les actions en lot
function bulkAction(action) {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (userIds.length === 0) {
        alert('Veuillez sélectionner au moins un utilisateur.');
        return;
    }
    
    if (action === 'delete' && !confirm('Êtes-vous sûr de vouloir supprimer les utilisateurs sélectionnés ?')) {
        return;
    }
    
    // Ici vous pouvez implémenter les actions en lot
    console.log(`Action: ${action}, Users: ${userIds.join(',')}`);
    alert(`Action "${action}" appliquée à ${userIds.length} utilisateur(s).`);
}
</script>
@endpush

@push('styles')
<style>
/* Design moderne pour la page de gestion des utilisateurs */
.card {
    border-radius: 15px;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #003366 0%, #004080 100%);
    border: none;
    padding: 1.5rem;
}

.table {
    margin-bottom: 0;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #003366;
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.table tbody tr {
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    transform: translateX(3px);
}

.badge {
    font-size: 0.85rem;
    padding: 0.4em 0.8em;
    font-weight: 500;
}

.btn-group .btn {
    border-radius: 0.375rem;
    margin-right: 2px;
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
}

.form-check-input:checked {
    background-color: #003366;
    border-color: #003366;
}

.rounded-circle {
    border: 2px solid #dee2e6;
    transition: transform 0.2s ease;
}

.rounded-circle:hover {
    transform: scale(1.1);
    border-color: #0d6efd;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .card-header {
        padding: 0.75rem;
    }
    
    .card-header h4 {
        font-size: 1rem;
    }
    
    .card-header .btn-outline-light.btn-sm {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }
    
    .card-header .btn-light {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .row.mb-4 .col-md-2 {
        margin-bottom: 0.5rem;
    }
    
    .table {
        font-size: 0.85rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .card-header .d-flex {
        flex-direction: column;
        gap: 0.5rem;
        align-items: stretch !important;
    }
    
    .card-header .btn-light:not(.btn-sm) {
        width: 100%;
    }
}
</style>
@endpush
