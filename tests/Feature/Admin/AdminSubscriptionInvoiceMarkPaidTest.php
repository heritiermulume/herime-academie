<?php

namespace Tests\Feature\Admin;

use App\Models\Payment;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\AdminSubscriptionActivated;
use App\Notifications\SubscriptionActivated;
use App\Notifications\SubscriptionInvoicePaid;
use App\Services\SubscriptionCheckoutOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminSubscriptionInvoiceMarkPaidTest extends TestCase
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

    public function test_admin_mark_paid_pending_payment_activates_subscription_and_order(): void
    {
        Notification::fake();

        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan admin mark paid',
            'slug' => 'plan-admin-mp-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 18,
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
            'invoice_number' => 'SUB-ADMIN-MP-001',
            'user_subscription_id' => $subscription->id,
            'user_id' => $customer->id,
            'amount' => 18,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $checkout = app(SubscriptionCheckoutOrderService::class);
        $req = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '127.0.0.1']);
        $order = $checkout->createPendingOrderForSubscriptionInvoice($invoice, $customer, $req);
        $checkout->recordPendingPayment($order, 'subinv_admin_mp', [], [], 'py_admin_mp');
        $invoice->update([
            'metadata' => array_merge($invoice->metadata ?? [], [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.subscriptions.invoices.mark-paid', $invoice))
            ->assertRedirect();

        $invoice->refresh();
        $subscription->refresh();
        $order->refresh();

        $this->assertSame('paid', $invoice->status);
        $this->assertSame('active', $subscription->status);
        $this->assertSame('paid', $order->status);
        $this->assertSame('completed', Payment::query()->where('order_id', $order->id)->value('status'));
        $this->assertNotNull(data_get($invoice->metadata, 'marked_paid_by_admin_at'));

        Notification::assertSentTo($customer, SubscriptionActivated::class);
        Notification::assertSentTo($customer, SubscriptionInvoicePaid::class);
    }

    public function test_admin_mark_paid_past_due_sends_renewal_paid_notifications(): void
    {
        Notification::fake();

        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan past due',
            'slug' => 'plan-pd-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 9,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $customer->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'past_due',
            'starts_at' => now()->subMonth(),
            'current_period_starts_at' => now()->subMonth(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);

        $invoice = SubscriptionInvoice::create([
            'invoice_number' => 'SUB-PD-001',
            'user_subscription_id' => $subscription->id,
            'user_id' => $customer->id,
            'amount' => 9,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.subscriptions.invoices.mark-paid', $invoice))
            ->assertRedirect();

        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertSame('paid', $invoice->fresh()->status);

        Notification::assertSentTo($customer, SubscriptionActivated::class, function ($notification) use ($customer) {
            return ($notification->toArray($customer)['type'] ?? '') === 'subscription_paid_renewal';
        });
        Notification::assertSentTo($customer, SubscriptionInvoicePaid::class);
        Notification::assertSentTo($this->admin, AdminSubscriptionActivated::class, function ($notification) {
            return ($notification->toArray($this->admin)['type'] ?? '') === 'admin_subscription_paid_renewal';
        });
    }

    public function test_admin_mark_paid_idempotent_when_already_paid(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan idem',
            'slug' => 'plan-idem-'.uniqid(),
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
            'status' => 'active',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'auto_renew' => true,
            'payment_method' => 'moneroo',
        ]);

        $invoice = SubscriptionInvoice::create([
            'invoice_number' => 'SUB-PAID-ALREADY',
            'user_subscription_id' => $subscription->id,
            'user_id' => $customer->id,
            'amount' => 1,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
            'due_at' => now()->addDay(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.subscriptions.invoices.mark-paid', $invoice))
            ->assertRedirect();
    }
}
