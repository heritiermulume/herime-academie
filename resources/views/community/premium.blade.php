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
    $communityPremiumPlanShort = $communityPremiumPlanShort ?? null;
    $communityPremiumPlanAnnual = $communityPremiumPlanAnnual ?? null;
    $showPremiumCard = $communityPremiumPlanShort || $communityPremiumPlanAnnual;
    $premiumPageTexts = $premiumPageTexts ?? [];
    $premiumPlanHighlights = $premiumPlanHighlights ?? [];
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
    .community-premium-hero h1 {
        font-weight: 700;
        letter-spacing: -0.02em;
    }
    .community-premium-card {
        border: 2px solid #e9ecef;
        border-radius: 16px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }
    .community-premium-card:hover {
        border-color: #003366;
        box-shadow: 0 12px 40px rgba(0, 51, 102, 0.12);
    }
    .community-premium-price {
        font-size: clamp(2rem, 5vw, 2.75rem);
        font-weight: 700;
        color: #003366;
        letter-spacing: -0.02em;
        line-height: 1.1;
    }
    .community-premium-cta {
        background: #003366;
        border: none;
        font-weight: 600;
        padding: 0.75rem 1.25rem;
        border-radius: 10px;
    }
    .community-premium-cta:hover {
        background: #002147;
    }
    .community-premium-segment {
        display: flex;
        padding: 4px;
        border-radius: 12px;
        background: #f1f3f5;
        max-width: 22rem;
        margin-left: auto;
        margin-right: auto;
        gap: 4px;
    }
    .community-premium-segment-btn {
        flex: 1;
        border: none;
        background: transparent;
        color: #495057;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 0.55rem 0.75rem;
        border-radius: 9px;
        transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    }
    .community-premium-segment-btn:hover {
        color: #003366;
    }
    .community-premium-segment-btn.is-active {
        background: #fff;
        color: #003366;
        box-shadow: 0 1px 4px rgba(0, 51, 102, 0.12);
    }
    .community-premium-segment-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
</style>

