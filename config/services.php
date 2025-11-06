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

    // Paiements: nous utilisons exclusivement pawaPay. Les autres intégrations sont désactivées.

    'pawapay' => [
        'base_url' => env('PAWAPAY_BASE_URL', 'https://api.sandbox.pawapay.io/v2'),
        'api_key' => env('PAWAPAY_API_KEY'),
        'webhook_secret' => env('PAWAPAY_WEBHOOK_SECRET'), // Secret pour valider les signatures webhook
        'default_country' => env('PAWAPAY_DEFAULT_COUNTRY', 'COD'),
        'default_currency' => env('PAWAPAY_DEFAULT_CURRENCY', 'CDF'),
        // Public URLs where the provider can redirect after auth (Wave, etc.)
        'successful_url' => env('PAWAPAY_SUCCESSFUL_URL', env('APP_URL') . '/payments/pawapay/success'),
        'failed_url' => env('PAWAPAY_FAILED_URL', env('APP_URL') . '/payments/pawapay/failed'),
    ],

    'sso' => [
        'enabled' => env('SSO_ENABLED', true),
        'base_url' => env('SSO_BASE_URL', 'https://compte.herime.com'),
        'secret' => env('SSO_SECRET'),
        'timeout' => env('SSO_TIMEOUT', 10),
        // Si true, la déconnexion se fera uniquement localement sans passer par le SSO
        // Utile si le SSO ne redirige pas correctement après déconnexion
        'force_local_logout' => env('SSO_FORCE_LOCAL_LOGOUT', false),
    ],

];
