<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class VideoAccessToken extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_id',
        'token',
        'ip_address',
        'user_agent',
        'expires_at',
        'is_revoked',
        'concurrent_streams',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_revoked' => 'boolean',
        ];
    }

    /**
     * Relation vers l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation vers la leçon
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    /**
     * Vérifier si le token est valide
     */
    public function isValid(): bool
    {
        return !$this->is_revoked && $this->expires_at->isFuture();
    }

    /**
     * Créer un nouveau token d'accès
     */
    public static function createForUser(
        int $userId,
        int $lessonId,
        string $ipAddress,
        ?string $userAgent = null,
        int $validityHours = 24
    ): self {
        return self::create([
            'user_id' => $userId,
            'lesson_id' => $lessonId,
            'token' => bin2hex(random_bytes(32)),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'expires_at' => now()->addHours($validityHours),
            'is_revoked' => false,
            'concurrent_streams' => 1,
        ]);
    }

    /**
     * Nettoyer les tokens expirés
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', now())
            ->orWhere('is_revoked', true)
            ->delete();
    }

    /**
     * Vérifier et limiter les streams concurrents
     */
    public function canAddConcurrentStream(): bool
    {
        $maxStreams = config('video.max_concurrent_streams', 3);
        
        // Compter les tokens actifs pour cet utilisateur et cette leçon
        $activeStreams = self::where('user_id', $this->user_id)
            ->where('lesson_id', $this->lesson_id)
            ->where('is_revoked', false)
            ->where('expires_at', '>', now())
            ->count();
        
        return $activeStreams < $maxStreams;
    }
}
