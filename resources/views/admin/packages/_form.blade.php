@php
    $p = $package ?? null;
    $highlights = old('marketing_highlights');
    if ($highlights === null) {
        $highlights = $p ? ($p->marketing_highlights ?? []) : [];
    }
    $highlights = array_values(array_filter($highlights, fn ($x) => true));
    while (count($highlights) < 5) {
        $highlights[] = '';
    }
    $benefits = old('marketing_benefits');
    if ($benefits === null) {
        $benefits = $p ? ($p->marketing_benefits ?? []) : [];
    }
    $benefits = array_values(array_filter($benefits, fn ($x) => true));
    while (count($benefits) < 5) {
        $benefits[] = '';
    }
    $selectedContentIds = old('content_ids', $p ? $p->contents->pluck('id')->all() : []);
@endphp

<div class="admin-form-grid">
    <div class="admin-form-card">
        <h5><i class="fas fa-box me-2"></i>Informations principales</h5>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control form-control-lg @error('title') is-invalid @enderror"
                       value="{{ old('title', $p->title ?? '') }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @if($p)
                <div class="col-md-6">
                    <label class="form-label fw-bold">Slug URL</label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                           value="{{ old('slug', $p->slug) }}">
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endif
            <div class="col-12">
                <label class="form-label fw-bold">Sous-titre</label>
                <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $p->subtitle ?? '') }}">
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">Accroche marketing</label>
                <input type="text" name="marketing_headline" class="form-control"
                       value="{{ old('marketing_headline', $p->marketing_headline ?? '') }}"
                       placeholder="Ex : Maîtrisez la bureautique en un seul pack">
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">Résumé court</label>
                <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $p->short_description ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-between gap-2 mb-1">
                    <label class="form-label fw-bold mb-0">Description détaillée</label>
                    <button type="button" class="btn btn-sm btn-outline-primary insert-package-embed-btn embed-insert-btn" data-target-textarea-id="package-description-editor">
                        <i class="fas fa-link me-1"></i>Insérer un lien embarqué
                    </button>
                </div>
                <textarea name="description" class="form-control package-rich-text-editor" rows="6" data-editor-placeholder="Décrivez le pack, ses avantages et ses objectifs...">{{ old('description', $p->description ?? '') }}</textarea>
                <small class="text-muted d-block mt-1">Intégrer une page/lien: utilisez <code>[[embed:https://exemple.com]]</code> dans la description.</small>
            </div>
        </div>
    </div>

    <div class="admin-form-card">
        <h5><i class="fas fa-layer-group me-2"></i>Contenus inclus <span class="text-danger">*</span></h5>
        <p class="text-muted small">Maintenez Ctrl (Cmd sur Mac) pour sélectionner plusieurs contenus. L’ordre suit la sélection.</p>
        <select name="content_ids[]" class="form-select @error('content_ids') is-invalid @enderror" multiple size="14">
            @foreach($courses as $course)
                <option value="{{ $course->id }}" @selected(in_array($course->id, $selectedContentIds, true))>
                    {{ $course->title }}
                </option>
            @endforeach
        </select>
        @error('content_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="admin-form-card">
        <h5><i class="fas fa-images me-2"></i>Couverture</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold" for="packageThumbnail">Image de couverture</label>
                @if($p && $p->thumbnail_url)
                    <div class="current-package-thumbnail mb-2 text-center">
                        <p class="small text-success mb-1"><i class="fas fa-check-circle me-1"></i>Image actuelle</p>
                        <img src="{{ $p->thumbnail_url }}" alt="" class="img-thumbnail rounded" style="max-height:120px;">
                    </div>
                @endif
                <div class="upload-zone package-upload-zone" id="packageThumbnailUploadZone">
                    <input type="file"
                           class="form-control d-none"
                           id="packageThumbnail"
                           name="thumbnail"
                           accept="image/jpeg,image/png,image/jpg,image/webp,image/gif"
                           onchange="handlePackageThumbnailUpload(this)">
                    <input type="hidden" id="thumbnail_chunk_path" name="thumbnail_chunk_path" value="{{ old('thumbnail_chunk_path') }}">
                    <input type="hidden" id="thumbnail_chunk_name" name="thumbnail_chunk_name" value="{{ old('thumbnail_chunk_name') }}">
                    <input type="hidden" id="thumbnail_chunk_size" name="thumbnail_chunk_size" value="{{ old('thumbnail_chunk_size') }}">
                    <div class="upload-placeholder text-center p-3" onclick="document.getElementById('packageThumbnail').click()">
                        <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
                        <p class="mb-1 small"><strong>Glissez-déposez une image ou cliquez pour parcourir</strong></p>
                        <p class="text-muted small mb-0">JPG, PNG, WEBP, GIF — max 5&nbsp;Mo · envoi par morceaux (comme les contenus)</p>
                    </div>
                    <div class="upload-preview d-none">
                        <p class="small text-info text-center mb-2"><i class="fas fa-eye me-1"></i>Nouvelle image</p>
                        <img src="" alt="" class="img-fluid rounded mx-auto d-block" style="max-width:100%;max-height:200px;">
                        <div class="upload-info mt-2 text-center">
                            <span class="badge bg-primary file-name"></span>
                            <span class="badge bg-info file-size"></span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-block mx-auto" onclick="clearPackageThumbnail()">
                            <i class="fas fa-times me-1"></i>Annuler le remplacement
                        </button>
                    </div>
                </div>
                <div class="invalid-feedback d-block" id="packageThumbnailError" style="display:none;"></div>
                @error('thumbnail')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('thumbnail_chunk_path')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @if($p)
                    <small class="text-muted d-block mt-1">Laissez vide pour conserver l’image actuelle.</small>
                @endif
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold" for="packageCoverVideo">Vidéo de couverture (fichier)</label>
                @if($p && $p->cover_video && ! $p->isYoutubeCoverVideo() && ! filter_var($p->cover_video, FILTER_VALIDATE_URL))
                    <div class="current-package-cover-video mb-2">
                        <p class="small text-success mb-1"><i class="fas fa-check-circle me-1"></i>Vidéo fichier actuelle</p>
                        <x-hls-native-video
                            class="w-100 rounded"
                            style="max-height:180px;background:#000;"
                            :fallback-src="$p->cover_video_url"
                            :hls-url="$p->hasCoverVideoHlsStreamReady() ? $p->cover_video_hls_manifest_url : ''"
                        />
                    </div>
                @endif
                <div class="upload-zone package-upload-zone" id="packageCoverVideoUploadZone">
                    <input type="file"
                           class="form-control d-none"
                           id="packageCoverVideo"
                           name="cover_video_file"
                           accept="video/mp4,video/webm,video/ogg"
                           onchange="handlePackageCoverVideoUpload(this)">
                    <input type="hidden" id="cover_video_path" name="cover_video_path" value="{{ old('cover_video_path', ($p && $p->cover_video && ! $p->isYoutubeCoverVideo() && ! filter_var($p->cover_video, FILTER_VALIDATE_URL)) ? $p->cover_video : '') }}">
                    <input type="hidden" id="cover_video_name" name="cover_video_name" value="{{ old('cover_video_name') }}">
                    <input type="hidden" id="cover_video_size" name="cover_video_size" value="{{ old('cover_video_size') }}">
                    <div class="upload-placeholder text-center p-3" onclick="document.getElementById('packageCoverVideo').click()">
                        <i class="fas fa-cloud-upload-alt fa-2x text-success mb-2"></i>
                        <p class="mb-1 small"><strong>Glissez-déposez une vidéo ou cliquez pour parcourir</strong></p>
                        <p class="text-muted small mb-0">MP4, WEBM, OGG — max 10&nbsp;Go · envoi par morceaux (comme les contenus)</p>
                    </div>
                    <div class="upload-preview d-none">
                        <p class="small text-info text-center mb-2"><i class="fas fa-eye me-1"></i>Nouvelle vidéo</p>
                        <video controls playsinline preload="metadata" class="w-100 rounded herime-stream-video" style="max-height:200px;background:#000;"></video>
                        <div class="upload-info mt-2 text-center">
                            <span class="badge bg-primary file-name"></span>
                            <span class="badge bg-info file-size"></span>
                        </div>
                        <div class="progress mt-2" style="height:6px;display:none;" id="packageCoverVideoProgress">
                            <div class="progress-bar bg-success" role="progressbar" style="width:0%"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-block mx-auto" onclick="clearPackageCoverVideo()">
                            <i class="fas fa-times me-1"></i>Annuler le remplacement
                        </button>
                    </div>
                </div>
                <div class="invalid-feedback d-block" id="packageCoverVideoError" style="display:none;"></div>
                @error('cover_video_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('cover_video_path')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @if($p)
                    <small class="text-muted d-block mt-1">Laissez vide pour conserver la vidéo fichier actuelle (si aucune URL YouTube n’est renseignée).</small>
                @endif
                <label class="form-label fw-bold mt-3">Ou ID / URL YouTube</label>
                <input type="text" name="cover_video_youtube_id" class="form-control"
                       value="{{ old('cover_video_youtube_id', $p->cover_video_youtube_id ?? '') }}"
                       placeholder="https://youtu.be/... ou ID">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="cover_video_is_unlisted" value="1" id="cv_unlisted"
                        @checked(old('cover_video_is_unlisted', $p->cover_video_is_unlisted ?? false))>
                    <label class="form-check-label" for="cv_unlisted">Vidéo YouTube non répertoriée</label>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-form-card">
        <h5><i class="fas fa-tags me-2"></i>Prix &amp; promotion</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Prix <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="price" class="form-control @error('price') is-invalid @enderror"
                       value="{{ old('price', $p->price ?? '0') }}" required>
                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Prix promo</label>
                <input type="number" step="0.01" min="0" name="sale_price" class="form-control @error('sale_price') is-invalid @enderror"
                       value="{{ old('sale_price', $p->sale_price ?? '') }}">
                @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Tri (liste)</label>
                <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $p->sort_order ?? 0) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Début promo</label>
                <input type="datetime-local" name="sale_start_at" class="form-control"
                       value="{{ old('sale_start_at', $p && $p->sale_start_at ? $p->sale_start_at->format('Y-m-d\TH:i') : '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Fin promo</label>
                <input type="datetime-local" name="sale_end_at" class="form-control"
                       value="{{ old('sale_end_at', $p && $p->sale_end_at ? $p->sale_end_at->format('Y-m-d\TH:i') : '') }}">
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="use_fake_promo_countdown" value="1" id="use_fake_promo_countdown"
                        onchange="togglePackageFakePromoDurationVisibility()"
                        @checked(old('use_fake_promo_countdown', $p->use_fake_promo_countdown ?? false))>
                    <label class="form-check-label" for="use_fake_promo_countdown">Compteur promo dynamique</label>
                </div>
                <small class="text-muted">Remplace le compteur normal et redémarre à chaque chargement de page.</small>
            </div>
            <div class="col-md-4" id="packageFakePromoDurationWrapper" style="display: {{ old('use_fake_promo_countdown', $p->use_fake_promo_countdown ?? false) ? 'block' : 'none' }};">
                <label class="form-label fw-bold">Durée compteur (jours)</label>
                <input type="number" min="1" max="365" name="fake_promo_duration_days" class="form-control @error('fake_promo_duration_days') is-invalid @enderror"
                       value="{{ old('fake_promo_duration_days', $p->fake_promo_duration_days ?? 3) }}">
                @error('fake_promo_duration_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 d-flex flex-wrap gap-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_sale_enabled" value="1" id="is_sale_enabled"
                        @checked(old('is_sale_enabled', $p->is_sale_enabled ?? true))>
                    <label class="form-check-label" for="is_sale_enabled">Promotions activées</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published"
                        @checked(old('is_published', $p->is_published ?? false))>
                    <label class="form-check-label" for="is_published">Publié sur le site</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured"
                        @checked(old('is_featured', $p->is_featured ?? false))>
                    <label class="form-check-label" for="is_featured">À la une</label>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-form-card">
        <h5><i class="fas fa-bullhorn me-2"></i>Marketing &amp; SEO</h5>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-bold">Texte du bouton d’achat (optionnel)</label>
                <input type="text" name="cta_label" class="form-control" value="{{ old('cta_label', $p->cta_label ?? '') }}" placeholder="Ex : Obtenir le pack">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Meta titre</label>
                <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $p->meta_title ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Meta mots-clés</label>
                <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $p->meta_keywords ?? '') }}">
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">Meta description</label>
                <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $p->meta_description ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Points forts (liste)</label>
                @foreach($highlights as $i => $line)
                    <input type="text" name="marketing_highlights[]" class="form-control mb-2" value="{{ $line }}" placeholder="Point {{ $i + 1 }}">
                @endforeach
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Bénéfices (liste)</label>
                @foreach($benefits as $i => $line)
                    <input type="text" name="marketing_benefits[]" class="form-control mb-2" value="{{ $line }}" placeholder="Bénéfice {{ $i + 1 }}">
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function togglePackageFakePromoDurationVisibility() {
    const checkbox = document.getElementById('use_fake_promo_countdown');
    const wrapper = document.getElementById('packageFakePromoDurationWrapper');
    if (!checkbox || !wrapper) return;
    wrapper.style.display = checkbox.checked ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    togglePackageFakePromoDurationVisibility();
});
</script>

