@extends('layouts.admin')

@section('title', 'Gestion des témoignages')
@section('admin-title', 'Témoignages des étudiants')
@section('admin-subtitle', 'Gérez les retours d\'expérience affichés sur la page d\'accueil')
@section('admin-actions')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTestimonialModal">
        <i class="fas fa-plus-circle me-2"></i>Nouveau témoignage
    </button>
@endsection

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <!-- Grille des témoignages -->
            <div class="row">
                @forelse($testimonials as $testimonial)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                @if($testimonial->photo)
                                <img src="{{ str_starts_with($testimonial->photo, 'http') ? $testimonial->photo : \App\Helpers\FileHelper::url($testimonial->photo) }}" 
                                     alt="{{ $testimonial->name }}" 
                                     class="rounded-circle me-3" 
                                     width="50" 
                                     height="50"
                                     style="object-fit: cover;">
                                @else
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                                     style="width: 50px; height: 50px;">
                                    {{ substr($testimonial->name, 0, 1) }}
                                </div>
                                @endif
                                
                                <div>
                                    <h6 class="mb-0">{{ $testimonial->name }}</h6>
                                    @if($testimonial->title)
                                    <small class="text-muted">{{ $testimonial->title }}</small>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="text-warning mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star{{ $i <= $testimonial->rating ? '' : '-o' }}"></i>
                                @endfor
                            </div>
                            
                            <p class="card-text">{{ Str::limit($testimonial->testimonial, 150) }}</p>
                            
                            @if($testimonial->company)
                            <small class="text-muted d-block">
                                <i class="fas fa-building me-1"></i>{{ $testimonial->company }}
                            </small>
                            @endif
                        </div>
                        
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    {{ $testimonial->created_at->format('d/m/Y') }}
                                </small>
                                <div class="d-flex gap-2 testimonial-actions">
                                    <button type="button"
                                            class="testimonial-action-icon text-warning"
                                            onclick="editTestimonial({{ $testimonial->id }})"
                                            title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button"
                                            class="testimonial-action-icon text-danger"
                                            onclick="deleteTestimonial({{ $testimonial->id }})"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="admin-empty-state">
                        <i class="fas fa-quote-left"></i>
                        <p class="mb-1">Aucun témoignage trouvé</p>
                        <p class="text-muted mb-0">Ajoutez un premier témoignage pour mettre en avant la satisfaction de vos étudiants.</p>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <x-admin.pagination :paginator="$testimonials" />
        </div>
    </section>
@endsection

