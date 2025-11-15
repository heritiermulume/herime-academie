@php
    $existingSections = $existingSections ?? [];
    $enableCourseBuilder = $enableCourseBuilder ?? false;
@endphp

@push('scripts')
    @once
        <script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
    @endonce
    <script>
        const MAX_THUMBNAIL_SIZE = 5 * 1024 * 1024; // 5MB
        const MAX_PREVIEW_VIDEO_SIZE = 10 * 1024 * 1024 * 1024; // 10GB
        const LESSON_FILE_MAX_SIZE = 10 * 1024 * 1024 * 1024; // 10GB
        const CHUNK_SIZE_BYTES = 1 * 1024 * 1024; // 1MB (aligné avec post_max_size local)
        @php
            $chunkRouteName = request()->routeIs('admin.*')
                ? (Route::has('admin.uploads.chunk') ? 'admin.uploads.chunk' : 'instructor.uploads.chunk')
                : 'instructor.uploads.chunk';
        @endphp
        const CHUNK_UPLOAD_ENDPOINT = (function() {
            const origin = window.location.origin.replace(/\/+$/, '');
            const path = "{{ trim(parse_url(route($chunkRouteName), PHP_URL_PATH), '/') }}";
            return `${origin}/${path}`;
        })();
        const ENABLE_COURSE_BUILDER = {{ $enableCourseBuilder ? 'true' : 'false' }};
        const existingSections = @json($existingSections);
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

        const isTemporaryPath = (path) => {
            const prefix = window.__tempUploadConfig?.prefix;
            return typeof path === 'string' && prefix && path.startsWith(prefix);
        };

        const queueTemporaryDeletion = (path) => {
            TempUploadManager.queueDelete(path);
        };

        const registerTemporaryPath = (path) => {
            TempUploadManager.register(path);
        };

        const formatBytes = (bytes) => {
            if (!bytes && bytes !== 0) {
                return '';
            }
            if (bytes === 0) {
                return '0 o';
            }
            const units = ['o', 'Ko', 'Mo', 'Go', 'To'];
            const index = Math.floor(Math.log(bytes) / Math.log(1024));
            const value = bytes / Math.pow(1024, index);
            return `${value.toFixed(value >= 10 || index === 0 ? 0 : 1)} ${units[index]}`;
        };

        const UploadTaskManager = (() => {
            const randomId = () => `upload-${Date.now()}-${Math.random().toString(16).slice(2, 10)}`;
            const getModal = () => (typeof window !== 'undefined' ? window.UploadProgressModal : null);

            return {
                startTask(fileName, fileSize, description, initialMessage = 'Préparation du téléversement…') {
                    const taskId = randomId();
                    const modal = getModal();
                    if (modal) {
                        modal.startTask(taskId, {
                            label: fileName,
                            description,
                            sizeLabel: formatBytes(fileSize),
                            initialMessage,
                        });
                    }
                    return taskId;
                },
                update(taskId, percent, message) {
                    const modal = getModal();
                    if (modal && taskId) {
                        modal.updateTask(taskId, percent, message);
                    }
                },
                setIndeterminate(taskId) {
                    const modal = getModal();
                    if (modal && taskId) {
                        modal.setIndeterminate(taskId);
                    }
                },
                complete(taskId, message = 'Téléversement terminé') {
                    const modal = getModal();
                    if (modal && taskId) {
                        modal.completeTask(taskId, message);
                    }
                },
                error(taskId, message = 'Erreur lors du téléversement') {
                    const modal = getModal();
                    if (modal && taskId) {
                        modal.errorTask(taskId, message);
                    }
                },
                cancel(taskId) {
                    const modal = getModal();
                    if (modal && taskId) {
                        modal.cancelTask(taskId);
                    }
                },
            };
        })();

        const toggleHidden = (element, shouldHide = true) => {
            if (!element) {
                return;
            }
            element.classList.toggle('is-hidden', shouldHide);
        };

        const assignFileToInput = (input, file, fileList = null) => {
            if (!input) {
                return false;
            }

            if (fileList && fileList.length) {
                try {
                    input.files = fileList;
                    return true;
                } catch (error) {
                    // Continue to fallback
                }
            }

            if (!file) {
                return false;
            }

            if (typeof DataTransfer !== 'undefined') {
                try {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
                    return true;
                } catch (error) {
                    // ignored
                }
            }

            return false;
        };

        const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const ChunkUpload = {
            isSupported() {
                return typeof Resumable !== 'undefined';
            },

            upload({ file, type = 'lesson', metadata = {}, onProgress, onSuccess, onError }) {
                if (!this.isSupported()) {
                    onError?.('Votre navigateur ne supporte pas l’upload fractionné. Veuillez le mettre à jour.');
                    return null;
                }

                const normalizeError = (rawMessage) => {
                    if (!rawMessage) {
                        return 'Erreur lors du téléversement.';
                    }

                    if (typeof rawMessage === 'string') {
                        try {
                            const parsed = JSON.parse(rawMessage);
                            return parsed?.message ?? parsed?.error ?? rawMessage;
                        } catch (error) {
                            return rawMessage;
                        }
                    }

                    if (rawMessage?.message) {
                        return rawMessage.message;
                    }

                    return 'Erreur lors du téléversement.';
                };

                const resumable = new Resumable({
                    target: CHUNK_UPLOAD_ENDPOINT,
                    chunkSize: CHUNK_SIZE_BYTES,
                    simultaneousUploads: 3,
                    testChunks: false,
                    throttleProgressCallbacks: 1,
                    fileParameterName: 'file',
                    fileType: [
                        'mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'mkv',
                        'pdf', 'zip', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'
                    ],
                    withCredentials: true,
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    query: () => ({
                        upload_type: type,
                        original_name: file.name,
                        ...metadata,
                    }),
                });

                resumable.on('fileProgress', (resumableFile) => {
                    const percent = Math.max(0, Math.min(100, Math.round(resumableFile.progress() * 100)));
                    onProgress?.(percent);
                });

                resumable.on('fileSuccess', (_resumableFile, response) => {
                    let payload = null;
                    try {
                        payload = typeof response === 'string' ? JSON.parse(response) : response;
                    } catch (error) {
                        payload = null;
                    }

                    if (!payload || !payload.path) {
                        onError?.('La réponse du serveur est invalide. Veuillez réessayer.');
                        resumable.cancel();
                        return;
                    }

                    onSuccess?.(payload);
                    resumable.cancel();
                });

                const handleError = (message) => {
                    onError?.(normalizeError(message));
                    resumable.cancel();
                };

                resumable.on('fileError', (_resumableFile, message) => handleError(message));
        resumable.on('error', (message) => handleError(message));

                resumable.addFile(file);

                return {
                    cancel() {
                        resumable.cancel();
                    },
                };
            },
        };

        const CreateCourseForm = {
            templates: {
                requirements: () => CreateCourseForm.buildItem('requirements[]', 'Ex : Connaissances de base en informatique'),
                learnings: () => CreateCourseForm.buildItem('what_you_will_learn[]', 'Ex : Déployer une API Laravel'),
                tags: () => CreateCourseForm.buildItem('tags[]', 'Ex : backend', true)
            },

            addItem(type) {
                const listId = `${type}-list`;
                const list = document.getElementById(listId);
                if (!list) return;

                const builder = this.templates[type];
                if (!builder) return;

                const item = builder();
                list.appendChild(item);
                const input = item.querySelector('input');
                if (input) {
                    input.focus();
                }
            },

            removeItem(button) {
                const item = button.closest('.create-course__list-item');
                if (!item) return;

                const list = item.parentElement;
                item.remove();

                const type = list.dataset.type;
                const hasItems = list.querySelectorAll('.create-course__list-item').length;
                if (!hasItems && type && this.templates[type]) {
                    list.appendChild(this.templates[type]());
                }
            },

            buildItem(name, placeholder, isChip = false) {
                const wrapper = document.createElement('div');
                wrapper.className = 'create-course__list-item' + (isChip ? ' create-course__list-item--chip' : '');
                wrapper.innerHTML = `
                <input type="text" name="${name}" placeholder="${placeholder}">
                <button type="button" class="btn btn-link text-danger" aria-label="Supprimer">
                    <i class="fas fa-times"></i>
                </button>
            `;
                wrapper.querySelector('button').addEventListener('click', (event) => {
                    event.preventDefault();
                    CreateCourseForm.removeItem(event.currentTarget);
                });
                return wrapper;
            }
        };

        const CreateCourseBuilder = {
            sectionCounter: 0,
            container: null,
            emptyState: null,

            init(existingSections = []) {
                this.container = document.getElementById('course-structure-sections');
                this.emptyState = document.getElementById('course-structure-empty');

                if (!this.container) {
                    return;
                }

                const sectionsArray = Array.isArray(existingSections)
                    ? existingSections
                    : Object.values(existingSections || {});

                if (sectionsArray.length) {
                    sectionsArray.forEach(section => this.addSection(section || {}));
                } else {
                    this.addSection();
                }

                this.toggleEmptyState();
            },

            addSection(data = {}) {
                const sectionIndex = this.sectionCounter++;

                const sectionEl = document.createElement('div');
                sectionEl.className = 'course-structure__section';
                sectionEl.dataset.sectionIndex = sectionIndex;
                sectionEl.dataset.lessonCounter = 0;

                sectionEl.innerHTML = `
                <div class="course-structure__section-header">
                    <div class="course-structure__section-title">
                        <span class="course-structure__section-index"></span>
                        <input type="text" name="sections[${sectionIndex}][title]" class="course-structure__section-input" placeholder="Titre de la section" required>
                    </div>
                    <button type="button" class="btn btn-link text-danger" data-action="remove-section" aria-label="Supprimer la section">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="course-structure__section-body">
                    <label class="course-structure__field">
                        <span>Description (optionnel)</span>
                        <textarea name="sections[${sectionIndex}][description]" rows="2" placeholder="Décrivez les notions abordées dans cette section"></textarea>
                    </label>
                    <div class="course-structure__lessons" data-lessons></div>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-action="add-lesson">
                        <i class="fas fa-plus me-1"></i>Ajouter une leçon
                    </button>
                </div>
            `;

                this.container.appendChild(sectionEl);

                sectionEl.querySelector('[data-action="remove-section"]').addEventListener('click', () => {
                    this.removeSection(sectionEl);
                });

                sectionEl.querySelector('[data-action="add-lesson"]').addEventListener('click', () => {
                    this.addLesson(sectionEl);
                });

                const titleInput = sectionEl.querySelector(`[name="sections[${sectionIndex}][title]"]`);
                if (titleInput && data.title) {
                    titleInput.value = data.title;
                }

                const descriptionTextarea = sectionEl.querySelector(`[name="sections[${sectionIndex}][description]"]`);
                if (descriptionTextarea && data.description) {
                    descriptionTextarea.value = data.description;
                }

                const lessonsData = Array.isArray(data.lessons)
                    ? data.lessons
                    : Object.values(data.lessons || {});

                if (lessonsData.length) {
                    lessonsData.forEach(lesson => this.addLesson(sectionEl, lesson || {}));
                } else {
                    this.addLesson(sectionEl);
                }

                this.refreshSectionOrder();
                this.toggleEmptyState();
            },

            removeSection(sectionEl) {
                sectionEl.remove();
                this.refreshSectionOrder();
                this.toggleEmptyState();

                if (!this.container.querySelector('.course-structure__section')) {
                    this.addSection();
                }
            },

            addLesson(sectionEl, data = {}) {
                const lessonsContainer = sectionEl.querySelector('[data-lessons]');
                if (!lessonsContainer) {
                    return;
                }

                const sectionIndex = sectionEl.dataset.sectionIndex;
                const lessonIndex = parseInt(sectionEl.dataset.lessonCounter || '0', 10);
                sectionEl.dataset.lessonCounter = lessonIndex + 1;

                const lessonEl = document.createElement('div');
                lessonEl.className = 'course-structure__lesson';
                lessonEl.innerHTML = `
                <div class="course-structure__lesson-header">
                    <strong class="course-structure__lesson-index"></strong>
                    <button type="button" class="btn btn-link text-danger" data-action="remove-lesson" aria-label="Supprimer la leçon">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="course-structure__lesson-grid">
                    <div class="course-structure__field">
                        <span>Titre de la leçon <span class="required">*</span></span>
                        <input type="text" name="sections[${sectionIndex}][lessons][${lessonIndex}][title]" placeholder="Ex : Introduction au module" required>
                    </div>
                    <div class="course-structure__lesson-meta">
                        <div class="course-structure__field">
                            <span>Type <span class="required">*</span></span>
                            <select name="sections[${sectionIndex}][lessons][${lessonIndex}][type]" required>
                                <option value="">Sélectionner</option>
                                <option value="video">Vidéo</option>
                                <option value="text">Texte</option>
                                <option value="quiz">Quiz</option>
                                <option value="assignment">Devoir</option>
                            </select>
                        </div>
                        <div class="course-structure__field">
                            <span>Durée (min)</span>
                            <input type="number" min="0" name="sections[${sectionIndex}][lessons][${lessonIndex}][duration]" placeholder="0">
                        </div>
                        <label class="course-structure__checkbox">
                            <input type="checkbox" name="sections[${sectionIndex}][lessons][${lessonIndex}][is_preview]" value="1">
                            <span>Proposer en aperçu gratuit</span>
                        </label>
                    </div>
                    <div class="course-structure__field">
                        <span>Description (optionnel)</span>
                        <textarea name="sections[${sectionIndex}][lessons][${lessonIndex}][description]" rows="2" placeholder="Décrivez brièvement le contenu de la leçon"></textarea>
                    </div>
                    <div class="course-structure__field">
                        <span>Contenu texte (optionnel)</span>
                        <textarea name="sections[${sectionIndex}][lessons][${lessonIndex}][content_text]" rows="3" placeholder="Ajoutez un support écrit ou un résumé de la leçon"></textarea>
                    </div>
                    <div class="course-structure__field">
                        <span>Lien externe (YouTube, Vimeo, Google Drive...)</span>
                        <input type="url" name="sections[${sectionIndex}][lessons][${lessonIndex}][content_url]" placeholder="https://...">
                    </div>
                    <div class="course-structure__file" data-role="lesson-dropzone">
                        <span>Fichier de la leçon</span>
                        <input type="file"
                               name="sections[${sectionIndex}][lessons][${lessonIndex}][content_file]"
                               class="course-structure__dropzone-input"
                               data-role="lesson-file"
                               accept="video/mp4,video/webm,video/ogg,application/pdf,application/zip,application/x-zip-compressed,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,application/x-rar-compressed,application/x-7z-compressed,application/x-tar,application/gzip">
                        <input type="hidden"
                               name="sections[${sectionIndex}][lessons][${lessonIndex}][content_file_path]"
                               data-role="lesson-file-path">
                        <input type="hidden"
                               name="sections[${sectionIndex}][lessons][${lessonIndex}][content_file_name]"
                               data-role="lesson-file-name">
                        <input type="hidden"
                               name="sections[${sectionIndex}][lessons][${lessonIndex}][content_file_size]"
                               data-role="lesson-file-size">
                        <div class="course-structure__dropzone" data-role="lesson-dropzone-box" tabindex="0">
                            <div class="course-structure__dropzone-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="course-structure__dropzone-body">
                                <div class="course-structure__dropzone-thumb is-hidden" data-role="lesson-file-thumb">
                                    <i class="fas fa-file"></i>
                                </div>
                                <div class="course-structure__dropzone-text">
                                    <span class="course-structure__dropzone-title" data-role="lesson-file-label">Glissez-déposez un fichier ou cliquez pour importer</span>
                                    <small>Formats : MP4, WEBM, PDF, ZIP, DOCX... (max. 1 Go)</small>
                                    <div class="course-structure__dropzone-actions">
                                        <button type="button" class="course-structure__dropzone-change is-hidden" data-role="lesson-file-change">Changer de fichier</button>
                                        <button type="button" class="course-structure__dropzone-clear is-hidden" data-role="lesson-file-clear">Retirer le fichier</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="course-structure__upload-progress is-hidden" data-role="lesson-upload-progress">
                            <div class="course-structure__upload-progress-track">
                                <div class="course-structure__upload-progress-bar" data-role="lesson-upload-progress-bar"></div>
                            </div>
                            <span class="course-structure__upload-progress-label" data-role="lesson-upload-progress-label">0%</span>
                        </div>
                        <span class="course-structure__error is-hidden" data-role="lesson-file-error"></span>
                    </div>
                </div>
            `;

                lessonsContainer.appendChild(lessonEl);
                lessonEl.dataset.lessonIndex = lessonIndex;

                const titleInput = lessonEl.querySelector(`[name="sections[${sectionIndex}][lessons][${lessonIndex}][title]"]`);
                if (titleInput && data.title) {
                    titleInput.value = data.title;
                }

                const typeSelect = lessonEl.querySelector(`[name="sections[${sectionIndex}][lessons][${lessonIndex}][type]"]`);
                if (typeSelect && data.type) {
                    typeSelect.value = data.type;
                }

                const durationInput = lessonEl.querySelector(`[name="sections[${sectionIndex}][lessons][${lessonIndex}][duration]"]`);
                if (durationInput && data.duration) {
                    durationInput.value = data.duration;
                }

                const isPreviewCheckbox = lessonEl.querySelector(`[name="sections[${sectionIndex}][lessons][${lessonIndex}][is_preview]"]`);
                if (isPreviewCheckbox && (data.is_preview === true || data.is_preview === 1 || data.is_preview === '1' || data.is_preview === 'on')) {
                    isPreviewCheckbox.checked = true;
                }

                const descriptionTextarea = lessonEl.querySelector(`[name="sections[${sectionIndex}][lessons][${lessonIndex}][description]"]`);
                if (descriptionTextarea && data.description) {
                    descriptionTextarea.value = data.description;
                }

                const contentText = lessonEl.querySelector(`[name="sections[${sectionIndex}][lessons][${lessonIndex}][content_text]"]`);
                if (contentText && data.content_text) {
                    contentText.value = data.content_text;
                }

                const contentUrlInput = lessonEl.querySelector(`[name="sections[${sectionIndex}][lessons][${lessonIndex}][content_url]"]`);
                if (contentUrlInput && data.content_url) {
                    contentUrlInput.value = data.content_url;
                }

                const contentPathInput = lessonEl.querySelector(`[name="sections[${sectionIndex}][lessons][${lessonIndex}][content_file_path]"]`);
                if (contentPathInput && (data.content_file_path || data.file_path)) {
                    contentPathInput.value = data.content_file_path ?? data.file_path;
                }

                const contentNameInput = lessonEl.querySelector(`[name="sections[${sectionIndex}][lessons][${lessonIndex}][content_file_name]"]`);
                if (contentNameInput && data.content_file_name) {
                    contentNameInput.value = data.content_file_name;
                }

                const contentSizeInput = lessonEl.querySelector(`[name="sections[${sectionIndex}][lessons][${lessonIndex}][content_file_size]"]`);
                if (contentSizeInput && data.content_file_size) {
                    contentSizeInput.value = data.content_file_size;
                }

                lessonEl.querySelector('[data-action="remove-lesson"]').addEventListener('click', () => {
                    this.removeLesson(sectionEl, lessonEl);
                });

                this.setupLessonDropzone(lessonEl);
                this.refreshLessonOrder(sectionEl);
            },

            removeLesson(sectionEl, lessonEl) {
                const lessonsContainer = sectionEl.querySelector('[data-lessons]');
                lessonEl.remove();

                if (!lessonsContainer.querySelector('.course-structure__lesson')) {
                    this.addLesson(sectionEl);
                }

                this.refreshLessonOrder(sectionEl);
            },

            setupLessonDropzone(lessonEl) {
                const dropzone = lessonEl.querySelector('[data-role="lesson-dropzone-box"]');
                const fileInput = lessonEl.querySelector('[data-role="lesson-file"]');
                const label = lessonEl.querySelector('[data-role="lesson-file-label"]');
                const thumbEl = lessonEl.querySelector('[data-role="lesson-file-thumb"]');
                const changeBtn = lessonEl.querySelector('[data-role="lesson-file-change"]');
                const clearBtn = lessonEl.querySelector('[data-role="lesson-file-clear"]');
                const errorEl = lessonEl.querySelector('[data-role="lesson-file-error"]');
                const urlInput = lessonEl.querySelector('[name$="[content_url]"]');
                const pathInput = lessonEl.querySelector('[data-role="lesson-file-path"]');
                const nameInput = lessonEl.querySelector('[data-role="lesson-file-name"]');
                const sizeInput = lessonEl.querySelector('[data-role="lesson-file-size"]');
                const progressContainer = lessonEl.querySelector('[data-role="lesson-upload-progress"]');
                const progressBar = lessonEl.querySelector('[data-role="lesson-upload-progress-bar"]');
                const progressLabel = lessonEl.querySelector('[data-role="lesson-upload-progress-label"]');
                const sectionEl = lessonEl.closest('.course-structure__section');
                const sectionIndex = sectionEl?.dataset.sectionIndex ?? '';
                const lessonIndex = lessonEl.dataset.lessonIndex ?? '';

                let previewUrl = null;
                let activeUpload = null;
                let uploadTaskId = null;

                if (!dropzone || !fileInput || !label) {
                    return;
                }

                const defaultLabel = label.dataset.defaultText || label.textContent || 'Glissez-déposez un fichier ou cliquez pour importer';
                label.dataset.defaultText = defaultLabel;

                const stopActiveUpload = () => {
                    if (activeUpload && typeof activeUpload.cancel === 'function') {
                        activeUpload.cancel();
                    }
                    activeUpload = null;
                    if (uploadTaskId) {
                        UploadTaskManager.cancel(uploadTaskId);
                        uploadTaskId = null;
                    }
                };

                const updateProgress = (percent = 0) => {
                    const normalized = Math.max(0, Math.min(100, Math.round(percent)));
                    if (progressBar) {
                        progressBar.style.width = `${normalized}%`;
                    }
                    if (progressLabel) {
                        progressLabel.textContent = `${normalized}%`;
                    }
                };

                const reset = ({ clearUrl = false, keepError = false, clearStored = true } = {}) => {
                    stopActiveUpload();

                    if (uploadTaskId) {
                        UploadTaskManager.cancel(uploadTaskId);
                        uploadTaskId = null;
                    }

                    try {
                        fileInput.value = '';
                    } catch (error) {
                        // ignore
                    }

                    dropzone.classList.remove('is-active', 'is-uploading', 'is-complete', 'has-file');
                    if (!keepError) {
                        dropzone.classList.remove('has-error');
                        toggleHidden(errorEl, true);
                    }

                    label.textContent = defaultLabel;
                    toggleHidden(changeBtn, true);
                    toggleHidden(clearBtn, true);

                    if (previewUrl) {
                        URL.revokeObjectURL(previewUrl);
                        previewUrl = null;
                    }

                    if (thumbEl) {
                        thumbEl.innerHTML = '<i class="fas fa-file"></i>';
                        thumbEl.classList.add('is-hidden');
                        thumbEl.classList.remove('has-image');
                    }

                    if (clearUrl && urlInput) {
                        urlInput.value = '';
                    }

                    if (progressContainer) {
                        updateProgress(0);
                        toggleHidden(progressContainer, true);
                    }

                    if (clearStored) {
                        if (pathInput) {
                            const previousPath = pathInput.value;
                            if (previousPath && isTemporaryPath(previousPath)) {
                                queueTemporaryDeletion(previousPath);
                            }
                            pathInput.value = '';
                        }
                        if (nameInput) {
                            nameInput.value = '';
                        }
                        if (sizeInput) {
                            sizeInput.value = '';
                        }
                    }
                };

                const showError = (message) => {
                    if (errorEl) {
                        errorEl.textContent = message;
                        toggleHidden(errorEl, false);
                    }
                    dropzone.classList.add('has-error');
                    dropzone.classList.remove('is-uploading');
                };

                const clearError = () => {
                    if (errorEl) {
                        errorEl.textContent = '';
                        toggleHidden(errorEl, true);
                    }
                    dropzone.classList.remove('has-error');
                };

                const renderThumb = (file) => {
                    if (!thumbEl) {
                        return;
                    }
                    if (previewUrl) {
                        URL.revokeObjectURL(previewUrl);
                        previewUrl = null;
                    }
                    thumbEl.innerHTML = '<i class="fas fa-file"></i>';
                    thumbEl.classList.add('is-hidden');
                    thumbEl.classList.remove('has-image');

                    if (!file) {
                        return;
                    }

                    const type = file.type || '';
                    if (type.startsWith('image/')) {
                        previewUrl = URL.createObjectURL(file);
                        thumbEl.innerHTML = `<img src="${previewUrl}" alt="Aperçu du fichier sélectionné">`;
                        thumbEl.classList.add('has-image');
                    } else if (type.startsWith('video/')) {
                        thumbEl.innerHTML = '<i class="fas fa-file-video"></i>';
                    } else {
                        thumbEl.innerHTML = '<i class="fas fa-file"></i>';
                    }
                    thumbEl.classList.remove('is-hidden');
                };

                const applyStoredFile = ({ clearError: shouldClearError = true } = {}) => {
                    if (!pathInput || !pathInput.value) {
                        return;
                    }

                    if (shouldClearError) {
                        clearError();
                    }

                    dropzone.classList.add('has-file', 'is-complete');
                    toggleHidden(changeBtn, false);
                    toggleHidden(clearBtn, false);

                    if (progressContainer) {
                        toggleHidden(progressContainer, true);
                        updateProgress(100);
                    }

                    const storedName = nameInput && nameInput.value
                        ? nameInput.value
                        : pathInput.value.split('/').pop();
                    const storedSizeValue = sizeInput && sizeInput.value ? parseInt(sizeInput.value, 10) : null;
                    const storedSize = storedSizeValue ? formatBytes(storedSizeValue) : null;

                    label.textContent = storedSize ? `${storedName} (${storedSize})` : storedName;

                    if (thumbEl) {
                        thumbEl.innerHTML = '<i class="fas fa-file"></i>';
                        thumbEl.classList.remove('is-hidden');
                        thumbEl.classList.remove('has-image');
                    }
                };

                const startChunkUpload = (file, previousPath = null) => {
                    if (!ChunkUpload.isSupported()) {
                        showError('Votre navigateur ne supporte pas l’upload fractionné. Veuillez le mettre à jour.');
                        reset({ keepError: true, clearStored: false });
                        if (pathInput && pathInput.value) {
                            applyStoredFile({ clearError: false });
                        }
                        return;
                    }

                    if (uploadTaskId) {
                        UploadTaskManager.cancel(uploadTaskId);
                    }
                    uploadTaskId = UploadTaskManager.startTask(
                        file.name,
                        file.size,
                        'Téléversement du fichier de leçon'
                    );

                    dropzone.classList.add('is-uploading', 'has-file');
                    dropzone.classList.remove('is-complete');
                    toggleHidden(changeBtn, true);
                    toggleHidden(clearBtn, false);
                    if (progressContainer) {
                        toggleHidden(progressContainer, false);
                        updateProgress(0);
                    }

                    try {
                        fileInput.value = '';
                    } catch (error) {
                        // ignore
                    }

                    activeUpload = ChunkUpload.upload({
                        file,
                        type: 'lesson',
                        metadata: {
                            section_index: sectionIndex,
                            lesson_index: lessonIndex,
                            replace_path: previousPath,
                        },
                        onProgress: (percent) => {
                            updateProgress(percent);
                            UploadTaskManager.update(uploadTaskId, percent, 'Téléversement en cours…');
                        },
                        onSuccess: (payload) => {
                            activeUpload = null;
                            dropzone.classList.remove('is-uploading');
                            dropzone.classList.add('is-complete');
                            updateProgress(100);
                            toggleHidden(changeBtn, false);
                            toggleHidden(clearBtn, false);

                            const displayName = payload.filename || file.name;
                            const displaySize = formatBytes(payload.size || file.size);

                            label.textContent = `${displayName} (${displaySize})`;

                            const previousPath = pathInput ? pathInput.value : '';
                            if (pathInput) {
                                pathInput.value = payload.path;
                            }
                            if (nameInput) {
                                nameInput.value = displayName;
                            }
                            if (sizeInput) {
                                sizeInput.value = payload.size || file.size;
                            }
                            if (previousPath && previousPath !== payload.path) {
                                queueTemporaryDeletion(previousPath);
                            }
                            registerTemporaryPath(payload.path);
                            if (uploadTaskId) {
                                UploadTaskManager.complete(uploadTaskId, 'Fichier importé avec succès');
                                uploadTaskId = null;
                            }
                        },
                        onError: (message) => {
                            activeUpload = null;
                            const errorMessage = typeof message === 'string'
                                ? message
                                : (message?.message ?? 'Erreur lors du téléversement. Veuillez réessayer.');
                            showError(errorMessage);
                            reset({ keepError: true, clearStored: false });
                            if (pathInput && pathInput.value) {
                                applyStoredFile({ clearError: false });
                            }
                            if (uploadTaskId) {
                                UploadTaskManager.error(uploadTaskId, errorMessage);
                                uploadTaskId = null;
                            }
                        },
                    });
                };

                const handleFileSelection = (file) => {
                    if (!file) {
                        reset();
                        return;
                    }

                    if (file.size > LESSON_FILE_MAX_SIZE) {
                        showError(`Le fichier est trop volumineux (${formatBytes(file.size)}). Limite : ${formatBytes(LESSON_FILE_MAX_SIZE)}.`);
                        reset({ keepError: true });
                        return;
                    }

                    const previousPath = pathInput?.value || null;

                    clearError();
                    reset({ clearStored: false });

                    renderThumb(file);
                    label.textContent = `${file.name} (${formatBytes(file.size)})`;
                    dropzone.classList.add('has-file');

                    if (urlInput) {
                        urlInput.value = '';
                    }

                    startChunkUpload(file, previousPath);
                };

                dropzone.addEventListener('click', (event) => {
                    if (event.target.closest('[data-role="lesson-file-clear"]')) {
                        event.preventDefault();
                        event.stopPropagation();
                        return;
                    }
                    if (event.target.closest('[data-role="lesson-file-change"]')) {
                        event.preventDefault();
                        event.stopPropagation();
                        fileInput.click();
                        return;
                    }
                    event.preventDefault();
                    fileInput.click();
                });

                dropzone.addEventListener('keydown', (event) => {
                    if (event.key === ' ' || event.key === 'Enter') {
                        event.preventDefault();
                        fileInput.click();
                    }
                });

                dropzone.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    dropzone.classList.add('is-active');
                });

                dropzone.addEventListener('dragleave', () => {
                    dropzone.classList.remove('is-active');
                });

                dropzone.addEventListener('drop', (event) => {
                    event.preventDefault();
                    dropzone.classList.remove('is-active');
                    const fileList = event.dataTransfer?.files || null;
                    if (fileList && fileList.length > 1) {
                        showError('Veuillez sélectionner un seul fichier à la fois.');
                        return;
                    }
                    const file = fileList && fileList[0];
                    if (!file) {
                        return;
                    }
                    const assigned = assignFileToInput(fileInput, file, fileList);
                    if (!assigned) {
                        showError('Le glisser-déposer n’est pas pris en charge par ce navigateur. Utilisez le bouton pour sélectionner le fichier.');
                        return;
                    }
                    handleFileSelection(file);
                });

                fileInput.addEventListener('change', () => {
                    if (fileInput.files && fileInput.files.length > 1) {
                        showError('Veuillez sélectionner un seul fichier à la fois.');
                        reset({ keepError: true });
                        try {
                            fileInput.value = '';
                        } catch (error) {
                            // ignore
                        }
                        return;
                    }
                    const file = fileInput.files && fileInput.files[0];
                    handleFileSelection(file);
                });

                if (changeBtn) {
                    changeBtn.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        fileInput.click();
                    });
                }

                if (clearBtn) {
                    clearBtn.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        reset();
                        clearError();
                    });
                }

                if (urlInput) {
                    urlInput.addEventListener('input', () => {
                        if (urlInput.value.trim().length > 0) {
                            reset();
                            clearError();
                        }
                    });
                }

                reset({ clearStored: false });
                clearError();
                applyStoredFile();
            },

            refreshSectionOrder() {
                const sections = this.container ? Array.from(this.container.querySelectorAll('.course-structure__section')) : [];
                sections.forEach((section, index) => {
                    const badge = section.querySelector('.course-structure__section-index');
                    if (badge) {
                        badge.textContent = index + 1;
                    }
                });
            },

            refreshLessonOrder(sectionEl) {
                const lessons = Array.from(sectionEl.querySelectorAll('.course-structure__lesson'));
                lessons.forEach((lesson, index) => {
                    const indexLabel = lesson.querySelector('.course-structure__lesson-index');
                    if (indexLabel) {
                        indexLabel.textContent = `Leçon ${index + 1}`;
                    }
                });
            },

            toggleEmptyState() {
                if (!this.emptyState) {
                    return;
                }
                const hasSections = this.container && this.container.querySelector('.course-structure__section');
                this.emptyState.style.display = hasSections ? 'none' : 'flex';
            }
        };

        const setupMediaUpload = (container, { maxSize } = {}) => {
            if (!container) {
                return;
            }

            const dropzone = container.querySelector('[data-role="media-dropzone"]');
            const input = container.querySelector('[data-role="media-input"]');
            const placeholder = container.querySelector('[data-role="media-placeholder"]');
            const preview = container.querySelector('[data-role="media-preview"]');
            const filenameEl = container.querySelector('[data-role="media-filename"]');
            const thumbEl = container.querySelector('[data-role="media-thumb"]');
            const changeBtn = container.querySelector('[data-role="media-change"]');
            const clearBtn = container.querySelector('[data-role="media-clear"]');
            const errorEl = container.querySelector('[data-role="media-error"]');
            const pathInput = container.querySelector('[data-role="media-path"]');
            const nameInput = container.querySelector('[data-role="media-name"]');
            const sizeInput = container.querySelector('[data-role="media-size"]');
            const progressEl = container.querySelector('[data-role="media-progress"]');
            const progressBar = container.querySelector('[data-role="media-progress-bar"]');
            const progressLabel = container.querySelector('[data-role="media-progress-label"]');
            const isVideoUpload = container.dataset.media === 'video_preview';

            let previewUrl = null;
            let activeUpload = null;
            let uploadTaskId = null;

            if (!dropzone || !input) {
                return;
            }

            const stopActiveUpload = () => {
                if (activeUpload && typeof activeUpload.cancel === 'function') {
                    activeUpload.cancel();
                }
                activeUpload = null;
                if (uploadTaskId) {
                    UploadTaskManager.cancel(uploadTaskId);
                    uploadTaskId = null;
                }
            };

            const updateProgress = (percent = 0) => {
                const normalized = Math.max(0, Math.min(100, Math.round(percent)));
                if (progressBar) {
                    progressBar.style.width = `${normalized}%`;
                }
                if (progressLabel) {
                    progressLabel.textContent = `${normalized}%`;
                }
            };

            const reset = ({ keepError = false, clearStored = true } = {}) => {
                stopActiveUpload();

                if (uploadTaskId) {
                    UploadTaskManager.cancel(uploadTaskId);
                    uploadTaskId = null;
                }

                try {
                    input.value = '';
                } catch (error) {
                    // ignore
                }

                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = null;
                }

                dropzone.classList.remove('is-active', 'is-uploading', 'is-complete', 'has-file');
                if (!keepError) {
                    dropzone.classList.remove('has-error');
                    toggleHidden(errorEl, true);
                }

                toggleHidden(placeholder, false);
                toggleHidden(preview, true);
                toggleHidden(changeBtn, true);
                toggleHidden(clearBtn, true);

                if (thumbEl) {
                    thumbEl.innerHTML = '<i class="fas fa-file"></i>';
                    thumbEl.classList.remove('has-image');
                    thumbEl.classList.add('is-hidden');
                }

                if (filenameEl) {
                    filenameEl.textContent = '';
                }

                if (progressEl) {
                    updateProgress(0);
                    toggleHidden(progressEl, true);
                }

                if (clearStored) {
                    if (pathInput) {
                        const previousPath = pathInput.value;
                        if (previousPath && isTemporaryPath(previousPath)) {
                            queueTemporaryDeletion(previousPath);
                        }
                        pathInput.value = '';
                    }
                    if (nameInput) {
                        nameInput.value = '';
                    }
                    if (sizeInput) {
                        sizeInput.value = '';
                    }
                }
            };

            const showError = (message) => {
                if (errorEl) {
                    errorEl.textContent = message;
                    toggleHidden(errorEl, false);
                }
                dropzone.classList.add('has-error');
                dropzone.classList.remove('is-uploading');
            };

            const clearError = () => {
                if (errorEl) {
                    errorEl.textContent = '';
                    toggleHidden(errorEl, true);
                }
                dropzone.classList.remove('has-error');
            };

            const renderThumb = (file) => {
                if (!thumbEl) {
                    return;
                }
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = null;
                }
                thumbEl.innerHTML = '<i class="fas fa-file"></i>';
                thumbEl.classList.remove('has-image');
                thumbEl.classList.add('is-hidden');

                if (!file) {
                    return;
                }

                const type = file.type || '';
                if (type.startsWith('image/')) {
                    previewUrl = URL.createObjectURL(file);
                    thumbEl.innerHTML = `<img src="${previewUrl}" alt="Aperçu du fichier sélectionné">`;
                    thumbEl.classList.add('has-image');
                } else if (type.startsWith('video/')) {
                    thumbEl.innerHTML = '<i class="fas fa-file-video"></i>';
                } else {
                    thumbEl.innerHTML = '<i class="fas fa-file"></i>';
                }

                thumbEl.classList.remove('is-hidden');
            };

            const applyStoredMedia = ({ clearError: shouldClearError = true } = {}) => {
                if (!pathInput || !pathInput.value) {
                    return;
                }

                if (shouldClearError) {
                    clearError();
                }

                dropzone.classList.add('has-file', 'is-complete');
                toggleHidden(placeholder, true);
                toggleHidden(preview, false);
                toggleHidden(changeBtn, false);
                toggleHidden(clearBtn, false);

                if (progressEl) {
                    updateProgress(100);
                    toggleHidden(progressEl, true);
                }

                const storedName = nameInput && nameInput.value
                    ? nameInput.value
                    : pathInput.value.split('/').pop();
                const storedSizeValue = sizeInput && sizeInput.value ? parseInt(sizeInput.value, 10) : null;
                const storedSize = storedSizeValue ? formatBytes(storedSizeValue) : null;

                if (filenameEl) {
                    filenameEl.textContent = storedSize ? `${storedName} (${storedSize})` : storedName;
                }

                if (thumbEl) {
                    thumbEl.innerHTML = isVideoUpload
                        ? '<i class="fas fa-file-video"></i>'
                        : '<i class="fas fa-file-image"></i>';
                    thumbEl.classList.remove('is-hidden');
                    thumbEl.classList.remove('has-image');
                }
            };

            const startChunkUpload = (file, previousPath = null) => {
                if (!isVideoUpload) {
                    return;
                }

                if (!ChunkUpload.isSupported()) {
                    showError('Votre navigateur ne supporte pas l’upload fractionné. Veuillez le mettre à jour.');
                    reset({ keepError: true, clearStored: false });
                    if (pathInput && pathInput.value) {
                        applyStoredMedia({ clearError: false });
                    }
                    return;
                }

                if (uploadTaskId) {
                    UploadTaskManager.cancel(uploadTaskId);
                }
                uploadTaskId = UploadTaskManager.startTask(
                    file.name,
                    file.size,
                    'Téléversement de la vidéo de prévisualisation'
                );

                dropzone.classList.add('is-uploading', 'has-file');
                dropzone.classList.remove('is-complete');
                toggleHidden(changeBtn, true);
                toggleHidden(clearBtn, false);
                if (progressEl) {
                    toggleHidden(progressEl, false);
                    updateProgress(0);
                }

                try {
                    input.value = '';
                } catch (error) {
                    // ignore
                }

                activeUpload = ChunkUpload.upload({
                    file,
                    type: 'preview',
                    metadata: {
                        replace_path: previousPath,
                    },
                    onProgress: (percent) => {
                        updateProgress(percent);
                        UploadTaskManager.update(uploadTaskId, percent, 'Téléversement en cours…');
                    },
                    onSuccess: (payload) => {
                        activeUpload = null;
                        dropzone.classList.remove('is-uploading');
                        dropzone.classList.add('is-complete');
                        updateProgress(100);
                        toggleHidden(changeBtn, false);
                        toggleHidden(clearBtn, false);

                        const displayName = payload.filename || file.name;
                        const displaySize = formatBytes(payload.size || file.size);

                        if (filenameEl) {
                            filenameEl.textContent = `${displayName} (${displaySize})`;
                        }

                        const previousPath = pathInput ? pathInput.value : '';
                        if (pathInput) {
                            pathInput.value = payload.path;
                        }
                        if (nameInput) {
                            nameInput.value = displayName;
                        }
                        if (sizeInput) {
                            sizeInput.value = payload.size || file.size;
                        }
                        if (previousPath && previousPath !== payload.path) {
                            queueTemporaryDeletion(previousPath);
                        }
                        registerTemporaryPath(payload.path);
                        if (uploadTaskId) {
                            UploadTaskManager.complete(uploadTaskId, 'Vidéo importée avec succès');
                            uploadTaskId = null;
                        }
                    },
                    onError: (message) => {
                        activeUpload = null;
                        const errorMessage = typeof message === 'string'
                            ? message
                            : (message?.message ?? 'Erreur lors du téléversement. Veuillez réessayer.');
                        showError(errorMessage);
                        reset({ keepError: true, clearStored: false });
                        if (pathInput && pathInput.value) {
                            applyStoredMedia({ clearError: false });
                        }
                        if (uploadTaskId) {
                            UploadTaskManager.error(uploadTaskId, errorMessage);
                            uploadTaskId = null;
                        }
                    },
                });
            };

            const handleFile = (file) => {
                if (!file) {
                    reset();
                    return;
                }

                if (maxSize && file.size > maxSize) {
                    showError(`Le fichier est trop volumineux (${formatBytes(file.size)}). Limite : ${formatBytes(maxSize)}.`);
                    reset({ keepError: true, clearStored: !isVideoUpload });
                    return;
                }

                const previousPath = pathInput?.value || null;

                clearError();

                if (isVideoUpload) {
                    reset({ clearStored: false });
                } else {
                    reset();
                }

                toggleHidden(placeholder, true);
                toggleHidden(preview, false);
                renderThumb(file);

                if (filenameEl) {
                    filenameEl.textContent = `${file.name} (${formatBytes(file.size)})`;
                }

                dropzone.classList.add('has-file');
                toggleHidden(clearBtn, false);

                if (isVideoUpload) {
                    startChunkUpload(file, previousPath);
                } else {
                    toggleHidden(changeBtn, false);
                }
            };

            dropzone.addEventListener('click', (event) => {
                if (event.target.closest('[data-role="media-clear"]')) {
                    event.preventDefault();
                    event.stopPropagation();
                    return;
                }
                if (event.target.closest('[data-role="media-change"]')) {
                    event.preventDefault();
                    event.stopPropagation();
                    input.click();
                    return;
                }
                event.preventDefault();
                input.click();
            });

            dropzone.addEventListener('dragover', (event) => {
                event.preventDefault();
                dropzone.classList.add('is-active');
            });

            dropzone.addEventListener('dragleave', () => {
                dropzone.classList.remove('is-active');
            });

            dropzone.addEventListener('drop', (event) => {
                event.preventDefault();
                dropzone.classList.remove('is-active');
                const fileList = event.dataTransfer?.files || null;
                if (fileList && fileList.length > 1) {
                    showError('Veuillez sélectionner un seul fichier à la fois.');
                    return;
                }
                const file = fileList && fileList[0];
                if (!file) {
                    return;
                }
                const assigned = assignFileToInput(input, file, fileList);
                if (!assigned) {
                    showError('Le glisser-déposer n’est pas pris en charge par ce navigateur. Utilisez le bouton pour sélectionner le fichier.');
                    return;
                }
                handleFile(file);
            });

            dropzone.addEventListener('keydown', (event) => {
                if (event.key === ' ' || event.key === 'Enter') {
                    event.preventDefault();
                    input.click();
                }
            });

            input.addEventListener('change', () => {
                if (input.files && input.files.length > 1) {
                    showError('Veuillez sélectionner un seul fichier à la fois.');
                    reset({ keepError: true });
                    try {
                        input.value = '';
                    } catch (error) {
                        // ignore
                    }
                    return;
                }
                const file = input.files && input.files[0];
                handleFile(file);
            });

            if (changeBtn) {
                changeBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    input.click();
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    reset();
                    clearError();
                });
            }

            reset({ clearStored: false });
            clearError();
            applyStoredMedia();
        };

        document.addEventListener('DOMContentLoaded', () => {
            if (ENABLE_COURSE_BUILDER) {
                const addSectionBtn = document.getElementById('add-section-btn');
                if (addSectionBtn) {
                    addSectionBtn.addEventListener('click', () => CreateCourseBuilder.addSection());
                }

                CreateCourseBuilder.init(existingSections);
            }

            const thumbnailUpload = document.querySelector('[data-media="thumbnail"]');
            if (thumbnailUpload) {
                setupMediaUpload(thumbnailUpload, { maxSize: MAX_THUMBNAIL_SIZE });
            }

            const previewUpload = document.querySelector('[data-media="video_preview"]');
            if (previewUpload) {
                setupMediaUpload(previewUpload, { maxSize: MAX_PREVIEW_VIDEO_SIZE });
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('courseForm');
            if (form) {
                form.addEventListener('submit', () => {
                    TempUploadManager.markSubmitting();
                });
            }

            document.querySelectorAll('[data-temp-upload-cancel]').forEach((cancelLink) => {
                cancelLink.addEventListener('click', (event) => {
                    event.preventDefault();
                    const href = cancelLink.getAttribute('href');
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
            window.addEventListener('beforeunload', () => {
                if (TempUploadManager && !TempUploadManager.isSubmitting()) {
                    TempUploadManager.flushAll({ keepalive: true });
                }
            });
        }
    </script>
@endpush

