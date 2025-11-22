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
                    <p class="admin-stat-card__muted">Cours délivrés</p>
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
                                <option value="whatsapp" {{ request('payment_method') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
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
                                <td class="text-center align-top">
                                    @if($loop->first)
                                        <div class="dropdown d-none d-md-block">
                                            <button class="btn btn-sm btn-light course-actions-btn" type="button" id="actionsDropdown{{ $order->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown{{ $order->id }}">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.orders.show', $order) }}">
                                                        <i class="fas fa-eye me-2"></i>Voir
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       data-action-url="{{ route('admin.orders.confirm', $order) }}"
                                                       data-confirm="Confirmer cette commande ?"
                                                       data-success="Commande confirmée avec succès."
                                                       onclick="handleOrderAction(this); return false;">
                                                        <i class="fas fa-check me-2"></i>Confirmer
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" 
                                                       data-action-url="{{ route('admin.orders.cancel', $order) }}"
                                                       data-confirm="Annuler cette commande ?"
                                                       data-success="Commande annulée."
                                                       onclick="handleOrderAction(this); return false;">
                                                        <i class="fas fa-times me-2"></i>Annuler
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="dropdown d-md-none">
                                            <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="actionsDropdownMobile{{ $order->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdownMobile{{ $order->id }}">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.orders.show', $order) }}">
                                                        <i class="fas fa-eye me-2"></i>Voir
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       data-action-url="{{ route('admin.orders.confirm', $order) }}"
                                                       data-confirm="Confirmer cette commande ?"
                                                       data-success="Commande confirmée avec succès."
                                                       onclick="handleOrderAction(this); return false;">
                                                        <i class="fas fa-check me-2"></i>Confirmer
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" 
                                                       data-action-url="{{ route('admin.orders.cancel', $order) }}"
                                                       data-confirm="Annuler cette commande ?"
                                                       data-success="Commande annulée."
                                                       onclick="handleOrderAction(this); return false;">
                                                        <i class="fas fa-times me-2"></i>Annuler
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @else
                                        <div class="dropup d-none d-md-block">
                                            <button class="btn btn-sm btn-light course-actions-btn" type="button" id="actionsDropdown{{ $order->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown{{ $order->id }}">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.orders.show', $order) }}">
                                                        <i class="fas fa-eye me-2"></i>Voir
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       data-action-url="{{ route('admin.orders.confirm', $order) }}"
                                                       data-confirm="Confirmer cette commande ?"
                                                       data-success="Commande confirmée avec succès."
                                                       onclick="handleOrderAction(this); return false;">
                                                        <i class="fas fa-check me-2"></i>Confirmer
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" 
                                                       data-action-url="{{ route('admin.orders.cancel', $order) }}"
                                                       data-confirm="Annuler cette commande ?"
                                                       data-success="Commande annulée."
                                                       onclick="handleOrderAction(this); return false;">
                                                        <i class="fas fa-times me-2"></i>Annuler
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="dropup d-md-none">
                                            <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="actionsDropdownMobile{{ $order->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdownMobile{{ $order->id }}">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.orders.show', $order) }}">
                                                        <i class="fas fa-eye me-2"></i>Voir
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       data-action-url="{{ route('admin.orders.confirm', $order) }}"
                                                       data-confirm="Confirmer cette commande ?"
                                                       data-success="Commande confirmée avec succès."
                                                       onclick="handleOrderAction(this); return false;">
                                                        <i class="fas fa-check me-2"></i>Confirmer
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" 
                                                       data-action-url="{{ route('admin.orders.cancel', $order) }}"
                                                       data-confirm="Annuler cette commande ?"
                                                       data-success="Commande annulée."
                                                       onclick="handleOrderAction(this); return false;">
                                                        <i class="fas fa-times me-2"></i>Annuler
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
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

function handleOrderAction(button) {
    if (!button || button.disabled) {
        return;
    }

    const url = button.dataset.actionUrl;
    if (!url) {
        console.error('Aucune URL d’action disponible pour ce bouton.');
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
        alert(error.message || 'Impossible d’exécuter l’action demandée.');
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