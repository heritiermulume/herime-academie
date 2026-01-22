@extends('providers.admin.layout')

@section('admin-title', 'Configuration de paiement')
@section('admin-subtitle', 'Configurez votre moyen de règlement pour recevoir vos paiements automatiquement.')

@section('admin-content')
    <form action="{{ route('provider.payment-settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('POST')

        <div class="admin-form-card">
            <div class="admin-form-grid">
                <div class="mt-4 pt-3 border-top">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_external_provider" name="is_external_provider" value="1" 
                               {{ old('is_external_provider', $provider->is_external_provider) ? 'checked' : '' }}
                               onchange="toggleExternalProviderFields()">
                        <label class="form-check-label fw-bold" for="is_external_provider">
                            Activer les paiements automatiques
                        </label>
                    </div>
                    <small class="text-muted d-block mb-3">
                        Si activé, vous recevrez automatiquement vos paiements après chaque vente de contenu. Un pourcentage de commission sera déduit automatiquement.
                    </small>
                    
                    <div id="moneroo-fields" style="display: {{ old('is_external_provider', $provider->is_external_provider) ? 'block' : 'none' }};">
                        @php
                            $countries = $monerooData['countries'] ?? [];
                            $providers = $monerooData['providers'] ?? [];
                            $methods = $monerooData['methods'] ?? [];
                            $selectedCountry = old('moneroo_country', $provider->moneroo_country);
                            $selectedProvider = old('moneroo_provider', $provider->moneroo_provider);
                            $selectedCurrency = old('moneroo_currency', $provider->moneroo_currency ?? '');
                            $selectedPhone = old('moneroo_phone', $provider->moneroo_phone);
                            
                            // Déterminer les devises disponibles selon la méthode sélectionnée
                            $availableCurrencies = [];
                            $selectedMethod = null;
                            if ($selectedProvider) {
                                foreach ($providers as $p) {
                                    if ($p['code'] == $selectedProvider && (empty($selectedCountry) || $p['country'] == $selectedCountry)) {
                                        $availableCurrencies = !empty($p['currencies']) && is_array($p['currencies']) 
                                            ? $p['currencies'] 
                                            : (!empty($p['currency']) ? [$p['currency']] : []);
                                        if (empty($selectedCurrency) && !empty($availableCurrencies)) {
                                            $selectedCurrency = $availableCurrencies[0];
                                        }
                                        $selectedMethod = $p;
                                        break;
                                    }
                                }
                                // Récupérer aussi depuis methods si disponible
                                if (isset($methods[$selectedProvider])) {
                                    $selectedMethod = $methods[$selectedProvider];
                                    if (empty($availableCurrencies) && !empty($selectedMethod['currency'])) {
                                        $availableCurrencies = [$selectedMethod['currency']];
                                        if (empty($selectedCurrency)) {
                                            $selectedCurrency = $selectedMethod['currency'];
                                        }
                                    }
                                }
                            }
                            
                            // Déterminer les champs requis pour la méthode sélectionnée
                            // Selon la documentation: https://docs.moneroo.io/payouts/available-methods#required-fields
                            $requiredFields = $selectedMethod['required_fields'] ?? ['msisdn'];
                            $needsMsisdn = in_array('msisdn', $requiredFields);
                            $needsAccountNumber = in_array('account_number', $requiredFields);
                            
                            // Vérifier si on est en mode sandbox
                            $isSandbox = config('services.moneroo.environment', 'production') === 'sandbox';
                        @endphp
                        
                        @if(empty($countries) && empty($providers))
                            <div class="alert alert-danger">
                                <h6 class="alert-heading mb-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Impossible de charger les méthodes de paiement
                                </h6>
                                <p class="mb-0">
                                    Les méthodes de paiement ne peuvent pas être chargées actuellement. 
                                    Veuillez contacter l'administrateur pour résoudre ce problème.
                                </p>
                            </div>
                        @else
                            <div class="alert alert-info mb-4">
                                <h6 class="alert-heading mb-2">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informations importantes
                                </h6>
                                <ul class="mb-0 small">
                                    <li>Les paiements sont traités automatiquement après chaque vente de contenu.</li>
                                    <li>Le numéro de téléphone doit être en <strong>format international complet</strong> (avec indicatif pays).</li>
                                    <li>Exemple pour le Bénin: <strong>22951345020</strong> (229 = indicatif, 51345020 = numéro).</li>
                                    <li>Les paiements peuvent prendre quelques minutes à quelques heures selon la méthode choisie.</li>
                                    <li>Vous recevrez une notification par email lorsque le paiement sera traité.</li>
                                </ul>
                            </div>
                            
                            @if($isSandbox)
                                <div class="alert alert-warning mb-4">
                                    <h6 class="alert-heading mb-2">
                                        <i class="fas fa-flask me-2"></i>
                                        Mode Sandbox - Numéros de test disponibles
                                    </h6>
                                    <p class="mb-2 small">
                                        Pour tester les payouts en mode sandbox, utilisez ces numéros de test (Moneroo Test Payout Gateway):
                                    </p>
                                    <ul class="mb-0 small">
                                        <li><strong>4149518161</strong> - ✅ Transaction réussie</li>
                                        <li><strong>4149518162</strong> - ❌ Transaction échouée</li>
                                        <li><strong>4149518163</strong> - ⏳ Transaction en attente</li>
                                    </ul>
                                    <p class="mb-0 mt-2 small text-muted">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Ces numéros fonctionnent uniquement avec la méthode de test en mode sandbox.
                                    </p>
                                </div>
                            @endif
                        @endif
                        
                        <div class="admin-form-grid admin-form-grid--responsive">
                            <div>
                                <label for="moneroo_country" class="form-label fw-bold">Pays</label>
                                <select class="form-select @error('moneroo_country') is-invalid @enderror" 
                                        id="moneroo_country" 
                                        name="moneroo_country"
                                        onchange="updateProviders()">
                                    <option value="">Sélectionner un pays</option>
                                    @foreach($countries as $country)
                                    <option value="{{ $country['code'] }}" 
                                            {{ $selectedCountry == $country['code'] ? 'selected' : '' }}>
                                        {{ $country['name'] }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('moneroo_country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Sélectionnez votre pays pour filtrer les méthodes de payout disponibles
                                </small>
                            </div>
                            <div>
                                <label for="moneroo_provider" class="form-label fw-bold">Méthode de payout</label>
                                <select class="form-select @error('moneroo_provider') is-invalid @enderror" 
                                        id="moneroo_provider" 
                                        name="moneroo_provider"
                                        onchange="updatePhoneField(); updateCurrencyField();"
                                        disabled>
                                    <option value="">Sélectionner une méthode</option>
                                    @foreach($providers as $provider)
                                        @php
                                            $providerCurrencies = !empty($provider['currencies']) && is_array($provider['currencies']) 
                                                ? $provider['currencies'] 
                                                : (!empty($provider['currency'] ?? null) ? [$provider['currency']] : []);
                                        @endphp
                                        <option value="{{ $provider['code'] }}" 
                                                data-country="{{ $provider['country'] }}"
                                                data-currencies="{{ json_encode($providerCurrencies) }}"
                                                style="display: {{ empty($selectedCountry) || $provider['country'] == $selectedCountry ? 'block' : 'none' }};"
                                                {{ $selectedProvider == $provider['code'] && (empty($selectedCountry) || $provider['country'] == $selectedCountry) ? 'selected' : '' }}>
                                            {{ $provider['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('moneroo_provider')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Sélectionnez votre méthode de payout (ex: M-Pesa, Orange Money, etc.)
                                </small>
                            </div>

                            <div>
                                <label for="moneroo_currency" class="form-label fw-bold">Devise</label>
                                <select class="form-select @error('moneroo_currency') is-invalid @enderror" 
                                        id="moneroo_currency" 
                                        name="moneroo_currency"
                                        data-selected-currency="{{ $selectedCurrency }}"
                                        onchange="updateFieldsState()"
                                        {{ empty($availableCurrencies) ? 'disabled' : '' }}>
                                    <option value="">Sélectionner une devise</option>
                                    @foreach($availableCurrencies as $currency)
                                        <option value="{{ $currency }}" 
                                                {{ $selectedCurrency == $currency ? 'selected' : '' }}>
                                            {{ $currency }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('moneroo_currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Devise supportée par la méthode sélectionnée (ex: USD, CDF)
                                </small>
                            </div>

                            <div>
                                <label for="moneroo_phone" class="form-label fw-bold">
                                    @if($needsAccountNumber)
                                        Numéro de compte
                                    @else
                                        Numéro de téléphone mobile money
                                    @endif
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="tel" 
                                       class="form-control @error('moneroo_phone') is-invalid @enderror" 
                                       id="moneroo_phone" 
                                       name="moneroo_phone" 
                                       value="{{ $selectedPhone }}"
                                       placeholder="{{ $needsAccountNumber ? '1XXXXXXXXX' : '243824449218' }}"
                                       pattern="[0-9]+"
                                       inputmode="numeric"
                                       maxlength="20"
                                       disabled>
                                @error('moneroo_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    @if($needsAccountNumber)
                                        Entrez votre numéro de compte en format international (ex: 14149518161 pour le test).
                                    @else
                                        Entrez votre numéro de téléphone en format international complet avec l'indicatif pays.
                                        <br class="d-none d-md-inline">
                                        Exemple pour la RDC: <strong>243824449218</strong> (243 = indicatif, 824449218 = numéro).
                                    @endif
                                    @if($isSandbox && $selectedProvider === 'moneroo_payout_demo')
                                        <br>
                                        <span class="text-warning">
                                            <i class="fas fa-flask me-1"></i>
                                            Mode test: Utilisez 4149518161 (succès), 4149518162 (échec), ou 4149518163 (en attente)
                                        </span>
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($selectedProvider && $selectedPhone && $selectedCurrency)
            <div class="admin-form-card mt-4">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    Configuration actuelle
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Méthode:</strong> 
                        <span class="text-muted">{{ $selectedMethod['name'] ?? $selectedProvider }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Pays:</strong> 
                        <span class="text-muted">
                            @foreach($countries as $c)
                                @if($c['code'] == $selectedCountry){{ $c['name'] }}@endif
                            @endforeach
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Devise:</strong> 
                        <span class="text-muted">{{ $selectedCurrency }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>{{ $needsAccountNumber ? 'Numéro de compte' : 'Numéro de téléphone' }}:</strong> 
                        <span class="text-muted">{{ $selectedPhone }}</span>
                    </div>
                </div>
            </div>
            
        @endif

        <div class="admin-form-actions">
            <button type="submit" class="admin-btn primary">
                <i class="fas fa-save me-2"></i>Enregistrer les modifications
            </button>
            <a href="{{ route('provider.dashboard') }}" class="admin-btn outline">
                <i class="fas fa-times me-2"></i>Annuler
            </a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
// Afficher/masquer les champs Moneroo
function toggleExternalProviderFields() {
    const isExternal = document.getElementById('is_external_provider');
    const monerooFields = document.getElementById('moneroo-fields');
    
    if (isExternal && monerooFields) {
        if (isExternal.checked) {
            monerooFields.style.display = 'block';
            updateProviders(); // Initialiser les providers et l'état des champs
        } else {
            monerooFields.style.display = 'none';
        }
    }
}

// Stocker toutes les options de providers au chargement
let allProviderOptions = [];

// Mettre à jour les providers disponibles selon le pays sélectionné
function updateProviders() {
    const countrySelect = document.getElementById('moneroo_country');
    const providerSelect = document.getElementById('moneroo_provider');
    
    if (!countrySelect || !providerSelect) return;
    
    const selectedCountry = countrySelect.value;
    
    // Sauvegarder la valeur actuelle du provider
    const currentProviderValue = providerSelect.value;
    
    // Si les options n'ont pas encore été stockées, les récupérer maintenant
    if (allProviderOptions.length === 0) {
        allProviderOptions = Array.from(providerSelect.querySelectorAll('option[data-country]')).map(option => {
            let currencies = [];
            try {
                const currenciesJson = option.getAttribute('data-currencies');
                if (currenciesJson) {
                    currencies = JSON.parse(currenciesJson);
                }
            } catch (e) {
                console.error('Error parsing currencies:', e);
            }
            return {
                value: option.value,
                text: option.textContent,
                country: option.getAttribute('data-country'),
                currencies: currencies,
                element: option.cloneNode(true)
            };
        });
    }
    
    // Vider le select (sauf l'option par défaut)
    providerSelect.innerHTML = '<option value="">Sélectionner un fournisseur</option>';
    
    // Filtrer et ajouter les providers du pays sélectionné
    let hasVisibleProviders = false;
    allProviderOptions.forEach(optionData => {
        if (!selectedCountry || optionData.country === selectedCountry) {
            // Créer une nouvelle option et copier tous les attributs
            const newOption = document.createElement('option');
            newOption.value = optionData.value;
            newOption.textContent = optionData.text;
            newOption.setAttribute('data-country', optionData.country);
            newOption.setAttribute('data-currencies', JSON.stringify(optionData.currencies));
            newOption.style.display = 'block';
            providerSelect.appendChild(newOption);
            hasVisibleProviders = true;
        }
    });
    
    // Restaurer la valeur du provider si elle est toujours valide pour le nouveau pays
    if (currentProviderValue && hasVisibleProviders) {
        const selectedOption = providerSelect.querySelector(`option[value="${currentProviderValue}"]`);
        if (selectedOption) {
            providerSelect.value = currentProviderValue;
        } else {
            providerSelect.value = '';
        }
    } else {
        providerSelect.value = '';
    }
    
    // Effacer la devise sélectionnée quand le pays change
    const currencySelect = document.getElementById('moneroo_currency');
    if (currencySelect) {
        currencySelect.value = '';
        currencySelect.innerHTML = '<option value="">Sélectionner une devise</option>';
        currencySelect.disabled = true;
        currencySelect.classList.add('bg-light');
    }
    
    // Mettre à jour l'état des champs
    updateFieldsState();
    
    // Mettre à jour le champ devise si un provider est sélectionné
    if (providerSelect.value) {
        updateCurrencyField();
    }
}

// Mettre à jour l'état du champ numéro selon le provider sélectionné
function updatePhoneField() {
    updateCurrencyField();
    const phoneInput = document.getElementById('moneroo_phone');
    if (phoneInput) {
        const countrySelect = document.getElementById('moneroo_country');
        const providerSelect = document.getElementById('moneroo_provider');
        const currencySelect = document.getElementById('moneroo_currency');
        const hasCountry = countrySelect?.value !== '';
        const hasProvider = providerSelect?.value !== '';
        const hasCurrency = currencySelect?.value !== '';
        const isEnabled = hasCountry && hasProvider && hasCurrency;
        
        phoneInput.disabled = !isEnabled;
        phoneInput.classList.toggle('bg-light', !isEnabled);
        phoneInput.style.cursor = isEnabled ? 'text' : 'not-allowed';
        
        // Mettre à jour le placeholder selon le pays sélectionné
        if (isEnabled && hasCountry) {
            const countryPrefixes = {
                'BJ': '229', 'CI': '225', 'SN': '221', 'TG': '228', 'CM': '237',
                'KE': '254', 'GH': '233', 'NG': '234', 'UG': '256', 'RW': '250',
                'TZ': '255', 'ZM': '260', 'MW': '265', 'ML': '223', 'CD': '243', 'US': '1'
            };
            const prefix = countryPrefixes[countrySelect.value] || '';
            phoneInput.placeholder = prefix ? prefix + 'XXXXXXXXX' : 'Format international requis';
        } else {
            phoneInput.placeholder = '22951345020';
        }
    }
}

// Mettre à jour le champ devise selon le provider sélectionné
function updateCurrencyField() {
    const providerSelect = document.getElementById('moneroo_provider');
    const currencySelect = document.getElementById('moneroo_currency');
    
    if (!providerSelect || !currencySelect) return;
    
    const selectedOption = providerSelect.options[providerSelect.selectedIndex];
    let currencies = [];
    
    if (selectedOption && selectedOption.value) {
        // Essayer d'abord depuis l'attribut data-currencies de l'option
        try {
            const currenciesJson = selectedOption.getAttribute('data-currencies');
            if (currenciesJson) {
                currencies = JSON.parse(currenciesJson);
                console.log('Currencies from data-currencies attribute:', currencies);
            }
        } catch (e) {
            console.error('Error parsing currencies from data-currencies:', e);
        }
        
        // Si pas de devises trouvées, essayer depuis allProviderOptions
        if (currencies.length === 0) {
            const providerData = allProviderOptions.find(opt => opt.value === selectedOption.value);
            if (providerData && providerData.currencies && providerData.currencies.length > 0) {
                currencies = providerData.currencies;
                console.log('Currencies from allProviderOptions:', currencies);
            } else {
                console.warn('No currencies found in allProviderOptions for provider:', selectedOption.value);
            }
        }
    } else {
        console.warn('No provider selected or selectedOption is null');
    }
    
    // Sauvegarder la valeur actuelle AVANT de vider le select
    // Si c'est l'initialisation, récupérer la valeur depuis l'attribut data-selected-currency
    const currentCurrencyValue = currencySelect.value || currencySelect.getAttribute('data-selected-currency') || '';
    
    // Vider le select (sauf l'option par défaut)
    currencySelect.innerHTML = '<option value="">Sélectionner une devise</option>';
    
    // Ajouter les devises disponibles
    if (currencies.length > 0) {
        currencies.forEach(currency => {
            const option = document.createElement('option');
            option.value = currency;
            option.textContent = currency;
            // Restaurer la sélection si c'est la devise sauvegardée
            if (currentCurrencyValue === currency) {
                option.selected = true;
            }
            currencySelect.appendChild(option);
        });
        
        // FORCER l'activation du champ devise
        currencySelect.disabled = false;
        currencySelect.removeAttribute('disabled');
        currencySelect.classList.remove('bg-light');
        currencySelect.style.cursor = 'pointer';
        currencySelect.style.backgroundColor = '';
        currencySelect.style.opacity = '1';
        
        console.log('Currency field activated with', currencies.length, 'currencies');
        
        // Si une devise était sélectionnée mais n'est plus dans la liste, la restaurer quand même
        if (currentCurrencyValue && !currencySelect.value) {
            // La devise sauvegardée n'est plus disponible, mais on la garde pour référence
            // On peut aussi essayer de la sélectionner si elle existe
            const savedOption = currencySelect.querySelector(`option[value="${currentCurrencyValue}"]`);
            if (savedOption) {
                savedOption.selected = true;
            }
        }
    } else {
        console.warn('No currencies found for provider:', selectedOption?.value);
        currencySelect.disabled = true;
        currencySelect.classList.add('bg-light');
        currencySelect.style.cursor = 'not-allowed';
    }
    
    // Ne pas appeler updateFieldsState() ici car elle pourrait désactiver le champ devise
    // On met à jour seulement le champ phone si nécessaire
    const phoneInput = document.getElementById('moneroo_phone');
    if (phoneInput) {
        const hasCountry = document.getElementById('moneroo_country')?.value !== '';
        const hasProvider = providerSelect.value !== '';
        const hasCurrency = currencySelect.value !== '';
        phoneInput.disabled = !hasCountry || !hasProvider || !hasCurrency;
        phoneInput.classList.toggle('bg-light', phoneInput.disabled);
        phoneInput.style.cursor = phoneInput.disabled ? 'not-allowed' : 'text';
    }
}

// Mettre à jour l'état de tous les champs selon les sélections
function updateFieldsState() {
    const countrySelect = document.getElementById('moneroo_country');
    const providerSelect = document.getElementById('moneroo_provider');
    const currencySelect = document.getElementById('moneroo_currency');
    const phoneInput = document.getElementById('moneroo_phone');
    
    if (!countrySelect || !providerSelect || !currencySelect || !phoneInput) return;
    
    const hasCountry = countrySelect.value !== '';
    const hasProvider = providerSelect.value !== '';
    const hasCurrency = currencySelect.value !== '';
    
    // Gérer le champ provider
    const wasProviderEnabled = !providerSelect.disabled;
    providerSelect.disabled = !hasCountry;
    if (providerSelect.disabled && wasProviderEnabled) {
        providerSelect.value = ''; // Effacer la valeur si désactivé
    }
    providerSelect.classList.toggle('bg-light', providerSelect.disabled);
    providerSelect.style.cursor = providerSelect.disabled ? 'not-allowed' : 'pointer';
    
    // Gérer le champ devise : NE JAMAIS le désactiver ici si un provider est sélectionné
    // Laisser updateCurrencyField() gérer complètement l'activation/désactivation selon les devises disponibles
    // On désactive seulement si pas de provider
    if (!hasProvider) {
        currencySelect.disabled = true;
        currencySelect.classList.add('bg-light');
        currencySelect.style.cursor = 'not-allowed';
    }
    // Si hasProvider est true, on ne touche PAS au champ devise - updateCurrencyField() l'a déjà géré
    
    // Gérer le champ numéro : nécessite pays, opérateur ET devise
    const wasPhoneEnabled = !phoneInput.disabled;
    phoneInput.disabled = !hasCountry || !hasProvider || !hasCurrency;
    if (phoneInput.disabled && wasPhoneEnabled) {
        phoneInput.value = ''; // Effacer la valeur si désactivé
    }
    phoneInput.classList.toggle('bg-light', phoneInput.disabled);
    phoneInput.style.cursor = phoneInput.disabled ? 'not-allowed' : 'text';
}

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', function() {
    // Stocker toutes les options de providers au chargement initial
    const providerSelect = document.getElementById('moneroo_provider');
    if (providerSelect) {
        allProviderOptions = Array.from(providerSelect.querySelectorAll('option[data-country]')).map(option => {
            let currencies = [];
            try {
                const currenciesJson = option.getAttribute('data-currencies');
                if (currenciesJson) {
                    currencies = JSON.parse(currenciesJson);
                }
            } catch (e) {
                console.error('Error parsing currencies:', e);
            }
            return {
                value: option.value,
                text: option.textContent,
                country: option.getAttribute('data-country'),
                currencies: currencies,
                element: option.cloneNode(true)
            };
        });
    }
    
    toggleExternalProviderFields();
    
    // Initialiser les providers selon le pays sélectionné
    updateProviders();
    
    // Initialiser le champ devise (après updateProviders pour que le provider soit chargé)
        // Attendre un peu pour s'assurer que tout est initialisé, surtout si un provider est déjà sélectionné
        setTimeout(function() {
            const providerSelect = document.getElementById('moneroo_provider');
            const currencySelect = document.getElementById('moneroo_currency');
        
        // Si un provider est déjà sélectionné, charger les devises
        if (providerSelect && providerSelect.value && currencySelect) {
            updateCurrencyField();
        }
        
        // Initialiser l'état des champs APRÈS updateCurrencyField pour ne pas désactiver le champ devise
        updateFieldsState();
        
        // Ajouter la validation du numéro de téléphone en format international
        const phoneInput = document.getElementById('moneroo_phone');
        if (phoneInput) {
            phoneInput.addEventListener('blur', function() {
                validatePhoneNumber(this);
            });
            
            phoneInput.addEventListener('input', function() {
                // Retirer les caractères non numériques
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    }, 150);
    
    // Valider le format du numéro de téléphone
    function validatePhoneNumber(input) {
        const value = input.value.trim();
        const countrySelect = document.getElementById('moneroo_country');
        const country = countrySelect?.value;
        
        if (!value || !country) {
            return;
        }
        
        // Vérifier que le numéro commence par l'indicatif pays
        const countryPrefixes = {
            'BJ': '229', 'CI': '225', 'SN': '221', 'TG': '228', 'CM': '237',
            'KE': '254', 'GH': '233', 'NG': '234', 'UG': '256', 'RW': '250',
            'TZ': '255', 'ZM': '260', 'MW': '265', 'ML': '223', 'CD': '243', 'US': '1'
        };
        
        const prefix = countryPrefixes[country];
        if (prefix && !value.startsWith(prefix)) {
            // Afficher un avertissement mais ne pas bloquer
            const helpText = input.parentElement.querySelector('small');
            if (helpText && !helpText.textContent.includes('⚠')) {
                const originalText = helpText.textContent;
                helpText.innerHTML = '<i class="fas fa-exclamation-triangle text-warning me-1"></i>' +
                    'Le numéro devrait commencer par ' + prefix + ' pour le format international.';
                helpText.classList.add('text-warning');
                
                setTimeout(() => {
                    helpText.textContent = originalText;
                    helpText.classList.remove('text-warning');
                }, 5000);
            }
        }
    }
});
</script>
@endpush

@push('styles')
<style>
/* Grille responsive pour les champs de formulaire */
.admin-form-grid--responsive {
    grid-template-columns: 1fr;
    gap: 1.25rem;
}

/* Tablette: 2 colonnes */
@media (min-width: 768px) {
    .admin-form-grid--responsive {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
}

/* Desktop: 4 colonnes pour les champs Moneroo */
@media (min-width: 1024px) {
    .admin-form-grid--responsive {
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }
}

/* Réduire l'espacement en haut sur mobile/tablette */
@media (max-width: 1024px) {
    .provider-admin-shell {
        padding-top: calc(var(--site-navbar-height, 64px) + 0.25rem) !important;
    }
    
    .admin-main {
        padding-top: 0.25rem !important;
        padding-bottom: 1.5rem !important;
    }
    
    .admin-header {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-content {
        gap: 1rem !important;
        margin-top: 0 !important;
    }
    
    .admin-form-card {
        margin-top: 0 !important;
        padding: 1.5rem !important;
    }
    
    /* Alertes responsive */
    .alert {
        padding: 1rem !important;
    }
    
    .alert-heading {
        font-size: 1rem !important;
    }
    
    .alert ul {
        padding-left: 1.25rem !important;
        font-size: 0.875rem !important;
    }
}

@media (max-width: 640px) {
    .provider-admin-shell {
        padding-top: calc(var(--site-navbar-height, 64px) + 0.1rem) !important;
    }
    
    .admin-main {
        padding-top: 0.1rem !important;
        padding-bottom: 1rem !important;
    }
    
    .admin-header {
        margin-bottom: 0.25rem !important;
    }
    
    .admin-header__title {
        font-size: 1.5rem !important;
        margin-bottom: 0.25rem !important;
    }
    
    .admin-header__subtitle {
        margin-top: 0.25rem !important;
        margin-bottom: 0 !important;
        font-size: 0.9rem !important;
    }
    
    .admin-content {
        gap: 0.75rem !important;
        margin-top: 0 !important;
    }
    
    .admin-form-card {
        margin-top: 0 !important;
        padding: 1.25rem !important;
        border-radius: 1rem !important;
    }
    
    /* Form labels et inputs sur mobile */
    .form-label {
        font-size: 0.95rem !important;
        margin-bottom: 0.5rem !important;
    }
    
    .form-select,
    .form-control {
        font-size: 0.95rem !important;
        padding: 0.625rem 0.75rem !important;
    }
    
    .form-check-label {
        font-size: 0.95rem !important;
    }
    
    /* Alertes sur mobile */
    .alert {
        padding: 0.875rem !important;
        border-radius: 0.75rem !important;
    }
    
    .alert-heading {
        font-size: 0.95rem !important;
        margin-bottom: 0.5rem !important;
    }
    
    .alert-heading i {
        font-size: 1.1rem !important;
    }
    
    .alert ul {
        padding-left: 1.15rem !important;
        font-size: 0.85rem !important;
        margin-bottom: 0 !important;
    }
    
    .alert p {
        font-size: 0.85rem !important;
        margin-bottom: 0.5rem !important;
    }
    
    .alert small {
        font-size: 0.8rem !important;
    }
    
    /* Textes d'aide sur mobile */
    small.text-muted {
        font-size: 0.8rem !important;
        line-height: 1.4 !important;
    }
    
    /* Configuration actuelle sur mobile */
    .admin-form-card h6 {
        font-size: 1rem !important;
        margin-bottom: 1rem !important;
    }
    
    .admin-form-card .row {
        margin: 0 !important;
    }
    
    .admin-form-card .col-12 {
        padding: 0.5rem 0 !important;
        margin-bottom: 0.5rem !important;
    }
    
    .admin-form-card strong {
        display: block;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
    
    .admin-form-card .text-muted {
        font-size: 0.85rem !important;
    }
}

/* Styles pour les boutons d'action */
.admin-form-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-top: 1.5rem;
}

/* Mobile: boutons en colonne */
@media (max-width: 767.98px) {
    .admin-form-actions {
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
        margin-top: 1.25rem;
    }
    
    .admin-form-actions .admin-btn {
        width: 100%;
        margin: 0;
        justify-content: center;
        padding: 0.75rem 1rem !important;
    }
    
    .admin-form-actions .admin-btn i {
        margin-right: 0.5rem;
    }
}

/* Tablette et desktop: boutons en ligne */
@media (min-width: 768px) {
    .admin-form-actions {
        flex-direction: row;
        gap: 1rem;
    }
    
    .admin-form-actions .admin-btn {
        flex: 0 0 auto;
    }
}

/* Amélioration des switch sur mobile */
@media (max-width: 640px) {
    .form-check {
        padding-left: 2.25rem !important;
    }
    
    .form-check-input {
        width: 2.25rem !important;
        height: 1.25rem !important;
        margin-left: -2.25rem !important;
    }
    
    .form-check-label {
        padding-top: 0.125rem !important;
    }
}

/* Amélioration de l'espacement des sections */
@media (max-width: 1024px) {
    .admin-form-card + .admin-form-card {
        margin-top: 1rem !important;
    }
    
    .border-top {
        padding-top: 1rem !important;
        margin-top: 1rem !important;
    }
}

/* Amélioration de la lisibilité sur tablette */
@media (min-width: 768px) and (max-width: 1023px) {
    .admin-form-grid--responsive > div {
        min-width: 0;
    }
    
    .admin-form-card {
        padding: 1.5rem !important;
    }
}
</style>
@endpush
 
