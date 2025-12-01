@extends('layouts.admin')

@section('title', 'Détails de la commande')
@section('admin-title', 'Détails de la commande')
@section('admin-subtitle', 'Analysez et gérez chaque étape du cycle de vie de la commande')
@section('admin-actions')
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('admin-content')
    <div class="row g-4">
        <div class="col-md-8">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-shopping-cart me-2"></i>Informations de la commande
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Numéro de commande</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-primary fs-6">#{{ $order->order_number }}</span>
                        </dd>

                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">
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
                        </dd>

                        <dt class="col-sm-4">Date de commande</dt>
                        <dd class="col-sm-8">
                            <i class="fas fa-calendar me-2"></i>
                            {{ $order->created_at->format('d/m/Y à H:i') }}
                        </dd>

                        <dt class="col-sm-4">Mode de paiement</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-secondary">
                                {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Non spécifié')) }}
                            </span>
                        </dd>

                        @if($order->payment_provider)
                            <dt class="col-sm-4">Fournisseur</dt>
                            <dd class="col-sm-8">{{ strtoupper($order->payment_provider) }}</dd>
                        @endif

                        @if($order->payment_reference)
                            <dt class="col-sm-4">Référence de paiement</dt>
                            <dd class="col-sm-8">{{ $order->payment_reference }}</dd>
                        @endif

                        @if($order->payment_amount)
                            <dt class="col-sm-4">Montant du paiement</dt>
                            <dd class="col-sm-8">
                                <strong>{{ number_format((float)$order->payment_amount, 2) }} {{ $order->payment_currency }}</strong>
                                @if(!is_null($order->provider_fee))
                                    <br><small class="text-muted">Frais: {{ number_format((float)$order->provider_fee, 2) }} {{ $order->provider_fee_currency ?? $order->payment_currency }}</small>
                                @endif
                                @if(!is_null($order->net_total))
                                    <br><small class="text-muted">Net: {{ number_format((float)$order->net_total, 2) }} {{ $order->payment_currency }}</small>
                                @endif
                            </dd>
                        @endif

                        @if($order->exchange_rate)
                            <dt class="col-sm-4">Taux de change</dt>
                            <dd class="col-sm-8">
                                <small class="text-muted">1 {{ $order->currency }} ≈ {{ number_format((float)$order->exchange_rate, 6) }} {{ $order->payment_currency }}</small>
                            </dd>
                        @endif

                        @if($order->confirmed_at)
                            <dt class="col-sm-4">Date de confirmation</dt>
                            <dd class="col-sm-8">{{ $order->confirmed_at->format('d/m/Y à H:i') }}</dd>
                        @endif

                        @if($order->paid_at)
                            <dt class="col-sm-4">Date de paiement</dt>
                            <dd class="col-sm-8">{{ $order->paid_at->format('d/m/Y à H:i') }}</dd>
                        @endif

                        @php($lastPayment = $order->payments()->latest()->first())
                        @if($lastPayment && $lastPayment->failure_reason)
                            <dt class="col-sm-4">Raison de l'échec</dt>
                            <dd class="col-sm-8">
                                <span class="text-danger">{{ $lastPayment->failure_reason }}</span>
                            </dd>
                        @endif
                    </dl>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-user me-2"></i>Informations client
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div>
                            <h5 class="mb-1">{{ $order->user->name }}</h5>
                            <p class="text-muted mb-0">
                                <i class="fas fa-envelope me-2"></i>{{ $order->user->email }}
                            </p>
                        </div>
                    </div>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Membre depuis</dt>
                        <dd class="col-sm-8">{{ $order->user->created_at->format('d/m/Y') }}</dd>

                        <dt class="col-sm-4">ID utilisateur</dt>
                        <dd class="col-sm-8">#{{ $order->user->id }}</dd>

                        @if($order->payer_phone)
                            <dt class="col-sm-4">Téléphone payeur</dt>
                            <dd class="col-sm-8">{{ $order->payer_phone }}</dd>
                        @endif

                        @if($order->payer_country)
                            <dt class="col-sm-4">Pays payeur</dt>
                            <dd class="col-sm-8">{{ $order->payer_country }}</dd>
                        @endif

                        @if($order->customer_ip)
                            <dt class="col-sm-4">Adresse IP</dt>
                            <dd class="col-sm-8">{{ $order->customer_ip }}</dd>
                        @endif

                        @if($order->user_agent)
                            <dt class="col-sm-4">User-Agent</dt>
                            <dd class="col-sm-8">
                                <small class="text-break">{{ $order->user_agent }}</small>
                            </dd>
                        @endif
                    </dl>

                    @if($order->billing_info)
                        <hr class="my-3">
                        <h6 class="text-muted mb-3">Informations de facturation</h6>
                        <dl class="row mb-0">
                            @if(isset($order->billing_info['first_name']))
                                <dt class="col-sm-4">Prénom</dt>
                                <dd class="col-sm-8">{{ $order->billing_info['first_name'] }}</dd>
                            @endif
                            @if(isset($order->billing_info['last_name']))
                                <dt class="col-sm-4">Nom</dt>
                                <dd class="col-sm-8">{{ $order->billing_info['last_name'] }}</dd>
                            @endif
                            @if(isset($order->billing_info['email']))
                                <dt class="col-sm-4">Email</dt>
                                <dd class="col-sm-8">{{ $order->billing_info['email'] }}</dd>
                            @endif
                            @if(isset($order->billing_info['phone']))
                                <dt class="col-sm-4">Téléphone</dt>
                                <dd class="col-sm-8">{{ $order->billing_info['phone'] }}</dd>
                            @endif
                        </dl>
                    @endif
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-list me-2"></i>Cours inclus
                    </h3>
                </div>
                <div class="admin-panel__body">
                    @php($relItems = $order->orderItems ?? collect())
                    @if($relItems->count() > 0)
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
                                    @foreach($relItems as $item)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ optional($item->course)->title ?? 'Cours supprimé' }}</strong>
                                                    @if($item->course && $item->course->instructor)
                                                        <br><small class="text-muted">par {{ $item->course->instructor->name }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">1</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-bold text-success">
                                                    {{ \App\Helpers\CurrencyHelper::formatWithSymbol($item->total ?? $item->price ?? 0, $order->currency) }}
                                                </div>
                                                @if($item->course)
                                                    <a href="{{ route('courses.show', $item->course->slug) }}" class="btn btn-sm btn-outline-primary mt-1" target="_blank">
                                                        <i class="fas fa-external-link-alt me-1"></i>Voir le cours
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Aucun cours trouvé pour cette commande.</p>
                    @endif
                </div>
            </section>
        </div>

        <div class="col-md-4">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-wallet me-2"></i>Résumé du paiement
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Sous-total</dt>
                        <dd class="col-sm-6 text-end">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->subtotal ?? $order->total_amount, $order->currency) }}</dd>

                        <dt class="col-sm-6">Remise</dt>
                        <dd class="col-sm-6 text-end">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->discount ?? 0, $order->currency) }}</dd>

                        <dt class="col-sm-6">Taxes</dt>
                        <dd class="col-sm-6 text-end">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->tax ?? 0, $order->currency) }}</dd>

                        <dt class="col-sm-6"><strong>Total</strong></dt>
                        <dd class="col-sm-6 text-end">
                            <strong class="text-success fs-5">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->total_amount ?? $order->total, $order->currency) }}</strong>
                        </dd>
                    </dl>

                    @if($order->payment_amount)
                        <hr class="my-3">
                        <h6 class="text-muted mb-2">Montants en devise de paiement</h6>
                        <dl class="row mb-0">
                            <dt class="col-sm-6">Brut</dt>
                            <dd class="col-sm-6 text-end">{{ number_format((float)$order->payment_amount, 2) }} {{ $order->payment_currency }}</dd>
                            @if(!is_null($order->provider_fee))
                                <dt class="col-sm-6">Frais</dt>
                                <dd class="col-sm-6 text-end">- {{ number_format((float)$order->provider_fee, 2) }} {{ $order->provider_fee_currency ?? $order->payment_currency }}</dd>
                            @endif
                            @if(!is_null($order->net_total))
                                <dt class="col-sm-6"><strong>Net reçu</strong></dt>
                                <dd class="col-sm-6 text-end"><strong>{{ number_format((float)$order->net_total, 2) }} {{ $order->payment_currency }}</strong></dd>
                            @endif
                        </dl>
                    @endif
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-cog me-2"></i>Actions administrateur
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <div class="d-grid gap-2">
                        @if($order->status === 'pending')
                            <button class="btn btn-outline-primary" onclick="confirmOrder({{ $order->id }})">
                                <i class="fas fa-check me-2"></i>Confirmer la commande
                            </button>
                            <button class="btn btn-outline-danger" onclick="cancelOrder({{ $order->id }})">
                                <i class="fas fa-times me-2"></i>Annuler la commande
                            </button>
                        @elseif($order->status === 'confirmed')
                            <button class="btn btn-outline-primary" onclick="markAsPaid({{ $order->id }})">
                                <i class="fas fa-credit-card me-2"></i>Marquer comme payée
                            </button>
                        @elseif($order->status === 'paid')
                            <button class="btn btn-outline-success" onclick="markAsCompleted({{ $order->id }})">
                                <i class="fas fa-check-double me-2"></i>Marquer comme terminée
                            </button>
                        @endif
                        <button class="btn btn-danger" onclick="deleteOrder({{ $order->id }})">
                            <i class="fas fa-trash me-2"></i>Supprimer définitivement
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Action Modals -->
    @include('admin.orders.partials.action-modals')
