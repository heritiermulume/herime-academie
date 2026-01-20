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
        /* Réinitialiser max-width pour les éléments qui doivent pouvoir dépasser */
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
            width: 100%;
            box-sizing: border-box;
            overflow-wrap: break-word;
            word-wrap: break-word;
            overflow: hidden;
        }
        .course-card h2 {
            color: #003366;
            font-size: 22px;
            margin-bottom: 15px;
            font-weight: 700;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
        }
        .course-description {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(0, 51, 102, 0.1);
            color: #2c3e50;
            font-size: 14px;
            line-height: 1.6;
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            word-wrap: break-word;
            overflow-wrap: break-word;
            width: 100%;
            box-sizing: border-box;
        }
        .course-description p {
            margin-bottom: 10px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
        }
        .course-description * {
            max-width: 100% !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
            box-sizing: border-box;
        }
        /* Limiter tous les éléments HTML dans la description */
        .course-description img {
            max-width: 100% !important;
            height: auto !important;
        }
        .course-description table {
            max-width: 100% !important;
            width: 100% !important;
            table-layout: fixed;
        }
        .course-description pre,
        .course-description code {
            max-width: 100% !important;
            overflow-x: auto;
            word-wrap: break-word;
            white-space: pre-wrap;
        }
        .course-meta {
            width: 100%;
            margin-top: 15px;
        }
        .course-meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            color: #2c3e50;
        }
        .course-meta-table tr {
            border-bottom: 1px solid rgba(0, 51, 102, 0.1);
        }
        .course-meta-table tr:last-child {
            border-bottom: none;
        }
        .course-meta-table td {
            padding: 10px 0;
            vertical-align: top;
        }
        .course-meta-table td:first-child {
            color: #003366;
            font-weight: 600;
            width: 35%;
            padding-right: 15px;
        }
        .course-meta-table td:last-child {
            color: #2c3e50;
            width: 65%;
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
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
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
        /* Styles pour éviter le débordement - appliqués à tous les éléments */
        html {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
        }
        img {
            max-width: 100% !important;
            height: auto !important;
            display: block;
        }
        table {
            max-width: 100% !important;
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse;
        }
        td, th {
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 0;
        }
        /* Assurer que le texte ne déborde pas */
        p, div, span, a, li {
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            max-width: 100% !important;
        }
        /* Limiter la largeur des éléments inline */
        pre, code {
            max-width: 100% !important;
            overflow-x: auto;
            word-wrap: break-word !important;
            white-space: pre-wrap !important;
        }
        /* Limiter les éléments de liste */
        ul, ol {
            max-width: 100% !important;
            padding-left: 20px;
        }
        /* Limiter les éléments de formulaire */
        input, textarea, select {
            max-width: 100% !important;
        }
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                width: 100% !important;
            }
            .logo-container img {
                max-width: 150px;
            }
            .course-card {
                padding: 20px;
                width: 100% !important;
            }
            .course-card h2 {
                font-size: 18px;
            }
            .course-description {
                max-height: 150px;
                font-size: 13px;
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
            <h1>Félicitations !</h1>
            <span class="success-badge">Inscription confirmée</span>
            <p>Votre inscription a été validée avec succès</p>
        </div>

        <div class="course-card">
            <h2>{{ $course->title }}</h2>
            <div class="course-meta">
                <table class="course-meta-table">
                    @if($course->provider)
                    <tr>
                        <td>Formateur</td>
                        <td>{{ $course->provider->name }}</td>
                    </tr>
                    @endif
                    @if($course->category)
                    <tr>
                        <td>Catégorie</td>
                        <td>{{ $course->category->name }}</td>
                    </tr>
                    @endif
                    @if($course->duration)
                    <tr>
                        <td>Durée</td>
                        <td>{{ $course->duration }}</td>
                    </tr>
                    @endif
                    @if($course->level)
                    <tr>
                        <td>Niveau</td>
                        <td>{{ ucfirst($course->level) }}</td>
                    </tr>
                    @endif
                    @if($course->short_description || $course->description)
                    <tr>
                        <td>Description</td>
                        <td>
                            @if($course->short_description)
                                {{ Str::limit(strip_tags($course->short_description), 200) }}
                            @elseif($course->description)
                                {{ Str::limit(strip_tags($course->description), 200) }}
                            @endif
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="message">
            <p>
                <strong>Bienvenue dans ce cours !</strong><br>
                Votre inscription a été confirmée avec succès. Vous pouvez maintenant accéder à tous les contenus du cours et commencer votre apprentissage immédiatement.
            </p>
        </div>

        <div class="button-container">
            <a href="{{ $courseUrl }}" class="button">{{ $buttonText ?? 'Commencer le cours maintenant' }}</a>
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
            <p><strong>Herime Académie</strong></p>
            <p>Merci de votre confiance et bon apprentissage !</p>
            <p>Pour toute question, n'hésitez pas à nous contacter.</p>
            <p style="margin-top: 15px; font-size: 12px; color: #6c757d;">
                Cet email a été envoyé automatiquement le {{ now()->format('d/m/Y à H:i') }}
            </p>
        </div>
    </div>
</body>
</html>


