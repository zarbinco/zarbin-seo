<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\SeoFormFields;
use Zarbin\Seo\Tests\TestCase;

final class PublishedViewsSmokeTest extends TestCase
{
    public function test_package_ui_views_exist_and_render_without_syntax_errors(): void
    {
        $this->registerUiRouteNames();

        $this->assertTrue(view()->exists('zarbin-seo::ui.layout'));
        $this->assertTrue(view()->exists('zarbin-seo::ui.dashboard'));
        $this->assertTrue(view()->exists('zarbin-seo::ui.routes.index'));
        $this->assertTrue(view()->exists('zarbin-seo::ui.routes.edit'));

        $this->assertIsString(view('zarbin-seo::ui.dashboard', [
            'status' => [
                'ui_enabled' => true,
                'database_overrides_enabled' => true,
                'table_exists' => true,
                'sitemap_enabled' => true,
                'robots_enabled' => true,
                'localization_enabled' => false,
            ],
            'databaseReady' => true,
            'routeNamePrefix' => 'zarbin-seo.ui.',
        ])->render());

        $this->assertIsString(view('zarbin-seo::ui.routes.index', [
            'routes' => [],
            'databaseReady' => true,
            'routeNamePrefix' => 'zarbin-seo.ui.',
        ])->render());

        $resolved = SeoData::make(['title' => 'Resolved']);
        $this->assertIsString(view('zarbin-seo::ui.routes.edit', [
            'routeName' => 'home',
            'locale' => null,
            'resolved' => $resolved,
            'override' => null,
            'fields' => SeoFormFields::fields(),
            'values' => SeoFormFields::values([], $resolved->toArray()),
            'databaseReady' => true,
            'showPreview' => true,
            'previewHtml' => '<title>Resolved</title>',
            'routeNamePrefix' => 'zarbin-seo.ui.',
        ])->render());
    }

    public function test_component_views_exist_and_render_without_syntax_errors(): void
    {
        $fields = SeoFormFields::fields();
        $values = SeoFormFields::values([], ['title' => 'Resolved']);

        $this->assertTrue(view()->exists('zarbin-seo::components.form'));
        $this->assertTrue(view()->exists('zarbin-seo::components.fields'));
        $this->assertTrue(view()->exists('zarbin-seo::components.preview'));
        $this->assertTrue(view()->exists('zarbin-seo::components.alert'));

        $this->assertIsString(view('zarbin-seo::components.fields', compact('fields', 'values'))->render());
        $this->assertIsString(view('zarbin-seo::components.preview', ['previewHtml' => '<title>Resolved</title>'])->render());
        $this->assertIsString(view('zarbin-seo::components.form', [
            'source' => 'home',
            'locale' => null,
            'action' => null,
            'method' => 'POST',
            'standalone' => false,
            'showPreview' => true,
            'fields' => $fields,
            'values' => $values,
            'resolved' => SeoData::make(['title' => 'Resolved']),
            'previewHtml' => '<title>Resolved</title>',
            'databaseReady' => true,
            'warning' => null,
        ])->render());
    }

    private function registerUiRouteNames(): void
    {
        if (! Route::has('zarbin-seo.ui.dashboard')) {
            Route::get('/admin/seo')->name('zarbin-seo.ui.dashboard');
            Route::get('/admin/seo/routes')->name('zarbin-seo.ui.routes.index');
            Route::get('/admin/seo/routes/edit')->name('zarbin-seo.ui.routes.edit');
            Route::post('/admin/seo/routes')->name('zarbin-seo.ui.routes.update');
            Route::delete('/admin/seo/routes')->name('zarbin-seo.ui.routes.delete');
        }
    }
}
