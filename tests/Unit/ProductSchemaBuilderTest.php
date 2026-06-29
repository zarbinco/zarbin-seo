<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Schema\ProductSchemaBuilder;
use Zarbin\Seo\Tests\TestCase;

final class ProductSchemaBuilderTest extends TestCase
{
    public function test_builds_product_schema_from_commerce_extra(): void
    {
        $schema = $this->builder()->build(SeoData::make([
            'extra' => [
                'commerce' => [
                    'name' => 'Product',
                    'sku' => 'SKU-1',
                    'brand' => 'Zarbin',
                ],
            ],
        ]));

        $this->assertSame('Product', $schema['name']);
        $this->assertSame('SKU-1', $schema['sku']);
        $this->assertSame(['@type' => 'Brand', 'name' => 'Zarbin'], $schema['brand']);
    }

    public function test_falls_back_to_seo_data_values(): void
    {
        $schema = $this->builder()->build(SeoData::make([
            'title' => 'SEO title',
            'description' => 'SEO description',
            'canonical' => 'https://example.com/product',
            'image' => 'https://example.com/product.jpg',
            'type' => 'Product',
        ]));

        $this->assertSame('SEO title', $schema['name']);
        $this->assertSame('SEO description', $schema['description']);
        $this->assertSame('https://example.com/product', $schema['url']);
        $this->assertSame('https://example.com/product.jpg', $schema['image']);
    }

    public function test_includes_offer_when_offer_data_exists(): void
    {
        $schema = $this->builder()->build(SeoData::make([
            'title' => 'Product',
            'extra' => [
                'commerce' => [
                    'price' => 0,
                    'currency' => 'irr',
                    'availability' => 'in_stock',
                    'condition' => 'new',
                    'price_valid_until' => '2026-12-31',
                    'seller' => 'Zarbin',
                ],
            ],
        ]));

        $this->assertSame('Offer', $schema['offers']['@type']);
        $this->assertSame(0, $schema['offers']['price']);
        $this->assertSame('IRR', $schema['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $schema['offers']['availability']);
        $this->assertSame('https://schema.org/NewCondition', $schema['offers']['itemCondition']);
        $this->assertSame('2026-12-31', $schema['offers']['priceValidUntil']);
        $this->assertSame(['@type' => 'Organization', 'name' => 'Zarbin'], $schema['offers']['seller']);
    }

    public function test_includes_aggregate_rating_when_rating_and_review_count_exist(): void
    {
        $schema = $this->builder()->build(SeoData::make([
            'title' => 'Product',
            'extra' => [
                'commerce' => [
                    'rating_value' => '4.8',
                    'review_count' => 12,
                    'best_rating' => 5,
                    'worst_rating' => 1,
                ],
            ],
        ]));

        $this->assertSame('AggregateRating', $schema['aggregateRating']['@type']);
        $this->assertSame('4.8', $schema['aggregateRating']['ratingValue']);
        $this->assertSame(12, $schema['aggregateRating']['reviewCount']);
    }

    public function test_removes_empty_values_recursively(): void
    {
        $schema = $this->builder()->build(SeoData::make([
            'title' => 'Product',
            'extra' => [
                'commerce' => [
                    'brand' => '',
                    'seller' => '',
                    'price' => null,
                ],
            ],
        ]));

        $this->assertArrayNotHasKey('brand', $schema);
        $this->assertArrayNotHasKey('offers', $schema);
    }

    public function test_returns_null_when_no_product_data_is_available(): void
    {
        $this->assertNull($this->builder()->build(SeoData::make(['title' => 'Page'])));
    }

    private function builder(): ProductSchemaBuilder
    {
        return new ProductSchemaBuilder;
    }
}
