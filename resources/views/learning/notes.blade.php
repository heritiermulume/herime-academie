@extends('layouts.app')

@section('title', 'Mes notes - ' . $lesson->title)

@push('styles')
<style>
:root {
    --learning-bg: #003366;
    --learning-card: rgba(0, 51, 102, 0.9);
    --learning-highlight: #ffcc33;
    --learning-muted: #94a3b8;
}

body {
    background: var(--learning-bg);
    color: #f8fafc;
}

.notes-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem;
}

.notes-header {
    background: var(--learning-card);
    border: 1px solid rgba(255, 204, 51, 0.15);
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.note-card {
    background: rgba(0, 51, 102, 0.75);
    border: 1px solid rgba(255, 204, 51, 0.15);
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.note-card p {
    color: #cbd5e1;
    margin-bottom: 0;
}

.note-card small,
.note-card .text-muted {
    color: #94a3b8 !important;
}

.note-card i {
    color: #94a3b8;
}

.note-card strong {
    color: #f8fafc;
}

.note-edit-form {
    display: none;
}

.note-edit-form.active {
    display: block;
}

.note-content-display {
    display: block;
}

.note-content-display.hidden {
    display: none;
}

.pagination {
    justify-content: center;
}

.pagination .page-link {
    background: rgba(0, 51, 102, 0.75);
    border-color: rgba(255, 204, 51, 0.25);
    color: #ffcc33;
}

.pagination .page-link:hover {
    background: rgba(255, 204, 51, 0.1);
    border-color: #ffcc33;
    color: #ffcc33;
}

.pagination .page-item.active .page-link {
    background: #ffcc33;
    border-color: #ffcc33;
    color: #003366;
}

.alert-success {
    background-color: rgba(40, 167, 69, 0.2);
    border-color: rgba(40, 167, 69, 0.5);
    color: #90ee90;
}

/* Styles responsives pour mobile - utiliser les mêmes petites tailles que desktop */
@media (max-width: 768px) {
    .notes-container {
        padding: 1rem;
    }
    
    .notes-header {
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .notes-header h2 {
        font-size: 1.25rem !important;
    }
    
    .note-card {
        padding: 1rem;
        margin-bottom: 0.75rem;
    }
    
    /* Utiliser les tailles Bootstrap standard pour btn-sm (comme desktop) */
    .notes-container .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }
    
    /* Utiliser les tailles Bootstrap standard pour les champs */
    .notes-container .form-control,
    .notes-container textarea,
    .notes-container input {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    /* Réduire la taille des dates/heures */
    .note-card small {
        font-size: 0.75rem !important;
    }
    
    .note-card small i {
        font-size: 0.7rem !important;
    }
    
    /* Boutons d'action compacts - uniquement icônes sur mobile */
    .note-card .btn-sm {
        padding: 0.25rem !important;
        font-size: 0.75rem !important;
        min-width: 28px !important;
        width: 28px !important;
        height: 28px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        line-height: 1 !important;
    }
    
    .note-card .btn-sm i {
        font-size: 0.7rem !important;
        margin: 0 !important;
        line-height: 1 !important;
    }
    
    /* Masquer le texte dans les boutons, garder seulement les icônes */
    .note-card .btn-sm > *:not(i) {
        display: none !important;
    }
    
    /* S'assurer que seul l'icône est visible */
    .note-card .btn-sm {
        text-indent: 0 !important;
    }
    
    /* Réduire l'espacement entre les boutons */
    .note-card .d-flex.gap-2 {
        gap: 0.3rem !important;
    }
    
    /* S'assurer que les boutons ne se dilatent pas */
    .note-card .d-flex.gap-2 > * {
        flex-shrink: 0 !important;
    }
    
    /* Forcer le conteneur des boutons à ne pas déborder */
    .note-card .d-flex.justify-content-between {
        flex-wrap: wrap !important;
    }
    
    .note-card .d-flex.gap-2 {
        flex-wrap: nowrap !important;
        min-width: 0 !important;
    }
    
    /* Formulaires d'édition - boutons avec largeur relative au texte */
    .note-edit-form .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
        width: auto !important;
        min-width: auto !important;
        height: auto !important;
    }
    
    /* S'assurer que le texte est visible dans les boutons des formulaires */
    .note-edit-form .btn-sm > *:not(i) {
        display: inline !important;
    }
    
    .note-edit-form .btn-sm i {
        margin-right: 0.25rem !important;
    }
    
    .note-edit-form textarea {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    /* Header */
    .notes-header .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }
    
    .notes-header textarea,
    .notes-header .form-control {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
}

/* Styles pour très petits écrans - garder les mêmes petites tailles */
@media (max-width: 480px) {
    .notes-container {
        padding: 0.75rem;
    }
    
    .notes-header {
        padding: 0.75rem;
    }
    
    .note-card {
        padding: 0.75rem;
    }
    
    /* Garder les mêmes tailles Bootstrap btn-sm */
    .notes-container .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }
    
    /* Garder les mêmes tailles pour les champs */
    .notes-container .form-control,
    .notes-container textarea,
    .notes-container input {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    .note-card small {
        font-size: 0.7rem !important;
    }
    
    /* Boutons d'action encore plus compacts - uniquement icônes */
    .note-card .btn-sm {
        padding: 0.2rem !important;
        font-size: 0.7rem !important;
        min-width: 24px !important;
        width: 24px !important;
        height: 24px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        line-height: 1 !important;
    }
    
    .note-card .btn-sm i {
        font-size: 0.65rem !important;
        margin: 0 !important;
        line-height: 1 !important;
    }
    
    /* Masquer le texte dans les boutons */
    .note-card .btn-sm > *:not(i) {
        display: none !important;
    }
    
    /* Réduire encore plus l'espacement */
    .note-card .d-flex.gap-2 {
        gap: 0.25rem !important;
    }
    
    .note-card p {
        font-size: 0.875rem !important;
    }
    
    .note-edit-form .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
        width: auto !important;
        min-width: auto !important;
        height: auto !important;
    }
    
    /* S'assurer que le texte est visible dans les boutons des formulaires */
    .note-edit-form .btn-sm > *:not(i) {
        display: inline !important;
    }
    
    .note-edit-form .btn-sm i {
        margin-right: 0.25rem !important;
    }
    
    .note-edit-form textarea {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    .notes-header .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }
    
    .notes-header textarea,
    .notes-header .form-control {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
}

/* Styles pour le modal de confirmation */
#confirmModal .modal-content {
    background: rgba(0, 51, 102, 0.95) !important;
    color: #f8fafc !important;
}

#confirmModal .modal-body {
    background: rgba(0, 51, 102, 0.95) !important;
    color: #f8fafc !important;
}

#confirmModal .modal-body p {
    color: #f8fafc !important;
}

