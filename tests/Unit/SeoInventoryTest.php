<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Support\SeoInventory;
use Zarbin\Seo\Tests\TestCase;

final class SeoInventoryTest extends TestCase
{
    public function test_builds_route_inventory_from_config(): void
    {
        $this->configureRoutes();

        $items = (new SeoInventory)->routes();

        $this->assertCount(2, $items);
        $this->assertSame('complete.route', $items[0]->key);
        $this->assertSame('route', $items[0]->type);
    }

    public function test_route_item_has_complete_status_symbol(): void
    {
        $this->configureRoutes();

        $item = (new SeoInventory)->routes()[0];

        $this->assertTrue($item->complete);
        $this->assertSame('✓', $item->statusSymbol());
        $this->assertSame('Complete', $item->statusLabel());
    }

    public function test_route_item_has_incomplete_status_symbol(): void
    {
        $this->configureRoutes();

        $item = (new SeoInventory)->routes()[1];

        $this->assertFalse($item->complete);
        $this->assertSame('×', $item->statusSymbol());
        $this->assertContains('description', $item->missing);
        $this->assertContains('canonical', $item->missing);
    }

    public function test_route_config_locale_and_label_are_used(): void
    {
        $this->configureRoutes();

        $item = (new SeoInventory)->routes()[0];

        $this->assertSame('fa', $item->locale);
        $this->assertSame('Complete Page', $item->label);
    }

    public function test_ui_false_skips_item(): void
    {
        $this->configureRoutes();

        $keys = array_map(static fn ($item): string => $item->key, (new SeoInventory)->routes());

        $this->assertNotContains('hidden.route', $keys);
    }

    public function test_virtual_route_config_without_laravel_route_does_not_throw(): void
    {
        config()->set('zarbin-seo.routes', [
            'virtual.route' => [
                'title' => 'Virtual',
                'description' => 'Virtual page',
                'canonical' => 'https://example.test/virtual',
                'robots' => 'index, follow',
            ],
        ]);

        $items = (new SeoInventory)->routes();

        $this->assertCount(1, $items);
        $this->assertSame('virtual.route', $items[0]->key);
        $this->assertNull($items[0]->editUrl);
    }

    public function test_edit_url_is_generated_when_ui_route_exists(): void
    {
        $this->configureRoutes();
        Route::get('/admin/seo/routes/edit')->name('zarbin-seo.ui.routes.edit');

        $item = (new SeoInventory)->routes()[0];

        $this->assertNotNull($item->editUrl);
        $this->assertStringContainsString('route=complete.route', (string) $item->editUrl);
        $this->assertStringContainsString('locale=fa', (string) $item->editUrl);
    }

    private function configureRoutes(): void
    {
        config()->set('zarbin-seo.routes', [
            'complete.route' => [
                'label' => 'Complete Page',
                'locale' => 'fa',
                'title' => 'Complete',
                'description' => 'Complete page',
                'canonical' => 'https://example.test/fa/complete',
                'robots' => 'index, follow',
                'image' => 'https://example.test/image.jpg',
            ],
            'incomplete.route' => [
                'title' => 'Incomplete',
            ],
            'hidden.route' => [
                'ui' => false,
                'title' => 'Hidden',
            ],
        ]);
    }
}
