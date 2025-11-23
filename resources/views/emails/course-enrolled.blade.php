<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription confirmée - {{ $course->title }}</title>
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
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            color: #ffffff;
            margin-bottom: 20px;
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
        .course-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 14px;
            color: #2c3e50;
        }
        .course-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .course-meta-item strong {
            color: #003366;
            min-width: 100px;
        }
        .message {
            background: linear-gradient(135deg, rgba(255, 204, 51, 0.1) 0%, rgba(255, 204, 51, 0.05) 100%);
            border-left: 4px solid #ffcc33;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        .message p {
            color: #856404;
            font-size: 15px;
            margin: 0;
            line-height: 1.8;
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
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 51, 102, 0.4);
        }
        .features {
            margin: 30px 0;
            padding: 25px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .features h3 {
            color: #003366;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .features ul {
            list-style: none;
            padding: 0;
        }
        .features li {
            padding: 10px 0;
            padding-left: 25px;
            position: relative;
            color: #2c3e50;
            font-size: 14px;
        }
        .features li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #003366;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            margin-top: 40px;
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
            .logo-container img {
                max-width: 150px;
            }
            .course-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(isset($logoUrl))
            <div class="logo-container">
                <img src="{{ $logoUrl }}" alt="Herime Academie Logo" />
            </div>
            @endif
            <h1>Félicitations !</h1>
            <span class="success-badge">Inscription confirmée</span>
            <p>Votre inscription a été validée avec succès</p>
        </div>

        <div class="course-card">
            <h2>{{ $course->title }}</h2>
            <div class="course-meta">
                @if($course->instructor)
                <div class="course-meta-item">
                    <strong>Formateur :</strong>
                    <span>{{ $course->instructor->name }}</span>
                </div>
                @endif
                @if($course->category)
                <div class="course-meta-item">
                    <strong>Catégorie :</strong>
                    <span>{{ $course->category->name }}</span>
                </div>
                @endif
                @if($course->duration)
                <div class="course-meta-item">
                    <strong>Durée :</strong>
                    <span>{{ $course->duration }}</span>
                </div>
                @endif
                @if($course->level)
                <div class="course-meta-item">
                    <strong>Niveau :</strong>
                    <span>{{ ucfirst($course->level) }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="message">
            <p>
                <strong>Bienvenue dans ce cours !</strong><br>
                Votre inscription a été confirmée avec succès. Vous pouvez maintenant accéder à tous les contenus du cours et commencer votre apprentissage immédiatement.
            </p>
        </div>

        <div class="button-container">
            <a href="{{ $courseUrl }}" class="button">Commencer le cours maintenant</a>
        </div>

        <div class="features">
            <h3>Ce que vous pouvez faire maintenant :</h3>
            <ul>
                <li>Accéder à tous les modules et leçons du cours</li>
                <li>Suivre votre progression en temps réel</li>
                <li>Télécharger les ressources et supports de cours</li>
                <li>Interagir avec le formateur et les autres étudiants</li>
                <li>Obtenir un certificat à la fin du cours</li>
            </ul>
        </div>

        <div class="footer">
            <p><strong>Herime Academie</strong></p>
            <p>Merci de votre confiance et bon apprentissage !</p>
            <p>Pour toute question, n'hésitez pas à nous contacter.</p>
            <p style="margin-top: 15px; font-size: 12px; color: #6c757d;">
                Cet email a été envoyé automatiquement le {{ now()->format('d/m/Y à H:i') }}
            </p>
        </div>
    </div>
</body>
</html>


