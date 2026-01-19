<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement confirmé - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
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
            border-bottom: 3px solid #003366;
        }
        .logo-container {
            margin-bottom: 20px;
        }
        .logo-container img {
            max-width: 200px;
            height: auto;
        }
        .header h1 {
            color: #003366;
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .header p {
            color: #6c757d;
            font-size: 14px;
        }
        .success-badge {
            display: inline-block;
            padding: 8px 16px;
            background-color: #28a745;
            color: #ffffff;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 40px;
        }
        .content h2 {
            color: #003366;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .content p {
            color: #495057;
            font-size: 16px;
            margin-bottom: 15px;
        }
        .order-details {
            background-color: #f8f9fa;
            border-left: 4px solid #003366;
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
        .order-details p {
            margin-bottom: 10px;
            color: #495057;
        }
        .order-details strong {
            color: #003366;
            font-weight: 600;
        }
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
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
        .footer p {
            margin-bottom: 10px;
        }
        .footer a {
            color: #003366;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(isset($logoUrl))
            <div class="logo-container">
                <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}">
            </div>
            @endif
            <h1>Paiement confirmé</h1>
            <p>Merci pour votre confiance !</p>
        </div>

        <div class="content">
            <div class="success-badge">✓ Paiement reçu</div>
            
            <h2>Bonjour {{ $order->user->name }} !</h2>
            
            <p>Nous sommes heureux de vous confirmer que votre paiement a bien été reçu.</p>

            <div class="order-details">
                <h3>Détails de la commande</h3>
                <p><strong>Numéro de commande :</strong> {{ $order->order_number }}</p>
                <p><strong>Montant :</strong> {{ number_format($order->total, 2) }} {{ $order->currency }}</p>
                @if($paidAtText)
                <p><strong>Date :</strong> {{ $paidAtText }}</p>
                @endif
            </div>

            <p>Vous avez maintenant accès à tous les {{ $accessLabel ?? 'contenus' }} que vous avez achetés.</p>

            <div class="button-container">
                <a href="{{ $orderUrl }}" class="button">Voir ma commande</a>
            </div>

            <p>Merci de votre confiance !</p>
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



