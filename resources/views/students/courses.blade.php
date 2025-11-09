@extends('layouts.dashboard')

@php($user = auth()->user())
@php
    use Illuminate\Support\Str;
@endphp
@include('students.partials.dashboard-context', ['user' => $user])

@section('title', 'Mes cours - Tableau de bord étudiant')
@section('dashboard-title', 'Mes cours')
@section('dashboard-subtitle', 'Retrouvez tous vos cours et reprenez-les à votre rythme')
@section('dashboard-actions')
    <a href="{{ route('courses.index') }}" class="btn btn-primary">
        <i class="fas fa-search me-2"></i>Explorer de nouveaux cours
    </a>
@endsection

@section('dashboard-content')
    <section class="row g-3 mb-4">
        <div class="col-12 col-xl-4">
            <div class="insight-card shadow-sm h-100">
                <div class="insight-card__icon bg-primary-subtle text-primary"><i class="fas fa-book"></i></div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Cours inscrits</p>
                    <h3 class="insight-card__value">{{ $enrollments->total() }}</h3>
                    <p class="insight-card__supplement">{{ $enrollments->where('progress', '<', 100)->count() }} cours en cours</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="insight-card shadow-sm h-100">
                <div class="insight-card__icon bg-success-subtle text-success"><i class="fas fa-check-circle"></i></div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Cours terminés</p>
                    <h3 class="insight-card__value">{{ $enrollments->where('progress', '>=', 100)->count() }}</h3>
                    <p class="insight-card__supplement">Bravo pour votre engagement !</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="insight-card shadow-sm h-100">
                <div class="insight-card__icon bg-info-subtle text-info"><i class="fas fa-clock"></i></div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Progression moyenne</p>
                    <h3 class="insight-card__value">
                        @php
                            $avgProgress = $enrollments->count() ? round($enrollments->avg('progress')) : 0;
                        @endphp
                        {{ $avgProgress }}%
                    </h3>
                    <p class="insight-card__supplement">Continuez sur votre lancée</p>
                </div>
            </div>
        </div>
    </section>

    <section class="card shadow-sm border-0">
        <div class="card-header card-header-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="card-title mb-0 text-white fw-semibold">Tous mes cours</h5>
                <small class="text-white-50">{{ $enrollments->total() }} cours inscrits</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('student.dashboard') }}" class="btn btn-light btn-sm text-primary fw-semibold">
                    <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                </a>
                </div>
            </div>
            <div class="card-body p-0">
            @if($enrollments->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($enrollments as $enrollment)
                            @php
                                $course = $enrollment->course;
                            if (!$course) {
                                continue;
                            }
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
                                        ->whereHas('orderItems', fn($query) => $query->where('course_id', $course->id))
                                            ->exists();
                                    }
                                    $isDownloadableAndPurchased = $hasPurchased;
                                }
                            $buttonText = $enrollment->progress > 0 ? 'Continuer' : 'Commencer';
                            @endphp
                        <div class="list-group-item py-3">
                            <div class="row align-items-center g-3">
                                <div class="col-12 col-md-2 text-center text-md-start">
                                    @if($course->thumbnail_url)
                                        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="rounded" style="height: 60px; width: 100%; max-width: 100px; object-fit: cover;">
                                    @else
                                        @php $initials = collect(explode(' ', trim($course->title)))->take(2)->map(fn($w)=>mb_substr($w,0,1))->implode(''); @endphp
                                        <div class="d-flex align-items-center justify-content-center rounded bg-light mx-auto" style="height:60px;width:100px;font-weight:700;color:#003366;">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6">
                                    <h6 class="fw-semibold mb-1">
                                        <a href="{{ route('courses.show', $course->slug) }}" class="text-decoration-none text-dark">
                                            {{ $course->title }}
                                        </a>
                                    </h6>
                                    <div class="d-flex flex-wrap gap-2 text-muted small">
                                        <span><i class="fas fa-user me-1"></i>{{ $course->instructor->name }}</span>
                                        <span><i class="fas fa-tag me-1"></i>{{ $course->category->name }}</span>
                                        <span><i class="fas fa-clock me-1"></i>{{ $course->duration }} min</span>
                                        @if($course->is_downloadable && isset($course->user_downloads_count))
                                            <span class="text-info"><i class="fas fa-download me-1"></i>{{ $course->user_downloads_count }} téléchargements</span>
                                        @endif
                                    </div>
                            </div>
                            <div class="col-12 col-md-2">
                                    @if(!$isDownloadableAndPurchased)
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $enrollment->progress }}%" aria-valuenow="{{ $enrollment->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                                        <small class="text-muted">{{ $enrollment->progress }}% terminé</small>
                                    @else
                                        <span class="badge bg-info-subtle text-info"><i class="fas fa-download me-1"></i>Téléchargeable</span>
                            @endif
                                </div>
                                <div class="col-12 col-md-2 text-md-end text-center">
                                @if($isDownloadableAndPurchased)
                                        <a href="{{ route('courses.download', $course->slug) }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-download me-1"></i>Télécharger
                                    </a>
                                @else
                                        <a href="{{ route('student.courses.learn', $course->slug) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-play me-1"></i>{{ $buttonText }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $enrollments->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun cours inscrit</h5>
                    <p class="text-muted">Commencez votre parcours d'apprentissage dès maintenant.</p>
                    <a href="{{ route('courses.index') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Découvrir des cours
                    </a>
                </div>
            @endif
            </div>
    </section>
@endsection

@push('styles')
<style>
    .insight-card {
        display: flex;
        gap: 1rem;
        padding: 1.5rem;
        border-radius: 1.25rem;
        background: #ffffff;
        border: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .insight-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 45px -30px rgba(0, 51, 102, 0.35);
    }
    .insight-card__icon {
        width: 60px;
        height: 60px;
        border-radius: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .insight-card__label {
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        margin-bottom: 0.35rem;
    }
    .insight-card__value {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.1rem;
        color: #0b1f3a;
    }
    .insight-card__supplement {
        margin: 0;
        color: #7b8a9f;
        font-size: 0.875rem;
    }
</style>
@endpush
