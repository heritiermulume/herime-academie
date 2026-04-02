@php
    $plan = $plan ?? null;
    $preferredCurrency = strtoupper((string) ($preferredCurrency ?? 'USD'));
    $premiumPlanHighlights = $premiumPlanHighlights ?? [];
    $periodLabel = $periodLabel ?? '';
@endphp
<div class="community-premium-option-col h-100 d-flex flex-column p-4 rounded-3 {{ $plan ? 'bg-light bg-opacity-50 border border-light-subtle' : 'bg-light bg-opacity-25 border border-dashed border-secondary-subtle' }}">
    @if($plan)
        @php
            $localizedAmount = $plan->effectivePriceForCurrency($preferredCurrency);
        @endphp
        <div class="mb-2">
            <span class="badge rounded-pill px-3 py-2" style="background: #003366;">{{ $periodLabel }}</span>
        </div>
        <h4 class="h6 fw-bold mb-2" style="color: #003366;">{{ $plan->name }}</h4>
        @if($plan->description)
            <p class="text-muted small flex-grow-1 mb-2">{{ $plan->description }}</p>
        @endif
        @if(!empty($premiumPlanHighlights[$plan->slug] ?? null))
            <p class="small text-secondary mb-2">{{ $premiumPlanHighlights[$plan->slug] }}</p>
        @endif
        <p class="community-premium-price mb-1">
            {{ \App\Helpers\CurrencyHelper::formatWithSymbol($localizedAmount, $preferredCurrency) }}
        </p>
        <p class="small text-muted mb-4">Facturation {{ strtolower($periodLabel) }} · {{ $preferredCurrency }}</p>
        @auth
            <form method="POST" action="{{ route('subscriptions.subscribe', $plan) }}" class="mt-auto">
                @csrf
                <input type="hidden" name="redirect_after_subscribe" value="community.premium">
                <button type="submit" class="btn btn-lg text-white w-100 community-premium-cta">
                    <i class="fas fa-lock-open me-2"></i>Procéder au paiement
                </button>
            </form>
        @else
            <p class="small text-muted mt-auto mb-0">Connecte-toi ci-dessus pour souscrire.</p>
        @endauth
    @else
        <div class="text-center text-muted py-4 my-auto">
            <i class="fas fa-clock fa-2x mb-3 opacity-50"></i>
            <p class="small mb-0">Formule « {{ $periodLabel }} » en cours de configuration.</p>
        </div>
    @endif
</div>
