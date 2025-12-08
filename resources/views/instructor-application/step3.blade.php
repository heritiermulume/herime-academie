@extends('layouts.app')

@section('title', 'Candidature Formateur - Étape 3 - Herime Academie')

@include('partials.upload-progress-modal')

@section('content')
<!-- Header -->
<section class="page-header-section" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); padding: 2rem 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="h3 h2-md fw-bold mb-2">Candidature Formateur</h1>
                <p class="mb-0 small small-md">Étape 3 sur 3 - Documents à télécharger</p>
            </div>
        </div>
    </div>
</section>

<!-- Progress Bar -->
<section class="bg-light py-2 py-md-3">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1 mb-md-2" 
                             style="width: 35px; height: 35px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; font-size: 0.9rem;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="small fw-bold d-none d-md-block">Informations</div>
                        <div class="extra-small fw-bold d-md-none">Info</div>
                    </div>
                    <div class="flex-fill mx-1 mx-md-2">
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar" role="progressbar" style="width: 100%; background: linear-gradient(135deg, #003366 0%, #004080 100%);"></div>
                        </div>
                    </div>
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1 mb-md-2" 
                             style="width: 35px; height: 35px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; font-size: 0.9rem;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="small fw-bold d-none d-md-block">Spécialisations</div>
                        <div class="extra-small fw-bold d-md-none">Spéc.</div>
                    </div>
                    <div class="flex-fill mx-1 mx-md-2">
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar" role="progressbar" style="width: 50%; background: linear-gradient(135deg, #003366 0%, #004080 100%);"></div>
                        </div>
                    </div>
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1 mb-md-2" 
                             style="width: 35px; height: 35px; background: linear-gradient(135deg, #003366 0%, #004080 100%); color: white; font-weight: bold; font-size: 0.9rem;">
                            3
                        </div>
                        <div class="small fw-bold d-none d-md-block">Documents</div>
                        <div class="extra-small fw-bold d-md-none">Docs</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Form Section -->
<section class="page-content-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-3 p-md-5">
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Format accepté :</strong> PDF, DOC, DOCX (Maximum 5MB par fichier)
                        </div>

                        <form method="POST" action="{{ route('instructor-application.store-step3', $application) }}" enctype="multipart/form-data">
                            @csrf

                            <!-- CV Upload -->
                            <div class="mb-5">
                                <label class="form-label fw-bold">
                                    CV / Curriculum Vitae <span class="text-danger">*</span>
                                </label>
                                <div class="document-upload-wrapper">
                                    <input type="file"
                                           class="document-upload-input @error('cv') is-invalid @enderror"
                                           id="cv"
                                           name="cv"
                                           accept=".pdf,.doc,.docx"
                                           data-role="cv-file">
                                    <input type="hidden"
                                           name="cv_path"
                                           id="cv_path"
                                           value="{{ old('cv_path', $application->cv_path ?? '') }}"
                                           data-role="cv-path">
                                    <input type="hidden"
                                           name="cv_name"
                                           id="cv_name"
                                           value="{{ $application->cv_path ? basename($application->cv_path) : '' }}"
                                           data-role="cv-name">
                                    <input type="hidden"
                                           name="cv_size"
                                           id="cv_size"
                                           value=""
                                           data-role="cv-size">
                                    <div class="document-dropzone" data-role="cv-dropzone-box" tabindex="0">
                                        <div class="document-dropzone-icon">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                        </div>
                                        <div class="document-dropzone-body">
                                            <div class="document-dropzone-thumb is-hidden" data-role="cv-thumb">
                                                <i class="fas fa-file-pdf"></i>
                                            </div>
                                            <div class="document-dropzone-text">
                                                <span class="document-dropzone-title" data-role="cv-label">
                                                    @if($application->cv_path)
                                                        CV déjà téléchargé
                                                    @else
                                                        Glissez-déposez votre CV ou cliquez pour importer
                                                    @endif
                                                </span>
                                                <small>Format : PDF, DOC, DOCX (max. 5MB) - Requis</small>
                                                <div class="document-dropzone-actions">
                                                    <button type="button" class="document-dropzone-change is-hidden" data-role="cv-change">Changer de fichier</button>
                                                    <button type="button" class="document-dropzone-clear is-hidden" data-role="cv-clear">Retirer le fichier</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="document-upload-progress is-hidden" data-role="cv-upload-progress">
                                        <div class="document-upload-progress-track">
                                            <div class="document-upload-progress-bar" data-role="cv-upload-progress-bar"></div>
                                        </div>
                                        <span class="document-upload-progress-label" data-role="cv-upload-progress-label">0%</span>
                                    </div>
                                    <span class="document-error is-hidden" data-role="cv-error"></span>
                                    @if($application->cv_path)
                                        <div class="mt-2">
                                            <div class="alert alert-info d-flex align-items-center justify-content-between">
                                                <div>
                                                    <i class="fas fa-file-pdf me-2"></i>
                                                    <span>CV actuel : {{ basename($application->cv_path) }}</span>
                                                </div>
                                                <a href="{{ route('instructor-application.download-cv', $application) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-download me-1"></i>Voir
                                                </a>
                                            </div>
                                            <small class="text-muted">Vous pouvez télécharger un nouveau CV pour remplacer celui-ci.</small>
                                        </div>
                                    @endif
                                    @error('cv')
                                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Motivation Letter Upload -->
                            <div class="mb-5">
                                <label class="form-label fw-bold">
                                    Lettre de Motivation <span class="text-danger">*</span>
                                </label>
                                <div class="document-upload-wrapper">
                                    <input type="file"
                                           class="document-upload-input @error('motivation_letter') is-invalid @enderror"
                                           id="motivation_letter"
                                           name="motivation_letter"
                                           accept=".pdf,.doc,.docx"
                                           data-role="letter-file">
                                    <input type="hidden"
                                           name="motivation_letter_path"
                                           id="motivation_letter_path"
                                           value="{{ old('motivation_letter_path', $application->motivation_letter_path ?? '') }}"
                                           data-role="letter-path">
                                    <input type="hidden"
                                           name="motivation_letter_name"
                                           id="motivation_letter_name"
                                           value="{{ $application->motivation_letter_path ? basename($application->motivation_letter_path) : '' }}"
                                           data-role="letter-name">
                                    <input type="hidden"
                                           name="motivation_letter_size"
                                           id="motivation_letter_size"
                                           value=""
                                           data-role="letter-size">
                                    <div class="document-dropzone" data-role="letter-dropzone-box" tabindex="0">
                                        <div class="document-dropzone-icon">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                        </div>
                                        <div class="document-dropzone-body">
                                            <div class="document-dropzone-thumb is-hidden" data-role="letter-thumb">
                                                <i class="fas fa-file-pdf"></i>
                                            </div>
                                            <div class="document-dropzone-text">
                                                <span class="document-dropzone-title" data-role="letter-label">
                                                    @if($application->motivation_letter_path)
                                                        Lettre de motivation déjà téléchargée
                                                    @else
                                                        Glissez-déposez votre lettre de motivation ou cliquez pour importer
                                                    @endif
                                                </span>
                                                <small>Format : PDF, DOC, DOCX (max. 5MB) - Requis</small>
                                                <div class="document-dropzone-actions">
                                                    <button type="button" class="document-dropzone-change is-hidden" data-role="letter-change">Changer de fichier</button>
                                                    <button type="button" class="document-dropzone-clear is-hidden" data-role="letter-clear">Retirer le fichier</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="document-upload-progress is-hidden" data-role="letter-upload-progress">
                                        <div class="document-upload-progress-track">
                                            <div class="document-upload-progress-bar" data-role="letter-upload-progress-bar"></div>
                                        </div>
                                        <span class="document-upload-progress-label" data-role="letter-upload-progress-label">0%</span>
                                    </div>
                                    <span class="document-error is-hidden" data-role="letter-error"></span>
                                    @if($application->motivation_letter_path)
                                        <div class="mt-2">
                                            <div class="alert alert-info d-flex align-items-center justify-content-between">
                                                <div>
                                                    <i class="fas fa-file-pdf me-2"></i>
                                                    <span>Lettre actuelle : {{ basename($application->motivation_letter_path) }}</span>
                                                </div>
                                                <a href="{{ route('instructor-application.download-motivation-letter', $application) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-download me-1"></i>Voir
                                                </a>
                                            </div>
                                            <small class="text-muted">Vous pouvez télécharger une nouvelle lettre pour remplacer celle-ci.</small>
                                        </div>
                                    @endif
                                    @error('motivation_letter')
                                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mt-4 mt-md-5">
                                <a href="{{ route('instructor-application.step2', $application) }}" class="btn btn-outline-secondary order-2 order-md-1">
                                    <i class="fas fa-arrow-left me-2"></i>Précédent
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg px-3 px-md-5 order-1 order-md-2">
                                    <i class="fas fa-paper-plane me-2"></i>Soumettre ma candidature
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Responsive typography */
    @media (min-width: 768px) {
        .h2-md {
            font-size: 2rem;
        }
        .small-md {
            font-size: 1rem;
        }
    }
    
    @media (max-width: 767px) {
        .h2-md {
            font-size: 1.5rem;
        }
        .small-md {
            font-size: 0.875rem;
        }
        .extra-small {
            font-size: 0.7rem;
        }
    }
    
    /* Navbar offset for fixed navbar */
    @media (max-width: 767px) {
        .page-header-section {
            padding-top: calc(1.5rem + 65px) !important;
        }
    }
    
    @media (min-width: 768px) and (max-width: 991px) {
        .page-header-section {
            padding-top: calc(2rem + 70px) !important;
        }
    }
    
    @media (min-width: 992px) {
        .page-header-section {
            padding-top: calc(2rem + 75px) !important;
        }
    }

    /* Document Dropzone Styles */
    .document-upload-wrapper {
        position: relative;
    }

    .document-upload-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        overflow: hidden;
    }

    .document-dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .document-dropzone:hover {
        border-color: #003366;
        background: #f1f5f9;
    }

    .document-dropzone.is-active {
        border-color: #003366;
        background: #e0e7ff;
    }

    .document-dropzone.is-uploading {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .document-dropzone.is-complete {
        border-color: #10b981;
        background: #ecfdf5;
    }

    .document-dropzone.has-error {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .document-dropzone.has-file {
        border-color: #10b981;
    }

    .document-dropzone-icon {
        font-size: 3rem;
        color: #64748b;
        margin-bottom: 1rem;
    }

    .document-dropzone.is-active .document-dropzone-icon,
    .document-dropzone.is-uploading .document-dropzone-icon {
        color: #003366;
    }

    .document-dropzone.is-complete .document-dropzone-icon {
        color: #10b981;
    }

    .document-dropzone.has-error .document-dropzone-icon {
        color: #ef4444;
    }

    .document-dropzone-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }

    .document-dropzone-thumb {
        width: 64px;
        height: 64px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e2e8f0;
        margin-bottom: 0.5rem;
    }

    .document-dropzone-thumb i {
        font-size: 2rem;
        color: #ef4444;
    }

    .document-dropzone-thumb.is-hidden {
        display: none;
    }

    .document-dropzone-text {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .document-dropzone-title {
        font-weight: 600;
        color: #0f172a;
        font-size: 1rem;
    }

    .document-dropzone small {
        color: #64748b;
        font-size: 0.875rem;
    }

    .document-dropzone-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 0.5rem;
        justify-content: center;
    }

    .document-dropzone-change,
    .document-dropzone-clear {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: white;
        color: #475569;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .document-dropzone-change:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }

    .document-dropzone-clear:hover {
        background: #fef2f2;
        border-color: #ef4444;
        color: #ef4444;
    }

    .document-dropzone-change.is-hidden,
    .document-dropzone-clear.is-hidden {
        display: none;
    }

    .document-upload-progress {
        margin-top: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .document-upload-progress.is-hidden {
        display: none;
    }

    .document-upload-progress-track {
        flex: 1;
        height: 8px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .document-upload-progress-bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #003366, #004080);
        border-radius: 999px;
        transition: width 0.2s ease;
    }

    .document-upload-progress-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #475569;
        min-width: 45px;
        text-align: right;
    }

    .document-error {
        display: block;
        margin-top: 0.5rem;
        color: #ef4444;
        font-size: 0.875rem;
    }

    .document-error.is-hidden {
        display: none;
    }
</style>
@endpush

@push('scripts')
@once
<script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
@endonce
<script>
    (function() {
        const MAX_DOCUMENT_SIZE = 5 * 1024 * 1024; // 5MB
        const CHUNK_SIZE_BYTES = 1 * 1024 * 1024; // 1MB
        const CHUNK_UPLOAD_ENDPOINT = (function() {
            const origin = window.location.origin.replace(/\/+$/, '');
            const path = "{{ trim(parse_url(route('instructor.uploads.chunk'), PHP_URL_PATH), '/') }}";
            const endpoint = `${origin}/${path}`;
            console.log('Chunk upload endpoint:', endpoint);
            return endpoint;
        })();

        if (!window.__tempUploadConfig) {
            window.__tempUploadConfig = {
                prefix: '{{ \App\Services\FileUploadService::TEMPORARY_BASE_PATH }}/',
                endpoint: "{{ route('uploads.temp.destroy') }}",
            };
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
            if (!bytes && bytes !== 0) return '';
            if (bytes === 0) return '0 o';
            const units = ['o', 'Ko', 'Mo', 'Go'];
            const index = Math.floor(Math.log(bytes) / Math.log(1024));
            const value = bytes / Math.pow(1024, index);
            return `${value.toFixed(value >= 10 || index === 0 ? 0 : 1)} ${units[index]}`;
        };

        const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const ChunkUpload = {
            isSupported() {
                return typeof Resumable !== 'undefined';
            },

            upload({ file, type = 'document', metadata = {}, onProgress, onSuccess, onError }) {
                if (!this.isSupported()) {
                    onError?.('Votre navigateur ne supporte pas l\'upload fractionné. Veuillez le mettre à jour.');
                    return null;
                }

                const normalizeError = (rawMessage) => {
                    if (!rawMessage) return 'Erreur lors du téléversement.';
                    if (typeof rawMessage === 'string') {
                        try {
                            const parsed = JSON.parse(rawMessage);
                            return parsed?.message ?? parsed?.error ?? rawMessage;
                        } catch (error) {
                            return rawMessage;
                        }
                    }
                    return rawMessage?.message || 'Erreur lors du téléversement.';
                };

                const resumable = new Resumable({
                    target: CHUNK_UPLOAD_ENDPOINT,
                    chunkSize: CHUNK_SIZE_BYTES,
                    simultaneousUploads: 1,
                    testChunks: false,
                    throttleProgressCallbacks: 0.5,
                    fileParameterName: 'file',
                    fileType: ['pdf', 'doc', 'docx'],
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
                
                console.log('Resumable instance created:', {
                    target: CHUNK_UPLOAD_ENDPOINT,
                    chunkSize: CHUNK_SIZE_BYTES,
                    file: file.name,
                    fileSize: file.size,
                    csrfToken: getCsrfToken() ? 'present' : 'missing'
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

                resumable.on('fileError', (_resumableFile, message) => {
                    console.error('Resumable fileError:', message);
                    handleError(message);
                });
                resumable.on('error', (message, file) => {
                    console.error('Resumable error:', message, file);
                    handleError(message);
                });
                resumable.on('uploadStart', () => {
                    console.log('Upload started');
                });
                resumable.on('progress', () => {
                    console.log('Upload progress');
                });

                resumable.addFile(file);
                
                // Démarrer l'upload explicitement
                setTimeout(() => {
                    resumable.upload();
                }, 100);

                return {
                    cancel() {
                        resumable.cancel();
                    },
                };
            },
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
            if (!element) return;
            element.classList.toggle('is-hidden', shouldHide);
        };

        const assignFileToInput = (input, file, fileList = null) => {
            if (!input) return false;
            if (fileList && fileList.length) {
                try {
                    input.files = fileList;
                    return true;
                } catch (error) {}
            }
            if (!file) return false;
            if (typeof DataTransfer !== 'undefined') {
                try {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
                    return true;
                } catch (error) {}
            }
            return false;
        };

        // Fonction pour initialiser un uploader de document
        const initDocumentUploader = (prefix) => {
            const dropzone = document.querySelector(`[data-role="${prefix}-dropzone-box"]`);
            const fileInput = document.querySelector(`[data-role="${prefix}-file"]`);
            const label = document.querySelector(`[data-role="${prefix}-label"]`);
            const thumbEl = document.querySelector(`[data-role="${prefix}-thumb"]`);
            const changeBtn = document.querySelector(`[data-role="${prefix}-change"]`);
            const clearBtn = document.querySelector(`[data-role="${prefix}-clear"]`);
            const errorEl = document.querySelector(`[data-role="${prefix}-error"]`);
            const pathInput = document.querySelector(`[data-role="${prefix}-path"]`);
            const nameInput = document.querySelector(`[data-role="${prefix}-name"]`);
            const sizeInput = document.querySelector(`[data-role="${prefix}-size"]`);
            const progressContainer = document.querySelector(`[data-role="${prefix}-upload-progress"]`);
            const progressBar = document.querySelector(`[data-role="${prefix}-upload-progress-bar"]`);
            const progressLabel = document.querySelector(`[data-role="${prefix}-upload-progress-label"]`);

            let activeUpload = null;
            let uploadTaskId = null;

            if (!dropzone || !fileInput || !label) return;

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
                if (progressBar) progressBar.style.width = `${normalized}%`;
                if (progressLabel) progressLabel.textContent = `${normalized}%`;
            };

            const reset = ({ clearUrl = false, keepError = false, clearStored = true } = {}) => {
                stopActiveUpload();
                try {
                    fileInput.value = '';
                } catch (error) {}

                dropzone.classList.remove('is-active', 'is-uploading', 'is-complete', 'has-file');
                if (!keepError) {
                    dropzone.classList.remove('has-error');
                    toggleHidden(errorEl, true);
                }

                label.textContent = defaultLabel;
                toggleHidden(changeBtn, true);
                toggleHidden(clearBtn, true);

                if (thumbEl) {
                    thumbEl.innerHTML = '<i class="fas fa-file-pdf"></i>';
                    thumbEl.classList.add('is-hidden');
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
                    if (nameInput) nameInput.value = '';
                    if (sizeInput) sizeInput.value = '';
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

            const applyStoredFile = ({ clearError: shouldClearError = true } = {}) => {
                if (!pathInput || !pathInput.value) return;

                if (shouldClearError) clearError();

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
                    thumbEl.innerHTML = '<i class="fas fa-file-pdf"></i>';
                    thumbEl.classList.remove('is-hidden');
                }
                
                // Enregistrer le chemin temporaire s'il s'agit d'un fichier temporaire
                if (isTemporaryPath(pathInput.value)) {
                    registerTemporaryPath(pathInput.value);
                }
            };

            const startChunkUpload = (file, previousPath = null) => {
                if (!ChunkUpload.isSupported()) {
                    showError('Votre navigateur ne supporte pas l\'upload fractionné. Veuillez le mettre à jour.');
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
                    prefix === 'cv' ? 'Téléversement du CV' : 'Téléversement de la lettre de motivation'
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
                } catch (error) {}

                activeUpload = ChunkUpload.upload({
                    file,
                    type: 'document',
                    metadata: {
                        replace_path: previousPath,
                    },
                    onProgress: (percent) => {
                        console.log('Upload progress:', percent + '%');
                        updateProgress(percent);
                        UploadTaskManager.update(uploadTaskId, percent, 'Téléversement en cours…');
                    },
                    onSuccess: (payload) => {
                        console.log('Upload success:', payload);
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
                        
                        // Enregistrer le chemin temporaire pour la gestion automatique
                        if (payload.path && typeof registerTemporaryPath === 'function') {
                            registerTemporaryPath(payload.path);
                        }
                        
                        if (previousPath && previousPath !== payload.path && typeof queueTemporaryDeletion === 'function') {
                            queueTemporaryDeletion(previousPath);
                        }
                        
                        if (uploadTaskId) {
                            UploadTaskManager.complete(uploadTaskId, 'Document importé avec succès');
                            uploadTaskId = null;
                        }
                    },
                    onError: (message) => {
                        console.error('Upload error:', message);
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
                
                if (!activeUpload) {
                    console.error('ChunkUpload.upload returned null');
                    showError('Impossible de démarrer l\'upload. Veuillez réessayer.');
                }
            };

            const handleFileSelection = (file) => {
                if (!file) {
                    reset();
                    return;
                }

                if (file.size > MAX_DOCUMENT_SIZE) {
                    showError(`Le fichier est trop volumineux (${formatBytes(file.size)}). Limite : ${formatBytes(MAX_DOCUMENT_SIZE)}.`);
                    reset({ keepError: true });
                    return;
                }

                const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (!allowedTypes.includes(file.type) && !file.name.match(/\.(pdf|doc|docx)$/i)) {
                    showError('Seuls les fichiers PDF, DOC et DOCX sont acceptés.');
                    reset({ keepError: true });
                    return;
                }

                const previousPath = pathInput?.value || null;

                clearError();
                reset({ clearStored: false });

                if (thumbEl) {
                    thumbEl.innerHTML = '<i class="fas fa-file-pdf"></i>';
                    thumbEl.classList.remove('is-hidden');
                }
                label.textContent = `${file.name} (${formatBytes(file.size)})`;
                dropzone.classList.add('has-file');

                startChunkUpload(file, previousPath);
            };

            dropzone.addEventListener('click', (event) => {
                if (event.target.closest(`[data-role="${prefix}-clear"]`)) {
                    event.preventDefault();
                    event.stopPropagation();
                    return;
                }
                if (event.target.closest(`[data-role="${prefix}-change"]`)) {
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
                if (!file) return;
                const assigned = assignFileToInput(fileInput, file, fileList);
                if (!assigned) {
                    showError('Le glisser-déposer n\'est pas pris en charge par ce navigateur. Utilisez le bouton pour sélectionner le fichier.');
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
                    } catch (error) {}
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

            reset({ clearStored: false });
            clearError();
            applyStoredFile();
        };

        document.addEventListener('DOMContentLoaded', () => {
            // Initialiser les deux uploaders
            initDocumentUploader('cv');
            initDocumentUploader('letter');

            // Gérer la soumission du formulaire
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', (event) => {
                    TempUploadManager.markSubmitting();
                    
                    // Désactiver les inputs file pour ne pas envoyer les fichiers directement
                    // On utilise seulement les chemins qui sont dans les inputs hidden
                    const cvInput = document.querySelector('[data-role="cv-file"]');
                    const letterInput = document.querySelector('[data-role="letter-file"]');
                    if (cvInput) cvInput.disabled = true;
                    if (letterInput) letterInput.disabled = true;
                });
            }
        });

        // Gérer la fermeture de la page
        if (!window.__tempUploadUnloadHook) {
            window.__tempUploadUnloadHook = true;
            window.addEventListener('beforeunload', () => {
                if (TempUploadManager && !TempUploadManager.isSubmitting()) {
                    TempUploadManager.flushAll({ keepalive: true });
                }
            });
        }
    })();
</script>
@endpush
