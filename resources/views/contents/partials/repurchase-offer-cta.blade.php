{{--
  Boutons d’achat additionnel / cadeau lorsque l’utilisateur a déjà accès (payant uniquement).
  @var \App\Models\Course $course
  @var string $layout 'desktop' | 'mobile'
--}}
@php
    $layout = $layout ?? 'desktop';
@endphp
@if(! $course->is_free && ($course->is_sale_enabled ?? true))
    @if($layout === 'desktop')
        <div class="repurchase-offer-cta border-top pt-3 mt-3">
            <h6 class="fw-semibold mb-1" style="font-size: 0.9375rem;">
                <i class="fas fa-gift me-1 text-primary"></i>Acheter à nouveau / Offrir
            </h6>
            <p class="small text-muted mb-3">Un nouvel achat peut servir à offrir l’accès à une autre personne (compte distinct après commande).</p>
            <div class="d-grid gap-2">
                @if(! ($course->is_in_person_program ?? false))
                    <button type="button" class="btn btn-outline-primary btn-lg w-100" onclick="addToCart({{ $course->id }})">
                        <i class="fas fa-shopping-cart me-2"></i>Ajouter au panier
                    </button>
                @endif
                <button type="button" class="btn btn-success btn-lg w-100" onclick="proceedToCheckout({{ $course->id }})">
                    <i class="fas fa-credit-card me-2"></i>Procéder au paiement
                </button>
            </div>
        </div>
    @else
        <div class="repurchase-offer-cta mobile-price-slider__repurchase mt-2 pt-2 border-top border-secondary border-opacity-25">
            <div class="small text-muted text-center mb-2 px-1">
                <strong class="text-body d-block mb-1"><i class="fas fa-gift me-1 text-primary"></i>Acheter à nouveau / Offrir</strong>
                <span class="d-block" style="font-size: 0.75rem;">Nouvel achat (ex. cadeau)</span>
            </div>
            @if(! ($course->is_in_person_program ?? false))
                <div class="mobile-price-slider__btn-group">
                    <button type="button" class="mobile-price-slider__btn mobile-price-slider__btn--outline" onclick="addToCart({{ $course->id }})">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Panier</span>
                    </button>
                    <button type="button" class="mobile-price-slider__btn mobile-price-slider__btn--success" onclick="proceedToCheckout({{ $course->id }})">
                        <i class="fas fa-credit-card"></i>
                        <span>Payer</span>
                    </button>
                </div>
            @else
                <button type="button" class="mobile-price-slider__btn mobile-price-slider__btn--success w-100" onclick="proceedToCheckout({{ $course->id }})">
                    <i class="fas fa-credit-card"></i>
                    <span>Payer</span>
                </button>
            @endif
        </div>
    @endif
@endif
