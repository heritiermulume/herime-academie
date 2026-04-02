<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 25mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            padding: 0 5mm;
            max-width: 100%;
        }
        .header {
            text-align: center;
            padding-bottom: 14px;
            margin-bottom: 18px;
            border-bottom: 3px solid #003366;
        }
        .logo { margin-bottom: 10px; }
        .logo img { max-height: 52px; width: auto; display: block; margin: 0 auto; }
        .header h1 { font-size: 16pt; color: #003366; font-weight: bold; margin-bottom: 4px; }
        .header .sub { font-size: 9pt; color: #003366; opacity: 0.85; }
        .header .badge {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 12px;
            background: #003366;
            color: #fff;
            font-size: 9pt;
            font-weight: bold;
        }
        .content { margin-bottom: 22px; }
        .content-body { margin-bottom: 18px; padding: 0 2mm; }
        .content-body p { margin-bottom: 10px; }
        .content-body ul, .content-body ol { margin: 10px 0 10px 22px; }
        .content-body li { margin-bottom: 5px; }
        .content-body a { color: #003366; text-decoration: underline; }
        .details {
            background: #f0f4f8;
            border-left: 5px solid #003366;
            padding: 14px 16px;
            font-size: 10pt;
            margin: 0 2mm 16px;
        }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 5px 10px 5px 0; vertical-align: top; }
        .details td:first-child {
            font-weight: bold;
            width: 150px;
            color: #003366;
        }
        .contents-table { width: 100%; border-collapse: collapse; font-size: 9.5pt; margin-top: 8px; }
        .contents-table th {
            text-align: left;
            padding: 6px 8px;
            background: #003366;
            color: #fff;
            font-weight: bold;
        }
        .contents-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #dde4ec;
        }
        .footer {
            margin-top: 28px;
            padding-top: 14px;
            border-top: 2px solid #ffcc33;
            font-size: 9pt;
            color: #003366;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($logoBase64))
            <div class="logo">
                <img src="{{ $logoBase64 }}" alt="Herime Académie" />
            </div>
        @endif
        <h1>{{ $title }}</h1>
        <div class="sub">{{ config('app.name', 'Herime Académie') }}</div>
        <span class="badge">Reçu — pack de formations</span>
    </div>

    <div class="content">
        <div class="content-body">
            {!! $body !!}
        </div>

        <div class="details">
            <table>
                <tr><td>N° utilisateur</td><td>{{ $user->id }}</td></tr>
                <tr><td>Nom complet</td><td>{{ $user->name ?? $user->email ?? '—' }}</td></tr>
                <tr><td>Pack</td><td>{{ $package->title }}</td></tr>
                @if($package->subtitle)
                <tr><td>Sous-titre</td><td>{{ $package->subtitle }}</td></tr>
                @endif
                <tr><td>Date</td><td>{{ $purchaseDate }}</td></tr>
                <tr><td>Prix du pack (forfait)</td><td>{{ $amountFormatted }}</td></tr>
                @if($order)
                <tr><td>N° commande</td><td>{{ $order->order_number ?? $order->id }}</td></tr>
                <tr><td>ID commande</td><td>{{ $order->id }}</td></tr>
                @endif
            </table>
        </div>

        @if($courses->isNotEmpty())
        <div class="details" style="margin-top: 12px;">
            <p style="font-weight: bold; color: #003366; margin-bottom: 8px;">Contenus inclus ({{ $courses->count() }})</p>
            <table class="contents-table">
                <thead>
                    <tr>
                        <th style="width: 55%;">Titre</th>
                        <th>Type</th>
                        <th>Prestataire</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $c)
                    <tr>
                        <td>{{ $c->title }}</td>
                        <td>{{ $c->getContentTypeLabel() }}</td>
                        <td>{{ $c->provider?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} — {{ config('app.name', 'Herime Académie') }}
    </div>
</body>
</html>
