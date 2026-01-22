@extends('layouts.admin')

@section('title', 'Gestion des annonces')
@section('admin-title', 'Annonces globales')
@section('admin-subtitle', 'Diffusez des messages clés auprès de vos apprenants et prestataires')
@section('admin-actions')
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAnnouncementModal">
            <i class="fas fa-plus-circle me-2"></i>Nouvelle annonce
        </button>
        <a href="{{ route('admin.announcements.send-combined') }}" class="btn btn-primary" title="Envoyer par Email et WhatsApp simultanément">
            <i class="fas fa-paper-plane me-2"></i>Messages Combinés
        </a>
    </div>
@endsection

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
                    <x-admin.search-panel
                        :action="route('admin.announcements')"
                        formId="announcementsFilterForm"
                        filtersId="announcementsFilters"
                        :hasFilters="true"
                        :searchValue="request('announcement_search')"
                        placeholder="Rechercher par titre ou contenu..."
                    >
                        <x-slot:filters>
                            <div class="admin-form-grid admin-form-grid--two mb-3">
                                <div>
                                    <label class="form-label fw-semibold">Type</label>
                                    <select class="form-select" name="announcement_type">
                                        <option value="">Tous les types</option>
                                        <option value="info" {{ request('announcement_type') === 'info' ? 'selected' : '' }}>Information</option>
                                        <option value="success" {{ request('announcement_type') === 'success' ? 'selected' : '' }}>Succès</option>
                                        <option value="warning" {{ request('announcement_type') === 'warning' ? 'selected' : '' }}>Attention</option>
                                        <option value="error" {{ request('announcement_type') === 'error' ? 'selected' : '' }}>Erreur</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold">Statut</label>
                                    <select class="form-select" name="announcement_status">
                                        <option value="">Tous les statuts</option>
                                        <option value="active" {{ request('announcement_status') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('announcement_status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </x-slot:filters>
                    </x-admin.search-panel>

                    <div id="bulkActionsContainer-announcementsTable"></div>

                    <div class="admin-table mt-4">
                        <div class="table-responsive">
                            <table class="table table-hover" id="announcementsTable" data-bulk-select="true">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">
                                            <input type="checkbox" data-select-all data-table-id="announcementsTable" title="Sélectionner tout">
                                        </th>
                                        <th style="min-width: 250px; max-width: 400px;">Titre</th>
                                        <th style="width: 100px; white-space: nowrap;">Type</th>
                                        <th style="width: 100px; white-space: nowrap;">Statut</th>
                                        <th style="min-width: 140px; max-width: 160px; white-space: nowrap;">Début</th>
                                        <th style="min-width: 140px; max-width: 160px; white-space: nowrap;">Fin</th>
                                        <th class="text-center d-none d-md-table-cell" style="width: 120px; white-space: nowrap;">Actions</th>
                                        <th class="text-center d-md-none" style="width: 120px; white-space: nowrap;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($announcements as $announcement)
                                    <tr>
                                        <td>
                                            <input type="checkbox" data-item-id="{{ $announcement->id }}" class="form-check-input">
                                        </td>
                                        <td style="max-width: 400px;">
                                            <h6 class="mb-0 text-truncate d-block" style="max-width: 100%;" title="{{ $announcement->title }}">{{ $announcement->title }}</h6>
                                            <small class="text-muted text-truncate d-block" style="max-width: 100%;" title="{{ $announcement->content }}">{{ $announcement->content }}</small>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <span class="badge bg-{{ $announcement->type === 'info' ? 'info' : ($announcement->type === 'success' ? 'success' : ($announcement->type === 'warning' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($announcement->type) }}
                                            </span>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <span class="badge bg-{{ $announcement->is_active ? 'success' : 'secondary' }}">
                                                {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td style="max-width: 160px; white-space: nowrap;">
                                            <small>
                                                {{ $announcement->starts_at ? $announcement->starts_at->format('d/m/Y H:i') : 'Immédiat' }}
                                            </small>
                                        </td>
                                        <td style="max-width: 160px; white-space: nowrap;">
                                            <small>
                                                {{ $announcement->expires_at ? $announcement->expires_at->format('d/m/Y H:i') : 'Illimité' }}
                                            </small>
                                        </td>
                                        <td class="text-center d-none d-md-table-cell">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('admin.announcements.preview', $announcement) }}" class="btn btn-info btn-sm" title="Aperçu de l'annonce">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-primary btn-sm" onclick="editAnnouncement({{ $announcement->id }})" title="Modifier l'annonce">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        data-action="{{ route('admin.announcements.destroy', $announcement) }}"
                                                        data-title="{{ $announcement->title }}"
                                                        onclick="openDeleteAnnouncementModal(this)" 
                                                        title="Supprimer l'annonce">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center d-md-none">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('admin.announcements.preview', $announcement) }}" class="btn btn-info btn-sm" title="Aperçu de l'annonce">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-primary btn-sm" onclick="editAnnouncement({{ $announcement->id }})" title="Modifier l'annonce">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        data-action="{{ route('admin.announcements.destroy', $announcement) }}"
                                                        data-title="{{ $announcement->title }}"
                                                        onclick="openDeleteAnnouncementModal(this)" 
                                                        title="Supprimer l'annonce">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Aucune annonce trouvée</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <!-- Pagination -->
                    <x-admin.pagination :paginator="$announcements" />
        </div>
    </section>

    <!-- Section de gestion des emails -->
    <section class="admin-panel mt-4">
        <div class="admin-panel__header">
            <h3 class="mb-3"><i class="fas fa-envelope me-2"></i>Gestion des emails</h3>
            <!-- Bouton pour envoyer un email -->
            <a href="{{ route('admin.announcements.send-email') }}" class="btn btn-primary w-100" title="Envoyer un email">
                <i class="fas fa-plus me-2"></i>Envoyer un email
            </a>
        </div>
        <div class="admin-panel__body">
            <!-- Statistiques des emails -->
            <div class="row g-3 mb-3 email-stats-row">
                <div class="col-6 col-md-3">
                    <div class="admin-stat-card">
                        <div class="admin-stat-card__icon" style="background-color: #e3f2fd;">
                            <i class="fas fa-paper-plane text-primary"></i>
                        </div>
                        <div class="admin-stat-card__content">
                            <div class="admin-stat-card__value">{{ number_format($emailStats['total_sent'] ?? 0) }}</div>
                            <div class="admin-stat-card__label">Emails envoyés</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="admin-stat-card">
                        <div class="admin-stat-card__icon" style="background-color: #e8f5e9;">
                            <i class="fas fa-calendar-day text-success"></i>
                        </div>
                        <div class="admin-stat-card__content">
                            <div class="admin-stat-card__value">{{ number_format($emailStats['sent_today'] ?? 0) }}</div>
                            <div class="admin-stat-card__label">Aujourd'hui</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="admin-stat-card">
                        <div class="admin-stat-card__icon" style="background-color: #fff3e0;">
                            <i class="fas fa-clock text-warning"></i>
                        </div>
                        <div class="admin-stat-card__content">
                            <div class="admin-stat-card__value">{{ number_format($emailStats['pending_scheduled'] ?? 0) }}</div>
                            <div class="admin-stat-card__label">En attente</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="admin-stat-card">
                        <div class="admin-stat-card__icon" style="background-color: #ffebee;">
                            <i class="fas fa-exclamation-triangle text-danger"></i>
                        </div>
                        <div class="admin-stat-card__content">
                            <div class="admin-stat-card__value">{{ number_format($emailStats['failed_today'] ?? 0) }}</div>
                            <div class="admin-stat-card__label">Échecs aujourd'hui</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onglets pour les emails -->
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2 mb-3">
                <ul class="nav nav-tabs flex-grow-1" id="emailsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="sent-emails-tab" data-bs-toggle="tab" data-bs-target="#sent-emails" type="button" role="tab">
                            <i class="fas fa-paper-plane me-2"></i>Emails envoyés
                            <span class="badge bg-primary ms-2">{{ $recentSentEmails->total() ?? 0 }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="scheduled-emails-tab" data-bs-toggle="tab" data-bs-target="#scheduled-emails" type="button" role="tab">
                            <i class="fas fa-clock me-2"></i>Emails programmés
                            <span class="badge bg-warning ms-2">{{ $pendingScheduledEmails->total() ?? 0 }}</span>
                        </button>
                    </li>
                </ul>
                <!-- Bouton "Voir tout" visible sur tous les écrans -->
                <a href="{{ route('admin.emails.sent') }}" class="btn btn-sm btn-outline-primary align-self-start align-self-md-center">
                    <i class="fas fa-list me-2"></i>Voir tout
                </a>
            </div>

            <div class="tab-content" id="emailsTabContent">
                <!-- Onglet emails envoyés -->
                <div class="tab-pane fade show active" id="sent-emails" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px; min-width: 50px; max-width: 50px;"></th>
                                    <th style="min-width: 180px; max-width: 250px;">Destinataire</th>
                                    <th style="min-width: 200px; max-width: 300px;">Sujet</th>
                                    <th style="width: 100px; white-space: nowrap;">Type</th>
                                    <th style="width: 120px; white-space: nowrap;">Statut</th>
                                    <th style="min-width: 140px; max-width: 160px; white-space: nowrap;">Date</th>
                                    <th class="text-center d-none d-md-table-cell" style="width: 120px; white-space: nowrap;">Actions</th>
                                    <th class="text-center d-md-none" style="width: 120px; white-space: nowrap;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSentEmails ?? [] as $email)
                                <tr>
                                    <td class="align-middle" style="width: 50px !important; min-width: 50px !important; max-width: 50px !important; padding: 0.5rem; vertical-align: middle;">
                                        @php
                                            $recipientUser = $email->recipient_user ?? null;
                                            $avatarUrl = $recipientUser ? $recipientUser->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($email->recipient_name ?? 'N/A') . '&background=003366&color=fff&size=128';
                                        @endphp
                                        <div class="email-avatar-container" style="width: 40px !important; height: 40px !important; min-width: 40px !important; min-height: 40px !important; max-width: 40px !important; max-height: 40px !important; border-radius: 50% !important; overflow: hidden !important; display: inline-block !important;">
                                            <img src="{{ $avatarUrl }}" 
                                                 alt="{{ $email->recipient_name ?? 'N/A' }}" 
                                                 class="email-avatar"
                                                 style="border-radius: 50% !important; width: 100% !important; height: 100% !important; object-fit: cover !important; display: block !important;"
                                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($email->recipient_name ?? 'N/A') }}&background=003366&color=fff&size=128'">
                                        </div>
                                    </td>
                                    <td style="max-width: 250px;">
                                        <div style="min-width: 0; overflow: hidden;">
                                            <small class="text-truncate d-block" style="max-width: 100%;" title="{{ $email->recipient_name ?? 'N/A' }}">{{ $email->recipient_name ?? 'N/A' }}</small>
                                            <small class="text-muted text-truncate d-block" style="max-width: 100%;" title="{{ $email->recipient_email }}">{{ $email->recipient_email }}</small>
                                        </div>
                                    </td>
                                    <td style="max-width: 300px;">
                                        <small class="text-truncate d-block" style="max-width: 100%;" title="{{ $email->subject }}">{{ $email->subject }}</small>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <span class="badge bg-info">{{ ucfirst($email->type) }}</span>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        @if($email->status === 'sent')
                                            <span class="badge bg-success">Envoyé</span>
                                        @elseif($email->status === 'failed')
                                            <span class="badge bg-danger">Échoué</span>
                                        @else
                                            <span class="badge bg-secondary">En attente</span>
                                        @endif
                                    </td>
                                    <td style="max-width: 160px; white-space: nowrap;">
                                        <small>{{ $email->sent_at ? $email->sent_at->format('d/m/Y H:i') : ($email->created_at->format('d/m/Y H:i')) }}</small>
                                    </td>
                                    <td class="text-center d-none d-md-table-cell">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('admin.emails.sent.show', $email) }}" class="btn btn-light btn-sm" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    data-action="{{ route('admin.emails.sent.destroy', $email) }}"
                                                    data-subject="{{ $email->subject }}"
                                                    onclick="openDeleteEmailModal(this)" 
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-center d-md-none">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('admin.emails.sent.show', $email) }}" class="btn btn-light btn-sm" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    data-action="{{ route('admin.emails.sent.destroy', $email) }}"
                                                    data-subject="{{ $email->subject }}"
                                                    onclick="openDeleteEmailModal(this)" 
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-3 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">Aucun email envoyé récemment</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination des emails envoyés -->
                    <x-admin.pagination :paginator="$recentSentEmails" :pageName="'emails_page'" />
                </div>

                <!-- Onglet emails programmés -->
                <div class="tab-pane fade" id="scheduled-emails" role="tabpanel">
                    <div id="bulkActionsContainer-scheduledEmailsTable"></div>

                    <div class="table-responsive mt-4">
                        <table class="table table-hover table-sm" id="scheduledEmailsTable" data-bulk-select="true">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">
                                        <input type="checkbox" data-select-all data-table-id="scheduledEmailsTable" title="Sélectionner tout">
                                    </th>
                                    <th style="width: 50px; min-width: 50px; max-width: 50px;"></th>
                                    <th style="min-width: 200px; max-width: 300px;">Sujet</th>
                                    <th style="min-width: 150px; max-width: 250px;">Destinataires</th>
                                    <th style="min-width: 160px; max-width: 180px; white-space: nowrap;">Programmé pour</th>
                                    <th style="width: 120px; white-space: nowrap;">Statut</th>
                                    <th class="text-center d-none d-md-table-cell" style="width: 150px; white-space: nowrap;">Actions</th>
                                    <th class="text-center d-md-none" style="width: 150px; white-space: nowrap;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingScheduledEmails ?? [] as $scheduled)
                                <tr>
                                    <td>
                                        <input type="checkbox" data-item-id="{{ $scheduled->id }}" class="form-check-input">
                                    </td>
                                    <td class="align-middle" style="width: 50px; min-width: 50px; max-width: 50px; padding: 0.5rem; vertical-align: middle;">
                                        @php
                                            $creator = $scheduled->created_by_user ?? null;
                                            $creatorName = $creator?->name ?? ($scheduled->created_by_name ?? 'N/A');
                                            $avatarUrl = $creator && $creator->avatar_url
                                                ? $creator->avatar_url
                                                : 'https://ui-avatars.com/api/?name=' . urlencode($creatorName) . '&background=003366&color=fff&size=128';
                                        @endphp
                                        <div class="email-avatar-container" style="width: 40px !important; height: 40px !important; min-width: 40px !important; min-height: 40px !important; max-width: 40px !important; max-height: 40px !important; border-radius: 50% !important; overflow: hidden !important; display: inline-block !important;">
                                            <img src="{{ $avatarUrl }}"
                                                 alt="{{ $creatorName }}"
                                                 class="email-avatar"
                                                 style="border-radius: 50% !important; width: 100% !important; height: 100% !important; object-fit: cover !important; display: block !important;"
                                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($creatorName) }}&background=003366&color=fff&size=128'">
                                        </div>
                                    </td>
                                    <td style="max-width: 300px;">
                                        <small class="text-truncate d-block fw-bold" style="max-width: 100%;" title="{{ $scheduled->subject }}">{{ $scheduled->subject }}</small>
                                    </td>
                                    <td style="max-width: 250px;">
                                        <div style="min-width: 0; overflow: hidden;">
                                            <small class="text-truncate d-block" style="max-width: 100%;" title="{{ $scheduled->total_recipients }} destinataire(s)">
                                                {{ $scheduled->total_recipients }} destinataire(s)
                                            </small>
                                            @if($scheduled->recipient_type === 'role' && isset($scheduled->recipient_config['roles']))
                                                <span class="badge bg-secondary text-truncate d-inline-block" style="max-width: 100%;" title="{{ implode(', ', $scheduled->recipient_config['roles']) }}">{{ implode(', ', $scheduled->recipient_config['roles']) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="max-width: 180px; white-space: nowrap;">
                                        <small>{{ $scheduled->scheduled_at->format('d/m/Y à H:i') }}</small>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        @if($scheduled->status === 'pending')
                                            <span class="badge bg-warning">En attente</span>
                                        @elseif($scheduled->status === 'processing')
                                            <span class="badge bg-info">En cours</span>
                                        @elseif($scheduled->status === 'completed')
                                            <span class="badge bg-success">Terminé</span>
                                        @elseif($scheduled->status === 'failed')
                                            <span class="badge bg-danger">Échoué</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($scheduled->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center d-none d-md-table-cell">
                                        <div class="d-flex gap-2 justify-content-center">
                                            @if($scheduled->status === 'pending')
                                            <a href="{{ route('admin.announcements.send-email', ['edit' => $scheduled->id]) }}" class="btn btn-primary btn-sm" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                            <a href="{{ route('admin.emails.scheduled.show', $scheduled) }}" class="btn btn-light btn-sm" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($scheduled->status === 'pending')
                                            <button type="button" class="btn btn-warning btn-sm" 
                                                    data-action="{{ route('admin.emails.scheduled.cancel', $scheduled) }}"
                                                    data-subject="{{ $scheduled->subject }}"
                                                    onclick="openCancelScheduledEmailModal(this)" 
                                                    title="Annuler">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    data-action="{{ route('admin.emails.scheduled.destroy', $scheduled) }}"
                                                    data-subject="{{ $scheduled->subject }}"
                                                    onclick="openDeleteEmailModal(this)" 
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center d-md-none">
                                        <div class="d-flex gap-2 justify-content-center">
                                            @if($scheduled->status === 'pending')
                                            <a href="{{ route('admin.announcements.send-email', ['edit' => $scheduled->id]) }}" class="btn btn-primary btn-sm" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                            <a href="{{ route('admin.emails.scheduled.show', $scheduled) }}" class="btn btn-light btn-sm" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($scheduled->status === 'pending')
                                            <button type="button" class="btn btn-warning btn-sm" 
                                                    data-action="{{ route('admin.emails.scheduled.cancel', $scheduled) }}"
                                                    data-subject="{{ $scheduled->subject }}"
                                                    onclick="openCancelScheduledEmailModal(this)" 
                                                    title="Annuler">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    data-action="{{ route('admin.emails.scheduled.destroy', $scheduled) }}"
                                                    data-subject="{{ $scheduled->subject }}"
                                                    onclick="openDeleteEmailModal(this)" 
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-3 text-muted">
                                        <i class="fas fa-clock fa-2x mb-2"></i>
                                        <p class="mb-0">Aucun email programmé</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination des emails programmés -->
                    <x-admin.pagination :paginator="$pendingScheduledEmails" :pageName="'scheduled_page'" />
                </div>
            </div>
        </div>
    </section>

    <!-- Section de gestion des messages WhatsApp -->
    <section class="admin-panel mt-4">
        <div class="admin-panel__header d-flex justify-content-between align-items-center">
            <h3 class="mb-0 flex-grow-1"><i class="fab fa-whatsapp me-2"></i>Gestion des messages WhatsApp</h3>
            <!-- Bouton pour envoyer un message WhatsApp -->
            <a href="{{ route('admin.announcements.send-whatsapp') }}" class="btn btn-primary" title="Envoyer un message WhatsApp">
                <i class="fas fa-plus me-2"></i>Envoyer un message
            </a>
        </div>
        <div class="admin-panel__body">
            <!-- Statistiques des messages WhatsApp -->
            <div class="row g-3 mb-3 whatsapp-stats-row">
                <div class="col-6 col-md-3">
                    <div class="admin-stat-card">
                        <div class="admin-stat-card__icon" style="background-color: #dcf8c6;">
                            <i class="fab fa-whatsapp text-success"></i>
                        </div>
                        <div class="admin-stat-card__content">
                            <div class="admin-stat-card__value">{{ number_format($whatsappStats['total_sent'] ?? 0) }}</div>
                            <div class="admin-stat-card__label">Messages envoyés</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="admin-stat-card">
                        <div class="admin-stat-card__icon" style="background-color: #e8f5e9;">
                            <i class="fas fa-calendar-day text-success"></i>
                        </div>
                        <div class="admin-stat-card__content">
                            <div class="admin-stat-card__value">{{ number_format($whatsappStats['sent_today'] ?? 0) }}</div>
                            <div class="admin-stat-card__label">Aujourd'hui</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="admin-stat-card">
                        <div class="admin-stat-card__icon" style="background-color: #ffebee;">
                            <i class="fas fa-exclamation-triangle text-danger"></i>
                        </div>
                        <div class="admin-stat-card__content">
                            <div class="admin-stat-card__value">{{ number_format($whatsappStats['failed_today'] ?? 0) }}</div>
                            <div class="admin-stat-card__label">Échecs aujourd'hui</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="admin-stat-card">
                        <div class="admin-stat-card__icon" style="background-color: #e3f2fd;">
                            <i class="fas fa-info-circle text-info"></i>
                        </div>
                        <div class="admin-stat-card__content">
                            <div class="admin-stat-card__value">{{ number_format(count($recentSentWhatsApp ?? [])) }}</div>
                            <div class="admin-stat-card__label">Messages récents</div>
                        </div>
                    </div>
                </div>
            </div>

            <x-admin.search-panel
                :action="route('admin.announcements')"
                formId="whatsappFilterForm"
                filtersId="whatsappFilters"
                :hasFilters="true"
                :searchValue="request('whatsapp_search')"
                placeholder="Rechercher par message, téléphone ou nom..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Statut</label>
                            <select class="form-select" name="whatsapp_status">
                                <option value="">Tous les statuts</option>
                                <option value="sent" {{ request('whatsapp_status') === 'sent' ? 'selected' : '' }}>Envoyé</option>
                                <option value="delivered" {{ request('whatsapp_status') === 'delivered' ? 'selected' : '' }}>Livré</option>
                                <option value="read" {{ request('whatsapp_status') === 'read' ? 'selected' : '' }}>Lu</option>
                                <option value="failed" {{ request('whatsapp_status') === 'failed' ? 'selected' : '' }}>Échoué</option>
                                <option value="pending" {{ request('whatsapp_status') === 'pending' ? 'selected' : '' }}>En attente</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Type</label>
                            <select class="form-select" name="whatsapp_type">
                                <option value="">Tous les types</option>
                                <option value="announcement" {{ request('whatsapp_type') === 'announcement' ? 'selected' : '' }}>Annonce</option>
                                <option value="notification" {{ request('whatsapp_type') === 'notification' ? 'selected' : '' }}>Notification</option>
                            </select>
                        </div>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            <div id="bulkActionsContainer-whatsappTable"></div>

            <!-- Tableau des messages WhatsApp récents -->
            <div class="table-responsive whatsapp-messages-table mt-4">
                <table class="table table-hover table-sm" id="whatsappTable" data-bulk-select="true">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" data-select-all data-table-id="whatsappTable" title="Sélectionner tout">
                            </th>
                            <th style="width: 50px; min-width: 50px; max-width: 50px;"></th>
                            <th style="min-width: 180px; max-width: 250px;">Destinataire</th>
                            <th style="min-width: 200px; max-width: 350px;">Message</th>
                            <th style="width: 100px; white-space: nowrap;">Type</th>
                            <th style="width: 120px; white-space: nowrap;">Statut</th>
                            <th style="min-width: 140px; max-width: 160px; white-space: nowrap;">Date</th>
                            <th class="text-center d-none d-md-table-cell" style="width: 120px; white-space: nowrap;">Actions</th>
                            <th class="text-center d-md-none" style="width: 120px; white-space: nowrap;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSentWhatsApp ?? [] as $whatsapp)
                        <tr>
                            <td>
                                <input type="checkbox" data-item-id="{{ $whatsapp->id }}" class="form-check-input">
                            </td>
                            <td class="align-middle" style="width: 50px; min-width: 50px; max-width: 50px; padding: 0.5rem; vertical-align: middle;">
                                @php
                                    $recipientUser = $whatsapp->recipient_user ?? null;
                                    $avatarUrl = $recipientUser ? $recipientUser->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($whatsapp->recipient_name ?? 'N/A') . '&background=25d366&color=fff&size=128';
                                @endphp
                                <div class="whatsapp-avatar-container" style="width: 40px !important; height: 40px !important; min-width: 40px !important; min-height: 40px !important; max-width: 40px !important; max-height: 40px !important; border-radius: 50% !important; overflow: hidden !important; display: inline-block !important;">
                                    <img src="{{ $avatarUrl }}" 
                                         alt="{{ $whatsapp->recipient_name ?? 'N/A' }}" 
                                         class="whatsapp-avatar"
                                         style="border-radius: 50% !important; width: 100% !important; height: 100% !important; object-fit: cover !important; display: block !important;"
                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($whatsapp->recipient_name ?? 'N/A') }}&background=25d366&color=fff&size=128'">
                                </div>
                            </td>
                            <td style="max-width: 250px;">
                                <div style="min-width: 0; overflow: hidden;">
                                    <small class="text-truncate d-block" style="max-width: 100%;" title="{{ $whatsapp->recipient_name ?? 'N/A' }}">{{ $whatsapp->recipient_name ?? 'N/A' }}</small>
                                    <small class="text-muted text-truncate d-block" style="max-width: 100%;" title="{{ $whatsapp->recipient_phone }}">{{ $whatsapp->recipient_phone }}</small>
                                </div>
                            </td>
                            <td style="max-width: 350px;">
                                <small class="text-truncate d-block" style="max-width: 100%;" title="{{ $whatsapp->message }}">{{ $whatsapp->message }}</small>
                            </td>
                            <td style="white-space: nowrap;">
                                <span class="badge bg-info">{{ ucfirst($whatsapp->type) }}</span>
                            </td>
                            <td style="white-space: nowrap;">
                                @if($whatsapp->status === 'sent')
                                    <span class="badge bg-success">Envoyé</span>
                                @elseif($whatsapp->status === 'delivered')
                                    <span class="badge bg-primary">Livré</span>
                                @elseif($whatsapp->status === 'read')
                                    <span class="badge bg-info">Lu</span>
                                @elseif($whatsapp->status === 'failed')
                                    <span class="badge bg-danger">Échoué</span>
                                @else
                                    <span class="badge bg-secondary">En attente</span>
                                @endif
                            </td>
                            <td style="max-width: 160px; white-space: nowrap;">
                                <small>{{ $whatsapp->sent_at ? $whatsapp->sent_at->format('d/m/Y H:i') : ($whatsapp->created_at->format('d/m/Y H:i')) }}</small>
                            </td>
                            <td class="text-center d-none d-md-table-cell">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-light btn-sm" onclick="viewWhatsAppMessage({{ $whatsapp->id }})" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            data-action="{{ route('admin.whatsapp-messages.destroy', $whatsapp) }}"
                                            data-message="{{ Str::limit($whatsapp->message, 50) }}"
                                            onclick="openDeleteWhatsAppModal(this)" 
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="text-center d-md-none">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-light btn-sm" onclick="viewWhatsAppMessage({{ $whatsapp->id }})" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            data-action="{{ route('admin.whatsapp-messages.destroy', $whatsapp) }}"
                                            data-message="{{ Str::limit($whatsapp->message, 50) }}"
                                            onclick="openDeleteWhatsAppModal(this)" 
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-3 text-muted">
                                <i class="fab fa-whatsapp fa-2x mb-2"></i>
                                <p class="mb-0">Aucun message WhatsApp envoyé récemment</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination des messages WhatsApp -->
            <x-admin.pagination :paginator="$recentSentWhatsApp" :pageName="'whatsapp_page'" />
        </div>
    </section>

<!-- Modal de création d'annonce -->
<div class="modal fade" id="createAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle annonce</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.announcements.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Contenu *</label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="info">Information</option>
                                    <option value="success">Succès</option>
                                    <option value="warning">Attention</option>
                                    <option value="error">Erreur</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="button_text" class="form-label">Texte du bouton</label>
                                <input type="text" class="form-control" id="button_text" name="button_text">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="button_url" class="form-label">URL du bouton</label>
                        <input type="url" class="form-control" id="button_url" name="button_url">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="starts_at" class="form-label">Date de début</label>
                                <input type="datetime-local" class="form-control" id="starts_at" name="starts_at">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expires_at" class="form-label">Date de fin</label>
                                <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">
                            Annonce active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer l'annonce</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'édition -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier l'annonce</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAnnouncementForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Titre *</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_content" class="form-label">Contenu *</label>
                        <textarea class="form-control" id="edit_content" name="content" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_type" class="form-label">Type</label>
                                <select class="form-select" id="edit_type" name="type">
                                    <option value="info">Information</option>
                                    <option value="success">Succès</option>
                                    <option value="warning">Attention</option>
                                    <option value="error">Erreur</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_button_text" class="form-label">Texte du bouton</label>
                                <input type="text" class="form-control" id="edit_button_text" name="button_text">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_button_url" class="form-label">URL du bouton</label>
                        <input type="url" class="form-control" id="edit_button_url" name="button_url">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_starts_at" class="form-label">Date de début</label>
                                <input type="datetime-local" class="form-control" id="edit_starts_at" name="starts_at">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_expires_at" class="form-label">Date de fin</label>
                                <input type="datetime-local" class="form-control" id="edit_expires_at" name="expires_at">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">
                            Annonce active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Modifier l'annonce</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/bulk-actions.js') }}"></script>
