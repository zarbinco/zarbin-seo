<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Support\LocaleHelper;
use Zarbin\Seo\Tests\TestCase;

final class LocaleHelperTest extends TestCase
{
    public function test_localization_enabled_and_disabled(): void
    {
        config()->set('zarbin-seo.localization.enabled', false);
        $this->assertFalse(LocaleHelper::enabled());

        config()->set('zarbin-seo.localization.enabled', true);
        $this->assertTrue(LocaleHelper::enabled());
    }

    public function test_configured_locales_are_normalized(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', [' fa ', '', 'en', 'fa']);

        $this->assertSame(['fa', 'en'], LocaleHelper::configuredLocales());
    }

    public function test_current_locale_priority(): void
    {
        config()->set('app.locale', 'en');
        config()->set('zarbin-seo.localization.default_locale', 'fa');
        $this->app->setLocale('de');

        $this->assertSame('fr', LocaleHelper::currentLocale('fr'));
        $this->assertSame('de', LocaleHelper::currentLocale());
    }

    public function test_default_locale_and_x_default(): void
    {
        config()->set('zarbin-seo.localization.default_locale', ' fa ');
        config()->set('zarbin-seo.localization.x_default', 'fa');

        $this->assertSame('fa', LocaleHelper::defaultLocale());
        $this->assertSame('fa', LocaleHelper::xDefault());
    }

    public function test_invalid_missing_translation_strategy_falls_back_to_hide(): void
    {
        config()->set('zarbin-seo.localization.missing_translation_strategy', 'unknown');

        $this->assertSame('hide', LocaleHelper::missingTranslationStrategy());
        $this->assertFalse(LocaleHelper::isValidStrategy('unknown'));
    }
}
