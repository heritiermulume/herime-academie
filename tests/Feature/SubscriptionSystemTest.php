<?php

namespace Tests\Feature;

use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\Category;
use App\Models\ContentPackage;
use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\AdminSubscriptionActivated;
use App\Notifications\SubscriptionActivated;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SubscriptionSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_localized_pricing_is_used_for_user_currency(): void
    {
        $plan = new SubscriptionPlan([
            'price' => 100,
            'annual_discount_percent' => 0,
            'metadata' => [
                'localized_pricing' => [
                    'CDF' => ['amount' => 250000],
                ],
            ],
        ]);

        $this->assertSame(250000.0, $plan->effectivePriceForCurrency('CDF'));
        $this->assertSame(100.0, $plan->effectivePriceForCurrency('USD'));
    }

    public function test_trial_subscription_renews_and_generates_invoice(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Pro Monthly',
            'slug' => 'pro-monthly-test',
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 20,
            'trial_days' => 7,
            'is_active' => true,
            'auto_renew_default' => true,
            'metadata' => ['localized_pricing' => ['USD' => ['amount' => 20]]],
        ]);

        $service = app(SubscriptionService::class);
        $subscription = $service->subscribe($user, $plan, 'moneroo');

        $this->assertSame('trialing', $subscription->status);
        $this->assertNotNull($subscription->trial_ends_at);
        $this->assertDatabaseCount('subscription_invoices', 0);

        $this->travelTo($subscription->current_period_ends_at->copy()->addMinute());
        $service->processRenewals();

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertDatabaseHas('subscription_invoices', [
            'user_subscription_id' => $subscription->id,
            'status' => 'pending',
            'currency' => 'USD',
        ]);
    }

    public function test_invoice_payment_flow_updates_from_moneroo_webhook(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Starter',
            'slug' => 'starter-test',
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 10,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'past_due',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);

        $invoice = SubscriptionInvoice::create([
            'invoice_number' => 'SUB-T-001',
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => ['id' => 'py_test_123', 'payment_url' => 'https://pay.example/redirect'],
            ], 200),
        ]);

        $this->actingAs($user)
            ->post(route('subscriptions.invoices.pay', $invoice))
            ->assertRedirect('https://pay.example/redirect');

        $payload = [
            'data' => [
                'id' => 'py_test_123',
                'status' => 'success',
                'metadata' => [
                    'kind' => 'subscription_invoice',
                    'invoice_id' => (string) $invoice->id,
                ],
            ],
        ];

        $this->postJson('/moneroo/webhook', $payload)->assertOk();

        $invoice->refresh();
        $subscription->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('active', $subscription->status);
    }

    public function test_subscription_invoice_payment_grants_access_to_linked_contents(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Dev',
            'slug' => 'dev-' . uniqid(),
        ]);

        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours abonnement',
            'slug' => 'cours-abonnement-' . uniqid(),
            'description' => 'desc',
            'price' => 50,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan contenu lié',
            'slug' => 'plan-lie-' . uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 10,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);
        $plan->contents()->sync([$course->id]);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'past_due',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);

        $invoice = SubscriptionInvoice::create([
            'invoice_number' => 'SUB-T-LINK',
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $payload = [
            'data' => [
                'id' => 'py_sub_link',
                'status' => 'success',
                'metadata' => [
                    'kind' => 'subscription_invoice',
                    'invoice_id' => (string) $invoice->id,
                ],
            ],
        ];

        $this->postJson('/moneroo/webhook', $payload)->assertOk();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'content_id' => $course->id,
            'status' => 'active',
        ]);
    }

    public function test_pack_purchase_is_recorded_in_order_items(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Backend',
            'slug' => 'backend-' . uniqid(),
        ]);

        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours pack',
            'slug' => 'cours-pack-' . uniqid(),
            'description' => 'desc',
            'price' => 80,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $package = ContentPackage::create([
            'title' => 'Pack Test',
            'slug' => 'pack-test-' . uniqid(),
            'price' => 60,
            'is_published' => true,
            'is_sale_enabled' => true,
        ]);
        $package->contents()->sync([$course->id]);

        $order = Order::create([
            'order_number' => 'ORD-PACK-' . strtoupper(substr(uniqid(), -6)),
            'user_id' => $user->id,
            'subtotal' => 60,
            'total' => 60,
            'total_amount' => 60,
            'currency' => 'USD',
            'status' => 'paid',
            'payment_method' => 'moneroo',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'content_id' => $course->id,
            'content_package_id' => $package->id,
            'price' => 60,
            'sale_price' => null,
            'total' => 60,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'content_id' => $course->id,
            'content_package_id' => $package->id,
        ]);

        $this->assertTrue($user->hasPurchasedContentPackage($package));
    }

    public function test_subscription_notifications_include_contents_and_packages_in_payload(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Notifications',
            'slug' => 'notifications-' . uniqid(),
        ]);

        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours notif abonnement',
            'slug' => 'cours-notif-abonnement-' . uniqid(),
            'description' => 'desc',
            'price' => 45,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $package = ContentPackage::create([
            'title' => 'Pack notif abonnement',
            'slug' => 'pack-notif-abonnement-' . uniqid(),
            'price' => 90,
            'is_published' => true,
            'is_sale_enabled' => true,
        ]);
        $package->contents()->sync([$course->id]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan notif',
            'slug' => 'plan-notif-' . uniqid(),
            'plan_type' => 'one_time',
            'billing_period' => null,
            'price' => 90,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => false,
            'metadata' => [
                'included_package_ids' => [$package->id],
            ],
        ]);
        $plan->contents()->sync([$course->id]);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => false,
            'payment_method' => 'moneroo',
        ]);

        $invoice = SubscriptionInvoice::create([
            'invoice_number' => 'SUB-NOTIF-' . strtoupper(substr(uniqid(), -6)),
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 90,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $customerPayload = (new SubscriptionActivated($subscription, $invoice))->toArray($user);
        $adminPayload = (new AdminSubscriptionActivated($subscription, $invoice))->toArray($user);

        $this->assertContains($course->title, $customerPayload['included_contents']);
        $this->assertContains($package->title, $customerPayload['included_packages']);
        $this->assertContains($course->title, $adminPayload['included_contents']);
        $this->assertContains($package->title, $adminPayload['included_packages']);
    }
}

