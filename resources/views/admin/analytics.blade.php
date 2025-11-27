@extends('layouts.admin')

@section('title', 'Analytics')
@section('admin-title')
Analyses & Statistiques
@endsection
@section('admin-subtitle', 'Visualisez les indicateurs clés de performance de la plateforme')
@section('admin-actions')
    <button type="button" class="btn btn-light" onclick="refreshAnalytics()" id="refreshAnalyticsBtn">
        <i class="fas fa-sync-alt me-2" id="refreshIcon"></i><span id="refreshText">Mis à jour maintenant</span>
    </button>
@endsection

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Utilisateurs</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['total_users'] ?? 0) }}</p>
                    <p class="admin-stat-card__muted">Total inscrits</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Cours</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['total_courses'] ?? 0) }}</p>
                    <p class="admin-stat-card__muted">Catalogue disponible</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Commandes</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['total_orders'] ?? 0) }}</p>
                    <p class="admin-stat-card__muted">Transactions enregistrées</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Revenus totaux</p>
                    <p class="admin-stat-card__value">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($stats['total_revenue'] ?? 0) }}</p>
                    <p class="admin-stat-card__muted">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($stats['internal_revenue'] ?? 0) }} internes + {{ \App\Helpers\CurrencyHelper::formatWithSymbol($stats['commissions_revenue'] ?? 0) }} commissions</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Visiteurs uniques</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['unique_visitors'] ?? 0) }}</p>
                    <p class="admin-stat-card__muted">Visiteurs distincts</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Visites</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['total_visits'] ?? $stats['total_visitors'] ?? 0) }}</p>
                    <p class="admin-stat-card__muted">Total des visites</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Visites aujourd'hui</p>
                    <p class="admin-stat-card__value">{{ number_format($stats['visitors_today'] ?? 0) }}</p>
                    <p class="admin-stat-card__muted">{{ number_format($stats['unique_visitors_today'] ?? 0) }} visiteurs uniques</p>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-chart-line me-2"></i>Statistiques de revenus
            </h3>
        </div>
        <div class="admin-panel__body">
            <!-- Graphique des différents revenus -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title mb-1">
                                <i class="fas fa-chart-area me-2"></i>Évolution des différents revenus
                            </h5>
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <select id="revenueBreakdownPeriodFilter" class="form-select form-select-sm" style="width: auto; min-width: 120px;">
                                    <option value="day">Par jour</option>
                                    <option value="week">Par semaine</option>
                                    <option value="month" selected>Par mois</option>
                                    <option value="year">Par année</option>
                                </select>
                                <input type="date" id="revenueBreakdownStartDate" class="form-control form-control-sm" style="width: auto; min-width: 140px;">
                                <input type="date" id="revenueBreakdownEndDate" class="form-control form-control-sm" style="width: auto; min-width: 140px;">
                                <button type="button" class="form-control form-control-sm d-inline-flex align-items-center justify-content-center gap-2" onclick="updateRevenueBreakdownChart()" title="Filtrer" style="width: auto; min-width: 140px; padding: 0.25rem 0.5rem; cursor: pointer; background-color: #fff; border: 1px solid #ced4da; border-radius: 0.375rem;">
                                    <i class="fas fa-filter" style="font-size: 0.875rem; color: #495057;"></i>
                                    <span class="filter-text">Filtrer</span>
                                </button>
                            </div>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="revenueBreakdownChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-12">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title mb-1">
                                <i class="fas fa-chart-bar me-2"></i>Évolution des revenus totaux
                            </h5>
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <select id="revenuePeriodFilter" class="form-select form-select-sm" style="width: auto; min-width: 120px;">
                                    <option value="day">Par jour</option>
                                    <option value="week">Par semaine</option>
                                    <option value="month" selected>Par mois</option>
                                    <option value="year">Par année</option>
                                </select>
                                <input type="date" id="revenueStartDate" class="form-control form-control-sm" style="width: auto; min-width: 140px;">
                                <input type="date" id="revenueEndDate" class="form-control form-control-sm" style="width: auto; min-width: 140px;">
                                <button type="button" class="form-control form-control-sm d-inline-flex align-items-center justify-content-center gap-2" onclick="updateRevenueChart()" title="Filtrer" style="width: auto; min-width: 140px; padding: 0.25rem 0.5rem; cursor: pointer; background-color: #fff; border: 1px solid #ced4da; border-radius: 0.375rem;">
                                    <i class="fas fa-filter" style="font-size: 0.875rem; color: #495057;"></i>
                                    <span class="filter-text">Filtrer</span>
                                </button>
                            </div>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="admin-card__title mb-0">
                                    <i class="fas fa-tags me-2"></i>Revenus par catégorie
                                </h5>
                                <select id="categoryPeriodFilter" class="form-select form-select-sm" style="width: auto; min-width: 100px;" onchange="updateCategoryChart()">
                                    <option value="all" selected>Tout</option>
                                    <option value="30">30 jours</option>
                                    <option value="90">90 jours</option>
                                    <option value="365">1 an</option>
                                </select>
                            </div>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="revenueByCategoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="admin-card__title mb-0">
                                    <i class="fas fa-book me-2"></i>Revenus par cours
                                </h5>
                                <select id="coursePeriodFilter" class="form-select form-select-sm" style="width: auto; min-width: 100px;" onchange="updateCourseChart()">
                                    <option value="all" selected>Tout</option>
                                    <option value="30">30 jours</option>
                                    <option value="90">90 jours</option>
                                    <option value="365">1 an</option>
                                </select>
                            </div>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="revenueByCourseChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="admin-card__title mb-0">
                                    <i class="fas fa-user-tie me-2"></i>Revenus par formateur
                                </h5>
                                <select id="instructorPeriodFilter" class="form-select form-select-sm" style="width: auto; min-width: 100px;" onchange="updateInstructorChart()">
                                    <option value="all" selected>Tout</option>
                                    <option value="30">30 jours</option>
                                    <option value="90">90 jours</option>
                                    <option value="365">1 an</option>
                                </select>
                            </div>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="revenueByInstructorChart"></canvas>
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
                <i class="fas fa-chart-pie me-2"></i>Répartition des cours et paiements
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-tags me-2"></i>Cours par catégorie
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="categoriesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-wallet me-2"></i>Paiements par méthode
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="paymentsMethodChart"></canvas>
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
                <i class="fas fa-trophy me-2"></i>Performance des cours et transactions
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-star me-2"></i>Cours les plus populaires
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            @if($popularCourses->count() > 0)
                            <div class="modern-courses-container">
                                <div class="modern-courses-wrapper">
                                    @foreach($popularCourses as $course)
                                    <a href="{{ route('courses.show', $course->slug) }}" class="modern-course-item" target="_blank">
                                        <div class="modern-course-thumbnail">
                                            @if($course->thumbnail)
                                                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" loading="lazy">
                                            @else
                                                <div class="modern-course-placeholder">
                                                    <i class="fas fa-book"></i>
                                        </div>
                                            @endif
                                            <div class="modern-course-badge">
                                                <i class="fas fa-users me-1"></i>{{ $course->enrollments_count }}
                                            </div>
                                        </div>
                                        <div class="modern-course-content">
                                            <h6 class="modern-course-name">{{ Str::limit($course->title, 30) }}</h6>
                                            <p class="modern-course-instructor">
                                                <i class="fas fa-user-tie me-1"></i>{{ Str::limit($course->instructor->name ?? 'N/A', 25) }}
                                            </p>
                                            @if($course->category)
                                            <span class="modern-course-category" style="background: {{ $course->category->color ?? '#003366' }}20; color: {{ $course->category->color ?? '#003366' }};">
                                                <i class="{{ $course->category->icon ?? 'fas fa-tag' }} me-1"></i>{{ Str::limit($course->category->name, 15) }}
                                            </span>
                                            @endif
                                        </div>
                                        <div class="modern-course-arrow">
                                            <i class="fas fa-chevron-right"></i>
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                            @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-book-open fa-2x mb-2"></i>
                                <p>Aucun cours trouvé</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-chart-pie me-2"></i>Paiements par statut
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="paymentsStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-panel admin-panel--visitors">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-chart-line me-2"></i>Statistiques des visiteurs
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="admin-stat-card admin-stat-card--visitor">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="admin-stat-card__label mb-1">Visiteurs uniques</p>
                                <p class="admin-stat-card__value mb-0">{{ number_format($stats['unique_visitors'] ?? 0) }}</p>
                            </div>
                            <div class="admin-stat-card__icon">
                                <i class="fas fa-user-friends"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="admin-stat-card admin-stat-card--visit">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="admin-stat-card__label mb-1">Total des visites</p>
                                <p class="admin-stat-card__value mb-0">{{ number_format($stats['total_visits'] ?? $stats['total_visitors'] ?? 0) }}</p>
                            </div>
                            <div class="admin-stat-card__icon">
                                <i class="fas fa-eye"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-users me-2"></i>Croissance des utilisateurs
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="usersChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-users me-2"></i>Visiteurs par jour (30 derniers jours)
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="visitorsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-mobile-alt me-2"></i>Par appareil
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="visitorsDeviceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-globe me-2"></i>Par navigateur
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="visitorsBrowserChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-flag me-2"></i>Visiteurs par pays
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="visitorsCountryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="admin-card shadow-sm h-100">
                        <div class="admin-card__header">
                            <h5 class="admin-card__title">
                                <i class="fas fa-map-marker-alt me-2"></i>Visiteurs par ville
                            </h5>
                        </div>
                        <div class="admin-card__body">
                            <div class="chart-container">
                                <canvas id="visitorsCityChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-panel admin-panel--stats-details">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-table me-2"></i>Statistiques détaillées
            </h3>
        </div>
        <div class="admin-panel__body admin-panel__body--stats-details">
            <div class="admin-card shadow-sm">
                <div class="admin-card__body admin-card__body--stats-details">
                    <div class="table-responsive table-responsive--stats-details">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Métrique</th>
                                    <th>Valeur</th>
                                    <th>Évolution</th>
                                    <th>Tendance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <i class="fas fa-users text-primary me-2"></i>
                                        Nouveaux utilisateurs ce mois
                                    </td>
                                    <td><strong>{{ $userGrowth->last()?->count ?? 0 }}</strong></td>
                                    <td>
                                        @if($userGrowth->count() > 1)
                                            @php
                                                $previous = $userGrowth->slice(-2, 1)->first()?->count ?? 0;
                                                $current = $userGrowth->last()?->count ?? 0;
                                                $change = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
                                            @endphp
                                            <span class="badge bg-{{ $change >= 0 ? 'success' : 'danger' }}">
                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 1) }}%
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($userGrowth->count() > 1)
                                            @php
                                                $change = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
                                            @endphp
                                            <i class="fas fa-arrow-{{ $change >= 0 ? 'up' : 'down' }} text-{{ $change >= 0 ? 'success' : 'danger' }}"></i>
                                        @else
                                            <i class="fas fa-minus text-secondary"></i>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fas fa-graduation-cap text-success me-2"></i>
                                        Cours publiés
                                    </td>
                                    <td><strong>{{ $courseStats->published_courses ?? 0 }}</strong></td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $courseStats->total_courses ?? 0 }} total
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-check text-success"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fas fa-user-graduate text-warning me-2"></i>
                                        Total des étudiants
                                    </td>
                                    <td><strong>{{ $courseStats->total_students ?? 0 }}</strong></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $stats['total_enrollments'] ?? 0 }} inscriptions
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-arrow-up text-success"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fas fa-star text-info me-2"></i>
                                        Note moyenne des cours
                                    </td>
                                    <td><strong>{{ number_format($courseStats->average_rating ?? 0, 1) }}/5</strong></td>
                                    <td>
                                        <div class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star{{ $i <= ($courseStats->average_rating ?? 0) ? '' : '-o' }}"></i>
                                            @endfor
                                        </div>
                                    </td>
                                    <td>
                                        <i class="fas fa-star text-warning"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fas fa-user-friends text-primary me-2"></i>
                                        Visiteurs uniques
                                    </td>
                                    <td><strong>{{ number_format($stats['unique_visitors'] ?? 0) }}</strong></td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ number_format($stats['total_visits'] ?? $stats['total_visitors'] ?? 0) }} visites totales
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-users text-primary"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fas fa-eye text-success me-2"></i>
                                        Total des visites
                                    </td>
                                    <td><strong>{{ number_format($stats['total_visits'] ?? $stats['total_visitors'] ?? 0) }}</strong></td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ number_format($stats['unique_visitors'] ?? 0) }} visiteurs uniques
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-chart-line text-success"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fas fa-calendar-day text-info me-2"></i>
                                        Visites aujourd'hui
                                    </td>
                                    <td><strong>{{ number_format($stats['visitors_today'] ?? 0) }}</strong></td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ number_format($stats['unique_visitors_today'] ?? 0) }} visiteurs uniques
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar-check text-info"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fas fa-calendar-week text-warning me-2"></i>
                                        Visites cette semaine
                                    </td>
                                    <td><strong>{{ number_format($stats['visitors_this_week'] ?? 0) }}</strong></td>
                                    <td>
                                        <span class="badge bg-warning">
                                            {{ number_format($stats['visitors_this_month'] ?? 0) }} ce mois
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-chart-bar text-warning"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                                        Visites ce mois
                                    </td>
                                    <td><strong>{{ number_format($stats['visitors_this_month'] ?? 0) }}</strong></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ number_format($stats['visitors_this_week'] ?? 0) }} cette semaine
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar text-primary"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
// Données pour les graphiques
const revenueData = @json($revenueByMonth ?? []);
const revenueByDayData = @json($revenueByDay ?? []);
const revenueByWeekData = @json($revenueByWeek ?? []);
const revenueByYearData = @json($revenueByYear ?? []);

