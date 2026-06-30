<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Support\LocaleUrlStrategy;
use Zarbin\Seo\Tests\TestCase;

final class LocaleUrlStrategyTest extends TestCase
{
    public function test_invalid_strategy_falls_back_to_custom(): void
    {
        config()->set('zarbin-seo.localization.url_strategy', 'unexpected');

        $this->assertSame('custom', LocaleUrlStrategy::strategy());
        $this->assertTrue(LocaleUrlStrategy::isCustom());
    }

    public function test_prefixed_all_prefixes_every_configured_locale(): void
    {
        $this->configureLocalization('prefixed_all', 'en');

        $this->assertTrue(LocaleUrlStrategy::shouldPrefixLocale('en'));
        $this->assertTrue(LocaleUrlStrategy::shouldPrefixLocale('fa'));
        $this->assertSame('en/products', LocaleUrlStrategy::localizedPath('/products/', 'en'));
        $this->assertSame('fa/products', LocaleUrlStrategy::localizedPath('fa/products', 'fa'));
        $this->assertSame('fa', LocaleUrlStrategy::localizedPath('', 'fa'));
    }

    public function test_default_without_prefix_skips_default_locale_prefix(): void
    {
        $this->configureLocalization('default_without_prefix', 'en');

        $this->assertFalse(LocaleUrlStrategy::shouldPrefixLocale('en'));
        $this->assertTrue(LocaleUrlStrategy::shouldPrefixLocale('fa'));
        $this->assertSame('products', LocaleUrlStrategy::localizedPath('/products', 'en'));
        $this->assertSame('fa/products', LocaleUrlStrategy::localizedPath('/products', 'fa'));
        $this->assertSame('', LocaleUrlStrategy::localizedPath('', 'en'));
    }

    public function test_custom_strategy_does_not_prefix_paths(): void
    {
        $this->configureLocalization('custom', 'en');

        $this->assertFalse(LocaleUrlStrategy::shouldPrefixLocale('fa'));
        $this->assertSame('products', LocaleUrlStrategy::localizedPath('/products', 'fa'));
    }

    public function test_route_parameters_for_locale_adds_configured_parameter_without_mutating_input(): void
    {
        $this->configureLocalization('prefixed_all', 'en');
        config()->set('zarbin-seo.localization.route_parameter', 'locale');

        $parameters = ['product' => 'juice'];
        $localized = LocaleUrlStrategy::routeParametersForLocale($parameters, 'fa');

        $this->assertSame(['product' => 'juice'], $parameters);
        $this->assertSame(['locale' => 'fa', 'product' => 'juice'], $localized);
    }

    public function test_route_parameters_for_locale_does_not_overwrite_existing_locale(): void
    {
        $this->configureLocalization('prefixed_all', 'en');
        config()->set('zarbin-seo.localization.route_parameter', 'locale');

        $parameters = LocaleUrlStrategy::routeParametersForLocale(['locale' => 'en'], 'fa');

        $this->assertSame(['locale' => 'en'], $parameters);
    }

    public function test_route_parameters_for_default_without_prefix_skip_default_locale(): void
    {
        $this->configureLocalization('default_without_prefix', 'en');
        config()->set('zarbin-seo.localization.route_parameter', 'locale');

        $this->assertSame([], LocaleUrlStrategy::routeParametersForLocale([], 'en'));
        $this->assertSame(['locale' => 'fa'], LocaleUrlStrategy::routeParametersForLocale([], 'fa'));
    }

    private function configureLocalization(string $strategy, string $defaultLocale): void
    {
        config()->set('zarbin-seo.localization.locales', ['en', 'fa']);
        config()->set('zarbin-seo.localization.default_locale', $defaultLocale);
        config()->set('zarbin-seo.localization.url_strategy', $strategy);
    }
}
