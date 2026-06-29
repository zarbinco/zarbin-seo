<?php

declare(strict_types=1);

namespace Zarbin\Seo\Resolvers;

use ReflectionMethod;
use Throwable;
use Zarbin\Seo\Contracts\CommerceSeo;
use Zarbin\Seo\Data\CommerceData;
use Zarbin\Seo\Support\AttributeReader;
use Zarbin\Seo\Support\CommerceConfig;
use Zarbin\Seo\Support\Text;

final class CommerceDataResolver
{
    private const CONFIG_KEYS = [
        'name',
        'description',
        'url',
        'image',
        'price',
        'currency',
        'sku',
        'brand',
        'availability',
        'condition',
        'gtin',
        'gtin8',
        'gtin12',
        'gtin13',
        'gtin14',
        'mpn',
        'category',
        'seller',
        'price_valid_until',
        'rating_value',
        'review_count',
        'best_rating',
        'worst_rating',
    ];

    public function __construct(
        private readonly LocalizedUrlResolver $urls = new LocalizedUrlResolver,
    ) {}

    public function resolve(mixed $source, ?string $locale = null): ?CommerceData
    {
        if (! CommerceConfig::enabled() || (! is_object($source) && ! is_array($source))) {
            return null;
        }

        $config = is_object($source) ? CommerceConfig::modelConfig($source) : [];

        if ($config !== [] && ($config['enabled'] ?? true) === false) {
            return null;
        }

        $data = $this->commonData($source, $locale);

        if ($config !== []) {
            $data = $data->merge($this->configData($source, $config, $locale));
        }

        if (is_object($source)) {
            $data = $data->merge($this->contractData($source, $locale));
        }

        $currency = $data->normalizedCurrency() ?? CommerceConfig::currencyForLocale($locale);

        if ($currency !== null) {
            $data = $data->with(['currency' => $currency]);
        }

        return $data->hasProductIdentity() || $data->hasOffer() ? $data : null;
    }

    private function contractData(object $source, ?string $locale): CommerceData
    {
        if ($source instanceof CommerceSeo) {
            return $this->commerceDataFromValue($this->safeCall($source, 'toCommerceData', [$locale]));
        }

        if (method_exists($source, 'toCommerceData')) {
            return $this->commerceDataFromValue($this->safeCall($source, 'toCommerceData', [$locale]));
        }

        return CommerceData::make();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function configData(mixed $source, array $config, ?string $locale): CommerceData
    {
        $data = [];

        foreach (self::CONFIG_KEYS as $key) {
            if (! array_key_exists($key, $config)) {
                continue;
            }

            $value = $this->mappedValue($source, $key, $config[$key]);

            if ($this->filled($value)) {
                $data[$key] = $value;
            }
        }

        if (! $this->filled($data['currency'] ?? null)) {
            $currency = CommerceConfig::currencyForLocale($locale);

            if ($currency !== null) {
                $data['currency'] = $currency;
            }
        }

        return CommerceData::make($this->normalizeDescription($data));
    }

    private function commonData(mixed $source, ?string $locale): CommerceData
    {
        $data = [
            'name' => AttributeReader::first($source, ['name', 'title']),
            'description' => AttributeReader::first($source, ['short_description', 'excerpt', 'description', 'content']),
            'url' => AttributeReader::first($source, ['url', 'canonical_url']),
            'image' => AttributeReader::first($source, ['image_url', 'image', 'cover_image_url', 'cover']),
            'price' => AttributeReader::first($source, ['price', 'sale_price', 'regular_price']),
            'currency' => AttributeReader::first($source, ['currency', 'price_currency']),
            'sku' => AttributeReader::get($source, 'sku'),
            'brand' => AttributeReader::first($source, ['brand.name', 'brand', 'manufacturer.name', 'manufacturer']),
            'availability' => AttributeReader::first($source, ['availability', 'stock_status', 'status']),
            'condition' => AttributeReader::first($source, ['condition', 'item_condition']),
            'gtin' => AttributeReader::first($source, ['gtin', 'barcode']),
            'mpn' => AttributeReader::get($source, 'mpn'),
            'category' => AttributeReader::first($source, ['category.name', 'category']),
        ];

        if (! $this->filled($data['url'] ?? null) && is_object($source)) {
            $currentLocale = $locale ?? $this->currentLocale();

            if ($currentLocale !== null) {
                $data['url'] = $this->urls->resolveForSource($source, $currentLocale);
            }
        }

        if (! $this->filled($data['currency'] ?? null)) {
            $data['currency'] = CommerceConfig::currencyForLocale($locale);
        }

        return CommerceData::make($this->normalizeDescription($data));
    }

    private function mappedValue(mixed $source, string $key, mixed $mapping): mixed
    {
        if (is_array($mapping)) {
            return AttributeReader::first($source, array_values(array_filter($mapping, 'is_string')));
        }

        if (! is_string($mapping)) {
            return $mapping;
        }

        if (AttributeReader::exists($source, $mapping)) {
            return AttributeReader::get($source, $mapping);
        }

        return $this->literalValue($key, $mapping);
    }

    private function literalValue(string $key, string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if ($key === 'currency' && preg_match('/^[A-Za-z]{3}$/', $value) === 1) {
            return $value;
        }

        if (in_array($key, ['availability', 'condition', 'seller'], true)) {
            return $value;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeDescription(array $data): array
    {
        if (isset($data['description']) && is_scalar($data['description'])) {
            $data['description'] = Text::limit((string) $data['description'], $this->descriptionLimit());
        }

        return $data;
    }

    private function commerceDataFromValue(mixed $value): CommerceData
    {
        if ($value instanceof CommerceData) {
            return $value;
        }

        return is_array($value) ? CommerceData::make($value) : CommerceData::make();
    }

    /**
     * @param  array<int, mixed>  $parameters
     */
    private function safeCall(object $source, string $method, array $parameters): mixed
    {
        try {
            $reflection = new ReflectionMethod($source, $method);

            if (! $reflection->isPublic() || $reflection->getNumberOfRequiredParameters() > count($parameters)) {
                return null;
            }

            $parameters = $reflection->isVariadic()
                ? $parameters
                : array_slice($parameters, 0, $reflection->getNumberOfParameters());

            return $source->{$method}(...$parameters);
        } catch (Throwable) {
            return null;
        }
    }

    private function currentLocale(): ?string
    {
        if (! function_exists('app')) {
            return null;
        }

        try {
            $app = app();

            return method_exists($app, 'getLocale') ? $app->getLocale() : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function descriptionLimit(): int
    {
        if (! function_exists('config')) {
            return 160;
        }

        try {
            return (int) config('zarbin-seo.defaults.description_limit', 160);
        } catch (Throwable) {
            return 160;
        }
    }

    private function filled(mixed $value): bool
    {
        return ! ($value === null || $value === '' || $value === []);
    }
}
