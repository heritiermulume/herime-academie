/**
 * Fonction globale pour afficher un modal de confirmation moderne
 * Utilise le même modal que les actions en lot pour l'harmonie
 * 
 * @param {string} message - Le message de confirmation à afficher
 * @param {string} title - Le titre du modal (optionnel, par défaut "Confirmation")
 * @param {string} confirmButtonText - Le texte du bouton de confirmation (optionnel, par défaut "Confirmer")
 * @param {string} confirmButtonClass - La classe CSS du bouton de confirmation (optionnel, par défaut "btn-danger")
 * @param {string} icon - L'icône à afficher dans le titre (optionnel, par défaut "fa-exclamation-triangle")
 * @returns {Promise<boolean>} - Promise qui résout à true si confirmé, false si annulé
 */
window.showModernConfirmModal = function(message, options = {}) {
    return new Promise((resolve) => {
        const {
            title = 'Confirmation',
            confirmButtonText = 'Confirmer',
            confirmButtonClass = 'btn-danger',
            icon = 'fa-exclamation-triangle',
            iconClass = 'text-white'
        } = options;
        
        const modal = document.getElementById('bulkActionConfirmModal');
        const messageElement = document.getElementById('bulkActionConfirmModalMessage');
        const titleElement = document.getElementById('bulkActionConfirmModalLabel');
        const confirmBtn = document.getElementById('bulkActionConfirmModalConfirmBtn');
        
        if (!modal || !messageElement || !confirmBtn) {
            // Fallback vers confirm() si le modal n'existe pas
            if (confirm(message)) {
                resolve(true);
            } else {
                resolve(false);
            }
            return;
        }
        
        // Mettre à jour le message
        messageElement.textContent = message;
        
        // Mettre à jour le titre
        if (titleElement) {
            titleElement.innerHTML = `<i class="fas ${icon} me-2 ${iconClass}"></i>${title}`;
        }
        
        // Mettre à jour le bouton de confirmation
        const originalClass = confirmBtn.className;
        confirmBtn.className = `btn ${confirmButtonClass}`;
        confirmBtn.innerHTML = `<i class="fas fa-check me-2"></i>${confirmButtonText}`;
        
        let resolved = false;
        
        // Fonction pour confirmer
        const handleConfirm = () => {
            if (resolved) return;
            resolved = true;
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
            confirmBtn.removeEventListener('click', handleConfirm);
            modal.removeEventListener('hidden.bs.modal', handleCancel);
            // Restaurer le bouton à son état original
            confirmBtn.className = originalClass;
            confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i>Confirmer';
            resolve(true);
        };
        
        // Fonction pour annuler
        const handleCancel = () => {
            if (resolved) return;
            resolved = true;
            confirmBtn.removeEventListener('click', handleConfirm);
            modal.removeEventListener('hidden.bs.modal', handleCancel);
            // Restaurer le bouton à son état original
            confirmBtn.className = originalClass;
            confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i>Confirmer';
            resolve(false);
        };
        
        // Ajouter les event listeners
        confirmBtn.addEventListener('click', handleConfirm);
        modal.addEventListener('hidden.bs.modal', handleCancel, { once: true });
        
        // Afficher le modal
        const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
        bsModal.show();
    });
};
