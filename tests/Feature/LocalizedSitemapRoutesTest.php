<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Tests\TestCase;

final class LocalizedSitemapRoutesTest extends TestCase
{
    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('app.url', 'https://example.com');
        $app['config']->set('zarbin-seo.localization.enabled', true);
        $app['config']->set('zarbin-seo.localization.locales', ['fa', 'en']);
        $app['config']->set('zarbin-seo.localization.default_locale', 'fa');
        $app['config']->set('zarbin-seo.localization.url_strategy', 'prefixed_all');
        $app['config']->set('zarbin-seo.localization.route_parameter', 'locale');
        $app['config']->set('zarbin-seo.sitemap.include_alternates', false);
        $app['config']->set('zarbin-seo.sitemap.localized_paths', [
            'fa' => 'sitemap-fa.xml',
            'en' => 'sitemap-en.xml',
        ]);
    }

    public function test_localized_sitemap_route_names_exist(): void
    {
        $this->assertTrue(Route::has('zarbin-seo.sitemap.localized.fa'));
        $this->assertTrue(Route::has('zarbin-seo.sitemap.localized.en'));
    }

    public function test_fa_localized_sitemap_route_returns_fa_xml(): void
    {
        $this->registerProductsRoute();

        $response = $this->get('/sitemap-fa.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<urlset', false);
        $response->assertSee('<loc>http://sunich.test/fa/products</loc>', false);
        $response->assertDontSee('<loc>http://sunich.test/en/products</loc>', false);
    }

    public function test_en_localized_sitemap_route_returns_en_xml(): void
    {
        $this->registerProductsRoute();

        $response = $this->get('/sitemap-en.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<urlset', false);
        $response->assertSee('<loc>http://sunich.test/en/products</loc>', false);
        $response->assertDontSee('<loc>http://sunich.test/fa/products</loc>', false);
    }

    private function registerProductsRoute(): void
    {
        config()->set('zarbin-seo.routes', [
            'localized.routes.products.fa' => [
                'locale' => 'fa',
                'canonical' => 'http://sunich.test/fa/products',
                'sitemap' => true,
            ],
            'localized.routes.products.en' => [
                'locale' => 'en',
                'canonical' => 'http://sunich.test/en/products',
                'sitemap' => true,
            ],
        ]);
    }
}
