<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Zarbin\Seo\Repositories\SeoMetaRepository;
use Zarbin\Seo\Support\UiTranslator;
use Zarbin\Seo\Tests\Concerns\CreatesSeoMetaTable;
use Zarbin\Seo\Tests\TestCase;

final class SeoUiModelsTest extends TestCase
{
    use CreatesSeoMetaTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enableUiRoutes();
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $this->createProductsTable();

        if (! Route::has('zarbin-seo.ui.models.index')) {
            require dirname(__DIR__, 2).'/routes/zarbin-seo-ui.php';
        }
    }

    public function test_model_index_is_not_linked_when_disabled(): void
    {
        $this->get('/admin/seo')
            ->assertOk()
            ->assertDontSee(UiTranslator::get('navigation.models'));

        $this->get('/admin/seo/models')
            ->assertOk()
            ->assertSee(UiTranslator::get('models.disabled'));
    }

    public function test_model_index_renders_when_enabled(): void
    {
        $this->seedProducts();
        $this->configureModelInventory();

        $this->get('/admin/seo/models')
            ->assertOk()
            ->assertSee('Products')
            ->assertSee('Complete Product')
            ->assertSee('Incomplete Product')
            ->assertSee(UiTranslator::get('status.complete_symbol'), false)
            ->assertSee(UiTranslator::get('status.incomplete_symbol'), false);
    }

    public function test_model_edit_page_renders_seo_form_and_preview(): void
    {
        $this->seedProducts();
        $this->configureModelInventory();

        $this->get($this->modelEditUrl(1))
            ->assertOk()
            ->assertSee(UiTranslator::get('models.edit_title'))
            ->assertSee('Complete Product')
            ->assertSee('name="seo[title]"', false)
            ->assertSee(UiTranslator::get('preview.search_result'))
            ->assertSee(UiTranslator::get('preview.raw_html'));
    }

    public function test_model_edit_rejects_unconfigured_model_class(): void
    {
        $this->seedProducts();
        $this->configureModelInventory();

        $this->get('/admin/seo/models/edit?'.http_build_query([
            'model' => self::class,
            'id' => '1',
        ]))->assertNotFound();
    }

    public function test_model_edit_404s_missing_model(): void
    {
        $this->seedProducts();
        $this->configureModelInventory();

        $this->get($this->modelEditUrl(999))->assertNotFound();
    }

    public function test_model_save_stores_override(): void
    {
        $this->seedProducts();
        $this->configureModelInventory();

        $this->post('/admin/seo/models', [
            'model' => SeoUiModelsProduct::class,
            'id' => '1',
            'locale' => 'fa',
            'seo' => [
                'title' => 'Manual model title',
                'robots' => 'noindex, follow',
            ],
        ])->assertRedirect();

        $meta = (new SeoMetaRepository)->findForSource(SeoUiModelsProduct::query()->findOrFail(1), 'fa');

        $this->assertSame('Manual model title', $meta?->title);
        $this->assertSame(['noindex', 'follow'], $meta?->robots);
    }

    public function test_model_delete_removes_override(): void
    {
        $this->seedProducts();
        $this->configureModelInventory();
        $product = SeoUiModelsProduct::query()->findOrFail(1);
        (new SeoMetaRepository)->saveForSource($product, ['title' => 'Manual model title'], 'en');

        $this->delete('/admin/seo/models', [
            'model' => SeoUiModelsProduct::class,
            'id' => '1',
            'locale' => 'en',
        ])->assertRedirect();

        $this->assertNull((new SeoMetaRepository)->findForSource($product, 'en'));
    }

    public function test_persian_model_ui_strings_render(): void
    {
        $this->app->setLocale('fa');
        $this->seedProducts();
        $this->configureModelInventory();

        $this->get('/admin/seo/models')
            ->assertOk()
            ->assertSee(__('zarbin-seo::ui.models.title'))
            ->assertSee(__('zarbin-seo::ui.models.item'));
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

    private function configureModelInventory(): void
    {
        config()->set('zarbin-seo.ui.inventory.models.enabled', true);
        config()->set('zarbin-seo.models', [
            SeoUiModelsProduct::class => [
                'title' => 'title',
                'description' => 'description',
                'canonical' => 'canonical',
                'robots' => 'robots',
                'ui' => [
                    'enabled' => true,
                    'label' => 'Products',
                    'source' => fn () => SeoUiModelsProduct::query()->orderBy('id')->get(),
                    'key' => 'id',
                    'display' => ['title'],
                ],
            ],
        ]);
    }

    private function createProductsTable(): void
    {
        Schema::dropIfExists('seo_ui_products');
        Schema::create('seo_ui_products', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('canonical')->nullable();
            $table->string('robots')->nullable();
        });
    }

    private function seedProducts(): void
    {
        SeoUiModelsProduct::query()->create([
            'title' => 'Complete Product',
            'description' => 'Complete description',
            'canonical' => 'https://example.test/products/1',
            'robots' => 'index, follow',
        ]);

        SeoUiModelsProduct::query()->create([
            'title' => 'Incomplete Product',
        ]);
    }

    private function modelEditUrl(int|string $id): string
    {
        return '/admin/seo/models/edit?'.http_build_query([
            'model' => SeoUiModelsProduct::class,
            'id' => (string) $id,
        ]);
    }
}

final class SeoUiModelsProduct extends Model
{
    protected $table = 'seo_ui_products';

    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $guarded = [];
}
