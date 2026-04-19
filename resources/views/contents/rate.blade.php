@extends('layouts.app')

@section('title', 'Noter : ' . $course->title)

@section('content')
@php
    $thumbnailUrl = '';
    try {
        $thumbnailUrl = $course->thumbnail_url ?? '';
    } catch (\Throwable $e) {
        $thumbnailUrl = '';
    }
    $thumbnailUrl = $thumbnailUrl ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=300&h=200&fit=crop';
    $canSubmitReview = $user
        && (($canReview ?? false) || ($course->is_in_person_program ?? false));
@endphp

<div class="udemy-cart-container content-rate-page">
    <div class="cart-wrapper content-rate-wrapper">
        <div class="cart-header content-rate-header">
            <div class="cart-title-section">
                <h1 class="cart-title">Noter ce contenu</h1>
                <p class="cart-subtitle">Votre avis aide la communauté à choisir ses formations. Pour inviter quelqu’un à noter, utilisez le bloc <strong>«&nbsp;Lien public&nbsp;»</strong> juste en dessous.</p>
            </div>
            <div class="cart-actions">
                <a href="{{ route('contents.show', $course) }}" class="continue-shopping-btn">
                    <i class="fas fa-arrow-left"></i>
                    Voir la fiche du contenu
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        <div class="cart-items-section content-rate-share mb-4 shadow-sm" id="lien-public-page-noter">
            <div class="cart-items-header py-3 px-4">
                <h2 class="cart-items-title mb-0">
                    <i class="fas fa-link"></i>
                    Lien public de cette page (à copier)
                </h2>
            </div>
            <div class="p-3 p-md-4 bg-white rounded-bottom">
                <p class="text-dark mb-3" style="font-size: 0.95rem;">Copiez l’adresse ci-dessous et envoyez-la par e-mail, WhatsApp, etc. La personne ouvre directement <strong>cette page</strong> pour laisser une note et un avis.</p>
                <label for="content-rate-share-url" class="form-label fw-semibold small text-muted mb-1">Adresse à copier</label>
                <div class="input-group">
                    <input type="text" class="form-control font-monospace" id="content-rate-share-url" readonly value="{{ $shareUrl }}" aria-describedby="content-rate-copy-hint">
                    <button type="button" class="btn btn-primary px-3" id="content-rate-copy-btn" title="Copier le lien dans le presse-papiers">
                        <i class="fas fa-copy me-1"></i> Copier le lien
                    </button>
                </div>
                <p class="form-text mb-0 mt-2" id="content-rate-copy-hint">Après « Copier », collez le lien où vous voulez l’envoyer.</p>
            </div>
        </div>

        <div class="cart-items-section">
            <div class="cart-items-header">
                <h2 class="cart-items-title mb-0">
                    <i class="fas fa-star"></i>
                    {{ $course->title }}
                </h2>
            </div>
            <div class="cart-items-list recommended-courses pb-4">
                <div class="recommended-item cart-item-wrapper content-rate-course-card">
                    <div class="recommended-thumb">
                        <img src="{{ $thumbnailUrl }}" alt="{{ $course->title }}">
                    </div>
                    <div class="recommended-content flex-grow-1">
                        <h6 class="mb-1">{{ $course->title }}</h6>
                        @if($course->provider)
                            <p class="small text-muted mb-0">
                                <i class="fas fa-user me-1"></i>{{ $course->provider->name }}
                            </p>
                        @endif
                    </div>
                </div>

                @if(!$user)
                    <div class="px-4 pb-4">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Connectez-vous</strong> pour attribuer une note et rédiger votre avis.
                            <a href="{{ route('contents.rate.login-intent', $course) }}" class="alert-link ms-1">Se connecter</a>
                        </div>
                    </div>
                @elseif($canSubmitReview)
                    <div class="px-4 pb-4">
                        <form id="contentRateReviewForm" action="{{ route('contents.review.store', $course->slug) }}" method="POST">
                            @csrf
                            <input type="hidden" name="redirect_to" value="rate">

                            <p class="fw-semibold mb-2" style="font-size: 0.9375rem;">Comment évaluez-vous ce contenu ou cette formation ?</p>

                            <div class="mb-3">
                                <div class="rating-input-wrapper">
                                    <div class="rating-stars-input" data-rating="{{ $hasUserReview ? $userReview->rating : 0 }}">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star rating-star {{ $hasUserReview && $i <= $userReview->rating ? 'active' : '' }}"
                                               data-value="{{ $i }}"
                                               style="font-size: 1.5rem; color: #ddd; cursor: pointer; transition: all 0.2s; margin-right: 0.375rem;"></i>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="rating" id="ratingInput" value="{{ $hasUserReview ? $userReview->rating : 0 }}" required>
                                    <div class="rating-value-text mt-2 text-muted" style="font-size: 0.8125rem;">
                                        <span id="ratingText">{{ $hasUserReview ? $userReview->rating . ' étoile' . ($userReview->rating > 1 ? 's' : '') : 'Sélectionnez une note' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="reviewCommentRate" class="form-label fw-semibold mb-2" style="font-size: 0.9375rem;">Votre avis</label>
                                <textarea class="form-control"
                                          id="reviewCommentRate"
                                          name="comment"
                                          rows="4"
                                          style="font-size: 0.9375rem;"
                                          placeholder="Partagez votre expérience…">{{ $hasUserReview ? $userReview->comment : '' }}</textarea>
                                <div class="form-text" style="font-size: 0.8125rem;">Votre avis aidera d'autres personnes à prendre une décision.</div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>
                                    {{ $hasUserReview ? 'Mettre à jour' : 'Publier mon avis' }}
                                </button>
                                @if($hasUserReview)
                                    <button type="button" class="btn btn-outline-danger" id="contentRateDeleteReviewBtn">
                                        <i class="fas fa-trash me-1"></i>
                                        Supprimer
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                @else
                    <div class="px-4 pb-4">
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            @if($course->is_downloadable)
                                @if($course->is_free)
                                    Vous devez avoir <strong>téléchargé ce contenu au moins une fois</strong> pour pouvoir le noter et donner votre avis.
                                @else
                                    Vous devez avoir <strong>acheté ce contenu</strong> pour pouvoir le noter et donner votre avis.
                                @endif
                            @else
                                @if($course->is_free)
                                    Vous devez être <strong>inscrit à ce contenu</strong> pour pouvoir le noter et donner votre avis.
                                @else
                                    Vous devez avoir <strong>acheté ce contenu</strong> pour pouvoir le noter et donner votre avis.
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.content-rate-page .content-rate-wrapper {
    max-width: 720px;
}
.content-rate-page .content-rate-header {
    flex-wrap: wrap;
    gap: 1rem;
}
.content-rate-page .content-rate-course-card {
    margin: 1rem 1.5rem 0;
}
.content-rate-page .cart-items-list.recommended-courses {
    padding-top: 0;
}
.content-rate-page .cart-items-list .recommended-item.cart-item-wrapper {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 16px;
    border: 1px solid rgba(0, 51, 102, 0.15);
    background: linear-gradient(135deg, rgba(0, 51, 102, 0.05) 0%, rgba(0, 51, 102, 0.1) 100%);
    align-items: flex-start;
}
.content-rate-page .cart-items-list .recommended-thumb {
    width: 72px;
    height: 72px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
}
.content-rate-page .cart-items-list .recommended-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.content-rate-page .cart-items-list .recommended-content h6 {
    font-weight: 600;
    font-size: 0.9375rem;
    color: #1c1d1f;
    line-height: 1.35;
}
.content-rate-page .rating-stars-input {
    display: inline-flex;
    align-items: center;
}
.content-rate-page .rating-star:hover,
.content-rate-page .rating-star.active {
    color: var(--warning-color, #ffc107) !important;
}
.content-rate-page .rating-star.active {
    color: var(--warning-color, #ffc107) !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const copyBtn = document.getElementById('content-rate-copy-btn');
    const shareInput = document.getElementById('content-rate-share-url');
    if (copyBtn && shareInput) {
        copyBtn.addEventListener('click', function() {
            shareInput.select();
            shareInput.setSelectionRange(0, 99999);
            const url = shareInput.value;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function() {
                    copyBtn.innerHTML = '<i class="fas fa-check me-1"></i> Copié';
                    setTimeout(function() {
                        copyBtn.innerHTML = '<i class="fas fa-copy me-1"></i> Copier le lien';
                    }, 2000);
                }).catch(function() {
                    document.execCommand('copy');
                });
            } else {
                document.execCommand('copy');
            }
        });
    }

    const ratingStars = document.querySelectorAll('.content-rate-page .rating-star');
    const ratingInput = document.getElementById('ratingInput');
    const ratingText = document.getElementById('ratingText');

    if (ratingStars.length > 0 && ratingInput && ratingText) {
        let currentRating = parseInt(ratingInput.value, 10) || 0;

        function updateStars(rating) {
            ratingStars.forEach(function(star, index) {
                const starValue = index + 1;
                if (starValue <= rating) {
                    star.classList.add('active');
                    star.style.color = 'var(--warning-color, #ffc107)';
                } else {
                    star.classList.remove('active');
                    star.style.color = '#ddd';
                }
            });
        }

        function updateRatingText(rating) {
            if (rating === 0) {
                ratingText.textContent = 'Sélectionnez une note';
            } else {
                ratingText.textContent = rating + ' étoile' + (rating > 1 ? 's' : '');
            }
        }

        updateStars(currentRating);
        updateRatingText(currentRating);

        ratingStars.forEach(function(star, index) {
            const starValue = index + 1;
            star.addEventListener('click', function() {
                currentRating = starValue;
                ratingInput.value = currentRating;
                updateStars(currentRating);
                updateRatingText(currentRating);
            });
            star.addEventListener('mouseenter', function() {
                updateStars(starValue);
            });
        });

        const ratingWrapper = document.querySelector('.content-rate-page .rating-stars-input');
        if (ratingWrapper) {
            ratingWrapper.addEventListener('mouseleave', function() {
                updateStars(currentRating);
            });
        }
    }

    const deleteBtn = document.getElementById('contentRateDeleteReviewBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (!confirm('Êtes-vous sûr de vouloir supprimer votre avis ? Cette action est irréversible.')) {
                return;
            }
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('contents.review.destroy', $course->slug) }}';
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            const redirectInput = document.createElement('input');
            redirectInput.type = 'hidden';
            redirectInput.name = 'redirect_to';
            redirectInput.value = 'rate';
            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            form.appendChild(redirectInput);
            document.body.appendChild(form);
            form.submit();
        });
    }
});
</script>
@endpush
