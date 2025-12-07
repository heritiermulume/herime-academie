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

            <div class="admin-table mt-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
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
                                    <td colspan="5" class="text-center py-4">Aucun ambassadeur</td>
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

                    <div class="admin-table mt-4">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $application)
                                        <tr>
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
                                            <td colspan="4" class="text-center py-4">Aucune candidature</td>
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

                    <div class="admin-table mt-4">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
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
                                            <td colspan="6" class="text-center py-4">Aucune commission</td>
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

    <!-- Modal de confirmation de suppression de candidature -->
    <div class="modal fade" id="deleteApplicationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteApplicationForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmer la suppression</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer cette candidature ? Cette action est irréversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression d'ambassadeur -->
    <div class="modal fade" id="deleteAmbassadorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteAmbassadorForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmer la suppression</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer cet ambassadeur ?</p>
                        <p class="text-muted small mb-0">La candidature associée sera automatiquement mise à jour au statut "rejeté". Cette action est irréversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function deleteApplication(applicationId) {
    console.log('deleteApplication appelé avec ID:', applicationId);
    const form = document.getElementById('deleteApplicationForm');
    if (form) {
        const actionUrl = `{{ url('/admin/ambassadors/applications') }}/${applicationId}`;
        form.action = actionUrl;
        console.log('Formulaire action mise à jour:', actionUrl);
        
        const modalElement = document.getElementById('deleteApplicationModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            console.log('Modal ouvert');
        } else {
            console.error('Modal non trouvé');
            alert('Erreur: Modal de confirmation non trouvé');
        }
    } else {
        console.error('Formulaire non trouvé');
        alert('Erreur: Formulaire de suppression non trouvé');
    }
}

// Ajouter un listener sur le formulaire pour logger la soumission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('deleteApplicationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('=== SOUMISSION DU FORMULAIRE ===');
            console.log('Action:', form.action);
            console.log('Méthode:', form.method);
            console.log('Token CSRF:', form.querySelector('input[name="_token"]')?.value);
            console.log('Méthode DELETE:', form.querySelector('input[name="_method"]')?.value);
            
            // Vérifier que tous les champs sont présents
            const token = form.querySelector('input[name="_token"]');
            const method = form.querySelector('input[name="_method"]');
            
            if (!token || !token.value) {
                console.error('Token CSRF manquant!');
                e.preventDefault();
                alert('Erreur: Token de sécurité manquant. Veuillez recharger la page.');
                return false;
            }
            
            if (!method || method.value !== 'DELETE') {
                console.error('Méthode DELETE incorrecte!');
                e.preventDefault();
                alert('Erreur: Méthode de requête incorrecte.');
                return false;
            }
            
            console.log('Formulaire valide, soumission en cours...');
        });
    } else {
        console.warn('Formulaire deleteApplicationForm non trouvé au chargement de la page');
    }
});

// Fonction pour supprimer un ambassadeur
function deleteAmbassador(ambassadorId) {
    console.log('deleteAmbassador appelé avec ID:', ambassadorId);
    const form = document.getElementById('deleteAmbassadorForm');
    if (form) {
        const actionUrl = `{{ url('/admin/ambassadors') }}/${ambassadorId}`;
        form.action = actionUrl;
        console.log('Formulaire action mise à jour:', actionUrl);
        
        const modalElement = document.getElementById('deleteAmbassadorModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            console.log('Modal ouvert');
        } else {
            console.error('Modal element not found');
            alert('Erreur: Modal de confirmation non trouvé');
        }
    } else {
        console.error('Form element not found');
        alert('Erreur: Formulaire de suppression non trouvé');
    }
}

// Ajouter un listener sur le formulaire de suppression d'ambassadeur
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('deleteAmbassadorForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('=== SOUMISSION DU FORMULAIRE DE SUPPRESSION D\'AMBASSADEUR ===');
            console.log('Action:', form.action);
            console.log('Méthode:', form.method);
            
            // Vérifier que tous les champs sont présents
            const token = form.querySelector('input[name="_token"]');
            const method = form.querySelector('input[name="_method"]');
            
            if (!token || !token.value) {
                console.error('Token CSRF manquant!');
                e.preventDefault();
                alert('Erreur: Token de sécurité manquant. Veuillez recharger la page.');
                return false;
            }
            
            if (!method || method.value !== 'DELETE') {
                console.error('Méthode DELETE incorrecte!');
                e.preventDefault();
                alert('Erreur: Méthode de requête incorrecte.');
                return false;
            }
            
            console.log('Formulaire valide, soumission en cours...');
        });
    } else {
        console.warn('Formulaire deleteAmbassadorForm non trouvé au chargement de la page');
    }
});
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
