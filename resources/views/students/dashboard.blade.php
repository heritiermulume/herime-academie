@extends('layouts.dashboard')

@php
    use Illuminate\Support\Str;

    $totalSpent = $user->orders()->sum('total_amount');
    $recentOrders = $user->orders()->latest()->limit(5)->get();
    $lastEnrollment = $enrollments->first();
@endphp

@section('title', 'Tableau de bord étudiant')
@section('dashboard-title', 'Tableau de bord étudiant')
@section('dashboard-subtitle', 'Suivez vos apprentissages, vos commandes et vos certificats en un clin d’œil')
@section('dashboard-actions')
    <a href="{{ route('courses.index') }}" class="btn btn-primary">
        <i class="fas fa-search me-2"></i>Découvrir des cours
    </a>
@endsection

@section('dashboard-content')
    <section class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div>
                        <h2 class="h4 fw-semibold mb-2 text-white">Bonjour, {{ $user->name }} !</h2>
                        <p class="mb-0 text-white-50">Continuez votre progression et reprenez vos cours là où vous les avez laissés.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($lastEnrollment && $lastEnrollment->course)
                            <a href="{{ route('student.courses.learn', $lastEnrollment->course->slug) }}" class="btn btn-light text-primary fw-semibold">
                                <i class="fas fa-play me-2"></i>Reprendre « {{ Str::limit($lastEnrollment->course->title, 24) }} »
                            </a>
                        @endif
                        <a href="{{ route('student.courses') }}" class="btn btn-outline-light">
                            <i class="fas fa-graduation-cap me-2"></i>Mes cours
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="insight-card shadow-sm h-100">
                <div class="insight-card__icon bg-primary-subtle text-primary">
                    <i class="fas fa-book"></i>
                </div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Cours inscrits</p>
                    <h3 class="insight-card__value">{{ $stats['total_courses'] }}</h3>
                    <p class="insight-card__supplement">{{ $stats['active_courses'] ?? $stats['total_courses'] }} actifs en ce moment</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="insight-card shadow-sm h-100">
                <div class="insight-card__icon bg-success-subtle text-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Cours terminés</p>
                    <h3 class="insight-card__value">{{ $stats['completed_courses'] }}</h3>
                    <p class="insight-card__supplement">Bravo pour vos apprentissages !</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="insight-card shadow-sm h-100">
                <div class="insight-card__icon bg-warning-subtle text-warning">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Certificats obtenus</p>
                    <h3 class="insight-card__value">{{ $stats['certificates_earned'] }}</h3>
                    <p class="insight-card__supplement">Retrouvez-les dans « Certificats »</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="insight-card shadow-sm h-100">
                <div class="insight-card__icon bg-info-subtle text-info">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="insight-card__content">
                    <p class="insight-card__label">Total dépensé</p>
                    <h3 class="insight-card__value">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($totalSpent) }}</h3>
                    <p class="insight-card__supplement">Investissements dans vos connaissances</p>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-4">
        <div class="col-12 col-xl-8 d-flex flex-column gap-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header card-header-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0 text-white fw-semibold">Mes cours récents</h5>
                        <small class="text-white-50">Reprenez un cours en un clic</small>
                    </div>
                    <a href="{{ route('student.courses') }}" class="btn btn-light btn-sm text-primary fw-semibold">
                        Voir tous <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($enrollments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($enrollments as $enrollment)
                                @if(!$enrollment->course)
                                    @continue
                                @endif
                                @php
                                    $course = $enrollment->course;
                                    $isDownloadableAndPurchased = false;
                                    if ($course->is_downloadable) {
                                        $hasPurchased = false;
                                        if (!$course->is_free && $enrollment->order_id) {
                                            $hasPurchased = $enrollment->order && $enrollment->order->status === 'paid';
                                        } elseif ($course->is_free) {
                                            $hasPurchased = true;
                                        } else {
                                            $hasPurchased = \App\Models\Order::where('user_id', $user->id)
                                                ->where('status', 'paid')
                                                ->whereHas('orderItems', fn($q) => $q->where('course_id', $course->id))
                                                ->exists();
                                        }
                                        $isDownloadableAndPurchased = $hasPurchased;
                                    }
                                    $buttonText = $enrollment->progress > 0 ? 'Continuer' : 'Commencer';
                                @endphp
                                <div class="list-group-item py-3">
                                    <div class="row align-items-center g-3">
                                        <div class="col-sm-2">
                                            @if($course->thumbnail_url)
                                                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="rounded w-100" style="height: 64px; object-fit: cover;">
                                            @else
                                                @php $initials = collect(explode(' ', trim($course->title)))->take(2)->map(fn($w)=>mb_substr($w,0,1))->implode(''); @endphp
                                                <div class="rounded bg-light d-flex align-items-center justify-content-center" style="height:64px;font-weight:700;color:#003366;">
                                                    {{ $initials }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-sm-6">
                                            <h6 class="fw-semibold mb-1">
                                                <a href="{{ route('courses.show', $course->slug) }}" class="text-decoration-none text-dark">
                                                    {{ $course->title }}
                                                </a>
                                            </h6>
                                            <div class="d-flex flex-wrap gap-2 text-muted small">
                                                <span><i class="fas fa-user me-1"></i>{{ $course->instructor->name }}</span>
                                                <span><i class="fas fa-tag me-1"></i>{{ $course->category->name }}</span>
                                                <span><i class="fas fa-clock me-1"></i>{{ $course->duration }} min</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            @if(!$isDownloadableAndPurchased)
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $enrollment->progress }}%" aria-valuenow="{{ $enrollment->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <small class="text-muted">{{ $enrollment->progress }}% terminé</small>
                                            @else
                                                <span class="badge bg-info text-dark"><i class="fas fa-download me-1"></i>Disponible hors ligne</span>
                                            @endif
                                        </div>
                                        <div class="col-sm-2 text-sm-end">
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
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header card-header-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0 text-white fw-semibold">Commandes récentes</h5>
                        <small class="text-white-50">Historique des 5 dernières commandes</small>
                    </div>
                    <a href="{{ route('orders.index') }}" class="btn btn-light btn-sm text-primary fw-semibold">
                        Voir toutes
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recentOrders->count())
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Commande</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td class="fw-semibold">{{ $order->order_number }}</td>
                                            <td class="text-success fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->total_amount) }}</td>
                                            <td>
                                                <span class="badge order-status-{{ $order->status }}">
                                                    @switch($order->status)
                                                        @case('pending')<i class="fas fa-clock me-1"></i>En attente @break
                                                        @case('confirmed')<i class="fas fa-check-circle me-1"></i>Confirmée @break
                                                        @case('paid')<i class="fas fa-credit-card me-1"></i>Payée @break
                                                        @case('completed')<i class="fas fa-check-double me-1"></i>Terminée @break
                                                        @case('cancelled')<i class="fas fa-times-circle me-1"></i>Annulée @break
                                                        @default {{ ucfirst($order->status) }}
                                                    @endswitch
                                                </span>
                                            </td>
                                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">Aucune commande récente</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4 d-flex flex-column gap-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0 fw-semibold">Accès rapide</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('student.courses') }}" class="btn btn-primary">
                            <i class="fas fa-graduation-cap me-2"></i>Mes cours
                        </a>
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-shopping-bag me-2"></i>Mes commandes
                        </a>
                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-bell me-2"></i>Notifications
                        </a>
                        <a href="{{ route('profile.redirect') }}" @if(session('sso_token')) target="_blank" rel="noopener noreferrer" @endif class="btn btn-outline-primary">
                            <i class="fas fa-user me-2"></i>Mon profil
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0 fw-semibold">Cours populaires</h6>
                </div>
                <div class="card-body p-0">
                    @if($recent_courses->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recent_courses as $course)
                                <div class="list-group-item py-3">
                                    <div class="d-flex align-items-start gap-3">
                                        @if($course->thumbnail_url)
                                            <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="rounded" style="width: 64px; height: 48px; object-fit: cover;">
                                        @else
                                            @php $ci = collect(explode(' ', trim($course->title)))->take(2)->map(fn($w)=>mb_substr($w,0,1))->implode(''); @endphp
                                            <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:64px;height:48px;font-weight:700;color:#003366;">
                                                {{ $ci }}
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <h6 class="fw-semibold mb-1">
                                                <a href="{{ route('courses.show', $course->slug) }}" class="text-decoration-none text-dark">
                                                    {{ Str::limit($course->title, 40) }}
                                                </a>
                                            </h6>
                                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                                <span><i class="fas fa-user me-1"></i>{{ $course->instructor->name }}</span>
                                                <span><i class="fas fa-users me-1"></i>{{ $course->enrollments_count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">Aucun cours suggéré pour le moment.</div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Mes certificats</h6>
                    <a href="{{ route('student.certificates') }}" class="btn btn-sm btn-outline-primary">Voir tous</a>
                </div>
                <div class="card-body p-0">
                    @if($certificates->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($certificates as $certificate)
                                <div class="list-group-item py-3 d-flex align-items-center gap-3">
                                    <span class="text-warning"><i class="fas fa-certificate fa-2x"></i></span>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-semibold mb-1">{{ Str::limit($certificate->course->title, 42) }}</h6>
                                        <small class="text-muted">Obtenu le {{ $certificate->issue_date->format('d/m/Y') }}</small>
                                    </div>
                                    <a href="{{ \App\Helpers\FileHelper::url($certificate->certificate_url) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">Aucun certificat pour l’instant.</div>
                    @endif
                </div>
            </div>
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
        box-shadow: 0 20px 45px -30px rgba(0, 51, 102, 0.45);
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