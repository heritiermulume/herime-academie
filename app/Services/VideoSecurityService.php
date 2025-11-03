<?php

namespace App\Services;

use App\Models\VideoAccessToken;
use App\Models\CourseLesson;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VideoSecurityService
{
    /**
     * Générer un token d'accès sécurisé pour une leçon
     */
    public function generateAccessToken(
        User $user,
        CourseLesson $lesson,
        string $ipAddress,
        ?string $userAgent = null
    ): VideoAccessToken {
        // Vérifier la limite de streams concurrents
        $this->checkConcurrentStreams($user, $lesson);
        
        // Vérifier si l'IP est blacklistée
        $this->checkBlacklistedIp($ipAddress);
        
        // Créer le token
        $token = VideoAccessToken::createForUser(
            $user->id,
            $lesson->id,
            $ipAddress,
            $userAgent,
            $this->getTokenValidityHours()
        );
        
        // Enregistrer l'accès
        $this->logAccess($user, $lesson, $ipAddress, $userAgent);
        
        return $token;
    }

    /**
     * Vérifier la validité d'un token
     */
    public function validateToken(string $token, string $ipAddress): ?VideoAccessToken
    {
        $accessToken = VideoAccessToken::where('token', $token)->first();
        
        if (!$accessToken) {
            Log::warning('VideoSecurityService: Invalid token', ['token' => substr($token, 0, 8)]);
            return null;
        }
        
        // Vérifier si le token est valide
        if (!$accessToken->isValid()) {
            Log::warning('VideoSecurityService: Expired or revoked token', ['token_id' => $accessToken->id]);
            return null;
        }
        
        // Vérifier l'adresse IP (optionnel, peut être assoupli selon les besoins)
        $strictIpCheck = config('video.strict_ip_check', false);
        if ($strictIpCheck && $accessToken->ip_address !== $ipAddress) {
            Log::warning('VideoSecurityService: IP mismatch', [
                'token_id' => $accessToken->id,
                'expected_ip' => $accessToken->ip_address,
                'actual_ip' => $ipAddress
            ]);
            return null;
        }
        
        // Vérifier les streams concurrents
        if (!$accessToken->canAddConcurrentStream()) {
            Log::warning('VideoSecurityService: Max concurrent streams reached', [
                'user_id' => $accessToken->user_id,
                'lesson_id' => $accessToken->lesson_id
            ]);
            return null;
        }
        
        return $accessToken;
    }

    /**
     * Révocquer un token (en cas de fuite détectée)
     */
    public function revokeToken(VideoAccessToken $token): void
    {
        $token->update(['is_revoked' => true]);
        
        Log::info('VideoSecurityService: Token revoked', [
            'token_id' => $token->id,
            'user_id' => $token->user_id,
            'lesson_id' => $token->lesson_id
        ]);
    }

    /**
     * Révocquer tous les tokens d'un utilisateur pour une leçon
     */
    public function revokeUserTokensForLesson(User $user, CourseLesson $lesson): int
    {
        $revoked = VideoAccessToken::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->where('is_revoked', false)
            ->update(['is_revoked' => true]);
        
        Log::info('VideoSecurityService: User tokens revoked for lesson', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'revoked_count' => $revoked
        ]);
        
        return $revoked;
    }

    /**
     * Blacklister une IP
     */
    public function blacklistIp(string $ipAddress, int $hours = 24): void
    {
        $key = "video_security:blacklist:{$ipAddress}";
        Cache::put($key, true, now()->addHours($hours));
        
        Log::warning('VideoSecurityService: IP blacklisted', [
            'ip' => $ipAddress,
            'hours' => $hours
        ]);
    }

    /**
     * Vérifier si une IP est blacklistée
     */
    private function checkBlacklistedIp(string $ipAddress): void
    {
        $key = "video_security:blacklist:{$ipAddress}";
        
        if (Cache::has($key)) {
            Log::warning('VideoSecurityService: Blocked blacklisted IP', ['ip' => $ipAddress]);
            abort(403, 'Accès refusé pour cette adresse IP');
        }
    }

    /**
     * Vérifier et limiter les streams concurrents
     */
    private function checkConcurrentStreams(User $user, CourseLesson $lesson): void
    {
        $maxStreams = config('video.max_concurrent_streams', 3);
        
        $activeStreams = VideoAccessToken::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->where('is_revoked', false)
            ->where('expires_at', '>', now())
            ->count();
        
        if ($activeStreams >= $maxStreams) {
            Log::warning('VideoSecurityService: Max concurrent streams exceeded', [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'active_streams' => $activeStreams,
                'max_streams' => $maxStreams
            ]);
            
            abort(429, 'Nombre maximum de streams simultanés atteint');
        }
    }

    /**
     * Enregistrer un accès pour surveillance
     */
    private function logAccess(User $user, CourseLesson $lesson, string $ipAddress, ?string $userAgent): void
    {
        $key = "video_security:access:{$user->id}:{$lesson->id}:{$ipAddress}";
        
        // Compter les accès récents (dernières 24h)
        $recentAccesses = Cache::get($key, 0);
        $recentAccesses++;
        Cache::put($key, $recentAccesses, now()->addHours(24));
        
        // Si trop d'accès suspect, blacklister
        if ($recentAccesses > config('video.suspicious_access_threshold', 100)) {
            $this->blacklistIp($ipAddress);
        }
        
        Log::info('VideoSecurityService: Video access logged', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'ip' => $ipAddress,
            'recent_accesses' => $recentAccesses
        ]);
    }

    /**
     * Obtenir la durée de validité des tokens en heures
     */
    private function getTokenValidityHours(): int
    {
        return config('video.token_validity_hours', 24);
    }

    /**
     * Surveiller les activités suspectes et révoquer si nécessaire
     */
    public function monitorSuspiciousActivity(): void
    {
        $suspiciousPatterns = VideoAccessToken::selectRaw('
                user_id,
                lesson_id,
                ip_address,
                COUNT(*) as access_count
            ')
            ->where('created_at', '>=', now()->subHours(1))
            ->groupBy('user_id', 'lesson_id', 'ip_address')
            ->having('access_count', '>', config('video.suspicious_access_threshold', 100))
            ->get();
        
        foreach ($suspiciousPatterns as $pattern) {
            Log::warning('VideoSecurityService: Suspicious activity detected', [
                'user_id' => $pattern->user_id,
                'lesson_id' => $pattern->lesson_id,
                'ip' => $pattern->ip_address,
                'access_count' => $pattern->access_count
            ]);
            
            // Révocquer les tokens et blacklister l'IP
            VideoAccessToken::where('user_id', $pattern->user_id)
                ->where('lesson_id', $pattern->lesson_id)
                ->where('ip_address', $pattern->ip_address)
                ->update(['is_revoked' => true]);
            
            $this->blacklistIp($pattern->ip_address, 48);
        }
    }
}

