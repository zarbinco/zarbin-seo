<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use JsonException;
use Throwable;
use Zarbin\Seo\Data\SeoData;

final class SeoFormFields
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function fields(): array
    {
        return [
            'title' => self::field('title', 'text'),
            'description' => self::field('description', 'textarea', ['rows' => 3]),
            'canonical' => self::field('canonical', 'url'),
            'robots' => self::field('robots', 'select', ['options' => self::robotsOptions()]),
            'image' => self::field('image', 'url'),
            'og_title' => self::field('og_title', 'text'),
            'og_description' => self::field('og_description', 'textarea', ['rows' => 3]),
            'og_image' => self::field('og_image', 'url'),
            'twitter_title' => self::field('twitter_title', 'text'),
            'twitter_description' => self::field('twitter_description', 'textarea', ['rows' => 3]),
            'twitter_image' => self::field('twitter_image', 'url'),
            'schema_type' => self::field('schema_type', 'text'),
            'extra' => self::field('extra', 'textarea', ['rows' => 5]),
        ];
    }

    /**
     * @param  array<string, mixed>  $override
     * @param  array<string, mixed>  $resolved
     * @return array<string, string>
     */
    public static function values(array $override = [], array $resolved = []): array
    {
        $values = [];

        foreach (array_keys(self::fields()) as $field) {
            $value = self::valueForField($field, $override);

            if (! self::filled($value)) {
                $value = self::valueForField($field, $resolved);
            }

            $values[$field] = self::stringValue($field, $value);
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function flattenOverrideData(array $attributes): array
    {
        $source = isset($attributes['seo']) && is_array($attributes['seo'])
            ? $attributes['seo']
            : $attributes;
        $flattened = [];

        foreach (array_keys(self::fields()) as $field) {
            if (! array_key_exists($field, $source)) {
                continue;
            }

            $value = $source[$field];

            if (! self::filled($value)) {
                continue;
            }

            if ($field === 'canonical') {
                $flattened['canonical_url'] = $value;

                continue;
            }

            if ($field === 'robots') {
                $robots = SeoData::make(['robots' => $value])->robots;

                if ($robots !== []) {
                    $flattened['robots'] = $robots;
                }

                continue;
            }

            if ($field === 'extra') {
                $extra = self::decodeExtra($value);

                if ($extra !== null) {
                    $flattened['extra'] = $extra;
                }

                continue;
            }

            $flattened[$field] = $value;
        }

        return $flattened;
    }

    public static function inputName(string $field): string
    {
        return "seo[{$field}]";
    }

    /**
     * @return array<string, string>
     */
    public static function robotsOptions(): array
    {
        $defaults = [
            'index, follow' => 'Index, Follow',
            'noindex, follow' => 'Noindex, Follow',
            'index, nofollow' => 'Index, Nofollow',
            'noindex, nofollow' => 'Noindex, Nofollow',
        ];

        if (! function_exists('config')) {
            return $defaults;
        }

        try {
            $configured = config('zarbin-seo.ui.robots_options', $defaults);
        } catch (Throwable) {
            return $defaults;
        }

        if (! is_array($configured)) {
            return $defaults;
        }

        $normalized = [];

        foreach ($configured as $value => $label) {
            if (is_int($value)) {
                $value = $label;
            }

            if (! is_scalar($value)) {
                continue;
            }

            $value = trim((string) $value);

            if ($value === '') {
                continue;
            }

            $normalized[$value] = is_scalar($label) && trim((string) $label) !== ''
                ? trim((string) $label)
                : $value;
        }

        return $normalized === [] ? $defaults : array_replace($defaults, $normalized);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function valueForField(string $field, array $data): mixed
    {
        return match ($field) {
            'canonical' => $data['canonical_url'] ?? ($data['canonical'] ?? null),
            'schema_type' => $data['schema_type'] ?? ($data['type'] ?? null),
            'extra' => $data['extra'] ?? null,
            default => $data[$field] ?? null,
        };
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private static function field(string $key, string $type, array $extra = []): array
    {
        return array_replace([
            'key' => $key,
            'label_key' => "zarbin-seo::ui.fields.{$key}.label",
            'hint_key' => "zarbin-seo::ui.fields.{$key}.hint",
            'label' => UiTranslator::fieldLabel($key),
            'help' => UiTranslator::fieldHint($key),
            'type' => $type,
        ], $extra);
    }

    private static function stringValue(string $field, mixed $value): string
    {
        if ($field === 'robots' && is_array($value)) {
            return SeoData::make(['robots' => $value])->robotsContent();
        }

        if ($field === 'extra' && is_array($value)) {
            try {
                return (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return '';
            }
        }

        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function decodeExtra(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) && ! array_is_list($decoded) ? $decoded : null;
    }

    private static function filled(mixed $value): bool
    {
        return ! ($value === null || $value === '' || $value === []);
    }
}
