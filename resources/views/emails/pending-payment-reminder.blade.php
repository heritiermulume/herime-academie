<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalisez votre commande - {{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 3px solid #f59e0b;
        }
        .logo-container { margin-bottom: 20px; }
        .logo-container img { max-width: 200px; height: auto; }
        .header h1 { color: #003366; font-size: 28px; margin-bottom: 10px; font-weight: 700; }
        .header p { color: #6c757d; font-size: 14px; }
        .pending-badge {
            display: inline-block;
            padding: 8px 16px;
            background-color: #f59e0b;
            color: #ffffff;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .content { margin-bottom: 40px; }
        .content h2 { color: #003366; font-size: 24px; margin-bottom: 20px; font-weight: 600; }
        .content p { color: #495057; font-size: 16px; margin-bottom: 15px; }
        .order-details {
            background-color: #f8f9fa;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }
        .order-details h3 {
            color: #003366;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .order-details p { margin-bottom: 10px; color: #495057; }
        .order-details strong { color: #003366; font-weight: 600; }
        .order-lines-title {
            color: #003366;
            font-size: 16px;
            font-weight: 600;
            margin: 18px 0 10px;
        }
        .order-lines {
            margin: 0 0 8px 18px;
            padding: 0;
            color: #495057;
            font-size: 15px;
        }
        .order-lines li {
            margin-bottom: 6px;
        }
        .button-container { text-align: center; margin: 40px 0; }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background-color: #003366;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #004080;
        }
        .footer {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        .footer p { margin-bottom: 10px; }
        .footer a {
            color: #003366;
            text-decoration: none;
        }
    </style>
</head>
<body>
@php
    $emailHour = now()->timezone(config('app.timezone'))->hour;
    $timeGreeting = $emailHour < 12 ? 'Bonjour' : ($emailHour < 18 ? 'Bon après-midi' : 'Bonsoir');
@endphp
<div class="container">
    <div class="header">
        @if(isset($logoUrl))
            <div class="logo-container">
                <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}">
            </div>
        @endif
        <h1>Commande en attente</h1>
        <p>Votre paiement n'est pas encore finalisé</p>
    </div>

    <div class="content">
        <div class="pending-badge">! Paiement en attente</div>
        <h2>{{ $timeGreeting }} {{ $order->user->name ?? 'cher client' }},</h2>
        <p>Nous avons remarqué que votre commande est toujours en attente de paiement.</p>
        <p>Vous pouvez reprendre le paiement en cliquant sur le bouton ci-dessous.</p>

        <div class="order-details">
            <h3>Details de la commande</h3>
            <p><strong>Commande :</strong> {{ $order->order_number }}</p>
            <p><strong>Montant :</strong> {{ number_format((float) ($order->total_amount ?? $order->total ?? 0), 2) }} {{ $order->currency }}</p>
            <p><strong>Date :</strong> {{ $order->created_at->timezone(config('app.timezone'))->format('d/m/Y à H:i') }}</p>
            @if(!empty($orderLines))
                <p class="order-lines-title">Contenus de votre commande</p>
                <ul class="order-lines">
                    @foreach($orderLines as $line)
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="button-container">
            <a href="{{ $orderUrl }}" class="button">Retrouver ma commande et payer</a>
        </div>

        <p>Sans paiement, cette commande sera automatiquement annulée après quelques minutes.</p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé par <strong>{{ config('app.name') }}</strong></p>
        <p>
            <a href="{{ config('app.url') }}">Visiter le site</a> |
            <a href="{{ config('app.url') }}/contact">Nous contacter</a>
        </p>
    </div>
</div>
</body>
</html>
