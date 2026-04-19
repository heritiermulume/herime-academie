<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    public const TYPE_HOME_MODAL = 'home_modal';

    protected $fillable = [
        'title',
        'content',
        'image',
        'button_text',
        'button_url',
        'type',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Annonces affichées dans la bannière globale (hors modale d’accueil).
     */
    public function scopeForGlobalBanner($query)
    {
        return $query->where('type', '!=', self::TYPE_HOME_MODAL);
    }
}
