@extends('layouts.admin')

@section('title', 'Gestion des annonces')
@section('admin-title', 'Annonces globales')
@section('admin-subtitle', 'Diffusez des messages clés auprès de vos apprenants et formateurs')
@section('admin-actions')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAnnouncementModal">
        <i class="fas fa-plus-circle me-2"></i>Nouvelle annonce
    </button>
@endsection

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
                    <div class="admin-table">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Titre</th>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th>Début</th>
                                        <th>Fin</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($announcements as $announcement)
                                    <tr>
                                        <td>
                                            <h6 class="mb-0">{{ $announcement->title }}</h6>
                                            <small class="text-muted">{{ Str::limit($announcement->content, 100) }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $announcement->type === 'info' ? 'info' : ($announcement->type === 'success' ? 'success' : ($announcement->type === 'warning' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($announcement->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $announcement->is_active ? 'success' : 'secondary' }}">
                                                {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                {{ $announcement->starts_at ? $announcement->starts_at->format('d/m/Y H:i') : 'Immédiat' }}
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                {{ $announcement->expires_at ? $announcement->expires_at->format('d/m/Y H:i') : 'Illimité' }}
                                            </small>
                                        </td>
                                        <td class="text-center align-top">
                                            @if($loop->first)
                                                <div class="dropdown d-none d-md-block">
                                                    <button class="btn btn-sm btn-light course-actions-btn" type="button" id="actionsDropdown{{ $announcement->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown{{ $announcement->id }}">
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="editAnnouncement({{ $announcement->id }}); return false;">
                                                                <i class="fas fa-edit me-2"></i>Modifier
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" 
                                                               data-action="{{ route('admin.announcements.destroy', $announcement) }}"
                                                               data-title="{{ $announcement->title }}"
                                                               onclick="openDeleteAnnouncementModal(this); return false;">
                                                                <i class="fas fa-trash me-2"></i>Supprimer
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="dropdown d-md-none">
                                                    <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="actionsDropdownMobile{{ $announcement->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdownMobile{{ $announcement->id }}">
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="editAnnouncement({{ $announcement->id }}); return false;">
                                                                <i class="fas fa-edit me-2"></i>Modifier
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" 
                                                               data-action="{{ route('admin.announcements.destroy', $announcement) }}"
                                                               data-title="{{ $announcement->title }}"
                                                               onclick="openDeleteAnnouncementModal(this); return false;">
                                                                <i class="fas fa-trash me-2"></i>Supprimer
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @else
                                                <div class="dropup d-none d-md-block">
                                                    <button class="btn btn-sm btn-light course-actions-btn" type="button" id="actionsDropdown{{ $announcement->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown{{ $announcement->id }}">
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="editAnnouncement({{ $announcement->id }}); return false;">
                                                                <i class="fas fa-edit me-2"></i>Modifier
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" 
                                                               data-action="{{ route('admin.announcements.destroy', $announcement) }}"
                                                               data-title="{{ $announcement->title }}"
                                                               onclick="openDeleteAnnouncementModal(this); return false;">
                                                                <i class="fas fa-trash me-2"></i>Supprimer
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="dropup d-md-none">
                                                    <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="actionsDropdownMobile{{ $announcement->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdownMobile{{ $announcement->id }}">
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="editAnnouncement({{ $announcement->id }}); return false;">
                                                                <i class="fas fa-edit me-2"></i>Modifier
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" 
                                                               data-action="{{ route('admin.announcements.destroy', $announcement) }}"
                                                               data-title="{{ $announcement->title }}"
                                                               onclick="openDeleteAnnouncementModal(this); return false;">
                                                                <i class="fas fa-trash me-2"></i>Supprimer
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
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
        <div class="admin-panel__header d-flex justify-content-between align-items-center">
            <h3 class="mb-0 flex-grow-1"><i class="fas fa-envelope me-2"></i>Gestion des emails</h3>
            <!-- Icône pour envoyer un email -->
            <a href="{{ route('admin.announcements.send-email') }}" class="email-send-icon-btn" title="Envoyer un email">
                <i class="fas fa-envelope"></i>
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
            <ul class="nav nav-tabs mb-3" id="emailsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="sent-emails-tab" data-bs-toggle="tab" data-bs-target="#sent-emails" type="button" role="tab">
                        <i class="fas fa-paper-plane me-2"></i>Emails envoyés
                        <span class="badge bg-primary ms-2">{{ count($recentSentEmails ?? []) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="scheduled-emails-tab" data-bs-toggle="tab" data-bs-target="#scheduled-emails" type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>Emails programmés
                        <span class="badge bg-warning ms-2">{{ count($pendingScheduledEmails ?? []) }}</span>
                    </button>
                </li>
                <li class="nav-item ms-auto d-none d-md-block" role="presentation">
                    <a href="{{ route('admin.emails.sent') }}" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="fas fa-list me-2"></i>Voir tout
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="emailsTabContent">
                <!-- Onglet emails envoyés -->
                <div class="tab-pane fade show active" id="sent-emails" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Destinataire</th>
                                    <th>Sujet</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSentEmails ?? [] as $email)
                                <tr>
                                    <td>
                                        <small>
                                            {{ $email->recipient_name ?? 'N/A' }}<br>
                                            <span class="text-muted">{{ $email->recipient_email }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($email->subject, 50) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($email->type) }}</span>
                                    </td>
                                    <td>
                                        @if($email->status === 'sent')
                                            <span class="badge bg-success">Envoyé</span>
                                        @elseif($email->status === 'failed')
                                            <span class="badge bg-danger">Échoué</span>
                                        @else
                                            <span class="badge bg-secondary">En attente</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $email->sent_at ? $email->sent_at->format('d/m/Y H:i') : ($email->created_at->format('d/m/Y H:i')) }}</small>
                                    </td>
                                    <td class="text-center align-top">
                                        @if($loop->first)
                                            <div class="dropdown d-none d-md-block">
                                                <button class="btn btn-sm btn-light course-actions-btn" type="button" id="emailActionsDropdown{{ $email->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="emailActionsDropdown{{ $email->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.emails.sent.show', $email) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.emails.sent.destroy', $email) }}"
                                                           data-subject="{{ $email->subject }}"
                                                           onclick="openDeleteEmailModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="dropdown d-md-none">
                                                <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="emailActionsDropdownMobile{{ $email->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="emailActionsDropdownMobile{{ $email->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.emails.sent.show', $email) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.emails.sent.destroy', $email) }}"
                                                           data-subject="{{ $email->subject }}"
                                                           onclick="openDeleteEmailModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @else
                                            <div class="dropup d-none d-md-block">
                                                <button class="btn btn-sm btn-light course-actions-btn" type="button" id="emailActionsDropdown{{ $email->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="emailActionsDropdown{{ $email->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.emails.sent.show', $email) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.emails.sent.destroy', $email) }}"
                                                           data-subject="{{ $email->subject }}"
                                                           onclick="openDeleteEmailModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="dropup d-md-none">
                                                <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="emailActionsDropdownMobile{{ $email->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="emailActionsDropdownMobile{{ $email->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.emails.sent.show', $email) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.emails.sent.destroy', $email) }}"
                                                           data-subject="{{ $email->subject }}"
                                                           onclick="openDeleteEmailModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">Aucun email envoyé récemment</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(count($recentSentEmails ?? []) > 0)
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.emails.sent') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-list me-2"></i>Voir tous les emails envoyés
                        </a>
                    </div>
                    @endif
                </div>

                <!-- Onglet emails programmés -->
                <div class="tab-pane fade" id="scheduled-emails" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Sujet</th>
                                    <th>Destinataires</th>
                                    <th>Programmé pour</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingScheduledEmails ?? [] as $scheduled)
                                <tr>
                                    <td>
                                        <small><strong>{{ Str::limit($scheduled->subject, 50) }}</strong></small>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $scheduled->total_recipients }} destinataire(s)
                                            @if($scheduled->recipient_type === 'role' && isset($scheduled->recipient_config['roles']))
                                                <br><span class="badge bg-secondary">{{ implode(', ', $scheduled->recipient_config['roles']) }}</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <small>{{ $scheduled->scheduled_at->format('d/m/Y à H:i') }}</small>
                                    </td>
                                    <td>
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
                                    <td class="text-center align-top">
                                        @if($loop->first)
                                            <div class="dropdown d-none d-md-block">
                                                <button class="btn btn-sm btn-light course-actions-btn" type="button" id="scheduledActionsDropdown{{ $scheduled->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="scheduledActionsDropdown{{ $scheduled->id }}">
                                                    @if($scheduled->status === 'pending')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.announcements.send-email', ['edit' => $scheduled->id]) }}">
                                                            <i class="fas fa-edit me-2"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.emails.scheduled.show', $scheduled) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                        @if($scheduled->status === 'pending')
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.emails.scheduled.cancel', $scheduled) }}"
                                                           data-subject="{{ $scheduled->subject }}"
                                                           onclick="openCancelScheduledEmailModal(this); return false;">
                                                            <i class="fas fa-times me-2"></i>Annuler
                                                        </a>
                                                    </li>
                                                    @else
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.emails.scheduled.destroy', $scheduled) }}"
                                                           data-subject="{{ $scheduled->subject }}"
                                                           onclick="openDeleteEmailModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="dropdown d-md-none">
                                                <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="scheduledActionsDropdownMobile{{ $scheduled->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="scheduledActionsDropdownMobile{{ $scheduled->id }}">
                                                    @if($scheduled->status === 'pending')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.announcements.send-email', ['edit' => $scheduled->id]) }}">
                                                            <i class="fas fa-edit me-2"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.emails.scheduled.show', $scheduled) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    @if($scheduled->status === 'pending')
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.emails.scheduled.cancel', $scheduled) }}"
                                                           data-subject="{{ $scheduled->subject }}"
                                                           onclick="openCancelScheduledEmailModal(this); return false;">
                                                            <i class="fas fa-times me-2"></i>Annuler
                                                        </a>
                                                    </li>
                                                    @else
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.emails.scheduled.destroy', $scheduled) }}"
                                                           data-subject="{{ $scheduled->subject }}"
                                                           onclick="openDeleteEmailModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @else
                                            <div class="dropup d-none d-md-block">
                                                <button class="btn btn-sm btn-light course-actions-btn" type="button" id="scheduledActionsDropdown{{ $scheduled->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="scheduledActionsDropdown{{ $scheduled->id }}">
                                                    @if($scheduled->status === 'pending')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.announcements.send-email', ['edit' => $scheduled->id]) }}">
                                                            <i class="fas fa-edit me-2"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.emails.scheduled.show', $scheduled) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    @if($scheduled->status === 'pending')
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.emails.scheduled.cancel', $scheduled) }}"
                                                           data-subject="{{ $scheduled->subject }}"
                                                           onclick="openCancelScheduledEmailModal(this); return false;">
                                                            <i class="fas fa-times me-2"></i>Annuler
                                                        </a>
                                                    </li>
                                                    @else
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.emails.scheduled.destroy', $scheduled) }}"
                                                           data-subject="{{ $scheduled->subject }}"
                                                           onclick="openDeleteEmailModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="dropup d-md-none">
                                                <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="scheduledActionsDropdownMobile{{ $scheduled->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="scheduledActionsDropdownMobile{{ $scheduled->id }}">
                                                    @if($scheduled->status === 'pending')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.announcements.send-email', ['edit' => $scheduled->id]) }}">
                                                            <i class="fas fa-edit me-2"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.emails.scheduled.show', $scheduled) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    @if($scheduled->status === 'pending')
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.emails.scheduled.cancel', $scheduled) }}"
                                                           data-subject="{{ $scheduled->subject }}"
                                                           onclick="openCancelScheduledEmailModal(this); return false;">
                                                            <i class="fas fa-times me-2"></i>Annuler
                                                        </a>
                                                    </li>
                                                    @else
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           data-action="{{ route('admin.emails.scheduled.destroy', $scheduled) }}"
                                                           data-subject="{{ $scheduled->subject }}"
                                                           onclick="openDeleteEmailModal(this); return false;">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">
                                        <i class="fas fa-clock fa-2x mb-2"></i>
                                        <p class="mb-0">Aucun email programmé</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(count($pendingScheduledEmails ?? []) > 0)
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.emails.scheduled') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-list me-2"></i>Voir tous les emails programmés
                        </a>
                    </div>
                    @endif
                </div>
            </div>
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
<script>
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

