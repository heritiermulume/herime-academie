@extends('ambassadors.admin.layout')

@section('admin-title', 'Effectuer un retrait')
@section('admin-subtitle', 'Retirez vos gains vers votre compte Mobile Money')

@section('admin-content')
<div class="payout-form-container">
    <div class="wallet-balance-info">
        <div class="balance-card">
            <i class="fas fa-wallet fa-2x mb-3"></i>
            <h4>Solde disponible</h4>
            <div class="balance-amount">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</div>
            <p class="text-muted mt-2">Montant minimum de retrait : 5 {{ $wallet->currency }}</p>
        </div>
    </div>

    <form action="{{ route('wallet.store-payout') }}" method="POST" id="payoutForm">
        @csrf

        <div class="payout-form-card">
            <h5 class="form-section-title"><i class="fas fa-money-bill-wave me-2"></i>Informations du retrait</h5>
            
            <div class="form-group mb-4">
                <label for="amount" class="form-label fw-bold">Montant à retirer <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" 
                           class="form-control @error('amount') is-invalid @enderror" 
                           id="amount" 
                           name="amount" 
                           value="{{ old('amount') }}"
                           placeholder="Entrez le montant"
                           min="5"
                           max="{{ $wallet->balance }}"
                           step="0.01"
                           required>
                    <span class="input-group-text">{{ $wallet->currency }}</span>
                </div>
                @error('amount')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <small class="text-muted">Solde disponible : {{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</small>
            </div>

            <div class="form-group mb-4">
                <label for="description" class="form-label fw-bold">Description (optionnel)</label>
                <input type="text" 
                       class="form-control @error('description') is-invalid @enderror" 
                       id="description" 
                       name="description" 
                       value="{{ old('description') }}"
                       placeholder="Ex: Retrait du mois de décembre"
                       maxlength="255">
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="payout-form-card">
            <h5 class="form-section-title"><i class="fas fa-mobile-alt me-2"></i>Informations de paiement</h5>
            
            @php
                $countries = $monerooData['countries'] ?? [];
                $providers = $monerooData['providers'] ?? [];
                $selectedCountry = old('country', auth()->user()->moneroo_country);
                $selectedProvider = old('method', auth()->user()->moneroo_provider);
                $selectedCurrency = old('currency', auth()->user()->moneroo_currency ?? $wallet->currency);
                $selectedPhone = old('phone', auth()->user()->moneroo_phone);
            @endphp

            @if(empty($countries) && empty($providers))
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Impossible de charger les méthodes de paiement. Veuillez vérifier la configuration de l'API.
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="country" class="form-label fw-bold">Pays <span class="text-danger">*</span></label>
                    <select class="form-select @error('country') is-invalid @enderror" 
                            id="country" 
                            name="country"
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
                    @error('country')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="method" class="form-label fw-bold">Opérateur <span class="text-danger">*</span></label>
                    <select class="form-select @error('method') is-invalid @enderror" 
                            id="method" 
                            name="method"
                            required
                            onchange="updateCurrencyField(); updatePhoneField();"
                            disabled>
                        <option value="">Sélectionner un opérateur</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider['code'] }}" 
                                    data-country="{{ $provider['country'] }}"
                                    data-currencies="{{ json_encode($provider['currencies'] ?? []) }}"
                                    style="display: {{ empty($selectedCountry) || $provider['country'] == $selectedCountry ? 'block' : 'none' }};"
                                    {{ $selectedProvider == $provider['code'] ? 'selected' : '' }}>
                                {{ $provider['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="currency" class="form-label fw-bold">Devise <span class="text-danger">*</span></label>
                    <select class="form-select @error('currency') is-invalid @enderror" 
                            id="currency" 
                            name="currency"
                            data-selected-currency="{{ $selectedCurrency }}"
                            required
                            disabled>
                        <option value="">Sélectionner une devise</option>
                    </select>
                    @error('currency')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label fw-bold">Numéro de téléphone <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" 
                           name="phone" 
                           value="{{ $selectedPhone }}"
                           placeholder="820000000"
                           required
                           disabled>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Numéro sans indicatif pays</small>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary-custom" id="submitBtn">
                <i class="fas fa-check me-2"></i>Effectuer le retrait
            </button>
            <a href="{{ route('wallet.index') }}" class="btn btn-outline-custom">
                <i class="fas fa-times me-2"></i>Annuler
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let allProviderOptions = [];

function updateProviders() {
    const countrySelect = document.getElementById('country');
    const providerSelect = document.getElementById('method');
    
    if (!countrySelect || !providerSelect) return;
    
    const selectedCountry = countrySelect.value;
    const currentProviderValue = providerSelect.value;
    
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
    
    providerSelect.innerHTML = '<option value="">Sélectionner un opérateur</option>';
    
    let hasVisibleProviders = false;
    allProviderOptions.forEach(optionData => {
        if (!selectedCountry || optionData.country === selectedCountry) {
            const newOption = optionData.element.cloneNode(true);
            newOption.style.display = 'block';
            providerSelect.appendChild(newOption);
            hasVisibleProviders = true;
        }
    });
    
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
    
    const currencySelect = document.getElementById('currency');
    if (currencySelect) {
        currencySelect.value = '';
        currencySelect.innerHTML = '<option value="">Sélectionner une devise</option>';
        currencySelect.disabled = true;
    }
    
    updateFieldsState();
    
    if (providerSelect.value) {
        updateCurrencyField();
    }
}

function updateCurrencyField() {
    const providerSelect = document.getElementById('method');
    const currencySelect = document.getElementById('currency');
    
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
    
    const currentCurrencyValue = currencySelect.value || currencySelect.getAttribute('data-selected-currency') || '';
    
    currencySelect.innerHTML = '<option value="">Sélectionner une devise</option>';
    
    if (currencies.length > 0) {
        currencies.forEach(currency => {
            const option = document.createElement('option');
            option.value = currency;
            option.textContent = currency;
            if (currentCurrencyValue === currency) {
                option.selected = true;
            }
            currencySelect.appendChild(option);
        });
        
        currencySelect.disabled = false;
    } else {
        currencySelect.disabled = true;
    }
    
    updateFieldsState();
}

function updatePhoneField() {
    updateFieldsState();
}

function updateFieldsState() {
    const countrySelect = document.getElementById('country');
    const providerSelect = document.getElementById('method');
    const currencySelect = document.getElementById('currency');
    const phoneInput = document.getElementById('phone');
    
    if (!countrySelect || !providerSelect || !currencySelect || !phoneInput) return;
    
    const hasCountry = countrySelect.value !== '';
    const hasProvider = providerSelect.value !== '';
    const hasCurrency = currencySelect.value !== '';
    
    providerSelect.disabled = !hasCountry;
    
    if (!hasProvider) {
        currencySelect.disabled = true;
    }
    
    phoneInput.disabled = !hasCountry || !hasProvider || !hasCurrency;
}

document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('method');
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
    
    updateProviders();
    updateFieldsState();
    
    setTimeout(function() {
        const providerSelect = document.getElementById('method');
        const currencySelect = document.getElementById('currency');
        
        if (providerSelect && providerSelect.value && currencySelect) {
            updateCurrencyField();
        }
    }, 150);

    // Validation du formulaire
    const form = document.getElementById('payoutForm');
    const amountInput = document.getElementById('amount');
    const maxBalance = parseFloat('{{ $wallet->balance }}');

    form.addEventListener('submit', function(e) {
        const amount = parseFloat(amountInput.value);
        
        if (isNaN(amount) || amount < 5) {
            e.preventDefault();
            alert('Le montant minimum de retrait est de 5 {{ $wallet->currency }}');
            return;
        }
        
        if (amount > maxBalance) {
            e.preventDefault();
            alert('Le montant demandé dépasse votre solde disponible (' + maxBalance.toFixed(2) + ' {{ $wallet->currency }})');
            return;
        }

        // Confirmation avant soumission
        if (!confirm(`Êtes-vous sûr de vouloir retirer ${amount.toFixed(2)} {{ $wallet->currency }} ?`)) {
            e.preventDefault();
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.payout-form-container {
    max-width: 900px;
    margin: 0 auto;
}

.wallet-balance-info {
    margin-bottom: 2rem;
}

.balance-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.balance-card h4 {
    font-size: 1rem;
    font-weight: 600;
    opacity: 0.9;
    margin-bottom: 0.5rem;
}

.balance-amount {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 1rem 0;
}

.payout-form-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.form-section-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
}

.form-label {
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-control,
.form-select {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    padding: 0.75rem 1rem;
}

.form-control:focus,
.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.input-group-text {
    background: #f9fafb;
    border: 1px solid #d1d5db;
    border-left: none;
    border-radius: 0 8px 8px 0;
    font-weight: 600;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-start;
}

.btn-primary-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
}

.btn-outline-custom {
    background: white;
    color: #374151;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    border: 2px solid #d1d5db;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-outline-custom:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

@media (max-width: 768px) {
    .payout-form-card {
        padding: 1.5rem;
    }

    .balance-amount {
        font-size: 2rem;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-primary-custom,
    .btn-outline-custom {
        width: 100%;
    }
}
</style>
@endpush

