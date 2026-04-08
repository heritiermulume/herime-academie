<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\SubscriptionPlan;
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

        if (SubscriptionPlan::userMeetsMemberPeriodForSubscriptionGatedContent($user, $course)) {
            return $next($request);
        }

        $activeSubscriptions = $user->activeSubscriptions()->with('plan')->get();
        if ($activeSubscriptions->isNotEmpty()) {
            return redirect()->route('customer.subscriptions')
                ->with('error', 'Votre formule actuelle ne permet pas d\'accéder à ce contenu.');
        }

        return redirect()->route('customer.subscriptions')
            ->with('error', 'Un abonnement actif est requis pour accéder à ce contenu.');
    }
}
