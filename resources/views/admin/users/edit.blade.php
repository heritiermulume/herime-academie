@extends('layouts.admin')

@section('title', "Modifier l'utilisateur")
@section('admin-title', "Modifier l'utilisateur")
@section('admin-subtitle', "Ajustez le rôle et le statut de " . ($user->name ?? 'cet utilisateur') . " (données SSO synchronisées)")
@section('admin-actions')
    <a href="{{ route('admin.users') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('admin-content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Erreurs de validation</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="admin-panel">
        <div class="admin-panel__body admin-panel__body--padded">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="admin-form-grid admin-form-grid--two">
                    <div class="admin-form-card">
                        <h5><i class="fas fa-user me-2"></i>Profil synchronisé</h5>
                        <!-- Avertissement Compte Herime -->
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Gestion via Compte Herime :</strong> Les informations personnelles (nom, email, photo) sont gérées via Compte Herime (compte.herime.com) et seront synchronisées automatiquement lors de la prochaine connexion de l'utilisateur. Seuls le rôle et le statut actif peuvent être modifiés ici.
                        </div>
                        <div class="text-center mb-3">
                            <div style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; margin: 0 auto;">
                                <img src="{{ $user->avatar_url }}" alt="Avatar actuel" class="img-fluid" style="width:100%; height:100%; object-fit:cover;">
                            </div>
                            <p class="text-muted small mt-2">
                                <i class="fas fa-info-circle me-1"></i>Photo, nom et email sont gérés via <a href="{{ config('services.sso.base_url') }}" target="_blank">compte.herime.com</a>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nom complet</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->name }}" readonly disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly disabled>
                        </div>
                        <div class="alert alert-secondary mb-0">
                            <i class="fas fa-info-circle me-2"></i>Les informations complémentaires (téléphone, biographie, etc.) sont synchronisées à la connexion.
                        </div>
                    </div>

                    <div class="admin-form-card">
                        <h5><i class="fas fa-cog me-2"></i>Rôle & Paramètres</h5>
                        <div class="admin-form-grid">
                            <div>
                                <label for="role" class="form-label fw-bold">Rôle <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required onchange="toggleExternalInstructorFields()">
                                    <option value="">Sélectionner un rôle</option>
                                    <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Étudiant</option>
                                    <option value="instructor" {{ old('role', $user->role) == 'instructor' ? 'selected' : '' }}>Formateur</option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                    <option value="affiliate" {{ old('role', $user->role) == 'affiliate' ? 'selected' : '' }}>Affilié</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="form-label fw-bold">Statut</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Utilisateur actif</label>
                                </div>
                                <small class="text-muted">Désactivez pour bloquer l'accès à la plateforme.</small>
                                <div class="mt-3">
                                    <span class="admin-chip {{ $user->is_verified ? 'admin-chip--success' : 'admin-chip--warning' }}">
                                        <i class="fas fa-{{ $user->is_verified ? 'check' : 'clock' }} me-1"></i>Email {{ $user->is_verified ? 'vérifié' : 'non vérifié' }}
                                    </span>
                                    <small class="text-muted ms-2">(Géré par Compte Herime)</small>
                                </div>
                            </div>
                        </div>
                        
                        <div id="external-instructor-section" class="mt-4 pt-3 border-top" style="display: {{ $user->role === 'instructor' ? 'block' : 'none' }};">
                            <h6 class="mb-3"><i class="fas fa-money-bill-wave me-2"></i>Formateur externe (Moneroo)</h6>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_external_instructor" name="is_external_instructor" value="1" 
                                       {{ old('is_external_instructor', $user->is_external_instructor) ? 'checked' : '' }}
                                       onchange="toggleExternalInstructorFields()"
                                       {{ $user->role !== 'instructor' ? 'disabled' : '' }}>
                                <label class="form-check-label fw-bold" for="is_external_instructor">
                                    Formateur externe
                                </label>
                            </div>
                            <small class="text-muted d-block mb-3">
                                Si activé, ce formateur recevra automatiquement ses paiements via Moneroo après chaque vente de cours.
                            </small>
                            
                            <div id="moneroo-fields" style="display: {{ old('is_external_instructor', $user->is_external_instructor) && $user->role === 'instructor' ? 'block' : 'none' }};">
                                @php
                                    $countries = $monerooData['countries'] ?? ($pawapayData['countries'] ?? []);
                                    $providers = $monerooData['providers'] ?? ($pawapayData['providers'] ?? []);
                                    $selectedCountry = old('pawapay_country', $user->pawapay_country);
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
                                        <small class="text-muted">Sélectionnez le pays du formateur</small>
                                    </div>
                                    <div>
                                        <label for="pawapay_provider" class="form-label fw-bold">Fournisseur</label>
                                        <select class="form-select @error('pawapay_provider') is-invalid @enderror" 
                                                id="pawapay_provider" 
                                                name="pawapay_provider"
                                                onchange="updatePhoneField()">
                                            <option value="">Sélectionner un provider</option>
                                            @foreach($providers as $provider)
                                                <option value="{{ $provider['code'] }}" 
                                                        data-country="{{ $provider['country'] }}"
                                                        style="display: {{ empty($selectedCountry) || $provider['country'] == $selectedCountry ? 'block' : 'none' }};"
                                                        {{ old('pawapay_provider', $user->pawapay_provider) == $provider['code'] && (empty($selectedCountry) || $provider['country'] == $selectedCountry) ? 'selected' : '' }}>
                                                    {{ $provider['name'] }} ({{ $provider['code'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('pawapay_provider')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Sélectionnez le provider mobile money</small>
                                    </div>
                                    <div>
                                        <label for="pawapay_phone" class="form-label fw-bold">Numéro de téléphone Moneroo</label>
                                        <input type="text" 
                                               class="form-control @error('pawapay_phone') is-invalid @enderror" 
                                               id="pawapay_phone" 
                                               name="pawapay_phone" 
                                               value="{{ old('pawapay_phone', $user->pawapay_phone) }}"
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
                <div class="admin-form-card">
                    <h5><i class="fas fa-history me-2"></i>Historique & infos</h5>
                    <div class="admin-form-grid admin-form-grid--two">
                        <div>
                            <label class="form-label fw-bold">Date d'inscription</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->created_at->format('d/m/Y H:i') }}" readonly disabled>
                        </div>
                        <div>
                            <label class="form-label fw-bold">Dernière connexion</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais' }}" readonly disabled>
                        </div>
                        <div>
                            <label class="form-label fw-bold">Dernière mise à jour</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->updated_at->format('d/m/Y H:i') }}" readonly disabled>
                        </div>
                        <div>
                            <label class="form-label fw-bold">Profils sociaux (SSO)</label>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="admin-chip admin-chip--neutral"><i class="fab fa-linkedin me-1"></i>LinkedIn</span>
                                <span class="admin-chip admin-chip--neutral"><i class="fab fa-twitter me-1"></i>Twitter</span>
                                <span class="admin-chip admin-chip--neutral"><i class="fab fa-facebook-f me-1"></i>Facebook</span>
                                <span class="admin-chip admin-chip--neutral"><i class="fas fa-globe me-1"></i>Site</span>
                            </div>
                            <small class="text-muted d-block mt-1">Gérés via Compte Herime lorsqu’ils sont fournis.</small>
                        </div>
                    </div>
                </div>

                <div class="admin-panel__footer d-flex justify-content-between flex-wrap gap-2">
                    <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<style>
/* Design moderne pour formulaire d'édition */
.card {
    border-radius: 15px;
    overflow: hidden;
}

.card-header.text-white {
    background: linear-gradient(135deg, #003366 0%, #004080 100%) !important;
    border: none;
    padding: 1.5rem;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #003366 0%, #004080 100%) !important;
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
}

.form-control:focus, .form-select:focus {
    border-color: #003366;
    box-shadow: 0 0 0 0.2rem rgba(0, 51, 102, 0.25);
}

.form-check-input:checked {
    background-color: #003366;
    border-color: #003366;
}

.form-label.fw-bold {
    color: #003366;
}

.card-body {
    padding: 1.5rem;
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

/* Preview d'images */
.current-avatar img {
    transition: transform 0.2s ease;
}

.current-avatar img:hover {
    transform: scale(1.05);
}

/* Zone d'upload moderne */
.upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    overflow: hidden;
}

.upload-zone:hover {
    border-color: #17a2b8;
    background-color: #e9ecef;
}

.upload-placeholder {
    cursor: pointer;
    transition: all 0.2s ease;
}

.upload-placeholder:hover {
    background-color: rgba(23, 162, 184, 0.05);
}

.upload-placeholder:hover i {
    transform: scale(1.1);
    color: #138496 !important;
}

.upload-placeholder i {
    transition: transform 0.2s ease;
}

.upload-preview {
    padding: 1.5rem;
}

.upload-preview img {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.upload-preview img:hover {
    transform: scale(1.05);
}

.upload-info {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.upload-info .badge {
    font-size: 0.85rem;
    padding: 0.4em 0.8em;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .card-header.text-white {
        padding: 1rem;
    }
    
    .card-header h4 {
        font-size: 1.1rem;
    }
    
    .card-header .small {
        font-size: 0.8rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn-outline-light.btn-sm {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Constantes de validation
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const VALID_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

// Gestion de l'upload d'avatar
function handleAvatarUpload(input) {
    const zone = document.getElementById('avatarUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const errorDiv = document.getElementById('avatarError');
    
    // Réinitialiser les erreurs
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validation du type
        if (!VALID_IMAGE_TYPES.includes(file.type)) {
            showAvatarError('❌ Format invalide. Utilisez JPG, PNG ou WEBP.');
            input.value = '';
            return;
        }
        
        // Validation de la taille
        if (file.size > MAX_FILE_SIZE) {
            showAvatarError('❌ Le fichier est trop volumineux. Maximum 5MB.');
            input.value = '';
            return;
        }
        
        // Afficher la preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = preview.querySelector('img');
            img.src = e.target.result;
            
            // Afficher les informations du fichier
            preview.querySelector('.file-name').textContent = file.name;
            preview.querySelector('.file-size').textContent = formatFileSize(file.size);
            
            // Basculer l'affichage
            placeholder.classList.add('d-none');
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
}

// Effacer la sélection
function clearAvatar() {
    const zone = document.getElementById('avatarUploadZone');
    const placeholder = zone.querySelector('.upload-placeholder');
    const preview = zone.querySelector('.upload-preview');
    const input = document.getElementById('avatar');
    const errorDiv = document.getElementById('avatarError');
    
    // Réinitialiser
    input.value = '';
    preview.querySelector('img').src = '';
    errorDiv.textContent = '';
    errorDiv.style.display = 'none';
    
    // Basculer l'affichage
    preview.classList.add('d-none');
    placeholder.classList.remove('d-none');
}

// Afficher une erreur
function showAvatarError(message) {
    const errorDiv = document.getElementById('avatarError');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

// Formater la taille du fichier
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Afficher/masquer les champs Moneroo et la section complète
function toggleExternalInstructorFields() {
    const roleSelect = document.getElementById('role');
    const role = roleSelect ? roleSelect.value : '';
    const isExternal = document.getElementById('is_external_instructor');
    const monerooFields = document.getElementById('moneroo-fields');
    const externalInstructorSection = document.getElementById('external-instructor-section');
    
    // Si le rôle n'est pas "instructor", masquer toute la section et désactiver la checkbox
    if (role !== 'instructor') {
        if (externalInstructorSection) {
            externalInstructorSection.style.display = 'none';
        }
        if (isExternal) {
            isExternal.disabled = true;
            isExternal.checked = false;
        }
        if (monerooFields) {
            monerooFields.style.display = 'none';
        }
        return;
    }
    
    // Si le rôle est "instructor", afficher la section et activer la checkbox
    if (externalInstructorSection) {
        externalInstructorSection.style.display = 'block';
    }
    if (isExternal) {
        isExternal.disabled = false;
    }
    
    // Afficher/masquer les champs Moneroo selon l'état de la checkbox
    if (isExternal && monerooFields) {
        if (isExternal.checked) {
            monerooFields.style.display = 'block';
        } else {
            monerooFields.style.display = 'none';
        }
    }
}

// Mettre à jour les providers selon le pays sélectionné (comme sur la page de paiement)
function updateProviders() {
    const countrySelect = document.getElementById('pawapay_country');
    const providerSelect = document.getElementById('pawapay_provider');
    
    if (!countrySelect || !providerSelect) return;
    
    const selectedCountry = countrySelect.value;
    const currentValue = providerSelect.value;
    
    // Récupérer toutes les options de providers
    const allOptions = Array.from(providerSelect.querySelectorAll('option[data-country]'));
    
    // Filtrer et afficher/masquer les options selon le pays sélectionné
    allOptions.forEach(option => {
        const optionCountry = option.getAttribute('data-country');
        
        if (!selectedCountry || optionCountry === selectedCountry) {
            // Afficher l'option si elle correspond au pays sélectionné
            option.style.display = 'block';
        } else {
            // Masquer l'option si elle ne correspond pas
            option.style.display = 'none';
            // Désélectionner si elle était sélectionnée
            if (option.selected) {
                option.selected = false;
            }
        }
    });
    
    // Si le provider actuellement sélectionné ne correspond pas au nouveau pays, le réinitialiser
    if (currentValue) {
        const selectedOption = providerSelect.querySelector(`option[value="${currentValue}"]`);
        if (selectedOption && selectedOption.style.display === 'none') {
            providerSelect.value = '';
            // Effacer aussi le numéro si le provider est réinitialisé
            const phoneInput = document.getElementById('pawapay_phone');
            if (phoneInput) {
                phoneInput.value = '';
            }
        }
    }
    
    // Si aucun provider visible, réinitialiser
    const visibleOptions = allOptions.filter(opt => opt.style.display !== 'none');
    if (visibleOptions.length === 0) {
        providerSelect.value = '';
        // Effacer aussi le numéro si aucun provider n'est disponible
        const phoneInput = document.getElementById('pawapay_phone');
        if (phoneInput) {
            phoneInput.value = '';
        }
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
    
    // Désactiver le champ provider si aucun pays n'est sélectionné
    const wasProviderDisabled = providerSelect.disabled;
    providerSelect.disabled = !hasCountry;
    
    // Si le provider vient d'être désactivé, effacer sa valeur
    if (!wasProviderDisabled && providerSelect.disabled) {
        providerSelect.value = '';
    }
    
    // Désactiver le champ numéro si aucun pays OU aucun provider n'est sélectionné
    const wasPhoneDisabled = phoneInput.disabled;
    phoneInput.disabled = !hasCountry || !hasProvider;
    
    // Si le numéro vient d'être désactivé, effacer sa valeur
    if (!wasPhoneDisabled && phoneInput.disabled) {
        phoneInput.value = '';
    }
    
    // Ajouter un style visuel pour indiquer que le champ est désactivé
    if (providerSelect.disabled) {
        providerSelect.classList.add('bg-light');
        providerSelect.style.cursor = 'not-allowed';
    } else {
        providerSelect.classList.remove('bg-light');
        providerSelect.style.cursor = 'pointer';
    }
    
    if (phoneInput.disabled) {
        phoneInput.classList.add('bg-light');
        phoneInput.style.cursor = 'not-allowed';
    } else {
        phoneInput.classList.remove('bg-light');
        phoneInput.style.cursor = 'text';
    }
}

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', function() {
    toggleExternalInstructorFields();
    
    // Initialiser les providers selon le pays sélectionné
    updateProviders();
    
    // Initialiser l'état des champs
    updateFieldsState();
    
    // Écouter les changements de rôle
    const roleSelect = document.getElementById('role');
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            toggleExternalInstructorFields();
            // Réinitialiser les champs Moneroo si le rôle change
            const countrySelect = document.getElementById('pawapay_country');
            const providerSelect = document.getElementById('pawapay_provider');
            const phoneInput = document.getElementById('pawapay_phone');
            
            if (this.value !== 'instructor') {
                // Si le rôle n'est plus "instructor", effacer tous les champs Moneroo
                if (countrySelect) countrySelect.value = '';
                if (providerSelect) providerSelect.value = '';
                if (phoneInput) phoneInput.value = '';
            }
        });
    }
});
</script>
@endpush
