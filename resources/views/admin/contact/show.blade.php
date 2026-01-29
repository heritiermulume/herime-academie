@extends('layouts.admin')

@section('title', 'Détails du message de contact')
@section('admin-title', 'Détails du message de contact')
@section('admin-subtitle', 'Consultez les détails complets du message de contact')
@section('admin-actions')
    <a href="{{ route('admin.announcements') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('admin-content')
    <div class="row g-4">
        <div class="col-md-8">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-envelope-open-text me-2"></i>Informations du contact
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nom</dt>
                        <dd class="col-sm-8">
                            <strong>{{ $contactMessage->name }}</strong>
                        </dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">
                            <a href="mailto:{{ $contactMessage->email }}" class="text-primary">
                                <i class="fas fa-envelope me-1"></i>{{ $contactMessage->email }}
                            </a>
                        </dd>

                        @if($contactMessage->phone)
                        <dt class="col-sm-4">Téléphone</dt>
                        <dd class="col-sm-8">
                            <a href="tel:{{ $contactMessage->phone }}" class="text-primary">
                                <i class="fas fa-phone me-1"></i>{{ $contactMessage->phone }}
                            </a>
                        </dd>
                        @endif

                        <dt class="col-sm-4">Sujet</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-primary">{{ $contactMessage->subject_label }}</span>
                        </dd>

                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">
                            @if($contactMessage->status === 'unread')
                                <span class="badge bg-warning">Non lu</span>
                            @elseif($contactMessage->status === 'read')
                                <span class="badge bg-success">Lu</span>
                            @elseif($contactMessage->status === 'replied')
                                <span class="badge bg-info">Répondu</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($contactMessage->status) }}</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Date de réception</dt>
                        <dd class="col-sm-8">
                            <i class="fas fa-calendar me-2"></i>
                            {{ $contactMessage->created_at->format('d/m/Y à H:i:s') }}
                        </dd>

                        @if($contactMessage->read_at)
                        <dt class="col-sm-4">Date de lecture</dt>
                        <dd class="col-sm-8">
                            <i class="fas fa-eye me-2"></i>
                            {{ $contactMessage->read_at->format('d/m/Y à H:i:s') }}
                        </dd>
                        @endif
                    </dl>
                </div>
            </section>

            <section class="admin-panel mt-4">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-comment me-2"></i>Message
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <div class="message-content" style="white-space: pre-wrap; line-height: 1.6;">
                        {{ $contactMessage->message }}
                    </div>
                </div>
            </section>
        </div>

        <div class="col-md-4">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-cog me-2"></i>Actions
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <div class="d-grid gap-2">
                        @if($contactMessage->status === 'unread')
                        <form action="{{ route('admin.contact-messages.mark-read', $contactMessage) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check me-2"></i>Marquer comme lu
                            </button>
                        </form>
                        @endif

                        <a href="mailto:{{ $contactMessage->email }}?subject=Re: {{ $contactMessage->subject_label }}" class="btn btn-primary w-100">
                            <i class="fas fa-reply me-2"></i>Répondre par email
                        </a>

                        <button type="button" class="btn btn-danger w-100" 
                                data-action="{{ route('admin.contact-messages.destroy', $contactMessage) }}"
                                data-name="{{ $contactMessage->name }}"
                                onclick="openDeleteContactModal(this)">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
async function openDeleteContactModal(button) {
    const action = button?.dataset?.action;
    if (!action) {
        console.error('Aucune action de suppression fournie.');
        return;
    }

    const name = button.dataset.name || '';
    const message = name
        ? `Êtes-vous sûr de vouloir supprimer le message de contact de « ${name} » ? Cette action est irréversible.`
        : `Êtes-vous sûr de vouloir supprimer ce message de contact ? Cette action est irréversible.`;
    
    const confirmed = await showModernConfirmModal(message, {
        title: 'Supprimer le message de contact',
        confirmButtonText: 'Supprimer',
        confirmButtonClass: 'btn-danger',
        icon: 'fa-exclamation-triangle'
    });
    
    if (confirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        
        // Ajouter le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
        
        // Ajouter la méthode DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function viewContactMessage(id) {
    window.location.href = '{{ route("admin.contact-messages.show", ":id") }}'.replace(':id', id);
}
</script>
@endpush
