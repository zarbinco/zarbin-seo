<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Renderers\OpenGraphRenderer;
use Zarbin\Seo\Renderers\TwitterCardRenderer;
use Zarbin\Seo\Tests\TestCase;

final class SocialOverrideRendererTest extends TestCase
{
    public function test_open_graph_renderer_uses_direct_extra_overrides(): void
    {
        $html = (new OpenGraphRenderer)->render(SeoData::make([
            'title' => 'Base title',
            'description' => 'Base description',
            'image' => 'https://example.com/base.jpg',
            'extra' => [
                'og_title' => 'OG title',
                'og_description' => 'OG description',
                'og_image' => 'https://example.com/og.jpg',
            ],
        ]));

        $this->assertStringContainsString('property="og:title" content="OG title"', $html);
        $this->assertStringContainsString('property="og:description" content="OG description"', $html);
        $this->assertStringContainsString('property="og:image" content="https://example.com/og.jpg"', $html);
    }

    public function test_open_graph_renderer_uses_nested_extra_overrides(): void
    {
        $html = (new OpenGraphRenderer)->render(SeoData::make([
            'title' => 'Base title',
            'extra' => [
                'open_graph' => [
                    'title' => 'Nested OG title',
                    'description' => 'Nested OG description',
                    'image' => 'https://example.com/nested-og.jpg',
                ],
            ],
        ]));

        $this->assertStringContainsString('content="Nested OG title"', $html);
        $this->assertStringContainsString('content="Nested OG description"', $html);
        $this->assertStringContainsString('content="https://example.com/nested-og.jpg"', $html);
    }

    public function test_twitter_renderer_uses_direct_extra_overrides(): void
    {
        $html = (new TwitterCardRenderer)->render(SeoData::make([
            'title' => 'Base title',
            'description' => 'Base description',
            'image' => 'https://example.com/base.jpg',
            'extra' => [
                'twitter_title' => 'Twitter title',
                'twitter_description' => 'Twitter description',
                'twitter_image' => 'https://example.com/twitter.jpg',
            ],
        ]));

        $this->assertStringContainsString('name="twitter:title" content="Twitter title"', $html);
        $this->assertStringContainsString('name="twitter:description" content="Twitter description"', $html);
        $this->assertStringContainsString('name="twitter:image" content="https://example.com/twitter.jpg"', $html);
        $this->assertStringContainsString('name="twitter:card" content="summary_large_image"', $html);
    }

    public function test_twitter_renderer_uses_nested_extra_overrides(): void
    {
        $html = (new TwitterCardRenderer)->render(SeoData::make([
            'title' => 'Base title',
            'extra' => [
                'twitter' => [
                    'title' => 'Nested Twitter title',
                    'description' => 'Nested Twitter description',
                    'image' => 'https://example.com/nested-twitter.jpg',
                ],
            ],
        ]));

        $this->assertStringContainsString('content="Nested Twitter title"', $html);
        $this->assertStringContainsString('content="Nested Twitter description"', $html);
        $this->assertStringContainsString('content="https://example.com/nested-twitter.jpg"', $html);
    }
}