<script src="{{ asset('js/modern-confirm-modal.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les bulk actions pour la liste principale des annonces
    const announcementsContainer = document.getElementById('bulkActionsContainer-announcementsTable');
    if (announcementsContainer) {
        const bar = document.createElement('div');
        bar.id = 'bulkActionsBar-announcementsTable';
        bar.className = 'bulk-actions-bar';
        bar.style.display = 'none';
        bar.innerHTML = `
            <div class="bulk-actions-bar__content">
                <div class="bulk-actions-bar__info">
                    <span class="bulk-actions-bar__count" id="selectedCount-announcementsTable">0</span>
                    <span class="bulk-actions-bar__text">élément(s) sélectionné(s)</span>
                </div>
                <div class="bulk-actions-bar__actions">
                    <button type="button" class="btn btn-sm btn-danger bulk-action-btn" data-action="delete" data-table-id="announcementsTable" data-confirm="true" data-confirm-message="Êtes-vous sûr de vouloir supprimer les annonces sélectionnées ?" data-route="{{ route('admin.announcements.bulk-action') }}" data-method="POST">
                        <i class="fas fa-trash me-1"></i>Supprimer
                    </button>
                    <button type="button" class="btn btn-sm btn-success bulk-action-btn" data-action="activate" data-table-id="announcementsTable" data-confirm="false" data-route="{{ route('admin.announcements.bulk-action') }}" data-method="POST">
                        <i class="fas fa-check me-1"></i>Activer
                    </button>
                    <button type="button" class="btn btn-sm btn-warning bulk-action-btn" data-action="deactivate" data-table-id="announcementsTable" data-confirm="false" data-route="{{ route('admin.announcements.bulk-action') }}" data-method="POST">
                        <i class="fas fa-ban me-1"></i>Désactiver
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown-announcementsTable" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i>Exporter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown-announcementsTable">
                            <li><a class="dropdown-item export-link" href="#" data-format="csv" data-table-id="announcementsTable"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                            <li><a class="dropdown-item export-link" href="#" data-format="excel" data-table-id="announcementsTable"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkActions.clearSelection('announcementsTable')">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                </div>
            </div>
        `;
        announcementsContainer.appendChild(bar);
    }
    bulkActions.init('announcementsTable', {
        exportRoute: '{{ route('admin.announcements') }}'
    });

    // Initialiser les bulk actions pour la liste des emails envoyés
    const sentEmailsContainer = document.getElementById('bulkActionsContainer-sentEmailsTable');
    if (sentEmailsContainer) {
        const bar = document.createElement('div');
        bar.id = 'bulkActionsBar-sentEmailsTable';
        bar.className = 'bulk-actions-bar';
        bar.style.display = 'none';
        bar.innerHTML = `
            <div class="bulk-actions-bar__content">
                <div class="bulk-actions-bar__info">
                    <span class="bulk-actions-bar__count" id="selectedCount-sentEmailsTable">0</span>
                    <span class="bulk-actions-bar__text">élément(s) sélectionné(s)</span>
                </div>
                <div class="bulk-actions-bar__actions">
                    <button type="button" class="btn btn-sm btn-danger bulk-action-btn" data-action="delete" data-table-id="sentEmailsTable" data-confirm="true" data-confirm-message="Êtes-vous sûr de vouloir supprimer les emails sélectionnés ?" data-route="{{ route('admin.emails.sent.bulk-action') }}" data-method="POST">
                        <i class="fas fa-trash me-1"></i>Supprimer
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown-sentEmailsTable" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i>Exporter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown-sentEmailsTable">
                            <li><a class="dropdown-item export-link" href="#" data-format="csv" data-table-id="sentEmailsTable"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                            <li><a class="dropdown-item export-link" href="#" data-format="excel" data-table-id="sentEmailsTable"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkActions.clearSelection('sentEmailsTable')">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                </div>
            </div>
        `;
        sentEmailsContainer.appendChild(bar);
    }
    bulkActions.init('sentEmailsTable', {
        exportRoute: '{{ route('admin.announcements') }}'
    });

    // Initialiser les bulk actions pour la liste des emails programmés
    const scheduledEmailsContainer = document.getElementById('bulkActionsContainer-scheduledEmailsTable');
    if (scheduledEmailsContainer) {
        const bar = document.createElement('div');
        bar.id = 'bulkActionsBar-scheduledEmailsTable';
        bar.className = 'bulk-actions-bar';
        bar.style.display = 'none';
        bar.innerHTML = `
            <div class="bulk-actions-bar__content">
                <div class="bulk-actions-bar__info">
                    <span class="bulk-actions-bar__count" id="selectedCount-scheduledEmailsTable">0</span>
                    <span class="bulk-actions-bar__text">élément(s) sélectionné(s)</span>
                </div>
                <div class="bulk-actions-bar__actions">
                    <button type="button" class="btn btn-sm btn-warning bulk-action-btn" data-action="cancel" data-table-id="scheduledEmailsTable" data-confirm="true" data-confirm-message="Êtes-vous sûr de vouloir annuler les emails programmés sélectionnés ?" data-route="{{ route('admin.emails.scheduled.bulk-action') }}" data-method="POST">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                    <button type="button" class="btn btn-sm btn-danger bulk-action-btn" data-action="delete" data-table-id="scheduledEmailsTable" data-confirm="true" data-confirm-message="Êtes-vous sûr de vouloir supprimer les emails programmés sélectionnés ?" data-route="{{ route('admin.emails.scheduled.bulk-action') }}" data-method="POST">
                        <i class="fas fa-trash me-1"></i>Supprimer
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown-scheduledEmailsTable" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i>Exporter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown-scheduledEmailsTable">
                            <li><a class="dropdown-item export-link" href="#" data-format="csv" data-table-id="scheduledEmailsTable"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                            <li><a class="dropdown-item export-link" href="#" data-format="excel" data-table-id="scheduledEmailsTable"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkActions.clearSelection('scheduledEmailsTable')">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                </div>
            </div>
        `;
        scheduledEmailsContainer.appendChild(bar);
    }
    bulkActions.init('scheduledEmailsTable', {
        exportRoute: '{{ route('admin.announcements') }}'
    });

    // Initialiser les bulk actions pour la liste des messages WhatsApp
    const whatsappContainer = document.getElementById('bulkActionsContainer-whatsappTable');
    if (whatsappContainer) {
        const bar = document.createElement('div');
        bar.id = 'bulkActionsBar-whatsappTable';
        bar.className = 'bulk-actions-bar';
        bar.style.display = 'none';
        bar.innerHTML = `
            <div class="bulk-actions-bar__content">
                <div class="bulk-actions-bar__info">
                    <span class="bulk-actions-bar__count" id="selectedCount-whatsappTable">0</span>
                    <span class="bulk-actions-bar__text">élément(s) sélectionné(s)</span>
                </div>
                <div class="bulk-actions-bar__actions">
                    <button type="button" class="btn btn-sm btn-danger bulk-action-btn" data-action="delete" data-table-id="whatsappTable" data-confirm="true" data-confirm-message="Êtes-vous sûr de vouloir supprimer les messages WhatsApp sélectionnés ?" data-route="{{ route('admin.whatsapp-messages.bulk-action') }}" data-method="POST">
                        <i class="fas fa-trash me-1"></i>Supprimer
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown-whatsappTable" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i>Exporter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown-whatsappTable">
                            <li><a class="dropdown-item export-link" href="#" data-format="csv" data-table-id="whatsappTable"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                            <li><a class="dropdown-item export-link" href="#" data-format="excel" data-table-id="whatsappTable"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkActions.clearSelection('whatsappTable')">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                </div>
            </div>
        `;
        whatsappContainer.appendChild(bar);
    }
    bulkActions.init('whatsappTable', {
        exportRoute: '{{ route('admin.announcements') }}'
    });
});

