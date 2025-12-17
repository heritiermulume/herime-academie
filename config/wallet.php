<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Wallet Holding Period
    |--------------------------------------------------------------------------
    |
    | Nombre de jours pendant lesquels les fonds sont bloqués avant d'être
    | disponibles au retrait. Cette période permet de gérer les litiges,
    | remboursements et autres cas nécessitant un délai de sécurité.
    |
    | Valeurs recommandées :
    | - 7 jours : Standard pour la plupart des plateformes
    | - 14 jours : Sécurité renforcée
    | - 30 jours : Maximum de sécurité
    |
    */
    'holding_period_days' => env('WALLET_HOLDING_PERIOD_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Wallet Minimum Payout Amount
    |--------------------------------------------------------------------------
    |
    | Montant minimum pour effectuer un retrait.
    |
    */
    'minimum_payout_amount' => env('WALLET_MINIMUM_PAYOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Auto-release Schedule
    |--------------------------------------------------------------------------
    |
    | Configuration du scheduler pour la libération automatique des fonds.
    | Les valeurs possibles sont : 'hourly', 'daily', 'twiceDaily', etc.
    |
    */
    'auto_release_schedule' => env('WALLET_AUTO_RELEASE_SCHEDULE', 'daily'),
];

