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
        <form action="{{ route('learning.discussions.store', ['course' => $course->slug, 'lesson' => $lesson->id]) }}" method="POST">
            @csrf
            <textarea name="content" class="form-control mb-2" rows="3" placeholder="Posez une question ou partagez votre avis..." required style="background: rgba(255,255,255,0.1); border-color: rgba(255,204,51,0.25); color: #fff;"></textarea>
            <button type="submit" class="btn btn-info btn-sm">
                <i class="fas fa-paper-plane me-2"></i>Publier
            </button>
        </form>
    </div>

    @if($discussions->count() > 0)
        @foreach($discussions as $discussion)
            <div class="discussion-card">
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
                        <form action="{{ route('learning.discussions.like', ['course' => $course->slug, 'lesson' => $lesson->id, 'discussion' => $discussion->id]) }}" 
                              method="POST" 
                              class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-thumbs-up me-1"></i>{{ $discussion->likes_count }}
                            </button>
                        </form>
                        @if($discussion->user_id === auth()->id())
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="toggleEditDiscussion({{ $discussion->id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('learning.discussions.destroy', ['course' => $course->slug, 'lesson' => $lesson->id, 'discussion' => $discussion->id]) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Supprimer cette discussion ?');"
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
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
                                            <form action="{{ route('learning.discussions.destroy', ['course' => $course->slug, 'lesson' => $lesson->id, 'discussion' => $reply->id]) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Supprimer cette réponse ?');"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
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

@push('scripts')
<script>
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
</script>
@endpush
@endsection

