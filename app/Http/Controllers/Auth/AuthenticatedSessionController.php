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
    public function create(Request $request): View|RedirectResponse
    {
        // Si l'utilisateur est déjà connecté localement, rediriger vers le dashboard
        if (Auth::check()) {
            return redirect()->intended(route('dashboard'));
        }

        // Protéger contre les boucles de redirection SSO
        // Si on vient d'une erreur SSO récente, ne pas rediriger vers SSO
        $ssoErrorCount = $request->session()->get('sso_error_count', 0);
        if ($ssoErrorCount >= 2) {
            Log::warning('SSO redirect loop detected, showing local login instead');
            $request->session()->forget('sso_error_count');
            // Afficher la vue de connexion locale pour éviter la boucle
            return view('auth.login')->withErrors(['sso' => 'Erreur de connexion SSO. Veuillez vous connecter localement.']);
        }

        // Si SSO est activé, rediriger vers compte.herime.com
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
            // En cas d'erreur SSO, afficher la vue de connexion locale
            Log::error('SSO Redirect Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
