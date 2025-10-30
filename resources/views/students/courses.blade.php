@extends('layouts.app')

@section('title', 'Mes Cours - Herime Académie')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 text-white rounded-3 p-3" style="background-color:#003366;">
        <div class="d-flex align-items-center">
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-light me-3" title="Tableau de bord">
                <i class="fas fa-tachometer-alt"></i>
            </a>
            <div>
                <h1 class="h3 mb-1 text-white">
                    <i class="fas fa-book-open me-2 text-white"></i>
                    Mes Cours
                </h1>
                <p class="mb-0 text-white-50">Retrouvez tous vos cours inscrits</p>
            </div>
        </div>
    </div>

    @if($enrollments->count() > 0)
        <!-- Course Grid -->
        <div class="row g-4">
            @foreach($enrollments as $enrollment)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 hover-shadow">
                        <a href="{{ route('student.courses.learn', $enrollment->course->slug) }}" class="text-decoration-none">
                            @if($enrollment->course->thumbnail)
                                <img src="{{ $enrollment->course->thumbnail }}" 
                                     alt="{{ $enrollment->course->title }}" 
                                     class="card-img-top" 
                                     style="height: 180px; object-fit: cover;">
                            @else
                                @php $ci = collect(explode(' ', trim($enrollment->course->title)))->take(2)->map(fn($w)=>mb_substr($w,0,1))->implode(''); @endphp
                                <div class="card-img-top d-flex align-items-center justify-content-center" style="height:180px;background:#e9eef6;color:#003366;font-weight:700;font-size:2rem;">
                                    {{ $ci }}
                                </div>
                            @endif
                        </a>
                        
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                <a href="{{ route('courses.show', $enrollment->course->slug) }}" class="text-decoration-none">
                                    <h5 class="card-title text-dark mb-2">{{ $enrollment->course->title }}</h5>
                                </a>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-user-tie me-1"></i>{{ $enrollment->course->instructor->name }}
                                </p>
                            </div>
                            
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        @if($enrollment->status === 'completed')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>Terminé
                                            </span>
                                        @elseif($enrollment->status === 'active')
                                            <span class="badge bg-primary">
                                                <i class="fas fa-play-circle me-1"></i>En cours
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-pause-circle me-1"></i>En pause
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-muted small">
                                        <i class="fas fa-clock me-1"></i>{{ $enrollment->course->duration }}h
                                    </span>
                                </div>
                                
                                <a href="{{ route('student.courses.learn', $enrollment->course->slug) }}" class="btn btn-primary w-100">
                                    <i class="fas fa-play me-2"></i>
                                    {{ $enrollment->status === 'completed' ? 'Revoir' : 'Continuer' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $enrollments->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-5">
            <i class="fas fa-book-open fa-4x text-muted mb-4"></i>
            <h3 class="text-muted mb-3">Aucun cours inscrit</h3>
            <p class="text-muted mb-4">Commencez votre parcours d'apprentissage en explorant nos formations !</p>
            <a href="{{ route('courses.index') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-search me-2"></i>Explorer les cours
            </a>
        </div>
    @endif
</div>

@push('styles')
<style>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.card-img-top {
    border-radius: 8px 8px 0 0;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    line-height: 1.4;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.badge {
    font-weight: 500;
    padding: 6px 12px;
}

.btn-primary {
    background-color: #003366;
    border-color: #003366;
    border-radius: 8px;
    font-weight: 600;
}

.btn-primary:hover {
    background-color: #004080;
    border-color: #004080;
}
</style>
@endpush
@endsection

