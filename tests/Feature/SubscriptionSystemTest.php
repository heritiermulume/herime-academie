<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ContentPackage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\AdminSubscriptionActivated;
use App\Notifications\AdminSubscriptionAutoRenewResumed;
use App\Notifications\OrderStatusUpdated;
use App\Notifications\SubscriptionActivated;
use App\Notifications\SubscriptionAutoRenewResumed;
use App\Notifications\SubscriptionInvoiceCancelled;
use App\Notifications\SubscriptionInvoiceFailed;
use App\Services\OrderEnrollmentService;
use App\Services\SubscriptionCheckoutOrderService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
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
        $this->assertSame('past_due', $subscription->status);
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

    public function test_second_checkout_for_same_invoice_cancels_prior_pending_order(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Retry plan',
            'slug' => 'retry-plan-'.uniqid(),
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
            'invoice_number' => 'SUB-RETRY-001',
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $initCount = 0;
        Http::fake(function () use (&$initCount) {
            $initCount++;
            $suffix = $initCount === 1 ? 'first' : 'second';

            return Http::response([
                'success' => true,
                'data' => [
                    'id' => 'py_'.$suffix,
                    'payment_url' => 'https://pay.example/'.$suffix,
                ],
            ], 200);
        });
        config(['services.moneroo.api_key' => 'test_key']);

        $this->actingAs($user)
            ->post(route('subscriptions.invoices.pay', $invoice))
            ->assertRedirect('https://pay.example/first');

        $orderFirst = Order::query()->where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($orderFirst);
        $this->assertSame('pending', $orderFirst->status);
        $this->assertTrue(
            Payment::query()->where('order_id', $orderFirst->id)->where('status', 'pending')->exists()
        );

        $this->actingAs($user)
            ->post(route('subscriptions.invoices.pay', $invoice))
            ->assertRedirect('https://pay.example/second');

        $orderFirst->refresh();
        $this->assertSame('cancelled', $orderFirst->status);
        $this->assertTrue(
            Payment::query()->where('order_id', $orderFirst->id)->where('status', 'failed')->exists()
        );

        $orderSecond = Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();
        $this->assertNotNull($orderSecond);
        $this->assertNotSame($orderFirst->id, $orderSecond->id);
        $this->assertTrue(
            Payment::query()->where('order_id', $orderSecond->id)->where('status', 'pending')->exists()
        );

        $invoice->refresh();
        $this->assertSame($orderSecond->id, (int) data_get($invoice->metadata, 'order_id'));
    }

    public function test_subscription_invoice_payment_grants_access_to_linked_contents(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Dev',
            'slug' => 'dev-'.uniqid(),
        ]);

        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours abonnement',
            'slug' => 'cours-abonnement-'.uniqid(),
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
            'slug' => 'plan-lie-'.uniqid(),
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
            'slug' => 'backend-'.uniqid(),
        ]);

        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours pack',
            'slug' => 'cours-pack-'.uniqid(),
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
            'slug' => 'pack-test-'.uniqid(),
            'price' => 60,
            'is_published' => true,
            'is_sale_enabled' => true,
        ]);
        $package->contents()->sync([$course->id]);

        $order = Order::create([
            'order_number' => 'ORD-PACK-'.strtoupper(substr(uniqid(), -6)),
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
            'slug' => 'notifications-'.uniqid(),
        ]);

        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours notif abonnement',
            'slug' => 'cours-notif-abonnement-'.uniqid(),
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
            'slug' => 'pack-notif-abonnement-'.uniqid(),
            'price' => 90,
            'is_published' => true,
            'is_sale_enabled' => true,
        ]);
        $package->contents()->sync([$course->id]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan notif',
            'slug' => 'plan-notif-'.uniqid(),
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
            'invoice_number' => 'SUB-NOTIF-'.strtoupper(substr(uniqid(), -6)),
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
            'slug' => 'packs-abo-'.uniqid(),
        ]);

        $coursePublished = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours pack publié',
            'slug' => 'cours-pack-pub-'.uniqid(),
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
            'slug' => 'cours-pack-draft-'.uniqid(),
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
            'slug' => 'pack-inclus-abo-'.uniqid(),
            'price' => 50,
            'is_published' => true,
            'is_sale_enabled' => true,
        ]);
        $package->contents()->sync([$coursePublished->id, $courseDraft->id]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan avec pack',
            'slug' => 'plan-avec-pack-'.uniqid(),
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
            'slug' => 'sync-plan-'.uniqid(),
        ]);

        $courseA = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours A',
            'slug' => 'cours-a-'.uniqid(),
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
            'slug' => 'cours-b-'.uniqid(),
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
            'slug' => 'plan-sync-'.uniqid(),
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

    public function test_plan_contents_sync_does_not_grant_entitlements_to_past_due_subscribers(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Sync past due',
            'slug' => 'sync-past-due-'.uniqid(),
        ]);

        $courseA = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours PD A',
            'slug' => 'cours-pd-a-'.uniqid(),
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
            'title' => 'Cours PD B',
            'slug' => 'cours-pd-b-'.uniqid(),
            'description' => 'd',
            'price' => 10,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan sync PD',
            'slug' => 'plan-sync-pd-'.uniqid(),
            'plan_type' => 'one_time',
            'billing_period' => null,
            'price' => 1,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => false,
            'metadata' => ['included_package_ids' => []],
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'past_due',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addYear(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);

        SubscriptionInvoice::create([
            'invoice_number' => 'SUB-PD-'.uniqid(),
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $plan->contents()->sync([$courseA->id]);
        SubscriptionService::flushDeferredEntitlementSyncs();

        $plan->contents()->sync([$courseA->id, $courseB->id]);
        SubscriptionService::flushDeferredEntitlementSyncs();

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user->id,
            'content_id' => $courseA->id,
        ]);
        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user->id,
            'content_id' => $courseB->id,
        ]);
    }

    public function test_package_contents_sync_updates_subscribers_of_plans_referencing_package(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Sync pack',
            'slug' => 'sync-pack-'.uniqid(),
        ]);

        $courseInPack = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Dans pack après sync',
            'slug' => 'dans-pack-'.uniqid(),
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
            'slug' => 'pack-ref-plan-'.uniqid(),
            'price' => 20,
            'is_published' => true,
            'is_sale_enabled' => true,
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan avec pack ref',
            'slug' => 'plan-pack-ref-'.uniqid(),
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
            'name' => 'Réseau Membre Herime — Annuel',
            'slug' => SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'],
            'plan_type' => 'recurring',
            'billing_period' => 'yearly',
            'price' => 15,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
            'metadata' => [
                'community_premium' => true,
                'community_display_order' => 2,
            ],
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
            'slug' => 'plan-pending-'.uniqid(),
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

    public function test_invoice_return_redirects_to_community_when_return_to_is_community(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $plan = SubscriptionPlan::create([
            'name' => 'Plan retour',
            'slug' => 'plan-retour-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 5,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
        ]);
        $invoice = SubscriptionInvoice::create([
            'invoice_number' => 'SUB-RET-001',
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 5,
            'currency' => 'USD',
            'status' => 'paid',
            'due_at' => now()->addDay(),
            'paid_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('subscriptions.invoices.return', [
                'invoice' => $invoice->id,
                'return_to' => 'community',
            ]))
            ->assertRedirect(route('community.premium'));
    }

    public function test_webhook_paid_on_member_plan_expires_other_member_bundle_subscriptions(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'customer']);

        $planQuarterly = SubscriptionPlan::create([
            'name' => 'Réseau Membre Herime — Trimestriel',
            'slug' => SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'],
            'plan_type' => 'recurring',
            'billing_period' => 'quarterly',
            'price' => 10,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
            'metadata' => [
                'community_premium' => true,
                'community_display_order' => 0,
            ],
        ]);

        $planYearly = SubscriptionPlan::create([
            'name' => 'Réseau Membre Herime — Annuel',
            'slug' => SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'],
            'plan_type' => 'recurring',
            'billing_period' => 'yearly',
            'price' => 99,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
            'metadata' => [
                'community_premium' => true,
                'community_display_order' => 2,
            ],
        ]);

        $subQuarterly = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $planQuarterly->id,
            'status' => 'active',
            'starts_at' => now()->subMonth(),
            'trial_ends_at' => null,
            'current_period_starts_at' => now()->subMonth(),
            'current_period_ends_at' => now()->addMonths(2),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
            'metadata' => ['currency' => 'USD', 'amount' => 10],
        ]);

        $subYearly = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $planYearly->id,
            'status' => 'pending_payment',
            'starts_at' => now(),
            'trial_ends_at' => null,
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addYear(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
            'metadata' => ['currency' => 'USD', 'amount' => 99],
        ]);

        $invoice = SubscriptionInvoice::create([
            'invoice_number' => 'SUB-SWITCH-001',
            'user_subscription_id' => $subYearly->id,
            'user_id' => $user->id,
            'amount' => 99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $payload = [
            'data' => [
                'id' => 'py_webhook_switch',
                'status' => 'success',
                'metadata' => [
                    'kind' => 'subscription_invoice',
                    'invoice_id' => (string) $invoice->id,
                ],
            ],
        ];

        $this->postJson('/moneroo/webhook', $payload)->assertOk();

        $subQuarterly->refresh();
        $subYearly->refresh();
        $invoice->refresh();

        $this->assertSame('expired', $subQuarterly->status);
        $this->assertTrue($subQuarterly->ended_at !== null && $subQuarterly->ended_at->isPast());
        $this->assertSame('active', $subYearly->status);
        $this->assertSame('paid', $invoice->status);
        $this->assertSame((string) $subYearly->id, (string) data_get($subQuarterly->metadata, 'member_bundle_replaced_by_subscription_id'));
    }

    public function test_full_paid_subscribe_grants_content_only_after_webhook_confirms_payment(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Full flow',
            'slug' => 'full-flow-'.uniqid(),
        ]);

        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours cycle complet',
            'slug' => 'cours-cycle-complet-'.uniqid(),
            'description' => 'desc',
            'price' => 40,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'is_downloadable' => false,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Réseau Membre Herime — Annuel',
            'slug' => SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'],
            'plan_type' => 'recurring',
            'billing_period' => 'yearly',
            'price' => 18,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
            'metadata' => [
                'community_premium' => true,
                'community_display_order' => 2,
            ],
        ]);

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

    public function test_member_subscription_grants_downloadable_when_reserved_for_subscribers(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'DL membre',
            'slug' => 'dl-membre-'.uniqid(),
        ]);

        $downloadableForMembers = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Ressource PDF membres',
            'slug' => 'ressource-pdf-membres-'.uniqid(),
            'description' => 'd',
            'price' => 12,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'is_downloadable' => true,
            'requires_subscription' => true,
            'required_subscription_tier' => 'quarterly',
            'download_file_path' => 'courses/downloads/member-resource.zip',
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $downloadableStandaloneOnly = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Guide achat seul',
            'slug' => 'guide-achat-seul-'.uniqid(),
            'description' => 'd',
            'price' => 12,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'is_downloadable' => true,
            'requires_subscription' => false,
            'download_file_path' => 'courses/downloads/standalone-only.zip',
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Réseau Membre Herime — Annuel',
            'slug' => SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'],
            'plan_type' => 'recurring',
            'billing_period' => 'yearly',
            'price' => 18,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
            'metadata' => [
                'community_premium' => true,
                'community_display_order' => 2,
            ],
        ]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => ['id' => 'py_dl_member_1', 'payment_url' => 'https://pay.example/dl-member'],
            ], 200),
        ]);
        config(['services.moneroo.api_key' => 'test_key']);

        $this->actingAs($user)
            ->post(route('subscriptions.subscribe', $plan))
            ->assertRedirect('https://pay.example/dl-member');

        $invoice = SubscriptionInvoice::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($invoice);

        $payload = [
            'data' => [
                'id' => 'py_dl_member_1',
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
            'content_id' => $downloadableForMembers->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user->id,
            'content_id' => $downloadableStandaloneOnly->id,
        ]);
    }

    public function test_quarterly_member_subscription_skips_year_minimum_subscriber_content(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Période DL',
            'slug' => 'periode-dl-'.uniqid(),
        ]);

        $yearlyOnlyDownloadable = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Pack annuel membres seulement',
            'slug' => 'pack-annuel-membres-'.uniqid(),
            'description' => 'd',
            'price' => 20,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'is_downloadable' => true,
            'requires_subscription' => true,
            'required_subscription_tier' => 'yearly',
            'download_file_path' => 'courses/downloads/yearly-only.zip',
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $planQuarterly = SubscriptionPlan::create([
            'name' => 'Réseau Membre Herime — Trimestriel',
            'slug' => SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'],
            'plan_type' => 'recurring',
            'billing_period' => 'quarterly',
            'price' => 9,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
            'metadata' => [
                'community_premium' => true,
                'community_display_order' => 0,
            ],
        ]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => ['id' => 'py_q_dl', 'payment_url' => 'https://pay.example/q-dl'],
            ], 200),
        ]);
        config(['services.moneroo.api_key' => 'test_key']);

        $this->actingAs($user)
            ->post(route('subscriptions.subscribe', $planQuarterly))
            ->assertRedirect('https://pay.example/q-dl');

        $invoice = SubscriptionInvoice::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($invoice);

        $this->postJson('/moneroo/webhook', [
            'data' => [
                'id' => 'py_q_dl',
                'status' => 'success',
                'metadata' => [
                    'kind' => 'subscription_invoice',
                    'invoice_id' => (string) $invoice->id,
                ],
            ],
        ])->assertOk();

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user->id,
            'content_id' => $yearlyOnlyDownloadable->id,
        ]);
    }

    public function test_content_update_revokes_member_grant_when_period_no_longer_matches(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Révocation période',
            'slug' => 'revocation-periode-'.uniqid(),
        ]);

        $downloadable = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Ressource membres modifiable',
            'slug' => 'ressource-membres-modifiable-'.uniqid(),
            'description' => 'd',
            'price' => 15,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'is_downloadable' => true,
            'requires_subscription' => true,
            'required_subscription_tier' => 'quarterly',
            'download_file_path' => 'courses/downloads/revocation.zip',
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $planQuarterly = SubscriptionPlan::create([
            'name' => 'Réseau Membre Herime — Trimestriel',
            'slug' => SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'],
            'plan_type' => 'recurring',
            'billing_period' => 'quarterly',
            'price' => 9,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
            'metadata' => [
                'community_premium' => true,
                'community_display_order' => 0,
            ],
        ]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => ['id' => 'py_revoke_period', 'payment_url' => 'https://pay.example/revoke-period'],
            ], 200),
        ]);
        config(['services.moneroo.api_key' => 'test_key']);

        $this->actingAs($user)
            ->post(route('subscriptions.subscribe', $planQuarterly))
            ->assertRedirect('https://pay.example/revoke-period');

        $invoice = SubscriptionInvoice::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($invoice);

        $this->postJson('/moneroo/webhook', [
            'data' => [
                'id' => 'py_revoke_period',
                'status' => 'success',
                'metadata' => [
                    'kind' => 'subscription_invoice',
                    'invoice_id' => (string) $invoice->id,
                ],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'content_id' => $downloadable->id,
            'status' => 'active',
        ]);

        $downloadable->update([
            'required_subscription_tier' => 'yearly',
        ]);

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user->id,
            'content_id' => $downloadable->id,
        ]);
    }

    public function test_process_renewals_creates_invoice_and_extends_billing_period(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $plan = SubscriptionPlan::create([
            'name' => 'Mensuel renouv',
            'slug' => 'mensuel-renouv-'.uniqid(),
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
        $this->assertSame('past_due', $subscription->status);
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

        $this->assertSame(0, $user->activeSubscriptions()->count());
    }

    /**
     * Trimestre, semestre et année : même moteur (calculatePeriodEnd) pour souscription, renouvellement et paiement.
     */
    public function test_process_renewals_extends_period_for_quarterly_semiannual_and_yearly(): void
    {
        $cases = [
            'quarterly' => fn (Carbon $end) => $end->copy()->subMonths(3),
            'semiannual' => fn (Carbon $end) => $end->copy()->subMonths(6),
            'yearly' => fn (Carbon $end) => $end->copy()->subYear(),
        ];

        foreach ($cases as $billingPeriod => $periodStartFn) {
            $user = User::factory()->create(['role' => 'customer']);

            $plan = SubscriptionPlan::create([
                'name' => 'Plan '.$billingPeriod,
                'slug' => 'plan-'.$billingPeriod.'-'.uniqid(),
                'plan_type' => 'recurring',
                'billing_period' => $billingPeriod,
                'price' => 15,
                'trial_days' => 0,
                'is_active' => true,
                'auto_renew_default' => true,
            ]);

            $periodEnd = now()->subHour();
            $periodStart = $periodStartFn($periodEnd);

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
                'metadata' => ['currency' => 'USD', 'amount' => 15],
            ]);

            app(SubscriptionService::class)->processRenewalsForUser($user->id);

            $subscription = UserSubscription::query()->where('user_id', $user->id)->firstOrFail();
            $this->assertSame(
                $periodEnd->format('Y-m-d H:i:s'),
                $subscription->current_period_starts_at->format('Y-m-d H:i:s'),
                'La nouvelle période doit démarrer à l’ancienne fin ('.$billingPeriod.').'
            );

            $expectedEnd = match ($billingPeriod) {
                'quarterly' => $periodEnd->copy()->addMonths(3),
                'semiannual' => $periodEnd->copy()->addMonths(6),
                'yearly' => $periodEnd->copy()->addYear(),
                default => $periodEnd->copy(),
            };
            $this->assertSame(
                $expectedEnd->format('Y-m-d H:i:s'),
                $subscription->current_period_ends_at->format('Y-m-d H:i:s'),
                'Fin de période après renouvellement ('.$billingPeriod.').'
            );
            $this->assertSame('past_due', $subscription->status);
        }
    }

    public function test_process_renewals_with_recent_unpaid_invoice_sets_past_due(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan retard',
            'slug' => 'plan-retard-'.uniqid(),
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

    public function test_process_renewals_past_due_revokes_subscription_only_enrollments(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Revoke sub',
            'slug' => 'revoke-sub-'.uniqid(),
        ]);
        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours sub only',
            'slug' => 'cours-sub-only-'.uniqid(),
            'description' => 'd',
            'price' => 40,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan revoke',
            'slug' => 'plan-revoke-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 30,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);
        $plan->contents()->sync([$course->id]);

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
            'invoice_number' => 'SUB-PEND-REVOKE',
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 30,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
            'created_at' => now()->subDays(2),
        ]);

        app(SubscriptionService::class)->grantLinkedContentAccess($subscription);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'content_id' => $course->id,
            'access_granted_by_subscription_id' => $subscription->id,
        ]);

        app(SubscriptionService::class)->processRenewalsForUser($user->id);

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user->id,
            'content_id' => $course->id,
        ]);
    }

    public function test_past_due_renewal_keeps_enrollment_when_course_also_purchased(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Hybrid sub',
            'slug' => 'hybrid-sub-'.uniqid(),
        ]);
        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours hybrid',
            'slug' => 'cours-hybrid-'.uniqid(),
            'description' => 'd',
            'price' => 40,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan hybrid',
            'slug' => 'plan-hybrid-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 30,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);
        $plan->contents()->sync([$course->id]);

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
            'invoice_number' => 'SUB-PEND-HYBRID',
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 30,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
            'created_at' => now()->subDays(2),
        ]);

        app(SubscriptionService::class)->grantLinkedContentAccess($subscription);

        $order = Order::create([
            'order_number' => 'ORD-HYB-'.strtoupper(substr(uniqid(), -6)),
            'user_id' => $user->id,
            'subtotal' => 40,
            'total' => 40,
            'total_amount' => 40,
            'currency' => 'USD',
            'status' => 'paid',
            'payment_method' => 'moneroo',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'content_id' => $course->id,
            'content_package_id' => null,
            'price' => 40,
            'sale_price' => null,
            'total' => 40,
        ]);
        app(OrderEnrollmentService::class)->syncEnrollmentsFromOrderItems($order, $order->orderItems()->get());

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('content_id', $course->id)
            ->firstOrFail();
        $this->assertSame($order->id, (int) $enrollment->order_id);
        $this->assertNull($enrollment->access_granted_by_subscription_id);

        app(SubscriptionService::class)->processRenewalsForUser($user->id);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'content_id' => $course->id,
            'order_id' => $order->id,
            'status' => 'active',
        ]);
    }

    public function test_learning_allows_standalone_purchase_when_course_requires_subscription(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Learn standalone',
            'slug' => 'learn-standalone-'.uniqid(),
        ]);
        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours req sub',
            'slug' => 'cours-req-sub-'.uniqid(),
            'description' => 'd',
            'price' => 25,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'is_downloadable' => false,
            'requires_subscription' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-LS-'.strtoupper(substr(uniqid(), -6)),
            'user_id' => $user->id,
            'subtotal' => 25,
            'total' => 25,
            'total_amount' => 25,
            'currency' => 'USD',
            'status' => 'paid',
            'payment_method' => 'moneroo',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'content_id' => $course->id,
            'content_package_id' => null,
            'price' => 25,
            'sale_price' => null,
            'total' => 25,
        ]);
        app(OrderEnrollmentService::class)->syncEnrollmentsFromOrderItems($order, $order->orderItems()->get());

        $this->actingAs($user)
            ->get(route('learning.course', $course))
            ->assertOk();
    }

    public function test_subscribe_after_cancelled_reactivates_same_row(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $plan = SubscriptionPlan::create([
            'name' => 'Réabo test',
            'slug' => 'reabo-test-'.uniqid(),
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
        $this->assertSame('pending_payment', $returned->status);
        $this->assertNull($returned->cancelled_at);
        $this->assertNull($returned->ended_at);
        $this->assertTrue($returned->auto_renew);
        $pendingInvoice = $returned->invoices()->where('status', 'pending')->first();
        $this->assertNotNull($pendingInvoice);
        $this->assertSame(9.0, (float) $pendingInvoice->amount);
    }

    public function test_resubscribe_primary_label_only_when_paid_history_exists(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $plan = SubscriptionPlan::create([
            'name' => 'Label CTA test',
            'slug' => 'label-cta-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 10,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        $firstAttempt = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'pending_payment',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);

        SubscriptionInvoice::create([
            'invoice_number' => 'SUB-LABEL-PEND-1',
            'user_subscription_id' => $firstAttempt->id,
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $firstAttempt->load('invoices');
        $this->assertFalse($firstAttempt->hasPaidOrPriorPaidSamePlan());
        $this->assertFalse($firstAttempt->shouldUseResubscribePrimaryLabel());

        SubscriptionInvoice::create([
            'invoice_number' => 'SUB-LABEL-PAID-1',
            'user_subscription_id' => $firstAttempt->id,
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USD',
            'status' => 'paid',
            'due_at' => now()->subDay(),
            'paid_at' => now(),
        ]);

        $firstAttempt->refresh()->load('invoices');
        $this->assertTrue($firstAttempt->hasPaidOrPriorPaidSamePlan());
        $this->assertTrue($firstAttempt->shouldUseResubscribePrimaryLabel());

        $secondSub = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'pending_payment',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);

        SubscriptionInvoice::create([
            'invoice_number' => 'SUB-LABEL-PEND-2',
            'user_subscription_id' => $secondSub->id,
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $secondSub->refresh()->load('invoices');
        $this->assertTrue($secondSub->hasPaidOrPriorPaidSamePlan());
        $this->assertTrue($secondSub->shouldUseResubscribePrimaryLabel());
    }

    public function test_overdue_first_payment_invoice_expires_subscription_and_emails_user(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'customer']);

        $plan = SubscriptionPlan::create([
            'name' => 'Premier paiement délai',
            'slug' => 'premier-delai-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 11,
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

        SubscriptionInvoice::create([
            'invoice_number' => 'SUB-OVERDUE-FIRST',
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 11,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->subMinute(),
        ]);

        app(SubscriptionService::class)->processRenewalsForUser($user->id);

        $subscription->refresh();
        $this->assertSame('expired', $subscription->status);

        $invoice = SubscriptionInvoice::query()->where('user_subscription_id', $subscription->id)->firstOrFail();
        $this->assertSame('failed', $invoice->status);
        $this->assertSame('overdue', $invoice->metadata['failed_reason'] ?? null);

        Notification::assertSentTo($user, SubscriptionInvoiceFailed::class);
    }

    public function test_overdue_invoice_notifications_are_not_sent_twice_on_reprocessing(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'customer']);

        $plan = SubscriptionPlan::create([
            'name' => 'Premier paiement idempotent',
            'slug' => 'premier-idempotent-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 11,
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

        SubscriptionInvoice::create([
            'invoice_number' => 'SUB-OVERDUE-IDEMPOTENT',
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 11,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->subMinute(),
        ]);

        $service = app(SubscriptionService::class);
        $service->processRenewalsForUser($user->id);
        $service->processRenewalsForUser($user->id);

        Notification::assertSentToTimes($user, SubscriptionInvoiceFailed::class, 1);
    }

    public function test_cancelled_member_plan_http_subscribe_redirects_to_moneroo_checkout(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'customer']);

        $plan = SubscriptionPlan::create([
            'name' => 'Réseau Membre Herime — Annuel',
            'slug' => SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'],
            'plan_type' => 'recurring',
            'billing_period' => 'yearly',
            'price' => 20,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
            'metadata' => [
                'community_premium' => true,
                'community_display_order' => 2,
            ],
        ]);

        UserSubscription::create([
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
            'metadata' => ['currency' => 'USD', 'amount' => 20],
        ]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => ['id' => 'py_cancel_reabo', 'payment_url' => 'https://pay.example/cancel-reabo'],
            ], 200),
        ]);
        config(['services.moneroo.api_key' => 'test_key']);

        $this->actingAs($user)
            ->post(route('subscriptions.subscribe', $plan))
            ->assertRedirect('https://pay.example/cancel-reabo');

        $sub = UserSubscription::query()->where('user_id', $user->id)->first();
        $this->assertSame('pending_payment', $sub->status);

        $invoice = SubscriptionInvoice::query()->where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($invoice);
        $order = Order::query()->where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertSame('pending', $order->status);
        $this->assertSame($order->id, (int) data_get($invoice->metadata, 'order_id'));
        $this->assertTrue(
            Payment::query()->where('order_id', $order->id)->where('status', 'pending')->exists()
        );

        Notification::assertSentTo($user, OrderStatusUpdated::class);
        Notification::assertNotSentTo($user, SubscriptionActivated::class);
        Notification::assertNotSentTo($user, AdminSubscriptionActivated::class);
    }

    public function test_moneroo_cancel_latest_pending_ignores_subscription_orders(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        Order::query()->create([
            'order_number' => 'SUB-TESTAB-'.time(),
            'user_id' => $user->id,
            'subtotal' => 10,
            'discount' => 0,
            'total' => 10,
            'total_amount' => 10,
            'currency' => 'USD',
            'payment_currency' => 'USD',
            'payment_amount' => 10,
            'exchange_rate' => 1,
            'status' => 'pending',
            'payment_method' => 'moneroo',
        ]);

        $this->actingAs($user)
            ->post(url('/moneroo/cancel-latest'))
            ->assertStatus(404);
    }

    public function test_moneroo_cancel_latest_pending_targets_cart_when_subscription_is_newer(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $t = (string) time();
        $cartOrder = Order::query()->create([
            'order_number' => 'MON-TESTCD-'.$t,
            'user_id' => $user->id,
            'subtotal' => 5,
            'discount' => 0,
            'total' => 5,
            'total_amount' => 5,
            'currency' => 'USD',
            'payment_currency' => 'USD',
            'payment_amount' => 5,
            'exchange_rate' => 1,
            'status' => 'pending',
            'payment_method' => 'moneroo',
        ]);
        $subOrder = Order::query()->create([
            'order_number' => 'SUB-TESTXY-'.$t,
            'user_id' => $user->id,
            'subtotal' => 20,
            'discount' => 0,
            'total' => 20,
            'total_amount' => 20,
            'currency' => 'USD',
            'payment_currency' => 'USD',
            'payment_amount' => 20,
            'exchange_rate' => 1,
            'status' => 'pending',
            'payment_method' => 'moneroo',
        ]);

        $this->actingAs($user)
            ->post(url('/moneroo/cancel-latest'))
            ->assertOk();

        $this->assertSame('cancelled', $cartOrder->fresh()->status);
        $this->assertSame('pending', $subOrder->fresh()->status);
    }

    public function test_closed_subscription_invoices_cancel_pending_linked_orders(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $plan = SubscriptionPlan::create([
            'name' => 'Close order plan',
            'slug' => 'close-order-'.uniqid(),
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
            'status' => 'past_due',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);
        $invoice = SubscriptionInvoice::create([
            'invoice_number' => 'SUB-CLOSE-001',
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 12,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $checkout = app(SubscriptionCheckoutOrderService::class);
        $req = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '127.0.0.1']);
        $order = $checkout->createPendingOrderForSubscriptionInvoice($invoice, $user, $req);
        $checkout->recordPendingPayment($order, 'subinv_close_test', [], [], 'py_close_test');
        $this->assertSame('pending', $order->status);

        $checkout->cancelPendingOrdersForInvoicesClosed([$invoice->id], 'Test fermeture facture');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertTrue(
            Payment::query()->where('order_id', $order->id)->where('status', 'failed')->exists()
        );
    }

    public function test_customer_subscription_cancel_closes_pending_invoices_and_linked_orders(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'customer']);
        $plan = SubscriptionPlan::create([
            'name' => 'Cancel closes inv',
            'slug' => 'cancel-closes-inv-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 15,
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
            'invoice_number' => 'SUB-CUST-CXL-001',
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 15,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $checkout = app(SubscriptionCheckoutOrderService::class);
        $req = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '127.0.0.1']);
        $order = $checkout->createPendingOrderForSubscriptionInvoice($invoice, $user, $req);
        $checkout->recordPendingPayment($order, 'subinv_cust_cxl_test', [], [], 'py_cust_cxl_test');
        $this->assertSame('pending', $order->fresh()->status);

        $this->actingAs($user)
            ->post(route('subscriptions.cancel', $subscription))
            ->assertRedirect();

        $invoice->refresh();
        $subscription->refresh();
        $this->assertSame('cancelled', $subscription->status);
        $this->assertSame('cancelled', $invoice->status);
        $this->assertNotNull(data_get($invoice->metadata, 'cancelled_due_to_customer_subscription_cancellation_at'));
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertTrue(
            Payment::query()->where('order_id', $order->id)->where('status', 'failed')->exists()
        );

        Notification::assertSentTo($user, SubscriptionInvoiceCancelled::class);
    }

    public function test_process_renewals_for_user_does_not_touch_other_users_subscriptions(): void
    {
        $userA = User::factory()->create(['role' => 'customer']);
        $userB = User::factory()->create(['role' => 'customer']);

        $plan = SubscriptionPlan::create([
            'name' => 'Iso user',
            'slug' => 'iso-user-'.uniqid(),
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
            'slug' => 'mw-renew-'.uniqid(),
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

    public function test_resume_auto_renew_sends_customer_and_admin_notifications(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan resume AR',
            'slug' => 'plan-resume-ar-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 11,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $customer->id,
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
            'metadata' => ['currency' => 'USD', 'amount' => 11],
        ]);

        $this->actingAs($customer)
            ->post(route('subscriptions.resume', $subscription))
            ->assertRedirect();

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertTrue($subscription->auto_renew);

        Notification::assertSentTo($customer, SubscriptionAutoRenewResumed::class);
        Notification::assertSentTo($admin, AdminSubscriptionAutoRenewResumed::class);
    }

    public function test_resume_sets_past_due_when_unpaid_pending_invoice_exists(): void
    {
        Notification::fake();

        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan resume PD',
            'slug' => 'plan-resume-pd-'.uniqid(),
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
            'status' => 'cancelled',
            'starts_at' => now()->subMonth(),
            'trial_ends_at' => null,
            'current_period_starts_at' => now()->subMonth(),
            'current_period_ends_at' => now()->addMonth(),
            'cancelled_at' => now(),
            'ended_at' => now()->addMonth(),
            'auto_renew' => false,
            'payment_method' => 'moneroo',
            'metadata' => ['currency' => 'USD', 'amount' => 12],
        ]);

        SubscriptionInvoice::create([
            'invoice_number' => 'SUB-RESUME-PD-'.uniqid(),
            'user_subscription_id' => $subscription->id,
            'user_id' => $customer->id,
            'amount' => 12,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $this->actingAs($customer)
            ->post(route('subscriptions.resume', $subscription))
            ->assertRedirect();

        $subscription->refresh();
        $this->assertSame('past_due', $subscription->status);
        $this->assertTrue($subscription->auto_renew);
    }
}
