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
 * Middleware pour valider le token SSO à chaque chargement de page
 * 
 * Ce middleware s'exécute sur toutes les requêtes authentifiées (GET, POST, etc.)
 * pour s'assurer que la session SSO est toujours valide
 */
class ValidateSSOOnPageLoad
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

            // Exclure certaines routes qui ne nécessitent pas de validation SSO
            $excludedRoutes = [
                'sso.callback',
                'sso.redirect',
                'logout',
                'login',
                'register',
                'password.request',
                'password.reset',
            ];

            $routeName = $request->route()?->getName();
            if (in_array($routeName, $excludedRoutes)) {
                return $next($request);
            }

            $chunkRoutes = ['admin.uploads.chunk', 'provider.uploads.chunk'];
            if (in_array($routeName, $chunkRoutes, true) || $request->ajax() || $request->wantsJson()) {
                return $next($request);
            }

            // Exclure les routes API et AJAX si nécessaire
            if ($request->expectsJson() && !$request->is('api/*')) {
                // Pour les requêtes AJAX, on peut être plus permissif
                // ou valider quand même selon vos besoins
            }

            // Si l'utilisateur n'est pas authentifié, laisser passer
            if (!Auth::check()) {
                return $next($request);
            }

            $user = Auth::user();
            
            if (!$user) {
                return $next($request);
            }
            
            // Récupérer le token SSO depuis la session
            $ssoToken = null;
            try {
                if ($request->hasSession() && $request->session()->has('sso_token')) {
                    $ssoToken = $request->session()->get('sso_token');
                }
            } catch (\Throwable $e) {
                Log::debug('SSO token retrieval failed in page load middleware', [
                    'error' => $e->getMessage(),
                    'type' => get_class($e),
                ]);
                $ssoToken = null;
            }

            // Si pas de token SSO, laisser passer (utilisateur peut être connecté localement)
            // Mais seulement pour les routes qui ne nécessitent pas SSO
            if (empty($ssoToken)) {
                // Si SSO est activé mais pas de token, on peut considérer que c'est un problème
                // Cependant, pour éviter de bloquer les utilisateurs, on laisse passer
                // Vous pouvez activer la validation stricte si nécessaire
                $strictValidation = config('services.sso.strict_validation', false);
                
                if ($strictValidation) {
                    // En mode strict, si SSO est activé, un token est requis
                    Log::warning('SSO token missing but SSO is enabled (strict mode)', [
                        'user_id' => $user->id,
                        'route' => $routeName,
                    ]);
                    // Déconnecter l'utilisateur en mode strict
                    return $this->handleInvalidToken($request);
                }
                
                return $next($request);
            }

            // TOUJOURS vérifier d'abord la validation locale
            // Si le token est valide localement (pas expiré, format correct), on fait confiance
            // même si l'API SSO échoue
            try {
                $localValidation = $this->ssoService->validateToken($ssoToken);
                if ($localValidation !== null) {
                    // Le token est valide localement - on fait TOUJOURS confiance
                    // Même si l'API SSO échoue, on autorise la requête
                    Log::debug('SSO token valid locally on page load - allowing request (trusting local validation)', [
                        'user_id' => $user->id ?? null,
                        'method' => $request->method(),
                        'route' => $routeName,
                        'user_email' => $localValidation['email'] ?? 'unknown',
                    ]);
                    return $next($request);
                }
            } catch (\Throwable $localException) {
                Log::debug('SSO local validation failed on page load', [
                    'error' => $localException->getMessage(),
                ]);
            }

            // Si la validation locale échoue, essayer l'API mais ne jamais déconnecter
            // si l'API échoue aussi (on considère que c'est un problème d'API)
            try {
                $isValid = $this->ssoService->checkToken($ssoToken);
                if ($isValid) {
                    return $next($request);
                }
            } catch (\Throwable $e) {
                // En cas d'erreur API, on laisse passer (l'API pourrait être indisponible)
                Log::debug('SSO API validation error on page load - allowing request anyway', [
                    'user_id' => $user->id ?? null,
                    'error' => $e->getMessage(),
                ]);
                return $next($request);
            }

            // Seulement déconnecter si la validation locale ET l'API échouent
            // Mais seulement si on est sûr que le token est vraiment invalide
            Log::warning('SSO token validation failed both locally and via API on page load - disconnecting user', [
                'user_id' => $user->id ?? null,
                'method' => $request->method(),
                'route' => $routeName,
            ]);

            // Token vraiment invalide, déconnecter l'utilisateur
            return $this->handleInvalidToken($request);

            return $next($request);
        } catch (\Throwable $e) {
            // En cas d'erreur dans le middleware, logger et laisser passer pour ne pas bloquer l'application
            Log::error('SSO page load validation middleware error', [
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return $next($request);
        }
    }

    /**
     * Gérer le cas d'un token invalide
     * 
     * @param Request $request
     * @return Response
     */
    protected function handleInvalidToken(Request $request): Response
    {
        // Pour les requêtes AJAX, retourner une réponse JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Votre session a expiré. Veuillez vous reconnecter.',
                'redirect' => route('home'),
                'session_expired' => true
            ], 401);
        }

        // Si l'utilisateur est encore connecté, appeler la méthode logout avec notification
        if (Auth::check()) {
            try {
                return AuthenticatedSessionController::performLocalLogoutWithNotification($request);
            } catch (\Throwable $e) {
                Log::debug('Error during logout in SSO page load validation', [
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
                    Log::debug('Error during basic logout in SSO page load', [
                        'error' => $logoutError->getMessage(),
                    ]);
                }
                
                return redirect()->route('home');
            }
        }

        // Si l'utilisateur n'est plus connecté, rediriger vers la page d'accueil
        return redirect()->route('home');
    }

    /**
     * Valider la session SSO en appelant /api/me
     * 
     * @param string $token
     * @return bool
     */
    protected function validateSSOSession(string $token): bool
    {
        try {
            // Utiliser l'endpoint /api/sso/check-token pour vérifier le token
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post('https://compte.herime.com/api/sso/check-token', [
                    'token' => $token,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Vérifier que le token est valide ET que la session est active
                $isValid = $data['valid'] ?? false;
                $sessionActive = $data['session_active'] ?? false;
                
                if (!$isValid) {
                    Log::debug('SSO token is invalid', [
                        'response' => $data,
                    ]);
                    return false;
                }
                
                if (!$sessionActive) {
                    Log::debug('SSO session marked as inactive', [
                        'response' => $data,
                    ]);
                    return false;
                }
                
                // Token valide ET session active
                return true;
            }

            Log::debug('SSO session validation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::debug('SSO session validation exception', [
                'error' => $e->getMessage(),
                'type' => get_class($e),
            ]);
            
            // En cas d'erreur réseau, considérer comme invalide pour forcer une reconnexion
            return false;
        }
    }
}

