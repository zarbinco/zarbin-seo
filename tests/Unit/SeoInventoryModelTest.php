<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Support\SeoInventory;
use Zarbin\Seo\Tests\TestCase;

final class SeoInventoryModelTest extends TestCase
{
    public function test_models_returns_configured_model_items(): void
    {
        $this->configureModelInventory();

        $items = (new SeoInventory)->models();

        $this->assertCount(2, $items);
        $this->assertSame('model', $items[0]->type);
        $this->assertSame('Products', $items[0]->meta['source_label']);
    }

    public function test_all_combines_routes_and_models(): void
    {
        config()->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Home',
                'description' => 'Home page',
                'canonical' => 'https://example.test',
                'robots' => 'index, follow',
            ],
        ]);
        $this->configureModelInventory();

        $items = (new SeoInventory)->all();

        $this->assertCount(3, $items);
        $this->assertSame(['route', 'model', 'model'], array_map(static fn ($item): string => $item->type, $items));
    }

    public function test_failing_one_model_source_does_not_break_others(): void
    {
        $this->enableModelInventory();
        config()->set('zarbin-seo.models', [
            SeoInventoryModelTestItem::class => $this->modelConfig([
                new SeoInventoryModelTestItem('1', 'Visible'),
            ]),
            SeoInventoryFailingModelTestItem::class => [
                'ui' => [
                    'enabled' => true,
                    'source' => static function (): array {
                        throw new \RuntimeException('Broken source');
                    },
                ],
            ],
        ]);

        $items = (new SeoInventory)->models();

        $this->assertCount(1, $items);
        $this->assertSame('Visible', $items[0]->label);
    }

    public function test_disabled_model_inventory_returns_no_models(): void
    {
        config()->set('zarbin-seo.models', [
            SeoInventoryModelTestItem::class => $this->modelConfig([
                new SeoInventoryModelTestItem('1', 'Hidden'),
            ]),
        ]);

        $this->assertSame([], (new SeoInventory)->models());
    }

    private function configureModelInventory(): void
    {
        $this->enableModelInventory();
        config()->set('zarbin-seo.models', [
            SeoInventoryModelTestItem::class => $this->modelConfig([
                new SeoInventoryModelTestItem('1', 'Complete Product'),
                new SeoInventoryModelTestItem('2', 'Incomplete Product', complete: false),
            ]),
        ]);
    }

    /**
     * @param  array<int, SeoInventoryModelTestItem>  $source
     * @return array<string, mixed>
     */
    private function modelConfig(array $source): array
    {
        return [
            'title' => 'title',
            'description' => 'description',
            'canonical' => 'canonical',
            'robots' => 'robots',
            'ui' => [
                'enabled' => true,
                'label' => 'Products',
                'source' => $source,
                'key' => 'id',
                'display' => ['title'],
            ],
        ];
    }

    private function enableModelInventory(): void
    {
        config()->set('zarbin-seo.ui.inventory.models.enabled', true);
    }
}

final class SeoInventoryModelTestItem
{
    public string $description = 'Description';

    public string $canonical = 'https://example.test/item';

    public string $robots = 'index, follow';

    public function __construct(
        public string $id,
        public string $title,
        bool $complete = true,
    ) {
        if (! $complete) {
            $this->description = '';
            $this->canonical = '';
        }
    }
}

final class SeoInventoryFailingModelTestItem
{
    public string $id = 'failing';
}
