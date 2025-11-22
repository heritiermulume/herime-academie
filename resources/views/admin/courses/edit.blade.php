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
@section('admin-subtitle', "Actualisez l'ensemble des informations du cours « " . Str::limit(html_entity_decode($course->title, ENT_QUOTES, 'UTF-8'), 60) . " »")
@section('admin-actions')
    <a href="{{ route('admin.courses') }}" class="btn btn-light" data-temp-upload-cancel>
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@include('partials.upload-progress-modal')

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
                            <div class="col-md-8">
                                <label for="title" class="form-label fw-bold">
                                    Titre du cours <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                       id="title" 
                                       name="title" 
                                       value="{{ old('title', $course->title) }}" 
                                       placeholder="Ex: Formation complète en développement web"
                                       required>
                                <small class="form-text text-muted">Le titre principal du cours</small>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="instructor_id" class="form-label fw-bold">Instructeur <span class="text-danger">*</span></label>
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
                            
                            <div class="col-md-6">
                                <label for="category_id" class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
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
                            
                            <div class="col-md-3">
                                <label for="level" class="form-label fw-bold">Niveau <span class="text-danger">*</span></label>
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
                            
                            <div class="col-md-3">
                                <label for="language" class="form-label fw-bold">Langue <span class="text-danger">*</span></label>
                                <select class="form-select @error('language') is-invalid @enderror" id="language" name="language" required>
                                    <option value="">Sélectionner</option>
                                    <option value="fr" {{ old('language', $course->language) == 'fr' ? 'selected' : '' }}>Français</option>
                                    <option value="en" {{ old('language', $course->language) == 'en' ? 'selected' : '' }}>English</option>
                                </select>
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12">
                                <label for="description" class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="5"
                                          placeholder="Décrivez le contenu et les objectifs du cours..."
                                          required>{{ old('description', $course->description) }}</textarea>
                                <small class="form-text text-muted">Une description détaillée qui sera visible par les étudiants</small>
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
                                    <input type="hidden" id="thumbnail_chunk_path" name="thumbnail_chunk_path" value="{{ old('thumbnail_chunk_path') }}">
                                    <input type="hidden" id="thumbnail_chunk_name" name="thumbnail_chunk_name" value="{{ old('thumbnail_chunk_name') }}">
                                    <input type="hidden" id="thumbnail_chunk_size" name="thumbnail_chunk_size" value="{{ old('thumbnail_chunk_size') }}">
                                    <div class="upload-placeholder text-center p-4" onclick="document.getElementById('thumbnail').click()">
                                        <i class="fas fa-image fa-3x text-primary mb-3"></i>
                                        <p class="mb-2"><strong>Cliquez pour sélectionner une image</strong></p>
                                        <p class="text-muted small mb-0">Format : JPG, PNG, WEBP | Max : 5MB</p>
                                        <p class="text-muted small">Recommandé : 1920x1080px (16:9)</p>
                                    </div>
                                    <div class="upload-preview d-none">
                                        <p class="fw-bold text-info text-center mb-2">
                                            <i class="fas fa-eye me-1"></i>Nouvelle image :
                                        </p>
                                        <img src="" alt="Preview" class="img-fluid rounded mx-auto d-block" style="max-width: 100%; max-height: 300px; border: 3px solid #28a745;">
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
                                <small class="text-muted d-block mt-2">Laissez vide pour conserver l'image actuelle.</small>
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
                                        <input type="hidden" id="video_preview_path" name="video_preview_path" value="{{ old('video_preview_path', (!empty($course->video_preview) && !filter_var($course->video_preview, FILTER_VALIDATE_URL)) ? $course->video_preview : '') }}">
                                        <input type="hidden" id="video_preview_name" name="video_preview_name" value="{{ old('video_preview_name') }}">
                                        <input type="hidden" id="video_preview_size" name="video_preview_size" value="{{ old('video_preview_size') }}">
                                        <div class="upload-placeholder text-center p-3" onclick="document.getElementById('video_preview_file').click()">
                                            <i class="fas fa-video fa-2x text-success mb-2"></i>
                                            <p class="mb-1 small"><strong>Cliquez pour sélectionner une vidéo</strong></p>
                                            <p class="text-muted small mb-0">Format : MP4, WEBM | Max : 10&nbsp;Go</p>
                                        </div>
                                        <div class="upload-preview d-none">
                                            <p class="fw-bold text-info text-center mb-2">
                                                <i class="fas fa-eye me-1"></i>Nouvelle vidéo :
                                            </p>
                                            <video controls class="w-100 rounded" style="max-height: 200px; border: 3px solid #28a745;"></video>
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
                            <div class="col-md-3">
                                <label for="price" class="form-label fw-bold">Prix ({{ $baseCurrency ?? 'USD' }}) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" value="{{ old('price', $course->price) }}" min="0" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="sale_price" class="form-label fw-bold">Prix de vente ({{ $baseCurrency ?? 'USD' }})</label>
                                <input type="number" class="form-control @error('sale_price') is-invalid @enderror" 
                                       id="sale_price" name="sale_price" value="{{ old('sale_price', $course->sale_price) }}" min="0">
                                @error('sale_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="sale_start_at" class="form-label fw-bold">Début de promotion</label>
                                <input type="datetime-local" class="form-control @error('sale_start_at') is-invalid @enderror"
                                       id="sale_start_at" name="sale_start_at"
                                       value="{{ old('sale_start_at', optional($course->sale_start_at)->format('Y-m-d\TH:i')) }}">
                                <small class="form-text text-muted">Laissez vide pour rendre la promotion active immédiatement.</small>
                                @error('sale_start_at')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="sale_end_at" class="form-label fw-bold">Fin de promotion</label>
                                <input type="datetime-local" class="form-control @error('sale_end_at') is-invalid @enderror"
                                       id="sale_end_at" name="sale_end_at"
                                       value="{{ old('sale_end_at', optional($course->sale_end_at)->format('Y-m-d\TH:i')) }}">
                                <small class="form-text text-muted">La promotion s'arrêtera automatiquement à cette date.</small>
                                @error('sale_end_at')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
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
                                        Publier immédiatement
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
                                    <input class="form-check-input" type="checkbox" id="show_students_count" name="show_students_count" value="1" 
                                           {{ old('show_students_count', $course->show_students_count) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_students_count">
                                        Afficher le nombre d'étudiants inscrits sur la carte du cours
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
                                    <div class="current-file mb-3 download-current-file" id="downloadCurrentFile">
                                        <p class="fw-bold mb-2"><i class="fas fa-check-circle text-success me-1"></i>Fichier actuel :</p>
                                        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2 p-2 bg-light rounded w-100">
                                            <i class="fas fa-file-archive fa-2x text-primary"></i>
                                            <div class="flex-grow-1 text-break text-center text-md-start">
                                                <strong>{{ basename($course->download_file_path) }}</strong>
                                                <br>
                                                <small class="text-muted">Fichier déjà uploadé</small>
                                            </div>
                                            <a href="{{ $course->download_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary w-100 w-md-auto">
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
                                               accept=".zip,.pdf,.doc,.docx,.rar,.7z,.tar,.gz"
                                               onchange="handleDownloadFileUpload(this)">
                                        <input type="hidden" id="download_file_chunk_path" name="download_file_chunk_path" value="{{ old('download_file_chunk_path') }}">
                                        <input type="hidden" id="download_file_chunk_name" name="download_file_chunk_name" value="{{ old('download_file_chunk_name') }}">
                                        <input type="hidden" id="download_file_chunk_size" name="download_file_chunk_size" value="{{ old('download_file_chunk_size') }}">
                                        <div class="upload-placeholder text-center p-4" onclick="document.getElementById('download_file_path').click()">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                            <p class="mb-2"><strong>Cliquez pour sélectionner un fichier</strong></p>
                                            <p class="text-muted small mb-0">Formats : ZIP, PDF, DOC, DOCX, RAR, 7Z, TAR, GZ</p>
                                            <p class="text-muted small">Maximum : 10&nbsp;Go</p>
                                        </div>
                                        <div class="upload-preview d-none download-upload-preview">
                                            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 p-3 w-100 download-preview-item">
                                                <i class="fas fa-file-archive fa-3x text-primary"></i>
                                                <div class="flex-grow-1">
                                                    <div class="upload-info">
                                                        <span class="badge bg-primary file-name"></span>
                                                        <span class="badge bg-info file-size ms-2"></span>
                                                    </div>
                                                    <div class="upload-info mt-2">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="clearDownloadFile(true)">
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
                            <div class="col-md-6">
                                <label for="requirements" class="form-label fw-bold">Prérequis</label>
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
                            
                            <div class="col-md-6">
                                <label for="what_you_will_learn" class="form-label fw-bold">Ce que vous apprendrez</label>
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
                            <div class="col-md-6">
                                <label for="meta_description" class="form-label fw-bold">Description SEO</label>
                                <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                          id="meta_description" name="meta_description" rows="3" maxlength="160">{{ old('meta_description', $course->meta_description) }}</textarea>
                                <small class="text-muted">Maximum 160 caractères</small>
                                @error('meta_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="meta_keywords" class="form-label fw-bold">Mots-clés SEO</label>
                                <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" 
                                       id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $course->meta_keywords) }}" 
                                       placeholder="mot-clé1, mot-clé2, mot-clé3">
                                @error('meta_keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="tags" class="form-label fw-bold">Tags</label>
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
                <div class="card shadow-sm mb-4 course-content-card">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Contenu du cours</h5>
                    </div>
                    <div class="card-body course-content-card__body p-0">
                        <div id="sections-container">
                            <!-- Les sections seront ajoutées dynamiquement -->
                        </div>

                        <button type="button" class="btn btn-primary" onclick="addSection()">
                            <i class="fas fa-plus me-1"></i>Ajouter une section
                        </button>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-sm mb-4 form-actions-card">
                    <div class="card-body">
                        <div class="form-actions d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center">
                            <a href="{{ route('admin.courses') }}" class="btn btn-secondary form-actions__btn" data-temp-upload-cancel>
                                <i class="fas fa-times me-1"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary form-actions__btn form-actions__btn--primary">
                                <i class="fas fa-check me-1"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </div>
            </form>

@push('scripts')
    @once
        <script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
    @endonce
@endpush

@push('scripts')
<script>
// Constantes de validation
const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5MB
const MAX_VIDEO_SIZE = 10 * 1024 * 1024 * 1024; // 10GB
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
const LESSON_MAX_FILE_SIZE = 10 * 1024 * 1024 * 1024; // 10GB
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
let cachedSaleStartValue = null;
let cachedSaleEndValue = null;

const CHUNK_SIZE_BYTES = 1 * 1024 * 1024; // 1MB
const DOWNLOAD_ALLOWED_EXTENSIONS = ['.zip', '.pdf', '.doc', '.docx', '.rar', '.7z', '.tar', '.gz'];
const MAX_DOWNLOAD_FILE_SIZE = 10 * 1024 * 1024 * 1024; // 10GB
const CHUNK_UPLOAD_ENDPOINT = (function() {
    const origin = window.location.origin.replace(/\/+$/, '');
    const path = "{{ trim(parse_url(route('admin.uploads.chunk'), PHP_URL_PATH), '/') }}";
    return `${origin}/${path}`;
})();
if (!window.__tempUploadConfig) {
    window.__tempUploadConfig = {
        prefix: '{{ \App\Services\FileUploadService::TEMPORARY_BASE_PATH }}/',
        endpoint: "{{ route('uploads.temp.destroy') }}",
    };
} else {
    window.__tempUploadConfig.prefix = '{{ \App\Services\FileUploadService::TEMPORARY_BASE_PATH }}/';
    window.__tempUploadConfig.endpoint = "{{ route('uploads.temp.destroy') }}";
}

const TempUploadManager = (() => {
    if (window.TempUploadManager) {
        window.TempUploadManager.configure(window.__tempUploadConfig);
        return window.TempUploadManager;
    }

    let config = window.__tempUploadConfig;
    const state = {
        active: new Set(),
        queue: new Set(),
        timer: null,
        isSubmitting: false,
    };

    const getToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const isTemporary = (path) => {
        return typeof path === 'string'
            && config?.prefix
            && path.startsWith(config.prefix);
    };

    const sendRequest = (paths, keepalive) => {
        const endpoint = config?.endpoint;
        const token = getToken();
        if (!endpoint || !token || !Array.isArray(paths) || paths.length === 0) {
            return;
        }

        try {
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ paths }),
                keepalive: !!keepalive,
            }).catch(() => {});
        } catch (error) {
            // Ignorer les erreurs réseau
        }
    };

    const performFlush = ({ includeActive = false, keepalive = false } = {}) => {
        if (state.timer) {
            clearTimeout(state.timer);
            state.timer = null;
        }

        const paths = new Set();

        state.queue.forEach((path) => paths.add(path));
        state.queue.clear();

        if (includeActive) {
            state.active.forEach((path) => paths.add(path));
            state.active.clear();
        }

        if (!paths.size) {
            return;
        }

        sendRequest(Array.from(paths), keepalive);
    };

    const scheduleFlush = () => {
        if (state.timer) {
            return;
        }
        state.timer = setTimeout(() => {
            state.timer = null;
            performFlush();
        }, 400);
    };

    return window.TempUploadManager = {
        configure(newConfig) {
            config = newConfig || config;
        },
        register(path) {
            if (isTemporary(path)) {
                state.active.add(path);
            }
        },
        queueDelete(path) {
            if (!isTemporary(path)) {
                return;
            }
            state.active.delete(path);
            state.queue.add(path);
            scheduleFlush();
        },
        flush(options = {}) {
            performFlush(options);
        },
        flushAll(options = {}) {
            performFlush({ includeActive: true, keepalive: options.keepalive });
        },
        markSubmitting() {
            state.isSubmitting = true;
        },
        isSubmitting() {
            return state.isSubmitting;
        },
    };
})();

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function isTemporaryPath(path) {
    const prefix = window.__tempUploadConfig?.prefix;
    return typeof path === 'string' && prefix && path.startsWith(prefix);
}

