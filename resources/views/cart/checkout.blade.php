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
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Progress Steps -->
                <div class="checkout-progress mb-5">
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

                <!-- pawaPay Payment -->
                <div class="payment-section">
                    <h4 class="section-title mb-4">
                        <i class="fas fa-mobile-alt me-2"></i>Paiement Mobile Money
                    </h4>

                    <form id="pawapayForm" method="POST" onsubmit="return false;">
                        @csrf
                        
                                <div class="form-section">
                            <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                    <label class="form-label"><i class="fas fa-flag me-1"></i>Pays</label>
                                    <select id="country" class="form-select"></select>
                                            </div>
                                                    <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <label class="form-label mb-0"><i class="fas fa-coins me-1"></i>Montant</label>
                                        <div style="min-width: 140px;">
                                            <select id="currencySelect" class="form-select form-select-sm"></select>
                                                </div>
                                            </div>
                                    {{-- Montant initial dans la devise de base du site (configurée dans /admin/settings) --}}
                                    <input type="text" id="amount" class="form-control mt-2" value="{{ number_format($total, 2, '.', '') }}" readonly>
                                    <div class="invalid-feedback" id="amountError"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-network-wired me-1"></i>Opérateur</label>
                                <div id="providers" class="d-flex flex-wrap gap-2"></div>
                                <small class="form-text text-muted">Sélectionnez votre opérateur.</small>
                                    </div>
                                    
                            <div class="row g-3 mb-2">
                                <div class="col-md-4">
                                    <label class="form-label"><i class="fas fa-phone me-1"></i>Indicatif</label>
                                    <input type="text" id="prefix" class="form-control" value="243" readonly>
                                    </div>
                                <div class="col-md-8">
                                    <label class="form-label"><i class="fas fa-phone me-1"></i>Numéro (sans indicatif)</label>
                                    <input type="tel" id="phoneNumber" class="form-control" placeholder="783 456 789" required>
                                    <div class="invalid-feedback" id="phoneError">Veuillez saisir un numéro de téléphone valide.</div>
                            </div>
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

                            <input type="hidden" id="currency" value="{{ config('services.pawapay.default_currency') }}">
                        </div>

                        <div class="payment-actions mt-3">
                            <button type="button" id="payButton" class="btn btn-primary btn-lg w-100">
                                <span id="payButtonText">Payer maintenant</span>
                                    </button>
                        </div>

                        <div id="paymentNotice" class="alert alert-info mt-3" style="display:none;"></div>
                    </form>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-lg-4">
                <div class="order-summary-card">
                    <h5 class="summary-title mb-3">Résumé de la commande</h5>
                    <div class="order-items">
                        @foreach($cartItems as $item)
                        <div class="order-item">
                            <div class="item-info">
                                <h6 class="item-title">{{ $item['course']->title ?? 'Cours' }}</h6>
                                <p class="item-instructor text-muted">Par {{ $item['course']->instructor->name ?? '' }}</p>
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
    const providersContainer = document.getElementById('providers');
    const countrySelect = document.getElementById('country');
    const prefixInput = document.getElementById('prefix');
    const phoneNumberInput = document.getElementById('phoneNumber');
    const amountInput = document.getElementById('amount');
    const currencyInput = document.getElementById('currency');
    const currencySelect = document.getElementById('currencySelect');
    const payButton = document.getElementById('payButton');
    const payButtonText = document.getElementById('payButtonText');
    const paymentNotice = document.getElementById('paymentNotice');
    const termsCheckbox = document.getElementById('terms');
    let selectedProvider = null;
    let cachedActiveConf = null;
    let currentProviderRules = null; // min/max/decimals
    
    // IMPORTANT: Le montant de base est dans la devise de base du site (configurée dynamiquement dans /admin/settings)
    // Ce montant provient des prix des cours stockés dans la base de données (dans la devise de base)
    let baseAmount = parseFloat(amountInput.value) || 0; // Montant original dans la devise de base du site
    const baseCurrency = '{{ $baseCurrency ?? "USD" }}'; // Devise de base configurée dynamiquement dans l'admin
    
    let exchangeRates = {}; // Cache des taux de change

    async function loadCountries() {
        // Récupère toute la configuration active (sans filtre pays)
        const res = await fetch(`{{ route('pawapay.active-conf') }}`);
        if (!res.ok) {
            countrySelect.innerHTML = '<option>Chargement impossible</option>';
            return;
        }
        const data = await res.json();
        cachedActiveConf = data;
        const countries = data.countries || [];
        // Construire la liste des pays disponibles
        let html = '';
        countries.forEach(c => {
            const selected = c.country === `{{ config('services.pawapay.default_country') }}` ? 'selected' : '';
            const label = (c.displayName && (c.displayName.fr || c.displayName.en)) || c.country;
            html += `<option value="${c.country}" ${selected}>${label}</option>`;
        });
        // Si aucun pays dans la conf, fallback sur défaut
        if (!html) {
            html = `<option value="{{ config('services.pawapay.default_country') }}" selected>{{ config('services.pawapay.default_country') }}</option>`;
        }
        countrySelect.innerHTML = html;
        // Déclencher chargement des fournisseurs pour le pays sélectionné
        onCountryChange();
    }

    function onCountryChange() {
        providersContainer.innerHTML = '<div class="text-muted">Chargement des fournisseurs…</div>';
        currencySelect.innerHTML = '<option value="">Chargement...</option>';
        selectedProvider = null;
        
        const data = cachedActiveConf || { countries: [] };
        const country = (data.countries || []).find(c => c.country === countrySelect.value) || null;
        if (!country) {
            providersContainer.innerHTML = '<div class="text-danger">Aucun fournisseur disponible.</div>';
            currencySelect.innerHTML = '<option value="">Aucune devise disponible</option>';
            selectedProvider = null;
            updatePayButtonState();
            return;
        }
        prefixInput.value = country.prefix || prefixInput.value;
        const providers = country.providers || [];
        renderProviders(providers);
    }

    function renderProviders(providers) {
        providersContainer.innerHTML = '';
        if (providers.length === 0) {
            providersContainer.innerHTML = '<div class="text-danger">Aucun fournisseur disponible.</div>';
            currencySelect.innerHTML = '<option value="">Aucune devise disponible</option>';
            selectedProvider = null;
            updatePayButtonState();
                return;
            }
            
        providers.forEach((p, index) => {
            const card = document.createElement('div');
            card.className = 'provider-card';
            card.innerHTML = `
                <div class="provider-logo"><img src="${p.logo}" alt="${p.displayName || p.provider}"></div>
                <div class="provider-name">${p.displayName || p.provider}</div>
            `;
            
            // Sélectionner automatiquement le premier fournisseur
            if (index === 0) {
                selectedProvider = p.provider;
                card.classList.add('active');
                setupCurrenciesForProvider(p);
            }
            
            card.addEventListener('click', () => {
                selectedProvider = p.provider;
                [...providersContainer.children].forEach(c => c.classList.remove('active'));
                card.classList.add('active');
                setupCurrenciesForProvider(p);
            });
            providersContainer.appendChild(card);
        });
    }

    function setupCurrenciesForProvider(provider) {
        if (!provider) {
            currencySelect.innerHTML = '<option value="">Aucune devise disponible</option>';
            currencyInput.value = '';
            amountInput.value = baseAmount.toFixed(2);
            updatePayButtonState();
                return;
            }
            
        const currencies = (provider.currencies || []).filter(c => !!c.currency);
        let html = '';
        
        if (currencies.length === 0) {
            // Aucune devise dans la config, utiliser la devise par défaut
            const defaultCurrency = '{{ config('services.pawapay.default_currency') }}';
            html = `<option value="${defaultCurrency}" selected>${defaultCurrency}</option>`;
            currencySelect.innerHTML = html;
            currencyInput.value = defaultCurrency;
            convertAmount(defaultCurrency);
        } else {
            currencies.forEach((c, index) => {
                const code = c.currency;
                const selected = index === 0 ? 'selected' : '';
                html += `<option value="${code}" ${selected}>${code}</option>`;
            });
            currencySelect.innerHTML = html;
            // Définir règles min/max/decimals pour DEPOSIT si fournies
            const selectedCurrency = currencies[0]; // Première devise par défaut
            const opTypes = selectedCurrency && selectedCurrency.operationTypes ? selectedCurrency.operationTypes : null;
            const deposit = opTypes && opTypes.DEPOSIT ? opTypes.DEPOSIT : null;
            currentProviderRules = deposit ? {
                minAmount: deposit.minAmount ? parseFloat(deposit.minAmount) : null,
                maxAmount: deposit.maxAmount ? parseFloat(deposit.maxAmount) : null,
                decimalsInAmount: deposit.decimalsInAmount || 'TWO_PLACES',
            } : null;
            
            currencyInput.value = selectedCurrency.currency;
            convertAmount(selectedCurrency.currency);
        }
        updatePayButtonState();
    }

    /**
     * Convertir le montant de la devise de base du site vers la devise sélectionnée par l'utilisateur
     * @param {string} targetCurrency - La devise cible sélectionnée (ex: CDF, XOF, etc.)
     */
    async function convertAmount(targetCurrency) {
        if (!targetCurrency || !selectedProvider) {
            // Pas de devise sélectionnée, afficher le montant dans la devise de base
            amountInput.value = baseAmount.toFixed(2);
            validateAmount();
            updatePayButtonState();
                    return;
                }
        
        // Si la devise cible est la même que la devise de base du site, pas de conversion nécessaire
        if (targetCurrency === baseCurrency) {
            amountInput.value = baseAmount.toFixed(2);
            validateAmount();
            updatePayButtonState();
                    return;
                }
                
        // Récupérer le taux de change depuis la devise de base du site vers la devise cible sélectionnée
        // Exemple: Si baseCurrency = 'USD' et targetCurrency = 'CDF', on convertit USD -> CDF
        try {
            const rate = await getExchangeRate(baseCurrency, targetCurrency);
            const convertedAmount = baseAmount * rate;
            
            // Formater selon les règles du fournisseur
            const decimals = currentProviderRules?.decimalsInAmount === 'NONE' ? 0 : 2;
            amountInput.value = convertedAmount.toFixed(decimals);
            
            validateAmount();
            updatePayButtonState();
        } catch (error) {
            console.error('Erreur conversion:', error);
            // En cas d'erreur, garder le montant original
            amountInput.value = baseAmount.toFixed(2);
            validateAmount();
            updatePayButtonState();
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
            console.error('Erreur récupération taux:', error);
            throw error;
        }
    }
    
    function currencyChanged() {
        if (!selectedProvider) {
            currencySelect.innerHTML = '<option value="">Aucune devise disponible</option>';
            return;
        }
        
        currencyInput.value = currencySelect.value;
        
        // Mettre à jour les règles si nécessaire
        const provider = findProviderByCode(selectedProvider);
        if (provider) {
            const currencies = (provider.currencies || []).filter(c => !!c.currency);
            const selectedCurrency = currencies.find(c => c.currency === currencySelect.value);
            if (selectedCurrency) {
                const opTypes = selectedCurrency.operationTypes || {};
                const deposit = opTypes.DEPOSIT || {};
                currentProviderRules = {
                    minAmount: deposit.minAmount ? parseFloat(deposit.minAmount) : null,
                    maxAmount: deposit.maxAmount ? parseFloat(deposit.maxAmount) : null,
                    decimalsInAmount: deposit.decimalsInAmount || 'TWO_PLACES',
                };
            }
        }
        
        convertAmount(currencySelect.value);
    }
    
    function findProviderByCode(providerCode) {
        if (!cachedActiveConf) return null;
        const countries = cachedActiveConf.countries || [];
        for (const country of countries) {
            const provider = (country.providers || []).find(p => p.provider === providerCode);
            if (provider) return provider;
        }
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
        const digits = phoneNumberInput.value.replace(/\D/g,'');
        const valid = digits.length >= 7; // règle simple; peut être affinée par pays
        if (!valid) setInvalid(phoneNumberInput, document.getElementById('phoneError'), 'Numéro invalide.');
        else clearInvalid(phoneNumberInput, document.getElementById('phoneError'));
        return valid;
    }

    function validateTerms() {
        const ok = !!termsCheckbox.checked;
        if (!ok) setInvalid(termsCheckbox, document.getElementById('termsError'), 'Veuillez accepter les conditions.');
        else clearInvalid(termsCheckbox, document.getElementById('termsError'));
        return ok;
    }

    function validateAmount() {
        const val = parseFloat(amountInput.value);
        let ok = true;
        let msg = '';
        if (Number.isNaN(val)) { ok = false; msg = 'Montant invalide.'; }
        if (ok && currentProviderRules) {
            if (currentProviderRules.minAmount !== null && val < currentProviderRules.minAmount) { ok = false; msg = `Montant minimum: ${currentProviderRules.minAmount}`; }
            if (currentProviderRules.maxAmount !== null && val > currentProviderRules.maxAmount) { ok = false; msg = `Montant maximum: ${currentProviderRules.maxAmount}`; }
        }
        if (!ok) setInvalid(amountInput, document.getElementById('amountError'), msg);
        else clearInvalid(amountInput, document.getElementById('amountError'));
        return ok;
    }

    function updatePayButtonState() {
        // Le fournisseur doit être sélectionné
        if (!selectedProvider) {
            payButton.disabled = true;
            return;
        }
        
        const ready = validatePhone() && validateTerms() && validateAmount();
        payButton.disabled = !ready;
    }

    async function initiateDeposit() {
        if (!validateTerms() | !validatePhone() | !selectedProvider | !validateAmount()) {
            updatePayButtonState();
            return;
        }
        // S'assurer que la devise est synchronisée
        currencyInput.value = currencySelect.value;
        
        const fullPhone = `${prefixInput.value}${phoneNumberInput.value.replace(/\D/g,'')}`;
        payButton.disabled = true;
        payButtonText.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Initialisation…';
        paymentNotice.style.display = 'none';

        // Payload avec montant converti et devise sélectionnée pour l'opérateur
        const payload = {
            amount: parseFloat(amountInput.value), // Montant converti dans la devise sélectionnée
            currency: currencySelect.value, // Devise sélectionnée (assurée d'être à jour)
            phoneNumber: fullPhone,
            provider: selectedProvider,
            country: countrySelect.value,
            _token: '{{ csrf_token() }}'
        };

        // Ajout d'un timeout pour éviter l'attente infinie
        const controller = new AbortController();
        const timeoutMs = 30000; // 30 secondes
        const timeoutId = setTimeout(() => controller.abort(), timeoutMs);
        let res;
        try {
            res = await fetch(`{{ route('pawapay.initiate') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
            try { await fetch(`{{ url('/pawapay/cancel-latest') }}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }); } catch(e){}
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

        // Gestion du nextStep selon la documentation pawaPay
        if (data.nextStep === 'REDIRECT_TO_AUTH_URL' && data.authorizationUrl) {
            // Rediriger vers l'URL d'autorisation (pour flux Wave, etc.)
            window.location.href = data.authorizationUrl;
            return;
        } else if (data.nextStep === 'GET_AUTH_URL') {
            // L'URL d'autorisation n'est pas encore disponible, on doit poller
            paymentNotice.style.display = 'block';
            paymentNotice.className = 'alert alert-info mt-3';
            paymentNotice.textContent = 'Attente de l\'URL d\'autorisation…';
            pollForAuthUrl(depositId);
            return;
        }

        // Flux standard : polling pour le statut final
        // Le polling est nécessaire pour donner un feedback immédiat à l'utilisateur
        // et gérer les différents statuts (COMPLETED, FAILED, etc.)
        if (data.depositId) {
            paymentNotice.style.display = 'block';
            paymentNotice.className = 'alert alert-info mt-3';
            paymentNotice.textContent = 'Paiement initié. Veuillez approuver le paiement sur votre téléphone…';
            pollStatus(data.depositId);
        }
    }

    // Polling pour obtenir l'URL d'autorisation (cas Wave, etc.)
    async function pollForAuthUrl(depositId) {
        const maxAttempts = 10; // 10 tentatives maximum
        let attempts = 0;
        
        const poll = async () => {
            if (attempts >= maxAttempts) {
                paymentNotice.className = 'alert alert-warning mt-3';
                paymentNotice.textContent = 'Délai dépassé lors de l\'obtention de l\'URL d\'autorisation.';
                payButton.disabled = false;
                payButtonText.innerHTML = '<i class="fas fa-credit-card me-2"></i>Payer maintenant';
                return;
            }
            
            attempts++;
            const res = await fetch(`{{ url('/pawapay/status') }}/${depositId}`);
            if (!res.ok) {
                setTimeout(poll, 1000);
                return;
            }
            
            const data = await res.json();
            
            if (data.nextStep === 'REDIRECT_TO_AUTH_URL' && data.authorizationUrl) {
                // URL d'autorisation disponible, rediriger
                paymentNotice.textContent = 'Redirection en cours…';
                setTimeout(() => window.location.href = data.authorizationUrl, 500);
            } else {
                // Continuer à poller
                setTimeout(poll, 1000);
            }
        };
        
        poll();
    }

    // Polling pour le statut final du paiement
    // Selon la documentation pawaPay officielle:
    // - Ne PAS imposer de timeout strict (pawaPay gère les délais)
    // - Le webhook est la source de vérité
    // - Le polling sert uniquement au feedback immédiat utilisateur
    async function pollStatus(depositId) {
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
                const res = await fetch(`{{ url('/pawapay/status') }}/${depositId}`);
                if (!res.ok) {
                    console.error('Status check failed:', res.status);
                    setTimeout(poll, 2000);
                    return;
                }
                
                const data = await res.json();
                const status = data.status;
                const nextStep = data.nextStep;
                
                console.log('pawaPay status:', status, 'nextStep:', nextStep, 'full data:', data);
                
                // Gérer le cas NOT_FOUND
                if (status === 'NOT_FOUND') {
                    stopped = true;
                    paymentNotice.className = 'alert alert-danger mt-3';
                    paymentNotice.innerHTML = `
                        <strong>Transaction introuvable</strong><br>
                        La transaction n'a pas été trouvée dans le système pawaPay.
                        Veuillez contacter le support si vous avez approuvé le paiement.
                    `;
                    payButton.disabled = false;
                    payButtonText.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Problème détecté';
                    return;
                }
                
                // Détecter si le statut a changé
                if (status !== lastStatus) {
                    lastStatus = status;
                    console.log('Status changed from', lastStatus, 'to', status);
                }
                
                // Gérer tous les statuts possibles selon la documentation pawaPay
                if (status === 'COMPLETED') {
                    stopped = true;
                    paymentNotice.className = 'alert alert-success mt-3';
                    paymentNotice.textContent = 'Paiement réussi ! Redirection…';
                    payButtonText.textContent = 'Paiement réussi';
                    
                    // Rediriger vers la page de succès
                    setTimeout(() => {
                        window.location.href = `{{ route('pawapay.success') }}?depositId=${depositId}`;
                    }, 1000);
                    
                } else if (status === 'FAILED') {
                    stopped = true;
                    paymentNotice.className = 'alert alert-danger mt-3';
                    paymentNotice.textContent = 'Le paiement a échoué. Veuillez réessayer.';
                    payButton.disabled = false;
                    payButtonText.innerHTML = '<i class="fas fa-credit-card me-2"></i>Réessayer';
                    
                } else if (status === 'IN_RECONCILIATION') {
                    // En réconciliation : pawaPay gère automatiquement
                    if (status !== lastStatus) {
                        paymentNotice.className = 'alert alert-warning mt-3';
                        paymentNotice.innerHTML = `
                            <strong><i class="fas fa-clock me-1"></i>Réconciliation en cours</strong><br>
                            Votre paiement est en cours de validation automatique par pawaPay.
                            Vous recevrez une confirmation dès que c'est terminé.
                        `;
                    }
                    setTimeout(poll, 3000); // Poll plus lentement pour réconciliation
                    
                } else if (status === 'PROCESSING' || status === 'ACCEPTED') {
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
                    console.warn('Unknown status:', status);
                    setTimeout(poll, 2000);
                }
            } catch (error) {
                console.error('Error polling status:', error);
                setTimeout(poll, 2000);
            }
        };
        
        poll();
    }

    countrySelect.addEventListener('change', onCountryChange);
    payButton.addEventListener('click', initiateDeposit);
    phoneNumberInput.addEventListener('input', () => { validatePhone(); updatePayButtonState(); });
    termsCheckbox.addEventListener('change', () => { validateTerms(); updatePayButtonState(); });
    currencySelect.addEventListener('change', currencyChanged);
    loadCountries();
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
    padding: 24px 0;
    margin-bottom: 32px;
}

