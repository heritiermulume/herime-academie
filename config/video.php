<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Video Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour la gestion sécurisée des vidéos YouTube
    |
    */

    // Durée de validité des tokens d'accès en heures
    'token_validity_hours' => env('VIDEO_TOKEN_VALIDITY_HOURS', 24),

    // Nombre maximum de streams simultanés par utilisateur
    'max_concurrent_streams' => env('VIDEO_MAX_CONCURRENT_STREAMS', 3),

    // Vérification stricte de l'adresse IP (doit correspondre exactement)
    'strict_ip_check' => env('VIDEO_STRICT_IP_CHECK', false),

    // Seuil d'accès suspects pour déclencher le blacklist
    'suspicious_access_threshold' => env('VIDEO_SUSPICIOUS_ACCESS_THRESHOLD', 100),

    // Configuration pour le watermark dynamique
    'watermark' => [
        'enabled' => env('VIDEO_WATERMARK_ENABLED', true),
        'position' => env('VIDEO_WATERMARK_POSITION', 'bottom-right'),
        'opacity' => env('VIDEO_WATERMARK_OPACITY', 0.7),
    ],

    // YouTube API Configuration
    'youtube' => [
        'api_key' => env('YOUTUBE_API_KEY'),
        'embed_domain' => env('YOUTUBE_EMBED_DOMAIN', 'herime-academie.com'),
    ],

    // Rotation des uploads (pour réduire les risques de fuite)
    'rotation' => [
        'enabled' => env('VIDEO_ROTATION_ENABLED', false),
        'rotation_interval_days' => env('VIDEO_ROTATION_INTERVAL_DAYS', 30),
    ],
];

