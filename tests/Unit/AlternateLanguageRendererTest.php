<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Renderers\AlternateLanguageRenderer;
use Zarbin\Seo\Tests\TestCase;

final class AlternateLanguageRendererTest extends TestCase
{
    public function test_renders_hreflang_links(): void
    {
        $html = $this->renderer()->render(SeoData::make([
            'alternate_languages' => [
                'fa' => 'https://example.com/fa',
                'en' => 'https://example.com/en',
            ],
        ]));

        $this->assertStringContainsString('<link rel="alternate" hreflang="fa" href="https://example.com/fa">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="en" href="https://example.com/en">', $html);
    }

    public function test_renders_x_default(): void
    {
        $html = $this->renderer()->render(SeoData::make([
            'alternate_languages' => [
                'x-default' => 'https://example.com',
            ],
        ]));

        $this->assertStringContainsString('hreflang="x-default"', $html);
    }

    public function test_skips_empty_values(): void
    {
        $html = $this->renderer()->render(SeoData::make([
            'alternate_languages' => [
                'fa' => 'https://example.com/fa',
                'en' => '',
            ],
        ]));

        $this->assertStringContainsString('hreflang="fa"', $html);
        $this->assertStringNotContainsString('hreflang="en"', $html);
    }

    public function test_respects_disabled_feature(): void
    {
        config()->set('zarbin-seo.features.alternate_languages', false);

        $this->assertSame('', $this->renderer()->render(SeoData::make([
            'alternate_languages' => ['fa' => 'https://example.com/fa'],
        ])));
    }

    public function test_respects_disabled_hreflang_generation(): void
    {
        config()->set('zarbin-seo.localization.generate_hreflang', false);

        $this->assertSame('', $this->renderer()->render(SeoData::make([
            'alternate_languages' => ['fa' => 'https://example.com/fa'],
        ])));
    }

    private function renderer(): AlternateLanguageRenderer
    {
        return new AlternateLanguageRenderer;
    }
}
