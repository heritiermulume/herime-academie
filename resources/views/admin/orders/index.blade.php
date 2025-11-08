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
    <section class="admin-panel">
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
                                <th class="text-center" style="width:48px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
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
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input order-checkbox" value="{{ $order->id }}">
                                </td>
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
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-light" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-light" title="Confirmer" onclick="updateOrderStatus('{{ $order->id }}', 'confirm')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-light text-danger" title="Annuler" onclick="updateOrderStatus('{{ $order->id }}', 'cancel')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="admin-table__empty">
                                    <i class="fas fa-receipt mb-2 d-block"></i>
                                    Aucune commande ne correspond aux filtres sélectionnés.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-pagination justify-content-between align-items-center">
                <span class="text-muted">
                    Affichage de {{ $orders->firstItem() ?? 0 }} à {{ $orders->lastItem() ?? 0 }} sur {{ $orders->total() }} commandes
                </span>
                {{ $orders->appends(request()->query())->links() }}
            </div>

            <div class="mt-3" id="bulkActions" style="display:none;">
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-success" onclick="bulkAction('confirm')">
                        <i class="fas fa-check me-1"></i>Confirmer
                    </button>
                    <button class="btn btn-sm btn-info" onclick="bulkAction('mark-paid')">
                        <i class="fas fa-credit-card me-1"></i>Marquer payée
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="bulkAction('complete')">
                        <i class="fas fa-flag-checkered me-1"></i>Terminer
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="bulkAction('cancel')">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
// Sélection multiple
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkActions();
});

document.querySelectorAll('.order-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkActions);
});

function toggleBulkActions() {
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    
    if (checkedBoxes.length > 0) {
        bulkActions.style.display = 'block';
    } else {
        bulkActions.style.display = 'none';
    }
}

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

// Fonction pour les actions en lot
function bulkAction(action) {
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
    const orderIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (orderIds.length === 0) {
        alert('Veuillez sélectionner au moins une commande.');
        return;
    }
    
    if (action === 'cancel' && !confirm('Êtes-vous sûr de vouloir annuler les commandes sélectionnées ?')) {
        return;
    }
    
    // Ici vous pouvez implémenter les actions en lot
    console.log(`Action: ${action}, Orders: ${orderIds.join(',')}`);
    alert(`Action "${action}" appliquée à ${orderIds.length} commande(s).`);
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
</script>
@endpush