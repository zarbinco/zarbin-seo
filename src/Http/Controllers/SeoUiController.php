<?php

declare(strict_types=1);

namespace Zarbin\Seo\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JsonException;
use Throwable;
use Zarbin\Seo\Repositories\SeoMetaRepository;
use Zarbin\Seo\Support\ModelInventoryItemFactory;
use Zarbin\Seo\Support\ModelInventorySourceResolver;
use Zarbin\Seo\Support\SearchPreviewBuilder;
use Zarbin\Seo\Support\SeoFormFields;
use Zarbin\Seo\Support\SeoInventory;
use Zarbin\Seo\Support\SeoUiAuthorization;
use Zarbin\Seo\Support\UiConfig;
use Zarbin\Seo\Support\UiDirection;
use Zarbin\Seo\Support\UiTranslator;

final class SeoUiController
{
    public function __construct(
        private readonly SeoMetaRepository $repository = new SeoMetaRepository,
        private readonly SeoInventory $inventory = new SeoInventory,
        private readonly SearchPreviewBuilder $preview = new SearchPreviewBuilder,
        private readonly ModelInventorySourceResolver $modelSources = new ModelInventorySourceResolver,
        private readonly ModelInventoryItemFactory $modelItems = new ModelInventoryItemFactory,
    ) {}

    public function dashboard(): View
    {
        SeoUiAuthorization::authorize();

        return view('zarbin-seo::ui.dashboard', array_replace([
            'status' => [
                'ui_enabled' => UiConfig::enabled(),
                'database_overrides_enabled' => $this->repository->enabled(),
                'table_exists' => $this->repository->tableExists(),
                'sitemap_enabled' => (bool) config('zarbin-seo.features.sitemap', true),
                'robots_enabled' => (bool) config('zarbin-seo.features.robots_txt', true),
                'localization_enabled' => (bool) config('zarbin-seo.localization.enabled', false),
            ],
            'inventoryStats' => $this->inventoryStats(),
            'databaseReady' => $this->databaseReady(),
            'modelsEnabled' => UiConfig::modelInventoryEnabled(),
            'routeNamePrefix' => UiConfig::routeNamePrefix(),
        ], $this->uiViewData(null, UiTranslator::get('dashboard.title'))));
    }

    public function routes(): View
    {
        SeoUiAuthorization::authorize();

        return view('zarbin-seo::ui.routes.index', array_replace([
            'routes' => $this->inventory->routes(),
            'databaseReady' => $this->databaseReady(),
            'routeNamePrefix' => UiConfig::routeNamePrefix(),
        ], $this->uiViewData(null, UiTranslator::get('routes.title'))));
    }

    public function models(): View
    {
        SeoUiAuthorization::authorize();

        return view('zarbin-seo::ui.models.index', array_replace([
            'models' => $this->inventory->models(),
            'modelsEnabled' => UiConfig::modelInventoryEnabled(),
            'databaseReady' => $this->databaseReady(),
            'routeNamePrefix' => UiConfig::routeNamePrefix(),
        ], $this->uiViewData(null, UiTranslator::get('models.title'))));
    }

    public function editRoute(Request $request): View
    {
        SeoUiAuthorization::authorize();

        $routeName = (string) $request->query('route', '');
        $locale = $this->locale($request);

        abort_unless($this->routeIsConfigured($routeName), 404);

        $resolved = seo()->resolve($routeName, $locale);
        $override = $this->repository->findForRoute($routeName, $locale);
        $previewHtml = seo()->renderer()->render($resolved);

        return view('zarbin-seo::ui.routes.edit', array_replace([
            'routeName' => $routeName,
            'locale' => $locale,
            'resolved' => $resolved,
            'override' => $override,
            'fields' => SeoFormFields::fields(),
            'values' => SeoFormFields::values($override?->toArray() ?? [], $resolved->toArray()),
            'databaseReady' => $this->databaseReady(),
            'showPreview' => UiConfig::showPreview(),
            'searchPreview' => $this->preview->build($resolved),
            'previewHtml' => $previewHtml,
            'rawHtmlPreview' => $previewHtml,
            'routeNamePrefix' => UiConfig::routeNamePrefix(),
        ], $this->uiViewData($locale, UiTranslator::get('routes.edit_title'))));
    }

