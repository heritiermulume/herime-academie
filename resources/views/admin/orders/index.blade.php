@extends('layouts.app')

@section('title', 'Gestion des Commandes - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-sm" title="Tableau de bord">
                                <i class="fas fa-tachometer-alt"></i>
                            </a>
                            <h4 class="mb-0">
                                <i class="fas fa-shopping-bag me-2"></i>Gestion des Commandes
                            </h4>
                        </div>
                        <div>
                            <button class="btn btn-light" onclick="exportOrders()">
                                <i class="fas fa-download me-1"></i>Exporter
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['total'] ?? 0 }}</h5>
                                    <p class="card-text small">Total</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['pending'] ?? 0 }}</h5>
                                    <p class="card-text small">En attente</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['confirmed'] ?? 0 }}</h5>
                                    <p class="card-text small">Confirmées</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['paid'] ?? 0 }}</h5>
                                    <p class="card-text small">Payées</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['completed'] ?? 0 }}</h5>
                                    <p class="card-text small">Terminées</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['cancelled'] ?? 0 }}</h5>
                                    <p class="card-text small">Annulées</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenus -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                <div class="card-body text-center">
                                    <h4 class="mb-0 text-white">
                                        <i class="fas fa-dollar-sign me-2"></i>
                                        Revenus totaux: {{ \App\Helpers\CurrencyHelper::formatWithSymbol($stats['total_revenue'] ?? 0) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres et recherche -->
                    <form method="GET" action="{{ route('admin.orders.index') }}" id="filterForm">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           value="{{ request('search') }}"
                                           placeholder="Rechercher par numéro, client...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Payée</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminée</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="payment_method">
                                    <option value="">Tous les modes</option>
                                    <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Carte bancaire</option>
                                    <option value="paypal" {{ request('payment_method') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                                    <option value="mobile" {{ request('payment_method') == 'mobile' ? 'selected' : '' }}>Mobile Money</option>
                                    <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Virement bancaire</option>
                                    <option value="whatsapp" {{ request('payment_method') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" 
                                       value="{{ request('date_from') }}" placeholder="Date de début">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" 
                                       value="{{ request('date_to') }}" placeholder="Date de fin">
                            </div>
                            <div class="col-md-1">
                                <div class="btn-group w-100" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter me-1"></i>Filtrer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Tableau des commandes -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'order_number', 'direction' => request('sort') == 'order_number' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Commande
                                            @if(request('sort') == 'order_number')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @else
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'user_id', 'direction' => request('sort') == 'user_id' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Client
                                            @if(request('sort') == 'user_id')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @else
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_amount', 'direction' => request('sort') == 'total_amount' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Montant
                                            @if(request('sort') == 'total_amount')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @else
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Statut</th>
                                    <th>Mode de paiement</th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Date
                                            @if(request('sort') == 'created_at')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @else
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input order-checkbox" value="{{ $order->id }}">
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->order_number }}</strong>
                                            @if($order->payment_reference)
                                                <br><small class="text-muted">{{ $order->payment_reference }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 40px; height: 40px; font-size: 14px; font-weight: bold;">
                                                {{ strtoupper(substr($order->user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $order->user->name }}</h6>
                                                <small class="text-muted">{{ $order->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-success">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->total_amount, $order->currency) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge order-status-{{ $order->status }}">
                                            @switch($order->status)
                                                @case('pending')
                                                    En attente
                                                    @break
                                                @case('confirmed')
                                                    Confirmée
                                                    @break
                                                @case('paid')
                                                    Payée
                                                    @break
                                                @case('completed')
                                                    Terminée
                                                    @break
                                                @case('cancelled')
                                                    Annulée
                                                    @break
                                                @default
                                                    {{ ucfirst($order->status) }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order->payment_method === 'card' ? 'primary' : ($order->payment_method === 'paypal' ? 'info' : ($order->payment_method === 'mobile' ? 'success' : ($order->payment_method === 'bank' ? 'warning' : 'secondary'))) }}">
                                            @switch($order->payment_method)
                                                @case('card')
                                                    Carte bancaire
                                                    @break
                                                @case('paypal')
                                                    PayPal
                                                    @break
                                                @case('mobile')
                                                    Mobile Money
                                                    @break
                                                @case('bank')
                                                    Virement bancaire
                                                    @break
                                                @case('whatsapp')
                                                    WhatsApp
                                                    @break
                                                @default
                                                    {{ ucfirst($order->payment_method ?? 'Non défini') }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($order->status === 'pending')
                                                <button class="btn btn-sm btn-outline-success" 
                                                        onclick="confirmOrder({{ $order->id }})" title="Confirmer">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="cancelOrder({{ $order->id }})" title="Annuler">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @elseif($order->status === 'confirmed')
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="markAsPaid({{ $order->id }})" title="Marquer comme payé">
                                                    <i class="fas fa-credit-card"></i>
                                                </button>
                                            @elseif($order->status === 'paid')
                                                <button class="btn btn-sm btn-outline-warning" 
                                                        onclick="markAsCompleted({{ $order->id }})" title="Marquer comme terminé">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucune commande trouvée</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Résultats de recherche -->
                    @if(request()->hasAny(['search', 'status', 'payment_method', 'date_from', 'date_to']))
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Filtres appliqués :</strong>
                        @if(request('search'))
                            Recherche: "{{ request('search') }}"
                        @endif
                        @if(request('status'))
                            | Statut: {{ ucfirst(request('status')) }}
                        @endif
                        @if(request('payment_method'))
                            | Mode: {{ ucfirst(request('payment_method')) }}
                        @endif
                        @if(request('date_from'))
                            | Depuis: {{ request('date_from') }}
                        @endif
                        @if(request('date_to'))
                            | Jusqu'à: {{ request('date_to') }}
                        @endif
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="fas fa-times me-1"></i>Effacer les filtres
                        </a>
                    </div>
                    @endif

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <span class="text-muted">
                                Affichage de {{ $orders->firstItem() ?? 0 }} à {{ $orders->lastItem() ?? 0 }} sur {{ $orders->total() }} commandes
                                @if(request()->hasAny(['search', 'status', 'payment_method', 'date_from', 'date_to']))
                                    ({{ $orders->count() }} résultat{{ $orders->count() > 1 ? 's' : '' }})
                                @endif
                            </span>
                        </div>
                        <div>
                            {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>

                    <!-- Actions en lot -->
                    <div class="mt-3" id="bulkActions" style="display: none;">
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-success" onclick="bulkAction('confirm')">
                                <i class="fas fa-check me-1"></i>Confirmer
                            </button>
                            <button class="btn btn-sm btn-info" onclick="bulkAction('mark-paid')">
                                <i class="fas fa-credit-card me-1"></i>Marquer payé
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="bulkAction('mark-completed')">
                                <i class="fas fa-check-double me-1"></i>Marquer terminé
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="bulkAction('cancel')">
                                <i class="fas fa-times me-1"></i>Annuler
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Modals -->
@include('admin.orders.partials.action-modals')

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

// Recherche en temps réel avec debounce
let searchTimeout;
document.querySelector('input[name="search"]').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
});

// Soumission automatique du formulaire lors du changement des sélecteurs
document.querySelectorAll('select[name="status"], select[name="payment_method"]').forEach(select => {
    select.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});

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

@push('styles')
<style>
/* Design moderne pour la page de gestion des commandes */
.card {
    border-radius: 15px;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #003366 0%, #004080 100%);
    border: none;
    padding: 1.5rem;
}

.table {
    margin-bottom: 0;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #003366;
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.table tbody tr {
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    transform: translateX(3px);
}

.badge {
    font-size: 0.85rem;
    padding: 0.4em 0.8em;
    font-weight: 500;
}

.btn-group .btn {
    border-radius: 0.375rem;
    margin-right: 2px;
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
}

.form-check-input:checked {
    background-color: #003366;
    border-color: #003366;
}

/* Order status badges */
.order-status-pending {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.order-status-confirmed {
    background-color: #17a2b8 !important;
    color: #fff !important;
}

.order-status-paid {
    background-color: #28a745 !important;
    color: #fff !important;
}

.order-status-completed {
    background-color: #6f42c1 !important;
    color: #fff !important;
}

.order-status-cancelled {
    background-color: #dc3545 !important;
    color: #fff !important;
}

/* Pagination styles */
.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #0d6efd;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.pagination .page-link:hover {
    color: #0a58ca;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
}

.pagination .page-link i {
    font-size: 0.75rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .card-header {
        padding: 0.75rem;
    }
    
    .card-header h4 {
        font-size: 1rem;
    }
    
    .card-header .btn-outline-light.btn-sm {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }
    
    .card-header .btn-light {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .table {
        font-size: 0.85rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .card-header .d-flex {
        flex-direction: column;
        gap: 0.5rem;
        align-items: stretch !important;
    }
    
    .card-header .btn-light:not(.btn-sm) {
        width: 100%;
    }
}
</style>
@endpush