// Données pour les revenus détaillés
const internalRevenueByMonthData = @json($internalRevenueByMonth ?? []);
const internalRevenueByDayData = @json($internalRevenueByDay ?? []);
const internalRevenueByWeekData = @json($internalRevenueByWeek ?? []);
const internalRevenueByYearData = @json($internalRevenueByYear ?? []);

const commissionsByMonthData = @json($commissionsByMonth ?? []);
const commissionsByDayData = @json($commissionsByDay ?? []);
const commissionsByWeekData = @json($commissionsByWeek ?? []);
const commissionsByYearData = @json($commissionsByYear ?? []);

const revenueByCategoryData = @json($revenueByCategory ?? []);
const revenueByCourseData = @json($revenueByCourse ?? []);
const revenueByInstructorData = @json($revenueByInstructor ?? []);
const userGrowthData = @json($userGrowth ?? []);

// Variables globales pour les graphiques
let revenueChartInstance = null;
let revenueBreakdownChartInstance = null;
let revenueByCategoryChartInstance = null;
let revenueByCourseChartInstance = null;
let revenueByInstructorChartInstance = null;
const categoryStats = @json($categoryStats ?? []);
const paymentsByMethod = @json($paymentsByMethod ?? []);
const paymentsByStatus = @json($paymentsByStatus ?? []);
const visitorsByDay = @json($visitorStats['visitors_by_day'] ?? []);
const uniqueVisitorsByDay = @json($visitorStats['unique_visitors_by_day'] ?? []);
const visitorsByDevice = @json($visitorStats['by_device'] ?? []);
const visitorsByBrowser = @json($visitorStats['by_browser'] ?? []);
const visitorsByOS = @json($visitorStats['by_os'] ?? []);
const visitorsByCountry = @json($visitorStats['by_country'] ?? []);
const visitorsByCity = @json($visitorStats['by_city'] ?? []);


