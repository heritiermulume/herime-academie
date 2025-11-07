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
    public function create(Request $request): RedirectResponse|View
    {
        // Si l'utilisateur est déjà connecté localement, rediriger vers le dashboard
        if (Auth::check()) {
            return redirect()->intended(route('dashboard'));
        }

        // En environnement de test ou si SSO désactivé, afficher la vue locale
        if (!config('services.sso.enabled', true)) {
            return view('auth.login');
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

        // Si le SSO est désactivé, afficher la vue locale
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
     * Redirige vers le SSO pour une déconnexion globale si activé, sinon vers l'accueil
     */
    public function destroy(Request $request)
    {
        return $this->performLogout($request);
    }

    /**
     * Effectue une déconnexion locale avec notification (sans redirection SSO).
     * Utilisée quand le token SSO est invalide - l'utilisateur voit une notification et peut choisir de se reconnecter.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public static function performLocalLogoutWithNotification(Request $request)
    {
        try {
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                // Stocker un message spécial pour indiquer la déconnexion due à un token invalide
                $request->session()->flash('session_expired', true);
                $request->session()->flash('warning', 'Votre session a expiré. Veuillez vous reconnecter pour continuer.');
            }
        } catch (\Throwable $e) {
            Log::debug('Error during local logout with notification', [
                'error' => $e->getMessage(),
            ]);
        }

        // Rediriger vers la page d'accueil où la notification sera affichée
        return redirect()->route('home');
    }

    /**
     * Effectue une déconnexion complète de l'utilisateur.
     * Peut être appelée depuis d'autres endroits (middleware, trait, etc.)
     * 
     * @param Request $request
     * @param string|null $redirectUrl URL de redirection personnalisée (optionnelle)
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public static function performLogout(Request $request, ?string $redirectUrl = null)
    {
        try {
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        } catch (\Throwable $e) {
            Log::debug('Error during logout', [
                'error' => $e->getMessage(),
            ]);
        }

        // Pour les requêtes AJAX, retourner une réponse JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Votre session a expiré. Veuillez vous reconnecter.',
                'redirect' => $redirectUrl ?? route('home')
            ], 401);
        }

        // URL de redirection (personnalisée ou vers l'accueil)
        $homeUrl = $redirectUrl ?? route('home');
        $finalRedirectUrl = url($homeUrl);

        // Option pour forcer la déconnexion locale uniquement (sans passer par SSO)
        // Si le SSO ne redirige pas correctement, cette option peut être activée
        $forceLocalLogout = config('services.sso.force_local_logout', false);

        // Si SSO est activé et que la déconnexion locale forcée n'est pas activée
        if (!$forceLocalLogout && config('services.sso.enabled', true)) {
            try {
                $ssoService = app(SSOService::class);
                
                // Construire l'URL de déconnexion SSO avec l'URL de redirection
                // Le SSO redirigera l'utilisateur vers cette URL après la déconnexion
                $ssoLogoutUrl = $ssoService->getLogoutUrl($finalRedirectUrl);
                
                Log::info('SSO Logout redirect', [
                    'sso_logout_url' => $ssoLogoutUrl,
                    'redirect_url' => $finalRedirectUrl
                ]);
                
                // Rediriger vers le SSO qui déconnectera l'utilisateur et le redirigera
                return redirect($ssoLogoutUrl);
            } catch (\Throwable $e) {
                // En cas d'erreur SSO, logger l'erreur et rediriger directement
                Log::debug('SSO Logout Error', [
                    'message' => $e->getMessage(),
                    'type' => get_class($e),
                ]);
            }
        }

        // Redirection directe
        // Soit si SSO désactivé, soit si déconnexion locale forcée, soit en cas d'erreur
        return redirect($homeUrl);
    }
}
