<?php

namespace App\Http\Controllers;

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

        // Si pas de token SSO, rediriger quand même (utilisateur peut être connecté localement)
        if (empty($ssoToken)) {
            return redirect($this->ssoService->getProfileUrl());
        }

        // Valider le token SSO
        $isValid = $this->ssoService->checkToken($ssoToken);

        if (!$isValid) {
            Log::warning('SSO token validation failed before profile redirect', [
                'user_id' => $user->id,
            ]);

            // Déconnecter l'utilisateur et rediriger vers le SSO
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $currentUrl = $request->fullUrl();
            $callbackUrl = route('sso.callback', ['redirect' => $currentUrl]);
            $ssoLoginUrl = $this->ssoService->getLoginUrl($callbackUrl, true);

            return redirect($ssoLoginUrl)
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        }

        // Token valide, rediriger vers le profil
        return redirect($this->ssoService->getProfileUrl());
    }
}

