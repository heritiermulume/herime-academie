@extends('customers.admin.layout')

@section('admin-title', 'Commande ' . $order->order_number)
@section('admin-subtitle')
@php
    $hasPackItems = $order->orderItems->contains(fn ($item) => ! empty($item->content_package_id));
    $hasDownloadable = $order->orderItems->contains(function ($item) {
        return $item->course && $item->course->is_downloadable;
    });
    $hasInPerson = $order->orderItems->contains(function ($item) {
        return $item->course && ($item->course->is_in_person_program ?? false);
    });
    $generalLabel = $hasDownloadable
        ? 'produits'
        : ($hasInPerson
            ? 'programmes'
            : ($hasPackItems ? 'contenus' : 'cours'));
@endphp
Détails de la commande et accès aux {{ $generalLabel }} associés.
@endsection

@section('admin-actions')
    <a href="{{ route('orders.index') }}" class="admin-btn ghost">
        <i class="fas fa-arrow-left me-2"></i>Retour à mes commandes
    </a>
@endsection

@section('admin-content')
@php
    $statusLabels = [
        'pending' => ['label' => 'En attente', 'badge' => 'warning'],
        'confirmed' => ['label' => 'Confirmée', 'badge' => 'info'],
        'paid' => ['label' => 'Payée', 'badge' => 'success'],
        'completed' => ['label' => 'Terminée', 'badge' => 'success'],
        'cancelled' => ['label' => 'Annulée', 'badge' => 'error'],
    ];
    $statusData = $statusLabels[$order->status] ?? ['label' => ucfirst($order->status), 'badge' => 'info'];
    
    // Fonction helper pour obtenir le terme approprié selon le type de contenu
    $getContentLabel = function($course) {
        if (!$course) return 'cours';
        return $course->getContentLabel();
    };
    
    $hasPackItems = $order->orderItems->contains(fn ($item) => ! empty($item->content_package_id));
    $hasDownloadableItems = $order->orderItems->contains(function ($item) {
        return $item->course && $item->course->is_downloadable;
    });
    $hasInPersonItems = $order->orderItems->contains(function ($item) {
        return $item->course && ($item->course->is_in_person_program ?? false);
    });
    $generalLabel = $hasDownloadableItems
        ? 'produits'
        : ($hasInPersonItems
            ? 'programmes'
            : ($hasPackItems ? 'contenus' : 'cours'));

    // Blocs d'affichage : un pack = une carte regroupée (ordre des lignes commande conservé)
    $orderItemsSorted = $order->orderItems->sortBy('id')->values();
    $renderedPackIds = [];
    $orderLineBlocks = [];
    foreach ($orderItemsSorted as $item) {
        if (! empty($item->content_package_id)) {
            $pid = (int) $item->content_package_id;
            if (isset($renderedPackIds[$pid])) {
                continue;
            }
            $renderedPackIds[$pid] = true;
            $orderLineBlocks[] = [
                'type' => 'pack',
                'items' => $orderItemsSorted->where('content_package_id', $pid)->values(),
            ];
        } else {
            $orderLineBlocks[] = ['type' => 'course', 'item' => $item];
        }
    }
@endphp

