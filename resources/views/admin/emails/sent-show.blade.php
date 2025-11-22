@extends('layouts.admin')

@section('title', 'Détails de l\'email')
@section('admin-title', 'Détails de l\'email envoyé')
@section('admin-subtitle', 'Consultez les détails complets de l\'email')
@section('admin-actions')
    <a href="{{ route('admin.emails.sent') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <div class="row">
                <div class="col-md-8">
                    <div class="admin-form-card mb-4">
                        <h5 class="mb-3"><i class="fas fa-envelope me-2"></i>Informations de l'email</h5>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Destinataire</label>
                            <div>
                                <strong>{{ $sentEmail->recipient_name ?? 'N/A' }}</strong><br>
                                <span class="text-muted">{{ $sentEmail->recipient_email }}</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Sujet</label>
                            <div><strong>{{ $sentEmail->subject }}</strong></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Type</label>
                            <div>
                                <span class="badge bg-info">{{ ucfirst($sentEmail->type) }}</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Statut</label>
                            <div>
                                @if($sentEmail->status === 'sent')
                                    <span class="badge bg-success">Envoyé</span>
                                @elseif($sentEmail->status === 'failed')
                                    <span class="badge bg-danger">Échoué</span>
                                @else
                                    <span class="badge bg-warning">En attente</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Date d'envoi</label>
                            <div>
                                {{ $sentEmail->sent_at ? $sentEmail->sent_at->format('d/m/Y à H:i:s') : ($sentEmail->created_at->format('d/m/Y à H:i:s')) }}
                            </div>
                        </div>
                        
                        @if($sentEmail->error_message)
                        <div class="alert alert-danger">
                            <strong>Erreur:</strong> {{ $sentEmail->error_message }}
                        </div>
                        @endif
                    </div>
                    
                    <div class="admin-form-card">
                        <h5 class="mb-3"><i class="fas fa-file-alt me-2"></i>Contenu de l'email</h5>
                        <div class="email-content-preview">
                            {!! $sentEmail->content !!}
                        </div>
                        
                        @if($sentEmail->attachments && count($sentEmail->attachments) > 0)
                        <div class="mt-3">
                            <h6>Pièces jointes:</h6>
                            <ul>
                                @foreach($sentEmail->attachments as $attachment)
                                <li>{{ $attachment }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="admin-form-card">
                        <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Métadonnées</h5>
                        
                        @if($sentEmail->metadata)
                        <dl class="mb-0">
                            @foreach($sentEmail->metadata as $key => $value)
                            <dt class="text-muted small">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="mb-2">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                            @endforeach
                        </dl>
                        @else
                        <p class="text-muted mb-0">Aucune métadonnée</p>
                        @endif
                        
                        <hr>
                        
                        <div class="mb-2">
                            <small class="text-muted">Date de création</small><br>
                            <strong>{{ $sentEmail->created_at->format('d/m/Y à H:i') }}</strong>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted">Dernière mise à jour</small><br>
                            <strong>{{ $sentEmail->updated_at->format('d/m/Y à H:i') }}</strong>
                        </div>
                        
                        @if($sentEmail->user)
                        <hr>
                        <div>
                            <small class="text-muted">Utilisateur</small><br>
                            <a href="{{ route('admin.users.show', $sentEmail->user) }}">
                                {{ $sentEmail->user->name }}
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
.email-content-preview {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: #fff;
    min-height: 200px;
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
    
    .admin-form-card {
        padding: 0.75rem !important;
        margin-bottom: 0.75rem !important;
    }
    
    .admin-form-card h5 {
        font-size: 1rem !important;
        margin-bottom: 0.75rem !important;
    }
    
    .email-content-preview {
        padding: 0.75rem !important;
        font-size: 0.9rem;
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
    
    .admin-form-card {
        padding: 0.625rem !important;
        margin-bottom: 0.625rem !important;
    }
    
    .admin-form-card h5 {
        font-size: 0.95rem !important;
        margin-bottom: 0.625rem !important;
    }
    
    .admin-form-card h5 i {
        font-size: 0.9rem !important;
    }
    
    .admin-form-card .mb-3 {
        margin-bottom: 0.625rem !important;
    }
    
    .admin-form-card .mb-2 {
        margin-bottom: 0.5rem !important;
    }
    
    .email-content-preview {
        padding: 0.625rem !important;
        font-size: 0.85rem;
        min-height: 150px;
    }
    
    .form-label {
        font-size: 0.875rem !important;
    }
    
    .admin-form-card small {
        font-size: 0.8rem !important;
    }
    
    .admin-form-card strong {
        font-size: 0.9rem !important;
    }
    
    /* Adapter les colonnes pour mobile */
    .admin-panel__body .row > [class*="col-"] {
        margin-bottom: 0.625rem;
    }
    
    /* Réduire les espaces dans les définitions de liste */
    .admin-form-card dl dt {
        font-size: 0.8rem !important;
        margin-bottom: 0.25rem !important;
    }
    
    .admin-form-card dl dd {
        font-size: 0.875rem !important;
        margin-bottom: 0.75rem !important;
    }
    
    /* Réduire le padding des alertes */
    .alert {
        padding: 0.625rem 0.75rem !important;
        font-size: 0.875rem !important;
    }
    
    /* Réduire la taille des badges */
    .badge {
        font-size: 0.75rem !important;
        padding: 0.25rem 0.5rem !important;
    }
}
</style>
@endpush

