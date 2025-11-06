<?php

namespace App\Http\Controllers;

use App\Models\CourseLesson;
use App\Models\VideoAccessToken;
use App\Services\VideoSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YouTubeAccessController extends Controller
{
    protected VideoSecurityService $securityService;

    public function __construct(VideoSecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Générer un token d'accès pour une leçon YouTube
     */
    public function generateAccessToken(Request $request, CourseLesson $lesson)
    {
        // Vérifier que l'utilisateur est authentifié
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise'
            ], 401);
        }

        $user = auth()->user();

        // Vérifier que la leçon appartient au cours de l'utilisateur
        if (!$lesson->course->isEnrolledBy($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à cette leçon'
            ], 403);
        }

        // Vérifier que c'est une leçon YouTube
        if (!$lesson->isYoutubeVideo()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette leçon n\'utilise pas YouTube'
            ], 400);
        }

        try {
            // Générer le token d'accès
            $accessToken = $this->securityService->generateAccessToken(
                $user,
                $lesson,
                $request->ip(),
                $request->userAgent()
            );

            // Retourner l'URL d'embed sécurisée et le token
            return response()->json([
                'success' => true,
                'embed_url' => $lesson->getSecureYouTubeEmbedUrl(),
                'token' => $accessToken->token,
                'expires_at' => $accessToken->expires_at->toIso8601String(),
                'user_info' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('YouTubeAccessController: Error generating access token', [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du token d\'accès'
            ], 500);
        }
    }

    /**
     * Vérifier la validité d'un token
     */
    public function validateToken(Request $request)
    {
        $token = $request->input('token');
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token manquant'
            ], 400);
        }

        $accessToken = $this->securityService->validateToken($token, $request->ip());

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalide ou expiré'
            ], 401);
        }

        // Charger la leçon
        $lesson = $accessToken->lesson;
        
        return response()->json([
            'success' => true,
            'valid' => true,
            'embed_url' => $lesson->getSecureYouTubeEmbedUrl(),
            'lesson_id' => $lesson->id,
            'expires_at' => $accessToken->expires_at->toIso8601String()
        ]);
    }

    /**
     * Révocquer un token (pour l'admin en cas de fuite)
     */
    public function revokeToken(Request $request, VideoAccessToken $token)
    {
        // Vérifier les permissions admin (admin ou super_user)
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé. Vous devez être administrateur ou super utilisateur.'
            ], 403);
        }

        $this->securityService->revokeToken($token);

        return response()->json([
            'success' => true,
            'message' => 'Token révoqué avec succès'
        ]);
    }

    /**
     * Nettoyer les tokens expirés (à appeler via cron)
     */
    public function cleanupExpiredTokens()
    {
        $cleaned = VideoAccessToken::cleanupExpired();
        
        Log::info('YouTubeAccessController: Expired tokens cleaned', [
            'cleaned_count' => $cleaned
        ]);

        return response()->json([
            'success' => true,
            'cleaned' => $cleaned
        ]);
    }
}
