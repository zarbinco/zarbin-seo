<?php

declare(strict_types=1);

namespace Zarbin\Seo\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Throwable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Models\SeoMeta;

final class SeoMetaRepository
{
    private ?bool $tableExists = null;

    private ?string $checkedTable = null;

    public function enabled(): bool
    {
        return (bool) $this->config('zarbin-seo.features.database_overrides', false)
            && (bool) $this->config('zarbin-seo.database.enabled', false);
    }

    public function tableExists(): bool
    {
        $table = $this->table();

        if ($this->tableExists !== null && $this->checkedTable === $table) {
            return $this->tableExists;
        }

        try {
            $this->tableExists = Schema::hasTable($table);
            $this->checkedTable = $table;

            return $this->tableExists;
        } catch (Throwable $exception) {
            if (! $this->ignoreMissingTable()) {
                throw $exception;
            }

            $this->tableExists = false;
            $this->checkedTable = $table;

            return false;
        }
    }

    public function findForSource(object $source, ?string $locale = null): ?SeoMeta
    {
        $id = SeoMeta::idForSource($source);

        if (! $this->canQuery() || $id === null) {
            return null;
        }

        return $this->safeQuery(fn (): ?SeoMeta => $this->query()
            ->where('seoable_type', SeoMeta::typeForSource($source))
            ->where('seoable_id', $id)
            ->where('locale', SeoMeta::normalizedLocale($locale))
            ->first());
    }

