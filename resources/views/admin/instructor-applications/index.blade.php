@extends('layouts.admin')

@section('title', 'Formateurs')
@section('admin-title', 'Gestion des Formateurs')
@section('admin-subtitle', 'Consultez et gérez les formateurs, leurs candidatures et leurs paiements')

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <!-- Onglets -->
            <ul class="nav nav-tabs mb-4" id="instructorsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($tab ?? 'instructors') === 'instructors' ? 'active' : '' }}" 
                            id="instructors-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#instructors" 
                            type="button" 
                            role="tab"
                            onclick="window.location.href='{{ route('admin.instructor-applications', ['tab' => 'instructors']) }}'">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Formateurs
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($tab ?? 'instructors') === 'payouts' ? 'active' : '' }}" 
                            id="payouts-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#payouts" 
                            type="button" 
                            role="tab"
                            onclick="window.location.href='{{ route('admin.instructor-applications', ['tab' => 'payouts']) }}'">
                        <i class="fas fa-money-bill-wave me-2"></i>Paiements
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($tab ?? 'instructors') === 'applications' ? 'active' : '' }}" 
                            id="applications-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#applications" 
                            type="button" 
                            role="tab"
                            onclick="window.location.href='{{ route('admin.instructor-applications', ['tab' => 'applications']) }}'">
                        <i class="fas fa-file-alt me-2"></i>Candidatures
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="instructorsTabContent">
                <!-- Onglet Formateurs -->
                @if(($tab ?? 'instructors') === 'instructors')
                <div class="tab-pane fade show active" 
                     id="instructors" 
                     role="tabpanel" 
                     aria-labelledby="instructors-tab">
                    <div class="admin-stats-grid mb-4">
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Total</p>
                            <p class="admin-stat-card__value">{{ $stats['total'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Formateurs</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">En attente</p>
                            <p class="admin-stat-card__value">{{ $stats['pending'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">En file de traitement</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">En examen</p>
                            <p class="admin-stat-card__value">{{ $stats['under_review'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Analyse en cours</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Approuvées</p>
                            <p class="admin-stat-card__value">{{ $stats['approved'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Prêtes à être activées</p>
                        </div>
                    </div>

                    <x-admin.search-panel
                        :action="route('admin.instructor-applications', ['tab' => 'instructors'])"
                        formId="instructorsFilterForm"
                        filtersId="instructorsFilters"
                        :hasFilters="true"
                        :searchValue="request('search')"
                        placeholder="Rechercher par nom ou email..."
                    >
                        <x-slot:filters>
                            <input type="hidden" name="tab" value="instructors">
                            <div class="admin-form-grid admin-form-grid--two mb-3">
                                <div>
                                    <label class="form-label fw-semibold">Statut</label>
                                    <select class="form-select" name="status">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>En examen</option>
                                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvée</option>
                                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <span class="text-muted small">Filtrez rapidement les formateurs selon leur statut.</span>
                                <a href="{{ route('admin.instructor-applications', ['tab' => 'instructors']) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo me-2"></i>Réinitialiser
                                </a>
                            </div>
                        </x-slot:filters>
                    </x-admin.search-panel>

                    <div class="admin-table mt-4">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Date de soumission</th>
                                        <th>Statut</th>
                                        <th>Révisé par</th>
                                        <th class="text-center" style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications ?? [] as $application)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="{{ $application->user->avatar_url }}"
                                                         alt="{{ $application->user->name }}"
                                                         class="admin-user-avatar">
                                                    <div>
                                                        <a href="{{ route('admin.users.show', $application->user) }}"
                                                           class="fw-semibold text-decoration-none text-dark">
                                                            {{ $application->user->name }}
                                                        </a>
                                                        <div class="text-muted small">{{ $application->user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="admin-chip admin-chip--neutral">
                                                    {{ $application->created_at->format('d/m/Y H:i') }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match ($application->status) {
                                                        'pending' => 'admin-chip--warning',
                                                        'under_review' => 'admin-chip--info',
                                                        'approved' => 'admin-chip--success',
                                                        'rejected' => 'admin-chip--danger',
                                                        default => 'admin-chip--neutral',
                                                    };
                                                @endphp
                                                <span class="admin-chip {{ $statusClass }}">
                                                    {{ $application->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                @if(isset($application->is_virtual) && $application->is_virtual)
                                                    <span class="admin-chip admin-chip--success">
                                                        <i class="fas fa-user-shield me-1"></i>Nommé par admin
                                                    </span>
                                                @elseif($application->reviewer)
                                                    <span class="admin-chip admin-chip--info">
                                                        <i class="fas fa-user-check me-1"></i>{{ $application->reviewer->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted small">Non attribué</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(isset($application->is_virtual) && $application->is_virtual)
                                                    <a href="{{ route('admin.users.show', $application->user) }}"
                                                       class="btn btn-light btn-sm" title="Voir le formateur">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('admin.instructor-applications.show', $application) }}"
                                                       class="btn btn-light btn-sm" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="admin-table__empty">
                                                <i class="fas fa-inbox mb-2 d-block"></i>
                                                Aucun formateur trouvé
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if(isset($applications))
                        <x-admin.pagination :paginator="$applications" />
                    @endif
                </div>
                @endif

                <!-- Onglet Paiements -->
                @if(($tab ?? 'instructors') === 'payouts')
                <div class="tab-pane fade show active" 
                     id="payouts" 
                     role="tabpanel" 
                     aria-labelledby="payouts-tab">
                    <div class="admin-stats-grid mb-4">
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Total</p>
                            <p class="admin-stat-card__value">{{ $payoutStats['total'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Payouts enregistrés</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">En attente</p>
                            <p class="admin-stat-card__value">{{ $payoutStats['pending'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">En cours de traitement</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Complétés</p>
                            <p class="admin-stat-card__value">{{ $payoutStats['completed'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Paiements réussis</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Échoués</p>
                            <p class="admin-stat-card__value">{{ $payoutStats['failed'] ?? 0 }}</p>
                            <p class="admin-stat-card__muted">Paiements échoués</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Montant total</p>
                            <p class="admin-stat-card__value">{{ number_format($payoutStats['total_amount'] ?? 0, 2) }}</p>
                            <p class="admin-stat-card__muted">USD payés</p>
                        </div>
                        <div class="admin-stat-card">
                            <p class="admin-stat-card__label">Commission</p>
                            <p class="admin-stat-card__value">{{ number_format($payoutStats['total_commission'] ?? 0, 2) }}</p>
                            <p class="admin-stat-card__muted">USD retenus</p>
                        </div>
                    </div>

                    <x-admin.search-panel
                        :action="route('admin.instructor-applications', ['tab' => 'payouts'])"
                        formId="payoutsFilterForm"
                        filtersId="payoutsFilters"
                        :hasFilters="true"
                        :searchValue="request('search')"
                        placeholder="Rechercher par ID payout ou numéro de commande..."
                    >
                        <x-slot:filters>
                            <input type="hidden" name="tab" value="payouts">
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
                                        @foreach($instructors ?? [] as $instructor)
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
                                <a href="{{ route('admin.instructor-applications', ['tab' => 'payouts']) }}" class="btn btn-outline-secondary">
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
                                <span class="badge bg-warning ms-2">Formateur: {{ ($instructors ?? collect())->firstWhere('id', request('instructor_id'))->name ?? 'N/A' }}</span>
                            @endif
                        </div>
                        <a href="{{ route('admin.instructor-applications', ['tab' => 'payouts']) }}" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-times me-1"></i>Effacer les filtres
                        </a>
                    </div>
                    @endif

                    <!-- Table des payouts -->
                    <div class="admin-table mt-4">
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
                                    @forelse($payouts ?? [] as $payout)
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
                                            @if($payout->moneroo_status ?? $payout->pawapay_status)
                                                <br><small class="text-muted">Moneroo: {{ $payout->moneroo_status ?? $payout->pawapay_status }}</small>
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

                    @if(isset($payouts))
                        <x-admin.pagination :paginator="$payouts" />
                    @endif
                </div>
                @endif

                <!-- Onglet Candidatures -->
                @if(($tab ?? 'instructors') === 'applications')
                <div class="tab-pane fade show active" 
                     id="applications" 
                     role="tabpanel" 
                     aria-labelledby="applications-tab">
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
                            <p class="admin-stat-card__muted">Prêtes à être activées</p>
                        </div>
                    </div>

                    <x-admin.search-panel
                        :action="route('admin.instructor-applications', ['tab' => 'applications'])"
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
                                        <option value="">Tous les statuts</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>En examen</option>
                                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvée</option>
                                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <span class="text-muted small">Filtrez rapidement les candidatures selon leur statut.</span>
                                <a href="{{ route('admin.instructor-applications', ['tab' => 'applications']) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo me-2"></i>Réinitialiser
                                </a>
                            </div>
                        </x-slot:filters>
                    </x-admin.search-panel>

                    <div class="admin-table mt-4">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Date de soumission</th>
                                        <th>Statut</th>
                                        <th>Révisé par</th>
                                        <th class="text-center" style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications ?? [] as $application)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="{{ $application->user->avatar_url }}"
                                                         alt="{{ $application->user->name }}"
                                                         class="admin-user-avatar">
                                                    <div>
                                                        <a href="{{ route('admin.users.show', $application->user) }}"
                                                           class="fw-semibold text-decoration-none text-dark">
                                                            {{ $application->user->name }}
                                                        </a>
                                                        <div class="text-muted small">{{ $application->user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="admin-chip admin-chip--neutral">
                                                    {{ $application->created_at->format('d/m/Y H:i') }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match ($application->status) {
                                                        'pending' => 'admin-chip--warning',
                                                        'under_review' => 'admin-chip--info',
                                                        'approved' => 'admin-chip--success',
                                                        'rejected' => 'admin-chip--danger',
                                                        default => 'admin-chip--neutral',
                                                    };
                                                @endphp
                                                <span class="admin-chip {{ $statusClass }}">
                                                    {{ $application->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($application->reviewer)
                                                    <span class="admin-chip admin-chip--info">
                                                        <i class="fas fa-user-check me-1"></i>{{ $application->reviewer->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted small">Non attribué</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.instructor-applications.show', $application) }}"
                                                   class="btn btn-light btn-sm" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="admin-table__empty">
                                                <i class="fas fa-inbox mb-2 d-block"></i>
                                                Aucune candidature trouvée
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if(isset($applications))
                        <x-admin.pagination :paginator="$applications" />
                    @endif
                </div>
                @endif
            </div>
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
.admin-user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    box-shadow: 0 6px 12px -6px rgba(15, 23, 42, 0.35);
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

/* Statistiques en 2 colonnes sur desktop */
@media (min-width: 992px) {
    .admin-panel--main .admin-stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

@media (max-width: 991.98px) {
    /* Réduire les paddings et margins sur tablette */
    .admin-panel {
        margin-bottom: 1rem;
    }
    
    .admin-panel--main .admin-panel__body {
        padding: 1rem !important;
    }
    
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
    /* Réduire encore plus les paddings et margins sur mobile */
    .admin-panel {
        margin-bottom: 0.75rem;
    }
    
    .admin-panel--main .admin-panel__body {
        padding: 0.75rem !important;
    }
    
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

@push('scripts')
<script>
function showFailureReason(reason) {
    document.getElementById('failureReasonText').textContent = reason;
    const modal = new bootstrap.Modal(document.getElementById('failureReasonModal'));
    modal.show();
}

// Gestion des formulaires de recherche
document.addEventListener('DOMContentLoaded', function() {
    // Formulaires de filtres
    const forms = ['instructorsFilterForm', 'payoutsFilterForm', 'applicationsFilterForm'];
    
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        const filtersOffcanvas = document.getElementById(formId.replace('Form', 'Filters'));
        
        if (form) {
            form.addEventListener('submit', () => {
                if (filtersOffcanvas) {
                    const instance = bootstrap.Offcanvas.getInstance(filtersOffcanvas);
                    if (instance) {
                        instance.hide();
                    }
                }
            });
        }
    });

    // Recherche en temps réel
    const searchInputs = document.querySelectorAll('input[name="search"]');
    searchInputs.forEach(input => {
        let searchTimeout;
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const form = input.closest('form');
                if (form) {
                    form.submit();
                }
            }, 500);
        });
    });
});
</script>
@endpush
