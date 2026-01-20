@extends('layouts.admin')

@section('title', 'Gestion des commandes')
@section('admin-title', 'Gestion des commandes')
@section('admin-subtitle', 'Suivez les transactions réalisées sur Herime Académie et leur état de traitement')
@section('admin-actions')
    <button class="btn btn-primary" onclick="exportOrders()">
        <i class="fas fa-download me-2"></i>Exporter
    </button>
@endsection

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <div class="admin-stats-grid mb-4">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Commandes</p>
                    <p class="admin-stat-card__value">{{ $stats['total'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">Référencées dans la base</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">En attente</p>
                    <p class="admin-stat-card__value">{{ $stats['pending'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">À confirmer</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Confirmées</p>
                    <p class="admin-stat-card__value">{{ $stats['confirmed'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">En cours de paiement</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Payées</p>
                    <p class="admin-stat-card__value">{{ $stats['paid'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">Revenus sécurisés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Terminées</p>
                    <p class="admin-stat-card__value">{{ $stats['completed'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">Contenus délivrés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Annulées</p>
                    <p class="admin-stat-card__value">{{ $stats['cancelled'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">À analyser</p>
                </div>
            </div>

            <div class="admin-panel admin-panel__revenues mb-4">
                <div class="admin-panel__body admin-panel__body--padded text-center" style="background: linear-gradient(135deg, #22c55e 0%, #0ea5e9 100%); color:#fff;">
                    <h4 class="mb-0">
                        <i class="fas fa-coins me-2"></i>Revenus totaux : {{ \App\Helpers\CurrencyHelper::formatWithSymbol($stats['total_revenue'] ?? 0) }}
                    </h4>
                </div>
            </div>

            <x-admin.search-panel
                :action="route('admin.orders.index')"
                formId="ordersFilterForm"
                filtersId="ordersFilters"
                :hasFilters="true"
                :searchValue="request('search')"
                placeholder="Numéro de commande, nom, email..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Payée</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminée</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Mode de paiement</label>
                            <select class="form-select" name="payment_method">
                                <option value="">Tous les modes</option>
                                <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Carte bancaire</option>
                                <option value="paypal" {{ request('payment_method') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                                <option value="mobile" {{ request('payment_method') == 'mobile' ? 'selected' : '' }}>Mobile Money</option>
                                <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Virement bancaire</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Date de début</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Date de fin</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Ajustez les filtres puis appliquez-les.</span>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            <div class="admin-table">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Commande</th>
                                <th>Client</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th class="d-none d-sm-table-cell">Mode de paiement</th>
                                <th class="d-none d-md-table-cell">Devise paiement</th>
                                <th class="d-none d-md-table-cell">Date</th>
                                <th class="text-center" style="width:120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td>
                                    <div class="fw-semibold">#{{ $order->order_number ?? $order->id }}</div>
                                    <div class="text-muted small">{{ strtoupper($order->payment_method ?? '—') }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $order->user->name ?? 'Utilisateur supprimé' }}</div>
                                    <div class="text-muted small">{{ $order->user->email ?? '—' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->total_amount ?? $order->total, $order->currency ?? 'USD') }}</div>
                                    <div class="text-muted small">HT : {{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->subtotal ?? 0, $order->currency ?? 'USD') }}</div>
                                </td>
                                <td>
                                    @switch($order->status)
                                        @case('paid')
                                            <span class="admin-chip admin-chip--success">Payée</span>
                                            @break
                                        @case('pending')
                                            <span class="admin-chip admin-chip--warning">En attente</span>
                                            @break
                                        @case('confirmed')
                                            <span class="admin-chip admin-chip--info">Confirmée</span>
                                            @break
                                        @case('completed')
                                            <span class="admin-chip admin-chip--success">Terminée</span>
                                            @break
                                        @case('cancelled')
                                            <span class="admin-chip admin-chip--danger">Annulée</span>
                                            @break
                                        @default
                                            <span class="admin-chip admin-chip--neutral">{{ ucfirst($order->status ?? 'inconnu') }}</span>
                                    @endswitch
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    <span class="admin-chip admin-chip--neutral">{{ strtoupper($order->payment_method ?? '—') }}</span>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="admin-chip admin-chip--info">{{ strtoupper($order->currency ?? 'USD') }}</span>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="admin-chip admin-chip--neutral">{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '—' }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-light btn-sm" title="Voir la commande">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-success btn-sm" 
                                                data-order-id="{{ $order->id }}"
                                                onclick="openConfirmOrderModal(this.dataset.orderId)" 
                                                title="Confirmer la commande">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                data-order-id="{{ $order->id }}"
                                                onclick="openCancelOrderModal(this.dataset.orderId)" 
                                                title="Annuler la commande">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="admin-table__empty">
                                    <i class="fas fa-receipt mb-2 d-block"></i>
                                    Aucune commande ne correspond aux filtres sélectionnés.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-admin.pagination :paginator="$orders" :showInfo="true" itemName="commandes" />

        </div>
    </section>

    <!-- Confirm Order Modal -->
    <div class="modal fade" id="confirmOrderModal" tabindex="-1" aria-labelledby="confirmOrderModalLabel" data-bs-backdrop="static" data-bs-keyboard="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="confirmOrderModalLabel">
                        <i class="fas fa-check-circle text-success me-2"></i>Confirmer la commande
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form id="confirmOrderForm">
                    <div class="modal-body pt-3">
                        <div class="alert alert-info d-flex align-items-start mb-3" role="alert">
                            <i class="fas fa-info-circle me-2 mt-1"></i>
                            <div>
                                En confirmant cette commande, l'utilisateur aura accès aux contenus commandés.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPaymentReference" class="form-label fw-semibold">
                                Référence de paiement <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="confirmPaymentReference" 
                                name="payment_reference" 
                                class="form-control" 
                                required 
                                placeholder="Entrez la référence de paiement"
                            >
                            <div class="form-text">Cette référence sera enregistrée pour référence.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmNotes" class="form-label fw-semibold">Notes</label>
                            <textarea 
                                id="confirmNotes" 
                                name="notes" 
                                class="form-control" 
                                rows="3" 
                                placeholder="Notes optionnelles sur cette commande"
                                style="resize: vertical;"
                            ></textarea>
                            <div class="form-text">Ces notes seront enregistrées avec la commande.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" tabindex="-1">
                            <i class="fas fa-times me-2"></i>Fermer
                        </button>
                        <button type="submit" class="btn btn-success" id="confirmOrderSubmitBtn" tabindex="-1">
                            <i class="fas fa-check me-2"></i>Confirmer la commande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" data-bs-backdrop="static" data-bs-keyboard="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="cancelOrderModalLabel">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>Annuler la commande
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form id="cancelOrderForm">
                    <div class="modal-body pt-3">
                        <div class="alert alert-danger d-flex align-items-start mb-3" role="alert">
                            <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                            <div>
                                <strong>Attention !</strong> Cette commande sera annulée et l'accès aux contenus sera révoqué.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label fw-semibold">
                                Raison de l'annulation <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                id="cancelReason" 
                                name="reason" 
                                class="form-control" 
                                rows="4" 
                                required 
                                placeholder="Expliquez pourquoi cette commande est annulée"
                                style="resize: vertical;"
                            ></textarea>
                            <div class="form-text">Cette information sera enregistrée pour référence.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" tabindex="-1">
                            <i class="fas fa-times me-2"></i>Fermer
                        </button>
                        <button type="submit" class="btn btn-danger" id="cancelOrderSubmitBtn" tabindex="-1">
                            <i class="fas fa-ban me-2"></i>Annuler la commande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const ordersFilterForm = document.getElementById('ordersFilterForm');
const ordersFiltersOffcanvas = document.getElementById('ordersFilters');

if (ordersFilterForm) {
    ordersFilterForm.addEventListener('submit', () => {
        if (ordersFiltersOffcanvas) {
            const instance = bootstrap.Offcanvas.getInstance(ordersFiltersOffcanvas);
            if (instance) {
                instance.hide();
            }
        }
    });
}

const ordersSearchInput = document.querySelector('#ordersFilterForm input[name="search"]');
if (ordersSearchInput) {
    let searchTimeout;
    ordersSearchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            ordersFilterForm?.submit();
        }, 500);
    });
}

function exportOrders() {
    const urlParams = new URLSearchParams(window.location.search);
    const exportUrl = '{{ route("admin.orders.export") }}?' + urlParams.toString();
    
    // Afficher un indicateur de chargement
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Exportation...';
    button.disabled = true;
    
    // Créer un lien temporaire pour télécharger le fichier
    const link = document.createElement('a');
    link.href = exportUrl;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Restaurer le bouton après un délai
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 2000);
}

let currentConfirmOrderId = null;
let currentCancelOrderId = null;

function openConfirmOrderModal(orderId) {
    currentConfirmOrderId = parseInt(orderId, 10);
    if (!currentConfirmOrderId || isNaN(currentConfirmOrderId)) {
        alert('Erreur: ID de commande invalide.');
        return;
    }
    const form = document.getElementById('confirmOrderForm');
    if (form) {
        form.reset();
    }
    const modalElement = document.getElementById('confirmOrderModal');
    if (modalElement) {
        // Retirer aria-hidden avant d'afficher le modal
        modalElement.removeAttribute('aria-hidden');
        
        // Observer les changements d'attribut aria-hidden pour le retirer immédiatement
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                    // Si aria-hidden est ajouté et vaut "true" pendant que le modal est visible, le retirer
                    if (modalElement.getAttribute('aria-hidden') === 'true' && 
                        modalElement.classList.contains('show')) {
                        // Utiliser requestAnimationFrame pour s'assurer que c'est après le rendu
                        requestAnimationFrame(() => {
                            if (modalElement.classList.contains('show')) {
                                modalElement.removeAttribute('aria-hidden');
                            }
                        });
                    }
                }
            });
        });
        
        // Observer les changements d'attributs sur le modal
        observer.observe(modalElement, {
            attributes: true,
            attributeFilter: ['aria-hidden']
        });
        
        // Gérer le focus après que le modal soit complètement affiché
        const handleModalShown = function() {
            // S'assurer que aria-hidden est bien retiré
            modalElement.removeAttribute('aria-hidden');
            
            // Arrêter l'observer
            observer.disconnect();
            
            // Réactiver les tabindex des boutons
            const closeBtn = modalElement.querySelector('.btn-secondary[data-bs-dismiss="modal"]');
            const submitBtn = document.getElementById('confirmOrderSubmitBtn');
            if (closeBtn) closeBtn.removeAttribute('tabindex');
            if (submitBtn) submitBtn.removeAttribute('tabindex');
            
            // Mettre le focus sur le champ de référence de paiement
            const paymentRefInput = document.getElementById('confirmPaymentReference');
            if (paymentRefInput) {
                // Utiliser un double requestAnimationFrame pour s'assurer que tout est rendu
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        paymentRefInput.focus();
                    });
                });
            }
            
            // Nettoyer l'écouteur
            modalElement.removeEventListener('shown.bs.modal', handleModalShown);
        };
        
        // Gérer la fermeture du modal pour nettoyer l'observer
        const handleModalHidden = function() {
            observer.disconnect();
            modalElement.removeEventListener('hidden.bs.modal', handleModalHidden);
        };
        
        // Ajouter les écouteurs
        modalElement.addEventListener('shown.bs.modal', handleModalShown, { once: true });
        modalElement.addEventListener('hidden.bs.modal', handleModalHidden, { once: true });
        
        // Créer et afficher le modal
        const modal = new bootstrap.Modal(modalElement, {
            focus: false, // Désactiver le focus automatique de Bootstrap
            keyboard: true
        });
        modal.show();
    }
}

function openCancelOrderModal(orderId) {
    currentCancelOrderId = parseInt(orderId, 10);
    if (!currentCancelOrderId || isNaN(currentCancelOrderId)) {
        alert('Erreur: ID de commande invalide.');
        return;
    }
    const form = document.getElementById('cancelOrderForm');
    if (form) {
        form.reset();
    }
    const modalElement = document.getElementById('cancelOrderModal');
    if (modalElement) {
        // Retirer aria-hidden avant d'afficher le modal
        modalElement.removeAttribute('aria-hidden');
        
        // Observer les changements d'attribut aria-hidden pour le retirer immédiatement
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                    // Si aria-hidden est ajouté et vaut "true" pendant que le modal est visible, le retirer
                    if (modalElement.getAttribute('aria-hidden') === 'true' && 
                        modalElement.classList.contains('show')) {
                        // Utiliser requestAnimationFrame pour s'assurer que c'est après le rendu
                        requestAnimationFrame(() => {
                            if (modalElement.classList.contains('show')) {
                                modalElement.removeAttribute('aria-hidden');
                            }
                        });
                    }
                }
            });
        });
        
        // Observer les changements d'attributs sur le modal
        observer.observe(modalElement, {
            attributes: true,
            attributeFilter: ['aria-hidden']
        });
        
        // Gérer le focus après que le modal soit complètement affiché
        const handleModalShown = function() {
            // S'assurer que aria-hidden est bien retiré
            modalElement.removeAttribute('aria-hidden');
            
            // Arrêter l'observer
            observer.disconnect();
            
            // Réactiver les tabindex des boutons
            const closeBtn = modalElement.querySelector('.btn-secondary[data-bs-dismiss="modal"]');
            const submitBtn = document.getElementById('cancelOrderSubmitBtn');
            if (closeBtn) closeBtn.removeAttribute('tabindex');
            if (submitBtn) submitBtn.removeAttribute('tabindex');
            
            // Mettre le focus sur le textarea
            const textarea = document.getElementById('cancelReason');
            if (textarea) {
                // Utiliser un double requestAnimationFrame pour s'assurer que tout est rendu
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        textarea.focus();
                    });
                });
            }
            
            // Nettoyer l'écouteur
            modalElement.removeEventListener('shown.bs.modal', handleModalShown);
        };
        
        // Gérer la fermeture du modal pour nettoyer l'observer
        const handleModalHidden = function() {
            observer.disconnect();
            modalElement.removeEventListener('hidden.bs.modal', handleModalHidden);
        };
        
        // Ajouter les écouteurs
        modalElement.addEventListener('shown.bs.modal', handleModalShown, { once: true });
        modalElement.addEventListener('hidden.bs.modal', handleModalHidden, { once: true });
        
        // Créer et afficher le modal
        const modal = new bootstrap.Modal(modalElement, {
            focus: false, // Désactiver le focus automatique de Bootstrap
            keyboard: true
        });
        modal.show();
    }
}

