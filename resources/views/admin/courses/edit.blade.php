@extends('layouts.admin')

@php
    use Illuminate\Support\Str;
    use App\Helpers\FileHelper;

    if (!$course->relationLoaded('sections')) {
        $course->load(['sections.lessons']);
    } else {
        $course->sections->load('lessons');
    }

    $prefilledSections = old('sections');

    if (!$prefilledSections) {
        $prefilledSections = $course->sections
            ->sortBy('sort_order')
            ->values()
            ->map(function ($section) {
            return [
                'id' => $section->id,
                'title' => $section->title,
                'description' => $section->description,
                'lessons' => $section->lessons
                    ->sortBy('sort_order')
                    ->values()
                    ->map(function ($lesson) {
                    $contentUrl = $lesson->content_url;
                    $isLocalFile = $contentUrl && !filter_var($contentUrl, FILTER_VALIDATE_URL);
                    $existingFileUrl = null;

                    if ($isLocalFile) {
                        $existingFileUrl = FileHelper::lessonFile($contentUrl);
                    }

                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'description' => $lesson->description,
                        'type' => $lesson->type,
                        'duration' => $lesson->duration,
                        'is_preview' => (bool) $lesson->is_preview,
                        'content_url' => $isLocalFile ? null : $contentUrl,
                        'existing_file_path' => $isLocalFile ? $contentUrl : null,
                        'existing_file_name' => $isLocalFile && $contentUrl ? basename($contentUrl) : null,
                        'existing_file_url' => $existingFileUrl,
                        'content_text' => $lesson->content_text,
                    ];
                })->values(),
            ];
        })->values();
    }
@endphp

