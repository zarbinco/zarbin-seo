<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Tests\Concerns\CreatesSeoMetaTable;
use Zarbin\Seo\Tests\TestCase;

final class SeoUiHostLayoutTest extends TestCase
{
    use CreatesSeoMetaTable;

    protected function setUp(): void
    {
        parent::setUp();

        view()->addNamespace('testing', dirname(__DIR__).'/Fixtures/views');
        $this->enableUiRoutes();
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $this->configureRoutes();

        if (! Route::has('zarbin-seo.ui.dashboard')) {
            require dirname(__DIR__, 2).'/routes/zarbin-seo-ui.php';
        }
    }

    public function test_dashboard_renders_inside_host_layout(): void
    {
        $this->enableHostLayout();

        $this->get('/admin/seo')
            ->assertOk()
            ->assertSee('id="host-layout"', false)
            ->assertSee('id="host-content"', false)
            ->assertSee('Zarbin SEO')
            ->assertSee('Zarbin SEO|ltr|en')
            ->assertDontSee('zarbin-seo-standalone');
    }

    public function test_missing_host_view_falls_back_to_standalone_layout(): void
    {
        config()->set('zarbin-seo.ui.layout.mode', 'host');
        config()->set('zarbin-seo.ui.layout.view', 'testing::missing-layout');

        $this->get('/admin/seo')
            ->assertOk()
            ->assertSee('<!doctype html>', false)
            ->assertDontSee('id="host-layout"', false);
    }

    public function test_routes_page_renders_inside_host_layout(): void
    {
        $this->enableHostLayout();

        $this->get('/admin/seo/routes')
            ->assertOk()
            ->assertSee('id="host-layout"', false)
            ->assertSee('home');
    }

    public function test_models_page_renders_inside_host_layout(): void
    {
        $this->enableHostLayout();
        $this->configureModelInventory();

        $this->get('/admin/seo/models')
            ->assertOk()
            ->assertSee('id="host-layout"', false)
            ->assertSee('Host Product');
    }

    private function enableHostLayout(): void
    {
        config()->set('zarbin-seo.ui.layout.mode', 'host');
        config()->set('zarbin-seo.ui.layout.view', 'testing::admin-layout');
        config()->set('zarbin-seo.ui.layout.section', 'content');
        config()->set('zarbin-seo.ui.layout.title_section', 'title');
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

    private function configureModelInventory(): void
    {
        config()->set('zarbin-seo.ui.inventory.models.enabled', true);
        config()->set('zarbin-seo.models', [
            SeoUiHostLayoutModel::class => [
                'title' => 'title',
                'description' => 'description',
                'canonical' => 'canonical',
                'robots' => 'robots',
                'ui' => [
                    'enabled' => true,
                    'label' => 'Products',
                    'source' => [new SeoUiHostLayoutModel],
                    'key' => 'id',
                    'display' => ['title'],
                ],
            ],
        ]);
    }
}

final class SeoUiHostLayoutModel
{
    public string $id = '1';

    public string $title = 'Host Product';

    public string $description = 'Host description';

    public string $canonical = 'https://example.test/host-product';

    public string $robots = 'index, follow';
}