<!-- Modal de création de témoignage -->
<div class="modal fade" id="createTestimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau témoignage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.testimonials.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Titre</label>
                                <input type="text" class="form-control" id="title" name="title">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="company" class="form-label">Entreprise</label>
                                <input type="text" class="form-control" id="company" name="company">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Photo de l'étudiant</label>
                                <div class="upload-zone text-center" id="testimonialPhotoUploadZone">
                                    <input type="file"
                                           class="form-control d-none"
                                           id="testimonial_photo"
                                           accept="image/jpeg,image/png,image/jpg,image/webp"
                                           onchange="handleTestimonialPhotoUpload(this)">
                                    <input type="hidden" id="photo_chunk_path" name="photo_chunk_path" value="">
                                    <input type="hidden" id="photo_chunk_name" name="photo_chunk_name" value="">
                                    <input type="hidden" id="photo_chunk_size" name="photo_chunk_size" value="">
                                    
                                    <div class="upload-placeholder p-3" onclick="document.getElementById('testimonial_photo').click()">
                                        <i class="fas fa-user-circle fa-2x text-primary mb-2 d-block"></i>
                                        <p class="mb-1"><strong>Cliquez pour sélectionner une photo</strong></p>
                                        <p class="text-muted small mb-0">JPG, PNG, WEBP · Max 5MB</p>
                                    </div>
                                    
                                    <div class="upload-preview d-none">
                                        <img src="" alt="Preview" class="img-fluid rounded-circle mb-2 d-block mx-auto" style="width: 72px; height: 72px; object-fit: cover; border: 2px solid #0d6efd;">
                                        <div class="upload-info mb-2">
                                            <span class="badge bg-primary file-name"></span>
                                            <span class="badge bg-info file-size"></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearTestimonialPhoto()">
                                            <i class="fas fa-trash me-1"></i>Supprimer
                                        </button>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" id="testimonialPhotoError"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="testimonial" class="form-label">Témoignage *</label>
                        <textarea class="form-control" id="testimonial" name="testimonial" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Note *</label>
                                <select class="form-select" id="rating" name="rating" required>
                                    <option value="1">1 étoile</option>
                                    <option value="2">2 étoiles</option>
                                    <option value="3">3 étoiles</option>
                                    <option value="4">4 étoiles</option>
                                    <option value="5" selected>5 étoiles</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">
                                        Témoignage actif
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer le témoignage</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'édition -->
<div class="modal fade" id="editTestimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le témoignage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTestimonialForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_title" class="form-label">Titre</label>
                                <input type="text" class="form-control" id="edit_title" name="title">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_company" class="form-label">Entreprise</label>
                                <input type="text" class="form-control" id="edit_company" name="company">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Photo de l'étudiant</label>
                                <div class="upload-zone text-center" id="editTestimonialPhotoUploadZone">
                                    <input type="file"
                                           class="form-control d-none"
                                           id="edit_testimonial_photo"
                                           accept="image/jpeg,image/png,image/jpg,image/webp"
                                           onchange="handleEditTestimonialPhotoUpload(this)">
                                    <input type="hidden" id="edit_photo_chunk_path" name="photo_chunk_path" value="">
                                    <input type="hidden" id="edit_photo_chunk_name" name="photo_chunk_name" value="">
                                    <input type="hidden" id="edit_photo_chunk_size" name="photo_chunk_size" value="">
                                    
                                    <div class="upload-placeholder p-3" onclick="document.getElementById('edit_testimonial_photo').click()">
                                        <i class="fas fa-user-circle fa-2x text-primary mb-2 d-block"></i>
                                        <p class="mb-1"><strong>Cliquez pour sélectionner une photo</strong></p>
                                        <p class="text-muted small mb-0">JPG, PNG, WEBP · Max 5MB</p>
                                    </div>
                                    
                                    <div class="upload-preview d-none">
                                        <img src="" alt="Preview" class="img-fluid rounded-circle mb-2 d-block mx-auto" style="width: 72px; height: 72px; object-fit: cover; border: 2px solid #0d6efd;">
                                        <div class="upload-info mb-2">
                                            <span class="badge bg-primary file-name"></span>
                                            <span class="badge bg-info file-size"></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearEditTestimonialPhoto()">
                                            <i class="fas fa-trash me-1"></i>Supprimer
                                        </button>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" id="editTestimonialPhotoError"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_testimonial" class="form-label">Témoignage *</label>
                        <textarea class="form-control" id="edit_testimonial" name="testimonial" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_rating" class="form-label">Note *</label>
                                <select class="form-select" id="edit_rating" name="rating" required>
                                    <option value="1">1 étoile</option>
                                    <option value="2">2 étoiles</option>
                                    <option value="3">3 étoiles</option>
                                    <option value="4">4 étoiles</option>
                                    <option value="5">5 étoiles</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">
                                    Témoignage actif
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Modifier le témoignage</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteTestimonialModal" tabindex="-1" aria-labelledby="deleteTestimonialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTestimonialModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p id="deleteTestimonialMessage">
                    Êtes-vous sûr de vouloir supprimer ce témoignage ? Cette action est irréversible.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteTestimonialForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @once
        <script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
    @endonce
@endpush

@push('scripts')
<script>
// Config upload photo témoignage (simplifié)
const TESTIMONIAL_MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5MB
const TESTIMONIAL_VALID_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
const TESTIMONIAL_CHUNK_SIZE_BYTES = 1 * 1024 * 1024; // 1MB
const TESTIMONIAL_CHUNK_UPLOAD_ENDPOINT = (function() {
    const origin = window.location.origin.replace(/\/+$/, '');
    const path = "{{ trim(parse_url(route('admin.uploads.chunk'), PHP_URL_PATH), '/') }}";
    return `${origin}/${path}`;
})();

let testimonialPhotoResumable = null;
let editTestimonialPhotoResumable = null;

