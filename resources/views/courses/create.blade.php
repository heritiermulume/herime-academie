@extends('instructors.admin.layout')

@php
    $oldSections = old('sections', []);
@endphp

@section('admin-title', 'Créer un nouveau cours')
@section('admin-subtitle', 'Définissez les informations essentielles de votre formation avant de la publier.')

@section('admin-actions')
    <a href="{{ route('instructor.courses.index') }}" class="admin-btn outline" data-temp-upload-cancel="true">
        <i class="fas fa-arrow-left me-2"></i>Retour à mes cours
    </a>
@endsection

@include('partials.upload-progress-modal')

@section('admin-content')
    @if ($errors->any())
        <div class="admin-panel" style="background: rgba(254, 226, 226, 0.65); border: 1px solid rgba(248, 113, 113, 0.3); margin-bottom: 1.5rem;">
            <div class="admin-panel__body" style="display: flex; gap: 1rem; align-items: flex-start;">
                <div style="width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; background: rgba(248, 113, 113, 0.15); font-size: 1.3rem; color: #991b1b; flex-shrink: 0;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h3 style="margin: 0 0 0.5rem; color: #991b1b; font-size: 1.1rem;">Veuillez corriger les éléments suivants :</h3>
                    <ul style="margin: 0; padding-left: 1.5rem; color: #991b1b;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('instructor.courses.store') }}" method="POST" enctype="multipart/form-data" class="create-course__form" id="courseForm">
        @csrf

        <section class="admin-panel">
            <div class="admin-panel__header">
                <h3><i class="fas fa-info-circle me-2"></i>Informations principales</h3>
            </div>
            <div class="admin-panel__body">
                <p style="margin: 0 0 1.5rem; color: var(--instructor-muted); font-size: 0.95rem;">Posez les bases de votre formation pour guider les futurs étudiants.</p>
                <div class="create-course__grid">
                <div class="create-course__field">
                    <label for="title">Titre du cours <span class="required">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="Ex : Maîtriser Laravel en 30 jours" required>
                    @error('title') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field">
                    <label for="short_description">Résumé</label>
                    <textarea id="short_description" name="short_description" rows="3" placeholder="Une introduction concise affichée dans la liste des cours">{{ old('short_description') }}</textarea>
                    @error('short_description') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field create-course__field--full">
                    <label for="description">Description détaillée <span class="required">*</span></label>
                    <textarea id="description" name="description" rows="6" placeholder="Présentez les objectifs, le contenu et la valeur ajoutée du cours" required>{{ old('description') }}</textarea>
                    @error('description') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field">
                    <label for="category_id">Catégorie <span class="required">*</span></label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Sélectionnez une catégorie</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (int) old('category_id') === $category->id ? 'selected' : '' }}>
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
                        <option value="beginner" {{ old('level') === 'beginner' ? 'selected' : '' }}>Débutant</option>
                        <option value="intermediate" {{ old('level') === 'intermediate' ? 'selected' : '' }}>Intermédiaire</option>
                        <option value="advanced" {{ old('level') === 'advanced' ? 'selected' : '' }}>Avancé</option>
                    </select>
                    @error('level') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field">
                    <label for="language">Langue <span class="required">*</span></label>
                    <select id="language" name="language" required>
                        <option value="">Choisissez une langue</option>
                        <option value="fr" {{ old('language') === 'fr' ? 'selected' : '' }}>Français</option>
                        <option value="en" {{ old('language') === 'en' ? 'selected' : '' }}>English</option>
                    </select>
                    @error('language') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>
            </div>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel__header">
                <h3><i class="fas fa-photo-video me-2"></i>Médias et présentation</h3>
            </div>
            <div class="admin-panel__body">
                <p style="margin: 0 0 1.5rem; color: var(--instructor-muted); font-size: 0.95rem;">Mettez en valeur votre cours grâce à une image et une vidéo attrayantes.</p>
                <div class="create-course__media">
                <div class="create-course__upload" data-media="thumbnail">
                    <label for="thumbnail">Image de couverture</label>
                    <input type="file"
                           id="thumbnail"
                           name="thumbnail"
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="create-course__upload-input"
                           data-role="media-input">
                    <div class="create-course__upload-box" data-role="media-dropzone" tabindex="0">
                        <div class="create-course__upload-placeholder" data-role="media-placeholder">
                            <i class="fas fa-image"></i>
                            <span>Sélectionner une image</span>
                            <small>JPG, PNG ou WEBP – 5 Mo max.</small>
                        </div>
                        <div class="create-course__upload-selected is-hidden" data-role="media-preview">
                            <div class="create-course__upload-thumb" data-role="media-thumb">
                                <i class="fas fa-file-image"></i>
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
                    <span class="create-course__error is-hidden" data-role="media-error"></span>
                    @error('thumbnail') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

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
                           value="{{ old('video_preview_path') }}"
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
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel__header">
                <h3><i class="fas fa-dollar-sign me-2"></i>Tarification</h3>
            </div>
            <div class="admin-panel__body">
                <p style="margin: 0 0 1.5rem; color: var(--instructor-muted); font-size: 0.95rem;">Fixez un tarif cohérent avec la valeur du contenu proposé.</p>
                <div class="create-course__grid">
                <div class="create-course__field">
                    <label for="price">Prix (FCFA) <span class="required">*</span></label>
                    <input type="number" id="price" name="price" value="{{ old('price') }}" min="0" step="0.01" placeholder="Ex : 45000" required>
                    @error('price') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field">
                    <label for="sale_price">Prix promotionnel</label>
                    <input type="number" id="sale_price" name="sale_price" value="{{ old('sale_price') }}" min="0" step="0.01" placeholder="Laissez vide si aucun">
                    @error('sale_price') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field">
                    <label for="sale_start_at">Début de promotion</label>
                    <input type="datetime-local" id="sale_start_at" name="sale_start_at" value="{{ old('sale_start_at') }}">
                    <small class="create-course__hint">Laissez vide pour démarrer immédiatement.</small>
                    @error('sale_start_at') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__field">
                    <label for="sale_end_at">Fin de promotion</label>
                    <input type="datetime-local" id="sale_end_at" name="sale_end_at" value="{{ old('sale_end_at') }}">
                    <small class="create-course__hint">La promotion s'arrêtera automatiquement à cette date.</small>
                    @error('sale_end_at') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>
            </div>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel__header">
                <h3><i class="fas fa-target me-2"></i>Contenu pédagogique</h3>
            </div>
            <div class="admin-panel__body">
                <p style="margin: 0 0 1.5rem; color: var(--instructor-muted); font-size: 0.95rem;">Clarifiez ce que les apprenants doivent savoir et ce qu'ils obtiendront.</p>
                <div class="create-course__lists">
                <div class="create-course__list">
                    <div class="create-course__list-head">
                        <h3>Prérequis</h3>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="CreateCourseForm.addItem('requirements')">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </div>
                    <div id="requirements-list" class="create-course__list-items" data-type="requirements">
                        @php $requirements = old('requirements', ['']); @endphp
                        @foreach($requirements as $index => $requirement)
                            <div class="create-course__list-item">
                                <input type="text" name="requirements[]" value="{{ $requirement }}" placeholder="Ex : Connaissances de base en informatique">
                                <button type="button" class="btn btn-link text-danger" onclick="CreateCourseForm.removeItem(this)" aria-label="Supprimer">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    @error('requirements') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__list">
                    <div class="create-course__list-head">
                        <h3>Ce que les apprenants vont maîtriser</h3>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="CreateCourseForm.addItem('learnings')">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </div>
                    <div id="learnings-list" class="create-course__list-items" data-type="learnings">
                        @php $learnings = old('what_you_will_learn', ['']); @endphp
                        @foreach($learnings as $index => $learning)
                            <div class="create-course__list-item">
                                <input type="text" name="what_you_will_learn[]" value="{{ $learning }}" placeholder="Ex : Déployer une API Laravel">
                                <button type="button" class="btn btn-link text-danger" onclick="CreateCourseForm.removeItem(this)" aria-label="Supprimer">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    @error('what_you_will_learn') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>

                <div class="create-course__list create-course__list--wide">
                    <div class="create-course__list-head">
                        <h3>Tags</h3>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="CreateCourseForm.addItem('tags')">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </div>
                    <div id="tags-list" class="create-course__list-items create-course__list-items--chips" data-type="tags">
                        @php $tags = old('tags', ['']); @endphp
                        @foreach($tags as $tag)
                            <div class="create-course__list-item create-course__list-item--chip">
                                <input type="text" name="tags[]" value="{{ $tag }}" placeholder="Ex : backend">
                                <button type="button" class="btn btn-link text-danger" onclick="CreateCourseForm.removeItem(this)" aria-label="Supprimer">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    @error('tags') <span class="create-course__error">{{ $message }}</span> @enderror
                </div>
            </div>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel__header">
                <h3><i class="fas fa-list me-2"></i>Structure du cours</h3>
                <button type="button" class="admin-btn primary sm" id="add-section-btn">
                    <i class="fas fa-plus me-2"></i>Ajouter une section
                </button>
            </div>
            <div class="admin-panel__body">
                <p style="margin: 0 0 1.5rem; color: var(--instructor-muted); font-size: 0.95rem;">Organisez les sections et leurs leçons. Ajoutez des fichiers, du texte ou des liens selon le format de chaque leçon.</p>
                <div class="course-structure">
                <div class="course-structure__empty" id="course-structure-empty">
                    <i class="fas fa-layer-group"></i>
                    <p>Ajoutez votre première section pour commencer à structurer le cours.</p>
                </div>
                <div class="course-structure__sections" id="course-structure-sections"></div>
            </div>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel__body">
                <div class="create-course__submit-actions">
                    <a href="{{ route('instructor.courses.index') }}" class="admin-btn outline">
                        Annuler
                    </a>
                    <button type="submit" class="admin-btn primary">
                        <i class="fas fa-check me-2"></i>Enregistrer le cours
                    </button>
                </div>
            </div>
        </section>
    </form>
