<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isNewCode ? 'Nouveau code promo' : 'Code promo mis à jour' }} - {{ config('app.name') }}</title>
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
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 3px solid #003366;
            position: relative;
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
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .header p {
            color: #6c757d;
            font-size: 14px;
        }
        .content {
            margin-bottom: 30px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .promo-code-box {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            color: white;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
            border-radius: 8px;
        }
        .promo-code-label {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #ffffff;
            opacity: 0.95;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .promo-code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 4px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            color: #ffffff;
        }
        .promo-code-hint {
            font-size: 13px;
            color: #ffffff;
            opacity: 0.9;
            margin-top: 15px;
        }
        .warning-box {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%);
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .warning-box p {
            color: #856404;
            font-size: 14px;
            margin: 8px 0;
        }
        .warning-box strong {
            color: #003366;
            font-size: 15px;
        }
        .info-section {
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.05) 0%, rgba(0, 64, 128, 0.05) 100%);
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            border-left: 4px solid #003366;
        }
        .info-section h3 {
            color: #003366;
            font-size: 16px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .info-section ul {
            margin-left: 20px;
            margin-top: 10px;
        }
        .info-section li {
            margin-bottom: 8px;
            font-size: 14px;
            color: #2c3e50;
        }
        .cta-button {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
            text-align: center;
        }
        .cta-button:hover {
            background: linear-gradient(135deg, #004080 0%, #0050a0 100%);
        }
        .footer {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 13px;
            background-color: #f8f9fa;
            padding: 30px 20px;
            border-radius: 8px;
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
            .promo-code {
                font-size: 24px;
                letter-spacing: 2px;
            }
            .logo-container img {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <img src="{{ config('app.url') }}/images/logo-herime-academie.png" alt="Herime Académie Logo" />
            </div>
            <h1>{{ $isNewCode ? 'NOUVEAU CODE PROMO' : 'CODE PROMO MIS À JOUR' }}</h1>
            <p>{{ $isNewCode ? 'Un nouveau code promo vous a été attribué' : 'Votre code promo a été modifié' }}</p>
        </div>
        
        <div class="content">
            <p class="greeting">Bonjour <strong>{{ $ambassador->user->name }}</strong>,</p>
            
            @if($isNewCode && $oldPromoCode)
                <p>Nous vous informons qu'un <strong>nouveau code promo</strong> vous a été généré.</p>
                
                <div class="warning-box">
                    <p><strong>⚠️ Important :</strong></p>
                    <p>Vous aviez précédemment le code <strong>{{ $oldPromoCode->code }}</strong>.</p>
                    <p><strong>Veuillez désormais utiliser uniquement votre nouveau code promo ci-dessous.</strong></p>
                    <p>L'ancien code n'est plus actif et ne générera plus de commissions.</p>
                </div>
            @elseif($isNewCode)
                <p>Un <strong>nouveau code promo</strong> vous a été généré pour votre compte ambassadeur.</p>
            @else
                <p>Votre code promo a été <strong>modifié</strong>.</p>
                
                @if($oldPromoCode)
                    <div class="warning-box">
                        <p><strong>⚠️ Important :</strong></p>
                        <p>Vous aviez précédemment le code <strong>{{ $oldPromoCode->code }}</strong>.</p>
                        <p><strong>Veuillez désormais utiliser uniquement votre nouveau code promo ci-dessous.</strong></p>
                        <p>L'ancien code n'est plus actif et ne générera plus de commissions.</p>
                    </div>
                @endif
            @endif
            
            <div class="promo-code-box">
                <div class="promo-code-label">{{ $isNewCode ? 'Votre Nouveau Code Promo' : 'Votre Code Promo' }}</div>
                <div class="promo-code">{{ $promoCode->code }}</div>
                <div class="promo-code-hint">Partagez ce code avec votre réseau pour gagner des commissions !</div>
            </div>
            
            <div class="info-section">
                <h3>Comment utiliser votre code promo</h3>
                <ul>
                    <li>Partagez votre code promo avec votre réseau</li>
                    <li>Lorsqu'un client utilise votre code lors d'un achat, vous gagnez une commission</li>
                    <li>Vous pouvez suivre vos gains depuis votre tableau de bord ambassadeur</li>
                </ul>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ route('ambassador.dashboard') }}" class="cta-button">
                    Accéder à mon tableau de bord
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>Herime Académie</strong></p>
            <p>Programme Ambassadeur</p>
            <p>Merci de faire partie de notre communauté d'ambassadeurs !</p>
            <p style="margin-top: 15px; font-size: 12px; color: #6c757d;">
                Cet email a été envoyé automatiquement le {{ now()->format('d/m/Y à H:i') }}
            </p>
        </div>
    </div>
</body>
</html>

