<?php

namespace Tests\Feature;

use App\Http\Controllers\CartController;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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

    public function test_guest_checkout_new_account_auto_login_can_initiate_moneroo_without_session_expired(): void
    {
        Http::fake([
            'https://compte.herime.com/*' => Http::response([
                'success' => true,
                'user' => ['id' => 98765],
            ], 201),
            'https://api.moneroo.io/v1/payments/initialize' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 'pay_test_123',
                    'checkout_url' => 'https://checkout.moneroo.test/pay_test_123',
                ],
            ], 201),
        ]);

        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'TestCat3',
            'slug' => 'testcat3-'.uniqid(),
        ]);
        $course = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Cours init paiement invité',
            'slug' => 'cours-init-paiement-invite-'.uniqid(),
            'description' => 'desc',
            'price' => 40,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $this->withSession([
            'cart' => ['contents' => [$course->id], 'packages' => []],
        ]);

        $guestResponse = $this->postJson(route('cart.guest-checkout'), [
            'name' => 'Client Checkout',
            'email' => 'new-guest-'.uniqid().'@example.com',
            'phone' => '+33611223344',
        ]);

        $guestResponse->assertOk();
        $guestResponse->assertJson(['success' => true]);
        $this->assertAuthenticated();

        // Simule un jeton SSO expiré/invalide pour vérifier que le flux Moneroo n'est pas bloqué.
        session(['sso_token' => 'invalid-token-for-test']);

        $initResponse = $this->postJson(route('moneroo.initiate'), [
            'amount' => 40,
            'currency' => 'USD',
        ]);

        $initResponse->assertOk();
        $initResponse->assertJson([
            'success' => true,
            'checkout_url' => 'https://checkout.moneroo.test/pay_test_123',
        ]);
        $this->assertStringNotContainsString(
            'session a expiré',
            mb_strtolower((string) ($initResponse->json('message') ?? ''))
        );
    }

    public function test_profile_redirect_without_sso_token_redirects_to_sso_login_not_local_logout(): void
    {
        config(['services.sso.enabled' => true]);

        $user = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('profile.redirect'));

        $response->assertRedirect();
        $target = (string) $response->headers->get('Location', '');
        $this->assertStringContainsString('compte.herime.com/login', $target);
        $this->assertStringContainsString('force_token=1', $target);
        $this->assertAuthenticatedAs($user);
    }
}
