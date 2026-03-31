<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $firstPublishedCourseId = Course::query()
            ->where('is_published', true)
            ->orderBy('id')
            ->value('id');

        $plans = [
            [
                'name' => 'Abonnement Mensuel',
                'slug' => 'abonnement-mensuel',
                'description' => 'Acces premium mensuel a l ensemble du catalogue premium.',
                'plan_type' => 'recurring',
                'billing_period' => 'monthly',
                'price' => 10.00,
                'annual_discount_percent' => 0,
                'trial_days' => 7,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => ['label' => '10$/mois'],
            ],
            [
                'name' => 'Abonnement Annuel',
                'slug' => 'abonnement-annuel',
                'description' => 'Abonnement annuel avec reduction automatique.',
                'plan_type' => 'recurring',
                'billing_period' => 'yearly',
                'price' => 120.00,
                'annual_discount_percent' => 20.00,
                'trial_days' => 14,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => ['label' => 'Annuel -20%'],
            ],
            [
                'name' => 'Freemium',
                'slug' => 'freemium',
                'description' => 'Acces gratuit au contenu public, passage premium a la demande.',
                'plan_type' => 'freemium',
                'billing_period' => null,
                'price' => 0.00,
                'annual_discount_percent' => 0,
                'trial_days' => 0,
                'is_active' => true,
                'auto_renew_default' => false,
                'content_id' => null,
                'metadata' => ['label' => 'Gratuit + Premium'],
            ],
            [
                'name' => 'Formation Achat Unique',
                'slug' => 'formation-achat-unique',
                'description' => 'Paiement unique pour une formation specifique.',
                'plan_type' => 'one_time',
                'billing_period' => null,
                'price' => 49.00,
                'annual_discount_percent' => 0,
                'trial_days' => 0,
                'is_active' => true,
                'auto_renew_default' => false,
                'content_id' => $firstPublishedCourseId,
                'metadata' => ['label' => 'Acces unique'],
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

