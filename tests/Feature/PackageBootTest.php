<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Zarbin\Seo\Facades\ZarbinSeo;
use Zarbin\Seo\Tests\TestCase;
use Zarbin\Seo\ZarbinSeo as ZarbinSeoManager;

final class PackageBootTest extends TestCase
{
    public function test_config_is_loaded(): void
    {
        $this->assertIsArray(config('zarbin-seo'));
        $this->assertSame(' - ', config('zarbin-seo.defaults.separator'));
        $this->assertIsArray(config('zarbin-seo.features'));
        $this->assertTrue(config('zarbin-seo.ui.inventory.routes.enabled'));
        $this->assertFalse(config('zarbin-seo.ui.inventory.models.enabled'));
        $this->assertSame('standalone', config('zarbin-seo.ui.layout.mode'));
        $this->assertSame('auto', config('zarbin-seo.ui.direction.mode'));
    }

    public function test_service_container_can_resolve_package(): void
    {
        $this->assertInstanceOf(
            ZarbinSeoManager::class,
            $this->app->make('zarbin-seo')
        );
    }

    public function test_facade_returns_package_name(): void
    {
        $this->assertSame('zarbin-seo', ZarbinSeo::name());
    }

    public function test_version_returns_non_empty_string(): void
    {
        $this->assertNotSame('', $this->app->make('zarbin-seo')->version());
    }

    public function test_seo_helper_returns_package_service(): void
    {
        $this->assertSame($this->app->make('zarbin-seo'), seo());
    }
}
