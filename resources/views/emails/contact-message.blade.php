<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <img src="{{ $logoUrl }}" alt="Herime Académie" style="max-width: 200px; margin-bottom: 20px;">
        <h1 style="color: #003366; margin: 0;">Nouveau message de contact</h1>
    </div>

    <div style="background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <p style="font-size: 16px; margin-bottom: 20px;">Vous avez reçu un nouveau message de contact depuis le site Herime Académie.</p>

        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h2 style="color: #003366; margin-top: 0; margin-bottom: 15px;">Informations du contact</h2>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 120px;">Nom :</td>
                    <td style="padding: 8px 0;">{{ $contactMessage->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Email :</td>
                    <td style="padding: 8px 0;"><a href="mailto:{{ $contactMessage->email }}" style="color: #0066cc;">{{ $contactMessage->email }}</a></td>
                </tr>
                @if($contactMessage->phone)
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Téléphone :</td>
                    <td style="padding: 8px 0;"><a href="tel:{{ $contactMessage->phone }}" style="color: #0066cc;">{{ $contactMessage->phone }}</a></td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Sujet :</td>
                    <td style="padding: 8px 0;">{{ $subjectLabel }}</td>
                </tr>
            </table>
        </div>

        <div style="background-color: #ffffff; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="color: #003366; margin-top: 0; margin-bottom: 15px;">Message</h3>
            <p style="white-space: pre-wrap; margin: 0;">{{ $contactMessage->message }}</p>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
            <p style="font-size: 14px; color: #666; margin: 0;">
                <strong>Date :</strong> {{ $contactMessage->created_at->format('d/m/Y à H:i') }}
            </p>
            <p style="font-size: 14px; color: #666; margin: 10px 0 0 0;">
                <strong>ID du message :</strong> #{{ $contactMessage->id }}
            </p>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 8px; text-align: center;">
        <p style="font-size: 14px; color: #666; margin: 0;">
            Ce message a été envoyé depuis le formulaire de contact de <a href="{{ config('app.url') }}" style="color: #0066cc;">Herime Académie</a>.
        </p>
    </div>
</body>
</html>
