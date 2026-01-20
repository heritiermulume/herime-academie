@extends('providers.admin.layout')

@section('admin-title', 'Configuration de paiement')
@section('admin-subtitle', 'Configurez votre moyen de règlement pour recevoir vos paiements automatiquement via Moneroo.')

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
                            $countries = $monerooData['countries'] ?? ($pawapayData['countries'] ?? []);
                            $providers = $monerooData['providers'] ?? ($pawapayData['providers'] ?? []);
                            $selectedCountry = old('pawapay_country', $provider->pawapay_country);
                            $selectedProvider = old('pawapay_provider', $provider->pawapay_provider);
                            $selectedCurrency = old('pawapay_currency', $provider->pawapay_currency ?? '');
                            $availableCurrencies = [];
                            if ($selectedProvider) {
                                foreach ($providers as $provider) {
                                    if ($provider['code'] == $selectedProvider && (empty($selectedCountry) || $provider['country'] == $selectedCountry)) {
                                        $availableCurrencies = !empty($provider['currencies']) && is_array($provider['currencies']) 
                                            ? $provider['currencies'] 
                                            : (!empty($provider['currency']) ? [$provider['currency']] : []);
                                        if (empty($selectedCurrency) && !empty($availableCurrencies)) {
                                            $selectedCurrency = $availableCurrencies[0];
                                        }
                                        break;
                                    }
                                }
                            }
                        @endphp
                        
                        @if(empty($countries) && empty($providers))
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Impossible de charger les données Moneroo. Veuillez vérifier la configuration de l'API.
                            </div>
                        @endif
                        
                        <div class="admin-form-grid">
                            <div>
                                <label for="pawapay_country" class="form-label fw-bold">Pays</label>
                                <select class="form-select @error('pawapay_country') is-invalid @enderror" 
                                        id="pawapay_country" 
                                        name="pawapay_country"
                                        onchange="updateProviders()">
                                    <option value="">Sélectionner un pays</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country['code'] }}" 
                                                {{ $selectedCountry == $country['code'] ? 'selected' : '' }}>
                                            {{ $country['name'] }} ({{ $country['code'] }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('pawapay_country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Sélectionnez votre pays</small>
                            </div>
                            <div>
                                <label for="pawapay_provider" class="form-label fw-bold">Fournisseur</label>
                                <select class="form-select @error('pawapay_provider') is-invalid @enderror" 
                                        id="pawapay_provider" 
                                        name="pawapay_provider"
                                        onchange="updatePhoneField(); updateCurrencyField();"
                                        disabled>
                                    <option value="">Sélectionner un fournisseur</option>
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
                                            {{ $provider['name'] }} ({{ $provider['code'] }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('pawapay_provider')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Sélectionnez votre fournisseur mobile money</small>
                            </div>

                            <div>
                                <label for="pawapay_currency" class="form-label fw-bold">Devise</label>
                                <select class="form-select @error('pawapay_currency') is-invalid @enderror" 
                                        id="pawapay_currency" 
                                        name="pawapay_currency"
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
                                @error('pawapay_currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Sélectionnez la devise de l'opérateur</small>
                            </div>

                            <div>
                                <label for="pawapay_phone" class="form-label fw-bold">Numéro de téléphone mobile money</label>
                                <input type="text" 
                                       class="form-control @error('pawapay_phone') is-invalid @enderror" 
                                       id="pawapay_phone" 
                                       name="pawapay_phone" 
                                       value="{{ old('pawapay_phone', $provider->pawapay_phone) }}"
                                       placeholder="820000000"
                                       disabled>
                                @error('pawapay_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Numéro sans indicatif pays (ex: 820000000 pour la RDC)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
    const countrySelect = document.getElementById('pawapay_country');
    const providerSelect = document.getElementById('pawapay_provider');
    
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
    const currencySelect = document.getElementById('pawapay_currency');
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
    // updateFieldsState() est déjà appelée dans updateCurrencyField() pour gérer le phone
    // On appelle juste updateFieldsState() pour mettre à jour le phone si nécessaire
    const phoneInput = document.getElementById('pawapay_phone');
    if (phoneInput) {
        const countrySelect = document.getElementById('pawapay_country');
        const providerSelect = document.getElementById('pawapay_provider');
        const currencySelect = document.getElementById('pawapay_currency');
        const hasCountry = countrySelect?.value !== '';
        const hasProvider = providerSelect?.value !== '';
        const hasCurrency = currencySelect?.value !== '';
        phoneInput.disabled = !hasCountry || !hasProvider || !hasCurrency;
        phoneInput.classList.toggle('bg-light', phoneInput.disabled);
        phoneInput.style.cursor = phoneInput.disabled ? 'not-allowed' : 'text';
    }
}

// Mettre à jour le champ devise selon le provider sélectionné
function updateCurrencyField() {
    const providerSelect = document.getElementById('pawapay_provider');
    const currencySelect = document.getElementById('pawapay_currency');
    
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
    const phoneInput = document.getElementById('pawapay_phone');
    if (phoneInput) {
        const hasCountry = document.getElementById('pawapay_country')?.value !== '';
        const hasProvider = providerSelect.value !== '';
        const hasCurrency = currencySelect.value !== '';
        phoneInput.disabled = !hasCountry || !hasProvider || !hasCurrency;
        phoneInput.classList.toggle('bg-light', phoneInput.disabled);
        phoneInput.style.cursor = phoneInput.disabled ? 'not-allowed' : 'text';
    }
}

// Mettre à jour l'état de tous les champs selon les sélections
function updateFieldsState() {
    const countrySelect = document.getElementById('pawapay_country');
    const providerSelect = document.getElementById('pawapay_provider');
    const currencySelect = document.getElementById('pawapay_currency');
    const phoneInput = document.getElementById('pawapay_phone');
    
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
    const providerSelect = document.getElementById('pawapay_provider');
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
        const providerSelect = document.getElementById('pawapay_provider');
        const currencySelect = document.getElementById('pawapay_currency');
        
        // Si un provider est déjà sélectionné, charger les devises
        if (providerSelect && providerSelect.value && currencySelect) {
            updateCurrencyField();
        }
        
        // Initialiser l'état des champs APRÈS updateCurrencyField pour ne pas désactiver le champ devise
        updateFieldsState();
    }, 150);
});
</script>
@endpush

@push('styles')
<style>
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
    }
    
    .admin-content {
        gap: 0.75rem !important;
        margin-top: 0 !important;
    }
    
    .admin-form-card {
        margin-top: 0 !important;
        padding: 1.25rem !important;
    }
}

/* Styles pour les boutons d'action sur mobile */
@media (max-width: 767.98px) {
    .admin-form-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }
    
    .admin-form-actions .admin-btn {
        width: 100%;
        margin: 0;
        justify-content: center;
    }
    
    .admin-form-actions .admin-btn i {
        margin-right: 0.5rem;
    }
}

/* Styles pour les boutons d'action sur desktop */
@media (min-width: 768px) {
    .admin-form-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
    }
    
    .admin-form-actions .admin-btn {
        flex: 0 0 auto;
    }
}
</style>
@endpush
 
