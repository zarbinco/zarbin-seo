<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zarbin\Seo\Concerns\HasCommerceSeo;
use Zarbin\Seo\Data\CommerceData;

final class HasCommerceSeoTest extends TestCase
{
    public function test_trait_returns_commerce_data_with_overridden_methods(): void
    {
        $model = new class
        {
            use HasCommerceSeo;

            public function seoProductName(?string $locale = null): ?string
            {
                return $locale === 'fa' ? 'محصول' : 'Product';
            }

            public function seoProductPrice(?string $locale = null): int|float|string|null
            {
                return 120000;
            }

            public function seoProductCurrency(?string $locale = null): ?string
            {
                return 'irr';
            }

            public function seoProductAvailability(?string $locale = null): ?string
            {
                return 'in_stock';
            }
        };

        $data = $model->toCommerceData('fa');

        $this->assertInstanceOf(CommerceData::class, $data);
        $this->assertSame('محصول', $data->name);
        $this->assertSame(120000, $data->price);
        $this->assertSame('IRR', $data->currency);
        $this->assertSame('https://schema.org/InStock', $data->normalizedAvailability());
    }

    public function test_default_trait_methods_return_empty_data_safely(): void
    {
        $model = new class
        {
            use HasCommerceSeo;
        };

        $data = $model->toCommerceData();

        $this->assertInstanceOf(CommerceData::class, $data);
        $this->assertFalse($data->hasProductIdentity());
        $this->assertFalse($data->hasOffer());
    }
}
