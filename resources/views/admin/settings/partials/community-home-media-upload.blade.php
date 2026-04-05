@php
    $cs = $cs ?? [];
    $rawMain = isset($cs['home_media_url']) ? trim((string) $cs['home_media_url']) : '';
    $mainIsExternal = $rawMain !== '' && (
        filter_var($rawMain, FILTER_VALIDATE_URL)
        || str_starts_with($rawMain, 'http://')
        || str_starts_with($rawMain, 'https://')
    );
    $mainPreviewUrl = $rawMain === '' ? '' : (
        $mainIsExternal ? $rawMain : \App\Helpers\FileHelper::url($rawMain, 'site/community-home')
    );
    $mainExternalValue = old('community_home_media_external_url', $mainIsExternal ? $rawMain : '');

    $rawPoster = isset($cs['home_media_poster_url']) ? trim((string) $cs['home_media_poster_url']) : '';
    $posterIsExternal = $rawPoster !== '' && (
        filter_var($rawPoster, FILTER_VALIDATE_URL)
        || str_starts_with($rawPoster, 'http://')
        || str_starts_with($rawPoster, 'https://')
    );
    $posterPreviewUrl = $rawPoster === '' ? '' : (
        $posterIsExternal ? $rawPoster : \App\Helpers\FileHelper::url($rawPoster, 'site/community-home')
    );
    $posterExternalValue = old('community_home_media_poster_external_url', $posterIsExternal ? $rawPoster : '');

    $homeType = old('community_home_media_type', $cs['home_media_type'] ?? 'image');
    $storedHomeType = $cs['home_media_type'] ?? 'image';
@endphp

<p class="text-muted small mb-3">
    Glissez-déposez un fichier ou cliquez pour téléverser (envoi par morceaux, comme pour les contenus). Vous pouvez aussi coller une <strong>URL externe</strong> (YouTube, lien direct MP4, etc.). Laisser vide et cocher « réinitialiser » = image par défaut du site.
</p>

<div class="mb-3">
    <label class="form-label fw-semibold" for="community_home_media_type">Type</label>
    <select name="community_home_media_type" id="community_home_media_type" class="form-select" required>
        <option value="image" @selected($homeType === 'image')>Image</option>
        <option value="video" @selected($homeType === 'video')>Vidéo</option>
    </select>
</div>

@if($rawMain !== '')
    <div class="mb-3 p-2 bg-light rounded border current-community-home-main">
        <div class="small text-muted mb-1">Média actuellement enregistré</div>
        @if(!$mainIsExternal)
            @if($storedHomeType === 'video')
                <video src="{{ $mainPreviewUrl }}" controls playsinline preload="metadata" class="w-100 rounded herime-stream-video" style="max-height:220px;background:#000;"></video>
            @else
                <img src="{{ $mainPreviewUrl }}" alt="" class="w-100 rounded object-fit-cover" style="max-height:220px">
            @endif
        @else
            <p class="small mb-0 text-break"><i class="fas fa-link me-1"></i>{{ $mainExternalValue }}</p>
        @endif
    </div>
@endif

<input type="hidden" name="community_home_media_chunk_path" id="community_home_media_chunk_path" value="{{ old('community_home_media_chunk_path', '') }}">
<input type="hidden" name="community_home_media_chunk_name" id="community_home_media_chunk_name" value="{{ old('community_home_media_chunk_name', '') }}">
<input type="hidden" name="community_home_media_chunk_size" id="community_home_media_chunk_size" value="{{ old('community_home_media_chunk_size', '') }}">

<div id="communityHomeMediaUploadZone" class="community-home-upload-zone upload-zone package-upload-zone mb-2">
    <div class="upload-placeholder text-center p-4">
        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
        <p class="mb-1 fw-semibold">Média principal (image ou vidéo selon le type)</p>
        <p class="small text-muted mb-2">JPG, PNG, WEBP, GIF — ou MP4, WEBM, OGG si type « Vidéo »</p>
        <label class="btn btn-outline-primary btn-sm mb-0">
            Choisir un fichier
            <input type="file" id="communityHomeMediaFile" class="d-none" accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/ogg">
        </label>
    </div>
    <div class="upload-preview d-none text-center p-3">
        <div class="community-home-new-preview-wrap mb-2"></div>
        <div><span class="file-name badge bg-secondary"></span> <span class="file-size text-muted small"></span></div>
        <button type="button" class="btn btn-link btn-sm text-danger p-0 mt-2" id="communityHomeMediaClearBtn">Retirer le fichier sélectionné</button>
    </div>
