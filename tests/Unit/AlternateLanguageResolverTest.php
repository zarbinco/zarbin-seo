<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Resolvers\AlternateLanguageResolver;
use Zarbin\Seo\Tests\TestCase;

final class AlternateLanguageResolverTest extends TestCase
{
    public function test_returns_empty_when_localization_disabled(): void
    {
        config()->set('zarbin-seo.localization.enabled', false);

        $this->assertSame([], $this->resolver()->forSource(new AlternateSource(['fa', 'en'])));
    }

    public function test_generates_alternates_for_available_locales(): void
    {
        $this->enableLocalization();

        $alternates = $this->resolver()->forSource(new AlternateSource(['fa', 'en']));

        $this->assertSame('https://example.com/fa/page', $alternates['fa']);
        $this->assertSame('https://example.com/en/page', $alternates['en']);
    }

    public function test_hide_strategy_skips_unavailable_locale(): void
    {
        $this->enableLocalization('hide');

        $alternates = $this->resolver()->forSource(new AlternateSource(['fa']));

        $this->assertArrayHasKey('fa', $alternates);
        $this->assertArrayNotHasKey('en', $alternates);
    }

    public function test_fallback_strategy_can_use_default_locale_url(): void
    {
        $this->enableLocalization('fallback');

        $alternates = $this->resolver()->forSource(new AlternateSource(['fa']));

        $this->assertSame('https://example.com/fa/page', $alternates['en']);
    }

    public function test_noindex_strategy_does_not_include_unavailable_locale(): void
    {
        $this->enableLocalization('noindex');

        $alternates = $this->resolver()->forSource(new AlternateSource(['fa']));

        $this->assertArrayNotHasKey('en', $alternates);
    }

    public function test_x_default_locale_maps_to_default_locale_url(): void
    {
        $this->enableLocalization();
        config()->set('zarbin-seo.localization.x_default', 'fa');

        $alternates = $this->resolver()->forSource(new AlternateSource(['fa', 'en']));

        $this->assertSame('https://example.com/fa/page', $alternates['x-default']);
    }

    public function test_x_default_explicit_url_is_supported(): void
    {
        $this->enableLocalization();
        config()->set('zarbin-seo.localization.x_default', 'https://example.com');

        $alternates = $this->resolver()->forSource(new AlternateSource(['fa', 'en']));

        $this->assertSame('https://example.com', $alternates['x-default']);
    }

    private function enableLocalization(string $strategy = 'hide'): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['fa', 'en']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');
        config()->set('zarbin-seo.localization.missing_translation_strategy', $strategy);
    }

    private function resolver(): AlternateLanguageResolver
    {
        return new AlternateLanguageResolver;
    }
}

final class AlternateSource implements LocalizableSeo
{
    /**
     * @param  array<int, string>  $availableLocales
     */
    public function __construct(private readonly array $availableLocales) {}

    public function seoLocales(): array
    {
        return ['fa', 'en'];
    }

    public function hasSeoLocale(string $locale): bool
    {
        return in_array($locale, $this->availableLocales, true);
    }

    public function seoUrlForLocale(string $locale): ?string
    {
        return "https://example.com/{$locale}/page";
    }
}
