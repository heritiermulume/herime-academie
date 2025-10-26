<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Newsletter Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure various settings for the newsletter.
    |
    */

    'settings' => [
        'enabled' => env('NEWSLETTER_ENABLED', true),
        'require_confirmation' => true,
        'double_opt_in' => true,
        'max_subscribers' => 10000,
        'unsubscribe_grace_period' => 7, // days
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Templates
    |--------------------------------------------------------------------------
    |
    | Here you may configure email templates for the newsletter.
    |
    */

    'templates' => [
        'welcome' => [
            'subject' => 'Bienvenue dans notre newsletter !',
            'template' => 'newsletter.welcome',
        ],
        
        'confirmation' => [
            'subject' => 'Confirmez votre inscription à notre newsletter',
            'template' => 'newsletter.confirmation',
        ],
        
        'unsubscribe' => [
            'subject' => 'Vous avez été désabonné de notre newsletter',
            'template' => 'newsletter.unsubscribe',
        ],
        
        'newsletter' => [
            'subject' => 'Newsletter Herime Academie - {date}',
            'template' => 'newsletter.newsletter',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Newsletter Categories
    |--------------------------------------------------------------------------
    |
    | Here you may configure the different newsletter categories.
    |
    */

    'categories' => [
        'general' => [
            'name' => 'Général',
            'description' => 'Actualités générales et annonces',
            'enabled' => true,
        ],
        
        'courses' => [
            'name' => 'Nouveaux cours',
            'description' => 'Notifications sur les nouveaux cours',
            'enabled' => true,
        ],
        
        'promotions' => [
            'name' => 'Promotions',
            'description' => 'Offres spéciales et promotions',
            'enabled' => true,
        ],
        
        'blog' => [
            'name' => 'Articles de blog',
            'description' => 'Nouveaux articles de blog',
            'enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sending Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure sending settings for the newsletter.
    |
    */

    'sending' => [
        'batch_size' => 100,
        'delay_between_batches' => 60, // seconds
        'max_emails_per_hour' => 1000,
        'retry_failed_after' => 24, // hours
        'max_retries' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure analytics settings for the newsletter.
    |
    */

    'analytics' => [
        'track_opens' => true,
        'track_clicks' => true,
        'track_unsubscribes' => true,
        'retention_period' => 365, // days
    ],

    /*
    |--------------------------------------------------------------------------
    | Spam Protection
    |--------------------------------------------------------------------------
    |
    | Here you may configure spam protection settings.
    |
    */

    'spam_protection' => [
        'honeypot_field' => 'website',
        'rate_limiting' => [
            'max_attempts' => 3,
            'decay_minutes' => 60,
        ],
        'ip_blacklist' => [],
        'email_blacklist' => [],
    ],
];
