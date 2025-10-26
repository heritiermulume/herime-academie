<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure various security settings for the application.
    |
    */

    'settings' => [
        'enabled' => env('SECURITY_ENABLED', true),
        'encrypt_sensitive_data' => true,
        'log_security_events' => true,
        'rate_limiting' => true,
        'ip_whitelist' => [],
        'ip_blacklist' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Video Protection
    |--------------------------------------------------------------------------
    |
    | Here you may configure video protection settings.
    |
    */

    'video_protection' => [
        'prevent_download' => true,
        'prevent_screenshot' => true,
        'watermark_videos' => true,
        'watermark_text' => 'Herime Academie',
        'watermark_position' => 'bottom-right',
        'watermark_opacity' => 0.7,
        'session_timeout' => 30, // minutes
        'max_concurrent_sessions' => 3,
        'ip_restriction' => false,
        'domain_restriction' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Protection
    |--------------------------------------------------------------------------
    |
    | Here you may configure content protection settings.
    |
    */

    'content_protection' => [
        'prevent_copy' => true,
        'prevent_print' => true,
        'prevent_save' => true,
        'disable_right_click' => true,
        'disable_text_selection' => false,
        'disable_keyboard_shortcuts' => true,
        'obfuscate_source_code' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Security
    |--------------------------------------------------------------------------
    |
    | Here you may configure authentication security settings.
    |
    */

    'authentication' => [
        'max_login_attempts' => 5,
        'lockout_duration' => 15, // minutes
        'password_min_length' => 8,
        'password_require_special_chars' => true,
        'password_require_numbers' => true,
        'password_require_uppercase' => true,
        'password_require_lowercase' => true,
        'session_timeout' => 120, // minutes
        'remember_me_duration' => 30, // days
        'two_factor_authentication' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    |
    | Here you may configure API security settings.
    |
    */

    'api' => [
        'rate_limiting' => [
            'max_requests_per_minute' => 60,
            'max_requests_per_hour' => 1000,
        ],
        'require_authentication' => true,
        'require_https' => true,
        'cors_enabled' => true,
        'cors_origins' => ['*'],
        'cors_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
        'cors_headers' => ['Content-Type', 'Authorization'],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Here you may configure file upload security settings.
    |
    */

    'file_uploads' => [
        'scan_for_viruses' => true,
        'validate_file_types' => true,
        'max_file_size' => 100 * 1024 * 1024, // 100MB
        'allowed_file_types' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'videos' => ['mp4', 'avi', 'mov', 'wmv', 'flv'],
            'documents' => ['pdf', 'doc', 'docx', 'ppt', 'pptx'],
        ],
        'quarantine_suspicious_files' => true,
        'auto_compress_images' => true,
        'strip_metadata' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Security
    |--------------------------------------------------------------------------
    |
    | Here you may configure database security settings.
    |
    */

    'database' => [
        'encrypt_sensitive_fields' => true,
        'log_all_queries' => false,
        'backup_frequency' => 'daily',
        'backup_retention_days' => 30,
        'connection_timeout' => 30, // seconds
        'max_connections' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Alerting
    |--------------------------------------------------------------------------
    |
    | Here you may configure monitoring and alerting settings.
    |
    */

    'monitoring' => [
        'log_failed_logins' => true,
        'log_suspicious_activities' => true,
        'alert_on_brute_force' => true,
        'alert_on_unusual_activity' => true,
        'alert_email' => env('SECURITY_ALERT_EMAIL'),
        'log_retention_days' => 90,
    ],
];
