@extends('layouts.app')

@section('title', 'Tableau de bord étudiant - Herime Academie')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center text-white rounded-3 p-3" style="background-color:#003366;">
                <div>
                    <h1 class="h3 fw-bold mb-1 text-white">Bonjour, {{ auth()->user()->name }} !</h1>
                    <p class="mb-0 text-white-50">Bienvenue sur votre tableau de bord étudiant</p>
                </div>
                <div>
                    <a href="{{ route('courses.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Découvrir des cours
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-book text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Cours inscrits</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_courses'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Cours terminés</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['completed_courses'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-certificate text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Certificats</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['certificates_earned'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-clock text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Heures d'apprentissage</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['learning_hours'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total dépensé -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-wallet text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total dépensé</h6>
                            <h3 class="mb-0 fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol(auth()->user()->orders()->sum('total_amount')) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Enrollments -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Mes cours récents</h5>
                        <a href="{{ route('student.courses') }}" class="btn btn-primary btn-sm">
                            Voir tous <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($enrollments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($enrollments as $enrollment)
                                @if(!$enrollment->course)
                                    @continue
                                @endif
                            <div class="list-group-item border-0 py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        @if($enrollment->course->thumbnail)
                                            <img src="{{ $enrollment->course->thumbnail }}" 
                                                 alt="{{ $enrollment->course->title }}" class="img-fluid rounded" style="height: 60px; object-fit: cover;">
                                        @else
                                            @php $initials = collect(explode(' ', trim($enrollment->course->title)))->take(2)->map(fn($w)=>mb_substr($w,0,1))->implode(''); @endphp
                                            <div class="d-flex align-items-center justify-content-center rounded" style="height:60px; background:#e9eef6; color:#003366; font-weight:700;">
                                                {{ $initials }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-1 fw-bold">
                                            <a href="{{ route('courses.show', $enrollment->course->slug) }}" class="text-decoration-none text-dark">
                                                {{ $enrollment->course->title }}
                                            </a>
                                        </h6>
                                        <p class="text-muted small mb-1">{{ $enrollment->course->instructor->name }}</p>
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <span class="badge bg-primary me-2">{{ $enrollment->course->category->name }}</span>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>{{ $enrollment->course->duration }} min
                                            </small>
                                            @if($enrollment->course->is_downloadable && isset($enrollment->course->user_downloads_count))
                                                <small class="text-info">
                                                    <i class="fas fa-download me-1"></i>{{ $enrollment->course->user_downloads_count }} téléchargement(s)
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                    @php
                                        $course = $enrollment->course;
                                        $isDownloadableAndPurchased = false;
                                        if ($course && $course->is_downloadable) {
                                            $hasPurchased = false;
                                            if (!$course->is_free && $enrollment->order_id) {
                                                $hasPurchased = $enrollment->order && $enrollment->order->status === 'paid';
                                            } elseif ($course->is_free) {
                                                $hasPurchased = true;
                                            } else {
                                                $hasPurchased = \App\Models\Order::where('user_id', auth()->id())
                                                    ->where('status', 'paid')
                                                    ->whereHas('orderItems', function($query) use ($course) {
                                                        $query->where('course_id', $course->id);
                                                    })
                                                    ->exists();
                                            }
                                            $isDownloadableAndPurchased = $hasPurchased;
                                        }
                                    @endphp
                                    @if(!$isDownloadableAndPurchased)
                                    <div class="col-md-2">
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                 style="width: {{ $enrollment->progress }}%" 
                                                 aria-valuenow="{{ $enrollment->progress }}" 
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small class="text-muted">{{ $enrollment->progress }}% terminé</small>
                                    </div>
                                    @else
                                    <div class="col-md-2">
                                        <small class="text-muted">
                                            <i class="fas fa-download me-1"></i>Cours téléchargeable
                                        </small>
                                    </div>
                                    @endif
                                    <div class="col-md-2 text-end">
                                        @php
                                            $course = $enrollment->course;
                                            if (!$course) {
                                                $hasPurchased = false;
                                                $isDownloadableAndPurchased = false;
                                                $buttonText = 'Commencer';
                                            } else {
                                                $hasPurchased = false;
                                                
                                                // Vérifier si l'utilisateur a payé (pour les cours payants)
                                                if (!$course->is_free && $enrollment->order_id) {
                                                    $hasPurchased = $enrollment->order && $enrollment->order->status === 'paid';
                                                } elseif ($course->is_free) {
                                                    // Pour les cours gratuits, considérer comme "payé" si inscrit
                                                    $hasPurchased = true;
                                                } else {
                                                    // Vérifier via les commandes
                                                    $hasPurchased = \App\Models\Order::where('user_id', auth()->id())
                                                        ->where('status', 'paid')
                                                        ->whereHas('orderItems', function($query) use ($course) {
                                                            $query->where('course_id', $course->id);
                                                        })
                                                        ->exists();
                                                }
                                                
                                                // Si cours téléchargeable ET acheté, afficher uniquement le bouton télécharger
                                                $isDownloadableAndPurchased = $course->is_downloadable && $hasPurchased;
                                                
                                                // Déterminer le texte du bouton selon la progression
                                                $buttonText = $enrollment->progress > 0 ? 'Continuer' : 'Commencer';
                                            }
                                        @endphp
                                        
                                        @if($course && $isDownloadableAndPurchased)
                                            <a href="{{ route('courses.download', $course->slug) }}" 
                                               class="btn btn-success btn-sm">
                                                <i class="fas fa-download me-1"></i>Télécharger
                                            </a>
                                        @elseif($course)
                                            <a href="{{ route('student.courses.learn', $course->slug) }}" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-play me-1"></i>{{ $buttonText }} l'apprentissage
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
                            <p class="text-muted">Commencez votre parcours d'apprentissage dès maintenant</p>
                            <a href="{{ route('courses.index') }}" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Découvrir des cours
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Commandes récentes -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Commandes récentes</h5>
                    <a href="{{ route('orders.index') }}" class="btn btn-primary btn-sm">Voir toutes</a>
                </div>
                <div class="card-body">
                    @php $recentOrders = auth()->user()->orders()->latest()->limit(5)->get(); @endphp
                    @if($recentOrders->count())
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Commande</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th></th>
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
                                                <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">Aucune commande récente</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Accès rapide -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Accès rapide</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('student.courses') }}" class="btn btn-primary">
                            <i class="fas fa-graduation-cap me-2"></i>Mes cours
                        </a>
                        <a href="{{ route('orders.index') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i>Mes commandes
                        </a>
                        @php $lastEnrollment = $enrollments->first(); @endphp
                        @if($lastEnrollment)
                        <a href="{{ route('student.courses.learn', $lastEnrollment->course->slug) }}" class="btn btn-primary">
                            <i class="fas fa-play me-2"></i>Continuer l'apprentissage
                        </a>
                        @endif
                        <a href="{{ route('courses.index') }}" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Découvrir des cours
                        </a>
                    </div>
                </div>
            </div>
            <!-- Recent Courses -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Cours populaires</h5>
                </div>
                <div class="card-body p-0">
                    @if($recent_courses->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recent_courses as $course)
                            <div class="list-group-item border-0 py-3">
                                <div class="d-flex align-items-start">
                                    @if($course->thumbnail)
                                        <img src="{{ $course->thumbnail }}" 
                                             alt="{{ $course->title }}" class="rounded me-3" style="width: 60px; height: 40px; object-fit: cover;">
                                    @else
                                        @php $ci = collect(explode(' ', trim($course->title)))->take(2)->map(fn($w)=>mb_substr($w,0,1))->implode(''); @endphp
                                        <div class="rounded me-3 d-flex align-items-center justify-content-center" style="width:60px;height:40px;background:#e9eef6;color:#003366;font-weight:700;">
                                            {{ $ci }}
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('courses.show', $course->slug) }}" class="text-decoration-none text-dark">
                                                {{ Str::limit($course->title, 40) }}
                                            </a>
                                        </h6>
                                        <p class="text-muted small mb-1">{{ $course->instructor->name }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-primary">{{ $course->category->name }}</span>
                                            <small class="text-muted">
                                                <i class="fas fa-users me-1"></i>{{ $course->enrollments_count }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-graduation-cap fa-2x text-muted mb-2"></i>
                            <p class="text-muted small">Aucun cours disponible</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Certificates -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Mes certificats</h5>
                        <a href="{{ route('student.certificates') }}" class="btn btn-primary btn-sm">
                            Voir tous
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($certificates->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($certificates as $certificate)
                            <div class="list-group-item border-0 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-certificate text-warning fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ $certificate->course->title }}</h6>
                                        <small class="text-muted">
                                            Obtenu le {{ $certificate->issue_date->format('d/m/Y') }}
                                        </small>
                                    </div>
                                    <div>
                                        <a href="{{ \App\Helpers\FileHelper::url($certificate->certificate_url) }}" 
                                           target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-certificate fa-2x text-muted mb-2"></i>
                            <p class="text-muted small">Aucun certificat obtenu</p>
                            <p class="text-muted small">Terminez un cours pour obtenir votre premier certificat</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.btn-primary {
    background-color: #003366;
    border-color: #003366;
}
.btn-primary:hover, .btn-primary:focus {
    background-color: #004080;
    border-color: #004080;
}
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

.progress {
    border-radius: 4px;
}

.progress-bar {
    border-radius: 4px;
}

.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}
</style>
@endpush