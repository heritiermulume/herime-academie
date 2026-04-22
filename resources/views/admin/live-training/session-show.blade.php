@extends('layouts.admin')

@section('title', 'Detail session live')
@section('admin-title', 'Detail de la session live')
@section('admin-subtitle', ($session->course?->title ?? 'Programme').' - Salle '.$session->room_name)
@section('admin-actions')
    <a href="{{ route('admin.live-training.sessions.export-details', $session) }}" class="btn btn-primary me-2">
        <i class="fas fa-file-csv me-2"></i>Exporter CSV
    </a>
    <a href="{{ route('admin.live-training.sessions.export-details-pdf', $session) }}" class="btn btn-light me-2">
        <i class="fas fa-file-pdf me-2"></i>Exporter PDF
    </a>
    <a href="{{ route('admin.live-training.sessions') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour
    </a>
@endsection

@php
    $formatDuration = function (int $seconds): string {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;
        return sprintf('%02dh %02dm %02ds', $h, $m, $s);
    };
@endphp

@section('admin-content')
    <div class="admin-stats-grid mb-4">
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Participants</p>
            <p class="admin-stat-card__value">{{ $stats['participants_count'] }}</p>
            <p class="admin-stat-card__muted">{{ $stats['unique_participants_count'] }} utilisateurs uniques</p>
        </div>
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Messages</p>
            <p class="admin-stat-card__value">{{ $stats['messages_count'] }}</p>
            <p class="admin-stat-card__muted">Messages de conversation</p>
        </div>
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Duree session</p>
            <p class="admin-stat-card__value" style="font-size:1.2rem">{{ $formatDuration($stats['session_duration_seconds']) }}</p>
            <p class="admin-stat-card__muted">Du debut a la fin</p>
        </div>
        <div class="admin-stat-card">
            <p class="admin-stat-card__label">Presence moyenne</p>
            <p class="admin-stat-card__value" style="font-size:1.2rem">{{ $formatDuration($stats['avg_duration_seconds']) }}</p>
            <p class="admin-stat-card__muted">Par participant enregistre</p>
        </div>
    </div>

    <section class="admin-panel mb-4">
        <div class="admin-panel__header">
            <h3><i class="fas fa-info-circle me-2"></i>Informations session</h3>
        </div>
        <div class="admin-panel__body">
            <div class="row g-3">
                <div class="col-md-3"><strong>Statut:</strong> {{ $session->status }}</div>
                <div class="col-md-3"><strong>Debut:</strong> {{ optional($session->started_at)->format('d/m/Y H:i:s') }}</div>
                <div class="col-md-3"><strong>Fin:</strong> {{ optional($session->ended_at)->format('d/m/Y H:i:s') ?? '-' }}</div>
                <div class="col-md-3"><strong>Demarre par:</strong> {{ $session->starter?->name ?? 'Inconnu' }}</div>
            </div>
        </div>
    </section>

    <section class="admin-panel mb-4">
        <div class="admin-panel__header">
            <h3><i class="fas fa-users me-2"></i>Participants</h3>
        </div>
        <div class="admin-panel__body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Nom affiche</th>
                            <th>Arrivee</th>
                            <th>Depart</th>
                            <th>Duree</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $participant)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $participant->user?->name ?? 'Utilisateur inconnu' }}</div>
                                    <small class="text-muted">{{ $participant->user?->email ?? '-' }}</small>
                                </td>
                                <td>{{ $participant->display_name ?? '-' }}</td>
                                <td>{{ optional($participant->joined_at)->format('d/m/Y H:i:s') }}</td>
                                <td>{{ optional($participant->left_at)->format('d/m/Y H:i:s') ?? '-' }}</td>
                                <td>{{ $formatDuration((int) $participant->duration_seconds) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Aucun participant enregistre.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3><i class="fas fa-comments me-2"></i>Messages de conversation</h3>
        </div>
        <div class="admin-panel__body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Expediteur</th>
                            <th>Type</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                            <tr>
                                <td>{{ optional($message->sent_at)->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $message->sender_name ?? $message->user?->name ?? '-' }}</td>
                                <td><span class="admin-chip admin-chip--info">{{ $message->message_type }}</span></td>
                                <td style="white-space: pre-wrap;">{{ $message->message }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Aucun message enregistre pour cette session.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
