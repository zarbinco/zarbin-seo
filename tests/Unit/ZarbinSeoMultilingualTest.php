<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Tests\TestCase;

final class ZarbinSeoMultilingualTest extends TestCase
{
    public function test_alternate_languages_method_renders_links(): void
    {
        $html = seo()
            ->reset()
            ->alternateLanguages(['fa' => 'https://example.com/fa'])
            ->alternates();

        $this->assertStringContainsString('hreflang="fa"', $html);
    }

    public function test_add_alternate_language_renders_link(): void
    {
        $html = seo()
            ->reset()
            ->addAlternateLanguage('en', 'https://example.com/en')
            ->alternates();

        $this->assertStringContainsString('hreflang="en"', $html);
    }

    public function test_for_model_render_includes_alternate_links(): void
    {
        $this->enableLocalization();

        $html = seo()->reset()->for(new ManagerMultilingualModel(['fa', 'en']), 'fa')->render();

        $this->assertStringContainsString('hreflang="fa"', $html);
        $this->assertStringContainsString('hreflang="en"', $html);
    }

    public function test_route_render_includes_alternate_links(): void
    {
        $this->enableLocalization();
        config()->set('zarbin-seo.localization.route_parameter', 'locale');
        Route::get('/{locale}', fn (string $locale): string => $locale)->name('home');

        $html = seo()->reset()->route('home', [], 'en')->render();

        $this->assertStringContainsString('hreflang="fa"', $html);
        $this->assertStringContainsString('hreflang="en"', $html);
    }

    private function enableLocalization(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['fa', 'en']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');
    }
}

final class ManagerMultilingualModel implements LocalizableSeo
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
        return "https://example.com/{$locale}/manager";
    }
}
