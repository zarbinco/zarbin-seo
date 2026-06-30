<?php

declare(strict_types=1);

namespace Zarbin\Seo\Resolvers;

use ReflectionMethod;
use Throwable;
use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\AttributeReader;
use Zarbin\Seo\Support\LocaleHelper;
use Zarbin\Seo\Support\LocaleUrlStrategy;
use Zarbin\Seo\Support\RouteUrl;

final class LocalizedUrlResolver
{
    public function resolveForSource(mixed $source, string $locale): ?string
    {
        if ($source instanceof LocalizableSeo) {
            $url = $this->safeStringCall($source, 'seoUrlForLocale', [$locale]);

            if ($url !== null) {
                return $url;
            }
        }

        if (is_object($source)) {
            foreach (['seoUrlForLocale', 'localizedSeoUrl', 'seoCanonicalUrl'] as $method) {
                $url = $this->safeStringCall($source, $method, [$locale]);

                if ($url !== null) {
                    return $url;
                }
            }

            $config = $this->modelConfig($source);
            $localizedUrls = is_array($config['localized_urls'] ?? null) ? $config['localized_urls'] : [];
            $localizedUrl = $localizedUrls[$locale] ?? null;

            if (is_string($localizedUrl) && trim($localizedUrl) !== '') {
                return trim($localizedUrl);
            }

            $localizedRoutes = is_array($config['localized_routes'] ?? null) ? $config['localized_routes'] : [];
            $localizedRoute = $localizedRoutes[$locale] ?? null;

            if (is_string($localizedRoute)) {
                $url = $this->safeRouteUrl($localizedRoute, $this->routeParameters($source, $config, $locale, false), $locale);

                if ($url !== null) {
                    return $url;
                }
            }

            if (is_string($config['route'] ?? null)) {
                $url = $this->safeRouteUrl($config['route'], $this->routeParameters($source, $config, $locale), $locale);

                if ($url !== null) {
                    return $url;
                }

                $url = $this->safeRouteUrl($config['route'], $this->routeParameters($source, $config, $locale, false), $locale);

                if ($url !== null) {
                    return $url;
                }
            }
        }

        if ($source instanceof SeoData && $source->hasCanonical()) {
            return $source->canonical;
        }

        return null;
    }

