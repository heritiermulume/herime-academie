@extends('layouts.admin')

@section('title', 'Ambassadeurs')
@section('admin-title', 'Gestion des Ambassadeurs')
@section('admin-subtitle', 'Consultez et gérez les ambassadeurs actifs, leurs codes promo et leurs gains')

@section('admin-content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <!-- Onglets -->
            <ul class="nav nav-tabs mb-4" id="ambassadorsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($tab ?? 'ambassadors') === 'ambassadors' ? 'active' : '' }}" 
                            id="ambassadors-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#ambassadors" 
                            type="button" 
                            role="tab"
                            onclick="window.location.href='{{ route('admin.ambassadors.index', ['tab' => 'ambassadors']) }}'">
                        <i class="fas fa-users-cog me-2"></i>Ambassadeurs
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($tab ?? 'ambassadors') === 'applications' ? 'active' : '' }}" 
                            id="applications-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#applications" 
                            type="button" 
                            role="tab"
                            onclick="window.location.href='{{ route('admin.ambassadors.index', ['tab' => 'applications']) }}'">
                        <i class="fas fa-handshake me-2"></i>Candidatures
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($tab ?? 'ambassadors') === 'commissions' ? 'active' : '' }}" 
                            id="commissions-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#commissions" 
                            type="button" 
                            role="tab"
                            onclick="window.location.href='{{ route('admin.ambassadors.index', ['tab' => 'commissions']) }}'">
                        <i class="fas fa-money-bill-wave me-2"></i>Commissions
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="ambassadorsTabContent">
                <!-- Onglet Ambassadeurs -->
                <div class="tab-pane fade {{ ($tab ?? 'ambassadors') === 'ambassadors' ? 'show active' : '' }}" 
                     id="ambassadors" 
                     role="tabpanel" 
                     aria-labelledby="ambassadors-tab">
                    <!-- Statistiques -->
                    <div class="admin-stats-grid mb-4">
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Total</p>
                            <p class="admin-stat-card__value">{{ $ambassadorStats['total'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Ambassadeurs</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Actifs</p>
                            <p class="admin-stat-card__value">{{ $ambassadorStats['active'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">En activité</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Inactifs</p>
                            <p class="admin-stat-card__value">{{ $ambassadorStats['inactive'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Désactivés</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Gains totaux</p>
                            <p class="admin-stat-card__value">{{ number_format($ambassadorStats['total_earnings'] ?? 0, 2) }} {{ $currencyCode ?? 'USD' }}</p>
                            <p class="admin-stat-card__muted">Toutes commissions</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Payés</p>
                            <p class="admin-stat-card__value">{{ number_format($ambassadorStats['paid_earnings'] ?? 0, 2) }} {{ $currencyCode ?? 'USD' }}</p>
                            <p class="admin-stat-card__muted">Commissions versées</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">En attente</p>
                            <p class="admin-stat-card__value">{{ number_format($ambassadorStats['pending_earnings'] ?? 0, 2) }} {{ $currencyCode ?? 'USD' }}</p>
                            <p class="admin-stat-card__muted">Non encore payés</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Références</p>
                            <p class="admin-stat-card__value">{{ number_format($ambassadorStats['total_referrals'] ?? 0) }}</p>
                            <p class="admin-stat-card__muted">Utilisateurs uniques</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Ventes</p>
                            <p class="admin-stat-card__value">{{ number_format($ambassadorStats['total_sales'] ?? 0) }}</p>
                            <p class="admin-stat-card__muted">Commandes générées</p>
                        </div>
                    </div>

                    <x-admin.search-panel
                        :action="route('admin.ambassadors.index', ['tab' => 'ambassadors'])"
                        formId="ambassadorsFilterForm"
                        filtersId="ambassadorsFilters"
                        :hasFilters="true"
                        :searchValue="request('search')"
                        placeholder="Rechercher par nom ou email..."
                    >
                        <x-slot:filters>
                            <input type="hidden" name="tab" value="ambassadors">
                            <div class="admin-form-grid admin-form-grid--two mb-3">
                                <div>
                                    <label class="form-label fw-semibold">Statut</label>
                                    <select class="form-select" name="status">
                                        <option value="">Tous les statuts</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                                    </select>
                                </div>
                            </div>
                        </x-slot:filters>
                    </x-admin.search-panel>

                    <div id="bulkActionsContainer-ambassadorsTable"></div>

            <div class="admin-table mt-4">
                <div class="table-responsive">
                    <table class="table align-middle" id="ambassadorsTable" data-bulk-select="true" data-export-route="{{ route('admin.ambassadors.export') }}">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" data-select-all data-table-id="ambassadorsTable" title="Sélectionner tout">
                                </th>
                                <th>Ambassadeur</th>
                                        <th style="min-width: 180px;">Code Promo</th>
                                        <th style="min-width: 150px;">Gains totaux</th>
                                <th>Statut</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ambassadors as $ambassador)
                                <tr>
                                    <td>
                                        <input type="checkbox" data-item-id="{{ $ambassador->id }}" class="form-check-input">
                                    </td>
                                    <td style="max-width: 250px;">
                                        <div class="d-flex align-items-center gap-3">
                                                    <img src="{{ $ambassador->user->avatar_url }}" alt="{{ $ambassador->user->name }}" class="admin-user-avatar">
                                                    <div style="min-width: 0; flex: 1;">
                                                        <a href="{{ route('admin.ambassadors.show', $ambassador) }}" class="fw-semibold text-decoration-none text-dark text-truncate d-block" title="{{ $ambassador->user->name }}">{{ $ambassador->user->name }}</a>
                                                        <div class="text-muted small text-truncate d-block" title="{{ $ambassador->user->email }}">{{ $ambassador->user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="min-width: 180px; max-width: 220px;">
                                        @if($ambassador->activePromoCode())
                                                    <code class="promo-code-cell" style="font-size: 0.95rem; padding: 0.35rem 0.65rem; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem; display: inline-block; max-width: 100%; word-break: break-all;" title="{{ $ambassador->activePromoCode()->code }}">{{ $ambassador->activePromoCode()->code }}</code>
                                        @else
                                            <span class="text-muted">Aucun code</span>
                                        @endif
                                    </td>
                                            <td style="min-width: 150px; max-width: 180px; font-weight: 600; color: #0f172a; white-space: nowrap;">{{ number_format($ambassador->total_earnings, 2) }} {{ \App\Models\Setting::getBaseCurrency() }}</td>
                                    <td>
                                        <span class="badge bg-{{ $ambassador->is_active ? 'success' : 'secondary' }}">
                                            {{ $ambassador->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('admin.ambassadors.show', $ambassador) }}" class="btn btn-light btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteAmbassador({{ $ambassador->id }})" title="Supprimer l'ambassadeur">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">Aucun ambassadeur</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
                    <x-admin.pagination :paginator="$ambassadors" :showInfo="true" itemName="ambassadeurs" />
                </div>

                <!-- Onglet Candidatures -->
                <div class="tab-pane fade {{ ($tab ?? 'ambassadors') === 'applications' ? 'show active' : '' }}" 
                     id="applications" 
                     role="tabpanel" 
                     aria-labelledby="applications-tab">
                    <!-- Statistiques -->
                    <div class="admin-stats-grid mb-4">
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Total</p>
                            <p class="admin-stat-card__value">{{ $applicationStats['total'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Candidatures reçues</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">En attente</p>
                            <p class="admin-stat-card__value">{{ $applicationStats['pending'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">En file de traitement</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">En examen</p>
                            <p class="admin-stat-card__value">{{ $applicationStats['under_review'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Analyse en cours</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Approuvées</p>
                            <p class="admin-stat-card__value">{{ $applicationStats['approved'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Candidatures acceptées</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Rejetées</p>
                            <p class="admin-stat-card__value">{{ $applicationStats['rejected'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Candidatures refusées</p>
                        </div>
                    </div>

                    <x-admin.search-panel
                        :action="route('admin.ambassadors.index', ['tab' => 'applications'])"
                        formId="applicationsFilterForm"
                        filtersId="applicationsFilters"
                        :hasFilters="true"
                        :searchValue="request('search')"
                        placeholder="Rechercher par nom ou email..."
                    >
                        <x-slot:filters>
                            <input type="hidden" name="tab" value="applications">
                            <div class="admin-form-grid admin-form-grid--two mb-3">
                                <div>
                                    <label class="form-label fw-semibold">Statut</label>
                                    <select class="form-select" name="status">
                                        <option value="all">Tous les statuts</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>En examen</option>
                                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvée</option>
                                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                                    </select>
                                </div>
                            </div>
                        </x-slot:filters>
                    </x-admin.search-panel>

                    <div id="bulkActionsContainer-applicationsTable"></div>

                    <div class="admin-table mt-4">
                        <div class="table-responsive">
                            <table class="table align-middle" id="applicationsTable" data-bulk-select="true" data-export-route="{{ route('admin.ambassadors.applications.export') }}">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">
                                            <input type="checkbox" data-select-all data-table-id="applicationsTable" title="Sélectionner tout">
                                        </th>
                                        <th>Utilisateur</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $application)
                                        <tr>
                                            <td>
                                                <input type="checkbox" data-item-id="{{ $application->id }}" class="form-check-input">
                                            </td>
                                            <td style="max-width: 250px;">
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="{{ $application->user->avatar_url }}" alt="{{ $application->user->name }}" class="admin-user-avatar">
                                                    <div style="min-width: 0; flex: 1;">
                                                        <div class="fw-semibold text-truncate d-block" title="{{ $application->user->name }}">{{ $application->user->name }}</div>
                                                        <div class="text-muted small text-truncate d-block" title="{{ $application->user->email }}">{{ $application->user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $application->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $application->getStatusBadgeClass() }}">
                                                    {{ $application->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <a href="{{ route('admin.ambassadors.applications.show', $application) }}" class="btn btn-light btn-sm" title="Voir la candidature">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteApplication({{ $application->id }})" title="Supprimer la candidature">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">Aucune candidature</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <x-admin.pagination :paginator="$applications" :showInfo="true" itemName="candidatures" />
                </div>

                <!-- Onglet Commissions -->
                <div class="tab-pane fade {{ ($tab ?? 'ambassadors') === 'commissions' ? 'show active' : '' }}" 
                     id="commissions" 
                     role="tabpanel" 
                     aria-labelledby="commissions-tab">
                    <!-- Statistiques -->
                    <div class="admin-stats-grid mb-4">
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Total</p>
                            <p class="admin-stat-card__value">{{ $commissionStats['total'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Commissions</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">En attente</p>
                            <p class="admin-stat-card__value">{{ $commissionStats['pending'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Non approuvées</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Approuvées</p>
                            <p class="admin-stat-card__value">{{ $commissionStats['approved'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">En attente de paiement</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Payées</p>
                            <p class="admin-stat-card__value">{{ $commissionStats['paid'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Commissions versées</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Montant total</p>
                            <p class="admin-stat-card__value">{{ number_format($commissionStats['total_amount'] ?? 0, 2) }} {{ $currencyCode ?? 'USD' }}</p>
                            <p class="admin-stat-card__muted">Toutes commissions</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Payé</p>
                            <p class="admin-stat-card__value">{{ number_format($commissionStats['paid_amount'] ?? 0, 2) }} {{ $currencyCode ?? 'USD' }}</p>
                            <p class="admin-stat-card__muted">Montants versés</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">En attente</p>
                            <p class="admin-stat-card__value">{{ number_format($commissionStats['pending_amount'] ?? 0, 2) }} {{ $currencyCode ?? 'USD' }}</p>
                            <p class="admin-stat-card__muted">Montants non payés</p>
                        </div>
                    </div>

                    <x-admin.search-panel
                        :action="route('admin.ambassadors.index', ['tab' => 'commissions'])"
                        formId="commissionsFilterForm"
                        filtersId="commissionsFilters"
                        :hasFilters="true"
                        :searchValue="request('search')"
                        placeholder="Rechercher par numéro de commande..."
                    >
                        <x-slot:filters>
                            <input type="hidden" name="tab" value="commissions">
                            <div class="admin-form-grid admin-form-grid--two mb-3">
                                <div>
                                    <label class="form-label fw-semibold">Statut</label>
                                    <select class="form-select" name="status">
                                        <option value="all">Tous les statuts</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvée</option>
                                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payée</option>
                                    </select>
                                </div>
                            </div>
                        </x-slot:filters>
                    </x-admin.search-panel>

                    <div id="bulkActionsContainer-commissionsTable"></div>

                    <div class="admin-table mt-4">
                        <div class="table-responsive">
                            <table class="table align-middle" id="commissionsTable" data-bulk-select="true" data-export-route="{{ route('admin.ambassadors.commissions.export') }}">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">
                                            <input type="checkbox" data-select-all data-table-id="commissionsTable" title="Sélectionner tout">
                                        </th>
                                        <th>Ambassadeur</th>
                                        <th>Commande</th>
                                        <th>Montant</th>
                                        <th>Commission</th>
                                        <th>Statut</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($commissions as $commission)
                                        <tr>
                                            <td>
                                                <input type="checkbox" data-item-id="{{ $commission->id }}" class="form-check-input">
                                            </td>
                                            <td style="max-width: 250px;">
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="{{ $commission->ambassador->user->avatar_url }}" alt="{{ $commission->ambassador->user->name }}" class="admin-user-avatar">
                                                    <div style="min-width: 0; flex: 1;">
                                                        <div class="fw-semibold text-truncate d-block" title="{{ $commission->ambassador->user->name }}">{{ $commission->ambassador->user->name }}</div>
                                                        <div class="text-muted small text-truncate d-block" title="{{ $commission->ambassador->user->email }}">{{ $commission->ambassador->user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="max-width: 150px;">
                                                <span class="text-truncate d-block" title="{{ $commission->order->order_number }}">{{ $commission->order->order_number }}</span>
                                            </td>
                                            <td style="white-space: nowrap;">{{ number_format($commission->order_total, 2) }} {{ \App\Models\Setting::getBaseCurrency() }}</td>
                                            <td style="white-space: nowrap; font-weight: 600; color: #0f172a;">{{ number_format($commission->commission_amount, 2) }} {{ \App\Models\Setting::getBaseCurrency() }}</td>
                                            <td>
                                                <span class="badge bg-{{ $commission->getStatusBadgeClass() }}">
                                                    {{ $commission->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    @if($commission->status === 'pending')
                                                        <form method="POST" action="{{ route('admin.ambassadors.commissions.approve', $commission) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm" title="Approuver la commission">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($commission->status === 'approved')
                                                        <form method="POST" action="{{ route('admin.ambassadors.commissions.mark-paid', $commission) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-primary btn-sm" title="Marquer comme payée">
                                                                <i class="fas fa-money-bill-wave"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">Aucune commission</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <x-admin.pagination :paginator="$commissions" :showInfo="true" itemName="commissions" />
                </div>
            </div>
        </div>
    </section>

    <!-- Formulaires de suppression (cachés) -->
    <form id="deleteApplicationForm" method="POST" action="" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Formulaire de suppression d'ambassadeur (caché) -->
    <form id="deleteAmbassadorForm" method="POST" action="" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script src="{{ asset('js/bulk-actions.js') }}"></script>
<script>
// Initialiser la sélection multiple pour chaque onglet
document.addEventListener('DOMContentLoaded', function() {
    // Onglet Ambassadeurs
    const ambassadorsContainer = document.getElementById('bulkActionsContainer-ambassadorsTable');
    if (ambassadorsContainer) {
        const bar = document.createElement('div');
        bar.id = 'bulkActionsBar-ambassadorsTable';
        bar.className = 'bulk-actions-bar';
        bar.style.display = 'none';
        bar.innerHTML = `
            <div class="bulk-actions-bar__content">
                <div class="bulk-actions-bar__info">
                    <span class="bulk-actions-bar__count" id="selectedCount-ambassadorsTable">0</span>
                    <span class="bulk-actions-bar__text">élément(s) sélectionné(s)</span>
                </div>
                <div class="bulk-actions-bar__actions">
                    <button type="button" class="btn btn-sm btn-danger bulk-action-btn" data-action="delete" data-table-id="ambassadorsTable" data-confirm="true" data-confirm-message="Êtes-vous sûr de vouloir supprimer les ambassadeurs sélectionnés ?" data-route="{{ route('admin.ambassadors.bulk-action') }}" data-method="POST">
                        <i class="fas fa-trash me-1"></i>Supprimer
                    </button>
                    <button type="button" class="btn btn-sm btn-success bulk-action-btn" data-action="activate" data-table-id="ambassadorsTable" data-confirm="false" data-route="{{ route('admin.ambassadors.bulk-action') }}" data-method="POST">
                        <i class="fas fa-check me-1"></i>Activer
                    </button>
                    <button type="button" class="btn btn-sm btn-warning bulk-action-btn" data-action="deactivate" data-table-id="ambassadorsTable" data-confirm="false" data-route="{{ route('admin.ambassadors.bulk-action') }}" data-method="POST">
                        <i class="fas fa-ban me-1"></i>Désactiver
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown-ambassadorsTable" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i>Exporter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown-ambassadorsTable">
                            <li><a class="dropdown-item export-link" href="#" data-format="csv" data-table-id="ambassadorsTable"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                            <li><a class="dropdown-item export-link" href="#" data-format="excel" data-table-id="ambassadorsTable"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkActions.clearSelection('ambassadorsTable')">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                </div>
            </div>
        `;
        ambassadorsContainer.appendChild(bar);
    }
    bulkActions.init('ambassadorsTable', {
        exportRoute: '{{ route('admin.ambassadors.export') }}'
    });
    
    // Onglet Candidatures
    const applicationsContainer = document.getElementById('bulkActionsContainer-applicationsTable');
    if (applicationsContainer) {
        const bar = document.createElement('div');
        bar.id = 'bulkActionsBar-applicationsTable';
        bar.className = 'bulk-actions-bar';
        bar.style.display = 'none';
        bar.innerHTML = `
            <div class="bulk-actions-bar__content">
                <div class="bulk-actions-bar__info">
                    <span class="bulk-actions-bar__count" id="selectedCount-applicationsTable">0</span>
                    <span class="bulk-actions-bar__text">élément(s) sélectionné(s)</span>
                </div>
                <div class="bulk-actions-bar__actions">
                    <button type="button" class="btn btn-sm btn-danger bulk-action-btn" data-action="delete" data-table-id="applicationsTable" data-confirm="true" data-confirm-message="Êtes-vous sûr de vouloir supprimer les candidatures sélectionnées ?" data-route="{{ route('admin.ambassadors.applications.bulk-action') }}" data-method="POST">
                        <i class="fas fa-trash me-1"></i>Supprimer
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown-applicationsTable" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i>Exporter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown-applicationsTable">
                            <li><a class="dropdown-item export-link" href="#" data-format="csv" data-table-id="applicationsTable"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                            <li><a class="dropdown-item export-link" href="#" data-format="excel" data-table-id="applicationsTable"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkActions.clearSelection('applicationsTable')">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                </div>
            </div>
        `;
        applicationsContainer.appendChild(bar);
    }
    bulkActions.init('applicationsTable', {
        exportRoute: '{{ route('admin.ambassadors.applications.export') }}'
    });
    
    // Onglet Commissions
    const commissionsContainer = document.getElementById('bulkActionsContainer-commissionsTable');
    if (commissionsContainer) {
        const bar = document.createElement('div');
        bar.id = 'bulkActionsBar-commissionsTable';
        bar.className = 'bulk-actions-bar';
        bar.style.display = 'none';
        bar.innerHTML = `
            <div class="bulk-actions-bar__content">
                <div class="bulk-actions-bar__info">
                    <span class="bulk-actions-bar__count" id="selectedCount-commissionsTable">0</span>
                    <span class="bulk-actions-bar__text">élément(s) sélectionné(s)</span>
                </div>
                <div class="bulk-actions-bar__actions">
                    <button type="button" class="btn btn-sm btn-success bulk-action-btn" data-action="approve" data-table-id="commissionsTable" data-confirm="false" data-route="{{ route('admin.ambassadors.commissions.bulk-action') }}" data-method="POST">
                        <i class="fas fa-check me-1"></i>Approuver
                    </button>
                    <button type="button" class="btn btn-sm btn-primary bulk-action-btn" data-action="mark-paid" data-table-id="commissionsTable" data-confirm="false" data-route="{{ route('admin.ambassadors.commissions.bulk-action') }}" data-method="POST">
                        <i class="fas fa-money-bill-wave me-1"></i>Marquer comme payées
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown-commissionsTable" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i>Exporter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown-commissionsTable">
                            <li><a class="dropdown-item export-link" href="#" data-format="csv" data-table-id="commissionsTable"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                            <li><a class="dropdown-item export-link" href="#" data-format="excel" data-table-id="commissionsTable"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkActions.clearSelection('commissionsTable')">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                </div>
            </div>
        `;
        commissionsContainer.appendChild(bar);
    }
    bulkActions.init('commissionsTable', {
        exportRoute: '{{ route('admin.ambassadors.commissions.export') }}'
    });
});

async function deleteApplication(applicationId) {
    const message = 'Êtes-vous sûr de vouloir supprimer cette candidature ? Cette action est irréversible.';
    
    const confirmed = await showModernConfirmModal(message, {
        title: 'Supprimer la candidature',
        confirmButtonText: 'Supprimer',
        confirmButtonClass: 'btn-danger',
        icon: 'fa-exclamation-triangle'
    });

    if (confirmed) {
        const form = document.getElementById('deleteApplicationForm');
        if (form) {
            const actionUrl = `{{ url('/admin/ambassadors/applications') }}/${applicationId}`;
            form.action = actionUrl;
            form.submit();
        }
    }
}


// Fonction pour supprimer un ambassadeur
async function deleteAmbassador(ambassadorId) {
    const message = 'Êtes-vous sûr de vouloir supprimer cet ambassadeur ? La candidature associée sera automatiquement mise à jour au statut "rejeté". Cette action est irréversible.';
    
    const confirmed = await showModernConfirmModal(message, {
        title: 'Supprimer l\'ambassadeur',
        confirmButtonText: 'Supprimer',
        confirmButtonClass: 'btn-danger',
        icon: 'fa-exclamation-triangle'
    });

    if (confirmed) {
        const form = document.getElementById('deleteAmbassadorForm');
        if (form) {
            const actionUrl = `{{ url('/admin/ambassadors') }}/${ambassadorId}`;
            form.action = actionUrl;
            form.submit();
        }
    }
}

</script>
@endpush

@push('styles')
<style>
.admin-user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    box-shadow: 0 6px 12px -6px rgba(15, 23, 42, 0.35);
}

/* Statistiques en 2 colonnes sur desktop */
@media (min-width: 992px) {
    .admin-panel--main .admin-stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

/* Gestion du débordement de texte dans les tableaux */
.admin-table table td {
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.admin-table table td .text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.admin-table table td .promo-code-cell {
    word-break: break-all;
    overflow-wrap: break-word;
    max-width: 100%;
}

.nav-tabs {
    border-bottom: 2px solid #e2e8f0;
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}

.nav-tabs::-webkit-scrollbar {
    display: none;  /* Chrome, Safari, Opera */
}

.nav-tabs .nav-item {
    flex-shrink: 0;
}

.nav-tabs .nav-link {
    color: #64748b;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 0.75rem 1.25rem;
    font-weight: 600;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.nav-tabs .nav-link:hover {
    color: #0b1f3a;
    border-bottom-color: #cbd5e1;
}

.nav-tabs .nav-link.active {
    color: #003366;
    background-color: transparent;
    border-bottom-color: #003366;
}

@media (max-width: 991.98px) {
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

    /* Réduire la taille des boutons d'actions sur tablette */
    .admin-table table td.text-center .btn-sm,
    .admin-table table td .btn-sm {
        padding: 0.3rem 0.6rem !important;
        font-size: 0.8rem !important;
        line-height: 1.2 !important;
        min-height: 32px !important;
        height: 32px !important;
        width: 32px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .admin-table table td.text-center .btn-sm i,
    .admin-table table td .btn-sm i {
        font-size: 0.8rem !important;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1 !important;
    }

    /* Masquer le scrollbar vertical des onglets */
    .nav-tabs {
        overflow-y: hidden;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .nav-tabs::-webkit-scrollbar {
        display: none;
    }
}

@media (max-width: 767.98px) {
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

    .nav-tabs .nav-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    /* Réduire encore plus la taille des boutons d'actions sur mobile */
    .admin-table table td.text-center .btn-sm,
    .admin-table table td .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.75rem !important;
        line-height: 1 !important;
        min-width: 28px !important;
        min-height: 28px !important;
        height: 28px !important;
        width: 28px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .admin-table table td.text-center .btn-sm i,
    .admin-table table td .btn-sm i {
        font-size: 0.75rem !important;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1 !important;
    }

    /* Réduire l'espacement entre les boutons */
    .admin-table table td .d-flex.gap-2 {
        gap: 0.25rem !important;
    }

    /* Masquer le scrollbar vertical des onglets */
    .nav-tabs {
        overflow-y: hidden;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .nav-tabs::-webkit-scrollbar {
        display: none;
    }
}
</style>
@endpush
