@extends('layouts.admin')

@section('title', 'Envoyer un message WhatsApp')
@section('admin-title', 'Envoyer un message WhatsApp')
@section('admin-subtitle', 'Rédigez et envoyez des messages WhatsApp à vos utilisateurs')

@section('admin-actions')
    <a href="{{ route('admin.announcements') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour aux annonces
    </a>
@endsection

@section('admin-content')
<div class="admin-panel">
    <div class="admin-panel__body">
        @if(isset($connectionStatus) && !$connectionStatus['connected'])
        <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Attention:</strong> La connexion WhatsApp n'est pas active. Veuillez vérifier votre configuration Evolution API.
            <br><small>État actuel: {{ $connectionStatus['state'] ?? 'Inconnu' }}</small>
        </div>
        @endif

        <form id="sendWhatsAppForm" method="POST" action="{{ route('admin.announcements.send-whatsapp.post') }}">
            @csrf

            <!-- Destinataires -->
            <div class="admin-form-card mb-4">
                <h5 class="mb-3"><i class="fas fa-users me-2"></i>Sélection des destinataires</h5>
                
                <div class="mb-3">
                    <label class="form-label">Type d'envoi *</label>
                    <select class="form-select" id="recipient_type" name="recipient_type" required>
                        <option value="all">Tous les utilisateurs (avec numéro de téléphone)</option>
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

                <!-- Sélection par cours -->
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

                <!-- Sélection par téléchargement de contenu gratuit -->
                <div class="mb-3" id="downloaded_free_selection" style="display: none;">
                    <label class="form-label">Contenu téléchargeable gratuit *</label>
                    <select class="form-select" id="downloaded_content_id" name="downloaded_content_id">
                        <option value="">Tous les contenus téléchargeables gratuits</option>
                        @foreach(\App\Models\Course::where('is_published', true)->where('is_downloadable', true)->where('is_free', true)->orderBy('title')->get() as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Seuls les utilisateurs ayant téléchargé au moins une fois ce contenu (ou tous les contenus téléchargeables gratuits si aucun n'est sélectionné) recevront le message</small>
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
                    <small class="text-muted">Seuls les utilisateurs ayant acheté ce contenu recevront le message</small>
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

            <!-- Contenu du message -->
            <div class="admin-form-card mb-4">
                <h5 class="mb-3"><i class="fab fa-whatsapp me-2"></i>Contenu du message</h5>
                
                <div class="mb-3">
                    <label class="form-label">Message *</label>
                    <textarea class="form-control" name="message" id="message" required rows="8" 
                              placeholder="Rédigez votre message WhatsApp ici..." maxlength="4096"></textarea>
                    <small class="form-text text-muted">
                        <span id="char_count">0</span> / 4096 caractères
                    </small>
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
                            <small class="d-block text-muted">Le message sera envoyé dès que vous soumettez le formulaire</small>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="d-flex gap-2 justify-content-end action-buttons-container">
                <a href="{{ route('admin.announcements') }}" class="btn btn-light">Annuler</a>
                <button type="submit" class="btn btn-success" id="send_btn">
                    <i class="fab fa-whatsapp me-2"></i>Envoyer le message
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
                <div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <h5 class="mb-2">Envoi en cours...</h5>
                <p class="text-muted mb-0">Veuillez patienter pendant l'envoi des messages WhatsApp. Cela peut prendre quelques instants.</p>
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
    background-color: #dcf8c6;
    border: 1px solid #25d366;
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
    
    /* Ajustement des boutons d'action sur mobile */
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
</style>
@endpush

@push('scripts')
<script>
// Variables globales
let selectedUsers = [];

document.addEventListener('DOMContentLoaded', function() {
    // Compteur de caractères
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('char_count');
    
    if (messageTextarea && charCount) {
        messageTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
        // Initialiser le compteur
        charCount.textContent = messageTextarea.value.length;
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
        if (roleSelection) roleSelection.style.display = 'none';
        if (courseSelection) courseSelection.style.display = 'none';
        if (categorySelection) categorySelection.style.display = 'none';
        if (providerSelection) providerSelection.style.display = 'none';
        if (downloadedFreeSelection) downloadedFreeSelection.style.display = 'none';
        if (purchasedSelection) purchasedSelection.style.display = 'none';
        if (purchasedContentSelection) purchasedContentSelection.style.display = 'none';
        if (registrationDateSelection) registrationDateSelection.style.display = 'none';
        if (activitySelection) activitySelection.style.display = 'none';
        if (singleUserSelection) singleUserSelection.style.display = 'none';
        if (multipleUsersSelection) multipleUsersSelection.style.display = 'none';
        
        // Afficher la section appropriée
        if (type === 'role' && roleSelection) roleSelection.style.display = 'block';
        else if (type === 'course' && courseSelection) courseSelection.style.display = 'block';
        else if (type === 'category' && categorySelection) categorySelection.style.display = 'block';
        else if (type === 'provider' && providerSelection) providerSelection.style.display = 'block';
        else if (type === 'downloaded_free' && downloadedFreeSelection) downloadedFreeSelection.style.display = 'block';
        else if (type === 'purchased' && purchasedSelection) purchasedSelection.style.display = 'block';
        else if (type === 'registration_date' && registrationDateSelection) registrationDateSelection.style.display = 'block';
        else if (type === 'activity' && activitySelection) activitySelection.style.display = 'block';
        else if (type === 'single' && singleUserSelection) singleUserSelection.style.display = 'block';
        else if (type === 'selected' && multipleUsersSelection) multipleUsersSelection.style.display = 'block';
    }
    
    // Gestion du type de destinataire
    const recipientTypeSelect = document.getElementById('recipient_type');
    if (recipientTypeSelect) {
        updateRecipientSections(recipientTypeSelect.value);
        
        recipientTypeSelect.addEventListener('change', function() {
            const type = this.value;
            
            // Réinitialiser les champs
            const singleUserId = document.getElementById('single_user_id');
            const userSearch = document.getElementById('user_search');
            const userSearchResults = document.getElementById('user_search_results');
            const multipleUserSearch = document.getElementById('multiple_user_search');
            const multipleUserSearchResults = document.getElementById('multiple_user_search_results');
            const courseId = document.getElementById('content_id');
            const categoryId = document.getElementById('category_id');
            const providerId = document.getElementById('provider_id');
            const downloadedContentId = document.getElementById('downloaded_content_id');
            const purchaseType = document.getElementById('purchase_type');
            const purchasedContentId = document.getElementById('purchased_content_id');
            const registrationDateFrom = document.getElementById('registration_date_from');
            const registrationDateTo = document.getElementById('registration_date_to');
            const activityType = document.getElementById('activity_type');
            
            if (singleUserId) singleUserId.value = '';
            if (userSearch) userSearch.value = '';
            if (userSearchResults) {
                userSearchResults.innerHTML = '';
                userSearchResults.style.display = 'none';
            }
            if (multipleUserSearch) multipleUserSearch.value = '';
            if (multipleUserSearchResults) {
                multipleUserSearchResults.innerHTML = '';
                multipleUserSearchResults.style.display = 'none';
            }
            if (courseId) courseId.value = '';
            if (categoryId) categoryId.value = '';
            if (providerId) providerId.value = '';
            if (downloadedContentId) downloadedContentId.value = '';
            if (purchaseType) purchaseType.value = '';
            if (purchasedContentId) purchasedContentId.value = '';
            if (registrationDateFrom) registrationDateFrom.value = '';
            if (registrationDateTo) registrationDateTo.value = '';
            if (activityType) activityType.value = '';
            
            document.querySelectorAll('input[name="roles[]"]').forEach(cb => cb.checked = false);
            
            if (type !== 'selected') {
                selectedUsers = [];
                updateSelectedUsersDisplay();
            }
            
            updateRecipientSections(type);
            setTimeout(() => window.updateRecipientCount(), 100);
        });
    }
    
    // Fonction pour mettre à jour le compte de destinataires (définie tôt pour être accessible partout)
    window.updateRecipientCount = function() {
        const recipientTypeSelect = document.getElementById('recipient_type');
        const countDiv = document.getElementById('recipient_count');
        const countText = document.getElementById('recipient_count_text');
        
        if (!recipientTypeSelect || !countDiv || !countText) return;
        
        const type = recipientTypeSelect.value;
        
        if (type === 'all') {
            fetch('{{ route("admin.announcements.count-users-whatsapp") }}?type=all')
                .then(response => response.json())
                .then(data => {
                    if (data && typeof data.count !== 'undefined') {
                        countText.textContent = `${data.count} utilisateur(s) avec numéro de téléphone recevront ce message`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    countText.textContent = 'Impossible de compter les utilisateurs';
                    countDiv.style.display = 'block';
                });
        } else if (type === 'role') {
            const roles = Array.from(document.querySelectorAll('input[name="roles[]"]:checked')).map(cb => cb.value);
            if (roles.length > 0) {
                fetch(`{{ route("admin.announcements.count-users-whatsapp") }}?type=role&roles=${roles.join(',')}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            countText.textContent = `${data.count} utilisateur(s) avec numéro de téléphone recevront ce message`;
                            countDiv.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        countText.textContent = 'Impossible de compter les utilisateurs';
                        countDiv.style.display = 'block';
                    });
            } else {
                countDiv.style.display = 'none';
            }
        } else if (type === 'course') {
            const courseId = document.getElementById('content_id')?.value;
            if (!courseId) {
                countText.textContent = 'Veuillez sélectionner un contenu';
                countDiv.style.display = 'block';
                return;
            }
            fetch(`{{ route("admin.announcements.count-users-whatsapp") }}?type=course&content_id=${courseId}`)
                .then(response => response.json())
                .then(data => {
                    if (data && typeof data.count !== 'undefined') {
                        countText.textContent = `${data.count} utilisateur(s) inscrit(s) à ce contenu recevront ce message`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
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
            fetch(`{{ route("admin.announcements.count-users-whatsapp") }}?type=category&category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data && typeof data.count !== 'undefined') {
                        countText.textContent = `${data.count} utilisateur(s) inscrit(s) à des contenus de cette catégorie recevront ce message`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
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
            fetch(`{{ route("admin.announcements.count-users-whatsapp") }}?type=provider&provider_id=${providerId}`)
                .then(response => response.json())
                .then(data => {
                    if (data && typeof data.count !== 'undefined') {
                        countText.textContent = `${data.count} utilisateur(s) inscrit(s) à des contenus de ce prestataire recevront ce message`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    countText.textContent = 'Impossible de compter les utilisateurs';
                    countDiv.style.display = 'block';
                });
        } else if (type === 'downloaded_free') {
            const downloadedContentId = document.getElementById('downloaded_content_id')?.value;
            let url = `{{ route("admin.announcements.count-users-whatsapp") }}?type=downloaded_free`;
            if (downloadedContentId) {
                url += `&downloaded_content_id=${downloadedContentId}`;
            }
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data && typeof data.count !== 'undefined') {
                        const contentText = downloadedContentId ? 'ce contenu' : 'des contenus téléchargeables gratuits';
                        countText.textContent = `${data.count} utilisateur(s) ayant téléchargé ${contentText} recevront ce message`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    countText.textContent = 'Impossible de compter les utilisateurs';
                    countDiv.style.display = 'block';
                });
        } else if (type === 'purchased') {
            const purchaseType = document.getElementById('purchase_type')?.value;
            if (!purchaseType) {
                countText.textContent = 'Veuillez sélectionner un type d\'achat';
                countDiv.style.display = 'block';
                return;
            }
            let url = `{{ route("admin.announcements.count-users-whatsapp") }}?type=purchased&purchase_type=${purchaseType}`;
            const purchasedContentId = document.getElementById('purchased_content_id')?.value;
            if (purchaseType === 'specific_content' && purchasedContentId) {
                url += `&purchased_content_id=${purchasedContentId}`;
            }
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data && typeof data.count !== 'undefined') {
                        let purchaseText = '';
                        switch(purchaseType) {
                            case 'any':
                                purchaseText = 'ayant effectué un achat';
                                break;
                            case 'paid':
                                purchaseText = 'ayant des commandes payées';
                                break;
                            case 'completed':
                                purchaseText = 'ayant des commandes complétées';
                                break;
                            case 'specific_content':
                                purchaseText = 'ayant acheté ce contenu';
                                break;
                        }
                        countText.textContent = `${data.count} utilisateur(s) ${purchaseText} recevront ce message`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
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
            let url = `{{ route("admin.announcements.count-users-whatsapp") }}?type=registration_date`;
            if (dateFrom) url += `&registration_date_from=${dateFrom}`;
            if (dateTo) url += `&registration_date_to=${dateTo}`;
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data && typeof data.count !== 'undefined') {
                        countText.textContent = `${data.count} utilisateur(s) inscrit(s) dans cette période recevront ce message`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
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
            fetch(`{{ route("admin.announcements.count-users-whatsapp") }}?type=activity&activity_type=${activityType}`)
                .then(response => response.json())
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
                        countText.textContent = `${data.count} utilisateur(s) ${activityLabel} recevront ce message`;
                        countDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    countText.textContent = 'Impossible de compter les utilisateurs';
                    countDiv.style.display = 'block';
                });
        } else if (type === 'single') {
            const userIdInput = document.getElementById('single_user_id');
            if (userIdInput && userIdInput.value) {
                countText.textContent = '1 utilisateur recevra ce message';
                countDiv.style.display = 'block';
            } else {
                countDiv.style.display = 'none';
            }
        } else if (type === 'selected') {
            if (selectedUsers && selectedUsers.length > 0) {
                countText.textContent = `${selectedUsers.length} utilisateur(s) recevront ce message`;
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
    if (instructorSelect) {
        instructorSelect.addEventListener('change', window.updateRecipientCount);
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

    // Fonction de recherche d'utilisateurs
    window.searchUsers = function(query, type = 'single') {
        const resultsDivId = type === 'single' ? 'user_search_results' : 'multiple_user_search_results';
        const resultsDiv = document.getElementById(resultsDivId);
        
        if (!resultsDiv) {
            console.error('Div de résultats non trouvé:', resultsDivId);
            return;
        }
        
        if (!query || query.length < 2) {
            resultsDiv.innerHTML = '';
            resultsDiv.style.display = 'none';
            return;
        }
        
        resultsDiv.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Recherche...</p>';
        resultsDiv.style.display = 'block';
        
        const searchUrl = `{{ route('admin.announcements.search-users-whatsapp') }}?q=${encodeURIComponent(query)}`;
        
        fetch(searchUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur HTTP: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (!Array.isArray(data)) {
                    console.error('Réponse non-array:', data);
                    resultsDiv.innerHTML = '<p class="text-danger">Erreur: Format de réponse invalide</p>';
                    return;
                }
                
                if (data.length === 0) {
                    resultsDiv.innerHTML = '<p class="text-muted">Aucun utilisateur avec numéro de téléphone trouvé</p>';
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
                    // Pour 'multiple' ou 'selected'
                    const html = data.map(user => {
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
                    }).filter(html => html !== '').join('');
                    
                    resultsDiv.innerHTML = html || '<p class="text-muted">Tous les utilisateurs trouvés sont déjà sélectionnés</p>';
                }
            })
            .catch(error => {
                console.error('Erreur lors de la recherche:', error);
                resultsDiv.innerHTML = '<p class="text-danger">Erreur lors de la recherche. Vérifiez la console pour plus de détails.</p>';
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
        document.getElementById('user_search').value = `${name} (${phone})`;
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
                const safePhone = (user.phone || 'Pas de téléphone').replace(/"/g, '&quot;');
                return `
                    <span class="user-badge">
                        ${safeName} (${safePhone})
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
    const sendWhatsAppForm = document.getElementById('sendWhatsAppForm');
    if (sendWhatsAppForm) {
        sendWhatsAppForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const recipientTypeSelect = document.getElementById('recipient_type');
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
                    alert('Veuillez sélectionner un cours');
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
            const formData = new FormData(sendWhatsAppForm);
            
            // Envoyer via AJAX
            fetch(sendWhatsAppForm.action, {
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
                alert('Une erreur est survenue lors de l\'envoi. Veuillez réessayer.');
                if (sendBtn) {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="fab fa-whatsapp me-2"></i>Envoyer le message';
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

