<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature\Bulletproof;

use Zarbin\Seo\Support\LocaleHelper;
use Zarbin\Seo\Support\SeoDoctor;
use Zarbin\Seo\Tests\TestCase;

final class LocalizationSafetyTest extends TestCase
{
    public function test_enabled_localization_with_empty_locales_does_not_throw(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', []);
        config()->set('zarbin-seo.localization.default_locale', null);

        $html = seo()->for(new LocalizationSafetyModel, 'fa')->render();
        $messages = array_map(static fn ($result): string => $result->message, (new SeoDoctor)->results());

        $this->assertStringContainsString('Localized title', $html);
        $this->assertSame([], seo()->get()->alternateLanguages);
        $this->assertContains('Localization is enabled but no locales are configured.', $messages);
    }

    public function test_invalid_missing_translation_strategy_falls_back_to_hide(): void
    {
        config()->set('zarbin-seo.localization.missing_translation_strategy', 'explode');

        $this->assertSame('hide', LocaleHelper::missingTranslationStrategy());
    }

    public function test_invalid_x_default_does_not_render_hreflang(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['fa']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');
        config()->set('zarbin-seo.localization.x_default', 'not a url');

        $data = seo()->resolve([
            'title' => 'Home',
            'canonical' => 'https://example.test/fa',
        ], 'fa');

        $this->assertArrayNotHasKey('x-default', $data->alternateLanguages);
        $this->assertStringNotContainsString('x-default', seo()->alternates());
    }

    public function test_throwing_has_seo_locale_method_does_not_fatal(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['fa']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');

        $data = seo()->resolve(new ThrowingHasSeoLocaleModel, 'fa');

        $this->assertSame('Throwing locale model', $data->title);
    }

    public function test_throwing_get_translation_method_does_not_fatal(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['fa']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');
        config()->set('zarbin-seo.localization.missing_translation_strategy', 'hide');
        config()->set('zarbin-seo.models.'.ThrowingTranslationModel::class.'.translation_availability', ['title']);

        $data = seo()->resolve(new ThrowingTranslationModel, 'fa');

        $this->assertFalse($data->extra['available_for_locale'] ?? true);
        $this->assertSame('hide', $data->extra['missing_translation_strategy'] ?? null);
    }

    public function test_malformed_localized_url_config_does_not_throw(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', [' fa ', '', null, 'en']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');
        config()->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Home',
                'localized_urls' => 'https://example.test/not-array',
                'localized_routes' => 'also-not-array',
            ],
        ]);

        $data = seo()->route('home', [], 'fa')->get();

        $this->assertSame('Home', $data->title);
        $this->assertSame([], $data->alternateLanguages);
        $this->assertSame(['fa', 'en'], LocaleHelper::configuredLocales());
    }
}

final class LocalizationSafetyModel
{
    public string $title = 'Localized title';
}

final class ThrowingHasSeoLocaleModel
{
    public string $title = 'Throwing locale model';

    public function hasSeoLocale(string $locale): bool
    {
        throw new \RuntimeException('translation package unavailable');
    }
}

final class ThrowingTranslationModel
{
    public string $title = 'Throwing translation model';

    public function getTranslation(string $field, string $locale): string
    {
        throw new \RuntimeException('translation package unavailable');
    }
}
