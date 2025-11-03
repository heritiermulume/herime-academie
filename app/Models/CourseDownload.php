<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseDownload extends Model
{
    protected $fillable = [
        'course_id',
        'user_id',
        'ip_address',
        'user_agent',
        'country',
        'country_name',
        'city',
        'region',
        'download_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec le cours
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec la catégorie (via le cours)
     */
    public function category()
    {
        return $this->hasOneThrough(Category::class, Course::class, 'id', 'id', 'course_id', 'category_id');
    }
}
