@extends('layouts.app')

@section('title', 'Notifications - Herime Academie')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 fw-bold mb-1">Notifications</h1>
                    <p class="text-muted mb-0">Restez informé de toutes les activités importantes</p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary" onclick="openMarkAllModal()">
                        <i class="fas fa-check-double me-2"></i>Marquer tout comme lu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                            @php
                                $data = $notification->data ?? [];
                                $type = $data['type'] ?? 'default';
                                $message = $data['excerpt'] ?? $data['message'] ?? $data['body'] ?? 'Nouvelle notification';
                                $ctaUrl = $data['button_url'] ?? $data['action_url'] ?? null;
                                $ctaText = $data['button_text'] ?? $data['action_text'] ?? 'Voir';
                            @endphp
                            <div class="list-group-item border-0 py-3 {{ $notification->read_at ? '' : 'bg-light' }}">
                                <div class="row align-items-center">
                                    <div class="col-md-1">
                                        @if(!$notification->read_at)
                                        <div class="unread-indicator bg-primary rounded-circle" style="width: 10px; height: 10px;"></div>
                                        @endif
                                    </div>
                                    <div class="col-md-1">
                                        <div class="notification-icon">
                                            @switch($type)
                                                @case('course_enrolled')
                                                    <i class="fas fa-user-plus text-success fa-2x"></i>
                                                    @break
                                                @case('course_completed')
                                                    <i class="fas fa-certificate text-warning fa-2x"></i>
                                                    @break
                                                @case('new_message')
                                                    <i class="fas fa-envelope text-info fa-2x"></i>
                                                    @break
                                                @case('payment_received')
                                                    <i class="fas fa-dollar-sign text-success fa-2x"></i>
                                                    @break
                                                @case('course_published')
                                                    <i class="fas fa-book text-primary fa-2x"></i>
                                                    @break
                                                @case('success')
                                                    <i class="fas fa-circle-check text-success fa-2x"></i>
                                                    @break
                                                @case('warning')
                                                    <i class="fas fa-triangle-exclamation text-warning fa-2x"></i>
                                                    @break
                                                @case('error')
                                                    <i class="fas fa-circle-exclamation text-danger fa-2x"></i>
                                                    @break
                                                @case('info')
                                                    <i class="fas fa-bullhorn text-primary fa-2x"></i>
                                                    @break
                                                @default
                                                    <i class="fas fa-bell text-muted fa-2x"></i>
                                            @endswitch
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="mb-1 fw-bold {{ !$notification->read_at ? 'text-dark' : 'text-muted' }}">
                                            {{ $notification->data['title'] ?? 'Notification' }}
                                        </h6>
                                        <p class="mb-1 {{ !$notification->read_at ? 'fw-bold' : '' }}">
                                            {{ $message }}
                                        </p>
                                        @if(isset($notification->data['course_title']))
                                        <small class="text-primary">
                                            <i class="fas fa-book me-1"></i>{{ $notification->data['course_title'] }}
                                        </small>
                                        @endif
                                        @if($ctaUrl)
                                        <div class="mt-2">
                                            <a href="{{ $ctaUrl }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener noreferrer">
                                                {{ $ctaText }}
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <small class="text-muted">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </small>
                                        <div class="mt-2">
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if(!$notification->read_at)
                                                    <li><a class="dropdown-item" href="#" onclick="markAsRead('{{ $notification->id }}')">
                                                        <i class="fas fa-check me-2"></i>Marquer comme lu
                                                    </a></li>
                                                    @endif
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteNotification('{{ $notification->id }}')">
                                                        <i class="fas fa-trash me-2"></i>Supprimer
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="p-3">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune notification</h5>
                            <p class="text-muted">Vous n'avez pas encore de notifications</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="markAllModal" tabindex="-1" aria-labelledby="markAllModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="markAllModalLabel">
                    <i class="fas fa-check-double me-2"></i>Confirmer l'action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Voulez-vous vraiment marquer toutes vos notifications comme lues ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="markAllAsRead()">
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
    fetch('/notifications/recent', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    })
        .then(response => response.json())
        .then(data => {
            const listContainer = document.querySelector('.list-group');
            if (!listContainer) return;

            if (!Array.isArray(data) || data.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune notification</h5>
                        <p class="text-muted">Vous n'avez pas encore de notifications</p>
                    </div>
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
.unread-indicator {
    margin-top: 15px;
}

.list-group-item:hover {
    background-color: #f8f9fa !important;
}

.notification-icon {
    text-align: center;
}

.dropdown-toggle::after {
    display: none;
}

.pagination {
    justify-content: center;
}

.page-link {
    color: #003366;
    border-color: #dee2e6;
}

.page-link:hover {
    color: #ffcc33;
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.page-item.active .page-link {
    background-color: #003366;
    border-color: #003366;
}

.btn-primary {
    background-color: #003366;
    border-color: #003366;
}

.btn-primary:hover {
    background-color: #004080;
    border-color: #004080;
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