<?php

declare(strict_types=1);

namespace Zarbin\Seo\Resolvers;

use Throwable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\Text;

final class RouteSeoResolver
{
    public function __construct(
        private readonly FallbackSeoResolver $fallback = new FallbackSeoResolver,
    ) {}

    /**
     * @param  array<int|string, mixed>  $parameters
     */
    public function resolve(string $routeName, array $parameters = [], ?string $locale = null): SeoData
    {
        $config = $this->routeConfig($routeName);
        $resolvedRouteName = is_string($config['route'] ?? null) ? $config['route'] : $routeName;
        $resolvedParameters = $this->parameters($config['parameters'] ?? [], $parameters);
        $type = $config['type'] ?? ($config['schema'] ?? null);

        return $this->fallback->resolve(null, $locale)->merge($this->nonEmpty([
            'title' => Text::clean($this->stringOrNull($config['title'] ?? null)),
            'description' => Text::limit(
                $this->stringOrNull($config['description'] ?? null),
                (int) $this->config('zarbin-seo.defaults.description_limit', 160)
            ),
            'image' => $this->stringOrNull($config['image'] ?? null),
            'canonical' => $this->canonical($config, $resolvedRouteName, $resolvedParameters),
            'robots' => $config['robots'] ?? null,
            'type' => $this->stringOrNull($type),
            'locale' => $this->stringOrNull($config['locale'] ?? $locale),
            'siteName' => $this->stringOrNull($config['site_name'] ?? null),
            'separator' => $this->stringOrNull($config['separator'] ?? null),
            'extra' => is_array($config['extra'] ?? null) ? $config['extra'] : [],
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    private function routeConfig(string $routeName): array
    {
        $config = $this->config('zarbin-seo.routes.'.$routeName, []);

        return is_array($config) ? $config : [];
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<int|string, mixed>  $parameters
     */
    private function canonical(array $config, string $routeName, array $parameters): ?string
    {
        foreach (['canonical', 'url'] as $key) {
            if (isset($config[$key]) && is_string($config[$key]) && $config[$key] !== '') {
                return $config[$key];
            }
        }

        return $this->routeUrl($routeName, $parameters);
    }

    /**
     * @param  array<int|string, mixed>  $passed
     * @return array<int|string, mixed>
     */
    private function parameters(mixed $configured, array $passed): array
    {
        $configured = is_array($configured) ? $configured : [];

        return array_replace($configured, $passed);
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function nonEmpty(array $data): array
    {
        return array_filter(
            $data,
            fn (mixed $value): bool => ! ($value === null || $value === '' || $value === []),
        );
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
