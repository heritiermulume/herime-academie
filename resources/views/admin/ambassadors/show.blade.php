@extends('layouts.admin')

@section('title', 'Détails Ambassadeur')
@section('admin-title', 'Détails de l\'ambassadeur')
@section('admin-subtitle', 'Consultez les statistiques, commissions et informations de l\'ambassadeur')
@section('admin-actions')
    <a href="{{ route('admin.ambassadors.index') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('admin-content')
    <!-- Profil de l'ambassadeur -->
    <section class="admin-panel mb-4">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-user me-2"></i>Profil de l'ambassadeur
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="d-flex align-items-center gap-3 mb-4">
                <img src="{{ $ambassador->user->avatar_url }}" 
                     alt="{{ $ambassador->user->name }}" 
                     class="rounded-circle"
                     style="width: 80px; height: 80px; object-fit: cover;">
                <div>
                    <h5 class="mb-1">{{ $ambassador->user->name }}</h5>
                    <p class="text-muted mb-1">
                        <i class="fas fa-envelope me-2"></i>{{ $ambassador->user->email }}
                    </p>
                    <p class="mb-0">
                        <span class="badge bg-{{ $ambassador->is_active ? 'success' : 'secondary' }}">
                            {{ $ambassador->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </p>
                </div>
            </div>
            <dl class="row mb-0">
                <dt class="col-sm-4">Membre depuis</dt>
                <dd class="col-sm-8">
                    <i class="fas fa-calendar me-2"></i>
                    {{ $ambassador->user->created_at->format('d/m/Y à H:i') }}
                </dd>

                <dt class="col-sm-4">Statut</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-{{ $ambassador->is_active ? 'success' : 'secondary' }} fs-6">
                        {{ $ambassador->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </dd>

                @if($ambassador->user->phone)
                    <dt class="col-sm-4">Téléphone</dt>
                    <dd class="col-sm-8">
                        <i class="fas fa-phone me-2"></i>{{ $ambassador->user->phone }}
                    </dd>
                @endif

                @if($ambassador->user->bio)
                    <dt class="col-sm-4">Biographie</dt>
                    <dd class="col-sm-8">{{ $ambassador->user->bio }}</dd>
                @endif
            </dl>
        </div>
    </section>

    <!-- Statistiques -->
    <section class="admin-panel admin-panel--main mb-4">
        <div class="admin-panel__body">
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Gains totaux</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['total_earnings'], 2) }} {{ \App\Models\Setting::getBaseCurrency() }}</p>
                    <p class="admin-stat-card__muted">Total des commissions</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">En attente</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['pending_earnings'], 2) }} {{ \App\Models\Setting::getBaseCurrency() }}</p>
                    <p class="admin-stat-card__muted">Non encore payés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Payés</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['paid_earnings'], 2) }} {{ \App\Models\Setting::getBaseCurrency() }}</p>
                    <p class="admin-stat-card__muted">Commissions versées</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Références</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['total_referrals']) }}</p>
                    <p class="admin-stat-card__muted">Total des références</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Ventes</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['total_sales']) }}</p>
                    <p class="admin-stat-card__muted">Commandes générées</p>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5>Commissions</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Commande</th>
                                    <th>Montant</th>
                                    <th>Commission</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ambassador->commissions->take(10) as $commission)
                                    <tr>
                                        <td>{{ $commission->order->order_number }}</td>
                                        <td>{{ number_format($commission->order_total, 2) }}</td>
                                        <td>{{ number_format($commission->commission_amount, 2) }}</td>
                                        <td><span class="badge bg-{{ $commission->getStatusBadgeClass() }}">{{ $commission->getStatusLabel() }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4 promo-code-card">
                <div class="card-body">
                    <h5>Code Promo</h5>
                    @php
                        $activePromoCode = $ambassador->activePromoCode();
                    @endphp
                    @if($activePromoCode)
                        <div class="mb-3">
                            <form method="POST" action="{{ route('admin.ambassadors.update-promo-code', $ambassador) }}" id="updatePromoCodeForm">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="promo_code_id" value="{{ $activePromoCode->id }}">
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="promo_code" 
                                           name="code" 
                                           value="{{ old('code', $activePromoCode->code) }}"
                                           pattern="[A-Z0-9\-]+"
                                           maxlength="50"
                                           required
                                           style="text-transform: uppercase;">
                                    <button type="submit" class="btn btn-primary" id="updatePromoCodeBtn" title="Modifier le code promo">
                                        <i class="fas fa-save text-white"></i>
                                    </button>
                                </div>
                                <div id="promo_code_feedback" class="invalid-feedback d-block" style="display: none;"></div>
                                @error('code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Le code doit être unique et contenir uniquement des lettres majuscules, chiffres et tirets.
                                </small>
                            </form>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const promoCodeInput = document.getElementById('promo_code');
                                const updateForm = document.getElementById('updatePromoCodeForm');
                                const feedbackDiv = document.getElementById('promo_code_feedback');
                                const submitBtn = document.getElementById('updatePromoCodeBtn');
                                const promoCodeId = document.querySelector('input[name="promo_code_id"]').value;
                                let checkTimeout = null;
                                let isCodeValid = true;
                                
                                // Convertir automatiquement en majuscules lors de la saisie
                                if (promoCodeInput) {
                                    const originalCode = promoCodeInput.value;
                                    
                                    promoCodeInput.addEventListener('input', function(e) {
                                        const oldValue = e.target.value;
                                        e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
                                        
                                        // Si la valeur a changé (et n'est pas la valeur originale), vérifier l'unicité
                                        if (e.target.value !== originalCode && e.target.value.length >= 3) {
                                            // Annuler la vérification précédente si elle existe
                                            if (checkTimeout) {
                                                clearTimeout(checkTimeout);
                                            }
                                            
                                            // Masquer temporairement le feedback
                                            feedbackDiv.style.display = 'none';
                                            promoCodeInput.classList.remove('is-invalid', 'is-valid');
                                            // Désactiver temporairement le bouton pendant la vérification
                                            submitBtn.disabled = true;
                                            submitBtn.classList.remove('btn-primary');
                                            submitBtn.classList.add('btn-secondary');
                                            submitBtn.title = 'Vérification en cours...';
                                            
                                            // Vérifier après un délai de 500ms (debounce)
                                            checkTimeout = setTimeout(() => {
                                                checkCodeUniqueness(e.target.value);
                                            }, 500);
                                        } else if (e.target.value === originalCode) {
                                            // Si c'est le code original, c'est valide
                                            feedbackDiv.style.display = 'none';
                                            promoCodeInput.classList.remove('is-invalid', 'is-valid');
                                            promoCodeInput.classList.add('is-valid');
                                            isCodeValid = true;
                                            // Réactiver le bouton
                                            submitBtn.disabled = false;
                                            submitBtn.classList.remove('btn-secondary');
                                            submitBtn.classList.add('btn-primary');
                                            submitBtn.title = 'Modifier le code promo';
                                        } else if (e.target.value.length < 3) {
                                            // Code trop court, masquer le feedback
                                            feedbackDiv.style.display = 'none';
                                            promoCodeInput.classList.remove('is-invalid', 'is-valid');
                                            isCodeValid = false;
                                            // Désactiver le bouton si le code est trop court
                                            submitBtn.disabled = true;
                                            submitBtn.classList.remove('btn-primary');
                                            submitBtn.classList.add('btn-secondary');
                                            submitBtn.title = 'Le code doit contenir au moins 3 caractères';
                                        }
                                    });
                                    
                                    // Fonction pour vérifier l'unicité du code
                                    function checkCodeUniqueness(code) {
                                        if (!code || code.length < 3) {
                                            isCodeValid = false;
                                            return;
                                        }
                                        
                                        const url = '{{ route("admin.ambassadors.check-promo-code", $ambassador) }}' + 
                                                   '?code=' + encodeURIComponent(code) + 
                                                   '&promo_code_id=' + promoCodeId;
                                        
                                        fetch(url, {
                                            method: 'GET',
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest',
                                                'Accept': 'application/json'
                                            }
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.available) {
                                                // Code disponible
                                                feedbackDiv.style.display = 'none';
                                                promoCodeInput.classList.remove('is-invalid');
                                                promoCodeInput.classList.add('is-valid');
                                                isCodeValid = true;
                                                // Réactiver le bouton
                                                submitBtn.disabled = false;
                                                submitBtn.classList.remove('btn-secondary');
                                                submitBtn.classList.add('btn-primary');
                                                submitBtn.title = 'Modifier le code promo';
                                            } else {
                                                // Code déjà utilisé
                                                feedbackDiv.textContent = data.message || 'Ce code promo est déjà utilisé par un autre ambassadeur.';
                                                feedbackDiv.style.display = 'block';
                                                promoCodeInput.classList.remove('is-valid');
                                                promoCodeInput.classList.add('is-invalid');
                                                isCodeValid = false;
                                                // Désactiver le bouton
                                                submitBtn.disabled = true;
                                                submitBtn.classList.remove('btn-primary');
                                                submitBtn.classList.add('btn-secondary');
                                                submitBtn.title = 'Le code promo est déjà utilisé. Veuillez en choisir un autre.';
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error checking code uniqueness:', error);
                                            isCodeValid = false;
                                            // Désactiver le bouton en cas d'erreur
                                            submitBtn.disabled = true;
                                            submitBtn.classList.remove('btn-primary');
                                            submitBtn.classList.add('btn-secondary');
                                        });
                                    }
                                    
                                    // Soumission AJAX du formulaire
                                    updateForm.addEventListener('submit', function(e) {
                                        e.preventDefault();
                                        
                                        // Formater la valeur
                                        promoCodeInput.value = promoCodeInput.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
                                        
                                        // Vérifier une dernière fois avant de soumettre
                                        if (!isCodeValid || promoCodeInput.classList.contains('is-invalid')) {
                                            // Si le code n'est pas valide, vérifier immédiatement
                                            checkCodeUniqueness(promoCodeInput.value);
                                            
                                            // Attendre un peu pour laisser la vérification se terminer
                                            setTimeout(() => {
                                                if (!isCodeValid || promoCodeInput.classList.contains('is-invalid')) {
                                                    showToast('Ce code promo est déjà utilisé. Veuillez en choisir un autre.', 'error');
                                                    return;
                                                }
                                                submitForm();
                                            }, 300);
                                        } else {
                                            submitForm();
                                        }
                                        
                                        function submitForm() {
                                            // Désactiver le bouton pendant la requête
                                            const originalHtml = submitBtn.innerHTML;
                                            submitBtn.disabled = true;
                                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-white"></i>';
                                            
                                            // Préparer les données du formulaire
                                            const formData = new FormData(updateForm);
                                            
                                            // Envoyer la requête AJAX
                                            fetch(updateForm.action, {
                                                method: 'POST',
                                                body: formData,
                                                headers: {
                                                    'X-Requested-With': 'XMLHttpRequest',
                                                }
                                            })
                                            .then(response => {
                                                if (response.redirected) {
                                                    // Si redirection, suivre la redirection pour récupérer les messages de session
                                                    window.location.href = response.url;
                                                    return;
                                                }
                                                return response.json();
                                            })
                                            .then(data => {
                                                if (data) {
                                                    if (data.success) {
                                                        showToast(data.message || 'Code promo mis à jour avec succès', 'success');
                                                        // Mettre à jour la valeur du champ si nécessaire
                                                        if (data.code) {
                                                            promoCodeInput.value = data.code;
                                                            // Réinitialiser le feedback
                                                            feedbackDiv.style.display = 'none';
                                                            promoCodeInput.classList.remove('is-invalid', 'is-valid');
                                                            promoCodeInput.classList.add('is-valid');
                                                            isCodeValid = true;
                                                            // Réactiver le bouton
                                                            submitBtn.disabled = false;
                                                            submitBtn.classList.remove('btn-secondary');
                                                            submitBtn.classList.add('btn-primary');
                                                            submitBtn.title = 'Modifier le code promo';
                                                        }
                                                    } else {
                                                        showToast(data.message || 'Erreur lors de la mise à jour', 'error');
                                                        // Afficher les erreurs de validation
                                                        if (data.errors && data.errors.code) {
                                                            feedbackDiv.textContent = data.errors.code[0];
                                                            feedbackDiv.style.display = 'block';
                                                            promoCodeInput.classList.add('is-invalid');
                                                            isCodeValid = false;
                                                        }
                                                    }
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error:', error);
                                                showToast('Une erreur est survenue lors de la mise à jour', 'error');
                                            })
                                            .finally(() => {
                                                // Réactiver le bouton
                                                submitBtn.disabled = false;
                                                submitBtn.innerHTML = originalHtml;
                                            });
                                        }
                                    });
                                }
                            });
                        </script>
                        <div class="mb-3">
                            <p class="mb-1"><strong>Utilisations:</strong> {{ $activePromoCode->usage_count }}</p>
                            @if($activePromoCode->max_usage)
                                <p class="mb-1"><strong>Maximum:</strong> {{ $activePromoCode->max_usage }}</p>
                            @endif
                            @if($activePromoCode->expires_at)
                                <p class="mb-0"><strong>Expire le:</strong> {{ $activePromoCode->expires_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    @else
                        <p class="text-muted mb-3">Aucun code promo actif</p>
                    @endif
                    <form method="POST" action="{{ route('admin.ambassadors.generate-promo-code', $ambassador) }}" id="generatePromoCodeForm">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100" id="generatePromoCodeBtn">
                            <i class="fas fa-plus"></i> Générer nouveau code promo
                        </button>
                    </form>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const generateForm = document.getElementById('generatePromoCodeForm');
                            const generateBtn = document.getElementById('generatePromoCodeBtn');
                            
                            if (generateForm) {
                                generateForm.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    
                                    // Désactiver le bouton pendant la requête
                                    const originalHtml = generateBtn.innerHTML;
                                    generateBtn.disabled = true;
                                    generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Génération...';
                                    
                                    // Préparer les données du formulaire
                                    const formData = new FormData(generateForm);
                                    
                                    // Envoyer la requête AJAX
                                    fetch(generateForm.action, {
                                        method: 'POST',
                                        body: formData,
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest',
                                        }
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            showToast(data.message, 'success');
                                            
                                            // Recharger la page pour afficher le nouveau code promo
                                            setTimeout(() => {
                                                window.location.reload();
                                            }, 1000);
                                        } else {
                                            showToast(data.message || 'Erreur lors de la génération', 'error');
                                            generateBtn.disabled = false;
                                            generateBtn.innerHTML = originalHtml;
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        showToast('Une erreur est survenue lors de la génération', 'error');
                                        generateBtn.disabled = false;
                                        generateBtn.innerHTML = originalHtml;
                                    });
                                });
                            }
                        });
                    </script>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5>Actions</h5>
                    <form method="POST" action="{{ route('admin.ambassadors.toggle-active', $ambassador) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-{{ $ambassador->is_active ? 'warning' : 'success' }} w-100">
                            {{ $ambassador->is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Système de toast moderne
function showToast(message, type = 'success') {
    // Créer le conteneur de toast s'il n'existe pas
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }

    // Créer le toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    const icon = icons[type] || icons['info'];
    
    toast.innerHTML = `
        <div class="toast__icon">
            <i class="fas ${icon}"></i>
        </div>
        <div class="toast__content">
            <div class="toast__message">${message}</div>
        </div>
        <button class="toast__close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Ajouter le toast au conteneur
    toastContainer.appendChild(toast);

    // Animation d'entrée
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Suppression automatique après 4 secondes
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }, 4000);
}

// Afficher les messages de session Laravel avec des toasts
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif
    
    @if(session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif
    
    @if($errors->any())
        @foreach($errors->all() as $error)
            showToast('{{ $error }}', 'error');
        @endforeach
    @endif
});
</script>
@endpush

@push('styles')
<style>
    .toast-container {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        max-width: 400px;
        pointer-events: none;
    }

    .toast {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 40px -20px rgba(0, 0, 0, 0.3);
        border-left: 4px solid #22c55e;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        pointer-events: auto;
        min-width: 300px;
    }

    .toast.show {
        opacity: 1;
        transform: translateX(0);
    }

    .toast-success {
        border-left-color: #22c55e;
    }

    .toast-info {
        border-left-color: #0ea5e9;
    }

    .toast-warning {
        border-left-color: #f59e0b;
    }

    .toast-error {
        border-left-color: #dc2626;
    }

    .toast__icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .toast-success .toast__icon {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
    }

    .toast-info .toast__icon {
        background: rgba(14, 165, 233, 0.15);
        color: #0ea5e9;
    }

    .toast-warning .toast__icon {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }

    .toast-error .toast__icon {
        background: rgba(220, 38, 38, 0.15);
        color: #dc2626;
    }

    .toast__content {
        flex: 1;
        min-width: 0;
    }

    .toast__message {
        font-size: 0.95rem;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.4;
        word-break: break-word;
    }

    .toast__close {
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s ease;
        flex-shrink: 0;
        width: 28px;
        height: 28px;
    }

    .toast__close:hover {
        background: rgba(0, 0, 0, 0.05);
        color: #0f172a;
    }

    @media (max-width: 640px) {
        .toast-container {
            top: 70px;
            right: 10px;
            left: 10px;
            max-width: none;
        }
        
        .toast {
            min-width: auto;
        }
    }

    /* Styles personnalisés pour les statistiques de l'ambassadeur */
    .admin-panel--main .admin-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .admin-panel--main .admin-stat-card {
        padding: 0.75rem 1rem;
    }

    @media (max-width: 767.98px) {
        .admin-panel--main .admin-stats-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Réduire le padding de la carte Code Promo sur desktop */
    @media (min-width: 768px) {
        .promo-code-card .card-body {
            padding: 1rem;
        }
    }
</style>
@endpush

