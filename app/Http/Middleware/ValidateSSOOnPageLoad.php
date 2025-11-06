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

            // Utiliser un cache pour éviter de valider le token à chaque requête
            // La validation est mise en cache pour 30 secondes pour améliorer les performances
            $cacheKey = 'sso_token_valid_' . md5($ssoToken . '_' . $user->id);
            $cacheTtl = 30; // 30 secondes
            
            // Essayer de récupérer depuis le cache
            $cachedValidation = cache()->get($cacheKey);
            
            if ($cachedValidation !== null) {
                // Utiliser la valeur en cache
                $isValid = (bool) $cachedValidation;
                Log::debug('SSO token validation from cache', [
                    'user_id' => $user->id,
                    'is_valid' => $isValid,
                ]);
            } else {
                // Pas de cache, valider le token
                try {
                    $isValid = $this->ssoService->checkToken($ssoToken);
                    
                    // Mettre en cache le résultat (30 secondes)
                    cache()->put($cacheKey, $isValid ? 1 : 0, $cacheTtl);
                    
                    Log::debug('SSO token validation performed', [
                        'user_id' => $user->id,
                        'is_valid' => $isValid,
                    ]);
                } catch (\Throwable $e) {
                    // Capturer toutes les exceptions et erreurs
                    Log::debug('SSO token validation exception in page load', [
                        'user_id' => $user->id ?? null,
                        'method' => $request->method(),
                        'route' => $routeName,
                        'error' => $e->getMessage(),
                        'type' => get_class($e),
                    ]);
                    
                    // En cas d'erreur d'API, on peut être permissif ou strict selon la config
                    $failOnError = config('services.sso.fail_on_api_error', false);
                    
                    if ($failOnError) {
                        // En mode strict, une erreur API = token invalide
                        // Invalider le cache pour forcer une nouvelle vérification au prochain appel
                        cache()->forget($cacheKey);
                        return $this->handleInvalidToken($request);
                    }
                    
                    // Sinon, laisser passer (l'API SSO pourrait être temporairement indisponible)
                    // Ne pas mettre en cache en cas d'erreur pour permettre une nouvelle tentative
                    return $next($request);
                }
            }

            // Si le token est invalide, invalider le cache et déconnecter
            if (!$isValid) {
                cache()->forget($cacheKey);
                
                Log::warning('SSO token validation failed on page load', [
                    'user_id' => $user->id ?? null,
                    'method' => $request->method(),
                    'route' => $routeName,
                ]);

                // Token invalide, déconnecter l'utilisateur
                return $this->handleInvalidToken($request);
            }

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
}

