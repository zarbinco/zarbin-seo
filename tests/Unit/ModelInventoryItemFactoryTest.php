<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Support\ModelInventoryItemFactory;
use Zarbin\Seo\Support\UiTranslator;
use Zarbin\Seo\Tests\TestCase;

final class ModelInventoryItemFactoryTest extends TestCase
{
    public function test_builds_model_item_with_get_key(): void
    {
        $model = new ModelInventoryFactoryTestModel('10');
        $config = $this->completeConfig();
        $this->configureModel($config);

        $item = $this->factory()->make($model, $model::class, $config);

        $this->assertNotNull($item);
        $this->assertSame('10', $item->key);
        $this->assertSame('model', $item->type);
    }

    public function test_uses_ui_key(): void
    {
        $model = new ModelInventoryFactoryTestModel('10');
        $model->slug = 'sunich-product';
        $config = array_replace_recursive($this->completeConfig(), [
            'ui' => [
                'key' => 'slug',
            ],
        ]);
        $this->configureModel($config);

        $item = $this->factory()->make($model, $model::class, $config);

        $this->assertSame('sunich-product', $item?->key);
    }

    public function test_uses_route_key_fallback(): void
    {
        $model = new ModelInventoryFactoryTestModel('10');
        $model->slug = 'route-key-product';
        $config = array_replace($this->completeConfig(), [
            'route_key' => 'slug',
        ]);
        $this->configureModel($config);

        $item = $this->factory()->make($model, $model::class, $config);

        $this->assertSame('route-key-product', $item?->key);
    }

    public function test_uses_display_fields(): void
    {
        $model = new ModelInventoryFactoryTestModel('10');
        $model->name = 'Displayed Product';
        $config = array_replace_recursive($this->completeConfig(), [
            'ui' => [
                'display' => ['name'],
            ],
        ]);
        $this->configureModel($config);

        $item = $this->factory()->make($model, $model::class, $config);

        $this->assertSame('Displayed Product', $item?->label);
    }

    public function test_falls_back_to_seo_title_name_and_id(): void
    {
        $model = new ModelInventoryFactoryTestModel('10');
        $config = $this->completeConfig();
        $this->configureModel($config);

        $item = $this->factory()->make($model, $model::class, $config);

        $this->assertSame('Factory title', $item?->label);
    }

    public function test_returns_null_when_no_key_exists(): void
    {
        $model = new ModelInventoryFactoryNoKeyModel;
        $config = ['title' => 'title'];
        config()->set('zarbin-seo.models.'.$model::class, $config);

        $this->assertNull($this->factory()->make($model, $model::class, $config));
    }

    public function test_includes_model_meta(): void
    {
        $model = new ModelInventoryFactoryTestModel('10');
        $config = $this->completeConfig();
        $this->configureModel($config);

        $item = $this->factory()->make($model, $model::class, $config);

        $this->assertSame($model::class, $item?->meta['model_class']);
        $this->assertSame('10', $item?->meta['model_id']);
        $this->assertSame('10', $item?->meta['model_key']);
        $this->assertSame('Products', $item?->meta['source_label']);
    }

    public function test_status_symbol_reflects_completion(): void
    {
        $model = new ModelInventoryFactoryTestModel('10');
        $config = $this->completeConfig();
        $this->configureModel($config);

        $item = $this->factory()->make($model, $model::class, $config);

        $this->assertTrue($item?->complete);
        $this->assertSame(UiTranslator::get('status.complete_symbol'), $item?->statusSymbol());
    }

    /**
     * @return array<string, mixed>
     */
    private function completeConfig(): array
    {
        return [
            'title' => 'title',
            'description' => 'description',
            'canonical' => 'canonical',
            'robots' => 'robots',
            'ui' => [
                'enabled' => true,
                'label' => 'Products',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function configureModel(array $config): void
    {
        config()->set('zarbin-seo.models.'.ModelInventoryFactoryTestModel::class, $config);
    }

    private function factory(): ModelInventoryItemFactory
    {
        return new ModelInventoryItemFactory;
    }
}

final class ModelInventoryFactoryTestModel
{
    public string $title = 'Factory title';

    public string $description = 'Factory description';

    public string $canonical = 'https://example.test/factory';

    public string $robots = 'index, follow';

    public ?string $name = null;

    public ?string $slug = null;

    public function __construct(private readonly string $key) {}

    public function getKey(): string
    {
        return $this->key;
    }
}

final class ModelInventoryFactoryNoKeyModel
{
    public string $title = 'No key';
}
