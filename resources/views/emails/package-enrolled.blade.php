<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès au pack — {{ $package->title }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #2c3e50; background-color: #f8f9fa; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 40px; box-sizing: border-box; }
        .header { text-align: center; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 3px solid #003366; }
        .header h1 { color: #003366; font-size: 26px; margin: 0 0 8px; font-weight: 700; }
        .header p { color: #6c757d; font-size: 14px; margin: 0; }
        .pack-card { background: linear-gradient(135deg, rgba(0, 51, 102, 0.06) 0%, rgba(0, 64, 128, 0.06) 100%); padding: 24px; border-radius: 12px; border-left: 4px solid #003366; margin-bottom: 28px; }
        .pack-card h2 { color: #003366; font-size: 20px; margin: 0 0 12px; font-weight: 700; }
        .message { background: linear-gradient(135deg, rgba(255, 204, 51, 0.1) 0%, rgba(255, 204, 51, 0.05) 100%); border-left: 4px solid #ffcc33; padding: 18px; margin-bottom: 28px; border-radius: 8px; }
        .message p { color: #856404; font-size: 15px; margin: 0; line-height: 1.7; }
        .button-container { text-align: center; margin: 28px 0; }
        .button { display: inline-block; padding: 14px 28px; background: linear-gradient(135deg, #003366 0%, #004080 100%); color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; }
        .features { margin: 24px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .features h3 { color: #003366; font-size: 17px; margin: 0 0 12px; font-weight: 600; }
        .features ul { list-style: none; padding: 0; margin: 0; }
        .features li { padding: 8px 0 8px 24px; position: relative; color: #2c3e50; font-size: 14px; }
        .features li:before { content: "✓"; position: absolute; left: 0; color: #003366; font-weight: bold; }
        .footer { margin-top: 36px; padding-top: 24px; border-top: 2px solid #e9ecef; text-align: center; color: #6c757d; font-size: 13px; }
    </style>
</head>
<body>
    @php
        $emailHour = now()->timezone(config('app.timezone'))->hour;
        $timeGreeting = $emailHour < 12 ? 'Bonjour' : ($emailHour < 18 ? 'Bon après-midi' : 'Bonsoir');
    @endphp


    <div class="container">
        <div class="header">
            @if(!empty($logoUrl))
                <div style="margin-bottom: 16px;">
                    <img src="{{ $logoUrl }}" alt="Herime Académie" style="max-width: 180px; height: auto;">
                </div>
            @endif
            <h1>Accès à votre pack</h1>
            <p>Herime Académie</p>
        </div>

        <div class="message">
            <p>
                {{ $timeGreeting }} {{ $recipientName }},<br><br>
                Votre accès au pack <strong>{{ $package->title }}</strong> est actif.
                Tous les contenus inclus sont disponibles depuis la page dédiée au pack — vous ne recevrez pas d’e-mails séparés pour chaque cours.
            </p>
        </div>

        <div class="pack-card">
            <h2>{{ $package->title }}</h2>
            @if($package->subtitle)
                <p style="margin: 0 0 12px; color: #495057; font-size: 14px;">{{ $package->subtitle }}</p>
            @endif
            <p style="margin: 0; font-size: 14px; color: #6c757d;">
                {{ $courseCount }} contenu{{ $courseCount > 1 ? 's' : '' }} inclus dans ce pack.
            </p>
        </div>

        <div class="features">
            <h3>Prochaines étapes</h3>
            <ul>
                <li>Ouvrir la page de votre pack pour voir la liste des formations</li>
                <li>Vous inscrire à chaque contenu en un clic depuis cette page</li>
                <li>Suivre votre progression depuis votre espace apprenant</li>
            </ul>
        </div>

        <div class="button-container">
            <a href="{{ $packUrl }}" class="button">Accéder à mon pack</a>
        </div>

        <div class="footer">
            <p><strong>Herime Académie</strong></p>
            <p>Besoin d’aide ? <a href="mailto:academie@herime.com">academie@herime.com</a></p>
        </div>
    </div>
</body>
</html>