<section class="community-premium-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 text-center">
                <p class="text-uppercase small mb-2 opacity-75">{{ $pt('kicker', 'Dernière étape') }}</p>
                <h1 class="display-6 mb-3">{{ $pt('title', 'Après ça, tu fais partie des membres premium') }}</h1>
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

        @guest
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5 text-center">
                            <h2 class="h4 fw-bold mb-3" style="color: #003366;">{{ $pt('guest_box_title', 'Connecte-toi pour continuer') }}</h2>
                            <p class="text-muted mb-4">{{ $pt('guest_box_text', 'Un compte est nécessaire pour souscrire et régler ton adhésion en toute sécurité.') }}</p>
                            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                                @php
                                    $final = url()->full();
                                    $callback = route('sso.callback', ['redirect' => $final]);
                                    $ssoRegisterUrl = 'https://compte.herime.com/login?force_token=1&action=register&redirect=' . urlencode($callback);
                                    $ssoLoginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callback);
                                @endphp
                                <a href="{{ $ssoRegisterUrl }}" class="btn btn-lg text-white community-premium-cta">
                                    <i class="fas fa-user-plus me-2"></i>Créer un compte
                                </a>
                                <a href="{{ $ssoLoginUrl }}" class="btn btn-lg btn-outline-secondary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endguest

        @if($communityPlans->isEmpty() || ! $showPremiumCard)
            <div class="text-center py-5">
                <p class="text-muted mb-3">Les formules d’adhésion au réseau sont en cours de configuration.</p>
                <a href="{{ route('contact') }}" class="btn btn-outline-primary">Nous contacter</a>
                @auth
                    <a href="{{ route('customer.subscriptions') }}" class="btn btn-primary ms-2">Voir tous les abonnements</a>
                @endauth
            </div>
        @else
            <div class="row mb-4">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="h3 fw-bold mb-2" style="color: #003366;">{{ $pt('plans_intro_title', 'Choisis ta formule') }}</h2>
                    <p class="text-muted mb-0">{{ $pt('plans_intro_subtitle', 'Une offre Membre Herime : même avantages, choisis la période de facturation qui te convient.') }}</p>
                </div>
            </div>
            @php
                $planShortLabel = $communityPremiumPlanShort
                    ? ($billingLabels[$communityPremiumPlanShort->billing_period] ?? ucfirst((string) $communityPremiumPlanShort->billing_period))
                    : '';
                $defaultPremiumPlan = ($communityPremiumPlanAnnual && $communityPremiumPlanShort)
                    ? $communityPremiumPlanAnnual
                    : ($communityPremiumPlanAnnual ?? $communityPremiumPlanShort);
                $premiumSegmentCount = (int) (bool) $communityPremiumPlanShort + (int) (bool) $communityPremiumPlanAnnual;
                $premiumSsoCallback = route('sso.callback', ['redirect' => url()->full()]);
                $premiumSsoLoginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($premiumSsoCallback);
                $premiumPlansPayload = [
                    'short' => null,
                    'annual' => null,
                    'defaultKey' => ($communityPremiumPlanAnnual && $communityPremiumPlanShort) ? 'annual' : ($communityPremiumPlanAnnual ? 'annual' : 'short'),
                ];
                if ($communityPremiumPlanShort) {
                    $premiumPlansPayload['short'] = [
                        'subscribe_url' => route('subscriptions.subscribe', $communityPremiumPlanShort),
                        'price_formatted' => \App\Helpers\CurrencyHelper::formatWithSymbol(
                            $communityPremiumPlanShort->effectivePriceForCurrency($preferredCurrency),
                            $preferredCurrency
                        ),
                        'period_label' => $planShortLabel,
                        'name' => $communityPremiumPlanShort->name,
                        'description' => $communityPremiumPlanShort->description,
                        'highlight' => $premiumPlanHighlights[$communityPremiumPlanShort->slug] ?? '',
                    ];
                }
                if ($communityPremiumPlanAnnual) {
                    $annualLabel = $billingLabels[$communityPremiumPlanAnnual->billing_period] ?? 'Annuel';
                    $premiumPlansPayload['annual'] = [
                        'subscribe_url' => route('subscriptions.subscribe', $communityPremiumPlanAnnual),
                        'price_formatted' => \App\Helpers\CurrencyHelper::formatWithSymbol(
                            $communityPremiumPlanAnnual->effectivePriceForCurrency($preferredCurrency),
                            $preferredCurrency
                        ),
                        'period_label' => $annualLabel,
                        'name' => $communityPremiumPlanAnnual->name,
                        'description' => $communityPremiumPlanAnnual->description,
                        'highlight' => $premiumPlanHighlights[$communityPremiumPlanAnnual->slug] ?? '',
                    ];
                }
            @endphp
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-7 col-xl-6">
                    <div class="card community-premium-card shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <div class="text-center mb-4 pb-md-3 border-bottom border-light">
                                <h3 class="h4 fw-bold mb-2" style="color: #003366;">{{ $pt('premium_single_card_title', 'Adhésion réseau Membre Herime') }}</h3>
                                <p class="text-muted small mb-0 mx-auto" style="max-width: 42rem;">
                                    {{ $pt('premium_single_card_lead', 'Communauté privée, formations, réseau, lives et ressources premium. Choisis uniquement la fréquence de paiement qui te convient.') }}
                                </p>
                            </div>

                            @if($premiumSegmentCount > 1)
                                <div class="community-premium-segment mb-4" role="tablist" aria-label="Période de facturation">
                                    @if($communityPremiumPlanShort)
                                        <button type="button" class="community-premium-segment-btn" data-premium-key="short" aria-selected="false">
                                            {{ $planShortLabel }}
                                        </button>
                                    @endif
                                    @if($communityPremiumPlanAnnual)
                                        <button type="button" class="community-premium-segment-btn" data-premium-key="annual" aria-selected="false">
                                            Annuel
                                        </button>
                                    @endif
                                </div>
                            @endif

                            <div class="text-center px-md-2">
                                <h4 id="community-premium-plan-name" class="h6 fw-bold mb-2" style="color: #003366;">{{ $defaultPremiumPlan->name }}</h4>
                                <p id="community-premium-desc" class="text-muted small mb-2 {{ $defaultPremiumPlan->description ? '' : 'd-none' }}">{{ $defaultPremiumPlan->description }}</p>
                                <p id="community-premium-highlight" class="small text-secondary mb-3 {{ !empty($premiumPlanHighlights[$defaultPremiumPlan->slug] ?? null) ? '' : 'd-none' }}">{{ $premiumPlanHighlights[$defaultPremiumPlan->slug] ?? '' }}</p>
                                <p id="community-premium-price" class="community-premium-price mb-1">
                                    {{ \App\Helpers\CurrencyHelper::formatWithSymbol($defaultPremiumPlan->effectivePriceForCurrency($preferredCurrency), $preferredCurrency) }}
                                </p>
                                <p id="community-premium-billing-line" class="small text-muted mb-4">
                                    Facturation {{ strtolower($billingLabels[$defaultPremiumPlan->billing_period] ?? $defaultPremiumPlan->billing_period) }} · {{ $preferredCurrency }}
                                </p>

                                @auth
                                    <form id="community-premium-subscribe-form" method="POST" action="{{ route('subscriptions.subscribe', $defaultPremiumPlan) }}">
                                        @csrf
                                        <input type="hidden" name="redirect_after_subscribe" value="community.premium">
                                        <button type="submit" class="btn btn-lg text-white w-100 community-premium-cta">
                                            <i class="fas fa-lock-open me-2"></i>Procéder au paiement
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ $premiumSsoLoginUrl }}" class="btn btn-lg text-white w-100 community-premium-cta d-inline-block">
                                        <i class="fas fa-lock-open me-2"></i>Procéder au paiement
                                    </a>
                                    <p class="small text-muted mt-3 mb-0">{{ $pt('premium_pay_login_hint', 'Tu seras invité à te connecter pour finaliser le paiement en toute sécurité.') }}</p>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($premiumSegmentCount > 1)
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
                        var form = document.getElementById('community-premium-subscribe-form');
                        var buttons = document.querySelectorAll('.community-premium-segment-btn[data-premium-key]');
                        var nameEl = document.getElementById('community-premium-plan-name');
                        var descEl = document.getElementById('community-premium-desc');
                        var hiEl = document.getElementById('community-premium-highlight');
                        var priceEl = document.getElementById('community-premium-price');
                        var billEl = document.getElementById('community-premium-billing-line');

                        function applyKey(key) {
                            var data = payload[key];
                            if (!data) return;
                            if (priceEl) priceEl.textContent = data.price_formatted;
                            if (billEl) {
                                billEl.textContent = 'Facturation ' + String(data.period_label || '').toLowerCase() + ' · {{ $preferredCurrency }}';
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
                            if (form && data.subscribe_url) {
                                form.action = data.subscribe_url;
                            }
                            buttons.forEach(function (btn) {
                                var k = btn.getAttribute('data-premium-key');
                                var on = k === key;
                                btn.classList.toggle('is-active', on);
                                btn.setAttribute('aria-selected', on ? 'true' : 'false');
                            });
                        }

                        var initial = payload.defaultKey;
                        if (!payload[initial]) {
                            initial = payload.short ? 'short' : 'annual';
                        }
                        applyKey(initial);

                        buttons.forEach(function (btn) {
                            btn.addEventListener('click', function () {
                                var k = btn.getAttribute('data-premium-key');
                                if (k && payload[k]) applyKey(k);
                            });
                        });
                    })();
                </script>
            @endif
        @endif

        <div class="text-center mt-5">
            <a href="{{ route('home') }}" class="text-muted text-decoration-none small">
                <i class="fas fa-arrow-left me-1"></i>Retour à l’accueil
            </a>
        </div>
    </div>
</section>
@endsection
