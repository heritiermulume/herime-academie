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
        'youtube_video_id',
        'is_unlisted',
        'youtube_embed_url',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_preview' => 'boolean',
            'is_unlisted' => 'boolean',
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

    /**
     * Vérifier si la leçon utilise YouTube
     */
    public function isYoutubeVideo(): bool
    {
        return !empty($this->youtube_video_id);
    }

    /**
     * Obtenir l'URL d'embed YouTube sécurisée
     */
    public function getSecureYouTubeEmbedUrl(): ?string
    {
        if (!$this->isYoutubeVideo()) {
            return null;
        }

        $videoId = $this->youtube_video_id;
        $params = [
            'rel' => 0, // Ne pas afficher de vidéos suggérées
            'modestbranding' => 1, // Masquer le logo YouTube
            'iv_load_policy' => 3, // Masquer les annotations vidéo
            'origin' => config('video.youtube.embed_domain', request()->getHost()),
        ];

        return "https://www.youtube.com/embed/{$videoId}?" . http_build_query($params);
    }

    /**
     * Obtenir l'URL YouTube complète
     */
    public function getYouTubeWatchUrl(): ?string
    {
        if (!$this->isYoutubeVideo()) {
            return null;
        }

        return "https://www.youtube.com/watch?v={$this->youtube_video_id}";
    }
}
