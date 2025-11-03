@props(['lesson'])

<div class="pdf-viewer-container">
    <div class="pdf-toolbar d-flex align-items-center justify-content-between p-3 bg-light border-bottom">
        <div class="d-flex align-items-center gap-3">
            <h5 class="mb-0">{{ $lesson->title }}</h5>
            @if($lesson->duration)
                <span class="badge bg-primary">{{ $lesson->duration }} min</span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="downloadPDF()">
                <i class="fas fa-download"></i> Télécharger
            </button>
            @if($lesson->content_url)
                <button class="btn btn-sm btn-primary" onclick="openPDFInNewTab()">
                    <i class="fas fa-external-link-alt"></i> Ouvrir
                </button>
            @endif
        </div>
    </div>
    <div class="pdf-content" style="height: calc(100vh - 200px); background: #f5f5f5;">
        <iframe 
            id="pdf-viewer-{{ $lesson->id }}" 
            src="{{ str_starts_with($lesson->content_url, 'http') ? $lesson->content_url : \App\Helpers\FileHelper::lessonFile($lesson->content_url) }}#toolbar=0" 
            style="width: 100%; height: 100%; border: none;"
            frameborder="0">
        </iframe>
    </div>
</div>

<script>
function downloadPDF() {
    const pdfUrl = "{{ str_starts_with($lesson->content_url, 'http') ? $lesson->content_url : \App\Helpers\FileHelper::lessonFile($lesson->content_url) }}";
    window.open(pdfUrl, '_blank');
}

function openPDFInNewTab() {
    const pdfUrl = "{{ str_starts_with($lesson->content_url, 'http') ? $lesson->content_url : \App\Helpers\FileHelper::lessonFile($lesson->content_url) }}";
    window.open(pdfUrl, '_blank');
}
</script>