function registerTemporaryPath(path) {
    TempUploadManager.register(path);
}

function queueTemporaryDeletion(path) {
    TempUploadManager.queueDelete(path);
}

function getThumbnailHiddenInputs() {
    return {
        path: document.getElementById('thumbnail_chunk_path'),
        name: document.getElementById('thumbnail_chunk_name'),
        size: document.getElementById('thumbnail_chunk_size'),
    };
}

function resetThumbnailHiddenFields(options = {}) {
    const { preserve = false } = options;
    const hidden = getThumbnailHiddenInputs();
    if (!hidden.path) {
        return;
    }

    if (!preserve) {
        const previousPath = hidden.path.value;
        if (previousPath && isTemporaryPath(previousPath)) {
            queueTemporaryDeletion(previousPath);
        }
        hidden.path.value = '';
    }

    if (!preserve && hidden.name) {
        hidden.name.value = '';
    }
    if (!preserve && hidden.size) {
        hidden.size.value = '';
    }
}

let thumbnailUploadResumable = null;
let thumbnailUploadTaskId = null;
let previewUploadResumable = null;
let previewUploadTaskId = null;
const lessonUploadControllers = new Map();
let downloadUploadResumable = null;
let downloadUploadTaskId = null;
let downloadUploadSuppressError = false;

function resetFileInput(input, options = {}) {
    if (!input) {
        return null;
    }

    const { onRebind } = options;

    try {
        input.value = '';
    } catch (error) {
        // ignore value reset errors
    }

    if (input.files && input.files.length) {
        try {
            const emptyFiles = new DataTransfer().files;
            input.files = emptyFiles;
        } catch (error) {
            // ignore DataTransfer reset errors
        }
    }

    const hasValue = input.value && input.value !== '';
    const hasFiles = input.files && input.files.length > 0;
    if (!hasValue && !hasFiles) {
        return input;
    }

    const parent = input.parentNode;
    if (!parent) {
        return input;
    }

    const replacement = input.cloneNode(true);
    replacement.value = '';
    parent.replaceChild(replacement, input);

    if (typeof onRebind === 'function') {
        try {
            onRebind(replacement);
        } catch (error) {
            // ignore rebind errors
        }
    }

    return replacement;
}

function createUploadTask(fileName, fileSize, description = 'Téléversement en cours…', extra = {}) {
    if (!window.UploadProgressModal) {
        return null;
    }
    const taskId = `admin-upload-${Date.now()}-${Math.random().toString(16).slice(2, 10)}`;
    const baseConfig = {
        label: fileName,
        description,
        sizeLabel: formatFileSize(fileSize),
        initialMessage: 'Préparation du téléversement…',
    };
    const taskConfig = Object.assign({}, baseConfig, extra);
    if (typeof taskConfig.onCancel === 'function' && typeof taskConfig.cancelable === 'undefined') {
        taskConfig.cancelable = true;
    }
    window.UploadProgressModal.startTask(taskId, taskConfig);
    return taskId;
}

function updateUploadTask(taskId, percent, message) {
    if (taskId && window.UploadProgressModal) {
        window.UploadProgressModal.updateTask(taskId, percent, message);
    }
}

function completeUploadTask(taskId, message = 'Téléversement terminé') {
    if (taskId && window.UploadProgressModal) {
        window.UploadProgressModal.completeTask(taskId, message);
    }
}

function errorUploadTask(taskId, message = 'Erreur lors du téléversement') {
    if (taskId && window.UploadProgressModal) {
        window.UploadProgressModal.errorTask(taskId, message);
    }
}

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

document.addEventListener('DOMContentLoaded', function() {
    const videoLinkInput = document.getElementById('video_preview');
    if (videoLinkInput) {
        videoLinkInput.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                const pathInput = document.getElementById('video_preview_path');
                const nameInput = document.getElementById('video_preview_name');
                const sizeInput = document.getElementById('video_preview_size');
                if (pathInput) {
                    const previousPath = pathInput.value;
                    if (previousPath && isTemporaryPath(previousPath)) {
                        queueTemporaryDeletion(previousPath);
                    }
                    pathInput.value = '';
                }
                if (nameInput) nameInput.value = '';
                if (sizeInput) sizeInput.value = '';
            }
        });
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
            resetFileInput(input);
            return;
        }
        
        if (file.size > MAX_IMAGE_SIZE) {
            showError(errorDiv, '❌ Le fichier est trop volumineux. Maximum 5MB.');
            resetFileInput(input);
            return;
        }

        if (typeof Resumable === 'undefined') {
            showError(errorDiv, '❌ Votre navigateur ne supporte pas l’upload fractionné. Veuillez le mettre à jour ou utiliser un autre navigateur.');
            resetFileInput(input);
            return;
        }

        resetThumbnailHiddenFields();
        
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

        document.querySelectorAll('.current-thumbnail').forEach((element) => element.classList.add('d-none'));

        zone.style.borderColor = '#0d6efd';
        startThumbnailChunkUpload(file, input);
    }
}

