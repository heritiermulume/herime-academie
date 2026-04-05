<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $memberPlanType = Schema::getConnection()->getDriverName() === 'mysql' ? 'membre' : 'recurring';

        $plans = [
            [
                'name' => 'Réseau Membre Herime — Trimestriel',
                'slug' => 'membre-herime-trimestriel',
                'description' => 'Communauté privée Membre Herime, formations, réseau, lives et templates premium (facturation tous les 3 mois).',
                'plan_type' => $memberPlanType,
                'billing_period' => 'quarterly',
                'price' => 89.00,
                'annual_discount_percent' => 0,
                'trial_days' => 0,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => [
                    'community_premium' => true,
                    'community_display_order' => 0,
                    'label' => 'Trimestriel',
                    'community_card_popular' => false,
                ],
            ],
            [
                'name' => 'Réseau Membre Herime — Semestriel',
                'slug' => 'membre-herime-semestriel',
                'description' => 'Communauté privée Membre Herime, formations, réseau, lives et templates premium (facturation tous les 6 mois).',
                'plan_type' => $memberPlanType,
                'billing_period' => 'semiannual',
                'price' => 149.00,
                'annual_discount_percent' => 0,
                'trial_days' => 0,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => [
                    'community_premium' => true,
                    'community_display_order' => 1,
                    'label' => 'Semestriel',
                    'community_card_popular' => false,
                ],
            ],
            [
                'name' => 'Réseau Membre Herime — Annuel',
                'slug' => 'membre-herime-annuel',
                'description' => 'Communauté privée Membre Herime, formations, réseau, lives et templates premium (facturation annuelle).',
                'plan_type' => $memberPlanType,
                'billing_period' => 'yearly',
                'price' => 249.00,
                'annual_discount_percent' => 0,
                'trial_days' => 0,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => [
                    'community_premium' => true,
                    'community_display_order' => 2,
                    'label' => 'Annuel',
                    'community_card_popular' => true,
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
