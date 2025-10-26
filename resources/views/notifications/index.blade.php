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
                    <button type="button" class="btn btn-outline-primary" onclick="markAllAsRead()">
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
                            <div class="list-group-item border-0 py-3 {{ $notification->read_at ? '' : 'bg-light' }}">
                                <div class="row align-items-center">
                                    <div class="col-md-1">
                                        @if(!$notification->read_at)
                                        <div class="unread-indicator bg-primary rounded-circle" style="width: 10px; height: 10px;"></div>
                                        @endif
                                    </div>
                                    <div class="col-md-1">
                                        <div class="notification-icon">
                                            @php
                                                $data = $notification->data;
                                                $type = $data['type'] ?? 'default';
                                            @endphp
                                            
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
                                            {{ $notification->data['message'] ?? $notification->data['body'] ?? 'Nouvelle notification' }}
                                        </p>
                                        @if(isset($notification->data['course_title']))
                                        <small class="text-primary">
                                            <i class="fas fa-book me-1"></i>{{ $notification->data['course_title'] }}
                                        </small>
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
                                                    <li><a class="dropdown-item" href="#" onclick="markAsRead({{ $notification->id }})">
                                                        <i class="fas fa-check me-2"></i>Marquer comme lu
                                                    </a></li>
                                                    @endif
                                                    <li><a class="dropdown-item" href="#" onclick="deleteNotification({{ $notification->id }})">
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
@endsection

@push('scripts')
<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
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
}

function markAllAsRead() {
    if (confirm('Êtes-vous sûr de vouloir marquer toutes les notifications comme lues ?')) {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
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
    }
}

function deleteNotification(notificationId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
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
            alert('Une erreur est survenue lors de la suppression.');
        });
    }
}

// Auto-refresh notifications every 30 seconds
setInterval(function() {
    fetch('/notifications/unread-count')
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