<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CartController;
use App\Services\SSOService;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     * Redirige vers le SSO si activé, sinon affiche la vue de connexion locale
     */
    public function create(Request $request): RedirectResponse
    {
        // Si l'utilisateur est déjà connecté localement, rediriger vers le dashboard
        if (Auth::check()) {
            return redirect()->intended(route('dashboard'));
        }

        // Toujours rediriger vers SSO, jamais utiliser la vue locale
        try {
            if (config('services.sso.enabled', true)) {
                $ssoService = app(SSOService::class);
                
                $redirectUrl = $request->query('redirect') 
                    ?: $request->header('Referer') 
                    ?: url()->previous() 
                    ?: route('dashboard');

                // Construire l'URL de callback complète
                $callbackUrl = route('sso.callback', [
                    'redirect' => $redirectUrl
                ]);

                // Utiliser force_token=true pour forcer la génération d'un token
                // même si l'utilisateur est déjà connecté sur compte.herime.com
                $ssoLoginUrl = $ssoService->getLoginUrl($callbackUrl, true);
                
                return redirect($ssoLoginUrl);
            }
        } catch (\Exception $e) {
            // En cas d'erreur, réessayer la redirection vers SSO
            Log::error('SSO Redirect Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Toujours rediriger vers SSO même en cas d'erreur
            $ssoService = app(SSOService::class);
            $callbackUrl = route('sso.callback', [
                'redirect' => route('dashboard')
            ]);
            $ssoLoginUrl = $ssoService->getLoginUrl($callbackUrl, true);
            
            return redirect($ssoLoginUrl);
        }

        // Si SSO est désactivé, rediriger quand même vers compte.herime.com
        // (ne devrait jamais arriver si SSO est correctement configuré)
        $ssoService = app(SSOService::class);
        $callbackUrl = route('sso.callback', [
            'redirect' => route('dashboard')
        ]);
        $ssoLoginUrl = $ssoService->getLoginUrl($callbackUrl, true);
        
        return redirect($ssoLoginUrl);
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
        try {
            if (config('services.sso.enabled', true)) {
                $ssoService = app(SSOService::class);
                $redirectUrl = url('/');
                $ssoLogoutUrl = $ssoService->getLogoutUrl($redirectUrl);
                
                return redirect($ssoLogoutUrl);
            }
        } catch (\Exception $e) {
            // En cas d'erreur SSO, rediriger vers la page d'accueil
            Log::error('SSO Logout Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return redirect('/');
    }
}
