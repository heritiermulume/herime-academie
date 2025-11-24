<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande supprimée - {{ $order->order_number }}</title>
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
            max-width: 120px;
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
        .warning-badge {
            display: inline-block;
            padding: 8px 16px;
            background-color: #dc3545;
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
            border-left: 4px solid #dc3545;
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
        .courses-list {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }
        .courses-list ul {
            list-style: none;
            padding: 0;
        }
        .courses-list li {
            padding: 8px 0;
            color: #495057;
            border-bottom: 1px solid #e9ecef;
        }
        .courses-list li:last-child {
            border-bottom: none;
        }
        .message {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
            border-left: 4px solid #dc3545;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        .message p {
            color: #721c24;
            font-size: 15px;
            margin: 0;
            line-height: 1.8;
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
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
        }
        .button:hover {
            background-color: #004080;
        }
        .footer {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 13px;
        }
        .footer p {
            margin-bottom: 8px;
        }
        .footer strong {
            color: #003366;
            font-size: 16px;
        }
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            .logo-container img {
                max-width: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(isset($logoUrl))
            <div class="logo-container">
                <img src="{{ $logoUrl }}" alt="Herime Académie Logo" />
            </div>
            @endif
            <h1>Commande supprimée</h1>
            <span class="warning-badge">Action administrative</span>
            <p>Votre commande a été supprimée par un administrateur</p>
        </div>

        <div class="content">
            <h2>Bonjour {{ $order->user->name }},</h2>
            <p>Nous vous informons que votre commande <strong>#{{ $order->order_number }}</strong> a été supprimée par un administrateur.</p>
        </div>

        <div class="order-details">
            <h3>Détails de la commande supprimée</h3>
            <p><strong>Numéro de commande :</strong> {{ $order->order_number }}</p>
            <p><strong>Date de commande :</strong> {{ $order->created_at->format('d/m/Y à H:i') }}</p>
            <p><strong>Montant total :</strong> {{ number_format($order->total, 2) }} {{ $order->currency }}</p>
            <p><strong>Statut :</strong> {{ ucfirst($order->status) }}</p>
            
            @if($order->orderItems && $order->orderItems->count() > 0)
            <div class="courses-list">
                <p><strong>Cours concernés :</strong></p>
                <ul>
                    @foreach($order->orderItems as $item)
                        @if($item->course)
                        <li>• {{ $item->course->title }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <div class="message">
            <p>
                <strong>Important :</strong><br>
                Suite à cette suppression, votre accès aux cours associés à cette commande a été retiré. 
                Si vous avez des questions ou souhaitez contester cette décision, veuillez nous contacter.
            </p>
        </div>

        <div class="button-container">
            <a href="{{ route('orders.index') }}" class="button">Voir mes commandes</a>
        </div>

        <div class="footer">
            <p><strong>Herime Académie</strong></p>
            <p>Pour toute question, n'hésitez pas à nous contacter.</p>
            <p style="margin-top: 15px; font-size: 12px; color: #6c757d;">
                Cet email a été envoyé automatiquement le {{ now()->format('d/m/Y à H:i') }}
            </p>
        </div>
    </div>
</body>
</html>

