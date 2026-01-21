@extends('layouts.admin')

@section('title', 'Envoyer un email')
@section('admin-title', 'Envoyer un email')
@section('admin-subtitle', 'Rédigez et envoyez des emails à vos utilisateurs')

@section('admin-actions')
    <a href="{{ route('admin.announcements') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour aux annonces
    </a>
@endsection

@section('admin-content')
<div class="admin-panel">
    <div class="admin-panel__body">
        @if(isset($isTestMode) && $isTestMode)
        <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>⚠️ Mode de test activé:</strong> Le mailer est configuré en mode <code>{{ $mailerTransport ?? 'log' }}</code>.
            <br>
            <small>Les emails seront enregistrés dans les logs mais <strong>ne seront PAS envoyés réellement</strong> aux destinataires.</small>
            <br>
            <small>Pour activer l'envoi réel, configurez SMTP dans votre fichier <code>.env</code> avec <code>MAIL_MAILER=smtp</code>.</small>
        </div>
        @endif
        
        <form id="sendEmailForm" method="POST" action="{{ route('admin.announcements.send-email') }}" enctype="multipart/form-data">
            @csrf

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
                    <label class="form-label">Cours *</label>
                    <select class="form-select" id="content_id" name="content_id">
                        <option value="">Sélectionner un cours</option>
                        @foreach(\App\Models\Course::where('is_published', true)->orderBy('title')->get() as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Seuls les utilisateurs inscrits à ce contenu recevront l'email</small>
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
                    <small class="text-muted">Seuls les utilisateurs inscrits à des contenus de cette catégorie recevront l'email</small>
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
                    <small class="text-muted">Seuls les utilisateurs inscrits à des contenus de ce prestataire recevront l'email</small>
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

                <!-- Sélection par téléchargement de contenu gratuit -->
                <div class="mb-3" id="downloaded_free_selection" style="display: none;">
                    <label class="form-label">Contenu téléchargeable gratuit *</label>
                    <select class="form-select" id="downloaded_content_id" name="downloaded_content_id">
                        <option value="">Tous les contenus téléchargeables gratuits</option>
                        @foreach(\App\Models\Course::where('is_published', true)->where('is_downloadable', true)->where('is_free', true)->orderBy('title')->get() as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Seuls les utilisateurs ayant téléchargé au moins une fois ce contenu (ou tous les contenus téléchargeables gratuits si aucun n'est sélectionné) recevront l'email</small>
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
                    <small class="text-muted">Filtrez les utilisateurs selon leurs achats</small>
                </div>

                <!-- Sélection par contenu acheté -->
                <div class="mb-3" id="purchased_content_selection" style="display: none;">
                    <label class="form-label">Contenu acheté *</label>
                    <select class="form-select" id="purchased_content_id" name="purchased_content_id">
                        <option value="">Sélectionner un contenu</option>
                        @foreach(\App\Models\Course::where('is_published', true)->where('is_free', false)->orderBy('title')->get() as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Seuls les utilisateurs ayant acheté ce contenu recevront l'email</small>
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
                    <input type="text" class="form-control" id="user_search" placeholder="Rechercher par nom ou email (minimum 2 caractères)...">
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

            <!-- Contenu de l'email -->
            <div class="admin-form-card mb-4">
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
                </div>

                <div class="mb-3">
                    <label class="form-label">Pièces jointes</label>
                    <input type="file" class="form-control" name="attachments[]" id="attachments" multiple 
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar">
                    <small class="form-text text-muted">Vous pouvez sélectionner plusieurs fichiers. Formats acceptés: PDF, Word, Excel, PowerPoint, Images, ZIP, RAR</small>
                    <div id="attachments_preview" class="mt-2"></div>
                </div>
            </div>

            <!-- Options d'envoi -->
            <div class="admin-form-card mb-4">
                <h5 class="mb-3"><i class="fas fa-cog me-2"></i>Options d'envoi</h5>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="send_type" id="send_now" value="now" checked>
                        <label class="form-check-label" for="send_now">
                            <strong>Envoyer immédiatement</strong>
                            <small class="d-block text-muted">L'email sera envoyé dès que vous soumettez le formulaire</small>
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="send_type" id="send_scheduled" value="scheduled">
                        <label class="form-check-label" for="send_scheduled">
                            <strong>Programmer l'envoi</strong>
                            <small class="d-block text-muted">L'email sera envoyé à une date et heure spécifiques</small>
                        </label>
                    </div>
                </div>

                <div class="mb-3" id="scheduled_date_section" style="display: none;">
                    <label class="form-label">Date et heure d'envoi</label>
                    <input type="datetime-local" class="form-control" name="scheduled_at" id="scheduled_at" 
                           min="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="d-flex gap-2 justify-content-end action-buttons-container">
                <a href="{{ route('admin.announcements') }}" class="btn btn-light">Annuler</a>
                <button type="button" class="btn btn-secondary" id="preview_btn">
                    <i class="fas fa-eye me-2"></i>Aperçu
                </button>
                <button type="submit" class="btn btn-primary" id="send_btn">
                    <i class="fas fa-paper-plane me-2"></i>Envoyer l'email
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
                <p class="text-muted mb-0">Veuillez patienter pendant l'envoi des emails. Cela peut prendre quelques instants.</p>
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

<!-- Modal d'aperçu -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aperçu de l'email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="email_preview_content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter un bouton d'action -->
<div class="modal fade" id="actionButtonModal" tabindex="-1" aria-labelledby="actionButtonModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="actionButtonModalLabel">
                    <i class="fas fa-mouse-pointer me-2"></i>Ajouter un bouton d'action
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="actionButtonForm">
                    <div class="mb-3">
                        <label for="button_url" class="form-label">
                            <i class="fas fa-link me-2"></i>URL du bouton <span class="text-danger">*</span>
                        </label>
                        <input type="url" class="form-control" id="button_url" placeholder="https://exemple.com" required>
                        <small class="form-text text-muted">L'URL vers laquelle le bouton redirigera</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="button_text" class="form-label">
                            <i class="fas fa-font me-2"></i>Texte du bouton <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="button_text" placeholder="Cliquez ici" required>
                        <small class="form-text text-muted">Le texte affiché sur le bouton</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="button_type" class="form-label">
                            <i class="fas fa-palette me-2"></i>Type de bouton
                        </label>
                        <select class="form-select" id="button_type">
                            <option value="primary" selected>Primary (Bleu foncé)</option>
                            <option value="secondary">Secondary (Gris)</option>
                            <option value="success">Success (Vert)</option>
                            <option value="danger">Danger (Rouge)</option>
                        </select>
                        <small class="form-text text-muted">Choisissez le style du bouton</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Aperçu</label>
                        <div id="button_preview" class="p-3 border rounded bg-light text-center">
                            <a href="#" id="preview_button" class="action-button primary" style="display: inline-block; padding: 12px 24px; margin: 0; background-color: #003366; color: #ffffff !important; text-decoration: none !important; border-radius: 6px; font-weight: 600; text-align: center; pointer-events: none;">Cliquez ici</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-primary" id="insert_action_button">
                    <i class="fas fa-plus me-2"></i>Insérer le bouton
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
/* Style pour le bouton d'action dans la toolbar Quill */
.ql-action-button {
    width: 28px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: none;
    background: transparent;
    padding: 0;
}
.ql-action-button:hover {
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 3px;
}
.ql-action-button svg {
    width: 18px;
    height: 18px;
}
.ql-action-button svg .ql-stroke {
    stroke: currentColor;
    stroke-width: 1;
}

/* Styles pour le modal de bouton d'action */
#actionButtonModal .modal-header {
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}
#actionButtonModal .modal-footer {
    border-top: 1px solid #dee2e6;
}
#button_preview {
    min-height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}
#preview_button {
    cursor: default;
}
#preview_button:hover {
    opacity: 0.9;
}
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
<script>
// Variables globales
let selectedUsers = [];
let quill;

