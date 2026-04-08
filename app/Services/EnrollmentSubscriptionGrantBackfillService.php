<?php

namespace App\Services;

use App\Models\ContentPackage;
use App\Models\Course;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;

class EnrollmentSubscriptionGrantBackfillService
{
    /**
     * Même périmètre métier que {@see SubscriptionService::grantLinkedContentAccess} pour savoir si un plan ouvre ce cours.
     */
    public static function planGrantsContent(SubscriptionPlan $plan, Course $course): bool
    {
        if ((int) $plan->content_id === (int) $course->id) {
            return SubscriptionPlan::planMatchesCourseMemberPeriod($plan, $course);
        }

        if ($plan->contents()->where('contents.id', $course->id)->exists()) {
            return SubscriptionPlan::planMatchesCourseMemberPeriod($plan, $course);
        }

        $packageIds = data_get($plan->metadata, 'included_package_ids', []);
        if (is_array($packageIds) && $packageIds !== []) {
            $ids = array_values(array_filter(array_map('intval', $packageIds)));
            if ($ids !== [] && ContentPackage::query()
                ->whereIn('id', $ids)
                ->whereHas('contents', fn ($q) => $q->where('contents.id', $course->id))
                ->exists()) {
                return SubscriptionPlan::planMatchesCourseMemberPeriod($plan, $course);
            }
        }

        if ($plan->isCommunityPremiumPlan()
            && $course->qualifiesForCommunityMemberSubscriptionCatalog()) {
            return SubscriptionPlan::planMatchesCourseMemberPeriod($plan, $course);
        }

        return false;
    }

    /**
     * Dernière ligne d’abonnement (id décroissant) dont le plan couvre le cours.
     */
    public static function findGrantingSubscriptionId(int $userId, Course $course): ?int
    {
        $subscriptions = UserSubscription::query()
            ->where('user_id', $userId)
            ->with('plan')
            ->orderByDesc('id')
            ->get();

        foreach ($subscriptions as $sub) {
            $plan = $sub->plan;
            if (! $plan) {
                continue;
            }
            if (self::planGrantsContent($plan, $course)) {
                return (int) $sub->id;
            }
        }

        return null;
    }
}
