@extends('layouts.admin')

@section('title', 'Aperçu de l\'annonce')
@section('admin-title', 'Aperçu de l\'annonce')
@section('admin-subtitle', 'Visualisez comment l\'annonce apparaîtra sur le site')

@section('admin-actions')
    <a href="{{ route('admin.announcements') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('admin-content')
<div class="admin-panel">
    <div class="admin-panel__body">
        <!-- Informations de l'annonce -->
        <div class="mb-4">
            <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Informations de l'annonce</h5>
            <dl class="row">
                <dt class="col-sm-3">Titre :</dt>
                <dd class="col-sm-9">{{ $announcement->title }}</dd>
                
                <dt class="col-sm-3">Type :</dt>
                <dd class="col-sm-9">
                    <span class="badge bg-{{ $announcement->type === 'info' ? 'info' : ($announcement->type === 'success' ? 'success' : ($announcement->type === 'warning' ? 'warning' : 'danger')) }}">
                        {{ ucfirst($announcement->type) }}
                    </span>
                </dd>
                
                <dt class="col-sm-3">Statut :</dt>
                <dd class="col-sm-9">
                    <span class="badge bg-{{ $announcement->is_active ? 'success' : 'secondary' }}">
                        {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </dd>
                
                <dt class="col-sm-3">Date de début :</dt>
                <dd class="col-sm-9">{{ $announcement->starts_at ? $announcement->starts_at->format('d/m/Y H:i') : 'Immédiat' }}</dd>
                
                <dt class="col-sm-3">Date de fin :</dt>
                <dd class="col-sm-9">{{ $announcement->expires_at ? $announcement->expires_at->format('d/m/Y H:i') : 'Illimité' }}</dd>
                
                @if($announcement->button_text && $announcement->button_url)
                <dt class="col-sm-3">Bouton d'action :</dt>
                <dd class="col-sm-9">
                    <span class="badge bg-info">{{ $announcement->button_text }}</span>
                    <small class="text-muted d-block mt-1">{{ $announcement->button_url }}</small>
                </dd>
                @endif
            </dl>
        </div>

        <!-- Aperçu de l'annonce -->
        <div class="mb-4">
            <h5 class="mb-3"><i class="fas fa-eye me-2"></i>Aperçu de l'annonce</h5>
            <div class="preview-container" style="border: 2px dashed #dee2e6; border-radius: 8px; padding: 1rem; background: #f8f9fa; position: relative; z-index: 0; overflow: visible;">
                <div class="global-announcement global-announcement--{{ $announcement->type }}" style="position: relative; width: 100%; border-radius: 8px; overflow: hidden; z-index: 0;">
                    <div class="global-announcement__inner">
                        <div class="global-announcement__content">
                            <div class="global-announcement__icon">
                                <i class="fas fa-{{ $announcement->type === 'info' ? 'info-circle' : ($announcement->type === 'success' ? 'check-circle' : ($announcement->type === 'warning' ? 'exclamation-triangle' : 'times-circle')) }}"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h6 class="global-announcement__title">{{ $announcement->title }}</h6>
                                <p class="global-announcement__text">{{ $announcement->content }}</p>
                            </div>
                        </div>
                        @if($announcement->button_text && $announcement->button_url)
                        <div class="global-announcement__actions">
                            <a href="{{ $announcement->button_url }}" class="global-announcement__btn" target="_blank">
                                {{ $announcement->button_text }}
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu complet -->
        <div class="mb-4">
            <h5 class="mb-3"><i class="fas fa-file-alt me-2"></i>Contenu complet</h5>
            <div class="admin-panel" style="background: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 1.5rem;">
                <div class="mb-3">
                    <strong>Titre :</strong>
                    <p class="mb-0">{{ $announcement->title }}</p>
                </div>
                <div>
                    <strong>Contenu :</strong>
                    <div class="mt-2" style="white-space: pre-wrap; word-wrap: break-word;">{{ $announcement->content }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Styles pour l'aperçu de l'annonce */
.preview-container {
    max-width: 100%;
    position: relative;
    z-index: 0;
    overflow: visible;
}

.global-announcement {
    width: 100%;
    box-shadow: 0 15px 30px -20px rgba(15, 23, 42, 0.35);
    position: relative;
    z-index: 0;
}

.global-announcement__inner {
    display: flex;
    align-items: center;
    gap: 1rem;
    justify-content: space-between;
    padding: 0.85rem 1.25rem;
}

.global-announcement__content {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex: 1;
    min-width: 0;
}

.global-announcement__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
    flex-shrink: 0;
}

.global-announcement__title {
    font-weight: 600;
    margin: 0;
    color: #ffffff;
    font-size: 1rem;
}

.global-announcement__text {
    margin: 0.25rem 0 0 0;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.95rem;
    line-height: 1.5;
}

.global-announcement__actions {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
}

.global-announcement__btn {
    color: #ffffff;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border: 1px solid rgba(255, 255, 255, 0.55);
    border-radius: 999px;
    text-decoration: none;
    transition: background-color 0.2s ease, color 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.9rem;
}

.global-announcement__btn:hover {
    color: #0b1f3a;
    background: #ffffff;
}

.global-announcement--info {
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
}

.global-announcement--success {
    background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
}

.global-announcement--warning {
    background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
}

.global-announcement--error {
    background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%);
}

@media (max-width: 768px) {
    .global-announcement__inner {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .global-announcement__actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .global-announcement__btn {
        flex: 1;
        justify-content: center;
    }
}
</style>
@endpush
@endsection

