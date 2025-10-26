<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Messaging Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure various settings for the messaging system.
    |
    */

    'settings' => [
        'enabled' => env('MESSAGING_ENABLED', true),
        'max_message_length' => 5000,
        'max_attachments_per_message' => 5,
        'max_attachment_size' => 10 * 1024 * 1024, // 10MB
        'message_retention_days' => 365,
        'auto_delete_old_messages' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Types
    |--------------------------------------------------------------------------
    |
    | Here you may configure the different message types available.
    |
    */

    'types' => [
        'general' => [
            'name' => 'Général',
            'description' => 'Message général',
            'icon' => 'fas fa-envelope',
        ],
        
        'course_related' => [
            'name' => 'Relatif au cours',
            'description' => 'Message lié à un cours spécifique',
            'icon' => 'fas fa-book',
        ],
        
        'support' => [
            'name' => 'Support',
            'description' => 'Message de support technique',
            'icon' => 'fas fa-life-ring',
        ],
        
        'feedback' => [
            'name' => 'Commentaires',
            'description' => 'Commentaires et suggestions',
            'icon' => 'fas fa-comment',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Statuses
    |--------------------------------------------------------------------------
    |
    | Here you may configure the different message statuses.
    |
    */

    'statuses' => [
        'sent' => [
            'name' => 'Envoyé',
            'description' => 'Message envoyé avec succès',
            'color' => '#28a745',
        ],
        
        'delivered' => [
            'name' => 'Livré',
            'description' => 'Message livré au destinataire',
            'color' => '#17a2b8',
        ],
        
        'read' => [
            'name' => 'Lu',
            'description' => 'Message lu par le destinataire',
            'color' => '#6c757d',
        ],
        
        'failed' => [
            'name' => 'Échoué',
            'description' => 'Échec de l\'envoi du message',
            'color' => '#dc3545',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure notification settings for messages.
    |
    */

    'notifications' => [
        'email_notifications' => true,
        'push_notifications' => true,
        'in_app_notifications' => true,
        'notification_delay' => 0, // seconds
        'digest_frequency' => 'daily', // hourly, daily, weekly
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure security settings for messages.
    |
    */

    'security' => [
        'encrypt_messages' => true,
        'log_all_messages' => true,
        'content_filtering' => true,
        'spam_protection' => true,
        'rate_limiting' => [
            'max_messages_per_hour' => 50,
            'max_messages_per_day' => 200,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure file upload settings for messages.
    |
    */

    'uploads' => [
        'allowed_file_types' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'documents' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
            'archives' => ['zip', 'rar', '7z'],
        ],
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'scan_for_viruses' => true,
        'auto_compress_images' => true,
    ],
];