// Fonction pour détecter la taille d'écran
function isMobile() {
    return window.innerWidth < 768;
}

function isTablet() {
    return window.innerWidth >= 768 && window.innerWidth < 992;
}

// Fonction pour formater les dates au format mois (Y-m -> "Jan 2024")
function formatMonthLabel(monthStr) {
    if (!monthStr) return '';
    
    // Convertir en string si ce n'est pas déjà le cas
    let str = String(monthStr).trim();
    
    // Si c'est vide après trim, retourner vide
    if (!str) return '';
    
    // Si c'est au format YYYY-MM, on le formate
    if (/^\d{4}-\d{2}$/.test(str)) {
        const [year, month] = str.split('-');
        const monthNum = parseInt(month, 10);
        if (monthNum >= 1 && monthNum <= 12) {
            const monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
            return monthNames[monthNum - 1] + ' ' + year;
        }
    }
    
    // Essayer de parser comme date si ce n'est pas au format attendu
    try {
        const date = new Date(str);
        if (!isNaN(date.getTime())) {
            const monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
            return monthNames[date.getMonth()] + ' ' + date.getFullYear();
        }
    } catch (e) {
        // Ignorer les erreurs de parsing
    }
    
    // Si c'est déjà formaté ou autre format, on retourne tel quel
    return str;
}

// Fonction pour formater les dates au format jour (Y-m-d -> "15 Jan")
function formatDayLabel(dateStr) {
    if (!dateStr) return '';
    
    // Convertir en string si ce n'est pas déjà le cas
    let str = String(dateStr).trim();
    
    // Si c'est vide après trim, retourner vide
    if (!str) return '';
    
    // Si c'est au format YYYY-MM-DD, on le formate
    if (/^\d{4}-\d{2}-\d{2}$/.test(str)) {
        const [year, month, day] = str.split('-');
        const monthNum = parseInt(month, 10);
        const dayNum = parseInt(day, 10);
        if (monthNum >= 1 && monthNum <= 12 && dayNum >= 1 && dayNum <= 31) {
            const monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
            return dayNum + ' ' + monthNames[monthNum - 1];
        }
    }
    
    // Essayer de parser comme date si ce n'est pas au format attendu
    try {
        const date = new Date(str);
        if (!isNaN(date.getTime())) {
            const monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
            return date.getDate() + ' ' + monthNames[date.getMonth()];
        }
    } catch (e) {
        // Ignorer les erreurs de parsing
    }
    
    // Si c'est déjà formaté ou autre format, on retourne tel quel
    return str;
}

// Fonction pour formater les labels de semaine
function formatWeekLabel(weekValue) {
    if (!weekValue) return '';
    const parts = weekValue.split('-');
    if (parts.length === 2) {
        return `S${parts[1]} ${parts[0]}`;
    }
    return weekValue;
}

// Fonction pour formater les labels d'année
function formatYearLabel(yearValue) {
    if (!yearValue) return '';
    return String(yearValue).trim();
}

// Fonction pour obtenir les données de revenus selon la période
function getRevenueDataByPeriod(period, startDate, endDate) {
    let data = [];
    let labels = [];
    
    switch(period) {
        case 'day':
            data = revenueByDayData;
            if (startDate && endDate) {
                data = data.filter(item => {
                    const itemDate = item.date || '';
                    return itemDate >= startDate && itemDate <= endDate;
                });
            }
            labels = data.map(item => formatDayLabel(item.date || ''));
            break;
        case 'week':
            data = revenueByWeekData;
            if (startDate && endDate) {
                data = data.filter(item => {
                    const weekValue = item.week || '';
                    const weekYear = weekValue.split('-')[0];
                    const weekNum = weekValue.split('-')[1];
                    // Approximation: comparer les années et semaines
                    return weekValue >= startDate && weekValue <= endDate;
                });
            }
            labels = data.map(item => formatWeekLabel(item.week || ''));
            break;
        case 'month':
            data = revenueData;
            if (startDate && endDate) {
                data = data.filter(item => {
                    const monthValue = item.month || '';
                    return monthValue >= startDate.substring(0, 7) && monthValue <= endDate.substring(0, 7);
                });
            }
            labels = data.map(item => formatMonthLabel(item.month || ''));
            break;
        case 'year':
            data = revenueByYearData;
            if (startDate && endDate) {
                data = data.filter(item => {
                    const yearValue = item.year || '';
                    return yearValue >= startDate.substring(0, 4) && yearValue <= endDate.substring(0, 4);
                });
            }
            labels = data.map(item => formatYearLabel(item.year || ''));
            break;
    }
    
    return { data, labels };
}

