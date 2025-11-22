@extends('layouts.admin')

@section('title', 'Détails de l\'email programmé')
@section('admin-title', 'Détails de l\'email programmé')
@section('admin-subtitle', 'Consultez les détails complets de l\'email programmé')
@section('admin-actions')
    <a href="{{ route('admin.emails.scheduled') }}" class="btn btn-outline-secondary">
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
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <div class="row">
                <div class="col-md-8">
                    <div class="admin-form-card mb-4">
                        <h5 class="mb-3"><i class="fas fa-envelope me-2"></i>Informations de l'email programmé</h5>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Sujet</label>
                            <div><strong>{{ $scheduledEmail->subject }}</strong></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Type de destinataires</label>
                            <div>
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
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Nombre de destinataires</label>
                            <div><strong>{{ $scheduledEmail->total_recipients }}</strong> destinataire(s)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Programmé pour</label>
                            <div><strong>{{ $scheduledEmail->scheduled_at->format('d/m/Y à H:i') }}</strong></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Statut</label>
                            <div>
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
                            </div>
                        </div>
                        
                        @if($scheduledEmail->status === 'processing' || $scheduledEmail->status === 'completed')
                        <div class="mb-3">
                            <label class="form-label text-muted">Progression</label>
                            <div class="progress mb-2" style="height: 25px;">
                                @php
                                    $percentage = $scheduledEmail->total_recipients > 0 
                                        ? ($scheduledEmail->sent_count / $scheduledEmail->total_recipients) * 100 
                                        : 0;
                                @endphp
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
                        </div>
                        @endif
                        
                        @if($scheduledEmail->error_message)
                        <div class="alert alert-danger">
                            <strong>Erreur:</strong> {{ $scheduledEmail->error_message }}
                        </div>
                        @endif
                        
                        @if($scheduledEmail->started_at)
                        <div class="mb-2">
                            <small class="text-muted">Démarré le</small><br>
                            <strong>{{ $scheduledEmail->started_at->format('d/m/Y à H:i') }}</strong>
                        </div>
                        @endif
                        
                        @if($scheduledEmail->completed_at)
                        <div class="mb-2">
                            <small class="text-muted">Terminé le</small><br>
                            <strong>{{ $scheduledEmail->completed_at->format('d/m/Y à H:i') }}</strong>
                        </div>
                        @endif
                    </div>
                    
                    <div class="admin-form-card">
                        <h5 class="mb-3"><i class="fas fa-file-alt me-2"></i>Contenu de l'email</h5>
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
                </div>
                
                <div class="col-md-4">
                    <div class="admin-form-card">
                        <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Informations</h5>
                        
                        <div class="mb-3">
                            <small class="text-muted">Créé par</small><br>
                            <strong>{{ $scheduledEmail->creator->name ?? 'N/A' }}</strong>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Date de création</small><br>
                            <strong>{{ $scheduledEmail->created_at->format('d/m/Y à H:i') }}</strong>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Dernière mise à jour</small><br>
                            <strong>{{ $scheduledEmail->updated_at->format('d/m/Y à H:i') }}</strong>
                        </div>
                        
                        @if($scheduledEmail->metadata)
                        <hr>
                        <h6 class="mb-2">Métadonnées</h6>
                        <dl class="mb-0">
                            @foreach($scheduledEmail->metadata as $key => $value)
                            <dt class="text-muted small">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="mb-2">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                            @endforeach
                        </dl>
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
</style>
@endpush

