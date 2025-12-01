@extends('layouts.app')

@section('title', 'Discussions - ' . $lesson->title)

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

.discussions-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem;
}

.discussions-header {
    background: var(--learning-card);
    border: 1px solid rgba(255, 204, 51, 0.15);
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.discussion-card {
    background: rgba(0, 51, 102, 0.75);
    border: 1px solid rgba(255, 204, 51, 0.15);
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.discussion-card .reply-card {
    background: rgba(0, 51, 102, 0.5);
    border: 1px solid rgba(255, 204, 51, 0.1);
    border-radius: 6px;
    padding: 1rem;
    margin-top: 1rem;
    margin-left: 2rem;
}

.discussion-card p {
    color: #cbd5e1;
    margin-bottom: 0;
}

.discussion-card small,
.discussion-card .text-muted {
    color: #94a3b8 !important;
}

.discussion-card i {
    color: #94a3b8;
}

.discussion-card strong {
    color: #f8fafc;
}

.discussion-edit-form {
    display: none;
}

.discussion-edit-form.active {
    display: block;
}

.discussion-content-display {
    display: block;
}

.discussion-content-display.hidden {
    display: none;
}

.reply-edit-form {
    display: none;
}

.reply-edit-form.active {
    display: block;
}

.reply-content-display {
    display: block;
}

.reply-content-display.hidden {
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
    .discussions-container {
        padding: 1rem;
    }
    
    .discussions-header {
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .discussions-header h2 {
        font-size: 1.25rem !important;
    }
    
    .discussion-card {
        padding: 1rem;
        margin-bottom: 0.75rem;
    }
    
    /* Utiliser les tailles Bootstrap standard pour btn-sm (comme desktop) */
    .discussions-container .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }
    
    /* Utiliser les tailles Bootstrap standard pour les champs */
    .discussions-container .form-control,
    .discussions-container textarea,
    .discussions-container input {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    .discussion-card .reply-card {
        padding: 0.75rem;
        margin-left: 0.5rem;
        margin-top: 0.75rem;
    }
    
    /* Réduire la taille des dates/heures */
    .discussion-card small,
    .reply-card small {
        font-size: 0.75rem !important;
    }
    
    /* Réduire la taille des noms d'utilisateurs */
    .discussion-card strong {
        font-size: 0.9rem !important;
    }
    
    .reply-card strong {
        font-size: 0.85rem !important;
    }
    
    /* Réduire la taille des badges */
    .discussion-card .badge {
        font-size: 0.7rem !important;
        padding: 0.2rem 0.4rem !important;
    }
    
    /* Boutons d'action compacts - uniquement icônes sur mobile */
    .discussion-card .btn-sm,
    .reply-card .btn-sm {
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
    
    .discussion-card .btn-sm i,
    .reply-card .btn-sm i {
        font-size: 0.7rem !important;
        margin: 0 !important;
        line-height: 1 !important;
    }
    
    /* Masquer le texte dans les boutons, garder seulement les icônes */
    .discussion-card .btn-sm > *:not(i):not(.badge):not(.likes-count),
    .reply-card .btn-sm > *:not(i):not(.badge):not(.likes-count) {
        display: none !important;
    }
    
    /* Exception pour le bouton like qui a un nombre - le rendre plus compact */
    .discussion-card .btn-sm.btn-outline-info:first-child,
    .discussion-card .like-discussion-btn {
        width: auto !important;
        min-width: 36px !important;
        max-width: 60px !important;
        padding: 0.25rem 0.35rem !important;
    }
    
    .discussion-card .btn-sm.btn-outline-info:first-child i,
    .discussion-card .like-discussion-btn i {
        margin-right: 0.2rem !important;
    }
    
    .discussion-card .btn-sm.btn-outline-info:first-child > *:not(i),
    .discussion-card .like-discussion-btn .likes-count {
        display: inline !important;
        font-size: 0.65rem !important;
    }
    
    /* Réduire l'espacement entre les boutons */
    .discussion-card .d-flex.gap-2,
    .reply-card .d-flex.gap-2 {
        gap: 0.3rem !important;
    }
    
    /* S'assurer que les boutons ne se dilatent pas */
    .discussion-card .d-flex.gap-2 > *,
    .reply-card .d-flex.gap-2 > * {
        flex-shrink: 0 !important;
    }
    
    /* Forcer le conteneur des boutons à ne pas déborder */
    .discussion-card .d-flex.justify-content-between,
    .reply-card .d-flex.justify-content-between {
        flex-wrap: wrap !important;
    }
    
    .discussion-card .d-flex.gap-2,
    .reply-card .d-flex.gap-2 {
        flex-wrap: nowrap !important;
        min-width: 0 !important;
    }
    
    /* S'assurer que le conteneur principal ne déborde pas */
    .discussion-card > .d-flex:first-child,
    .reply-card > .d-flex:first-child {
        overflow: hidden !important;
    }
    
    /* Formulaires d'édition - boutons avec largeur relative au texte */
    .discussion-edit-form .btn-sm,
    .reply-edit-form .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
        width: auto !important;
        min-width: auto !important;
        height: auto !important;
    }
    
    /* S'assurer que le texte est visible dans les boutons des formulaires */
    .discussion-edit-form .btn-sm > *:not(i):not(.badge),
    .reply-edit-form .btn-sm > *:not(i):not(.badge) {
        display: inline !important;
    }
    
    .discussion-edit-form .btn-sm i,
    .reply-edit-form .btn-sm i {
        margin-right: 0.25rem !important;
    }
    
    .discussion-edit-form textarea,
    .reply-edit-form textarea {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    /* Bouton "Répondre" avec largeur relative au texte - cibler tous les boutons dans les formulaires de réponse */
    .discussion-card form .btn-sm.btn-info,
    .discussion-card form button.btn-sm[type="submit"].btn-info {
        width: auto !important;
        min-width: auto !important;
        height: auto !important;
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }
    
    .discussion-card form .btn-sm.btn-info > *:not(i),
    .discussion-card form button.btn-sm[type="submit"].btn-info > *:not(i) {
        display: inline !important;
    }
    
    .discussion-card form .btn-sm.btn-info i,
    .discussion-card form button.btn-sm[type="submit"].btn-info i {
        margin-right: 0.25rem !important;
    }
    
    /* Réduire la taille du contenu */
    .discussion-card p {
        font-size: 0.9rem !important;
        line-height: 1.5 !important;
    }
    
    .reply-card p {
        font-size: 0.85rem !important;
        line-height: 1.4 !important;
    }
    
    /* Formulaires de réponse */
    .discussion-card form textarea {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    /* Sur mobile, afficher le bouton "Répondre" en dessous du textarea */
    .discussion-card form .d-flex.gap-2 {
        flex-direction: column !important;
    }
    
    .discussion-card form .d-flex.gap-2 textarea {
        width: 100% !important;
        margin-bottom: 0.5rem !important;
    }
    
    .discussion-card form .d-flex.gap-2 button {
        align-self: flex-start !important;
        width: auto !important;
    }
    
    .discussions-header .btn-sm,
    .discussion-card form .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }
    
    .discussions-header textarea,
    .discussions-header .form-control,
    .discussion-card form textarea,
    .discussion-card form .form-control {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    .discussions-header input,
    .discussion-card input {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
}

/* Styles pour très petits écrans - garder les mêmes petites tailles */
@media (max-width: 480px) {
    .discussions-container {
        padding: 0.75rem;
    }
    
    .discussions-header {
        padding: 0.75rem;
    }
    
    .discussion-card {
        padding: 0.75rem;
    }
    
    /* Garder les mêmes tailles Bootstrap btn-sm */
    .discussions-container .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }
    
    /* Garder les mêmes tailles pour les champs */
    .discussions-container .form-control,
    .discussions-container textarea,
    .discussions-container input {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    .discussion-card .reply-card {
        padding: 0.5rem;
        margin-left: 0.25rem;
    }
    
    .discussion-card small,
    .reply-card small {
        font-size: 0.7rem !important;
    }
    
    .discussion-card strong {
        font-size: 0.85rem !important;
    }
    
    .reply-card strong {
        font-size: 0.8rem !important;
    }
    
    .discussion-card .badge {
        font-size: 0.65rem !important;
        padding: 0.15rem 0.3rem !important;
    }
    
    /* Boutons d'action encore plus compacts - uniquement icônes */
    .discussion-card .btn-sm,
    .reply-card .btn-sm {
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
    
    .discussion-card .btn-sm i,
    .reply-card .btn-sm i {
        font-size: 0.65rem !important;
        margin: 0 !important;
        line-height: 1 !important;
    }
    
    /* Masquer le texte dans les boutons */
    .discussion-card .btn-sm > *:not(i):not(.badge):not(.likes-count),
    .reply-card .btn-sm > *:not(i):not(.badge):not(.likes-count) {
        display: none !important;
    }
    
    /* Exception pour le bouton like qui a un nombre - le rendre plus compact */
    .discussion-card .btn-sm.btn-outline-info:first-child,
    .discussion-card .like-discussion-btn {
        width: auto !important;
        min-width: 32px !important;
        max-width: 55px !important;
        padding: 0.2rem 0.3rem !important;
    }
    
    .discussion-card .btn-sm.btn-outline-info:first-child i,
    .discussion-card .like-discussion-btn i {
        margin-right: 0.15rem !important;
    }
    
    .discussion-card .btn-sm.btn-outline-info:first-child > *:not(i),
    .discussion-card .like-discussion-btn .likes-count {
        display: inline !important;
        font-size: 0.6rem !important;
    }
    
    /* Réduire encore plus l'espacement */
    .discussion-card .d-flex.gap-2,
    .reply-card .d-flex.gap-2 {
        gap: 0.25rem !important;
    }
    
    .discussion-card p {
        font-size: 0.875rem !important;
    }
    
    .reply-card p {
        font-size: 0.8rem !important;
    }
    
    .discussion-edit-form .btn-sm,
    .reply-edit-form .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
        width: auto !important;
        min-width: auto !important;
        height: auto !important;
    }
    
    /* S'assurer que le texte est visible dans les boutons des formulaires */
    .discussion-edit-form .btn-sm > *:not(i):not(.badge),
    .reply-edit-form .btn-sm > *:not(i):not(.badge) {
        display: inline !important;
    }
    
    .discussion-edit-form .btn-sm i,
    .reply-edit-form .btn-sm i {
        margin-right: 0.25rem !important;
    }
    
    .discussion-edit-form textarea,
    .reply-edit-form textarea {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    /* Bouton "Répondre" avec largeur relative au texte - cibler tous les boutons dans les formulaires de réponse */
    .discussion-card form .btn-sm.btn-info,
    .discussion-card form button.btn-sm[type="submit"].btn-info {
        width: auto !important;
        min-width: auto !important;
        height: auto !important;
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }
    
    .discussion-card form .btn-sm.btn-info > *:not(i),
    .discussion-card form button.btn-sm[type="submit"].btn-info > *:not(i) {
        display: inline !important;
    }
    
    .discussion-card form .btn-sm.btn-info i,
    .discussion-card form button.btn-sm[type="submit"].btn-info i {
        margin-right: 0.25rem !important;
    }
    
    /* Sur mobile, afficher le bouton "Répondre" en dessous du textarea */
    .discussion-card form .d-flex.gap-2 {
        flex-direction: column !important;
    }
    
    .discussion-card form .d-flex.gap-2 textarea {
        width: 100% !important;
        margin-bottom: 0.5rem !important;
    }
    
    .discussion-card form .d-flex.gap-2 button {
        align-self: flex-start !important;
        width: auto !important;
    }
    
    .discussions-header .btn-sm,
    .discussion-card form .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }
    
    .discussions-header textarea,
    .discussions-header .form-control,
    .discussion-card form textarea,
    .discussion-card form .form-control {
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    .discussions-header input,
    .discussion-card input {
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
<div class="discussions-container">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="discussions-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="mb-1" style="color: #f8fafc;">Discussions</h2>
                <p class="text-muted mb-0">
                    <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $lesson->id]) }}" style="color: #ffcc33;">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la leçon
                    </a>
                </p>
            </div>
        </div>
        <div class="small mb-3" style="color: #cbd5e1;">
            <strong style="color: #f8fafc;">Leçon:</strong> {{ $lesson->title }}<br>
            <strong style="color: #f8fafc;">Cours:</strong> {{ $course->title }}
        </div>
        <form id="create-discussion-form" method="POST" onsubmit="return false;">
            @csrf
            <textarea name="content" id="discussion-content" class="form-control mb-2" rows="3" placeholder="Posez une question ou partagez votre avis..." required style="background: rgba(255,255,255,0.1); border-color: rgba(255,204,51,0.25); color: #fff;"></textarea>
            <button type="button" id="submit-discussion-btn" class="btn btn-info btn-sm">
                <i class="fas fa-paper-plane me-2"></i>Publier
            </button>
        </form>
    </div>

    @if($discussions->count() > 0)
        @foreach($discussions as $discussion)
            <div class="discussion-card" id="discussion-{{ $discussion->id }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong style="color: #f8fafc;">{{ $discussion->user->name }}</strong>
                        <small style="color: #94a3b8;" class="ms-2">{{ $discussion->created_at->diffForHumans() }}</small>
                        @if($discussion->is_pinned)
                            <span class="badge bg-warning ms-2">Épinglé</span>
                        @endif
                        @if($discussion->is_answered)
                            <span class="badge bg-success ms-2">Répondu</span>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <form method="POST" 
                              class="d-inline like-discussion-form"
                              data-discussion-id="{{ $discussion->id }}"
                              data-action="{{ route('learning.discussions.like', ['course' => $course->slug, 'lesson' => $lesson->id, 'discussion' => $discussion->id]) }}"
                              onsubmit="return false;">
                            @csrf
                            <button type="button" class="btn btn-sm like-discussion-btn {{ ($discussion->is_liked ?? false) ? 'btn-info' : 'btn-outline-info' }}" id="like-btn-{{ $discussion->id }}" data-is-liked="{{ ($discussion->is_liked ?? false) ? 'true' : 'false' }}">
                                <i class="fas fa-thumbs-up me-1"></i><span class="likes-count">{{ $discussion->likes_count ?? 0 }}</span>
                            </button>
                        </form>
                        @if($discussion->user_id === auth()->id())
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="toggleEditDiscussion({{ $discussion->id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" 
                                  class="d-inline delete-discussion-form"
                                  data-discussion-id="{{ $discussion->id }}"
                                  data-action="{{ route('learning.discussions.destroy', ['course' => $course->slug, 'lesson' => $lesson->id, 'discussion' => $discussion->id]) }}"
                                  onsubmit="return false;">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger delete-discussion-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="discussion-content-display" id="discussion-content-{{ $discussion->id }}">
                    <p class="mb-3">{{ $discussion->content }}</p>
                </div>
                <form class="discussion-edit-form" id="discussion-edit-form-{{ $discussion->id }}" 
                      action="{{ route('learning.discussions.update', ['course' => $course->slug, 'lesson' => $lesson->id, 'discussion' => $discussion->id]) }}" 
                      method="POST">
                    @csrf
                    @method('PUT')
                    <textarea name="content" class="form-control mb-2" rows="4" required style="background: rgba(255,255,255,0.1); border-color: rgba(255,204,51,0.25); color: #fff;">{{ $discussion->content }}</textarea>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-save me-1"></i>Enregistrer
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleEditDiscussion({{ $discussion->id }})">
                            Annuler
                        </button>
                    </div>
                </form>
                
                @if($discussion->replies->count() > 0)
                    <div class="mt-3">
                        <p class="small mb-2" style="color: #94a3b8;">
                            <i class="fas fa-comments me-1" style="color: #94a3b8;"></i>{{ $discussion->replies->count() }} réponse(s)
                        </p>
                        @foreach($discussion->replies as $reply)
                            <div class="reply-card" id="reply-{{ $reply->id }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong style="color: #f8fafc;" class="small">{{ $reply->user->name }}</strong>
                                        <small style="color: #94a3b8;" class="ms-2">{{ $reply->created_at->diffForHumans() }}</small>
                                    </div>
                                    @if($reply->user_id === auth()->id())
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="toggleEditReply({{ $reply->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" 
                                                  class="d-inline delete-reply-form"
                                                  data-reply-id="{{ $reply->id }}"
                                                  data-action="{{ route('learning.discussions.destroy', ['course' => $course->slug, 'lesson' => $lesson->id, 'discussion' => $reply->id]) }}"
                                                  onsubmit="return false;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-reply-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                                <div class="reply-content-display" id="reply-content-{{ $reply->id }}">
                                    <p class="mb-0 small">{{ $reply->content }}</p>
                                </div>
                                <form class="reply-edit-form" id="reply-edit-form-{{ $reply->id }}" 
                                      action="{{ route('learning.discussions.update', ['course' => $course->slug, 'lesson' => $lesson->id, 'discussion' => $reply->id]) }}" 
                                      method="POST">
                                    @csrf
                                    @method('PUT')
                                    <textarea name="content" class="form-control mb-2" rows="3" required style="background: rgba(255,255,255,0.1); border-color: rgba(255,204,51,0.25); color: #fff;">{{ $reply->content }}</textarea>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-save me-1"></i>Enregistrer
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleEditReply({{ $reply->id }})">
                                            Annuler
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
                <form action="{{ route('learning.discussions.store', ['course' => $course->slug, 'lesson' => $lesson->id]) }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $discussion->id }}">
                    <div class="d-flex gap-2">
                        <textarea name="content" class="form-control" rows="2" placeholder="Répondre à cette discussion..." required style="background: rgba(255,255,255,0.1); border-color: rgba(255,204,51,0.25); color: #fff;"></textarea>
                        <button type="submit" class="btn btn-info btn-sm align-self-start">
                            <i class="fas fa-reply me-1"></i>Répondre
                        </button>
                    </div>
                </form>
            </div>
        @endforeach

        <div class="mt-4">
            {{ $discussions->links() }}
        </div>
    @else
        <div class="discussion-card text-center">
            <p class="text-muted mb-0">Aucune discussion pour cette leçon.</p>
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

function toggleEditDiscussion(discussionId) {
    const contentDisplay = document.getElementById('discussion-content-' + discussionId);
    const editForm = document.getElementById('discussion-edit-form-' + discussionId);
    
    if (contentDisplay && editForm) {
        contentDisplay.classList.toggle('hidden');
        editForm.classList.toggle('active');
    }
}

function toggleEditReply(replyId) {
    const contentDisplay = document.getElementById('reply-content-' + replyId);
    const editForm = document.getElementById('reply-edit-form-' + replyId);
    
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

// Intercepter la soumission des formulaires
document.addEventListener('DOMContentLoaded', function() {
    // Formulaire de création de discussion
    const createForm = document.getElementById('create-discussion-form');
    const submitBtn = document.getElementById('submit-discussion-btn');
    const contentTextarea = document.getElementById('discussion-content');
    
    if (createForm && submitBtn && contentTextarea) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const content = contentTextarea.value.trim();
            if (!content) {
                showToast('Veuillez saisir un message', 'warning');
                return;
            }
            
            // Désactiver le bouton pendant la requête
            const originalHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Publication...';
            
            // Préparer les données
            const formData = new FormData(createForm);
            
            // Faire la requête AJAX
            fetch('{{ route("learning.discussions.store", ["course" => $course->slug, "lesson" => $lesson->id]) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': createForm.querySelector('input[name="_token"]').value,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
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
                    
                    // Vider le champ de texte
                    contentTextarea.value = '';
                    
                    // Recharger la page pour afficher la nouvelle discussion
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    showToast(data.message || 'Une erreur est survenue', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Une erreur est survenue lors de la publication', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            });
        });
    }
    
    // Intercepter les clics sur les boutons de suppression de discussion
    document.querySelectorAll('.delete-discussion-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const formElement = this.closest('.delete-discussion-form');
            if (!formElement) return;
            
            const discussionId = formElement.dataset.discussionId;
            const actionUrl = formElement.dataset.action;
            const discussionCard = document.getElementById('discussion-' + discussionId) || formElement.closest('.discussion-card');
            
            showConfirmModal(
                'Êtes-vous sûr de vouloir supprimer cette discussion ? Cette action est irréversible.',
                function() {
                    // Désactiver le bouton pendant la requête
                    const submitBtn = formElement.querySelector('.delete-discussion-btn');
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
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
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
                            
                            // Supprimer la carte de la discussion de la page
                            if (discussionCard) {
                                discussionCard.style.transition = 'opacity 0.3s ease-out';
                                discussionCard.style.opacity = '0';
                                setTimeout(() => {
                                    discussionCard.remove();
                                    
                                    // Vérifier s'il reste des discussions
                                    const remainingDiscussions = document.querySelectorAll('.discussion-card');
                                    if (remainingDiscussions.length === 0) {
                                        // Rediriger vers la page des discussions au lieu de recharger
                                        setTimeout(() => {
                                            window.location.href = '{{ route("learning.discussions.all", ["course" => $course->slug, "lesson" => $lesson->id]) }}';
                                        }, 500);
                                    }
                                }, 300);
                            } else {
                                // Si on ne trouve pas la carte, rediriger vers la page des discussions
                                setTimeout(() => {
                                    window.location.href = '{{ route("learning.discussions.all", ["course" => $course->slug, "lesson" => $lesson->id]) }}';
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
    
    // Intercepter les clics sur les boutons de suppression de réponse
    document.querySelectorAll('.delete-reply-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const formElement = this.closest('.delete-reply-form');
            if (!formElement) return;
            
            const replyId = formElement.dataset.replyId;
            const actionUrl = formElement.dataset.action;
            const replyCard = document.getElementById('reply-' + replyId);
            
            showConfirmModal(
                'Êtes-vous sûr de vouloir supprimer cette réponse ? Cette action est irréversible.',
                function() {
                    // Désactiver le bouton pendant la requête
                    const submitBtn = formElement.querySelector('.delete-reply-btn');
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
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
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
                            
                            // Supprimer la carte de la réponse de la page
                            if (replyCard) {
                                replyCard.style.transition = 'opacity 0.3s ease-out';
                                replyCard.style.opacity = '0';
                                setTimeout(() => {
                                    replyCard.remove();
                                }, 300);
                            } else {
                                // Si on ne trouve pas la carte, recharger la page
                                setTimeout(() => {
                                    window.location.reload();
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
    
    // Intercepter les clics sur les boutons de like
    document.querySelectorAll('.like-discussion-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const formElement = this.closest('.like-discussion-form');
            if (!formElement) return;
            
            const discussionId = formElement.dataset.discussionId;
            const actionUrl = formElement.dataset.action;
            const likeBtn = this;
            const likesCountSpan = likeBtn.querySelector('.likes-count');
            const originalHtml = likeBtn.innerHTML;
            
            // Désactiver le bouton pendant la requête
            likeBtn.disabled = true;
            likeBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i><span class="likes-count">' + (likesCountSpan ? likesCountSpan.textContent : '0') + '</span>';
            
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
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('La réponse n\'est pas du JSON');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Mettre à jour le compteur de likes
                    if (likesCountSpan) {
                        likesCountSpan.textContent = data.likes_count;
                    }
                    
                    // Mettre à jour l'état visuel du bouton (liké ou non liké)
                    if (data.is_liked) {
                        likeBtn.classList.remove('btn-outline-info');
                        likeBtn.classList.add('btn-info');
                        likeBtn.setAttribute('data-is-liked', 'true');
                    } else {
                        likeBtn.classList.remove('btn-info');
                        likeBtn.classList.add('btn-outline-info');
                        likeBtn.setAttribute('data-is-liked', 'false');
                    }
                    
                    // Réactiver le bouton avec le nouveau compteur
                    likeBtn.disabled = false;
                    likeBtn.innerHTML = '<i class="fas fa-thumbs-up me-1"></i><span class="likes-count">' + data.likes_count + '</span>';
                } else {
                    showToast(data.message || 'Une erreur est survenue', 'error');
                    likeBtn.disabled = false;
                    likeBtn.innerHTML = originalHtml;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Une erreur est survenue lors du like', 'error');
                likeBtn.disabled = false;
                likeBtn.innerHTML = originalHtml;
            });
        });
    });
});
</script>
@endpush
@endsection

