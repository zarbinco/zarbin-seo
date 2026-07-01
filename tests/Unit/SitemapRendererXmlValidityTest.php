<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use SimpleXMLElement;
use Zarbin\Seo\Data\SitemapUrl;
use Zarbin\Seo\Renderers\SitemapRenderer;
use Zarbin\Seo\Tests\TestCase;

final class SitemapRendererXmlValidityTest extends TestCase
{
    private const SITEMAP_NAMESPACE = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    private const XHTML_NAMESPACE = 'http://www.w3.org/1999/xhtml';

    public function test_render_without_alternates_is_valid_sitemap_xml_without_xhtml_namespace(): void
    {
        $xml = (new SitemapRenderer)->render([
            SitemapUrl::make([
                'loc' => 'https://example.com/products',
            ]),
        ]);

        $parsed = $this->parseXml($xml);

        $this->assertStringStartsWith('<?xml', $xml);
        $this->assertSame('urlset', $parsed->getName());
        $this->assertSame(self::SITEMAP_NAMESPACE, $parsed->getNamespaces()[''] ?? null);
        $this->assertStringNotContainsString('xmlns:xhtml', $xml);
        $this->assertStringNotContainsString('xhtml:link', $xml);
    }

    public function test_render_with_alternates_is_valid_xml_with_parseable_xhtml_links(): void
    {
        $xml = (new SitemapRenderer)->render([
            SitemapUrl::make([
                'loc' => 'https://example.com/fa/products',
                'changefreq' => 'weekly',
                'priority' => 0.9,
                'alternates' => [
                    'fa' => 'https://example.com/fa/products',
                    'en' => 'https://example.com/en/products',
                    'x-default' => 'https://example.com/fa/products',
                ],
            ]),
        ]);

        $parsed = $this->parseXml($xml);
        $links = $this->xhtmlLinks($parsed);

        $this->assertStringStartsWith('<?xml', $xml);
        $this->assertSame('urlset', $parsed->getName());
        $this->assertStringContainsString('xmlns:xhtml="http://www.w3.org/1999/xhtml"', $xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="fa" href="https://example.com/fa/products" />', $xml);
        $this->assertCount(3, $links);
        $this->assertSame('alternate', (string) $links[0]['rel']);
        $this->assertSame('fa', (string) $links[0]['hreflang']);
        $this->assertSame('https://example.com/fa/products', (string) $links[0]['href']);
    }

    public function test_render_escapes_query_strings_in_loc_and_alternate_href(): void
    {
        $xml = (new SitemapRenderer)->render([
            SitemapUrl::make([
                'loc' => 'https://example.com/fa/products?pack=family&visible=1',
                'alternates' => [
                    'en' => 'https://example.com/en/products?pack=family&visible=1',
                ],
            ]),
        ]);

        $parsed = $this->parseXml($xml);

        $this->assertSame('urlset', $parsed->getName());
        $this->assertStringContainsString('https://example.com/fa/products?pack=family&amp;visible=1', $xml);
        $this->assertStringContainsString('href="https://example.com/en/products?pack=family&amp;visible=1"', $xml);
    }

    public function test_render_index_is_valid_sitemap_index_xml(): void
    {
        $xml = (new SitemapRenderer)->renderIndex([
            ['loc' => 'https://example.com/sitemap.xml', 'lastmod' => '2026-01-01'],
        ]);

        $parsed = $this->parseXml($xml);

        $this->assertStringStartsWith('<?xml', $xml);
        $this->assertSame('sitemapindex', $parsed->getName());
        $this->assertSame(self::SITEMAP_NAMESPACE, $parsed->getNamespaces()[''] ?? null);
    }

    private function parseXml(string $xml): SimpleXMLElement
    {
        if (! function_exists('simplexml_load_string')) {
            $this->markTestSkipped('SimpleXML extension is not available.');
        }

        $parsed = simplexml_load_string($xml);

        $this->assertInstanceOf(SimpleXMLElement::class, $parsed);

        return $parsed;
    }

    /**
     * @return array<int, SimpleXMLElement>
     */
    private function xhtmlLinks(SimpleXMLElement $xml): array
    {
        $xml->registerXPathNamespace('xhtml', self::XHTML_NAMESPACE);
        $links = $xml->xpath('//xhtml:link');

        $this->assertIsArray($links);

        return $links;
    }
}
