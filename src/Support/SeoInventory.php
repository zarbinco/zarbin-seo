<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Data\SeoInventoryItem;
use Zarbin\Seo\Resolvers\SeoSourceResolver;

final class SeoInventory
{
    public function __construct(
        private readonly SeoSourceResolver $resolver = new SeoSourceResolver,
        private readonly SeoCompletionChecker $completion = new SeoCompletionChecker,
        private readonly ModelInventorySourceResolver $modelSources = new ModelInventorySourceResolver,
        private readonly ModelInventoryItemFactory $modelItems = new ModelInventoryItemFactory,
    ) {}

    /**
     * @return array<int, SeoInventoryItem>
     */
    public function routes(?string $locale = null): array
    {
        if (! UiConfig::routeInventoryEnabled()) {
            return [];
        }

        $items = [];

        foreach ($this->configuredRoutes() as $routeName => $config) {
            if (($config['ui'] ?? true) === false) {
                continue;
            }

            $itemLocale = $this->itemLocale($config, $locale);
            $data = $this->resolveRoute($routeName, $itemLocale);
            $status = $this->completion->check($data);

            $items[] = new SeoInventoryItem(
                key: $routeName,
                type: 'route',
                label: $this->label($routeName, $config),
                locale: $itemLocale,
                editUrl: $this->editUrl($routeName, $itemLocale),
                data: $data,
                complete: $status['complete'],
                missing: $status['missing'],
                warnings: $status['warnings'],
                meta: [
                    'configured_title' => $this->stringOrNull($config['title'] ?? null),
                    'schema_type' => $this->stringOrNull($config['schema'] ?? ($config['type'] ?? null)),
                ],
            );
        }

        return $items;
    }

    /**
     * @return array<int, SeoInventoryItem>
     */
    public function models(?string $locale = null): array
    {
        $items = [];

        foreach ($this->configuredModels() as $modelClass => $config) {
            foreach ($this->modelSources->resolve($modelClass, $config) as $model) {
                if (! is_object($model)) {
                    continue;
                }

                $item = $this->modelItems->make($model, $modelClass, $config, $locale);

                if ($item !== null) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * @return array<int, SeoInventoryItem>
     */
    public function all(?string $locale = null): array
    {
        return array_values(array_merge(
            $this->routes($locale),
            $this->models($locale),
        ));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function configuredRoutes(): array
    {
        $routes = $this->config('zarbin-seo.routes', []);

        return is_array($routes) ? array_filter($routes, 'is_array') : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function configuredModels(): array
    {
        $models = $this->config('zarbin-seo.models', []);

        if (! is_array($models)) {
            return [];
        }

        $configured = [];

        foreach ($models as $modelClass => $config) {
            if (is_string($modelClass) && $modelClass !== '' && is_array($config)) {
                $configured[$modelClass] = $config;
            }
        }

        return $configured;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function itemLocale(array $config, ?string $locale): ?string
    {
        return $this->stringOrNull($config['locale'] ?? null)
            ?? LocaleHelper::normalizeLocale($locale);
    }

    private function resolveRoute(string $routeName, ?string $locale): SeoData
    {
        try {
            return $this->resolver->route($routeName, [], $locale);
        } catch (Throwable) {
            return SeoData::make(['locale' => $locale]);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function label(string $routeName, array $config): ?string
    {
        return $this->stringOrNull($config['label'] ?? null)
            ?? $this->stringOrNull($config['title'] ?? null)
            ?? $routeName;
    }

    private function editUrl(string $routeName, ?string $locale): ?string
    {
        $name = UiConfig::routeNamePrefix().'routes.edit';

        if (! function_exists('route')) {
            return null;
        }

        try {
            $parameters = ['route' => $routeName];

            if ($locale !== null) {
                $parameters['locale'] = $locale;
            }

            return route($name, $parameters);
        } catch (Throwable) {
            return null;
        }
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
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
