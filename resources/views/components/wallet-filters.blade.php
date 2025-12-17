@props([
    'type' => 'transactions', // 'transactions' ou 'payouts'
    'filters' => []
])

<div class="wallet-filters-card">
    <div class="filters-header">
        <h5 class="filters-title">
            <i class="fas fa-filter me-2"></i>Filtres et recherche
        </h5>
        @if(request()->hasAny(array_keys($filters)))
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetWalletFilters()">
            <i class="fas fa-undo me-1"></i>Réinitialiser
        </button>
        @endif
    </div>

    <form id="walletFiltersForm" method="GET" action="{{ request()->url() }}" class="filters-form">
        @csrf
        <div class="row g-3">
            <!-- Recherche globale -->
            <div class="col-md-6">
                <label for="search" class="form-label">
                    <i class="fas fa-search me-1"></i>Rechercher
                </label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="search" 
                    name="search" 
                    placeholder="Référence, description..."
                    value="{{ request('search') }}"
                    autocomplete="off">
                <small class="form-text text-muted">Rechercher par référence ou description</small>
            </div>

            @if($type === 'transactions')
            <!-- Type de transaction -->
            <div class="col-md-3">
                <label for="type" class="form-label">
                    <i class="fas fa-tag me-1"></i>Type
                </label>
                <select name="type" id="type" class="form-select">
                    <option value="">Tous les types</option>
                    <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Crédit</option>
                    <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Débit</option>
                    <option value="commission" {{ request('type') == 'commission' ? 'selected' : '' }}>Commission</option>
                    <option value="payout" {{ request('type') == 'payout' ? 'selected' : '' }}>Retrait</option>
                    <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>Remboursement</option>
                    <option value="bonus" {{ request('type') == 'bonus' ? 'selected' : '' }}>Bonus</option>
                </select>
            </div>
            @endif

            <!-- Statut -->
            <div class="col-md-3">
                <label for="status" class="form-label">
                    <i class="fas fa-info-circle me-1"></i>Statut
                </label>
                <select name="status" id="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    @if($type === 'transactions')
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Complété</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échoué</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                    @else
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>En cours</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Complété</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échoué</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                    @endif
                </select>
            </div>

            <!-- Date de début -->
            <div class="col-md-3">
                <label for="from" class="form-label">
                    <i class="fas fa-calendar-alt me-1"></i>Du
                </label>
                <input 
                    type="date" 
                    name="from" 
                    id="from" 
                    class="form-control" 
                    value="{{ request('from') }}"
                    max="{{ date('Y-m-d') }}">
            </div>

            <!-- Date de fin -->
            <div class="col-md-3">
                <label for="to" class="form-label">
                    <i class="fas fa-calendar-alt me-1"></i>Au
                </label>
                <input 
                    type="date" 
                    name="to" 
                    id="to" 
                    class="form-control" 
                    value="{{ request('to') }}"
                    max="{{ date('Y-m-d') }}">
            </div>

            @if($type === 'transactions')
            <!-- Montant minimum -->
            <div class="col-md-3">
                <label for="min_amount" class="form-label">
                    <i class="fas fa-dollar-sign me-1"></i>Montant min
                </label>
                <input 
                    type="number" 
                    name="min_amount" 
                    id="min_amount" 
                    class="form-control" 
                    placeholder="0.00"
                    value="{{ request('min_amount') }}"
                    step="0.01"
                    min="0">
            </div>

            <!-- Montant maximum -->
            <div class="col-md-3">
                <label for="max_amount" class="form-label">
                    <i class="fas fa-dollar-sign me-1"></i>Montant max
                </label>
                <input 
                    type="number" 
                    name="max_amount" 
                    id="max_amount" 
                    class="form-control" 
                    placeholder="10000.00"
                    value="{{ request('max_amount') }}"
                    step="0.01"
                    min="0">
            </div>
            @endif

            <!-- Boutons d'action -->
            <div class="col-12">
                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Appliquer les filtres
                    </button>
                    @if(request()->hasAny(['search', 'type', 'status', 'from', 'to', 'min_amount', 'max_amount']))
                    <a href="{{ request()->url() }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Effacer tous les filtres
                    </a>
                    @endif
                    <button type="button" class="btn btn-outline-info" onclick="toggleAdvancedFilters()">
                        <i class="fas fa-cog me-1"></i>Options avancées
                    </button>
                </div>
            </div>

            <!-- Filtres avancés (cachés par défaut) -->
            <div class="col-12 advanced-filters-section" id="advancedFilters" style="display: none;">
                <hr>
                <div class="row g-3">
                    <!-- Tri -->
                    <div class="col-md-6">
                        <label for="sort_by" class="form-label">
                            <i class="fas fa-sort me-1"></i>Trier par
                        </label>
                        <select name="sort_by" id="sort_by" class="form-select">
                            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date de création</option>
                            <option value="amount" {{ request('sort_by') == 'amount' ? 'selected' : '' }}>Montant</option>
                            @if($type === 'transactions')
                            <option value="balance_after" {{ request('sort_by') == 'balance_after' ? 'selected' : '' }}>Solde après</option>
                            @endif
                        </select>
                    </div>

                    <!-- Ordre de tri -->
                    <div class="col-md-6">
                        <label for="sort_order" class="form-label">
                            <i class="fas fa-arrow-down me-1"></i>Ordre
                        </label>
                        <select name="sort_order" id="sort_order" class="form-select">
                            <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Décroissant (plus récent)</option>
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Croissant (plus ancien)</option>
                        </select>
                    </div>

                    <!-- Nombre de résultats par page -->
                    <div class="col-md-6">
                        <label for="per_page" class="form-label">
                            <i class="fas fa-list me-1"></i>Résultats par page
                        </label>
                        <select name="per_page" id="per_page" class="form-select">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request('per_page', '20') == '20' ? 'selected' : '' }}>20</option>
                            <option value="30" {{ request('per_page') == '30' ? 'selected' : '' }}>30</option>
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Résumé des filtres actifs -->
    @if(request()->hasAny(['search', 'type', 'status', 'from', 'to', 'min_amount', 'max_amount']))
    <div class="active-filters">
        <strong><i class="fas fa-check-circle me-1"></i>Filtres actifs :</strong>
        @if(request('search'))
        <span class="filter-badge">Recherche: {{ request('search') }}</span>
        @endif
        @if(request('type'))
        <span class="filter-badge">Type: {{ ucfirst(request('type')) }}</span>
        @endif
        @if(request('status'))
        <span class="filter-badge">Statut: {{ ucfirst(request('status')) }}</span>
        @endif
        @if(request('from'))
        <span class="filter-badge">Du: {{ request('from') }}</span>
        @endif
        @if(request('to'))
        <span class="filter-badge">Au: {{ request('to') }}</span>
        @endif
        @if(request('min_amount'))
        <span class="filter-badge">Montant min: {{ request('min_amount') }}</span>
        @endif
        @if(request('max_amount'))
        <span class="filter-badge">Montant max: {{ request('max_amount') }}</span>
        @endif
    </div>
    @endif
