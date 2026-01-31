<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaEvent extends Model
{
    protected $fillable = [
        'event_name',
        'is_standard',
        'is_active',
        'default_payload',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_standard' => 'boolean',
            'is_active' => 'boolean',
            'default_payload' => 'array',
        ];
    }

    public function triggers(): HasMany
    {
        return $this->hasMany(MetaEventTrigger::class, 'meta_event_id');
    }
}

