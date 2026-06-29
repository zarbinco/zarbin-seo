<?php

declare(strict_types=1);

namespace Zarbin\Seo\Resolvers;

use Zarbin\Seo\Data\SeoData;

final class SeoSourceResolver
{
    public function __construct(
        private readonly FallbackSeoResolver $fallback = new FallbackSeoResolver,
        private readonly ModelSeoResolver $models = new ModelSeoResolver,
        private readonly RouteSeoResolver $routes = new RouteSeoResolver,
    ) {}

    public function resolve(mixed $source = null, ?string $locale = null): SeoData
    {
        if ($source instanceof SeoData) {
            return $source->locale === null && $locale !== null
                ? $source->withLocale($locale)
                : $source;
        }

        if (is_array($source)) {
            return $this->fallback
                ->resolve(null, $locale)
                ->merge($this->nonEmptyData(SeoData::fromArray($source)));
        }

        if (is_object($source)) {
            return $this->models->resolve($source, $locale);
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
        return $this->routes->resolve($routeName, $parameters, $locale);
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
}
