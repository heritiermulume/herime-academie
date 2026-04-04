<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Services\CommunitySettingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityController extends Controller
{
    /**
     * Page d’adhésion « membre premium » / réseau Membre Herime (choix d’abonnement).
     */
    public function premiumJoin(Request $request): View
    {
        $preferredCurrency = strtoupper((string) (is_array(Setting::getBaseCurrency())
            ? (Setting::getBaseCurrency()['code'] ?? 'USD')
            : (Setting::getBaseCurrency() ?: 'USD')));

        $recurringCommunity = SubscriptionPlan::query()
            ->where('is_active', true)
            ->whereIn('plan_type', ['recurring', 'premium'])
            ->with(['content', 'contents'])
            ->orderBy('price')
            ->get()
            ->filter(function (SubscriptionPlan $plan) {
                if (filter_var(data_get($plan->metadata, 'community_premium'), FILTER_VALIDATE_BOOLEAN)) {
                    return true;
                }

                return in_array($plan->slug, [
                    'membre-herime-semestriel',
                    'membre-herime-trimestriel',
                    'membre-herime-annuel',
                ], true);
            });

        $communityPlans = $recurringCommunity
            ->sortBy(function (SubscriptionPlan $plan) {
                return (int) data_get($plan->metadata, 'community_display_order', 99);
            })
            ->values();

        // Une seule offre sur la page : cycle court (mensuel > trimestriel > semestriel) + annuel — bascule dans la même carte.
        $planShort = $communityPlans->firstWhere('billing_period', 'monthly')
            ?? $communityPlans->firstWhere('billing_period', 'quarterly')
            ?? $communityPlans->firstWhere('billing_period', 'semiannual');
        $planAnnual = $communityPlans->firstWhere('billing_period', 'yearly');

        return view('community.premium', [
            'communityPlans' => $communityPlans,
            'communityPremiumPlanShort' => $planShort,
            'communityPremiumPlanAnnual' => $planAnnual,
            'preferredCurrency' => $preferredCurrency,
            'premiumPageTexts' => CommunitySettingsService::premiumPageTexts(),
            'premiumPlanHighlights' => CommunitySettingsService::premiumPlanHighlights(),
        ]);
    }
}
