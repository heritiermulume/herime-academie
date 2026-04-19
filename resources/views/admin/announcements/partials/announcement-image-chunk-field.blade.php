@props([
    'suffix' => 'create',
    'label' => 'Image (modale accueil)',
    'help' => 'Glissez-déposez une image ou cliquez pour parcourir. Formats : JPEG, PNG, WebP, GIF (5 Mo max).',
])

<div class="announcement-image-chunk-field">
    <label class="form-label">{{ $label }}@if($suffix === 'create') *@endif</label>
    <input type="hidden" name="image_chunk_path" id="announcement_image_chunk_path_{{ $suffix }}" value="">
    <input type="hidden" name="image_chunk_name" id="announcement_image_chunk_name_{{ $suffix }}" value="">
    <input type="hidden" name="image_chunk_size" id="announcement_image_chunk_size_{{ $suffix }}" value="">
    <div id="announcementImageUploadZone_{{ $suffix }}" class="package-upload-zone upload-zone announcement-image-upload-zone">
        <div class="upload-placeholder text-center p-4">
            <i class="fas fa-cloud-upload-alt fa-2x text-secondary mb-2"></i>
            <p class="mb-2 small text-muted">{{ $help }}</p>
            <label class="btn btn-outline-primary btn-sm mb-0" for="announcementImageInput_{{ $suffix }}">
                Parcourir…
            </label>
            <input type="file"
                   id="announcementImageInput_{{ $suffix }}"
                   class="d-none"
                   accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp">
        </div>
        <div class="upload-preview d-none text-center p-3">
            <img src="" alt="" class="img-fluid rounded border mb-2" style="max-height: 160px;">
            <div><span class="badge bg-light text-dark file-name"></span> <span class="text-muted small file-size"></span></div>
            <button type="button" class="btn btn-sm btn-outline-danger mt-2 announcement-image-chunk-clear">
                <i class="fas fa-times me-1"></i>Retirer
            </button>
        </div>
    </div>
    <div id="announcementImageError_{{ $suffix }}" class="text-danger small mt-1" style="display: none;"></div>
</div>
