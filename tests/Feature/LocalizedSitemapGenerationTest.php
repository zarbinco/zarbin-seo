<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Support\Facades\Route;
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
        $this->registerProductsRoute();

        $fa = (new SitemapGenerator)->render('fa');
        $en = (new SitemapGenerator)->render('en');

        $this->assertStringContainsString('<loc>http://localhost/fa/products</loc>', $fa);
        $this->assertStringNotContainsString('<loc>http://localhost/en/products</loc>', $fa);
        $this->assertStringContainsString('<loc>http://localhost/en/products</loc>', $en);
        $this->assertStringNotContainsString('<loc>http://localhost/fa/products</loc>', $en);
    }

    public function test_urls_without_locale_remain_combined_for_backward_compatibility(): void
    {
        $this->configureLocalizedSitemaps();
        $this->registerProductsRoute();

        $urls = (new SitemapGenerator)->urls();

        $this->assertSame([
            'http://localhost/fa/products',
            'http://localhost/en/products',
        ], array_map(fn ($url): string => $url->loc, $urls));
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

    private function registerProductsRoute(): void
    {
        Route::get('/{locale}/products', fn (string $locale): string => $locale)->name('generation.products');
        config()->set('zarbin-seo.routes', [
            'generation.products' => [
                'sitemap' => true,
            ],
        ]);
    }
}
