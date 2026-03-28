@push('scripts')
@once
    <script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
@endonce
<script>
(function() {
    const MAX_IMAGE_SIZE = 5 * 1024 * 1024;
    const VALID_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    const CHUNK_SIZE_BYTES = 1 * 1024 * 1024;
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

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
    function isTemporaryPath(path) {
        const prefix = window.__tempUploadConfig?.prefix;
        return typeof path === 'string' && prefix && path.startsWith(prefix);
    }
    function registerTemporaryPath(path) { TempUploadManager.register(path); }
    function queueTemporaryDeletion(path) { TempUploadManager.queueDelete(path); }

    function getPackageThumbnailHiddenInputs() {
        return {
            path: document.getElementById('thumbnail_chunk_path'),
            name: document.getElementById('thumbnail_chunk_name'),
            size: document.getElementById('thumbnail_chunk_size'),
        };
    }

    function resetPackageThumbnailHiddenFields(options = {}) {
        const { preserve = false } = options;
        const hidden = getPackageThumbnailHiddenInputs();
        if (!hidden.path) return;
        if (!preserve) {
            const previousPath = hidden.path.value;
            if (previousPath && isTemporaryPath(previousPath)) {
                queueTemporaryDeletion(previousPath);
            }
            hidden.path.value = '';
        }
        if (!preserve && hidden.name) hidden.name.value = '';
        if (!preserve && hidden.size) hidden.size.value = '';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.min(Math.floor(Math.log(bytes) / Math.log(k)), sizes.length - 1);
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    function showPackageThumbError(message) {
        const errorDiv = document.getElementById('packageThumbnailError');
        if (!errorDiv) return;
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    function resetPackageFileInput(input) {
        if (!input) return null;
        try { input.value = ''; } catch (e) {}
        const parent = input.parentNode;
        if (!parent) return input;
        const replacement = input.cloneNode(true);
        replacement.value = '';
        parent.replaceChild(replacement, input);
        return replacement;
    }

    function createUploadTask(fileName, fileSize, description, extra = {}) {
        if (!window.UploadProgressModal) return null;
        const taskId = `pkg-upload-${Date.now()}-${Math.random().toString(16).slice(2, 10)}`;
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
    function completeUploadTask(taskId, message) {
        if (taskId && window.UploadProgressModal) {
            window.UploadProgressModal.completeTask(taskId, message);
        }
    }
    function errorUploadTask(taskId, message) {
        if (taskId && window.UploadProgressModal) {
            window.UploadProgressModal.errorTask(taskId, message);
        }
    }

    let pkgThumbnailResumable = null;
    let pkgThumbnailTaskId = null;

    window.handlePackageThumbnailUpload = function(input) {
        const zone = document.getElementById('packageThumbnailUploadZone');
        const errorDiv = document.getElementById('packageThumbnailError');
        if (!zone || !errorDiv) return;

        const placeholder = zone.querySelector('.upload-placeholder');
        const preview = zone.querySelector('.upload-preview');
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';

        if (!input.files || !input.files[0]) return;

        const file = input.files[0];
        if (!VALID_IMAGE_TYPES.includes(file.type)) {
            showPackageThumbError('❌ Format invalide. Utilisez JPG, PNG, WEBP ou GIF.');
            resetPackageFileInput(input);
            return;
        }
        if (file.size > MAX_IMAGE_SIZE) {
            showPackageThumbError('❌ Le fichier est trop volumineux. Maximum 5 Mo.');
            resetPackageFileInput(input);
            return;
        }
        if (typeof Resumable === 'undefined') {
            showPackageThumbError('❌ Votre navigateur ne supporte pas l’upload fractionné.');
            resetPackageFileInput(input);
            return;
        }

        resetPackageThumbnailHiddenFields();

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = preview.querySelector('img');
            if (img) img.src = e.target.result;
            const fn = preview.querySelector('.file-name');
            const fs = preview.querySelector('.file-size');
            if (fn) fn.textContent = file.name;
            if (fs) fs.textContent = formatFileSize(file.size);
            placeholder.classList.add('d-none');
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);

        document.querySelectorAll('.current-package-thumbnail').forEach((el) => el.classList.add('d-none'));

        zone.style.borderColor = '#0d6efd';
        startPackageThumbnailChunkUpload(file, input);
    };

    function startPackageThumbnailChunkUpload(file, input) {
        const token = getCsrfToken();
        const errorDiv = document.getElementById('packageThumbnailError');
        const zone = document.getElementById('packageThumbnailUploadZone');
        if (!token) {
            showPackageThumbError('❌ Impossible de récupérer le jeton CSRF.');
            resetPackageFileInput(input);
            return;
        }
        if (pkgThumbnailResumable) {
            try { pkgThumbnailResumable.cancel(); } catch (e) {}
            pkgThumbnailResumable = null;
        }
        if (pkgThumbnailTaskId) {
            errorUploadTask(pkgThumbnailTaskId, 'Téléversement annulé');
            pkgThumbnailTaskId = null;
        }

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
            query: () => ({
                upload_type: 'thumbnail',
                original_name: file.name,
            }),
        });
        pkgThumbnailResumable = resumable;

        pkgThumbnailTaskId = createUploadTask(
            file.name,
            file.size,
            'Téléversement de l’image de couverture',
            {
                onCancel: () => window.clearPackageThumbnail({ skipModalCancel: true }),
                cancelLabel: 'Annuler',
            }
        );

        resumable.on('fileProgress', function(resumableFile) {
            const percent = Math.max(0, Math.min(100, Math.round(resumableFile.progress() * 100)));
            updateUploadTask(pkgThumbnailTaskId, percent, 'Téléversement en cours…');
        });

        const handleUploadError = (message, { suppressModal = false } = {}) => {
            const displayMessage = typeof message === 'string' && message.trim() !== ''
                ? message
                : 'Erreur lors du téléversement de l’image.';
            if (pkgThumbnailTaskId) {
                if (suppressModal && window.UploadProgressModal && typeof window.UploadProgressModal.cancelTask === 'function') {
                    window.UploadProgressModal.cancelTask(pkgThumbnailTaskId);
                } else {
                    errorUploadTask(pkgThumbnailTaskId, displayMessage);
                }
                pkgThumbnailTaskId = null;
            }
            pkgThumbnailResumable = null;
            if (!suppressModal) showPackageThumbError(displayMessage);
            zone.style.borderColor = '#dc3545';
            window.clearPackageThumbnail({ skipModalCancel: true, preserveHidden: false, restoreExistingPreview: true });
        };

        resumable.on('fileSuccess', function(resumableFile, response) {
            let payload = response;
            if (typeof response === 'string') {
                try { payload = JSON.parse(response); } catch (e) { payload = null; }
            }
            if (!payload || !payload.path) {
                handleUploadError('Réponse invalide du serveur.');
                return;
            }
            const hidden = getPackageThumbnailHiddenInputs();
            const previousPath = hidden.path ? hidden.path.value : '';
            if (hidden.path) hidden.path.value = payload.path;
            if (hidden.name) hidden.name.value = payload.filename || file.name;
            if (hidden.size) hidden.size.value = payload.size || file.size;
            if (previousPath && previousPath !== payload.path) queueTemporaryDeletion(previousPath);
            registerTemporaryPath(payload.path);
            if (pkgThumbnailTaskId) {
                completeUploadTask(pkgThumbnailTaskId, 'Image importée avec succès');
                pkgThumbnailTaskId = null;
            }
            pkgThumbnailResumable = null;
            zone.style.borderColor = '#28a745';
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
            const cleared = resetPackageFileInput(input);
            if (cleared && cleared.id === 'packageThumbnail') {
                cleared.addEventListener('change', function() { window.handlePackageThumbnailUpload(this); });
            }
        });

        resumable.on('fileError', function(resumableFile, message) { handleUploadError(message); });
        resumable.on('error', function(message) { handleUploadError(message); });
        resumable.on('cancel', function() { handleUploadError('Téléversement annulé.', { suppressModal: true }); });
        resumable.on('chunkingComplete', function() {
            if (!resumable.isUploading()) resumable.upload();
        });
        resumable.addFile(file);
    }

    window.clearPackageThumbnail = function(options = {}) {
        const { skipModalCancel = false, preserveHidden = false, restoreExistingPreview = true } = options;
        const zone = document.getElementById('packageThumbnailUploadZone');
        if (!zone) return;
        const placeholder = zone.querySelector('.upload-placeholder');
        const preview = zone.querySelector('.upload-preview');
        let input = document.getElementById('packageThumbnail');
        const errorDiv = document.getElementById('packageThumbnailError');

        if (pkgThumbnailResumable) {
            try { pkgThumbnailResumable.cancel(); } catch (e) {}
        }
        pkgThumbnailResumable = null;

        if (pkgThumbnailTaskId) {
            const progressModal = window.UploadProgressModal;
            if (!skipModalCancel && progressModal && typeof progressModal.cancelTask === 'function') {
                progressModal.cancelTask(pkgThumbnailTaskId);
            }
            pkgThumbnailTaskId = null;
        }

        if (!preserveHidden) resetPackageThumbnailHiddenFields();

        input = resetPackageFileInput(input);
        if (input && input.id === 'packageThumbnail') {
            input.addEventListener('change', function() { window.handlePackageThumbnailUpload(this); });
        }

        const img = preview.querySelector('img');
        if (img) img.src = '';
        const fileNameBadge = preview.querySelector('.file-name');
        if (fileNameBadge) fileNameBadge.textContent = '';
        const fileSizeBadge = preview.querySelector('.file-size');
        if (fileSizeBadge) fileSizeBadge.textContent = '';
        if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
        }
        preview.classList.add('d-none');
        placeholder.classList.remove('d-none');
        zone.style.borderColor = '#dee2e6';

        if (restoreExistingPreview) {
            document.querySelectorAll('.current-package-thumbnail').forEach((el) => el.classList.remove('d-none'));
        }
    };

    const MAX_VIDEO_SIZE = 10 * 1024 * 1024 * 1024;
    const VALID_VIDEO_TYPES = ['video/mp4', 'video/webm', 'video/ogg'];
    let pkgCoverVideoResumable = null;
    let pkgCoverVideoTaskId = null;

    function getPackageCoverVideoHiddenInputs() {
        return {
            path: document.getElementById('cover_video_path'),
            name: document.getElementById('cover_video_name'),
            size: document.getElementById('cover_video_size'),
        };
    }

    function resetPackageCoverVideoHiddenFields(options = {}) {
        const { preserve = false } = options;
        const hidden = getPackageCoverVideoHiddenInputs();
        if (!hidden.path) return;
        if (!preserve) {
            const previousPath = hidden.path.value;
            if (previousPath && isTemporaryPath(previousPath)) {
                queueTemporaryDeletion(previousPath);
            }
            hidden.path.value = '';
        }
        if (!preserve && hidden.name) hidden.name.value = '';
        if (!preserve && hidden.size) hidden.size.value = '';
    }

    function showPackageCoverVideoError(message) {
        const errorDiv = document.getElementById('packageCoverVideoError');
        if (!errorDiv) return;
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    function revokePackageCoverVideoObjectUrl(zone) {
        if (!zone) return;
        const u = zone.dataset.previewObjectUrl;
        if (u) {
            try { URL.revokeObjectURL(u); } catch (e) {}
            delete zone.dataset.previewObjectUrl;
        }
    }

    window.handlePackageCoverVideoUpload = function(input) {
        const zone = document.getElementById('packageCoverVideoUploadZone');
        const errorDiv = document.getElementById('packageCoverVideoError');
        if (!zone || !errorDiv) return;

        const placeholder = zone.querySelector('.upload-placeholder');
        const preview = zone.querySelector('.upload-preview');
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';

        if (!input.files || !input.files[0]) return;

        const file = input.files[0];
        if (!VALID_VIDEO_TYPES.includes(file.type)) {
            showPackageCoverVideoError('❌ Format invalide. Utilisez MP4, WEBM ou OGG.');
            resetPackageFileInput(input);
            return;
        }
        if (file.size > MAX_VIDEO_SIZE) {
            showPackageCoverVideoError(`❌ Fichier trop volumineux. Maximum ${formatFileSize(MAX_VIDEO_SIZE)}.`);
            resetPackageFileInput(input);
            return;
        }
        if (typeof Resumable === 'undefined') {
            showPackageCoverVideoError('❌ Votre navigateur ne supporte pas l’upload fractionné.');
            resetPackageFileInput(input);
            return;
        }

        resetPackageCoverVideoHiddenFields();
        revokePackageCoverVideoObjectUrl(zone);

        const video = preview.querySelector('video');
        if (video) {
            try { video.pause(); } catch (e) {}
            video.removeAttribute('src');
            const objectUrl = URL.createObjectURL(file);
            zone.dataset.previewObjectUrl = objectUrl;
            video.src = objectUrl;
        }
        const fn = preview.querySelector('.file-name');
        const fs = preview.querySelector('.file-size');
        if (fn) fn.textContent = file.name;
        if (fs) fs.textContent = formatFileSize(file.size);
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');

        document.querySelectorAll('.current-package-cover-video').forEach((el) => el.classList.add('d-none'));

        zone.style.borderColor = '#198754';
        startPackageCoverVideoChunkUpload(file, input);
    };

    function startPackageCoverVideoChunkUpload(file, input) {
        const token = getCsrfToken();
        const errorDiv = document.getElementById('packageCoverVideoError');
        const zone = document.getElementById('packageCoverVideoUploadZone');
        const progressWrapper = document.getElementById('packageCoverVideoProgress');
        const progressBar = progressWrapper ? progressWrapper.querySelector('.progress-bar') : null;

        if (!token) {
            showPackageCoverVideoError('❌ Impossible de récupérer le jeton CSRF.');
            resetPackageFileInput(input);
            return;
        }

        if (pkgCoverVideoResumable) {
            try { pkgCoverVideoResumable.cancel(); } catch (e) {}
            pkgCoverVideoResumable = null;
        }
        if (pkgCoverVideoTaskId) {
            errorUploadTask(pkgCoverVideoTaskId, 'Téléversement annulé');
            pkgCoverVideoTaskId = null;
        }

        if (progressWrapper) progressWrapper.style.display = 'block';
        if (progressBar) progressBar.style.width = '0%';

        const resumable = new Resumable({
            target: CHUNK_UPLOAD_ENDPOINT,
            chunkSize: CHUNK_SIZE_BYTES,
            simultaneousUploads: 3,
            testChunks: false,
            throttleProgressCallbacks: 1,
            fileParameterName: 'file',
            fileType: ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'mkv', 'pdf', 'zip', 'doc', 'ppt', 'xls'],
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
        pkgCoverVideoResumable = resumable;

        pkgCoverVideoTaskId = createUploadTask(
            file.name,
            file.size,
            'Téléversement de la vidéo de couverture',
            {
                onCancel: () => window.clearPackageCoverVideo({ skipModalCancel: true }),
                cancelLabel: 'Annuler',
            }
        );

        resumable.on('fileProgress', function(resumableFile) {
            const percent = Math.max(0, Math.min(100, Math.round(resumableFile.progress() * 100)));
            if (progressBar) progressBar.style.width = percent + '%';
            updateUploadTask(pkgCoverVideoTaskId, percent, 'Téléversement en cours…');
        });

        const handleUploadError = (message, { suppressModal = false } = {}) => {
            const displayMessage = typeof message === 'string' && message.trim() !== ''
                ? message
                : 'Erreur lors du téléversement de la vidéo.';
            if (pkgCoverVideoTaskId) {
                if (suppressModal && window.UploadProgressModal && typeof window.UploadProgressModal.cancelTask === 'function') {
                    window.UploadProgressModal.cancelTask(pkgCoverVideoTaskId);
                } else {
                    errorUploadTask(pkgCoverVideoTaskId, displayMessage);
                }
                pkgCoverVideoTaskId = null;
            }
            pkgCoverVideoResumable = null;
            if (progressWrapper) progressWrapper.style.display = 'none';
            if (progressBar) progressBar.style.width = '0%';
            if (!suppressModal) showPackageCoverVideoError(displayMessage);
            zone.style.borderColor = '#dc3545';
            window.clearPackageCoverVideo({ skipModalCancel: true, preserveHidden: false, restoreExistingPreview: true });
        };

        resumable.on('fileSuccess', function(resumableFile, response) {
            let payload = response;
            if (typeof response === 'string') {
                try { payload = JSON.parse(response); } catch (e) { payload = null; }
            }
            if (!payload || !payload.path) {
                handleUploadError('Réponse invalide du serveur.');
                return;
            }
            const hidden = getPackageCoverVideoHiddenInputs();
            const previousPath = hidden.path ? hidden.path.value : '';
            if (hidden.path) hidden.path.value = payload.path;
            if (hidden.name) hidden.name.value = payload.filename || file.name;
            if (hidden.size) hidden.size.value = payload.size || file.size;
            if (previousPath && previousPath !== payload.path) {
                queueTemporaryDeletion(previousPath);
            }
            registerTemporaryPath(payload.path);
            if (pkgCoverVideoTaskId) {
                completeUploadTask(pkgCoverVideoTaskId, 'Vidéo importée avec succès');
                pkgCoverVideoTaskId = null;
            }
            pkgCoverVideoResumable = null;
            if (progressWrapper) progressWrapper.style.display = 'none';
            if (progressBar) progressBar.style.width = '0%';
            zone.style.borderColor = '#28a745';
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
            const cleared = resetPackageFileInput(input);
            if (cleared && cleared.id === 'packageCoverVideo') {
                cleared.addEventListener('change', function() { window.handlePackageCoverVideoUpload(this); });
            }
        });

        resumable.on('fileError', function(resumableFile, message) { handleUploadError(message); });
        resumable.on('error', function(message) { handleUploadError(message); });
        resumable.on('cancel', function() { handleUploadError('Téléversement annulé.', { suppressModal: true }); });
        resumable.on('chunkingComplete', function() {
            if (!resumable.isUploading()) resumable.upload();
        });
        resumable.addFile(file);
    }

    window.clearPackageCoverVideo = function(options = {}) {
        const { skipModalCancel = false, preserveHidden = false, restoreExistingPreview = true } = options;
        const zone = document.getElementById('packageCoverVideoUploadZone');
        if (!zone) return;
        const placeholder = zone.querySelector('.upload-placeholder');
        const preview = zone.querySelector('.upload-preview');
        let input = document.getElementById('packageCoverVideo');
        const errorDiv = document.getElementById('packageCoverVideoError');
        const progressWrapper = document.getElementById('packageCoverVideoProgress');
        const progressBar = progressWrapper ? progressWrapper.querySelector('.progress-bar') : null;

        if (pkgCoverVideoResumable) {
            try { pkgCoverVideoResumable.cancel(); } catch (e) {}
        }
        pkgCoverVideoResumable = null;

        if (pkgCoverVideoTaskId) {
            const progressModal = window.UploadProgressModal;
            if (!skipModalCancel && progressModal && typeof progressModal.cancelTask === 'function') {
                progressModal.cancelTask(pkgCoverVideoTaskId);
            }
            pkgCoverVideoTaskId = null;
        }

        if (!preserveHidden) resetPackageCoverVideoHiddenFields();

        revokePackageCoverVideoObjectUrl(zone);

        const video = preview ? preview.querySelector('video') : null;
        if (video) {
            try { video.pause(); } catch (e) {}
            video.removeAttribute('src');
            try { video.load(); } catch (e) {}
        }

        input = resetPackageFileInput(input);
        if (input && input.id === 'packageCoverVideo') {
            input.addEventListener('change', function() { window.handlePackageCoverVideoUpload(this); });
        }

        const fileNameBadge = preview ? preview.querySelector('.file-name') : null;
        const fileSizeBadge = preview ? preview.querySelector('.file-size') : null;
        if (fileNameBadge) fileNameBadge.textContent = '';
        if (fileSizeBadge) fileSizeBadge.textContent = '';
        if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
        }
        if (progressWrapper) progressWrapper.style.display = 'none';
        if (progressBar) progressBar.style.width = '0%';
        if (preview) preview.classList.add('d-none');
        if (placeholder) placeholder.classList.remove('d-none');
        zone.style.borderColor = '#dee2e6';

        if (restoreExistingPreview) {
            document.querySelectorAll('.current-package-cover-video').forEach((el) => el.classList.remove('d-none'));
        }
    };

    function assignFileToThumbnailInput(input, file) {
        if (!input || !file) return false;
        try {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            return true;
        } catch (e) {
            return false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const zone = document.getElementById('packageThumbnailUploadZone');
        const input = document.getElementById('packageThumbnail');
        const form = document.getElementById('packageForm');
        if (form) {
            form.addEventListener('submit', function() {
                TempUploadManager.markSubmitting();
            });
        }
        if (!zone || !input) return;

        ['dragenter', 'dragover'].forEach((ev) => {
            zone.addEventListener(ev, function(e) {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.add('is-dragover');
            });
        });
        zone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (!zone.contains(e.relatedTarget)) zone.classList.remove('is-dragover');
        });
        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            zone.classList.remove('is-dragover');
            const f = e.dataTransfer?.files?.[0];
            if (!f) return;
            if (assignFileToThumbnailInput(input, f)) {
                window.handlePackageThumbnailUpload(input);
            }
        });

        const coverZone = document.getElementById('packageCoverVideoUploadZone');
        const coverInput = document.getElementById('packageCoverVideo');
        if (coverZone && coverInput) {
            ['dragenter', 'dragover'].forEach((ev) => {
                coverZone.addEventListener(ev, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    coverZone.classList.add('is-dragover');
                });
            });
            coverZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (!coverZone.contains(e.relatedTarget)) coverZone.classList.remove('is-dragover');
            });
            coverZone.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                coverZone.classList.remove('is-dragover');
                const f = e.dataTransfer?.files?.[0];
                if (!f) return;
                if (assignFileToThumbnailInput(coverInput, f)) {
                    window.handlePackageCoverVideoUpload(coverInput);
                }
            });
        }

        document.querySelectorAll('[data-temp-upload-cancel]').forEach((cancelLink) => {
            cancelLink.addEventListener('click', function(event) {
                event.preventDefault();
                const href = this.getAttribute('href');
                TempUploadManager.flushAll({ keepalive: true });
                const navigate = () => { window.location.href = href; };
                if (navigator.sendBeacon) {
                    setTimeout(navigate, 50);
                } else {
                    setTimeout(navigate, 0);
                }
            });
        });
    });

    if (!window.__pkgTempUploadUnloadHook) {
        window.__pkgTempUploadUnloadHook = true;
        window.addEventListener('beforeunload', function() {
            if (TempUploadManager && !TempUploadManager.isSubmitting()) {
                TempUploadManager.flushAll({ keepalive: true });
            }
        });
    }
})();
</script>
@endpush

@push('styles')
<style>
.package-upload-zone.upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    background-color: #f8f9fa;
    transition: border-color 0.2s ease, background-color 0.2s ease;
    overflow: hidden;
}
.package-upload-zone.upload-zone:hover,
.package-upload-zone.upload-zone.is-dragover {
    border-color: #0b1f3a;
    background-color: #e9ecef;
}
.package-upload-zone .upload-placeholder {
    cursor: pointer;
}
.package-upload-zone .upload-preview {
    padding: 1rem;
}
</style>
@endpush
