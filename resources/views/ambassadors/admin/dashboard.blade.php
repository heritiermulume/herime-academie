@extends('ambassadors.admin.layout')

@section('admin-title', 'Tableau de bord ambassadeur')
@section('admin-subtitle', 'Suivez vos gains, vos références et vos commissions en temps réel.')

@section('admin-actions')
    @if($promoCode)
        <button type="button" class="admin-btn outline" id="togglePromoCodeBtn" onclick="togglePromoCodeCard()">
            <i class="fas fa-ticket-alt me-2"></i>Afficher le code promo
        </button>
        <button type="button" class="admin-btn outline" onclick="copyPromoCode('{{ $promoCode->code }}')">
            <i class="fas fa-copy me-2"></i>Copier le code promo
        </button>
    @endif
@endsection

@section('admin-content')
    @if($promoCode)
        <article class="admin-panel" id="promoCodeCard" style="display: none;">
            <div class="admin-panel__header">
                <h3>
                    <i class="fas fa-ticket-alt me-2"></i>Votre Code Promo
                </h3>
            </div>
            <div class="admin-panel__body">
                <div class="text-center">
                    <div class="admin-card promo-code-card" style="background: linear-gradient(135deg, #003366 0%, #004080 100%); color: white; padding: 2rem;">
                        <h2 class="mb-0 promo-code-text" style="font-size: 2.5rem; font-weight: 700; letter-spacing: 0.1em;">{{ $promoCode->code }}</h2>
                    </div>
                    <p class="text-muted mt-3 mb-0 promo-code-description">Partagez ce code avec votre réseau pour gagner des commissions !</p>
                </div>
            </div>
        </article>
    @endif

    <section class="dashboard-grid">
        @foreach($metrics as $metric)
            <article class="admin-card dashboard-grid__item">
                <div class="dashboard-metric">
                    <div class="dashboard-metric__icon" style="background: {{ $metric['accent'] }}20; color: {{ $metric['accent'] }};">
                        <i class="{{ $metric['icon'] }}"></i>
                    </div>
                    <div class="dashboard-metric__content">
                        <span class="dashboard-metric__label">{{ $metric['label'] }}</span>
                        <strong class="dashboard-metric__value">{{ $metric['value'] }}</strong>
                        @if($metric['trend'] != 0)
                            <span class="dashboard-metric__trend {{ $metric['trend'] >= 0 ? 'is-up' : 'is-down' }}">
                                <i class="fas fa-arrow-{{ $metric['trend'] >= 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($metric['trend']), 1) }}% vs. 30 j
                            </span>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <article class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-clock me-2"></i>Activité récente
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="dashboard-activity">
                <div class="dashboard-activity__list">
                    <h3 class="dashboard-activity__title">Commandes récentes</h3>
                    <ul class="dashboard-activity__items">
                        @forelse($recentOrders as $order)
                            <li class="dashboard-activity__item">
                                <div>
                                    <strong>{{ $order->order_number }}</strong>
                                    <span>{{ $order->user?->name ?? 'Client' }} - {{ number_format($order->total ?? 0, 2) }} {{ $currencyCode }}</span>
                                </div>
                                <span class="dashboard-activity__meta">{{ $order->created_at->diffForHumans() }}</span>
                            </li>
                        @empty
                            <li class="dashboard-activity__empty">Aucune commande récente.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="dashboard-activity__list">
                    <h3 class="dashboard-activity__title">Commissions</h3>
                    <ul class="dashboard-activity__items">
                        @forelse($recentCommissions as $commission)
                            <li class="dashboard-activity__item">
                                <div>
                                    <strong>{{ number_format($commission->commission_amount, 2) }} {{ $currencyCode }}</strong>
                                    <span>{{ $commission->order?->order_number ?? 'N/A' }} - {{ $commission->getStatusLabel() }}</span>
                                </div>
                                <span class="dashboard-activity__meta">{{ $commission->created_at->diffForHumans() }}</span>
                            </li>
                        @empty
                            <li class="dashboard-activity__empty">Aucune commission récente.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </article>

    <article class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-bolt me-2"></i>Actions rapides
            </h3>
        </div>
        <div class="admin-panel__body">
            <div class="dashboard-actions">
                @if($promoCode)
                    <button type="button" class="dashboard-actions__item" onclick="copyPromoCode('{{ $promoCode->code }}')">
                        <i class="fas fa-copy"></i>
                        <span>Copier le code promo</span>
                    </button>
                @endif
                <a href="{{ route('contents.index') }}" class="dashboard-actions__item">
                    <i class="fas fa-book"></i>
                    <span>Découvrir les cours</span>
                </a>
            </div>
        </div>
    </article>

    <article class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-tasks me-2"></i>Tâches à suivre
            </h3>
        </div>
        <div class="admin-panel__body">
            <ul class="dashboard-tasks">
                @forelse($pendingTasks as $task)
                    <li class="dashboard-tasks__item">
                        <div>
                            <strong>{{ $task['title'] }}</strong>
                            <span>{{ $task['description'] }}</span>
                        </div>
                        <span class="dashboard-tasks__badge {{ $task['type'] }}">{{ ucfirst($task['type']) }}</span>
                    </li>
                @empty
                    <li class="dashboard-tasks__empty">Aucune action urgente. Continuez sur cette lancée !</li>
                @endforelse
            </ul>
        </div>
    </article>
