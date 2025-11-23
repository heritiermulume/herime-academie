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
                <div class="cart-items-list recommended-courses" id="cart-items-container">
                        @foreach($cartItems as $item)
                    <div class="recommended-item cart-item-wrapper" id="cart-item-{{ $item['course']->id }}">
                        <!-- Course Image -->
                        <div class="recommended-thumb">
                            <img src="{{ $item['course']->thumbnail ? $item['course']->thumbnail : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=300&h=200&fit=crop' }}" 
                                 alt="{{ $item['course']->title }}">
                        </div>
                        
                        <!-- Course Details -->
                        <div class="recommended-content flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-0">
                                <h6 class="flex-grow-1 mb-0">
                                    <a href="{{ route('courses.show', $item['course']->slug) }}" class="text-decoration-none">
                                        {{ $item['course']->title }}
                                    </a>
                                </h6>
                                <!-- Price on mobile - top right -->
                                <div class="cart-item-price-mobile">
                                    <div class="price-container">
                                        <span class="current-price">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($item['subtotal']) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Formateur, niveau et prix barré sur la même ligne -->
                            <div class="recommended-meta-with-price d-flex justify-content-between align-items-center mb-0">
                                <div class="recommended-meta">
                                    <span><i class="fas fa-user me-1"></i>{{ $item['course']->instructor->name }}</span>
                                    <span><i class="fas fa-signal me-1"></i>{{ ucfirst($item['course']->level) }}</span>
                                </div>
                                @if($item['course']->is_sale_active && $item['course']->active_sale_price !== null)
                                <div class="original-price-inline">
                                    <span class="original-price">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($item['course']->price) }}</span>
                                </div>
                                @endif
                            </div>
                            
                            <div class="recommended-actions">
                                <span class="badge bg-primary bg-opacity-10 text-info border-0">
                                    {{ $item['course']->stats['total_lessons'] ?? 0 }} leçons
                                </span>
                                <span class="badge bg-primary bg-opacity-10 text-warning border-0">
                                    {{ number_format($item['course']->stats['average_rating'] ?? 0, 1) }}/5
                                </span>
                            </div>
                            
                            <!-- Cart Actions -->
                            <div class="cart-item-actions mt-1">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger remove-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#removeItemModal"
                                        data-course-id="{{ $item['course']->id }}"
                                        data-course-title="{{ $item['course']->title }}"
                                        title="Supprimer du panier">
                                    <i class="fas fa-trash"></i>
                                    <span class="btn-text">Supprimer</span>
                                </button>
                                <a href="{{ route('courses.show', $item['course']->slug) }}" 
                                   class="btn btn-sm btn-outline-primary view-btn"
                                   title="Voir le cours">
                                    <i class="fas fa-eye"></i>
                                    <span class="btn-text">Voir le cours</span>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Price on desktop - right side -->
                        <div class="cart-item-price">
                            <div class="price-container">
                                <span class="current-price">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($item['subtotal']) }}</span>
                                @if($item['course']->is_sale_active && $item['course']->active_sale_price !== null)
                                <span class="original-price">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($item['course']->price) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                        @endforeach
                </div>
                
                <!-- Clear Cart Button -->
                <div class="clear-cart-section">
                    <button type="button" class="clear-cart-btn" data-bs-toggle="modal" data-bs-target="#clearCartModal">
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
                                $ssoLoginUrlCart = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callbackLoginCart);
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

<!-- Modal pour supprimer un cours -->
<div class="modal fade" id="removeItemModal" tabindex="-1" aria-labelledby="removeItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal">
            <div class="modal-header modern-modal-header remove-item-header">
                <div class="modal-icon-wrapper">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="modal-title" id="removeItemModalLabel">Supprimer le cours</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body modern-modal-body">
                <p>Êtes-vous sûr de vouloir supprimer <strong id="removeItemCourseTitle"></strong> de votre panier ?</p>
            </div>
            <div class="modal-footer modern-modal-footer">
                <button type="button" class="btn btn-secondary cancel-remove-btn" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-danger" id="confirmRemoveItemBtn">
                    <i class="fas fa-trash me-2"></i>Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour vider le panier -->
<div class="modal fade" id="clearCartModal" tabindex="-1" aria-labelledby="clearCartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal">
            <div class="modal-header modern-modal-header">
                <div class="modal-icon-wrapper">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="modal-title" id="clearCartModalLabel">Vider le panier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body modern-modal-body">
                <p>Êtes-vous sûr de vouloir vider votre panier ? Cette action est irréversible et tous les cours seront supprimés.</p>
            </div>
            <div class="modal-footer modern-modal-footer">
                <button type="button" class="btn btn-secondary cancel-clear-btn" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-danger" id="confirmClearCartBtn" onclick="confirmClearCart(event)">
                    <i class="fas fa-trash me-2"></i>Vider le panier
                </button>
            </div>
        </div>
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

