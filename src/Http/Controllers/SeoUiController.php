<?php

declare(strict_types=1);

namespace Zarbin\Seo\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JsonException;
use Zarbin\Seo\Repositories\SeoMetaRepository;
use Zarbin\Seo\Support\SeoFormFields;
use Zarbin\Seo\Support\SeoInventory;
use Zarbin\Seo\Support\SeoUiAuthorization;
use Zarbin\Seo\Support\UiConfig;

final class SeoUiController
{
    public function __construct(
        private readonly SeoMetaRepository $repository = new SeoMetaRepository,
        private readonly SeoInventory $inventory = new SeoInventory,
    ) {}

    public function dashboard(): View
    {
        SeoUiAuthorization::authorize();

        return view('zarbin-seo::ui.dashboard', [
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
            'routeNamePrefix' => UiConfig::routeNamePrefix(),
        ]);
    }

    public function routes(): View
    {
        SeoUiAuthorization::authorize();

        return view('zarbin-seo::ui.routes.index', [
            'routes' => $this->inventory->routes(),
            'databaseReady' => $this->databaseReady(),
            'routeNamePrefix' => UiConfig::routeNamePrefix(),
        ]);
    }

    public function editRoute(Request $request): View
    {
        SeoUiAuthorization::authorize();

        $routeName = (string) $request->query('route', '');
        $locale = $this->locale($request);

        abort_unless($this->routeIsConfigured($routeName), 404);

        $resolved = seo()->resolve($routeName, $locale);
        $override = $this->repository->findForRoute($routeName, $locale);

        return view('zarbin-seo::ui.routes.edit', [
            'routeName' => $routeName,
            'locale' => $locale,
            'resolved' => $resolved,
            'override' => $override,
            'fields' => SeoFormFields::fields(),
            'values' => SeoFormFields::values($override?->toArray() ?? [], $resolved->toArray()),
            'databaseReady' => $this->databaseReady(),
            'showPreview' => UiConfig::showPreview(),
            'previewHtml' => seo()->renderer()->render($resolved),
            'routeNamePrefix' => UiConfig::routeNamePrefix(),
        ]);
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
            return back()->with('zarbin_seo_warning', 'SEO database overrides are not ready. Check the feature flags and migration.');
        }

        return back()->with('zarbin_seo_success', 'SEO override saved.');
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
            $deleted ? 'SEO override deleted.' : 'No SEO override was found to delete.'
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

    private function routeIsConfigured(string $routeName): bool
    {
        return $routeName !== '' && array_key_exists($routeName, $this->configuredRoutes());
    }

    private function databaseReady(): bool
    {
        return $this->repository->enabled() && $this->repository->tableExists();
    }

    /**
     * @return array{total: int, complete: int, incomplete: int}
     */
    private function inventoryStats(): array
    {
        $items = $this->inventory->routes();
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
}