function startThumbnailChunkUpload(file, input) {
    const token = getCsrfToken();
    const errorDiv = document.getElementById('thumbnailError');
    const zone = document.getElementById('thumbnailUploadZone');

    if (!token) {
        showError(errorDiv, '❌ Impossible de récupérer le jeton CSRF pour l’upload.');
        resetFileInput(input);
        return;
    }

    if (thumbnailUploadResumable) {
        try {
            thumbnailUploadResumable.cancel();
        } catch (error) {
            // ignore
        }
        thumbnailUploadResumable = null;
    }
    if (thumbnailUploadTaskId) {
        errorUploadTask(thumbnailUploadTaskId, 'Téléversement annulé');
        thumbnailUploadTaskId = null;
    }

    const resumable = new Resumable({
        target: CHUNK_UPLOAD_ENDPOINT,
        chunkSize: CHUNK_SIZE_BYTES,
        simultaneousUploads: 3,
        testChunks: false,
        throttleProgressCallbacks: 1,
        fileParameterName: 'file',
        fileType: ['png', 'jpg', 'jpeg', 'webp'],
        withCredentials: true,
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
        query: () => ({
            upload_type: 'thumbnail',
            original_name: file.name,
        }),
    });

    thumbnailUploadResumable = resumable;

    thumbnailUploadTaskId = createUploadTask(
        file.name,
        file.size,
        'Téléversement de l’image de couverture',
        {
            onCancel: () => clearThumbnail({ skipModalCancel: true }),
            cancelLabel: 'Annuler',
        }
    );

    resumable.on('fileProgress', function(resumableFile) {
        const percent = Math.max(0, Math.min(100, Math.round(resumableFile.progress() * 100)));
        updateUploadTask(thumbnailUploadTaskId, percent, 'Téléversement en cours…');
    });

    const handleUploadError = (message, { suppressModal = false } = {}) => {
        const displayMessage = typeof message === 'string' && message.trim() !== ''
            ? message
            : 'Erreur lors du téléversement de l’image.';

        if (thumbnailUploadTaskId) {
            if (suppressModal && window.UploadProgressModal && typeof window.UploadProgressModal.cancelTask === 'function') {
                window.UploadProgressModal.cancelTask(thumbnailUploadTaskId);
            } else {
                errorUploadTask(thumbnailUploadTaskId, displayMessage);
            }
            thumbnailUploadTaskId = null;
        }

        thumbnailUploadResumable = null;

        if (!suppressModal) {
            showError(errorDiv, displayMessage);
        }

        zone.style.borderColor = '#dc3545';
        clearThumbnail({ skipModalCancel: true, preserveHidden: false, restoreExistingPreview: true });
    };

    resumable.on('fileSuccess', function(resumableFile, response) {
        let payload = response;
        if (typeof response === 'string') {
            try {
                payload = JSON.parse(response);
            } catch (error) {
                payload = null;
            }
        }

        if (!payload || !payload.path) {
            handleUploadError('Réponse invalide du serveur.');
            return;
        }

        const hidden = getThumbnailHiddenInputs();
        const previousPath = hidden.path ? hidden.path.value : '';

        if (hidden.path) hidden.path.value = payload.path;
        if (hidden.name) hidden.name.value = payload.filename || file.name;
        if (hidden.size) hidden.size.value = payload.size || file.size;

        if (previousPath && previousPath !== payload.path) {
            queueTemporaryDeletion(previousPath);
        }
        registerTemporaryPath(payload.path);

        if (thumbnailUploadTaskId) {
            completeUploadTask(thumbnailUploadTaskId, 'Image importée avec succès');
            thumbnailUploadTaskId = null;
        }

        thumbnailUploadResumable = null;

        zone.style.borderColor = '#28a745';
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';

        resetFileInput(input);
    });

    resumable.on('fileError', function(resumableFile, message) {
        handleUploadError(message);
    });

    resumable.on('error', function(message) {
        handleUploadError(message);
    });

    resumable.on('cancel', function() {
        handleUploadError('Téléversement annulé.', { suppressModal: true });
    });

    resumable.on('chunkingComplete', function() {
        if (!resumable.isUploading()) {
            resumable.upload();
        }
    });

    resumable.addFile(file);
}

function clearThumbnail(options = {}) {
    const { skipModalCancel = false, preserveHidden = false, restoreExistingPreview = true } = options;
    const zone = document.getElementById('thumbnailUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const input = document.getElementById('thumbnail');
    const errorDiv = document.getElementById('thumbnailError');
    
    if (thumbnailUploadResumable) {
        try {
            thumbnailUploadResumable.cancel();
        } catch (error) {
            // ignore
        }
    }
    thumbnailUploadResumable = null;

    if (thumbnailUploadTaskId) {
        const progressModal = window.UploadProgressModal;
        if (!skipModalCancel && progressModal && typeof progressModal.cancelTask === 'function') {
            progressModal.cancelTask(thumbnailUploadTaskId);
        }
        thumbnailUploadTaskId = null;
    }

    if (!preserveHidden) {
        resetThumbnailHiddenFields();
    }

    resetFileInput(input);

    const img = preview.querySelector('img');
    if (img) {
        img.src = '';
    }
    const fileNameBadge = preview.querySelector('.file-name');
    if (fileNameBadge) {
        fileNameBadge.textContent = '';
    }
    const fileSizeBadge = preview.querySelector('.file-size');
    if (fileSizeBadge) {
        fileSizeBadge.textContent = '';
    }
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    preview.classList.add('d-none');
    placeholder.classList.remove('d-none');
    zone.style.borderColor = '#dee2e6';

    if (restoreExistingPreview) {
        document.querySelectorAll('.current-thumbnail').forEach((element) => element.classList.remove('d-none'));
    }
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
            resetFileInput(input);
            return;
        }
        
        if (file.size > MAX_VIDEO_SIZE) {
            showError(errorDiv, `❌ Le fichier est trop volumineux. Maximum ${formatFileSize(MAX_VIDEO_SIZE)}.`);
            resetFileInput(input);
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

        uploadVideoPreviewAjax(input);
    }
}

function clearVideo(options = {}) {
    const {
        cancelUpload = true,
        preserveError = false,
        clearHiddenFields = true,
        skipModalCancel = false,
    } = options;

    const zone = document.getElementById('videoUploadZone');
    if (!zone) {
        previewUploadResumable = null;
        previewUploadTaskId = null;
        return;
    }

    const pathInput = document.getElementById('video_preview_path');
    const previousPath = pathInput ? pathInput.value : '';

    if (cancelUpload && previewUploadResumable) {
        try {
            previewUploadResumable.cancel();
        } catch (error) {
            // ignore cancellation errors
        }
    }

    const progressModal = window.UploadProgressModal;
    if (!skipModalCancel && progressModal && typeof progressModal.cancelTask === 'function' && previewUploadTaskId) {
        progressModal.cancelTask(previewUploadTaskId);
    }
    previewUploadTaskId = null;
    previewUploadResumable = null;

    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const videoElement = preview ? preview.querySelector('video') : null;
    const fileNameBadge = preview ? preview.querySelector('.file-name') : null;
    const fileSizeBadge = preview ? preview.querySelector('.file-size') : null;

    if (videoElement) {
        try {
            videoElement.pause();
        } catch (error) {
            // ignore pause errors
        }
        videoElement.removeAttribute('src');
        const source = videoElement.querySelector('source');
        if (source) {
            source.removeAttribute('src');
        }
        videoElement.load();
    }

    if (fileNameBadge) fileNameBadge.textContent = '';
    if (fileSizeBadge) fileSizeBadge.textContent = '';

    if (preview) {
        preview.classList.add('d-none');
    }
    if (placeholder) {
        placeholder.classList.remove('d-none');
    }

    const progressWrapper = document.getElementById('videoPreviewProgress');
    if (progressWrapper) {
        progressWrapper.style.display = 'none';
        const progressBar = progressWrapper.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = '0%';
        }
    }

    resetFileInput(document.getElementById('video_preview_file'));

    const errorDiv = document.getElementById('videoError');
    if (errorDiv) {
        if (preserveError && errorDiv.textContent) {
            errorDiv.style.display = 'block';
        } else {
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
        }
    }

    if (clearHiddenFields) {
        const nameInput = document.getElementById('video_preview_name');
        const sizeInput = document.getElementById('video_preview_size');
        if (pathInput && previousPath && isTemporaryPath(previousPath)) {
            queueTemporaryDeletion(previousPath);
        }
        if (pathInput) pathInput.value = '';
        if (nameInput) nameInput.value = '';
        if (sizeInput) sizeInput.value = '';
    }
}

