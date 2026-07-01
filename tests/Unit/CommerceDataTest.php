<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\CommerceData;
use Zarbin\Seo\Tests\TestCase;

final class CommerceDataTest extends TestCase
{
    public function test_creates_from_array_and_trims_strings(): void
    {
        $data = CommerceData::make([
            'name' => ' Product ',
            'currency' => ' irr ',
            'brand' => '',
        ]);

        $this->assertSame('Product', $data->name);
        $this->assertSame('IRR', $data->currency);
        $this->assertNull($data->brand);
    }

    public function test_preserves_zero_values(): void
    {
        $data = CommerceData::make([
            'price' => 0,
            'rating_value' => '0',
        ]);

        $this->assertSame(0, $data->price);
        $this->assertSame('0', $data->ratingValue);
    }

    public function test_normalizes_currency(): void
    {
        $this->assertSame('USD', CommerceData::make(['currency' => 'usd'])->normalizedCurrency());
    }

    public function test_normalizes_availability_values(): void
    {
        $this->assertSame('https://schema.org/InStock', CommerceData::make(['availability' => 'in_stock'])->normalizedAvailability());
        $this->assertSame('https://schema.org/OutOfStock', CommerceData::make(['availability' => 'OutOfStock'])->normalizedAvailability());
        $this->assertSame('https://schema.org/PreOrder', CommerceData::make(['availability' => 'pre_order'])->normalizedAvailability());
        $this->assertSame('https://schema.org/BackOrder', CommerceData::make(['availability' => 'BackOrder'])->normalizedAvailability());
        $this->assertSame('https://schema.org/Discontinued', CommerceData::make(['availability' => 'Discontinued'])->normalizedAvailability());
        $this->assertSame('https://schema.org/SoldOut', CommerceData::make(['availability' => 'soldout'])->normalizedAvailability());
        $this->assertSame('https://example.com/Availability', CommerceData::make(['availability' => 'https://example.com/Availability'])->normalizedAvailability());
        $this->assertNull(CommerceData::make(['availability' => 'unknown'])->normalizedAvailability());
    }

    public function test_normalizes_condition_values(): void
    {
        $this->assertSame('https://schema.org/NewCondition', CommerceData::make(['condition' => 'new'])->normalizedCondition());
        $this->assertSame('https://schema.org/UsedCondition', CommerceData::make(['condition' => 'UsedCondition'])->normalizedCondition());
        $this->assertSame('https://schema.org/RefurbishedCondition', CommerceData::make(['condition' => 'refurbished'])->normalizedCondition());
        $this->assertSame('https://schema.org/DamagedCondition', CommerceData::make(['condition' => 'damaged'])->normalizedCondition());
    }

    public function test_identity_and_offer_detection_work(): void
    {
        $this->assertTrue(CommerceData::make(['sku' => 'SKU-1'])->hasProductIdentity());
        $this->assertTrue(CommerceData::make(['price' => 0])->hasOffer());
        $this->assertTrue(CommerceData::make(['price' => '0'])->hasOfferData());
        $this->assertTrue(CommerceData::make(['price' => 0])->hasPricedOffer());
        $this->assertTrue(CommerceData::make(['price_valid_until' => '2026-12-31'])->hasOfferData());
        $this->assertFalse(CommerceData::make(['availability' => 'in_stock'])->hasPricedOffer());
        $this->assertFalse(CommerceData::make()->hasProductIdentity());
        $this->assertFalse(CommerceData::make()->hasOffer());
    }

    public function test_merge_works(): void
    {
        $data = CommerceData::make([
            'name' => 'Old',
            'extra' => ['a' => 1],
        ])->merge([
            'name' => 'New',
            'price_valid_until' => '2026-12-31',
            'extra' => ['b' => 2],
        ]);

        $this->assertSame('New', $data->name);
        $this->assertSame('2026-12-31', $data->priceValidUntil);
        $this->assertSame(['a' => 1, 'b' => 2], $data->extra);
    }
}
