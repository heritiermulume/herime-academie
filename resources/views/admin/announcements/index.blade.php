@extends('layouts.app')

@section('title', 'Gestion des annonces - Admin')

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
                                <i class="fas fa-bullhorn me-2"></i>Gestion des annonces
                            </h4>
                        </div>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createAnnouncementModal">
                            <i class="fas fa-plus me-1"></i>Nouvelle annonce
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tableau des annonces -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Titre</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($announcements as $announcement)
                                <tr>
                                    <td>
                                        <h6 class="mb-0">{{ $announcement->title }}</h6>
                                        <small class="text-muted">{{ Str::limit($announcement->content, 100) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $announcement->type === 'info' ? 'info' : ($announcement->type === 'success' ? 'success' : ($announcement->type === 'warning' ? 'warning' : 'danger')) }}">
                                            {{ ucfirst($announcement->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $announcement->is_active ? 'success' : 'secondary' }}">
                                            {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $announcement->starts_at ? $announcement->starts_at->format('d/m/Y') : 'Immédiat' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $announcement->expires_at ? $announcement->expires_at->format('d/m/Y') : 'Illimité' }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-warning" onclick="editAnnouncement({{ $announcement->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteAnnouncement({{ $announcement->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucune annonce trouvée</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $announcements->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de création d'annonce -->
<div class="modal fade" id="createAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle annonce</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.announcements.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Contenu *</label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="info">Information</option>
                                    <option value="success">Succès</option>
                                    <option value="warning">Attention</option>
                                    <option value="error">Erreur</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="button_text" class="form-label">Texte du bouton</label>
                                <input type="text" class="form-control" id="button_text" name="button_text">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="button_url" class="form-label">URL du bouton</label>
                        <input type="url" class="form-control" id="button_url" name="button_url">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="starts_at" class="form-label">Date de début</label>
                                <input type="datetime-local" class="form-control" id="starts_at" name="starts_at">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expires_at" class="form-label">Date de fin</label>
                                <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">
                            Annonce active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer l'annonce</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'édition -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier l'annonce</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAnnouncementForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Titre *</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_content" class="form-label">Contenu *</label>
                        <textarea class="form-control" id="edit_content" name="content" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_type" class="form-label">Type</label>
                                <select class="form-select" id="edit_type" name="type">
                                    <option value="info">Information</option>
                                    <option value="success">Succès</option>
                                    <option value="warning">Attention</option>
                                    <option value="error">Erreur</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_button_text" class="form-label">Texte du bouton</label>
                                <input type="text" class="form-control" id="edit_button_text" name="button_text">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_button_url" class="form-label">URL du bouton</label>
                        <input type="url" class="form-control" id="edit_button_url" name="button_url">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_starts_at" class="form-label">Date de début</label>
                                <input type="datetime-local" class="form-control" id="edit_starts_at" name="starts_at">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_expires_at" class="form-label">Date de fin</label>
                                <input type="datetime-local" class="form-control" id="edit_expires_at" name="expires_at">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">
                            Annonce active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Modifier l'annonce</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editAnnouncement(id) {
    // Récupérer les données de l'annonce via AJAX
    fetch(`/admin/announcements/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            // Remplir le formulaire d'édition
            document.getElementById('edit_title').value = data.title || '';
            document.getElementById('edit_content').value = data.content || '';
            document.getElementById('edit_type').value = data.type || 'info';
            document.getElementById('edit_button_text').value = data.button_text || '';
            document.getElementById('edit_button_url').value = data.button_url || '';
            document.getElementById('edit_starts_at').value = data.starts_at ? data.starts_at.substring(0, 16) : '';
            document.getElementById('edit_expires_at').value = data.expires_at ? data.expires_at.substring(0, 16) : '';
            document.getElementById('edit_is_active').checked = data.is_active || false;
            
            // Mettre à jour l'action du formulaire
            document.getElementById('editAnnouncementForm').action = `/admin/announcements/${id}`;
            
            // Ouvrir le modal
            const modal = new bootstrap.Modal(document.getElementById('editAnnouncementModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Erreur lors du chargement de l\'annonce:', error);
            alert('Erreur lors du chargement de l\'annonce');
        });
}

function deleteAnnouncement(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/announcements/${id}`;
        
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
}
</script>
@endpush

@push('styles')
<style>
/* Design moderne pour la page de gestion des annonces */
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

.table tbody tr:hover {
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
    
    .card-header .btn-light:not(.btn-sm), .card-header button.btn-light {
        width: 100%;
    }
}
</style>
@endpush
