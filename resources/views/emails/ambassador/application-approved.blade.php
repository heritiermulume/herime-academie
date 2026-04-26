<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidature approuvée - {{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #2c3e50; background-color: #f8f9fa; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; }
        .header { text-align: center; margin-bottom: 40px; padding-bottom: 30px; border-bottom: 3px solid #003366; }
        .header h1 { color: #003366; font-size: 28px; margin-bottom: 10px; }
        .content { margin-bottom: 30px; }
        .promo-code-box { background: linear-gradient(135deg, #003366 0%, #004080 100%); color: white; padding: 30px; text-align: center; margin: 30px 0; border-radius: 10px; }
        .promo-code { font-size: 36px; font-weight: bold; letter-spacing: 3px; margin: 20px 0; }
        .footer { text-align: center; padding-top: 30px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    @php
        $emailHour = now()->timezone(config('app.timezone'))->hour;
        $timeGreeting = $emailHour < 12 ? 'Bonjour' : ($emailHour < 18 ? 'Bon après-midi' : 'Bonsoir');
    @endphp


    <div class="container">
        <div class="header">
            <h1>🎉 Félicitations !</h1>
            <p>Votre candidature a été approuvée</p>
        </div>
        
        <div class="content">
            <p>{{ $timeGreeting }} <strong>{{ $ambassador->user->name }}</strong>,</p>
            
            <p>Nous sommes ravis de vous informer que votre candidature au programme ambassadeur a été <strong>approuvée</strong> !</p>
            
            <p>Vous êtes maintenant officiellement ambassadeur de Herime Académie. Voici votre code promo unique :</p>
            
            @if($promoCode)
            <div class="promo-code-box">
                <p style="margin-bottom: 10px; font-size: 18px;">Votre Code Promo</p>
                <div class="promo-code">{{ $promoCode->code }}</div>
                <p style="margin-top: 10px; font-size: 14px; opacity: 0.9;">Partagez ce code avec votre réseau pour gagner des commissions !</p>
            </div>
            @endif
            
            <p><strong>Comment ça fonctionne :</strong></p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Partagez votre code promo avec votre réseau</li>
                <li>Lorsqu'un client utilise votre code lors d'un achat, vous gagnez une commission</li>
                <li>Le pourcentage de commission est configuré par l'administration</li>
                <li>Vous pouvez suivre vos gains depuis votre tableau de bord</li>
            </ul>
            
            <p style="margin-top: 20px;">
                <a href="{{ route('ambassador.dashboard') }}" style="display: inline-block; padding: 12px 24px; background: #003366; color: white; text-decoration: none; border-radius: 5px;">
                    Accéder à mon tableau de bord
                </a>
            </p>
        </div>
        
        <div class="footer">
            <p>Herime Académie - Programme Ambassadeur</p>
            <p>Bienvenue dans notre communauté d'ambassadeurs !</p>
        </div>
    </div>
</body>
</html>












