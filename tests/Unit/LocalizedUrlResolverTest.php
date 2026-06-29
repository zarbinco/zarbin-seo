<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Resolvers\LocalizedUrlResolver;
use Zarbin\Seo\Tests\TestCase;

final class LocalizedUrlResolverTest extends TestCase
{
    public function test_localizable_seo_url_for_locale_is_used(): void
    {
        $source = new UrlLocalizableSource;

        $this->assertSame('https://example.com/fa/source', $this->resolver()->resolveForSource($source, 'fa'));
    }

    public function test_seo_url_for_locale_method_is_used(): void
    {
        $source = new UrlMethodSource;

        $this->assertSame('https://example.com/en/method', $this->resolver()->resolveForSource($source, 'en'));
    }

    public function test_localized_routes_config_generates_url(): void
    {
        Route::get('/fa/posts/{post}', fn (string $post): string => $post)->name('fa.posts.show');
        config()->set('zarbin-seo.models.'.UrlPlainSource::class, [
            'localized_routes' => ['fa' => 'fa.posts.show'],
            'route_parameters' => ['post' => 'slug'],
        ]);

        $source = new UrlPlainSource;
        $source->slug = 'hello';

        $this->assertStringEndsWith('/fa/posts/hello', $this->resolver()->resolveForSource($source, 'fa'));
    }

    public function test_route_parameter_config_can_generate_localized_route_url(): void
    {
        Route::get('/{locale}/posts/{post}', fn (string $locale, string $post): string => $post)->name('posts.show');
        config()->set('zarbin-seo.localization.route_parameter', 'locale');
        config()->set('zarbin-seo.models.'.UrlPlainSource::class, [
            'route' => 'posts.show',
            'route_parameters' => ['post' => 'slug'],
        ]);

        $source = new UrlPlainSource;
        $source->slug = 'hello';

        $this->assertStringEndsWith('/fa/posts/hello', $this->resolver()->resolveForSource($source, 'fa'));
    }

    public function test_route_only_localized_urls_are_used(): void
    {
        config()->set('zarbin-seo.routes.home.localized_urls.fa', 'https://example.com/fa');

        $this->assertSame('https://example.com/fa', $this->resolver()->resolveForRoute('home', 'fa'));
    }

    public function test_route_only_localized_routes_are_used(): void
    {
        Route::get('/fa', fn (): string => 'fa')->name('fa.home');
        config()->set('zarbin-seo.routes.home.localized_routes.fa', 'fa.home');

        $this->assertStringEndsWith('/fa', $this->resolver()->resolveForRoute('home', 'fa'));
    }

    public function test_failures_do_not_throw(): void
    {
        $this->assertNull($this->resolver()->resolveForRoute('missing.route', 'fa'));
    }

    private function resolver(): LocalizedUrlResolver
    {
        return new LocalizedUrlResolver;
    }
}

final class UrlLocalizableSource implements LocalizableSeo
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
        return "https://example.com/{$locale}/source";
    }
}

final class UrlMethodSource
{
    public function seoUrlForLocale(string $locale): ?string
    {
        return "https://example.com/{$locale}/method";
    }
}

final class UrlPlainSource
{
    public string $slug = 'hello';
}
