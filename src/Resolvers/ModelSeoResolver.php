<?php

declare(strict_types=1);

namespace Zarbin\Seo\Resolvers;

use Throwable;
use Zarbin\Seo\Contracts\Seoable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\AttributeReader;
use Zarbin\Seo\Support\Text;

final class ModelSeoResolver
{
    public function __construct(
        private readonly FallbackSeoResolver $fallback = new FallbackSeoResolver,
    ) {}

    public function resolve(object $model, ?string $locale = null): SeoData
    {
        $data = $this->fallback->resolve($model, $locale);
        $configData = $this->resolveConfigData($model, $this->modelConfig($model));

        $data = $data->merge($this->nonEmptyData($configData));

        if ($model instanceof Seoable) {
            $data = $data->merge($this->nonEmptyData($model->toSeoData($locale)));
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function resolveConfigData(object $model, array $config): SeoData
    {
        $descriptionLimit = (int) ($config['description_limit'] ?? $this->config('zarbin-seo.defaults.description_limit', 160));
        $type = $this->literalOrAttribute($model, $config['type'] ?? null);
        $schema = $this->literalOrAttribute($model, $config['schema'] ?? null);

        return SeoData::make([
            'title' => Text::clean($this->stringOrNull($this->mappedAttribute($model, $config['title'] ?? null))),
            'description' => Text::limit(
                $this->stringOrNull($this->mappedAttribute($model, $config['description'] ?? null)),
                $descriptionLimit
            ),
            'image' => $this->stringOrNull($this->mappedAttribute($model, $config['image'] ?? null)),
            'canonical' => $this->canonical($model, $config),
            'robots' => $this->robots($model, $config['robots'] ?? null),
            'type' => $this->stringOrNull($type ?? $schema),
            'locale' => $this->stringOrNull($this->literalOrAttribute($model, $config['locale'] ?? null)),
            'siteName' => $this->stringOrNull($this->literalOrAttribute($model, $config['site_name'] ?? null)),
            'separator' => $this->stringOrNull($this->literalOrAttribute($model, $config['separator'] ?? null)),
            'extra' => $this->extra($model, $config['extra'] ?? []),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function modelConfig(object $model): array
    {
        $config = $this->config('zarbin-seo.models.'.get_class($model), []);

        return is_array($config) ? $config : [];
    }

    private function mappedAttribute(object $model, mixed $mapping): mixed
    {
        if ($mapping === null) {
            return null;
        }

        if (is_array($mapping)) {
            return AttributeReader::first($model, array_values($mapping));
        }

        if (is_string($mapping) && AttributeReader::exists($model, $mapping)) {
            return AttributeReader::get($model, $mapping);
        }

        return null;
    }

    private function literalOrAttribute(object $model, mixed $value): mixed
    {
        if (is_string($value) && AttributeReader::exists($model, $value)) {
            return AttributeReader::get($model, $value);
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function canonical(object $model, array $config): ?string
    {
        $canonical = $config['canonical'] ?? null;

        if (is_string($canonical) && $this->looksLikeUrl($canonical)) {
            return $canonical;
        }

        if (is_string($canonical) && AttributeReader::exists($model, $canonical)) {
            return $this->stringOrNull(AttributeReader::get($model, $canonical));
        }

        if (is_string($config['route'] ?? null)) {
            return $this->routeUrl($config['route'], $this->routeParameters($model, $config));
        }

        return null;
    }

    private function robots(object $model, mixed $robots): string|array|null
    {
        if (is_string($robots) && AttributeReader::exists($model, $robots)) {
            $value = AttributeReader::get($model, $robots);

            return is_array($value) || is_string($value) || $value === null
                ? $value
                : (string) $value;
        }

        return is_string($robots) || is_array($robots) || $robots === null
            ? $robots
            : (string) $robots;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int|string, mixed>
     */
    private function routeParameters(object $model, array $config): array
    {
        if (isset($config['route_parameters']) && is_array($config['route_parameters'])) {
            $parameters = [];

            foreach ($config['route_parameters'] as $name => $value) {
                $parameters[$name] = is_string($value) && AttributeReader::exists($model, $value)
                    ? AttributeReader::get($model, $value)
                    : $value;
            }

            return $parameters;
        }

        if (array_key_exists('route_key', $config)) {
            $routeKey = $config['route_key'];

            if (is_string($routeKey)) {
                return [AttributeReader::get($model, $routeKey)];
            }

            if ($routeKey === null) {
                return [$model];
            }
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function extra(object $model, mixed $extra): array
    {
        if (! is_array($extra)) {
            return [];
        }

        $resolved = [];

        foreach ($extra as $key => $value) {
            $resolved[$key] = is_string($value) && AttributeReader::exists($model, $value)
                ? AttributeReader::get($model, $value)
                : $value;
        }

        return $resolved;
    }

    /**
     * @return array<string, mixed>
     */
    private function nonEmptyData(SeoData $data): array
    {
        return array_filter(
            $data->toArray(),
            fn (mixed $value): bool => ! ($value === null || $value === '' || $value === []),
        );
    }

    /**
     * @param  array<int|string, mixed>  $parameters
     */
    private function routeUrl(string $name, array $parameters): ?string
    {
        if (! function_exists('route')) {
            return null;
        }

        try {
            return route($name, $parameters);
        } catch (Throwable) {
            return null;
        }
    }

    private function looksLikeUrl(string $value): bool
    {
        return str_starts_with($value, 'http://')
            || str_starts_with($value, 'https://')
            || str_starts_with($value, '/');
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) $value;
    }

    private function config(?string $key = null, mixed $default = null): mixed
    {
        if (! function_exists('config')) {
            return $default;
        }

        try {
            return config($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }
}
