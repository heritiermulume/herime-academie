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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
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
        $service->processRenewalsForUser($user->id);

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

    public function test_included_package_ids_create_enrollments_for_pack_contents(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Packs abo',
            'slug' => 'packs-abo-' . uniqid(),
        ]);

        $coursePublished = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours pack publié',
            'slug' => 'cours-pack-pub-' . uniqid(),
            'description' => 'desc',
            'price' => 10,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $courseDraft = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours brouillon dans pack',
            'slug' => 'cours-pack-draft-' . uniqid(),
            'description' => 'desc',
            'price' => 10,
            'is_free' => false,
            'is_published' => false,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $package = ContentPackage::create([
            'title' => 'Pack inclus abo',
            'slug' => 'pack-inclus-abo-' . uniqid(),
            'price' => 50,
            'is_published' => true,
            'is_sale_enabled' => true,
        ]);
        $package->contents()->sync([$coursePublished->id, $courseDraft->id]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan avec pack',
            'slug' => 'plan-avec-pack-' . uniqid(),
            'plan_type' => 'one_time',
            'billing_period' => null,
            'price' => 50,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => false,
            'metadata' => [
                'included_package_ids' => [$package->id],
            ],
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addYear(),
            'auto_renew' => false,
            'payment_method' => 'moneroo',
        ]);

        app(SubscriptionService::class)->grantLinkedContentAccess($subscription);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'content_id' => $coursePublished->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user->id,
            'content_id' => $courseDraft->id,
        ]);
    }

    public function test_plan_contents_sync_updates_all_subscribers_via_flush(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Sync plan',
            'slug' => 'sync-plan-' . uniqid(),
        ]);

        $courseA = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours A',
            'slug' => 'cours-a-' . uniqid(),
            'description' => 'd',
            'price' => 10,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $courseB = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours B',
            'slug' => 'cours-b-' . uniqid(),
            'description' => 'd',
            'price' => 10,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan sync',
            'slug' => 'plan-sync-' . uniqid(),
            'plan_type' => 'one_time',
            'billing_period' => null,
            'price' => 1,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => false,
            'metadata' => ['included_package_ids' => []],
        ]);
        UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addYear(),
            'auto_renew' => false,
            'payment_method' => 'moneroo',
        ]);

        $plan->contents()->sync([$courseA->id]);
        SubscriptionService::flushDeferredEntitlementSyncs();

        $plan->contents()->sync([$courseA->id, $courseB->id]);
        SubscriptionService::flushDeferredEntitlementSyncs();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'content_id' => $courseB->id,
            'status' => 'active',
        ]);
    }

    public function test_package_contents_sync_updates_subscribers_of_plans_referencing_package(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Sync pack',
            'slug' => 'sync-pack-' . uniqid(),
        ]);

        $courseInPack = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Dans pack après sync',
            'slug' => 'dans-pack-' . uniqid(),
            'description' => 'd',
            'price' => 10,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $package = ContentPackage::create([
            'title' => 'Pack ref plan',
            'slug' => 'pack-ref-plan-' . uniqid(),
            'price' => 20,
            'is_published' => true,
            'is_sale_enabled' => true,
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan avec pack ref',
            'slug' => 'plan-pack-ref-' . uniqid(),
            'plan_type' => 'one_time',
            'billing_period' => null,
            'price' => 5,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => false,
            'metadata' => ['included_package_ids' => [$package->id]],
        ]);

        UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addYear(),
            'auto_renew' => false,
            'payment_method' => 'moneroo',
        ]);

        SubscriptionService::flushDeferredEntitlementSyncs();

        $package->contents()->sync([$courseInPack->id]);
        SubscriptionService::flushDeferredEntitlementSyncs();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'content_id' => $courseInPack->id,
            'status' => 'active',
        ]);
    }

    public function test_paid_subscribe_defers_activation_notifications_until_checkout(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'customer',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Payant mensuel',
            'slug' => 'payant-mensuel-' . uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 15,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => ['id' => 'py_init_1', 'payment_url' => 'https://pay.example/checkout'],
            ], 200),
        ]);

        config(['services.moneroo.api_key' => 'test_key']);

        $this->actingAs($user)
            ->post(route('subscriptions.subscribe', $plan))
            ->assertRedirect('https://pay.example/checkout');

        $subscription = UserSubscription::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($subscription);
        $this->assertSame('pending_payment', $subscription->status);

        Notification::assertNotSentTo($user, SubscriptionActivated::class);
        Notification::assertNotSentTo($user, \App\Notifications\SubscriptionInvoiceIssued::class);
    }

    public function test_webhook_paid_activates_pending_payment_and_sends_subscription_activated(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'customer',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan pending',
            'slug' => 'plan-pending-' . uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 12,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'pending_payment',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);

        $invoice = SubscriptionInvoice::create([
            'invoice_number' => 'SUB-PEND-001',
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 12,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $payload = [
            'data' => [
                'id' => 'py_webhook_pend',
                'status' => 'success',
                'metadata' => [
                    'kind' => 'subscription_invoice',
                    'invoice_id' => (string) $invoice->id,
                ],
            ],
        ];

        $this->postJson('/moneroo/webhook', $payload)->assertOk();

        $subscription->refresh();
        $invoice->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertSame('paid', $invoice->status);

        Notification::assertSentTo($user, SubscriptionActivated::class);
    }

    public function test_full_paid_subscribe_grants_content_only_after_webhook_confirms_payment(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Full flow',
            'slug' => 'full-flow-' . uniqid(),
        ]);

        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours cycle complet',
            'slug' => 'cours-cycle-complet-' . uniqid(),
            'description' => 'desc',
            'price' => 40,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan cycle complet',
            'slug' => 'plan-cycle-' . uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 18,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);
        $plan->contents()->sync([$course->id]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => ['id' => 'py_full_1', 'payment_url' => 'https://pay.example/full'],
            ], 200),
        ]);
        config(['services.moneroo.api_key' => 'test_key']);

        $this->actingAs($user)
            ->post(route('subscriptions.subscribe', $plan))
            ->assertRedirect('https://pay.example/full');

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user->id,
            'content_id' => $course->id,
        ]);

        $invoice = SubscriptionInvoice::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($invoice);
        $this->assertSame('pending', $invoice->status);

        $payload = [
            'data' => [
                'id' => 'py_full_1',
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

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
    }

    public function test_process_renewals_creates_invoice_and_extends_billing_period(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $plan = SubscriptionPlan::create([
            'name' => 'Mensuel renouv',
            'slug' => 'mensuel-renouv-' . uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 22,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        $periodEnd = now()->subHour();
        $periodStart = $periodEnd->copy()->subMonth();

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => $periodStart,
            'trial_ends_at' => null,
            'current_period_starts_at' => $periodStart,
            'current_period_ends_at' => $periodEnd,
            'auto_renew' => true,
            'payment_method' => 'moneroo',
            'metadata' => ['currency' => 'USD', 'amount' => 22],
        ]);

        $service = app(SubscriptionService::class);
        $processed = $service->processRenewalsForUser($user->id);

        $this->assertGreaterThanOrEqual(1, $processed);

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertTrue($subscription->current_period_ends_at->greaterThan($periodEnd));
        $this->assertSame(
            $periodEnd->copy()->addMonth()->format('Y-m-d'),
            $subscription->current_period_ends_at->format('Y-m-d')
        );

        $this->assertDatabaseHas('subscription_invoices', [
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_process_renewals_with_recent_unpaid_invoice_sets_past_due(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan retard',
            'slug' => 'plan-retard-' . uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 30,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        $periodEnd = now()->subMinutes(30);
        $periodStart = $periodEnd->copy()->subMonth();

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => $periodStart,
            'trial_ends_at' => null,
            'current_period_starts_at' => $periodStart,
            'current_period_ends_at' => $periodEnd,
            'auto_renew' => true,
            'payment_method' => 'moneroo',
            'metadata' => ['currency' => 'USD', 'amount' => 30],
        ]);

        SubscriptionInvoice::create([
            'invoice_number' => 'SUB-PEND-RENEW',
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 30,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
            'created_at' => now()->subDays(2),
        ]);

        $invoiceCountBefore = SubscriptionInvoice::query()->where('user_subscription_id', $subscription->id)->count();

        $service = app(SubscriptionService::class);
        $service->processRenewalsForUser($user->id);

        $subscription->refresh();
        $this->assertSame('past_due', $subscription->status);
        $this->assertTrue($subscription->current_period_ends_at->greaterThan($periodEnd));

        $invoiceCountAfter = SubscriptionInvoice::query()->where('user_subscription_id', $subscription->id)->count();
        $this->assertSame($invoiceCountBefore, $invoiceCountAfter);
    }

    public function test_subscribe_after_cancelled_reactivates_same_row(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $plan = SubscriptionPlan::create([
            'name' => 'Réabo test',
            'slug' => 'reabo-test-' . uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 9,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'cancelled',
            'starts_at' => now()->subMonth(),
            'trial_ends_at' => null,
            'current_period_starts_at' => now()->subMonth(),
            'current_period_ends_at' => now()->addMonth(),
            'cancelled_at' => now(),
            'ended_at' => now()->addMonth(),
            'auto_renew' => false,
            'payment_method' => 'moneroo',
            'metadata' => ['currency' => 'USD', 'amount' => 9],
        ]);

        $idBefore = $subscription->id;

        $service = app(SubscriptionService::class);
        $returned = $service->subscribe($user, $plan, 'moneroo');

        $this->assertSame($idBefore, $returned->id);
        $returned->refresh();
        $this->assertSame('active', $returned->status);
        $this->assertNull($returned->cancelled_at);
        $this->assertNull($returned->ended_at);
        $this->assertTrue($returned->auto_renew);
    }

    public function test_process_renewals_for_user_does_not_touch_other_users_subscriptions(): void
    {
        $userA = User::factory()->create(['role' => 'customer']);
        $userB = User::factory()->create(['role' => 'customer']);

        $plan = SubscriptionPlan::create([
            'name' => 'Iso user',
            'slug' => 'iso-user-' . uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 11,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        $periodEnd = now()->subHour();
        $periodStart = $periodEnd->copy()->subMonth();

        foreach ([$userA, $userB] as $user) {
            UserSubscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $periodStart,
                'trial_ends_at' => null,
                'current_period_starts_at' => $periodStart,
                'current_period_ends_at' => $periodEnd,
                'auto_renew' => true,
                'payment_method' => 'moneroo',
                'metadata' => ['currency' => 'USD', 'amount' => 11],
            ]);
        }

        app(SubscriptionService::class)->processRenewalsForUser($userA->id);

        $subA = UserSubscription::query()->where('user_id', $userA->id)->first();
        $subB = UserSubscription::query()->where('user_id', $userB->id)->first();

        $this->assertTrue($subA->fresh()->current_period_ends_at->greaterThan($periodEnd));
        $this->assertSame(
            $periodEnd->copy()->utc()->format('Y-m-d H:i:s'),
            $subB->fresh()->current_period_ends_at->copy()->utc()->format('Y-m-d H:i:s'),
            'L’abonnement de l’autre utilisateur ne doit pas être renouvelé.'
        );
    }

    public function test_customer_get_request_triggers_subscription_renewal_middleware(): void
    {
        Cache::flush();

        $user = User::factory()->create(['role' => 'customer']);

        $plan = SubscriptionPlan::create([
            'name' => 'Middleware renew',
            'slug' => 'mw-renew-' . uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 19,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        $periodEnd = now()->subMinutes(5);
        $periodStart = $periodEnd->copy()->subMonth();

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => $periodStart,
            'trial_ends_at' => null,
            'current_period_starts_at' => $periodStart,
            'current_period_ends_at' => $periodEnd,
            'auto_renew' => true,
            'payment_method' => 'moneroo',
            'metadata' => ['currency' => 'USD', 'amount' => 19],
        ]);

        $this->actingAs($user)->get(route('customer.dashboard'))->assertOk();

        $subscription->refresh();
        $this->assertTrue($subscription->current_period_ends_at->greaterThan($periodEnd));
    }
}

