<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Renderers\MetaRenderer;

final class MetaRendererTest extends TestCase
{
    public function test_renders_description(): void
    {
        $html = $this->renderer()->description(SeoData::make(['description' => 'About us']));

        $this->assertSame('<meta name="description" content="About us">', $html);
    }

    public function test_renders_canonical(): void
    {
        $html = $this->renderer()->canonical(SeoData::make(['canonical' => 'https://example.com/about']));

        $this->assertSame('<link rel="canonical" href="https://example.com/about">', $html);
    }

    public function test_renders_robots(): void
    {
        $html = $this->renderer()->robots(SeoData::make(['robots' => ['index', 'follow']]));

        $this->assertSame('<meta name="robots" content="index, follow">', $html);
    }

    public function test_skips_missing_values(): void
    {
        $renderer = $this->renderer();
        $data = SeoData::make();

        $this->assertSame('', $renderer->description($data));
        $this->assertSame('', $renderer->canonical($data));
        $this->assertSame('', $renderer->robots($data));
        $this->assertSame('', $renderer->basic($data));
    }

    private function renderer(): MetaRenderer
    {
        return new MetaRenderer;
    }
}
