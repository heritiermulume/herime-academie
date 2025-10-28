<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MediaVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_file_id',
        'variant_type',
        'format',
        'storage_path',
        'size',
        'width',
        'height',
        'bitrate',
        'codec',
        'metadata',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'bitrate' => 'integer',
    ];

    /**
     * Relations
     */
    public function mediaFile()
    {
        return $this->belongsTo(MediaFile::class);
    }

    /**
     * Scopes
     */
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeThumbnails($query)
    {
        return $query->where('variant_type', 'thumbnail');
    }

    public function scopeResolution($query, string $resolution)
    {
        return $query->where('variant_type', $resolution);
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

    public function getResolutionLabel(): ?string
    {
        if (preg_match('/(\d+)p/', $this->variant_type, $matches)) {
            return $matches[1] . 'p';
        }
        
        return $this->variant_type;
    }

    /**
     * Méthodes utilitaires
     */
    public function getUrl(): string
    {
        // Si chemin absolu (http/https), retourner tel quel
        if (Str::startsWith($this->storage_path, ['http://', 'https://'])) {
            return $this->storage_path;
        }
        
        // Sinon, construire l'URL avec asset()
        return asset($this->storage_path);
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function markAsReady(): void
    {
        $this->update(['status' => 'ready']);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
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
}

