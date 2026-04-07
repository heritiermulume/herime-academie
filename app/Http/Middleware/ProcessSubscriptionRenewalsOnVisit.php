<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fait avancer les renouvellements d’abonnement pour l’utilisateur connecté sans dépendre du cron Laravel.
 * S’exécute sur les GET « navigateur » (pas AJAX/API), avec un cache (défaut 10 min, config/subscriptions.php).
 */
class ProcessSubscriptionRenewalsOnVisit
{
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

        $ttl = (int) config('subscriptions.process_renewals_visit_cache_seconds', 600);
        if ($ttl > 0 && Cache::has($cacheKey)) {
            return $next($request);
        }

        try {
            app(SubscriptionService::class)->processRenewalsForUser($userId);
            if ($ttl > 0) {
                Cache::put($cacheKey, true, now()->addSeconds($ttl));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ProcessSubscriptionRenewalsOnVisit: erreur', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}
