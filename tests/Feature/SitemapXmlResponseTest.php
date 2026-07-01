<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Foundation\Application;
use Illuminate\Testing\TestResponse;
use SimpleXMLElement;
use Zarbin\Seo\Tests\TestCase;

final class SitemapXmlResponseTest extends TestCase
{
    private const SITEMAP_NAMESPACE = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    private const XHTML_NAMESPACE = 'http://www.w3.org/1999/xhtml';

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('app.url', 'https://example.test');
        $app['config']->set('zarbin-seo.localization.enabled', true);
        $app['config']->set('zarbin-seo.localization.locales', ['fa', 'en']);
        $app['config']->set('zarbin-seo.localization.default_locale', 'fa');
        $app['config']->set('zarbin-seo.sitemap.localized_paths', [
            'fa' => 'sitemap-fa.xml',
            'en' => 'sitemap-en.xml',
        ]);
    }

    public function test_default_sitemap_http_response_is_xml(): void
    {
        config()->set('zarbin-seo.routes', [
            'xml.default.products' => [
                'canonical' => 'https://example.test/products',
                'sitemap' => true,
            ],
        ]);

        $response = $this->get('/sitemap.xml');

        $this->assertXmlResponse($response, 'application/xml');
        $response->assertSee('<urlset', false);
        $response->assertSee('</urlset>', false);
        $response->assertSee('<loc>https://example.test/products</loc>', false);
        $response->assertDontSee('xmlns:xhtml', false);
        $response->assertDontSee('xhtml:link', false);
    }

    public function test_sitemap_index_http_response_is_xml(): void
    {
        $response = $this->get('/sitemap_index.xml');

        $this->assertXmlResponse($response, 'application/xml');
        $response->assertSee('<sitemapindex', false);
        $response->assertSee('</sitemapindex>', false);
    }

    public function test_fa_localized_sitemap_http_response_is_xml_and_locale_scoped(): void
    {
        $this->configureLocalizedRoutes();

        $response = $this->get('/sitemap-fa.xml');

        $this->assertXmlResponse($response, 'application/xml');
        $response->assertSee('<urlset', false);
        $response->assertSee('<loc>https://example.test/fa/products</loc>', false);
        $response->assertDontSee('<loc>https://example.test/en/products</loc>', false);
        $response->assertDontSee('xmlns:xhtml', false);
        $response->assertDontSee('xhtml:link', false);
    }

    public function test_fa_localized_sitemap_with_alternates_is_parseable_xml(): void
    {
        $this->configureLocalizedRoutes();
        config()->set('zarbin-seo.sitemap.include_alternates', true);

        $response = $this->get('/sitemap-fa.xml');
        $xml = $this->parseXml($response->getContent());
        $links = $this->xhtmlLinks($xml);

        $this->assertXmlResponse($response, 'application/xml');
        $this->assertSame('urlset', $xml->getName());
        $this->assertSame(['https://example.test/fa/products'], $this->mainLocs($xml));
        $this->assertStringContainsString('xmlns:xhtml="http://www.w3.org/1999/xhtml"', $response->getContent());
        $this->assertNotEmpty($links);
        $this->assertContains('https://example.test/en/products', array_map(
            static fn (SimpleXMLElement $link): string => (string) $link['href'],
            $links,
        ));
    }

    public function test_localized_sitemap_without_alternates_omits_xhtml_namespace_and_links(): void
    {
        $this->configureLocalizedRoutes();
        config()->set('zarbin-seo.sitemap.include_alternates', false);

        $response = $this->get('/sitemap-fa.xml');
        $xml = $this->parseXml($response->getContent());

        $this->assertXmlResponse($response, 'application/xml');
        $this->assertSame('urlset', $xml->getName());
        $this->assertSame(['https://example.test/fa/products'], $this->mainLocs($xml));
        $this->assertStringNotContainsString('xmlns:xhtml', $response->getContent());
        $this->assertStringNotContainsString('xhtml:link', $response->getContent());
        $this->assertSame([], $this->xhtmlLinks($xml));
    }

    public function test_en_localized_sitemap_http_response_is_xml_and_locale_scoped(): void
    {
        $this->configureLocalizedRoutes();

        $response = $this->get('/sitemap-en.xml');

        $this->assertXmlResponse($response, 'application/xml');
        $response->assertSee('<urlset', false);
        $response->assertSee('<loc>https://example.test/en/products</loc>', false);
        $response->assertDontSee('<loc>https://example.test/fa/products</loc>', false);
        $response->assertDontSee('xmlns:xhtml', false);
        $response->assertDontSee('xhtml:link', false);
    }

    public function test_all_sitemap_endpoints_use_configured_text_xml_content_type(): void
    {
        config()->set('zarbin-seo.sitemap.content_type', 'text/xml; charset=UTF-8');
        config()->set('zarbin-seo.routes', [
            'xml.text.products' => [
                'canonical' => 'https://example.test/products',
                'sitemap' => true,
            ],
        ]);
        $this->configureLocalizedRoutes();

        $default = $this->get('/sitemap.xml');
        $index = $this->get('/sitemap_index.xml');
        $fa = $this->get('/sitemap-fa.xml');
        $en = $this->get('/sitemap-en.xml');

        $this->assertXmlResponse($default, 'text/xml');
        $default->assertSee('<urlset', false);

        $this->assertXmlResponse($index, 'text/xml');
        $index->assertSee('<sitemapindex', false);

        $this->assertXmlResponse($fa, 'text/xml');
        $fa->assertSee('<urlset', false);

        $this->assertXmlResponse($en, 'text/xml');
        $en->assertSee('<urlset', false);
    }

    private function assertXmlResponse(TestResponse $response, string $expectedContentType): void
    {
        $response->assertOk();

        $contentType = (string) $response->headers->get('Content-Type');

        $this->assertStringContainsString($expectedContentType, $contentType);
        $this->assertFalse(str_starts_with(mb_strtolower($contentType), 'text/html'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertStringStartsWith('<?xml', $response->getContent());
    }

    private function configureLocalizedRoutes(): void
    {
        config()->set('zarbin-seo.routes', [
            'xml.products.fa' => [
                'locale' => 'fa',
                'canonical' => 'https://example.test/fa/products',
                'localized_urls' => [
                    'fa' => 'https://example.test/fa/products',
                    'en' => 'https://example.test/en/products',
                ],
                'sitemap' => true,
            ],
            'xml.products.en' => [
                'locale' => 'en',
                'canonical' => 'https://example.test/en/products',
                'localized_urls' => [
                    'fa' => 'https://example.test/fa/products',
                    'en' => 'https://example.test/en/products',
                ],
                'sitemap' => true,
            ],
        ]);
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
     * @return array<int, string>
     */
    private function mainLocs(SimpleXMLElement $xml): array
    {
        $xml->registerXPathNamespace('sm', self::SITEMAP_NAMESPACE);
        $locs = $xml->xpath('//sm:url/sm:loc');

        $this->assertIsArray($locs);

        return array_map(static fn (SimpleXMLElement $loc): string => (string) $loc, $locs);
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
