<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Closure;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Throwable;

final class CommerceFieldResolver
{
    public function resolve(mixed $source, mixed $mapping, ?string $locale = null, mixed $default = null): mixed
    {
        if ($mapping === null) {
            return $default;
        }

        if (is_callable($mapping)) {
            return $this->resolveCallable($source, $mapping, $locale, $default);
        }

        if (is_array($mapping)) {
            return array_is_list($mapping)
                ? $this->first($source, $mapping, $locale, $default)
                : $this->resolveSpec($source, $mapping, $locale, $default);
        }

        if (! is_string($mapping)) {
            return $mapping;
        }

        $mapping = trim($mapping);

        if ($mapping === '') {
            return $default;
        }

        if (str_starts_with($mapping, 'literal:')) {
            return substr($mapping, strlen('literal:'));
        }

        return $this->resolvePath($source, $mapping, $locale, $default);
    }

    /**
     * @param  array<int, mixed>  $mappings
     */
    public function first(mixed $source, array $mappings, ?string $locale = null, mixed $default = null): mixed
    {
        $missing = $this->missingValue();

        foreach ($mappings as $mapping) {
            $value = $this->resolve($source, $mapping, $locale, $missing);

            if ($value !== $missing && ! $this->isEmpty($value)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * @param  array<string, mixed>  $mapping
     */
    private function resolveSpec(mixed $source, array $mapping, ?string $locale, mixed $default): mixed
    {
        if (array_key_exists('literal', $mapping)) {
            return $mapping['literal'] ?? $default;
        }

        if (isset($mapping['path']) && is_string($mapping['path'])) {
            return $this->resolvePath($source, $mapping['path'], $locale, $default);
        }

        if (isset($mapping['method']) && is_string($mapping['method'])) {
            return $this->resolveMethod($source, $mapping, $locale, $default);
        }

        if (isset($mapping['relation']) && is_string($mapping['relation'])) {
            return $this->resolveRelationSpec($source, $mapping, $locale, $default);
        }

        return $default;
    }

    /**
     * @param  array<string, mixed>  $mapping
     */
    private function resolveMethod(mixed $source, array $mapping, ?string $locale, mixed $default): mixed
    {
        if (! is_object($source) || ! method_exists($source, $mapping['method'])) {
            return $default;
        }

        try {
            $method = new ReflectionMethod($source, $mapping['method']);

            if (! $method->isPublic()) {
                return $default;
            }

            $parameters = array_key_exists('parameters', $mapping)
                ? $this->methodParameters($mapping['parameters'], $locale)
                : [$locale];

            if ($method->getNumberOfRequiredParameters() > count($parameters)) {
                return $default;
            }

            $parameters = $method->isVariadic()
                ? $parameters
                : array_slice($parameters, 0, $method->getNumberOfParameters());

            return $source->{$mapping['method']}(...$parameters);
        } catch (Throwable) {
            return $default;
        }
    }

    /**
     * @param  array<string, mixed>  $mapping
     */
    private function resolveRelationSpec(mixed $source, array $mapping, ?string $locale, mixed $default): mixed
    {
        $missing = $this->missingValue();
        $relation = AttributeReader::get($source, $mapping['relation'], $missing);

        if ($relation === $missing) {
            return $default;
        }

        $value = $relation;

        if (isset($mapping['where']) && is_array($mapping['where'])) {
            $value = $this->firstMatching($relation, $mapping['where'], $locale, $missing);

            if ($value === $missing) {
                return $default;
            }
        } elseif (array_key_exists('value', $mapping) && is_iterable($relation)) {
            $value = $this->firstItem($relation, $missing);

            if ($value === $missing) {
                return $default;
            }
        }

        if (isset($mapping['value']) && is_string($mapping['value'])) {
            return AttributeReader::get($value, $mapping['value'], $default);
        }

        return $value;
    }

    private function resolvePath(mixed $source, string $path, ?string $locale, mixed $default): mixed
    {
        $path = $this->replacePlaceholders($path, $locale);

        if ($path === '') {
            return $default;
        }

        $missing = $this->missingValue();
        $value = $source;

        foreach (explode('.', $path) as $segment) {
            if ($segment === '') {
                return $default;
            }

            $filtered = $this->filteredSegment($segment);

            if ($filtered === null) {
                $value = AttributeReader::get($value, $segment, $missing);
            } else {
                [$relation, $whereKey, $whereValue] = $filtered;
                $items = AttributeReader::get($value, $relation, $missing);
                $value = $items === $missing
                    ? $missing
                    : $this->firstMatching($items, [$whereKey => $whereValue], $locale, $missing);
            }

            if ($value === $missing) {
                return $default;
            }
        }

        return $value;
    }

    private function resolveCallable(mixed $source, callable $mapping, ?string $locale, mixed $default): mixed
    {
        try {
            $parameters = $this->callableParameters($mapping, $source, $locale);

            if ($parameters === null) {
                return $default;
            }

            return $mapping(...$parameters);
        } catch (Throwable) {
            return $default;
        }
    }

    /**
     * @return array<int, mixed>|null
     */
    private function callableParameters(callable $mapping, mixed $source, ?string $locale): ?array
    {
        $reflection = $this->callableReflection($mapping);

        if ($reflection === null) {
            return [$source, $locale];
        }

        if ($reflection->getNumberOfRequiredParameters() > 2) {
            return null;
        }

        $parameters = [$source, $locale];

        return $reflection->isVariadic()
            ? $parameters
            : array_slice($parameters, 0, $reflection->getNumberOfParameters());
    }

    private function callableReflection(callable $mapping): ?ReflectionFunctionAbstract
    {
        try {
            if ($mapping instanceof Closure || is_string($mapping)) {
                return new ReflectionFunction($mapping);
            }

            if (is_array($mapping) && isset($mapping[0], $mapping[1]) && is_string($mapping[1])) {
                return new ReflectionMethod($mapping[0], $mapping[1]);
            }

            if (is_object($mapping) && method_exists($mapping, '__invoke')) {
                return new ReflectionMethod($mapping, '__invoke');
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    /**
     * @return array<int, mixed>
     */
    private function methodParameters(mixed $parameters, ?string $locale): array
    {
        if (! is_array($parameters)) {
            return [];
        }

        return array_map(
            fn (mixed $parameter): mixed => is_string($parameter)
                ? $this->replacePlaceholders($parameter, $locale)
                : $parameter,
            array_values($parameters),
        );
    }

    /**
     * @param  array<string, mixed>  $where
     */
    private function firstMatching(mixed $items, array $where, ?string $locale, mixed $default): mixed
    {
        foreach ($this->items($items) as $item) {
            if ($this->matchesWhere($item, $where, $locale)) {
                return $item;
            }
        }

        return $default;
    }

    /**
     * @param  array<string, mixed>  $where
     */
    private function matchesWhere(mixed $item, array $where, ?string $locale): bool
    {
        $missing = $this->missingValue();

        foreach ($where as $key => $expected) {
            if (! is_scalar($key) || $key === '') {
                return false;
            }

            $actual = AttributeReader::get($item, (string) $key, $missing);

            if ($actual === $missing || ! $this->valuesMatch($actual, $this->replaceExpectedValue($expected, $locale))) {
                return false;
            }
        }

        return true;
    }

    private function valuesMatch(mixed $actual, mixed $expected): bool
    {
        if (is_bool($actual) || is_bool($expected) || $this->isBoolString($actual) || $this->isBoolString($expected)) {
            return $this->boolValue($actual) === $this->boolValue($expected);
        }

        if (! is_scalar($actual) || ! is_scalar($expected)) {
            return $actual == $expected;
        }

        $actual = trim((string) $actual);
        $expected = trim((string) $expected);

        return $actual === $expected || mb_strtolower($actual) === mb_strtolower($expected);
    }

    private function replaceExpectedValue(mixed $value, ?string $locale): mixed
    {
        return is_string($value) ? $this->replacePlaceholders($value, $locale) : $value;
    }

    /**
     * @return array{string, string, string}|null
     */
    private function filteredSegment(string $segment): ?array
    {
        if (preg_match('/^([^\[\]]+)\[([^=\]]+)=([^\]]*)\]$/', $segment, $matches) !== 1) {
            return null;
        }

        return [$matches[1], $matches[2], $matches[3]];
    }

    private function replacePlaceholders(string $value, ?string $locale): string
    {
        return str_replace('{locale}', $locale ?? '', $value);
    }

    /**
     * @return array<int, mixed>
     */
    private function items(mixed $items): array
    {
        if (! is_iterable($items)) {
            return is_object($items) || is_array($items) ? [$items] : [];
        }

        $values = [];

        foreach ($items as $item) {
            $values[] = $item;
        }

        return $values;
    }

    private function firstItem(iterable $items, mixed $default): mixed
    {
        foreach ($items as $item) {
            return $item;
        }

        return $default;
    }

    private function isBoolString(mixed $value): bool
    {
        return is_string($value) && in_array(mb_strtolower(trim($value)), ['true', 'false', '1', '0'], true);
    }

    private function boolValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(mb_strtolower(trim((string) $value)), ['true', '1'], true);
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    private function missingValue(): object
    {
        static $missing;

        return $missing ??= new class {};
    }
}
