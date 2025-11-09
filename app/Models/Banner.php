<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'mobile_image',
        'button1_text',
        'button1_url',
        'button1_style',
        'button1_target',
        'button2_text',
        'button2_url',
        'button2_style',
        'button2_target',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return '';
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        $service = app(\App\Services\FileUploadService::class);
        return $service->getUrl($this->image, 'banners');
    }

    public function getMobileImageUrlAttribute(): string
    {
        if (!$this->mobile_image) {
            return '';
        }

        if (filter_var($this->mobile_image, FILTER_VALIDATE_URL)) {
            return $this->mobile_image;
        }

        $service = app(\App\Services\FileUploadService::class);
        return $service->getUrl($this->mobile_image, 'banners');
    }
}