@endsection

@push('styles')
<style>
    .create-course__form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .create-course__alert {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        background: rgba(254, 226, 226, 0.65);
        border: 1px solid rgba(248, 113, 113, 0.3);
        color: #991b1b;
    }

    .create-course__alert-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: rgba(248, 113, 113, 0.15);
        font-size: 1.3rem;
    }


    .create-course__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }

    .create-course__field {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .create-course__field label {
        font-size: 0.95rem;
        font-weight: 600;
        color: #0f172a;
    }

    .create-course__field input,
    .create-course__field select,
    .create-course__field textarea {
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        background: #ffffff;
        width: 100%;
    }

    .create-course__field input:focus,
    .create-course__field select:focus,
    .create-course__field textarea:focus {
        outline: none;
        border-color: rgba(14, 165, 233, 0.6);
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
    }

    .create-course__field--full {
        grid-column: 1 / -1;
    }

    .create-course__media {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.5rem;
    }

    .create-course__upload {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .create-course__upload-input {
        display: none;
    }

    .create-course__upload-box {
        position: relative;
        border: 2px dashed rgba(148, 163, 184, 0.45);
        border-radius: 18px;
        padding: 1.8rem;
        background: rgba(241, 245, 249, 0.65);
        text-align: center;
        transition: border-color 0.2s ease, background 0.2s ease;
        cursor: pointer;
        overflow: hidden;
    }

    .create-course__upload-box:hover,
    .create-course__upload-box.is-active {
        border-color: rgba(14, 165, 233, 0.75);
        background: rgba(224, 242, 254, 0.65);
    }

    .create-course__upload-placeholder {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        align-items: center;
        justify-content: center;
        color: #0f172a;
    }

    .create-course__upload-placeholder i {
        font-size: 1.8rem;
        color: #0ea5e9;
    }

    .create-course__upload-placeholder small {
        color: #64748b;
    }

    .create-course__upload-box[data-role="media-dropzone"].is-active {
        border-color: rgba(14, 165, 233, 0.8);
        background: rgba(224, 242, 254, 0.55);
    }

    .create-course__upload-box[data-role="media-dropzone"].has-file {
        border-color: rgba(14, 165, 233, 0.55);
        background: rgba(224, 242, 254, 0.35);
    }

    .create-course__upload-box:focus {
        outline: none;
        border-color: rgba(14, 165, 233, 0.85);
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
    }

    .create-course__upload-selected {
        display: flex;
        align-items: center;
        gap: 1rem;
        text-align: left;
        color: #0f172a;
    }

    .create-course__upload-thumb {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        overflow: hidden;
        background: rgba(14, 165, 233, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0284c7;
        flex-shrink: 0;
    }

    .create-course__upload-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .create-course__upload-thumb i,
    .course-structure__dropzone-thumb i {
        font-size: 1.6rem;
        color: #0ea5e9;
    }

    .create-course__upload-selected__body {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .create-course__upload-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .create-course__upload-change,
    .create-course__upload-clear {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .create-course__upload-change {
        color: #0369a1;
    }

    .create-course__upload-clear {
        color: #b91c1c;
    }

    .create-course__upload-change:hover,
    .create-course__upload-clear:hover {
        text-decoration: underline;
    }

    .create-course__lists {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.5rem;
    }

    .create-course__list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .create-course__list--wide {
        grid-column: 1 / -1;
    }

    .create-course__list-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .create-course__list-head h3 {
        margin: 0;
        font-size: 1rem;
        color: #0f172a;
        font-weight: 700;
    }

    .create-course__list-items {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .create-course__list-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: rgba(248, 250, 252, 0.9);
        border: 1px solid rgba(148, 163, 184, 0.3);
        border-radius: 14px;
        padding: 0.75rem;
    }

    .create-course__list-item input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 0;
        font-size: 0.95rem;
    }

    .create-course__list-item input:focus {
        outline: none;
    }

    .create-course__list-item--chip {
        flex-wrap: nowrap;
    }

    .create-course__list-items--chips .create-course__list-item {
        background: rgba(14, 165, 233, 0.08);
        border: 1px solid rgba(14, 165, 233, 0.25);
    }

    .create-course__submit {
        display: flex;
        justify-content: flex-end;
    }

    .create-course__submit-actions {
        display: flex;
        gap: 1rem;
        width: 100%;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .create-course__error {
        font-size: 0.82rem;
        color: #b91c1c;
        font-weight: 500;
    }

    .course-structure {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .course-structure__empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 2.25rem;
        border-radius: 18px;
        border: 1px dashed rgba(148, 163, 184, 0.45);
        color: #94a3b8;
        background: rgba(241, 245, 249, 0.6);
        text-align: center;
    }

    .course-structure__empty i {
        font-size: 1.9rem;
        color: #0ea5e9;
    }

    .course-structure__sections {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .course-structure__section {
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.95);
        padding: 1.5rem;
        box-shadow: 0 18px 35px -30px rgba(15, 23, 42, 0.2);
    }

    .course-structure__section-header {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .course-structure__section-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
    }

    .course-structure__section-index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background: rgba(14, 165, 233, 0.15);
        color: #0369a1;
        font-weight: 700;
    }

    .course-structure__section-input {
        flex: 1;
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 12px;
        padding: 0.75rem 0.9rem;
        font-weight: 600;
    }

    .course-structure__section-body {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .course-structure__field {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .course-structure__field > span:first-child {
        font-size: 0.85rem;
        font-weight: 600;
        color: #0f172a;
    }

    .course-structure__field textarea,
    .course-structure__field input {
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 12px;
        padding: 0.75rem;
        font-size: 0.95rem;
        background: rgba(248, 250, 252, 0.9);
    }

    .course-structure__lessons {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .course-structure__lesson {
        border: 1px solid rgba(148, 163, 184, 0.3);
        border-radius: 14px;
        background: rgba(248, 250, 252, 0.95);
        padding: 1.1rem;
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
    }

    .course-structure__lesson-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
    }

    .course-structure__lesson-index {
        font-weight: 700;
        color: #0f172a;
    }

    .course-structure__lesson-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem 1.25rem;
    }

    .course-structure__lesson-grid .course-structure__field {
        margin: 0;
    }

    .course-structure__lesson-grid .course-structure__field:nth-child(1) {
        grid-column: 1 / -1;
    }

    .course-structure__lesson-meta {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        grid-column: 1 / -1;
    }

    .course-structure__checkbox {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: #0f172a;
        font-weight: 600;
    }

    .course-structure__checkbox input {
        width: 18px;
        height: 18px;
    }

    .course-structure__file {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        grid-column: 1 / -1;
    }

    .course-structure__file small {
        color: #64748b;
        font-size: 0.8rem;
    }

    .course-structure__dropzone-input {
        display: none;
    }

    .course-structure__dropzone {
        position: relative;
        display: flex;
        align-items: stretch;
        gap: 0.85rem;
        border: 2px dashed rgba(14, 165, 233, 0.45);
        border-radius: 14px;
        padding: 1.25rem;
        background: rgba(224, 242, 254, 0.32);
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease;
    }

    .course-structure__dropzone.is-active {
        border-color: rgba(14, 165, 233, 0.85);
        background: rgba(224, 242, 254, 0.55);
    }

    .course-structure__dropzone.has-file {
        border-style: solid;
        background: rgba(14, 165, 233, 0.12);
    }

    .course-structure__dropzone.has-error {
        border-color: rgba(248, 113, 113, 0.75);
        background: rgba(254, 226, 226, 0.55);
    }

    .create-course__upload-box.is-uploading,
    .course-structure__dropzone.is-uploading {
        opacity: 0.7;
        pointer-events: none;
    }

    .create-course__upload-progress,
    .course-structure__upload-progress {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-top: 0.75rem;
    }

    .create-course__upload-progress-track,
    .course-structure__upload-progress-track {
        flex: 1;
        height: 6px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .create-course__upload-progress-bar,
    .course-structure__upload-progress-bar {
        width: 0%;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(14, 165, 233, 0.9), rgba(59, 130, 246, 0.9));
        transition: width 0.2s ease;
    }

    .create-course__upload-progress-label,
    .course-structure__upload-progress-label {
        min-width: 3.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #0f172a;
        text-align: right;
    }

    .course-structure__dropzone:focus {
        outline: none;
        border-color: rgba(14, 165, 233, 0.85);
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
    }

    .course-structure__dropzone-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(14, 165, 233, 0.18);
        color: #0ea5e9;
        font-size: 1.4rem;
    }

    .course-structure__dropzone.has-file .course-structure__dropzone-icon {
        display: none;
    }

    .course-structure__dropzone-body {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        flex: 1;
    }

    .course-structure__dropzone-thumb {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        overflow: hidden;
        background: rgba(14, 165, 233, 0.12);
        color: #0284c7;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .course-structure__dropzone-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .course-structure__dropzone-text {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        color: #0f172a;
    }

    .course-structure__dropzone-title {
        font-weight: 700;
    }

    .course-structure__dropzone-actions {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .course-structure__dropzone-change,
    .course-structure__dropzone-clear {
        border: none;
        background: none;
        padding: 0;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        align-self: flex-start;
    }

    .course-structure__dropzone-change {
        color: #0369a1;
    }

    .course-structure__dropzone-clear {
        color: #b91c1c;
    }

    .course-structure__dropzone-change:hover,
    .course-structure__dropzone-clear:hover {
        text-decoration: underline;
    }

    .required {
        color: #ef4444;
    }

    .is-hidden {
        display: none !important;
    }

    @media (max-width: 1024px) {
        .create-course__form {
            gap: 1.25rem;
        }
        .create-course__grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .create-course__media {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        .create-course__lists {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        .create-course__submit-actions {
            justify-content: center;
            flex-direction: column;
        }
        .create-course__submit-actions .admin-btn {
            width: 100%;
        }
        .course-structure__lesson-grid {
            grid-template-columns: 1fr;
        }
        .course-structure__lesson-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .admin-panel__header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        .admin-panel__header .admin-btn {
            width: 100%;
        }
    }

    @media (max-width: 640px) {
        .create-course__form {
            gap: 1rem;
        }
        .create-course__grid {
            gap: 0.875rem;
        }
        .create-course__field {
            gap: 0.35rem;
        }
        .create-course__field input,
        .create-course__field select,
        .create-course__field textarea {
            padding: 0.65rem 0.875rem;
            font-size: 0.9rem;
        }
        .create-course__field label {
            font-size: 0.9rem;
        }
        .create-course__list-item {
            flex-direction: column;
            align-items: stretch;
            padding: 0.625rem;
        }
        .create-course__list-item button {
            align-self: flex-end;
        }
        .create-course__list-item input {
            font-size: 0.9rem;
        }
        .course-structure__lesson-meta {
            grid-template-columns: 1fr;
        }
        .course-structure__section {
            padding: 1rem;
        }
        .admin-panel__header h3 {
            font-size: 1.1rem;
        }
        .admin-panel__body {
            padding: 1rem;
        }
        .create-course__upload-box {
            padding: 1.25rem;
        }
        .create-course__upload-placeholder i {
            font-size: 1.5rem;
        }
    }
</style>
@endpush

@include('courses.partials.chunk-upload-script', ['existingSections' => $oldSections, 'enableCourseBuilder' => true])