@endsection

@push('styles')
<style>
/* Styles identiques à analytics */
.admin-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-card__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 16px 16px 0 0;
}

/* Réduire l'espace au-dessus du contenu sur desktop */
@media (min-width: 992px) {
    .admin-card__header .admin-card__title.mb-1 {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-card__header {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }
}

.admin-card__title {
    margin: 0;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
}

.admin-card__body {
    padding: 1.25rem;
}

/* Styles pour admin-panel - identiques à analytics */
.admin-panel {
    margin-bottom: 2rem;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-panel__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-panel__header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.admin-panel__body {
    padding: 1rem;
}

/* Padding légèrement réduit sur desktop */
@media (min-width: 992px) {
    .admin-panel__body {
        padding: 0.875rem 1rem;
    }
}

/* Corriger le chevauchement des boutons dans la carte Informations du certificat */
.admin-panel__body dl.row dd {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.admin-panel__body dl.row dd .badge {
    flex-shrink: 0;
}

.admin-panel__body dl.row dd .btn,
.admin-panel__body dl.row dd button {
    flex-shrink: 0;
    white-space: nowrap;
}

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

/* Styles responsives pour les paddings et margins - identiques à analytics */
@media (max-width: 991.98px) {
    /* Réduire les paddings et margins sur tablette */
    .admin-panel {
        margin-bottom: 1rem;
    }
    
    .admin-panel__body {
        padding: 0 !important;
    }
    
    .admin-panel__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-panel__header h3 {
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.5rem;
        --bs-gutter-y: 0.5rem;
    }
    
    .admin-card {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-card__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-card__body {
        padding: 0.5rem;
    }
}

@media (max-width: 767.98px) {
    /* Réduire encore plus les paddings et margins sur mobile */
    .admin-panel {
        margin-bottom: 0.75rem;
    }
    
    .admin-panel__body {
        padding: 1.25rem !important;
    }
    
    .admin-panel__header {
        padding: 0.375rem 0.5rem;
    }
    
    .admin-panel__header h3 {
        font-size: 0.95rem;
        margin-bottom: 0.125rem;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.375rem;
        --bs-gutter-y: 0.375rem;
    }
    
    .admin-card {
        margin-bottom: 0.5rem !important;
    }
    
    /* Garder le même design de carte que sur desktop - mêmes tailles */
    .admin-card__header {
        padding: 1rem 1.25rem !important;
    }
    
    .admin-card__body {
        padding: 1.25rem !important;
    }
    
    /* Empiler les boutons sur mobile dans la carte Informations du certificat */
    .admin-panel__body dl.row dd .btn,
    .admin-panel__body dl.row dd button {
        flex: 1 1 auto;
        min-width: 120px;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
}
</style>
@endpush
