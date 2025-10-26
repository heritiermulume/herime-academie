<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_whatsapp_order_and_get_json(): void
    {
        $user = User::factory()->create([
            'phone' => '+243 999 000 000',
        ]);

        $payload = [
            'cart_items' => [
                [
                    'course' => [
                        'id' => 10,
                        'title' => 'Cours Test',
                        'instructor' => [
                            'name' => 'Instructeur Test',
                        ],
                    ],
                    'price' => 19.99,
                    'quantity' => 1,
                ],
            ],
            'total_amount' => 19.99,
            'billing_info' => [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'email' => 'jean@example.com',
                'phone' => '+243 999 000 000',
                'address' => 'Kinshasa',
            ],
        ];

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->postJson(route('whatsapp.order.create'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success', 'message', 'order_number', 'order_id'
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_method' => 'whatsapp',
            'status' => 'pending',
            'total' => 19.99,
        ]);
    }
}






