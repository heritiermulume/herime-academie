@extends('layouts.app')

@section('title', 'Mon Panier - Herime Académie')

@section('content')
<div class="udemy-cart-container">
    <div class="cart-wrapper">
        <!-- Header Section -->
        <div class="cart-header">
            <div class="cart-title-section">
                <h1 class="cart-title">Votre panier</h1>
                <p class="cart-subtitle">Découvrez des cours qui vous intéressent</p>
                    </div>
            <div class="cart-actions">
                <a href="{{ route('courses.index') }}" class="continue-shopping-btn">
                    <i class="fas fa-arrow-left"></i>
                    Continuer mes achats
                </a>
            </div>
        </div>

        @if(count($cartItems) > 0)
        <!-- Main Cart Content -->
        <div class="cart-main-content" id="cart-main-container">
            <!-- Cart Items Section -->
            <div class="cart-items-section">
                <div class="cart-items-header">
                    <h2 class="cart-items-title">
                        <i class="fas fa-shopping-cart"></i>
                        {{ count($cartItems) }} cours dans votre panier
                    </h2>
                </div>
                <div class="cart-items-list" id="cart-items-container">
                        @foreach($cartItems as $item)
                    <div class="udemy-cart-item" id="cart-item-{{ $item['course']->id }}">
                        <div class="cart-item-content">
                            <!-- Course Image -->
                            <div class="cart-item-image-container">
                                <img src="{{ $item['course']->thumbnail ? $item['course']->thumbnail : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=300&h=200&fit=crop' }}" 
                                     alt="{{ $item['course']->title }}" 
                                     class="cart-item-image">
                                @if($item['course']->is_featured || $item['course']->is_free || $item['course']->is_sale_active)
                                <div class="course-badges">
                                    @if($item['course']->is_featured)
                                    <span class="course-badge featured">En vedette</span>
                                    @endif
                                    @if($item['course']->is_free)
                                    <span class="course-badge free">Gratuit</span>
                                    @endif
                                    @if($item['course']->sale_discount_percentage)
                                    <span class="course-badge sale">
                                        -{{ $item['course']->sale_discount_percentage }}%
                                    </span>
                                    @endif
                                </div>
                                @endif
                            </div>
                            
                            <!-- Course Details -->
                            <div class="cart-item-details">
                                <h3 class="cart-item-title">
                                    <a href="{{ route('courses.show', $item['course']->slug) }}">
                                        {{ $item['course']->title }}
                                    </a>
                                </h3>
                                
                                <div class="cart-item-meta">
                                    <div class="instructor-info">
                                        <span class="instructor-name">{{ $item['course']->instructor->name }}</span>
                                    </div>
                                    <div class="course-rating">
                                        <div class="rating-stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <span class="rating-text">{{ number_format($item['course']->stats['average_rating'] ?? 0, 1) }} ({{ $item['course']->stats['total_reviews'] ?? 0 }} avis)</span>
                                    </div>
                                    <div class="course-level">
                                        <span class="level-badge">{{ ucfirst($item['course']->level) }}</span>
                                    </div>
                                </div>
                                
                                <div class="cart-item-actions">
                                    <button type="button" 
                                            class="remove-btn" 
                                            onclick="removeItem({{ $item['course']->id }})"
                                            title="Supprimer du panier">
                                        <i class="fas fa-trash"></i>
                                        Supprimer
                                    </button>
                                    <a href="{{ route('courses.show', $item['course']->slug) }}" 
                                       class="view-btn">
                                        <i class="fas fa-eye"></i>
                                        Voir le cours
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Price -->
                            <div class="cart-item-price">
                                <div class="price-container">
                                    <span class="current-price">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($item['subtotal']) }}</span>
                                    @if($item['course']->is_sale_active && $item['course']->active_sale_price !== null)
                                    <span class="original-price">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($item['course']->price) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                        @endforeach
                </div>
                
                <!-- Clear Cart Button -->
                <div class="clear-cart-section">
                    <button type="button" class="clear-cart-btn" onclick="clearCart()">
                        <i class="fas fa-trash"></i>
                        Vider le panier
                    </button>
                </div>
            </div>
            
            <!-- Order Summary Section -->
            <div class="cart-summary-section">
                <div class="summary-card">
                    <div class="summary-header">
                        <h3 class="summary-title">
                            <i class="fas fa-receipt"></i>
                            Résumé de la commande
                        </h3>
                    </div>
                    <div class="summary-content" id="cart-summary">
                        <!-- Order Details -->
                        <div class="summary-details">
                            <div class="summary-row">
                                <span class="summary-label">Sous-total (<span id="cart-items-count">{{ count($cartItems) }}</span> cours)</span>
                                <span class="summary-value" id="cart-subtotal">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($subtotal) }}</span>
                            </div>
                            
                            <div class="summary-row">
                                <span class="summary-label">Taxes</span>
                                <span class="summary-value">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($tax) }}</span>
                            </div>
                            
                            <div class="summary-divider"></div>
                            
                            <div class="summary-row total-row">
                                <span class="summary-label">Total</span>
                                <span class="summary-value total-price" id="cart-total">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($total) }}</span>
                            </div>
                        </div>
                        
                        <!-- Checkout Button -->
                        @auth
                            <button type="button" class="checkout-btn" onclick="proceedToCheckout()">
                                <i class="fas fa-credit-card"></i>
                                Procéder au paiement
                            </button>
                        @else
                            @php
                                $finalLoginCart = url()->full();
                                $callbackLoginCart = route('sso.callback', ['redirect' => $finalLoginCart]);
                                $ssoLoginUrlCart = 'https://compte.herime.com/login?redirect=' . urlencode($callbackLoginCart);
                            @endphp
                            <a href="{{ $ssoLoginUrlCart }}" class="checkout-btn">
                                <i class="fas fa-sign-in-alt"></i>
                                Se connecter pour payer
                            </a>
                        @endauth
                        
                        <!-- Security Badge -->
                        <div class="security-badge">
                            <i class="fas fa-shield-alt"></i>
                            <span>Paiement sécurisé SSL</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Smart Course Recommendations -->
        @if($recommendedCourses->count() > 0)
        <div class="mt-5 recommendations-container">
            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="fw-bold text-dark mb-3">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>
                        Recommandations pour vous
                    </h3>
                    <p class="text-muted">Découvrez d'autres cours qui pourraient vous intéresser</p>
                </div>
            </div>
            
            <div class="row g-3">
                @foreach($recommendedCourses as $course)
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <x-course-card-standard :course="$course" />
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
        @else
        <!-- Empty Cart State -->
        <div id="empty-cart-container" class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-shopping-cart fa-4x text-muted"></i>
            </div>
            <h3 class="fw-bold mb-3 text-dark">Votre panier est vide</h3>
            <p class="text-muted mb-4 fs-5">Découvrez nos cours et ajoutez-les à votre panier pour commencer votre apprentissage.</p>
            <a href="{{ route('courses.index') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-search me-2"></i>Explorer les cours
            </a>
        </div>
        
        <!-- Popular Courses for Empty Cart -->
        <div id="empty-cart-recommendations" class="mt-5">
            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="fw-bold text-dark mb-3">
                        <i class="fas fa-fire me-2 text-danger"></i>
                        Cours populaires
                    </h3>
                    <p class="text-muted">Commencez votre parcours d'apprentissage avec nos cours les plus appréciés</p>
                </div>
            </div>
            
            <div class="row g-3">
                @foreach($popularCourses as $course)
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <x-course-card-standard :course="$course" />
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<style>
/* Udemy-inspired Cart Styles with Site Colors */
.udemy-cart-container {
    background-color: #f7f9fa;
    padding: 0;
}

