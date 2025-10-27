@extends('layouts.app')

@section('title', 'Messages - Herime Academie')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 fw-bold mb-1">Messages</h1>
                    <p class="text-muted mb-0">Communiquez avec vos formateurs et étudiants</p>
                </div>
                <div>
                    <a href="{{ route('messages.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nouveau message
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Messages Tabs -->
        <div class="col-12">
            <ul class="nav nav-tabs" id="messagesTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="received-tab" data-bs-toggle="tab" data-bs-target="#received" type="button" role="tab">
                        Messages reçus
                        @if($unreadCount > 0)
                        <span class="badge bg-danger ms-2">{{ $unreadCount }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab">
                        Messages envoyés
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="messagesTabContent">
                <!-- Received Messages -->
                <div class="tab-pane fade show active" id="received" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            @if($receivedMessages->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($receivedMessages as $message)
                                    <div class="list-group-item border-0 py-3 {{ !$message->is_read ? 'bg-light' : '' }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-1">
                                                @if(!$message->is_read)
                                                <div class="unread-indicator bg-primary rounded-circle" style="width: 10px; height: 10px;"></div>
                                                @endif
                                            </div>
                                            <div class="col-md-2">
                                                <img src="{{ $message->sender->avatar ? $1->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($message->sender->name) . '&background=003366&color=fff' }}" 
                                                     alt="{{ $message->sender->name }}" class="rounded-circle" width="40" height="40">
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="mb-1 fw-bold {{ !$message->is_read ? 'text-dark' : 'text-muted' }}">
                                                    {{ $message->sender->name }}
                                                </h6>
                                                <p class="mb-1 {{ !$message->is_read ? 'fw-bold' : '' }}">
                                                    {{ $message->subject }}
                                                </p>
                                                <p class="text-muted small mb-0">
                                                    {{ Str::limit(strip_tags($message->message), 100) }}
                                                </p>
                                                @if($message->course)
                                                <small class="text-primary">
                                                    <i class="fas fa-book me-1"></i>{{ $message->course->title }}
                                                </small>
                                                @endif
                                            </div>
                                            <div class="col-md-2">
                                                <small class="text-muted">
                                                    {{ $message->created_at->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="{{ route('messages.show', $message->id) }}">
                                                            <i class="fas fa-eye me-2"></i>Lire
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="{{ route('messages.show', $message->id) }}#reply">
                                                            <i class="fas fa-reply me-2"></i>Répondre
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteMessage({{ $message->id }})">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <!-- Pagination -->
                                <div class="p-3">
                                    {{ $receivedMessages->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Aucun message reçu</h5>
                                    <p class="text-muted">Vous n'avez pas encore reçu de messages</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sent Messages -->
                <div class="tab-pane fade" id="sent" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            @if($sentMessages->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($sentMessages as $message)
                                    <div class="list-group-item border-0 py-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <img src="{{ $message->receiver->avatar ? $1->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($message->receiver->name) . '&background=003366&color=fff' }}" 
                                                     alt="{{ $message->receiver->name }}" class="rounded-circle" width="40" height="40">
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="mb-1 fw-bold text-muted">
                                                    À : {{ $message->receiver->name }}
                                                </h6>
                                                <p class="mb-1 fw-bold">
                                                    {{ $message->subject }}
                                                </p>
                                                <p class="text-muted small mb-0">
                                                    {{ Str::limit(strip_tags($message->message), 100) }}
                                                </p>
                                                @if($message->course)
                                                <small class="text-primary">
                                                    <i class="fas fa-book me-1"></i>{{ $message->course->title }}
                                                </small>
                                                @endif
                                            </div>
                                            <div class="col-md-2">
                                                <small class="text-muted">
                                                    {{ $message->created_at->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('messages.show', $message->id) }}" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteMessage({{ $message->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <!-- Pagination -->
                                <div class="p-3">
                                    {{ $sentMessages->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-paper-plane fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Aucun message envoyé</h5>
                                    <p class="text-muted">Vous n'avez pas encore envoyé de messages</p>
                                    <a href="{{ route('messages.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Envoyer un message
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteMessage(messageId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
        fetch(`/messages/${messageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue lors de la suppression.');
        });
    }
}

// Mark message as read when clicked
document.querySelectorAll('.list-group-item').forEach(item => {
    item.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown') && !e.target.closest('a')) {
            const messageId = this.dataset.messageId;
            if (messageId) {
                fetch(`/messages/${messageId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                });
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.unread-indicator {
    margin-top: 15px;
}

.list-group-item:hover {
    background-color: #f8f9fa !important;
}

.nav-tabs .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    border-bottom-color: #003366;
    color: #003366;
    background-color: transparent;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #003366;
    color: #003366;
}

.dropdown-toggle::after {
    display: none;
}

.pagination {
    justify-content: center;
}

.page-link {
    color: #003366;
    border-color: #dee2e6;
}

.page-link:hover {
    color: #ffcc33;
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.page-item.active .page-link {
    background-color: #003366;
    border-color: #003366;
}
</style>
@endpush