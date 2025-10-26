<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the notification channels used by the application.
    | You can add custom channels or modify existing ones as needed.
    |
    */

    'channels' => [
        'mail' => [
            'driver' => 'mail',
            'queue' => 'default',
        ],
        
        'database' => [
            'driver' => 'database',
            'table' => 'notifications',
        ],
        
        'broadcast' => [
            'driver' => 'broadcast',
            'queue' => 'default',
        ],
        
        'nexmo' => [
            'driver' => 'nexmo',
            'queue' => 'default',
        ],
        
        'slack' => [
            'driver' => 'slack',
            'queue' => 'default',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Types
    |--------------------------------------------------------------------------
    |
    | Here you may configure the different types of notifications that can be
    | sent by the application.
    |
    */

    'types' => [
        'course_enrolled' => [
            'title' => 'Inscription à un cours',
            'description' => 'Notification envoyée lorsqu\'un utilisateur s\'inscrit à un cours',
            'channels' => ['mail', 'database'],
        ],
        
        'course_completed' => [
            'title' => 'Cours terminé',
            'description' => 'Notification envoyée lorsqu\'un utilisateur termine un cours',
            'channels' => ['mail', 'database'],
        ],
        
        'new_message' => [
            'title' => 'Nouveau message',
            'description' => 'Notification envoyée lorsqu\'un utilisateur reçoit un nouveau message',
            'channels' => ['mail', 'database'],
        ],
        
        'payment_received' => [
            'title' => 'Paiement reçu',
            'description' => 'Notification envoyée lorsqu\'un paiement est reçu',
            'channels' => ['mail', 'database'],
        ],
        
        'course_published' => [
            'title' => 'Cours publié',
            'description' => 'Notification envoyée lorsqu\'un cours est publié',
            'channels' => ['mail', 'database'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure various settings for notifications.
    |
    */

    'settings' => [
        'max_notifications_per_user' => 1000,
        'notification_retention_days' => 90,
        'batch_size' => 100,
        'rate_limit' => [
            'max_attempts' => 5,
            'decay_minutes' => 60,
        ],
    ],
];
