/**
 * Système de sélection multiple et actions en lot
 */
const bulkActions = {
    selections: {},
    exportRoutes: {},
    
    /**
     * Initialiser la sélection multiple pour une table
     */
    init(tableId, options = {}) {
        const {
            exportRoute = null,
            onSelectionChange = null,
            checkboxSelector = 'input[type="checkbox"][data-item-id]',
            selectAllSelector = 'input[type="checkbox"][data-select-all]'
        } = options;
        
        this.selections[tableId] = new Set();
        if (exportRoute) {
            this.exportRoutes[tableId] = exportRoute;
        }
        
        // Gérer le checkbox "Sélectionner tout"
        const selectAllCheckbox = document.querySelector(`${selectAllSelector}[data-table-id="${tableId}"]`);
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                this.toggleSelectAll(tableId, e.target.checked);
            });
        }
        
        // Gérer les checkboxes individuels
        const checkboxes = document.querySelectorAll(`#${tableId} ${checkboxSelector}`);
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const itemId = e.target.dataset.itemId;
                if (itemId) {
                    if (e.target.checked) {
                        this.selections[tableId].add(itemId);
                    } else {
                        this.selections[tableId].delete(itemId);
                        // Décocher "Sélectionner tout" si un élément est désélectionné
                        if (selectAllCheckbox) {
                            selectAllCheckbox.checked = false;
                            selectAllCheckbox.indeterminate = false;
                        }
                    }
                    this.updateUI(tableId);
                    if (onSelectionChange) {
                        onSelectionChange(this.selections[tableId].size);
                    }
                }
            });
        });
        
        // Gérer les boutons d'action en lot
        document.querySelectorAll(`.bulk-action-btn[data-table-id="${tableId}"]`).forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.handleBulkAction(e.target.closest('.bulk-action-btn'), tableId);
            });
        });
        
        // Gérer les liens d'export (avec délai pour s'assurer qu'ils sont dans le DOM)
        setTimeout(() => {
            document.querySelectorAll(`.export-link[data-table-id="${tableId}"]`).forEach(link => {
                // Retirer l'ancien listener s'il existe
                const newLink = link.cloneNode(true);
                link.parentNode.replaceChild(newLink, link);
                
                newLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.handleExport(tableId, newLink.dataset.format);
                });
            });
        }, 50);
    },
    
    /**
     * Sélectionner/Désélectionner tous les éléments
     */
    toggleSelectAll(tableId, checked) {
        const checkboxes = document.querySelectorAll(`#${tableId} input[type="checkbox"][data-item-id]`);
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
            const itemId = checkbox.dataset.itemId;
            if (itemId) {
                if (checked) {
                    this.selections[tableId].add(itemId);
                } else {
                    this.selections[tableId].delete(itemId);
                }
            }
        });
        this.updateUI(tableId);
    },
    
    /**
     * Mettre à jour l'interface utilisateur
     */
    updateUI(tableId) {
        const count = this.selections[tableId]?.size || 0;
        const bar = document.getElementById(`bulkActionsBar-${tableId}`);
        const countElement = document.getElementById(`selectedCount-${tableId}`);
        
        if (countElement) {
            countElement.textContent = count;
        }
        
        if (bar) {
            if (count > 0) {
                // Afficher avec animation
                bar.style.display = 'block';
                // Forcer le reflow pour l'animation
                void bar.offsetHeight; // Force reflow
                requestAnimationFrame(() => {
                    bar.classList.add('show');
                });
            } else {
                // Animation de sortie
                bar.classList.remove('show');
                setTimeout(() => {
                    const currentBar = document.getElementById(`bulkActionsBar-${tableId}`);
                    if (currentBar && !currentBar.classList.contains('show')) {
                        currentBar.style.display = 'none';
                    }
                }, 300);
            }
        } else {
            // Si la barre n'existe pas, essayer de la créer
            console.warn(`Bulk actions bar not found for table: ${tableId}`);
        }
        
        // Mettre à jour l'état du checkbox "Sélectionner tout"
        const selectAllCheckbox = document.querySelector(`input[type="checkbox"][data-select-all][data-table-id="${tableId}"]`);
        if (selectAllCheckbox) {
            const totalCheckboxes = document.querySelectorAll(`#${tableId} input[type="checkbox"][data-item-id]`).length;
            if (count === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (count === totalCheckboxes) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }
    },
    
    /**
     * Effacer la sélection
     */
    clearSelection(tableId) {
        this.selections[tableId] = new Set();
        const checkboxes = document.querySelectorAll(`#${tableId} input[type="checkbox"][data-item-id]`);
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        const selectAllCheckbox = document.querySelector(`input[type="checkbox"][data-select-all][data-table-id="${tableId}"]`);
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
        
        this.updateUI(tableId);
    },
    
    /**
     * Obtenir les IDs sélectionnés
     */
    getSelectedIds(tableId) {
        return Array.from(this.selections[tableId] || []);
    },
    
    /**
     * Afficher un modal de confirmation moderne
     */
    showConfirmModal(message) {
        return new Promise((resolve) => {
            const modal = document.getElementById('bulkActionConfirmModal');
            const messageElement = document.getElementById('bulkActionConfirmModalMessage');
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
            
            messageElement.textContent = message;
            
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
                resolve(true);
            };
            
            // Fonction pour annuler
            const handleCancel = () => {
                if (resolved) return;
                resolved = true;
                confirmBtn.removeEventListener('click', handleConfirm);
                modal.removeEventListener('hidden.bs.modal', handleCancel);
                resolve(false);
            };
            
            // Ajouter les event listeners
            confirmBtn.addEventListener('click', handleConfirm);
            modal.addEventListener('hidden.bs.modal', handleCancel, { once: true });
            
            // Afficher le modal
            const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
            bsModal.show();
        });
    },
    
    /**
     * Gérer une action en lot
     */
    async handleBulkAction(button, tableId) {
        const selectedIds = this.getSelectedIds(tableId);
        
        if (selectedIds.length === 0) {
            // Utiliser un toast ou notification si disponible
            if (typeof window.showNotification === 'function') {
                window.showNotification('Veuillez sélectionner au moins un élément.', 'warning');
            } else {
                alert('Veuillez sélectionner au moins un élément.');
            }
            return;
        }
        
        const action = button.dataset.action;
        const confirmRequired = button.dataset.confirm === 'true';
        const confirmMessage = button.dataset.confirmMessage || 'Confirmer cette action ?';
        const route = button.dataset.route;
        const method = button.dataset.method || 'POST';
        
        if (confirmRequired) {
            const confirmed = await this.showConfirmModal(confirmMessage);
            if (!confirmed) {
                return;
            }
        }
        
        // Désactiver le bouton pendant le traitement
        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Traitement...';
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            const response = await fetch(route, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ids: selectedIds,
                    action: action
                })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Une erreur est survenue.');
            }
            
            // Afficher un message de succès
            if (typeof window.showNotification === 'function') {
                window.showNotification(data.message || 'Action effectuée avec succès.', 'success');
            } else {
                alert(data.message || 'Action effectuée avec succès.');
            }
            
            // Recharger la page ou mettre à jour la table
            if (data.reload !== false) {
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                // Mettre à jour la table sans recharger
                this.clearSelection(tableId);
                if (data.updateTable) {
                    // Callback pour mettre à jour la table
                    if (typeof data.updateTable === 'function') {
                        data.updateTable();
                    }
                }
            }
        } catch (error) {
            console.error('Erreur lors de l\'action en lot:', error);
            alert(error.message || 'Une erreur est survenue lors du traitement.');
        } finally {
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
    },
    
    /**
     * Gérer l'export
     */
    handleExport(tableId, format) {
        const selectedIds = this.getSelectedIds(tableId);
        const exportRoute = this.exportRoutes[tableId];
        
        if (!exportRoute) {
            alert('Route d\'export non configurée.');
            return;
        }
        
        // Construire l'URL avec les paramètres
        const url = new URL(exportRoute, window.location.origin);
        url.searchParams.set('format', format);
        
        // Si des éléments sont sélectionnés, exporter uniquement ceux-là
        if (selectedIds.length > 0) {
            url.searchParams.set('ids', selectedIds.join(','));
        }
        
        // Ajouter les filtres actuels de la page
        const currentParams = new URLSearchParams(window.location.search);
        currentParams.forEach((value, key) => {
            if (key !== 'page' && key !== 'ids' && key !== 'format') {
                url.searchParams.set(key, value);
            }
        });
        
        // Créer un lien temporaire pour télécharger
        const link = document.createElement('a');
        link.href = url.toString();
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Effacer la sélection après l'export
        this.clearSelection(tableId);
    }
};

// Initialiser automatiquement quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    // Trouver toutes les tables avec sélection multiple
    document.querySelectorAll('[data-bulk-select="true"]').forEach(table => {
        const tableId = table.id || `table-${Date.now()}`;
        if (!table.id) {
            table.id = tableId;
        }
        
        const exportRoute = table.dataset.exportRoute || null;
        
        bulkActions.init(tableId, {
            exportRoute: exportRoute
        });
    });
});
