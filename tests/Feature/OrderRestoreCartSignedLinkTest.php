<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\CartPackage;
use App\Models\Category;
use App\Models\ContentPackage;
use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OrderRestoreCartSignedLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_restore_link_returns_dedicated_expired_page_when_signature_is_invalid(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'order_number' => 'ORD-EXPIRED-'.strtoupper(substr(uniqid(), -6)),
            'user_id' => $customer->id,
            'subtotal' => 50,
            'total' => 50,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => 'moneroo',
        ]);

        $expiredUrl = URL::temporarySignedRoute(
            'orders.restore-cart.signed',
            now()->subMinutes(5),
            ['order' => $order->id]
        );

        $this->get($expiredUrl)
            ->assertStatus(410)
            ->assertViewIs('orders.restore-link-expired');
    }

    public function test_valid_signed_restore_link_replaces_cart_from_order_for_owner(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Relance paiement',
            'slug' => 'relance-paiement-'.uniqid(),
        ]);

        $courseInOrder = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Contenu commande',
            'slug' => 'contenu-commande-'.uniqid(),
            'description' => 'desc',
            'price' => 40,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);
        $otherCourse = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Autre contenu panier',
            'slug' => 'autre-contenu-'.uniqid(),
            'description' => 'desc',
            'price' => 55,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $packageInOrder = ContentPackage::create([
            'title' => 'Pack commande',
            'slug' => 'pack-commande-'.uniqid(),
            'price' => 80,
            'is_published' => true,
            'is_sale_enabled' => true,
        ]);
        $otherPackage = ContentPackage::create([
            'title' => 'Pack existant panier',
            'slug' => 'pack-panier-'.uniqid(),
            'price' => 90,
            'is_published' => true,
            'is_sale_enabled' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-RESTORE-'.strtoupper(substr(uniqid(), -6)),
            'user_id' => $customer->id,
            'subtotal' => 120,
            'total' => 120,
            'total_amount' => 120,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => 'moneroo',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'content_id' => $courseInOrder->id,
            'content_package_id' => null,
            'price' => 40,
            'sale_price' => null,
            'total' => 40,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'content_id' => $courseInOrder->id,
            'content_package_id' => $packageInOrder->id,
            'price' => 80,
            'sale_price' => null,
            'total' => 80,
        ]);

        CartItem::create([
            'user_id' => $customer->id,
            'content_id' => $otherCourse->id,
        ]);
        CartPackage::create([
            'user_id' => $customer->id,
            'content_package_id' => $otherPackage->id,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'orders.restore-cart.signed',
            now()->addMinutes(30),
            ['order' => $order->id]
        );

        $this->actingAs($customer)
            ->get($signedUrl)
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $customer->id,
            'content_id' => $courseInOrder->id,
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $customer->id,
            'content_id' => $otherCourse->id,
        ]);
        $this->assertDatabaseHas('cart_packages', [
            'user_id' => $customer->id,
            'content_package_id' => $packageInOrder->id,
        ]);
        $this->assertDatabaseMissing('cart_packages', [
            'user_id' => $customer->id,
            'content_package_id' => $otherPackage->id,
        ]);
    }

    public function test_signed_restore_link_with_wrong_authenticated_user_redirects_without_cart_change(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $wrongUser = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'Relance mauvais compte',
            'slug' => 'relance-mauvais-compte-'.uniqid(),
        ]);

        $ownerCourse = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Contenu proprietaire',
            'slug' => 'contenu-proprietaire-'.uniqid(),
            'description' => 'desc',
            'price' => 33,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);
        $wrongUserCourse = Course::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Contenu mauvais compte',
            'slug' => 'contenu-mauvais-compte-'.uniqid(),
            'description' => 'desc',
            'price' => 44,
            'is_free' => false,
            'is_published' => true,
            'is_sale_enabled' => true,
            'level' => 'beginner',
            'language' => 'fr',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-WRONG-'.strtoupper(substr(uniqid(), -6)),
            'user_id' => $owner->id,
            'subtotal' => 33,
            'total' => 33,
            'total_amount' => 33,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => 'moneroo',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'content_id' => $ownerCourse->id,
            'content_package_id' => null,
            'price' => 33,
            'sale_price' => null,
            'total' => 33,
        ]);

        CartItem::create([
            'user_id' => $wrongUser->id,
            'content_id' => $wrongUserCourse->id,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'orders.restore-cart.signed',
            now()->addMinutes(30),
            ['order' => $order->id]
        );

        $this->actingAs($wrongUser)
            ->get($signedUrl)
            ->assertRedirect(route('orders.index'));

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $wrongUser->id,
            'content_id' => $wrongUserCourse->id,
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $wrongUser->id,
            'content_id' => $ownerCourse->id,
        ]);
    }
}
