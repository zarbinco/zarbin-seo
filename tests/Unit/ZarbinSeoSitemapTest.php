<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Tests\TestCase;

final class ZarbinSeoSitemapTest extends TestCase
{
    public function test_sitemap_urls_returns_array(): void
    {
        config()->set('zarbin-seo.routes', [
            'manager.sitemap' => [
                'canonical' => 'https://example.com/manager',
                'sitemap' => true,
            ],
        ]);

        $urls = seo()->sitemapUrls();

        $this->assertCount(1, $urls);
        $this->assertSame('https://example.com/manager', $urls[0]->loc);
    }

    public function test_sitemap_returns_xml(): void
    {
        config()->set('zarbin-seo.routes', [
            'manager.xml' => [
                'canonical' => 'https://example.com/manager-xml',
                'sitemap' => true,
            ],
        ]);

        $this->assertStringContainsString('<urlset', seo()->sitemap());
    }

    public function test_sitemap_index_returns_xml(): void
    {
        config()->set('app.url', 'https://example.com');

        $this->assertStringContainsString('<sitemapindex', seo()->sitemapIndex());
    }

    public function test_robots_txt_returns_robots_content(): void
    {
        $this->assertStringContainsString('User-agent: *', seo()->robotsTxt());
    }
}
