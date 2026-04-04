<?php

namespace App\Models\Pivots;

use App\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ContentPackageContentPivot extends Pivot
{
    protected $table = 'content_package_content';

    protected static function booted(): void
    {
        static::created(function (ContentPackageContentPivot $pivot) {
            SubscriptionService::deferEntitlementSyncForContentPackage((int) $pivot->content_package_id);
        });

        static::deleted(function (ContentPackageContentPivot $pivot) {
            SubscriptionService::deferEntitlementSyncForContentPackage((int) $pivot->content_package_id);
        });
    }
}
