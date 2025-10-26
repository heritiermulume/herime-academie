@extends('layouts.app')

@section('title', 'Détails de la commande')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-receipt me-2"></i>Commande {{ $order->order_number }}
                    </h2>
                    <p class="text-muted mb-0">
                        Passée le {{ $order->created_at->format('d/m/Y à H:i') }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                    
                    @if($order->status === 'pending')
                        <button class="btn btn-success" onclick="confirmOrder({{ $order->id }})">
                            <i class="fas fa-check me-2"></i>Confirmer
                        </button>
                        <button class="btn btn-danger" onclick="cancelOrder({{ $order->id }})">
                            <i class="fas fa-times me-2"></i>Annuler
                        </button>
                    @elseif($order->status === 'confirmed')
                        <button class="btn btn-info" onclick="markAsPaid({{ $order->id }})">
                            <i class="fas fa-credit-card me-2"></i>Marquer comme payé
                        </button>
                    @elseif($order->status === 'paid')
                        <button class="btn btn-purple" onclick="markAsCompleted({{ $order->id }})">
                            <i class="fas fa-check-double me-2"></i>Marquer comme terminé
                        </button>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Order Details -->
                <div class="col-lg-8">
                    <!-- Order Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Informations de la commande
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Numéro de commande</label>
                                        <div class="fw-bold">{{ $order->order_number }}</div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Date de commande</label>
                                        <div>{{ $order->created_at->format('d/m/Y à H:i') }}</div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Mode de paiement</label>
                                        <div>
                                            <span class="badge bg-light text-dark">
                                                {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Non spécifié')) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Statut</label>
                                        <div>
                                            <span class="badge order-status-{{ $order->status }} fs-6">
                                                @switch($order->status)
                                                    @case('pending')
                                                        <i class="fas fa-clock me-1"></i>En attente
                                                        @break
                                                    @case('confirmed')
                                                        <i class="fas fa-check-circle me-1"></i>Confirmée
                                                        @break
                                                    @case('paid')
                                                        <i class="fas fa-credit-card me-1"></i>Payée
                                                        @break
                                                    @case('completed')
                                                        <i class="fas fa-check-double me-1"></i>Terminée
                                                        @break
                                                    @case('cancelled')
                                                        <i class="fas fa-times-circle me-1"></i>Annulée
                                                        @break
                                                    @default
                                                        {{ ucfirst($order->status) }}
                                                @endswitch
                                            </span>
                                        </div>
                                    </div>
                                    @if($order->payment_reference)
                                        <div class="info-item mb-3">
                                            <label class="text-muted small">Référence de paiement</label>
                                            <div class="fw-bold">{{ $order->payment_reference }}</div>
                                        </div>
                                    @endif
                                    @if($order->confirmed_at)
                                        <div class="info-item mb-3">
                                            <label class="text-muted small">Date de confirmation</label>
                                            <div>{{ $order->confirmed_at ? $order->confirmed_at->format('d/m/Y à H:i') : 'Non confirmée' }}</div>
                                        </div>
                                    @endif
                                    @if($order->paid_at)
                                        <div class="info-item mb-3">
                                            <label class="text-muted small">Date de paiement</label>
                                            <div>{{ $order->paid_at ? $order->paid_at->format('d/m/Y à H:i') : 'Non payée' }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>Informations client
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Nom complet</label>
                                        <div class="fw-bold">{{ $order->user->name }}</div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Email</label>
                                        <div>{{ $order->user->email }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Membre depuis</label>
                                        <div>{{ $order->user->created_at->format('d/m/Y') }}</div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">ID utilisateur</label>
                                        <div class="fw-bold">#{{ $order->user->id }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            @if($order->billing_info)
                                <hr>
                                <h6 class="text-muted mb-3">Informations de facturation</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        @if(isset($order->billing_info['first_name']))
                                            <div class="info-item mb-2">
                                                <label class="text-muted small">Prénom</label>
                                                <div>{{ $order->billing_info['first_name'] }}</div>
                                            </div>
                                        @endif
                                        @if(isset($order->billing_info['last_name']))
                                            <div class="info-item mb-2">
                                                <label class="text-muted small">Nom</label>
                                                <div>{{ $order->billing_info['last_name'] }}</div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        @if(isset($order->billing_info['email']))
                                            <div class="info-item mb-2">
                                                <label class="text-muted small">Email</label>
                                                <div>{{ $order->billing_info['email'] }}</div>
                                            </div>
                                        @endif
                                        @if(isset($order->billing_info['phone']))
                                            <div class="info-item mb-2">
                                                <label class="text-muted small">Téléphone</label>
                                                <div>{{ $order->billing_info['phone'] }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-shopping-cart me-2"></i>Cours commandés
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($order->order_items && count($order->order_items) > 0)
                                <div class="order-items">
                                    @foreach($order->order_items as $item)
                                        <div class="order-item row align-items-center py-3 border-bottom">
                                            <div class="col-md-8">
                                                <div class="d-flex align-items-center">
                                                    <div class="course-icon me-3">
                                                        <i class="fas fa-play-circle fa-2x text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">{{ $item['course_title'] ?? 'Cours supprimé' }}</h6>
                                                        <p class="text-muted mb-0 small">
                                                            <i class="fas fa-user me-1"></i>{{ $item['instructor_name'] ?? 'Instructeur inconnu' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <span class="badge bg-light text-dark">
                                                    Quantité: {{ $item['quantity'] ?? 1 }}
                                                </span>
                                            </div>
                                            <div class="col-md-2 text-end">
                                                <div class="fw-bold text-success">
                                                    ${{ number_format($item['price'] ?? 0, 2) }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                    <p>Aucun cours trouvé pour cette commande.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Enrollments -->
                    @if($order->enrollments->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-graduation-cap me-2"></i>Accès aux cours
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($order->enrollments as $enrollment)
                                        <div class="col-md-6 mb-3">
                                            <div class="enrollment-item p-3 border rounded">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1">{{ $enrollment->course->title ?? 'Cours supprimé' }}</h6>
                                                        <p class="text-muted small mb-2">
                                                            Inscrit le {{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('d/m/Y') : 'Date non disponible' }}
                                                        </p>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Actif
                                                        </span>
                                                    </div>
                                                    <a href="{{ route('learning.course', $enrollment->course_id) }}" 
                                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="fas fa-external-link-alt me-1"></i>Voir
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calculator me-2"></i>Résumé financier
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="summary-item d-flex justify-content-between mb-2">
                                <span>Sous-total</span>
                                <span>${{ number_format($order->subtotal ?? $order->total_amount, 2) }}</span>
                            </div>
                            <div class="summary-item d-flex justify-content-between mb-2">
                                <span>Remise</span>
                                <span>${{ number_format($order->discount ?? 0, 2) }}</span>
                            </div>
                            <div class="summary-item d-flex justify-content-between mb-2">
                                <span>Taxes</span>
                                <span>${{ number_format($order->tax ?? 0, 2) }}</span>
                            </div>
                            <hr>
                            <div class="summary-item d-flex justify-content-between fw-bold fs-5">
                                <span>Total</span>
                                <span class="text-success">${{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    @if($order->notes)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-sticky-note me-2"></i>Notes
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $order->notes }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Timeline -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-history me-2"></i>Historique
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Commande créée</h6>
                                        <p class="text-muted small mb-0">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>
                                
                                @if($order->confirmed_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Commande confirmée</h6>
                                            <p class="text-muted small mb-0">{{ $order->confirmed_at ? $order->confirmed_at->format('d/m/Y à H:i') : 'Non confirmée' }}</p>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($order->paid_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Paiement reçu</h6>
                                            <p class="text-muted small mb-0">{{ $order->paid_at ? $order->paid_at->format('d/m/Y à H:i') : 'Non payée' }}</p>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($order->completed_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-purple"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Commande terminée</h6>
                                            <p class="text-muted small mb-0">{{ $order->completed_at ? $order->completed_at->format('d/m/Y à H:i') : 'Non terminée' }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Modals -->
@include('admin.orders.partials.action-modals')

<style>
.order-status-pending {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.order-status-confirmed {
    background-color: #17a2b8 !important;
    color: #fff !important;
}

.order-status-paid {
    background-color: #28a745 !important;
    color: #fff !important;
}

.order-status-completed {
    background-color: #6f42c1 !important;
    color: #fff !important;
}

.order-status-cancelled {
    background-color: #dc3545 !important;
    color: #fff !important;
}

.info-item label {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.order-item:last-child {
    border-bottom: none !important;
}

.enrollment-item {
    transition: box-shadow 0.2s ease;
}

.enrollment-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.summary-item {
    font-size: 0.9rem;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.bg-purple {
    background-color: #6f42c1 !important;
}
</style>
@endsection
