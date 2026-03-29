<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Vidéo — défauts « hébergement mutualisé »
    |--------------------------------------------------------------------------
    |
    | Les valeurs par défaut (sans .env) supposent un mutualisé : souvent pas de FFmpeg,
    | pas de worker queue permanent, limites CPU / mémoire. Activez FFmpeg + HLS + faststart
    | sur un VPS ou un plan qui les fournit (voir .env.example, section VIDEO_*).
    |
    */

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

    // Nombre maximum de streams simultanés par utilisateur (plus bas = plus léger sur mutualisé)
    'max_concurrent_streams' => env('VIDEO_MAX_CONCURRENT_STREAMS', 2),

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

    // Optimisation streaming : moov atom au début (faststart). Requiert FFmpeg — désactivé par défaut (mutualisé).
    'optimize_faststart' => env('VIDEO_OPTIMIZE_FASTSTART', false),

    /*
    | Préchargement du lecteur HTML5 (vidéos hébergées sur la plateforme).
    | - metadata : charge surtout la durée et les infos ; la vidéo se remplit au fil de la lecture (recommandé, moins de données, mieux sur 3G/4G).
    | - none : minimum jusqu’au clic sur lecture (le client peut passer à metadata sur 2G pour éviter un premier clic « sans réaction »).
    | - auto : ancien comportement type « tout précharger » — plus lourd pour la facture data et les connexions lentes.
    */
    'player_preload' => env('VIDEO_PLAYER_PRELOAD', 'metadata'),

    /*
    | Taille des blocs lus côté PHP lors du streaming (octets).
    | Défaut 256 Ko : un peu plus léger en mémoire sur mutualisé ; sur VPS rapide : 524288 ou 2097152.
    */
    'stream_chunk_bytes' => (int) env('VIDEO_STREAM_CHUNK_BYTES', 262144),

    /*
    | HLS multi-débits : nécessite FFmpeg + idéalement queue worker. Désactivé par défaut (mutualisé).
    */
    'hls' => [
        'enabled' => env('VIDEO_HLS_ENABLED', false),
        'ffmpeg_path' => env('FFMPEG_PATH', 'ffmpeg'),
        'segment_seconds' => (int) env('VIDEO_HLS_SEGMENT_SECONDS', 6),
        'preset' => env('VIDEO_HLS_PRESET', 'veryfast'),
        'variants' => [
            ['height' => 720, 'bitrate' => '2500k', 'maxrate' => '2800k', 'bufsize' => '3500k'],
            ['height' => 480, 'bitrate' => '1000k', 'maxrate' => '1200k', 'bufsize' => '1500k'],
            ['height' => 360, 'bitrate' => '550k', 'maxrate' => '700k', 'bufsize' => '900k'],
        ],
    ],
];

