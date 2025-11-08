@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('admin-title', 'Tableau de bord')
@section('admin-subtitle', 'Suivi global de la plateforme Herime Académie')
@section('admin-actions')
    <a href="{{ route('admin.analytics') }}" class="btn btn-outline-primary">
        <i class="fas fa-chart-line me-2"></i>Console analytics
    </a>
    <a href="{{ route('courses.index') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-2"></i>Nouveau cours
    </a>
@endsection

@php
    $currencyCode = $baseCurrency ?? 'USD';
    $formatCurrency = fn($amount) => \App\Helpers\CurrencyHelper::formatWithSymbol($amount, $currencyCode);

    $revenueLabels = $revenueByMonth->map(function ($month) {
        $label = $month->month;
        if (is_string($month->month) && preg_match('/^\d{4}-\d{2}$/', $month->month)) {
            try {
                $label = \Carbon\Carbon::createFromFormat('Y-m', $month->month)->translatedFormat('M Y');
            } catch (\Throwable $e) {
                $label = $month->month;
            }
        }
        return $label;
    })->toArray();

    $revenueValues = $revenueByMonth->map(fn($month) => round((float) $month->revenue, 2))->toArray();

    $currentRevenue = $revenueValues[count($revenueValues) - 1] ?? 0;
    $previousRevenue = $revenueValues[count($revenueValues) - 2] ?? 0;
    $growth = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : null;
@endphp

