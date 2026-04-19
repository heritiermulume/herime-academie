<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CourseLesson extends Model
{
    protected $table = 'content_lessons';

    protected $fillable = [
        'content_id',
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

    protected $appends = ['content_file_url', 'file_url'];

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
        return $this->belongsTo(Course::class, 'content_id');
    }

    /**
     * Alias pour compatibilité avec le nouveau nom
     */
    public function content(): BelongsTo
    {
        return $this->course();
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class, 'lesson_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LessonNote::class, 'lesson_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LessonResource::class, 'lesson_id');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(LessonDiscussion::class, 'lesson_id');
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
        return ! empty($this->youtube_video_id);
    }

    /**
     * Obtenir l'URL d'embed YouTube sécurisée
     */
    public function getSecureYouTubeEmbedUrl(): ?string
    {
        if (! $this->isYoutubeVideo()) {
            return null;
        }

        $videoId = $this->youtube_video_id;
        $params = [
            'rel' => 0, // Ne pas afficher de vidéos suggérées
            'modestbranding' => 1, // Masquer le logo YouTube
            'iv_load_policy' => 3, // Masquer les annotations vidéo
            'origin' => config('video.youtube.embed_domain', request()->getHost()),
        ];

        return "https://www.youtube.com/embed/{$videoId}?".http_build_query($params);
    }

    /**
     * Obtenir l'URL YouTube complète
     */
    public function getYouTubeWatchUrl(): ?string
    {
        if (! $this->isYoutubeVideo()) {
            return null;
        }

        return "https://www.youtube.com/watch?v={$this->youtube_video_id}";
    }

    public function getFileUrlAttribute(): string
    {
        if (! $this->file_path) {
            return '';
        }

        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return $this->file_path;
        }

        $service = app(\App\Services\FileUploadService::class);

        return $service->getUrl($this->file_path, 'courses/lessons');
    }

    public function getContentFileUrlAttribute(): string
    {
        if (! $this->content_url) {
            return '';
        }

        if (filter_var($this->content_url, FILTER_VALIDATE_URL)) {
            return $this->content_url;
        }

        $service = app(\App\Services\FileUploadService::class);

        return $service->getUrl($this->content_url, 'courses/lessons');
    }

    /**
     * Chemin relatif disque (storage/app/private) de la vidéo hébergée, hors URL externe / YouTube.
     */
    public function getInternalStorageVideoPath(): ?string
    {
        if (! empty($this->youtube_video_id)) {
            return null;
        }

        foreach ([$this->file_path, $this->content_url] as $p) {
            if (empty($p) || filter_var($p, FILTER_VALIDATE_URL)) {
                continue;
            }
            $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));

            if (in_array($ext, ['mp4', 'm4v', 'mov', 'webm', 'mkv'], true)) {
                return $p;
            }
        }

        return null;
    }

    /**
     * Chemin relatif sur le disque local (storage/app/private) pour un fichier de leçon
     * hébergé sur le serveur (hors URL http(s)), pour ZIP ou téléchargement espace d'apprentissage.
     */
    public function getStoredLessonFileRelativePath(): ?string
    {
        $disk = Storage::disk('local');

        foreach (['file_path', 'content_url'] as $attr) {
            $value = $this->getRawOriginal($attr) ?? $this->getAttribute($attr);
            if (empty($value) || filter_var($value, FILTER_VALIDATE_URL)) {
                continue;
            }
            $clean = ltrim((string) $value, '/');
            if ($disk->exists($clean)) {
                return $clean;
            }
            if ($disk->exists((string) $value)) {
                return ltrim((string) $value, '/');
            }
        }

        return null;
    }

    public function hasHlsStreamReady(): bool
    {
        return $this->hls_status === 'ready'
            && ! empty($this->hls_manifest_path);
    }

    /**
     * URL signée via FileController pour le master.m3u8 (HLS).
     */
    public function getHlsManifestUrlAttribute(): string
    {
        if (! $this->hasHlsStreamReady()) {
            return '';
        }

        $p = ltrim((string) $this->hls_manifest_path, '/');

        return route('files.serve', ['type' => 'lessons', 'path' => $p]);
    }

    /**
     * Détection alignée sur le lecteur Plyr (YouTube via champ ou URL dans content_url).
     */
    public function isYoutubePlyrSource(): bool
    {
        $videoId = trim((string) ($this->youtube_video_id ?? ''));
        $isYoutube = $videoId !== '';

        if (! $isYoutube && ! empty($this->content_url)) {
            $contentUrl = (string) $this->content_url;
            if (str_contains($contentUrl, 'youtube.com') || str_contains($contentUrl, 'youtu.be')) {
                if (str_contains($contentUrl, 'youtube.com/watch')) {
                    parse_str((string) parse_url($contentUrl, PHP_URL_QUERY), $query);
                    $videoId = trim((string) ($query['v'] ?? ''));
                } elseif (str_contains($contentUrl, 'youtu.be/')) {
                    $videoId = trim((string) basename((string) parse_url($contentUrl, PHP_URL_PATH)));
                } elseif (str_contains($contentUrl, 'youtube.com/embed/')) {
                    $videoId = trim((string) basename((string) parse_url($contentUrl, PHP_URL_PATH)));
                }
                $isYoutube = $videoId !== '';
            }
        }

        return $isYoutube;
    }

    /**
     * URL de lecture HTML5 (fichier hébergé), hors YouTube / Vimeo URL seule.
     * Même logique que {@see resources/views/components/plyr-player.blade.php}.
     */
    public function resolveInternalPlyrVideoUrl(): ?string
    {
        if ($this->isYoutubePlyrSource()) {
            return null;
        }

        $internalVideoUrl = null;
        $filePath = $this->getRawOriginal('file_path') ?? $this->file_path ?? null;
        $contentUrlRaw = $this->getRawOriginal('content_url') ?? $this->content_url ?? null;

        if (! empty($filePath) && trim((string) $filePath) !== '') {
            try {
                $fileUrl = $this->file_url;
                if (! empty($fileUrl) && trim($fileUrl) !== '') {
                    $internalVideoUrl = $fileUrl;
                } elseif (! filter_var($filePath, FILTER_VALIDATE_URL)) {
                    $internalVideoUrl = route('files.serve', ['type' => 'lessons', 'path' => ltrim((string) $filePath, '/')]);
                } else {
                    $internalVideoUrl = (string) $filePath;
                }
            } catch (\Throwable) {
                if (! filter_var($filePath, FILTER_VALIDATE_URL)) {
                    $internalVideoUrl = route('files.serve', ['type' => 'lessons', 'path' => ltrim((string) $filePath, '/')]);
                } else {
                    $internalVideoUrl = (string) $filePath;
                }
            }
        } elseif (! empty($contentUrlRaw) && trim((string) $contentUrlRaw) !== '') {
            $isExternalUrl = filter_var($contentUrlRaw, FILTER_VALIDATE_URL)
                && str_contains((string) $contentUrlRaw, 'vimeo.com');

            if (! $isExternalUrl) {
                try {
                    $contentFileUrl = $this->content_file_url;
                    if (! empty($contentFileUrl) && trim($contentFileUrl) !== '') {
                        $internalVideoUrl = $contentFileUrl;
                    } elseif (! filter_var($contentUrlRaw, FILTER_VALIDATE_URL)) {
                        $internalVideoUrl = route('files.serve', ['type' => 'lessons', 'path' => ltrim((string) $contentUrlRaw, '/')]);
                    } else {
                        $internalVideoUrl = (string) $contentUrlRaw;
                    }
                } catch (\Throwable) {
                    if (! filter_var($contentUrlRaw, FILTER_VALIDATE_URL)) {
                        $internalVideoUrl = route('files.serve', ['type' => 'lessons', 'path' => ltrim((string) $contentUrlRaw, '/')]);
                    } else {
                        $internalVideoUrl = (string) $contentUrlRaw;
                    }
                }
            }
        }

        if ($internalVideoUrl === null || trim($internalVideoUrl) === '') {
            return null;
        }

        return $internalVideoUrl;
    }

    /**
     * Coque du lecteur sur la page apprentissage : ratio piloté par la vidéo (MP4/HLS), pas l’iframe YouTube 16:9.
     */
    public function usesAdaptivePlayerShell(): bool
    {
        if (($this->type ?? '') !== 'video') {
            return false;
        }

        return $this->resolveInternalPlyrVideoUrl() !== null;
    }
}