.checkout-header .checkout-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.checkout-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}

.checkout-page .checkout-wrapper {
    padding: 24px;
}

.checkout-title-section {
    flex: 1;
}

.checkout-title {
    font-size: 32px;
    font-weight: 700;
    color: #1c1d1f;
    margin: 0 0 8px 0;
    line-height: 1.2;
}

.checkout-subtitle {
    font-size: 16px;
    color: #6a6f73;
    margin: 0;
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

/* Payment form visibility is controlled by inline styles, so no CSS needed */

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
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
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
    .checkout-page {
        padding-bottom: 70px;
    }
    
    /* Colonnes s'empilent sur tablette */
    .row > .col-lg-8,
    .row > .col-lg-4 {
        width: 100%;
        max-width: 100%;
        flex: 0 0 100%;
    }
    
    .order-summary-card {
        position: relative;
        margin-top: 30px;
        top: 0;
    }
    
    .continue-shopping-btn {
        font-size: 13px;
    }
    
    /* Réduire drastiquement le padding du wrapper principal */
    .checkout-page .checkout-wrapper {
        padding: 5px 20px !important;
    }
    
    /* Progress section padding sur tablette */
    .checkout-progress {
        background: white;
        border-radius: 8px;
        padding: 15px;
        margin: 0 0 15px 0;
    }
    
    .checkout-progress.mb-5 {
        margin-bottom: 15px !important;
    }
    
    .progress-steps {
        padding: 0 10px;
    }
}

