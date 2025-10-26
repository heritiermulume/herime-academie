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

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
    ],

    'mobile_money' => [
        'orange_money' => [
            'api_key' => env('ORANGE_MONEY_API_KEY'),
            'merchant_id' => env('ORANGE_MONEY_MERCHANT_ID'),
        ],
        'mpesa' => [
            'consumer_key' => env('MPESA_CONSUMER_KEY'),
            'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
            'business_short_code' => env('MPESA_BUSINESS_SHORT_CODE'),
        ],
    ],

    'maxicash' => [
        'merchant_id' => env('MAXICASH_MERCHANT_ID'),
        'merchant_password' => env('MAXICASH_MERCHANT_PASSWORD'),
        'api_url' => env('MAXICASH_API_URL', 'https://api.maxicashapp.com'),
        'gateway_url' => env('MAXICASH_GATEWAY_URL', 'https://api.maxicashapp.com/PayEntryPost'),
        'sandbox' => env('MAXICASH_SANDBOX', true),
    ],

];
