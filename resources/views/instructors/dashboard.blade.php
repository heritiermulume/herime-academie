@extends('layouts.dashboard')

@php($instructor = auth()->user())
@php
    use Illuminate\Support\Str;
@endphp
@include('instructors.partials.dashboard-context', ['instructor' => $instructor])
@php
    $coursesIndexUrl = Route::has('instructor.courses.list')
        ? route('instructor.courses.list')
        : url('/instructor/courses/list');
@endphp

@section('title', 'Tableau de bord formateur')
@section('dashboard-title', 'Tableau de bord formateur')
@section('dashboard-subtitle', 'Suivez vos cours, vos inscriptions et vos revenus en un coup d’œil')
@section('dashboard-actions')
    <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau cours
    </a>
@endsection

@section('dashboard-content')
    <section class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div>
                        <h2 class="h4 fw-semibold mb-2 text-white">Bienvenue, {{ $instructor->name }} !</h2>
                        <p class="mb-0 text-white-50">Votre impact grandit : continuez à inspirer vos apprenants.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $coursesIndexUrl }}" class="btn btn-light text-primary fw-semibold">
                            <i class="fas fa-book-open me-2"></i>Gérer mes cours
                        </a>
                        <a href="{{ route('instructor.students') }}" class="btn btn-outline-light">
                            <i class="fas fa-users me-2"></i>Voir mes étudiants
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="insight-card shadow-sm h-100">
                <div class="insight-card__icon bg-primary-subtle text-primary">
                    <i class="fas fa-book"></i>
                </div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Total des cours</p>
                    <h3 class="insight-card__value">{{ $stats['total_courses'] }}</h3>
                    <p class="insight-card__supplement">{{ $stats['published_courses'] }} cours publiés</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="insight-card shadow-sm h-100">
                <div class="insight-card__icon bg-success-subtle text-success">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Total étudiants</p>
                    <h3 class="insight-card__value">{{ number_format($stats['total_students']) }}</h3>
                    <p class="insight-card__supplement">Inscriptions cumulées</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="insight-card shadow-sm h-100">
                <div class="insight-card__icon bg-info-subtle text-info">
                    <i class="fas fa-users"></i>
                </div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Étudiants actifs</p>
                    <h3 class="insight-card__value">{{ number_format($stats['active_students'] ?? $stats['total_students']) }}</h3>
                    <p class="insight-card__supplement">Inscrits sur les 30 derniers jours</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="insight-card shadow-sm h-100">
                <div class="insight-card__icon bg-warning-subtle text-warning">
                    <i class="fas fa-star"></i>
                </div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Satisfaction moyenne</p>
                    <h3 class="insight-card__value">{{ number_format($stats['average_rating'] ?? 0, 1) }}/5</h3>
                    <p class="insight-card__supplement">{{ number_format($stats['total_reviews'] ?? 0) }} avis reçus</p>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-4">
        <div class="col-12 col-xl-8 d-flex flex-column gap-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header card-header-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0 text-white fw-semibold">Mes cours récents</h5>
                        <small class="text-white-50">Gestion rapide des derniers cours publiés</small>
                    </div>
                    <a href="{{ $coursesIndexUrl }}" class="btn btn-light btn-sm text-primary fw-semibold">
                        Voir tous <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recent_courses->count() > 0)
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Cours</th>
                                        <th>Catégorie</th>
                                        <th>Statut</th>
                                        <th>Étudiants</th>
                                        <th>Note</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_courses as $course)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="{{ $course->thumbnail_url ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=60&h=40&fit=crop' }}" alt="{{ $course->title }}" class="rounded" style="width: 64px; height: 48px; object-fit: cover;">
                                                    <div>
                                                        <a href="{{ route('instructor.courses.edit', $course) }}" class="fw-semibold text-decoration-none text-dark">
                                                            {{ Str::limit($course->title, 60) }}
                                                        </a>
                                                        <div class="text-muted small">Créé le {{ $course->created_at->format('d/m/Y') }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-primary">{{ $course->category->name }}</span></td>
                                            <td>
                                                @if($course->is_published)
                                                    <span class="badge bg-success">Publié</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Brouillon</span>
                                                @endif
                                            </td>
                                            <td><strong>{{ number_format($course->stats['total_students'] ?? 0) }}</strong></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1">
                                                    <i class="fas fa-star text-warning"></i>
                                                    <span>{{ number_format($course->stats['average_rating'] ?? 0, 1) }}</span>
                                                    <small class="text-muted">({{ $course->stats['total_reviews'] ?? 0 }})</small>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-outline-secondary btn-sm" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-outline-primary btn-sm" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('instructor.courses.lessons', $course) }}" class="btn btn-outline-info btn-sm" title="Gérer les leçons">
                                                        <i class="fas fa-list"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun cours créé pour l’instant</h5>
                            <p class="text-muted">Publiez votre premier cours pour le voir apparaître ici.</p>
                            <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Créer un cours
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header card-header-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0 text-white fw-semibold">Inscriptions récentes</h5>
                        <small class="text-white-50">Les dix dernières inscriptions à vos cours</small>
                    </div>
                    <a href="{{ route('instructor.students') }}" class="btn btn-light btn-sm text-primary fw-semibold">
                        Voir tous
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recent_enrollments->count() > 0)
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Étudiant</th>
                                        <th>Cours</th>
                                        <th>Inscription</th>
                                        <th class="text-end">Progression</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_enrollments as $enrollment)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="{{ $enrollment->user->avatar_url }}" alt="{{ $enrollment->user->name }}" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">
                                                    <div>
                                                        <div class="fw-semibold">{{ $enrollment->user->name }}</div>
                                                        <div class="text-muted small">{{ $enrollment->user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ Str::limit($enrollment->course->title, 60) }}</td>
                                            <td>{{ $enrollment->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-end">
                                                <span class="badge bg-primary-subtle text-primary fw-semibold">{{ $enrollment->progress }}%</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">Pas encore d’inscriptions récentes.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4 d-flex flex-column gap-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0 fw-semibold">Actions rapides</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Créer un cours
                        </a>
                        <a href="{{ $coursesIndexUrl }}" class="btn btn-outline-primary">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Gérer mes cours
                        </a>
                        <a href="{{ route('instructor.students') }}" class="btn btn-outline-primary">
                            <i class="fas fa-users me-2"></i>Mes étudiants
                        </a>
                        <a href="{{ route('instructor.analytics') }}" class="btn btn-outline-primary">
                            <i class="fas fa-chart-line me-2"></i>Consulter les analytics
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0 fw-semibold">Cours les plus populaires</h6>
                </div>
                <div class="card-body p-0">
                    @if($stats['top_courses'] ?? false)
                        <div class="list-group list-group-flush">
                            @foreach($stats['top_courses'] as $topCourse)
                                <div class="list-group-item py-3 d-flex align-items-start gap-3">
                                    <div class="flex-grow-1">
                                        <h6 class="fw-semibold mb-1">{{ Str::limit($topCourse->title, 42) }}</h6>
                                        <div class="d-flex justify-content-between text-muted small">
                                            <span><i class="fas fa-users me-1"></i>{{ $topCourse->enrollments_count }} étudiants</span>
                                            <span><i class="fas fa-star text-warning me-1"></i>{{ number_format($topCourse->reviews_avg_rating ?? 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('instructor.courses.edit', $topCourse) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-pen"></i></a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">Publiez plusieurs cours pour voir vos statistiques ici.</div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0 fw-semibold">Conseils pédagogiques</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                        <li class="d-flex gap-3">
                            <span class="badge rounded-pill bg-primary-subtle text-primary"><i class="fas fa-lightbulb"></i></span>
                            <div>
                                <p class="fw-semibold mb-1">Actualisez vos cours</p>
                                <p class="text-muted small mb-0">Ajoutez un nouveau module ou un quiz pour maintenir l’engagement.</p>
                            </div>
                        </li>
                        <li class="d-flex gap-3">
                            <span class="badge rounded-pill bg-success-subtle text-success"><i class="fas fa-comments"></i></span>
                            <div>
                                <p class="fw-semibold mb-1">Répondez aux avis</p>
                                <p class="text-muted small mb-0">Remerciez vos étudiants et prenez en compte leurs retours.</p>
                            </div>
                        </li>
                        <li class="d-flex gap-3">
                            <span class="badge rounded-pill bg-warning-subtle text-warning"><i class="fas fa-bullhorn"></i></span>
                            <div>
                                <p class="fw-semibold mb-1">Faites connaître vos nouveautés</p>
                                <p class="text-muted small mb-0">Utilisez les annonces pour informer vos étudiants des mises à jour.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .insight-card {
        display: flex;
        gap: 1rem;
        padding: 1.5rem;
        border-radius: 1.25rem;
        background: #ffffff;
        border: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .insight-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 45px -30px rgba(0, 51, 102, 0.45);
    }
    .insight-card__icon {
        width: 60px;
        height: 60px;
        border-radius: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .insight-card__label {
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        margin-bottom: 0.35rem;
    }
    .insight-card__value {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.1rem;
        color: #0b1f3a;
    }
    .insight-card__supplement {
        margin: 0;
        color: #7b8a9f;
        font-size: 0.875rem;
    }
</style>
@endpush