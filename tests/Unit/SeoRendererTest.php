<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Renderers\SeoRenderer;
use Zarbin\Seo\Tests\TestCase;

final class SeoRendererTest extends TestCase
{
    public function test_full_render_includes_all_supported_parts(): void
    {
        $html = $this->renderer()->render($this->data());

        $this->assertStringContainsString('<title>About | Example</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="About description">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="https://example.com/about">', $html);
        $this->assertStringContainsString('<meta name="robots" content="index, follow">', $html);
        $this->assertStringContainsString('<meta property="og:title" content="About">', $html);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
        $this->assertStringContainsString('<script type="application/ld+json">', $html);
    }

    public function test_minified_render_has_no_newlines(): void
    {
        $html = $this->renderer()->render($this->data(), true);

        $this->assertStringNotContainsString(PHP_EOL, $html);
        $this->assertStringNotContainsString("\n", $html);
    }

    public function test_segmented_methods_return_expected_parts(): void
    {
        $renderer = $this->renderer();
        $data = $this->data();

        $this->assertSame('<title>About | Example</title>', $renderer->title($data));
        $this->assertStringContainsString('<meta name="description" content="About description">', $renderer->meta($data));
        $this->assertStringContainsString('<meta property="og:title" content="About">', $renderer->openGraph($data));
        $this->assertStringContainsString('<meta name="twitter:title" content="About">', $renderer->twitter($data));
        $this->assertStringContainsString('"name":"About"', $renderer->jsonLd($data));
    }

    private function data(): SeoData
    {
        return SeoData::make([
            'title' => 'About',
            'description' => 'About description',
            'canonical' => 'https://example.com/about',
            'robots' => ['index', 'follow'],
            'image' => 'https://example.com/about.jpg',
            'siteName' => 'Example',
            'separator' => '|',
        ]);
    }

    private function renderer(): SeoRenderer
    {
        return new SeoRenderer;
    }
}
