<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte créé</title>
</head>
<body style="font-family: system-ui, -apple-system, sans-serif; line-height: 1.6; color: #333; max-width: 560px; margin: 0 auto; padding: 24px;">
    <h1 style="color: #003366; font-size: 22px;">Bienvenue sur {{ config('app.name') }}</h1>
    <p>Bonjour {{ $user->name }},</p>
    <p>Un compte a été créé pour finaliser votre commande depuis le panier.</p>
    <p><strong>Votre mot de passe temporaire :</strong></p>
    <p style="font-size: 18px; letter-spacing: 0.05em; background: #f4f6f8; padding: 12px 16px; border-radius: 8px; font-family: ui-monospace, monospace;">{{ $plainPassword }}</p>
    <p>Nous vous recommandons de le modifier après votre paiement depuis votre profil, lorsque cette option est disponible, ou d’utiliser la connexion Herime (compte.herime.com) avec la même adresse e-mail si vous y avez déjà un compte.</p>
    <p style="margin-top: 32px; font-size: 14px; color: #666;">— L’équipe {{ config('app.name') }}</p>
</body>
</html>
