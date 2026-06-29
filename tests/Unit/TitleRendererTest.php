<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Renderers\TitleRenderer;

final class TitleRendererTest extends TestCase
{
    public function test_renders_title_with_site_name(): void
    {
        $html = $this->renderer()->render(SeoData::make([
            'title' => 'About Us',
            'siteName' => 'Example',
            'separator' => '|',
        ]));

        $this->assertSame('<title>About Us | Example</title>', $html);
    }

    public function test_does_not_duplicate_site_name(): void
    {
        $html = $this->renderer()->render(SeoData::make([
            'title' => 'About Us | Example',
            'siteName' => 'Example',
        ]));

        $this->assertSame('<title>About Us | Example</title>', $html);
    }

    public function test_falls_back_to_site_name(): void
    {
        $html = $this->renderer()->render(SeoData::make([
            'siteName' => 'Example',
        ]));

        $this->assertSame('<title>Example</title>', $html);
    }

    public function test_returns_empty_string_when_no_title_or_site_name(): void
    {
        $this->assertSame('', $this->renderer()->render(SeoData::make()));
    }

    private function renderer(): TitleRenderer
    {
        return new TitleRenderer;
    }
}
