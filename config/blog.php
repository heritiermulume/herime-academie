<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blog Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure various settings for the blog.
    |
    */

    'settings' => [
        'enabled' => env('BLOG_ENABLED', true),
        'posts_per_page' => 12,
        'featured_posts_count' => 3,
        'recent_posts_count' => 5,
        'max_excerpt_length' => 200,
        'allow_comments' => true,
        'require_approval' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Post Types
    |--------------------------------------------------------------------------
    |
    | Here you may configure the different post types available.
    |
    */

    'post_types' => [
        'article' => [
            'name' => 'Article',
            'description' => 'Article de blog standard',
            'icon' => 'fas fa-newspaper',
        ],
        
        'tutorial' => [
            'name' => 'Tutoriel',
            'description' => 'Tutoriel étape par étape',
            'icon' => 'fas fa-graduation-cap',
        ],
        
        'news' => [
            'name' => 'Actualité',
            'description' => 'Actualité ou annonce',
            'icon' => 'fas fa-bullhorn',
        ],
        
        'review' => [
            'name' => 'Avis',
            'description' => 'Avis ou critique',
            'icon' => 'fas fa-star',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Post Statuses
    |--------------------------------------------------------------------------
    |
    | Here you may configure the different post statuses.
    |
    */

    'statuses' => [
        'draft' => [
            'name' => 'Brouillon',
            'description' => 'Post en cours de rédaction',
            'color' => '#6c757d',
        ],
        
        'pending' => [
            'name' => 'En attente',
            'description' => 'Post en attente d\'approbation',
            'color' => '#ffc107',
        ],
        
        'published' => [
            'name' => 'Publié',
            'description' => 'Post publié et visible',
            'color' => '#28a745',
        ],
        
        'archived' => [
            'name' => 'Archivé',
            'description' => 'Post archivé',
            'color' => '#17a2b8',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure SEO settings for the blog.
    |
    */

    'seo' => [
        'meta_title_suffix' => ' - Blog Herime Academie',
        'meta_description_length' => 160,
        'meta_keywords_length' => 255,
        'auto_generate_slugs' => true,
        'slug_separator' => '-',
        'max_slug_length' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Sharing
    |--------------------------------------------------------------------------
    |
    | Here you may configure social sharing settings.
    |
    */

    'social_sharing' => [
        'enabled' => true,
        'platforms' => [
            'facebook' => [
                'enabled' => true,
                'app_id' => env('FACEBOOK_APP_ID'),
            ],
            'twitter' => [
                'enabled' => true,
                'username' => env('TWITTER_USERNAME'),
            ],
            'linkedin' => [
                'enabled' => true,
            ],
            'whatsapp' => [
                'enabled' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Comment Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure comment settings for the blog.
    |
    */

    'comments' => [
        'enabled' => true,
        'require_approval' => false,
        'allow_guest_comments' => false,
        'max_length' => 1000,
        'spam_protection' => true,
        'rate_limiting' => [
            'max_attempts' => 5,
            'decay_minutes' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure file upload settings for the blog.
    |
    */

    'uploads' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_image_formats' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_document_formats' => ['pdf', 'doc', 'docx'],
        'image_quality' => 85,
        'thumbnail_sizes' => [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600],
        ],
    ],
];
