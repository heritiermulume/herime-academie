<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaEventTrigger extends Model
{
    protected $fillable = [
        'meta_event_id',
        'trigger_type',
        'priority',
        'match_route_name',
        'match_path_pattern',
        'css_selector',
        'country_codes',
        'funnel_keys',
        'pixel_ids',
        'payload',
        'is_active',
        'once_per_page',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'country_codes' => 'array',
            'funnel_keys' => 'array',
            'pixel_ids' => 'array',
            'payload' => 'array',
            'is_active' => 'boolean',
            'once_per_page' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(MetaEvent::class, 'meta_event_id');
    }
}

