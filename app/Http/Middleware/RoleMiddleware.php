<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SSOService;

class RoleMiddleware
{
    protected $ssoService;

    public function __construct(SSOService $ssoService)
    {
        $this->ssoService = $ssoService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            // Si SSO est activé, rediriger vers le SSO
            if (config('services.sso.enabled', true)) {
                $currentUrl = $request->fullUrl();
                $callbackUrl = route('sso.callback', [
                    'redirect' => $currentUrl
                ]);
                $ssoLoginUrl = $this->ssoService->getLoginUrl($callbackUrl);
                
                return redirect($ssoLoginUrl);
            }
            
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role ?? 'student';
        $user = auth()->user();
        
        // Pour le rôle "student", permettre l'accès à tous les utilisateurs authentifiés
        // car par défaut tous les utilisateurs sont des étudiants
        // Même les admins, instructeurs peuvent être étudiants d'autres cours
        if ($role === 'student') {
            // Tous les utilisateurs authentifiés peuvent accéder au tableau de bord étudiant
            return $next($request);
        } elseif ($role === 'admin') {
            // Pour l'accès admin, accepter les rôles "admin" et "super_user"
            if (!$user->isAdmin()) {
                abort(403, 'Accès non autorisé. Seuls les administrateurs et super utilisateurs peuvent accéder à cette section.');
            }
        } else {
            // Pour les autres rôles (instructor, affiliate), vérifier strictement
            if ($userRole !== $role) {
                abort(403, 'Accès non autorisé pour ce rôle.');
            }
        }

        return $next($request);
    }
}
