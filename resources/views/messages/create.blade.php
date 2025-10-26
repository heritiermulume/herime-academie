@extends('layouts.app')

@section('title', 'Nouveau message - Herime Academie')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('messages.index') }}">Messages</a></li>
                    <li class="breadcrumb-item active">Nouveau message</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 fw-bold mb-1">Nouveau message</h1>
                    <p class="text-muted mb-0">Envoyez un message à un formateur ou un étudiant</p>
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
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('messages.store') }}" method="POST">
                        @csrf
                        
                        <!-- Recipient -->
                        <div class="mb-4">
                            <label for="receiver_id" class="form-label fw-bold">Destinataire <span class="text-danger">*</span></label>
                            <select class="form-select @error('receiver_id') is-invalid @enderror" id="receiver_id" name="receiver_id" required>
                                <option value="">Sélectionner un destinataire</option>
                                @foreach($recipients as $recipient)
                                <option value="{{ $recipient->id }}" {{ old('receiver_id') == $recipient->id ? 'selected' : '' }}>
                                    {{ $recipient->name }} 
                                    @if($recipient->role === 'instructor')
                                        <span class="text-muted">(Formateur)</span>
                                    @elseif($recipient->role === 'student')
                                        <span class="text-muted">(Étudiant)</span>
                                    @endif
                                </option>
                                @endforeach
                            </select>
                            @error('receiver_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Course (Optional) -->
                        <div class="mb-4">
                            <label for="course_id" class="form-label fw-bold">Cours (optionnel)</label>
                            <select class="form-select @error('course_id') is-invalid @enderror" id="course_id" name="course_id">
                                <option value="">Sélectionner un cours</option>
                                @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->title }}
                                </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Sélectionnez un cours si votre message concerne un cours spécifique</div>
                        </div>

                        <!-- Subject -->
                        <div class="mb-4">
                            <label for="subject" class="form-label fw-bold">Sujet <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                   id="subject" name="subject" value="{{ old('subject') }}" 
                                   placeholder="Sujet de votre message" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div class="mb-4">
                            <label for="message" class="form-label fw-bold">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" name="message" rows="8" 
                                      placeholder="Tapez votre message ici..." required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Soyez clair et précis dans votre message</div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer le message
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tips -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-lightbulb text-warning me-2"></i>Conseils pour un bon message
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Soyez poli et respectueux
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Utilisez un sujet clair et descriptif
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Mentionnez le cours concerné si applicable
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Posez des questions précises
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            Relisez votre message avant l'envoi
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-update course list based on selected recipient
document.getElementById('receiver_id').addEventListener('change', function() {
    const recipientId = this.value;
    const courseSelect = document.getElementById('course_id');
    
    if (recipientId) {
        // Enable course selection
        courseSelect.disabled = false;
        
        // You could add AJAX here to load courses specific to the recipient
        // For now, we'll just enable all courses
    } else {
        // Disable course selection
        courseSelect.disabled = true;
        courseSelect.value = '';
    }
});

// Character counter for message
document.getElementById('message').addEventListener('input', function() {
    const maxLength = 2000;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    // Create or update counter
    let counter = document.getElementById('message-counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'message-counter';
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

// Auto-save draft
let draftTimer;
document.querySelectorAll('input, textarea, select').forEach(element => {
    element.addEventListener('input', function() {
        clearTimeout(draftTimer);
        draftTimer = setTimeout(() => {
            const formData = new FormData(document.querySelector('form'));
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            // Save draft to localStorage
            const draft = {
                receiver_id: formData.get('receiver_id'),
                course_id: formData.get('course_id'),
                subject: formData.get('subject'),
                message: formData.get('message')
            };
            
            localStorage.setItem('message_draft', JSON.stringify(draft));
        }, 2000);
    });
});

// Load draft on page load
window.addEventListener('load', function() {
    const draft = localStorage.getItem('message_draft');
    if (draft) {
        const draftData = JSON.parse(draft);
        document.getElementById('receiver_id').value = draftData.receiver_id || '';
        document.getElementById('course_id').value = draftData.course_id || '';
        document.getElementById('subject').value = draftData.subject || '';
        document.getElementById('message').value = draftData.message || '';
    }
});

// Clear draft on successful send
document.querySelector('form').addEventListener('submit', function() {
    localStorage.removeItem('message_draft');
});
</script>
@endpush

@push('styles')
<style>
.form-label {
    color: #003366;
}

.form-control:focus,
.form-select:focus {
    border-color: #003366;
    box-shadow: 0 0 0 0.2rem rgba(0, 51, 102, 0.25);
}

.btn-primary {
    background-color: #003366;
    border-color: #003366;
}

.btn-primary:hover {
    background-color: #004080;
    border-color: #004080;
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
</style>
@endpush