<?php

declare(strict_types=1);

namespace Zarbin\Seo\Resolvers;

use ReflectionMethod;
use Throwable;
use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Support\AttributeReader;

final class TranslationAvailabilityResolver
{
    public function isAvailable(mixed $source, string $locale): bool
    {
        if (! is_object($source)) {
            return true;
        }

        if ($source instanceof LocalizableSeo) {
            return $this->safeBooleanCall($source, 'hasSeoLocale', [$locale], true);
        }

        foreach (['hasSeoLocale', 'isSeoAvailableForLocale', 'shouldShowLocale'] as $method) {
            if ($this->canCallWithParameters($source, $method, 1)) {
                return $this->safeBooleanCall($source, $method, [$locale], true);
            }
        }

        $fields = $this->availabilityFields($source);

        if ($fields !== [] && $this->canCallWithParameters($source, 'hasTranslation', 2)) {
            foreach ($fields as $field) {
                if ($this->safeBooleanCall($source, 'hasTranslation', [$field, $locale], false)) {
                    return true;
                }
            }
        }

        if ($fields !== []) {
            return $this->hasConfiguredFieldTranslation($source, $locale, $fields);
        }

        if ($this->canCallWithParameters($source, 'seoLocales', 0)) {
            try {
                return in_array($locale, (array) $source->seoLocales(), true);
            } catch (Throwable) {
                return true;
            }
        }

        return true;
    }

    /**
     * @return array<int, string>
     */
    private function availabilityFields(object $source): array
    {
        $fields = $this->config('zarbin-seo.models.'.get_class($source).'.translation_availability', []);

        if (! is_array($fields)) {
            return [];
        }

        $normalized = [];

        foreach ($fields as $field) {
            if (! is_scalar($field)) {
                continue;
            }

            $field = trim((string) $field);

            if ($field === '') {
                continue;
            }

            $normalized[] = $field;
        }

        return $normalized;
    }

    /**
     * @param  array<int, string>  $fields
     */
    private function hasConfiguredFieldTranslation(object $source, string $locale, array $fields): bool
    {
        foreach ($fields as $field) {
            $value = $this->translationValue($source, $field, $locale);

            if ($this->hasValue($value)) {
                return true;
            }
        }

        return false;
    }

    private function translationValue(object $source, string $field, string $locale): mixed
    {
        if ($this->canCallWithParameters($source, 'getTranslation', 2)) {
            try {
                $value = $source->getTranslation($field, $locale);

                if ($this->hasValue($value)) {
                    return $value;
                }
            } catch (Throwable) {
                // Keep checking generic structures.
            }
        }

        foreach (["{$field}.{$locale}", "{$locale}.{$field}"] as $path) {
            if (AttributeReader::exists($source, $path)) {
                return AttributeReader::get($source, $path);
            }
        }

        return null;
    }

    private function hasValue(mixed $value): bool
    {
        return ! ($value === null || $value === '' || $value === []);
    }

    /**
     * @param  array<int, mixed>  $parameters
     */
    private function safeBooleanCall(object $source, string $method, array $parameters, bool $default): bool
    {
        try {
            return (bool) $source->{$method}(...$parameters);
        } catch (Throwable) {
            return $default;
        }
    }

    private function canCallWithParameters(object $source, string $method, int $parameterCount): bool
    {
        if (! method_exists($source, $method)) {
            return false;
        }

        try {
            $method = new ReflectionMethod($source, $method);

            return $method->isPublic()
                && $method->getNumberOfRequiredParameters() <= $parameterCount
                && ($method->getNumberOfParameters() >= $parameterCount || $method->isVariadic());
        } catch (Throwable) {
            return false;
        }
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
