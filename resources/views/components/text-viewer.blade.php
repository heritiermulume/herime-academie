@props(['lesson', 'lessonId' => null])

@php
    $viewerId = 'text-viewer-' . ($lessonId ?? $lesson->id ?? 'default');
    $isPdf = isset($lesson) && $lesson->type === 'pdf';
@endphp

<div class="text-viewer-container" id="{{ $viewerId }}">
    <div class="text-viewer-toolbar d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
        <div class="d-flex align-items-center gap-3">
            @if(isset($lesson) && $lesson->duration)
                <span class="badge bg-primary"><i class="fas fa-clock"></i> {{ $lesson->duration }} min</span>
            @endif
            <span class="badge bg-secondary">
                <i class="fas fa-{{ $isPdf ? 'file-pdf' : 'file-alt' }}"></i> {{ $isPdf ? 'PDF' : 'Texte' }}
            </span>
        </div>
        <div class="text-viewer-actions">
            <button class="btn btn-sm btn-outline-secondary" onclick="toggleFullscreen('{{ $viewerId }}')" title="Plein écran">
                <i class="fas fa-expand" id="{{ $viewerId }}-expand-icon"></i>
                <i class="fas fa-compress d-none" id="{{ $viewerId }}-compress-icon"></i>
            </button>
        </div>
    </div>
    <div class="text-content p-4" id="{{ $viewerId }}-content">
        @if($isPdf && isset($lesson->content_file_url))
            <div class="pdf-viewer-wrapper">
                <iframe src="{{ $lesson->content_file_url }}#toolbar=1" class="pdf-iframe" frameborder="0"></iframe>
            </div>
        @else
            <div class="text-body">
                {!! isset($lesson) && $lesson->content_text ? $lesson->content_text : 'Aucun contenu disponible.' !!}
            </div>
        @endif
    </div>
</div>

<style>
.text-viewer-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    height: 100%;
    width: 100%;
    max-height: calc(100vh - 200px);
    transition: all 0.3s ease;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.text-viewer-container.fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    max-height: 100vh;
    border-radius: 0;
    margin: 0;
}

.text-viewer-toolbar {
    flex-shrink: 0;
    border-bottom: 1px solid #dee2e6;
}

.text-content {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 0;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    width: 100%;
}

.text-body {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
    word-wrap: break-word;
    max-width: 900px;
    width: 100%;
    margin: 0 auto;
    padding: 0 1rem;
    box-sizing: border-box;
}

/* Préserver tous les styles HTML du contenu */
.text-body * {
    max-width: 100%;
}

.text-body p {
    margin-bottom: 1.5rem;
}

.text-body h1, .text-body h2, .text-body h3, .text-body h4, .text-body h5, .text-body h6 {
    color: #003366;
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.text-body h1 { font-size: 2rem; }
.text-body h2 { font-size: 1.75rem; }
.text-body h3 { font-size: 1.5rem; }
.text-body h4 { font-size: 1.25rem; }
.text-body h5 { font-size: 1.1rem; }
.text-body h6 { font-size: 1rem; }

/* Préserver les styles de texte (gras, italique, souligné, etc.) */
.text-body strong, .text-body b {
    font-weight: 700;
}

.text-body em, .text-body i {
    font-style: italic;
}

.text-body u {
    text-decoration: underline;
}

.text-body s, .text-body strike {
    text-decoration: line-through;
}

/* Préserver les différentes tailles de police via les balises <font> ou les styles inline */
.text-body [style*="font-size"] {
    /* Préserver les tailles de police définies inline */
}

.text-body [style*="font-weight"] {
    /* Préserver les poids de police définis inline */
}

.text-body [style*="font-style"] {
    /* Préserver les styles de police définis inline */
}

.text-body [style*="color"] {
    /* Préserver les couleurs définies inline */
}

/* Préserver les balises <font> si elles sont utilisées */
.text-body font[size] {
    /* Les tailles de font seront préservées via l'attribut size */
}

.text-body font[color] {
    /* Les couleurs de font seront préservées via l'attribut color */
}

.text-body font[face] {
    /* Les polices de font seront préservées via l'attribut face */
}

.text-body ul, .text-body ol {
    margin-left: 2rem;
    margin-bottom: 1.5rem;
    padding-left: 1rem;
}

.text-body li {
    margin-bottom: 0.5rem;
}

.text-body blockquote {
    border-left: 4px solid #ffcc33;
    padding-left: 1.5rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #666;
    background-color: #f8f9fa;
    padding: 1rem 1.5rem;
    border-radius: 4px;
}

.text-body code {
    background-color: #f4f4f4;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

.text-body pre {
    background-color: #f4f4f4;
    padding: 1rem;
    border-radius: 4px;
    overflow-x: auto;
    margin: 1.5rem 0;
}

.text-body pre code {
    background-color: transparent;
    padding: 0;
}

.text-body img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    margin: 1.5rem 0;
}

.text-body table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
}

.text-body table th,
.text-body table td {
    border: 1px solid #dee2e6;
    padding: 0.75rem;
    text-align: left;
}

.text-body table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #003366;
}

.text-body a {
    color: #003366;
    text-decoration: underline;
}

.text-body a:hover {
    color: #004080;
}

/* PDF Viewer */
.pdf-viewer-wrapper {
    width: 100%;
    height: calc(100vh - 300px);
    min-height: 600px;
    position: relative;
}

.pdf-iframe {
    width: 100%;
    height: 100%;
    border: none;
}

.text-viewer-container.fullscreen .pdf-viewer-wrapper {
    height: calc(100vh - 80px);
}

/* Responsive */
@media (min-width: 992px) {
    .player-shell .text-viewer-container {
        max-height: none;
    }
}

@media (max-width: 767.98px) {
    .text-viewer-container {
        max-height: calc(100vh - 150px);
    }
    
    .text-body {
        font-size: 1rem;
        line-height: 1.6;
    }
    
    .pdf-viewer-wrapper {
        height: calc(100vh - 250px);
        min-height: 400px;
    }
    
    .text-viewer-container.fullscreen .pdf-viewer-wrapper {
        height: calc(100vh - 60px);
    }
}

/* Scrollbar styling */
.text-content::-webkit-scrollbar {
    width: 8px;
}

.text-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.text-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.text-content::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
function toggleFullscreen(viewerId) {
    const container = document.getElementById(viewerId);
    const expandIcon = document.getElementById(viewerId + '-expand-icon');
    const compressIcon = document.getElementById(viewerId + '-compress-icon');
    
    if (!container) return;
    
    if (container.classList.contains('fullscreen')) {
        // Exit fullscreen
        container.classList.remove('fullscreen');
        expandIcon.classList.remove('d-none');
        compressIcon.classList.add('d-none');
        document.body.style.overflow = '';
    } else {
        // Enter fullscreen
        container.classList.add('fullscreen');
        expandIcon.classList.add('d-none');
        compressIcon.classList.remove('d-none');
        document.body.style.overflow = 'hidden';
    }
}

// Exit fullscreen on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const fullscreenViewers = document.querySelectorAll('.text-viewer-container.fullscreen');
        fullscreenViewers.forEach(viewer => {
            const viewerId = viewer.id;
            const expandIcon = document.getElementById(viewerId + '-expand-icon');
            const compressIcon = document.getElementById(viewerId + '-compress-icon');
            viewer.classList.remove('fullscreen');
            if (expandIcon) expandIcon.classList.remove('d-none');
            if (compressIcon) compressIcon.classList.add('d-none');
            document.body.style.overflow = '';
        });
    }
});
</script>

