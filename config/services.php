<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Paiements: nous utilisons exclusivement Moneroo. Les autres intégrations sont désactivées.

    'moneroo' => [
        'base_url' => env('MONEROO_BASE_URL', 'https://api.moneroo.io/v1'),
        'api_key' => env('MONEROO_API_KEY'),
        'webhook_secret' => env('MONEROO_WEBHOOK_SECRET'), // Secret pour valider les signatures webhook
        'default_country' => env('MONEROO_DEFAULT_COUNTRY', 'SN'),
        'default_currency' => env('MONEROO_DEFAULT_CURRENCY', 'USD'),
        'company_name' => env('MONEROO_COMPANY_NAME', 'Herime Académie'), // Nom de l'entreprise affiché sur Moneroo
        'webhook_url' => env('MONEROO_WEBHOOK_URL', env('APP_URL') . '/moneroo/webhook'),
        'successful_url' => env('MONEROO_SUCCESSFUL_URL', env('APP_URL') . '/moneroo/success'),
        'failed_url' => env('MONEROO_FAILED_URL', env('APP_URL') . '/moneroo/failed'),
        'payout_callback_url' => env('MONEROO_PAYOUT_CALLBACK_URL', env('APP_URL') . '/api/moneroo/payout/callback'),
    ],

    'sso' => [
        'enabled' => env('SSO_ENABLED', true),
        'base_url' => env('SSO_BASE_URL', 'https://compte.herime.com'),
        'secret' => env('SSO_SECRET'),
        'timeout' => env('SSO_TIMEOUT', 10),
        // Si true, la déconnexion se fera uniquement localement sans passer par le SSO
        // Utile si le SSO ne redirige pas correctement après déconnexion
        'force_local_logout' => env('SSO_FORCE_LOCAL_LOGOUT', false),
        // Si true, désactive la validation stricte du token SSO
        // La validation locale sera utilisée même si l'API SSO est disponible
        'skip_strict_validation' => env('SSO_SKIP_STRICT_VALIDATION', false),
    ],

    'whatsapp' => [
        'base_url' => env('WHATSAPP_BASE_URL', 'http://localhost:8080'),
        'instance_name' => env('WHATSAPP_INSTANCE_NAME', 'default'),
        'api_key' => env('WHATSAPP_API_KEY', ''),
    ],

];
