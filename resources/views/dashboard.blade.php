@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
            </h2>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-1">{{ auth()->user()->orders()->count() }}</h4>
                            <p class="mb-0">Commandes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shopping-bag fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-1">{{ auth()->user()->enrollments()->count() }}</h4>
                            <p class="mb-0">Cours suivis</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-graduation-cap fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-1">{{ auth()->user()->enrollments()->where('status', 'active')->count() }}</h4>
                            <p class="mb-0">Cours actifs</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-play-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-1">${{ number_format(auth()->user()->orders()->sum('total_amount'), 2) }}</h4>
                            <p class="mb-0">Total dépensé</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-bag me-2"></i>Commandes récentes
                    </h5>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary">
                        Voir toutes
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $recentOrders = auth()->user()->orders()->latest()->limit(5)->get();
                    @endphp
                    
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Commande</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td>
                                                <strong>{{ $order->order_number }}</strong>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    ${{ number_format($order->total_amount, 2) }}
                                                </span>
                                            </td>
                                            <td>
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
                                            </td>
                                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <a href="{{ route('orders.show', $order) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune commande</h5>
                            <p class="text-muted">Vous n'avez pas encore passé de commande.</p>
                            <a href="{{ route('courses.index') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Découvrir nos cours
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Active Courses -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-play-circle me-2"></i>Cours actifs
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $activeEnrollments = auth()->user()->enrollments()->where('status', 'active')->with('course')->limit(5)->get();
                    @endphp
                    
                    @if($activeEnrollments->count() > 0)
                        @foreach($activeEnrollments as $enrollment)
                            <div class="course-item d-flex align-items-center mb-3 p-2 border rounded">
                                <div class="course-icon me-3">
                                    <i class="fas fa-play-circle fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $enrollment->course->title ?? 'Cours supprimé' }}</h6>
                                    <p class="text-muted small mb-0">
                                        Inscrit le {{ $enrollment->enrolled_at->format('d/m/Y') }}
                                    </p>
                                </div>
                                <a href="{{ route('learning.course', $enrollment->course_id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-play"></i>
                                </a>
                            </div>
                        @endforeach
                        
                        @if(auth()->user()->enrollments()->where('status', 'active')->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('learning.index') }}" class="btn btn-sm btn-outline-primary">
                                    Voir tous les cours
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Aucun cours actif</h6>
                            <p class="text-muted small">Commencez par passer une commande.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

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

.course-item {
    transition: box-shadow 0.2s ease;
}

.course-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>
@endsection
