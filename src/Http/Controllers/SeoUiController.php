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
use Zarbin\Seo\Support\SeoUiAuthorization;
use Zarbin\Seo\Support\UiComponentDataFactory;
use Zarbin\Seo\Support\UiConfig;
use Zarbin\Seo\Support\UiDirection;
use Zarbin\Seo\Support\UiTranslator;

final class SeoUiController
{
    public function __construct(
        private readonly SeoMetaRepository $repository = new SeoMetaRepository,
        private readonly UiComponentDataFactory $components = new UiComponentDataFactory,
    ) {}

    public function dashboard(): View
    {
        SeoUiAuthorization::authorize();

        return view('zarbin-seo::ui.dashboard', array_replace([
            'routeNamePrefix' => UiConfig::routeNamePrefix(),
        ], $this->uiViewData(null, UiTranslator::get('dashboard.title'))));
    }

    public function routes(): View
    {
        SeoUiAuthorization::authorize();

        return view('zarbin-seo::ui.routes.index', array_replace([
            'routeNamePrefix' => UiConfig::routeNamePrefix(),
        ], $this->uiViewData(null, UiTranslator::get('routes.title'))));
    }

    public function models(): View
    {
        SeoUiAuthorization::authorize();

        return view('zarbin-seo::ui.models.index', array_replace([
            'routeNamePrefix' => UiConfig::routeNamePrefix(),
        ], $this->uiViewData(null, UiTranslator::get('models.title'))));
    }

    public function editRoute(Request $request): View
    {
        SeoUiAuthorization::authorize();

        $routeName = (string) $request->query('route', '');
        $locale = $this->locale($request);

        abort_unless($this->components->routeIsConfigured($routeName), 404);

        return view('zarbin-seo::ui.routes.edit', array_replace([
            'routeName' => $routeName,
            'locale' => $locale,
            'routeNamePrefix' => UiConfig::routeNamePrefix(),
        ], $this->uiViewData($locale, UiTranslator::get('routes.edit_title'))));
    }

    public function updateRoute(Request $request): RedirectResponse
    {
        SeoUiAuthorization::authorize();

        $this->validateRouteRequest($request);

        $routeName = (string) $request->input('route');
        $locale = $this->locale($request);

        abort_unless($this->components->routeIsConfigured($routeName), 404);

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

        abort_unless($this->components->routeIsConfigured($routeName), 404);

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

        return view('zarbin-seo::ui.models.edit', array_replace([
            'model' => $context['model'],
            'modelClass' => $context['modelClass'],
            'modelKey' => $context['modelKey'],
            'modelLabel' => $context['modelLabel'],
            'sourceLabel' => $context['sourceLabel'],
            'modelToken' => $context['modelToken'],
            'locale' => $context['locale'],
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
        $context = $this->components->modelContext($modelToken, $modelKey, $locale);

        abort_unless($context !== null, 404);

        return $context;
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
