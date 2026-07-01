<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Tests\TestCase;

final class ZarbinSeoCommerceTest extends TestCase
{
    public function test_commerce_method_stores_commerce_extra(): void
    {
        $data = seo()
            ->reset()
            ->commerce([
                'price' => 120000,
                'currency' => 'irr',
                'sku' => 'SKU-1',
            ])
            ->get();

        $this->assertSame(120000, $data->extra['commerce']['price']);
        $this->assertSame('IRR', $data->extra['commerce']['currency']);
        $this->assertSame('SKU-1', $data->extra['commerce']['sku']);
    }

    public function test_product_method_is_alias_to_commerce(): void
    {
        $data = seo()
            ->reset()
            ->product([
                'sku' => 'SKU-2',
            ])
            ->get();

        $this->assertSame('SKU-2', $data->extra['commerce']['sku']);
    }

    public function test_commerce_data_causes_product_type_when_type_missing(): void
    {
        $data = seo()
            ->reset()
            ->commerce([
                'price' => 1,
            ])
            ->get();

        $this->assertSame('Product', $data->type);
    }

    public function test_commerce_json_ld_renders_product_schema(): void
    {
        $html = seo()
            ->reset()
            ->title('Product title')
            ->commerce([
                'price' => 120000,
                'currency' => 'IRR',
                'availability' => 'in_stock',
            ])
            ->jsonLd();

        $this->assertStringContainsString('"@type":"Product"', $html);
        $this->assertStringContainsString('"@type":"Offer"', $html);
        $this->assertStringContainsString('"priceCurrency":"IRR"', $html);
    }

    public function test_catalog_commerce_json_ld_renders_product_without_offer(): void
    {
        $html = seo()
            ->reset()
            ->commerce(['name' => 'Catalog Product'])
            ->jsonLd();

        $this->assertStringContainsString('"@type":"Product"', $html);
        $this->assertStringContainsString('"name":"Catalog Product"', $html);
        $this->assertStringNotContainsString('"offers"', $html);
    }

    public function test_zero_price_commerce_json_ld_renders_offer(): void
    {
        $html = seo()
            ->reset()
            ->commerce(['price' => 0, 'currency' => 'IRR'])
            ->jsonLd();

        $this->assertStringContainsString('"@type":"Product"', $html);
        $this->assertStringContainsString('"@type":"Offer"', $html);
        $this->assertStringContainsString('"price":0', $html);
        $this->assertStringContainsString('"priceCurrency":"IRR"', $html);
    }

    public function test_existing_fluent_setters_still_work(): void
    {
        $data = seo()
            ->reset()
            ->title('Product title')
            ->description('Product description')
            ->canonical('https://example.com/product')
            ->commerce(['sku' => 'SKU-3'])
            ->get();

        $this->assertSame('Product title', $data->title);
        $this->assertSame('Product description', $data->description);
        $this->assertSame('https://example.com/product', $data->canonical);
        $this->assertSame('SKU-3', $data->extra['commerce']['sku']);
    }
}
