@extends('layouts.app')

@section('title', $instructor->name . ' - Formateur - Herime Academie')
@section('description', $instructor->bio ?: 'Découvrez les cours de ' . $instructor->name . ' sur Herime Academie.')

@section('content')
@php
use Illuminate\Support\Facades\Storage;
@endphp

<!-- Instructor Profile Header -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-4 text-center mb-4 mb-lg-0" data-aos="fade-right">
                <div class="instructor-avatar-large">
                    <img src="{{ $instructor->avatar ? $instructor->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($instructor->name) . '&background=003366&color=fff&size=200' }}" 
                         alt="{{ $instructor->name }}" class="rounded-circle">
                </div>
            </div>
            <div class="col-lg-8" data-aos="fade-left">
                <h1 class="display-5 fw-bold mb-3">{{ $instructor->name }}</h1>
                @if($instructor->bio)
                    <p class="lead text-muted mb-4">{{ $instructor->bio }}</p>
                @endif
                
                <div class="row mb-4">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center">
                            <div class="h4 fw-bold text-primary">{{ $instructor->courses_count }}</div>
                            <small class="text-muted">Cours</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center">
                            <div class="h4 fw-bold text-success">{{ $courses->sum('stats.total_students') }}</div>
                            <small class="text-muted">Étudiants</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center">
                            <div class="h4 fw-bold text-warning">{{ number_format($courses->avg('stats.average_rating'), 1) }}</div>
                            <small class="text-muted">Note moyenne</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center">
                            <div class="h4 fw-bold text-info">{{ $courses->sum('stats.total_duration') }}</div>
                            <small class="text-muted">Minutes</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3">
                    @if($instructor->website)
                        <a href="{{ $instructor->website }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-globe me-2"></i>Site web
                        </a>
                    @endif
                    @if($instructor->linkedin)
                        <a href="{{ $instructor->linkedin }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fab fa-linkedin me-2"></i>LinkedIn
                        </a>
                    @endif
                    @if($instructor->twitter)
                        <a href="{{ $instructor->twitter }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fab fa-twitter me-2"></i>Twitter
                        </a>
                    @endif
                    @if($instructor->youtube)
                        <a href="{{ $instructor->youtube }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fab fa-youtube me-2"></i>YouTube
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Instructor's Courses -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title text-center mb-5" data-aos="fade-up">Cours de {{ $instructor->name }}</h2>
        
        @if($courses->count() > 0)
            <div class="row g-3">
                @foreach($courses as $course)
                <div class="col-lg-4 col-md-6 col-sm-6" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    <div class="course-card">
                        <div class="course-thumbnail" style="background-image: url('{{ $course->thumbnail_url ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80' }}')">
                            @if($course->is_featured)
                            <span class="course-badge">En vedette</span>
                            @endif
                            @if($course->sale_discount_percentage)
                            <span class="course-badge" style="background: #e74c3c; top: 3rem;">-{{ $course->sale_discount_percentage }}%</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary">{{ $course->category->name }}</span>
                                <div class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star{{ $i <= ($course->stats['average_rating'] ?? 0) ? '' : '-o' }}"></i>
                                    @endfor
                                    <small class="text-muted ms-1">({{ $course->stats['total_reviews'] ?? 0 }})</small>
                                </div>
                            </div>
                            <h5 class="card-title">{{ $course->title }}</h5>
                            <p class="card-text text-muted">{{ Str::limit($course->short_description, 80) }}</p>
                            
                            @if($course->show_students_count && isset($course->stats['total_students']))
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-users me-1"></i>
                                    {{ number_format($course->stats['total_students'], 0, ',', ' ') }} 
                                    {{ $course->stats['total_students'] > 1 ? 'étudiants inscrits' : 'étudiant inscrit' }}
                                </small>
                            </div>
                            @endif
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    @if($course->is_sale_active && $course->active_sale_price !== null)
                                        <span class="course-price">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->active_sale_price) }}</span>
                                        <span class="course-price-old ms-2">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</span>
                                    @else
                                        <span class="course-price">{{ $course->is_free ? 'Gratuit' : \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</span>
                                    @endif
                                </div>
                                @if($course->show_students_count)
                                <small class="text-muted">
                                    <i class="fas fa-users me-1"></i>{{ number_format($course->stats['total_students'] ?? 0, 0, ',', ' ') }} 
                                    {{ ($course->stats['total_students'] ?? 0) > 1 ? 'étudiants' : 'étudiant' }}
                                </small>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>{{ $course->stats['total_duration'] ?? 0 }} min
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-play-circle me-1"></i>{{ $course->stats['total_lessons'] ?? 0 }} leçons
                                </small>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-primary w-100">
                                    Voir le cours
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-5">
                {{ $courses->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <h3>Aucun cours disponible</h3>
                <p class="text-muted">Ce formateur n'a pas encore publié de cours.</p>
            </div>
        @endif
    </div>
</section>
@endsection

@push('styles')
<style>
    .instructor-avatar-large img {
        width: 200px;
        height: 200px;
        object-fit: cover;
        border: 6px solid var(--secondary-color);
    }
    
    .course-card {
        border: none;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
    }
    
    .course-thumbnail {
        height: 200px;
        background-size: cover;
        background-position: center;
        position: relative;
    }
    
    .course-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: var(--secondary-color);
        color: var(--text-dark);
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 2;
    }
    
    .course-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-color);
    }
    
    .course-price-old {
        text-decoration: line-through;
        color: var(--text-light);
        font-size: 1rem;
    }
</style>
@endpush