@section('title', 'Modifier un cours')
@section('admin-title', 'Modifier un cours')
@section('admin-subtitle', "Actualisez l'ensemble des informations du cours « " . Str::limit($course->title, 60) . " »")
@section('admin-actions')
    <a href="{{ route('admin.courses') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('admin-content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Erreurs de validation</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="admin-panel">
        <div class="admin-panel__body admin-panel__body--padded">
            <form action="{{ route('admin.courses.update', $course) }}" method="POST" id="courseForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Informations de base -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations de base</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label">Titre du cours <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title', $course->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="instructor_id" class="form-label">Instructeur <span class="text-danger">*</span></label>
                                <select class="form-select @error('instructor_id') is-invalid @enderror" 
                                        id="instructor_id" name="instructor_id" required>
                                    <option value="">Sélectionner un instructeur</option>
                                    @foreach($instructors as $instructor)
                                        <option value="{{ $instructor->id }}" {{ old('instructor_id', $course->instructor_id) == $instructor->id ? 'selected' : '' }}>
                                            {{ $instructor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('instructor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Catégorie <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                    <option value="">Sélectionner une catégorie</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $course->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="level" class="form-label">Niveau <span class="text-danger">*</span></label>
                                <select class="form-select @error('level') is-invalid @enderror" id="level" name="level" required>
                                    <option value="">Sélectionner</option>
                                    <option value="beginner" {{ old('level', $course->level) == 'beginner' ? 'selected' : '' }}>Débutant</option>
                                    <option value="intermediate" {{ old('level', $course->level) == 'intermediate' ? 'selected' : '' }}>Intermédiaire</option>
                                    <option value="advanced" {{ old('level', $course->level) == 'advanced' ? 'selected' : '' }}>Avancé</option>
                                </select>
                                @error('level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="language" class="form-label">Langue <span class="text-danger">*</span></label>
                                <select class="form-select @error('language') is-invalid @enderror" id="language" name="language" required>
                                    <option value="">Sélectionner</option>
                                    <option value="fr" {{ old('language', $course->language) == 'fr' ? 'selected' : '' }}>Français</option>
                                    <option value="en" {{ old('language', $course->language) == 'en' ? 'selected' : '' }}>English</option>
                                </select>
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4" required>{{ old('description', $course->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Médias (Image et Vidéo) -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-success text-white">
                        <h5 class="mb-0"><i class="fas fa-photo-video me-2"></i>Médias du cours</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6 mb-3">
                                <label for="thumbnail" class="form-label fw-bold">Image de couverture</label>
                                
                                <!-- Image actuelle -->
                                @if($course->thumbnail_url)
                                    <div class="current-thumbnail mb-3 text-center">
                                        <p class="fw-bold mb-2 text-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Image actuelle :
                                        </p>
                                        <img src="{{ $course->thumbnail_url }}" alt="Image actuelle" class="img-thumbnail rounded" style="max-width: 300px; max-height: 200px; border: 4px solid #28a745;">
                                    </div>
                                @endif
                                
                                <!-- Zone d'upload pour nouveau -->
                                <div class="upload-zone" id="thumbnailUploadZone">
                                    <input type="file" 
                                           class="form-control d-none @error('thumbnail') is-invalid @enderror" 
                                           id="thumbnail" 
                                           name="thumbnail" 
                                           accept="image/jpeg,image/png,image/jpg,image/webp"
                                           onchange="handleThumbnailUpload(this)">
                                    <div class="upload-placeholder text-center p-4" onclick="document.getElementById('thumbnail').click()">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                        <p class="mb-2"><strong>Cliquez pour changer l'image</strong></p>
                                        <p class="text-muted small mb-0">Format : JPG, PNG, WEBP | Max : 5MB</p>
                                        <p class="text-muted small">Laissez vide pour conserver l'image actuelle</p>
                                    </div>
                                    <div class="upload-preview d-none">
                                        <p class="fw-bold text-info text-center mb-2">
                                            <i class="fas fa-eye me-1"></i>Nouvelle image :
                                        </p>
                                        <img src="" alt="Preview" class="img-fluid rounded mx-auto d-block" style="max-width: 100%; max-height: 300px; border: 3px solid #17a2b8;">
                                        <div class="upload-info mt-2 text-center">
                                            <span class="badge bg-primary file-name"></span>
                                            <span class="badge bg-info file-size"></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger mt-2 d-block mx-auto" onclick="clearThumbnail()">
                                            <i class="fas fa-trash me-1"></i>Annuler le changement
                                        </button>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" id="thumbnailError"></div>
                                @error('thumbnail')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="video_preview" class="form-label fw-bold">Vidéo de prévisualisation</label>
                                
                                {{-- Champ YouTube (recommandé pour vidéos sécurisées) --}}
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle"></i> <strong>Recommandé:</strong> Utilisez YouTube pour un hébergement sécurisé.
                                </div>
                                
                                @if($course->video_preview_youtube_id)
                                    <div class="alert alert-success mb-3">
                                        <i class="fab fa-youtube text-danger"></i> 
                                        <strong>Vidéo YouTube actuelle:</strong> {{ $course->video_preview_youtube_id }}
                                        @if($course->video_preview_is_unlisted)
                                            <span class="badge bg-warning">Mode Non Répertorié</span>
                                        @endif
                                    </div>
                                @endif
                                
                                <!-- Vidéo actuelle -->
                                @if($course->video_preview && !filter_var($course->video_preview, FILTER_VALIDATE_URL))
                                    <div class="mt-3">
                                        <p class="text-muted mb-2">Vidéo actuelle :</p>
                                        <video controls class="w-100 rounded-3" style="max-height: 280px; object-fit: cover;">
                                            <source src="{{ $course->video_preview_url }}" type="video/mp4">
                                            Votre navigateur ne supporte pas la lecture vidéo.
                                        </video>
                                    </div>
                                @endif
                                
                                <!-- Option 1: YouTube -->
                                <div class="mb-3">
                                    <label class="form-label small">
                                        <i class="fab fa-youtube text-danger"></i> Option 1: URL YouTube (Mode Non Répertorié)
                                    </label>
                                    <input type="text" class="form-control @error('video_preview_youtube_id') is-invalid @enderror" 
                                           id="video_preview_youtube_id" name="video_preview_youtube_id" value="{{ old('video_preview_youtube_id', $course->video_preview_youtube_id) }}" 
                                           placeholder="https://www.youtube.com/watch?v=xxx ou youtu.be/xxx">
                                    <small class="text-muted">Collez l'URL complète ou juste l'ID de la vidéo YouTube</small>
                                    @error('video_preview_youtube_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="video_preview_is_unlisted" name="video_preview_is_unlisted" value="1" {{ old('video_preview_is_unlisted', $course->video_preview_is_unlisted) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="video_preview_is_unlisted">
                                            Vidéo en mode "Non répertorié" sur YouTube
                                        </label>
                                    </div>
                                    <small class="text-muted">Cochez cette case si votre vidéo YouTube est en mode non répertorié (recommandé pour plus de sécurité)</small>
                                </div>
                                
                                <!-- Option 2: Lien vidéo -->
                                <div class="mb-3">
                                    <label class="form-label small">Option 2: Lien vidéo (Vimeo, autres)</label>
                                    <input type="url" class="form-control @error('video_preview') is-invalid @enderror" 
                                           id="video_preview" name="video_preview" value="{{ old('video_preview', filter_var($course->video_preview, FILTER_VALIDATE_URL) ? $course->video_preview : '') }}" 
                                           placeholder="https://...">
                                    @error('video_preview')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Option 3: Upload fichier -->
                                <div>
                                    <label class="form-label small">Option 3: Téléverser un fichier</label>
                                    <div class="upload-zone" id="videoUploadZone">
                                        <input type="file" 
                                               class="form-control d-none @error('video_preview_file') is-invalid @enderror" 
                                               id="video_preview_file" 
                                               name="video_preview_file" 
                                               accept="video/mp4,video/webm,video/ogg"
                                               onchange="handleVideoUpload(this)">
                                        <div class="upload-placeholder text-center p-3" onclick="document.getElementById('video_preview_file').click()">
                                            <i class="fas fa-video fa-2x text-success mb-2"></i>
                                            <p class="mb-1 small"><strong>Cliquez pour changer la vidéo</strong></p>
                                            <p class="text-muted small mb-0">Format : MP4, WEBM | Max : 100MB</p>
                                        </div>
                                        <div class="upload-preview d-none">
                                            <p class="fw-bold text-info text-center mb-2">
                                                <i class="fas fa-eye me-1"></i>Nouvelle vidéo :
                                            </p>
                                            <video controls class="w-100 rounded" style="max-height: 200px; border: 3px solid #17a2b8;"></video>
                                            <div class="upload-info mt-2 text-center">
                                                <span class="badge bg-primary file-name"></span>
                                                <span class="badge bg-info file-size"></span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger mt-2 d-block mx-auto" onclick="clearVideo()">
                                                <i class="fas fa-trash me-1"></i>Annuler le changement
                                            </button>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback d-block" id="videoError"></div>
                                    @error('video_preview_file')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prix et statut -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Prix et statut</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3 mb-3">
                                <label for="price" class="form-label">Prix ({{ $baseCurrency ?? 'USD' }}) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" value="{{ old('price', $course->price) }}" min="0" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="sale_price" class="form-label">Prix de vente ({{ $baseCurrency ?? 'USD' }})</label>
                                <input type="number" class="form-control @error('sale_price') is-invalid @enderror" 
                                       id="sale_price" name="sale_price" value="{{ old('sale_price', $course->sale_price) }}" min="0">
                                @error('sale_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_free" name="is_free" value="1" 
                                           {{ old('is_free', $course->is_free) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_free">
                                        Cours gratuit
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" 
                                           {{ old('is_published', $course->is_published) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">
                                        Publié
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" 
                                           {{ old('is_featured', $course->is_featured) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">
                                        Cours en vedette
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_downloadable" name="is_downloadable" value="1" 
                                           {{ old('is_downloadable', $course->is_downloadable) ? 'checked' : '' }}
                                           onchange="toggleDownloadFileFields()">
                                    <label class="form-check-label" for="is_downloadable">
                                        <strong>Cours téléchargeable</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Fichier de téléchargement spécifique -->
                        <div id="download-file-fields" style="display: {{ old('is_downloadable', $course->is_downloadable) ? 'block' : 'none' }};" class="mt-4">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <strong>Option de téléchargement :</strong> Vous pouvez définir un fichier spécifique à télécharger (ZIP, PDF, etc.) au lieu de télécharger toutes les sections et leçons du cours. Laissez vide pour télécharger le contenu complet du cours.
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="download_file_path" class="form-label fw-bold">
                                        Fichier de téléchargement spécifique <span class="text-muted">(Optionnel)</span>
                                    </label>
                                    
                                    @if($course->download_file_path && !filter_var($course->download_file_path, FILTER_VALIDATE_URL))
                                    <div class="current-file mb-3">
                                        <p class="fw-bold mb-2"><i class="fas fa-check-circle text-success me-1"></i>Fichier actuel :</p>
                                        <div class="d-flex align-items-center gap-2 p-2 bg-light rounded">
                                            <i class="fas fa-file-archive fa-2x text-primary"></i>
                                            <div class="flex-grow-1">
                                                <strong>{{ basename($course->download_file_path) }}</strong>
                                                <br>
                                                <small class="text-muted">Fichier déjà uploadé</small>
                                            </div>
                                            <a href="{{ $course->download_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download me-1"></i>Aperçu
                                            </a>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <div class="upload-zone" id="downloadFileUploadZone">
                                        <input type="file" 
                                               class="form-control d-none @error('download_file_path') is-invalid @enderror" 
                                               id="download_file_path" 
                                               name="download_file_path" 
                                               accept=".zip,.pdf,.doc,.docx,.rar,.7z,.tar,.gz">
                                        <div class="upload-placeholder text-center p-4" onclick="document.getElementById('download_file_path').click()">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                            <p class="mb-2"><strong>Cliquez pour sélectionner un fichier</strong></p>
                                            <p class="text-muted small mb-0">Formats : ZIP, PDF, DOC, DOCX, RAR, 7Z, TAR, GZ</p>
                                            <p class="text-muted small">Maximum : 2MB</p>
                                        </div>
                                        <div class="upload-preview d-none">
                                            <div class="d-flex align-items-center gap-3 p-3">
                                                <i class="fas fa-file-archive fa-3x text-primary"></i>
                                                <div class="flex-grow-1">
                                                    <div class="upload-info">
                                                        <span class="badge bg-primary file-name"></span>
                                                        <span class="badge bg-info file-size ms-2"></span>
                                                    </div>
                                                    <div class="upload-info mt-2">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="clearDownloadFile()">
                                                            <i class="fas fa-trash me-1"></i>Supprimer
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Pour les fichiers plus volumineux, utilisez une URL externe dans le champ ci-dessous.
                                    </small>
                                    <div class="invalid-feedback d-block" id="downloadFileError"></div>
                                    @error('download_file_path')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="download_file_url" class="form-label">OU URL externe du fichier</label>
                                    <input type="text" 
                                           class="form-control @error('download_file_url') is-invalid @enderror" 
                                           id="download_file_url" 
                                           name="download_file_url" 
                                           value="{{ old('download_file_url', filter_var($course->download_file_path ?? '', FILTER_VALIDATE_URL) ? $course->download_file_path : '') }}"
                                           placeholder="https://example.com/course.zip">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Si vous avez le fichier hébergé ailleurs, entrez son URL complète ici.
                                    </small>
                                    @error('download_file_url')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                @if($course->download_file_path && !filter_var($course->download_file_path, FILTER_VALIDATE_URL))
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_download_file" name="remove_download_file" value="1">
                                        <label class="form-check-label" for="remove_download_file">
                                            Supprimer le fichier de téléchargement actuel (retour au téléchargement du contenu complet)
                                        </label>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Paiement externe -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-external-link-alt me-2"></i>Paiement externe
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="use_external_payment" name="use_external_payment" value="1" 
                                           {{ old('use_external_payment', $course->use_external_payment) ? 'checked' : '' }}
                                           onchange="toggleExternalPaymentFields()">
                                    <label class="form-check-label" for="use_external_payment">
                                        <strong>Utiliser un paiement externe</strong>
                                    </label>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Si activé, les utilisateurs seront redirigés vers un lien de paiement externe au lieu d'utiliser le panier.
                                </small>
                            </div>
                        </div>

                        <div id="external-payment-fields" style="display: {{ old('use_external_payment', $course->use_external_payment) ? 'block' : 'none' }};">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="external_payment_url" class="form-label">URL de paiement externe <span class="text-danger">*</span></label>
                                    <input type="url" class="form-control @error('external_payment_url') is-invalid @enderror" 
                                           id="external_payment_url" name="external_payment_url" 
                                           value="{{ old('external_payment_url', $course->external_payment_url) }}" 
                                           placeholder="https://example.com/payment/...">
                                    @error('external_payment_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="external_payment_text" class="form-label">Texte du bouton</label>
                                    <input type="text" class="form-control @error('external_payment_text') is-invalid @enderror" 
                                           id="external_payment_text" name="external_payment_text" 
                                           value="{{ old('external_payment_text', $course->external_payment_text) }}" 
                                           placeholder="Acheter maintenant">
                                    <small class="text-muted">Texte affiché sur le bouton de paiement (par défaut: "Acheter maintenant")</small>
                                    @error('external_payment_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prérequis et objectifs -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-info text-white">
                        <h5 class="mb-0"><i class="fas fa-target me-2"></i>Prérequis et objectifs</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="requirements" class="form-label">Prérequis</label>
                                <div id="requirements-container">
                                    @php $requirements = $course->getRequirementsArray(); @endphp
                                    @if(count($requirements) > 0)
                                        @foreach($requirements as $requirement)
                                            <div class="input-group mb-2">
                                                <input type="text" class="form-control" name="requirements[]" value="{{ $requirement }}">
                                                <button type="button" class="btn btn-outline-danger" onclick="removeRequirement(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="requirements[]" placeholder="Ajouter un prérequis">
                                            <button type="button" class="btn btn-outline-danger" onclick="removeRequirement(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRequirement()">
                                    <i class="fas fa-plus me-1"></i>Ajouter un prérequis
                                </button>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="what_you_will_learn" class="form-label">Ce que vous apprendrez</label>
                                <div id="learnings-container">
                                    @php $learnings = $course->getWhatYouWillLearnArray(); @endphp
                                    @if(count($learnings) > 0)
                                        @foreach($learnings as $learning)
                                            <div class="input-group mb-2">
                                                <input type="text" class="form-control" name="what_you_will_learn[]" value="{{ $learning }}">
                                                <button type="button" class="btn btn-outline-danger" onclick="removeLearning(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="what_you_will_learn[]" placeholder="Ajouter un objectif">
                                            <button type="button" class="btn btn-outline-danger" onclick="removeLearning(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLearning()">
                                    <i class="fas fa-plus me-1"></i>Ajouter un objectif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Optimisation SEO</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="meta_description" class="form-label">Description SEO</label>
                                <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                          id="meta_description" name="meta_description" rows="3" maxlength="160">{{ old('meta_description', $course->meta_description) }}</textarea>
                                <small class="text-muted">Maximum 160 caractères</small>
                                @error('meta_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="meta_keywords" class="form-label">Mots-clés SEO</label>
                                <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" 
                                       id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $course->meta_keywords) }}" 
                                       placeholder="mot-clé1, mot-clé2, mot-clé3">
                                @error('meta_keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                                       id="tags" name="tags" value="{{ old('tags', implode(', ', $course->getTagsArray())) }}" 
                                       placeholder="tag1, tag2, tag3">
                                @error('tags')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu du cours -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Contenu du cours</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Organisez vos sections et leçons directement ici. Chaque leçon peut inclure un fichier téléversé ou un lien externe, ainsi qu'un contenu texte.
                        </p>
                        <div id="sections-container"></div>
                        <div class="d-flex justify-content-start mt-3">
                            <button type="button" class="btn btn-primary" onclick="addSection()">
                                <i class="fas fa-plus me-1"></i>Ajouter une section
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <a href="{{ route('admin.courses') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annuler
                            </a>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-1"></i>Mettre à jour
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script>
// Constantes de validation
const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5MB
const MAX_VIDEO_SIZE = 100 * 1024 * 1024; // 100MB
const VALID_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
const VALID_VIDEO_TYPES = ['video/mp4', 'video/webm', 'video/ogg'];
const LESSON_ALLOWED_TYPES = [
    'video/mp4',
    'video/webm',
    'application/pdf',
    'application/zip',
    'application/x-zip-compressed',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'text/csv',
    'application/x-rar-compressed',
    'application/x-7z-compressed',
    'application/x-tar',
    'application/gzip'
];
const LESSON_MAX_FILE_SIZE = 200 * 1024 * 1024; // 200MB
const INITIAL_SECTIONS = (() => {
    const raw = @json($prefilledSections);
    if (!raw) {
        return [];
    }
    if (Array.isArray(raw)) {
        return raw;
    }
    return Object.values(raw);
})();

let sectionCount = 0;
let lessonCount = 0;
let cachedPriceValue = null;
let cachedSalePriceValue = null;

function toBoolean(value) {
    return value === true || value === '1' || value === 1 || value === 'true' || value === 'on';
}

document.addEventListener('DOMContentLoaded', function() {
    if (INITIAL_SECTIONS.length > 0) {
        INITIAL_SECTIONS.forEach(section => addSection(section));
    } else {
        addSection();
    }
});

// Gestion de l'upload d'image de couverture
function handleThumbnailUpload(input) {
    const zone = document.getElementById('thumbnailUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const errorDiv = document.getElementById('thumbnailError');
    
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        if (!VALID_IMAGE_TYPES.includes(file.type)) {
            showError(errorDiv, '❌ Format invalide. Utilisez JPG, PNG ou WEBP.');
            input.value = '';
            return;
        }
        
        if (file.size > MAX_IMAGE_SIZE) {
            showError(errorDiv, '❌ Le fichier est trop volumineux. Maximum 5MB.');
            input.value = '';
            return;
        }
        
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
    }
}

function clearThumbnail() {
    const zone = document.getElementById('thumbnailUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const input = document.getElementById('thumbnail');
    const errorDiv = document.getElementById('thumbnailError');
    
    input.value = '';
    preview.querySelector('img').src = '';
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    preview.classList.add('d-none');
    placeholder.classList.remove('d-none');
}

// Gestion de l'upload de vidéo
function handleVideoUpload(input) {
    const zone = document.getElementById('videoUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const errorDiv = document.getElementById('videoError');
    
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        if (!VALID_VIDEO_TYPES.includes(file.type)) {
            showError(errorDiv, '❌ Format invalide. Utilisez MP4 ou WEBM.');
            input.value = '';
            return;
        }
        
        if (file.size > MAX_VIDEO_SIZE) {
            showError(errorDiv, '❌ Le fichier est trop volumineux. Maximum 100MB.');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const video = preview.querySelector('video');
            video.src = e.target.result;
            preview.querySelector('.file-name').textContent = file.name;
            preview.querySelector('.file-size').textContent = formatFileSize(file.size);
            placeholder.classList.add('d-none');
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
}

function clearVideo() {
    const zone = document.getElementById('videoUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const input = document.getElementById('video_preview_file');
    const errorDiv = document.getElementById('videoError');
    
    input.value = '';
    preview.querySelector('video').src = '';
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    preview.classList.add('d-none');
    placeholder.classList.remove('d-none');
}

// Fonctions utilitaires
function showError(errorDiv, message) {
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Gestion dynamique des sections et leçons
function addSection(sectionData = null) {
    sectionCount++;
    const sectionId = sectionCount;
    const container = document.getElementById('sections-container');
    if (!container) return;

    const sectionOrder = container.children.length + 1;

    const normalizedLessons = sectionData && sectionData.lessons
        ? (Array.isArray(sectionData.lessons) ? sectionData.lessons : Object.values(sectionData.lessons))
        : [];

    const sectionTitle = sectionData?.title ?? '';
    const sectionDescription = sectionData?.description ?? '';
    const sectionDbId = sectionData?.id ?? '';

    const sectionDiv = document.createElement('div');
    sectionDiv.className = 'card border-0 shadow-sm mb-3';
    sectionDiv.id = `section-${sectionId}`;
    sectionDiv.innerHTML = `
        <div class="card-header bg-gradient-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-primary section-order-badge">${sectionOrder}</span>
                    <h6 class="mb-0 text-white section-title-label">${sectionTitle || `Section ${sectionOrder}`}</h6>
                </div>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="removeSection(${sectionId})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <input type="hidden" name="sections[${sectionId}][id]" value="${sectionDbId}">
            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label">Titre de la section</label>
                    <input type="text" class="form-control section-title-input" name="sections[${sectionId}][title]" placeholder="Titre de la section" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="sections[${sectionId}][description]" placeholder="Description (optionnel)">
                </div>
            </div>
            <div id="lessons-${sectionId}" class="lesson-list"></div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-3" onclick="addLesson(${sectionId})">
                <i class="fas fa-plus me-1"></i>Ajouter une leçon
            </button>
        </div>
    `;

    container.appendChild(sectionDiv);

    const titleInput = sectionDiv.querySelector('.section-title-input');
    const titleLabel = sectionDiv.querySelector('.section-title-label');
    if (titleInput && titleLabel) {
        titleInput.value = sectionTitle;
        titleInput.addEventListener('input', function() {
            const order = Array.from(container.children).indexOf(sectionDiv) + 1;
            titleLabel.textContent = this.value.trim() !== '' ? this.value : `Section ${order}`;
        });
    }

    const descriptionInput = sectionDiv.querySelector(`[name="sections[${sectionId}][description]"]`);
    if (descriptionInput) {
        descriptionInput.value = sectionDescription;
    }

    if (normalizedLessons.length > 0) {
        normalizedLessons.forEach(lesson => addLesson(sectionId, lesson));
    }

    renumberSections();

    if (sectionTitle) {
        titleInput.value = sectionTitle;
    }

    if (sectionDescription) {
        descriptionInput.value = sectionDescription;
    }
}

function removeSection(sectionId) {
    const section = document.getElementById(`section-${sectionId}`);
    if (!section) return;
    section.remove();
    renumberSections();
}

function renumberSections() {
    const container = document.getElementById('sections-container');
    if (!container) return;

    Array.from(container.children).forEach((card, index) => {
        const badge = card.querySelector('.section-order-badge');
        if (badge) {
            badge.textContent = index + 1;
        }
        const titleInput = card.querySelector('.section-title-input');
        const titleLabel = card.querySelector('.section-title-label');
        if (titleInput && titleLabel && titleInput.value.trim() === '') {
            titleLabel.textContent = `Section ${index + 1}`;
        }
    });
}

function addLesson(sectionId, lessonData = null) {
    const lessonsContainer = document.getElementById(`lessons-${sectionId}`);
    if (!lessonsContainer) return;

    if (typeof lessonsContainer.dataset.lessonCount === 'undefined') {
        lessonsContainer.dataset.lessonCount = '0';
    }
    const lessonIndex = parseInt(lessonsContainer.dataset.lessonCount, 10);
    lessonsContainer.dataset.lessonCount = String(lessonIndex + 1);
    const lessonUniqueId = `${sectionId}-${lessonIndex}-${Date.now()}`;
    const lessonPrefix = `sections[${sectionId}][lessons][${lessonIndex}]`;

    const existingFilePath = lessonData?.existing_file_path ?? '';
    const existingFileName = lessonData?.existing_file_name ?? (existingFilePath ? existingFilePath.split('/').pop() : '');
    const existingFileUrl = lessonData?.existing_file_url ?? '';
    const removeExistingFlag = toBoolean(lessonData?.remove_existing_file) ? 1 : 0;

    const lessonDiv = document.createElement('div');
    lessonDiv.className = 'card border-0 shadow-sm mb-2';
    lessonDiv.innerHTML = `
        <div class="card-body">
            <input type="hidden" name="${lessonPrefix}[id]" value="${lessonData?.id ?? ''}">
            <input type="hidden" name="${lessonPrefix}[existing_file_path]" value="${existingFilePath}" data-lesson-existing-path="${lessonUniqueId}">
            <input type="hidden" name="${lessonPrefix}[remove_existing_file]" value="${removeExistingFlag}" data-lesson-remove-flag="${lessonUniqueId}">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Titre de la leçon</label>
                    <input type="text" class="form-control" name="${lessonPrefix}[title]" placeholder="Titre de la leçon" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="${lessonPrefix}[type]" required>
                        <option value="">Sélectionner</option>
                        <option value="video">Vidéo</option>
                        <option value="text">Texte</option>
                        <option value="quiz">Quiz</option>
                        <option value="assignment">Devoir</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Durée (min)</label>
                    <input type="number" class="form-control" name="${lessonPrefix}[duration]" min="0" placeholder="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Aperçu</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="${lessonPrefix}[is_preview]" value="1">
                        <label class="form-check-label">Gratuit</label>
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger d-block" onclick="removeLesson(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="${lessonPrefix}[description]" rows="2" placeholder="Description de la leçon"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fichier ou média de la leçon</label>
                    <div class="upload-zone lesson-upload-zone" id="lessonUploadZone-${lessonUniqueId}" onclick="triggerLessonFile('${lessonUniqueId}', event)">
                        <input type="file"
                               class="form-control d-none lesson-file-input"
                               id="lesson_file_${lessonUniqueId}"
                               name="${lessonPrefix}[content_file]"
                               accept="video/mp4,video/webm,application/pdf,application/zip,application/x-zip-compressed,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,application/x-rar-compressed,application/x-7z-compressed,application/x-tar,application/gzip"
                               onchange="handleLessonFileUpload('${lessonUniqueId}', this)">
                        <div class="upload-placeholder text-center p-3">
                            <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
                            <p class="mb-1"><strong>Cliquez pour sélectionner un fichier</strong></p>
                            <p class="text-muted small mb-0">Formats acceptés : MP4, WEBM, PDF, ZIP, DOCX...</p>
                            <p class="text-muted small">Taille max : 200MB</p>
                        </div>
                        <div class="upload-preview d-none text-center">
                            <div class="lesson-file-visual mb-3"></div>
                            <div class="upload-info mb-2 d-inline-flex flex-column gap-1">
                                <span class="badge bg-primary file-name"></span>
                                <span class="badge bg-info file-size"></span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-danger lesson-remove-btn" onclick="clearLessonFile('${lessonUniqueId}', true)">
                                    <i class="fas fa-trash me-1"></i>Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="lesson-existing-file alert alert-secondary d-none mt-3" id="lessonExistingFile-${lessonUniqueId}">
                        <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                            <div class="lesson-existing-icon text-primary">
                                <i class="fas fa-folder-open fa-2x"></i>
                            </div>
                            <div class="text-center text-md-start">
                                <p class="mb-1 fw-semibold" data-lesson-existing-name>Fichier actuel</p>
                                <a href="#" target="_blank" rel="noopener" class="small d-none" data-lesson-existing-link>Voir / télécharger</a>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearExistingLessonFile('${lessonUniqueId}')">
                                <i class="fas fa-trash me-1"></i>Retirer
                            </button>
                        </div>
                    </div>
                    <div class="invalid-feedback d-block lesson-file-error" id="lessonFileError-${lessonUniqueId}"></div>
                    <div class="mt-3">
                        <label class="form-label small">Ou renseignez un lien externe</label>
                        <input type="url"
                               class="form-control lesson-url-input"
                               name="${lessonPrefix}[content_url]"
                               placeholder="Lien (https://...)"
                               data-lesson-url="${lessonUniqueId}"
                               oninput="handleLessonUrlInput('${lessonUniqueId}', this)">
                        <small class="text-muted">Le lien sera prioritaire si aucun fichier n'est téléversé.</small>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <label class="form-label">Contenu texte</label>
                    <textarea class="form-control" name="${lessonPrefix}[content_text]" rows="3" placeholder="Contenu texte de la leçon"></textarea>
                </div>
            </div>
        </div>
    `;

    lessonsContainer.appendChild(lessonDiv);

    const titleInput = lessonDiv.querySelector(`[name="${lessonPrefix}[title]"]`);
    if (titleInput) {
        titleInput.value = lessonData?.title ?? '';
    }

    const typeSelect = lessonDiv.querySelector(`[name="${lessonPrefix}[type]"]`);
    if (typeSelect && lessonData?.type) {
        typeSelect.value = lessonData.type;
    }

    const durationInput = lessonDiv.querySelector(`[name="${lessonPrefix}[duration]"]`);
    if (durationInput && lessonData?.duration) {
        durationInput.value = lessonData.duration;
    }

    const previewCheckbox = lessonDiv.querySelector(`[name="${lessonPrefix}[is_preview]"]`);
    if (previewCheckbox && toBoolean(lessonData?.is_preview)) {
        previewCheckbox.checked = true;
    }

    const descriptionInput = lessonDiv.querySelector(`[name="${lessonPrefix}[description]"]`);
    if (descriptionInput) {
        descriptionInput.value = lessonData?.description ?? '';
    }

    const contentText = lessonDiv.querySelector(`[name="${lessonPrefix}[content_text]"]`);
    if (contentText) {
        contentText.value = lessonData?.content_text ?? '';
    }

    const urlInput = lessonDiv.querySelector(`[name="${lessonPrefix}[content_url]"]`);
    if (urlInput) {
        urlInput.value = lessonData?.content_url ?? '';
    }

    const existingPathInput = lessonDiv.querySelector(`[data-lesson-existing-path="${lessonUniqueId}"]`);
    if (existingPathInput) {
        if (existingFilePath) {
            existingPathInput.dataset.lessonOriginalPath = existingFilePath;
        }
        if (existingFileName) {
            existingPathInput.dataset.lessonOriginalName = existingFileName;
        }
        if (existingFileUrl) {
            existingPathInput.dataset.lessonOriginalUrl = existingFileUrl;
        }
    }

    if (existingFilePath && removeExistingFlag === 0) {
        showLessonExistingFile(lessonUniqueId, {
            path: existingFilePath,
            name: existingFileName,
            url: existingFileUrl
        });
    }
}

function removeLesson(button) {
    const card = button.closest('.card');
    if (!card) return;
    card.remove();
}

function handleLessonFileUpload(uniqueId, input) {
    const zone = document.getElementById(`lessonUploadZone-${uniqueId}`);
    if (!zone) return;

    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const fileNameBadge = preview?.querySelector('.file-name');
    const fileSizeBadge = preview?.querySelector('.file-size');
    const visualContainer = preview?.querySelector('.lesson-file-visual');
    const errorDiv = document.getElementById(`lessonFileError-${uniqueId}`);

    if (errorDiv) {
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';
    }

    if (!input.files || !input.files[0]) {
        clearLessonFile(uniqueId, true);
        return;
    }

    const file = input.files[0];

    if (!isLessonFileTypeAllowed(file)) {
        showLessonFileError(uniqueId, '❌ Format non supporté. Utilisez une vidéo (MP4/WEBM) ou un document (PDF, ZIP, DOCX, etc.).');
        input.value = '';
        return;
    }

    if (file.size > LESSON_MAX_FILE_SIZE) {
        showLessonFileError(uniqueId, `❌ Fichier trop volumineux (${formatFileSize(file.size)}). Maximum : ${formatFileSize(LESSON_MAX_FILE_SIZE)}.`);
        input.value = '';
        return;
    }

    if (placeholder && preview) {
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
        if (fileNameBadge) fileNameBadge.textContent = file.name;
        if (fileSizeBadge) fileSizeBadge.textContent = formatFileSize(file.size);
        if (visualContainer) {
            renderLessonFilePreview(zone, visualContainer, file);
        }
    }

    zone.style.borderColor = '#28a745';

    const existingContainer = document.getElementById(`lessonExistingFile-${uniqueId}`);
    if (existingContainer) {
        existingContainer.classList.add('d-none');
    }

    const removeFlagInput = document.querySelector(`[data-lesson-remove-flag="${uniqueId}"]`);
    if (removeFlagInput) {
        removeFlagInput.value = 0;
    }
}

function triggerLessonFile(uniqueId, event) {
    if (event) {
        const target = event.target;
        if (target.closest('button') || target.tagName === 'INPUT' || target.closest('a')) {
            return;
        }
    }

    const zone = document.getElementById(`lessonUploadZone-${uniqueId}`);
    if (!zone) return;

    const input = zone.querySelector('input[type="file"]');
    if (input) {
        input.click();
    }
}

function renderLessonFilePreview(zone, container, file) {
    if (!container) return;

    if (zone.dataset.previewUrl) {
        URL.revokeObjectURL(zone.dataset.previewUrl);
        delete zone.dataset.previewUrl;
    }

    const type = file.type || '';
    const extension = file.name?.split('.').pop()?.toLowerCase() || '';

    if (type.startsWith('video/')) {
        const videoUrl = URL.createObjectURL(file);
        zone.dataset.previewUrl = videoUrl;
        container.innerHTML = `
            <video controls class="rounded" style="max-width: 240px; max-height: 180px; border: 3px solid #28a745;">
                <source src="${videoUrl}" type="${file.type}">
                Votre navigateur ne supporte pas la lecture de cette vidéo.
            </video>
        `;
        return;
    }

    let iconClass = 'fas fa-file text-secondary';
    if (type === 'application/pdf' || extension === 'pdf') {
        iconClass = 'fas fa-file-pdf text-danger';
    } else if (type.includes('zip') || ['zip', 'rar', '7z'].includes(extension)) {
        iconClass = 'fas fa-file-archive text-warning';
    } else if (type.includes('word') || ['doc', 'docx'].includes(extension)) {
        iconClass = 'fas fa-file-word text-primary';
    } else if (type.includes('presentation') || ['ppt', 'pptx'].includes(extension)) {
        iconClass = 'fas fa-file-powerpoint text-warning';
    } else if (type.includes('excel') || ['xls', 'xlsx', 'csv'].includes(extension)) {
        iconClass = 'fas fa-file-excel text-success';
    }

    container.innerHTML = `<i class="${iconClass} fa-3x"></i>`;
}

function clearLessonFile(uniqueId, restoreExisting = false) {
    const zone = document.getElementById(`lessonUploadZone-${uniqueId}`);
    if (!zone) return;

    const input = zone.querySelector('input[type="file"]');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const errorDiv = document.getElementById(`lessonFileError-${uniqueId}`);

    if (zone.dataset.previewUrl) {
        URL.revokeObjectURL(zone.dataset.previewUrl);
        delete zone.dataset.previewUrl;
    }

    if (input) {
        input.value = '';
    }

    if (preview) {
        preview.classList.add('d-none');
        const fileNameBadge = preview.querySelector('.file-name');
        const fileSizeBadge = preview.querySelector('.file-size');
        const visualContainer = preview.querySelector('.lesson-file-visual');
        if (fileNameBadge) fileNameBadge.textContent = '';
        if (fileSizeBadge) fileSizeBadge.textContent = '';
        if (visualContainer) visualContainer.innerHTML = '';
    }

    if (placeholder) {
        placeholder.classList.remove('d-none');
    }

    if (errorDiv) {
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';
    }

    zone.style.borderColor = '#dee2e6';

    if (restoreExisting) {
        restoreLessonExistingFile(uniqueId);
    }
}

function handleLessonUrlInput(uniqueId, input) {
    if (!input) return;
    const value = input.value.trim();

    if (value !== '') {
        const existingContainer = document.getElementById(`lessonExistingFile-${uniqueId}`);
        if (existingContainer) {
            existingContainer.classList.add('d-none');
        }
        const removeFlagInput = document.querySelector(`[data-lesson-remove-flag="${uniqueId}"]`);
        if (removeFlagInput) {
            removeFlagInput.value = 1;
        }
        const existingPathInput = document.querySelector(`[data-lesson-existing-path="${uniqueId}"]`);
        if (existingPathInput) {
            existingPathInput.value = '';
        }
    } else {
        restoreLessonExistingFile(uniqueId);
    }
}

function isLessonFileTypeAllowed(file) {
    if (!file) return false;
    if (LESSON_ALLOWED_TYPES.includes(file.type)) {
        return true;
    }

    const extension = file.name?.split('.').pop()?.toLowerCase() || '';
    const allowedExtensions = ['mp4', 'webm', 'pdf', 'zip', 'rar', '7z', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'csv', 'tar', 'gz'];
    return allowedExtensions.includes(extension);
}

function showLessonFileError(uniqueId, message) {
    const errorDiv = document.getElementById(`lessonFileError-${uniqueId}`);
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
}

function showLessonExistingFile(uniqueId, fileData = {}) {
    const container = document.getElementById(`lessonExistingFile-${uniqueId}`);
    const removeFlagInput = document.querySelector(`[data-lesson-remove-flag="${uniqueId}"]`);
    const existingPathInput = document.querySelector(`[data-lesson-existing-path="${uniqueId}"]`);

    if (!container || !existingPathInput) return;

    const path = fileData.path ?? existingPathInput.dataset.lessonOriginalPath ?? '';
    if (path) {
        existingPathInput.value = path;
        existingPathInput.dataset.lessonOriginalPath = existingPathInput.dataset.lessonOriginalPath || path;
    }

    const name = fileData.name ?? existingPathInput.dataset.lessonOriginalName ?? 'Fichier actuel';
    const url = fileData.url ?? existingPathInput.dataset.lessonOriginalUrl ?? null;

    existingPathInput.dataset.lessonOriginalName = existingPathInput.dataset.lessonOriginalName || name;
    if (url) {
        existingPathInput.dataset.lessonOriginalUrl = existingPathInput.dataset.lessonOriginalUrl || url;
    }

    const nameEl = container.querySelector('[data-lesson-existing-name]');
    const linkEl = container.querySelector('[data-lesson-existing-link]');

    if (nameEl) {
        nameEl.textContent = name;
    }
    if (linkEl) {
        if (url) {
            linkEl.href = url;
            linkEl.textContent = 'Voir / télécharger';
            linkEl.classList.remove('d-none');
        } else {
            linkEl.href = '#';
            linkEl.classList.add('d-none');
        }
    }

    container.classList.remove('d-none');

    if (removeFlagInput) {
        removeFlagInput.value = 0;
    }
}

function clearExistingLessonFile(uniqueId) {
    const container = document.getElementById(`lessonExistingFile-${uniqueId}`);
    const removeFlagInput = document.querySelector(`[data-lesson-remove-flag="${uniqueId}"]`);
    const existingPathInput = document.querySelector(`[data-lesson-existing-path="${uniqueId}"]`);

    if (removeFlagInput) {
        removeFlagInput.value = 1;
    }

    if (existingPathInput) {
        existingPathInput.value = '';
    }

    if (container) {
        container.classList.add('d-none');
    }
}

function restoreLessonExistingFile(uniqueId) {
    const removeFlagInput = document.querySelector(`[data-lesson-remove-flag="${uniqueId}"]`);
    const existingPathInput = document.querySelector(`[data-lesson-existing-path="${uniqueId}"]`);
    const container = document.getElementById(`lessonExistingFile-${uniqueId}`);

    if (!existingPathInput || !container) return;

    const originalPath = existingPathInput.dataset.lessonOriginalPath;
    if (!originalPath) return;

    existingPathInput.value = originalPath;
    if (removeFlagInput) {
        removeFlagInput.value = 0;
    }

    showLessonExistingFile(uniqueId, {
        path: originalPath,
        name: existingPathInput.dataset.lessonOriginalName,
        url: existingPathInput.dataset.lessonOriginalUrl
    });
}

// Gestion des prérequis
function addRequirement() {
    const container = document.getElementById('requirements-container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" name="requirements[]" placeholder="Ajouter un prérequis">
        <button type="button" class="btn btn-outline-danger" onclick="removeRequirement(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeRequirement(button) {
    button.parentElement.remove();
}

// Gestion des objectifs
function addLearning() {
    const container = document.getElementById('learnings-container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" name="what_you_will_learn[]" placeholder="Ajouter un objectif">
        <button type="button" class="btn btn-outline-danger" onclick="removeLearning(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeLearning(button) {
    button.parentElement.remove();
}

// Sauvegarder comme brouillon
// Gestion du cours gratuit
function syncFreeCourseFields(isInitial = false) {
    const priceField = document.getElementById('price');
    const salePriceField = document.getElementById('sale_price');
    const freeCheckbox = document.getElementById('is_free');

    if (!priceField || !salePriceField) {
        return;
    }

    const isFree = freeCheckbox ? freeCheckbox.checked : false;

    if (isFree) {
        if (!isInitial) {
            cachedPriceValue = priceField.value;
            cachedSalePriceValue = salePriceField.value;
        }
        if (cachedPriceValue === null) {
            cachedPriceValue = priceField.value;
        }
        if (cachedSalePriceValue === null) {
            cachedSalePriceValue = salePriceField.value;
        }

        priceField.value = '0';
        priceField.disabled = true;
        salePriceField.value = '';
        salePriceField.disabled = true;
    } else {
        priceField.disabled = false;
        salePriceField.disabled = false;

        if (cachedPriceValue !== null) {
            priceField.value = cachedPriceValue || '';
        }

        if (cachedSalePriceValue !== null) {
            salePriceField.value = cachedSalePriceValue || '';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const freeCheckbox = document.getElementById('is_free');
    const priceField = document.getElementById('price');
    const salePriceField = document.getElementById('sale_price');

    if (priceField) {
        cachedPriceValue = priceField.value;
        priceField.addEventListener('input', function() {
            if (!freeCheckbox || !freeCheckbox.checked) {
                cachedPriceValue = this.value;
            }
        });
    }

    if (salePriceField) {
        cachedSalePriceValue = salePriceField.value;
        salePriceField.addEventListener('input', function() {
            if (!freeCheckbox || !freeCheckbox.checked) {
                cachedSalePriceValue = this.value;
            }
        });
    }

    syncFreeCourseFields(true);

    if (freeCheckbox) {
        freeCheckbox.addEventListener('change', function() {
            syncFreeCourseFields();
        });
    }
});

// Gestion des champs de paiement externe
function toggleExternalPaymentFields() {
    const checkbox = document.getElementById('use_external_payment');
    const fields = document.getElementById('external-payment-fields');
    const urlField = document.getElementById('external_payment_url');
    
    if (checkbox.checked) {
        fields.style.display = 'block';
        urlField.required = true;
    } else {
        fields.style.display = 'none';
        urlField.required = false;
        urlField.value = '';
        document.getElementById('external_payment_text').value = '';
    }
}

// Gestion des champs de fichier de téléchargement
function toggleDownloadFileFields() {
    const checkbox = document.getElementById('is_downloadable');
    const fields = document.getElementById('download-file-fields');
    
    if (checkbox.checked) {
        fields.style.display = 'block';
    } else {
        fields.style.display = 'none';
        const downloadInput = document.getElementById('download_file_path');
        if (downloadInput) {
            downloadInput.value = '';
            clearDownloadFile();
        }
        const urlInput = document.getElementById('download_file_url');
        if (urlInput) {
            urlInput.value = '';
        }
        const removeCheckbox = document.getElementById('remove_download_file');
        if (removeCheckbox) {
            removeCheckbox.checked = false;
        }
    }
}

// Gestion de l'upload du fichier de téléchargement avec validation et preview
document.addEventListener('DOMContentLoaded', function() {
    const downloadFileInput = document.getElementById('download_file_path');
    if (downloadFileInput) {
        downloadFileInput.addEventListener('change', function(e) {
            handleDownloadFileUpload(e.target);
        });
    }
});

function handleDownloadFileUpload(input) {
    const zone = document.getElementById('downloadFileUploadZone');
    const errorDiv = document.getElementById('downloadFileError');
    const file = input.files[0];
    
    // Reset error
    if (errorDiv) {
        errorDiv.textContent = '';
        errorDiv.classList.remove('d-block');
    }
    
    if (!file) return;
    
    // Validation du type
    const validExtensions = ['.zip', '.pdf', '.doc', '.docx', '.rar', '.7z', '.tar', '.gz'];
    const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
    
    if (!validExtensions.includes(fileExtension)) {
        showDownloadFileError('❌ Format invalide. Utilisez ZIP, PDF, DOC, DOCX, RAR, 7Z, TAR ou GZ.');
        input.value = '';
        return;
    }
    
    // Validation de la taille (2MB)
    const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
    if (file.size > MAX_FILE_SIZE) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        showDownloadFileError(`❌ Fichier trop volumineux (${sizeMB}MB). Maximum : 2MB. Utilisez une URL externe pour les fichiers plus volumineux.`);
        input.value = '';
        return;
    }
    
    // Afficher le preview
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    
    if (placeholder && preview) {
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
        
        preview.querySelector('.file-name').textContent = file.name;
        preview.querySelector('.file-size').textContent = formatFileSize(file.size);
        
        zone.style.borderColor = '#28a745';
    }
    
    // Effacer l'URL si un fichier est sélectionné
    const urlInput = document.getElementById('download_file_url');
    if (urlInput) {
        urlInput.value = '';
    }
}

function clearDownloadFile() {
    const input = document.getElementById('download_file_path');
    const zone = document.getElementById('downloadFileUploadZone');
    
    if (!input || !zone) return;
    
    input.value = '';
    
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    
    if (placeholder && preview) {
        placeholder.classList.remove('d-none');
        preview.classList.add('d-none');
        
        zone.style.borderColor = '#dee2e6';
    }
    
    // Réinitialiser l'erreur
    const errorDiv = document.getElementById('downloadFileError');
    if (errorDiv) {
        errorDiv.textContent = '';
        errorDiv.classList.remove('d-block');
    }
}

function showDownloadFileError(message) {
    const errorDiv = document.getElementById('downloadFileError');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.classList.add('d-block');
    }
}

// Effacer le fichier uploadé si une URL est saisie
document.addEventListener('DOMContentLoaded', function() {
    const urlInput = document.getElementById('download_file_url');
    if (urlInput) {
        urlInput.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                // Si une URL est saisie, effacer le fichier uploadé
                const fileInput = document.getElementById('download_file_path');
                if (fileInput && fileInput.files.length > 0) {
                    clearDownloadFile();
                }
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
/* En-tête */
.text-description {
    opacity: 0.9;
}

/* Gradients pour les headers */
.bg-gradient-primary {
    background: linear-gradient(135deg, #003366 0%, #004080 100%) !important;
    color: #ffffff !important;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    color: #ffffff !important;
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
    color: #ffffff !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%) !important;
    color: #ffffff !important;
}

.bg-gradient-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
    color: #ffffff !important;
}

/* Cards */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 12px;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-2px);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border-bottom: none;
}

.card-header h5 {
    font-weight: 600;
}

.card-header h6 {
    color: #003366;
}

/* Form controls */
.form-label {
    font-weight: 600;
    color: #333;
}

.form-label.fw-bold {
    color: #003366;
}

.form-control-lg {
    font-size: 1.1rem;
    padding: 0.75rem 1rem;
}

.form-control:focus, .form-select:focus {
    border-color: #003366;
    box-shadow: 0 0 0 0.2rem rgba(0, 51, 102, 0.25);
}

/* Buttons */
.btn {
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-outline-primary {
    border-color: #003366;
    color: #003366;
}

.btn-outline-primary:hover {
    background-color: #003366;
    border-color: #003366;
    color: white;
}

.text-primary {
    color: #003366 !important;
}

.accordion-button {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}

.accordion-button:not(.collapsed) {
    background-color: #e3f2fd;
    color: #003366;
}

.list-group-item {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem !important;
    margin-bottom: 0.5rem;
}

.badge {
    font-size: 0.75em;
}

/* Images/Vidéos actuelles */
.current-thumbnail img,
.current-video video {
    transition: transform 0.2s ease;
}

.current-thumbnail img:hover {
    transform: scale(1.05);
}

/* Zone d'upload moderne */
.upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    overflow: hidden;
}

.upload-zone:hover {
    border-color: #003366;
    background-color: #e9ecef;
}

.upload-placeholder {
    cursor: pointer;
    transition: all 0.2s ease;
}

.upload-placeholder:hover {
    background-color: rgba(0, 51, 102, 0.05);
}

.upload-placeholder:hover i {
    transform: scale(1.1);
}

.upload-placeholder i {
    transition: transform 0.2s ease;
}

.upload-preview {
    padding: 1.5rem;
}

.upload-preview img,
.upload-preview video {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.upload-preview img:hover {
    transform: scale(1.02);
}

.current-file {
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 10px;
    border: 2px solid #28a745;
}

.current-file i {
    opacity: 0.8;
}

.upload-info {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.upload-info .badge {
    font-size: 0.85rem;
    padding: 0.4em 0.8em;
}

.lesson-upload-zone .upload-preview {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 1rem;
}

.lesson-upload-zone .lesson-file-visual {
    display: flex;
    align-items: center;
    justify-content: center;
}

.lesson-upload-zone .upload-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.lesson-existing-file {
    border: 1px dashed #94a3b8;
    background-color: #f8fafc;
}

.lesson-existing-icon i {
    opacity: 0.85;
}

.lesson-upload-zone .lesson-remove-btn {
    min-width: 140px;
}
</style>
@endpush
@endsection

