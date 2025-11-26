@extends('layouts.app')

@section('title', 'Paiement réussi - Herime Academie')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center mb-5">
                <div class="mb-4">
                    <img src="{{ asset('images/logo-herime-academie.png') }}" alt="Herime Academie" style="height: 80px; max-width: 300px; object-fit: contain;">
                </div>
                <div class="success-icon mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h1 class="display-5 fw-bold text-success mb-3">Paiement réussi !</h1>
                <p class="lead text-muted">
                    Félicitations ! Votre paiement a été traité avec succès.
                </p>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-receipt me-2"></i>Détails de la commande
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Numéro de commande :</div>
                        <div class="col-sm-8">#{{ $order->order_number }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Date :</div>
                        <div class="col-sm-8">{{ $order->created_at->format('d/m/Y à H:i') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Montant total :</div>
                        <div class="col-sm-8">
                            <span class="h5 text-success fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->total) }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Statut :</div>
                        <div class="col-sm-8">
                            <span class="badge bg-success">Payé</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-book me-2"></i>Cours achetés
                    </h5>
                </div>
                <div class="card-body p-0">
                    @foreach($order->orderItems as $item)
                    <div class="border-bottom p-4">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <img src="{{ $item->course->thumbnail_url ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=100&h=60&fit=crop' }}" 
                                     alt="{{ $item->course->title }}" class="img-fluid rounded">
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-1">{{ $item->course->title }}</h6>
                                <p class="text-muted small mb-1">{{ $item->course->instructor->name }}</p>
                                <span class="badge bg-primary">{{ $item->course->category->name }}</span>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($item->total) }}</div>
                                @if($item->course->is_downloadable)
                                    <a href="{{ route('courses.show', $item->course->slug) }}" 
                                       class="btn btn-primary btn-sm mt-2">
                                        <i class="fas fa-eye me-1"></i>Voir le cours
                                    </a>
                                @else
                                    <a href="{{ route('learning.course', $item->course->slug) }}" 
                                       class="btn btn-primary btn-sm mt-2">
                                        <i class="fas fa-play me-1"></i>Commencer
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-info-circle text-info me-2"></i>Prochaines étapes
                    </h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Vous avez maintenant accès à tous les cours achetés
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Un email de confirmation a été envoyé à votre adresse
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Vous pouvez commencer à apprendre immédiatement
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            Votre progression sera sauvegardée automatiquement
                        </li>
                    </ul>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('student.dashboard') }}" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-tachometer-alt me-2"></i>Aller au tableau de bord
                </a>
                <a href="{{ route('courses.index') }}" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-search me-2"></i>Découvrir d'autres cours
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.success-icon {
    animation: bounceIn 0.6s ease-in-out;
}

@keyframes bounceIn {
    0% {
        transform: scale(0.3);
        opacity: 0;
    }
    50% {
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.btn-primary {
    background-color: #003366;
    border-color: #003366;
}

.btn-primary:hover {
    background-color: #004080;
    border-color: #004080;
}

.btn-outline-primary {
    color: #003366;
    border-color: #003366;
}

.btn-outline-primary:hover {
    background-color: #003366;
    border-color: #003366;
}
</style>
@endpush

@push('scripts')
<script>
    // Rafraîchir immédiatement les notifications après un paiement réussi
    document.addEventListener('DOMContentLoaded', function() {
        // Déclencher l'événement personnalisé pour notifier que des notifications ont été créées
        document.dispatchEvent(new CustomEvent('notification-created'));
        
        // Attendre un court délai pour que les notifications soient créées côté serveur
        setTimeout(function() {
            if (typeof window.loadNotifications === 'function') {
                window.loadNotifications();
            } else if (typeof window.refreshNotificationsNow === 'function') {
                window.refreshNotificationsNow();
            }
        }, 1000);
        
        // Rafraîchir à nouveau après 3 secondes pour être sûr
        setTimeout(function() {
            if (typeof window.loadNotifications === 'function') {
                window.loadNotifications();
            } else if (typeof window.refreshNotificationsNow === 'function') {
                window.refreshNotificationsNow();
            }
        }, 3000);
    });
</script>
@endpush