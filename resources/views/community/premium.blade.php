@extends('layouts.app')

@section('title', 'Rejoindre le réseau — Membre premium | Herime Académie')
@section('description', 'Dernière étape pour accéder à la communauté privée Membre Herime : choisissez la facturation mensuelle ou annuelle et finalisez votre adhésion.')

@section('content')
@php
    $billingLabels = [
        'quarterly' => 'Trimestriel',
        'semiannual' => 'Semestriel',
        'yearly' => 'Annuel',
        'monthly' => 'Mensuel',
    ];
    $communityPlansOrdered = $communityPlansOrdered ?? collect();
    $communityPremiumDefaultPlan = $communityPremiumDefaultPlan ?? null;
    $showPremiumCard = (bool) ($showPremiumCard ?? false);
    $premiumPageTexts = $premiumPageTexts ?? [];
    $premiumPlanHighlights = $premiumPlanHighlights ?? [];
    $premiumSubscriptionsByPlanId = $premiumSubscriptionsByPlanId ?? collect();
    $pt = function (string $key, string $default) use ($premiumPageTexts) {
        $v = $premiumPageTexts[$key] ?? null;

        return (is_string($v) && trim($v) !== '') ? $v : $default;
    };
@endphp
<style>
    .community-premium-hero {
        background: linear-gradient(145deg, #001a33 0%, #003366 45%, #004080 100%);
        color: #fff;
        padding: 3rem 0 2.5rem;
    }
    @media (max-width: 767.98px) {
        .community-premium-hero {
            padding: 2rem 0 1.5rem;
        }
    }
    .community-premium-hero h1 {
        font-weight: 700;
        letter-spacing: -0.02em;
    }
    @media (min-width: 768px) {
        .community-premium-hero .h1-md {
            font-size: 2.5rem;
        }
    }
    @media (max-width: 767.98px) {
        .community-premium-hero .h1-md {
            font-size: 1.75rem;
        }
    }

    /* ——— Carte abonnement moderne ——— */
    .mp-subscribe-zone {
        position: relative;
        padding: 0.5rem 0 1rem;
        width: 100%;
        max-width: 440px;
        margin-left: auto;
        margin-right: auto;
    }
    @media (min-width: 576px) {
        .mp-subscribe-zone {
            max-width: 480px;
        }
    }
    .mp-subscribe-zone::before {
        content: '';
        position: absolute;
        inset: -8% -20% auto -20%;
        height: 70%;
        max-height: 420px;
        margin: 0 auto;
        background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(0, 51, 102, 0.14), transparent 70%);
        pointer-events: none;
    }

    .mp-card {
        position: relative;
        width: 100%;
        max-width: none;
        margin: 0;
        border-radius: 1.35rem;
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
        box-shadow:
            0 0 0 1px rgba(15, 23, 42, 0.06),
            0 2px 4px rgba(15, 23, 42, 0.04),
            0 24px 48px -12px rgba(0, 51, 102, 0.18),
            0 48px 96px -24px rgba(15, 23, 42, 0.12);
        overflow: visible;
    }
    @media (min-width: 576px) {
        .mp-card {
            border-radius: 1.5rem;
        }
    }

    .mp-intro-heading {
        text-align: center;
    }
    .mp-intro-heading h2 {
        color: #003366;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .mp-intro-heading p {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .mp-card__inner {
        padding: 1.75rem 1.5rem 1.5rem;
    }
    @media (min-width: 576px) {
        .mp-card__inner {
            padding: 2rem 2rem 1.75rem;
        }
    }

    .mp-card__badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #003366;
        background: rgba(0, 51, 102, 0.08);
        border: 1px solid rgba(0, 51, 102, 0.12);
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        margin-bottom: 0.85rem;
    }

    .mp-card__title {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
        line-height: 1.2;
        margin-bottom: 0.35rem;
    }
    @media (min-width: 576px) {
        .mp-card__title {
            font-size: 1.5rem;
        }
    }

    .mp-card__subtitle {
        font-size: 0.875rem;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 1.35rem;
    }

    /* Sélecteur période */
    .mp-period {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.4rem;
        padding: 0.45rem 0.35rem 0.35rem;
        border-radius: 0.85rem;
        background: #0f172a;
        margin-bottom: 1.5rem;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.25);
    }
    @media (max-width: 374px) {
        .mp-period__btn {
            min-height: 3.85rem;
            padding: 0.4rem 0.2rem;
        }
        .mp-period__btn .mp-period__label {
            font-size: 0.74rem;
        }
        .mp-period__btn .mp-period__hint {
            font-size: 0.58rem;
        }
    }
    .mp-period__btn {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.15rem;
        min-height: 4.25rem;
        padding: 0.5rem 0.35rem;
        border: none;
        border-radius: 0.65rem;
        background: transparent;
        color: rgba(255, 255, 255, 0.55);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        transition: color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
    }
    .mp-period__btn .mp-period__label {
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: none;
        letter-spacing: -0.02em;
        color: inherit;
    }
    .mp-period__btn .mp-period__hint {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0;
        opacity: 0.75;
    }
    .mp-period__btn:hover:not(:disabled) {
        color: rgba(255, 255, 255, 0.92);
    }
    .mp-period__btn.is-active {
        background: #fff;
        color: #0f172a;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
        transform: scale(1.02);
    }
    /* :hover:not(:disabled) a une spécificité plus forte que .is-active seul — sans ceci le libellé redevient blanc sur fond blanc */
    .mp-period__btn.is-active:hover:not(:disabled),
    .mp-period__btn.is-active:focus-visible:not(:disabled) {
        color: #0f172a;
    }
    .mp-period__btn.is-active .mp-period__hint {
        color: #64748b;
        opacity: 1;
    }
    .mp-period__btn:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }
    .mp-period__pill {
        position: absolute;
        top: -0.42rem;
        left: 50%;
        transform: translateX(-50%);
        white-space: nowrap;
        font-size: 0.55rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #b45309;
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        padding: 0.1rem 0.4rem;
        border-radius: 999px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        pointer-events: none;
    }

    .mp-price-block {
        text-align: center;
        padding: 1rem 0 1.25rem;
        margin-bottom: 0.25rem;
        border-radius: 1rem;
        background: linear-gradient(180deg, rgba(0, 51, 102, 0.04) 0%, transparent 100%);
        border: 1px solid rgba(0, 51, 102, 0.08);
    }
    .mp-price {
        font-size: clamp(2rem, 8vw, 2.75rem);
        font-weight: 800;
        letter-spacing: -0.04em;
        color: #003366;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }
    .mp-price-suffix {
        font-size: 0.95rem;
        font-weight: 600;
        color: #64748b;
        margin-top: 0.35rem;
    }
    .mp-currency-note {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 0.25rem;
    }

    .mp-plan-meta h4 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    .mp-plan-meta p {
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 0;
    }

    .mp-features {
        list-style: none;
        padding: 0;
        margin: 1rem 0 1.35rem;
        text-align: left;
    }
    .mp-features li {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
        font-size: 0.82rem;
        color: #475569;
        padding: 0.4rem 0;
        border-bottom: 1px solid rgba(148, 163, 184, 0.2);
    }
    .mp-features li:last-child {
        border-bottom: none;
    }
    .mp-features li i {
        color: #059669;
        margin-top: 0.15rem;
        flex-shrink: 0;
    }

    .mp-cta {
        display: block;
        width: 100%;
        border: none;
        font-weight: 700;
        font-size: 1rem;
        padding: 0.95rem 1.25rem;
        border-radius: 0.75rem;
        color: #fff !important;
        text-align: center;
        text-decoration: none;
        background: linear-gradient(135deg, #003366 0%, #004a94 50%, #0055aa 100%);
        box-shadow: 0 4px 16px rgba(0, 51, 102, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.12);
        transition: transform 0.15s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }
    .mp-cta:hover {
        filter: brightness(1.06);
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(0, 51, 102, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.15);
        color: #fff !important;
    }
    .mp-cta:active {
        transform: translateY(0);
    }

    .mp-trust {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.75rem 1.25rem;
        margin-top: 1rem;
        font-size: 0.72rem;
        color: #94a3b8;
    }
    .mp-trust span {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .mp-trust i {
        color: #003366;
        opacity: 0.7;
    }

    .mp-pay-hint {
        font-size: 0.78rem;
        color: #94a3b8;
        margin-top: 0.85rem;
        margin-bottom: 0;
        text-align: center;
        line-height: 1.45;
    }

    .mp-subscriber-tools .btn {
        font-weight: 600;
        border-radius: 0.75rem;
        padding: 0.65rem 1rem;
    }

    /* Boutons primaires (carte + encart invité) */
    a.mp-cta,
    button.mp-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<section class="community-premium-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 text-center">
                <p class="text-uppercase small mb-2 opacity-75">{{ $pt('kicker', 'Dernière étape') }}</p>
                <h1 class="h2 h1-md fw-bold mb-3">{{ $pt('title', 'Après ça, tu fais partie des membres premium') }}</h1>
                <p class="lead mb-0 opacity-90">
                    {{ $pt('lead', 'Tu es à un pas d’accéder à la communauté privée des membres, notre espace Membre Herime, et de débloquer toutes les ressources qui l’accompagnent.') }}
                </p>
                @if(filled($premiumPageTexts['second'] ?? null))
                    <p class="mt-3 mb-0 opacity-85">{{ $premiumPageTexts['second'] }}</p>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($communityPlansOrdered->isEmpty() || ! $showPremiumCard || ! $communityPremiumDefaultPlan)
            <div class="text-center py-5">
                <p class="text-muted mb-3">Les formules d’adhésion au réseau sont en cours de configuration.</p>
                <a href="{{ route('contact') }}" class="btn btn-outline-primary">Nous contacter</a>
                @auth
                    <a href="{{ route('customer.subscriptions') }}" class="btn btn-primary ms-2">Mes abonnements et factures</a>
                @endauth
            </div>
        @else
            @php
                $defaultPremiumPlan = $communityPremiumDefaultPlan;
                $plansByPeriod = $communityPlansOrdered->keyBy('billing_period');
                $anyMemberPopular = $communityPlansOrdered->contains(fn ($pl) => $pl->isCommunityCardPopular());
                $periodUiOrder = [
                    'quarterly' => ['label' => 'Trimestre', 'hint' => '3 mois'],
                    'semiannual' => ['label' => 'Semestre', 'hint' => '6 mois'],
                    'yearly' => ['label' => 'Annuel', 'hint' => '12 mois'],
                ];
                $premiumSsoCallback = route('sso.callback', ['redirect' => url()->full()]);
                $premiumSsoLoginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($premiumSsoCallback);
                $premiumPlansPayload = [
                    'plans' => [],
                    'defaultSlug' => $defaultPremiumPlan->slug,
                ];
                foreach ($communityPlansOrdered as $p) {
                    $periodLabel = $billingLabels[$p->billing_period] ?? ucfirst((string) $p->billing_period);
                    $renewalCaption = match ($p->billing_period) {
                        'quarterly' => 'Renouvellement tous les 3 mois',
                        'semiannual' => 'Renouvellement tous les 6 mois',
                        'yearly' => 'Renouvellement annuel',
                        default => 'Facturation '.mb_strtolower($periodLabel),
                    };
                    $planSub = $premiumSubscriptionsByPlanId->get($p->id);
                    $premiumPlansPayload['plans'][$p->slug] = [
                        'subscribe_url' => route('subscriptions.subscribe', $p),
                        'price_formatted' => \App\Helpers\CurrencyHelper::formatWithSymbol(
                            $p->effectivePriceForCurrency($preferredCurrency),
                            $preferredCurrency
                        ),
                        'period_label' => $periodLabel,
                        'renewal_caption' => $renewalCaption,
                        'name' => $p->name,
                        'description' => $p->description,
                        'highlight' => $premiumPlanHighlights[$p->slug] ?? '',
                        'trial_days' => (int) ($p->trial_days ?? 0),
                        'user_subscription' => $planSub?->asCommunityPremiumCardSubscription(),
                    ];
                }
            @endphp
            <div class="row justify-content-center">
                <div class="col-12 px-3 px-sm-4">
                    <div class="mp-subscribe-zone">
                        <header class="mp-intro-heading mb-4 pb-1">
                            <h2 class="h3 mb-2">{{ $pt('plans_intro_title', 'Choisis ta formule') }}</h2>
                            <p class="mb-0">{{ $pt('plans_intro_subtitle', 'Une offre Membre Herime : même avantages, choisis la période de facturation qui te convient.') }}</p>
                        </header>
                        <article class="mp-card" aria-labelledby="mp-card-heading">
                            <div class="mp-card__inner">
                                <span class="mp-card__badge">
                                    <i class="fas fa-gem" aria-hidden="true"></i>
                                    {{ $pt('premium_badge', 'Adhésion premium') }}
                                </span>
                                <h3 id="mp-card-heading" class="mp-card__title">{{ $pt('premium_single_card_title', 'Membre Herime') }}</h3>
                                <p class="mp-card__subtitle mb-0">{{ $pt('premium_single_card_lead', 'Communauté privée, formations, réseau, lives et ressources premium. Choisis la période qui correspond à ton rythme.') }}</p>

                                <div class="mp-period mt-4" role="tablist" aria-label="Période de facturation">
                                    @foreach($periodUiOrder as $periodKey => $meta)
                                        @php
                                            $segPlan = $plansByPeriod->get($periodKey);
                                        @endphp
                                        <button
                                            type="button"
                                            class="mp-period__btn"
                                            @if($segPlan) data-premium-slug="{{ $segPlan->slug }}" @endif
                                            @disabled(! $segPlan)
                                            aria-selected="false"
                                        >
                                            @if($segPlan && ($segPlan->isCommunityCardPopular() || (! $anyMemberPopular && $periodKey === 'yearly')))
                                                <span class="mp-period__pill">{{ $pt('premium_period_popular_pill', 'Populaire') }}</span>
                                            @endif
                                            <span class="mp-period__label">{{ $meta['label'] }}</span>
                                            <span class="mp-period__hint">{{ $segPlan ? $meta['hint'] : 'Bientôt' }}</span>
                                        </button>
                                    @endforeach
                                </div>

                                <div class="mp-price-block">
                                    <div id="community-premium-price" class="mp-price">
                                        {{ \App\Helpers\CurrencyHelper::formatWithSymbol($defaultPremiumPlan->effectivePriceForCurrency($preferredCurrency), $preferredCurrency) }}
                                    </div>
                                    <p id="community-premium-billing-line" class="mp-price-suffix mb-0">
                                        {{ data_get($premiumPlansPayload, 'plans.'.$defaultPremiumPlan->slug.'.renewal_caption', '') }}
                                    </p>
                                    <p class="mp-currency-note mb-0">{{ $preferredCurrency }} · taxes incluses le cas échéant</p>
                                </div>

                                <div class="mp-plan-meta text-center">
                                    <h4 id="community-premium-plan-name">{{ $defaultPremiumPlan->name }}</h4>
                                    <p id="community-premium-desc" class="{{ $defaultPremiumPlan->description ? '' : 'd-none' }}">{{ $defaultPremiumPlan->description }}</p>
                                    <p id="community-premium-highlight" class="mt-2 mb-0 {{ ! empty($premiumPlanHighlights[$defaultPremiumPlan->slug] ?? null) ? '' : 'd-none' }}">{{ $premiumPlanHighlights[$defaultPremiumPlan->slug] ?? '' }}</p>
                                    @php
                                        $defaultTrialDays = (int) ($defaultPremiumPlan->trial_days ?? 0);
                                    @endphp
                                    <p id="community-premium-trial" class="small text-success mb-0 mt-2 fw-semibold {{ $defaultTrialDays > 0 ? '' : 'd-none' }}">
                                        @if($defaultTrialDays === 1)
                                            1 jour d’essai gratuit
                                        @elseif($defaultTrialDays > 1)
                                            {{ $defaultTrialDays }} jours d’essai gratuit
                                        @endif
                                    </p>
                                </div>

                                <ul class="mp-features" aria-label="Inclus dans l’adhésion">
                                    <li><i class="fas fa-check-circle" aria-hidden="true"></i><span>Accès à la communauté privée Membre Herime</span></li>
                                    <li><i class="fas fa-check-circle" aria-hidden="true"></i><span>Formations et contenus selon les règles du site (streaming, non téléchargeable)</span></li>
                                    <li><i class="fas fa-check-circle" aria-hidden="true"></i><span>Lives, réseau et ressources réservées aux membres</span></li>
                                    <li><i class="fas fa-check-circle" aria-hidden="true"></i><span>Renouvellement automatique, résiliable depuis ton espace</span></li>
                                </ul>

                                @auth
                                    <form id="mp-form-cancel" method="POST" class="d-none" aria-hidden="true">@csrf</form>
                                    <form id="mp-form-resume" method="POST" class="d-none" aria-hidden="true">@csrf</form>
                                    <form id="mp-form-pay" method="POST" class="d-none" aria-hidden="true">
                                        @csrf
                                        <input type="hidden" name="return_to" value="community">
                                    </form>

                                    <div id="mp-subscriber-tools" class="mp-subscriber-tools d-none">
                                        <p id="mp-subscriber-status" class="small text-center fw-semibold mb-2 d-none" role="status"></p>
                                        <button type="button" id="mp-btn-pay" class="mp-cta w-100 mb-2 d-none">
                                            <i class="fas fa-credit-card me-2" aria-hidden="true"></i>Finaliser le paiement
                                        </button>
                                        <button type="button" id="mp-btn-resume" class="btn btn-outline-success w-100 mb-2 d-none">
                                            <i class="fas fa-redo me-2" aria-hidden="true"></i>Réactiver le renouvellement auto
                                        </button>
                                        <button type="button" id="mp-btn-cancel" class="btn btn-outline-danger w-100 mb-2 d-none">
                                            <i class="fas fa-ban me-2" aria-hidden="true"></i>Résilier l’abonnement
                                        </button>
                                        <p id="mp-subscriber-hint-resiliate" class="small text-muted text-center mb-0 d-none">
                                            Le renouvellement automatique sera arrêté ; tu conserves l’accès jusqu’à la fin de la période payée.
                                        </p>
                                    </div>

                                    <form id="community-premium-subscribe-form" method="POST" action="{{ route('subscriptions.subscribe', $defaultPremiumPlan) }}">
                                        @csrf
                                        <input type="hidden" name="redirect_after_subscribe" value="community.premium">
                                        <button type="submit" id="community-premium-submit-btn" class="mp-cta w-100">
                                            <i class="fas fa-shield-alt me-2" aria-hidden="true"></i><span id="community-premium-submit-label">Procéder au paiement</span>
                                        </button>
                                    </form>
                                    <p class="mp-pay-hint mb-0 mt-2">
                                        <a href="{{ route('customer.subscriptions') }}" class="text-muted">Mes abonnements et factures</a>
                                    </p>
                                @else
                                    <a href="{{ $premiumSsoLoginUrl }}" class="mp-cta w-100">
                                        <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>Se connecter
                                    </a>
                                    <p class="mp-pay-hint mb-0 mt-2">{{ $pt('premium_guest_card_hint', 'Connecte-toi pour souscrire et régler ton adhésion en toute sécurité.') }}</p>
                                @endauth

                                <div class="mp-trust">
                                    <span><i class="fas fa-lock" aria-hidden="true"></i>Paiement chiffré</span>
                                    <span><i class="fas fa-undo" aria-hidden="true"></i>Gestion en ligne</span>
                                    <span><i class="fas fa-headset" aria-hidden="true"></i>Support Herime</span>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>

            <script type="application/json" id="community-premium-plans-json">{!! json_encode($premiumPlansPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
            <script>
                (function () {
                    var root = document.getElementById('community-premium-plans-json');
                    if (!root) return;
                    var payload;
                    try {
                        payload = JSON.parse(root.textContent || '{}');
                    } catch (e) {
                        return;
                    }
                    var plans = payload.plans || {};
                    var form = document.getElementById('community-premium-subscribe-form');
                    var buttons = document.querySelectorAll('.mp-period__btn');
                    var nameEl = document.getElementById('community-premium-plan-name');
                    var descEl = document.getElementById('community-premium-desc');
                    var hiEl = document.getElementById('community-premium-highlight');
                    var trialEl = document.getElementById('community-premium-trial');
                    var priceEl = document.getElementById('community-premium-price');
                    var billEl = document.getElementById('community-premium-billing-line');
                    var toolsEl = document.getElementById('mp-subscriber-tools');
                    var statusEl = document.getElementById('mp-subscriber-status');
                    var btnCancel = document.getElementById('mp-btn-cancel');
                    var btnResume = document.getElementById('mp-btn-resume');
                    var btnPay = document.getElementById('mp-btn-pay');
                    var hintResiliate = document.getElementById('mp-subscriber-hint-resiliate');
                    var submitLabel = document.getElementById('community-premium-submit-label');
                    var subscribeForm = document.getElementById('community-premium-subscribe-form');

                    function trialDaysLabel(n) {
                        var td = parseInt(n, 10);
                        if (!td || td < 1) return '';
                        return td === 1 ? '1 jour d\'essai gratuit' : td + ' jours d\'essai gratuit';
                    }

                    function postHiddenForm(formId, url) {
                        var f = document.getElementById(formId);
                        if (!f || !url) return;
                        f.action = url;
                        f.submit();
                    }

                    function wantsResubscribePrimaryLabel(us) {
                        if (typeof us.use_resubscribe_primary_label === 'boolean') {
                            return us.use_resubscribe_primary_label;
                        }
                        return us.status !== 'pending_payment' && us.status !== 'cancelled';
                    }

                    function expiryLabel(us) {
                        if (!us) return null;
                        if (us.period_end_label) return us.period_end_label;
                        if (us.period_end_at) {
                            var d = new Date(us.period_end_at);
                            if (!isNaN(d.getTime())) {
                                return d.toLocaleDateString('fr-FR');
                            }
                        }
                        return null;
                    }

                    function updateCtaArea(data) {
                        var us = data.user_subscription || null;
                        window.__mpCurrentPlanUserSub = us;

                        function hideAllTools() {
                            [statusEl, btnCancel, btnResume, btnPay, hintResiliate].forEach(function (el) {
                                if (el) el.classList.add('d-none');
                            });
                            if (statusEl) {
                                statusEl.textContent = '';
                                statusEl.classList.remove('text-success', 'text-danger', 'text-warning', 'text-secondary');
                            }
                        }

                        if (!us) {
                            hideAllTools();
                            if (toolsEl) toolsEl.classList.add('d-none');
                            if (submitLabel) submitLabel.textContent = 'Procéder au paiement';
                            if (subscribeForm) subscribeForm.classList.remove('d-none');
                            return;
                        }

                        hideAllTools();
                        if (toolsEl) toolsEl.classList.remove('d-none');

                        if (us.status === 'pending_payment') {
                            if (statusEl) {
                                var pendingExpiry = expiryLabel(us);
                                statusEl.textContent = pendingExpiry
                                    ? 'Paiement en attente — échéance le ' + pendingExpiry + '. Utilise « Payer » ou finalise depuis Mes abonnements.'
                                    : 'Paiement en attente — échéance: date non disponible. Utilise « Payer » ou finalise depuis Mes abonnements.';
                                statusEl.classList.add('text-warning');
                                statusEl.classList.remove('d-none');
                            }
                            if (us.show_pay && btnPay) btnPay.classList.remove('d-none');
                            if (submitLabel) {
                                submitLabel.textContent = wantsResubscribePrimaryLabel(us) ? 'Réabonnement' : 'Procéder au paiement';
                            }
                            if (subscribeForm) subscribeForm.classList.toggle('d-none', !!us.show_pay);
                            return;
                        }

                        if (us.status === 'cancelled') {
                            if (statusEl) {
                                var cancelledExpiry = expiryLabel(us);
                                statusEl.textContent = cancelledExpiry
                                    ? 'Abonnement annulé — accès maintenu jusqu\'au ' + cancelledExpiry + '.'
                                    : 'Abonnement annulé — échéance: date non disponible.';
                                statusEl.classList.add('text-secondary');
                                statusEl.classList.remove('d-none');
                            }
                            if (us.show_resume && btnResume) btnResume.classList.remove('d-none');
                            if (submitLabel) {
                                submitLabel.textContent = wantsResubscribePrimaryLabel(us) ? 'Réabonnement' : 'Procéder au paiement';
                            }
                            if (subscribeForm) subscribeForm.classList.remove('d-none');
                            return;
                        }

                        if (us.status === 'past_due') {
                            if (statusEl) {
                                var pastDueExpiry = expiryLabel(us);
                                statusEl.textContent = pastDueExpiry
                                    ? 'Paiement en retard — échéance le ' + pastDueExpiry + '. Utilise « Payer » ou « Réabonner » ci-dessous.'
                                    : 'Paiement en retard — échéance: date non disponible. Utilise « Payer » ou « Réabonner » ci-dessous.';
                                statusEl.classList.add('text-danger');
                                statusEl.classList.remove('d-none');
                            }
                            if (us.show_pay && btnPay) btnPay.classList.remove('d-none');
                            if (submitLabel) submitLabel.textContent = 'Réabonner';
                            if (subscribeForm) subscribeForm.classList.toggle('d-none', !!us.show_pay);
                            return;
                        }

                        if (statusEl) {
                            statusEl.classList.remove('text-success', 'text-danger', 'text-warning', 'text-secondary');
                            if (us.show_pay) {
                                var activeExpiry = expiryLabel(us);
                                statusEl.textContent = activeExpiry
                                    ? 'Facture en attente — échéance le ' + activeExpiry + '. Finalise avec « Finaliser le paiement » ou Mes abonnements.'
                                    : 'Facture en attente — échéance: date non disponible. Finalise avec « Finaliser le paiement » ou Mes abonnements.';
                                statusEl.classList.add('text-warning');
                            } else {
                                var subscribedExpiry = expiryLabel(us);
                                statusEl.textContent = subscribedExpiry
                                    ? 'Tu es déjà abonné. Prochaine échéance le ' + subscribedExpiry + '.'
                                    : 'Tu es déjà abonné. Échéance: date non disponible.';
                                statusEl.classList.add('text-success');
                            }
                            statusEl.classList.remove('d-none');
                        }
                        if (us.show_cancel && btnCancel) {
                            btnCancel.classList.remove('d-none');
                            if (hintResiliate) hintResiliate.classList.remove('d-none');
                        }
                        if (submitLabel) {
                            submitLabel.textContent = wantsResubscribePrimaryLabel(us) ? 'Réabonnement' : 'Procéder au paiement';
                        }
                        if (us.show_pay && btnPay) {
                            btnPay.classList.remove('d-none');
                        }
                        if (subscribeForm) {
                            subscribeForm.classList.toggle('d-none', !!us.show_pay);
                        }
                    }

                    if (btnCancel) {
                        btnCancel.addEventListener('click', function () {
                            var us = window.__mpCurrentPlanUserSub;
                            if (us && us.show_cancel) postHiddenForm('mp-form-cancel', us.cancel_url);
                        });
                    }
                    if (btnResume) {
                        btnResume.addEventListener('click', function () {
                            var us = window.__mpCurrentPlanUserSub;
                            if (us && us.show_resume) postHiddenForm('mp-form-resume', us.resume_url);
                        });
                    }
                    if (btnPay) {
                        btnPay.addEventListener('click', function () {
                            var us = window.__mpCurrentPlanUserSub;
                            if (us && us.show_pay && us.pay_url) postHiddenForm('mp-form-pay', us.pay_url);
                        });
                    }

                    function applySlug(slug) {
                        var data = plans[slug];
                        if (!data) return;
                        if (priceEl) priceEl.textContent = data.price_formatted;
                        if (billEl) {
                            billEl.textContent = data.renewal_caption || ('Facturation ' + String(data.period_label || '').toLowerCase());
                        }
                        if (nameEl) nameEl.textContent = data.name || '';
                        if (descEl) {
                            if (data.description) {
                                descEl.textContent = data.description;
                                descEl.classList.remove('d-none');
                            } else {
                                descEl.textContent = '';
                                descEl.classList.add('d-none');
                            }
                        }
                        if (hiEl) {
                            if (data.highlight) {
                                hiEl.textContent = data.highlight;
                                hiEl.classList.remove('d-none');
                            } else {
                                hiEl.textContent = '';
                                hiEl.classList.add('d-none');
                            }
                        }
                        if (trialEl) {
                            var trialText = trialDaysLabel(data.trial_days);
                            if (trialText) {
                                trialEl.textContent = trialText;
                                trialEl.classList.remove('d-none');
                            } else {
                                trialEl.textContent = '';
                                trialEl.classList.add('d-none');
                            }
                        }
                        if (form && data.subscribe_url) {
                            form.action = data.subscribe_url;
                        }
                        updateCtaArea(data);
                        buttons.forEach(function (btn) {
                            var s = btn.getAttribute('data-premium-slug');
                            if (!s) return;
                            var on = s === slug;
                            btn.classList.toggle('is-active', on);
                            btn.setAttribute('aria-selected', on ? 'true' : 'false');
                        });
                    }

                    var initial = payload.defaultSlug;
                    if (!plans[initial]) {
                        initial = Object.keys(plans)[0];
                    }
                    if (initial) applySlug(initial);

                    buttons.forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            if (btn.disabled) return;
                            var s = btn.getAttribute('data-premium-slug');
                            if (s && plans[s]) applySlug(s);
                        });
                    });
                })();
            </script>
        @endif

        <div class="text-center mt-5">
            <a href="{{ route('home') }}" class="text-muted text-decoration-none small">
                <i class="fas fa-arrow-left me-1"></i>Retour à l’accueil
            </a>
        </div>
    </div>
</section>
@endsection
