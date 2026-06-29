<?php

declare(strict_types=1);

namespace Zarbin\Seo\Generators;

use Closure;
use DateTimeImmutable;
use ReflectionFunction;
use Throwable;
use Traversable;
use Zarbin\Seo\Data\SitemapUrl;
use Zarbin\Seo\Renderers\SitemapRenderer;
use Zarbin\Seo\Resolvers\SitemapUrlResolver;
use Zarbin\Seo\Resolvers\TranslationAvailabilityResolver;
use Zarbin\Seo\Support\LocaleHelper;
use Zarbin\Seo\Support\RouteUrl;

final class SitemapGenerator
{
    public function __construct(
        private readonly SitemapUrlResolver $resolver = new SitemapUrlResolver,
        private readonly SitemapRenderer $renderer = new SitemapRenderer,
        private readonly TranslationAvailabilityResolver $availability = new TranslationAvailabilityResolver,
    ) {}

    /**
     * @return array<int, SitemapUrl>
     */
    public function urls(?string $locale = null): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $urls = [];

        foreach ($this->locales($locale) as $activeLocale) {
            foreach ($this->routeUrls($activeLocale) as $url) {
                $urls[$url->loc] = $url;
            }

            foreach ($this->modelUrls($activeLocale) as $url) {
                $urls[$url->loc] = $url;
            }
        }

        return array_values($urls);
    }

    public function render(?string $locale = null): string
    {
        return $this->renderer->render($this->urls($locale));
    }

    /**
     * @return array<int, array{loc: string, lastmod: string}>
     */
    public function index(): array
    {
        $loc = $this->absoluteUrl((string) $this->config('zarbin-seo.sitemap.path', 'sitemap.xml'));

        return $loc === null ? [] : [[
            'loc' => $loc,
            'lastmod' => (new DateTimeImmutable)->format(DATE_ATOM),
        ]];
    }

    public function renderIndex(): string
    {
        return $this->renderer->renderIndex($this->index());
    }

    private function enabled(): bool
    {
        return (bool) $this->config('zarbin-seo.features.sitemap', true)
            && (bool) $this->config('zarbin-seo.sitemap.enabled', true);
    }

    /**
     * @return array<int, string|null>
     */
    private function locales(?string $locale): array
    {
        if ($locale !== null) {
            return [$locale];
        }

        if (LocaleHelper::enabled()) {
            $locales = LocaleHelper::configuredLocales();

            if ($locales !== []) {
                return $locales;
            }
        }

        return [LocaleHelper::currentLocale()];
    }

    /**
     * @return array<int, SitemapUrl>
     */
    private function routeUrls(?string $locale): array
    {
        $routes = $this->config('zarbin-seo.routes', []);
        $routes = is_array($routes) ? $routes : [];
        $urls = [];

        foreach ($routes as $routeName => $config) {
            if (! is_string($routeName) || ! is_array($config) || ! $this->routeShouldBeIncluded($config)) {
                continue;
            }

            $url = $this->resolver->fromRoute($routeName, [], $locale);

            if ($url !== null) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    /**
     * @return array<int, SitemapUrl>
     */
    private function modelUrls(?string $locale): array
    {
        $models = $this->config('zarbin-seo.models', []);
        $models = is_array($models) ? $models : [];
        $urls = [];

        foreach ($models as $class => $config) {
            if (! is_array($config) || ($config['sitemap'] ?? null) === false) {
                continue;
            }

            try {
                foreach ($this->items($config, $locale) as $item) {
                    if ($locale !== null && ! $this->availability->isAvailable($item, $locale)) {
                        continue;
                    }

                    $url = $this->resolver->fromSource($item, $locale);

                    if ($url !== null) {
                        $urls[] = $url;
                    }
                }
            } catch (Throwable) {
                continue;
            }
        }

        return $urls;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function routeShouldBeIncluded(array $config): bool
    {
        if (($config['sitemap'] ?? null) === false) {
            return false;
        }

        if (($config['sitemap'] ?? null) === true) {
            return true;
        }

        foreach (['title', 'description', 'url', 'canonical', 'route'] as $key) {
            if (array_key_exists($key, $config)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return iterable<int, mixed>
     */
    private function items(array $config, ?string $locale): iterable
    {
        foreach (['sitemap_source', 'sitemap_items', 'query'] as $key) {
            if (! array_key_exists($key, $config)) {
                continue;
            }

            return $this->iterableFromSource($config[$key], $locale);
        }

        return [];
    }

    /**
     * @return iterable<int, mixed>
     */
    private function iterableFromSource(mixed $source, ?string $locale): iterable
    {
        if (is_callable($source)) {
            $source = $this->callSource($source, $locale);
        }

        if ($source instanceof Traversable || is_array($source)) {
            return $source;
        }

        if (is_object($source) && method_exists($source, 'cursor')) {
            try {
                return $source->cursor();
            } catch (Throwable) {
                return [];
            }
        }

        if (is_object($source) && method_exists($source, 'get')) {
            try {
                return $source->get();
            } catch (Throwable) {
                return [];
            }
        }

        return [];
    }

    private function callSource(callable $source, ?string $locale): mixed
    {
        try {
            $source = $source instanceof Closure ? $source : Closure::fromCallable($source);
            $reflection = new ReflectionFunction($source);

            return $reflection->getNumberOfRequiredParameters() === 0
                ? $source()
                : $source($locale);
        } catch (Throwable) {
            return [];
        }
    }

    private function absoluteUrl(string $path): ?string
    {
        $path = trim($path, '/');
        $appUrl = $this->config('app.url');

        if (is_string($appUrl) && trim($appUrl) !== '') {
            return rtrim($appUrl, '/').'/'.$path;
        }

        return RouteUrl::make('zarbin-seo.sitemap.index') ?? RouteUrl::make('zarbin-seo.sitemap');
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
