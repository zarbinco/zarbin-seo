<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\Route;
use Throwable;
use Zarbin\Seo\Models\SeoMeta;
use Zarbin\Seo\Repositories\SeoMetaRepository;

final class UiComponentDataFactory
{
    public function __construct(
        private readonly SeoMetaRepository $repository = new SeoMetaRepository,
        private readonly SeoInventory $inventory = new SeoInventory,
        private readonly SearchPreviewBuilder $preview = new SearchPreviewBuilder,
        private readonly ModelInventorySourceResolver $modelSources = new ModelInventorySourceResolver,
        private readonly ModelInventoryItemFactory $modelItems = new ModelInventoryItemFactory,
    ) {}

    /**
     * @return array{uiDirection: string, uiDir: string, uiLang: string, uiIsRtl: bool, uiTextStart: string, uiTextEnd: string, uiLocale: ?string}
     */
    public function directionData(?string $locale = null): array
    {
        $attributes = UiDirection::htmlAttributes($locale);

        return [
            'uiDirection' => $attributes['dir'],
            'uiDir' => $attributes['dir'],
            'uiLang' => $attributes['lang'],
            'uiIsRtl' => $attributes['dir'] === 'rtl',
            'uiTextStart' => UiDirection::textAlignStart($locale),
            'uiTextEnd' => UiDirection::textAlignEnd($locale),
            'uiLocale' => $locale,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboard(?string $locale = null): array
    {
        $routes = $this->inventory->routes($locale);
        $models = UiConfig::modelInventoryEnabled() ? $this->inventory->models($locale) : [];
        $routeStats = $this->stats($routes);
        $modelStats = $this->stats($models);

        return array_replace($this->directionData($locale), [
            'status' => [
                'ui_enabled' => UiConfig::enabled(),
                'database_overrides_enabled' => $this->repository->enabled(),
                'table_exists' => $this->repository->tableExists(),
                'sitemap_enabled' => (bool) config('zarbin-seo.features.sitemap', true),
                'robots_enabled' => (bool) config('zarbin-seo.features.robots_txt', true),
                'localization_enabled' => (bool) config('zarbin-seo.localization.enabled', false),
            ],
            'inventoryStats' => [
                'total' => $routeStats['total'] + $modelStats['total'],
                'complete' => $routeStats['complete'] + $modelStats['complete'],
                'incomplete' => $routeStats['incomplete'] + $modelStats['incomplete'],
                'routes' => $routeStats,
                'models' => $modelStats,
            ],
            'routeStats' => $routeStats,
            'modelStats' => $modelStats,
            'databaseReady' => $this->databaseReady(),
            'modelsEnabled' => UiConfig::modelInventoryEnabled(),
            'routeIndexUrl' => $this->hostedUrl('routes.index'),
            'modelIndexUrl' => $this->hostedUrl('models.index'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function routes(?string $locale = null, ?string $editUrlBase = null, bool $showActions = true): array
    {
        $routes = $this->inventory->routes($locale);
        $actionUrls = [];

        foreach ($routes as $index => $item) {
            $actionUrls[$index] = $this->routeEditUrl($item->key, $item->locale, $item->editUrl, $editUrlBase);
        }

        return array_replace($this->directionData($locale), [
            'routes' => $routes,
            'actionUrls' => $actionUrls,
            'databaseReady' => $this->databaseReady(),
            'showActions' => $showActions,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function models(?string $locale = null, bool $showActions = true): array
    {
        $models = UiConfig::modelInventoryEnabled() ? $this->inventory->models($locale) : [];
        $actionUrls = [];

        foreach ($models as $index => $item) {
            $actionUrls[$index] = $item->editUrl;
        }

        return array_replace($this->directionData($locale), [
            'models' => $models,
            'actionUrls' => $actionUrls,
            'modelsEnabled' => UiConfig::modelInventoryEnabled(),
            'databaseReady' => $this->databaseReady(),
            'showActions' => $showActions,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function routeForm(
        string $routeName,
        ?string $locale = null,
        ?string $action = null,
        ?string $deleteAction = null,
        bool $showPreview = true,
        bool $showRawHtml = true,
    ): array {
        $routeConfigured = $this->routeIsConfigured($routeName);
        $resolved = $routeConfigured ? seo()->resolve($routeName, $locale) : null;
        $override = $routeConfigured ? $this->repository->findForRoute($routeName, $locale) : null;
        $previewHtml = $resolved === null ? '' : seo()->renderer()->render($resolved);
        $action ??= $this->hostedUrl('routes.update');
        $deleteAction ??= $this->hostedUrl('routes.delete');
        $databaseReady = $this->databaseReady();
        $canSubmit = $routeConfigured && $databaseReady && $action !== null;
        $canDelete = $routeConfigured && $databaseReady && $deleteAction !== null;

        return array_replace($this->directionData($locale ?? $resolved?->locale), [
            'routeName' => $routeName,
            'locale' => $locale,
            'routeConfigured' => $routeConfigured,
            'resolved' => $resolved,
            'override' => $override,
            'fields' => SeoFormFields::fields(),
            'values' => SeoFormFields::values($override?->toArray() ?? [], $resolved?->toArray() ?? []),
            'databaseReady' => $databaseReady,
            'showPreview' => $showPreview && UiConfig::showPreview(),
            'showRawHtml' => $showRawHtml,
            'searchPreview' => $resolved === null ? null : $this->preview->build($resolved),
            'previewHtml' => $previewHtml,
            'rawHtmlPreview' => $previewHtml,
            'action' => $action,
            'deleteAction' => $deleteAction,
            'canSubmit' => $canSubmit,
            'canDelete' => $canDelete,
            'routeIndexUrl' => $this->hostedUrl('routes.index'),
            'warning' => $this->formWarning($databaseReady, $routeConfigured, $action),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function modelForm(
        mixed $source = null,
        ?string $model = null,
        mixed $id = null,
        ?string $locale = null,
        ?string $action = null,
        ?string $deleteAction = null,
        bool $showPreview = true,
        bool $showRawHtml = true,
    ): array {
        $context = is_object($source)
            ? $this->modelContextFromSource($source, $locale)
            : $this->modelContext($model ?? '', $this->stringOrNull($id) ?? '', $locale);

        $resolved = $context === null ? null : seo()->resolve($context['model'], $context['locale']);
        $override = $context === null ? null : $this->repository->findForSource($context['model'], $context['locale']);
        $previewHtml = $resolved === null ? '' : seo()->renderer()->render($resolved);
        $defaultAction = ($context['configured'] ?? false) ? $this->hostedUrl('models.update') : null;
        $defaultDeleteAction = ($context['configured'] ?? false) ? $this->hostedUrl('models.destroy') : null;
        $action ??= $defaultAction;
        $deleteAction ??= $defaultDeleteAction;
        $databaseReady = $this->databaseReady();
        $canSubmit = $context !== null && $databaseReady && $action !== null;
        $canDelete = $context !== null && $databaseReady && $deleteAction !== null;

        return array_replace($this->directionData($locale ?? $resolved?->locale), [
            'source' => $context['model'] ?? null,
            'modelClass' => $context['modelClass'] ?? $model,
            'modelKey' => $context['modelKey'] ?? $this->stringOrNull($id),
            'modelLabel' => $context['modelLabel'] ?? null,
            'sourceLabel' => $context['sourceLabel'] ?? null,
            'modelToken' => $context['modelToken'] ?? $model,
            'locale' => $context['locale'] ?? $locale,
            'sourceFound' => $context !== null,
            'sourceConfigured' => (bool) ($context['configured'] ?? false),
            'resolved' => $resolved,
            'override' => $override,
            'fields' => SeoFormFields::fields(),
            'values' => SeoFormFields::values($override?->toArray() ?? [], $resolved?->toArray() ?? []),
            'databaseReady' => $databaseReady,
            'showPreview' => $showPreview && UiConfig::showPreview(),
            'showRawHtml' => $showRawHtml,
            'searchPreview' => $resolved === null ? null : $this->preview->build($resolved),
            'previewHtml' => $previewHtml,
            'rawHtmlPreview' => $previewHtml,
            'action' => $action,
            'deleteAction' => $deleteAction,
            'canSubmit' => $canSubmit,
            'canDelete' => $canDelete,
            'modelIndexUrl' => $this->hostedUrl('models.index'),
            'warning' => $context === null
                ? UiTranslator::get('components.source_not_found')
                : $this->formWarning($databaseReady, true, $action),
        ]);
    }

    public function routeIsConfigured(string $routeName): bool
    {
        return $routeName !== '' && array_key_exists($routeName, $this->configuredRoutes());
    }

    /**
     * @return array{modelClass: string, modelConfig: array<string, mixed>, model: object, modelKey: string, modelLabel: string, sourceLabel: string, modelToken: string, locale: ?string, configured: bool}|null
     */
    public function modelContext(string $modelToken, string $modelKey, ?string $locale = null): ?array
    {
        $configured = $this->configuredModelForToken($modelToken);

        if ($modelToken === '' || $modelKey === '' || $configured === null) {
            return null;
        }

        [$modelClass, $modelConfig] = $configured;
        $model = $this->findModel($modelClass, $modelConfig, $modelKey);

        if ($model === null) {
            return null;
        }

        return $this->modelContextFromConfiguredSource($model, $modelClass, $modelConfig, $modelKey, $locale);
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array{total: int, complete: int, incomplete: int}
     */
    private function stats(array $items): array
    {
        $complete = count(array_filter($items, fn (mixed $item): bool => (bool) $item->complete));
        $total = count($items);

        return [
            'total' => $total,
            'complete' => $complete,
            'incomplete' => $total - $complete,
        ];
    }

    private function databaseReady(): bool
    {
        return $this->repository->enabled() && $this->repository->tableExists();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function configuredRoutes(): array
    {
        $routes = config('zarbin-seo.routes', []);

        return is_array($routes) ? array_filter($routes, 'is_array') : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function configuredModels(): array
    {
        $models = config('zarbin-seo.models', []);

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
     * @return array{0: string, 1: array<string, mixed>}|null
     */
    private function configuredModelForToken(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        foreach ($this->configuredModels() as $modelClass => $config) {
            if ($token === $modelClass || $token === $this->modelItems->modelToken($modelClass, $config)) {
                return [$modelClass, $config];
            }
        }

        return null;
    }

    /**
     * @return array{modelClass: string, modelConfig: array<string, mixed>, model: object, modelKey: string, modelLabel: string, sourceLabel: string, modelToken: string, locale: ?string, configured: bool}
     */
    private function modelContextFromSource(object $source, ?string $locale = null): array
    {
        $modelClass = $source::class;
        $modelConfig = $this->configuredModels()[$modelClass] ?? [];
        $modelKey = $this->modelItems->keyFor($source, $modelConfig) ?? SeoMeta::idForSource($source) ?? '';
        $configured = $modelConfig !== [];

        return $configured
            ? $this->modelContextFromConfiguredSource($source, $modelClass, $modelConfig, $modelKey, $locale)
            : $this->modelContextFromUnconfiguredSource($source, $modelClass, $modelKey, $locale);
    }

    /**
     * @param  array<string, mixed>  $modelConfig
     * @return array{modelClass: string, modelConfig: array<string, mixed>, model: object, modelKey: string, modelLabel: string, sourceLabel: string, modelToken: string, locale: ?string, configured: bool}
     */
    private function modelContextFromConfiguredSource(
        object $model,
        string $modelClass,
        array $modelConfig,
        string $modelKey,
        ?string $locale,
    ): array {
        $data = seo()->resolve($model, $locale);

        return [
            'modelClass' => $modelClass,
            'modelConfig' => $modelConfig,
            'model' => $model,
            'modelKey' => $modelKey,
            'modelLabel' => $this->modelItems->make($model, $modelClass, $modelConfig, $locale)?->label
                ?? $data->title
                ?? $modelKey,
            'sourceLabel' => $this->modelItems->sourceLabel($modelClass, $modelConfig),
            'modelToken' => $this->modelItems->modelToken($modelClass, $modelConfig),
            'locale' => $locale,
            'configured' => true,
        ];
    }

    /**
     * @return array{modelClass: string, modelConfig: array<string, mixed>, model: object, modelKey: string, modelLabel: string, sourceLabel: string, modelToken: string, locale: ?string, configured: bool}
     */
    private function modelContextFromUnconfiguredSource(
        object $model,
        string $modelClass,
        string $modelKey,
        ?string $locale,
    ): array {
        $data = seo()->resolve($model, $locale);
        $label = $data->title ?? ($modelKey !== '' ? $modelKey : $this->basename($modelClass));

        return [
            'modelClass' => $modelClass,
            'modelConfig' => [],
            'model' => $model,
            'modelKey' => $modelKey,
            'modelLabel' => $label,
            'sourceLabel' => $this->basename($modelClass),
            'modelToken' => $modelClass,
            'locale' => $locale,
            'configured' => false,
        ];
    }

    /**
     * @param  class-string|string  $modelClass
     * @param  array<string, mixed>  $modelConfig
     */
    private function findModel(string $modelClass, array $modelConfig, string $modelKey): ?object
    {
        $eloquent = $this->findEloquentModel($modelClass, $modelConfig, $modelKey);

        if ($eloquent !== null) {
            return $eloquent;
        }

        foreach ($this->modelSources->resolve($modelClass, $modelConfig) as $model) {
            if (! is_object($model)) {
                continue;
            }

            if ($this->modelItems->keyFor($model, $modelConfig) === $modelKey) {
                return $model;
            }
        }

        return null;
    }

    /**
     * @param  class-string|string  $modelClass
     * @param  array<string, mixed>  $modelConfig
     */
    private function findEloquentModel(string $modelClass, array $modelConfig, string $modelKey): ?object
    {
        if (! class_exists($modelClass) || ! is_a($modelClass, EloquentModel::class, true)) {
            return null;
        }

        try {
            /** @var EloquentModel $instance */
            $instance = new $modelClass;
            $query = $modelClass::query();
            $field = $this->modelLookupField($instance, $modelConfig);

            if ($field === null || $field === $instance->getKeyName()) {
                return $query->whereKey($modelKey)->first();
            }

            return $query->where($field, $modelKey)->first();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $modelConfig
     */
    private function modelLookupField(EloquentModel $model, array $modelConfig): ?string
    {
        $ui = is_array($modelConfig['ui'] ?? null) ? $modelConfig['ui'] : [];

        foreach ([$ui['key'] ?? null, $modelConfig['route_key'] ?? null] as $field) {
            if (is_string($field) && trim($field) !== '') {
                return trim($field);
            }
        }

        return $model->getKeyName();
    }

    private function formWarning(bool $databaseReady, bool $sourceReady, ?string $action): ?string
    {
        if (! $sourceReady) {
            return UiTranslator::get('components.source_not_found');
        }

        if (! $databaseReady) {
            return UiTranslator::get('form.database_preview_warning');
        }

        if ($action === null) {
            return UiTranslator::get('components.hosted_routes_disabled').' '.UiTranslator::get('components.custom_action_missing');
        }

        return null;
    }

    private function routeEditUrl(string $routeName, ?string $locale, ?string $default, ?string $editUrlBase): ?string
    {
        if ($editUrlBase === null || trim($editUrlBase) === '') {
            return $default;
        }

        $parameters = ['route' => $routeName];

        if ($locale !== null) {
            $parameters['locale'] = $locale;
        }

        return $this->urlWithQuery($editUrlBase, $parameters);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function hostedUrl(string $name, array $parameters = []): ?string
    {
        $routeName = UiConfig::routeNamePrefix().$name;

        try {
            if (! Route::has($routeName)) {
                return null;
            }

            return route($routeName, $parameters);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, string>  $parameters
     */
    private function urlWithQuery(string $baseUrl, array $parameters): string
    {
        $baseUrl = trim($baseUrl);
        $separator = str_contains($baseUrl, '?')
            ? (str_ends_with($baseUrl, '?') || str_ends_with($baseUrl, '&') ? '' : '&')
            : '?';

        return $baseUrl.$separator.http_build_query($parameters);
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
