<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CartController;
use App\Services\SSOService;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected $ssoService;

    public function __construct(SSOService $ssoService)
    {
        $this->ssoService = $ssoService;
    }

    /**
     * Display the login view.
     * Redirige vers le SSO si activé, sinon affiche la vue de connexion locale
     */
    public function create(Request $request): View|RedirectResponse
    {
        // Si SSO est activé, rediriger vers compte.herime.com
        if (config('services.sso.enabled', true)) {
            $redirectUrl = $request->query('redirect') 
                ?: $request->header('Referer') 
                ?: url()->previous() 
                ?: route('dashboard');

            // Construire l'URL de callback complète
            $callbackUrl = route('sso.callback', [
                'redirect' => $redirectUrl
            ]);

            $ssoLoginUrl = $this->ssoService->getLoginUrl($callbackUrl);
            
            return redirect($ssoLoginUrl);
        }

        // Sinon, afficher la vue de connexion locale
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Synchroniser le panier de session avec la base de données
        $cartController = new CartController();
        $cartController->syncSessionToDatabase();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     * Redirige vers le SSO pour une déconnexion globale si activé
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Si SSO est activé, rediriger vers la déconnexion SSO pour une déconnexion globale
        if (config('services.sso.enabled', true)) {
            $redirectUrl = url('/');
            $ssoLogoutUrl = $this->ssoService->getLogoutUrl($redirectUrl);
            
            return redirect($ssoLogoutUrl);
        }

        return redirect('/');
    }
}