.cart-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px;
}

/* Header Styles */
.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid #e5e5e5;
}

.cart-title-section {
    flex: 1;
}

.cart-title {
    font-size: 32px;
    font-weight: 700;
    color: #1c1d1f;
    margin: 0 0 8px 0;
    line-height: 1.2;
}

.cart-subtitle {
    font-size: 16px;
    color: #6a6f73;
    margin: 0;
}

.cart-actions {
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

/* Main Content Layout */
.cart-main-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 32px;
    align-items: start;
}

/* Cart Items Section */
.cart-items-section {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.cart-items-header {
    padding: 24px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
    border-radius: 12px 12px 0 0;
    box-shadow: 0 2px 8px rgba(0, 51, 102, 0.15);
}

.cart-items-title {
    font-size: 20px;
    font-weight: 700;
    color: white;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.cart-items-title i {
    color: var(--accent-color);
    font-size: 18px;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
}

.cart-items-list {
    padding: 0;
}

/* Individual Cart Item */
.udemy-cart-item {
    border-bottom: 1px solid #e5e5e5;
    transition: all 0.2s ease;
}

.udemy-cart-item:last-child {
    border-bottom: none;
}

.udemy-cart-item:hover {
    background-color: #f7f9fa;
}

.cart-item-content {
    display: grid;
    grid-template-columns: 240px 1fr auto;
    gap: 24px;
    padding: 24px;
    align-items: start;
}

/* Course Image */
.cart-item-image-container {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.cart-item-image {
    width: 100%;
    height: 135px;
    object-fit: cover;
    display: block;
}

.course-badges {
    position: absolute;
    top: 8px;
    left: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.course-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.course-badge.featured {
    background-color: #ffcc33;
    color: #1c1d1f;
}

.course-badge.free {
    background-color: #0f5132;
    color: white;
}

.course-badge.sale {
    background-color: #dc3545;
    color: white;
}

/* Course Details */
.cart-item-details {
    flex: 1;
    min-width: 0;
}

.cart-item-title {
    margin: 0 0 12px 0;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.3;
}

.cart-item-title a {
    color: #1c1d1f;
    text-decoration: none;
    transition: color 0.2s ease;
}

.cart-item-title a:hover {
    color: #003366;
}

.cart-item-meta {
    margin-bottom: 16px;
}

.instructor-info {
    margin-bottom: 8px;
}

.instructor-name {
    font-size: 14px;
    color: #6a6f73;
    font-weight: 400;
}

.course-rating {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.rating-stars {
    display: flex;
    gap: 2px;
}

.rating-stars i {
    color: #ffcc33;
    font-size: 12px;
}

.rating-text {
    font-size: 14px;
    color: #6a6f73;
}

.course-level {
    margin-bottom: 8px;
}

.level-badge {
    display: inline-block;
    padding: 4px 8px;
    background-color: #e3f2fd;
    color: #1976d2;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.cart-item-actions {
    display: flex;
    gap: 12px;
    margin-top: 16px;
}

.remove-btn, .view-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.remove-btn {
    background-color: transparent;
    color: #dc3545;
    border: 1px solid #dc3545;
}

.remove-btn:hover {
    background-color: #dc3545;
    color: white;
}

.view-btn {
    background-color: transparent;
    color: #003366;
    border: 1px solid #003366;
}

.view-btn:hover {
    background-color: #003366;
    color: white;
    text-decoration: none;
}

/* Price Section */
.cart-item-price {
    text-align: right;
    min-width: 120px;
}

.price-container {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.current-price {
    font-size: 20px;
    font-weight: 700;
    color: #1c1d1f;
}

.original-price {
    font-size: 14px;
    color: #6a6f73;
    text-decoration: line-through;
}

/* Clear Cart Button */
.clear-cart-section {
    padding: 24px;
    text-align: right;
    background: white;
    border-top: 1px solid #e5e5e5;
}

.clear-cart-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background-color: transparent;
    color: #dc3545;
    border: 1px solid #dc3545;
    border-radius: 4px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.clear-cart-btn:hover {
    background-color: #dc3545;
    color: white;
}

/* Summary Section */
.cart-summary-section {
    position: sticky;
    top: 24px;
}

.summary-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.summary-header {
    background: linear-gradient(135deg, #003366 0%, #001a33 100%);
    padding: 20px;
}

.summary-title {
    color: white;
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.summary-content {
    padding: 24px;
}

.summary-details {
    margin-bottom: 24px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.summary-label {
    font-size: 14px;
    color: #6a6f73;
}

.summary-value {
    font-size: 14px;
    font-weight: 600;
    color: #1c1d1f;
}

.total-row {
    padding-top: 12px;
    border-top: 1px solid #e5e5e5;
    margin-top: 12px;
}

.total-row .summary-label {
    font-size: 16px;
    font-weight: 700;
    color: #1c1d1f;
}

.total-price {
    font-size: 20px;
    font-weight: 700;
    color: #003366;
}

.summary-divider {
    height: 1px;
    background-color: #e5e5e5;
    margin: 16px 0;
}

.checkout-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 16px;
    background: linear-gradient(135deg, #003366 0%, #001a33 100%);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 16px;
}

.checkout-btn:hover {
    background: linear-gradient(135deg, #001a33 0%, #003366 100%);
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
}

.security-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 12px;
    color: #6a6f73;
}

.security-badge i {
    color: #28a745;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .cart-main-content {
        grid-template-columns: 1fr 350px;
        gap: 24px;
    }
    
    .cart-item-content {
        grid-template-columns: 200px 1fr auto;
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .cart-wrapper {
        padding: 16px;
    }
    
    .cart-header {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
    }
    
    .cart-title {
        font-size: 24px;
    }
    
    .cart-main-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .cart-summary-section {
        position: static;
        order: -1;
    }
    
    .cart-items-header {
        padding: 16px;
    }
    
    .cart-items-title {
        font-size: 18px;
    }
    
    .cart-items-title i {
        font-size: 16px;
    }
    
    .cart-item-content {
        grid-template-columns: 100px 1fr auto;
        gap: 12px;
        padding: 12px;
    }
    
    .cart-item-image {
        height: 80px;
    }
    
    .cart-item-title {
        font-size: 14px;
        margin-bottom: 4px;
        line-height: 1.3;
    }
    
    .cart-item-meta {
        margin-bottom: 8px;
    }
    
    .instructor-name {
        font-size: 12px;
    }
    
    .rating-text {
        font-size: 11px;
    }
    
    .cart-item-actions {
        flex-direction: column;
        gap: 6px;
        margin-top: 8px;
    }
    
    .remove-btn, .view-btn {
        font-size: 12px;
        padding: 4px 8px;
    }
    
    .current-price {
        font-size: 16px;
    }
    
    .original-price {
        font-size: 12px;
    }
    
    .level-badge {
        font-size: 10px;
        padding: 2px 6px;
    }
    
    .rating-stars i {
        font-size: 10px;
    }
}

@media (max-width: 480px) {
    .cart-wrapper {
        padding: 12px;
    }
    
    .cart-title {
        font-size: 20px;
    }
    
    .cart-subtitle {
        font-size: 14px;
    }
    
    .continue-shopping-btn {
        font-size: 13px;
        padding: 10px 14px;
    }
    
    .cart-items-header {
        padding: 12px;
    }
    
    .cart-items-title {
        font-size: 16px;
    }
    
    .cart-items-title i {
        font-size: 14px;
    }
    
    /* Ajustements pour très petits écrans - Version ultra compacte */
    .cart-item-content {
        padding: 8px;
        gap: 8px;
        grid-template-columns: 70px 1fr auto;
    }
    
    .cart-item-image-container {
        width: 70px;
        height: 70px;
    }
    
    .cart-item-image {
        height: 70px;
    }
    
    .cart-item-title {
        font-size: 12px;
        margin-bottom: 2px;
        line-height: 1.2;
    }
    
    .cart-item-title a {
        -webkit-line-clamp: 1;
    }
    
    .cart-item-meta {
        margin-bottom: 4px;
    }
    
    .instructor-name {
        font-size: 10px;
    }
    
    .rating-text {
        font-size: 9px;
    }
    
    .rating-stars i {
        font-size: 8px;
    }
    
    .level-badge {
        font-size: 8px;
        padding: 1px 4px;
    }
    
    .current-price {
        font-size: 14px;
    }
    
    .original-price {
        font-size: 10px;
    }
    
    .cart-item-actions {
        gap: 4px;
        margin-top: 4px;
    }
    
    .remove-btn,
    .view-btn {
        padding: 4px 6px;
        font-size: 10px;
    }
    
    .btn-text {
        display: none;
    }
    
    .remove-btn i,
    .view-btn i {
        margin-right: 0;
    }
    
    .summary-content {
        padding: 16px;
    }
    
    .checkout-btn {
        font-size: 14px;
        padding: 14px;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.udemy-cart-item {
    animation: fadeInUp 0.5s ease-out;
}

/* Hover effects */
.udemy-cart-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.cart-item-image:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}

/* Focus states */
.remove-btn:focus, .view-btn:focus, .checkout-btn:focus {
    outline: 2px solid #003366;
    outline-offset: 2px;
}

/* Loading states */
.btn.loading, .remove-btn.loading, .view-btn.loading {
    opacity: 0.7;
    pointer-events: none;
    position: relative;
}

.btn.loading::after, .remove-btn.loading::after, .view-btn.loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Disabled button states */
.remove-btn:disabled, .view-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Notification styles */
.alert {
    border-radius: 8px;
    border: none;
    font-weight: 500;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border-left: 4px solid #17a2b8;
}

/* Styles harmonisés - utilisent les styles globaux de app.blade.php */
</style>

<script>
// Fonction pour supprimer un article du panier
function removeItem(courseId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce cours de votre panier ?')) {
        fetch('{{ route("cart.remove") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                course_id: courseId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Supprimer l'élément spécifique du DOM
                const itemElement = document.getElementById(`cart-item-${courseId}`);
                if (itemElement) {
                    itemElement.remove();
                }
                
                // Mettre à jour le compteur
                updateCartCount();
                
                // Vérifier si le panier est maintenant vide
                const remainingItems = document.querySelectorAll('.cart-item-modern');
                if (remainingItems.length === 0) {
                    // Le panier est vide, recharger la page pour afficher l'état vide
                    showNotification('Cours supprimé du panier', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Le panier n'est pas vide, mettre à jour le résumé via AJAX
                    updateCartSummary();
                showNotification('Cours supprimé du panier', 'success');
                }
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Une erreur est survenue', 'error');
        });
    }
}

// Fonction pour vider le panier
function clearCart() {
    if (confirm('Êtes-vous sûr de vouloir vider votre panier ?')) {
        fetch('{{ route("cart.clear") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour le compteur
                updateCartCount();
                
                // Recharger la page pour afficher l'état du panier vide
                showNotification('Panier vidé', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Une erreur est survenue', 'error');
        });
    }
}

// Fonction pour procéder au checkout
function proceedToCheckout() {
    window.location.href = '{{ route("cart.checkout") }}';
}

// Fonction pour mettre à jour les recommandations
function updateRecommendations() {
    // Cette fonction peut être étendue pour mettre à jour les recommandations dynamiquement
    console.log('Updating recommendations...');
}

// Fonction utilitaire pour masquer l'état du panier vide (utilisée lors de l'ajout d'articles)
function hideEmptyCartState() {
    const emptyCartContainer = document.getElementById('empty-cart-container');
    const emptyCartRecommendations = document.getElementById('empty-cart-recommendations');
    const mainContainer = document.getElementById('cart-main-container');
    const recommendationsContainer = document.querySelector('.recommendations-container');
    
    // Masquer les sections de panier vide
    if (emptyCartContainer) {
        emptyCartContainer.style.display = 'none';
    }
    
    if (emptyCartRecommendations) {
        emptyCartRecommendations.style.display = 'none';
    }
    
    // Afficher le contenu principal
    if (mainContainer) {
        mainContainer.style.display = 'block';
    }
    
    if (recommendationsContainer) {
        recommendationsContainer.style.display = 'block';
    }
}

// Fonction pour mettre à jour seulement le résumé de la commande
function updateCartSummary() {
    fetch('{{ route("cart.summary") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour les éléments du résumé
            const subtotalElement = document.getElementById('cart-subtotal');
            const totalElement = document.getElementById('cart-total');
            const itemCountElement = document.getElementById('cart-items-count');
            
            if (subtotalElement) {
                subtotalElement.textContent = data.formatted_subtotal;
            }
            
            if (totalElement) {
                totalElement.textContent = data.formatted_total;
            }
            
            if (itemCountElement) {
                itemCountElement.textContent = data.item_count;
                // Mettre à jour aussi le texte parent
                const parentLabel = itemCountElement.parentElement;
                if (parentLabel) {
                    parentLabel.innerHTML = `<span class="text-muted">Sous-total (<span id="cart-items-count">${data.item_count}</span> article${data.item_count > 1 ? 's' : '' })</span>`;
                }
            }
        }
    })
    .catch(error => {
        console.error('Error updating cart summary:', error);
    });
}

// Fonction pour ajouter un article au panier (version spécifique à la page panier)
function addToCartFromCartPage(courseId) {
    console.log('Tentative d\'ajout du cours:', courseId);
    
    // Vérifier si une requête est déjà en cours
    if (window.addingToCart) {
        console.log('Une requête est déjà en cours, ignorer');
        return;
    }
    
    // Marquer qu'une requête est en cours
    window.addingToCart = true;
    
    // Désactiver le bouton
    const button = event.target.closest('button');
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Ajout...';
    }
    
    // Masquer l'état du panier vide si visible
    hideEmptyCartState();
    
    // Faire la requête AJAX
    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            course_id: courseId
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Réponse du serveur:', data);
        
        if (data.success) {
            // Succès - recharger immédiatement la page
            showNotification('Cours ajouté au panier', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Erreur - afficher le message d'erreur
            showNotification(data.message || 'Erreur lors de l\'ajout', 'error');
            
            // Réactiver le bouton après l'erreur
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-shopping-cart me-1"></i>Ajouter au panier';
            }
        }
    })
    .catch(error => {
        console.error('Erreur AJAX:', error);
        showNotification('Erreur de connexion', 'error');
        
        // Réactiver le bouton en cas d'erreur
        if (button) {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-shopping-cart me-1"></i>Ajouter au panier';
        }
    })
    .finally(() => {
        // Nettoyer le flag de requête en cours
        window.addingToCart = false;
    });
}

// Fonction utilitaire pour afficher les notifications
function showNotification(message, type = 'info') {
    // Vérifier si la fonction globale existe
    if (typeof window.showNotification === 'function') {
        window.showNotification(message, type);
        return;
    }
    
    // Fallback simple
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Fonction pour mettre à jour le compteur du panier
function updateCartCount() {
    // Vérifier si la fonction globale existe
    if (typeof window.updateCartCount === 'function') {
        window.updateCartCount();
        return;
    }
    
    // Mettre à jour le compteur dans l'interface
    const cartCountElements = document.querySelectorAll('.cart-count, .cart-count-badge');
    cartCountElements.forEach(element => {
        const currentCount = parseInt(element.textContent) || 0;
        element.textContent = currentCount + 1;
    });
    
    console.log('Cart count updated');
}

// Fonction supprimée - plus nécessaire avec la nouvelle approche

// Initialiser la page au chargement
document.addEventListener('DOMContentLoaded', function() {
    // Mettre à jour le compteur du panier au chargement
    updateCartCount();
    
    // Ajouter des gestionnaires d'événements pour les boutons d'ajout au panier
    document.addEventListener('click', function(e) {
        if (e.target.closest('button[onclick*="addToCart"]')) {
            e.preventDefault();
            const button = e.target.closest('button');
            const onclick = button.getAttribute('onclick');
            const courseIdMatch = onclick.match(/addToCart\((\d+)\)/);
            
            if (courseIdMatch) {
                const courseId = parseInt(courseIdMatch[1]);
                addToCartFromCartPage(courseId);
            }
        }
    });
});
</script>
@endsection