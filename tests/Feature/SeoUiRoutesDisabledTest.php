<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Support\UiConfig;
use Zarbin\Seo\Tests\TestCase;

final class SeoUiRoutesDisabledTest extends TestCase
{
    public function test_ui_routes_are_not_available_by_default(): void
    {
        $this->assertFalse(UiConfig::routeEnabled());
        $this->assertFalse(Route::has('zarbin-seo.ui.dashboard'));
        $this->assertFalse(Route::has('zarbin-seo.ui.routes.index'));
    }
}
