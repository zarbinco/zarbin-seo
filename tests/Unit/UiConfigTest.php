<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Support\UiConfig;
use Zarbin\Seo\Tests\TestCase;

final class UiConfigTest extends TestCase
{
    public function test_ui_is_disabled_by_default(): void
    {
        $this->assertFalse(UiConfig::enabled());
        $this->assertFalse(UiConfig::routeEnabled());
    }

    public function test_enabled_requires_feature_and_ui_enabled(): void
    {
        config()->set('zarbin-seo.features.ui', true);
        $this->assertFalse(UiConfig::enabled());

        config()->set('zarbin-seo.ui.enabled', true);
        $this->assertTrue(UiConfig::enabled());
    }

    public function test_route_enabled_requires_ui_enabled_and_route_enabled(): void
    {
        config()->set('zarbin-seo.features.ui', true);
        config()->set('zarbin-seo.ui.enabled', true);
        $this->assertFalse(UiConfig::routeEnabled());

        config()->set('zarbin-seo.ui.route_enabled', true);
        $this->assertTrue(UiConfig::routeEnabled());
    }

    public function test_path_fallback_and_trimming(): void
    {
        $this->assertSame('admin/seo', UiConfig::path());

        config()->set('zarbin-seo.ui.path', '/custom/seo/');

        $this->assertSame('custom/seo', UiConfig::path());
    }

    public function test_middleware_is_normalized_to_array(): void
    {
        config()->set('zarbin-seo.ui.middleware', 'web');
        $this->assertSame(['web'], UiConfig::middleware());

        config()->set('zarbin-seo.ui.middleware', ['web', '', 'auth']);
        $this->assertSame(['web', 'auth'], UiConfig::middleware());
    }

    public function test_gate_returns_null_for_empty_values(): void
    {
        config()->set('zarbin-seo.ui.gate', '   ');
        $this->assertNull(UiConfig::gate());

        config()->set('zarbin-seo.ui.gate', 'viewSeo');
        $this->assertSame('viewSeo', UiConfig::gate());
    }
}
