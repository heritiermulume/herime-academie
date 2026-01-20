@extends('customers.admin.layout')

@section('admin-title', 'Mes commandes')
@section('admin-subtitle', 'Consultez vos achats, états de paiement et accès à vos contenus.')

@section('admin-actions')
    <a href="{{ route('contents.index') }}" class="admin-btn primary">
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

<section class="admin-panel admin-panel--main">
    <div class="admin-panel__body">
        <div class="admin-stats-grid">
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Commandes totales</p>
                <p class="admin-stat-card__value">{{ number_format($summary['total']) }}</p>
                <p class="admin-stat-card__muted">Toutes vos commandes passées</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Commandes payées</p>
                <p class="admin-stat-card__value">{{ number_format($summary['paid'] + $summary['completed']) }}</p>
                <p class="admin-stat-card__muted">Contenus accessibles immédiatement</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Total dépensé</p>
                <p class="admin-stat-card__value">
                    {{ \App\Helpers\CurrencyHelper::formatWithSymbol($summary['total_spent'] ?? 0) }}
                </p>
                <p class="admin-stat-card__muted">Somme des commandes payées</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Dernière commande</p>
                @if($summary['last_order'] ?? null)
                    <p class="admin-stat-card__value">
                        {{ optional($summary['last_order']->created_at)->format('d/m/Y') }}
                    </p>
                    <p class="admin-stat-card__muted">
                        {{ $statusLabels[$summary['last_order']->status] ?? ucfirst($summary['last_order']->status) }}
                    </p>
                @else
                    <p class="admin-stat-card__value">-</p>
                    <p class="admin-stat-card__muted">Aucune commande passée</p>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="admin-panel">
    <div class="admin-panel__header">
        <h3>
            <i class="fas fa-filter me-2"></i>Filtres
        </h3>
    </div>
    <div class="admin-panel__body">
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
</section>

<section class="admin-panel">
    <div class="admin-panel__header">
        <h3>
            <i class="fas fa-shopping-bag me-2"></i>Historique de commandes
        </h3>
        <div class="admin-panel__actions">
            <a href="{{ route('customer.dashboard') }}" class="admin-btn soft">
                <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
            </a>
        </div>
    </div>
    <div class="admin-panel__body">
        <p class="mb-3" style="color: var(--student-muted); font-size: 0.9rem;">
            {{ $orders->total() }} commande(s)
            @if($status !== 'all')
                · Filtre « {{ $statusLabels[$status] ?? $status }} »
            @endif
        </p>

        @if($orders->isEmpty())
            <div class="admin-empty-state">
                <i class="fas fa-shopping-basket"></i>
                <p>Aucune commande trouvée avec ces critères.</p>
                <a href="{{ route('contents.index') }}" class="admin-btn primary sm mt-3">
                    Explorer des contenus
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
                                <span><i class="fas fa-layer-group me-1"></i>{{ $order->orderItems->count() > 0 ? $order->orderItems->count() : $order->enrollments->count() }} contenus</span>
                                @if($order->payment_reference)
                                    <span><i class="fas fa-hashtag me-1"></i>{{ $order->payment_reference }}</span>
                                @endif
                            </div>
                            @if($order->enrollments->isNotEmpty())
                                <div class="student-order-card__courses">
                                    @foreach($order->enrollments->take(3) as $enrollment)
                                        <span>{{ $enrollment->course->title ?? 'Contenu supprimé' }}</span>
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
                                @php
                                    $firstCourse = optional($order->enrollments->first()->course);
                                @endphp
                                @if($firstCourse && $firstCourse->is_downloadable)
                                    <a href="{{ route('contents.show', $firstCourse->slug) }}" class="admin-btn primary sm">
                                        <i class="fas fa-eye me-1"></i>Voir le contenu
                                    </a>
                                @else
                                    <a href="{{ route('learning.course', $firstCourse->slug) }}"
                                       class="admin-btn success sm">
                                        <i class="fas fa-play me-1"></i>Commencer un contenu
                                    </a>
                                @endif
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <x-customer.pagination :paginator="$orders" :showInfo="true" itemName="commandes" />
        @endif
    </div>
