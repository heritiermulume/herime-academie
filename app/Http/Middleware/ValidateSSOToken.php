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

            // Si pas de token SSO, laisser passer (l'utilisateur peut être connecté localement)
            if (empty($ssoToken)) {
                return $next($request);
            }

            // Valider le token SSO (avec gestion d'erreur)
            try {
                $isValid = $this->ssoService->checkToken($ssoToken);
            } catch (\Throwable $e) {
                // Capturer toutes les exceptions et erreurs
                Log::debug('SSO token validation exception', [
                    'user_id' => $user->id ?? null,
                    'method' => $request->method(),
                    'route' => $request->route()?->getName(),
                    'error' => $e->getMessage(),
                    'type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                // En cas d'erreur, considérer comme valide pour ne pas bloquer l'utilisateur
                // (l'API SSO pourrait être temporairement indisponible)
                return $next($request);
            }

            if (!$isValid) {
                Log::debug('SSO token validation failed before important action', [
                    'user_id' => $user->id ?? null,
                    'method' => $request->method(),
                    'route' => $request->route()?->getName(),
                ]);

                // Si l'utilisateur est encore connecté, appeler la méthode logout avec notification
                if (Auth::check()) {
                    try {
                        // Déconnexion locale avec notification (l'utilisateur verra une notification et pourra choisir de se reconnecter)
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

            return $next($request);
        } catch (\Throwable $e) {
            // En cas d'erreur dans le middleware, logger et laisser passer
            Log::debug('SSO validation middleware error', [
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
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
}
