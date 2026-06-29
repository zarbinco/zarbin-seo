<?php

declare(strict_types=1);

namespace Zarbin\Seo\Data;

use Throwable;

final readonly class CommerceData
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?string $url = null,
        public ?string $image = null,
        public int|float|string|null $price = null,
        public ?string $currency = null,
        public ?string $sku = null,
        public ?string $brand = null,
        public ?string $availability = null,
        public ?string $condition = null,
        public ?string $gtin = null,
        public ?string $gtin8 = null,
        public ?string $gtin12 = null,
        public ?string $gtin13 = null,
        public ?string $gtin14 = null,
        public ?string $mpn = null,
        public ?string $category = null,
        public ?string $seller = null,
        public ?string $priceValidUntil = null,
        public int|float|string|null $ratingValue = null,
        public int|float|string|null $reviewCount = null,
        public int|float|string|null $bestRating = null,
        public int|float|string|null $worstRating = null,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data = []): self
    {
        return self::fromArray($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $source = $data;
        $extra = isset($source['extra']) && is_array($source['extra'])
            ? $source['extra']
            : [];

        foreach (array_keys(self::knownKeys()) as $key) {
            unset($data[$key]);
        }

        return new self(
            name: self::stringOrNull($source['name'] ?? null),
            description: self::stringOrNull($source['description'] ?? null),
            url: self::stringOrNull($source['url'] ?? null),
            image: self::stringOrNull($source['image'] ?? null),
            price: self::scalarOrNull($source['price'] ?? null),
            currency: self::currencyOrNull($source['currency'] ?? null),
            sku: self::stringOrNull($source['sku'] ?? null),
            brand: self::stringOrNull($source['brand'] ?? null),
            availability: self::stringOrNull($source['availability'] ?? null),
            condition: self::stringOrNull($source['condition'] ?? null),
            gtin: self::stringOrNull($source['gtin'] ?? null),
            gtin8: self::stringOrNull($source['gtin8'] ?? null),
            gtin12: self::stringOrNull($source['gtin12'] ?? null),
            gtin13: self::stringOrNull($source['gtin13'] ?? null),
            gtin14: self::stringOrNull($source['gtin14'] ?? null),
            mpn: self::stringOrNull($source['mpn'] ?? null),
            category: self::stringOrNull($source['category'] ?? null),
            seller: self::stringOrNull($source['seller'] ?? null),
            priceValidUntil: self::stringOrNull($source['priceValidUntil'] ?? ($source['price_valid_until'] ?? null)),
            ratingValue: self::scalarOrNull($source['ratingValue'] ?? ($source['rating_value'] ?? null)),
            reviewCount: self::scalarOrNull($source['reviewCount'] ?? ($source['review_count'] ?? null)),
            bestRating: self::scalarOrNull($source['bestRating'] ?? ($source['best_rating'] ?? null)),
            worstRating: self::scalarOrNull($source['worstRating'] ?? ($source['worst_rating'] ?? null)),
            extra: array_replace($data, $extra),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'url' => $this->url,
            'image' => $this->image,
            'price' => $this->price,
            'currency' => $this->currency,
            'sku' => $this->sku,
            'brand' => $this->brand,
            'availability' => $this->availability,
            'condition' => $this->condition,
            'gtin' => $this->gtin,
            'gtin8' => $this->gtin8,
            'gtin12' => $this->gtin12,
            'gtin13' => $this->gtin13,
            'gtin14' => $this->gtin14,
            'mpn' => $this->mpn,
            'category' => $this->category,
            'seller' => $this->seller,
            'priceValidUntil' => $this->priceValidUntil,
            'price_valid_until' => $this->priceValidUntil,
            'ratingValue' => $this->ratingValue,
            'rating_value' => $this->ratingValue,
            'reviewCount' => $this->reviewCount,
            'review_count' => $this->reviewCount,
            'bestRating' => $this->bestRating,
            'best_rating' => $this->bestRating,
            'worstRating' => $this->worstRating,
            'worst_rating' => $this->worstRating,
            'extra' => $this->extra,
        ];
    }

    /**
     * @param  array<string, mixed>|self  $data
     */
    public function merge(array|self $data): self
    {
        $incoming = $data instanceof self ? $data->toArray() : $data;
        $current = $this->toArray();

        foreach ([
            'price_valid_until' => 'priceValidUntil',
            'rating_value' => 'ratingValue',
            'review_count' => 'reviewCount',
            'best_rating' => 'bestRating',
            'worst_rating' => 'worstRating',
        ] as $snake => $camel) {
            if (array_key_exists($snake, $incoming) && ! array_key_exists($camel, $incoming)) {
                $incoming[$camel] = $incoming[$snake];
            }
        }

        if (
            isset($incoming['extra'], $current['extra'])
            && is_array($incoming['extra'])
            && is_array($current['extra'])
        ) {
            $incoming['extra'] = array_replace($current['extra'], $incoming['extra']);
        }

        return self::fromArray(array_replace($current, $this->nonEmpty($incoming)));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function with(array $data): self
    {
        return $this->merge($data);
    }

    public function hasProductIdentity(): bool
    {
        foreach ([$this->name, $this->sku, $this->gtin, $this->mpn, $this->url] as $value) {
            if ($this->filled($value)) {
                return true;
            }
        }

        return false;
    }

    public function hasOffer(): bool
    {
        foreach ([$this->price, $this->currency, $this->availability, $this->condition, $this->seller] as $value) {
            if ($this->filled($value)) {
                return true;
            }
        }

        return false;
    }

    public function normalizedCurrency(): ?string
    {
        return self::currencyOrNull($this->currency);
    }

    public function normalizedAvailability(): ?string
    {
        return $this->normalizeMappedValue($this->availability, self::availabilityMap());
    }

    public function normalizedCondition(): ?string
    {
        return $this->normalizeMappedValue($this->condition, self::conditionMap());
    }

    /**
     * @return array<string, true>
     */
    private static function knownKeys(): array
    {
        return [
            'name' => true,
            'description' => true,
            'url' => true,
            'image' => true,
            'price' => true,
            'currency' => true,
            'sku' => true,
            'brand' => true,
            'availability' => true,
            'condition' => true,
            'gtin' => true,
            'gtin8' => true,
            'gtin12' => true,
            'gtin13' => true,
            'gtin14' => true,
            'mpn' => true,
            'category' => true,
            'seller' => true,
            'priceValidUntil' => true,
            'price_valid_until' => true,
            'ratingValue' => true,
            'rating_value' => true,
            'reviewCount' => true,
            'review_count' => true,
            'bestRating' => true,
            'best_rating' => true,
            'worstRating' => true,
            'worst_rating' => true,
            'extra' => true,
        ];
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function scalarOrNull(mixed $value): int|float|string|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_int($value) || is_float($value) || is_string($value) ? $value : null;
    }

    private static function currencyOrNull(mixed $value): ?string
    {
        $value = self::stringOrNull($value);

        return $value === null ? null : mb_strtoupper($value);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function nonEmpty(array $data): array
    {
        return array_filter($data, fn (mixed $value): bool => $this->filled($value));
    }

    private function filled(mixed $value): bool
    {
        return ! ($value === null || $value === '' || $value === []);
    }

    /**
     * @param  array<string, string>  $map
     */
    private function normalizeMappedValue(?string $value, array $map): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        $key = self::mapKey($value);

        return $map[$key] ?? null;
    }

    /**
     * @return array<string, string>
     */
    private static function availabilityMap(): array
    {
        return array_replace([
            'in_stock' => 'https://schema.org/InStock',
            'instock' => 'https://schema.org/InStock',
            'available' => 'https://schema.org/InStock',
            'out_of_stock' => 'https://schema.org/OutOfStock',
            'outofstock' => 'https://schema.org/OutOfStock',
            'unavailable' => 'https://schema.org/OutOfStock',
            'preorder' => 'https://schema.org/PreOrder',
            'pre_order' => 'https://schema.org/PreOrder',
            'backorder' => 'https://schema.org/BackOrder',
            'back_order' => 'https://schema.org/BackOrder',
            'discontinued' => 'https://schema.org/Discontinued',
            'soldout' => 'https://schema.org/SoldOut',
            'sold_out' => 'https://schema.org/SoldOut',
        ], self::configMap('zarbin-seo.commerce.availability_map'));
    }

    /**
     * @return array<string, string>
     */
    private static function conditionMap(): array
    {
        return array_replace([
            'new' => 'https://schema.org/NewCondition',
            'newcondition' => 'https://schema.org/NewCondition',
            'new_condition' => 'https://schema.org/NewCondition',
            'used' => 'https://schema.org/UsedCondition',
            'usedcondition' => 'https://schema.org/UsedCondition',
            'used_condition' => 'https://schema.org/UsedCondition',
            'refurbished' => 'https://schema.org/RefurbishedCondition',
            'refurbishedcondition' => 'https://schema.org/RefurbishedCondition',
            'refurbished_condition' => 'https://schema.org/RefurbishedCondition',
            'damaged' => 'https://schema.org/DamagedCondition',
            'damagedcondition' => 'https://schema.org/DamagedCondition',
            'damaged_condition' => 'https://schema.org/DamagedCondition',
        ], self::configMap('zarbin-seo.commerce.condition_map'));
    }

    /**
     * @return array<string, string>
     */
    private static function configMap(string $key): array
    {
        if (! function_exists('config')) {
            return [];
        }

        try {
            $values = config($key, []);
        } catch (Throwable) {
            return [];
        }

        if (! is_array($values)) {
            return [];
        }

        $map = [];

        foreach ($values as $mapKey => $url) {
            if (is_scalar($mapKey) && is_scalar($url)) {
                $map[self::mapKey((string) $mapKey)] = (string) $url;
            }
        }

        return $map;
    }

    private static function mapKey(string $value): string
    {
        $value = preg_replace('/(?<!^)[A-Z]/', '_$0', $value) ?? $value;
        $value = mb_strtolower(trim($value));

        return str_replace(['-', ' '], '_', $value);
    }
}
