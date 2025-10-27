@extends('layouts.app')

@section('title', $message->subject . ' - Messages Herime Academie')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('messages.index') }}">Messages</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($message->subject, 30) }}</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 fw-bold mb-1">{{ $message->subject }}</h1>
                    <p class="text-muted mb-0">
                        @if($message->sender_id === auth()->id())
                            Message envoyé à {{ $message->receiver->name }}
                        @else
                            Message reçu de {{ $message->sender->name }}
                        @endif
                    </p>
                </div>
                <div>
                    <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour aux messages
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Message Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <img src="{{ $message->sender->avatar ? $message->sender->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($message->sender->name) . '&background=003366&color=fff' }}" 
                                     alt="{{ $message->sender->name }}" class="rounded-circle me-3" width="50" height="50">
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $message->sender->name }}</h6>
                                    <small class="text-muted">
                                        @if($message->sender->role === 'instructor')
                                            Formateur
                                        @elseif($message->sender->role === 'student')
                                            Étudiant
                                        @elseif($message->sender->role === 'admin')
                                            Administrateur
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                {{ $message->created_at->format('d/m/Y à H:i') }}
                            </small>
                            @if($message->course)
                            <br>
                            <small class="text-primary">
                                <i class="fas fa-book me-1"></i>
                                {{ $message->course->title }}
                            </small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="message-content">
                        {!! nl2br(e($message->message)) !!}
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($message->sender_id !== auth()->id())
                                <a href="{{ route('messages.create') }}?reply_to={{ $message->sender_id }}&subject={{ urlencode('Re: ' . $message->subject) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-reply me-1"></i>Répondre
                                </a>
                            @endif
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                @if($message->sender_id !== auth()->id())
                                <li><a class="dropdown-item" href="{{ route('messages.create') }}?reply_to={{ $message->sender_id }}&subject={{ urlencode('Re: ' . $message->subject) }}">
                                    <i class="fas fa-reply me-2"></i>Répondre
                                </a></li>
                                @endif
                                <li><a class="dropdown-item" href="{{ route('messages.create') }}?forward_to={{ $message->receiver_id }}&subject={{ urlencode('Fwd: ' . $message->subject) }}&message={{ urlencode($message->message) }}">
                                    <i class="fas fa-share me-2"></i>Transférer
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

            <!-- Reply Form -->
            @if($message->sender_id !== auth()->id())
            <div class="card border-0 shadow-sm" id="reply">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-reply me-2"></i>Répondre à {{ $message->sender->name }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('messages.reply', $message->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="reply_message" class="form-label fw-bold">Votre réponse</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="reply_message" name="message" rows="6" 
                                      placeholder="Tapez votre réponse ici..." required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('reply').style.display='none'">
                                <i class="fas fa-times me-2"></i>Annuler
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer la réponse
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Course Information -->
            @if($message->course)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-book me-2"></i>Cours concerné
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <img src="{{ $message->course->thumbnail ? $message->course->thumbnail : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=150&h=100&fit=crop' }}" 
                                 alt="{{ $message->course->title }}" class="img-fluid rounded">
                        </div>
                        <div class="col-md-9">
                            <h6 class="fw-bold mb-1">{{ $message->course->title }}</h6>
                            <p class="text-muted small mb-2">{{ Str::limit($message->course->short_description, 100) }}</p>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-primary">{{ $message->course->category->name }}</span>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>{{ $message->course->duration }} min
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-users me-1"></i>{{ number_format($message->course->students_count) }} étudiants
                                </small>
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('courses.show', $message->course->slug) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>Voir le cours
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
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
                window.location.href = '{{ route("messages.index") }}';
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

// Auto-resize textarea
document.getElementById('reply_message').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});

// Character counter for reply
document.getElementById('reply_message').addEventListener('input', function() {
    const maxLength = 2000;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    // Create or update counter
    let counter = document.getElementById('reply-counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'reply-counter';
        counter.className = 'form-text text-end';
        this.parentNode.appendChild(counter);
    }
    
    counter.textContent = `${currentLength}/${maxLength} caractères`;
    
    if (remaining < 100) {
        counter.className = 'form-text text-end text-warning';
    } else if (remaining < 0) {
        counter.className = 'form-text text-end text-danger';
    } else {
        counter.className = 'form-text text-end';
    }
});

// Show reply form when clicking reply button
document.querySelectorAll('a[href*="reply_to"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('reply').style.display = 'block';
        document.getElementById('reply_message').focus();
    });
});
</script>
@endpush

@push('styles')
<style>
.message-content {
    font-size: 1.1rem;
    line-height: 1.6;
    white-space: pre-wrap;
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.breadcrumb {
    background-color: transparent;
    padding: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: ">";
    color: #6c757d;
}

.breadcrumb-item a {
    color: #003366;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #ffcc33;
}

.breadcrumb-item.active {
    color: #6c757d;
}

.dropdown-toggle::after {
    display: none;
}

.btn-primary {
    background-color: #003366;
    border-color: #003366;
}

.btn-primary:hover {
    background-color: #004080;
    border-color: #004080;
}

.form-control:focus {
    border-color: #003366;
    box-shadow: 0 0 0 0.2rem rgba(0, 51, 102, 0.25);
}

#reply {
    display: none;
}
</style>
@endpush