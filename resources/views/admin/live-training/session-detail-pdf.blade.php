<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Detail session live</title>
    <style>
        @page { margin: 20mm 12mm 20mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .header { border-bottom: 3px solid #003366; padding-bottom: 10px; margin-bottom: 12px; }
        .logo img { max-height: 46px; width: auto; }
        h1 { margin: 6px 0 4px 0; font-size: 18px; color: #003366; }
        h2 { margin: 14px 0 6px 0; font-size: 13px; color: #003366; }
        .sub { color: #6b7280; font-size: 10px; }
        .badge { display: inline-block; margin-top: 6px; background: #003366; color: #fff; padding: 2px 8px; font-size: 10px; }
        .meta td { padding: 3px 8px 3px 0; vertical-align: top; }
        .cards { margin: 10px 0; width: 100%; border-collapse: collapse; }
        .cards td { border: 1px solid #d1d5db; padding: 6px 8px; width: 25%; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 5px; text-align: left; }
        th { background: #f3f4f6; }
        .footer {
            position: fixed;
            bottom: -12mm;
            left: 0;
            right: 0;
            height: 10mm;
            font-size: 9px;
            color: #475569;
            border-top: 1px solid #e2e8f0;
            padding-top: 3mm;
        }
        .footer .left { float: left; }
        .footer .right { float: right; }
        .page-number:before { content: counter(page); }
        .page-count:before { content: counter(pages); }
    </style>
</head>
<body>
    @php
        $formatDuration = function (int $seconds): string {
            $h = intdiv($seconds, 3600);
            $m = intdiv($seconds % 3600, 60);
            $s = $seconds % 60;
            return sprintf('%02dh %02dm %02ds', $h, $m, $s);
        };
    @endphp

    <div class="header">
        @if(!empty($logoBase64))
            <div class="logo"><img src="{{ $logoBase64 }}" alt="Herime Academie"></div>
        @endif
        <h1>Rapport detaille de session live</h1>
        <div class="sub">{{ $appName }} - Genere le {{ $generatedAt->format('d/m/Y H:i:s') }}</div>
        <span class="badge">Rapport de session</span>
    </div>

    <table class="meta">
        <tr><td><strong>Session ID</strong></td><td>{{ $session->id }}</td></tr>
        <tr><td><strong>Programme</strong></td><td>{{ $session->course?->title ?? '-' }}</td></tr>
        <tr><td><strong>Salle</strong></td><td>{{ $session->room_name }}</td></tr>
        <tr><td><strong>Démarré par</strong></td><td>{{ $session->starter?->name ?? '-' }} ({{ $session->starter?->email ?? '-' }})</td></tr>
        <tr><td><strong>Début</strong></td><td>{{ optional($session->started_at)->format('d/m/Y H:i:s') }}</td></tr>
        <tr><td><strong>Fin</strong></td><td>{{ optional($session->ended_at)->format('d/m/Y H:i:s') ?? '-' }}</td></tr>
        <tr><td><strong>Statut</strong></td><td>{{ $session->status }}</td></tr>
    </table>

    <table class="cards">
        <tr>
            <td><strong>Participants</strong><br>{{ $stats['participants_count'] }}</td>
            <td><strong>Participants uniques</strong><br>{{ $stats['unique_participants_count'] }}</td>
            <td><strong>Messages</strong><br>{{ $stats['messages_count'] }}</td>
            <td><strong>Durée session</strong><br>{{ $formatDuration($stats['session_duration_seconds']) }}</td>
        </tr>
    </table>

    <h2>Participants</h2>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Nom affiché</th>
                <th>Arrivée</th>
                <th>Départ</th>
                <th>Durée</th>
            </tr>
        </thead>
        <tbody>
            @forelse($participants as $participant)
                <tr>
                    <td>{{ $participant->user?->name ?? 'Inconnu' }}</td>
                    <td>{{ $participant->user?->email ?? '-' }}</td>
                    <td>{{ $participant->display_name ?? '-' }}</td>
                    <td>{{ optional($participant->joined_at)->format('d/m/Y H:i:s') }}</td>
                    <td>{{ optional($participant->left_at)->format('d/m/Y H:i:s') ?? '-' }}</td>
                    <td>{{ $formatDuration((int) $participant->duration_seconds) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Aucun participant enregistré.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Messages de conversation</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Expéditeur</th>
                <th>Type</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
            @forelse($messages as $message)
                <tr>
                    <td>{{ optional($message->sent_at)->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $message->sender_name ?? $message->user?->name ?? '-' }}</td>
                    <td>{{ $message->message_type }}</td>
                    <td>{{ $message->message }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Aucun message enregistré.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">
        <div class="left">{{ $appName }} - Session #{{ $session->id }}</div>
        <div class="right">Page <span class="page-number"></span>/<span class="page-count"></span></div>
    </div>
</body>
</html>
