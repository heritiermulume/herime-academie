@push('styles')
<style>
.package-upload-zone.upload-zone.announcement-image-upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    background-color: #f8f9fa;
    transition: border-color 0.2s ease, background-color 0.2s ease;
    overflow: hidden;
}
.package-upload-zone.upload-zone.announcement-image-upload-zone:hover,
.package-upload-zone.upload-zone.announcement-image-upload-zone.is-dragover {
    border-color: #0b1f3a;
    background-color: #e9ecef;
}
.package-upload-zone.upload-zone.announcement-image-upload-zone .upload-placeholder {
    cursor: default;
}
</style>
@endpush

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

    function getHidden(suffix) {
        return {
            path: document.getElementById('announcement_image_chunk_path_' + suffix),
            name: document.getElementById('announcement_image_chunk_name_' + suffix),
            size: document.getElementById('announcement_image_chunk_size_' + suffix),
        };
    }

    function resetHiddenFields(suffix) {
        const hidden = getHidden(suffix);
        if (!hidden.path) return;
        const previousPath = hidden.path.value;
        if (previousPath && isTemporaryPath(previousPath)) {
            queueTemporaryDeletion(previousPath);
        }
        hidden.path.value = '';
        if (hidden.name) hidden.name.value = '';
        if (hidden.size) hidden.size.value = '';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.min(Math.floor(Math.log(bytes) / Math.log(k)), sizes.length - 1);
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    function showError(suffix, message) {
        const errorDiv = document.getElementById('announcementImageError_' + suffix);
        if (!errorDiv) return;
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    function hideError(suffix) {
        const errorDiv = document.getElementById('announcementImageError_' + suffix);
        if (!errorDiv) return;
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';
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

    function createUploadTask(fileName, fileSize, description, extra) {
        if (!window.UploadProgressModal) return null;
        const taskId = 'ann-img-' + Date.now() + '-' + Math.random().toString(16).slice(2, 10);
        const baseConfig = {
            label: fileName,
            description,
            sizeLabel: formatFileSize(fileSize),
            initialMessage: 'Préparation du téléversement…',
        };
        const taskConfig = Object.assign({}, baseConfig, extra || {});
        if (typeof taskConfig.onCancel === 'function' && typeof taskConfig.cancelable === 'undefined') {
            taskConfig.cancelable = true;
        }
        window.UploadProgressModal.startTask(taskId, taskConfig);
        return taskId;
    }

    const resumableBySuffix = { create: null, edit: null };
    const taskIdBySuffix = { create: null, edit: null };

    function assignFileToInput(input, file) {
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

    window.resetAnnouncementImageChunk = function(suffix) {
        const zone = document.getElementById('announcementImageUploadZone_' + suffix);
        if (!zone) return;
        const placeholder = zone.querySelector('.upload-placeholder');
        const preview = zone.querySelector('.upload-preview');
        let input = document.getElementById('announcementImageInput_' + suffix);

        if (resumableBySuffix[suffix]) {
            try { resumableBySuffix[suffix].cancel(); } catch (e) {}
        }
        resumableBySuffix[suffix] = null;

        if (taskIdBySuffix[suffix]) {
            const progressModal = window.UploadProgressModal;
            if (progressModal && typeof progressModal.cancelTask === 'function') {
                progressModal.cancelTask(taskIdBySuffix[suffix]);
            }
            taskIdBySuffix[suffix] = null;
        }

        resetHiddenFields(suffix);

        input = resetFileInput(input);
        if (input && input.id === 'announcementImageInput_' + suffix) {
            input.addEventListener('change', function() { window.handleAnnouncementImageChunkUpload(suffix, this); });
        }

        const img = preview.querySelector('img');
        if (img) img.src = '';
        const fn = preview.querySelector('.file-name');
        const fs = preview.querySelector('.file-size');
        if (fn) fn.textContent = '';
        if (fs) fs.textContent = '';
        preview.classList.add('d-none');
        placeholder.classList.remove('d-none');
        zone.style.borderColor = '';
        hideError(suffix);
    };

    window.handleAnnouncementImageChunkUpload = function(suffix, input) {
        const zone = document.getElementById('announcementImageUploadZone_' + suffix);
        if (!zone || !input || !input.files || !input.files[0]) return;

        const file = input.files[0];
        hideError(suffix);

        if (!VALID_IMAGE_TYPES.includes(file.type)) {
            showError(suffix, 'Format invalide. Utilisez JPG, PNG, WEBP ou GIF.');
            resetFileInput(input);
            return;
        }
        if (file.size > MAX_IMAGE_SIZE) {
            showError(suffix, 'Le fichier est trop volumineux. Maximum 5 Mo.');
            resetFileInput(input);
            return;
        }
        if (typeof Resumable === 'undefined') {
            showError(suffix, 'Votre navigateur ne supporte pas l’upload fractionné.');
            resetFileInput(input);
            return;
        }

        resetHiddenFields(suffix);

        const placeholder = zone.querySelector('.upload-placeholder');
        const preview = zone.querySelector('.upload-preview');
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = preview.querySelector('img');
            if (img) img.src = e.target.result;
            const fnEl = preview.querySelector('.file-name');
            const fsEl = preview.querySelector('.file-size');
            if (fnEl) fnEl.textContent = file.name;
            if (fsEl) fsEl.textContent = formatFileSize(file.size);
            placeholder.classList.add('d-none');
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);

        zone.style.borderColor = '#0d6efd';
        startChunkUpload(suffix, file, input);
    };

    function startChunkUpload(suffix, file, input) {
        const token = getCsrfToken();
        const zone = document.getElementById('announcementImageUploadZone_' + suffix);
        if (!token) {
            showError(suffix, 'Impossible de récupérer le jeton CSRF.');
            resetFileInput(input);
            return;
        }

        if (resumableBySuffix[suffix]) {
            try { resumableBySuffix[suffix].cancel(); } catch (e) {}
            resumableBySuffix[suffix] = null;
        }
        if (taskIdBySuffix[suffix] && window.UploadProgressModal) {
            window.UploadProgressModal.errorTask(taskIdBySuffix[suffix], 'Téléversement annulé');
            taskIdBySuffix[suffix] = null;
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
                upload_type: 'announcement_image',
                original_name: file.name,
            }),
        });
        resumableBySuffix[suffix] = resumable;

        taskIdBySuffix[suffix] = createUploadTask(
            file.name,
            file.size,
            'Téléversement de l’image d’annonce',
            {
                onCancel: () => window.resetAnnouncementImageChunk(suffix),
                cancelLabel: 'Annuler',
            }
        );

        resumable.on('fileProgress', function(resumableFile) {
            const percent = Math.max(0, Math.min(100, Math.round(resumableFile.progress() * 100)));
            if (taskIdBySuffix[suffix] && window.UploadProgressModal) {
                window.UploadProgressModal.updateTask(taskIdBySuffix[suffix], percent, 'Téléversement en cours…');
            }
        });

        const handleUploadError = (message, suppressModal) => {
            const displayMessage = typeof message === 'string' && message.trim() !== ''
                ? message
                : 'Erreur lors du téléversement.';
            if (taskIdBySuffix[suffix]) {
                if (suppressModal && window.UploadProgressModal && typeof window.UploadProgressModal.cancelTask === 'function') {
                    window.UploadProgressModal.cancelTask(taskIdBySuffix[suffix]);
                } else if (window.UploadProgressModal) {
                    window.UploadProgressModal.errorTask(taskIdBySuffix[suffix], displayMessage);
                }
                taskIdBySuffix[suffix] = null;
            }
            resumableBySuffix[suffix] = null;
            if (!suppressModal) showError(suffix, displayMessage);
            zone.style.borderColor = '#dc3545';
            window.resetAnnouncementImageChunk(suffix);
        };

        resumable.on('fileSuccess', function(resumableFile, response) {
            let payload = response;
            if (typeof response === 'string') {
                try { payload = JSON.parse(response); } catch (e) { payload = null; }
            }
            if (!payload || !payload.path) {
                handleUploadError('Réponse invalide du serveur.', false);
                return;
            }
            const hidden = getHidden(suffix);
            const previousPath = hidden.path ? hidden.path.value : '';
            if (hidden.path) hidden.path.value = payload.path;
            if (hidden.name) hidden.name.value = payload.filename || file.name;
            if (hidden.size) hidden.size.value = payload.size || file.size;
            if (previousPath && previousPath !== payload.path) queueTemporaryDeletion(previousPath);
            registerTemporaryPath(payload.path);
            if (taskIdBySuffix[suffix] && window.UploadProgressModal) {
                window.UploadProgressModal.completeTask(taskIdBySuffix[suffix], 'Image importée');
                taskIdBySuffix[suffix] = null;
            }
            resumableBySuffix[suffix] = null;
            zone.style.borderColor = '#28a745';
            hideError(suffix);
            const cleared = resetFileInput(input);
            if (cleared && cleared.id === 'announcementImageInput_' + suffix) {
                cleared.addEventListener('change', function() { window.handleAnnouncementImageChunkUpload(suffix, this); });
            }
        });

        resumable.on('fileError', function() { handleUploadError('Erreur réseau ou serveur.', false); });
        resumable.on('error', function() { handleUploadError('Erreur lors du téléversement.', false); });
        resumable.on('cancel', function() { handleUploadError('Téléversement annulé.', true); });
        resumable.on('chunkingComplete', function() {
            if (!resumable.isUploading()) resumable.upload();
        });
        resumable.addFile(file);
    }

    document.addEventListener('DOMContentLoaded', function() {
        ['create', 'edit'].forEach(function(suffix) {
            const zone = document.getElementById('announcementImageUploadZone_' + suffix);
            const input = document.getElementById('announcementImageInput_' + suffix);
            if (!zone || !input) return;

            input.addEventListener('change', function() {
                window.handleAnnouncementImageChunkUpload(suffix, this);
            });

            ['dragenter', 'dragover'].forEach(function(ev) {
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
                if (assignFileToInput(input, f)) {
                    window.handleAnnouncementImageChunkUpload(suffix, input);
                }
            });

            const clearBtn = zone.querySelector('.announcement-image-chunk-clear');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    window.resetAnnouncementImageChunk(suffix);
                });
            }
        });

        const createForm = document.querySelector('#createAnnouncementModal form');
        const editForm = document.getElementById('editAnnouncementForm');
        [createForm, editForm].forEach(function(form) {
            if (!form) return;
            form.addEventListener('submit', function() {
                TempUploadManager.markSubmitting();
            });
        });
    });

    if (!window.__announcementTempUploadUnloadHook) {
        window.__announcementTempUploadUnloadHook = true;
        window.addEventListener('beforeunload', function() {
            if (TempUploadManager && !TempUploadManager.isSubmitting()) {
                TempUploadManager.flushAll({ keepalive: true });
            }
        });
    }
})();
</script>
@endpush
