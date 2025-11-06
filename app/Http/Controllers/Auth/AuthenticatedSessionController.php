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
     * Redirige vers le SSO pour une déconnexion globale si activé, sinon vers l'accueil
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // URL de redirection vers l'accueil (URL absolue complète)
        $homeUrl = route('home');
        $redirectUrl = url($homeUrl);

        // Option pour forcer la déconnexion locale uniquement (sans passer par SSO)
        // Si le SSO ne redirige pas correctement, cette option peut être activée
        $forceLocalLogout = config('services.sso.force_local_logout', false);

        // Si SSO est activé et que la déconnexion locale forcée n'est pas activée
        if (!$forceLocalLogout && config('services.sso.enabled', true)) {
            try {
                $ssoService = app(SSOService::class);
                
                // Construire l'URL de déconnexion SSO avec l'URL de redirection vers l'accueil
                // Le SSO redirigera l'utilisateur vers cette URL après la déconnexion
                $ssoLogoutUrl = $ssoService->getLogoutUrl($redirectUrl);
                
                Log::info('SSO Logout redirect', [
                    'sso_logout_url' => $ssoLogoutUrl,
                    'redirect_url' => $redirectUrl
                ]);
                
                // Rediriger vers le SSO qui déconnectera l'utilisateur et le redirigera vers l'accueil
                return redirect($ssoLogoutUrl);
            } catch (\Exception $e) {
                // En cas d'erreur SSO, logger l'erreur et rediriger directement vers l'accueil
                Log::error('SSO Logout Error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'redirect_url' => $redirectUrl
                ]);
            }
        }

        // Redirection directe vers l'accueil
        // Soit si SSO désactivé, soit si déconnexion locale forcée, soit en cas d'erreur
        return redirect($homeUrl);
    }
}
