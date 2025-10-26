@extends('layouts.app')

@section('title', 'Tableau de bord administrateur - Herime Academie')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 fw-bold mb-1">Tableau de bord administrateur</h1>
                    <p class="text-muted mb-0">Gérez votre plateforme d'apprentissage en ligne</p>
                </div>
                <div>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-danger me-2">
                        <i class="fas fa-shopping-bag me-2"></i>Gérer les commandes
                    </a>
                    <a href="{{ route('admin.analytics') }}" class="btn btn-primary">
                        <i class="fas fa-chart-line me-2"></i>Analytics détaillées
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-users text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total utilisateurs</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_users']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-graduation-cap text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Étudiants</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_students']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-chalkboard-teacher text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Formateurs</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_instructors']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-book text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Cours publiés</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['published_courses']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue and Orders Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-dollar-sign text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Revenus totaux</h6>
                            <h3 class="mb-0 fw-bold">${{ number_format($stats['total_revenue'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-shopping-cart text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total commandes</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_orders']) }}</h3>
                            <small class="text-muted">
                                <span class="text-warning">{{ $stats['pending_orders'] }} en attente</span> • 
                                <span class="text-success">{{ $stats['paid_orders'] }} payées</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-user-plus text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Inscriptions</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_enrollments']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-book-open text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total cours</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_courses']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Évolution des revenus (6 derniers mois)</h5>
                </div>
                <div class="card-body">
                    @if($revenueByMonth->count() > 0)
                        <canvas id="revenueChart" height="100"></canvas>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune donnée de revenus disponible</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-danger">
                            <i class="fas fa-shopping-bag me-2"></i>Gérer les commandes
                        </a>
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-primary">
                            <i class="fas fa-users me-2"></i>Gérer les utilisateurs
                        </a>
                        <a href="{{ route('admin.courses') }}" class="btn btn-outline-success">
                            <i class="fas fa-book me-2"></i>Gérer les cours
                        </a>
                        <a href="{{ route('admin.categories') }}" class="btn btn-outline-warning">
                            <i class="fas fa-tags me-2"></i>Gérer les catégories
                        </a>
                        <a href="{{ route('admin.announcements') }}" class="btn btn-outline-info">
                            <i class="fas fa-bullhorn me-2"></i>Gérer les annonces
                        </a>
                        <a href="{{ route('admin.partners') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-handshake me-2"></i>Gérer les partenaires
                        </a>
                        <a href="{{ route('admin.testimonials') }}" class="btn btn-outline-dark">
                            <i class="fas fa-quote-left me-2"></i>Gérer les témoignages
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Popular Courses -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Cours les plus populaires</h5>
                </div>
                <div class="card-body p-0">
                    @if($popularCourses->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($popularCourses as $course)
                            <div class="list-group-item border-0 py-3">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $course->thumbnail ? Storage::url($course->thumbnail) : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=60&h=40&fit=crop' }}" 
                                         alt="{{ $course->title }}" class="rounded me-3" style="width: 60px; height: 40px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">{{ Str::limit($course->title, 40) }}</h6>
                                        <p class="text-muted small mb-1">{{ $course->instructor->name }}</p>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary me-2">{{ $course->category->name }}</span>
                                            <small class="text-muted">
                                                <i class="fas fa-users me-1"></i>{{ number_format($course->stats['total_students'] ?? 0) }} étudiants
                                            </small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-star text-warning me-1"></i>
                                            <span class="fw-bold">{{ number_format($course->stats['average_rating'] ?? 0, 1) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-book fa-2x text-muted mb-2"></i>
                            <p class="text-muted small">Aucun cours disponible</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Enrollments -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Inscriptions récentes</h5>
                </div>
                <div class="card-body p-0">
                    @if($recentEnrollments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentEnrollments as $enrollment)
                            <div class="list-group-item border-0 py-3">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $enrollment->user->avatar ? Storage::url($enrollment->user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($enrollment->user->name) . '&background=003366&color=fff' }}" 
                                         alt="{{ $enrollment->user->name }}" class="rounded-circle me-3" width="40" height="40">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $enrollment->user->name }}</h6>
                                        <p class="text-muted small mb-1">{{ $enrollment->course->title }}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ $enrollment->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success">Nouveau</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-plus fa-2x text-muted mb-2"></i>
                            <p class="text-muted small">Aucune inscription récente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Commandes récentes</h5>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>Voir toutes
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Commande</th>
                                        <th>Client</th>
                                        <th>Cours</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">#{{ $order->order_number }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $order->user->avatar ? Storage::url($order->user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($order->user->name) . '&background=003366&color=fff' }}" 
                                                     alt="{{ $order->user->name }}" class="rounded-circle me-2" width="30" height="30">
                                                <span>{{ $order->user->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @foreach($order->orderItems as $item)
                                                <div class="small">{{ Str::limit($item->course->title, 30) }}</div>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="fw-bold">${{ number_format($order->total, 2) }}</span>
                                        </td>
                                        <td>
                                            @switch($order->status)
                                                @case('paid')
                                                    <span class="badge bg-success">Payé</span>
                                                    @break
                                                @case('pending')
                                                    <span class="badge bg-warning">En attente</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Annulé</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune commande récente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
@if($revenueByMonth->count() > 0)
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            @foreach($revenueByMonth as $month)
                '{{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('M Y') }}',
            @endforeach
        ],
        datasets: [{
            label: 'Revenus ($)',
            data: [
                @foreach($revenueByMonth as $month)
                    {{ $month->revenue }},
                @endforeach
            ],
            borderColor: '#003366',
            backgroundColor: 'rgba(0, 51, 102, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
@endif
</script>
@endpush

@push('styles')
<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

#revenueChart {
    max-height: 300px;
}
</style>
@endpush