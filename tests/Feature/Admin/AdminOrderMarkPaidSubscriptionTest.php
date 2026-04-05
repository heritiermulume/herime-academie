<?php

namespace Tests\Feature\Admin;

use App\Models\Payment;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\SubscriptionActivated;
use App\Notifications\SubscriptionInvoicePaid;
use App\Services\SubscriptionCheckoutOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminOrderMarkPaidSubscriptionTest extends TestCase
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

    public function test_admin_mark_order_paid_aligns_subscription_invoice_like_invoice_mark_paid(): void
    {
        Notification::fake();

        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan admin order mark paid',
            'slug' => 'plan-admin-omp-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 19,
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
            'invoice_number' => 'SUB-ADMIN-OMP-001',
            'user_subscription_id' => $subscription->id,
            'user_id' => $customer->id,
            'amount' => 19,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $checkout = app(SubscriptionCheckoutOrderService::class);
        $req = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '127.0.0.1']);
        $order = $checkout->createPendingOrderForSubscriptionInvoice($invoice, $customer, $req);
        $checkout->recordPendingPayment($order, 'subinv_admin_omp', [], [], 'py_admin_omp');
        $invoice->update([
            'metadata' => array_merge($invoice->metadata ?? [], [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]),
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.orders.mark-paid', $order), [
                'payment_reference' => 'virement-admin',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $invoice->refresh();
        $subscription->refresh();
        $order->refresh();

        $this->assertSame('paid', $invoice->status);
        $this->assertSame('active', $subscription->status);
        $this->assertSame('paid', $order->status);
        $this->assertSame('completed', Payment::query()->where('order_id', $order->id)->value('status'));
        $this->assertSame('order_manual_verify', data_get($invoice->metadata, 'confirmed_via'));

        Notification::assertSentTo($customer, SubscriptionActivated::class);
        Notification::assertSentTo($customer, SubscriptionInvoicePaid::class);
    }

    public function test_admin_order_show_lists_subscription_plan_in_included_contents(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $plan = SubscriptionPlan::create([
            'name' => 'Plan visible fiche commande admin',
            'slug' => 'plan-visible-order-'.uniqid(),
            'plan_type' => 'recurring',
            'billing_period' => 'monthly',
            'price' => 19,
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
            'invoice_number' => 'SUB-VIS-001',
            'user_subscription_id' => $subscription->id,
            'user_id' => $customer->id,
            'amount' => 19,
            'currency' => 'USD',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ]);

        $checkout = app(SubscriptionCheckoutOrderService::class);
        $req = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '127.0.0.1']);
        $order = $checkout->createPendingOrderForSubscriptionInvoice($invoice, $customer, $req);

        $this->actingAs($this->admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Plan visible fiche commande admin', false)
            ->assertSee('Facture SUB-VIS-001', false)
            ->assertSee('Abonnement', false)
            ->assertSee('Annuler l’abonnement', false);
    }
}