function getCsrfToken() {
    return document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '';
}

function formatFileSize(bytes) {
    if (!bytes && bytes !== 0) return '';
    const sizes = ['o', 'Ko', 'Mo', 'Go'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${sizes[i]}`;
}

function resetTestimonialPhotoHiddenFields() {
    const path = document.getElementById('photo_chunk_path');
    const name = document.getElementById('photo_chunk_name');
    const size = document.getElementById('photo_chunk_size');
    if (path) path.value = '';
    if (name) name.value = '';
    if (size) size.value = '';
}

function resetTestimonialPhotoInput(input) {
    if (!input) return;
    try { input.value = ''; } catch (e) {}
    if (input.files && input.files.length) {
        try {
            const emptyFiles = new DataTransfer().files;
            input.files = emptyFiles;
        } catch (e) {}
    }
}

function handleTestimonialPhotoUpload(input) {
    const zone = document.getElementById('testimonialPhotoUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const errorDiv = document.getElementById('testimonialPhotoError');
    
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    
    if (!(input.files && input.files[0])) {
        return;
    }
    
    const file = input.files[0];
    
    if (!TESTIMONIAL_VALID_IMAGE_TYPES.includes(file.type)) {
        errorDiv.textContent = '❌ Format invalide. Utilisez JPG, PNG ou WEBP.';
        errorDiv.style.display = 'block';
        resetTestimonialPhotoInput(input);
        return;
    }
    
    if (file.size > TESTIMONIAL_MAX_IMAGE_SIZE) {
        errorDiv.textContent = '❌ Le fichier est trop volumineux. Maximum 5MB.';
        errorDiv.style.display = 'block';
        resetTestimonialPhotoInput(input);
        return;
    }
    
    if (typeof Resumable === 'undefined') {
        errorDiv.textContent = '❌ Votre navigateur ne supporte pas l’upload fractionné. Veuillez le mettre à jour ou utiliser un autre navigateur.';
        errorDiv.style.display = 'block';
        resetTestimonialPhotoInput(input);
        return;
    }
    
    resetTestimonialPhotoHiddenFields();
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = preview.querySelector('img');
        img.src = e.target.result;
        preview.querySelector('.file-name').textContent = file.name;
        preview.querySelector('.file-size').textContent = formatFileSize(file.size);
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
    };
    reader.readAsDataURL(file);
    
    startTestimonialPhotoChunkUpload(file, input);
}

function startTestimonialPhotoChunkUpload(file, input) {
    const token = getCsrfToken();
    const errorDiv = document.getElementById('testimonialPhotoError');
    
    if (!token) {
        errorDiv.textContent = '❌ Impossible de récupérer le jeton CSRF pour l’upload.';
        errorDiv.style.display = 'block';
        resetTestimonialPhotoInput(input);
        return;
    }
    
    if (testimonialPhotoResumable) {
        try { testimonialPhotoResumable.cancel(); } catch (e) {}
        testimonialPhotoResumable = null;
    }
    
    const pathInput = document.getElementById('photo_chunk_path');
    const nameInput = document.getElementById('photo_chunk_name');
    const sizeInput = document.getElementById('photo_chunk_size');
    
    const resumable = new Resumable({
        target: TESTIMONIAL_CHUNK_UPLOAD_ENDPOINT,
        chunkSize: TESTIMONIAL_CHUNK_SIZE_BYTES,
        simultaneousUploads: 3,
        testChunks: false,
        throttleProgressCallbacks: 1,
        fileParameterName: 'file',
        fileType: ['png', 'jpg', 'jpeg', 'webp'],
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
        query: () => ({
            upload_type: 'testimonial_photo',
            original_name: file.name,
        }),
    });
    
    testimonialPhotoResumable = resumable;
    
    resumable.addFile(file);
    
    resumable.on('fileError', function(_file, message) {
        console.error('Erreur upload témoignage:', message);
        errorDiv.textContent = '❌ Erreur lors du téléversement de la photo.';
        errorDiv.style.display = 'block';
        resetTestimonialPhotoHiddenFields();
        resetTestimonialPhotoInput(input);
    });
    
    resumable.on('fileSuccess', function(_file, message) {
        try {
            const response = JSON.parse(message);
            const payload = response.data || response;
            if (pathInput && payload.path) pathInput.value = payload.path;
            if (nameInput) nameInput.value = payload.filename || file.name;
            if (sizeInput) sizeInput.value = payload.size || file.size;
        } catch (e) {
            console.error('Réponse upload invalide:', e);
            errorDiv.textContent = '❌ Réponse du serveur invalide lors du téléversement.';
            errorDiv.style.display = 'block';
            resetTestimonialPhotoHiddenFields();
        }
        
        resetTestimonialPhotoInput(input);
        testimonialPhotoResumable = null;
    });
    
    resumable.upload();
}

function clearTestimonialPhoto() {
    const zone = document.getElementById('testimonialPhotoUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const input = document.getElementById('testimonial_photo');
    const errorDiv = document.getElementById('testimonialPhotoError');
    
    if (testimonialPhotoResumable) {
        try { testimonialPhotoResumable.cancel(); } catch (e) {}
    }
    testimonialPhotoResumable = null;
    
    resetTestimonialPhotoHiddenFields();
    resetTestimonialPhotoInput(input);
    
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    
    preview.classList.add('d-none');
    placeholder.classList.remove('d-none');
}

function handleEditTestimonialPhotoUpload(input) {
    const zone = document.getElementById('editTestimonialPhotoUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const errorDiv = document.getElementById('editTestimonialPhotoError');
    
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    
    if (!(input.files && input.files[0])) {
        return;
    }
    
    const file = input.files[0];
    
    if (!TESTIMONIAL_VALID_IMAGE_TYPES.includes(file.type)) {
        errorDiv.textContent = '❌ Format invalide. Utilisez JPG, PNG ou WEBP.';
        errorDiv.style.display = 'block';
        resetTestimonialPhotoInput(input);
        return;
    }
    
    if (file.size > TESTIMONIAL_MAX_IMAGE_SIZE) {
        errorDiv.textContent = '❌ Le fichier est trop volumineux. Maximum 5MB.';
        errorDiv.style.display = 'block';
        resetTestimonialPhotoInput(input);
        return;
    }
    
    if (typeof Resumable === 'undefined') {
        errorDiv.textContent = '❌ Votre navigateur ne supporte pas l’upload fractionné. Veuillez le mettre à jour ou utiliser un autre navigateur.';
        errorDiv.style.display = 'block';
        resetTestimonialPhotoInput(input);
        return;
    }
    
    const pathInput = document.getElementById('edit_photo_chunk_path');
    const nameInput = document.getElementById('edit_photo_chunk_name');
    const sizeInput = document.getElementById('edit_photo_chunk_size');
    if (pathInput) pathInput.value = '';
    if (nameInput) nameInput.value = '';
    if (sizeInput) sizeInput.value = '';
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = preview.querySelector('img');
        img.src = e.target.result;
        preview.querySelector('.file-name').textContent = file.name;
        preview.querySelector('.file-size').textContent = formatFileSize(file.size);
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
    };
    reader.readAsDataURL(file);
    
    startEditTestimonialPhotoChunkUpload(file, input);
}

function startEditTestimonialPhotoChunkUpload(file, input) {
    const token = getCsrfToken();
    const errorDiv = document.getElementById('editTestimonialPhotoError');
    
    if (!token) {
        errorDiv.textContent = '❌ Impossible de récupérer le jeton CSRF pour l’upload.';
        errorDiv.style.display = 'block';
        resetTestimonialPhotoInput(input);
        return;
    }
    
    if (editTestimonialPhotoResumable) {
        try { editTestimonialPhotoResumable.cancel(); } catch (e) {}
        editTestimonialPhotoResumable = null;
    }
    
    const pathInput = document.getElementById('edit_photo_chunk_path');
    const nameInput = document.getElementById('edit_photo_chunk_name');
    const sizeInput = document.getElementById('edit_photo_chunk_size');
    
    const resumable = new Resumable({
        target: TESTIMONIAL_CHUNK_UPLOAD_ENDPOINT,
        chunkSize: TESTIMONIAL_CHUNK_SIZE_BYTES,
        simultaneousUploads: 3,
        testChunks: false,
        throttleProgressCallbacks: 1,
        fileParameterName: 'file',
        fileType: ['png', 'jpg', 'jpeg', 'webp'],
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
        query: () => ({
            upload_type: 'testimonial_photo',
            original_name: file.name,
        }),
    });
    
    editTestimonialPhotoResumable = resumable;
    
    resumable.on('fileError', function(_file, message) {
        console.error('Erreur upload témoignage (édition):', message);
        errorDiv.textContent = '❌ Erreur lors du téléversement de la photo.';
        errorDiv.style.display = 'block';
        if (pathInput) pathInput.value = '';
        if (nameInput) nameInput.value = '';
        if (sizeInput) sizeInput.value = '';
        resetTestimonialPhotoInput(input);
    });
    
    resumable.on('fileSuccess', function(_file, message) {
        try {
            const response = JSON.parse(message);
            const payload = response.data || response;
            if (pathInput && payload.path) pathInput.value = payload.path;
            if (nameInput) nameInput.value = payload.filename || file.name;
            if (sizeInput) sizeInput.value = payload.size || file.size;
        } catch (e) {
            console.error('Réponse upload invalide (édition):', e);
            errorDiv.textContent = '❌ Réponse du serveur invalide lors du téléversement.';
            errorDiv.style.display = 'block';
            if (pathInput) pathInput.value = '';
            if (nameInput) nameInput.value = '';
            if (sizeInput) sizeInput.value = '';
        }
        
        resetTestimonialPhotoInput(input);
        editTestimonialPhotoResumable = null;
    });
    
    resumable.on('chunkingComplete', function() {
        if (!resumable.isUploading()) {
            resumable.upload();
        }
    });
    
    resumable.addFile(file);
}

function clearEditTestimonialPhoto() {
    const zone = document.getElementById('editTestimonialPhotoUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const input = document.getElementById('edit_testimonial_photo');
    const errorDiv = document.getElementById('editTestimonialPhotoError');
    
    if (editTestimonialPhotoResumable) {
        try { editTestimonialPhotoResumable.cancel(); } catch (e) {}
    }
    editTestimonialPhotoResumable = null;
    
    const pathInput = document.getElementById('edit_photo_chunk_path');
    const nameInput = document.getElementById('edit_photo_chunk_name');
    const sizeInput = document.getElementById('edit_photo_chunk_size');
    if (pathInput) pathInput.value = '';
    if (nameInput) nameInput.value = '';
    if (sizeInput) sizeInput.value = '';
    
    resetTestimonialPhotoInput(input);
    
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    
    preview.classList.add('d-none');
    placeholder.classList.remove('d-none');
}

function editTestimonial(id) {
    // Récupérer les données du témoignage via AJAX
    fetch(`/admin/testimonials/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            // Remplir le formulaire d'édition
            document.getElementById('edit_name').value = data.name || '';
            document.getElementById('edit_title').value = data.title || '';
            document.getElementById('edit_company').value = data.company || '';
            document.getElementById('edit_testimonial').value = data.testimonial || '';
            document.getElementById('edit_rating').value = data.rating || '5';
            document.getElementById('edit_is_active').checked = data.is_active || false;
            
            // Gérer la preview de photo existante
            const photoZone = document.getElementById('editTestimonialPhotoUploadZone');
            if (photoZone) {
                const placeholder = photoZone.querySelector('.upload-placeholder');
                const preview = photoZone.querySelector('.upload-preview');
                const img = preview.querySelector('img');
                const nameBadge = preview.querySelector('.file-name');
                const sizeBadge = preview.querySelector('.file-size');
                const errorDiv = document.getElementById('editTestimonialPhotoError');
                if (errorDiv) {
                    errorDiv.textContent = '';
                    errorDiv.style.display = 'none';
                }
                
                const pathInput = document.getElementById('edit_photo_chunk_path');
                const nameInput = document.getElementById('edit_photo_chunk_name');
                const sizeInput = document.getElementById('edit_photo_chunk_size');
                if (pathInput) pathInput.value = '';
                if (nameInput) nameInput.value = '';
                if (sizeInput) sizeInput.value = '';
                
                if (data.photo_url) {
                    img.src = data.photo_url;
                    if (nameBadge) nameBadge.textContent = data.name || 'Photo actuelle';
                    if (sizeBadge) sizeBadge.textContent = '';
                    placeholder.classList.add('d-none');
                    preview.classList.remove('d-none');
                } else {
                    preview.classList.add('d-none');
                    placeholder.classList.remove('d-none');
                }
            }
            
            // Mettre à jour l'action du formulaire
            document.getElementById('editTestimonialForm').action = `/admin/testimonials/${id}`;
            
            // Ouvrir le modal
            const modal = new bootstrap.Modal(document.getElementById('editTestimonialModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Erreur lors du chargement du témoignage:', error);
            alert('Erreur lors du chargement du témoignage');
        });
}

