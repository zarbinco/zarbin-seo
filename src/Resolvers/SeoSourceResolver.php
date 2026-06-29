<?php

declare(strict_types=1);

namespace Zarbin\Seo\Resolvers;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\LocaleHelper;

final class SeoSourceResolver
{
    public function __construct(
        private readonly FallbackSeoResolver $fallback = new FallbackSeoResolver,
        private readonly ModelSeoResolver $models = new ModelSeoResolver,
        private readonly RouteSeoResolver $routes = new RouteSeoResolver,
        private readonly AlternateLanguageResolver $alternates = new AlternateLanguageResolver,
        private readonly TranslationAvailabilityResolver $availability = new TranslationAvailabilityResolver,
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

            return $this->withSourceLocalization($data, $source, $locale);
        }

        if (is_object($source)) {
            return $this->withSourceLocalization(
                $this->models->resolve($source, $locale),
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

        return $alternates === []
            ? $data
            : $data->withAlternateLanguages($alternates);
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
