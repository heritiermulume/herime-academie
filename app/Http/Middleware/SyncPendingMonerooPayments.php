<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\MonerooController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Synchronise automatiquement les paiements Moneroo en attente pour l'utilisateur.
 * Exécuté à chaque chargement de page de l'espace client : si l'utilisateur a été débité
 * mais que la page de succès n'a pas chargé (connexion coupée), la vérification auprès
 * de Moneroo se fait ici et la commande est finalisée (enrollments, emails, etc.).
 */
class SyncPendingMonerooPayments
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        // Seulement pour les requêtes GET (chargement de page), pas AJAX/API
        if (!$request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return $next($request);
        }

        try {
            app(MonerooController::class)->syncPendingPaymentsForUser(auth()->id());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SyncPendingMonerooPayments: Error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}
