<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MOKO Afrika Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'intégration avec MOKO Afrika Payment Gateway
    | Documentation: https://moko-africa-documentation.vercel.app
    |
    */

    'api_url' => env('MOKO_API_URL', 'https://paydrc.gofreshbakery.net/api/v5'),
    'token_url' => env('MOKO_TOKEN_URL', 'https://paydrc.gofreshbakery.net/api/v5/token'),
    'merchant_id' => env('MOKO_MERCHANT_ID'),
    'merchant_secret' => env('MOKO_MERCHANT_SECRET'),
    
    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Méthodes de paiement supportées par MOKO Afrika
    |
    */
    'payment_methods' => [
        'airtel' => [
            'name' => 'Airtel Money',
            'icon' => 'fas fa-mobile-alt',
            'color' => '#E60012',
        ],
        'orange' => [
            'name' => 'Orange Money',
            'icon' => 'fas fa-mobile-alt',
            'color' => '#FF6600',
        ],
        'mpesa' => [
            'name' => 'M-Pesa (Vodacom)',
            'icon' => 'fas fa-mobile-alt',
            'color' => '#00A86B',
        ],
        'africell' => [
            'name' => 'Afrimoney (Africell)',
            'icon' => 'fas fa-mobile-alt',
            'color' => '#FF0000',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Currencies
    |--------------------------------------------------------------------------
    |
    | Devises supportées par MOKO Afrika
    |
    */
    'currencies' => [
        'CDF' => 'Franc Congolais',
        'USD' => 'Dollar Américain',
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Settings
    |--------------------------------------------------------------------------
    |
    | Paramètres par défaut pour les transactions
    |
    */
    'default_currency' => env('MOKO_DEFAULT_CURRENCY', 'CDF'),
    'callback_url' => env('MOKO_CALLBACK_URL', env('APP_URL') . '/moko/callback'),
    'success_url' => env('MOKO_SUCCESS_URL', env('APP_URL') . '/moko/success'),
    'failure_url' => env('MOKO_FAILURE_URL', env('APP_URL') . '/moko/failure'),

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Paramètres de sécurité
    |
    */
    'token_expiry_minutes' => 20, // Durée de validité du token en minutes
    'max_retry_attempts' => 3, // Nombre maximum de tentatives de retry
    'timeout_seconds' => 30, // Timeout pour les requêtes API
];
