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

    public function test_model_localized_urls_config_is_used_before_routes(): void
    {
        config()->set('zarbin-seo.models.'.UrlPlainSource::class, [
            'localized_urls' => ['fa' => 'https://example.com/fa/explicit'],
            'route' => 'missing.route',
        ]);

        $this->assertSame('https://example.com/fa/explicit', $this->resolver()->resolveForSource(new UrlPlainSource, 'fa'));
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

    public function test_default_without_prefix_strategy_generates_default_and_localized_urls(): void
    {
        Route::get('/products', fn (): string => 'products')->name('strategy.default.products');
        Route::get('/fa/products', fn (): string => 'fa products')->name('strategy.fa.products');
        $this->configureStrategy('default_without_prefix', 'en');
        config()->set('zarbin-seo.localization.route_parameter', 'locale');
        config()->set('zarbin-seo.routes.strategy.default.products', [
            'localized_routes' => [
                'fa' => 'strategy.fa.products',
            ],
        ]);

        $this->assertStringEndsWith('/products', $this->resolver()->resolveForRoute('strategy.default.products', 'en'));
        $this->assertStringEndsWith('/fa/products', $this->resolver()->resolveForRoute('strategy.default.products', 'fa'));
    }

    public function test_prefixed_all_strategy_generates_urls_for_all_locales(): void
    {
        Route::get('/{locale}/about', fn (string $locale): string => $locale)->name('strategy.prefixed.about');
        $this->configureStrategy('prefixed_all', 'en');
        config()->set('zarbin-seo.localization.route_parameter', 'locale');

        $this->assertStringEndsWith('/en/about', $this->resolver()->resolveForRoute('strategy.prefixed.about', 'en'));
        $this->assertStringEndsWith('/fa/about', $this->resolver()->resolveForRoute('strategy.prefixed.about', 'fa'));
    }

    public function test_custom_strategy_relies_on_explicit_localized_mappings(): void
    {
        Route::get('/en/custom-about', fn (): string => 'en')->name('strategy.en.custom.about');
        $this->configureStrategy('custom', 'en');
        config()->set('zarbin-seo.routes.strategy.custom.about', [
            'localized_urls' => [
                'fa' => 'https://example.com/fa/custom-about',
            ],
            'localized_routes' => [
                'en' => 'strategy.en.custom.about',
            ],
        ]);

        $this->assertSame('https://example.com/fa/custom-about', $this->resolver()->resolveForRoute('strategy.custom.about', 'fa'));
        $this->assertStringEndsWith('/en/custom-about', $this->resolver()->resolveForRoute('strategy.custom.about', 'en'));
    }

    public function test_generated_route_urls_do_not_return_duplicate_locale_prefixes(): void
    {
        Route::get('/en/{locale}/about', fn (string $locale): string => $locale)->name('strategy.duplicate.about');
        $this->configureStrategy('prefixed_all', 'en');
        config()->set('zarbin-seo.localization.route_parameter', 'locale');

        $this->assertStringContainsString('/en/fa/about', route('strategy.duplicate.about', ['locale' => 'fa']));

        $url = $this->resolver()->resolveForRoute('strategy.duplicate.about', 'fa');

        $this->assertNull($url);
        $this->assertStringNotContainsString('/en/fa/', (string) $url);
    }

    public function test_failures_do_not_throw(): void
    {
        $this->assertNull($this->resolver()->resolveForRoute('missing.route', 'fa'));
    }

    private function configureStrategy(string $strategy, string $defaultLocale): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['en', 'fa']);
        config()->set('zarbin-seo.localization.default_locale', $defaultLocale);
        config()->set('zarbin-seo.localization.url_strategy', $strategy);
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
