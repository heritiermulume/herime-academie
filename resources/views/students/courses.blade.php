@extends('layouts.app')

@section('title', 'Mes cours - Tableau de bord étudiant')

@push('styles')
<style>
.student-courses-hero {
    background: linear-gradient(135deg, #003366 0%, #004080 100%);
    color: white;
    padding: 2rem 0 3rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    margin-top: 0;
    width: 100%;
    max-width: 100vw;
}

.student-courses-page {
    width: 100%;
    max-width: 100vw;
    overflow-x: hidden;
    margin: 0;
    padding: 0;
}

.student-courses-page .container-fluid {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}

@media (min-width: 768px) {
    .student-courses-page .container-fluid {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
}

/* Card spacing */
.student-courses-page .card {
    margin-bottom: 1rem;
}

@media (max-width: 575.98px) {
    .student-courses-page .card {
        border-radius: 0.5rem;
    }
}

.student-courses-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
    opacity: 0.1;
}

.student-courses-hero .container {
    position: relative;
    z-index: 1;
    max-width: 100%;
    padding-left: 1rem;
    padding-right: 1rem;
}

.btn-back-courses {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.3);
    display: inline-flex;
    align-items: center;
    margin-bottom: 1rem;
}

.btn-back-courses:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    transform: translateX(-3px);
    border-color: rgba(255, 255, 255, 0.5);
}

.courses-title-hero {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.3;
    margin-bottom: 0.5rem;
    color: white;
    word-wrap: break-word;
}

.courses-subtitle-hero {
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 0;
}

@media (max-width: 767.98px) {
    .student-courses-hero {
        padding: 1.5rem 0 2rem;
        margin-bottom: 1.5rem;
    }
    
    .courses-title-hero {
        font-size: 1.5rem;
    }
    
    .courses-subtitle-hero {
        font-size: 0.875rem;
    }
    
    .btn-back-courses {
        padding: 0.4rem 0.875rem;
        font-size: 0.8rem;
        margin-bottom: 0.75rem;
    }
}

@media (max-width: 575.98px) {
    .student-courses-hero {
        padding: 1.25rem 0 1.75rem;
    }
    
    .courses-title-hero {
        font-size: 1.25rem;
    }
    
    .courses-subtitle-hero {
        font-size: 0.8rem;
    }
}

/* Responsive List Items */
@media (max-width: 767.98px) {
    .list-group-item {
        padding: 1rem 0.75rem !important;
    }
    
    .list-group-item .row {
        margin: 0;
    }
    
    .list-group-item .col-12 {
        margin-bottom: 0.75rem;
    }
    
    .list-group-item .col-12:last-child {
        margin-bottom: 0;
    }
    
    .list-group-item h6 {
        font-size: 0.95rem;
        line-height: 1.4;
    }
    
    .list-group-item .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
    
    .list-group-item small {
        font-size: 0.75rem;
    }
    
    .list-group-item .btn {
        font-size: 0.8rem;
        padding: 0.375rem 0.75rem;
    }
    
    .card-header h5 {
        font-size: 1rem;
    }
    
    .card-header {
        padding: 0.75rem 0.75rem !important;
    }
    
    .card-footer {
        padding: 0.75rem 0.75rem !important;
    }
}

@media (max-width: 575.98px) {
    .list-group-item {
        padding: 0.875rem 0.5rem !important;
    }
    
    .list-group-item h6 {
        font-size: 0.9rem;
    }
    
    .list-group-item img,
    .list-group-item > div > div > div:first-child > div {
        height: 50px !important;
        max-width: 80px !important;
        width: 80px !important;
    }
    
    .list-group-item .progress {
        height: 6px !important;
    }
    
    .card-header {
        padding: 0.625rem 0.5rem !important;
    }
    
    .card-header h5 {
        font-size: 0.9rem;
    }
    
    .card-footer {
        padding: 0.625rem 0.5rem !important;
    }
    
    .student-courses-page .card {
        margin-left: 0.5rem;
        margin-right: 0.5rem;
    }
    
    .container-fluid {
        padding-left: 0.25rem !important;
        padding-right: 0.25rem !important;
    }
}
</style>
@endpush

