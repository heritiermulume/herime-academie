<!-- Confirm Order Modal -->
<div class="modal fade" id="confirmOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="confirmOrderForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        En confirmant cette commande, l'utilisateur aura accès aux contenus commandés.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Référence de paiement <span class="text-danger">*</span></label>
                        <input type="text" name="payment_reference" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Notes optionnelles sur cette commande"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Confirmer la commande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Marquer comme payé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="markPaidForm">
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-credit-card me-2"></i>
                        Cette commande sera marquée comme payée.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Référence de paiement</label>
                        <input type="text" name="payment_reference" class="form-control" 
                               placeholder="Référence du paiement reçu">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Notes sur le paiement"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-credit-card me-2"></i>Marquer comme payé
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark as Completed Modal -->
<div class="modal fade" id="markCompletedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Marquer comme terminé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Cette commande sera marquée comme terminée. Cette action est irréversible.
                </div>
                <p>Êtes-vous sûr de vouloir marquer cette commande comme terminée ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-purple" onclick="confirmMarkAsCompleted()">
                    <i class="fas fa-check-double me-2"></i>Marquer comme terminé
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Annuler la commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelOrderForm">
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Cette commande sera annulée et l'accès aux cours sera révoqué.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Raison de l'annulation <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required 
                                  placeholder="Expliquez pourquoi cette commande est annulée"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Annuler la commande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Order Modal -->
<div class="modal fade" id="deleteOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Supprimer définitivement la commande
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention !</strong> Cette action est irréversible.
                </div>
                <p>Vous êtes sur le point de supprimer définitivement cette commande. Cette action va :</p>
                <ul>
                    <li>Supprimer complètement la commande de la base de données</li>
                    <li>Retirer l'accès à tous les contenus associés à cette commande</li>
                    <li>Supprimer toutes les inscriptions liées à cette commande</li>
                    <li>Supprimer tous les paiements associés</li>
                </ul>
                <p class="mb-0 text-danger fw-bold">
                    <i class="fas fa-info-circle me-2"></i>
                    Cette action fonctionne même si la commande était déjà payée.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteOrder()">
                    <i class="fas fa-trash me-2"></i>Supprimer définitivement
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderId = null;

// Confirm Order
function confirmOrder(orderId) {
    currentOrderId = orderId;
    document.getElementById('confirmOrderForm').reset();
    new bootstrap.Modal(document.getElementById('confirmOrderModal')).show();
}

document.getElementById('confirmOrderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch(`/admin/orders/${currentOrderId}/confirm`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue');
    });
});

// Mark as Paid
function markAsPaid(orderId) {
    currentOrderId = orderId;
    document.getElementById('markPaidForm').reset();
    new bootstrap.Modal(document.getElementById('markPaidModal')).show();
}

document.getElementById('markPaidForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch(`/admin/orders/${currentOrderId}/mark-paid`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue');
    });
});

// Mark as Completed
function markAsCompleted(orderId) {
    currentOrderId = orderId;
    new bootstrap.Modal(document.getElementById('markCompletedModal')).show();
}

function confirmMarkAsCompleted() {
    fetch(`/admin/orders/${currentOrderId}/mark-completed`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue');
    });
}

// Cancel Order
function cancelOrder(orderId) {
    currentOrderId = orderId;
    document.getElementById('cancelOrderForm').reset();
    new bootstrap.Modal(document.getElementById('cancelOrderModal')).show();
}

document.getElementById('cancelOrderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch(`/admin/orders/${currentOrderId}/cancel`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue');
    });
});

// Delete Order
function deleteOrder(orderId) {
    currentOrderId = orderId;
    new bootstrap.Modal(document.getElementById('deleteOrderModal')).show();
}

function confirmDeleteOrder() {
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch(`/admin/orders/${currentOrderId}/delete`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        // Vérifier le type de contenu de la réponse
        const contentType = response.headers.get('content-type');
        
        if (!contentType || !contentType.includes('application/json')) {
            // Si ce n'est pas du JSON, c'est probablement une redirection HTML
            if (response.status === 401 || response.status === 403) {
                throw new Error('Session expirée. Veuillez vous reconnecter.');
            }
            return response.text().then(text => {
                // Si c'est du HTML, c'est probablement une page d'erreur ou de redirection
                if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<!doctype')) {
                    throw new Error('La session a expiré. Veuillez recharger la page et réessayer.');
                }
                throw new Error(`Réponse inattendue du serveur (${response.status})`);
            });
        }
        
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || `Erreur HTTP ${response.status}`);
            });
        }
        
        return response.json();
    })
    .then(data => {
        // Vérifier si la session a expiré
        if (data && data.session_expired) {
            alert(data.message || 'Votre session a expiré. Vous allez être redirigé vers la page de connexion.');
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.reload();
            }
            return;
        }
        
        if (data && data.success) {
            // Rediriger vers la liste des commandes après suppression
            window.location.href = '{{ route("admin.orders.index") }}';
        } else {
            alert('Erreur: ' + (data?.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMessage = error.message || 'Une erreur est survenue lors de la suppression';
        alert(errorMessage);
        
        // Si c'est une erreur de session, recharger la page
        if (errorMessage.includes('session') || errorMessage.includes('Session') || errorMessage.includes('expiré')) {
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    });
}
</script>


