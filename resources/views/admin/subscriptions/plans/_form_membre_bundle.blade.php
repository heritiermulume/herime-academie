@csrf

@if ($errors->any())
    <div class="alert alert-danger mb-4" role="alert">
        <p class="fw-semibold mb-2">Veuillez corriger les champs ci-dessous :</p>
        <ul class="mb-0 small">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $bc = $baseCurrency ?? \App\Models\Setting::getBaseCurrency();
    $siteCurrencyDisplay = strtoupper((string) (is_array($bc) ? ($bc['code'] ?? 'USD') : (($bc !== null && $bc !== '') ? $bc : 'USD')));
    $memberBundlePlans = $memberBundlePlans ?? collect();
    $membreBaseName = '';
    if ($memberBundlePlans->isNotEmpty()) {
        foreach (\App\Models\SubscriptionPlan::MEMBER_COMMUNITY_SLUGS as $slug) {
            $p = $memberBundlePlans->get($slug);
            if ($p) {
                $membreBaseName = $p->memberOfferBaseName();
                break;
            }
        }
    }
    $anchorPlan = $memberBundlePlans->get(\App\Models\SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'])
        ?? $memberBundlePlans->get(\App\Models\SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'])
        ?? ($memberBundlePlans->isNotEmpty() ? $memberBundlePlans->first() : null);
    $defDescription = $anchorPlan?->description ?? '';
    $defActive = $anchorPlan?->is_active ?? true;
    $defAutoRenew = $anchorPlan?->auto_renew_default ?? true;
    $defPopularPeriod = 'yearly';
    foreach (\App\Models\SubscriptionPlan::MEMBER_COMMUNITY_SLUGS as $periodKey => $slugKey) {
        $pp = $memberBundlePlans->get($slugKey);
        if ($pp && $pp->isCommunityCardPopular()) {
            $defPopularPeriod = $periodKey;
            break;
        }
    }
@endphp

<input type="hidden" name="plan_type" value="membre">

<p class="text-muted small mb-4">
    Trois périodes de facturation fixes (slugs <code>membre-herime-trimestriel</code>, <code>membre-herime-semestriel</code>, <code>membre-herime-annuel</code>),
    affichées sur <a href="{{ route('community.premium') }}" target="_blank" rel="noopener">la page adhésion</a> et dans l’espace client.
</p>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <label class="form-label fw-semibold" for="membre-offer-name">Nom de l’offre</label>
        <input type="text" name="name" id="membre-offer-name" class="form-control" required maxlength="255"
               value="{{ old('name', $membreBaseName) }}"
               placeholder="Ex. Réseau Membre Herime">
        <small class="text-muted">Préfixe commun aux trois libellés (suffixe : Trimestriel, Semestriel, Annuel).</small>
    </div>
    <div class="col-lg-6 d-flex flex-column justify-content-end gap-2">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="membre-is-active" value="1"
                   @checked((bool) old('is_active', $defActive))>
            <label class="form-check-label" for="membre-is-active">Offre active</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="auto_renew_default" id="membre-auto-renew" value="1"
                   @checked((bool) old('auto_renew_default', $defAutoRenew))>
            <label class="form-check-label" for="membre-auto-renew">Renouvellement auto par défaut</label>
        </div>
    </div>
</div>

<div class="mb-4">
    <label class="form-label fw-semibold" for="membre-description">Description commune</label>
    <textarea name="description" id="membre-description" rows="3" class="form-control" placeholder="Texte partagé par les trois formules">{{ old('description', $defDescription) }}</textarea>
</div>

<h6 class="fw-bold mb-3 border-bottom pb-2">Périodes et tarifs</h6>
<p class="small text-muted mb-3">Les montants ci-dessous sont dans la devise du site : <strong>{{ $siteCurrencyDisplay }}</strong>.</p>

<div class="row g-3 mb-4">
    @foreach(\App\Models\SubscriptionPlan::MEMBER_COMMUNITY_SLUGS as $period => $slug)
        @php
            $pl = $memberBundlePlans->get($slug);
            $priceKey = 'membre_price_'.$period;
            $hiKey = 'membre_highlight_'.$period;
            $defPrice = $pl ? $pl->price : 0;
            $defTrialDays = (int) ($pl?->trial_days ?? 0);
            $defHi = $pl ? (string) data_get($pl->metadata, 'community_card_highlight', '') : '';
            $trialKey = 'membre_trial_days.'.$period;
            $label = \App\Models\SubscriptionPlan::MEMBER_COMMUNITY_PERIOD_LABELS[$period] ?? $period;
            $periodHint = match ($period) {
                'quarterly' => 'Facturation tous les 3 mois',
                'semiannual' => 'Facturation tous les 6 mois',
                'yearly' => 'Facturation annuelle',
                default => '',
            };
        @endphp
        <div class="col-md-4">
            <div class="border rounded-3 p-3 h-100 bg-light">
                @if($pl)
                    <input type="hidden" name="membre_plan_ids[{{ $period }}]" value="{{ $pl->id }}">
                @endif
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <span class="fw-bold text-primary">{{ $label }}</span>
                        <p class="small text-muted mb-0">{{ $periodHint }}</p>
                    </div>
                </div>
                <label class="form-label fw-semibold small mb-1" for="{{ $priceKey }}">Prix ({{ $siteCurrencyDisplay }})</label>
                <div class="input-group input-group-sm mb-2">
                    <input type="number" step="0.01" min="0" name="{{ $priceKey }}" id="{{ $priceKey }}" class="form-control"
                           value="{{ old($priceKey, $defPrice) }}" required>
                    <span class="input-group-text">{{ $siteCurrencyDisplay }}</span>
                </div>
                <label class="form-label fw-semibold small mb-1" for="membre-trial-{{ $period }}">Essai gratuit (jours)</label>
                <input type="number" min="0" max="365" name="membre_trial_days[{{ $period }}]" id="membre-trial-{{ $period }}"
                       class="form-control form-control-sm mb-2" value="{{ old($trialKey, $defTrialDays) }}">
                <label class="form-label small text-muted mb-1" for="{{ $hiKey }}">Sous-texte sur la carte (optionnel)</label>
                <textarea name="{{ $hiKey }}" id="{{ $hiKey }}" class="form-control form-control-sm" rows="2" maxlength="2000">{{ old($hiKey, $defHi) }}</textarea>
                <div class="form-check mt-2 pt-1 border-top">
                    <input class="form-check-input" type="radio" name="membre_popular_period" id="membre-popular-{{ $period }}" value="{{ $period }}"
                           @checked(old('membre_popular_period', $defPopularPeriod) === $period)>
                    <label class="form-check-label small" for="membre-popular-{{ $period }}">Période « populaire » sur la carte</label>
                </div>
                <small class="text-muted d-block mt-2"><code>{{ $slug }}</code></small>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-4 d-flex flex-wrap gap-2">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i>Enregistrer
    </button>
    <a href="{{ route('admin.subscriptions.plans.index') }}" class="btn btn-light border">Annuler</a>
</div>
