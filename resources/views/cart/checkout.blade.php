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

    <div class="checkout-wrapper" style="padding: 24px 0;">
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

                <!-- Payment Methods -->
                <div class="payment-section">
                    <h4 class="section-title mb-4">
                        <i class="fas fa-credit-card me-2"></i>Choisissez votre mode de paiement
                    </h4>

                    <form id="paymentForm" method="POST" action="{{ route('maxicash.process') }}">
                        @csrf
                        
                        <!-- Payment Method Selection -->
                        <div class="payment-methods mb-4">
                            <div class="row g-3">
                                <!-- MaxiCash Payment -->
                                <div class="col-md-6">
                                    <div class="payment-option" data-method="maxicash">
                                        <input type="radio" name="payment_method" value="maxicash" id="maxicash" class="payment-radio" checked>
                                        <label for="maxicash" class="payment-label">
                                            <div class="payment-icon">
                                                <i class="fas fa-wallet"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h6>MaxiCash</h6>
                                                <p>Paiement sécurisé par portefeuille numérique</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- WhatsApp Order -->
                                <div class="col-md-6">
                                    <div class="payment-option" data-method="whatsapp">
                                        <input type="radio" name="payment_method" value="whatsapp" id="whatsapp" class="payment-radio">
                                        <label for="whatsapp" class="payment-label">
                                            <div class="payment-icon">
                                                <i class="fab fa-whatsapp"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h6>Commande WhatsApp</h6>
                                                <p>Envoyer votre commande via WhatsApp</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Forms -->
                        <div class="payment-forms">
                            <!-- MaxiCash Payment Form -->
                            <div id="maxicashForm" class="maxicash-form" style="display: block;">
                                <div class="form-section">
                                    <h5 class="form-title">Paiement avec MaxiCash</h5>
                                        
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                            <label for="maxicash_phone" class="form-label">
                                                    <i class="fas fa-phone me-1"></i>Numéro de téléphone
                                                </label>
                                            <input type="tel" class="form-control" id="maxicash_phone" name="maxicash_phone" 
                                                       placeholder="+243 00 000 0000" required>
                                            <small class="form-text text-muted">Pour les paiements Mobile Money</small>
                                            </div>
                                                    <div class="col-md-6">
                                            <label for="maxicash_email" class="form-label">
                                                <i class="fas fa-envelope me-1"></i>Email (recommandé)
                                            </label>
                                            <input type="email" class="form-control" id="maxicash_email" name="maxicash_email" 
                                                               placeholder="votre@email.com">
                                            <small class="form-text text-muted">Pour recevoir la confirmation de paiement</small>
                                                </div>
                                            </div>

                                        <div class="alert alert-success">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        <strong>Paiement sécurisé SSL :</strong> Votre transaction est protégée par un chiffrement SSL de niveau bancaire.
                                    </div>
                                </div>
                            </div>

                            <!-- WhatsApp Form -->
                            <div id="whatsappForm" class="whatsapp-form" style="display: none;">
                                <div class="form-section">
                                    <div class="alert alert-info">
                                        <i class="fab fa-whatsapp me-2"></i>
                                        <strong>Commande WhatsApp</strong><br>
                                        Envoyez votre commande directement sur WhatsApp. Notre équipe vous contactera pour confirmer votre achat.
                                    </div>
                                    
                                    <div class="alert alert-warning">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Important :</strong> Votre commande sera traitée manuellement. Vous recevrez un email de confirmation une fois le paiement validé par notre équipe.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Information - Only for WhatsApp -->
                        <div class="billing-section mt-4" id="billingSection" style="display: none;">
                            <h5 class="form-title">Informations de facturation</h5>
                            <p class="text-muted mb-3">Pour finaliser votre commande WhatsApp, veuillez remplir vos informations</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="billing_first_name" class="form-label">Prénom</label>
                                    <input type="text" class="form-control" id="billing_first_name" name="billing_first_name" value="{{ auth()->user()->first_name ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="billing_last_name" class="form-label">Nom</label>
                                    <input type="text" class="form-control" id="billing_last_name" name="billing_last_name" value="{{ auth()->user()->last_name ?? '' }}">
                                </div>
                                <div class="col-12">
                                    <label for="billing_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="billing_email" name="billing_email" value="{{ auth()->user()->email ?? '' }}">
                                </div>
                                <div class="col-12">
                                    <label for="billing_address" class="form-label">Adresse</label>
                                    <textarea class="form-control" id="billing_address" name="billing_address" rows="3" placeholder="Votre adresse complète"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="terms-section mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    J'accepte les <a href="{{ route('legal.terms') }}" target="_blank" class="text-primary">conditions générales de vente</a> et la <a href="{{ route('legal.privacy') }}" target="_blank" class="text-primary">politique de confidentialité</a>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="payment-actions mt-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100" id="payButton">
                                        <span id="payButtonText">Payer avec MaxiCash ${{ number_format($total, 2) }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

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
                                ${{ number_format($item['subtotal'], 2) }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="order-total mt-3">
                        <div class="d-flex justify-content-between">
                            <strong>Total :</strong>
                            <strong class="text-primary fs-4">${{ number_format($total, 2) }}</strong>
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
// Simple and direct payment method switching
document.addEventListener('DOMContentLoaded', function() {
    const maxicashRadio = document.getElementById('maxicash');
    const whatsappRadio = document.getElementById('whatsapp');
    const maxicashForm = document.getElementById('maxicashForm');
    const whatsappForm = document.getElementById('whatsappForm');
    const payButtonText = document.getElementById('payButtonText');
    const billingSection = document.getElementById('billingSection');
    
    // MaxiCash handler
    if (maxicashRadio) {
        maxicashRadio.addEventListener('change', function() {
            maxicashForm.style.display = 'block';
            whatsappForm.style.display = 'none';
            if (billingSection) billingSection.style.display = 'none';
            if (payButtonText) {
                payButtonText.textContent = 'Payer avec MaxiCash ${{ number_format($total, 2) }}';
            }
        });
    }
    
    // WhatsApp handler  
    if (whatsappRadio) {
        whatsappRadio.addEventListener('change', function() {
            whatsappForm.style.display = 'block';
            maxicashForm.style.display = 'none';
            if (billingSection) billingSection.style.display = 'block';
            if (payButtonText) {
                payButtonText.textContent = 'Envoyer la commande WhatsApp';
            }
            setTimeout(() => {
                if (billingSection) billingSection.scrollIntoView({ behavior: 'smooth' });
            }, 100);
        });
    }

    // Form submission
    const paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!selectedMethod) {
                alert('Veuillez sélectionner un mode de paiement.');
                return;
            }
            
            const paymentMethod = selectedMethod.value;
            const terms = document.getElementById('terms');
            
            if (!terms || !terms.checked) {
                alert('Veuillez accepter les conditions générales de vente.');
                return;
            }
            
            if (paymentMethod === 'maxicash') {
                const phone = document.getElementById('maxicash_phone').value;
                if (!phone) {
                    alert('Veuillez saisir votre numéro de téléphone.');
                    return;
                }
                handleMaxiCashPayment();
            } else if (paymentMethod === 'whatsapp') {
                // Validation des champs de facturation
                const firstName = document.getElementById('billing_first_name').value.trim();
                const lastName = document.getElementById('billing_last_name').value.trim();
                const email = document.getElementById('billing_email').value.trim();
                
                if (!firstName || !lastName || !email) {
                    alert('Veuillez remplir toutes les informations de facturation (Prénom, Nom et Email).');
                    return;
                }
                
                handleWhatsAppPayment();
            }
        });
    }

    function handleMaxiCashPayment() {
        const phone = document.getElementById('maxicash_phone').value;
        const email = document.getElementById('maxicash_email').value;
        const user = @json(auth()->user() ?? null);
        
        if (!phone) {
            alert('Veuillez saisir votre numéro de téléphone.');
            return;
        }
        
        payButton.disabled = true;
        payButtonText.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Redirection vers MaxiCash...';
        
        // Récupérer les données du panier
        const cartItems = @json($cartItems ?? []);
        const total = {{ $total ?? 0 }};
        
        // Générer une référence unique
        const reference = 'MAXI-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9).toUpperCase();
        
        // Préparer les données de paiement
        const paymentData = {
            phone: phone,
            email: email || (user ? user.email : ''),
            total: total,
            cart_items: cartItems,
            reference: reference,
            _token: '{{ csrf_token() }}'
        };
        
        // Créer et soumettre le formulaire
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("maxicash.process") }}';
        form.style.display = 'none';
        
        for (const key in paymentData) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = typeof paymentData[key] === 'object' ? JSON.stringify(paymentData[key]) : paymentData[key];
            form.appendChild(input);
        }
        
        document.body.appendChild(form);
        form.submit();
    }

    function handleWhatsAppPayment() {
        // Récupérer les informations de facturation
        const firstName = document.getElementById('billing_first_name').value.trim();
        const lastName = document.getElementById('billing_last_name').value.trim();
        const email = document.getElementById('billing_email').value.trim();
        const address = document.getElementById('billing_address').value.trim();
        
        // Construire le message WhatsApp
        let message = '*🛒 COMMANDE - HERIME ACADÉMIE*\n\n';
        message += '*Informations Client:*\n';
        message += 'Nom: ' + firstName + ' ' + lastName + '\n';
        message += 'Email: ' + email + '\n';
        if (address) {
            message += 'Adresse: ' + address + '\n';
        }
        message += '\n*Articles Commandés:*\n';
        
        // Ajouter les cours
        let courses = '';
        @foreach($cartItems as $item)
            courses += '{{ $loop->iteration }}. {{ $item['course']->title ?? 'Cours' }} - ${{ number_format($item['subtotal'] ?? 0, 2) }}\n';
        @endforeach
        message += courses;
        
        message += '\n*💰 TOTAL: ${{ number_format($total, 2) }}*\n';
        message += '\n📅 Date: ' + new Date().toLocaleDateString('fr-FR') + ' ' + new Date().toLocaleTimeString('fr-FR');
        message += '\n\n✅ Merci pour votre commande!';
        
        // Encoder et ouvrir WhatsApp
        const whatsappNumber = '243850478400';
        const whatsappUrl = 'https://wa.me/' + whatsappNumber + '?text=' + encodeURIComponent(message);
        window.open(whatsappUrl, '_blank');
    }
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
    
    /* Réduire le padding du wrapper principal */
    .checkout-page .checkout-wrapper {
        padding: 16px 20px !important;
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
    
    /* Réduire le padding du wrapper principal */
    .checkout-page .checkout-wrapper {
        padding: 12px 16px !important;
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

@media (max-width: 480px) {
    .checkout-wrapper {
        padding: 0 12px !important;
    }
    
    /* Réduire encore plus le padding du wrapper principal */
    .checkout-page .checkout-wrapper {
        padding: 8px 12px !important;
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