</section>
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
        background: linear-gradient(90deg, var(--student-primary), #0b4f99);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(30, 58, 138, 0.55);
    }

    .admin-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(30, 58, 138, 0.45);
    }

    .admin-btn.success {
        background: linear-gradient(90deg, #22c55e, #16a34a);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(34, 197, 94, 0.55);
    }

    .admin-btn.success:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(34, 197, 94, 0.45);
        background: linear-gradient(90deg, #16a34a, #15803d);
    }

    .admin-btn.ghost {
        border-color: rgba(30, 58, 138, 0.3);
        color: #ffffff;
        background: var(--student-primary);
    }

    .admin-btn.ghost:hover {
        background: rgba(30, 58, 138, 0.9);
        border-color: var(--student-primary);
    }

    .admin-btn.soft {
        border-color: rgba(148, 163, 184, 0.4);
        background: rgba(148, 163, 184, 0.12);
        color: var(--student-primary-dark);
        padding: 0.55rem 1rem;
        font-size: 0.85rem;
    }

    .admin-btn.sm {
        padding: 0.5rem 0.9rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
    }

    .admin-btn.lg {
        padding: 0.82rem 1.65rem;
        font-size: 1.02rem;
        border-radius: 1rem;
    }

    .admin-btn.outline {
        border-color: rgba(30, 58, 138, 0.32);
        color: var(--student-primary);
        background: rgba(30, 58, 138, 0.08);
    }

    .admin-panel {
        margin-bottom: 1.5rem;
        background: var(--student-card-bg);
        border-radius: 1.25rem;
        box-shadow: 0 22px 45px -35px rgba(15, 23, 42, 0.25);
        border: 1px solid rgba(226, 232, 240, 0.7);
    }

    .admin-panel__header {
        padding: 1.25rem 1.75rem;
        background: linear-gradient(120deg, var(--student-primary) 0%, var(--student-primary-dark) 100%);
        color: #ffffff;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        align-items: center;
        border-radius: 1.25rem 1.25rem 0 0;
    }

    .admin-panel__header h2,
    .admin-panel__header h3,
    .admin-panel__header h4 {
        margin: 0;
        font-weight: 600;
        color: #ffffff;
    }

    .admin-panel__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .admin-panel__actions .admin-btn.soft {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .admin-panel__actions .admin-btn.soft:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.5);
    }
    
    .admin-panel__body {
        padding: 1.75rem;
    }

    .admin-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.25rem;
    }

    .admin-stat-card {
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.07) 0%, rgba(30, 58, 138, 0.15) 100%);
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        color: var(--student-primary-dark);
        border: 1px solid rgba(30, 58, 138, 0.1);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .admin-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 28px 60px -45px rgba(30, 58, 138, 0.35);
    }

    .admin-stat-card__label {
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.65rem;
        margin-bottom: 0.4rem;
        color: var(--student-primary);
        font-weight: 600;
    }

    .admin-stat-card__value {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        color: var(--student-primary-dark);
        line-height: 1.2;
    }

    .admin-stat-card__muted {
        margin-top: 0.25rem;
        color: var(--student-muted);
        font-size: 0.8rem;
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
        color: var(--student-muted);
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
        color: var(--student-primary-dark);
    }

    .filters-input i {
        color: var(--student-muted);
    }

    .filters-field select {
        border: 1px solid rgba(148, 163, 184, 0.4);
        border-radius: 0.85rem;
        padding: 0.55rem 0.85rem;
        background: rgba(248, 250, 252, 0.7);
        font-size: 0.95rem;
        color: var(--student-primary-dark);
    }

    .filters-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
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
        box-shadow: 0 28px 60px -40px rgba(30, 58, 138, 0.35);
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
        color: var(--student-primary-dark);
    }

    .student-order-card__date {
        margin: 0.35rem 0 0;
        font-size: 0.85rem;
        color: var(--student-muted);
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
        color: var(--student-accent);
        font-weight: 700;
    }

    .student-order-card__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        font-size: 0.82rem;
        color: var(--student-muted);
    }

    .student-order-card__courses {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        font-size: 0.82rem;
        color: var(--student-muted);
    }

    .student-order-card__courses span {
        background: rgba(30, 58, 138, 0.1);
        color: var(--student-primary-dark);
        padding: 0.35rem 0.65rem;
        border-radius: 0.65rem;
        font-weight: 600;
    }

    .student-order-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }


    @media (max-width: 991.98px) {
        .admin-panel__header {
            padding: 0.75rem 1rem;
        }

        .admin-panel__header h3 {
            font-size: 1rem;
        }

        .admin-panel__body {
            padding: 1rem;
        }

        .admin-stats-grid {
            gap: 0.5rem !important;
        }

        .admin-stat-card {
            padding: 0.75rem 0.875rem !important;
        }

        .admin-stat-card__value {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 767.98px) {
        .admin-panel {
            margin-bottom: 0.75rem;
        }

        .admin-panel--main .admin-panel__body {
            padding: 0.75rem 0.25rem !important;
        }

        .admin-panel__header {
            padding: 0.5rem 0.75rem;
        }

        .admin-panel__header h3 {
            font-size: 0.95rem;
        }

        .admin-panel__body {
            padding: 0.75rem;
        }

        .admin-stats-grid {
            gap: 0.375rem !important;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }

        .admin-stat-card {
            padding: 0.5rem 0.625rem !important;
        }

        .admin-stat-card__value {
            font-size: 1.35rem;
        }

        .admin-stat-card__label {
            font-size: 0.7rem;
        }

        .admin-stat-card__muted {
            font-size: 0.75rem;
        }

        .admin-btn {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }

        .admin-btn.sm {
            padding: 0.4rem 0.7rem;
            font-size: 0.75rem;
        }

        .admin-panel__actions .admin-btn {
            width: auto;
            font-size: 0.75rem;
            padding: 0.4rem 0.7rem;
        }

        .filters-actions {
            flex-direction: column;
        }

        .student-order-card__header {
            flex-direction: column;
            align-items: flex-start;
        }

        .student-order-card__meta {
            flex-direction: column;
            align-items: flex-start;
        }

        .student-order-card {
            padding: 1rem;
        }

        .student-order-card__number {
            font-size: 0.85rem;
        }

        .student-order-card__date {
            font-size: 0.75rem;
        }

        .student-order-card__amount strong {
            font-size: 1rem;
        }
    }
</style>
@endpush










