@extends('layouts.admin')

@section('title', 'Gestion des bannières')
@section('admin-title', 'Gestion des bannières')
@section('admin-subtitle', 'Mettez en avant vos contenus clés sur la page d’accueil et les pages thématiques')
@section('admin-actions')
    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-2"></i>Nouvelle bannière
    </a>
@endsection

@php use Illuminate\Support\Str; @endphp

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="admin-table">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 280px;">Bannière</th>
                                <th>Boutons</th>
                                <th class="text-center" style="width: 140px;">Ordre</th>
                                <th class="text-center" style="width: 140px;">Statut</th>
                                <th class="text-center" style="width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banners as $banner)
                                <tr>
                                    <td style="min-width: 280px;">
                                        <div class="d-flex align-items-center gap-3">
                                            @php
                                                $bannerImage = $banner->image_url ?: 'https://via.placeholder.com/160x90?text=Banner';
                                            @endphp
                                            <img
                                                src="{{ $bannerImage }}"
                                                alt="{{ $banner->title }}"
                                                class="admin-banner-thumb"
                                            >
                                            <div>
                                                <div class="fw-semibold">{{ $banner->title }}</div>
                                                @if($banner->subtitle)
                                                    <div class="text-muted small">{{ Str::limit($banner->subtitle, 60) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($banner->button1_text)
                                                <span class="admin-chip admin-chip--info">
                                                    <i class="fas fa-link me-1"></i>{{ $banner->button1_text }}
                                                </span>
                                            @endif
                                            @if($banner->button2_text)
                                                <span class="admin-chip admin-chip--neutral">
                                                    <i class="fas fa-link me-1"></i>{{ $banner->button2_text }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($banner->button1_url || $banner->button2_url)
                                            <div class="text-muted small mt-1">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                {{ $banner->button1_url ?? $banner->button2_url }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-2 flex-wrap justify-content-center">
                                            <span class="admin-chip admin-chip--neutral">{{ $banner->sort_order }}</span>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @if(!$loop->first)
                                                    <form action="{{ route('admin.banners.update', $banner) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="sort_order" value="{{ $banner->sort_order - 1 }}">
                                                        <input type="hidden" name="title" value="{{ $banner->title }}">
                                                        <button type="submit" class="btn btn-light" title="Monter">
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
                                                        <button type="submit" class="btn btn-light" title="Descendre">
                                                            <i class="fas fa-arrow-down"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="admin-chip {{ $banner->is_active ? 'admin-chip--success' : 'admin-chip--neutral' }}">
                                            {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-center align-top">
                                        @if($loop->first)
                                            <div class="dropdown d-none d-md-block">
                                                <button class="btn btn-sm btn-light course-actions-btn" type="button" id="actionsDropdown{{ $banner->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown{{ $banner->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           data-action="{{ route('admin.banners.toggle-active', $banner) }}"
                                                           data-confirm="Confirmer le changement de statut de cette bannière ?"
                                                           data-success="Statut de la bannière mis à jour."
                                                           onclick="toggleBannerStatus(this); return false;">
                                                            <i class="fas fa-toggle-{{ $banner->is_active ? 'on' : 'off' }} me-2"></i>
                                                            {{ $banner->is_active ? 'Désactiver' : 'Activer' }}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.banners.edit', $banner) }}">
                                                            <i class="fas fa-edit me-2"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.banners.destroy', $banner) }}"
                                                           onclick="openDeleteBannerModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="dropdown d-md-none">
                                                <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="actionsDropdownMobile{{ $banner->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdownMobile{{ $banner->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           data-action="{{ route('admin.banners.toggle-active', $banner) }}"
                                                           data-confirm="Confirmer le changement de statut de cette bannière ?"
                                                           data-success="Statut de la bannière mis à jour."
                                                           onclick="toggleBannerStatus(this); return false;">
                                                            <i class="fas fa-toggle-{{ $banner->is_active ? 'on' : 'off' }} me-2"></i>
                                                            {{ $banner->is_active ? 'Désactiver' : 'Activer' }}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.banners.edit', $banner) }}">
                                                            <i class="fas fa-edit me-2"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.banners.destroy', $banner) }}"
                                                           onclick="openDeleteBannerModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @else
                                            <div class="dropup d-none d-md-block">
                                                <button class="btn btn-sm btn-light course-actions-btn" type="button" id="actionsDropdown{{ $banner->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown{{ $banner->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           data-action="{{ route('admin.banners.toggle-active', $banner) }}"
                                                           data-confirm="Confirmer le changement de statut de cette bannière ?"
                                                           data-success="Statut de la bannière mis à jour."
                                                           onclick="toggleBannerStatus(this); return false;">
                                                            <i class="fas fa-toggle-{{ $banner->is_active ? 'on' : 'off' }} me-2"></i>
                                                            {{ $banner->is_active ? 'Désactiver' : 'Activer' }}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.banners.edit', $banner) }}">
                                                            <i class="fas fa-edit me-2"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.banners.destroy', $banner) }}"
                                                           onclick="openDeleteBannerModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="dropup d-md-none">
                                                <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="actionsDropdownMobile{{ $banner->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdownMobile{{ $banner->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           data-action="{{ route('admin.banners.toggle-active', $banner) }}"
                                                           data-confirm="Confirmer le changement de statut de cette bannière ?"
                                                           data-success="Statut de la bannière mis à jour."
                                                           onclick="toggleBannerStatus(this); return false;">
                                                            <i class="fas fa-toggle-{{ $banner->is_active ? 'on' : 'off' }} me-2"></i>
                                                            {{ $banner->is_active ? 'Désactiver' : 'Activer' }}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.banners.edit', $banner) }}">
                                                            <i class="fas fa-edit me-2"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.banners.destroy', $banner) }}"
                                                           onclick="openDeleteBannerModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="admin-table__empty">
                                        <i class="fas fa-image mb-2 d-block"></i>
                                        Aucune bannière trouvée
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-admin.pagination :paginator="$banners" />
        </div>
    </section>

    <!-- Modal suppression -->
    <div class="modal fade" id="deleteBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cette bannière ? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="deleteBannerForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function toggleBannerStatus(button) {
    if (!button || button.disabled) {
        return;
    }

    const url = button.dataset.action;
    if (!url) {
        console.error('Aucune URL d’action fournie pour ce bouton.');
        return;
    }

    const confirmMessage = button.dataset.confirm || 'Confirmer cette action ?';
    if (!confirm(confirmMessage)) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        alert('Jeton CSRF introuvable. Veuillez rafraîchir la page.');
        return;
    }

    const originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    })
    .then(async response => {
        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            data = null;
        }

        if (!response.ok) {
            const message = data?.message || 'Une erreur est survenue lors du changement de statut.';
            throw new Error(message);
        }

        const successMessage = button.dataset.success || data?.message || 'Statut de la bannière mis à jour.';
        alert(successMessage);
        window.location.reload();
    })
    .catch(error => {
        console.error(error);
        alert(error.message || 'Impossible de mettre à jour le statut.');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalHtml;
    });
}

function openDeleteBannerModal(button) {
    const modalElement = document.getElementById('deleteBannerModal');
    const form = document.getElementById('deleteBannerForm');
    if (!modalElement || !form) return;

    const actionUrl = button.dataset.action;
    if (!actionUrl) return;

    form.action = actionUrl;
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}
</script>
@endpush

@push('styles')
<style>
.admin-banner-thumb {
    width: 96px;
    height: 64px;
    border-radius: 0.75rem;
    object-fit: cover;
    box-shadow: 0 12px 25px -18px rgba(15, 23, 42, 0.45);
    flex-shrink: 0;
}

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
    
    .admin-panel__body .row.mt-2 {
        margin-top: 0.375rem !important;
    }
    
    .admin-card__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-card__body {
        padding: 0.5rem;
    }
    
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
    
    .admin-panel__body .row.mt-2 {
        margin-top: 0.375rem !important;
    }
    
    .admin-card__header {
        padding: 0.5rem 0.625rem;
    }
    
    .admin-card__body {
        padding: 0.375rem;
    }
    
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
}
</style>
@endpush