// Fonction pour obtenir les données de revenus détaillés par période
function getRevenueBreakdownDataByPeriod(period, startDate, endDate) {
    let internalData = [];
    let commissionsData = [];
    let allKeys = new Set();
    
    switch(period) {
        case 'day':
            internalData = [...internalRevenueByDayData];
            commissionsData = [...commissionsByDayData];
            if (startDate && endDate) {
                internalData = internalData.filter(item => {
                    const dateValue = item.date || '';
                    return dateValue >= startDate && dateValue <= endDate;
                });
                commissionsData = commissionsData.filter(item => {
                    const dateValue = item.date || '';
                    return dateValue >= startDate && dateValue <= endDate;
                });
            }
            internalData.forEach(item => allKeys.add(item.date || ''));
            commissionsData.forEach(item => allKeys.add(item.date || ''));
            break;
        case 'week':
            internalData = [...internalRevenueByWeekData];
            commissionsData = [...commissionsByWeekData];
            internalData.forEach(item => allKeys.add(item.week || ''));
            commissionsData.forEach(item => allKeys.add(item.week || ''));
            break;
        case 'month':
            internalData = [...internalRevenueByMonthData];
            commissionsData = [...commissionsByMonthData];
            if (startDate && endDate) {
                internalData = internalData.filter(item => {
                    const monthValue = item.month || '';
                    return monthValue >= startDate.substring(0, 7) && monthValue <= endDate.substring(0, 7);
                });
                commissionsData = commissionsData.filter(item => {
                    const monthValue = item.month || '';
                    return monthValue >= startDate.substring(0, 7) && monthValue <= endDate.substring(0, 7);
                });
            }
            internalData.forEach(item => allKeys.add(item.month || ''));
            commissionsData.forEach(item => allKeys.add(item.month || ''));
            break;
        case 'year':
            internalData = [...internalRevenueByYearData];
            commissionsData = [...commissionsByYearData];
            if (startDate && endDate) {
                internalData = internalData.filter(item => {
                    const yearValue = item.year || '';
                    return yearValue >= startDate.substring(0, 4) && yearValue <= endDate.substring(0, 4);
                });
                commissionsData = commissionsData.filter(item => {
                    const yearValue = item.year || '';
                    return yearValue >= startDate.substring(0, 4) && yearValue <= endDate.substring(0, 4);
                });
            }
            internalData.forEach(item => allKeys.add(item.year || ''));
            commissionsData.forEach(item => allKeys.add(item.year || ''));
            break;
    }
    
    // Trier les clés
    const sortedKeys = Array.from(allKeys).sort();
    
    // Créer des maps pour faciliter la recherche
    const internalMap = new Map();
    internalData.forEach(item => {
        const key = item.date || item.week || item.month || item.year || '';
        internalMap.set(key, parseFloat(item.revenue || 0));
    });
    
    const commissionsMap = new Map();
    commissionsData.forEach(item => {
        const key = item.date || item.week || item.month || item.year || '';
        commissionsMap.set(key, parseFloat(item.revenue || 0));
    });
    
    // Créer les labels et valeurs
    const labels = sortedKeys.map(key => {
        if (period === 'day') return formatDayLabel(key);
        if (period === 'week') return formatWeekLabel(key);
        if (period === 'month') return formatMonthLabel(key);
        if (period === 'year') return formatYearLabel(key);
        return key;
    });
    
    const internalValues = sortedKeys.map(key => internalMap.get(key) || 0);
    const commissionsValues = sortedKeys.map(key => commissionsMap.get(key) || 0);
    const totalValues = sortedKeys.map(key => (internalMap.get(key) || 0) + (commissionsMap.get(key) || 0));
    
    return { labels, internalValues, commissionsValues, totalValues };
}

// Fonction pour mettre à jour le graphique de décomposition des revenus
function updateRevenueBreakdownChart() {
    const period = document.getElementById('revenueBreakdownPeriodFilter').value;
    const startDate = document.getElementById('revenueBreakdownStartDate').value;
    const endDate = document.getElementById('revenueBreakdownEndDate').value;
    
    const { labels, internalValues, commissionsValues, totalValues } = getRevenueBreakdownDataByPeriod(period, startDate, endDate);
    
    const ctx = document.getElementById('revenueBreakdownChart');
    
    if (revenueBreakdownChartInstance) {
        revenueBreakdownChartInstance.destroy();
    }
    
    revenueBreakdownChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenus internes',
                    data: internalValues,
                    borderColor: '#003366',
                    backgroundColor: 'rgba(0, 51, 102, 0.1)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Commissions',
                    data: commissionsValues,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Revenu total',
                    data: totalValues,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4,
                    fill: false,
                    borderDash: [5, 5]
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR', {
                                style: 'currency',
                                currency: '{{ $baseCurrency ?? "USD" }}'
                            }).format(value);
                        }
                    }
                }
            }
        }
    });
}

// Initialiser les dates par défaut et les graphiques de revenus
document.addEventListener('DOMContentLoaded', function() {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setMonth(startDate.getMonth() - 6);
    
    const startDateInput = document.getElementById('revenueStartDate');
    const endDateInput = document.getElementById('revenueEndDate');
    const breakdownStartDateInput = document.getElementById('revenueBreakdownStartDate');
    const breakdownEndDateInput = document.getElementById('revenueBreakdownEndDate');
    
    if (startDateInput) {
        startDateInput.value = startDate.toISOString().split('T')[0];
    }
    if (endDateInput) {
        endDateInput.value = endDate.toISOString().split('T')[0];
    }
    if (breakdownStartDateInput) {
        breakdownStartDateInput.value = startDate.toISOString().split('T')[0];
    }
    if (breakdownEndDateInput) {
        breakdownEndDateInput.value = endDate.toISOString().split('T')[0];
    }
    
    // Initialiser les graphiques avec les données par défaut
    updateRevenueChart();
    updateRevenueBreakdownChart();
});

// Fonction pour mettre à jour le graphique de revenus
function updateRevenueChart() {
    const period = document.getElementById('revenuePeriodFilter').value;
    const startDate = document.getElementById('revenueStartDate').value;
    const endDate = document.getElementById('revenueEndDate').value;
    
    // Afficher un indicateur de chargement
    const revenueCtx = document.getElementById('revenueChart');
    const loadingText = revenueCtx.getContext('2d');
    
    // Faire une requête AJAX
    fetch('{{ route("admin.analytics.revenue-data") }}?period=' + period + '&start_date=' + startDate + '&end_date=' + endDate, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(result => {
        const data = result.data || [];
        let labels = [];
        
        switch(period) {
            case 'day':
                labels = data.map(item => formatDayLabel(item.date || ''));
                break;
            case 'week':
                labels = data.map(item => formatWeekLabel(item.week || ''));
                break;
            case 'month':
                labels = data.map(item => formatMonthLabel(item.month || ''));
                break;
            case 'year':
                labels = data.map(item => formatYearLabel(item.year || ''));
                break;
        }
        
        if (revenueChartInstance) {
            revenueChartInstance.destroy();
        }
        
        revenueChartInstance = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenus ({{ $baseCurrency ?? "USD" }})',
                    data: data.map(item => parseFloat(item.revenue || 0)),
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
                    x: {
                        ticks: {
                            font: {
                                size: isMobile() ? 10 : 12
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: isMobile() ? 10 : 12
                            },
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value);
                            }
                        }
                    }
                }
            }
        });
    })
    .catch(error => {
        console.error('Erreur lors du chargement des données:', error);
        // En cas d'erreur, utiliser les données locales
        const { data, labels } = getRevenueDataByPeriod(period, startDate, endDate);
        
        if (revenueChartInstance) {
            revenueChartInstance.destroy();
        }
        
        revenueChartInstance = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenus ({{ $baseCurrency ?? "USD" }})',
                    data: data.map(item => parseFloat(item.revenue || 0)),
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
                    x: {
                        ticks: {
                            font: {
                                size: isMobile() ? 10 : 12
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: isMobile() ? 10 : 12
                            },
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value);
                            }
                        }
                    }
                }
            }
        });
    });
}

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

