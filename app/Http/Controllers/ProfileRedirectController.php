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

        // Si pas de token SSO, déclencher la déconnexion avec notification
        // Ne pas rediriger vers compte.herime.com si le token est absent
        if (empty($ssoToken)) {
            Log::warning('SSO token missing before profile redirect', [
                'user_id' => $user->id,
            ]);

            // Déclencher la déconnexion avec notification
            if (Auth::check()) {
                try {
                    return AuthenticatedSessionController::performLocalLogoutWithNotification($request);
                } catch (\Throwable $e) {
                    Log::debug('Error during logout in profile redirect (missing token)', [
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
                        Log::debug('Error during basic logout in profile redirect (missing token)', [
                            'error' => $logoutError->getMessage(),
                        ]);
                    }
                    
                    return redirect()->route('home');
                }
            }

            return redirect()->route('home');
        }

        // Valider le token SSO (avec gestion d'erreur)
        try {
            $isValid = $this->ssoService->checkToken($ssoToken);
        } catch (\Throwable $e) {
            Log::error('SSO token validation exception in profile redirect', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            // En cas d'exception lors de la validation, déclencher la déconnexion avec notification
            // Ne pas rediriger vers compte.herime.com si la validation échoue
            if (Auth::check()) {
                try {
                    return AuthenticatedSessionController::performLocalLogoutWithNotification($request);
                } catch (\Throwable $logoutException) {
                    Log::debug('Error during logout in profile redirect (validation exception)', [
                        'error' => $logoutException->getMessage(),
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
                        Log::debug('Error during basic logout in profile redirect (validation exception)', [
                            'error' => $logoutError->getMessage(),
                        ]);
                    }
                    
                    return redirect()->route('home');
                }
            }

            return redirect()->route('home');
        }

        // Si le token n'est pas valide, déclencher la déconnexion avec notification
        if (!$isValid) {
            Log::warning('SSO token validation failed before profile redirect', [
                'user_id' => $user->id,
            ]);

            // Si l'utilisateur est encore connecté, appeler la méthode logout avec notification
            if (Auth::check()) {
                try {
                    // Déconnexion locale avec notification (l'utilisateur verra une notification et pourra choisir de se reconnecter)
                    return AuthenticatedSessionController::performLocalLogoutWithNotification($request);
                } catch (\Throwable $e) {
                    Log::debug('Error during logout in profile redirect (invalid token)', [
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
                        Log::debug('Error during basic logout in profile redirect (invalid token)', [
                            'error' => $logoutError->getMessage(),
                        ]);
                    }
                    
                    return redirect()->route('home');
                }
            }

            // Si l'utilisateur n'est plus connecté, rediriger vers la page d'accueil
            return redirect()->route('home');
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
                        return AuthenticatedSessionController::performLocalLogoutWithNotification($request);
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

                        return redirect()->route('home');
                    }
                }

                return redirect()->route('home');
            }
        } catch (\Throwable $e) {
            Log::debug('SSO token user validation exception before profile redirect', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Token valide et correspond à l'utilisateur, rediriger vers le profil SSO
        return redirect($this->ssoService->getProfileUrl());
    }
}

