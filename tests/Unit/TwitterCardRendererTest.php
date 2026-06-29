<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Renderers\TwitterCardRenderer;
use Zarbin\Seo\Tests\TestCase;

final class TwitterCardRendererTest extends TestCase
{
    public function test_renders_summary_large_image_when_image_exists(): void
    {
        $html = $this->renderer()->render(SeoData::make([
            'title' => 'About',
            'description' => 'About description',
            'image' => 'https://example.com/about.jpg',
        ]));

        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
        $this->assertStringContainsString('<meta name="twitter:title" content="About">', $html);
        $this->assertStringContainsString('<meta name="twitter:description" content="About description">', $html);
        $this->assertStringContainsString('<meta name="twitter:image" content="https://example.com/about.jpg">', $html);
    }

    public function test_renders_summary_when_image_is_missing(): void
    {
        $html = $this->renderer()->render(SeoData::make(['title' => 'About']));

        $this->assertStringContainsString('<meta name="twitter:card" content="summary">', $html);
    }

    public function test_respects_disabled_twitter_feature(): void
    {
        config()->set('zarbin-seo.features.twitter', false);

        $this->assertSame('', $this->renderer()->render(SeoData::make(['title' => 'About'])));
    }

    private function renderer(): TwitterCardRenderer
    {
        return new TwitterCardRenderer;
    }
}
