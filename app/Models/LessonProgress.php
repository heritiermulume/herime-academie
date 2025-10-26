<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'is_completed',
        'time_watched',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'time_watched' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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

    /**
     * Marquer une leçon comme commencée
     */
    public function markAsStarted()
    {
        if (!$this->started_at) {
            $this->update(['started_at' => now()]);
        }
    }

    /**
     * Marquer une leçon comme terminée
     */
    public function markAsCompleted()
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now()
        ]);
    }

    /**
     * Mettre à jour le temps de visionnage
     */
    public function updateTimeWatched($seconds)
    {
        $this->update(['time_watched' => $seconds]);
    }

    /**
     * Obtenir le pourcentage de progression d'une leçon
     */
    public function getProgressPercentageAttribute()
    {
        if (!$this->lesson || $this->lesson->duration == 0) {
            return $this->is_completed ? 100 : 0;
        }

        $percentage = ($this->time_watched / ($this->lesson->duration * 60)) * 100;
        return min(100, max(0, round($percentage, 2)));
    }
}