</div>

@push('styles')
<style>
.wallet-filters-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f3f4f6;
}

.filters-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
}

.filters-form .form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
}

.filters-form .form-label i {
    color: #667eea;
}

.filters-form .form-select,
.filters-form .form-control {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    padding: 0.625rem 0.875rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.filters-form .form-select:focus,
.filters-form .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
}

.filters-form .form-text {
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.advanced-filters-section {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.active-filters {
    margin-top: 1.25rem;
    padding: 1rem;
    background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
    border-radius: 8px;
    border: 1px solid #bfdbfe;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.active-filters strong {
    color: #1e40af;
}

.filter-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    background: white;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .filters-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .wallet-filters-card {
        padding: 1rem;
    }

    .active-filters {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
@endpush

@push('scripts')
<script>
function resetWalletFilters() {
    window.location.href = '{{ request()->url() }}';
}

function toggleAdvancedFilters() {
    const advancedSection = document.getElementById('advancedFilters');
    if (advancedSection.style.display === 'none') {
        advancedSection.style.display = 'block';
    } else {
        advancedSection.style.display = 'none';
    }
}

// Validation des dates
document.getElementById('from')?.addEventListener('change', function() {
    const toInput = document.getElementById('to');
    if (toInput && this.value) {
        toInput.min = this.value;
    }
});

document.getElementById('to')?.addEventListener('change', function() {
    const fromInput = document.getElementById('from');
    if (fromInput && this.value) {
        fromInput.max = this.value;
    }
});

// Validation des montants
document.getElementById('min_amount')?.addEventListener('change', function() {
    const maxAmountInput = document.getElementById('max_amount');
    if (maxAmountInput && this.value) {
        maxAmountInput.min = this.value;
    }
});

document.getElementById('max_amount')?.addEventListener('change', function() {
    const minAmountInput = document.getElementById('min_amount');
    if (minAmountInput && this.value) {
        minAmountInput.max = this.value;
    }
});

// Afficher les filtres avancés si des options avancées sont déjà appliquées
@if(request()->hasAny(['sort_by', 'sort_order', 'per_page']))
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('advancedFilters').style.display = 'block';
});
@endif
</script>
@endpush

