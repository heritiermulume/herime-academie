@extends('layouts.app')

@section('title', 'Avis - ' . $course->title . ' - Herime Academie')
@section('description', 'Consultez tous les avis des étudiants sur le cours : ' . $course->title)

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="breadcrumb-modern mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">Cours</a></li>
            @if($course->category)
            <li class="breadcrumb-item"><a href="{{ route('courses.category', $course->category->slug) }}">{{ $course->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->slug) }}">{{ Str::limit($course->title, 40) }}</a></li>
            <li class="breadcrumb-item active">Avis</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="h3 fw-bold mb-2">Avis des étudiants</h1>
                    <a href="{{ route('courses.show', $course->slug) }}" class="text-decoration-none">
                        <h2 class="h5 text-muted mb-0">{{ $course->title }}</h2>
                    </a>
                </div>
                <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Retour au cours
                </a>
            </div>
        </div>
    </div>

    <!-- Rating Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            <div class="rating-score" style="font-size: 3rem; font-weight: 700; color: var(--primary-color); line-height: 1;">
                                {{ number_format($averageRating, 1) }}
                            </div>
                            <div class="rating-stars mb-2" style="color: var(--warning-color); font-size: 1.25rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    @php
                                        $filledStar = $i <= round($averageRating, 0);
                                    @endphp
                                    <i class="fas fa-star {{ $filledStar ? '' : 'far' }}"></i>
                                @endfor
                            </div>
                            <div class="rating-count text-muted">
                                Basé sur {{ $totalReviews }} {{ $totalReviews > 1 ? 'avis' : 'avis' }}
                            </div>
                        </div>
                        <div class="col-md-9">
                            <!-- Rating Distribution -->
                            @php
                                $ratingDistribution = [];
                                for ($i = 5; $i >= 1; $i--) {
                                    $count = \App\Models\Review::where('course_id', $course->id)
                                        ->where('is_approved', true)
                                        ->where('rating', $i)
                                        ->count();
                                    $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100, 1) : 0;
                                    $ratingDistribution[$i] = [
                                        'count' => $count,
                                        'percentage' => $percentage
                                    ];
                                }
                            @endphp
                            <div class="rating-distribution">
                                @for($i = 5; $i >= 1; $i--)
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="d-flex align-items-center" style="width: 50px;">
                                            <span class="text-muted small">{{ $i }}</span>
                                            <i class="fas fa-star text-warning ms-1" style="font-size: 0.875rem;"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-warning" role="progressbar" 
                                                     style="width: {{ $ratingDistribution[$i]['percentage'] }}%" 
                                                     aria-valuenow="{{ $ratingDistribution[$i]['percentage'] }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ms-3" style="min-width: 60px; text-align: right;">
                                            <span class="text-muted small">{{ $ratingDistribution[$i]['count'] }}</span>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="row">
        <div class="col-12">
            @if($reviews->count() > 0)
                <div class="reviews-list">
                    @foreach($reviews as $review)
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-auto">
                                    @if($review->user && $review->user->avatar_url)
                                        <div class="review-avatar" style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden;">
                                            <img src="{{ $review->user->avatar_url }}" 
                                                 alt="{{ $review->user->name }}"
                                                 style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    @else
                                        <div class="review-avatar d-flex align-items-center justify-content-center bg-primary text-white" 
                                             style="width: 60px; height: 60px; border-radius: 50%; font-size: 1.5rem; font-weight: bold;">
                                            {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="mb-1 fw-bold">{{ $review->user->name ?? 'Utilisateur' }}</h5>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <div class="rating-stars" style="color: var(--warning-color); font-size: 1rem;">
                                                    @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= $review->rating ? '' : 'far' }}"></i>
                                                    @endfor
                                                </div>
                                                <span class="text-muted small">{{ $review->created_at->format('d/m/Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @if($review->comment)
                                    <div class="review-comment">
                                        {{ $review->comment }}
                                    </div>
                                    @else
                                    <div class="review-comment text-muted">
                                        <em>Aucun commentaire</em>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($reviews->hasPages())
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-center">
                            {{ $reviews->links('pagination.bootstrap-4') }}
                        </div>
                    </div>
                </div>
                @endif
            @else
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center p-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h5 class="fw-bold mb-2">Aucun avis pour le moment</h5>
                        <p class="text-muted mb-4">Soyez le premier à laisser un avis sur ce cours !</p>
                        <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Retour au cours
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .rating-score {
        font-size: 3rem;
        font-weight: 700;
        color: var(--primary-color);
        line-height: 1;
    }

    .rating-stars {
        color: var(--warning-color);
    }

    .review-comment {
        line-height: 1.6;
        color: var(--text-color);
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .progress {
        background-color: #e9ecef;
    }

    .breadcrumb-modern {
        background: transparent;
        padding: 0;
    }

    .breadcrumb-modern .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: var(--text-muted);
    }
</style>
@endpush
@endsection