function deleteTestimonial(id) {
    const form = document.getElementById('deleteTestimonialForm');
    if (form) {
        form.action = `/admin/testimonials/${id}`;
    }

    const modalElement = document.getElementById('deleteTestimonialModal');
    if (!modalElement) {
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}
</script>
@endpush

@push('styles')
<style>
/* Design moderne pour la page de gestion des témoignages */
.card.h-100 {
    border-radius: 15px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card.h-100:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
}

.rounded-circle {
    transition: transform 0.2s ease, border-color 0.2s ease;
    border: 2px solid #dee2e6;
}

.card:hover .rounded-circle {
    transform: scale(1.1);
    border-color: #0d6efd;
}

.btn-group .btn, .d-flex.gap-1 .btn {
    transition: all 0.2s ease;
}

.btn-group .btn:hover, .d-flex.gap-1 .btn:hover {
    transform: translateY(-2px);
}

.badge {
    font-size: 0.85rem;
    padding: 0.4em 0.8em;
    font-weight: 500;
}

/* Zone d'upload photo témoignage */
#testimonialPhotoUploadZone {
    border: 1px dashed #d1d5db;
    border-radius: 0.75rem;
    padding: 0.75rem;
    background-color: #f9fafb;
    max-width: 260px;
    margin-left: auto;
    margin-right: auto;
}

