<?php

namespace App\Models\Pivots;

use App\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SubscriptionPlanContentPivot extends Pivot
{
    protected $table = 'subscription_plan_content';

    protected static function booted(): void
    {
        static::created(function (SubscriptionPlanContentPivot $pivot) {
            SubscriptionService::deferEntitlementSyncForPlan((int) $pivot->subscription_plan_id);
        });

        static::deleted(function (SubscriptionPlanContentPivot $pivot) {
            SubscriptionService::deferEntitlementSyncForPlan((int) $pivot->subscription_plan_id);
        });
    }
}
