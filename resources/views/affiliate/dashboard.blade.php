@extends('layouts.app')

@section('title', 'Tableau de bord affilié - Herime Academie')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 fw-bold mb-1">Tableau de bord affilié</h1>
                    <p class="text-muted mb-0">Gérez votre programme d'affiliation et suivez vos gains</p>
                </div>
                <div>
                    <a href="{{ route('affiliate.links') }}" class="btn btn-primary">
                        <i class="fas fa-link me-2"></i>Générer des liens
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
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-dollar-sign text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Gains totaux</h6>
                            <h3 class="mb-0 fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($stats['total_earnings'] ?? 0) }}</h3>
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
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">En attente</h6>
                            <h3 class="mb-0 fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($stats['pending_earnings'] ?? 0) }}</h3>
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
                                <i class="fas fa-check-circle text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Payés</h6>
                            <h3 class="mb-0 fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($stats['paid_earnings'] ?? 0) }}</h3>
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
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-shopping-cart text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Commandes</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_orders']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Commandes récentes</h5>
                        <a href="{{ route('affiliate.earnings') }}" class="btn btn-outline-primary btn-sm">
                            Voir toutes <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Commande</th>
                                        <th>Client</th>
                                        <th>Cours</th>
                                        <th>Commission</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">#{{ $order->order_number }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $order->user->avatar_url }}" 
                                                     alt="{{ $order->user->name }}" class="rounded-circle me-2" width="30" height="30">
                                                <span>{{ $order->user->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @foreach($order->orderItems as $item)
                                                <div class="small">{{ Str::limit($item->course->title, 30) }}</div>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">{{ \App\Helpers\CurrencyHelper::formatWithSymbol(($order->total * ($affiliate->commission_rate ?? 0)) / 100) }}</span>
                                        </td>
                                        <td>
                                            @switch($order->status)
                                                @case('paid')
                                                    <span class="badge bg-success">Payé</span>
                                                    @break
                                                @case('pending')
                                                    <span class="badge bg-warning">En attente</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Annulé</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $order->created_at->format('d/m/Y') }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune commande générée</h5>
                            <p class="text-muted">Commencez à promouvoir des cours pour générer des commissions</p>
                            <a href="{{ route('affiliate.links') }}" class="btn btn-primary">
                                <i class="fas fa-link me-2"></i>Générer des liens
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Affiliate Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold">Informations d'affiliation</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">Code d'affiliation</h6>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $affiliate->code }}" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard('{{ $affiliate->code }}')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">Taux de commission</h6>
                        <span class="h5 text-primary">{{ $affiliate->commission_rate }}%</span>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">Statut</h6>
                        <span class="badge bg-success">Actif</span>
                    </div>
                    <button class="btn btn-outline-primary btn-sm w-100" onclick="updateProfile()">
                        <i class="fas fa-edit me-1"></i>Modifier le profil
                    </button>
                </div>
            </div>

            <!-- Popular Courses -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Cours populaires</h5>
                </div>
                <div class="card-body p-0">
                    @if($popularCourses->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($popularCourses as $course)
                            <div class="list-group-item border-0 py-3">
                                <div class="d-flex align-items-start">
                                    <img src="{{ $course->thumbnail_url ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=60&h=40&fit=crop' }}" 
                                         alt="{{ $course->title }}" class="rounded me-3" style="width: 60px; height: 40px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">
                                            <a href="{{ route('contents.show', $course->slug) }}" class="text-decoration-none text-dark">
                                                {{ Str::limit($course->title, 40) }}
                                            </a>
                                        </h6>
                                        <p class="text-muted small mb-1">{{ $course->provider->name }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-primary">{{ $course->category->name }}</span>
                                            <small class="text-muted">
                                                <i class="fas fa-users me-1"></i>{{ $course->customers_count }}
                                            </small>
                                        </div>
                                        <div class="mt-2">
                                            <button class="btn btn-outline-primary btn-sm" onclick="generateLink({{ $course->id }})">
                                                <i class="fas fa-link me-1"></i>Générer un lien
                                            </button>
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

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('affiliate.links') }}" class="btn btn-primary">
                            <i class="fas fa-link me-2"></i>Générer des liens
                        </a>
                        <a href="{{ route('affiliate.earnings') }}" class="btn btn-outline-primary">
                            <i class="fas fa-chart-line me-2"></i>Voir les gains
                        </a>
                        <button class="btn btn-outline-success" onclick="requestPayout()">
                            <i class="fas fa-money-bill-wave me-2"></i>Demander un paiement
                        </button>
                        <a href="{{ route('contents.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-search me-2"></i>Explorer les cours
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Profile Modal -->
<div class="modal fade" id="updateProfileModal" tabindex="-1" aria-labelledby="updateProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateProfileModalLabel">Modifier le profil d'affiliation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateProfileForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom d'affiliation</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $affiliate->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="commission_rate" class="form-label">Taux de commission (%)</label>
                        <input type="number" class="form-control" id="commission_rate" name="commission_rate" 
                               value="{{ $affiliate->commission_rate }}" min="0" max="100" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Payout Modal -->
<div class="modal fade" id="requestPayoutModal" tabindex="-1" aria-labelledby="requestPayoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestPayoutModalLabel">Demander un paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="requestPayoutForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Montant ($)</label>
                        <input type="number" class="form-control" id="amount" name="amount" 
                               min="10" max="{{ $affiliate->pending_earnings }}" step="0.01" required>
                        <div class="form-text">Montant disponible: {{ \App\Helpers\CurrencyHelper::formatWithSymbol($affiliate->pending_earnings ?? 0) }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Méthode de paiement</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Sélectionner une méthode</option>
                            <option value="bank_transfer">Virement bancaire</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="paypal">PayPal</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="account_details" class="form-label">Détails du compte</label>
                        <textarea class="form-control" id="account_details" name="account_details" rows="3" 
                                  placeholder="Numéro de compte, nom de la banque, etc." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Soumettre la demande</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Code copié dans le presse-papiers !');
    });
}

function updateProfile() {
    const modal = new bootstrap.Modal(document.getElementById('updateProfileModal'));
    modal.show();
}

function requestPayout() {
    const modal = new bootstrap.Modal(document.getElementById('requestPayoutModal'));
    modal.show();
}

function generateLink(courseId) {
    fetch('/affiliate/generate-link', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ content_id: courseId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show link in a modal or copy to clipboard
            navigator.clipboard.writeText(data.url).then(function() {
                alert('Lien d\'affiliation copié dans le presse-papiers !');
            });
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue.');
    });
}

// Update profile form
document.getElementById('updateProfileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/affiliate/update-profile', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue.');
    });
});

// Request payout form
document.getElementById('requestPayoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/affiliate/request-payout', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Demande de paiement soumise avec succès !');
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue.');
    });
});
</script>
@endpush

@push('styles')
<style>
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

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
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