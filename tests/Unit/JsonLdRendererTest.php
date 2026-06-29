<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Renderers\JsonLdRenderer;
use Zarbin\Seo\Tests\TestCase;

final class JsonLdRendererTest extends TestCase
{
    public function test_renders_basic_json_ld(): void
    {
        $html = $this->renderer()->render(SeoData::make([
            'title' => 'About',
            'description' => 'About description',
            'canonical' => 'https://example.com/about',
            'image' => 'https://example.com/about.jpg',
            'type' => 'Article',
        ]));

        $this->assertStringStartsWith('<script type="application/ld+json">', $html);
        $this->assertStringContainsString('"@context":"https://schema.org"', $html);
        $this->assertStringContainsString('"@type":"Article"', $html);
        $this->assertStringContainsString('"name":"About"', $html);
        $this->assertStringContainsString('"description":"About description"', $html);
        $this->assertStringContainsString('"url":"https://example.com/about"', $html);
        $this->assertStringContainsString('"image":"https://example.com/about.jpg"', $html);
    }

    public function test_uses_web_page_fallback_type(): void
    {
        $html = $this->renderer()->render(SeoData::make(['title' => 'About']));

        $this->assertStringContainsString('"@type":"WebPage"', $html);
    }

    public function test_respects_disabled_schema_feature(): void
    {
        config()->set('zarbin-seo.features.schema', false);

        $this->assertSame('', $this->renderer()->render(SeoData::make(['title' => 'About'])));
    }

    public function test_uses_unescaped_unicode_for_persian_text(): void
    {
        $html = $this->renderer()->render(SeoData::make([
            'title' => 'درباره ما',
        ]));

        $this->assertStringContainsString('درباره ما', $html);
        $this->assertStringNotContainsString('\u062f', $html);
    }

    private function renderer(): JsonLdRenderer
    {
        return new JsonLdRenderer;
    }
}
