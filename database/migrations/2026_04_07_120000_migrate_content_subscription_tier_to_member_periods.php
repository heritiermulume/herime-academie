<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'starter' => 'quarterly',
            'pro' => 'semiannual',
            'enterprise' => 'yearly',
        ];

        foreach ($map as $from => $to) {
            DB::table('contents')
                ->where('requires_subscription', true)
                ->where('required_subscription_tier', $from)
                ->update(['required_subscription_tier' => $to]);
        }
    }

    public function down(): void
    {
        $map = [
            'quarterly' => 'starter',
            'semiannual' => 'pro',
            'yearly' => 'enterprise',
        ];

        foreach ($map as $from => $to) {
            DB::table('contents')
                ->where('requires_subscription', true)
                ->where('required_subscription_tier', $from)
                ->update(['required_subscription_tier' => $to]);
        }
    }
};