function uploadVideoPreviewAjax(input) {
    const file = input.files && input.files[0];
    if (!file) {
        return;
    }

    if (typeof Resumable === 'undefined') {
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) {
        return;
    }

    if (previewUploadResumable) {
        try {
            previewUploadResumable.cancel();
        } catch (error) {
        }
        previewUploadResumable = null;
    }
    if (previewUploadTaskId) {
        errorUploadTask(previewUploadTaskId, 'Téléversement annulé');
        previewUploadTaskId = null;
    }

    const progressWrapper = document.getElementById('videoPreviewProgress');
    const progressBar = progressWrapper ? progressWrapper.querySelector('.progress-bar') : null;
    if (progressWrapper) {
        progressWrapper.style.display = 'block';
    }
    if (progressBar) {
        progressBar.style.width = '0%';
    }

    const resumable = new Resumable({
        target: CHUNK_UPLOAD_ENDPOINT,
        chunkSize: CHUNK_SIZE_BYTES,
        simultaneousUploads: 3,
        testChunks: false,
        throttleProgressCallbacks: 1,
        fileParameterName: 'file',
        fileType: ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'mkv', 'pdf', 'zip', 'rar', '7z', 'tar', 'gz', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'csv'],
        withCredentials: true,
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
        query: () => ({
            upload_type: 'preview',
            original_name: file.name,
        }),
    });

    previewUploadTaskId = createUploadTask(file.name, file.size, 'Téléversement de la vidéo de prévisualisation', {
        onCancel: () => clearVideo({ skipModalCancel: true }),
        cancelLabel: 'Annuler'
    });

    resumable.on('fileProgress', function(resumableFile) {
        const percent = Math.max(0, Math.min(100, Math.round(resumableFile.progress() * 100)));
        if (progressBar) {
            progressBar.style.width = percent + '%';
        }
        updateUploadTask(previewUploadTaskId, percent, 'Téléversement en cours…');
    });

    resumable.on('fileSuccess', function(resumableFile, response) {
        let payload = response;
        if (typeof response === 'string') {
            try {
                payload = JSON.parse(response);
            } catch (error) {
                payload = null;
            }
        }

        if (!payload || !payload.path) {
            errorUploadTask(previewUploadTaskId, 'Réponse invalide du serveur.');
            if (progressWrapper) {
                progressWrapper.style.display = 'none';
            }
            return;
        }

        const pathInput = document.getElementById('video_preview_path');
        const nameInput = document.getElementById('video_preview_name');
        const sizeInput = document.getElementById('video_preview_size');
        const previousPath = pathInput ? pathInput.value : '';
        if (pathInput) {
            pathInput.value = payload.path;
        }
        if (nameInput) {
            nameInput.value = payload.filename || file.name;
        }
        if (sizeInput) {
            sizeInput.value = payload.size || file.size;
        }
        if (previousPath && previousPath !== payload.path) {
            queueTemporaryDeletion(previousPath);
        }
        registerTemporaryPath(payload.path);

        const urlField = document.getElementById('video_preview');
        if (urlField && !urlField.value) {
            urlField.value = '';
        }

        completeUploadTask(previewUploadTaskId, 'Vidéo importée avec succès');
        previewUploadTaskId = null;
        if (progressWrapper) {
            progressWrapper.style.display = 'none';
        }
        resetFileInput(input);
        previewUploadResumable = null;
    });

    const handleUploadError = (message, options = {}) => {
        const { suppressMessage = false, resetField = true } = options;
        const errorDiv = document.getElementById('videoError');
        const displayMessage = (typeof message === 'string' && message.trim() !== '')
            ? message
            : 'Erreur lors du téléversement de la vidéo.';

        if (progressWrapper) {
            progressWrapper.style.display = 'none';
        }
        if (progressBar) {
            progressBar.style.width = '0%';
        }

        if (previewUploadTaskId) {
            if (suppressMessage && window.UploadProgressModal && typeof window.UploadProgressModal.cancelTask === 'function') {
                window.UploadProgressModal.cancelTask(previewUploadTaskId);
            } else {
                errorUploadTask(previewUploadTaskId, displayMessage);
            }
            previewUploadTaskId = null;
        }

        if (resetField) {
            clearVideo({ cancelUpload: false, preserveError: !suppressMessage });
        }

        if (!suppressMessage && errorDiv) {
            errorDiv.textContent = displayMessage;
            errorDiv.style.display = 'block';
        } else if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
        }

        previewUploadResumable = null;
    };

    resumable.on('fileError', function(resumableFile, message) {
        let errorMessage = message;
        if (typeof message === 'object' && message !== null && message.message) {
            errorMessage = message.message;
        }
        handleUploadError(typeof errorMessage === 'string' ? errorMessage : 'Erreur lors du téléversement de la vidéo.');
    });

    resumable.on('error', function(message) {
        handleUploadError(typeof message === 'string' ? message : 'Erreur réseau lors du téléversement.');
    });

    resumable.on('cancel', function() {
        handleUploadError('Téléversement annulé.', { suppressMessage: true, resetField: true });
    });

    resumable.on('chunkingComplete', function(resumableFile) {
        if (!resumable.isUploading()) {
            resumable.upload();
        }
    });

    resumable.addFile(file);
    previewUploadResumable = resumable;
}

function cancelAllUploads() {
    const progressModal = window.UploadProgressModal;
    if (progressModal && typeof progressModal.getActiveTaskIds === 'function') {
        progressModal.getActiveTaskIds().forEach((taskId) => {
            progressModal.cancelTask(taskId);
        });
    }

    if (thumbnailUploadResumable) {
        try {
            thumbnailUploadResumable.cancel();
        } catch (error) {
            // ignore
        }
        thumbnailUploadResumable = null;
    }

    if (thumbnailUploadTaskId && progressModal) {
        progressModal.cancelTask(thumbnailUploadTaskId);
    }
    thumbnailUploadTaskId = null;
    clearThumbnail({ skipModalCancel: true });

    if (previewUploadResumable) {
        try {
            previewUploadResumable.cancel();
        } catch (error) {
            // ignore
        }
        previewUploadResumable = null;
    }

    if (previewUploadTaskId && progressModal) {
        progressModal.cancelTask(previewUploadTaskId);
    }
    previewUploadTaskId = null;

    resetFileInput(document.getElementById('video_preview_file'));
    clearVideo({ skipModalCancel: true });

    const activeLessons = Array.from(lessonUploadControllers.entries());
    activeLessons.forEach(([uniqueId, controller]) => {
        if (controller && controller.resumable) {
            try {
                controller.resumable.cancel();
            } catch (error) {
                // ignore
            }
        }
        if (controller && controller.taskId && progressModal) {
            progressModal.cancelTask(controller.taskId);
        }
        clearLessonFile(uniqueId, true);
    });
    lessonUploadControllers.clear();

    if (downloadUploadResumable) {
        downloadUploadSuppressError = true;
        try {
            downloadUploadResumable.cancel();
        } catch (error) {
            // ignore
        }
        downloadUploadResumable = null;
    }

    if (downloadUploadTaskId && progressModal) {
        progressModal.cancelTask(downloadUploadTaskId);
        downloadUploadTaskId = null;
    }

    clearDownloadFile(true);

    if (progressModal && typeof progressModal.hide === 'function') {
        progressModal.hide();
    }
}

