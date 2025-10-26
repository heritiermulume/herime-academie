@extends('layouts.app')

@section('title', 'Mes Commandes')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-shopping-bag me-2"></i>Mes Commandes
                </h2>
                <a href="{{ route('courses.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-plus me-2"></i>Nouvelle commande
                </a>
            </div>

            @if($orders->count() > 0)
                <div class="row">
                    @foreach($orders as $order)
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card h-100 order-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="fas fa-receipt me-2"></i>{{ $order->order_number }}
                                    </h6>
                                    <span class="badge order-status-{{ $order->status }}">
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
                                
                                <div class="card-body">
                                    <div class="order-info mb-3">
                                        <div class="row text-muted small">
                                            <div class="col-6">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ $order->created_at->format('d/m/Y') }}
                                            </div>
                                            <div class="col-6">
                                                <i class="fas fa-dollar-sign me-1"></i>
                                                ${{ number_format($order->total_amount, 2) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="order-items">
                                        <h6 class="text-muted mb-2">Cours commandés :</h6>
                                        @if($order->order_items && count($order->order_items) > 0)
                                            @foreach(array_slice($order->order_items, 0, 3) as $item)
                                                <div class="order-item d-flex justify-content-between align-items-center mb-2">
                                                    <div class="item-info">
                                                        <div class="item-title">{{ $item['course_title'] ?? 'Cours supprimé' }}</div>
                                                        <div class="item-instructor text-muted small">
                                                            {{ $item['instructor_name'] ?? 'Instructeur inconnu' }}
                                                        </div>
                                                    </div>
                                                    <div class="item-price">
                                                        ${{ number_format($item['price'] ?? 0, 2) }}
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if(count($order->order_items) > 3)
                                                <div class="text-muted small">
                                                    +{{ count($order->order_items) - 3 }} autre(s) cours
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-muted">Aucun cours trouvé</div>
                                        @endif
                                    </div>

                                    @if($order->payment_method)
                                        <div class="payment-method mt-3">
                                            <span class="badge bg-light text-dark">
                                                <i class="fas fa-credit-card me-1"></i>
                                                {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>Détails
                                        </a>
                                        
                                        @if($order->status === 'paid' || $order->status === 'completed')
                                            <a href="{{ route('learning.course', $order->enrollments->first()->course_id ?? '#') }}" 
                                               class="btn btn-success btn-sm">
                                                <i class="fas fa-play me-1"></i>Commencer
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">Aucune commande trouvée</h4>
                        <p class="text-muted mb-4">Vous n'avez pas encore passé de commande.</p>
                        <a href="{{ route('courses.index') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Découvrir nos cours
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.order-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #e9ecef;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

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

.order-item {
    padding: 0.5rem;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    border: 1px solid #e9ecef;
}

.item-title {
    font-weight: 500;
    color: #333;
    font-size: 0.9rem;
}

.item-instructor {
    font-size: 0.8rem;
}

.item-price {
    font-weight: 600;
    color: #28a745;
    font-size: 0.9rem;
}

.empty-state {
    max-width: 400px;
    margin: 0 auto;
}
</style>
@endsection


