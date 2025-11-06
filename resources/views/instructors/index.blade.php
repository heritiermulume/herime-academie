@extends('layouts.app')

@section('title', 'Nos Formateurs - Herime Academie')
@section('description', 'Découvrez nos formateurs experts qui partagent leurs connaissances et expériences pour vous aider à réussir.')

@section('content')
<!-- Instructors Header -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h1 class="display-5 fw-bold mb-3">Nos Formateurs</h1>
                <p class="lead text-muted">Découvrez nos formateurs experts qui partagent leurs connaissances et expériences pour vous aider à réussir dans votre domaine.</p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <div class="d-flex gap-2">
                    <span class="badge bg-primary fs-6">{{ $instructors->total() }} formateurs</span>
                    <span class="badge bg-success fs-6">Experts certifiés</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Instructors Grid -->
<section class="py-5">
    <div class="container">
        @if($instructors->count() > 0)
            <div class="row">
                @foreach($instructors as $instructor)
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    <div class="card instructor-card h-100">
                        <div class="card-body text-center">
                            <div class="instructor-avatar mb-3">
                                <img src="{{ $instructor->avatar ? $instructor->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($instructor->name) . '&background=003366&color=fff&size=120' }}" 
                                     alt="{{ $instructor->name }}" class="rounded-circle">
                            </div>
                            <h5 class="card-title">{{ $instructor->name }}</h5>
                            @if($instructor->bio)
                                <p class="card-text text-muted">{{ Str::limit($instructor->bio, 100) }}</p>
                            @endif
                            <div class="d-flex justify-content-center gap-3 mb-3">
                                @if($instructor->website)
                                    <a href="{{ $instructor->website }}" target="_blank" class="text-primary">
                                        <i class="fas fa-globe fa-lg"></i>
                                    </a>
                                @endif
                                @if($instructor->linkedin)
                                    <a href="{{ $instructor->linkedin }}" target="_blank" class="text-primary">
                                        <i class="fab fa-linkedin fa-lg"></i>
                                    </a>
                                @endif
                                @if($instructor->twitter)
                                    <a href="{{ $instructor->twitter }}" target="_blank" class="text-primary">
                                        <i class="fab fa-twitter fa-lg"></i>
                                    </a>
                                @endif
                                @if($instructor->youtube)
                                    <a href="{{ $instructor->youtube }}" target="_blank" class="text-primary">
                                        <i class="fab fa-youtube fa-lg"></i>
                                    </a>
                                @endif
                            </div>
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <div class="fw-bold text-primary">{{ $instructor->courses_count }}</div>
                                    <small class="text-muted">Cours</small>
                                </div>
                                <div class="col-6">
                                    <div class="fw-bold text-success">{{ $instructor->courses->sum('students_count') }}</div>
                                    <small class="text-muted">Étudiants</small>
                                </div>
                            </div>
                            <a href="{{ route('instructors.show', $instructor) }}" class="btn btn-primary">
                                Voir le profil
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-5">
                {{ $instructors->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                <h3>Aucun formateur trouvé</h3>
                <p class="text-muted">Il n'y a pas encore de formateurs disponibles.</p>
            </div>
        @endif
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up">
                <h2 class="display-6 fw-bold mb-4">Vous voulez devenir formateur ?</h2>
                <p class="lead mb-4">
                    Partagez vos connaissances et aidez d'autres personnes à réussir. Rejoignez notre communauté de formateurs experts.
                </p>
                @auth
                    @if(auth()->user()->role !== 'instructor')
                        <a href="{{ route('instructor-application.index') }}" class="btn btn-warning btn-lg">
                            <i class="fas fa-rocket me-2"></i>Devenir formateur
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-warning btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Se connecter pour postuler
                    </a>
                @endauth
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .instructor-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
    }
    
    .instructor-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
    }
    
    .instructor-avatar img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border: 4px solid var(--secondary-color);
    }
</style>
@endpush

@php
use Illuminate\Support\Facades\Storage;
@endphp
