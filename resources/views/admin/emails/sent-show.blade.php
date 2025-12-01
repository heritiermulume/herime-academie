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
    <div class="row g-4">
        <div class="col-md-8">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-envelope me-2"></i>Informations de l'email
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Destinataire</dt>
                        <dd class="col-sm-8">
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $avatarUrl = $recipientUser ? $recipientUser->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($sentEmail->recipient_name ?? 'N/A') . '&background=003366&color=fff&size=128';
                                @endphp
                                <div class="email-avatar-container" style="width: 40px; height: 40px; min-width: 40px; max-width: 40px; min-height: 40px; max-height: 40px; flex-shrink: 0; border-radius: 50% !important; overflow: hidden !important; display: inline-block; position: relative;">
                                    <img src="{{ $avatarUrl }}" 
                                         alt="{{ $sentEmail->recipient_name ?? 'N/A' }}" 
                                         class="email-avatar"
                                         style="width: 100% !important; height: 100% !important; object-fit: cover !important; display: block !important; border-radius: 50% !important;"
                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($sentEmail->recipient_name ?? 'N/A') }}&background=003366&color=fff&size=128'">
                                </div>
                                <div>
                                    <strong>{{ $sentEmail->recipient_name ?? 'N/A' }}</strong><br>
                                    <span class="text-muted">
                                        <i class="fas fa-envelope me-1"></i>{{ $sentEmail->recipient_email }}
                                    </span>
                                </div>
                            </div>
                        </dd>

                        <dt class="col-sm-4">Sujet</dt>
                        <dd class="col-sm-8"><strong>{{ $sentEmail->subject }}</strong></dd>

                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-info">{{ ucfirst($sentEmail->type) }}</span>
                        </dd>

                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">
                            @if($sentEmail->status === 'sent')
                                <span class="badge bg-success">Envoyé</span>
                            @elseif($sentEmail->status === 'failed')
                                <span class="badge bg-danger">Échoué</span>
                            @else
                                <span class="badge bg-warning">En attente</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Date d'envoi</dt>
                        <dd class="col-sm-8">
                            <i class="fas fa-calendar me-2"></i>
                            {{ $sentEmail->sent_at ? $sentEmail->sent_at->format('d/m/Y à H:i:s') : ($sentEmail->created_at->format('d/m/Y à H:i:s')) }}
                        </dd>

                        @if($sentEmail->error_message)
                            <dt class="col-sm-4">Erreur</dt>
                            <dd class="col-sm-8">
                                <div class="alert alert-danger mb-0">
                                    {{ $sentEmail->error_message }}
                                </div>
                            </dd>
                        @endif
                    </dl>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-file-alt me-2"></i>Contenu de l'email
                    </h3>
                </div>
                <div class="admin-panel__body">
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
            </section>
        </div>

        <div class="col-md-4">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-info-circle me-2"></i>Métadonnées
                    </h3>
                </div>
                <div class="admin-panel__body">
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

                    <hr class="my-3">

                    <dl class="row mb-0">
                        <dt class="col-sm-6">Date de création</dt>
                        <dd class="col-sm-6">{{ $sentEmail->created_at->format('d/m/Y à H:i') }}</dd>

                        <dt class="col-sm-6">Dernière mise à jour</dt>
                        <dd class="col-sm-6">{{ $sentEmail->updated_at->format('d/m/Y à H:i') }}</dd>
                    </dl>

                    @if($sentEmail->user)
                        <hr class="my-3">
                        <div>
                            <dt class="col-sm-12 mb-1"><strong>Utilisateur</strong></dt>
                            <dd class="col-sm-12">
                                <a href="{{ route('admin.users.show', $sentEmail->user) }}">
                                    {{ $sentEmail->user->name }}
                                </a>
                            </dd>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection

@push('styles')
<style>
/* Styles identiques à analytics */
.admin-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-card__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 16px 16px 0 0;
}

/* Réduire l'espace au-dessus du contenu sur desktop */
@media (min-width: 992px) {
    .admin-card__header .admin-card__title.mb-1 {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-card__header {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }
}

.admin-card__title {
    margin: 0;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
}

.admin-card__body {
    padding: 1.25rem;
}

/* Styles pour admin-panel - identiques à analytics */
.admin-panel {
    margin-bottom: 2rem;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-panel__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-panel__header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.admin-panel__body {
    padding: 1rem;
}

/* Padding légèrement réduit sur desktop */
@media (min-width: 992px) {
    .admin-panel__body {
        padding: 0.875rem 1rem;
    }
}

/* Corriger le chevauchement des boutons dans la carte Informations du certificat */
.admin-panel__body dl.row dd {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.admin-panel__body dl.row dd .badge {
    flex-shrink: 0;
}

.admin-panel__body dl.row dd .btn,
.admin-panel__body dl.row dd button {
    flex-shrink: 0;
    white-space: nowrap;
}

.email-content-preview {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: #fff;
    min-height: 200px;
}

/* Styles responsives pour les paddings et margins - identiques à analytics */
@media (max-width: 991.98px) {
    /* Réduire les paddings et margins sur tablette */
    .admin-panel {
        margin-bottom: 1rem;
    }
    
    .admin-panel__body {
        padding: 0 !important;
    }
    
    .admin-panel__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-panel__header h3 {
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.5rem;
        --bs-gutter-y: 0.5rem;
    }
    
    .admin-card {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-card__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-card__body {
        padding: 0.5rem;
    }
}

@media (max-width: 767.98px) {
    /* Réduire encore plus les paddings et margins sur mobile */
    .admin-panel {
        margin-bottom: 0.75rem;
    }
    
    .admin-panel__body {
        padding: 1.25rem !important;
    }
    
    .admin-panel__header {
        padding: 0.375rem 0.5rem;
    }
    
    .admin-panel__header h3 {
        font-size: 0.95rem;
        margin-bottom: 0.125rem;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.375rem;
        --bs-gutter-y: 0.375rem;
    }
    
    .admin-card {
        margin-bottom: 0.5rem !important;
    }
    
    /* Garder le même design de carte que sur desktop - mêmes tailles */
    .admin-card__header {
        padding: 1rem 1.25rem !important;
    }
    
    .admin-card__body {
        padding: 1.25rem !important;
    }
    
    /* Empiler les boutons sur mobile dans la carte Informations du certificat */
    .admin-panel__body dl.row dd .btn,
    .admin-panel__body dl.row dd button {
        flex: 1 1 auto;
        min-width: 120px;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    }
    
    /* Avatar Email - toujours circulaire */
    .email-avatar-container {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        max-width: 40px !important;
        min-height: 40px !important;
        max-height: 40px !important;
        flex-shrink: 0 !important;
        border-radius: 50% !important;
        -webkit-border-radius: 50% !important;
        -moz-border-radius: 50% !important;
        overflow: hidden !important;
        display: inline-block !important;
        position: relative !important;
        aspect-ratio: 1 / 1 !important;
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .email-avatar {
        width: 100% !important;
        height: 100% !important;
        min-width: 100% !important;
        min-height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: cover !important;
        object-position: center !important;
        display: block !important;
        border-radius: 50% !important;
        -webkit-border-radius: 50% !important;
        -moz-border-radius: 50% !important;
        aspect-ratio: 1 / 1 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
    }
</style>
@endpush
