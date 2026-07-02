<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use InvalidArgumentException;
use Zarbin\Seo\Tests\TestCase;

final class UiComponentAliasesTest extends TestCase
{
    public function test_namespaced_components_always_work(): void
    {
        $html = Blade::render('<x-zarbin-seo::dashboard />');

        $this->assertStringContainsString('data-zarbin-seo-component="dashboard"', $html);
    }

    public function test_global_aliases_do_not_render_by_default_when_disabled(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Blade::render('<x-zarbin-seo-dashboard data-alias-state="disabled" />');
    }
}
