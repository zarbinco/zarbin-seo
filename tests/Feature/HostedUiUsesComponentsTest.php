<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Zarbin\Seo\Tests\Concerns\CreatesSeoMetaTable;
use Zarbin\Seo\Tests\TestCase;

final class HostedUiUsesComponentsTest extends TestCase
{
    use CreatesSeoMetaTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $this->createProductsTable();
        $this->seedProduct();
        $this->enableUiRoutes();
        $this->configureRoutes();
        $this->configureModelInventory();

        if (! Route::has('zarbin-seo.ui.dashboard')) {
            require dirname(__DIR__, 2).'/routes/zarbin-seo-ui.php';
        }
    }

    public function test_hosted_dashboard_renders_component_output(): void
    {
        $this->get('/admin/seo')
            ->assertOk()
            ->assertSee('data-zarbin-seo-component="dashboard"', false);
    }

    public function test_hosted_routes_page_renders_component_output(): void
    {
        $this->get('/admin/seo/routes')
            ->assertOk()
            ->assertSee('data-zarbin-seo-component="routes"', false)
            ->assertSee('home');
    }

    public function test_hosted_models_page_renders_component_output(): void
    {
        $this->get('/admin/seo/models')
            ->assertOk()
            ->assertSee('data-zarbin-seo-component="models"', false)
            ->assertSee('Hosted Component Product');
    }

    public function test_hosted_route_edit_page_renders_route_form_component_output(): void
    {
        $this->get('/admin/seo/routes/edit?route=home')
            ->assertOk()
            ->assertSee('data-zarbin-seo-component="route-form"', false)
            ->assertSee('name="route" value="home"', false);
    }

    public function test_hosted_model_edit_page_renders_model_form_component_output(): void
    {
        $this->get('/admin/seo/models/edit?'.http_build_query([
            'model' => HostedUiUsesComponentsProduct::class,
            'id' => '1',
        ]))
            ->assertOk()
            ->assertSee('data-zarbin-seo-component="model-form"', false)
            ->assertSee('Hosted Component Product');
    }

    private function enableUiRoutes(): void
    {
        config()->set('zarbin-seo.features.ui', true);
        config()->set('zarbin-seo.ui.enabled', true);
        config()->set('zarbin-seo.ui.route_enabled', true);
        config()->set('zarbin-seo.ui.middleware', []);
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
            HostedUiUsesComponentsProduct::class => [
                'title' => 'title',
                'description' => 'description',
                'canonical' => 'canonical',
                'robots' => 'robots',
                'ui' => [
                    'enabled' => true,
                    'label' => 'Products',
                    'source' => fn () => HostedUiUsesComponentsProduct::query()->orderBy('id')->get(),
                    'key' => 'id',
                    'display' => ['title'],
                ],
            ],
        ]);
    }

    private function createProductsTable(): void
    {
        Schema::dropIfExists('hosted_ui_component_products');
        Schema::create('hosted_ui_component_products', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('canonical')->nullable();
            $table->string('robots')->nullable();
        });
    }

    private function seedProduct(): void
    {
        HostedUiUsesComponentsProduct::query()->create([
            'title' => 'Hosted Component Product',
            'description' => 'Hosted component description',
            'canonical' => 'https://example.test/hosted-component-product',
            'robots' => 'index, follow',
        ]);
    }
}

final class HostedUiUsesComponentsProduct extends Model
{
    protected $table = 'hosted_ui_component_products';

    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $guarded = [];
}
