<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Course Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure various settings for courses.
    |
    */

    'settings' => [
        'max_courses_per_instructor' => 100,
        'max_lessons_per_course' => 500,
        'max_file_size' => 100 * 1024 * 1024, // 100MB
        'allowed_video_formats' => ['mp4', 'avi', 'mov', 'wmv', 'flv'],
        'allowed_document_formats' => ['pdf', 'doc', 'docx', 'ppt', 'pptx'],
        'thumbnail_dimensions' => [
            'width' => 800,
            'height' => 450,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Course Levels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the different course levels available.
    |
    */

    'levels' => [
        'beginner' => [
            'name' => 'Débutant',
            'description' => 'Aucune expérience préalable requise',
            'color' => '#28a745',
        ],
        'intermediate' => [
            'name' => 'Intermédiaire',
            'description' => 'Connaissances de base requises',
            'color' => '#ffc107',
        ],
        'advanced' => [
            'name' => 'Avancé',
            'description' => 'Expérience significative requise',
            'color' => '#dc3545',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Course Types
    |--------------------------------------------------------------------------
    |
    | Here you may configure the different course types available.
    |
    */

    'types' => [
        'video' => [
            'name' => 'Vidéo',
            'description' => 'Cours basé sur des vidéos',
            'icon' => 'fas fa-play-circle',
        ],
        'text' => [
            'name' => 'Texte',
            'description' => 'Cours basé sur du contenu textuel',
            'icon' => 'fas fa-file-alt',
        ],
        'pdf' => [
            'name' => 'PDF',
            'description' => 'Cours basé sur des documents PDF',
            'icon' => 'fas fa-file-pdf',
        ],
        'quiz' => [
            'name' => 'Quiz',
            'description' => 'Cours basé sur des quiz',
            'icon' => 'fas fa-question-circle',
        ],
        'assignment' => [
            'name' => 'Devoir',
            'description' => 'Cours basé sur des devoirs',
            'icon' => 'fas fa-tasks',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Course Security
    |--------------------------------------------------------------------------
    |
    | Here you may configure security settings for courses.
    |
    */

    'security' => [
        'prevent_download' => true,
        'prevent_screenshot' => true,
        'watermark_videos' => true,
        'session_timeout' => 30, // minutes
        'max_concurrent_sessions' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Course Pricing
    |--------------------------------------------------------------------------
    |
    | Here you may configure pricing settings for courses.
    |
    */

    'pricing' => [
        'currency' => 'USD',
        'min_price' => 0,
        'max_price' => 10000,
        'instructor_commission' => 70, // percentage
        'platform_commission' => 30, // percentage
    ],
];
