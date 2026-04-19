@auth
@if(!empty($pendingRatingModal))
@php
    /** @var \App\Models\Course $modalCourse */
    $modalCourse = $pendingRatingModal['course'];
    $canReviewModal = $pendingRatingModal['can_review'] ?? false;
    $reviewBlockMessage = $pendingRatingModal['message'] ?? '';
@endphp
<div class="modal fade" id="contentRatingRequestModal" tabindex="-1" aria-labelledby="contentRatingRequestModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                <h5 class="modal-title fw-bold" id="contentRatingRequestModalLabel">
                    <i class="fas fa-star me-2"></i>Noter « {{ $modalCourse->title }} »
                </h5>
                <form action="{{ route('rating.pending.dismiss') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn-close btn-close-white" aria-label="Fermer"></button>
                </form>
            </div>
            <div class="modal-body p-4">
                @if($canReviewModal)
                    <p class="text-muted mb-4">Votre retour aide les autres apprenants et l’équipe Herime Académie.</p>
                    <form id="globalContentRatingForm" action="{{ route('contents.review.store', $modalCourse->slug) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <span class="form-label fw-semibold d-block">Votre note</span>
                            <div class="rating-input-wrapper-global">
                                <div class="rating-stars-input-global d-flex align-items-center gap-1" role="group" aria-label="Note sur 5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button" class="btn btn-link p-0 text-decoration-none rating-star-global" data-value="{{ $i }}" aria-label="{{ $i }} sur 5" style="font-size: 1.75rem; color: #dee2e6; line-height: 1;">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" id="globalRatingInput" value="0">
                                <div class="text-muted small mt-1" id="globalRatingText">Sélectionnez une note</div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="globalReviewComment" class="form-label fw-semibold">Commentaire (optionnel)</label>
                            <textarea class="form-control" id="globalReviewComment" name="comment" rows="3" maxlength="2000" placeholder="Partagez votre expérience…"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i>Publier mon avis
                        </button>
                    </form>
                    <div class="mt-3">
                        <form action="{{ route('rating.pending.dismiss') }}" method="POST" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Plus tard</button>
                        </form>
                    </div>
                @else
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-info-circle me-2"></i>{{ $reviewBlockMessage ?: 'Vous ne pouvez pas noter ce contenu pour le moment.' }}
                    </div>
                    <div class="text-end">
                        <form action="{{ route('rating.pending.dismiss') }}" method="POST" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn btn-primary">OK</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('contentRatingRequestModal');
        if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            return;
        }
        var modal = new bootstrap.Modal(modalEl);
        modal.show();

        var stars = modalEl.querySelectorAll('.rating-star-global');
        var hidden = document.getElementById('globalRatingInput');
        var label = document.getElementById('globalRatingText');
        if (!stars.length || !hidden || !label) {
            return;
        }
        function paint(v) {
            stars.forEach(function (btn) {
                var val = parseInt(btn.getAttribute('data-value'), 10);
                var icon = btn.querySelector('i');
                if (icon) {
                    icon.style.color = val <= v ? '#ffc107' : '#dee2e6';
                }
            });
            label.textContent = v > 0 ? (v + ' étoile' + (v > 1 ? 's' : '')) : 'Sélectionnez une note';
        }
        stars.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var v = parseInt(btn.getAttribute('data-value'), 10);
                hidden.value = String(v);
                paint(v);
            });
        });

        var reviewForm = document.getElementById('globalContentRatingForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function (e) {
                var v = parseInt(hidden.value, 10);
                if (!v || v < 1 || v > 5) {
                    e.preventDefault();
                    label.textContent = 'Veuillez choisir une note entre 1 et 5.';
                    label.classList.add('text-danger');
                }
            });
        }
    });
})();
</script>
@endif
@endauth
