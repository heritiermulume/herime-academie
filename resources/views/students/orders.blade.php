@extends('students.admin.layout')

@section('admin-title', 'Mes commandes')
@section('admin-subtitle', 'Consultez vos achats, états de paiement et accès à vos cours.')

@section('admin-actions')
    <a href="{{ route('courses.index') }}" class="admin-btn primary">
        <i class="fas fa-shopping-bag me-2"></i>Passer une nouvelle commande
    </a>
@endsection

@section('admin-content')
@php
    $statusLabels = [
        'all' => 'Tous les statuts',
        'pending' => 'En attente',
        'confirmed' => 'Confirmée',
        'paid' => 'Payée',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
    ];
@endphp

<div class="student-orders">
    <div class="student-orders__summary">
        <div class="order-summary-card">
            <span class="order-summary-card__label">Commandes totales</span>
            <strong class="order-summary-card__value">{{ number_format($summary['total']) }}</strong>
            <small class="order-summary-card__hint">Toutes vos commandes passées</small>
        </div>
        <div class="order-summary-card">
            <span class="order-summary-card__label">Commandes payées</span>
            <strong class="order-summary-card__value">{{ number_format($summary['paid'] + $summary['completed']) }}</strong>
            <small class="order-summary-card__hint text-success">Cours accessibles immédiatement</small>
        </div>
        <div class="order-summary-card">
            <span class="order-summary-card__label">Total dépensé</span>
            <strong class="order-summary-card__value">
                {{ \App\Helpers\CurrencyHelper::formatWithSymbol($summary['total_spent'] ?? 0) }}
            </strong>
            <small class="order-summary-card__hint">Somme des commandes payées</small>
        </div>
        <div class="order-summary-card">
            <span class="order-summary-card__label">Dernière commande</span>
            @if($summary['last_order'] ?? null)
                <strong class="order-summary-card__value">
                    {{ optional($summary['last_order']->created_at)->format('d/m/Y') }}
                </strong>
                <small class="order-summary-card__hint">
                    {{ $statusLabels[$summary['last_order']->status] ?? ucfirst($summary['last_order']->status) }}
                </small>
            @else
                <strong class="order-summary-card__value">-</strong>
                <small class="order-summary-card__hint">Aucune commande passée</small>
            @endif
        </div>
    </div>

    <div class="admin-card">
        <form method="GET" class="student-orders__filters">
            <div class="filters-group">
                <div class="filters-field">
                    <label for="order-search" class="filters-label">Rechercher</label>
                    <div class="filters-input">
                        <i class="fas fa-search"></i>
                        <input type="text" id="order-search" name="q" placeholder="Numéro de commande, référence..."
                               value="{{ $search }}">
                    </div>
                </div>
                <div class="filters-field">
                    <label for="order-status" class="filters-label">Statut</label>
                    <select id="order-status" name="status">
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="filters-actions">
                <button type="submit" class="admin-btn primary sm">
                    <i class="fas fa-filter me-2"></i>Filtrer
                </button>
                <a href="{{ route('orders.index') }}" class="admin-btn ghost sm">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <div class="student-orders__header">
            <div>
                <h3 class="admin-card__title">Historique de commandes</h3>
                <p class="admin-card__subtitle">
                    {{ $orders->total() }} commande(s)
                    @if($status !== 'all')
                        · Filtre « {{ $statusLabels[$status] ?? $status }} »
                    @endif
                </p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="admin-btn soft">
                <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
            </a>
        </div>

        @if($orders->isEmpty())
            <div class="admin-empty-state">
                <i class="fas fa-shopping-basket"></i>
                <p>Aucune commande trouvée avec ces critères.</p>
                <a href="{{ route('courses.index') }}" class="admin-btn primary sm mt-3">
                    Explorer des cours
                </a>
            </div>
        @else
            <div class="student-orders__list">
                @foreach($orders as $order)
                    <article class="student-order-card">
                        <div class="student-order-card__header">
                            <div>
                                <span class="student-order-card__number">Commande {{ $order->order_number }}</span>
                                <p class="student-order-card__date">
                                    Passée le {{ $order->created_at->format('d/m/Y à H:i') }}
                                </p>
                            </div>
                            <span class="admin-badge {{ in_array($order->status, ['paid', 'completed']) ? 'success' : ($order->status === 'pending' ? 'warning' : 'info') }}">
                                <i class="fas fa-circle"></i>
                                {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                            </span>
                        </div>
                        <div class="student-order-card__body">
                            <div class="student-order-card__amount">
                                <span>Montant total</span>
                                <strong>{{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->total_amount ?? $order->total ?? 0) }}</strong>
                            </div>
                            <div class="student-order-card__meta">
                                <span><i class="fas fa-credit-card me-1"></i>{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Non spécifié')) }}</span>
                                <span><i class="fas fa-layer-group me-1"></i>{{ $order->order_items ? count($order->order_items) : $order->enrollments->count() }} cours</span>
                                @if($order->payment_reference)
                                    <span><i class="fas fa-hashtag me-1"></i>{{ $order->payment_reference }}</span>
                                @endif
                            </div>
                            @if($order->enrollments->isNotEmpty())
                                <div class="student-order-card__courses">
                                    @foreach($order->enrollments->take(3) as $enrollment)
                                        <span>{{ $enrollment->course->title ?? 'Cours supprimé' }}</span>
                                    @endforeach
                                    @if($order->enrollments->count() > 3)
                                        <span class="text-muted">+ {{ $order->enrollments->count() - 3 }} autre(s)</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="student-order-card__actions">
                            <a href="{{ route('orders.show', $order) }}" class="admin-btn ghost sm">
                                <i class="fas fa-eye me-1"></i>Détails
                            </a>
                            @if(in_array($order->status, ['paid', 'completed']) && $order->enrollments->isNotEmpty())
                                <a href="{{ route('student.courses.learn', optional($order->enrollments->first()->course)->slug) }}"
                                   class="admin-btn primary sm">
                                    <i class="fas fa-play me-1"></i>Commencer un cours
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="student-orders__pagination">
                {{ $orders->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .admin-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        padding: 0.65rem 1.2rem;
        border: 1px solid transparent;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.2s ease;
        color: inherit;
    }

    .admin-btn.primary {
        background: linear-gradient(90deg, #2563eb, #4f46e5);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(37, 99, 235, 0.55);
    }

    .admin-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(37, 99, 235, 0.45);
    }

    .admin-btn.ghost {
        border-color: rgba(37, 99, 235, 0.18);
        color: #2563eb;
        background: transparent;
    }

    .admin-btn.soft {
        border-color: rgba(148, 163, 184, 0.4);
        background: rgba(148, 163, 184, 0.12);
        color: #0f172a;
        padding: 0.55rem 1rem;
        font-size: 0.85rem;
    }

    .admin-btn.sm {
        padding: 0.5rem 0.9rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
    }

    .student-orders {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }

    .student-orders__summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.35rem;
    }

    .order-summary-card {
        padding: 1.35rem 1.45rem;
        border-radius: 1.15rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        background: #ffffff;
        box-shadow: 0 18px 45px -35px rgba(15, 23, 42, 0.18);
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .order-summary-card__label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 600;
    }

    .order-summary-card__value {
        font-size: 1.65rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }

    .order-summary-card__hint {
        font-size: 0.82rem;
        color: #94a3b8;
    }

    .student-orders__filters {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .filters-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
    }

    .filters-field {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filters-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 600;
    }

    .filters-input {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid rgba(148, 163, 184, 0.4);
        border-radius: 0.85rem;
        padding: 0.5rem 0.85rem;
        background: rgba(248, 250, 252, 0.7);
    }

    .filters-input input {
        border: none;
        background: transparent;
        width: 100%;
        outline: none;
        font-size: 0.95rem;
        color: #0f172a;
    }

    .filters-input i {
        color: #94a3b8;
    }

    .filters-field select {
        border: 1px solid rgba(148, 163, 184, 0.4);
        border-radius: 0.85rem;
        padding: 0.55rem 0.85rem;
        background: rgba(248, 250, 252, 0.7);
        font-size: 0.95rem;
    }

    .filters-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .student-orders__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .student-orders__list {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .student-order-card {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        padding: 1.35rem 1.5rem;
        border-radius: 1.2rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 22px 55px -45px rgba(15, 23, 42, 0.28);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .student-order-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 28px 60px -40px rgba(37, 99, 235, 0.35);
    }

    .student-order-card__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .student-order-card__number {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0f172a;
    }

    .student-order-card__date {
        margin: 0.35rem 0 0;
        font-size: 0.85rem;
        color: #64748b;
    }

    .student-order-card__body {
        display: grid;
        gap: 1rem;
    }

    .student-order-card__amount {
        display: flex;
        gap: 0.75rem;
        align-items: baseline;
    }

    .student-order-card__amount span {
        font-size: 0.82rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 600;
    }

    .student-order-card__amount strong {
        font-size: 1.2rem;
        color: #10b981;
        font-weight: 700;
    }

    .student-order-card__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        font-size: 0.82rem;
        color: #475569;
    }

    .student-order-card__courses {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        font-size: 0.82rem;
        color: #64748b;
    }

    .student-order-card__courses span {
        background: rgba(148, 163, 184, 0.15);
        color: #475569;
        padding: 0.35rem 0.65rem;
        border-radius: 0.65rem;
        font-weight: 600;
    }

    .student-order-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .student-orders__pagination {
        margin-top: 1.75rem;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .filters-actions {
            flex-direction: column;
        }

        .student-orders__header {
            flex-direction: column;
            align-items: stretch;
        }

        .student-order-card__header {
            flex-direction: column;
            align-items: flex-start;
        }

        .student-order-card__meta {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 640px) {
        .admin-btn {
            width: 100%;
        }
    }
</style>
@endpush

