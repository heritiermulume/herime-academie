@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('admin-title', 'Tableau de bord')
@section('admin-subtitle', 'Suivi global de la plateforme Herime Académie')
@section('admin-actions')
    <a href="{{ route('admin.analytics') }}" class="btn btn-outline-primary">
        <i class="fas fa-chart-line me-2"></i>Console analytics
    </a>
@endsection

@php
    $currencyCode = is_array($baseCurrency) ? ($baseCurrency['code'] ?? 'USD') : ($baseCurrency ?? 'USD');
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

    // Données pour le graphique de revenus par catégorie
    $revenueByCategoryLabels = $revenueByCategory->map(fn($cat) => $cat->name ?? 'Sans catégorie')->toArray();
    $revenueByCategoryValues = $revenueByCategory->map(fn($cat) => round((float) $cat->revenue, 2))->toArray();
@endphp

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <div class="row g-3 mb-4">
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
            </div>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-chart-line me-2"></i>Analyse financière et revenus
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="row g-4">
                <div class="col-12 col-xl-6">
                    <div class="admin-card shadow-sm h-100 admin-card--revenue">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-chart-line me-2"></i>Revenus des 6 derniers mois
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container chart-container--revenue">
                                <canvas id="revenueTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="admin-card shadow-sm h-100 admin-card--revenue">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-tags me-2"></i>Revenus par catégorie
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container chart-container--revenue">
                                <canvas id="revenueByCategoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4 mt-2">
                <div class="col-12">
                    <div class="admin-card shadow-sm h-100 admin-card--actions">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-tasks me-2"></i>Actions de la journée
                            </h5>
                        </div>
                        <div class="admin-card__body admin-card__body--actions">
                            <div class="admin-actions-container">
                                <ul class="list-unstyled admin-actions">
                                    <li class="admin-actions__item">
                                        <span class="admin-actions__dot bg-primary"></span>
                                        <div>
                                            <p class="fw-semibold">Analyser les inscriptions en baisse</p>
                                            <p class="text-muted small">Comparer les cours les moins dynamiques et proposer une animation.</p>
                                        </div>
                                    </li>
                                    <li class="admin-actions__item">
                                        <span class="admin-actions__dot bg-success"></span>
                                        <div>
                                            <p class="fw-semibold">Valider les nouvelles candidatures formateur</p>
                                            <p class="text-muted small">Assurer la conformité des profils et planifier l'onboarding.</p>
                                        </div>
                                    </li>
                                    <li class="admin-actions__item">
                                        <span class="admin-actions__dot bg-warning"></span>
                                        <div>
                                            <p class="fw-semibold">Relancer les commandes en attente</p>
                                            <p class="text-muted small">Contact proactif des apprenants pour finaliser leur paiement.</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="p-2 mt-2 rounded-3 bg-light border-start border-4 border-warning" style="margin-top: 0.5rem !important;">
                                <p class="text-muted small" style="margin: 0;">
                                    Astuce : programmez vos alertes hebdomadaires dans les réglages afin d'être notifié des anomalies.
                                </p>
                            </div>
                        </div>
            </div>
        </div>
            </div>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-bolt me-2"></i>Gestion rapide
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="row g-4">
                <div class="col-12">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-bolt me-2"></i>Accès rapide
                            </h5>
                        </div>
                        <div class="admin-card__body">
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
            </div>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-trophy me-2"></i>Activité et performance
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="row g-4">
                <div class="col-12 col-xl-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-trophy me-2"></i>Top cours du moment
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            @if($popularCourses->count() > 0)
                                <div class="admin-table">
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
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
                                </div>
                            @else
                                <div class="admin-table__empty">
                                    <i class="fas fa-chart-line fa-2x mb-3"></i>
                                    <p class="mb-0">Pas encore de données. Activez vos premiers parcours pour alimenter ce bloc.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-user-plus me-2"></i>Inscriptions récentes
                            </h5>
                        </div>
                        <div class="admin-card__body">
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
                        <div class="admin-table__empty">
                            <i class="fas fa-user-plus fa-2x mb-3"></i>
                            <p class="mb-0">Aucune inscription récente. Lancez une campagne de communication pour stimuler l'intérêt.</p>
                        </div>
                    @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-shopping-bag me-2"></i>Commandes récentes
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="admin-card shadow-sm">
                <div class="admin-card__header">
                    <h5 class="admin-card__title">
                        <i class="fas fa-receipt me-2"></i>Dernières transactions
                    </h5>
                </div>
                <div class="admin-card__body">
                    @if($recentOrders->count() > 0)
                        <div class="admin-table">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
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
                        </div>
                    @else
                        <div class="admin-table__empty">
                            <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                            <p class="mb-0">Aucune commande enregistrée pour le moment.</p>
                        </div>
                    @endif
                </div>
            </div>
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
    .chart-container {
        position: relative;
        width: 100%;
        min-height: 180px;
        max-height: 320px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Réduire la hauteur des graphiques de revenus sur desktop */
    @media (min-width: 1200px) {
        .admin-card--revenue .chart-container--revenue {
            min-height: 200px;
            max-height: 240px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .admin-card--revenue .chart-container--revenue canvas {
            width: 100% !important;
            height: 100% !important;
        }
        
        .admin-card--revenue .admin-card__body {
            padding: 1rem 1.25rem;
        }
    }

    .chart-container canvas {
        max-width: 100%;
        max-height: 100%;
        width: 100% !important;
        height: 100% !important;
        display: block;
    }

    .admin-card__body canvas {
        max-width: 100%;
        max-height: 100%;
        width: 100% !important;
        height: 100% !important;
        display: block;
    }
    .admin-actions-container {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        margin: 0;
        padding: 0;
    }

    .admin-actions {
        display: flex;
        flex-direction: row;
        gap: 0.875rem;
        margin: 0;
        padding: 0;
        min-width: max-content;
    }

    .admin-actions__item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 220px;
        max-width: 260px;
        flex-shrink: 0;
        padding: 0.625rem;
        background: #f8fafc;
        border-radius: 0.75rem;
        border: 1px solid rgba(226, 232, 240, 0.6);
    }

    .admin-actions__item > div {
        flex: 1;
        margin: 0;
    }

    .admin-actions__item p {
        font-size: 0.85rem;
        line-height: 1.4;
        margin: 0;
    }

    .admin-actions__item .fw-semibold {
        font-size: 0.875rem;
        margin: 0;
    }

    .admin-actions__item .text-muted.small {
        font-size: 0.75rem;
        margin: 0;
    }

    .admin-actions__dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        flex-shrink: 0;
        margin-top: 0.2rem;
    }

    .admin-card--actions .admin-card__body--actions {
        padding: 0;
    }
    /* Styles des cartes identiques à analytics */
    .admin-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .admin-card__header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 16px 16px 0 0;
    }

    .admin-card__title {
        margin: 0;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }

    .admin-card__body {
        padding: 1.25rem;
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
        .chart-container {
            min-height: 180px;
            max-height: 240px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }
        .quick-actions-grid {
            grid-template-columns: 1fr 1fr;
        }
        .card-header-primary .btn,
        .card-header-primary .badge {
            width: 100%;
            justify-content: center;
        }

        .admin-actions-container {
            margin: 0;
            padding: 0;
            overflow-x: visible;
        }

        .admin-actions {
            flex-direction: column;
            gap: 0.75rem;
            min-width: auto;
        }

        .admin-actions__item {
            min-width: 100%;
            max-width: 100%;
            padding: 0.5rem;
        }

        .admin-card--actions .admin-card__body--actions {
            padding: 0;
        }
    }
    @media (max-width: 575px) {
        .quick-actions-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Styles responsive comme analytics */
    @media (max-width: 991.98px) {
        /* Réduire les paddings et margins sur tablette */
        .admin-panel {
            margin-bottom: 1rem;
        }
        
        /* Padding uniquement pour la première section principale */
        .admin-panel--main .admin-panel__body {
            padding: 1rem !important;
        }
        
        /* Pas de padding pour les autres sections */
        .admin-panel:not(.admin-panel--main) .admin-panel__body {
            padding: 0 !important;
        }
        
        .admin-panel__header {
            padding: 0.5rem 0.75rem;
        }
        
        .admin-panel__header h3 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }
        
        .admin-panel__body .row.g-3 {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }
        
        .admin-panel__body .row.g-4 {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }
        
        .admin-panel__body .row.mb-4 {
            margin-bottom: 0.5rem !important;
        }
        
        .admin-panel__body .row.mt-2 {
            margin-top: 0.375rem !important;
        }
        
        .admin-card__header {
            padding: 0.5rem 0.75rem;
        }
        
        .admin-card__header {
            padding: 0.5rem 0.75rem;
        }

        .admin-card__body {
            padding: 0.5rem;
        }
    }

    @media (max-width: 767.98px) {
        /* Réduire encore plus les paddings et margins sur mobile */
        .admin-panel {
            margin-bottom: 0.75rem;
        }
        
        /* Padding uniquement pour la première section principale */
        .admin-panel--main .admin-panel__body {
            padding: 0.75rem !important;
        }
        
        /* Pas de padding pour les autres sections */
        .admin-panel:not(.admin-panel--main) .admin-panel__body {
            padding: 0 !important;
        }
        
        .admin-panel__header {
            padding: 0.375rem 0.5rem;
        }
        
        .admin-panel__header h3 {
            font-size: 0.95rem;
            margin-bottom: 0.125rem;
        }
        
        .admin-panel__body .row.g-3 {
            --bs-gutter-x: 0.375rem;
            --bs-gutter-y: 0.375rem;
        }
        
        .admin-panel__body .row.g-4 {
            --bs-gutter-x: 0.375rem;
            --bs-gutter-y: 0.375rem;
        }
        
        .admin-panel__body .row.mb-4 {
            margin-bottom: 0.5rem !important;
        }
        
        .admin-panel__body .row.mt-2 {
            margin-top: 0.375rem !important;
        }
        
        .admin-card__header {
            padding: 0.5rem 0.625rem;
        }

        .admin-card__body {
            padding: 0.375rem;
        }

        .chart-container {
            min-height: 180px;
            max-height: 240px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .admin-actions-container {
            margin: 0;
            padding: 0;
            overflow-x: visible;
        }

        .admin-actions {
            flex-direction: column;
            gap: 0.625rem;
        }

        .admin-actions__item {
            min-width: 100%;
            max-width: 100%;
            padding: 0.5rem;
        }

        .admin-card--actions .admin-card__body--actions {
            padding: 0;
        }
        
        /* Supprimer les scrollbars des conteneurs, garder seulement celle de table-responsive */
        .admin-table {
            overflow: visible !important;
        }
        
        .admin-panel__body {
            overflow: visible !important;
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const revenueLabels = @json($revenueLabels);
    const revenueValues = @json($revenueValues);
    const revenueByCategoryLabels = @json($revenueByCategoryLabels ?? []);
    const revenueByCategoryValues = @json($revenueByCategoryValues ?? []);

    // Fonction pour générer des couleurs
    function generateColors(count) {
        const colors = [
            '#003366', '#28a745', '#ffc107', '#17a2b8', '#dc3545',
            '#6f42c1', '#fd7e14', '#20c997', '#e83e8c', '#6c757d'
        ];
        const result = [];
        for (let i = 0; i < count; i++) {
            result.push(colors[i % colors.length]);
        }
        return result;
    }

    // Fonction pour détecter la taille d'écran
    function isMobile() {
        return window.innerWidth < 768;
    }

    function isTablet() {
        return window.innerWidth >= 768 && window.innerWidth < 992;
    }

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

    // Graphique des revenus par catégorie
    const revenueByCategoryCanvas = document.getElementById('revenueByCategoryChart');
    if (revenueByCategoryCanvas) {
        const revenueByCategoryCtx = revenueByCategoryCanvas.getContext('2d');
        const categoryColors = generateColors(revenueByCategoryLabels.length);
        
        new Chart(revenueByCategoryCtx, {
            type: 'doughnut',
            data: {
                labels: revenueByCategoryLabels.length ? revenueByCategoryLabels : [''],
                datasets: [{
                    label: 'Revenus',
                    data: revenueByCategoryValues.length ? revenueByCategoryValues : [0],
                    backgroundColor: categoryColors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            boxWidth: 12,
                            font: { size: isMobile() ? 10 : 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed ?? 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + new Intl.NumberFormat('fr-FR', {
                                    style: 'currency',
                                    currency: '{{ $currencyCode }}'
                                }).format(value) + ' (' + percentage + '%)';
                            }
                        },
                        padding: 12,
                        backgroundColor: 'rgba(15, 23, 42, 0.92)',
                        titleFont: { size: 12, weight: '600' },
                        bodyFont: { size: 12 }
                    }
                }
            }
        });
    }
</script>
@endpush