// Gérer la soumission du formulaire de confirmation
const confirmOrderForm = document.getElementById('confirmOrderForm');
if (confirmOrderForm) {
    confirmOrderForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentConfirmOrderId) {
        alert('Erreur: Aucune commande sélectionnée.');
        return;
    }

    const formData = new FormData(this);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        alert('Jeton CSRF introuvable. Veuillez rafraîchir la page.');
        return;
    }

    formData.append('_token', csrfToken);
    
    const submitBtn = document.getElementById('confirmOrderSubmitBtn');
    const originalHtml = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Traitement...';

    fetch(`/admin/orders/${currentConfirmOrderId}/confirm`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    })
    .then(async response => {
        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            data = null;
        }

        if (!response.ok) {
            const message = data?.message || 'Une erreur est survenue lors de la confirmation.';
            throw new Error(message);
        }

        // Fermer le modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmOrderModal'));
        if (modal) {
            modal.hide();
        }

        // Afficher un message de succès
        const successMessage = data?.message || 'Commande confirmée avec succès.';
        
        // Utiliser une notification moderne si disponible, sinon alert
        if (typeof window.showNotification === 'function') {
            window.showNotification(successMessage, 'success');
        } else {
            alert(successMessage);
        }

        // Recharger la page après un court délai
        setTimeout(() => {
            window.location.reload();
        }, 500);
    })
    .catch(error => {
        console.error(error);
        alert(error.message || 'Impossible de confirmer la commande.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
    });
    });
}

