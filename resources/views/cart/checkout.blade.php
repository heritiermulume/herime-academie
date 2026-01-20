@extends('layouts.app')

@section('title', 'Checkout - Paiement - Herime Academie')
@section('description', 'Finalisez votre commande et payez vos cours en toute sécurité.')

@section('content')
<div class="checkout-page">
    <!-- Header Section -->
    <div class="checkout-header">
        <div class="checkout-wrapper">
            <div class="checkout-title-section">
                <h1 class="checkout-title">Finaliser votre commande</h1>
                <p class="checkout-subtitle">Choisissez votre mode de paiement</p>
            </div>
            <div class="checkout-actions">
                <a href="{{ route('cart.index') }}" class="continue-shopping-btn">
                    <i class="fas fa-arrow-left"></i>
                    Retour au panier
                </a>
            </div>
        </div>
    </div>

    <div class="checkout-wrapper">
        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-12 col-lg-8 order-2 order-lg-1">
                <!-- Progress Steps -->
                <div class="checkout-progress mb-4">
                    <div class="progress-steps">
                        <div class="step completed">
                            <div class="step-circle">
                                <span class="step-number">1</span>
                                <i class="fas fa-check step-check"></i>
                            </div>
                            <div class="step-label">Informations</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step completed">
                            <div class="step-circle">
                                <span class="step-number">2</span>
                                <i class="fas fa-check step-check"></i>
                            </div>
                            <div class="step-label">Paiement</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step pending">
                            <div class="step-circle">
                                <span class="step-number">3</span>
                                <i class="fas fa-check step-check"></i>
                            </div>
                            <div class="step-label">Confirmation</div>
                        </div>
                    </div>
                </div>

                <!-- Moneroo Payment -->
                <div class="payment-section">
                    <h4 class="section-title mb-4">
                        <i class="fas fa-mobile-alt me-2"></i>Paiement Mobile Money via Moneroo
                    </h4>
                    
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Paiement sécurisé</strong><br>
                        Vous serez redirigé vers la page de paiement sécurisée de Moneroo pour compléter votre transaction.
                        Vous pourrez y sélectionner votre pays, opérateur mobile money et saisir votre numéro de téléphone.
                    </div>

                    <form id="monerooForm" method="POST" onsubmit="return false;">
                        @csrf
                        
                        <div class="form-section">
                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-coins me-1"></i>Montant à payer</label>
                                <div class="d-flex align-items-center gap-2">
                                    <strong class="fs-4 text-primary">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($total) }}</strong>
                                    <small class="text-muted">({{ config('services.moneroo.default_currency') }})</small>
                                </div>
                                <small class="form-text text-muted">Le montant sera converti automatiquement selon votre opérateur sur la page Moneroo.</small>
                            </div>

                            <!-- Code Promo Ambassadeur -->
                            <div class="mb-3 promo-code-container">
                                <label class="form-label">
                                    <i class="fas fa-gift me-1"></i>Code Promo Ambassadeur (optionnel)
                                </label>
                                <div class="input-group">
                                    <input type="text" 
                                           id="ambassadorPromoCode" 
                                           class="form-control" 
                                           placeholder="Entrez le code promo d'un ambassadeur"
                                           autocomplete="off">
                                    <button type="button" 
                                            class="btn btn-outline-secondary" 
                                            id="validatePromoCodeBtn"
                                            onclick="validatePromoCode()">
                                        <i class="fas fa-check"></i> Valider
                                    </button>
                                </div>
                                <small class="form-text text-muted">
                                    Si vous avez un code promo d'un ambassadeur, entrez-le ici. L'ambassadeur bénéficiera d'une commission sur votre achat.
                                </small>
                                <div id="promoCodeFeedback" class="mt-2" style="display:none;"></div>
                            </div>

                            <div class="terms-section mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        J'accepte les <a href="{{ route('legal.terms') }}" target="_blank" class="text-primary">conditions générales</a> et la <a href="{{ route('legal.privacy') }}" target="_blank" class="text-primary">politique de confidentialité</a>
                                    </label>
                                    <div class="invalid-feedback" style="display:none;" id="termsError"></div>
                                </div>
                            </div>

                            <input type="hidden" id="amount" value="{{ number_format($total, 2, '.', '') }}">
                            <input type="hidden" id="currency" value="{{ config('services.moneroo.default_currency') }}">
                        </div>

                        <div class="payment-actions mt-4">
                            <button type="button" id="payButton" class="btn btn-primary btn-lg w-100">
                                <span id="payButtonText">
                                    <i class="fas fa-credit-card me-2"></i>Payer avec Moneroo
                                </span>
                            </button>
                        </div>

                        <div id="paymentNotice" class="alert alert-info mt-3" style="display:none;"></div>
                    </form>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-12 col-lg-4 order-1 order-lg-2">
                <div class="order-summary-card">
                    <h5 class="summary-title mb-3">Résumé de la commande</h5>
                    <div class="order-items">
                        @foreach($cartItems as $item)
                        <div class="order-item">
                            <div class="item-info">
                                <h6 class="item-title">{{ $item['course']->title ?? 'Cours' }}</h6>
                                <p class="item-instructor text-muted">Par {{ $item['course']->provider->name ?? '' }}</p>
                            </div>
                            <div class="item-price">
                                {{ \App\Helpers\CurrencyHelper::formatWithSymbol($item['subtotal']) }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="order-total mt-3">
                        <div class="d-flex justify-content-between">
                            <strong>Total :</strong>
                            <strong class="text-primary fs-4">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($total) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Éléments simplifiés pour intégration standard Moneroo
    const amountInput = document.getElementById('amount');
    const currencyInput = document.getElementById('currency');
    const payButton = document.getElementById('payButton');
    const payButtonText = document.getElementById('payButtonText');
    const paymentNotice = document.getElementById('paymentNotice');
    const termsCheckbox = document.getElementById('terms');
    
    // IMPORTANT: Le montant de base est dans la devise de base du site (configurée dynamiquement dans /admin/settings)
    // Ce montant provient des prix des cours stockés dans la base de données (dans la devise de base)
    let baseAmount = parseFloat(amountInput.value) || 0; // Montant original dans la devise de base du site
    const baseCurrency = '{{ $baseCurrency ?? "USD" }}'; // Devise de base configurée dynamiquement dans l'admin
    
    let exchangeRates = {}; // Cache des taux de change

    async function loadCountries() {
        // Cette fonction n'est plus utilisée pour l'intégration standard Moneroo
        // Moneroo collectera les informations sur leur page de paiement
        // Conservée pour compatibilité mais ne fait rien
        return;
    }

    function onCountryChange() {
        // Fonction désactivée - non utilisée pour intégration standard Moneroo
    }

    function renderProviders(providers) {
        // Fonction désactivée - non utilisée pour intégration standard Moneroo
    }

    function setupCurrenciesForProvider(provider) {
        // Fonction désactivée - non utilisée pour intégration standard Moneroo
    }

    /**
     * Convertir le montant de la devise de base du site vers la devise sélectionnée par l'utilisateur
     * @param {string} targetCurrency - La devise cible sélectionnée (ex: CDF, XOF, etc.)
     */
    async function convertAmount(targetCurrency) {
        // Plus nécessaire pour intégration standard Moneroo
        // Moneroo gérera la conversion sur leur page
        if (amountInput) {
            amountInput.value = baseAmount.toFixed(2);
        }
    }
    
    /**
     * Récupérer le taux de change entre deux devises
     * @param {string} from - Devise source (doit être la devise de base du site : baseCurrency)
     * @param {string} to - Devise cible (devise sélectionnée par l'utilisateur)
     * @returns {Promise<number>} Taux de change
     */
    async function getExchangeRate(from, to) {
        const cacheKey = `${from}_${to}`;
        
        // Utiliser le cache s'il est encore valide (5 minutes)
        if (exchangeRates[cacheKey] && (Date.now() - exchangeRates[cacheKey].timestamp) < 300000) {
            return exchangeRates[cacheKey].rate;
        }
        
        // Utiliser exchangerate-api.com pour récupérer les taux de change
        // from = devise de base du site (baseCurrency), to = devise cible sélectionnée
        try {
            const response = await fetch(`https://api.exchangerate-api.com/v4/latest/${from}`);
            if (!response.ok) throw new Error('Erreur API taux de change');
            
            const data = await response.json();
            const rate = data.rates[to];
            
            if (!rate) {
                throw new Error(`Taux non trouvé pour ${to}`);
            }
            
            // Mettre en cache
            exchangeRates[cacheKey] = {
                rate: rate,
                timestamp: Date.now()
            };
            
            return rate;
        } catch (error) {
            throw error;
        }
    }
    
    function currencyChanged() {
        // Fonction désactivée - non utilisée pour intégration standard Moneroo
    }
    
    function findProviderByCode(providerCode) {
        // Fonction désactivée - non utilisée pour intégration standard Moneroo
        return null;
    }

    function setInvalid(el, msgEl, message) {
        el.classList.add('is-invalid');
        if (msgEl) { msgEl.textContent = message || ''; }
    }

    function clearInvalid(el, msgEl) {
        el.classList.remove('is-invalid');
        if (msgEl) { msgEl.textContent = ''; }
    }

    function validatePhone() {
        // Plus nécessaire pour intégration standard Moneroo
        // Moneroo validera le téléphone sur leur page
        return true;
    }

    function validateTerms() {
        const ok = !!termsCheckbox.checked;
        if (!ok) setInvalid(termsCheckbox, document.getElementById('termsError'), 'Veuillez accepter les conditions.');
        else clearInvalid(termsCheckbox, document.getElementById('termsError'));
        return ok;
    }

    function validateAmount() {
        // Validation simplifiée pour intégration standard Moneroo
        const val = parseFloat(amountInput.value);
        if (Number.isNaN(val) || val <= 0) {
            return false;
        }
        return true;
    }

    function updatePayButtonState() {
        // Pour l'intégration standard Moneroo, seule la validation des termes est nécessaire
        const ready = validateTerms();
        payButton.disabled = !ready;
    }

    async function initiateDeposit() {
        // Validation minimale pour intégration standard Moneroo
        if (!validateTerms()) {
            updatePayButtonState();
            return;
        }
        
        payButton.disabled = true;
        payButtonText.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Initialisation…';
        paymentNotice.style.display = 'none';

        // Récupérer le code promo ambassadeur si fourni
        const ambassadorPromoCodeInput = document.getElementById('ambassadorPromoCode');
        const ambassadorPromoCode = ambassadorPromoCodeInput ? ambassadorPromoCodeInput.value.trim().toUpperCase() : '';

        // Payload simplifié pour intégration standard Moneroo
        // Moneroo collectera pays, opérateur et téléphone sur leur page
        const payload = {
            amount: parseFloat(amountInput.value) || baseAmount, // Montant dans la devise de base
            currency: currencyInput.value || '{{ config('services.moneroo.default_currency') }}', // Devise de base
            ambassador_promo_code: ambassadorPromoCode || null, // Code promo ambassadeur optionnel
            _token: '{{ csrf_token() }}'
        };

        // Ajout d'un timeout pour éviter l'attente infinie
        const controller = new AbortController();
        const timeoutMs = 30000; // 30 secondes
        const timeoutId = setTimeout(() => controller.abort(), timeoutMs);
        let res;
        try {
            res = await fetch(`{{ route('moneroo.initiate') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify(payload),
                signal: controller.signal
            });
        } catch (err) {
            clearTimeout(timeoutId);
            payButton.disabled = false;
            payButtonText.innerHTML = '<i class="fas fa-credit-card me-2"></i>Payer maintenant';
            paymentNotice.className = 'alert alert-warning mt-2';
            paymentNotice.innerText = 'Temps dépassé lors de l\'initialisation du paiement. La commande a été annulée.';
            paymentNotice.style.display = 'block';
            // Annuler la dernière commande en attente côté serveur
            try { await fetch(`{{ url('/moneroo/cancel-latest') }}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }); } catch(e){}
            return;
        } finally {
            clearTimeout(timeoutId);
        }

        let data;
        try {
            data = await res.json();
        } catch (e) {
            payButton.disabled = false;
            payButtonText.innerHTML = '<i class="fas fa-credit-card me-2"></i>Payer maintenant';
            paymentNotice.className = 'alert alert-danger mt-2';
            paymentNotice.innerText = 'Réponse invalide du serveur. Veuillez réessayer.';
            paymentNotice.style.display = 'block';
            return;
        }
        if (!res.ok || data.success === false) {
            payButton.disabled = false;
            payButtonText.innerHTML = '<i class="fas fa-credit-card me-2"></i>Payer maintenant';
            paymentNotice.className = 'alert alert-danger mt-2';
            paymentNotice.innerText = (data && data.message) ? data.message : 'Échec de l\'initialisation du paiement. Veuillez réessayer.';
            paymentNotice.style.display = 'block';
            return;
        }

        // Intégration standard Moneroo : redirection obligatoire vers leur page de checkout
        // Format selon la documentation: data.checkout_url
        const paymentId = data.payment_id || data.data?.id || data.id;
        const redirectUrl = data.checkout_url 
                         || data.data?.checkout_url
                         || data.redirect_url 
                         || data.authorizationUrl 
                         || data.authorization_url
                         || data.data?.redirect_url 
                         || data.data?.authorizationUrl
                         || data.data?.authorization_url
                         || data.payment_url
                         || data.url;
        
        if (redirectUrl) {
            // Rediriger immédiatement vers la page de paiement Moneroo
            // Moneroo gérera la collecte des informations (pays, opérateur, téléphone)
            window.location.href = redirectUrl;
            return;
        }

        // Si pas d'URL de redirection, c'est une erreur
        payButton.disabled = false;
        payButtonText.innerHTML = '<i class="fas fa-credit-card me-2"></i>Payer maintenant';
        paymentNotice.className = 'alert alert-danger mt-2';
        paymentNotice.textContent = 'Erreur : Impossible d\'obtenir l\'URL de paiement Moneroo. Veuillez réessayer ou contacter le support.';
        paymentNotice.style.display = 'block';
        console.error('Moneroo: Pas d\'URL de redirection dans la réponse', data);
    }


    // Polling pour le statut final du paiement
    // Selon la documentation Moneroo officielle:
    // - Ne PAS imposer de timeout strict (Moneroo gère les délais)
    // - Le webhook est la source de vérité
    // - Le polling sert uniquement au feedback immédiat utilisateur
    async function pollStatus(paymentId) {
        let stopped = false;
        let lastStatus = null;
        let startTime = Date.now();
        const MAX_DURATION = 10 * 60 * 1000; // 10 minutes maximum (durée raisonnable pour UX)
        
        const poll = async () => {
            if (stopped) return;
            
            // Vérifier si la durée maximale est atteinte (pour UX, pas pour timeout payment)
            const elapsed = Date.now() - startTime;
            if (elapsed > MAX_DURATION) {
                stopped = true;
                paymentNotice.className = 'alert alert-info mt-3';
                paymentNotice.innerHTML = `
                    <strong>Paiement en cours de traitement...</strong><br>
                    Le traitement peut prendre du temps. Vous pouvez fermer cette page.
                    <br><br>
                    <strong>Vous recevrez automatiquement un email de confirmation.</strong><br>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-list me-1"></i>Voir mes commandes
                    </a>
                `;
                payButton.disabled = false;
                payButtonText.innerHTML = '<i class="fas fa-envelope me-2"></i>Confirmation par email';
                return;
            }
            
            // Vérifier le statut
            try {
                const res = await fetch(`{{ url('/moneroo/status') }}/${paymentId}`);
                if (!res.ok) {
                    setTimeout(poll, 2000);
                    return;
                }
                
                const data = await res.json();
                const status = data.status;
                const nextStep = data.nextStep;
                // Gérer le cas NOT_FOUND
                if (status === 'NOT_FOUND') {
                    stopped = true;
                    paymentNotice.className = 'alert alert-danger mt-3';
                    paymentNotice.innerHTML = `
                        <strong>Transaction introuvable</strong><br>
                        La transaction n'a pas été trouvée dans le système Moneroo.
                        Veuillez contacter le support si vous avez approuvé le paiement.
                    `;
                    payButton.disabled = false;
                    payButtonText.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Problème détecté';
                    return;
                }
                
                // Détecter si le statut a changé
                if (status !== lastStatus) {
                    lastStatus = status;
                }
                
                // Gérer tous les statuts possibles selon la documentation Moneroo
                if (status === 'completed') {
                    stopped = true;
                    paymentNotice.className = 'alert alert-success mt-3';
                    paymentNotice.textContent = 'Paiement réussi ! Redirection…';
                    payButtonText.textContent = 'Paiement réussi';
                    
                    // Rediriger vers la page de succès
                    setTimeout(() => {
                        window.location.href = `{{ route('moneroo.success') }}?payment_id=${paymentId}`;
                    }, 1000);
                    
                } else if (status === 'failed' || status === 'cancelled' || status === 'expired' || status === 'rejected') {
                    stopped = true;
                    paymentNotice.className = 'alert alert-danger mt-3';
                    paymentNotice.textContent = 'Le paiement a échoué. Veuillez réessayer.';
                    payButton.disabled = false;
                    payButtonText.innerHTML = '<i class="fas fa-credit-card me-2"></i>Réessayer';
                    
                } else if (status === 'pending' || status === 'processing') {
                    // En cours de traitement : continuer à poller
                    if (status !== lastStatus) {
                        paymentNotice.className = 'alert alert-info mt-3';
                        paymentNotice.innerHTML = `
                            <strong>Paiement en cours de traitement...</strong><br>
                            Veuillez approuver le paiement sur votre téléphone.
                        `;
                    }
                    setTimeout(poll, 2000);
                    
                } else {
                    // Statut inconnu : continuer à poller
                    setTimeout(poll, 2000);
                }
            } catch (error) {
                setTimeout(poll, 2000);
            }
        };
        
        poll();
    }

    // Plus besoin d'écouter les changements - Moneroo gère tout sur leur page
    // countrySelect.addEventListener('change', onCountryChange);
    // Fonction pour valider le code promo ambassadeur
    async function validatePromoCode() {
        const promoCodeInput = document.getElementById('ambassadorPromoCode');
        const feedbackDiv = document.getElementById('promoCodeFeedback');
        const validateBtn = document.getElementById('validatePromoCodeBtn');
        
        if (!promoCodeInput || !feedbackDiv) return;
        
        const code = promoCodeInput.value.trim().toUpperCase();
        
        if (!code) {
            feedbackDiv.style.display = 'none';
            return;
        }
        
        validateBtn.disabled = true;
        validateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        try {
            // Vérifier le code via une route dédiée ou via l'API
            const response = await fetch(`{{ route('moneroo.initiate') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    validate_promo_code: true,
                    ambassador_promo_code: code,
                    _token: '{{ csrf_token() }}'
                })
            });
            
            const data = await response.json();
            
            if (data.valid === true) {
                feedbackDiv.className = 'alert alert-success mt-2';
                feedbackDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>Code promo valide !';
                feedbackDiv.style.display = 'block';
                promoCodeInput.classList.remove('is-invalid');
                promoCodeInput.classList.add('is-valid');
            } else {
                feedbackDiv.className = 'alert alert-danger mt-2';
                feedbackDiv.innerHTML = '<i class="fas fa-times-circle me-2"></i>' + (data.message || 'Code promo invalide');
                feedbackDiv.style.display = 'block';
                promoCodeInput.classList.remove('is-valid');
                promoCodeInput.classList.add('is-invalid');
            }
        } catch (error) {
            feedbackDiv.className = 'alert alert-warning mt-2';
            feedbackDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Erreur lors de la validation';
            feedbackDiv.style.display = 'block';
        } finally {
            validateBtn.disabled = false;
            validateBtn.innerHTML = '<i class="fas fa-check"></i> Valider';
        }
    }

    // Valider le code promo lors de la saisie (avec debounce)
    let promoCodeTimeout;
    const promoCodeInput = document.getElementById('ambassadorPromoCode');
    if (promoCodeInput) {
        promoCodeInput.addEventListener('input', function() {
            clearTimeout(promoCodeTimeout);
            const code = this.value.trim();
            const feedbackDiv = document.getElementById('promoCodeFeedback');
            
            if (code.length >= 6) {
                promoCodeTimeout = setTimeout(() => {
                    validatePromoCode();
                }, 1000);
            } else if (code.length === 0) {
                if (feedbackDiv) {
                    feedbackDiv.style.display = 'none';
                }
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
    }

    payButton.addEventListener('click', initiateDeposit);
    termsCheckbox.addEventListener('change', () => { validateTerms(); updatePayButtonState(); });
    // Plus besoin de charger les pays/opérateurs - Moneroo le fera sur leur page
    updatePayButtonState();
});
</script>

@endpush

@push('styles')
<style>
/* Checkout Page */
.checkout-page {
    background-color: #f7f9fa;
}

.checkout-header {
    background-color: #fff;
    border-bottom: 1px solid #e5e5e5;
    padding: 12px 0;
    margin-bottom: 16px;
}

.checkout-header .checkout-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
}

.checkout-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}

.checkout-page .checkout-wrapper {
    padding: 24px;
}

/* Ensure proper spacing */
.checkout-page .row.g-4 {
    --bs-gutter-x: 1.5rem;
    --bs-gutter-y: 1.5rem;
}

.checkout-title-section {
    flex: 1;
}

.checkout-title {
    font-size: 32px;
    font-weight: 700;
    color: #1c1d1f;
    margin: 0 0 4px 0;
    line-height: 1.2;
}

.checkout-subtitle {
    font-size: 16px;
    color: #6a6f73;
    margin: 0;
    line-height: 1.3;
}

.checkout-actions {
    display: flex;
    align-items: center;
}

.continue-shopping-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background-color: transparent;
    color: #003366;
    text-decoration: none;
    border: 1px solid #003366;
    border-radius: 4px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s ease;
}

.continue-shopping-btn:hover {
    background-color: #003366;
    color: white;
    text-decoration: none;
}

/* Progress Steps */
.checkout-progress {
    padding: 20px 0;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}

.step-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #e9ecef;
    border: 3px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.3s ease;
}

.step-number {
    font-size: 18px;
    font-weight: 700;
    color: #6c757d;
}

.step-check {
    display: none;
    color: white;
    font-size: 18px;
}

.step.completed .step-circle {
    background-color: #28a745;
    border-color: #28a745;
}

.step.completed .step-number {
    display: none;
}

.step.completed .step-check {
    display: block;
}

.step.pending .step-circle {
    background-color: #fff;
    border-color: #e9ecef;
}

.step-label {
    margin-top: 10px;
    font-size: 14px;
    color: #6c757d;
    font-weight: 500;
    text-align: center;
}

.step.completed .step-label {
    color: #28a745;
}

.step-line {
    flex: 1;
    height: 3px;
    background-color: #e9ecef;
    margin: 0 10px;
    margin-top: -20px;
}

.step.completed ~ .step-line {
    background-color: #28a745;
}

/* Payment Options */
.payment-option {
    cursor: pointer;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.3s ease;
}

.payment-option:hover {
    border-color: #003366;
    background-color: #f8f9fa;
}

.payment-radio {
    display: none;
}

.payment-radio:checked + .payment-label {
    border-color: #003366;
    background-color: #e3f2fd;
}

.payment-label {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 10px;
    border-radius: 8px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.payment-icon {
    font-size: 2rem;
    color: #003366;
}

/* Payment Section */
.payment-section {
    background: white;
    border-radius: 8px;
    padding: 28px;
    margin-bottom: 16px;
}

/* Payment Actions */
.payment-actions {
    width: 100%;
    padding: 0 1rem;
    box-sizing: border-box;
}

.payment-actions #payButton {
    width: 100%;
    max-width: 100%;
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    color: #1c1d1f;
    margin-bottom: 20px;
}

.section-title i {
    color: #003366;
}

/* Payment form visibility is controlled by inline styles, so no CSS needed */

/* Styles des champs de formulaire - Desktop (tailles appropriées) */
@media (min-width: 992px) {
    .checkout-page .form-label {
        font-size: 14px;
        margin-bottom: 6px;
        font-weight: 500;
    }

    .checkout-page .form-control,
    .checkout-page .form-select {
        font-size: 15px;
        padding: 10px 12px;
        border-radius: 6px;
    }

    /* Aligner la hauteur du champ montant avec le champ devise - uniquement pour ces champs spécifiques */
    .checkout-page #currencySelect,
    .checkout-page #amount {
        height: 45px;
        line-height: 1.5;
        box-sizing: border-box;
        width: 100%;
    }

    .checkout-page .form-control-sm {
        font-size: 13px;
        padding: 7px 10px;
    }

    .checkout-page .form-select.form-select-sm {
        font-size: 13px;
        padding: 7px 10px;
    }

    .checkout-page .form-text {
        font-size: 13px;
        margin-top: 4px;
    }

    .checkout-page .invalid-feedback {
        font-size: 13px;
        margin-top: 4px;
    }

    .checkout-page .alert {
        padding: 12px 16px;
        font-size: 14px;
        margin-bottom: 0.75rem;
    }

    .checkout-page .alert i {
        font-size: 14px;
    }

    /* Input group pour desktop */
    .checkout-page .input-group {
        flex-wrap: wrap;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }

    .checkout-page .input-group .form-control {
        flex: 1 1 auto;
        min-width: 0;
        max-width: calc(100% - 100px);
        box-sizing: border-box;
        overflow: hidden;
        font-size: 15px;
        padding: 10px 12px;
    }

    .checkout-page .input-group .btn {
        white-space: nowrap;
        padding: 10px 16px;
        font-size: 14px;
        flex-shrink: 0;
        min-width: 90px;
        max-width: 100px;
        box-sizing: border-box;
    }

    .checkout-page #validatePromoCodeBtn {
        padding: 10px 16px;
        font-size: 14px;
        min-width: 90px;
        max-width: 100px;
    }

    /* Terms section pour desktop */
    .checkout-page .terms-section .form-check {
        align-items: center; /* centrer verticalement */
    }
    
    .checkout-page .terms-section .form-check-label {
        font-size: 14px;
    }

    .checkout-page .terms-section .form-check-input {
        width: 1.25em;
        height: 1.25em;
        margin-top: 0; /* centré verticalement */
    }
}

.benefit-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-right: 15px;
    margin-bottom: 10px;
}

.order-summary-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    position: sticky;
    top: 20px;
    height: fit-content;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
    gap: 12px;
}

.order-item:last-of-type {
    border-bottom: none;
}

.item-info {
    flex: 1;
}

.item-title {
    font-size: 14px;
    font-weight: 600;
    margin: 0;
    margin-bottom: 5px;
}

.item-instructor {
    font-size: 12px;
    margin: 0;
}

.item-price {
    font-weight: 700;
    color: #003366;
}

.summary-title {
    font-size: 18px;
    font-weight: 700;
    color: #003366;
}

.order-total {
    padding-top: 15px;
    border-top: 2px solid #003366;
}

/* Responsive Design - Comme le panier */
@media (max-width: 1024px) {
    .checkout-wrapper {
        max-width: 100%;
        padding: 0 16px;
    }
    
    .checkout-header .checkout-wrapper {
        flex-direction: row;
    }
}

@media (max-width: 991.98px) {
    /* Réduire les paddings et margins sur tablette - style analytics */
    .checkout-page {
        padding-bottom: 70px;
    }
    
    .checkout-wrapper {
        padding: 0 0.25rem;
    }
    
    .checkout-page .checkout-wrapper {
        padding: 0.25rem;
    }
    
    .checkout-header {
        padding: 0.25rem 0;
        margin-bottom: 0.25rem;
    }
    
    /* Colonnes s'empilent sur tablette */
    .row.g-4 {
        margin: 0 !important;
        --bs-gutter-x: 0.25rem;
        --bs-gutter-y: 0.25rem;
    }
    
    .row.g-4 > .col-12 {
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-bottom: 0.125rem !important;
    }
    
    .order-summary-card {
        position: relative;
        margin: 0 !important;
        padding: 0.375rem;
        top: 0;
        width: 100%;
        max-width: 100%;
    }
    
    .continue-shopping-btn {
        font-size: 12px !important;
        padding: 8px 12px !important;
    }
    
    /* Progress section padding sur tablette */
    .checkout-progress {
        background: white;
        border-radius: 8px;
        padding: 0.375rem 0.5rem;
        margin: 0 0 0.125rem 0;
    }
    
    .checkout-progress.mb-4 {
        margin-bottom: 0.125rem !important;
    }
    
    .progress-steps {
        padding: 0 10px;
    }
    
    .payment-section {
        background: white;
        border-radius: 8px;
        padding: 0.375rem;
        margin: 0 !important;
        width: 100%;
        max-width: 100%;
    }
    
    /* Réduire les margins entre les colonnes */
    .row.g-4 > .col-12 {
        margin-bottom: 0.125rem !important;
    }
    
    .section-title {
        font-size: 15px !important;
        padding: 0.375rem 0.5rem !important;
        margin-bottom: 0.375rem !important;
    }
    
    .section-title i {
        font-size: 15px !important;
    }
    
    .form-section {
        padding: 0.375rem;
        max-width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }
    
    .promo-code-container {
        max-width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }
    
    /* Forcer les tailles des champs de formulaire */
    .form-label {
        font-size: 12px !important;
        margin-bottom: 4px !important;
        font-weight: 500 !important;
    }
    
    .form-control,
    .form-select {
        font-size: 13px !important;
        padding: 8px 10px !important;
        border-radius: 6px !important;
    }
    
    .form-control-sm {
        font-size: 12px !important;
        padding: 6px 8px !important;
    }
    
    .form-select.form-select-sm {
        font-size: 12px !important;
        padding: 6px 8px !important;
    }
    
    .form-text {
        font-size: 11px !important;
        margin-top: 3px !important;
    }
    
    .invalid-feedback {
        font-size: 11px !important;
        margin-top: 3px !important;
    }
    
    .alert {
        padding: 10px 12px !important;
        font-size: 12px !important;
        margin-bottom: 0.5rem !important;
    }
    
    .alert i {
        font-size: 12px !important;
    }
    
    /* Input group responsive pour tablette */
    .input-group {
        flex-wrap: wrap;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }
    
    .input-group .form-control {
        flex: 1 1 auto;
        min-width: 0;
        max-width: calc(100% - 80px);
        box-sizing: border-box;
        overflow: hidden;
        font-size: 13px !important;
        padding: 8px 10px !important;
    }
    
    .input-group .btn {
        white-space: nowrap;
        padding: 8px 10px !important;
        font-size: 11px !important;
        flex-shrink: 0;
        min-width: 70px;
        max-width: 80px;
        box-sizing: border-box;
    }
    
    #validatePromoCodeBtn {
        padding: 8px 8px !important;
        font-size: 11px !important;
        min-width: 70px !important;
        max-width: 80px !important;
    }
    
    /* Payment button pour tablette */
    .payment-actions {
        display: flex;
        justify-content: center;
        padding: 0 0.5rem !important;
    }
    
    #payButton {
        width: 100% !important;
        max-width: 100%;
        padding: 12px 24px !important;
        font-size: 15px !important;
    }
    
    /* Order summary */
    .summary-title {
        font-size: 15px !important;
        margin-bottom: 0.5rem !important;
        padding: 0.25rem 0.375rem !important;
    }
    
    .order-item {
        padding: 0.375rem 0 !important;
        font-size: 13px !important;
    }
    
    .item-title {
        font-size: 12px !important;
    }
    
    .item-instructor {
        font-size: 10px !important;
    }
    
    .item-price {
        font-size: 13px !important;
    }
    
    .order-total {
        font-size: 14px !important;
        padding-top: 0.375rem !important;
    }
    
    .order-total .text-primary {
        font-size: 16px !important;
    }
    
    /* Terms section */
    .terms-section .form-check {
        align-items: center !important; /* centrer verticalement */
    }
    
    .terms-section .form-check-label {
        font-size: 13px !important;
    }
    
    .terms-section .form-check-input {
        width: 1.1em !important;
        height: 1.1em !important;
        margin-top: 0 !important; /* centré verticalement */
    }
}

@media (max-width: 767.98px) {
    /* Réduire encore plus les paddings et margins sur mobile - style analytics */
    .checkout-wrapper {
        padding: 0 0.25rem !important;
        max-width: 100%;
    }
    
    .checkout-page .checkout-wrapper {
        padding: 0.25rem !important;
    }
    
    /* Header responsive */
    .checkout-header {
        padding: 0.25rem 0;
        margin-bottom: 0.25rem;
    }
    
    .checkout-header .checkout-wrapper {
        flex-direction: column;
        gap: 8px;
        padding: 0 0.25rem;
    }
    
    .checkout-title-section {
        width: 100%;
    }
    
    .checkout-title {
        font-size: 18px !important;
        margin-bottom: 4px;
    }
    
    .checkout-subtitle {
        font-size: 12px !important;
    }
    
    .continue-shopping-btn {
        width: 100%;
        justify-content: center;
        padding: 8px 12px !important;
        font-size: 12px !important;
    }
    
    /* Progress responsive */
    .checkout-progress {
        margin: 0 0 0.125rem 0 !important;
        background: white;
        border-radius: 8px;
        padding: 0.25rem 0.375rem;
    }
    
    .checkout-progress.mb-4 {
        margin-bottom: 0.125rem !important;
    }
    
    .progress-steps {
        width: 100%;
        gap: 4px;
        padding: 0 5px;
    }
    
    .step {
        min-width: 60px;
        flex: 0 0 auto;
    }
    
    .step-circle {
        width: 32px;
        height: 32px;
    }
    
    .step-number {
        font-size: 12px;
    }
    
    .step-check {
        font-size: 12px;
    }
    
    .step-label {
        font-size: 9px;
        margin-top: 4px;
        line-height: 1.2;
    }
    
    .step-line {
        margin: -14px 2px;
        height: 2px;
    }
    
    /* Colonnes responsive */
    .row.g-4 {
        margin: 0 !important;
        --bs-gutter-x: 0.125rem;
        --bs-gutter-y: 0.125rem;
    }
    
    .row.g-4 > .col-12 {
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-bottom: 0.125rem !important;
    }
    
    .row.g-4 > .col-12:last-child {
        margin-bottom: 0 !important;
    }
    
    /* Order summary en premier sur mobile */
    .order-1 {
        order: 1 !important;
    }
    
    .order-2 {
        order: 2 !important;
    }
    
    /* Payment section responsive */
    .payment-section {
        background: white;
        border-radius: 8px;
        padding: 0.25rem;
        margin: 0 !important;
        width: 100%;
        max-width: 100%;
    }
    
    .section-title {
        font-size: 14px !important;
        margin-bottom: 0.375rem !important;
        padding: 0.25rem 0.375rem !important;
    }
    
    .section-title i {
        font-size: 14px !important;
    }
    
    /* Form responsive */
    .form-section {
        padding: 0.375rem;
        max-width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }
    
    .promo-code-container {
        max-width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }

    .form-section .row.g-3 {
        --bs-gutter-x: 0.75rem;
        --bs-gutter-y: 0.75rem;
    }
    
    .form-section .row {
        margin: 0;
        --bs-gutter-x: 0.375rem;
        --bs-gutter-y: 0.375rem;
    }
    
    .form-section .row > [class*="col-"] {
        padding-left: calc(var(--bs-gutter-x) * 0.5);
        padding-right: calc(var(--bs-gutter-x) * 0.5);
        margin-bottom: 0;
    }
    
    .form-label {
        font-size: 12px !important;
        margin-bottom: 4px !important;
        display: block;
        font-weight: 500 !important;
    }
    
    .form-control,
    .form-select {
        font-size: 13px !important;
        padding: 8px 10px !important;
        width: 100%;
        max-width: 100%;
        border-radius: 6px !important;
    }
    
    .form-control-sm {
        font-size: 12px !important;
        padding: 6px 8px !important;
    }
    
    .form-select.form-select-sm {
        font-size: 12px !important;
        padding: 6px 8px !important;
    }
    
    .form-text {
        font-size: 11px !important;
        margin-top: 3px !important;
    }
    
    .invalid-feedback {
        font-size: 11px !important;
        margin-top: 3px !important;
    }
    
    .alert {
        padding: 10px 12px !important;
        font-size: 12px !important;
        margin-bottom: 0.5rem !important;
        border-radius: 6px !important;
    }
    
    .alert i {
        font-size: 12px !important;
    }
    
    /* Input group responsive */
    .input-group {
        flex-wrap: wrap;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    .input-group .form-control {
        flex: 1 1 auto;
        min-width: 0;
        max-width: calc(100% - 85px);
        box-sizing: border-box;
    }
    
    .input-group .btn {
        white-space: nowrap;
        padding: 10px 10px;
        font-size: 12px;
        flex-shrink: 0;
        min-width: 75px;
        max-width: 85px;
        box-sizing: border-box;
    }
    
    #validatePromoCodeBtn {
        padding: 10px 8px !important;
        font-size: 12px !important;
        min-width: 70px !important;
        max-width: 80px !important;
    }
    
    #validatePromoCodeBtn .fas {
        margin-right: 3px;
    }
    
    /* Terms section responsive */
    .terms-section {
        margin-top: 0.5rem;
    }
    
    .terms-section .form-check {
        align-items: center !important; /* centrer verticalement */
    }
    
    .terms-section .form-check-label {
        font-size: 12px !important;
        line-height: 1.4;
    }
    
    .terms-section .form-check-input {
        width: 1em !important;
        height: 1em !important;
        margin-top: 0 !important; /* centré verticalement */
    }
    
    /* Payment actions responsive */
    .payment-actions {
        margin-top: 0.75rem;
    }
    
    .btn-lg {
        padding: 12px 18px !important;
        font-size: 14px !important;
        width: 100%;
        max-width: 100%;
        border-radius: 6px !important;
    }
    
    .payment-actions {
        display: flex;
        justify-content: center;
        padding: 0 0.375rem !important;
    }
    
    #payButton {
        width: 100% !important;
        max-width: 100%;
        padding: 12px 24px !important;
        font-size: 14px !important;
    }
    
    /* Order summary responsive */
    .order-summary-card {
        position: relative !important;
        margin: 0 !important;
        padding: 0.25rem;
        border-radius: 8px;
        top: 0 !important;
        width: 100%;
        max-width: 100%;
    }
    
    .summary-title {
        font-size: 14px !important;
        margin-bottom: 0.5rem !important;
        padding: 0.25rem 0.375rem !important;
    }
    
    .order-items {
        max-height: 180px;
        overflow-y: auto;
        margin-bottom: 0.375rem;
        padding: 0 0.25rem;
    }
    
    .order-item {
        padding: 0.375rem 0 !important;
        font-size: 12px !important;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    
    .order-item .item-info {
        flex: 1;
        min-width: 0;
        width: 100%;
    }
    
    .order-item .item-price {
        font-size: 12px !important;
        flex-shrink: 0;
        align-self: flex-end;
        font-weight: 700;
    }
    
    .item-title {
        font-size: 12px !important;
        line-height: 1.3;
        word-wrap: break-word;
        margin-bottom: 3px;
    }
    
    .item-instructor {
        font-size: 10px !important;
        margin: 0;
    }
    
    .order-total {
        font-size: 14px !important;
        padding-top: 0.375rem !important;
        border-top: 2px solid #003366;
        margin-top: 0.375rem;
        padding-left: 0.25rem;
        padding-right: 0.25rem;
    }
    
    .order-total .d-flex {
        font-size: 14px !important;
        align-items: center;
    }
    
    .order-total .text-primary {
        font-size: 16px !important;
    }
}

/* Cartes fournisseurs - charte graphique du site */
.providers-grid-init #providers,
#providers {
    display: grid !important; /* forcer sur .d-flex */
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 0;
}

.provider-card {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 12px;
    padding: 12px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    margin: 0; /* géré par grid gap */
    min-height: 72px; /* taille uniforme */
    width: 100%;
}

.provider-card:hover {
    border-color: #003366;
    background: #f3f8ff;
}

.provider-card.active {
    border-color: #003366;
    box-shadow: 0 0 0 3px rgba(0,51,102,0.1);
    background: #eaf2ff;
}

.provider-card .provider-logo {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.provider-card .provider-logo img {
    height: 44px; /* logo plus grand */
    width: auto;
    max-width: 100%;
    object-fit: contain;
}

.provider-card .provider-name {
    font-weight: 600;
    color: #003366;
    font-size: 14px;
    line-height: 1.3;
    word-wrap: break-word;
}

/* Optimisations mobile pour la liste des opérateurs */
@media (max-width: 768px) {
    #providers {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px !important;
    }
    
    .provider-card {
        width: 100%;
        min-width: unset;
        min-height: 70px;
        padding: 10px 12px;
        border-radius: 8px;
        gap: 10px;
    }
    
    .provider-card .provider-logo img {
        height: 40px;
    }
    
    .provider-card .provider-name {
        font-size: 13px;
        line-height: 1.2;
    }
}

@media (max-width: 480px) {
    #providers {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px !important;
    }
    
    .provider-card {
        min-height: 65px;
        padding: 8px 10px;
        gap: 8px;
    }
    
    .provider-card .provider-logo img {
        height: 36px;
    }
    
    .provider-card .provider-name {
        font-size: 12px;
    }
}

@media (max-width: 380px) {
    /* Une colonne pleine largeur pour très petits écrans */
    #providers {
        grid-template-columns: 1fr !important;
    }
    
    .provider-card {
        min-height: 70px;
    }
}

/* Améliorations responsives de la case à cocher des conditions */
.terms-section .form-check {
    display: flex; /* utiliser flexbox pour un meilleur alignement */
    align-items: center; /* centrer verticalement avec la checkbox */
    gap: 10px;
    margin: 0;
}

.terms-section .form-check-input {
    margin-top: 0; /* pas de marge, centré verticalement */
    flex: 0 0 auto; /* ne pas s'étirer */
    width: 1.25em;
    height: 1.25em;
    cursor: pointer;
}

.terms-section .form-check-label {
    flex: 1; /* occuper toute la largeur disponible */
    white-space: normal; /* autoriser le retour à la ligne */
    word-break: break-word;
    margin: 0; /* supprimer toute marge par défaut */
    cursor: pointer;
    line-height: 1.5;
    padding-top: 0;
}

.terms-section .invalid-feedback {
    grid-column: 1 / -1; /* occupe toute la largeur sous la ligne */
    margin-top: 4px;
    display: block;
}

.terms-section .form-check-label a {
    white-space: normal;
    text-decoration: underline;
}

.terms-section .form-check-label a:hover {
    text-decoration: none;
}

@media (max-width: 768px) {
    .terms-section .form-check-label {
        font-size: 13px;
        line-height: 1.5;
    }
    
    .terms-section .form-check-input {
        margin-top: 0; /* centré verticalement */
        width: 1.2em;
        height: 1.2em;
    }
    
    .terms-section .form-check {
        align-items: center; /* centrer verticalement */
        gap: 8px;
    }
}

@media (max-width: 480px) {
    .terms-section .form-check-label {
        font-size: 11px !important;
        line-height: 1.4;
    }
    
    .terms-section .form-check-input {
        margin-top: 0 !important; /* centré verticalement */
        width: 0.95em !important;
        height: 0.95em !important;
    }
    
    .terms-section .form-check {
        align-items: center; /* centrer verticalement */
        gap: 5px;
    }
}

@media (max-width: 480px) {
    /* Styles pour très petits écrans - style analytics */
    .checkout-wrapper {
        padding: 0 0.125rem !important;
    }
    
    .checkout-page .checkout-wrapper {
        padding: 0.125rem !important;
    }
    
    .checkout-header {
        padding: 0.375rem 0;
        margin-bottom: 0.5rem;
    }
    
    .checkout-header .checkout-wrapper {
        padding: 0 0.5rem;
    }
    
    .checkout-title {
        font-size: 16px !important;
    }
    
    .checkout-subtitle {
        font-size: 11px !important;
    }
    
    .continue-shopping-btn {
        padding: 8px 12px !important;
        font-size: 11px !important;
    }
    
    .checkout-progress {
        margin: 0 0 0.25rem 0 !important;
        padding: 0.375rem 0.5rem;
    }
    
    .checkout-progress.mb-4 {
        margin-bottom: 0.25rem !important;
    }
    
    .progress-steps {
        padding: 0 3px;
        gap: 2px;
    }
    
    .step {
        min-width: 55px;
    }
    
    .step-circle {
        width: 30px;
        height: 30px;
    }
    
    .step-number {
        font-size: 11px;
    }
    
    .step-check {
        font-size: 11px;
    }
    
    .step-label {
        font-size: 8px;
        margin-top: 3px;
    }
    
    .step-line {
        margin: -12px 1px;
        height: 2px;
    }
    
    .payment-section {
        padding: 0.25rem;
        margin: 0 !important;
        width: 100%;
        max-width: 100%;
    }
    
    .section-title {
        font-size: 13px !important;
        margin-bottom: 0.375rem !important;
        padding: 0.25rem 0.375rem !important;
    }
    
    .section-title i {
        font-size: 13px !important;
    }
    
    .form-section {
        padding: 0.375rem;
        max-width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }
    
    .promo-code-container {
        max-width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }
    
    .form-label {
        font-size: 11px !important;
        margin-bottom: 3px !important;
    }
    
    .form-control,
    .form-select {
        font-size: 12px !important;
        padding: 7px 9px !important;
    }
    
    .form-control-sm {
        font-size: 11px !important;
        padding: 5px 7px !important;
    }
    
    .form-select.form-select-sm {
        font-size: 11px !important;
        padding: 5px 7px !important;
    }
    
    .form-text {
        font-size: 10px !important;
    }
    
    .invalid-feedback {
        font-size: 10px !important;
    }
    
    .alert {
        padding: 8px 10px !important;
        font-size: 11px !important;
        margin-bottom: 0.375rem !important;
    }
    
    .alert i {
        font-size: 11px !important;
    }
    
    .input-group {
        flex-wrap: wrap;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }
    
    .input-group .form-control {
        flex: 1 1 auto;
        min-width: 0;
        max-width: calc(100% - 75px);
        box-sizing: border-box;
        overflow: hidden;
    }
    
    .input-group .btn {
        padding: 9px 6px;
        font-size: 11px;
        flex-shrink: 0;
        min-width: 65px;
        max-width: 75px;
        box-sizing: border-box;
    }
    
    #validatePromoCodeBtn {
        padding: 9px 6px !important;
        font-size: 11px !important;
        min-width: 60px !important;
        max-width: 70px !important;
    }
    
    #validatePromoCodeBtn .fas {
        margin-right: 2px;
    }
    
    /* Sur très petits écrans, réduire encore plus */
    @media (max-width: 400px) {
        .input-group .form-control {
            max-width: calc(100% - 70px);
        }
        
        #validatePromoCodeBtn {
            padding: 9px 4px !important;
            font-size: 10px !important;
            min-width: 55px !important;
            max-width: 65px !important;
        }
        
        #validatePromoCodeBtn .fas {
            margin-right: 0;
        }
    }
    
    .order-summary-card {
        padding: 0.25rem;
        margin: 0 !important;
        width: 100%;
        max-width: 100%;
    }
    
    .summary-title {
        font-size: 13px !important;
        margin-bottom: 0.375rem !important;
        padding: 0.25rem 0.375rem !important;
    }
    
    .order-items {
        max-height: 160px;
        padding: 0 0.25rem;
    }
    
    .order-item {
        padding: 0.375rem 0 !important;
        font-size: 11px !important;
    }
    
    .item-title {
        font-size: 11px !important;
        margin-bottom: 2px;
    }
    
    .item-instructor {
        font-size: 9px !important;
    }
    
    .item-price {
        font-size: 11px !important;
    }
    
    .order-total {
        font-size: 13px !important;
        padding-top: 0.375rem !important;
        padding-left: 0.25rem;
        padding-right: 0.25rem;
    }
    
    .order-total .d-flex {
        font-size: 13px !important;
    }
    
    .order-total .text-primary {
        font-size: 15px !important;
    }
    
    .terms-section {
        margin-top: 0.375rem;
    }
    
    .terms-section .form-check-label {
        font-size: 11px !important;
        line-height: 1.35;
    }
    
    .terms-section .form-check-input {
        width: 0.95em !important;
        height: 0.95em !important;
        margin-top: 0 !important; /* centré verticalement */
    }
    
    .terms-section .form-check {
        align-items: center !important; /* centrer verticalement */
    }
    
    .payment-actions {
        margin-top: 0.375rem;
    }
    
    .btn-lg {
        font-size: 13px !important;
        padding: 10px 16px !important;
    }
    
    .payment-actions {
        display: flex;
        justify-content: center;
        padding: 0 0.25rem !important;
    }
    
    #payButton {
        width: 100% !important;
        max-width: 100%;
        padding: 12px 20px !important;
        font-size: 13px !important;
    }
}
</style>
@endpush