    public function updateRoute(Request $request): RedirectResponse
    {
        SeoUiAuthorization::authorize();

        $this->validateRouteRequest($request);

        $routeName = (string) $request->input('route');
        $locale = $this->locale($request);

        abort_unless($this->routeIsConfigured($routeName), 404);

        $attributes = SeoFormFields::flattenOverrideData((array) $request->input());
        $meta = $this->repository->saveForRoute($routeName, $attributes, $locale);

        if ($meta === null) {
            return back()->with('zarbin_seo_warning', UiTranslator::get('form.not_saved'));
        }

        return back()->with('zarbin_seo_success', UiTranslator::get('form.saved'));
    }

    public function deleteRoute(Request $request): RedirectResponse
    {
        SeoUiAuthorization::authorize();

        $routeName = (string) $request->input('route', '');
        $locale = $this->locale($request);

        abort_unless($this->routeIsConfigured($routeName), 404);

        $deleted = $this->repository->deleteForRoute($routeName, $locale);

        return back()->with(
            $deleted ? 'zarbin_seo_success' : 'zarbin_seo_warning',
            $deleted ? UiTranslator::get('form.deleted') : UiTranslator::get('form.not_deleted')
        );
    }

    public function editModel(Request $request): View
    {
        SeoUiAuthorization::authorize();

        abort_unless(UiConfig::modelInventoryEnabled(), 404);

        $context = $this->modelContext($request);
        $resolved = seo()->resolve($context['model'], $context['locale']);
        $override = $this->repository->findForSource($context['model'], $context['locale']);
        $previewHtml = seo()->renderer()->render($resolved);

        return view('zarbin-seo::ui.models.edit', array_replace([
            'model' => $context['model'],
            'modelClass' => $context['modelClass'],
            'modelKey' => $context['modelKey'],
            'modelLabel' => $context['modelLabel'],
            'sourceLabel' => $context['sourceLabel'],
            'modelToken' => $context['modelToken'],
            'locale' => $context['locale'],
            'resolved' => $resolved,
            'override' => $override,
            'fields' => SeoFormFields::fields(),
            'values' => SeoFormFields::values($override?->toArray() ?? [], $resolved->toArray()),
            'databaseReady' => $this->databaseReady(),
            'showPreview' => UiConfig::showPreview(),
            'searchPreview' => $this->preview->build($resolved),
            'previewHtml' => $previewHtml,
            'rawHtmlPreview' => $previewHtml,
            'routeNamePrefix' => UiConfig::routeNamePrefix(),
        ], $this->uiViewData($context['locale'], UiTranslator::get('models.edit_title'))));
    }

    public function updateModel(Request $request): RedirectResponse
    {
        SeoUiAuthorization::authorize();

        abort_unless(UiConfig::modelInventoryEnabled(), 404);

        $this->validateModelRequest($request);

        $context = $this->modelContext($request);
        $attributes = SeoFormFields::flattenOverrideData((array) $request->input());
        $meta = seo()->saveOverride($context['model'], $attributes, $context['locale']);

        if ($meta === null) {
            return back()->with('zarbin_seo_warning', UiTranslator::get('form.model_not_saved'));
        }

        return back()->with('zarbin_seo_success', UiTranslator::get('form.model_saved'));
    }

