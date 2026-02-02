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

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        // Tabs: si on revient d'une action Meta (open=...), on ouvre l'onglet Meta automatiquement
        $activeTab = (string) request('tab', '');
        if ($activeTab === '' && request()->filled('open')) {
            $activeTab = 'meta';
        }
        if ($activeTab === '') {
            $activeTab = 'currency';
        }
    @endphp

    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body admin-panel__body--padded">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="nav nav-tabs flex-nowrap overflow-auto"
                     id="adminSettingsTabs"
                     role="tablist"
                     style="max-width: 100%;
                         --bs-nav-link-color: var(--primary-color, #003366);
                         --bs-nav-link-hover-color: var(--primary-color, #003366);
                         --bs-nav-tabs-link-active-bg: var(--primary-color, #003366);
                         --bs-nav-tabs-link-active-color: #ffffff;
                         --bs-nav-tabs-link-active-border-color: var(--primary-color, #003366);">
                    <button class="nav-link text-nowrap {{ $activeTab === 'currency' ? 'active' : '' }}"
                            id="tab-currency-btn"
                            data-bs-toggle="tab"
                            data-bs-target="#tab-currency"
                            type="button"
                            role="tab"
                            aria-controls="tab-currency"
                            aria-selected="{{ $activeTab === 'currency' ? 'true' : 'false' }}">
                        <i class="fas fa-coins me-2"></i>Devise
                    </button>
                    <button class="nav-link text-nowrap {{ $activeTab === 'wallet' ? 'active' : '' }}"
                            id="tab-wallet-btn"
                            data-bs-toggle="tab"
                            data-bs-target="#tab-wallet"
                            type="button"
                            role="tab"
                            aria-controls="tab-wallet"
                            aria-selected="{{ $activeTab === 'wallet' ? 'true' : 'false' }}">
                        <i class="fas fa-wallet me-2"></i>Wallet Ambassadeurs
                    </button>
                    <button class="nav-link text-nowrap {{ $activeTab === 'meta' ? 'active' : '' }}"
                            id="tab-meta-btn"
                            data-bs-toggle="tab"
                            data-bs-target="#tab-meta"
                            type="button"
                            role="tab"
                            aria-controls="tab-meta"
                            aria-selected="{{ $activeTab === 'meta' ? 'true' : 'false' }}">
                        <i class="fab fa-facebook me-2"></i>Meta Pixel & Events
                    </button>
                </div>
            </div>

            {{-- Form unique pour Devise + Wallet (validation controller: base_currency requis, wallet toujours sauvegardé) --}}
            <form id="admin-settings-general-form" method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
            </form>

            <div class="tab-content" id="adminSettingsTabContent">
                {{-- TAB: Devise --}}
                <div class="tab-pane fade {{ $activeTab === 'currency' ? 'show active' : '' }}" id="tab-currency" role="tabpanel" aria-labelledby="tab-currency-btn" tabindex="0">
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

                                    <div class="admin-form-grid gap-4">
                                        <div>
                                            <label for="base_currency" class="form-label fw-semibold">
                                                Devise de base du site
                                            </label>
                                            <select name="base_currency"
                                                    id="base_currency"
                                                    class="form-select form-select-lg"
                                                    required
                                                    form="admin-settings-general-form">
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
                                                Pourcentage de commission (prestataires externes)
                                            </label>
                                            <div class="input-group">
                                                <input type="number"
                                                       name="external_instructor_commission_percentage"
                                                       id="external_instructor_commission_percentage"
                                                       class="form-control form-control-lg"
                                                       value="{{ \App\Models\Setting::get('external_instructor_commission_percentage', 20) }}"
                                                       min="0"
                                                       max="100"
                                                       step="0.01"
                                                       form="admin-settings-general-form">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-text mt-2">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Pourcentage retenu sur les paiements aux prestataires externes. Le reste sera envoyé via Moneroo.
                                            </div>
                                        </div>

                                        <div>
                                            <label for="ambassador_commission_rate" class="form-label fw-semibold">
                                                Pourcentage de commission (ambassadeurs)
                                            </label>
                                            <div class="input-group">
                                                <input type="number"
                                                       name="ambassador_commission_rate"
                                                       id="ambassador_commission_rate"
                                                       class="form-control form-control-lg"
                                                       value="{{ \App\Models\Setting::get('ambassador_commission_rate', 10) }}"
                                                       min="0"
                                                       max="100"
                                                       step="0.01"
                                                       form="admin-settings-general-form">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-text mt-2">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Pourcentage de commission versé aux ambassadeurs sur chaque vente réalisée avec leur code promo.
                                            </div>
                                        </div>

                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-lightbulb me-2"></i>
                                            <strong>Note :</strong> Les prix sont désormais stockés et affichés dans la devise de base du site. Lors du paiement, le montant peut être débité dans une autre devise selon l’opérateur sélectionné, avec conversion appliquée depuis la devise de base.
                                        </div>

                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" form="admin-settings-general-form">
                                                <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                            </button>
                                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-2"></i>Annuler
                                            </a>
                                        </div>
                                    </div>
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

                {{-- TAB: Wallet --}}
                <div class="tab-pane fade {{ $activeTab === 'wallet' ? 'show active' : '' }}" id="tab-wallet" role="tabpanel" aria-labelledby="tab-wallet-btn" tabindex="0">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm admin-form-card h-100">
                                <div class="card-body p-4">
                                    <h5 class="card-title mb-4 d-flex align-items-center gap-2">
                                        <span class="admin-nav__icon" style="background: rgba(139, 92, 246, 0.15); color: #6d28d9;">
                                            <i class="fas fa-wallet"></i>
                                        </span>
                                        Configuration du Wallet Ambassadeurs
                                    </h5>

                                    <div class="admin-form-grid gap-4">
                                        <div>
                                            <label for="wallet_holding_period_days" class="form-label fw-semibold">
                                                Période de blocage (en jours)
                                            </label>
                                            <div class="input-group">
                                                <input type="number"
                                                       name="wallet_holding_period_days"
                                                       id="wallet_holding_period_days"
                                                       class="form-control form-control-lg"
                                                       value="{{ $walletSettings['holding_period_days'] }}"
                                                       min="0"
                                                       max="365"
                                                       step="1"
                                                       form="admin-settings-general-form">
                                                <span class="input-group-text">jours</span>
                                            </div>
                                            <div class="form-text mt-2">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Durée pendant laquelle les fonds sont bloqués avant d'être disponibles au retrait. Recommandé : 7 jours.
                                            </div>
                                        </div>

                                        <div>
                                            <label for="wallet_minimum_payout_amount" class="form-label fw-semibold">
                                                Montant minimum de retrait
                                            </label>
                                            <div class="input-group">
                                                <input type="number"
                                                       name="wallet_minimum_payout_amount"
                                                       id="wallet_minimum_payout_amount"
                                                       class="form-control form-control-lg"
                                                       value="{{ $walletSettings['minimum_payout_amount'] }}"
                                                       min="0"
                                                       step="0.01"
                                                       form="admin-settings-general-form">
                                                <span class="input-group-text">{{ $baseCurrency }}</span>
                                            </div>
                                            <div class="form-text mt-2">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Montant minimum que les ambassadeurs doivent avoir pour effectuer un retrait.
                                            </div>
                                        </div>

                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   role="switch"
                                                   name="wallet_auto_release_enabled"
                                                   id="wallet_auto_release_enabled"
                                                   value="on"
                                                   {{ $walletSettings['auto_release_enabled'] ? 'checked' : '' }}
                                                   form="admin-settings-general-form">
                                            <label class="form-check-label fw-semibold" for="wallet_auto_release_enabled">
                                                Activer la libération automatique des fonds
                                            </label>
                                            <div class="form-text mt-2">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Si activé, les fonds bloqués seront automatiquement libérés après la période de blocage (quotidiennement à 2h du matin).
                                            </div>
                                        </div>

                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Important :</strong> La période de blocage protège contre les litiges et remboursements. Réduire cette période peut augmenter les risques.
                                        </div>

                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" form="admin-settings-general-form">
                                                <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                            </button>
                                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-2"></i>Annuler
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm admin-form-card h-100">
                                <div class="card-body p-4">
                                    <h6 class="card-title mb-3 d-flex align-items-center gap-2">
                                        <span class="admin-nav__icon" style="background: rgba(139, 92, 246, 0.15); color: #6d28d9;">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                        Bonnes pratiques
                                    </h6>
                                    <ul class="list-unstyled small mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>Gardez une période de blocage pour limiter les risques de litige.
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>Fixez un minimum de retrait pour limiter les micro-paiements.
                                        </li>
                                        <li class="mb-0">
                                            <i class="fas fa-check text-success me-2"></i>Activez la libération auto uniquement si votre process de remboursement est stable.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB: Meta --}}
                <div class="tab-pane fade {{ $activeTab === 'meta' ? 'show active' : '' }}" id="tab-meta" role="tabpanel" aria-labelledby="tab-meta-btn" tabindex="0">
                    <div id="meta-settings-mount"></div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Monte la section Meta (existante) dans l'onglet Meta, sans dupliquer le markup
        (function () {
            function mountMetaSettings() {
                const mount = document.getElementById('meta-settings-mount');
                const section = document.getElementById('meta-settings-section');
                if (!mount || !section) return;
                section.classList.remove('d-none');
                section.classList.remove('mt-4');
                mount.appendChild(section);
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', mountMetaSettings);
            } else {
                mountMetaSettings();
            }
        })();
    </script>

    {{-- Meta (Facebook) Pixel & Events --}}
    <section id="meta-settings-section" class="admin-panel mt-4 d-none">
        <div class="admin-panel__body admin-panel__body--padded">
            <div class="card border-0 shadow-sm admin-form-card">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4 d-flex align-items-center gap-2">
                        <span class="admin-nav__icon" style="background: rgba(16, 185, 129, 0.15); color: #047857;">
                            <i class="fab fa-facebook"></i>
                        </span>
                        Meta Pixel & Events (dynamique)
                    </h5>

                    <div class="alert alert-info">
                        <strong>Objectif :</strong> configuration minimale du Pixel Meta.
                        Le <code>PageView</code> est envoyé automatiquement (snippet officiel), et les <strong>Triggers</strong> servent aux autres événements (<code>Purchase</code>, <code>Lead</code>…) au chargement, au clic ou au submit.
                    </div>

                    <datalist id="meta_event_names">
                        {{-- Triggers: uniquement les événements créés en BDD (section "Événements") --}}
                        @foreach(($metaEvents ?? collect()) as $ev)
                            <option value="{{ $ev->event_name }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="meta_standard_event_names">
                        @foreach(($metaStandardEventNameOptions ?? []) as $en)
                            <option value="{{ $en }}"></option>
                        @endforeach
                    </datalist>

                    {{-- Global toggle --}}
                    <form method="POST" action="{{ route('admin.settings.update') }}" class="mb-4">
                        @csrf
                        <input type="hidden" name="meta_action" value="meta_update_global">

                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   role="switch"
                                   name="meta_tracking_enabled"
                                   id="meta_tracking_enabled"
                                   value="on"
                                   {{ !empty($metaTrackingEnabled) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="meta_tracking_enabled">
                                Activer Meta Pixel globalement
                            </label>
                            <div class="form-text">
                                Quand activé, le script Meta se charge automatiquement sur le front (hors admin) et les triggers actifs peuvent envoyer des événements.
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Enregistrer Meta
                            </button>
                        </div>
                    </form>

                    <div class="accordion" id="metaAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="metaPixelsHeading">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#metaPixelsCollapse" aria-expanded="true" aria-controls="metaPixelsCollapse">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="fas fa-bullseye"></i>
                                        Pixels
                                        <span class="badge text-bg-light ms-1">{{ ($metaPixels ?? collect())->count() }}</span>
                                    </span>
                                </button>
                            </h2>
                            <div id="metaPixelsCollapse" class="accordion-collapse collapse show" aria-labelledby="metaPixelsHeading" data-bs-parent="#metaAccordion">
                                <div class="accordion-body">
                                    @if(isset($metaPixels) && $metaPixels->count())
                                        <div class="admin-table mb-3">
                                            <div class="table-responsive">
                                            <table class="table align-middle">
                                                <thead>
                                                <tr>
                                                    <th>Nom</th>
                                                    <th>Pixel ID</th>
                                                    <th>Actif</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($metaPixels as $p)
                                                    <tr>
                                                        <td class="fw-semibold">{{ $p->name ?: '—' }}</td>
                                                        <td><code>{{ $p->pixel_id }}</code></td>
                                                        <td>
                                                            <span class="badge {{ $p->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                                {{ $p->is_active ? 'ON' : 'OFF' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end">
                                                            <details class="d-inline-block me-2 text-start meta-flyout">
                                                                <summary class="btn btn-sm btn-outline-primary" title="Éditer">
                                                                    <i class="fas fa-pen"></i>
                                                                    <span class="visually-hidden">Éditer</span>
                                                                </summary>
                                                                <div class="border rounded p-3 mt-2 bg-white" style="min-width: 320px; max-width: 520px;">
                                                                    <form method="POST" action="{{ route('admin.settings.update') }}" class="row g-2">
                                                                        @csrf
                                                                        <input type="hidden" name="meta_action" value="meta_pixel_update">
                                                                        <input type="hidden" name="meta_pixel_id" value="{{ $p->id }}">
                                                                        {{-- (form inchangé) --}}
                                                                        <div class="col-12">
                                                                            <label class="form-label fw-semibold mb-1">Pixel ID</label>
                                                                            <input type="text" name="pixel_id" class="form-control form-control-sm" value="{{ $p->pixel_id }}" required>
                                                                            <div class="form-text">ID donné par Meta (Events Manager). Exemple: <code>1234567890</code>.</div>
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <label class="form-label fw-semibold mb-1">Nom (optionnel)</label>
                                                                            <input type="text" name="pixel_name" class="form-control form-control-sm" value="{{ $p->name }}" placeholder="Pixel principal">
                                                                            <div class="form-text">Pour vous repérer (n’impacte pas le tracking).</div>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <label class="form-label fw-semibold mb-1">Actif</label>
                                                                            <div class="form-check form-switch">
                                                                                <input class="form-check-input" type="checkbox" role="switch" name="pixel_is_active" value="on" {{ $p->is_active ? 'checked' : '' }}>
                                                                                <label class="form-check-label small">ON</label>
                                                                            </div>
                                                                            <div class="form-text">Si OFF, le pixel ne se charge pas.</div>
                                                                        </div>
                                                                        <div class="col-12 d-flex gap-2">
                                                                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </details>
                                                            <form method="POST" action="{{ route('admin.settings.update') }}" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="meta_action" value="meta_pixel_delete">
                                                                <input type="hidden" name="meta_pixel_id" value="{{ $p->id }}">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce pixel ?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-muted small mb-3">Aucun pixel configuré.</p>
                                    @endif

                                    <div class="border rounded-3 p-3 bg-body-tertiary">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                            <div>
                                                <div class="fw-semibold">Ajouter un pixel</div>
                                                <div class="text-muted small">Champs essentiels seulement (Pixel ID, nom optionnel, actif).</div>
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('admin.settings.update') }}" class="row g-3">
                                            @csrf
                                            <input type="hidden" name="meta_action" value="meta_pixel_create">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nom (optionnel)</label>
                            <input type="text" name="pixel_name" class="form-control" placeholder="Pixel principal">
                            <div class="form-text">Nom interne (pour vous repérer).</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pixel ID</label>
                            <input type="text" name="pixel_id" class="form-control" placeholder="1234567890" required>
                            <div class="form-text">ID donné par Meta (Events Manager).</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Actif</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="pixel_is_active" value="on" checked>
                                <label class="form-check-label">ON</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Ajouter le pixel
                            </button>
                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="metaEventsHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#metaEventsCollapse" aria-expanded="false" aria-controls="metaEventsCollapse">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="fas fa-bolt"></i>
                                        Événements
                                        <span class="badge text-bg-light ms-1">{{ ($metaEvents ?? collect())->count() }}</span>
                                    </span>
                                </button>
                            </h2>
                            <div id="metaEventsCollapse" class="accordion-collapse collapse" aria-labelledby="metaEventsHeading" data-bs-parent="#metaAccordion">
                                <div class="accordion-body">

                    @if(isset($metaEvents) && $metaEvents->count())
                        <div class="admin-table mb-3">
                            <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Actif</th>
                                    <th class="d-none d-lg-table-cell">Payload défaut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($metaEvents as $e)
                                    <tr>
                                        <td class="fw-semibold"><code>{{ $e->event_name }}</code></td>
                                        <td>
                                            <span class="badge {{ $e->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $e->is_active ? 'ON' : 'OFF' }}
                                            </span>
                                        </td>
                                        <td class="small text-muted d-none d-lg-table-cell">
                                            <code>{{ json_encode($e->default_payload ?? []) }}</code>
                                        </td>
                                        <td class="text-end">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success me-2 meta-quick-create-trigger"
                                                    data-event-name="{{ $e->event_name }}">
                                                <i class="fas fa-plus me-1"></i>Trigger
                                            </button>
                                            <details class="d-inline-block me-2 text-start meta-flyout">
                                                <summary class="btn btn-sm btn-outline-primary" title="Éditer">
                                                    <i class="fas fa-pen"></i>
                                                    <span class="visually-hidden">Éditer</span>
                                                </summary>
                                                <div class="border rounded p-3 mt-2 bg-white" style="min-width: 320px; max-width: 520px;">
                                                    <form method="POST" action="{{ route('admin.settings.update') }}" class="row g-2">
                                                        @csrf
                                                        <input type="hidden" name="meta_action" value="meta_event_update">
                                                        <input type="hidden" name="meta_event_id" value="{{ $e->id }}">

                                                        <div class="col-12">
                                                            <label class="form-label fw-semibold mb-1">Nom d’événement</label>
                                                            <input type="text" name="event_name" list="meta_standard_event_names" class="form-control form-control-sm meta-validate-event-name" value="{{ $e->event_name }}" required data-validate-datalist="meta_standard_event_names" data-validate-mode="event_form">
                                                            <div class="invalid-feedback">Choisissez un événement Meta standard (ou décochez “Standard” pour un custom).</div>
                                                            <div class="form-text">Meta recommande d’utiliser un événement standard quand c’est possible.</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" role="switch" name="event_is_standard" value="on" {{ $e->is_standard ? 'checked' : '' }}>
                                                                <label class="form-check-label small">Standard</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" role="switch" name="event_is_active" value="on" {{ $e->is_active ? 'checked' : '' }}>
                                                                <label class="form-check-label small">Actif</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <details>
                                                                <summary class="fw-semibold">Options avancées (payload)</summary>
                                                                <div class="mt-2">
                                                                    <label class="form-label fw-semibold mb-1">Payload par défaut (JSON)</label>
                                                                    <textarea name="event_default_payload" class="form-control form-control-sm" rows="2">{{ json_encode($e->default_payload ?? []) }}</textarea>
                                                                    <div class="form-text">Ex: <code>{"currency":"USD"}</code>. Ce payload sera fusionné avec celui du trigger.</div>
                                                                </div>
                                                                <div class="mt-2">
                                                                    <label class="form-label fw-semibold mb-1">Description (optionnel)</label>
                                                                    <input type="text" name="event_description" class="form-control form-control-sm" value="{{ $e->description }}">
                                                                </div>
                                                            </details>
                                                        </div>
                                                        <div class="col-12">
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-save me-1"></i>Enregistrer
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </details>
                                            <form method="POST" action="{{ route('admin.settings.update') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="meta_action" value="meta_event_delete">
                                                <input type="hidden" name="meta_event_id" value="{{ $e->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Supprimer cet événement ? (supprime aussi ses triggers)')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>
                    @else
                        <p class="text-muted small mb-3">Aucun événement configuré.</p>
                    @endif

                    <div class="border rounded-3 p-3 bg-body-tertiary mt-3">
                        <div class="fw-semibold mb-1">Ajouter un événement</div>
                        <div class="text-muted small mb-3">Utilisez un événement standard Meta quand c’est possible. Le payload est optionnel.</div>
                        <form method="POST" action="{{ route('admin.settings.update') }}" class="row g-3">
                        @csrf
                        <input type="hidden" name="meta_action" value="meta_event_create">

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nom d’événement</label>
                            <input type="text" name="event_name" list="meta_standard_event_names" class="form-control meta-validate-event-name" placeholder="PageView" required data-validate-datalist="meta_standard_event_names" data-validate-mode="event_form">
                            <div class="invalid-feedback">Choisissez un événement Meta standard (ou décochez “Standard” pour un custom).</div>
                            <div class="form-text">Ex: <code>Purchase</code>, <code>Lead</code>, <code>CompleteRegistration</code>.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Standard</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="event_is_standard" value="on" checked>
                                <label class="form-check-label">Oui</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Actif</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="event_is_active" value="on" checked>
                                <label class="form-check-label">ON</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <details>
                                <summary class="fw-semibold">Options avancées (payload)</summary>
                                <div class="mt-2">
                                    <label class="form-label fw-semibold">Payload par défaut (JSON)</label>
                                    <textarea name="event_default_payload" class="form-control" rows="2" placeholder='{"currency":"USD"}'></textarea>
                                    <div class="form-text">Optionnel. Sera fusionné avec le payload du trigger.</div>
                                </div>
                                <div class="mt-2">
                                    <label class="form-label fw-semibold">Description (optionnel)</label>
                                    <input type="text" name="event_description" class="form-control" placeholder="Optionnel">
                                </div>
                            </details>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Ajouter l’événement
                            </button>
                        </div>
                        </form>
                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="metaTriggersHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#metaTriggersCollapse" aria-expanded="false" aria-controls="metaTriggersCollapse">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="fas fa-diagram-project"></i>
                                        Triggers
                                        <span class="badge text-bg-light ms-1">{{ ($metaTriggers ?? collect())->count() }}</span>
                                    </span>
                                </button>
                            </h2>
                            <div id="metaTriggersCollapse" class="accordion-collapse collapse" aria-labelledby="metaTriggersHeading" data-bs-parent="#metaAccordion">
                                <div class="accordion-body">

                    @if(isset($metaTriggers) && $metaTriggers->count())
                        <div class="admin-table mb-3">
                            <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Type</th>
                                    <th class="d-none d-xl-table-cell">Selector</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($metaTriggers as $t)
                                    <tr>
                                        <td><code>{{ $t->event?->event_name }}</code></td>
                                        <td><span class="badge bg-dark">{{ $t->trigger_type }}</span></td>
                                        <td class="small text-muted d-none d-xl-table-cell">{{ $t->css_selector ?: '—' }}</td>
                                        <td class="text-end">
                                            <details class="d-inline-block me-2 text-start meta-flyout">
                                                <summary class="btn btn-sm btn-outline-primary" title="Éditer">
                                                    <i class="fas fa-pen"></i>
                                                    <span class="visually-hidden">Éditer</span>
                                                </summary>
                                                <div class="border rounded p-3 mt-2 bg-white" style="min-width: 320px; max-width: 620px;">
                                                    <form method="POST" action="{{ route('admin.settings.update') }}" class="row g-2">
                                                        @csrf
                                                        <input type="hidden" name="meta_action" value="meta_trigger_update">
                                                        <input type="hidden" name="meta_trigger_id" value="{{ $t->id }}">

                                                        <div class="col-12">
                                                            <label class="form-label fw-semibold mb-1">Événement</label>
                                                            <select name="event_name" class="form-select form-select-sm" required>
                                                                <option value="">— Choisir un événement —</option>
                                                                @foreach(($metaEvents ?? collect()) as $ev)
                                                                    <option value="{{ $ev->event_name }}" {{ ($t->event?->event_name === $ev->event_name) ? 'selected' : '' }}>
                                                                        {{ $ev->event_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="form-text">Sélectionnez un événement existant (créé dans “Événements”).</div>
                                                        </div>
                                                        <select name="trigger_type" class="form-select form-select-sm meta-trigger-type d-none" required aria-hidden="true" tabindex="-1">
                                                            <option value="page_load" {{ $t->trigger_type === 'page_load' ? 'selected' : '' }}>page_load</option>
                                                            <option value="click" {{ $t->trigger_type === 'click' ? 'selected' : '' }}>click</option>
                                                            <option value="form_submit" {{ $t->trigger_type === 'form_submit' ? 'selected' : '' }}>form_submit</option>
                                                        </select>
                                                        <div class="col-12">
                                                            <details>
                                                                <summary class="fw-semibold">Options avancées (page & payload)</summary>
                                                                <div class="row g-2 mt-2">
                                                                    <div class="col-12">
                                                                        <label class="form-label fw-semibold mb-1">Page (path pattern)</label>
                                                                        <select name="match_path_pattern" class="form-select form-select-sm meta-page-select">
                                                                            <option value="__all__" {{ empty($t->match_path_pattern) ? 'selected' : '' }}>— Toutes les pages —</option>
                                                                            @foreach(($metaPageOptions ?? []) as $p)
                                                                                <option value="{{ $p['path'] }}"
                                                                                    {{ (trim((string)($t->match_path_pattern ?? '')) === trim((string)$p['path']) || ltrim((string)($t->match_path_pattern ?? ''), '/') === ltrim((string)$p['path'], '/')) ? 'selected' : '' }}>
                                                                                    {{ $p['label'] }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        <div class="form-text">
                                                                            Optionnel. Vide = toutes les pages. Pour éviter les erreurs de saisie, choisissez une page dans la liste.
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-12 meta-trigger-type-fields meta-css-selector-field" data-show-when="click,form_submit">
                                                                        <label class="form-label fw-semibold mb-1">Sélecteur CSS</label>
                                                                        <select name="css_selector" class="form-select form-select-sm meta-element-select">
                                                                            @if(!empty($t->css_selector))
                                                                                <option value="{{ $t->css_selector }}" selected>Actuel: {{ $t->css_selector }}</option>
                                                                            @endif
                                                                            <option value="" {{ empty($t->css_selector) ? 'selected' : '' }}>— Choisir un élément (sélectionnez d’abord une page) —</option>
                                                                        </select>
                                                                        <div class="form-text">Obligatoire pour <code>click</code> et <code>form_submit</code>. La liste est générée à partir de la page choisie.</div>
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <label class="form-label fw-semibold mb-1">Pixels ciblés</label>
                                                                        <select class="form-select form-select-sm" multiple data-csv-name="trigger_pixel_ids">
                                                                            @foreach(($metaPixels ?? collect()) as $mp)
                                                                                @if(!empty($mp->pixel_id))
                                                                                    <option value="{{ $mp->pixel_id }}" {{ is_array($t->pixel_ids) && in_array($mp->pixel_id, $t->pixel_ids, true) ? 'selected' : '' }}>{{ $mp->pixel_id }}</option>
                                                                                @endif
                                                                            @endforeach
                                                                        </select>
                                                                        <input type="hidden" name="trigger_pixel_ids" value="{{ is_array($t->pixel_ids) ? implode(', ', $t->pixel_ids) : '' }}">
                                                                        <div class="form-text">Vide = tous les pixels actifs.</div>
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <label class="form-label fw-semibold mb-1">Payload (JSON)</label>
                                                                        <textarea name="trigger_payload" class="form-control form-control-sm" rows="2">{{ json_encode($t->payload ?? []) }}</textarea>
                                                                        <div class="form-text">Optionnel. Fusionné avec le payload par défaut de l’événement.</div>
                                                                    </div>
                                                                </div>
                                                            </details>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" role="switch" name="trigger_is_active" value="on" {{ $t->is_active ? 'checked' : '' }}>
                                                                <label class="form-check-label small">Actif</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" role="switch" name="once_per_page" value="on" {{ $t->once_per_page ? 'checked' : '' }}>
                                                                <label class="form-check-label small">Une fois par page</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-save me-1"></i>Enregistrer
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </details>
                                            <form method="POST" action="{{ route('admin.settings.update') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="meta_action" value="meta_trigger_delete">
                                                <input type="hidden" name="meta_trigger_id" value="{{ $t->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Supprimer ce trigger ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>
                    @else
                        <p class="text-muted small mb-3">Aucun trigger configuré.</p>
                    @endif

                    <div class="border rounded-3 p-3 bg-body-tertiary mt-3">
                        <div class="fw-semibold mb-1">Créer un trigger</div>
                        <div class="text-muted small mb-3">Choisissez un événement, un type, et seulement si nécessaire un sélecteur CSS.</div>

                        <form method="POST" action="{{ route('admin.settings.update') }}" class="row g-3 meta-trigger-create-form">
                        @csrf
                        <input type="hidden" name="meta_action" value="meta_trigger_create">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Événement</label>
                            <select name="event_name" class="form-select" id="metaTriggerEventName" required>
                                <option value="" selected>— Choisir un événement —</option>
                                @foreach(($metaEvents ?? collect()) as $ev)
                                    <option value="{{ $ev->event_name }}">{{ $ev->event_name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Choisissez un événement déjà créé dans la section <strong>“Événements”</strong>.
                                (Pour ajouter un nouvel événement, créez-le d’abord dans “Événements”.)
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Déclencheur</label>
                            <select class="form-select meta-trigger-preset">
                                <option value="" selected>Aucun</option>
                                <option value="page_load">Page load (success page)</option>
                                <option value="click">Click sur bouton</option>
                                <option value="form_submit">Form submit</option>
                            </select>
                            <div class="form-text">Pré-remplit le type + les placeholders (sans imposer un événement).</div>
                        </div>
                        <select name="trigger_type" class="form-select meta-trigger-type d-none" required aria-hidden="true" tabindex="-1">
                            <option value="" selected>—</option>
                            <option value="page_load">page_load</option>
                            <option value="click">click</option>
                            <option value="form_submit">form_submit</option>
                        </select>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Options</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="trigger_is_active" value="on" checked>
                                    <label class="form-check-label">Actif</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="once_per_page" value="on" checked>
                                    <label class="form-check-label">Une fois par page</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <details>
                                <summary class="fw-semibold">Options avancées (page & payload)</summary>
                                <div class="row g-3 mt-2">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Page (path pattern)</label>
                                        <select name="match_path_pattern" class="form-select meta-page-select">
                                            <option value="__all__" selected>— Toutes les pages —</option>
                                            @foreach(($metaPageOptions ?? []) as $p)
                                                <option value="{{ $p['path'] }}">{{ $p['label'] }}</option>
                                            @endforeach
                                        </select>
                                        <div class="form-text">
                                            Optionnel. Vide = toutes les pages. Pour éviter les erreurs de saisie, choisissez une page dans la liste.
                                        </div>
                                    </div>
                                    <div class="col-12 meta-trigger-type-fields meta-css-selector-field" data-show-when="click,form_submit">
                                        <label class="form-label fw-semibold">Sélecteur CSS</label>
                                        <select name="css_selector" class="form-select meta-element-select">
                                            <option value="" selected>— Choisir un élément (sélectionnez d’abord une page) —</option>
                                        </select>
                                        <div class="form-text">Obligatoire pour <code>click</code> et <code>form_submit</code>. La liste est générée à partir de la page choisie.</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Pixels ciblés</label>
                                        <select class="form-select" multiple data-csv-name="trigger_pixel_ids">
                                            @foreach(($metaPixels ?? collect()) as $mp)
                                                @if(!empty($mp->pixel_id))
                                                    <option value="{{ $mp->pixel_id }}">{{ $mp->pixel_id }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="trigger_pixel_ids" value="">
                                        <div class="form-text">Vide = tous les pixels actifs.</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Payload (JSON)</label>
                                        <textarea name="trigger_payload" class="form-control" rows="2" placeholder='{"value":10,"currency":"USD"}'></textarea>
                                        <div class="form-text">Optionnel. Fusionné avec le payload par défaut de l’événement. Vous pouvez aussi surcharger via HTML: <code>data-meta-payload='{"value":10}'</code>.</div>
                                    </div>
                                </div>
                            </details>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Ajouter le trigger
                            </button>
                        </div>
                        </form>
                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    (function () {
        function splitCsv(raw) {
            if (!raw) return [];
            return String(raw)
                .split(/[,\n\r]+/g)
                .map(s => s.trim())
                .filter(Boolean);
        }

        function unique(arr) {
            return Array.from(new Set(arr));
        }

        function updateCsv(selectEl) {
            const name = selectEl?.dataset?.csvName;
            if (!name || !selectEl.form) return;
            const hidden = selectEl.form.querySelector('input[name="' + name + '"]');
            if (!hidden) return;
            const values = Array.from(selectEl.selectedOptions || []).map(o => o.value).filter(Boolean);
            hidden.value = values.join(', ');
        }

        // Multi-select helper UI: show selected values + allow easy unselect/clear (especially on mobile)
        function initMultiSelectHelper(selectEl) {
            if (!selectEl || selectEl.dataset.metaMultiSelectInit === '1') return;
            if (!selectEl.multiple) return;
            if (!selectEl.dataset.csvName) return;
            selectEl.dataset.metaMultiSelectInit = '1';

            const wrapper = document.createElement('div');
            wrapper.className = 'meta-multiselect mt-2';
            wrapper.innerHTML =
                '<div class="d-flex align-items-center justify-content-between gap-2">' +
                '  <div class="small text-muted meta-multiselect__summary"></div>' +
                '  <button type="button" class="btn btn-sm btn-outline-secondary meta-multiselect__clear">Tout effacer</button>' +
                '</div>' +
                '<div class="meta-multiselect__tags d-flex flex-wrap gap-2 mt-2"></div>';

            selectEl.insertAdjacentElement('afterend', wrapper);

            const summary = wrapper.querySelector('.meta-multiselect__summary');
            const tags = wrapper.querySelector('.meta-multiselect__tags');
            const clearBtn = wrapper.querySelector('.meta-multiselect__clear');

            function render() {
                const selected = Array.from(selectEl.selectedOptions || []).map(o => o.value).filter(Boolean);
                summary.textContent = selected.length ? ('Sélectionnés: ' + selected.join(', ')) : 'Aucun sélectionné';
                tags.innerHTML = '';
                selected.forEach(v => {
                    const chip = document.createElement('span');
                    chip.className = 'badge rounded-pill text-bg-secondary d-inline-flex align-items-center gap-2';
                    chip.setAttribute('data-ms-value', v);
                    chip.innerHTML =
                        '<span>' + String(v).replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>' +
                        '<button type="button" class="btn btn-sm btn-light py-0 px-2 meta-multiselect__remove" aria-label="Remove">×</button>';
                    tags.appendChild(chip);
                });
            }

            wrapper.addEventListener('click', function (e) {
                const removeBtn = e.target && e.target.closest ? e.target.closest('.meta-multiselect__remove') : null;
                if (removeBtn) {
                    const chip = removeBtn.closest('[data-ms-value]');
                    const v = chip ? chip.getAttribute('data-ms-value') : null;
                    if (v) {
                        Array.from(selectEl.options || []).forEach(opt => {
                            if (opt.value === v) opt.selected = false;
                        });
                        updateCsv(selectEl);
                        render();
                    }
                    return;
                }
            });

            clearBtn?.addEventListener('click', function () {
                Array.from(selectEl.options || []).forEach(opt => { opt.selected = false; });
                updateCsv(selectEl);
                render();
            });

            selectEl.addEventListener('change', function () {
                updateCsv(selectEl);
                render();
            });

            // initial render
            render();
        }

        document.addEventListener('change', function (e) {
            const el = e.target;
            if (el && el.matches && el.matches('select[data-csv-name]')) {
                updateCsv(el);
            }
        });

        function renderTags(root, values) {
            const tagsWrap = root.querySelector('.meta-tag-input__tags');
            const hidden = root.querySelector('input[type="hidden"][name="' + root.dataset.tagCsvName + '"]');
            if (!tagsWrap || !hidden) return;

            tagsWrap.innerHTML = '';
            const uniqVals = unique(values);
            uniqVals.forEach(v => {
                const tag = document.createElement('span');
                tag.className = 'badge rounded-pill text-bg-secondary d-inline-flex align-items-center gap-2';
                tag.setAttribute('data-tag-value', v);
                tag.innerHTML = '<span style="max-width: 260px; overflow: hidden; text-overflow: ellipsis;">' +
                    String(v).replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                    '</span>' +
                    '<button type="button" class="btn btn-sm btn-light py-0 px-2 meta-tag-input__remove" aria-label="Remove">×</button>';
                tagsWrap.appendChild(tag);
            });

            hidden.value = uniqVals.join(', ');
        }

        function getTagValues(root) {
            const hidden = root.querySelector('input[type="hidden"][name="' + root.dataset.tagCsvName + '"]');
            const current = hidden ? splitCsv(hidden.value) : [];
            // plus safe: read from rendered tags if present
            const rendered = Array.from(root.querySelectorAll('[data-tag-value]')).map(el => el.getAttribute('data-tag-value')).filter(Boolean);
            return rendered.length ? rendered : current;
        }

        function initTagInput(root) {
            const name = root?.dataset?.tagCsvName;
            if (!name) return;
            const hidden = root.querySelector('input[type="hidden"][name="' + name + '"]');
            const input = root.querySelector('.meta-tag-input__text');
            const addBtn = root.querySelector('.meta-tag-input__add');
            const errorEl = root.querySelector('.meta-tag-input__error');
            if (!hidden || !input || !addBtn) return;

            // initial render
            renderTags(root, splitCsv(hidden.value));

            function isStrictEnabled() {
                const toggle = document.getElementById('meta_tag_validation_strict');
                return toggle ? !!toggle.checked : true;
            }

            function setError(msg) {
                if (!errorEl) return;
                if (!msg) {
                    errorEl.style.display = 'none';
                    errorEl.textContent = '';
                    input.classList.remove('is-invalid');
                    return;
                }
                errorEl.style.display = '';
                errorEl.textContent = msg;
                input.classList.add('is-invalid');
            }

            function validate(type, value) {
                if (!isStrictEnabled()) return { ok: true };
                const v = String(value || '').trim();
                if (!v) return { ok: false, msg: 'Valeur vide.' };
                if (/[,\s]/.test(v)) return { ok: false, msg: 'Pas d’espaces ni de virgules (utilisez des tags séparés).' };

                if (type === 'funnel') {
                    if (v.length > 64) return { ok: false, msg: 'Funnel trop long (max 64 caractères).' };
                    if (!/^[A-Za-z0-9._-]+$/.test(v)) return { ok: false, msg: 'Caractères autorisés: lettres, chiffres, ".", "_", "-".' };
                }

                if (type === 'path_pattern') {
                    if (v.length > 255) return { ok: false, msg: 'Pattern trop long (max 255 caractères).' };
                    if (/^https?:\/\//i.test(v)) return { ok: false, msg: 'Entrez un path/pattern (ex: "cart/*"), pas une URL.' };
                }

                return { ok: true };
            }

            function addValue(val) {
                const v = String(val || '').trim();
                if (!v) return;
                const rule = root.dataset.tagValidate || '';
                const res = validate(rule, v);
                if (!res.ok) {
                    setError(res.msg || 'Valeur invalide.');
                    return;
                }
                setError('');
                const next = getTagValues(root).concat([v]);
                renderTags(root, next);
                input.value = '';
            }

            addBtn.addEventListener('click', function () {
                addValue(input.value);
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addValue(input.value);
                }
            });

            input.addEventListener('input', function () {
                // clear error while typing
                setError('');
            });

            root.addEventListener('click', function (e) {
                const btn = e.target && e.target.closest ? e.target.closest('.meta-tag-input__remove') : null;
                if (!btn) return;
                const tag = btn.closest('[data-tag-value]');
                const v = tag ? tag.getAttribute('data-tag-value') : null;
                const next = getTagValues(root).filter(x => x !== v);
                renderTags(root, next);
            });
        }

        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form || !form.querySelectorAll) return;
            form.querySelectorAll('select[data-csv-name]').forEach(updateCsv);

            // Validation stricte event_name pour éviter les fautes de frappe
            const strictToggle = document.getElementById('meta_tag_validation_strict');
            const strictEnabled = strictToggle ? !!strictToggle.checked : true;

            function getDatalistValues(id) {
                const dl = document.getElementById(id);
                if (!dl) return [];
                return Array.from(dl.querySelectorAll('option')).map(o => o.value).filter(Boolean);
            }

            function levenshtein(a, b) {
                a = String(a || '');
                b = String(b || '');
                const m = a.length, n = b.length;
                if (m === 0) return n;
                if (n === 0) return m;
                const dp = new Array(n + 1);
                for (let j = 0; j <= n; j++) dp[j] = j;
                for (let i = 1; i <= m; i++) {
                    let prev = dp[0];
                    dp[0] = i;
                    for (let j = 1; j <= n; j++) {
                        const tmp = dp[j];
                        const cost = a[i - 1] === b[j - 1] ? 0 : 1;
                        dp[j] = Math.min(
                            dp[j] + 1,
                            dp[j - 1] + 1,
                            prev + cost
                        );
                        prev = tmp;
                    }
                }
                return dp[n];
            }

            function closestMatchFromList(id, value) {
                const v = String(value || '').trim();
                if (!v) return null;
                const opts = getDatalistValues(id);
                if (!opts.length) return null;

                const needle = v.toLowerCase();
                let best = null;
                let bestDist = Infinity;
                for (const opt of opts) {
                    const cand = String(opt || '');
                    const dist = levenshtein(needle, cand.toLowerCase());
                    if (dist < bestDist) {
                        bestDist = dist;
                        best = cand;
                    }
                }

                // Seuils raisonnables anti-bruit
                const maxLen = Math.max(needle.length, String(best || '').length);
                const maxDist = maxLen <= 6 ? 1 : (maxLen <= 12 ? 2 : 3);
                return bestDist <= maxDist ? best : null;
            }

            function findCanonicalInList(id, value) {
                const v = String(value || '').trim();
                if (!v) return null;
                const opts = getDatalistValues(id);
                const exact = opts.find(o => o === v);
                if (exact) return exact;
                const lower = v.toLowerCase();
                const ci = opts.find(o => String(o).toLowerCase() === lower);
                return ci || null;
            }

            function isValidCustomEventName(name) {
                const v = String(name || '').trim();
                if (!v) return false;
                if (v.length > 64) return false;
                if (/[,\s]/.test(v)) return false;
                return /^[A-Za-z0-9._-]+$/.test(v);
            }

            function setInvalidWithSuggestion(input, message, datalistId) {
                if (!input) return;
                input.classList.add('is-invalid');
                const feedback = input.parentElement?.querySelector('.invalid-feedback');
                if (!feedback) return;
                const suggestion = datalistId ? closestMatchFromList(datalistId, input.value) : null;
                if (suggestion) {
                    feedback.innerHTML =
                        (message ? message + ' ' : '') +
                        'Vouliez-vous dire ' +
                        '<a href="#" class="meta-event-suggest" data-suggest="' + String(suggestion).replace(/"/g, '&quot;') + '">' +
                        String(suggestion).replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                        '</a> ?';
                } else {
                    feedback.textContent = message || 'Valeur invalide.';
                }
            }

        // Triggers: "Événement" est un <select> (donc non-éditable), rien à valider ici.

            // Events: si "Standard" coché => whitelist stricte; sinon => custom format strict
            form.querySelectorAll('input.meta-validate-event-name[list="meta_standard_event_names"]').forEach(input => {
                input.classList.remove('is-invalid');
                const standardChecked = !!form.querySelector('input[name="event_is_standard"][type="checkbox"]')?.checked;
                if (standardChecked) {
                    const canonical = findCanonicalInList('meta_standard_event_names', input.value);
                    if (canonical) {
                        input.value = canonical;
                        return;
                    }
                    setInvalidWithSuggestion(
                        input,
                        'Nom d’événement standard invalide.',
                        'meta_standard_event_names'
                    );
                    e.preventDefault();
                    return;
                }

                if (strictEnabled && !isValidCustomEventName(input.value)) {
                    setInvalidWithSuggestion(
                        input,
                        'Nom custom invalide (caractères autorisés: lettres/chiffres/._- sans espaces).',
                        null
                    );
                    e.preventDefault();
                }
            });
        }, true);

        // Click sur suggestion "Vouliez-vous dire ..."
        document.addEventListener('click', function (e) {
            const a = e.target && e.target.closest ? e.target.closest('a.meta-event-suggest') : null;
            if (!a) return;
            e.preventDefault();
            const suggestion = a.getAttribute('data-suggest');
            // remonter jusqu'au champ input concerné (même parent .invalid-feedback)
            const feedback = a.closest('.invalid-feedback');
            const input = feedback?.parentElement?.querySelector('input.meta-validate-event-name');
            if (input && suggestion) {
                input.value = suggestion;
                input.classList.remove('is-invalid');
            }
        });

        // init tag widgets (on load)
        document.querySelectorAll('.meta-tag-input[data-tag-csv-name]').forEach(initTagInput);
        // init multi-select helpers (on load)
        document.querySelectorAll('select[data-csv-name][multiple]').forEach(initMultiSelectHelper);

        // Helpers for page scanning (suggest CSS selectors from a chosen page)
        function safeCssEscape(value) {
            try {
                if (window.CSS && typeof window.CSS.escape === 'function') {
                    return window.CSS.escape(String(value));
                }
            } catch (e) {}
            return String(value).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
        }

        function normalizePathToUrl(path) {
            const p = String(path || '').trim();
            if (!p || p === '/') return '/';
            return '/' + p.replace(/^\/+/, '');
        }

        function truncateText(s, maxLen) {
            const v = String(s || '').replace(/\s+/g, ' ').trim();
            if (!v) return '';
            return v.length > maxLen ? (v.slice(0, maxLen - 1) + '…') : v;
        }

        function buildSelectorForElement(el) {
            const tag = (el.tagName || '').toLowerCase();
            if (!tag) return null;

            // Prefer explicit tracking hooks when present (stable across dynamic/conditional UIs)
            try {
                const metaTrigger = el.getAttribute && el.getAttribute('data-meta-trigger');
                if (metaTrigger) {
                    return '[data-meta-trigger="' + String(metaTrigger).replace(/"/g, '\\"') + '"]';
                }
            } catch (e) {}

            const id = el.getAttribute && el.getAttribute('id');
            if (id) return tag + '#' + safeCssEscape(id);
            const classes = el.classList ? Array.from(el.classList) : [];
            const filtered = classes
                .filter(c => c && c.length <= 40)
                .filter(c => !['active', 'show', 'collapsed', 'disabled'].includes(c))
                .slice(0, 3);
            if (filtered.length) return tag + '.' + filtered.map(safeCssEscape).join('.');
            return tag;
        }

        function getVisibleLabelForElement(el) {
            if (!el || !el.getAttribute) return '';
            const aria = el.getAttribute('aria-label');
            const title = el.getAttribute('title');
            const value = el.getAttribute('value');
            const placeholder = el.getAttribute('placeholder');
            const dataName = el.getAttribute('data-name') || el.getAttribute('data-label');
            const text = truncateText(el.textContent || '', 80);

            return truncateText(
                text || aria || title || value || placeholder || dataName || '',
                80
            );
        }

        function describeElement(el, mode) {
            const tag = (el.tagName || '').toLowerCase();
            const id = el.getAttribute && el.getAttribute('id');
            const cls = el.getAttribute && el.getAttribute('class');

            const visible = getVisibleLabelForElement(el);
            const shortHint = id ? ('#' + id) : (cls ? ('.' + String(cls).split(/\s+/).slice(0, 1)[0]) : '');

            if (mode === 'form_submit') {
                const action = el.getAttribute ? (el.getAttribute('action') || '') : '';
                const actionHint = action ? (' → ' + truncateText(action, 50)) : '';
                return 'Formulaire' + (visible ? (': ' + visible) : (shortHint ? (' ' + shortHint) : '')) + actionHint;
            }

            // click mode
            if (tag === 'a') {
                const href = el.getAttribute ? (el.getAttribute('href') || '') : '';
                const hrefHint = href ? (' → ' + truncateText(href, 50)) : '';
                return 'Lien' + (visible ? (': ' + visible) : (shortHint ? (' ' + shortHint) : '')) + hrefHint;
            }

            return 'Bouton' + (visible ? (': ' + visible) : (shortHint ? (' ' + shortHint) : ''));
        }

        function extractCandidates(doc, mode) {
            const selectors = [];
            let nodes = [];
            if (mode === 'click') {
                nodes = Array.from(doc.querySelectorAll('button, a, [role="button"], input[type="button"], input[type="submit"]'));
            } else if (mode === 'form_submit') {
                nodes = Array.from(doc.querySelectorAll('form'));
            }

            for (const el of nodes) {
                const sel = buildSelectorForElement(el);
                if (!sel) continue;

                const label = describeElement(el, mode);
                selectors.push({ selector: sel, label });
            }

            // Add known dynamic/conditional UI actions (even if not present in the scanned HTML)
            if (mode === 'click') {
                selectors.push(
                    { selector: '[data-meta-trigger="whatsapp"]', label: 'Bouton WhatsApp (programme présentiel)' },
                    { selector: '[data-meta-trigger="checkout"]', label: 'Bouton Procéder au paiement' },
                    { selector: '[data-meta-trigger="add_to_cart"]', label: 'Bouton Ajouter au panier' },
                    { selector: '[data-meta-trigger="download"]', label: 'Bouton Télécharger' },
                    { selector: '[data-meta-trigger="enroll"]', label: 'Bouton S’inscrire / Intéresser' },
                    { selector: '[data-meta-trigger="learn"]', label: 'Bouton Commencer / Continuer' }
                );
            }
            if (mode === 'form_submit') {
                selectors.push(
                    { selector: '[data-meta-trigger="enroll"]', label: 'Formulaire S’inscrire / Intéresser' }
                );
            }

            const seen = new Set();
            const out = [];
            for (const it of selectors) {
                if (seen.has(it.selector)) continue;
                seen.add(it.selector);
                out.push(it);
            }
            return out.slice(0, 80);
        }

        // Fix "accordion open but content invisible" glitches:
        // ensure Bootstrap doesn't leave a 0px inline height after toggling.
        document.addEventListener('DOMContentLoaded', function () {
            const acc = document.getElementById('metaAccordion');
            if (!acc) return;
            if (!window.bootstrap) return;

            acc.addEventListener('shown.bs.collapse', function (e) {
                const el = e.target;
                if (!el || !el.classList || !el.classList.contains('accordion-collapse')) return;
                try {
                    el.style.height = 'auto';
                    el.style.overflow = 'visible';
                } catch (err) {}
            });

            acc.addEventListener('show.bs.collapse', function (e) {
                const el = e.target;
                if (!el || !el.classList || !el.classList.contains('accordion-collapse')) return;
                try {
                    // reset so Bootstrap can measure properly
                    el.style.height = '';
                } catch (err) {}
            });
        });

        // UX: afficher/masquer CSS selector selon trigger_type
        function updateSelectorVisibility(scope) {
            const root = scope || document;
            const forms = (root && root.tagName && root.tagName.toLowerCase() === 'form')
                ? [root]
                : Array.from((root.querySelectorAll && root.querySelectorAll('form')) || []);

            forms.forEach(form => {
                const typeSelect = form.querySelector('select.meta-trigger-type');
                if (!typeSelect) return;

                const type = typeSelect.value;

                // For page_load, page selection must be explicit (including "Toutes les pages")
                const pageSelect = form.querySelector('select.meta-page-select[name="match_path_pattern"]');
                if (pageSelect) {
                    pageSelect.required = (type === 'page_load');
                    if (pageSelect.required && !String(pageSelect.value || '').trim()) {
                        // default to explicit "all pages" option if present
                        const hasAll = Array.from(pageSelect.options || []).some(o => o.value === '__all__');
                        if (hasAll) pageSelect.value = '__all__';
                    }
                }

                // Toggle blocks inside "Options avancées" depending on trigger_type
                form.querySelectorAll('.meta-trigger-type-fields[data-show-when]').forEach(block => {
                    const allowed = String(block.getAttribute('data-show-when') || '')
                        .split(',')
                        .map(s => s.trim())
                        .filter(Boolean);
                    const show = allowed.includes(type);
                    block.style.display = show ? '' : 'none';
                });
            });
        }

        // Note: trigger_type is driven by "Type de trigger" (meta-trigger-preset) -> hidden select.meta-trigger-type

        document.addEventListener('change', function (e) {
            if (e.target && e.target.matches && e.target.matches('select.meta-trigger-type')) {
                const form = e.target.closest('form') || document;
                updateSelectorVisibility(form);
            }
        });

        updateSelectorVisibility(document);

        // Presets rapides pour création de trigger (sans hardcoder d'event)
        document.addEventListener('change', function (e) {
            const preset = e.target && e.target.matches ? (e.target.matches('select.meta-trigger-preset') ? e.target : null) : null;
            if (!preset) return;
            const form = preset.closest('form');
            if (!form) return;

            const typeSelect = form.querySelector('select.meta-trigger-type');
            const cssSelect = form.querySelector('[name="css_selector"]');

            const v = preset.value;
            if (!v) {
                if (typeSelect) typeSelect.value = '';
                updateSelectorVisibility(form);
                updateMetaTriggerFormState(form);
                return;
            }

            if (typeSelect) typeSelect.value = v;

            if (v === 'click') {
                if (cssSelect && !String(cssSelect.value || '').trim()) {
                    // ensure option exists (non-editable combobox)
                    const opt = document.createElement('option');
                    opt.value = '.btn-buy';
                    opt.textContent = '.btn-buy';
                    cssSelect.appendChild(opt);
                    cssSelect.value = '.btn-buy';
                }
            }
            if (v === 'form_submit') {
                if (cssSelect && !String(cssSelect.value || '').trim()) {
                    const opt = document.createElement('option');
                    opt.value = 'form';
                    opt.textContent = 'form';
                    cssSelect.appendChild(opt);
                    cssSelect.value = 'form';
                }
            }

            updateSelectorVisibility(form);
            updateMetaTriggerFormState(form);
        });

        // --- UX: disable dependent fields until prerequisites are chosen ---
        function setDisabled(el, disabled) {
            if (!el) return;
            el.disabled = !!disabled;
            if (disabled) {
                el.setAttribute('aria-disabled', 'true');
            } else {
                el.removeAttribute('aria-disabled');
            }
        }

        function updateMetaTriggerFormState(form) {
            if (!form) return;

            const eventSel = form.querySelector('select[name="event_name"]');
            const presetSel = form.querySelector('select.meta-trigger-preset');
            const typeSel = form.querySelector('select.meta-trigger-type');
            const submitBtn = form.querySelector('button[type="submit"]');

            const pageSel = form.querySelector('select.meta-page-select[name="match_path_pattern"]');
            const elementSel = form.querySelector('select.meta-element-select[name="css_selector"]');
            const payloadEl = form.querySelector('textarea[name="trigger_payload"]');
            const pixelsSel = form.querySelector('select[data-csv-name="trigger_pixel_ids"]');
            const activeCb = form.querySelector('input[name="trigger_is_active"]');
            const onceCb = form.querySelector('input[name="once_per_page"]');

            const eventChosen = !!(eventSel && String(eventSel.value || '').trim());

            // Step 1: event required before choosing a trigger preset
            if (presetSel) {
                setDisabled(presetSel, !eventChosen);
                if (!eventChosen) {
                    presetSel.value = '';
                }
            }

            // Step 2: trigger preset required (create form). Edit forms have no preset.
            const triggerChosen = presetSel ? !!String(presetSel.value || '').trim() : true;

            // Keep hidden trigger_type aligned for create form
            if (typeSel && presetSel) {
                if (!triggerChosen) typeSel.value = '';
            }

            const triggerType = typeSel ? String(typeSel.value || '').trim() : '';

            // Disable dependent fields until trigger chosen
            const depsDisabled = !(eventChosen && triggerChosen);

            setDisabled(pageSel, depsDisabled);
            setDisabled(payloadEl, depsDisabled);
            setDisabled(pixelsSel, depsDisabled);
            setDisabled(activeCb, depsDisabled);
            setDisabled(onceCb, depsDisabled);

            // Element select only relevant for click/form_submit
            const needsElement = (triggerType === 'click' || triggerType === 'form_submit');
            if (elementSel) {
                setDisabled(elementSel, depsDisabled || !needsElement);
            }

            // Also: if click/form_submit and no element selected, prevent submit
            let canSubmit = eventChosen;
            if (presetSel) canSubmit = canSubmit && triggerChosen;
            if (needsElement) {
                canSubmit = canSubmit && !!(elementSel && String(elementSel.value || '').trim());
            }
            if (triggerType === 'page_load') {
                // page_load: must explicitly select a page (including "__all__")
                canSubmit = canSubmit && !!(pageSel && String(pageSel.value || '').trim());
            }

            if (submitBtn) setDisabled(submitBtn, !canSubmit);
        }

        // Initial state
        try {
            document.querySelectorAll('form').forEach(form => {
                if (form.querySelector('select.meta-trigger-type') && form.querySelector('select[name="event_name"]')) {
                    updateMetaTriggerFormState(form);
                }
            });
        } catch (e) {}

        // Update on changes
        document.addEventListener('change', function (e) {
            const el = e.target;
            if (!el) return;
            const form = el.closest && el.closest('form');
            if (!form) return;
            if (el.matches('select[name="event_name"], select.meta-trigger-preset, select.meta-trigger-type, select[name="match_path_pattern"], select[name="css_selector"]')) {
                updateSelectorVisibility(form);
                updateMetaTriggerFormState(form);
            }
        });

        // ---- Non-editable combobox helpers: pages + elements ----
        function cssEscape(value) {
            try {
                if (window.CSS && typeof window.CSS.escape === 'function') {
                    return window.CSS.escape(String(value));
                }
            } catch (e) {}
            return String(value).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
        }

        function normalizePathToUrl(path) {
            const p = String(path || '').trim();
            if (!p) return null;
            return p.startsWith('/') ? p : '/' + p;
        }

        function isVisibleCandidate(el) {
            if (!el || !el.tagName) return false;
            // In static HTML we can't compute styles reliably; keep basic filters
            if (el.hasAttribute('disabled')) return false;
            return true;
        }

        function labelFor(el) {
            const tag = (el.tagName || '').toLowerCase();
            const txt = (el.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 60);
            const id = el.getAttribute && el.getAttribute('id');
            if (txt) return `${tag}: "${txt}"`;
            if (id) return `${tag}#${id}`;
            const name = el.getAttribute && el.getAttribute('name');
            if (name) return `${tag}[name="${name}"]`;
            return tag;
        }

        function uniqueSelector(doc, selector) {
            try {
                const n = doc.querySelectorAll(selector).length;
                return n === 1;
            } catch (e) {
                return false;
            }
        }

        function buildSelector(el, doc) {
            const tag = (el.tagName || '').toLowerCase();
            const id = el.getAttribute && el.getAttribute('id');
            if (id) return '#' + cssEscape(id);

            // Prefer stable attributes when present
            const stableAttrs = ['data-testid', 'data-test', 'data-track', 'data-qa', 'name'];
            for (const a of stableAttrs) {
                const v = el.getAttribute && el.getAttribute(a);
                if (v) {
                    const sel = `${tag}[${a}="${String(v).replace(/"/g, '\\"')}"]`;
                    if (uniqueSelector(doc, sel)) return sel;
                    return sel;
                }
            }

            // Links: use href if present
            if (tag === 'a') {
                const href = el.getAttribute && el.getAttribute('href');
                if (href) {
                    const sel = `a[href="${String(href).replace(/"/g, '\\"')}"]`;
                    if (uniqueSelector(doc, sel)) return sel;
                    return sel;
                }
            }

            // Fallback: tag + up to 2 classes
            const cls = (el.getAttribute && el.getAttribute('class')) ? String(el.getAttribute('class')) : '';
            const classes = cls.split(/\s+/).filter(Boolean).slice(0, 2);
            if (classes.length) {
                const sel = tag + classes.map(c => '.' + cssEscape(c)).join('');
                if (uniqueSelector(doc, sel)) return sel;
                return sel;
            }

            return tag;
        }

        async function scanElementsForForm(form) {
            const typeSelect = form.querySelector('select.meta-trigger-type');
            const type = typeSelect ? String(typeSelect.value || '') : '';
            if (type !== 'click' && type !== 'form_submit') return;

            const pageSelect = form.querySelector('select[name="match_path_pattern"]');
            const elementSelect = form.querySelector('select.meta-element-select[name="css_selector"]');
            if (!pageSelect || !elementSelect) return;

            const page = String(pageSelect.value || '').trim();
            if (!page) {
                // Can’t scan "all pages" — keep only current value
                const current = String(elementSelect.value || '').trim();
                elementSelect.innerHTML = '';
                if (current) {
                    const curOpt = document.createElement('option');
                    curOpt.value = current;
                    curOpt.textContent = 'Actuel: ' + current;
                    elementSelect.appendChild(curOpt);
                    elementSelect.value = current;
                }
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '— Choisir un élément (sélectionnez une page) —';
                elementSelect.appendChild(opt);
                if (!current) elementSelect.value = '';
                return;
            }

            const url = normalizePathToUrl(page);
            if (!url) return;

            try {
                const resp = await fetch(url, { method: 'GET', credentials: 'same-origin', cache: 'no-store' });
                const html = await resp.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');

                let candidates = [];
                if (type === 'click') {
                    candidates = Array.from(doc.querySelectorAll('button, a, [role="button"], input[type="button"], input[type="submit"]'));
                } else {
                    candidates = Array.from(doc.querySelectorAll('form'));
                }

                candidates = candidates.filter(isVisibleCandidate).slice(0, 200);
                const items = candidates.map(el => ({
                    selector: buildSelector(el, doc),
                    label: labelFor(el),
                }));

                // de-dup selectors
                const seen = new Set();
                const uniq = items.filter(it => {
                    const key = it.selector;
                    if (!key || seen.has(key)) return false;
                    seen.add(key);
                    return true;
                }).slice(0, 80);

                const current = String(elementSelect.value || '').trim();
                elementSelect.innerHTML = '';

                if (current && !seen.has(current)) {
                    const curOpt = document.createElement('option');
                    curOpt.value = current;
                    curOpt.textContent = 'Actuel: ' + current;
                    elementSelect.appendChild(curOpt);
                }

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = uniq.length ? '— Choisir un élément —' : '— Aucun élément trouvé sur cette page —';
                elementSelect.appendChild(placeholder);

                uniq.forEach(it => {
                    const opt = document.createElement('option');
                    opt.value = it.selector;
                    opt.textContent = `${it.label} (${it.selector})`;
                    elementSelect.appendChild(opt);
                });

                if (current && (seen.has(current) || !uniq.length)) {
                    elementSelect.value = current;
                } else {
                    elementSelect.value = '';
                }
            } catch (e) {
                // On erreur, ne pas casser l’admin
            }
        }

        document.addEventListener('change', function (e) {
            const pageSel = e.target && e.target.matches ? (e.target.matches('select[name="match_path_pattern"]') ? e.target : null) : null;
            if (pageSel) {
                const form = pageSel.closest('form');
                if (form) scanElementsForForm(form);
                return;
            }
            const typeSel = e.target && e.target.matches ? (e.target.matches('select.meta-trigger-type') ? e.target : null) : null;
            if (typeSel) {
                const form = typeSel.closest('form');
                if (form) scanElementsForForm(form);
            }
        });

        // Initial scan for edit forms that already have a page selected
        try {
            document.querySelectorAll('form').forEach(form => {
                const hasElementSelect = !!form.querySelector('select.meta-element-select[name="css_selector"]');
                const hasPageSelect = !!form.querySelector('select[name="match_path_pattern"]');
                if (hasElementSelect && hasPageSelect) {
                    scanElementsForForm(form);
                }
            });
        } catch (e) {}

        // Ouvrir automatiquement l'accordion "Triggers"
        function openMetaTriggersAccordion() {
            const el = document.getElementById('metaTriggersCollapse');
            if (!el) return;
            try {
                if (window.bootstrap && window.bootstrap.Collapse) {
                    new window.bootstrap.Collapse(el, { toggle: false }).show();
                } else {
                    el.classList.add('show');
                }
            } catch (e) {}
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Auto-open via query string (?open=triggers)
        try {
            const params = new URLSearchParams(window.location.search || '');
            if (params.get('open') === 'triggers') {
                openMetaTriggersAccordion();
            }
        } catch (e) {}

        // Quick action "Créer trigger pour cet événement"
        document.addEventListener('click', function (e) {
            const btn = e.target && e.target.closest ? e.target.closest('button.meta-quick-create-trigger') : null;
            if (!btn) return;
            e.preventDefault();

            const eventName = btn.getAttribute('data-event-name') || '';
            openMetaTriggersAccordion();

            const input = document.getElementById('metaTriggerEventName');
            if (input) {
                input.value = eventName;
                input.dispatchEvent(new Event('change', { bubbles: true }));
                input.focus();
            }
        });
    })();
</script>
@endpush

@push('styles')
<style>
/* Meta accordion: allow flyouts to overflow tables */
#metaAccordion,
#metaAccordion .accordion-item,
#metaAccordion .accordion-collapse,
#metaAccordion .accordion-body {
    overflow: visible !important;
}

/* Fix: prevent "collapsing" height glitches that can visually hide content */
#metaAccordion .accordion-collapse {
    transition: none !important;
}
#metaAccordion .accordion-collapse.collapsing {
    height: auto !important;
}
#metaAccordion .accordion-collapse.show {
    height: auto !important;
    visibility: visible !important;
    opacity: 1 !important;
}

#metaAccordion .table-responsive {
    overflow-y: visible;
}

/* Prevent admin-table wrapper from clipping flyouts */
#metaAccordion .admin-table {
    overflow: visible !important;
}
#metaAccordion .admin-table .table-responsive {
    overflow-x: auto;
    overflow-y: visible;
}

/* Match /admin/contents responsive admin-table look */
@media (max-width: 991.98px) {
    .admin-table .table thead th,
    .admin-table .table tbody td {
        white-space: nowrap;
    }
}

@media (max-width: 767.98px) {
    .admin-table .table {
        font-size: 0.85rem;
    }

    .admin-table .table-responsive {
        margin: 0;
    }
}

/* Some admin panel/card wrappers clip accordions by default */
.admin-panel,
.admin-panel__body,
.admin-card,
.admin-card__body {
    overflow: visible !important;
}

/* Flyout panels for "Éditer" buttons inside tables */
#metaAccordion details.meta-flyout {
    position: relative;
}
#metaAccordion details.meta-flyout > summary {
    list-style: none;
}
#metaAccordion details.meta-flyout > summary::-webkit-details-marker {
    display: none;
}
#metaAccordion details.meta-flyout > div {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    z-index: 2000;
    box-shadow: 0 18px 40px -24px rgba(15, 23, 42, 0.45);
}

@media (max-width: 767.98px) {
    #metaAccordion details.meta-flyout > div {
        left: 0;
        right: auto;
        max-width: 92vw;
        min-width: 0 !important;
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