#confirmModal .modal-footer {
    background: rgba(0, 51, 102, 0.95) !important;
}

/* Animation pour les notifications toast */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translate(-50%, -20px);
    }
    to {
        opacity: 1;
        transform: translate(-50%, 0);
    }
}
</style>
@endpush

@section('content')
<div class="notes-container">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="notes-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="mb-1" style="color: #f8fafc;">Mes notes</h2>
                <p class="text-muted mb-0">
                    <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $lesson->id]) }}" style="color: #ffcc33;">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la leçon
                    </a>
                </p>
            </div>
        </div>
        <div class="small" style="color: #cbd5e1;">
            <strong style="color: #f8fafc;">Leçon:</strong> {{ $lesson->title }}<br>
            <strong style="color: #f8fafc;">Cours:</strong> {{ $course->title }}
        </div>
    </div>

    @if($notes->count() > 0)
        @foreach($notes as $note)
            <div class="note-card" id="note-{{ $note->id }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small style="color: #94a3b8;">
                        <i class="far fa-calendar me-1" style="color: #94a3b8;"></i>
                        {{ $note->created_at->format('d/m/Y à H:i') }}
                    </small>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="toggleEditNote({{ $note->id }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" 
                              class="d-inline delete-note-form"
                              data-note-id="{{ $note->id }}"
                              data-action="{{ route('learning.notes.destroy', ['course' => $course->slug, 'lesson' => $lesson->id, 'note' => $note->id]) }}"
                              onsubmit="return false;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-outline-danger delete-note-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="note-content-display" id="note-content-{{ $note->id }}">
                    <p>{{ $note->content }}</p>
                </div>
                <form class="note-edit-form" id="note-edit-form-{{ $note->id }}" 
                      action="{{ route('learning.notes.update', ['course' => $course->slug, 'lesson' => $lesson->id, 'note' => $note->id]) }}" 
                      method="POST">
                    @csrf
                    @method('PUT')
                    <textarea name="content" class="form-control mb-2" rows="4" required style="background: rgba(255,255,255,0.1); border-color: rgba(255,204,51,0.25); color: #fff;">{{ $note->content }}</textarea>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-save me-1"></i>Enregistrer
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleEditNote({{ $note->id }})">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        @endforeach

        <div class="mt-4">
            {{ $notes->links() }}
        </div>
    @else
        <div class="note-card text-center">
            <p class="text-muted mb-0">Aucune note pour cette leçon.</p>
        </div>
    @endif
</div>

