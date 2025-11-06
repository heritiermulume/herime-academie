<?php

namespace App\Traits;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Services\SSOService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Trait pour faciliter la validation SSO avant les actions importantes dans les contrôleurs
 * 
 * Usage:
 * 
 * class MyController extends Controller
 * {
 *     use ValidatesSSOToken;
 *     
 *     public function store(Request $request)
 *     {
 *         // Valider le token SSO avant l'action
 *         if (!$this->validateSSOTokenBeforeAction()) {
 *             return redirect()->back()->with('error', 'Votre session a expiré.');
 *         }
 *         
 *         // Votre code ici...
 *     }
 * }
 */
trait ValidatesSSOToken
{
    /**
     * Valide le token SSO avant une action importante
     * 
     * @param callable|null $onInvalid Callback à exécuter si le token est invalide
     * @return bool true si le token est valide, false sinon
     */
    protected function validateSSOTokenBeforeAction(?callable $onInvalid = null): bool
    {
        // Ne valider que si SSO est activé
        if (!config('services.sso.enabled', true)) {
            return true;
        }

        // Si l'utilisateur n'est pas authentifié, retourner false
        if (!Auth::check()) {
            if ($onInvalid) {
                $onInvalid();
            }
            return false;
        }

        $user = Auth::user();
        $ssoToken = $this->getSSOTokenForUser($user);

        // Si pas de token SSO, considérer comme valide (utilisateur peut être connecté localement)
        if (empty($ssoToken)) {
            return true;
        }

        $ssoService = app(SSOService::class);
        $isValid = $ssoService->checkToken($ssoToken);

        if (!$isValid) {
            Log::warning('SSO token validation failed before important action', [
                'user_id' => $user->id,
                'method' => request()->method(),
                'route' => request()->route()?->getName(),
            ]);

            if ($onInvalid) {
                $onInvalid();
            } else {
                // Comportement par défaut : déconnecter et rediriger
                $this->handleSSOTokenInvalid();
            }

            return false;
        }

        return true;
    }

    /**
     * Récupérer le token SSO pour un utilisateur
     * 
     * @param \App\Models\User $user
     * @return string|null
     */
    protected function getSSOTokenForUser($user): ?string
    {
        // Option 1: Depuis la session (si stocké lors du callback)
        if (session()->has('sso_token')) {
            return session('sso_token');
        }

        // Option 2: Depuis les préférences utilisateur (non recommandé pour des raisons de sécurité)
        // $preferences = $user->preferences ?? [];
        // return $preferences['sso_token'] ?? null;

        return null;
    }

    /**
     * Gère le cas où le token SSO est invalide
     * Déconnecte l'utilisateur localement et affiche une notification avec option de reconnexion
     */
    protected function handleSSOTokenInvalid()
    {
        // Si l'utilisateur est encore connecté, appeler la méthode logout avec notification
        if (Auth::check()) {
            try {
                // Déconnexion locale avec notification (l'utilisateur verra une notification et pourra choisir de se reconnecter)
                $response = AuthenticatedSessionController::performLocalLogoutWithNotification(request());
                
                // Si la réponse est une redirection, l'envoyer et terminer
                if ($response instanceof \Illuminate\Http\RedirectResponse) {
                    $response->send();
                    exit;
                }
            } catch (\Throwable $e) {
                Log::debug('Error during logout in SSO token validation', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // Fallback : déconnexion basique si la méthode performLocalLogoutWithNotification échoue
        try {
            Auth::logout();
            if (request()->hasSession()) {
                request()->session()->invalidate();
                request()->session()->regenerateToken();
                request()->session()->flash('session_expired', true);
                request()->session()->flash('warning', 'Votre session a expiré. Veuillez vous reconnecter pour continuer.');
            }
        } catch (\Throwable $e) {
            Log::debug('Error during basic logout in SSO token validation', [
                'error' => $e->getMessage(),
            ]);
        }
        
        // Rediriger vers la page d'accueil
        redirect()->route('home')->send();
        exit;
    }
}