    public function deleteModel(Request $request): RedirectResponse
    {
        SeoUiAuthorization::authorize();

        abort_unless(UiConfig::modelInventoryEnabled(), 404);

        $context = $this->modelContext($request);
        $deleted = seo()->deleteOverride($context['model'], $context['locale']);

        return back()->with(
            $deleted ? 'zarbin_seo_success' : 'zarbin_seo_warning',
            $deleted ? UiTranslator::get('form.model_deleted') : UiTranslator::get('form.model_not_deleted')
        );
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

    private function routeIsConfigured(string $routeName): bool
    {
        return $routeName !== '' && array_key_exists($routeName, $this->configuredRoutes());
    }

    private function databaseReady(): bool
    {
        return $this->repository->enabled() && $this->repository->tableExists();
    }

    /**
     * @return array{uiDirection: string, uiDir: string, uiLang: string, uiIsRtl: bool, uiTextStart: string, uiTextEnd: string, uiLocale: ?string, pageTitle: string}
     */
    private function uiViewData(?string $locale, string $pageTitle): array
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
            'pageTitle' => $pageTitle,
        ];
    }

    /**
     * @return array{total: int, complete: int, incomplete: int, routes: array{total: int, complete: int, incomplete: int}, models: array{total: int, complete: int, incomplete: int}}
     */
    private function inventoryStats(): array
    {
        $routes = $this->stats($this->inventory->routes());
        $models = $this->stats($this->inventory->models());

        return [
            'total' => $routes['total'],
            'complete' => $routes['complete'],
            'incomplete' => $routes['incomplete'],
            'routes' => $routes,
            'models' => $models,
        ];
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array{total: int, complete: int, incomplete: int}
     */
    private function stats(array $items): array
    {
        $complete = count(array_filter($items, fn ($item): bool => $item->complete));
        $total = count($items);

        return [
            'total' => $total,
            'complete' => $complete,
            'incomplete' => $total - $complete,
        ];
    }

    private function locale(Request $request): ?string
    {
        $locale = $request->input('locale', $request->query('locale'));

        if (! is_scalar($locale)) {
            return null;
        }

        $locale = trim((string) $locale);

        return $locale === '' ? null : $locale;
    }

    /**
     * @return array{modelClass: string, modelConfig: array<string, mixed>, model: object, modelKey: string, modelLabel: string, sourceLabel: string, modelToken: string, locale: ?string}
     */
    private function modelContext(Request $request): array
    {
        $modelToken = $this->requestString($request, 'model');
        $modelKey = $this->requestString($request, 'id');
        $locale = $this->locale($request);
        $configured = $this->configuredModelForToken($modelToken);

        abort_unless($modelToken !== '' && $modelKey !== '' && $configured !== null, 404);

        [$modelClass, $modelConfig] = $configured;
        $model = $this->findModel($modelClass, $modelConfig, $modelKey);

        abort_unless($model !== null, 404);

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
        ];
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

    private function requestString(Request $request, string $key): string
    {
        $value = $request->input($key, $request->query($key));

        return is_scalar($value) ? trim((string) $value) : '';
    }

    /**
     * @throws ValidationException
     */
    private function validateRouteRequest(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'route' => ['required', 'string'],
            'locale' => ['nullable', 'string', 'max:20'],
            'seo' => ['nullable', 'array'],
            'seo.title' => ['nullable', 'string'],
            'seo.description' => ['nullable', 'string'],
            'seo.canonical' => ['nullable', 'string'],
            'seo.robots' => ['nullable', 'string'],
            'seo.image' => ['nullable', 'string'],
            'seo.og_title' => ['nullable', 'string'],
            'seo.og_description' => ['nullable', 'string'],
            'seo.og_image' => ['nullable', 'string'],
            'seo.twitter_title' => ['nullable', 'string'],
            'seo.twitter_description' => ['nullable', 'string'],
            'seo.twitter_image' => ['nullable', 'string'],
            'seo.schema_type' => ['nullable', 'string', 'max:100'],
            'seo.extra' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $extra = $request->input('seo.extra');

            if (! is_string($extra) || trim($extra) === '') {
                return;
            }

            try {
                $decoded = json_decode($extra, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $validator->errors()->add('seo.extra', 'The extra field must be a valid JSON object.');

                return;
            }

            if (! is_array($decoded) || array_is_list($decoded)) {
                $validator->errors()->add('seo.extra', 'The extra field must be a valid JSON object.');
            }
        });

        $validator->validate();
    }

    /**
     * @throws ValidationException
     */
    private function validateModelRequest(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'model' => ['required', 'string'],
            'id' => ['required', 'string'],
            'locale' => ['nullable', 'string', 'max:20'],
            'seo' => ['nullable', 'array'],
            'seo.title' => ['nullable', 'string'],
            'seo.description' => ['nullable', 'string'],
            'seo.canonical' => ['nullable', 'string'],
            'seo.robots' => ['nullable', 'string'],
            'seo.image' => ['nullable', 'string'],
            'seo.og_title' => ['nullable', 'string'],
            'seo.og_description' => ['nullable', 'string'],
            'seo.og_image' => ['nullable', 'string'],
            'seo.twitter_title' => ['nullable', 'string'],
            'seo.twitter_description' => ['nullable', 'string'],
            'seo.twitter_image' => ['nullable', 'string'],
            'seo.schema_type' => ['nullable', 'string', 'max:100'],
            'seo.extra' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $extra = $request->input('seo.extra');

            if (! is_string($extra) || trim($extra) === '') {
                return;
            }

            try {
                $decoded = json_decode($extra, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $validator->errors()->add('seo.extra', 'The extra field must be a valid JSON object.');

                return;
            }

            if (! is_array($decoded) || array_is_list($decoded)) {
                $validator->errors()->add('seo.extra', 'The extra field must be a valid JSON object.');
            }
        });

        $validator->validate();
    }
}
