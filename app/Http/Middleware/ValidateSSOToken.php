<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Services\SSOService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour valider le token SSO avant les actions importantes
 * 
 * Ce middleware est optionnel et peut être appliqué aux routes qui nécessitent
 * une validation SSO avant l'exécution (POST, PUT, PATCH, DELETE)
 */
class ValidateSSOToken
{
    protected $ssoService;

    public function __construct(SSOService $ssoService)
    {
        $this->ssoService = $ssoService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Ne valider que si SSO est activé
            if (!config('services.sso.enabled', true)) {
                return $next($request);
            }

            // Si la validation stricte est désactivée, laisser passer
            if (config('services.sso.skip_strict_validation', false)) {
                return $next($request);
            }

            // Ne valider que pour les méthodes qui modifient les données
            $methodsRequiringValidation = ['POST', 'PUT', 'PATCH', 'DELETE'];
            
            if (!in_array($request->method(), $methodsRequiringValidation)) {
                return $next($request);
            }

            // Si l'utilisateur n'est pas authentifié, laisser passer (auth middleware gère ça)
            if (!Auth::check()) {
                return $next($request);
            }

            // Récupérer le token SSO depuis la session ou les préférences utilisateur
            $user = Auth::user();
            
            if (!$user) {
                return $next($request);
            }
            
            // Utiliser request()->session() pour éviter les problèmes de session
            $ssoToken = null;
            try {
                if ($request->hasSession() && $request->session()->has('sso_token')) {
                    $ssoToken = $request->session()->get('sso_token');
                }
            } catch (\Throwable $e) {
                Log::debug('SSO token retrieval failed in middleware', [
                    'error' => $e->getMessage(),
                    'type' => get_class($e),
                ]);
                // En cas d'erreur, continuer sans token
                $ssoToken = null;
            }

            // Si pas de token SSO, vérifier si l'utilisateur est connecté localement
            // Si oui, laisser passer (connexion locale valide)
            if (empty($ssoToken)) {
                // Si l'utilisateur est authentifié localement, c'est OK
                if (Auth::check()) {
                    Log::debug('No SSO token but user is locally authenticated - allowing request', [
                        'user_id' => $user->id ?? null,
                        'method' => $request->method(),
                        'route' => $request->route()?->getName(),
                    ]);
                    return $next($request);
                }
                // Sinon, déconnecter car pas de token et pas d'auth locale
                Log::warning('No SSO token and user not locally authenticated - disconnecting', [
                    'user_id' => $user->id ?? null,
                    'method' => $request->method(),
                    'route' => $request->route()?->getName(),
                ]);
                return $this->handleInvalidToken($request);
            }

            // TOUJOURS vérifier d'abord la validation locale (JWT)
            // Si le token est valide localement (pas expiré, format correct), on fait confiance
            // même si l'API SSO échoue temporairement
            $localValidation = null;
            try {
                $localValidation = $this->ssoService->validateToken($ssoToken);
                if ($localValidation !== null) {
                    // Le token est valide localement - on fait confiance
                    // Même si l'API SSO échoue, on autorise la requête
                    Log::debug('SSO token valid locally - allowing request (trusting local JWT validation)', [
                        'user_id' => $user->id ?? null,
                        'method' => $request->method(),
                        'route' => $request->route()?->getName(),
                        'user_email' => $localValidation['email'] ?? 'unknown',
                    ]);
                    return $next($request);
                }
            } catch (\Throwable $localException) {
                Log::debug('SSO local validation exception', [
                    'error' => $localException->getMessage(),
                    'user_id' => $user->id ?? null,
                ]);
            }

            // Si la validation locale échoue, essayer l'API SSO
            // On est tolérant aux erreurs réseau mais on vérifie quand même
            $apiValidation = null;
            $apiError = null;
            try {
                $apiValidation = $this->ssoService->checkToken($ssoToken);
                if ($apiValidation) {
                    // L'API confirme que le token est valide
                    Log::debug('SSO token valid via API - allowing request', [
                        'user_id' => $user->id ?? null,
                        'method' => $request->method(),
                        'route' => $request->route()?->getName(),
                    ]);
                    return $next($request);
                } else {
                    // L'API a explicitement dit que le token est invalide
                    Log::warning('SSO API confirms token is invalid', [
                        'user_id' => $user->id ?? null,
                        'method' => $request->method(),
                        'route' => $request->route()?->getName(),
                    ]);
                    // On déconnecte car l'API confirme que le token est invalide
                    return $this->handleInvalidToken($request);
                }
            } catch (\Throwable $e) {
                // En cas d'erreur API (timeout, réseau, etc.), on est tolérant
                // Si la validation locale a échoué mais l'API est indisponible,
                // on considère que c'est un problème d'API et on laisse passer
                $apiError = $e->getMessage();
                Log::warning('SSO API validation error - but allowing request (API may be temporarily unavailable)', [
                    'user_id' => $user->id ?? null,
                    'error' => $apiError,
                    'method' => $request->method(),
                    'route' => $request->route()?->getName(),
                ]);
                // On laisse passer car l'erreur API pourrait être temporaire
                return $next($request);
            }
        } catch (\Throwable $e) {
            // En cas d'erreur dans le middleware, logger et laisser passer
            // On ne déconnecte JAMAIS l'utilisateur en cas d'erreur
            Log::debug('SSO validation middleware error - allowing request anyway', [
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id(),
                'method' => $request->method(),
                'route' => $request->route()?->getName(),
            ]);
            return $next($request);
        }
    }

    /**
     * Récupérer le token SSO depuis l'utilisateur
     * 
     * @param \App\Models\User $user
     * @return string|null
     */
    protected function getSSOTokenFromUser($user): ?string
    {
        // Le token SSO n'est généralement pas stocké localement pour des raisons de sécurité
        // On peut le récupérer depuis la session si nécessaire
        // Pour l'instant, on retourne null car le token n'est pas stocké localement
        // Cette méthode peut être étendue si nécessaire
        
        try {
            // Utiliser request()->session() pour éviter les problèmes de session non démarrée
            $request = request();
            if ($request && $request->hasSession() && $request->session()->has('sso_token')) {
                return $request->session()->get('sso_token');
            }
        } catch (\Throwable $e) {
            // Si la session n'est pas disponible, logger et continuer
            Log::debug('SSO token retrieval from session failed', [
                'error' => $e->getMessage(),
                'type' => get_class($e),
            ]);
        }

        // Option 2: Depuis les préférences utilisateur (non recommandé pour des raisons de sécurité)
        // $preferences = $user->preferences ?? [];
        // return $preferences['sso_token'] ?? null;

        return null;
    }

    /**
     * Gérer le cas où le token SSO est invalide
     * 
     * @param Request $request
     * @return Response
     */
    protected function handleInvalidToken(Request $request): Response
    {
        // Pour les requêtes AJAX, retourner directement une réponse JSON
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            try {
                // Déconnexion locale
                if ($request->hasSession() && $request->session()->has('sso_token')) {
                    $request->session()->forget('sso_token');
                }
                Auth::guard('web')->logout();
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            } catch (\Throwable $e) {
                Log::debug('Error during logout in SSO validation (AJAX)', [
                    'error' => $e->getMessage(),
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Votre session a expiré. Veuillez vous reconnecter pour continuer.',
                'session_expired' => true,
                'redirect' => route('home')
            ], 401);
        }

        // Pour les requêtes normales, déconnexion avec notification
        if (Auth::check()) {
            try {
                // Déconnexion locale avec notification
                return AuthenticatedSessionController::performLocalLogoutWithNotification($request);
            } catch (\Throwable $e) {
                Log::debug('Error during logout in SSO validation', [
                    'error' => $e->getMessage(),
                ]);
                
                // En cas d'erreur, faire une déconnexion de base et rediriger
                try {
                    // Supprimer le token SSO avant de déconnecter
                    if ($request->hasSession() && $request->session()->has('sso_token')) {
                        $request->session()->forget('sso_token');
                    }
                    
                    Auth::logout();
                    if ($request->hasSession()) {
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
                        $request->session()->flash('session_expired', true);
                        $request->session()->flash('warning', 'Votre session a expiré. Veuillez vous reconnecter pour continuer.');
                    }
                } catch (\Throwable $logoutError) {
                    Log::debug('Error during basic logout', [
                        'error' => $logoutError->getMessage(),
                    ]);
                }
                
                return redirect()->route('home');
            }
        }

        // Si l'utilisateur n'est plus connecté, rediriger vers la page d'accueil
        return redirect()->route('home');
    }
}
