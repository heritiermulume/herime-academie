<?php

namespace Tests\Feature\Admin;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\AdminSubscriptionCancelled;
use App\Notifications\SubscriptionCancelled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminCancelUserSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_cancel_active_user_subscription(): void
    {
        Notification::fake();

        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan admin membership cancel',
            'slug' => 'plan-admin-mc-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 12,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $customer->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.subscriptions.index'))
            ->post(route('admin.subscriptions.memberships.cancel', $subscription))
            ->assertRedirect(route('admin.subscriptions.index'));

        $subscription->refresh();
        $this->assertSame('cancelled', $subscription->status);
        $this->assertFalse($subscription->auto_renew);

        Notification::assertSentTo($customer, SubscriptionCancelled::class);
        Notification::assertSentTo($this->admin, AdminSubscriptionCancelled::class);
    }

    public function test_admin_cannot_cancel_already_cancelled_subscription(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan x',
            'slug' => 'plan-x-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 1,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $customer->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'cancelled',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addDay(),
            'auto_renew' => false,
            'payment_method' => 'moneroo',
            'cancelled_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.subscriptions.index'))
            ->post(route('admin.subscriptions.memberships.cancel', $subscription))
            ->assertRedirect(route('admin.subscriptions.index'))
            ->assertSessionHas('error');
    }
}