@section('content')
<div class="student-courses-page container-fluid p-0">
    <!-- Hero Header -->
    <section class="student-courses-hero">
        <div class="container">
            <a href="{{ route('student.dashboard') }}" class="btn-back-courses">
                <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
            </a>
            <h1 class="courses-title-hero">
                <i class="fas fa-book me-2"></i>Mes cours
            </h1>
            <p class="courses-subtitle-hero">Tous mes cours inscrits</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container-fluid py-4">
        <!-- Liste des cours -->
        <div class="card border-0 shadow-sm mx-2 mx-md-3">
            <div class="card-header bg-white border-0 py-3 px-3 px-md-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Tous mes cours ({{ $enrollments->total() }})</h5>
                </div>
            </div>
            <div class="card-body p-0">
            @if($enrollments->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($enrollments as $enrollment)
                    <div class="list-group-item border-0 py-3 px-2 px-md-3">
                        <div class="row align-items-center g-3">
                            <!-- Image/Thumbnail -->
                            <div class="col-12 col-md-2 text-center text-md-start">
                                @if($enrollment->course->thumbnail)
                                    <img src="{{ $enrollment->course->thumbnail }}" 
                                         alt="{{ $enrollment->course->title }}" 
                                         class="img-fluid rounded" 
                                         style="height: 60px; width: 100%; max-width: 100px; object-fit: cover;">
                                @else
                                    @php $initials = collect(explode(' ', trim($enrollment->course->title)))->take(2)->map(fn($w)=>mb_substr($w,0,1))->implode(''); @endphp
                                    <div class="d-flex align-items-center justify-content-center rounded mx-auto" style="height:60px; width: 100px; background:#e9eef6; color:#003366; font-weight:700;">
                                        {{ $initials }}
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Informations du cours -->
                            <div class="col-12 col-md-6">
                                <h6 class="mb-1 fw-bold">
                                    <a href="{{ route('courses.show', $enrollment->course->slug) }}" class="text-decoration-none text-dark">
                                        {{ $enrollment->course->title }}
                                    </a>
                                </h6>
                                <p class="text-muted small mb-1">{{ $enrollment->course->instructor->name }}</p>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <span class="badge bg-primary me-2">{{ $enrollment->course->category->name }}</span>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ $enrollment->course->duration }} min
                                    </small>
                                    @if($enrollment->course->is_downloadable && isset($enrollment->course->user_downloads_count))
                                        <small class="text-info">
                                            <i class="fas fa-download me-1"></i>{{ $enrollment->course->user_downloads_count }} téléchargement(s)
                                        </small>
                                    @endif
                                </div>
                            </div>
                            
                            @php
                                $course = $enrollment->course;
                                $isDownloadableAndPurchased = false;
                                if ($course->is_downloadable) {
                                    $hasPurchased = false;
                                    if (!$course->is_free && $enrollment->order_id) {
                                        $hasPurchased = $enrollment->order && $enrollment->order->status === 'paid';
                                    } elseif ($course->is_free) {
                                        $hasPurchased = true;
                                    } else {
                                        $hasPurchased = \App\Models\Order::where('user_id', auth()->id())
                                            ->where('status', 'paid')
                                            ->whereHas('orderItems', function($query) use ($course) {
                                                $query->where('course_id', $course->id);
                                            })
                                            ->exists();
                                    }
                                    $isDownloadableAndPurchased = $hasPurchased;
                                }
                            @endphp
                            
                            <!-- Progression ou statut téléchargeable -->
                            @if(!$isDownloadableAndPurchased)
                            <div class="col-12 col-md-2">
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: {{ $enrollment->progress }}%" 
                                         aria-valuenow="{{ $enrollment->progress }}" 
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted d-block text-center text-md-start">{{ $enrollment->progress }}% terminé</small>
                            </div>
                            @else
                            <div class="col-12 col-md-2">
                                <small class="text-muted d-block text-center text-md-start">
                                    <i class="fas fa-download me-1"></i>Cours téléchargeable
                                </small>
                            </div>
                            @endif
                            
                            <!-- Bouton d'action -->
                            <div class="col-12 col-md-2 text-center text-md-end">
                                @php
                                    $course = $enrollment->course;
                                    $hasPurchased = false;
                                    
                                    // Vérifier si l'utilisateur a payé (pour les cours payants)
                                    if (!$course->is_free && $enrollment->order_id) {
                                        $hasPurchased = $enrollment->order && $enrollment->order->status === 'paid';
                                    } elseif ($course->is_free) {
                                        // Pour les cours gratuits, considérer comme "payé" si inscrit
                                        $hasPurchased = true;
                                    } else {
                                        // Vérifier via les commandes
                                        $hasPurchased = \App\Models\Order::where('user_id', auth()->id())
                                            ->where('status', 'paid')
                                            ->whereHas('orderItems', function($query) use ($course) {
                                                $query->where('course_id', $course->id);
                                            })
                                            ->exists();
                                    }
                                    
                                    // Si cours téléchargeable ET acheté, afficher uniquement le bouton télécharger
                                    $isDownloadableAndPurchased = $course->is_downloadable && $hasPurchased;
                                    
                                    // Déterminer le texte du bouton selon la progression
                                    $buttonText = $enrollment->progress > 0 ? 'Continuer' : 'Commencer';
                                @endphp
                                
                                @if($isDownloadableAndPurchased)
                                    <a href="{{ route('courses.download', $course->slug) }}" 
                                       class="btn btn-success btn-sm w-100 w-md-auto">
                                        <i class="fas fa-download me-1"></i>Télécharger
                                    </a>
                                @else
                                    <a href="{{ route('student.courses.learn', $course->slug) }}" 
                                       class="btn btn-primary btn-sm w-100 w-md-auto">
                                        <i class="fas fa-play me-1"></i>{{ $buttonText }} l'apprentissage
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="card-footer bg-white border-0 py-3 px-3 px-md-4">
                    <div class="d-flex justify-content-center">
                        {{ $enrollments->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun cours inscrit</h5>
                    <p class="text-muted">Commencez votre parcours d'apprentissage dès maintenant</p>
                    <a href="{{ route('courses.index') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Découvrir des cours
                    </a>
                </div>
            @endif
            </div>
        </div>
    </div>
</div>
@endsection
