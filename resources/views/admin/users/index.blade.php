@extends('layouts.admin')

@section('title', 'Gestion des utilisateurs')
@section('admin-title', 'Gestion des utilisateurs')
@section('admin-subtitle', 'Supervisez les comptes créés via Compte Herime et ajustez leurs rôles')
@section('admin-actions')
    <div class="d-flex align-items-center gap-2 flex-wrap">
    <a href="https://compte.herime.com" class="btn btn-primary" target="_blank" rel="noopener">
        <i class="fas fa-user-plus me-2"></i>Nouvel utilisateur
    </a>
        <div class="alert alert-info mb-0 py-2 px-3">
            <i class="fas fa-info-circle me-2"></i>
            <small>La création se fait via Compte Herime (compte.herime.com)</small>
        </div>
    </div>
@endsection

@section('admin-content')
    <section class="admin-panel">
        <div class="admin-panel__body">
            <div class="admin-stats-grid mb-4">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Total</p>
                    <p class="admin-stat-card__value">{{ $stats['total'] }}</p>
                    <p class="admin-stat-card__muted">Utilisateurs enregistrés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Actifs</p>
                    <p class="admin-stat-card__value">{{ $stats['active'] }}</p>
                    <p class="admin-stat-card__muted">Accès autorisés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Étudiants</p>
                    <p class="admin-stat-card__value">{{ $stats['students'] }}</p>
                    <p class="admin-stat-card__muted">Apprenants actifs</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Formateurs</p>
                    <p class="admin-stat-card__value">{{ $stats['instructors'] }}</p>
                    <p class="admin-stat-card__muted">Experts pédagogiques</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Admins</p>
                    <p class="admin-stat-card__value">{{ $stats['admins'] }}</p>
                    <p class="admin-stat-card__muted">Équipe de pilotage</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Affiliés</p>
                    <p class="admin-stat-card__value">{{ $stats['affiliates'] }}</p>
                    <p class="admin-stat-card__muted">Partenaires commerciaux</p>
                </div>
            </div>

            <x-admin.search-panel
                :action="route('admin.users')"
                formId="usersFilterForm"
                filtersId="usersFilters"
                :hasFilters="true"
                :searchValue="request('search')"
                placeholder="Rechercher par nom ou email..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Rôle</label>
                            <select class="form-select" name="role">
                                <option value="">Tous les rôles</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                <option value="instructor" {{ request('role') == 'instructor' ? 'selected' : '' }}>Formateur</option>
                                <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Étudiant</option>
                                <option value="affiliate" {{ request('role') == 'affiliate' ? 'selected' : '' }}>Affilié</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Vérifié</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Tri</label>
                            <select class="form-select" name="sort">
                                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date d'inscription</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nom</option>
                                <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>Email</option>
                                <option value="role" {{ request('sort') == 'role' ? 'selected' : '' }}>Rôle</option>
                                <option value="last_login_at" {{ request('sort') == 'last_login_at' ? 'selected' : '' }}>Dernière connexion</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Ajustez les filtres puis appliquez-les.</span>
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            <!-- Filtres actifs -->
            @if(request('search') || request('role') || request('status'))
            <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <i class="fas fa-filter me-2"></i><strong>Filtres actifs :</strong>
                    @if(request('search'))
                        <span class="badge bg-primary ms-2">Recherche: "{{ request('search') }}"</span>
                    @endif
                    @if(request('role'))
                        <span class="badge bg-info ms-2">Rôle: {{ ucfirst(request('role')) }}</span>
                    @endif
                    @if(request('status'))
                        <span class="badge bg-warning ms-2">Statut: {{ ucfirst(request('status')) }}</span>
                    @endif
                </div>
                <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-times me-1"></i>Effacer les filtres
                </a>
            </div>
            @endif

            <div class="admin-table">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Utilisateur
                                        @if(request('sort') == 'name')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Inscription
                                        @if(request('sort') == 'created_at')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'last_login_at', 'direction' => request('sort') == 'last_login_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Dernière connexion
                                        @if(request('sort') == 'last_login_at')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th style="width: 130px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="admin-user-avatar">
                                        <div>
                                            <a href="{{ route('admin.users.show', $user) }}" class="fw-semibold text-decoration-none text-dark">{{ $user->name }}</a>
                                            <div class="text-muted small">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="admin-chip admin-chip--info text-uppercase">
                                        <i class="fas fa-user-tag me-1"></i>
                                        @switch($user->role)
                                            @case('admin')
                                                Administrateur
                                                @break
                                            @case('instructor')
                                                Formateur
                                                @break
                                            @case('affiliate')
                                                Affilié
                                                @break
                                            @case('student')
                                                Étudiant
                                                @break
                                            @default
                                                {{ ucfirst($user->role ?? 'utilisateur') }}
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="admin-chip {{ $user->is_active ? 'admin-chip--success' : 'admin-chip--neutral' }}">
                                            {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                        @if($user->is_verified)
                                            <span class="admin-chip admin-chip--success">
                                                <i class="fas fa-check-circle me-1"></i>Vérifié
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="admin-chip admin-chip--neutral">{{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}</span>
                                </td>
                                <td>
                                    @if($user->last_login_at)
                                        <span class="admin-chip admin-chip--info">{{ $user->last_login_at->diffForHumans() }}</span>
                                    @else
                                        <span class="text-muted small">Jamais connecté</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-light" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-light" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-light" title="Supprimer" onclick="deleteUser({{ $user->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun utilisateur trouvé</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="admin-pagination justify-content-between align-items-center">
                    {{ $users->appends(request()->query())->links() }}
                </div>

            </div>
        </div>
    </section>

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

const usersFilterForm = document.getElementById('usersFilterForm');
const usersFiltersOffcanvas = document.getElementById('usersFilters');

if (usersFilterForm) {
    usersFilterForm.addEventListener('submit', () => {
        if (usersFiltersOffcanvas) {
            const instance = bootstrap.Offcanvas.getInstance(usersFiltersOffcanvas);
            if (instance) {
                instance.hide();
            }
        }
    });
}

const searchInput = document.querySelector('#usersFilterForm input[name=\"search\"]');
if (searchInput) {
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            usersFilterForm?.submit();
        }, 500);
    });
}

</script>
@endpush

@push('styles')
<style>
.admin-user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    box-shadow: 0 6px 12px -6px rgba(15, 23, 42, 0.35);
}
</style>
@endpush
