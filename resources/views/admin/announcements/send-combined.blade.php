@extends('layouts.admin')

@section('title', 'Envoyer par Email et WhatsApp')
@section('admin-title', 'Envoyer par Email et WhatsApp')
@section('admin-subtitle', 'Envoyez simultanément par email et WhatsApp - Les envois sont traités en arrière-plan')

@section('admin-actions')
    <a href="{{ route('admin.announcements') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour aux annonces
    </a>
@endsection

@include('partials.upload-progress-modal')

@section('admin-content')
<div class="admin-panel">
    <div class="admin-panel__body admin-panel__body--padded">
        @if(isset($whatsappConnectionStatus) && !$whatsappConnectionStatus['connected'])
        <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Attention:</strong> La connexion WhatsApp n'est pas active. Les messages WhatsApp ne pourront pas être envoyés.
            <br><small>État actuel: {{ $whatsappConnectionStatus['state'] ?? 'Inconnu' }}</small>
        </div>
        @endif

        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Envoi direct:</strong> Les messages sont envoyés directement. L'échec d'un canal (email ou WhatsApp) n'empêche pas l'autre de fonctionner. Une fenêtre de chargement s'affichera pendant l'envoi.
        </div>

        <form id="sendCombinedForm" method="POST" action="{{ route('admin.announcements.send-combined.post') }}" enctype="multipart/form-data">
            @csrf

            <!-- Sélection des canaux -->
            <div class="admin-form-card mb-4">
                <h5 class="mb-3"><i class="fas fa-broadcast-tower me-2"></i>Canaux d'envoi</h5>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_email" id="send_email" value="1" checked>
                        <label class="form-check-label" for="send_email">
                            <strong><i class="fas fa-envelope me-2"></i>Envoyer par Email</strong>
                            <small class="d-block text-muted">Les utilisateurs avec une adresse email recevront le message</small>
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_whatsapp" id="send_whatsapp" value="1" checked>
                        <label class="form-check-label" for="send_whatsapp">
                            <strong><i class="fab fa-whatsapp me-2"></i>Envoyer par WhatsApp</strong>
                            <small class="d-block text-muted">Les utilisateurs avec un numéro de téléphone recevront le message</small>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Destinataires -->
            <div class="admin-form-card mb-4">
                <h5 class="mb-3"><i class="fas fa-users me-2"></i>Sélection des destinataires</h5>
                
                <div class="mb-3">
                    <label class="form-label">Type d'envoi *</label>
                    <select class="form-select" id="recipient_type" name="recipient_type" required>
                        <option value="all">Tous les utilisateurs</option>
                        <option value="role">Par rôle</option>
                        <option value="course">Utilisateurs inscrits à un contenu</option>
                        <option value="category">Utilisateurs inscrits à une catégorie</option>
                        <option value="provider">Utilisateurs inscrits à un prestataire</option>
                        <option value="downloaded_free">Utilisateurs ayant téléchargé un contenu gratuit</option>
                        <option value="purchased">Utilisateurs ayant effectué un achat</option>
                        <option value="purchased_content">Utilisateurs ayant acheté un contenu spécifique</option>
                        <option value="failed_payment">Utilisateurs dont le paiement a échoué</option>
                        <option value="registration_date">Par date d'inscription</option>
                        <option value="activity">Par activité</option>
                        <option value="selected">Utilisateurs sélectionnés</option>
                        <option value="single">Un seul utilisateur</option>
                    </select>
                </div>

                <!-- Sélection par rôle -->
                <div class="mb-3" id="role_selection" style="display: none;">
                    <label class="form-label">Rôle</label>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="customer" id="role_customer">
                                <label class="form-check-label" for="role_customer">Clients</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="provider" id="role_provider">
                                <label class="form-check-label" for="role_provider">Prestataires</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="admin" id="role_admin">
                                <label class="form-check-label" for="role_admin">Admins</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="affiliate" id="role_affiliate">
                                <label class="form-check-label" for="role_affiliate">Affiliés</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="ambassador" id="role_ambassador">
                                <label class="form-check-label" for="role_ambassador">Ambassadeurs</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sélection par contenu -->
                <div class="mb-3" id="course_selection" style="display: none;">
                    <label class="form-label">Contenu *</label>
                    <select class="form-select" id="content_id" name="content_id">
                        <option value="">Sélectionner un contenu</option>
                        @foreach(\App\Models\Course::where('is_published', true)->orderBy('title')->get() as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Seuls les utilisateurs inscrits à ce contenu recevront le message</small>
                </div>

                <!-- Sélection par catégorie -->
                <div class="mb-3" id="category_selection" style="display: none;">
                    <label class="form-label">Catégorie *</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Sélectionner une catégorie</option>
                        @foreach(\App\Models\Category::where('is_active', true)->orderBy('name')->get() as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Seuls les utilisateurs inscrits à des contenus de cette catégorie recevront le message</small>
                </div>

                <!-- Sélection par prestataire -->
                <div class="mb-3" id="provider_selection" style="display: none;">
                    <label class="form-label">Prestataire *</label>
                    <select class="form-select" id="provider_id" name="provider_id">
                        <option value="">Sélectionner un prestataire</option>
                        @foreach(\App\Models\User::where('role', 'provider')->where('is_active', true)->orderBy('name')->get() as $provider)
                            <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Seuls les utilisateurs inscrits à des contenus de ce prestataire recevront le message</small>
                </div>

                <!-- Sélection par téléchargement gratuit -->
                <div class="mb-3" id="downloaded_free_selection" style="display: none;">
                    <label class="form-label">Contenu téléchargeable gratuit *</label>
                    <select class="form-select" id="downloaded_content_id" name="downloaded_content_id">
                        <option value="">Tous les contenus téléchargeables gratuits</option>
                        @foreach(\App\Models\Course::where('is_published', true)->where('is_downloadable', true)->where('is_free', true)->orderBy('title')->get() as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sélection par achat -->
                <div class="mb-3" id="purchased_selection" style="display: none;">
                    <label class="form-label">Type d'achat *</label>
                    <select class="form-select" id="purchase_type" name="purchase_type">
                        <option value="any">Tous les utilisateurs ayant effectué un achat</option>
                        <option value="paid">Utilisateurs ayant des commandes payées</option>
                        <option value="completed">Utilisateurs ayant des commandes complétées</option>
                        <option value="specific_content">Utilisateurs ayant acheté un contenu spécifique</option>
                    </select>
                </div>

                <!-- Sélection par contenu acheté (pour purchased_content) -->
                <div class="mb-3" id="purchased_content_selection" style="display: none;">
                    <label class="form-label">Contenu acheté *</label>
                    <select class="form-select" id="purchased_content_id" name="purchased_content_id">
                        <option value="">Sélectionner un contenu</option>
                        @foreach(\App\Models\Course::where('is_published', true)->where('is_free', false)->orderBy('title')->get() as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sélection par date d'inscription -->
                <div class="mb-3" id="registration_date_selection" style="display: none;">
                    <label class="form-label">Période d'inscription *</label>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label small">Date de début</label>
                            <input type="date" class="form-control" id="registration_date_from" name="registration_date_from">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Date de fin</label>
                            <input type="date" class="form-control" id="registration_date_to" name="registration_date_to">
                        </div>
                    </div>
                    <small class="text-muted">Sélectionnez une période pour cibler les utilisateurs inscrits dans cette période</small>
                </div>

                <!-- Sélection par activité -->
                <div class="mb-3" id="activity_selection" style="display: none;">
                    <label class="form-label">Type d'activité *</label>
                    <select class="form-select" id="activity_type" name="activity_type">
                        <option value="">Sélectionner un type</option>
                        <option value="active_recent">Actifs récemment (7 derniers jours)</option>
                        <option value="active_month">Actifs ce mois</option>
                        <option value="active_3months">Actifs (3 derniers mois)</option>
                        <option value="inactive_30days">Inactifs (30+ jours)</option>
                        <option value="inactive_90days">Inactifs (90+ jours)</option>
                        <option value="never_logged">Jamais connectés</option>
                    </select>
                    <small class="text-muted">Filtrez les utilisateurs selon leur dernière connexion</small>
                </div>

                <!-- Sélection d'un seul utilisateur -->
                <div class="mb-3" id="single_user_selection" style="display: none;">
                    <label class="form-label">Sélectionner un utilisateur</label>
                    <input type="hidden" id="single_user_id" name="single_user_id" value="">
                    <input type="text" class="form-control" id="user_search" placeholder="Rechercher par nom, email ou téléphone (minimum 2 caractères)...">
                    <div id="user_search_results" class="mt-2 border rounded p-2" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                </div>

                <!-- Sélection de plusieurs utilisateurs -->
                <div class="mb-3" id="multiple_users_selection" style="display: none;">
                    <label class="form-label">Sélectionner des utilisateurs</label>
                    <input type="text" class="form-control" id="multiple_user_search" placeholder="Rechercher des utilisateurs (minimum 2 caractères)...">
                    <div id="multiple_user_search_results" class="mt-2 border rounded p-2" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                    <div id="selected_users" class="mt-3 mb-2"></div>
                    <input type="hidden" id="user_ids" name="user_ids" value="">
                </div>

                <div class="alert alert-info" id="recipient_count" style="display: none;">
                    <i class="fas fa-info-circle me-2"></i><span id="recipient_count_text"></span>
                </div>
            </div>

            <!-- Contenu Email -->
            <div class="admin-form-card mb-4" id="email_content_section">
                <h5 class="mb-3"><i class="fas fa-envelope me-2"></i>Contenu de l'email</h5>
                
                <div class="mb-3">
                    <label class="form-label">Objet *</label>
                    <input type="text" class="form-control" name="subject" id="subject" required 
                           placeholder="Objet de l'email" maxlength="255">
                </div>

                <div class="mb-3">
                    <label class="form-label">Contenu *</label>
                    <div id="email_content_editor" style="height: 400px;"></div>
                    <textarea class="form-control d-none" id="email_content" name="email_content" required></textarea>
                    <div id="email_editor_upload_status" class="rich-editor-upload-status" role="status" aria-live="polite"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pièces jointes</label>
                    <input type="file" class="form-control" name="attachments[]" id="attachments" multiple 
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar">
                    <small class="form-text text-muted">Vous pouvez sélectionner plusieurs fichiers. Formats acceptés: PDF, Word, Excel, PowerPoint, Images, ZIP, RAR</small>
                    <div id="attachments_preview" class="mt-2"></div>
                </div>
            </div>

            <!-- Contenu WhatsApp -->
            <div class="admin-form-card mb-4" id="whatsapp_content_section">
                <h5 class="mb-3"><i class="fab fa-whatsapp me-2"></i>Contenu du message WhatsApp</h5>
                
                <div class="mb-3">
                    <label class="form-label">Message *</label>
                    <textarea class="form-control" name="whatsapp_message" id="whatsapp_message" required rows="8" 
                              placeholder="Rédigez votre message WhatsApp ici..." maxlength="4096"></textarea>
                    <small class="form-text text-muted">
                        <span id="char_count">0</span> / 4096 caractères
                    </small>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.announcements') }}" class="btn btn-light">Annuler</a>
                <button type="submit" class="btn btn-primary" id="send_btn">
                    <i class="fas fa-paper-plane me-2"></i>Envoyer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de chargement -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="loadingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <h5 class="mb-2">Envoi en cours...</h5>
                <p class="text-muted mb-0">Veuillez patienter pendant l'envoi des messages. Cela peut prendre quelques instants.</p>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Ne fermez pas cette fenêtre pendant l'envoi
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
#selected_users {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.user-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background-color: #f0f4ff;
    border: 1px solid #003366;
    border-radius: 6px;
    font-size: 14px;
}
.user-badge button {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    padding: 0;
    font-size: 16px;
}
#attachments_preview {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.attachment-preview {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 14px;
}

.rich-editor-upload-status {
    display: none;
    margin-top: 0.5rem;
    font-size: 0.85rem;
    border-radius: 8px;
    padding: 0.4rem 0.6rem;
}
.rich-editor-upload-status.is-visible {
    display: inline-flex;
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
<script>
// Variables globales
let selectedUsers = [];
let quill;
let pendingRichEditorUploads = 0;

// Initialiser Quill Editor quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    const RICH_EDITOR_IMAGE_MAX_SIZE = 5 * 1024 * 1024;
    const editorUploadStatusEl = document.getElementById('email_editor_upload_status');

    function getRichEditorChunkEndpoint() {
        const origin = window.location.origin.replace(/\/+$/, '');
        const path = "{{ trim(parse_url(route('admin.uploads.chunk'), PHP_URL_PATH), '/') }}";
        return `${origin}/${path}`;
    }

    function setEditorUploadStatus(state, message = '') {
        if (!editorUploadStatusEl) {
            return;
        }
        editorUploadStatusEl.classList.remove('is-visible', 'is-loading', 'is-success', 'is-error');
        if (!state || !message) {
            editorUploadStatusEl.textContent = '';
            return;
        }
        editorUploadStatusEl.textContent = message;
        editorUploadStatusEl.classList.add('is-visible', `is-${state}`);
    }

    function hasPendingOrLocalImages() {
        return pendingRichEditorUploads > 0 || !!quill.root.querySelector('img[src^="data:image/"]');
    }

    function ensureModernDialogModal() {
        let modalEl = document.getElementById('announcementCombinedModernDialog');
        if (modalEl) {
            return modalEl;
        }

        modalEl = document.createElement('div');
        modalEl.className = 'modal fade';
        modalEl.id = 'announcementCombinedModernDialog';
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
                    </div>
                    <div class="modal-footer">
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
            const okBtn = modalEl.querySelector('[data-dialog-ok]');
            const modal = new bootstrap.Modal(modalEl);

            titleEl.textContent = title;
            messageEl.textContent = message;

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

    function createUploadTask(fileName, fileSize, description = 'Téléversement en cours…') {
        if (!window.UploadProgressModal) {
            return null;
        }
        const taskId = `announcement-combined-upload-${Date.now()}-${Math.random().toString(16).slice(2, 10)}`;
        window.UploadProgressModal.startTask(taskId, {
            label: fileName || 'Image',
            description,
            sizeLabel: fileSize ? `${Math.max(1, Math.round(fileSize / 1024))} Ko` : '',
            initialMessage: 'Préparation du téléversement…',
        });
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

    function uploadRichEditorImageInChunks(file) {
        return new Promise((resolve, reject) => {
            const uploadTaskId = createUploadTask(
                file?.name || 'Image de l’email',
                file?.size || 0,
                'Téléversement de l’image de l’email'
            );

            if (typeof Resumable === 'undefined') {
                errorUploadTask(uploadTaskId, 'Votre navigateur ne supporte pas l’upload fractionné.');
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
                setEditorUploadStatus('loading', `Téléversement de l'image en cours (${Math.max(5, percent)}%)`);
                updateUploadTask(uploadTaskId, percent, 'Téléversement en cours…');
            });

            resumable.on('fileSuccess', (_resumableFile, response) => {
                if (isSettled) {
                    return;
                }
                isSettled = true;
                try {
                    const payload = typeof response === 'string' ? JSON.parse(response) : response;
                    if (!payload || !payload.url) {
                        errorUploadTask(uploadTaskId, 'Réponse serveur invalide.');
                        reject(new Error('Réponse serveur invalide.'));
                        return;
                    }
                    completeUploadTask(uploadTaskId, 'Image importée avec succès');
                    resolve(payload);
                } catch (_error) {
                    errorUploadTask(uploadTaskId, 'Réponse serveur invalide.');
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
                errorUploadTask(uploadTaskId, 'Échec du téléversement de l’image.');
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

    // Configuration Quill Editor
    quill = new Quill('#email_content_editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image', 'video'],
                ['clean'],
                ['code-block']
            ]
        },
        placeholder: 'Rédigez votre email ici...',
        bounds: '#email_content_editor'
    });

    // Upload d'images (chunk upload + remplacement URL serveur)
    var toolbar = quill.getModule('toolbar');
    toolbar.addHandler('image', function() {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();
        
        input.onchange = async function() {
            var file = input.files[0];
            if (file) {
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
                    const localSrc = await readAsDataUrl(file);
                    const range = quill.getSelection(true);
                    const index = range ? range.index : quill.getLength();
                    quill.insertEmbed(index, 'image', localSrc, 'user');
                    quill.setSelection(index + 1);

                    pendingRichEditorUploads++;
                    setEditorUploadStatus('loading', "Téléversement de l'image en cours (5%)");
                    const payload = await uploadRichEditorImageInChunks(file);
                    const images = quill.root.querySelectorAll('img');
                    for (const image of images) {
                        if (image.getAttribute('src') === localSrc) {
                            image.setAttribute('src', payload.url);
                            break;
                        }
                    }
                    quill.update('user');
                    setEditorUploadStatus('success', 'Image téléversée et intégrée.');
                    window.setTimeout(() => setEditorUploadStatus(null, ''), 2500);
                } catch (error) {
                    setEditorUploadStatus('error', error?.message || "Échec du téléversement de l'image.");
                    showModernAlert(error?.message || "Échec du téléversement de l'image.");
                } finally {
                    pendingRichEditorUploads = Math.max(0, pendingRichEditorUploads - 1);
                }
            }
        };
    });

    // Synchroniser le contenu avec le textarea
    quill.on('text-change', function() {
        const textarea = document.getElementById('email_content');
        if (textarea) {
            textarea.value = quill.root.innerHTML;
        }
    });

    // Initialiser le textarea
    const textarea = document.getElementById('email_content');
    if (textarea) {
        textarea.value = quill.root.innerHTML;
    }

    // Gérer l'affichage des sections selon les canaux sélectionnés
    function updateChannelSections() {
        const sendEmail = document.getElementById('send_email').checked;
        const sendWhatsApp = document.getElementById('send_whatsapp').checked;
        
        const emailSection = document.getElementById('email_content_section');
        const whatsappSection = document.getElementById('whatsapp_content_section');
        
        if (emailSection) {
            emailSection.style.display = sendEmail ? 'block' : 'none';
            const emailInputs = emailSection.querySelectorAll('input[required], textarea[required]');
            emailInputs.forEach(input => input.required = sendEmail);
        }
        
        if (whatsappSection) {
            whatsappSection.style.display = sendWhatsApp ? 'block' : 'none';
            const whatsappInputs = whatsappSection.querySelectorAll('input[required], textarea[required]');
            whatsappInputs.forEach(input => input.required = sendWhatsApp);
        }
    }

    document.getElementById('send_email').addEventListener('change', updateChannelSections);
    document.getElementById('send_whatsapp').addEventListener('change', updateChannelSections);
    updateChannelSections();

    // Compteur de caractères WhatsApp
    const whatsappMessage = document.getElementById('whatsapp_message');
    const charCount = document.getElementById('char_count');
    if (whatsappMessage && charCount) {
        whatsappMessage.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // Fonction pour initialiser l'affichage selon le type de destinataire
    function updateRecipientSections(type) {
        const roleSelection = document.getElementById('role_selection');
        const courseSelection = document.getElementById('course_selection');
        const categorySelection = document.getElementById('category_selection');
        const providerSelection = document.getElementById('provider_selection');
        const downloadedFreeSelection = document.getElementById('downloaded_free_selection');
        const purchasedSelection = document.getElementById('purchased_selection');
        const purchasedContentSelection = document.getElementById('purchased_content_selection');
        const registrationDateSelection = document.getElementById('registration_date_selection');
        const activitySelection = document.getElementById('activity_selection');
        const singleUserSelection = document.getElementById('single_user_selection');
        const multipleUsersSelection = document.getElementById('multiple_users_selection');
        
        // Masquer toutes les sections
        [roleSelection, courseSelection, categorySelection, providerSelection, downloadedFreeSelection, purchasedSelection, purchasedContentSelection, registrationDateSelection, activitySelection, singleUserSelection, multipleUsersSelection].forEach(el => {
            if (el) el.style.display = 'none';
        });
        
        // Afficher la section appropriée
        if (type === 'role' && roleSelection) {
            roleSelection.style.display = 'block';
        } else if (type === 'course' && courseSelection) {
            courseSelection.style.display = 'block';
        } else if (type === 'category' && categorySelection) {
            categorySelection.style.display = 'block';
        } else if (type === 'provider' && providerSelection) {
            providerSelection.style.display = 'block';
        } else if (type === 'downloaded_free' && downloadedFreeSelection) {
            downloadedFreeSelection.style.display = 'block';
        } else if (type === 'purchased' && purchasedSelection) {
            purchasedSelection.style.display = 'block';
            const purchaseType = document.getElementById('purchase_type')?.value;
            if (purchaseType === 'specific_content' && purchasedContentSelection) {
                purchasedContentSelection.style.display = 'block';
            }
        } else if (type === 'purchased_content' && purchasedContentSelection) {
            purchasedContentSelection.style.display = 'block';
        } else if (type === 'failed_payment') {
            // Pas de section supplémentaire
        } else if (type === 'registration_date' && registrationDateSelection) {
            registrationDateSelection.style.display = 'block';
        } else if (type === 'activity' && activitySelection) {
            activitySelection.style.display = 'block';
        } else if (type === 'single' && singleUserSelection) {
            singleUserSelection.style.display = 'block';
        } else if (type === 'selected' && multipleUsersSelection) {
            multipleUsersSelection.style.display = 'block';
        }
    }
    
    window.updateRecipientSections = updateRecipientSections;
    
    // Gestion du type d'achat (pour purchased)
    const purchaseTypeSelect = document.getElementById('purchase_type');
    if (purchaseTypeSelect) {
        purchaseTypeSelect.addEventListener('change', function() {
            const recipientType = document.getElementById('recipient_type')?.value;
            if (recipientType === 'purchased') {
                const purchasedContentSelection = document.getElementById('purchased_content_selection');
                if (purchasedContentSelection) {
                    purchasedContentSelection.style.display = this.value === 'specific_content' ? 'block' : 'none';
                }
                setTimeout(() => window.updateRecipientCount(), 100);
            }
        });
    }
    
    // Gestion du type de destinataire
    const recipientTypeSelect = document.getElementById('recipient_type');
    if (recipientTypeSelect) {
        updateRecipientSections(recipientTypeSelect.value);
        
        recipientTypeSelect.addEventListener('change', function() {
            const type = this.value;
            
            // Réinitialiser les champs
            document.getElementById('single_user_id').value = '';
            document.getElementById('user_search').value = '';
            document.getElementById('user_search_results').innerHTML = '';
            document.getElementById('user_search_results').style.display = 'none';
            document.getElementById('multiple_user_search').value = '';
            document.getElementById('multiple_user_search_results').innerHTML = '';
            document.getElementById('multiple_user_search_results').style.display = 'none';
            
            document.querySelectorAll('input[name="roles[]"]').forEach(cb => cb.checked = false);
            
            if (type !== 'selected') {
                selectedUsers = [];
                if (window.updateSelectedUsersDisplay) {
                    window.updateSelectedUsersDisplay();
                }
            }
            
            updateRecipientSections(type);
            setTimeout(() => window.updateRecipientCount(), 100);
        });
    }
    
    // Fonction pour mettre à jour le compte de destinataires
    window.updateRecipientCount = function() {
        const recipientTypeSelect = document.getElementById('recipient_type');
        const countDiv = document.getElementById('recipient_count');
        const countText = document.getElementById('recipient_count_text');
        const sendEmail = document.getElementById('send_email').checked;
        const sendWhatsApp = document.getElementById('send_whatsapp').checked;
        
        if (!recipientTypeSelect || !countDiv || !countText) return;
        
        const type = recipientTypeSelect.value;
        let emailCount = 0;
        let whatsappCount = 0;
        
        if (type === 'all') {
            if (sendEmail) {
                fetch('{{ route("admin.announcements.count-users") }}?type=all')
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            emailCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
            if (sendWhatsApp) {
                fetch('{{ route("admin.announcements.count-users-whatsapp") }}?type=all')
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            whatsappCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
        } else if (type === 'role') {
            const roles = Array.from(document.querySelectorAll('input[name="roles[]"]:checked')).map(cb => cb.value);
            if (roles.length > 0) {
                if (sendEmail) {
                    fetch(`{{ route("admin.announcements.count-users") }}?type=role&roles=${roles.join(',')}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data && typeof data.count !== 'undefined') {
                                emailCount = data.count;
                                updateCountDisplay();
                            }
                        });
                }
                if (sendWhatsApp) {
                    fetch(`{{ route("admin.announcements.count-users-whatsapp") }}?type=role&roles=${roles.join(',')}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data && typeof data.count !== 'undefined') {
                                whatsappCount = data.count;
                                updateCountDisplay();
                            }
                        });
                }
            } else {
                countDiv.style.display = 'none';
            }
        } else if (type === 'course') {
            const courseId = document.getElementById('content_id')?.value;
            if (!courseId) {
                countDiv.style.display = 'none';
                return;
            }
            if (sendEmail) {
                fetch(`{{ route("admin.announcements.count-users") }}?type=course&content_id=${courseId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            emailCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
            if (sendWhatsApp) {
                fetch(`{{ route("admin.announcements.count-users-whatsapp") }}?type=course&content_id=${courseId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            whatsappCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
        } else if (type === 'category') {
            const categoryId = document.getElementById('category_id')?.value;
            if (!categoryId) {
                countDiv.style.display = 'none';
                return;
            }
            if (sendEmail) {
                fetch(`{{ route("admin.announcements.count-users") }}?type=category&category_id=${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            emailCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
            if (sendWhatsApp) {
                fetch(`{{ route("admin.announcements.count-users-whatsapp") }}?type=category&category_id=${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            whatsappCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
        } else if (type === 'downloaded_free') {
            const downloadedContentId = document.getElementById('downloaded_content_id')?.value;
            let url = `{{ route("admin.announcements.count-users") }}?type=downloaded_free`;
            if (downloadedContentId) url += `&downloaded_content_id=${downloadedContentId}`;
            if (sendEmail) {
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            emailCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
            let whatsappUrl = `{{ route("admin.announcements.count-users-whatsapp") }}?type=downloaded_free`;
            if (downloadedContentId) whatsappUrl += `&downloaded_content_id=${downloadedContentId}`;
            if (sendWhatsApp) {
                fetch(whatsappUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            whatsappCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
        } else if (type === 'purchased') {
            const purchaseType = document.getElementById('purchase_type')?.value;
            const purchasedContentId = document.getElementById('purchased_content_id')?.value;
            if (!purchaseType || (purchaseType === 'specific_content' && !purchasedContentId)) {
                countDiv.style.display = 'none';
                return;
            }
            let url = `{{ route("admin.announcements.count-users") }}?type=purchased&purchase_type=${purchaseType}`;
            if (purchasedContentId) url += `&purchased_content_id=${purchasedContentId}`;
            if (sendEmail) {
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            emailCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
            let whatsappUrl = `{{ route("admin.announcements.count-users-whatsapp") }}?type=purchased&purchase_type=${purchaseType}`;
            if (purchasedContentId) whatsappUrl += `&purchased_content_id=${purchasedContentId}`;
            if (sendWhatsApp) {
                fetch(whatsappUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            whatsappCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
        } else if (type === 'purchased_content') {
            const purchasedContentId = document.getElementById('purchased_content_id')?.value;
            if (!purchasedContentId) {
                countDiv.style.display = 'none';
                return;
            }
            if (sendEmail) {
                fetch(`{{ route("admin.announcements.count-users") }}?type=purchased_content&purchased_content_id=${purchasedContentId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            emailCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
            if (sendWhatsApp) {
                fetch(`{{ route("admin.announcements.count-users-whatsapp") }}?type=purchased_content&purchased_content_id=${purchasedContentId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            whatsappCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
        } else if (type === 'failed_payment') {
            if (sendEmail) {
                fetch(`{{ route("admin.announcements.count-users") }}?type=failed_payment`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            emailCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
            if (sendWhatsApp) {
                fetch(`{{ route("admin.announcements.count-users-whatsapp") }}?type=failed_payment`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            whatsappCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
        } else if (type === 'provider') {
            const providerId = document.getElementById('provider_id')?.value;
            if (!providerId) {
                countDiv.style.display = 'none';
                return;
            }
            if (sendEmail) {
                fetch(`{{ route("admin.announcements.count-users") }}?type=provider&provider_id=${providerId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            emailCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
            if (sendWhatsApp) {
                fetch(`{{ route("admin.announcements.count-users-whatsapp") }}?type=provider&provider_id=${providerId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            whatsappCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
        } else if (type === 'registration_date') {
            const dateFrom = document.getElementById('registration_date_from')?.value;
            const dateTo = document.getElementById('registration_date_to')?.value;
            if (!dateFrom && !dateTo) {
                countDiv.style.display = 'none';
                return;
            }
            let url = `{{ route("admin.announcements.count-users") }}?type=registration_date`;
            if (dateFrom) url += `&registration_date_from=${dateFrom}`;
            if (dateTo) url += `&registration_date_to=${dateTo}`;
            if (sendEmail) {
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            emailCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
            let whatsappUrl = `{{ route("admin.announcements.count-users-whatsapp") }}?type=registration_date`;
            if (dateFrom) whatsappUrl += `&registration_date_from=${dateFrom}`;
            if (dateTo) whatsappUrl += `&registration_date_to=${dateTo}`;
            if (sendWhatsApp) {
                fetch(whatsappUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            whatsappCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
        } else if (type === 'activity') {
            const activityType = document.getElementById('activity_type')?.value;
            if (!activityType) {
                countDiv.style.display = 'none';
                return;
            }
            if (sendEmail) {
                fetch(`{{ route("admin.announcements.count-users") }}?type=activity&activity_type=${activityType}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            emailCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
            if (sendWhatsApp) {
                fetch(`{{ route("admin.announcements.count-users-whatsapp") }}?type=activity&activity_type=${activityType}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            whatsappCount = data.count;
                            updateCountDisplay();
                        }
                    });
            }
        } else if (type === 'single') {
            const userIdInput = document.getElementById('single_user_id');
            if (userIdInput && userIdInput.value) {
                emailCount = sendEmail ? 1 : 0;
                whatsappCount = sendWhatsApp ? 1 : 0;
                updateCountDisplay();
            } else {
                countDiv.style.display = 'none';
            }
        } else if (type === 'selected') {
            if (selectedUsers && selectedUsers.length > 0) {
                emailCount = sendEmail ? selectedUsers.length : 0;
                whatsappCount = sendWhatsApp ? selectedUsers.length : 0;
                updateCountDisplay();
            } else {
                countDiv.style.display = 'none';
            }
        }
        
        function updateCountDisplay() {
            let messages = [];
            if (sendEmail && emailCount > 0) {
                messages.push(`${emailCount} email(s)`);
            }
            if (sendWhatsApp && whatsappCount > 0) {
                messages.push(`${whatsappCount} message(s) WhatsApp`);
            }
            if (messages.length > 0) {
                countText.textContent = messages.join(' et ') + ' seront envoyés';
                countDiv.style.display = 'block';
            } else {
                countDiv.style.display = 'none';
            }
        }
    };
    
    // Attacher les événements pour les rôles
    document.querySelectorAll('input[name="roles[]"]').forEach(cb => {
        cb.addEventListener('change', window.updateRecipientCount);
    });
    
    // Attacher les événements pour les nouveaux filtres
    const courseSelect = document.getElementById('content_id');
    if (courseSelect) {
        courseSelect.addEventListener('change', window.updateRecipientCount);
    }
    
    const categorySelect = document.getElementById('category_id');
    if (categorySelect) {
        categorySelect.addEventListener('change', window.updateRecipientCount);
    }
    
    const providerSelect = document.getElementById('provider_id');
    if (providerSelect) {
        providerSelect.addEventListener('change', window.updateRecipientCount);
    }
    
    const downloadedContentSelect = document.getElementById('downloaded_content_id');
    if (downloadedContentSelect) {
        downloadedContentSelect.addEventListener('change', window.updateRecipientCount);
    }
    
    const purchaseTypeSelectCount = document.getElementById('purchase_type');
    if (purchaseTypeSelectCount) {
        purchaseTypeSelectCount.addEventListener('change', window.updateRecipientCount);
    }
    
    const purchasedContentSelect = document.getElementById('purchased_content_id');
    if (purchasedContentSelect) {
        purchasedContentSelect.addEventListener('change', window.updateRecipientCount);
    }
    
    const registrationDateFrom = document.getElementById('registration_date_from');
    const registrationDateTo = document.getElementById('registration_date_to');
    if (registrationDateFrom) {
        registrationDateFrom.addEventListener('change', window.updateRecipientCount);
    }
    if (registrationDateTo) {
        registrationDateTo.addEventListener('change', window.updateRecipientCount);
    }
    
    const activityTypeSelect = document.getElementById('activity_type');
    if (activityTypeSelect) {
        activityTypeSelect.addEventListener('change', window.updateRecipientCount);
    }

    // Fonction de recherche d'utilisateurs (combine email et WhatsApp)
    window.searchUsers = function(query, type = 'single') {
        const resultsDivId = type === 'single' ? 'user_search_results' : 'multiple_user_search_results';
        const resultsDiv = document.getElementById(resultsDivId);
        
        if (!resultsDiv) return;
        
        if (!query || query.length < 2) {
            resultsDiv.innerHTML = '';
            resultsDiv.style.display = 'none';
            return;
        }
        
        resultsDiv.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Recherche...</p>';
        resultsDiv.style.display = 'block';
        
        // Utiliser la route de recherche email qui retourne aussi les téléphones
        fetch(`{{ route('admin.announcements.search-users') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data)) {
                    resultsDiv.innerHTML = '<p class="text-danger">Erreur lors de la recherche</p>';
                    return;
                }
                
                if (data.length === 0) {
                    resultsDiv.innerHTML = '<p class="text-muted">Aucun utilisateur trouvé</p>';
                    return;
                }
                
                if (type === 'single') {
                    resultsDiv.innerHTML = data.map(user => {
                        const safeName = (user.name || 'Utilisateur sans nom').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                        const safeEmail = (user.email || 'Pas d\'email').replace(/"/g, '&quot;');
                        const safePhone = (user.phone || 'Pas de téléphone').replace(/"/g, '&quot;');
                        return `
                            <div class="list-group-item list-group-item-action" style="cursor: pointer; padding: 8px 12px;" 
                                 onclick="selectSingleUser(${user.id}, '${safeName}', '${safeEmail}', '${safePhone}')">
                                <strong>${user.name || 'Utilisateur sans nom'}</strong><br>
                                <small class="text-muted">${user.email || 'Pas d\'email'}</small><br>
                                <small class="text-muted">📱 ${user.phone || 'Pas de téléphone'}</small>
                            </div>
                        `;
                    }).join('');
                } else {
                    resultsDiv.innerHTML = data.map(user => {
                        if (selectedUsers && selectedUsers.find(u => u.id === user.id)) {
                            return '';
                        }
                        const safeName = (user.name || 'Utilisateur sans nom').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                        const safeEmail = (user.email || 'Pas d\'email').replace(/"/g, '&quot;');
                        const safePhone = (user.phone || 'Pas de téléphone').replace(/"/g, '&quot;');
                        return `
                            <div class="list-group-item list-group-item-action" style="cursor: pointer; padding: 8px 12px;" 
                                 onclick="addSelectedUser(${user.id}, '${safeName}', '${safeEmail}', '${safePhone}')">
                                <strong>${user.name || 'Utilisateur sans nom'}</strong><br>
                                <small class="text-muted">${user.email || 'Pas d\'email'}</small><br>
                                <small class="text-muted">📱 ${user.phone || 'Pas de téléphone'}</small>
                            </div>
                        `;
                    }).filter(html => html !== '').join('') || '<p class="text-muted">Tous les utilisateurs trouvés sont déjà sélectionnés</p>';
                }
            })
            .catch(error => {
                console.error('Erreur lors de la recherche:', error);
                resultsDiv.innerHTML = '<p class="text-danger">Erreur lors de la recherche</p>';
            });
    };
    
    // Attacher les événements de recherche
    const userSearchInput = document.getElementById('user_search');
    const multipleUserSearchInput = document.getElementById('multiple_user_search');
    
    if (userSearchInput) {
        let searchTimeout;
        userSearchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => searchUsers(this.value, 'single'), 300);
        });
    }
    
    if (multipleUserSearchInput) {
        let searchTimeout;
        multipleUserSearchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => searchUsers(query, 'selected'), 300);
            } else {
                const resultsDiv = document.getElementById('multiple_user_search_results');
                if (resultsDiv) {
                    resultsDiv.innerHTML = '';
                    resultsDiv.style.display = 'none';
                }
            }
        });
    }

    // Fonctions globales pour la sélection d'utilisateurs
    window.selectSingleUser = function(id, name, email, phone) {
        document.getElementById('single_user_id').value = id;
        document.getElementById('user_search').value = `${name} (${email || phone || 'N/A'})`;
        document.getElementById('user_search_results').innerHTML = '';
        document.getElementById('user_search_results').style.display = 'none';
        window.updateRecipientCount();
    };
    
    window.addSelectedUser = function(id, name, email, phone) {
        if (!selectedUsers) selectedUsers = [];
        if (selectedUsers.find(u => u.id === id)) return;
        
        selectedUsers.push({ id, name, email, phone });
        updateSelectedUsersDisplay();
        
        document.getElementById('multiple_user_search').value = '';
        document.getElementById('multiple_user_search_results').innerHTML = '';
        document.getElementById('multiple_user_search_results').style.display = 'none';
        window.updateRecipientCount();
    };
    
    window.removeSelectedUser = function(id) {
        if (!selectedUsers) return;
        selectedUsers = selectedUsers.filter(u => u.id !== id);
        updateSelectedUsersDisplay();
    };
    
    window.updateSelectedUsersDisplay = function() {
        const container = document.getElementById('selected_users');
        const idsInput = document.getElementById('user_ids');
        
        if (!container) return;
        
        if (!selectedUsers || selectedUsers.length === 0) {
            container.innerHTML = '<p class="text-muted">Aucun utilisateur sélectionné</p>';
            if (idsInput) idsInput.value = '';
        } else {
            container.innerHTML = selectedUsers.map(user => {
                const safeName = (user.name || 'Utilisateur sans nom').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                return `
                    <span class="user-badge">
                        ${safeName}
                        <button type="button" onclick="removeSelectedUser(${user.id})">&times;</button>
                    </span>
                `;
            }).join('');
            if (idsInput) idsInput.value = selectedUsers.map(u => u.id).join(',');
        }
        window.updateRecipientCount();
    };
    
    // Modal de chargement
    function showLoadingModal() {
        const modal = document.getElementById('loadingModal');
        if (modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    }
    
    function hideLoadingModal() {
        const modal = document.getElementById('loadingModal');
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    }
    
    // Validation et soumission du formulaire avec AJAX
    const sendCombinedForm = document.getElementById('sendCombinedForm');
    if (sendCombinedForm) {
        sendCombinedForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (hasPendingOrLocalImages()) {
                showModernAlert('Veuillez patienter: une image est encore en cours de téléversement dans le contenu de l’email.');
                return false;
            }
            
            const sendEmail = document.getElementById('send_email').checked;
            const sendWhatsApp = document.getElementById('send_whatsapp').checked;
            
            if (!sendEmail && !sendWhatsApp) {
                showModernAlert('Veuillez sélectionner au moins un canal d\'envoi (Email ou WhatsApp)');
                return false;
            }
            
            const recipientTypeSelect = document.getElementById('recipient_type');
            const type = recipientTypeSelect.value;
            let isValid = true;
            
            if (type === 'role') {
                const checkedRoles = document.querySelectorAll('input[name="roles[]"]:checked');
                if (checkedRoles.length === 0) {
                    showModernAlert('Veuillez sélectionner au moins un rôle');
                    isValid = false;
                }
            } else if (type === 'course') {
                const courseId = document.getElementById('content_id')?.value;
                if (!courseId) {
                    showModernAlert('Veuillez sélectionner un contenu');
                    isValid = false;
                }
            } else if (type === 'category') {
                const categoryId = document.getElementById('category_id')?.value;
                if (!categoryId) {
                    showModernAlert('Veuillez sélectionner une catégorie');
                    isValid = false;
                }
            } else if (type === 'provider') {
                const providerId = document.getElementById('provider_id')?.value;
                if (!providerId) {
                    showModernAlert('Veuillez sélectionner un prestataire');
                    isValid = false;
                }
            } else if (type === 'downloaded_free') {
                // downloaded_content_id optionnel - tous les contenus gratuits si vide
            } else if (type === 'purchased') {
                const purchaseType = document.getElementById('purchase_type')?.value;
                const purchasedContentId = document.getElementById('purchased_content_id')?.value;
                if (!purchaseType) {
                    showModernAlert('Veuillez sélectionner un type d\'achat');
                    isValid = false;
                } else if (purchaseType === 'specific_content' && !purchasedContentId) {
                    showModernAlert('Veuillez sélectionner un contenu acheté');
                    isValid = false;
                }
            } else if (type === 'purchased_content') {
                const purchasedContentId = document.getElementById('purchased_content_id')?.value;
                if (!purchasedContentId) {
                    showModernAlert('Veuillez sélectionner un contenu acheté');
                    isValid = false;
                }
            } else if (type === 'failed_payment') {
                // Pas de paramètre supplémentaire
            } else if (type === 'registration_date') {
                const dateFrom = document.getElementById('registration_date_from')?.value;
                const dateTo = document.getElementById('registration_date_to')?.value;
                if (!dateFrom && !dateTo) {
                    showModernAlert('Veuillez sélectionner au moins une date');
                    isValid = false;
                }
            } else if (type === 'activity') {
                const activityType = document.getElementById('activity_type')?.value;
                if (!activityType) {
                    showModernAlert('Veuillez sélectionner un type d\'activité');
                    isValid = false;
                }
            } else if (type === 'single') {
                const singleUserId = document.getElementById('single_user_id');
                if (!singleUserId || !singleUserId.value) {
                    showModernAlert('Veuillez sélectionner un utilisateur');
                    isValid = false;
                }
            } else if (type === 'selected') {
                if (!selectedUsers || selectedUsers.length === 0) {
                    showModernAlert('Veuillez sélectionner au moins un utilisateur');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                return false;
            }
            
            // Synchroniser le contenu Quill avec le textarea avant soumission
            const textarea = document.getElementById('email_content');
            if (textarea && quill) {
                textarea.value = quill.root.innerHTML;
            }
            
            // Désactiver le bouton et afficher le modal de chargement
            const sendBtn = document.getElementById('send_btn');
            if (sendBtn) {
                sendBtn.disabled = true;
                sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Envoi en cours...';
            }
            
            // Afficher le modal de chargement
            showLoadingModal();
            
            // Préparer les données du formulaire
            const formData = new FormData(sendCombinedForm);
            
            // Envoyer via AJAX
            fetch(sendCombinedForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.redirected) {
                    // Redirection détectée, suivre la redirection
                    window.location.href = response.url;
                } else {
                    return response.text();
                }
            })
            .then(data => {
                hideLoadingModal();
                if (data) {
                    // Si pas de redirection, parser la réponse
                    try {
                        const json = JSON.parse(data);
                        if (json.redirect) {
                            window.location.href = json.redirect;
                        }
                    } catch (e) {
                        // Si ce n'est pas du JSON, c'est probablement du HTML de redirection
                        window.location.href = '{{ route("admin.announcements") }}';
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                hideLoadingModal();
                showModernAlert('Une erreur est survenue lors de l\'envoi. Veuillez réessayer.');
                if (sendBtn) {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Envoyer';
                }
            });
        });
    }
    
    // Initialiser
    if (window.updateSelectedUsersDisplay) {
        window.updateSelectedUsersDisplay();
    }
    if (window.updateRecipientCount) {
        setTimeout(() => window.updateRecipientCount(), 500);
    }
});
</script>
@endpush