<div class="student-order-show">
    <div class="student-order-show__summary">
        <div class="order-summary-card">
            <span class="order-summary-card__label">Statut de la commande</span>
            <span class="admin-badge {{ $statusData['badge'] }}">
                <i class="fas fa-circle"></i>{{ $statusData['label'] }}
            </span>
            <small class="order-summary-card__hint">
                Passée le {{ $order->created_at->format('d/m/Y à H:i') }}
            </small>
        </div>
        <div class="order-summary-card">
            <span class="order-summary-card__label">Montant total</span>
            <strong class="order-summary-card__value">
                {{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->total_amount ?? $order->total ?? 0) }}
            </strong>
            <small class="order-summary-card__hint">
                Sous-total {{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->subtotal ?? $order->total_amount ?? 0) }}
            </small>
        </div>
        <div class="order-summary-card">
            <span class="order-summary-card__label">Mode de paiement</span>
            <strong class="order-summary-card__value">
                {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Non spécifié')) }}
            </strong>
            <small class="order-summary-card__hint">
                Référence : {{ $order->payment_reference ?? '—' }}
            </small>
        </div>
        <div class="order-summary-card">
            <span class="order-summary-card__label">Dernière mise à jour</span>
            <strong class="order-summary-card__value">
                {{ $order->updated_at->diffForHumans() }}
            </strong>
            <small class="order-summary-card__hint">
                Client : {{ $order->user->name ?? 'Inconnu' }}
            </small>
        </div>
    </div>

    <div class="admin-card">
        <div class="student-order-show__header">
            <div>
                <h3 class="admin-card__title">Informations de la commande</h3>
                <p class="admin-card__subtitle">Numéro {{ $order->order_number }}</p>
            </div>
            <div class="student-order-show__actions">
                @php
                    $hasPendingMonerooPayment = $order->payments
                        ->where('payment_method', 'moneroo')
                        ->whereIn('status', ['pending', 'processing'])
                        ->isNotEmpty();
                @endphp
                @if($hasPendingMonerooPayment)
                    <form method="POST" action="{{ route('moneroo.verify-order', $order) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="admin-btn primary sm">
                            <i class="fas fa-sync-alt me-1"></i>Vérifier le paiement
                        </button>
                    </form>
                    <small class="order-verify-hint" style="display: block; margin-top: 0.5rem; color: #64748b; font-size: 0.8rem;">
                        Vous avez été débité mais la page ne s’est pas actualisée ? Cliquez pour vérifier.
                    </small>
                @elseif(in_array($order->status, ['paid', 'completed']))
                    @php
                        $firstPackItem = $order->orderItems->first(fn ($i) => ! empty($i->content_package_id) && $i->contentPackage);
                        $firstCourse = optional($order->enrollments->first())->course;
                    @endphp
                    @if($firstPackItem)
                        <a href="{{ route('customer.pack', $firstPackItem->contentPackage) }}" class="admin-btn primary sm">
                            <i class="fas fa-box-open me-1"></i>Ouvrir le pack
                        </a>
                    @elseif($firstCourse)
                        @if($firstCourse->is_downloadable)
                            <a href="{{ route('contents.show', $firstCourse->slug) }}" class="admin-btn primary sm">
                                <i class="fas fa-eye me-1"></i>Voir
                            </a>
                        @elseif($firstCourse->is_in_person_program ?? false)
                            <a href="{{ route('contents.show', $firstCourse->slug) }}" class="admin-btn primary sm">
                                <i class="fas fa-eye me-1"></i>Voir le programme
                            </a>
                        @else
                            <a href="{{ route('learning.course', $firstCourse->slug) }}" class="admin-btn success sm">
                                <i class="fas fa-play me-1"></i>Commencer le cours
                            </a>
                        @endif
                    @endif
                @endif
                <a href="{{ route('orders.index') }}" class="admin-btn soft sm">
                    Voir toutes les commandes
                </a>
            </div>
        </div>

        <div class="student-order-show__details">
            <div class="detail-block">
                <span class="detail-block__label">Montants</span>
                <ul>
                    <li>Sous-total : {{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->subtotal ?? $order->total_amount ?? 0) }}</li>
                    <li>Réduction : {{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->discount ?? 0) }}</li>
                    <li>Taxes : {{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->tax ?? 0) }}</li>
                </ul>
            </div>
            <div class="detail-block">
                <span class="detail-block__label">Dates clés</span>
                <ul>
                    <li>Confirmée : {{ optional($order->confirmed_at)->format('d/m/Y à H:i') ?? '—' }}</li>
                    <li>Payée : {{ optional($order->paid_at)->format('d/m/Y à H:i') ?? '—' }}</li>
                    <li>Terminée : {{ optional($order->completed_at)->format('d/m/Y à H:i') ?? '—' }}</li>
                </ul>
            </div>
            <div class="detail-block">
                <span class="detail-block__label">Client</span>
                <ul>
                    <li>{{ $order->user->name ?? 'Client supprimé' }}</li>
                    <li>{{ $order->user->email ?? 'Email indisponible' }}</li>
                    <li>{{ $order->user->phone ?? 'Téléphone indisponible' }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="student-order-show__section-header">
            <h3 class="admin-card__title">{{ ucfirst($generalLabel) }} inclus</h3>
            <p class="admin-card__subtitle">Liste des {{ $generalLabel }} associés à cette commande.</p>
        </div>

        @if($order->orderItems && $order->orderItems->count() > 0)
            <div class="student-order-show__courses">
                @foreach($orderLineBlocks as $block)
                    @if($block['type'] === 'pack')
                        @php
                            $packItems = $block['items'];
                            $firstPackItem = $packItems->first();
                            $pkg = $firstPackItem?->contentPackage;
                            $packTotal = \App\Models\Order::billedAmountForContentPackage($order->orderItems, (int) $firstPackItem->content_package_id);
                            $courseCount = $packItems->filter(fn ($i) => $i->course)->count();
                        @endphp
                        <div class="order-pack-card">
                            <div class="order-pack-card__main order-course-card">
                                <div class="order-course-card__meta">
                                    <h4>
                                        @if($pkg)
                                            <i class="fas fa-box-open me-1" style="color: #6366f1;"></i>{{ $pkg->title }}
                                        @else
                                            <i class="fas fa-box-open me-1 text-muted"></i>Pack (référence indisponible)
                                        @endif
                                    </h4>
                                    <p class="text-muted mb-0">
                                        Pack · {{ $courseCount }} contenu{{ $courseCount > 1 ? 's' : '' }} inclus
                                    </p>
                                </div>
                                <div class="order-course-card__info">
                                    <span class="order-course-card__price">
                                        {{ \App\Helpers\CurrencyHelper::formatWithSymbol($packTotal) }}
                                    </span>
                                    <span class="order-course-card__quantity">Forfait</span>
                                </div>
                                @if($pkg && in_array($order->status, ['paid', 'completed']))
                                    <div class="order-course-card__actions">
                                        <a href="{{ route('customer.pack', $pkg) }}" class="admin-btn ghost sm">
                                            <i class="fas fa-folder-open me-1"></i>Ouvrir le pack
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="order-pack-card__contents">
                                <span class="order-pack-card__contents-label">Contenus du pack</span>
                                <ul class="order-pack-card__list">
                                    @foreach($packItems as $pItem)
                                        @php($pcourse = $pItem->course)
                                        <li>
                                            @if($pcourse)
                                                <a href="{{ route('contents.show', $pcourse->slug) }}" class="order-pack-card__link">
                                                    {{ $pcourse->title }}
                                                </a>
                                                @if($pcourse->provider)
                                                    <span class="order-pack-card__link-meta">{{ $pcourse->provider->name }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">Contenu indisponible</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @else
                        @php($item = $block['item'])
                        @php($course = $item->course)
                        <div class="order-course-card">
                            <div class="order-course-card__meta">
                                <h4>{{ $course->title ?? ($course ? ($course->is_downloadable ? 'Produit supprimé' : (($course->is_in_person_program ?? false) ? 'Programme supprimé' : 'Cours supprimé')) : 'Contenu supprimé') }}</h4>
                                <p>
                                    {{ $course->provider->name ?? 'Prestataire inconnu' }}
                                    @if($course && $course->category)
                                        · {{ $course->category->name }}
                                    @endif
                                </p>
                            </div>
                            <div class="order-course-card__info">
                                <span class="order-course-card__price">
                                    {{ \App\Helpers\CurrencyHelper::formatWithSymbol($item->total ?? $item->price ?? 0) }}
                                </span>
                                <span class="order-course-card__quantity">Quantité : 1</span>
                            </div>
                            @if($course)
                                <div class="order-course-card__actions">
                                    <a href="{{ route('contents.show', $course->slug) }}" class="admin-btn ghost sm">
                                        <i class="fas fa-eye me-1"></i>Voir {{ $getContentLabel($course) }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="admin-empty-state">
                <i class="fas fa-tags"></i>
                <p>Aucun {{ $generalLabel }} enregistré pour cette commande.</p>
                @if($order->status === 'pending')
                    <small class="text-muted">Les {{ $generalLabel }} seront ajoutés une fois le paiement confirmé.</small>
                @endif
            </div>
        @endif
    </div>

    @if($order->enrollments->isNotEmpty())
        <div class="admin-card">
            <div class="student-order-show__section-header">
                <h3 class="admin-card__title">Accès aux {{ $generalLabel }}</h3>
                <p class="admin-card__subtitle">
                    @if($hasDownloadableItems)
                        Vous pouvez télécharger vos produits immédiatement.
                    @elseif($hasInPersonItems)
                        Consultez les détails de vos programmes et contactez les organisateurs via WhatsApp.
                    @else
                        Vous pouvez démarrer vos cours immédiatement.
                    @endif
                </p>
            </div>
            <div class="student-order-show__enrollments">
                @foreach($order->enrollments as $enrollment)
                    @php($course = $enrollment->course)
                    <div class="order-enrollment-card">
                        <div>
                            <h4>{{ $course->title ?? ($course ? ($course->is_downloadable ? 'Produit supprimé' : (($course->is_in_person_program ?? false) ? 'Programme supprimé' : 'Cours supprimé')) : 'Contenu supprimé') }}</h4>
                            <p>
                                @if($course && $course->is_downloadable)
                                    Acheté le {{ optional($enrollment->created_at)->format('d/m/Y') }}
                                @elseif($course && ($course->is_in_person_program ?? false))
                                    Inscrit le {{ optional($enrollment->created_at)->format('d/m/Y') }}
                                @else
                                    Inscrit le {{ optional($enrollment->created_at)->format('d/m/Y') }}
                                    · Progression {{ $enrollment->progress }}%
                                @endif
                            </p>
                        </div>
                        @if($course)
                            @if($course->is_downloadable || ($course->is_in_person_program ?? false))
                                <a href="{{ route('contents.download', $course->slug) }}" class="admin-btn primary sm">
                                    <i class="fas fa-download me-1"></i>{{ $course->getDownloadButtonText() }}
                                </a>
                            @else
                                <a href="{{ route('learning.course', $course->slug) }}" class="admin-btn success sm">
                                    <i class="fas fa-play me-1"></i>Commencer
                                </a>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($order->notes)
        <div class="admin-card">
            <div class="student-order-show__section-header">
                <h3 class="admin-card__title">Notes</h3>
            </div>
            <p class="student-order-show__notes">{{ $order->notes }}</p>
        </div>
    @endif
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

    .student-order-show {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }

    .student-order-show__summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.35rem;
    }

    .order-summary-card {
        padding: 1.4rem 1.5rem;
        border-radius: 1.2rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        background: #ffffff;
        box-shadow: 0 18px 45px -35px rgba(15, 23, 42, 0.2);
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
        font-size: 1.7rem;
        font-weight: 700;
        color: #0f172a;
    }

    .order-summary-card__hint {
        font-size: 0.82rem;
        color: #94a3b8;
    }

    .student-order-show__header {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }

    .student-order-show__actions {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .student-order-show__details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
    }

    .detail-block {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .detail-block__label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 600;
    }

    .detail-block ul {
        margin: 0;
        padding-left: 1.1rem;
        color: #475569;
        font-size: 0.88rem;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .student-order-show__section-header {
        margin-bottom: 1.2rem;
    }

    .student-order-show__courses {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .order-pack-card {
        border-radius: 1rem;
        border: 1px solid rgba(99, 102, 241, 0.22);
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.06) 0%, rgba(248, 250, 252, 0.95) 48%);
        box-shadow: 0 12px 32px -24px rgba(99, 102, 241, 0.35);
        overflow: hidden;
    }

    .order-pack-card .order-course-card {
        background: transparent;
        border: none;
        border-radius: 0;
        border-bottom: 1px solid rgba(226, 232, 240, 0.85);
    }

    .order-pack-card__contents {
        padding: 0.85rem 1.25rem 1.15rem;
        background: rgba(255, 255, 255, 0.65);
    }

    .order-pack-card__contents-label {
        display: block;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.55rem;
    }

    .order-pack-card__list {
        margin: 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .order-pack-card__list li {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        padding: 0.45rem 0.65rem;
        border-radius: 0.65rem;
        background: rgba(248, 250, 252, 0.9);
        border: 1px solid rgba(226, 232, 240, 0.6);
    }

    .order-pack-card__link {
        font-size: 0.9rem;
        font-weight: 600;
        color: #2563eb;
        text-decoration: none;
    }

    .order-pack-card__link:hover {
        text-decoration: underline;
    }

    .order-pack-card__link-meta {
        font-size: 0.78rem;
        color: #94a3b8;
    }

    .order-course-card {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.1rem 1.25rem;
        border-radius: 1rem;
        background: rgba(248, 250, 252, 0.8);
        border: 1px solid rgba(226, 232, 240, 0.7);
    }

    .order-course-card__meta h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .order-course-card__meta p {
        margin: 0.3rem 0 0;
        font-size: 0.85rem;
        color: #64748b;
    }

    .order-course-card__info {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.35rem;
    }

    .order-course-card__price {
        font-weight: 700;
        color: #22c55e;
    }

    .order-course-card__quantity {
        font-size: 0.78rem;
        color: #94a3b8;
    }

    .student-order-show__enrollments {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .order-enrollment-card {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.1rem 1.25rem;
        border-radius: 1rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        background: rgba(240, 249, 255, 0.8);
        align-items: center;
    }

    .order-enrollment-card h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .order-enrollment-card p {
        margin: 0.25rem 0 0;
        font-size: 0.85rem;
        color: #475569;
    }

    .student-order-show__notes {
        padding: 1rem 1.2rem;
        border-radius: 1rem;
        background: rgba(254, 240, 138, 0.25);
        border: 1px solid rgba(251, 191, 36, 0.35);
        color: #b45309;
        font-size: 0.95rem;
    }

    @media (max-width: 768px) {
        .student-order-show__header,
        .order-course-card,
        .order-pack-card .order-course-card,
        .order-enrollment-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .student-order-show__actions {
            width: 100%;
        }

        .admin-btn {
            width: 100%;
        }

        .order-course-card__info {
            align-items: flex-start;
        }
    }
</style>
@endpush










