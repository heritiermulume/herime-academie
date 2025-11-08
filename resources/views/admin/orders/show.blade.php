@extends('layouts.admin')

@section('title', 'Détails de la commande')
@section('admin-title', 'Détails de la commande')
@section('admin-subtitle', 'Analysez et gérez chaque étape du cycle de vie de la commande')
@section('admin-actions')
    <a href="{{ route('admin.orders.index') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour aux commandes
    </a>
@endsection

@section('admin-content')
    <div class="admin-panel">
        <div class="admin-panel__body admin-panel__body--padded">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
                <div>
                    <span class="admin-chip admin-chip--info">#{{ $order->order_number }}</span>
                    <span class="admin-chip admin-chip--neutral">Créée le {{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if($order->status === 'pending')
                        <button class="btn btn-outline-primary" onclick="confirmOrder({{ $order->id }})"><i class="fas fa-check me-2"></i>Confirmer</button>
                        <button class="btn btn-outline-danger" onclick="cancelOrder({{ $order->id }})"><i class="fas fa-times me-2"></i>Annuler</button>
                    @elseif($order->status === 'confirmed')
                        <button class="btn btn-outline-primary" onclick="markAsPaid({{ $order->id }})"><i class="fas fa-credit-card me-2"></i>Marquer payée</button>
                    @elseif($order->status === 'paid')
                        <button class="btn btn-outline-success" onclick="markAsCompleted({{ $order->id }})"><i class="fas fa-check-double me-2"></i>Terminer</button>
                    @endif
                </div>
            </div>

            <div class="admin-form-grid admin-form-grid--two">
                <div class="admin-form-card">
                    <h5><i class="fas fa-info-circle me-2"></i>Informations de la commande</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Numéro de commande</label>
                                <div class="fw-bold">{{ $order->order_number }}</div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="text-muted small">Date de commande</label>
                                <div>{{ $order->created_at->format('d/m/Y à H:i') }}</div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="text-muted small">Mode de paiement</label>
                                <div>
                                    <span class="badge bg-light text-dark">
                                        {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Non spécifié')) }}
                                    </span>
                                </div>
                            </div>
                            @if($order->payment_provider)
                            <div class="info-item mb-3">
                                <label class="text-muted small">Fournisseur</label>
                                <div class="fw-bold text-uppercase">{{ $order->payment_provider }}</div>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Statut</label>
                                <div>
                                    <span class="badge order-status-{{ $order->status }} fs-6">
                                        @switch($order->status)
                                            @case('pending')
                                                <i class="fas fa-clock me-1"></i>En attente
                                                @break
                                            @case('confirmed')
                                                <i class="fas fa-check-circle me-1"></i>Confirmée
                                                @break
                                            @case('paid')
                                                <i class="fas fa-credit-card me-1"></i>Payée
                                                @break
                                            @case('completed')
                                                <i class="fas fa-check-double me-1"></i>Terminée
                                                @break
                                            @case('cancelled')
                                                <i class="fas fa-times-circle me-1"></i>Annulée
                                                @break
                                            @default
                                                {{ ucfirst($order->status) }}
                                        @endswitch
                                    </span>
                                </div>
                            </div>
                            @if($order->payment_reference)
                                <div class="info-item mb-3">
                                    <label class="text-muted small">Référence de paiement</label>
                                    <div class="fw-bold">{{ $order->payment_reference }}</div>
                                </div>
                            @endif
                            @if($order->payment_amount)
                                <div class="info-item mb-3">
                                    <label class="text-muted small">Montant du paiement (devise fournisseur)</label>
                                    <div>
                                        <span class="fw-bold">{{ number_format((float)$order->payment_amount, 2) }} {{ $order->payment_currency }}</span>
                                        @if(!is_null($order->provider_fee))
                                            <span class="ms-2 text-muted small">· Frais: {{ number_format((float)$order->provider_fee, 2) }} {{ $order->provider_fee_currency ?? $order->payment_currency }}</span>
                                        @endif
                                        @if(!is_null($order->net_total))
                                            <span class="ms-2 text-muted small">· Net: {{ number_format((float)$order->net_total, 2) }} {{ $order->payment_currency }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if($order->exchange_rate)
                                <div class="info-item mb-3">
                                    <label class="text-muted small">Taux de change (approx.)</label>
                                    <div class="text-muted small">1 {{ $order->currency }} ≈ {{ number_format((float)$order->exchange_rate, 6) }} {{ $order->payment_currency }}</div>
                                </div>
                            @endif
                            @if($order->confirmed_at)
                                <div class="info-item mb-3">
                                    <label class="text-muted small">Date de confirmation</label>
                                    <div>{{ $order->confirmed_at ? $order->confirmed_at->format('d/m/Y à H:i') : 'Non confirmée' }}</div>
                                </div>
                            @endif
                            @if($order->paid_at)
                                <div class="info-item mb-3">
                                    <label class="text-muted small">Date de paiement</label>
                                    <div>{{ $order->paid_at ? $order->paid_at->format('d/m/Y à H:i') : 'Non payée' }}</div>
                                </div>
                            @endif
                            @php($lastPayment = $order->payments()->latest()->first())
                            @if($lastPayment && $lastPayment->failure_reason)
                                <div class="info-item mb-3">
                                    <label class="text-muted small">Raison de l'échec</label>
                                    <div class="text-danger small">{{ $lastPayment->failure_reason }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="admin-form-card">
                    <h5><i class="fas fa-user me-2"></i>Informations client</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Nom complet</label>
                                <div class="fw-bold">{{ $order->user->name }}</div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="text-muted small">Email</label>
                                <div>{{ $order->user->email }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Membre depuis</label>
                                <div>{{ $order->user->created_at->format('d/m/Y') }}</div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="text-muted small">ID utilisateur</label>
                                <div class="fw-bold">#{{ $order->user->id }}</div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3">Détails payeur</h6>
                    <div class="row">
                        <div class="col-md-6">
                            @if($order->payer_phone)
                                <div class="info-item mb-2">
                                    <label class="text-muted small">Téléphone payeur</label>
                                    <div>{{ $order->payer_phone }}</div>
                                </div>
                            @endif
                            @if($order->payer_country)
                                <div class="info-item mb-2">
                                    <label class="text-muted small">Pays payeur</label>
                                    <div>{{ $order->payer_country }}</div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($order->customer_ip)
                                <div class="info-item mb-2">
                                    <label class="text-muted small">Adresse IP</label>
                                    <div>{{ $order->customer_ip }}</div>
                                </div>
                            @endif
                            @if($order->user_agent)
                                <div class="info-item mb-2">
                                    <label class="text-muted small">User-Agent</label>
                                    <div class="small text-break">{{ $order->user_agent }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    @if($order->billing_info)
                        <hr>
                        <h6 class="text-muted mb-3">Informations de facturation</h6>
                        <div class="row">
                            <div class="col-md-6">
                                @if(isset($order->billing_info['first_name']))
                                    <div class="info-item mb-2">
                                        <label class="text-muted small">Prénom</label>
                                        <div>{{ $order->billing_info['first_name'] }}</div>
                                    </div>
                                @endif
                                @if(isset($order->billing_info['last_name']))
                                    <div class="info-item mb-2">
                                        <label class="text-muted small">Nom</label>
                                        <div>{{ $order->billing_info['last_name'] }}</div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if(isset($order->billing_info['email']))
                                    <div class="info-item mb-2">
                                        <label class="text-muted small">Email</label>
                                        <div>{{ $order->billing_info['email'] }}</div>
                                    </div>
                                @endif
                                @if(isset($order->billing_info['phone']))
                                    <div class="info-item mb-2">
                                        <label class="text-muted small">Téléphone</label>
                                        <div>{{ $order->billing_info['phone'] }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="admin-panel__body mt-4">
                <h5 class="mb-3"><i class="fas fa-list me-2"></i>Cours inclus</h5>
                <div class="admin-table">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Cours</th>
                                    <th>Quantité</th>
                                    <th>Prix</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php($relItems = $order->orderItems ?? collect())
                                @if($relItems->count() > 0)
                                    @foreach($relItems as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="course-icon me-3">
                                                        <i class="fas fa-play-circle fa-2x text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">{{ optional($item->course)->title ?? 'Cours supprimé' }}</h6>
                                                        <p class="text-muted mb-0 small">
                                                            <i class="fas fa-user me-1"></i>{{ optional(optional($item->course)->instructor)->name ?? 'Instructeur inconnu' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">1</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-bold text-success d-flex align-items-center justify-content-end gap-2 amount-icon">
                                                    <span>{{ \App\Helpers\CurrencyHelper::formatWithSymbol($item->total ?? $item->price ?? 0, $order->currency) }}</span>
                                                    @if($item->course)
                                                        <a href="{{ route('courses.show', $item->course->slug) }}" class="text-primary text-decoration-none small" target="_blank" title="Voir le cours">
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($order->order_items && count($order->order_items) > 0)
                                    @foreach($order->order_items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="course-icon me-3">
                                                        <i class="fas fa-play-circle fa-2x text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">{{ $item['course_title'] ?? 'Cours supprimé' }}</h6>
                                                        <p class="text-muted mb-0 small">
                                                            <i class="fas fa-user me-1"></i>{{ $item['instructor_name'] ?? 'Instructeur inconnu' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">Quantité: {{ $item['quantity'] ?? 1 }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-bold text-success">
                                                    {{ \App\Helpers\CurrencyHelper::formatWithSymbol($item['price'] ?? 0, $order->currency) }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                            <p>Aucun cours trouvé pour cette commande.</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="admin-form-grid admin-form-grid--two mt-4">
                <div class="admin-form-card">
                    <h5><i class="fas fa-wallet me-2"></i>Résumé du paiement</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="summary-item">
                                <span>Sous-total</span>
                                <span>{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->subtotal ?? $order->total_amount, $order->currency) }}</span>
                            </div>
                            <div class="summary-item">
                                <span>Remise</span>
                                <span>{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->discount ?? 0, $order->currency) }}</span>
                            </div>
                            <div class="summary-item">
                                <span>Taxes</span>
                                <span>{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->tax ?? 0, $order->currency) }}</span>
                            </div>
                            <hr>
                            <div class="summary-item d-flex justify-content-between fw-bold fs-5">
                                <span>Total</span>
                                <span class="text-success">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->total_amount ?? $order->total, $order->currency) }}</span>
                            </div>
                            @if($order->payment_amount)
                                <div class="mt-3">
                                    <div class="small text-muted mb-1">Montants en devise de paiement</div>
                                    <div class="d-flex justify-content-between small">
                                        <span>Brut</span>
                                        <span>{{ number_format((float)$order->payment_amount, 2) }} {{ $order->payment_currency }}</span>
                                    </div>
                                    @if(!is_null($order->provider_fee))
                                    <div class="d-flex justify-content-between small">
                                        <span>Frais</span>
                                        <span>- {{ number_format((float)$order->provider_fee, 2) }} {{ $order->provider_fee_currency ?? $order->payment_currency }}</span>
                                    </div>
                                    @endif
                                    @if(!is_null($order->net_total))
                                    <div class="d-flex justify-content-between small fw-bold">
                                        <span>Net reçu</span>
                                        <span>{{ number_format((float)$order->net_total, 2) }} {{ $order->payment_currency }}</span>
                                    </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="admin-form-card">
                    <h5><i class="fas fa-cog me-2"></i>Actions administrateur</h5>
                    <div class="list-group">
                        <a href="{{ route('admin.orders.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-arrow-left me-2"></i>Retour aux commandes
                        </a>
                        @if($order->status === 'pending')
                            <button class="list-group-item list-group-item-action" onclick="confirmOrder({{ $order->id }})">
                                <i class="fas fa-check me-2"></i>Confirmer la commande
                            </button>
                            <button class="list-group-item list-group-item-action" onclick="cancelOrder({{ $order->id }})">
                                <i class="fas fa-times me-2"></i>Annuler la commande
                            </button>
                        @elseif($order->status === 'confirmed')
                            <button class="list-group-item list-group-item-action" onclick="markAsPaid({{ $order->id }})">
                                <i class="fas fa-credit-card me-2"></i>Marquer comme payée
                            </button>
                        @elseif($order->status === 'paid')
                            <button class="list-group-item list-group-item-action" onclick="markAsCompleted({{ $order->id }})">
                                <i class="fas fa-check-double me-2"></i>Marquer comme terminée
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Modals -->
    @include('admin.orders.partials.action-modals')

    <style>
    .order-status-pending {
        background-color: #ffc107 !important;
        color: #000 !important;
    }

    .order-status-confirmed {
        background-color: #17a2b8 !important;
        color: #fff !important;
    }

    .order-status-paid {
        background-color: #28a745 !important;
        color: #fff !important;
    }

    .order-status-completed {
        background-color: #6f42c1 !important;
        color: #fff !important;
    }

    .order-status-cancelled {
        background-color: #dc3545 !important;
        color: #fff !important;
    }

    .info-item label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .order-item:last-child {
        border-bottom: none !important;
    }

    .enrollment-item {
        transition: box-shadow 0.2s ease;
    }

    .enrollment-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .summary-item {
        font-size: 0.9rem;
    }

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .timeline-marker {
        position: absolute;
        left: -2rem;
        top: 0.25rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #e9ecef;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
    }

    @media (max-width: 576px) {
        .order-items .order-item .amount-icon {
            justify-content: space-between !important;
            gap: 10px !important;
        }
        .order-items .order-item .amount-icon a {
            padding: 6px; /* plus grande zone tactile */
            border-radius: 6px;
        }
        .order-items .order-item .course-icon i {
            font-size: 1.6rem; /* icône un peu plus petite pour gagner de la place */
        }
    }
    </style>
@endsection
