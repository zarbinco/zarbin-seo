<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Support\CommerceConfig;
use Zarbin\Seo\Tests\TestCase;

final class CommerceConfigTest extends TestCase
{
    public function test_disabled_by_default(): void
    {
        $this->assertFalse(CommerceConfig::enabled());
    }

    public function test_enabled_requires_feature_and_commerce_enabled(): void
    {
        config()->set('zarbin-seo.features.commerce', true);
        $this->assertFalse(CommerceConfig::enabled());

        config()->set('zarbin-seo.commerce.enabled', true);
        $this->assertTrue(CommerceConfig::enabled());
    }

    public function test_default_and_locale_currency(): void
    {
        config()->set('zarbin-seo.commerce.default_currency', 'irr');
        config()->set('zarbin-seo.commerce.currency_per_locale.en', 'usd');

        $this->assertSame('IRR', CommerceConfig::defaultCurrency());
        $this->assertSame('USD', CommerceConfig::currencyForLocale('en'));
        $this->assertSame('IRR', CommerceConfig::currencyForLocale('fa'));
    }

    public function test_model_commerce_config(): void
    {
        config()->set('zarbin-seo.models.'.CommerceConfigProduct::class.'.commerce', [
            'enabled' => true,
            'price' => 'price',
        ]);

        $this->assertSame([
            'enabled' => true,
            'price' => 'price',
        ], CommerceConfig::modelConfig(CommerceConfigProduct::class));
    }
}

final class CommerceConfigProduct {}
