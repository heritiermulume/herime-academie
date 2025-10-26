<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseLesson extends Model
{
    protected $fillable = [
        'course_id',
        'section_id',
        'title',
        'description',
        'type',
        'content_url',
        'content_text',
        'file_path',
        'duration',
        'sort_order',
        'is_published',
        'is_preview',
        'quiz_data',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_preview' => 'boolean',
            'quiz_data' => 'array',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class, 'lesson_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopePreview($query)
    {
        return $query->where('is_preview', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
