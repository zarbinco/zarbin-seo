<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use RuntimeException;
use Zarbin\Seo\Support\ModelInventorySourceResolver;
use Zarbin\Seo\Tests\TestCase;

final class ModelInventorySourceResolverTest extends TestCase
{
    public function test_returns_empty_when_global_model_inventory_disabled(): void
    {
        $items = $this->resolver()->resolve(ModelInventorySourceTestItem::class, [
            'ui' => [
                'enabled' => true,
                'source' => [new ModelInventorySourceTestItem('1')],
            ],
        ]);

        $this->assertSame([], $items);
    }

    public function test_returns_empty_when_model_ui_is_disabled(): void
    {
        $this->enableModelInventory();

        $items = $this->resolver()->resolve(ModelInventorySourceTestItem::class, [
            'ui' => [
                'enabled' => false,
                'source' => [new ModelInventorySourceTestItem('1')],
            ],
        ]);

        $this->assertSame([], $items);
    }

    public function test_returns_empty_when_source_is_missing(): void
    {
        $this->enableModelInventory();

        $items = $this->resolver()->resolve(ModelInventorySourceTestItem::class, [
            'ui' => [
                'enabled' => true,
            ],
        ]);

        $this->assertSame([], $items);
    }

    public function test_resolves_array_source(): void
    {
        $this->enableModelInventory();

        $items = $this->resolver()->resolve(ModelInventorySourceTestItem::class, [
            'ui' => [
                'enabled' => true,
                'source' => [
                    new ModelInventorySourceTestItem('1'),
                    new ModelInventorySourceTestItem('2'),
                ],
            ],
        ]);

        $this->assertCount(2, $items);
    }

    public function test_resolves_collection_source(): void
    {
        $this->enableModelInventory();

        $items = $this->resolver()->resolve(ModelInventorySourceTestItem::class, [
            'ui' => [
                'enabled' => true,
                'source' => collect([
                    new ModelInventorySourceTestItem('1'),
                    new ModelInventorySourceTestItem('2'),
                ]),
            ],
        ]);

        $this->assertCount(2, $items);
    }

    public function test_resolves_callable_source(): void
    {
        $this->enableModelInventory();

        $items = $this->resolver()->resolve(ModelInventorySourceTestItem::class, [
            'ui' => [
                'enabled' => true,
                'source' => fn (string $modelClass, array $config): array => [
                    new ModelInventorySourceTestItem($modelClass === ModelInventorySourceTestItem::class && $config !== [] ? '1' : '0'),
                ],
            ],
        ]);

        $this->assertSame('1', $items[0]->id);
    }

    public function test_resolves_provider_class_source_with_invoke(): void
    {
        $this->enableModelInventory();
        ModelInventorySourceProvider::$items = [new ModelInventorySourceTestItem('provider')];

        $items = $this->resolver()->resolve(ModelInventorySourceTestItem::class, [
            'ui' => [
                'enabled' => true,
                'source' => ModelInventorySourceProvider::class,
            ],
        ]);

        $this->assertSame('provider', $items[0]->id);
    }

    public function test_enforces_default_limit(): void
    {
        $this->enableModelInventory(defaultLimit: 2);

        $items = $this->resolver()->resolve(ModelInventorySourceTestItem::class, [
            'ui' => [
                'enabled' => true,
                'source' => [
                    new ModelInventorySourceTestItem('1'),
                    new ModelInventorySourceTestItem('2'),
                    new ModelInventorySourceTestItem('3'),
                ],
            ],
        ]);

        $this->assertCount(2, $items);
    }

    public function test_enforces_max_limit(): void
    {
        $this->enableModelInventory(defaultLimit: 10, maxLimit: 2);

        $items = $this->resolver()->resolve(ModelInventorySourceTestItem::class, [
            'ui' => [
                'enabled' => true,
                'limit' => 10,
                'source' => [
                    new ModelInventorySourceTestItem('1'),
                    new ModelInventorySourceTestItem('2'),
                    new ModelInventorySourceTestItem('3'),
                ],
            ],
        ]);

        $this->assertCount(2, $items);
    }

    public function test_source_exception_returns_empty(): void
    {
        $this->enableModelInventory();

        $items = $this->resolver()->resolve(ModelInventorySourceTestItem::class, [
            'ui' => [
                'enabled' => true,
                'source' => static function (): array {
                    throw new RuntimeException('Nope');
                },
            ],
        ]);

        $this->assertSame([], $items);
    }

    private function enableModelInventory(int $defaultLimit = 50, int $maxLimit = 200): void
    {
        config()->set('zarbin-seo.ui.inventory.models.enabled', true);
        config()->set('zarbin-seo.ui.inventory.models.default_limit', $defaultLimit);
        config()->set('zarbin-seo.ui.inventory.models.max_limit', $maxLimit);
    }

    private function resolver(): ModelInventorySourceResolver
    {
        return new ModelInventorySourceResolver;
    }
}

final class ModelInventorySourceTestItem
{
    public function __construct(public string $id) {}
}

final class ModelInventorySourceProvider
{
    /**
     * @var array<int, ModelInventorySourceTestItem>
     */
    public static array $items = [];

    /**
     * @return array<int, ModelInventorySourceTestItem>
     */
    public function __invoke(): array
    {
        return self::$items;
    }
}
