<?php

declare(strict_types=1);

namespace Zarbin\Seo\Resolvers;

use DateTimeInterface;
use ReflectionMethod;
use Throwable;
use Zarbin\Seo\Contracts\Sitemapable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Data\SitemapUrl;
use Zarbin\Seo\Support\AttributeReader;
use Zarbin\Seo\Support\LocaleHelper;

final class SitemapUrlResolver
{
    public function __construct(
        private readonly SeoSourceResolver $seo = new SeoSourceResolver,
        private readonly LocalizedUrlResolver $urls = new LocalizedUrlResolver,
        private readonly AlternateLanguageResolver $alternates = new AlternateLanguageResolver,
    ) {}

    public function fromSource(mixed $source, ?string $locale = null): ?SitemapUrl
    {
        $config = is_object($source) ? $this->modelConfig($source) : [];
        $data = $source instanceof SeoData ? $source : $this->seo->resolve($source, $locale);

        if (! $this->shouldIncludeSource($source, $locale, $config, $data)) {
            return null;
        }

        $loc = $this->sourceLocation($source, $locale) ?? $data->canonical;

        if ($loc === null || trim($loc) === '') {
            return null;
        }

        return SitemapUrl::make([
            'loc' => $loc,
            'lastmod' => $this->sourceLastModified($source, $locale, $config),
            'changefreq' => $this->sourceChangeFrequency($source, $locale, $config),
            'priority' => $this->sourcePriority($source, $locale, $config),
            'alternates' => $this->includeAlternates()
                ? $this->alternates->forSource($source, LocaleHelper::currentLocale($locale))
                : [],
        ]);
    }

    /**
     * @param  array<int|string, mixed>  $parameters
     */
    public function fromRoute(string $routeName, array $parameters = [], ?string $locale = null): ?SitemapUrl
    {
        $config = $this->routeConfig($routeName);

        if (($config['sitemap'] ?? null) === false) {
            return null;
        }

        $loc = $this->urls->resolveForRoute($routeName, LocaleHelper::currentLocale($locale) ?? '', $parameters);
        $loc ??= (new RouteSeoResolver)->resolve($routeName, $parameters, $locale)->canonical;

        if ($loc === null || trim($loc) === '') {
            return null;
        }

        return SitemapUrl::make([
            'loc' => $loc,
            'lastmod' => $config['lastmod'] ?? null,
            'changefreq' => $config['change_frequency'] ?? $this->config('zarbin-seo.sitemap.defaults.change_frequency'),
            'priority' => $config['priority'] ?? $this->config('zarbin-seo.sitemap.defaults.priority'),
            'alternates' => $this->includeAlternates()
                ? $this->alternates->forRoute($routeName, $parameters, LocaleHelper::currentLocale($locale))
                : [],
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function shouldIncludeSource(mixed $source, ?string $locale, array $config, SeoData $data): bool
    {
        if ($source instanceof Sitemapable && ! $source->shouldBeInSitemap($locale)) {
            return false;
        }

        if ($this->canCallWithParameters($source, 'shouldBeInSitemap', 1) && ! (bool) $this->safeCall($source, 'shouldBeInSitemap', [$locale], true)) {
            return false;
        }

        if (($config['sitemap'] ?? null) === false) {
            return false;
        }

        return ! in_array('noindex', array_map('mb_strtolower', $data->robots), true);
    }

    private function sourceLocation(mixed $source, ?string $locale): ?string
    {
        if ($source instanceof Sitemapable) {
            $url = $this->safeCall($source, 'sitemapUrl', [$locale]);

            if ($this->filled($url)) {
                return (string) $url;
            }
        }

        foreach (['sitemapUrl', 'sitemapUrlForLocale'] as $method) {
            if (! is_object($source)) {
                continue;
            }

            $url = $this->safeCall($source, $method, [$locale]);

            if ($this->filled($url)) {
                return (string) $url;
            }
        }

        if ($locale !== null) {
            return $this->urls->resolveForSource($source, $locale);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function sourcePriority(mixed $source, ?string $locale, array $config): mixed
    {
        $priority = $this->safeCall($source, 'sitemapPriority', [$locale]);

        return $priority ?? ($config['priority'] ?? $this->config('zarbin-seo.sitemap.defaults.priority'));
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function sourceChangeFrequency(mixed $source, ?string $locale, array $config): mixed
    {
        $changeFrequency = $this->safeCall($source, 'sitemapChangeFrequency', [$locale]);

        return $changeFrequency ?? ($config['change_frequency'] ?? $this->config('zarbin-seo.sitemap.defaults.change_frequency'));
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function sourceLastModified(mixed $source, ?string $locale, array $config): DateTimeInterface|string|null
    {
        $lastModified = $this->safeCall($source, 'sitemapLastModified', [$locale]);

        if ($lastModified instanceof DateTimeInterface || is_string($lastModified)) {
            return $lastModified;
        }

        if (is_object($source)) {
            $updatedAt = AttributeReader::get($source, 'updated_at');

            if ($updatedAt instanceof DateTimeInterface || is_string($updatedAt)) {
                return $updatedAt;
            }
        }

        return isset($config['lastmod']) && is_string($config['lastmod']) ? $config['lastmod'] : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function modelConfig(object $source): array
    {
        $config = $this->config('zarbin-seo.models.'.get_class($source), []);

        return is_array($config) ? $config : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function routeConfig(string $routeName): array
    {
        $routes = $this->config('zarbin-seo.routes', []);

        if (is_array($routes) && isset($routes[$routeName]) && is_array($routes[$routeName])) {
            return $routes[$routeName];
        }

        $config = $this->config('zarbin-seo.routes.'.$routeName, []);

        return is_array($config) ? $config : [];
    }

    private function includeAlternates(): bool
    {
        return (bool) $this->config('zarbin-seo.sitemap.include_alternates', false);
    }

    private function filled(mixed $value): bool
    {
        return ! ($value === null || $value === '' || $value === []);
    }

    /**
     * @param  array<int, mixed>  $parameters
     */
    private function safeCall(mixed $source, string $method, array $parameters = [], mixed $default = null): mixed
    {
        if (! is_object($source) || ! method_exists($source, $method)) {
            return $default;
        }

        try {
            $reflection = new ReflectionMethod($source, $method);

            if (! $reflection->isPublic() || $reflection->getNumberOfRequiredParameters() > count($parameters)) {
                return $default;
            }

            $parameters = $reflection->isVariadic()
                ? $parameters
                : array_slice($parameters, 0, $reflection->getNumberOfParameters());

            return $source->{$method}(...$parameters);
        } catch (Throwable) {
            return $default;
        }
    }

    private function canCallWithParameters(mixed $source, string $method, int $parameterCount): bool
    {
        if (! is_object($source) || ! method_exists($source, $method)) {
            return false;
        }

        try {
            $method = new ReflectionMethod($source, $method);

            return $method->isPublic()
                && $method->getNumberOfRequiredParameters() <= $parameterCount;
        } catch (Throwable) {
            return false;
        }
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
