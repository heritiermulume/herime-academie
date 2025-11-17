<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonDiscussion extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'parent_id',
        'content',
        'likes_count',
        'is_pinned',
        'is_answered',
    ];

    protected function casts(): array
    {
        return [
            'likes_count' => 'integer',
            'is_pinned' => 'boolean',
            'is_answered' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(LessonDiscussion::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(LessonDiscussion::class, 'parent_id');
    }

    /**
     * Scope pour obtenir seulement les discussions principales (pas les réponses)
     */
    public function scopeMainThreads($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope pour obtenir les discussions épinglées
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Incrémenter le compteur de likes
     */
    public function incrementLikes(): void
    {
        $this->increment('likes_count');
    }

    /**
     * Décrémenter le compteur de likes
     */
    public function decrementLikes(): void
    {
        $this->decrement('likes_count');
    }
}
