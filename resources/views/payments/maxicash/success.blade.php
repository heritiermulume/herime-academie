@extends('layouts.app')

@section('title', 'Paiement Réussi - Herime Académie')
@section('description', 'Votre paiement a été traité avec succès.')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-check-circle fa-4x text-success"></i>
                        </div>
                        <h2 class="mb-2">Paiement Réussi !</h2>
                        <p class="text-muted">Votre paiement a été traité avec succès via MaxiCash.</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <strong><i class="fas fa-check-circle me-2"></i>Transaction Confirmée</strong>
                        <p class="mb-0 mt-2">Votre commande a été enregistrée avec succès. Vous pouvez maintenant accéder à vos cours.</p>
                    </div>
                    
                    <div class="order-details mb-4">
                        <h5 class="mb-3">Détails de la commande</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Référence</th>
                                    <td>{{ $paymentData['reference'] }}</td>
                                </tr>
                                <tr>
                                    <th>Méthode de paiement</th>
                                    <td>MaxiCash</td>
                                </tr>
                                <tr>
                                    <th>Montant total</th>
                                    <td>${{ number_format($paymentData['amount'], 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="course-list mb-4">
                        <h5 class="mb-3">Cours achetés</h5>
                        <div class="list-group">
                            @foreach($paymentData['cart_items'] as $item)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-book me-2"></i>{{ $item['course']['title'] ?? 'Cours' }}</span>
                                    <span class="badge bg-success">Enregistré</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('my-courses') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-book me-2"></i>Accéder à mes cours
                        </a>
                        <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

