<?php

declare(strict_types=1);

namespace Zarbin\Seo\Concerns;

use Zarbin\Seo\Data\CommerceData;

trait HasCommerceSeo
{
    public function toCommerceData(?string $locale = null): CommerceData|array|null
    {
        return CommerceData::make(array_filter([
            'name' => $this->seoProductName($locale),
            'description' => $this->seoProductDescription($locale),
            'url' => $this->seoProductUrl($locale),
            'image' => $this->seoProductImage($locale),
            'price' => $this->seoProductPrice($locale),
            'currency' => $this->seoProductCurrency($locale),
            'sku' => $this->seoProductSku($locale),
            'brand' => $this->seoProductBrand($locale),
            'availability' => $this->seoProductAvailability($locale),
            'condition' => $this->seoProductCondition($locale),
            'gtin' => $this->seoProductGtin($locale),
            'mpn' => $this->seoProductMpn($locale),
            'category' => $this->seoProductCategory($locale),
            'seller' => $this->seoProductSeller($locale),
            'priceValidUntil' => $this->seoProductPriceValidUntil($locale),
            'ratingValue' => $this->seoProductRatingValue($locale),
            'reviewCount' => $this->seoProductReviewCount($locale),
        ], fn (mixed $value): bool => ! ($value === null || $value === '' || $value === [])));
    }

    public function seoProductName(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductDescription(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductUrl(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductImage(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductPrice(?string $locale = null): int|float|string|null
    {
        return null;
    }

    public function seoProductCurrency(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductSku(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductBrand(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductAvailability(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductCondition(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductGtin(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductMpn(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductCategory(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductSeller(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductPriceValidUntil(?string $locale = null): ?string
    {
        return null;
    }

    public function seoProductRatingValue(?string $locale = null): int|float|string|null
    {
        return null;
    }

    public function seoProductReviewCount(?string $locale = null): int|float|string|null
    {
        return null;
    }
}
