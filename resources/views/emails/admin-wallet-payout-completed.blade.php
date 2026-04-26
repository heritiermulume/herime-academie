<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payout wallet réussi - {{ config('app.name') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #2c3e50; background-color: #f8f9fa; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 0 auto; background-color: #ffffff; padding: 32px; }
        .header { border-bottom: 3px solid #198754; padding-bottom: 20px; margin-bottom: 24px; }
        .header h1 { color: #198754; font-size: 22px; margin: 0 0 8px 0; }
        .muted { color: #6c757d; font-size: 13px; margin: 0; }
        .box { background: #f8f9fa; border-left: 4px solid #198754; padding: 16px; border-radius: 4px; margin: 16px 0; }
        .box p { margin: 6px 0; }
        .button { display: inline-block; padding: 12px 20px; background: #003366; color: #fff !important; text-decoration: none; border-radius: 6px; font-weight: 600; }
        .footer { margin-top: 28px; padding-top: 18px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 13px; }
        .logo { max-width: 180px; height: auto; margin-bottom: 12px; }
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
                <img class="logo" src="{{ $logoUrl }}" alt="{{ config('app.name') }}">
            @endif
            <h1>Payout wallet réussi</h1>
            <p class="muted">{{ config('app.name') }} - Notification admin</p>
        </div>

        <p>{{ $timeGreeting }} {{ $adminName }},</p>
        <p>Un retrait (payout) depuis le portefeuille admin a été effectué avec succès.</p>

        <div class="box">
            <p><strong>Référence payout :</strong> #{{ $payout->id }}</p>
            <p><strong>Montant :</strong> {{ number_format((float) $payout->amount, 2) }} {{ $payout->currency ?? '—' }}</p>
            @if($payout->method === 'manual')
                <p><strong>Type :</strong> Retrait manuel</p>
                <p><strong>Description :</strong> {{ $payout->description ?? '—' }}</p>
            @else
                <p><strong>Compte destination :</strong></p>
                <p>— Téléphone : {{ $payout->phone ?? '—' }}</p>
                <p>— Opérateur / Méthode : {{ $payout->method ?? '—' }}</p>
                <p>— Pays : {{ $payout->country ?? '—' }}</p>
            @endif
            @if($payout->wallet && $payout->wallet->user)
                <p><strong>Portefeuille débité (titulaire) :</strong> {{ $payout->wallet->user->name ?? '—' }} ({{ $payout->wallet->user->email ?? '—' }})</p>
            @endif
            <p><strong>Solde restant (portefeuille débité) :</strong> {{ $payout->wallet ? number_format((float) $payout->wallet->available_balance, 2) : '—' }} {{ $payout->currency ?? '' }}</p>
            @if($payout->fee !== null)
                <p><strong>Frais :</strong> {{ number_format((float) $payout->fee, 2) }} {{ $payout->currency ?? '' }}</p>
            @endif
            @if($payout->completed_at)
                <p><strong>Date d'exécution :</strong> {{ $payout->completed_at->timezone(config('app.timezone'))->format('d/m/Y à H:i') }}</p>
            @endif
        </div>

        @if(!empty($adminUrl))
            <p style="margin-top: 22px;">
                <a class="button" href="{{ $adminUrl }}">Voir les paiements</a>
            </p>
        @endif

        <div class="footer">
            <p>Cet email a été envoyé par <strong>{{ config('app.name') }}</strong>.</p>
        </div>
    </div>
</body>
</html>