/* Cart Items List - Using recommended-courses design */
.cart-items-list.recommended-courses {
    display: grid;
    gap: 0.75rem;
    padding: 24px;
}

.cart-items-list .recommended-item.cart-item-wrapper {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 16px;
    border: 1px solid rgba(0, 51, 102, 0.15);
    background: linear-gradient(135deg, rgba(0, 51, 102, 0.05) 0%, rgba(0, 51, 102, 0.1) 100%);
    transition: border 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    align-items: flex-start;
}

.cart-items-list .recommended-item.cart-item-wrapper:hover {
    border-color: rgba(0, 51, 102, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 51, 102, 0.1);
}

/* Course Image */
.cart-items-list .recommended-thumb {
    width: 72px;
    height: 72px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
}

.cart-items-list .recommended-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Course Details */
.cart-items-list .recommended-content {
    flex: 1;
    min-width: 0;
}

.cart-items-list .recommended-content h6 {
    margin-bottom: 0.1rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: #1c1d1f;
    flex: 1;
    min-width: 0;
    line-height: 1.3;
}

.cart-items-list .recommended-content h6 a {
    color: #1c1d1f;
    text-decoration: none;
    transition: color 0.2s ease;
}

.cart-items-list .recommended-content h6 a:hover {
    color: #003366;
}

.cart-items-list .cart-item-price-mobile .price-container {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}

.cart-items-list .cart-item-price-mobile .current-price {
    font-size: 0.9rem;
    font-weight: 700;
    color: #003366;
    white-space: nowrap;
}

.cart-items-list .cart-item-price-mobile .original-price {
    font-size: 0.65rem;
    color: #6a6f73;
    text-decoration: line-through;
    white-space: nowrap;
}

.cart-items-list .recommended-meta {
    font-size: 0.75rem;
    color: #6a6f73;
    display: flex;
    gap: 0.65rem;
    margin-bottom: 0;
}

.cart-items-list .recommended-meta-with-price {
    margin-bottom: 0.3rem;
    gap: 0.5rem;
}

.cart-items-list .original-price-inline {
    flex-shrink: 0;
}

.cart-items-list .original-price-inline .original-price {
    font-size: 0.75rem;
    color: #6a6f73;
    text-decoration: line-through;
    white-space: nowrap;
}

.cart-items-list .recommended-actions {
    margin-top: 0.4rem;
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.cart-items-list .recommended-actions .badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
    border-radius: 10px;
}

.cart-items-list .cart-item-actions {
    display: flex;
    flex-direction: row;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 0.4rem;
    flex-wrap: wrap;
}

