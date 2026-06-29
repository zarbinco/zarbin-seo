<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Zarbin\Seo\Tests\TestCase;
use Zarbin\Seo\ZarbinSeo;
use Zarbin\Seo\ZarbinSeoServiceProvider;

final class ReleaseReadinessTest extends TestCase
{
    public function test_package_binding_resolves(): void
    {
        $this->assertInstanceOf(ZarbinSeo::class, app('zarbin-seo'));
    }

    public function test_publish_tags_are_registered(): void
    {
        foreach (['zarbin-seo-config', 'zarbin-seo-migrations', 'zarbin-seo-views'] as $tag) {
            $this->assertNotEmpty(
                ServiceProvider::pathsToPublish(ZarbinSeoServiceProvider::class, $tag),
                "Expected publish tag [{$tag}] to be registered."
            );
        }
    }

    public function test_package_views_namespace_works(): void
    {
        $this->assertTrue(view()->exists('zarbin-seo::components.meta'));
        $this->assertTrue(view()->exists('zarbin-seo::components.form'));
    }

    public function test_commands_are_registered(): void
    {
        $commands = array_keys(Artisan::all());

        foreach ([
            'zarbin-seo:install',
            'zarbin-seo:doctor',
            'zarbin-seo:check',
            'zarbin-seo:sitemap',
            'zarbin-seo:robots',
        ] as $command) {
            $this->assertContains($command, $commands);
        }
    }

    public function test_public_sitemap_and_robots_routes_exist_when_enabled(): void
    {
        $this->assertTrue(Route::has('zarbin-seo.sitemap'));
        $this->assertTrue(Route::has('zarbin-seo.sitemap.index'));
        $this->assertTrue(Route::has('zarbin-seo.robots'));
    }

    public function test_ui_routes_remain_disabled_by_default(): void
    {
        $this->assertFalse(Route::has('zarbin-seo.ui.dashboard'));
        $this->assertFalse(Route::has('zarbin-seo.ui.routes.index'));
    }

    public function test_seo_helper_exists(): void
    {
        $this->assertTrue(function_exists('seo'));
        $this->assertInstanceOf(ZarbinSeo::class, seo());
    }

    public function test_readme_contains_key_user_facing_headings(): void
    {
        $readme = (string) file_get_contents(__DIR__.'/../../README.md');

        foreach ([
            '## Installation',
            '## Quick Start',
            '## Model-Aware SEO',
            '## Sitemap',
            '## Optional Database Overrides',
            '## Artisan Commands',
        ] as $heading) {
            $this->assertStringContainsString($heading, $readme);
        }
    }

    public function test_changelog_contains_release_version(): void
    {
        $changelog = (string) file_get_contents(__DIR__.'/../../CHANGELOG.md');

        $this->assertStringContainsString('## 0.1.0 - ', $changelog);
    }
}
