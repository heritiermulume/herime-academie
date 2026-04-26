<?php

namespace Tests\Unit;

use App\Support\RichText;
use Tests\TestCase;

class RichTextUtf8Test extends TestCase
{
    public function test_sanitize_preserves_utf8_in_allowed_html(): void
    {
        $html = '<p>DIGITAL COACHING : stratégique, conçue pour t’aider à lancer — ⏱</p>';
        $this->assertSame($html, RichText::sanitize($html));
    }

    public function test_sanitize_preserves_utf8_plain_fragment(): void
    {
        $text = "Ligne 1\nLigne 2 avec été et façade";
        $this->assertSame($text, RichText::sanitize($text));
    }

    public function test_to_html_outputs_utf8_plain_text(): void
    {
        $text = 'Bonjour été';
        $this->assertStringContainsString('été', RichText::toHtml($text));
    }
}
