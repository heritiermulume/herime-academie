@extends('layouts.app')

@section('title', 'Nos Prestataires - Herime Academie')
@section('description', 'Découvrez nos prestataires experts qui partagent leurs connaissances et expériences pour vous aider à réussir.')

@section('content')
<!-- Providers Header -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h1 class="display-5 fw-bold mb-3">Nos Prestataires</h1>
                <p class="lead text-muted">Découvrez nos prestataires experts qui partagent leurs connaissances et expériences pour vous aider à réussir dans votre domaine.</p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <div class="d-flex gap-2">
                    <span class="badge bg-primary fs-6">{{ $providers->total() }} prestataires</span>
                    <span class="badge bg-success fs-6">Experts certifiés</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Providers Grid -->
<section class="py-5">
    <div class="container">
        @if($providers->count() > 0)
            <div class="row">
                @foreach($providers as $provider)
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    <div class="card provider-card h-100">
                        <div class="card-body text-center">
                            <div class="provider-avatar mb-3">
                                <div style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; margin: 0 auto; border: 4px solid #e9ecef;">
                                    <img src="{{ $provider->avatar_url }}" 
                                         alt="{{ $provider->name }}" 
                                         style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                </div>
                            </div>
                            <h5 class="card-title">{{ $provider->name }}</h5>
                            @if($provider->bio)
                                <p class="card-text text-muted">{{ Str::limit($provider->bio, 100) }}</p>
                            @endif
                            <div class="d-flex justify-content-center gap-3 mb-3">
                                @if($provider->website)
                                    <a href="{{ $provider->website }}" target="_blank" class="text-primary">
                                        <i class="fas fa-globe fa-lg"></i>
                                    </a>
                                @endif
                                @if($provider->linkedin)
                                    <a href="{{ $provider->linkedin }}" target="_blank" class="text-primary">
                                        <i class="fab fa-linkedin fa-lg"></i>
                                    </a>
                                @endif
                                @if($provider->twitter)
                                    <a href="{{ $provider->twitter }}" target="_blank" class="text-primary">
                                        <i class="fab fa-twitter fa-lg"></i>
                                    </a>
                                @endif
                                @if($provider->youtube)
                                    <a href="{{ $provider->youtube }}" target="_blank" class="text-primary">
                                        <i class="fab fa-youtube fa-lg"></i>
                                    </a>
                                @endif
                            </div>
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <div class="fw-bold text-primary">{{ $provider->courses_count }}</div>
                                    <small class="text-muted">Contenus</small>
                                </div>
                                <div class="col-6">
                                    <div class="fw-bold text-success">{{ $provider->courses->sum('customers_count') }}</div>
                                    <small class="text-muted">Participants</small>
                                </div>
                            </div>
                            <a href="{{ route('providers.show', $provider) }}" class="btn btn-primary">
                                Voir le profil
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-5">
                {{ $providers->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                <h3>Aucun prestataire trouvé</h3>
                <p class="text-muted">Il n'y a pas encore de prestataires disponibles.</p>
            </div>
        @endif
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up">
                <h2 class="display-6 fw-bold mb-4">Vous voulez devenir prestataire ?</h2>
                <p class="lead mb-4">
                    Partagez vos connaissances et aidez d'autres personnes à réussir. Rejoignez notre communauté de prestataires experts.
                </p>
                @auth
                    @php
                        $hasApplication = \App\Models\ProviderApplication::where('user_id', auth()->id())->exists();
                    @endphp
                    @if(auth()->user()->role !== 'provider' && !$hasApplication)
                        <a href="{{ route('provider-application.index') }}" class="btn btn-warning btn-lg">
                            <i class="fas fa-rocket me-2"></i>Devenir prestataire
                        </a>
                    @elseif($hasApplication)
                        @php
                            $application = \App\Models\ProviderApplication::where('user_id', auth()->id())->first();
                        @endphp
                        <a href="{{ route('provider-application.status', $application) }}" class="btn btn-warning btn-lg">
                            <i class="fas fa-eye me-2"></i>Voir ma candidature
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
    .provider-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
    }
    
    .provider-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
    }
    
    .provider-avatar img {
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
