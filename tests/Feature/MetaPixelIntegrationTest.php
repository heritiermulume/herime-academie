<?php

namespace Tests\Feature;

use App\Models\MetaEvent;
use App\Models\MetaEventTrigger;
use App\Models\MetaPixel;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetaPixelIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_includes_official_meta_pixel_snippet_when_enabled_and_pixel_exists(): void
    {
        Setting::set('meta_tracking_enabled', true, 'boolean');

        $pixelId = '871745749003114';
        MetaPixel::create([
            'pixel_id' => $pixelId,
            'name' => 'Pixel test',
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('https://connect.facebook.net/en_US/fbevents.js', false)
            ->assertSee("fbq('init', '{$pixelId}')", false)
            ->assertSee("fbq('track', 'PageView')", false)
            ->assertSee("https://www.facebook.com/tr?id={$pixelId}&ev=PageView&noscript=1", false);
    }

    public function test_meta_triggers_are_embedded_and_pageview_trigger_is_excluded_from_dynamic_triggers(): void
    {
        Setting::set('meta_tracking_enabled', true, 'boolean');

        MetaPixel::create([
            'pixel_id' => '111',
            'is_active' => true,
        ]);

        $purchase = MetaEvent::create([
            'event_name' => 'Purchase',
            'is_standard' => true,
            'is_active' => true,
            'default_payload' => ['currency' => 'USD'],
        ]);

        MetaEventTrigger::create([
            'meta_event_id' => $purchase->id,
            'trigger_type' => 'page_load',
            'match_path_pattern' => '__all__',
            'payload' => ['value' => 10],
            'is_active' => true,
            'once_per_page' => true,
        ]);

        $pageView = MetaEvent::create([
            'event_name' => 'PageView',
            'is_standard' => true,
            'is_active' => true,
        ]);

        MetaEventTrigger::create([
            'meta_event_id' => $pageView->id,
            'trigger_type' => 'page_load',
            'match_path_pattern' => '__all__',
            'is_active' => true,
            'once_per_page' => true,
        ]);

        $resp = $this->get('/')->assertOk();

        $resp->assertSee('__META_TRIGGERS__', false);
        $resp->assertSee('"event_name":"Purchase"', false);
        $resp->assertSee('"match_path_pattern":"__all__"', false);
        $resp->assertDontSee('"event_name":"PageView"', false);
    }

    public function test_removed_meta_capi_endpoint_returns_404(): void
    {
        $this->post('/meta/capi', [])->assertNotFound();
    }

    public function test_admin_requires_page_selection_for_page_load_triggers_but_accepts_all_pages(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $event = MetaEvent::create([
            'event_name' => 'Lead',
            'is_standard' => true,
            'is_active' => true,
        ]);

        // page_load requires an explicit page selection (including "__all__")
        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'meta_action' => 'meta_trigger_create',
                'meta_event_id' => $event->id,
                'trigger_type' => 'page_load',
                // missing match_path_pattern
                'trigger_is_active' => 'on',
                'once_per_page' => 'on',
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('error');

        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'meta_action' => 'meta_trigger_create',
                'meta_event_id' => $event->id,
                'trigger_type' => 'page_load',
                'match_path_pattern' => '__all__',
                'trigger_is_active' => 'on',
                'once_per_page' => 'on',
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('success');
    }
}

