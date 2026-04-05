<?php

use App\Models\SubscriptionPlan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SubscriptionPlan::query()
            ->whereNotIn('slug', SubscriptionPlan::memberCommunitySlugList())
            ->update(['is_active' => false]);
    }

    public function down(): void {}
};
