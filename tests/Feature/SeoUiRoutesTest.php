<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Zarbin\Seo\Repositories\SeoMetaRepository;
use Zarbin\Seo\Tests\Concerns\CreatesSeoMetaTable;
use Zarbin\Seo\Tests\TestCase;

abstract class SeoUiEnabledTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->enableUi();

        if (! Route::has('zarbin-seo.ui.dashboard')) {
            require dirname(__DIR__, 2).'/routes/zarbin-seo-ui.php';
        }
    }

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('zarbin-seo.features.ui', true);
        $app['config']->set('zarbin-seo.ui.enabled', true);
        $app['config']->set('zarbin-seo.ui.route_enabled', true);
        $app['config']->set('zarbin-seo.ui.middleware', []);
        $app['config']->set('zarbin-seo.features.database_overrides', true);
        $app['config']->set('zarbin-seo.database.enabled', true);
        $app['config']->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Home',
                'description' => 'Welcome home',
                'canonical' => 'https://example.test/home',
                'robots' => 'index, follow',
            ],
            'about' => [
                'title' => 'About',
            ],
        ]);
    }

    protected function enableUi(): void
    {
        config()->set('zarbin-seo.features.ui', true);
        config()->set('zarbin-seo.ui.enabled', true);
        config()->set('zarbin-seo.ui.route_enabled', true);
        config()->set('zarbin-seo.ui.middleware', []);
        config()->set('zarbin-seo.features.database_overrides', true);
        config()->set('zarbin-seo.database.enabled', true);
        config()->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Home',
                'description' => 'Welcome home',
                'canonical' => 'https://example.test/home',
                'robots' => 'index, follow',
            ],
            'about' => [
                'title' => 'About',
            ],
        ]);
    }
}

final class SeoUiRoutesTest extends SeoUiEnabledTestCase
{
    use CreatesSeoMetaTable;

    public function test_dashboard_returns_200(): void
    {
        $this->createSeoMetaTable();

        $this->get('/admin/seo')->assertOk()->assertSee('Zarbin SEO');
    }

    public function test_routes_index_returns_200(): void
    {
        $this->createSeoMetaTable();

        $this->get('/admin/seo/routes')->assertOk()->assertSee('home');
    }

    public function test_routes_index_shows_completion_status_and_missing_fields(): void
    {
        $this->createSeoMetaTable();

        $this->get('/admin/seo/routes')
            ->assertOk()
            ->assertSee('✓', false)
            ->assertSee('×', false)
            ->assertSee('Complete')
            ->assertSee('Incomplete')
            ->assertSee('description')
            ->assertSee('canonical');
    }

    public function test_edit_route_returns_200_for_configured_route(): void
    {
        $this->createSeoMetaTable();

        $this->get('/admin/seo/routes/edit?route=home')
            ->assertOk()
            ->assertSee('Edit Route Override')
            ->assertSee('<select', false)
            ->assertSee('name="seo[robots]"', false)
            ->assertSee('Index, Follow');
    }

    public function test_edit_route_preserves_saved_robots_select_value(): void
    {
        $this->createSeoMetaTable();
        (new SeoMetaRepository)->saveForRoute('home', [
            'robots' => ['noindex', 'follow'],
        ]);

        $this->get('/admin/seo/routes/edit?route=home')
            ->assertOk()
            ->assertSee('Noindex, Follow')
            ->assertSee('value="noindex, follow"', false)
            ->assertSee('selected', false);
    }

    public function test_update_route_saves_route_override(): void
    {
        $this->createSeoMetaTable();

        $this->post('/admin/seo/routes', [
            'route' => 'home',
            'locale' => 'fa',
            'seo' => [
                'title' => 'Manual home title',
                'robots' => 'noindex, follow',
                'extra' => '{"note":"saved"}',
            ],
        ])->assertRedirect();

        $meta = (new SeoMetaRepository)->findForRoute('home', 'fa');

        $this->assertSame('Manual home title', $meta?->title);
        $this->assertSame(['noindex', 'follow'], $meta?->robots);
        $this->assertSame(['note' => 'saved'], $meta?->extra);
    }

    public function test_delete_route_deletes_route_override(): void
    {
        $this->createSeoMetaTable();
        (new SeoMetaRepository)->saveForRoute('home', ['title' => 'Manual home title'], 'en');

        $this->delete('/admin/seo/routes', [
            'route' => 'home',
            'locale' => 'en',
        ])->assertRedirect();

        $this->assertNull((new SeoMetaRepository)->findForRoute('home', 'en'));
    }

    public function test_missing_database_table_shows_warning_but_does_not_crash(): void
    {
        Schema::dropIfExists('seo_meta');

        $this->get('/admin/seo')->assertOk()->assertSee('Database overrides are not ready');
    }

    public function test_configured_gate_denial_returns_403(): void
    {
        $this->createSeoMetaTable();
        config()->set('zarbin-seo.ui.gate', 'viewZarbinSeo');
        Gate::define('viewZarbinSeo', fn (): bool => false);

        $this->get('/admin/seo')->assertForbidden();
    }
}
