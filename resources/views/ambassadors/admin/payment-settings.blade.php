@extends('ambassadors.admin.layout')

@section('admin-title', 'Configuration de paiement')
@section('admin-subtitle', 'Configurez votre moyen de règlement pour recevoir vos paiements automatiquement via Mobile Money.')

@section('admin-content')
    <form action="{{ route('ambassador.payment-settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('POST')

        <div class="admin-form-card">
            <div class="admin-form-grid">
                <div class="mt-4 pt-3 border-top">
                    <h4 class="mb-3">Informations de paiement</h4>
                    <small class="text-muted d-block mb-3">
                        Configurez vos informations de paiement pour recevoir vos commissions automatiquement.
                    </small>
                    
                    @php
                        $countries = $pawapayData['countries'] ?? [];
                        $providers = $pawapayData['providers'] ?? [];
                        $selectedCountry = old('pawapay_country', $user->pawapay_country);
                        $selectedProvider = old('pawapay_provider', $user->pawapay_provider);
                        $selectedCurrency = old('pawapay_currency', $user->pawapay_currency ?? '');
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
                            Impossible de charger les données pawaPay. Veuillez vérifier la configuration de l'API.
                        </div>
                    @endif
                    
                    <div class="admin-form-grid">
                        <div>
                            <label for="pawapay_country" class="form-label fw-bold">Pays <span class="text-danger">*</span></label>
                            <select class="form-select @error('pawapay_country') is-invalid @enderror" 
                                    id="pawapay_country" 
                                    name="pawapay_country"
                                    required
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
                            <label for="pawapay_provider" class="form-label fw-bold">Fournisseur <span class="text-danger">*</span></label>
                            <select class="form-select @error('pawapay_provider') is-invalid @enderror" 
                                    id="pawapay_provider" 
                                    name="pawapay_provider"
                                    required
                                    onchange="updatePhoneField(); updateCurrencyField();"
                                    disabled>
                                <option value="">Sélectionner un fournisseur</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider['code'] }}" 
                                            data-country="{{ $provider['country'] }}"
                                            data-currencies="{{ json_encode($provider['currencies'] ?? ($provider['currency'] ? [$provider['currency']] : [])) }}"
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
                            <label for="pawapay_currency" class="form-label fw-bold">Devise <span class="text-danger">*</span></label>
                            <select class="form-select @error('pawapay_currency') is-invalid @enderror" 
                                    id="pawapay_currency" 
                                    name="pawapay_currency"
                                    data-selected-currency="{{ $selectedCurrency }}"
                                    required
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
                            <label for="pawapay_phone" class="form-label fw-bold">Numéro de téléphone mobile money <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('pawapay_phone') is-invalid @enderror" 
                                   id="pawapay_phone" 
                                   name="pawapay_phone" 
                                   value="{{ old('pawapay_phone', $user->pawapay_phone) }}"
                                   placeholder="820000000"
                                   required
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

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="admin-btn primary">
                <i class="fas fa-save me-2"></i>Enregistrer les modifications
            </button>
            <a href="{{ route('ambassador.dashboard') }}" class="admin-btn outline">
                <i class="fas fa-times me-2"></i>Annuler
            </a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
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
            const newOption = optionData.element.cloneNode(true);
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
    updateFieldsState();
}

// Mettre à jour le champ devise selon le provider sélectionné
function updateCurrencyField() {
    const providerSelect = document.getElementById('pawapay_provider');
    const currencySelect = document.getElementById('pawapay_currency');
    
    if (!providerSelect || !currencySelect) return;
    
    const selectedOption = providerSelect.options[providerSelect.selectedIndex];
    let currencies = [];
    
    if (selectedOption && selectedOption.value) {
        try {
            const currenciesJson = selectedOption.getAttribute('data-currencies');
            if (currenciesJson) {
                currencies = JSON.parse(currenciesJson);
            }
        } catch (e) {
            console.error('Error parsing currencies:', e);
        }
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
        
        currencySelect.disabled = false;
        currencySelect.classList.remove('bg-light');
        currencySelect.style.cursor = 'pointer';
        
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
        currencySelect.disabled = true;
        currencySelect.classList.add('bg-light');
        currencySelect.style.cursor = 'not-allowed';
    }
    
    // Mettre à jour l'état des champs après avoir mis à jour les devises
    updateFieldsState();
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
    
    // Gérer le champ devise (sera géré par updateCurrencyField, mais on peut aussi le désactiver si pas de provider)
    if (!hasProvider) {
        currencySelect.disabled = true;
        currencySelect.classList.add('bg-light');
        currencySelect.style.cursor = 'not-allowed';
    }
    
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
    
    // Initialiser les providers selon le pays sélectionné
    updateProviders();
    
    // Initialiser l'état des champs
    updateFieldsState();
    
    // Initialiser le champ devise (après updateProviders pour que le provider soit chargé)
    // Attendre un peu pour s'assurer que tout est initialisé, surtout si un provider est déjà sélectionné
    setTimeout(function() {
        const providerSelect = document.getElementById('pawapay_provider');
        const currencySelect = document.getElementById('pawapay_currency');
        
        // Si un provider est déjà sélectionné, charger les devises
        if (providerSelect && providerSelect.value && currencySelect) {
            updateCurrencyField();
        }
    }, 150);
});
</script>
@endpush

@push('styles')
<style>
    /* Scoper tous les styles dans le conteneur du formulaire pour éviter les conflits avec la navbar */
    .admin-content .admin-form-card {
        background: var(--instructor-card-bg);
        border-radius: 1.25rem;
        padding: 1.75rem;
        box-shadow: 0 22px 45px -35px rgba(15, 23, 42, 0.25);
        border: 1px solid rgba(226, 232, 240, 0.7);
    }
    
    .admin-content .admin-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .admin-content .form-label {
        display: block;
        margin-bottom: 0.5rem;
        color: #0f172a;
        font-weight: 600;
    }
    
    .admin-content .form-select, 
    .admin-content .form-control {
        width: 100%;
        padding: 0.65rem 1rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        border-radius: 0.75rem;
        font-size: 0.95rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    
    .admin-content .form-select:focus, 
    .admin-content .form-control:focus {
        outline: none;
        border-color: var(--instructor-primary);
        box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
    }
    
    .admin-content .form-select:disabled, 
    .admin-content .form-control:disabled {
        background-color: #f8f9fa;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    .admin-content .form-select.is-invalid, 
    .admin-content .form-control.is-invalid {
        border-color: #dc2626;
    }
    
    .admin-content .invalid-feedback {
        display: block;
        color: #dc2626;
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }
    
    .admin-content .text-muted {
        color: #64748b;
        font-size: 0.85rem;
    }
    
    .admin-content .alert {
        padding: 1rem 1.25rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
    }
    
    .admin-content .alert-warning {
        background: rgba(234, 179, 8, 0.15);
        border: 1px solid rgba(234, 179, 8, 0.3);
        color: #b45309;
    }
    
    @media (max-width: 640px) {
        .admin-content .admin-form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        /* Scoper les styles .d-flex uniquement dans le formulaire de payment-settings */
        .admin-content form[action*="payment-settings"] > .d-flex,
        .admin-content form[action*="payment-settings"] .d-flex.gap-2 {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .admin-content form[action*="payment-settings"] .admin-btn {
            width: 100%;
        }
    }
</style>
@endpush
