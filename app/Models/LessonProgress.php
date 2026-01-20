<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    protected $fillable = [
        'user_id',
        'content_id',
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
        return $this->belongsTo(Course::class, 'content_id');
    }

    /**
     * Alias pour compatibilité avec le nouveau nom
     */
    public function content(): BelongsTo
    {
        return $this->course();
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
        // Si la leçon est complétée, retourner 100%
        if ($this->is_completed) {
            return 100;
        }

        // Si la relation lesson n'est pas chargée, essayer de la charger
        if (!$this->relationLoaded('lesson') && $this->lesson_id) {
            $this->load('lesson');
        }

        // Si la leçon n'existe pas ou n'a pas de durée, retourner 0% (sauf si complétée)
        if (!$this->lesson || !$this->lesson->duration || $this->lesson->duration == 0) {
            return 0;
        }

        // Calculer le pourcentage basé sur le temps visionné
        $totalSeconds = $this->lesson->duration * 60;
        if ($totalSeconds == 0) {
            return 0;
        }

        $percentage = ($this->time_watched / $totalSeconds) * 100;
        return min(100, max(0, round($percentage, 2)));
    }

    /**
     * Vérifier si la leçon a été commencée
     */
    public function getIsStartedAttribute()
    {
        return !is_null($this->started_at);
    }

    /**
     * Alias pour time_watched (compatibilité)
     */
    public function getWatchedSecondsAttribute()
    {
        return $this->time_watched;
    }
}