</div>
<div id="communityHomeMediaError" class="alert alert-danger py-2 small" style="display:none;"></div>

<div class="mb-3">
    <label class="form-label fw-semibold" for="community_home_media_external_url">URL externe (optionnel)</label>
    <input type="text" name="community_home_media_external_url" id="community_home_media_external_url" class="form-control"
           value="{{ $mainExternalValue }}"
           placeholder="https://www.youtube.com/… ou https://…/fichier.mp4">
    <p class="form-text small mb-0">Si renseignée à l’enregistrement (sans nouveau fichier ci-dessus), remplace le média par ce lien.</p>
</div>
<div class="form-check mb-4">
    <input type="checkbox" name="community_home_media_reset" value="1" class="form-check-input" id="community_home_media_reset"
           @checked(old('community_home_media_reset'))>
    <label class="form-check-label small" for="community_home_media_reset">Réinitialiser le média (image par défaut du site)</label>
</div>

<hr class="my-4">

<h6 class="fw-semibold mb-2">Affiche vidéo (poster)</h6>
<p class="text-muted small mb-3">Uniquement pour une <strong>vidéo fichier</strong> (pas YouTube). Image JPG, PNG, WEBP ou GIF.</p>

@if($rawPoster !== '')
    <div class="mb-3 p-2 bg-light rounded border">
        <div class="small text-muted mb-1">Poster actuel</div>
        @if(!$posterIsExternal)
            <img src="{{ $posterPreviewUrl }}" alt="" class="rounded object-fit-cover" style="max-height:120px;max-width:100%">
        @else
            <p class="small mb-0 text-break"><i class="fas fa-link me-1"></i>{{ $posterExternalValue }}</p>
        @endif
    </div>
@endif

<input type="hidden" name="community_home_poster_chunk_path" id="community_home_poster_chunk_path" value="{{ old('community_home_poster_chunk_path', '') }}">
<input type="hidden" name="community_home_poster_chunk_name" id="community_home_poster_chunk_name" value="{{ old('community_home_poster_chunk_name', '') }}">
<input type="hidden" name="community_home_poster_chunk_size" id="community_home_poster_chunk_size" value="{{ old('community_home_poster_chunk_size', '') }}">

<div id="communityHomePosterUploadZone" class="community-home-upload-zone upload-zone package-upload-zone mb-2">
    <div class="upload-placeholder text-center p-3">
        <i class="fas fa-image fa-lg text-muted mb-2"></i>
        <p class="small mb-2">Glisser-déposer une image d’affiche</p>
        <label class="btn btn-outline-secondary btn-sm mb-0">
            Choisir une image
            <input type="file" id="communityHomePosterFile" class="d-none" accept="image/jpeg,image/png,image/webp,image/gif">
        </label>
    </div>
    <div class="upload-preview d-none text-center p-3">
        <img src="" alt="" class="rounded mb-2 community-home-poster-preview-img" style="max-height:120px;max-width:100%;object-fit:contain">
        <div><span class="file-name badge bg-secondary"></span> <span class="file-size text-muted small"></span></div>
        <button type="button" class="btn btn-link btn-sm text-danger p-0 mt-2" id="communityHomePosterClearBtn">Retirer</button>
    </div>
</div>
<div id="communityHomePosterError" class="alert alert-danger py-2 small" style="display:none;"></div>

<div class="mb-2">
    <label class="form-label fw-semibold small" for="community_home_media_poster_external_url">URL poster externe (optionnel)</label>
    <input type="text" name="community_home_media_poster_external_url" id="community_home_media_poster_external_url" class="form-control form-control-sm"
           value="{{ $posterExternalValue }}" placeholder="https://…">
</div>
<div class="form-check">
    <input type="checkbox" name="community_home_poster_reset" value="1" class="form-check-input" id="community_home_poster_reset"
           @checked(old('community_home_poster_reset'))>
    <label class="form-check-label small" for="community_home_poster_reset">Supprimer l’affiche enregistrée</label>
</div>

