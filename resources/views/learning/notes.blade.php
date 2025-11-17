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
                        <form action="{{ route('learning.notes.destroy', ['course' => $course->slug, 'lesson' => $lesson->id, 'note' => $note->id]) }}" 
                              method="POST" 
                              onsubmit="return confirm('Supprimer cette note ?');"
                              class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
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

@push('scripts')
<script>
function toggleEditNote(noteId) {
    const contentDisplay = document.getElementById('note-content-' + noteId);
    const editForm = document.getElementById('note-edit-form-' + noteId);
    
    if (contentDisplay && editForm) {
        contentDisplay.classList.toggle('hidden');
        editForm.classList.toggle('active');
    }
}
</script>
@endpush
@endsection

