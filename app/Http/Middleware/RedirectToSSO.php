<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SSOService;

class RedirectToSSO
{
    protected $ssoService;

    public function __construct(SSOService $ssoService)
    {
        $this->ssoService = $ssoService;
    }

    /**
     * Handle an incoming request.
     * Redirige vers le SSO si l'utilisateur n'est pas authentifié
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            // Construire l'URL de callback complète
            $currentUrl = $request->fullUrl();
            $callbackUrl = route('sso.callback', [
                'redirect' => $currentUrl
            ]);

            // Rediriger vers le SSO
            $ssoLoginUrl = $this->ssoService->getLoginUrl($callbackUrl);
            
            return redirect($ssoLoginUrl);
        }

        return $next($request);
    }
}

