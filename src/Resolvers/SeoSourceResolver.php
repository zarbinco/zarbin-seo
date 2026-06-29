<?php

declare(strict_types=1);

namespace Zarbin\Seo\Resolvers;

use Zarbin\Seo\Data\CommerceData;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\CommerceConfig;
use Zarbin\Seo\Support\LocaleHelper;

final class SeoSourceResolver
{
    public function __construct(
        private readonly FallbackSeoResolver $fallback = new FallbackSeoResolver,
        private readonly ModelSeoResolver $models = new ModelSeoResolver,
        private readonly RouteSeoResolver $routes = new RouteSeoResolver,
        private readonly AlternateLanguageResolver $alternates = new AlternateLanguageResolver,
        private readonly TranslationAvailabilityResolver $availability = new TranslationAvailabilityResolver,
        private readonly DatabaseSeoOverrideResolver $database = new DatabaseSeoOverrideResolver,
        private readonly CommerceDataResolver $commerce = new CommerceDataResolver,
    ) {}

    public function resolve(mixed $source = null, ?string $locale = null): SeoData
    {
        if ($source instanceof SeoData) {
            $data = $source->locale === null && $locale !== null
                ? $source->withLocale($locale)
                : $source;

            return $this->withSourceLocalization($data, $source, $locale);
        }

        if (is_array($source)) {
            $data = $this->fallback
                ->resolve(null, $locale)
                ->merge($this->nonEmptyData(SeoData::fromArray($source)));

            return $this->withCommerceData(
                $this->withSourceLocalization($data, $source, $locale),
                $source,
                $locale
            );
        }

        if (is_object($source)) {
            $data = $this->withSourceLocalization(
                $this->models->resolve($source, $locale),
                $source,
                $locale
            );

            return $this->withCommerceData(
                $this->withDatabaseOverride($data, $this->database->resolveForSource($source, $locale)),
                $source,
                $locale
            );
        }

        if (is_string($source)) {
            return $this->route($source, [], $locale);
        }

        return $this->fallback->resolve(null, $locale);
    }

    /**
     * @param  array<int|string, mixed>  $parameters
     */
    public function route(string $routeName, array $parameters = [], ?string $locale = null): SeoData
    {
        $data = $this->routes->resolve($routeName, $parameters, $locale);
        $currentLocale = LocaleHelper::currentLocale($locale ?? $data->locale);
        $alternates = $currentLocale === null
            ? []
            : $this->alternates->forRoute($routeName, $parameters, $currentLocale);

        $data = $alternates === []
            ? $data
            : $data->withAlternateLanguages($alternates);

        return $this->withDatabaseOverride($data, $this->database->resolveForRoute($routeName, $locale));
    }

    /**
     * @return array<string, mixed>
     */
    private function nonEmptyData(SeoData $data): array
    {
        return array_filter(
            $data->toArray(),
            fn (mixed $value): bool => ! ($value === null || $value === '' || $value === []),
        );
    }

    private function withSourceLocalization(SeoData $data, mixed $source, ?string $locale): SeoData
    {
        $currentLocale = LocaleHelper::currentLocale($locale ?? $data->locale);

        if ($currentLocale === null) {
            return $data;
        }

        $alternates = $this->alternates->forSource($source, $currentLocale);

        if ($alternates !== []) {
            $data = $data->withAlternateLanguages($alternates);
        }

        return $this->withMissingTranslationState($data, $source, $currentLocale);
    }

    private function withMissingTranslationState(SeoData $data, mixed $source, string $locale): SeoData
    {
        if (! LocaleHelper::enabled() || $this->availability->isAvailable($source, $locale)) {
            return $data;
        }

        return match (LocaleHelper::missingTranslationStrategy()) {
            'noindex' => $data->withRobots($this->noindexRobots($data->robots)),
            'fallback' => $data->merge([
                'extra' => [
                    'used_locale_fallback' => true,
                ],
            ]),
            default => $data->merge([
                'extra' => [
                    'available_for_locale' => false,
                    'missing_translation_strategy' => 'hide',
                ],
            ]),
        };
    }

    private function withDatabaseOverride(SeoData $data, ?SeoData $override): SeoData
    {
        if ($override === null) {
            return $data;
        }

        return $data->merge($this->nonEmptyData($override));
    }

    private function withCommerceData(SeoData $data, mixed $source, ?string $locale): SeoData
    {
        $commerce = $this->commerce->resolve($source, $locale);

        if ($commerce === null) {
            return $data;
        }

        $extra = array_replace($data->extra, [
            'commerce' => $this->commercePayload($commerce),
        ]);

        $type = $data->type;
        $forceType = is_object($source) && (bool) (CommerceConfig::modelConfig($source)['force_type'] ?? false);

        if (
            $forceType
            || $type === null
            || $type === ''
            || mb_strtolower($type) === 'webpage'
        ) {
            $type = 'Product';
        }

        return $data->merge([
            'type' => $type,
            'extra' => $extra,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function commercePayload(CommerceData $commerce): array
    {
        return array_filter(
            $commerce->toArray(),
            fn (mixed $value): bool => ! ($value === null || $value === '' || $value === [])
        );
    }

    /**
     * @param  array<int, string>  $robots
     * @return array<int, string>
     */
    private function noindexRobots(array $robots): array
    {
        $normalized = [];
        $hasFollowDirective = false;

        foreach ($robots as $robot) {
            $lower = mb_strtolower($robot);

            if ($lower === 'index' || $lower === 'noindex') {
                continue;
            }

            if ($lower === 'follow' || $lower === 'nofollow') {
                $hasFollowDirective = true;
            }

            $normalized[] = $robot;
        }

        array_unshift($normalized, 'noindex');

        if (! $hasFollowDirective) {
            $normalized[] = 'follow';
        }

        return $normalized;
    }
}
