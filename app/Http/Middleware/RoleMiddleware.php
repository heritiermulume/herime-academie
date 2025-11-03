<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role ?? 'student';
        
        // Pour le rôle "student", permettre l'accès à tous les utilisateurs authentifiés
        // car par défaut tous les utilisateurs sont des étudiants
        // Même les admins, instructeurs peuvent être étudiants d'autres cours
        if ($role === 'student') {
            // Tous les utilisateurs authentifiés peuvent accéder au tableau de bord étudiant
            return $next($request);
        } else {
            // Pour les autres rôles (admin, instructor, affiliate), vérifier strictement
            if ($userRole !== $role) {
            abort(403, 'Accès non autorisé pour ce rôle.');
            }
        }

        return $next($request);
    }
}
