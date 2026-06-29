<?php

declare(strict_types=1);

namespace Zarbin\Seo\Resolvers;

use ReflectionMethod;
use Throwable;
use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\AttributeReader;
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
            $localizedRoute = $config['localized_routes'][$locale] ?? null;

            if (is_string($localizedRoute)) {
                $url = RouteUrl::make($localizedRoute, $this->routeParameters($source, $config, $locale, false));

                if ($url !== null) {
                    return $url;
                }
            }

            if (is_string($config['route'] ?? null)) {
                $url = RouteUrl::make($config['route'], $this->routeParameters($source, $config, $locale));

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
        $localizedUrl = $config['localized_urls'][$locale] ?? null;

        if (is_string($localizedUrl) && trim($localizedUrl) !== '') {
            return trim($localizedUrl);
        }

        $localizedRoute = $config['localized_routes'][$locale] ?? null;

        if (is_string($localizedRoute)) {
            $url = RouteUrl::make($localizedRoute, $this->routeParametersForConfig($config, $parameters, $locale, false));

            if ($url !== null) {
                return $url;
            }
        }

        $url = RouteUrl::make($routeName, $this->routeParametersForConfig($config, $parameters, $locale));

        if ($url !== null) {
            return $url;
        }

        $url = RouteUrl::make($routeName, $this->routeParametersForConfig($config, $parameters, $locale, false));

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
        $routeParameter = $this->routeParameterName();

        if ($routeParameter === null || array_key_exists($routeParameter, $parameters)) {
            return $parameters;
        }

        return array_replace([$routeParameter => $locale], $parameters);
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
