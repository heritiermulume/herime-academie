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

    <section class="admin-panel">
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