<!-- Modal de confirmation moderne -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                <h5 class="modal-title text-white fw-bold" id="confirmModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0 fs-5" id="confirmModalMessage"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-danger" id="confirmModalConfirmBtn">
                    <i class="fas fa-trash me-2"></i>Confirmer
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Fonction pour afficher une notification toast moderne
function showToast(message, type = 'success') {
    // Types: success, error, info, warning
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-triangle'
    };
    
    const colors = {
        success: {
            bg: 'bg-success',
            border: 'border-success',
            icon: 'text-success'
        },
        error: {
            bg: 'bg-danger',
            border: 'border-danger',
            icon: 'text-danger'
        },
        info: {
            bg: 'bg-info',
            border: 'border-info',
            icon: 'text-info'
        },
        warning: {
            bg: 'bg-warning',
            border: 'border-warning',
            icon: 'text-warning'
        }
    };
    
    const colorScheme = colors[type] || colors.success;
    const icon = icons[type] || icons.success;
    
    // Créer le conteneur toast
    const toast = document.createElement('div');
    toast.className = 'position-fixed top-0 start-50 translate-middle-x mt-3';
    toast.style.zIndex = '9999';
    toast.style.animation = 'slideDown 0.3s ease-out';
    
    toast.innerHTML = `
        <div class="alert ${colorScheme.bg} alert-dismissible fade show shadow-lg border-0" role="alert" style="min-width: 320px; max-width: 500px; border-radius: 12px; backdrop-filter: blur(10px);">
            <div class="d-flex align-items-center">
                <i class="fas ${icon} me-3 fs-5 ${colorScheme.icon}" style="color: white !important;"></i>
                <div class="flex-grow-1 text-white fw-semibold">${message}</div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animation d'entrée
    setTimeout(() => {
        toast.querySelector('.alert').classList.add('show');
    }, 10);
    
    // Supprimer automatiquement après 4 secondes
    setTimeout(() => {
        const alert = toast.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    }, 4000);
    
    // Supprimer au clic sur le bouton de fermeture
    toast.querySelector('.btn-close')?.addEventListener('click', () => {
        const alert = toast.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    });
}

function toggleEditNote(noteId) {
    const contentDisplay = document.getElementById('note-content-' + noteId);
    const editForm = document.getElementById('note-edit-form-' + noteId);
    
    if (contentDisplay && editForm) {
        contentDisplay.classList.toggle('hidden');
        editForm.classList.toggle('active');
    }
}

// Fonction pour afficher un modal de confirmation moderne
function showConfirmModal(message, onConfirm, confirmText = 'Confirmer') {
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const messageEl = document.getElementById('confirmModalMessage');
    const confirmBtn = document.getElementById('confirmModalConfirmBtn');
    
    messageEl.textContent = message;
    confirmBtn.innerHTML = `<i class="fas fa-trash me-2"></i>${confirmText}`;
    
    // Supprimer les anciens event listeners
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    // Ajouter le nouveau event listener
    document.getElementById('confirmModalConfirmBtn').addEventListener('click', function() {
        modal.hide();
        if (onConfirm) onConfirm();
    });
    
    modal.show();
}

// Intercepter les clics sur les boutons de suppression
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-note-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const formElement = this.closest('.delete-note-form');
            if (!formElement) return;
            
            const noteId = formElement.dataset.noteId;
            const actionUrl = formElement.dataset.action;
            const noteCard = document.getElementById('note-' + noteId);
            
            showConfirmModal(
                'Êtes-vous sûr de vouloir supprimer cette note ? Cette action est irréversible.',
                function() {
                    // Désactiver le bouton pendant la requête
                    const submitBtn = formElement.querySelector('.delete-note-btn');
                    const originalHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    
                    // Préparer les données du formulaire
                    const formData = new FormData(formElement);
                    
                    // Faire la requête AJAX
                    fetch(actionUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': formElement.querySelector('input[name="_token"]').value,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => {
                        // Vérifier si la réponse est OK
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        // Vérifier si la réponse est du JSON
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            throw new Error('La réponse n\'est pas du JSON');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Afficher la notification toast
                            showToast(data.message, 'success');
                            
                            // Supprimer la carte de la note de la page
                            if (noteCard) {
                                noteCard.style.transition = 'opacity 0.3s ease-out';
                                noteCard.style.opacity = '0';
                                setTimeout(() => {
                                    noteCard.remove();
                                    
                                    // Vérifier s'il reste des notes
                                    const remainingNotes = document.querySelectorAll('.note-card');
                                    if (remainingNotes.length === 0) {
                                        // Rediriger vers la page des notes au lieu de recharger
                                        setTimeout(() => {
                                            window.location.href = '{{ route("learning.notes.all", ["course" => $course->slug, "lesson" => $lesson->id]) }}';
                                        }, 500);
                                    }
                                }, 300);
                            } else {
                                // Si on ne trouve pas la carte, rediriger vers la page des notes
                                setTimeout(() => {
                                    window.location.href = '{{ route("learning.notes.all", ["course" => $course->slug, "lesson" => $lesson->id]) }}';
                                }, 500);
                            }
                        } else {
                            showToast(data.message || 'Une erreur est survenue', 'error');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalHtml;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Une erreur est survenue lors de la suppression', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHtml;
                    });
                },
                'Supprimer'
            );
        });
    });
});
</script>
@endpush
@endsection

