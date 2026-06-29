<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Generators\SitemapGenerator;
use Zarbin\Seo\Tests\TestCase;

final class SitemapGeneratorTest extends TestCase
{
    public function test_generates_route_only_sitemap_urls_from_config(): void
    {
        Route::get('/generator-home', fn (): string => 'home')->name('generator.home');
        config()->set('zarbin-seo.routes', [
            'generator.home' => [
                'sitemap' => true,
                'priority' => 1.0,
                'change_frequency' => 'daily',
            ],
        ]);

        $urls = (new SitemapGenerator)->urls();

        $this->assertCount(1, $urls);
        $this->assertStringEndsWith('/generator-home', $urls[0]->loc);
        $this->assertSame('1.0', $urls[0]->normalizedPriority());
        $this->assertSame('daily', $urls[0]->changefreq);
    }

    public function test_skips_route_when_sitemap_is_false(): void
    {
        config()->set('zarbin-seo.routes', [
            'generator.skip' => [
                'canonical' => 'https://example.com/skip',
                'sitemap' => false,
            ],
        ]);

        $this->assertSame([], (new SitemapGenerator)->urls());
    }

    public function test_generates_model_backed_sitemap_urls_from_sitemap_items(): void
    {
        config()->set('zarbin-seo.models.'.GeneratorSitemapItem::class, [
            'sitemap' => true,
            'sitemap_items' => [
                new GeneratorSitemapItem('https://example.com/posts/first'),
            ],
        ]);

        $urls = (new SitemapGenerator)->urls();

        $this->assertCount(1, $urls);
        $this->assertSame('https://example.com/posts/first', $urls[0]->loc);
    }

    public function test_generates_model_backed_holder_sitemap_urls(): void
    {
        config()->set('zarbin-seo.models.'.GeneratorHolderItem::class, [
            'sitemap' => true,
            'sitemap_source' => fn (): array => [
                new GeneratorHolderItem,
            ],
            'priority' => 0.9,
        ]);

        $urls = (new SitemapGenerator)->urls();

        $this->assertCount(1, $urls);
        $this->assertSame('https://example.com/products', $urls[0]->loc);
        $this->assertSame('0.9', $urls[0]->normalizedPriority());
    }

    public function test_deduplicates_loc_values(): void
    {
        config()->set('zarbin-seo.routes', [
            'generator.one' => [
                'canonical' => 'https://example.com/duplicate',
                'sitemap' => true,
            ],
            'generator.two' => [
                'canonical' => 'https://example.com/duplicate',
                'sitemap' => true,
            ],
        ]);

        $urls = (new SitemapGenerator)->urls();

        $this->assertCount(1, $urls);
    }

    public function test_handles_localization_enabled_with_multiple_locales(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['fa', 'en']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');
        config()->set('zarbin-seo.models.'.GeneratorLocalizedItem::class, [
            'sitemap' => true,
            'sitemap_items' => [
                new GeneratorLocalizedItem,
            ],
        ]);

        $urls = (new SitemapGenerator)->urls();

        $this->assertCount(2, $urls);
        $this->assertSame([
            'https://example.com/fa/localized',
            'https://example.com/en/localized',
        ], array_map(fn ($url): string => $url->loc, $urls));
    }

    public function test_render_returns_xml(): void
    {
        config()->set('zarbin-seo.routes', [
            'generator.xml' => [
                'canonical' => 'https://example.com/xml',
                'sitemap' => true,
            ],
        ]);

        $xml = (new SitemapGenerator)->render();

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertStringContainsString('<loc>https://example.com/xml</loc>', $xml);
    }

    public function test_index_returns_at_least_one_sitemap_entry(): void
    {
        config()->set('app.url', 'https://example.com');

        $index = (new SitemapGenerator)->index();

        $this->assertNotEmpty($index);
        $this->assertSame('https://example.com/sitemap.xml', $index[0]['loc']);
    }

    public function test_render_index_returns_xml(): void
    {
        config()->set('app.url', 'https://example.com');

        $xml = (new SitemapGenerator)->renderIndex();

        $this->assertStringContainsString('<sitemapindex', $xml);
        $this->assertStringContainsString('<loc>https://example.com/sitemap.xml</loc>', $xml);
    }
}

final class GeneratorSitemapItem
{
    public function __construct(private readonly string $url) {}

    public function sitemapUrl(?string $locale = null): ?string
    {
        return $this->url;
    }
}

final class GeneratorHolderItem
{
    public function sitemapUrl(?string $locale = null): ?string
    {
        return 'https://example.com/products';
    }
}

final class GeneratorLocalizedItem implements LocalizableSeo
{
    public function seoLocales(): array
    {
        return ['fa', 'en'];
    }

    public function hasSeoLocale(string $locale): bool
    {
        return true;
    }

    public function seoUrlForLocale(string $locale): ?string
    {
        return "https://example.com/{$locale}/localized";
    }
}
