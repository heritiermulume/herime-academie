<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre reçu d'inscription</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { border-bottom: 2px solid #003366; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { color: #003366; font-size: 20px; margin: 0 0 4px 0; }
        .header p { color: #666; font-size: 14px; margin: 0; }
        .message { margin-bottom: 20px; }
        .btn { display: inline-block; padding: 12px 24px; background: #003366; color: #fff !important; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 12px; }
        .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #eee; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    @php
        $emailHour = now()->timezone(config('app.timezone'))->hour;
        $timeGreeting = $emailHour < 12 ? 'Bonjour' : ($emailHour < 18 ? 'Bon après-midi' : 'Bonsoir');
    @endphp


    <div class="container">
        <div class="header">
            <h1>Votre reçu d'inscription</h1>
            <p>Herime Académie – {{ $course->title }}</p>
        </div>
        <div class="message">
            <p>{{ $timeGreeting }} {{ $recipientName }},</p>
            <p>Veuillez trouver en pièce jointe votre reçu d'inscription au {{ $course->getContentLabel() }} <strong>{{ $course->title }}</strong>.</p>
            <p>
                <a href="{{ $courseUrl }}" class="btn">Accéder au {{ $course->getContentLabel() }}</a>
            </p>
        </div>
        <div class="footer">
            Ce message a été envoyé automatiquement. Merci pour votre confiance.
        </div>
    </div>
</body>
</html>
