<?php

namespace App\Http\Middleware;

use App\Models\Course;
use Closure;
use Illuminate\Http\Request;

class EnsureSubscriptionAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        /** @var Course|null $course */
        $course = $request->route('course');
        if (! $course instanceof Course) {
            return $next($request);
        }

        if (! $course->requires_subscription || $course->is_free) {
            return $next($request);
        }

        $activeSubscriptions = $user->activeSubscriptions()->with('plan')->get();

        if ($activeSubscriptions->isNotEmpty()) {
            $requiredTier = strtolower((string) $course->required_subscription_tier);
            if ($requiredTier !== '') {
                $tierRank = [
                    'starter' => 1,
                    'pro' => 2,
                    'enterprise' => 3,
                ];
                $requiredRank = $tierRank[$requiredTier] ?? 1;

                $hasRequiredTier = $activeSubscriptions->contains(function ($subscription) use ($tierRank, $requiredRank) {
                    $tier = strtolower((string) data_get($subscription->plan?->metadata, 'tier', 'starter'));

                    return ($tierRank[$tier] ?? 1) >= $requiredRank;
                });

                if (! $hasRequiredTier) {
                    if ($course->userHasValidStandalonePurchase($user->id)) {
                        return $next($request);
                    }

                    return redirect()->route('customer.subscriptions')
                        ->with('error', 'Votre formule actuelle ne permet pas d\'accéder à ce contenu.');
                }
            }

            return $next($request);
        }

        if ($course->userHasValidStandalonePurchase($user->id)) {
            return $next($request);
        }

        return redirect()->route('customer.subscriptions')
            ->with('error', 'Un abonnement actif est requis pour accéder à ce contenu.');
    }
}