// Fonction pour mettre à jour le graphique par catégorie
function updateCategoryChart() {
    const days = document.getElementById('categoryPeriodFilter').value;
    
    fetch('{{ route("admin.analytics.revenue-by-category") }}?days=' + days, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(result => {
        const data = result.data || [];
        const labels = data.map(item => item.name || 'Sans catégorie');
        const values = data.map(item => parseFloat(item.revenue || 0));
        const colors = generateColors(data.length);
        
        const ctx = document.getElementById('revenueByCategoryChart');
        
        if (revenueByCategoryChartInstance) {
            revenueByCategoryChartInstance.destroy();
        }
        
        revenueByCategoryChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenus ({{ $baseCurrency ?? "USD" }})',
                data: values,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: isMobile() ? 10 : 12
                        },
                        padding: 10,
                        boxWidth: 12
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = new Intl.NumberFormat('fr-FR', {
                                style: 'currency',
                                currency: '{{ $baseCurrency ?? "USD" }}'
                            }).format(context.parsed);
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    })
    .catch(error => {
        console.error('Erreur lors du chargement des données:', error);
    });
}

// Fonction pour mettre à jour le graphique par cours
function updateCourseChart() {
    const days = document.getElementById('coursePeriodFilter').value;
    
    fetch('{{ route("admin.analytics.revenue-by-course") }}?days=' + days, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(result => {
        const data = result.data || [];
        const labels = data.map(item => {
            const title = item.title || 'Sans titre';
            return title.length > 20 ? title.substring(0, 20) + '...' : title;
        });
        const values = data.map(item => parseFloat(item.revenue || 0));
        
        const ctx = document.getElementById('revenueByCourseChart');
        
        if (revenueByCourseChartInstance) {
            revenueByCourseChartInstance.destroy();
        }
        
        revenueByCourseChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenus ({{ $baseCurrency ?? "USD" }})',
                data: values,
                backgroundColor: '#17a2b8',
                borderColor: '#17a2b8',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: isMobile() ? 10 : 12
                        },
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR').format(value);
                        }
                    }
                },
                y: {
                    ticks: {
                        font: {
                            size: isMobile() ? 9 : 11
                        }
                    }
                }
            }
        }
    });
    })
    .catch(error => {
        console.error('Erreur lors du chargement des données:', error);
    });
}

// Fonction pour mettre à jour le graphique par formateur
function updateInstructorChart() {
    const days = document.getElementById('instructorPeriodFilter').value;
    
    fetch('{{ route("admin.analytics.revenue-by-instructor") }}?days=' + days, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(result => {
        const data = result.data || [];
        const labels = data.map(item => {
            const name = item.name || 'Sans nom';
            return name.length > 20 ? name.substring(0, 20) + '...' : name;
        });
        const values = data.map(item => parseFloat(item.revenue || 0));
        
        const ctx = document.getElementById('revenueByInstructorChart');
        
        if (revenueByInstructorChartInstance) {
            revenueByInstructorChartInstance.destroy();
        }
        
        revenueByInstructorChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenus ({{ $baseCurrency ?? "USD" }})',
                data: values,
                backgroundColor: '#6f42c1',
                borderColor: '#6f42c1',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: isMobile() ? 10 : 12
                        },
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR').format(value);
                        }
                    }
                },
                y: {
                    ticks: {
                        font: {
                            size: isMobile() ? 9 : 11
                        }
                    }
                }
            }
        }
    });
    })
    .catch(error => {
        console.error('Erreur lors du chargement des données:', error);
    });
}

// Initialiser les graphiques
updateCategoryChart();
updateCourseChart();
updateInstructorChart();

// Graphique de croissance des utilisateurs
const usersCtx = document.getElementById('usersChart').getContext('2d');
const userGrowthLabels = userGrowthData.map(item => {
    const monthValue = item.month || item.date || item.created_at || '';
    return formatMonthLabel(monthValue) || monthValue;
});
new Chart(usersCtx, {
    type: 'bar',
    data: {
        labels: userGrowthLabels,
        datasets: [{
            label: 'Nouveaux utilisateurs',
            data: userGrowthData.map(item => item.count || 0),
            backgroundColor: '#28a745',
            borderColor: '#28a745',
            borderWidth: 1
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
            x: {
                ticks: {
                    font: {
                        size: isMobile() ? 10 : 12
                    }
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    font: {
                        size: isMobile() ? 10 : 12
                    }
                }
            }
        }
    }
});

// Graphique des catégories
const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
new Chart(categoriesCtx, {
    type: 'doughnut',
    data: {
        labels: categoryStats.map(item => item.name),
        datasets: [{
            data: categoryStats.map(item => item.courses_count),
            backgroundColor: [
                '#003366',
                '#ffcc33',
                '#28a745',
                '#dc3545',
                '#17a2b8',
                '#6f42c1',
                '#fd7e14',
                '#20c997'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: isMobile() ? 10 : 12,
                    padding: isMobile() ? 8 : 12,
                    font: {
                        size: isMobile() ? 10 : 12
                    }
                }
            }
        }
    }
});

// Paiements par méthode
const methodCtx = document.getElementById('paymentsMethodChart').getContext('2d');
new Chart(methodCtx, {
    type: 'doughnut',
    data: {
        labels: paymentsByMethod.map(p => (p.payment_method || 'inconnu').toUpperCase()),
        datasets: [{
            data: paymentsByMethod.map(p => p.count),
            backgroundColor: ['#003366','#ffcc33','#28a745','#dc3545','#17a2b8','#6f42c1']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: isMobile() ? 10 : 12,
                    padding: isMobile() ? 8 : 12,
                    font: {
                        size: isMobile() ? 10 : 12
                    }
                }
            }
        }
    }
});

// Paiements par statut
const statusCtx = document.getElementById('paymentsStatusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: paymentsByStatus.map(p => (p.status || 'inconnu').toUpperCase()),
        datasets: [{
            data: paymentsByStatus.map(p => p.count),
            backgroundColor: ['#28a745','#ffc107','#dc3545','#6c757d']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: isMobile() ? 10 : 12,
                    padding: isMobile() ? 8 : 12,
                    font: {
                        size: isMobile() ? 10 : 12
                    }
                }
            }
        }
    }
});

// Graphique des visiteurs par jour
const visitorsCtx = document.getElementById('visitorsChart').getContext('2d');

