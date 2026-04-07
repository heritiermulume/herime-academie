<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Déclenche processRenewals() (tous utilisateurs) sur navigation GET admin,
 * avec cache global (défaut 10 min, config/subscriptions.php).
 */
class ProcessSubscriptionRenewalsOnAdminVisit
{
    private const CACHE_KEY = 'subscription_renewals:admin_visit';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return $next($request);
        }

        $ttl = (int) config('subscriptions.process_renewals_admin_visit_cache_seconds', 600);
        if ($ttl > 0 && Cache::has(self::CACHE_KEY)) {
            return $next($request);
        }

        try {
            app(SubscriptionService::class)->processRenewals();
            if ($ttl > 0) {
                Cache::put(self::CACHE_KEY, true, now()->addSeconds($ttl));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ProcessSubscriptionRenewalsOnAdminVisit: erreur', [
                'error' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}