@once
    @push('styles')
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        <style>
        .embed-insert-btn {
            width: auto;
            max-width: 260px;
            white-space: nowrap;
        }

        .rich-editor-upload-status {
            display: none;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            border-radius: 8px;
            padding: 0.4rem 0.6rem;
        }

        .rich-editor-upload-status.is-visible {
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .rich-editor-upload-status.is-loading {
            color: #0c4a6e;
            background: #e0f2fe;
            border: 1px solid #bae6fd;
        }

        .rich-editor-upload-status.is-success {
            color: #065f46;
            background: #d1fae5;
            border: 1px solid #a7f3d0;
        }

        .rich-editor-upload-status.is-error {
            color: #991b1b;
            background: #fee2e2;
            border: 1px solid #fecaca;
        }

        @media (max-width: 576px) {
            .embed-insert-btn {
                width: 100%;
                max-width: 100%;
                font-size: 0.78rem;
                padding: 0.35rem 0.5rem;
                white-space: normal;
                line-height: 1.2;
            }
        }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
        <script>
        (function () {
            const packageQuillInstances = new Map();
            const richEditorStatusElements = new Set();
            const richEditorUploadProgress = new Map();
            const RICH_EDITOR_IMAGE_MAX_SIZE = 5 * 1024 * 1024;
            let pendingRichEditorUploads = 0;
            let richEditorFeedbackTimer = null;

            function clearRichEditorFeedbackTimer() {
                if (richEditorFeedbackTimer) {
                    window.clearTimeout(richEditorFeedbackTimer);
                    richEditorFeedbackTimer = null;
                }
            }

            function applyRichEditorInlineStatus(state, message = '') {
                richEditorStatusElements.forEach((el) => {
                    el.classList.remove('is-loading', 'is-success', 'is-error', 'is-visible');
                    if (!state || !message) {
                        el.textContent = '';
                        return;
                    }
                    el.textContent = message;
                    el.classList.add('is-visible', `is-${state}`);
                });
            }

            function refreshRichEditorInlineUploadingStatus() {
                const activeCount = richEditorUploadProgress.size;
                if (activeCount === 0) {
                    applyRichEditorInlineStatus(null, '');
                    return;
                }

                clearRichEditorFeedbackTimer();
                let total = 0;
                richEditorUploadProgress.forEach((percent) => {
                    total += Math.max(0, Math.min(100, Number(percent) || 0));
                });
                const avg = Math.round(total / activeCount);
                const suffix = activeCount > 1 ? 's' : '';
                applyRichEditorInlineStatus('loading', `Téléversement en cours: ${activeCount} image${suffix} (${avg}%).`);
            }

            function showRichEditorInlineFeedback(state, message, durationMs = 3200) {
                clearRichEditorFeedbackTimer();
                applyRichEditorInlineStatus(state, message);
                richEditorFeedbackTimer = window.setTimeout(() => {
                    applyRichEditorInlineStatus(null, '');
                }, durationMs);
            }

            function getRichEditorChunkEndpoint() {
                const origin = window.location.origin.replace(/\/+$/, '');
                const path = "{{ trim(parse_url(route('admin.uploads.chunk'), PHP_URL_PATH), '/') }}";
                return `${origin}/${path}`;
            }

            function ensureModernDialogModal() {
                let modalEl = document.getElementById('packageRichtextModernDialog');
                if (modalEl) {
                    return modalEl;
                }

                modalEl = document.createElement('div');
                modalEl.className = 'modal fade';
                modalEl.id = 'packageRichtextModernDialog';
                modalEl.tabIndex = -1;
                modalEl.innerHTML = `
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" data-dialog-title>Information</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0" data-dialog-message></p>
                                <input type="text" class="form-control mt-3 d-none" data-dialog-input>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light d-none" data-dialog-cancel>Annuler</button>
                                <button type="button" class="btn btn-primary" data-dialog-ok>OK</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modalEl);

                return modalEl;
            }

            function showModernAlert(message, title = 'Information') {
                return new Promise((resolve) => {
                    const modalEl = ensureModernDialogModal();
                    const titleEl = modalEl.querySelector('[data-dialog-title]');
                    const messageEl = modalEl.querySelector('[data-dialog-message]');
                    const inputEl = modalEl.querySelector('[data-dialog-input]');
                    const okBtn = modalEl.querySelector('[data-dialog-ok]');
                    const cancelBtn = modalEl.querySelector('[data-dialog-cancel]');
                    const modal = new bootstrap.Modal(modalEl);

                    titleEl.textContent = title;
                    messageEl.textContent = message;
                    inputEl.classList.add('d-none');
                    inputEl.value = '';
                    cancelBtn.classList.add('d-none');

                    const onOk = () => modal.hide();
                    const onHidden = () => {
                        okBtn.removeEventListener('click', onOk);
                        modalEl.removeEventListener('hidden.bs.modal', onHidden);
                        resolve();
                    };

                    okBtn.addEventListener('click', onOk);
                    modalEl.addEventListener('hidden.bs.modal', onHidden);
                    modal.show();
                });
            }

            function showModernPrompt(message, title = 'Entrer une valeur') {
                return new Promise((resolve) => {
                    const modalEl = ensureModernDialogModal();
                    const titleEl = modalEl.querySelector('[data-dialog-title]');
                    const messageEl = modalEl.querySelector('[data-dialog-message]');
                    const inputEl = modalEl.querySelector('[data-dialog-input]');
                    const okBtn = modalEl.querySelector('[data-dialog-ok]');
                    const cancelBtn = modalEl.querySelector('[data-dialog-cancel]');
                    const modal = new bootstrap.Modal(modalEl);

                    titleEl.textContent = title;
                    messageEl.textContent = message;
                    inputEl.classList.remove('d-none');
                    inputEl.value = '';
                    cancelBtn.classList.remove('d-none');

                    let resolved = false;
                    const closeWithValue = (value) => {
                        if (resolved) {
                            return;
                        }
                        resolved = true;
                        resolve(value);
                        modal.hide();
                    };
                    const onOk = () => closeWithValue(inputEl.value);
                    const onCancel = () => closeWithValue(null);
                    const onHidden = () => {
                        if (!resolved) {
                            resolved = true;
                            resolve(null);
                        }
                        okBtn.removeEventListener('click', onOk);
                        cancelBtn.removeEventListener('click', onCancel);
                        modalEl.removeEventListener('hidden.bs.modal', onHidden);
                    };

                    okBtn.addEventListener('click', onOk);
                    cancelBtn.addEventListener('click', onCancel);
                    modalEl.addEventListener('hidden.bs.modal', onHidden);
                    modal.show();
                    inputEl.focus();
                });
            }

            function uploadRichEditorImageInChunks(file, onProgress) {
                return new Promise((resolve, reject) => {
                    const inlineUploadId = `pkg-rich-inline-${Date.now()}-${Math.random().toString(16).slice(2, 8)}`;
                    const uploadTaskId = window.UploadProgressModal
                        ? `pkg-richtext-${Date.now()}-${Math.random().toString(16).slice(2, 10)}`
                        : null;
                    richEditorUploadProgress.set(inlineUploadId, 5);
                    refreshRichEditorInlineUploadingStatus();
                    if (uploadTaskId && window.UploadProgressModal) {
                        window.UploadProgressModal.startTask(uploadTaskId, {
                            label: file?.name || 'Image description',
                            description: 'Téléversement de l’image de la description',
                            sizeLabel: file?.size ? `${Math.max(1, Math.round(file.size / 1024))} Ko` : '',
                            initialMessage: 'Préparation du téléversement…',
                        });
                    }

                    if (typeof Resumable === 'undefined') {
                        richEditorUploadProgress.delete(inlineUploadId);
                        refreshRichEditorInlineUploadingStatus();
                        showRichEditorInlineFeedback('error', 'Upload impossible: navigateur non compatible.');
                        if (uploadTaskId && window.UploadProgressModal) {
                            window.UploadProgressModal.errorTask(uploadTaskId, 'Votre navigateur ne supporte pas l’upload fractionné.');
                        }
                        reject(new Error('Votre navigateur ne supporte pas l’upload fractionné.'));
                        return;
                    }

                    const resumable = new Resumable({
                        target: getRichEditorChunkEndpoint(),
                        chunkSize: 1 * 1024 * 1024,
                        simultaneousUploads: 3,
                        testChunks: false,
                        throttleProgressCallbacks: 1,
                        fileParameterName: 'file',
                        fileType: ['png', 'jpg', 'jpeg', 'webp', 'gif'],
                        withCredentials: true,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json',
                        },
                        query: () => ({
                            upload_type: 'richtext_image',
                            original_name: file.name,
                        }),
                    });
                    let isSettled = false;

                    resumable.on('fileProgress', (resumableFile) => {
                        const percent = Math.max(0, Math.min(100, Math.round(resumableFile.progress() * 100)));
                        onProgress?.(percent);
                        const currentPercent = richEditorUploadProgress.get(inlineUploadId) ?? 0;
                        richEditorUploadProgress.set(inlineUploadId, Math.max(currentPercent, percent, 5));
                        refreshRichEditorInlineUploadingStatus();
                        if (uploadTaskId && window.UploadProgressModal) {
                            window.UploadProgressModal.updateTask(uploadTaskId, percent, 'Téléversement en cours…');
                        }
                    });

                    resumable.on('fileSuccess', (_resumableFile, response) => {
                        if (isSettled) {
                            return;
                        }
                        isSettled = true;
                        try {
                            const payload = typeof response === 'string' ? JSON.parse(response) : response;
                            if (!payload || !payload.url) {
                                richEditorUploadProgress.delete(inlineUploadId);
                                refreshRichEditorInlineUploadingStatus();
                                showRichEditorInlineFeedback('error', 'Erreur: réponse serveur invalide.');
                                if (uploadTaskId && window.UploadProgressModal) {
                                    window.UploadProgressModal.errorTask(uploadTaskId, 'Réponse serveur invalide.');
                                }
                                reject(new Error('Réponse serveur invalide.'));
                            } else {
                                richEditorUploadProgress.delete(inlineUploadId);
                                refreshRichEditorInlineUploadingStatus();
                                showRichEditorInlineFeedback('success', 'Image téléversée et intégrée.');
                                if (uploadTaskId && window.UploadProgressModal) {
                                    window.UploadProgressModal.completeTask(uploadTaskId, 'Image importée avec succès');
                                }
                                resolve(payload);
                            }
                        } catch (error) {
                            richEditorUploadProgress.delete(inlineUploadId);
                            refreshRichEditorInlineUploadingStatus();
                            showRichEditorInlineFeedback('error', 'Erreur: réponse serveur invalide.');
                            if (uploadTaskId && window.UploadProgressModal) {
                                window.UploadProgressModal.errorTask(uploadTaskId, 'Réponse serveur invalide.');
                            }
                            reject(new Error('Réponse serveur invalide.'));
                        } finally {
                            resumable.cancel();
                        }
                    });

                    const handleError = () => {
                        if (isSettled) {
                            return;
                        }
                        isSettled = true;
                        resumable.cancel();
                        richEditorUploadProgress.delete(inlineUploadId);
                        refreshRichEditorInlineUploadingStatus();
                        showRichEditorInlineFeedback('error', 'Échec du téléversement de l’image.');
                        if (uploadTaskId && window.UploadProgressModal) {
                            window.UploadProgressModal.errorTask(uploadTaskId, 'Échec du téléversement de l’image.');
                        }
                        reject(new Error('Échec du téléversement de l’image.'));
                    };
                    resumable.on('fileError', handleError);
                    resumable.on('error', handleError);

                    resumable.on('chunkingComplete', function () {
                        if (!resumable.isUploading()) {
                            resumable.upload();
                        }
                    });

                    resumable.addFile(file);
                });
            }

            function isPendingRichEditorImageSrc(src) {
                if (!src) {
                    return false;
                }

                const normalized = String(src).trim().toLowerCase();
                return normalized.startsWith('data:image/');
            }

            function hasPendingOrLocalImages() {
                for (const [, quill] of packageQuillInstances) {
                    const images = quill.root.querySelectorAll('img[src]');
                    for (const image of images) {
                        if (isPendingRichEditorImageSrc(image.getAttribute('src'))) {
                            return true;
                        }
                    }
                }
                return pendingRichEditorUploads > 0;
            }

            function handleQuillImageInsert(quill) {
                const picker = document.createElement('input');
                picker.setAttribute('type', 'file');
                picker.setAttribute('accept', 'image/png,image/jpeg,image/jpg,image/webp,image/gif');

                picker.addEventListener('change', async () => {
                    const file = picker.files && picker.files[0];
                    if (!file) {
                        return;
                    }

                    if (file.size > RICH_EDITOR_IMAGE_MAX_SIZE) {
                        showModernAlert('Image trop volumineuse. Maximum 5 Mo.');
                        return;
                    }

                    const readAsDataUrl = (selectedFile) => new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = (event) => resolve(event.target?.result || '');
                        reader.onerror = () => reject(new Error('Impossible de lire le fichier image.'));
                        reader.readAsDataURL(selectedFile);
                    });

                    try {
                        const range = quill.getSelection(true);
                        const insertAt = range ? range.index : quill.getLength();
                        const localSrc = await readAsDataUrl(file);
                        quill.insertEmbed(insertAt, 'image', localSrc, 'user');
                        quill.setSelection(insertAt + 1, 0);

                        try {
                            pendingRichEditorUploads++;
                            const payload = await uploadRichEditorImageInChunks(file);
                            const images = quill.root.querySelectorAll('img');
                            for (const image of images) {
                                if (image.getAttribute('src') === localSrc) {
                                    image.setAttribute('src', payload.url);
                                    break;
                                }
                            }
                            quill.update('user');
                        } catch (uploadError) {
                            showModernAlert(uploadError?.message || 'Image insérée localement, mais le téléversement chunk a échoué.');
                        } finally {
                            pendingRichEditorUploads = Math.max(0, pendingRichEditorUploads - 1);
                        }
                    } catch (error) {
                        showModernAlert(error?.message || 'Impossible de téléverser cette image.');
                    }
                });

                picker.click();
            }

            function initPackageEditor(textarea) {
                if (!textarea || textarea.dataset.quillReady === '1') {
                    return;
                }

                if (!textarea.id) {
                    textarea.id = 'package-description-editor';
                }

                const container = document.createElement('div');
                container.className = 'quill-editor-container';
                container.style.height = '260px';
                textarea.parentNode.insertBefore(container, textarea);
                const inlineStatus = document.createElement('div');
                inlineStatus.className = 'rich-editor-upload-status';
                inlineStatus.setAttribute('aria-live', 'polite');
                inlineStatus.setAttribute('role', 'status');
                textarea.parentNode.insertBefore(inlineStatus, textarea);
                richEditorStatusElements.add(inlineStatus);
                textarea.style.display = 'none';

                const quill = new Quill(container, {
                    theme: 'snow',
                    modules: {
                        toolbar: {
                            container: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'color': [] }, { 'background': [] }],
                                [{ 'align': [] }],
                                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                [{ 'size': ['small', false, 'large', 'huge'] }],
                                ['link', 'image'],
                                ['clean']
                            ],
                            handlers: {
                                image: function () {
                                    handleQuillImageInsert(this.quill);
                                }
                            }
                        }
                    },
                    placeholder: textarea.dataset.editorPlaceholder || 'Décrivez le pack...'
                });

                if (textarea.value) {
                    quill.root.innerHTML = textarea.value;
                }

                quill.on('text-change', function () {
                    textarea.value = quill.root.innerHTML;
                });
                textarea.value = quill.root.innerHTML;
                textarea.dataset.quillReady = '1';
                packageQuillInstances.set(textarea, quill);
                refreshRichEditorInlineUploadingStatus();
            }

            function initAllPackageEditors() {
                document.querySelectorAll('.package-rich-text-editor').forEach(initPackageEditor);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAllPackageEditors);
            } else {
                initAllPackageEditors();
            }

            document.addEventListener('click', async function (event) {
                const button = event.target.closest('.insert-package-embed-btn');
                if (!button) {
                    return;
                }

                const textareaId = button.dataset.targetTextareaId;
                const textarea = textareaId ? document.getElementById(textareaId) : document.querySelector('.package-rich-text-editor');
                if (!textarea) {
                    return;
                }

                const url = await showModernPrompt('Entrez l’URL à embarquer (https://...)', 'Insérer un lien embarqué');
                if (!url) {
                    return;
                }

                const normalized = url.trim();
                if (!/^https?:\/\//i.test(normalized)) {
                    showModernAlert('Veuillez saisir une URL valide commençant par http:// ou https://');
                    return;
                }

                const embedTag = `[[embed:${normalized}]]`;
                const quill = packageQuillInstances.get(textarea);

                if (quill) {
                    const range = quill.getSelection(true);
                    const index = range ? range.index : quill.getLength();
                    quill.insertText(index, embedTag);
                    quill.setSelection(index + embedTag.length, 0);
                    textarea.value = quill.root.innerHTML;
                    return;
                }

                textarea.value = `${textarea.value}\n${embedTag}`.trim();
            });

            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('packageForm') || document.querySelector('form');
                if (!form) {
                    return;
                }

                form.addEventListener('submit', function (event) {
                    if (hasPendingOrLocalImages()) {
                        event.preventDefault();
                        showModernAlert('Veuillez patienter: une image de la description est encore en cours de téléversement ou locale. Réessayez dans quelques secondes.');
                    }
                });
            });
        })();
        </script>
    @endpush
@endonce