// Créer un objet pour mapper les dates aux valeurs
const visitorsMap = {};
visitorsByDay.forEach(item => {
    if (item.date) {
        visitorsMap[item.date] = { total: item.count || 0, unique: 0 };
    }
});

uniqueVisitorsByDay.forEach(item => {
    if (item.date) {
        if (!visitorsMap[item.date]) {
            visitorsMap[item.date] = { total: 0, unique: 0 };
        }
        visitorsMap[item.date].unique = item.count || 0;
    }
});

// Trier les dates
const sortedDates = Object.keys(visitorsMap).sort();
const visitorLabels = sortedDates.map(date => {
    return formatDayLabel(date) || date;
});

new Chart(visitorsCtx, {
    type: 'line',
    data: {
        labels: visitorLabels,
        datasets: [{
            label: 'Visiteurs totaux',
            data: sortedDates.map(date => visitorsMap[date].total),
            borderColor: '#003366',
            backgroundColor: 'rgba(0, 51, 102, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Visiteurs uniques',
            data: sortedDates.map(date => visitorsMap[date].unique),
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    boxWidth: isMobile() ? 10 : 12,
                    padding: isMobile() ? 8 : 12,
                    font: {
                        size: isMobile() ? 10 : 12
                    }
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    font: {
                        size: isMobile() ? 10 : 12
                    }
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    font: {
                        size: isMobile() ? 10 : 12
                    }
                }
            }
        }
    }
});

// Graphique des visiteurs par appareil
const deviceCtx = document.getElementById('visitorsDeviceChart').getContext('2d');
new Chart(deviceCtx, {
    type: 'doughnut',
    data: {
        labels: visitorsByDevice.map(item => (item.device_type || 'inconnu').charAt(0).toUpperCase() + (item.device_type || 'inconnu').slice(1)),
        datasets: [{
            data: visitorsByDevice.map(item => item.count),
            backgroundColor: ['#003366', '#ffcc33', '#28a745', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: isMobile() ? 8 : 12,
                    padding: isMobile() ? 6 : 8,
                    font: {
                        size: isMobile() ? 9 : 11
                    }
                }
            }
        }
    }
});

// Graphique des visiteurs par navigateur
const browserCtx = document.getElementById('visitorsBrowserChart').getContext('2d');
new Chart(browserCtx, {
    type: 'doughnut',
    data: {
        labels: visitorsByBrowser.map(item => item.browser || 'inconnu'),
        datasets: [{
            data: visitorsByBrowser.map(item => item.count),
            backgroundColor: ['#003366', '#ffcc33', '#28a745', '#dc3545', '#17a2b8', '#6f42c1']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: isMobile() ? 8 : 12,
                    padding: isMobile() ? 6 : 8,
                    font: {
                        size: isMobile() ? 9 : 11
                    }
                }
            }
        }
    }
});

// Graphique des visiteurs par pays
const countryCtx = document.getElementById('visitorsCountryChart');
if (countryCtx) {
    new Chart(countryCtx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: visitorsByCountry.map(item => item.country || 'Inconnu'),
            datasets: [{
                label: 'Visiteurs',
                data: visitorsByCountry.map(item => item.count),
                backgroundColor: '#003366',
                borderColor: '#003366',
                borderWidth: 1
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
                x: {
                    ticks: {
                        font: {
                            size: isMobile() ? 10 : 12
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: isMobile() ? 10 : 12
                        }
                    }
                }
            },
            indexAxis: 'y'
        }
    });
}

// Graphique des visiteurs par ville
const cityCtx = document.getElementById('visitorsCityChart');
if (cityCtx) {
    new Chart(cityCtx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: visitorsByCity.map(item => (item.city || 'Inconnu') + (item.country ? ' (' + item.country + ')' : '')),
            datasets: [{
                label: 'Visiteurs',
                data: visitorsByCity.map(item => item.count),
                backgroundColor: '#ffcc33',
                borderColor: '#ffcc33',
                borderWidth: 1
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
                x: {
                    ticks: {
                        font: {
                            size: isMobile() ? 9 : 12
                        },
                        maxRotation: isMobile() ? 45 : 0,
                        minRotation: isMobile() ? 45 : 0
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: isMobile() ? 10 : 12
                        }
                    }
                }
            },
            indexAxis: 'y'
        }
    });
}

// Gestionnaire de redimensionnement pour mettre à jour les graphiques
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        // Forcer la mise à jour de tous les graphiques
        Chart.helpers.each(Chart.instances, function(instance) {
            instance.resize();
        });
    }, 250);
});

// Fonction pour rafraîchir les analytics
function refreshAnalytics() {
    const btn = document.getElementById('refreshAnalyticsBtn');
    const icon = document.getElementById('refreshIcon');
    const text = document.getElementById('refreshText');
    
    // Désactiver le bouton et afficher l'animation de chargement
    btn.disabled = true;
    icon.classList.add('fa-spin');
    text.textContent = 'Actualisation...';
    
    // Recharger la page après un court délai pour voir l'animation
    setTimeout(function() {
        window.location.reload();
    }, 300);
}

</script>
@endpush

@push('styles')
<style>
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

