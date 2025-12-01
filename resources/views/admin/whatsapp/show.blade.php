@extends('layouts.admin')

@section('title', 'Détails du message WhatsApp')
@section('admin-title', 'Détails du message WhatsApp')
@section('admin-subtitle', 'Consultez les détails complets du message WhatsApp')
@section('admin-actions')
    <a href="{{ route('admin.announcements') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('admin-content')
    <div class="row g-4">
        <div class="col-md-8">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fab fa-whatsapp me-2"></i>Informations du message
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Destinataire</dt>
                        <dd class="col-sm-8">
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $recipientUser = $sentWhatsAppMessage->recipient_user ?? null;
                                    $avatarUrl = $recipientUser ? $recipientUser->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($sentWhatsAppMessage->recipient_name ?? 'N/A') . '&background=25d366&color=fff&size=128';
                                @endphp
                                <div class="whatsapp-avatar-container" style="width: 40px; height: 40px; min-width: 40px; max-width: 40px; min-height: 40px; max-height: 40px; flex-shrink: 0; border-radius: 50% !important; overflow: hidden !important; display: inline-block; position: relative;">
                                    <img src="{{ $avatarUrl }}" 
                                         alt="{{ $sentWhatsAppMessage->recipient_name ?? 'N/A' }}" 
                                         class="whatsapp-avatar"
                                         style="width: 100% !important; height: 100% !important; object-fit: cover !important; display: block !important; border-radius: 50% !important;"
                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($sentWhatsAppMessage->recipient_name ?? 'N/A') }}&background=25d366&color=fff&size=128'">
                                </div>
                                <div>
                                    <strong>{{ $sentWhatsAppMessage->recipient_name ?? 'N/A' }}</strong><br>
                                    <span class="text-muted">
                                        <i class="fas fa-phone me-1"></i>{{ $sentWhatsAppMessage->recipient_phone }}
                                    </span>
                                </div>
                            </div>
                        </dd>

                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-info">{{ ucfirst($sentWhatsAppMessage->type) }}</span>
                        </dd>

                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">
                            @if($sentWhatsAppMessage->status === 'sent')
                                <span class="badge bg-success">Envoyé</span>
                            @elseif($sentWhatsAppMessage->status === 'delivered')
                                <span class="badge bg-primary">Livré</span>
                            @elseif($sentWhatsAppMessage->status === 'read')
                                <span class="badge bg-info">Lu</span>
                            @elseif($sentWhatsAppMessage->status === 'failed')
                                <span class="badge bg-danger">Échoué</span>
                            @else
                                <span class="badge bg-warning">En attente</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Date d'envoi</dt>
                        <dd class="col-sm-8">
                            <i class="fas fa-calendar me-2"></i>
                            {{ $sentWhatsAppMessage->sent_at ? $sentWhatsAppMessage->sent_at->format('d/m/Y à H:i:s') : ($sentWhatsAppMessage->created_at->format('d/m/Y à H:i:s')) }}
                        </dd>

                        @if($sentWhatsAppMessage->message_id)
                            <dt class="col-sm-4">ID du message</dt>
                            <dd class="col-sm-8">
                                <code class="text-muted">{{ $sentWhatsAppMessage->message_id }}</code>
                            </dd>
                        @endif

                        @if($sentWhatsAppMessage->error_message)
                            <dt class="col-sm-4">Erreur</dt>
                            <dd class="col-sm-8">
                                <div class="alert alert-danger mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>
                                        @if(isset($sentWhatsAppMessage->translated_error))
                                            {{ $sentWhatsAppMessage->translated_error }}
                                        @else
                                            {{ $sentWhatsAppMessage->error_message }}
                                        @endif
                                    </strong>
                                    @if(isset($sentWhatsAppMessage->translated_error) && $sentWhatsAppMessage->translated_error !== $sentWhatsAppMessage->error_message)
                                        <br><small class="text-muted mt-1 d-block">
                                            <i class="fas fa-info-circle me-1"></i>Message original : {{ $sentWhatsAppMessage->error_message }}
                                        </small>
                                    @endif
                                </div>
                            </dd>
                        @endif
                    </dl>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-comment me-2"></i>Contenu du message
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <div class="whatsapp-message-content">
                        <div class="whatsapp-message-bubble">
                            <p class="mb-0">{{ nl2br(e($sentWhatsAppMessage->message)) }}</p>
                        </div>
                    </div>
                    
                    @if($sentWhatsAppMessage->attachments && count($sentWhatsAppMessage->attachments) > 0)
                        <div class="mt-3">
                            <h6><i class="fas fa-paperclip me-2"></i>Pièces jointes:</h6>
                            <div class="row g-2">
                                @foreach($sentWhatsAppMessage->attachments as $attachment)
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body p-2">
                                            @if(is_string($attachment))
                                                @php
                                                    $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $attachment);
                                                @endphp
                                                @if($isImage)
                                                    <img src="{{ $attachment }}" alt="Pièce jointe" class="img-fluid rounded mb-2" style="max-height: 200px;">
                                                @endif
                                                <a href="{{ $attachment }}" target="_blank" class="text-decoration-none">
                                                    <i class="fas fa-external-link-alt me-1"></i>{{ $attachment }}
                                                </a>
                                            @elseif(is_array($attachment))
                                                <strong>{{ $attachment['name'] ?? 'Fichier' }}</strong><br>
                                                <a href="{{ $attachment['url'] ?? '#' }}" target="_blank" class="text-decoration-none">
                                                    <i class="fas fa-external-link-alt me-1"></i>Télécharger
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
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
                    @if($sentWhatsAppMessage->metadata)
                        <dl class="mb-0">
                            @foreach($sentWhatsAppMessage->metadata as $key => $value)
                            <dt class="text-muted small">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="mb-2">{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value }}</dd>
                            @endforeach
                        </dl>
                    @else
                        <p class="text-muted mb-0">Aucune métadonnée</p>
                    @endif

                    <hr class="my-3">

                    <dl class="row mb-0">
                        <dt class="col-sm-6">Date de création</dt>
                        <dd class="col-sm-6">{{ $sentWhatsAppMessage->created_at->format('d/m/Y à H:i') }}</dd>

                        <dt class="col-sm-6">Dernière mise à jour</dt>
                        <dd class="col-sm-6">{{ $sentWhatsAppMessage->updated_at->format('d/m/Y à H:i') }}</dd>
                    </dl>

                    @if($sentWhatsAppMessage->user)
                        <hr class="my-3">
                        <div>
                            <dt class="col-sm-12 mb-1"><strong>Utilisateur</strong></dt>
                            <dd class="col-sm-12">
                                <a href="{{ route('admin.users.show', $sentWhatsAppMessage->user) }}">
                                    {{ $sentWhatsAppMessage->user->name }}
                                </a>
                            </dd>
                        </div>
                    @endif

                    <hr class="my-3">

                    <div class="d-grid gap-2">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $sentWhatsAppMessage->recipient_phone) }}" 
                           target="_blank" 
                           class="btn btn-success">
                            <i class="fab fa-whatsapp me-2"></i>Ouvrir dans WhatsApp
                        </a>
                        <form action="{{ route('admin.whatsapp-messages.destroy', $sentWhatsAppMessage) }}" 
                              method="POST" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-2"></i>Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('styles')
