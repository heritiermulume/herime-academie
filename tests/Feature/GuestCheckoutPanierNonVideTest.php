<?php

namespace Tests\Feature;

use App\Http\Controllers\CartController;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCheckoutPanierNonVideTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_checkout_prepare_accepts_db_cart_when_session_cart_is_empty_but_guest_pay_intent(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'TestCat',
            'slug' => 'testcat-'.uniqid(),
        ]);
        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours panier invité',
            'slug' => 'cours-panier-invite-'.uniqid(),
            'description' => 'desc',
            'price' => 25,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $customer = User::factory()->create([
            'email' => 'guestmerge@example.com',
            'phone' => '+33601020304',
            'role' => 'customer',
        ]);

        CartItem::create([
            'user_id' => $customer->id,
            'content_id' => $course->id,
        ]);

        $this->withSession([
            'cart' => ['contents' => [], 'packages' => []],
            CartController::GUEST_PAY_READY_KEY => true,
            CartController::GUEST_PAY_USER_ID_KEY => $customer->id,
        ]);

        $response = $this->postJson(route('cart.guest-checkout'), [
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertNotSame(
            'Votre panier est vide.',
            $response->json('message'),
            'Le panier ne doit pas être considéré vide lorsque les articles sont en base pour l’intent invité.'
        );
    }

    public function test_cart_summary_returns_total_for_guest_pay_db_cart(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'TestCat2',
            'slug' => 'testcat2-'.uniqid(),
        ]);
        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours résumé',
            'slug' => 'cours-resume-'.uniqid(),
            'description' => 'desc',
            'price' => 30,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $customer = User::factory()->create(['role' => 'customer']);

        CartItem::create([
            'user_id' => $customer->id,
            'content_id' => $course->id,
        ]);

        $this->withSession([
            CartController::GUEST_PAY_READY_KEY => true,
            CartController::GUEST_PAY_USER_ID_KEY => $customer->id,
        ]);

        $response = $this->getJson(route('cart.summary'));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertGreaterThan(0, (float) $response->json('total'));
        $this->assertGreaterThan(0, (int) $response->json('item_count'));
    }
}
