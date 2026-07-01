<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Data\SeoInventoryItem;
use Zarbin\Seo\Resolvers\SeoSourceResolver;

final class ModelInventoryItemFactory
{
    public function __construct(
        private readonly SeoSourceResolver $resolver = new SeoSourceResolver,
        private readonly SeoCompletionChecker $completion = new SeoCompletionChecker,
    ) {}

    /**
     * @param  class-string|string  $modelClass
     * @param  array<string, mixed>  $modelConfig
     */
    public function make(object $model, string $modelClass, array $modelConfig, ?string $locale = null): ?SeoInventoryItem
    {
        $key = $this->keyFor($model, $modelConfig);

        if ($key === null) {
            return null;
        }

        $itemLocale = $this->itemLocale($modelConfig, $locale);
        $data = $this->resolve($model, $itemLocale);
        $status = $this->completion->check($data);
        $label = $this->label($model, $data, $modelClass, $modelConfig, $key);
        $sourceLabel = $this->sourceLabel($modelClass, $modelConfig);

        return new SeoInventoryItem(
            key: $key,
            type: 'model',
            label: $label,
            locale: $itemLocale,
            editUrl: $this->editUrl($modelClass, $modelConfig, $key, $itemLocale),
            data: $data,
            complete: $status['complete'],
            missing: $status['missing'],
            warnings: $status['warnings'],
            meta: [
                'model_class' => $modelClass,
                'model_id' => $this->idFor($model),
                'model_key' => $key,
                'display' => $label,
                'source_label' => $sourceLabel,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $modelConfig
     */
    public function keyFor(object $model, array $modelConfig): ?string
    {
        $ui = $this->ui($modelConfig);

        foreach ([$ui['key'] ?? null, $modelConfig['route_key'] ?? null] as $field) {
            if (is_string($field) && trim($field) !== '') {
                $value = AttributeReader::get($model, trim($field));

                if ($this->filled($value)) {
                    return (string) $value;
                }
            }
        }

        if (method_exists($model, 'getKey')) {
            try {
                $value = $model->getKey();

                if ($this->filled($value)) {
                    return (string) $value;
                }
            } catch (Throwable) {
                //
            }
        }

        $id = AttributeReader::get($model, 'id');

        return $this->filled($id) ? (string) $id : null;
    }

    /**
     * @param  class-string|string  $modelClass
     * @param  array<string, mixed>  $modelConfig
     */
    public function modelToken(string $modelClass, array $modelConfig): string
    {
        return $this->stringOrNull($this->ui($modelConfig)['alias'] ?? null) ?? $modelClass;
    }

    /**
     * @param  class-string|string  $modelClass
     * @param  array<string, mixed>  $modelConfig
     */
    public function sourceLabel(string $modelClass, array $modelConfig): string
    {
        return $this->stringOrNull($this->ui($modelConfig)['label'] ?? null)
            ?? $this->stringOrNull($modelConfig['label'] ?? null)
            ?? $this->basename($modelClass);
    }

    /**
     * @param  array<string, mixed>  $modelConfig
     */
    private function itemLocale(array $modelConfig, ?string $locale): ?string
    {
        return $this->stringOrNull($this->ui($modelConfig)['locale'] ?? null)
            ?? $this->stringOrNull($modelConfig['locale'] ?? null)
            ?? LocaleHelper::normalizeLocale($locale);
    }

    private function resolve(object $model, ?string $locale): SeoData
    {
        try {
            return $this->resolver->resolve($model, $locale);
        } catch (Throwable) {
            return SeoData::make(['locale' => $locale]);
        }
    }

    /**
     * @param  class-string|string  $modelClass
     * @param  array<string, mixed>  $modelConfig
     */
    private function label(object $model, SeoData $data, string $modelClass, array $modelConfig, string $key): string
    {
        $display = $this->ui($modelConfig)['display'] ?? [];
        $display = is_array($display) ? $display : [$display];

        foreach ($display as $field) {
            if (! is_string($field) || trim($field) === '') {
                continue;
            }

            $value = AttributeReader::get($model, trim($field));

            if ($this->filled($value)) {
                return (string) $value;
            }
        }

        foreach ([$data->title, AttributeReader::get($model, 'title'), AttributeReader::get($model, 'name'), AttributeReader::get($model, 'slug'), AttributeReader::get($model, 'id')] as $value) {
            if ($this->filled($value)) {
                return (string) $value;
            }
        }

        return $this->basename($modelClass).' '.$key;
    }

    /**
     * @param  class-string|string  $modelClass
     * @param  array<string, mixed>  $modelConfig
     */
    private function editUrl(string $modelClass, array $modelConfig, string $key, ?string $locale): ?string
    {
        if (! function_exists('route')) {
            return null;
        }

        try {
            $parameters = [
                'model' => $this->modelToken($modelClass, $modelConfig),
                'id' => $key,
            ];

            if ($locale !== null) {
                $parameters['locale'] = $locale;
            }

            return route(UiConfig::routeNamePrefix().'models.edit', $parameters);
        } catch (Throwable) {
            return null;
        }
    }

    private function idFor(object $model): ?string
    {
        if (method_exists($model, 'getKey')) {
            try {
                $key = $model->getKey();

                if ($this->filled($key)) {
                    return (string) $key;
                }
            } catch (Throwable) {
                //
            }
        }

        $id = AttributeReader::get($model, 'id');

        return $this->filled($id) ? (string) $id : null;
    }

    /**
     * @param  array<string, mixed>  $modelConfig
     * @return array<string, mixed>
     */
    private function ui(array $modelConfig): array
    {
        $ui = $modelConfig['ui'] ?? [];

        return is_array($ui) ? $ui : [];
    }

    private function filled(mixed $value): bool
    {
        return is_scalar($value) && trim((string) $value) !== '';
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function basename(string $class): string
    {
        if (function_exists('class_basename')) {
            return class_basename($class);
        }

        $parts = explode('\\', $class);

        return end($parts) ?: $class;
    }
}