// Initialiser Quill Editor quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
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
    
    // Upload d'images
    const toolbar = quill.getModule('toolbar');
    toolbar.addHandler('image', function() {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();
        
        input.onchange = function() {
            var file = input.files[0];
            if (file) {
                var formData = new FormData();
                formData.append('file', file);
                formData.append('_token', '{{ csrf_token() }}');
                
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route("admin.announcements.upload-image") }}');
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var json = JSON.parse(xhr.responseText);
                            if (json.location) {
                                var range = quill.getSelection(true);
                                quill.insertEmbed(range.index, 'image', json.location);
                                quill.setSelection(range.index + 1);
                            } else if (json.error) {
                                alert('Erreur: ' + json.error);
                            } else {
                                alert('Réponse inattendue du serveur');
                            }
                        } catch (e) {
                            console.error('Erreur de parsing JSON:', e);
                            alert('Erreur lors du traitement de la réponse du serveur');
                        }
                    } else {
                        try {
                            var errorJson = JSON.parse(xhr.responseText);
                            alert('Erreur: ' + (errorJson.error || 'Erreur lors du téléchargement de l\'image'));
                        } catch (e) {
                            alert('Erreur lors du téléchargement de l\'image (Code: ' + xhr.status + ')');
                        }
                    }
                };
                
                xhr.onerror = function() {
                    alert('Erreur réseau lors du téléchargement de l\'image');
                };
                
                xhr.send(formData);
            }
        };
    });
    
    // Ajouter un bouton personnalisé pour les boutons d'action après que Quill soit complètement initialisé
    setTimeout(function() {
        const toolbarContainer = document.querySelector('.ql-toolbar');
        if (toolbarContainer) {
            // Créer un bouton personnalisé
            const actionButtonContainer = document.createElement('span');
            actionButtonContainer.className = 'ql-formats';
            actionButtonContainer.innerHTML = `
                <button type="button" class="ql-action-button" title="Insérer un bouton d'action">
                    <svg viewBox="0 0 18 18">
                        <rect class="ql-stroke" x="3" y="4" width="12" height="10" rx="2"></rect>
                        <text x="9" y="11" text-anchor="middle" font-size="8" fill="currentColor">BTN</text>
                    </svg>
                </button>
            `;
            toolbarContainer.appendChild(actionButtonContainer);
            
            // Ajouter l'événement click
            const actionButton = actionButtonContainer.querySelector('.ql-action-button');
            if (actionButton) {
                actionButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Ouvrir le modal moderne - utiliser getInstance ou créer une nouvelle instance
                    const modalElement = document.getElementById('actionButtonModal');
                    if (modalElement) {
                        let modal = bootstrap.Modal.getInstance(modalElement);
                        if (!modal) {
                            modal = new bootstrap.Modal(modalElement, {
                                backdrop: true,
                                keyboard: true,
                                focus: true
                            });
                        }
                        modal.show();
                    }
                });
            }
        }
    }, 100);

    // Synchroniser le contenu avec le textarea pour le formulaire
    quill.on('text-change', function() {
        const textarea = document.getElementById('email_content');
        if (textarea) {
            textarea.value = quill.root.innerHTML;
        }
    });

    // Initialiser le textarea avec le contenu par défaut
    const textarea = document.getElementById('email_content');
    if (textarea) {
        textarea.value = quill.root.innerHTML;
    }
    
    // Fonction pour initialiser l'affichage selon le type de destinataire
    function updateRecipientSections(type) {
        // Récupérer tous les éléments
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
        
        // Masquer toutes les sections d'abord en utilisant setAttribute pour forcer
        if (roleSelection) {
            roleSelection.setAttribute('style', 'display: none !important;');
        }
        if (courseSelection) {
            courseSelection.setAttribute('style', 'display: none !important;');
        }
        if (categorySelection) {
            categorySelection.setAttribute('style', 'display: none !important;');
        }
        if (providerSelection) {
            providerSelection.setAttribute('style', 'display: none !important;');
        }
        if (downloadedFreeSelection) {
            downloadedFreeSelection.setAttribute('style', 'display: none !important;');
        }
        if (purchasedSelection) {
            purchasedSelection.setAttribute('style', 'display: none !important;');
        }
        if (purchasedContentSelection) {
            purchasedContentSelection.setAttribute('style', 'display: none !important;');
        }
        if (registrationDateSelection) {
            registrationDateSelection.setAttribute('style', 'display: none !important;');
        }
        if (activitySelection) {
            activitySelection.setAttribute('style', 'display: none !important;');
        }
        if (singleUserSelection) {
            singleUserSelection.setAttribute('style', 'display: none !important;');
        }
        if (multipleUsersSelection) {
            multipleUsersSelection.setAttribute('style', 'display: none !important;');
        }
        
        // Afficher la section appropriée selon le type
        if (type === 'role' && roleSelection) {
            roleSelection.setAttribute('style', 'display: block !important;');
        } else if (type === 'course' && courseSelection) {
            courseSelection.setAttribute('style', 'display: block !important;');
        } else if (type === 'category' && categorySelection) {
            categorySelection.setAttribute('style', 'display: block !important;');
        } else if (type === 'provider' && providerSelection) {
            providerSelection.setAttribute('style', 'display: block !important;');
        } else if (type === 'downloaded_free' && downloadedFreeSelection) {
            downloadedFreeSelection.setAttribute('style', 'display: block !important;');
        } else if (type === 'purchased' && purchasedSelection) {
            purchasedSelection.setAttribute('style', 'display: block !important;');
        } else if (type === 'registration_date' && registrationDateSelection) {
            registrationDateSelection.setAttribute('style', 'display: block !important;');
        } else if (type === 'activity' && activitySelection) {
            activitySelection.setAttribute('style', 'display: block !important;');
        } else if (type === 'single' && singleUserSelection) {
            singleUserSelection.setAttribute('style', 'display: block !important;');
        } else if (type === 'selected' && multipleUsersSelection) {
            multipleUsersSelection.setAttribute('style', 'display: block !important;');
        }
        // Pour 'all', aucune section supplémentaire n'est affichée
    }
    
    // Rendre la fonction accessible globalement
    window.updateRecipientSections = updateRecipientSections;
    
    // Gestion du type de destinataire
    const recipientTypeSelect = document.getElementById('recipient_type');
    if (recipientTypeSelect) {
        // Initialiser l'affichage au chargement de la page
        const initialType = recipientTypeSelect.value || 'all';
        updateRecipientSections(initialType);
        
        // Mettre à jour le compte si nécessaire au chargement
        if (initialType === 'all' || initialType === 'role' || initialType === 'course' || initialType === 'category' || initialType === 'provider' || initialType === 'downloaded_free' || initialType === 'purchased' || initialType === 'registration_date' || initialType === 'activity') {
            setTimeout(function() {
                if (window.updateRecipientCount) {
                    window.updateRecipientCount();
                }
            }, 500);
        }
        
        // Gérer le changement de type
        recipientTypeSelect.addEventListener('change', function() {
            const type = this.value;
            
            // Réinitialiser les champs de recherche
            const singleUserId = document.getElementById('single_user_id');
            const userSearch = document.getElementById('user_search');
            const userSearchResults = document.getElementById('user_search_results');
            const multipleUserSearch = document.getElementById('multiple_user_search');
            const multipleUserSearchResults = document.getElementById('multiple_user_search_results');
            
            if (singleUserId) singleUserId.value = '';
            if (userSearch) {
                userSearch.value = '';
            }
            if (userSearchResults) {
                userSearchResults.innerHTML = '';
                userSearchResults.style.display = 'none';
            }
            if (multipleUserSearch) {
                multipleUserSearch.value = '';
            }
            if (multipleUserSearchResults) {
                multipleUserSearchResults.innerHTML = '';
                multipleUserSearchResults.style.display = 'none';
            }
            
            // Réinitialiser le contenu
            const courseId = document.getElementById('content_id');
            if (courseId) {
                courseId.value = '';
            }
            
            // Réinitialiser la catégorie
            const categoryId = document.getElementById('category_id');
            if (categoryId) {
                categoryId.value = '';
            }
            
            // Réinitialiser le prestataire
            const providerId = document.getElementById('provider_id');
            if (providerId) {
                providerId.value = '';
            }
            
            // Réinitialiser le contenu téléchargé
            const downloadedContentId = document.getElementById('downloaded_content_id');
            if (downloadedContentId) {
                downloadedContentId.value = '';
            }
            
            // Réinitialiser le type d'achat
            const purchaseType = document.getElementById('purchase_type');
            if (purchaseType) {
                purchaseType.value = '';
            }
            
            // Réinitialiser le contenu acheté
            const purchasedContentId = document.getElementById('purchased_content_id');
            if (purchasedContentId) {
                purchasedContentId.value = '';
            }
            
            // Réinitialiser les dates d'inscription
            const registrationDateFrom = document.getElementById('registration_date_from');
            const registrationDateTo = document.getElementById('registration_date_to');
            if (registrationDateFrom) {
                registrationDateFrom.value = '';
            }
            if (registrationDateTo) {
                registrationDateTo.value = '';
            }
            
            // Réinitialiser l'activité
            const activityType = document.getElementById('activity_type');
            if (activityType) {
                activityType.value = '';
            }
            
            // Décocher tous les rôles
            document.querySelectorAll('input[name="roles[]"]').forEach(function(cb) {
                cb.checked = false;
            });
            
            // Réinitialiser les utilisateurs sélectionnés si on change de type
            if (type !== 'selected') {
                selectedUsers = [];
                if (window.updateSelectedUsersDisplay) {
                    window.updateSelectedUsersDisplay();
                }
            }
            
            // Mettre à jour l'affichage des sections
            updateRecipientSections(type);
            
            // Mettre à jour le compte après un court délai pour permettre au DOM de se mettre à jour
            setTimeout(function() {
                if (window.updateRecipientCount) {
                    window.updateRecipientCount();
                }
            }, 100);
        });
    }
    
    // Attacher les événements pour les rôles
    document.querySelectorAll('input[name="roles[]"]').forEach(cb => {
        cb.addEventListener('change', function() {
            updateRecipientCount();
        });
    });
    
    // Attacher les événements pour les nouveaux filtres
    const courseSelect = document.getElementById('content_id');
    if (courseSelect) {
        courseSelect.addEventListener('change', function() {
            updateRecipientCount();
        });
    }
    
    const categorySelect = document.getElementById('category_id');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            updateRecipientCount();
        });
    }
    
    const providerSelect = document.getElementById('provider_id');
    if (providerSelect) {
        providerSelect.addEventListener('change', function() {
            updateRecipientCount();
        });
    }
    
    const downloadedContentSelect = document.getElementById('downloaded_content_id');
    if (downloadedContentSelect) {
        downloadedContentSelect.addEventListener('change', function() {
            updateRecipientCount();
        });
    }
    
    const purchaseTypeSelect = document.getElementById('purchase_type');
    if (purchaseTypeSelect) {
        purchaseTypeSelect.addEventListener('change', function() {
            const purchasedContentSelection = document.getElementById('purchased_content_selection');
            if (this.value === 'specific_content') {
                if (purchasedContentSelection) {
                    purchasedContentSelection.setAttribute('style', 'display: block !important;');
                }
            } else {
                if (purchasedContentSelection) {
                    purchasedContentSelection.setAttribute('style', 'display: none !important;');
                }
                const purchasedContentId = document.getElementById('purchased_content_id');
                if (purchasedContentId) {
                    purchasedContentId.value = '';
                }
            }
            updateRecipientCount();
        });
    }
    
    const purchasedContentSelect = document.getElementById('purchased_content_id');
    if (purchasedContentSelect) {
        purchasedContentSelect.addEventListener('change', function() {
            updateRecipientCount();
        });
    }
    
    const registrationDateFrom = document.getElementById('registration_date_from');
    const registrationDateTo = document.getElementById('registration_date_to');
    if (registrationDateFrom) {
        registrationDateFrom.addEventListener('change', function() {
            updateRecipientCount();
        });
    }
    if (registrationDateTo) {
        registrationDateTo.addEventListener('change', function() {
            updateRecipientCount();
        });
    }
    
    const activityTypeSelect = document.getElementById('activity_type');
    if (activityTypeSelect) {
        activityTypeSelect.addEventListener('change', function() {
            updateRecipientCount();
        });
    }

    // Fonction de recherche d'utilisateurs
    window.searchUsers = function(query, type = 'single') {
        const resultsDivId = type === 'single' ? 'user_search_results' : 'multiple_user_search_results';
        const resultsDiv = document.getElementById(resultsDivId);
        
        if (!resultsDiv) return;
        
        if (!query || query.length < 2) {
            resultsDiv.innerHTML = '';
            resultsDiv.style.display = 'none';
            return;
        }
        
        // Afficher un indicateur de chargement
        resultsDiv.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Recherche...</p>';
        resultsDiv.style.display = 'block';
        
        fetch(`{{ route('admin.announcements.search-users') }}?q=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (!Array.isArray(data)) {
                    console.error('Réponse invalide:', data);
                    resultsDiv.innerHTML = '<p class="text-danger">Erreur lors de la recherche</p>';
                    return;
                }
                
                if (data.length === 0) {
                    resultsDiv.innerHTML = '<p class="text-muted">Aucun utilisateur trouvé</p>';
                    return;
                }
                
                if (type === 'single') {
                    resultsDiv.innerHTML = data.map(user => {
                        const safeName = (user.name || 'Utilisateur sans nom').replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, ' ');
                        const safeEmail = (user.email || 'Pas d\'email').replace(/"/g, '&quot;');
                        return `
                            <div class="list-group-item list-group-item-action" style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #dee2e6;" 
                                 onclick="selectSingleUser(${user.id}, '${safeName}', '${safeEmail}')">
                                <strong>${user.name || 'Utilisateur sans nom'}</strong><br>
                                <small class="text-muted">${user.email || 'Pas d\'email'}</small>
                            </div>
                        `;
                    }).join('');
                } else {
                    resultsDiv.innerHTML = data.map(user => {
                        // Ne pas afficher les utilisateurs déjà sélectionnés
                        if (selectedUsers && selectedUsers.find(u => u.id === user.id)) {
                            return '';
                        }
                        const safeName = (user.name || 'Utilisateur sans nom').replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, ' ');
                        const safeEmail = (user.email || 'Pas d\'email').replace(/"/g, '&quot;');
                        return `
                            <div class="list-group-item list-group-item-action" style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #dee2e6;" 
                                 onclick="addSelectedUser(${user.id}, '${safeName}', '${safeEmail}')">
                                <strong>${user.name || 'Utilisateur sans nom'}</strong><br>
                                <small class="text-muted">${user.email || 'Pas d\'email'}</small>
                            </div>
                        `;
                    }).filter(html => html !== '').join('') || '<p class="text-muted">Tous les utilisateurs trouvés sont déjà sélectionnés</p>';
                }
            })
            .catch(error => {
                console.error('Erreur lors de la recherche:', error);
                resultsDiv.innerHTML = '<p class="text-danger">Erreur lors de la recherche. Veuillez réessayer.</p>';
            });
    };
    
    // Attacher les événements de recherche
    const userSearchInput = document.getElementById('user_search');
    const multipleUserSearchInput = document.getElementById('multiple_user_search');
    
    if (userSearchInput) {
        let searchTimeout;
        userSearchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchUsers(this.value, 'single');
            }, 300);
        });
    }
    
    if (multipleUserSearchInput) {
        let searchTimeout;
        multipleUserSearchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchUsers(this.value, 'multiple');
            }, 300);
        });
    }

    
    // Fonctions globales pour la sélection d'utilisateurs
    window.selectSingleUser = function(id, name, email) {
        const singleUserId = document.getElementById('single_user_id');
        const userSearch = document.getElementById('user_search');
        const userSearchResults = document.getElementById('user_search_results');
        
        if (singleUserId) singleUserId.value = id;
        if (userSearch) userSearch.value = `${name} (${email})`;
        if (userSearchResults) {
            userSearchResults.innerHTML = '';
            userSearchResults.style.display = 'none';
        }
        updateRecipientCount();
    };
    
    window.addSelectedUser = function(id, name, email) {
        if (!selectedUsers) selectedUsers = [];
        if (selectedUsers.find(u => u.id === id)) {
            return; // Utilisateur déjà sélectionné
        }
        
        selectedUsers.push({ id, name, email });
        updateSelectedUsersDisplay();
        
        // Réinitialiser le champ de recherche et les résultats
        const searchInput = document.getElementById('multiple_user_search');
        const resultsDiv = document.getElementById('multiple_user_search_results');
        if (searchInput) searchInput.value = '';
        if (resultsDiv) {
            resultsDiv.innerHTML = '';
            resultsDiv.style.display = 'none';
        }
        updateRecipientCount();
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
            container.innerHTML = '<p class="text-muted">Aucun utilisateur sélectionné. Recherchez et sélectionnez des utilisateurs ci-dessus.</p>';
            if (idsInput) idsInput.value = '';
        } else {
            container.innerHTML = selectedUsers.map(user => {
                const safeName = (user.name || 'Utilisateur sans nom').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const safeEmail = (user.email || 'Pas d\'email').replace(/"/g, '&quot;');
                return `
                    <span class="user-badge">
                        ${safeName} (${safeEmail})
                        <button type="button" onclick="removeSelectedUser(${user.id})" style="background: none; border: none; color: #dc3545; cursor: pointer; padding: 0; font-size: 18px; line-height: 1;">&times;</button>
                    </span>
                `;
            }).join('');
            if (idsInput) idsInput.value = selectedUsers.map(u => u.id).join(',');
        }
        if (window.updateRecipientCount) {
            window.updateRecipientCount();
        }
    };

    
    // Fonction pour mettre à jour le compte de destinataires
    window.updateRecipientCount = function() {
        const recipientTypeSelect = document.getElementById('recipient_type');
        const countDiv = document.getElementById('recipient_count');
        const countText = document.getElementById('recipient_count_text');
        
        if (!recipientTypeSelect || !countDiv || !countText) {
            return;
        }
        
        const type = recipientTypeSelect.value;
        
        if (type === 'all') {
            fetch('{{ route("admin.announcements.count-users") }}?type=all')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && typeof data.count !== 'undefined') {
                        countText.textContent = `${data.count} utilisateur(s) recevront cet email`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du comptage:', error);
                    countText.textContent = 'Impossible de compter les utilisateurs';
                    countDiv.style.display = 'block';
                });
        } else if (type === 'course') {
            const courseId = document.getElementById('content_id')?.value;
            if (!courseId) {
                countText.textContent = 'Veuillez sélectionner un contenu';
                countDiv.style.display = 'block';
                return;
            }
            fetch(`{{ route("admin.announcements.count-users") }}?type=course&content_id=${courseId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && typeof data.count !== 'undefined') {
                        countText.textContent = `${data.count} utilisateur(s) inscrit(s) à ce contenu recevront cet email`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du comptage:', error);
                    countText.textContent = 'Impossible de compter les utilisateurs';
                    countDiv.style.display = 'block';
                });
        } else if (type === 'category') {
            const categoryId = document.getElementById('category_id')?.value;
            if (!categoryId) {
                countText.textContent = 'Veuillez sélectionner une catégorie';
                countDiv.style.display = 'block';
                return;
            }
            fetch(`{{ route("admin.announcements.count-users") }}?type=category&category_id=${categoryId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && typeof data.count !== 'undefined') {
                        countText.textContent = `${data.count} utilisateur(s) inscrit(s) à des contenus de cette catégorie recevront cet email`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du comptage:', error);
                    countText.textContent = 'Impossible de compter les utilisateurs';
                    countDiv.style.display = 'block';
                });
        } else if (type === 'provider') {
            const providerId = document.getElementById('provider_id')?.value;
            if (!providerId) {
                countText.textContent = 'Veuillez sélectionner un prestataire';
                countDiv.style.display = 'block';
                return;
            }
            fetch(`{{ route("admin.announcements.count-users") }}?type=provider&provider_id=${providerId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && typeof data.count !== 'undefined') {
                        countText.textContent = `${data.count} utilisateur(s) inscrit(s) à des contenus de ce prestataire recevront cet email`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du comptage:', error);
                    countText.textContent = 'Impossible de compter les utilisateurs';
                    countDiv.style.display = 'block';
                });
        } else if (type === 'registration_date') {
            const dateFrom = document.getElementById('registration_date_from')?.value;
            const dateTo = document.getElementById('registration_date_to')?.value;
            if (!dateFrom && !dateTo) {
                countText.textContent = 'Veuillez sélectionner au moins une date';
                countDiv.style.display = 'block';
                return;
            }
            let url = `{{ route("admin.announcements.count-users") }}?type=registration_date`;
            if (dateFrom) url += `&registration_date_from=${dateFrom}`;
            if (dateTo) url += `&registration_date_to=${dateTo}`;
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && typeof data.count !== 'undefined') {
                        countText.textContent = `${data.count} utilisateur(s) inscrit(s) dans cette période recevront cet email`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du comptage:', error);
                    countText.textContent = 'Impossible de compter les utilisateurs';
                    countDiv.style.display = 'block';
                });
        } else if (type === 'activity') {
            const activityType = document.getElementById('activity_type')?.value;
            if (!activityType) {
                countText.textContent = 'Veuillez sélectionner un type d\'activité';
                countDiv.style.display = 'block';
                return;
            }
            fetch(`{{ route("admin.announcements.count-users") }}?type=activity&activity_type=${activityType}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && typeof data.count !== 'undefined') {
                        let activityLabel = '';
                        switch(activityType) {
                            case 'active_recent':
                                activityLabel = 'actifs récemment (7 derniers jours)';
                                break;
                            case 'active_month':
                                activityLabel = 'actifs ce mois';
                                break;
                            case 'active_3months':
                                activityLabel = 'actifs (3 derniers mois)';
                                break;
                            case 'inactive_30days':
                                activityLabel = 'inactifs (30+ jours)';
                                break;
                            case 'inactive_90days':
                                activityLabel = 'inactifs (90+ jours)';
                                break;
                            case 'never_logged':
                                activityLabel = 'jamais connectés';
                                break;
                            default:
                                activityLabel = 'correspondant à ce critère';
                        }
                        countText.textContent = `${data.count} utilisateur(s) ${activityLabel} recevront cet email`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du comptage:', error);
                    countText.textContent = 'Impossible de compter les utilisateurs';
                    countDiv.style.display = 'block';
                });
        } else if (type === 'role') {
            const roles = Array.from(document.querySelectorAll('input[name="roles[]"]:checked')).map(cb => cb.value);
            if (roles.length > 0) {
                fetch(`{{ route("admin.announcements.count-users") }}?type=role&roles=${roles.join(',')}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur réseau: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            countText.textContent = `${data.count} utilisateur(s) recevront cet email`;
                            countDiv.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors du comptage:', error);
                        countText.textContent = 'Impossible de compter les utilisateurs';
                        countDiv.style.display = 'block';
                    });
            } else {
                countDiv.style.display = 'none';
            }
        } else if (type === 'single') {
            const userIdInput = document.getElementById('single_user_id');
            if (userIdInput && userIdInput.value) {
                countText.textContent = '1 utilisateur recevra cet email';
                countDiv.style.display = 'block';
            } else {
                countDiv.style.display = 'none';
            }
        } else if (type === 'selected') {
            if (selectedUsers && selectedUsers.length > 0) {
                countText.textContent = `${selectedUsers.length} utilisateur(s) recevront cet email`;
                countDiv.style.display = 'block';
            } else {
                countDiv.style.display = 'none';
            }
        }
    };

    
    // Gestion de l'envoi programmé
    const sendScheduled = document.getElementById('send_scheduled');
    const sendNow = document.getElementById('send_now');
    const scheduledDateSection = document.getElementById('scheduled_date_section');
    
    if (sendScheduled && scheduledDateSection) {
        sendScheduled.addEventListener('change', function() {
            if (this.checked) {
                scheduledDateSection.style.display = 'block';
            }
        });
    }
    
    if (sendNow && scheduledDateSection) {
        sendNow.addEventListener('change', function() {
            if (this.checked) {
                scheduledDateSection.style.display = 'none';
            }
        });
    }
    
    // Prévisualisation des pièces jointes
    const attachmentsInput = document.getElementById('attachments');
    if (attachmentsInput) {
        attachmentsInput.addEventListener('change', function() {
            const preview = document.getElementById('attachments_preview');
            if (!preview) return;
            preview.innerHTML = '';
            
            Array.from(this.files).forEach(file => {
                const div = document.createElement('div');
                div.className = 'attachment-preview';
                div.innerHTML = `
                    <i class="fas fa-file"></i>
                    <span>${file.name}</span>
                    <small class="text-muted">(${(file.size / 1024).toFixed(2)} KB)</small>
                `;
                preview.appendChild(div);
            });
        });
    }
    
    // Aperçu de l'email
    const previewBtn = document.getElementById('preview_btn');
    if (previewBtn && quill) {
        previewBtn.addEventListener('click', function() {
            const content = quill.root.innerHTML;
            const previewContent = document.getElementById('email_preview_content');
            if (previewContent) {
                previewContent.innerHTML = content || '<p>Aucun contenu à prévisualiser</p>';
            }
            
            const modalElement = document.getElementById('previewModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        });
    }
    
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
    
    // Synchroniser le contenu avant la soumission du formulaire avec AJAX
    const sendEmailForm = document.getElementById('sendEmailForm');
    if (sendEmailForm && quill) {
        sendEmailForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // S'assurer que le contenu est synchronisé
            const emailContent = document.getElementById('email_content');
            if (emailContent) {
                emailContent.value = quill.root.innerHTML;
            }
            
            // Valider que le contenu n'est pas vide
            if (!quill.getText().trim() && !quill.root.innerHTML.match(/<img|<iframe/)) {
                alert('Veuillez rédiger un contenu pour votre email.');
                return false;
            }
            
            const recipientTypeSelect = document.getElementById('recipient_type');
            if (!recipientTypeSelect) {
                alert('Erreur : type de destinataire non défini');
                return false;
            }
            
            const type = recipientTypeSelect.value;
            let isValid = true;
            
            if (type === 'role') {
                const checkedRoles = document.querySelectorAll('input[name="roles[]"]:checked');
                if (checkedRoles.length === 0) {
                    alert('Veuillez sélectionner au moins un rôle');
                    isValid = false;
                }
            } else if (type === 'course') {
                const courseId = document.getElementById('content_id')?.value;
                if (!courseId) {
                    alert('Veuillez sélectionner un contenu');
                    isValid = false;
                }
            } else if (type === 'category') {
                const categoryId = document.getElementById('category_id')?.value;
                if (!categoryId) {
                    alert('Veuillez sélectionner une catégorie');
                    isValid = false;
                }
            } else if (type === 'provider') {
                const providerId = document.getElementById('provider_id')?.value;
                if (!providerId) {
                    alert('Veuillez sélectionner un prestataire');
                    isValid = false;
                }
            } else if (type === 'downloaded_free') {
                // Le contenu téléchargé est optionnel (si vide, tous les contenus téléchargeables gratuits)
                // Pas de validation nécessaire
            } else if (type === 'purchased') {
                const purchaseType = document.getElementById('purchase_type')?.value;
                if (!purchaseType) {
                    alert('Veuillez sélectionner un type d\'achat');
                    isValid = false;
                } else if (purchaseType === 'specific_content') {
                    const purchasedContentId = document.getElementById('purchased_content_id')?.value;
                    if (!purchasedContentId) {
                        alert('Veuillez sélectionner un contenu');
                        isValid = false;
                    }
                }
            } else if (type === 'registration_date') {
                const dateFrom = document.getElementById('registration_date_from')?.value;
                const dateTo = document.getElementById('registration_date_to')?.value;
                if (!dateFrom && !dateTo) {
                    alert('Veuillez sélectionner au moins une date');
                    isValid = false;
                }
            } else if (type === 'activity') {
                const activityType = document.getElementById('activity_type')?.value;
                if (!activityType) {
                    alert('Veuillez sélectionner un type d\'activité');
                    isValid = false;
                }
            } else if (type === 'single') {
                const singleUserId = document.getElementById('single_user_id');
                if (!singleUserId || !singleUserId.value) {
                    alert('Veuillez sélectionner un utilisateur');
                    isValid = false;
                }
            } else if (type === 'selected') {
                if (!selectedUsers || selectedUsers.length === 0) {
                    alert('Veuillez sélectionner au moins un utilisateur');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                return false;
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
            const formData = new FormData(sendEmailForm);
            
            // Envoyer via AJAX
            fetch(sendEmailForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                hideLoadingModal();
                
                // Vérifier le statut de la réponse
                if (!response.ok) {
                    // Erreur HTTP (400, 422, 500, etc.)
                    let errorMessage = 'Une erreur est survenue lors de l\'envoi.';
                    try {
                        const errorData = await response.json();
                        if (errorData.error) {
                            errorMessage = errorData.error;
                        } else if (errorData.errors) {
                            // Erreurs de validation
                            const errorList = Object.values(errorData.errors).flat().join('\n');
                            errorMessage = 'Erreurs de validation:\n' + errorList;
                        } else if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        errorMessage = `Erreur ${response.status}: ${response.statusText}`;
                    }
                    
                    alert(errorMessage);
                    if (sendBtn) {
                        sendBtn.disabled = false;
                        sendBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Envoyer l\'email';
                    }
                    return;
                }
                
                if (response.redirected) {
                    // Redirection détectée, suivre la redirection
                    window.location.href = response.url;
                    return;
                }
                
                // Parser la réponse JSON
                try {
                    const data = await response.json();
                    if (data.success) {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = '{{ route("admin.announcements") }}';
                        }
                    } else {
                        alert(data.error || 'Une erreur est survenue lors de l\'envoi.');
                        if (sendBtn) {
                            sendBtn.disabled = false;
                            sendBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Envoyer l\'email';
                        }
                    }
                } catch (e) {
                    // Si ce n'est pas du JSON, c'est probablement du HTML de redirection
                    window.location.href = '{{ route("admin.announcements") }}';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                hideLoadingModal();
                alert('Une erreur réseau est survenue lors de l\'envoi. Veuillez vérifier votre connexion et réessayer.');
                if (sendBtn) {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Envoyer l\'email';
                }
            });
        });
    }
    
    // Initialiser l'affichage des utilisateurs sélectionnés après que toutes les fonctions sont définies
    if (window.updateSelectedUsersDisplay) {
        window.updateSelectedUsersDisplay();
    }
    
    // Gestion du modal de bouton d'action
    const actionButtonModal = document.getElementById('actionButtonModal');
    const buttonUrlInput = document.getElementById('button_url');
    const buttonTextInput = document.getElementById('button_text');
    const buttonTypeSelect = document.getElementById('button_type');
    const previewButton = document.getElementById('preview_button');
    const insertButton = document.getElementById('insert_action_button');
    const actionButtonForm = document.getElementById('actionButtonForm');
    
    // Fonction pour mettre à jour l'aperçu du bouton
    function updateButtonPreview() {
        const url = buttonUrlInput.value || '#';
        const text = buttonTextInput.value || 'Cliquez ici';
        const type = buttonTypeSelect.value || 'primary';
        
        const backgroundColor = type === 'secondary' ? '#6c757d' : 
                              type === 'success' ? '#28a745' : 
                              type === 'danger' ? '#dc3545' : '#003366';
        
        previewButton.href = url;
        previewButton.textContent = text;
        previewButton.className = `action-button ${type}`;
        previewButton.style.backgroundColor = backgroundColor;
    }
    
    // Écouter les changements pour mettre à jour l'aperçu
    if (buttonUrlInput && buttonTextInput && buttonTypeSelect && previewButton) {
        buttonUrlInput.addEventListener('input', updateButtonPreview);
        buttonTextInput.addEventListener('input', updateButtonPreview);
        buttonTypeSelect.addEventListener('change', updateButtonPreview);
    }
    
    // Réinitialiser le formulaire quand le modal est fermé
    if (actionButtonModal) {
        actionButtonModal.addEventListener('hidden.bs.modal', function() {
            if (actionButtonForm) {
                actionButtonForm.reset();
            }
            if (previewButton) {
                previewButton.href = '#';
                previewButton.textContent = 'Cliquez ici';
                previewButton.className = 'action-button primary';
                previewButton.style.backgroundColor = '#003366';
            }
        });
    }
    
    // Insérer le bouton dans l'éditeur
    if (insertButton) {
        insertButton.addEventListener('click', function() {
            const url = buttonUrlInput.value?.trim();
            const text = buttonTextInput.value?.trim();
            const type = buttonTypeSelect.value || 'primary';
            
            if (!url || !text) {
                alert('Veuillez remplir tous les champs obligatoires.');
                return;
            }
            
            const backgroundColor = type === 'secondary' ? '#6c757d' : 
                                  type === 'success' ? '#28a745' : 
                                  type === 'danger' ? '#dc3545' : '#003366';
            
            const buttonClass = `action-button ${type}`;
            const buttonHtml = `<a href="${url}" class="${buttonClass}" style="display: inline-block; padding: 12px 24px; margin: 15px 10px 15px 0; background-color: ${backgroundColor}; color: #ffffff !important; text-decoration: none !important; border-radius: 6px; font-weight: 600; text-align: center;">${text}</a>`;
            
            const range = quill.getSelection(true);
            if (range) {
                quill.clipboard.dangerouslyPasteHTML(range.index, buttonHtml);
                quill.setSelection(range.index + buttonHtml.length);
            } else {
                // Si pas de sélection, insérer à la fin
                const length = quill.getLength();
                quill.clipboard.dangerouslyPasteHTML(length - 1, buttonHtml);
            }
            
            // Fermer le modal
            const modal = bootstrap.Modal.getInstance(actionButtonModal);
            if (modal) {
                modal.hide();
            }
        });
    }
});
</script>
<style>
#email_content_editor {
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}

