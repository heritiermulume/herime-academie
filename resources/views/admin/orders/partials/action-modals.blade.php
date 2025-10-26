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
                        En confirmant cette commande, l'utilisateur aura accès aux cours commandés.
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
</script>


