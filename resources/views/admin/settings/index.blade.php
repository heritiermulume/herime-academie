@extends('layouts.admin')

@section('title', 'Paramètres - Herime Academie')

@section('admin-content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="card border-0 shadow mb-4" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); border-radius: 15px 15px 0 0;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-sm" title="Tableau de bord">
                        <i class="fas fa-tachometer-alt"></i>
                    </a>
                    <div>
                        <h4 class="mb-1">
                            <i class="fas fa-cog me-2"></i>Paramètres du site
                        </h4>
                        <p class="mb-0 text-description small opacity-75">Configurez les paramètres généraux de la plateforme</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-coins me-2 text-warning"></i>Configuration de la devise
                    </h5>

                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf

                        <div class="mb-4">
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
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Cette devise sera utilisée pour afficher tous les prix sur le site (panier, cours, commandes).
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Note :</strong> Les prix sont désormais stockés et affichés dans la devise de base du site (configurable ci-dessus). Lors du paiement, le montant peut être débité dans une autre devise selon l’opérateur sélectionné, avec conversion appliquée depuis la devise de base.
                        </div>

                        <div class="d-flex gap-2">
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
            <div class="card border-0 shadow" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-question-circle me-2 text-info"></i>Information
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
@endsection

