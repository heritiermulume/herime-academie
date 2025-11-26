@extends('layouts.admin')

@section('title', 'Emails programmés')
@section('admin-title', 'Emails programmés')
@section('admin-subtitle', 'Gérez les emails programmés pour envoi automatique')
@section('admin-actions')
    <a href="{{ route('admin.announcements.send-email') }}" class="btn btn-success me-2">
        <i class="fas fa-envelope me-2"></i>Envoyer un email
    </a>
    <a href="{{ route('admin.announcements') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour aux annonces
    </a>
@endsection

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <!-- Statistiques -->
            <div class="admin-stats-grid mb-4">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Total</p>
                    <p class="admin-stat-card__value">{{ $stats['total'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">Emails programmés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">En attente</p>
                    <p class="admin-stat-card__value">{{ $stats['pending'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">À envoyer</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">En cours</p>
                    <p class="admin-stat-card__value">{{ $stats['processing'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">En traitement</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Terminés</p>
                    <p class="admin-stat-card__value">{{ $stats['completed'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">Envoyés avec succès</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Échoués</p>
                    <p class="admin-stat-card__value">{{ $stats['failed'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">En erreur</p>
                </div>
            </div>

            <!-- Filtres et recherche -->
            <x-admin.search-panel
                :action="route('admin.emails.scheduled')"
                formId="scheduledEmailsFilterForm"
                filtersId="scheduledEmailsFilters"
                :hasFilters="true"
                :searchValue="request('search')"
                placeholder="Rechercher par sujet..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>En cours</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminé</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échoué</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Ajustez les filtres puis appliquez-les.</span>
                        <a href="{{ route('admin.emails.scheduled') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            <!-- Table des emails programmés -->
            <div class="admin-table">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Sujet</th>
                                <th>Destinataires</th>
                                <th>Programmé pour</th>
                                <th>Statut</th>
                                <th>Progression</th>
                                <th>Créé par</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($emails as $email)
                            <tr>
                                <td>
                                    <strong>{{ Str::limit($email->subject, 60) }}</strong>
                                </td>
                                <td>
                                    <small>
                                        {{ $email->total_recipients }} destinataire(s)
                                        @if($email->recipient_type === 'role' && isset($email->recipient_config['roles']))
                                            <br><span class="badge bg-secondary">{{ implode(', ', $email->recipient_config['roles']) }}</span>
                                        @elseif($email->recipient_type === 'selected' && isset($email->recipient_config['user_ids']))
                                            <br><span class="badge bg-info">{{ count($email->recipient_config['user_ids']) }} sélectionnés</span>
                                        @elseif($email->recipient_type === 'all')
                                            <br><span class="badge bg-primary">Tous les utilisateurs</span>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <small>{{ $email->scheduled_at->format('d/m/Y à H:i') }}</small>
                                </td>
                                <td>
                                    @if($email->status === 'pending')
                                        <span class="badge bg-warning">En attente</span>
                                    @elseif($email->status === 'processing')
                                        <span class="badge bg-info">En cours</span>
                                    @elseif($email->status === 'completed')
                                        <span class="badge bg-success">Terminé</span>
                                    @elseif($email->status === 'failed')
                                        <span class="badge bg-danger" title="{{ $email->error_message }}">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Échoué
                                        </span>
                                    @elseif($email->status === 'cancelled')
                                        <span class="badge bg-secondary">Annulé</span>
                                    @endif
                                </td>
                                <td>
                                    @if($email->total_recipients > 0)
                                        <div class="progress" style="height: 20px;">
                                            @php
                                                $percentage = $email->total_recipients > 0 
                                                    ? ($email->sent_count / $email->total_recipients) * 100 
                                                    : 0;
                                            @endphp
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ $percentage }}%"
                                                 aria-valuenow="{{ $percentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ round($percentage) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            {{ $email->sent_count }}/{{ $email->total_recipients }} envoyés
                                            @if($email->failed_count > 0)
                                                <span class="text-danger">({{ $email->failed_count }} échecs)</span>
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small>
                                        {{ $email->creator->name ?? 'N/A' }}<br>
                                        <span class="text-muted">{{ $email->created_at->format('d/m/Y') }}</span>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.emails.scheduled.show', $email) }}" class="btn btn-sm btn-light" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($email->status === 'pending')
                                        <form action="{{ route('admin.emails.scheduled.cancel', $email) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cet email programmé ?');">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Annuler">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun email programmé trouvé</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <x-admin.pagination :paginator="$emails" />
        </div>
    </section>
@endsection