window.cancelAllUploads = cancelAllUploads;

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
    sectionDiv.className = 'card border-0 shadow-sm mb-3 course-section-card';
    sectionDiv.id = `section-${sectionId}`;
    sectionDiv.innerHTML = `
        <div class="card-header bg-gradient-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-white section-title-label">${sectionTitle || `Section ${sectionOrder}`}</h6>
                <i class="fas fa-times course-section-remove-icon text-white" role="button" onclick="removeSection(${sectionId})" aria-label="Supprimer la section"></i>
            </div>
        </div>
        <div class="card-body course-section-card__body">
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
            <div id="lessons-${sectionId}"></div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLesson(${sectionId})">
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
        const titleInput = card.querySelector('.section-title-input');
        const titleLabel = card.querySelector('.section-title-label');
        if (!titleLabel) {
            return;
        }
        if (titleInput && titleInput.value.trim() !== '') {
            titleLabel.textContent = titleInput.value;
        } else {
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
    lessonCount += 1;
    const lessonDisplayNumber = lessonCount;

    const existingFilePath = lessonData?.existing_file_path ?? '';
    const existingFileName = lessonData?.existing_file_name ?? (existingFilePath ? existingFilePath.split('/').pop() : '');
    const existingFileUrl = lessonData?.existing_file_url ?? '';
    const removeExistingFlag = toBoolean(lessonData?.remove_existing_file) ? 1 : 0;

    const lessonDiv = document.createElement('div');
    lessonDiv.className = 'card border-0 shadow-sm mb-2 course-lesson-card';
    lessonDiv.innerHTML = `
        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center course-lesson-card__header">
            <h6 class="mb-0 text-white"><i class="fas fa-play-circle me-2 text-white-50"></i>Leçon ${lessonDisplayNumber}</h6>
            <i class="fas fa-times course-lesson-remove-icon text-white" role="button" onclick="removeLesson(this)" aria-label="Supprimer la leçon"></i>
        </div>
        <div class="card-body course-lesson-card__body">
            <input type="hidden" name="${lessonPrefix}[id]" value="${lessonData?.id ?? ''}">
            <input type="hidden" name="${lessonPrefix}[existing_file_path]" value="${existingFilePath}" data-lesson-existing-path="${lessonUniqueId}">
            <input type="hidden" name="${lessonPrefix}[remove_existing_file]" value="${removeExistingFlag}" data-lesson-remove-flag="${lessonUniqueId}">
            <div class="row gy-3">
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
                <div class="col-md-3">
                    <label class="form-label">Aperçu</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="${lessonPrefix}[is_preview]" value="1">
                        <label class="form-check-label">Gratuit</label>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="${lessonPrefix}[description]" rows="2" placeholder="Description de la leçon"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fichier ou média de la leçon</label>
                    <div class="upload-zone lesson-upload-zone" id="lessonUploadZone-${lessonUniqueId}" data-lesson-unique="${lessonUniqueId}" data-lesson-section="${sectionId}" data-lesson-index="${lessonIndex}" onclick="triggerLessonFile('${lessonUniqueId}', event)">
                        <input type="file"
                               class="form-control d-none lesson-file-input"
                               id="lesson_file_${lessonUniqueId}"
                               name="${lessonPrefix}[content_file]"
                               accept="video/mp4,video/webm,video/ogg,video/avi,video/x-msvideo,video/quicktime,video/x-ms-wmv,video/x-matroska,.mp4,.webm,.ogg,.avi,.mov,.wmv,.mkv,.pdf,.zip,.rar,.7z,.tar,.gz,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv"
                               onchange="handleLessonFileUpload('${lessonUniqueId}', this)">
                        <input type="hidden"
                               name="${lessonPrefix}[content_file_path]"
                               value="${lessonData?.content_file_path ?? ''}"
                               data-lesson-path="${lessonUniqueId}">
                        <input type="hidden"
                               name="${lessonPrefix}[content_file_name]"
                               value="${lessonData?.content_file_name ?? ''}"
                               data-lesson-name="${lessonUniqueId}">
                        <input type="hidden"
                               name="${lessonPrefix}[content_file_size]"
                               value="${lessonData?.content_file_size ?? ''}"
                               data-lesson-size="${lessonUniqueId}">
                        <div class="upload-placeholder text-center p-3">
                            <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
                            <p class="mb-1"><strong>Cliquez pour sélectionner un fichier</strong></p>
                            <p class="text-muted small mb-0">Formats acceptés : MP4, WEBM, PDF, ZIP, DOCX...</p>
                            <p class="text-muted small">Taille max&nbsp;: 10&nbsp;Go</p>
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
                        <div class="lesson-existing-wrapper">
                            <div class="lesson-existing-preview" data-lesson-existing-preview>
                                <i class="fas fa-folder-open fa-2x text-primary"></i>
                            </div>
                            <div class="lesson-existing-content">
                                <div class="lesson-existing-details">
                                    <p class="mb-1 fw-semibold" data-lesson-existing-name>Fichier actuel</p>
                                    <a href="#" target="_blank" rel="noopener" class="small d-none" data-lesson-existing-link>Voir / télécharger</a>
                                </div>
                                <div class="lesson-existing-actions">
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100 w-lg-auto" onclick="clearExistingLessonFile('${lessonUniqueId}')">
                                        <i class="fas fa-trash me-1"></i>Retirer
                                    </button>
                                </div>
                            </div>
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
                    <textarea class="form-control lesson-content-text-editor" name="${lessonPrefix}[content_text]" rows="3" placeholder="Contenu texte de la leçon"></textarea>
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
        // Initialiser TinyMCE sur le nouveau textarea
        if (typeof window.initTinyMCEOnTextarea === 'function') {
            setTimeout(() => {
                window.initTinyMCEOnTextarea(contentText);
            }, 100);
        }
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

    cancelLessonUpload(uniqueId, true);

    if (!input.files || !input.files[0]) {
        clearLessonFile(uniqueId, true);
        return;
    }

    if (input.files.length > 1) {
        showLessonFileError(uniqueId, 'Veuillez sélectionner un seul fichier à la fois.');
        resetFileInput(input);
        return;
    }

    const file = input.files[0];

    if (!isLessonFileTypeAllowed(file)) {
        showLessonFileError(uniqueId, '❌ Format non supporté. Utilisez une vidéo (MP4/WEBM) ou un document (PDF, ZIP, DOCX, etc.).');
        resetFileInput(input);
        return;
    }

    if (file.size > LESSON_MAX_FILE_SIZE) {
        showLessonFileError(uniqueId, `❌ Fichier trop volumineux (${formatFileSize(file.size)}). Maximum : ${formatFileSize(LESSON_MAX_FILE_SIZE)}.`);
        resetFileInput(input);
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

    startLessonChunkUpload(uniqueId, file, input);
}

function getLessonHiddenInputs(uniqueId) {
    return {
        zone: document.getElementById(`lessonUploadZone-${uniqueId}`),
        path: document.querySelector(`[data-lesson-path="${uniqueId}"]`),
        name: document.querySelector(`[data-lesson-name="${uniqueId}"]`),
        size: document.querySelector(`[data-lesson-size="${uniqueId}"]`),
        existingPath: document.querySelector(`[data-lesson-existing-path="${uniqueId}"]`),
        removeFlag: document.querySelector(`[data-lesson-remove-flag="${uniqueId}"]`),
        existingContainer: document.getElementById(`lessonExistingFile-${uniqueId}`)
    };
}

function cancelLessonUpload(uniqueId, silent = false) {
    const controller = lessonUploadControllers.get(uniqueId);
    if (!controller || !controller.resumable) {
        return;
    }
    controller.silent = silent;
    try {
        controller.resumable.cancel();
    } catch (error) {
        // ignore
    }
}

function startLessonChunkUpload(uniqueId, file, input) {
    const hidden = getLessonHiddenInputs(uniqueId);
    const zone = hidden.zone;
    if (!zone) {
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) {
        clearLessonFile(uniqueId, true);
        showLessonFileError(uniqueId, 'Impossible de récupérer le jeton CSRF pour l’upload.');
        return;
    }

    const existingPath = hidden.path?.value || hidden.existingPath?.value || '';

    const taskId = createUploadTask(file.name, file.size, 'Téléversement du fichier de la leçon', {
        onCancel: () => clearLessonFile(uniqueId, true),
        cancelLabel: 'Annuler'
    });
    const resumable = new Resumable({
        target: CHUNK_UPLOAD_ENDPOINT,
        chunkSize: CHUNK_SIZE_BYTES,
        simultaneousUploads: 3,
        testChunks: false,
        throttleProgressCallbacks: 1,
        fileParameterName: 'file',
        fileType: ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'mkv', 'pdf', 'zip', 'rar', '7z', 'tar', 'gz', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'csv'],
        withCredentials: true,
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
        query: () => ({
            upload_type: 'lesson',
            original_name: file.name,
            section_index: zone.dataset.lessonSection ?? '',
            lesson_index: zone.dataset.lessonIndex ?? '',
            replace_path: existingPath || '',
        }),
    });


    const controller = { resumable, taskId, input, zone, silent: false };
    lessonUploadControllers.set(uniqueId, controller);
    zone.dataset.uploadTaskId = taskId;
    zone.style.borderColor = '#0d6efd';

    const handleError = (message, options = {}) => {
        const current = lessonUploadControllers.get(uniqueId);
        const silent = options.silent ?? current?.silent ?? false;
        if (!silent) {
            showLessonFileError(uniqueId, message || 'Erreur lors du téléversement du fichier de leçon.');
            errorUploadTask(taskId, message || 'Erreur lors du téléversement du fichier de leçon.');
            if (hidden.path) hidden.path.value = '';
            if (hidden.name) hidden.name.value = '';
            if (hidden.size) hidden.size.value = '';
            if (hidden.removeFlag) hidden.removeFlag.value = hidden.removeFlag.value || 0;
            restoreLessonExistingFile(uniqueId);
        } else if (window.UploadProgressModal && typeof window.UploadProgressModal.cancelTask === 'function') {
            window.UploadProgressModal.cancelTask(taskId);
        } else {
            errorUploadTask(taskId, message || 'Téléversement annulé.');
        }

        lessonUploadControllers.delete(uniqueId);
        delete zone.dataset.uploadTaskId;
        zone.style.borderColor = silent ? '#dee2e6' : '#dc3545';
    };

    resumable.on('fileProgress', function(resumableFile) {
        const percent = Math.max(0, Math.min(100, Math.round(resumableFile.progress() * 100)));
        updateUploadTask(taskId, percent, 'Téléversement en cours…');
    });

    resumable.on('fileSuccess', function(resumableFile, response) {
        let payload = response;
        if (typeof response === 'string') {
            try {
                payload = JSON.parse(response);
            } catch (error) {
                payload = null;
            }
        }

        if (!payload || !payload.path) {
            handleError('Réponse invalide du serveur.');
            return;
        }

        const previousPath = hidden.path ? hidden.path.value : '';
        if (hidden.path) hidden.path.value = payload.path;
        if (hidden.name) hidden.name.value = payload.filename || file.name;
        if (hidden.size) hidden.size.value = payload.size || file.size;
        if (hidden.removeFlag) hidden.removeFlag.value = 0;
        if (hidden.existingContainer) hidden.existingContainer.classList.add('d-none');
        if (previousPath && previousPath !== payload.path) {
            queueTemporaryDeletion(previousPath);
        }
        registerTemporaryPath(payload.path);

        completeUploadTask(taskId, 'Fichier importé avec succès');
        lessonUploadControllers.delete(uniqueId);
        delete zone.dataset.uploadTaskId;
        zone.style.borderColor = '#28a745';

        if (input) {
            resetFileInput(input);
        }
    });

    resumable.on('fileError', function(resumableFile, message) {
        const displayMessage = typeof message === 'string'
            ? message
            : (message?.message ?? 'Erreur lors du téléversement du fichier de leçon.');
        handleError(displayMessage);
    });

    resumable.on('error', function(message) {
        const displayMessage = typeof message === 'string'
            ? message
            : 'Erreur réseau lors du téléversement.';
        handleError(displayMessage);
    });

    resumable.on('cancel', function() {
        handleError('Téléversement annulé.', { silent: true });
    });

    resumable.on('chunkingComplete', function(resumableFile) {
        if (!resumable.isUploading()) {
            resumable.upload();
        }
    });

    resumable.addFile(file);
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

    const hidden = getLessonHiddenInputs(uniqueId);
    const previousPath = hidden.path ? hidden.path.value : '';

    cancelLessonUpload(uniqueId, true);

    const input = zone.querySelector('input[type="file"]');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const errorDiv = document.getElementById(`lessonFileError-${uniqueId}`);

    if (zone.dataset.previewUrl) {
        URL.revokeObjectURL(zone.dataset.previewUrl);
        delete zone.dataset.previewUrl;
    }

    resetFileInput(input);

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
    delete zone.dataset.uploadTaskId;

    if (hidden.path) {
        if (previousPath && isTemporaryPath(previousPath)) {
            queueTemporaryDeletion(previousPath);
        }
        hidden.path.value = '';
    }
    if (hidden.name) hidden.name.value = '';
    if (hidden.size) hidden.size.value = '';
    if (hidden.removeFlag && !restoreExisting) {
        hidden.removeFlag.value = hidden.removeFlag.value || 0;
    }
    lessonUploadControllers.delete(uniqueId);

    if (restoreExisting) {
        restoreLessonExistingFile(uniqueId);
    }
}

