<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use ArrayAccess;
use ReflectionMethod;
use ReflectionProperty;
use Throwable;

final class AttributeReader
{
    public static function get(mixed $source, string|array|null $keys, mixed $default = null): mixed
    {
        if ($keys === null) {
            return $default;
        }

        if (is_array($keys)) {
            return self::first($source, $keys, $default);
        }

        $missing = self::missingValue();
        $value = self::readPath($source, $keys, $missing);

        return $value === $missing ? $default : $value;
    }

    /**
     * @param  array<int, string>  $keys
     */
    public static function first(mixed $source, array $keys, mixed $default = null): mixed
    {
        $missing = self::missingValue();

        foreach ($keys as $key) {
            $value = self::get($source, $key, $missing);

            if ($value !== $missing && ! self::isEmpty($value)) {
                return $value;
            }
        }

        return $default;
    }

    public static function exists(mixed $source, string $key): bool
    {
        return self::readPath($source, $key, self::missingValue()) !== self::missingValue();
    }

    private static function readPath(mixed $source, string $path, mixed $default): mixed
    {
        if ($path === '') {
            return $default;
        }

        $value = $source;

        foreach (explode('.', $path) as $segment) {
            if ($segment === '') {
                return $default;
            }

            $value = self::readSegment($value, $segment, $default);

            if ($value === $default) {
                return $default;
            }
        }

        return $value;
    }

    private static function readSegment(mixed $source, string $key, mixed $default): mixed
    {
        if (is_array($source)) {
            return array_key_exists($key, $source) ? $source[$key] : $default;
        }

        if ($source instanceof ArrayAccess) {
            try {
                if ($source->offsetExists($key)) {
                    return $source->offsetGet($key);
                }
            } catch (Throwable) {
                return $default;
            }
        }

        if (! is_object($source)) {
            return $default;
        }

        $property = self::publicProperty($source, $key, $default);

        if ($property !== $default) {
            return $property;
        }

        $relation = self::loadedRelation($source, $key, $default);

        if ($relation !== $default) {
            return $relation;
        }

        $attribute = self::modelAttribute($source, $key, $default);

        if ($attribute !== $default) {
            return $attribute;
        }

        return self::zeroArgumentMethod($source, $key, $default);
    }

    private static function publicProperty(object $source, string $key, mixed $default): mixed
    {
        if (! property_exists($source, $key)) {
            return $default;
        }

        try {
            $property = new ReflectionProperty($source, $key);

            if (! $property->isPublic() || ! $property->isInitialized($source)) {
                return $default;
            }

            return $property->getValue($source);
        } catch (Throwable) {
            return $default;
        }
    }

    private static function loadedRelation(object $source, string $key, mixed $default): mixed
    {
        if (! method_exists($source, 'relationLoaded')) {
            return $default;
        }

        try {
            if (! $source->relationLoaded($key)) {
                return $default;
            }

            if (method_exists($source, 'getRelation')) {
                return $source->getRelation($key);
            }

            if (method_exists($source, 'getRelationValue')) {
                return $source->getRelationValue($key);
            }
        } catch (Throwable) {
            return $default;
        }

        return $default;
    }

    private static function modelAttribute(object $source, string $key, mixed $default): mixed
    {
        if (! method_exists($source, 'getAttribute')) {
            return $default;
        }

        if (! self::hasModelAttribute($source, $key)) {
            return $default;
        }

        try {
            return $source->getAttribute($key);
        } catch (Throwable) {
            return $default;
        }
    }

    private static function hasModelAttribute(object $source, string $key): bool
    {
        foreach (['getAttributes', 'getCasts'] as $method) {
            try {
                if (
                    method_exists($source, $method)
                    && self::methodRequiresNoParameters($source, $method)
                    && array_key_exists($key, (array) $source->{$method}())
                ) {
                    return true;
                }
            } catch (Throwable) {
                continue;
            }
        }

        foreach (['hasAttribute', 'hasGetMutator', 'hasAttributeGetMutator'] as $method) {
            try {
                if (method_exists($source, $method) && (bool) $source->{$method}($key)) {
                    return true;
                }
            } catch (Throwable) {
                continue;
            }
        }

        return false;
    }

    private static function zeroArgumentMethod(object $source, string $key, mixed $default): mixed
    {
        if (! method_exists($source, $key) || ! self::methodRequiresNoParameters($source, $key)) {
            return $default;
        }

        try {
            return $source->{$key}();
        } catch (Throwable) {
            return $default;
        }
    }

    private static function methodRequiresNoParameters(object $source, string $method): bool
    {
        try {
            $method = new ReflectionMethod($source, $method);

            return $method->isPublic() && $method->getNumberOfRequiredParameters() === 0;
        } catch (Throwable) {
            return false;
        }
    }

    private static function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    private static function missingValue(): object
    {
        static $missing;

        return $missing ??= new class {};
    }
}
