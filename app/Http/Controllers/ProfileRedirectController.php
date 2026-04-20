<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Services\SSOService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileRedirectController extends Controller
{
    protected $ssoService;

    public function __construct(SSOService $ssoService)
    {
        $this->ssoService = $ssoService;
    }

    /**
     * Valider le token SSO avant de rediriger vers le profil
     * Si la validation échoue, déclencher la déconnexion avec notification au lieu de rediriger
     */
    public function redirect(Request $request)
    {
        // Ne valider que si SSO est activé
        if (!config('services.sso.enabled', true)) {
            return redirect($this->ssoService->getProfileUrl());
        }

        // Si l'utilisateur n'est pas authentifié, rediriger vers login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $ssoToken = session('sso_token');

        // Si pas de token SSO, démarrer le flux SSO pour en obtenir un
        if (empty($ssoToken)) {
            Log::warning('SSO token missing before profile redirect', [
                'user_id' => $user->id,
            ]);

            return redirect($this->buildSsoLoginUrlForProfileRedirect());
        }

        // Valider le token SSO (avec gestion d'erreur)
        try {
            $isValid = $this->ssoService->checkToken($ssoToken);
        } catch (\Throwable $e) {
            Log::error('SSO token validation exception in profile redirect', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            if ($request->hasSession()) {
                $request->session()->forget('sso_token');
            }

            return redirect($this->buildSsoLoginUrlForProfileRedirect());
        }

        // Si le token n'est pas valide, relancer un login SSO pour regénérer un token valide.
        if (!$isValid) {
            Log::warning('SSO token validation failed before profile redirect', [
                'user_id' => $user->id,
            ]);

            if ($request->hasSession()) {
                $request->session()->forget('sso_token');
            }

            return redirect($this->buildSsoLoginUrlForProfileRedirect());
        }

        // Token valide, vérifier que le profil correspond bien à l'utilisateur connecté
        try {
            $tokenUser = $this->ssoService->validateToken($ssoToken);

            $tokenEmail = isset($tokenUser['email']) ? strtolower($tokenUser['email']) : null;
            $localEmail = strtolower($user->email ?? '');

            if (!$tokenUser || ($tokenEmail && $tokenEmail !== $localEmail)) {
                Log::warning('SSO token belongs to another user before profile redirect', [
                    'current_user_id' => $user->id,
                    'current_email' => $user->email,
                    'token_email' => $tokenEmail,
                ]);

                if (Auth::check()) {
                    try {
                        // Déconnexion locale
                        AuthenticatedSessionController::performLocalLogoutWithNotification($request);
                    } catch (\Throwable $e) {
                        Log::debug('Error during logout after token mismatch in profile redirect', [
                            'error' => $e->getMessage(),
                        ]);

                        try {
                            Auth::logout();
                            if ($request->hasSession()) {
                                $request->session()->invalidate();
                                $request->session()->regenerateToken();
                                $request->session()->flash('session_expired', true);
                                $request->session()->flash('warning', 'Votre session a expiré. Veuillez vous reconnecter pour continuer.');
                            }
                        } catch (\Throwable $logoutError) {
                            Log::debug('Error during basic logout after token mismatch in profile redirect', [
                                'error' => $logoutError->getMessage(),
                            ]);
                        }

                    }
                }

                try {
                    $callbackUrl = route('profile.redirect');
                    $loginUrl = $this->ssoService->getLoginUrl($callbackUrl, true);
                    $logoutUrl = $this->ssoService->getLogoutUrl($loginUrl);

                    return redirect($logoutUrl)
                        ->with('warning', 'Nous vous redirigeons vers le SSO pour sélectionner le bon compte.');
                } catch (\Throwable $e) {
                    Log::debug('Error creating SSO logout/login URLs after token mismatch', [
                        'error' => $e->getMessage(),
                    ]);

                    return redirect()->route('home')
                        ->with('warning', 'Votre session a expiré. Veuillez vous reconnecter.');
                }
            }
        } catch (\Throwable $e) {
            Log::debug('SSO token user validation exception before profile redirect', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Token valide et correspond à l'utilisateur : aller vers le profil SSO.
        return redirect($this->ssoService->getProfileUrl());
    }

    private function buildSsoLoginUrlForProfileRedirect(): string
    {
        $callbackUrl = route('sso.callback', ['redirect' => route('profile.redirect')]);

        return $this->ssoService->getLoginUrl($callbackUrl, true);
    }
}

