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
                                <input class="form-check-input" type="checkbox" name="roles[]" value="student" id="role_student">
                                <label class="form-check-label" for="role_student">Étudiants</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="instructor" id="role_instructor">
                                <label class="form-check-label" for="role_instructor">Formateurs</label>
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
                    </div>
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
        const singleUserSelection = document.getElementById('single_user_selection');
        const multipleUsersSelection = document.getElementById('multiple_users_selection');
        
        if (roleSelection) roleSelection.style.display = type === 'role' ? 'block' : 'none';
        if (singleUserSelection) singleUserSelection.style.display = type === 'single' ? 'block' : 'none';
        if (multipleUsersSelection) multipleUsersSelection.style.display = type === 'selected' ? 'block' : 'none';
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

