<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Zarbin\Seo\Tests\TestCase;

final class UiComponentAliasesEnabledTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('zarbin-seo.ui.components.global_aliases', true);
    }

    public function test_global_aliases_render_when_enabled(): void
    {
        $html = Blade::render('<x-zarbin-seo-dashboard />');

        $this->assertStringContainsString('data-zarbin-seo-component="dashboard"', $html);
    }
}
