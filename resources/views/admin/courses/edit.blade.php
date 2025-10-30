@extends('layouts.app')

@section('title', 'Modifier le cours - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header text-white" style="background-color: #003366;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-sm" title="Tableau de bord">
                                <i class="fas fa-tachometer-alt"></i>
                            </a>
                            <a href="{{ route('admin.courses') }}" class="btn btn-outline-light btn-sm" title="Liste des cours">
                                <i class="fas fa-th-list"></i>
                            </a>
                            <div>
                                <h4 class="mb-1">
                                    <i class="fas fa-edit me-2"></i>Modifier le cours
                                </h4>
                                <p class="mb-0 text-description small">{{ $course->title }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Affichage des erreurs -->
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
                                @if($course->thumbnail)
                                    <div class="current-thumbnail mb-3 text-center">
                                        <p class="fw-bold mb-2 text-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Image actuelle :
                                        </p>
                                        <img src="{{ $course->thumbnail }}" alt="Image actuelle" class="img-thumbnail rounded" style="max-width: 300px; max-height: 200px; border: 4px solid #28a745;">
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
                                
                                <!-- Vidéo actuelle -->
                                @if($course->video_preview)
                                    <div class="current-video mb-3 text-center">
                                        <p class="fw-bold mb-2 text-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Vidéo actuelle :
                                        </p>
                                        <video controls class="w-100 rounded" style="max-height: 200px; border: 4px solid #28a745;">
                                            <source src="{{ $course->video_preview }}" type="video/mp4">
                                        </video>
                                    </div>
                                @endif
                                
                                <!-- Option 1: Lien vidéo -->
                                <div class="mb-3">
                                    <label class="form-label small">Option 1: Lien vidéo</label>
                                    <input type="url" class="form-control @error('video_preview') is-invalid @enderror" 
                                           id="video_preview" name="video_preview" value="{{ old('video_preview', $course->video_preview) }}" 
                                           placeholder="https://...">
                                    @error('video_preview')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Option 2: Upload fichier -->
                                <div>
                                    <label class="form-label small">Option 2: Téléverser un fichier</label>
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
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note :</strong> Pour modifier les sections et leçons, veuillez utiliser la page de détail du cours.
                            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-sm btn-outline-primary ms-2">
                                <i class="fas fa-external-link-alt me-1"></i>Gérer le contenu
                            </a>
                        </div>
                        
                        @if($course->sections->count() > 0)
                            <div class="accordion" id="courseAccordion">
                                @foreach($course->sections as $sectionIndex => $section)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $sectionIndex }}">
                                            <button class="accordion-button {{ $sectionIndex > 0 ? 'collapsed' : '' }}" 
                                                    type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#collapse{{ $sectionIndex }}" 
                                                    aria-expanded="{{ $sectionIndex === 0 ? 'true' : 'false' }}" 
                                                    aria-controls="collapse{{ $sectionIndex }}">
                                                <div class="d-flex justify-content-between w-100 me-3">
                                                    <span>{{ $section->title }}</span>
                                                    <span class="badge bg-primary">{{ $section->lessons->count() }} leçons</span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $sectionIndex }}" 
                                             class="accordion-collapse collapse {{ $sectionIndex === 0 ? 'show' : '' }}" 
                                             aria-labelledby="heading{{ $sectionIndex }}" 
                                             data-bs-parent="#courseAccordion">
                                            <div class="accordion-body p-0">
                                                @if($section->description)
                                                    <div class="p-3 border-bottom">
                                                        <p class="text-muted mb-0">{{ $section->description }}</p>
                                                    </div>
                                                @endif

                                                @if($section->lessons->count() > 0)
                                                    <div class="list-group list-group-flush">
                                                        @foreach($section->lessons as $lesson)
                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fas fa-{{ $lesson->type === 'video' ? 'play-circle' : ($lesson->type === 'quiz' ? 'question-circle' : 'file-text') }} me-3 text-primary"></i>
                                                                    <div>
                                                                        <h6 class="mb-1">{{ $lesson->title }}</h6>
                                                                        @if($lesson->description)
                                                                            <small class="text-muted">{{ $lesson->description }}</small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex align-items-center">
                                                                    @if($lesson->is_preview)
                                                                        <span class="badge bg-success me-2">Aperçu</span>
                                                                    @endif
                                                                    @if($lesson->duration > 0)
                                                                        <small class="text-muted me-2">{{ $lesson->duration }} min</small>
                                                                    @endif
                                                                    <span class="badge bg-{{ $lesson->type === 'video' ? 'primary' : ($lesson->type === 'quiz' ? 'warning' : 'info') }}">
                                                                        {{ ucfirst($lesson->type) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="text-muted p-3">Aucune leçon dans cette section.</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Aucune section définie pour ce cours.
                            </div>
                        @endif
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
                                <button type="button" class="btn btn-outline-primary" onclick="saveDraft()">
                                    <i class="fas fa-save me-1"></i>Enregistrer comme brouillon
                                </button>
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
</div>

@push('scripts')
<script>
// Constantes de validation
const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5MB
const MAX_VIDEO_SIZE = 100 * 1024 * 1024; // 100MB
const VALID_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
const VALID_VIDEO_TYPES = ['video/mp4', 'video/webm', 'video/ogg'];

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
function saveDraft() {
    document.getElementById('is_published').checked = false;
    document.getElementById('courseForm').submit();
}

// Gestion du cours gratuit
document.getElementById('is_free').addEventListener('change', function() {
    const priceField = document.getElementById('price');
    const salePriceField = document.getElementById('sale_price');
    
    if (this.checked) {
        priceField.value = '0';
        priceField.disabled = true;
        salePriceField.value = '0';
        salePriceField.disabled = true;
    } else {
        priceField.disabled = false;
        salePriceField.disabled = false;
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
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%) !important;
}

.bg-gradient-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
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
</style>
@endpush
@endsection