@push('scripts')
@once
    <script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
@endonce
<script>
(function() {
    const CHUNK_SIZE_BYTES = 1 * 1024 * 1024;
    const CHUNK_UPLOAD_ENDPOINT = (function() {
        const origin = window.location.origin.replace(/\/+$/, '');
        const path = "{{ trim(parse_url(route('admin.uploads.chunk'), PHP_URL_PATH), '/') }}";
        return origin + '/' + path;
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
        const state = { active: new Set(), queue: new Set(), timer: null, isSubmitting: false };
        const getToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const isTemporary = (path) => typeof path === 'string' && config?.prefix && path.startsWith(config.prefix);
        const sendRequest = (paths, keepalive) => {
            const endpoint = config?.endpoint;
            const token = getToken();
            if (!endpoint || !token || !Array.isArray(paths) || paths.length === 0) return;
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
            } catch (e) {}
        };
        const performFlush = ({ includeActive = false, keepalive = false } = {}) => {
            if (state.timer) {
                clearTimeout(state.timer);
                state.timer = null;
            }
            const paths = new Set();
            state.queue.forEach((p) => paths.add(p));
            state.queue.clear();
            if (includeActive) {
                state.active.forEach((p) => paths.add(p));
                state.active.clear();
            }
            if (!paths.size) return;
            sendRequest(Array.from(paths), keepalive);
        };
        const scheduleFlush = () => {
            if (state.timer) return;
            state.timer = setTimeout(() => {
                state.timer = null;
                performFlush();
            }, 400);
        };
        return window.TempUploadManager = {
            configure(newConfig) { config = newConfig || config; },
            register(path) { if (isTemporary(path)) state.active.add(path); },
            queueDelete(path) {
                if (!isTemporary(path)) return;
                state.active.delete(path);
                state.queue.add(path);
                scheduleFlush();
            },
            flush(options = {}) { performFlush(options); },
            flushAll(options = {}) { performFlush({ includeActive: true, keepalive: options.keepalive }); },
            markSubmitting() { state.isSubmitting = true; },
            isSubmitting() { return state.isSubmitting; },
        };
    })();

    const VALID_IMAGE = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    const VALID_VIDEO = ['video/mp4', 'video/webm', 'video/ogg'];
    const MAX_IMAGE = 20 * 1024 * 1024;
    const MAX_VIDEO = 500 * 1024 * 1024;
    const MAX_POSTER = 5 * 1024 * 1024;

    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
    function isTempPath(path) {
        const p = window.__tempUploadConfig?.prefix;
        return typeof path === 'string' && p && path.startsWith(p);
    }
    function formatSize(bytes) {
        if (bytes === 0) return '0 o';
        const k = 1024;
        const sizes = ['o', 'Ko', 'Mo', 'Go'];
        const i = Math.min(Math.floor(Math.log(bytes) / Math.log(k)), sizes.length - 1);
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    function resetFileInput(input) {
        if (!input) return null;
        try { input.value = ''; } catch (e) {}
        const parent = input.parentNode;
        if (!parent) return input;
        const replacement = input.cloneNode(true);
        replacement.value = '';
        parent.replaceChild(replacement, input);
        return replacement;
    }
    function assignFile(input, file) {
        if (!input || !file) return false;
        try {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            return true;
        } catch (e) { return false; }
    }

    function taskStart(name, size, desc, onCancel) {
        if (!window.UploadProgressModal) return null;
        const id = 'comm-home-' + Date.now() + '-' + Math.random().toString(16).slice(2, 8);
        window.UploadProgressModal.startTask(id, {
            label: name,
            description: desc,
            sizeLabel: formatSize(size),
            initialMessage: 'Préparation…',
            onCancel,
            cancelLabel: 'Annuler',
            cancelable: true,
        });
        return id;
    }
    function taskUpdate(id, pct, msg) {
        if (id && window.UploadProgressModal) window.UploadProgressModal.updateTask(id, pct, msg);
    }
    function taskDone(id, msg) {
        if (id && window.UploadProgressModal) window.UploadProgressModal.completeTask(id, msg);
    }
    function taskErr(id, msg) {
        if (id && window.UploadProgressModal) window.UploadProgressModal.errorTask(id, msg);
    }

    function mediaKind() {
        const sel = document.getElementById('community_home_media_type');
        return sel && sel.value === 'video' ? 'video' : 'image';
    }

    let mainResumable = null;
    let mainTaskId = null;

    function showMainErr(msg) {
        const el = document.getElementById('communityHomeMediaError');
        if (!el) return;
        el.textContent = msg;
        el.style.display = msg ? 'block' : 'none';
    }

    function resetMainHidden(clearTemp) {
        const pathEl = document.getElementById('community_home_media_chunk_path');
        const nameEl = document.getElementById('community_home_media_chunk_name');
        const sizeEl = document.getElementById('community_home_media_chunk_size');
        if (!pathEl) return;
        const prev = pathEl.value;
        if (clearTemp !== false && prev && isTempPath(prev)) TempUploadManager.queueDelete(prev);
        pathEl.value = '';
        if (nameEl) nameEl.value = '';
        if (sizeEl) sizeEl.value = '';
    }

    function clearMainPreviewUI(zone) {
        if (!zone) return;
        const wrap = zone.querySelector('.community-home-new-preview-wrap');
        if (wrap) wrap.innerHTML = '';
        const ph = zone.querySelector('.upload-placeholder');
        const pr = zone.querySelector('.upload-preview');
        if (ph) ph.classList.remove('d-none');
        if (pr) pr.classList.add('d-none');
        zone.style.borderColor = '#dee2e6';
    }

    function startMainUpload(file, input) {
        const token = getCsrf();
        const zone = document.getElementById('communityHomeMediaUploadZone');
        if (!token) {
            showMainErr('Jeton CSRF manquant.');
            resetFileInput(input);
            return;
        }
        const kind = mediaKind();
        if (kind === 'image' && !VALID_IMAGE.includes(file.type)) {
            showMainErr('Pour le type Image, utilisez JPG, PNG, WEBP ou GIF.');
            resetFileInput(input);
            return;
        }
        if (kind === 'video' && !VALID_VIDEO.includes(file.type)) {
            showMainErr('Pour le type Vidéo, utilisez MP4, WEBM ou OGG.');
            resetFileInput(input);
            return;
        }
        const max = kind === 'video' ? MAX_VIDEO : MAX_IMAGE;
        if (file.size > max) {
            showMainErr('Fichier trop volumineux (max ' + formatSize(max) + ').');
            resetFileInput(input);
            return;
        }
        if (typeof Resumable === 'undefined') {
            showMainErr('Navigateur incompatible avec l’envoi fractionné.');
            resetFileInput(input);
            return;
        }

        resetMainHidden(true);
        showMainErr('');

        if (mainResumable) { try { mainResumable.cancel(); } catch (e) {} mainResumable = null; }
        if (mainTaskId) { taskErr(mainTaskId, 'Annulé'); mainTaskId = null; }

        const placeholder = zone.querySelector('.upload-placeholder');
        const preview = zone.querySelector('.upload-preview');
        const wrap = zone.querySelector('.community-home-new-preview-wrap');
        wrap.innerHTML = '';
        if (kind === 'video') {
            const v = document.createElement('video');
            v.controls = true;
            v.muted = true;
            v.className = 'w-100 rounded';
            v.style.maxHeight = '220px';
            v.style.background = '#000';
            v.src = URL.createObjectURL(file);
            wrap.appendChild(v);
        } else {
            const img = document.createElement('img');
            img.className = 'w-100 rounded object-fit-cover';
            img.style.maxHeight = '220px';
            img.src = URL.createObjectURL(file);
            wrap.appendChild(img);
        }
        preview.querySelector('.file-name').textContent = file.name;
        preview.querySelector('.file-size').textContent = formatSize(file.size);
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
        document.querySelectorAll('.current-community-home-main').forEach((el) => el.classList.add('d-none'));

        const resumable = new Resumable({
            target: CHUNK_UPLOAD_ENDPOINT,
            chunkSize: CHUNK_SIZE_BYTES,
            simultaneousUploads: 3,
            testChunks: false,
            throttleProgressCallbacks: 1,
            fileParameterName: 'file',
            fileType: kind === 'video' ? ['mp4', 'webm', 'ogg'] : ['png', 'jpg', 'jpeg', 'webp', 'gif'],
            withCredentials: true,
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            query: () => ({ upload_type: 'community_home', original_name: file.name }),
        });
        mainResumable = resumable;

        mainTaskId = taskStart(file.name, file.size, 'Téléversement du média communauté', () => {
            try { resumable.cancel(); } catch (e) {}
        });

        resumable.on('fileProgress', (rf) => {
            const pct = Math.max(0, Math.min(100, Math.round(rf.progress() * 100)));
            taskUpdate(mainTaskId, pct, 'Envoi en cours…');
        });

        const fail = (msg, { silentModal } = {}) => {
            if (mainTaskId) {
                if (silentModal && window.UploadProgressModal?.cancelTask) window.UploadProgressModal.cancelTask(mainTaskId);
                else taskErr(mainTaskId, msg || 'Erreur d’envoi');
                mainTaskId = null;
            }
            mainResumable = null;
            if (!silentModal) showMainErr(msg || 'Erreur d’envoi');
            zone.style.borderColor = '#dc3545';
            resetMainHidden(false);
            clearMainPreviewUI(zone);
            const ph = zone.querySelector('.upload-placeholder');
            const pr = zone.querySelector('.upload-preview');
            if (ph) ph.classList.remove('d-none');
            if (pr) pr.classList.add('d-none');
            document.querySelectorAll('.current-community-home-main').forEach((el) => el.classList.remove('d-none'));
        };

        resumable.on('fileSuccess', (rf, response) => {
            let payload = response;
            if (typeof response === 'string') {
                try { payload = JSON.parse(response); } catch (e) { payload = null; }
            }
            if (!payload || !payload.path) {
                fail('Réponse serveur invalide.');
                return;
            }
            const pathEl = document.getElementById('community_home_media_chunk_path');
            const nameEl = document.getElementById('community_home_media_chunk_name');
            const sizeEl = document.getElementById('community_home_media_chunk_size');
            const prev = pathEl.value;
            pathEl.value = payload.path;
            if (nameEl) nameEl.value = payload.filename || file.name;
            if (sizeEl) sizeEl.value = payload.size || file.size;
            if (prev && prev !== payload.path && isTempPath(prev)) TempUploadManager.queueDelete(prev);
            TempUploadManager.register(payload.path);
            taskDone(mainTaskId, 'Média importé');
            mainTaskId = null;
            mainResumable = null;
            zone.style.borderColor = '#28a745';
            showMainErr('');
            const cleared = resetFileInput(input);
            if (cleared && cleared.id === 'communityHomeMediaFile') {
                cleared.addEventListener('change', () => window.__communityHomeOnMainFile(cleared));
            }
        });
        resumable.on('fileError', () => fail('Échec du téléversement.'));
        resumable.on('error', () => fail('Échec du téléversement.'));
        resumable.on('cancel', () => fail('Annulé.', { silentModal: true }));
        resumable.on('chunkingComplete', () => { if (!resumable.isUploading()) resumable.upload(); });
        resumable.addFile(file);
    }

    window.__communityHomeOnMainFile = function(input) {
        if (!input.files || !input.files[0]) return;
        startMainUpload(input.files[0], input);
    };

    let posterResumable = null;
    let posterTaskId = null;

    function showPosterErr(msg) {
        const el = document.getElementById('communityHomePosterError');
        if (!el) return;
        el.textContent = msg;
        el.style.display = msg ? 'block' : 'none';
    }

    function resetPosterHidden(clearTemp) {
        const pathEl = document.getElementById('community_home_poster_chunk_path');
        const nameEl = document.getElementById('community_home_poster_chunk_name');
        const sizeEl = document.getElementById('community_home_poster_chunk_size');
        if (!pathEl) return;
        const prev = pathEl.value;
        if (clearTemp !== false && prev && isTempPath(prev)) TempUploadManager.queueDelete(prev);
        pathEl.value = '';
        if (nameEl) nameEl.value = '';
        if (sizeEl) sizeEl.value = '';
    }

    function startPosterUpload(file, input) {
        const token = getCsrf();
        const zone = document.getElementById('communityHomePosterUploadZone');
        if (!VALID_IMAGE.includes(file.type)) {
            showPosterErr('Utilisez JPG, PNG, WEBP ou GIF.');
            resetFileInput(input);
            return;
        }
        if (file.size > MAX_POSTER) {
            showPosterErr('Image trop volumineuse (max 5 Mo).');
            resetFileInput(input);
            return;
        }
        if (typeof Resumable === 'undefined') {
            showPosterErr('Navigateur incompatible.');
            resetFileInput(input);
            return;
        }

        resetPosterHidden(true);
        showPosterErr('');

        if (posterResumable) { try { posterResumable.cancel(); } catch (e) {} posterResumable = null; }
        if (posterTaskId) { taskErr(posterTaskId, 'Annulé'); posterTaskId = null; }

        const placeholder = zone.querySelector('.upload-placeholder');
        const preview = zone.querySelector('.upload-preview');
        const img = preview.querySelector('.community-home-poster-preview-img');
        const r = new FileReader();
        r.onload = (e) => { img.src = e.target.result; };
        r.readAsDataURL(file);
        preview.querySelector('.file-name').textContent = file.name;
        preview.querySelector('.file-size').textContent = formatSize(file.size);
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');

        const resumable = new Resumable({
            target: CHUNK_UPLOAD_ENDPOINT,
            chunkSize: CHUNK_SIZE_BYTES,
            simultaneousUploads: 3,
            testChunks: false,
            throttleProgressCallbacks: 1,
            fileParameterName: 'file',
            fileType: ['png', 'jpg', 'jpeg', 'webp', 'gif'],
            withCredentials: true,
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            query: () => ({ upload_type: 'community_home_poster', original_name: file.name }),
        });
        posterResumable = resumable;

        posterTaskId = taskStart(file.name, file.size, 'Téléversement du poster', () => {
            try { resumable.cancel(); } catch (e) {}
        });

        resumable.on('fileProgress', (rf) => {
            const pct = Math.max(0, Math.min(100, Math.round(rf.progress() * 100)));
            taskUpdate(posterTaskId, pct, 'Envoi…');
        });

        const failP = (msg, { silentModal } = {}) => {
            if (posterTaskId) {
                if (silentModal && window.UploadProgressModal?.cancelTask) window.UploadProgressModal.cancelTask(posterTaskId);
                else taskErr(posterTaskId, msg || 'Erreur');
                posterTaskId = null;
            }
            posterResumable = null;
            if (!silentModal) showPosterErr(msg || 'Erreur');
            resetPosterHidden(false);
            const ph = zone.querySelector('.upload-placeholder');
            const pr = zone.querySelector('.upload-preview');
            if (ph) ph.classList.remove('d-none');
            if (pr) pr.classList.add('d-none');
        };

        resumable.on('fileSuccess', (rf, response) => {
            let payload = response;
            if (typeof response === 'string') {
                try { payload = JSON.parse(response); } catch (e) { payload = null; }
            }
            if (!payload || !payload.path) {
                failP('Réponse serveur invalide.');
                return;
            }
            const pathEl = document.getElementById('community_home_poster_chunk_path');
            const nameEl = document.getElementById('community_home_poster_chunk_name');
            const sizeEl = document.getElementById('community_home_poster_chunk_size');
            const prev = pathEl.value;
            pathEl.value = payload.path;
            if (nameEl) nameEl.value = payload.filename || file.name;
            if (sizeEl) sizeEl.value = payload.size || file.size;
            if (prev && prev !== payload.path && isTempPath(prev)) TempUploadManager.queueDelete(prev);
            TempUploadManager.register(payload.path);
            taskDone(posterTaskId, 'Poster importé');
            posterTaskId = null;
            posterResumable = null;
            showPosterErr('');
            const cleared = resetFileInput(input);
            if (cleared && cleared.id === 'communityHomePosterFile') {
                cleared.addEventListener('change', () => window.__communityHomeOnPosterFile(cleared));
            }
        });
        resumable.on('fileError', () => failP('Échec du téléversement.'));
        resumable.on('error', () => failP('Échec du téléversement.'));
        resumable.on('cancel', () => failP('Annulé.', { silentModal: true }));
        resumable.on('chunkingComplete', () => { if (!resumable.isUploading()) resumable.upload(); });
        resumable.addFile(file);
    }

    window.__communityHomeOnPosterFile = function(input) {
        if (!input.files || !input.files[0]) return;
        startPosterUpload(input.files[0], input);
    };

    document.addEventListener('DOMContentLoaded', function() {
        const mainInput = document.getElementById('communityHomeMediaFile');
        const mainZone = document.getElementById('communityHomeMediaUploadZone');
        const posterInput = document.getElementById('communityHomePosterFile');
        const posterZone = document.getElementById('communityHomePosterUploadZone');
        const form = document.getElementById('admin-settings-community-form');

        if (form) {
            form.addEventListener('submit', () => TempUploadManager.markSubmitting());
        }

        if (mainInput) {
            mainInput.addEventListener('change', function() { window.__communityHomeOnMainFile(this); });
        }
        if (mainZone && mainInput) {
            ['dragenter', 'dragover'].forEach((ev) => {
                mainZone.addEventListener(ev, (e) => { e.preventDefault(); e.stopPropagation(); mainZone.classList.add('is-dragover'); });
            });
            mainZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (!mainZone.contains(e.relatedTarget)) mainZone.classList.remove('is-dragover');
            });
            mainZone.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                mainZone.classList.remove('is-dragover');
                const f = e.dataTransfer?.files?.[0];
                if (f && assignFile(mainInput, f)) window.__communityHomeOnMainFile(mainInput);
            });
        }

        document.getElementById('communityHomeMediaClearBtn')?.addEventListener('click', function() {
            if (mainResumable) { try { mainResumable.cancel(); } catch (e) {} }
            if (mainTaskId && window.UploadProgressModal?.cancelTask) window.UploadProgressModal.cancelTask(mainTaskId);
            mainTaskId = null;
            mainResumable = null;
            resetMainHidden(true);
            const zone = document.getElementById('communityHomeMediaUploadZone');
            clearMainPreviewUI(zone);
            let inp = document.getElementById('communityHomeMediaFile');
            inp = resetFileInput(inp);
            if (inp) inp.addEventListener('change', function() { window.__communityHomeOnMainFile(this); });
            document.querySelectorAll('.current-community-home-main').forEach((el) => el.classList.remove('d-none'));
        });

        if (posterInput) {
            posterInput.addEventListener('change', function() { window.__communityHomeOnPosterFile(this); });
        }
        if (posterZone && posterInput) {
            ['dragenter', 'dragover'].forEach((ev) => {
                posterZone.addEventListener(ev, (e) => { e.preventDefault(); e.stopPropagation(); posterZone.classList.add('is-dragover'); });
            });
            posterZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (!posterZone.contains(e.relatedTarget)) posterZone.classList.remove('is-dragover');
            });
            posterZone.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                posterZone.classList.remove('is-dragover');
                const f = e.dataTransfer?.files?.[0];
                if (f && assignFile(posterInput, f)) window.__communityHomeOnPosterFile(posterInput);
            });
        }

        document.getElementById('communityHomePosterClearBtn')?.addEventListener('click', function() {
            if (posterResumable) { try { posterResumable.cancel(); } catch (e) {} }
            if (posterTaskId && window.UploadProgressModal?.cancelTask) window.UploadProgressModal.cancelTask(posterTaskId);
            posterTaskId = null;
            posterResumable = null;
            resetPosterHidden(true);
            const zone = document.getElementById('communityHomePosterUploadZone');
            const ph = zone?.querySelector('.upload-placeholder');
            const pr = zone?.querySelector('.upload-preview');
            if (ph) ph.classList.remove('d-none');
            if (pr) pr.classList.add('d-none');
            let inp = document.getElementById('communityHomePosterFile');
            inp = resetFileInput(inp);
            if (inp) inp.addEventListener('change', function() { window.__communityHomeOnPosterFile(this); });
        });

        if (!window.__communityHomeUnload) {
            window.__communityHomeUnload = true;
            window.addEventListener('beforeunload', function() {
                if (TempUploadManager && !TempUploadManager.isSubmitting()) {
                    TempUploadManager.flushAll({ keepalive: true });
                }
            });
        }
    });
})();
</script>
@endpush

@push('styles')
<style>
.community-home-upload-zone.upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    background-color: #f8f9fa;
    transition: border-color 0.2s ease, background-color 0.2s ease;
}
.community-home-upload-zone.upload-zone:hover,
.community-home-upload-zone.upload-zone.is-dragover {
    border-color: #0b1f3a;
    background-color: #e9ecef;
}
</style>
@endpush