@section('admin-content')
    <section class="row g-3 mb-4">
        <div class="col-12 col-lg-3">
            <div class="insight-card shadow-sm">
                <div class="insight-card__icon bg-primary-subtle text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Communauté</p>
                    <h3 class="insight-card__value">{{ number_format($stats['total_users']) }}</h3>
                    <p class="insight-card__supplement">
                        {{ number_format($stats['total_students']) }} étudiants · {{ number_format($stats['total_instructors']) }} formateurs
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-3">
            <div class="insight-card shadow-sm">
                <div class="insight-card__icon bg-success-subtle text-success">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Catalogue</p>
                    <h3 class="insight-card__value">{{ number_format($stats['total_courses']) }}</h3>
                    <p class="insight-card__supplement">
                        {{ number_format($stats['published_courses']) }} cours publiés
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-3">
            <div class="insight-card shadow-sm">
                <div class="insight-card__icon bg-warning-subtle text-warning">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Commandes</p>
                    <h3 class="insight-card__value">{{ number_format($stats['total_orders']) }}</h3>
                    <p class="insight-card__supplement">
                        {{ number_format($stats['paid_orders']) }} payées · {{ number_format($stats['pending_orders']) }} en attente
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-3">
            <div class="insight-card shadow-sm">
                <div class="insight-card__icon bg-info-subtle text-info">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Revenus</p>
                    <h3 class="insight-card__value">{{ $formatCurrency($stats['total_revenue']) }}</h3>
                    <p class="insight-card__supplement">
                        Croissance {{ ($growth ?? 0) >= 0 ? '+' : '-' }}{{ number_format(abs($growth ?? 0), 1) }}%
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-4 align-items-stretch">
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header card-header-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0 text-white fw-semibold">Revenus des 6 derniers mois</h5>
                        <small class="text-white-50">Montants en {{ $currencyCode }}</small>
                    </div>
                    <span class="badge bg-light text-primary fw-semibold">Temps réel</span>
                </div>
                <div class="card-body">
                    <div class="chart-wrapper">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header card-header-primary">
                    <h6 class="card-title mb-0 text-white fw-semibold">Actions de la journée</h6>
                    <small class="text-white-50">Concentrez vos efforts là où ça compte</small>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled admin-actions">
                        <li>
                            <span class="admin-actions__dot bg-primary"></span>
                            <div>
                                <p class="fw-semibold mb-1">Analyser les inscriptions en baisse</p>
                                <p class="text-muted small mb-0">Comparer les cours les moins dynamiques et proposer une animation.</p>
                            </div>
                        </li>
                        <li>
                            <span class="admin-actions__dot bg-success"></span>
                            <div>
                                <p class="fw-semibold mb-1">Valider les nouvelles candidatures formateur</p>
                                <p class="text-muted small mb-0">Assurer la conformité des profils et planifier l’onboarding.</p>
                            </div>
                        </li>
                        <li>
                            <span class="admin-actions__dot bg-warning"></span>
                            <div>
                                <p class="fw-semibold mb-1">Relancer les commandes en attente</p>
                                <p class="text-muted small mb-0">Contact proactif des apprenants pour finaliser leur paiement.</p>
                            </div>
                        </li>
                    </ul>
                    <div class="p-3 mt-4 rounded-3 bg-light border-start border-4 border-warning">
                        <p class="text-muted small mb-0">
                            Astuce : programmez vos alertes hebdomadaires dans les réglages afin d’être notifié des anomalies.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header card-header-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="card-title mb-0 text-white fw-semibold">Gestion rapide</h6>
                        <small class="text-white-50">Accédez directement aux sections clés de l’espace administrateur</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="quick-actions-grid">
                        <a href="{{ route('admin.courses') }}" class="quick-action-link">
                            <i class="fas fa-book me-2"></i>Gérer les cours
                        </a>
                        <a href="{{ route('admin.categories') }}" class="quick-action-link">
                            <i class="fas fa-tags me-2"></i>Catégories
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="quick-action-link">
                            <i class="fas fa-shopping-bag me-2"></i>Commandes
                        </a>
                        <a href="{{ route('admin.users') }}" class="quick-action-link">
                            <i class="fas fa-users-cog me-2"></i>Utilisateurs
                        </a>
                        @if(Route::has('admin.instructor-applications'))
                            <a href="{{ route('admin.instructor-applications') }}" class="quick-action-link">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Candidatures formateur
                            </a>
                        @endif
                        @if(Route::has('admin.banners.index'))
                            <a href="{{ route('admin.banners.index') }}" class="quick-action-link">
                                <i class="fas fa-image me-2"></i>Bannières
                            </a>
                        @endif
                        <a href="{{ route('admin.announcements') }}" class="quick-action-link">
                            <i class="fas fa-bullhorn me-2"></i>Annonces
                        </a>
                        <a href="{{ route('admin.settings') }}" class="quick-action-link">
                            <i class="fas fa-sliders-h me-2"></i>Réglages
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-4 align-items-stretch">
        <div class="col-12 col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header card-header-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="card-title mb-0 text-white fw-semibold">Top cours du moment</h6>
                        <small class="text-white-50">Classement basé sur les inscriptions</small>
                    </div>
                    <a href="{{ route('admin.courses') }}" class="btn btn-sm btn-light text-primary fw-semibold flex-grow-1 flex-sm-grow-0 text-center">
                        <i class="fas fa-layer-group me-2"></i>Gérer
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($popularCourses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Cours</th>
                                        <th>Formateur</th>
                                        <th>Catégorie</th>
                                        <th class="text-end">Inscriptions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($popularCourses as $course)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold d-block">{{ Str::limit($course->title, 42) }}</span>
                                                <small class="text-muted">{{ Str::limit($course->subtitle, 60) }}</small>
                                            </td>
                                            <td>{{ $course->instructor->name ?? 'N/A' }}</td>
                                            <td><span class="badge bg-light text-muted border">{{ $course->category->name ?? 'Général' }}</span></td>
                                            <td class="text-end fw-semibold">{{ number_format($course->enrollments_count) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-5 text-center text-muted">
                            <i class="fas fa-chart-line fa-2x mb-3"></i>
                            <p class="mb-0">Pas encore de données. Activez vos premiers parcours pour alimenter ce bloc.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header card-header-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="card-title mb-0 text-white fw-semibold">Inscriptions récentes</h6>
                        <small class="text-white-50">10 derniers engagements apprenants</small>
                    </div>
                    <span class="badge bg-light text-primary fw-semibold flex-grow-1 flex-sm-grow-0 text-center">Live</span>
                </div>
                <div class="card-body">
                    @if($recentEnrollments->count() > 0)
                        <div class="timeline">
                            @foreach($recentEnrollments as $enrollment)
                                <div class="timeline__item">
                                    <span class="timeline__dot"></span>
                                    <div class="timeline__content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <p class="fw-semibold mb-1">{{ $enrollment->user->name ?? 'Utilisateur supprimé' }}</p>
                                                <p class="text-muted small mb-2">
                                                    a rejoint <span class="fw-semibold">{{ $enrollment->course->title ?? 'Cours retiré' }}</span>
                                                </p>
                                                <div class="d-flex flex-wrap gap-3 text-muted small">
                                                    <span><i class="fas fa-chalkboard-teacher me-1 text-primary"></i>{{ $enrollment->course->instructor->name ?? 'Non assigné' }}</span>
                                                    <span><i class="fas fa-layer-group me-1 text-primary"></i>{{ $enrollment->course->category->name ?? 'Général' }}</span>
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $enrollment->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-5 text-center text-muted">
                            <i class="fas fa-user-plus fa-2x mb-3"></i>
                            <p class="mb-0">Aucune inscription récente. Lancez une campagne de communication pour stimuler l’intérêt.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="card shadow-sm border-0">
        <div class="card-header card-header-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="card-title mb-0 text-white fw-semibold">Commandes récentes</h6>
                <small class="text-white-50">Dernières transactions traitées</small>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light text-primary fw-semibold flex-grow-1 flex-sm-grow-0 text-center">
                <i class="fas fa-receipt me-2"></i>Tout voir
            </a>
        </div>
        <div class="card-body p-0">
            @if($recentOrders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
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
                                        <span class="fw-semibold">#{{ $order->order_number ?? $order->id }}</span>
                                        <div class="text-muted small">{{ strtoupper($order->payment_method ?? 'Inconnu') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $order->user->name ?? 'Utilisateur supprimé' }}</div>
                                        <small class="text-muted">{{ $order->user->email ?? 'Email indisponible' }}</small>
                                    </td>
                                    <td>
                                        @foreach($order->orderItems as $item)
                                            <div class="text-muted small">{{ Str::limit($item->course->title ?? 'Cours supprimé', 40) }}</div>
                                        @endforeach
                                    </td>
                                    <td>{{ $formatCurrency($order->total) }}</td>
                                    <td>
                                        @switch($order->status)
                                            @case('paid')
                                                <span class="badge bg-success-subtle text-success">Payée</span>
                                                @break
                                            @case('pending')
                                                <span class="badge bg-warning-subtle text-warning">En attente</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger-subtle text-danger">Annulée</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($order->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td class="text-muted small">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-5 text-center text-muted">
                    <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                    <p class="mb-0">Aucune commande enregistrée pour le moment.</p>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('styles')
<style>
    .insight-card {
        background: #ffffff;
        border-radius: 1.25rem;
        padding: 1.5rem;
        display: flex;
        gap: 1.25rem;
        align-items: flex-start;
    }
    .insight-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .insight-card__label {
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.08em;
        margin-bottom: 0.25rem;
        color: #1f2937;
    }
    .insight-card__value {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        color: #0b1f3a;
    }
    .insight-card__supplement {
        margin-bottom: 0;
        color: #334155;
    }
    .chart-wrapper {
        position: relative;
        width: 100%;
        height: 280px;
    }
    .admin-actions {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        margin: 0;
        padding: 0;
    }
    .admin-actions li {
        display: flex;
        gap: 0.9rem;
        align-items: flex-start;
    }
    .admin-actions__dot {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        margin-top: 0.4rem;
    }
    .card-header-primary {
        background: linear-gradient(120deg, #003366 0%, #0b4f99 100%);
        border: none;
        color: #ffffff;
        border-radius: 1rem 1rem 0 0;
    }
    .card-header-primary .btn,
    .card-header-primary .badge {
        border-radius: 999px;
    }
    .card-header-primary .btn-light {
        border: none;
    }
    .timeline {
        position: relative;
        padding-left: 1.5rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 6px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline__item {
        position: relative;
        margin-bottom: 1.75rem;
    }
    .timeline__item:last-child {
        margin-bottom: 0;
    }
    .timeline__dot {
        position: absolute;
        left: -1.5rem;
        top: 0.35rem;
        width: 12px;
        height: 12px;
        border-radius: 999px;
        background: #003366;
    }
    .timeline__content {
        background: #ffffff;
        border-radius: 0.75rem;
        padding: 1rem 1.25rem;
        border: 1px solid #e2e8f0;
    }
    .quick-actions-grid {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    .quick-action-link {
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0.6rem;
        padding: 0.9rem 1rem;
        border: 1px solid #dbe3f0;
        border-radius: 0.75rem;
        font-weight: 600;
        color: #0f172a;
        background: #f9fbff;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .quick-action-link:hover {
        color: #ffffff;
        background: #003366;
        border-color: #003366;
        box-shadow: 0 10px 18px -12px rgba(0, 51, 102, 0.6);
    }
    @media (max-width: 767px) {
        .chart-wrapper {
            height: 240px;
        }
        .quick-actions-grid {
            grid-template-columns: 1fr 1fr;
        }
        .card-header-primary .btn,
        .card-header-primary .badge {
            width: 100%;
            justify-content: center;
        }
    }
    @media (max-width: 575px) {
        .quick-actions-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const revenueLabels = @json($revenueLabels);
    const revenueValues = @json($revenueValues);

    const canvas = document.getElementById('revenueTrendChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: revenueLabels.length ? revenueLabels : [''],
                datasets: [{
                    label: 'Revenus',
                    data: revenueValues.length ? revenueValues : [0],
                    tension: 0.35,
                    borderWidth: 3,
                    borderColor: 'rgba(0, 68, 136, 1)',
                    backgroundColor: 'rgba(0, 68, 136, 0.15)',
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: 'rgba(0, 68, 136, 1)',
                    pointHoverBorderWidth: 3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                const value = context.raw ?? 0;
                                return ' ' + new Intl.NumberFormat('fr-FR', {
                                    style: 'currency',
                                    currency: '{{ $currencyCode }}'
                                }).format(value);
                            }
                        },
                        padding: 12,
                        backgroundColor: 'rgba(15, 23, 42, 0.92)',
                        titleFont: { size: 12, weight: '600' },
                        bodyFont: { size: 12 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 11 } }
                    },
                    y: {
                        grid: { color: 'rgba(148, 163, 184, 0.2)' },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11 },
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR', {
                                    style: 'currency',
                                    currency: '{{ $currencyCode }}',
                                    maximumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush

