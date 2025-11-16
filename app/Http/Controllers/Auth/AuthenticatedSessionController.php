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
     * Display the login view (local).
     */
    public function create(Request $request): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->intended(route('dashboard'));
        }
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
            // Supprimer le token SSO de la session avant de déconnecter
            if ($request->hasSession() && $request->session()->has('sso_token')) {
                $request->session()->forget('sso_token');
                Log::debug('SSO token removed from session during logout with notification');
            }

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
            // Supprimer le token SSO de la session avant de déconnecter
            if ($request->hasSession() && $request->session()->has('sso_token')) {
                $request->session()->forget('sso_token');
                Log::debug('SSO token removed from session during logout');
            }

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

        // Redirection directe (sans SSO)
        return redirect($homeUrl);
    }
}