.cart-items-list .cart-item-actions .remove-btn,
.cart-items-list .cart-item-actions .view-btn {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

/* Price Section */
.cart-items-list .cart-item-price {
    text-align: right;
    min-width: 100px;
    flex-shrink: 0;
    display: flex;
    align-items: flex-start;
}

.cart-items-list .cart-item-price-mobile {
    display: none; /* Hidden on desktop by default */
}

.cart-items-list .price-container {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.cart-items-list .current-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: #003366;
}

.cart-items-list .original-price {
    font-size: 0.75rem;
    color: #6a6f73;
    text-decoration: line-through;
}

/* Button text - visible on desktop */
.cart-items-list .btn-text {
    display: inline;
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
    
    .cart-items-list.recommended-courses {
        padding: 16px;
    }
    
    .cart-items-list .recommended-item.cart-item-wrapper {
        flex-wrap: wrap;
    }
    
    .cart-items-list .cart-item-price {
        width: 100%;
        margin-top: 0.5rem;
        justify-content: flex-end;
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
    
    .cart-items-list.recommended-courses {
        padding: 16px;
        gap: 0.5rem;
    }
    
    .cart-items-list .recommended-item.cart-item-wrapper {
        padding: 0.5rem;
        gap: 0.5rem;
    }
    
    .cart-items-list .recommended-thumb {
        width: 60px;
        height: 60px;
    }
    
    .cart-items-list .recommended-content h6 {
        font-size: 0.8rem;
    }
    
    .cart-items-list .recommended-meta {
        font-size: 0.7rem;
        gap: 0.5rem;
    }
    
    .cart-items-list .recommended-meta-with-price {
        gap: 0.3rem;
        margin-bottom: 0.25rem;
    }
    
    .cart-items-list .original-price-inline .original-price {
        font-size: 0.7rem;
    }
    
    .cart-items-list .recommended-actions .badge {
        font-size: 0.7rem;
        padding: 0.3rem 0.5rem;
    }
    
    .cart-items-list .cart-item-actions {
        flex-direction: row;
        justify-content: flex-end;
        gap: 0.4rem;
    }
    
    /* Hide button text and make buttons icon-only on tablet */
    .cart-items-list .cart-item-actions .btn-text {
        display: none !important;
    }
    
    .cart-items-list .cart-item-actions .remove-btn,
    .cart-items-list .cart-item-actions .view-btn {
        font-size: 0.875rem;
        padding: 0;
        width: 36px;
        height: 36px;
        min-width: 36px;
        min-height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .cart-items-list .cart-item-actions .remove-btn i,
    .cart-items-list .cart-item-actions .view-btn i {
        margin: 0 !important;
        font-size: 0.875rem;
    }
    
    /* Hide desktop price on tablet */
    .cart-items-list .cart-item-price {
        display: none;
    }
    
    /* Show mobile price on tablet */
    .cart-items-list .cart-item-price-mobile {
        display: block;
        margin-left: 0.5rem;
        flex-shrink: 0;
    }
    
    .cart-items-list .cart-item-price-mobile .price-container {
        align-items: flex-end;
    }
    
    .cart-items-list .current-price {
        font-size: 1rem;
    }
    
    .cart-items-list .original-price {
        font-size: 0.7rem;
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
    
    .cart-items-list.recommended-courses {
        padding: 12px;
        gap: 0.5rem;
    }
    
    .cart-items-list .recommended-item.cart-item-wrapper {
        padding: 0.5rem;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .cart-items-list .recommended-thumb {
        width: 56px;
        height: 56px;
    }
    
    .cart-items-list .recommended-content h6 {
        font-size: 0.75rem;
        margin-bottom: 0.1rem;
    }
    
    .cart-items-list .recommended-meta {
        font-size: 0.65rem;
        gap: 0.4rem;
        flex-wrap: wrap;
    }
    
    .cart-items-list .recommended-meta-with-price {
        gap: 0.3rem;
        flex-wrap: wrap;
        margin-bottom: 0.25rem;
    }
    
    .cart-items-list .original-price-inline .original-price {
        font-size: 0.65rem;
    }
    
    .cart-items-list .recommended-actions {
        margin-top: 0.3rem;
        gap: 0.3rem;
    }
    
    .cart-items-list .recommended-actions .badge {
        font-size: 0.65rem;
        padding: 0.25rem 0.4rem;
    }
    
    .cart-items-list .cart-item-actions {
        flex-direction: row;
        justify-content: flex-end;
        gap: 0.3rem;
        margin-top: 0.3rem;
    }
    
    /* Hide button text and make buttons icon-only on mobile */
    .cart-items-list .cart-item-actions .btn-text {
        display: none !important;
    }
    
    .cart-items-list .cart-item-actions .remove-btn,
    .cart-items-list .cart-item-actions .view-btn {
        font-size: 0.8rem;
        padding: 0;
        width: 32px;
        height: 32px;
        min-width: 32px;
        min-height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .cart-items-list .cart-item-actions .remove-btn i,
    .cart-items-list .cart-item-actions .view-btn i {
        margin: 0 !important;
        font-size: 0.8rem;
    }
    
    /* Hide desktop price on mobile */
    .cart-items-list .cart-item-price {
        display: none;
    }
    
    /* Show mobile price on mobile - top right */
    .cart-items-list .cart-item-price-mobile {
        display: block;
        margin-left: 0.5rem;
        flex-shrink: 0;
    }
    
    .cart-items-list .cart-item-price-mobile .price-container {
        align-items: flex-end;
    }
    
    .cart-items-list .current-price {
        font-size: 0.85rem;
    }
    
    .cart-items-list .original-price {
        font-size: 0.6rem;
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

.cart-items-list .recommended-item.cart-item-wrapper {
    animation: fadeInUp 0.5s ease-out;
}

/* Hover effects */
.cart-items-list .recommended-thumb img {
    transition: transform 0.3s ease;
}

.cart-items-list .recommended-item.cart-item-wrapper:hover .recommended-thumb img {
    transform: scale(1.05);
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

/* Modern Modal Styles */
.modern-modal {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
}

.modern-modal-header {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border-bottom: none;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.modern-modal-header .modal-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.modern-modal-header .modal-icon-wrapper i {
    font-size: 1.5rem;
    color: white;
}

.modern-modal-header .modal-title {
    color: white;
    font-weight: 700;
    font-size: 1.25rem;
    margin: 0;
    flex: 1;
}

.modern-modal-header .btn-close {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    opacity: 1;
    padding: 0.5rem;
    width: 32px;
    height: 32px;
    transition: all 0.2s ease;
}

.modern-modal-header .btn-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.modern-modal-header.remove-item-header {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

.modern-modal-body {
    padding: 1.5rem;
    font-size: 1rem;
    color: #1c1d1f;
    line-height: 1.6;
}

.modern-modal-footer {
    border-top: 1px solid #e5e5e5;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.modern-modal-footer .btn {
    padding: 0.625rem 1.25rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}

.modern-modal-footer .btn-secondary {
    background-color: #f7f9fa;
    border: 1px solid #e5e5e5;
    color: #1c1d1f;
}

.modern-modal-footer .btn-secondary:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.modern-modal-footer .btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border: none;
    color: white;
}

.modern-modal-footer .btn-danger:hover {
    background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

/* Styles harmonisés - utilisent les styles globaux de app.blade.php */
</style>

<script>
// Fonction pour confirmer la suppression d'un cours (appelée depuis le modal)
function confirmRemoveItem() {
    const confirmBtn = document.getElementById('confirmRemoveItemBtn');
    if (!confirmBtn) {
        console.error('Confirm button not found');
        showNotification('Erreur: Bouton de confirmation introuvable', 'error');
        return;
    }
    
    // Récupérer l'ID du cours depuis le dataset ou l'attribut
    let courseId = confirmBtn.dataset.courseId || confirmBtn.getAttribute('data-course-id');
    if (!courseId) {
        console.error('Course ID not found in button dataset');
        showNotification('Erreur: ID du cours introuvable', 'error');
        return;
    }
    
    // Retirer le focus du bouton avant de fermer le modal
    confirmBtn.blur();
    
    // Fermer le modal
    const modalElement = document.getElementById('removeItemModal');
    if (modalElement && typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            // Écouter l'événement de fermeture complète du modal
            modalElement.addEventListener('hidden.bs.modal', function handler() {
                // Supprimer le cours après que le modal soit complètement fermé
                removeItem(courseId);
                // Retirer l'écouteur après utilisation
                modalElement.removeEventListener('hidden.bs.modal', handler);
            }, { once: true });
            modal.hide();
        } else {
            const bsModal = new bootstrap.Modal(modalElement);
            modalElement.addEventListener('hidden.bs.modal', function handler() {
                removeItem(courseId);
                modalElement.removeEventListener('hidden.bs.modal', handler);
            }, { once: true });
            bsModal.hide();
        }
    } else {
        // Si Bootstrap n'est pas disponible, supprimer directement
        removeItem(courseId);
    }
}

// Fonction pour supprimer un article du panier
function removeItem(courseId) {
    // S'assurer que courseId est un nombre
    courseId = parseInt(courseId);
    if (isNaN(courseId)) {
        console.error('Invalid course ID:', courseId);
        showNotification('Erreur: ID du cours invalide', 'error');
        return;
    }
    
    fetch('{{ route("cart.remove") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            course_id: courseId
        })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
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
                const remainingItems = document.querySelectorAll('.recommended-item.cart-item-wrapper');
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
                showNotification(data.message || 'Erreur lors de la suppression', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Une erreur est survenue lors de la suppression', 'error');
        });
}

// Fonction pour confirmer le vidage du panier (appelée depuis le modal)
function confirmClearCart(event) {
    // Retirer le focus du bouton avant de fermer le modal
    const confirmBtn = event?.target?.closest('button') || document.getElementById('confirmClearCartBtn') || document.querySelector('#clearCartModal .btn-danger');
    if (confirmBtn && typeof confirmBtn.blur === 'function') {
        confirmBtn.blur();
    }
    
    // Fermer le modal
    const modalElement = document.getElementById('clearCartModal');
    if (modalElement && typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            // Écouter l'événement de fermeture complète du modal
            modalElement.addEventListener('hidden.bs.modal', function handler() {
                // Vider le panier après que le modal soit complètement fermé
                clearCart();
                // Retirer l'écouteur après utilisation
                modalElement.removeEventListener('hidden.bs.modal', handler);
            }, { once: true });
            modal.hide();
        } else {
            const bsModal = new bootstrap.Modal(modalElement);
            modalElement.addEventListener('hidden.bs.modal', function handler() {
                clearCart();
                modalElement.removeEventListener('hidden.bs.modal', handler);
            }, { once: true });
            bsModal.hide();
        }
    } else {
        // Si Bootstrap n'est pas disponible, vider directement
        clearCart();
    }
}

// Fonction pour vider le panier
function clearCart() {
    fetch('{{ route("cart.clear") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau');
        }
        return response.json();
    })
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
            showNotification(data.message || 'Erreur lors du vidage du panier', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Une erreur est survenue lors du vidage du panier', 'error');
    });
}

// Fonction pour procéder au checkout
function proceedToCheckout() {
    window.location.href = '{{ route("cart.checkout") }}';
}

// Fonction pour mettre à jour les recommandations
function updateRecommendations() {
    // Cette fonction peut être étendue pour mettre à jour les recommandations dynamiquement
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
    // Vérifier si une requête est déjà en cours
    if (window.addingToCart) {
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
        if (data.success) {
            // Mettre à jour le compteur du panier
            updateCartCount();
            
            // Succès - recharger immédiatement la page pour afficher le nouveau cours
            showNotification('Cours ajouté au panier', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 500);
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
}

// Fonction supprimée - plus nécessaire avec la nouvelle approche

// Initialiser la page au chargement
document.addEventListener('DOMContentLoaded', function() {
    // Mettre à jour le compteur du panier au chargement
    updateCartCount();
    
    // Initialiser le modal de suppression d'un cours
    const removeItemModal = document.getElementById('removeItemModal');
    if (removeItemModal) {
        removeItemModal.addEventListener('show.bs.modal', function(event) {
            // Bouton qui a déclenché le modal
            const button = event.relatedTarget;
            if (!button) {
                console.error('Button not found in event');
                return;
            }
            
            // Extraire les informations depuis les attributs data-*
            const courseId = button.getAttribute('data-course-id');
            const courseTitle = button.getAttribute('data-course-title');
            
            // Mettre à jour le contenu du modal
            const modalTitle = removeItemModal.querySelector('#removeItemCourseTitle');
            if (modalTitle) {
                modalTitle.textContent = courseTitle || 'ce cours';
            }
            
            // Stocker l'ID du cours dans le bouton de confirmation
            const confirmBtn = document.getElementById('confirmRemoveItemBtn');
            if (confirmBtn) {
                confirmBtn.setAttribute('data-course-id', courseId);
                confirmBtn.dataset.courseId = courseId;
            } else {
                console.error('Confirm button not found');
            }
        });
        
        // Gestionnaire pour le bouton Annuler - retirer le focus avant la fermeture
        const cancelRemoveBtn = removeItemModal.querySelector('.cancel-remove-btn');
        if (cancelRemoveBtn) {
            cancelRemoveBtn.addEventListener('click', function(e) {
                // Retirer le focus immédiatement
                this.blur();
            });
        }
        
        // Écouter l'événement hide.bs.modal pour retirer le focus de tous les éléments focusables
        removeItemModal.addEventListener('hide.bs.modal', function() {
            const activeElement = document.activeElement;
            if (activeElement && removeItemModal.contains(activeElement)) {
                activeElement.blur();
            }
        });
    }
    
    // Gestionnaire d'événement pour le bouton de confirmation de suppression
    const confirmRemoveBtn = document.getElementById('confirmRemoveItemBtn');
    if (confirmRemoveBtn) {
        confirmRemoveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            confirmRemoveItem();
        });
    } else {
        console.error('Confirm remove button not found in DOM');
    }
    
    // Initialiser le modal Bootstrap si disponible
    const clearCartModal = document.getElementById('clearCartModal');
    if (clearCartModal && typeof bootstrap !== 'undefined') {
        // Le modal sera initialisé automatiquement par Bootstrap via data-bs-toggle
        
        // Gestionnaire pour le bouton Annuler - retirer le focus avant la fermeture
        const cancelClearBtn = clearCartModal.querySelector('.cancel-clear-btn');
        if (cancelClearBtn) {
            cancelClearBtn.addEventListener('click', function(e) {
                // Retirer le focus immédiatement
                this.blur();
            });
        }
        
        // Écouter l'événement hide.bs.modal pour retirer le focus de tous les éléments focusables
        clearCartModal.addEventListener('hide.bs.modal', function() {
            const activeElement = document.activeElement;
            if (activeElement && clearCartModal.contains(activeElement)) {
                activeElement.blur();
            }
        });
    }
    
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