<?php

namespace Tests\Feature\Admin;

use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\SubscriptionInvoiceCancelled;
use App\Services\SubscriptionCheckoutOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminSubscriptionInvoiceCancelTest extends TestCase
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

    public function test_admin_cancel_pending_invoice_closes_orders_and_notifies_customer(): void
    {
        Notification::fake();

        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan admin cancel',
            'slug' => 'plan-admin-cancel-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 15,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $customer->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'pending_payment',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);

        $invoice = SubscriptionInvoice::create([
            'invoice_number' => 'SUB-ADMIN-CANCEL-001',
            'user_subscription_id' => $subscription->id,
            'user_id' => $customer->id,
            'amount' => 15,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $checkout = app(SubscriptionCheckoutOrderService::class);
        $req = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '127.0.0.1']);
        $order = $checkout->createPendingOrderForSubscriptionInvoice($invoice, $customer, $req);
        $checkout->recordPendingPayment($order, 'subinv_admin_cancel', [], [], 'py_admin_cancel');

        $this->actingAs($this->admin)
            ->post(route('admin.subscriptions.invoices.cancel', $invoice))
            ->assertRedirect();

        $invoice->refresh();
        $subscription->refresh();

        $this->assertSame('cancelled', $invoice->status);
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('expired', $subscription->status);
        $this->assertNotNull(data_get($invoice->metadata, 'cancelled_by_admin_at'));

        Notification::assertSentTo($customer, SubscriptionInvoiceCancelled::class);
    }

    public function test_admin_cannot_cancel_non_pending_invoice(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan paid',
            'slug' => 'plan-paid-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 10,
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

        $invoice = SubscriptionInvoice::create([
            'invoice_number' => 'SUB-PAID-001',
            'user_subscription_id' => $subscription->id,
            'user_id' => $customer->id,
            'amount' => 10,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
            'due_at' => now()->addDay(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.subscriptions.invoices.cancel', $invoice))
            ->assertRedirect();

        $this->assertSame('paid', $invoice->fresh()->status);
    }
}
