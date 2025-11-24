<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SSOService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view (local).
     * Redirige maintenant vers SSO - l'inscription locale est désactivée.
     */
    public function create(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended(route('dashboard'));
        }
        
        // Rediriger vers SSO
        $ssoService = app(SSOService::class);
        $redirectUrl = $request->query('redirect') 
            ?: $request->header('Referer') 
            ?: url()->previous() 
            ?: route('dashboard');
        
        $callbackUrl = route('sso.callback', ['redirect' => $redirectUrl]);
        $ssoRegisterUrl = $ssoService->getRegisterUrl($callbackUrl);
        
        return redirect()->away($ssoRegisterUrl);
    }

    /**
     * Handle an incoming registration request.
     * Redirige maintenant vers SSO - l'inscription locale est désactivée.
     */
    public function store(Request $request): RedirectResponse
    {
        // Rediriger vers SSO au lieu d'enregistrer localement
        $ssoService = app(SSOService::class);
        $redirectUrl = $request->query('redirect') 
            ?: route('dashboard');
        
        $callbackUrl = route('sso.callback', ['redirect' => $redirectUrl]);
        $ssoRegisterUrl = $ssoService->getRegisterUrl($callbackUrl);
        
        return redirect()->away($ssoRegisterUrl);
    }
}
