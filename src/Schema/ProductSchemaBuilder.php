<?php

declare(strict_types=1);

namespace Zarbin\Seo\Schema;

use Zarbin\Seo\Data\CommerceData;
use Zarbin\Seo\Data\SeoData;

final class ProductSchemaBuilder
{
    /**
     * @return array<string, mixed>|null
     */
    public function build(SeoData $data): ?array
    {
        $commercePayload = $this->commercePayload($data);

        if ($commercePayload === [] && mb_strtolower((string) $data->type) !== 'product') {
            return null;
        }

        $commerce = CommerceData::make($commercePayload);
        $name = $commerce->name ?: ($data->title ?: $data->siteName);

        if (($name === null || $name === '') && ! $commerce->hasProductIdentity()) {
            return null;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $name,
            'description' => $commerce->description ?: $data->description,
            'image' => $commerce->image ?: $data->image,
            'url' => $commerce->url ?: $data->canonical,
            'sku' => $commerce->sku,
            'mpn' => $commerce->mpn,
            'gtin' => $commerce->gtin,
            'gtin8' => $commerce->gtin8,
            'gtin12' => $commerce->gtin12,
            'gtin13' => $commerce->gtin13,
            'gtin14' => $commerce->gtin14,
            'category' => $commerce->category,
            'brand' => $commerce->brand === null ? null : [
                '@type' => 'Brand',
                'name' => $commerce->brand,
            ],
            'offers' => $commerce->hasOffer() ? $this->offer($commerce, $data) : null,
            'aggregateRating' => $this->aggregateRating($commerce),
        ];

        return $this->withoutEmpty($schema);
    }

    /**
     * @return array<string, mixed>
     */
    private function offer(CommerceData $commerce, SeoData $data): array
    {
        return [
            '@type' => 'Offer',
            'url' => $commerce->url ?: $data->canonical,
            'price' => $commerce->price,
            'priceCurrency' => $commerce->normalizedCurrency(),
            'availability' => $commerce->normalizedAvailability(),
            'itemCondition' => $commerce->normalizedCondition(),
            'priceValidUntil' => $commerce->priceValidUntil,
            'seller' => $commerce->seller === null ? null : [
                '@type' => 'Organization',
                'name' => $commerce->seller,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function aggregateRating(CommerceData $commerce): ?array
    {
        if (! $this->filled($commerce->ratingValue) || ! $this->filled($commerce->reviewCount)) {
            return null;
        }

        return [
            '@type' => 'AggregateRating',
            'ratingValue' => $commerce->ratingValue,
            'reviewCount' => $commerce->reviewCount,
            'bestRating' => $commerce->bestRating,
            'worstRating' => $commerce->worstRating,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function commercePayload(SeoData $data): array
    {
        $commerce = $data->extra['commerce'] ?? ($data->extra['product'] ?? []);

        return is_array($commerce) ? $commerce : [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withoutEmpty(array $data): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->withoutEmpty($value);
            }

            if (! $this->filled($value)) {
                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }

    private function filled(mixed $value): bool
    {
        return ! ($value === null || $value === '' || $value === []);
    }
}
