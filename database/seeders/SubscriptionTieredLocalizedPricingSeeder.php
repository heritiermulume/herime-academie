<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionTieredLocalizedPricingSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter Monthly',
                'slug' => 'starter-monthly',
                'description' => 'Starter plan with essential premium access.',
                'plan_type' => 'recurring',
                'billing_period' => 'monthly',
                'price' => 9.99,
                'annual_discount_percent' => 0,
                'trial_days' => 7,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => [
                    'tier' => 'starter',
                    'localized_pricing' => [
                        'USD' => ['amount' => 9.99, 'period' => 'monthly'],
                        'CDF' => ['amount' => 27900, 'period' => 'monthly'],
                        'XOF' => ['amount' => 6000, 'period' => 'monthly'],
                    ],
                    'regions' => ['global', 'cd', 'waemu'],
                ],
            ],
            [
                'name' => 'Pro Monthly',
                'slug' => 'pro-monthly',
                'description' => 'Pro plan for power users and professionals.',
                'plan_type' => 'recurring',
                'billing_period' => 'monthly',
                'price' => 19.99,
                'annual_discount_percent' => 0,
                'trial_days' => 14,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => [
                    'tier' => 'pro',
                    'localized_pricing' => [
                        'USD' => ['amount' => 19.99, 'period' => 'monthly'],
                        'CDF' => ['amount' => 55900, 'period' => 'monthly'],
                        'XOF' => ['amount' => 12000, 'period' => 'monthly'],
                    ],
                    'regions' => ['global', 'cd', 'waemu'],
                ],
            ],
            [
                'name' => 'Enterprise Monthly',
                'slug' => 'enterprise-monthly',
                'description' => 'Enterprise plan with advanced team features.',
                'plan_type' => 'recurring',
                'billing_period' => 'monthly',
                'price' => 49.99,
                'annual_discount_percent' => 0,
                'trial_days' => 14,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => [
                    'tier' => 'enterprise',
                    'localized_pricing' => [
                        'USD' => ['amount' => 49.99, 'period' => 'monthly'],
                        'CDF' => ['amount' => 139900, 'period' => 'monthly'],
                        'XOF' => ['amount' => 30000, 'period' => 'monthly'],
                    ],
                    'regions' => ['global', 'cd', 'waemu'],
                ],
            ],
            [
                'name' => 'Starter Annual',
                'slug' => 'starter-annual',
                'description' => 'Starter yearly plan with discount.',
                'plan_type' => 'recurring',
                'billing_period' => 'yearly',
                'price' => 119.88,
                'annual_discount_percent' => 20,
                'trial_days' => 14,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => [
                    'tier' => 'starter',
                    'localized_pricing' => [
                        'USD' => ['amount' => 95.90, 'period' => 'yearly'],
                        'CDF' => ['amount' => 268000, 'period' => 'yearly'],
                        'XOF' => ['amount' => 57500, 'period' => 'yearly'],
                    ],
                    'regions' => ['global', 'cd', 'waemu'],
                ],
            ],
            [
                'name' => 'Pro Annual',
                'slug' => 'pro-annual',
                'description' => 'Pro yearly plan with discount.',
                'plan_type' => 'recurring',
                'billing_period' => 'yearly',
                'price' => 239.88,
                'annual_discount_percent' => 20,
                'trial_days' => 14,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => [
                    'tier' => 'pro',
                    'localized_pricing' => [
                        'USD' => ['amount' => 191.90, 'period' => 'yearly'],
                        'CDF' => ['amount' => 536000, 'period' => 'yearly'],
                        'XOF' => ['amount' => 115000, 'period' => 'yearly'],
                    ],
                    'regions' => ['global', 'cd', 'waemu'],
                ],
            ],
            [
                'name' => 'Enterprise Annual',
                'slug' => 'enterprise-annual',
                'description' => 'Enterprise yearly plan with discount.',
                'plan_type' => 'recurring',
                'billing_period' => 'yearly',
                'price' => 599.88,
                'annual_discount_percent' => 20,
                'trial_days' => 30,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => [
                    'tier' => 'enterprise',
                    'localized_pricing' => [
                        'USD' => ['amount' => 479.90, 'period' => 'yearly'],
                        'CDF' => ['amount' => 1340000, 'period' => 'yearly'],
                        'XOF' => ['amount' => 288000, 'period' => 'yearly'],
                    ],
                    'regions' => ['global', 'cd', 'waemu'],
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

