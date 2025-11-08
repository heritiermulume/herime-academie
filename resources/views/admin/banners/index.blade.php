@extends('layouts.admin')

@section('title', 'Gestion des bannières')
@section('admin-title', 'Gestion des bannières')
@section('admin-subtitle', 'Mettez en avant vos contenus clés sur la page d’accueil et les pages thématiques')
@section('admin-actions')
    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-2"></i>Nouvelle bannière
    </a>
@endsection

@section('admin-content')
    <section class="admin-panel">
        <div class="admin-panel__body">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="admin-table d-none d-md-block mb-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Visuel</th>
                                <th>Titre / Sous-titre</th>
                                <th>Boutons</th>
                                <th class="text-center">Ordre</th>
                                <th class="text-center">Statut</th>
                                <th class="text-center">Actions</th>
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
                                    <td colspan="7" class="admin-table__empty">
                                        <i class="fas fa-image mb-2 d-block"></i>
                                        Aucune bannière trouvée
                                    </td>
                                </tr>
                                @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-card-grid d-md-none">
                        @forelse($banners as $banner)
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex gap-3">
                                    <img src="{{ str_starts_with($banner->image, 'http') ? $banner->image : asset($banner->image) }}" alt="{{ $banner->title }}" class="rounded" style="width: 96px; height: 64px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="admin-chip admin-chip--info">#{{ $banner->id }}</span>
                                            <span class="admin-chip admin-chip--neutral">Ordre {{ $banner->sort_order }}</span>
                                            <span class="admin-chip {{ $banner->is_active ? 'admin-chip--success' : 'admin-chip--neutral' }}">
                                                {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                        <div class="fw-semibold">{{ $banner->title }}</div>
                                        @if($banner->subtitle)
                                            <div class="text-muted small">{{ Str::limit($banner->subtitle, 60) }}</div>
                                        @endif
                                        <div class="mt-2 d-flex flex-wrap gap-1">
                                            @if($banner->button1_text)
                                                <span class="admin-chip admin-chip--info">{{ $banner->button1_text }}</span>
                                            @endif
                                            @if($banner->button2_text)
                                                <span class="admin-chip admin-chip--neutral">{{ $banner->button2_text }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-light d-flex gap-2 justify-content-between">
                                <div class="btn-group" role="group">
                                    @if(!$loop->first)
                                        <form action="{{ route('admin.banners.update', $banner) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="sort_order" value="{{ $banner->sort_order - 1 }}">
                                            <input type="hidden" name="title" value="{{ $banner->title }}">
                                            <button type="submit" class="btn btn-sm btn-light" title="Monter"><i class="fas fa-arrow-up"></i></button>
                                        </form>
                                    @endif
                                    @if(!$loop->last)
                                        <form action="{{ route('admin.banners.update', $banner) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="sort_order" value="{{ $banner->sort_order + 1 }}">
                                            <input type="hidden" name="title" value="{{ $banner->title }}">
                                            <button type="submit" class="btn btn-sm btn-light" title="Descendre"><i class="fas fa-arrow-down"></i></button>
                                        </form>
                                    @endif
                                </div>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-light" onclick="toggleActive({{ $banner->id }}, {{ $banner->is_active ? 'true' : 'false' }})" title="Changer le statut">
                                        <i class="fas fa-{{ $banner->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                    </button>
                                    <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-sm btn-light" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette bannière ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="admin-table__empty">
                            <i class="fas fa-image mb-2 d-block"></i>
                            Aucune bannière trouvée
                            <div class="mt-3">
                                <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Créer la première bannière
                                </a>
                            </div>
                        </div>
                        @endforelse
                    </div>
            <div class="admin-pagination">
                {{ $banners->links() }}
            </div>
        </div>
    </section>
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

