@extends('layouts.app')

@section('title', 'Notifications - Herime Academie')

@section('content')
<div class="notifications-page">
    <div class="container py-4 py-lg-5">
        <!-- Header moderne -->
        <header class="notifications-header mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-lg-center gap-3">
                <div class="notifications-header__text">
                    <h1 class="notifications-header__title">Notifications</h1>
                    <p class="notifications-header__subtitle">Restez informé de toutes les activités importantes</p>
                </div>
                <div class="mt-3 mt-lg-0">
                    <button type="button" class="btn notifications-header__btn" onclick="openMarkAllModal()">
                        <i class="fas fa-check-double me-2" aria-hidden="true"></i>
                        Marquer tout comme lu
                    </button>
                </div>
            </div>
        </header>

        <!-- Liste des notifications -->
        <div class="notifications-card card border-0 shadow-sm rounded-4 overflow-hidden">
            @if($notifications->count() > 0)
                <div class="list-group list-group-flush notifications-list">
                    @foreach($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $type = $data['type'] ?? 'default';
                        $message = $data['excerpt'] ?? $data['message'] ?? $data['body'] ?? 'Nouvelle notification';
                        $ctaUrl = $data['button_url'] ?? $data['action_url'] ?? null;
                        $ctaText = $data['button_text'] ?? $data['action_text'] ?? 'Voir';
                    @endphp
                    <div class="notifications-list__item list-group-item border-0 {{ $notification->read_at ? 'notifications-list__item--read' : 'notifications-list__item--unread' }}">
                        <div class="row align-items-start g-3">
                            <div class="col-auto">
                                <div class="notification-icon-wrap notification-icon-wrap--{{ $type }}">
                                    @switch($type)
                                        @case('course_enrolled')
                                            <i class="fas fa-user-plus"></i>
                                            @break
                                        @case('course_completed')
                                            <i class="fas fa-certificate"></i>
                                            @break
                                        @case('new_message')
                                            <i class="fas fa-envelope"></i>
                                            @break
                                        @case('payment_received')
                                            <i class="fas fa-dollar-sign"></i>
                                            @break
                                        @case('contact_message_received')
                                            <i class="fas fa-envelope-open-text"></i>
                                            @break
                                        @case('course_published')
                                            <i class="fas fa-book"></i>
                                            @break
                                        @case('success')
                                            <i class="fas fa-circle-check"></i>
                                            @break
                                        @case('warning')
                                            <i class="fas fa-triangle-exclamation"></i>
                                            @break
                                        @case('error')
                                            <i class="fas fa-circle-exclamation"></i>
                                            @break
                                        @case('info')
                                            <i class="fas fa-bullhorn"></i>
                                            @break
                                        @default
                                            <i class="fas fa-bell"></i>
                                    @endswitch
                                </div>
                            </div>
                            <div class="col flex-grow-1 min-width-0">
                                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-1">
                                    <h6 class="notification-title mb-0 {{ !$notification->read_at ? 'notification-title--unread' : '' }}">
                                        {{ $notification->data['title'] ?? 'Notification' }}
                                    </h6>
                                    <span class="notification-time text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="notification-message mb-2 {{ !$notification->read_at ? 'fw-medium' : '' }}">{{ $message }}</p>
                                @if(isset($notification->data['course_title']))
                                    <p class="notification-meta text-primary mb-2 small">
                                        <i class="fas fa-book me-1"></i>{{ $notification->data['course_title'] }}
                                    </p>
                                @endif
                                @if($ctaUrl)
                                    <a href="{{ $ctaUrl }}" class="btn btn-sm notification-cta" target="_blank" rel="noopener noreferrer">
                                        {{ $ctaText }}
                                        <i class="fas fa-arrow-right ms-1 small"></i>
                                    </a>
                                @endif
                            </div>
                            <div class="col-auto">
                                <div class="dropdown">
                                    <button class="btn btn-icon-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Options notification">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-2">
                                        @if(!$notification->read_at)
                                        <li>
                                            <a class="dropdown-item rounded-2" href="#" onclick="markAsRead('{{ $notification->id }}')">
                                                <i class="fas fa-check me-2 text-success"></i>Marquer comme lu
                                            </a>
                                        </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item rounded-2 text-danger" href="#" onclick="deleteNotification('{{ $notification->id }}')">
                                                <i class="fas fa-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($notifications->hasPages())
                <div class="notifications-pagination px-4 py-3 border-top bg-light">
                    {{ $notifications->links() }}
                </div>
                @endif
            @else
                <div class="notifications-empty text-center py-5 px-4">
                    <div class="notifications-empty__icon mb-3">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <h3 class="h5 fw-semibold text-body mb-2">Aucune notification</h3>
                    <p class="text-muted mb-0">Vous n'avez pas encore de notifications. Elles apparaîtront ici.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Marquer tout comme lu -->
<div class="modal fade" id="markAllModal" tabindex="-1" aria-labelledby="markAllModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header notifications-modal-header border-0 py-4">
                <h5 class="modal-title text-white fw-semibold" id="markAllModalLabel">
                    <i class="fas fa-check-double me-2"></i>Confirmer l'action
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0 text-body">Voulez-vous vraiment marquer toutes vos notifications comme lues ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer border-0 bg-light py-4">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary rounded-3 px-4" onclick="markAllAsRead()">
                    <i class="fas fa-check-double me-2"></i>Oui, tout marquer comme lu
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateNotificationUI(data);
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue.');
    });
}

