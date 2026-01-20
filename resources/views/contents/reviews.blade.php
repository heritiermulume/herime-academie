@extends('layouts.app')

@section('title', 'Avis - ' . $course->title . ' - Herime Academie')
@section('description', 'Consultez tous les avis des étudiants sur le contenu : ' . $course->title)

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="breadcrumb-modern mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
            <li class="breadcrumb-item"><a href="{{ route('contents.index') }}">Contenus</a></li>
            @if($course->category)
            <li class="breadcrumb-item"><a href="{{ route('contents.category', $course->category->slug) }}">{{ $course->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item"><a href="{{ route('contents.show', $course->slug) }}">{{ Str::limit($course->title, 40) }}</a></li>
            <li class="breadcrumb-item active">Avis</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="h3 fw-bold mb-2">Avis des étudiants</h1>
                    <a href="{{ route('contents.show', $course->slug) }}" class="text-decoration-none">
                        <h2 class="h5 text-muted mb-0">{{ $course->title }}</h2>
                    </a>
                </div>
                <a href="{{ route('contents.show', $course->slug) }}" class="btn btn-outline-primary">
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
                                    $count = \App\Models\Review::where('content_id', $course->id)
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
                <div class="reviews-list-modern">
                    @foreach($reviews as $review)
                    <div class="review-card-modern">
                        <div class="review-card-content">
                            <div class="review-header-modern">
                                <div class="review-avatar-modern">
                                    @if($review->user && $review->user->avatar_url)
                                        <img src="{{ $review->user->avatar_url }}" 
                                             alt="{{ $review->user->name }}"
                                             class="avatar-image">
                                        <div class="avatar-ring"></div>
                                    @else
                                        <div class="avatar-initials">
                                            {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="avatar-ring"></div>
                                    @endif
                                </div>
                                <div class="review-meta">
                                    <div class="review-author-name">{{ $review->user->name ?? 'Utilisateur' }}</div>
                                    <div class="review-date-modern">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        {{ $review->created_at->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>
                            <div class="review-rating-modern">
                                <div class="rating-stars-modern">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? 'star-filled' : 'star-empty' }}"></i>
                                    @endfor
                                </div>
                                <span class="rating-badge">{{ $review->rating }}/5</span>
                            </div>
                            @if($review->comment)
                            <div class="review-comment-modern">
                                <div class="comment-icon">
                                    <i class="fas fa-quote-left"></i>
                                </div>
                                <p>{{ $review->comment }}</p>
                            </div>
                            @else
                            <div class="review-comment-modern no-comment">
                                <em>Aucun commentaire</em>
                            </div>
                            @endif
                        </div>
                        <div class="review-card-footer">
                            <div class="review-verified">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                <span>Avis vérifié</span>
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
                        <p class="text-muted mb-4">Soyez le premier à laisser un avis sur ce contenu !</p>
                        <a href="{{ route('contents.show', $course->slug) }}" class="btn btn-primary">
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
    :root {
        --review-card-bg: #ffffff;
        --review-card-border: #e9ecef;
        --review-card-hover-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        --review-primary: #0d6efd;
        --review-success: #198754;
        --review-warning: #ffc107;
        --review-text: #212529;
        --review-text-muted: #6c757d;
        --review-bg-light: #f8f9fa;
    }

    .rating-score {
        font-size: 3rem;
        font-weight: 700;
        color: var(--primary-color);
        line-height: 1;
    }

    .rating-stars {
        color: var(--warning-color);
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

    /* Modern Review Cards */
    .reviews-list-modern {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    /* Desktop: 2 columns */
    @media (min-width: 992px) {
        .reviews-list-modern {
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
    }

    .review-card-modern {
        background: var(--review-card-bg);
        border: 1px solid var(--review-card-border);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        position: relative;
    }

    .review-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--review-primary), var(--review-warning));
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .review-card-modern:hover {
        transform: translateY(-4px);
        box-shadow: var(--review-card-hover-shadow);
        border-color: rgba(13, 110, 253, 0.2);
    }

    .review-card-modern:hover::before {
        opacity: 1;
    }

    .review-card-content {
        padding: 1.75rem;
    }

    .review-header-modern {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .review-avatar-modern {
        position: relative;
        flex-shrink: 0;
    }

    .review-avatar-modern .avatar-image,
    .review-avatar-modern .avatar-initials {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 2;
    }

    .review-avatar-modern .avatar-image {
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .review-avatar-modern .avatar-initials {
        background: linear-gradient(135deg, var(--review-primary), #0056b3);
        color: white;
        font-size: 1.5rem;
        font-weight: 700;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .avatar-ring {
        position: absolute;
        top: -4px;
        left: -4px;
        right: -4px;
        bottom: -4px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--review-primary), var(--review-warning));
        opacity: 0.2;
        z-index: 1;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 0.2;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.1;
        }
    }

    .review-meta {
        flex: 1;
        min-width: 0;
    }

    .review-author-name {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--review-text);
        margin-bottom: 0.375rem;
        line-height: 1.3;
    }

    .review-date-modern {
        font-size: 0.875rem;
        color: var(--review-text-muted);
        display: flex;
        align-items: center;
    }

    .review-rating-modern {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
        padding: 0.75rem;
        background: var(--review-bg-light);
        border-radius: 10px;
    }

    .rating-stars-modern {
        display: flex;
        gap: 0.25rem;
    }

    .rating-stars-modern .star-filled {
        color: var(--review-warning);
        font-size: 1.125rem;
    }

    .rating-stars-modern .star-empty {
        color: #dee2e6;
        font-size: 1.125rem;
    }

    .rating-badge {
        background: var(--review-primary);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .review-comment-modern {
        position: relative;
        padding-left: 2rem;
        color: var(--review-text);
        line-height: 1.7;
        font-size: 0.9375rem;
    }

    .review-comment-modern .comment-icon {
        position: absolute;
        left: 0;
        top: 0;
        color: var(--review-primary);
        opacity: 0.2;
        font-size: 1.5rem;
    }

    .review-comment-modern p {
        margin: 0;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .review-comment-modern.no-comment {
        color: var(--review-text-muted);
        font-style: italic;
        padding-left: 0;
    }

    .review-card-footer {
        padding: 1rem 1.75rem;
        background: var(--review-bg-light);
        border-top: 1px solid var(--review-card-border);
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .review-verified {
        font-size: 0.8125rem;
        color: var(--review-text-muted);
        display: flex;
        align-items: center;
    }

    /* Responsive Design */
    @media (max-width: 991.98px) {
        .review-card-content {
            padding: 1.5rem;
        }

        .review-header-modern {
            gap: 0.875rem;
            margin-bottom: 1rem;
        }

        .review-avatar-modern .avatar-image,
        .review-avatar-modern .avatar-initials {
            width: 56px;
            height: 56px;
            font-size: 1.25rem;
        }

        .review-author-name {
            font-size: 1rem;
        }

        .review-rating-modern {
            padding: 0.625rem;
            margin-bottom: 1rem;
        }

        .review-comment-modern {
            font-size: 0.875rem;
            padding-left: 1.75rem;
        }

        .review-card-footer {
            padding: 0.875rem 1.5rem;
        }
    }

    @media (max-width: 575.98px) {
        .reviews-list-modern {
            gap: 1.25rem;
        }

        .review-card-modern {
            border-radius: 12px;
        }

        .review-card-content {
            padding: 1.25rem;
        }

        .review-header-modern {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .review-avatar-modern {
            align-self: center;
        }

        .review-avatar-modern .avatar-image,
        .review-avatar-modern .avatar-initials {
            width: 52px;
            height: 52px;
            font-size: 1.125rem;
        }

        .review-meta {
            text-align: center;
            width: 100%;
        }

        .review-author-name {
            font-size: 0.9375rem;
            text-align: center;
        }

        .review-date-modern {
            justify-content: center;
            font-size: 0.8125rem;
        }

        .review-rating-modern {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
            padding: 0.75rem;
        }

        .rating-stars-modern .star-filled,
        .rating-stars-modern .star-empty {
            font-size: 1rem;
        }

        .rating-badge {
            font-size: 0.8125rem;
            padding: 0.2rem 0.625rem;
        }

        .review-comment-modern {
            font-size: 0.875rem;
            padding-left: 1.5rem;
            line-height: 1.6;
        }

        .review-comment-modern .comment-icon {
            font-size: 1.25rem;
        }

        .review-card-footer {
            padding: 0.75rem 1.25rem;
            justify-content: center;
        }

        .review-verified {
            font-size: 0.75rem;
        }
    }
</style>
@endpush
@endsection

