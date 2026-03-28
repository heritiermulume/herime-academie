{{-- Actions pour un contenu inclus dans un pack acheté : $course, $enrollment (nullable), $package (pour retour) --}}
@php
    $isPurchasedNotEnrolled = $enrollment === null;
    $statusKey = $enrollment->status ?? ($isPurchasedNotEnrolled ? 'purchased' : 'active');
    $progress = $isPurchasedNotEnrolled ? 0 : (float) ($enrollment->progress ?? 0);
@endphp
<div class="customer-pack-course__actions">
    @if($isPurchasedNotEnrolled)
        @if(($course->is_downloadable ?? false) || ($course->is_in_person_program ?? false))
            <a href="{{ route('contents.download', $course->slug) }}" class="admin-btn primary sm">
                <i class="fas fa-download me-1"></i>{{ $course->getDownloadButtonText() }}
            </a>
        @else
            <form action="{{ route('customer.contents.enroll', $course->slug) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="redirect_to" value="dashboard">
                @isset($package)
                    <input type="hidden" name="return_to_customer_pack" value="{{ $package->slug }}">
                @endisset
                <button type="submit" class="admin-btn primary sm">
                    <i class="fas fa-user-plus me-1"></i>Activer l'accès
                </button>
            </form>
        @endif
    @else
        @if(($course->is_downloadable ?? false) || ($course->is_in_person_program ?? false))
            <a href="{{ route('contents.download', $course->slug) }}" class="admin-btn primary sm">
                <i class="fas fa-download me-1"></i>{{ $course->getDownloadButtonText() }}
            </a>
        @else
            <a href="{{ route('learning.course', $course->slug) }}" class="admin-btn success sm">
                <i class="fas fa-play me-1"></i>{{ $progress > 0 ? 'Continuer' : 'Commencer' }}
            </a>
        @endif
    @endif
    <a href="{{ route('contents.show', $course->slug) }}" class="admin-btn ghost sm">
        Fiche contenu
    </a>
</div>