<style>
/* Styles identiques à sent-show.blade.php */
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

@media (min-width: 992px) {
    .admin-panel__body {
        padding: 0.875rem 1rem;
    }
}

.admin-panel__body dl.row dd {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

/* Style pour la bulle de message WhatsApp */
.whatsapp-message-content {
    padding: 1rem;
}

.whatsapp-message-bubble {
    background-color: #dcf8c6;
    border-radius: 8px;
    padding: 1rem;
    max-width: 100%;
    position: relative;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.whatsapp-message-bubble::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 20px;
    width: 0;
    height: 0;
    border-top: 8px solid transparent;
    border-bottom: 8px solid transparent;
    border-right: 8px solid #dcf8c6;
}

.whatsapp-message-bubble p {
    margin: 0;
    color: #000;
    line-height: 1.5;
    word-wrap: break-word;
}

/* Avatar WhatsApp */
.whatsapp-avatar-container {
    width: 40px !important;
    height: 40px !important;
    min-width: 40px !important;
    max-width: 40px !important;
    min-height: 40px !important;
    max-height: 40px !important;
    flex-shrink: 0;
    border-radius: 50% !important;
    overflow: hidden !important;
    display: inline-block;
    position: relative;
}

.whatsapp-avatar {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
    border-radius: 50% !important;
}

/* Styles responsives */
@media (max-width: 991.98px) {
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
}

@media (max-width: 767.98px) {
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
}
</style>
@endpush

