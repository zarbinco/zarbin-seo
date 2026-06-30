<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Zarbin\Seo\Generators\SitemapGenerator;
use Zarbin\Seo\Tests\TestCase;

final class LocalizedSitemapGenerationTest extends TestCase
{
    public function test_index_includes_configured_localized_sitemap_paths(): void
    {
        $this->configureLocalizedSitemaps();

        $index = (new SitemapGenerator)->index();

        $this->assertSame([
            'https://example.com/sitemap-fa.xml',
            'https://example.com/sitemap-en.xml',
        ], array_map(fn (array $entry): string => $entry['loc'], $index));
    }

    public function test_index_uses_sitemap_base_url_for_localized_sitemap_paths(): void
    {
        $this->configureLocalizedSitemaps();
        config()->set('zarbin-seo.sitemap.base_url', 'http://sunich.test/');

        $index = (new SitemapGenerator)->index();

        $this->assertSame([
            'http://sunich.test/sitemap-fa.xml',
            'http://sunich.test/sitemap-en.xml',
        ], array_map(fn (array $entry): string => $entry['loc'], $index));
    }

    public function test_index_falls_back_to_default_sitemap_path_when_localized_paths_are_empty(): void
    {
        config()->set('app.url', 'https://example.com');
        config()->set('zarbin-seo.sitemap.localized_paths', []);

        $index = (new SitemapGenerator)->index();

        $this->assertCount(1, $index);
        $this->assertSame('https://example.com/sitemap.xml', $index[0]['loc']);
    }

    public function test_render_with_locale_only_includes_that_locale_url(): void
    {
        $this->configureLocalizedSitemaps();
        $this->registerScopedRouteEntries();

        $fa = (new SitemapGenerator)->render('fa');
        $en = (new SitemapGenerator)->render('en');

        $this->assertStringContainsString('<loc>http://sunich.test/fa/products</loc>', $fa);
        $this->assertStringNotContainsString('<loc>http://sunich.test/en/products</loc>', $fa);
        $this->assertStringContainsString('<loc>http://sunich.test/en/products</loc>', $en);
        $this->assertStringNotContainsString('<loc>http://sunich.test/fa/products</loc>', $en);
    }

    public function test_route_locale_scoping_supports_locale_locales_and_unscoped_entries(): void
    {
        $this->configureLocalizedSitemaps();
        $this->registerScopedRouteEntries();

        $fa = (new SitemapGenerator)->render('fa');
        $en = (new SitemapGenerator)->render('en');

        $this->assertStringContainsString('<loc>http://sunich.test/fa/products</loc>', $fa);
        $this->assertStringNotContainsString('<loc>http://sunich.test/en/products</loc>', $fa);
        $this->assertStringContainsString('<loc>http://sunich.test/shared-products</loc>', $fa);
        $this->assertStringContainsString('<loc>http://sunich.test/all-products</loc>', $fa);

        $this->assertStringContainsString('<loc>http://sunich.test/en/products</loc>', $en);
        $this->assertStringNotContainsString('<loc>http://sunich.test/fa/products</loc>', $en);
        $this->assertStringContainsString('<loc>http://sunich.test/shared-products</loc>', $en);
        $this->assertStringContainsString('<loc>http://sunich.test/all-products</loc>', $en);
    }

    public function test_urls_without_locale_remain_combined_for_backward_compatibility(): void
    {
        $this->configureLocalizedSitemaps();
        $this->registerScopedRouteEntries();

        $urls = (new SitemapGenerator)->urls();

        $this->assertSame([
            'http://sunich.test/fa/products',
            'http://sunich.test/shared-products',
            'http://sunich.test/all-products',
            'http://sunich.test/en/products',
        ], array_map(fn ($url): string => $url->loc, $urls));
    }

    public function test_malformed_route_locale_scope_does_not_crash_and_is_ignored(): void
    {
        $this->configureLocalizedSitemaps();
        config()->set('zarbin-seo.routes', [
            'generation.malformed.locale' => [
                'locale' => ['bad'],
                'locales' => 'also-bad',
                'canonical' => 'http://sunich.test/malformed',
                'sitemap' => true,
            ],
        ]);

        $xml = (new SitemapGenerator)->render('fa');

        $this->assertStringContainsString('<loc>http://sunich.test/malformed</loc>', $xml);
    }

    public function test_malformed_localized_paths_do_not_crash_index_generation(): void
    {
        config()->set('app.url', 'https://example.com');
        config()->set('zarbin-seo.sitemap.localized_paths', 'not-an-array');

        $index = (new SitemapGenerator)->index();

        $this->assertCount(1, $index);
        $this->assertSame('https://example.com/sitemap.xml', $index[0]['loc']);
    }

    private function configureLocalizedSitemaps(): void
    {
        config()->set('app.url', 'https://example.com');
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['fa', 'en']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');
        config()->set('zarbin-seo.localization.url_strategy', 'prefixed_all');
        config()->set('zarbin-seo.localization.route_parameter', 'locale');
        config()->set('zarbin-seo.sitemap.include_alternates', false);
        config()->set('zarbin-seo.sitemap.localized_paths', [
            'fa' => 'sitemap-fa.xml',
            'en' => 'sitemap-en.xml',
        ]);
    }

    private function registerScopedRouteEntries(): void
    {
        config()->set('zarbin-seo.routes', [
            'generation.products.fa' => [
                'locale' => 'fa',
                'canonical' => 'http://sunich.test/fa/products',
                'sitemap' => true,
            ],
            'generation.products.en' => [
                'locale' => 'en',
                'canonical' => 'http://sunich.test/en/products',
                'sitemap' => true,
            ],
            'generation.shared' => [
                'locales' => ['fa', 'en'],
                'canonical' => 'http://sunich.test/shared-products',
                'sitemap' => true,
            ],
            'generation.unscoped' => [
                'canonical' => 'http://sunich.test/all-products',
                'sitemap' => true,
            ],
        ]);
    }
}
