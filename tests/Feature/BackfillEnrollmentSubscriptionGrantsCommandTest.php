<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackfillEnrollmentSubscriptionGrantsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_backfill_sets_subscription_grant_when_eligible(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Backfill',
            'slug' => 'backfill-'.uniqid(),
        ]);
        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours BF',
            'slug' => 'cours-bf-'.uniqid(),
            'description' => 'd',
            'price' => 10,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan BF',
            'slug' => 'plan-bf-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 5,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);
        $plan->contents()->sync([$course->id]);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'content_id' => $course->id,
            'order_id' => null,
            'access_granted_by_subscription_id' => null,
            'status' => 'active',
            'progress' => 0,
        ]);

        $this->artisan('enrollments:backfill-subscription-grants')->assertSuccessful();

        $enrollment->refresh();
        $this->assertSame($subscription->id, (int) $enrollment->access_granted_by_subscription_id);
    }

    public function test_backfill_dry_run_does_not_write(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'BF dry',
            'slug' => 'bf-dry-'.uniqid(),
        ]);
        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours BF dry',
            'slug' => 'cours-bf-dry-'.uniqid(),
            'description' => 'd',
            'price' => 10,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan BF dry',
            'slug' => 'plan-bf-dry-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 5,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);
        $plan->contents()->sync([$course->id]);

        UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'content_id' => $course->id,
            'order_id' => null,
            'access_granted_by_subscription_id' => null,
            'status' => 'active',
            'progress' => 0,
        ]);

        $this->artisan('enrollments:backfill-subscription-grants', ['--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'content_id' => $course->id,
            'access_granted_by_subscription_id' => null,
        ]);
    }

    public function test_backfill_skips_when_standalone_purchase_exists_without_order_on_enrollment(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'BF purchase',
            'slug' => 'bf-purchase-'.uniqid(),
        ]);
        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours BF purchase',
            'slug' => 'cours-bf-purchase-'.uniqid(),
            'description' => 'd',
            'price' => 10,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan BF purchase',
            'slug' => 'plan-bf-purchase-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 5,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);
        $plan->contents()->sync([$course->id]);

        UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-BF-'.strtoupper(substr(uniqid(), -6)),
            'user_id' => $user->id,
            'subtotal' => 10,
            'total' => 10,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'paid',
            'payment_method' => 'moneroo',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'content_id' => $course->id,
            'content_package_id' => null,
            'price' => 10,
            'sale_price' => null,
            'total' => 10,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'content_id' => $course->id,
            'order_id' => null,
            'access_granted_by_subscription_id' => null,
            'status' => 'active',
            'progress' => 0,
        ]);

        $this->artisan('enrollments:backfill-subscription-grants')->assertSuccessful();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'content_id' => $course->id,
            'access_granted_by_subscription_id' => null,
        ]);
    }
}
