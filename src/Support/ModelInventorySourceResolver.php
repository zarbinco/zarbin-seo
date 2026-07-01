<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Throwable;

final class ModelInventorySourceResolver
{
    /**
     * @param  class-string|string  $modelClass
     * @param  array<string, mixed>  $modelConfig
     * @return iterable<int, mixed>
     */
    public function resolve(string $modelClass, array $modelConfig): iterable
    {
        if (! UiConfig::modelInventoryEnabled()) {
            return [];
        }

        $ui = $this->modelUiConfig($modelConfig);

        if (($ui['enabled'] ?? false) !== true || ! array_key_exists('source', $ui)) {
            return [];
        }

        try {
            $source = $this->source($ui['source'], $modelClass, $modelConfig);

            return $this->limitedItems($source, $this->limitFor($modelConfig));
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @param  array<string, mixed>  $modelConfig
     */
    public function limitFor(array $modelConfig): int
    {
        $ui = $this->modelUiConfig($modelConfig);
        $default = $this->positiveInt($this->config('zarbin-seo.ui.inventory.models.default_limit', 50), 50);
        $max = $this->positiveInt($this->config('zarbin-seo.ui.inventory.models.max_limit', 200), 200);
        $limit = $this->positiveInt($ui['limit'] ?? $modelConfig['limit'] ?? $default, $default);

        return min($limit, $max);
    }

    /**
     * @param  array<string, mixed>  $modelConfig
     * @return array<string, mixed>
     */
    public function modelUiConfig(array $modelConfig): array
    {
        $ui = $modelConfig['ui'] ?? [];

        return is_array($ui) ? $ui : [];
    }

    /**
     * @param  class-string|string  $modelClass
     * @param  array<string, mixed>  $modelConfig
     */
    private function source(mixed $source, string $modelClass, array $modelConfig): mixed
    {
        if (is_string($source) && class_exists($source)) {
            return $this->providerSource($source, $modelClass, $modelConfig);
        }

        if (is_callable($source)) {
            return $this->call($source, $modelClass, $modelConfig);
        }

        return $source;
    }

    /**
     * @param  class-string  $providerClass
     * @param  class-string|string  $modelClass
     * @param  array<string, mixed>  $modelConfig
     */
    private function providerSource(string $providerClass, string $modelClass, array $modelConfig): mixed
    {
        $provider = $this->provider($providerClass);

        if ($provider === null) {
            return [];
        }

        if (is_callable($provider)) {
            return $this->call($provider, $modelClass, $modelConfig);
        }

        foreach (['items', 'query'] as $method) {
            if (method_exists($provider, $method)) {
                return $this->call([$provider, $method], $modelClass, $modelConfig);
            }
        }

        return [];
    }

    /**
     * @param  class-string  $providerClass
     */
    private function provider(string $providerClass): ?object
    {
        try {
            if (function_exists('app')) {
                return app($providerClass);
            }

            return new $providerClass;
        } catch (Throwable) {
            try {
                return new $providerClass;
            } catch (Throwable) {
                return null;
            }
        }
    }

    /**
     * @param  class-string|string  $modelClass
     * @param  array<string, mixed>  $modelConfig
     */
    private function call(callable $callback, string $modelClass, array $modelConfig): mixed
    {
        $reflection = $this->callableReflection($callback);
        $parameters = [$modelClass, $modelConfig];

        if ($reflection === null) {
            return $callback(...$parameters);
        }

        if ($reflection->getNumberOfRequiredParameters() > count($parameters)) {
            return [];
        }

        return $callback(...($reflection->isVariadic()
            ? $parameters
            : array_slice($parameters, 0, $reflection->getNumberOfParameters())));
    }

    private function callableReflection(callable $callback): ?ReflectionFunctionAbstract
    {
        try {
            if ($callback instanceof Closure || is_string($callback)) {
                return new ReflectionFunction($callback);
            }

            if (is_array($callback) && isset($callback[0], $callback[1]) && is_string($callback[1])) {
                return new ReflectionMethod($callback[0], $callback[1]);
            }

            if (is_object($callback) && method_exists($callback, '__invoke')) {
                return new ReflectionMethod($callback, '__invoke');
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    /**
     * @return array<int, mixed>
     */
    private function limitedItems(mixed $source, int $limit): array
    {
        if ($source instanceof EloquentBuilder) {
            $source = $this->limitedBuilder($source, $limit)->get();
        }

        if (! is_iterable($source)) {
            return is_object($source) ? [$source] : [];
        }

        $items = [];

        foreach ($source as $item) {
            $items[] = $item;

            if (count($items) >= $limit) {
                break;
            }
        }

        return $items;
    }

    private function limitedBuilder(EloquentBuilder $builder, int $limit): EloquentBuilder
    {
        $builder = clone $builder;

        try {
            if ($builder->getQuery()->limit === null) {
                $builder->limit($limit);
            }
        } catch (Throwable) {
            $builder->limit($limit);
        }

        return $builder;
    }

    private function positiveInt(mixed $value, int $default): int
    {
        $value = is_numeric($value) ? (int) $value : $default;

        return $value > 0 ? $value : $default;
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
