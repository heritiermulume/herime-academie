@extends('instructors.admin.layout')

@section('admin-title', 'Modifier le cours')
@section('admin-subtitle', "Actualisez les informations et le contenu de la formation pour vos étudiants.")

@section('admin-actions')
    <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour à mes cours
    </a>
    <a href="{{ route('instructor.courses.show', $course->slug) }}" class="btn btn-outline-primary" target="_blank">
        <i class="fas fa-eye me-2"></i>Voir la page publique
    </a>
@endsection

@section('admin-content')
    @if ($errors->any())
        <div class="admin-card create-course__alert">
            <div class="create-course__alert-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h3>Veuillez corriger les éléments suivants :</h3>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('instructor.courses.update', $course) }}" method="POST" enctype="multipart/form-data" class="create-course__form">
        @csrf
        @method('PUT')

        <section class="admin-card">
            <header class="create-course__section-head">
                <div>
                    <h2>Informations principales</h2>
                    <p>Mettez à jour les éléments essentiels de votre formation.</p>
                </div>
                <span class="course-status {{ $course->is_published ? 'is-published' : 'is-draft' }}">
                    {{ $course->is_published ? 'Publié' : 'Brouillon' }}
                </span>
            </header>

            <div class="create-course__grid">
                <div class="create-course__field">
                    <label for="title">Titre du cours <span class="required">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $course->title) }}" required>
                    @error('title') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field">
                    <label for="short_description">Résumé</label>
                    <textarea id="short_description" name="short_description" rows="3">{{ old('short_description', $course->short_description) }}</textarea>
                    @error('short_description') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field create-course__field--full">
                    <label for="description">Description détaillée <span class="required">*</span></label>
                    <textarea id="description" name="description" rows="6" required>{{ old('description', $course->description) }}</textarea>
                    @error('description') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field">
                    <label for="category_id">Catégorie <span class="required">*</span></label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Sélectionnez une catégorie</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (int) old('category_id', $course->category_id) === $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field">
                    <label for="level">Niveau <span class="required">*</span></label>
                    <select id="level" name="level" required>
                        <option value="">Choisissez un niveau</option>
                        <option value="beginner" {{ old('level', $course->level) === 'beginner' ? 'selected' : '' }}>Débutant</option>
                        <option value="intermediate" {{ old('level', $course->level) === 'intermediate' ? 'selected' : '' }}>Intermédiaire</option>
                        <option value="advanced" {{ old('level', $course->level) === 'advanced' ? 'selected' : '' }}>Avancé</option>
                    </select>
                    @error('level') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field">
                    <label for="language">Langue <span class="required">*</span></label>
                    <select id="language" name="language" required>
                        <option value="">Choisissez une langue</option>
                        <option value="fr" {{ old('language', $course->language) === 'fr' ? 'selected' : '' }}>Français</option>
                        <option value="en" {{ old('language', $course->language) === 'en' ? 'selected' : '' }}>English</option>
                    </select>
                    @error('language') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>
            </div>
        </section>

        <section class="admin-card">
            <header class="create-course__section-head">
                <div>
                    <h2>Médias et présentation</h2>
                    <p>Actualisez l’image et la vidéo de présentation de votre formation.</p>
                </div>
            </header>

            <div class="create-course__media">
                <x-instructor-media-field
                    type="image"
                    name="thumbnail"
                    :current-url="$course->thumbnail_url"
                    label="Image de couverture"
                    helper="JPG, PNG ou WEBP – 5 Mo max."
                />

                <div class="create-course__upload" data-media="video_preview">
                    <label for="video_preview">Vidéo de prévisualisation</label>
                    <input type="file"
                           id="video_preview"
                           name="video_preview"
                           accept="video/mp4,video/webm,video/ogg"
                           class="create-course__upload-input"
                           data-role="media-input">
                    <input type="hidden"
                           name="video_preview_path"
                           value="{{ old('video_preview_path', $course->video_preview && !filter_var($course->video_preview, FILTER_VALIDATE_URL) ? $course->video_preview : '') }}"
                           data-role="media-path">
                    <input type="hidden"
                           name="video_preview_name"
                           value="{{ old('video_preview_name') }}"
                           data-role="media-name">
                    <input type="hidden"
                           name="video_preview_size"
                           value="{{ old('video_preview_size') }}"
                           data-role="media-size">
                    <div class="create-course__upload-box" data-role="media-dropzone" tabindex="0">
                        <div class="create-course__upload-placeholder" data-role="media-placeholder">
                            <i class="fas fa-video"></i>
                            <span>Ajouter une vidéo</span>
                            <small>MP4, WEBM ou OGG – 100 Mo max.</small>
                        </div>
                        <div class="create-course__upload-selected is-hidden" data-role="media-preview">
                            <div class="create-course__upload-thumb" data-role="media-thumb">
                                <i class="fas fa-file-video"></i>
                            </div>
                            <div class="create-course__upload-selected__body">
                                <span data-role="media-filename"></span>
                                <div class="create-course__upload-actions">
                                    <button type="button" class="create-course__upload-change is-hidden" data-role="media-change">Changer de fichier</button>
                                    <button type="button" class="create-course__upload-clear is-hidden" data-role="media-clear">Retirer le fichier</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="create-course__upload-progress is-hidden" data-role="media-progress">
                        <div class="create-course__upload-progress-track">
                            <div class="create-course__upload-progress-bar" data-role="media-progress-bar"></div>
                        </div>
                        <span class="create-course__upload-progress-label" data-role="media-progress-label">0%</span>
                    </div>
                    <span class="create-course__error is-hidden" data-role="media-error"></span>
                    @error('video_preview') <span class="create-course__error">{{ $message }}</span> @enderror
                    @error('video_preview_path') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>
            </div>
        </section>

        <section class="admin-card">
            <header class="create-course__section-head">
                <div>
                    <h2>Tarification</h2>
                    <p>Modifiez le tarif et les promotions éventuelles.</p>
                </div>
            </header>

            <div class="create-course__grid">
                <div class="create-course__field">
                    <label for="price">Prix (FCFA) <span class="required">*</span></label>
                    <input type="number" id="price" name="price" value="{{ old('price', $course->price) }}" min="0" step="0.01" required>
                    @error('price') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field">
                    <label for="sale_price">Prix promotionnel</label>
                    <input type="number" id="sale_price" name="sale_price" value="{{ old('sale_price', $course->sale_price) }}" min="0" step="0.01">
                    @error('sale_price') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field">
                    <label class="course-switch">
                        <input type="checkbox" id="is_free" name="is_free" value="1" {{ old('is_free', $course->is_free) ? 'checked' : '' }}>
                        <span></span>
                        Cours gratuit
                    </label>
                </div>
            </div>
        </section>

        <section class="admin-card">
            <header class="create-course__section-head">
                <div>
                    <h2>Contenu pédagogique</h2>
                    <p>Mettez à jour ce que vos apprenants doivent savoir ou obtiendront.</p>
                </div>
            </header>

            <div class="create-course__lists">
                <x-instructor-dynamic-list
                    id="edit-requirements"
                    name="requirements[]"
                    title="Prérequis"
                    helper="Ajoutez les prérequis nécessaires avant de démarrer le cours."
                    :items="old('requirements', $course->requirements ?? [''])"
                    placeholder="Ex : Notions de base en informatique"
                />

                <x-instructor-dynamic-list
                    id="edit-learnings"
                    name="what_you_will_learn[]"
                    title="Ce que les apprenants vont maîtriser"
                    helper="Mettez en avant les compétences clés qui seront acquises."
                    :items="old('what_you_will_learn', $course->what_you_will_learn ?? [''])"
                    placeholder="Ex : Construire une API Laravel"
                />

                <x-instructor-dynamic-list
                    id="edit-tags"
                    name="tags[]"
                    title="Tags"
                    helper="Ajoutez des mots-clés pour optimiser la recherche."
                    :items="old('tags', $course->tags ?? [''])"
                    placeholder="Ex : backend"
                    variant="chips"
                />
            </div>
        </section>

        <section class="admin-card course-sections">
            <header class="create-course__section-head">
                <div>
                    <h2>Structure du cours</h2>
                    <p>Réorganisez les sections et les leçons existantes depuis le panneau “Leçons”.</p>
                </div>
                <a href="{{ route('instructor.courses.lessons', $course) }}" class="btn btn-outline-primary">
                    <i class="fas fa-layer-group me-2"></i>Gérer les leçons
                </a>
            </header>

            @if($course->sections->isEmpty())
                <div class="course-lessons-empty">
                    <i class="fas fa-layer-group"></i>
                    <h3>Aucune section définie</h3>
                    <p>Ajoutez vos sections et leçons dans l’onglet “Leçons”.</p>
                </div>
            @else
                <div class="course-lessons-summary">
                    @foreach($course->sections as $section)
                        <article class="course-lessons-summary__section">
                            <header>
                                <h3>{{ $section->title }}</h3>
                                <span>{{ $section->lessons->count() }} leçons</span>
                            </header>
                            <p>{{ $section->description ?: '—' }}</p>
                            <a href="{{ route('instructor.courses.lessons', $course) }}#section-{{ $section->id }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-pen"></i>Modifier les leçons
                            </a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="admin-card create-course__submit">
            <div class="create-course__submit-actions">
                <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                </button>
            </div>
        </section>
    </form>
@endsection

@include('courses.partials.chunk-upload-script', ['existingSections' => [], 'enableCourseBuilder' => false])

@push('styles')
<link rel="stylesheet" href="{{ asset('css/instructor-course.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/instructor-course-editor.js') }}"></script>
@endpush
