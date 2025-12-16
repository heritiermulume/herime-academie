@extends('layouts.admin')

@section('title', 'Paiements aux formateurs externes')
@section('admin-title', 'Paiements aux formateurs externes')
@section('admin-subtitle', 'Suivez les paiements effectués via pawaPay aux formateurs externes')

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <!-- Statistiques -->
            <div class="admin-stats-grid mb-4">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Total</p>
                    <p class="admin-stat-card__value">{{ $stats['total'] }}</p>
                    <p class="admin-stat-card__muted">Payouts enregistrés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">En attente</p>
                    <p class="admin-stat-card__value">{{ $stats['pending'] }}</p>
                    <p class="admin-stat-card__muted">En cours de traitement</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Complétés</p>
                    <p class="admin-stat-card__value">{{ $stats['completed'] }}</p>
                    <p class="admin-stat-card__muted">Paiements réussis</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Échoués</p>
                    <p class="admin-stat-card__value">{{ $stats['failed'] }}</p>
                    <p class="admin-stat-card__muted">Paiements échoués</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Montant total</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['total_amount'], 2) }}</p>
                    <p class="admin-stat-card__muted">USD payés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Commission</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['total_commission'], 2) }}</p>
                    <p class="admin-stat-card__muted">USD retenus</p>
                </div>
            </div>

            <!-- Filtres -->
            <x-admin.search-panel
                :action="route('admin.instructor-payouts')"
                formId="payoutsFilterForm"
                filtersId="payoutsFilters"
                :hasFilters="true"
                :searchValue="request('search')"
                placeholder="Rechercher par ID payout ou numéro de commande..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>En traitement</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Complété</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échoué</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Formateur</label>
                            <select class="form-select" name="instructor_id">
                                <option value="">Tous les formateurs</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}" {{ request('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                        {{ $instructor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Tri</label>
                            <select class="form-select" name="sort">
                                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date de création</option>
                                <option value="amount" {{ request('sort') == 'amount' ? 'selected' : '' }}>Montant</option>
                                <option value="status" {{ request('sort') == 'status' ? 'selected' : '' }}>Statut</option>
                                <option value="processed_at" {{ request('sort') == 'processed_at' ? 'selected' : '' }}>Date de traitement</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Ajustez les filtres puis appliquez-les.</span>
                        <a href="{{ route('admin.instructor-payouts') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            <!-- Filtres actifs -->
            @if(request('search') || request('status') || request('instructor_id'))
            <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <i class="fas fa-filter me-2"></i><strong>Filtres actifs :</strong>
                    @if(request('search'))
                        <span class="badge bg-primary ms-2">Recherche: "{{ request('search') }}"</span>
                    @endif
                    @if(request('status'))
                        <span class="badge bg-info ms-2">Statut: {{ ucfirst(request('status')) }}</span>
                    @endif
                    @if(request('instructor_id'))
                        <span class="badge bg-warning ms-2">Formateur: {{ $instructors->firstWhere('id', request('instructor_id'))->name ?? 'N/A' }}</span>
                    @endif
                </div>
                <a href="{{ route('admin.instructor-payouts') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-times me-1"></i>Effacer les filtres
                </a>
            </div>
            @endif

            <!-- Table des payouts -->
            <div class="admin-table">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID Payout</th>
                                <th>Formateur</th>
                                <th>Cours</th>
                                <th>Commande</th>
                                <th>Montant</th>
                                <th>Commission</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payouts as $payout)
                            <tr>
                                <td>
                                    <code class="small">{{ str()->limit($payout->payout_id, 20) }}</code>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $payout->instructor->avatar_url }}" alt="{{ $payout->instructor->name }}" 
                                             class="admin-user-avatar" style="width: 32px; height: 32px;">
                                        <div>
                                            <div class="fw-semibold">{{ $payout->instructor->name }}</div>
                                            <small class="text-muted">{{ $payout->instructor->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.courses.show', $payout->course) }}" class="text-decoration-none">
                                        {{ str()->limit($payout->course->title, 30) }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $payout->order) }}" class="text-decoration-none">
                                        {{ $payout->order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ number_format($payout->amount, 2) }} {{ $payout->currency }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ number_format($payout->commission_amount, 2) }} {{ $payout->currency }}
                                        <br>
                                        <span class="badge bg-secondary">{{ $payout->commission_percentage }}%</span>
                                    </small>
                                </td>
                                <td>
                                    @switch($payout->status)
                                        @case('pending')
                                            <span class="admin-chip admin-chip--warning">
                                                <i class="fas fa-clock me-1"></i>En attente
                                            </span>
                                            @break
                                        @case('processing')
                                            <span class="admin-chip admin-chip--info">
                                                <i class="fas fa-spinner fa-spin me-1"></i>En traitement
                                            </span>
                                            @break
                                        @case('completed')
                                            <span class="admin-chip admin-chip--success">
                                                <i class="fas fa-check-circle me-1"></i>Complété
                                            </span>
                                            @break
                                        @case('failed')
                                            <span class="admin-chip admin-chip--danger">
                                                <i class="fas fa-times-circle me-1"></i>Échoué
                                            </span>
                                            @break
                                        @default
                                            <span class="admin-chip admin-chip--neutral">{{ $payout->status }}</span>
                                    @endswitch
                                    @if($payout->pawapay_status)
                                        <br><small class="text-muted">pawaPay: {{ $payout->pawapay_status }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <small class="text-muted">Créé:</small><br>
                                        {{ $payout->created_at->format('d/m/Y H:i') }}
                                    </div>
                                    @if($payout->processed_at)
                                        <div class="mt-1">
                                            <small class="text-muted">Traité:</small><br>
                                            {{ $payout->processed_at->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('moneroo.payout.status', $payout->payout_id) }}" target="_blank">
                                                    <i class="fas fa-sync me-2"></i>Vérifier le statut
                                                </a>
                                            </li>
                                            @if($payout->failure_reason)
                                                <li>
                                                    <button class="dropdown-item" onclick="showFailureReason('{{ $payout->failure_reason }}')">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>Voir l'erreur
                                                    </button>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Aucun payout trouvé</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($payouts->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $payouts->links() }}
            </div>
            @endif
        </div>
    </section>

    <!-- Modal pour afficher l'erreur -->
    <div class="modal fade" id="failureReasonModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Raison de l'échec</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="failureReasonText"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
/* Statistiques en 2 colonnes sur desktop */
@media (min-width: 992px) {
    .admin-panel--main .admin-stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

/* Désactiver le scrollbar du conteneur sur mobile */
@media (max-width: 767.98px) {
    /* Désactiver le scrollbar du conteneur principal et de tous les parents */
    .admin-shell,
    .admin-shell__container,
    .admin-shell__content {
        overflow-x: visible !important;
        overflow-y: visible !important;
    }
    
    .admin-panel--main,
    .admin-panel--main .admin-panel__body {
        overflow-x: visible !important;
        overflow-y: visible !important;
        max-width: 100% !important;
    }
    
    /* Garder le scrollbar uniquement pour la table */
    .admin-table .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        width: 100% !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
function showFailureReason(reason) {
    document.getElementById('failureReasonText').textContent = reason;
    const modal = new bootstrap.Modal(document.getElementById('failureReasonModal'));
    modal.show();
}
</script>
@endpush