function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateNotificationUI(data);
            closeMarkAllModal();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue.');
    });
}

function openMarkAllModal() {
    const modalElement = document.getElementById('markAllModal');
    if (!modalElement) return;

    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function closeMarkAllModal() {
    const modalElement = document.getElementById('markAllModal');
    if (!modalElement) return;

    const modal = bootstrap.Modal.getInstance(modalElement);
    modal?.hide();
}

function deleteNotification(notificationId) {
    fetch(`/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateNotificationUI(data);
            refreshNotificationList();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue lors de la suppression.');
    });
}

function updateNotificationUI(payload) {
    if (payload.unread_count !== undefined) {
        const badges = document.querySelectorAll('#notification-count, #notification-count-mobile');
        badges.forEach(badge => {
            badge.textContent = payload.unread_count > 99 ? '99+' : payload.unread_count;
            badge.style.display = payload.unread_count > 0 ? 'inline' : 'none';
        });
    }

    if (Array.isArray(payload.recent)) {
        const desktopList = document.getElementById('notifications-list');
        const mobileList = document.getElementById('notifications-list-mobile');
        if (desktopList) {
            desktopList.innerHTML = window.renderNotificationItems(payload.recent);
        }
        if (mobileList) {
            mobileList.innerHTML = window.renderNotificationItems(payload.recent);
        }
    }

    window.loadNotifications?.(true);
    refreshNotificationList();
}

function refreshNotificationList() {
    const listContainer = document.querySelector('.list-group');
    if (!listContainer) return;

    // Sur la page notifications, recharger pour garder le design et la pagination
    if (listContainer.closest('.notifications-page')) {
        window.location.reload();
        return;
    }

    fetch('/notifications/recent', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    })
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                listContainer.innerHTML = `
                    <li class="dropdown-item text-center py-4 text-muted">
                        <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
                        Aucune notification pour le moment
                    </li>
                `;
                return;
            }
            const rendered = window.renderNotificationItems(data);
            if (rendered.trim()) {
                listContainer.innerHTML = rendered;
            }
        })
        .catch(error => console.error('Error refreshing notifications list:', error));
}

window.renderNotificationItems = function(notifications) {
    const list = Array.isArray(notifications) ? notifications : [];

    if (!list.length) {
        return `
            <li class="dropdown-item text-center py-4 text-muted">
                <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
                Aucune notification pour le moment
            </li>
        `;
    }

    return list.map(notification => {
        const data = notification.data || {};
        const title = data.title || 'Notification';
        const message = data.excerpt || data.message || data.body || '';
        const createdAt = data.created_at_formatted || '';
        const url = data.button_url || data.action_url || null;
        const ctaText = data.button_text || data.action_text || 'Voir les détails';
        const badge = notification.read_at ? '' : '<span class="badge bg-primary me-2">Nouveau</span>';

        return `
            <li class="dropdown-item px-3 py-3 ${notification.read_at ? '' : 'bg-light'}" style="white-space: normal;">
                <div class="d-flex flex-column gap-1">
                    <div class="d-flex align-items-start gap-3">
                        <div class="d-flex flex-column flex-grow-1 gap-1">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <span class="fw-semibold text-truncate">${badge}${title}</span>
                                <small class="text-muted flex-shrink-0">${createdAt}</small>
                            </div>
                            <p class="mb-0 text-muted" style="overflow-wrap: anywhere;">${message}</p>
                            ${url ? `
                                <div>
                                    <a href="${url}" class="btn btn-sm btn-outline-primary mt-2 text-truncate" target="_blank" rel="noopener" style="max-width: 100%;">
                                        ${ctaText}
                                    </a>
                                </div>` : ''}
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-label="Options notification">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ${notification.read_at ? '' : `
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="markAsRead('${notification.id}')">
                                            <i class="fas fa-check me-2"></i>Marquer comme lu
                                        </a>
                                    </li>
                                `}
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="deleteNotification('${notification.id}')">
                                        <i class="fas fa-trash me-2"></i>Supprimer
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </li>
        `;
    }).join('');
};

// Auto-refresh notifications every 30 seconds
setInterval(function() {
    fetch('/notifications/unread-count', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    })
        .then(response => response.json())
        .then(data => {
            // Update notification count in navbar if exists
            const countElement = document.querySelector('.notification-count');
            if (countElement) {
                countElement.textContent = data.count;
                countElement.style.display = data.count > 0 ? 'inline' : 'none';
            }
        })
        .catch(error => {
            console.error('Error fetching notification count:', error);
        });
}, 30000);
</script>
@endpush

@push('styles')
<style>
/* Charte graphique Herime */
.notifications-page {
    --notif-primary: var(--primary-color, #003366);
    --notif-primary-light: rgba(0, 51, 102, 0.08);
    --notif-secondary: var(--secondary-color, #ffcc33);
    --notif-secondary-light: rgba(255, 204, 51, 0.15);
    --notif-text: var(--text-dark, #2c3e50);
    --notif-radius: 1rem;
    --notif-radius-top: 1.5rem;
    --notif-radius-sm: 0.75rem;
    --notif-shadow: 0 4px 20px rgba(0, 51, 102, 0.06);
    --notif-shadow-hover: 0 8px 28px rgba(0, 51, 102, 0.1);
}

/* Header */
.notifications-header__title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--notif-text);
    letter-spacing: -0.02em;
    margin-bottom: 0.25rem;
}

.notifications-header__subtitle {
    font-size: 1rem;
    color: #6b7280;
    margin: 0;
}

.notifications-header__btn {
    background: transparent;
    border: 2px solid var(--notif-primary);
    color: var(--notif-primary);
    font-weight: 600;
    padding: 0.5rem 1.25rem;
    border-radius: var(--notif-radius-sm);
    transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease;
}

.notifications-header__btn:hover {
    background: var(--notif-primary);
    color: #fff;
    transform: translateY(-1px);
}

/* Carte principale */
.notifications-card {
    border-radius: var(--notif-radius) !important;
    border-top-left-radius: var(--notif-radius-top) !important;
    border-top-right-radius: var(--notif-radius-top) !important;
    box-shadow: var(--notif-shadow);
    transition: box-shadow 0.2s ease;
}

.notifications-card:hover {
    box-shadow: var(--notif-shadow-hover);
}

/* Items de liste */
.notifications-list__item {
    padding: 1.25rem 1.5rem;
    transition: background 0.2s ease;
}

.notifications-list__item--unread {
    background: var(--notif-primary-light);
    border-left: 3px solid var(--notif-primary);
}

.notifications-list__item--read {
    border-left: 3px solid transparent;
}

.notifications-list__item:hover {
    background: rgba(0, 51, 102, 0.04) !important;
}

.notifications-list__item + .notifications-list__item {
    border-top: 1px solid rgba(0, 0, 0, 0.06);
}

/* Icône par type */
.notification-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: var(--notif-radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.notification-icon-wrap--course_enrolled,
.notification-icon-wrap--success,
.notification-icon-wrap--payment_received {
    background: rgba(34, 197, 94, 0.12);
    color: #16a34a;
}

.notification-icon-wrap--course_completed,
.notification-icon-wrap--warning {
    background: rgba(234, 179, 8, 0.15);
    color: #ca8a04;
}

.notification-icon-wrap--new_message,
.notification-icon-wrap--contact_message_received {
    background: rgba(14, 165, 233, 0.12);
    color: #0284c7;
}

.notification-icon-wrap--course_published,
.notification-icon-wrap--info {
    background: var(--notif-primary-light);
    color: var(--notif-primary);
}

.notification-icon-wrap--error {
    background: rgba(239, 68, 68, 0.12);
    color: #dc2626;
}

.notification-icon-wrap--default {
    background: #f3f4f6;
    color: #6b7280;
}

/* Titre et message */
.notification-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--notif-text);
}

.notification-title--unread {
    color: var(--notif-text);
}

.notification-title.text-muted {
    color: #6b7280 !important;
}

.notification-message {
    font-size: 0.9375rem;
    color: #4b5563;
    line-height: 1.5;
    margin: 0;
}

.notification-time {
    font-size: 0.8125rem;
    white-space: nowrap;
}

/* Bouton CTA dans une notification */
.notification-cta {
    border: 1px solid var(--notif-primary);
    color: var(--notif-primary);
    font-weight: 500;
    border-radius: var(--notif-radius-sm);
    padding: 0.35rem 0.75rem;
    font-size: 0.875rem;
    transition: background 0.2s ease, color 0.2s ease;
}

.notification-cta:hover {
    background: var(--notif-primary);
    color: #fff;
    border-color: var(--notif-primary);
}

/* Bouton menu (trois points) */
.btn-icon-dropdown {
    width: 36px;
    height: 36px;
    padding: 0;
    border: 1px solid #e5e7eb;
    border-radius: var(--notif-radius-sm);
    color: #6b7280;
    background: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease;
}

.btn-icon-dropdown:hover {
    border-color: var(--notif-primary);
    color: var(--notif-primary);
    background: var(--notif-primary-light);
}

.btn-icon-dropdown::after {
    display: none;
}

/* État vide */
.notifications-empty__icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    border-radius: 50%;
    background: var(--notif-primary-light);
    color: var(--notif-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

/* Modal */
.notifications-modal-header {
    background: linear-gradient(135deg, var(--notif-primary) 0%, #004080 100%) !important;
}

.notifications-page .modal-content {
    border-top-left-radius: var(--notif-radius-top) !important;
    border-top-right-radius: var(--notif-radius-top) !important;
}

/* Pagination (charte) */
.notifications-page .pagination {
    justify-content: center;
    gap: 0.25rem;
}

.notifications-page .page-link {
    color: var(--notif-primary);
    border-color: #e5e7eb;
    border-radius: var(--notif-radius-sm) !important;
    padding: 0.5rem 0.75rem;
    font-weight: 500;
    transition: color 0.2s ease, background 0.2s ease, border-color 0.2s ease;
}

.notifications-page .page-link:hover {
    color: #004080;
    background: var(--notif-primary-light);
    border-color: var(--notif-primary);
}

.notifications-page .page-item.active .page-link {
    background: var(--notif-primary);
    border-color: var(--notif-primary);
    color: #fff;
}

.min-width-0 {
    min-width: 0;
}

/* Mobile / tablette : bords plus arrondis */
@media (max-width: 991.98px) {
    .notifications-page {
        --notif-radius-top: 3.25rem;
        --notif-radius: 1.5rem;
    }

    .notifications-card {
        border-radius: var(--notif-radius) !important;
        border-top-left-radius: var(--notif-radius-top) !important;
        border-top-right-radius: var(--notif-radius-top) !important;
    }

    .notifications-page .modal-content {
        border-top-left-radius: var(--notif-radius-top) !important;
        border-top-right-radius: var(--notif-radius-top) !important;
        border-bottom-left-radius: var(--notif-radius) !important;
        border-bottom-right-radius: var(--notif-radius) !important;
    }
}

/* Responsive */
@media (max-width: 575.98px) {
    .notifications-header__title {
        font-size: 1.5rem;
    }

    .notifications-list__item {
        padding: 1rem 1rem;
    }

    .notification-icon-wrap {
        width: 42px;
        height: 42px;
        font-size: 1.1rem;
    }
}

.notification-count {
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 0.75rem;
    font-weight: bold;
    margin-left: 5px;
}
</style>
@endpush