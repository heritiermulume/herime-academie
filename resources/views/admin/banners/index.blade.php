@extends('layouts.app')

@section('title', 'Gestion des bannières - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-image me-2"></i>Gestion des bannières
                        </h4>
                        <a href="{{ route('admin.banners.create') }}" class="btn btn-light">
                            <i class="fas fa-plus me-1"></i>Nouvelle bannière
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

                    <!-- Liste des bannières -->
                    <div class="table-responsive">
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
                                    <td>{{ $banner->id }}</td>
                                    <td>
                                        <img src="{{ $banner->image }}" 
                                             alt="{{ $banner->title }}" 
                                             class="img-thumbnail"
                                             style="width: 80px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <strong>{{ $banner->title }}</strong>
                                        @if($banner->subtitle)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($banner->subtitle, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($banner->button1_text)
                                        <span class="badge bg-{{ $banner->button1_style ?? 'primary' }}">
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
                                        <span class="badge bg-info">{{ $banner->sort_order }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-{{ $banner->is_active ? 'success' : 'secondary' }}" 
                                                onclick="toggleActive({{ $banner->id }}, {{ $banner->is_active ? 'true' : 'false' }})">
                                            <i class="fas fa-{{ $banner->is_active ? 'check' : 'times' }}"></i>
                                            {{ $banner->is_active ? 'Actif' : 'Inactif' }}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.banners.edit', $banner) }}" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.banners.destroy', $banner) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette bannière ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
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