/* Réduire l'espace au-dessus du contenu pour la carte "Évolution des revenus" sur desktop */
@media (min-width: 992px) {
    .admin-card__header .admin-card__title.mb-1 {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-card__header {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }
}

.admin-card__title {
    margin: 0;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
}

/* Bouton filtre avec le même design que les datepickers */
.admin-card__header button.form-control-sm[title="Filtrer"] {
    min-width: 140px !important;
    width: auto !important;
    height: calc(1.5em + 0.75rem + 2px) !important;
    padding: 0.375rem 0.75rem !important;
    background-color: #fff !important;
    border: 1px solid #ced4da !important;
    border-radius: 0.375rem !important;
    color: #495057 !important;
    font-size: 0.875rem !important;
    line-height: 1.5 !important;
    cursor: pointer !important;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.admin-card__header button.form-control-sm[title="Filtrer"]:hover {
    border-color: #86b7fe !important;
    background-color: #fff !important;
}

.admin-card__header button.form-control-sm[title="Filtrer"]:focus {
    border-color: #86b7fe !important;
    outline: 0 !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

.admin-card__header button.form-control-sm[title="Filtrer"] i {
    font-size: 0.875rem !important;
    margin: 0 !important;
    line-height: 1.5 !important;
    color: #495057 !important;
}

.admin-card__header button.form-control-sm[title="Filtrer"] .filter-text {
    font-size: 0.875rem !important;
    color: #495057 !important;
    white-space: nowrap;
}

/* Masquer le texte sur tablette et mobile */
@media (max-width: 991.98px) {
    .admin-card__header button.form-control-sm[title="Filtrer"] .filter-text {
        display: none !important;
    }
    
    .admin-card__header button.form-control-sm[title="Filtrer"] {
        min-width: 80px !important;
        justify-content: center !important;
    }
}

.admin-card__body {
    padding: 1.25rem;
}

/* Conteneurs pour les graphiques */
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

/* Premier conteneur de statistiques - 2 colonnes sur desktop */
@media (min-width: 992px) {
    .admin-panel--main .admin-stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

.chart-container canvas {
    max-width: 100%;
    max-height: 100%;
    width: 100% !important;
    height: 100% !important;
    display: block;
}

/* Assurer que tous les canvas s'adaptent correctement */
.admin-card__body canvas {
    max-width: 100%;
    max-height: 100%;
    width: 100% !important;
    height: 100% !important;
    display: block;
}

/* Styles responsives pour les graphiques */
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
    
    /* Ajouter un espacement pour la section des visiteurs */
    .admin-panel--visitors .admin-panel__body {
        padding-top: 1.25rem !important;
    }
    
    /* Espacement supplémentaire pour la première ligne de cartes */
    .admin-panel--visitors .admin-panel__body > .row:first-child {
        margin-top: 0.75rem !important;
    }
    
    .admin-panel__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-panel__header h3 {
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    
    .admin-stats-grid {
        gap: 0.5rem !important;
    }
    
    .admin-stat-card {
        padding: 0.75rem 0.875rem !important;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.5rem;
        --bs-gutter-y: 0.5rem;
    }
    
    .admin-panel__body .row.g-3 {
        --bs-gutter-x: 0.375rem;
        --bs-gutter-y: 0.375rem;
    }
    
    .admin-panel__body .row.mb-4 {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-panel__body .row.mt-2 {
        margin-top: 0.375rem !important;
    }
    
    .admin-stat-card--visitor,
    .admin-stat-card--visit {
        padding: 0.75rem 1rem !important;
    }
    
    .admin-card__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-card__body {
        padding: 0.5rem;
    }
    
    .chart-container {
        min-height: 200px;
        max-height: 280px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .chart-container canvas {
        width: 100% !important;
        height: 100% !important;
    }
}

@media (max-width: 767.98px) {
    /* Réduire encore plus les paddings et margins sur mobile */
    .admin-panel {
        margin-bottom: 0.75rem;
    }
    
    /* Réduire la taille des filtres de revenus pour qu'ils tiennent sur une ligne */
    .admin-card__header .d-flex.gap-2 {
        gap: 0.25rem !important;
        flex-wrap: nowrap !important;
    }
    
    .admin-card__header .admin-card__title {
        font-size: 0.9rem !important;
        margin-bottom: 0.5rem !important;
    }
    
    .admin-card__header .admin-card__title i {
        font-size: 0.85rem !important;
    }
    
    /* Uniformiser tous les combobox de filtres sur mobile */
    #revenuePeriodFilter,
    #revenueBreakdownPeriodFilter,
    #categoryPeriodFilter,
    #coursePeriodFilter,
    #instructorPeriodFilter,
    #revenueStartDate,
    #revenueEndDate,
    #revenueBreakdownStartDate,
    #revenueBreakdownEndDate {
        min-width: 75px !important;
        max-width: 85px !important;
        font-size: 0.7rem !important;
        padding: 0.2rem 0.3rem !important;
        flex-shrink: 1;
    }
    
    #revenuePeriodFilter,
    #revenueBreakdownPeriodFilter,
    #categoryPeriodFilter,
    #coursePeriodFilter,
    #instructorPeriodFilter {
        min-width: 65px !important;
        max-width: 75px !important;
    }
    
    #revenueStartDate,
    #revenueEndDate,
    #revenueBreakdownStartDate,
    #revenueBreakdownEndDate {
        min-width: 80px !important;
        max-width: 90px !important;
    }
    
    /* Uniformiser tous les combobox de filtres sur tablette */
    #revenuePeriodFilter,
    #revenueBreakdownPeriodFilter,
    #categoryPeriodFilter,
    #coursePeriodFilter,
    #instructorPeriodFilter,
    #revenueStartDate,
    #revenueEndDate,
    #revenueBreakdownStartDate,
    #revenueBreakdownEndDate {
        min-width: 75px !important;
        max-width: 85px !important;
        font-size: 0.7rem !important;
        padding: 0.2rem 0.3rem !important;
        flex-shrink: 1;
    }
    
    #revenuePeriodFilter,
    #revenueBreakdownPeriodFilter,
    #categoryPeriodFilter,
    #coursePeriodFilter,
    #instructorPeriodFilter {
        min-width: 65px !important;
        max-width: 75px !important;
    }
    
    #revenueStartDate,
    #revenueEndDate,
    #revenueBreakdownStartDate,
    #revenueBreakdownEndDate {
        min-width: 80px !important;
        max-width: 90px !important;
    }
    
    .admin-card__header .btn-sm.p-1 {
        font-size: 0.7rem !important;
        padding: 0.2rem !important;
        white-space: nowrap;
        flex-shrink: 0;
        min-width: 24px !important;
        width: 24px !important;
        height: 24px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .admin-card__header .btn-sm.p-1 i {
        font-size: 0.7rem !important;
        margin: 0 !important;
        line-height: 1;
    }
    
    /* Padding uniquement pour la première section principale */
    .admin-panel--main .admin-panel__body {
        padding: 0.75rem !important;
    }
    
    /* Pas de padding pour les autres sections */
    .admin-panel:not(.admin-panel--main) .admin-panel__body {
        padding: 0 !important;
    }
    
    /* Ajouter un espacement pour la section des visiteurs */
    .admin-panel--visitors .admin-panel__body {
        padding-top: 1.25rem !important;
    }
    
    /* Espacement supplémentaire pour la première ligne de cartes */
    .admin-panel--visitors .admin-panel__body > .row:first-child {
        margin-top: 0.75rem !important;
    }
    
    .admin-panel__header {
        padding: 0.375rem 0.5rem;
    }
    
    .admin-panel__header h3 {
        font-size: 0.95rem;
        margin-bottom: 0.125rem;
    }
    
    .admin-stats-grid {
        gap: 0.375rem !important;
    }
    
    .admin-stat-card {
        padding: 0.5rem 0.625rem !important;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.375rem;
        --bs-gutter-y: 0.375rem;
    }
    
    .admin-panel__body .row.g-3 {
        --bs-gutter-x: 0.25rem;
        --bs-gutter-y: 0.25rem;
    }
    
    .admin-panel__body .row.mb-4 {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-panel__body .row.mt-2 {
        margin-top: 0.375rem !important;
    }
    
    .admin-stat-card--visitor,
    .admin-stat-card--visit {
        padding: 0.5rem 0.75rem !important;
    }
    
    .admin-stat-card--visitor .admin-stat-card__icon,
    .admin-stat-card--visit .admin-stat-card__icon {
        width: 35px !important;
        height: 35px !important;
        font-size: 1.1rem !important;
    }
    
    .admin-card__header {
        padding: 0.5rem 0.625rem;
    }
    
    /* Bouton filtre sur mobile - même taille que les datepickers */
    .admin-card__header button.form-control-sm[title="Filtrer"] {
        min-width: 80px !important;
        padding: 0.2rem 0.3rem !important;
        font-size: 0.7rem !important;
        justify-content: center !important;
    }
    
    .admin-card__header button.form-control-sm[title="Filtrer"] .filter-text {
        display: none !important;
    }
    
    .admin-card__header button.form-control-sm[title="Filtrer"] i {
        font-size: 0.7rem !important;
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
    
    /* Graphiques en donut plus compacts sur mobile */
    .admin-card--compact .admin-card__body--compact .chart-container {
        min-height: 140px;
        max-height: 180px;
    }
    
    .admin-card--compact .admin-card__body--compact canvas {
        min-height: 140px;
        max-height: 180px;
    }
    
    .admin-card--compact .admin-card__header--compact {
        padding: 0.375rem 0.5rem;
    }
    
    .admin-card--compact .admin-card__body--compact {
        padding: 0.25rem 0.375rem 0.375rem;
    }
}

/* Styles pour les cartes compactes (appareil et navigateur) */
.admin-card--compact .admin-card__header--compact {
    padding: 0.75rem 1rem;
    min-height: auto;
}

.admin-card--compact .admin-card__title--compact {
    font-size: 0.875rem;
    font-weight: 600;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
    width: 100%;
}

.admin-card--compact .admin-card__body--compact {
    padding: 0.5rem 0.75rem 0.75rem;
    min-height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.admin-card--compact .admin-card__body--compact canvas {
    max-width: 100% !important;
    max-height: 100% !important;
    width: 100% !important;
    height: auto !important;
}

/* Styles pour les cours populaires - Design horizontal comme les catégories */
.modern-courses-container {
    position: relative;
    margin: 0 -15px;
    padding: 0 15px;
    overflow: hidden;
}

.modern-courses-wrapper {
    display: flex;
    overflow-x: auto;
    gap: 1rem;
    padding: 1rem 0;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 51, 102, 0.3) transparent;
    scroll-snap-type: x mandatory;
}

.modern-courses-wrapper::-webkit-scrollbar {
    height: 6px;
}

.modern-courses-wrapper::-webkit-scrollbar-track {
    background: transparent;
}

.modern-courses-wrapper::-webkit-scrollbar-thumb {
    background: rgba(0, 51, 102, 0.3);
    border-radius: 3px;
}

.modern-courses-wrapper::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 51, 102, 0.5);
}

.modern-course-item {
    flex: 0 0 auto;
    width: 300px;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1rem;
    background: white;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    scroll-snap-align: start;
    position: relative;
}

.modern-course-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 51, 102, 0.15);
    border-color: #003366;
    text-decoration: none;
}