@endsection

@php
    $currency = \App\Models\Setting::getBaseCurrency();
    $currencyCode = is_array($currency) ? ($currency['code'] ?? 'USD') : ($currency ?? 'USD');
@endphp

@push('styles')
<style>
    /* Styles pour les boutons d'actions sur mobile/tablette */
    @media (max-width: 768px) {
        .admin-header__actions {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 0.5rem !important;
            width: 100%;
        }

        .admin-header__actions .admin-btn {
            font-size: 0.75rem !important;
            padding: 0.5rem 0.75rem !important;
            width: 100%;
            justify-content: center;
        }

        .admin-header__actions .admin-btn i {
            font-size: 0.8rem !important;
            margin-right: 0.4rem !important;
        }
    }

    @media (max-width: 480px) {
        .admin-header__actions {
            gap: 0.4rem !important;
        }

        .admin-header__actions .admin-btn {
            font-size: 0.7rem !important;
            padding: 0.45rem 0.6rem !important;
        }

        .admin-header__actions .admin-btn i {
            font-size: 0.75rem !important;
            margin-right: 0.3rem !important;
        }
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
    .dashboard-grid__item {
        padding: 0;
    }
    .dashboard-metric {
        display: flex;
        gap: 1.25rem;
        align-items: center;
    }
    .dashboard-metric__icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 1.4rem;
    }
    .dashboard-metric__label {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
    }
    .dashboard-metric__value {
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        display: block;
    }
    .dashboard-metric__trend {
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .dashboard-metric__trend.is-up {
        color: #16a34a;
    }
    .dashboard-metric__trend.is-down {
        color: #dc2626;
    }

    .dashboard-activity {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
        padding: 0;
    }
    .dashboard-activity__list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .dashboard-activity__title {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .dashboard-activity__items {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .dashboard-activity__item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
        border-radius: 1rem;
        background: rgba(226, 232, 240, 0.35);
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .dashboard-activity__item strong {
        display: block;
        color: #0f172a;
        word-break: break-word;
    }
    .dashboard-activity__item span {
        color: #64748b;
        font-size: 0.85rem;
        word-break: break-word;
    }
    .dashboard-activity__meta {
        font-size: 0.8rem;
        color: #0ea5e9;
        font-weight: 600;
    }
    .dashboard-activity__empty {
        padding: 1.25rem;
        border-radius: 1rem;
        background: rgba(226, 232, 240, 0.5);
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .dashboard-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.75rem;
    }
    .dashboard-actions__item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-radius: 1rem;
        background: rgba(15, 23, 42, 0.04);
        color: #0f172a;
        text-decoration: none;
        font-weight: 600;
        transition: background 0.2s ease, transform 0.2s ease;
        border: none;
        cursor: pointer;
    }
    .dashboard-actions__item i {
        font-size: 1.2rem;
        color: var(--instructor-primary);
    }
    .dashboard-actions__item:hover {
        background: rgba(14, 165, 233, 0.15);
        transform: translateY(-3px);
    }

    .dashboard-tasks {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .dashboard-tasks__item {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-radius: 1rem;
        background: rgba(226, 232, 240, 0.35);
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .dashboard-tasks__item strong {
        color: #0f172a;
        display: block;
        margin-bottom: 0.25rem;
        word-break: break-word;
    }
    .dashboard-tasks__item span {
        color: #64748b;
        font-size: 0.85rem;
        word-break: break-word;
    }
    .dashboard-tasks__badge {
        align-self: flex-start;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .dashboard-tasks__badge.alert {
        background: rgba(220, 38, 38, 0.15);
        color: #b91c1c;
    }
    .dashboard-tasks__badge.info {
        background: rgba(14, 165, 233, 0.15);
        color: #0369a1;
    }
    .dashboard-tasks__badge.success {
        background: rgba(34, 197, 94, 0.15);
        color: #15803d;
    }
    .dashboard-tasks__empty {
        text-align: center;
        padding: 1.25rem;
        border-radius: 1rem;
        background: rgba(226, 232, 240, 0.5);
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .promo-code-card {
        word-break: break-all;
        overflow-wrap: break-word;
    }

    .promo-code-text {
        word-break: break-all;
        overflow-wrap: break-word;
    }

    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .dashboard-metric {
            gap: 0.85rem;
        }

        .dashboard-metric__icon {
            width: 48px;
            height: 48px;
            font-size: 1.2rem;
        }

        .dashboard-metric__label {
            font-size: 0.7rem;
        }

        .dashboard-metric__value {
            font-size: 1.5rem;
        }

        .dashboard-metric__trend {
            font-size: 0.75rem;
        }

        .dashboard-activity {
            grid-template-columns: 1fr;
            gap: 0.75rem;
            padding: 0;
        }

        .dashboard-activity__title {
            font-size: 0.9rem;
        }

        .dashboard-activity__item {
            padding: 0.75rem;
            gap: 0.75rem;
        }

        .dashboard-activity__item strong {
            font-size: 0.85rem;
        }

        .dashboard-activity__item span {
            font-size: 0.75rem;
        }

        .dashboard-activity__meta {
            font-size: 0.7rem;
        }

        .dashboard-activity__empty {
            padding: 1rem;
            font-size: 0.85rem;
        }

        .dashboard-actions {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .dashboard-actions__item {
            padding: 0.65rem 0.85rem;
            font-size: 0.85rem;
        }

        .dashboard-actions__item i {
            font-size: 1rem;
        }

        .dashboard-tasks {
            padding: 0 1rem 1rem;
            gap: 0.65rem;
        }

        .dashboard-tasks__item {
            padding: 0.75rem;
            gap: 0.75rem;
        }

        .dashboard-tasks__item strong {
            font-size: 0.85rem;
        }

        .dashboard-tasks__item span {
            font-size: 0.75rem;
        }

        .dashboard-tasks__badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }

        .dashboard-tasks__empty {
            padding: 1rem;
            font-size: 0.85rem;
        }
    }

    @media (max-width: 768px) {
        .dashboard-grid {
            gap: 0.75rem;
        }

        .dashboard-metric {
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .dashboard-metric__icon {
            width: 44px;
            height: 44px;
            font-size: 1.1rem;
        }

        .dashboard-metric__content {
            flex: 1;
            min-width: 0;
        }

        .dashboard-metric__label {
            font-size: 0.65rem;
        }

        .dashboard-metric__value {
            font-size: 1.25rem;
            word-break: break-word;
        }

        .dashboard-metric__trend {
            font-size: 0.7rem;
            margin-top: 0.25rem;
        }

        .dashboard-activity {
            gap: 1rem;
        }

        .dashboard-activity__title {
            font-size: 0.85rem;
        }

        .dashboard-activity__item {
            padding: 0.65rem;
            gap: 0.5rem;
            flex-direction: column;
            align-items: flex-start;
        }

        .dashboard-activity__item > div {
            width: 100%;
        }

        .dashboard-activity__item strong {
            font-size: 0.8rem;
            display: block;
            margin-bottom: 0.25rem;
        }

        .dashboard-activity__item span {
            font-size: 0.7rem;
            display: block;
        }

        .dashboard-activity__meta {
            font-size: 0.65rem;
            align-self: flex-end;
        }

        .dashboard-actions {
            gap: 0.5rem;
        }

        .dashboard-actions__item {
            padding: 0.75rem;
            font-size: 0.8rem;
            justify-content: center;
        }

        .dashboard-actions__item i {
            font-size: 0.9rem;
        }

        .dashboard-tasks__item {
            padding: 0.65rem;
            gap: 0.5rem;
            flex-direction: column;
            align-items: flex-start;
        }

        .dashboard-tasks__item > div {
            width: 100%;
        }

        .dashboard-tasks__item strong {
            font-size: 0.8rem;
        }

        .dashboard-tasks__item span {
            font-size: 0.7rem;
        }

        .dashboard-tasks__badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.45rem;
            align-self: flex-end;
        }
    }

    @media (max-width: 480px) {
        .admin-card {
            padding: 1rem !important;
        }

        .admin-card h2 {
            font-size: 1.75rem !important;
        }

        .dashboard-grid {
            gap: 0.5rem;
        }

        .dashboard-metric {
            gap: 0.5rem;
        }

        .dashboard-metric__icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .dashboard-metric__value {
            font-size: 1.1rem;
        }

        .dashboard-metric__trend {
            font-size: 0.65rem;
        }

        .dashboard-activity__item {
            padding: 0.5rem;
        }

        .dashboard-activity__item strong {
            font-size: 0.75rem;
        }

        .dashboard-activity__item span {
            font-size: 0.65rem;
        }

        .dashboard-actions__item {
            padding: 0.6rem;
            font-size: 0.75rem;
        }

        .dashboard-tasks__item {
            padding: 0.5rem;
        }

        .dashboard-tasks__item strong {
            font-size: 0.75rem;
        }

        .dashboard-tasks__item span {
            font-size: 0.65rem;
        }

        .promo-code-card {
            padding: 1.25rem !important;
        }

        .promo-code-text {
            font-size: 1.75rem !important;
            letter-spacing: 0.05em !important;
        }

        .promo-code-description {
            font-size: 0.85rem;
            padding: 0 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
// Système de toast moderne
function showToast(message, type = 'success') {
    // Créer le conteneur de toast s'il n'existe pas
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }

    // Créer le toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-info-circle';
    toast.innerHTML = `
        <div class="toast__icon">
            <i class="fas ${icon}"></i>
        </div>
        <div class="toast__content">
            <div class="toast__message">${message}</div>
        </div>
        <button class="toast__close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Ajouter le toast au conteneur
    toastContainer.appendChild(toast);

    // Animation d'entrée
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Suppression automatique après 4 secondes
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }, 4000);
}

function togglePromoCodeCard() {
    const card = document.getElementById('promoCodeCard');
    const btn = document.getElementById('togglePromoCodeBtn');
    
    if (card && btn) {
        if (card.style.display === 'none') {
            card.style.display = 'block';
            btn.innerHTML = '<i class="fas fa-eye-slash me-2"></i>Masquer le code promo';
        } else {
            card.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-ticket-alt me-2"></i>Afficher le code promo';
        }
    }
}

function copyPromoCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        showToast('Code promo copié : ' + code, 'success');
    }, function() {
        // Fallback pour les navigateurs plus anciens
        const textarea = document.createElement('textarea');
        textarea.value = code;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast('Code promo copié : ' + code, 'success');
    });
}
</script>
@endpush

@push('styles')
<style>
    .toast-container {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        max-width: 400px;
        pointer-events: none;
    }

    .toast {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 40px -20px rgba(0, 0, 0, 0.3);
        border-left: 4px solid #22c55e;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        pointer-events: auto;
        min-width: 300px;
    }

    .toast.show {
        opacity: 1;
        transform: translateX(0);
    }

    .toast-success {
        border-left-color: #22c55e;
    }

    .toast-info {
        border-left-color: #0ea5e9;
    }

    .toast-warning {
        border-left-color: #f59e0b;
    }

    .toast-error {
        border-left-color: #dc2626;
    }

    .toast__icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .toast-success .toast__icon {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
    }

    .toast-info .toast__icon {
        background: rgba(14, 165, 233, 0.15);
        color: #0ea5e9;
    }

    .toast-warning .toast__icon {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }

    .toast-error .toast__icon {
        background: rgba(220, 38, 38, 0.15);
        color: #dc2626;
    }

    .toast__content {
        flex: 1;
        min-width: 0;
    }

    .toast__message {
        font-size: 0.95rem;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.4;
        word-break: break-word;
    }

    .toast__close {
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s ease;
        flex-shrink: 0;
        width: 28px;
        height: 28px;
    }

    .toast__close:hover {
        background: rgba(0, 0, 0, 0.05);
        color: #0f172a;
    }

    @media (max-width: 640px) {
        .toast-container {
            top: 70px;
            right: 10px;
            left: 10px;
            max-width: none;
        }

        .toast {
            min-width: auto;
            width: 100%;
        }
    }
</style>
@endpush

