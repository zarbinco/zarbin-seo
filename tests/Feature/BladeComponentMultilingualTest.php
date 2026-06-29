<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Tests\TestCase;

final class BladeComponentMultilingualTest extends TestCase
{
    public function test_blade_component_with_source_and_locale_includes_hreflang_tags(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['fa', 'en']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');

        $model = new BladeMultilingualSource(['fa', 'en']);

        $html = Blade::render('<x-zarbin-seo::meta :source="$model" locale="fa" />', [
            'model' => $model,
        ]);

        $this->assertStringContainsString('hreflang="fa"', $html);
        $this->assertStringContainsString('hreflang="en"', $html);
    }
}

final class BladeMultilingualSource implements LocalizableSeo
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
        return "https://example.com/{$locale}/blade";
    }
}
