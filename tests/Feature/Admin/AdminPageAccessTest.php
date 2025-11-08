<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPageAccessTest extends TestCase
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

    public function test_admin_can_access_banner_create_form(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.banners.create'))
            ->assertOk();
    }

    public function test_admin_can_access_user_edit_form(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $user))
            ->assertOk();
    }

    public function test_admin_can_access_user_profile_page(): void
    {
        $user = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user))
            ->assertOk();
    }

    public function test_admin_can_access_order_detail_page(): void
    {
        $customer = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => Str::upper(Str::random(10)),
            'user_id' => $customer->id,
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk();
    }
}

