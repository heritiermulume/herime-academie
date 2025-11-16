@extends('instructors.admin.layout')

@section('admin-title', 'Centre de notifications')
@section('admin-subtitle', 'Consultez vos notifications récentes et marquez-les comme lues en un clic.')

@section('admin-actions')
    <button type="button" class="btn btn-outline-primary" onclick="markAllAsRead()">
        <i class="fas fa-check-double me-2"></i>Tout marquer comme lu
    </button>
@endsection

@section('admin-content')
    <section class="admin-card">
        <div class="notifications-list">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data ?? [];
                    $message = $data['excerpt'] ?? $data['message'] ?? $data['body'] ?? 'Nouvelle notification';
                    $type = $data['type'] ?? 'default';
                    $ctaUrl = $data['button_url'] ?? $data['action_url'] ?? null;
                    $ctaText = $data['button_text'] ?? $data['action_text'] ?? 'Voir';
                @endphp
                <article class="notifications-item {{ $notification->read_at ? 'is-read' : 'is-unread' }}" data-id="{{ $notification->id }}">
                    <div class="notifications-item__icon">
                        @switch($type)
                            @case('course_enrolled')<i class="fas fa-user-plus"></i>@break
                            @case('course_completed')<i class="fas fa-certificate"></i>@break
                            @case('payment_received')<i class="fas fa-dollar-sign"></i>@break
                            @case('new_message')<i class="fas fa-envelope"></i>@break
                            @case('warning')<i class="fas fa-triangle-exclamation"></i>@break
                            @case('success')<i class="fas fa-circle-check"></i>@break
                            @default<i class="fas fa-bell"></i>@break
                        @endswitch
                    </div>
                    <div class="notifications-item__content">
                        <div class="notifications-item__header">
                            <strong>{{ $data['title'] ?? 'Notification' }}</strong>
                            <span class="notifications-item__time">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <p>{{ $message }}</p>
                        <div class="notifications-item__actions">
                            @if($ctaUrl)
                                <a href="{{ $ctaUrl }}" class="btn btn-outline-primary btn-sm" target="_blank">{{ $ctaText }}</a>
                            @endif
                            @if(!$notification->read_at)
                                <button class="btn btn-link btn-sm" onclick="markAsRead('{{ $notification->id }}')">
                                    <i class="fas fa-check me-1"></i>Marquer comme lu
                                </button>
                            @endif
                            <button class="btn btn-link btn-sm text-danger" onclick="deleteNotification('{{ $notification->id }}')">
                                <i class="fas fa-trash me-1"></i>Supprimer
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <div class="notifications-empty">
                    <i class="fas fa-bell-slash fa-2x"></i>
                    <p>Aucune notification pour le moment.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    </section>
@endsection

@push('styles')
<style>
    .notifications-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .notifications-item {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 1.25rem;
        padding: 1.25rem;
        border-radius: 1.1rem;
        border: 1px solid rgba(226, 232, 240, 0.6);
        background: #ffffff;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .notifications-item.is-unread {
        border-color: rgba(14, 165, 233, 0.5);
        box-shadow: 0 22px 45px -35px rgba(14, 165, 233, 0.3);
    }
    .notifications-item__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: rgba(14, 165, 233, 0.12);
        display: grid;
        place-items: center;
        font-size: 1.3rem;
        color: #0284c7;
    }
    .notifications-item__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 0.35rem;
    }
    .notifications-item__header strong {
        color: #0f172a;
        font-size: 1.05rem;
    }
    .notifications-item__time {
        color: #94a3b8;
        font-size: 0.8rem;
    }
    .notifications-item__content p {
        margin: 0 0 0.75rem;
        color: #475569;
        line-height: 1.5;
    }
    .notifications-item__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    .notifications-item__actions .btn-link {
        padding: 0.25rem 0.5rem;
        font-weight: 600;
    }
    .notifications-empty {
        text-align: center;
        padding: 2rem;
        border-radius: 1.25rem;
        background: rgba(226, 232, 240, 0.5);
        color: #64748b;
    }
    @media (max-width: 640px) {
        .notifications-item {
            grid-template-columns: 1fr;
        }
        .notifications-item__icon {
            width: 40px;
            height: 40px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function markAsRead(id) {
        fetch(`/notifications/${id}/mark-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(r => r.json())
        .then(() => window.location.reload());
    }

    function markAllAsRead() {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(r => r.json())
        .then(() => window.location.reload());
    }

    function deleteNotification(id) {
        if (!confirm('Supprimer cette notification ?')) return;
        fetch(`/notifications/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(r => r.json())
        .then(() => window.location.reload());
    }
</script>
@endpush









