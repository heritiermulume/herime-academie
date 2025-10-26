@extends('layouts.app')

@section('title', 'Gestion des Statistiques')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Gestion des Statistiques</h1>
            <p class="text-muted">Surveillez et gérez les statistiques des cours en temps réel</p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="recalculateAllStats()">
                <i class="fas fa-sync-alt me-1"></i>Recalculer toutes les statistiques
            </button>
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
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_courses']) }}</h3>
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
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['published_courses']) }}</h3>
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
                                <i class="fas fa-users text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total inscriptions</h6>
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
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-star text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total avis</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_reviews']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Courses by Students -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Cours les plus populaires</h5>
                    <p class="text-muted small mb-0">Classés par nombre d'étudiants inscrits</p>
                </div>
                <div class="card-body p-0">
                    @if($topCourses->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($topCourses as $course)
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
                                                <i class="fas fa-users me-1"></i>{{ number_format($course->enrollments_count) }} étudiants
                                            </small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button class="btn btn-outline-primary btn-sm" onclick="recalculateCourseStats({{ $course->id }})">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">Aucun cours trouvé</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Rated Courses -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Cours les mieux notés</h5>
                    <p class="text-muted small mb-0">Classés par note moyenne</p>
                </div>
                <div class="card-body p-0">
                    @if($topRatedCourses->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($topRatedCourses as $course)
                            <div class="list-group-item border-0 py-3">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $course->thumbnail ? Storage::url($course->thumbnail) : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=60&h=40&fit=crop' }}" 
                                         alt="{{ $course->title }}" class="rounded me-3" style="width: 60px; height: 40px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">{{ Str::limit($course->title, 40) }}</h6>
                                        <p class="text-muted small mb-1">{{ $course->instructor->name }}</p>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary me-2">{{ $course->category->name }}</span>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-star text-warning me-1"></i>
                                                <span class="fw-bold">{{ number_format($course->reviews_avg_rating, 1) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button class="btn btn-outline-primary btn-sm" onclick="recalculateCourseStats({{ $course->id }})">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">Aucun cours noté trouvé</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mb-0">Recalcul des statistiques en cours...</p>
            </div>
        </div>
    </div>
</div>

<script>
function recalculateCourseStats(courseId) {
    const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
    modal.show();
    
    fetch(`/admin/courses/${courseId}/recalculate-stats`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        modal.hide();
        if (data.success) {
            showNotification('Statistiques recalculées avec succès', 'success');
            // Optionnel: recharger la page pour voir les nouvelles données
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Erreur lors du recalcul', 'error');
        }
    })
    .catch(error => {
        modal.hide();
        showNotification('Erreur de connexion', 'error');
    });
}

function recalculateAllStats() {
    if (confirm('Êtes-vous sûr de vouloir recalculer toutes les statistiques ? Cette opération peut prendre du temps.')) {
        const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
        modal.show();
        
        fetch('/admin/statistics/recalculate-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            modal.hide();
            if (data.success) {
                showNotification('Toutes les statistiques ont été recalculées', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.message || 'Erreur lors du recalcul', 'error');
            }
        })
        .catch(error => {
            modal.hide();
            showNotification('Erreur de connexion', 'error');
        });
    }
}

function showNotification(message, type) {
    // Utiliser le système de notifications existant ou créer une alerte simple
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alert.style.top = '20px';
    alert.style.right = '20px';
    alert.style.zIndex = '9999';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>
@endsection
