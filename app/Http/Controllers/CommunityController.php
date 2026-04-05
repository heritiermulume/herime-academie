<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\CommunitySettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        $communityPlansOrdered = SubscriptionPlan::activeMemberCommunityPlans();
        $communityPlans = $communityPlansOrdered;

        $premiumPlanHighlights = [];
        foreach ($communityPlansOrdered as $p) {
            $h = trim((string) data_get($p->metadata, 'community_card_highlight', ''));
            if ($h !== '') {
                $premiumPlanHighlights[$p->slug] = $h;
            }
        }

        $defaultPlan = $communityPlansOrdered->first(fn (SubscriptionPlan $p) => $p->isCommunityCardPopular())
            ?? $communityPlansOrdered->firstWhere('billing_period', 'yearly')
            ?? $communityPlansOrdered->first();

        $showPremiumCard = $communityPlansOrdered->isNotEmpty();

        $premiumSubscriptionsByPlanId = $this->premiumMemberSubscriptionsKeyedByPlanId(auth()->user());

        return view('community.premium', [
            'communityPlans' => $communityPlans,
            'communityPlansOrdered' => $communityPlansOrdered,
            'communityPremiumDefaultPlan' => $defaultPlan,
            'showPremiumCard' => $showPremiumCard,
            'preferredCurrency' => $preferredCurrency,
            'premiumPageTexts' => CommunitySettingsService::premiumPageTexts(),
            'premiumPlanHighlights' => $premiumPlanHighlights,
            'premiumSubscriptionsByPlanId' => $premiumSubscriptionsByPlanId,
        ]);
    }

    /**
     * Dernier abonnement « en cours » par plan Membre Herime (slugs fixes).
     *
     * @return Collection<int, UserSubscription>
     */
    private function premiumMemberSubscriptionsKeyedByPlanId(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        $memberPlanIds = SubscriptionPlan::memberBundlePlanIds();

        if ($memberPlanIds === []) {
            return collect();
        }

        return $user->subscriptions()
            ->whereIn('subscription_plan_id', $memberPlanIds)
            ->with('invoices')
            ->get()
            ->filter(fn (UserSubscription $s) => $s->isActiveMembershipPeriod())
            ->sortByDesc('id')
            ->unique('subscription_plan_id')
            ->keyBy('subscription_plan_id');
    }
}
