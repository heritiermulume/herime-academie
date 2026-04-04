<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fait avancer les renouvellements d’abonnement pour l’utilisateur connecté sans dépendre du cron Laravel.
 * S’exécute sur les GET « navigateur » (pas AJAX/API), avec un délai minimal entre deux passages (cache).
 */
class ProcessSubscriptionRenewalsOnVisit
{
    /** Délai entre deux traitements complets pour un même utilisateur (évite du travail à chaque clic). */
    private const CACHE_TTL_SECONDS = 600;

    private const CACHE_PREFIX = 'subscription_renewals_visit:';

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        if (! $request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return $next($request);
        }

        $userId = (int) auth()->id();
        $cacheKey = self::CACHE_PREFIX.$userId;

        if (Cache::has($cacheKey)) {
            return $next($request);
        }

        try {
            app(SubscriptionService::class)->processRenewalsForUser($userId);
            Cache::put($cacheKey, true, now()->addSeconds(self::CACHE_TTL_SECONDS));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ProcessSubscriptionRenewalsOnVisit: erreur', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}
