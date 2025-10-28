<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MediaFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'file_id',
        'filename',
        'mime_type',
        'media_type',
        'size',
        'storage_bucket',
        'storage_path',
        'storage_driver',
        'checksum_md5',
        'checksum_sha256',
        'metadata',
        'user_id',
        'entity_type',
        'entity_id',
        'status',
        'processing_error',
        'uploaded_at',
        'processed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
        'uploaded_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->file_id)) {
                $model->file_id = self::generateFileId();
            }
            if (empty($model->uploaded_at)) {
                $model->uploaded_at = now();
            }
        });
    }

    /**
     * Génère un ID unique pour le fichier
     */
    public static function generateFileId(): string
    {
        return 'mf_' . Str::random(24);
    }

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function variants()
    {
        return $this->hasMany(MediaVariant::class);
    }

    /**
     * Relation polymorphe avec l'entité propriétaire
     */
    public function entity()
    {
        return $this->morphTo('entity');
    }

    /**
     * Scopes
     */
    public function scopeImages($query)
    {
        return $query->where('media_type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('media_type', 'video');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeForEntity($query, $entityType, $entityId)
    {
        return $query->where('entity_type', $entityType)
                     ->where('entity_id', $entityId);
    }

    /**
     * Accesseurs
     */
    public function getUrlAttribute(): string
    {
        return $this->getUrl();
    }

    public function getSizeHumanAttribute(): string
    {
        return $this->formatBytes($this->size);
    }

    /**
     * Méthodes utilitaires
     */
    public function getUrl(string $variant = null): string
    {
        if ($variant) {
            $variantModel = $this->variants()->where('variant_type', $variant)->first();
            if ($variantModel) {
                return $this->buildUrl($variantModel->storage_path);
            }
        }
        
        return $this->buildUrl($this->storage_path);
    }

    protected function buildUrl(string $path): string
    {
        // Si chemin absolu (http/https), retourner tel quel
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }
        
        // Sinon, construire l'URL avec asset()
        return asset($path);
    }

    public function isImage(): bool
    {
        return $this->media_type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function markAsReady(): void
    {
        $this->update([
            'status' => 'ready',
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'processing_error' => $error,
        ]);
    }

    /**
     * Obtenir les métadonnées spécifiques
     */
    public function getMeta(string $key, $default = null)
    {
        return data_get($this->metadata, $key, $default);
    }

    public function setMeta(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        data_set($metadata, $key, $value);
        $this->metadata = $metadata;
        $this->save();
    }

    /**
     * Pour les vidéos: obtenir le manifeste HLS
     */
    public function getHlsManifestUrl(): ?string
    {
        if (!$this->isVideo()) {
            return null;
        }
        
        $manifestPath = $this->getMeta('hls.manifest_path');
        if (!$manifestPath) {
            return null;
        }
        
        return $this->buildUrl($manifestPath);
    }

    /**
     * Obtenir la miniature
     */
    public function getThumbnailUrl(): ?string
    {
        $thumbnail = $this->variants()->where('variant_type', 'thumbnail')->first();
        return $thumbnail ? $this->buildUrl($thumbnail->storage_path) : null;
    }

    /**
     * Formater la taille en bytes
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Obtenir toutes les résolutions disponibles pour une vidéo
     */
    public function getAvailableResolutions(): array
    {
        if (!$this->isVideo()) {
            return [];
        }
        
        return $this->variants()
            ->where('variant_type', 'LIKE', '%p')
            ->where('status', 'ready')
            ->pluck('variant_type')
            ->map(fn($v) => (int) str_replace('p', '', $v))
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Obtenir la durée de la vidéo (en secondes)
     */
    public function getDuration(): ?float
    {
        if (!$this->isVideo()) {
            return null;
        }
        
        return $this->getMeta('video.duration');
    }

    /**
     * Obtenir la durée formatée (HH:MM:SS)
     */
    public function getDurationFormatted(): ?string
    {
        $duration = $this->getDuration();
        if (!$duration) {
            return null;
        }
        
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;
        
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}

