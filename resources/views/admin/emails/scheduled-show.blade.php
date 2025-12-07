@extends('layouts.admin')

@section('title', 'Détails de l\'email programmé')
@section('admin-title', 'Détails de l\'email programmé')
@section('admin-subtitle', 'Consultez les détails complets de l\'email programmé')
@section('admin-actions')
    <a href="{{ route('admin.emails.scheduled') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
    @if($scheduledEmail->status === 'pending')
    <form action="{{ route('admin.emails.scheduled.cancel', $scheduledEmail) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cet email programmé ?');">
        @csrf
        @method('POST')
        <button type="submit" class="btn btn-danger">
            <i class="fas fa-times me-2"></i>Annuler l'envoi
        </button>
    </form>
    @endif
@endsection

@section('admin-content')
    <div class="row g-4">
        <div class="col-md-8">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-envelope me-2"></i>Informations de l'email programmé
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Sujet</dt>
                        <dd class="col-sm-8"><strong>{{ $scheduledEmail->subject }}</strong></dd>

                        <dt class="col-sm-4">Type de destinataires</dt>
                        <dd class="col-sm-8">
                            @if($scheduledEmail->recipient_type === 'all')
                                <span class="badge bg-primary">Tous les utilisateurs</span>
                            @elseif($scheduledEmail->recipient_type === 'role')
                                <span class="badge bg-info">Par rôle</span>
                                @if(isset($scheduledEmail->recipient_config['roles']))
                                    <br><small class="text-muted">Rôles: {{ implode(', ', $scheduledEmail->recipient_config['roles']) }}</small>
                                @endif
                            @elseif($scheduledEmail->recipient_type === 'selected')
                                <span class="badge bg-warning">Utilisateurs sélectionnés</span>
                                @if(isset($scheduledEmail->recipient_config['user_ids']))
                                    <br><small class="text-muted">{{ count($scheduledEmail->recipient_config['user_ids']) }} utilisateurs sélectionnés</small>
                                @endif
                            @elseif($scheduledEmail->recipient_type === 'single')
                                <span class="badge bg-secondary">Un seul utilisateur</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Nombre de destinataires</dt>
                        <dd class="col-sm-8"><strong>{{ $scheduledEmail->total_recipients }}</strong> destinataire(s)</dd>

                        <dt class="col-sm-4">Programmé pour</dt>
                        <dd class="col-sm-8">
                            <i class="fas fa-calendar me-2"></i>
                            <strong>{{ $scheduledEmail->scheduled_at->format('d/m/Y à H:i') }}</strong>
                        </dd>

                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">
                            @if($scheduledEmail->status === 'pending')
                                <span class="badge bg-warning">En attente</span>
                            @elseif($scheduledEmail->status === 'processing')
                                <span class="badge bg-info">En cours d'envoi</span>
                            @elseif($scheduledEmail->status === 'completed')
                                <span class="badge bg-success">Terminé</span>
                            @elseif($scheduledEmail->status === 'failed')
                                <span class="badge bg-danger">Échoué</span>
                            @elseif($scheduledEmail->status === 'cancelled')
                                <span class="badge bg-secondary">Annulé</span>
                            @endif
                        </dd>

                        @if($scheduledEmail->status === 'processing' || $scheduledEmail->status === 'completed')
                            <dt class="col-sm-4">Progression</dt>
                            <dd class="col-sm-8">
                                @php
                                    $percentage = $scheduledEmail->total_recipients > 0 
                                        ? ($scheduledEmail->sent_count / $scheduledEmail->total_recipients) * 100 
                                        : 0;
                                @endphp
                                <div class="progress mb-2" style="height: 25px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $percentage }}%"
                                         aria-valuenow="{{ $percentage }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ round($percentage) }}%
                                    </div>
                                </div>
                                <small class="text-muted">
                                    {{ $scheduledEmail->sent_count }}/{{ $scheduledEmail->total_recipients }} envoyés
                                    @if($scheduledEmail->failed_count > 0)
                                        <span class="text-danger">({{ $scheduledEmail->failed_count }} échecs)</span>
                                    @endif
                                </small>
                            </dd>
                        @endif

                        @if($scheduledEmail->error_message)
                            <dt class="col-sm-4">Erreur</dt>
                            <dd class="col-sm-8">
                                <div class="alert alert-danger mb-0">
                                    {{ $scheduledEmail->error_message }}
                                </div>
                            </dd>
                        @endif

                        @if($scheduledEmail->started_at)
                            <dt class="col-sm-4">Démarré le</dt>
                            <dd class="col-sm-8">{{ $scheduledEmail->started_at->format('d/m/Y à H:i') }}</dd>
                        @endif

                        @if($scheduledEmail->completed_at)
                            <dt class="col-sm-4">Terminé le</dt>
                            <dd class="col-sm-8">{{ $scheduledEmail->completed_at->format('d/m/Y à H:i') }}</dd>
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
                        {!! $scheduledEmail->content !!}
                    </div>
                    
                    @if($scheduledEmail->attachments && count($scheduledEmail->attachments) > 0)
                        <div class="mt-3">
                            <h6>Pièces jointes:</h6>
                            <ul>
                                @foreach($scheduledEmail->attachments as $attachment)
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
                        <i class="fas fa-info-circle me-2"></i>Informations
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Créé par</dt>
                        <dd class="col-sm-6">{{ $scheduledEmail->creator->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-6">Date de création</dt>
                        <dd class="col-sm-6">{{ $scheduledEmail->created_at->format('d/m/Y à H:i') }}</dd>

                        <dt class="col-sm-6">Dernière mise à jour</dt>
                        <dd class="col-sm-6">{{ $scheduledEmail->updated_at->format('d/m/Y à H:i') }}</dd>
                    </dl>

                    @if($scheduledEmail->metadata)
                        <hr class="my-3">
                        <h6 class="mb-2">Métadonnées</h6>
                        <dl class="mb-0">
                            @foreach($scheduledEmail->metadata as $key => $value)
                            <dt class="text-muted small">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="mb-2">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                            @endforeach
                        </dl>
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
</style>
@endpush
