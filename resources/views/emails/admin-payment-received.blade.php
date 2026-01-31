<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement effectué - {{ config('app.name') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #2c3e50; background-color: #f8f9fa; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 0 auto; background-color: #ffffff; padding: 32px; }
        .header { border-bottom: 3px solid #003366; padding-bottom: 20px; margin-bottom: 24px; }
        .header h1 { color: #003366; font-size: 22px; margin: 0 0 8px 0; }
        .muted { color: #6c757d; font-size: 13px; margin: 0; }
        .box { background: #f8f9fa; border-left: 4px solid #003366; padding: 16px; border-radius: 4px; margin: 16px 0; }
        .box p { margin: 6px 0; }
        .button { display: inline-block; padding: 12px 20px; background: #003366; color: #fff !important; text-decoration: none; border-radius: 6px; font-weight: 600; }
        ul { padding-left: 18px; }
        .footer { margin-top: 28px; padding-top: 18px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 13px; }
        .logo { max-width: 180px; height: auto; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(!empty($logoUrl))
                <img class="logo" src="{{ $logoUrl }}" alt="{{ config('app.name') }}">
            @endif
            <h1>Paiement effectué</h1>
            <p class="muted">{{ config('app.name') }} - Notification admin</p>
        </div>

        <p>Bonjour{{ !empty($adminName) ? ' ' . $adminName : '' }},</p>
        <p>Un utilisateur vient d’effectuer un paiement.</p>

        <div class="box">
            <p><strong>Commande :</strong> {{ $order->order_number }}</p>
            <p><strong>Montant :</strong> {{ number_format($order->total, 2) }} {{ $order->currency }}</p>
            <p><strong>Client :</strong> {{ $order->user?->name ?? '—' }} ({{ $order->user?->email ?? '—' }})</p>
            @if(!empty($order->paid_at))
                <p><strong>Date :</strong> {{ optional($order->paid_at)->timezone(config('app.timezone'))->format('d/m/Y à H:i') }}</p>
            @endif
        </div>

        <p><strong>Contenus achetés :</strong></p>
        <ul>
            @foreach($order->orderItems as $item)
                <li>{{ $item->course?->title ?? 'Contenu' }}</li>
            @endforeach
        </ul>

        @if(!empty($adminUrl))
            <p style="margin-top: 22px;">
                <a class="button" href="{{ $adminUrl }}">Voir la commande</a>
            </p>
        @endif

        <div class="footer">
            <p>Cet email a été envoyé par <strong>{{ config('app.name') }}</strong>.</p>
        </div>
    </div>
</body>
</html>

