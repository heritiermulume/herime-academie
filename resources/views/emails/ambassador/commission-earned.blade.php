<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle commission - {{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #2c3e50; background-color: #f8f9fa; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; }
        .header { text-align: center; margin-bottom: 40px; padding-bottom: 30px; border-bottom: 3px solid #003366; }
        .header h1 { color: #003366; font-size: 28px; margin-bottom: 10px; }
        .content { margin-bottom: 30px; }
        .commission-box { background: #f8f9fa; border-left: 4px solid #28a745; padding: 20px; margin: 20px 0; }
        .commission-amount { font-size: 32px; font-weight: bold; color: #28a745; }
        .footer { text-align: center; padding-top: 30px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Nouvelle Commission Gagnée !</h1>
            <p>Herime Académie</p>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $ambassador->user->name }}</strong>,</p>
            
            <p>Félicitations ! Vous avez gagné une nouvelle commission grâce à votre code promo.</p>
            
            <div class="commission-box">
                <p style="margin-bottom: 10px;"><strong>Détails de la commission :</strong></p>
                <div class="commission-amount">{{ number_format($commission->commission_amount, 2) }} {{ \App\Models\Setting::getBaseCurrency() }}</div>
                <p style="margin-top: 10px; color: #6c757d;">
                    Commande : <strong>{{ $order->order_number }}</strong><br>
                    Montant de la commande : {{ number_format($commission->order_total, 2) }} {{ \App\Models\Setting::getBaseCurrency() }}<br>
                    Taux de commission : {{ $commission->commission_rate }}%
                </p>
            </div>
            
            <p>Cette commission est actuellement en attente d'approbation. Une fois approuvée et payée, elle sera ajoutée à vos gains totaux.</p>
            
            <p style="margin-top: 20px;">
                <a href="{{ route('ambassador.dashboard') }}" style="display: inline-block; padding: 12px 24px; background: #003366; color: white; text-decoration: none; border-radius: 5px;">
                    Voir mon tableau de bord
                </a>
            </p>
        </div>
        
        <div class="footer">
            <p>Herime Académie - Programme Ambassadeur</p>
            <p>Continuez à partager votre code promo pour gagner plus !</p>
        </div>
    </div>
</body>
</html>








