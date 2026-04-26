<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue - {{ config('app.name') }}</title>
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
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 3px solid #003366;
        }
        .logo-container { margin-bottom: 18px; }
        .logo-container img { max-width: 200px; height: auto; }
        .header h1 {
            color: #003366;
            font-size: 27px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .header p { color: #6c757d; font-size: 14px; }
        .content p { font-size: 16px; color: #495057; margin-bottom: 16px; }
        .highlight {
            background-color: #f8f9fa;
            border-left: 4px solid #003366;
            border-radius: 4px;
            padding: 20px;
            margin: 24px 0;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 30px;
            background-color: #003366;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
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
            <h1>Bienvenue sur Herime Académie</h1>
            <p>Nous sommes ravis de vous accompagner dans votre parcours.</p>
        </div>

        <div class="content">
            <p><strong>{{ $timeGreeting }} {{ $recipientName }},</strong></p>

            <p>Nous sommes honorés de vous accueillir au sein de Herime Académie et heureux de vous accompagner dans votre parcours d'évolution.</p>

            <p>Permettez-nous de vous partager l'origine de cette initiative.</p>

            <div class="highlight">
                <p><strong>Notre histoire est née d'un constat simple :</strong></p>
                <p>
                    En Afrique centrale, de nombreux talents et professionnels ont besoin d'un accès plus simple
                    à une éducation de qualité et à des ressources professionnelles fiables pour évoluer.
                </p>
            </div>

            <p>
                C'est pourquoi nous avons créé Herime Académie : une plateforme simple, intuitive et puissante,
                pensée pour que chaque utilisateur trouve des contenus, des formations et ressources utiles pour progresser.
            </p>

            <p>
                Notre ambition est claire : permettre à chaque apprenant et professionnel de développer ses
                compétences à son rythme, d'accéder à des contenus utiles et de transformer ses ambitions en
                résultats concrets.
            </p>

            <p>
                Pour découvrir notre vision complète,
                <a href="{{ route('about') }}">consultez notre page À propos</a>.
            </p>

            <div class="button-container">
                <a href="{{ route('contents.index') }}" class="button">Découvrir les contenus</a>
            </div>

            <p>
                Aujourd'hui, Herime Académie est devenue une communauté dynamique de créateurs et de professionnels
                qui se soutiennent et se développent. Nous sommes fiers de vous avoir parmi nous et de vous aider
                à réaliser vos ambitions.
            </p>

            <p>Au plaisir de vous retrouver très bientôt,<br><strong>L'équipe Herime Académie</strong></p>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé par <strong>{{ config('app.name') }}</strong></p>
            <p>
                <a href="{{ config('app.url') }}">Visiter le site</a> |
                <a href="{{ route('contact') }}">Nous contacter</a>
            </p>
        </div>
    </div>
</body>
</html>
