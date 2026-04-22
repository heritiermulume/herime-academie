<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Recapitulatif formations live</title>
    <style>
        @page { margin: 20mm 12mm 20mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .header { border-bottom: 3px solid #003366; padding-bottom: 10px; margin-bottom: 12px; }
        .logo img { max-height: 46px; width: auto; }
        h1 { margin: 6px 0 4px 0; font-size: 18px; color: #003366; }
        .sub { color: #6b7280; font-size: 10px; }
        .badge { display: inline-block; margin-top: 6px; background: #003366; color: #fff; padding: 2px 8px; font-size: 10px; }
        .summary { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px; }
        .summary td { border: 1px solid #d1d5db; padding: 6px 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 5px; text-align: left; }
        th { background: #f3f4f6; color: #0f172a; }
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
    <div class="header">
        @if(!empty($logoBase64))
            <div class="logo"><img src="{{ $logoBase64 }}" alt="Herime Academie"></div>
        @endif
        <h1>Recapitulatif - Formations live</h1>
        <div class="sub">{{ $appName }} - Genere le {{ $generatedAt->format('d/m/Y H:i:s') }}</div>
        <span class="badge">Rapport administration</span>
    </div>

    <table class="summary">
        <tr><td><strong>Sessions</strong></td><td>{{ $summary['sessions_count'] }}</td></tr>
        <tr><td><strong>Presences enregistrees</strong></td><td>{{ $summary['participants_entries'] }}</td></tr>
        <tr><td><strong>Messages</strong></td><td>{{ $summary['messages_count'] }}</td></tr>
        <tr><td><strong>Moyenne messages / session</strong></td><td>{{ $summary['avg_messages_per_session'] }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Programme</th>
                <th>Salle</th>
                <th>Demarre par</th>
                <th>Debut</th>
                <th>Fin</th>
                <th>Statut</th>
                <th>Participants</th>
                <th>Messages</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sessions as $session)
                <tr>
                    <td>{{ $session->id }}</td>
                    <td>{{ $session->course?->title ?? '-' }}</td>
                    <td>{{ $session->room_name }}</td>
                    <td>{{ $session->starter?->name ?? '-' }}</td>
                    <td>{{ optional($session->started_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ optional($session->ended_at)->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $session->status }}</td>
                    <td>{{ $session->participants_count }}</td>
                    <td>{{ $session->messages_count }}</td>
                </tr>
            @empty
                <tr><td colspan="9">Aucune session.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="left">{{ $appName }} - Formations live</div>
        <div class="right">Page <span class="page-number"></span>/<span class="page-count"></span></div>
    </div>
</body>
</html>
