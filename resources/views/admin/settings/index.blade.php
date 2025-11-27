@extends('layouts.admin')

@section('title', 'Paramètres - Herime Academie')
@section('admin-title', 'Paramètres du site')
@section('admin-subtitle', 'Configurez les paramètres généraux de la plateforme')

@section('admin-content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body admin-panel__body--padded">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm admin-form-card h-100">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4 d-flex align-items-center gap-2">
                                <span class="admin-nav__icon" style="background: rgba(251, 191, 36, 0.15); color: #b45309;">
                                    <i class="fas fa-coins"></i>
                                </span>
                                Configuration de la devise
                            </h5>

                            <form method="POST" action="{{ route('admin.settings.update') }}" class="admin-form-grid gap-4">
                                @csrf

                                <div>
                                    <label for="base_currency" class="form-label fw-semibold">
                                        Devise de base du site
                                    </label>
                                    <select name="base_currency" id="base_currency" class="form-select form-select-lg" required>
                                        @foreach($currencies as $code => $label)
                                            <option value="{{ $code }}" {{ $baseCurrency === $code ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Cette devise sera utilisée pour afficher tous les prix sur le site (panier, cours, commandes).
                                    </div>
                                </div>

                                <div>
                                    <label for="external_instructor_commission_percentage" class="form-label fw-semibold">
                                        Pourcentage de commission (formateurs externes)
                                    </label>
                                    <div class="input-group">
                                        <input type="number" 
                                               name="external_instructor_commission_percentage" 
                                               id="external_instructor_commission_percentage" 
                                               class="form-control form-control-lg" 
                                               value="{{ \App\Models\Setting::get('external_instructor_commission_percentage', 20) }}"
                                               min="0" 
                                               max="100" 
                                               step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Pourcentage retenu sur les paiements aux formateurs externes. Le reste sera envoyé via pawaPay.
                                    </div>
                                </div>

                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    <strong>Note :</strong> Les prix sont désormais stockés et affichés dans la devise de base du site. Lors du paiement, le montant peut être débité dans une autre devise selon l’opérateur sélectionné, avec conversion appliquée depuis la devise de base.
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                    </button>
                                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Annuler
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm admin-form-card h-100">
                        <div class="card-body p-4">
                            <h6 class="card-title mb-3 d-flex align-items-center gap-2">
                                <span class="admin-nav__icon" style="background: rgba(59, 130, 246, 0.18); color: #1d4ed8;">
                                    <i class="fas fa-question-circle"></i>
                                </span>
                                Information
                            </h6>
                            <p class="text-muted small mb-3">
                                La devise de base détermine comment les montants sont affichés sur toute la plateforme.
                            </p>
                            <ul class="list-unstyled small mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>Cours
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>Panier
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>Commandes
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-check text-success me-2"></i>Tableaux de bord
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
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