.modern-course-thumbnail {
    position: relative;
    width: 100%;
    height: 160px;
    border-radius: 8px;
    overflow: hidden;
    background: #f8fafc;
}

.modern-course-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.modern-course-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #003366, #004080);
    color: white;
    font-size: 2.5rem;
}

.modern-course-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: rgba(0, 51, 102, 0.9);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.modern-course-content {
    flex: 1;
    min-width: 0;
}

.modern-course-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #003366;
    margin: 0 0 0.5rem 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.modern-course-instructor {
    font-size: 0.8rem;
    color: #6c757d;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.modern-course-category {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

.modern-course-arrow {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    color: #6c757d;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.modern-course-item:hover .modern-course-arrow {
    color: #003366;
    transform: translateX(4px);
}

@media (max-width: 767.98px) {
    .modern-courses-container {
        margin: 0 -0.75rem;
        padding: 0 0.75rem;
    }
    
    .modern-courses-wrapper {
        gap: 0.75rem;
        padding: 0.75rem 0;
    }
    
    .modern-course-item {
        width: 260px;
        padding: 0.875rem;
    }
    
    .modern-course-thumbnail {
        height: 140px;
    }
    
    /* Réduire le padding pour les statistiques détaillées sur mobile */
    .admin-panel--stats-details .admin-panel__body--stats-details {
        padding: 0.5rem;
    }
    
    .admin-panel--stats-details .admin-card {
        margin: 0;
    }
    
    .admin-panel--stats-details .admin-card__body--stats-details {
        padding: 0.5rem;
    }
    
    .table-responsive--stats-details {
        margin: 0;
        padding: 0;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .admin-panel--stats-details .table {
        font-size: 0.8rem;
        margin-bottom: 0;
        min-width: 100%;
        width: max-content;
    }
    
    .admin-panel--stats-details .table th,
    .admin-panel--stats-details .table td {
        padding: 0.5rem 0.5rem;
        white-space: nowrap;
        vertical-align: middle;
    }
    
    .admin-panel--stats-details .table th {
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .admin-panel--stats-details .table td {
        font-size: 0.8rem;
    }
    
    .admin-panel--stats-details .table td i {
        font-size: 0.75rem;
    }
    
    .admin-panel--stats-details .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
}

/* Styles pour les cartes d'indicateurs de visiteurs */
.admin-stat-card--visitor,
.admin-stat-card--visit {
    background: linear-gradient(135deg, rgba(0, 51, 102, 0.07) 0%, rgba(0, 51, 102, 0.15) 100%);
    border-radius: 1rem;
    padding: 1.25rem 1.5rem;
    border: 1px solid rgba(0, 51, 102, 0.1);
    transition: all 0.3s ease;
}

.admin-stat-card--visitor:hover,
.admin-stat-card--visit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 51, 102, 0.15);
}

.admin-stat-card--visitor .admin-stat-card__icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: linear-gradient(135deg, #003366, #004080);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.admin-stat-card--visit .admin-stat-card__icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

@media (max-width: 767.98px) {
    .admin-stat-card--visitor,
    .admin-stat-card--visit {
        padding: 1rem 1.25rem;
    }
    
    .admin-stat-card--visitor .admin-stat-card__icon,
    .admin-stat-card--visit .admin-stat-card__icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }
    
    .admin-stat-card--visitor .admin-stat-card__value,
    .admin-stat-card--visit .admin-stat-card__value {
        font-size: 1.25rem;
    }
}
}

.admin-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 1rem;
}

.admin-list__item {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-list__item:hover {
    background: #eef2ff;
}

.admin-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.82rem;
}

.admin-chip--info {
    background: rgba(59, 130, 246, 0.12);
    color: #1d4ed8;
}

.admin-chip--primary {
    background: rgba(14, 165, 233, 0.12);
    color: #0369a1;
}

.table thead th {
    background: #f1f5f9;
    font-weight: 600;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
}

.revenue-detail-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #fff;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.revenue-detail-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.revenue-detail-card__icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.revenue-detail-card__content {
    flex: 1;
}

.revenue-detail-card__label {
    font-size: 0.875rem;
    color: #6c757d;
    margin: 0 0 0.25rem 0;
    font-weight: 500;
}

.revenue-detail-card__value {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: #212529;
}

@media (max-width: 768px) {
    .revenue-detail-card {
        padding: 0.75rem;
    }

    .revenue-detail-card__icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .revenue-detail-card__value {
        font-size: 1.25rem;
    }
}
</style>
@endpush

