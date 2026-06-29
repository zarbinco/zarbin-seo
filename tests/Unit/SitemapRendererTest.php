<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\SitemapUrl;
use Zarbin\Seo\Renderers\SitemapRenderer;
use Zarbin\Seo\Tests\TestCase;

final class SitemapRendererTest extends TestCase
{
    public function test_renders_url_sitemap_xml(): void
    {
        $xml = (new SitemapRenderer)->render([
            SitemapUrl::make([
                'loc' => 'https://example.com/posts?sort=new&visible=1',
                'lastmod' => '2026-01-01',
                'changefreq' => 'weekly',
                'priority' => 0.8,
                'alternates' => [
                    'fa' => 'https://example.com/fa/posts',
                    'x-default' => 'https://example.com/posts',
                ],
            ]),
        ]);

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringContainsString('<loc>https://example.com/posts?sort=new&amp;visible=1</loc>', $xml);
        $this->assertStringContainsString('<lastmod>2026-01-01</lastmod>', $xml);
        $this->assertStringContainsString('<changefreq>weekly</changefreq>', $xml);
        $this->assertStringContainsString('<priority>0.8</priority>', $xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="fa" href="https://example.com/fa/posts" />', $xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="x-default" href="https://example.com/posts" />', $xml);
    }

    public function test_renders_sitemap_index(): void
    {
        $xml = (new SitemapRenderer)->renderIndex([
            ['loc' => 'https://example.com/sitemap.xml', 'lastmod' => '2026-01-01'],
        ]);

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertStringContainsString('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $xml);
        $this->assertStringContainsString('<loc>https://example.com/sitemap.xml</loc>', $xml);
        $this->assertStringContainsString('<lastmod>2026-01-01</lastmod>', $xml);
    }
}
