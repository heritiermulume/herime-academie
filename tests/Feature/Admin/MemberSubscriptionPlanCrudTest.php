<?php

namespace Tests\Feature\Admin;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberSubscriptionPlanCrudTest extends TestCase
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

    public function test_legacy_edit_url_redirects_to_membre_bundle_edit(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Réseau Membre Herime — Trimestriel',
            'slug' => SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'],
            'description' => 'x',
            'plan_type' => 'recurring',
            'billing_period' => 'quarterly',
            'price' => 1,
            'annual_discount_percent' => 0,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
            'content_id' => null,
            'metadata' => [
                'community_premium' => true,
                'community_display_order' => 0,
                'label' => 'Trimestriel',
            ],
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.subscriptions.plans.edit', $plan))
            ->assertRedirect(route('admin.subscriptions.plans.membre.edit'));
    }

    public function test_admin_can_create_member_bundle_when_slugs_are_free(): void
    {
        $payload = [
            'name' => 'Réseau Test',
            'description' => 'Description test',
            'membre_price_quarterly' => '10.50',
            'membre_price_semiannual' => '20',
            'membre_price_yearly' => '30.25',
            'membre_highlight_quarterly' => 'H1',
            'membre_highlight_semiannual' => '',
            'membre_highlight_yearly' => 'H3',
            'membre_trial_days' => [
                'quarterly' => '7',
                'semiannual' => '14',
                'yearly' => '0',
            ],
            'is_active' => '1',
            'auto_renew_default' => '1',
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.subscriptions.plans.store'), $payload)
            ->assertRedirect(route('admin.subscriptions.plans.index'));

        $expectedTrials = ['quarterly' => 7, 'semiannual' => 14, 'yearly' => 0];
        foreach (SubscriptionPlan::MEMBER_COMMUNITY_SLUGS as $period => $slug) {
            $plan = SubscriptionPlan::query()->where('slug', $slug)->first();
            $this->assertNotNull($plan, "Plan manquant pour le slug {$slug}");
            $this->assertStringStartsWith('Réseau Test —', $plan->name);
            $this->assertSame('Description test', $plan->description);
            $this->assertSame($expectedTrials[$period], $plan->trial_days);
            $this->assertTrue($plan->is_active);
            $this->assertTrue($plan->auto_renew_default);
        }

        $this->assertSame(10.5, (float) SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'])->value('price'));
        $this->assertSame(20.0, (float) SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['semiannual'])->value('price'));
        $this->assertSame(30.25, (float) SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'])->value('price'));

        $qPlan = SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'])->firstOrFail();
        $this->assertFalse($qPlan->isCommunityCardPopular());
        $yPlan = SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'])->firstOrFail();
        $this->assertTrue($yPlan->isCommunityCardPopular());
    }

    public function test_admin_can_update_member_bundle_without_plan_type_field(): void
    {
        foreach (SubscriptionPlan::MEMBER_COMMUNITY_SLUGS as $period => $slug) {
            SubscriptionPlan::create([
                'name' => 'Old — '.(SubscriptionPlan::MEMBER_COMMUNITY_PERIOD_LABELS[$period] ?? $period),
                'slug' => $slug,
                'description' => 'Old',
                'plan_type' => 'recurring',
                'billing_period' => match ($period) {
                    'quarterly' => 'quarterly',
                    'semiannual' => 'semiannual',
                    default => 'yearly',
                },
                'price' => 1,
                'annual_discount_percent' => 0,
                'trial_days' => 0,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => [
                    'community_premium' => true,
                    'community_display_order' => match ($period) {
                        'quarterly' => 0,
                        'semiannual' => 1,
                        default => 2,
                    },
                    'label' => SubscriptionPlan::MEMBER_COMMUNITY_PERIOD_LABELS[$period] ?? $period,
                ],
            ]);
        }

        $yearly = SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'])->firstOrFail();

        $payload = [
            'name' => 'Nouveau nom',
            'description' => 'Nouvelle description',
            'membre_price_quarterly' => '99',
            'membre_price_semiannual' => '149.5',
            'membre_price_yearly' => '249',
            'membre_highlight_quarterly' => '',
            'membre_highlight_semiannual' => 'Promo',
            'membre_highlight_yearly' => '',
            'membre_trial_days' => [
                'quarterly' => '14',
                'semiannual' => '5',
                'yearly' => '21',
            ],
            'is_active' => '1',
            'auto_renew_default' => '1',
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.subscriptions.plans.membre.update'), $payload)
            ->assertRedirect(route('admin.subscriptions.plans.index'));

        $q = SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'])->firstOrFail();
        $this->assertSame(99.0, (float) $q->price);
        $this->assertSame('Nouvelle description', $q->description);
        $this->assertSame(14, $q->trial_days);
        $this->assertNull(data_get($q->metadata, 'community_card_highlight'));

        $s = SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['semiannual'])->firstOrFail();
        $this->assertSame(149.5, (float) $s->price);
        $this->assertSame(5, $s->trial_days);
        $this->assertSame('Promo', data_get($s->metadata, 'community_card_highlight'));

        $y = SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'])->firstOrFail();
        $this->assertSame(249.0, (float) $y->price);
        $this->assertSame(21, $y->trial_days);

        $this->assertTrue($y->fresh()->isCommunityCardPopular());
        $this->assertFalse(SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['semiannual'])->firstOrFail()->isCommunityCardPopular());
    }

    public function test_membre_update_sets_popular_period_to_semiannual(): void
    {
        foreach (SubscriptionPlan::MEMBER_COMMUNITY_SLUGS as $period => $slug) {
            SubscriptionPlan::create([
                'name' => 'Old — '.(SubscriptionPlan::MEMBER_COMMUNITY_PERIOD_LABELS[$period] ?? $period),
                'slug' => $slug,
                'description' => 'Old',
                'plan_type' => 'recurring',
                'billing_period' => match ($period) {
                    'quarterly' => 'quarterly',
                    'semiannual' => 'semiannual',
                    default => 'yearly',
                },
                'price' => 1,
                'annual_discount_percent' => 0,
                'trial_days' => 0,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => [
                    'community_premium' => true,
                    'community_display_order' => match ($period) {
                        'quarterly' => 0,
                        'semiannual' => 1,
                        default => 2,
                    },
                    'label' => SubscriptionPlan::MEMBER_COMMUNITY_PERIOD_LABELS[$period] ?? $period,
                    'community_card_popular' => $period === 'yearly',
                ],
            ]);
        }

        $payload = [
            'name' => 'Nouveau nom',
            'description' => 'D',
            'membre_price_quarterly' => '1',
            'membre_price_semiannual' => '2',
            'membre_price_yearly' => '3',
            'membre_highlight_quarterly' => '',
            'membre_highlight_semiannual' => '',
            'membre_highlight_yearly' => '',
            'membre_popular_period' => 'semiannual',
            'membre_trial_days' => ['quarterly' => '0', 'semiannual' => '0', 'yearly' => '0'],
            'is_active' => '1',
            'auto_renew_default' => '1',
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.subscriptions.plans.membre.update'), $payload)
            ->assertRedirect(route('admin.subscriptions.plans.index'));

        $this->assertTrue(SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['semiannual'])->firstOrFail()->isCommunityCardPopular());
        $this->assertFalse(SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'])->firstOrFail()->isCommunityCardPopular());
        $this->assertFalse(SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'])->firstOrFail()->isCommunityCardPopular());
    }

    public function test_update_accepts_prices_with_decimal_comma(): void
    {
        foreach (SubscriptionPlan::MEMBER_COMMUNITY_SLUGS as $period => $slug) {
            SubscriptionPlan::create([
                'name' => 'Old — '.(SubscriptionPlan::MEMBER_COMMUNITY_PERIOD_LABELS[$period] ?? $period),
                'slug' => $slug,
                'description' => 'Old',
                'plan_type' => 'recurring',
                'billing_period' => match ($period) {
                    'quarterly' => 'quarterly',
                    'semiannual' => 'semiannual',
                    default => 'yearly',
                },
                'price' => 1,
                'annual_discount_percent' => 0,
                'trial_days' => 0,
                'is_active' => true,
                'auto_renew_default' => true,
                'content_id' => null,
                'metadata' => [
                    'community_premium' => true,
                    'community_display_order' => match ($period) {
                        'quarterly' => 0,
                        'semiannual' => 1,
                        default => 2,
                    },
                    'label' => SubscriptionPlan::MEMBER_COMMUNITY_PERIOD_LABELS[$period] ?? $period,
                ],
            ]);
        }

        $yearly = SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'])->firstOrFail();

        $payload = [
            'name' => 'Nouveau nom',
            'description' => 'D',
            'membre_price_quarterly' => '10,50',
            'membre_price_semiannual' => '20,00',
            'membre_price_yearly' => '99,99',
            'membre_highlight_quarterly' => '',
            'membre_highlight_semiannual' => '',
            'membre_highlight_yearly' => '',
            'membre_trial_days' => ['quarterly' => '0', 'semiannual' => '0', 'yearly' => '0'],
            'is_active' => '1',
            'auto_renew_default' => '1',
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.subscriptions.plans.membre.update'), $payload)
            ->assertRedirect(route('admin.subscriptions.plans.index'));

        $this->assertSame(10.5, (float) SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'])->value('price'));
        $this->assertSame(20.0, (float) SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['semiannual'])->value('price'));
        $this->assertSame(99.99, (float) SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'])->value('price'));
    }

    public function test_membre_update_creates_missing_semiannual_and_yearly_plans(): void
    {
        SubscriptionPlan::create([
            'name' => 'Réseau — Trimestriel',
            'slug' => SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'],
            'description' => 'd',
            'plan_type' => 'recurring',
            'billing_period' => 'quarterly',
            'price' => 10,
            'annual_discount_percent' => 0,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
            'content_id' => null,
            'metadata' => [
                'community_premium' => true,
                'community_display_order' => 0,
                'label' => 'Trimestriel',
            ],
        ]);

        $this->assertNull(SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['semiannual'])->first());
        $this->assertNull(SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'])->first());

        $payload = [
            'name' => 'Réseau',
            'description' => 'Desc',
            'membre_price_quarterly' => '11',
            'membre_price_semiannual' => '22',
            'membre_price_yearly' => '33',
            'membre_highlight_quarterly' => '',
            'membre_highlight_semiannual' => '',
            'membre_highlight_yearly' => '',
            'membre_trial_days' => ['quarterly' => '3', 'semiannual' => '0', 'yearly' => '7'],
            'is_active' => '1',
            'auto_renew_default' => '1',
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.subscriptions.plans.membre.update'), $payload)
            ->assertRedirect(route('admin.subscriptions.plans.index'));

        $this->assertSame(11.0, (float) SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'])->value('price'));
        $this->assertSame(22.0, (float) SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['semiannual'])->value('price'));
        $this->assertSame(33.0, (float) SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'])->value('price'));
        $this->assertSame(3, (int) SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'])->value('trial_days'));
        $this->assertSame(0, (int) SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['semiannual'])->value('trial_days'));
        $this->assertSame(7, (int) SubscriptionPlan::query()->where('slug', SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['yearly'])->value('trial_days'));
    }

    public function test_store_redirects_with_flash_when_member_slugs_already_exist(): void
    {
        SubscriptionPlan::create([
            'name' => 'Réseau Membre Herime — Trimestriel',
            'slug' => SubscriptionPlan::MEMBER_COMMUNITY_SLUGS['quarterly'],
            'description' => 'x',
            'plan_type' => 'recurring',
            'billing_period' => 'quarterly',
            'price' => 1,
            'annual_discount_percent' => 0,
            'trial_days' => 0,
            'is_active' => true,
            'auto_renew_default' => true,
            'content_id' => null,
            'metadata' => ['community_premium' => true],
        ]);

        $payload = [
            'name' => 'Réseau Test',
            'membre_price_quarterly' => '1',
            'membre_price_semiannual' => '2',
            'membre_price_yearly' => '3',
            'membre_trial_days' => ['quarterly' => '0', 'semiannual' => '0', 'yearly' => '0'],
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.subscriptions.plans.store'), $payload)
            ->assertRedirect(route('admin.subscriptions.plans.index'))
            ->assertSessionHas('error');
    }
}
