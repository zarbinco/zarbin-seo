<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature\Bulletproof;

use Zarbin\Seo\Data\CommerceData;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Renderers\JsonLdRenderer;
use Zarbin\Seo\Schema\ProductSchemaBuilder;
use Zarbin\Seo\Tests\TestCase;

final class CommerceSafetyTest extends TestCase
{
    public function test_disabled_commerce_feature_does_not_add_commerce_extra(): void
    {
        config()->set('zarbin-seo.features.commerce', false);
        config()->set('zarbin-seo.commerce.enabled', false);

        $data = seo()->resolve(new CommerceSafetyProduct);

        $this->assertArrayNotHasKey('commerce', $data->extra);
        $this->assertStringContainsString('"@type":"WebPage"', (new JsonLdRenderer)->render($data));
    }

    public function test_feature_enabled_but_commerce_disabled_outputs_no_commerce(): void
    {
        config()->set('zarbin-seo.features.commerce', true);
        config()->set('zarbin-seo.commerce.enabled', false);

        $this->assertArrayNotHasKey('commerce', seo()->resolve(new CommerceSafetyProduct)->extra);
    }

    public function test_malformed_product_values_normalize_without_crashing(): void
    {
        $commerce = CommerceData::make([
            'name' => ' Product ',
            'price' => '0',
            'currency' => ' irr ',
            'availability' => 'impossible',
            'condition' => 'broken',
            'brand' => new \stdClass,
        ]);

        $schema = (new ProductSchemaBuilder)->build(SeoData::make([
            'extra' => [
                'commerce' => $commerce->toArray(),
            ],
        ]));

        $this->assertSame('IRR', $commerce->normalizedCurrency());
        $this->assertNull($commerce->normalizedAvailability());
        $this->assertNull($commerce->normalizedCondition());
        $this->assertSame('0', $schema['offers']['price'] ?? null);
        $this->assertArrayNotHasKey('availability', $schema['offers'] ?? []);
    }

    public function test_missing_or_unexpected_brand_relation_does_not_crash(): void
    {
        config()->set('zarbin-seo.features.commerce', true);
        config()->set('zarbin-seo.commerce.enabled', true);

        $product = new CommerceSafetyProduct;
        $product->brand = new \stdClass;

        $data = seo()->resolve($product);

        $this->assertArrayHasKey('commerce', $data->extra);
        $this->assertArrayNotHasKey('brand', array_filter($data->extra['commerce']));
    }

    public function test_collection_page_type_is_preserved_unless_force_type_is_enabled(): void
    {
        config()->set('zarbin-seo.features.commerce', true);
        config()->set('zarbin-seo.commerce.enabled', true);
        config()->set('zarbin-seo.models.'.CommerceSafetyProduct::class, [
            'type' => 'CollectionPage',
            'commerce' => [
                'enabled' => true,
                'price' => 'price',
            ],
        ]);

        $this->assertSame('CollectionPage', seo()->resolve(new CommerceSafetyProduct)->type);

        config()->set('zarbin-seo.models.'.CommerceSafetyProduct::class.'.commerce.force_type', true);

        $this->assertSame('Product', seo()->resolve(new CommerceSafetyProduct)->type);
    }

    public function test_product_schema_preserves_zero_price_and_unicode(): void
    {
        $json = seo()
            ->reset()
            ->title('محصول تست')
            ->commerce([
                'price' => 0,
                'currency' => 'IRR',
                'availability' => 'in_stock',
            ])
            ->jsonLd();

        $this->assertStringContainsString('"price":0', $json);
        $this->assertStringContainsString('محصول تست', $json);
        $this->assertStringNotContainsString('\\u0645', $json);
    }
}

final class CommerceSafetyProduct
{
    public string $title = 'Product title';

    public string $name = 'Product name';

    public int $price = 1000;

    public mixed $brand = null;
}