    public function findForRoute(string $routeName, ?string $locale = null): ?SeoMeta
    {
        if (! $this->canQuery()) {
            return null;
        }

        return $this->safeQuery(fn (): ?SeoMeta => $this->query()
            ->where('seoable_type', SeoMeta::routeType())
            ->where('seoable_id', $routeName)
            ->where('locale', SeoMeta::normalizedLocale($locale))
            ->first());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function saveForSource(object $source, array $attributes, ?string $locale = null): ?SeoMeta
    {
        $id = SeoMeta::idForSource($source);

        if (! $this->canQuery() || $id === null) {
            return null;
        }

        return $this->safeQuery(fn (): ?SeoMeta => $this->query()->updateOrCreate([
            'seoable_type' => SeoMeta::typeForSource($source),
            'seoable_id' => $id,
            'locale' => SeoMeta::normalizedLocale($locale),
        ], $this->normalizeAttributes($attributes)));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function saveForRoute(string $routeName, array $attributes, ?string $locale = null): ?SeoMeta
    {
        if (! $this->canQuery()) {
            return null;
        }

        return $this->safeQuery(fn (): ?SeoMeta => $this->query()->updateOrCreate([
            'seoable_type' => SeoMeta::routeType(),
            'seoable_id' => $routeName,
            'locale' => SeoMeta::normalizedLocale($locale),
        ], $this->normalizeAttributes($attributes)));
    }

    public function deleteForSource(object $source, ?string $locale = null): bool
    {
        $id = SeoMeta::idForSource($source);

        if (! $this->canQuery() || $id === null) {
            return false;
        }

        return (bool) $this->safeQuery(fn (): int => $this->query()
            ->where('seoable_type', SeoMeta::typeForSource($source))
            ->where('seoable_id', $id)
            ->where('locale', SeoMeta::normalizedLocale($locale))
            ->delete(), 0);
    }

    public function deleteForRoute(string $routeName, ?string $locale = null): bool
    {
        if (! $this->canQuery()) {
            return false;
        }

        return (bool) $this->safeQuery(fn (): int => $this->query()
            ->where('seoable_type', SeoMeta::routeType())
            ->where('seoable_id', $routeName)
            ->where('locale', SeoMeta::normalizedLocale($locale))
            ->delete(), 0);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function normalizeAttributes(array $attributes): array
    {
        $normalized = [];

        foreach ([
            'title',
            'description',
            'canonical_url',
            'image',
            'og_title',
            'og_description',
            'og_image',
            'twitter_title',
            'twitter_description',
            'twitter_image',
            'schema_type',
        ] as $key) {
            if (array_key_exists($key, $attributes) && $attributes[$key] !== null) {
                $normalized[$key] = $attributes[$key];
            }
        }

        if (array_key_exists('canonical', $attributes) && ! array_key_exists('canonical_url', $normalized) && $attributes['canonical'] !== null) {
            $normalized['canonical_url'] = $attributes['canonical'];
        }

        if (! array_key_exists('schema_type', $normalized)) {
            foreach (['type', 'schema'] as $key) {
                if (array_key_exists($key, $attributes) && $attributes[$key] !== null) {
                    $normalized['schema_type'] = $attributes[$key];
                    break;
                }
            }
        }

        if (array_key_exists('robots', $attributes) && $attributes['robots'] !== null) {
            $normalized['robots'] = SeoData::make(['robots' => $attributes['robots']])->robots;
        }

        if (isset($attributes['extra']) && is_array($attributes['extra'])) {
            $normalized['extra'] = $attributes['extra'];
        }

        $this->normalizeSocialAttributes($attributes, $normalized);

        return $this->withoutNulls($normalized);
    }

    private function canQuery(): bool
    {
        return $this->enabled() && $this->tableExists();
    }

    private function query(): Builder
    {
        return $this->model()->newQuery();
    }

    private function model(): SeoMeta
    {
        $class = $this->config('zarbin-seo.database.model', SeoMeta::class);
        $class = is_string($class) && is_a($class, SeoMeta::class, true) ? $class : SeoMeta::class;

        return new $class;
    }

    private function table(): string
    {
        $table = $this->config('zarbin-seo.database.table', 'seo_meta');

        return is_string($table) && trim($table) !== '' ? trim($table) : 'seo_meta';
    }

    private function ignoreMissingTable(): bool
    {
        return (bool) $this->config('zarbin-seo.database.ignore_missing_table', true);
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn|null
     */
    private function safeQuery(callable $callback, mixed $default = null): mixed
    {
        try {
            return $callback();
        } catch (QueryException $exception) {
            if (! $this->ignoreMissingTable()) {
                throw $exception;
            }

            return $default;
        } catch (Throwable $exception) {
            if (! $this->ignoreMissingTable()) {
                throw $exception;
            }

            return $default;
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $normalized
     */
    private function normalizeSocialAttributes(array $attributes, array &$normalized): void
    {
        $map = [
            'og_title' => [
                ['og', 'title'],
                ['open_graph', 'title'],
                'og.title',
                'open_graph.title',
            ],
            'og_description' => [
                ['og', 'description'],
                ['open_graph', 'description'],
                'og.description',
                'open_graph.description',
            ],
            'og_image' => [
                ['og', 'image'],
                ['open_graph', 'image'],
                'og.image',
                'open_graph.image',
            ],
            'twitter_title' => [
                ['twitter', 'title'],
                'twitter.title',
            ],
            'twitter_description' => [
                ['twitter', 'description'],
                'twitter.description',
            ],
            'twitter_image' => [
                ['twitter', 'image'],
                'twitter.image',
            ],
        ];

        foreach ($map as $target => $sources) {
            if (array_key_exists($target, $normalized)) {
                continue;
            }

            foreach ($sources as $source) {
                $value = is_array($source)
                    ? $this->nestedValue($attributes, $source[0], $source[1])
                    : ($attributes[$source] ?? null);

                if ($value !== null) {
                    $normalized[$target] = $value;
                    break;
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function nestedValue(array $attributes, string $group, string $key): mixed
    {
        return isset($attributes[$group]) && is_array($attributes[$group]) && array_key_exists($key, $attributes[$group])
            ? $attributes[$group][$key]
            : null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function withoutNulls(array $attributes): array
    {
        return array_filter($attributes, fn (mixed $value): bool => $value !== null);
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
