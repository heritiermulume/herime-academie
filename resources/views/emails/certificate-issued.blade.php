<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Félicitations ! Votre certificat de complétion</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            max-width: none;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #f8f9fa;
            width: 100%;
            overflow-x: hidden;
            word-wrap: break-word;
        }
        .container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 40px;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 3px solid #003366;
        }
        .header h1 {
            color: #003366;
            font-size: 32px;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .success-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            color: #ffffff;
            margin-bottom: 20px;
        }
        .certificate-icon {
            font-size: 64px;
            color: #ffcc33;
            margin-bottom: 20px;
        }
        .message {
            background: linear-gradient(135deg, rgba(255, 204, 51, 0.15) 0%, rgba(255, 204, 51, 0.05) 100%);
            border-left: 4px solid #ffcc33;
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        .message p {
            color: #2c3e50;
            font-size: 16px;
            margin: 0;
            line-height: 1.8;
            word-wrap: break-word;
        }
        .course-card {
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.05) 0%, rgba(0, 64, 128, 0.05) 100%);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            border-left: 4px solid #003366;
        }
        .course-card h2 {
            color: #003366;
            font-size: 22px;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .certificate-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .certificate-info p {
            margin-bottom: 10px;
            font-size: 14px;
            color: #2c3e50;
        }
        .certificate-info strong {
            color: #003366;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 30px;
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
        }
        .button:hover {
            box-shadow: 0 6px 16px rgba(0, 51, 102, 0.4);
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
            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="certificate-icon">🎓</div>
            <h1>Félicitations !</h1>
            <span class="success-badge">Certificat de Complétion</span>
        </div>

        <div class="message">
            <p>
                <strong>Bravo {{ $user->name }} !</strong><br><br>
                Vous avez complété avec succès le cours <strong>{{ $course->title }}</strong> et avez démontré une compréhension approfondie des concepts enseignés.
            </p>
        </div>

        <div class="course-card">
            <h2>{{ $course->title }}</h2>
            @if($course->provider)
            @php
                $providerLabel = $course->is_downloadable ? 'Prestataire' : 'Formateur';
            @endphp
            <p style="color: #666; margin-top: 10px;">
                <strong>{{ $providerLabel }}:</strong> {{ $course->provider->name }}
            </p>
            @endif
        </div>

        <div class="certificate-info">
            <p><strong>Numéro de certificat:</strong> {{ $certificate->certificate_number }}</p>
            <p><strong>Date de délivrance:</strong> {{ $certificate->issued_at->format('d/m/Y') }}</p>
            <p style="margin-top: 15px; font-size: 13px; color: #666;">
                Votre certificat PDF est joint à cet email. Vous pouvez le télécharger et le partager sur vos réseaux professionnels.
            </p>
        </div>

        <div class="button-container">
            <a href="{{ route('customer.certificates') }}" class="button">Voir tous mes certificats</a>
        </div>

        <div class="footer">
            <p><strong>Herime Académie</strong></p>
            <p>Merci de votre engagement et félicitations pour votre réussite !</p>
            <p style="margin-top: 15px; font-size: 12px; color: #6c757d;">
                Cet email a été envoyé automatiquement le {{ now()->format('d/m/Y à H:i') }}
            </p>
        </div>
    </div>
</body>
</html>
















