@props(['lesson'])

<div class="text-viewer-container">
    <div class="text-content p-4" style="max-height: calc(100vh - 200px); overflow-y: auto;">
        <div class="text-viewer-header mb-4 border-bottom pb-3">
            <h2 class="mb-2">{{ $lesson->title }}</h2>
            @if($lesson->description)
                <p class="text-muted mb-3">{{ $lesson->description }}</p>
            @endif
            <div class="d-flex align-items-center gap-3">
                @if($lesson->duration)
                    <span class="badge bg-primary"><i class="fas fa-clock"></i> {{ $lesson->duration }} min</span>
                @endif
                <span class="badge bg-secondary"><i class="fas fa-file-alt"></i> Texte</span>
            </div>
        </div>
        <div class="text-body">
            {!! nl2br(e($lesson->content_text ?? 'Aucun contenu disponible.')) !!}
        </div>
    </div>
</div>

<style>
.text-viewer-container {
    background: white;
    border-radius: 8px;
}

.text-body {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
}

.text-body p {
    margin-bottom: 1.5rem;
}

.text-body h1, .text-body h2, .text-body h3 {
    color: #003366;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.text-body ul, .text-body ol {
    margin-left: 2rem;
    margin-bottom: 1.5rem;
}

.text-body blockquote {
    border-left: 4px solid #ffcc33;
    padding-left: 1.5rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #666;
}
</style>

