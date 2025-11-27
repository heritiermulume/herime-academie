@extends('instructors.admin.layout')

@section('admin-title', 'Configuration de paiement')
@section('admin-subtitle', 'Configurez votre moyen de règlement pour recevoir vos paiements automatiquement via pawaPay.')

@section('admin-content')
    <form action="{{ route('instructor.payment-settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('POST')

        <div class="admin-form-card">
            <div class="admin-form-grid">
                <div class="mt-4 pt-3 border-top">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_external_instructor" name="is_external_instructor" value="1" 
                               {{ old('is_external_instructor', $instructor->is_external_instructor) ? 'checked' : '' }}
                               onchange="toggleExternalInstructorFields()">
                        <label class="form-check-label fw-bold" for="is_external_instructor">
                            Activer les paiements automatiques
                        </label>
                    </div>
                    <small class="text-muted d-block mb-3">
                        Si activé, vous recevrez automatiquement vos paiements après chaque vente de cours. Un pourcentage de commission sera déduit automatiquement.
                    </small>
                    
                    <div id="pawapay-fields" style="display: {{ old('is_external_instructor', $instructor->is_external_instructor) ? 'block' : 'none' }};">
                        @php
                            $countries = $pawapayData['countries'] ?? [];
                            $providers = $pawapayData['providers'] ?? [];
                            $selectedCountry = old('pawapay_country', $instructor->pawapay_country);
                            $selectedProvider = old('pawapay_provider', $instructor->pawapay_provider);
                        @endphp
                        
                        @if(empty($countries) && empty($providers))
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Impossible de charger les données pawaPay. Veuillez vérifier la configuration de l'API.
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
                                        onchange="updatePhoneField()"
                                        disabled>
                                    <option value="">Sélectionner un fournisseur</option>
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider['code'] }}" 
                                                data-country="{{ $provider['country'] }}"
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
                                <label for="pawapay_phone" class="form-label fw-bold">Numéro de téléphone mobile money</label>
                                <input type="text" 
                                       class="form-control @error('pawapay_phone') is-invalid @enderror" 
                                       id="pawapay_phone" 
                                       name="pawapay_phone" 
                                       value="{{ old('pawapay_phone', $instructor->pawapay_phone) }}"
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
            <a href="{{ route('instructor.dashboard') }}" class="admin-btn outline">
                <i class="fas fa-times me-2"></i>Annuler
            </a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
// Afficher/masquer les champs pawaPay
function toggleExternalInstructorFields() {
    const isExternal = document.getElementById('is_external_instructor');
    const pawapayFields = document.getElementById('pawapay-fields');
    
    if (isExternal && pawapayFields) {
        if (isExternal.checked) {
            pawapayFields.style.display = 'block';
            updateProviders(); // Initialiser les providers et l'état des champs
        } else {
            pawapayFields.style.display = 'none';
        }
    }
}

// Mettre à jour les providers disponibles selon le pays sélectionné
function updateProviders() {
    const countrySelect = document.getElementById('pawapay_country');
    const providerSelect = document.getElementById('pawapay_provider');
    const selectedCountry = countrySelect.value;
    
    // Sauvegarder la valeur actuelle du provider
    const currentProviderValue = providerSelect.value;
    
    // Récupérer toutes les options disponibles (stockées dans data attributes)
    const allOptions = Array.from(providerSelect.querySelectorAll('option[data-country]'));
    
    // Vider le select (sauf l'option par défaut)
    providerSelect.innerHTML = '<option value="">Sélectionner un fournisseur</option>';
    
    // Filtrer et ajouter les providers du pays sélectionné
    let hasVisibleProviders = false;
    allOptions.forEach(option => {
        const optionCountry = option.getAttribute('data-country');
        if (!selectedCountry || optionCountry === selectedCountry) {
            const newOption = option.cloneNode(true);
            newOption.style.display = 'block'; // S'assurer qu'il est visible
            providerSelect.appendChild(newOption);
            hasVisibleProviders = true;
        }
    });
    
    // Restaurer la valeur du provider si elle est toujours valide pour le nouveau pays
    if (currentProviderValue) {
        const selectedOption = providerSelect.querySelector(`option[value="${currentProviderValue}"]`);
        if (selectedOption && selectedOption.style.display !== 'none') {
            providerSelect.value = currentProviderValue;
        } else {
            // Si l'ancien provider n'est plus valide, réinitialiser
            providerSelect.value = '';
        }
    } else if (!hasVisibleProviders && selectedCountry !== '') {
        // Si aucun provider n'est disponible pour le pays sélectionné, réinitialiser
        providerSelect.value = '';
    }
    
    // Mettre à jour l'état des champs
    updateFieldsState();
}

// Mettre à jour l'état du champ numéro selon le provider sélectionné
function updatePhoneField() {
    updateFieldsState();
}

// Mettre à jour l'état de tous les champs selon les sélections
function updateFieldsState() {
    const countrySelect = document.getElementById('pawapay_country');
    const providerSelect = document.getElementById('pawapay_provider');
    const phoneInput = document.getElementById('pawapay_phone');
    
    if (!countrySelect || !providerSelect || !phoneInput) return;
    
    const hasCountry = countrySelect.value !== '';
    const hasProvider = providerSelect.value !== '';
    
    // Gérer le champ provider
    const wasProviderEnabled = !providerSelect.disabled;
    providerSelect.disabled = !hasCountry;
    if (providerSelect.disabled && wasProviderEnabled) {
        providerSelect.value = ''; // Effacer la valeur si désactivé
    }
    providerSelect.classList.toggle('bg-light', providerSelect.disabled);
    providerSelect.style.cursor = providerSelect.disabled ? 'not-allowed' : 'pointer';
    
    // Gérer le champ numéro
    const wasPhoneEnabled = !phoneInput.disabled;
    phoneInput.disabled = !hasCountry || !hasProvider;
    if (phoneInput.disabled && wasPhoneEnabled) {
        phoneInput.value = ''; // Effacer la valeur si désactivé
    }
    phoneInput.classList.toggle('bg-light', phoneInput.disabled);
    phoneInput.style.cursor = phoneInput.disabled ? 'not-allowed' : 'text';
}

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', function() {
    toggleExternalInstructorFields();
    
    // Initialiser les providers selon le pays sélectionné
    updateProviders();
    
    // Initialiser l'état des champs
    updateFieldsState();
});
</script>
@endpush

@push('styles')
<style>
/* Réduire l'espacement en haut sur mobile/tablette */
@media (max-width: 1024px) {
    .instructor-admin-shell {
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
    .instructor-admin-shell {
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
 
