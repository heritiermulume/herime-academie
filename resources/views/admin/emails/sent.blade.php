@extends('layouts.admin')

@section('title', 'Emails envoyés')
@section('admin-title', 'Emails envoyés')
@section('admin-subtitle', 'Consultez l\'historique de tous les emails envoyés')
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
                    <p class="admin-stat-card__muted">Emails envoyés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Envoyés</p>
                    <p class="admin-stat-card__value">{{ $stats['sent'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">Avec succès</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Échoués</p>
                    <p class="admin-stat-card__value">{{ $stats['failed'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">En erreur</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">En attente</p>
                    <p class="admin-stat-card__value">{{ $stats['pending'] ?? 0 }}</p>
                    <p class="admin-stat-card__muted">Non envoyés</p>
                </div>
            </div>

            <!-- Filtres et recherche -->
            <x-admin.search-panel
                :action="route('admin.emails.sent')"
                formId="sentEmailsFilterForm"
                filtersId="sentEmailsFilters"
                :hasFilters="true"
                :searchValue="request('search')"
                placeholder="Rechercher par sujet, email ou destinataire..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Type</label>
                            <select class="form-select" name="type">
                                <option value="">Tous les types</option>
                                <option value="invoice" {{ request('type') == 'invoice' ? 'selected' : '' }}>Facture</option>
                                <option value="enrollment" {{ request('type') == 'enrollment' ? 'selected' : '' }}>Inscription</option>
                                <option value="announcement" {{ request('type') == 'announcement' ? 'selected' : '' }}>Annonce</option>
                                <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Personnalisé</option>
                                <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>Paiement</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Envoyé</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échoué</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Date de début</label>
                            <input type="date" class="form-select" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Date de fin</label>
                            <input type="date" class="form-select" name="date_to" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Ajustez les filtres puis appliquez-les.</span>
                        <a href="{{ route('admin.emails.sent') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            <!-- Table des emails envoyés -->
            <div class="admin-table">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Destinataire</th>
                                <th>Sujet</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Date d'envoi</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($emails as $email)
                            <tr>
                                <td>
                                    <strong>{{ $email->recipient_name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $email->recipient_email }}</small>
                                </td>
                                <td>
                                    <strong>{{ Str::limit($email->subject, 60) }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($email->type) }}</span>
                                </td>
                                <td>
                                    @if($email->status === 'sent')
                                        <span class="badge bg-success">Envoyé</span>
                                    @elseif($email->status === 'failed')
                                        <span class="badge bg-danger" title="{{ $email->error_message }}">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Échoué
                                        </span>
                                    @else
                                        <span class="badge bg-warning">En attente</span>
                                    @endif
                                </td>
                                <td>
                                    <small>
                                        {{ $email->sent_at ? $email->sent_at->format('d/m/Y à H:i') : ($email->created_at->format('d/m/Y à H:i')) }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.emails.sent.show', $email) }}" class="btn btn-sm btn-light" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun email envoyé trouvé</p>
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

@push('styles')
<style>
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
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }
    
    .table-responsive::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
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
    
    /* Supprimer les scrollbars des conteneurs sur mobile */
    .admin-table {
        overflow: visible !important;
    }
    
    .admin-panel__body {
        overflow: visible !important;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }
    
    .table-responsive::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }
}
</style>
@endpush