function editAnnouncement(id) {
    // Récupérer les données de l'annonce via AJAX
    fetch(`/admin/announcements/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            // Remplir le formulaire d'édition
            document.getElementById('edit_title').value = data.title || '';
            document.getElementById('edit_content').value = data.content || '';
            document.getElementById('edit_type').value = data.type || 'info';
            document.getElementById('edit_button_text').value = data.button_text || '';
            document.getElementById('edit_button_url').value = data.button_url || '';
            document.getElementById('edit_starts_at').value = data.starts_at ? data.starts_at.substring(0, 16) : '';
            document.getElementById('edit_expires_at').value = data.expires_at ? data.expires_at.substring(0, 16) : '';
            document.getElementById('edit_is_active').checked = data.is_active || false;
            
            // Mettre à jour l'action du formulaire
            document.getElementById('editAnnouncementForm').action = `/admin/announcements/${id}`;
            
            // Ouvrir le modal
            const modal = new bootstrap.Modal(document.getElementById('editAnnouncementModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Erreur lors du chargement de l\'annonce:', error);
            alert('Erreur lors du chargement de l\'annonce');
        });
}

async function openDeleteAnnouncementModal(button) {
    const action = button?.dataset?.action;
    if (!action) {
        console.error('Aucune action de suppression fournie.');
        return;
    }

    const title = button.dataset.title || '';
    const message = title
        ? `Êtes-vous sûr de vouloir supprimer l'annonce « ${title} » ? Cette action est irréversible.`
        : `Êtes-vous sûr de vouloir supprimer cette annonce ? Cette action est irréversible.`;
    
    const confirmed = await showModernConfirmModal(message, {
        title: 'Supprimer l\'annonce',
        confirmButtonText: 'Supprimer',
        confirmButtonClass: 'btn-danger',
        icon: 'fa-exclamation-triangle'
    });
    
    if (confirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        
        // Ajouter le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
        
        // Ajouter la méthode DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

async function openDeleteEmailModal(button) {
    const action = button?.dataset?.action;
    if (!action) {
        console.error('Aucune action de suppression fournie.');
        return;
    }

    const subject = button.dataset.subject || '';
    const message = subject
        ? `Êtes-vous sûr de vouloir supprimer l'email « ${subject} » ? Cette action est irréversible.`
        : `Êtes-vous sûr de vouloir supprimer cet email ? Cette action est irréversible.`;
    
    const confirmed = await showModernConfirmModal(message, {
        title: 'Supprimer l\'email',
        confirmButtonText: 'Supprimer',
        confirmButtonClass: 'btn-danger',
        icon: 'fa-exclamation-triangle'
    });
    
    if (confirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        
        // Ajouter le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
        
        // Ajouter la méthode DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

async function openCancelScheduledEmailModal(button) {
    const action = button?.dataset?.action;
    if (!action) {
        console.error('Aucune action d\'annulation fournie.');
        return;
    }

    const subject = button.dataset.subject || '';
    const message = subject
        ? `Êtes-vous sûr de vouloir annuler l'email programmé « ${subject} » ?`
        : `Êtes-vous sûr de vouloir annuler cet email programmé ?`;
    
    const confirmed = await showModernConfirmModal(message, {
        title: 'Annuler l\'email programmé',
        confirmButtonText: 'Annuler',
        confirmButtonClass: 'btn-warning',
        icon: 'fa-exclamation-triangle'
    });
    
    if (confirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        
        // Ajouter le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
        
        document.body.appendChild(form);
        form.submit();
    }
}

function viewWhatsAppMessage(id) {
    window.location.href = '{{ route("admin.whatsapp-messages.show", ":id") }}'.replace(':id', id);
}

async function openDeleteWhatsAppModal(button) {
    const action = button?.dataset?.action;
    if (!action) {
        console.error('Aucune action de suppression fournie.');
        return;
    }

    const messageText = button.dataset.message || '';
    const message = messageText
        ? `Êtes-vous sûr de vouloir supprimer le message WhatsApp « ${messageText} » ? Cette action est irréversible.`
        : `Êtes-vous sûr de vouloir supprimer ce message WhatsApp ? Cette action est irréversible.`;
    
    const confirmed = await showModernConfirmModal(message, {
        title: 'Supprimer le message WhatsApp',
        confirmButtonText: 'Supprimer',
        confirmButtonClass: 'btn-danger',
        icon: 'fa-exclamation-triangle'
    });
    
    if (confirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        
        // Ajouter le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
        
        // Ajouter la méthode DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush

@push('styles')
<style>
/* Gestion des contenus qui dépassent dans les colonnes */
.admin-table table td {
    overflow: hidden;
    text-overflow: ellipsis;
}

.admin-table table td .text-truncate {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

/* Colonnes avec white-space: nowrap */
.admin-table table td[style*="white-space: nowrap"] {
    white-space: nowrap !important;
}

/* Design moderne pour la page de gestion des annonces */
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

.table tbody tr:hover {
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
    
    /* Adaptation du header de la section Gestion des emails */
    .admin-panel__header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.75rem;
    }
    
    .admin-panel__header h3 {
        font-size: 1rem;
        width: 100%;
    }
    
    .admin-panel__header .btn-primary {
        width: 100%;
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
    }
    
    .admin-panel__header .btn-primary.btn-sm {
        width: 100% !important;
        flex-shrink: 1 !important;
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
    
    .card-header .btn-light:not(.btn-sm), .card-header button.btn-light {
        width: 100%;
    }
    
    /* Bouton "Voir tout" sur très petits écrans - toujours visible */
    .btn-outline-primary.btn-sm {
        font-size: 0.7rem;
        padding: 0.35rem 0.6rem;
        white-space: nowrap;
        width: auto;
        min-width: auto;
    }
    
    .btn-outline-primary.btn-sm i {
        font-size: 0.65rem;
        margin-right: 0.15rem;
    }
}

@media (max-width: 991.98px) {
    /* Réduire la taille des boutons dans les en-têtes sur tablette */
    .admin-panel__header .btn-primary {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
    }
    
    .admin-panel__header .btn i {
        font-size: 0.75rem;
        margin-right: 0.3rem !important;
    }
    
    .admin-panel__header h3 {
        font-size: 1.1rem;
    }
    
    /* Forcer les onglets à rester sur une ligne en 2 colonnes sur tablette */
    .nav-tabs {
        display: flex;
        flex-wrap: nowrap;
    }
    
    .nav-tabs .nav-item {
        flex: 1 1 50%;
        min-width: 0;
        max-width: 50%;
    }
    
    .nav-tabs .nav-link {
        font-size: 0.75rem;
        padding: 0.4rem 0.5rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
    }
    
    .nav-tabs .nav-link i {
        font-size: 0.7rem;
        margin-right: 0;
        flex-shrink: 0;
    }
    
    .nav-tabs .nav-link .badge {
        font-size: 0.65rem;
        padding: 0.15em 0.35em;
        margin-left: 0.2rem !important;
        flex-shrink: 0;
    }
    
    /* Bouton "Voir tout" sur tablette - visible et accessible */
    .btn-outline-primary.btn-sm {
        font-size: 0.75rem;
        padding: 0.4rem 0.75rem;
        white-space: nowrap;
        flex-shrink: 0;
        min-width: auto;
    }
    
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
    
    /* Scrollbar visible pour le tableau WhatsApp sur mobile */
    .whatsapp-messages-table {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .whatsapp-messages-table::-webkit-scrollbar {
        height: 8px;
    }
    
    .whatsapp-messages-table::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .whatsapp-messages-table::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .whatsapp-messages-table::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
}

@media (max-width: 767.98px) {
    /* Réduire la taille des boutons dans les en-têtes sur mobile */
    .admin-panel__header .btn {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .admin-panel__header .btn i {
        font-size: 0.7rem;
        margin-right: 0.25rem !important;
    }
    
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
    
    /* Padding-top supplémentaire pour les statistiques WhatsApp sur mobile */
    .whatsapp-stats-row {
        padding-top: 1.25rem !important;
    }
    
    .admin-panel__header {
        padding: 0.375rem 0.5rem;
        flex-wrap: nowrap;
        gap: 0.5rem;
    }
    
    .admin-panel__header h3 {
        font-size: 0.85rem;
        margin-bottom: 0;
        flex: 1;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .admin-panel__header h3 i {
        font-size: 0.75rem;
    }
    
    
    /* Avatar Email - toujours circulaire, jamais déformé (desktop et mobile) */
    .email-avatar-container {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        max-width: 40px !important;
        min-height: 40px !important;
        max-height: 40px !important;
        flex-shrink: 0 !important;
        border-radius: 50% !important;
        -webkit-border-radius: 50% !important;
        -moz-border-radius: 50% !important;
        overflow: hidden !important;
        display: inline-block !important;
        position: relative !important;
        aspect-ratio: 1 / 1 !important;
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .email-avatar {
        width: 100% !important;
        height: 100% !important;
        min-width: 100% !important;
        min-height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: cover !important;
        object-position: center !important;
        display: block !important;
        border-radius: 50% !important;
        -webkit-border-radius: 50% !important;
        -moz-border-radius: 50% !important;
        aspect-ratio: 1 / 1 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
    }
    
    /* Forcer l'avatar email sur desktop - même taille que mobile et TOUJOURS ROND */
    @media (min-width: 768px) {
        .email-avatar-container {
            width: 40px !important;
            height: 40px !important;
            min-width: 40px !important;
            max-width: 40px !important;
            min-height: 40px !important;
            max-height: 40px !important;
            border-radius: 50% !important;
            -webkit-border-radius: 50% !important;
            -moz-border-radius: 50% !important;
        }
        
        .email-avatar {
            width: 100% !important;
            height: 100% !important;
            min-width: 100% !important;
            min-height: 100% !important;
            border-radius: 50% !important;
            -webkit-border-radius: 50% !important;
            -moz-border-radius: 50% !important;
        }
        
        /* S'assurer que la cellule du tableau ne déforme pas l'avatar */
        .table tbody td:first-child {
            width: 50px !important;
            min-width: 50px !important;
            max-width: 50px !important;
        }
    }
    
    /* Avatar WhatsApp - toujours circulaire, jamais déformé (desktop et mobile) */
    .whatsapp-avatar-container {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        max-width: 40px !important;
        min-height: 40px !important;
        max-height: 40px !important;
        flex-shrink: 0 !important;
        border-radius: 50% !important;
        -webkit-border-radius: 50% !important;
        -moz-border-radius: 50% !important;
        overflow: hidden !important;
        display: inline-block !important;
        position: relative !important;
        aspect-ratio: 1 / 1 !important;
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .whatsapp-avatar {
        width: 100% !important;
        height: 100% !important;
        min-width: 100% !important;
        min-height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: cover !important;
        object-position: center !important;
        display: block !important;
        border-radius: 50% !important;
        -webkit-border-radius: 50% !important;
        -moz-border-radius: 50% !important;
        aspect-ratio: 1 / 1 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
    }
    
    /* Forcer l'avatar sur desktop - même taille que mobile et TOUJOURS ROND */
    @media (min-width: 768px) {
        .whatsapp-avatar-container {
            width: 40px !important;
            height: 40px !important;
            min-width: 40px !important;
            max-width: 40px !important;
            min-height: 40px !important;
            max-height: 40px !important;
            border-radius: 50% !important;
            -webkit-border-radius: 50% !important;
            -moz-border-radius: 50% !important;
            overflow: hidden !important;
        }
        
        .whatsapp-avatar {
            width: 100% !important;
            height: 100% !important;
            min-width: 100% !important;
            min-height: 100% !important;
            border-radius: 50% !important;
            -webkit-border-radius: 50% !important;
            -moz-border-radius: 50% !important;
            object-fit: cover !important;
        }
        
        /* S'assurer que la cellule du tableau ne déforme pas l'avatar */
        .table tbody td:first-child {
            width: 50px !important;
            min-width: 50px !important;
            max-width: 50px !important;
        }
        
        /* Forcer le border-radius sur tous les navigateurs */
        .table tbody td .whatsapp-avatar-container,
        .table tbody td .whatsapp-avatar-container * {
            border-radius: 50% !important;
            -webkit-border-radius: 50% !important;
            -moz-border-radius: 50% !important;
        }
    }
    
    
    /* Espacement des statistiques emails */
    .email-stats-row {
        padding-top: 0.75rem;
        margin-bottom: 0.75rem !important;
    }
    
    @media (min-width: 768px) {
        .email-stats-row {
            padding-top: 1rem;
            margin-bottom: 1rem !important;
        }
    }
    
    /* Espacement des statistiques WhatsApp */
    .whatsapp-stats-row {
        padding-top: 1rem;
        margin-bottom: 0.75rem !important;
    }
    
    @media (min-width: 768px) {
        .whatsapp-stats-row {
            padding-top: 1rem;
            margin-bottom: 1rem !important;
        }
    }
    
    /* Adaptation des cartes de statistiques pour mobile */
    .admin-stats-grid {
        gap: 0.375rem !important;
    }
    
    .admin-stat-card {
        padding: 0.5rem 0.5rem !important;
    }
    
    .admin-stat-card__icon {
        width: 32px !important;
        height: 32px !important;
        min-width: 32px !important;
    }
    
    .admin-stat-card__icon i {
        font-size: 0.875rem !important;
    }
    
    .admin-stat-card__value {
        font-size: 1rem !important;
        line-height: 1.2;
    }
    
    .admin-stat-card__label {
        font-size: 0.7rem !important;
        line-height: 1.2;
        margin-top: 0.125rem;
    }
    
    /* Adaptation des onglets pour mobile */
    .nav-tabs {
        font-size: 0.75rem;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        flex-wrap: nowrap;
        width: 100%;
    }
    
    .nav-tabs .nav-item {
        flex: 1 1 50%;
        min-width: 0;
        max-width: 50%;
    }
    
    .nav-tabs .nav-link {
        padding: 0.375rem 0.4rem;
        font-size: 0.7rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
    }
    
    .nav-tabs .nav-link i {
        font-size: 0.65rem;
        margin-right: 0;
        flex-shrink: 0;
    }
    
    .nav-tabs .nav-link .badge {
        font-size: 0.6rem;
        padding: 0.1em 0.3em;
        margin-left: 0.15rem !important;
        flex-shrink: 0;
    }
    
    /* Bouton "Voir tout" - visible sur tous les écrans */
    .btn-outline-primary.btn-sm {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
        white-space: nowrap;
        flex-shrink: 0;
    }
    
    .btn-outline-primary.btn-sm i {
        font-size: 0.7rem;
        margin-right: 0.25rem;
    }
    
    /* Adaptation des tableaux pour mobile */
    .table {
        font-size: 0.8rem;
    }
    
    .table thead th {
        font-size: 0.75rem;
        padding: 0.375rem 0.25rem;
        font-weight: 600;
    }
    
    .table tbody td {
        padding: 0.375rem 0.25rem;
        vertical-align: middle;
    }
    
    .table tbody td small {
        font-size: 0.75rem;
        line-height: 1.3;
    }
    
    .table .badge {
        font-size: 0.65rem;
        padding: 0.2em 0.4em;
    }
    
    .table .btn-sm {
        padding: 0.2rem 0.4rem;
        font-size: 0.7rem;
    }
    
    .table .btn-sm i {
        font-size: 0.7rem;
    }
    
    /* Adaptation des boutons d'action pour mobile */
    .table tbody td .btn-group {
        display: flex;
        gap: 0.25rem;
    }
    
    /* Adaptation des messages vides pour mobile */
    .table tbody td .fa-2x {
        font-size: 1.5rem !important;
    }
    
    .table tbody td p {
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }
    
    /* Adaptation des boutons "Voir tout" pour mobile */
    .text-center .btn-sm {
        font-size: 0.7rem;
        padding: 0.3rem 0.5rem;
    }
    
    .text-center .btn-sm i {
        font-size: 0.65rem;
        margin-right: 0.25rem;
    }
    
    /* Marges et hauteur pour les tab-pane */
    .tab-content .tab-pane {
        margin-left: 0.5rem;
        margin-right: 0.5rem;
        min-height: auto;
    }
    
    /* Avatar WhatsApp sur mobile - toujours circulaire, jamais déformé */
    .whatsapp-avatar-container {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        max-width: 40px !important;
        min-height: 40px !important;
        max-height: 40px !important;
        flex-shrink: 0;
        border-radius: 50% !important;
        overflow: hidden !important;
        display: inline-block;
        position: relative;
        aspect-ratio: 1 / 1;
    }
    
    .whatsapp-avatar {
        width: 100% !important;
        height: 100% !important;
        min-width: 100% !important;
        min-height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: cover !important;
        object-position: center !important;
        display: block !important;
        border-radius: 50% !important;
        aspect-ratio: 1 / 1;
    }
    
    @media (min-width: 768px) {
        .tab-content .tab-pane {
            margin-left: 0.75rem;
            margin-right: 0.75rem;
            min-height: 700px;
        }
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
    
    /* Scrollbar visible pour le tableau WhatsApp sur mobile */
    .whatsapp-messages-table {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .whatsapp-messages-table::-webkit-scrollbar {
        height: 8px;
    }
    
    .whatsapp-messages-table::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .whatsapp-messages-table::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .whatsapp-messages-table::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
}
</style>

<style>
/* Ajustement des boutons dans l'en-tête admin-actions sur mobile */
@media (max-width: 767.98px) {
    /* Centrer le header et le conteneur des actions */
    .admin-content__header {
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
    }
    
    .admin-content__header > div {
        width: 100% !important;
        text-align: center !important;
    }
    
    /* Centrer le conteneur des actions */
    .admin-content__actions {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        width: 100% !important;
        margin: 0 auto !important;
    }
    
    /* Cibler les boutons dans la section admin-content__actions */
    .admin-content__actions .d-flex.gap-2 {
        flex-direction: row !important;
        width: 100% !important;
        max-width: 100% !important;
        gap: 0.5rem !important;
        justify-content: center !important;
        align-items: center !important;
        margin: 0 auto !important;
    }
    
    .admin-content__actions .d-flex.gap-2 .btn {
        flex: 0 1 auto !important;
        width: calc(50% - 0.25rem) !important;
        max-width: calc(50% - 0.25rem) !important;
        font-size: 0.875rem !important;
        padding: 0.5rem 0.5rem !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .admin-content__actions .d-flex.gap-2 .btn i {
        font-size: 0.875rem !important;
        margin-right: 0.25rem !important;
    }
}

/* Ajustement pour très petits écrans */
@media (max-width: 575.98px) {
    .admin-content__actions .d-flex.gap-2 {
        gap: 0.4rem !important;
    }
    
    .admin-content__actions .d-flex.gap-2 {
        gap: 0.4rem !important;
        justify-content: center !important;
    }
    
    .admin-content__actions .d-flex.gap-2 .btn {
        flex: 0 1 auto !important;
        width: calc(50% - 0.2rem) !important;
        max-width: calc(50% - 0.2rem) !important;
        font-size: 0.75rem !important;
        padding: 0.45rem 0.4rem !important;
    }
    
    .admin-content__actions .d-flex.gap-2 .btn i {
        font-size: 0.75rem !important;
        margin-right: 0.2rem !important;
    }
}
</style>
@endpush

