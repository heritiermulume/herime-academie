<?php

namespace App\Http\Middleware;

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
        $ssoToken = $this->getSSOTokenFromUser($user);

        // Si pas de token SSO, laisser passer (l'utilisateur peut être connecté localement)
        if (empty($ssoToken)) {
            return $next($request);
        }

        // Valider le token SSO (avec gestion d'erreur)
        try {
            $isValid = $this->ssoService->checkToken($ssoToken);
        } catch (\Exception $e) {
            Log::error('SSO token validation exception', [
                'user_id' => $user->id,
                'method' => $request->method(),
                'route' => $request->route()?->getName(),
                'error' => $e->getMessage(),
            ]);
            // En cas d'erreur, considérer comme valide pour ne pas bloquer l'utilisateur
            // (l'API SSO pourrait être temporairement indisponible)
            return $next($request);
        }

        if (!$isValid) {
            Log::warning('SSO token validation failed before important action', [
                'user_id' => $user->id,
                'method' => $request->method(),
                'route' => $request->route()?->getName(),
            ]);

            // Déconnecter l'utilisateur et rediriger vers le SSO
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $ssoService = app(SSOService::class);
            $currentUrl = $request->fullUrl();
            $callbackUrl = route('sso.callback', ['redirect' => $currentUrl]);
            $ssoLoginUrl = $ssoService->getLoginUrl($callbackUrl, true);

            return redirect($ssoLoginUrl)
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        }

        return $next($request);
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
        
        // Option 1: Depuis la session (si stocké lors du callback)
        if (session()->has('sso_token')) {
            return session('sso_token');
        }

        // Option 2: Depuis les préférences utilisateur (non recommandé pour des raisons de sécurité)
        // $preferences = $user->preferences ?? [];
        // return $preferences['sso_token'] ?? null;

        return null;
    }
}

