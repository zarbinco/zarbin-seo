<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature\Bulletproof;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Zarbin\Seo\Support\UiConfig;
use Zarbin\Seo\Tests\TestCase;

final class UiSafetyTest extends TestCase
{
    public function test_ui_is_disabled_by_default_and_not_exposed(): void
    {
        $this->assertFalse(UiConfig::enabled());
        $this->assertFalse(UiConfig::routeEnabled());
        $this->assertFalse(Route::has('zarbin-seo.ui.dashboard'));
    }

    public function test_feature_or_route_flags_keep_route_ui_disabled(): void
    {
        config()->set('zarbin-seo.features.ui', true);
        config()->set('zarbin-seo.ui.enabled', false);
        config()->set('zarbin-seo.ui.route_enabled', true);

        $this->assertFalse(UiConfig::routeEnabled());

        config()->set('zarbin-seo.ui.enabled', true);
        config()->set('zarbin-seo.ui.route_enabled', false);

        $this->assertFalse(UiConfig::routeEnabled());
    }

    public function test_ui_enabled_with_database_disabled_shows_warning(): void
    {
        $this->enableUiRoutes(databaseEnabled: false);

        $this->get('/admin/seo')
            ->assertOk()
            ->assertSee('Database overrides are not ready');
    }

    public function test_ui_enabled_with_missing_database_table_does_not_crash(): void
    {
        $this->enableUiRoutes(databaseEnabled: true);
        Schema::dropIfExists('seo_meta');

        $this->get('/admin/seo')->assertOk()->assertSee('Database overrides are not ready');
        $this->get('/admin/seo/routes/edit?route=home')->assertOk()->assertSee('Database overrides are not ready');
        $this->post('/admin/seo/routes', [
            'route' => 'home',
            'seo' => [
                'title' => 'Manual',
            ],
        ])->assertRedirect()->assertSessionHas('zarbin_seo_warning');
    }

    public function test_gate_denial_and_allowance_are_respected(): void
    {
        $this->enableUiRoutes(databaseEnabled: false);
        config()->set('zarbin-seo.ui.gate', 'viewZarbinSeo');
        $allowed = false;
        Gate::define('viewZarbinSeo', static function ($user = null) use (&$allowed): bool {
            return $allowed;
        });

        $this->get('/admin/seo')->assertForbidden();

        $allowed = true;

        $this->get('/admin/seo')->assertOk();
    }

    public function test_edit_route_with_unconfigured_key_returns_404(): void
    {
        $this->enableUiRoutes(databaseEnabled: false);

        $this->get('/admin/seo/routes/edit?route=missing')->assertNotFound();
    }

    private function enableUiRoutes(bool $databaseEnabled): void
    {
        config()->set('zarbin-seo.features.ui', true);
        config()->set('zarbin-seo.ui.enabled', true);
        config()->set('zarbin-seo.ui.route_enabled', true);
        config()->set('zarbin-seo.ui.middleware', []);
        config()->set('zarbin-seo.features.database_overrides', $databaseEnabled);
        config()->set('zarbin-seo.database.enabled', $databaseEnabled);
        config()->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Home',
                'description' => 'Welcome home',
            ],
        ]);

        if (! Route::has('zarbin-seo.ui.dashboard')) {
            require dirname(__DIR__, 3).'/routes/zarbin-seo-ui.php';
        }
    }
}