// Gérer la soumission du formulaire d'annulation
const cancelOrderForm = document.getElementById('cancelOrderForm');
if (cancelOrderForm) {
    cancelOrderForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentCancelOrderId) {
        alert('Erreur: Aucune commande sélectionnée.');
        return;
    }

    const formData = new FormData(this);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        alert('Jeton CSRF introuvable. Veuillez rafraîchir la page.');
        return;
    }

    formData.append('_token', csrfToken);
    
    const submitBtn = document.getElementById('cancelOrderSubmitBtn');
    const originalHtml = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Traitement...';

    fetch(`/admin/orders/${currentCancelOrderId}/cancel`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    })
    .then(async response => {
        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            data = null;
        }

        if (!response.ok) {
            const message = data?.message || 'Une erreur est survenue lors de l\'annulation.';
            throw new Error(message);
        }

        // Fermer le modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('cancelOrderModal'));
        if (modal) {
            modal.hide();
        }

        // Afficher un message de succès
        const successMessage = data?.message || 'Commande annulée avec succès.';
        
        // Utiliser une notification moderne si disponible, sinon alert
        if (typeof window.showNotification === 'function') {
            window.showNotification(successMessage, 'success');
        } else {
            alert(successMessage);
        }

        // Recharger la page après un court délai
        setTimeout(() => {
            window.location.reload();
        }, 500);
    })
    .catch(error => {
        console.error(error);
        alert(error.message || 'Impossible d\'annuler la commande.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
    });
    });
}

