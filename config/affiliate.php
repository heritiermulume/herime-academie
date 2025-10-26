<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Affiliate Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure various settings for the affiliate program.
    |
    */

    'settings' => [
        'enabled' => env('AFFILIATE_ENABLED', true),
        'default_commission_rate' => 10.0, // percentage
        'min_payout_amount' => 50.0,
        'payout_frequency' => 'monthly', // weekly, monthly, quarterly
        'cookie_lifetime' => 30, // days
        'auto_approve' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Commission Rates
    |--------------------------------------------------------------------------
    |
    | Here you may configure commission rates for different scenarios.
    |
    */

    'commission_rates' => [
        'default' => 10.0,
        'premium' => 15.0,
        'vip' => 20.0,
        'new_affiliate' => 5.0, // First 30 days
    ],

    /*
    |--------------------------------------------------------------------------
    | Payout Methods
    |--------------------------------------------------------------------------
    |
    | Here you may configure the available payout methods.
    |
    */

    'payout_methods' => [
        'bank_transfer' => [
            'enabled' => true,
            'name' => 'Virement bancaire',
            'description' => 'Paiement direct sur votre compte bancaire',
            'processing_days' => 3,
        ],
        
        'mobile_money' => [
            'enabled' => true,
            'name' => 'Mobile Money',
            'description' => 'Paiement via Mobile Money',
            'processing_days' => 1,
        ],
        
        'paypal' => [
            'enabled' => true,
            'name' => 'PayPal',
            'description' => 'Paiement via PayPal',
            'processing_days' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Affiliate Tiers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the different affiliate tiers.
    |
    */

    'tiers' => [
        'bronze' => [
            'name' => 'Bronze',
            'min_sales' => 0,
            'commission_rate' => 10.0,
            'benefits' => [
                'Commission de base',
                'Support par email',
            ],
        ],
        
        'silver' => [
            'name' => 'Argent',
            'min_sales' => 1000,
            'commission_rate' => 12.0,
            'benefits' => [
                'Commission augmentée',
                'Support prioritaire',
                'Outils marketing avancés',
            ],
        ],
        
        'gold' => [
            'name' => 'Or',
            'min_sales' => 5000,
            'commission_rate' => 15.0,
            'benefits' => [
                'Commission maximale',
                'Support dédié',
                'Outils marketing premium',
                'Accès aux webinaires exclusifs',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Marketing Materials
    |--------------------------------------------------------------------------
    |
    | Here you may configure marketing materials for affiliates.
    |
    */

    'marketing_materials' => [
        'banners' => [
            'enabled' => true,
            'sizes' => [
                '728x90' => 'Leaderboard',
                '300x250' => 'Medium Rectangle',
                '160x600' => 'Wide Skyscraper',
            ],
        ],
        
        'text_links' => [
            'enabled' => true,
            'templates' => [
                'basic' => 'Découvrez ce cours incroyable : {course_title}',
                'detailed' => 'Apprenez {course_title} avec {instructor_name} - {course_description}',
            ],
        ],
        
        'social_media' => [
            'enabled' => true,
            'platforms' => ['facebook', 'twitter', 'linkedin', 'instagram'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracking Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure tracking settings for affiliates.
    |
    */

    'tracking' => [
        'cookie_name' => 'affiliate_id',
        'cookie_duration' => 30, // days
        'track_clicks' => true,
        'track_conversions' => true,
        'track_commissions' => true,
    ],
];
