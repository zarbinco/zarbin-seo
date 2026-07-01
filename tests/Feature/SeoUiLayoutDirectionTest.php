<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Tests\Concerns\CreatesSeoMetaTable;
use Zarbin\Seo\Tests\TestCase;

final class SeoUiLayoutDirectionTest extends TestCase
{
    use CreatesSeoMetaTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enableUiRoutes();
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $this->configureRoutes();

        if (! Route::has('zarbin-seo.ui.dashboard')) {
            require dirname(__DIR__, 2).'/routes/zarbin-seo-ui.php';
        }
    }

    public function test_dashboard_renders_rtl_when_locale_is_fa(): void
    {
        $this->app->setLocale('fa');

        $this->get('/admin/seo')
            ->assertOk()
            ->assertSee('dir="rtl"', false);
    }

    public function test_dashboard_renders_rtl_when_locale_is_ar(): void
    {
        $this->app->setLocale('ar');

        $this->get('/admin/seo')
            ->assertOk()
            ->assertSee('dir="rtl"', false);
    }

    public function test_dashboard_renders_ltr_when_locale_is_en(): void
    {
        $this->app->setLocale('en');

        $this->get('/admin/seo')
            ->assertOk()
            ->assertSee('dir="ltr"', false);
    }

    public function test_route_edit_keeps_raw_html_and_url_fields_ltr(): void
    {
        $this->app->setLocale('fa');

        $this->get('/admin/seo/routes/edit?route=home&locale=fa')
            ->assertOk()
            ->assertSee('class="zarbin-seo-search-preview" dir="rtl"', false)
            ->assertSee('class="zarbin-seo-snippet-url" dir="ltr"', false)
            ->assertSee('id="zarbin-seo-canonical"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('class="zarbin-seo-preview" readonly dir="ltr"', false);
    }

    private function enableUiRoutes(): void
    {
        config()->set('zarbin-seo.features.ui', true);
        config()->set('zarbin-seo.ui.enabled', true);
        config()->set('zarbin-seo.ui.route_enabled', true);
        config()->set('zarbin-seo.ui.middleware', []);
        config()->set('zarbin-seo.features.database_overrides', true);
        config()->set('zarbin-seo.database.enabled', true);
    }

    private function configureRoutes(): void
    {
        config()->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Home',
                'description' => 'Home description',
                'canonical' => 'https://example.test/home',
                'robots' => 'index, follow',
            ],
        ]);
    }
}
