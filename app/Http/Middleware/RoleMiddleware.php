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
            // Rediriger vers le SSO (authentification locale désactivée)
            $currentUrl = $request->fullUrl();
            $callbackUrl = route('sso.callback', [
                'redirect' => $currentUrl
            ]);
            $ssoLoginUrl = $this->ssoService->getLoginUrl($callbackUrl);
            
            return redirect($ssoLoginUrl);
        }

        $user = auth()->user();
        $userRole = $user->role ?? 'student';

        // Les administrateurs et super-utilisateurs ont accès à toutes les sections protégées
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        // Pour le rôle "student", permettre l'accès à tous les utilisateurs authentifiés
        // car par défaut tous les utilisateurs sont des étudiants
        // Même les admins, instructeurs peuvent être étudiants d'autres cours
        if ($role === 'student') {
            // Tous les utilisateurs authentifiés peuvent accéder au tableau de bord étudiant
            return $next($request);
        } elseif ($role === 'admin') {
            // Admin access already handled above; reaching ici signifie utilisateur non-admin
            abort(403, 'Accès non autorisé. Seuls les administrateurs et super utilisateurs peuvent accéder à cette section.');
        } else {
            // Pour les autres rôles (instructor, affiliate), vérifier strictement
            if ($userRole !== $role) {
                abort(403, 'Accès non autorisé pour ce rôle.');
            }
        }

        return $next($request);
    }
}
