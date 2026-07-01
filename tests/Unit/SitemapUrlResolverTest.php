<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use DateTimeImmutable;
use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Contracts\Seoable;
use Zarbin\Seo\Contracts\Sitemapable;
use Zarbin\Seo\Resolvers\SitemapUrlResolver;
use Zarbin\Seo\Tests\TestCase;

final class SitemapUrlResolverTest extends TestCase
{
    public function test_resolves_route_sitemap_url(): void
    {
        Route::get('/about', fn (): string => 'about')->name('resolver.about');
        config()->set('zarbin-seo.routes.resolver.about.sitemap', true);

        $url = $this->resolver()->fromRoute('resolver.about');

        $this->assertNotNull($url);
        $this->assertStringEndsWith('/about', $url->loc);
    }

    public function test_resolves_model_sitemap_url_from_sitemapable(): void
    {
        $url = $this->resolver()->fromSource(new ResolverSitemapableModel);

        $this->assertNotNull($url);
        $this->assertSame('https://example.com/sitemapable', $url->loc);
        $this->assertSame('0.9', $url->normalizedPriority());
        $this->assertSame('daily', $url->changefreq);
        $this->assertSame('2026-01-01T00:00:00+00:00', $url->normalizedLastModified());
    }

    public function test_resolves_model_sitemap_url_from_methods(): void
    {
        $url = $this->resolver()->fromSource(new ResolverMethodSitemapModel);

        $this->assertNotNull($url);
        $this->assertSame('https://example.com/method', $url->loc);
    }

    public function test_resolves_model_sitemap_url_from_zero_argument_methods(): void
    {
        $url = $this->resolver()->fromSource(new ResolverZeroArgumentSitemapModel);

        $this->assertNotNull($url);
        $this->assertSame('https://example.com/zero-argument', $url->loc);
    }

    public function test_skips_model_when_should_be_in_sitemap_is_false(): void
    {
        $this->assertNull($this->resolver()->fromSource(new ResolverSkippedSitemapModel));
    }

    public function test_skips_model_with_noindex_robots(): void
    {
        $this->assertNull($this->resolver()->fromSource(new ResolverNoindexSitemapModel));
    }

    public function test_uses_config_priority_and_change_frequency(): void
    {
        config()->set('zarbin-seo.models.'.ResolverConfiguredSitemapModel::class, [
            'priority' => 0.7,
            'change_frequency' => 'monthly',
        ]);

        $url = $this->resolver()->fromSource(new ResolverConfiguredSitemapModel);

        $this->assertNotNull($url);
        $this->assertSame('0.7', $url->normalizedPriority());
        $this->assertSame('monthly', $url->changefreq);
    }

    public function test_method_priority_and_change_frequency_beat_config(): void
    {
        config()->set('zarbin-seo.models.'.ResolverMethodSitemapModel::class, [
            'priority' => 0.1,
            'change_frequency' => 'yearly',
        ]);

        $url = $this->resolver()->fromSource(new ResolverMethodSitemapModel);

        $this->assertNotNull($url);
        $this->assertSame('0.4', $url->normalizedPriority());
        $this->assertSame('hourly', $url->changefreq);
    }

    public function test_includes_alternates_when_enabled(): void
    {
        $this->enableLocalization();
        config()->set('zarbin-seo.sitemap.include_alternates', true);

        $url = $this->resolver()->fromSource(new ResolverLocalizedSitemapModel, 'fa');

        $this->assertNotNull($url);
        $this->assertSame([
            'fa' => 'https://example.com/fa/localized',
            'en' => 'https://example.com/en/localized',
        ], $url->alternates);
    }

    public function test_does_not_include_alternates_by_default(): void
    {
        $this->enableLocalization();

        $url = $this->resolver()->fromSource(new ResolverLocalizedSitemapModel, 'fa');

        $this->assertNotNull($url);
        $this->assertSame([], $url->alternates);
    }

    public function test_does_not_throw_on_missing_routes(): void
    {
        config()->set('zarbin-seo.routes.missing.route.sitemap', true);

        $this->assertNull($this->resolver()->fromRoute('missing.route'));
    }

    private function resolver(): SitemapUrlResolver
    {
        return new SitemapUrlResolver;
    }

    private function enableLocalization(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['fa', 'en']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');
    }
}

final class ResolverSitemapableModel implements Sitemapable
{
    public function shouldBeInSitemap(?string $locale = null): bool
    {
        return true;
    }

    public function sitemapUrl(?string $locale = null): ?string
    {
        return 'https://example.com/sitemapable';
    }

    public function sitemapPriority(?string $locale = null): float|int|null
    {
        return 0.9;
    }

    public function sitemapChangeFrequency(?string $locale = null): ?string
    {
        return 'daily';
    }

    public function sitemapLastModified(?string $locale = null): \DateTimeInterface|string|null
    {
        return new DateTimeImmutable('2026-01-01T00:00:00+00:00');
    }
}

final class ResolverMethodSitemapModel
{
    public function sitemapUrl(?string $locale = null): ?string
    {
        return 'https://example.com/method';
    }

    public function sitemapPriority(?string $locale = null): float
    {
        return 0.4;
    }

    public function sitemapChangeFrequency(?string $locale = null): string
    {
        return 'hourly';
    }
}

final class ResolverZeroArgumentSitemapModel
{
    public function sitemapUrl(): ?string
    {
        return 'https://example.com/zero-argument';
    }
}

final class ResolverSkippedSitemapModel
{
    public function shouldBeInSitemap(?string $locale = null): bool
    {
        return false;
    }
}

final class ResolverNoindexSitemapModel implements Seoable
{
    use HasSeo;

    public function seoCanonicalUrl(?string $locale = null): ?string
    {
        return 'https://example.com/noindex';
    }

    public function seoRobots(?string $locale = null): string
    {
        return 'noindex, follow';
    }
}

final class ResolverConfiguredSitemapModel
{
    public function sitemapUrl(?string $locale = null): ?string
    {
        return 'https://example.com/configured';
    }
}

final class ResolverLocalizedSitemapModel implements LocalizableSeo
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
