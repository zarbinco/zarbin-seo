<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use JsonException;
use Zarbin\Seo\Data\SeoData;

final class SeoFormFields
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function fields(): array
    {
        return [
            'title' => ['key' => 'title', 'label' => 'SEO Title', 'type' => 'text', 'help' => 'Manual title override.'],
            'description' => ['key' => 'description', 'label' => 'SEO Description', 'type' => 'textarea', 'rows' => 3, 'help' => 'Search result description.'],
            'canonical' => ['key' => 'canonical', 'label' => 'Canonical URL', 'type' => 'url', 'help' => 'Absolute canonical URL.'],
            'robots' => ['key' => 'robots', 'label' => 'Robots', 'type' => 'text', 'help' => 'Example: index, follow'],
            'image' => ['key' => 'image', 'label' => 'Image URL', 'type' => 'url', 'help' => 'Default social image URL.'],
            'og_title' => ['key' => 'og_title', 'label' => 'Open Graph Title', 'type' => 'text'],
            'og_description' => ['key' => 'og_description', 'label' => 'Open Graph Description', 'type' => 'textarea', 'rows' => 3],
            'og_image' => ['key' => 'og_image', 'label' => 'Open Graph Image URL', 'type' => 'url'],
            'twitter_title' => ['key' => 'twitter_title', 'label' => 'Twitter/X Title', 'type' => 'text'],
            'twitter_description' => ['key' => 'twitter_description', 'label' => 'Twitter/X Description', 'type' => 'textarea', 'rows' => 3],
            'twitter_image' => ['key' => 'twitter_image', 'label' => 'Twitter/X Image URL', 'type' => 'url'],
            'schema_type' => ['key' => 'schema_type', 'label' => 'Schema Type', 'type' => 'text', 'help' => 'Example: WebPage, Article, CollectionPage'],
            'extra' => ['key' => 'extra', 'label' => 'Extra JSON', 'type' => 'textarea', 'rows' => 5, 'help' => 'Optional JSON object stored with the override.'],
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
