<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donnez votre avis — {{ $course->title }}</title>
</head>
<body style="margin:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;background:#f8f9fa;color:#2c3e50;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:600px;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.06);">
                    <tr>
                        <td style="padding:32px 28px 16px;text-align:center;border-bottom:3px solid #003366;">
                            @if(!empty($logoUrl))
                                <img src="{{ $logoUrl }}" alt="Herime Académie" style="max-width:200px;height:auto;margin-bottom:16px;">
                            @endif
                            <h1 style="margin:0;font-size:22px;color:#003366;">Votre avis nous aide à progresser</h1>
                            <p style="margin:12px 0 0;font-size:14px;color:#6c757d;">Quelques secondes pour noter ce contenu.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <div style="background:linear-gradient(135deg,rgba(0,51,102,.06) 0%,rgba(0,64,128,.06) 100%);padding:20px;border-radius:10px;border-left:4px solid #003366;margin-bottom:24px;">
                                <h2 style="margin:0 0 8px;font-size:18px;color:#003366;">{{ $course->title }}</h2>
                                @if($course->provider)
                                    <p style="margin:0;font-size:14px;color:#6c757d;">par {{ $course->provider->name }}</p>
                                @endif
                            </div>
                            <p style="font-size:15px;line-height:1.6;margin:0 0 24px;">
                                Vous y avez accès : partagez une note et un commentaire pour guider les prochains apprenants.
                            </p>
                            <div style="text-align:center;">
                                <a href="{{ $ratingUrl }}" style="display:inline-block;background:linear-gradient(135deg,#003366 0%,#004080 100%);color:#fff;text-decoration:none;font-weight:600;font-size:15px;padding:14px 28px;border-radius:8px;">
                                    Noter ce contenu
                                </a>
                            </div>
                            <p style="font-size:12px;color:#94a3b8;margin-top:28px;text-align:center;">
                                Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
                                <span style="word-break:break-all;color:#64748b;">{{ $ratingUrl }}</span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
