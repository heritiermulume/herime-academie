@extends('layouts.app')

@section('title', 'Tableau de bord formateur - Herime Academie')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 fw-bold mb-1">Tableau de bord formateur</h1>
                    <p class="text-muted mb-0">Gérez vos cours et suivez vos performances</p>
                </div>
                <div>
                    <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Créer un cours
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
                                <i class="fas fa-book text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total des cours</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_courses'] }}</h3>
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
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Cours publiés</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['published_courses'] }}</h3>
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
                                <i class="fas fa-users text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total étudiants</h6>
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
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-dollar-sign text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Revenus totaux</h6>
                            <h3 class="mb-0 fw-bold">${{ number_format($stats['total_earnings'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Courses -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Mes cours récents</h5>
                        <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline-primary btn-sm">
                            Voir tous <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recent_courses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Cours</th>
                                        <th>Catégorie</th>
                                        <th>Statut</th>
                                        <th>Étudiants</th>
                                        <th>Note</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_courses as $course)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $course->thumbnail ? Storage::url($course->thumbnail) : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=60&h=40&fit=crop' }}" 
                                                     alt="{{ $course->title }}" class="rounded me-3" style="width: 60px; height: 40px; object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-1 fw-bold">{{ Str::limit($course->title, 40) }}</h6>
                                                    <small class="text-muted">{{ $course->created_at->format('d/m/Y') }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $course->category->name }}</span>
                                        </td>
                                        <td>
                                            @if($course->is_published)
                                                <span class="badge bg-success">Publié</span>
                                            @else
                                                <span class="badge bg-warning">Brouillon</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ number_format($course->stats['total_students'] ?? 0) }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-star text-warning me-1"></i>
                                                <span>{{ number_format($course->stats['average_rating'] ?? 0, 1) }}</span>
                                                <small class="text-muted ms-1">({{ $course->stats['total_reviews'] ?? 0 }})</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('courses.show', $course->slug) }}">
                                                        <i class="fas fa-eye me-2"></i>Voir
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="{{ route('instructor.courses.edit', $course->id) }}">
                                                        <i class="fas fa-edit me-2"></i>Modifier
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="{{ route('instructor.courses.lessons', $course->id) }}">
                                                        <i class="fas fa-list me-2"></i>Leçons
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    @if($course->is_published)
                                                        <li><a class="dropdown-item text-warning" href="#" onclick="unpublishCourse({{ $course->id }})">
                                                            <i class="fas fa-eye-slash me-2"></i>Dépublier
                                                        </a></li>
                                                    @else
                                                        <li><a class="dropdown-item text-success" href="#" onclick="publishCourse({{ $course->id }})">
                                                            <i class="fas fa-eye me-2"></i>Publier
                                                        </a></li>
                                                    @endif
                                                </ul>
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
                            <h5 class="text-muted">Aucun cours créé</h5>
                            <p class="text-muted">Commencez par créer votre premier cours</p>
                            <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Créer un cours
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Recent Enrollments -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Inscriptions récentes</h5>
                        <a href="{{ route('instructor.students') }}" class="btn btn-outline-primary btn-sm">
                            Voir tous
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recent_enrollments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recent_enrollments as $enrollment)
                            <div class="list-group-item border-0 py-3">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $enrollment->user->avatar ? $enrollment->user->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($enrollment->user->name) . '&background=003366&color=fff' }}" 
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

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Créer un cours
                        </a>
                        <a href="{{ route('instructor.analytics') }}" class="btn btn-outline-primary">
                            <i class="fas fa-chart-bar me-2"></i>Voir les analytics
                        </a>
                        <a href="{{ route('instructor.students') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-users me-2"></i>Mes étudiants
                        </a>
                        <a href="{{ route('messages.create') }}" class="btn btn-outline-info">
                            <i class="fas fa-envelope me-2"></i>Envoyer un message
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function publishCourse(courseId) {
    if (confirm('Êtes-vous sûr de vouloir publier ce cours ?')) {
        fetch(`/instructor/courses/${courseId}/publish`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue.');
        });
    }
}

function unpublishCourse(courseId) {
    if (confirm('Êtes-vous sûr de vouloir dépublier ce cours ?')) {
        fetch(`/instructor/courses/${courseId}/unpublish`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue.');
        });
    }
}
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

.dropdown-toggle::after {
    margin-left: 0.5em;
}
</style>
@endpush