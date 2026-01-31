<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure various settings for payments.
    |
    */

    'settings' => [
        'currency' => 'USD',
        'currency_symbol' => '$',
        'decimal_places' => 2,
        'min_amount' => 0.01,
        'max_amount' => 10000.00,
        'refund_period_days' => 30,
        'hold_period_days' => 7, // Hold payments for 7 days before releasing to instructors
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Here you may configure the available payment methods.
    |
    */

    'methods' => [
        // Nous utilisons uniquement Moneroo
        'moneroo' => [
            'enabled' => true,
            'name' => 'Moneroo',
            'description' => 'Paiement via Moneroo',
            'icon' => 'fas fa-credit-card',
            'fees' => [
                'percentage' => 0.0,
                'fixed' => 0.0,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Statuses
    |--------------------------------------------------------------------------
    |
    | Here you may configure the different payment statuses.
    |
    */

    'statuses' => [
        'pending' => [
            'name' => 'En attente',
            'description' => 'Paiement en cours de traitement',
            'color' => '#ffc107',
        ],
        'completed' => [
            'name' => 'Terminé',
            'description' => 'Paiement traité avec succès',
            'color' => '#28a745',
        ],
        'failed' => [
            'name' => 'Échoué',
            'description' => 'Paiement échoué',
            'color' => '#dc3545',
        ],
        'cancelled' => [
            'name' => 'Annulé',
            'description' => 'Paiement annulé',
            'color' => '#6c757d',
        ],
        'refunded' => [
            'name' => 'Remboursé',
            'description' => 'Paiement remboursé',
            'color' => '#17a2b8',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Webhooks
    |--------------------------------------------------------------------------
    |
    | Here you may configure webhook settings for payment providers.
    |
    */

    'webhooks' => [
        // Le webhook Moneroo est géré via /moneroo/webhook (voir routes/web.php et MonerooController)
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Security
    |--------------------------------------------------------------------------
    |
    | Here you may configure security settings for payments.
    |
    */

    'security' => [
        'encrypt_sensitive_data' => true,
        'log_all_transactions' => true,
        'require_cvv' => true,
        'fraud_detection' => true,
        'ip_whitelist' => [], // Add trusted IPs if needed
    ],
];