function openDeleteAnnouncementModal(button) {
    const action = button?.dataset?.action;
    if (!action) {
        console.error('Aucune action de suppression fournie.');
        return;
    }

    const title = button.dataset.title || '';
    const messageElement = document.getElementById('deleteAnnouncementMessage');
    if (messageElement) {
        messageElement.textContent = title
            ? `Êtes-vous sûr de vouloir supprimer l'annonce « ${title} » ? Cette action est irréversible.`
            : `Êtes-vous sûr de vouloir supprimer cette annonce ? Cette action est irréversible.`;
    }

    const form = document.getElementById('deleteAnnouncementForm');
    if (form) {
        form.action = action;
    }

    const modalElement = document.getElementById('deleteAnnouncementModal');
    if (!modalElement) {
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function openDeleteEmailModal(button) {
    const action = button?.dataset?.action;
    if (!action) {
        console.error('Aucune action de suppression fournie.');
        return;
    }

    const subject = button.dataset.subject || '';
    const messageElement = document.getElementById('deleteEmailMessage');
    if (messageElement) {
        messageElement.textContent = subject
            ? `Êtes-vous sûr de vouloir supprimer l'email « ${subject} » ? Cette action est irréversible.`
            : `Êtes-vous sûr de vouloir supprimer cet email ? Cette action est irréversible.`;
    }

    const form = document.getElementById('deleteEmailForm');
    if (form) {
        form.action = action;
    }

    const modalElement = document.getElementById('deleteEmailModal');
    if (!modalElement) {
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function openCancelScheduledEmailModal(button) {
    const action = button?.dataset?.action;
    if (!action) {
        console.error('Aucune action d\'annulation fournie.');
        return;
    }

    const subject = button.dataset.subject || '';
    const messageElement = document.getElementById('cancelScheduledEmailMessage');
    if (messageElement) {
        messageElement.textContent = subject
            ? `Êtes-vous sûr de vouloir annuler l'email programmé « ${subject} » ?`
            : `Êtes-vous sûr de vouloir annuler cet email programmé ?`;
    }

    const form = document.getElementById('cancelScheduledEmailForm');
    if (form) {
        form.action = action;
    }

    const modalElement = document.getElementById('cancelScheduledEmailModal');
    if (!modalElement) {
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}
</script>
@endpush

@push('styles')
<style>
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
}

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
    
    /* Icône pour envoyer un email dans la section emails */
    .email-send-icon-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        flex-shrink: 0;
        margin-left: 0.5rem;
        color: #198754;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .email-send-icon-btn:hover {
        color: #157347;
        background-color: rgba(25, 135, 84, 0.1);
        transform: scale(1.1);
    }
    
    .email-send-icon-btn i {
        margin: 0;
        font-size: 1rem;
    }
    
    @media (min-width: 768px) {
        .email-send-icon-btn {
            width: 36px;
            height: 36px;
        }
        
        .email-send-icon-btn i {
            font-size: 1.1rem;
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
    }
    
    .nav-tabs .nav-link {
        padding: 0.375rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .nav-tabs .nav-link i {
        font-size: 0.7rem;
        margin-right: 0.25rem;
    }
    
    .nav-tabs .badge {
        font-size: 0.65rem;
        padding: 0.15em 0.4em;
        margin-left: 0.25rem !important;
    }
    
    .nav-tabs .btn-sm {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        margin-top: 0.25rem !important;
    }
    
    .nav-tabs .btn-sm i {
        font-size: 0.65rem;
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
        min-height: 600px;
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
}
</style>
@endpush

<!-- Modal de suppression -->
<div class="modal fade" id="deleteAnnouncementModal" tabindex="-1" aria-labelledby="deleteAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAnnouncementModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p id="deleteAnnouncementMessage">Êtes-vous sûr de vouloir supprimer cette annonce ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteAnnouncementForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de suppression d'email -->
<div class="modal fade" id="deleteEmailModal" tabindex="-1" aria-labelledby="deleteEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEmailModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p id="deleteEmailMessage">Êtes-vous sûr de vouloir supprimer cet email ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteEmailForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'annulation d'email programmé -->
<div class="modal fade" id="cancelScheduledEmailModal" tabindex="-1" aria-labelledby="cancelScheduledEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelScheduledEmailModalLabel">Confirmer l'annulation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p id="cancelScheduledEmailMessage">Êtes-vous sûr de vouloir annuler cet email programmé ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="cancelScheduledEmailForm" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-times me-2"></i>Annuler l'email
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
