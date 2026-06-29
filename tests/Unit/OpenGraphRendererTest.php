<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Renderers\OpenGraphRenderer;
use Zarbin\Seo\Tests\TestCase;

final class OpenGraphRendererTest extends TestCase
{
    public function test_renders_open_graph_tags(): void
    {
        $html = $this->renderer()->render($this->data());

        $this->assertStringContainsString('<meta property="og:title" content="About">', $html);
        $this->assertStringContainsString('<meta property="og:description" content="About description">', $html);
        $this->assertStringContainsString('<meta property="og:url" content="https://example.com/about">', $html);
        $this->assertStringContainsString('<meta property="og:image" content="https://example.com/about.jpg">', $html);
        $this->assertStringContainsString('<meta property="og:type" content="Article">', $html);
        $this->assertStringContainsString('<meta property="og:site_name" content="Example">', $html);
        $this->assertStringContainsString('<meta property="og:locale" content="en">', $html);
    }

    public function test_respects_disabled_open_graph_feature(): void
    {
        config()->set('zarbin-seo.features.open_graph', false);

        $this->assertSame('', $this->renderer()->render($this->data()));
    }

    private function data(): SeoData
    {
        return SeoData::make([
            'title' => 'About',
            'description' => 'About description',
            'canonical' => 'https://example.com/about',
            'image' => 'https://example.com/about.jpg',
            'type' => 'Article',
            'siteName' => 'Example',
            'locale' => 'en',
        ]);
    }

    private function renderer(): OpenGraphRenderer
    {
        return new OpenGraphRenderer;
    }
}