#editTestimonialPhotoUploadZone {
    border: 1px dashed #d1d5db;
    border-radius: 0.75rem;
    padding: 0.75rem;
    background-color: #f9fafb;
    max-width: 260px;
    margin-left: auto;
    margin-right: auto;
}

#testimonialPhotoUploadZone .upload-placeholder,
#testimonialPhotoUploadZone .upload-preview,
#editTestimonialPhotoUploadZone .upload-placeholder,
#editTestimonialPhotoUploadZone .upload-preview {
    cursor: pointer;
}

#testimonialPhotoUploadZone .upload-info .badge,
#editTestimonialPhotoUploadZone .upload-info .badge {
    font-size: 0.7rem;
}

.testimonial-action-icon {
    background: transparent;
    border: none;
    padding: 0;
    margin: 0;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.testimonial-action-icon i {
    font-size: 0.95rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .admin-panel--main .admin-panel__body {
        padding: 0.75rem 0.75rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .col-md-6.col-lg-4 {
        padding: 0.5rem;
    }
}

@media (max-width: 576px) {
    .testimonial-actions {
        gap: 0.25rem;
    }
    
    .testimonial-action-icon i {
        font-size: 0.8rem;
    }
    
    .admin-panel--main .row > [class*="col-"] {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    /* Modal de suppression - adaptation mobile */
    #deleteTestimonialModal .modal-dialog {
        margin: 0.75rem;
    }
    
    #deleteTestimonialModal .modal-content {
        border-radius: 0.85rem;
    }
    
    #deleteTestimonialModal .modal-header,
    #deleteTestimonialModal .modal-footer {
        padding: 0.75rem 0.9rem;
    }
    
    #deleteTestimonialModal .modal-title {
        font-size: 1rem;
    }
    
    #deleteTestimonialModal .modal-body {
        padding: 0.75rem 0.9rem;
        font-size: 0.9rem;
    }
    
    #deleteTestimonialModal .modal-footer {
        flex-direction: row;
        justify-content: flex-end;
        align-items: center;
        gap: 0.5rem;
    }
    
    #deleteTestimonialModal .modal-footer .btn {
        width: auto;
        min-width: 90px;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
}
</style>
@endpush