function handleLessonUrlInput(uniqueId, input) {
    if (!input) return;
    const value = input.value.trim();

    if (value !== '') {
        cancelLessonUpload(uniqueId, true);
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
        clearLessonFile(uniqueId);
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
    const zone = document.getElementById(`lessonUploadZone-${uniqueId}`);
    if (zone) {
        zone.style.borderColor = '#dc3545';
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
    const previewEl = container.querySelector('[data-lesson-existing-preview]');

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

    // Afficher une prévisualisation appropriée selon le type de fichier
    if (previewEl && url) {
        const extension = (name.split('.').pop() || '').toLowerCase();
        const isVideo = ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'mkv'].includes(extension);
        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension);
        const isPdf = extension === 'pdf';

        previewEl.innerHTML = ''; // Nettoyer le contenu précédent

        if (isVideo) {
            const video = document.createElement('video');
            video.src = url;
            video.controls = true;
            video.style.maxWidth = '100%';
            video.style.maxHeight = '100%';
            video.style.width = 'auto';
            video.style.height = 'auto';
            video.style.borderRadius = '8px';
            video.style.objectFit = 'contain';
            previewEl.appendChild(video);
        } else if (isImage) {
            const img = document.createElement('img');
            img.src = url;
            img.alt = name;
            img.style.maxWidth = '100%';
            img.style.maxHeight = '100%';
            img.style.width = 'auto';
            img.style.height = 'auto';
            img.style.borderRadius = '8px';
            img.style.objectFit = 'contain';
            previewEl.appendChild(img);
        } else if (isPdf) {
            const iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            iframe.style.minHeight = '150px';
            iframe.style.borderRadius = '8px';
            iframe.style.border = 'none';
            previewEl.appendChild(iframe);
        } else {
            // Icône par défaut pour les autres types de fichiers
            const icon = document.createElement('i');
            icon.className = 'fas fa-file fa-3x text-primary';
            previewEl.appendChild(icon);
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

    cancelLessonUpload(uniqueId, true);
    clearLessonFile(uniqueId);

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
    const saleStartField = document.getElementById('sale_start_at');
    const saleEndField = document.getElementById('sale_end_at');
    const freeCheckbox = document.getElementById('is_free');

    if (!priceField || !salePriceField || !saleStartField || !saleEndField) {
        return;
    }

    const isFree = freeCheckbox ? freeCheckbox.checked : false;

    if (isFree) {
        if (!isInitial) {
            cachedPriceValue = priceField.value;
            cachedSalePriceValue = salePriceField.value;
            cachedSaleStartValue = saleStartField.value;
            cachedSaleEndValue = saleEndField.value;
        }
        if (cachedPriceValue === null) {
            cachedPriceValue = priceField.value;
        }
        if (cachedSalePriceValue === null) {
            cachedSalePriceValue = salePriceField.value;
        }
        if (cachedSaleStartValue === null) {
            cachedSaleStartValue = saleStartField.value;
        }
        if (cachedSaleEndValue === null) {
            cachedSaleEndValue = saleEndField.value;
        }

        priceField.value = '0';
        priceField.disabled = true;
        salePriceField.value = '';
        salePriceField.disabled = true;
        saleStartField.value = '';
        saleStartField.disabled = true;
        saleEndField.value = '';
        saleEndField.disabled = true;
    } else {
        priceField.disabled = false;
        salePriceField.disabled = false;
        saleStartField.disabled = false;
        saleEndField.disabled = false;

        if (cachedPriceValue !== null) {
            priceField.value = cachedPriceValue || '';
        }

        if (cachedSalePriceValue !== null) {
            salePriceField.value = cachedSalePriceValue || '';
        }

        if (cachedSaleStartValue !== null) {
            saleStartField.value = cachedSaleStartValue || '';
        }

        if (cachedSaleEndValue !== null) {
            saleEndField.value = cachedSaleEndValue || '';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const freeCheckbox = document.getElementById('is_free');
    const priceField = document.getElementById('price');
    const salePriceField = document.getElementById('sale_price');
    const saleStartField = document.getElementById('sale_start_at');
    const saleEndField = document.getElementById('sale_end_at');

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

    if (saleStartField) {
        cachedSaleStartValue = saleStartField.value;
        saleStartField.addEventListener('input', function() {
            if (!freeCheckbox || !freeCheckbox.checked) {
                cachedSaleStartValue = this.value;
            }
        });
    }

    if (saleEndField) {
        cachedSaleEndValue = saleEndField.value;
        saleEndField.addEventListener('input', function() {
            if (!freeCheckbox || !freeCheckbox.checked) {
                cachedSaleEndValue = this.value;
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
            clearDownloadFile(true);
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

function resetDownloadHiddenFields() {
    const chunkPathInput = document.getElementById('download_file_chunk_path');
    const chunkNameInput = document.getElementById('download_file_chunk_name');
    const chunkSizeInput = document.getElementById('download_file_chunk_size');
    if (chunkPathInput) {
        const previousPath = chunkPathInput.value;
        if (previousPath && isTemporaryPath(previousPath)) {
            queueTemporaryDeletion(previousPath);
        }
        chunkPathInput.value = '';
    }
    if (chunkNameInput) chunkNameInput.value = '';
    if (chunkSizeInput) chunkSizeInput.value = '';
}

function handleDownloadFileUpload(input) {
    const zone = document.getElementById('downloadFileUploadZone');
    const errorDiv = document.getElementById('downloadFileError');
    const file = input.files[0];
    
    if (!zone) {
        return;
    }
    
    if (errorDiv) {
        errorDiv.textContent = '';
        errorDiv.classList.remove('d-block');
    }
    
    if (!file) {
        return;
    }
    
    const extension = '.' + file.name.split('.').pop().toLowerCase();
    if (!DOWNLOAD_ALLOWED_EXTENSIONS.includes(extension)) {
        showDownloadFileError('❌ Format invalide. Utilisez ZIP, PDF, DOC, DOCX, RAR, 7Z, TAR ou GZ.');
        resetFileInput(input);
        return;
    }
    
    if (file.size > MAX_DOWNLOAD_FILE_SIZE) {
        showDownloadFileError(`❌ Fichier trop volumineux (${formatFileSize(file.size)}). Maximum : ${formatFileSize(MAX_DOWNLOAD_FILE_SIZE)}.`);
        resetFileInput(input);
        return;
    }
    
    if (typeof Resumable === 'undefined') {
        showDownloadFileError('❌ Votre navigateur ne supporte pas l’upload fractionné. Veuillez le mettre à jour ou utiliser un autre navigateur.');
        resetFileInput(input);
        return;
    }
    
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const fileNameBadge = preview ? preview.querySelector('.file-name') : null;
    const fileSizeBadge = preview ? preview.querySelector('.file-size') : null;
    const urlInput = document.getElementById('download_file_url');
    const chunkPathInput = document.getElementById('download_file_chunk_path');
    const chunkNameInput = document.getElementById('download_file_chunk_name');
    const chunkSizeInput = document.getElementById('download_file_chunk_size');
    const progressModal = window.UploadProgressModal;
    const existingContainer = document.getElementById('downloadCurrentFile');
    const removeCheckbox = document.getElementById('remove_download_file');
    
    resetDownloadHiddenFields();
    
    if (downloadUploadResumable) {
        downloadUploadSuppressError = true;
        try {
            downloadUploadResumable.cancel();
        } catch (error) {
            // ignore
        }
        downloadUploadResumable = null;
    }
    if (downloadUploadTaskId && progressModal) {
        progressModal.cancelTask(downloadUploadTaskId);
        downloadUploadTaskId = null;
    }
    
    if (placeholder && preview) {
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
    }
    if (fileNameBadge) fileNameBadge.textContent = file.name;
    if (fileSizeBadge) fileSizeBadge.textContent = formatFileSize(file.size);
    zone.style.borderColor = '#0d6efd';
    
    if (existingContainer) {
        existingContainer.classList.add('d-none');
    }
    if (removeCheckbox) {
        removeCheckbox.checked = false;
    }
    
    if (urlInput) {
        urlInput.value = '';
    }
    
    downloadUploadTaskId = createUploadTask(file.name, file.size, 'Téléversement du fichier de téléchargement', {
        onCancel: () => clearDownloadFile(true),
        cancelLabel: 'Annuler'
    });
    
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const resumable = new Resumable({
        target: CHUNK_UPLOAD_ENDPOINT,
        chunkSize: CHUNK_SIZE_BYTES,
        simultaneousUploads: 3,
        testChunks: false,
        throttleProgressCallbacks: 1,
        fileParameterName: 'file',
        withCredentials: true,
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
        query: () => ({
            upload_type: 'download',
            original_name: file.name,
        }),
    });
    
    downloadUploadResumable = resumable;
    
    const finalizeFailure = (message, { suppressError = false } = {}) => {
        if (downloadUploadTaskId) {
            errorUploadTask(downloadUploadTaskId, message || 'Téléversement annulé.');
            downloadUploadTaskId = null;
        }
        downloadUploadResumable = null;
        downloadUploadSuppressError = false;
        resetDownloadHiddenFields();
        if (!suppressError && message) {
            showDownloadFileError(message);
        }
        if (placeholder && preview) {
            placeholder.classList.remove('d-none');
            preview.classList.add('d-none');
        }
        zone.style.borderColor = '#dee2e6';
        if (existingContainer && suppressError) {
            existingContainer.classList.remove('d-none');
        }
        resetFileInput(input);
    };
    
    resumable.on('fileProgress', function(resumableFile) {
        const percent = Math.max(0, Math.min(100, Math.round(resumableFile.progress() * 100)));
        updateUploadTask(downloadUploadTaskId, percent, 'Téléversement en cours…');
    });
    
    resumable.on('fileSuccess', function(resumableFile, response) {
        let payload = response;
        if (typeof response === 'string') {
            try {
                payload = JSON.parse(response);
            } catch (error) {
                payload = null;
            }
        }
    
        if (!payload || !payload.path) {
            finalizeFailure('La réponse du serveur est invalide. Veuillez réessayer.');
            return;
        }
    
        const previousPath = chunkPathInput ? chunkPathInput.value : '';
        if (chunkPathInput) chunkPathInput.value = payload.path;
        if (chunkNameInput) chunkNameInput.value = payload.filename || file.name;
        if (chunkSizeInput) chunkSizeInput.value = payload.size || file.size;
        if (previousPath && previousPath !== payload.path) {
            queueTemporaryDeletion(previousPath);
        }
        registerTemporaryPath(payload.path);
    
        if (downloadUploadTaskId) {
            completeUploadTask(downloadUploadTaskId, 'Fichier importé avec succès');
            downloadUploadTaskId = null;
        }
        downloadUploadResumable = null;
        downloadUploadSuppressError = false;
        zone.style.borderColor = '#28a745';
    
        if (fileNameBadge) fileNameBadge.textContent = payload.filename || file.name;
        if (fileSizeBadge) fileSizeBadge.textContent = formatFileSize(payload.size || file.size);
    
        if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.classList.remove('d-block');
        }
    
        if (progressModal && typeof progressModal.hideIfIdle === 'function') {
            progressModal.hideIfIdle();
        }
    
        resetFileInput(input);
    });
    
    resumable.on('fileError', function(resumableFile, message) {
        const errorMessage = typeof message === 'string'
            ? message
            : (message && message.message) || 'Erreur lors du téléversement du fichier.';
        finalizeFailure(errorMessage);
    });
    
    resumable.on('error', function(message) {
        const errorMessage = typeof message === 'string'
            ? message
            : 'Erreur réseau lors du téléversement.';
        finalizeFailure(errorMessage);
    });
    
    resumable.on('cancel', function() {
        if (downloadUploadSuppressError) {
            finalizeFailure(null, { suppressError: true });
            if (existingContainer) {
                existingContainer.classList.remove('d-none');
            }
        } else {
            finalizeFailure('Téléversement annulé.');
        }
    });
    
    resumable.on('chunkingComplete', function() {
        if (!resumable.isUploading()) {
            resumable.upload();
        }
    });
    
    resumable.addFile(file);
}

function clearDownloadFile(restoreExisting = false) {
    const input = document.getElementById('download_file_path');
    const zone = document.getElementById('downloadFileUploadZone');
    const errorDiv = document.getElementById('downloadFileError');
    const progressModal = window.UploadProgressModal;
    const existingContainer = document.getElementById('downloadCurrentFile');
    const removeCheckbox = document.getElementById('remove_download_file');
    
    if (downloadUploadResumable) {
        downloadUploadSuppressError = true;
        try {
            downloadUploadResumable.cancel();
        } catch (error) {
            // ignore
        }
    }
    downloadUploadResumable = null;
    
    if (downloadUploadTaskId && progressModal) {
        progressModal.cancelTask(downloadUploadTaskId);
        downloadUploadTaskId = null;
    }
    
    resetDownloadHiddenFields();
    
    resetFileInput(input);
    
    if (zone) {
        const placeholder = zone.querySelector('.upload-placeholder');
        const preview = zone.querySelector('.upload-preview');
        if (placeholder && preview) {
            placeholder.classList.remove('d-none');
            preview.classList.add('d-none');
        }
        zone.style.borderColor = '#dee2e6';
    }
    
    if (errorDiv) {
        errorDiv.textContent = '';
        errorDiv.classList.remove('d-block');
    }
    
    if (existingContainer) {
        if (restoreExisting) {
            existingContainer.classList.remove('d-none');
        } else {
            existingContainer.classList.add('d-none');
        }
    }
    
    if (removeCheckbox && restoreExisting) {
        removeCheckbox.checked = false;
    }

    downloadUploadSuppressError = false;
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

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('courseForm');
    if (form) {
        form.addEventListener('submit', function() {
            TempUploadManager.markSubmitting();
        });
    }

    document.querySelectorAll('[data-temp-upload-cancel]').forEach((cancelLink) => {
        cancelLink.addEventListener('click', function(event) {
            event.preventDefault();
            const href = this.getAttribute('href');
            TempUploadManager.flushAll({ keepalive: true });
            const navigate = () => window.location.href = href;
            if (navigator.sendBeacon) {
                setTimeout(navigate, 50);
            } else {
                setTimeout(navigate, 0);
            }
        });
    });
});

if (!window.__tempUploadUnloadHook) {
    window.__tempUploadUnloadHook = true;
    window.addEventListener('beforeunload', function() {
        if (TempUploadManager && !TempUploadManager.isSubmitting()) {
            TempUploadManager.flushAll({ keepalive: true });
        }
    });
}
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

/* Formulaire */
#courseForm {
    margin: 0;
    padding: 0;
}

/* Cards */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 1rem;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding: 0 !important;
}

.card.shadow-sm {
    padding: 0 !important;
}

.card:first-child {
    margin-top: 0;
}

.card:last-child {
    margin-bottom: 0;
}

.card:hover {
    transform: translateY(-2px);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border-bottom: none;
    margin: 0 !important;
    margin-top: 0 !important;
    padding: 0.75rem 1rem !important;
    border-top-left-radius: 12px !important;
    border-top-right-radius: 12px !important;
}

.card-header h5 {
    font-weight: 600;
}

.card-header h6 {
    color: #003366;
}

.card-body {
    padding: 1rem 1.25rem !important;
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
    padding: 1rem;
}

.lesson-existing-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    width: 100%;
}

.lesson-existing-preview {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 150px;
    max-width: 100%;
    overflow: hidden;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
}

.lesson-existing-preview video,
.lesson-existing-preview img,
.lesson-existing-preview iframe {
    max-width: 100%;
    max-height: 300px;
    width: auto;
    height: auto;
    border-radius: 8px;
    object-fit: contain;
}

.lesson-existing-content {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    width: 100%;
}

.lesson-existing-details {
    text-align: center;
    width: 100%;
}

.lesson-existing-details p {
    font-size: 1rem;
    margin-bottom: 0.5rem;
    word-break: break-word;
}

.lesson-existing-details a {
    display: inline-block;
    margin-top: 0.25rem;
}

.lesson-existing-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
}

@media (max-width: 575.98px) {
    .lesson-existing-details p {
        font-size: 0.875rem;
    }
}

.lesson-existing-icon i {
    opacity: 0.85;
}

.lesson-upload-zone .lesson-remove-btn {
    min-width: 140px;
}

.course-section-remove-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: none;
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    border-radius: 50%;
    transition: background 0.2s ease, transform 0.2s ease, color 0.2s ease;
}

.course-section-remove-icon:hover,
.course-section-remove-icon:focus {
    background: rgba(220, 53, 69, 0.2);
    color: #a71d2a;
    transform: scale(1.05);
}

.course-section-remove-icon i {
    margin: 0;
    font-size: 0.85rem;
}

.course-lesson-card__header {
    padding: 0.6rem 0.85rem;
    background: #f8fafc;
}

.course-lesson-card__header h6 {
    color: #0f172a;
    font-weight: 600;
}

.course-lesson-remove-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border: none;
    background: rgba(220, 53, 69, 0.12);
    color: #dc3545;
    border-radius: 50%;
    transition: background 0.2s ease, transform 0.2s ease, color 0.2s ease;
}

.course-lesson-remove-icon:hover,
.course-lesson-remove-icon:focus {
    background: rgba(220, 53, 69, 0.22);
    color: #a71d2a;
    transform: scale(1.05);
}

.course-lesson-remove-icon i {
    margin: 0;
    font-size: 0.82rem;
}

.form-actions-card .card-body {
    padding: 1rem 1.25rem;
}

.form-actions {
    width: 100%;
}

.form-actions__btn {
    flex: 1 1 auto;
    font-weight: 600;
}

.form-actions__btn--primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.form-actions__btn i {
    position: relative;
    top: -1px;
}

/* Responsive spacing adjustments */
@media (max-width: 991.98px) {
    .card {
        margin-bottom: 1rem;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-top: 0 !important;
        padding: 0 !important;
    }

    .card-header {
        padding: 0.7rem 0.9rem !important;
        margin: 0 !important;
        margin-top: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        border-top-left-radius: 12px !important;
        border-top-right-radius: 12px !important;
    }

    .card-body {
        padding: 0.85rem 1rem !important;
    }

    .course-content-card__body {
        padding: 0.85rem 1rem;
    }

    .course-section-card__body {
        padding: 0.75rem 0.95rem;
    }

    .course-lesson-card__body {
        padding: 0.7rem 0.85rem;
    }

    .course-section-remove-icon {
        width: 28px;
        height: 28px;
    }

    .course-lesson-card__header {
        padding: 0.55rem 0.75rem;
    }

    .course-lesson-remove-icon {
        width: 28px;
        height: 28px;
    }

    .course-lesson-remove-icon i {
        font-size: 0.78rem;
    }

    .card-header h5 {
        font-size: 1.05rem;
    }

    .card-header h6,
    .course-lesson-card__header h6 {
        font-size: 0.98rem;
    }

    .card-body .form-label {
        font-size: 0.95rem;
    }

    .form-control,
    .form-select,
    textarea.form-control {
        font-size: 0.95rem;
        padding: 0.6rem 0.85rem;
    }

    .form-control-lg {
        font-size: 1rem;
        padding: 0.65rem 0.9rem;
    }

    .btn {
        font-size: 0.95rem;
        padding: 0.55rem 1.1rem;
    }

    .form-actions-card .card-body {
        padding: 0.85rem 1rem;
    }

    .form-actions {
        justify-content: space-between;
    }

    .form-actions__btn {
        flex: 0 0 auto;
        min-width: 150px;
    }

    .form-actions__btn--primary {
        min-width: 170px;
    }

    /* Quill Editor - Adaptation tablette */
    .quill-editor-container .ql-toolbar {
        padding: 0.6rem;
        flex-wrap: wrap;
    }

    .quill-editor-container .ql-toolbar .ql-formats {
        margin-right: 0.6rem;
        margin-bottom: 0.3rem;
    }

    .quill-editor-container .ql-toolbar button {
        width: 30px;
        height: 30px;
    }

    .quill-editor-container .ql-toolbar button svg {
        width: 17px;
        height: 17px;
    }

    .quill-editor-container .ql-toolbar .ql-picker {
        height: 30px;
        font-size: 0.9rem;
    }
}

@media (max-width: 767.98px) {
    .card,
    .card.shadow-sm {
        margin-bottom: 1rem;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-top: 0 !important;
        padding: 0 !important;
    }

    .card-header {
        padding: 0.6rem 0.75rem !important;
        margin: 0 !important;
        margin-top: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-bottom: 0 !important;
        border-top-left-radius: 12px !important;
        border-top-right-radius: 12px !important;
        position: relative;
        top: 0;
    }

    .card-body {
        padding: 0.7rem 0.85rem !important;
    }

    .course-content-card__body {
        padding: 0.6rem 0.8rem;
    }

    .course-section-card__body {
        padding: 0.55rem 0.75rem;
    }

    .course-lesson-card__body {
        padding: 0.5rem 0.7rem;
    }

    .course-lesson-card__body .row {
        row-gap: 0.75rem;
    }

    .course-section-remove-icon {
        width: 26px;
        height: 26px;
    }

    .course-section-remove-icon i {
        font-size: 0.8rem;
    }

    .course-lesson-card__header {
        padding: 0.5rem 0.65rem;
    }

    .course-lesson-remove-icon {
        width: 24px;
        height: 24px;
    }

    .course-lesson-remove-icon i {
        font-size: 0.72rem;
    }

    .card-header h5 {
        font-size: 0.95rem;
    }

    .card-header h6,
    .course-lesson-card__header h6 {
        font-size: 0.88rem;
    }

    .card-body .form-label,
    .form-label {
        font-size: 0.88rem;
    }

    .card-body small,
    .card-body .text-muted,
    .upload-placeholder p,
    .upload-info .badge {
        font-size: 0.8rem;
    }

    .form-control,
    .form-select,
    textarea.form-control {
        font-size: 0.88rem;
        padding: 0.5rem 0.75rem;
    }

    .form-control-lg {
        font-size: 0.95rem;
        padding: 0.55rem 0.8rem;
    }

    .btn {
        font-size: 0.88rem;
        padding: 0.45rem 0.7rem;
    }

    .form-actions-card .card-body {
        padding: 0.7rem 0.85rem;
    }

    /* Quill Editor - Adaptation mobile/tablette */
    .quill-editor-container .ql-toolbar {
        padding: 0.5rem;
        flex-wrap: wrap;
    }

    .quill-editor-container .ql-toolbar .ql-formats {
        margin-right: 0.5rem;
        margin-bottom: 0.25rem;
    }

    .quill-editor-container .ql-toolbar button {
        width: 28px;
        height: 28px;
        padding: 0;
    }

    .quill-editor-container .ql-toolbar button svg {
        width: 16px;
        height: 16px;
    }

    .quill-editor-container .ql-toolbar .ql-picker {
        height: 28px;
        font-size: 0.85rem;
    }

    .quill-editor-container .ql-toolbar .ql-picker-label {
        padding: 0 0.5rem;
    }

    .quill-editor-container .ql-container {
        font-size: 0.9rem;
    }
}
</style>
@endpush

@push('styles')
<!-- Quill Editor CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
<!-- Quill Editor (alternative moderne et fiable à TinyMCE) -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
(function() {
    const quillInstances = new Map();

    function initQuillEditor(textarea) {
        if (!textarea || quillInstances.has(textarea)) {
            return;
        }

        // Créer un conteneur pour Quill
        const container = document.createElement('div');
        container.style.height = '300px';
        container.className = 'quill-editor-container';
        
        // Insérer le conteneur avant le textarea
        textarea.parentNode.insertBefore(container, textarea);
        
        // Masquer le textarea
        textarea.style.display = 'none';
        
        // Configuration Quill
        const quill = new Quill(container, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    ['link', 'image'],
                    ['clean']
                ]
            },
            placeholder: 'Saisissez le contenu de la leçon...'
        });

        // Charger le contenu existant
        if (textarea.value) {
            quill.root.innerHTML = textarea.value;
        }

        // Synchroniser le contenu avec le textarea pour la soumission du formulaire
        quill.on('text-change', function() {
            textarea.value = quill.root.innerHTML;
        });

        // Initialiser avec le contenu existant
        textarea.value = quill.root.innerHTML;

        // Stocker l'instance
        quillInstances.set(textarea, quill);
    }

    function initAllEditors() {
        const textareas = document.querySelectorAll('.lesson-content-text-editor');
        textareas.forEach(function(textarea) {
            initQuillEditor(textarea);
        });
    }

    // Fonction globale pour initialiser un éditeur sur un nouveau textarea
    window.initTinyMCEOnTextarea = function(textarea) {
        initQuillEditor(textarea);
    };

    // Initialiser les éditeurs existants
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllEditors);
    } else {
        initAllEditors();
    }

    // Observer pour initialiser Quill sur les nouveaux textareas ajoutés dynamiquement
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    const textareas = node.querySelectorAll ? node.querySelectorAll('.lesson-content-text-editor') : [];
                    textareas.forEach(function(textarea) {
                        initQuillEditor(textarea);
                    });
                    // Si le node lui-même est un textarea
                    if (node.classList && node.classList.contains('lesson-content-text-editor')) {
                        initQuillEditor(node);
                    }
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
})();
</script>
@endpush
@endsection

