@extends('layouts.app')

@section('title', 'Rejoindre le réseau — Membre premium | Herime Académie')
@section('description', 'Dernière étape pour accéder à la communauté privée Membre Herime : choisissez la facturation trimestrielle ou annuelle et finalisez votre adhésion.')

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
        font-size: 1.75rem;
        font-weight: 700;
        color: #003366;
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
    @media (min-width: 768px) {
        .community-premium-col-divider {
            border-left: 1px solid #e9ecef;
        }
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
                    <p class="text-muted mb-0">{{ $pt('plans_intro_subtitle', 'Une offre Membre Herime : facturation trimestrielle ou annuelle, mêmes avantages.') }}</p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">
                    <div class="card community-premium-card shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <div class="text-center mb-4 pb-md-3 border-bottom border-light">
                                <h3 class="h4 fw-bold mb-2" style="color: #003366;">{{ $pt('premium_single_card_title', 'Adhésion réseau Membre Herime') }}</h3>
                                <p class="text-muted small mb-0 mx-auto" style="max-width: 42rem;">
                                    {{ $pt('premium_single_card_lead', 'Communauté privée, formations, réseau, lives et ressources premium. Choisis uniquement la fréquence de paiement qui te convient.') }}
                                </p>
                            </div>
                            <div class="row g-4 align-items-stretch">
                                <div class="col-md-6">
                                    @include('community.partials.premium-plan-column', [
                                        'plan' => $communityPremiumPlanShort,
                                        'preferredCurrency' => $preferredCurrency,
                                        'premiumPlanHighlights' => $premiumPlanHighlights,
                                        'periodLabel' => $communityPremiumPlanShort
                                            ? ($billingLabels[$communityPremiumPlanShort->billing_period] ?? ucfirst((string) $communityPremiumPlanShort->billing_period))
                                            : 'Trimestriel',
                                    ])
                                </div>
                                <div class="col-md-6">
                                    <div class="community-premium-col-divider h-100 ps-md-4">
                                        @include('community.partials.premium-plan-column', [
                                            'plan' => $communityPremiumPlanAnnual,
                                            'preferredCurrency' => $preferredCurrency,
                                            'premiumPlanHighlights' => $premiumPlanHighlights,
                                            'periodLabel' => $communityPremiumPlanAnnual
                                                ? ($billingLabels[$communityPremiumPlanAnnual->billing_period] ?? ucfirst((string) $communityPremiumPlanAnnual->billing_period))
                                                : 'Annuel',
                                        ])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="text-center mt-5">
            <a href="{{ route('home') }}" class="text-muted text-decoration-none small">
                <i class="fas fa-arrow-left me-1"></i>Retour à l’accueil
            </a>
        </div>
    </div>
</section>
@endsection
