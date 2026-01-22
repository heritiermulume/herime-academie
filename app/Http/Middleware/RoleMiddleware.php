<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SSOService;
use App\Models\Ambassador;

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
        $userRole = $user->role ?? 'customer';

        // Les administrateurs (admin) et super-utilisateurs (super_user) ont accès à toutes les sections protégées
        // (y compris les routes ambassador, provider, affiliate, etc.)
        // La méthode isAdmin() vérifie à la fois 'admin' et 'super_user'
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        // Pour le rôle "customer", permettre l'accès à tous les utilisateurs authentifiés
        // car par défaut tous les utilisateurs sont des clients
        // Même les admins, prestataires peuvent être clients d'autres cours
        if ($role === 'customer') {
            // Tous les utilisateurs authentifiés peuvent accéder au tableau de bord étudiant
            return $next($request);
        } elseif ($role === 'admin') {
            // Admin access already handled above; reaching ici signifie utilisateur non-admin
            abort(403, 'Accès non autorisé. Seuls les administrateurs et super utilisateurs peuvent accéder à cette section.');
        } elseif ($role === 'ambassador') {
            // Pour les ambassadeurs, vérifier qu'ils ont un enregistrement Ambassador actif
            // Les ambassadeurs ne sont pas identifiés par un rôle dans la table users,
            // mais par la présence d'un enregistrement dans la table ambassadors
            // Note: Les administrateurs ont déjà accès (vérifié ci-dessus)
            $ambassador = Ambassador::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
            
            if (!$ambassador) {
                abort(403, 'Accès non autorisé. Seuls les ambassadeurs actifs peuvent accéder à cette section.');
            }
        } else {
            // Pour les autres rôles (provider, affiliate), vérifier strictement
            if ($userRole !== $role) {
                abort(403, 'Accès non autorisé pour ce rôle.');
            }
        }

        return $next($request);
    }
}
