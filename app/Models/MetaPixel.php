<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaPixel extends Model
{
    protected $fillable = [
        'name',
        'pixel_id',
        'is_active',
        'priority',
        'allowed_country_codes',
        'excluded_country_codes',
        'funnel_keys',
        'match_route_name',
        'match_path_pattern',
        'excluded_route_names',
        'excluded_path_patterns',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'priority' => 'integer',
            'allowed_country_codes' => 'array',
            'excluded_country_codes' => 'array',
            'funnel_keys' => 'array',
            'excluded_route_names' => 'array',
            'excluded_path_patterns' => 'array',
        ];
    }
}

