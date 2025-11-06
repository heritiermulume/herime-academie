<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SSOService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     * Ouvre le SSO dans un nouvel onglet pour l'enregistrement
     */
    public function create(Request $request)
    {
        // Si l'utilisateur est déjà connecté localement, rediriger vers le dashboard
        if (Auth::check()) {
            return redirect()->intended(route('dashboard'));
        }

        // Ouvrir le SSO dans un nouvel onglet pour l'enregistrement
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

                // Obtenir l'URL d'enregistrement SSO
                $ssoRegisterUrl = $ssoService->getRegisterUrl($callbackUrl);
                
                // Retourner une vue qui ouvre le SSO dans un nouvel onglet
                return view('auth.register-redirect', [
                    'ssoRegisterUrl' => $ssoRegisterUrl
                ]);
            }
        } catch (\Exception $e) {
            // En cas d'erreur, logger et essayer quand même
            Log::error('SSO Register Redirect Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Essayer quand même de rediriger vers SSO
            $ssoService = app(SSOService::class);
            $callbackUrl = route('sso.callback', [
                'redirect' => route('dashboard')
            ]);
            $ssoRegisterUrl = $ssoService->getRegisterUrl($callbackUrl);
            
            return view('auth.register-redirect', [
                'ssoRegisterUrl' => $ssoRegisterUrl
            ]);
        }

        // Si SSO est désactivé, rediriger quand même vers compte.herime.com
        // (ne devrait jamais arriver si SSO est correctement configuré)
        $ssoService = app(SSOService::class);
        $callbackUrl = route('sso.callback', [
            'redirect' => route('dashboard')
        ]);
        $ssoRegisterUrl = $ssoService->getRegisterUrl($callbackUrl);
        
        return view('auth.register-redirect', [
            'ssoRegisterUrl' => $ssoRegisterUrl
        ]);
    }

    /**
     * Handle an incoming registration request.
     * Cette méthode ne devrait jamais être appelée car l'enregistrement se fait via SSO
     * Redirige vers le SSO si quelqu'un essaie de soumettre un formulaire
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // L'enregistrement se fait uniquement via SSO, rediriger vers le SSO
        Log::warning('Registration attempt via POST request, redirecting to SSO', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return $this->create($request);
    }
}
