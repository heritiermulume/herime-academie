<?php

namespace App\Http\Middleware;

use App\Http\Controllers\MonerooController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Synchronise les paiements Moneroo en attente et annule les commandes pending trop anciennes.
 * Exécuté sur les GET « navigateur » uniquement, avec un cache global (défaut 10 min,
 * config payments.visit_processing_cache_seconds) pour limiter la charge serveur.
 */
class SyncPendingMonerooPayments
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        if (! $request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return $next($request);
        }

        try {
            app(MonerooController::class)->runThrottledVisitPaymentMaintenance((int) auth()->id());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SyncPendingMonerooPayments: Error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}
