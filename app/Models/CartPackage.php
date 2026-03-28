<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartPackage extends Model
{
    protected $fillable = [
        'user_id',
        'content_package_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contentPackage(): BelongsTo
    {
        return $this->belongsTo(ContentPackage::class, 'content_package_id');
    }
}