@media (max-width: 768px) {
    .checkout-wrapper {
        padding: 0 16px !important;
        max-width: 100%;
    }
    
    /* Réduire drastiquement le padding du wrapper principal */
    .checkout-page .checkout-wrapper {
        padding: 5px 16px !important;
    }
    
    /* Header responsive */
    .checkout-header {
        padding: 20px 0;
    }
    
    .checkout-header .checkout-wrapper {
        flex-direction: column;
        gap: 15px;
    }
    
    .checkout-title-section {
        width: 100%;
    }
    
    .checkout-title {
        font-size: 24px;
    }
    
    .checkout-subtitle {
        font-size: 14px;
    }
    
    .continue-shopping-btn {
        width: 100%;
        justify-content: center;
    }
    
    /* Progress responsive */
    .checkout-progress {
        margin: 0 0 15px 0 !important;
        background: white;
        border-radius: 8px;
        padding: 15px;
    }
    
    /* Désactiver le margin-bottom Bootstrap sur mobile */
    .checkout-progress.mb-5 {
        margin-bottom: 15px !important;
    }
    
    .progress-steps {
        min-width: 100%;
        gap: 5px;
        padding: 0 10px;
    }
    
    .step {
        min-width: 70px;
        flex: 0 0 auto;
    }
    
    .step-circle {
        width: 35px;
        height: 35px;
    }
    
    .step-number {
        font-size: 13px;
    }
    
    .step-check {
        font-size: 14px;
    }
    
    .step-label {
        font-size: 10px;
        margin-top: 5px;
    }
    
    .step-line {
        margin: -15px 3px;
        height: 2px;
    }
    
    /* Colonnes responsive */
    .row {
        margin: 0;
    }
    
    .row > .col-lg-8,
    .row > .col-lg-4 {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    
    /* Payment section responsive */
    .payment-section {
        padding: 15px 0;
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .section-title {
        font-size: 16px;
        margin-bottom: 20px;
    }
    
    .payment-methods {
        margin-bottom: 20px !important;
    }
    
    .payment-methods .row {
        margin: 0;
    }
    
    .payment-methods .row > .col-md-6 {
        padding: 0;
        margin-bottom: 12px;
    }
    
    .payment-option {
        margin-bottom: 0;
        padding: 15px;
        width: 100%;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
    }
    
    .payment-option:hover {
        border-color: #003366;
        background-color: #f8f9fa;
    }
    
    .payment-label {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .payment-info {
        text-align: center;
    }
    
    .payment-info h6 {
        font-size: 15px;
        margin-bottom: 4px;
    }
    
    .payment-info p {
        font-size: 12px;
        margin: 0;
    }
    
    .payment-icon {
        font-size: 1.8rem;
    }
    
    /* Form responsive */
    .payment-forms {
        margin-bottom: 20px;
    }
    
    .maxicash-form,
    .whatsapp-form {
        width: 100%;
    }
    
    .form-section {
        padding: 0;
    }
    
    .form-title {
        font-size: 16px;
        margin-bottom: 15px;
    }
    
    .form-section .row {
        margin: 0;
    }
    
    .form-section .row > .col-md-6 {
        padding: 0;
        margin-bottom: 15px;
    }
    
    .form-label {
        font-size: 14px;
        margin-bottom: 6px;
        display: block;
    }
    
    .form-control,
    .form-select {
        font-size: 14px;
        padding: 10px 12px;
        width: 100%;
        max-width: 100%;
    }
    
    .form-text {
        font-size: 12px;
    }
    
    .alert {
        padding: 12px 15px;
        font-size: 13px;
        margin-bottom: 15px !important;
    }
    
    .alert i {
        font-size: 14px;
    }
    
    /* Billing section responsive */
    .billing-section {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .billing-section .row {
        margin: 0;
    }
    
    .billing-section .row > div {
        padding: 0;
        margin-bottom: 15px;
    }
    
    /* Terms section responsive */
    .terms-section {
        margin-top: 20px;
    }
    
    .terms-section .form-check {
        padding-left: 0;
    }
    
    .terms-section .form-check-label {
        font-size: 13px;
        line-height: 1.4;
        padding-left: 25px;
    }
    
    /* Payment actions responsive */
    .payment-actions {
        margin-top: 20px;
    }
    
    .payment-actions .row {
        margin: 0;
    }
    
    .btn-lg {
        padding: 14px 20px;
        font-size: 15px;
        width: 100%;
    }
    
    /* Order summary responsive */
    .order-summary-card {
        position: relative !important;
        margin-top: 30px;
        padding: 16px;
        border-radius: 8px;
        top: 0 !important;
    }
    
    .summary-title {
        font-size: 16px;
        margin-bottom: 15px !important;
    }
    
    .order-items {
        max-height: 250px;
        overflow-y: auto;
    }
    
    .order-item {
        padding: 12px 0;
        font-size: 14px;
    }
    
    .item-info {
        flex: 1;
        min-width: 0;
    }
    
    .item-title {
        font-size: 13px;
        line-height: 1.3;
        word-wrap: break-word;
    }
    
    .item-instructor {
        font-size: 11px;
    }
    
    .item-price {
        font-size: 14px;
        flex-shrink: 0;
    }
    
    .order-total {
        font-size: 18px;
        padding-top: 12px;
        border-top: 2px solid #003366;
    }
    
    .order-total .d-flex {
        font-size: 18px;
    }
}

/* Cartes fournisseurs - charte graphique du site */
.providers-grid-init #providers,
#providers {
    display: grid !important; /* forcer sur .d-flex */
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
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
.provider-card .provider-logo img {
    height: 44px; /* logo plus grand */
    width: auto;
}

/* Optimisations mobile pour la liste des opérateurs */
@media (max-width: 576px) {
    /* Deux colonnes compactes sur mobile standard */
    #providers {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px !important;
    }
    .provider-card {
        width: 100%;
        min-width: unset;
        min-height: 80px;
        padding: 12px;
        border-radius: 10px;
    }
    .provider-card .provider-logo img {
        height: 48px;
    }
    .provider-card .provider-name {
        font-size: 13px;
        line-height: 1.2;
    }
}

@media (max-width: 380px) {
    /* Une colonne pleine largeur pour très petits écrans */
    .provider-card {
        flex: 0 0 100%;
        min-height: 84px;
    }
}

/* Améliorations responsives de la case à cocher des conditions */
.terms-section .form-check {
    display: grid; /* placer la case et le label sur une même ligne */
    grid-template-columns: auto 1fr; /* checkbox + label prend tout l'espace restant */
    column-gap: 10px;
    align-items: start;
}
.terms-section .form-check-input {
    margin-top: 2px;
    flex: 0 0 auto; /* ne pas s'étirer */
}
.terms-section .form-check-label {
    flex: 1; /* occuper toute la largeur disponible */
    white-space: normal; /* autoriser le retour à la ligne */
    word-break: break-word;
    margin: 0; /* supprimer toute marge par défaut */
}
.terms-section .invalid-feedback {
    grid-column: 1 / -1; /* occupe toute la largeur sous la ligne */
    margin-top: 4px;
}
.terms-section .form-check-label a {
    white-space: normal;
}
@media (max-width: 576px) {
    .terms-section .form-check-label {
        font-size: 14px;
        line-height: 1.35;
    }
    .terms-section .form-check-input {
        transform: scale(1.05);
    }
    /* Éviter que la checkbox touche les bords de l'écran */
    .terms-section {
        padding-left: 6px;
        padding-right: 6px;
    }
    /* Harmoniser l'alignement avec les autres contrôles */
    .terms-section .form-check {
        grid-template-columns: auto 1fr; /* largeur auto selon la checkbox */
        column-gap: 4px; /* réduire encore l'espace */
    }
    .terms-section .form-check-input {
        margin-left: 0;
        margin-right: 0;
    }
}
.provider-card .provider-name {
    font-weight: 600;
    color: #003366;
}

@media (max-width: 480px) {
    .checkout-wrapper {
        padding: 0 12px !important;
    }
    
    /* Réduire encore plus le padding du wrapper principal */
    .checkout-page .checkout-wrapper {
        padding: 3px 12px !important;
    }
    
    .checkout-header {
        padding: 15px 0;
    }
    
    .checkout-title {
        font-size: 20px;
    }
    
    .checkout-subtitle {
        font-size: 13px;
    }
    
    .continue-shopping-btn {
        padding: 10px 14px;
        font-size: 13px;
    }
    
    .checkout-progress {
        margin: 0 0 12px 0 !important;
        padding: 12px;
    }
    
    .checkout-progress.mb-5 {
        margin-bottom: 12px !important;
    }
    
    .progress-steps {
        padding: 0 5px;
    }
    
    .step {
        min-width: 65px;
    }
    
    .step-circle {
        width: 35px;
        height: 35px;
    }
    
    .step-number {
        font-size: 13px;
    }
    
    .step-label {
        font-size: 10px;
        margin-top: 4px;
    }
    
    .step-line {
        margin: -12px 3px;
        height: 2px;
    }
    
    .payment-option {
        padding: 12px;
        margin-bottom: 12px;
    }
    
    .payment-label {
        padding: 8px;
    }
    
    .payment-info h6 {
        font-size: 13px;
    }
    
    .payment-info p {
        font-size: 11px;
    }
    
    .payment-icon {
        font-size: 1.5rem;
    }
    
    .section-title {
        font-size: 14px;
    }
    
    .section-title i {
        font-size: 14px;
    }
    
    .form-title {
        font-size: 14px;
    }
    
    .form-label {
        font-size: 13px;
    }
    
    .form-control,
    .form-select {
        font-size: 13px;
        padding: 8px 10px;
    }
    
    .alert {
        padding: 10px 12px;
        font-size: 13px;
    }
    
    .alert i {
        font-size: 13px;
    }
    
    .order-summary-card {
        padding: 12px;
    }
    
    .summary-title {
        font-size: 14px;
    }
    
    .order-item {
        padding: 10px 0;
        flex-wrap: wrap;
    }
    
    .item-title {
        font-size: 12px;
        margin-bottom: 3px;
    }
    
    .item-instructor {
        font-size: 10px;
    }
    
    .item-price {
        font-size: 13px;
        margin-top: 5px;
    }
    
    .order-total {
        font-size: 16px;
        padding-top: 10px;
    }
    
    .terms-section .form-check-label {
        font-size: 12px;
    }
    
    #payButton {
        font-size: 14px;
        padding: 14px 16px;
    }
    
    .btn-primary.btn-lg {
        font-size: 14px;
        padding: 14px 16px;
    }
}
</style>
@endpush
