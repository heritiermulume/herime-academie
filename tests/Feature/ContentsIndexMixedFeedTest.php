<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ContentPackage;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContentsIndexMixedFeedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 15 contenus (tri « plus récents » : MIX-COURSE-15 en premier) + 3 packs.
     * Mélange attendu (3 contenus / 1 pack) : 18 entrées → page 1 = 12, page 2 = 6 (sans pack).
     */
    private function seedMixedCatalog(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create([
            'name' => 'MixCat',
            'slug' => 'mixcat-'.uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        for ($i = 1; $i <= 15; $i++) {
            $course = Course::create([
                'provider_id' => $provider->id,
                'category_id' => $category->id,
                'title' => 'MIX-COURSE-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'slug' => 'mix-course-'.$i.'-'.uniqid(),
                'description' => 'd',
                'short_description' => 'sd',
                'price' => 10,
                'is_free' => false,
                'is_published' => true,
                'is_sale_enabled' => false,
                'level' => 'beginner',
                'language' => 'fr',
            ]);
            $minutesAgo = 40 - $i;
            DB::table('contents')->where('id', $course->id)->update([
                'created_at' => now()->subMinutes($minutesAgo),
                'updated_at' => now()->subMinutes($minutesAgo),
            ]);
        }

        foreach ([1, 2, 3] as $p) {
            ContentPackage::create([
                'title' => 'MIX-PACK-0'.$p,
                'slug' => 'mix-pack-'.$p.'-'.uniqid(),
                'price' => 50,
                'is_published' => true,
                'is_sale_enabled' => true,
                'sort_order' => $p,
            ]);
        }
    }

    public function test_contents_index_shows_mixed_feed_page_one_and_two_with_latest_sort(): void
    {
        $this->seedMixedCatalog();

        $r1 = $this->get('/contents?sort=latest');
        $r1->assertOk();
        $r1->assertSee('MIX-COURSE-15', false);
        $r1->assertSee('MIX-COURSE-13', false);
        $r1->assertSee('MIX-PACK-01', false);
        $r1->assertSee('MIX-PACK-03', false);
        $r1->assertDontSee('MIX-COURSE-04', false);

        $r2 = $this->get('/contents?sort=latest&page=2');
        $r2->assertOk();
        $r2->assertSee('MIX-COURSE-06', false);
        $r2->assertSee('MIX-COURSE-01', false);
        $r2->assertDontSee('MIX-PACK-01', false);
        $r2->assertDontSee('MIX-COURSE-15', false);
    }

    public function test_contents_index_infinite_scroll_page_one_includes_pack_and_page_two_is_courses_only(): void
    {
        $this->seedMixedCatalog();

        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ];

        $p1 = $this->withHeaders($headers)->getJson('/contents?sort=latest&infinite_scroll=1&page=1');
        $p1->assertOk();
        $p1->assertJsonStructure(['fragments', 'hasMore', 'nextPage']);
        $this->assertCount(12, $p1->json('fragments'));
        $this->assertTrue($p1->json('hasMore'));
        $this->assertSame(2, $p1->json('nextPage'));
        $html1 = implode('', $p1->json('fragments'));
        $this->assertStringContainsString('MIX-PACK-01', $html1);
        $this->assertStringContainsString('MIX-COURSE-15', $html1);

        $p2 = $this->withHeaders($headers)->getJson('/contents?sort=latest&infinite_scroll=1&page=2');
        $p2->assertOk();
        $this->assertCount(6, $p2->json('fragments'));
        $this->assertFalse($p2->json('hasMore'));
        $this->assertNull($p2->json('nextPage'));
        $html2 = implode('', $p2->json('fragments'));
        $this->assertStringContainsString('MIX-COURSE-06', $html2);
        $this->assertStringNotContainsString('MIX-PACK-01', $html2);
    }
}
