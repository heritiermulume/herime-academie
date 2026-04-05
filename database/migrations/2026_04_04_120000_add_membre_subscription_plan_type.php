<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $memberSlugs = ['membre-herime-trimestriel', 'membre-herime-semestriel', 'membre-herime-annuel'];

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE subscription_plans MODIFY COLUMN plan_type ENUM('recurring', 'one_time', 'freemium', 'premium', 'membre') NOT NULL DEFAULT 'recurring'");
            DB::table('subscription_plans')
                ->whereIn('slug', $memberSlugs)
                ->update(['plan_type' => 'membre']);
        }

        // SQLite : CHECK héritée des migrations (souvent sans quarterly) — pas d’insert ici ; MySQL : slug + trimestre.
        if ($driver === 'mysql' && ! DB::table('subscription_plans')->where('slug', 'membre-herime-trimestriel')->exists()) {
            DB::table('subscription_plans')->insert([
                'name' => 'Réseau Membre Herime — Trimestriel',
                'slug' => 'membre-herime-trimestriel',
                'description' => 'Communauté privée Membre Herime, formations, réseau, lives et templates premium (facturation tous les 3 mois).',
                'plan_type' => 'membre',
                'billing_period' => 'quarterly',
                'price' => 89.00,
                'annual_discount_percent' => 0,
                'trial_days' => 0,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => json_encode([
                    'community_premium' => true,
                    'community_display_order' => 0,
                    'label' => 'Trimestriel',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('subscription_plans')
                ->whereIn('slug', ['membre-herime-trimestriel', 'membre-herime-semestriel', 'membre-herime-annuel'])
                ->update(['plan_type' => 'recurring']);
            DB::statement("ALTER TABLE subscription_plans MODIFY COLUMN plan_type ENUM('recurring', 'one_time', 'freemium', 'premium') NOT NULL DEFAULT 'recurring'");
        }
    }
};
