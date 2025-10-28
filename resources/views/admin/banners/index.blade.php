@extends('layouts.app')

@section('title', 'Gestion des bannières - Admin')

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
                                <i class="fas fa-images me-2"></i>Gestion des bannières
                            </h4>
                        </div>
                        <a href="{{ route('admin.banners.create') }}" class="btn btn-light">
                            <i class="fas fa-plus-circle me-1"></i>Nouvelle bannière
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <!-- Liste des bannières - Version Desktop (Tableau) -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="60">ID</th>
                                    <th width="100">Image</th>
                                    <th>Titre</th>
                                    <th width="150">Boutons</th>
                                    <th width="100" class="text-center">Ordre</th>
                                    <th width="100" class="text-center">Statut</th>
                                    <th width="200" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($banners as $banner)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $banner->id }}</span>
                                    </td>
                                    <td>
                                        <img src="{{ str_starts_with($banner->image, 'http') ? $banner->image : asset($banner->image) }}" 
                                             alt="{{ $banner->title }}" 
                                             class="img-thumbnail rounded"
                                             style="width: 100px; height: 60px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <strong class="d-block mb-1">{{ $banner->title }}</strong>
                                        @if($banner->subtitle)
                                        <small class="text-muted d-block">{{ Str::limit($banner->subtitle, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($banner->button1_text)
                                        <span class="badge bg-{{ $banner->button1_style ?? 'primary' }} mb-1">
                                            {{ $banner->button1_text }}
                                        </span>
                                        @endif
                                        @if($banner->button2_text)
                                        <br>
                                        <span class="badge bg-{{ $banner->button2_style ?? 'secondary' }}">
                                            {{ $banner->button2_text }}
                                        </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            <span class="badge bg-info fs-6">{{ $banner->sort_order }}</span>
                                            <div class="btn-group-vertical btn-group-sm">
                                                @if(!$loop->first)
                                                <form action="{{ route('admin.banners.update', $banner) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="sort_order" value="{{ $banner->sort_order - 1 }}">
                                                    <input type="hidden" name="title" value="{{ $banner->title }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Monter">
                                                        <i class="fas fa-arrow-up"></i>
                                                    </button>
                                                </form>
                                                @endif
                                                @if(!$loop->last)
                                                <form action="{{ route('admin.banners.update', $banner) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="sort_order" value="{{ $banner->sort_order + 1 }}">
                                                    <input type="hidden" name="title" value="{{ $banner->title }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Descendre">
                                                        <i class="fas fa-arrow-down"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-{{ $banner->is_active ? 'success' : 'secondary' }}" 
                                                onclick="toggleActive({{ $banner->id }}, {{ $banner->is_active ? 'true' : 'false' }})"
                                                title="Changer le statut">
                                            <i class="fas fa-{{ $banner->is_active ? 'check' : 'times' }} me-1"></i>
                                            {{ $banner->is_active ? 'Actif' : 'Inactif' }}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.banners.edit', $banner) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.banners.destroy', $banner) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette bannière ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucune bannière trouvée</p>
                                        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i>Créer la première bannière
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Liste des bannières - Version Mobile (Liste compacte) -->
                    <div class="d-md-none mobile-banner-list">
                        @forelse($banners as $banner)
                        <div class="banner-list-item">
                            <!-- Info principale -->
                            <div class="banner-info">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="{{ str_starts_with($banner->image, 'http') ? $banner->image : asset($banner->image) }}" 
                                         alt="{{ $banner->title }}" 
                                         class="banner-thumb me-2">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-primary badge-sm">{{ $banner->id }}</span>
                                            <span class="badge bg-info badge-sm">{{ $banner->sort_order }}</span>
                                            <span class="badge badge-sm {{ $banner->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas fa-{{ $banner->is_active ? 'check' : 'times' }}"></i>
                                            </span>
                                        </div>
                                        <div class="banner-title">{{ $banner->title }}</div>
                                        @if($banner->subtitle)
                                        <div class="banner-subtitle">{{ Str::limit($banner->subtitle, 40) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Actions compactes avec icônes uniquement -->
                            <div class="banner-actions">
                                <!-- Ordre -->
                                @if(!$loop->first)
                                <form action="{{ route('admin.banners.update', $banner) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="sort_order" value="{{ $banner->sort_order - 1 }}">
                                    <input type="hidden" name="title" value="{{ $banner->title }}">
                                    <button type="submit" class="btn-icon btn-icon-primary" title="Monter">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                </form>
                                @else
                                <span class="btn-icon btn-icon-disabled">
                                    <i class="fas fa-arrow-up"></i>
                                </span>
                                @endif

                                @if(!$loop->last)
                                <form action="{{ route('admin.banners.update', $banner) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="sort_order" value="{{ $banner->sort_order + 1 }}">
                                    <input type="hidden" name="title" value="{{ $banner->title }}">
                                    <button type="submit" class="btn-icon btn-icon-primary" title="Descendre">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                </form>
                                @else
                                <span class="btn-icon btn-icon-disabled">
                                    <i class="fas fa-arrow-down"></i>
                                </span>
                                @endif

                                <!-- Statut -->
                                <button class="btn-icon btn-icon-{{ $banner->is_active ? 'success' : 'secondary' }}" 
                                        onclick="toggleActive({{ $banner->id }}, {{ $banner->is_active ? 'true' : 'false' }})"
                                        title="{{ $banner->is_active ? 'Actif' : 'Inactif' }}">
                                    <i class="fas fa-{{ $banner->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                </button>

                                <!-- Modifier -->
                                <a href="{{ route('admin.banners.edit', $banner) }}" 
                                   class="btn-icon btn-icon-warning"
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <!-- Supprimer -->
                                <form action="{{ route('admin.banners.destroy', $banner) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette bannière ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-icon-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="fas fa-image fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted">Aucune bannière trouvée</p>
                            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Créer la première bannière
                            </a>
                        </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($banners->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $banners->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Design moderne pour la page de gestion des bannières */
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

.table thead {
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

.img-thumbnail {
    border: 2px solid #dee2e6;
    transition: transform 0.2s ease;
}

.img-thumbnail:hover {
    transform: scale(1.05);
    border-color: #0d6efd;
}

/* Amélioration des badges */
.badge {
    font-size: 0.85rem;
    padding: 0.4em 0.8em;
    font-weight: 500;
}

/* Correction des badges pour les boutons outline */
.badge.bg-outline-light {
    background-color: #f8f9fa !important;
    color: #333 !important;
    border: 1px solid #dee2e6;
}

.badge.bg-outline-primary {
    background-color: #e7f1ff !important;
    color: #0d6efd !important;
    border: 1px solid #b6d4fe;
}

.badge.bg-light {
    background-color: #f8f9fa !important;
    color: #333 !important;
    border: 1px solid #dee2e6;
}

/* Boutons d'ordre */
.btn-group-vertical .btn {
    padding: 0.25rem 0.5rem;
}

/* Liste Mobile Compacte */
.mobile-banner-list {
    padding: 0;
}

.banner-list-item {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 0.75rem;
    margin-bottom: 0.75rem;
    transition: box-shadow 0.2s ease;
}

.banner-list-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.banner-thumb {
    width: 60px;
    height: 38px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.banner-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #333;
    line-height: 1.3;
    margin-bottom: 0.15rem;
}

.banner-subtitle {
    font-size: 0.75rem;
    color: #6c757d;
    line-height: 1.2;
}

.badge-sm {
    font-size: 0.65rem;
    padding: 0.2em 0.5em;
}

/* Boutons avec icônes uniquement */
.banner-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #f0f0f0;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    padding: 0;
    border: 1px solid transparent;
    border-radius: 6px;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.btn-icon:hover {
    transform: translateY(-1px);
}

.btn-icon-primary {
    color: #0d6efd;
    border-color: #0d6efd;
}

.btn-icon-primary:hover {
    background: #0d6efd;
    color: white;
}

.btn-icon-success {
    color: #198754;
    border-color: #198754;
}

.btn-icon-success:hover {
    background: #198754;
    color: white;
}

.btn-icon-secondary {
    color: #6c757d;
    border-color: #6c757d;
}

.btn-icon-secondary:hover {
    background: #6c757d;
    color: white;
}

.btn-icon-warning {
    color: #ffc107;
    border-color: #ffc107;
}

.btn-icon-warning:hover {
    background: #ffc107;
    color: #000;
}

.btn-icon-danger {
    color: #dc3545;
    border-color: #dc3545;
}

.btn-icon-danger:hover {
    background: #dc3545;
    color: white;
}

.btn-icon-disabled {
    color: #d0d0d0;
    border-color: #e0e0e0;
    cursor: not-allowed;
    opacity: 0.5;
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
    
    .card-header .d-flex:not(.align-items-center) {
        flex-direction: column;
        gap: 0.5rem;
        align-items: stretch !important;
    }
    
    .card-header .btn-light:not(.btn-sm) {
        width: 100%;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    /* Liste mobile */
    .banner-list-item {
        padding: 0.65rem;
        margin-bottom: 0.65rem;
    }
    
    .banner-thumb {
        width: 55px;
        height: 35px;
    }
    
    .banner-title {
        font-size: 0.85rem;
    }
    
    .banner-subtitle {
        font-size: 0.7rem;
    }
    
    .btn-icon {
        width: 34px;
        height: 34px;
        font-size: 0.85rem;
    }
    
    .banner-actions {
        gap: 0.35rem;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-left: 0.25rem;
        padding-right: 0.25rem;
    }
    
    .card-header {
        padding: 0.75rem;
    }
    
    .card-header h4 {
        font-size: 1rem;
    }
    
    .card-body {
        padding: 0.5rem;
    }
    
    .banner-list-item {
        padding: 0.6rem;
        margin-bottom: 0.6rem;
        border-radius: 6px;
    }
    
    .banner-thumb {
        width: 50px;
        height: 32px;
    }
    
    .banner-title {
        font-size: 0.8rem;
    }
    
    .banner-subtitle {
        font-size: 0.68rem;
    }
    
    .badge-sm {
        font-size: 0.6rem;
        padding: 0.15em 0.4em;
    }
    
    .btn-icon {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
    }
    
    .banner-actions {
        gap: 0.3rem;
        margin-top: 0.4rem;
        padding-top: 0.4rem;
    }
}

/* Très petits écrans */
@media (max-width: 380px) {
    .banner-list-item {
        padding: 0.5rem;
    }
    
    .banner-thumb {
        width: 45px;
        height: 28px;
    }
    
    .banner-title {
        font-size: 0.75rem;
    }
    
    .banner-subtitle {
        font-size: 0.65rem;
    }
    
    .btn-icon {
        width: 30px;
        height: 30px;
        font-size: 0.75rem;
    }
    
    .banner-actions {
        gap: 0.25rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function toggleActive(bannerId, currentStatus) {
    if (confirm('Voulez-vous changer le statut de cette bannière ?')) {
        fetch(`/admin/banners/${bannerId}/toggle-active`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors du changement de statut');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du changement de statut');
        });
    }
}
</script>
@endpush

