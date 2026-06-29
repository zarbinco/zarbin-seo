<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Resolvers\SeoSourceResolver;
use Zarbin\Seo\Tests\TestCase;

final class SeoSourceResolverMultilingualTest extends TestCase
{
    public function test_resolving_model_adds_alternate_languages(): void
    {
        $this->enableLocalization();

        $data = $this->resolver()->resolve(new SourceMultilingualModel(['fa', 'en']), 'fa');

        $this->assertSame('https://example.com/fa/page', $data->alternateLanguages['fa']);
        $this->assertSame('https://example.com/en/page', $data->alternateLanguages['en']);
    }

    public function test_resolving_route_adds_alternate_languages(): void
    {
        $this->enableLocalization();
        config()->set('zarbin-seo.localization.route_parameter', 'locale');
        Route::get('/{locale}', fn (string $locale): string => $locale)->name('home');

        $data = $this->resolver()->route('home', [], 'en');

        $this->assertStringEndsWith('/fa', $data->alternateLanguages['fa']);
        $this->assertStringEndsWith('/en', $data->alternateLanguages['en']);
    }

    public function test_noindex_strategy_changes_robots_when_current_locale_is_unavailable(): void
    {
        $this->enableLocalization('noindex');

        $data = $this->resolver()->resolve(new SourceMultilingualModel(['fa']), 'en');

        $this->assertSame(['noindex', 'follow'], $data->robots);
    }

    public function test_hide_strategy_adds_unavailable_extra_flag(): void
    {
        $this->enableLocalization('hide');

        $data = $this->resolver()->resolve(new SourceMultilingualModel(['fa']), 'en');

        $this->assertFalse($data->extra['available_for_locale']);
        $this->assertSame('hide', $data->extra['missing_translation_strategy']);
    }

    public function test_fallback_strategy_adds_used_locale_fallback_flag(): void
    {
        $this->enableLocalization('fallback');

        $data = $this->resolver()->resolve(new SourceMultilingualModel(['fa']), 'en');

        $this->assertTrue($data->extra['used_locale_fallback']);
    }

    private function enableLocalization(string $strategy = 'hide'): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['fa', 'en']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');
        config()->set('zarbin-seo.localization.missing_translation_strategy', $strategy);
    }

    private function resolver(): SeoSourceResolver
    {
        return new SeoSourceResolver;
    }
}

final class SourceMultilingualModel implements LocalizableSeo
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
