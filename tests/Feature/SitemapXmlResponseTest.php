<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Foundation\Application;
use Illuminate\Testing\TestResponse;
use Zarbin\Seo\Tests\TestCase;

final class SitemapXmlResponseTest extends TestCase
{
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
    }

    public function test_en_localized_sitemap_http_response_is_xml_and_locale_scoped(): void
    {
        $this->configureLocalizedRoutes();

        $response = $this->get('/sitemap-en.xml');

        $this->assertXmlResponse($response, 'application/xml');
        $response->assertSee('<urlset', false);
        $response->assertSee('<loc>https://example.test/en/products</loc>', false);
        $response->assertDontSee('<loc>https://example.test/fa/products</loc>', false);
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
                'sitemap' => true,
            ],
            'xml.products.en' => [
                'locale' => 'en',
                'canonical' => 'https://example.test/en/products',
                'sitemap' => true,
            ],
        ]);
    }
}
