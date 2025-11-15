<?php

return [
    'temporary' => [
        'max_age_minutes' => env('TEMP_UPLOAD_MAX_AGE', 1440), // 24h par défaut
        'home_cleanup_interval_minutes' => env('TEMP_UPLOAD_HOME_CLEAN_INTERVAL', 60),
    ],
];