#email_content_editor .ql-container {
    font-size: 14px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

#email_content_editor .ql-editor {
    min-height: 350px;
}

#email_content_editor .ql-editor.ql-blank::before {
    color: #6c757d;
    font-style: normal;
}

/* Ajustement des boutons d'action sur mobile */
@media (max-width: 767.98px) {
    .action-buttons-container {
        flex-direction: column !important;
        width: 100% !important;
        gap: 0.5rem !important;
    }
    
    .action-buttons-container .btn {
        width: 100% !important;
        font-size: 0.875rem !important;
        padding: 0.5rem 0.75rem !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .action-buttons-container .btn i {
        font-size: 0.875rem !important;
    }
}

/* Ajustement pour très petits écrans */
@media (max-width: 575.98px) {
    .action-buttons-container .btn {
        font-size: 0.8rem !important;
        padding: 0.45rem 0.6rem !important;
    }
    
    .action-buttons-container .btn i {
        font-size: 0.8rem !important;
        margin-right: 0.4rem !important;
    }
}

/* Enlever le padding du conteneur */
.admin-panel__body {
    padding: 0 !important;
}

.admin-panel__body > form {
    padding: 1.5rem;
}

@media (max-width: 767.98px) {
    .admin-panel__body > form {
        padding: 1rem;
    }
}
</style>
@endpush