function handleOrderAction(button) {
    if (!button || button.disabled) {
        return;
    }

    const url = button.dataset.actionUrl;
    if (!url) {
        console.error('Aucune URL d\'action disponible pour ce bouton.');
        return;
    }

    const confirmMessage = button.dataset.confirm;
    if (confirmMessage && !confirm(confirmMessage)) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        alert('Jeton CSRF introuvable. Veuillez rafraîchir la page.');
        return;
    }

    const originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    })
    .then(async response => {
        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            data = null;
        }

        if (!response.ok) {
            const message = data?.message || 'Une erreur est survenue lors du traitement.';
            throw new Error(message);
        }

        const successMessage = button.dataset.success || data?.message || 'Action effectuée avec succès.';
        alert(successMessage);

        if (button.dataset.refresh !== 'false') {
            window.location.reload();
        }
    })
    .catch(error => {
        console.error(error);
        alert(error.message || 'Impossible d\'exécuter l\'action demandée.');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalHtml;
    });
}
</script>
@endpush

@push('styles')
<style>
@media (max-width: 991.98px) {
    /* Réduire les paddings et margins sur tablette */
    .admin-panel {
        margin-bottom: 1rem;
    }
    
    /* Padding uniquement pour la première section principale */
    .admin-panel--main .admin-panel__body {
        padding: 1rem !important;
    }
    
    /* Pas de padding pour les autres sections */
    .admin-panel:not(.admin-panel--main) .admin-panel__body {
        padding: 0 !important;
    }
    
    .admin-panel__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-panel__header h3 {
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    
    .admin-stats-grid {
        gap: 0.5rem !important;
    }
    
    .admin-stat-card {
        padding: 0.75rem 0.875rem !important;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.5rem;
        --bs-gutter-y: 0.5rem;
    }
    
    .admin-panel__body .row.g-3 {
        --bs-gutter-x: 0.375rem;
        --bs-gutter-y: 0.375rem;
    }
    
    .admin-panel__body .row.mb-4 {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-panel__body .row.mt-2 {
        margin-top: 0.375rem !important;
    }
    
    .admin-card__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-card__body {
        padding: 0.5rem;
    }
    
    /* Supprimer les scrollbars des conteneurs, garder seulement celle de table-responsive */
    .admin-table {
        overflow: visible !important;
    }
    
    .admin-panel__body {
        overflow: visible !important;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
}

@media (max-width: 767.98px) {
    /* Réduire encore plus les paddings et margins sur mobile */
    .admin-panel {
        margin-bottom: 0.75rem;
    }
    
    /* Padding uniquement pour la première section principale */
    .admin-panel--main .admin-panel__body {
        padding: 0.75rem !important;
    }
    
    /* Pas de padding pour les autres sections */
    .admin-panel:not(.admin-panel--main) .admin-panel__body {
        padding: 0 !important;
    }
    
    .admin-panel__header {
        padding: 0.375rem 0.5rem;
    }
    
    .admin-panel__header h3 {
        font-size: 0.95rem;
        margin-bottom: 0.125rem;
    }
    
    .admin-stats-grid {
        gap: 0.375rem !important;
    }
    
    .admin-stat-card {
        padding: 0.5rem 0.625rem !important;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.375rem;
        --bs-gutter-y: 0.375rem;
    }
    
    .admin-panel__body .row.g-3 {
        --bs-gutter-x: 0.25rem;
        --bs-gutter-y: 0.25rem;
    }
    
    .admin-panel__body .row.mb-4 {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-panel__body .row.mt-2 {
        margin-top: 0.375rem !important;
    }
    
    .admin-card__header {
        padding: 0.5rem 0.625rem;
    }
    
    .admin-card__body {
        padding: 0.375rem;
    }
    
    /* Supprimer les scrollbars des conteneurs, garder seulement celle de table-responsive */
    .admin-table {
        overflow: visible !important;
    }
    
    .admin-panel__body {
        overflow: visible !important;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
}
</style>
@endpush