    /**
     * @param  array<int|string, mixed>  $parameters
     */
    public function resolveForRoute(string $routeName, string $locale, array $parameters = []): ?string
    {
        $config = $this->routeConfig($routeName);
        $localizedUrls = is_array($config['localized_urls'] ?? null) ? $config['localized_urls'] : [];
        $localizedUrl = $localizedUrls[$locale] ?? null;

        if (is_string($localizedUrl) && trim($localizedUrl) !== '') {
            return trim($localizedUrl);
        }

        $localizedRoutes = is_array($config['localized_routes'] ?? null) ? $config['localized_routes'] : [];
        $localizedRoute = $localizedRoutes[$locale] ?? null;

        if (is_string($localizedRoute)) {
            $url = $this->safeRouteUrl($localizedRoute, $this->routeParametersForConfig($config, $parameters, $locale, false), $locale);

            if ($url !== null) {
                return $url;
            }
        }

        $url = $this->safeRouteUrl($routeName, $this->routeParametersForConfig($config, $parameters, $locale), $locale);

        if ($url !== null) {
            return $url;
        }

        $url = $this->safeRouteUrl($routeName, $this->routeParametersForConfig($config, $parameters, $locale, false), $locale);

        if ($url !== null) {
            return $url;
        }

        $canonical = (new RouteSeoResolver)->resolve($routeName, $parameters, $locale)->canonical;

        return $canonical === '' ? null : $canonical;
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

    /**
     * @param  array<string, mixed>  $config
     * @return array<int|string, mixed>
     */
    private function routeParameters(object $source, array $config, string $locale, bool $includeConfiguredLocale = true): array
    {
        $parameters = [];

        if (isset($config['route_parameters']) && is_array($config['route_parameters'])) {
            foreach ($config['route_parameters'] as $name => $value) {
                $parameters[$name] = $this->parameterValue($source, (string) $name, $value, $locale);
            }
        } elseif (array_key_exists('route_key', $config)) {
            $routeKey = $config['route_key'];

            if (is_string($routeKey)) {
                $parameters[] = AttributeReader::get($source, $routeKey);
            } elseif ($routeKey === null) {
                $parameters[] = $source;
            }
        }

        return $includeConfiguredLocale ? $this->withRouteLocale($parameters, $locale) : $parameters;
    }

    private function parameterValue(object $source, string $name, mixed $value, string $locale): mixed
    {
        $routeParameter = $this->routeParameterName();

        if (
            $name === $routeParameter
            || $name === 'locale'
            || $name === 'language'
            || $value === '$locale'
            || $value === '{locale}'
        ) {
            return $locale;
        }

        return is_string($value) && AttributeReader::exists($source, $value)
            ? AttributeReader::get($source, $value)
            : $value;
    }

    /**
     * @param  array<int|string, mixed>  $parameters
     * @return array<int|string, mixed>
     */
    private function routeParametersForConfig(array $config, array $parameters, string $locale, bool $includeConfiguredLocale = true): array
    {
        $configured = is_array($config['parameters'] ?? null) ? $config['parameters'] : [];
        $parameters = array_replace($configured, $parameters);

        return $includeConfiguredLocale ? $this->withRouteLocale($parameters, $locale) : $parameters;
    }

    /**
     * @param  array<int|string, mixed>  $parameters
     * @return array<int|string, mixed>
     */
    private function withRouteLocale(array $parameters, string $locale): array
    {
        return LocaleUrlStrategy::routeParametersForLocale($parameters, $locale);
    }

    /**
     * @param  array<int|string, mixed>  $parameters
     */
    private function safeRouteUrl(string $routeName, array $parameters, string $locale): ?string
    {
        $url = RouteUrl::make($routeName, $parameters);

        if ($url === null || $this->hasDuplicatedLocalePrefix($url, $locale)) {
            return null;
        }

        return $url;
    }

    private function hasDuplicatedLocalePrefix(string $url, string $locale): bool
    {
        $locales = $this->configuredLocales();

        if ($locales === []) {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $path = is_string($path) ? $path : $url;
        $segments = array_values(array_filter(explode('/', trim($path, '/')), fn (string $segment): bool => $segment !== ''));
        $locale = LocaleHelper::normalizeLocale($locale);

        for ($index = 0; $index < count($segments) - 1; $index++) {
            $current = $segments[$index];
            $next = $segments[$index + 1];

            if (
                in_array($current, $locales, true)
                && in_array($next, $locales, true)
                && ($locale === null || $current === $locale || $next === $locale)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function configuredLocales(): array
    {
        $locales = $this->config('zarbin-seo.localization.locales', []);

        return is_array($locales) ? LocaleHelper::normalizeLocales($locales) : [];
    }

    private function routeParameterName(): ?string
    {
        $parameter = $this->config('zarbin-seo.localization.route_parameter');

        if (! is_string($parameter)) {
            return null;
        }

        $parameter = trim($parameter);

        return $parameter === '' ? null : $parameter;
    }

    /**
     * @param  array<int, mixed>  $parameters
     */
    private function safeStringCall(object $source, string $method, array $parameters): ?string
    {
        if (! $this->canCallWithParameters($source, $method, count($parameters))) {
            return null;
        }

        try {
            $value = $source->{$method}(...$parameters);
        } catch (Throwable) {
            return null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function canCallWithParameters(object $source, string $method, int $parameterCount): bool
    {
        if (! method_exists($source, $method)) {
            return false;
        }

        try {
            $method = new ReflectionMethod($source, $method);

            return $method->isPublic()
                && $method->getNumberOfRequiredParameters() <= $parameterCount
                && ($method->getNumberOfParameters() >= $parameterCount || $method->isVariadic());
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
