<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact - {{ config('app.name') }}</title>
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
        .contact-details {
            background-color: #f8f9fa;
            border-left: 4px solid #003366;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }
        .contact-details h3 {
            color: #003366;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .contact-details p {
            margin-bottom: 10px;
            color: #495057;
        }
        .contact-details strong {
            color: #003366;
            font-weight: 600;
        }
        .message-box {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 18px;
            margin: 20px 0 30px 0;
        }
        .message-box h3 {
            color: #003366;
            margin-bottom: 12px;
            font-size: 18px;
            font-weight: 600;
        }
        .message-box p {
            margin-bottom: 0;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .meta {
            border-top: 1px solid #e9ecef;
            padding-top: 16px;
        }
        .meta p {
            margin-bottom: 8px;
            font-size: 14px;
            color: #6c757d;
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
        .footer-disclaimer {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 13px;
            color: #6c757d;
            line-height: 1.5;
            text-align: left;
        }
        @media (max-width: 600px) {
            .container {
                padding: 24px 20px;
            }
            .header h1 {
                font-size: 22px;
            }
            .content h2 {
                font-size: 20px;
            }
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
            <h1>Nouveau message de contact</h1>
            <p>Notification formulaire de contact</p>
        </div>

        <div class="content">
            <h2>{{ $timeGreeting }} {{ $recipientName }},</h2>
            <p>Vous avez reçu un nouveau message depuis le formulaire de contact de {{ config('app.name') }}.</p>

            <div class="contact-details">
                <h3>Informations du contact</h3>
                <p><strong>Nom :</strong> {{ $contactMessage->name }}</p>
                <p><strong>Email :</strong> <a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></p>
                @if($contactMessage->phone)
                <p><strong>Téléphone :</strong> <a href="tel:{{ $contactMessage->phone }}">{{ $contactMessage->phone }}</a></p>
                @endif
                <p><strong>Sujet :</strong> {{ $subjectLabel }}</p>
            </div>

            <div class="message-box">
                <h3>Message</h3>
                <p>{{ $contactMessage->message }}</p>
            </div>

            <div class="meta">
                <p><strong>Date :</strong> {{ $contactMessage->created_at->format('d/m/Y à H:i') }}</p>
                <p><strong>ID du message :</strong> #{{ $contactMessage->id }}</p>
            </div>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé par <strong>{{ config('app.name') }}</strong></p>
            <p>
                <a href="{{ config('app.url') }}">Visiter le site</a> |
                <a href="{{ config('app.url') }}/contact">Nous contacter</a>
            </p>
            <div class="footer-disclaimer">
                Ce message provient du formulaire de contact du site.
                Merci de traiter cette demande dans les meilleurs délais.
            </div>
        </div>
    </div>
</body>
</html>
